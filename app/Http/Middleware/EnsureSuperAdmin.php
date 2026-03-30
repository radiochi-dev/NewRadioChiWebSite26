<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isSuperAdminByEmail = $user && $user->email === 'fernandocardonatoro@gmail.com';
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $isSuperAdminByRole = $hasRoleColumn && $user && $user->role === 'SuperAdmin';

        if (! $user || (! $isSuperAdminByRole && ! $isSuperAdminByEmail)) {
            abort(403);
        }

        return $next($request);
    }
}
