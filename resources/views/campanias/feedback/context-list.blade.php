@if($contextRows->isNotEmpty())
    <div class="feedback-context-grid">
        @foreach($contextRows as $context)
            @php
                $isCustom = $context->contexto_id !== null;
                $isGeneral = $context->tarea_id === null && ! $isCustom;
                $task = $isGeneral || $isCustom ? null : $tasks->get($context->tarea_id);
                $customContext = $isCustom ? $customContexts->get($context->contexto_id) : null;
                $contextValue = $isGeneral ? 'general' : ($isCustom ? 'custom:'.($customContext?->id) : $task?->id);
                $contextTitle = $isGeneral ? 'Campaña general' : ($isCustom ? $customContext?->nombre : $task?->titulo);
            @endphp
            @if($isGeneral || $task || $customContext)
                <button type="button" class="feedback-context-card {{ $isGeneral ? 'is-general' : '' }}"
                    data-feedback-context="{{ $contextValue }}"
                    data-context-title="{{ $contextTitle }}">
                    <span class="feedback-context-icon"><i class="fas {{ $isGeneral ? 'fa-bullhorn' : ($isCustom ? 'fa-comments' : 'fa-list-check') }}"></i></span>
                    <span>
                        <small>{{ $isGeneral ? 'Contexto principal' : ($isCustom ? 'Contexto personalizado' : ucfirst(str_replace('_', ' ', $task->estado))) }}</small>
                        <strong>{{ $contextTitle }}</strong>
                        <em>{{ $context->total }} {{ (int) $context->total === 1 ? 'mensaje' : 'mensajes' }}</em>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            @endif
        @endforeach
    </div>
@else
    <div class="feedback-context-empty">
        <i class="far fa-comments"></i>
        <strong>No hay conversaciones en esta subpestaña</strong>
        <p>Los contextos aparecerán aquí cuando contengan al menos un mensaje.</p>
    </div>
@endif
