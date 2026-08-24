<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanCustomerPreviewController extends Controller
{
    public function __invoke()
    {
        return view('clientes.home', [
            'planes' => Plan::with('planCaracteristicas.caracteristica')
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('id')
                ->get(),
            'tieneSuscripcionActiva' => false,
            'tieneSuscripcionPendiente' => false,
            'suscripcionPendiente' => null,
            'pagoPendiente' => null,
            'isAdminPreview' => true,
        ]);
    }
}
