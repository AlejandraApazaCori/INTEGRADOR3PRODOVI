@php
    $responsablesTarjeta = $tarea->responsables->isNotEmpty() ? $tarea->responsables : ($tarea->asignado ? collect([$tarea->asignado]) : collect());
    $nombresResponsables = $responsablesTarjeta->pluck('name')->implode(', ') ?: 'Sin asignar';
    $diasRestantesTarjeta = (int) now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false);
    $prioridadTarjeta = in_array($tarea->prioridad, ['media', 'alta', 'urgente'], true) ? 'is-'.$tarea->prioridad : '';
    $estadoKanban = $estadoKanban ?? ($tarea->estado === 'rechazada' ? 'pendiente' : $tarea->estado);
    $puedePublicarse = $tarea->archivos->contains('estado', 'aprobado');
@endphp

<article class="task-kanban-card" draggable="true" data-task-id="{{ $tarea->id }}" data-task-status="{{ $estadoKanban }}" data-status-url="{{ route('administrador.tareas.update-estado', $tarea->id) }}">
    <span class="task-card-accent {{ $prioridadTarjeta }}"></span>
    <div class="task-card-top"><h4>{{ $tarea->titulo }}</h4><span class="task-card-priority {{ $prioridadTarjeta }}">{{ ucfirst($tarea->prioridad) }}</span></div>
    <span class="task-card-deliverable">{{ $tarea->entregable ? 'Entregable: '.$tarea->entregable : 'Tarea #'.$tarea->id }}</span>
    @if($tarea->tipo_contenido || $tarea->visible_cliente || $tarea->requiere_aprobacion)
        <div class="task-card-labels">
            @if($tarea->tipo_contenido)<span>{{ ucfirst($tarea->tipo_contenido) }}</span>@endif
            @if($tarea->visible_cliente)<span class="is-visible"><i class="fas fa-eye"></i> Cliente</span>@endif
            @if($tarea->requiere_aprobacion)<span><i class="fas fa-user-check"></i> Aprobación</span>@endif
        </div>
    @endif
    <div class="task-card-info">
        <div title="{{ $nombresResponsables }}"><i class="fas fa-user-group"></i><span>{{ $nombresResponsables }}</span></div>
        <div class="{{ $diasRestantesTarjeta < 0 && $tarea->estado !== 'completada' ? 'is-overdue' : '' }}"><i class="fas fa-clock"></i><span>{{ $tarea->fecha_limite->format('d/m/Y') }} · {{ $diasRestantesTarjeta >= 0 ? ($diasRestantesTarjeta === 0 ? 'Vence hoy' : 'Faltan '.$diasRestantesTarjeta.' días') : 'Vencida' }}</span></div>
    </div>
    <label class="task-card-status-select"><i class="fas fa-arrows-left-right"></i><span>Mover a</span><select data-card-status aria-label="Cambiar estado de {{ $tarea->titulo }}"><option value="pendiente" @selected($estadoKanban === 'pendiente')>Por hacer</option><option value="en_progreso" @selected($estadoKanban === 'en_progreso')>Haciendo</option><option value="completada" @selected($estadoKanban === 'completada')>Hecho</option></select></label>
    @if($puedePublicarse)
        <a href="{{ route('administrador.publicaciones.publicar', ['tarea_id' => $tarea->id]) }}" class="task-card-publish"><i class="fas fa-rocket"></i><span>Publicar contenido</span><i class="fas fa-arrow-right"></i></a>
    @endif
    <div class="task-card-actions">
        @if($embeddedUploadDrawer ?? false)
            <button type="button" data-open-task-upload data-task-title="{{ $tarea->titulo }}" data-upload-url="{{ route('administrador.tareas.archivos.store', $tarea->id) }}">Subir</button>
        @else
            <a href="{{ route('administrador.tareas.archivos.create', $tarea->id) }}">Subir</a>
        @endif
        <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}">Revisar</a><a href="{{ route('administrador.tareas.edit', $tarea->id) }}">Editar</a>
    </div>
</article>
