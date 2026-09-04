<?php

namespace App\Http\Controllers;

use App\Mail\CambioContrasenaCuenta;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\Suscripcion;
use App\Models\Pago;
use App\Models\Empresa;
use App\Models\RespuestaCuestionario;
use App\Models\TemaCuestionario;
use Carbon\Carbon;
use App\Services\CampaignFeedbackService;

class ClienteController extends Controller
{


    public function home()
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            return redirect()->route('administrador.dashboard');
        }

        $tieneSuscripcionActiva = Suscripcion::where('usuario_id', $user->id)
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->exists();

        if ($tieneSuscripcionActiva) {
            return redirect()->route('clientes.dashboard');
        }

        $data = [
            'planes' => Plan::all(),
            'tieneSuscripcionActiva' => $tieneSuscripcionActiva,
            'tieneSuscripcionPendiente' => false,
            'suscripcionPendiente' => null,
            'pagoPendiente' => null,
        ];

        $data['pagoPendiente'] = Pago::with(['codigoPago', 'plan', 'suscripcion'])
            ->where('usuario_id', $user->id)
            ->where('estado', 'pendiente')
            ->whereHas('suscripcion', fn ($query) => $query->where('estado', 'pendiente'))
            ->latest('id')
            ->first();
        $data['suscripcionPendiente'] = $data['pagoPendiente']?->suscripcion;
        $data['tieneSuscripcionPendiente'] = $data['pagoPendiente'] !== null;

        return view('clientes.home', $data);
    }

    public function comprarOtroPlan()
    {
        $planes = Plan::with('planCaracteristicas.caracteristica')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $pagoPendiente = Pago::with(['plan', 'codigoPago'])
            ->where('usuario_id', Auth::id())
            ->where('metodo', 'fisico')
            ->where('estado', 'pendiente')
            ->latest('id')
            ->first();

        return view('clientes.comprar-plan', compact('planes', 'pagoPendiente'));
    }

    public function dashboard(Request $request, CampaignFeedbackService $feedbackService)
    {
        $user = Auth::user();
        $hasExistingCompany = $user->empresas()->exists();

        if (! $this->hasCompletedInitialSetup($user) && ! $hasExistingCompany) {
            return redirect()->route('clientes.onboarding');
        }

        $pendingSetupSubscription = $hasExistingCompany
            ? $user->suscripciones()
                ->with('plan')
                ->where('estado', 'activa')
                ->where(function ($query) {
                    $query->whereNull('vigencia_activada_at')
                        ->orWhere('fecha_fin', '>', now());
                })
                ->whereDoesntHave('empresa')
                ->whereHas('pagos', fn ($query) => $query->where('estado', 'completado'))
                ->latest('id')
                ->first()
            : null;

        $suscripcionesDisponibles = Suscripcion::with(['plan', 'empresa', 'campanias'])
            ->where('usuario_id', $user->id)
            ->whereHas('empresa')
            ->latest('id')
            ->get();

        $empresaSolicitadaId = $request->integer('empresa');
        $suscripcionActiva = $empresaSolicitadaId
            ? $suscripcionesDisponibles->first(fn ($suscripcion) => (int) $suscripcion->empresa?->id === $empresaSolicitadaId)
            : null;
        $suscripcionActiva ??= $suscripcionesDisponibles->first(fn ($suscripcion) => $suscripcion->campanias->contains(
            fn ($campania) => in_array($campania->estado, ['activa', 'pausada'], true)
                && Carbon::parse($campania->fecha_fin)->gte(now()->startOfDay())
        ));
        $suscripcionActiva ??= $suscripcionesDisponibles->first(fn ($suscripcion) => $suscripcion->esta_activa);
        $suscripcionActiva ??= $suscripcionesDisponibles->firstOrFail();
        $dashboardCompanies = $suscripcionesDisponibles
            ->pluck('empresa')
            ->filter()
            ->unique('id')
            ->values();

        $diasRestantes = null;
        $porcentajeRestante = null;
        $campaniaDashboard = $suscripcionActiva->campanias()
            ->latest('id')
            ->first();
        $campaignDashboardSummary = null;

        if ($campaniaDashboard) {
            $visibleTasks = $campaniaDashboard->tareas()
                ->where('visible_cliente', true)
                ->with([
                    'archivos' => fn ($query) => $query->latest('id'),
                    'comentarios' => fn ($query) => $query->with('user')->latest('id')->limit(8),
                ])
                ->orderBy('fecha_limite')
                ->get();
            $totalTasks = $visibleTasks->count();
            $completedTasks = $visibleTasks->whereIn('estado', ['entregado', 'aprobado', 'publicado'])->count();
            $pendingReviewFiles = $visibleTasks->flatMap(function ($task) {
                return $task->requiere_aprobacion
                    ? $task->archivos->where('estado', 'pendiente')->map(fn ($file) => ['task' => $task, 'file' => $file])
                    : collect();
            })->values();
            $scheduledTasks = $visibleTasks->where('publication_status', 'scheduled');
            $publishedTasks = $visibleTasks->where('publication_status', 'published');
            $nextPublication = $scheduledTasks
                ->filter(fn ($task) => $task->publication_scheduled_at && $task->publication_scheduled_at->isFuture())
                ->sortBy('publication_scheduled_at')
                ->first();
            $upcomingTasks = $visibleTasks
                ->map(function ($task) {
                    $task->dashboard_date = $task->publication_scheduled_at ?: $task->fecha_limite;

                    return $task;
                })
                ->filter(fn ($task) => $task->dashboard_date && Carbon::parse($task->dashboard_date)->gte(now()->startOfDay()))
                ->sortBy('dashboard_date')
                ->take(5)
                ->values();

            $campaignDashboardSummary = [
                'tasks' => $visibleTasks,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'progress' => $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0,
                'pending_review_files' => $pendingReviewFiles,
                'scheduled_count' => $scheduledTasks->count(),
                'published_count' => $publishedTasks->count(),
                'next_publication' => $nextPublication,
                'upcoming_tasks' => $upcomingTasks,
                'unread_messages' => $feedbackService->unreadCount($campaniaDashboard, $user),
            ];
        }

        if ($campaniaDashboard?->fecha_fin) {
            $hoy = now()->startOfDay();
            $fechaInicio = Carbon::parse($campaniaDashboard->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($campaniaDashboard->fecha_fin)->startOfDay();
            $diasRestantes = max(0, (int) $hoy->diffInDays($fechaFin, false));
            $diasTotales = max(1, (int) $fechaInicio->diffInDays($fechaFin));
            $porcentajeRestante = min(100, round(($diasRestantes / $diasTotales) * 100));
        }

        $empresas = $user->empresas;
        $empresaActiva = $suscripcionActiva->empresa;
        $dashboardSocialAccounts = collect();

        if ($empresaActiva && $user->socialAccountsTableExists()) {
            $empresaActiva->load('socialAccounts');
            $dashboardSocialAccounts = $empresaActiva->socialAccounts->keyBy('provider');

            $isFirstCompany = (int) $empresas->sortBy('id')->first()?->id === (int) $empresaActiva->id;
            if ($isFirstCompany) {
                $dashboardSocialAccounts = $dashboardSocialAccounts
                    ->union($user->linkedSocialAccounts());
            }
        }
        $empresaCuestionario = $empresaActiva && ! $empresaActiva->cuestionario_completado
            ? $empresaActiva
            : null;
        $temasCuestionario = $empresaCuestionario
            ? TemaCuestionario::with('preguntas')->orderBy('orden')->get()
            : collect();
        $respuestasCuestionario = $empresaCuestionario
            ? RespuestaCuestionario::where('empresa_id', $empresaCuestionario->id)
                ->pluck('respuesta', 'pregunta_id')
                ->toArray()
            : [];

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
            'empresaActiva',
            'dashboardCompanies',
            'dashboardSocialAccounts',
            'pendingSetupSubscription',
            'empresaCuestionario',
            'temasCuestionario',
            'respuestasCuestionario',
            'campaniaDashboard',
            'campaignDashboardSummary',
            'data' // Pasamos los datos de analíticas a la vista
        ));
    }

    public function onboarding(Request $request)
    {
        $user = Auth::user();
        $socialAccounts = $user->linkedSocialAccounts();
        $facebookAccount = $socialAccounts->get('facebook');
        $facebookPage = $socialAccounts->get('facebook_page');
        $instagramAccount = $socialAccounts->get('instagram');
        $facebookLinked = filled($facebookAccount?->provider_user_id);
        $instagramLinked = filled($instagramAccount?->provider_user_id);
        $anyAccountLinked = $facebookLinked || $instagramLinked;
        $socialSetupSkipped = (bool) $user->social_setup_skipped;
        $canContinueSocialSetup = $anyAccountLinked || $socialSetupSkipped;
        $requestedSubscriptionId = $request->integer('suscripcion');
        if ($requestedSubscriptionId > 0) {
            $request->session()->put('onboarding.subscription_id', $requestedSubscriptionId);
        }

        $onboardingSubscriptionId = (int) $request->session()->get('onboarding.subscription_id', 0);
        $subscriptionQuery = $user->suscripciones()
            ->with('empresa')
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            });

        if ($onboardingSubscriptionId > 0) {
            $subscriptionQuery->whereKey($onboardingSubscriptionId);
        }

        $suscripcionActiva = $subscriptionQuery->latest('id')->first();
        if (! $suscripcionActiva && $onboardingSubscriptionId > 0) {
            $request->session()->forget('onboarding.subscription_id');
            $suscripcionActiva = $user->suscripciones()
                ->with('empresa')
                ->where('estado', 'activa')
                ->where(function ($query) {
                    $query->whereNull('vigencia_activada_at')
                        ->orWhere('fecha_fin', '>', now());
                })
                ->latest('id')
                ->first();
        }

        $onboardingSubscriptionId = $suscripcionActiva?->id;
        if ($onboardingSubscriptionId) {
            $request->session()->put('onboarding.subscription_id', $onboardingSubscriptionId);
        }
        $empresa = $suscripcionActiva?->empresa;
        $isReturningSetup = ! $empresa && $user->empresas()
            ->when($onboardingSubscriptionId, fn ($query) => $query->where('suscripcion_id', '!=', $onboardingSubscriptionId))
            ->exists();
        $suggestedCompanyName = $empresa?->nombre_empresa
            ?? $facebookPage?->display_name
            ?? data_get($facebookPage?->metadata, 'page_name')
            ?? '';

        $initialStep = 1;
        if ($request->boolean('inicio')) {
            $initialStep = 1;
        } elseif ($request->query('empresa') === 'editada' && $empresa) {
            $initialStep = 3;
        } elseif ($request->query('empresa') === 'creada' && $empresa) {
            $initialStep = 4;
        } elseif (session()->has('social_accounts_error') || session()->has('social_accounts_success')) {
            $initialStep = $anyAccountLinked ? 3 : 2;
        } elseif ($request->session()->has('errors')) {
            $initialStep = 3;
        } elseif ($canContinueSocialSetup && $empresa) {
            $initialStep = 4;
        } elseif ($socialSetupSkipped) {
            $initialStep = 3;
        }

        return view('clientes.popupRedes', compact(
            'user',
            'facebookLinked',
            'instagramLinked',
            'anyAccountLinked',
            'socialSetupSkipped',
            'canContinueSocialSetup',
            'empresa',
            'onboardingSubscriptionId',
            'isReturningSetup',
            'suggestedCompanyName',
            'initialStep'
        ));
    }

    public function skipSocialAccounts(Request $request)
    {
        Auth::user()->update(['social_setup_skipped' => true]);

        return redirect()->route('clientes.onboarding', [
            'suscripcion' => $request->session()->get('onboarding.subscription_id'),
        ]);
    }

    public function storeOnboardingCompany(Request $request)
    {
        $user = Auth::user();

        if (! $user->social_setup_skipped
            && ! $user->hasLinkedSocialAccount('facebook')
            && ! $user->hasLinkedSocialAccount('instagram')) {
            return redirect()->route('clientes.onboarding')
                ->with('onboarding_error', 'Vincula al menos una red social antes de crear tu empresa.');
        }

        $onboardingSubscriptionId = (int) $request->session()->get('onboarding.subscription_id', 0);
        $suscripcionActiva = $user->suscripciones()
            ->with('empresa')
            ->when($onboardingSubscriptionId > 0, fn ($query) => $query->whereKey($onboardingSubscriptionId))
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->latest('id')
            ->first();

        if (! $suscripcionActiva) {
            return redirect()->route('clientes.home')
                ->with('error', 'Necesitas una suscripción activa para registrar una empresa.');
        }

        if ($suscripcionActiva->empresa) {
            return redirect()->route('clientes.onboarding', ['empresa' => 'creada', 'suscripcion' => $suscripcionActiva->id])
                ->with('onboarding_success', 'Esta empresa ya está asociada a tu plan.');
        }

        $validated = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:255'],
            'tipo_empresa' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'descripcion' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $empresa = new Empresa([
            'suscripcion_id' => $suscripcionActiva->id,
            'nombre_empresa' => $validated['nombre_empresa'],
            'tipo_empresa' => $validated['tipo_empresa'],
            'direccion' => $validated['direccion'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
        ]);
        $empresa->usuario_id = $user->id;

        if ($request->hasFile('logo')) {
            $empresa->logo = $request->file('logo')->store('logos', 'public');
        }

        $empresa->save();

        return redirect()->route('clientes.onboarding', ['empresa' => 'creada', 'suscripcion' => $suscripcionActiva->id])
            ->with('onboarding_success', 'Tu empresa fue creada correctamente.');
    }

    public function completeOnboarding(Request $request)
    {
        $user = Auth::user();
        $onboardingSubscriptionId = (int) $request->session()->get('onboarding.subscription_id', 0);
        $completedSubscription = $onboardingSubscriptionId > 0
            ? $user->suscripciones()->with('empresa')->find($onboardingSubscriptionId)
            : null;
        $hasSocialAccount = $user->social_setup_skipped
            || $user->hasLinkedSocialAccount('facebook')
            || $user->hasLinkedSocialAccount('instagram');

        if (! $hasSocialAccount || ! $completedSubscription?->empresa) {
            return redirect()->route('clientes.onboarding', ['suscripcion' => $onboardingSubscriptionId ?: null])
                ->with('onboarding_error', 'Completa la vinculación y crea tu empresa antes de continuar.');
        }

        $empresaId = $completedSubscription->empresa->id;
        $request->session()->forget('onboarding.subscription_id');

        return redirect()->route('clientes.dashboard', ['empresa' => $empresaId])
            ->with('success', '¡Configuración completada! Ya puedes comenzar.');
    }

    private function hasCompletedInitialSetup($user): bool
    {
        $hasSocialAccount = $user->social_setup_skipped
            || $user->hasLinkedSocialAccount('facebook')
            || $user->hasLinkedSocialAccount('instagram');

        $suscripcionActiva = $user->suscripciones()
            ->with('empresa')
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->latest('id')
            ->first();

        return $hasSocialAccount && $suscripcionActiva?->empresa !== null;
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
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->first();

        // Calcular días restantes si hay suscripción activa
        $diasRestantes = null;
        $porcentajeRestante = null;

        if ($suscripcionActiva?->vigencia_activada_at) {
            $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
            $diasRestantes = now()->diffInDays($fechaFin, false);
            $diasTotales = max(1, $suscripcionActiva->fecha_inicio->diffInDays($fechaFin));
            $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;
        }

        // NUEVO: Obtener todas las empresas del usuario
        $empresas = $user->empresas;

        if ($user->socialAccountsTableExists()) {
            $empresas->load('socialAccounts');
        }
        $globalSocialAccounts = $user->linkedSocialAccounts();

        return view('clientes.micuenta', compact(
            'user',
            'suscripcionActiva',
            'diasRestantes',
            'porcentajeRestante',
            'empresas',
            'globalSocialAccounts'
        ));
    }

    public function updateAccountData(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'phone.regex' => 'El celular solo debe contener números.',
        ]);

        $request->user()->update($validated);

        return redirect()->route('clientes.micuenta')->with('account_success', 'Tus datos fueron actualizados correctamente.');
    }

    public function requestPasswordChange(Request $request)
    {
        $user = $request->user();
        $token = Password::broker()->createToken($user);
        $resetUrl = route('clientes.password.reset.form', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new CambioContrasenaCuenta($user, $resetUrl));

        return redirect()->route('clientes.micuenta')->with(
            'password_link_sent',
            'Te enviamos un correo de confirmación. Abre el enlace para crear tu nueva contraseña.'
        );
    }

    public function showPasswordReset(Request $request, string $token)
    {
        return view('clientes.cambiar-contrasena', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request, string $token)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'password.required' => 'Ingresa tu nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $status = Password::broker()->reset(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'El enlace venció o ya fue utilizado. Solicita uno nuevo desde Mi cuenta.']);
        }

        $request->session()->regenerate();

        return redirect()->route('clientes.micuenta')->with('account_success', 'Tu contraseña fue actualizada correctamente.');
    }



}
