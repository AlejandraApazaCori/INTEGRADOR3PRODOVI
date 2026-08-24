@extends('layouts.app')

@section('title', 'Detalle de campaña')

@section('content')
@php
    $fechaInicio = \Carbon\Carbon::parse($campania->fecha_inicio);
    $fechaFin = \Carbon\Carbon::parse($campania->fecha_fin);
    $duracion = (int) $fechaInicio->diffInDays($fechaFin);
    $diasRestantes = (int) now()->startOfDay()->diffInDays($fechaFin->copy()->startOfDay(), false);
    $avance = $duracion > 0
        ? max(0, min(100, (int) round($fechaInicio->diffInDays(now(), false) / $duracion * 100)))
        : 100;
    $cliente = $campania->cliente;
    $empresa = $cliente?->empresas?->first();
    $creador = $campania->creador;
    $communityManager = $campania->communityManager;
    $estadoClase = match($campania->estado) {
        'activa' => 'is-active',
        'pausada' => 'is-paused',
        default => 'is-finished',
    };
@endphp

<div class="campaign-detail-page">
    <div class="campaign-detail-shell">
        <nav class="campaign-detail-actions" aria-label="Acciones de campaña">
            <a href="{{ route('administrador.campañas.index') }}" class="campaign-detail-action"><i class="fas fa-table-columns"></i> General</a>
            <a href="{{ route('administrador.campañas.calendario', $campania->id) }}" class="campaign-detail-action"><i class="fas fa-calendar-days"></i> Calendario</a>
            <a href="{{ route('administrador.campañas.edit', $campania->id) }}" class="campaign-detail-action is-primary"><i class="fas fa-pen"></i> Editar campaña</a>
        </nav>

        <header class="campaign-detail-hero rp-banner">
            <div class="campaign-detail-overlay"></div>
            <div class="campaign-detail-hero-content">
                <div class="campaign-detail-heading">
                    <div>
                        <span class="campaign-detail-eyebrow">Operación de marketing</span>
                        <h1>{{ $campania->nombre }}</h1>
                        <p>Información, equipo, cronograma y tareas de la campaña</p>
                    </div>
                </div>
                <span class="campaign-status {{ $estadoClase }}"><span></span>{{ ucfirst($campania->estado) }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="campaign-detail-alert is-success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="campaign-detail-alert is-error"><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</div>
        @endif

        <main class="campaign-detail-content">
            <section class="campaign-detail-kpis" aria-label="Resumen de la campaña">
                <article class="campaign-detail-kpi kpi-calendar">
                    <div><span>Duración</span><strong>{{ $duracion }}</strong><small>días planificados</small></div>
                </article>
                <article class="campaign-detail-kpi kpi-progress">
                    <div><span>Progreso temporal</span><strong>{{ $avance }}%</strong><small>del periodo transcurrido</small></div>
                </article>
                <article class="campaign-detail-kpi kpi-deadline">
                    <div>
                        <span>Fecha de cierre</span>
                        <strong>{{ $fechaFin->format('d/m/Y') }}</strong>
                        <small>{{ $diasRestantes >= 0 ? ($diasRestantes === 0 ? 'Finaliza hoy' : $diasRestantes.' días restantes') : 'Campaña finalizada' }}</small>
                    </div>
                </article>
            </section>

            <div class="campaign-detail-grid">
                <section class="campaign-detail-card campaign-overview-card">
                    <div class="campaign-card-body">
                        <div class="campaign-description-block">
                            <h2 class="campaign-underlined-title">Descripción</h2>
                            <p>{{ $campania->descripcion ?: 'Sin descripción registrada para esta campaña.' }}</p>
                        </div>
                        <div class="campaign-schedule">
                            <div class="campaign-date">
                                <div><small>Fecha de inicio</small><strong>{{ $fechaInicio->format('d/m/Y') }}</strong></div>
                            </div>
                            <div class="campaign-date is-end">
                                <div><small>Fecha de finalización</small><strong>{{ $fechaFin->format('d/m/Y') }}</strong></div>
                            </div>
                        </div>
                        <div class="campaign-progress">
                            <div><span>Avance del periodo</span><strong>{{ $avance }}%</strong></div>
                            <div class="campaign-progress-track"><span style="width: {{ $avance }}%"></span></div>
                            <small>{{ $duracion }} días entre el inicio y la finalización</small>
                        </div>
                    </div>
                </section>

                <aside class="campaign-detail-sidebar">
                    <section class="campaign-detail-card">
                        <div class="campaign-card-header compact">
                            <div><span>Cuenta vinculada</span><h2>Cliente</h2></div>
                        </div>
                        <div class="campaign-card-body compact">
                            <div class="campaign-person">
                                <span class="campaign-avatar is-teal">{{ strtoupper(substr($cliente?->name ?? 'SC', 0, 2)) }}</span>
                                <div><strong>{{ $cliente?->name ?? 'Sin cliente asignado' }}</strong><small>{{ $cliente?->email ?? 'Sin correo registrado' }}</small></div>
                            </div>
                            @if($empresa)
                                <div class="campaign-company-row">
                                    <div><span>Empresa registrada</span></div>
                                    <a href="{{ route('administrador.empresas.show', $empresa->id) }}">Ver empresa <i class="fas fa-arrow-right"></i></a>
                                </div>
                            @else
                                <div class="campaign-empty-note">Cliente sin empresa registrada</div>
                            @endif
                        </div>
                    </section>

                    <section class="campaign-detail-card">
                        <div class="campaign-card-header compact">
                            <div><span>Responsables</span><h2>Equipo de trabajo</h2></div>
                        </div>
                        <div class="campaign-card-body compact campaign-team">
                            <div class="campaign-person">
                                <span class="campaign-avatar is-indigo">{{ strtoupper(substr($creador?->name ?? 'SA', 0, 2)) }}</span>
                                <div><small>Creador</small><strong>{{ $creador?->name ?? 'Sin asignar' }}</strong></div>
                            </div>
                            <div class="campaign-person">
                                <span class="campaign-avatar is-purple">{{ strtoupper(substr($communityManager?->name ?? 'SA', 0, 2)) }}</span>
                                <div><small>Community Manager</small><strong>{{ $communityManager?->name ?? 'Sin asignar' }}</strong></div>
                            </div>
                        </div>
                    </section>

                    <section class="campaign-analytics-card">
                        <div><span>Rendimiento</span><strong>Analíticas de campaña</strong><small>Consulta los resultados y métricas del cliente.</small></div>
                        @if($cliente)
                            <a href="{{ route('administrador.usuarios.analiticas-campania', $cliente->id) }}">Ver analíticas <i class="fas fa-arrow-right"></i></a>
                        @endif
                    </section>
                </aside>
            </div>
        </main>

        <div class="campaign-tasks">
            @include('administrador.tareas.index')
        </div>
    </div>
</div>

<style>
    .rp-banner{background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#1d4ed8}
    .campaign-detail-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}
    .campaign-detail-shell{position:relative;width:100%}
    .campaign-detail-hero{position:relative;min-height:180px;overflow:hidden}
    .campaign-detail-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .campaign-detail-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:30px 490px 30px 48px}
    .campaign-detail-heading{min-width:0;display:flex;align-items:center;gap:16px}
    .campaign-detail-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 52px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.25rem;backdrop-filter:blur(5px)}
    .campaign-detail-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}
    .campaign-detail-heading h1{max-width:700px;margin:0 0 4px;overflow:hidden;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;text-overflow:ellipsis;white-space:nowrap}
    .campaign-detail-heading p{margin:0;color:#dbeafe;font-size:.74rem;font-weight:600}
    .campaign-status{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.45);border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:.66rem;font-weight:900;text-transform:uppercase;backdrop-filter:blur(5px)}
    .campaign-status i{font-size:.44rem}.campaign-status.is-active i{color:#bef264}.campaign-status.is-paused i{color:#fde047}.campaign-status.is-finished i{color:#cbd5e1}
    .campaign-detail-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:9px}
    .campaign-detail-action{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}
    .campaign-detail-action.is-primary,.campaign-detail-action:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .campaign-detail-alert{width:calc(100% - 48px);margin:24px auto 0;padding:13px 16px;display:flex;align-items:center;gap:10px;border:1px solid;border-radius:12px;font-size:.76rem;font-weight:800}.campaign-detail-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.campaign-detail-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .campaign-detail-content{padding:24px 24px 0}
    .campaign-detail-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:22px}
    .campaign-detail-kpi{--accent:#117e8c;--soft:#e6f4f5;--rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;min-height:126px;padding:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;border:1px solid rgba(var(--rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--soft));box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09)}
    .campaign-detail-kpi:after{content:'';position:absolute;z-index:-1;right:12px;bottom:7px;width:84px;height:43px;opacity:.2;background-image:radial-gradient(circle,var(--accent) 1.4px,transparent 1.6px);background-size:9px 9px}
    .campaign-detail-kpi span,.campaign-detail-kpi small{display:block}.campaign-detail-kpi span{color:#596170;font-size:.67rem;font-weight:900;letter-spacing:.03em;text-transform:uppercase}.campaign-detail-kpi strong{display:block;margin-top:8px;color:#263024;font-size:1.55rem;font-weight:900;line-height:1.1}.campaign-detail-kpi small{margin-top:7px;color:#7f8878;font-size:.62rem;font-weight:600}
    .campaign-detail-kpi>i{width:50px;height:50px;display:grid;place-items:center;flex:0 0 50px;border-radius:14px;background:var(--accent);color:#fff;font-size:1.1rem;box-shadow:0 8px 17px rgba(var(--rgb),.27)}
    .kpi-calendar{--accent:#7da533;--soft:#f0f6e7;--rgb:125,165,51}.kpi-progress{--accent:#117e8c;--soft:#e6f4f5;--rgb:17,126,140}.kpi-deadline{--accent:#5b2b76;--soft:#f3edf6;--rgb:91,43,118}
    .campaign-detail-grid{display:grid;grid-template-columns:minmax(0,1.75fr) minmax(300px,.85fr);gap:18px}
    .campaign-detail-sidebar{display:grid;align-content:start;gap:18px}
    .campaign-detail-card{overflow:hidden;border:1px solid #e1e3de;border-radius:16px;background:#fff;box-shadow:0 9px 22px rgba(55,60,52,.06)}
    .campaign-card-header{display:flex;align-items:center;gap:13px;padding:20px 22px 16px;border-bottom:1px solid #eceeea}.campaign-card-header.compact{padding:16px 18px 14px}
    .campaign-card-title-icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;border-radius:12px;color:#fff;box-shadow:0 7px 15px rgba(55,60,52,.14)}.campaign-card-title-icon.is-indigo{background:#4f46e5}.campaign-card-title-icon.is-teal{background:#117e8c}.campaign-card-title-icon.is-purple{background:#5b2b76}
    .campaign-card-header span{display:block;margin-bottom:2px;color:#8a9186;font-size:.58rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase}.campaign-card-header h2{margin:0;color:#25272b;font-size:.96rem;font-weight:900;letter-spacing:-.015em}
    .campaign-card-body{padding:22px}.campaign-card-body.compact{padding:17px 18px}
    .campaign-name-block small,.campaign-description-block small{display:block;color:#8a9186;font-size:.6rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.campaign-name-block h3{margin:5px 0 0;color:#272b31;font-size:1.15rem;font-weight:900}.campaign-description-block{margin-top:21px;padding-top:19px;border-top:1px solid #eceeea}.campaign-description-block p{margin:7px 0 0;color:#626a60;font-size:.77rem;font-weight:600;line-height:1.65}
    .campaign-schedule{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:20px}.campaign-date{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dce8cc;border-radius:13px;background:#f5f9ef}.campaign-date.is-end{border-color:#e6d9eb;background:#f8f3fa}.campaign-date>i{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:10px;background:#7da533;color:#fff;font-size:.72rem}.campaign-date.is-end>i{background:#5b2b76}.campaign-date small,.campaign-date strong{display:block}.campaign-date small{color:#7e8778;font-size:.57rem;font-weight:850;text-transform:uppercase}.campaign-date strong{margin-top:3px;color:#353a32;font-size:.74rem;font-weight:900}
    .campaign-progress{margin-top:20px;padding:15px;border:1px solid #e1e4de;border-radius:13px;background:#fafbf9}.campaign-progress>div:first-child{display:flex;justify-content:space-between;color:#596170;font-size:.68rem;font-weight:850}.campaign-progress-track{height:8px;margin:10px 0 8px;overflow:hidden;border-radius:999px;background:#e8ebe5}.campaign-progress-track span{display:block;height:100%;border-radius:inherit;background:#117e8c}.campaign-progress>small{color:#8a9186;font-size:.58rem;font-weight:600}
    .campaign-person{min-width:0;display:flex;align-items:center;gap:11px}.campaign-person+.campaign-person{margin-top:14px;padding-top:14px;border-top:1px solid #eceeea}.campaign-avatar{width:40px;height:40px;display:grid;place-items:center;flex:0 0 40px;border-radius:11px;color:#fff;font-size:.72rem;font-weight:900}.campaign-avatar.is-teal{background:#117e8c}.campaign-avatar.is-indigo{background:#4f46e5}.campaign-avatar.is-purple{background:#5b2b76}.campaign-person>div{min-width:0}.campaign-person strong,.campaign-person small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.campaign-person strong{color:#30352e;font-size:.73rem;font-weight:900}.campaign-person small{margin-top:2px;color:#858d82;font-size:.59rem;font-weight:600}.campaign-team .campaign-person small{margin:0 0 2px;font-size:.56rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}
    .campaign-company-row{margin-top:15px;padding-top:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;border-top:1px solid #eceeea}.campaign-company-row>div{display:flex;align-items:center;gap:7px;color:#477323;font-size:.61rem;font-weight:800}.campaign-company-row a{color:#4f46e5;font-size:.6rem;font-weight:900;text-decoration:none}.campaign-company-row a i{margin-left:3px}.campaign-empty-note{margin-top:15px;padding:10px;border:1px solid #f4dcaa;border-radius:10px;background:#fff8e8;color:#9a6512;font-size:.61rem;font-weight:800}
    .campaign-analytics-card{display:grid;grid-template-columns:42px minmax(0,1fr) 34px;align-items:center;gap:12px;padding:16px;border:1px solid #d9d0df;border-radius:16px;background:linear-gradient(135deg,#fff 40%,#f3edf6);box-shadow:0 9px 22px rgba(91,43,118,.08)}.campaign-analytics-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#5b2b76;color:#fff}.campaign-analytics-card span,.campaign-analytics-card strong,.campaign-analytics-card small{display:block}.campaign-analytics-card span{color:#8c6c9d;font-size:.56rem;font-weight:900;text-transform:uppercase}.campaign-analytics-card strong{margin-top:2px;color:#35253d;font-size:.74rem;font-weight:900}.campaign-analytics-card small{margin-top:3px;color:#807485;font-size:.56rem}.campaign-analytics-card>a{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#fff;color:#5b2b76;box-shadow:0 4px 10px rgba(91,43,118,.12);text-decoration:none;transition:.18s}.campaign-analytics-card>a:hover{transform:translateX(2px);background:#5b2b76;color:#fff}
    .campaign-tasks>.mt-0.m-8{margin:22px 24px 0!important}.campaign-tasks section{border:1px solid #e1e3de!important;border-radius:16px!important;box-shadow:0 9px 22px rgba(55,60,52,.06)!important}.campaign-tasks section>div:first-child{background:#117e8c!important}.campaign-tasks section>div:first-child>div:first-child h2{font-size:1.35rem!important}.campaign-tasks section>div:first-child>div:first-child p{font-size:.72rem!important}.campaign-tasks section>div:first-child .rounded-2xl{border-radius:12px!important}

    /* Espacio de trabajo simplificado */
    .campaign-detail-content,.campaign-tasks{width:min(1280px,calc(100% - 48px));margin-right:auto;margin-left:auto}
    .campaign-detail-content{padding:24px 0 0}
    .campaign-detail-hero-content{padding-left:max(48px,calc((100% - 1280px)/2));}
    .campaign-detail-heading{gap:0}
    .campaign-status>span{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.campaign-status.is-active>span{background:#bef264}.campaign-status.is-paused>span{background:#fde047}
    .campaign-detail-kpis{gap:0;margin-bottom:18px;overflow:hidden;border:1px solid #e2e5df;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(55,60,52,.04)}
    .campaign-detail-kpi{min-height:88px;padding:16px 20px;border:0;border-right:1px solid #e8ebe5;border-radius:0;background:#fff;box-shadow:inset 0 3px 0 var(--accent)}
    .campaign-detail-kpi:last-child{border-right:0}.campaign-detail-kpi:after{display:none}.campaign-detail-kpi strong{margin-top:5px;font-size:1.12rem}.campaign-detail-kpi small{margin-top:4px}
    .campaign-detail-grid{gap:16px}.campaign-detail-sidebar{gap:16px}
    .campaign-detail-card{border-radius:12px;box-shadow:0 5px 15px rgba(55,60,52,.04)}
    .campaign-card-header,.campaign-card-header.compact{padding:16px 18px;gap:0}.campaign-card-body{padding:18px}.campaign-card-body.compact{padding:16px 18px}
    .campaign-underlined-title{margin:0;color:#302832;font-size:1rem;font-weight:900;letter-spacing:-.02em}.campaign-underlined-title:after,.campaign-card-header h2:after,.campaign-analytics-card strong:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}
    .campaign-description-block{margin-top:0;padding-top:0;border-top:0}.campaign-description-block p{margin-top:12px}
    .campaign-schedule{gap:0;border-top:1px solid #eceeea;border-bottom:1px solid #eceeea}.campaign-date,.campaign-date.is-end{padding:14px 0;border:0;border-radius:0;background:#fff}.campaign-date+.campaign-date{padding-left:18px;border-left:1px solid #eceeea}
    .campaign-analytics-card{grid-template-columns:minmax(0,1fr) auto;border-radius:12px;background:#faf8fb;box-shadow:none}.campaign-analytics-card>a{width:auto;height:auto;padding:8px 10px;border:1px solid #ddd3e2;border-radius:8px;box-shadow:none;font-size:.58rem;font-weight:900;white-space:nowrap}
    .campaign-tasks{margin-top:16px}.campaign-tasks .tasks-workspace{border-radius:12px!important;box-shadow:0 5px 15px rgba(55,60,52,.04)!important}
    @media(max-width:1080px){.campaign-detail-hero-content{padding-right:430px}.campaign-detail-grid{grid-template-columns:1fr}.campaign-detail-sidebar{grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-analytics-card{grid-column:1/-1}}
    @media(max-width:900px){.campaign-detail-actions{position:static;padding:14px 24px 0;justify-content:center}.campaign-detail-action{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.campaign-detail-action.is-primary{background:#4f46e5;color:#fff}.campaign-detail-hero{margin-top:14px}.campaign-detail-hero-content{padding:28px 24px}.campaign-status{flex:0 0 auto}.campaign-detail-kpis{grid-template-columns:1fr 1fr}.campaign-detail-kpi:last-child{grid-column:1/-1;border-top:1px solid #e8ebe5}.campaign-detail-kpi:nth-child(2){border-right:0}.campaign-detail-content,.campaign-tasks{width:calc(100% - 32px)}}
    @media(max-width:640px){.campaign-detail-page{padding-bottom:24px}.campaign-detail-actions{display:grid;grid-template-columns:1fr;padding:12px}.campaign-detail-action{width:100%}.campaign-detail-hero{min-height:200px;margin-top:0}.campaign-detail-hero-content{min-height:200px;align-items:flex-start;flex-direction:column;justify-content:center;padding:28px 20px}.campaign-detail-heading{align-items:flex-start}.campaign-detail-heading h1{white-space:normal}.campaign-detail-content,.campaign-tasks{width:calc(100% - 24px)}.campaign-detail-content{padding-top:14px}.campaign-detail-kpis{grid-template-columns:1fr}.campaign-detail-kpi,.campaign-detail-kpi:nth-child(2){border-right:0;border-bottom:1px solid #e8ebe5}.campaign-detail-kpi:last-child{grid-column:auto;border-top:0;border-bottom:0}.campaign-detail-sidebar{grid-template-columns:1fr}.campaign-analytics-card{grid-column:auto}.campaign-schedule{grid-template-columns:1fr}.campaign-date+.campaign-date{padding-left:0;border-top:1px solid #eceeea;border-left:0}.campaign-card-body{padding:18px}.campaign-company-row{align-items:flex-start;flex-direction:column}}
</style>
@endsection
