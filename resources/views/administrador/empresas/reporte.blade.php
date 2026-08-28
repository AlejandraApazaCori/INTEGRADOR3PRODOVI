@extends('layouts.app')

@section('title', 'Resumen ejecutivo')

@section('content')
<div class="executive-report-page">
    <header class="executive-report-hero rp-banner">
        <div class="rp-banner-overlay"></div>
        <div class="executive-report-hero-content">
            <div class="executive-report-identity">
                @if($empresa->logo)
                    <div class="executive-report-logo is-image"><img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}"></div>
                @else
                    <div class="executive-report-logo" aria-hidden="true"><i class="fas fa-file-lines"></i></div>
                @endif
                <div>
                    <span class="executive-report-eyebrow">Documento estratégico</span>
                    <h1>Resumen ejecutivo</h1>
                    <p>{{ $empresa->nombre_empresa }} <span aria-hidden="true">•</span> {{ $empresa->tipo_empresa }}</p>
                </div>
            </div>
            <div class="executive-report-hero-actions">
                <a href="{{ route('administrador.empresas.reporte.pdf', $empresa->id) }}" class="is-primary"><i class="fas fa-file-pdf"></i>Descargar PDF</a>
                <a href="{{ route('administrador.empresas.show', $empresa->id) }}"><i class="fas fa-arrow-left"></i>Volver a la empresa</a>
            </div>
        </div>
    </header>

    @if(session('success'))<div class="executive-report-alert is-success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="executive-report-alert is-error"><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
    @if(session('drive_error'))<div class="executive-report-alert is-error"><i class="fab fa-google-drive"></i>{{ session('drive_error') }}</div>@endif

    <div class="executive-report-content">
        <main class="executive-report-main" id="reporte-contenido">
            <div class="executive-report-intro">
                <span>Resumen autogenerado</span>
                <h2>Diagnóstico estratégico empresarial</h2>
                <p>Documento elaborado a partir de las respuestas registradas en el cuestionario empresarial.</p>
            </div>

            @forelse($secciones as $seccion)
                <section class="executive-report-section">
                    <header><span>{{ $loop->iteration }}</span><h2>{{ $seccion['titulo'] }}</h2></header>
                    <div class="executive-report-text">{!! $seccion['html'] !!}</div>
                </section>
            @empty
                <div class="executive-report-empty"><i class="fas fa-file-circle-exclamation"></i><strong>No hay secciones disponibles</strong><p>El resumen ejecutivo no contiene información estructurada para mostrar.</p></div>
            @endforelse

            <footer class="executive-report-footer"><i class="fas fa-clock"></i>Documento consultado el {{ now()->format('d/m/Y H:i') }}</footer>
        </main>

        <aside class="executive-report-aside">
            <div class="executive-report-sidebar">
                <section class="executive-report-company">
                    <span class="executive-report-sidebar-label">Empresa</span>
                    <h2>{{ $empresa->nombre_empresa }}</h2>
                    <p>{{ $empresa->tipo_empresa }}</p>
                    <dl>
                        <div><dt><i class="fas fa-user"></i> Propietario</dt><dd>{{ $empresa->usuario->name }}</dd></div>
                        <div><dt><i class="fas fa-envelope"></i> Correo</dt><dd>{{ $empresa->usuario->email }}</dd></div>
                        <div><dt><i class="fas fa-layer-group"></i> Secciones</dt><dd>{{ count($secciones) }} apartados estratégicos</dd></div>
                    </dl>
                    @if($empresa->descripcion)<p class="executive-report-description">{{ $empresa->descripcion }}</p>@endif
                </section>

                <section class="executive-report-actions">
                    <span class="executive-report-sidebar-label">Acciones</span>
                    <form action="{{ route('administrador.empresas.regenerar-resumen', $empresa) }}" method="POST" onsubmit="return confirm('Se reemplazará el resumen actual usando las respuestas más recientes del cuestionario. ¿Deseas continuar?');">
                        @csrf
                        <button type="submit" class="executive-report-regenerate"><i class="fas fa-wand-magic-sparkles"></i>Regenerar con IA</button>
                    </form>
                    <a href="{{ route('administrador.empresas.reporte.pdf', $empresa->id) }}" class="executive-report-pdf"><i class="fas fa-file-pdf"></i>Descargar PDF</a>
                    <button type="button" id="executive-drive-open" class="executive-report-doc"><i class="fab fa-google-drive"></i>Ver en Google Docs</button>
                    <a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" class="executive-report-secondary"><i class="fas fa-clipboard-list"></i>Ver cuestionario</a>
                    <a href="{{ route('administrador.empresas.editar-resumen', $empresa->id) }}" class="executive-report-secondary"><i class="fas fa-pen"></i>Editar resumen</a>
                </section>
            </div>
        </aside>
    </div>

    <div id="executive-drive-modal" class="executive-drive-modal hidden" role="dialog" aria-modal="true" aria-labelledby="executive-drive-title">
        <div class="executive-drive-dialog">
            <div class="executive-drive-head">
                <div><h3 id="executive-drive-title">Guardar resumen ejecutivo en Drive</h3><p>El documento se guardará dentro del respaldo organizado de esta empresa.</p></div>
                <button type="button" id="executive-drive-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('administrador.empresas.reporte.google-doc', $empresa->id) }}" method="POST">
                @csrf
                <div class="executive-drive-location">
                    <span><i class="fas fa-folder-tree"></i></span>
                    <div><small>Ubicación administrada</small><strong>PRODOVI / Empresas / {{ $empresa->nombre_empresa }}</strong><p id="executive-drive-current">Consultando documento...</p></div>
                </div>
                <div class="executive-drive-editor">
                    <label for="executive-drive-folder-trigger">Guardar en</label>
                    <input id="executive-drive-folder" name="folder_id" type="hidden" value="">
                    <div class="executive-drive-dropdown" data-folder-dropdown>
                        <button id="executive-drive-folder-trigger" type="button" disabled aria-haspopup="listbox" aria-expanded="false"><span><i class="fas fa-folder"></i><b id="executive-drive-folder-label">Consultando carpetas...</b></span><i class="fas fa-chevron-down"></i></button>
                        <div id="executive-drive-folder-options" class="executive-drive-options hidden" role="listbox"></div>
                    </div>
                    <div class="executive-drive-divider"><span></span><b>o crea una</b><span></span></div>
                    <label for="executive-drive-new-folder">Nueva subcarpeta dentro de {{ $empresa->nombre_empresa }}</label>
                    <div class="executive-drive-new"><i class="fas fa-folder-plus"></i><input id="executive-drive-new-folder" name="new_folder" type="text" maxlength="80" placeholder="Ej.: Reportes ejecutivos 2026"></div>
                </div>
                <p id="executive-drive-status">Al guardar se creará un Google Docs con encabezado repetido y sin pie de página.</p>
                <div class="executive-drive-buttons"><button type="button" id="executive-drive-cancel">Cancelar</button><button type="submit" id="executive-drive-save" disabled><i class="fab fa-google-drive"></i>Guardar y abrir</button></div>
            </form>
        </div>
    </div>
