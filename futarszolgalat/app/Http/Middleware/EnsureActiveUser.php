<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Ends the session of a user who was deactivated after logging in. */
class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null && ! $request->user()->aktiv) {
            Auth::logout();
            $request->session()->invalidate();
            abort(403, 'A felhasznalo inaktiv.');
        }

        return $next($request);
    }
}
