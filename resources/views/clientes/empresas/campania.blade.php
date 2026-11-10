@extends('layouts.app2')

@section('title', 'Campaña de la empresa')

@section('content')
<main class="client-campaign-detail">
    <a class="client-campaign-back" href="{{ route('empresas.campanias', $empresa) }}"><i class="fas fa-arrow-left"></i> Todas las campañas</a>
    <header><small>{{ $empresa->nombre_empresa }} · {{ ucfirst($campania->estado) }}</small><h1>{{ $campania->nombre }}</h1><p>Publicaciones realizadas y piezas que requieren tu revisión.</p></header>
    @if(session('dashboard_review_success'))<div class="client-campaign-notice">{{ session('dashboard_review_success') }}</div>@endif
    @if($errors->any())<div class="client-campaign-notice is-error">{{ $errors->first() }}</div>@endif

    <section class="client-campaign-section">
        <h2>Publicaciones realizadas</h2>
        <div class="client-campaign-list">
            @forelse($publishedTasks as $task)
                <article class="client-campaign-item">
                    <div class="client-campaign-item-head"><div><small>{{ $task->published_at?->format('d/m/Y H:i') ?? 'Publicada' }}</small><h3>{{ $task->titulo }}</h3></div><span>Publicada</span></div>
                    @if($task->publication_message)<p>{{ $task->publication_message }}</p>@endif
                    <div class="client-campaign-tags">@foreach($task->publication_platforms ?? [] as $platform)<span>{{ ucfirst($platform) }}</span>@endforeach</div>
                    @foreach($task->archivos->where('estado', 'aprobado')->filter(fn ($file) => ! $task->revision_cliente_habilitada || $file->revision_cliente_estado === 'aprobado') as $file)
                        <a class="client-campaign-file" href="{{ $file->mediaUrl(true) }}"><i class="fas fa-file-arrow-down"></i> {{ $file->nombre_original }}</a>
                    @endforeach
                </article>
            @empty
                <p class="client-campaign-empty">Aún no hay publicaciones realizadas en esta campaña.</p>
            @endforelse
        </div>
    </section>

    @if($reviewTasks->isNotEmpty())
    <section class="client-campaign-section" id="revisiones">
        <h2>Revisar publicaciones</h2>
        <p class="client-campaign-explain">El equipo ya revisó estos entregables. Puedes aprobarlos, solicitar cambios o dejar retroalimentación.</p>
        <div class="client-campaign-list">
            @foreach($reviewTasks as $task)
                @foreach($task->archivos as $file)
                    <article class="client-campaign-item" id="revision-archivo-{{ $file->id }}">
                        <div class="client-campaign-item-head"><div><small>{{ $task->titulo }}</small><h3>{{ $file->nombre_original }}</h3></div><span>{{ match($file->revision_cliente_estado) { 'aprobado' => 'Aprobada', 'rechazado' => 'Cambios solicitados', default => 'Por revisar' } }}</span></div>
                        @if($file->descripcion)<p>{{ $file->descripcion }}</p>@endif
                        @if(str_starts_with((string) $file->mime_type, 'image/'))<img class="client-campaign-preview" src="{{ $file->mediaUrl() }}" alt="{{ $file->nombre_original }}">@elseif(str_starts_with((string) $file->mime_type, 'video/'))<video class="client-campaign-preview" src="{{ $file->mediaUrl() }}" controls preload="metadata"></video>@endif
                        <a class="client-campaign-file" href="{{ $file->mediaUrl(true) }}"><i class="fas fa-download"></i> Descargar archivo</a>
                        @if($file->revision_cliente_estado === 'pendiente')
                            <form action="{{ route('clientes.tareas.archivos.revision', $file) }}" method="POST" class="client-campaign-review-form">
                                @csrf
                                <label for="feedback-{{ $file->id }}">Retroalimentación <small>(opcional al decidir)</small></label>
                                <textarea id="feedback-{{ $file->id }}" name="comentario" rows="3" maxlength="2000" placeholder="Escribe una observación para el equipo..."></textarea>
                                <div><button type="submit" name="estado" value="rechazado" class="is-change">Solicitar cambios</button><button type="submit" name="estado" value="aprobado" class="is-approve">Aprobar pieza</button></div>
                            </form>
                        @elseif($file->revisado_cliente_at)
                            <small class="client-campaign-reviewed">Revisada por {{ $file->revisorCliente?->name ?? 'Cliente' }} el {{ $file->revisado_cliente_at->format('d/m/Y H:i') }}</small>
                        @endif
                    </article>
                @endforeach
            @endforeach
        </div>
    </section>
    @endif
