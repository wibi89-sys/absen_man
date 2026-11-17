<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\DailyAttendance;
use App\Models\SchoolClass;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorshipSessionParticipant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display adaptive role dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $roles = $user->roles->pluck('name')->toArray();

        // 1. Dashboard Pimpinan & Admin (super_admin, admin, kepsek, waka)
        if (array_intersect($roles, ['super_admin', 'admin', 'kepsek', 'waka'])) {
            $stats = [
                'total_students' => Student::where('status', 'active')->count(),
                'total_classes' => SchoolClass::where('is_active', true)->count(),
                'total_teachers' => Teacher::where('status', 'active')->count(),
                'total_users' => User::where('is_active', true)->count(),
                'active_academic_year' => AcademicYear::where('is_active', true)->with('semesters')->first(),
            ];

            $recentStudents = Student::with('currentClass')->latest()->take(5)->get();

            return view('dashboard.leadership', compact('stats', 'recentStudents', 'user'));
        }

        // 2. Dashboard Wali Kelas
        if (in_array('wali_kelas', $roles)) {
            $teacher = $user->teacher;
            $myClass = $teacher ? SchoolClass::where('homeroom_teacher_id', $teacher->id)->with('students')->first() : null;

            return view('dashboard.homeroom', compact('myClass', 'user', 'teacher'));
        }

        // 3. Dashboard Guru Piket
        if (in_array('guru_piket', $roles)) {
            return view('dashboard.piket', compact('user'));
        }

        // 4. Dashboard Petugas Keagamaan
        if (in_array('petugas_keagamaan', $roles)) {
            return view('dashboard.religious', compact('user'));
        }

        // 5. Dashboard Guru BK
        if (in_array('guru_bk', $roles)) {
            return view('dashboard.counselor', compact('user'));
        }

        // 6. Dashboard Orang Tua / Wali
        if (in_array('orang_tua', $roles)) {
            $parent = $user->schoolParent;
            // STRICT IDOR RULE: Only legally verified children are accessible
            $children = $parent ? $parent->verifiedStudents()->with(['currentClass.homeroomTeacher.user'])->get() : collect();
            $unverifiedCount = $parent ? $parent->unverifiedStudents()->count() : 0;
            $announcements = Announcement::active()->forRole('parents')->take(4)->get();
            $unreadMessages = $parent ? $parent->messages()->unread()->count() : 0;
            $recentMessages = $parent ? $parent->messages()->with(['student', 'sender'])->take(4)->get() : collect();

            $todayDate = now()->toDateString();
            $childIds = $children->pluck('id');
            $todayAttendances = DailyAttendance::whereIn('student_id', $childIds)
                ->whereDate('date', $todayDate)
                ->get()
                ->keyBy('student_id');

            $todayWorships = WorshipSessionParticipant::with('session.type')
                ->whereIn('student_id', $childIds)
                ->whereHas('session', fn ($q) => $q->whereDate('date', $todayDate))
                ->get()
                ->keyBy('student_id');

            return view('dashboard.parent', compact(
                'parent',
                'children',
                'unverifiedCount',
                'announcements',
                'unreadMessages',
                'recentMessages',
                'todayAttendances',
                'todayWorships',
                'user'
            ));
        }

        // 7. Dashboard Siswa
        if (in_array('siswa', $roles)) {
            $student = $user->student()->with(['currentClass', 'currentClass.homeroomTeacher'])->first();
            $today = now()->toDateString();
            $todayAttendance = $student ? DailyAttendance::where('student_id', $student->id)->whereDate('date', $today)->first() : null;
            $schoolLocation = SchoolLocation::where('is_active', true)->whereNotNull('latitude')->first();
            $recentAttendances = $student ? DailyAttendance::where('student_id', $student->id)->latest('date')->take(5)->get() : collect();
            $today = now()->toDateString();
            $todayAttendance = $student ? DailyAttendance::where('student_id', $student->id)->whereDate('date', $today)->first() : null;
            $schoolLocation = SchoolLocation::where('is_active', true)->whereNotNull('latitude')->first();
            $recentAttendances = $student ? DailyAttendance::where('student_id', $student->id)->latest('date')->take(5)->get() : collect();

            return view('dashboard.student', compact('student', 'user', 'todayAttendance', 'schoolLocation', 'recentAttendances'));
        }

        // 8. Dashboard Guru Mata Pelajaran / Default
        return view('dashboard.teacher', compact('user'));
    }
}
