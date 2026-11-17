<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\AttendanceType;
use App\Models\DailyAttendance;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Services\Attendance\AttendanceDynamicQrService;
use App\Services\Attendance\GateAttendanceService;
use App\Services\Attendance\StudentDynamicQrService;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GateAttendanceController extends Controller
{
    public function __construct(
        protected GateAttendanceService $gateAttendanceService,
        protected AttendanceDynamicQrService $dynamicQrService,
        protected StudentDynamicQrService $studentDynamicQrService
    ) {}

    /**
     * Dapatkan atau buat sesi absensi masuk hari ini secara otomatis.
     */
    protected function getOrCreateTodaySession(): AttendanceSession
    {
        $today = Carbon::today()->format('Y-m-d');
        $type = AttendanceType::firstOrCreate(
            ['code' => 'masuk'],
            [
                'name' => 'Presensi Masuk Pagi',
                'default_start_time' => '06:30:00',
                'default_end_time' => '07:15:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();

        return AttendanceSession::firstOrCreate(
            [
                'attendance_type_id' => $type->id,
                'date' => $today,
            ],
            [
                'academic_year_id' => $activeYear?->id ?? 1,
                'title' => 'Presensi Masuk Pagi - '.Carbon::today()->isoFormat('D MMMM Y'),
                'status' => 'open',
                'created_by' => auth()->id(),
            ]
        );
    }

    /**
     * 1. Presensi Mandiri Siswa via GPS Geolocation & Radius Tagging.
     */
    public function selfGpsCheckIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil siswa aktif.',
            ], 403);
        }

        // Cari lokasi resmi madrasah
        $location = SchoolLocation::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        $schoolLat = $location?->latitude ?? -6.2088; // Default koordinat madrasah jika belum diset
        $schoolLon = $location?->longitude ?? 106.8456;
        $maxRadius = $location?->radius_meters ?? 100;

        $distance = GeoLocationService::calculateDistanceMeters(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $schoolLat,
            $schoolLon
        );

        // Validasi Radius Geofencing
        if ($distance > $maxRadius) {
            return response()->json([
                'success' => false,
                'distance' => $distance,
                'max_radius' => $maxRadius,
                'message' => "Gagal: Anda berada di luar radius madrasah. Jarak Anda saat ini: {$distance} meter (Batas toleransi: {$maxRadius} meter). Pastikan Anda sudah berada di area madrasah.",
            ], 422);
        }

        $session = $this->getOrCreateTodaySession();

        $result = $this->gateAttendanceService->recordAttendance(
            student: $student,
            session: $session,
            source: 'self_mobile_gps',
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            distanceMeters: (int) $distance,
            deviceInfo: $request->userAgent(),
            userId: $user->id
        );

        $result['distance'] = $distance;
        $result['max_radius'] = $maxRadius;

        return response()->json($result);
    }

    /**
     * 2 & 4. Halaman Kiosk / Pos Gerbang Scanner (Kamera Kartu OSIS, Barcode Scanner, & Reader NFC).
     */
    public function gateScanner(): View
    {
        $session = $this->getOrCreateTodaySession();
        $todayAttendances = DailyAttendance::where('attendance_session_id', $session->id)
            ->with(['student.currentClass'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_in' => DailyAttendance::where('attendance_session_id', $session->id)->count(),
            'present' => DailyAttendance::where('attendance_session_id', $session->id)->where('status', 'present')->count(),
            'late' => DailyAttendance::where('attendance_session_id', $session->id)->where('status', 'late')->count(),
        ];

        return view('attendance.gate-scanner', compact('session', 'todayAttendances', 'stats'));
    }

    /**
     * Endpoint JSON untuk menghasilkan token QR Dinamis Kartu OSIS Digital siswa (Anti-Screenshot).
     */
    public function studentOsisTokenJson(Request $request): JsonResponse
    {
        $student = $request->user()?->student ?? Student::where('user_id', $request->user()?->id)->first();
        if (! $student) {
            return response()->json(['success' => false, 'message' => 'Profil siswa tidak ditemukan.'], 403);
        }

        $data = $this->studentDynamicQrService->generateToken($student);
        $data['remaining_seconds'] = $data['expires_in'];
        $data['success'] = true;

        return response()->json($data);
    }

    /**
     * Proses Pemindaian di Pos Gerbang (QR Kartu OSIS Dinamis/Statis, Barcode NIS, atau Tap NFC).
     */
    public function processGateScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => 'required|string|max:100',
            'method' => 'required|in:qr_card,barcode_nis,nfc_uid',
        ]);

        $identifier = trim($validated['identifier']);
        $method = $validated['method'];

        $studentQuery = Student::with('currentClass')->whereIn('status', ['active', 'aktif']);

        if ($method === 'nfc_uid') {
            $student = $studentQuery->where('rfid_uid', $identifier)->first();
            $source = 'nfc_tap';
        } elseif ($method === 'barcode_nis') {
            $student = $studentQuery->where('nis', $identifier)->first();
            $source = 'student_qr_scan';
        } else {
            // qr_card: Cek apakah ini QR Dinamis Kartu OSIS Digital (Anti-Screenshot)
            if (str_starts_with($identifier, 'STDYN.')) {
                $student = $this->studentDynamicQrService->validateToken($identifier);
                if (! $student) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR Kartu OSIS Digital sudah kedaluwarsa atau tidak valid. Harap buka kartu langsung dari aplikasi siswa (bukan hasil screenshot/foto lama).',
                    ], 422);
                }
                $source = 'dynamic_student_qr_card';
            } else {
                // Kartu fisik konvensional / NIS barcode
                $student = $studentQuery->where('qr_token', $identifier)
                    ->orWhere('nis', $identifier)
                    ->first();
                $source = 'student_qr_scan';
            }
        }

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => "Data siswa dengan identitas '{$identifier}' ({$method}) tidak ditemukan atau tidak berstatus aktif.",
            ], 404);
        }

        $session = $this->getOrCreateTodaySession();

        $result = $this->gateAttendanceService->recordAttendance(
            student: $student,
            session: $session,
            source: $source,
            deviceInfo: 'Kiosk Gerbang Sekolah',
            userId: auth()->id()
        );

        $result['student_data'] = [
            'name' => $student->full_name,
            'nis' => $student->nis,
            'class' => $student->currentClass?->name ?? '-',
            'photo' => $student->photo,
            'check_in_time' => $result['attendance']->check_in_time,
            'status' => $result['status'],
            'status_label' => $result['attendance']->status_label,
            'status_badge' => $result['attendance']->status_badge,
        ];

        return response()->json($result);
    }

    /**
     * 3. Layar Proyektor / TV / Tablet QR Dinamis di Gerbang / Depan Kelas.
     */
    public function dynamicQrDisplay(): View
    {
        $session = $this->getOrCreateTodaySession();
        $totalIn = DailyAttendance::where('attendance_session_id', $session->id)->count();

        return view('attendance.dynamic-qr-display', compact('session', 'totalIn'));
    }

    /**
     * JSON Endpoint untuk rotasi token QR Dinamis setiap 20-25 detik.
     */
    public function dynamicQrTokenJson(): JsonResponse
    {
        $session = $this->getOrCreateTodaySession();
        $token = $this->dynamicQrService->generateToken($session, 25);
        $totalIn = DailyAttendance::where('attendance_session_id', $session->id)->count();

        return response()->json([
            'token' => $token->token,
            'expires_at_epoch' => $token->expires_at_epoch,
            'ttl' => 25,
            'total_checked_in' => $totalIn,
        ]);
    }

    /**
     * Siswa memindai QR Dinamis Gerbang menggunakan kamera ponsel mandiri.
     */
    public function scanDynamicQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan profil siswa aktif.',
            ], 403);
        }

        $session = $this->getOrCreateTodaySession();
        $qrToken = $this->dynamicQrService->validateToken($session, $validated['token']);

        if (! $qrToken) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kedaluwarsa. Silakan arahkan kamera ke QR Code terbaru di layar.',
            ], 422);
        }

        $result = $this->gateAttendanceService->recordAttendance(
            student: $student,
            session: $session,
            source: 'dynamic_qr_scan',
            deviceInfo: $request->userAgent(),
            userId: $user->id
        );

        return response()->json($result);
    }
}
