@extends('layouts.app')

@section('title', 'Empresas Registradas')

@section('content')
<div class="companies-page min-h-screen">
    <div class="companies-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="companies-hero mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4 sm:gap-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-building text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Empresas Registradas</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Gestiona todas las empresas registradas en el sistema</p>
                        </div>
                    </div>
                    <a href="{{ route('administrador.dashboard') }}" 
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" 
                       style="background: linear-gradient(to right, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver al Panel
                    </a>
                </div>
            </div>
        </div>

        <div class="companies-kpis grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mx-3 sm:mx-6 mb-6">
                    <div class="company-kpi company-kpi-total rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #e37225; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Total Empresas</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-building text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="company-kpi company-kpi-filtered rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Resultados Filtrados</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $stats['filtradas'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-filter text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="company-kpi company-kpi-complete rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Cuestionario Completo</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $stats['cuestionario_completado'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clipboard-check text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="company-kpi company-kpi-inactive rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #14697b; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Sin Suscripción Activa</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $stats['sin_suscripcion_activa'] }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-ban text-white text-base"></i>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Filtros -->
        <div class="companies-filters bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-8">
            <form method="GET" action="{{ route('administrador.empresas.index') }}" class="companies-filter-grid grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Filtro por Usuario -->
                <div>
                    <label for="usuario_id" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-1 text-gray-400"></i>Usuario
                    </label>
                    <select id="usuario_id" name="usuario_id" data-company-select data-icon="fa-user" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Todos los usuarios</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }} ({{ $usuario->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Plan -->
                <div>
                    <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-crown mr-1 text-gray-400"></i>Plan
                    </label>
                    <select id="plan_id" name="plan_id" data-company-select data-icon="fa-crown" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Todos los planes</option>
                        @foreach($planes as $plan)
                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-toggle-on mr-1 text-gray-400"></i>Estado
                    </label>
                    <select id="estado" name="estado" data-company-select data-icon="fa-toggle-on" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Todos los estados</option>
                        <option value="activa" {{ request('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                        <option value="inactiva" {{ request('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>

                <!-- Botón de filtrar -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-list-ol mr-1 text-gray-400"></i>Empresas por página
                    </label>
                    <select id="per_page" name="per_page" data-company-select data-icon="fa-list-ol" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        @foreach([6, 9, 12, 18, 24] as $option)
                            <option value="{{ $option }}" {{ (int) request('per_page', $perPage) === $option ? 'selected' : '' }}>
                                {{ $option }} empresas
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-indigo-200/50 font-medium">
                        <i class="fas fa-search mr-2"></i>
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Contador de resultados -->
        <div class="mb-6 text-gray-600 flex items-center gap-2">
            <i class="fas fa-chart-bar text-indigo-500"></i>
            <span>Mostrando <strong>{{ $empresas->count() }}</strong> de <strong>{{ $empresas->total() }}</strong> empresas</span>
        </div>

        <!-- Grid de empresas -->
        <div class="companies-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($empresas as $empresa)
                <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="company-card-link block transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl animate-fade-up">
                    <div class="company-card bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden h-full hover:border-indigo-200 transition-all">
                        <!-- Header de la card -->
                        <div class="company-card-head bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                            <div class="flex items-center">
                                @if($empresa->logo)
                                    <div class="w-16 h-16 bg-white rounded-xl p-2 mr-4 shadow-md">
                                        <img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}" class="w-full h-full object-contain">
                                    </div>
                                @else
                                    <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center mr-4 shadow-md">
                                        <i class="fas fa-building text-white text-2xl"></i>
                                    </div>
                                @endif
                                <div class="text-white">
                                    <h3 class="text-xl font-bold truncate">{{ $empresa->nombre_empresa }}</h3>
                                    <p class="text-indigo-100 text-sm">{{ $empresa->tipo_empresa }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido de la card -->
                        <div class="company-card-body p-6">
                            <!-- Información del usuario -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">
                                    <i class="fas fa-user mr-1 text-gray-400"></i>Propietario
                                </p>
                                <p class="text-gray-900 font-medium">{{ $empresa->usuario->name }}</p>
                                <p class="text-gray-600 text-sm">{{ $empresa->usuario->email }}</p>
                            </div>

                            <!-- Información del plan -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">
                                    <i class="fas fa-crown mr-1 text-gray-400"></i>Plan
                                </p>
                                @if($empresa->usuario->suscripciones->isNotEmpty())
                                    @php
                                        $suscripcionActiva = $empresa->usuario->suscripciones->where('estado', 'activa')->first();
                                    @endphp
                                    @if($suscripcionActiva)
                                        <p class="text-gray-900 font-medium">{{ $suscripcionActiva->plan->nombre }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                                Activo
                                            </span>
                                            <span class="text-gray-500 text-xs ml-2">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Vence: {{ $suscripcionActiva->fecha_fin->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-gray-900 font-medium">Sin suscripción activa</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                @else
                                    <p class="text-gray-900 font-medium">Sin suscripción</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        <i class="fas fa-minus-circle mr-1"></i>
                                        Sin plan
                                    </span>
                                @endif
                            </div>

                            <!-- Estado del cuestionario -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">
                                    <i class="fas fa-file-alt mr-1 text-gray-400"></i>Cuestionario
                                </p>
                                @if($empresa->cuestionario_completado)
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                            Completado
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fas fa-clock mr-1 text-amber-500"></i>
                                            Pendiente
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Descripción (truncada) -->
                            @if($empresa->descripcion)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 mb-1">
                                        <i class="fas fa-align-left mr-1 text-gray-400"></i>Descripción
                                    </p>
                                    <p class="text-gray-700 text-sm line-clamp-3">{{ Str::limit($empresa->descripcion, 100) }}</p>
                                </div>
                            @endif

                            <!-- Fecha de registro -->
                            <div>
                                <p class="text-sm text-gray-500 mb-1">
                                    <i class="fas fa-calendar-plus mr-1 text-gray-400"></i>Fecha de registro
                                </p>
                                <p class="text-gray-700 text-sm">{{ $empresa->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12 bg-white rounded-2xl shadow-lg border border-gray-100">
                    <i class="fas fa-building text-4xl text-gray-300 block mb-4"></i>
                    <h3 class="text-sm font-medium text-gray-900">No se encontraron empresas</h3>
                    <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros para ver más resultados.</p>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($empresas->hasPages())
            <div class="mt-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <form method="GET" action="{{ route('administrador.empresas.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input type="hidden" name="usuario_id" value="{{ request('usuario_id') }}">
                        <input type="hidden" name="plan_id" value="{{ request('plan_id') }}">
                        <input type="hidden" name="estado" value="{{ request('estado') }}">
                        <label for="per_page_footer" class="text-sm font-medium text-gray-700">Empresas por página</label>
                        <select id="per_page_footer" name="per_page" data-company-select data-icon="fa-list-ol" class="w-44 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach([6, 9, 12, 18, 24] as $option)
                                <option value="{{ $option }}" {{ (int) request('per_page', $perPage) === $option ? 'selected' : '' }}>
                                    {{ $option }} empresas
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-md hover:shadow-indigo-200/50">
                            Aplicar
                        </button>
                    </form>

                    <div>
                        {{ $empresas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Banner geométrico - Mismo estilo que las otras vistas */
    .rp-banner {
        background:
            linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, #4f46e5 25%, transparent 25%),
            linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
            linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
        background-size:
            100px 100px,
            100px 100px,
            100px 100px,
            100px 100px,
            100% 100%;
        background-color: #1d4ed8;
        position: relative;
    }

    .rp-banner-overlay {
        background:
            radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size:     50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat:   no-repeat;
    }

    @media (max-width: 640px) {
        .rp-banner .px-8 { 
            padding-left: 1.25rem; 
            padding-right: 1.25rem; 
        }
        .rp-banner .flex.flex-col.sm\:flex-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .rp-banner a {
            justify-content: center;
            width: 100%;
        }
    }

    .companies-page{min-height:100vh;padding-bottom:48px;background:#f7f8fa!important;color:#302834}.companies-shell{max-width:none!important;padding:0!important}.companies-hero{overflow:visible!important;margin:0 0 24px!important;border-radius:0!important}.companies-hero>.relative{max-width:1536px;margin:auto;padding:30px 48px 34px!important}.companies-hero>.relative>.flex:first-child{margin-bottom:22px!important}.companies-hero h1{font-weight:900;letter-spacing:-.035em}.companies-hero>a,.companies-hero .flex>a{border:1px solid #fff!important;background:#fff!important;color:#2563eb!important;box-shadow:0 8px 20px rgba(15,23,42,.17)!important}.companies-kpis{gap:14px!important}.company-kpi{--kpi-accent:#117e8c;--kpi-soft:#e6f4f5;--kpi-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;min-height:124px;padding:20px!important;border:1px solid rgba(var(--kpi-rgb),.25)!important;border-radius:16px!important;background:linear-gradient(135deg,#fff 35%,var(--kpi-soft))!important;box-shadow:inset 0 4px 0 var(--kpi-accent),0 12px 25px rgba(15,23,42,.14)}.company-kpi::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--kpi-rgb),.09);border-radius:50%}.company-kpi::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px);background-size:9px 9px}.company-kpi>div{height:100%;display:flex;align-items:center;justify-content:space-between}.company-kpi p:first-child{color:#596170!important;font-size:.68rem!important;font-weight:900;text-transform:uppercase}.company-kpi p:nth-child(2){margin-top:8px!important;color:#263024!important;font-size:1.85rem!important;line-height:1}.company-kpi>div>div:last-child{width:50px!important;height:50px!important;display:grid!important;place-items:center;border-radius:14px!important;background:var(--kpi-accent)!important;color:#fff;box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27)}.company-kpi-filtered{--kpi-accent:#5b2b76;--kpi-soft:#f3edf6;--kpi-rgb:91,43,118}.company-kpi-complete{--kpi-accent:#7da533;--kpi-soft:#f0f6e7;--kpi-rgb:125,165,51}.company-kpi-inactive{--kpi-accent:#e37225;--kpi-soft:#fff0e6;--kpi-rgb:227,114,37}.companies-filters{position:relative;z-index:20;margin:0 24px 22px!important;padding:18px 20px!important;border:1px solid #e1e3de!important;border-radius:16px!important;background:#f8f8f6!important;box-shadow:0 9px 22px rgba(55,60,52,.06)!important}.companies-filters>.flex:first-child{margin-bottom:13px!important}.companies-filters>.flex:first-child>div{width:35px;height:35px;border-radius:9px!important;background:#eceeeb!important;color:#62685f!important;box-shadow:none!important}.companies-filters h2{color:#454a43;font-size:.86rem}.companies-filter-grid{grid-template-columns:repeat(12,minmax(0,1fr))!important;gap:12px!important;align-items:end}.companies-filter-grid>div{grid-column:span 3}.companies-filter-grid>div:last-child{grid-column:span 12}.companies-filter-grid label{color:#565d53;font-size:.66rem;font-weight:900}.companies-filter-grid button[type=submit]{min-height:46px;border-radius:10px!important;background:#117e8c!important;box-shadow:none!important;font-size:.75rem;font-weight:900}.company-native-select{position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important}.company-custom-select{position:relative}.company-custom-trigger{position:relative;width:100%;height:46px;display:flex;align-items:center;gap:10px;padding:0 40px 0 14px;border:1px solid #d9dcd6;border-radius:10px;background:#fff;color:#3f443d;text-align:left;box-shadow:0 2px 5px rgba(55,60,52,.07)}.company-custom-trigger>i:first-child{width:18px;color:#737a70;text-align:center}.company-custom-trigger span{min-width:0;overflow:hidden;flex:1;font-size:.72rem;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.company-custom-trigger>i:last-child{position:absolute;right:14px;color:#8a9186;font-size:.65rem;transition:.18s}.company-custom-select.is-open .company-custom-trigger{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}.company-custom-select.is-open .company-custom-trigger>i:last-child{transform:rotate(180deg)}.company-custom-menu{position:absolute;z-index:100;top:calc(100% + 7px);right:0;left:0;display:none;max-height:250px;overflow-y:auto;padding:7px;border:1px solid #d9dcd6;border-radius:11px;background:#fff;box-shadow:0 18px 38px rgba(55,60,52,.17)}.company-custom-select.is-open .company-custom-menu{display:grid;gap:3px}.company-custom-menu button{min-height:38px;padding:8px 10px;border-radius:7px;color:#565d53;text-align:left;font-size:.7rem;font-weight:750}.company-custom-menu button:hover,.company-custom-menu button.is-selected{background:#eff0ed;color:#3f443d}.companies-filters+div{margin:0 24px 14px!important;padding:0 2px;color:#706775!important}.companies-grid{gap:20px!important;margin:0 24px}.company-card-link{--company-accent:#ef6c22;min-width:0}.company-card-link:nth-child(3n+2){--company-accent:#117e8c}.company-card-link:nth-child(3n){--company-accent:#5b2b76}.company-card-link:hover{transform:translateY(-6px)!important;box-shadow:none!important}.company-card{overflow:hidden;border:1px solid #ded7e1!important;border-top:5px solid var(--company-accent)!important;border-radius:5px!important;box-shadow:0 15px 36px #e5dfe7!important}.company-card-link:hover .company-card{box-shadow:0 22px 44px #d8d0db!important}.company-card-head{padding:22px 22px 19px!important;background:#fff!important;border-bottom:1px solid #e5dfe7}.company-card-head h3{color:#302834!important;font-size:1.15rem!important;font-weight:900}.company-card-head p{color:#756a7a!important}.company-card-head>div>div:first-child{border:1px solid #e1dce3;background:#f7f5f8!important}.company-card-head>div>div:first-child i{color:var(--company-accent)!important}.company-card-body{padding:20px 22px!important;background:#f7f5f8}.company-card-body>div{margin-bottom:0!important;padding:12px 0;border-bottom:1px solid #e3dee5}.company-card-body>div:first-child{padding-top:0}.company-card-body>div:last-child{padding-bottom:0;border-bottom:0}.company-card-body p.text-sm.text-gray-500{color:#7c727f!important;font-size:.64rem!important;font-weight:900;text-transform:uppercase}.company-card-body p.text-gray-900{color:#302834!important}.companies-grid>.col-span-full{margin:0;border:1px dashed #d4ced6!important;border-radius:14px!important;box-shadow:none!important}.companies-grid+div{margin-right:24px!important;margin-left:24px!important}
    @media(max-width:1100px){.companies-filter-grid{grid-template-columns:repeat(2,1fr)!important}.companies-filter-grid>div{grid-column:span 1}.companies-filter-grid>div:last-child{grid-column:1/-1}}@media(max-width:700px){.companies-hero>.relative{padding:24px 16px 28px!important}.companies-hero>.relative>.flex:first-child{align-items:stretch!important}.companies-kpis{grid-template-columns:1fr!important}.companies-filters,.companies-grid{margin-right:12px!important;margin-left:12px!important}.companies-filter-grid{grid-template-columns:1fr!important}.companies-filter-grid>div{grid-column:1!important}.companies-grid+div{margin-right:12px!important;margin-left:12px!important}}

    /* Alineación visual con la vista de Gestión de Pagos */
    .companies-page {
        padding: 20px 0 48px;
        background: #fff !important;
    }

    .companies-shell {
        width: 100%;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .companies-hero {
        position: relative;
        overflow: hidden !important;
        width: 100%;
        min-height: 180px;
        margin: 0 0 24px !important;
        border-radius: 0 !important;
        box-shadow: none;
    }

    .companies-hero .rp-banner-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(rgba(15, 23, 42, .28), rgba(15, 23, 42, .28)),
            radial-gradient(circle at 0% 0%, rgba(255,255,255,.20) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(255,255,255,.20) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,.20) 0%, transparent 50%),
            radial-gradient(circle at 0% 100%, rgba(255,255,255,.20) 0%, transparent 50%);
        background-size: 100% 100%, 50% 50%, 50% 50%, 50% 50%, 50% 50%;
        background-position: 0 0, 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat: no-repeat;
    }

    .companies-hero > .relative {
        max-width: none;
        margin: 0;
        min-height: 180px;
        display: flex;
        align-items: center;
        padding: 30px 48px !important;
    }

    .companies-hero > .relative > .flex:first-child {
        width: 100%;
        margin-bottom: 0 !important;
    }

    .companies-hero h1 {
        margin: 0 0 4px;
        color: #fff;
        font-size: clamp(1.55rem, 3vw, 2.25rem);
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .companies-hero h1::before {
        content: 'Administración empresarial';
        display: block;
        margin-bottom: 7px;
        color: #dbeafe;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .companies-hero p {
        color: #dbeafe !important;
        font-size: .74rem !important;
        font-weight: 600;
    }

    .companies-hero .h-14.w-14 {
        width: 52px;
        height: 52px;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 14px;
        background: rgba(255,255,255,.14) !important;
        backdrop-filter: blur(5px);
    }

    .companies-hero .flex > a {
        min-height: 42px;
        padding: 10px 13px;
        border: 1px solid #fff !important;
        border-radius: .65rem;
        background: #fff !important;
        color: #4f46e5 !important;
        box-shadow: none !important;
        font-size: .69rem;
        font-weight: 900;
    }

    .companies-kpis {
        margin: 24px 24px 0 !important;
    }

    .companies-filters {
        margin: 24px 24px 0 !important;
        padding: 20px !important;
        border: 1px solid #e1e3de !important;
        border-radius: 16px !important;
        background: #f8f8f6 !important;
        box-shadow: 0 9px 22px rgba(55,60,52,.06) !important;
    }

    .companies-filter-grid button[type=submit] {
        min-height: 50px;
        border: 1px solid #d7dad4 !important;
        border-radius: 14px !important;
        background: #fff !important;
        color: #62685f !important;
        box-shadow: none !important;
    }

    .companies-filter-grid button[type=submit]:hover {
        background: #eff0ed !important;
        color: #3f443d !important;
    }

    .company-custom-trigger {
        height: 50px;
        border-radius: 14px;
    }

    .company-custom-menu {
        border-radius: 14px;
    }

    .companies-filters + div,
    .companies-grid,
    .companies-grid + div {
        margin-right: 24px !important;
        margin-left: 24px !important;
    }

    .company-card,
    .company-card-head,
    .company-card-body {
        background: #fff !important;
    }

    @media (max-width: 700px) {
        .companies-hero > .relative {
            min-height: 205px;
            padding: 28px 20px !important;
        }

        .companies-kpis,
        .companies-filters,
        .companies-grid {
            margin-right: 12px !important;
            margin-left: 12px !important;
        }

        .companies-filters {
            padding: 20px !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdowns = [];
    const closeDropdowns = except => dropdowns.forEach(dropdown => {
        if (dropdown !== except) dropdown.classList.remove('is-open');
    });

    document.querySelectorAll('[data-company-select]').forEach(function (select) {
        const wrapper = document.createElement('div');
        const trigger = document.createElement('button');
        const leadingIcon = document.createElement('i');
        const label = document.createElement('span');
        const chevron = document.createElement('i');
        const menu = document.createElement('div');
        wrapper.className = 'company-custom-select';
        trigger.type = 'button';
        trigger.className = 'company-custom-trigger';
        leadingIcon.className = 'fas ' + (select.dataset.icon || 'fa-list');
        chevron.className = 'fas fa-chevron-down';
        menu.className = 'company-custom-menu';
        select.classList.add('company-native-select');
        trigger.append(leadingIcon, label, chevron);
        wrapper.append(trigger, menu);
        select.insertAdjacentElement('afterend', wrapper);
        dropdowns.push(wrapper);

        const sync = () => {
            label.textContent = select.options[select.selectedIndex]?.textContent || 'Seleccionar';
            menu.querySelectorAll('button').forEach(button => button.classList.toggle('is-selected', button.dataset.value === select.value));
        };
        Array.from(select.options).forEach(function (option) {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.value = option.value;
            button.textContent = option.textContent;
            button.addEventListener('click', function (event) {
                event.stopPropagation();
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                sync();
                closeDropdowns();
            });
            menu.appendChild(button);
        });
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const opening = !wrapper.classList.contains('is-open');
            closeDropdowns(wrapper);
            wrapper.classList.toggle('is-open', opening);
        });
        select.addEventListener('change', sync);
        sync();
    });
    document.addEventListener('click', () => closeDropdowns());
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeDropdowns(); });
});
</script>
@endsection
