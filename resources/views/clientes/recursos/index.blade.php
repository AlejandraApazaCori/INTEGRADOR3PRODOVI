@extends('layouts.app2')
@section('title', 'Recursos')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div id="client-resources">
    <header class="resources-hero">
        <div class="resources-hero-content"><span>Material de tu marca</span><h1>Mis <b>recursos</b></h1><p>Comparte el contenido que quieres que utilicemos para crear las publicaciones de tu empresa.</p></div>
        <div class="resources-hero-side"><div class="resources-total"><small>Recursos</small><strong>{{ $recursos->count() }}</strong></div><div class="resources-mosaic" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div></div>
    </header>

    <main class="resources-content">
        @if(session('success'))<div class="resources-alert success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="resources-alert error"><i class="fas fa-circle-exclamation"></i>{{ $errors->first() }}</div>@endif

        <section class="resources-panel">
            <div class="resources-toolbar">
                <span class="resources-toolbar-icon"><i class="fas fa-folder-open"></i></span>
                <div><h2>Biblioteca de recursos</h2><p>Selecciona la empresa cuyos materiales deseas consultar</p></div>

                @if($empresas->isNotEmpty())
                    <div class="resources-company-select" id="resources-company-select">
                        <button type="button" aria-expanded="false"><i class="fas fa-building"></i><span>{{ $empresaSeleccionada->nombre_empresa }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="resources-company-menu">
                            @foreach($empresas as $empresa)
                                <a href="{{ route('clientes.recursos', ['empresa_id' => $empresa->id]) }}" class="{{ $empresaSeleccionada->id === $empresa->id ? 'is-selected' : '' }}"><i class="fas fa-store"></i><span>{{ $empresa->nombre_empresa }}</span>@if($empresaSeleccionada->id === $empresa->id)<i class="fas fa-check"></i>@endif</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <button type="button" class="add-resource-button" id="open-resource-modal"><i class="fas fa-plus"></i> Agregar recursos</button>
            </div>

            @if(!$empresaSeleccionada)
                <div class="resources-empty"><i class="fas fa-building-circle-exclamation"></i><h3>No tienes una empresa registrada</h3><p>Compra un plan y registra una empresa antes de agregar recursos.</p><a href="{{ route('clientes.planes.comprar') }}">Comprar un plan</a></div>
            @elseif($recursos->isEmpty())
                <div class="resources-empty"><i class="fas fa-cloud-arrow-up"></i><h3>Sube lo que quieres que utilicemos</h3><p>Pueden ser logos, fotografías, imágenes de productos o enlaces de videos de YouTube y Google Drive asociados a <strong>{{ $empresaSeleccionada->nombre_empresa }}</strong>.</p><button type="button" data-open-resources><i class="fas fa-plus"></i> Agregar mi primer recurso</button></div>
            @else
                <div class="resources-grid">
                    @foreach($recursos as $recurso)
                        <article class="resource-card">
                            @if($recurso->tipo === 'imagen')
                                <a href="{{ Storage::url($recurso->archivo_path) }}" target="_blank" class="resource-preview"><img src="{{ Storage::url($recurso->archivo_path) }}" alt="{{ $recurso->nombre }}"></a>
                            @else
                                <a href="{{ $recurso->url }}" target="_blank" rel="noopener noreferrer" class="resource-preview resource-link-preview"><i class="fab {{ str_contains($recurso->url, 'youtube') || str_contains($recurso->url, 'youtu.be') ? 'fa-youtube' : 'fa-google-drive' }}"></i><span>Abrir enlace</span></a>
                            @endif
                            <div class="resource-meta"><span><small>{{ $recurso->tipo === 'imagen' ? 'Imagen' : 'Enlace' }}</small><strong title="{{ $recurso->nombre }}">{{ $recurso->nombre }}</strong></span><form method="POST" action="{{ route('clientes.recursos.destroy', $recurso) }}" onsubmit="return confirm('¿Eliminar este recurso?')">@csrf @method('DELETE')<button aria-label="Eliminar recurso"><i class="fas fa-trash"></i></button></form></div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</div>

<div class="resource-modal hidden" id="resource-modal" role="dialog" aria-modal="true" aria-labelledby="resource-modal-title">
    <div class="resource-modal-backdrop" data-close-resource-modal></div>
    <div class="resource-modal-dialog">
        <header><div><span>NUEVO MATERIAL</span><h3 id="resource-modal-title">Agregar recursos</h3></div><button type="button" data-close-resource-modal><i class="fas fa-xmark"></i></button></header>
        @if($empresaSeleccionada)
            <form method="POST" action="{{ route('clientes.recursos.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="empresa_id" value="{{ $empresaSeleccionada->id }}">
                <div class="resource-modal-body">
                    <div class="resource-company-note"><i class="fas fa-building"></i><span>Estos recursos se asociarán a <strong>{{ $empresaSeleccionada->nombre_empresa }}</strong></span></div>
                    <label class="resource-dropzone" for="resource-images"><input id="resource-images" name="imagenes[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple><i class="fas fa-images"></i><strong>Selecciona una o varias imágenes</strong><small>JPG, PNG, GIF o WEBP · Máximo 10 MB por imagen</small><span id="resource-file-count">Explorar archivos</span></label>
                    <div class="resource-divider"><span>También puedes agregar enlaces</span></div>
                    <div class="resource-links-heading"><label class="resource-links-label" for="resource-link-0">Enlaces de YouTube o Google Drive</label><button type="button" id="add-resource-link" aria-label="Agregar otro enlace" title="Agregar otro enlace"><i class="fas fa-plus"></i></button></div>
                    <div class="resource-links-list" id="resource-links-list">
                        @foreach(old('enlaces', ['']) as $enlace)
                            <div class="resource-link-row">
                                <i class="fas fa-link"></i>
                                <input id="resource-link-{{ $loop->index }}" name="enlaces[]" type="url" value="{{ $enlace }}" placeholder="https://youtube.com/... o https://drive.google.com/...">
                                <button type="button" class="remove-resource-link" aria-label="Eliminar enlace"><i class="fas fa-xmark"></i></button>
                            </div>
                        @endforeach
                    </div>
                    <small class="resource-links-help">Presiona + para agregar otro enlace.</small>
                </div>
                <footer><button type="button" data-close-resource-modal>Cancelar</button><button type="submit"><i class="fas fa-cloud-arrow-up"></i> Subir recursos</button></footer>
            </form>
        @else
            <div class="resource-modal-body resource-no-company"><i class="fas fa-building-circle-xmark"></i><p>Necesitas registrar una empresa antes de subir recursos.</p><a href="{{ route('clientes.planes.comprar') }}">Comprar un plan</a></div>
        @endif
    </div>
</div>

<style>
    #client-resources{--purple:#5b2b76;--orange:#ee9f2b;--turquoise:#117e8c;--green:#7da533;min-height:100vh;background:#fff;color:#302834}.resources-hero{min-height:150px;display:flex;align-items:center;justify-content:space-between;gap:32px;padding:28px 32px;background:#242426;color:#fff}.resources-hero-content{max-width:720px}.resources-hero-content>span{display:block;margin-bottom:10px;color:var(--orange);font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.resources-hero h1{margin:0;font-size:clamp(1.65rem,3vw,2.35rem);font-weight:800;line-height:1.08;letter-spacing:-.035em}.resources-hero h1 b{color:var(--orange)}.resources-hero p{margin:11px 0 0;color:#aaa5ad;font-size:.86rem;line-height:1.55}.resources-hero-side{display:flex;align-items:center;gap:26px}.resources-total{min-width:112px;padding:13px 16px;border-left:4px solid var(--orange);background:#303033}.resources-total small,.resources-total strong{display:block}.resources-total small{color:#aaa5ad;font-size:.65rem;font-weight:800;text-transform:uppercase}.resources-total strong{margin-top:3px;font-size:1.55rem}.resources-mosaic{width:144px;height:96px;display:grid;grid-template-columns:repeat(3,1fr);grid-template-rows:repeat(2,1fr)}.resources-mosaic i:nth-child(1){background:#ef6c22;border-radius:100% 0 0}.resources-mosaic i:nth-child(2){background:#f5a900;border-radius:0 0 0 100%}.resources-mosaic i:nth-child(3){background:var(--purple);border-radius:100% 0}.resources-mosaic i:nth-child(4){background:var(--turquoise);border-radius:0 100%}.resources-mosaic i:nth-child(5){background:var(--green);border-radius:50%}.resources-mosaic i:nth-child(6){border:12px solid #607078;border-top-color:transparent;border-left-color:transparent;border-radius:50%;transform:rotate(45deg)}.resources-content{margin:32px}.resources-alert{display:flex;align-items:center;gap:9px;max-width:1320px;margin:0 auto 16px;padding:12px 15px;border-left:4px solid;font-size:.78rem;font-weight:800}.resources-alert.success{border-color:var(--green);background:#f3f7eb;color:#587923}.resources-alert.error{border-color:#bf4444;background:#fff0f0;color:#a33333}.resources-panel{max-width:1320px;margin:auto;border:1px solid #ded7e1;border-radius:5px;background:#fff;box-shadow:0 10px 28px #ded9e0}.resources-toolbar{position:relative;z-index:20;display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid #ded7e1;border-left:4px solid var(--orange);background:#f7f5f8}.resources-toolbar-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:3px;background:var(--orange);color:#242426}.resources-toolbar h2{margin:0;font-size:1rem;font-weight:900}.resources-toolbar p{margin:2px 0 0;color:#887d8c;font-size:.72rem}.resources-company-select{position:relative;margin-left:auto}.resources-company-select>button{min-width:190px;height:41px;display:flex;align-items:center;gap:9px;padding:0 12px;border:1px solid #d8cfdc;border-radius:3px;background:#fff;color:#514557;font-size:.76rem;font-weight:800;cursor:pointer}.resources-company-select>button span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.resources-company-select>button i:first-child{color:var(--turquoise)}.resources-company-select>button i:last-child{margin-left:auto;font-size:.62rem}.resources-company-menu{position:absolute;z-index:40;top:calc(100% + 6px);right:0;display:none;min-width:230px;padding:7px;border:1px solid #d8cfdc;border-radius:3px;background:#fff;box-shadow:0 16px 34px #cfc8d2}.resources-company-select.is-open .resources-company-menu{display:block}.resources-company-menu a{display:flex;align-items:center;gap:9px;padding:10px;border-radius:2px;color:#514557;font-size:.76rem;text-decoration:none}.resources-company-menu a i:first-child{color:var(--turquoise)}.resources-company-menu a i:last-child{margin-left:auto;color:var(--green)}.resources-company-menu a:hover,.resources-company-menu a.is-selected{background:#fff4e3;color:#9a620f;font-weight:800}.add-resource-button,.resources-empty button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 15px;border:0;border-radius:3px;background:var(--purple);color:#fff;font-size:.76rem;font-weight:900;cursor:pointer;box-shadow:0 4px 0 #432056}.resources-empty{padding:72px 22px;text-align:center}.resources-empty>i{color:var(--orange);font-size:2.6rem}.resources-empty h3{margin:16px 0 7px;font-size:1.08rem}.resources-empty p{max-width:610px;margin:0 auto 22px;color:#817585;font-size:.8rem;line-height:1.65}.resources-empty a{display:inline-block;padding:11px 16px;border-radius:3px;background:var(--purple);color:#fff;font-size:.77rem;font-weight:900;text-decoration:none}.resources-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;padding:22px}.resource-card{overflow:hidden;border:1px solid #e2dce4;border-radius:4px;background:#fff}.resource-preview{height:170px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f3f0f4;text-decoration:none}.resource-preview img{width:100%;height:100%;object-fit:cover}.resource-link-preview{flex-direction:column;gap:9px;color:var(--turquoise)}.resource-link-preview i{font-size:2.5rem}.resource-link-preview span{font-size:.72rem;font-weight:900}.resource-meta{display:flex;align-items:center;gap:10px;padding:12px}.resource-meta>span{min-width:0;flex:1}.resource-meta small,.resource-meta strong{display:block}.resource-meta small{color:var(--orange);font-size:.58rem;font-weight:900;text-transform:uppercase}.resource-meta strong{overflow:hidden;margin-top:3px;color:#413745;font-size:.74rem;text-overflow:ellipsis;white-space:nowrap}.resource-meta form{margin:0}.resource-meta button{width:30px;height:30px;border:1px solid #d7a1a1;border-radius:50%;background:#fff;color:#b33b3b;cursor:pointer}.resource-modal{position:fixed;z-index:2147483000;inset:0;display:flex;align-items:center;justify-content:center;padding:20px}.resource-modal.hidden{display:none}.resource-modal-backdrop{position:absolute;inset:0;background:rgba(18,14,20,.76);backdrop-filter:blur(5px)}.resource-modal-dialog{position:relative;width:min(650px,100%);max-height:calc(100vh - 40px);overflow:hidden;border-radius:5px;background:#fff;box-shadow:0 28px 80px rgba(0,0,0,.34)}.resource-modal-dialog>header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:5px solid var(--orange,#ee9f2b);background:#242426;color:#fff}.resource-modal-dialog>header span{display:block;margin-bottom:4px;color:#ee9f2b;font-size:.62rem;font-weight:900;letter-spacing:.12em}.resource-modal-dialog h3{margin:0;font-size:1.15rem}.resource-modal-dialog>header button{width:36px;height:36px;border:1px solid #565259;border-radius:3px;background:#343436;color:#fff}.resource-modal-body{max-height:calc(100vh - 190px);overflow-y:auto;padding:22px}.resource-company-note{display:flex;align-items:center;gap:10px;margin-bottom:17px;padding:12px;border-left:4px solid #117e8c;background:#edf7f8;color:#45636a;font-size:.76rem}.resource-dropzone{min-height:180px;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:24px;border:2px dashed #cfc6d2;border-radius:5px;background:#faf9fb;text-align:center;cursor:pointer}.resource-dropzone input{position:absolute;width:1px;height:1px;opacity:0}.resource-dropzone>i{color:#5b2b76;font-size:2rem}.resource-dropzone strong{margin-top:11px;font-size:.86rem}.resource-dropzone small{margin-top:5px;color:#887d8c;font-size:.66rem}.resource-dropzone span{margin-top:14px;padding:8px 12px;border-radius:3px;background:#5b2b76;color:#fff;font-size:.68rem;font-weight:900}.resource-divider{display:flex;align-items:center;gap:10px;margin:21px 0;color:#918696;font-size:.65rem;font-weight:800}.resource-divider:before,.resource-divider:after{content:'';height:1px;flex:1;background:#ded7e1}.resource-links-label{display:block;margin-bottom:7px;color:#665b6b;font-size:.68rem;font-weight:900;text-transform:uppercase}.resource-modal textarea{width:100%;padding:12px;border:1px solid #d8cfdc;border-radius:4px;background:#fff;color:#302834;font:inherit;font-size:.78rem;resize:vertical;outline:none}.resource-modal textarea:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.resource-links-help{display:block;margin-top:5px;color:#918696;font-size:.65rem}.resource-modal footer{display:flex;justify-content:flex-end;gap:9px;padding:14px 22px;border-top:1px solid #ded7e1;background:#f7f5f8}.resource-modal footer button{padding:10px 14px;border:1px solid #d8cfdc;border-radius:3px;background:#fff;color:#665b6b;font-size:.75rem;font-weight:900}.resource-modal footer button[type=submit]{border-color:#5b2b76;background:#5b2b76;color:#fff}.resource-no-company{text-align:center}.resource-no-company>i{color:#ee9f2b;font-size:2rem}.resource-no-company a{color:#5b2b76;font-weight:900}
    .resource-links-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}.resource-links-heading .resource-links-label{margin:0}.resource-links-heading>button{width:32px;height:32px;display:grid;place-items:center;border:0;border-radius:50%;background:#117e8c;color:#fff;cursor:pointer}.resource-links-list{display:grid;gap:9px}.resource-link-row{position:relative;display:grid;grid-template-columns:34px minmax(0,1fr) 34px;align-items:center;border:1px solid #d8cfdc;border-radius:4px;background:#fff}.resource-link-row>i{color:#117e8c;text-align:center;font-size:.75rem}.resource-link-row input{width:100%;height:44px;padding:0 8px;border:0;background:transparent;color:#302834;font:inherit;font-size:.76rem;outline:none}.resource-link-row:focus-within{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.remove-resource-link{width:27px;height:27px;display:grid;place-items:center;border:0;border-radius:50%;background:#f5e9e9;color:#a63b3b;cursor:pointer}.resource-link-row:only-child .remove-resource-link{visibility:hidden}.resource-links-help{display:block;margin-top:7px;color:#918696;font-size:.65rem}
    html[data-client-theme="dark"] #client-resources{background:#141216;color:#e9e5eb}html[data-client-theme="dark"] .resources-panel{border-color:#403943;background:#1e1b21;box-shadow:0 10px 28px #0d0b0e}html[data-client-theme="dark"] .resources-toolbar,html[data-client-theme="dark"] .resource-modal footer{border-color:#403943;background:#29252c}html[data-client-theme="dark"] .resources-toolbar p,html[data-client-theme="dark"] .resources-empty p{color:#b4abb8}html[data-client-theme="dark"] .resources-company-select>button,html[data-client-theme="dark"] .resources-company-menu{border-color:#4a434e;background:#29252c;color:#eee;box-shadow:0 16px 34px #0d0b0e}html[data-client-theme="dark"] .resources-company-menu a{color:#ddd8df}html[data-client-theme="dark"] .resources-company-menu a:hover,html[data-client-theme="dark"] .resources-company-menu a.is-selected{background:#3a3020;color:#efbd6f}html[data-client-theme="dark"] .resource-card{border-color:#403943;background:#242127}html[data-client-theme="dark"] .resource-preview{background:#29252c}html[data-client-theme="dark"] .resource-meta strong{color:#e9e5eb}html[data-client-theme="dark"] .resource-modal-dialog{background:#1e1b21;color:#eee}html[data-client-theme="dark"] .resource-company-note{background:#173136;color:#9ccdd2}html[data-client-theme="dark"] .resource-dropzone{border-color:#4a434e;background:#242127}html[data-client-theme="dark"] .resource-link-row{border-color:#4a434e;background:#29252c}html[data-client-theme="dark"] .resource-link-row input{color:#eee}html[data-client-theme="dark"] .resource-links-label{color:#c4bbc7}
    @media(max-width:820px){.resources-toolbar{flex-wrap:wrap}.resources-company-select{order:3;width:100%;margin-left:0}.resources-company-select>button{width:100%}.add-resource-button{margin-left:auto}.resources-mosaic{display:none}}@media(max-width:560px){.resources-hero{align-items:flex-start;flex-direction:column;padding:26px 20px}.resources-hero-side,.resources-total{width:100%}.resources-content{margin:20px 16px}.resources-toolbar>div:nth-child(2){width:calc(100% - 55px)}.add-resource-button{width:100%;margin:4px 0 0}.resources-grid{grid-template-columns:1fr;padding:16px}}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const select=document.getElementById('resources-company-select');
    const selectButton=select?.querySelector(':scope > button');
    selectButton?.addEventListener('click',function(event){event.stopPropagation();const open=!select.classList.contains('is-open');select.classList.toggle('is-open',open);this.setAttribute('aria-expanded',String(open))});
    document.addEventListener('click',()=>{select?.classList.remove('is-open');selectButton?.setAttribute('aria-expanded','false')});
    const modal=document.getElementById('resource-modal');
    const openModal=()=>{modal.classList.remove('hidden');document.body.style.overflow='hidden'};
    const closeModal=()=>{modal.classList.add('hidden');document.body.style.overflow=''};
    document.getElementById('open-resource-modal')?.addEventListener('click',openModal);
    document.querySelectorAll('[data-open-resources]').forEach(button=>button.addEventListener('click',openModal));
    document.querySelectorAll('[data-close-resource-modal]').forEach(button=>button.addEventListener('click',closeModal));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!modal.classList.contains('hidden'))closeModal()});
    const images=document.getElementById('resource-images'),count=document.getElementById('resource-file-count');
    images?.addEventListener('change',()=>{count.textContent=images.files.length?`${images.files.length} imagen${images.files.length===1?'':'es'} seleccionada${images.files.length===1?'':'s'}`:'Explorar archivos'});
    const linksList=document.getElementById('resource-links-list'),addLink=document.getElementById('add-resource-link');
    const bindRemoveButtons=()=>linksList?.querySelectorAll('.remove-resource-link').forEach(button=>{button.onclick=()=>{if(linksList.children.length>1)button.closest('.resource-link-row').remove()}});
    addLink?.addEventListener('click',()=>{const index=linksList.children.length,row=document.createElement('div');row.className='resource-link-row';row.innerHTML=`<i class="fas fa-link"></i><input id="resource-link-${index}" name="enlaces[]" type="url" placeholder="https://youtube.com/... o https://drive.google.com/..."><button type="button" class="remove-resource-link" aria-label="Eliminar enlace"><i class="fas fa-xmark"></i></button>`;linksList.appendChild(row);bindRemoveButtons();row.querySelector('input').focus()});
    bindRemoveButtons();
    @if($errors->any()) openModal(); @endif
});
</script>
@endsection
