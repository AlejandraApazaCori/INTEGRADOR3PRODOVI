@extends('layouts.app')

@section('title', 'Solicitudes web')

@section('content')
<div id="contact-requests-index">
    <header class="requests-banner">
        <div class="requests-banner-overlay" aria-hidden="true"></div>
        <div class="requests-banner-content">
            <div>
                <a href="{{ route('administrador.dashboard') }}" class="requests-back"><i class="fas fa-arrow-left"></i> Volver al dashboard</a>
                <h1>Solicitudes de <span>contacto</span></h1>
                <p>Gestiona los proyectos enviados desde el formulario público.</p>
            </div>
            <a href="{{ url('/#contact') }}" target="_blank" rel="noopener noreferrer" class="requests-public-link"><i class="fas fa-arrow-up-right-from-square"></i> Ver formulario público</a>
        </div>
    </header>

    <main class="requests-content">
        <section class="requests-overview">
            <div>
                <span><i class="fas fa-comments"></i> Hablemos de tu Proyecto</span>
                <h2>Solicitudes recibidas</h2>
                <p>Consulta los datos de contacto, el servicio de interés y el mensaje enviado por cada posible cliente.</p>
            </div>
        </section>

        <section class="requests-kpis" aria-label="Resumen de solicitudes">
            <article class="requests-kpi-total"><div><span>Total recibidas</span><strong>{{ $stats['total'] }}</strong><small>Solicitudes registradas</small></div><i class="fas fa-inbox"></i></article>
            <article class="requests-kpi-month"><div><span>Este mes</span><strong>{{ $stats['este_mes'] }}</strong><small>Nuevos proyectos</small></div><i class="fas fa-calendar-days"></i></article>
            <article class="requests-kpi-sent"><div><span>Confirmadas</span><strong>{{ $stats['correo_enviado'] }}</strong><small>Correo enviado</small></div><i class="fas fa-circle-check"></i></article>
            <article class="requests-kpi-pending"><div><span>Sin confirmación</span><strong>{{ $stats['correo_pendiente'] }}</strong><small>Requieren revisión</small></div><i class="fas fa-envelope-open-text"></i></article>
        </section>

        <section class="requests-filter-panel">
            <div class="requests-filter-title"><span><i class="fas fa-filter"></i> Filtrar solicitudes</span><small>{{ $solicitudes->total() }} resultado(s)</small></div>
            <form method="GET" action="{{ route('administrador.solicitudes-contacto.index') }}" class="requests-filter-form">
                <label class="requests-search"><span>Buscar</span><div><i class="fas fa-magnifying-glass"></i><input name="buscar" type="search" value="{{ request('buscar') }}" placeholder="Nombre, correo, teléfono o mensaje"></div></label>
                <label><span>Servicio</span><select name="servicio"><option value="">Todos los servicios</option>@foreach ($servicios as $valor => $etiqueta)<option value="{{ $valor }}" @selected(request('servicio') === $valor)>{{ $etiqueta }}</option>@endforeach</select></label>
                <label><span>Confirmación</span><select name="envio"><option value="">Todos los estados</option><option value="enviado" @selected(request('envio') === 'enviado')>Enviada</option><option value="no_enviado" @selected(request('envio') === 'no_enviado')>No enviada</option></select></label>
                <div class="requests-filter-actions">
                    <button type="submit"><i class="fas fa-magnifying-glass"></i> Aplicar filtros</button>
                    @if (request()->hasAny(['buscar', 'servicio', 'envio']))<a href="{{ route('administrador.solicitudes-contacto.index') }}" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>@endif
                </div>
            </form>
        </section>

        <section class="requests-list" aria-label="Listado de solicitudes">
            @forelse ($solicitudes as $index => $solicitud)
                @php
                    $telefonoWhatsApp = ltrim((string) $solicitud->telefono, '0');
                    $telefonoWhatsApp = str_starts_with($telefonoWhatsApp, '591') ? $telefonoWhatsApp : '591'.$telefonoWhatsApp;
                @endphp
                <article class="request-card">
                    <div class="request-main">
                        <div class="request-number">{{ ($solicitudes->firstItem() ?? 1) + $index }}</div>
                        <div class="request-copy">
                            <div class="request-heading">
                                <div><h2>{{ $solicitud->nombre }}</h2><span class="request-service">{{ $solicitud->servicio_nombre }}</span></div>
                                <div class="request-status-wrap">
                                    <time datetime="{{ $solicitud->created_at->toIso8601String() }}"><i class="far fa-clock"></i> {{ $solicitud->created_at->format('d/m/Y H:i') }}</time>
                                    @if ($solicitud->correo_enviado_at)<span class="request-status is-sent"><i class="fas fa-check"></i> Confirmación enviada</span>@else<span class="request-status is-pending"><i class="fas fa-exclamation"></i> Sin confirmación</span>@endif
                                </div>
                            </div>
                            <div class="request-contact-data">
                                <a href="mailto:{{ $solicitud->correo }}"><i class="fas fa-envelope"></i> {{ $solicitud->correo }}</a>
                                @if ($solicitud->telefono)<a href="tel:{{ $solicitud->telefono }}"><i class="fas fa-phone"></i> {{ $solicitud->telefono }}</a>@else<span><i class="fas fa-phone-slash"></i> Sin teléfono</span>@endif
                            </div>
                            <div class="request-message"><span>Cuéntanos sobre tu proyecto</span><p>{{ $solicitud->mensaje }}</p></div>
                        </div>
                    </div>
                    <aside class="request-actions">
                        <a href="mailto:{{ $solicitud->correo }}?subject={{ rawurlencode('Respuesta a tu solicitud en PRODOVI') }}" class="request-action-email"><i class="fas fa-reply"></i><span>Responder</span></a>
                        @if ($solicitud->telefono)<a href="https://wa.me/{{ $telefonoWhatsApp }}" target="_blank" rel="noopener noreferrer" class="request-action-whatsapp"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>@endif
                    </aside>
                </article>
            @empty
                <div class="requests-empty"><i class="fas fa-inbox"></i><h2>No hay solicitudes para mostrar</h2><p>Cuando alguien complete “Hablemos de tu Proyecto”, aparecerá aquí.</p></div>
            @endforelse
        </section>

        @if ($solicitudes->hasPages())<div class="requests-pagination">{{ $solicitudes->onEachSide(1)->links('componentes.paginacion-es') }}</div>@endif
    </main>
