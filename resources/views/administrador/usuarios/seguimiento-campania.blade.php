@extends('layouts.app')

@section('title', 'Seguimiento de campaña')

@push('styles')
<style>
    .team-campaign-followup{--purple:#5b2b76;--orange:#ef6c22;--teal:#117e8c;--green:#638522;--red:#c64733;min-height:100vh;padding-bottom:48px;background:#fff;color:#302832;font-family:Inter,'Segoe UI',sans-serif}
    .team-campaign-followup *{box-sizing:border-box}.team-campaign-followup a{text-decoration:none}
    .tcf-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:180px;padding:30px 48px;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;color:#fff}
    .tcf-hero:after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%);pointer-events:none}
    .tcf-hero-copy{min-width:0}.tcf-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.tcf-hero h1{margin:0;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em;overflow-wrap:anywhere}.tcf-hero p{margin:8px 0 0;color:#e0e7ff;font-size:.84rem;line-height:1.5;overflow-wrap:anywhere}
    .tcf-actions{display:flex;flex-wrap:wrap;gap:8px;flex:0 0 auto}.tcf-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:10px 14px;border:1px solid rgba(255,255,255,.25);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.72rem;font-weight:900;transition:.18s}.tcf-action:hover,.tcf-action:focus-visible{border-color:#fff;background:#fff;color:#4f46e5}.tcf-action.primary{border-color:#fff;background:#fff;color:#4f46e5}.tcf-action.primary:hover{background:#eef2ff}
    .tcf-content{display:grid;gap:22px;margin:26px 24px 0}.tcf-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.tcf-overview-card{--accent:var(--teal);--soft:#e6f4f5;min-width:0;display:flex;align-items:center;gap:14px;min-height:104px;padding:17px;border:1px solid color-mix(in srgb,var(--accent) 22%,#fff);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--soft));box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09)}.tcf-overview-card.is-campaign{--accent:var(--purple);--soft:#f3edf6}.tcf-overview-card.is-person{--accent:var(--orange);--soft:#fff1e8}.tcf-overview-card.is-tasks{--accent:var(--green);--soft:#f0f6e7}.tcf-overview-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:.75rem;background:var(--accent);color:#fff;font-size:1rem}.tcf-overview-card small{display:block;color:#6d7468;font-size:.61rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.tcf-overview-card strong{display:block;margin-top:5px;color:#302832;font-size:.83rem;font-weight:900;line-height:1.35;overflow-wrap:anywhere}
    .tcf-panel{min-width:0;padding:22px 24px;border:1px solid #eee8f0;border-radius:1rem;background:linear-gradient(135deg,#fff 0%,#fbf8fc 58%,#f2fbfa 100%);box-shadow:0 9px 22px rgba(61,23,79,.07)}.tcf-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.tcf-panel-head small{display:block;margin-bottom:4px;color:var(--teal);font-size:.64rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.tcf-panel h2{margin:0;color:#302832;font-size:1.08rem;font-weight:900}.tcf-panel h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:var(--teal)}.tcf-panel-head p{margin:8px 0 0;color:#817786;font-size:.72rem;line-height:1.5}.tcf-count{flex:0 0 auto;padding:7px 10px;border-radius:999px;background:#e6f4f5;color:var(--teal);font-size:.65rem;font-weight:900}
    .tcf-task-list{display:grid;gap:12px;margin-top:20px}.tcf-task{padding:18px;border:1px solid #e9e3eb;border-radius:.85rem;background:rgba(255,255,255,.85)}.tcf-task-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.tcf-task-title{display:flex;align-items:flex-start;gap:11px;min-width:0}.tcf-task-icon{display:grid;place-items:center;flex:0 0 37px;width:37px;height:37px;border-radius:10px;background:#e6f4f5;color:var(--teal)}.tcf-task h3{margin:0;color:#302832;font-size:.86rem;font-weight:900;line-height:1.4;overflow-wrap:anywhere}.tcf-meta{display:flex;flex-wrap:wrap;gap:5px 15px;margin-top:8px;color:#817786;font-size:.69rem}.tcf-meta span{display:inline-flex;align-items:center;gap:6px}.tcf-meta i{color:var(--teal)}.tcf-badges{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:6px}.tcf-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:.61rem;font-weight:900;white-space:nowrap}.tcf-badge.is-assigned{background:#eef2ff;color:#4f46e5}.tcf-badge.is-done{background:#edf6e5;color:#587923}.tcf-badge.is-progress{background:#e6f4f5;color:#0e6c78}.tcf-badge.is-waiting{background:#fff3e8;color:#b45309}.tcf-badge.is-late{background:#fff0ed;color:#b5372b}.tcf-task-note{display:flex;align-items:center;gap:8px;margin:14px 0 0;padding:9px 11px;border-radius:8px;background:#fff7e9;color:#9c6615;font-size:.68rem;font-weight:800}
    .tcf-files{margin-top:16px;padding-top:14px;border-top:1px solid #eee8f0}.tcf-files-head{display:flex;align-items:center;gap:8px;color:#51475a;font-size:.72rem;font-weight:900}.tcf-files-head i{color:var(--purple)}.tcf-files-head strong{display:grid;place-items:center;min-width:21px;height:21px;padding:0 5px;border-radius:999px;background:#f0e8f4;color:var(--purple);font-size:.6rem}.tcf-file-list{display:grid;gap:7px;margin-top:11px}.tcf-file{display:flex;align-items:center;gap:10px;min-width:0;padding:9px 11px;border:1px solid #ede9ee;border-radius:9px;background:#fff}.tcf-file-icon{display:grid;place-items:center;flex:0 0 30px;width:30px;height:30px;border-radius:7px;background:#e6f4f5;color:var(--teal);font-size:.76rem}.tcf-file-main{min-width:0}.tcf-file-main strong{display:block;color:#39333d;font-size:.69rem;font-weight:850;overflow-wrap:anywhere}.tcf-file-main small{display:block;margin-top:3px;color:#8a818d;font-size:.6rem}.tcf-no-files{margin:10px 0 0;color:#817786;font-size:.69rem}.tcf-open{display:inline-flex;align-items:center;gap:8px;margin-top:15px;padding:9px 12px;border-radius:.6rem;background:#f0e8f4;color:var(--purple);font-size:.68rem;font-weight:900;transition:.18s}.tcf-open:hover,.tcf-open:focus-visible{background:var(--purple);color:#fff}.tcf-empty{display:grid;justify-items:center;gap:8px;padding:38px 16px;border:1px dashed #dcd4df;border-radius:.8rem;color:#817786;text-align:center;font-size:.75rem}.tcf-empty i{color:var(--purple);font-size:1.5rem}.tcf-pagination{margin-top:20px}
    html.admin-dark-nav .team-campaign-followup{background:#141414;color:#e5e5e5}html.admin-dark-nav .tcf-overview-card{background:linear-gradient(135deg,#202020 35%,color-mix(in srgb,var(--accent) 13%,#202020));border-color:color-mix(in srgb,var(--accent) 45%,#404040)}html.admin-dark-nav .tcf-overview-card small,html.admin-dark-nav .tcf-panel-head p,html.admin-dark-nav .tcf-meta,html.admin-dark-nav .tcf-no-files{color:#c0c0c0}html.admin-dark-nav .tcf-overview-card strong,html.admin-dark-nav .tcf-panel h2,html.admin-dark-nav .tcf-task h3,html.admin-dark-nav .tcf-file-main strong{color:#f5f5f5}html.admin-dark-nav .tcf-panel{background:linear-gradient(135deg,#202020,#242424);border-color:#353535}html.admin-dark-nav .tcf-task,html.admin-dark-nav .tcf-file{background:#292929;border-color:#414141}html.admin-dark-nav .tcf-files{border-color:#414141}html.admin-dark-nav .tcf-files-head{color:#e5e5e5}html.admin-dark-nav .tcf-file-main small{color:#b8b8b8}html.admin-dark-nav .tcf-open{background:#443354;color:#e3bdf5}html.admin-dark-nav .tcf-open:hover{background:var(--purple);color:#fff}html.admin-dark-nav .tcf-empty{border-color:#414141;color:#c0c0c0}
    @media(max-width:1000px){.tcf-overview{grid-template-columns:repeat(2,minmax(0,1fr))}.tcf-overview-card.is-tasks{grid-column:1/-1}}@media(max-width:800px){.tcf-hero{flex-direction:column;align-items:flex-start}.tcf-task-top{flex-direction:column}.tcf-badges{justify-content:flex-start}}@media(max-width:640px){.tcf-hero{padding:24px 20px}.tcf-actions{width:100%}.tcf-action{flex:1}.tcf-content{margin:22px 12px 0}.tcf-overview{grid-template-columns:1fr;gap:10px}.tcf-overview-card.is-tasks{grid-column:auto}.tcf-panel{padding:18px}.tcf-task{padding:14px}.tcf-panel-head{flex-direction:column}.tcf-count{align-self:flex-start}}@media(max-width:390px){.tcf-actions{flex-direction:column}}
</style>
@endpush

@section('content')
@php
    $doneStates = ['entregado', 'aprobado', 'publicado'];
    $stateNames = ['no_iniciado' => 'No iniciada', 'pendiente' => 'Pendiente', 'en_curso' => 'En curso', 'entregado' => 'Entregada', 'reformular' => 'Reformular', 'aprobado' => 'Aprobada', 'publicado' => 'Publicada'];
    $companyName = $campania->suscripcion?->empresa?->nombre_empresa ?? $campania->cliente?->name ?? 'Sin empresa';
@endphp
<div class="team-campaign-followup">
    <header class="tcf-hero">
        <div class="tcf-hero-copy">
            <span class="tcf-eyebrow">Seguimiento de campaña</span>
            <h1>{{ $campania->nombre }}</h1>
            <p>{{ $companyName }} · Seguimiento de {{ $user->name }}</p>
        </div>
        <nav class="tcf-actions" aria-label="Acciones de seguimiento">
            <a href="{{ route('administrador.usuarios.seguimiento', $user) }}" class="tcf-action"><i class="fas fa-arrow-left" aria-hidden="true"></i>Volver al seguimiento</a>
            <a href="{{ route('administrador.campañas.show', $campania) }}" class="tcf-action primary"><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>Abrir campaña</a>
        </nav>
    </header>

    <div class="tcf-content">
        <section class="tcf-overview" aria-label="Contexto de la campaña">
            <div class="tcf-overview-card is-campaign"><span class="tcf-overview-icon"><i class="fas fa-building" aria-hidden="true"></i></span><div><small>Empresa</small><strong>{{ $companyName }}</strong></div></div>
            <div class="tcf-overview-card is-person"><span class="tcf-overview-icon"><i class="fas fa-user-group" aria-hidden="true"></i></span><div><small>Persona del equipo</small><strong>{{ $user->name }}</strong></div></div>
            <div class="tcf-overview-card is-tasks"><span class="tcf-overview-icon"><i class="fas fa-list-check" aria-hidden="true"></i></span><div><small>Tareas vinculadas</small><strong>{{ $tasks->total() }}</strong></div></div>
        </section>

        <section class="tcf-panel" aria-labelledby="tcf-tasks-title">
            <div class="tcf-panel-head">
                <div>
                    <small>Actividad de {{ $user->name }}</small>
                    <h2 id="tcf-tasks-title">Tareas y entregables</h2>
                    <p>La entrega se atribuye a quien subió el archivo. El estado de la tarea corresponde a la campaña.</p>
                </div>
                <span class="tcf-count">{{ $tasks->total() }} {{ $tasks->total() === 1 ? 'tarea' : 'tareas' }}</span>
            </div>
            <div class="tcf-task-list">
                @forelse($tasks as $task)
                    @php
                        $isAssigned = (int) $task->asignado_id === (int) $user->id || $task->responsables->isNotEmpty();
                        $done = in_array($task->estado, $doneStates, true);
                        $late = ! $done && $task->fecha_limite && $task->fecha_limite->lt(today());
                        $stateClass = $done ? 'is-done' : ($task->estado === 'en_curso' ? 'is-progress' : 'is-waiting');
                    @endphp
                    <article class="tcf-task">
                        <div class="tcf-task-top">
                            <div class="tcf-task-title">
                                <span class="tcf-task-icon"><i class="fas fa-list-check" aria-hidden="true"></i></span>
                                <div>
                                    <h3>{{ $task->titulo }}</h3>
                                    <div class="tcf-meta">
                                        <span><i class="fas fa-user" aria-hidden="true"></i>Responsable: {{ $task->asignado?->name ?? 'Sin registro' }}</span>
                                        <span><i class="fas fa-calendar-day" aria-hidden="true"></i>Límite: {{ $task->fecha_limite?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tcf-badges">
                                @if($isAssigned)<span class="tcf-badge is-assigned"><i class="fas fa-user-check" aria-hidden="true"></i>Asignada</span>@endif
                                <span class="tcf-badge {{ $stateClass }}"><i class="fas fa-circle" aria-hidden="true"></i>{{ $stateNames[$task->estado] ?? ucfirst($task->estado) }}</span>
                                @if($late && $isAssigned)<span class="tcf-badge is-late"><i class="fas fa-clock" aria-hidden="true"></i>Vencida</span>@endif
                            </div>
                        </div>
                        @if($isAssigned && ! $done)
                            <p class="tcf-task-note"><i class="fas fa-hourglass-half" aria-hidden="true"></i>Pendiente de completar por {{ $user->name }}.</p>
                        @endif
                        <div class="tcf-files">
                            <div class="tcf-files-head"><i class="fas fa-paperclip" aria-hidden="true"></i>Archivos subidos por {{ $user->name }} <strong>{{ $task->archivos_usuario_count }}</strong></div>
                            @if($task->archivos->isNotEmpty())
                                <div class="tcf-file-list">
                                    @foreach($task->archivos as $file)
                                        @php
                                            $extension = strtolower((string) $file->extension);
                                            $fileIcon = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? 'fa-file-image' : (in_array($extension, ['mp4', 'mov', 'avi', 'webm'], true) ? 'fa-file-video' : ($extension === 'pdf' ? 'fa-file-pdf' : 'fa-file'));
                                        @endphp
                                        <div class="tcf-file">
                                            <span class="tcf-file-icon"><i class="fas {{ $fileIcon }}" aria-hidden="true"></i></span>
                                            <div class="tcf-file-main"><strong>{{ $file->nombre_original }}</strong><small>{{ $file->created_at?->format('d/m/Y H:i') }} · {{ ucfirst($file->estado) }}</small></div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="tcf-no-files">No registró archivos en esta tarea.</p>
                            @endif
                        </div>
                        <a href="{{ route('administrador.tareas.ver-subidas', $task) }}" class="tcf-open">Ver detalle y archivos <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </article>
                @empty
                    <div class="tcf-empty"><i class="fas fa-folder-open" aria-hidden="true"></i><span>No hay tareas vinculadas a esta persona en la campaña.</span></div>
                @endforelse
            </div>
            @if($tasks->hasPages())<div class="tcf-pagination">{{ $tasks->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
