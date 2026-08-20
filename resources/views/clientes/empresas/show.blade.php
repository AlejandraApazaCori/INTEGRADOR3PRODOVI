@extends('layouts.app2')

@section('title', 'Detalles de la Empresa')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    $facebookAccount = $companySocialAccounts->get('facebook') ?? $legacySocialAccounts->get('facebook');
    $facebookPage = $companySocialAccounts->get('facebook_page') ?? $legacySocialAccounts->get('facebook_page');
    $instagramAccount = $companySocialAccounts->get('instagram') ?? $legacySocialAccounts->get('instagram');
    $facebookLinked = filled($facebookAccount?->provider_user_id) || filled($facebookPage?->provider_user_id);
    $instagramLinked = filled($instagramAccount?->provider_user_id);
    $facebookName = $facebookPage?->display_name
        ?? data_get($facebookPage?->metadata, 'page_name')
        ?? $facebookAccount?->display_name
        ?? $facebookAccount?->username
        ?? $facebookAccount?->provider_user_id;
    $instagramName = $instagramAccount?->display_name
        ?? $instagramAccount?->username
        ?? $instagramAccount?->provider_user_id;
    $instagramUsername = filled($instagramAccount?->username)
        ? '@'.ltrim($instagramAccount->username, '@')
        : null;
@endphp

