<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Student;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(Request $request): View
    {
        $query = ParentModel::with(['user', 'students']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $parents = $query->orderBy('full_name')->paginate(15)->withQueryString();
        $unlinkedStudents = Student::where('status', 'active')->orderBy('full_name')->get();

        return view('master.parents.index', compact('parents', 'unlinkedStudents'));
    }

    public function linkStudent(Request $request, ParentModel $parent): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'is_primary_contact' => ['nullable', 'boolean'],
        ]);

        $parent->students()->syncWithoutDetaching([
            $validated['student_id'] => [
                'is_primary_contact' => $request->boolean('is_primary_contact', true),
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ],
        ]);

        AuditService::log(
            event: 'parent_student_linked_and_verified',
            auditableType: ParentModel::class,
            auditableId: $parent->id,
            newValues: ['student_id' => $validated['student_id']]
        );

        return redirect()->route('master.parents.index')->with('success', 'Relasi orang tua dan siswa berhasil ditautkan dan diverifikasi.');
    }

    public function toggleVerify(ParentModel $parent, Student $student): RedirectResponse
    {
        $currentPivot = $parent->students()->where('student_id', $student->id)->first()?->pivot;
        if (! $currentPivot) {
            return back()->withErrors(['error' => 'Relasi tidak ditemukan.']);
        }

        $newStatus = ! $currentPivot->is_verified;
        $parent->students()->updateExistingPivot($student->id, [
            'is_verified' => $newStatus,
            'verified_by' => $newStatus ? auth()->id() : null,
            'verified_at' => $newStatus ? now() : null,
        ]);

        AuditService::log(
            event: 'parent_student_verification_toggled',
            auditableType: ParentModel::class,
            auditableId: $parent->id,
            newValues: ['student_id' => $student->id, 'is_verified' => $newStatus]
        );

        return redirect()->route('master.parents.index')->with('success', 'Status verifikasi berhasil diubah.');
    }
}
