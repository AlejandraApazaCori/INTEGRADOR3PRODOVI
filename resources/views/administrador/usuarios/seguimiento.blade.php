@extends('layouts.app')

@section('title', 'Seguimiento del equipo')

@push('styles')
<style>
    .team-followup{--purple:#5b2b76;--orange:#ef6c22;--teal:#117e8c;--green:#638522;--red:#c64733;min-height:100vh;padding-bottom:48px;background:#fff;color:#302832;font-family:Inter,'Segoe UI',sans-serif}
    .team-followup *{box-sizing:border-box}.team-followup a{text-decoration:none}
    .tf-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:180px;padding:30px 48px;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;color:#fff}
    .tf-hero:after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%);pointer-events:none}
    .tf-hero-copy{min-width:0}.tf-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.tf-hero h1{margin:0;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em;overflow-wrap:anywhere}.tf-hero p{margin:8px 0 0;color:#e0e7ff;font-size:.84rem;line-height:1.5;overflow-wrap:anywhere}
    .tf-actions{display:flex;flex-wrap:wrap;gap:8px;flex:0 0 auto}.tf-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:10px 14px;border:1px solid rgba(255,255,255,.25);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.72rem;font-weight:900;transition:.18s}.tf-action:hover,.tf-action:focus-visible{border-color:#fff;background:#fff;color:#4f46e5}.tf-action.primary{border-color:#fff;background:#fff;color:#4f46e5}.tf-action.primary:hover{background:#eef2ff}
    .tf-content{display:grid;gap:26px;margin:26px 24px 0}.tf-heading{margin-bottom:13px}.tf-heading small{display:block;margin-bottom:4px;color:var(--teal);font-size:.64rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.tf-heading h2{margin:0;color:#302832;font-size:1.08rem;font-weight:900}.tf-heading p{margin:5px 0 0;color:#817786;font-size:.72rem;line-height:1.5}
    .tf-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}.tf-kpi{--accent:var(--teal);--soft:#e6f4f5;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;gap:14px;min-height:132px;padding:16px 17px;border:1px solid color-mix(in srgb,var(--accent) 22%,#fff);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--soft));box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09)}.tf-kpi:after{content:'';position:absolute;right:-36px;top:-46px;width:125px;height:125px;border:22px solid color-mix(in srgb,var(--accent) 9%,transparent);border-radius:50%;pointer-events:none}.tf-kpi-label,.tf-kpi-bottom{position:relative;z-index:1}.tf-kpi-label{color:#50594a;font-size:.65rem;font-weight:900;letter-spacing:.025em;text-transform:uppercase;line-height:1.35}.tf-kpi-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}.tf-kpi-bottom strong{color:#263024;font-size:1.7rem;font-weight:900;line-height:1}.tf-kpi-icon{display:grid;place-items:center;flex:0 0 auto;width:42px;height:42px;border-radius:.75rem;background:var(--accent);color:#fff;font-size:1rem}.tf-kpi.campaigns{--accent:var(--purple);--soft:#f3edf6}.tf-kpi.assigned{--accent:#e9a51a;--soft:#fff7df}.tf-kpi.completed{--accent:var(--green);--soft:#f0f6e7}.tf-kpi.overdue{--accent:var(--red);--soft:#fff0ed}
    .tf-charts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.tf-panel{min-width:0;padding:22px 24px;border:1px solid #eee8f0;border-radius:1rem;background:linear-gradient(135deg,#fff 0%,#fbf8fc 58%,#f2fbfa 100%);box-shadow:0 9px 22px rgba(61,23,79,.07)}.tf-panel h2{margin:0;color:#302832;font-size:1.05rem;font-weight:900}.tf-panel h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:var(--teal)}.tf-note{margin:8px 0 0;color:#817786;font-size:.72rem}
    .tf-status-list{display:grid;gap:15px;margin-top:22px}.tf-status-head{display:flex;justify-content:space-between;gap:10px;margin-bottom:7px;color:#4b4250;font-size:.74rem;font-weight:800}.tf-status-head span{display:inline-flex;align-items:center;gap:8px}.tf-dot{width:9px;height:9px;border-radius:50%;background:var(--status-color)}.tf-track{height:9px;overflow:hidden;border-radius:999px;background:#eae5ec}.tf-fill{height:100%;border-radius:999px;background:var(--status-color)}.tf-task-summary{display:flex;align-items:baseline;gap:7px;margin-top:20px}.tf-task-summary strong{color:var(--purple);font-size:1.7rem;font-weight:900}.tf-task-summary span{color:#817786;font-size:.72rem}.tf-task-track{margin-top:12px}.tf-task-track .tf-fill{background:linear-gradient(90deg,var(--green),var(--teal))}.tf-legend{display:flex;flex-wrap:wrap;gap:8px;margin-top:19px}.tf-legend span{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border-radius:999px;background:#fff;color:#4b4250;font-size:.67rem;font-weight:800}.tf-legend i{font-size:.6rem}.tf-legend .done i{color:var(--green)}.tf-legend .pending i{color:#a39ba7}.tf-legend .late i{color:var(--red)}
    .tf-campaigns{padding:22px 24px}.tf-campaign-list{display:grid;gap:10px;margin-top:18px}.tf-campaign{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:16px 18px;border:1px solid #e9e3eb;border-radius:.8rem;background:rgba(255,255,255,.78)}.tf-campaign-main{min-width:0;flex:1}.tf-campaign-title{display:flex;align-items:center;flex-wrap:wrap;gap:9px}.tf-campaign h3{margin:0;color:#302832;font-size:.86rem;font-weight:900;overflow-wrap:anywhere}.tf-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border-radius:999px;font-size:.61rem;font-weight:900;text-transform:uppercase}.tf-badge.activa{background:#e4f3f4;color:#0e6c78}.tf-badge.pausada{background:#fff3e8;color:#b45309}.tf-badge.finalizada{background:#eef5e3;color:#587923}.tf-badge.other{background:#f3f4f6;color:#4b5563}.tf-campaign-meta{display:flex;flex-wrap:wrap;gap:5px 13px;margin-top:8px;color:#817786;font-size:.69rem}.tf-campaign-meta span{display:inline-flex;align-items:center;gap:5px}.tf-campaign-meta i{color:var(--teal)}.tf-campaign-progress{display:flex;align-items:center;gap:10px;max-width:440px;margin-top:11px}.tf-campaign-progress .tf-track{flex:1;height:6px}.tf-campaign-progress .tf-fill{background:linear-gradient(90deg,var(--green),var(--teal))}.tf-campaign-progress span{color:#526b37;font-size:.67rem;font-weight:900;white-space:nowrap}.tf-open{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:0 0 auto;min-height:38px;padding:9px 12px;border-radius:.6rem;background:#f0e8f4;color:var(--purple);font-size:.68rem;font-weight:900;transition:.18s}.tf-open:hover,.tf-open:focus-visible{background:var(--purple);color:#fff}.tf-empty{display:grid;justify-items:center;gap:7px;padding:34px 16px;border:1px dashed #dcd4df;border-radius:.8rem;color:#817786;text-align:center;font-size:.75rem}.tf-empty i{color:var(--purple);font-size:1.5rem}.tf-pagination{margin-top:20px}
    html.admin-dark-nav .team-followup{background:#141414;color:#e5e5e5}html.admin-dark-nav .tf-heading h2,html.admin-dark-nav .tf-panel h2,html.admin-dark-nav .tf-campaign h3{color:#f5f5f5}html.admin-dark-nav .tf-heading p,html.admin-dark-nav .tf-note,html.admin-dark-nav .tf-campaign-meta,html.admin-dark-nav .tf-task-summary span{color:#c0c0c0}html.admin-dark-nav .tf-kpi{background:linear-gradient(135deg,#202020 35%,color-mix(in srgb,var(--accent) 13%,#202020));border-color:color-mix(in srgb,var(--accent) 45%,#404040)}html.admin-dark-nav .tf-kpi-label,html.admin-dark-nav .tf-kpi-bottom strong{color:#fafafa}html.admin-dark-nav .tf-panel{background:linear-gradient(135deg,#202020,#242424);border-color:#353535}html.admin-dark-nav .tf-campaign,html.admin-dark-nav .tf-legend span{background:#292929;border-color:#414141;color:#e5e5e5}html.admin-dark-nav .tf-track{background:#3b3b3b}html.admin-dark-nav .tf-status-head{color:#e5e5e5}html.admin-dark-nav .tf-open{background:#443354;color:#e3bdf5}html.admin-dark-nav .tf-open:hover{background:var(--purple);color:#fff}html.admin-dark-nav .tf-empty{border-color:#414141;color:#c0c0c0}
    @media(max-width:1100px){.tf-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:800px){.tf-hero{flex-direction:column;align-items:flex-start}.tf-charts{grid-template-columns:1fr}}@media(max-width:640px){.tf-hero{padding:24px 20px}.tf-actions{width:100%}.tf-action{flex:1}.tf-content{margin:22px 12px 0;gap:22px}.tf-kpis{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.tf-kpi{min-height:116px;padding:13px}.tf-kpi-label{font-size:.57rem}.tf-kpi-icon{width:36px;height:36px}.tf-panel,.tf-campaigns{padding:18px}.tf-campaign{align-items:stretch;flex-direction:column}.tf-open{align-self:flex-start}}@media(max-width:390px){.tf-kpis{grid-template-columns:1fr}.tf-actions{flex-direction:column}}
</style>
@endpush

@section('content')
@php
    $totalCampaigns = $campaigns->total();
    $totalTasks = (int) ($taskCounts->total ?? 0);
    $completed = (int) ($taskCounts->completed ?? 0);
    $overdue = (int) ($taskCounts->overdue ?? 0);
    $statuses = ['activa' => ['Activas', '#117e8c'], 'pausada' => ['Pausadas', '#ef6c22'], 'finalizada' => ['Finalizadas', '#638522']];
    $taskPercent = $totalTasks ? min(100, round($completed / $totalTasks * 100)) : 0;
@endphp
<div class="team-followup">
    <header class="tf-hero">
        <div class="tf-hero-copy">
            <h1>Seguimiento de {{ $user->name }}</h1>
            <p>{{ $user->email }} · {{ $user->roles->pluck('nombre_rol')->join(', ') ?: 'Sin rol asignado' }}</p>
        </div>
        <nav class="tf-actions" aria-label="Acciones del usuario">
            <a href="{{ route('administrador.usuarios.edit', $user) }}" class="tf-action"><i class="fas fa-user-pen" aria-hidden="true"></i>Editar perfil</a>
            <a href="{{ route('administrador.usuarios.index') }}" class="tf-action primary"><i class="fas fa-arrow-left" aria-hidden="true"></i>Volver a usuarios</a>
        </nav>
    </header>

    <div class="tf-content">
        <section aria-labelledby="tf-summary-title">
            <div class="tf-kpis">
                @foreach([
                    ['Campañas vinculadas', $totalCampaigns, 'campaigns', 'fa-bullhorn'],
                    ['Tareas asignadas', $totalTasks, 'assigned', 'fa-list-check'],
                    ['Tareas terminadas', $completed, 'completed', 'fa-circle-check'],
                    ['Tareas vencidas', $overdue, 'overdue', 'fa-clock'],
                    ['Archivos subidos', $uploadedFiles, 'files', 'fa-file-arrow-up'],
                ] as [$label, $value, $type, $icon])
                    <div class="tf-kpi {{ $type }}"><span class="tf-kpi-label">{{ $label }}</span><div class="tf-kpi-bottom"><strong>{{ $value }}</strong><span class="tf-kpi-icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span></div></div>
                @endforeach
            </div>
        </section>

        <section class="tf-charts" aria-label="Gráficas de seguimiento">
            <div class="tf-panel">
                <h2>Estado de las campañas</h2>
                <p class="tf-note">Distribución de las campañas vinculadas</p>
                <div class="tf-status-list">
                    @foreach($statuses as $key => [$label, $color])
                        @php $count = (int) ($statusCounts[$key] ?? 0); @endphp
                        <div style="--status-color: {{ $color }}">
                            <div class="tf-status-head"><span><i class="tf-dot" aria-hidden="true"></i>{{ $label }}</span><strong>{{ $count }}</strong></div>
                            <div class="tf-track" role="img" aria-label="{{ $label }}: {{ $count }} de {{ $totalCampaigns }} campañas"><div class="tf-fill" style="width: {{ $totalCampaigns ? min(100, round($count / $totalCampaigns * 100)) : 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="tf-panel">
                <h2>Avance de tareas asignadas</h2>
                <p class="tf-note">Progreso de las tareas bajo su responsabilidad</p>
                <div class="tf-task-summary"><strong>{{ $taskPercent }}%</strong><span>{{ $completed }} de {{ $totalTasks }} terminadas</span></div>
                <div class="tf-track tf-task-track" role="img" aria-label="{{ $completed }} tareas terminadas de {{ $totalTasks }}"><div class="tf-fill" style="width: {{ $taskPercent }}%"></div></div>
                <div class="tf-legend"><span class="done"><i class="fas fa-circle" aria-hidden="true"></i>Terminadas: {{ $completed }}</span><span class="pending"><i class="fas fa-circle" aria-hidden="true"></i>Por realizar: {{ max(0, $totalTasks - $completed) }}</span><span class="late"><i class="fas fa-circle" aria-hidden="true"></i>Vencidas: {{ $overdue }}</span></div>
            </div>
        </section>

        <section class="tf-panel tf-campaigns" aria-labelledby="tf-campaigns-title">
            <div class="tf-campaign-list">
                @forelse($campaigns as $campaign)
                    @php
                        $roles = collect();
                        if ((int) $campaign->usuario_creador_id === (int) $user->id) $roles->push('Creador');
                        if ((int) $campaign->community_manager_id === (int) $user->id) $roles->push('Community manager');
                        if ((int) $campaign->disenador_id === (int) $user->id || $campaign->disenadores->contains('id', $user->id)) $roles->push('Diseño');
                        if ($roles->isEmpty()) $roles->push('Participó en tareas');
                        $assignedCount = (int) $campaign->tareas_asignadas_count;
                        $doneCount = (int) $campaign->tareas_terminadas_count;
                        $campaignPercent = $assignedCount ? min(100, round($doneCount / $assignedCount * 100)) : 0;
                    @endphp
                    <article class="tf-campaign">
                        <div class="tf-campaign-main">
                            <div class="tf-campaign-title"><h3>{{ $campaign->nombre }}</h3><span class="tf-badge {{ in_array($campaign->estado, ['activa', 'pausada', 'finalizada'], true) ? $campaign->estado : 'other' }}"><i class="fas fa-circle" aria-hidden="true"></i>{{ ucfirst($campaign->estado) }}</span></div>
                            <div class="tf-campaign-meta"><span><i class="fas fa-building" aria-hidden="true"></i>{{ $campaign->suscripcion?->empresa?->nombre_empresa ?? $campaign->cliente?->name ?? 'Sin empresa' }}</span><span><i class="fas fa-list-check" aria-hidden="true"></i>{{ $campaign->tareas_count }} tareas en la campaña</span><span><i class="fas fa-user-tag" aria-hidden="true"></i>{{ $roles->join(' · ') }}</span><span><i class="fas fa-calendar-day" aria-hidden="true"></i>{{ $campaign->fecha_inicio ? \Carbon\Carbon::parse($campaign->fecha_inicio)->format('d/m/Y') : 'Sin fecha de inicio' }}</span></div>
                            <div class="tf-campaign-progress"><div class="tf-track" role="img" aria-label="{{ $doneCount }} de {{ $assignedCount }} tareas asignadas terminadas"><div class="tf-fill" style="width: {{ $campaignPercent }}%"></div></div><span>{{ $doneCount }} / {{ $assignedCount }} tareas asignadas</span></div>
                        </div>
                        <a href="{{ route('administrador.usuarios.seguimiento.campania', [$user, $campaign]) }}" class="tf-open">Ver tareas y entregas <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                @empty
                    <div class="tf-empty"><i class="fas fa-folder-open" aria-hidden="true"></i><span>Todavía no hay campañas vinculadas a este usuario.</span></div>
                @endforelse
            </div>
            @if($campaigns->hasPages())<div class="tf-pagination">{{ $campaigns->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
