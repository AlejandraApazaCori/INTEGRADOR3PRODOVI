@php
    $todosLosRecursos = $recursosCliente->concat($recursosAdministracion)->sortByDesc('created_at');
    $uploadErrors = $errors->getBag('default');
    $renameErrors = $errors->getBag('renameResource');
    $renameResource = session('rename_resource_id')
        ? $todosLosRecursos->firstWhere('id', (int) session('rename_resource_id'))
        : null;
@endphp

<section class="resource-library" id="campaign-resource-library" data-open-upload="{{ $uploadErrors->any() ? 'true' : 'false' }}" data-open-rename="{{ $renameErrors->any() ? 'true' : 'false' }}">
    <div class="resource-library-bar">
        <nav class="resource-library-tabs" aria-label="Filtrar recursos">
            <button type="button" class="is-active" data-resource-filter="all" aria-pressed="true">
                Todos los recursos <span>{{ $todosLosRecursos->count() }}</span>
            </button>
            <button type="button" data-resource-filter="cliente" aria-pressed="false">
                Recursos del cliente <span>{{ $recursosCliente->count() }}</span>
            </button>
            <button type="button" data-resource-filter="administracion" aria-pressed="false">
                Recursos del equipo <span>{{ $recursosAdministracion->count() }}</span>
            </button>
        </nav>

        <div class="resource-library-actions">
            <div class="resource-layout-switch" aria-label="Forma de visualizar los recursos">
                <button type="button" data-resource-layout="grid" aria-label="Vista en cuadrícula" title="Vista en cuadrícula"><i class="fas fa-grip"></i></button>
                <button type="button" class="is-active" data-resource-layout="list" aria-label="Vista en filas" title="Vista en filas"><i class="fas fa-list"></i></button>
            </div>
            @if($empresa)
                <button type="button" class="resource-upload-open" data-open-resource-upload><i class="fas fa-plus"></i> Subir recursos</button>
            @endif
        </div>
    </div>

    @if(!$empresa)
        <div class="resource-library-empty is-visible">
            <i class="fas fa-building-circle-exclamation"></i>
            <h3>Sin empresa vinculada</h3>
            <p>Esta campaña todavía no tiene una empresa asociada.</p>
        </div>
    @else
        <div class="resource-library-content is-list" data-resource-list>
            @foreach($todosLosRecursos as $recurso)
                <article class="resource-item" data-resource-origin="{{ $recurso->origen }}">
                    @if($recurso->tipo === 'imagen')
                        <a class="resource-item-preview" href="{{ Storage::url($recurso->archivo_path) }}" target="_blank">
                            <img src="{{ Storage::url($recurso->archivo_path) }}" alt="{{ $recurso->nombre }}">
                        </a>
                    @else
                        <a class="resource-item-preview is-link" href="{{ $recurso->url }}" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-link"></i>
                            <span>Abrir enlace</span>
                        </a>
                    @endif

                    <div class="resource-item-info">
                        <div class="resource-item-badges">
                            <span class="resource-origin-badge {{ $recurso->origen === 'cliente' ? 'is-client' : 'is-team' }}">
                                <i class="fas {{ $recurso->origen === 'cliente' ? 'fa-user' : 'fa-people-group' }}"></i>
                                {{ $recurso->origen === 'cliente' ? 'Cliente' : 'Equipo' }}
                            </span>
                            <span class="resource-type-badge">{{ $recurso->tipo === 'imagen' ? 'Imagen' : 'Enlace' }}</span>
                        </div>
                        <strong title="{{ $recurso->nombre }}">{{ $recurso->nombre }}</strong>
                        <small>
                            {{ $recurso->origen === 'administracion' ? ($recurso->creador?->name ?? 'Administración') : 'Subido por el cliente' }}
                            <span>·</span> {{ $recurso->created_at?->format('d/m/Y') }}
                        </small>
                        @if($recurso->origen === 'administracion')
                            <span class="resource-visibility-state {{ $recurso->visible_cliente ? 'is-visible' : 'is-private' }}">
                                <i class="fas {{ $recurso->visible_cliente ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                {{ $recurso->visible_cliente ? 'Visible para el cliente' : 'Solo equipo interno' }}
                            </span>
                        @endif
                    </div>

                    <div class="resource-item-actions">
                        <a href="{{ $recurso->tipo === 'imagen' ? Storage::url($recurso->archivo_path) : $recurso->url }}" target="_blank" rel="noopener noreferrer" title="Abrir recurso"><i class="fas fa-arrow-up-right-from-square"></i></a>
                        <button type="button" data-rename-resource data-resource-name="{{ $recurso->nombre }}" data-update-url="{{ route('administrador.campañas.recursos.nombre', [$campania, $recurso]) }}" title="Cambiar nombre"><i class="fas fa-pen"></i></button>
                        @if($recurso->origen === 'administracion')
                            <form action="{{ route('administrador.campañas.recursos.visibilidad', [$campania, $recurso]) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="visible_cliente" value="{{ $recurso->visible_cliente ? 0 : 1 }}">
                                <button type="submit" title="{{ $recurso->visible_cliente ? 'Ocultar al cliente' : 'Mostrar al cliente' }}"><i class="fas {{ $recurso->visible_cliente ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('administrador.campañas.recursos.destroy', [$campania, $recurso]) }}" method="POST" onsubmit="return confirm('¿Eliminar este recurso? Esta acción no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="is-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="resource-library-empty {{ $todosLosRecursos->isEmpty() ? 'is-visible' : '' }}" data-resource-empty="all">
            <i class="fas fa-box-open"></i><h3>No hay recursos</h3><p>Sube el primer material para comenzar.</p>
        </div>
        <div class="resource-library-empty" data-resource-empty="cliente">
            <i class="fas fa-folder-open"></i><h3>El cliente no subió ningún recurso</h3><p>Cuando agregue materiales desde su dashboard aparecerán aquí.</p>
        </div>
        <div class="resource-library-empty" data-resource-empty="administracion">
            <i class="fas fa-box-archive"></i><h3>El equipo todavía no agregó recursos</h3><p>Puedes subir imágenes o compartir enlaces.</p>
        </div>
    @endif
