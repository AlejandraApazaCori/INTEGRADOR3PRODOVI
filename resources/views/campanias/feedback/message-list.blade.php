@php
    $canModerateMessages = $user->hasAnyRole(['Super Administrador', 'Administrador']);
@endphp
@forelse($messages as $message)
    @php
        $isOwn = (int) $message->remitente_id === (int) $user->id;
        $senderRole = $message->remitente?->roles?->pluck('nombre_rol')->first() ?? 'Participante';
        $audienceLabel = match($message->audiencia) {
            'equipo' => 'Equipo',
            'cliente_equipo' => 'Cliente ↔ equipo',
            default => 'Directo',
        };
        $recipientNames = $message->audiencia === 'directo'
            ? $message->destinatarios->pluck('name')->implode(', ')
            : null;
        $directReplyTarget = $message->audiencia === 'directo'
            ? ($isOwn ? $message->destinatarios->first()?->id : $message->remitente_id)
            : null;
        $displayContent = preg_replace('/\*\*([^*\r\n]*?\S)\h+\*\*/u', '**$1** ', $message->contenido);
    @endphp
    <article class="feedback-message {{ $isOwn ? 'is-own' : '' }} {{ $message->mensaje_padre_id ? 'is-reply' : '' }}" data-message-id="{{ $message->id }}" data-message-content="{{ base64_encode($message->contenido) }}">
        <span class="feedback-message-avatar">{{ strtoupper(mb_substr($message->remitente?->name ?? 'U', 0, 2)) }}</span>
        <div class="feedback-message-content">
            <header>
                <div>
                    <strong>{{ $message->remitente?->name ?? 'Usuario eliminado' }}</strong>
                    <span>{{ $senderRole }}</span>
                </div>
                <time datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->format('d/m/Y H:i') }}">{{ $message->created_at->diffForHumans() }}</time>
            </header>
            <div class="feedback-message-meta">
                <span class="is-{{ $message->audiencia }}"><i class="fas {{ $message->audiencia === 'directo' ? 'fa-user' : ($message->audiencia === 'equipo' ? 'fa-users' : 'fa-people-arrows') }}"></i>{{ $audienceLabel }}</span>
                @if($recipientNames)<small>Para {{ $recipientNames }}</small>@endif
                @if($message->tarea)<span class="is-task"><i class="fas fa-list-check"></i>{{ $message->tarea->titulo }}</span>@endif
                @if($message->contexto)<span class="is-task"><i class="fas fa-comments"></i>{{ $message->contexto->nombre }}</span>@endif
            </div>
            @if($message->padre)
                <div class="feedback-reply-reference"><i class="fas fa-reply"></i><span><small>En respuesta a {{ $message->padre->remitente?->name ?? 'Usuario' }}</small><em>{{ \Illuminate\Support\Str::limit(strip_tags($message->padre->contenido), 90) }}</em></span></div>
            @endif
            <div class="feedback-message-text">{!! \Illuminate\Support\Str::markdown($displayContent, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
            @if($message->imagenes->isNotEmpty())
                <div class="feedback-message-images {{ $message->imagenes->count() === 1 ? 'has-one' : '' }}">
                    @foreach($message->imagenes as $image)
                        <a href="{{ Storage::url($image->ruta_archivo) }}" target="_blank" rel="noopener noreferrer" title="Abrir {{ $image->nombre_original }}">
                            <img src="{{ Storage::url($image->ruta_archivo) }}" alt="{{ $image->nombre_original }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="feedback-message-actions">
                <button type="button" data-reply-message data-message-id="{{ $message->id }}" data-sender-name="{{ $message->remitente?->name }}" data-message-preview="{{ \Illuminate\Support\Str::limit($message->contenido, 100) }}" data-audience="{{ $message->audiencia }}" data-direct-target="{{ $directReplyTarget }}"><i class="fas fa-reply"></i>Responder</button>
                @if($isOwn)<button type="button" data-edit-message data-update-url="{{ route('campanias.mensajes.update', [$message->campania_id, $message]) }}"><i class="fas fa-pen"></i>Editar</button>@endif
                @if($isOwn || $canModerateMessages)<button type="button" data-delete-url="{{ route('campanias.mensajes.destroy', [$message->campania_id, $message]) }}" aria-label="Eliminar mensaje"><i class="fas fa-trash"></i></button>@endif
            </div>
        </div>
    </article>
@empty
    <div class="feedback-empty">
        <i class="far fa-comments"></i>
        <strong>No hay mensajes en esta vista</strong>
        <p>Cuando alguien escriba en este canal, el mensaje aparecerá aquí.</p>
    </div>
@endforelse
