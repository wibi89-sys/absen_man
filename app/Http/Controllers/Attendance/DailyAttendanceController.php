<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceSession;
use App\Models\AttendanceType;
use App\Models\DailyAttendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyAttendanceController extends Controller
{
    /**
     * Display listing of attendance records.
     */
    public function index(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $typeId = $request->input('type_id');
        $classId = $request->input('class_id');
        $status = $request->input('status');

        $types = AttendanceType::where('is_active', true)->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        $query = DailyAttendance::with(['student', 'student.currentClass', 'session.attendanceType', 'recorder'])
            ->where('date', $date);

        if ($typeId) {
            $query->whereHas('session', fn ($q) => $q->where('attendance_type_id', $typeId));
        }

        if ($classId) {
            $query->whereHas('student', fn ($q) => $q->where('current_class_id', $classId));
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Statistik Harian
        $stats = [
            'total_recorded' => DailyAttendance::where('date', $date)->count(),
            'present' => DailyAttendance::where('date', $date)->where('status', 'present')->count(),
            'late' => DailyAttendance::where('date', $date)->where('status', 'late')->count(),
            'sick' => DailyAttendance::where('date', $date)->where('status', 'sick')->count(),
            'excused' => DailyAttendance::where('date', $date)->where('status', 'excused')->count(),
            'absent' => DailyAttendance::where('date', $date)->where('status', 'absent')->count(),
        ];

        return view('attendance.index', compact('attendances', 'types', 'classes', 'date', 'stats', 'typeId', 'classId', 'status'));
    }

    /**
     * Lembar Input Absensi Cepat Rombel (Piket & Wali Kelas).
     */
    public function sheet(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $types = AttendanceType::where('is_active', true)->get();
        $type = $types->firstWhere('id', $request->input('type_id')) ?? $types->firstWhere('code', 'masuk') ?? $types->first();

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return redirect()->route('master.academic-years.index')->withErrors(['error' => 'Belum ada tahun ajaran yang aktif. Silakan aktifkan tahun ajaran terlebih dahulu.']);
        }

        // Buka atau buat sesi otomatis untuk tanggal dan jenis presensi terpilih
        $session = AttendanceSession::firstOrCreate(
            [
                'attendance_type_id' => $type->id,
                'date' => $date,
            ],
            [
                'academic_year_id' => $activeYear->id,
                'title' => "{$type->name} - ".Carbon::parse($date)->isoFormat('D MMMM Y'),
                'status' => 'open',
                'created_by' => $user->id,
            ]
        );

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        // Default rombel: jika wali kelas, arahkan ke rombel binaannya
        $selectedClassId = $request->input('class_id');
        if (! $selectedClassId && $user->hasRole('wali_kelas') && $user->teacher) {
            $myClass = SchoolClass::where('homeroom_teacher_id', $user->teacher->id)->first();
            $selectedClassId = $myClass?->id;
        }

        if (! $selectedClassId && $classes->isNotEmpty()) {
            $selectedClassId = $classes->first()->id;
        }

        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        // Ambil data siswa di rombel terpilih dan join dengan absensi yang sudah ada pada sesi ini
        $students = $selectedClass ? Student::where('current_class_id', $selectedClass->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get() : collect();

        $existingAttendances = DailyAttendance::where('attendance_session_id', $session->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('attendance.sheet', compact('session', 'types', 'type', 'date', 'classes', 'selectedClass', 'students', 'existingAttendances'));
    }

    /**
     * Simpan input massal presensi harian per rombel.
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:attendance_sessions,id'],
            'attendances' => ['required', 'array'],
            'attendances.*.status' => ['required', 'in:present,late,sick,excused,absent,early_departure'],
            'attendances.*.minutes_late' => ['nullable', 'integer', 'min:0'],
            'attendances.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $session = AttendanceSession::findOrFail($validated['session_id']);
        $user = $request->user();
        $source = $user->hasRole('guru_piket') ? 'manual_piket' : ($user->hasRole('wali_kelas') ? 'manual_homeroom' : 'system');
        $nowTime = Carbon::now()->format('H:i:s');

        DB::transaction(function () use ($validated, $session, $user, $source, $nowTime) {
            foreach ($validated['attendances'] as $studentId => $data) {
                $status = $data['status'];
                $minutesLate = ($status === 'late') ? (int) ($data['minutes_late'] ?? 15) : 0;
                $notes = $data['notes'] ?? null;

                $existing = DailyAttendance::where('attendance_session_id', $session->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    // Jika status berubah, catat riwayat koreksi
                    if ($existing->status !== $status) {
                        AttendanceCorrection::create([
                            'daily_attendance_id' => $existing->id,
                            'old_status' => $existing->status,
                            'new_status' => $status,
                            'reason' => 'Pembaruan data massal melalui lembar absensi rombel.',
                            'requested_by' => $user->id,
                            'approved_by' => $user->id,
                            'approved_at' => now(),
                        ]);
                    }

                    $existing->update([
                        'status' => $status,
                        'minutes_late' => $minutesLate,
                        'notes' => $notes,
                        'recorded_by' => $user->id,
                    ]);
                } else {
                    DailyAttendance::create([
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'date' => $session->date,
                        'status' => $status,
                        'check_in_time' => ($status === 'present' || $status === 'late') ? $nowTime : null,
                        'minutes_late' => $minutesLate,
                        'recorded_by' => $user->id,
                        'source' => $source,
                        'notes' => $notes,
                    ]);
                }
            }
        });

        AuditService::log(
            event: 'batch_attendance_saved',
            auditableType: AttendanceSession::class,
            auditableId: $session->id,
            newValues: ['count' => count($validated['attendances'])]
        );

        return back()->with('success', 'Data presensi rombel berhasil disimpan.');
    }

    /**
     * Form & Proses Koreksi Status Kehadiran (Audit Trail).
     */
    public function correct(Request $request, DailyAttendance $dailyAttendance): RedirectResponse
    {
        $validated = $request->validate([
            'new_status' => ['required', 'in:present,late,sick,excused,absent,early_departure'],
            'minutes_late' => ['nullable', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'reason.required' => 'Alasan koreksi kehadiran wajib diisi secara jelas.',
            'reason.min' => 'Alasan koreksi minimal 5 karakter.',
        ]);

        $oldStatus = $dailyAttendance->status;
        $newStatus = $validated['new_status'];
        $minutesLate = ($newStatus === 'late') ? (int) ($validated['minutes_late'] ?? 15) : 0;

        DB::transaction(function () use ($dailyAttendance, $oldStatus, $newStatus, $minutesLate, $validated, $request) {
            // Catat ke tabel riwayat koreksi
            AttendanceCorrection::create([
                'daily_attendance_id' => $dailyAttendance->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $validated['reason'],
                'requested_by' => $request->user()->id,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            // Update status absensi
            $dailyAttendance->update([
                'status' => $newStatus,
                'minutes_late' => $minutesLate,
                'notes' => "Dikoreksi: {$validated['reason']}",
            ]);
        });

        AuditService::log(
            event: 'attendance_corrected',
            auditableType: DailyAttendance::class,
            auditableId: $dailyAttendance->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus, 'reason' => $validated['reason']]
        );

        return back()->with('success', "Status presensi {$dailyAttendance->student->full_name} berhasil dikoreksi.");
    }

    /**
     * Halaman Rekapitulasi Kehadiran per Rombel & Periode.
     */
    public function recap(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $classId = $request->input('class_id');

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        // Agregasi statistik per kelas
        $classRecaps = [];
        $queryClasses = $classId ? $classes->where('id', $classId) : $classes;

        foreach ($queryClasses as $c) {
            $studentIds = $c->students()->pluck('id');
            $totalExpected = count($studentIds);

            $attendances = DailyAttendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            $sick = $attendances->where('status', 'sick')->count();
            $excused = $attendances->where('status', 'excused')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $totalRecords = $attendances->count();

            $attendanceRate = $totalRecords > 0
                ? round((($present + $late) / $totalRecords) * 100, 1)
                : 0;

            $classRecaps[] = [
                'class' => $c,
                'total_students' => $totalExpected,
                'total_records' => $totalRecords,
                'present' => $present,
                'late' => $late,
                'sick' => $sick,
                'excused' => $excused,
                'absent' => $absent,
                'rate' => $attendanceRate,
            ];
        }

        return view('attendance.recap', compact('classes', 'classRecaps', 'startDate', 'endDate', 'classId'));
    }
}