</section>

@if($empresa)
<div class="resource-upload-modal" id="campaign-resource-upload-modal" hidden role="dialog" aria-modal="true" aria-labelledby="campaign-resource-upload-title">
    <button type="button" class="resource-upload-backdrop" data-close-resource-upload aria-label="Cerrar"></button>
    <div class="resource-upload-dialog">
        <header>
            <span><i class="fas fa-cloud-arrow-up"></i></span>
            <div><h2 id="campaign-resource-upload-title">Subir recursos</h2><p>Para la campaña: <strong>{{ $campania->nombre }}</strong></p></div>
            <button type="button" data-close-resource-upload aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>

        <form action="{{ route('administrador.campañas.recursos.store', $campania) }}#recursos" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="resource-upload-body">
                @if($uploadErrors->any())
                    <div class="resource-upload-errors"><i class="fas fa-circle-exclamation"></i><div>@foreach($uploadErrors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif

                <div class="resource-upload-field-label"><i class="fas fa-images"></i> Seleccionar imágenes <span>*</span></div>
                <label class="resource-upload-dropzone" for="campaign-resource-images">
                    <input id="campaign-resource-images" type="file" name="imagenes[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    <span><i class="fas fa-cloud-arrow-up"></i></span>
                    <strong>Haz clic o arrastra tus imágenes aquí</strong>
                    <small id="campaign-resource-file-label">JPG, PNG, GIF o WEBP · Máximo 10 MB</small>
                </label>

                <div class="resource-selected-files" id="campaign-resource-selected-files" hidden></div>

                <div class="resource-upload-divider"><span>o agrega enlaces</span></div>

                <div class="resource-upload-links-head">
                    <label><i class="fas fa-link"></i> Enlaces de YouTube o Google Drive <span>(opcional)</span></label>
                    <button type="button" id="campaign-resource-add-link"><i class="fas fa-plus"></i> Otro enlace</button>
                </div>
                <div class="resource-upload-links" id="campaign-resource-links">
                    <div><i class="fas fa-link"></i><input type="url" name="enlaces[]" placeholder="https://..."></div>
                </div>

                <label class="resource-upload-visibility">
                    <input type="checkbox" name="visible_cliente" value="1" checked>
                    <span><i class="fas fa-check"></i></span>
                    <div><strong>Visible para el cliente</strong><small>Aparecerá en Recursos de su dashboard.</small></div>
                </label>
            </div>
            <footer>
                <button type="button" data-close-resource-upload>Cancelar</button>
                <button type="submit"><i class="fas fa-upload"></i> Subir recursos</button>
            </footer>
        </form>
    </div>
</div>
@endif

<div class="resource-rename-modal" id="campaign-resource-rename-modal" hidden role="dialog" aria-modal="true" aria-labelledby="campaign-resource-rename-title">
    <button type="button" class="resource-rename-backdrop" data-close-resource-rename aria-label="Cerrar"></button>
    <div class="resource-rename-dialog">
        <header>
            <span><i class="fas fa-pen"></i></span>
            <div><small>EDITAR RECURSO</small><h2 id="campaign-resource-rename-title">Cambiar nombre</h2></div>
            <button type="button" data-close-resource-rename aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <form id="campaign-resource-rename-form" method="POST" action="{{ $renameResource ? route('administrador.campañas.recursos.nombre', [$campania, $renameResource]) : '#' }}">
            @csrf @method('PATCH')
            <div class="resource-rename-body">
                @if($renameErrors->any())
                    <div class="resource-upload-errors"><i class="fas fa-circle-exclamation"></i><div>@foreach($renameErrors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif
                <label for="campaign-resource-new-name">Nombre del recurso</label>
                <div><i class="fas fa-file-signature"></i><input id="campaign-resource-new-name" type="text" name="nombre" value="{{ old('nombre', $renameResource?->nombre) }}" maxlength="255" required></div>
                <small>Puedes modificar el nombre sin alterar la imagen o el enlace original.</small>
            </div>
            <footer>
                <button type="button" data-close-resource-rename>Cancelar</button>
                <button type="submit"><i class="fas fa-check"></i> Guardar nombre</button>
            </footer>
        </form>
    </div>
</div>

<style>
.resource-library{--resource-purple:#5b2b76;--resource-purple-dark:#432056;--resource-purple-soft:#eee8f1;min-height:420px;background:#f7f5f8;color:#302834}
.resource-library-bar{min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:10px 16px;background:var(--resource-purple);color:#fff}.resource-library-tabs{align-self:stretch;display:flex;align-items:end;gap:4px}.resource-library-tabs button{height:48px;display:flex;align-items:center;gap:7px;padding:0 14px;border:0;border-bottom:3px solid transparent;background:var(--resource-purple);color:#d9cce0;font-size:.67rem;font-weight:900;cursor:pointer}.resource-library-tabs button:hover,.resource-library-tabs button.is-active{border-bottom-color:#fff;color:#fff}.resource-library-tabs button span{min-width:21px;height:21px;display:grid;place-items:center;padding:0 5px;border-radius:999px;background:#fff;color:var(--resource-purple);font-size:.55rem}.resource-library-actions{display:flex;align-items:center;gap:10px}.resource-layout-switch{display:flex;overflow:hidden;border:1px solid #89709a;border-radius:8px}.resource-layout-switch button{width:38px;height:38px;border:0;background:var(--resource-purple);color:#cdbed5;cursor:pointer}.resource-layout-switch button+button{border-left:1px solid #89709a}.resource-layout-switch button.is-active{background:#fff;color:var(--resource-purple)}.resource-upload-open{min-height:40px;display:inline-flex;align-items:center;gap:8px;padding:0 14px;border:0;border-radius:8px;background:#fff;color:var(--resource-purple);font-size:.65rem;font-weight:900;cursor:pointer}
.resource-library-content{padding:20px}.resource-library-content.is-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:15px}.resource-item[hidden]{display:none!important}.resource-library-content.is-list .resource-item{min-width:0;display:grid;grid-template-columns:64px minmax(0,1fr) auto;align-items:center;gap:13px;padding:10px 12px;border-bottom:1px solid #ded8e1;background:#fff}.resource-library-content.is-list .resource-item:first-child{border-radius:10px 10px 0 0}.resource-library-content.is-list .resource-item:last-child{border-bottom:0;border-radius:0 0 10px 10px}.resource-library-content.is-list .resource-item:only-child{border-radius:10px}.resource-library-content.is-list .resource-item-preview{width:64px;height:64px}
.resource-library-content.is-grid .resource-item{position:relative;min-width:0;overflow:hidden;border:1px solid #dcd4e0;border-radius:12px;background:#fff}.resource-library-content.is-grid .resource-item-preview{width:100%;aspect-ratio:1/1;display:flex}.resource-library-content.is-grid .resource-item-info{padding:13px}.resource-library-content.is-grid .resource-item-actions{padding:0 13px 13px}.resource-library-content.is-grid .resource-item-info>strong{font-size:.72rem}.resource-library-content.is-grid .resource-item-info>small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.resource-item-preview{display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:8px;background:var(--resource-purple-soft);color:var(--resource-purple);text-decoration:none}.resource-item-preview img{width:100%;height:100%;object-fit:cover}.resource-item-preview.is-link{flex-direction:column;gap:7px}.resource-item-preview.is-link i{font-size:1.2rem}.resource-item-preview.is-link span{font-size:.5rem;font-weight:900}.resource-item-info{min-width:0}.resource-item-badges{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:5px}.resource-item-badges>span{display:inline-flex;align-items:center;gap:4px;padding:4px 7px;border-radius:999px;font-size:.48rem;font-weight:900;text-transform:uppercase}.resource-origin-badge.is-client{background:#dff2f4;color:#116975}.resource-origin-badge.is-team{background:var(--resource-purple);color:#fff}.resource-type-badge{background:#ece9ed;color:#716978}.resource-item-info>strong,.resource-item-info>small,.resource-visibility-state{display:block}.resource-item-info>strong{overflow:hidden;color:#342c37;font-size:.7rem;text-overflow:ellipsis;white-space:nowrap}.resource-item-info>small{margin-top:4px;color:#918997;font-size:.51rem}.resource-item-info>small span{margin:0 3px}.resource-visibility-state{margin-top:6px;font-size:.51rem;font-weight:800}.resource-visibility-state.is-visible{color:#347544}.resource-visibility-state.is-private{color:#9a622b}.resource-item-actions{display:flex;align-items:center;justify-content:flex-end;gap:5px}.resource-item-actions form{margin:0}.resource-item-actions a,.resource-item-actions button{width:34px;height:34px;display:grid;place-items:center;border:1px solid #d8cfdd;border-radius:8px;background:#fff;color:var(--resource-purple);text-decoration:none;cursor:pointer}.resource-item-actions button.is-delete{border-color:#edcccc;color:#b23737}
.resource-library-empty{display:none;padding:76px 22px;text-align:center}.resource-library-empty.is-visible{display:block}.resource-library-empty>i{color:var(--resource-purple);font-size:2.15rem}.resource-library-empty h3{margin:13px 0 5px;color:#463b4a;font-size:.9rem}.resource-library-empty p{margin:0;color:#89808d;font-size:.65rem}
.resource-upload-modal{position:fixed;z-index:10050;inset:0;display:grid;place-items:center;padding:20px}.resource-upload-modal[hidden]{display:none}.resource-upload-backdrop{position:absolute;inset:0;border:0;background:#241c28;opacity:.82;cursor:pointer}.resource-upload-dialog{position:relative;width:min(620px,100%);max-height:calc(100vh - 40px);overflow:auto;border-radius:14px;background:#fff;box-shadow:0 26px 70px #18111c}.resource-upload-dialog>header{display:flex;align-items:center;gap:12px;padding:18px 20px;background:var(--resource-purple,#5b2b76);color:#fff}.resource-upload-dialog>header>span{width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;border-radius:10px;background:#fff;color:#5b2b76}.resource-upload-dialog>header>div{min-width:0;flex:1}.resource-upload-dialog>header small,.resource-upload-dialog>header h2{display:block;margin:0}.resource-upload-dialog>header small{color:#d9cce0;font-size:.52rem;font-weight:900;letter-spacing:.1em}.resource-upload-dialog>header h2{margin-top:3px;font-size:1.05rem}.resource-upload-dialog>header>button{width:36px;height:36px;border:1px solid #826692;border-radius:8px;background:#432056;color:#fff;cursor:pointer}.resource-upload-body{padding:20px}.resource-upload-errors{display:flex;gap:9px;margin-bottom:14px;padding:11px 12px;border-left:4px solid #b52f2f;background:#fff0f0;color:#a32f2f;font-size:.65rem}.resource-upload-errors p{margin:0}.resource-upload-errors p+p{margin-top:3px}
.resource-upload-dropzone{min-height:170px;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:22px;border:2px dashed #ad98b8;border-radius:11px;background:#f8f5fa;text-align:center;cursor:pointer}.resource-upload-dropzone input{position:absolute;width:1px;height:1px;opacity:0}.resource-upload-dropzone>span{width:48px;height:48px;display:grid;place-items:center;border-radius:12px;background:#5b2b76;color:#fff;font-size:1.1rem}.resource-upload-dropzone strong{margin-top:10px;color:#3f3344;font-size:.76rem}.resource-upload-dropzone small{margin-top:4px;color:#8d828f;font-size:.57rem}.resource-upload-dropzone b{margin-top:11px;padding:8px 11px;border-radius:7px;background:#5b2b76;color:#fff;font-size:.57rem}.resource-selected-files{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}.resource-selected-files span{max-width:180px;overflow:hidden;padding:6px 8px;border-radius:7px;background:#eee8f1;color:#5b2b76;font-size:.52rem;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.resource-upload-divider{display:flex;align-items:center;gap:10px;margin:18px 0;color:#8f8592;font-size:.56rem;font-weight:900;text-transform:uppercase}.resource-upload-divider:before,.resource-upload-divider:after{content:'';height:1px;flex:1;background:#ddd6e0}.resource-upload-links-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.resource-upload-links-head label{color:#554a59;font-size:.62rem;font-weight:900}.resource-upload-links-head label span{color:#948a97;font-weight:600}.resource-upload-links-head button{padding:6px 8px;border:0;border-radius:7px;background:#eee8f1;color:#5b2b76;font-size:.53rem;font-weight:900;cursor:pointer}.resource-upload-links{display:grid;gap:7px;margin-top:8px}.resource-upload-links>div{display:grid;grid-template-columns:36px minmax(0,1fr);align-items:center;border:1px solid #d9d1dd;border-radius:8px;background:#fff}.resource-upload-links i{color:#5b2b76;text-align:center}.resource-upload-links input{width:100%;height:41px;padding:0 10px 0 0;border:0;background:transparent;color:#3b333e;font-size:.65rem;outline:none}.resource-upload-visibility{position:relative;display:flex;align-items:center;gap:10px;margin-top:17px;padding:12px;border:1px solid #d9d1dd;border-radius:9px;background:#f8f5fa;cursor:pointer}.resource-upload-visibility>input{position:absolute;width:1px;height:1px;opacity:0}.resource-upload-visibility>span{width:24px;height:24px;display:grid;place-items:center;flex:0 0 24px;border:2px solid #b9aebd;border-radius:6px;background:#fff;color:transparent;font-size:.55rem}.resource-upload-visibility>input:checked+span{border-color:#5b2b76;background:#5b2b76;color:#fff}.resource-upload-visibility strong,.resource-upload-visibility small{display:block}.resource-upload-visibility strong{color:#4d424f;font-size:.63rem}.resource-upload-visibility small{margin-top:2px;color:#8d828f;font-size:.53rem}.resource-upload-dialog footer{display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid #ded7e1;background:#f6f3f7}.resource-upload-dialog footer button{min-height:39px;padding:0 13px;border:1px solid #d4cbd8;border-radius:8px;background:#fff;color:#675c6b;font-size:.61rem;font-weight:900;cursor:pointer}.resource-upload-dialog footer button[type=submit]{display:inline-flex;align-items:center;gap:7px;border-color:#5b2b76;background:#5b2b76;color:#fff}
.resource-rename-modal{position:fixed;z-index:10060;inset:0;display:grid;place-items:center;padding:20px}.resource-rename-modal[hidden]{display:none}.resource-rename-backdrop{position:absolute;inset:0;border:0;background:#241c28;opacity:.82;cursor:pointer}.resource-rename-dialog{position:relative;width:min(470px,100%);overflow:hidden;border-radius:14px;background:#fff;box-shadow:0 26px 70px #18111c}.resource-rename-dialog>header{display:flex;align-items:center;gap:12px;padding:18px 20px;background:#5b2b76;color:#fff}.resource-rename-dialog>header>span{width:42px;height:42px;display:grid;place-items:center;flex:0 0 42px;border-radius:10px;background:#fff;color:#5b2b76}.resource-rename-dialog>header>div{min-width:0;flex:1}.resource-rename-dialog>header small,.resource-rename-dialog>header h2{display:block;margin:0}.resource-rename-dialog>header small{color:#d9cce0;font-size:.52rem;font-weight:900;letter-spacing:.1em}.resource-rename-dialog>header h2{margin-top:3px;font-size:1.05rem}.resource-rename-dialog>header>button{width:36px;height:36px;border:1px solid #826692;border-radius:8px;background:#432056;color:#fff;cursor:pointer}.resource-rename-body{padding:22px}.resource-rename-body>label{display:block;margin-bottom:7px;color:#554a59;font-size:.63rem;font-weight:900}.resource-rename-body>div:not(.resource-upload-errors){display:grid;grid-template-columns:40px minmax(0,1fr);align-items:center;border:1px solid #d9d1dd;border-radius:9px;background:#fff}.resource-rename-body>div:not(.resource-upload-errors)>i{color:#5b2b76;text-align:center}.resource-rename-body input{width:100%;height:44px;padding:0 11px 0 0;border:0;background:transparent;color:#3b333e;font-size:.69rem;outline:none}.resource-rename-body>small{display:block;margin-top:7px;color:#8d828f;font-size:.53rem}.resource-rename-dialog footer{display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid #ded7e1;background:#f6f3f7}.resource-rename-dialog footer button{min-height:39px;padding:0 13px;border:1px solid #d4cbd8;border-radius:8px;background:#fff;color:#675c6b;font-size:.61rem;font-weight:900;cursor:pointer}.resource-rename-dialog footer button[type=submit]{display:inline-flex;align-items:center;gap:7px;border-color:#5b2b76;background:#5b2b76;color:#fff}

/* Lenguaje visual compartido con la carga de archivos de tareas */
.resource-library{overflow:hidden;border:1px solid #eceaf0;border-radius:16px;background:#fff;box-shadow:0 16px 38px #ded9e3}.resource-library-bar{min-height:76px;padding:12px 20px}.resource-library-content{background:#f8f7fa}.resource-library-content.is-list{padding:22px}.resource-library-content.is-list .resource-item{border-right:1px solid #e5e1e7;border-left:1px solid #e5e1e7}.resource-library-content.is-list .resource-item:first-child{border-top:1px solid #e5e1e7}.resource-library-content.is-list .resource-item:last-child{border-bottom:1px solid #e5e1e7}.resource-library-content.is-grid .resource-item{box-shadow:0 8px 20px #e3dfe6;transition:transform .2s ease,box-shadow .2s ease}.resource-library-content.is-grid .resource-item:hover{transform:translateY(-3px);box-shadow:0 13px 25px #d8d1dc}.resource-item-actions a:hover,.resource-item-actions button:hover{border-color:#5b2b76;background:#5b2b76;color:#fff}.resource-item-actions button.is-delete:hover{border-color:#b23737;background:#b23737;color:#fff}
.resource-upload-dialog{width:min(720px,100%);border-radius:16px;box-shadow:0 30px 80px #18111c}.resource-upload-dialog>header{gap:17px;padding:25px 28px}.resource-upload-dialog>header>span{width:56px;height:56px;flex-basis:56px;border-radius:14px;font-size:1.35rem}.resource-upload-dialog>header h2{font-size:1.45rem;font-weight:900}.resource-upload-dialog>header p{margin:5px 0 0;color:#ddd1e3;font-size:.7rem}.resource-upload-dialog>header p strong{color:#fff}.resource-upload-dialog>header>button{width:40px;height:40px}.resource-upload-body{padding:28px}.resource-upload-field-label{margin-bottom:9px;color:#4b4550;font-size:.68rem;font-weight:900}.resource-upload-field-label>i{margin-right:7px;color:#9ca3af}.resource-upload-field-label>span{color:#dc2626}.resource-upload-dropzone{position:relative;min-height:210px;padding:28px;border-color:#d1d5db;border-radius:12px;background:#f9fafb;transition:border-color .2s ease,background .2s ease}.resource-upload-dropzone:hover{border-color:#7c3aed;background:#f6f3f8}.resource-upload-dropzone>span{width:auto;height:auto;border-radius:0;background:transparent;color:#d1d5db;font-size:2.6rem}.resource-upload-dropzone strong{margin-top:12px;color:#6b7280;font-size:.73rem}.resource-upload-dropzone small{max-width:430px;margin-top:8px;color:#9ca3af;font-size:.57rem}.resource-selected-files{display:grid;gap:0;margin-top:12px;padding:10px 13px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}.resource-selected-files span{max-width:none;padding:8px 4px;border-bottom:1px solid #f0edf2;border-radius:0;background:#fff;color:#6b7280;font-size:.56rem}.resource-selected-files span:last-child{border-bottom:0}.resource-selected-files span:before{content:'\f15b';margin-right:8px;color:#9ca3af;font-family:'Font Awesome 6 Free';font-weight:900}.resource-upload-divider{margin:24px 0}.resource-upload-links-head label{font-size:.67rem}.resource-upload-links-head label>i{margin-right:6px;color:#9ca3af}.resource-upload-links>div{border-color:#d1d5db;border-radius:10px;background:#f9fafb}.resource-upload-links input{height:46px;font-size:.68rem}.resource-upload-links>div:focus-within{border-color:#7c3aed;background:#fff;box-shadow:0 0 0 3px #eee8f1}.resource-upload-visibility{margin-top:20px;padding:14px;border-color:#ddd6e0;border-radius:11px;background:#f8f5fa}.resource-upload-dialog footer{justify-content:space-between;padding:18px 28px;background:#fff}.resource-upload-dialog footer button{min-height:46px;padding:0 20px;border-radius:11px;font-size:.67rem}.resource-upload-dialog footer button[type=submit]{padding:0 22px;box-shadow:0 8px 18px #d7ccdc;transition:transform .2s ease,box-shadow .2s ease}.resource-upload-dialog footer button[type=submit]:hover{transform:translateY(-2px);box-shadow:0 12px 23px #cbbdd2}
.resource-rename-dialog{border-radius:16px}.resource-rename-dialog>header{padding:22px 24px}.resource-rename-body{padding:26px}.resource-rename-body>label{font-size:.68rem}.resource-rename-body input{height:48px;font-size:.72rem}.resource-rename-dialog footer{justify-content:space-between;padding:17px 24px;background:#fff}.resource-rename-dialog footer button{min-height:44px;padding:0 18px;border-radius:10px;font-size:.65rem}
.resource-upload-modal{display:flex;align-items:stretch;justify-content:flex-end;padding:0}.resource-upload-dialog{width:min(560px,100%);height:100%;max-height:none;display:flex;flex-direction:column;overflow:hidden;border-radius:0;box-shadow:-24px 0 65px #18111c;animation:resourceDrawerIn .24s ease both}@keyframes resourceDrawerIn{from{transform:translateX(100%)}to{transform:translateX(0)}}.resource-upload-dialog>header{flex:0 0 auto}.resource-upload-dialog>form{min-height:0;display:flex;flex:1;flex-direction:column}.resource-upload-body{min-height:0;flex:1;overflow-y:auto;padding:24px}.resource-upload-dialog footer{flex:0 0 auto;padding:17px 24px}.resource-upload-dropzone{min-height:190px}
@media(max-width:760px){.resource-library-bar{align-items:stretch;flex-direction:column;padding:10px}.resource-library-tabs{overflow-x:auto}.resource-library-tabs button{flex:1;justify-content:center;white-space:nowrap}.resource-library-actions{justify-content:space-between}.resource-library-content{padding:12px}.resource-library-content.is-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.resource-library-content.is-list .resource-item{grid-template-columns:52px minmax(0,1fr)}.resource-library-content.is-list .resource-item-preview{width:52px;height:52px}.resource-library-content.is-list .resource-item-actions{grid-column:1/-1}.resource-library-content.is-grid{grid-template-columns:1fr}.resource-upload-dialog{width:100%;max-height:none}.resource-upload-body{padding:17px}.resource-upload-dialog footer{padding:14px 17px}.resource-upload-dialog footer button{flex:1}}
</style>

<script>
(function initializeResourceLibrary(){
    const library = document.getElementById('campaign-resource-library');
    if (!library) return;
    const list = library.querySelector('[data-resource-list]');
    const items = Array.from(library.querySelectorAll('[data-resource-origin]'));
    const filterButtons = Array.from(library.querySelectorAll('[data-resource-filter]'));
    const emptyStates = Array.from(library.querySelectorAll('[data-resource-empty]'));

    function filterResources(filter) {
        let visibleCount = 0;
        items.forEach(item => {
            const visible = filter === 'all' || item.dataset.resourceOrigin === filter;
            item.hidden = !visible;
            if (visible) visibleCount++;
        });
        filterButtons.forEach(button => {
            const active = button.dataset.resourceFilter === filter;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        emptyStates.forEach(empty => empty.classList.toggle('is-visible', empty.dataset.resourceEmpty === filter && visibleCount === 0));
        if (list) list.hidden = visibleCount === 0;
    }

    filterButtons.forEach(button => button.addEventListener('click', () => filterResources(button.dataset.resourceFilter)));

    library.querySelectorAll('[data-resource-layout]').forEach(button => button.addEventListener('click', function () {
        const layout = this.dataset.resourceLayout;
        if (!list) return;
        list.classList.toggle('is-grid', layout === 'grid');
        list.classList.toggle('is-list', layout === 'list');
        library.querySelectorAll('[data-resource-layout]').forEach(option => option.classList.toggle('is-active', option === this));
        localStorage.setItem('campaignResourceLayout', layout);
    }));

    const savedLayout = localStorage.getItem('campaignResourceLayout');
    if (savedLayout === 'grid') library.querySelector('[data-resource-layout="grid"]')?.click();

    const modal = document.getElementById('campaign-resource-upload-modal');
    const openModal = () => { if (modal) { modal.hidden = false; document.body.style.overflow = 'hidden'; } };
    const closeModal = () => { if (modal) { modal.hidden = true; document.body.style.overflow = ''; } };
    document.querySelectorAll('[data-open-resource-upload]').forEach(button => button.addEventListener('click', openModal));
    modal?.querySelectorAll('[data-close-resource-upload]').forEach(button => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal && !modal.hidden) closeModal(); });
    if (library.dataset.openUpload === 'true') openModal();

    const input = document.getElementById('campaign-resource-images');
    const fileLabel = document.getElementById('campaign-resource-file-label');
    const selectedFiles = document.getElementById('campaign-resource-selected-files');
    input?.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        if (fileLabel) fileLabel.textContent = files.length ? `${files.length} imagen${files.length === 1 ? '' : 'es'} seleccionada${files.length === 1 ? '' : 's'}` : 'JPG, PNG, GIF o WEBP · Máximo 10 MB';
        if (selectedFiles) {
            selectedFiles.replaceChildren(...files.map(file => {
                const tag = document.createElement('span');
                tag.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                tag.title = file.name;
                return tag;
            }));
            selectedFiles.hidden = files.length === 0;
        }
    });

    document.getElementById('campaign-resource-add-link')?.addEventListener('click', function () {
        const container = document.getElementById('campaign-resource-links');
        if (!container || container.children.length >= 10) return;
        const row = document.createElement('div');
        row.innerHTML = '<i class="fas fa-link"></i><input type="url" name="enlaces[]" placeholder="https://...">';
        container.appendChild(row);
        row.querySelector('input')?.focus();
    });

    const renameModal = document.getElementById('campaign-resource-rename-modal');
    const renameForm = document.getElementById('campaign-resource-rename-form');
    const renameInput = document.getElementById('campaign-resource-new-name');
    const openRenameModal = button => {
        if (!renameModal || !renameForm || !renameInput) return;
        if (button) {
            renameForm.action = button.dataset.updateUrl;
            renameInput.value = button.dataset.resourceName;
        }
        renameModal.hidden = false;
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => { renameInput.focus(); renameInput.select(); }, 50);
    };
    const closeRenameModal = () => {
        if (!renameModal) return;
        renameModal.hidden = true;
        document.body.style.overflow = '';
    };
    library.querySelectorAll('[data-rename-resource]').forEach(button => button.addEventListener('click', () => openRenameModal(button)));
    renameModal?.querySelectorAll('[data-close-resource-rename]').forEach(button => button.addEventListener('click', closeRenameModal));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && renameModal && !renameModal.hidden) closeRenameModal(); });
    if (library.dataset.openRename === 'true') openRenameModal();

    filterResources('all');
})();
</script>
