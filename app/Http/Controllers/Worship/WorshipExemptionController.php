<?php

namespace App\Http\Controllers\Worship;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\WorshipExemption;
use App\Models\WorshipSessionParticipant;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorshipExemptionController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Display a listing of worship exemptions.
     * Accessible only by authorized roles.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'active');
        $search = $request->input('search');
        $category = $request->input('category');

        $query = WorshipExemption::with(['student.currentClass', 'approver'])
            ->latest('start_date');

        if ($status === 'active') {
            $query->activeOnDate(now());
        } elseif ($status === 'past') {
            $query->where('end_date', '<', now()->toDateString());
        }

        if ($category) {
            $query->where('reason_category', $category);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $exemptions = $query->paginate(20)->withQueryString();

        $activeCount = WorshipExemption::activeOnDate(now())->count();
        $shariaCount = WorshipExemption::activeOnDate(now())->where('reason_category', 'sharia_halangan')->count();
        $sickCount = WorshipExemption::activeOnDate(now())->where('reason_category', 'sick')->count();

        return view('worship.exemptions.index', compact('exemptions', 'status', 'search', 'category', 'activeCount', 'shariaCount', 'sickCount'));
    }

    /**
     * Show form to register a sensitive exemption.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();

        return view('worship.exemptions.create', compact('classes'));
    }

    /**
     * Store a newly created exemption with strict audit logging.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason_category' => 'required|in:sharia_halangan,sick,emergency,dispensation',
            'notes' => 'nullable|string|max:1000',
            'is_confidential' => 'nullable|boolean',
        ]);

        $exemption = WorshipExemption::create([
            'student_id' => $validated['student_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason_category' => $validated['reason_category'],
            'notes' => $validated['notes'] ?? null,
            'approved_by' => auth()->id(),
            'is_confidential' => $request->boolean('is_confidential', true),
        ]);

        // Auto-update any today's active worship participants for this student to exempt
        $todayStr = now()->toDateString();
        if ($exemption->start_date->toDateString() <= $todayStr && $exemption->end_date->toDateString() >= $todayStr) {
            WorshipSessionParticipant::where('student_id', $exemption->student_id)
                ->whereHas('session', function ($q) use ($todayStr) {
                    $q->whereDate('date', $todayStr)->whereIn('status', ['scheduled', 'in_progress', 'completed']);
                })
                ->update([
                    'status' => 'exempt',
                    'verification_notes' => 'Dispensasi: '.$exemption->category_label,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);
        }

        AuditService::log(
            'create',
            'worship_exemption',
            $exemption->id,
            null,
            [
                'student_id' => $exemption->student_id,
                'category' => $exemption->reason_category,
                'period' => "{$exemption->start_date} s/d {$exemption->end_date}",
            ],
            auth()->id()
        );

        return redirect()->route('worship.exemptions.index')
            ->with('success', 'Dispensasi ibadah berhasil dicatat dengan proteksi kerahasiaan.');
    }

    /**
     * Remove an exemption record.
     */
    public function destroy(WorshipExemption $exemption): RedirectResponse
    {
        AuditService::log(
            'delete',
            'worship_exemption',
            $exemption->id,
            [
                'student_id' => $exemption->student_id,
                'category' => $exemption->reason_category,
            ],
            null,
            auth()->id()
        );

        $exemption->delete();

        return redirect()->route('worship.exemptions.index')
            ->with('success', 'Catatan dispensasi berhasil dihapus.');
    }
}
