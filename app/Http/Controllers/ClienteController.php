<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Suscripcion;
use App\Models\Pago;
use App\Models\Empresa;
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

        if (! $this->hasCompletedInitialSetup($user)) {
            return redirect()->route('clientes.onboarding');
        }

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
        $empresa = $user->empresas()->latest('id')->first();
        $suggestedCompanyName = $empresa?->nombre_empresa
            ?? $facebookPage?->display_name
            ?? data_get($facebookPage?->metadata, 'page_name')
            ?? '';

        $initialStep = 1;
        if ($request->query('empresa') === 'creada' && $empresa) {
            $initialStep = 4;
        } elseif (session()->has('social_accounts_error') || session()->has('social_accounts_success')) {
            $initialStep = $anyAccountLinked ? 3 : 2;
        } elseif ($request->session()->has('errors')) {
            $initialStep = 3;
        } elseif ($anyAccountLinked && $empresa) {
            $initialStep = 4;
        }

        return view('clientes.popupRedes', compact(
            'user',
            'facebookLinked',
            'instagramLinked',
            'anyAccountLinked',
            'empresa',
            'suggestedCompanyName',
            'initialStep'
        ));
    }

    public function storeOnboardingCompany(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasLinkedSocialAccount('facebook') && ! $user->hasLinkedSocialAccount('instagram')) {
            return redirect()->route('clientes.onboarding')
                ->with('onboarding_error', 'Vincula al menos una red social antes de crear tu empresa.');
        }

        $validated = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:255'],
            'tipo_empresa' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $empresa = new Empresa([
            'nombre_empresa' => $validated['nombre_empresa'],
            'tipo_empresa' => $validated['tipo_empresa'],
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
        $hasSocialAccount = $user->hasLinkedSocialAccount('facebook')
            || $user->hasLinkedSocialAccount('instagram');

        return $hasSocialAccount && $user->empresas()->exists();
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

