<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Suscripcion;
use App\Models\Pago;
use Carbon\Carbon;

class ClienteController extends Controller
{


    public function home()
    {
        $user = Auth::user();
        $planes = Plan::all();

        $data = [
            'planes' => $planes,
            'tieneSuscripcionActiva' => false,
            'tieneSuscripcionPendiente' => false,
            'suscripcionPendiente' => null,
            'pagoPendiente' => null,
        ];

        if ($user) {
            $data['tieneSuscripcionActiva'] = Suscripcion::where('usuario_id', $user->id)
                ->where('estado', 'activa')
                ->where('fecha_fin', '>', now())
                ->exists();

            $data['pagoPendiente'] = Pago::with(['codigoPago', 'plan', 'suscripcion'])
                ->where('usuario_id', $user->id)
                ->where('estado', 'pendiente')
                ->whereHas('suscripcion', fn ($query) => $query->where('estado', 'pendiente'))
                ->latest('id')
                ->first();
            $data['suscripcionPendiente'] = $data['pagoPendiente']?->suscripcion;
            $data['tieneSuscripcionPendiente'] = $data['pagoPendiente'] !== null;
        }

        return view('clientes.home', $data);
    }
    public function dashboard()
    {
        $user = Auth::user();

        $suscripcionActiva = Suscripcion::with('plan')
            ->where('usuario_id', $user->id)
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->firstOrFail();

        $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
        $diasRestantes = now()->diffInDays($fechaFin, false);
        $diasTotales = $suscripcionActiva->fecha_inicio->diffInDays($fechaFin);
        $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;

        // NUEVO: Obtener las empresas del usuario
        $empresas = $user->empresas;

        // CARGAR DATOS DE ANALITICAS PARA EL DASHBOARD (Últimos 7 días)
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData['last7days'] ?? [];
        } else {
            $data = [];
        }

        return view('clientes.dashboard', compact(
            'user',
            'suscripcionActiva',
            'diasRestantes',
            'porcentajeRestante',
            'empresas',
            'data' // Pasamos los datos de analíticas a la vista
        ));
    }

    public function brief()
    {
        return view('clientes.brief');
    }

    public function analiticas()
    {
        return view('clientes.analiticas');
    }
    public function miCuenta()
    {
        $user = Auth::user();

        // Obtener la suscripción activa si existe
        $suscripcionActiva = Suscripcion::with('plan')
            ->where('usuario_id', $user->id)
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->first();

        // Calcular días restantes si hay suscripción activa
        $diasRestantes = 0;
        $porcentajeRestante = 0;

        if ($suscripcionActiva) {
            $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
            $diasRestantes = now()->diffInDays($fechaFin, false);
            $diasTotales = $suscripcionActiva->fecha_inicio->diffInDays($fechaFin);
            $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;
        }

        // NUEVO: Obtener todas las empresas del usuario
        $empresas = $user->empresas;

        return view('clientes.micuenta', compact(
            'user',
            'suscripcionActiva',
            'diasRestantes',
            'porcentajeRestante',
            'empresas'
        ));
    }



}



