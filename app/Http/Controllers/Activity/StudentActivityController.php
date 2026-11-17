<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentActivityAttendance;
use App\Models\StudentActivityMember;
use App\Models\Teacher;
use App\Services\AuditService;
use App\Services\Worship\WorshipSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentActivityController extends Controller
{
    /**
     * Display a listing of extracurricular activities and organizations.
     */
    public function index(): View
    {
        $activities = StudentActivity::with(['supervisor.user'])
            ->withCount('activeMembers')
            ->get();
        $teachers = Teacher::with('user')->where('status', 'active')->get();

        return view('activity.index', compact('activities', 'teachers'));
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:ekstrakurikuler,organisasi,keagamaan',
            'supervisor_id' => 'nullable|exists:teachers,id',
            'day_of_week' => 'nullable|integer|min:1|max:7',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:100',
        ]);

        $activity = StudentActivity::create($validated);

        AuditService::log('create_activity', 'student_activity', $activity->id, null, ['name' => $activity->name], auth()->id());

        return back()->with('success', "Kegiatan {$activity->name} berhasil ditambahkan.");
    }

    /**
     * Display activity details with member roster and attendance.
     */
    public function show(StudentActivity $activity, Request $request): View
    {
        $activity->load(['supervisor.user', 'members.student.currentClass']);

        $date = $request->input('date', now()->toDateString());
        $members = $activity->activeMembers()->with('student.currentClass')->get();

        $attendances = StudentActivityAttendance::where('student_activity_id', $activity->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();

        return view('activity.show', compact('activity', 'date', 'members', 'attendances', 'classes'));
    }

    /**
     * Enroll a student as member of this activity.
     */
    public function storeMember(Request $request, StudentActivity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'role' => 'required|in:anggota,ketua,pengurus',
            'joined_date' => 'required|date',
        ]);

        StudentActivityMember::updateOrCreate(
            [
                'student_activity_id' => $activity->id,
                'student_id' => $validated['student_id'],
            ],
            [
                'role' => $validated['role'],
                'joined_date' => $validated['joined_date'],
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Anggota berhasil didaftarkan ke kegiatan.');
    }

    /**
     * Record batch attendance for an activity session.
     */
    public function recordAttendance(Request $request, StudentActivity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*' => 'in:present,absent,excused',
        ]);

        foreach ($validated['attendances'] as $studentId => $status) {
            StudentActivityAttendance::updateOrCreate(
                [
                    'student_activity_id' => $activity->id,
                    'date' => $validated['date'],
                    'student_id' => (int) $studentId,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return back()->with('success', 'Presensi kegiatan berhasil disimpan.');
    }

    /**
     * Integration: Automatically generate Asar prayer session for attendees of this afternoon activity.
     */
    public function generateAsarSession(Request $request, StudentActivity $activity, WorshipSessionService $sessionService): RedirectResponse
    {
        $date = $request->input('date', now()->toDateString());

        // Get students who attended today
        $presentStudentIds = StudentActivityAttendance::where('student_activity_id', $activity->id)
            ->whereDate('date', $date)
            ->where('status', 'present')
            ->pluck('student_id')
            ->toArray();

        if (empty($presentStudentIds)) {
            // Fallback to all active members of the activity
            $presentStudentIds = $activity->activeMembers()->pluck('student_id')->toArray();
        }

        if (empty($presentStudentIds)) {
            return back()->with('error', 'Tidak ada siswa aktif yang terdaftar pada kegiatan ini.');
        }

        $session = $sessionService->createAsarSession(
            $date,
            $presentStudentIds,
            null,
            auth()->id(),
            "Salat Asar Berjamaah Peserta Kegiatan: {$activity->name}"
        );

        return redirect()->route('worship.sessions.show', $session)
            ->with('success', "Sesi Salat Asar berhasil dibuat untuk {$session->participants()->count()} siswa kegiatan {$activity->name}.");
    }
}
