<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::with('semesters')->orderByDesc('start_date')->get();

        return view('master.academic_years.index', compact('years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $isActive = $request->boolean('is_active');
        if ($isActive) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        $validated['is_active'] = $isActive;
        $year = AcademicYear::create($validated);

        // Otomatis buat Semester 1 dan 2
        Semester::create([
            'academic_year_id' => $year->id,
            'semester' => '1',
            'is_active' => $isActive,
        ]);

        Semester::create([
            'academic_year_id' => $year->id,
            'semester' => '2',
            'is_active' => false,
        ]);

        AuditService::log(
            event: 'academic_year_created',
            auditableType: AcademicYear::class,
            auditableId: $year->id,
            newValues: $year->toArray()
        );

        return redirect()->route('master.academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function setActive(AcademicYear $academicYear): RedirectResponse
    {
        AcademicYear::query()->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        AuditService::log(
            event: 'academic_year_activated',
            auditableType: AcademicYear::class,
            auditableId: $academicYear->id
        );

        return redirect()->route('master.academic-years.index')->with('success', "Tahun ajaran {$academicYear->name} berhasil diaktifkan.");
    }
}
