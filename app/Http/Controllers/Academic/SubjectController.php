<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects and class assignments.
     */
    public function index(Request $request): View
    {
        $subjects = Subject::withCount('classSubjects')->orderBy('order')->orderBy('name')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $selectedClassId = $request->input('class_id', $classes->first()?->id);
        $assignedSubjects = ClassSubject::with(['subject', 'teacher.user'])
            ->where('school_class_id', $selectedClassId)
            ->get();

        return view('academic.subjects.index', compact(
            'subjects',
            'classes',
            'teachers',
            'activeYear',
            'activeSemester',
            'selectedClassId',
            'assignedSubjects'
        ));
    }

    /**
     * Store a newly created subject.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:subjects,code',
            'name' => 'required|string|max:100',
            'order' => 'nullable|integer',
        ]);

        $subject = Subject::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'order' => $validated['order'] ?? 0,
            'is_active' => true,
        ]);

        AuditService::log('create', 'subject', $subject->id, null, ['name' => $subject->name], auth()->id());

        return back()->with('success', "Mata pelajaran {$subject->name} berhasil ditambahkan.");
    }

    /**
     * Assign teacher and subject to class.
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $assignment = ClassSubject::updateOrCreate(
            [
                'school_class_id' => $validated['school_class_id'],
                'subject_id' => $validated['subject_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ],
            [
                'teacher_id' => $validated['teacher_id'],
                'semester_id' => $validated['semester_id'] ?? null,
            ]
        );

        AuditService::log('assign_subject', 'class_subject', $assignment->id, null, $validated, auth()->id());

        return back()->with('success', 'Penugasan guru mata pelajaran berhasil disimpan.');
    }
}
