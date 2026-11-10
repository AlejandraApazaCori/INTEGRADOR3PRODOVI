@php
    $responsablesTarjeta = $tarea->responsables->isNotEmpty() ? $tarea->responsables : ($tarea->asignado ? collect([$tarea->asignado]) : collect());
    $nombresResponsables = $responsablesTarjeta->pluck('name')->implode(', ') ?: 'Sin asignar';
    $diasRestantesTarjeta = (int) now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false);
    $prioridadTarjeta = in_array($tarea->prioridad, ['media', 'alta', 'urgente'], true) ? 'is-'.$tarea->prioridad : '';
    $estadoKanban = $estadoKanban ?? $tarea->estado;
    $archivoAprobadoTarjeta = $tarea->archivos->where('estado', 'aprobado')->sortByDesc('revisado_equipo_at')->first();
    $puedePublicarse = $tarea->estado === 'aprobado' && $tarea->archivos->contains(fn ($file) => $file->estado === 'aprobado' && (! $tarea->revision_cliente_habilitada || $file->revision_cliente_estado === 'aprobado'));
@endphp

<article class="task-kanban-card" draggable="true" data-task-id="{{ $tarea->id }}" data-task-status="{{ $estadoKanban }}" data-status-url="{{ route('administrador.tareas.update-estado', $tarea->id) }}" data-publish-url="{{ route('administrador.publicaciones.publicar', ['tarea_id' => $tarea->id]) }}">
    <span class="task-card-accent {{ $prioridadTarjeta }}"></span>
    <div class="task-card-top"><h4>{{ $tarea->titulo }}</h4><span class="task-card-priority {{ $prioridadTarjeta }}">{{ ucfirst($tarea->prioridad) }}</span></div>
    <span class="task-card-deliverable">{{ $tarea->entregable ? 'Entregable: '.$tarea->entregable : 'Tarea #'.$tarea->id }}</span>
    @if($tarea->tipo_contenido || $tarea->visible_cliente || $tarea->requiere_aprobacion || $tarea->revision_cliente_habilitada)
        <div class="task-card-labels">
            @if($tarea->tipo_contenido)<span>{{ ucfirst($tarea->tipo_contenido) }}</span>@endif
            @if($tarea->visible_cliente)<span class="is-visible"><i class="fas fa-eye"></i> Cliente</span>@endif
            @if($tarea->requiere_aprobacion)<span><i class="fas fa-user-check"></i> Aprobación</span>@endif
            @if($tarea->revision_cliente_habilitada)<span><i class="fas fa-clipboard-check"></i> Revisión cliente</span>@endif
        </div>
    @endif
    <div class="task-card-info">
        <div title="{{ $nombresResponsables }}"><i class="fas fa-user-group"></i><span>{{ $nombresResponsables }}</span></div>
        @if($archivoAprobadoTarjeta?->revisado_equipo_at)<div><i class="fas fa-user-check"></i><span>Equipo: {{ $archivoAprobadoTarjeta->revisorEquipo?->name ?? 'Usuario eliminado' }}</span></div>@endif
        @if($tarea->revision_cliente_habilitada && $archivoAprobadoTarjeta)<div><i class="fas fa-clipboard-check"></i><span>Cliente: {{ $archivoAprobadoTarjeta->revision_cliente_estado === 'aprobado' ? ($archivoAprobadoTarjeta->revisorCliente?->name ?? 'Aprobado') : ($archivoAprobadoTarjeta->revision_cliente_estado === 'rechazado' ? 'Solicitó cambios' : 'Pendiente') }}</span></div>@endif
        <div class="{{ $diasRestantesTarjeta < 0 && ! in_array($tarea->estado, ['entregado', 'aprobado', 'publicado'], true) ? 'is-overdue' : '' }}"><i class="fas fa-clock"></i><span>{{ $tarea->fecha_limite->format('d/m/Y') }} · {{ $diasRestantesTarjeta >= 0 ? ($diasRestantesTarjeta === 0 ? 'Vence hoy' : 'Faltan '.$diasRestantesTarjeta.' días') : 'Vencida' }}</span></div>
    </div>
    <div class="task-card-status-select" data-status-dropdown>
        <i class="fas fa-arrows-left-right"></i><span>Mover a</span>
        <div class="task-status-dropdown-control">
            <button type="button" data-status-trigger aria-expanded="false" aria-label="Cambiar estado de {{ $tarea->titulo }}"><b data-status-label>{{ ['no_iniciado' => 'No iniciado', 'pendiente' => 'Pendiente', 'en_curso' => 'En curso', 'entregado' => 'Entregado', 'reformular' => 'Reformular', 'aprobado' => 'Aprobado', 'publicado' => 'Publicado'][$estadoKanban] ?? 'No iniciado' }}</b><i class="fas fa-chevron-down"></i></button>
            <div class="task-status-dropdown-menu" data-status-menu hidden>
                @foreach(['no_iniciado' => 'No iniciado', 'pendiente' => 'Pendiente', 'en_curso' => 'En curso', 'entregado' => 'Entregado', 'reformular' => 'Reformular', 'aprobado' => 'Aprobado', 'publicado' => 'Publicado'] as $valorEstado => $nombreEstado)
                    <button type="button" data-status-option="{{ $valorEstado }}" class="{{ $estadoKanban === $valorEstado ? 'is-selected' : '' }}"><span>{{ $nombreEstado }}</span><i class="fas fa-check"></i></button>
                @endforeach
            </div>
        </div>
    </div>
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
