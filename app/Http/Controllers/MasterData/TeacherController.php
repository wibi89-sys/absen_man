<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Teacher::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('full_name')->paginate(15)->withQueryString();

        return view('master.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('master.teachers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
            'full_name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:20'],
            'title_suffix' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $teacher = Teacher::create($validated);

        AuditService::log(
            event: 'teacher_created',
            auditableType: Teacher::class,
            auditableId: $teacher->id,
            newValues: $teacher->toArray()
        );

        return redirect()->route('master.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher): View
    {
        return view('master.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:20'],
            'title_suffix' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $oldValues = $teacher->toArray();
        $teacher->update($validated);

        AuditService::log(
            event: 'teacher_updated',
            auditableType: Teacher::class,
            auditableId: $teacher->id,
            oldValues: $oldValues,
            newValues: $teacher->toArray()
        );

        return redirect()->route('master.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $oldValues = $teacher->toArray();
        $teacher->delete();

        AuditService::log(
            event: 'teacher_soft_deleted',
            auditableType: Teacher::class,
            auditableId: $teacher->id,
            oldValues: $oldValues
        );

        return redirect()->route('master.teachers.index')->with('success', 'Data guru telah dinonaktifkan.');
    }
}
