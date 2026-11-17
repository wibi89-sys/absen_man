<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use App\Models\SchoolClass;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    /**
     * Display a listing of discipline and achievement records.
     */
    public function index(Request $request): View
    {
        $type = $request->input('type'); // 'violation' or 'achievement'
        $status = $request->input('status');
        $search = $request->input('search');

        $query = DisciplineRecord::with(['student.currentClass', 'category', 'reporter'])->latest('date');

        if ($type) {
            $query->whereHas('category', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate(20)->withQueryString();
        $categories = DisciplineCategory::all();

        $stats = [
            'total_violations' => DisciplineRecord::whereHas('category', fn ($q) => $q->where('type', 'violation'))->count(),
            'total_achievements' => DisciplineRecord::whereHas('category', fn ($q) => $q->where('type', 'achievement'))->count(),
            'pending_resolution' => DisciplineRecord::where('status', '!=', 'resolved')->count(),
        ];

        return view('discipline.index', compact('records', 'categories', 'stats', 'type', 'status', 'search'));
    }

    /**
     * Show form to record a new incident or achievement.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $categories = DisciplineCategory::all();

        return view('discipline.create', compact('classes', 'categories'));
    }

    /**
     * Store a newly created discipline or achievement record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'category_id' => 'required|exists:discipline_categories,id',
            'date' => 'required|date',
            'description' => 'required|string|max:1000',
            'action_taken' => 'nullable|string|max:1000',
            'status' => 'required|in:reported,processed,resolved',
        ]);

        $category = DisciplineCategory::findOrFail($validated['category_id']);

        $record = DisciplineRecord::create([
            'student_id' => $validated['student_id'],
            'category_id' => $validated['category_id'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'action_taken' => $validated['action_taken'] ?? null,
            'reported_by' => auth()->id(),
            'status' => $validated['status'],
            'points_awarded' => $category->points,
        ]);

        AuditService::log(
            'create',
            'discipline_record',
            $record->id,
            null,
            ['student_id' => $record->student_id, 'points' => $record->points_awarded],
            auth()->id()
        );

        return redirect()->route('discipline.index')
            ->with('success', 'Catatan kedisiplinan/prestasi berhasil dicatat.');
    }

    /**
     * Update handling status of a discipline record.
     */
    public function updateStatus(Request $request, DisciplineRecord $record): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:reported,processed,resolved',
            'action_taken' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $record->status;
        $record->update([
            'status' => $validated['status'],
            'action_taken' => $validated['action_taken'] ?? $record->action_taken,
        ]);

        AuditService::log(
            'update_status',
            'discipline_record',
            $record->id,
            ['status' => $oldStatus],
            ['status' => $record->status],
            auth()->id()
        );

        return back()->with('success', 'Status penanganan berhasil diperbarui.');
    }

    /**
     * Delete record.
     */
    public function destroy(DisciplineRecord $record): RedirectResponse
    {
        AuditService::log('delete', 'discipline_record', $record->id, null, null, auth()->id());
        $record->delete();

        return redirect()->route('discipline.index')
            ->with('success', 'Catatan kedisiplinan berhasil dihapus.');
    }
}
