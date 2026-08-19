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

    public function comprarOtroPlan()
    {
        $planes = Plan::with('planCaracteristicas.caracteristica')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return view('clientes.comprar-plan', compact('planes'));
    }

    public function dashboard()
    {
        $user = Auth::user();

        if (! $this->hasCompletedInitialSetup($user)) {
            return redirect()->route('clientes.onboarding');
        }

        $suscripcionActiva = Suscripcion::with(['plan', 'empresa'])
            ->where('usuario_id', $user->id)
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->latest('id')
            ->firstOrFail();

        $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
        $diasRestantes = now()->diffInDays($fechaFin, false);
        $diasTotales = $suscripcionActiva->fecha_inicio->diffInDays($fechaFin);
        $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;

        $empresas = $user->empresas;
        $empresaActiva = $suscripcionActiva->empresa;
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
            'empresaCuestionario',
            'temasCuestionario',
            'respuestasCuestionario',
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
        $suscripcionActiva = $user->suscripciones()
            ->with('empresa')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->latest('id')
            ->first();
        $empresa = $suscripcionActiva?->empresa;
        $suggestedCompanyName = $empresa?->nombre_empresa
            ?? $facebookPage?->display_name
            ?? data_get($facebookPage?->metadata, 'page_name')
            ?? '';

        $initialStep = 1;
        if ($request->query('empresa') === 'editada' && $empresa) {
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
            'suggestedCompanyName',
            'initialStep'
        ));
    }

    public function skipSocialAccounts()
    {
        Auth::user()->update(['social_setup_skipped' => true]);

        return redirect()->route('clientes.onboarding');
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

        $suscripcionActiva = $user->suscripciones()
            ->with('empresa')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->latest('id')
            ->first();

        if (! $suscripcionActiva) {
            return redirect()->route('clientes.home')
                ->with('error', 'Necesitas una suscripción activa para registrar una empresa.');
        }

        if ($suscripcionActiva->empresa) {
            return redirect()->route('clientes.onboarding', ['empresa' => 'creada'])
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

        return redirect()->route('clientes.onboarding', ['empresa' => 'creada'])
            ->with('onboarding_success', 'Tu empresa fue creada correctamente.');
    }

    public function completeOnboarding()
    {
        $user = Auth::user();

        if (! $this->hasCompletedInitialSetup($user)) {
            return redirect()->route('clientes.onboarding')
                ->with('onboarding_error', 'Completa la vinculación y crea tu empresa antes de continuar.');
        }

        return redirect()->route('clientes.dashboard')
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
            ->where('fecha_fin', '>', now())
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

    public function updateAccountData(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'phone.regex' => 'Ingresa un número de teléfono válido.',
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
