@extends('layouts.app2')

@section('title', 'Detalles de la Empresa')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
                    <a href="{{ route('clientes.micuenta') }}" class="company-action-back"><i class="fas fa-arrow-left"></i><span><strong>Volver a Mi cuenta</strong><small>Regresar al listado</small></span></a>
                </nav>
            </aside>
        </div>
    </main>
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
    #company-detail .company-actions .company-action-back { grid-template-columns:34px 1fr; margin-top:6px; border:0; border-top:1px solid #ded7e1; border-radius:0; }

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
    html[data-client-theme="dark"] #company-detail .company-actions .company-action-back { border-top-color:#403943; }
    html[data-client-theme="dark"] #company-detail .company-alert { background:#28321f; color:#b5d17e; }

    @media (max-width:900px) { #company-detail .company-layout { grid-template-columns:1fr; } #company-detail .company-actions-panel { position:static; } }
    @media (max-width:720px) { #company-detail .company-hero { min-height:190px; padding:26px 20px; } #company-detail .company-hero-side { margin-left:auto; } #company-detail .company-mosaic { display:none; } #company-detail .company-content { margin:20px 16px; } }
    @media (max-width:520px) { #company-detail .company-hero { align-items:flex-start; flex-direction:column; gap:20px; } #company-detail .company-hero-side, #company-detail .company-hero-status { width:100%; margin-left:0; } #company-detail .company-profile { padding:19px; } #company-detail .company-identity { align-items:flex-start; } #company-detail .company-logo { width:64px; height:64px; } #company-detail .company-data-grid { grid-template-columns:1fr; } }
</style>
@endsection
