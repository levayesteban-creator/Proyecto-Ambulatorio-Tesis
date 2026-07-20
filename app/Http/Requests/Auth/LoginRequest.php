<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required', function ($attribute, $value, $fail) {
                if ((int) $value !== (int) session('captcha_answer')) {
                    $fail('La respuesta de seguridad es incorrecta.');
                }
            }],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->getCredentials();

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            $remaining = RateLimiter::remaining($this->throttleKey(), 5);
            throw ValidationException::withMessages([
                'email' => trans('auth.failed') . " Te quedan {$remaining} intento(s).",
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    protected function getCredentials(): array
    {
        $login = $this->string('email');
        $field = str_contains($login, '@') ? 'email' : 'id_number';

        return [$field => $login, 'password' => $this->string('password')];
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