</div>

<style>
    #contact-requests-index{min-height:100vh;padding-bottom:44px;background:#f7f8fa;--orange:#ef6c22;--turquoise:#117e8c;--green:#7da533;--purple:#5b2b76;--ink:#302834}#contact-requests-index *{box-sizing:border-box}
    .requests-banner{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}.requests-banner-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.2),rgba(15,23,42,.2)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 48%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 45%)}.requests-banner-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:28px 48px}.requests-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:13px;padding:7px 12px;border:1px solid rgba(255,255,255,.36);border-radius:9px;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:800;transition:.18s}.requests-back:hover{border-color:#fff;background:#fff;color:#2563eb}.requests-banner h1{margin:0;color:#fff;font-size:2rem;font-weight:900;line-height:1.1}.requests-banner h1 span{color:#dbeafe}.requests-banner p{margin:8px 0 0;color:#dbeafe;font-size:.82rem}.requests-public-link{min-height:44px;display:inline-flex;align-items:center;justify-content:center;gap:9px;flex:none;padding:0 16px;border:1px solid #fff;border-radius:10px;background:#fff;color:#2563eb;font-size:.72rem;font-weight:900;box-shadow:0 9px 22px rgba(15,23,42,.16);transition:.18s}.requests-public-link:hover{transform:translateY(-2px);box-shadow:0 13px 27px rgba(15,23,42,.22)}
    .requests-content{padding:0 48px}.requests-overview{margin-top:26px;padding:0 0 16px;border-bottom:1px solid #ddd8df}.requests-overview>div>span{display:flex;align-items:center;gap:7px;color:var(--turquoise);font-size:.62rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.requests-overview h2{margin:5px 0 0;color:var(--ink);font-size:1.35rem;font-weight:900}.requests-overview p{max-width:760px;margin:5px 0 0;color:#756a7a;font-size:.74rem;line-height:1.55}
    .requests-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px}.requests-kpis article{--accent:#117e8c;--soft:#e6f4f5;--rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px;border:1px solid rgba(var(--rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--soft));box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09);transition:.22s}.requests-kpis article::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--rgb),.09);border-radius:50%}.requests-kpis article::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.requests-kpis article:hover{transform:translateY(-5px);box-shadow:inset 0 4px 0 var(--accent),0 17px 32px rgba(var(--rgb),.16)}.requests-kpis span,.requests-kpis small{display:block}.requests-kpis span{color:#596170;font-size:.7rem;font-weight:900;text-transform:uppercase}.requests-kpis strong{display:block;margin-top:9px;color:#263024;font-size:1.85rem;font-weight:900;line-height:1}.requests-kpis small{margin-top:8px;color:#7f8878;font-size:.62rem}.requests-kpis article>i{width:52px;height:52px;display:grid;place-items:center;flex:none;border-radius:14px;background:var(--accent);color:#fff;font-size:1.18rem;box-shadow:0 8px 17px rgba(var(--rgb),.27)}.requests-kpi-total{--accent:#117e8c!important;--soft:#e6f4f5!important;--rgb:17,126,140!important}.requests-kpi-month{--accent:#5b2b76!important;--soft:#f3edf6!important;--rgb:91,43,118!important}.requests-kpi-sent{--accent:#7da533!important;--soft:#f0f6e7!important;--rgb:125,165,51!important}.requests-kpi-pending{--accent:#e37225!important;--soft:#fff0e6!important;--rgb:227,114,37!important}
    .requests-filter-panel{margin-top:22px;padding:16px;border:1px solid #e1dde4;border-left:4px solid var(--turquoise);border-radius:12px;background:#fff;box-shadow:0 5px 14px rgba(48,40,52,.05)}.requests-filter-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px;padding-bottom:11px;border-bottom:1px solid #ece8ee}.requests-filter-title span{color:#655c68;font-size:.72rem;font-weight:900}.requests-filter-title i{margin-right:6px;color:var(--turquoise)}.requests-filter-title small{color:#8a818d;font-size:.66rem;font-weight:700}.requests-filter-form{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}.requests-filter-form label>span{display:block;margin-bottom:6px;color:#706775;font-size:.62rem;font-weight:900;text-transform:uppercase}.requests-filter-form input,.requests-filter-form select{width:100%;height:44px;border:1px solid #dcd7de;border-radius:9px;background:#fff;color:#453c48;font-size:.72rem;outline:none}.requests-filter-form input:focus,.requests-filter-form select:focus{border-color:var(--turquoise);box-shadow:0 0 0 3px rgba(17,126,140,.1)}.requests-search div{position:relative}.requests-search i{position:absolute;top:15px;left:14px;color:#9a929d;font-size:.75rem}.requests-search input{padding:0 13px 0 39px}.requests-filter-form select{padding:0 12px}.requests-filter-actions{display:flex;gap:8px}.requests-filter-actions button,.requests-filter-actions a{height:44px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:9px;font-size:.7rem;font-weight:900}.requests-filter-actions button{padding:0 16px;border:0;background:var(--orange);color:#fff;box-shadow:0 7px 15px rgba(239,108,34,.2)}.requests-filter-actions button:hover{background:#dc5710}.requests-filter-actions a{width:44px;border:1px solid #dcd7de;background:#f7f5f8;color:#706775}
    .requests-list{display:grid;gap:18px;margin-top:18px}.request-card{--card-accent:var(--orange);display:flex;min-height:185px;overflow:hidden;border:1px solid #ded7e1;border-top:5px solid var(--card-accent);border-radius:5px;background:#fff;box-shadow:0 15px 36px #e5dfe7;transition:.2s}.request-card:nth-child(3n+2){--card-accent:var(--turquoise)}.request-card:nth-child(3n){--card-accent:var(--purple)}.request-card:hover{transform:translateY(-5px);box-shadow:0 22px 44px #d8d0db}.request-main{min-width:0;display:flex;flex:1;gap:16px;padding:22px 24px}.request-number{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:4px;background:var(--card-accent);color:#fff;font-size:.82rem;font-weight:900}.request-copy{min-width:0;flex:1}.request-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}.request-heading h2{margin:0;color:var(--ink);font-size:.95rem;font-weight:900}.request-service{display:inline-block;margin-top:6px;padding:4px 8px;border-radius:999px;background:#f4f1f5;color:#77707a;font-size:.59rem;font-weight:800}.request-status-wrap{display:flex;align-items:flex-end;flex-direction:column;gap:7px}.request-status-wrap time{color:#8b828e;font-size:.62rem;font-weight:700}.request-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;font-size:.58rem;font-weight:900}.request-status.is-sent{background:#edf6e4;color:#587922}.request-status.is-pending{background:#fff0e6;color:#b64b0d}.request-contact-data{display:flex;flex-wrap:wrap;gap:8px 18px;margin-top:11px}.request-contact-data a,.request-contact-data span{color:#6f6673;font-size:.66rem;font-weight:700}.request-contact-data i{margin-right:5px;color:var(--card-accent)}.request-contact-data a:hover{color:var(--turquoise)}.request-message{margin-top:14px;padding-top:12px;border-top:1px solid #ece8ee}.request-message span{color:#938a96;font-size:.57rem;font-weight:900;letter-spacing:.07em;text-transform:uppercase}.request-message p{margin:5px 0 0;color:#655c68;font-size:.7rem;line-height:1.55;white-space:pre-line}.request-actions{width:132px;display:flex;align-items:stretch;justify-content:center;flex-direction:column;gap:9px;padding:18px;border-left:1px solid #e5dfe7;background:#f7f5f8}.request-actions a{min-height:42px;display:flex;align-items:center;justify-content:center;gap:7px;border:1px solid #e1dce3;border-radius:9px;background:#fff;font-size:.64rem;font-weight:900;box-shadow:0 3px 8px rgba(48,40,52,.07);transition:.18s}.request-actions a:hover{transform:translateY(-2px)}.request-action-email{color:var(--purple)}.request-action-whatsapp{color:#25804a}.requests-empty{padding:55px 24px;border:1px dashed #d4ced6;border-radius:14px;background:#fff;text-align:center}.requests-empty>i{color:#c7c0ca;font-size:2.2rem}.requests-empty h2{margin:13px 0 3px;color:var(--ink);font-size:1rem;font-weight:900}.requests-empty p{margin:0;color:#8b828e;font-size:.7rem}.requests-pagination{margin-top:22px;padding-top:15px;border-top:1px solid #ddd8df}
    @media(max-width:1050px){.requests-kpis{grid-template-columns:repeat(2,1fr)}.requests-filter-form{grid-template-columns:repeat(2,1fr)}.requests-filter-actions{grid-column:1/-1}.requests-filter-actions button{flex:1}}@media(max-width:700px){.requests-banner-content{align-items:stretch;flex-direction:column;padding:24px 18px}.requests-public-link{width:100%}.requests-content{padding:0 18px}.requests-kpis,.requests-filter-form{grid-template-columns:1fr}.requests-filter-actions{grid-column:auto}.request-card{align-items:stretch;flex-direction:column}.request-main{padding:18px 16px}.request-heading{flex-direction:column}.request-status-wrap{align-items:flex-start}.request-actions{width:100%;flex-direction:row;padding:12px 16px;border-top:1px solid #e5dfe7;border-left:0}.request-actions a{flex:1}.requests-banner h1{font-size:1.7rem}}
</style>
@endsection
