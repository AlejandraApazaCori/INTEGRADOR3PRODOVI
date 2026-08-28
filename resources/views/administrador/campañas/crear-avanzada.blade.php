@extends('layouts.app')

@section('title', isset($campania) ? 'Editar campaña' : 'Crear campaña avanzada')

@section('content')
@php
    $isEditing = isset($campania);
    $empresa = $suscripcion->empresa;
    $campaignMode = old('modo_creacion', $isEditing ? ($campania->modo_creacion ?: 'manual') : 'manual');
    $editingCampaignId = $isEditing ? $campania->id : null;
    $campaignChannels = (array) old('canales', $isEditing ? ($campania->canales ?? []) : []);
    $campaignIndicators = (array) old('indicadores', $isEditing ? ($campania->indicadores ?? []) : []);
    $campaignTasks = old('tareas', $isEditing ? ($tareasActuales ?? []) : []);
    $campaignAudiences = old('publicos_objetivo', $isEditing ? ($publicosActuales ?? []) : []);
    $campaignDesignerIds = array_values(array_filter((array) old(
        'disenadores_ids',
        $isEditing ? $campania->disenadores->pluck('id')->all() : []
    )));
    $campaignAdministrator = [
        'id' => $isEditing ? $campania->usuario_creador_id : auth()->id(),
        'name' => $isEditing ? ($campania->creador?->name ?? 'Administrador') : (auth()->user()?->name ?? 'Administrador'),
        'role' => 'Administrador',
    ];
@endphp
<div class="campaign-wizard-page">
    <header class="campaign-wizard-hero rp-banner">
        <div class="campaign-wizard-hero-overlay"></div>
        <div class="campaign-wizard-hero-content">
            <div class="campaign-wizard-identity">
                <span class="campaign-wizard-hero-icon"><i class="fas fa-bullhorn"></i></span>
                <div>
                    <span class="campaign-wizard-eyebrow">Planificación operativa</span>
                    <h1>{{ $isEditing ? 'Editar campaña' : 'Crear campaña' }} para {{ $empresa->nombre_empresa }}</h1>
                    <p>{{ $isEditing ? 'Actualiza la estrategia, el equipo y las tareas conservando el historial de la campaña.' : 'El cuestionario, el resumen ejecutivo y el plan de marketing ya están listos.' }}</p>
                </div>
            </div>
            <a href="{{ route('administrador.campañas.index') }}"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </header>

    @if(session('error'))<div class="wizard-alert is-error"><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="wizard-alert is-error"><i class="fas fa-circle-exclamation"></i><div><strong>Revisa la información:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <section class="wizard-readiness">
        <article class="is-ready"><i class="fas fa-clipboard-check"></i><div><small>Paso 1</small><strong>Cuestionario empresarial</strong><span>Completado</span></div><a class="wizard-readiness-link" href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" title="Ver respuestas del cuestionario">Ver <i class="fas fa-arrow-up-right-from-square"></i></a></article>
        <article class="is-ready"><i class="fas fa-file-lines"></i><div><small>Paso 2</small><strong>Resumen ejecutivo</strong><span>Generado</span></div><a class="wizard-readiness-link" href="{{ route('administrador.empresas.reporte', $empresa->id) }}" title="Ver resumen ejecutivo">Ver <i class="fas fa-arrow-up-right-from-square"></i></a></article>
        <article class="is-ready"><i class="fas fa-lightbulb"></i><div><small>Paso 3</small><strong>Plan de marketing</strong><span>{{ $planMarketing ? 'Activo' : 'No disponible' }}</span></div>@if($planMarketing)<a class="wizard-readiness-link" href="{{ route('administrador.empresas.planes-marketing.show', $planMarketing) }}" title="Ver plan de marketing autogenerado">Ver <i class="fas fa-arrow-up-right-from-square"></i></a>@endif</article>
        <article class="is-current"><i class="fas fa-bullhorn"></i><div><small>Paso 4</small><strong>Campaña operativa</strong><span>{{ $isEditing ? 'En edición' : 'En preparación' }}</span></div></article>
    </section>

    <form id="advanced-campaign-form" action="{{ $isEditing ? route('administrador.campañas.update', $campania) : route('administrador.campañas.guardar-avanzada') }}" method="POST">
        @csrf
        @if($isEditing) @method('PUT') @endif
        <input type="hidden" name="usuario_cliente_id" value="{{ $suscripcion->usuario_id }}">
        <input type="hidden" name="suscripcion_id" value="{{ $suscripcion->id }}">
        <input type="hidden" name="modo_creacion" id="modo_creacion" value="{{ $campaignMode }}">
        @if($isEditing)<input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">@endif
        <div id="indicators-hidden"></div>

        <nav class="campaign-creation-timeline" aria-label="Progreso de creación de la campaña">
            <button type="button" class="is-active" data-go-step="0"><span>1</span><div><small>Método</small><strong>Tipo de creación</strong></div></button>
            <button type="button" data-go-step="1"><span>2</span><div><small>Estrategia</small><strong>Brief de campaña</strong></div></button>
            <button type="button" data-go-step="2"><span>3</span><div><small>Equipo</small><strong>Responsables</strong></div></button>
            <button type="button" data-go-step="3"><span>4</span><div><small>Ejecución</small><strong>Tareas y fechas</strong></div></button>
        </nav>

    <section class="wizard-mode-section wizard-step is-active" data-wizard-step="0">
        <div class="wizard-section-heading wizard-mode-heading"><div><h2>{{ $isEditing ? '¿Con qué modo quieres trabajar?' : '¿Cómo quieres crearla?' }}</h2><p>Elige entre comenzar desde cero, automatizarla con las reglas del sistema o enriquecerla con inteligencia artificial.</p></div></div>
        <div class="wizard-modes">
            <button type="button" class="wizard-mode {{ $campaignMode === 'manual' ? 'is-selected' : '' }}" data-mode="manual" onclick="requestCampaignModeChange(this, event)">
                <i class="fas fa-pen-ruler"></i><strong>Manual</strong><span>Define estrategia, equipo y tareas desde cero.</span><em>Control total</em>
            </button>
            <button type="button" class="wizard-mode {{ $campaignMode === 'automatico' ? 'is-selected' : '' }}" data-mode="automatico" onclick="requestCampaignModeChange(this, event)">
                <i class="fas fa-gears"></i><strong>Automático</strong><span>Usa el cuestionario y el plan para completar estrategia, equipo, tareas y responsables sin IA.</span><em>Recomendado</em>
            </button>
            <button type="button" class="wizard-mode {{ $campaignMode === 'asistido' ? 'is-selected' : '' }}" data-mode="asistido" onclick="requestCampaignModeChange(this, event)">
                <i class="fas fa-wand-magic-sparkles"></i><strong>Asistido por IA</strong><span>La inteligencia artificial analiza las fuentes y prepara una propuesta editable con respaldo automático.</span><em>Inteligencia artificial</em>
            </button>
        </div>
    </section>

        <section class="wizard-panel wizard-step" data-wizard-step="1">
            <div class="wizard-section-heading wizard-mode-heading"><div><h2>Brief estratégico de campaña</h2><p>Estos datos permiten medir y ejecutar la estrategia, no solo describirla.</p></div></div>
            <div class="wizard-prefill-note" id="prefill-note" hidden><i class="fas fa-wand-magic-sparkles"></i><span><strong>Información preparada</strong>Completamos esta etapa con el cuestionario y el plan de marketing. Revisa los datos y ajusta solo lo necesario.</span></div>
            <div class="wizard-grid">
                <label><span>Nombre de la campaña *</span><input name="nombre" id="nombre" value="{{ old('nombre', $isEditing ? $campania->nombre : '') }}" maxlength="100" required placeholder="Ej.: Lanzamiento digital septiembre"></label>
                <label><span>Tono de comunicación</span><input name="tono_comunicacion" id="tono_comunicacion" value="{{ old('tono_comunicacion', $isEditing ? $campania->tono_comunicacion : '') }}" maxlength="120" placeholder="Ej.: cercano, profesional y educativo"></label>
                <label class="is-full"><span>Descripción operativa *</span><textarea name="descripcion" id="descripcion" required rows="5" placeholder="Explica el enfoque general de ejecución...">{{ old('descripcion', $isEditing ? $campania->descripcion : '') }}</textarea></label>
                <label class="is-full"><span>Objetivo general</span><textarea name="objetivo_general" id="objetivo_general" rows="3" placeholder="¿Qué resultado debe alcanzar esta campaña?">{{ old('objetivo_general', $isEditing ? $campania->objetivo_general : '') }}</textarea></label>
                <div class="wizard-audiences is-full">
                    <div class="wizard-audiences-head"><div><span>Públicos objetivo</span><small>Separa cada segmento y conserva solamente la información esencial.</small></div><button type="button" id="add-audience"><i class="fas fa-plus"></i> Agregar público</button></div>
                    <div id="audiences-container"></div>
                </div>
                <label><span>Mensaje principal</span><textarea name="mensaje_principal" id="mensaje_principal" rows="6" placeholder="Idea central que debe recordar la audiencia...">{{ old('mensaje_principal', $isEditing ? $campania->mensaje_principal : '') }}</textarea></label>
                <fieldset><legend>Canales</legend><label class="wizard-check"><input type="checkbox" name="canales[]" value="Facebook" @checked(in_array('Facebook', $campaignChannels))><span><i class="fab fa-facebook"></i> Facebook</span></label><label class="wizard-check"><input type="checkbox" name="canales[]" value="Instagram" @checked(in_array('Instagram', $campaignChannels))><span><i class="fab fa-instagram"></i> Instagram</span></label><label class="wizard-check"><input type="checkbox" name="canales[]" value="TikTok" @checked(in_array('TikTok', $campaignChannels))><span><i class="fab fa-tiktok"></i> TikTok</span></label><label class="wizard-check"><input type="checkbox" name="canales[]" value="WhatsApp" @checked(in_array('WhatsApp', $campaignChannels))><span><i class="fab fa-whatsapp"></i> WhatsApp</span></label></fieldset>
                <label><span>Indicadores de rendimiento</span><input id="indicadores_csv" value="{{ implode(', ', $campaignIndicators) }}" placeholder="Alcance, interacciones, CTR"><small>Puedes agregar varios indicadores separándolos con comas.</small></label>
                @if($isEditing)
                    <label><span>Estado de la campaña *</span><select name="estado" required><option value="activa" @selected(old('estado', $campania->estado) === 'activa')>Activa</option><option value="pausada" @selected(old('estado', $campania->estado) === 'pausada')>Pausada</option><option value="finalizada" @selected(old('estado', $campania->estado) === 'finalizada')>Finalizada</option></select></label>
                    <label><span>Fecha de finalización *</span><input type="date" name="fecha_fin" value="{{ old('fecha_fin', $fechaFin) }}" min="{{ $fechaInicio }}" required></label>
                @endif
            </div>
        </section>

        <section class="wizard-panel wizard-step" data-wizard-step="2">
            <div class="wizard-section-heading wizard-mode-heading"><div><h2>Equipo responsable</h2><p>Asigna un Community Manager y agrega los diseñadores que necesite la campaña.</p></div></div>
            <div class="wizard-team-recommendation">
                <button type="button" id="recommend-team" data-url="{{ route('administrador.campañas.recomendar-community-manager', array_filter(['suscripcion_id' => $suscripcion->id, 'campania_id' => $isEditing ? $campania->id : null])) }}"><i class="fas fa-wand-magic-sparkles"></i> Recomendar equipo</button>
                <div id="team-recommendation-result" hidden></div>
            </div>
            <div class="wizard-team-layout">
                <div class="wizard-team-column">
                    <div class="wizard-team-column-head"><span><i class="fas fa-headset"></i></span><div><strong>Community Manager</strong><small>Solo puede asignarse un responsable principal.</small></div></div>
                    <label><span>Responsable de la campaña *</span><select name="community_manager_id" id="community_manager_id" required><option value="">Selecciona un responsable</option>@foreach($communityManagers as $manager)<option value="{{ $manager->id }}" @selected(old('community_manager_id', $isEditing ? $campania->community_manager_id : null) == $manager->id)>{{ $manager->name }}</option>@endforeach</select></label>
                </div>
                <div class="wizard-team-column">
                    <div class="wizard-team-column-head"><span><i class="fas fa-palette"></i></span><div><strong>Equipo de diseño</strong><small>Puedes sumar varios diseñadores y distribuir sus tareas.</small></div><button type="button" id="add-designer"><i class="fas fa-plus"></i> Agregar</button></div>
                    <div id="designers-container"></div>
                    <div id="designers-empty" class="wizard-designers-empty">Aún no asignaste diseñadores. Puedes agregarlos ahora o usar la recomendación.</div>
                </div>
            </div>
        </section>

        <section class="wizard-panel wizard-step" data-wizard-step="3">
            <div class="wizard-tasks-heading">
                <div class="wizard-section-heading wizard-mode-heading"><div><h2>Cronograma y tareas</h2><p>Entregables, responsables, fechas y aprobaciones de la campaña.</p></div></div>
            </div>
            <div class="wizard-task-source">
                <div><i class="fas fa-calendar-check"></i><span><strong>Basado en el calendario operativo mensual</strong>Convertimos las semanas del plan de marketing en tareas editables para Diseño y Community Manager.</span></div>
                <div><button type="button" id="restore-plan-tasks"><i class="fas fa-rotate-left"></i> Restaurar desde el plan</button><button type="button" id="add-task"><i class="fas fa-plus"></i> Agregar tarea</button></div>
            </div>
            <div id="tasks-empty" class="wizard-empty"><i class="fas fa-list-check"></i><strong>Todavía no hay tareas</strong><span>Agrégalas manualmente o recupera el calendario del plan de marketing.</span></div>
            <div id="tasks-container"></div>
        </section>

        <footer class="wizard-footer">
            <div id="mode-result"><i class="fas fa-circle-info"></i><span>{{ $isEditing ? 'Estás editando la configuración actual. Revisa cada etapa antes de guardar.' : 'El modo manual comienza con todos los campos vacíos.' }}</span></div>
            <div class="wizard-footer-actions">
                <a href="{{ $isEditing ? route('administrador.campañas.show', $campania) : route('administrador.campañas.index') }}">Cancelar</a>
                <button type="button" id="wizard-previous" class="wizard-button-secondary" hidden><i class="fas fa-arrow-left"></i> Anterior</button>
                <button type="button" id="wizard-next">Siguiente <i class="fas fa-arrow-right"></i></button>
                <button type="submit" id="save-campaign" hidden><i class="fas fa-check"></i> {{ $isEditing ? 'Guardar cambios' : 'Finalizar y crear campaña' }}</button>
            </div>
        </footer>
    </form>
