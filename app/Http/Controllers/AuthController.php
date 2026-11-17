<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('login')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditService::logLogin(
                usernameEntered: $request->input('login'),
                status: 'failed',
                reason: "Terlalu banyak percobaan. Terkunci selama {$seconds} detik."
            );

            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        $loginInput = $request->input('login');
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $credentials = [
            $isEmail ? 'email' : 'username' => $loginInput,
            'password' => $request->input('password'),
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);

            /** @var User $user */
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                AuditService::logLogin(
                    usernameEntered: $loginInput,
                    status: 'failed',
                    userId: $user->id,
                    reason: 'Akun dinonaktifkan oleh administrator.'
                );

                return back()->withErrors([
                    'login' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator Madrasah.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            AuditService::logLogin(
                usernameEntered: $loginInput,
                status: 'success',
                userId: $user->id
            );

            AuditService::log(
                event: 'user_login',
                auditableType: User::class,
                auditableId: $user->id,
                userId: $user->id
            );

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        AuditService::logLogin(
            usernameEntered: $loginInput,
            status: 'failed',
            reason: 'Kombinasi email/username atau kata sandi tidak cocok.'
        );

        return back()->withErrors([
            'login' => 'Email/Username atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('login');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditService::log(
                event: 'user_logout',
                auditableType: User::class,
                auditableId: Auth::id(),
                userId: Auth::id()
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
