<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitedMail;
use App\Models\JobPosting;
use App\Models\User;
use App\Support\Audit;
use App\Support\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Semua action (kecuali showSetPassword & setPassword) dibatasi middleware
 * 'auth' + 'role:admin' di routes/web.php.
 */
class UserManagementController extends Controller
{
    /** Link undangan berlaku 7 hari. */
    protected const INVITE_DAYS = 7;

    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,recruiter,hiring_manager,interviewer',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = User::create($data + [
            'invite_token' => Str::random(48),
            'invited_at' => now(),
            'is_active' => true,
        ]);

        $this->sendInvite($user);
        Audit::log('user.invited', $user, "Mengundang {$user->email} sebagai {$user->role}");

        return redirect()->route('users.index')->with('status', 'Undangan dikirim ke ' . $user->email);
    }

    public function resendInvite(User $user)
    {
        abort_unless($user->invite_token, 422, 'User ini sudah mengaktifkan akunnya.');

        $user->update(['invite_token' => Str::random(48), 'invited_at' => now()]);
        $this->sendInvite($user);
        Audit::log('user.invite_resent', $user, "Undangan dikirim ulang ke {$user->email}");

        return back()->with('status', 'Undangan dikirim ulang ke ' . $user->email);
    }

    protected function sendInvite(User $user): void
    {
        try {
            Mail::to($user->email)->send(new UserInvitedMail($user));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email undangan', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,recruiter,hiring_manager,interviewer',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        // Jangan sampai admin mengunci dirinya sendiri
        if ($user->id === $request->user()->id && ($data['role'] !== 'admin' || ! $data['is_active'])) {
            return back()->withErrors(['role' => 'Kamu tidak bisa menurunkan role atau menonaktifkan akunmu sendiri.']);
        }

        $before = $user->only(['role', 'is_active', 'email']);
        $user->update($data);

        // Role berubah atau akun dinonaktifkan: cabut token API supaya akses lama langsung berhenti
        if ($before['role'] !== $user->role || ($before['is_active'] && ! $user->is_active)) {
            $user->tokens()->delete();
        }

        Audit::log('user.updated', $user, "Mengubah data {$user->email}", ['before' => $before, 'after' => $user->only(['role', 'is_active', 'email'])]);

        return redirect()->route('users.index')->with('status', 'Data user diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menghapus akun sendiri.');

        // User yang punya riwayat (lowongan/interview) dinonaktifkan saja agar datanya tidak rusak.
        $hasHistory = JobPosting::withTrashed()->where('created_by', $user->id)->exists()
            || $user->interviews()->exists();

        $user->tokens()->delete();

        if ($hasHistory) {
            $user->update(['is_active' => false]);
            Audit::log('user.deactivated', $user, "Menonaktifkan {$user->email} (punya riwayat data)");

            return redirect()->route('users.index')->with('status', 'User punya riwayat data, jadi dinonaktifkan (tidak dihapus).');
        }

        Audit::log('user.deleted', $user, "Menghapus {$user->email}");
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User dihapus.');
    }

    // --- Alur user baru mengatur password lewat link undangan ---

    protected function findInvited(string $token): User
    {
        return User::where('invite_token', $token)
            ->where('invited_at', '>=', now()->subDays(self::INVITE_DAYS))
            ->firstOrFail();
    }

    public function showSetPassword(string $token)
    {
        $user = $this->findInvited($token);

        return view('auth.set-password', compact('user', 'token'));
    }

    public function setPassword(Request $request, string $token)
    {
        $user = $this->findInvited($token);

        $data = $request->validate([
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rule()],
        ]);

        $user->update(['password' => $data['password'], 'invite_token' => null]);
        Audit::log('user.activated', $user, 'Akun diaktifkan lewat link undangan', [], $user);

        return redirect()->route('login')->with('status', 'Password berhasil diatur, silakan masuk.');
    }
}
