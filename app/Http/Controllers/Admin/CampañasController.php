<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Pago;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\AdminCampaignAnalyticsService;
use App\Services\CampaignAudienceService;
use App\Services\CampaignBlueprintService;
use App\Services\CampaignBriefPrefillService;
use App\Services\CampaignCreatedNotifier;
use App\Services\CampaignFeedbackService;
use App\Services\CampaignPreparationService;
use App\Services\CampaignTaskPrefillService;
use App\Services\CommunityManagerRecommender;
use App\Services\MetaCampaignAnalyticsService;
use App\Services\SocialContentPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampañasController extends Controller
{
    public function index()
    {
        // 1. Obtener usuarios con suscripción activa que no tienen campaña activa
        $clientesSinCampania = Pago::with(['usuario', 'suscripcion.empresa.planesMarketing', 'plan'])
            ->where('estado', 'completado')
            ->whereHas('suscripcion', function ($query) {
                $query->where('estado', 'activa')
                    ->where(function ($vigenciaQuery) {
                        $vigenciaQuery->whereNull('vigencia_activada_at')
                            ->orWhere('fecha_fin', '>', now());
                    });
            })
            ->whereDoesntHave('suscripcion.campanias', function ($query) {
                $query->where('fecha_fin', '>', now())
                    ->whereIn('estado', ['activa', 'pausada']);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pago) {
                $empresa = $pago->suscripcion->empresa;
                $planesActivos = $empresa?->planesMarketing
                    ->where('estado', 'activo')
                    ->sortByDesc('id')
                    ?? collect();
                $planMarketing = $planesActivos->firstWhere('suscripcion_id', $pago->suscripcion->id)
                    ?? $planesActivos->first();

                return [
                    'id' => $pago->usuario->id,
                    'suscripcion_id' => $pago->suscripcion->id,
                    'nombre' => $pago->usuario->name,
                    'email' => $pago->usuario->email,
                    'plan' => $pago->plan->nombre ?? 'Sin plan',
                    'fecha_fin_suscripcion' => $pago->suscripcion->vigencia_activada_at ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'Pendiente de campaña',
                    'fecha_fin_suscripcion_raw' => $pago->suscripcion->vigencia_activada_at ? $pago->suscripcion->fecha_fin->format('Y-m-d') : null,
                    'tiene_empresa' => $empresa !== null,
                    'cuestionario_completado' => (bool) $empresa?->cuestionario_completado,
                    'tiene_resumen_ejecutivo' => filled($empresa?->resumen_ejecutivo),
                    'empresa_id' => $empresa ? $empresa->id : null,
                    'empresa_nombre' => $empresa?->nombre_empresa,
                    'tiene_plan_marketing' => $planMarketing !== null,
                    'plan_marketing_id' => $planMarketing?->id,
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
            ->where('es_borrador', false)
            ->where(function ($query) {
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
        $communityManagers = User::whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Community Manager');
        })->get();

        return view('administrador.campañas.index', [
            'clientesSinCampania' => $clientesSinCampania,
            'campaniasActivas' => $campaniasActivas,
            'campaniasFinalizadas' => $campaniasFinalizadas,
            'aniosFinalizadasDisponibles' => $aniosFinalizadasDisponibles,
            'mesesFinalizadasDisponibles' => $mesesFinalizadasDisponibles,
            'communityManagers' => $communityManagers,
            'adminActual' => Auth::user(),
        ]);
    }

    public function analiticas(Request $request, AdminCampaignAnalyticsService $dashboardService)
    {
        $validated = $request->validate([
            'filter_type' => 'nullable|in:all,range,month,year',
            'start_date' => 'nullable|required_if:filter_type,range|date',
            'end_date' => 'nullable|required_if:filter_type,range|date|after_or_equal:start_date',
            'month' => 'nullable|required_if:filter_type,month|date_format:Y-m',
            'year' => 'nullable|required_if:filter_type,year|integer|min:2004|max:'.now()->year,
        ]);
        $filterType = $validated['filter_type'] ?? 'all';
        $period = match ($filterType) {
            'range' => filled($validated['start_date'] ?? null) && filled($validated['end_date'] ?? null)
                ? [
                    'type' => 'range',
                    'since' => Carbon::parse($validated['start_date'])->startOfDay(),
                    'until' => Carbon::parse($validated['end_date'])->endOfDay(),
                    'label' => Carbon::parse($validated['start_date'])->format('d/m/Y').' al '.Carbon::parse($validated['end_date'])->format('d/m/Y'),
                ]
                : ['type' => 'all', 'since' => null, 'until' => now()->endOfDay(), 'label' => 'todo el historial'],
            'month' => filled($validated['month'] ?? null)
                ? [
                    'type' => 'month',
                    'since' => Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth(),
                    'until' => Carbon::createFromFormat('Y-m', $validated['month'])->endOfMonth(),
                    'label' => Carbon::createFromFormat('Y-m', $validated['month'])->locale('es')->translatedFormat('F Y'),
                ]
                : ['type' => 'all', 'since' => null, 'until' => now()->endOfDay(), 'label' => 'todo el historial'],
            'year' => filled($validated['year'] ?? null)
                ? [
                    'type' => 'year',
                    'since' => Carbon::create((int) $validated['year'])->startOfYear(),
                    'until' => Carbon::create((int) $validated['year'])->endOfYear(),
                    'label' => 'año '.$validated['year'],
                ]
                : ['type' => 'all', 'since' => null, 'until' => now()->endOfDay(), 'label' => 'todo el historial'],
            default => ['type' => 'all', 'since' => null, 'until' => now()->endOfDay(), 'label' => 'todo el historial'],
        };
        $campaigns = Campania::with(['cliente', 'communityManager'])
            ->orderByDesc('fecha_inicio')
            ->get();
        $dashboard = $dashboardService->build($campaigns, $period);

        return view('administrador.campañas.analiticas', [
            'dashboard' => $dashboard,
            'analyticsFilter' => [
                'type' => $period['type'],
                'start_date' => $validated['start_date'] ?? now()->startOfMonth()->toDateString(),
                'end_date' => $validated['end_date'] ?? now()->toDateString(),
                'month' => $validated['month'] ?? now()->format('Y-m'),
                'year' => (int) ($validated['year'] ?? now()->year),
            ],
            'selectedPeriodLabel' => $period['label'],
            'usingFallback' => $dashboard === null,
        ]);
    }

    public function preparar(
        Suscripcion $suscripcion,
        CampaignPreparationService $preparationService,
        CampaignBriefPrefillService $briefPrefillService,
        CampaignTaskPrefillService $taskPrefillService
    ) {
        $suscripcion->loadMissing(['usuario', 'empresa.respuestasCuestionario', 'plan']);
        $this->assertSubscriptionCanCreateCampaign($suscripcion);

        if (! $suscripcion->empresa) {
            return redirect()->route('administrador.empresas.crear-con-cuestionario', [
                'usuario_id' => $suscripcion->usuario_id,
                'suscripcion_id' => $suscripcion->id,
                'continuar_campania' => $suscripcion->id,
            ])->with('info', 'Primero registra la empresa y completa su cuestionario. Al guardar continuaremos automáticamente.');
        }

        if (! $suscripcion->empresa->cuestionario_completado
            || $suscripcion->empresa->respuestasCuestionario->isEmpty()) {
            return redirect()->route('administrador.empresas.cuestionario.show', [
                'id' => $suscripcion->empresa->id,
                'continuar_campania' => $suscripcion->id,
            ])->with('info', 'Completa el cuestionario empresarial para continuar con la campaña.');
        }

        try {
            $planMarketing = $preparationService->prepare($suscripcion);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('administrador.campañas.index')->with('error', $exception->getMessage());
        }

        $communityManagers = User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))
            ->orderBy('name')->get();
        $disenadores = User::whereHas('roles', fn ($query) => $query
            ->whereIn('nombre_rol', ['Diseñador', 'Disenador']))
            ->orderBy('name')->get();
        $fechaInicio = now()->toDateString();
        $fechaFin = ($suscripcion->vigencia_activada_at
            ? $suscripcion->fecha_fin
            : now()->copy()->addMonthNoOverflow())->toDateString();
        $briefInicial = $briefPrefillService->build($suscripcion, $planMarketing);
        $tareasIniciales = $taskPrefillService->build($planMarketing, $fechaInicio, $fechaFin);

        return view('administrador.campañas.crear-avanzada', compact(
            'suscripcion', 'planMarketing', 'communityManagers', 'disenadores', 'fechaInicio', 'fechaFin', 'briefInicial', 'tareasIniciales'
        ));
    }

    public function propuestaIA(
        Request $request,
        Suscripcion $suscripcion,
        CampaignPreparationService $preparationService,
        CampaignBlueprintService $blueprintService
    ) {
        $validated = $request->validate([
            'modo' => ['required', Rule::in(['asistido', 'automatico'])],
            'campania_id' => ['nullable', 'integer', 'exists:campanias,id'],
        ]);
        if (! empty($validated['campania_id'])) {
            abort_unless(
                $suscripcion->campanias()->whereKey($validated['campania_id'])->exists(),
                422,
                'La campaña seleccionada no pertenece a esta suscripción.'
            );
        }
        $this->assertSubscriptionCanCreateCampaign($suscripcion, $validated['campania_id'] ?? null);

        try {
            $plan = $preparationService->prepare($suscripcion);
            $proposal = $blueprintService->generate($suscripcion, $plan, $validated['modo']);
            $proposal['community_manager_id'] = $this->leastLoadedUserId('Community Manager');
            $designTasks = collect($proposal['tareas'] ?? [])->filter(function (array $task) {
                return in_array('Diseñador', $task['roles_sugeridos'] ?? [], true);
            })->count();
            $proposal['disenadores_ids'] = $this->leastLoadedUserIds(
                ['Diseñador', 'Disenador'],
                $designTasks >= 8 ? 2 : 1
            );
            if (! $proposal['community_manager_id']) {
                throw new \RuntimeException('No hay un Community Manager disponible para delegar la campaña.');
            }
            if ($designTasks > 0 && $proposal['disenadores_ids'] === []) {
                throw new \RuntimeException('La campaña necesita piezas gráficas, pero no hay diseñadores disponibles.');
            }
            $proposal['disenador_id'] = $proposal['disenadores_ids'][0] ?? null;
            $proposal['tareas'] = $this->delegateProposalTasks(
                $proposal['tareas'] ?? [],
                $proposal['community_manager_id'],
                $proposal['disenadores_ids']
            );

            return response()->json(['success' => true, 'proposal' => $proposal]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function guardarAvanzada(
        Request $request,
        CampaignAudienceService $audienceService,
        CampaignCreatedNotifier $campaignNotifier
    )
    {
        $audiences = $audienceService->normalize($request->input('publicos_objetivo', []));
        $request->merge([
            'publicos_objetivo' => $audiences,
            'publico_objetivo' => $audienceService->serialize($audiences),
        ]);

        if ($request->has('tareas')) {
            $request->merge([
                'tareas' => collect($request->input('tareas', []))->map(function ($task) {
                    if (! is_array($task)) {
                        return $task;
                    }

                    $task['responsables_ids'] = collect($task['responsables_ids'] ?? [])
                        ->filter(fn ($id) => filled($id))
                        ->map(fn ($id) => (string) $id)
                        ->unique()
                        ->values()
                        ->all();

                    return $task;
                })->all(),
            ]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:5000',
            'objetivo_general' => 'nullable|string|max:2500',
            'publico_objetivo' => 'nullable|string|max:7000',
            'publicos_objetivo' => 'nullable|array|max:10',
            'publicos_objetivo.*.tipo_edades' => 'required_with:publicos_objetivo|string|max:120',
            'publicos_objetivo.*.descripcion' => 'nullable|string|max:600',
            'mensaje_principal' => 'nullable|string|max:1500',
            'tono_comunicacion' => 'nullable|string|max:120',
            'canales' => 'nullable|array|max:4',
            'canales.*' => ['string', Rule::in(['Facebook', 'Instagram', 'TikTok', 'WhatsApp'])],
            'indicadores' => 'nullable|array|max:10',
            'indicadores.*' => 'string|max:100',
            'modo_creacion' => ['required', Rule::in(['manual', 'asistido', 'automatico'])],
            'usuario_cliente_id' => 'required|exists:users,id',
            'community_manager_id' => 'required|exists:users,id',
            'disenadores_ids' => 'nullable|array|max:10',
            'disenadores_ids.*' => 'nullable|integer|distinct|exists:users,id',
            'suscripcion_id' => 'required|exists:suscripciones,id',
            'tareas' => 'nullable|array|max:20',
            'tareas.*.titulo' => 'required_with:tareas|string|max:100',
            'tareas.*.descripcion' => 'required_with:tareas|string|max:3000',
            'tareas.*.entregable' => 'nullable|string|max:1500',
            'tareas.*.tipo_contenido' => ['nullable', Rule::in(['reel', 'post', 'historia', 'carrusel', 'guion', 'otro'])],
            'tareas.*.tipo_contenido_otro' => 'nullable|required_if:tareas.*.tipo_contenido,otro|string|max:30',
            'tareas.*.fecha_inicio' => 'required_with:tareas|date',
            'tareas.*.fecha_limite' => 'required_with:tareas|date',
            'tareas.*.prioridad' => ['required_with:tareas', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'tareas.*.rol_sugerido' => ['nullable', Rule::in(['Community Manager', 'Diseñador', 'Administrador'])],
            'tareas.*.requiere_aprobacion' => 'nullable|boolean',
            'tareas.*.visible_cliente' => 'nullable|boolean',
            'tareas.*.responsables_ids' => 'required_with:tareas|array|min:1|max:10',
            'tareas.*.responsables_ids.*' => 'nullable|integer|exists:users,id',
        ]);

        $pago = Pago::with(['suscripcion.empresa.respuestasCuestionario', 'suscripcion.planMarketing'])
            ->where('usuario_id', $validated['usuario_cliente_id'])
            ->where('suscripcion_id', $validated['suscripcion_id'])
            ->where('estado', 'completado')
            ->first();

        if (! $pago?->suscripcion) {
            return back()->with('error', 'El cliente no tiene una suscripción activa válida.')->withInput();
        }

        $suscripcionPreparada = $pago->suscripcion;
        if (! $suscripcionPreparada->empresa?->cuestionario_completado
            || blank($suscripcionPreparada->empresa?->resumen_ejecutivo)
            || ! $suscripcionPreparada->planMarketing) {
            return redirect()->route('administrador.campañas.preparar', $suscripcionPreparada)
                ->with('error', 'Antes de guardar deben existir cuestionario, resumen ejecutivo y plan de marketing.');
        }

        if (! User::whereKey($validated['community_manager_id'])
            ->whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))->exists()) {
            return back()->withErrors(['community_manager_id' => 'El responsable seleccionado no es Community Manager.'])->withInput();
        }

        $designerIds = array_values($validated['disenadores_ids'] ?? []);
        $validDesignerCount = User::whereKey($designerIds)
            ->whereHas('roles', fn ($query) => $query->whereIn('nombre_rol', ['Diseñador', 'Disenador']))
            ->count();
        if ($validDesignerCount !== count($designerIds)) {
            return back()->withErrors(['disenadores_ids' => 'Todos los usuarios seleccionados deben pertenecer al equipo de diseño.'])->withInput();
        }

        $campaignTeamIds = collect([$validated['community_manager_id'], Auth::id(), ...$designerIds])
            ->filter()->map(fn ($id) => (int) $id)->unique();
        foreach ($validated['tareas'] ?? [] as $index => $task) {
            $invalidResponsible = collect($task['responsables_ids'] ?? [])
                ->filter()->map(fn ($id) => (int) $id)
                ->first(fn (int $id) => ! $campaignTeamIds->contains($id));
            if ($invalidResponsible) {
                return back()->withErrors([
                    "tareas.{$index}.responsables_ids" => 'Los responsables deben pertenecer al equipo seleccionado para la campaña.',
                ])->withInput();
            }
        }

        if ($validated['modo_creacion'] !== 'manual') {
            foreach (['objetivo_general', 'publico_objetivo', 'mensaje_principal'] as $field) {
                if (blank($validated[$field] ?? null)) {
                    return back()->withErrors([$field => 'La propuesta con IA debe incluir este campo estratégico.'])->withInput();
                }
            }

            if (empty($validated['tareas'])) {
                return back()->withErrors(['tareas' => 'Una campaña creada con IA debe conservar al menos una tarea operativa.'])->withInput();
            }
        }

        $inicioPermitido = now()->startOfDay();
        $finPermitido = ($suscripcionPreparada->vigencia_activada_at
            ? $suscripcionPreparada->fecha_fin
            : now()->copy()->addMonthNoOverflow())->endOfDay();

        foreach ($validated['tareas'] ?? [] as $index => $tarea) {
            $inicioTarea = Carbon::parse($tarea['fecha_inicio']);
            $finTarea = Carbon::parse($tarea['fecha_limite']);
            if ($finTarea->lt($inicioTarea)) {
                return back()->withErrors([
                    "tareas.{$index}.fecha_limite" => 'La fecha límite no puede ser anterior al inicio.',
                ])->withInput();
            }
            if ($inicioTarea->lt($inicioPermitido) || $finTarea->gt($finPermitido)) {
                return back()->withErrors([
                    "tareas.{$index}.fecha_limite" => 'Las fechas deben permanecer dentro de la vigencia de la campaña.',
                ])->withInput();
            }
        }

        $requiereDisenador = collect($validated['tareas'] ?? [])
            ->contains(fn ($tarea) => ($tarea['rol_sugerido'] ?? null) === 'Diseñador');
        if ($requiereDisenador && $designerIds === []) {
            return back()->withErrors([
                'disenadores_ids' => 'El cronograma incluye tareas de diseño; selecciona al menos un diseñador para asignarlas correctamente.',
            ])->withInput();
        }

        try {
            $campania = DB::transaction(function () use ($validated, $pago, $designerIds) {
                $suscripcion = $pago->suscripcion()->lockForUpdate()->firstOrFail();
                $esBorrador = false;

                if (! $esBorrador && ! $suscripcion->vigencia_activada_at) {
                    $inicioVigencia = now();
                    $suscripcion->update([
                        'fecha_inicio' => $inicioVigencia,
                        'fecha_fin' => $inicioVigencia->copy()->addMonthNoOverflow(),
                        'vigencia_activada_at' => $inicioVigencia,
                    ]);
                }

                $campania = Campania::create([
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'],
                    'objetivo_general' => $validated['objetivo_general'] ?? null,
                    'publico_objetivo' => $validated['publico_objetivo'] ?? null,
                    'mensaje_principal' => $validated['mensaje_principal'] ?? null,
                    'tono_comunicacion' => $validated['tono_comunicacion'] ?? null,
                    'canales' => $validated['canales'] ?? [],
                    'indicadores' => $validated['indicadores'] ?? [],
                    'modo_creacion' => $validated['modo_creacion'],
                    'es_borrador' => $esBorrador,
                    'ai_generation_metadata' => $validated['modo_creacion'] === 'manual' ? null : [
                        'generated_at' => now()->toIso8601String(),
                        'method' => $validated['modo_creacion'] === 'automatico' ? 'automatic_rules' : 'artificial_intelligence',
                        'model' => $validated['modo_creacion'] === 'asistido' ? config('services.groq.model') : null,
                        'review_required' => true,
                    ],
                    'fecha_inicio' => $suscripcion->vigencia_activada_at ? $suscripcion->fecha_inicio : now(),
                    'fecha_fin' => $suscripcion->vigencia_activada_at ? $suscripcion->fecha_fin : now()->copy()->addMonthNoOverflow(),
                    'usuario_creador_id' => Auth::id(),
                    'community_manager_id' => $validated['community_manager_id'],
                    'disenador_id' => $designerIds[0] ?? null,
                    'usuario_cliente_id' => $validated['usuario_cliente_id'],
                    'suscripcion_id' => $suscripcion->id,
                    'estado' => $esBorrador ? 'pausada' : 'activa',
                ]);

                $campania->empresas()->syncWithoutDetaching([$suscripcion->empresa->id]);
                $campania->disenadores()->sync($designerIds);

                $designerTaskIndex = 0;
                foreach ($validated['tareas'] ?? [] as $tarea) {
                    $responsibleIds = collect($tarea['responsables_ids'] ?? [])
                        ->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
                    $fallbackAssignedId = match ($tarea['rol_sugerido'] ?? 'Community Manager') {
                        'Diseñador' => $designerIds !== []
                            ? $designerIds[$designerTaskIndex++ % count($designerIds)]
                            : $validated['community_manager_id'],
                        'Administrador' => Auth::id(),
                        default => $validated['community_manager_id'],
                    };
                    $asignadoId = $responsibleIds[0] ?? $fallbackAssignedId;

                    $createdTask = $campania->tareas()->create([
                        'titulo' => $tarea['titulo'],
                        'descripcion' => $tarea['descripcion'],
                        'entregable' => $tarea['entregable'] ?? null,
                        'tipo_contenido' => ($tarea['tipo_contenido'] ?? null) === 'otro'
                            ? ($tarea['tipo_contenido_otro'] ?? 'otro')
                            : ($tarea['tipo_contenido'] ?? null),
                        'fecha_inicio' => $tarea['fecha_inicio'],
                        'fecha_limite' => $tarea['fecha_limite'],
                        'prioridad' => $tarea['prioridad'],
                        'requiere_aprobacion' => (bool) ($tarea['requiere_aprobacion'] ?? false),
                        'visible_cliente' => (bool) ($tarea['visible_cliente'] ?? false),
                        'creador_id' => Auth::id(),
                        'asignado_id' => $asignadoId,
                    ]);
                    $createdTask->responsables()->sync($responsibleIds ?: [$asignadoId]);
                }

                return $campania;
            });

            $campaignNotifier->send($campania);

            return redirect()->route('administrador.campañas.show', $campania)
                ->with('success', $campania->es_borrador
                    ? 'La IA creó la campaña, el equipo y las tareas como borrador. Revísala antes de activarla.'
                    : 'Campaña creada exitosamente.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'No se pudo crear la campaña: '.$exception->getMessage())->withInput();
        }
    }

    public function guardar(Request $request, CampaignCreatedNotifier $campaignNotifier)
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
                ->whereHas('suscripcion', function ($query) {
                    $query->where('estado', 'activa')
                        ->where(function ($vigenciaQuery) {
                            $vigenciaQuery->whereNull('vigencia_activada_at')
                                ->orWhere('fecha_fin', '>', now());
                        });
                })
                ->first();

            if (! $pago || ! $pago->suscripcion) {
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

            \Log::info('Campaña creada con ID: '.$campania->id);
            $campaignNotifier->send($campania);

            return redirect()->route('administrador.campañas.index')
                ->with('success', 'Campaña creada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error al crear campaña: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Ha ocurrido un error al crear la campaña: '.$e->getMessage())
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

        if (! $tieneSuscripcionActiva) {
            return redirect()->back()
                ->with('error', 'El cliente no tiene una suscripción activa para reactivar la campaña');
        }

        // Actualizar la campaña
        $campania->update([
            'estado' => 'activa',
            'fecha_fin' => $campania->cliente->suscripciones()->where('estado', 'activa')->first()->fecha_fin,
        ]);

        return redirect()->back()
            ->with('success', 'Campaña reactivada exitosamente');
    }

    // Mostrar detalles de una campaña
    public function show(
        Campania $campania,
        CampaignAudienceService $audienceService,
        CampaignFeedbackService $feedbackService
    )
    {
        $campania->loadMissing([
            'suscripcion.empresa',
            'empresas',
            'cliente.empresas.planesMarketing.suscripcion.plan',
            'creador',
            'communityManager',
            'disenador',
            'disenadores',
            'tareas.asignado',
            'tareas.responsables.roles',
            'tareas.archivos',
            'reuniones.participantes',
            'reuniones.creador',
        ]);

        $empresa = $campania->suscripcion?->empresa
            ?? $campania->empresas->first()
            ?? $campania->cliente?->empresas?->first();
        $recursosCliente = $empresa
            ? $empresa->recursos()->where('origen', 'cliente')->latest()->get()
            : collect();
        $recursosAdministracion = $empresa
            ? $empresa->recursos()->where('origen', 'administracion')->with('creador')->latest()->get()
            : collect();
        $publicosObjetivo = $audienceService->parse((string) $campania->publico_objetivo);
        $feedbackParticipants = $feedbackService->participants($campania);

        return view('administrador.campañas.show', compact(
            'campania', 'empresa', 'recursosCliente', 'recursosAdministracion', 'publicosObjetivo', 'feedbackParticipants'
        ));
    }

    public function analyticsData(Request $request, Campania $campania, MetaCampaignAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'days' => 'nullable|in:7,30,90,365,730,all',
        ]);

        return response()->json(
            $analyticsService->forCampaign($campania, $validated['days'] ?? 30)
        );
    }

    // Mostrar formulario de edición
    public function edit(Campania $campania, CampaignAudienceService $audienceService)
    {
        $campania->loadMissing([
            'suscripcion.empresa.respuestasCuestionario',
            'suscripcion.planMarketing',
            'creador',
            'communityManager',
            'disenadores',
            'tareas.responsables',
        ]);

        $suscripcion = $campania->suscripcion;
        abort_unless($suscripcion?->empresa, 404, 'La campaña no tiene una empresa asociada.');

        $planMarketing = $suscripcion->planMarketing
            ?? PlanMarketing::where('empresa_id', $suscripcion->empresa->id)
                ->where('estado', 'activo')
                ->latest()
                ->first();
        $communityManagers = User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))
            ->orderBy('name')->get();
        $disenadores = User::whereHas('roles', fn ($query) => $query
            ->whereIn('nombre_rol', ['Diseñador', 'Disenador']))
            ->orderBy('name')->get();
        $fechaInicio = Carbon::parse($campania->fecha_inicio)->toDateString();
        $fechaFin = Carbon::parse($campania->fecha_fin)->toDateString();
        $briefInicial = [];
        $tareasIniciales = [];
        $tareasActuales = $campania->tareas->map(fn ($tarea) => [
            'id' => $tarea->id,
            'titulo' => $tarea->titulo,
            'descripcion' => $tarea->descripcion,
            'entregable' => $tarea->entregable,
            'tipo_contenido' => $tarea->tipo_contenido ?: 'post',
            'fecha_inicio' => $tarea->fecha_inicio?->toDateString(),
            'fecha_limite' => $tarea->fecha_limite?->toDateString(),
            'prioridad' => $tarea->prioridad,
            'rol_sugerido' => $tarea->asignado_id === Auth::id() ? 'Administrador' : 'Community Manager',
            'roles_sugeridos' => [],
            'requiere_aprobacion' => $tarea->requiere_aprobacion,
            'visible_cliente' => $tarea->visible_cliente,
            'responsables_ids' => $tarea->responsables->pluck('id')->values()->all() ?: [$tarea->asignado_id],
        ])->values()->all();
        $publicosActuales = $audienceService->parse((string) $campania->publico_objetivo);

        return view('administrador.campañas.crear-avanzada', compact(
            'campania', 'suscripcion', 'planMarketing', 'communityManagers', 'disenadores',
            'fechaInicio', 'fechaFin', 'briefInicial', 'tareasIniciales', 'tareasActuales', 'publicosActuales'
        ));
    }

    // Actualizar campaña
    public function update(Request $request, Campania $campania, CampaignAudienceService $audienceService)
    {
        $audiences = $audienceService->normalize($request->input('publicos_objetivo', []));
        $request->merge([
            'publicos_objetivo' => $audiences,
            'publico_objetivo' => $audienceService->serialize($audiences),
        ]);

        if ($request->has('tareas')) {
            $request->merge([
                'tareas' => collect($request->input('tareas', []))->map(function ($task) {
                    if (! is_array($task)) {
                        return $task;
                    }

                    $task['responsables_ids'] = collect($task['responsables_ids'] ?? [])
                        ->filter(fn ($id) => filled($id))
                        ->map(fn ($id) => (string) $id)
                        ->unique()
                        ->values()
                        ->all();

                    return $task;
                })->all(),
            ]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:5000',
            'objetivo_general' => 'nullable|string|max:2500',
            'publico_objetivo' => 'nullable|string|max:7000',
            'publicos_objetivo' => 'nullable|array|max:10',
            'publicos_objetivo.*.tipo_edades' => 'required_with:publicos_objetivo|string|max:120',
            'publicos_objetivo.*.descripcion' => 'nullable|string|max:600',
            'mensaje_principal' => 'nullable|string|max:1500',
            'tono_comunicacion' => 'nullable|string|max:120',
            'canales' => 'nullable|array|max:4',
            'canales.*' => ['string', Rule::in(['Facebook', 'Instagram', 'TikTok', 'WhatsApp'])],
            'indicadores' => 'nullable|array|max:10',
            'indicadores.*' => 'string|max:100',
            'modo_creacion' => ['required', Rule::in(['manual', 'asistido', 'automatico'])],
            'community_manager_id' => 'required|exists:users,id',
            'disenadores_ids' => 'nullable|array|max:10',
            'disenadores_ids.*' => 'nullable|integer|distinct|exists:users,id',
            'estado' => 'required|in:activa,pausada,finalizada',
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_inicio' => 'required|date',
            'tareas' => 'nullable|array|max:20',
            'tareas.*.id' => 'nullable|integer',
            'tareas.*.titulo' => 'required_with:tareas|string|max:100',
            'tareas.*.descripcion' => 'required_with:tareas|string|max:3000',
            'tareas.*.entregable' => 'nullable|string|max:1500',
            'tareas.*.tipo_contenido' => ['nullable', Rule::in(['reel', 'post', 'historia', 'carrusel', 'guion', 'otro'])],
            'tareas.*.tipo_contenido_otro' => 'nullable|required_if:tareas.*.tipo_contenido,otro|string|max:30',
            'tareas.*.fecha_inicio' => 'required_with:tareas|date',
            'tareas.*.fecha_limite' => 'required_with:tareas|date',
            'tareas.*.prioridad' => ['required_with:tareas', Rule::in(['baja', 'media', 'alta', 'urgente'])],
            'tareas.*.rol_sugerido' => ['nullable', Rule::in(['Community Manager', 'Diseñador', 'Administrador'])],
            'tareas.*.requiere_aprobacion' => 'nullable|boolean',
            'tareas.*.visible_cliente' => 'nullable|boolean',
            'tareas.*.responsables_ids' => 'required_with:tareas|array|min:1|max:10',
            'tareas.*.responsables_ids.*' => 'nullable|integer|exists:users,id',
        ]);

        if (! User::whereKey($validated['community_manager_id'])
            ->whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))->exists()) {
            return back()->withErrors(['community_manager_id' => 'El responsable seleccionado no es Community Manager.'])->withInput();
        }

        $designerIds = array_values($validated['disenadores_ids'] ?? []);
        $validDesignerCount = User::whereKey($designerIds)
            ->whereHas('roles', fn ($query) => $query->whereIn('nombre_rol', ['Diseñador', 'Disenador']))
            ->count();
        if ($validDesignerCount !== count($designerIds)) {
            return back()->withErrors(['disenadores_ids' => 'Todos los usuarios seleccionados deben pertenecer al equipo de diseño.'])->withInput();
        }

        $campaignTeamIds = collect([$validated['community_manager_id'], $campania->usuario_creador_id, Auth::id(), ...$designerIds])
            ->filter()->map(fn ($id) => (int) $id)->unique();
        $campaignStart = Carbon::parse($validated['fecha_inicio'])->startOfDay();
        $campaignEnd = Carbon::parse($validated['fecha_fin'])->endOfDay();

        foreach ($validated['tareas'] ?? [] as $index => $task) {
            if (! empty($task['id']) && ! $campania->tareas()->whereKey($task['id'])->exists()) {
                return back()->withErrors(["tareas.{$index}.id" => 'La tarea seleccionada no pertenece a esta campaña.'])->withInput();
            }

            $invalidResponsible = collect($task['responsables_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->first(fn ($id) => ! $campaignTeamIds->contains($id));
            if ($invalidResponsible) {
                return back()->withErrors(["tareas.{$index}.responsables_ids" => 'Los responsables deben pertenecer al equipo de la campaña.'])->withInput();
            }

            $taskStart = Carbon::parse($task['fecha_inicio']);
            $taskEnd = Carbon::parse($task['fecha_limite']);
            if ($taskEnd->lt($taskStart) || $taskStart->lt($campaignStart) || $taskEnd->gt($campaignEnd)) {
                return back()->withErrors(["tareas.{$index}.fecha_limite" => 'Las fechas de la tarea deben estar dentro de la vigencia de la campaña.'])->withInput();
            }
        }

        DB::transaction(function () use ($campania, $validated, $designerIds) {
            $campania->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'objetivo_general' => $validated['objetivo_general'] ?? null,
                'publico_objetivo' => $validated['publico_objetivo'] ?? null,
                'mensaje_principal' => $validated['mensaje_principal'] ?? null,
                'tono_comunicacion' => $validated['tono_comunicacion'] ?? null,
                'canales' => $validated['canales'] ?? [],
                'indicadores' => $validated['indicadores'] ?? [],
                'modo_creacion' => $validated['modo_creacion'],
                'community_manager_id' => $validated['community_manager_id'],
                'disenador_id' => $designerIds[0] ?? null,
                'estado' => $validated['estado'],
                'fecha_fin' => $validated['fecha_fin'],
            ]);
            $campania->disenadores()->sync($designerIds);

            $retainedTaskIds = [];
            foreach ($validated['tareas'] ?? [] as $taskData) {
                $responsibleIds = collect($taskData['responsables_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)->unique()->values()->all();
                $task = ! empty($taskData['id'])
                    ? $campania->tareas()->findOrFail($taskData['id'])
                    : $campania->tareas()->make(['creador_id' => Auth::id()]);

                $task->fill([
                    'titulo' => $taskData['titulo'],
                    'descripcion' => $taskData['descripcion'],
                    'entregable' => $taskData['entregable'] ?? null,
                    'tipo_contenido' => ($taskData['tipo_contenido'] ?? null) === 'otro'
                        ? ($taskData['tipo_contenido_otro'] ?? 'otro')
                        : ($taskData['tipo_contenido'] ?? null),
                    'fecha_inicio' => $taskData['fecha_inicio'],
                    'fecha_limite' => $taskData['fecha_limite'],
                    'prioridad' => $taskData['prioridad'],
                    'requiere_aprobacion' => (bool) ($taskData['requiere_aprobacion'] ?? false),
                    'visible_cliente' => (bool) ($taskData['visible_cliente'] ?? false),
                    'asignado_id' => $responsibleIds[0],
                ]);
                $task->save();
                $task->responsables()->sync($responsibleIds);
                $retainedTaskIds[] = $task->id;
            }

            $campania->tareas()->whereNotIn('id', $retainedTaskIds)->delete();
        });

        return redirect()->route('administrador.campañas.show', $campania)
            ->with('success', 'Campaña actualizada exitosamente');
    }

    public function obtenerPlanIA(Request $request, Empresa $empresa)
    {
        try {
            $request->validate(['suscripcion_id' => 'required|integer|exists:suscripciones,id']);
            $pago = Pago::with(['suscripcion.plan.planCaracteristicas.caracteristica'])
                ->where('usuario_id', $empresa->usuario_id)
                ->where('suscripcion_id', $request->integer('suscripcion_id'))
                ->where('estado', 'completado')
                ->firstOrFail();

            if ((int) $empresa->suscripcion_id !== (int) $pago->suscripcion_id) {
                return response()->json([
                    'error' => 'La empresa seleccionada no corresponde a esta suscripción.',
                ], 422);
            }

            // Priorizar el plan de la suscripción seleccionada. Los registros
            // creados antes de vincular empresas y suscripciones pueden conservar
            // otro suscripcion_id aunque el plan sí pertenezca a esta empresa.
            $plan = PlanMarketing::where('empresa_id', $empresa->id)
                ->where('estado', 'activo')
                ->where('suscripcion_id', $pago->suscripcion_id)
                ->latest()
                ->first()
                ?? PlanMarketing::where('empresa_id', $empresa->id)
                    ->where('estado', 'activo')
                    ->latest()
                    ->first();

            if (! $plan) {
                return response()->json($this->crearCampaniaDesdePlanContratado($empresa, $pago->suscripcion));
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
                    .' Enfoque: Facebook, Instagram y TikTok.';
            }

            $partesDescripcion = [];
            if ($descripcionBreve !== '') {
                $partesDescripcion[] = "DESCRIPCIÓN:\n".$descripcionBreve;
            }
            if ($objetivosBreves !== '') {
                $partesDescripcion[] = "OBJETIVOS:\n".$objetivosBreves;
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
                'nombre' => 'Campaña Estratégica: '.$empresa->nombre_empresa,
                'descripcion' => mb_substr($descripcion, 0, 2500),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el plan: '.$e->getMessage()], 500);
        }
    }

    private function crearCampaniaDesdePlanContratado(Empresa $empresa, Suscripcion $suscripcion): array
    {
        $planContratado = $suscripcion->plan;
        $contextoEmpresa = $empresa->resumen_ejecutivo ?: $empresa->descripcion;
        $descripcionEmpresa = $this->resumirSeccionCampania((string) $contextoEmpresa, 2, 600);
        $recursos = $planContratado?->planCaracteristicas
            ?->map(function ($planCaracteristica) {
                $nombre = $planCaracteristica->caracteristica?->nombre;
                if (! $nombre) {
                    return null;
                }

                $cantidad = $planCaracteristica->cantidad
                    ? $planCaracteristica->cantidad.' '
                    : '';
                $frecuencia = $planCaracteristica->frecuencia
                    ? ' '.$planCaracteristica->frecuencia
                    : '';

                return trim($cantidad.$nombre.$frecuencia);
            })
            ->filter()
            ->take(6)
            ->implode(', ');

        $partes = [
            'DESCRIPCIÓN:',
            $descripcionEmpresa !== ''
                ? $descripcionEmpresa
                : "Campaña digital para {$empresa->nombre_empresa}.",
            '',
            'OBJETIVOS:',
            '- Fortalecer la presencia digital de la empresa.',
            '- Ejecutar las acciones incluidas en el plan '.($planContratado?->nombre ?? 'contratado').'.',
        ];

        if ($recursos !== '') {
            $partes[] = '- Recursos contratados: '.$recursos.'.';
        }

        return [
            'nombre' => 'Campaña '.($planContratado?->nombre ?? 'Digital').': '.$empresa->nombre_empresa,
            'descripcion' => mb_substr(implode("\n", $partes), 0, 2500),
        ];
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

        $recommendedDesigner = User::whereHas('roles', fn ($query) => $query
            ->whereIn('nombre_rol', ['Diseñador', 'Disenador']))
            ->withCount(['campaniasComoParteDiseno as carga_diseno' => fn ($query) => $query
                ->whereIn('estado', ['activa', 'pausada'])
                ->where('fecha_fin', '>=', $inicio)])
            ->orderBy('carga_diseno')
            ->orderBy('name')
            ->first();

        return response()->json([
            'recommended' => $recommended,
            'recommended_designer' => $recommendedDesigner ? [
                'id' => $recommendedDesigner->id,
                'name' => $recommendedDesigner->name,
                'active_campaigns' => $recommendedDesigner->carga_diseno,
                'reason' => $recommendedDesigner->carga_diseno === 0
                    ? 'No tiene campañas activas asignadas como parte del equipo de diseño.'
                    : 'Tiene la menor carga disponible dentro del equipo de diseño.',
            ] : null,
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
                $primeraNormalizada = Str::lower(Str::ascii(trim($primeraCelda, ' *_`')));

                if (in_array($primeraNormalizada, $encabezadosTabla, true)) {
                    continue;
                }

                if (preg_match('/^\d+[.)]?$/', $primeraCelda) && isset($celdas[1])) {
                    $linea = $celdas[1];
                } elseif (isset($celdas[1])) {
                    $linea = trim($primeraCelda, ' *_`').': '.$celdas[1];
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
            $lineasResultado = array_map(fn (string $linea) => '- '.$linea, $lineasResultado);
        }

        return Str::limit(implode("\n", $lineasResultado), $maxCaracteres, '');
    }

    public function aprobarBorrador(Campania $campania, CampaignCreatedNotifier $campaignNotifier)
    {
        if (! $campania->es_borrador) {
            return back()->with('error', 'Esta campaña ya fue activada.');
        }

        DB::transaction(function () use ($campania) {
            $suscripcion = $campania->suscripcion()->lockForUpdate()->firstOrFail();
            $inicioAnterior = Carbon::parse($campania->fecha_inicio)->startOfDay();
            $inicio = now()->startOfDay();

            if (! $suscripcion->vigencia_activada_at) {
                $suscripcion->update([
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $inicio->copy()->addMonthNoOverflow(),
                    'vigencia_activada_at' => $inicio,
                ]);
            }

            $desplazamiento = $inicioAnterior->diffInDays($inicio, false);
            if ($desplazamiento !== 0) {
                foreach ($campania->tareas as $tarea) {
                    $tarea->update([
                        'fecha_inicio' => Carbon::parse($tarea->fecha_inicio)->addDays($desplazamiento),
                        'fecha_limite' => Carbon::parse($tarea->fecha_limite)->addDays($desplazamiento),
                    ]);
                }
            }

            $campania->update([
                'fecha_inicio' => $suscripcion->fecha_inicio,
                'fecha_fin' => $suscripcion->fecha_fin,
                'estado' => 'activa',
                'es_borrador' => false,
            ]);
        });

        $campaignNotifier->send($campania->fresh());

        return back()->with('success', 'Campaña revisada y activada. La vigencia comenzó hoy.');
    }

    private function assertSubscriptionCanCreateCampaign(Suscripcion $suscripcion, ?int $ignoredCampaignId = null): void
    {
        $hasAnotherCampaign = $suscripcion->campanias()
            ->whereIn('estado', ['activa', 'pausada'])
            ->when($ignoredCampaignId, fn ($query) => $query->whereKeyNot($ignoredCampaignId))
            ->exists();
        $isValid = $suscripcion->estado === 'activa'
            && (! $suscripcion->vigencia_activada_at || $suscripcion->fecha_fin->isFuture())
            && $suscripcion->pagos()->where('estado', 'completado')->exists()
            && ! $hasAnotherCampaign;

        abort_unless($isValid, 422, 'La suscripción no está disponible para crear una nueva campaña.');
    }

    private function leastLoadedUserId(string|array $roles): ?int
    {
        return $this->leastLoadedUserIds($roles, 1)[0] ?? null;
    }

    private function leastLoadedUserIds(string|array $roles, int $limit): array
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('nombre_rol', (array) $roles))
            ->withCount(['campaniasComoCM as carga_campanias' => fn ($query) => $query
                ->where('es_borrador', false)
                ->whereIn('estado', ['activa', 'pausada'])])
            ->withCount(['campaniasComoParteDiseno as carga_diseno' => fn ($query) => $query
                ->where('es_borrador', false)
                ->whereIn('estado', ['activa', 'pausada'])])
            ->orderByRaw('(carga_campanias + carga_diseno) asc')
            ->orderBy('name')
            ->limit(max(1, $limit))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function delegateProposalTasks(array $tasks, ?int $communityManagerId, array $designerIds): array
    {
        $designerIndex = 0;
        $administratorId = Auth::id();

        return collect($tasks)->map(function (array $task) use (
            $communityManagerId,
            $designerIds,
            $administratorId,
            &$designerIndex
        ) {
            $roles = $task['roles_sugeridos'] ?? [$task['rol_sugerido'] ?? 'Community Manager'];
            $responsibles = [];

            if (in_array('Community Manager', $roles, true) && $communityManagerId) {
                $responsibles[] = $communityManagerId;
            }
            if (in_array('Diseñador', $roles, true) && $designerIds !== []) {
                $responsibles[] = $designerIds[$designerIndex % count($designerIds)];
                $designerIndex++;
            }
            if (in_array('Administrador', $roles, true) && $administratorId) {
                $responsibles[] = (int) $administratorId;
            }

            $task['responsables_ids'] = array_values(array_unique($responsibles));

            return $task;
        })->all();
    }

    public function destroy(Campania $campania)
    {
        try {
            $campania->delete();

            return redirect()->route('administrador.campañas.index')
                ->with('success', 'Campaña eliminada exitosamente. El cliente ahora puede tener una nueva campaña.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la campaña: '.$e->getMessage());
        }
    }
}
