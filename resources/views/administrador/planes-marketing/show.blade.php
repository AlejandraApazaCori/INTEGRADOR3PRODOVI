@extends('layouts.app')

@section('title', 'Plan de marketing')

@section('content')
<div class="marketing-plan-page">
    <header class="marketing-plan-hero">
        <div class="marketing-plan-overlay"></div>
        <div class="marketing-plan-hero-content">
            <div class="marketing-plan-identity">
                @if($planMarketing->empresa->logo)
                    <div class="marketing-plan-logo is-image"><img src="{{ Storage::url($planMarketing->empresa->logo) }}" alt="Logo de {{ $planMarketing->empresa->nombre_empresa }}"></div>
                @else
                    <div class="marketing-plan-logo"><i class="fas fa-bullseye"></i></div>
                @endif
                <div>
                    <span>Documento estratégico y operativo</span>
                    <h1>Plan de marketing</h1>
                    <p>{{ $planMarketing->empresa->nombre_empresa }} <b aria-hidden="true">•</b> {{ $planMarketing->suscripcion->plan->nombre }}</p>
                </div>
            </div>
            <div class="marketing-plan-hero-actions">
                <a href="{{ route('administrador.empresas.planes-marketing.edit', $planMarketing) }}" class="is-primary"><i class="fas fa-pen"></i>Editar plan</a>
                <a href="{{ route('administrador.empresas.show', $planMarketing->empresa_id) }}"><i class="fas fa-arrow-left"></i>Volver a la empresa</a>
            </div>
        </div>
    </header>

    @if(session('success'))<div class="marketing-plan-alert"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>@endif
    @if(session('drive_error'))<div class="marketing-plan-alert is-error"><i class="fab fa-google-drive"></i>{{ session('drive_error') }}</div>@endif

    <div class="marketing-plan-layout">
        <main class="marketing-plan-main">
            <div class="marketing-plan-intro">
                <span>Plan autogenerado</span>
                <h2>Estrategia lista para su ejecución</h2>
                <p>Documento construido a partir del cuestionario, el resumen ejecutivo y los recursos incluidos en el plan contratado.</p>
            </div>

            @forelse($secciones as $seccion)
                <section class="marketing-plan-section">
                    <header><span>{{ $loop->iteration }}</span><h2>{{ $seccion['titulo'] }}</h2></header>
                    <div class="marketing-plan-text">{!! $seccion['html'] !!}</div>
                </section>
            @empty
                <div class="marketing-plan-empty"><i class="fas fa-file-circle-exclamation"></i><strong>No hay secciones disponibles</strong><p>El plan no contiene información estructurada para mostrar.</p></div>
            @endforelse

            <footer class="marketing-plan-footer"><i class="fas fa-clock"></i>Plan generado el {{ $planMarketing->created_at->format('d/m/Y H:i') }}</footer>
        </main>

        <aside class="marketing-plan-aside">
            <div class="marketing-plan-sidebar">
                <section class="marketing-plan-summary">
                    <span class="marketing-plan-sidebar-label">Información del plan</span>
                    <h2>{{ $planMarketing->suscripcion->plan->nombre }}</h2>
                    <p>{{ $planMarketing->empresa->nombre_empresa }}</p>
                    <dl>
                        <div><dt><i class="fas fa-circle-check"></i> Estado</dt><dd>{{ ucfirst($planMarketing->estado) }}</dd></div>
                        <div><dt><i class="fas fa-calendar"></i> Creación</dt><dd>{{ $planMarketing->created_at->format('d/m/Y') }}</dd></div>
                        <div><dt><i class="fas fa-layer-group"></i> Estructura</dt><dd>{{ count($secciones) }} secciones estratégicas</dd></div>
                    </dl>
                </section>
                <section class="marketing-plan-actions">
                    <span class="marketing-plan-sidebar-label">Acciones</span>
                    <a href="{{ route('administrador.empresas.planes-marketing.edit', $planMarketing) }}" class="marketing-plan-edit"><i class="fas fa-pen-to-square"></i>Editar visualmente</a>
                    <a href="{{ route('administrador.empresas.planes-marketing.download-pdf', $planMarketing) }}" class="marketing-plan-pdf"><i class="fas fa-file-pdf"></i>Descargar PDF</a>
                    <button type="button" id="marketing-plan-drive-open" class="marketing-plan-doc"><i class="fab fa-google-drive"></i>Ver en Google Docs</button>
                    <a href="{{ route('administrador.empresas.reporte', $planMarketing->empresa_id) }}" class="marketing-plan-secondary"><i class="fas fa-file-lines"></i>Ver resumen ejecutivo</a>
                </section>
            </div>
        </aside>
    </div>

    <div id="marketing-plan-drive-modal" class="marketing-drive-modal hidden" role="dialog" aria-modal="true" aria-labelledby="marketing-drive-title">
        <div class="marketing-drive-dialog">
            <div class="marketing-drive-head"><div><h3 id="marketing-drive-title">Guardar plan de marketing en Drive</h3><p>El documento se guardará dentro del respaldo organizado de esta empresa.</p></div><button type="button" id="marketing-drive-close" aria-label="Cerrar"><i class="fas fa-times"></i></button></div>
            <form action="{{ route('administrador.empresas.planes-marketing.google-doc', $planMarketing) }}" method="POST">
                @csrf
                <div class="marketing-drive-location"><span><i class="fas fa-folder-tree"></i></span><div><small>Ubicación administrada</small><strong>PRODOVI / Empresas / {{ $planMarketing->empresa->nombre_empresa }}</strong><p id="marketing-drive-current">Consultando documento...</p></div></div>
                <div class="marketing-drive-editor">
                    <label for="marketing-drive-folder-trigger">Guardar en</label><input id="marketing-drive-folder" name="folder_id" type="hidden" value="">
                    <div class="marketing-drive-dropdown"><button id="marketing-drive-folder-trigger" type="button" disabled aria-haspopup="listbox" aria-expanded="false"><span><i class="fas fa-folder"></i><b id="marketing-drive-folder-label">Consultando carpetas...</b></span><i class="fas fa-chevron-down"></i></button><div id="marketing-drive-folder-options" class="marketing-drive-options hidden" role="listbox"></div></div>
                    <div class="marketing-drive-divider"><span></span><b>o crea una</b><span></span></div>
                    <label for="marketing-drive-new-folder">Nueva subcarpeta dentro de {{ $planMarketing->empresa->nombre_empresa }}</label><div class="marketing-drive-new"><i class="fas fa-folder-plus"></i><input id="marketing-drive-new-folder" name="new_folder" type="text" maxlength="80" placeholder="Ej.: Planes de marketing 2026"></div>
                </div>
                <p id="marketing-drive-status">Al guardar se creará un Google Docs con encabezado repetido y sin pie de página.</p>
                <div class="marketing-drive-buttons"><button type="button" id="marketing-drive-cancel">Cancelar</button><button type="submit" id="marketing-drive-save" disabled><i class="fab fa-google-drive"></i>Guardar y abrir</button></div>
            </form>
        </div>
    </div>
