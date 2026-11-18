<?php

namespace App\Http\Controllers\Worship;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorshipRecapController extends Controller
{
    /**
     * Display comprehensive worship attendance recap.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $classId = $request->input('class_id');
        $worshipTypeId = $request->input('worship_type_id');

        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $worshipTypes = WorshipType::all();

        // Get sessions in range
        $sessionQuery = WorshipSession::whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['in_progress', 'completed', 'verified']);

        if ($worshipTypeId) {
            $sessionQuery->where('worship_type_id', $worshipTypeId);
        }

        $sessionIds = $sessionQuery->pluck('id');
        $totalSessions = $sessionIds->count();

        // Query students
        $studentsQuery = Student::with('currentClass')
            ->whereIn('status', ['active', 'aktif']);

        if ($classId) {
            $studentsQuery->where('current_class_id', $classId);
        }

        $students = $studentsQuery->orderBy('full_name')->paginate(30)->withQueryString();

        // Aggregate statistics for students in current page
        $studentIds = $students->pluck('id');

        $attendanceStats = WorshipSessionParticipant::whereIn('worship_session_id', $sessionIds)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('student_id,
                COUNT(*) as total_registered,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as count_present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as count_late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as count_absent,
                SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as count_excused,
                SUM(CASE WHEN status = "exempt" THEN 1 ELSE 0 END) as count_exempt')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // Macro aggregates
        $overallStats = WorshipSessionParticipant::whereIn('worship_session_id', $sessionIds)
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN status IN ("present", "late") THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN status = "exempt" THEN 1 ELSE 0 END) as total_exempt,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent')
            ->first();

        $overallRate = 0;
        if ($overallStats && $overallStats->total_records > 0) {
            // Rate excludes exempt students from denominator or counts them neutrally
            $effectiveDenominator = max(1, $overallStats->total_records - $overallStats->total_exempt);
            $overallRate = round(($overallStats->total_present / $effectiveDenominator) * 100, 1);
        }

        return view('worship.recap.index', compact(
            'classes',
            'worshipTypes',
            'students',
            'attendanceStats',
            'startDate',
            'endDate',
            'classId',
            'worshipTypeId',
            'totalSessions',
            'overallStats',
            'overallRate'
        ));
    }

    /**
     * Display printable view for worship attendance recap.
     */
    public function print(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $classId = $request->input('class_id');
        $worshipTypeId = $request->input('worship_type_id');

        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $selectedClass = $classId ? $classes->firstWhere('id', $classId) : null;
        $worshipTypes = WorshipType::all();
        $selectedType = $worshipTypeId ? $worshipTypes->firstWhere('id', $worshipTypeId) : null;

        $sessionQuery = WorshipSession::whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['in_progress', 'completed', 'verified']);

        if ($worshipTypeId) {
            $sessionQuery->where('worship_type_id', $worshipTypeId);
        }

        $sessionIds = $sessionQuery->pluck('id');
        $totalSessions = $sessionIds->count();

        $studentsQuery = Student::with('currentClass')->whereIn('status', ['active', 'aktif']);
        if ($classId) {
            $studentsQuery->where('current_class_id', $classId);
        }
        $students = $studentsQuery->orderBy('full_name')->get();

        $attendanceStats = WorshipSessionParticipant::whereIn('worship_session_id', $sessionIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id,
                COUNT(*) as total_registered,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as count_present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as count_late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as count_absent,
                SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as count_excused,
                SUM(CASE WHEN status = "exempt" THEN 1 ELSE 0 END) as count_exempt')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return view('worship.recap.print', compact(
            'classes',
            'selectedClass',
            'worshipTypes',
            'selectedType',
            'students',
            'attendanceStats',
            'startDate',
            'endDate',
            'classId',
            'worshipTypeId',
            'totalSessions'
        ));
    }

    /**
     * Export worship recap to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $classId = $request->input('class_id');
        $worshipTypeId = $request->input('worship_type_id');

        $sessionQuery = WorshipSession::whereBetween('date', [$startDate, $endDate]);
        if ($worshipTypeId) {
            $sessionQuery->where('worship_type_id', $worshipTypeId);
        }
        $sessionIds = $sessionQuery->pluck('id');

        $studentsQuery = Student::with('currentClass')->whereIn('status', ['active', 'aktif']);
        if ($classId) {
            $studentsQuery->where('current_class_id', $classId);
        }
        $students = $studentsQuery->orderBy('full_name')->get();

        $attendanceStats = WorshipSessionParticipant::whereIn('worship_session_id', $sessionIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id,
                COUNT(*) as total_registered,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as count_present,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as count_late,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as count_absent,
                SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as count_excused,
                SUM(CASE WHEN status = "exempt" THEN 1 ELSE 0 END) as count_exempt')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $filename = "rekap_ibadah_{$startDate}_sampai_{$endDate}.csv";

        return response()->streamDownload(function () use ($students, $attendanceStats) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Total Sesi', 'Hadir', 'Masbuk/Terlambat', 'Dispensasi', 'Izin', 'Tidak Hadir', 'Tingkat Kehadiran (%)']);

            $no = 1;
            foreach ($students as $student) {
                $stat = $attendanceStats->get($student->id);
                $present = $stat->count_present ?? 0;
                $late = $stat->count_late ?? 0;
                $exempt = $stat->count_exempt ?? 0;
                $excused = $stat->count_excused ?? 0;
                $absent = $stat->count_absent ?? 0;
                $total = $stat->total_registered ?? 0;

                $effectiveDenom = max(1, $total - $exempt);
                $rate = $total > 0 ? round((($present + $late) / $effectiveDenom) * 100, 1) : 0;

                fputcsv($handle, [
                    $no++,
                    $student->nis,
                    $student->full_name,
                    $student->currentClass?->name ?? '-',
                    $total,
                    $present,
                    $late,
                    $exempt,
                    $excused,
                    $absent,
                    $rate.'%',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
