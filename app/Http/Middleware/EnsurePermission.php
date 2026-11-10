<?php

namespace App\Http\Middleware;

use App\Support\CampaignDocumentAccess;
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
            ) || (in_array('gestionar-empresas', $permissions, true)
                && CampaignDocumentAccess::allowsRequest($user, $request))),
            403,
            'No tienes permisos para acceder a esta página.'
        );

        return $next($request);
    }
}
