<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\User;
use App\Models\Pago;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Services\CommunityManagerRecommender;
use App\Services\SocialContentPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CampañasController extends Controller
{
    public function index()
    {
        // 1. Obtener usuarios con suscripción activa que no tienen campaña activa
        $clientesSinCampania = Pago::with(['usuario', 'suscripcion.empresa', 'plan'])
            ->where('estado', 'completado')
            ->whereHas('suscripcion', function($query) {
                $query->where('estado', 'activa')
                    ->where(function ($vigenciaQuery) {
                        $vigenciaQuery->whereNull('vigencia_activada_at')
                            ->orWhere('fecha_fin', '>', now());
                    });
            })
            ->whereDoesntHave('suscripcion.campanias', function($query) {
                $query->where('fecha_fin', '>', now())
                    ->whereIn('estado', ['activa', 'pausada']);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function($pago) {
                $empresa = $pago->suscripcion->empresa;

                return [
                    'id' => $pago->usuario->id,
                    'suscripcion_id' => $pago->suscripcion->id,
                    'nombre' => $pago->usuario->name,
                    'email' => $pago->usuario->email,
                    'plan' => $pago->plan->nombre ?? 'Sin plan',
                    'fecha_fin_suscripcion' => $pago->suscripcion->vigencia_activada_at ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'Pendiente de campaña',
                    'fecha_fin_suscripcion_raw' => $pago->suscripcion->vigencia_activada_at ? $pago->suscripcion->fecha_fin->format('Y-m-d') : null,
                    'tiene_empresa' => $empresa !== null,
                    'empresa_id' => $empresa ? $empresa->id : null,
                ];
            })
            ->sortByDesc('fecha_fin_suscripcion_raw')
            ->values();

        // 2. Obtener campañas activas
        $campaniasActivas = Campania::with(['cliente', 'communityManager'])
            ->where('fecha_fin', '>', now())
            ->whereIn('estado', ['activa', 'pausada'])
            ->orderBy('fecha_fin', 'desc')
            ->get();

        // 3. Obtener campañas finalizadas
        $campaniasFinalizadas = Campania::with(['cliente', 'communityManager'])
            ->where(function($query) {
                $query->where('fecha_fin', '<=', now())
                    ->orWhere('estado', 'finalizada');
            })
            ->orderBy('fecha_fin', 'desc')
            ->get();

        $aniosFinalizadasDisponibles = $campaniasFinalizadas
            ->pluck('fecha_fin')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->year)
            ->unique()
            ->sortDesc()
            ->values();

        $mesesFinalizadasDisponibles = $campaniasFinalizadas
            ->pluck('fecha_fin')
            ->filter()
            ->map(function ($fecha) {
                $carbon = Carbon::parse($fecha);

                return [
                    'numero' => $carbon->month,
                    'nombre' => ucfirst($carbon->translatedFormat('F')),
                ];
            })
            ->unique('numero')
            ->sortBy('numero')
            ->values();

        // 4. Obtener community managers para el formulario
        $communityManagers = User::whereHas('roles', function($query) {
            $query->where('nombre_rol', 'Community Manager');
        })->get();

        return view('administrador.campañas.index', [
            'clientesSinCampania' => $clientesSinCampania,
            'campaniasActivas' => $campaniasActivas,
            'campaniasFinalizadas' => $campaniasFinalizadas,
            'aniosFinalizadasDisponibles' => $aniosFinalizadasDisponibles,
            'mesesFinalizadasDisponibles' => $mesesFinalizadasDisponibles,
            'communityManagers' => $communityManagers,
            'adminActual' => Auth::user()
        ]);
    }

    public function analiticas()
    {
        return view('administrador.campañas.analiticas');
    }

  public function guardar(Request $request)
{
    \Log::info('Datos recibidos:', $request->all());
    
    try {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'usuario_cliente_id' => 'required|exists:users,id',
            'community_manager_id' => 'required|exists:users,id',
            'suscripcion_id' => 'required|exists:suscripciones,id',
        ]);

        // Obtener la suscripción del cliente para las fechas
        $pago = Pago::with('suscripcion')
            ->where('usuario_id', $request->usuario_cliente_id)
            ->where('suscripcion_id', $request->suscripcion_id)
            ->where('estado', 'completado')
            ->whereHas('suscripcion', function($query) {
                $query->where('estado', 'activa')
                    ->where(function ($vigenciaQuery) {
                        $vigenciaQuery->whereNull('vigencia_activada_at')
                            ->orWhere('fecha_fin', '>', now());
                    });
            })
            ->first();

        if (!$pago || !$pago->suscripcion) {
            return redirect()->back()
                ->with('error', 'El cliente no tiene una suscripción activa válida')
                ->withInput();
        }

        // Añade esto antes de crear la campaña
        \Log::info('Datos de la campaña a crear:', [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'usuario_cliente_id' => $request->usuario_cliente_id,
            'community_manager_id' => $request->community_manager_id,
            'usuario_creador_id' => Auth::id(),
        ]);

        // Crear la campaña
        $campania = DB::transaction(function () use ($request, $pago) {
            $suscripcion = $pago->suscripcion()->lockForUpdate()->firstOrFail();

            if (! $suscripcion->vigencia_activada_at) {
                $inicioVigencia = now();
                $suscripcion->update([
                    'fecha_inicio' => $inicioVigencia,
                    'fecha_fin' => $inicioVigencia->copy()->addMonthNoOverflow(),
                    'vigencia_activada_at' => $inicioVigencia,
                ]);
            }

            return Campania::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => now(),
                'fecha_fin' => $suscripcion->fecha_fin,
                'usuario_creador_id' => Auth::id(),
                'community_manager_id' => $request->community_manager_id,
                'usuario_cliente_id' => $request->usuario_cliente_id,
                'suscripcion_id' => $suscripcion->id,
                'estado' => 'activa',
            ]);
        });

        \Log::info('Campaña creada con ID: ' . $campania->id);

        return redirect()->route('administrador.campañas.index')
            ->with('success', 'Campaña creada exitosamente');
            
    } catch (\Exception $e) {
        \Log::error('Error al crear campaña: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Ha ocurrido un error al crear la campaña: ' . $e->getMessage())
            ->withInput();
    }
}

    public function activar(Campania $campania)
{
    // Verificar que el cliente tenga suscripción activa
    $tieneSuscripcionActiva = $campania->cliente->suscripciones()
        ->where('estado', 'activa')
        ->where('fecha_fin', '>', now())
        ->exists();

    if (!$tieneSuscripcionActiva) {
        return redirect()->back()
            ->with('error', 'El cliente no tiene una suscripción activa para reactivar la campaña');
    }

    // Actualizar la campaña
    $campania->update([
        'estado' => 'activa',
        'fecha_fin' => $campania->cliente->suscripciones()->where('estado', 'activa')->first()->fecha_fin
    ]);

    return redirect()->back()
        ->with('success', 'Campaña reactivada exitosamente');
}
    // Mostrar detalles de una campaña
    public function show(Campania $campania)
    {
        return view('administrador.campañas.show', compact('campania'));
    }

    // Mostrar formulario de edición
    public function edit(Campania $campania)
    {
        $communityManagers = User::whereHas('roles', function($query) {
            $query->where('nombre_rol', 'Community Manager');
        })->get();

        return view('administrador.campañas.edit', [
            'campania' => $campania,
            'communityManagers' => $communityManagers
        ]);
    }

    // Actualizar campaña
    public function update(Request $request, Campania $campania)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'community_manager_id' => 'required|exists:users,id',
            'estado' => 'required|in:activa,pausada,finalizada',
            'fecha_fin' => 'required|date'
        ]);

        $campania->update($validated);

        return redirect()->route('administrador.campañas.index')
            ->with('success', 'Campaña actualizada exitosamente');
    }

    public function obtenerPlanIA(Request $request, $usuario_id)
    {
        try {
            $request->validate(['suscripcion_id' => 'required|integer|exists:suscripciones,id']);
            $pago = Pago::with('suscripcion.empresa')
                ->where('usuario_id', $usuario_id)
                ->where('suscripcion_id', $request->integer('suscripcion_id'))
                ->where('estado', 'completado')
                ->firstOrFail();
            $empresa = $pago->suscripcion->empresa;

            if (!$empresa) {
                return response()->json(['error' => 'El cliente no tiene una empresa registrada.'], 404);
            }

            $plan = PlanMarketing::where('empresa_id', $empresa->id)
                ->where('suscripcion_id', $pago->suscripcion_id)
                ->latest()
                ->first();

            if (!$plan) {
                return response()->json(['error' => 'No se encontró un plan de marketing para este cliente.'], 404);
            }

            $contenido = (string) $plan->contenido;
            $diagnostico = $this->extraerSeccionPlan($contenido, [
                'diagnostico estrategico',
                'resumen ejecutivo',
                'conclusiones',
                'recomendaciones finales',
            ]);
            $objetivos = $this->extraerSeccionPlan($contenido, ['objetivos smart', 'objetivos']);

            $descripcionBreve = $this->resumirSeccionCampania($diagnostico, 1, 320);
            $objetivosBreves = $this->resumirSeccionCampania($objetivos, 3, 650, true);

            if ($descripcionBreve !== '') {
                $descripcionBreve = Str::limit($descripcionBreve, 235, '')
                    . ' Enfoque: Facebook, Instagram y TikTok.';
            }

            $partesDescripcion = [];
            if ($descripcionBreve !== '') {
                $partesDescripcion[] = "DESCRIPCIÓN:\n" . $descripcionBreve;
            }
            if ($objetivosBreves !== '') {
                $partesDescripcion[] = "OBJETIVOS:\n" . $objetivosBreves;
            }

            // Si el documento cambia de formato, usar su contenido limpio como respaldo.
            if ($partesDescripcion === []) {
                $partesDescripcion[] = $this->resumirSeccionCampania($contenido, 3, 850);
            }

            $descripcion = trim(implode("\n\n", array_filter($partesDescripcion)));

            if ($descripcion === '') {
                return response()->json([
                    'error' => 'El plan de marketing no contiene información suficiente para crear la campaña.',
                ], 422);
            }
            
            return response()->json([
                'nombre' => 'Campaña Estratégica: ' . $empresa->nombre_empresa,
                'descripcion' => mb_substr($descripcion, 0, 2500)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el plan: ' . $e->getMessage()], 500);
        }
    }

    private function extraerSeccionPlan(string $contenido, array $titulosBuscados): string
    {
        preg_match_all(
            '/^##(?!#)\s*(.+?)\s*\R(.*?)(?=^##(?!#)\s|\z)/msu',
            str_replace(["\r\n", "\r"], "\n", $contenido),
            $secciones,
            PREG_SET_ORDER
        );

        $titulosNormalizados = array_map(
            fn (string $titulo) => Str::lower(Str::ascii($titulo)),
            $titulosBuscados
        );

        foreach ($secciones as $seccion) {
            $titulo = preg_replace('/^[\s*_`#]*(?:\d+[\s.)\-:]*)?/u', '', $seccion[1]);
            $titulo = preg_replace('/[*_`]+/u', '', (string) $titulo);
            $tituloNormalizado = Str::lower(Str::ascii(trim((string) $titulo)));

            foreach ($titulosNormalizados as $buscado) {
                if (str_contains($tituloNormalizado, $buscado)) {
                    return trim($seccion[2]);
                }
            }
        }

        return '';
    }

    public function recomendarCommunityManager(Request $request, CommunityManagerRecommender $recommender)
    {
        $validated = $request->validate([
            'suscripcion_id' => ['required', 'integer', 'exists:suscripciones,id'],
            'campania_id' => ['nullable', 'integer', 'exists:campanias,id'],
        ]);

        $suscripcion = Suscripcion::findOrFail($validated['suscripcion_id']);
        $inicio = now()->startOfDay();
        $fin = $suscripcion->vigencia_activada_at && $suscripcion->fecha_fin?->isFuture()
            ? $suscripcion->fecha_fin->copy()->startOfDay()
            : $inicio->copy()->addMonthNoOverflow();

        $managers = User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))
            ->with(['campaniasComoCM' => fn ($query) => $query
                ->whereIn('estado', ['activa', 'pausada'])
                ->where('fecha_fin', '>=', $inicio)
                ->when($validated['campania_id'] ?? null, fn ($campaignQuery, $campaignId) => $campaignQuery->where('id', '!=', $campaignId))
                ->orderBy('fecha_fin')])
            ->get();

        $ranking = $recommender->rank($managers, $inicio, $fin);

        if ($ranking->isEmpty()) {
            return response()->json(['message' => 'No hay Community Managers disponibles para recomendar.'], 404);
        }

        $recommended = $ranking->first();
        $recommended['reason'] = $recommended['active_campaigns'] === 0
            ? 'No tiene campañas activas y dispone de la menor carga proyectada.'
            : sprintf(
                'Gestiona %d %s; su carga proyectada es %.2f, %d %s en los próximos 14 días y su siguiente liberación es el %s.',
                $recommended['active_campaigns'],
                $recommended['active_campaigns'] === 1 ? 'campaña activa' : 'campañas activas',
                $recommended['average_load'],
                $recommended['ending_soon'],
                $recommended['ending_soon'] === 1 ? 'finaliza' : 'finalizan',
                $recommended['next_release']
            );

        return response()->json([
            'recommended' => $recommended,
            'evaluated' => $ranking->count(),
            'campaign_ends_at' => $fin->format('d/m/Y'),
        ]);
    }

    private function limpiarMarkdownPlan(string $contenido): string
    {
        $contenido = html_entity_decode(strip_tags($contenido), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $contenido = preg_replace('/^-{3,}\s*$/m', '', $contenido);
        $contenido = preg_replace('/^#{1,6}\s*/m', '', (string) $contenido);
        $contenido = preg_replace('/[*_`]+/u', '', (string) $contenido);
        $contenido = preg_replace('/[ \t]+$/m', '', (string) $contenido);
        $contenido = preg_replace('/\n{3,}/', "\n\n", (string) $contenido);

        return SocialContentPolicy::sanitize(trim((string) $contenido));
    }

    private function resumirSeccionCampania(
        string $contenido,
        int $maxLineas,
        int $maxCaracteres,
        bool $usarVinetas = false
    ): string {
        $contenido = $this->limpiarMarkdownPlan($contenido);
        $lineasResultado = [];
        $encabezadosTabla = [
            '#', 'elemento', 'objetivo', 'objetivo smart', 'especifico', 'medible', 'alcanzable',
            'relevante', 'temporal', 'indicador', 'meta', 'plazo',
        ];

        foreach (preg_split('/\R/u', $contenido) ?: [] as $linea) {
            $linea = trim($linea);
            if ($linea === '' || preg_match('/^\|?[\s:|\-]+\|?$/u', $linea)) {
                continue;
            }

            if (str_contains($linea, '|')) {
                $celdas = array_values(array_filter(
                    array_map('trim', explode('|', trim($linea, '| '))),
                    fn (string $celda) => $celda !== ''
                ));
                $primeraCelda = $celdas[0] ?? '';
                $primeraNormalizada = Str::lower(Str::ascii(trim($primeraCelda, " *_`")));

                if (in_array($primeraNormalizada, $encabezadosTabla, true)) {
                    continue;
                }

                if (preg_match('/^\d+[.)]?$/', $primeraCelda) && isset($celdas[1])) {
                    $linea = $celdas[1];
                } elseif (isset($celdas[1])) {
                    $linea = trim($primeraCelda, " *_`") . ': ' . $celdas[1];
                } else {
                    $linea = $primeraCelda;
                }
            }

            $linea = preg_replace('/^(?:[-•]+|\d+[.)])\s*/u', '', $linea);
            $linea = trim(preg_replace('/\s+/u', ' ', (string) $linea));
            $normalizada = Str::lower(Str::ascii($linea));

            if ($linea === '' || in_array($normalizada, $encabezadosTabla, true)) {
                continue;
            }

            $linea = Str::limit($linea, 220, '');
            if (! in_array($linea, $lineasResultado, true)) {
                $lineasResultado[] = $linea;
            }

            if (count($lineasResultado) >= $maxLineas) {
                break;
            }
        }

        if ($usarVinetas) {
            $lineasResultado = array_map(fn (string $linea) => '- ' . $linea, $lineasResultado);
        }

        return Str::limit(implode("\n", $lineasResultado), $maxCaracteres, '');
    }

    public function destroy(Campania $campania)
    {
        try {
            $campania->delete();
            return redirect()->route('administrador.campañas.index')
                ->with('success', 'Campaña eliminada exitosamente. El cliente ahora puede tener una nueva campaña.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la campaña: ' . $e->getMessage());
        }
    }
}



