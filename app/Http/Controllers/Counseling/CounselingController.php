<?php

namespace App\Http\Controllers\Counseling;

use App\Http\Controllers\Controller;
use App\Models\CounselingSession;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounselingController extends Controller
{
    /**
     * Display a listing of counseling sessions.
     * Strictly restricted to Guru BK and Super Admin.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $category = $request->input('category');
        $search = $request->input('search');

        $query = CounselingSession::with(['student.currentClass', 'counselor.user'])->latest('session_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $sessions = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => CounselingSession::count(),
            'in_progress' => CounselingSession::where('status', 'in_progress')->count(),
            'follow_up_needed' => CounselingSession::where('status', 'follow_up_needed')->count(),
            'completed' => CounselingSession::where('status', 'completed')->count(),
        ];

        return view('counseling.index', compact('sessions', 'stats', 'status', 'category', 'search'));
    }

    /**
     * Show form to schedule/record a counseling session.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $counselors = Teacher::with('user')->where('status', 'active')->get();

        return view('counseling.create', compact('classes', 'counselors'));
    }

    /**
     * Store a newly created counseling session with audit logging.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'counselor_id' => 'required|exists:teachers,id',
            'session_date' => 'required|date',
            'category' => 'required|in:pribadi,sosial,belajar,karir,kedisiplinan',
            'confidentiality_level' => 'required|in:standard,strictly_confidential',
            'status' => 'required|in:scheduled,in_progress,completed,follow_up_needed',
            'case_background' => 'required|string',
            'action_plan' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after_or_equal:session_date',
        ]);

        $session = CounselingSession::create($validated);

        AuditService::log(
            'create_counseling',
            'counseling_session',
            $session->id,
            null,
            ['student_id' => $session->student_id, 'category' => $session->category],
            auth()->id()
        );

        return redirect()->route('counseling.show', $session)
            ->with('success', 'Sesi konseling berhasil disimpan dengan proteksi kerahasiaan.');
    }

    /**
     * Display a specific confidential counseling session.
     */
    public function show(CounselingSession $counseling): View
    {
        // Strict Authorization check
        if (! $counseling->canViewDetails(auth()->user())) {
            abort(403, 'Akses Ditolak: Catatan bimbingan konseling bersifat sangat rahasia.');
        }

        $counseling->load(['student.currentClass', 'counselor.user']);

        AuditService::log(
            'view_confidential_counseling',
            'counseling_session',
            $counseling->id,
            null,
            null,
            auth()->id()
        );

        return view('counseling.show', compact('counseling'));
    }

    /**
     * Update counseling progress.
     */
    public function update(Request $request, CounselingSession $counseling): RedirectResponse
    {
        if (! $counseling->canViewDetails(auth()->user())) {
            abort(403, 'Akses Ditolak.');
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,follow_up_needed',
            'action_plan' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $counseling->update($validated);

        AuditService::log('update_counseling', 'counseling_session', $counseling->id, null, $validated, auth()->id());

        return back()->with('success', 'Perkembangan sesi konseling berhasil diperbarui.');
    }
}
