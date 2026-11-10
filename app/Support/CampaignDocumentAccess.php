<?php

namespace App\Support;

use App\Models\Campania;
use App\Models\PlanMarketing;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignDocumentAccess
{
    private const READ_ROUTES = [
        'administrador.empresas.cuestionario.show',
        'administrador.empresas.cuestionario.pdf',
        'administrador.empresas.reporte',
        'administrador.empresas.reporte.pdf',
        'administrador.empresas.planes-marketing.show',
        'administrador.empresas.planes-marketing.download-pdf',
    ];

    public static function allowsRequest(User $user, Request $request): bool
    {
        if (! in_array($request->route()?->getName(), self::READ_ROUTES, true)
            || ! $user->hasPermission('gestionar-campanas')) {
            return false;
        }

        $plan = $request->route('planMarketing');
        $empresaId = $plan !== null
            ? PlanMarketing::query()->find($plan instanceof PlanMarketing ? $plan->getKey() : $plan)?->empresa_id
            : $request->route('id');

        return $empresaId !== null && self::allowsCompany($user, (int) $empresaId);
    }

    public static function allowsCompany(User $user, int $empresaId): bool
    {
        if ($user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            return true;
        }

        return Campania::query()->visibleToStaff($user)
            ->where(function ($query) use ($empresaId) {
                $query->whereHas('suscripcion.empresa', fn ($empresa) => $empresa->whereKey($empresaId))
                    ->orWhereHas('empresas', fn ($empresa) => $empresa->whereKey($empresaId));
            })->exists();
    }
}
