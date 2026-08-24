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
@endphp

<div class="review-page">
    <div class="review-shell">
        <nav class="review-top-actions" aria-label="Navegación de tarea">
            <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}"><i class="fas fa-arrow-left"></i> Campaña</a>
            <a href="{{ route('administrador.campañas.calendario', $tarea->campania_id) }}"><i class="fas fa-calendar-days"></i> Planificación</a>
            <a href="{{ route('administrador.tareas.edit', $tarea->id) }}"><i class="fas fa-pen"></i> Editar tarea</a>
            <a href="{{ route('administrador.tareas.archivos.create', $tarea->id) }}" class="is-primary"><i class="fas fa-upload"></i> Subir archivo</a>
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
                                    <a href="{{ Storage::url($archivo->ruta_archivo) }}" download><i class="fas fa-download"></i> Descargar</a>
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
                                <a href="{{ route('administrador.tareas.archivos.create', $tarea->id) }}"><i class="fas fa-upload"></i> Subir archivo</a>
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

<style>
    .review-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}.review-shell{position:relative;width:100%}
    .review-hero{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#1d4ed8}.review-hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .review-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:22px;padding:30px 570px 30px max(48px,calc((100% - 1280px)/2))}.review-hero-content>div>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.review-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.review-hero p{margin:5px 0 0;color:#dbeafe;font-size:.74rem;font-weight:600}.review-task-status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(255,255,255,.42);border-radius:999px;background:rgba(255,255,255,.14);color:#fff;font-size:.61rem;font-weight:900;text-transform:uppercase}.review-task-status i{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.review-task-status.is-complete i{background:#bef264}.review-task-status.is-progress i{background:#fde047}.review-task-status.is-rejected i{background:#fda4af}
    .review-top-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:8px}.review-top-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 12px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.66rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}.review-top-actions a.is-primary,.review-top-actions a:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .review-content{width:min(1280px,calc(100% - 48px));margin:24px auto 0}.review-alert{margin-bottom:14px;padding:12px 14px;display:flex;align-items:center;gap:9px;border:1px solid;border-radius:10px;font-size:.65rem;font-weight:800}.review-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.review-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .review-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));overflow:hidden;margin-bottom:16px;border:1px solid #e2e5df;border-radius:12px;background:#fafbf9;box-shadow:0 5px 15px rgba(55,60,52,.04)}.review-summary>div{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-right:1px solid #e5e8e2}.review-summary>div:last-child{border-right:0}.review-summary span{color:#7c8479;font-size:.58rem;font-weight:800}.review-summary strong{color:#30362e;font-size:1rem;font-weight:900}
    .review-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,.75fr);gap:16px;align-items:start}.review-sidebar{display:grid;gap:16px}.review-card{overflow:hidden;border:1px solid #e1e3de;border-radius:13px;background:#fff;box-shadow:0 6px 18px rgba(55,60,52,.05)}.review-card-header{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px 15px;border-bottom:1px solid #e8ebe5}.review-card-header.compact{padding:16px 18px 14px}.review-card-header span{display:block;margin-bottom:3px;color:#117e8c;font-size:.56rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.review-card-header h2{margin:0;color:#302832;font-size:.98rem;font-weight:900}.review-card-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.review-card-header p{margin:6px 0 0;color:#7e867b;font-size:.59rem}.review-publish{min-height:37px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border-radius:8px;background:#5b2b76;color:#fff;font-size:.62rem;font-weight:900;text-decoration:none}
    .review-files{padding:6px 18px 18px}.review-file{padding:14px 2px;border-bottom:1px solid #e8ebe5}.review-file:last-child{border-bottom:0}.review-file-main{display:flex;align-items:flex-start;gap:11px}.review-file-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 38px;border-radius:9px;background:#edf7f8;color:#117e8c}.review-file-copy{min-width:0;flex:1}.review-file-title{display:flex;align-items:center;justify-content:space-between;gap:12px}.review-file-title strong{overflow:hidden;color:#30362e;font-size:.69rem;font-weight:900;text-overflow:ellipsis;white-space:nowrap}.review-file-copy small{display:block;margin-top:4px;color:#8b9288;font-size:.54rem}.review-file-copy p{margin:9px 0 0;padding:8px 10px;border-left:3px solid #dfe7de;background:#fafbf9;color:#687066;font-size:.59rem;line-height:1.5}.review-file-status{padding:4px 7px;border-radius:999px;font-size:.52rem;font-weight:900}.review-file-status.is-approved{background:#edf7e5;color:#547a27}.review-file-status.is-rejected{background:#fff0f0;color:#a72d2d}.review-file-status.is-pending{background:#fff4da;color:#9a6512}
    .review-file-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:11px;padding-left:49px}.review-file-actions>a{display:inline-flex;align-items:center;gap:6px;color:#4f46e5;font-size:.57rem;font-weight:900;text-decoration:none}.review-file-actions form{display:flex;gap:5px}.review-file-actions button{min-height:29px;padding:0 8px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#687066;font-size:.53rem;font-weight:850;cursor:pointer}.review-file-actions button.is-approve{color:#547a27}.review-file-actions button.is-reject{color:#a72d2d}.review-file-actions button.is-current{border-color:currentColor;background:#f8faf7;box-shadow:0 0 0 2px rgba(80,90,78,.06)}.review-empty{padding:46px 20px;text-align:center}.review-empty h3{margin:0;color:#343a32;font-size:.86rem;font-weight:900}.review-empty p{margin:7px 0 17px;color:#838b80;font-size:.61rem}.review-empty a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border-radius:8px;background:#4f46e5;color:#fff;font-size:.6rem;font-weight:900;text-decoration:none}
    .review-task-info{padding:16px 18px}.review-description small{display:block;color:#858d82;font-size:.54rem;font-weight:900;text-transform:uppercase}.review-description p{margin:7px 0 0;color:#626a60;font-size:.63rem;line-height:1.55}.review-task-info dl{margin:15px 0 0;border-top:1px solid #e8ebe5}.review-task-info dl>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #eef0ec}.review-task-info dl>div:last-child{border-bottom:0}.review-task-info dt{color:#858d82;font-size:.57rem}.review-task-info dd{margin:0;color:#343a32;font-size:.62rem;font-weight:900}.review-priority{padding:4px 7px;border-radius:999px}.review-priority.is-urgent{background:#fff0f0;color:#a72d2d}.review-priority.is-high{background:#fff1e8;color:#b65c1c}.review-priority.is-medium{background:#eaf1ff;color:#355ca8}.review-priority.is-low{background:#edf7e5;color:#547a27}
    .review-people{padding:5px 18px 16px}.review-people>div{display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid #e8ebe5}.review-people>div:last-child{border-bottom:0}.review-people>div>span{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:9px;background:#117e8c;color:#fff;font-size:.6rem;font-weight:900}.review-people>div:nth-child(2)>span{background:#5b2b76}.review-people small,.review-people strong{display:block}.review-people small{color:#899087;font-size:.52rem;font-weight:850;text-transform:uppercase}.review-people strong{margin-top:3px;color:#343a32;font-size:.64rem;font-weight:900}.review-comments{margin-top:16px}
    @media(max-width:1080px){.review-hero-content{padding-right:520px}.review-grid{grid-template-columns:1fr}.review-sidebar{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:900px){.review-top-actions{position:static;justify-content:center;padding:14px 24px 0}.review-top-actions a{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.review-top-actions a.is-primary{background:#4f46e5;color:#fff}.review-hero{margin-top:14px}.review-hero-content{padding:28px 24px}.review-summary{grid-template-columns:repeat(2,1fr)}.review-summary>div:nth-child(2){border-right:0}.review-summary>div:nth-child(-n+2){border-bottom:1px solid #e5e8e2}}
    @media(max-width:640px){.review-page{padding-bottom:24px}.review-top-actions{display:grid;grid-template-columns:1fr;padding:12px}.review-top-actions a{width:100%}.review-hero{min-height:195px;margin-top:0}.review-hero-content{min-height:195px;align-items:flex-start;flex-direction:column;justify-content:center;padding:26px 20px}.review-content{width:calc(100% - 24px);margin-top:14px}.review-summary{grid-template-columns:1fr}.review-summary>div{border-right:0;border-bottom:1px solid #e5e8e2}.review-summary>div:last-child{border-bottom:0}.review-sidebar{grid-template-columns:1fr}.review-card-header{align-items:flex-start;flex-direction:column}.review-file-title{align-items:flex-start;flex-direction:column;gap:5px}.review-file-actions{align-items:flex-start;flex-direction:column;padding-left:0}.review-file-actions form{width:100%;display:grid;grid-template-columns:repeat(3,1fr)}.review-file-actions button{width:100%;padding:0 4px}}
</style>
@endsection