</div>

<div id="ai-loading" class="wizard-loading" hidden><div><span class="wizard-spinner"></span><strong id="generation-loading-title">Preparando la campaña</strong><p id="generation-loading-copy">Analizamos el cuestionario, el plan, el calendario y la carga del equipo.</p></div></div>

@if($isEditing)
<div id="mode-change-modal" class="wizard-confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="mode-change-title">
    <div class="wizard-confirm-backdrop" onclick="closeCampaignModeModal()"></div>
    <div class="wizard-confirm-card">
        <span class="wizard-confirm-icon"><i class="fas fa-triangle-exclamation"></i></span>
        <div><small>Cambio de modo</small><h2 id="mode-change-title">¿Estás seguro de cambiar de modo?</h2></div>
        <p>Al continuar se borrará del formulario todo lo anterior previamente guardado: estrategia, públicos objetivo, canales, equipo y tareas. El reemplazo será definitivo cuando guardes los cambios.</p>
        <div class="wizard-confirm-actions"><button type="button" class="wizard-confirm-cancel" onclick="closeCampaignModeModal()">Cancelar</button><button type="button" class="wizard-confirm-accept" id="confirm-mode-change"><i class="fas fa-rotate"></i> Sí, cambiar de modo</button></div>
    </div>
</div>
@endif

<script>
    window.campaignWizardIsEditing = @json($isEditing);
    window.pendingCampaignModeButton = null;
    window.closeCampaignModeModal = function () {
        const modal = document.getElementById('mode-change-modal');
        if (modal) modal.hidden = true;
        document.body.style.overflow = '';
    };
    window.requestCampaignModeChange = function (button, event) {
        event?.preventDefault();
        const currentMode = document.getElementById('modo_creacion')?.value;
        if (!button?.dataset.mode || button.dataset.mode === currentMode) return;
        if (!window.campaignWizardIsEditing) {
            window.applyCampaignModeChange?.(button.dataset.mode, button);
            return;
        }
        window.pendingCampaignModeButton = button;
        const modal = document.getElementById('mode-change-modal');
        if (!modal) return;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        document.getElementById('confirm-mode-change')?.focus();
    };
    document.getElementById('confirm-mode-change')?.addEventListener('click', function () {
        const button = window.pendingCampaignModeButton;
        closeCampaignModeModal();
        if (button) window.applyCampaignModeChange?.(button.dataset.mode, button);
        window.pendingCampaignModeButton = null;
    });
</script>

