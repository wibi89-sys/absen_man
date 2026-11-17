<?php

namespace App\Http\Controllers\Worship;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolLocation;
use App\Models\Student;
use App\Models\WorshipSession;
use App\Models\WorshipSessionParticipant;
use App\Models\WorshipType;
use App\Services\Worship\WorshipQrService;
use App\Services\Worship\WorshipSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorshipSessionController extends Controller
{
    public function __construct(
        protected WorshipSessionService $sessionService,
        protected WorshipQrService $qrService
    ) {}

    /**
     * Display a listing of worship sessions.
     */
    public function index(Request $request): View
    {
        $date = $request->input('date', now()->toDateString());
        $typeId = $request->input('worship_type_id');
        $status = $request->input('status');

        $query = WorshipSession::with(['type', 'location', 'creator', 'verifier'])
            ->withCount(['participants', 'attendances'])
            ->whereDate('date', $date);

        if ($typeId) {
            $query->where('worship_type_id', $typeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sessions = $query->orderBy('wave_number')->latest()->paginate(15);
        $worshipTypes = WorshipType::all();

        // Calculate today's summary metrics
        $todaySessions = WorshipSession::whereDate('date', $date)->pluck('id');
        $totalParticipants = WorshipSessionParticipant::whereIn('worship_session_id', $todaySessions)->count();
        $totalPresent = WorshipSessionParticipant::whereIn('worship_session_id', $todaySessions)
            ->whereIn('status', ['present', 'late'])->count();
        $totalExempt = WorshipSessionParticipant::whereIn('worship_session_id', $todaySessions)
            ->where('status', 'exempt')->count();
        $totalPending = WorshipSessionParticipant::whereIn('worship_session_id', $todaySessions)
            ->where('status', 'pending_verification')->count();

        return view('worship.sessions.index', compact(
            'sessions',
            'worshipTypes',
            'date',
            'typeId',
            'status',
            'totalParticipants',
            'totalPresent',
            'totalExempt',
            'totalPending'
        ));
    }

    /**
     * Show form to create a new worship session.
     */
    public function create(): View
    {
        $worshipTypes = WorshipType::all();
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();
        $locations = SchoolLocation::where('is_active', true)->get();

        return view('worship.sessions.create', compact('worshipTypes', 'classes', 'locations'));
    }

    /**
     * Store a newly created worship session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'worship_type_id' => 'required|exists:worship_types,id',
            'date' => 'required|date',
            'wave_number' => 'required|integer|min:1|max:5',
            'location_id' => 'nullable|exists:school_locations,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:school_classes,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = $this->sessionService->createSession(
            (int) $validated['worship_type_id'],
            $validated['date'],
            (int) $validated['wave_number'],
            $validated['location_id'] ? (int) $validated['location_id'] : null,
            array_map('intval', $validated['class_ids']),
            auth()->id(),
            null,
            $validated['notes'] ?? null
        );

        return redirect()->route('worship.sessions.show', $session)
            ->with('success', "Sesi ibadah berhasil dibuat. Total peserta terkunci: {$session->participants()->count()} siswa.");
    }

    /**
     * Display session details with locked participants and status breakdown.
     */
    public function show(WorshipSession $session, Request $request): View
    {
        $session->load(['type', 'location', 'creator', 'verifier']);

        $classId = $request->input('class_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $participantsQuery = $session->participants()
            ->with(['student.currentClass', 'scanRecord', 'verifier'])
            ->join('students', 'worship_session_participants.student_id', '=', 'students.id')
            ->select('worship_session_participants.*')
            ->orderBy('students.full_name');

        if ($classId) {
            $participantsQuery->where('worship_session_participants.school_class_id', $classId);
        }

        if ($status) {
            $participantsQuery->where('worship_session_participants.status', $status);
        }

        if ($search) {
            $participantsQuery->where(function ($q) use ($search) {
                $q->where('students.full_name', 'like', "%{$search}%")
                    ->orWhere('students.nis', 'like', "%{$search}%");
            });
        }

        $participants = $participantsQuery->paginate(25)->withQueryString();

        // Get class filter options
        $sessionClassIds = $session->participants()->distinct()->pluck('school_class_id');
        $sessionClasses = SchoolClass::whereIn('id', $sessionClassIds)->get();

        // Statistics
        $stats = [
            'total' => $session->participants()->count(),
            'present' => $session->participants()->where('status', 'present')->count(),
            'late' => $session->participants()->where('status', 'late')->count(),
            'pending' => $session->participants()->where('status', 'pending_verification')->count(),
            'absent' => $session->participants()->where('status', 'absent')->count(),
            'excused' => $session->participants()->where('status', 'excused')->count(),
            'exempt' => $session->participants()->where('status', 'exempt')->count(),
        ];

        return view('worship.sessions.show', compact('session', 'participants', 'sessionClasses', 'stats', 'classId', 'status', 'search'));
    }

    /**
     * Display live projector / mosque big-screen view with auto-rotating dynamic QR.
     */
    public function displayQr(WorshipSession $session): View
    {
        $session->load(['type', 'location']);
        $activeToken = $session->activeQrToken ?? $this->qrService->generateToken($session);

        return view('worship.sessions.display-qr', compact('session', 'activeToken'));
    }

    /**
     * AJAX endpoint to fetch or rotate dynamic QR token and live participant stats.
     */
    public function tokenJson(WorshipSession $session): JsonResponse
    {
        $activeToken = $session->activeQrToken;

        // Auto-regenerate if missing or near expiry (< 3 seconds remaining)
        if (! $activeToken || now()->diffInSeconds($activeToken->valid_until, false) <= 3) {
            $activeToken = $this->qrService->generateToken($session);
        }

        $remainingSeconds = max(0, (int) now()->diffInSeconds($activeToken->valid_until, false));
        $presentCount = $session->participants()->whereIn('status', ['present', 'late'])->count();
        $totalCount = $session->participants()->count();

        return response()->json([
            'token' => $activeToken->token,
            'valid_until' => $activeToken->valid_until->toIso8601String(),
            'remaining_seconds' => $remainingSeconds,
            'present_count' => $presentCount,
            'total_count' => $totalCount,
            'attendance_rate' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0,
        ]);
    }

    /**
     * Scanner view for students (camera) or officers (barcode/manual fallback).
     */
    public function scanner(WorshipSession $session): View
    {
        $session->load(['type', 'location']);

        return view('worship.sessions.scanner', compact('session'));
    }

    /**
     * Process an incoming QR scan or manual officer input.
     */
    public function processScan(Request $request, WorshipSession $session): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'identifier' => 'required|string', // can be student NIS or student ID
            'source' => 'nullable|string|in:qr_student_camera,qr_officer_scanner,manual_officer',
        ]);

        $token = $this->qrService->validateToken($validated['token']);
        if (! $token || $token->worship_session_id !== $session->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token QR tidak valid atau sudah kedaluwarsa. Tunggu QR berikutnya pada layar.',
            ], 422);
        }

        // Find student by NIS or ID
        $student = Student::with('currentClass')
            ->where('nis', $validated['identifier'])
            ->orWhere('id', $validated['identifier'])
            ->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => "Data siswa dengan nomor identitas '{$validated['identifier']}' tidak ditemukan.",
            ], 404);
        }

        $source = $validated['source'] ?? 'qr_student_camera';
        $result = $this->qrService->recordScan(
            $session,
            $student,
            $token,
            $source,
            $request->userAgent(),
            $request->ip()
        );

        $statusHttp = $result['success'] ? 200 : 422;

        return response()->json($result, $statusHttp);
    }

    /**
     * Verify individual or bulk participant status.
     */
    public function verify(Request $request, WorshipSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'participant_id' => 'nullable|exists:worship_session_participants,id',
            'status' => 'nullable|string|in:present,late,absent,excused,exempt',
            'notes' => 'nullable|string|max:255',
            'bulk_statuses' => 'nullable|array',
            'bulk_statuses.*' => 'in:present,late,absent,excused,exempt',
        ]);

        if (! empty($validated['bulk_statuses'])) {
            $this->sessionService->bulkVerify($session, $validated['bulk_statuses'], auth()->id());

            return back()->with('success', 'Status peserta terpilih berhasil diperbarui.');
        }

        if (! empty($validated['participant_id']) && ! empty($validated['status'])) {
            $participant = WorshipSessionParticipant::where('worship_session_id', $session->id)
                ->findOrFail($validated['participant_id']);

            $this->sessionService->verifyParticipant(
                $participant,
                $validated['status'],
                $validated['notes'] ?? null,
                auth()->id()
            );

            return back()->with('success', "Status siswa {$participant->student->full_name} berhasil diverifikasi.");
        }

        return back()->with('error', 'Tidak ada data verifikasi yang dikirimkan.');
    }

    /**
     * Finalize the worship session.
     */
    public function finalize(WorshipSession $session): RedirectResponse
    {
        $this->sessionService->finalizeSession($session, auth()->id());

        return redirect()->route('worship.sessions.show', $session)
            ->with('success', 'Sesi ibadah berhasil diselesaikan dan diverifikasi final. Peserta yang belum scan ditandai Tidak Hadir.');
    }
}