<div id="company-detail" class="min-h-screen">
    <header class="company-hero">
        <div class="company-hero-content">
            <span class="company-kicker">Información de tu marca</span>
            <h1>Mi <span>empresa</span></h1>
            <p>Consulta la información registrada y administra los datos necesarios para preparar tu estrategia.</p>
        </div>
        <div class="company-hero-side">
            <div class="company-hero-status"><small>Cuestionario</small><strong>{{ $empresa->cuestionario_completado ? 'Completado' : 'Pendiente' }}</strong></div>
            <div class="company-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>
    </header>

    <main class="company-content">
        @if(session('success'))
            <div class="company-alert"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>
        @endif

        <div class="company-back-row">
            <a href="{{ route('clientes.micuenta') }}" class="company-back-top"><i class="fas fa-arrow-left"></i><span>Volver a Mi cuenta</span></a>
        </div>

        <div class="company-layout">
            <section class="company-panel">
                <div class="company-panel-heading">
                    <span><i class="fas fa-building"></i></span>
                    <div><h2>Información de la empresa</h2><p>Datos principales de la marca registrada</p></div>
                </div>

                <div class="company-profile">
                    <div class="company-identity">
                        @if($empresa->logo)
                            <div class="company-logo"><img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}"></div>
                        @else
                            <div class="company-logo company-logo-placeholder">{{ mb_strtoupper(mb_substr($empresa->nombre_empresa, 0, 1)) }}</div>
                        @endif
                        <div>
                            <span class="company-type">{{ $empresa->tipo_empresa }}</span>
                            <h3>{{ $empresa->nombre_empresa }}</h3>
                            <p>Registrada el {{ $empresa->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="company-data-grid">
                        <article class="company-data-item"><i class="fas fa-layer-group"></i><div><small>Tipo de empresa</small><strong>{{ $empresa->tipo_empresa }}</strong></div></article>
                        <article class="company-data-item"><i class="fas fa-location-dot"></i><div><small>Dirección</small><strong>{{ $empresa->direccion ?: 'No registrada' }}</strong></div></article>
                    </div>

                    <div class="company-description">
                        <span>Descripción</span>
                        <p>{{ $empresa->descripcion ?: 'No se ha proporcionado una descripción para esta empresa.' }}</p>
                    </div>

                    <section class="questionnaire-state {{ $empresa->cuestionario_completado ? 'is-complete' : 'is-pending' }}">
                        <div class="questionnaire-icon"><i class="fas {{ $empresa->cuestionario_completado ? 'fa-circle-check' : 'fa-clock' }}"></i></div>
                        <div>
                            <span>ESTADO DEL CUESTIONARIO</span>
                            <h4>{{ $empresa->cuestionario_completado ? 'Cuestionario completado' : 'Cuestionario pendiente' }}</h4>
                            <p>
                                @if($empresa->cuestionario_completado)
                                    Gracias por proporcionar toda la información necesaria. Un administrador generará tu resumen ejecutivo pronto.
                                @else
                                    Completa el cuestionario para que podamos conocer tu empresa y comenzar a trabajar en tu estrategia.
                                @endif
                            </p>
                        </div>
                    </section>
                </div>
            </section>

            <aside class="company-actions-panel">
                <div class="company-actions-heading"><i class="fas fa-sliders"></i><div><h2>Acciones</h2><p>Gestiona esta empresa</p></div></div>
                <nav class="company-actions">
                    <a href="{{ route('empresas.edit', $empresa->id) }}"><i class="fas fa-pen"></i><span><strong>Editar empresa</strong><small>Actualiza sus datos</small></span><i class="fas fa-chevron-right"></i></a>
                    <a href="{{ route('empresas.cuestionario', $empresa->id) }}" class="company-action-primary">
                        <i class="fas {{ $empresa->cuestionario_completado ? 'fa-file-lines' : 'fa-list-check' }}"></i>
                        <span><strong>{{ $empresa->cuestionario_completado ? 'Ver cuestionario' : 'Completar cuestionario' }}</strong><small>{{ $empresa->cuestionario_completado ? 'Consulta tus respuestas' : 'Proporciona los datos pendientes' }}</small></span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>

                <section class="company-social-section">
                    <div class="company-social-title"><i class="fas fa-share-nodes"></i><div><h3>Redes sociales</h3><p>Canales vinculados a esta empresa</p></div></div>
                    <button type="button" id="open-company-social"><i class="fas fa-link"></i> Conectar redes</button>

                    <div class="company-social-connected">
                        @if($facebookLinked)
                            <article class="facebook"><span><i class="fab fa-facebook-f"></i></span><div><small>Facebook conectado</small><strong>{{ $facebookName ?: 'Cuenta de Facebook' }}</strong><em>{{ $facebookPage ? 'Página autorizada' : 'Perfil autorizado' }}</em></div><i class="fas fa-circle-check"></i></article>
                        @endif
                        @if($instagramLinked)
                            <article class="instagram"><span><i class="fab fa-instagram"></i></span><div><small>Instagram conectado</small><strong>{{ $instagramName ?: 'Cuenta de Instagram' }}</strong>@if($instagramUsername)<em>{{ $instagramUsername }}</em>@endif</div><i class="fas fa-circle-check"></i></article>
                        @endif
                        @unless($facebookLinked || $instagramLinked)
                            <p class="company-social-empty"><i class="fas fa-circle-info"></i> Aún no hay redes conectadas.</p>
                        @endunless
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>

<div id="company-social-modal" class="company-social-modal hidden" role="dialog" aria-modal="true" aria-labelledby="company-social-modal-title">
    <div class="company-social-backdrop" data-close-company-social></div>
    <div class="company-social-dialog">
        <header><span><i class="fas fa-share-nodes"></i></span><div><small>CANALES DE TU EMPRESA</small><h3 id="company-social-modal-title">Conectar redes sociales</h3></div><button type="button" data-close-company-social aria-label="Cerrar"><i class="fas fa-xmark"></i></button></header>
        <div class="company-social-modal-body">
            <div class="company-social-company"><i class="fas fa-building"></i> Configurarás las redes de <strong>{{ $empresa->nombre_empresa }}</strong></div>
            @if(session('social_accounts_success'))<div class="company-social-notice success"><i class="fas fa-circle-check"></i><span>{{ session('social_accounts_success') }}</span></div>@endif
            @if(session('social_accounts_error'))<div class="company-social-notice error"><i class="fas fa-circle-exclamation"></i><span>{{ session('social_accounts_error') }}</span></div>@endif
            <p class="company-social-intro">Conecta Facebook y sincroniza automáticamente la cuenta profesional de Instagram asociada a la misma página.</p>
            <div class="company-social-options">
                <a class="company-social-option facebook {{ $facebookLinked ? 'is-linked' : '' }}" href="{{ route('clientes.social.redirect', ['provider' => 'facebook', 'empresa_id' => $empresa->id, 'return_to' => 'empresa']) }}">
                    <div><span><i class="fab fa-facebook-f"></i></span><b>{{ $facebookLinked ? 'Vinculado' : 'Disponible' }}</b></div><h4>Facebook</h4><p>Autoriza la página de esta empresa para conectarla con PRODOVI.</p>
                    @if($facebookLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $facebookName ?: 'Cuenta de Facebook' }}</strong></span></aside>@endif
                    <em>{{ $facebookLinked ? 'Volver a conectar' : 'Conectar con Facebook' }} <i class="fas fa-arrow-right"></i></em>
                </a>
                <a class="company-social-option instagram {{ $instagramLinked ? 'is-linked' : '' }} {{ !$facebookLinked ? 'is-disabled' : '' }}" href="{{ $facebookLinked ? route('clientes.social.redirect', ['provider' => 'instagram', 'empresa_id' => $empresa->id, 'return_to' => 'empresa']) : '#' }}" aria-disabled="{{ $facebookLinked ? 'false' : 'true' }}">
                    <div><span><i class="fab fa-instagram"></i></span><b>{{ $instagramLinked ? 'Vinculado' : ($facebookLinked ? 'Disponible' : 'Bloqueado') }}</b></div><h4>Instagram</h4><p>Sincroniza el perfil profesional asociado a la página de Facebook.</p>
                    @if($instagramLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $instagramName ?: 'Cuenta de Instagram' }}</strong></span></aside>@endif
                    <em>{{ $instagramLinked ? 'Volver a sincronizar' : ($facebookLinked ? 'Conectar con Instagram' : 'Primero conecta Facebook') }} @if($facebookLinked)<i class="fas fa-arrow-right"></i>@endif</em>
                </a>
            </div>
        </div>
        <footer><button type="button" data-close-company-social>Listo</button></footer>
    </div>
