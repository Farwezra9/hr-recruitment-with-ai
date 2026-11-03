<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\PasswordPolicy;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Kunci per email + IP: 5 percobaan gagal = terkunci 5 menit (selain throttle per IP di route)
        $key = 'login:' . mb_strtolower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            Audit::log('auth.lockout', null, 'Login terkunci sementara', ['email' => $credentials['email']]);

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.',
            ])->onlyInput('email');
        }

        // Akun nonaktif tidak boleh masuk
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($key, 300);
            Audit::log('auth.login_failed', null, 'Login gagal', ['email' => $credentials['email']]);

            return back()->withErrors(['email' => 'Email atau password salah, atau akun dinonaktifkan.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate(); // cegah session fixation
        Audit::log('auth.login', Auth::user(), 'Login berhasil');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Audit::log('auth.logout', $request->user(), 'Logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // --- Lupa password ---

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim link reset password', ['error' => $e->getMessage()]);
        }

        // Pesan sama apa pun hasilnya, supaya email yang terdaftar tidak bisa ditebak orang lain.
        return back()->with('status', 'Jika email terdaftar, link untuk mengatur ulang password sudah dikirim.');
    }

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rule()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Cabut semua token API: password berubah berarti sesi lama tidak lagi dipercaya
                $user->tokens()->delete();

                event(new PasswordReset($user));
                Audit::log('auth.password_reset', $user, 'Password diatur ulang lewat email', [], $user);
            }
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan masuk.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Link reset tidak valid atau sudah kedaluwarsa. Minta link baru.']);
    }
}