</main>
<style>
.client-campaign-detail{max-width:1050px;margin:0 auto;padding:32px 22px 70px;color:#302834}.client-campaign-back{display:inline-flex;align-items:center;gap:8px;margin-bottom:22px;color:#117e8c;font-size:.78rem;font-weight:800;text-decoration:none}.client-campaign-detail>header{padding:27px;border-radius:12px;background:#242426;color:#fff}.client-campaign-detail>header small{color:#9bc249;font-size:.62rem;font-weight:900}.client-campaign-detail h1{margin:6px 0;font-size:1.7rem}.client-campaign-detail>header p{margin:0;color:#dedee0;font-size:.8rem}.client-campaign-section{margin-top:28px}.client-campaign-section h2{margin:0 0 14px;font-size:1.15rem}.client-campaign-explain{margin:-5px 0 15px;color:#677066;font-size:.75rem}.client-campaign-list{display:grid;gap:13px}.client-campaign-item{padding:20px;border:1px solid #e1e5df;border-radius:12px;background:#fff}.client-campaign-item-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.client-campaign-item-head small{color:#117e8c;font-size:.6rem;font-weight:800}.client-campaign-item h3{margin:4px 0 0;font-size:.92rem;overflow-wrap:anywhere}.client-campaign-item-head>span{flex:none;padding:5px 8px;border-radius:99px;background:#eef5e3;color:#587923;font-size:.58rem;font-weight:900}.client-campaign-item p{color:#596257;font-size:.74rem;line-height:1.6}.client-campaign-tags{display:flex;gap:7px;margin:10px 0}.client-campaign-tags span{padding:5px 8px;border-radius:6px;background:#f1f4ef;color:#586257;font-size:.62rem}.client-campaign-file{display:inline-flex;align-items:center;gap:7px;margin-top:10px;color:#117e8c;font-size:.7rem;font-weight:800;text-decoration:none}.client-campaign-preview{display:block;max-width:100%;max-height:420px;margin:15px 0;border-radius:8px;object-fit:contain}.client-campaign-review-form{display:grid;gap:10px;margin-top:18px;padding-top:17px;border-top:1px solid #e5e8e2}.client-campaign-review-form label{font-size:.7rem;font-weight:850}.client-campaign-review-form label small{font-weight:500}.client-campaign-review-form textarea{width:100%;padding:10px;border:1px solid #d8ddd5;border-radius:8px;font:inherit;font-size:.72rem}.client-campaign-review-form>div{display:flex;flex-wrap:wrap;gap:8px}.client-campaign-review-form button{padding:10px 13px;border:0;border-radius:8px;color:#fff;font-size:.68rem;font-weight:900;cursor:pointer}.client-campaign-review-form .is-change{background:#b65c31}.client-campaign-review-form .is-approve{background:#6f942c}.client-campaign-reviewed{display:block;margin-top:14px;color:#667060;font-size:.64rem}.client-campaign-empty{padding:23px;border:1px dashed #d8ddd2;border-radius:10px;color:#667060}.client-campaign-notice{margin-top:15px;padding:12px;border-left:4px solid #7da533;background:#f2f8e8;color:#50731d;font-size:.75rem}.client-campaign-notice.is-error{border-color:#ba5b31;background:#fff3ed;color:#9c451f}@media(max-width:600px){.client-campaign-item-head{flex-direction:column}}
</style>
@endsection
