@extends('layouts.app2')

@section('title', 'Dashboard del Cliente')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    $suscripcionActiva->loadMissing('plan.planCaracteristicas.caracteristica');
    $plan = $suscripcionActiva->plan;
    $planCaracteristicas = $plan?->planCaracteristicas ?? collect();
@endphp

<div id="client-dashboard" class="min-h-screen">
    <div class="dashboard-shell space-y-8">
        
        <!-- Banner con fondo geométrico -->
        <div class="client-hero relative">
            <div class="hero-content">
                <span class="hero-kicker">Centro de operaciones</span>
                <h1>Hola, <span>{{ $user->name }}</span></h1>
                <p>Todo lo que PRODOVI está construyendo para tí, organizado en un solo lugar.</p>
            </div>
            <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>

        <section class="dashboard-metrics" aria-label="Resumen de tu servicio">
            <article class="active-company-metric">
                <i class="fas fa-building"></i>
                <span><small>Marca activa</small><strong>{{ $empresaActiva?->nombre_empresa ?? 'Por registrar' }}</strong></span>
                <details class="company-options">
                    <summary aria-label="Opciones de la empresa"><i class="fas fa-ellipsis-vertical"></i></summary>
                    <div class="company-options-menu">
                        @if($empresaActiva)
                            <a href="{{ route('empresas.show', $empresaActiva->id) }}"><i class="fas fa-eye"></i> Ver empresa</a>
                        @endif
                        <a href="{{ route('clientes.planes.comprar') }}"><i class="fas fa-plus"></i> Agregar otra empresa</a>
                    </div>
                </details>
            </article>
            <article><i class="fas fa-crown"></i><span><small>Plan contratado</small><strong>{{ $plan->nombre }}</strong></span></article>
            <article><i class="fas fa-calendar-check"></i><span><small>Tiempo disponible</small><strong>{{ max(0, intval($diasRestantes)) }} días</strong></span></article>
            <article><i class="fas fa-signal"></i><span><small>Estado del servicio</small><strong class="capitalize">{{ $suscripcionActiva->estado }}</strong></span></article>
        </section>

        <section class="campaign-progress" aria-labelledby="campaign-progress-title">
            <div class="campaign-progress-icon" aria-hidden="true">
                <i class="fas fa-gears"></i>
            </div>
            <h2 id="campaign-progress-title">Estamos trabajando en tu campaña mensual</h2>
            <p>Estamos preparando tu plan. Cuando esté listo, recibirás una notificación por correo electrónico.</p>
        </section>

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

    #client-dashboard {
        --prodovi-purple: #5B2B76;
        --prodovi-purple-dark: #3d174f;
        --prodovi-orange: #EF6C22;
        --prodovi-gold: #F5A900;
        --prodovi-green: #7DA533;
        --prodovi-turquoise: #117E8C;
        background: #fff;
        color: #17131d;
    }
    #client-dashboard .dashboard-shell { width: 100%; padding-bottom: 40px; }
    #client-dashboard .dashboard-shell > :not(.client-hero) { margin-right: 2rem; margin-left: 2rem; }
    #client-dashboard .client-hero {
        position: relative;
        min-height: 178px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 36px;
        padding: 32px 38px;
        overflow: hidden;
        border-bottom: 0;
        background:
            linear-gradient(118deg, #4a205d 0%, #68317d 46%, #285b70 100%);
        color: #fff;
        isolation: isolate;
    }
    #client-dashboard .client-hero::before {
        content: '';
        position: absolute;
        z-index: 0;
        width: 330px;
        height: 330px;
        top: -230px;
        left: 34%;
        border: 54px solid rgba(255,255,255,.055);
        border-radius: 50%;
    }
    #client-dashboard .client-hero::after {
        content: '';
        position: absolute;
        z-index: 0;
        right: 8%;
        bottom: -125px;
        width: 250px;
        height: 250px;
        border-radius: 50%;
        background: radial-gradient(circle,rgba(245,169,0,.2),rgba(239,108,34,.06) 48%,transparent 70%);
    }
    #client-dashboard .hero-content { position: relative; z-index: 2; max-width: 760px; }
    #client-dashboard .hero-kicker { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 13px; color: rgba(255,255,255,.76); font-size: .68rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
    #client-dashboard .hero-kicker::before { content: ''; width: 24px; height: 3px; background: #64d3d5; }
    #client-dashboard .client-hero h1 { margin: 0; color: #fff; font-size: clamp(1.75rem,3.4vw,2.65rem); font-weight: 900; line-height: 1.05; letter-spacing: -.04em; }
    #client-dashboard .client-hero h1 span { color: #ffc14c; }
    #client-dashboard .client-hero p { max-width: 650px; margin-top: 12px; color: rgba(255,255,255,.72); font-size: .9rem; line-height: 1.6; }
    #client-dashboard .login-mosaic { position: relative; z-index: 2; width: 156px; height: 104px; flex: 0 0 auto; display: grid; grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(2,1fr); }
    #client-dashboard .login-mosaic span:nth-child(1) { background: var(--prodovi-orange); border-radius: 100% 0 0 0; }
    #client-dashboard .login-mosaic span:nth-child(2) { background: var(--prodovi-gold); border-radius: 0 0 0 100%; }
    #client-dashboard .login-mosaic span:nth-child(3) { background: var(--prodovi-purple); border-radius: 100% 0 100% 0; }
    #client-dashboard .login-mosaic span:nth-child(4) { background: var(--prodovi-turquoise); border-radius: 0 100% 0 100%; }
    #client-dashboard .login-mosaic span:nth-child(5) { background: var(--prodovi-green); border-radius: 50%; }
    #client-dashboard .login-mosaic span:nth-child(6) { border: 12px solid #607078; border-top-color: transparent; border-left-color: transparent; border-radius: 50%; transform: rotate(45deg); }
    #client-dashboard .dashboard-metrics {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
        border-top: 1px solid #ded7e1;
        border-bottom: 1px solid #ded7e1;
        background: #fff;
    }
    #client-dashboard .dashboard-metrics article { min-width: 0; display: flex; align-items: center; gap: 13px; padding: 20px 22px; border-right: 1px solid #e7e1e9; }
    #client-dashboard .dashboard-metrics article:last-child { border-right: 0; }
    #client-dashboard .dashboard-metrics article > i { width: 39px; height: 39px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 3px; background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #client-dashboard .dashboard-metrics article:nth-child(2) > i { background: rgba(239,108,34,.11); color: var(--prodovi-orange); }
    #client-dashboard .dashboard-metrics article:nth-child(3) > i { background: rgba(125,165,51,.13); color: var(--prodovi-green); }
    #client-dashboard .dashboard-metrics article:nth-child(4) > i { background: rgba(17,126,140,.11); color: var(--prodovi-turquoise); }
    #client-dashboard .dashboard-metrics span { min-width: 0; }
    #client-dashboard .dashboard-metrics small { display: block; color: #8a7f8e; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    #client-dashboard .dashboard-metrics strong { display: block; overflow: hidden; margin-top: 3px; color: #302834; font-size: .92rem; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
    #client-dashboard .active-company-metric { position: relative; padding-right: 50px; }
    #client-dashboard .company-options { position: absolute; z-index: 20; top: 8px; right: 8px; }
    #client-dashboard .company-options summary { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; color: #756a7a; cursor: pointer; list-style: none; }
    #client-dashboard .company-options summary::-webkit-details-marker { display: none; }
    #client-dashboard .company-options summary:hover { background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #client-dashboard .company-options-menu { position: absolute; top: 34px; right: 0; width: 205px; padding: 6px; border: 1px solid #ded7e1; background: #fff; box-shadow: 0 12px 30px rgba(28,19,32,.18); }
    #client-dashboard .company-options-menu a { display: flex; align-items: center; gap: 9px; padding: 10px; color: #514557; font-size: .78rem; font-weight: 700; text-decoration: none; }
    #client-dashboard .company-options-menu a:hover { background: #f4edf7; color: var(--prodovi-purple); }
    #client-dashboard .company-options-menu i { width: 16px; color: var(--prodovi-orange); }
    html[data-client-theme="dark"] #client-dashboard .company-options summary { color: #b4abb8; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu { border-color: #444047; background: #242426; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a { color: #ddd8df; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a:hover { background: rgba(17,126,140,.16); color: #fff; }
    #client-dashboard .campaign-progress { padding: 42px 24px; background: transparent; text-align: center; }
    #client-dashboard .campaign-progress-icon { width: 68px; height: 68px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 50%; background: rgba(91,43,118,.1); color: var(--prodovi-purple); font-size: 1.7rem; }
    #client-dashboard .campaign-progress h2 { margin: 0; color: #302834; font-size: 1.25rem; font-weight: 900; letter-spacing: -.025em; }
    #client-dashboard .campaign-progress p { max-width: 580px; margin: 9px auto 0; color: #756a7a; font-size: .9rem; line-height: 1.65; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress-icon { background: rgba(201,148,229,.12); color: #c994e5; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress h2 { color: #f1edf3; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress p { color: #b4abb8; }
    #client-dashboard .dashboard-grid { align-items: start; }
    #client-dashboard .service-panel,
    #client-dashboard .company-panel {
        border: 0;
        border-top: 1px solid #d9d2dc;
        border-bottom: 1px solid #d9d2dc;
        border-radius: 0;
        box-shadow: none;
    }
    #client-dashboard .service-panel { border-left: 4px solid var(--prodovi-purple); }
    #client-dashboard .section-heading { border-color: #ded7e1; background: #f7f5f8; }
    #client-dashboard .section-icon { border-radius: 3px; background: var(--prodovi-purple) !important; }
    #client-dashboard .section-heading h2 { letter-spacing: -.025em; }
    #client-dashboard .plan-overview { border: 0; border-radius: 0; background: #fff; }
    #client-dashboard .plan-badge { border: 1px solid rgba(17,126,140,.22); border-radius: 2px; background: rgba(17,126,140,.08); color: var(--prodovi-turquoise); }
    #client-dashboard .plan-status { position: relative; overflow: hidden; border-radius: 3px; background: #242426 !important; box-shadow: none; }
    #client-dashboard .plan-status::before { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 5px; background: var(--prodovi-orange); }
    #client-dashboard .service-progress { margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.12); }
    #client-dashboard .service-progress > div { display: flex; justify-content: space-between; gap: 12px; color: #c2bdc5; font-size: .72rem; }
    #client-dashboard .service-progress > div strong { color: #fff; }
    #client-dashboard .service-progress-track { height: 5px; display: block; margin-top: 8px; overflow: hidden; background: #454248; }
    #client-dashboard .service-progress-track i { height: 100%; display: block; background: linear-gradient(90deg,var(--prodovi-turquoise),var(--prodovi-green)); }
    #client-dashboard .feature-count { border-radius: 2px; background: #f1edf2; color: #756a7a; }
    #client-dashboard .feature-item { border-width: 0 0 1px 3px; border-color: #e5dfe7 #e5dfe7 #e5dfe7 var(--prodovi-turquoise); border-radius: 0; background: #fff; box-shadow: none; }
    #client-dashboard .feature-item:hover { border-left-color: var(--prodovi-orange); background: #faf8fb; transform: translateX(2px); }
    #client-dashboard .feature-check { border-radius: 2px; background: rgba(125,165,51,.13); color: var(--prodovi-green); }
    #client-dashboard .company-panel { position: sticky; top: 24px; border-top: 5px solid var(--prodovi-orange); }
    #client-dashboard .company-panel > div:first-child { border-radius: 3px !important; background: var(--prodovi-purple) !important; }
    #client-dashboard .company-panel h2 { letter-spacing: -.03em; }
    #client-dashboard .company-summary { border-width: 1px 0; border-color: #ded7e1 !important; border-radius: 0; background: #faf8fb !important; }
    #client-dashboard .company-summary .w-10 { border-radius: 2px !important; background: var(--prodovi-green) !important; }
    #client-dashboard .company-cta { width: 100%; border-radius: 3px; background: linear-gradient(135deg,var(--prodovi-purple),var(--prodovi-purple-dark)) !important; box-shadow: none; }
    #client-dashboard .company-cta:hover { background: var(--prodovi-orange) !important; }
    #plan-modal { z-index: 1000; }
    #plan-modal .inline-block { overflow: hidden; border: 1px solid #d9d2dc; border-radius: 3px; box-shadow: 0 28px 80px rgba(0,0,0,.3); }
    #plan-modal .bg-gradient-to-r { background: #242426 !important; }
    #plan-modal .bg-gray-50.rounded-xl { border-width: 0 0 0 4px; border-radius: 0; border-left-color: #117E8C; background: #f7f5f8; }
    #plan-modal #modal-plan-features > * { border-radius: 2px !important; box-shadow: none !important; }
    #plan-modal #close-modal-footer { border-radius: 3px; background: #5B2B76 !important; box-shadow: none; }

    @media (max-width: 640px) {
        #client-dashboard .dashboard-shell > :not(.client-hero) { margin-right: 1rem; margin-left: 1rem; }
        #client-dashboard .client-hero { min-height: 205px; padding: 28px 20px; }
        #client-dashboard .login-mosaic { display: none; }
        #client-dashboard .dashboard-metrics { grid-template-columns: 1fr 1fr; }
        #client-dashboard .dashboard-metrics article:nth-child(2) { border-right: 0; }
        #client-dashboard .dashboard-metrics article:nth-child(-n+2) { border-bottom: 1px solid #e7e1e9; }
        #client-dashboard .dashboard-metrics article { padding: 16px 13px; }
        #client-dashboard .company-panel { position: static; }
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

@include('clientes.cuestionarioPopup')

@push('scripts')
<script>
    window.userHasCompanies = @json((bool) $empresaActiva);
    window.socialModalClosed = localStorage.getItem('socialModalClosed') === 'true';
</script>
<script src="/js/dashboardcliente.js"></script>
@endpush

@endsection
