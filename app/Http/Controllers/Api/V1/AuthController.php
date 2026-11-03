<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends ApiController
{
    /** Token login berlaku 7 hari. */
    protected const TOKEN_DAYS = 7;

    /** POST /api/v1/auth/login -> token Bearer */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        // Kunci per email + IP: 5 percobaan gagal = terkunci 5 menit
        $key = 'api-login:' . mb_strtolower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            Audit::log('api.lockout', null, 'Login API terkunci sementara', ['email' => $data['email']]);

            return response()->json([
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.',
            ], 429);
        }

        $user = User::where('email', $data['email'])->first();

        // Hash::check tetap dijalankan walau user tidak ada, supaya waktu respons tidak membocorkan email terdaftar
        $valid = Hash::check($data['password'], (string) ($user?->password ?? '$2y$12$' . str_repeat('a', 53)));

        if (! $user || ! $user->is_active || ! $valid) {
            RateLimiter::hit($key, 300);
            Audit::log('api.login_failed', null, 'Login API gagal', ['email' => $data['email']]);

            return response()->json(['message' => 'Email atau password salah, atau akun dinonaktifkan.'], 401);
        }

        RateLimiter::clear($key);

        $token = $user->createToken($data['device_name'] ?? 'api-client', ['*'], now()->addDays(self::TOKEN_DAYS));
        Audit::log('api.login', $user, 'Login API berhasil', ['device' => $data['device_name'] ?? 'api-client'], $user);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /** Mencabut token yang sedang dipakai. */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        Audit::log('api.logout', null, 'Logout API');

        return response()->noContent();
    }
}
