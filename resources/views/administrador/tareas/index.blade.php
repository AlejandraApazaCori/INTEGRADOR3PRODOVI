@php
    $tareas = $campania->tareas;
    $taskUploadErrors = $errors->getBag('taskUpload');
    $taskUploadToReopen = session('upload_task_id') ? $tareas->firstWhere('id', (int) session('upload_task_id')) : null;
    $estadosTarea = [
        'pendiente' => ['titulo' => 'Por hacer', 'icono' => 'fa-circle-dot'],
        'en_progreso' => ['titulo' => 'Haciendo', 'icono' => 'fa-spinner'],
        'completada' => ['titulo' => 'Hecho', 'icono' => 'fa-circle-check'],
    ];
@endphp

<section class="tasks-workspace" data-task-workspace>
    <header class="tasks-toolbar">
        <div class="tasks-view-switch" role="group" aria-label="Cambiar vista de tareas">
            <button type="button" class="is-active" data-task-view="board" aria-pressed="true"><i class="fas fa-table-columns"></i> Tarjetas</button>
            <button type="button" data-task-view="table" aria-pressed="false"><i class="fas fa-table-list"></i> Tabla</button>
        </div>
        <a href="{{ route('administrador.tareas.create', $campania->id) }}" class="tasks-primary-action"><i class="fas fa-plus"></i> Nueva tarea</a>
    </header>

    <div class="tasks-board" data-task-panel="board">
        @foreach($estadosTarea as $estado => $configuracion)
            @php $tareasColumna = $estado === 'pendiente' ? $tareas->whereIn('estado', ['pendiente', 'rechazada']) : $tareas->where('estado', $estado); @endphp
            <section class="task-column is-{{ str_replace('_', '-', $estado) }}" data-task-column="{{ $estado }}">
                <header class="task-column-header">
                    <div><i class="fas {{ $configuracion['icono'] }}"></i><h3>{{ $configuracion['titulo'] }}</h3></div>
                    <span data-column-count>{{ $tareasColumna->count() }}</span>
                </header>
                <div class="task-column-body" data-task-dropzone="{{ $estado }}">
                    @foreach($tareasColumna as $tarea)
                        @include('administrador.tareas.partials.kanban-card', ['tarea' => $tarea, 'estadoKanban' => $estado, 'embeddedUploadDrawer' => true])
                    @endforeach
                    <div class="task-column-empty" data-column-empty><i class="fas fa-arrow-down"></i><span>Suelta una tarea aquí</span></div>
                </div>
                <a href="{{ route('administrador.tareas.create', $campania->id) }}" class="task-column-add"><i class="fas fa-plus"></i> Agregar tarea</a>
            </section>
        @endforeach
    </div>

    <div class="tasks-table-view is-hidden" data-task-panel="table">
        <div class="tasks-table-filters" role="group" aria-label="Filtrar tareas por estado">
            <button type="button" class="is-active" data-task-filter="all"><i class="fas fa-layer-group"></i> Todas <span>{{ $tareas->count() }}</span></button>
            <button type="button" data-task-filter="pendiente"><i class="fas fa-circle-dot"></i> Por hacer <span>{{ $tareas->whereIn('estado', ['pendiente', 'rechazada'])->count() }}</span></button>
            <button type="button" data-task-filter="en_progreso"><i class="fas fa-spinner"></i> Haciendo <span>{{ $tareas->where('estado', 'en_progreso')->count() }}</span></button>
            <button type="button" data-task-filter="completada"><i class="fas fa-circle-check"></i> Hecho <span>{{ $tareas->where('estado', 'completada')->count() }}</span></button>
        </div>

        @if($tareas->isNotEmpty())
            <div class="tasks-table-wrap">
                <table class="tasks-table">
                    <thead><tr><th>Tarea</th><th>Responsable</th><th>Estado</th><th>Prioridad</th><th>Fecha límite</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @foreach($tareas as $tarea)
                            @php
                                $estadoClase = match($tarea->estado) {'pendiente' => 'is-pending', 'en_progreso' => 'is-progress', 'completada' => 'is-complete', default => 'is-cancelled'};
                                $prioridadClase = match($tarea->prioridad) {'baja' => 'is-low', 'media' => 'is-medium', 'alta' => 'is-high', default => 'is-urgent'};
                                $diasTarea = (int) now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false);
                                $responsablesTarea = $tarea->responsables->isNotEmpty() ? $tarea->responsables : ($tarea->asignado ? collect([$tarea->asignado]) : collect());
                            @endphp
                            <tr data-task-row data-task-id="{{ $tarea->id }}" data-task-status="{{ $tarea->estado === 'rechazada' ? 'pendiente' : $tarea->estado }}">
                                <td><strong class="task-title">{{ $tarea->titulo }}</strong><small>{{ $tarea->entregable ? 'Entregable: '.$tarea->entregable : 'ID #'.$tarea->id }}</small><div class="task-meta-labels">@if($tarea->tipo_contenido)<span><i class="fas fa-photo-film"></i>{{ ucfirst($tarea->tipo_contenido) }}</span>@endif @if($tarea->visible_cliente)<span class="is-visible"><i class="fas fa-eye"></i>Visible para cliente</span>@endif @if($tarea->requiere_aprobacion)<span class="is-approval"><i class="fas fa-user-check"></i>Requiere aprobación</span>@endif</div></td>
                                <td>@forelse($responsablesTarea as $responsable)<strong>{{ $responsable->name }}</strong><small>{{ $responsable->roles->pluck('nombre_rol')->filter()->implode(', ') ?: ($loop->first ? 'Responsable principal' : 'Responsable de apoyo') }}</small>@empty<strong>Sin asignar</strong>@endforelse</td>
                                <td><span class="task-badge {{ $estadoClase }}" data-row-status-badge>{{ $estadosTarea[$tarea->estado]['titulo'] ?? ucfirst(str_replace('_', ' ', $tarea->estado)) }}</span></td>
                                <td><span class="task-badge {{ $prioridadClase }}">{{ ucfirst($tarea->prioridad) }}</span></td>
                                <td><strong>{{ $tarea->fecha_limite->format('d/m/Y') }}</strong><small>{{ $diasTarea >= 0 ? ($diasTarea === 0 ? 'Vence hoy' : 'Faltan '.$diasTarea.' días') : 'Vencida hace '.abs($diasTarea).' días' }}</small></td>
                                <td><div class="task-actions"><button type="button" data-open-task-upload data-task-title="{{ $tarea->titulo }}" data-upload-url="{{ route('administrador.tareas.archivos.store', $tarea->id) }}">Subir</button><a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}">Revisar</a><a href="{{ route('administrador.tareas.edit', $tarea->id) }}">Editar</a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tasks-filter-empty is-hidden" data-filter-empty><i class="fas fa-filter-circle-xmark"></i><strong>No hay tareas en este estado</strong></div>
        @else
            <div class="tasks-empty"><i class="fas fa-list-check"></i><h3>No hay tareas registradas</h3><p>Crea la primera tarea para organizar responsables y fechas de entrega.</p><a href="{{ route('administrador.tareas.create', $campania->id) }}">Crear primera tarea</a></div>
        @endif
    </div>

    <div class="tasks-status-message" data-task-message role="status" aria-live="polite"></div>
