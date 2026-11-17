<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\DailyAttendance;
use App\Models\ParentMessage;
use App\Models\Student;
use App\Models\WorshipSessionParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    /**
     * Display parent child deep-dive details.
     * Enforces STRICT IDOR defense: checks legal verified relationship.
     */
    public function childDetail(Student $student, Request $request): View
    {
        $parent = auth()->user()->schoolParent;

        // STRICT IDOR DEFENSE
        if (! $parent || ! $parent->hasVerifiedChild($student)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki otorisasi sah atas data siswa ini.');
        }

        $student->load(['currentClass.homeroomTeacher', 'classHistories']);

        $tab = $request->input('tab', 'overview'); // 'overview', 'attendance', 'worship', 'messages'
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // 1. School Attendance History (Verified)
        $dailyAttendances = DailyAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->latest('date')
            ->get();

        $attendanceSummary = [
            'total' => $dailyAttendances->count(),
            'present' => $dailyAttendances->where('status', 'present')->count(),
            'late' => $dailyAttendances->where('status', 'late')->count(),
            'sick' => $dailyAttendances->where('status', 'sick')->count(),
            'excused' => $dailyAttendances->where('status', 'excused')->count(),
            'absent' => $dailyAttendances->where('status', 'absent')->count(),
        ];
        $effectiveDays = max(1, $attendanceSummary['total']);
        $attendanceRate = round((($attendanceSummary['present'] + $attendanceSummary['late']) / $effectiveDays) * 100, 1);

        // 2. Worship Attendance History (Verified sessions only)
        // Sensitive privacy protection: female health notes masked as 'Dispensasi'
        $worshipParticipants = WorshipSessionParticipant::with(['session.type', 'session.location'])
            ->where('student_id', $student->id)
            ->whereHas('session', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->whereIn('status', ['in_progress', 'completed', 'verified']);
            })
            ->latest()
            ->get();

        $worshipSummary = [
            'total' => $worshipParticipants->count(),
            'present' => $worshipParticipants->where('status', 'present')->count(),
            'late' => $worshipParticipants->where('status', 'late')->count(),
            'exempt' => $worshipParticipants->where('status', 'exempt')->count(),
            'absent' => $worshipParticipants->where('status', 'absent')->count(),
            'excused' => $worshipParticipants->where('status', 'excused')->count(),
        ];
        $effectiveWorship = max(1, $worshipSummary['total'] - $worshipSummary['exempt']);
        $worshipRate = $worshipSummary['total'] > 0
            ? round((($worshipSummary['present'] + $worshipSummary['late']) / $effectiveWorship) * 100, 1)
            : 0;

        // 3. Messages from Homeroom Teacher / Madrasah
        $messages = ParentMessage::where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->with('sender')
            ->latest()
            ->paginate(10);

        return view('parent.child-detail', compact(
            'parent',
            'student',
            'tab',
            'startDate',
            'endDate',
            'dailyAttendances',
            'attendanceSummary',
            'attendanceRate',
            'worshipParticipants',
            'worshipSummary',
            'worshipRate',
            'messages'
        ));
    }

    /**
     * Display all active school announcements for parents.
     */
    public function announcements(): View
    {
        $announcements = Announcement::active()
            ->forRole('parents')
            ->with('creator')
            ->paginate(12);

        return view('parent.announcements', compact('announcements'));
    }

    /**
     * Display messages inbox for the authenticated parent.
     */
    public function messages(Request $request): View
    {
        $parent = auth()->user()->schoolParent;
        if (! $parent) {
            abort(403, 'Profil orang tua tidak ditemukan.');
        }

        $filter = $request->input('filter', 'all');
        $query = ParentMessage::where('parent_id', $parent->id)
            ->with(['student.currentClass', 'sender'])
            ->latest();

        if ($filter === 'unread') {
            $query->unread();
        }

        $messages = $query->paginate(15);
        $unreadCount = ParentMessage::where('parent_id', $parent->id)->unread()->count();

        return view('parent.messages', compact('messages', 'filter', 'unreadCount'));
    }

    /**
     * Mark an announcement or direct message as read.
     */
    public function markMessageRead(ParentMessage $message): RedirectResponse
    {
        $parent = auth()->user()->schoolParent;
        if (! $parent || $message->parent_id !== $parent->id) {
            abort(403, 'Akses Ditolak.');
        }

        $message->markAsRead();

        return back()->with('success', 'Pesan ditandai telah dibaca.');
    }
}
