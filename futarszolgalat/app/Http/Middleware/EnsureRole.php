<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowed = in_array('admin', $roles, true) && $user?->isAdmin();

        abort_unless($allowed, 403);

        return $next($request);
    }
}
