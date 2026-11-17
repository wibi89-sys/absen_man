<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $target = $request->input('target_role');

        $query = Announcement::with(['creator', 'schoolClass'])->latest('published_at');

        if ($category) {
            $query->where('category', $category);
        }

        if ($target) {
            $query->where('target_role', $target);
        }

        $announcements = $query->paginate(15)->withQueryString();

        return view('communication.announcements.index', compact('announcements', 'category', 'target'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();

        return view('communication.announcements.create', compact('classes'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,academic,attendance,worship,emergency',
            'target_role' => 'required|in:all,parents,teachers,students',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'target_role' => $validated['target_role'],
            'school_class_id' => $validated['school_class_id'] ?? null,
            'is_pinned' => $request->boolean('is_pinned', false),
            'published_at' => $validated['published_at'],
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditService::log(
            'create',
            'announcement',
            $announcement->id,
            null,
            ['title' => $announcement->title, 'target' => $announcement->target_role],
            auth()->id()
        );

        return redirect()->route('communication.announcements.index')
            ->with('success', 'Pengumuman madrasah berhasil dipublikasikan.');
    }

    /**
     * Show form for editing an announcement.
     */
    public function edit(Announcement $announcement): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_id')->orderBy('name')->get();

        return view('communication.announcements.edit', compact('announcement', 'classes'));
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,academic,attendance,worship,emergency',
            'target_role' => 'required|in:all,parents,teachers,students',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'target_role' => $validated['target_role'],
            'school_class_id' => $validated['school_class_id'] ?? null,
            'is_pinned' => $request->boolean('is_pinned', false),
            'published_at' => $validated['published_at'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        AuditService::log(
            'update',
            'announcement',
            $announcement->id,
            null,
            ['title' => $announcement->title],
            auth()->id()
        );

        return redirect()->route('communication.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        AuditService::log(
            'delete',
            'announcement',
            $announcement->id,
            ['title' => $announcement->title],
            null,
            auth()->id()
        );

        $announcement->delete();

        return redirect()->route('communication.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
