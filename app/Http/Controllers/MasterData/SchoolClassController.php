<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with(['academicYear', 'grade', 'homeroomTeacher'])
            ->withCount('students')
            ->orderBy('name')
            ->paginate(15);

        return view('master.classes.index', compact('classes'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::where('is_active', true)->get();
        $grades = Grade::orderBy('level')->get();
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();

        return view('master.classes.create', compact('academicYears', 'grades', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'name' => ['required', 'string', 'max:50'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $class = SchoolClass::create($validated);

        AuditService::log(
            event: 'class_created',
            auditableType: SchoolClass::class,
            auditableId: $class->id,
            newValues: $class->toArray()
        );

        return redirect()->route('master.classes.index')->with('success', 'Rombongan belajar berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class): View
    {
        $academicYears = AcademicYear::all();
        $grades = Grade::orderBy('level')->get();
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();

        return view('master.classes.edit', compact('class', 'academicYears', 'grades', 'teachers'));
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'name' => ['required', 'string', 'max:50'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $oldValues = $class->toArray();
        $class->update($validated);

        AuditService::log(
            event: 'class_updated',
            auditableType: SchoolClass::class,
            auditableId: $class->id,
            oldValues: $oldValues,
            newValues: $class->toArray()
        );

        return redirect()->route('master.classes.index')->with('success', 'Rombongan belajar berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        if ($class->students()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus kelas yang telah memiliki data siswa aktif. Silakan nonaktifkan kelas.']);
        }

        $class->delete();

        return redirect()->route('master.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
