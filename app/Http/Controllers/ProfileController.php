<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /** Token integrasi berlaku 90 hari. */
    protected const TOKEN_DAYS = 90;

    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'tokens' => $user->isAdmin() ? $user->tokens()->latest()->get() : collect(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', PasswordPolicy::rule()],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();

        // Cabut semua token API dan perbarui sesi
        $user->tokens()->delete();
        $request->session()->regenerate();

        Audit::log('password.changed', $user, 'Password diganti oleh pemilik akun');

        return back()->with('status', 'Password berhasil diubah. Token API lama dicabut.');
    }

    /** Token API (Sanctum) untuk integrasi sistem lain. Hanya admin. */
    public function storeToken(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100']);

        $token = $request->user()->createToken($data['name'], ['*'], now()->addDays(self::TOKEN_DAYS));

        Audit::log('token.created', $request->user(), "Token API dibuat: {$data['name']}", ['expires_in_days' => self::TOKEN_DAYS]);

        return back()->with('status', 'Token dibuat (berlaku ' . self::TOKEN_DAYS . ' hari). Salin sekarang, token tidak akan ditampilkan lagi.')
            ->with('new_token', $token->plainTextToken);
    }

    public function destroyToken(Request $request, int $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        Audit::log('token.deleted', $request->user(), "Token API dihapus (#{$tokenId})");

        return back()->with('status', 'Token dihapus.');
    }
}
