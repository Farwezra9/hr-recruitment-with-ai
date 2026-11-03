<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Mail\UserInvitedMail;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** Hanya admin (dibatasi di routes). */
class UserController extends ApiController
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = User::query()
            ->when($term !== '', fn ($q) => $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            }))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->orderBy('name');

        return UserResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    /** Membuat akun lewat undangan email (user mengatur passwordnya sendiri). */
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

        try {
            Mail::to($user->email)->send(new UserInvitedMail($user));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email undangan', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|required|in:admin,recruiter,hiring_manager,interviewer',
            'phone' => 'sometimes|nullable|string|max:30',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($user->id === $request->user()->id
            && (($data['role'] ?? 'admin') !== 'admin' || ($data['is_active'] ?? true) === false)) {
            return response()->json(['message' => 'Kamu tidak bisa menurunkan role atau menonaktifkan akunmu sendiri.'], 409);
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 409);
        }

        $hasHistory = JobPosting::withTrashed()->where('created_by', $user->id)->exists() || $user->interviews()->exists();

        if ($hasHistory) {
            $user->update(['is_active' => false]);
            $user->tokens()->delete();

            return response()->json(['message' => 'User punya riwayat data, jadi dinonaktifkan (tidak dihapus).', 'data' => new UserResource($user)], 200);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