</div>

<style>
    #company-detail { --purple:#5b2b76; --orange:#ee9f2b; --turquoise:#117e8c; --green:#7da533; background:#fff; color:#302834; }
    #company-detail .company-hero { min-height:150px; display:flex; align-items:center; justify-content:space-between; gap:32px; padding:28px 32px; background:#242426; color:#fff; }
    #company-detail .company-hero-content { max-width:720px; }
    #company-detail .company-kicker { display:block; margin-bottom:10px; color:var(--turquoise); font-size:.68rem; font-weight:900; letter-spacing:.13em; text-transform:uppercase; }
    #company-detail .company-hero h1 { margin:0; font-size:clamp(1.65rem,3vw,2.35rem); font-weight:800; line-height:1.08; letter-spacing:-.035em; }
    #company-detail .company-hero h1 span { color:var(--turquoise); }
    #company-detail .company-hero p { margin-top:11px; color:#aaa5ad; font-size:.86rem; line-height:1.55; }
    #company-detail .company-hero-side { display:flex; align-items:center; gap:26px; }
    #company-detail .company-hero-status { min-width:132px; padding:13px 16px; border-left:4px solid var(--turquoise); background:#303033; }
    #company-detail .company-hero-status small, #company-detail .company-hero-status strong { display:block; }
    #company-detail .company-hero-status small { color:#aaa5ad; font-size:.65rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    #company-detail .company-hero-status strong { margin-top:4px; color:#fff; font-size:.9rem; }
    #company-detail .company-mosaic { width:144px; height:96px; display:grid; flex:0 0 auto; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(2,1fr); }
    #company-detail .company-mosaic span:nth-child(1) { background:#ef6c22; border-radius:100% 0 0 0; }
    #company-detail .company-mosaic span:nth-child(2) { background:#f5a900; border-radius:0 0 0 100%; }
    #company-detail .company-mosaic span:nth-child(3) { background:var(--purple); border-radius:100% 0 100% 0; }
    #company-detail .company-mosaic span:nth-child(4) { background:var(--turquoise); border-radius:0 100% 0 100%; }
    #company-detail .company-mosaic span:nth-child(5) { background:var(--green); border-radius:50%; }
    #company-detail .company-mosaic span:nth-child(6) { border:12px solid #607078; border-top-color:transparent; border-left-color:transparent; border-radius:50%; transform:rotate(45deg); }
    #company-detail .company-content { margin:32px; }
    #company-detail .company-alert { display:flex; align-items:center; gap:9px; margin:0 auto 18px; max-width:1280px; padding:13px 16px; border-left:4px solid var(--green); background:#f3f7eb; color:#587923; font-size:.8rem; font-weight:800; }
    #company-detail .company-back-row { max-width:1280px; margin:0 auto 12px; }
    #company-detail .company-back-top { display:inline-flex; align-items:center; gap:8px; padding:9px 13px; border:1px solid #ded7e1; border-radius:4px; background:#fff; color:#5b2b76; font-size:.72rem; font-weight:900; text-decoration:none; transition:.18s ease; }
    #company-detail .company-back-top:hover { border-color:var(--purple); background:#f7f5f8; transform:translateX(-2px); }
    #company-detail .company-layout { display:grid; grid-template-columns:minmax(0,1fr) 310px; align-items:start; gap:24px; max-width:1280px; margin:0 auto; }
    #company-detail .company-panel, #company-detail .company-actions-panel { overflow:hidden; border:1px solid #ded7e1; border-radius:5px; background:#fff; box-shadow:0 10px 28px #ded9e0; }
    #company-detail .company-panel-heading, #company-detail .company-actions-heading { display:flex; align-items:center; gap:12px; padding:17px 20px; border-bottom:1px solid #ded7e1; border-left:4px solid var(--turquoise); background:#f7f5f8; }
    #company-detail .company-panel-heading > span, #company-detail .company-actions-heading > i { width:38px; height:38px; display:grid; place-items:center; flex:0 0 auto; border-radius:3px; background:var(--turquoise); color:#fff; }
    #company-detail .company-panel-heading h2, #company-detail .company-actions-heading h2 { margin:0; color:#302834; font-size:1rem; font-weight:900; }
    #company-detail .company-panel-heading p, #company-detail .company-actions-heading p { margin:2px 0 0; color:#887d8c; font-size:.73rem; }
    #company-detail .company-profile { padding:26px; }
    #company-detail .company-identity { display:flex; align-items:center; gap:18px; padding-bottom:24px; border-bottom:1px solid #ebe6ed; }
    #company-detail .company-logo { width:82px; height:82px; display:grid; place-items:center; flex:0 0 auto; overflow:hidden; border:1px solid #ded7e1; border-radius:5px; background:#fff; }
    #company-detail .company-logo img { width:100%; height:100%; padding:8px; object-fit:contain; }
    #company-detail .company-logo-placeholder { border:0; background:var(--purple); color:#fff; font-size:2rem; font-weight:900; }
    #company-detail .company-type { color:var(--turquoise); font-size:.65rem; font-weight:900; letter-spacing:.09em; text-transform:uppercase; }
    #company-detail .company-identity h3 { margin:4px 0; color:#302834; font-size:1.55rem; font-weight:900; }
    #company-detail .company-identity p { margin:0; color:#887d8c; font-size:.73rem; }
    #company-detail .company-data-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin:22px 0; }
    #company-detail .company-data-item { display:flex; align-items:center; gap:12px; padding:15px; border:1px solid #e4dee6; border-radius:4px; }
    #company-detail .company-data-item > i { width:34px; color:var(--turquoise); font-size:1rem; text-align:center; }
    #company-detail .company-data-item small, #company-detail .company-data-item strong { display:block; }
    #company-detail .company-data-item small { margin-bottom:3px; color:#887d8c; font-size:.65rem; font-weight:800; text-transform:uppercase; }
    #company-detail .company-data-item strong { color:#413745; font-size:.8rem; }
    #company-detail .company-description { padding:18px 0 22px; }
    #company-detail .company-description > span { display:block; margin-bottom:8px; color:#665b6b; font-size:.68rem; font-weight:900; letter-spacing:.07em; text-transform:uppercase; }
    #company-detail .company-description p { margin:0; color:#6f6573; font-size:.84rem; line-height:1.7; }
    #company-detail .questionnaire-state { display:flex; align-items:flex-start; gap:14px; padding:18px; border-left:5px solid; background:#f7f7f7; }
    #company-detail .questionnaire-state.is-complete { border-color:var(--green); background:#f3f7eb; }
    #company-detail .questionnaire-state.is-pending { border-color:var(--orange); background:#fff7ea; }
    #company-detail .questionnaire-icon { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:50%; }
    #company-detail .is-complete .questionnaire-icon { background:var(--green); color:#fff; }
    #company-detail .is-pending .questionnaire-icon { background:var(--orange); color:#242426; }
    #company-detail .questionnaire-state span { color:#817585; font-size:.62rem; font-weight:900; letter-spacing:.09em; }
    #company-detail .questionnaire-state h4 { margin:3px 0 5px; color:#302834; font-size:.9rem; font-weight:900; }
    #company-detail .questionnaire-state p { margin:0; color:#6f6573; font-size:.76rem; line-height:1.55; }
    #company-detail .company-actions-panel { position:sticky; top:24px; }
    #company-detail .company-actions-heading { border-left-color:var(--purple); }
    #company-detail .company-actions-heading > i { background:var(--purple); }
    #company-detail .company-actions { display:grid; gap:10px; padding:18px; }
    #company-detail .company-actions a { display:grid; grid-template-columns:34px minmax(0,1fr) auto; align-items:center; gap:10px; padding:12px; border:1px solid #ded7e1; border-radius:4px; color:#514557; text-decoration:none; transition:.18s ease; }
    #company-detail .company-actions a > i:first-child { color:var(--purple); text-align:center; }
    #company-detail .company-actions a strong, #company-detail .company-actions a small { display:block; }
    #company-detail .company-actions a strong { font-size:.76rem; font-weight:900; }
    #company-detail .company-actions a small { margin-top:2px; color:#918696; font-size:.65rem; }
    #company-detail .company-actions a > i:last-child { color:#a59ca8; font-size:.65rem; }
    #company-detail .company-actions a:hover { border-color:var(--turquoise); transform:translateX(2px); }
    #company-detail .company-actions .company-action-primary { border-color:var(--purple); background:var(--purple); color:#fff; }
    #company-detail .company-actions .company-action-primary > i, #company-detail .company-actions .company-action-primary small { color:#fff; }
    #company-detail .company-social-section { padding:18px; border-top:1px solid #ded7e1; }
    #company-detail .company-social-title { display:flex; align-items:center; gap:10px; margin-bottom:13px; }
    #company-detail .company-social-title > i { width:34px; height:34px; display:grid; place-items:center; border-radius:3px; background:var(--turquoise); color:#fff; }
    #company-detail .company-social-title h3 { margin:0; color:#302834; font-size:.88rem; font-weight:900; }
    #company-detail .company-social-title p { margin:2px 0 0; color:#918696; font-size:.64rem; }
    #company-detail #open-company-social { width:100%; display:flex; align-items:center; justify-content:center; gap:8px; padding:11px; border:1px solid var(--turquoise); border-radius:4px; background:var(--turquoise); color:#fff; font-size:.73rem; font-weight:900; cursor:pointer; transition:.18s ease; }
    #company-detail #open-company-social:hover { background:#0d6671; transform:translateY(-1px); }
    #company-detail .company-social-connected { display:grid; gap:8px; margin-top:12px; }
    #company-detail .company-social-connected article { display:grid; grid-template-columns:34px minmax(0,1fr) auto; align-items:center; gap:9px; padding:10px; border:1px solid #d9e8c0; border-radius:4px; background:#f7faf1; }
    #company-detail .company-social-connected article > span { width:32px; height:32px; display:grid; place-items:center; border-radius:50%; background:#1877f2; color:#fff; }
    #company-detail .company-social-connected article.instagram > span { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }
    #company-detail .company-social-connected small, #company-detail .company-social-connected strong, #company-detail .company-social-connected em { display:block; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    #company-detail .company-social-connected small { color:#6c7e4c; font-size:.53rem; font-weight:900; text-transform:uppercase; }
    #company-detail .company-social-connected strong { color:#35451b; font-size:.68rem; }
    #company-detail .company-social-connected em { color:#778064; font-size:.56rem; font-style:normal; }
    #company-detail .company-social-connected article > i { color:var(--green); font-size:.75rem; }
    #company-detail .company-social-empty { margin:0; padding:10px; background:#f3f1f4; color:#817585; font-size:.66rem; text-align:center; }

    .company-social-modal { position:fixed; z-index:2147483001; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .company-social-modal.hidden { display:none; }
    .company-social-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    .company-social-dialog { position:relative; width:min(690px,100%); max-height:calc(100vh - 40px); display:flex; flex-direction:column; overflow:hidden; border-radius:5px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.38); }
    .company-social-dialog > header { display:flex; align-items:center; gap:12px; padding:19px 22px; border-bottom:5px solid #117e8c; background:#242426; color:#fff; }
    .company-social-dialog > header > span { width:40px; height:40px; display:grid; place-items:center; border-radius:3px; background:#117e8c; }
    .company-social-dialog > header > div { flex:1; }
    .company-social-dialog header small { display:block; color:#76c5ce; font-size:.6rem; font-weight:900; letter-spacing:.12em; }
    .company-social-dialog header h3 { margin:3px 0 0; font-size:1.12rem; font-weight:900; }
    .company-social-dialog > header button { width:36px; height:36px; border:1px solid #565259; border-radius:3px; background:#343436; color:#fff; cursor:pointer; }
    .company-social-modal-body { overflow-y:auto; padding:22px; }
    .company-social-company { margin-bottom:14px; padding:11px 13px; border-left:4px solid #ee9f2b; background:#fff5e6; color:#70572f; font-size:.75rem; }
    .company-social-company i { margin-right:7px; color:#ee9f2b; }
    .company-social-intro { margin:0 0 16px; color:#756a7a; font-size:.77rem; line-height:1.55; }
    .company-social-notice { display:flex; gap:8px; margin-bottom:13px; padding:11px; border-left:4px solid; font-size:.72rem; font-weight:800; }
    .company-social-notice.success { border-color:#7da533; background:#f3f7eb; color:#587923; }
    .company-social-notice.error { border-color:#b63b3b; background:#fff1f1; color:#9b2929; }
    .company-social-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:13px; }
    .company-social-option { display:flex; flex-direction:column; padding:17px; border:1px solid #ded7e1; border-top:4px solid #1877f2; border-radius:4px; color:#302834; text-decoration:none; transition:.2s ease; }
    .company-social-option.instagram { border-top-color:#d62976; }
    .company-social-option:hover { transform:translateY(-2px); box-shadow:0 10px 22px #ded9e0; }
    .company-social-option > div { display:flex; align-items:center; justify-content:space-between; }
    .company-social-option > div > span { width:37px; height:37px; display:grid; place-items:center; border-radius:50%; background:#1877f2; color:#fff; }
    .company-social-option.instagram > div > span { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }
    .company-social-option > div > b { padding:5px 7px; background:#edf7f8; color:#117e8c; font-size:.57rem; text-transform:uppercase; }
    .company-social-option h4 { margin:13px 0 0; font-size:.94rem; font-weight:900; }
    .company-social-option > p { flex:1; margin:6px 0 13px; color:#756a7a; font-size:.69rem; line-height:1.5; }
    .company-social-option > em { color:#5b2b76; font-size:.67rem; font-style:normal; font-weight:900; }
    .company-social-option aside { display:flex; align-items:center; gap:8px; margin-bottom:13px; padding:9px; border:1px solid #cdddaf; background:#f7faf1; color:#587923; }
    .company-social-option aside span, .company-social-option aside small, .company-social-option aside strong { display:block; }
    .company-social-option aside small { font-size:.52rem; font-weight:900; text-transform:uppercase; }
    .company-social-option aside strong { color:#35451b; font-size:.68rem; }
    .company-social-option.is-linked { border-color:#7da533; border-top-color:#7da533; }
    .company-social-option.is-disabled { background:#f4f2f5; opacity:.58; pointer-events:none; }
    .company-social-dialog > footer { display:flex; justify-content:flex-end; padding:13px 22px; border-top:1px solid #ded7e1; background:#f7f5f8; }
    .company-social-dialog > footer button { padding:9px 17px; border:0; border-radius:3px; background:#5b2b76; color:#fff; font-size:.73rem; font-weight:900; cursor:pointer; }

    html[data-client-theme="dark"] #company-detail { background:#141216; color:#e9e5eb; }
    html[data-client-theme="dark"] #company-detail .company-panel, html[data-client-theme="dark"] #company-detail .company-actions-panel { border-color:#403943; background:#1e1b21; box-shadow:0 10px 28px #0d0b0e; }
    html[data-client-theme="dark"] #company-detail .company-panel-heading, html[data-client-theme="dark"] #company-detail .company-actions-heading { border-color:#403943; background:#29252c; }
    html[data-client-theme="dark"] #company-detail h2, html[data-client-theme="dark"] #company-detail .company-identity h3, html[data-client-theme="dark"] #company-detail .questionnaire-state h4 { color:#f1edf3; }
    html[data-client-theme="dark"] #company-detail .company-data-item { border-color:#403943; background:#242127; }
    html[data-client-theme="dark"] #company-detail .company-data-item strong, html[data-client-theme="dark"] #company-detail .company-description p { color:#d0c8d3; }
    html[data-client-theme="dark"] #company-detail .company-identity { border-color:#403943; }
    html[data-client-theme="dark"] #company-detail .company-logo { border-color:#4a434e; background:#29252c; }
    html[data-client-theme="dark"] #company-detail .questionnaire-state.is-complete { background:#28321f; }
    html[data-client-theme="dark"] #company-detail .questionnaire-state.is-pending { background:#3a3020; }
    html[data-client-theme="dark"] #company-detail .questionnaire-state p { color:#c4bbc7; }
    html[data-client-theme="dark"] #company-detail .company-actions a { border-color:#4a434e; color:#ddd6df; }
    html[data-client-theme="dark"] #company-detail .company-actions a small { color:#aaa1ae; }
    html[data-client-theme="dark"] #company-detail .company-actions .company-action-primary { border-color:#754391; background:#754391; }
    html[data-client-theme="dark"] #company-detail .company-back-top { border-color:#4a434e; background:#29252c; color:#c89cdd; }
    html[data-client-theme="dark"] #company-detail .company-back-top:hover { border-color:#754391; background:#332b37; }
    html[data-client-theme="dark"] #company-detail .company-alert { background:#28321f; color:#b5d17e; }
    html[data-client-theme="dark"] #company-detail .company-social-section { border-color:#403943; }
    html[data-client-theme="dark"] #company-detail .company-social-title h3 { color:#f1edf3; }
    html[data-client-theme="dark"] #company-detail .company-social-connected article { border-color:#526b2b; background:#28321f; }
    html[data-client-theme="dark"] #company-detail .company-social-connected strong { color:#dcebbf; }
    html[data-client-theme="dark"] #company-detail .company-social-empty { background:#29252c; color:#aaa1ae; }
    html[data-client-theme="dark"] .company-social-dialog { background:#1e1b21; color:#f1edf3; }
    html[data-client-theme="dark"] .company-social-company { background:#3a3020; color:#efcf9e; }
    html[data-client-theme="dark"] .company-social-intro { color:#b4abb8; }
    html[data-client-theme="dark"] .company-social-option { border-color:#403943; background:#29252c; color:#f1edf3; }
    html[data-client-theme="dark"] .company-social-option > p { color:#b4abb8; }
    html[data-client-theme="dark"] .company-social-option.is-linked { border-color:#627f2f; }
    html[data-client-theme="dark"] .company-social-option aside { border-color:#526b2b; background:#20291a; }
    html[data-client-theme="dark"] .company-social-dialog > footer { border-color:#403943; background:#29252c; }

    @media (max-width:900px) { #company-detail .company-layout { grid-template-columns:1fr; } #company-detail .company-actions-panel { position:static; } }
    @media (max-width:720px) { #company-detail .company-hero { min-height:190px; padding:26px 20px; } #company-detail .company-hero-side { margin-left:auto; } #company-detail .company-mosaic { display:none; } #company-detail .company-content { margin:20px 16px; } }
    @media (max-width:520px) { #company-detail .company-hero { align-items:flex-start; flex-direction:column; gap:20px; } #company-detail .company-hero-side, #company-detail .company-hero-status { width:100%; margin-left:0; } #company-detail .company-profile { padding:19px; } #company-detail .company-identity { align-items:flex-start; } #company-detail .company-logo { width:64px; height:64px; } #company-detail .company-data-grid, .company-social-options { grid-template-columns:1fr; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('company-social-modal');
    const openButton = document.getElementById('open-company-social');
    const closeButtons = modal.querySelectorAll('[data-close-company-social]');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        modal.querySelector('header button').focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);
    closeButtons.forEach(button => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    @if(session('social_accounts_success') || session('social_accounts_error'))
        openModal();
    @endif
});
</script>
@endsection
