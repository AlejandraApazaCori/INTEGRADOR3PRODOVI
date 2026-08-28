@extends('layouts.app')

@section('title', 'Cuestionario de Información de Empresa (Vista Admin)')

@section('content')
<div class="company-questionnaire-page">
    <header class="company-questionnaire-hero rp-banner">
        <div class="rp-banner-overlay"></div>
        <div class="company-questionnaire-hero-content">
            <div class="company-questionnaire-identity">
                @if($empresa->logo)
                    <div class="company-questionnaire-logo is-image"><img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}"></div>
                @else
                    <div class="company-questionnaire-logo" aria-hidden="true"><i class="fas fa-clipboard-list"></i></div>
                @endif
                <div>
                    <span class="company-questionnaire-eyebrow">Información empresarial</span>
                    <h1>Cuestionario de la Empresa</h1>
                    <p>{{ $empresa->nombre_empresa }} <span aria-hidden="true">•</span> {{ $empresa->tipo_empresa }}</p>
                </div>
            </div>
            <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="company-questionnaire-back"><i class="fas fa-arrow-left"></i>Volver a la empresa</a>
        </div>
    </header>

    <div class="company-questionnaire-alerts">
        @if(session('success'))
            <div class="company-questionnaire-alert is-success" role="alert"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>
        @endif
        @if($errors->any())
            <div class="company-questionnaire-alert is-error" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <div><strong>No se pudo guardar el cuestionario.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            </div>
        @endif
        @if(session('drive_error'))
            <div class="company-questionnaire-alert is-error" role="alert"><i class="fab fa-google-drive"></i><span>{{ session('drive_error') }}</span></div>
        @endif
    </div>

    <div class="company-questionnaire-content">
        <form action="{{ route('administrador.empresas.cuestionario.update', $empresa->id) }}" method="POST" class="company-questionnaire-layout">
            @csrf
            @method('PUT')
            @if(request('continuar_campania'))
                <input type="hidden" name="continuar_campania" value="{{ request('continuar_campania') }}">
            @endif

            <main class="company-questionnaire-main">
                @forelse($temas as $tema)
                    <section class="questionnaire-topic">
                        <header class="questionnaire-topic-heading">
                            <span>{{ $loop->iteration }}</span>
                            <div>
                                <h2>{{ $tema->nombre_tema }}</h2>
                                @if($tema->descripcion_tema)<p>{{ $tema->descripcion_tema }}</p>@endif
                            </div>
                        </header>
                        <div class="questionnaire-questions">
                            @foreach($tema->preguntas as $pregunta)
                                <div class="questionnaire-question">
                                    @php
                                        $respuestaActual = $respuestasExistentes[$pregunta->id] ?? '';
                                        $respuestasMarcadas = collect(preg_split('/\s*\|\s*/', $respuestaActual, -1, PREG_SPLIT_NO_EMPTY));
                                        $respuestaOtro = $respuestasMarcadas->first(fn ($valor) => str_starts_with($valor, 'Otro:'));
                                    @endphp
                                    <label for="respuesta_{{ $pregunta->id }}">{{ $pregunta->pregunta }} @if($pregunta->requerido)<span title="Campo obligatorio">*</span>@endif</label>
                                    @if($pregunta->ayuda)<p class="questionnaire-help">{{ $pregunta->ayuda }}</p>@endif
                                    @if($pregunta->tipo_respuesta === 'texto_largo')
                                        <textarea id="respuesta_{{ $pregunta->id }}" name="respuesta_{{ $pregunta->id }}" rows="4" {{ $pregunta->requerido ? 'required' : '' }} placeholder="Escribe la respuesta aquí...">{{ $respuestaActual }}</textarea>
                                    @elseif($pregunta->tipo_respuesta === 'opcion_multiple')
                                        <div class="questionnaire-choices">
                                            @foreach($pregunta->opciones ?? [] as $opcion)
                                                @php $seleccionada = $respuestasMarcadas->contains($opcion) || ($opcion === 'Otro' && $respuestaOtro); @endphp
                                                <label class="questionnaire-choice {{ $seleccionada ? 'is-selected' : '' }}">
                                                    <input type="radio" name="respuesta_{{ $pregunta->id }}" value="{{ $opcion }}" {{ $seleccionada ? 'checked' : '' }} {{ $pregunta->requerido ? 'required' : '' }}>
                                                    <span class="choice-mark"></span><span>{{ $opcion }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                            <input class="questionnaire-other" type="text" name="respuesta_{{ $pregunta->id }}_otro" value="{{ $respuestaOtro ? str_replace('Otro: ', '', $respuestaOtro) : '' }}" placeholder="Especifica otra opción...">
                                        @endif
                                    @elseif($pregunta->tipo_respuesta === 'checkbox')
                                        <div class="questionnaire-choices" data-required-checkboxes="{{ $pregunta->requerido ? '1' : '0' }}">
                                            @foreach($pregunta->opciones ?? [] as $opcion)
                                                @php $seleccionada = $respuestasMarcadas->contains($opcion) || ($opcion === 'Otro' && $respuestaOtro); @endphp
                                                <label class="questionnaire-choice {{ $seleccionada ? 'is-selected' : '' }}">
                                                    <input type="checkbox" name="respuesta_{{ $pregunta->id }}[]" value="{{ $opcion }}" {{ $seleccionada ? 'checked' : '' }}>
                                                    <span class="choice-mark"><i class="fas fa-check"></i></span><span>{{ $opcion }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                            <input class="questionnaire-other" type="text" name="respuesta_{{ $pregunta->id }}_otro" value="{{ $respuestaOtro ? str_replace('Otro: ', '', $respuestaOtro) : '' }}" placeholder="Escribe otra opción...">
                                        @endif
                                    @else
                                        <input id="respuesta_{{ $pregunta->id }}" type="text" name="respuesta_{{ $pregunta->id }}" value="{{ $respuestaActual }}" {{ $pregunta->requerido ? 'required' : '' }} placeholder="Escribe la respuesta aquí...">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="questionnaire-empty"><i class="fas fa-clipboard-question"></i><strong>No hay preguntas configuradas</strong><p>El cuestionario todavía no tiene temas o preguntas disponibles.</p></div>
                @endforelse
            </main>

            <aside class="company-questionnaire-aside">
                <div class="questionnaire-sidebar">
                    <section class="questionnaire-company-summary">
                        <span class="questionnaire-sidebar-label">Empresa</span>
                        <h2>{{ $empresa->nombre_empresa }}</h2>
                        <p>{{ $empresa->tipo_empresa }}</p>
                        <dl>
                            <div><dt><i class="fas fa-user"></i> Propietario</dt><dd>{{ $empresa->usuario->name }}</dd></div>
                            <div><dt><i class="fas fa-envelope"></i> Correo</dt><dd>{{ $empresa->usuario->email }}</dd></div>
                        </dl>
                        @if($empresa->descripcion)<p class="questionnaire-company-description">{{ $empresa->descripcion }}</p>@endif
                    </section>
                    <section class="questionnaire-actions">
                        <span class="questionnaire-sidebar-label">Acciones</span>
                        <button type="submit" class="questionnaire-save"><i class="fas fa-floppy-disk"></i>Guardar cambios</button>
                        <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="questionnaire-cancel"><i class="fas fa-xmark"></i>Cancelar</a>
                        <div class="questionnaire-document-actions">
                            <a href="{{ route('administrador.empresas.cuestionario.pdf', $empresa->id) }}" class="questionnaire-pdf"><i class="fas fa-file-pdf"></i>Descargar PDF</a>
                            <button type="button" id="questionnaire-drive-open" class="questionnaire-doc"><i class="fab fa-google-drive"></i>Ver en Google Docs</button>
                        </div>
                    </section>
                </div>
            </aside>
        </form>
    </div>

    <div id="questionnaire-drive-modal" class="questionnaire-drive-modal hidden" role="dialog" aria-modal="true" aria-labelledby="questionnaire-drive-title">
        <div class="questionnaire-drive-dialog">
            <div class="questionnaire-drive-head">
                <div><h3 id="questionnaire-drive-title">Guardar cuestionario en Drive</h3><p>El documento se guardará dentro del respaldo organizado de esta empresa.</p></div>
                <button type="button" id="questionnaire-drive-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <form id="questionnaire-drive-form" action="{{ route('administrador.empresas.cuestionario.google-doc', $empresa->id) }}" method="POST">
                @csrf
                <div class="questionnaire-drive-location">
                    <span><i class="fas fa-folder-tree"></i></span>
                    <div><small>Ubicación administrada</small><strong>PRODOVI / Empresas / {{ $empresa->nombre_empresa }}</strong><p id="questionnaire-drive-current">Consultando documento...</p></div>
                </div>
                <div class="questionnaire-drive-editor">
                    <label for="questionnaire-drive-folder">Guardar en</label>
                    <select id="questionnaire-drive-folder" name="folder_id" disabled><option value="">Consultando carpetas...</option></select>
                    <div class="questionnaire-drive-divider"><span></span><b>o crea una</b><span></span></div>
                    <label for="questionnaire-drive-new-folder">Nueva subcarpeta dentro de {{ $empresa->nombre_empresa }}</label>
                    <div class="questionnaire-drive-new"><i class="fas fa-folder-plus"></i><input id="questionnaire-drive-new-folder" name="new_folder" type="text" maxlength="80" placeholder="Ej.: Cuestionarios 2026"></div>
                </div>
                <p id="questionnaire-drive-status">Al guardar se creará un Google Docs con encabezado repetido.</p>
                <div class="questionnaire-drive-buttons"><button type="button" id="questionnaire-drive-cancel">Cancelar</button><button type="submit" id="questionnaire-drive-save" disabled><i class="fab fa-google-drive"></i>Guardar y abrir</button></div>
            </form>
        </div>
    </div>
</div>

<style>
    .company-questionnaire-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.rp-banner{position:relative;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}
    .company-questionnaire-hero{position:relative;overflow:hidden;width:100%;min-height:180px}.company-questionnaire-hero .rp-banner-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-repeat:no-repeat}.company-questionnaire-hero-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px}.company-questionnaire-identity{min-width:0;display:flex;align-items:center;gap:18px}.company-questionnaire-logo{width:58px;height:58px;display:grid;place-items:center;flex:0 0 auto;overflow:hidden;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.4rem;backdrop-filter:blur(5px)}.company-questionnaire-logo.is-image{padding:7px;background:#fff}.company-questionnaire-logo img{width:100%;height:100%;object-fit:contain}.company-questionnaire-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.company-questionnaire-hero h1{margin:0 0 5px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;line-height:1.1;letter-spacing:-.04em}.company-questionnaire-hero p{margin:0;color:#dbeafe;font-size:.74rem;font-weight:600}.company-questionnaire-back{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:0 0 auto;padding:10px 13px;border:1px solid #fff;border-radius:.65rem;background:#fff;color:#4f46e5;font-size:.69rem;font-weight:900;transition:.18s}.company-questionnaire-back:hover{color:#4338ca;transform:translateY(-2px);box-shadow:0 8px 18px rgba(15,23,42,.18)}
    .company-questionnaire-hero.rp-banner{background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.company-questionnaire-hero .rp-banner-overlay{background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%)}.company-questionnaire-eyebrow{color:#ecfccb}.company-questionnaire-hero p{color:#f0fdf4}.company-questionnaire-back{color:#638522}.company-questionnaire-back:hover{color:#4f6c1b;box-shadow:0 8px 18px rgba(31,55,20,.18)}
    .company-questionnaire-alerts{width:calc(100% - 48px);max-width:1500px;margin:24px auto 0}.company-questionnaire-alert{display:flex;align-items:flex-start;gap:10px;padding:13px 16px;border:1px solid;border-radius:12px;font-size:.75rem;font-weight:700}.company-questionnaire-alert+.company-questionnaire-alert{margin-top:10px}.company-questionnaire-alert.is-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.company-questionnaire-alert.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}.company-questionnaire-alert ul{margin:7px 0 0;padding-left:18px;list-style:disc}
    .company-questionnaire-content{max-width:1500px;margin:0 auto;padding:34px 48px 0}.company-questionnaire-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,340px);align-items:start;gap:48px}.company-questionnaire-main,.company-questionnaire-aside{min-width:0}.company-questionnaire-main{grid-column:1}.company-questionnaire-aside{grid-column:2}.questionnaire-topic{padding:0 0 38px;border-bottom:1px solid #e5e7eb}.questionnaire-topic+.questionnaire-topic{padding-top:38px}.questionnaire-topic:last-child{border-bottom:0}.questionnaire-topic-heading{display:flex;align-items:flex-start;gap:13px;margin-bottom:26px}.questionnaire-topic-heading>span{width:34px;height:34px;display:grid;place-items:center;flex:0 0 auto;border-radius:10px;background:#117e8c;color:#fff;font-size:.72rem;font-weight:900}.questionnaire-topic-heading h2{margin:1px 0 0;color:#302834;font-size:1.15rem;font-weight:900}.questionnaire-topic-heading h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:#7da533}.questionnaire-topic-heading p{margin:9px 0 0;color:#7c8379;font-size:.7rem;line-height:1.55}.questionnaire-questions{display:grid;gap:28px;padding-left:47px}.questionnaire-question{padding:0 0 28px;border-bottom:1px solid #eef0ec}.questionnaire-question:last-child{padding-bottom:0;border-bottom:0}.questionnaire-question label{display:block;margin-bottom:8px;color:#3f443d;font-size:.78rem;font-weight:900;line-height:1.5}.questionnaire-question label span{color:#c93e32}.questionnaire-help{margin:-2px 0 10px;color:#8a9186;font-size:.66rem;line-height:1.5}.questionnaire-question input,.questionnaire-question textarea{width:100%;padding:12px 14px;border:1px solid #d9dcd6;border-radius:12px;background:#fff;color:#3f443d;box-shadow:0 2px 5px rgba(55,60,52,.06);font-size:.8rem;outline:0;transition:.18s}.questionnaire-question textarea{min-height:116px;resize:vertical}.questionnaire-question input:focus,.questionnaire-question textarea:focus{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}
    .questionnaire-choices{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.questionnaire-choice{position:relative;display:flex!important;align-items:center;gap:9px;min-height:45px;margin:0!important;padding:9px 12px;border:1px solid #d9dcd6;border-radius:11px;background:#fff;cursor:pointer;transition:.18s}.questionnaire-choice:hover,.questionnaire-choice.is-selected{border-color:#117e8c;background:#f2fafa}.questionnaire-choice input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}.questionnaire-choice .choice-mark{width:19px;height:19px;display:grid;place-items:center;flex:0 0 auto;padding:0;border:1px solid #bfc5bc;border-radius:5px;background:#fff;color:transparent;font-size:.58rem}.questionnaire-choice input[type=radio]+.choice-mark{border-radius:50%}.questionnaire-choice input:checked+.choice-mark{border-color:#117e8c;background:#117e8c;color:#fff;box-shadow:inset 0 0 0 4px #117e8c}.questionnaire-choice input[type=radio]:checked+.choice-mark{background:#fff;box-shadow:inset 0 0 0 5px #117e8c}.questionnaire-choice>span:last-child{color:#4b5148!important;font-size:.72rem;font-weight:750}.questionnaire-other{margin-top:10px}
    .questionnaire-sidebar{position:sticky;top:24px;padding-left:28px;border-left:1px solid #e1e3de}.questionnaire-sidebar-label{display:block;margin-bottom:9px;color:#7c8379;font-size:.63rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.questionnaire-company-summary{padding-bottom:26px;border-bottom:1px solid #e5e7eb}.questionnaire-company-summary h2{margin:0;color:#302834;font-size:1.05rem;font-weight:900}.questionnaire-company-summary>p{margin:5px 0 0;color:#737a70;font-size:.72rem}.questionnaire-company-summary dl{display:grid;gap:13px;margin-top:20px}.questionnaire-company-summary dt{color:#8a9186;font-size:.62rem;font-weight:800;text-transform:uppercase}.questionnaire-company-summary dt i{width:16px;color:#117e8c}.questionnaire-company-summary dd{margin:4px 0 0;overflow-wrap:anywhere;color:#3f443d;font-size:.72rem;font-weight:700}.questionnaire-company-summary .questionnaire-company-description{margin-top:20px;padding-top:16px;border-top:1px solid #eef0ec;color:#737a70;font-size:.68rem;line-height:1.6}.questionnaire-actions{display:grid;gap:10px;padding-top:26px}.questionnaire-actions .questionnaire-sidebar-label{margin-bottom:3px}.questionnaire-save,.questionnaire-cancel{min-height:46px;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px 14px;border-radius:12px;font-size:.72rem;font-weight:900;transition:.18s}.questionnaire-save{border:1px solid #117e8c;background:#117e8c;color:#fff}.questionnaire-save:hover{background:#0d6975;transform:translateY(-1px)}.questionnaire-cancel{border:1px solid #d9dcd6;background:#fff;color:#62685f}.questionnaire-cancel:hover{border-color:#bfc5bc;background:#f4f5f2;color:#3f443d}.questionnaire-empty{padding:50px 20px;border:1px dashed #d9dcd6;border-radius:14px;color:#7c8379;text-align:center}.questionnaire-empty i,.questionnaire-empty strong,.questionnaire-empty p{display:block}.questionnaire-empty i{margin-bottom:12px;color:#aab0a7;font-size:1.6rem}.questionnaire-empty strong{color:#3f443d;font-size:.85rem}.questionnaire-empty p{margin-top:5px;font-size:.7rem}
    .questionnaire-document-actions{display:grid;grid-template-columns:1fr;gap:8px;margin-top:10px;padding-top:18px;border-top:1px solid #e5e7eb}.questionnaire-pdf,.questionnaire-doc{min-height:43px;display:flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:9px 12px;border-radius:11px;font-size:.69rem;font-weight:900}.questionnaire-pdf{border:1px solid #f3c4c4;background:#fff;color:#b42323}.questionnaire-doc{border:1px solid #cfd8f6;background:#fff;color:#4f46e5}.questionnaire-pdf:hover{background:#fff0f0;color:#991b1b}.questionnaire-doc:hover{background:#eef2ff;color:#4338ca}
    .questionnaire-drive-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:16px;background:rgba(17,24,39,.58)}.questionnaire-drive-modal.flex{display:flex}.questionnaire-drive-dialog{width:100%;max-width:520px;padding:24px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.25)}.questionnaire-drive-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.questionnaire-drive-head h3{margin:0;color:#1f2937;font-size:1.18rem;font-weight:900}.questionnaire-drive-head p{margin:5px 0 0;color:#6b7280;font-size:.74rem}.questionnaire-drive-head>button{width:36px;height:36px;display:grid;place-items:center;flex:none;border-radius:50%;color:#6b7280}.questionnaire-drive-head>button:hover{background:#f3f4f6}.questionnaire-drive-location{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dce7cc;border-radius:13px;background:#f7faf2}.questionnaire-drive-location>span{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:11px;background:#e4efd4;color:#638524}.questionnaire-drive-location div{min-width:0}.questionnaire-drive-location small,.questionnaire-drive-location strong,.questionnaire-drive-location p{display:block}.questionnaire-drive-location small{color:#7b8376;font-size:.6rem;font-weight:900;text-transform:uppercase}.questionnaire-drive-location strong{margin-top:3px;overflow:hidden;color:#30382b;font-size:.73rem;text-overflow:ellipsis;white-space:nowrap}.questionnaire-drive-location p{margin:4px 0 0;color:#7a8275;font-size:.64rem}.questionnaire-drive-editor{margin-top:15px;padding:15px;border:1px solid #dce7cc;border-radius:13px;background:#fbfcf9}.questionnaire-drive-editor label{display:block;margin-bottom:7px;color:#374151;font-size:.7rem;font-weight:900}.questionnaire-drive-editor select,.questionnaire-drive-editor input{width:100%;height:46px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;font-size:.75rem;outline:0}.questionnaire-drive-editor select{padding:0 12px}.questionnaire-drive-editor select:focus,.questionnaire-drive-editor input:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.questionnaire-drive-divider{display:flex;align-items:center;gap:10px;margin:14px 0}.questionnaire-drive-divider span{height:1px;flex:1;background:#e5e7eb}.questionnaire-drive-divider b{color:#9ca3af;font-size:.58rem;text-transform:uppercase}.questionnaire-drive-new{position:relative}.questionnaire-drive-new i{position:absolute;top:15px;left:14px;color:#7da533}.questionnaire-drive-new input{padding:0 12px 0 40px}.questionnaire-drive-status{margin:12px 0 0;color:#6b7280;font-size:.7rem}.questionnaire-drive-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:20px}.questionnaire-drive-buttons button{min-height:44px;border-radius:11px;font-size:.72rem;font-weight:900}.questionnaire-drive-buttons button:first-child{background:#f3f4f6;color:#5f6670}.questionnaire-drive-buttons button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#7da533;color:#fff}.questionnaire-drive-buttons button:disabled{cursor:not-allowed;opacity:.55}
    @media(max-width:760px){.company-questionnaire-layout{grid-template-columns:1fr;gap:30px}.company-questionnaire-main,.company-questionnaire-aside{grid-column:1}.questionnaire-sidebar{position:static;padding:28px 0 0;border-top:1px solid #e1e3de;border-left:0}}
    @media(max-width:700px){.company-questionnaire-hero,.company-questionnaire-hero-content{min-height:225px}.company-questionnaire-hero-content{align-items:stretch;flex-direction:column;justify-content:center;padding:28px 20px}.company-questionnaire-identity{align-items:flex-start}.company-questionnaire-back{width:100%}.company-questionnaire-alerts{width:calc(100% - 24px)}.company-questionnaire-content{padding:28px 20px 0}.questionnaire-questions{padding-left:0}.questionnaire-choices{grid-template-columns:1fr}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.questionnaire-choice input').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.type === 'radio') document.querySelectorAll(`input[name="${input.name}"]`).forEach(item => item.closest('.questionnaire-choice')?.classList.toggle('is-selected', item.checked));
            else input.closest('.questionnaire-choice')?.classList.toggle('is-selected', input.checked);
        });
    });
    document.querySelectorAll('[data-required-checkboxes="1"]').forEach(function (group) {
        const inputs = [...group.querySelectorAll('input[type="checkbox"]')];
        const syncRequired = () => {
            inputs.forEach(input => input.required = false);
            if (!inputs.some(item => item.checked) && inputs[0]) inputs[0].required = true;
        };
        inputs.forEach(input => input.addEventListener('change', syncRequired));
        syncRequired();
    });

    const driveModal = document.getElementById('questionnaire-drive-modal');
    const driveOpen = document.getElementById('questionnaire-drive-open');
    const driveFolder = document.getElementById('questionnaire-drive-folder');
    const driveNewFolder = document.getElementById('questionnaire-drive-new-folder');
    const driveCurrent = document.getElementById('questionnaire-drive-current');
    const driveStatus = document.getElementById('questionnaire-drive-status');
    const driveSave = document.getElementById('questionnaire-drive-save');
    const foldersUrl = @json(route('administrador.empresas.cuestionario.drive-folders', $empresa->id));
    const closeDriveModal = () => {
        driveModal.classList.add('hidden'); driveModal.classList.remove('flex'); document.body.classList.remove('overflow-hidden');
    };
    driveOpen?.addEventListener('click', async function () {
        driveModal.classList.remove('hidden'); driveModal.classList.add('flex'); document.body.classList.add('overflow-hidden');
        driveFolder.innerHTML = '<option value="">Consultando carpetas...</option>'; driveFolder.disabled = true; driveNewFolder.value = ''; driveSave.disabled = true;
        driveCurrent.textContent = 'Consultando documento...'; driveStatus.textContent = 'Preparando PRODOVI / Empresas / {{ $empresa->nombre_empresa }}...'; driveStatus.style.color = '';
        try {
            const response = await fetch(foldersUrl, {headers:{'Accept':'application/json'}}); const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No se pudieron consultar las carpetas.');
            driveFolder.innerHTML = '';
            [{id:data.root.id,name:`${data.root.name} (carpeta principal)`}, ...data.folders].forEach(folder => {
                const option = document.createElement('option'); option.value = folder.id; option.textContent = folder.name; driveFolder.appendChild(option);
            });
            if (data.current_folder) driveFolder.value = data.current_folder.id;
            driveCurrent.textContent = data.current_document ? `Documento existente: ${data.current_document.name}` : 'El documento aún no fue creado';
            driveStatus.textContent = data.current_document ? 'Al guardar se actualizará el respaldo registrado.' : 'Puedes guardar en la carpeta de la empresa o crear una subcarpeta.';
            driveFolder.disabled = false; driveSave.disabled = false;
        } catch (error) {
            driveCurrent.textContent = 'No se pudo consultar Drive'; driveStatus.textContent = error.message; driveStatus.style.color = '#b91c1c';
        }
    });
    document.getElementById('questionnaire-drive-close')?.addEventListener('click', closeDriveModal);
    document.getElementById('questionnaire-drive-cancel')?.addEventListener('click', closeDriveModal);
    driveModal?.addEventListener('click', event => { if (event.target === driveModal) closeDriveModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && driveModal?.classList.contains('flex')) closeDriveModal(); });
});
</script>
@endsection
