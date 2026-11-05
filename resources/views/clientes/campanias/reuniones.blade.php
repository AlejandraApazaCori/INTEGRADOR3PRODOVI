@extends('layouts.app2')

@section('title', 'Reuniones de campaña')

@section('content')
<div class="client-meetings-page">
    <header class="client-meetings-hero">
        <div>
            <span>Coordinación de campaña</span>
            <h1>Reuniones con tu <b>equipo</b></h1>
            <p>Agenda una conversación y consulta las reuniones de <strong>{{ $campania->nombre }}</strong>.</p>
        </div>
        <div class="client-meetings-hero-count"><small>Reuniones agendadas</small><strong>{{ $reuniones->count() }}</strong></div>
    </header>

    <main class="client-meetings-content">
        <div class="client-meetings-links">
            <a href="{{ route('clientes.micuenta') }}"><i class="fas fa-arrow-left"></i> Volver a mi cuenta</a>
            <a href="{{ route('clientes.campanias.feedback', $campania) }}"><i class="fas fa-comment-dots"></i> Mensajes con tu equipo</a>
        </div>

        @if(session('success'))<div class="client-meetings-success"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
        @if($errors->any())<div class="client-meetings-error"><i class="fas fa-circle-exclamation"></i><div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>@endif

        <div class="client-meetings-grid">
            <section class="client-meetings-card">
                <div class="client-meetings-card-head"><div><small>AGENDA</small><h2>Reuniones agendadas</h2></div><span>{{ $reuniones->count() }}</span></div>
                @forelse($reuniones as $reunion)
                    <article class="client-meeting-item">
                        <div class="client-meeting-date"><strong>{{ $reunion->fecha_inicio->format('d') }}</strong><span>{{ strtoupper($reunion->fecha_inicio->locale('es')->translatedFormat('M')) }}</span></div>
                        <div class="client-meeting-details">
                            <small>{{ ucfirst($reunion->plataforma) }} · {{ $reunion->fecha_inicio->format('H:i') }}–{{ $reunion->fecha_fin->format('H:i') }}</small>
                            <h3>{{ $reunion->titulo }}</h3>
                            <p>{{ $reunion->descripcion ?: 'Reunión de coordinación con el equipo.' }}</p>
                            <span><i class="fas fa-calendar-days"></i> {{ $reunion->fecha_inicio->translatedFormat('d \d\e F \d\e Y') }} · {{ $reunion->participantes->count() }} participantes</span>
                        </div>
                        <a class="client-meeting-open" href="{{ $reunion->enlace }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-video"></i> Abrir enlace</a>
                    </article>
                @empty
                    <div class="client-meetings-empty"><i class="fas fa-calendar-check"></i><strong>Aún no hay reuniones agendadas</strong><p>Cuando se programe una reunión, aparecerá aquí.</p></div>
                @endforelse
            </section>

            <aside class="client-meetings-card client-meetings-booking">
                <div class="client-meetings-card-head"><div><small>COORDINACIÓN</small><h2>Agendar reunión</h2></div><i class="fas fa-calendar-plus"></i></div>
                @if($cupoMensual === 0)
                    <p class="client-meetings-disabled">La agenda del cliente aún no está habilitada. Puedes coordinar una reunión por mensajes con tu equipo.</p>
                    <a class="client-meetings-message-link" href="{{ route('clientes.campanias.feedback', $campania) }}">Ir a mensajes <i class="fas fa-arrow-right"></i></a>
                @elseif($solicitudesDelMes >= $cupoMensual)
                    <p class="client-meetings-disabled">Ya usaste tus {{ $cupoMensual }} {{ $cupoMensual === 1 ? 'reunión' : 'reuniones' }} disponibles de este mes.</p>
                @else
                    <p class="client-meetings-quota">Puedes agendar {{ $cupoMensual - $solicitudesDelMes }} {{ $cupoMensual - $solicitudesDelMes === 1 ? 'reunión' : 'reuniones' }} más este mes.</p>
                    <form method="POST" action="{{ route('clientes.campanias.reuniones.store', $campania) }}">
                        @csrf
                        <label>Motivo de la reunión<input name="titulo" type="text" maxlength="150" value="{{ old('titulo') }}" placeholder="Ej. Revisión de avances" required></label>
                        <label>Descripción (opcional)<textarea name="descripcion" rows="3" maxlength="2000" placeholder="Temas que deseas revisar">{{ old('descripcion') }}</textarea></label>
                        <label>Fecha y hora<input name="fecha_inicio" type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('fecha_inicio') }}" required></label>
                        <label>Plataforma<select name="plataforma" required><option value="meet" @selected(old('plataforma') === 'meet')>Google Meet</option><option value="zoom" @selected(old('plataforma') === 'zoom')>Zoom</option><option value="teams" @selected(old('plataforma') === 'teams')>Microsoft Teams</option><option value="otro" @selected(old('plataforma') === 'otro')>Otra</option></select></label>
                        <label>Enlace de la reunión<input name="enlace" type="url" maxlength="2048" value="{{ old('enlace') }}" placeholder="https://meet.google.com/..." required></label>
                        <small>La reunión durará una hora. El equipo recibirá una notificación al agendarla.</small>
                        <button type="submit"><i class="fas fa-calendar-check"></i> Agendar reunión</button>
                    </form>
                @endif
            </aside>
        </div>
    </main>
