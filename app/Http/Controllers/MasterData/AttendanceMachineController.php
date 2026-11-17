<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceMachine;
use App\Models\AttendanceSession;
use App\Models\AttendanceType;
use App\Models\Student;
use App\Services\Attendance\GateAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AttendanceMachineController extends Controller
{
    public function __construct(
        protected GateAttendanceService $gateAttendanceService
    ) {}

    /**
     * Tampilkan daftar mesin absensi biometrik LAN (Solution X100C / ZKTeco).
     */
    public function index(): View
    {
        $machines = AttendanceMachine::latest()->get();

        return view('master.machines.index', compact('machines'));
    }

    /**
     * Daftarkan mesin absensi LAN baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'ip_address' => 'required|string|max:45',
            'port' => 'required|integer|min:1|max:65535',
            'comm_key' => 'nullable|string|max:50',
            'protocol' => 'required|in:adms_http,socket_udp,socket_tcp,rest_api',
            'location_name' => 'nullable|string|max:100',
        ]);

        AttendanceMachine::create($validated);

        return redirect()->route('master.machines.index')
            ->with('success', "Mesin '{$validated['name']}' berhasil didaftarkan.");
    }

    /**
     * Uji koneksi socket / ping ke IP Mesin pada jaringan LAN.
     */
    public function testConnection(AttendanceMachine $machine): JsonResponse
    {
        $ip = $machine->ip_address;
        $port = $machine->port;
        $timeout = 2.0;

        $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);

        if ($fp) {
            fclose($fp);
            $machine->update([
                'last_status' => 'online',
                'last_sync_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'online',
                'message' => "Koneksi berhasil ke mesin {$machine->name} ({$ip}:{$port}). Mesin aktif di jaringan LAN.",
            ]);
        }

        $machine->update(['last_status' => 'unreachable']);

        return response()->json([
            'success' => false,
            'status' => 'unreachable',
            'message' => "Tidak dapat terhubung ke {$ip}:{$port} ({$errstr}). Pastikan mesin terhubung kabel LAN / WiFi sekolah dan IP sudah benar.",
        ], 503);
    }

    /**
     * Endpoint ADMS / Cloud Server Push untuk Mesin Solution X100C / ZKTeco.
     * Mesin mengirimkan log transaksi kehadiran secara berkala via HTTP POST.
     */
    public function receiveAdmsPush(Request $request): Response|JsonResponse
    {
        // ADMS Handshake request (GET iclock/cdata)
        if ($request->isMethod('GET')) {
            return response("GET OPTION FROM: 1\nStamp=9999\nOpStamp=9999\nPhotoStamp=0\nTransInterval=1\nTransTimes=00:00;23:59\nServerVersion=3.0.1\nServerName=MadrasahOne\nPushProtVer=2.4.1\n")
                ->header('Content-Type', 'text/plain');
        }

        // ADMS Data Upload (POST iclock/cdata)
        $content = $request->getContent();
        $lines = explode("\n", trim($content));

        $session = $this->getTodaySession();
        $processedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Parse format baris data: e.g. "PIN=202610001\tTIME=2026-11-17 06:45:10\tSTATUS=0"
            // atau format CSV / Tab separated: "202610001\t2026-11-17 06:45:10\t0\t..."
            $pin = null;
            $time = null;

            if (str_contains($line, 'PIN=') || str_contains($line, 'pin=')) {
                parse_str(str_replace("\t", '&', $line), $data);
                $pin = $data['PIN'] ?? $data['pin'] ?? null;
                $time = $data['TIME'] ?? $data['time'] ?? null;
            } else {
                $cols = preg_split('/\s+/', $line);
                if (count($cols) >= 2) {
                    $pin = $cols[0];
                    $time = $cols[1].(isset($cols[2]) ? ' '.$cols[2] : '');
                }
            }

            if (! $pin) {
                continue;
            }

            // Cari siswa berdasarkan NIS atau User ID
            $student = Student::where('nis', $pin)
                ->orWhere('id', $pin)
                ->first();

            if ($student && $session) {
                $this->gateAttendanceService->recordAttendance(
                    student: $student,
                    session: $session,
                    source: 'machine_lan',
                    deviceInfo: 'Mesin Biometrik Solution X100C LAN',
                    userId: 1
                );
                $processedCount++;
            }
        }

        // Standard ADMS protocol response
        return response("OK: {$processedCount}\n")->header('Content-Type', 'text/plain');
    }

    /**
     * Dapatkan sesi absensi masuk hari ini.
     */
    protected function getTodaySession(): ?AttendanceSession
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
                'created_by' => 1,
            ]
        );
    }
}
