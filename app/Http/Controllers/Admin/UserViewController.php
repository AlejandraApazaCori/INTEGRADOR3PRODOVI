<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Cliente\PlanController;
use App\Models\User;
use Carbon\Carbon;

class UserViewController extends Controller
{
    public function show($id)
    {
        $user = User::with(['roles', 'suscripciones.plan.caracteristicas', 'empresas'])->findOrFail($id);

        $suscripcionActiva = $user->suscripciones()
            ->with('plan.caracteristicas')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->first();

        $campaniaActual = $user->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();

        $diasRestantes = 0;
        $porcentajeRestante = 0;

        if ($suscripcionActiva) {
            $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
            $diasRestantes = now()->diffInDays($fechaFin, false);
            $diasTotales = $suscripcionActiva->fecha_inicio->diffInDays($fechaFin);
            $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;
        }

        $empresas = $user->empresas;
        if ($user->socialAccountsTableExists()) {
            $empresas->load('socialAccounts');
        }
        $globalSocialAccounts = $user->linkedSocialAccounts();
        $planResponse = app(PlanController::class)->getPlanContratado((int) $user->id);
        $accountPlans = $planResponse->getStatusCode() === 200
            ? $planResponse->getData(true)
            : ['plan' => null, 'plans' => []];
        $accountUser = $user;
        $adminPreview = true;

        return view('clientes.micuenta', compact(
            'user',
            'accountUser',
            'adminPreview',
            'accountPlans',
            'suscripcionActiva',
            'campaniaActual',
            'diasRestantes',
            'porcentajeRestante',
            'empresas',
            'globalSocialAccounts'
        ));
    }

    public function campaignAnalytics($id)
    {
        $user = User::findOrFail($id);

        $campaniaActual = $user->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();

        $data = $this->loadAnalyticsData('last30days', (int) $id);

        return view('administrador.campanias.analiticasusuario', compact('user', 'campaniaActual', 'data'));
    }

    private function loadAnalyticsData(string $periodKey, int $userId): array
    {
        $campaignJsonPath = resource_path('data/analiticas_por_campania.json');
        if (file_exists($campaignJsonPath)) {
            $campaignJson = json_decode(file_get_contents($campaignJsonPath), true);
            $campaignData = $campaignJson['usuarios'][(string) $userId]['periodos'][$periodKey] ?? null;

            if (is_array($campaignData)) {
                return $campaignData;
            }
        }

        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            return $allData[$periodKey] ?? [];
        }

        return [];
    }
}
