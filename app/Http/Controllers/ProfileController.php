<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginAudit;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile and security settings.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Recent login history for this user
        $loginHistory = LoginAudit::where('user_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get();

        // Recent audit events performed by or related to this user
        $recentActivities = AuditLog::where('user_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get();

        return view('profile.show', compact('user', 'loginHistory', 'recentActivities'));
    }

    /**
     * Update the user's personal details.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $oldValues = [
            'name' => $user->name,
            'phone' => $user->phone,
        ];

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        AuditService::log(
            event: 'profile_updated',
            auditableType: get_class($user),
            auditableId: $user->id,
            oldValues: $oldValues,
            newValues: $validated
        );

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Update the user's password securely.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok dengan data kami.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            event: 'password_changed',
            auditableType: get_class($user),
            auditableId: $user->id
        );

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui. Harap gunakan kata sandi baru saat login berikutnya.');
    }
}
