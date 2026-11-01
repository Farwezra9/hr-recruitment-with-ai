<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Seluruh action di controller ini (kecuali showSetPassword & setPassword)
 * dibatasi dengan middleware 'auth' + 'admin' langsung di routes/web.php,
 * bukan di sini — supaya rute invite (tanpa login) tidak ikut ter-lock.
 */
class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    /**
     * Admin mengundang user baru: akun dibuat tanpa password, email berisi
     * link unik (invite_token) untuk user mengatur passwordnya sendiri.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,recruiter,hiring_manager,interviewer',
            'phone' => 'nullable|string|max:30',
        ]);

        $data['invite_token'] = Str::random(48);
        $data['invited_at'] = now();
        $data['is_active'] = true;

        $user = User::create($data);

        Mail::to($user->email)->send(new UserInvitedMail($user));

        return redirect()->route('users.index')->with('status', 'Undangan berhasil dikirim ke ' . $user->email);
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

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User dihapus.');
    }

    // --- Alur user baru mengatur password lewat link invite ---

    public function showSetPassword(string $token)
    {
        $user = User::where('invite_token', $token)->firstOrFail();

        return view('auth.set-password', compact('user', 'token'));
    }

    public function setPassword(Request $request, string $token)
    {
        $user = User::where('invite_token', $token)->firstOrFail();

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => bcrypt($data['password']),
            'invite_token' => null,
        ]);

        return redirect()->route('login')->with('status', 'Password berhasil diatur, silakan masuk.');
    }
}
