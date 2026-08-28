@extends('layouts.app')

@section('title', 'Revisión de entregables')

@section('content')
@php
    $archivos = $tarea->archivos->sortByDesc('created_at');
    $totalArchivos = $archivos->count();
    $archivosPendientes = $archivos->where('estado', 'pendiente')->count();
    $archivosAprobados = $archivos->where('estado', 'aprobado')->count();
    $archivosRechazados = $archivos->where('estado', 'rechazado')->count();
    $estadoTareaClase = match($tarea->estado) {
        'completada' => 'is-complete',
        'en_progreso' => 'is-progress',
        'rechazada' => 'is-rejected',
        default => 'is-pending',
    };
    $prioridadClase = match($tarea->prioridad) {
        'urgente' => 'is-urgent',
        'alta' => 'is-high',
        'baja' => 'is-low',
        default => 'is-medium',
    };
    $reviewUploadErrors = $errors->getBag('reviewUpload');
    $abrirDrawer = request()->boolean('subir') || session('open_upload_drawer') || $reviewUploadErrors->any();
@endphp

<div class="review-page">
    <div class="review-shell">
        <nav class="review-top-actions" aria-label="Navegación de tarea">
            <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}"><i class="fas fa-arrow-left"></i> Campaña</a>
            <a href="{{ route('administrador.campañas.calendario', $tarea->campania_id) }}"><i class="fas fa-calendar-days"></i> Planificación</a>
            <a href="{{ route('administrador.tareas.edit', $tarea->id) }}"><i class="fas fa-pen"></i> Editar tarea</a>
            <button type="button" class="is-primary" data-open-review-upload><i class="fas fa-upload"></i> Subir archivo</button>
        </nav>

        <header class="review-hero">
            <div class="review-hero-overlay"></div>
            <div class="review-hero-content">
                <div>
                    <span>Control de calidad</span>
                    <h1>Revisión de entregables</h1>
                    <p>{{ $tarea->titulo }}</p>
                </div>
                <span class="review-task-status {{ $estadoTareaClase }}"><i></i>{{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}</span>
            </div>
        </header>

        <main class="review-content">
            @if(session('success'))
                <div class="review-alert is-success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="review-alert is-error"><i class="fas fa-circle-exclamation"></i>{{ $errors->first() }}</div>
            @endif

            <section class="review-summary" aria-label="Resumen de archivos">
                <div><span>Entregables</span><strong>{{ $totalArchivos }}</strong></div>
                <div><span>Pendientes</span><strong>{{ $archivosPendientes }}</strong></div>
                <div><span>Aprobados</span><strong>{{ $archivosAprobados }}</strong></div>
                <div><span>Rechazados</span><strong>{{ $archivosRechazados }}</strong></div>
            </section>

            <div class="review-grid">
                <section class="review-card review-deliverables">
                    <header class="review-card-header">
                        <div><h2>Archivos adjuntos</h2><p>Descarga y define el resultado de cada entregable.</p></div>
                        @if($archivosAprobados > 0)
                            <a href="{{ route('administrador.publicaciones.publicar', ['tarea_id' => $tarea->id]) }}" class="review-publish"><i class="fas fa-rocket"></i> Publicar</a>
                        @endif
                    </header>

                    <div class="review-files">
                        @forelse($archivos as $archivo)
                            @php
                                $extension = strtolower($archivo->extension ?? '');
                                $fileIcon = match(true) {
                                    in_array($extension, ['jpg','jpeg','png','gif','webp']) => 'fa-file-image',
                                    $extension === 'pdf' => 'fa-file-pdf',
                                    in_array($extension, ['doc','docx']) => 'fa-file-word',
                                    in_array($extension, ['xls','xlsx']) => 'fa-file-excel',
                                    in_array($extension, ['mp4','mov','avi']) => 'fa-file-video',
                                    in_array($extension, ['mp3','wav']) => 'fa-file-audio',
                                    in_array($extension, ['zip','rar','7z']) => 'fa-file-zipper',
                                    default => 'fa-file',
                                };
                                $fileStatusClass = match($archivo->estado) {
                                    'aprobado' => 'is-approved',
                                    'rechazado' => 'is-rejected',
                                    default => 'is-pending',
                                };
                                $fileSize = $archivo->tamanio >= 1048576
                                    ? number_format($archivo->tamanio / 1048576, 2).' MB'
                                    : number_format($archivo->tamanio / 1024, 2).' KB';
                                $previewType = match(true) {
                                    in_array($extension, ['jpg','jpeg','png','gif','webp','svg'], true) => 'image',
                                    in_array($extension, ['mp4','mov','avi'], true) => 'video',
                                    default => null,
                                };
                            @endphp
                            <article class="review-file">
                                <div class="review-file-main">
                                    <span class="review-file-icon"><i class="fas {{ $fileIcon }}"></i></span>
                                    <div class="review-file-copy">
                                        <div class="review-file-title">
                                            <strong>{{ $archivo->nombre_original }}</strong>
                                            <span class="review-file-status {{ $fileStatusClass }}">{{ ucfirst($archivo->estado) }}</span>
                                        </div>
                                        <small>{{ strtoupper($extension ?: 'Archivo') }} · {{ $fileSize }} · Subido por {{ $archivo->user?->name ?? 'Usuario eliminado' }}</small>
                                        @if($archivo->descripcion)<p>{{ $archivo->descripcion }}</p>@endif
                                    </div>
                                </div>
                                <div class="review-file-actions">
                                    <div class="review-file-links">
                                        @if($previewType)
                                            <button type="button" class="review-file-preview" data-open-file-preview data-preview-type="{{ $previewType }}" data-preview-url="{{ Storage::url($archivo->ruta_archivo) }}" data-preview-title="{{ $archivo->nombre_original }}"><i class="fas fa-eye"></i> Ver</button>
                                        @endif
                                        <a href="{{ Storage::url($archivo->ruta_archivo) }}" download><i class="fas fa-download"></i> Descargar</a>
                                    </div>
                                    <form action="{{ route('administrador.tareas.archivos.update-estado', $archivo->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" name="estado" value="pendiente" class="is-neutral {{ $archivo->estado === 'pendiente' ? 'is-current' : '' }}">Pendiente</button>
                                        <button type="submit" name="estado" value="aprobado" class="is-approve {{ $archivo->estado === 'aprobado' ? 'is-current' : '' }}"><i class="fas fa-check"></i> Aprobar</button>
                                        <button type="submit" name="estado" value="rechazado" class="is-reject {{ $archivo->estado === 'rechazado' ? 'is-current' : '' }}"><i class="fas fa-xmark"></i> Rechazar</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="review-empty">
                                <h3>No hay archivos adjuntos</h3>
                                <p>Sube el primer entregable para iniciar el proceso de revisión.</p>
                                <button type="button" data-open-review-upload><i class="fas fa-upload"></i> Subir archivo</button>
                            </div>
                        @endforelse
                    </div>
                </section>

                <aside class="review-sidebar">
                    <section class="review-card">
                        <header class="review-card-header compact"><div><span>Contexto</span><h2>Información de la tarea</h2></div></header>
                        <div class="review-task-info">
                            <div class="review-description"><small>Descripción</small><p>{{ $tarea->descripcion ?: 'Sin descripción registrada.' }}</p></div>
                            <dl>
                                <div><dt>Inicio</dt><dd>{{ $tarea->fecha_inicio->format('d/m/Y') }}</dd></div>
                                <div><dt>Fecha límite</dt><dd>{{ $tarea->fecha_limite->format('d/m/Y') }}</dd></div>
                                <div><dt>Prioridad</dt><dd><span class="review-priority {{ $prioridadClase }}">{{ ucfirst($tarea->prioridad) }}</span></dd></div>
                            </dl>
                        </div>
                    </section>

                    <section class="review-card">
                        <header class="review-card-header compact"><div><span>Equipo</span><h2>Responsables</h2></div></header>
                        <div class="review-people">
                            <div><span>{{ strtoupper(mb_substr($tarea->creador?->name ?? 'SC', 0, 2)) }}</span><div><small>Creada por</small><strong>{{ $tarea->creador?->name ?? 'Sin información' }}</strong></div></div>
                            <div><span>{{ strtoupper(mb_substr($tarea->asignado?->name ?? 'SA', 0, 2)) }}</span><div><small>Asignada a</small><strong>{{ $tarea->asignado?->name ?? 'Sin asignar' }}</strong></div></div>
                        </div>
                    </section>
                </aside>
            </div>

            <div class="review-comments">
                @include('administrador.tareas.comentarios', ['tarea' => $tarea])
            </div>
        </main>
    </div>
</div>

<div class="review-preview-modal" id="review-preview-modal" hidden>
    <button type="button" class="review-preview-backdrop" data-close-file-preview aria-label="Cerrar vista previa"></button>
    <section class="review-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="review-preview-title">
        <header>
            <div><span>VISTA PREVIA</span><h2 id="review-preview-title">Archivo</h2></div>
            <button type="button" data-close-file-preview aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <div class="review-preview-stage">
            <img id="review-preview-image" alt="" hidden>
            <video id="review-preview-video" controls playsinline preload="metadata" hidden></video>
        </div>
    </section>
</div>

<div class="review-upload-drawer" id="review-upload-drawer" hidden data-open-on-load="{{ $abrirDrawer ? 'true' : 'false' }}">
    <button type="button" class="review-upload-backdrop" data-close-review-upload aria-label="Cerrar panel"></button>
    <aside class="review-upload-panel" role="dialog" aria-modal="true" aria-labelledby="review-upload-title">
        <header>
            <span><i class="fas fa-cloud-arrow-up"></i></span>
            <div><small>ENTREGABLE DE TAREA</small><h2 id="review-upload-title">Subir contenido</h2></div>
            <button type="button" data-close-review-upload aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <form method="POST" enctype="multipart/form-data" action="{{ route('administrador.tareas.archivos.store', $tarea->id) }}">
            @csrf
            <input type="hidden" name="contexto" value="revision">
            <div class="review-upload-body">
                <div class="review-upload-context"><i class="fas fa-list-check"></i><div><small>Tarea seleccionada</small><strong>{{ $tarea->titulo }}</strong></div></div>
                @if($reviewUploadErrors->any())
                    <div class="review-upload-errors"><i class="fas fa-circle-exclamation"></i><div>@foreach($reviewUploadErrors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif
                <div class="review-upload-field-label"><i class="fas fa-files"></i> Seleccionar archivos <span>*</span></div>
                <label class="review-upload-dropzone" for="review-upload-files">
                    <input id="review-upload-files" type="file" name="archivos[]" multiple required accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.pdf,.ai,.mp3,.wav,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.psd,.svg,.webp">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <strong>Haz clic o arrastra tus archivos aquí</strong>
                    <small>Imágenes, videos, audio, documentos y archivos comprimidos.</small>
                    <b>Máximo aproximado de 1 GB por archivo</b>
                </label>
                <div class="review-upload-files-list" id="review-upload-files-list" hidden><strong><i class="fas fa-list"></i> Archivos seleccionados</strong><ul></ul></div>
                <label class="review-upload-description" for="review-upload-description">
                    <span><i class="fas fa-align-left"></i> Descripción <small>(opcional)</small></span>
                    <textarea id="review-upload-description" name="descripcion" rows="4" placeholder="Describe el propósito de estos archivos...">{{ old('descripcion') }}</textarea>
                </label>
            </div>
            <footer>
                <button type="button" data-close-review-upload><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit"><i class="fas fa-upload"></i> Subir archivos</button>
            </footer>
        </form>
    </aside>
</div>

<style>
    .review-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}.review-shell{position:relative;width:100%}
    .review-hero{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.review-hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .review-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:22px;padding:30px 570px 30px max(48px,calc((100% - 1280px)/2))}.review-hero-content>div>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.review-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.review-hero p{margin:5px 0 0;color:#dbeafe;font-size:.74rem;font-weight:600}.review-task-status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(255,255,255,.42);border-radius:999px;background:rgba(255,255,255,.14);color:#fff;font-size:.61rem;font-weight:900;text-transform:uppercase}.review-task-status i{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.review-task-status.is-complete i{background:#bef264}.review-task-status.is-progress i{background:#fde047}.review-task-status.is-rejected i{background:#fda4af}
    .review-top-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:8px}.review-top-actions a,.review-top-actions button{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 12px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font:inherit;font-size:.66rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);cursor:pointer;transition:.18s}.review-top-actions .is-primary,.review-top-actions a:hover,.review-top-actions button:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#638522;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .review-content{width:min(1280px,calc(100% - 48px));margin:24px auto 0}.review-alert{margin-bottom:14px;padding:12px 14px;display:flex;align-items:center;gap:9px;border:1px solid;border-radius:10px;font-size:.65rem;font-weight:800}.review-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.review-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .review-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));overflow:hidden;margin-bottom:16px;border:1px solid #e2e5df;border-radius:12px;background:#fafbf9;box-shadow:0 5px 15px rgba(55,60,52,.04)}.review-summary>div{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-right:1px solid #e5e8e2}.review-summary>div:last-child{border-right:0}.review-summary span{color:#7c8479;font-size:.58rem;font-weight:800}.review-summary strong{color:#30362e;font-size:1rem;font-weight:900}
    .review-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr);gap:16px;align-items:start}.review-sidebar{display:grid;gap:16px}.review-card{overflow:hidden;border:1px solid #e1e3de;border-radius:13px;background:#fff;box-shadow:0 6px 18px rgba(55,60,52,.05)}.review-card-header{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px 15px;border-bottom:1px solid #e8ebe5}.review-card-header.compact{padding:16px 18px 14px}.review-card-header span{display:block;margin-bottom:3px;color:#117e8c;font-size:.56rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.review-card-header h2{margin:0;color:#302832;font-size:.98rem;font-weight:900}.review-card-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#7da533}.review-card-header p{margin:6px 0 0;color:#7e867b;font-size:.59rem}.review-publish{min-height:44px;display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:0 18px;border:1px solid #d85b16;border-radius:10px;background:linear-gradient(135deg,#ef6c22,#c94f0c);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;box-shadow:0 9px 20px rgba(201,79,12,.28);transition:.18s}.review-publish:hover{transform:translateY(-2px);filter:brightness(.96);box-shadow:0 13px 25px rgba(201,79,12,.34)}
    .review-files{padding:6px 18px 18px}.review-file{padding:14px 2px;border-bottom:1px solid #e8ebe5}.review-file:last-child{border-bottom:0}.review-file-main{display:flex;align-items:flex-start;gap:11px}.review-file-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 38px;border-radius:9px;background:#edf7f8;color:#117e8c}.review-file-copy{min-width:0;flex:1}.review-file-title{display:flex;align-items:center;justify-content:space-between;gap:12px}.review-file-title strong{overflow:hidden;color:#30362e;font-size:.69rem;font-weight:900;text-overflow:ellipsis;white-space:nowrap}.review-file-copy small{display:block;margin-top:4px;color:#8b9288;font-size:.54rem}.review-file-copy p{margin:9px 0 0;padding:8px 10px;border-left:3px solid #dfe7de;background:#fafbf9;color:#687066;font-size:.59rem;line-height:1.5}.review-file-status{padding:4px 7px;border-radius:999px;font-size:.52rem;font-weight:900}.review-file-status.is-approved{background:#edf7e5;color:#547a27}.review-file-status.is-rejected{background:#fff0f0;color:#a72d2d}.review-file-status.is-pending{background:#fff4da;color:#9a6512}
    .review-file-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:11px;padding-left:49px}.review-file-links{display:flex;align-items:center;gap:6px}.review-file-links a,.review-file-links button{min-height:31px;display:inline-flex;align-items:center;gap:6px;padding:0 9px;border:1px solid #d8e3c7;border-radius:7px;background:#fff;color:#638522;font:inherit;font-size:.57rem;font-weight:900;text-decoration:none;cursor:pointer}.review-file-links .review-file-preview{border-color:#bcdadd;background:#edf7f8;color:#117e8c}.review-file-links a:hover{border-color:#7da533;background:#f4f8ee}.review-file-links .review-file-preview:hover{border-color:#117e8c;background:#e1f1f3}.review-file-actions form{display:flex;gap:5px}.review-file-actions form button{min-height:29px;padding:0 8px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#687066;font-size:.53rem;font-weight:850;cursor:pointer}.review-file-actions button.is-approve{color:#547a27}.review-file-actions button.is-reject{color:#a72d2d}.review-file-actions button.is-current{border-color:currentColor;background:#f8faf7;box-shadow:0 0 0 2px rgba(80,90,78,.06)}.review-empty{padding:46px 20px;text-align:center}.review-empty h3{margin:0;color:#343a32;font-size:.86rem;font-weight:900}.review-empty p{margin:7px 0 17px;color:#838b80;font-size:.61rem}.review-empty button{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:0;border-radius:8px;background:#7da533;color:#fff;font:inherit;font-size:.6rem;font-weight:900;cursor:pointer}
    .review-task-info{padding:16px 18px}.review-description small{display:block;color:#858d82;font-size:.54rem;font-weight:900;text-transform:uppercase}.review-description p{margin:7px 0 0;color:#626a60;font-size:.63rem;line-height:1.55}.review-task-info dl{margin:15px 0 0;border-top:1px solid #e8ebe5}.review-task-info dl>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #eef0ec}.review-task-info dl>div:last-child{border-bottom:0}.review-task-info dt{color:#858d82;font-size:.57rem}.review-task-info dd{margin:0;color:#343a32;font-size:.62rem;font-weight:900}.review-priority{padding:4px 7px;border-radius:999px}.review-priority.is-urgent{background:#fff0f0;color:#a72d2d}.review-priority.is-high{background:#fff1e8;color:#b65c1c}.review-priority.is-medium{background:#eaf1ff;color:#355ca8}.review-priority.is-low{background:#edf7e5;color:#547a27}
    .review-people{padding:5px 18px 16px}.review-people>div{display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid #e8ebe5}.review-people>div:last-child{border-bottom:0}.review-people>div>span{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:9px;background:#117e8c;color:#fff;font-size:.6rem;font-weight:900}.review-people>div:nth-child(2)>span{background:#5b2b76}.review-people small,.review-people strong{display:block}.review-people small{color:#899087;font-size:.52rem;font-weight:850;text-transform:uppercase}.review-people strong{margin-top:3px;color:#343a32;font-size:.64rem;font-weight:900}.review-comments{margin-top:16px}
    .review-preview-modal{position:fixed;z-index:10200;inset:0;display:grid;place-items:center;padding:24px}.review-preview-modal[hidden]{display:none}.review-preview-backdrop{position:absolute;inset:0;border:0;background:rgba(18,23,16,.86);backdrop-filter:blur(6px);cursor:pointer}.review-preview-dialog{position:relative;width:min(1040px,100%);max-height:calc(100vh - 48px);display:flex;flex-direction:column;overflow:hidden;border:1px solid rgba(255,255,255,.18);border-radius:16px;background:#171a16;box-shadow:0 28px 80px rgba(0,0,0,.48);animation:reviewPreviewIn .2s ease both}@keyframes reviewPreviewIn{from{transform:scale(.96);opacity:0}to{transform:none;opacity:1}}.review-preview-dialog>header{min-height:66px;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:13px 16px 13px 20px;border-bottom:1px solid rgba(255,255,255,.12);background:#242a21;color:#fff}.review-preview-dialog>header>div{min-width:0}.review-preview-dialog>header span{display:block;color:#b9cf96;font-size:.52rem;font-weight:900;letter-spacing:.12em}.review-preview-dialog>header h2{overflow:hidden;margin:4px 0 0;font-size:.78rem;font-weight:900;text-overflow:ellipsis;white-space:nowrap}.review-preview-dialog>header button{width:39px;height:39px;display:grid;place-items:center;flex:0 0 39px;border:1px solid rgba(255,255,255,.18);border-radius:9px;background:rgba(255,255,255,.08);color:#fff;cursor:pointer}.review-preview-dialog>header button:hover{background:#ef6c22}.review-preview-stage{min-height:280px;display:grid;place-items:center;overflow:auto;background:radial-gradient(circle at center,#30362e,#121511)}.review-preview-stage img,.review-preview-stage video{display:block;max-width:100%;max-height:calc(100vh - 114px);object-fit:contain}.review-preview-stage img[hidden],.review-preview-stage video[hidden]{display:none}.review-preview-stage video{width:100%;background:#000}
    .review-upload-drawer{position:fixed;z-index:10100;inset:0;display:flex;justify-content:flex-end}.review-upload-drawer[hidden]{display:none}.review-upload-backdrop{position:absolute;inset:0;border:0;background:rgba(20,25,18,.72);backdrop-filter:blur(3px);cursor:pointer}.review-upload-panel{position:relative;width:min(560px,100%);height:100%;display:flex;flex-direction:column;background:#fff;box-shadow:-24px 0 60px rgba(25,35,20,.28);animation:reviewDrawerIn .24s ease both}@keyframes reviewDrawerIn{from{transform:translateX(100%)}to{transform:translateX(0)}}.review-upload-panel>header{display:flex;align-items:center;gap:14px;padding:22px 24px;background:#638522;color:#fff}.review-upload-panel>header>span{width:50px;height:50px;display:grid;place-items:center;flex:0 0 50px;border-radius:13px;background:#fff;color:#638522;font-size:1.2rem}.review-upload-panel>header>div{min-width:0;flex:1}.review-upload-panel>header small,.review-upload-panel>header h2{display:block;margin:0}.review-upload-panel>header small{color:#e5f0d4;font-size:.55rem;font-weight:900;letter-spacing:.11em}.review-upload-panel>header h2{margin-top:3px;font-size:1.25rem;font-weight:900}.review-upload-panel>header>button{width:38px;height:38px;border:1px solid #91aa62;border-radius:9px;background:#4f6d19;color:#fff;cursor:pointer}.review-upload-panel>form{min-height:0;display:flex;flex:1;flex-direction:column}.review-upload-body{min-height:0;flex:1;overflow-y:auto;padding:24px}.review-upload-context{display:flex;align-items:center;gap:11px;margin-bottom:20px;padding:13px;border-left:4px solid #ef6c22;background:#fff5ed}.review-upload-context>i{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:9px;background:#ef6c22;color:#fff}.review-upload-context small,.review-upload-context strong{display:block}.review-upload-context small{color:#a16236;font-size:.53rem;font-weight:900;text-transform:uppercase}.review-upload-context strong{overflow:hidden;margin-top:3px;color:#573c2c;font-size:.7rem;text-overflow:ellipsis;white-space:nowrap}.review-upload-errors{display:flex;gap:9px;margin-bottom:17px;padding:12px 13px;border-left:4px solid #b52f2f;background:#fff0f0;color:#a32f2f;font-size:.64rem}.review-upload-errors p{margin:0}.review-upload-errors p+p{margin-top:3px}.review-upload-field-label{margin-bottom:9px;color:#4b5148;font-size:.67rem;font-weight:900}.review-upload-field-label>i{margin-right:7px;color:#9ca3af}.review-upload-field-label>span{color:#dc2626}.review-upload-dropzone{position:relative;min-height:210px;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:26px;border:2px dashed #cfd5cb;border-radius:12px;background:#f9faf8;text-align:center;cursor:pointer;transition:.2s}.review-upload-dropzone:hover,.review-upload-dropzone.is-dragging{border-color:#7da533;background:#f3f7ed}.review-upload-dropzone input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}.review-upload-dropzone>i{color:#cbd1c7;font-size:2.7rem}.review-upload-dropzone strong{margin-top:12px;color:#687164;font-size:.72rem}.review-upload-dropzone small{max-width:420px;margin-top:7px;color:#929a8e;font-size:.56rem}.review-upload-dropzone b{margin-top:8px;color:#7da533;font-size:.54rem}.review-upload-files-list{margin-top:12px;padding:12px 14px;border:1px solid #e0e4dd;border-radius:10px;background:#fff}.review-upload-files-list>strong{display:block;margin-bottom:6px;color:#596154;font-size:.59rem}.review-upload-files-list ul{max-height:145px;overflow-y:auto;margin:0;padding:0;list-style:none}.review-upload-files-list li{display:flex;align-items:center;gap:8px;padding:8px 2px;border-bottom:1px solid #edf0ea;color:#747d70;font-size:.56rem}.review-upload-files-list li:last-child{border-bottom:0}.review-upload-description{display:block;margin-top:20px}.review-upload-description>span{display:block;margin-bottom:8px;color:#4b5148;font-size:.67rem;font-weight:900}.review-upload-description>span small{color:#929a8e;font-weight:600}.review-upload-description textarea{width:100%;padding:12px 13px;border:1px solid #d5dad2;border-radius:11px;background:#f9faf8;color:#384034;font:inherit;font-size:.68rem;resize:vertical;outline:none}.review-upload-description textarea:focus{border-color:#7da533;background:#fff;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.review-upload-panel footer{display:flex;justify-content:space-between;gap:12px;padding:17px 24px;border-top:1px solid #e1e5de;background:#fff}.review-upload-panel footer button{min-height:45px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 18px;border:1px solid #d1d6ce;border-radius:11px;background:#fff;color:#626b5e;font-size:.65rem;font-weight:900;cursor:pointer}.review-upload-panel footer button[type=submit]{border-color:#638522;background:#638522;color:#fff;box-shadow:0 8px 18px rgba(99,133,34,.22)}.review-upload-panel footer button[type=submit]:hover{transform:translateY(-1px);background:#526f1c}
    @media(max-width:1080px){.review-hero-content{padding-right:520px}.review-grid{grid-template-columns:1fr}.review-sidebar{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:900px){.review-top-actions{position:static;justify-content:center;padding:14px 24px 0}.review-top-actions a,.review-top-actions button{border-color:#d8e3c7;background:#f4f8ee;color:#638522}.review-top-actions .is-primary{background:#7da533;color:#fff}.review-hero{margin-top:14px}.review-hero-content{padding:28px 24px}.review-summary{grid-template-columns:repeat(2,1fr)}.review-summary>div:nth-child(2){border-right:0}.review-summary>div:nth-child(-n+2){border-bottom:1px solid #e5e8e2}}
    @media(max-width:640px){.review-page{padding-bottom:24px}.review-top-actions{display:grid;grid-template-columns:1fr;padding:12px}.review-top-actions a,.review-top-actions button{width:100%}.review-hero{min-height:195px;margin-top:0}.review-hero-content{min-height:195px;align-items:flex-start;flex-direction:column;justify-content:center;padding:26px 20px}.review-content{width:calc(100% - 24px);margin-top:14px}.review-summary{grid-template-columns:1fr}.review-summary>div{border-right:0;border-bottom:1px solid #e5e8e2}.review-summary>div:last-child{border-bottom:0}.review-sidebar{grid-template-columns:1fr}.review-card-header{align-items:flex-start;flex-direction:column}.review-publish{width:100%}.review-file-title{align-items:flex-start;flex-direction:column;gap:5px}.review-file-actions{align-items:flex-start;flex-direction:column;padding-left:0}.review-file-actions form{width:100%;display:grid;grid-template-columns:repeat(3,1fr)}.review-file-actions button{width:100%;padding:0 4px}.review-upload-panel{width:100%}.review-upload-body{padding:18px}.review-upload-panel footer{padding:14px 18px}.review-upload-panel footer button{flex:1}}
    @media(max-width:640px){.review-file-links{width:100%}.review-file-links a,.review-file-links button{flex:1;justify-content:center}.review-preview-modal{padding:10px}.review-preview-dialog{max-height:calc(100vh - 20px);border-radius:12px}.review-preview-stage img,.review-preview-stage video{max-height:calc(100vh - 86px)}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const drawer = document.getElementById('review-upload-drawer');
    const filesInput = document.getElementById('review-upload-files');
    const filesList = document.getElementById('review-upload-files-list');
    const dropzone = drawer?.querySelector('.review-upload-dropzone');
    const previewModal = document.getElementById('review-preview-modal');
    const previewImage = document.getElementById('review-preview-image');
    const previewVideo = document.getElementById('review-preview-video');
    const previewTitle = document.getElementById('review-preview-title');

    function syncBodyScroll() {
        const overlayOpen = (drawer && !drawer.hidden) || (previewModal && !previewModal.hidden);
        document.body.style.overflow = overlayOpen ? 'hidden' : '';
    }

    function renderFiles() {
        if (!filesInput || !filesList) return;
        const files = Array.from(filesInput.files || []);
        const list = filesList.querySelector('ul');
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
        filesList.hidden = files.length === 0;
    }

    function openDrawer() {
        if (!drawer) return;
        drawer.hidden = false;
        syncBodyScroll();
        drawer.querySelector('[data-close-review-upload]')?.focus();
    }

    function closeDrawer() {
        if (!drawer) return;
        drawer.hidden = true;
        syncBodyScroll();
        const url = new URL(window.location.href);
        if (url.searchParams.delete('subir')) window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
    }

    function openPreview(button) {
        if (!previewModal || !previewImage || !previewVideo || !previewTitle) return;
        const type = button.dataset.previewType;
        const url = button.dataset.previewUrl;
        const title = button.dataset.previewTitle || 'Archivo';
        previewTitle.textContent = title;
        previewImage.hidden = type !== 'image';
        previewVideo.hidden = type !== 'video';
        if (type === 'image') {
            previewImage.src = url;
            previewImage.alt = `Vista previa de ${title}`;
        } else {
            previewVideo.src = url;
            previewVideo.load();
        }
        previewModal.hidden = false;
        syncBodyScroll();
        previewModal.querySelector('[data-close-file-preview]')?.focus();
    }

    function closePreview() {
        if (!previewModal || !previewImage || !previewVideo) return;
        previewModal.hidden = true;
        previewImage.removeAttribute('src');
        previewImage.alt = '';
        previewVideo.pause();
        previewVideo.removeAttribute('src');
        previewVideo.load();
        syncBodyScroll();
    }

    document.querySelectorAll('[data-open-review-upload]').forEach(button => button.addEventListener('click', openDrawer));
    document.querySelectorAll('[data-open-file-preview]').forEach(button => button.addEventListener('click', () => openPreview(button)));
    drawer?.querySelectorAll('[data-close-review-upload]').forEach(button => button.addEventListener('click', closeDrawer));
    previewModal?.querySelectorAll('[data-close-file-preview]').forEach(button => button.addEventListener('click', closePreview));
    filesInput?.addEventListener('change', renderFiles);
    ['dragenter', 'dragover'].forEach(eventName => dropzone?.addEventListener(eventName, () => dropzone.classList.add('is-dragging')));
    ['dragleave', 'drop'].forEach(eventName => dropzone?.addEventListener(eventName, () => dropzone.classList.remove('is-dragging')));
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        if (previewModal && !previewModal.hidden) closePreview();
        else if (drawer && !drawer.hidden) closeDrawer();
    });
    if (drawer?.dataset.openOnLoad === 'true') openDrawer();
});
</script>
@endsection
