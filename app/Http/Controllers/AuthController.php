<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected CaptchaService $captchaService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $captcha = $this->captchaService->generate();

        return view('auth.login', [
            'captchaSvg' => $captcha['svg'],
        ]);
    }

    public function refreshCaptcha(): JsonResponse
    {
        $captcha = $this->captchaService->generate();

        return response()->json([
            'status' => 'success',
            'svg' => $captcha['svg'],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'captcha.required' => 'Kode captcha wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        if (! $this->captchaService->validate($request->input('captcha'))) {
            RateLimiter::hit($throttleKey);

            return back()->withErrors([
                'captcha' => 'Kode captcha tidak cocok atau sudah kedaluwarsa. Silakan ketik kode baru.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            ActivityLogger::log('LOGIN', 'auth', 'Berhasil masuk ke sistem SIMAK');

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang di SIMAK DINDIKPORA Banjarnegara, '.Auth::user()->name);
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'email' => 'Kombinasi email dan kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            ActivityLogger::log('LOGOUT', 'auth', 'Keluar dari sistem SIMAK');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem SIMAK.');
    }
}
