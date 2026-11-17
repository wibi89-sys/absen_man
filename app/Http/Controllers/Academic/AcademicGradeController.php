<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicGrade;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicGradeController extends Controller
{
    /**
     * Display a listing of grades per class subject.
     */
    public function index(Request $request): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $selectedClassId = $request->input('class_id', $classes->first()?->id);

        $classSubjects = ClassSubject::with(['subject', 'teacher.user'])
            ->where('school_class_id', $selectedClassId)
            ->get();

        $selectedSubjectId = $request->input('class_subject_id', $classSubjects->first()?->id);
        $currentClassSubject = $classSubjects->firstWhere('id', $selectedSubjectId);

        $students = Student::where('current_class_id', $selectedClassId)
            ->whereIn('status', ['active', 'aktif'])
            ->orderBy('full_name')
            ->get();

        $grades = AcademicGrade::where('class_subject_id', $selectedSubjectId)
            ->get()
            ->groupBy('student_id');

        return view('academic.grades.index', compact(
            'classes',
            'selectedClassId',
            'classSubjects',
            'selectedSubjectId',
            'currentClassSubject',
            'students',
            'grades'
        ));
    }

    /**
     * Store batch grades for an assessment.
     */
    public function batchStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
            'assessment_name' => 'required|string|max:100',
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
        ]);

        $classSubjectId = (int) $validated['class_subject_id'];
        $assessmentName = $validated['assessment_name'];
        $scores = $validated['scores'];
        $notes = $validated['notes'] ?? [];

        foreach ($scores as $studentId => $score) {
            if ($score === null || $score === '') {
                continue;
            }

            AcademicGrade::updateOrCreate(
                [
                    'student_id' => (int) $studentId,
                    'class_subject_id' => $classSubjectId,
                    'assessment_name' => $assessmentName,
                ],
                [
                    'score' => (float) $score,
                    'notes' => $notes[$studentId] ?? null,
                    'created_by' => auth()->id(),
                ]
            );
        }

        AuditService::log(
            'batch_grades',
            'class_subject',
            $classSubjectId,
            null,
            ['assessment' => $assessmentName, 'count' => count($scores)],
            auth()->id()
        );

        return back()->with('success', "Nilai {$assessmentName} berhasil disimpan.");
    }

    /**
     * Toggle publication status of grades so parents can view them.
     */
    public function togglePublish(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_subject_id' => 'required|exists:class_subjects,id',
            'assessment_name' => 'required|string',
            'is_published' => 'required|boolean',
        ]);

        AcademicGrade::where('class_subject_id', $validated['class_subject_id'])
            ->where('assessment_name', $validated['assessment_name'])
            ->update(['is_published' => $validated['is_published']]);

        return back()->with('success', 'Status publikasi nilai berhasil diperbarui.');
    }
}
