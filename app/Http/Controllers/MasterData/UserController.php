<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users with filter and search.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $roleName = $request->input('role');
        $status = $request->input('status');

        $query = User::with(['roles', 'teacher', 'student', 'schoolParent']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleName) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('display_name')->get();

        return view('master.users.index', compact('users', 'roles', 'search', 'roleName', 'status'));
    }

    /**
     * Reset a user's password directly by Administrator.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(
            event: 'user_password_reset_by_admin',
            auditableType: User::class,
            auditableId: $user->id,
            newValues: ['reset_by' => $request->user()->name]
        );

        return back()->with('success', "Kata sandi untuk pengguna {$user->name} berhasil direset.");
    }

    /**
     * Toggle a user's active status (activate / deactivate).
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $oldStatus = $user->is_active;
        $newStatus = ! $oldStatus;

        $user->update([
            'is_active' => $newStatus,
        ]);

        AuditService::log(
            event: $newStatus ? 'user_activated' : 'user_deactivated',
            auditableType: User::class,
            auditableId: $user->id,
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $newStatus]
        );

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Update user roles.
     */
    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->roles()->sync($validated['role_ids']);

        AuditService::log(
            event: 'user_roles_updated',
            auditableType: User::class,
            auditableId: $user->id,
            newValues: ['roles' => $validated['role_ids']]
        );

        return back()->with('success', "Peran untuk {$user->name} berhasil diperbarui.");
    }
}
