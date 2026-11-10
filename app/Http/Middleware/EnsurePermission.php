<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasRole('Super Administrador') || collect($permissions)->contains(
                fn (string $permission) => $user->hasPermission($permission)
            )),
            403,
            'No tienes permisos para acceder a esta página.'
        );

        return $next($request);
    }
}
