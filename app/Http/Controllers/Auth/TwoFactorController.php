<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function challenge(): Response|RedirectResponse
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge', [
            'email' => session('2fa_email'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $cached = cache("2fa_user_{$userId}");
        if (!$cached || $cached !== $request->code) {
            return back()->withErrors([
                'code' => 'El código ingresado es inválido o ha expirado.',
            ]);
        }

        Auth::loginUsingId($userId);
        cache()->forget("2fa_user_{$userId}");
        session()->forget(['2fa_user_id', '2fa_email']);
        session()->regenerate();

        AuditLogger::login($userId, [
            'method' => '2fa',
        ]);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
