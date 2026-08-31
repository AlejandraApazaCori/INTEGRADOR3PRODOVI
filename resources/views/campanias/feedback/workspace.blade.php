@php
    $feedbackClientMode = $feedbackClientMode ?? false;
    $currentUser = auth()->user();
    $directParticipants = $feedbackClientMode
        ? $feedbackParticipants->reject(fn ($participant) => (int) $participant->id === (int) $campania->usuario_cliente_id)
        : $feedbackParticipants->reject(fn ($participant) => (int) $participant->id === (int) $currentUser->id);
    $feedbackTasks = $feedbackClientMode
        ? $campania->tareas->where('visible_cliente', true)
        : $campania->tareas;
    $feedbackCustomContextsQuery = $campania->mensajeContextos()->orderBy('nombre');
    if ($feedbackClientMode) {
        $feedbackCustomContextsQuery->where(function ($query) use ($currentUser) {
            $query->where('creado_por_id', $currentUser->id)
                ->orWhereHas('mensajes', fn ($messages) => $messages->visiblePara($currentUser, true));
        });
    }
    $feedbackCustomContexts = $feedbackCustomContextsQuery->get();
@endphp

<section class="feedback-workspace {{ $feedbackClientMode ? 'is-client-feedback' : '' }}" data-feedback-workspace
    data-index-url="{{ route('campanias.mensajes.index', $campania) }}"
    data-store-url="{{ route('campanias.mensajes.store', $campania) }}"
    data-context-store-url="{{ route('campanias.mensajes.contextos.store', $campania) }}">
    <header class="feedback-header">
        <div>
            <span>Comunicación de campaña</span>
            <h2>Centro de mensajes</h2>
            <p>Conversaciones organizadas y vinculadas con el trabajo de la campaña.</p>
        </div>
        <div class="feedback-live"><span></span>Actualización automática</div>
    </header>

    <nav class="feedback-filters feedback-main-filters" aria-label="Filtros de mensajes">
        <button type="button" class="is-active" data-feedback-filter="todos"><i class="fas fa-inbox"></i>Todos <span data-count="todos">0</span></button>
        <button type="button" data-feedback-filter="mios"><i class="fas fa-at"></i>Para mí <span data-count="mios">0</span></button>
        <button type="button" data-feedback-filter="cliente"><i class="fas fa-people-arrows"></i>Cliente ↔ equipo <span data-count="cliente">0</span></button>
    </nav>

    <div class="feedback-conversation" data-feedback-conversation>
    <div class="feedback-layout">
        <div class="feedback-feed-card">
            <div class="feedback-context-browser" data-feedback-contexts>
                <div class="feedback-context-browser-heading"><div><small>Conversaciones</small><strong>Contextos con mensajes</strong></div><i class="fas fa-comments"></i></div>
                <div data-feedback-context-loading class="feedback-context-loading"><i class="fas fa-circle-notch fa-spin"></i>Cargando conversaciones…</div>
                <div data-feedback-context-list hidden></div>
            </div>
            <div data-feedback-message-panel hidden>
                <div class="feedback-message-panel-head">
                    <button type="button" data-feedback-context-back><i class="fas fa-arrow-left"></i>Conversaciones</button>
                    <strong data-feedback-context-title>Campaña general</strong>
                </div>
                <div class="feedback-loading" data-feedback-loading><i class="fas fa-circle-notch fa-spin"></i>Cargando mensajes…</div>
                <div class="feedback-message-list" data-feedback-list aria-live="polite"></div>
            </div>
        </div>

        <aside class="feedback-composer-card">
            <div class="feedback-composer-heading">
                <span><i class="fas fa-pen"></i></span>
                <div><small>Nuevo mensaje</small><strong>Escribir al equipo</strong></div>
            </div>
            <form data-feedback-form>
                <label class="feedback-field">
                    <span>Enviar a</span>
                    <div class="feedback-dropdown" data-feedback-dropdown>
                        <select name="audiencia" class="feedback-native-select" data-feedback-audience required tabindex="-1" aria-hidden="true">
                            @unless($feedbackClientMode)<option value="equipo">Equipo interno</option>@endunless
                            <option value="cliente_equipo" {{ $feedbackClientMode ? 'selected' : '' }}>Cliente y equipo</option>
                            <option value="directo">Una persona</option>
                        </select>
                        <button type="button" class="feedback-dropdown-trigger" data-feedback-dropdown-trigger aria-haspopup="listbox" aria-expanded="false"><span data-feedback-dropdown-label></span><i class="fas fa-chevron-down"></i></button>
                        <div class="feedback-dropdown-menu" data-feedback-dropdown-menu role="listbox" hidden>
                            @unless($feedbackClientMode)<button type="button" data-value="equipo" role="option"><i class="fas fa-users"></i><span>Equipo interno<small>Solo personal de la campaña</small></span><i class="fas fa-check"></i></button>@endunless
                            <button type="button" data-value="cliente_equipo" role="option"><i class="fas fa-people-arrows"></i><span>Cliente y equipo<small>Canal compartido con el cliente</small></span><i class="fas fa-check"></i></button>
                            <button type="button" data-value="directo" role="option"><i class="fas fa-user"></i><span>Una persona<small>Conversación privada</small></span><i class="fas fa-check"></i></button>
                        </div>
                    </div>
                </label>
                <label class="feedback-field" data-feedback-recipient-wrapper hidden>
                    <span>Destinatario</span>
                    <div class="feedback-dropdown" data-feedback-dropdown>
                        <select name="destinatario_id" class="feedback-native-select" data-feedback-recipient tabindex="-1" aria-hidden="true">
                            <option value="">Selecciona una persona</option>
                            @foreach($directParticipants as $participant)
                                <option value="{{ $participant->id }}">{{ $participant->name }} — {{ $participant->roles->pluck('nombre_rol')->first() ?? 'Participante' }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="feedback-dropdown-trigger" data-feedback-dropdown-trigger aria-haspopup="listbox" aria-expanded="false"><span data-feedback-dropdown-label></span><i class="fas fa-chevron-down"></i></button>
                        <div class="feedback-dropdown-menu" data-feedback-dropdown-menu role="listbox" hidden>
                            <button type="button" data-value="" role="option"><i class="fas fa-user-plus"></i><span>Selecciona una persona<small>Destinatario del mensaje</small></span><i class="fas fa-check"></i></button>
                            @foreach($directParticipants as $participant)
                                <button type="button" data-value="{{ $participant->id }}" role="option"><span class="feedback-option-avatar">{{ strtoupper(mb_substr($participant->name, 0, 2)) }}</span><span>{{ $participant->name }}<small>{{ $participant->roles->pluck('nombre_rol')->first() ?? 'Participante' }}</small></span><i class="fas fa-check"></i></button>
                            @endforeach
                        </div>
                    </div>
                </label>
                <label class="feedback-field">
                    <span>Contexto seleccionado</span>
                    <div class="feedback-dropdown" data-feedback-dropdown>
                        <select class="feedback-native-select" data-feedback-new-context tabindex="-1" aria-hidden="true">
                            <option value="general" selected>Campaña general</option>
                            <option value="otro">Otro</option>
                            @foreach($feedbackCustomContexts as $customContext)<option value="custom:{{ $customContext->id }}">{{ $customContext->nombre }}</option>@endforeach
                            @foreach($feedbackTasks as $task)<option value="{{ $task->id }}">{{ $task->titulo }}</option>@endforeach
                        </select>
                        <button type="button" class="feedback-dropdown-trigger" data-feedback-dropdown-trigger aria-haspopup="listbox" aria-expanded="false"><i class="fas fa-folder-open"></i><span data-feedback-dropdown-label>Campaña general</span><i class="fas fa-chevron-down"></i></button>
                        <div class="feedback-dropdown-menu" data-feedback-dropdown-menu role="listbox" hidden>
                            <button type="button" data-value="otro" role="option"><i class="fas fa-plus"></i><span>Otro<small>Crear un contexto personalizado</small></span><i class="fas fa-check"></i></button>
                            <button type="button" data-value="general" data-context-option-title="Campaña general" role="option"><i class="fas fa-bullhorn"></i><span>Campaña general<small>Coordinación de toda la campaña</small></span><i class="fas fa-check"></i></button>
                            @foreach($feedbackCustomContexts as $customContext)
                                <button type="button" data-value="custom:{{ $customContext->id }}" data-context-option-title="{{ $customContext->nombre }}" role="option"><i class="fas fa-comments"></i><span>{{ $customContext->nombre }}<small>Contexto personalizado</small></span><i class="fas fa-check"></i></button>
                            @endforeach
                            @foreach($feedbackTasks as $task)
                                <button type="button" data-value="{{ $task->id }}" data-context-option-title="{{ $task->titulo }}" role="option"><i class="fas fa-list-check"></i><span>{{ $task->titulo }}<small>Conversación de la tarea</small></span><i class="fas fa-check"></i></button>
                            @endforeach
                        </div>
                    </div>
                    <strong data-feedback-composer-context hidden>Campaña general</strong>
                </label>
                <div class="feedback-compose-mode" data-feedback-compose-mode hidden>
                    <div><i class="fas fa-reply"></i><span><small data-feedback-mode-label>Respondiendo a</small><strong data-feedback-mode-person></strong><em data-feedback-mode-preview></em></span></div>
                    <button type="button" data-feedback-mode-cancel aria-label="Cancelar"><i class="fas fa-xmark"></i></button>
                </div>
                <input type="hidden" name="tarea_id" data-feedback-task-id>
                <input type="hidden" name="contexto_id" data-feedback-custom-context-id>
                <input type="hidden" name="mensaje_padre_id" data-feedback-reply-id>
                <label>
                    <span>Mensaje</span>
                    <div class="feedback-editor">
                        <div class="feedback-editor-toolbar" aria-label="Formato del mensaje">
                            <button type="button" data-feedback-bold title="Negrita"><i class="fas fa-bold"></i><span>Negrita</span></button>
                            <button type="button" data-feedback-link title="Agregar enlace"><i class="fas fa-link"></i><span>Enlace</span></button>
                            <button type="button" data-feedback-image title="Agregar o pegar imágenes"><i class="fas fa-image"></i><span>Imagen</span></button>
                        </div>
                        <div class="feedback-editor-input" contenteditable="true" role="textbox" aria-multiline="true" data-feedback-editor-text data-placeholder="Escribe un mensaje. También puedes pegar imágenes directamente aquí…"></div>
                        <textarea name="contenido" data-feedback-editor-value hidden></textarea>
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-feedback-image-input hidden>
                        <div class="feedback-editor-hint"><i class="fas fa-paste"></i>Pega imágenes o enlaces con Ctrl + V</div>
                    </div>
                </label>
                <div class="feedback-form-error" data-feedback-error hidden></div>
                <button type="submit" data-feedback-submit><i class="fas fa-paper-plane"></i>Enviar mensaje</button>
            </form>
            <div class="feedback-privacy-note"><i class="fas fa-shield-halved"></i><span>Los mensajes directos solo son visibles para el remitente y el destinatario.</span></div>
        </aside>
    </div>
    </div>

    <div class="feedback-context-modal" data-feedback-context-modal hidden role="dialog" aria-modal="true" aria-labelledby="feedback-context-modal-title">
        <button type="button" class="feedback-delete-backdrop" data-feedback-context-modal-cancel aria-label="Cerrar"></button>
        <section class="feedback-context-dialog">
            <span class="feedback-context-modal-icon"><i class="fas fa-comments"></i></span>
            <small>Nuevo contexto</small>
            <h3 id="feedback-context-modal-title">Crear otra conversación</h3>
            <p>Escribe un nombre breve que permita identificar el tema de los mensajes.</p>
            <label><span>Nombre del contexto</span><input type="text" maxlength="100" data-feedback-context-name placeholder="Ej.: Revisión de lanzamiento"></label>
            <div class="feedback-context-modal-error" data-feedback-context-modal-error hidden></div>
            <footer><button type="button" data-feedback-context-modal-cancel>Cancelar</button><button type="button" data-feedback-context-modal-confirm><i class="fas fa-plus"></i>Crear contexto</button></footer>
        </section>
    </div>

    <div class="feedback-delete-modal" data-feedback-delete-modal hidden role="alertdialog" aria-modal="true" aria-labelledby="feedback-delete-title" aria-describedby="feedback-delete-description">
        <button type="button" class="feedback-delete-backdrop" data-feedback-delete-cancel aria-label="Cerrar confirmación"></button>
        <section class="feedback-delete-dialog">
            <span class="feedback-delete-icon"><i class="fas fa-trash-can"></i></span>
            <div>
                <small>Confirmar eliminación</small>
                <h3 id="feedback-delete-title">¿Eliminar este mensaje?</h3>
                <p id="feedback-delete-description">Esta acción quitará el mensaje de la conversación y no se puede deshacer.</p>
            </div>
            <footer>
                <button type="button" data-feedback-delete-cancel>Cancelar</button>
                <button type="button" data-feedback-delete-confirm><i class="fas fa-trash"></i>Eliminar mensaje</button>
            </footer>
        </section>
    </div>
</section>

<style>
    .feedback-workspace{width:min(1280px,calc(100% - 48px));margin:16px auto 0;color:#302834}
    .feedback-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:22px 24px;border-radius:14px 14px 0 0;background:linear-gradient(135deg,#0f6f79,#117e8c);color:#fff}.feedback-header>div:first-child>span{display:block;margin-bottom:4px;color:#bdebf0;font-size:.6rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.feedback-header h2{margin:0;font-size:1.25rem;font-weight:900}.feedback-header p{margin:4px 0 0;color:#d7f3f5;font-size:.68rem}.feedback-live{display:flex;align-items:center;gap:8px;padding:8px 11px;border:1px solid rgba(255,255,255,.25);border-radius:999px;background:rgba(255,255,255,.1);font-size:.58rem;font-weight:850}.feedback-live>span{width:7px;height:7px;border-radius:50%;background:#86efac;box-shadow:0 0 0 4px rgba(134,239,172,.16)}
    .feedback-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr);gap:16px;padding:16px;border:1px solid #dce5e6;border-top:0;border-radius:0 0 14px 14px;background:#f6f9f9}.feedback-feed-card,.feedback-composer-card{overflow:hidden;border:1px solid #dde4e5;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(33,62,66,.05)}
    .feedback-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border-bottom:1px solid #e5eaeb}.feedback-filters button{min-height:52px;display:flex;align-items:center;justify-content:center;gap:7px;padding:8px;border:0;border-right:1px solid #edf0f1;background:#fff;color:#747c80;font-size:.64rem;font-weight:900;cursor:pointer}.feedback-filters button:last-child{border-right:0}.feedback-filters button.is-active{background:#eff9fa;color:#0f6f79;box-shadow:inset 0 -3px 0 #117e8c}.feedback-filters button>span{min-width:20px;padding:2px 5px;border-radius:999px;background:#edf1f2;color:#667176;font-size:.52rem}.feedback-filters button.is-active>span{background:#117e8c;color:#fff}
    .feedback-loading{height:360px;display:flex;align-items:center;justify-content:center;gap:8px;color:#6c787d;font-size:.68rem;font-weight:800}.feedback-loading[hidden],.feedback-message-list[hidden]{display:none!important}.feedback-message-list{height:520px;overflow:auto;padding:18px}.feedback-message{display:grid;grid-template-columns:38px minmax(0,1fr);align-items:start;gap:10px;margin-bottom:15px}.feedback-message.is-own{grid-template-columns:minmax(0,1fr) 38px}.feedback-message.is-own .feedback-message-avatar{grid-column:2}.feedback-message.is-own .feedback-message-content{grid-column:1;grid-row:1;background:#eff9fa;border-color:#cfe7e9}.feedback-message-avatar{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:#5b2b76;color:#fff;font-size:.62rem;font-weight:900}.feedback-message-content{position:relative;padding:12px 40px 12px 14px;border:1px solid #e1e6e7;border-radius:5px 13px 13px 13px;background:#fff}.feedback-message-content header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.feedback-message-content header strong,.feedback-message-content header span{display:block}.feedback-message-content header strong{color:#2f3739;font-size:.69rem}.feedback-message-content header span{margin-top:2px;color:#8a9498;font-size:.54rem}.feedback-message-content time{color:#9aa2a5;font-size:.52rem;white-space:nowrap}.feedback-message-content>p{margin:9px 0 0;color:#4b5559;font-size:.68rem;line-height:1.6;white-space:pre-wrap}.feedback-message-meta{display:flex;align-items:center;flex-wrap:wrap;gap:5px;margin-top:8px}.feedback-message-meta span,.feedback-message-meta a{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border-radius:999px;background:#f1f3f4;color:#657075;font-size:.5rem;font-weight:850;text-decoration:none}.feedback-message-meta span.is-cliente_equipo{background:#fff2e9;color:#b14b0f}.feedback-message-meta span.is-directo{background:#f3edf6;color:#5b2b76}.feedback-message-meta small{color:#7a8589;font-size:.51rem}.feedback-message-meta a{background:#eef2ff;color:#4338ca}.feedback-delete-message{position:absolute;right:11px;bottom:10px;border:0;background:transparent;color:#a3aaad;font-size:.58rem;cursor:pointer}.feedback-delete-message:hover{color:#dc2626}.feedback-empty{height:410px;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;color:#899398}.feedback-empty>i{margin-bottom:12px;color:#b8cfd1;font-size:2rem}.feedback-empty strong{color:#586367;font-size:.78rem}.feedback-empty p{max-width:300px;margin:5px 0 0;font-size:.62rem}
    .feedback-composer-card{padding:18px}.feedback-composer-heading{display:flex;align-items:center;gap:10px;margin-bottom:17px;padding-bottom:14px;border-bottom:1px solid #e8ecec}.feedback-composer-heading>span{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:#117e8c;color:#fff}.feedback-composer-heading small,.feedback-composer-heading strong{display:block}.feedback-composer-heading small{color:#8c9699;font-size:.53rem;font-weight:900;text-transform:uppercase}.feedback-composer-heading strong{margin-top:2px;color:#30383a;font-size:.78rem}.feedback-composer-card form{display:grid;gap:12px}.feedback-composer-card label>span{display:block;margin-bottom:6px;color:#596367;font-size:.56rem;font-weight:900;text-transform:uppercase}.feedback-composer-card select,.feedback-composer-card textarea{width:100%;padding:10px 11px;border:1px solid #d8dfe0;border-radius:9px;background:#fff;color:#384246;font-size:.66rem;outline:none}.feedback-composer-card textarea{resize:vertical;line-height:1.5}.feedback-composer-card select:focus,.feedback-composer-card textarea:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.feedback-composer-card form>button{min-height:40px;display:flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:9px;background:#117e8c;color:#fff;font-size:.65rem;font-weight:900;cursor:pointer}.feedback-composer-card form>button:disabled{opacity:.65;cursor:wait}.feedback-form-error{padding:8px 10px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;color:#b91c1c;font-size:.58rem;font-weight:700}.feedback-privacy-note{display:flex;align-items:flex-start;gap:8px;margin-top:15px;padding:10px;border-radius:9px;background:#f5f7f7;color:#707a7e;font-size:.54rem;line-height:1.5}.feedback-privacy-note i{margin-top:2px;color:#117e8c}
    .feedback-composer-card{overflow:visible}.feedback-field[hidden]{display:none!important}.feedback-dropdown{position:relative}.feedback-native-select{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}.feedback-dropdown-trigger{width:100%;min-height:40px;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 11px;border:1px solid #d8dfe0;border-radius:9px;background:#fff;color:#384246;font-size:.66rem;font-weight:750;text-align:left;cursor:pointer;transition:.16s}.feedback-dropdown-trigger:hover{border-color:#aabfc1}.feedback-dropdown.is-open .feedback-dropdown-trigger{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.feedback-dropdown-trigger>i{color:#7c898d;font-size:.55rem;transition:.16s}.feedback-dropdown.is-open .feedback-dropdown-trigger>i{transform:rotate(180deg)}.feedback-dropdown-menu{position:absolute;z-index:40;top:calc(100% + 6px);right:0;left:0;max-height:235px;overflow:auto;padding:6px;border:1px solid #d9e1e2;border-radius:10px;background:#fff;box-shadow:0 14px 34px rgba(31,52,56,.16)}.feedback-dropdown-menu[hidden]{display:none!important}.feedback-dropdown-menu>button{width:100%;display:grid;grid-template-columns:26px minmax(0,1fr) 16px;align-items:center;gap:8px;padding:9px;border:0;border-radius:7px;background:#fff;color:#536065;text-align:left;cursor:pointer}.feedback-dropdown-menu>button:hover,.feedback-dropdown-menu>button.is-selected{background:#eff9fa;color:#0f6f79}.feedback-dropdown-menu>button>i:first-child{width:26px;text-align:center;color:#117e8c}.feedback-dropdown-menu>button>i:last-child{visibility:hidden;color:#117e8c;font-size:.58rem}.feedback-dropdown-menu>button.is-selected>i:last-child{visibility:visible}.feedback-dropdown-menu>button>span:nth-child(2){min-width:0;display:block;overflow:hidden;font-size:.62rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap}.feedback-dropdown-menu small{display:block;margin-top:2px;color:#8b9599;font-size:.5rem;font-weight:600}.feedback-option-avatar{width:26px;height:26px!important;display:grid!important;place-items:center;border-radius:7px;background:#5b2b76;color:#fff!important;font-size:.5rem!important;font-weight:900!important}
    .feedback-editor{overflow:hidden;border:1px solid #d8dfe0;border-radius:9px;background:#fff;transition:.16s}.feedback-editor:focus-within{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.feedback-editor-toolbar{display:flex;align-items:center;gap:3px;padding:6px;border-bottom:1px solid #e7ebec;background:#f7f9f9}.feedback-editor-toolbar button{min-height:30px;display:inline-flex;align-items:center;gap:6px;padding:6px 8px;border:0;border-radius:6px;background:transparent;color:#5f6a6e;font-size:.56rem;font-weight:850;cursor:pointer}.feedback-editor-toolbar button:hover{background:#e5f3f4;color:#0f6f79}.feedback-editor-toolbar button i{font-size:.62rem}.feedback-editor textarea{width:100%!important;min-height:118px;padding:11px!important;border:0!important;border-radius:0!important;box-shadow:none!important}.feedback-image-previews{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;padding:8px;border-top:1px solid #e7ebec;background:#fafcfc}.feedback-image-previews[hidden]{display:none!important}.feedback-image-preview{position:relative;aspect-ratio:1;overflow:hidden;border:1px solid #dbe2e3;border-radius:8px;background:#eef2f2}.feedback-image-preview img{width:100%;height:100%;display:block;object-fit:cover}.feedback-image-preview button{position:absolute;top:5px;right:5px;width:23px;height:23px;display:grid;place-items:center;border:0;border-radius:50%;background:rgba(25,31,33,.78);color:#fff;font-size:.55rem;cursor:pointer}.feedback-image-hint{grid-column:1/-1;margin:0;color:#748085;font-size:.5rem;line-height:1.4}
    .feedback-editor-input{min-height:132px;max-height:310px;overflow-y:auto;padding:11px;color:#384246;font-size:.68rem;line-height:1.55;outline:none;white-space:pre-wrap;overflow-wrap:anywhere}.feedback-editor-input:empty:before{content:attr(data-placeholder);color:#9aa3a6;pointer-events:none}.feedback-editor-input strong,.feedback-editor-input b{color:#20292c;font-weight:900}.feedback-editor-input a{padding:1px 3px;border-radius:4px;background:#e4f4f5;color:#0a6b75;font-weight:850;text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:2px}.feedback-editor-hint{display:flex;align-items:center;gap:6px;padding:7px 10px;border-top:1px solid #edf0f1;background:#fafcfc;color:#899397;font-size:.49rem;font-weight:700}.feedback-editor-hint i{color:#117e8c}.feedback-inline-image{position:relative;width:min(210px,100%);display:inline-block;margin:7px 7px 3px 0;padding:0;vertical-align:top;border:1px solid #d6dfe0;border-radius:9px;background:#eef2f2}.feedback-inline-image img{width:100%;max-height:170px;display:block;border-radius:8px;object-fit:cover}.feedback-inline-image button{position:absolute;top:6px;right:6px;width:25px;height:25px;display:grid;place-items:center;border:0;border-radius:50%;background:rgba(27,34,36,.82);color:#fff;font-size:.58rem;cursor:pointer}.feedback-inline-image figcaption{padding:5px 7px;overflow:hidden;color:#697579;font-size:.48rem;font-weight:750;text-overflow:ellipsis;white-space:nowrap}.feedback-editor-toolbar button.is-active{background:#dceff1;color:#0f6f79}
    .feedback-message-text{margin-top:9px;color:#4b5559;font-size:.68rem;line-height:1.6;overflow-wrap:anywhere}.feedback-message-text p{margin:0 0 7px}.feedback-message-text p:last-child{margin-bottom:0}.feedback-message-text strong{color:#263033;font-weight:900}.feedback-message-text a{color:#0f6f79;font-weight:800;text-decoration:underline;text-underline-offset:2px}.feedback-message-images{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;margin-top:10px}.feedback-message-images.has-one{grid-template-columns:minmax(0,320px)}.feedback-message-images a{display:block;overflow:hidden;border:1px solid #d8e0e1;border-radius:9px;background:#eef2f2}.feedback-message-images img{width:100%;height:150px;display:block;object-fit:cover;transition:.18s}.feedback-message-images a:hover img{transform:scale(1.025)}.feedback-message-images.has-one img{height:auto;max-height:280px;object-fit:contain}
    .feedback-main-filters{border:1px solid #dce5e6;border-top:0;background:#fff}.feedback-contexts{padding:18px;border:1px solid #dce5e6;border-top:0;border-radius:0 0 14px 14px;background:#f6f9f9}.feedback-contexts[hidden],.feedback-conversation[hidden]{display:none!important}.feedback-contexts-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:14px}.feedback-contexts-heading small{color:#117e8c;font-size:.55rem;font-weight:900;text-transform:uppercase}.feedback-contexts-heading h3{margin:3px 0;color:#30383a;font-size:1rem;font-weight:900}.feedback-contexts-heading p{margin:0;color:#798386;font-size:.62rem}.feedback-new-context{width:min(260px,100%);flex:0 0 auto}.feedback-new-context .feedback-dropdown-trigger{background:#117e8c;color:#fff;border-color:#117e8c}.feedback-new-context .feedback-dropdown-trigger>i{color:#fff}.feedback-context-loading,.feedback-context-empty{min-height:180px;display:flex;align-items:center;justify-content:center;gap:8px;color:#718084;font-size:.64rem;font-weight:800}.feedback-context-loading[hidden],.feedback-context-list[hidden]{display:none!important}.feedback-context-empty{flex-direction:column;text-align:center}.feedback-context-empty>i{color:#b7cbce;font-size:1.7rem}.feedback-context-empty strong{color:#59666a;font-size:.72rem}.feedback-context-empty p{margin:0;color:#879195;font-size:.57rem;font-weight:600}.feedback-context-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px}.feedback-context-card{min-width:0;display:grid;grid-template-columns:38px minmax(0,1fr) 22px;align-items:center;gap:11px;padding:14px;border:1px solid #dfe6e7;border-radius:12px;background:#fff;color:#3f494d;text-align:left;cursor:pointer;transition:.18s}.feedback-context-card:hover{transform:translateY(-2px);border-color:#9fc5c9;box-shadow:0 8px 20px rgba(35,75,80,.09)}.feedback-context-card.is-general{border-color:#c7dba7;background:#fbfdf8}.feedback-context-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:#117e8c;color:#fff}.feedback-context-card.is-general .feedback-context-icon{background:#7da533}.feedback-context-card>span:nth-child(2){min-width:0}.feedback-context-card small,.feedback-context-card strong,.feedback-context-card em{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.feedback-context-card small{color:#879195;font-size:.49rem;font-weight:900;text-transform:uppercase}.feedback-context-card strong{margin-top:3px;color:#30383a;font-size:.69rem;font-weight:900}.feedback-context-card em{margin-top:4px;color:#7c878a;font-size:.52rem;font-style:normal}.feedback-context-card>i{color:#9aa8ab;font-size:.6rem}.feedback-conversation-head{display:flex;align-items:center;gap:13px;padding:12px max(18px,calc((100% - 1280px)/2));border:1px solid #dce5e6;border-top:0;background:#f6f9f9}.feedback-conversation-head>button{display:inline-flex;align-items:center;gap:7px;padding:7px 9px;border:1px solid #cedadc;border-radius:8px;background:#fff;color:#117e8c;font-size:.56rem;font-weight:900;cursor:pointer}.feedback-conversation-head small,.feedback-conversation-head strong{display:block}.feedback-conversation-head small{color:#8a9497;font-size:.48rem;font-weight:900;text-transform:uppercase}.feedback-conversation-head strong{margin-top:2px;color:#354044;font-size:.68rem}.feedback-conversation .feedback-layout{border-top:0;border-radius:0 0 14px 14px}
    .feedback-selected-context{display:flex;align-items:center;gap:9px;padding:9px 10px;border:1px solid #d9e5e6;border-radius:9px;background:#f4fafa}.feedback-selected-context>i{color:#117e8c}.feedback-selected-context small,.feedback-selected-context strong{display:block}.feedback-selected-context small{color:#869195;font-size:.47rem;font-weight:900;text-transform:uppercase}.feedback-selected-context strong{margin-top:2px;color:#3b474a;font-size:.61rem}.feedback-compose-mode{margin-top:12px;padding:9px;border:1px solid #d8cde0;border-radius:9px;background:#faf7fb}.feedback-compose-mode[hidden]{display:none!important}.feedback-compose-mode,.feedback-compose-mode>div{display:flex;align-items:center;justify-content:space-between;gap:8px}.feedback-compose-mode>div>i{color:#5b2b76}.feedback-compose-mode span{min-width:0}.feedback-compose-mode small,.feedback-compose-mode strong,.feedback-compose-mode em{display:block}.feedback-compose-mode small{color:#937aa0;font-size:.46rem;font-weight:900;text-transform:uppercase}.feedback-compose-mode strong{margin-top:1px;color:#493351;font-size:.57rem}.feedback-compose-mode em{max-width:240px;margin-top:2px;overflow:hidden;color:#817386;font-size:.49rem;font-style:normal;text-overflow:ellipsis;white-space:nowrap}.feedback-compose-mode>button{border:0;background:transparent;color:#8f7b97;cursor:pointer}
    .feedback-message{width:fit-content;max-width:88%;display:flex!important;grid-template-columns:none!important;align-items:flex-start;gap:9px}.feedback-message.is-own{margin-left:auto;flex-direction:row-reverse}.feedback-message.is-reply:not(.is-own){margin-left:45px;max-width:82%}.feedback-message.is-reply.is-own{margin-right:45px;max-width:82%}.feedback-message.is-own .feedback-message-avatar,.feedback-message.is-own .feedback-message-content{grid-column:auto;grid-row:auto}.feedback-message-content{min-width:170px;max-width:680px;padding:11px 13px;border-radius:5px 17px 17px 17px;box-shadow:0 3px 9px rgba(42,57,60,.05)}.feedback-message.is-own .feedback-message-content{border-radius:17px 5px 17px 17px}.feedback-reply-reference{display:flex;align-items:flex-start;gap:7px;margin-top:8px;padding:7px 8px;border-left:3px solid #117e8c;border-radius:5px;background:rgba(17,126,140,.07)}.feedback-reply-reference>i{margin-top:2px;color:#117e8c;font-size:.52rem}.feedback-reply-reference small,.feedback-reply-reference em{display:block}.feedback-reply-reference small{color:#48666a;font-size:.48rem;font-weight:900}.feedback-reply-reference em{max-width:420px;margin-top:2px;overflow:hidden;color:#718084;font-size:.49rem;font-style:normal;text-overflow:ellipsis;white-space:nowrap}.feedback-message-actions{display:flex;align-items:center;justify-content:flex-end;gap:3px;margin-top:8px;padding-top:6px;border-top:1px solid rgba(126,143,147,.14)}.feedback-message-actions button{display:inline-flex;align-items:center;gap:4px;padding:4px 6px;border:0;border-radius:5px;background:transparent;color:#7b878b;font-size:.48rem;font-weight:800;cursor:pointer}.feedback-message-actions button:hover{background:rgba(17,126,140,.09);color:#0f6f79}.feedback-message-images{grid-template-columns:repeat(auto-fill,minmax(86px,110px));gap:5px}.feedback-message-images.has-one{grid-template-columns:minmax(90px,190px)}.feedback-message-images img,.feedback-message-images.has-one img{width:100%;height:92px;max-height:120px;object-fit:cover}.feedback-message-images.has-one img{height:auto;max-height:150px}.feedback-message-text strong{font-weight:900!important}.feedback-message-text b{font-weight:900!important}
    .feedback-context-browser{height:520px;overflow:auto;padding:16px}.feedback-context-browser[hidden],[data-feedback-message-panel][hidden]{display:none!important}.feedback-context-browser-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:13px;padding-bottom:11px;border-bottom:1px solid #e7ebec}.feedback-context-browser-heading small,.feedback-context-browser-heading strong{display:block}.feedback-context-browser-heading small{color:#117e8c;font-size:.5rem;font-weight:900;text-transform:uppercase}.feedback-context-browser-heading strong{margin-top:2px;color:#354044;font-size:.72rem}.feedback-context-browser-heading>i{color:#9bbfc3}.feedback-context-browser .feedback-context-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.feedback-message-panel-head{height:52px;display:flex;align-items:center;gap:11px;padding:9px 13px;border-bottom:1px solid #e5eaeb;background:#f8fafa}.feedback-message-panel-head button{display:inline-flex;align-items:center;gap:6px;padding:6px 8px;border:1px solid #d4dfe0;border-radius:7px;background:#fff;color:#117e8c;font-size:.53rem;font-weight:900;cursor:pointer}.feedback-message-panel-head strong{min-width:0;overflow:hidden;color:#354044;font-size:.64rem;text-overflow:ellipsis;white-space:nowrap}.feedback-message-panel-head~.feedback-loading{height:468px}.feedback-message-panel-head~.feedback-message-list{height:468px}.feedback-dropdown-trigger:disabled,.feedback-editor-toolbar button:disabled{cursor:not-allowed;opacity:.55}.feedback-message-text{white-space:pre-wrap}
    .feedback-delete-modal{position:fixed;z-index:15000;inset:0;display:grid;place-items:center;padding:20px}.feedback-delete-modal[hidden]{display:none!important}.feedback-delete-backdrop{position:absolute;inset:0;border:0;background:rgba(20,28,31,.58);backdrop-filter:blur(4px);cursor:default}.feedback-delete-dialog{position:relative;width:min(420px,100%);padding:24px;border-radius:16px;background:#fff;box-shadow:0 24px 65px rgba(15,23,42,.3);animation:feedback-modal-in .18s ease-out}.feedback-delete-icon{width:46px;height:46px;display:grid;place-items:center;margin-bottom:14px;border-radius:13px;background:#fef2f2;color:#dc2626;font-size:1rem}.feedback-delete-dialog small{color:#b45353;font-size:.52rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.feedback-delete-dialog h3{margin:4px 0 0;color:#30383a;font-size:1.05rem;font-weight:900}.feedback-delete-dialog p{margin:7px 0 0;color:#747f83;font-size:.65rem;line-height:1.55}.feedback-delete-dialog footer{display:grid;grid-template-columns:1fr 1.15fr;gap:9px;margin-top:21px}.feedback-delete-dialog footer button{min-height:41px;border:1px solid #dce2e3;border-radius:9px;background:#fff;color:#667176;font-size:.63rem;font-weight:900;cursor:pointer}.feedback-delete-dialog footer button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;border-color:#dc2626;background:#dc2626;color:#fff}.feedback-delete-dialog footer button:last-child:hover{background:#b91c1c}.feedback-delete-dialog footer button:disabled{cursor:wait;opacity:.65}@keyframes feedback-modal-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:none}}
    .feedback-context-modal{position:fixed;z-index:15000;inset:0;display:grid;place-items:center;padding:20px}.feedback-context-modal[hidden]{display:none!important}.feedback-context-dialog{position:relative;width:min(440px,100%);padding:24px;border-radius:16px;background:#fff;box-shadow:0 24px 65px rgba(15,23,42,.3);animation:feedback-modal-in .18s ease-out}.feedback-context-modal-icon{width:46px;height:46px;display:grid;place-items:center;margin-bottom:14px;border-radius:13px;background:#eaf6f7;color:#117e8c;font-size:1rem}.feedback-context-dialog>small{color:#117e8c;font-size:.52rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.feedback-context-dialog h3{margin:4px 0 0;color:#30383a;font-size:1.05rem;font-weight:900}.feedback-context-dialog>p{margin:7px 0 16px;color:#747f83;font-size:.65rem;line-height:1.55}.feedback-context-dialog label>span{display:block;margin-bottom:6px;color:#596367;font-size:.55rem;font-weight:900;text-transform:uppercase}.feedback-context-dialog input{width:100%;padding:11px;border:1px solid #d8dfe0;border-radius:9px;color:#384246;font-size:.68rem;outline:none}.feedback-context-dialog input:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.feedback-context-modal-error{margin-top:9px;padding:8px;border-radius:7px;background:#fef2f2;color:#b91c1c;font-size:.56rem;font-weight:750}.feedback-context-modal-error[hidden]{display:none!important}.feedback-context-dialog footer{display:grid;grid-template-columns:1fr 1.15fr;gap:9px;margin-top:18px}.feedback-context-dialog footer button{min-height:41px;border:1px solid #dce2e3;border-radius:9px;background:#fff;color:#667176;font-size:.63rem;font-weight:900;cursor:pointer}.feedback-context-dialog footer button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;border-color:#117e8c;background:#117e8c;color:#fff}.feedback-context-dialog footer button:disabled{cursor:wait;opacity:.65}.feedback-new-message-label{min-width:0;display:block!important;flex:1;text-align:left}.feedback-new-message-label small,.feedback-new-message-label strong{display:block}.feedback-new-message-label small{color:#ccebee;font-size:.46rem;font-weight:800;text-transform:uppercase}.feedback-new-message-label strong{margin-top:1px;color:#fff;font-size:.62rem;font-weight:900}
    @media(max-width:900px){.feedback-workspace{width:calc(100% - 32px)}.feedback-layout{grid-template-columns:1fr}.feedback-message-list{height:440px}.feedback-context-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:640px){.feedback-workspace{width:calc(100% - 24px)}.feedback-header{align-items:flex-start;flex-direction:column}.feedback-live{align-self:flex-start}.feedback-layout{padding:10px}.feedback-filters button{flex-direction:column;gap:3px;font-size:.57rem}.feedback-message-list{height:390px;padding:12px}.feedback-context-grid{grid-template-columns:1fr}.feedback-contexts-heading{align-items:flex-start;flex-direction:column}.feedback-message{max-width:96%}.feedback-message.is-reply:not(.is-own){margin-left:20px}.feedback-message.is-reply.is-own{margin-right:20px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-feedback-workspace]').forEach(function (workspace) {
        const list = workspace.querySelector('[data-feedback-list]');
        const loading = workspace.querySelector('[data-feedback-loading]');
        const form = workspace.querySelector('[data-feedback-form]');
        const submit = workspace.querySelector('[data-feedback-submit]');
        const errorBox = workspace.querySelector('[data-feedback-error]');
        const audience = workspace.querySelector('[data-feedback-audience]');
        const recipientWrapper = workspace.querySelector('[data-feedback-recipient-wrapper]');
        const recipient = workspace.querySelector('[data-feedback-recipient]');
        const editor = workspace.querySelector('[data-feedback-editor-text]');
        const editorValue = workspace.querySelector('[data-feedback-editor-value]');
        const imageInput = workspace.querySelector('[data-feedback-image-input]');
        const contextsScreen = workspace.querySelector('[data-feedback-contexts]');
        const contextsList = workspace.querySelector('[data-feedback-context-list]');
        const contextsLoading = workspace.querySelector('[data-feedback-context-loading]');
        const messagePanel = workspace.querySelector('[data-feedback-message-panel]');
        const newContextSelect = workspace.querySelector('[data-feedback-new-context]');
        const conversation = workspace.querySelector('[data-feedback-conversation]');
        const contextTitle = workspace.querySelector('[data-feedback-context-title]');
        const composerContext = workspace.querySelector('[data-feedback-composer-context]');
        const taskIdInput = workspace.querySelector('[data-feedback-task-id]');
        const customContextIdInput = workspace.querySelector('[data-feedback-custom-context-id]');
        const replyIdInput = workspace.querySelector('[data-feedback-reply-id]');
        const composeMode = workspace.querySelector('[data-feedback-compose-mode]');
        const modeLabel = workspace.querySelector('[data-feedback-mode-label]');
        const modePerson = workspace.querySelector('[data-feedback-mode-person]');
        const modePreview = workspace.querySelector('[data-feedback-mode-preview]');
        const audienceTrigger = audience.closest('[data-feedback-dropdown]').querySelector('[data-feedback-dropdown-trigger]');
        const recipientTrigger = recipient.closest('[data-feedback-dropdown]').querySelector('[data-feedback-dropdown-trigger]');
        const deleteModal = workspace.querySelector('[data-feedback-delete-modal]');
        const deleteConfirm = workspace.querySelector('[data-feedback-delete-confirm]');
        const contextModal = workspace.querySelector('[data-feedback-context-modal]');
        const contextNameInput = workspace.querySelector('[data-feedback-context-name]');
        const contextModalError = workspace.querySelector('[data-feedback-context-modal-error]');
        const contextModalConfirm = workspace.querySelector('[data-feedback-context-modal-confirm]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        let activeFilter = 'todos';
        let activeContext = 'general';
        let editingUrl = null;
        let loadingMessages = false;
        let loadingContexts = false;
        let pendingDeleteUrl = null;
        let deleteTrigger = null;
        let messageImages = [];
        let lastEditorRange = null;

        function nodeToMarkdown(node) {
            if (node.nodeType === Node.TEXT_NODE) return node.nodeValue || '';
            if (node.nodeType !== Node.ELEMENT_NODE) return '';
            if (node.matches?.('[data-feedback-inline-image]')) return '';

            const content = Array.from(node.childNodes).map(nodeToMarkdown).join('');
            if (node.tagName === 'BR') return '\n';
            if (node.tagName === 'STRONG' || node.tagName === 'B') return boldToMarkdown(content);
            if (node.tagName === 'SPAN' && (node.style.fontWeight === 'bold' || Number.parseInt(node.style.fontWeight, 10) >= 600)) {
                return boldToMarkdown(content);
            }
            if (node.tagName === 'A') return content ? `[${content}](${node.getAttribute('href') || ''})` : '';
            if (node.tagName === 'DIV' || node.tagName === 'P') return content + '\n';
            return content;
        }

        function boldToMarkdown(content) {
            if (!content) return '';
            const leadingWhitespace = content.match(/^\s*/u)?.[0] || '';
            const trailingWhitespace = content.match(/\s*$/u)?.[0] || '';
            const core = content.slice(leadingWhitespace.length, content.length - trailingWhitespace.length);
            return core ? `${leadingWhitespace}**${core}**${trailingWhitespace}` : content;
        }

        function syncEditorValue() {
            editorValue.value = nodeToMarkdown(editor).replace(/\n{3,}/g, '\n\n').trim();
        }

        function saveEditorSelection() {
            const selection = window.getSelection();
            if (!selection?.rangeCount) return;
            const range = selection.getRangeAt(0);
            if (editor.contains(range.commonAncestorContainer)) lastEditorRange = range.cloneRange();
        }

        function restoreEditorSelection() {
            editor.focus();
            const selection = window.getSelection();
            selection.removeAllRanges();
            if (lastEditorRange && editor.contains(lastEditorRange.commonAncestorContainer)) {
                selection.addRange(lastEditorRange);
                return selection.getRangeAt(0);
            }
            const range = document.createRange();
            range.selectNodeContents(editor);
            range.collapse(false);
            selection.addRange(range);
            return range;
        }

        function placeCaretAfter(node) {
            const range = document.createRange();
            range.setStartAfter(node);
            range.collapse(true);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            lastEditorRange = range.cloneRange();
        }

        function insertText(text) {
            const range = restoreEditorSelection();
            range.deleteContents();
            const node = document.createTextNode(text);
            range.insertNode(node);
            placeCaretAfter(node);
            syncEditorValue();
        }

        function insertLink(text, url) {
            const range = restoreEditorSelection();
            range.deleteContents();
            const link = document.createElement('a');
            link.href = url;
            link.textContent = text || url;
            range.insertNode(link);
            placeCaretAfter(link);
            syncEditorValue();
        }

        function insertPastedText(text) {
            text.split(/(https?:\/\/[^\s]+)/gi).filter(part => part !== '').forEach(function (part) {
                if (/^https?:\/\//i.test(part)) insertLink(part, part);
                else insertText(part);
            });
        }

        function createInlineImage(item) {
            const figure = document.createElement('figure');
            figure.className = 'feedback-inline-image';
            figure.dataset.feedbackInlineImage = item.id;
            figure.contentEditable = 'false';
            const image = document.createElement('img');
            image.src = item.url;
            image.alt = item.file.name || 'Imagen pegada';
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.dataset.removeImageId = item.id;
            remove.setAttribute('aria-label', 'Quitar imagen');
            remove.innerHTML = '<i class="fas fa-xmark"></i>';
            const caption = document.createElement('figcaption');
            caption.textContent = item.file.name || 'Imagen pegada';
            figure.append(image, remove, caption);

            const range = restoreEditorSelection();
            range.deleteContents();
            range.insertNode(figure);
            placeCaretAfter(figure);
        }

        function addImages(files) {
            errorBox.hidden = true;
            Array.from(files).forEach(function (file) {
                if (messageImages.length >= 5) {
                    showError('Puedes enviar un máximo de 5 imágenes por mensaje.');
                    return;
                }
                if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(file.type)) {
                    showError('Solo se permiten imágenes JPG, PNG, WEBP o GIF.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showError('Cada imagen debe pesar como máximo 5 MB.');
                    return;
                }
                const item = { file, url: URL.createObjectURL(file), id: `${Date.now()}-${Math.random().toString(16).slice(2)}` };
                messageImages.push(item);
                createInlineImage(item);
            });
            syncEditorValue();
        }

        workspace.querySelectorAll('.feedback-editor-toolbar button').forEach(button => button.addEventListener('mousedown', event => event.preventDefault()));

        workspace.querySelector('[data-feedback-bold]').addEventListener('click', function () {
            restoreEditorSelection();
            document.execCommand('bold', false);
            saveEditorSelection();
            this.classList.toggle('is-active', document.queryCommandState('bold'));
            syncEditorValue();
        });

        workspace.querySelector('[data-feedback-link]').addEventListener('click', function () {
            const selected = window.getSelection()?.toString().trim() || '';
            let url = window.prompt('Pega la dirección del enlace:', selected.startsWith('http') ? selected : 'https://');
            if (!url) return;
            url = url.trim();
            if (!/^https?:\/\//i.test(url)) url = 'https://' + url;
            const text = selected && !selected.startsWith('http') ? selected : (window.prompt('Texto que se mostrará:', url) || url);
            insertLink(text, url);
        });

        workspace.querySelector('[data-feedback-image]').addEventListener('click', function () {
            imageInput.click();
        });

        imageInput.addEventListener('change', function () {
            addImages(imageInput.files);
            imageInput.value = '';
        });

        editor.addEventListener('click', function (event) {
            const button = event.target.closest('[data-remove-image-id]');
            if (!button) return;
            const index = messageImages.findIndex(item => item.id === button.dataset.removeImageId);
            if (index < 0) return;
            URL.revokeObjectURL(messageImages[index].url);
            messageImages.splice(index, 1);
            button.closest('[data-feedback-inline-image]')?.remove();
            syncEditorValue();
        });

        editor.addEventListener('paste', function (event) {
            const clipboardImages = Array.from(event.clipboardData?.items || [])
                .filter(item => item.kind === 'file' && item.type.startsWith('image/'))
                .map(item => item.getAsFile())
                .filter(Boolean);
            event.preventDefault();
            saveEditorSelection();
            if (clipboardImages.length) {
                addImages(clipboardImages);
                return;
            }

            const pastedText = event.clipboardData?.getData('text/plain') || '';
            const trimmedText = pastedText.trim();
            if (/^https?:\/\/\S+$/i.test(trimmedText)) insertLink(trimmedText, trimmedText);
            else insertPastedText(pastedText);
        });

        editor.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            restoreEditorSelection();
            document.execCommand('insertLineBreak', false);
            saveEditorSelection();
            syncEditorValue();
        });

        ['input', 'keyup', 'mouseup', 'focus'].forEach(eventName => editor.addEventListener(eventName, function () {
            saveEditorSelection();
            syncEditorValue();
        }));

        function closeDropdown(dropdown) {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-feedback-dropdown-trigger]').setAttribute('aria-expanded', 'false');
            dropdown.querySelector('[data-feedback-dropdown-menu]').hidden = true;
        }

        workspace.querySelectorAll('[data-feedback-dropdown]').forEach(function (dropdown) {
            const select = dropdown.querySelector('select');
            const trigger = dropdown.querySelector('[data-feedback-dropdown-trigger]');
            const label = dropdown.querySelector('[data-feedback-dropdown-label]');
            const menu = dropdown.querySelector('[data-feedback-dropdown-menu]');
            const options = Array.from(menu.querySelectorAll('[data-value]'));

            function syncDropdown() {
                const selectedOption = select.options[select.selectedIndex];
                if (label) label.textContent = selectedOption?.textContent || 'Selecciona una opción';
                options.forEach(function (option) {
                    const selected = String(option.dataset.value) === String(select.value);
                    option.classList.toggle('is-selected', selected);
                    option.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
            }

            trigger.addEventListener('click', function () {
                const willOpen = !dropdown.classList.contains('is-open');
                workspace.querySelectorAll('[data-feedback-dropdown].is-open').forEach(closeDropdown);
                if (willOpen) {
                    dropdown.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    menu.hidden = false;
                }
            });

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    select.value = option.dataset.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeDropdown(dropdown);
                    trigger.focus();
                });
            });

            select.addEventListener('change', syncDropdown);
            syncDropdown();
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-feedback-dropdown]')) {
                workspace.querySelectorAll('[data-feedback-dropdown].is-open').forEach(closeDropdown);
            }
        });

        workspace.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;
            workspace.querySelectorAll('[data-feedback-dropdown].is-open').forEach(closeDropdown);
            if (!deleteModal.hidden) closeDeleteModal();
            if (!contextModal.hidden) closeContextModal();
        });

        function openContextModal() {
            contextNameInput.value = '';
            contextModalError.hidden = true;
            contextModal.hidden = false;
            document.body.style.overflow = 'hidden';
            window.setTimeout(() => contextNameInput.focus(), 0);
        }

        function closeContextModal() {
            if (contextModalConfirm.disabled) return;
            contextModal.hidden = true;
            document.body.style.overflow = '';
            syncContextSelector(activeContext);
        }

        function syncContextSelector(value) {
            newContextSelect.value = value;
            const label = newContextSelect.closest('[data-feedback-dropdown]').querySelector('[data-feedback-dropdown-label]');
            const selectedOption = newContextSelect.options[newContextSelect.selectedIndex];
            if (label) label.textContent = selectedOption?.textContent || 'Campaña general';
        }

        workspace.querySelectorAll('[data-feedback-context-modal-cancel]').forEach(button => button.addEventListener('click', closeContextModal));

        async function createCustomContext() {
            const name = contextNameInput.value.trim();
            contextModalError.hidden = true;
            if (!name) {
                contextModalError.textContent = 'Escribe un nombre para el contexto.';
                contextModalError.hidden = false;
                contextNameInput.focus();
                return;
            }
            contextModalConfirm.disabled = true;
            contextModalConfirm.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>Creando…';
            try {
                const response = await fetch(workspace.dataset.contextStoreUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ nombre: name }),
                });
                const data = await response.json();
                if (!response.ok) {
                    const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validation || data.message || 'No fue posible crear el contexto.');
                }
                const value = `custom:${data.id}`;
                const option = document.createElement('option');
                option.value = value;
                option.textContent = data.nombre;
                newContextSelect.append(option);

                const menu = newContextSelect.closest('[data-feedback-dropdown]').querySelector('[data-feedback-dropdown-menu]');
                const menuOption = document.createElement('button');
                menuOption.type = 'button';
                menuOption.dataset.value = value;
                menuOption.dataset.contextOptionTitle = data.nombre;
                menuOption.setAttribute('role', 'option');
                menuOption.innerHTML = '<i class="fas fa-comments"></i><span></span><i class="fas fa-check"></i>';
                menuOption.querySelector('span').append(document.createTextNode(data.nombre));
                const detail = document.createElement('small');
                detail.textContent = 'Contexto personalizado';
                menuOption.querySelector('span').append(detail);
                menu.append(menuOption);
                menuOption.addEventListener('click', function () {
                    newContextSelect.value = value;
                    newContextSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    closeDropdown(newContextSelect.closest('[data-feedback-dropdown]'));
                });

                contextModalConfirm.disabled = false;
                contextModal.hidden = true;
                document.body.style.overflow = '';
                syncContextSelector(value);
                openContext(value, data.nombre);
            } catch (error) {
                contextModalError.textContent = error.message;
                contextModalError.hidden = false;
            } finally {
                contextModalConfirm.disabled = false;
                contextModalConfirm.innerHTML = '<i class="fas fa-plus"></i>Crear contexto';
            }
        }

        contextModalConfirm.addEventListener('click', createCustomContext);
        contextNameInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                createCustomContext();
            }
        });

        function openDeleteModal(button) {
            pendingDeleteUrl = button.dataset.deleteUrl;
            deleteTrigger = button;
            deleteModal.hidden = false;
            document.body.style.overflow = 'hidden';
            deleteConfirm.focus();
        }

        function closeDeleteModal() {
            if (deleteConfirm.disabled) return;
            deleteModal.hidden = true;
            document.body.style.overflow = '';
            pendingDeleteUrl = null;
            deleteTrigger?.focus();
            deleteTrigger = null;
        }

        workspace.querySelectorAll('[data-feedback-delete-cancel]').forEach(button => button.addEventListener('click', closeDeleteModal));

        deleteConfirm.addEventListener('click', async function () {
            if (!pendingDeleteUrl) return;
            deleteConfirm.disabled = true;
            deleteConfirm.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>Eliminando…';
            try {
                const response = await fetch(pendingDeleteUrl, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'No fue posible eliminar el mensaje.');
                deleteConfirm.disabled = false;
                closeDeleteModal();
                await loadMessages();
            } catch (error) {
                showError(error.message);
            } finally {
                deleteConfirm.disabled = false;
                deleteConfirm.innerHTML = '<i class="fas fa-trash"></i>Eliminar mensaje';
            }
        });

        function toggleRecipient() {
            const direct = audience.value === 'directo';
            recipientWrapper.hidden = !direct;
            recipient.required = direct;
            if (!direct && recipient.value !== '') {
                recipient.value = '';
                recipient.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function showError(message) {
            errorBox.textContent = message;
            errorBox.hidden = false;
        }

        function clearEditor() {
            editor.innerHTML = '';
            editorValue.value = '';
            lastEditorRange = null;
            messageImages.forEach(item => URL.revokeObjectURL(item.url));
            messageImages = [];
        }

        function resetComposeMode(clearContent = true) {
            editingUrl = null;
            replyIdInput.value = '';
            composeMode.hidden = true;
            submit.innerHTML = '<i class="fas fa-paper-plane"></i>Enviar mensaje';
            imageInput.disabled = false;
            workspace.querySelector('[data-feedback-image]').disabled = false;
            audienceTrigger.disabled = false;
            recipientTrigger.disabled = false;
            if (clearContent) clearEditor();
        }

        function decodeMessageContent(encoded) {
            try {
                const bytes = Uint8Array.from(atob(encoded || ''), character => character.charCodeAt(0));
                return new TextDecoder().decode(bytes);
            } catch (error) {
                return '';
            }
        }

        function markdownToEditor(markdown) {
            const container = document.createElement('div');
            const pattern = /(\*\*([^*]+)\*\*)|(\[([^\]]+)\]\((https?:\/\/[^\s)]+)\))/g;
            let cursor = 0;
            let match;
            while ((match = pattern.exec(markdown)) !== null) {
                container.append(document.createTextNode(markdown.slice(cursor, match.index)));
                if (match[1]) {
                    const strong = document.createElement('strong');
                    strong.textContent = match[2];
                    container.append(strong);
                } else {
                    const link = document.createElement('a');
                    link.href = match[5];
                    link.textContent = match[4];
                    container.append(link);
                }
                cursor = pattern.lastIndex;
            }
            container.append(document.createTextNode(markdown.slice(cursor)));
            const fragment = document.createDocumentFragment();
            Array.from(container.childNodes).forEach(function (node) {
                if (node.nodeType !== Node.TEXT_NODE) {
                    fragment.append(node);
                    return;
                }
                node.nodeValue.split('\n').forEach(function (line, index, lines) {
                    fragment.append(document.createTextNode(line));
                    if (index < lines.length - 1) fragment.append(document.createElement('br'));
                });
            });
            editor.replaceChildren(fragment);
            syncEditorValue();
        }

        function openContext(value, title) {
            activeContext = String(value);
            const isCustom = activeContext.startsWith('custom:');
            taskIdInput.value = activeContext === 'general' || isCustom ? '' : activeContext;
            customContextIdInput.value = isCustom ? activeContext.slice(7) : '';
            contextTitle.textContent = title;
            composerContext.textContent = title;
            contextsScreen.hidden = true;
            messagePanel.hidden = false;
            resetComposeMode();
            loadMessages();
        }

        contextsList.addEventListener('click', function (event) {
            const card = event.target.closest('[data-feedback-context]');
            if (card) openContext(card.dataset.feedbackContext, card.dataset.contextTitle);
        });

        newContextSelect.addEventListener('change', function () {
            if (!newContextSelect.value) return;
            if (newContextSelect.value === 'otro') {
                openContextModal();
                return;
            }
            const selectedButton = newContextSelect.closest('[data-feedback-dropdown]')
                .querySelector(`[data-value="${CSS.escape(newContextSelect.value)}"]`);
            const title = selectedButton?.dataset.contextOptionTitle
                || newContextSelect.options[newContextSelect.selectedIndex]?.textContent
                || 'Conversación';
            openContext(newContextSelect.value, title.trim());
        });

        workspace.querySelector('[data-feedback-context-back]').addEventListener('click', function () {
            messagePanel.hidden = true;
            contextsScreen.hidden = false;
            resetComposeMode();
            loadContexts();
        });

        workspace.querySelector('[data-feedback-mode-cancel]').addEventListener('click', function () {
            resetComposeMode();
            editor.focus();
        });

        async function loadContexts(silent = false) {
            if (loadingContexts) return;
            loadingContexts = true;
            if (!silent) {
                contextsLoading.hidden = false;
                contextsList.hidden = true;
            }
            try {
                const parameters = new URLSearchParams({ filtro: activeFilter, vista: 'contextos' });
                const response = await fetch(`${workspace.dataset.indexUrl}?${parameters}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('No fue posible cargar las conversaciones.');
                const data = await response.json();
                contextsList.innerHTML = data.html;
                Object.entries(data.counts || {}).forEach(([name, value]) => {
                    workspace.querySelectorAll(`[data-count="${name}"]`).forEach(node => node.textContent = value);
                });
            } catch (error) {
                if (!silent) showError(error.message);
            } finally {
                contextsLoading.hidden = true;
                contextsList.hidden = false;
                loadingContexts = false;
            }
        }

        async function loadMessages(silent = false) {
            if (loadingMessages || !activeContext) return;
            loadingMessages = true;
            if (!silent) {
                loading.hidden = false;
                list.hidden = true;
            }
            try {
                const parameters = new URLSearchParams({ filtro: activeFilter, contexto: activeContext });
                const response = await fetch(`${workspace.dataset.indexUrl}?${parameters}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('No fue posible cargar los mensajes.');
                const data = await response.json();
                const nearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 90;
                list.innerHTML = data.html;
                Object.entries(data.counts || {}).forEach(([name, value]) => {
                    workspace.querySelectorAll(`[data-count="${name}"]`).forEach(node => node.textContent = value);
                });
                if (!silent || nearBottom) list.scrollTop = list.scrollHeight;
            } catch (error) {
                if (!silent) showError(error.message);
            } finally {
                loading.hidden = true;
                list.hidden = false;
                loadingMessages = false;
            }
        }

        workspace.querySelectorAll('[data-feedback-filter]').forEach(button => button.addEventListener('click', function () {
            activeFilter = button.dataset.feedbackFilter;
            workspace.querySelectorAll('[data-feedback-filter]').forEach(item => item.classList.toggle('is-active', item === button));
            resetComposeMode();
            messagePanel.hidden = true;
            contextsScreen.hidden = false;
            loadContexts();
        }));

        audience.addEventListener('change', toggleRecipient);
        toggleRecipient();

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            errorBox.hidden = true;
            syncEditorValue();
            if (!editorValue.value.trim() && (editingUrl || messageImages.length === 0)) {
                showError('Escribe un mensaje o agrega al menos una imagen.');
                editor.focus();
                return;
            }
            if (editorValue.value.length > 5000) {
                showError('El mensaje no puede superar los 5000 caracteres.');
                editor.focus();
                return;
            }
            submit.disabled = true;
            const payload = editingUrl ? new FormData() : new FormData(form);
            if (editingUrl) {
                payload.append('_method', 'PATCH');
                payload.append('contenido', editorValue.value);
            } else {
                messageImages.forEach(item => payload.append('imagenes[]', item.file, item.file.name || 'imagen-pegada.png'));
            }
            try {
                const response = await fetch(editingUrl || workspace.dataset.storeUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: payload,
                });
                const data = await response.json();
                if (!response.ok) {
                    const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validation || data.message || 'No fue posible enviar el mensaje.');
                }
                resetComposeMode();
                contextsScreen.hidden = true;
                messagePanel.hidden = false;
                await loadMessages();
            } catch (error) {
                showError(error.message);
            } finally {
                submit.disabled = false;
            }
        });

        list.addEventListener('click', async function (event) {
            const replyButton = event.target.closest('[data-reply-message]');
            if (replyButton) {
                resetComposeMode();
                replyIdInput.value = replyButton.dataset.messageId;
                modeLabel.textContent = 'Respondiendo a';
                modePerson.textContent = replyButton.dataset.senderName || 'Usuario';
                modePreview.textContent = replyButton.dataset.messagePreview || '';
                composeMode.hidden = false;
                audience.value = replyButton.dataset.audience;
                audience.dispatchEvent(new Event('change', { bubbles: true }));
                if (audience.value === 'directo') {
                    recipient.value = replyButton.dataset.directTarget || '';
                    recipient.dispatchEvent(new Event('change', { bubbles: true }));
                }
                audienceTrigger.disabled = true;
                recipientTrigger.disabled = true;
                editor.focus();
                return;
            }

            const editButton = event.target.closest('[data-edit-message]');
            if (editButton) {
                resetComposeMode();
                const article = editButton.closest('[data-message-content]');
                editingUrl = editButton.dataset.updateUrl;
                modeLabel.textContent = 'Editando mensaje';
                modePerson.textContent = 'Solo puedes cambiar el texto';
                modePreview.textContent = '';
                composeMode.hidden = false;
                submit.innerHTML = '<i class="fas fa-check"></i>Guardar cambios';
                imageInput.disabled = true;
                workspace.querySelector('[data-feedback-image]').disabled = true;
                markdownToEditor(decodeMessageContent(article?.dataset.messageContent));
                editor.focus();
                return;
            }

            const button = event.target.closest('[data-delete-url]');
            if (button) openDeleteModal(button);
        });

        loadContexts();
        window.setInterval(function () {
            if (workspace.offsetParent === null || document.visibilityState !== 'visible') return;
            if (!messagePanel.hidden) loadMessages(true);
            else loadContexts(true);
        }, 5000);
    });
});
</script>
