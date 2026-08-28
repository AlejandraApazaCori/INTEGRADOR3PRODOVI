@extends('layouts.app')

@php
    $editorConfig = array_merge([
        'page_title' => 'Editar resumen ejecutivo',
        'eyebrow' => 'Documento estratégico',
        'hero_title' => 'Editar resumen ejecutivo',
        'hero_description' => 'Organiza y revisa cada sección antes de guardar',
        'back_label' => 'Volver al resumen',
        'back_url' => route('administrador.empresas.reporte', $empresa->id),
        'form_action' => route('administrador.empresas.update-resumen', $empresa->id),
        'intro_label' => 'Editor por secciones',
        'intro_title' => 'Contenido del documento',
        'intro_description' => 'Edita directamente el resultado final: las negritas, viñetas y tablas se muestran tal como aparecerán en el reporte.',
        'document_note' => 'Este resumen servirá como base para el plan de marketing.',
        'view_label' => 'Ver resumen actual',
    ], $editorConfig ?? []);
@endphp
@section('title', $editorConfig['page_title'])

@section('content')
<div class="summary-editor-page">
    <header class="summary-editor-hero">
        <div class="summary-editor-overlay"></div>
        <div class="summary-editor-hero-content">
            <div class="summary-editor-identity">
                @if($empresa->logo)
                    <div class="summary-editor-logo is-image"><img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}"></div>
                @else
                    <div class="summary-editor-logo" aria-hidden="true"><i class="fas fa-pen-to-square"></i></div>
                @endif
                <div>
                    <span>{{ $editorConfig['eyebrow'] }}</span>
                    <h1>{{ $editorConfig['hero_title'] }}</h1>
                    <p>{{ $empresa->nombre_empresa }} <b aria-hidden="true">•</b> {{ $editorConfig['hero_description'] }}</p>
                </div>
            </div>
            <a href="{{ $editorConfig['back_url'] }}" class="summary-editor-back"><i class="fas fa-arrow-left"></i>{{ $editorConfig['back_label'] }}</a>
        </div>
    </header>

    @if($errors->any())
        <div class="summary-editor-alert" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div><strong>Revisa la información antes de guardar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <form id="summary-editor-form" action="{{ $editorConfig['form_action'] }}" method="POST" class="summary-editor-layout">
        @csrf
        @method('PUT')

        <main class="summary-editor-main">
            <div class="summary-editor-intro">
                <span>{{ $editorConfig['intro_label'] }}</span>
                <h2>{{ $editorConfig['intro_title'] }}</h2>
                <p>{{ $editorConfig['intro_description'] }}</p>
            </div>

            <div id="summary-sections" class="summary-sections">
                @foreach($editorSections as $index => $seccion)
                    <section class="summary-section" data-section>
                        <header class="summary-section-head">
                            <span class="summary-section-number">{{ $index + 1 }}</span>
                            <div><small>Sección {{ $index + 1 }}</small><strong>{{ $seccion['titulo'] }}</strong></div>
                            <button type="button" class="summary-remove-section" title="Eliminar esta sección" aria-label="Eliminar esta sección"><i class="fas fa-trash-can"></i></button>
                        </header>

                        <div class="summary-field">
                            <label>Título de la sección</label>
                            <input type="text" name="secciones[{{ $index }}][titulo]" value="{{ $seccion['titulo'] }}" maxlength="160" required data-title-input>
                        </div>

                        <div class="summary-writing-panel">
                            <div class="summary-toolbar" aria-label="Herramientas de formato">
                                <button type="button" data-format="bold" title="Texto en negrita"><i class="fas fa-bold"></i><span>Negrita</span></button>
                                <button type="button" data-format="bullet" title="Lista con viñetas"><i class="fas fa-list-ul"></i><span>Viñetas</span></button>
                                <button type="button" data-format="ordered" title="Lista numerada"><i class="fas fa-list-ol"></i><span>Numeración</span></button>
                                <button type="button" data-format="table" title="Insertar una tabla"><i class="fas fa-table"></i><span>Tabla</span></button>
                            </div>
                            <div class="summary-table-tools hidden" data-table-tools>
                                <span><i class="fas fa-table-cells"></i>Editar tabla</span>
                                <button type="button" data-table-action="row-add"><i class="fas fa-plus"></i>Fila</button>
                                <button type="button" data-table-action="column-add"><i class="fas fa-plus"></i>Columna</button>
                                <button type="button" data-table-action="row-remove"><i class="fas fa-minus"></i>Fila</button>
                                <button type="button" data-table-action="column-remove"><i class="fas fa-minus"></i>Columna</button>
                                <button type="button" data-table-action="table-remove" class="is-danger"><i class="fas fa-trash"></i>Tabla</button>
                            </div>
                            <input type="hidden" name="secciones[{{ $index }}][contenido_html]" value="" data-content-input>
                            <div class="summary-visual-editor" contenteditable="true" role="textbox" aria-multiline="true" data-visual-editor data-placeholder="Escribe aquí el contenido de esta sección...">{!! $seccion['contenido_html'] !!}</div>
                            <div class="summary-field-meta"><span>Editas directamente el aspecto final del documento.</span><b data-character-count>0 caracteres</b></div>
                        </div>
                    </section>
                @endforeach
            </div>

            <button type="button" id="summary-add-section" class="summary-add-section"><i class="fas fa-plus"></i>Agregar otra sección</button>
        </main>

        <aside class="summary-editor-aside">
            <div class="summary-editor-sidebar">
                <section class="summary-company">
                    <span class="summary-sidebar-label">Empresa</span>
                    <h2>{{ $empresa->nombre_empresa }}</h2>
                    <p>{{ $empresa->tipo_empresa }}</p>
                    <dl>
                        <div><dt><i class="fas fa-user"></i> Propietario</dt><dd>{{ $empresa->usuario->name }}</dd></div>
                        <div><dt><i class="fas fa-envelope"></i> Correo</dt><dd>{{ $empresa->usuario->email }}</dd></div>
                        <div><dt><i class="fas fa-layer-group"></i> Estructura</dt><dd><span id="summary-section-total">{{ count($editorSections) }}</span> secciones</dd></div>
                    </dl>
                </section>

                <section class="summary-editor-help">
                    <span class="summary-sidebar-label">Guía rápida</span>
                    <ul>
                        <li><i class="fas fa-check"></i><span>Conserva únicamente información confirmada.</span></li>
                        <li><i class="fas fa-check"></i><span>Usa párrafos breves y títulos descriptivos.</span></li>
                        <li><i class="fas fa-check"></i><span>Las tablas son útiles para comparar datos.</span></li>
                    </ul>
                    <p><i class="fas fa-circle-info"></i>{{ $editorConfig['document_note'] }}</p>
                </section>

                <section class="summary-editor-actions">
                    <span class="summary-sidebar-label">Acciones</span>
                    <button type="submit" class="summary-save"><i class="fas fa-floppy-disk"></i>Guardar cambios</button>
                    <a href="{{ $editorConfig['back_url'] }}" target="_blank" class="summary-view"><i class="fas fa-arrow-up-right-from-square"></i>{{ $editorConfig['view_label'] }}</a>
                    <a href="{{ $editorConfig['back_url'] }}" class="summary-cancel"><i class="fas fa-xmark"></i>Cancelar</a>
                </section>
            </div>
        </aside>
    </form>
