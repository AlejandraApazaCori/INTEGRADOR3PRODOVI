<section class="discussion-card">
    <header class="discussion-header">
        <div>
            <span>Colaboración</span>
            <h2>Comentarios</h2>
            <p>Registra observaciones y decisiones del proceso de revisión.</p>
        </div>
        <strong>{{ $tarea->comentarios->count() }} {{ $tarea->comentarios->count() === 1 ? 'comentario' : 'comentarios' }}</strong>
    </header>

    <div class="discussion-body">
        <form action="{{ route('administrador.tareas.comentarios.store', $tarea->id) }}" method="POST" enctype="multipart/form-data" class="discussion-form">
            @csrf
            <label for="contenido">Nuevo comentario</label>
            <textarea name="contenido" id="contenido" rows="3" placeholder="Escribe una observación para el equipo..." required>{{ old('contenido') }}</textarea>
            <div class="discussion-form-footer">
                <label class="discussion-attachment" for="archivos">
                    <i class="fas fa-paperclip"></i>
                    <span>Adjuntar archivos</span>
                    <small>Máximo 10 MB por archivo</small>
                    <input type="file" name="archivos[]" id="archivos" multiple>
                </label>
                <button type="submit"><i class="fas fa-paper-plane"></i> Publicar comentario</button>
            </div>
            <div id="comment-files-preview" class="discussion-preview hidden" aria-live="polite"></div>
        </form>

        <div class="discussion-list">
            @forelse($tarea->comentarios->sortByDesc('created_at') as $comentario)
                <article class="discussion-item">
                    <header>
                        <span class="discussion-avatar">{{ strtoupper(mb_substr($comentario->user?->name ?? 'U', 0, 2)) }}</span>
                        <div>
                            <strong>{{ $comentario->user?->name ?? 'Usuario eliminado' }}</strong>
                            <time datetime="{{ $comentario->created_at->toIso8601String() }}">{{ $comentario->created_at->diffForHumans() }}</time>
                        </div>
                        @if(Auth::id() === $comentario->user_id || Auth::user()?->hasRole('admin'))
                            <form action="{{ route('administrador.tareas.comentarios.destroy', ['tarea' => $tarea->id, 'comentario' => $comentario->id]) }}" method="POST" onsubmit="return confirm('¿Eliminar este comentario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Eliminar comentario" title="Eliminar comentario"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </header>
                    <div class="discussion-message">{!! nl2br(e($comentario->contenido)) !!}</div>
                    @if($comentario->archivos->isNotEmpty())
                        <div class="discussion-files">
                            @foreach($comentario->archivos as $archivo)
                                <a href="{{ Storage::url($archivo->ruta_archivo) }}" download title="Descargar {{ $archivo->nombre_original }}">
                                    <i class="fas fa-paperclip"></i><span>{{ $archivo->nombre_original }}</span><i class="fas fa-download"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="discussion-empty">
                    <strong>No hay comentarios todavía</strong>
                    <span>Las observaciones del equipo aparecerán aquí.</span>
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
    .discussion-card{overflow:hidden;border:1px solid #e1e3de;border-radius:13px;background:#fff;box-shadow:0 6px 18px rgba(55,60,52,.05)}.discussion-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 20px 15px;border-bottom:1px solid #e8ebe5}.discussion-header>div>span{display:block;margin-bottom:3px;color:#117e8c;font-size:.56rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.discussion-header h2{margin:0;color:#302832;font-size:.98rem;font-weight:900}.discussion-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.discussion-header p{margin:6px 0 0;color:#7e867b;font-size:.59rem}.discussion-header>strong{padding:6px 9px;border-radius:999px;background:#edf7f8;color:#117e8c;font-size:.56rem;font-weight:900}
    .discussion-body{padding:18px 20px 20px}.discussion-form{padding:14px;border:1px solid #e1e5df;border-radius:10px;background:#fafbf9}.discussion-form>label{display:block;margin:0 0 7px;color:#4a5248;font-size:.61rem;font-weight:900}.discussion-form textarea{width:100%;min-height:90px;padding:11px 12px;border:1px solid #d7dcd4;border-radius:9px;background:#fff;color:#343a32;font-family:inherit;font-size:.65rem;line-height:1.5;outline:0;resize:vertical}.discussion-form textarea:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.discussion-form-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:10px}.discussion-attachment{position:relative;display:grid!important;grid-template-columns:auto auto;column-gap:6px;align-items:center;margin:0!important;color:#596257!important;cursor:pointer}.discussion-attachment>i{grid-row:1/3;color:#117e8c}.discussion-attachment span{font-size:.57rem;font-weight:900}.discussion-attachment small{color:#92998f;font-size:.5rem;font-weight:600}.discussion-attachment input{position:absolute;width:1px;height:1px;overflow:hidden;opacity:0}.discussion-form-footer>button{min-height:36px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border:0;border-radius:8px;background:#4f46e5;color:#fff;font-size:.59rem;font-weight:900;cursor:pointer}.discussion-preview{margin-top:9px;padding:8px 10px;border:1px solid #dce4d9;border-radius:8px;background:#fff;color:#737b71;font-size:.54rem}.discussion-preview span{display:block}.discussion-preview span+span{margin-top:3px}
    .discussion-list{margin-top:17px}.discussion-item{padding:15px 2px;border-top:1px solid #e8ebe5}.discussion-item>header{display:grid;grid-template-columns:36px minmax(0,1fr) auto;align-items:center;gap:9px}.discussion-avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:9px;background:#117e8c;color:#fff;font-size:.58rem;font-weight:900}.discussion-item header strong,.discussion-item header time{display:block}.discussion-item header strong{color:#343a32;font-size:.64rem;font-weight:900}.discussion-item header time{margin-top:2px;color:#92998f;font-size:.51rem}.discussion-item header form button{width:30px;height:30px;border:1px solid #e4e7e1;border-radius:7px;background:#fff;color:#a0a69d;cursor:pointer}.discussion-item header form button:hover{border-color:#f1c8c8;color:#b53c3c}.discussion-message{margin:10px 0 0 45px;color:#5f675d;font-size:.63rem;line-height:1.6}.discussion-files{display:flex;flex-wrap:wrap;gap:6px;margin:10px 0 0 45px}.discussion-files a{max-width:250px;display:flex;align-items:center;gap:6px;padding:6px 8px;border:1px solid #dfe3dc;border-radius:7px;background:#fafbf9;color:#596257;font-size:.53rem;font-weight:750;text-decoration:none}.discussion-files a span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.discussion-files a i:last-child{margin-left:auto;color:#4f46e5}.discussion-empty{padding:34px 20px 18px;text-align:center}.discussion-empty strong,.discussion-empty span{display:block}.discussion-empty strong{color:#4b5349;font-size:.68rem}.discussion-empty span{margin-top:5px;color:#92998f;font-size:.56rem}
    @media(max-width:640px){.discussion-header{align-items:flex-start;flex-direction:column}.discussion-body{padding:14px}.discussion-form-footer{align-items:stretch;flex-direction:column}.discussion-form-footer>button{width:100%}.discussion-message,.discussion-files{margin-left:0}.discussion-files a{max-width:100%;width:100%}}
</style>

<script>
    (() => {
        const input = document.getElementById('archivos');
        const preview = document.getElementById('comment-files-preview');
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            preview.replaceChildren();
            Array.from(input.files).forEach(file => {
                const line = document.createElement('span');
                line.textContent = `${file.name} · ${(file.size / 1048576).toFixed(2)} MB`;
                preview.appendChild(line);
            });
            preview.classList.toggle('hidden', input.files.length === 0);
        });
    })();
</script>
