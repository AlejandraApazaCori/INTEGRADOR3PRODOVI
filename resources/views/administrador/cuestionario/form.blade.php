@extends('layouts.app')

@section('title', isset($tema) ? 'Editar Tema' : 'Crear Nuevo Tema')

@section('content')
<style>
    /* Custom Animations */
    @keyframes slideDownAndFade {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-enter {
        animation: slideDownAndFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* Custom Scrollbar for Textareas */
    textarea::-webkit-scrollbar {
        width: 8px;
    }
    textarea::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    textarea::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    textarea::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    #questionnaire-editor {
        --prodovi-purple: #5B2B76;
        --prodovi-purple-dark: #3d174f;
        --prodovi-orange: #EF6C22;
        --prodovi-gold: #F5A900;
        --prodovi-green: #7DA533;
        --prodovi-turquoise: #117E8C;
        --prodovi-ink: #17131d;
        width: 100%;
        background: #fff;
        color: var(--prodovi-ink);
    }
    #questionnaire-editor .prodovi-hero {
        position: relative;
        min-height: 170px;
        padding: 32px 36px;
        overflow: hidden;
        border: 0;
        border-radius: 0;
        background: #242426;
        color: #fff;
        box-shadow: none;
    }
    #questionnaire-editor .prodovi-hero::before,
    #questionnaire-editor .prodovi-hero::after {
        display: none;
    }
    #questionnaire-editor .hero-content { position: relative; z-index: 2; max-width: 720px; }
    #questionnaire-editor .hero-kicker { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 13px; color: #a5a5aa; font-size: .68rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
    #questionnaire-editor .hero-kicker::before { content: ''; width: 22px; height: 3px; border-radius: 99px; background: var(--prodovi-turquoise); }
    #questionnaire-editor .back-button { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 13px; padding: 9px 14px; border: 1px solid rgba(255,255,255,.22); border-radius: 10px; background: rgba(255,255,255,.1); color: #fff; font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; transition: .2s ease; }
    #questionnaire-editor .back-button:hover { border-color: var(--prodovi-orange); background: var(--prodovi-orange); transform: translateX(-2px); }
    #questionnaire-editor .hero-title { margin: 0; color: #fff; font-size: clamp(1.65rem, 3vw, 2.35rem); font-weight: 800; line-height: 1.08; letter-spacing: -.035em; }
    #questionnaire-editor .hero-title span { color: var(--prodovi-orange); }
    #questionnaire-editor .hero-description { max-width: 620px; margin-top: 11px; color: #96969c; font-size: .86rem; line-height: 1.55; }
    #questionnaire-editor .login-mosaic { position: relative; z-index: 2; width: 144px; height: 96px; flex: 0 0 auto; display: grid; grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(2,1fr); }
    #questionnaire-editor .login-mosaic span { display: block; }
    #questionnaire-editor .login-mosaic span:nth-child(1) { background: var(--prodovi-orange); border-radius: 100% 0 0 0; }
    #questionnaire-editor .login-mosaic span:nth-child(2) { background: var(--prodovi-gold); border-radius: 0 0 0 100%; }
    #questionnaire-editor .login-mosaic span:nth-child(3) { background: var(--prodovi-purple); border-radius: 100% 0 100% 0; }
    #questionnaire-editor .login-mosaic span:nth-child(4) { background: var(--prodovi-turquoise); border-radius: 0 100% 0 100%; }
    #questionnaire-editor .login-mosaic span:nth-child(5) { background: var(--prodovi-green); border-radius: 50%; }
    #questionnaire-editor .login-mosaic span:nth-child(6) { border: 12px solid #607078; border-top-color: transparent; border-left-color: transparent; border-radius: 50%; transform: rotate(45deg); }
    #questionnaire-editor .brand-bars { position: absolute; z-index: 2; left: 38px; bottom: 0; display: flex; height: 7px; }
    #questionnaire-editor .brand-bars i { display: block; width: 42px; }
    #questionnaire-editor .brand-bars i:nth-child(1) { background: var(--prodovi-purple); }
    #questionnaire-editor .brand-bars i:nth-child(2) { background: var(--prodovi-orange); }
    #questionnaire-editor .brand-bars i:nth-child(3) { background: var(--prodovi-gold); }
    #questionnaire-editor .brand-bars i:nth-child(4) { background: var(--prodovi-green); }
    #questionnaire-editor .brand-bars i:nth-child(5) { background: var(--prodovi-turquoise); }
    #questionnaire-editor .hero-icon { position: relative; z-index: 2; width: 66px; height: 66px; display: grid; place-items: center; flex: 0 0 auto; border: 1px solid rgba(255,255,255,.22); border-radius: 20px; background: rgba(255,255,255,.12); color: #fff; backdrop-filter: blur(8px); transform: rotate(4deg); }
    #questionnaire-editor .editor-shell { position: relative; z-index: 3; margin-top: 0; border: 0; border-radius: 0; background: #fff; box-shadow: none; }
    #questionnaire-editor .topic-panel { padding: 23px; border: 1px solid #eadfec; border-radius: 20px; background: linear-gradient(135deg, #fbf8fc, #fff 58%, #f2fbfa); }
    #questionnaire-editor .section-label { display: flex; align-items: center; gap: 11px; margin-bottom: 16px; color: var(--prodovi-purple); font-size: .76rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
    #questionnaire-editor .section-label-icon { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 11px; background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #questionnaire-editor input:not([type="checkbox"]),
    #questionnaire-editor textarea,
    #questionnaire-editor select { min-height: 48px; border: 1px solid #ded4e1; border-radius: 13px; background: linear-gradient(180deg,#fff,#fdfbfe); box-shadow: 0 5px 14px rgba(55,34,65,.04); }
    #questionnaire-editor input:not([type="checkbox"]):focus,
    #questionnaire-editor textarea:focus,
    #questionnaire-editor select:focus { border-color: var(--prodovi-purple); box-shadow: 0 0 0 4px rgba(91,43,118,.10); }
    #questionnaire-editor .prodovi-input-wrap { position: relative; }
    #questionnaire-editor .prodovi-input-wrap > i { position: absolute; z-index: 2; left: 15px; top: 50%; transform: translateY(-50%); color: var(--prodovi-purple); font-size: .82rem; pointer-events: none; }
    #questionnaire-editor .prodovi-input-wrap.is-textarea > i { top: 16px; transform: none; }
    #questionnaire-editor .prodovi-input-wrap input,
    #questionnaire-editor .prodovi-input-wrap textarea { padding-left: 42px; }
    #questionnaire-editor label { color: #514557; font-weight: 700; }
    #questionnaire-editor input:not([type="checkbox"]):hover,
    #questionnaire-editor textarea:hover { border-color: #c4b3ca; }
    #questionnaire-editor .answer-type-dropdown { position: relative; }
    #questionnaire-editor .type-dropdown-trigger { width: 100%; min-height: 48px; display: flex; align-items: center; gap: 11px; padding: 8px 12px; border: 1px solid #ded4e1; border-radius: 13px; background: linear-gradient(180deg,#fff,#fdfbfe); color: #34293a; text-align: left; box-shadow: 0 5px 14px rgba(55,34,65,.04); cursor: pointer; transition: .2s ease; }
    #questionnaire-editor .type-dropdown-trigger:hover { border-color: #bdaac5; transform: translateY(-1px); }
    #questionnaire-editor .type-dropdown-trigger.is-open { border-color: var(--prodovi-purple); box-shadow: 0 0 0 4px rgba(91,43,118,.10); }
    #questionnaire-editor .type-current-icon { width: 31px; height: 31px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 9px; background: rgba(91,43,118,.10); color: var(--prodovi-purple); }
    #questionnaire-editor .type-current-label { flex: 1; font-size: .86rem; font-weight: 800; }
    #questionnaire-editor .type-chevron { color: #938799; font-size: .7rem; transition: transform .2s; }
    #questionnaire-editor .type-dropdown-trigger.is-open .type-chevron { transform: rotate(180deg); }
    #questionnaire-editor .type-dropdown-menu { position: absolute; z-index: 40; top: calc(100% + 8px); left: 0; right: 0; display: none; padding: 7px; border: 1px solid #dfd5e2; border-radius: 15px; background: #fff; box-shadow: 0 20px 42px rgba(55,34,65,.18); }
    #questionnaire-editor .type-dropdown-menu.is-open { display: grid; gap: 3px; animation: slideDownAndFade .2s ease both; }
    #questionnaire-editor .type-dropdown-option { width: 100%; display: flex; align-items: center; gap: 11px; padding: 10px 11px; border: 0; border-radius: 10px; background: transparent; color: #514557; text-align: left; cursor: pointer; transition: .18s ease; }
    #questionnaire-editor .type-dropdown-option:hover,
    #questionnaire-editor .type-dropdown-option.is-selected { background: #f4edf7; color: var(--prodovi-purple); }
    #questionnaire-editor .type-dropdown-option i { width: 25px; text-align: center; color: var(--prodovi-orange); }
    #questionnaire-editor .type-dropdown-option span { font-size: .83rem; font-weight: 700; }
    #questionnaire-editor .options-builder { padding: 17px; border: 1px solid #e2d8e5; border-radius: 16px; background: linear-gradient(135deg,#faf7fb,#fff); }
    #questionnaire-editor .options-builder-head { display: flex; align-items: flex-start; gap: 11px; margin-bottom: 14px; }
    #questionnaire-editor .options-builder-icon { width: 36px; height: 36px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 11px; background: rgba(17,126,140,.11); color: var(--prodovi-turquoise); }
    #questionnaire-editor .options-builder-title { display: block; color: #3d3341; font-size: .86rem; font-weight: 900; }
    #questionnaire-editor .options-builder-description { display: block; margin-top: 2px; color: #837789; font-size: .72rem; }
    #questionnaire-editor .option-builder-list { display: grid; gap: 8px; }
    #questionnaire-editor .option-builder-row { display: grid; grid-template-columns: 29px minmax(0,1fr) 34px; align-items: center; gap: 8px; padding: 7px 8px; border: 1px solid #e7dfe9; border-radius: 12px; background: #fff; transition: .18s ease; }
    #questionnaire-editor .option-builder-row:focus-within { border-color: var(--prodovi-purple); box-shadow: 0 0 0 3px rgba(91,43,118,.08); }
    #questionnaire-editor .option-kind-icon { width: 26px; height: 26px; display: grid; place-items: center; color: var(--prodovi-purple); font-size: .8rem; }
    #questionnaire-editor .option-builder-input { min-height: 35px !important; padding: 5px 8px !important; border: 0 !important; border-radius: 7px !important; background: transparent !important; box-shadow: none !important; }
    #questionnaire-editor .remove-option-button { width: 30px; height: 30px; display: grid; place-items: center; border: 0; border-radius: 8px; background: #fff3f1; color: #d85a49; cursor: pointer; transition: .18s ease; }
    #questionnaire-editor .remove-option-button:hover { background: #d85a49; color: #fff; }
    #questionnaire-editor .option-builder-add { display: grid; grid-template-columns:minmax(0,1fr) auto auto; gap: 9px; margin-top: 10px; }
    #questionnaire-editor .new-option-input { min-height: 42px !important; padding: 8px 12px !important; }
    #questionnaire-editor .add-option-button { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border: 0; border-radius: 11px; background: var(--prodovi-turquoise); color: #fff; font-size: .78rem; font-weight: 800; cursor: pointer; transition: .2s ease; }
    #questionnaire-editor .add-option-button:hover { background: #0d6974; transform: translateY(-1px); }
    #questionnaire-editor .add-other-button { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border: 1px solid rgba(239,108,34,.35); border-radius: 11px; background: #fff5ed; color: var(--prodovi-orange); font-size: .78rem; font-weight: 800; cursor: pointer; transition: .2s ease; }
    #questionnaire-editor .add-other-button:hover { border-color: var(--prodovi-orange); background: var(--prodovi-orange); color: #fff; transform: translateY(-1px); }
    #questionnaire-editor .add-question-button { border-radius: 12px; background: linear-gradient(135deg, var(--prodovi-orange), #dc5710); box-shadow: 0 12px 25px rgba(239,108,34,.22); }
    #questionnaire-editor .add-question-button:hover { background: linear-gradient(135deg, #ff7d32, var(--prodovi-orange)); }
    #questionnaire-editor .pregunta-item { position: relative; overflow: visible; border: 1px solid #e8dfea; border-radius: 19px; box-shadow: 0 12px 30px rgba(55,34,65,.06); }
    #questionnaire-editor .pregunta-item:focus-within { z-index: 30; }
    #questionnaire-editor .question-head { border-radius: 18px 18px 0 0; }
    #questionnaire-editor .pregunta-item:hover { border-color: rgba(91,43,118,.28); box-shadow: 0 18px 38px rgba(55,34,65,.11); transform: translateY(-2px); }
    #questionnaire-editor .pregunta-item:nth-child(4n+1) .question-accent { background: var(--prodovi-purple); }
    #questionnaire-editor .pregunta-item:nth-child(4n+2) .question-accent { background: var(--prodovi-orange); }
    #questionnaire-editor .pregunta-item:nth-child(4n+3) .question-accent { background: var(--prodovi-turquoise); }
    #questionnaire-editor .pregunta-item:nth-child(4n) .question-accent { background: var(--prodovi-green); }
    #questionnaire-editor .question-head { background: linear-gradient(90deg, #faf7fb, #fff); }
    #questionnaire-editor .question-number { width: 32px; height: 32px; background: var(--prodovi-purple); color: #fff; box-shadow: 0 7px 16px rgba(91,43,118,.2); }
    #questionnaire-editor .required-box { border-color: #e4dce7; border-radius: 12px; background: #faf7fb; }
    #questionnaire-editor .save-button { border-radius: 12px; background: linear-gradient(135deg, var(--prodovi-purple), var(--prodovi-purple-dark)); box-shadow: 0 13px 28px rgba(91,43,118,.25); }
    #questionnaire-editor .save-button:hover { background: linear-gradient(135deg, #71388e, var(--prodovi-purple)); }
    #questionnaire-editor .cancel-button { border-radius: 12px; border-color: #d9cedd; color: #65576a; }
    #questionnaire-editor .cancel-button:hover { border-color: var(--prodovi-turquoise); background: #eff9f8; color: var(--prodovi-turquoise); }
    #questionnaire-editor .pregunta-requerido:checked + div { background-color: var(--prodovi-green); }

    /* Diseño plano: la jerarquía se marca con líneas y espacio, no con tarjetas anidadas */
    #questionnaire-editor .prodovi-hero {
        min-height: 150px;
        border-bottom: 5px solid var(--prodovi-orange);
    }
    #questionnaire-editor .hero-content { max-width: 850px; }
    #questionnaire-editor .back-button,
    #questionnaire-editor .add-question-button,
    #questionnaire-editor .save-button,
    #questionnaire-editor .cancel-button,
    #questionnaire-editor .add-option-button,
    #questionnaire-editor .add-other-button {
        border-radius: 3px;
        box-shadow: none;
    }
    #questionnaire-editor .topic-panel {
        padding: 0 0 32px;
        border: 0;
        border-bottom: 1px solid #d9d2dc;
        border-radius: 0;
        background: #fff;
    }
    #questionnaire-editor .topic-panel > .border {
        padding: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
    #questionnaire-editor .section-label {
        padding-bottom: 12px;
        border-bottom: 2px solid #ece7ee;
    }
    #questionnaire-editor .section-label-icon,
    #questionnaire-editor .type-current-icon,
    #questionnaire-editor .options-builder-icon {
        border-radius: 2px;
    }
    #questionnaire-editor input:not([type="checkbox"]),
    #questionnaire-editor textarea,
    #questionnaire-editor select,
    #questionnaire-editor .type-dropdown-trigger {
        border-radius: 3px;
        background: #fff;
        box-shadow: none;
    }
    #questionnaire-editor .pregunta-item {
        border: 0;
        border-top: 1px solid #d9d2dc;
        border-bottom: 1px solid #d9d2dc;
        border-radius: 0;
        box-shadow: none;
    }
    #questionnaire-editor .pregunta-item:hover {
        border-color: var(--prodovi-purple);
        box-shadow: none;
        transform: none;
    }
    #questionnaire-editor .question-head {
        border-radius: 0;
        background: #f7f5f8;
    }
    #questionnaire-editor .question-number {
        border-radius: 2px !important;
        box-shadow: none;
    }
    #questionnaire-editor .required-box {
        border-radius: 3px;
        background: #fff;
        box-shadow: none;
    }
    #questionnaire-editor .options-builder {
        padding: 18px 0 0 18px;
        border: 0;
        border-left: 3px solid var(--prodovi-turquoise);
        border-radius: 0;
        background: #fff;
    }
    #questionnaire-editor .option-builder-row {
        border-width: 0 0 1px;
        border-radius: 0;
        box-shadow: none;
    }
    #questionnaire-editor .type-dropdown-menu {
        border-radius: 3px;
    }
    #questionnaire-editor .type-dropdown-option,
    #questionnaire-editor .remove-option-button,
    #questionnaire-editor .remove-pregunta-btn {
        border-radius: 2px;
    }
    #questionnaire-editor #empty-preguntas-state {
        border-radius: 0;
        background: #fff;
    }
    #questionnaire-editor .editor-shell { padding-bottom: 76px; }
    #questionnaire-editor .floating-actions {
        position: fixed;
        z-index: 60;
        right: 24px;
        bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px;
        border: 1px solid rgba(91,43,118,.14);
        background: rgba(255,255,255,.94);
        box-shadow: 0 12px 32px rgba(35,24,40,.18);
        backdrop-filter: blur(10px);
    }
    @media (max-width: 640px) {
        #questionnaire-editor .prodovi-hero { min-height: 190px; padding: 27px 22px; border-radius: 0; }
        #questionnaire-editor .login-mosaic { display: none; }
        #questionnaire-editor .brand-bars { left: 22px; }
        #questionnaire-editor .editor-shell { margin-top: 0; border-radius: 0; }
        #questionnaire-editor .option-builder-add { grid-template-columns: 1fr 1fr; }
        #questionnaire-editor .new-option-input { grid-column: 1 / -1; }
        #questionnaire-editor .floating-actions {
            right: 12px;
            bottom: 12px;
            left: 12px;
        }
        #questionnaire-editor .floating-actions > * { flex: 1; justify-content: center; }
    }
