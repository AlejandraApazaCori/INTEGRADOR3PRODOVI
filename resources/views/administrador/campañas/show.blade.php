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
    $empresa = $empresa ?? $cliente?->empresas?->first();
    $creador = $campania->creador;
    $communityManager = $campania->communityManager;
    $disenador = $campania->disenador;
    $disenadoresCampania = $campania->disenadores->isNotEmpty()
        ? $campania->disenadores
        : ($disenador ? collect([$disenador]) : collect());
    $estadoClase = match(true) {
        $campania->es_borrador => 'is-draft',
        $campania->estado === 'activa' => 'is-active',
        $campania->estado === 'pausada' => 'is-paused',
        default => 'is-finished',
    };
@endphp

<div class="campaign-detail-page">
    <div class="campaign-detail-shell">
        <header class="campaign-detail-hero rp-banner">
            <div class="campaign-detail-overlay"></div>
            <div class="campaign-detail-hero-content">
                <div class="campaign-detail-heading">
                    <div>
                        <div class="campaign-hero-context">
                            <a href="{{ route('administrador.campañas.index') }}" class="campaign-hero-back"><i class="fas fa-arrow-left"></i> Ver todas las campañas</a>
                            <span class="campaign-status {{ $estadoClase }}"><span></span>{{ $campania->es_borrador ? 'Borrador IA' : ucfirst($campania->estado) }}</span>
                            <a href="{{ route('administrador.campañas.edit', $campania->id) }}" class="campaign-hero-edit"><i class="fas fa-pen"></i> Editar</a>
                            @if($campania->es_borrador)
                                <form action="{{ route('administrador.campañas.aprobar-borrador', $campania) }}" method="POST" onsubmit="return confirm('¿Revisaste la estrategia, el equipo y todas las tareas? Al activar comenzará la vigencia del servicio.');">
                                    @csrf @method('PATCH')
                                    <button class="campaign-hero-approve" type="submit"><i class="fas fa-circle-check"></i> Aprobar y activar</button>
                                </form>
                            @endif
                        </div>
                        <h1>Campaña {{ $empresa?->nombre_empresa ?? $cliente?->name ?? 'Sin empresa asignada' }}</h1>
                        <p>Información, equipo, cronograma y tareas de la campaña</p>
                    </div>
                </div>
                <section class="campaign-hero-summary" aria-label="Resumen temporal de la campaña">
                    <div class="campaign-hero-metric">
                        <small>Duración</small>
                        <strong>{{ $duracion }} <span>días</span></strong>
                        <p>Periodo planificado</p>
                    </div>
                    <div class="campaign-hero-metric">
                        <small>Fecha de cierre</small>
                        <strong>{{ $fechaFin->format('d/m/Y') }}</strong>
                        <p>{{ $diasRestantes >= 0 ? ($diasRestantes === 0 ? 'Finaliza hoy' : $diasRestantes.' días restantes') : 'Campaña finalizada' }}</p>
                    </div>
                    <div class="campaign-hero-progress-row">
                        <div class="campaign-hero-progress" role="progressbar" aria-label="Progreso temporal" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $avance }}"><span style="width:{{ $avance }}%"></span></div>
                        <small>{{ $avance }}%</small>
                    </div>
                </section>
            </div>
        </header>

        <nav class="campaign-subtabs" aria-label="Secciones de la campaña" role="tablist">
            <button type="button" class="campaign-subtab is-active" style="--tab-color:#5b2b76" id="campaign-tab-summary" data-campaign-tab="summary" role="tab" aria-controls="campaign-panel-summary" aria-selected="true">
                <span><i class="fas fa-chart-pie"></i></span>Resumen
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#c94f0c" id="campaign-tab-documents" data-campaign-tab="documents" role="tab" aria-controls="campaign-panel-documents" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-folder-open"></i></span>Documentos
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#ef6c22" id="campaign-tab-calendar" data-campaign-tab="calendar" role="tab" aria-controls="campaign-panel-calendar" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-calendar-days"></i></span>Calendario
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#7da533" id="campaign-tab-tasks" data-campaign-tab="tasks" role="tab" aria-controls="campaign-panel-tasks" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-list-check"></i></span>Tareas
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#117e8c" id="campaign-tab-feedback" data-campaign-tab="feedback" role="tab" aria-controls="campaign-panel-feedback" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-comments"></i></span>Feedback
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#5b2b76" id="campaign-tab-resources" data-campaign-tab="resources" role="tab" aria-controls="campaign-panel-resources" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-box-archive"></i></span>Recursos
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#5b2b76" id="campaign-tab-meetings" data-campaign-tab="meetings" role="tab" aria-controls="campaign-panel-meetings" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-users"></i></span>Reuniones
            </button>
            <button type="button" class="campaign-subtab" style="--tab-color:#c94f0c" id="campaign-tab-analytics" data-campaign-tab="analytics" role="tab" aria-controls="campaign-panel-analytics" aria-selected="false" tabindex="-1">
                <span><i class="fas fa-chart-line"></i></span>Analíticas
            </button>
        </nav>

        @if(session('success'))
            <div class="campaign-detail-alert is-success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="campaign-detail-alert is-error"><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</div>
        @endif

        <div class="campaign-documents campaign-tab-panel" id="campaign-panel-documents" data-campaign-panel="documents" role="tabpanel" aria-labelledby="campaign-tab-documents" hidden>
            @include('administrador.campañas.partials.documentos', ['empresa' => $empresa])
        </div>

        <main class="campaign-detail-content campaign-tab-panel" id="campaign-panel-summary" data-campaign-panel="summary" role="tabpanel" aria-labelledby="campaign-tab-summary">
            <div class="campaign-detail-grid">
                <section class="campaign-detail-card campaign-overview-card">
                    <div class="campaign-card-body">
                        <div class="campaign-description-block">
                            <h2 class="campaign-underlined-title">Descripción</h2>
                            <p>{{ $campania->descripcion ?: 'Sin descripción registrada para esta campaña.' }}</p>
                        </div>
                        @if($campania->objetivo_general || $campania->mensaje_principal || $campania->tono_comunicacion)
                            <div class="campaign-strategy-grid">
                                @if($campania->objetivo_general)<article><small>Objetivo general</small><p>{{ $campania->objetivo_general }}</p></article>@endif
                                @if($campania->mensaje_principal)<article><small>Mensaje principal</small><p>{{ $campania->mensaje_principal }}</p></article>@endif
                                @if($campania->tono_comunicacion)<article><small>Tono</small><p>{{ $campania->tono_comunicacion }}</p></article>@endif
                            </div>
                        @endif
                        @if($publicosObjetivo !== [])
                            <section class="campaign-audiences" aria-labelledby="campaign-audiences-title">
                                <header>
                                    <h3 id="campaign-audiences-title" class="campaign-underlined-title">Público objetivo</h3>
                                    <span>{{ count($publicosObjetivo) }} {{ count($publicosObjetivo) === 1 ? 'segmento' : 'segmentos' }}</span>
                                </header>
                                <div class="campaign-audience-list">
                                    @foreach($publicosObjetivo as $publico)
                                        <article class="campaign-audience-row">
                                            <span class="campaign-audience-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            <div>
                                                <small>Tipo de público y edades</small>
                                                <strong>{{ $publico['tipo_edades'] }}</strong>
                                            </div>
                                            <div>
                                                <small>Descripción esencial</small>
                                                <p>{{ $publico['descripcion'] ?: 'Sin descripción adicional registrada.' }}</p>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if($campania->canales || $campania->indicadores)
                            <div class="campaign-strategy-tags">
                                @foreach($campania->canales ?? [] as $canal)<span><i class="fas fa-share-nodes"></i>{{ $canal }}</span>@endforeach
                                @foreach($campania->indicadores ?? [] as $indicador)<span class="is-kpi"><i class="fas fa-chart-line"></i>{{ $indicador }}</span>@endforeach
                            </div>
                        @endif
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

                    <section class="campaign-detail-card campaign-drive-card" aria-labelledby="campaign-drive-title">
                        <div class="campaign-card-header compact">
                            <div><span>Archivos de la campaña</span><h2 id="campaign-drive-title">Carpeta en Drive</h2></div>
                        </div>
                        <div class="campaign-card-body compact">
                            <p>Accede a los entregables de {{ $campania->nombre }} organizados por tarea.</p>
                            @if($cliente)
                                <form action="{{ route('administrador.campañas.drive', $campania) }}" method="POST" target="_blank">
                                    @csrf
                                    <button type="submit"><i class="fas fa-arrow-up-right-from-square"></i> Abrir carpeta en Drive</button>
                                </form>
                            @else
                                <div class="campaign-empty-note">Asigna un cliente para acceder a la carpeta.</div>
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
                            @forelse($disenadoresCampania as $miembroDiseno)
                                <div class="campaign-person">
                                    <span class="campaign-avatar is-teal">{{ strtoupper(substr($miembroDiseno->name, 0, 2)) }}</span>
                                    <div><small>{{ $loop->first ? 'Diseñador principal' : 'Diseñador de apoyo' }}</small><strong>{{ $miembroDiseno->name }}</strong></div>
                                </div>
                            @empty
                                <div class="campaign-person">
                                    <span class="campaign-avatar is-teal">SD</span>
                                    <div><small>Equipo de diseño</small><strong>Sin asignar</strong></div>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="campaign-analytics-card">
                        <div><span>Rendimiento</span><strong>Analíticas de campaña</strong><small>Consulta los resultados y métricas del cliente.</small></div>
                        <a href="{{ route('administrador.campañas.show', $campania) }}#analiticas">Ver analíticas <i class="fas fa-arrow-right"></i></a>
                    </section>
                </aside>
            </div>
        </main>

        <div class="campaign-calendar campaign-tab-panel" id="campaign-panel-calendar" data-campaign-panel="calendar" role="tabpanel" aria-labelledby="campaign-tab-calendar" hidden>
            @include('administrador.campañas.partials.calendario-gantt')
        </div>

        <div class="campaign-tasks campaign-tab-panel" id="campaign-panel-tasks" data-campaign-panel="tasks" role="tabpanel" aria-labelledby="campaign-tab-tasks" hidden>
            @include('administrador.tareas.index')
        </div>

        <div class="campaign-feedback campaign-tab-panel" id="campaign-panel-feedback" data-campaign-panel="feedback" role="tabpanel" aria-labelledby="campaign-tab-feedback" hidden>
            @include('campanias.feedback.workspace', ['feedbackClientMode' => false])
        </div>

        <div class="campaign-resources campaign-tab-panel" id="campaign-panel-resources" data-campaign-panel="resources" role="tabpanel" aria-labelledby="campaign-tab-resources" hidden>
            @include('administrador.campañas.partials.recursos')
        </div>

        <div class="campaign-meetings campaign-tab-panel" id="campaign-panel-meetings" data-campaign-panel="meetings" role="tabpanel" aria-labelledby="campaign-tab-meetings" hidden>
            @include('administrador.campañas.partials.reuniones')
        </div>

        <div class="campaign-analytics campaign-tab-panel" id="campaign-panel-analytics" data-campaign-panel="analytics" role="tabpanel" aria-labelledby="campaign-tab-analytics" hidden>
            @include('administrador.campañas.partials.conexion-redes')
            @include('administrador.campañas.partials.reportes-analiticas')
            @include('administrador.analiticas.analiticasporcuentas', [
                'campania' => $campania,
                'defaultAnalyticsDays' => 'all',
                'analyticsEmptyDetail' => 'Esta empresa todavía no tiene cuentas conectadas. Puedes vincularlas desde las opciones superiores.',
            ])
        </div>
    </div>
