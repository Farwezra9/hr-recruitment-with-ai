<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pemakaian: ->middleware('role:admin,recruiter'). Admin selalu lolos.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->is_active && ($user->isAdmin() || in_array($user->role, $roles, true)),
            403,
            'Kamu tidak punya akses ke halaman ini.'
        );

        return $next($request);
    }
}
