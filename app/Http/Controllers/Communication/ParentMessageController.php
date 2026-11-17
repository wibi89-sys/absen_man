<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\ParentMessage;
use App\Models\ParentModel;
use App\Models\Student;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentMessageController extends Controller
{
    /**
     * Show form to send a message to student's parent.
     */
    public function create(Request $request): View
    {
        $studentId = $request->input('student_id');
        $student = $studentId ? Student::with(['verifiedParents', 'currentClass'])->findOrFail($studentId) : null;

        return view('communication.messages.create', compact('student'));
    }

    /**
     * Store a message to a verified parent.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'parent_id' => 'required|exists:parents,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'category' => 'required|in:academic,attendance,worship,general',
        ]);

        $parent = ParentModel::findOrFail($validated['parent_id']);
        if (! $parent->hasVerifiedChild((int) $validated['student_id'])) {
            return back()->with('error', 'Orang tua belum terverifikasi secara sah dengan siswa ini.');
        }

        $msg = ParentMessage::create([
            'student_id' => $validated['student_id'],
            'parent_id' => $validated['parent_id'],
            'sender_id' => auth()->id(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'category' => $validated['category'],
            'is_read' => false,
        ]);

        AuditService::log(
            'send_parent_message',
            'parent_message',
            $msg->id,
            null,
            ['student_id' => $msg->student_id, 'parent_id' => $msg->parent_id, 'title' => $msg->title],
            auth()->id()
        );

        return back()->with('success', 'Pesan resmi berhasil dikirimkan ke portal orang tua.');
    }
}