</div>

<style>
    .marketing-plan-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.marketing-plan-hero{position:relative;overflow:hidden;width:100%;min-height:180px;background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.marketing-plan-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%)}.marketing-plan-hero-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px}.marketing-plan-identity{min-width:0;display:flex;align-items:center;gap:18px}.marketing-plan-logo{width:58px;height:58px;display:grid;place-items:center;flex:none;overflow:hidden;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.4rem}.marketing-plan-logo.is-image{padding:7px;background:#fff}.marketing-plan-logo img{width:100%;height:100%;object-fit:contain}.marketing-plan-identity>div>span{display:block;margin-bottom:7px;color:#ecfccb;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.marketing-plan-hero h1{margin:0 0 5px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;line-height:1.1;letter-spacing:-.04em}.marketing-plan-hero p{margin:0;color:#f0fdf4;font-size:.74rem;font-weight:600}.marketing-plan-hero-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.marketing-plan-hero-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.3);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;transition:.18s}.marketing-plan-hero-actions a.is-primary,.marketing-plan-hero-actions a:hover{border-color:#fff;background:#fff;color:#638522;transform:translateY(-2px)}
    .marketing-plan-alert{width:calc(100% - 48px);max-width:1500px;margin:22px auto 0;padding:13px 16px;display:flex;align-items:center;gap:10px;border:1px solid #bfe3c5;border-radius:12px;background:#ecf8ee;color:#276738;font-size:.75rem;font-weight:750}.marketing-plan-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}.marketing-plan-layout{max-width:1500px;margin:0 auto;padding:34px 48px 0;display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);align-items:start;gap:48px}.marketing-plan-main,.marketing-plan-aside{min-width:0}.marketing-plan-intro{padding-bottom:28px;border-bottom:1px solid #e5e7eb}.marketing-plan-intro>span{display:block;color:#7da533;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.marketing-plan-intro h2{margin:7px 0 0;color:#302834;font-size:1.35rem;font-weight:900}.marketing-plan-intro h2:after{content:'';display:block;width:48px;height:3px;margin-top:9px;border-radius:999px;background:#7da533}.marketing-plan-intro p{max-width:780px;margin:11px 0 0;color:#737a70;font-size:.74rem;line-height:1.6}.marketing-plan-section{padding:34px 0;border-bottom:1px solid #e5e7eb}.marketing-plan-section>header{display:flex;align-items:flex-start;gap:13px;margin-bottom:20px}.marketing-plan-section>header>span{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:10px;background:#117e8c;color:#fff;font-size:.72rem;font-weight:900}.marketing-plan-section h2{margin:2px 0 0;color:#302834;font-size:1.08rem;font-weight:900;line-height:1.35}.marketing-plan-section h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:#7da533}.marketing-plan-text{padding-left:47px;color:#50574d;font-size:.78rem;line-height:1.8}.marketing-plan-text p{margin:0 0 13px}.marketing-plan-text h3{margin:20px 0 9px;color:#3c4438;font-size:.86rem;font-weight:900}.marketing-plan-text strong{color:#343b32;font-weight:900}.marketing-plan-text ul,.marketing-plan-text ol{margin:11px 0 14px;padding-left:22px}.marketing-plan-text ul{list-style:disc}.marketing-plan-text ol{list-style:decimal}.marketing-plan-text li{margin:6px 0}.marketing-plan-text table{width:100%;margin:14px 0 18px;border-collapse:collapse;font-size:.7rem}.marketing-plan-text th{padding:10px;border:1px solid #c7cbd1;background:#e5e7eb;color:#343a40;text-align:left}.marketing-plan-text td{padding:10px;border:1px solid #d7d9dc;vertical-align:top}.marketing-plan-footer{display:flex;align-items:center;gap:8px;padding-top:25px;color:#8a9186;font-size:.64rem}.marketing-plan-footer i{color:#7da533}.marketing-plan-empty{padding:50px 20px;color:#7c8379;text-align:center}.marketing-plan-empty i,.marketing-plan-empty strong,.marketing-plan-empty p{display:block}
    .marketing-plan-sidebar{position:sticky;top:24px;padding-left:28px;border-left:1px solid #e1e3de}.marketing-plan-sidebar-label{display:block;margin-bottom:9px;color:#7c8379;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.marketing-plan-summary{padding-bottom:26px;border-bottom:1px solid #e5e7eb}.marketing-plan-summary h2{margin:0;color:#302834;font-size:1.05rem;font-weight:900}.marketing-plan-summary>p{margin:5px 0 0;color:#737a70;font-size:.72rem}.marketing-plan-summary dl{display:grid;gap:13px;margin-top:20px}.marketing-plan-summary dt{color:#8a9186;font-size:.62rem;font-weight:800;text-transform:uppercase}.marketing-plan-summary dt i{width:16px;color:#117e8c}.marketing-plan-summary dd{margin:4px 0 0;color:#3f443d;font-size:.72rem;font-weight:700}.marketing-plan-actions{display:grid;gap:9px;padding-top:26px}.marketing-plan-actions a,.marketing-plan-actions button{min-height:44px;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:9px 12px;border-radius:11px;font-size:.69rem;font-weight:900;transition:.18s}.marketing-plan-edit{border:1px solid #7da533;background:#7da533;color:#fff}.marketing-plan-edit:hover{background:#638522}.marketing-plan-pdf{border:1px solid #f3c4c4;background:#fff;color:#b42323}.marketing-plan-pdf:hover{background:#fff0f0}.marketing-plan-doc{border:1px solid #cfd8f6;background:#fff;color:#3158a5;cursor:pointer}.marketing-plan-doc:hover{background:#eef2ff}.marketing-plan-secondary{border:1px solid #d9dcd6;background:#fff;color:#62685f}.marketing-plan-secondary:hover{border-color:#7da533;background:#f7faf2;color:#638522}
    .marketing-drive-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:16px;background:rgba(17,24,39,.58)}.marketing-drive-modal.flex{display:flex}.marketing-drive-dialog{width:100%;max-width:520px;padding:24px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.25)}.marketing-drive-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.marketing-drive-head h3{margin:0;color:#1f2937;font-size:1.18rem;font-weight:900}.marketing-drive-head p{margin:5px 0 0;color:#6b7280;font-size:.74rem}.marketing-drive-head>button{width:36px;height:36px;display:grid;place-items:center;flex:none;border-radius:50%;color:#6b7280}.marketing-drive-location{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dce7cc;border-radius:13px;background:#f7faf2}.marketing-drive-location>span{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:11px;background:#e4efd4;color:#638524}.marketing-drive-location div{min-width:0}.marketing-drive-location small,.marketing-drive-location strong,.marketing-drive-location p{display:block}.marketing-drive-location small{color:#7b8376;font-size:.6rem;font-weight:900;text-transform:uppercase}.marketing-drive-location strong{margin-top:3px;overflow:hidden;color:#30382b;font-size:.73rem;text-overflow:ellipsis;white-space:nowrap}.marketing-drive-location p{margin:4px 0 0;color:#7a8275;font-size:.64rem}.marketing-drive-editor{margin-top:15px;padding:15px;border:1px solid #dce7cc;border-radius:13px;background:#fbfcf9}.marketing-drive-editor label{display:block;margin-bottom:7px;color:#374151;font-size:.7rem;font-weight:900}.marketing-drive-editor select,.marketing-drive-editor input{width:100%;height:46px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;font-size:.75rem;outline:0}.marketing-drive-editor select{padding:0 12px}.marketing-drive-divider{display:flex;align-items:center;gap:10px;margin:14px 0}.marketing-drive-divider span{height:1px;flex:1;background:#e5e7eb}.marketing-drive-divider b{color:#9ca3af;font-size:.58rem;text-transform:uppercase}.marketing-drive-new{position:relative}.marketing-drive-new i{position:absolute;top:15px;left:14px;color:#7da533}.marketing-drive-new input{padding:0 12px 0 40px}.marketing-drive-status{margin:12px 0 0;color:#6b7280;font-size:.7rem}.marketing-drive-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:20px}.marketing-drive-buttons button{min-height:44px;border-radius:11px;font-size:.72rem;font-weight:900}.marketing-drive-buttons button:first-child{background:#f3f4f6;color:#5f6670}.marketing-drive-buttons button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#7da533;color:#fff}.marketing-drive-buttons button:disabled{cursor:not-allowed;opacity:.55}.hidden{display:none!important}
    .marketing-drive-dropdown{position:relative}.marketing-drive-dropdown>button{width:100%;height:46px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:0 13px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;outline:0;transition:.18s}.marketing-drive-dropdown>button>span{min-width:0;display:flex;align-items:center;gap:9px}.marketing-drive-dropdown>button>span>i{color:#7da533}.marketing-drive-dropdown>button b{overflow:hidden;font-size:.73rem;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.marketing-drive-dropdown>button>.fa-chevron-down{color:#929a8d;font-size:.65rem;transition:.18s}.marketing-drive-dropdown.is-open>button{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.marketing-drive-dropdown.is-open>button>.fa-chevron-down{transform:rotate(180deg)}.marketing-drive-dropdown>button:disabled{cursor:not-allowed;background:#f3f4f6;color:#9ca3af}.marketing-drive-options{position:absolute;z-index:15;top:calc(100% + 6px);right:0;left:0;max-height:210px;overflow:auto;padding:6px;border:1px solid #d7dce2;border-radius:12px;background:#fff;box-shadow:0 15px 35px rgba(31,41,55,.16)}.marketing-drive-options button{width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px;border-radius:8px;color:#4b5563;font-size:.71rem;font-weight:750;text-align:left}.marketing-drive-options button span{display:flex;align-items:center;gap:8px}.marketing-drive-options button span i{color:#8aa35d}.marketing-drive-options button:hover,.marketing-drive-options button.is-selected{background:#f1f6e9;color:#526d25}.marketing-drive-options button>.fa-check{color:#7da533;opacity:0}.marketing-drive-options button.is-selected>.fa-check{opacity:1}
    @media(max-width:850px){.marketing-plan-layout{grid-template-columns:1fr;gap:30px}.marketing-plan-sidebar{position:static;padding:28px 0 0;border-top:1px solid #e1e3de;border-left:0}}@media(max-width:700px){.marketing-plan-hero,.marketing-plan-hero-content{min-height:235px}.marketing-plan-hero-content{align-items:stretch;flex-direction:column;justify-content:center;padding:28px 20px}.marketing-plan-hero-actions{display:grid}.marketing-plan-layout{padding:28px 20px 0}.marketing-plan-text{padding-left:0}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('marketing-plan-drive-modal');
    const folder = document.getElementById('marketing-drive-folder');
    const folderDropdown = document.querySelector('.marketing-drive-dropdown');
    const folderTrigger = document.getElementById('marketing-drive-folder-trigger');
    const folderLabel = document.getElementById('marketing-drive-folder-label');
    const folderOptions = document.getElementById('marketing-drive-folder-options');
    const newFolder = document.getElementById('marketing-drive-new-folder');
    const current = document.getElementById('marketing-drive-current');
    const status = document.getElementById('marketing-drive-status');
    const save = document.getElementById('marketing-drive-save');
    const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
    const foldersUrl = @json(route('administrador.empresas.planes-marketing.drive-folders', $planMarketing));
    const closeModal = () => { folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false'); modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.classList.remove('overflow-hidden'); };
    const selectFolder = (id, name) => { folder.value = id; folderLabel.textContent = name; folderOptions.querySelectorAll('button').forEach(option => option.classList.toggle('is-selected', option.dataset.value === id)); folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false'); };
    folderTrigger.addEventListener('click', function () { if (folderTrigger.disabled) return; const opening = !folderDropdown.classList.contains('is-open'); folderDropdown.classList.toggle('is-open', opening); folderOptions.classList.toggle('hidden', !opening); folderTrigger.setAttribute('aria-expanded', String(opening)); });
    folderOptions.addEventListener('click', event => { const option = event.target.closest('[data-value]'); if (option) selectFolder(option.dataset.value, option.dataset.name); });

    document.getElementById('marketing-plan-drive-open')?.addEventListener('click', async function () {
        modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.classList.add('overflow-hidden');
        folder.value = ''; folderLabel.textContent = 'Consultando carpetas...'; folderOptions.innerHTML = ''; folderTrigger.disabled = true; newFolder.value = ''; save.disabled = true;
        current.textContent = 'Consultando documento...'; status.textContent = 'Preparando PRODOVI / Empresas / {{ $planMarketing->empresa->nombre_empresa }}...'; status.style.color = '';
        try {
            const response = await fetch(foldersUrl, {headers:{'Accept':'application/json'}}); const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No se pudieron consultar las carpetas.');
            const locations = [{id:data.root.id,name:`${data.root.name} (carpeta principal)`}, ...data.folders];
            folderOptions.innerHTML = locations.map(item => `<button type="button" role="option" data-value="${escapeHtml(item.id)}" data-name="${escapeHtml(item.name)}"><span><i class="fas fa-folder"></i>${escapeHtml(item.name)}</span><i class="fas fa-check"></i></button>`).join('');
            const selected = data.current_folder || locations[0]; selectFolder(selected.id, selected.name);
            current.textContent = data.current_document ? `Documento existente: ${data.current_document.name}` : 'El plan de marketing aún no fue creado';
            status.textContent = data.current_document ? 'Al guardar se actualizará el documento registrado.' : 'Puedes guardar en la carpeta de la empresa o crear una subcarpeta.';
            folderTrigger.disabled = false; save.disabled = false;
        } catch (error) {
            current.textContent = 'No se pudo consultar Drive'; status.textContent = error.message; status.style.color = '#b91c1c';
        }
    });
    document.getElementById('marketing-drive-close')?.addEventListener('click', closeModal);
    document.getElementById('marketing-drive-cancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('click', event => { if (!folderDropdown.contains(event.target)) { folderDropdown.classList.remove('is-open'); folderOptions.classList.add('hidden'); folderTrigger.setAttribute('aria-expanded', 'false'); } });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal?.classList.contains('flex')) closeModal(); });
});
</script>
@endsection
