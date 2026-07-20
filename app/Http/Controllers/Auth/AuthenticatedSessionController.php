<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Notifications\TwoFactorCodeNotification;
use App\Providers\RouteServiceProvider;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $a = random_int(1, 20);
        $b = random_int(1, 20);
        session()->put('captcha_answer', $a + $b);

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'captcha_expression' => "$a + $b",
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();

            $user = Auth::user();

            // Si el usuario tiene 2FA activado, enviar código y redirigir
            if ($user->two_factor_enabled) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                cache()->put("2fa_user_{$user->id}", $code, 600);

                $user->notify(new TwoFactorCodeNotification($code));

                Auth::logout();
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_email', $user->email);

                return redirect()->route('login.2fa');
            }

            $request->session()->regenerate();

            // Registrar login exitoso
            AuditLogger::login($user->id, [
                'user_email' => $user->email,
                'user_name' => $user->name,
                'user_role' => $user->role?->name ?? 'Sin rol',
            ]);

            if ($user->must_change_password) {
                return redirect()->route('password.force-change');
            }

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (\Exception $e) {
            // Registrar intento fallido de login
            AuditLogger::failedLogin($request->email, [
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Registrar logout antes de cerrar sesión
        if ($user) {
            AuditLogger::logout($user->id, [
                'user_email' => $user->email,
                'user_name' => $user->name,
                'user_role' => $user->role?->name ?? 'Sin rol',
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
