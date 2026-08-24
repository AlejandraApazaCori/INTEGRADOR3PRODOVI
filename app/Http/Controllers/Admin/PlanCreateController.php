<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caracteristica;
use App\Models\Plan;

class PlanCreateController extends Controller
{
    public function __invoke()
    {
        $caracteristicas = Caracteristica::orderBy('nombre')->get();
        $plan = new Plan([
            'nombre' => '',
            'subtitulo' => '',
            'precio' => 0,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
            'orden' => 0,
            'activo' => true,
            'descripcion' => '',
        ]);
        $plan->setRelation('planCaracteristicas', collect());

        return view('administrador.planes.edit', [
            'plan' => $plan,
            'caracteristicas' => $caracteristicas,
            'isCreating' => true,
        ]);
    }
}
