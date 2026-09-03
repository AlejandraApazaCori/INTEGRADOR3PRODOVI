<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudContacto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudContactoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        $servicios = [
            'publicidad' => 'Publicidad y marketing',
            'social' => 'Redes sociales',
            'audiovisual' => 'Producción audiovisual',
            'eventos' => 'Planificación de eventos',
            'bodas' => 'Planificación de bodas',
            'influencers' => 'Manejo de influencers',
            'other' => 'Otro',
        ];

        $solicitudesQuery = SolicitudContacto::query();

        if ($request->filled('buscar')) {
            $termino = trim((string) $request->input('buscar'));

            $solicitudesQuery->where(function (Builder $query) use ($termino) {
                $query->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('correo', 'like', "%{$termino}%")
                    ->orWhere('telefono', 'like', "%{$termino}%")
                    ->orWhere('mensaje', 'like', "%{$termino}%");
            });
        }

        $servicio = (string) $request->input('servicio', '');
        if (array_key_exists($servicio, $servicios)) {
            $solicitudesQuery->where('servicio', $servicio);
        }

        $envio = (string) $request->input('envio', '');
        if ($envio === 'enviado') {
            $solicitudesQuery->whereNotNull('correo_enviado_at');
        } elseif ($envio === 'no_enviado') {
            $solicitudesQuery->whereNull('correo_enviado_at');
        }

        $solicitudes = $solicitudesQuery
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => SolicitudContacto::count(),
            'este_mes' => SolicitudContacto::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'correo_enviado' => SolicitudContacto::whereNotNull('correo_enviado_at')->count(),
            'correo_pendiente' => SolicitudContacto::whereNull('correo_enviado_at')->count(),
        ];

        return view('administrador.solicitudes-contacto.index', compact(
            'solicitudes',
            'servicios',
            'stats'
        ));
    }
}