</div>

<template id="summary-section-template">
    <section class="summary-section" data-section>
        <header class="summary-section-head"><span class="summary-section-number"></span><div><small></small><strong>Nueva sección</strong></div><button type="button" class="summary-remove-section" title="Eliminar esta sección" aria-label="Eliminar esta sección"><i class="fas fa-trash-can"></i></button></header>
        <div class="summary-field"><label>Título de la sección</label><input type="text" maxlength="160" required data-title-input placeholder="Ej.: Recomendaciones estratégicas"></div>
        <div class="summary-writing-panel"><div class="summary-toolbar" aria-label="Herramientas de formato"><button type="button" data-format="bold"><i class="fas fa-bold"></i><span>Negrita</span></button><button type="button" data-format="bullet"><i class="fas fa-list-ul"></i><span>Viñetas</span></button><button type="button" data-format="ordered"><i class="fas fa-list-ol"></i><span>Numeración</span></button><button type="button" data-format="table"><i class="fas fa-table"></i><span>Tabla</span></button></div><div class="summary-table-tools hidden" data-table-tools><span><i class="fas fa-table-cells"></i>Editar tabla</span><button type="button" data-table-action="row-add"><i class="fas fa-plus"></i>Fila</button><button type="button" data-table-action="column-add"><i class="fas fa-plus"></i>Columna</button><button type="button" data-table-action="row-remove"><i class="fas fa-minus"></i>Fila</button><button type="button" data-table-action="column-remove"><i class="fas fa-minus"></i>Columna</button><button type="button" data-table-action="table-remove" class="is-danger"><i class="fas fa-trash"></i>Tabla</button></div><input type="hidden" value="" data-content-input><div class="summary-visual-editor" contenteditable="true" role="textbox" aria-multiline="true" data-visual-editor data-placeholder="Escribe aquí el contenido de esta sección..."></div><div class="summary-field-meta"><span>Editas directamente el aspecto final del documento.</span><b data-character-count>0 caracteres</b></div></div>
    </section>
