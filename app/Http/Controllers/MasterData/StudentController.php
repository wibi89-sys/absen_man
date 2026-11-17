<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(Request $request): View
    {
        $query = Student::with('currentClass');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('current_class_id', $request->input('class_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query->orderBy('full_name')->paginate(15)->withQueryString();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('master.students.index', compact('students', 'classes'));
    }

    /**
     * Show form for creating a new student.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('master.students.create', compact('classes'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:students,nisn'],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'current_class_id' => ['nullable', 'exists:school_classes,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'moved', 'dropped_out'])],
            'enrollment_date' => ['required', 'date'],
        ], [
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN sudah digunakan oleh siswa lain.',
            'full_name.required' => 'Nama lengkap siswa wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'enrollment_date.required' => 'Tanggal masuk wajib diisi.',
        ]);

        $student = Student::create($validated);

        AuditService::log(
            event: 'student_created',
            auditableType: Student::class,
            auditableId: $student->id,
            newValues: $student->toArray()
        );

        return redirect()->route('master.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    /**
     * Show form for editing student.
     */
    public function edit(Student $student): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('master.students.edit', compact('student', 'classes'));
    }

    /**
     * Update student.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nisn')->ignore($student->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'current_class_id' => ['nullable', 'exists:school_classes,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'moved', 'dropped_out'])],
            'enrollment_date' => ['required', 'date'],
        ]);

        $oldValues = $student->toArray();
        $student->update($validated);

        AuditService::log(
            event: 'student_updated',
            auditableType: Student::class,
            auditableId: $student->id,
            oldValues: $oldValues,
            newValues: $student->toArray()
        );

        return redirect()->route('master.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Remove or deactivate student.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $oldValues = $student->toArray();
        $student->delete(); // Soft delete safe

        AuditService::log(
            event: 'student_soft_deleted',
            auditableType: Student::class,
            auditableId: $student->id,
            oldValues: $oldValues
        );

        return redirect()->route('master.students.index')->with('success', 'Data siswa telah diarsipkan.');
    }
}