<style>
.campaign-wizard-page{min-height:100vh;background:#f7f8fb;padding:0 0 48px;color:#263044}.campaign-wizard-hero{position:relative;min-height:180px;overflow:hidden;display:flex;align-items:center;color:#fff;background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}.campaign-wizard-hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-repeat:no-repeat}.campaign-wizard-hero-content{position:relative;z-index:1;width:100%;padding:30px 48px;display:flex;align-items:center;justify-content:space-between;gap:28px}.campaign-wizard-identity{min-width:0;display:flex;align-items:center;gap:16px}.campaign-wizard-hero-icon{width:54px;height:54px;display:grid;place-items:center;flex:0 0 54px;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.15);color:#fff;font-size:1.22rem;backdrop-filter:blur(5px)}.campaign-wizard-eyebrow{display:block;margin-bottom:7px;color:#ecfccb;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.campaign-wizard-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em;line-height:1.1}.campaign-wizard-hero p{max-width:720px;margin:8px 0 0;color:#f0fdf4;font-size:.84rem;line-height:1.55}.campaign-wizard-hero a{min-height:41px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 14px;border:1px solid rgba(255,255,255,.28);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:900;text-decoration:none;white-space:nowrap;backdrop-filter:blur(4px);transition:.18s}.campaign-wizard-hero a:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#638522;box-shadow:0 8px 20px rgba(31,55,20,.18)}.wizard-alert,.wizard-readiness,.wizard-mode-section,.wizard-panel,.wizard-footer{width:calc(100% - 48px);max-width:1380px;margin-left:auto;margin-right:auto}.wizard-alert{margin-top:20px;padding:14px 16px;display:flex;gap:10px;border-radius:12px;font-size:.78rem}.wizard-alert.is-error{background:#fff0f0;border:1px solid #fecaca;color:#991b1b}.wizard-readiness{margin-top:-28px;position:relative;z-index:2;display:grid;grid-template-columns:repeat(4,1fr);border:1px solid #e4e7ec;border-radius:16px;background:#fff;box-shadow:0 14px 35px rgba(31,41,55,.1);overflow:hidden}.wizard-readiness article{padding:18px;display:flex;align-items:center;gap:12px;border-right:1px solid #eceef2}.wizard-readiness article:last-child{border:0}.wizard-readiness article>div{min-width:0;flex:1}.wizard-readiness>article>i{width:40px;height:40px;display:grid;place-items:center;flex:0 0 40px;border-radius:12px;background:#ecfdf5;color:#059669}.wizard-readiness .is-current>i{background:#f0f6e7;color:#638522}.wizard-readiness small,.wizard-readiness strong,.wizard-readiness span{display:block}.wizard-readiness small{color:#9aa1ad;font-size:.57rem;font-weight:900;text-transform:uppercase}.wizard-readiness strong{font-size:.76rem;margin:2px 0}.wizard-readiness span{font-size:.62rem;color:#059669}.wizard-readiness-link{display:inline-flex;align-items:center;justify-content:center;gap:5px;flex:0 0 auto;padding:7px 9px;border:1px solid #dce7ca;border-radius:8px;background:#f5faed;color:#638522;font-size:.58rem;font-weight:900;text-decoration:none;transition:.18s}.wizard-readiness-link i{width:auto!important;height:auto!important;display:inline!important;flex:auto!important;border:0!important;border-radius:0!important;background:transparent!important;color:inherit!important;font-size:.52rem!important}.wizard-readiness-link:hover{transform:translateY(-1px);border-color:#789d32;background:#789d32;color:#fff;box-shadow:0 6px 13px rgba(99,133,34,.18)}.wizard-mode-section,.wizard-panel{margin-top:24px;padding:25px;border:1px solid #e2e5ea;border-radius:18px;background:#fff;box-shadow:0 8px 25px rgba(40,50,70,.05)}.wizard-section-heading{display:flex;align-items:center;gap:13px}.wizard-section-heading>span{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:10px;background:#4f46e5;color:#fff;font-weight:900}.wizard-section-heading h2{margin:0;color:#202838;font-size:1rem;font-weight:900}.wizard-section-heading p{margin:3px 0 0;color:#788191;font-size:.7rem}.wizard-modes{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:20px}.wizard-mode{position:relative;padding:19px;text-align:left;border:1px solid #e1e5eb;border-radius:14px;background:#fff;color:#2f3745;cursor:pointer;transition:.2s}.wizard-mode:hover,.wizard-mode.is-selected{transform:translateY(-2px);border-color:#6366f1;box-shadow:0 10px 25px rgba(79,70,229,.13)}.wizard-mode>i{width:40px;height:40px;display:grid;place-items:center;margin-bottom:12px;border-radius:11px;background:#eef2ff;color:#4f46e5}.wizard-mode strong,.wizard-mode span{display:block}.wizard-mode strong{font-size:.82rem}.wizard-mode span{margin-top:5px;color:#7a8391;font-size:.67rem;line-height:1.45}.wizard-mode em{position:absolute;right:12px;top:12px;padding:4px 7px;border-radius:99px;background:#f1f5f9;color:#64748b;font-size:.52rem;font-style:normal;font-weight:900;text-transform:uppercase}.wizard-mode.is-selected:after{content:'✓';position:absolute;right:14px;bottom:12px;color:#4f46e5;font-weight:900}.wizard-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin-top:22px}.wizard-grid>label,.wizard-grid fieldset{display:block;margin:0;padding:0;border:0}.wizard-grid .is-full{grid-column:1/-1}.wizard-grid label>span,.wizard-grid legend{display:block;margin-bottom:7px;color:#3f4755;font-size:.68rem;font-weight:900}.wizard-grid label>small{display:block;margin-top:5px;color:#8a929f;font-size:.6rem}.wizard-grid input,.wizard-grid textarea,.wizard-grid select,.wizard-task input,.wizard-task textarea,.wizard-task select{width:100%;border:1px solid #dfe3e9;border-radius:10px;background:#fff;padding:11px 12px;color:#303846;font-size:.72rem;outline:0}.wizard-grid input:focus,.wizard-grid textarea:focus,.wizard-grid select:focus,.wizard-task input:focus,.wizard-task textarea:focus,.wizard-task select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}.wizard-grid textarea{resize:vertical}.wizard-grid fieldset{display:flex;align-items:end;gap:10px}.wizard-grid legend{width:100%}.wizard-check input{position:absolute;opacity:0}.wizard-check span{margin:0!important;padding:10px 13px;border:1px solid #e1e4e8;border-radius:10px;cursor:pointer}.wizard-check input:checked+span{border-color:#6366f1;background:#eef2ff;color:#4338ca}.wizard-tasks-heading{display:flex;justify-content:space-between;align-items:center;gap:15px}.wizard-tasks-heading>button{padding:10px 13px;border:0;border-radius:9px;background:#eef2ff;color:#4338ca;font-size:.67rem;font-weight:900;cursor:pointer}.wizard-empty{margin-top:20px;padding:32px;display:grid;place-items:center;text-align:center;border:1px dashed #cdd3dc;border-radius:13px;color:#8a929f}.wizard-empty i{font-size:1.5rem;color:#a5adba}.wizard-empty strong{margin:8px 0 3px;color:#555e6d;font-size:.78rem}.wizard-empty span{font-size:.65rem}.wizard-task{margin-top:14px;padding:17px;border:1px solid #e3e6eb;border-radius:13px;background:#fbfcfe}.wizard-task-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px}.wizard-task-head strong{font-size:.75rem}.wizard-task-head button{border:0;background:#fff0f0;color:#dc2626;width:30px;height:30px;border-radius:8px;cursor:pointer}.wizard-task-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px}.wizard-task-grid label{font-size:.6rem;font-weight:800;color:#606a78}.wizard-task-grid label input,.wizard-task-grid label textarea,.wizard-task-grid label select{margin-top:5px}.wizard-task-grid .task-wide{grid-column:span 2}.wizard-task-approval{display:flex;align-items:center;gap:8px;padding-top:22px!important}.wizard-task-approval input{width:auto}.wizard-footer{margin-top:24px;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;gap:20px;border:1px solid #e2e5ea;border-radius:16px;background:#fff;box-shadow:0 9px 25px rgba(40,50,70,.07)}.wizard-footer>div{display:flex;align-items:center;gap:12px}.wizard-footer #mode-result{font-size:.67rem;color:#657080}.wizard-footer a{color:#64748b;font-size:.7rem;font-weight:800;text-decoration:none}.wizard-footer button{padding:12px 16px;border:0;border-radius:10px;background:#4f46e5;color:#fff;font-size:.72rem;font-weight:900;cursor:pointer}.wizard-loading{position:fixed;z-index:9999;inset:0;background:rgba(15,23,42,.62);backdrop-filter:blur(4px);place-items:center}.wizard-loading:not([hidden]){display:grid}.wizard-loading>div{width:min(420px,90vw);padding:34px;text-align:center;border-radius:18px;background:#fff}.wizard-loading strong,.wizard-loading p{display:block}.wizard-loading strong{margin:15px 0 5px}.wizard-loading p{font-size:.7rem;color:#788191}.wizard-spinner{width:44px;height:44px;display:inline-block;border:4px solid #e0e7ff;border-top-color:#4f46e5;border-radius:50%;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
.wizard-readiness>article>i{background:#059669;color:#fff;box-shadow:0 7px 16px rgba(5,150,105,.22)}
.wizard-readiness .is-current>i{background:#638522;color:#fff;box-shadow:0 7px 16px rgba(99,133,34,.24)}
.campaign-wizard-page{background:#fff}
.campaign-creation-timeline{width:calc(100% - 48px);max-width:1380px;margin:24px auto 0;padding:18px 22px;display:grid;grid-template-columns:repeat(4,1fr);border:1px solid #e2e5ea;border-radius:16px;background:#fff;box-shadow:0 8px 25px rgba(40,50,70,.05)}
.campaign-creation-timeline button{position:relative;display:flex;align-items:center;gap:10px;padding:0 14px;border:0;background:transparent;color:#9aa1ad;text-align:left;cursor:default}
.campaign-creation-timeline button:not(:last-child):after{content:'';position:absolute;z-index:0;left:56px;right:-14px;top:18px;height:2px;background:#e5e7eb}
.campaign-creation-timeline button>span{position:relative;z-index:1;width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border:2px solid #dfe3e8;border-radius:50%;background:#fff;color:#98a0ac;font-size:.68rem;font-weight:900;transition:.2s}
.campaign-creation-timeline button>div{position:relative;z-index:1;min-width:0;background:#fff;padding-right:8px}
.campaign-creation-timeline small,.campaign-creation-timeline strong{display:block}.campaign-creation-timeline small{font-size:.55rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.campaign-creation-timeline strong{margin-top:2px;color:#737c89;font-size:.67rem}
.campaign-creation-timeline button.is-active>span{border-color:#638522;background:#638522;color:#fff;box-shadow:0 6px 14px rgba(99,133,34,.25)}.campaign-creation-timeline button.is-active small,.campaign-creation-timeline button.is-active strong{color:#638522}
.campaign-creation-timeline button.is-complete{cursor:pointer}.campaign-creation-timeline button.is-complete>span{border-color:#059669;background:#059669;color:#fff}.campaign-creation-timeline button.is-complete:not(:last-child):after{background:#86c59f}.campaign-creation-timeline button.is-complete strong{color:#374151}
.wizard-step{display:none}.wizard-step.is-active{display:block}.wizard-mode-section.wizard-step.is-active,.wizard-panel.wizard-step.is-active{animation:wizardStepIn .25s ease both}@keyframes wizardStepIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.wizard-footer-actions{margin-left:auto}.wizard-button-secondary{border:1px solid #dfe3e8!important;background:#fff!important;color:#596273!important}.wizard-button-secondary:hover{border-color:#638522!important;color:#638522!important}.wizard-footer button[hidden]{display:none}
.custom-dropdown{position:relative;width:100%;margin-top:5px}.custom-dropdown-native{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;opacity:0!important;pointer-events:none!important}.custom-dropdown-trigger{width:100%;min-height:42px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid #dfe3e9;border-radius:10px;background:#fff;color:#303846;font-size:.72rem;text-align:left;cursor:pointer;transition:.18s}.custom-dropdown-trigger:hover,.custom-dropdown.is-open .custom-dropdown-trigger{border-color:#638522;box-shadow:0 0 0 3px rgba(99,133,34,.12)}.custom-dropdown-trigger.is-placeholder{color:#8a929f}.custom-dropdown-trigger i{color:#7d8794;font-size:.62rem;transition:.18s}.custom-dropdown.is-open .custom-dropdown-trigger i{transform:rotate(180deg)}
.custom-dropdown-menu{position:absolute;z-index:80;left:0;right:0;top:calc(100% + 6px);max-height:220px;overflow:auto;padding:6px;border:1px solid #e0e4e9;border-radius:11px;background:#fff;box-shadow:0 14px 32px rgba(31,41,55,.16)}.custom-dropdown-menu[hidden]{display:none}.custom-dropdown-option{width:100%;display:flex;align-items:center;justify-content:space-between;padding:10px;border:0;border-radius:8px;background:#fff;color:#3b4452;font-size:.7rem;text-align:left;cursor:pointer}.custom-dropdown-option:hover,.custom-dropdown-option.is-selected{background:#f1f7e8;color:#58751f}.custom-dropdown-option.is-selected:after{content:'\2713';font-weight:900}.custom-dropdown-option[disabled]{color:#a6adb7;cursor:default}
.campaign-creation-timeline{max-width:1050px;margin-top:38px;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
.campaign-creation-timeline button{flex-direction:column;justify-content:flex-start;gap:10px;padding:0;text-align:center}
.campaign-creation-timeline button:not(:last-child):after{left:calc(50% + 18px);right:calc(-50% + 18px);top:17px;height:3px;background:#dfe4e1}
.campaign-creation-timeline button>div{padding:0;background:transparent}.campaign-creation-timeline small{color:#8c9591;font-size:.58rem}.campaign-creation-timeline strong{margin-top:4px;color:#606963;font-size:.7rem}
.wizard-mode-section,.wizard-panel{max-width:1120px;margin-top:28px;padding:22px 0 8px;border:0;background:transparent;box-shadow:none}
.wizard-mode-heading{justify-content:center;text-align:center}.wizard-mode-heading>div{max-width:720px}.wizard-mode-heading h2{color:#202a24;font-size:clamp(1.55rem,3vw,2.15rem);letter-spacing:-.035em}.wizard-mode-heading p{margin-top:10px;color:#6f7873;font-size:.82rem;line-height:1.6}
.wizard-modes{margin-top:30px}.wizard-footer{max-width:1120px;margin-top:12px;padding:14px 0;border:0;border-radius:0;background:transparent;box-shadow:none}.wizard-footer #mode-result{color:#66706a}.wizard-footer #mode-result>i{color:#638522}
.wizard-mode-heading h2{color:#638522}
.wizard-mode:hover,.wizard-mode.is-selected{border-color:#638522;box-shadow:0 10px 25px rgba(99,133,34,.16)}
.wizard-mode>i{background:#f1f7e8;color:#638522}
.wizard-mode.is-selected:after{color:#638522}
.wizard-prefill-note{max-width:760px;margin:22px auto 2px;display:flex;align-items:center;justify-content:center;gap:11px;color:#667064;text-align:left}.wizard-prefill-note>i{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border-radius:50%;background:#638522;color:#fff;font-size:.7rem}.wizard-prefill-note span,.wizard-prefill-note strong{display:block}.wizard-prefill-note span{font-size:.66rem;line-height:1.45}.wizard-prefill-note strong{margin-bottom:2px;color:#44503f;font-size:.68rem}
#wizard-next{background:#ef6c22}.wizard-footer #wizard-next:hover{background:#d85b16;box-shadow:0 7px 16px rgba(239,108,34,.22);transform:translateY(-1px)}
#save-campaign{background:#7c3aed}.wizard-footer #save-campaign:hover{background:#6d28d9;box-shadow:0 7px 16px rgba(124,58,237,.24);transform:translateY(-1px)}
.wizard-grid input:focus,.wizard-grid textarea:focus,.wizard-grid select:focus,.wizard-task input:focus,.wizard-task textarea:focus,.wizard-task select:focus{border-color:#638522;box-shadow:0 0 0 3px rgba(99,133,34,.14)}
.wizard-grid textarea,.wizard-task textarea{overflow:hidden;transition:height .16s ease,border-color .18s,box-shadow .18s}
.wizard-team-recommendation{margin:22px auto 0;text-align:center}.wizard-team-recommendation>button{min-height:40px;padding:0 14px;border:1px solid #638522;border-radius:10px;background:#638522;color:#fff;font-size:.67rem;font-weight:900;cursor:pointer;transition:.18s}.wizard-team-recommendation>button:hover{transform:translateY(-1px);box-shadow:0 7px 16px rgba(99,133,34,.2)}.wizard-team-recommendation>button:disabled{opacity:.65;cursor:wait}.wizard-team-recommendation>div{max-width:780px;margin:12px auto 0;color:#687165;font-size:.64rem;line-height:1.5}.wizard-team-recommendation>div.is-error{color:#b42318}
.wizard-team-layout{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:42px;margin-top:30px}.wizard-team-column{padding-top:18px;border-top:3px solid #e5eadf}.wizard-team-column:first-child{border-color:#638522}.wizard-team-column:last-child{border-color:#ef6c22}.wizard-team-column-head{min-height:42px;display:flex;align-items:center;gap:11px;margin-bottom:20px}.wizard-team-column-head>span{width:38px;height:38px;display:grid;place-items:center;flex:0 0 38px;border-radius:50%;background:#638522;color:#fff}.wizard-team-column:last-child .wizard-team-column-head>span{background:#ef6c22}.wizard-team-column-head>div{min-width:0;flex:1}.wizard-team-column-head strong,.wizard-team-column-head small{display:block}.wizard-team-column-head strong{color:#35402f;font-size:.78rem}.wizard-team-column-head small{margin-top:3px;color:#7d857a;font-size:.61rem;line-height:1.4}.wizard-team-column>label,.wizard-designer-row label{display:block}.wizard-team-column>label>span,.wizard-designer-row label>span{display:block;margin-bottom:7px;color:#3f4755;font-size:.68rem;font-weight:900}.wizard-team-column-head>button{min-height:34px;padding:0 10px;border:1px solid #ef6c22;border-radius:8px;background:#fff;color:#d85b16;font-size:.6rem;font-weight:900;cursor:pointer}.wizard-team-column-head>button:hover{background:#ef6c22;color:#fff}
.wizard-designer-row{display:grid;grid-template-columns:minmax(0,1fr) 36px;align-items:end;gap:9px;margin-top:12px}.wizard-designer-row:first-child{margin-top:0}.remove-designer{width:36px;height:42px;border:1px solid #f0d6d2;border-radius:9px;background:#fff;color:#c43d32;cursor:pointer}.remove-designer:hover{background:#fff1ef}.wizard-designers-empty{padding:14px 0;color:#8a9288;font-size:.63rem;line-height:1.5}.wizard-designers-empty[hidden]{display:none}
.wizard-tasks-heading{display:block;text-align:center}.wizard-tasks-heading .wizard-mode-heading{justify-content:center}.wizard-task-source{margin:24px 0 8px;padding:15px 0;display:flex;align-items:center;justify-content:space-between;gap:18px;border-top:1px solid #e5eadf;border-bottom:1px solid #e5eadf}.wizard-task-source>div{display:flex;align-items:center;gap:10px}.wizard-task-source>div:first-child{min-width:0}.wizard-task-source>div:first-child>i{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:50%;background:#638522;color:#fff;font-size:.72rem}.wizard-task-source span,.wizard-task-source strong{display:block}.wizard-task-source span{color:#717a6e;font-size:.64rem;line-height:1.45}.wizard-task-source strong{margin-bottom:2px;color:#3f4a39;font-size:.7rem}.wizard-task-source button{min-height:38px;padding:0 11px;border:1px solid #638522;border-radius:9px;background:#fff;color:#638522;font-size:.59rem;font-weight:900;white-space:nowrap;cursor:pointer}.wizard-task-source #add-task{border-color:#ef6c22;background:#ef6c22;color:#fff}.wizard-task-source button:hover{transform:translateY(-1px);box-shadow:0 6px 14px rgba(75,94,57,.13)}
.wizard-task-grid .task-full{grid-column:1/-1}.task-assignees{margin:2px 0 0;padding:0;border:0}.task-assignees legend{margin-bottom:9px;color:#4d5749;font-size:.64rem;font-weight:900}.task-assignees legend small{margin-left:5px;color:#8a9287;font-size:.57rem;font-weight:600}.task-assignees>div{display:flex;flex-wrap:wrap;gap:8px}.task-assignees>div>p{margin:0;color:#8a9287;font-size:.62rem}.task-person-option{position:relative}.task-person-option input{position:absolute!important;width:1px!important;height:1px!important;opacity:0}.task-person-option span{min-height:40px;display:flex!important;align-items:flex-start;justify-content:center;flex-direction:column;padding:7px 12px!important;border:1px solid #dfe4dc;border-radius:9px;background:#fff;color:#566052;cursor:pointer;transition:.16s}.task-person-option span b{font-size:.62rem}.task-person-option span small{margin:2px 0 0;color:#8b9388;font-size:.51rem}.task-person-option input:checked+span{border-color:#638522;background:#f1f7e8;color:#526f1c;box-shadow:inset 0 -2px 0 #638522}.task-person-option input:focus+span{box-shadow:0 0 0 3px rgba(99,133,34,.14)}
.wizard-task-grid .task-three{grid-column:span 3}.task-content-types{margin:0;padding:0;border:0}.task-content-types>legend{margin-bottom:9px;color:#4d5749;font-size:.64rem;font-weight:900}.task-content-types>div{display:flex;flex-wrap:wrap;gap:7px}.task-content-types>div label{position:relative}.task-content-types>div input{position:absolute!important;width:1px!important;height:1px!important;opacity:0}.task-content-types>div span{min-height:36px;display:inline-flex!important;align-items:center;gap:6px;margin:0!important;padding:0 11px!important;border:1px solid #dfe4dc;border-radius:999px;background:#fff;color:#626b5e;font-size:.58rem;font-weight:850;cursor:pointer;transition:.16s}.task-content-types>div input:checked+span{border-color:#638522;background:#638522;color:#fff;box-shadow:0 5px 12px rgba(99,133,34,.2)}.task-content-types>div input:focus+span{box-shadow:0 0 0 3px rgba(99,133,34,.16)}.task-other-type{max-width:390px;display:block;margin-top:12px}.task-other-type[hidden]{display:none}.task-other-type>span{display:block;margin-bottom:6px;color:#4d5749;font-size:.61rem;font-weight:850}
.task-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:-2px}.task-custom-check{position:relative;min-height:58px;display:flex!important;align-items:center;gap:10px;padding:10px 12px!important;border:1px solid #e0e4de;border-radius:10px;background:#fff;cursor:pointer;transition:.16s}.task-custom-check>input[type=checkbox]{position:absolute!important;width:1px!important;height:1px!important;opacity:0}.task-custom-check>span{width:22px;height:22px;display:grid!important;place-items:center;flex:0 0 22px;margin:0!important;border:2px solid #cfd5cb;border-radius:6px;background:#fff;color:transparent;font-size:.55rem;transition:.16s}.task-custom-check>div{min-width:0}.task-custom-check strong,.task-custom-check small{display:block}.task-custom-check strong{color:#4b5547;font-size:.63rem}.task-custom-check small{margin-top:3px;color:#899186;font-size:.53rem;line-height:1.35}.task-custom-check:has(input[type=checkbox]:checked){border-color:#a9bf80;background:#f8fbf4}.task-custom-check>input[type=checkbox]:checked+span{border-color:#638522;background:#638522;color:#fff}.task-custom-check>input[type=checkbox]:focus+span{box-shadow:0 0 0 3px rgba(99,133,34,.14)}
@media(max-width:900px){.wizard-readiness,.wizard-modes{grid-template-columns:1fr 1fr}.wizard-task-grid{grid-template-columns:1fr 1fr}.campaign-wizard-hero-content{padding:30px 24px}.wizard-grid{grid-template-columns:1fr}.wizard-grid .is-full{grid-column:auto}}@media(max-width:620px){.wizard-readiness,.wizard-modes,.wizard-task-grid{grid-template-columns:1fr}.wizard-readiness article{border-right:0;border-bottom:1px solid #eceef2}.wizard-task-grid .task-wide{grid-column:auto}.wizard-footer,.campaign-wizard-hero-content{align-items:flex-start;flex-direction:column}.campaign-wizard-identity{align-items:flex-start}.wizard-footer>div{flex-wrap:wrap}}
@media(max-width:900px){.campaign-creation-timeline{grid-template-columns:repeat(4,minmax(110px,1fr));overflow-x:auto}.campaign-creation-timeline button{padding:0 8px}.campaign-creation-timeline strong{white-space:nowrap}.wizard-footer-actions{width:100%;justify-content:flex-end}}
@media(max-width:620px){.campaign-creation-timeline{width:calc(100% - 28px);padding:14px 10px;grid-template-columns:repeat(4,54px);justify-content:space-between;overflow:visible}.campaign-creation-timeline button{justify-content:center;padding:0}.campaign-creation-timeline button>div{display:none}.campaign-creation-timeline button:not(:last-child):after{left:36px;right:-18px}.wizard-mode-section,.wizard-panel,.wizard-footer{width:calc(100% - 28px);padding:18px}.wizard-footer-actions{justify-content:stretch}.wizard-footer-actions a{width:100%;text-align:center}.wizard-footer-actions button{flex:1}.wizard-footer #mode-result{line-height:1.45}}
.wizard-audiences{padding:16px;border:1px solid #e1e5eb;border-radius:13px;background:#fafbfe}.wizard-audiences-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:12px}.wizard-audiences-head span,.wizard-audiences-head small{display:block}.wizard-audiences-head span{color:#3f4755;font-size:.72rem;font-weight:900}.wizard-audiences-head small{margin-top:3px;color:#8a929f;font-size:.6rem}.wizard-audiences-head button{padding:9px 12px;border:0;border-radius:9px;background:#eef2ff;color:#4338ca;font-size:.64rem;font-weight:900;cursor:pointer}.wizard-audience-row{display:grid;grid-template-columns:minmax(220px,.7fr) minmax(280px,1.3fr) auto;align-items:start;gap:10px;padding:12px;border:1px solid #e3e6eb;border-radius:11px;background:#fff}.wizard-audience-row+.wizard-audience-row{margin-top:10px}.wizard-audience-row label span{display:block;margin-bottom:6px;color:#606a78;font-size:.61rem;font-weight:900}.wizard-audience-row textarea{min-height:72px;resize:vertical}.wizard-audience-remove{width:36px;height:36px;margin-top:22px;border:0;border-radius:9px;background:#fff0f0;color:#dc2626;cursor:pointer}
@media(max-width:760px){.wizard-team-layout{grid-template-columns:1fr;gap:28px}.wizard-team-column-head{align-items:flex-start;flex-wrap:wrap}.wizard-team-column-head>button{margin-left:49px}.wizard-task-source{align-items:flex-start;flex-direction:column}.wizard-task-source>div:last-child{width:100%;flex-wrap:wrap}.wizard-task-source button{flex:1}.wizard-audience-row{grid-template-columns:1fr}.wizard-audience-remove{width:100%;margin-top:0}.wizard-audiences-head{align-items:flex-start;flex-direction:column}}
@media(max-width:900px){.wizard-task-grid .task-three{grid-column:1/-1}}@media(max-width:620px){.task-options{grid-template-columns:1fr}.wizard-task-grid .task-three{grid-column:auto}.task-content-types>div span{min-height:34px;padding:0 9px!important}}
.wizard-confirm-modal{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:20px}.wizard-confirm-modal[hidden]{display:none}.wizard-confirm-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.68);backdrop-filter:blur(4px)}.wizard-confirm-card{position:relative;width:min(500px,100%);padding:28px;border:1px solid #fed7aa;border-radius:18px;background:#fff;box-shadow:0 28px 75px rgba(15,23,42,.3)}.wizard-confirm-icon{width:48px;height:48px;display:grid;place-items:center;margin-bottom:15px;border-radius:14px;background:#fff7ed;color:#ea580c;font-size:1.2rem}.wizard-confirm-card small{color:#ea580c;font-size:.59rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.wizard-confirm-card h2{margin:4px 0 0;color:#1f2937;font-size:1.15rem}.wizard-confirm-card p{margin:16px 0 22px;color:#64748b;font-size:.73rem;line-height:1.65}.wizard-confirm-actions{display:flex;justify-content:flex-end;gap:9px}.wizard-confirm-actions button{min-height:42px;padding:0 15px;border-radius:10px;font-size:.68rem;font-weight:900;cursor:pointer}.wizard-confirm-cancel{border:1px solid #dbe1e8;background:#fff;color:#64748b}.wizard-confirm-accept{border:1px solid #dc2626;background:#dc2626;color:#fff}
</style>

<script>
function initializeCampaignWizard() {
    const isEditing = @json($isEditing);
    const modeInput = document.getElementById('modo_creacion');
    const tasksContainer = document.getElementById('tasks-container');
    const tasksEmpty = document.getElementById('tasks-empty');
    const loading = document.getElementById('ai-loading');
    const form = document.getElementById('advanced-campaign-form');
    const saveButton = document.getElementById('save-campaign');
    const nextButton = document.getElementById('wizard-next');
    const previousButton = document.getElementById('wizard-previous');
    const wizardSteps = [...document.querySelectorAll('[data-wizard-step]')];
    const timelineSteps = [...document.querySelectorAll('[data-go-step]')];
    const designersContainer = document.getElementById('designers-container');
    const designersEmpty = document.getElementById('designers-empty');
    const audiencesContainer = document.getElementById('audiences-container');
    const designersCatalog = @json($disenadores->map(fn ($designer) => ['id' => $designer->id, 'name' => $designer->name])->values());
    const administrator = @json($campaignAdministrator);
    const result = document.getElementById('mode-result').querySelector('span');
    const prefillNote = document.getElementById('prefill-note');
    const loadingTitle = document.getElementById('generation-loading-title');
    const loadingCopy = document.getElementById('generation-loading-copy');
    const modeChangeModal = document.getElementById('mode-change-modal');
    const planTasks = @json($tareasIniciales ?? []);
    let tasks = @json($campaignTasks);
    let audiences = @json($campaignAudiences);
    const initialDesignerIds = @json($campaignDesignerIds);
    let currentStep = 0;
    let maximumReachedStep = 0;

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const allowedDate = value => /^\d{4}-\d{2}-\d{2}$/.test(value ?? '') ? value : '{{ $fechaInicio }}';

    function renderAudiences() {
        if (!Array.isArray(audiences) || audiences.length === 0) {
            audiences = [{tipo_edades:'', descripcion:''}];
        }
        audiencesContainer.innerHTML = audiences.map((audience, index) => `
            <article class="wizard-audience-row">
                <label><span>Tipo de público + edades *</span><input name="publicos_objetivo[${index}][tipo_edades]" value="${escapeHtml(audience.tipo_edades)}" maxlength="120" required placeholder="Ej.: Adultos planificadores (35-55 años)"></label>
                <label><span>Descripción esencial (opcional)</span><textarea name="publicos_objetivo[${index}][descripcion]" maxlength="600" rows="2" placeholder="Necesidad, motivación u objeción principal...">${escapeHtml(audience.descripcion)}</textarea></label>
                <button type="button" class="wizard-audience-remove" data-remove-audience="${index}" aria-label="Eliminar público" ${audiences.length === 1 ? 'disabled' : ''}><i class="fas fa-trash"></i></button>
            </article>`).join('');
    }

    function readAudiences() {
        audiences = [...audiencesContainer.querySelectorAll('.wizard-audience-row')].map(row => ({
            tipo_edades: row.querySelector('[name$="[tipo_edades]"]').value,
            descripcion: row.querySelector('[name$="[descripcion]"]').value,
        }));
    }

    function resizeTextarea(textarea) {
        if (!textarea || textarea.offsetParent === null) return;
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.max(textarea.scrollHeight, 82)}px`;
    }

    function resizeVisibleTextareas(scope = document) {
        scope.querySelectorAll('textarea').forEach(resizeTextarea);
    }

    function closeCustomDropdowns(except = null) {
        document.querySelectorAll('.custom-dropdown.is-open').forEach(dropdown => {
            if (dropdown === except) return;
            dropdown.classList.remove('is-open');
            dropdown.querySelector('.custom-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
            const menu = dropdown.querySelector('.custom-dropdown-menu');
            if (menu) menu.hidden = true;
        });
    }

    function syncCustomSelect(select) {
        const dropdown = select.closest('.custom-dropdown');
        if (!dropdown) return;
        const selected = select.options[select.selectedIndex] || select.options[0];
        const trigger = dropdown.querySelector('.custom-dropdown-trigger');
        trigger.querySelector('span').textContent = selected?.textContent?.trim() || 'Selecciona una opción';
        trigger.classList.toggle('is-placeholder', !select.value);
        dropdown.querySelectorAll('.custom-dropdown-option').forEach(option => option.classList.toggle('is-selected', option.dataset.value === select.value));
    }

    function enhanceCustomSelects(scope = document) {
        scope.querySelectorAll('select:not([data-customized])').forEach(select => {
            select.dataset.customized = 'true';
            const dropdown = document.createElement('div');
            dropdown.className = 'custom-dropdown';
            select.parentNode.insertBefore(dropdown, select);
            dropdown.appendChild(select);
            select.classList.add('custom-dropdown-native');

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'custom-dropdown-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.innerHTML = '<span></span><i class="fas fa-chevron-down"></i>';

            const menu = document.createElement('div');
            menu.className = 'custom-dropdown-menu';
            menu.setAttribute('role', 'listbox');
            menu.hidden = true;
            [...select.options].forEach(nativeOption => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'custom-dropdown-option';
                option.dataset.value = nativeOption.value;
                option.textContent = nativeOption.textContent.trim();
                option.disabled = nativeOption.disabled;
                option.addEventListener('click', () => {
                    select.value = option.dataset.value;
                    select.dispatchEvent(new Event('change', {bubbles:true}));
                    syncCustomSelect(select);
                    closeCustomDropdowns();
                });
                menu.appendChild(option);
            });
            dropdown.append(trigger, menu);
            trigger.addEventListener('click', () => {
                const opening = !dropdown.classList.contains('is-open');
                closeCustomDropdowns(dropdown);
                dropdown.classList.toggle('is-open', opening);
                trigger.setAttribute('aria-expanded', String(opening));
                menu.hidden = !opening;
            });
            select.addEventListener('change', () => syncCustomSelect(select));
            syncCustomSelect(select);
        });
    }

    function selectedDesignerIds() {
        return [...designersContainer.querySelectorAll('select[name="disenadores_ids[]"]')]
            .map(select => select.value)
            .filter(Boolean);
    }

    function addDesignerSelection(value = '') {
        if (designersCatalog.length === 0) {
            designersEmpty.textContent = 'No hay usuarios con el rol Diseñador disponibles.';
            designersEmpty.hidden = false;
            return null;
        }
        if (designersContainer.children.length >= designersCatalog.length) return null;

        const row = document.createElement('div');
        row.className = 'wizard-designer-row';
        row.innerHTML = `<label><span>Diseñador</span><select name="disenadores_ids[]"><option value="">Selecciona un diseñador</option>${designersCatalog.map(designer => `<option value="${designer.id}" ${String(designer.id) === String(value) ? 'selected' : ''}>${escapeHtml(designer.name)}</option>`).join('')}</select></label><button type="button" class="remove-designer" aria-label="Quitar diseñador"><i class="fas fa-trash"></i></button>`;
        designersContainer.appendChild(row);
        enhanceCustomSelects(row);
        const select = row.querySelector('select');
        let previousValue = select.value;
        select.addEventListener('change', () => {
            const repeated = select.value && [...designersContainer.querySelectorAll('select[name="disenadores_ids[]"]')]
                .some(other => other !== select && other.value === select.value);
            if (repeated) {
                alert('Este diseñador ya forma parte del equipo.');
                select.value = previousValue;
                syncCustomSelect(select);
                return;
            }
            previousValue = select.value;
        });
        row.querySelector('.remove-designer').addEventListener('click', () => {
            row.remove();
            designersEmpty.hidden = designersContainer.children.length > 0;
        });
        designersEmpty.hidden = true;

        return select;
    }

    function setDesignerSelections(ids) {
        designersContainer.innerHTML = '';
        [...new Set((ids ?? []).map(String).filter(Boolean))].forEach(addDesignerSelection);
        designersEmpty.hidden = designersContainer.children.length > 0;
    }

    function validateCurrentStep() {
        const invalidField = [...wizardSteps[currentStep].querySelectorAll('input, textarea, select')].find(field => !field.checkValidity());
        if (!invalidField) return true;
        invalidField.reportValidity();
        invalidField.closest('.custom-dropdown')?.querySelector('.custom-dropdown-trigger')?.focus();
        return false;
    }

    function showStep(step) {
        if (currentStep === 3 && tasksContainer.children.length > 0) readTasks();
        currentStep = Math.max(0, Math.min(step, wizardSteps.length - 1));
        maximumReachedStep = Math.max(maximumReachedStep, currentStep);
        wizardSteps.forEach((panel, index) => panel.classList.toggle('is-active', index === currentStep));
        timelineSteps.forEach((item, index) => {
            item.classList.toggle('is-active', index === currentStep);
            item.classList.toggle('is-complete', index < currentStep);
            item.disabled = index > maximumReachedStep;
        });
        previousButton.hidden = currentStep === 0;
        nextButton.hidden = currentStep === wizardSteps.length - 1;
        saveButton.hidden = currentStep !== wizardSteps.length - 1;
        if (currentStep === 3) refreshTaskAssignees();
        requestAnimationFrame(() => resizeVisibleTextareas(wizardSteps[currentStep]));
        document.querySelector('.campaign-creation-timeline')?.scrollIntoView({behavior:'smooth', block:'nearest'});
    }

    function campaignTeam() {
        const team = [administrator];
        const managerSelect = document.getElementById('community_manager_id');
        if (managerSelect.value) {
            team.push({id:Number(managerSelect.value), name:managerSelect.options[managerSelect.selectedIndex].text.trim(), role:'Community Manager'});
        }
        selectedDesignerIds().forEach(id => {
            const designer = designersCatalog.find(item => String(item.id) === String(id));
            if (designer) team.push({...designer, role:'Diseñador'});
        });

        return team.filter((person, index, people) => people.findIndex(item => String(item.id) === String(person.id)) === index);
    }

    function renderTaskAssignees(item, task, index) {
        const holder = item.querySelector('[data-task-assignees]');
        if (!holder) return;
        const hasExplicitSelection = Object.prototype.hasOwnProperty.call(task, 'responsables_ids');
        const selectedIds = (task.responsables_ids ?? []).map(String);
        const suggestedRoles = task.roles_sugeridos ?? [task.rol_sugerido ?? 'Community Manager'];
        const team = campaignTeam();
        holder.innerHTML = team.length
            ? team.map(person => {
                const checked = selectedIds.includes(String(person.id)) || (!hasExplicitSelection && suggestedRoles.includes(person.role));
                return `<label class="task-person-option"><input type="checkbox" name="tareas[${index}][responsables_ids][]" value="${person.id}" ${checked ? 'checked' : ''}><span><b>${escapeHtml(person.name)}</b><small>${escapeHtml(person.role)}</small></span></label>`;
            }).join('')
            : '<p>Selecciona primero el equipo responsable de la campaña.</p>';
    }

    function refreshTaskAssignees() {
        [...tasksContainer.querySelectorAll('.wizard-task')].forEach((item, index) => renderTaskAssignees(item, tasks[index] ?? {}, index));
    }

    function renderTasks() {
        tasksEmpty.hidden = tasks.length > 0;
        tasksContainer.innerHTML = '';
        tasks.forEach((task, index) => {
            const item = document.createElement('article');
            item.className = 'wizard-task';
            const knownContentTypes = ['post', 'reel', 'historia', 'carrusel', 'guion'];
            const selectedContentType = knownContentTypes.includes(task.tipo_contenido) ? task.tipo_contenido : 'otro';
            const customContentType = task.tipo_contenido_otro || (!knownContentTypes.includes(task.tipo_contenido) && task.tipo_contenido !== 'otro' ? task.tipo_contenido : '');
            const contentTypeCapsules = [['post','fa-image'],['reel','fa-circle-play'],['historia','fa-mobile-screen'],['carrusel','fa-images'],['guion','fa-file-pen'],['otro','fa-shapes']]
                .map(([value, icon]) => `<label><input type="radio" name="tareas[${index}][tipo_contenido]" value="${value}" ${selectedContentType === value ? 'checked' : ''} required><span><i class="fas ${icon}"></i>${value.charAt(0).toUpperCase() + value.slice(1)}</span></label>`)
                .join('');
            item.innerHTML = `
                <div class="wizard-task-head"><strong>Tarea ${index + 1}</strong><button type="button" data-remove-task="${index}" aria-label="Eliminar"><i class="fas fa-trash"></i></button></div>
                <div class="wizard-task-grid">
                    ${task.id ? `<input type="hidden" name="tareas[${index}][id]" value="${escapeHtml(task.id)}">` : ''}
                    <label class="task-three">Título *<input name="tareas[${index}][titulo]" value="${escapeHtml(task.titulo)}" required maxlength="100"></label>
                    <label>Prioridad<select name="tareas[${index}][prioridad]"><option value="baja" ${task.prioridad === 'baja' ? 'selected' : ''}>Baja</option><option value="media" ${!task.prioridad || task.prioridad === 'media' ? 'selected' : ''}>Media</option><option value="alta" ${task.prioridad === 'alta' ? 'selected' : ''}>Alta</option><option value="urgente" ${task.prioridad === 'urgente' ? 'selected' : ''}>Urgente</option></select></label>
                    <input type="hidden" name="tareas[${index}][rol_sugerido]" value="${escapeHtml(task.rol_sugerido || 'Community Manager')}">
                    <fieldset class="task-content-types task-full"><legend>Tipo de contenido</legend><div>${contentTypeCapsules}</div><label class="task-other-type" ${selectedContentType === 'otro' ? '' : 'hidden'}><span>Especifica el tipo de contenido</span><input name="tareas[${index}][tipo_contenido_otro]" value="${escapeHtml(customContentType)}" maxlength="30" placeholder="Ej.: podcast, infografía"></label></fieldset>
                    <label class="task-wide">Descripción *<textarea name="tareas[${index}][descripcion]" rows="2" required>${escapeHtml(task.descripcion)}</textarea></label>
                    <label class="task-wide">Entregable<textarea name="tareas[${index}][entregable]" rows="2">${escapeHtml(task.entregable)}</textarea></label>
                    <div class="task-options task-full">
                        <label class="task-custom-check"><input type="hidden" name="tareas[${index}][requiere_aprobacion]" value="0"><input type="checkbox" name="tareas[${index}][requiere_aprobacion]" value="1" ${(task.requiere_aprobacion === true || Number(task.requiere_aprobacion) === 1) ? 'checked' : ''}><span><i class="fas fa-check"></i></span><div><strong>Requiere aprobación</strong><small>La pieza deberá validarse antes de publicarse.</small></div></label>
                        <label class="task-custom-check"><input type="hidden" name="tareas[${index}][visible_cliente]" value="0"><input type="checkbox" name="tareas[${index}][visible_cliente]" value="1" ${(task.visible_cliente === true || Number(task.visible_cliente) === 1) ? 'checked' : ''}><span><i class="fas fa-check"></i></span><div><strong>Visible para el cliente</strong><small>El cliente podrá consultar esta tarea.</small></div></label>
                    </div>
                    <label>Inicio<input type="date" name="tareas[${index}][fecha_inicio]" value="${allowedDate(task.fecha_inicio)}" min="{{ $fechaInicio }}" max="{{ $fechaFin }}" required></label>
                    <label>Límite<input type="date" name="tareas[${index}][fecha_limite]" value="${allowedDate(task.fecha_limite || task.fecha_inicio)}" min="{{ $fechaInicio }}" max="{{ $fechaFin }}" required></label>
                    <fieldset class="task-assignees task-full"><legend>Responsables <small>Puedes seleccionar una o más personas del equipo</small></legend><div data-task-assignees></div></fieldset>
                </div>`;
            tasksContainer.appendChild(item);
            const otherType = item.querySelector('.task-other-type');
            const otherTypeInput = otherType.querySelector('input');
            item.querySelectorAll('[name$="[tipo_contenido]"]').forEach(radio => radio.addEventListener('change', () => {
                const isOther = radio.checked && radio.value === 'otro';
                if (!radio.checked) return;
                otherType.hidden = !isOther;
                otherTypeInput.required = isOther;
                if (isOther) otherTypeInput.focus();
            }));
            otherTypeInput.required = selectedContentType === 'otro';
            renderTaskAssignees(item, task, index);
            enhanceCustomSelects(item);
            resizeVisibleTextareas(item);
        });
    }

    function readTasks() {
        tasks = [...tasksContainer.querySelectorAll('.wizard-task')].map((item, index) => ({
            id: item.querySelector('[name$="[id]"]')?.value || null,
            titulo: item.querySelector('[name$="[titulo]"]').value,
            descripcion: item.querySelector('[name$="[descripcion]"]').value,
            entregable: item.querySelector('[name$="[entregable]"]').value,
            fecha_inicio: item.querySelector('[name$="[fecha_inicio]"]').value,
            fecha_limite: item.querySelector('[name$="[fecha_limite]"]').value,
            prioridad: item.querySelector('[name$="[prioridad]"]').value,
            rol_sugerido: item.querySelector('[name$="[rol_sugerido]"]').value,
            roles_sugeridos: tasks[index]?.roles_sugeridos ?? [item.querySelector('[name$="[rol_sugerido]"]').value],
            tipo_contenido: item.querySelector('[name$="[tipo_contenido]"]:checked')?.value || 'post',
            tipo_contenido_otro: item.querySelector('[name$="[tipo_contenido_otro]"]').value,
            responsables_ids: [...item.querySelectorAll('[name*="[responsables_ids]"]:checked')].map(input => input.value),
            requiere_aprobacion: item.querySelector('[name$="[requiere_aprobacion]"][type="checkbox"]')?.checked ?? false,
            visible_cliente: item.querySelector('[name$="[visible_cliente]"][type="checkbox"]')?.checked ?? false,
        }));
    }

    function fillProposal(proposal) {
        ['nombre','descripcion','objetivo_general','mensaje_principal','tono_comunicacion'].forEach(key => {
            document.getElementById(key).value = proposal[key] ?? '';
        });
        audiences = proposal.publicos_objetivo ?? [];
        renderAudiences();
        document.getElementById('indicadores_csv').value = (proposal.indicadores ?? []).join(', ');
        document.querySelectorAll('input[name="canales[]"]').forEach(box => box.checked = (proposal.canales ?? []).includes(box.value));
        if (proposal.community_manager_id) document.getElementById('community_manager_id').value = proposal.community_manager_id;
        syncCustomSelect(document.getElementById('community_manager_id'));
        setDesignerSelections(proposal.disenadores_ids ?? [proposal.disenador_id].filter(Boolean));
        tasks = proposal.tareas ?? [];
        renderTasks();
        prefillNote.hidden = false;
        resizeVisibleTextareas(wizardSteps[currentStep]);
    }

    function clearCampaignForm() {
        ['nombre','descripcion','objetivo_general','mensaje_principal','tono_comunicacion'].forEach(key => {
            document.getElementById(key).value = '';
        });
        audiences = [{tipo_edades:'', descripcion:''}];
        renderAudiences();
        document.getElementById('indicadores_csv').value = '';
        document.querySelectorAll('input[name="canales[]"]').forEach(box => box.checked = false);
        const managerSelect = document.getElementById('community_manager_id');
        managerSelect.value = '';
        syncCustomSelect(managerSelect);
        setDesignerSelections([]);
        tasks = [];
        renderTasks();
        prefillNote.hidden = true;
    }

    async function selectMode(mode, button) {
        const previousMode = modeInput.value;
        readAudiences();
        readTasks();
        document.querySelectorAll('.wizard-mode').forEach(item => item.classList.toggle('is-selected', item === button));
        modeInput.value = mode;
        saveButton.innerHTML = `<i class="fas fa-check"></i> ${isEditing ? 'Guardar cambios' : 'Finalizar y crear campaña'}`;
        result.textContent = mode === 'automatico'
            ? 'El sistema completará la campaña y distribuirá el trabajo sin utilizar inteligencia artificial.'
            : mode === 'asistido'
                ? 'Revisa y edita la propuesta de IA; al guardar se activará la campaña.'
                : 'El modo manual comienza con todos los campos vacíos.';
        if (mode === 'manual') {
            clearCampaignForm();
            return;
        }

        loadingTitle.textContent = mode === 'automatico'
            ? 'El sistema está preparando la campaña'
            : 'La IA está construyendo la campaña';
        loadingCopy.textContent = mode === 'automatico'
            ? 'Aplicamos el cuestionario, el plan, el calendario y la carga del equipo sin consultar servicios de IA.'
            : 'La inteligencia artificial analiza el brief, el plan contratado, el calendario y la carga del equipo.';

        loading.hidden = false;
        try {
            const response = await fetch(@json(route('administrador.campañas.propuesta-ia', $suscripcion)), {
                method: 'POST',
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body: JSON.stringify({modo: mode, campania_id: @json($editingCampaignId)})
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo generar la propuesta.');
            fillProposal(data.proposal);
            result.textContent = mode === 'automatico'
                ? 'Campaña automática preparada sin IA: estrategia, equipo, tareas y responsables están listos para revisar.'
                : 'Propuesta asistida preparada. Puedes revisar los campos antes de crear la campaña.';
        } catch (error) {
            alert(error.message);
            modeInput.value = isEditing ? previousMode : 'manual';
            document.querySelectorAll('.wizard-mode').forEach(item => item.classList.toggle('is-selected', item.dataset.mode === modeInput.value));
            saveButton.innerHTML = `<i class="fas fa-check"></i> ${isEditing ? 'Guardar cambios' : 'Finalizar y crear campaña'}`;
            result.textContent = 'La propuesta no pudo generarse. Puedes continuar manualmente o volver a intentarlo.';
        } finally { loading.hidden = true; }
    }

    window.applyCampaignModeChange = selectMode;
    nextButton.addEventListener('click', () => {
        if (!validateCurrentStep()) return;
        showStep(currentStep + 1);
    });
    previousButton.addEventListener('click', () => showStep(currentStep - 1));
    timelineSteps.forEach(button => button.addEventListener('click', () => {
        const requestedStep = Number(button.dataset.goStep);
        if (requestedStep <= maximumReachedStep) showStep(requestedStep);
    }));
    document.addEventListener('click', event => {
        if (!event.target.closest('.custom-dropdown')) closeCustomDropdowns();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeCustomDropdowns();
        if (event.key === 'Escape' && modeChangeModal && !modeChangeModal.hidden) closeCampaignModeModal();
    });
    form.addEventListener('input', event => {
        if (event.target.matches('textarea')) resizeTextarea(event.target);
    });
    document.getElementById('add-designer').addEventListener('click', () => addDesignerSelection());
    document.getElementById('add-audience').addEventListener('click', () => {
        readAudiences();
        if (audiences.length >= 10) return;
        audiences.push({tipo_edades:'', descripcion:''});
        renderAudiences();
        audiencesContainer.querySelector('.wizard-audience-row:last-child input')?.focus();
    });
    audiencesContainer.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-audience]');
        if (!button) return;
        readAudiences();
        audiences.splice(Number(button.dataset.removeAudience), 1);
        renderAudiences();
    });
    document.getElementById('recommend-team').addEventListener('click', async event => {
        const button = event.currentTarget;
        const recommendationResult = document.getElementById('team-recommendation-result');
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analizando carga';
        recommendationResult.hidden = true;
        recommendationResult.classList.remove('is-error');

        try {
            const response = await fetch(button.dataset.url, {
                headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No se pudo recomendar el equipo.');

            const managerSelect = document.getElementById('community_manager_id');
            managerSelect.value = String(data.recommended.id);
            managerSelect.dispatchEvent(new Event('change', {bubbles:true}));
            syncCustomSelect(managerSelect);

            if (data.recommended_designer && !selectedDesignerIds().includes(String(data.recommended_designer.id))) {
                const blankDesigner = [...designersContainer.querySelectorAll('select[name="disenadores_ids[]"]')].find(select => !select.value);
                const designerSelect = blankDesigner || addDesignerSelection(data.recommended_designer.id);
                if (designerSelect) {
                    designerSelect.value = String(data.recommended_designer.id);
                    designerSelect.dispatchEvent(new Event('change', {bubbles:true}));
                    syncCustomSelect(designerSelect);
                }
            }

            const designerCopy = data.recommended_designer
                ? ` Diseño: ${data.recommended_designer.name}. ${data.recommended_designer.reason}`
                : ' No hay diseñadores disponibles para recomendar.';
            recommendationResult.textContent = `Community Manager: ${data.recommended.name}. ${data.recommended.reason}${designerCopy}`;
            recommendationResult.hidden = false;
        } catch (error) {
            recommendationResult.textContent = error.message || 'No se pudo recomendar el equipo.';
            recommendationResult.classList.add('is-error');
            recommendationResult.hidden = false;
        } finally {
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    });
    document.getElementById('add-task').addEventListener('click', () => {
        readTasks();
        tasks.push({fecha_inicio:'{{ $fechaInicio }}',fecha_limite:'{{ $fechaFin }}',prioridad:'media',tipo_contenido:'post',rol_sugerido:'Community Manager',roles_sugeridos:['Community Manager'],requiere_aprobacion:false,visible_cliente:false});
        renderTasks();
    });
    document.getElementById('restore-plan-tasks').addEventListener('click', () => {
        if (planTasks.length === 0) {
            alert('El plan de marketing no contiene un calendario operativo que pueda convertirse en tareas.');
            return;
        }
        readTasks();
        if (tasks.length > 0 && !confirm('Se reemplazarán las tareas actuales por las del calendario operativo del plan. ¿Deseas continuar?')) return;
        tasks = JSON.parse(JSON.stringify(planTasks));
        renderTasks();
        resizeVisibleTextareas(wizardSteps[currentStep]);
    });
    tasksContainer.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-task]');
        if (!button) return;
        readTasks(); tasks.splice(Number(button.dataset.removeTask), 1); renderTasks();
    });
    form.addEventListener('submit', event => {
        readAudiences();
        readTasks();
        const missingResponsible = tasks.findIndex(task => (task.responsables_ids ?? []).length === 0);
        if (missingResponsible >= 0) {
            event.preventDefault();
            showStep(3);
            alert(`Selecciona al menos un responsable para la tarea ${missingResponsible + 1}.`);
            return;
        }
        const holder = document.getElementById('indicators-hidden'); holder.innerHTML = '';
        document.getElementById('indicadores_csv').value.split(',').map(value => value.trim()).filter(Boolean).forEach(value => {
            const input = document.createElement('input'); input.type='hidden'; input.name='indicadores[]'; input.value=value; holder.appendChild(input);
        });
    });
    enhanceCustomSelects(document);
    renderAudiences();
    setDesignerSelections(initialDesignerIds);
    renderTasks();
    prefillNote.hidden = isEditing || modeInput.value === 'manual';
    showStep(0);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCampaignWizard, { once: true });
} else {
    initializeCampaignWizard();
}
</script>
@endsection