</template>

<style>
    .summary-editor-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.summary-editor-hero{position:relative;overflow:hidden;width:100%;min-height:180px;background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.summary-editor-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%)}.summary-editor-hero-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px}.summary-editor-identity{min-width:0;display:flex;align-items:center;gap:18px}.summary-editor-logo{width:58px;height:58px;display:grid;place-items:center;flex:0 0 auto;overflow:hidden;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.4rem}.summary-editor-logo.is-image{padding:7px;background:#fff}.summary-editor-logo img{width:100%;height:100%;object-fit:contain}.summary-editor-identity>div>span{display:block;margin-bottom:7px;color:#ecfccb;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.summary-editor-hero h1{margin:0 0 5px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;line-height:1.1;letter-spacing:-.04em}.summary-editor-hero p{margin:0;color:#f0fdf4;font-size:.74rem;font-weight:600}.summary-editor-back{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid #fff;border-radius:.65rem;background:#fff;color:#638522;font-size:.69rem;font-weight:900;transition:.18s}.summary-editor-back:hover{color:#4f6c1b;transform:translateY(-2px);box-shadow:0 8px 18px rgba(31,55,20,.18)}
    .summary-editor-alert{width:calc(100% - 48px);max-width:1500px;margin:22px auto 0;padding:14px 16px;display:flex;align-items:flex-start;gap:11px;border:1px solid #f3c4c4;border-radius:12px;background:#fff0f0;color:#a72d2d;font-size:.72rem}.summary-editor-alert strong{font-weight:900}.summary-editor-alert ul{margin:5px 0 0;padding-left:18px}.summary-editor-layout{max-width:1500px;margin:0 auto;padding:34px 48px 0;display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);align-items:start;gap:48px}.summary-editor-main,.summary-editor-aside{min-width:0}.summary-editor-intro{padding-bottom:27px;border-bottom:1px solid #e5e7eb}.summary-editor-intro>span{display:block;color:#7da533;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.summary-editor-intro h2{margin:7px 0 0;color:#302834;font-size:1.35rem;font-weight:900}.summary-editor-intro h2:after{content:'';display:block;width:48px;height:3px;margin-top:9px;border-radius:999px;background:#7da533}.summary-editor-intro p{max-width:760px;margin:11px 0 0;color:#737a70;font-size:.74rem;line-height:1.6}.summary-sections{display:grid}.summary-section{padding:32px 0;border-bottom:1px solid #e5e7eb}.summary-section-head{display:flex;align-items:center;gap:12px;margin-bottom:20px}.summary-section-number{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:10px;background:#117e8c;color:#fff;font-size:.72rem;font-weight:900}.summary-section-head>div{min-width:0;flex:1}.summary-section-head small,.summary-section-head strong{display:block}.summary-section-head small{color:#8a9186;font-size:.6rem;font-weight:900;text-transform:uppercase}.summary-section-head strong{margin-top:2px;overflow:hidden;color:#343b32;font-size:.85rem;text-overflow:ellipsis;white-space:nowrap}.summary-remove-section{width:34px;height:34px;display:grid;place-items:center;flex:none;border:1px solid #ead4d4;border-radius:9px;background:#fff;color:#b44b4b;transition:.18s}.summary-remove-section:hover{background:#fff0f0;color:#991b1b}.summary-field{padding-left:46px}.summary-field label{display:block;margin-bottom:7px;color:#3f443d;font-size:.71rem;font-weight:900}.summary-field input,.summary-writing-panel textarea{width:100%;border:1px solid #d9dcd6;background:#fff;color:#3f443d;outline:0;transition:.18s}.summary-field input{height:45px;padding:0 13px;border-radius:11px;font-size:.78rem;font-weight:800}.summary-field input:focus,.summary-writing-panel textarea:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.12)}.summary-editor-tabs{display:flex;gap:5px;margin:20px 0 0 46px;padding-bottom:8px;border-bottom:1px solid #e5e7eb}.summary-editor-tabs button{display:inline-flex;align-items:center;gap:6px;padding:8px 10px;border-radius:8px;color:#7a8177;font-size:.67rem;font-weight:900}.summary-editor-tabs button.is-active{background:#eef5e5;color:#567622}.summary-writing-panel,.summary-preview-panel{margin-left:46px}.summary-toolbar{display:flex;flex-wrap:wrap;gap:5px;padding:9px;border:1px solid #d9dcd6;border-bottom:0;border-radius:11px 11px 0 0;background:#f6f7f5}.summary-toolbar button{min-height:31px;display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid transparent;border-radius:7px;color:#5f665c;font-size:.64rem;font-weight:850}.summary-toolbar button:hover{border-color:#d7dcd1;background:#fff;color:#4f6c1b}.summary-writing-panel textarea{display:block;min-height:210px;padding:14px;border-radius:0 0 11px 11px;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.74rem;line-height:1.65;resize:vertical}.summary-field-meta{display:flex;justify-content:space-between;gap:12px;margin-top:7px;color:#90958d;font-size:.61rem}.summary-field-meta b{color:#747b70}.summary-preview-panel{min-height:280px;padding:18px;border:1px solid #dfe4da;border-radius:0 0 12px 12px;background:#fbfcfa}.summary-preview-label{display:flex;align-items:center;gap:7px;margin-bottom:15px;color:#718060;font-size:.62rem;font-weight:900;text-transform:uppercase}.summary-preview{color:#50574d;font-size:.75rem;line-height:1.75}.summary-preview p{margin:0 0 11px}.summary-preview strong{color:#343b32}.summary-preview ul,.summary-preview ol{margin:8px 0 12px;padding-left:22px}.summary-preview li{margin:4px 0}.summary-preview table{width:100%;margin:10px 0;border-collapse:collapse;font-size:.68rem}.summary-preview th{padding:8px;border:1px solid #c7cbd1;background:#e5e7eb;color:#343a40;text-align:left}.summary-preview td{padding:8px;border:1px solid #d7d9dc}.summary-preview-empty{padding:55px 10px;color:#9aa096;text-align:center}.summary-add-section{min-height:45px;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:24px;border:1px dashed #aeb99e;border-radius:11px;background:#f9fbf6;color:#638522;font-size:.7rem;font-weight:900}.summary-add-section:hover{border-color:#7da533;background:#f2f7ea}
    .summary-visual-editor{min-height:245px;padding:16px;border:1px solid #d9dcd6;border-radius:0 0 11px 11px;background:#fff;color:#50574d;font-size:.76rem;line-height:1.75;outline:0}.summary-visual-editor:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.12)}.summary-visual-editor:empty:before{content:attr(data-placeholder);color:#a0a69d;pointer-events:none}.summary-visual-editor p{margin:0 0 11px}.summary-visual-editor strong,.summary-visual-editor b{color:#343b32;font-weight:900}.summary-visual-editor ul,.summary-visual-editor ol{margin:8px 0 12px;padding-left:24px}.summary-visual-editor ul{list-style:disc}.summary-visual-editor ol{list-style:decimal}.summary-visual-editor li{margin:4px 0}.summary-visual-editor table{width:100%;margin:12px 0;border-collapse:collapse;table-layout:fixed;font-size:.69rem}.summary-visual-editor th,.summary-visual-editor td{min-width:80px;padding:9px;border:1px solid #cfd2d6;vertical-align:top}.summary-visual-editor th{background:#e5e7eb;color:#343a40;font-weight:900;text-align:left}.summary-visual-editor td{background:#fff;color:#4b5563}.summary-visual-editor td.is-selected,.summary-visual-editor th.is-selected{outline:2px solid #117e8c;outline-offset:-2px;background:#eef8f9}.summary-table-tools{display:flex;align-items:center;flex-wrap:wrap;gap:5px;padding:7px 9px;border-right:1px solid #d9dcd6;border-left:1px solid #d9dcd6;background:#eef8f9}.summary-table-tools>span{display:inline-flex;align-items:center;gap:6px;margin-right:auto;color:#117e8c;font-size:.62rem;font-weight:900;text-transform:uppercase}.summary-table-tools button{min-height:28px;padding:5px 8px;border:1px solid #cbdedf;border-radius:7px;background:#fff;color:#386b70;font-size:.6rem;font-weight:850}.summary-table-tools button:hover{background:#e5f2f3}.summary-table-tools button.is-danger{border-color:#ead4d4;color:#a33d3d}.summary-writing-panel:has(.summary-table-tools:not(.hidden)) .summary-toolbar{border-radius:11px 11px 0 0}.summary-writing-panel:has(.summary-table-tools:not(.hidden)) .summary-visual-editor{border-radius:0 0 11px 11px}.summary-editor-sidebar{position:sticky;top:24px;padding-left:28px;border-left:1px solid #e1e3de}.summary-sidebar-label{display:block;margin-bottom:9px;color:#7c8379;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.summary-company{padding-bottom:25px;border-bottom:1px solid #e5e7eb}.summary-company h2{margin:0;color:#302834;font-size:1.05rem;font-weight:900}.summary-company>p{margin:5px 0 0;color:#737a70;font-size:.72rem}.summary-company dl{display:grid;gap:13px;margin-top:20px}.summary-company dt{color:#8a9186;font-size:.62rem;font-weight:800;text-transform:uppercase}.summary-company dt i{width:16px;color:#117e8c}.summary-company dd{margin:4px 0 0;overflow-wrap:anywhere;color:#3f443d;font-size:.72rem;font-weight:700}.summary-editor-help{padding:25px 0;border-bottom:1px solid #e5e7eb}.summary-editor-help ul{display:grid;gap:10px;margin:0;padding:0;list-style:none}.summary-editor-help li{display:flex;align-items:flex-start;gap:8px;color:#62695f;font-size:.68rem;line-height:1.45}.summary-editor-help li i{margin-top:2px;color:#7da533}.summary-editor-help>p{display:flex;align-items:flex-start;gap:8px;margin:17px 0 0;padding:11px;border-radius:10px;background:#f7faf2;color:#62695f;font-size:.65rem;line-height:1.5}.summary-editor-help>p i{margin-top:2px;color:#117e8c}.summary-editor-actions{display:grid;gap:9px;padding-top:25px}.summary-editor-actions button,.summary-editor-actions a{min-height:44px;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:9px 12px;border-radius:11px;font-size:.69rem;font-weight:900;transition:.18s}.summary-save{border:1px solid #7da533;background:#7da533;color:#fff}.summary-save:hover{background:#638522;transform:translateY(-1px)}.summary-view{border:1px solid #cddab8;background:#fff;color:#638522}.summary-view:hover{background:#f7faf2}.summary-cancel{border:1px solid #d9dcd6;background:#fff;color:#62685f}.summary-cancel:hover{background:#f4f5f2}.hidden{display:none!important}
    @media(max-width:900px){.summary-editor-layout{grid-template-columns:1fr;gap:30px}.summary-editor-sidebar{position:static;padding:28px 0 0;border-top:1px solid #e1e3de;border-left:0}}@media(max-width:700px){.summary-editor-hero,.summary-editor-hero-content{min-height:225px}.summary-editor-hero-content{align-items:stretch;flex-direction:column;justify-content:center;padding:28px 20px}.summary-editor-back{width:100%}.summary-editor-layout{padding:28px 20px 0}.summary-field,.summary-editor-tabs,.summary-writing-panel,.summary-preview-panel{margin-left:0;padding-left:0}.summary-toolbar span{display:none}.summary-field-meta span{display:none}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('summary-sections');
    const template = document.getElementById('summary-section-template');
    const total = document.getElementById('summary-section-total');

    function updateSection(section) {
        const title = section.querySelector('[data-title-input]');
        const editor = section.querySelector('[data-visual-editor]');
        const content = section.querySelector('[data-content-input]');
        section.querySelector('.summary-section-head strong').textContent = title.value.trim() || 'Sección sin título';
        content.value = editor.innerHTML;
        section.querySelector('[data-character-count]').textContent = `${editor.innerText.trim().length} caracteres`;
    }

    function renumber() {
        [...container.querySelectorAll('[data-section]')].forEach((section, index) => {
            section.querySelector('.summary-section-number').textContent = index + 1;
            section.querySelector('.summary-section-head small').textContent = `Sección ${index + 1}`;
            section.querySelector('[data-title-input]').name = `secciones[${index}][titulo]`;
            section.querySelector('[data-content-input]').name = `secciones[${index}][contenido_html]`;
        });
        total.textContent = container.querySelectorAll('[data-section]').length;
    }

    function insertTable(editor) {
        editor.focus();
        document.execCommand('insertHTML', false, '<table><thead><tr><th>Encabezado 1</th><th>Encabezado 2</th></tr></thead><tbody><tr><td>Dato 1</td><td>Dato 2</td></tr></tbody></table><p><br></p>');
        updateSection(editor.closest('[data-section]'));
    }

    function applyFormat(editor, type) {
        editor.focus();
        if (type === 'bold') document.execCommand('bold', false);
        if (type === 'bullet') document.execCommand('insertUnorderedList', false);
        if (type === 'ordered') document.execCommand('insertOrderedList', false);
        if (type === 'table') insertTable(editor);
        updateSection(editor.closest('[data-section]'));
    }

    function selectCell(section, cell) {
        section.querySelectorAll('td.is-selected,th.is-selected').forEach(item => item.classList.remove('is-selected'));
        cell?.classList.add('is-selected');
        section._selectedTableCell = cell || null;
        section.querySelector('[data-table-tools]').classList.toggle('hidden', !cell);
    }

    function editTable(section, action) {
        const cell = section._selectedTableCell;
        const table = cell?.closest('table');
        if (!table) return;
        const row = cell.closest('tr');
        const cellIndex = [...row.children].indexOf(cell);
        if (action === 'row-add') {
            const newRow = document.createElement('tr');
            [...row.children].forEach(() => { const item = document.createElement('td'); item.innerHTML = '<br>'; newRow.appendChild(item); });
            row.parentNode.insertBefore(newRow, row.nextSibling);
        }
        if (action === 'column-add') {
            table.querySelectorAll('tr').forEach((item, rowIndex) => { const newCell = document.createElement(rowIndex === 0 ? 'th' : 'td'); newCell.innerHTML = rowIndex === 0 ? 'Nuevo encabezado' : '<br>'; item.appendChild(newCell); });
        }
        if (action === 'row-remove') {
            if (table.querySelectorAll('tr').length <= 1) table.remove(); else row.remove();
        }
        if (action === 'column-remove') {
            if (row.children.length <= 1) table.remove(); else table.querySelectorAll('tr').forEach(item => item.children[cellIndex]?.remove());
        }
        if (action === 'table-remove') table.remove();
        selectCell(section, null);
        updateSection(section);
    }

    container.addEventListener('input', event => { const section = event.target.closest('[data-section]'); if (section) updateSection(section); });
    container.addEventListener('paste', event => {
        const editor = event.target.closest('[data-visual-editor]');
        if (!editor) return;
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
    });
    container.addEventListener('mousedown', event => {
        if (event.target.closest('[data-format],[data-table-action]')) event.preventDefault();
    });
    container.addEventListener('click', event => {
        const section = event.target.closest('[data-section]'); if (!section) return;
        const cell = event.target.closest('td,th');
        if (cell?.closest('[data-visual-editor]')) selectCell(section, cell);
        const format = event.target.closest('[data-format]');
        if (format) applyFormat(section.querySelector('[data-visual-editor]'), format.dataset.format);
        const tableAction = event.target.closest('[data-table-action]');
        if (tableAction) editTable(section, tableAction.dataset.tableAction);
        if (event.target.closest('.summary-remove-section')) {
            if (container.querySelectorAll('[data-section]').length === 1) { alert('El resumen debe conservar al menos una sección.'); return; }
            if (confirm('¿Deseas eliminar esta sección del resumen?')) { section.remove(); renumber(); }
        }
    });

    document.getElementById('summary-add-section').addEventListener('click', function () {
        const section = template.content.firstElementChild.cloneNode(true); container.appendChild(section); renumber(); updateSection(section); section.querySelector('[data-title-input]').focus();
    });

    container.querySelectorAll('[data-section]').forEach(updateSection); renumber();
    document.getElementById('summary-editor-form').addEventListener('submit', function () {
        container.querySelectorAll('[data-section]').forEach(updateSection);
    });
});
</script>
@endsection
