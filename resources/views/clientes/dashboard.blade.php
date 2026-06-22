@extends('layouts.app2')

@section('title', 'Dashboard del Cliente')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    $suscripcionActiva->loadMissing('plan.planCaracteristicas.caracteristica');
    $plan = $suscripcionActiva->plan;
    $planCaracteristicas = $plan?->planCaracteristicas ?? collect();
@endphp

@include('clientes.popupRedes')

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Banner con fondo geométrico -->
        <div class="mb-0 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-10 md:px-10 md:py-12">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">
                            Bienvenido, {{ $user->name }}
                        </h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;" class="max-w-3xl">
                            Consulta el estado de tus campañas, revisa métricas de rendimiento y descubre oportunidades para mejorar tus resultados en redes sociales.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 space-y-8">
                <!-- Plan Contratado -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #ea9f21;">
                                <i class="fas fa-crown text-white text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Plan Contratado</h2>
                                <p class="text-gray-600 text-sm mt-0.5">Detalles de tu suscripción actual</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-2xl border border-indigo-200/50 p-6 md:p-8">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1">
                                    <div class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 border border-indigo-200">
                                        <i class="fas fa-check-circle mr-1.5 text-indigo-500 text-[10px]"></i>
                                        {{ $plan->subtitulo ?: 'Plan activo' }}
                                    </div>
                                    <h3 class="mt-4 text-3xl font-bold text-gray-900">{{ $plan->nombre }}</h3>
                                    @if($plan->descripcion)
                                        <p class="mt-3 max-w-2xl text-gray-600 leading-7">{{ $plan->descripcion }}</p>
                                    @endif
                                </div>

                                <div class="rounded-2xl px-6 py-5 text-white shadow-lg min-w-[250px]" style="background: #475569;">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-chart-simple text-slate-300 text-sm"></i>
                                        <p class="text-sm text-slate-300">Estado del plan</p>
                                    </div>
                                    <p class="mt-1 text-2xl font-bold capitalize flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block"></span>
                                        {{ $suscripcionActiva->estado }}
                                    </p>
                                    <div class="mt-5 space-y-3 text-sm text-slate-200">
                                        <div class="flex items-center justify-between gap-4 border-b border-slate-700/50 pb-2">
                                            <span class="flex items-center gap-2">
                                                <i class="fas fa-calendar-day text-slate-400 text-xs"></i>
                                                Vigencia
                                            </span>
                                            <span class="font-semibold">{{ intval($diasRestantes) }} días</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4 border-b border-slate-700/50 pb-2">
                                            <span class="flex items-center gap-2">
                                                <i class="fas fa-clock text-slate-400 text-xs"></i>
                                                Facturación
                                            </span>
                                            <span class="font-semibold">{{ $plan->periodo_facturacion }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="flex items-center gap-2">
                                                <i class="fas fa-tag text-slate-400 text-xs"></i>
                                                Precio
                                            </span>
                                            <span class="font-semibold">{{ number_format($plan->precio, 0, ',', '.') }} {{ $plan->moneda == 'BS' ? 'Bs' : '$' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <div class="mb-4 flex items-center justify-between gap-4">
                                    <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-list-check text-indigo-500"></i>
                                        Características incluidas
                                    </h4>
                                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $planCaracteristicas->count() }} características</span>
                                </div>

                                @if($planCaracteristicas->isNotEmpty())
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        @foreach($planCaracteristicas as $planCaracteristica)
                                            <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm hover:shadow-md transition-all duration-200 hover:border-indigo-200">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 flex-shrink-0">
                                                        <i class="fas fa-check text-sm"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="font-semibold text-gray-900">
                                                            {{ $planCaracteristica->caracteristica->nombre ?? 'Característica' }}
                                                        </p>
                                                        @if($planCaracteristica->cantidad || $planCaracteristica->frecuencia)
                                                            <p class="mt-1 text-sm text-gray-600">
                                                                @if($planCaracteristica->cantidad)
                                                                    {{ $planCaracteristica->cantidad }}
                                                                @endif
                                                                @if($planCaracteristica->cantidad && $planCaracteristica->frecuencia)
                                                                    ·
                                                                @endif
                                                                @if($planCaracteristica->frecuencia)
                                                                    {{ $planCaracteristica->frecuencia }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500">Este plan no tiene características registradas.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                @if($empresas->isEmpty())
                    <!-- Crea tu empresa -->
                    <div class="rounded-3xl bg-white shadow-xl border border-gray-100 p-8 hover:shadow-2xl transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl mb-4" style="background: #a7b838;">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Crea tu empresa</h2>
                        <p class="mt-3 text-gray-600 leading-7">
                            Registra tu empresa para comenzar a organizar tu información, conectar campañas y avanzar con tu estrategia digital.
                        </p>
                        <a href="{{ route('empresas.create') }}" class="mt-6 inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Crear empresa
                        </a>
                    </div>
                @else
                    <!-- Tu empresa -->
                    <div class="rounded-3xl bg-white shadow-xl border border-gray-100 p-8 hover:shadow-2xl transition-all duration-300">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl mb-4" style="background: #475569;">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Tu empresa</h2>
                        <p class="mt-2 text-gray-600">Ya tienes una empresa registrada en tu cuenta.</p>
                        <div class="mt-6 rounded-2xl p-5 border" style="background: #f8fafc; border-color: #e2e8f0;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #a7b838;">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Empresa registrada</p>
                                    <p class="mt-0.5 text-xl font-bold text-gray-900">{{ $empresas->first()->nombre_empresa }}</p>
                                    <p class="text-sm text-gray-600">{{ $empresas->first()->tipo_empresa }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('empresas.show', $empresas->first()->id) }}" class="mt-6 inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5" style="background: #475569;">
                            <i class="fas fa-eye mr-2"></i>
                            Ver empresa
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="plan-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity backdrop-blur-sm" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/50"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white" id="modal-plan-title">Detalles del Plan</h3>
                    <button type="button" id="close-modal" class="text-white/80 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="bg-white px-6 py-6">
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ciclo de facturación
                    </h4>
                    <p class="text-gray-700 font-medium" id="modal-plan-dates"></p>
                    <p class="text-sm mt-1" id="modal-plan-status"></p>
                </div>

                <div class="mb-6">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descripción
                    </h4>
                    <p class="text-gray-600 leading-relaxed" id="modal-plan-description"></p>
                </div>

                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Características incluidas
                    </h4>
                    <div class="space-y-2" id="modal-plan-features"></div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" id="close-modal-footer" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Banner geométrico */
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
        .grid.grid-cols-1.xl\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

@push('scripts')
<script>
    window.userHasCompanies = @json($empresas->isNotEmpty());
    window.socialModalClosed = localStorage.getItem('socialModalClosed') === 'true';
</script>
<script src="/js/dashboardcliente.js"></script>
@endpush

@endsection