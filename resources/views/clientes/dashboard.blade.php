@extends('layouts.app2')

@section('title', 'Dashboard del Cliente')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    $suscripcionActiva->loadMissing('plan.planCaracteristicas.caracteristica');
    $plan = $suscripcionActiva->plan;
    $planCaracteristicas = $plan?->planCaracteristicas ?? collect();
    $dashboardFacebookAccount = $dashboardSocialAccounts->get('facebook');
    $dashboardFacebookPage = $dashboardSocialAccounts->get('facebook_page');
    $dashboardInstagramAccount = $dashboardSocialAccounts->get('instagram');
    $dashboardFacebookLinked = filled($dashboardFacebookAccount?->provider_user_id) || filled($dashboardFacebookPage?->provider_user_id);
    $dashboardInstagramLinked = filled($dashboardInstagramAccount?->provider_user_id);
    $dashboardHasSocialAccounts = $dashboardFacebookLinked || $dashboardInstagramLinked;
    $dashboardFacebookName = $dashboardFacebookPage?->display_name
        ?? data_get($dashboardFacebookPage?->metadata, 'page_name')
        ?? $dashboardFacebookAccount?->display_name
        ?? $dashboardFacebookAccount?->username
        ?? 'Facebook';
    $dashboardInstagramName = $dashboardInstagramAccount?->display_name
        ?? $dashboardInstagramAccount?->username
        ?? 'Instagram';
    $dashboardFacebookIdentifier = $dashboardFacebookPage?->provider_user_id
        ?? $dashboardFacebookAccount?->provider_user_id
        ?? $dashboardFacebookAccount?->username;
    $dashboardInstagramUsername = ltrim((string) $dashboardInstagramAccount?->username, '@');
    $dashboardInstagramTooltip = filled($dashboardInstagramUsername)
        ? '@'.$dashboardInstagramUsername
        : $dashboardInstagramName;
    $dashboardFacebookUrl = filled($dashboardFacebookIdentifier)
        ? 'https://www.facebook.com/'.rawurlencode($dashboardFacebookIdentifier)
        : 'https://www.facebook.com/';
    $dashboardInstagramUrl = filled($dashboardInstagramUsername)
        ? 'https://www.instagram.com/'.rawurlencode($dashboardInstagramUsername).'/'
        : 'https://www.instagram.com/';
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
                @if($dashboardCompanies->count() > 1)
                    <details class="company-options">
                        <summary aria-label="Cambiar empresa" title="Cambiar empresa"><i class="fas fa-chevron-down"></i></summary>
                        <div class="company-options-menu">
                            <small>Cambiar empresa</small>
                            @foreach($dashboardCompanies as $dashboardCompany)
                                <a href="{{ route('clientes.dashboard', ['empresa' => $dashboardCompany->id]) }}" class="{{ (int) $empresaActiva->id === (int) $dashboardCompany->id ? 'is-current' : '' }}">
                                    <i class="fas {{ (int) $empresaActiva->id === (int) $dashboardCompany->id ? 'fa-circle-check' : 'fa-building' }}"></i>
                                    <span>{{ $dashboardCompany->nombre_empresa }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </article>
            <article><i class="fas fa-crown"></i><span><small>Plan contratado</small><strong>{{ $plan->nombre }}</strong></span></article>
            <article class="social-networks-metric">
                <i class="fas fa-share-nodes"></i>
                <div class="social-metric-content">
                    <small>Redes sociales</small>
                    @if($dashboardHasSocialAccounts)
                        <div class="social-metric-links">
                            @if($dashboardFacebookLinked)<a class="social-pill facebook" href="{{ $dashboardFacebookUrl }}" target="_blank" rel="noopener noreferrer" data-tooltip="{{ $dashboardFacebookName }}" aria-label="Abrir Facebook: {{ $dashboardFacebookName }}"><i class="fab fa-facebook-f"></i> Facebook</a>@endif
                            @if($dashboardInstagramLinked)<a class="social-pill instagram" href="{{ $dashboardInstagramUrl }}" target="_blank" rel="noopener noreferrer" data-tooltip="{{ $dashboardInstagramTooltip }}" aria-label="Abrir Instagram: {{ $dashboardInstagramTooltip }}"><i class="fab fa-instagram"></i> Instagram</a>@endif
                        </div>
                        <button type="button" id="open-dashboard-social" class="edit-social-links" aria-label="Editar redes vinculadas" title="Editar redes vinculadas"><i class="fas fa-pen"></i></button>
                    @else
                        <button type="button" id="open-dashboard-social" class="connect-social-links"><i class="fas fa-link"></i> Vincular cuentas</button>
                    @endif
                </div>
            </article>
            <article><i class="fas fa-calendar-check"></i><span><small>Tiempo disponible</small>@if($suscripcionActiva->vigencia_activada_at)<strong>{{ max(0, intval($diasRestantes)) }} días</strong>@else<strong class="time-pending-label">No definido hasta que comience la campaña</strong>@endif</span></article>
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

<div id="dashboard-social-modal" class="dashboard-social-modal hidden" role="dialog" aria-modal="true" aria-labelledby="dashboard-social-title">
    <div class="dashboard-social-backdrop" data-close-dashboard-social></div>
    <div class="dashboard-social-dialog">
        <header>
            <span><i class="fas fa-share-nodes"></i></span>
            <div><small>CANALES DE TU EMPRESA</small><h3 id="dashboard-social-title">Vincular redes sociales</h3></div>
            <button type="button" data-close-dashboard-social aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <div class="dashboard-social-body">
            <div class="dashboard-social-company"><i class="fas fa-building"></i> Configurarás las redes de <strong>{{ $empresaActiva->nombre_empresa }}</strong></div>
            @if(session('social_accounts_success'))<div class="dashboard-social-notice success"><i class="fas fa-circle-check"></i><span>{{ session('social_accounts_success') }}</span></div>@endif
            @if(session('social_accounts_error'))<div class="dashboard-social-notice error"><i class="fas fa-circle-exclamation"></i><span>{{ session('social_accounts_error') }}</span></div>@endif
            <p>Conecta Facebook y sincroniza automáticamente la cuenta profesional de Instagram asociada a la misma página.</p>
            <div class="dashboard-social-options">
                <a class="dashboard-social-option facebook {{ $dashboardFacebookLinked ? 'is-linked' : '' }}" href="{{ route('clientes.social.redirect', ['provider' => 'facebook', 'empresa_id' => $empresaActiva->id, 'return_to' => 'dashboard']) }}">
                    <div><span><i class="fab fa-facebook-f"></i></span><b>{{ $dashboardFacebookLinked ? 'Vinculado' : 'Disponible' }}</b></div>
                    <h4>Facebook</h4><p>Autoriza la página de esta empresa para conectarla con PRODOVI.</p>
                    @if($dashboardFacebookLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $dashboardFacebookName }}</strong></span></aside>@endif
                    <em>{{ $dashboardFacebookLinked ? 'Volver a conectar' : 'Conectar con Facebook' }} <i class="fas fa-arrow-right"></i></em>
                </a>
                <a class="dashboard-social-option instagram {{ $dashboardInstagramLinked ? 'is-linked' : '' }} {{ !$dashboardFacebookLinked ? 'is-disabled' : '' }}" href="{{ $dashboardFacebookLinked ? route('clientes.social.redirect', ['provider' => 'instagram', 'empresa_id' => $empresaActiva->id, 'return_to' => 'dashboard']) : '#' }}" aria-disabled="{{ $dashboardFacebookLinked ? 'false' : 'true' }}">
                    <div><span><i class="fab fa-instagram"></i></span><b>{{ $dashboardInstagramLinked ? 'Vinculado' : ($dashboardFacebookLinked ? 'Disponible' : 'Bloqueado') }}</b></div>
                    <h4>Instagram</h4><p>Sincroniza el perfil profesional asociado a la página de Facebook.</p>
                    @if($dashboardInstagramLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $dashboardInstagramName }}</strong></span></aside>@endif
                    <em>{{ $dashboardInstagramLinked ? 'Volver a sincronizar' : ($dashboardFacebookLinked ? 'Conectar con Instagram' : 'Primero conecta Facebook') }} @if($dashboardFacebookLinked)<i class="fas fa-arrow-right"></i>@endif</em>
                </a>
            </div>
        </div>
        <footer><button type="button" data-close-dashboard-social>Listo</button></footer>
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
        grid-template-columns: repeat(5,minmax(0,1fr));
        border-top: 1px solid #ded7e1;
        border-bottom: 1px solid #ded7e1;
        background: #fff;
    }
    #client-dashboard .dashboard-metrics article { min-width: 0; display: flex; align-items: center; gap: 13px; padding: 20px 22px; border-right: 1px solid #e7e1e9; }
    #client-dashboard .dashboard-metrics article:last-child { border-right: 0; }
    #client-dashboard .dashboard-metrics article > i { width: 39px; height: 39px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 3px; background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #client-dashboard .dashboard-metrics article:nth-child(2) > i { background: rgba(239,108,34,.11); color: var(--prodovi-orange); }
    #client-dashboard .dashboard-metrics article:nth-child(3) > i { background: rgba(17,126,140,.11); color: var(--prodovi-turquoise); }
    #client-dashboard .dashboard-metrics article:nth-child(4) > i { background: rgba(125,165,51,.13); color: var(--prodovi-green); }
    #client-dashboard .dashboard-metrics article:nth-child(5) > i { background: rgba(17,126,140,.11); color: var(--prodovi-turquoise); }
    #client-dashboard .dashboard-metrics span { min-width: 0; }
    #client-dashboard .dashboard-metrics small { display: block; color: #8a7f8e; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    #client-dashboard .dashboard-metrics strong { display: block; overflow: hidden; margin-top: 3px; color: #302834; font-size: .92rem; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
    #client-dashboard .dashboard-metrics .time-pending-label { overflow: visible; max-width: 190px; font-size: .72rem; line-height: 1.35; text-overflow: clip; white-space: normal; }
    #client-dashboard .social-metric-content { min-width:0; }
    #client-dashboard .social-metric-content > small { display:block; }
    #client-dashboard .social-metric-links { display:flex; flex-wrap:wrap; gap:5px; margin-top:5px; }
    #client-dashboard .social-pill { position:relative; display:inline-flex; align-items:center; gap:5px; padding:4px 6px; border-radius:2px; background:#edf7f8; color:#117e8c; font-size:.6rem; font-weight:900; text-decoration:none; }
    #client-dashboard .social-pill.facebook i { color:#1877f2; }
    #client-dashboard .social-pill.instagram i { color:#d62976; }
    #client-dashboard .social-pill::after { content:attr(data-tooltip); position:absolute; z-index:40; bottom:calc(100% + 8px); left:50%; width:max-content; max-width:210px; padding:7px 9px; border-radius:3px; background:#242426; color:#fff; font-size:.62rem; font-weight:700; line-height:1.25; text-align:center; opacity:0; pointer-events:none; transform:translate(-50%,4px); transition:.16s ease; }
    #client-dashboard .social-pill::before { content:''; position:absolute; z-index:41; bottom:calc(100% + 3px); left:50%; border:5px solid transparent; border-top-color:#242426; opacity:0; pointer-events:none; transform:translateX(-50%); transition:.16s ease; }
    #client-dashboard .social-pill:hover::after, #client-dashboard .social-pill:hover::before, #client-dashboard .social-pill:focus-visible::after, #client-dashboard .social-pill:focus-visible::before { opacity:1; transform:translate(-50%,0); }
    #client-dashboard .social-pill:hover { background:#dff0f2; }
    #client-dashboard .dashboard-metrics article.social-networks-metric { position:relative; padding-right:48px; }
    #client-dashboard .connect-social-links { display:inline-flex; align-items:center; gap:5px; margin-top:5px; padding:5px 7px; border:1px solid #117e8c; border-radius:3px; background:#117e8c; color:#fff; font-size:.61rem; font-weight:900; cursor:pointer; }
    #client-dashboard .edit-social-links { position:absolute; top:8px; right:8px; width:30px; height:30px; display:grid; place-items:center; border:0; border-radius:50%; background:transparent; color:#756a7a; cursor:pointer; transition:.18s ease; }
    #client-dashboard .edit-social-links:hover { background:rgba(17,126,140,.11); color:#117e8c; }
    #client-dashboard .active-company-metric { position: relative; padding-right: 50px; }
    #client-dashboard .company-options { position: absolute; z-index: 20; right: 8px; bottom: 7px; }
    #client-dashboard .company-options summary { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; color: #756a7a; cursor: pointer; list-style: none; }
    #client-dashboard .company-options summary::-webkit-details-marker { display: none; }
    #client-dashboard .company-options summary:hover { background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #client-dashboard .company-options[open] summary { background:rgba(91,43,118,.1); color:var(--prodovi-purple); }
    #client-dashboard .company-options[open] summary i { transform:rotate(180deg); }
    #client-dashboard .company-options summary i { transition:.18s ease; }
    #client-dashboard .company-options-menu { position: absolute; top: 34px; right: 0; width: 230px; padding: 6px; border: 1px solid #ded7e1; background: #fff; box-shadow: 0 12px 30px rgba(28,19,32,.18); }
    #client-dashboard .company-options-menu > small { padding:7px 10px 6px; color:#918696; font-size:.58rem; }
    #client-dashboard .company-options-menu a { display: flex; align-items: center; gap: 9px; min-width:0; padding: 10px; color: #514557; font-size: .78rem; font-weight: 700; text-decoration: none; }
    #client-dashboard .company-options-menu a span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    #client-dashboard .company-options-menu a:hover, #client-dashboard .company-options-menu a.is-current { background: #f4edf7; color: var(--prodovi-purple); }
    #client-dashboard .company-options-menu i { width: 16px; color: var(--prodovi-orange); }
    html[data-client-theme="dark"] #client-dashboard .company-options summary { color: #b4abb8; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu { border-color: #444047; background: #242426; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a { color: #ddd8df; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a:hover, html[data-client-theme="dark"] #client-dashboard .company-options-menu a.is-current { background: rgba(17,126,140,.16); color: #fff; }
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

    .dashboard-social-modal { position:fixed; z-index:2147483001; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .dashboard-social-modal.hidden { display:none; }
    .dashboard-social-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    .dashboard-social-dialog { position:relative; width:min(690px,100%); max-height:calc(100vh - 40px); display:flex; flex-direction:column; overflow:hidden; border-radius:5px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.38); }
    .dashboard-social-dialog > header { display:flex; align-items:center; gap:12px; padding:19px 22px; border-bottom:5px solid #117e8c; background:#242426; color:#fff; }
    .dashboard-social-dialog > header > span { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:3px; background:#117e8c; }
    .dashboard-social-dialog > header > div { flex:1; }.dashboard-social-dialog header small { display:block; color:#76c5ce; font-size:.6rem; font-weight:900; letter-spacing:.12em; }.dashboard-social-dialog header h3 { margin:3px 0 0; font-size:1.12rem; font-weight:900; }
    .dashboard-social-dialog > header button { width:36px; height:36px; border:1px solid #565259; border-radius:3px; background:#343436; color:#fff; cursor:pointer; }
    .dashboard-social-body { overflow-y:auto; padding:22px; }.dashboard-social-company { margin-bottom:14px; padding:11px 13px; border-left:4px solid #ee9f2b; background:#fff5e6; color:#70572f; font-size:.75rem; }.dashboard-social-company i { margin-right:7px; color:#ee9f2b; }.dashboard-social-body > p { margin:0 0 16px; color:#756a7a; font-size:.77rem; line-height:1.55; }
    .dashboard-social-notice { display:flex; gap:8px; margin-bottom:13px; padding:11px; border-left:4px solid; font-size:.72rem; font-weight:800; }.dashboard-social-notice.success { border-color:#7da533; background:#f3f7eb; color:#587923; }.dashboard-social-notice.error { border-color:#b63b3b; background:#fff1f1; color:#9b2929; }
    .dashboard-social-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:13px; }.dashboard-social-option { display:flex; flex-direction:column; padding:17px; border:1px solid #ded7e1; border-top:4px solid #1877f2; border-radius:4px; color:#302834; text-decoration:none; transition:.2s ease; }.dashboard-social-option.instagram { border-top-color:#d62976; }.dashboard-social-option:hover { transform:translateY(-2px); box-shadow:0 10px 22px #ded9e0; }
    .dashboard-social-option > div { display:flex; align-items:center; justify-content:space-between; }.dashboard-social-option > div > span { width:37px; height:37px; display:grid; place-items:center; border-radius:50%; background:#1877f2; color:#fff; }.dashboard-social-option.instagram > div > span { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }.dashboard-social-option > div > b { padding:5px 7px; background:#edf7f8; color:#117e8c; font-size:.57rem; text-transform:uppercase; }.dashboard-social-option h4 { margin:13px 0 0; font-size:.94rem; font-weight:900; }.dashboard-social-option > p { flex:1; margin:6px 0 13px; color:#756a7a; font-size:.69rem; line-height:1.5; }.dashboard-social-option > em { color:#5b2b76; font-size:.67rem; font-style:normal; font-weight:900; }
    .dashboard-social-option aside { display:flex; align-items:center; gap:8px; margin-bottom:13px; padding:9px; border:1px solid #cdddaf; background:#f7faf1; color:#587923; }.dashboard-social-option aside span,.dashboard-social-option aside small,.dashboard-social-option aside strong { display:block; }.dashboard-social-option aside small { font-size:.52rem; font-weight:900; text-transform:uppercase; }.dashboard-social-option aside strong { color:#35451b; font-size:.68rem; }.dashboard-social-option.is-linked { border-color:#7da533; border-top-color:#7da533; }.dashboard-social-option.is-disabled { background:#f4f2f5; opacity:.58; pointer-events:none; }
    .dashboard-social-dialog > footer { display:flex; justify-content:flex-end; padding:13px 22px; border-top:1px solid #ded7e1; background:#f7f5f8; }.dashboard-social-dialog > footer button { padding:9px 17px; border:0; border-radius:3px; background:#5b2b76; color:#fff; font-size:.73rem; font-weight:900; cursor:pointer; }
    html[data-client-theme="dark"] #client-dashboard .social-pill { background:#173136; color:#78c3cb; }
    html[data-client-theme="dark"] #client-dashboard .social-pill:hover { background:#21444a; }
    html[data-client-theme="dark"] #client-dashboard .edit-social-links { color:#b4abb8; }
    html[data-client-theme="dark"] .dashboard-social-dialog { background:#1e1b21; color:#f1edf3; } html[data-client-theme="dark"] .dashboard-social-company { background:#3a3020; color:#efcf9e; } html[data-client-theme="dark"] .dashboard-social-body > p { color:#b4abb8; } html[data-client-theme="dark"] .dashboard-social-option { border-color:#403943; background:#29252c; color:#f1edf3; } html[data-client-theme="dark"] .dashboard-social-option > p { color:#b4abb8; } html[data-client-theme="dark"] .dashboard-social-option.is-linked { border-color:#627f2f; } html[data-client-theme="dark"] .dashboard-social-option aside { border-color:#526b2b; background:#20291a; } html[data-client-theme="dark"] .dashboard-social-dialog > footer { border-color:#403943; background:#29252c; }

    @media (max-width: 1100px) and (min-width: 641px) {
        #client-dashboard .dashboard-metrics { grid-template-columns:repeat(3,minmax(0,1fr)); }
    }

    @media (max-width: 640px) {
        #client-dashboard .dashboard-shell > :not(.client-hero) { margin-right: 1rem; margin-left: 1rem; }
        #client-dashboard .client-hero { min-height: 205px; padding: 28px 20px; }
        #client-dashboard .login-mosaic { display: none; }
        #client-dashboard .dashboard-metrics { grid-template-columns: 1fr 1fr; }
        #client-dashboard .dashboard-metrics article:nth-child(even) { border-right: 0; }
        #client-dashboard .dashboard-metrics article:not(:last-child) { border-bottom: 1px solid #e7e1e9; }
        #client-dashboard .dashboard-metrics article { padding: 16px 13px; }
        .dashboard-social-options { grid-template-columns:1fr; }
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('dashboard-social-modal');
    const openButton = document.getElementById('open-dashboard-social');
    const closeButtons = modal?.querySelectorAll('[data-close-dashboard-social]') ?? [];

    function openSocialModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        modal.querySelector('header button')?.focus();
    }

    function closeSocialModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        openButton?.focus();
    }

    openButton?.addEventListener('click', openSocialModal);
    closeButtons.forEach(button => button.addEventListener('click', closeSocialModal));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeSocialModal();
    });

    @if(session('social_accounts_success') || session('social_accounts_error'))
        openSocialModal();
    @endif
});
</script>
@endpush

@endsection