</style>

<div id="questionnaire-editor" class="min-h-screen font-sans">
    <div class="w-full">
        
        <!-- Header Section -->
        <div class="prodovi-hero flex items-center justify-between gap-8">
            <div class="hero-content">
                <a href="{{ route('administrador.cuestionario.estructura.index') }}" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Regresar
                </a>
                <h1 class="hero-title">Constructor de <span>brief</span></h1>
                <p class="hero-description">
                    {{ isset($tema) ? 'Edita “' . $tema->nombre_tema . '” y define preguntas claras, rápidas y útiles para conocer cada marca.' : 'Construye un brief breve y atractivo para conocer lo esencial de cada marca.' }}
                </p>
            </div>
            <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>

        <div class="editor-shell overflow-hidden">
            <div class="p-8">
                <form action="{{ isset($tema) ? route('administrador.cuestionario.estructura.update', $tema->id) : route('administrador.cuestionario.estructura.store') }}" method="POST">
                    @csrf
                    @if(isset($tema)) @method('PUT') @endif

                    <!-- Datos del Tema -->
                    <div class="topic-panel grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8 mb-10">
                        <div class="sm:col-span-2">
                            <h2 class="section-label"><span class="section-label-icon"><i class="fas fa-layer-group"></i></span>Información general</h2>
                        </div>
                        
                        <div class="sm:col-span-1 border border-slate-200 p-4 rounded-xl shadow-sm hover:border-indigo-300 transition-colors duration-200">
                            <label for="nombre_tema" class="block text-sm font-medium text-slate-700 mb-1">Nombre del Tema <span class="text-red-500">*</span></label>
                            <div class="prodovi-input-wrap"><i class="fas fa-layer-group"></i><input type="text" id="nombre_tema" name="nombre_tema" value="{{ old('nombre_tema', $tema->nombre_tema ?? '') }}" required placeholder="Ej: Brief inicial de marca" class="w-full px-4 py-2.5 text-slate-900 transition-all duration-200 sm:text-sm"></div>
                        </div>
                        
                        <div class="sm:col-span-1 border border-slate-200 p-4 rounded-xl shadow-sm hover:border-indigo-300 transition-colors duration-200">
                            <label for="descripcion_tema" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <div class="prodovi-input-wrap is-textarea"><i class="fas fa-align-left"></i><textarea id="descripcion_tema" name="descripcion_tema" rows="2" placeholder="Propósito de este bloque de preguntas..." class="w-full px-4 py-2.5 text-slate-900 transition-all duration-200 sm:text-sm">{{ old('descripcion_tema', $tema->descripcion_tema ?? '') }}</textarea></div>
                        </div>
                    </div>

                    <!-- Preguntas Dinámicas -->
                    <div class="mb-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                            <h2 class="section-label mb-0"><span class="section-label-icon" style="background:rgba(239,108,34,.11);color:#EF6C22"><i class="fas fa-list-check"></i></span>Preguntas del tema</h2>
                            <button type="button" id="add-pregunta-btn" class="add-question-button inline-flex items-center px-5 py-3 text-white text-sm font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus:outline-none gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Añadir Pregunta
                            </button>
                        </div>
                        
                        <div id="preguntas-container" class="space-y-5 min-h-[150px]">
                            {{-- Las preguntas existentes se cargarán aquí con JavaScript --}}
                        </div>
                        <div class="flex justify-center pt-7">
                            <button type="button" id="add-pregunta-bottom-btn" class="add-question-button inline-flex items-center px-5 py-3 text-white text-sm font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus:outline-none gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Añadir Pregunta
                            </button>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="floating-actions">
                        <a href="{{ route('administrador.cuestionario.estructura.index') }}" class="cancel-button inline-flex items-center px-5 py-2.5 border bg-white text-sm font-medium transition-colors focus:outline-none gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Cancelar
                        </a>
                        <button type="submit" class="save-button inline-flex items-center px-6 py-2.5 text-white text-sm font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus:outline-none gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ isset($tema) ? 'Guardar Cambios' : 'Crear Tema' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template para una nueva pregunta (usado por JavaScript) -->
