<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        // Resolver cédula o correo → usuario
        $login = $request->string('identifier');
        $user = \App\Models\User::findByIdentifier($login);

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => 'No se encontró una cuenta con esa cédula o correo.',
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (\Exception $e) {
            return back()->with('email_error', true)->with('status', 'No se pudo enviar el correo. Por favor, contacta al administrador del sistema para restablecer tu contraseña.');
        }

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->with('email_error', true)->with('status', 'No se pudo enviar el correo. Por favor, contacta al administrador del sistema para restablecer tu contraseña.');
    }
}