</section>

<div class="task-upload-drawer" id="task-upload-drawer" hidden data-open-on-load="{{ $taskUploadErrors->any() ? 'true' : 'false' }}">
    <button type="button" class="task-upload-drawer-backdrop" data-close-task-upload aria-label="Cerrar panel"></button>
    <aside class="task-upload-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="task-upload-drawer-title">
        <header>
            <span><i class="fas fa-cloud-arrow-up"></i></span>
            <div><small>ENTREGABLE DE TAREA</small><h2 id="task-upload-drawer-title">Subir contenido</h2></div>
            <button type="button" data-close-task-upload aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>

        <form id="task-upload-drawer-form" method="POST" enctype="multipart/form-data" action="{{ $taskUploadToReopen ? route('administrador.tareas.archivos.store', $taskUploadToReopen) : '#' }}">
            @csrf
            <input type="hidden" name="contexto" value="campania">
            <div class="task-upload-drawer-body">
                <div class="task-upload-context"><i class="fas fa-list-check"></i><div><small>Tarea seleccionada</small><strong id="task-upload-selected-title">{{ $taskUploadToReopen?->titulo ?? 'Selecciona una tarea' }}</strong></div></div>

                @if($taskUploadErrors->any())
                    <div class="task-upload-errors"><i class="fas fa-circle-exclamation"></i><div>@foreach($taskUploadErrors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif

                <div class="task-upload-field-label"><i class="fas fa-files"></i> Seleccionar archivos <span>*</span></div>
                <label class="task-upload-dropzone" for="task-upload-files">
                    <input id="task-upload-files" type="file" name="archivos[]" multiple required accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.pdf,.ai,.mp3,.wav,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.psd,.svg,.webp">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <strong>Haz clic o arrastra tus archivos aquí</strong>
                    <small>Imágenes, videos, audio, documentos y archivos comprimidos.</small>
                    <b>Máximo aproximado de 1 GB por archivo</b>
                </label>
                <div class="task-upload-files-list" id="task-upload-files-list" hidden><strong><i class="fas fa-list"></i> Archivos seleccionados</strong><ul></ul></div>

                <label class="task-upload-description" for="task-upload-description">
                    <span><i class="fas fa-align-left"></i> Descripción <small>(opcional)</small></span>
                    <textarea id="task-upload-description" name="descripcion" rows="4" placeholder="Describe el propósito de estos archivos...">{{ old('descripcion') }}</textarea>
                </label>
            </div>
            <footer>
                <button type="button" data-close-task-upload><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit"><i class="fas fa-upload"></i> Subir archivos</button>
            </footer>
        </form>
    </aside>
</div>

<style>
    .tasks-workspace{overflow:hidden;border:1px solid #d8e3c7;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(91,121,38,.08)}.tasks-workspace .is-hidden{display:none!important}
    .tasks-toolbar{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:14px 16px;border-bottom:1px solid #dfe8d1;background:#f8fbf4}.tasks-view-switch{display:flex;padding:4px;border:1px solid #d8e3c7;border-radius:10px;background:#edf4e4}.tasks-view-switch button{min-height:36px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border:0;border-radius:7px;background:transparent;color:#66745a;font-size:.63rem;font-weight:900;cursor:pointer}.tasks-view-switch button.is-active{background:#fff;color:#638524;box-shadow:0 3px 9px rgba(91,121,38,.14)}.tasks-primary-action{min-height:38px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 15px;border-radius:9px;background:#7da533;color:#fff;font-size:.67rem;font-weight:900;text-decoration:none;transition:.18s}.tasks-primary-action:hover{transform:translateY(-1px);background:#638524}
    .tasks-board{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:16px;background:#f4f7f0}.task-column{--column-color:#7da533;min-width:0;display:flex;flex-direction:column;overflow:hidden;border:1px solid #dbe5cd;border-top:4px solid var(--column-color);border-radius:12px;background:#eef2eb}.task-column.is-en-progreso{--column-color:#4f8f59}.task-column.is-completada{--column-color:#327a56}.task-column-header{display:flex;align-items:center;justify-content:space-between;padding:13px 14px;border-bottom:1px solid #dce4d5;background:#fff}.task-column-header>div{display:flex;align-items:center;gap:8px}.task-column-header i{color:var(--column-color);font-size:.72rem}.task-column-header h3{margin:0;color:#35402f;font-size:.75rem;font-weight:900}.task-column-header>span{min-width:25px;height:25px;display:grid;place-items:center;border-radius:999px;background:#edf4e4;color:#638524;font-size:.6rem;font-weight:900}.task-column-body{min-height:260px;display:flex;flex:1;flex-direction:column;gap:10px;padding:10px;transition:.18s}.task-column-body.is-drag-over{background:#e1edd5;box-shadow:inset 0 0 0 2px #7da533}.task-column-add{display:flex;align-items:center;gap:7px;margin:0 10px 10px;padding:9px 10px;border-radius:8px;color:#638524;font-size:.62rem;font-weight:900;text-decoration:none}.task-column-add:hover{background:#e1ead8}.task-column-empty{display:none;min-height:82px;place-items:center;align-content:center;gap:6px;border:1px dashed #bdcbae;border-radius:9px;color:#859279;font-size:.59rem;font-weight:800}.task-column-body:not(:has(.task-kanban-card)) .task-column-empty{display:grid}
    .task-kanban-card{position:relative;padding:13px;border:1px solid #dce3d6;border-radius:10px;background:#fff;box-shadow:0 3px 9px rgba(55,70,47,.08);cursor:grab;transition:transform .18s,box-shadow .18s,opacity .18s}.task-kanban-card:hover{transform:translateY(-2px);box-shadow:0 7px 16px rgba(91,121,38,.14)}.task-kanban-card.is-dragging{opacity:.45;cursor:grabbing}.task-card-accent{position:absolute;top:0;right:0;left:0;height:3px;border-radius:10px 10px 0 0;background:#7da533}.task-card-accent.is-medium{background:#3b82f6}.task-card-accent.is-high{background:#f59e0b}.task-card-accent.is-urgent{background:#dc3545}.task-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}.task-card-top h4{margin:0;color:#2e3829;font-size:.72rem;font-weight:900;line-height:1.4}.task-card-priority{flex:0 0 auto;padding:4px 6px;border-radius:999px;background:#edf4e4;color:#638524;font-size:.5rem;font-weight:900;text-transform:uppercase}.task-card-priority.is-medium{background:#eaf1ff;color:#355ca8}.task-card-priority.is-high{background:#fff4da;color:#9a6512}.task-card-priority.is-urgent{background:#fff0f0;color:#a72d2d}.task-card-deliverable{display:block;margin-top:6px;overflow:hidden;color:#7d8776;font-size:.56rem;text-overflow:ellipsis;white-space:nowrap}.task-card-labels{display:flex;flex-wrap:wrap;gap:5px;margin-top:9px}.task-card-labels span{padding:4px 6px;border-radius:5px;background:#eef3e9;color:#63705b;font-size:.49rem;font-weight:850}.task-card-labels .is-visible{background:#e6f4f5;color:#117e8c}.task-card-info{display:grid;gap:7px;margin-top:11px;padding-top:10px;border-top:1px solid #edf0e9}.task-card-info>div{display:flex;align-items:center;gap:7px;min-width:0;color:#687164;font-size:.56rem;font-weight:750}.task-card-info i{width:14px;color:#7da533;text-align:center}.task-card-info span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-card-info .is-overdue{color:#b23e2c}.task-card-status-select{display:grid;grid-template-columns:auto auto minmax(0,1fr);align-items:center;gap:6px;margin-top:10px;color:#697565;font-size:.53rem;font-weight:900}.task-card-status-select i{color:#7da533}.task-card-status-select select{min-width:0;height:29px;padding:0 7px;border:1px solid #d8e3c7;border-radius:6px;background:#f8fbf4;color:#52604a;font-size:.54rem;font-weight:800;cursor:pointer}.task-card-publish{min-height:38px;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:11px;padding:0 11px;border:1px solid #d85b16;border-radius:8px;background:linear-gradient(135deg,#ef6c22,#c94f0c);color:#fff;font-size:.59rem;font-weight:900;text-decoration:none;box-shadow:0 7px 15px rgba(201,79,12,.24);transition:.18s}.task-card-publish span{flex:1;text-align:center}.task-card-publish:hover{transform:translateY(-1px);filter:brightness(.96);box-shadow:0 10px 19px rgba(201,79,12,.3)}.task-card-actions{display:flex;gap:5px;margin-top:11px}.task-card-actions a,.task-card-actions button{flex:1;padding:6px 5px;border:1px solid #dce4d5;border-radius:6px;background:#fafcf8;color:#5e6a56;font:inherit;font-size:.52rem;font-weight:900;text-align:center;text-decoration:none;cursor:pointer}.task-card-actions a:hover,.task-card-actions button:hover{border-color:#7da533;background:#edf4e4;color:#587923}
    .tasks-table-filters{display:flex;align-items:center;flex-wrap:wrap;gap:7px;padding:13px 16px;border-bottom:1px solid #dfe8d1;background:#fff}.tasks-table-filters button{min-height:34px;display:inline-flex;align-items:center;gap:7px;padding:0 11px;border:1px solid #d8e3c7;border-radius:8px;background:#fff;color:#66745a;font-size:.59rem;font-weight:900;cursor:pointer}.tasks-table-filters button>span{min-width:20px;height:20px;display:grid;place-items:center;border-radius:999px;background:#edf4e4;color:#638524;font-size:.52rem}.tasks-table-filters button:hover,.tasks-table-filters button.is-active{border-color:#7da533;background:#7da533;color:#fff}.tasks-table-filters button.is-active>span{background:rgba(255,255,255,.22);color:#fff}.tasks-table-wrap{overflow-x:auto}.tasks-table{width:100%;min-width:920px;border-collapse:collapse}.tasks-table th{padding:12px 16px;border-right:1px solid rgba(255,255,255,.28);background:#7da533;color:#fff;font-size:.57rem;font-weight:900;letter-spacing:.06em;text-align:left;text-transform:uppercase}.tasks-table th:last-child{border-right:0}.tasks-table td{padding:14px 16px;border-right:1px solid #dfe8d1;border-bottom:1px solid #dfe8d1;color:#3e443c;font-size:.68rem;vertical-align:middle}.tasks-table td:last-child{border-right:0}.tasks-table tbody tr:nth-child(even){background:#f5f9ef}.tasks-table tbody tr:hover{background:#eaf2df}.tasks-table td>strong,.tasks-table td>small{display:block}.tasks-table td>strong{font-size:.69rem;font-weight:800}.tasks-table td>small{margin-top:4px;color:#8a9187;font-size:.57rem}.task-title{color:#262b25!important;font-size:.72rem!important;font-weight:900!important}.task-badge{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:.56rem;font-weight:850;white-space:nowrap}.task-badge.is-pending,.task-badge.is-low{background:#edf7e5;color:#547a27}.task-badge.is-progress{background:#e6f4f5;color:#117e8c}.task-badge.is-complete{background:#dff3e5;color:#327a56}.task-badge.is-medium{background:#eaf1ff;color:#355ca8}.task-badge.is-high{background:#fff4da;color:#9a6512}.task-badge.is-cancelled,.task-badge.is-urgent{background:#fff0f0;color:#a72d2d}.task-actions{display:flex;gap:5px}.task-actions a,.task-actions button{padding:6px 8px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#51594f;font:inherit;font-size:.56rem;font-weight:850;text-decoration:none;cursor:pointer}.task-actions a:hover,.task-actions button:hover{border-color:#7da533;color:#638524}.task-meta-labels{display:flex;flex-wrap:wrap;gap:5px;margin-top:7px}.task-meta-labels span{display:inline-flex;align-items:center;gap:4px;padding:4px 7px;border-radius:999px;background:#f1f4ed;color:#5c6856;font-size:.5rem;font-weight:850}.task-meta-labels .is-visible{background:#edf7e5;color:#547a27}.task-meta-labels .is-approval{background:#fff3e8;color:#b65316}
    .tasks-empty,.tasks-filter-empty{padding:45px 24px;text-align:center}.tasks-empty>i,.tasks-filter-empty>i{display:block;margin-bottom:10px;color:#9eb77c;font-size:1.6rem}.tasks-empty h3,.tasks-filter-empty strong{margin:0;color:#2e332d;font-size:.82rem;font-weight:900}.tasks-empty p{margin:7px 0 18px;color:#7b8278;font-size:.68rem}.tasks-empty a{display:inline-flex;padding:9px 13px;border-radius:8px;background:#7da533;color:#fff;font-size:.65rem;font-weight:900;text-decoration:none}.tasks-status-message{position:fixed;z-index:100;right:24px;bottom:24px;max-width:320px;transform:translateY(20px);padding:11px 14px;border-radius:9px;background:#327a56;color:#fff;font-size:.65rem;font-weight:850;opacity:0;pointer-events:none;transition:.2s}.tasks-status-message.is-visible{transform:translateY(0);opacity:1}.tasks-status-message.is-error{background:#b23e2c}
    .task-upload-drawer{position:fixed;z-index:10100;inset:0;display:flex;justify-content:flex-end}.task-upload-drawer[hidden]{display:none}.task-upload-drawer-backdrop{position:absolute;inset:0;border:0;background:rgba(20,25,18,.72);backdrop-filter:blur(3px);cursor:pointer}.task-upload-drawer-panel{position:relative;width:min(560px,100%);height:100%;display:flex;flex-direction:column;background:#fff;box-shadow:-24px 0 60px rgba(25,35,20,.28);animation:taskDrawerIn .24s ease both}@keyframes taskDrawerIn{from{transform:translateX(100%)}to{transform:translateX(0)}}.task-upload-drawer-panel>header{display:flex;align-items:center;gap:14px;padding:22px 24px;background:#638522;color:#fff}.task-upload-drawer-panel>header>span{width:50px;height:50px;display:grid;place-items:center;flex:0 0 50px;border-radius:13px;background:#fff;color:#638522;font-size:1.2rem}.task-upload-drawer-panel>header>div{min-width:0;flex:1}.task-upload-drawer-panel>header small,.task-upload-drawer-panel>header h2{display:block;margin:0}.task-upload-drawer-panel>header small{color:#e5f0d4;font-size:.55rem;font-weight:900;letter-spacing:.11em}.task-upload-drawer-panel>header h2{margin-top:3px;font-size:1.25rem;font-weight:900}.task-upload-drawer-panel>header>button{width:38px;height:38px;border:1px solid #91aa62;border-radius:9px;background:#4f6d19;color:#fff;cursor:pointer}.task-upload-drawer-panel>form{min-height:0;display:flex;flex:1;flex-direction:column}.task-upload-drawer-body{min-height:0;flex:1;overflow-y:auto;padding:24px}.task-upload-context{display:flex;align-items:center;gap:11px;margin-bottom:20px;padding:13px;border-left:4px solid #ef6c22;background:#fff5ed}.task-upload-context>i{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:9px;background:#ef6c22;color:#fff}.task-upload-context small,.task-upload-context strong{display:block}.task-upload-context small{color:#a16236;font-size:.53rem;font-weight:900;text-transform:uppercase}.task-upload-context strong{overflow:hidden;margin-top:3px;color:#573c2c;font-size:.7rem;text-overflow:ellipsis;white-space:nowrap}.task-upload-errors{display:flex;gap:9px;margin-bottom:17px;padding:12px 13px;border-left:4px solid #b52f2f;background:#fff0f0;color:#a32f2f;font-size:.64rem}.task-upload-errors p{margin:0}.task-upload-errors p+p{margin-top:3px}.task-upload-field-label{margin-bottom:9px;color:#4b5148;font-size:.67rem;font-weight:900}.task-upload-field-label>i{margin-right:7px;color:#9ca3af}.task-upload-field-label>span{color:#dc2626}.task-upload-dropzone{position:relative;min-height:210px;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:26px;border:2px dashed #cfd5cb;border-radius:12px;background:#f9faf8;text-align:center;cursor:pointer;transition:.2s}.task-upload-dropzone:hover,.task-upload-dropzone.is-dragging{border-color:#7da533;background:#f3f7ed}.task-upload-dropzone input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.task-upload-dropzone>i{color:#cbd1c7;font-size:2.7rem}.task-upload-dropzone strong{margin-top:12px;color:#687164;font-size:.72rem}.task-upload-dropzone small{max-width:420px;margin-top:7px;color:#929a8e;font-size:.56rem}.task-upload-dropzone b{margin-top:8px;color:#7da533;font-size:.54rem}.task-upload-files-list{margin-top:12px;padding:12px 14px;border:1px solid #e0e4dd;border-radius:10px;background:#fff}.task-upload-files-list>strong{display:block;margin-bottom:6px;color:#596154;font-size:.59rem}.task-upload-files-list>strong i{margin-right:5px;color:#7da533}.task-upload-files-list ul{max-height:145px;overflow-y:auto;margin:0;padding:0;list-style:none}.task-upload-files-list li{display:flex;align-items:center;gap:8px;padding:8px 2px;border-bottom:1px solid #edf0ea;color:#747d70;font-size:.56rem}.task-upload-files-list li:last-child{border-bottom:0}.task-upload-files-list li i{color:#9ca3af}.task-upload-description{display:block;margin-top:20px}.task-upload-description>span{display:block;margin-bottom:8px;color:#4b5148;font-size:.67rem;font-weight:900}.task-upload-description>span i{margin-right:7px;color:#9ca3af}.task-upload-description>span small{color:#929a8e;font-weight:600}.task-upload-description textarea{width:100%;padding:12px 13px;border:1px solid #d5dad2;border-radius:11px;background:#f9faf8;color:#384034;font:inherit;font-size:.68rem;resize:vertical;outline:none}.task-upload-description textarea:focus{border-color:#7da533;background:#fff;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.task-upload-drawer-panel footer{display:flex;justify-content:space-between;gap:12px;padding:17px 24px;border-top:1px solid #e1e5de;background:#fff}.task-upload-drawer-panel footer button{min-height:45px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 18px;border:1px solid #d1d6ce;border-radius:11px;background:#fff;color:#626b5e;font-size:.65rem;font-weight:900;cursor:pointer}.task-upload-drawer-panel footer button[type=submit]{border-color:#638522;background:#638522;color:#fff;box-shadow:0 8px 18px rgba(99,133,34,.22)}.task-upload-drawer-panel footer button[type=submit]:hover{transform:translateY(-1px);background:#526f1c}
    @media(max-width:1000px){.tasks-board{grid-template-columns:1fr}.task-column-body{min-height:150px}}
    @media(max-width:640px){.tasks-toolbar{align-items:stretch;flex-direction:column}.tasks-view-switch{display:grid;grid-template-columns:1fr 1fr}.tasks-primary-action{width:100%}.tasks-table-filters{display:grid;grid-template-columns:1fr 1fr}.tasks-table-filters button{justify-content:center}.task-upload-drawer-panel{width:100%}.task-upload-drawer-body{padding:18px}.task-upload-drawer-panel footer{padding:14px 18px}.task-upload-drawer-panel footer button{flex:1}}
</style>

<script>
function initializeTaskWorkspace() {
    const workspace = document.querySelector('[data-task-workspace]');
    if (!workspace) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const statusNames = { pendiente: 'Por hacer', en_progreso: 'Haciendo', completada: 'Hecho' };
    const statusClasses = { pendiente: 'is-pending', en_progreso: 'is-progress', completada: 'is-complete' };
    let draggedCard = null;
    let messageTimer = null;

    function showMessage(text, isError = false) {
        const message = workspace.querySelector('[data-task-message]');
        message.textContent = text;
        message.classList.toggle('is-error', isError);
        message.classList.add('is-visible');
        clearTimeout(messageTimer);
        messageTimer = setTimeout(() => message.classList.remove('is-visible'), 2600);
    }
    function refreshCounts() {
        workspace.querySelectorAll('[data-task-column]').forEach(column => {
            const status = column.dataset.taskColumn;
            const count = column.querySelectorAll('.task-kanban-card').length;
            column.querySelector('[data-column-count]').textContent = count;
            const filterCount = workspace.querySelector(`[data-task-filter="${status}"] span`);
            if (filterCount) filterCount.textContent = count;
        });
    }
    function syncTableRow(taskId, status) {
        const row = workspace.querySelector(`[data-task-row][data-task-id="${taskId}"]`);
        if (!row) return;
        row.dataset.taskStatus = status;
        const badge = row.querySelector('[data-row-status-badge]');
        badge.className = `task-badge ${statusClasses[status]}`;
        badge.textContent = statusNames[status];
        applyTableFilter(workspace.querySelector('[data-task-filter].is-active')?.dataset.taskFilter || 'all');
    }
    function applyTableFilter(filter) {
        let visible = 0;
        workspace.querySelectorAll('[data-task-row]').forEach(row => { const show = filter === 'all' || row.dataset.taskStatus === filter; row.classList.toggle('is-hidden', !show); if (show) visible++; });
        workspace.querySelector('[data-filter-empty]')?.classList.toggle('is-hidden', visible > 0);
        workspace.querySelector('.tasks-table-wrap')?.classList.toggle('is-hidden', visible === 0);
    }
    async function persistStatus(card, status) {
        const response = await fetch(card.dataset.statusUrl, {
            method: 'PATCH',
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({estado: status}),
        });
        if (!response.ok) throw new Error('No se pudo actualizar el estado');
        card.dataset.taskStatus = status;
        const statusSelect = card.querySelector('[data-card-status]');
        if (statusSelect) statusSelect.value = status;
        syncTableRow(card.dataset.taskId, status);
        refreshCounts();
        showMessage(`Tarea movida a ${statusNames[status]}.`);
    }

    workspace.querySelectorAll('.task-kanban-card').forEach(card => {
        card.addEventListener('dragstart', event => { draggedCard = card; card.classList.add('is-dragging'); event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', card.dataset.taskId); });
        card.addEventListener('dragend', () => { card.classList.remove('is-dragging'); workspace.querySelectorAll('[data-task-dropzone]').forEach(zone => zone.classList.remove('is-drag-over')); draggedCard = null; });
        card.querySelector('[data-card-status]')?.addEventListener('change', async event => {
            const status = event.target.value;
            if (status === card.dataset.taskStatus) return;
            const previousZone = card.parentElement;
            const previousStatus = card.dataset.taskStatus;
            const targetZone = workspace.querySelector(`[data-task-dropzone="${status}"]`);
            targetZone.insertBefore(card, targetZone.querySelector('[data-column-empty]'));
            refreshCounts();
            try { await persistStatus(card, status); }
            catch (error) { previousZone.insertBefore(card, previousZone.querySelector('[data-column-empty]')); card.dataset.taskStatus = previousStatus; event.target.value = previousStatus; refreshCounts(); showMessage('No se pudo mover la tarea. Inténtalo nuevamente.', true); }
        });
    });
    workspace.querySelectorAll('[data-task-dropzone]').forEach(zone => {
        zone.addEventListener('dragover', event => { event.preventDefault(); event.dataTransfer.dropEffect = 'move'; zone.classList.add('is-drag-over'); });
        zone.addEventListener('dragleave', event => { if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-drag-over'); });
        zone.addEventListener('drop', async event => {
            event.preventDefault();
            zone.classList.remove('is-drag-over');
            if (!draggedCard || draggedCard.dataset.taskStatus === zone.dataset.taskDropzone) return;
            const card = draggedCard;
            const previousZone = card.parentElement;
            const previousStatus = card.dataset.taskStatus;
            zone.insertBefore(card, zone.querySelector('[data-column-empty]'));
            refreshCounts();
            try { await persistStatus(card, zone.dataset.taskDropzone); }
            catch (error) { previousZone.insertBefore(card, previousZone.querySelector('[data-column-empty]')); card.dataset.taskStatus = previousStatus; refreshCounts(); showMessage('No se pudo mover la tarea. Inténtalo nuevamente.', true); }
        });
    });
    workspace.querySelectorAll('[data-task-view]').forEach(button => button.addEventListener('click', function () {
        const view = button.dataset.taskView;
        workspace.querySelectorAll('[data-task-view]').forEach(item => { const active = item === button; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        workspace.querySelectorAll('[data-task-panel]').forEach(panel => panel.classList.toggle('is-hidden', panel.dataset.taskPanel !== view));
    }));
    workspace.querySelectorAll('[data-task-filter]').forEach(button => button.addEventListener('click', function () {
        const filter = button.dataset.taskFilter;
        workspace.querySelectorAll('[data-task-filter]').forEach(item => item.classList.toggle('is-active', item === button));
        applyTableFilter(filter);
    }));

    const uploadDrawer = document.getElementById('task-upload-drawer');
    const uploadForm = document.getElementById('task-upload-drawer-form');
    const uploadTitle = document.getElementById('task-upload-selected-title');
    const uploadFiles = document.getElementById('task-upload-files');
    const uploadFilesList = document.getElementById('task-upload-files-list');
    const uploadDropzone = uploadDrawer?.querySelector('.task-upload-dropzone');

    function renderUploadFiles() {
        if (!uploadFiles || !uploadFilesList) return;
        const files = Array.from(uploadFiles.files || []);
        const list = uploadFilesList.querySelector('ul');
        list.replaceChildren(...files.map(file => {
            const item = document.createElement('li');
            const icon = document.createElement('i');
            icon.className = 'fas fa-file';
            const name = document.createElement('span');
            const size = file.size >= 1048576 ? `${(file.size / 1048576).toFixed(2)} MB` : `${(file.size / 1024).toFixed(1)} KB`;
            name.textContent = `${file.name} (${size})`;
            item.append(icon, name);
            return item;
        }));
        uploadFilesList.hidden = files.length === 0;
    }

    function openUploadDrawer(button = null) {
        if (!uploadDrawer || !uploadForm) return;
        if (button) {
            uploadForm.action = button.dataset.uploadUrl;
            uploadTitle.textContent = button.dataset.taskTitle;
        }
        uploadDrawer.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeUploadDrawer() {
        if (!uploadDrawer) return;
        uploadDrawer.hidden = true;
        document.body.style.overflow = '';
    }

    workspace.querySelectorAll('[data-open-task-upload]').forEach(button => button.addEventListener('click', () => openUploadDrawer(button)));
    uploadDrawer?.querySelectorAll('[data-close-task-upload]').forEach(button => button.addEventListener('click', closeUploadDrawer));
    uploadFiles?.addEventListener('change', renderUploadFiles);
    ['dragenter', 'dragover'].forEach(eventName => uploadDropzone?.addEventListener(eventName, () => uploadDropzone.classList.add('is-dragging')));
    ['dragleave', 'drop'].forEach(eventName => uploadDropzone?.addEventListener(eventName, () => uploadDropzone.classList.remove('is-dragging')));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && uploadDrawer && !uploadDrawer.hidden) closeUploadDrawer(); });
    if (uploadDrawer?.dataset.openOnLoad === 'true') openUploadDrawer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTaskWorkspace, { once: true });
} else {
    initializeTaskWorkspace();
}
</script>
