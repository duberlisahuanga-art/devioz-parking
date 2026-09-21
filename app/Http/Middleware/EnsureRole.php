<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()?->hasRole($role)) {
            return to_route('dashboard')->with('toast', [
                'type' => 'warning',
                'message' => 'Acceso restringido.',
            ]);
        }

        return $next($request);
    }
}
