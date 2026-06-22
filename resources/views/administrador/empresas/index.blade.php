@extends('layouts.app')

@section('title', 'Empresas Registradas')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
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

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #e37225; border: 1px solid rgba(255,255,255,0.2);">
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

                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
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

                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
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

                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #14697b; border: 1px solid rgba(255,255,255,0.2);">
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
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white shadow-md">
                    <i class="fas fa-filter text-sm"></i>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
            </div>
            <form method="GET" action="{{ route('administrador.empresas.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Filtro por Usuario -->
                <div>
                    <label for="usuario_id" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user mr-1 text-gray-400"></i>Usuario
                    </label>
                    <select id="usuario_id" name="usuario_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
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
                    <select id="plan_id" name="plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
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
                    <select id="estado" name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
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
                    <select id="per_page" name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($empresas as $empresa)
                <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="block transform transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl animate-fade-up">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden h-full hover:border-indigo-200 transition-all">
                        <!-- Header de la card -->
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
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
                        <div class="p-6">
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
                        <select id="per_page_footer" name="per_page" class="w-44 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
</style>
@endsection
