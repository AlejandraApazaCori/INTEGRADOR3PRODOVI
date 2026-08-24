@php
    $tareas = $campania->tareas;
    $totalTareas = $tareas->count();
    $tareasPendientes = $tareas->where('estado', 'pendiente')->count();
    $tareasEnProgreso = $tareas->where('estado', 'en_progreso')->count();
    $tareasCompletadas = $tareas->where('estado', 'completada')->count();
@endphp

<section class="tasks-workspace">
    <header class="tasks-header">
        <div>
            <span class="tasks-eyebrow">Organización del equipo</span>
            <h2>Tareas de la campaña</h2>
            <p>Consulta el avance, los responsables y las fechas de entrega.</p>
        </div>
        <a href="{{ route('administrador.tareas.create', $campania->id) }}" class="tasks-primary-action"><i class="fas fa-plus"></i> Nueva tarea</a>
    </header>

    <div class="tasks-summary" aria-label="Resumen de tareas">
        <div><span>Total</span><strong>{{ $totalTareas }}</strong></div>
        <div><span>Pendientes</span><strong>{{ $tareasPendientes }}</strong></div>
        <div><span>En progreso</span><strong>{{ $tareasEnProgreso }}</strong></div>
        <div><span>Completadas</span><strong>{{ $tareasCompletadas }}</strong></div>
    </div>

    @if($totalTareas > 0)
        <div class="tasks-table-wrap">
            <table class="tasks-table">
                <thead>
                    <tr><th>Tarea</th><th>Responsable</th><th>Estado</th><th>Prioridad</th><th>Fecha límite</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach($tareas as $tarea)
                        @php
                            $estadoClase = match($tarea->estado) {
                                'pendiente' => 'is-pending', 'en_progreso' => 'is-progress',
                                'completada' => 'is-complete', default => 'is-cancelled',
                            };
                            $prioridadClase = match($tarea->prioridad) {
                                'baja' => 'is-low', 'media' => 'is-medium',
                                'alta' => 'is-high', default => 'is-urgent',
                            };
                            $diasTarea = (int) now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false);
                        @endphp
                        <tr>
                            <td><strong class="task-title">{{ $tarea->titulo }}</strong><small>ID #{{ $tarea->id }}</small></td>
                            <td><strong>{{ $tarea->asignado?->name ?? 'Sin asignar' }}</strong><small>Responsable principal</small></td>
                            <td><span class="task-badge {{ $estadoClase }}">{{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}</span></td>
                            <td><span class="task-badge {{ $prioridadClase }}">{{ ucfirst($tarea->prioridad) }}</span></td>
                            <td>
                                <strong>{{ $tarea->fecha_limite->format('d/m/Y') }}</strong>
                                <small>{{ $diasTarea >= 0 ? ($diasTarea === 0 ? 'Vence hoy' : 'Faltan '.$diasTarea.' días') : 'Vencida hace '.abs($diasTarea).' días' }}</small>
                            </td>
                            <td>
                                <div class="task-actions">
                                    <a href="{{ route('administrador.tareas.archivos.create', $tarea->id) }}">Subir</a>
                                    <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}">Revisar</a>
                                    <a href="{{ route('administrador.tareas.edit', $tarea->id) }}">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="tasks-empty">
            <h3>No hay tareas registradas</h3>
            <p>Crea la primera tarea para organizar responsables y fechas de entrega.</p>
            <a href="{{ route('administrador.tareas.create', $campania->id) }}">Crear primera tarea</a>
        </div>
    @endif
</section>

<style>
    .tasks-workspace{overflow:hidden;border:1px solid #e2e5df;border-radius:14px;background:#fff;box-shadow:0 6px 18px rgba(55,60,52,.05)}
    .tasks-header{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:22px 24px;border-bottom:1px solid #e8ebe5}
    .tasks-eyebrow{display:block;margin-bottom:4px;color:#117e8c;font-size:.59rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .tasks-header h2{margin:0;color:#252a24;font-size:1.12rem;font-weight:900;letter-spacing:-.02em}.tasks-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.tasks-header p{margin:8px 0 0;color:#737b71;font-size:.7rem;font-weight:600}
    .tasks-primary-action{min-height:38px;display:inline-flex;align-items:center;justify-content:center;gap:7px;flex:0 0 auto;padding:0 15px;border-radius:9px;background:#4f46e5;color:#fff;font-size:.67rem;font-weight:900;text-decoration:none;transition:.18s}.tasks-primary-action:hover{background:#4338ca;transform:translateY(-1px)}
    .tasks-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border-bottom:1px solid #e8ebe5;background:#fafbf9}.tasks-summary>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 18px;border-right:1px solid #e8ebe5}.tasks-summary>div:last-child{border-right:0}.tasks-summary span{color:#737b71;font-size:.61rem;font-weight:800}.tasks-summary strong{color:#30362e;font-size:.95rem;font-weight:900}
    .tasks-table-wrap{overflow-x:auto}.tasks-table{width:100%;min-width:920px;border-collapse:collapse}.tasks-table th{padding:12px 16px;border-bottom:1px solid #dfe3dc;background:#f5f6f3;color:#697067;font-size:.57rem;font-weight:900;letter-spacing:.06em;text-align:left;text-transform:uppercase}.tasks-table td{padding:14px 16px;border-bottom:1px solid #eceeea;color:#3e443c;font-size:.68rem;vertical-align:middle}.tasks-table tbody tr:last-child td{border-bottom:0}.tasks-table tbody tr:hover td{background:#fafbf9}
    .tasks-table td>strong,.tasks-table td>small{display:block}.tasks-table td>strong{font-size:.69rem;font-weight:800}.tasks-table td>small{margin-top:4px;color:#8a9187;font-size:.57rem}.task-title{color:#262b25!important;font-size:.72rem!important;font-weight:900!important}
    .task-badge{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:.56rem;font-weight:850;white-space:nowrap}.task-badge.is-pending,.task-badge.is-high{background:#fff4da;color:#9a6512}.task-badge.is-progress,.task-badge.is-medium{background:#eaf1ff;color:#355ca8}.task-badge.is-complete,.task-badge.is-low{background:#edf7e5;color:#547a27}.task-badge.is-cancelled,.task-badge.is-urgent{background:#fff0f0;color:#a72d2d}
    .task-actions{display:flex;gap:5px}.task-actions a{padding:6px 8px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#51594f;font-size:.56rem;font-weight:850;text-decoration:none}.task-actions a:hover{border-color:#4f46e5;color:#4f46e5}
    .tasks-empty{padding:45px 24px;text-align:center}.tasks-empty h3{margin:0;color:#2e332d;font-size:.95rem;font-weight:900}.tasks-empty p{margin:7px 0 18px;color:#7b8278;font-size:.68rem}.tasks-empty a{display:inline-flex;padding:9px 13px;border-radius:8px;background:#4f46e5;color:#fff;font-size:.65rem;font-weight:900;text-decoration:none}
    @media(max-width:700px){.tasks-header{align-items:stretch;flex-direction:column;padding:18px}.tasks-primary-action{width:100%}.tasks-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.tasks-summary>div:nth-child(2){border-right:0}.tasks-summary>div:nth-child(-n+2){border-bottom:1px solid #e8ebe5}}
</style>