</div>

<style>
    .executive-report-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.executive-report-hero{position:relative;overflow:hidden;width:100%;min-height:180px;background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.executive-report-hero .rp-banner-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-repeat:no-repeat}.executive-report-hero-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px}.executive-report-identity{min-width:0;display:flex;align-items:center;gap:18px}.executive-report-logo{width:58px;height:58px;display:grid;place-items:center;flex:0 0 auto;overflow:hidden;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.4rem;backdrop-filter:blur(5px)}.executive-report-logo.is-image{padding:7px;background:#fff}.executive-report-logo img{width:100%;height:100%;object-fit:contain}.executive-report-eyebrow{display:block;margin-bottom:7px;color:#ecfccb;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.executive-report-hero h1{margin:0 0 5px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;line-height:1.1;letter-spacing:-.04em}.executive-report-hero p{margin:0;color:#f0fdf4;font-size:.74rem;font-weight:600}.executive-report-hero-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.executive-report-hero-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.3);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;white-space:nowrap;backdrop-filter:blur(4px);transition:.18s}.executive-report-hero-actions a.is-primary,.executive-report-hero-actions a:hover{border-color:#fff;background:#fff;color:#638522;transform:translateY(-2px);box-shadow:0 8px 18px rgba(31,55,20,.18)}
    .executive-report-alert{width:calc(100% - 48px);max-width:1500px;margin:22px auto 0;padding:13px 16px;display:flex;align-items:center;gap:10px;border:1px solid;border-radius:12px;font-size:.75rem;font-weight:750}.executive-report-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.executive-report-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}.executive-report-content{max-width:1500px;margin:0 auto;padding:34px 48px 0;display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);align-items:start;gap:48px}.executive-report-main,.executive-report-aside{min-width:0}.executive-report-intro{padding-bottom:28px;border-bottom:1px solid #e5e7eb}.executive-report-intro>span{display:block;color:#7da533;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.executive-report-intro h2{margin:7px 0 0;color:#302834;font-size:1.35rem;font-weight:900}.executive-report-intro h2:after{content:'';display:block;width:48px;height:3px;margin-top:9px;border-radius:999px;background:#7da533}.executive-report-intro p{margin:11px 0 0;color:#737a70;font-size:.74rem;line-height:1.6}.executive-report-section{padding:34px 0;border-bottom:1px solid #e5e7eb}.executive-report-section header{display:flex;align-items:flex-start;gap:13px;margin-bottom:20px}.executive-report-section header>span{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:10px;background:#117e8c;color:#fff;font-size:.72rem;font-weight:900}.executive-report-section h2{margin:2px 0 0;color:#302834;font-size:1.08rem;font-weight:900;line-height:1.35}.executive-report-section h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:#7da533}.executive-report-text{padding-left:47px;color:#50574d;font-size:.78rem;line-height:1.8}.executive-report-text p{margin:0 0 13px}.executive-report-text p:last-child{margin-bottom:0}.executive-report-text strong{color:#343b32;font-weight:900}.executive-report-text ul,.executive-report-text ol{margin:11px 0 14px;padding-left:22px}.executive-report-text ul{list-style:disc}.executive-report-text ol{list-style:decimal}.executive-report-text li{margin:6px 0;padding-left:3px}.executive-report-text table{width:100%;margin:14px 0 18px;border:1px solid #dfe4da;border-collapse:separate;border-spacing:0;border-radius:11px;overflow:hidden;font-size:.7rem}.executive-report-text th{padding:10px 12px;background:#eef5e5;color:#415126;font-weight:900;text-align:left}.executive-report-text td{padding:10px 12px;border-top:1px solid #e5e9e1;color:#50574d;vertical-align:top}.executive-report-text th+th,.executive-report-text td+td{border-left:1px solid #e5e9e1}.executive-report-text tr:nth-child(even) td{background:#fafbf9}.executive-report-text blockquote{margin:14px 0;padding:11px 14px;border-left:4px solid #7da533;background:#f7faf2;color:#596153}.executive-report-footer{display:flex;align-items:center;gap:8px;padding-top:25px;color:#8a9186;font-size:.64rem}.executive-report-footer i{color:#7da533}.executive-report-empty{padding:50px 20px;color:#7c8379;text-align:center}.executive-report-empty i,.executive-report-empty strong,.executive-report-empty p{display:block}.executive-report-empty i{margin-bottom:12px;color:#aab0a7;font-size:1.6rem}.executive-report-empty strong{color:#3f443d;font-size:.85rem}.executive-report-empty p{margin-top:5px;font-size:.7rem}
    .executive-report-sidebar{position:sticky;top:24px;padding-left:28px;border-left:1px solid #e1e3de}.executive-report-sidebar-label{display:block;margin-bottom:9px;color:#7c8379;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.executive-report-company{padding-bottom:26px;border-bottom:1px solid #e5e7eb}.executive-report-company h2{margin:0;color:#302834;font-size:1.05rem;font-weight:900}.executive-report-company>p{margin:5px 0 0;color:#737a70;font-size:.72rem}.executive-report-company dl{display:grid;gap:13px;margin-top:20px}.executive-report-company dt{color:#8a9186;font-size:.62rem;font-weight:800;text-transform:uppercase}.executive-report-company dt i{width:16px;color:#117e8c}.executive-report-company dd{margin:4px 0 0;overflow-wrap:anywhere;color:#3f443d;font-size:.72rem;font-weight:700}.executive-report-description{margin-top:20px!important;padding-top:16px;border-top:1px solid #eef0ec;color:#737a70!important;font-size:.68rem!important;line-height:1.6}.executive-report-actions{display:grid;gap:10px;padding-top:26px}.executive-report-actions form{margin:0}.executive-report-actions a,.executive-report-actions button{min-height:44px;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:9px 12px;border-radius:11px;font-size:.69rem;font-weight:900;text-decoration:none;transition:.18s}.executive-report-regenerate{border:1px solid #7da533;background:#7da533;color:#fff;cursor:pointer}.executive-report-regenerate:hover{background:#638522;transform:translateY(-1px)}.executive-report-pdf{border:1px solid #f3c4c4;background:#fff;color:#b42323}.executive-report-pdf:hover{background:#fff0f0;color:#991b1b}.executive-report-doc{border:1px solid #cfd8f6;background:#fff;color:#4f46e5;cursor:pointer}.executive-report-doc:hover{background:#eef2ff;color:#4338ca}.executive-report-secondary{border:1px solid #d9dcd6;background:#fff;color:#62685f}.executive-report-secondary:hover{border-color:#7da533;background:#f7faf2;color:#638522}
    .executive-drive-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:16px;background:rgba(17,24,39,.58)}.executive-drive-modal.flex{display:flex}.executive-drive-dialog{width:100%;max-width:520px;padding:24px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.25)}.executive-drive-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.executive-drive-head h3{margin:0;color:#1f2937;font-size:1.18rem;font-weight:900}.executive-drive-head p{margin:5px 0 0;color:#6b7280;font-size:.74rem}.executive-drive-head>button{width:36px;height:36px;display:grid;place-items:center;flex:none;border-radius:50%;color:#6b7280}.executive-drive-head>button:hover{background:#f3f4f6}.executive-drive-location{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dce7cc;border-radius:13px;background:#f7faf2}.executive-drive-location>span{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:11px;background:#e4efd4;color:#638524}.executive-drive-location div{min-width:0}.executive-drive-location small,.executive-drive-location strong,.executive-drive-location p{display:block}.executive-drive-location small{color:#7b8376;font-size:.6rem;font-weight:900;text-transform:uppercase}.executive-drive-location strong{margin-top:3px;overflow:hidden;color:#30382b;font-size:.73rem;text-overflow:ellipsis;white-space:nowrap}.executive-drive-location p{margin:4px 0 0;color:#7a8275;font-size:.64rem}.executive-drive-editor{margin-top:15px;padding:15px;border:1px solid #dce7cc;border-radius:13px;background:#fbfcf9}.executive-drive-editor label{display:block;margin-bottom:7px;color:#374151;font-size:.7rem;font-weight:900}.executive-drive-editor select,.executive-drive-editor input{width:100%;height:46px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;font-size:.75rem;outline:0}.executive-drive-editor select{padding:0 12px}.executive-drive-editor select:focus,.executive-drive-editor input:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.executive-drive-divider{display:flex;align-items:center;gap:10px;margin:14px 0}.executive-drive-divider span{height:1px;flex:1;background:#e5e7eb}.executive-drive-divider b{color:#9ca3af;font-size:.58rem;text-transform:uppercase}.executive-drive-new{position:relative}.executive-drive-new i{position:absolute;top:15px;left:14px;color:#7da533}.executive-drive-new input{padding:0 12px 0 40px}.executive-drive-status{margin:12px 0 0;color:#6b7280;font-size:.7rem}.executive-drive-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:20px}.executive-drive-buttons button{min-height:44px;border-radius:11px;font-size:.72rem;font-weight:900}.executive-drive-buttons button:first-child{background:#f3f4f6;color:#5f6670}.executive-drive-buttons button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#7da533;color:#fff}.executive-drive-buttons button:disabled{cursor:not-allowed;opacity:.55}
    .executive-drive-dropdown{position:relative}.executive-drive-dropdown>button{width:100%;height:46px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 13px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;outline:0;transition:.18s}.executive-drive-dropdown>button>span{min-width:0;display:flex;align-items:center;gap:9px}.executive-drive-dropdown>button>span>i{color:#7da533}.executive-drive-dropdown>button b{overflow:hidden;font-size:.73rem;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.executive-drive-dropdown>button>.fa-chevron-down{color:#929a8d;font-size:.65rem;transition:.18s}.executive-drive-dropdown.is-open>button{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.executive-drive-dropdown.is-open>button>.fa-chevron-down{transform:rotate(180deg)}.executive-drive-dropdown>button:disabled{cursor:not-allowed;background:#f3f4f6;color:#9ca3af}.executive-drive-options{position:absolute;z-index:15;top:calc(100% + 6px);right:0;left:0;max-height:210px;overflow:auto;padding:6px;border:1px solid #d7dce2;border-radius:12px;background:#fff;box-shadow:0 15px 35px rgba(31,41,55,.16)}.executive-drive-options button{width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px;border-radius:8px;color:#4b5563;font-size:.71rem;font-weight:750;text-align:left}.executive-drive-options button span{display:flex;align-items:center;gap:8px}.executive-drive-options button span i{color:#8aa35d}.executive-drive-options button:hover,.executive-drive-options button.is-selected{background:#f1f6e9;color:#526d25}.executive-drive-options button>.fa-check{color:#7da533;opacity:0}.executive-drive-options button.is-selected>.fa-check{opacity:1}
    @media(max-width:850px){.executive-report-content{grid-template-columns:1fr;gap:30px}.executive-report-sidebar{position:static;padding:28px 0 0;border-top:1px solid #e1e3de;border-left:0}}
    @media(max-width:700px){.executive-report-hero,.executive-report-hero-content{min-height:235px}.executive-report-hero-content{align-items:stretch;flex-direction:column;justify-content:center;padding:28px 20px}.executive-report-identity{align-items:flex-start}.executive-report-hero-actions{display:grid;grid-template-columns:1fr}.executive-report-content{padding:28px 20px 0}.executive-report-text{padding-left:0}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('executive-drive-modal');
    const open = document.getElementById('executive-drive-open');
    const folder = document.getElementById('executive-drive-folder');
    const folderDropdown = document.querySelector('.executive-drive-dropdown');
    const folderTrigger = document.getElementById('executive-drive-folder-trigger');
    const folderLabel = document.getElementById('executive-drive-folder-label');
    const folderOptions = document.getElementById('executive-drive-folder-options');
    const newFolder = document.getElementById('executive-drive-new-folder');
    const current = document.getElementById('executive-drive-current');
    const status = document.getElementById('executive-drive-status');
    const save = document.getElementById('executive-drive-save');
    const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
    const foldersUrl = @json(route('administrador.empresas.reporte.drive-folders', $empresa->id));
    const closeModal = () => {
        folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false');
        modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.classList.remove('overflow-hidden');
    };
    const selectFolder = (id, name) => {
        folder.value = id; folderLabel.textContent = name;
        folderOptions.querySelectorAll('button').forEach(option => option.classList.toggle('is-selected', option.dataset.value === id));
        folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false');
    };
    folderTrigger.addEventListener('click', function () {
        if (folderTrigger.disabled) return;
        const opening = !folderDropdown.classList.contains('is-open');
        folderDropdown.classList.toggle('is-open', opening); folderOptions.classList.toggle('hidden', !opening); folderTrigger.setAttribute('aria-expanded', String(opening));
    });
    folderOptions.addEventListener('click', event => { const option = event.target.closest('[data-value]'); if (option) selectFolder(option.dataset.value, option.dataset.name); });

    open?.addEventListener('click', async function () {
        modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.classList.add('overflow-hidden');
        folder.value = ''; folderLabel.textContent = 'Consultando carpetas...'; folderOptions.innerHTML = ''; folderTrigger.disabled = true; newFolder.value = ''; save.disabled = true;
        current.textContent = 'Consultando documento...'; status.textContent = 'Preparando PRODOVI / Empresas / {{ $empresa->nombre_empresa }}...'; status.style.color = '';
        try {
            const response = await fetch(foldersUrl, {headers:{'Accept':'application/json'}}); const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No se pudieron consultar las carpetas.');
            const locations = [{id:data.root.id,name:`${data.root.name} (carpeta principal)`}, ...data.folders];
            folderOptions.innerHTML = locations.map(item => `<button type="button" role="option" data-value="${escapeHtml(item.id)}" data-name="${escapeHtml(item.name)}"><span><i class="fas fa-folder"></i>${escapeHtml(item.name)}</span><i class="fas fa-check"></i></button>`).join('');
            const selected = data.current_folder || locations[0]; selectFolder(selected.id, selected.name);
            current.textContent = data.current_document ? `Documento existente: ${data.current_document.name}` : 'El resumen ejecutivo aún no fue creado';
            status.textContent = data.current_document ? 'Al guardar se actualizará el documento registrado.' : 'Puedes guardar en la carpeta de la empresa o crear una subcarpeta.';
            folderTrigger.disabled = false; save.disabled = false;
        } catch (error) {
            current.textContent = 'No se pudo consultar Drive'; status.textContent = error.message; status.style.color = '#b91c1c';
        }
    });
    document.getElementById('executive-drive-close')?.addEventListener('click', closeModal);
    document.getElementById('executive-drive-cancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('click', event => { if (!folderDropdown.contains(event.target)) { folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false'); } });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal?.classList.contains('flex')) closeModal(); });
});
</script>
@endsection