<template id="pregunta-template">
    <div class="pregunta-item bg-white transition-all duration-300 group">
        <!-- Encabezado de la pregunta -->
        <div class="question-head px-5 py-3 border-b border-slate-200 flex justify-between items-center relative">
            <div class="question-accent absolute left-0 top-0 bottom-0 w-1"></div>
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2 uppercase tracking-wider">
                <span class="question-number inline-flex items-center justify-center rounded-full text-xs">
                    <span class="pregunta-num"></span>
                </span>
                Pregunta
            </h3>
            <button type="button" title="Eliminar pregunta" class="remove-pregunta-btn p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
        
        <div class="p-5">
            <input type="hidden" name="preguntas[index][id]" class="pregunta-id">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-full">
                <!-- Texto de la pregunta -->
                <div class="lg:col-span-8 flex flex-col justify-end">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Texto de la pregunta <span class="text-red-500">*</span></label>
                    <div class="prodovi-input-wrap"><i class="fas fa-circle-question"></i><input type="text" name="preguntas[index][pregunta]" required placeholder="Ej: ¿Cuál es tu objetivo principal?" class="w-full px-4 py-2.5 text-slate-900 transition-all sm:text-sm pregunta-texto outline-none"></div>
                </div>
                
                <!-- Tipo de respuesta -->
                <div class="lg:col-span-4 flex flex-col justify-end">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de respuesta <span class="text-red-500">*</span></label>
                    <div class="answer-type-dropdown">
                        <input type="hidden" name="preguntas[index][tipo_respuesta]" value="texto" class="pregunta-tipo">
                        <button type="button" class="type-dropdown-trigger" aria-expanded="false">
                            <span class="type-current-icon"><i class="fas fa-font"></i></span>
                            <span class="type-current-label">Texto corto</span>
                            <i class="fas fa-chevron-down type-chevron"></i>
                        </button>
                        <div class="type-dropdown-menu">
                            <button type="button" class="type-dropdown-option is-selected" data-value="texto" data-label="Texto corto" data-icon="fa-font"><i class="fas fa-font"></i><span>Texto corto</span></button>
                            <button type="button" class="type-dropdown-option" data-value="texto_largo" data-label="Texto largo" data-icon="fa-align-left"><i class="fas fa-align-left"></i><span>Texto largo</span></button>
                            <button type="button" class="type-dropdown-option" data-value="opcion_multiple" data-label="Selección única" data-icon="fa-circle-dot"><i class="fas fa-circle-dot"></i><span>Selección única</span></button>
                            <button type="button" class="type-dropdown-option" data-value="checkbox" data-label="Selección múltiple" data-icon="fa-square-check"><i class="fas fa-square-check"></i><span>Selección múltiple</span></button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-12 opciones-field hidden">
                    <textarea name="preguntas[index][opciones]" class="pregunta-opciones hidden"></textarea>
                    <div class="options-builder">
                        <div class="options-builder-head">
                            <span class="options-builder-icon"><i class="fas fa-circle-dot"></i></span>
                            <span><strong class="options-builder-title">Opciones de selección única</strong><small class="options-builder-description">El cliente podrá elegir solamente una alternativa.</small></span>
                        </div>
                        <div class="option-builder-list"></div>
                        <div class="option-builder-add">
                            <input type="text" class="new-option-input" placeholder="Escribe una nueva opción">
                            <button type="button" class="add-option-button"><i class="fas fa-plus"></i><span>Agregar</span></button>
                            <button type="button" class="add-other-button"><i class="fas fa-pen"></i><span>Otro</span></button>
                        </div>
                    </div>
                </div>
                
                <!-- Texto de ayuda -->
                <div class="lg:col-span-8 flex flex-col justify-end">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Texto de ayuda
                        <span class="text-slate-400 font-normal text-xs">(Opcional)</span>
                    </label>
                    <div class="prodovi-input-wrap"><i class="fas fa-lightbulb"></i><input type="text" name="preguntas[index][ayuda]" placeholder="Ej: Puedes elegir más de una opción." class="w-full px-4 py-2.5 text-slate-900 transition-all sm:text-sm pregunta-ayuda outline-none"></div>
                </div>
                
                <!-- Requerido -->
                <div class="lg:col-span-4 flex items-end">
                    <div class="required-box w-full border py-2.5 px-4 flex items-center shadow-sm">
                        <label class="flex items-center cursor-pointer w-full select-none">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="preguntas[index][requerido]" value="1" class="peer sr-only pregunta-requerido">
                                <!-- El track del switch -->
                                <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 peer-focus:ring-2 peer-focus:ring-emerald-500/40 transition-colors duration-200 ease-in-out cursor-pointer"></div>
                                <!-- El dot del switch -->
                                <div class="absolute left-[2px] w-4 h-4 bg-white rounded-full border border-slate-300 peer-checked:border-emerald-500 peer-checked:translate-x-full transition-all duration-200 shadow-sm cursor-pointer"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-slate-700 transition-colors peer-checked:text-emerald-700">Respuesta Obligatoria</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('preguntas-container');
    const addBtn = document.getElementById('add-pregunta-btn');
    const addBottomBtn = document.getElementById('add-pregunta-bottom-btn');
    const template = document.getElementById('pregunta-template');
    let preguntaIndex = 0;

    // Cargar preguntas existentes si estamos en modo edición
    @if(isset($tema))
        const preguntasExistentes = @json($tema->preguntas);
        if (preguntasExistentes && preguntasExistentes.length > 0) {
            preguntasExistentes.forEach((p, idx) => {
                // Añadir un pequeño retraso a cada pregunta existente
                setTimeout(() => {
                    addPregunta(p, false); 
                }, idx * 80);
            });
        } else {
            addPregunta();
            setupEmptyState();
        }
    @else
        // Añadir una pregunta por defecto si es creación
        addPregunta();
    @endif

    function addPregunta(data = {}, animate = true) {
        // Remover el empty state si existe
        const emptyState = document.getElementById('empty-preguntas-state');
        if (emptyState) {
            emptyState.remove();
        }

        const clone = template.content.cloneNode(true);
        const index = preguntaIndex++;
        const itemRoot = clone.querySelector('.pregunta-item');
        
        // Animación de entrada
        if(animate) {
            itemRoot.classList.add('animate-enter');
        }
        
        // Reemplazar 'index' con el índice real en los atributos 'name'
        clone.querySelectorAll('[name*="index"]').forEach(el => {
            el.name = el.name.replace('index', index);
        });
        
        // Actualizar número de pregunta
        clone.querySelector('.pregunta-num').textContent = index + 1;
        
        // Rellenar datos si existen
        if (data.id) clone.querySelector('.pregunta-id').value = data.id;
        if (data.pregunta) clone.querySelector('.pregunta-texto').value = data.pregunta;
        if (data.ayuda) clone.querySelector('.pregunta-ayuda').value = data.ayuda;
        if (Array.isArray(data.opciones)) clone.querySelector('.pregunta-opciones').value = data.opciones.join('\n');

        const typeInput = clone.querySelector('.pregunta-tipo');
        const typeDropdown = clone.querySelector('.answer-type-dropdown');
        const typeTrigger = clone.querySelector('.type-dropdown-trigger');
        const typeMenu = clone.querySelector('.type-dropdown-menu');
        const typeLabel = clone.querySelector('.type-current-label');
        const typeIcon = clone.querySelector('.type-current-icon i');
        const typeOptions = Array.from(clone.querySelectorAll('.type-dropdown-option'));
        const optionsField = clone.querySelector('.opciones-field');
        const optionsInput = clone.querySelector('.pregunta-opciones');
        const optionsList = clone.querySelector('.option-builder-list');
        const optionsTitle = clone.querySelector('.options-builder-title');
        const optionsDescription = clone.querySelector('.options-builder-description');
        const optionsIcon = clone.querySelector('.options-builder-icon i');
        const newOptionInput = clone.querySelector('.new-option-input');
        const addOptionButton = clone.querySelector('.add-option-button');
        const addOtherButton = clone.querySelector('.add-other-button');
        const readOptions = () => optionsInput.value.split(/\r?\n/).map(option => option.trim()).filter(Boolean);
        const syncOptions = () => {
            optionsInput.value = Array.from(optionsList.querySelectorAll('.option-builder-input'))
                .map(input => input.value.trim())
                .filter(Boolean)
                .join('\n');
        };
        const addOptionRow = value => {
            const row = document.createElement('div');
            row.className = 'option-builder-row';
            const iconClass = typeInput.value === 'checkbox' ? 'fa-square-check' : 'fa-circle-dot';
            row.innerHTML = `
                <span class="option-kind-icon"><i class="far ${iconClass}"></i></span>
                <input type="text" class="option-builder-input" value="" aria-label="Opción de respuesta">
                <button type="button" class="remove-option-button" title="Eliminar opción"><i class="fas fa-xmark"></i></button>
            `;
            row.querySelector('.option-builder-input').value = value;
            row.querySelector('.option-builder-input').addEventListener('input', syncOptions);
            row.querySelector('.remove-option-button').addEventListener('click', () => {
                row.remove();
                syncOptions();
            });
            optionsList.appendChild(row);
        };
        const renderOptions = () => {
            const values = readOptions();
            optionsList.innerHTML = '';
            values.forEach(addOptionRow);
        };
        const updateOptionsVisibility = () => {
            const usesOptions = ['opcion_multiple', 'checkbox'].includes(typeInput.value);
            optionsField.classList.toggle('hidden', !usesOptions);
            if (!usesOptions) return;

            const isMultiple = typeInput.value === 'checkbox';
            optionsTitle.textContent = isMultiple ? 'Opciones de selección múltiple' : 'Opciones de selección única';
            optionsDescription.textContent = isMultiple
                ? 'El cliente podrá elegir varias alternativas.'
                : 'El cliente podrá elegir solamente una alternativa.';
            optionsIcon.className = `fas ${isMultiple ? 'fa-square-check' : 'fa-circle-dot'}`;
            renderOptions();
        };
        const closeTypeMenu = () => {
            typeMenu.classList.remove('is-open');
            typeTrigger.classList.remove('is-open');
            typeTrigger.setAttribute('aria-expanded', 'false');
        };
        const selectType = value => {
            const selected = typeOptions.find(option => option.dataset.value === value) || typeOptions[0];
            typeInput.value = selected.dataset.value;
            typeLabel.textContent = selected.dataset.label;
            typeIcon.className = `fas ${selected.dataset.icon}`;
            typeOptions.forEach(option => option.classList.toggle('is-selected', option === selected));
            updateOptionsVisibility();
            closeTypeMenu();
        };
        typeTrigger.addEventListener('click', () => {
            const willOpen = !typeMenu.classList.contains('is-open');
            document.querySelectorAll('.type-dropdown-menu.is-open').forEach(menu => menu.classList.remove('is-open'));
            document.querySelectorAll('.type-dropdown-trigger.is-open').forEach(trigger => trigger.classList.remove('is-open'));
            if (willOpen) {
                typeMenu.classList.add('is-open');
                typeTrigger.classList.add('is-open');
                typeTrigger.setAttribute('aria-expanded', 'true');
            }
        });
        typeOptions.forEach(option => option.addEventListener('click', () => selectType(option.dataset.value)));
        addOptionButton.addEventListener('click', () => {
            const value = newOptionInput.value.trim();
            if (!value) {
                newOptionInput.focus();
                return;
            }
            syncOptions();
            optionsInput.value = [...readOptions(), value].join('\n');
            renderOptions();
            newOptionInput.value = '';
            newOptionInput.focus();
        });
        addOtherButton.addEventListener('click', () => {
            syncOptions();
            const values = readOptions();
            if (!values.some(value => value.toLowerCase() === 'otro')) {
                optionsInput.value = [...values, 'Otro'].join('\n');
                renderOptions();
            }
        });
        newOptionInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                addOptionButton.click();
            }
        });
        document.addEventListener('click', event => {
            if (!typeDropdown.contains(event.target)) closeTypeMenu();
        });
        selectType(data.tipo_respuesta === 'texto_corto' ? 'texto' : (data.tipo_respuesta || 'texto'));
        
        // Setup toggle switch
        const requiredCheckbox = clone.querySelector('.pregunta-requerido');
        if (data.requerido) {
            requiredCheckbox.checked = true;
        }

        // Evento para eliminar pregunta
        const removeBtn = clone.querySelector('.remove-pregunta-btn');
        removeBtn.addEventListener('click', function(e) {
            // Confirmación opcional si la pregunta ya estaba guardada y no está vacía
            const card = this.closest('.pregunta-item');
            const preexitingId = card.querySelector('.pregunta-id').value;
            const textInput = card.querySelector('.pregunta-texto').value;
            
            if (preexitingId && textInput) {
                if(!confirm('¿Estás seguro de eliminar esta pregunta ya guardada?')) return;
            }

            // Animación de salida (zoom out)
            card.style.transition = 'all 0.3s ease-out';
            card.style.transform = 'scale(0.95)';
            card.style.opacity = '0';
            card.style.height = '0px';
            card.style.margin = '0px';
            card.style.padding = '0px';
            card.style.overflow = 'hidden';
            
            setTimeout(() => {
                card.remove();
                updateQuestionNumbers();
                checkEmptyState();
            }, 300);
        });
        
        // Fokus inmediato si es una nueva pregunta y la página ya cargó
        if(animate && document.readyState === 'complete') {
            setTimeout(() => {
                const input = container.lastElementChild?.querySelector('.pregunta-texto');
                if(input) input.focus();
            }, 50);
        }

        container.appendChild(clone);
        updateQuestionNumbers();
    }

    function checkEmptyState() {
        const items = container.querySelectorAll('.pregunta-item');
        if (items.length === 0) {
            setupEmptyState();
        }
    }

    function setupEmptyState() {
        if (!document.getElementById('empty-preguntas-state')) {
            const emptyHtml = `
                <div id="empty-preguntas-state" class="text-center py-10 px-6 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl animate-enter">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 mb-4 shadow-sm border border-indigo-100">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Aún no hay preguntas</h3>
                    <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto mb-5">Empieza añadiendo la primera pregunta para este bloque de cuestionario.</p>
                    <button type="button" onclick="document.getElementById('add-pregunta-btn').click()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors gap-2">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Añadir primera pregunta
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', emptyHtml);
        }
    }

    function updateQuestionNumbers() {
        const items = container.querySelectorAll('.pregunta-item');
        items.forEach((item, i) => {
            const label = item.querySelector('.pregunta-num');
            if(label) label.textContent = i + 1;
        });
    }

    addBtn.addEventListener('click', () => addPregunta());
    addBottomBtn.addEventListener('click', () => addPregunta());
});
</script>
@endsection