</div>

<style>
    .client-meetings-page{min-height:100vh;background:#fff;color:#302834}.client-meetings-hero{display:flex;align-items:center;justify-content:space-between;gap:25px;min-height:150px;padding:28px 32px;background:#242426;color:#fff}.client-meetings-hero>div:first-child>span{color:#62bdc7;font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.client-meetings-hero h1{margin:8px 0 0;font-size:clamp(1.65rem,3vw,2.3rem);font-weight:800;letter-spacing:-.035em}.client-meetings-hero h1 b{color:#62bdc7}.client-meetings-hero p{margin:10px 0 0;color:#c8c2ca;font-size:.83rem}.client-meetings-hero p strong{color:#fff}.client-meetings-hero-count{min-width:155px;padding:13px 16px;border-left:4px solid #117e8c;background:#303033}.client-meetings-hero-count small,.client-meetings-hero-count strong{display:block}.client-meetings-hero-count small{font-size:.64rem;color:#c8c2ca}.client-meetings-hero-count strong{margin-top:3px;font-size:1.35rem}.client-meetings-content{width:min(1320px,calc(100% - 48px));margin:0 auto;padding:24px 0 50px}.client-meetings-links{display:flex;flex-wrap:wrap;gap:17px;margin-bottom:20px}.client-meetings-links a{color:#117e8c;font-size:.7rem;font-weight:900;text-decoration:none}.client-meetings-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,.8fr);gap:18px;align-items:start}.client-meetings-card{overflow:hidden;border:1px solid #ded7e1;border-radius:12px;background:#fff;box-shadow:0 7px 18px rgba(48,40,52,.06)}.client-meetings-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:19px 22px;border-bottom:1px solid #e8e2ea}.client-meetings-card-head small{color:#117e8c;font-size:.55rem;font-weight:900;letter-spacing:.1em}.client-meetings-card-head h2{margin:4px 0 0;font-size:1rem;font-weight:900}.client-meetings-card-head>span,.client-meetings-card-head>i{color:#117e8c;font-weight:900}.client-meeting-item{display:grid;grid-template-columns:58px minmax(0,1fr) auto;align-items:center;gap:15px;margin:0 22px;padding:18px 0;border-bottom:1px solid #eee9f0}.client-meeting-item:last-child{border-bottom:0}.client-meeting-date{height:58px;display:grid;place-content:center;border-radius:9px;background:#edf7f8;color:#117e8c;text-align:center}.client-meeting-date strong{font-size:1.2rem;line-height:1}.client-meeting-date span{margin-top:3px;font-size:.53rem;font-weight:900}.client-meeting-details small{color:#817985;font-size:.59rem}.client-meeting-details h3{margin:4px 0;color:#302834;font-size:.8rem;font-weight:900}.client-meeting-details p{margin:5px 0;color:#716a75;font-size:.66rem}.client-meeting-details>span{color:#807886;font-size:.59rem}.client-meeting-open,.client-meetings-message-link{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 11px;border-radius:8px;background:#117e8c;color:#fff;font-size:.61rem;font-weight:900;text-decoration:none;white-space:nowrap}.client-meetings-empty{display:grid;justify-items:center;gap:7px;padding:55px 20px;color:#7e7581;text-align:center}.client-meetings-empty i{color:#117e8c;font-size:1.5rem}.client-meetings-empty strong{color:#302834;font-size:.82rem}.client-meetings-empty p{margin:0;font-size:.66rem}.client-meetings-booking form{display:grid;gap:13px;padding:20px 22px}.client-meetings-booking form label{display:grid;gap:6px;color:#4b4250;font-size:.63rem;font-weight:900}.client-meetings-booking :is(input,select,textarea){width:100%;padding:10px 11px;border:1px solid #dcd6df;border-radius:8px;background:#fff;color:#302834;font:inherit;font-size:.7rem}.client-meetings-booking form>small{color:#817985;font-size:.57rem}.client-meetings-booking form button{padding:11px;border:0;border-radius:8px;background:#117e8c;color:#fff;font-size:.68rem;font-weight:900;cursor:pointer}.client-meetings-quota,.client-meetings-disabled{margin:17px 22px 0;padding:11px 13px;border-radius:8px;background:#edf7f8;color:#47686c;font-size:.64rem;font-weight:700}.client-meetings-disabled{margin-bottom:17px}.client-meetings-message-link{margin:0 22px 20px}.client-meetings-success,.client-meetings-error{display:flex;gap:8px;margin-bottom:16px;padding:12px;border-radius:8px;background:#e9f7ec;color:#276738;font-size:.68rem;font-weight:800}.client-meetings-error{background:#fff0f0;color:#a72d2d}.client-meetings-error p{margin:0}.client-meetings-error p+p{margin-top:4px}
    html[data-client-theme="dark"] .client-meetings-page{background:#141216;color:#e9e5eb}html[data-client-theme="dark"] .client-meetings-card{background:#1e1b21;border-color:#403943;box-shadow:none}html[data-client-theme="dark"] .client-meetings-card-head,html[data-client-theme="dark"] .client-meeting-item{border-color:#403943}html[data-client-theme="dark"] .client-meeting-details h3,html[data-client-theme="dark"] .client-meetings-empty strong{color:#f4f1f5}html[data-client-theme="dark"] .client-meeting-details :is(p,small,span),html[data-client-theme="dark"] .client-meetings-empty{color:#bbb2be}html[data-client-theme="dark"] .client-meeting-date,html[data-client-theme="dark"] .client-meetings-quota,html[data-client-theme="dark"] .client-meetings-disabled{background:#29252c;color:#c9e9ed}html[data-client-theme="dark"] .client-meetings-booking form label{color:#e9e5eb}html[data-client-theme="dark"] .client-meetings-booking :is(input,select,textarea){background:#29252c;border-color:#4a434e;color:#f4f1f5;color-scheme:dark}html[data-client-theme="dark"] .client-meetings-booking form>small{color:#bbb2be}
    @media(max-width:850px){.client-meetings-grid{grid-template-columns:1fr}}@media(max-width:600px){.client-meetings-hero{align-items:flex-start;flex-direction:column;padding:25px 20px}.client-meetings-hero-count{width:100%}.client-meetings-content{width:calc(100% - 24px)}.client-meeting-item{grid-template-columns:50px 1fr}.client-meeting-open{grid-column:2;justify-self:start}}
</style>
@endsection