</div>

@push('styles')
<style>
    .rp-banner{background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}
    .campaign-detail-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}
    .campaign-detail-shell{position:relative;width:100%}
    .campaign-detail-hero{position:relative;min-height:180px;overflow:hidden}
    .campaign-detail-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%)}
    .campaign-detail-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:34px;padding:30px max(48px,calc((100% - 1280px)/2))}
    .campaign-detail-heading{min-width:0;display:flex;flex:1 1 auto;align-items:center;gap:16px}
    .campaign-detail-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 52px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.25rem;backdrop-filter:blur(5px)}
    .campaign-detail-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}
    .campaign-detail-heading h1{max-width:700px;margin:0 0 4px;overflow:hidden;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;text-overflow:ellipsis;white-space:nowrap}
    .campaign-detail-heading p{margin:0;color:#f0fdf4;font-size:.74rem;font-weight:600}
    .campaign-hero-context{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:13px}.campaign-hero-context form{margin:0}
    .campaign-hero-back,.campaign-hero-edit,.campaign-hero-approve{min-height:34px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:7px 10px;border:1px solid rgba(255,255,255,.35);border-radius:8px;background:rgba(255,255,255,.13);color:#fff;font-size:.62rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}.campaign-hero-back:hover,.campaign-hero-edit:hover{transform:translateY(-1px);border-color:#fff;background:#fff;color:#638522}.campaign-hero-approve{border-color:#fff;background:#fff;color:#638522;cursor:pointer}
    .campaign-status{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.45);border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:.66rem;font-weight:900;text-transform:uppercase;backdrop-filter:blur(5px)}
    .campaign-status i{font-size:.44rem}.campaign-status.is-active i{color:#bef264}.campaign-status.is-paused i{color:#fde047}.campaign-status.is-finished i{color:#cbd5e1}
    .campaign-detail-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:9px}
    .campaign-detail-action{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}
    .campaign-detail-action.is-primary,.campaign-detail-action:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#638522;box-shadow:0 8px 20px rgba(31,55,20,.18)}
    .campaign-detail-actions form{margin:0}.campaign-detail-action.is-approve{border:0;background:#a7b838;color:#fff;cursor:pointer}.campaign-status.is-draft{background:#fff;color:#5b2b76;border-color:#fff}.campaign-status.is-draft span{background:#f59e0b}
    .campaign-hero-summary{width:min(430px,42vw);display:grid;grid-template-columns:repeat(2,minmax(0,1fr));flex:0 0 auto;overflow:hidden;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.12);backdrop-filter:blur(7px)}
    .campaign-hero-metric{min-width:0;padding:15px 16px;border-right:1px solid rgba(255,255,255,.18)}.campaign-hero-metric:nth-child(2){border-right:0}.campaign-hero-metric small,.campaign-hero-metric strong,.campaign-hero-metric p{display:block}.campaign-hero-metric small{color:#e7f5cf;font-size:.55rem;font-weight:900;letter-spacing:.07em;text-transform:uppercase}.campaign-hero-metric strong{margin-top:5px;color:#fff;font-size:1rem;font-weight:900;line-height:1.1;white-space:nowrap}.campaign-hero-metric strong span{font-size:.62rem}.campaign-hero-metric p{margin:5px 0 0;color:#f0fdf4;font-size:.54rem;font-weight:650;white-space:nowrap}.campaign-hero-progress-row{grid-column:1/-1;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:9px;padding:10px 16px;border-top:1px solid rgba(255,255,255,.18)}.campaign-hero-progress-row small{color:#fff;font-size:.56rem;font-weight:900}.campaign-hero-progress{height:5px;overflow:hidden;border-radius:999px;background:rgba(255,255,255,.22)}.campaign-hero-progress span{display:block;height:100%;border-radius:inherit;background:#fff}
    .campaign-subtabs{width:100%;display:grid;grid-template-columns:repeat(8,minmax(0,1fr));margin:0;padding:0;border-bottom:1px solid #e5e1e7;background:#fff;box-shadow:0 4px 12px rgba(48,40,52,.06)}
    .campaign-subtab{--tab-color:#5b2b76;position:relative;min-width:0;min-height:56px;display:flex;align-items:center;justify-content:center;gap:8px;padding:0 14px;border:0;border-right:1px solid #efedf0;background:#fff;color:#716a75;font-size:.69rem;font-weight:850;opacity:1;cursor:pointer}
    .campaign-subtab:disabled{opacity:.62;cursor:not-allowed}
    .campaign-subtab:last-child{border-right:0}.campaign-subtab>span{width:24px;height:24px;display:grid;place-items:center;flex:0 0 24px;border-radius:6px;background:var(--tab-color);color:#fff;font-size:.66rem}.campaign-subtab:after{content:'';position:absolute;right:0;bottom:-1px;left:0;height:3px;background:var(--tab-color);opacity:0}.campaign-subtab.is-active{color:#302834}.campaign-subtab.is-active:after{opacity:1}
    .campaign-detail-alert{width:calc(100% - 48px);margin:24px auto 0;padding:13px 16px;display:flex;align-items:center;gap:10px;border:1px solid;border-radius:12px;font-size:.76rem;font-weight:800}.campaign-detail-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.campaign-detail-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .campaign-tab-panel[hidden]{display:none!important}
    .campaign-detail-content{padding:24px 24px 0}
    .campaign-detail-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:22px}
    .campaign-detail-kpi{--accent:#117e8c;--soft:#e6f4f5;--rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;min-height:126px;padding:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;border:1px solid rgba(var(--rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--soft));box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09)}
    .campaign-detail-kpi:after{content:'';position:absolute;z-index:-1;right:12px;bottom:7px;width:84px;height:43px;opacity:.2;background-image:radial-gradient(circle,var(--accent) 1.4px,transparent 1.6px);background-size:9px 9px}
    .campaign-detail-kpi span,.campaign-detail-kpi small{display:block}.campaign-detail-kpi span{color:#596170;font-size:.67rem;font-weight:900;letter-spacing:.03em;text-transform:uppercase}.campaign-detail-kpi strong{display:block;margin-top:8px;color:#263024;font-size:1.55rem;font-weight:900;line-height:1.1}.campaign-detail-kpi small{margin-top:7px;color:#7f8878;font-size:.62rem;font-weight:600}
    .campaign-detail-kpi>i{width:50px;height:50px;display:grid;place-items:center;flex:0 0 50px;border-radius:14px;background:var(--accent);color:#fff;font-size:1.1rem;box-shadow:0 8px 17px rgba(var(--rgb),.27)}
    .kpi-calendar{--accent:#7da533;--soft:#f0f6e7;--rgb:125,165,51}.kpi-progress{--accent:#117e8c;--soft:#e6f4f5;--rgb:17,126,140}.kpi-deadline{--accent:#5b2b76;--soft:#f3edf6;--rgb:91,43,118}
    .campaign-detail-grid{display:grid;grid-template-columns:minmax(0,1.75fr) minmax(300px,.85fr);gap:18px}
    .campaign-strategy-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:18px 0}.campaign-strategy-grid article{padding:14px;border:1px solid #e3e6e1;border-radius:12px;background:#fafbf9}.campaign-strategy-grid small{display:block;margin-bottom:6px;color:#66705f;font-size:.61rem;font-weight:900;text-transform:uppercase}.campaign-strategy-grid p{margin:0!important;color:#394136!important;font-size:.72rem!important}.campaign-strategy-tags{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:18px}.campaign-strategy-tags span{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:.6rem;font-weight:800}.campaign-strategy-tags span.is-kpi{background:#ecfdf5;color:#047857}
    .campaign-audiences{margin:0 0 18px;padding:14px;border:1px solid #e3e6e1;border-radius:12px;background:#fafbf9}.campaign-audiences>header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:13px}.campaign-audiences>header>span{flex:0 0 auto;padding:7px 10px;border-radius:999px;background:#f3edf6;color:#5b2b76;font-size:.56rem;font-weight:900}.campaign-audience-list{display:grid;gap:9px}.campaign-audience-row{display:grid;grid-template-columns:34px minmax(180px,.72fr) minmax(240px,1.28fr);align-items:start;gap:12px;padding:13px;border:1px solid #e3e6e1;border-radius:11px;background:#fff}.campaign-audience-number{width:30px;height:30px;display:grid;place-items:center;border-radius:9px;background:#5b2b76;color:#fff;font-size:.56rem;font-weight:900}.campaign-audience-row>div{min-width:0}.campaign-audience-row small,.campaign-audience-row strong{display:block}.campaign-audience-row small{margin-bottom:5px;color:#858d99;font-size:.53rem;font-weight:900;text-transform:uppercase}.campaign-audience-row strong{color:#374151;font-size:.69rem;font-weight:900;line-height:1.45}.campaign-audience-row p{margin:0!important;color:#646d7a!important;font-size:.66rem!important;line-height:1.55}
    .campaign-detail-sidebar{display:grid;align-content:start;gap:18px}
    .campaign-detail-card{overflow:hidden;border:1px solid #e1e3de;border-radius:16px;background:#fff;box-shadow:0 9px 22px rgba(55,60,52,.06)}
    .campaign-drive-icon{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:10px;background:#e9f4e9;color:#188038;font-size:1rem}.campaign-drive-card .campaign-card-body p{margin:0 0 14px;color:#66705f;font-size:.7rem;line-height:1.5}.campaign-drive-card form{margin:0}.campaign-drive-card button{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 13px;border:0;border-radius:9px;background:#188038;color:#fff;font-size:.68rem;font-weight:900;cursor:pointer;transition:background .18s}.campaign-drive-card button:hover{background:#12642b}
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
    .campaign-detail-content,.campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-resources,.campaign-analytics{width:min(1280px,calc(100% - 48px));margin-right:auto;margin-left:auto}
    .campaign-detail-content{padding:24px 0 0}
    .campaign-detail-heading{gap:0}
    .campaign-status>span{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.campaign-status.is-active>span{background:#bef264}.campaign-status.is-paused>span{background:#fde047}
    .campaign-detail-kpis{gap:0;margin-bottom:18px;overflow:hidden;border:1px solid #e2e5df;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(55,60,52,.04)}
    .campaign-detail-kpi{min-height:88px;padding:16px 20px;border:0;border-right:1px solid #e8ebe5;border-radius:0;background:#fff;box-shadow:inset 0 3px 0 var(--accent)}
    .campaign-detail-kpi:last-child{border-right:0}.campaign-detail-kpi:after{display:none}.campaign-detail-kpi strong{margin-top:5px;font-size:1.12rem}.campaign-detail-kpi small{margin-top:4px}
    .campaign-detail-grid{gap:16px}.campaign-detail-sidebar{gap:16px}
    .campaign-detail-card{border-radius:12px;box-shadow:0 5px 15px rgba(55,60,52,.04)}
    .campaign-card-header,.campaign-card-header.compact{padding:16px 18px;gap:0}.campaign-card-body{padding:18px}.campaign-card-body.compact{padding:16px 18px}
    .campaign-underlined-title{margin:0;color:#302832;font-size:1rem;font-weight:900;letter-spacing:-.02em}.campaign-underlined-title:after,.campaign-card-header h2:after,.campaign-analytics-card strong:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#5b2b76}
    .campaign-description-block{margin-top:0;padding-top:0;border-top:0}.campaign-description-block p{margin-top:12px}
    .campaign-schedule{gap:0;border-top:1px solid #eceeea;border-bottom:1px solid #eceeea}.campaign-date,.campaign-date.is-end{padding:14px 0;border:0;border-radius:0;background:#fff}.campaign-date+.campaign-date{padding-left:18px;border-left:1px solid #eceeea}
    .campaign-analytics-card{grid-template-columns:minmax(0,1fr) auto;border-radius:12px;background:#faf8fb;box-shadow:none}.campaign-analytics-card>a{width:auto;height:auto;padding:8px 10px;border:1px solid #ddd3e2;border-radius:8px;box-shadow:none;font-size:.58rem;font-weight:900;white-space:nowrap}
    .campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-resources,.campaign-meetings,.campaign-analytics{margin-top:16px}.campaign-tasks .tasks-workspace{border-radius:12px!important;box-shadow:0 5px 15px rgba(55,60,52,.04)!important}
    @media(max-width:1080px){.campaign-detail-hero-content{align-items:stretch;flex-direction:column;padding:28px 32px}.campaign-hero-summary{width:100%}.campaign-detail-grid{grid-template-columns:1fr}.campaign-detail-sidebar{grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-drive-card{grid-column:1}.campaign-analytics-card{grid-column:1/-1}}
    @media(max-width:900px){.campaign-detail-hero{margin-top:0}.campaign-detail-hero-content{padding:28px 24px}.campaign-status{flex:0 0 auto}.campaign-detail-content,.campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-resources,.campaign-meetings,.campaign-analytics{width:calc(100% - 32px)}}
    @media(max-width:760px){.campaign-subtabs{display:flex;overflow-x:auto}.campaign-subtab{min-width:145px;flex:1 0 145px}}
    @media(max-width:760px){.campaign-audience-row{grid-template-columns:34px minmax(0,1fr)}.campaign-audience-row>div:last-child{grid-column:2}.campaign-audiences>header{flex-direction:column}.campaign-strategy-grid{grid-template-columns:1fr}}
    @media(max-width:640px){.campaign-detail-page{padding-bottom:24px}.campaign-detail-hero{min-height:200px;margin-top:0}.campaign-detail-hero-content{min-height:200px;align-items:stretch;flex-direction:column;justify-content:center;padding:26px 20px}.campaign-detail-heading{align-items:flex-start}.campaign-detail-heading h1{white-space:normal}.campaign-hero-context{align-items:stretch}.campaign-hero-back{width:100%}.campaign-hero-summary{grid-template-columns:1fr}.campaign-hero-metric{padding:12px 14px;border-right:0;border-bottom:1px solid rgba(255,255,255,.18)}.campaign-hero-metric:nth-child(2){border-bottom:0}.campaign-hero-progress-row{grid-column:1;padding:10px 14px}.campaign-detail-content,.campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-resources,.campaign-meetings,.campaign-analytics{width:calc(100% - 24px)}.campaign-detail-content{padding-top:14px}.campaign-detail-sidebar{grid-template-columns:1fr}.campaign-drive-card,.campaign-analytics-card{grid-column:auto}.campaign-schedule{grid-template-columns:1fr}.campaign-date+.campaign-date{padding-left:0;border-top:1px solid #eceeea;border-left:0}.campaign-card-body{padding:18px}.campaign-company-row{align-items:flex-start;flex-direction:column}}
    /* Modo oscuro del detalle; el banner conserva su diseño original. */
    html.admin-dark-nav .campaign-detail-page{background:#141414;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .campaign-subtabs,
    html.admin-dark-nav .campaign-detail-page .campaign-subtab,
    html.admin-dark-nav .campaign-detail-page .campaign-detail-kpis,
    html.admin-dark-nav .campaign-detail-page .campaign-detail-kpi,
    html.admin-dark-nav .campaign-detail-page .campaign-detail-card,
    html.admin-dark-nav .campaign-detail-page .campaign-analytics-card{background:#202020;border-color:#353535;box-shadow:none;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .campaign-subtab.is-active{background:#292929;color:#fff}
    html.admin-dark-nav .campaign-detail-page .campaign-detail-kpi span,
    html.admin-dark-nav .campaign-detail-page .campaign-detail-kpi small,
    html.admin-dark-nav .campaign-detail-page .campaign-card-header span,
    html.admin-dark-nav .campaign-detail-page .campaign-name-block small,
    html.admin-dark-nav .campaign-detail-page .campaign-description-block small,
    html.admin-dark-nav .campaign-detail-page .campaign-description-block p,
    html.admin-dark-nav .campaign-detail-page .campaign-person small,
    html.admin-dark-nav .campaign-detail-page .campaign-analytics-card span,
    html.admin-dark-nav .campaign-detail-page .campaign-analytics-card small{color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .campaign-detail-kpi strong,
    html.admin-dark-nav .campaign-detail-page .campaign-card-header h2,
    html.admin-dark-nav .campaign-detail-page .campaign-underlined-title,
    html.admin-dark-nav .campaign-detail-page .campaign-name-block h3,
    html.admin-dark-nav .campaign-detail-page .campaign-person strong,
    html.admin-dark-nav .campaign-detail-page .campaign-analytics-card strong{color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page .campaign-card-header,
    html.admin-dark-nav .campaign-detail-page .campaign-description-block,
    html.admin-dark-nav .campaign-detail-page .campaign-schedule,
    html.admin-dark-nav .campaign-detail-page .campaign-date+.campaign-date,
    html.admin-dark-nav .campaign-detail-page .campaign-person+.campaign-person,
    html.admin-dark-nav .campaign-detail-page .campaign-company-row{border-color:#353535}
    html.admin-dark-nav .campaign-detail-page .campaign-drive-card .campaign-card-body p{color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .campaign-strategy-grid article,
    html.admin-dark-nav .campaign-detail-page .campaign-audiences,
    html.admin-dark-nav .campaign-detail-page .campaign-audience-row,
    html.admin-dark-nav .campaign-detail-page .campaign-date,
    html.admin-dark-nav .campaign-detail-page .campaign-progress,
    html.admin-dark-nav .campaign-detail-page .campaign-analytics-card>a{background:#292929;border-color:#444;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .campaign-strategy-grid small,
    html.admin-dark-nav .campaign-detail-page .campaign-audience-row small,
    html.admin-dark-nav .campaign-detail-page .campaign-date small,
    html.admin-dark-nav .campaign-detail-page .campaign-progress>small{color:#aaa}
    html.admin-dark-nav .campaign-detail-page .campaign-strategy-grid p,
    html.admin-dark-nav .campaign-detail-page .campaign-audience-row p{color:#ccc!important}
    html.admin-dark-nav .campaign-detail-page .campaign-audience-row strong,
    html.admin-dark-nav .campaign-detail-page .campaign-date strong,
    html.admin-dark-nav .campaign-detail-page .campaign-progress>div:first-child{color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page .campaign-progress-track{background:#444}
    html.admin-dark-nav .campaign-detail-page .campaign-strategy-tags span,
    html.admin-dark-nav .campaign-detail-page .campaign-audiences>header>span{background:#383838;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .campaign-strategy-tags span.is-kpi{background:#203b2d;color:#a7e8bc}
    html.admin-dark-nav .campaign-detail-page .campaign-document-card,
    html.admin-dark-nav .campaign-detail-page .campaign-documents-workspace,
    html.admin-dark-nav .campaign-detail-page .embedded-planning-workspace,
    html.admin-dark-nav .campaign-detail-page .tasks-workspace,
    html.admin-dark-nav .campaign-detail-page .resource-library,
    html.admin-dark-nav .campaign-detail-page .meetings-workspace,
    html.admin-dark-nav .campaign-detail-page .feedback-workspace,
    html.admin-dark-nav .campaign-detail-page .admin-social-connections,
    html.admin-dark-nav .campaign-detail-page .campaign-report-panel,
    html.admin-dark-nav .campaign-detail-page .meta-analytics-head,
    html.admin-dark-nav .campaign-detail-page .meta-analytics-tabs,
    html.admin-dark-nav .campaign-detail-page .meta-kpis,
    html.admin-dark-nav .campaign-detail-page .meta-card{background:#202020!important;border-color:#353535!important;color:#e5e5e5;box-shadow:none!important}
    html.admin-dark-nav .campaign-detail-page .campaign-plan-list>div,
    html.admin-dark-nav .campaign-detail-page .campaign-documents-empty,
    html.admin-dark-nav .campaign-detail-page .embedded-planning-switch,
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-toolbar,
    html.admin-dark-nav .campaign-detail-page .embedded-gantt-toolbar,
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-grid,
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-weekday,
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-day,
    html.admin-dark-nav .campaign-detail-page .embedded-gantt-board,
    html.admin-dark-nav .campaign-detail-page .embedded-gantt-cell,
    html.admin-dark-nav .campaign-detail-page .task-column,
    html.admin-dark-nav .campaign-detail-page .task-kanban-card,
    html.admin-dark-nav .campaign-detail-page .tasks-toolbar,
    html.admin-dark-nav .campaign-detail-page .tasks-table-filters,
    html.admin-dark-nav .campaign-detail-page .resource-library-content,
    html.admin-dark-nav .campaign-detail-page .resource-item,
    html.admin-dark-nav .campaign-detail-page .meeting-card,
    html.admin-dark-nav .campaign-detail-page .feedback-feed-card,
    html.admin-dark-nav .campaign-detail-page .feedback-composer-card,
    html.admin-dark-nav .campaign-detail-page .feedback-context-card,
    html.admin-dark-nav .campaign-detail-page .feedback-message-content,
    html.admin-dark-nav .campaign-detail-page .admin-social-option,
    html.admin-dark-nav .campaign-detail-page .campaign-report-grid>a,
    html.admin-dark-nav .campaign-detail-page .meta-account,
    html.admin-dark-nav .campaign-detail-page .meta-best-slot,
    html.admin-dark-nav .campaign-detail-page .meta-post{background:#292929!important;border-color:#444!important;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .tasks-table td{background:#202020;border-color:#353535;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .tasks-table tbody tr:nth-child(even) td{background:#292929}
    html.admin-dark-nav .campaign-detail-page .tasks-table tbody tr:hover td{background:#353535}
    html.admin-dark-nav .campaign-detail-page .task-title,
    html.admin-dark-nav .campaign-detail-page .task-card-top h4{color:#f5f5f5!important}
    html.admin-dark-nav .campaign-detail-page .task-column-header,
    html.admin-dark-nav .campaign-detail-page .embedded-planning-header,
    html.admin-dark-nav .campaign-detail-page .resource-library-bar,
    html.admin-dark-nav .campaign-detail-page .meetings-header,
    html.admin-dark-nav .campaign-detail-page .feedback-header{border-color:#353535}
    html.admin-dark-nav .campaign-detail-page :is(.campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-feedback,.campaign-resources,.campaign-meetings,.campaign-analytics) :is(h2,h3,h4,strong,label){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.campaign-documents,.campaign-calendar,.campaign-tasks,.campaign-feedback,.campaign-resources,.campaign-meetings,.campaign-analytics) :is(p,small){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page :is(input,textarea,select){background:#141414;border-color:#4a4a4a;color:#f5f5f5;color-scheme:dark}
    html.admin-dark-nav .campaign-detail-page :is(input,textarea)::placeholder{color:#999}
    html.admin-dark-nav .campaign-detail-page :is(.campaign-document-actions a,.campaign-document-actions button,.embedded-planning-switch button,.tasks-view-switch button,.tasks-table-filters button,.task-card-actions a,.task-card-actions button,.task-actions a,.task-actions button,.resource-layout-switch button,.resource-library-tabs button,.meeting-date-trigger,.feedback-dropdown-trigger,.meta-analytics-tabs button,.meta-period-trigger,.meta-period-menu button){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.task-status-dropdown-menu,.meeting-select-menu,.meeting-time-dropdown,.meeting-calendar-popover,.feedback-dropdown-menu,.meta-period-menu){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.task-upload-drawer-panel,.resource-upload-dialog,.resource-rename-dialog,.meeting-drawer,.feedback-delete-dialog,.feedback-context-dialog){background:#202020;border-color:#353535;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.task-upload-drawer-body,.task-upload-drawer-panel footer,.task-upload-context,.task-upload-dropzone,.task-upload-files-list,.resource-upload-body,.resource-rename-body,.meeting-drawer-body,.feedback-editor,.feedback-selected-context,.feedback-compose-mode){background:#292929;border-color:#444;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meta-analytics{color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meta-analytics-state{background:rgba(32,32,32,.95);border-color:#353535;color:#ccc}
    html.admin-dark-nav .campaign-detail-page .meta-no-data,
    html.admin-dark-nav .campaign-detail-page .meta-post-media{background:#292929;border-color:#444;color:#aaa}
    /* Calendario y Gantt */
    html.admin-dark-nav .campaign-detail-page :is(.embedded-planning-switch,.embedded-calendar-grid,.embedded-planning-legend,.embedded-gantt-row.is-header,.embedded-gantt-corner,.embedded-gantt-day,.embedded-calendar-weekday){background:#292929;border-color:#444;color:#ccc}
    html.admin-dark-nav .campaign-detail-page :is(.embedded-calendar-day,.embedded-gantt-board,.embedded-gantt-label,.embedded-gantt-cell){background:#202020!important;border-color:#353535;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.embedded-calendar-day.is-other,.embedded-gantt-cell.is-weekend){background:#252525!important;color:#aaa}
    html.admin-dark-nav .campaign-detail-page :is(.embedded-calendar-day.is-today,.embedded-gantt-cell.is-today){background:#382b22!important}
    html.admin-dark-nav .campaign-detail-page .embedded-gantt-row{border-color:#353535}
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-day-number,
    html.admin-dark-nav .campaign-detail-page .embedded-gantt-label strong,
    html.admin-dark-nav .campaign-detail-page :is(.embedded-calendar-toolbar,.embedded-gantt-toolbar) h3{color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.embedded-gantt-label span,.embedded-gantt-toolbar p,.embedded-gantt-toolbar>span,.embedded-planning-legend span,.embedded-calendar-more){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-event{background:color-mix(in srgb,var(--event-color) 24%,#202020);color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-event:hover{background:color-mix(in srgb,var(--event-color) 35%,#202020)}
    html.admin-dark-nav .campaign-detail-page .embedded-calendar-toolbar button{background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .embedded-planning-switch button.is-active{background:#444;color:#fff}
    /* Tareas: tablero, lista y controles */
    html.admin-dark-nav .campaign-detail-page :is(.tasks-board,.tasks-view-switch,.task-column-body){background:#202020;border-color:#353535}
    html.admin-dark-nav .campaign-detail-page :is(.task-column-header,.task-status-dropdown-control>[data-status-trigger],.task-status-dropdown-menu button){background:#292929;border-color:#444;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.task-column-header h3,.task-column-empty,.tasks-empty h3,.tasks-filter-empty strong){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.task-card-deliverable,.task-card-info>div,.task-card-status-select,.tasks-table td>small,.tasks-empty p){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page :is(.task-card-info,.task-column-empty){border-color:#444}
    html.admin-dark-nav .campaign-detail-page :is(.task-card-labels span,.task-meta-labels span,.task-column-header>span,.tasks-table-filters button>span){background:#383838;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .tasks-view-switch button.is-active{background:#444;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page .tasks-table-filters button.is-active{background:#7da533;border-color:#7da533;color:#fff}
    html.admin-dark-nav .campaign-detail-page .tasks-table-filters button.is-active>span{background:rgba(255,255,255,.22);color:#fff}
    html.admin-dark-nav .campaign-detail-page :is(.task-status-dropdown-menu button:hover,.task-status-dropdown-menu button.is-selected,.task-column-add:hover){background:#383838;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.task-upload-field-label,.task-upload-description>span,.task-upload-context strong,.task-upload-files-list>strong,.task-upload-dropzone strong){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.task-upload-files-list li,.task-upload-dropzone small,.task-upload-context small){color:#b8b8b8;border-color:#444}
    html.admin-dark-nav .campaign-detail-page .task-upload-drawer-panel footer button:not([type=submit]){background:#383838;border-color:#555;color:#e5e5e5}
    /* Feedback: conversación, editor y selectores */
    html.admin-dark-nav .campaign-detail-page :is(.feedback-layout,.feedback-main-filters,.feedback-contexts,.feedback-conversation-head,.feedback-message-panel-head,.feedback-editor-toolbar,.feedback-editor-hint,.feedback-image-previews,.feedback-privacy-note){background:#202020;border-color:#353535;color:#ccc}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-filters button,.feedback-dropdown-menu>button,.feedback-conversation-head>button,.feedback-message-panel-head button,.feedback-delete-dialog footer button:not(:last-child),.feedback-context-dialog footer button:not(:last-child)){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-filters button.is-active,.feedback-dropdown-menu>button.is-selected,.feedback-dropdown-menu>button:hover,.feedback-editor-toolbar button.is-active){background:#383838;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page .feedback-new-context .feedback-dropdown-trigger{background:#117e8c;border-color:#117e8c;color:#fff}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-message.is-own .feedback-message-content,.feedback-context-card.is-general){background:#303030!important;border-color:#4a4a4a!important}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-message-meta span,.feedback-message-meta a,.feedback-image-preview,.feedback-inline-image,.feedback-message-images a){background:#383838;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-editor-input,.feedback-editor-input strong,.feedback-editor-input b,.feedback-message-text,.feedback-message-text strong,.feedback-context-card strong,.feedback-contexts-heading h3,.feedback-conversation-head strong,.feedback-message-panel-head strong,.feedback-selected-context strong,.feedback-compose-mode strong,.feedback-context-browser-heading strong,.feedback-delete-dialog h3,.feedback-context-dialog h3){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-context-card em,.feedback-message-meta,.feedback-message-actions button,.feedback-compose-mode em,.feedback-delete-dialog p,.feedback-context-dialog>p,.feedback-editor-hint,.feedback-image-hint){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-editor-toolbar button,.feedback-context-dialog label>span){color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.feedback-delete-dialog footer button:last-child,.feedback-context-dialog footer button:last-child){color:#fff}
    /* Panel lateral de subida de recursos */
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dialog,.resource-rename-dialog){background:#202020;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dialog>header>span,.resource-rename-dialog>header>span){background:#353535;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-body,.resource-rename-body){background:#202020;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dropzone,.resource-upload-links>div,.resource-selected-files,.resource-upload-visibility,.resource-rename-body>div:not(.resource-upload-errors)){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dropzone:hover,.resource-upload-links>div:focus-within){background:#353535;border-color:#777}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dialog footer,.resource-rename-dialog footer){background:#202020;border-color:#353535}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dialog footer button:not([type=submit]),.resource-rename-dialog footer button:not([type=submit]),.resource-upload-links-head button,.resource-selected-files span){background:#353535;border-color:#555;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-field-label,.resource-upload-links-head label,.resource-upload-visibility strong,.resource-upload-dropzone strong,.resource-rename-body>label){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-dropzone small,.resource-upload-visibility small,.resource-upload-links-head label span,.resource-rename-body>small,.resource-upload-divider){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .resource-upload-divider:before,
    html.admin-dark-nav .campaign-detail-page .resource-upload-divider:after{background:#4a4a4a}
    html.admin-dark-nav .campaign-detail-page .resource-upload-visibility>span{background:#202020;border-color:#777}
    html.admin-dark-nav .campaign-detail-page .resource-upload-visibility>input:checked+span{background:#5b2b76;border-color:#5b2b76}
    html.admin-dark-nav .campaign-detail-page :is(.resource-upload-links input,.resource-rename-body input){background:transparent;border:0;color:#f5f5f5}
    /* Reuniones */
    html.admin-dark-nav .campaign-detail-page :is(.meeting-client-access,.meeting-quota-control,.meeting-drawer>header,.meeting-drawer footer,.meeting-calendar-popover,.meeting-select-menu,.meeting-time-dropdown>[data-time-menu]){background:#292929;border-color:#444;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.meeting-date,.meetings-empty>i,.meeting-person-avatar,.meeting-access-icon){background:#383838;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meeting-status{background:#263a29;color:#b6e3b5}
    html.admin-dark-nav .campaign-detail-page .meeting-people>span{background:#383838;border-color:#202020;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meeting-client-access form>button,
    html.admin-dark-nav .campaign-detail-page :is(.meeting-drawer>header button,.meeting-drawer footer button:not([type=submit]),.meeting-custom-select>button,.meeting-calendar-popover>header button,.meeting-calendar-days button,.meeting-time-dropdown>[data-time-trigger],.meeting-time-dropdown>[data-time-menu] button,.meeting-select-menu button){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meeting-participants>label{background:#292929;border-color:#444;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page .meeting-participants>label:has(input:checked){background:#35402f;border-color:#6a8c3e}
    html.admin-dark-nav .campaign-detail-page :is(.meeting-calendar-days button:hover,.meeting-select-menu button:hover,.meeting-select-menu button.is-selected,.meeting-time-dropdown>[data-time-menu] button:hover,.meeting-time-dropdown>[data-time-menu] button.is-selected){background:#383838;color:#fff}
    html.admin-dark-nav .campaign-detail-page .meeting-calendar-days button.is-selected{background:#5b2b76;color:#fff}
    html.admin-dark-nav .campaign-detail-page .meeting-calendar-days button.is-outside{color:#888}
    html.admin-dark-nav .campaign-detail-page .meeting-calendar-days button.is-today:not(.is-selected){color:#b6e3b5}
    html.admin-dark-nav .campaign-detail-page .meeting-quota-control input{background:transparent;border:0;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.meetings-header h2,.meeting-main h3,.meeting-client-access h3,.meeting-drawer>header h2,.meeting-calendar-popover>header strong,.meeting-participants strong){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.meetings-header p,.meeting-main small,.meeting-main>p,.meeting-client-access p,.meeting-client-access form>small,.meeting-quota-control span,.meeting-drawer legend small,.meeting-participant-heading,.meeting-participants small,.meeting-calendar-week span,.meeting-time-picker>span){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page :is(.meeting-client-access form>label,.meeting-drawer-body>label,.meeting-form-grid>label,.meeting-drawer legend){color:#e5e5e5}
    /* Conexiones y reportes */
    html.admin-dark-nav .campaign-detail-page :is(.admin-social-option.is-linked,.admin-social-option.is-disabled,.admin-social-option aside,.admin-social-status,.admin-social-notice,.admin-social-missing,.campaign-report-grid>a>span){background:#383838;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.admin-social-connections header h2,.admin-social-option-top strong,.campaign-report-panel>header h2,.campaign-report-grid strong){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.admin-social-connections header p,.admin-social-option>p,.admin-social-help,.campaign-report-panel>header p,.campaign-report-grid small){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page :is(.admin-social-option-top b,.admin-social-option.is-linked .admin-social-option-top b){background:#383838;color:#e5e5e5}
    /* Analíticas por cuenta */
    html.admin-dark-nav .campaign-detail-page .meta-analytics-tabs button{background:#202020;border-color:#353535;color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .meta-analytics-tabs button.is-active{background:#353535;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.meta-kpi,.meta-audience,.meta-rank>div,.meta-empty-inline){border-color:#444}
    html.admin-dark-nav .campaign-detail-page :is(.meta-period-trigger,.meta-period-menu,.meta-period-menu button){background:#292929;border-color:#4a4a4a;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.meta-period-menu button:hover,.meta-period-menu button:focus-visible,.meta-period-menu button[aria-selected="true"]){background:#383838;color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.meta-period-trigger>span,.meta-period-menu button>span,.meta-evidence){background:#383838;color:#e5e5e5}
    html.admin-dark-nav .campaign-detail-page :is(.meta-rank>div,.meta-api-notes){background:#292929;border-color:#4a4a4a}
    html.admin-dark-nav .campaign-detail-page :is(.meta-analytics-head h2,.meta-analytics-state strong,.meta-account strong,.meta-kpi strong,.meta-card>header h3,.meta-section-title h3,.meta-best-slot strong,.meta-rank strong){color:#f5f5f5}
    html.admin-dark-nav .campaign-detail-page :is(.meta-analytics-head p,.meta-analytics-head label,.meta-account small,.meta-kpi small,.meta-kpi span,.meta-card>header small,.meta-section-title small,.meta-best-slot small,.meta-rank small,.meta-post-metrics,.meta-heatmap>span,.meta-period-field>span){color:#b8b8b8}
    html.admin-dark-nav .campaign-detail-page .meta-post p{color:#ccc!important}
    html.admin-dark-nav .campaign-detail-page .meta-api-notes{color:#d9c7a5}
    html.admin-dark-nav .campaign-detail-page .admin-social-status.is-connected,
    html.admin-dark-nav .campaign-detail-page .admin-social-notice:not(.is-error){background:#263a29;color:#b6e3b5}
    html.admin-dark-nav .campaign-detail-page .admin-social-status:not(.is-connected),
    html.admin-dark-nav .campaign-detail-page :is(.admin-social-notice.is-error,.admin-social-missing){background:#422b25;color:#f5b9a8}
    html.admin-dark-nav .campaign-detail-page .meta-heatmap .cell{background:rgba(165,108,191,calc(.16 + var(--heat,.05)))}
</style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = Array.from(document.querySelectorAll('[data-campaign-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-campaign-panel]'));

        function activateCampaignTab(tabName, updateHash = true) {
            tabs.forEach(function (tab) {
                const isActive = tab.dataset.campaignTab === tabName;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                tab.tabIndex = isActive ? 0 : -1;
            });

            panels.forEach(function (panel) {
                panel.hidden = panel.dataset.campaignPanel !== tabName;
            });

            if (updateHash) {
                const hashes = { summary: 'resumen', documents: 'documentos', calendar: 'calendario', tasks: 'tareas', feedback: 'feedback', resources: 'recursos', meetings: 'reuniones', analytics: 'analiticas' };
                history.replaceState(null, '', '#' + hashes[tabName]);
            }

            if (tabName === 'analytics') window.loadCampaignAnalytics?.();
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activateCampaignTab(tab.dataset.campaignTab);
            });
        });

        const initialTabs = { '#resumen': 'summary', '#documentos': 'documents', '#tareas': 'tasks', '#calendario': 'calendar', '#feedback': 'feedback', '#recursos': 'resources', '#reuniones': 'meetings', '#analiticas': 'analytics' };
        const fallbackTab = @json(($errors->any() && (old('_meeting_form') || $errors->has('reuniones_cliente_por_mes'))) ? 'meetings' : 'summary');
        activateCampaignTab(initialTabs[window.location.hash] || 'summary', false);
        if (!initialTabs[window.location.hash] && fallbackTab !== 'summary') activateCampaignTab(fallbackTab, false);
    });
</script>
@endsection
