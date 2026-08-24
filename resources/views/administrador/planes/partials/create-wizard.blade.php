<section id="plan-create-wizard" class="plan-wizard" aria-labelledby="plan-wizard-title">
    <div class="plan-wizard-progress">
        <div>
            <span id="plan-wizard-counter">Paso 1 de 5</span>
            <strong id="plan-wizard-progress-title">Comencemos</strong>
        </div>
        <div class="plan-wizard-progress-track"><i id="plan-wizard-progress-bar"></i></div>
        <div class="plan-wizard-dots" aria-hidden="true">
            @for($wizardStep = 0; $wizardStep < 5; $wizardStep++)
                <i class="{{ $wizardStep === 0 ? 'is-active' : '' }}"></i>
            @endfor
        </div>
    </div>

    <div class="plan-wizard-stage">
        <div class="plan-wizard-step is-active" data-wizard-step="0">
            <div class="plan-wizard-welcome-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
            <span class="plan-wizard-kicker">Nuevo plan</span>
            <h2 id="plan-wizard-title">Vamos a crear tu plan</h2>
            <p>Te guiaremos paso a paso para definir su identidad, precio y beneficios. Al terminar podrás revisar la card completa antes de guardarla.</p>
            <div class="plan-wizard-welcome-points">
                <span><i class="fas fa-pen"></i> Identidad</span>
                <span><i class="fas fa-coins"></i> Precio</span>
                <span><i class="fas fa-list-check"></i> Características</span>
            </div>
            <button type="button" id="plan-wizard-start" class="plan-wizard-primary">Empezar <i class="fas fa-arrow-right"></i></button>
        </div>

        <div class="plan-wizard-step" data-wizard-step="1">
            <span class="plan-wizard-kicker">Identidad del plan</span>
            <h2>¿Cómo se llamará?</h2>
            <p>Escribe la información principal que verá el cliente en la card.</p>
            <div class="plan-wizard-fields">
                <label class="is-wide"><span>Nombre del plan *</span><input id="wizard-plan-name" type="text" maxlength="255" placeholder="Ej: Marketing Junior"></label>
                <label class="is-wide"><span>Subtítulo</span><input id="wizard-plan-subtitle" type="text" maxlength="255" placeholder="Una frase breve que describa el plan"></label>
                <label class="is-wide"><span>Descripción</span><textarea id="wizard-plan-description" rows="4" placeholder="Describe el enfoque, alcance y valor del plan"></textarea></label>
            </div>
        </div>

        <div class="plan-wizard-step" data-wizard-step="2">
            <span class="plan-wizard-kicker">Configuración comercial</span>
            <h2>Define el precio</h2>
            <p>El plan se creará activo por defecto y estará disponible para nuevas contrataciones.</p>
            <div class="plan-wizard-fields is-commercial">
                <label><span>Precio *</span><div class="plan-wizard-price"><i class="fas fa-tag"></i><input id="wizard-plan-price" type="number" min="0" step="0.01" value="0"></div></label>
                <label><span>Moneda *</span>
                    <div class="wizard-dropdown" data-wizard-dropdown>
                        <input id="wizard-plan-currency" type="hidden" value="BS">
                        <button type="button" class="wizard-dropdown-trigger"><i class="fas fa-coins"></i><span>Bolivianos (Bs)</span><i class="fas fa-chevron-down"></i></button>
                        <div class="wizard-dropdown-menu">
                            <button type="button" data-value="BS" class="is-selected">Bolivianos (Bs)</button>
                            <button type="button" data-value="USD">Dólares (USD)</button>
                        </div>
                    </div>
                </label>
                <label class="is-wide"><span>Periodo de facturación *</span>
                    <div class="wizard-dropdown" data-wizard-dropdown>
                        <input id="wizard-plan-period" type="hidden" value="mes">
                        <button type="button" class="wizard-dropdown-trigger"><i class="fas fa-calendar-days"></i><span>Mensual</span><i class="fas fa-chevron-down"></i></button>
                        <div class="wizard-dropdown-menu">
                            <button type="button" data-value="mes" class="is-selected">Mensual</button>
                            <button type="button" data-value="trimestre">Trimestral</button>
                            <button type="button" data-value="semestre">Semestral</button>
                            <button type="button" data-value="año">Anual</button>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="plan-wizard-step" data-wizard-step="3">
            <div class="plan-wizard-feature-head">
                <div><span class="plan-wizard-kicker">Beneficios incluidos</span><h2>Selecciona las características</h2><p>Al seleccionar una, configura su cantidad y frecuencia.</p></div>
                <button type="button" id="wizard-open-new-feature" class="plan-wizard-secondary"><i class="fas fa-plus"></i> Agregar característica</button>
            </div>
            <div id="wizard-feature-list" class="wizard-feature-list">
                @foreach($caracteristicas as $wizardFeature)
                    <article class="wizard-feature-item" data-feature-id="{{ $wizardFeature->id }}" data-feature-name="{{ $wizardFeature->nombre }}" data-quantity="1" data-frequency="" data-configured="false">
                        <input type="checkbox" id="wizard-feature-{{ $wizardFeature->id }}">
                        <button type="button" class="wizard-feature-name"><span>{{ $wizardFeature->nombre }}</span><small>Seleccionar y configurar</small></button>
                        <span class="wizard-feature-check"><i class="fas fa-check"></i></span>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="plan-wizard-step" data-wizard-step="4">
            <div class="plan-wizard-finish-icon"><i class="fas fa-check"></i></div>
            <span class="plan-wizard-kicker">Configuración terminada</span>
            <h2>¡Acabaste de configurar tu plan!</h2>
            <p>Presiona finalizar para abrir la vista completa. Allí podrás revisar y modificar cualquier dato antes de crear el plan definitivamente.</p>
            <div id="wizard-finish-summary" class="wizard-finish-summary"></div>
        </div>
    </div>

    <p id="plan-wizard-error" class="plan-wizard-error hidden"></p>
    <div id="plan-wizard-navigation" class="plan-wizard-navigation hidden">
        <button type="button" id="plan-wizard-back" class="plan-wizard-back"><i class="fas fa-arrow-left"></i> Atrás</button>
        <button type="button" id="plan-wizard-next" class="plan-wizard-primary">Siguiente <i class="fas fa-arrow-right"></i></button>
    </div>
</section>

<div id="wizard-feature-config-modal" class="wizard-modal hidden" role="dialog" aria-modal="true" aria-labelledby="wizard-config-title">
    <div class="wizard-modal-dialog">
        <button type="button" class="wizard-modal-close" data-wizard-config-close><i class="fas fa-times"></i></button>
        <span class="wizard-modal-icon"><i class="fas fa-sliders"></i></span>
        <span class="plan-wizard-kicker">Configurar característica</span>
        <h3 id="wizard-config-title">Característica</h3>
        <p>Define cómo se incluirá este beneficio dentro del plan.</p>
        <div class="wizard-modal-fields">
            <label><span>Cantidad *</span><input id="wizard-feature-quantity" type="number" min="1" step="1" value="1"></label>
            <label><span>Frecuencia <small>(opcional)</small></span>
                <div class="wizard-dropdown" data-wizard-dropdown>
                    <input id="wizard-feature-frequency" type="hidden" value="">
                    <button type="button" class="wizard-dropdown-trigger"><i class="fas fa-clock"></i><span>Sin frecuencia</span><i class="fas fa-chevron-down"></i></button>
                    <div class="wizard-dropdown-menu"><button type="button" data-value="" class="is-selected">Sin frecuencia</button><button type="button" data-value="semanal">Semanal</button><button type="button" data-value="mensual">Mensual</button></div>
                </div>
            </label>
        </div>
        <div class="wizard-modal-actions"><button type="button" data-wizard-config-close>Cancelar</button><button type="button" id="wizard-save-feature-config"><i class="fas fa-check"></i> Guardar configuración</button></div>
    </div>
</div>

<div id="wizard-new-feature-modal" class="wizard-modal hidden" role="dialog" aria-modal="true" aria-labelledby="wizard-new-feature-title">
    <div class="wizard-modal-dialog">
        <button type="button" class="wizard-modal-close" data-wizard-new-close><i class="fas fa-times"></i></button>
        <span class="wizard-modal-icon is-turquoise"><i class="fas fa-plus"></i></span>
        <span class="plan-wizard-kicker">Nueva característica</span>
        <h3 id="wizard-new-feature-title">Crear característica</h3>
        <p>La nueva opción aparecerá inmediatamente en la lista para que puedas seleccionarla.</p>
        <label class="wizard-new-feature-field"><span>Nombre *</span><input id="wizard-new-feature-name" type="text" maxlength="255" placeholder="Ej: Soporte personalizado"></label>
        <p id="wizard-new-feature-error" class="plan-wizard-error hidden"></p>
        <div class="wizard-modal-actions"><button type="button" data-wizard-new-close>Cancelar</button><button type="button" id="wizard-save-new-feature"><i class="fas fa-floppy-disk"></i> Crear característica</button></div>
    </div>
</div>

<style>
    .plan-editor-form.is-wizard-pending{display:none!important}.plan-wizard{max-width:1100px;margin:24px auto 0;border:1px solid #e1dde4;border-top:5px solid #ef6c22;border-radius:18px;background:#fff;box-shadow:0 16px 38px rgba(48,40,52,.1)}.plan-wizard-progress{display:grid;grid-template-columns:auto minmax(180px,1fr) auto;align-items:center;gap:22px;padding:18px 24px;border-bottom:1px solid #ebe8ed;background:#faf9fb}.plan-wizard-progress span,.plan-wizard-progress strong{display:block}.plan-wizard-progress span{color:#117e8c;font-size:.59rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.plan-wizard-progress strong{margin-top:3px;color:#403743;font-size:.78rem}.plan-wizard-progress-track{height:5px;overflow:hidden;border-radius:999px;background:#e6e2e8}.plan-wizard-progress-track i{display:block;width:20%;height:100%;border-radius:inherit;background:linear-gradient(90deg,#ef6c22,#f5a900);transition:.3s}.plan-wizard-dots{display:flex;gap:6px}.plan-wizard-dots i{width:8px;height:8px;border-radius:50%;background:#d8d3da;transition:.25s}.plan-wizard-dots i.is-active{background:#ef6c22;box-shadow:0 0 0 4px rgba(239,108,34,.12)}.plan-wizard-stage{min-height:470px;display:flex;align-items:center;padding:38px 52px}.plan-wizard-step{display:none;width:100%;animation:wizardIn .28s ease}.plan-wizard-step.is-active{display:block}.plan-wizard-step[data-wizard-step="0"],.plan-wizard-step[data-wizard-step="4"]{max-width:720px;margin:auto;text-align:center}@keyframes wizardIn{from{opacity:0;transform:translateX(12px)}to{opacity:1;transform:none}}.plan-wizard-kicker{display:block;color:#ef6c22;font-size:.63rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.plan-wizard-step h2{margin:7px 0 0;color:#302834;font-size:clamp(1.55rem,3vw,2.15rem);font-weight:900;letter-spacing:-.035em}.plan-wizard-step>p,.plan-wizard-feature-head p{max-width:650px;margin:9px auto 0;color:#756a7a;font-size:.78rem;line-height:1.65}.plan-wizard-welcome-icon,.plan-wizard-finish-icon{width:74px;height:74px;display:grid;place-items:center;margin:0 auto 18px;border-radius:22px;background:linear-gradient(135deg,#fff1e7,#fff8e9);color:#ef6c22;font-size:1.75rem;box-shadow:0 12px 24px rgba(239,108,34,.13)}.plan-wizard-finish-icon{border-radius:50%;background:#eef7e7;color:#7da533}.plan-wizard-welcome-points{display:flex;justify-content:center;flex-wrap:wrap;gap:9px;margin:24px 0}.plan-wizard-welcome-points span{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #e5e1e7;border-radius:999px;background:#faf9fb;color:#655c68;font-size:.68rem;font-weight:800}.plan-wizard-welcome-points i{color:#117e8c}.plan-wizard-primary,.plan-wizard-secondary,.plan-wizard-back{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:43px;padding:0 18px;border-radius:9px;font-size:.74rem;font-weight:900;transition:.18s}.plan-wizard-primary{background:#ef6c22;color:#fff;box-shadow:0 8px 17px rgba(239,108,34,.2)}.plan-wizard-primary:hover{filter:brightness(.94);transform:translateY(-1px)}.plan-wizard-secondary{border:1px solid #bcdadd;background:#edf7f8;color:#0d6975}.plan-wizard-back{border:1px solid #ded9e1;background:#fff;color:#655c68}.plan-wizard-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px;max-width:760px;margin:28px auto 0}.plan-wizard-fields label>span,.wizard-modal-fields label>span,.wizard-new-feature-field>span{display:block;margin-bottom:7px;color:#514957;font-size:.7rem;font-weight:900}.plan-wizard-fields .is-wide{grid-column:1/-1}.plan-wizard-fields input,.plan-wizard-fields textarea,.wizard-modal-fields input,.wizard-new-feature-field input{width:100%;min-height:48px;padding:11px 14px;border:1px solid #ded9e1;border-radius:10px;background:#fbfafc;color:#403743;font-size:.8rem;outline:0;transition:.18s}.plan-wizard-fields textarea{resize:vertical}.plan-wizard-fields input:focus,.plan-wizard-fields textarea:focus,.wizard-modal-fields input:focus,.wizard-new-feature-field input:focus{border-color:#117e8c;background:#fff;box-shadow:0 0 0 3px rgba(17,126,140,.12)}.plan-wizard-price{position:relative}.plan-wizard-price>i{position:absolute;top:17px;left:15px;color:#117e8c}.plan-wizard-price input{padding-left:43px}.wizard-dropdown{position:relative}.wizard-dropdown-trigger{position:relative;width:100%;height:48px;display:flex;align-items:center;gap:10px;padding:0 40px 0 14px;border:1px solid #ded9e1;border-radius:10px;background:#fbfafc;color:#514957;text-align:left}.wizard-dropdown-trigger>i:first-child{width:18px;color:#117e8c;text-align:center}.wizard-dropdown-trigger span{flex:1;font-size:.78rem;font-weight:800}.wizard-dropdown-trigger>i:last-child{position:absolute;right:15px;color:#817983;font-size:.65rem;transition:.18s}.wizard-dropdown.is-open .wizard-dropdown-trigger{border-color:#117e8c;background:#fff;box-shadow:0 0 0 3px rgba(17,126,140,.12)}.wizard-dropdown.is-open .wizard-dropdown-trigger>i:last-child{transform:rotate(180deg)}.wizard-dropdown-menu{position:absolute;z-index:140;top:calc(100% + 7px);right:0;left:0;display:none;padding:7px;border:1px solid #ded9e1;border-radius:11px;background:#fff;box-shadow:0 18px 38px rgba(48,40,52,.18)}.wizard-dropdown.is-open .wizard-dropdown-menu{display:grid;gap:3px}.wizard-dropdown-menu button{min-height:39px;padding:8px 10px;border-radius:7px;color:#605763;text-align:left;font-size:.75rem;font-weight:800}.wizard-dropdown-menu button:hover,.wizard-dropdown-menu button.is-selected{background:#edf7f8;color:#0d6975}.plan-wizard-feature-head{display:flex;align-items:end;justify-content:space-between;gap:20px}.plan-wizard-feature-head h2{font-size:1.65rem}.plan-wizard-feature-head p{margin-right:0;margin-left:0}.wizard-feature-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;max-height:330px;overflow-y:auto;margin-top:22px;padding:3px}.wizard-feature-item{position:relative;display:grid;grid-template-columns:25px minmax(0,1fr) 24px;align-items:center;gap:10px;min-height:72px;padding:12px 13px;border:1px solid #e1dde4;border-radius:11px;background:#fff;transition:.18s}.wizard-feature-item:hover{border-color:#bcdadd;background:#fbfefe}.wizard-feature-item.is-selected{border-color:#7da533;background:#f4f9ee;box-shadow:0 0 0 3px rgba(125,165,51,.1)}.wizard-feature-item input{width:23px;height:23px;appearance:none;border:1px solid #cfcad1;border-radius:7px;background:#fff;cursor:pointer}.wizard-feature-item input:checked{border-color:#7da533;background:#7da533}.wizard-feature-item input:checked:after{content:'✓';display:grid;place-items:center;height:100%;color:#fff;font-size:.7rem;font-weight:900}.wizard-feature-name{text-align:left}.wizard-feature-name span,.wizard-feature-name small{display:block}.wizard-feature-name span{color:#49404c;font-size:.75rem;font-weight:900}.wizard-feature-name small{margin-top:3px;color:#918794;font-size:.59rem}.wizard-feature-item.is-selected .wizard-feature-name small{color:#668a2c}.wizard-feature-check{width:22px;height:22px;display:grid;place-items:center;border-radius:50%;background:#eeeaf0;color:#aaa2ad;font-size:.55rem}.wizard-feature-item.is-selected .wizard-feature-check{background:#7da533;color:#fff}.plan-wizard-error{max-width:760px;margin:0 auto 12px;padding:10px 13px;border:1px solid #f1c8c8;border-radius:9px;background:#fff1f1;color:#af3434;font-size:.7rem;font-weight:800}.plan-wizard-navigation{display:flex;align-items:center;justify-content:space-between;padding:17px 24px;border-top:1px solid #ebe8ed;background:#faf9fb}.wizard-finish-summary{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-top:24px}.wizard-finish-summary span{padding:7px 10px;border-radius:999px;background:#f1f6ea;color:#5f7e2d;font-size:.68rem;font-weight:800}.wizard-modal{position:fixed;z-index:13000;inset:0;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.65);backdrop-filter:blur(5px)}.wizard-modal.is-open{display:flex}.wizard-modal-dialog{position:relative;width:100%;max-width:480px;padding:27px;border-top:5px solid #ef6c22;border-radius:17px;background:#fff;box-shadow:0 28px 70px rgba(15,23,42,.32)}.wizard-modal-close{position:absolute;top:13px;right:13px;width:35px;height:35px;border-radius:50%;color:#77707a}.wizard-modal-close:hover{background:#f3f1f4}.wizard-modal-icon{width:48px;height:48px;display:grid;place-items:center;margin-bottom:16px;border-radius:14px;background:#fff1e8;color:#ef6c22}.wizard-modal-icon.is-turquoise{background:#eaf6f7;color:#117e8c}.wizard-modal-dialog h3{margin-top:6px;color:#302834;font-size:1.25rem;font-weight:900}.wizard-modal-dialog>p{margin-top:6px;color:#756a7a;font-size:.72rem;line-height:1.55}.wizard-modal-fields{display:grid;grid-template-columns:1fr 1fr;gap:13px;margin-top:20px}.wizard-new-feature-field{display:block;margin-top:20px}.wizard-modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:22px}.wizard-modal-actions button{min-height:43px;border-radius:9px;background:#f1eff2;color:#655d68;font-size:.71rem;font-weight:900}.wizard-modal-actions button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#117e8c;color:#fff}
    @media(max-width:767px){.plan-wizard{margin-right:12px;margin-left:12px}.plan-wizard-progress{grid-template-columns:1fr auto;padding:15px}.plan-wizard-progress-track{grid-column:1/-1;grid-row:2}.plan-wizard-stage{min-height:520px;padding:28px 19px}.plan-wizard-fields,.wizard-modal-fields,.wizard-feature-list{grid-template-columns:1fr}.plan-wizard-feature-head{align-items:flex-start;flex-direction:column}.plan-wizard-secondary{width:100%}.plan-wizard-navigation{padding:14px}.plan-wizard-dots{display:none}}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wizard = document.getElementById('plan-create-wizard');
    if (!wizard) return;

    const steps = [...wizard.querySelectorAll('[data-wizard-step]')];
    const dots = [...wizard.querySelectorAll('.plan-wizard-dots i')];
    const counter = document.getElementById('plan-wizard-counter');
    const progressTitle = document.getElementById('plan-wizard-progress-title');
    const progressBar = document.getElementById('plan-wizard-progress-bar');
    const navigation = document.getElementById('plan-wizard-navigation');
    const backButton = document.getElementById('plan-wizard-back');
    const nextButton = document.getElementById('plan-wizard-next');
    const startButton = document.getElementById('plan-wizard-start');
    const errorBox = document.getElementById('plan-wizard-error');
    const titles = ['Comencemos', 'Identidad', 'Precio y facturación', 'Características', 'Revisión final'];
    let currentStep = 0;

    const closeDropdowns = function (except) {
        document.querySelectorAll('[data-wizard-dropdown]').forEach(function (dropdown) {
            if (dropdown !== except) dropdown.classList.remove('is-open');
        });
    };
    document.querySelectorAll('[data-wizard-dropdown]').forEach(function (dropdown) {
        const input = dropdown.querySelector('input[type=hidden]');
        const trigger = dropdown.querySelector('.wizard-dropdown-trigger');
        const label = trigger.querySelector('span');
        const options = [...dropdown.querySelectorAll('.wizard-dropdown-menu button')];
        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const opening = !dropdown.classList.contains('is-open');
            closeDropdowns(dropdown);
            dropdown.classList.toggle('is-open', opening);
        });
        options.forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.stopPropagation();
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                closeDropdowns();
            });
        });
    });
    document.addEventListener('click', () => closeDropdowns());

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }
    function clearError() {
        errorBox.textContent = '';
        errorBox.classList.add('hidden');
    }
    function showStep(index) {
        currentStep = index;
        steps.forEach(step => step.classList.toggle('is-active', Number(step.dataset.wizardStep) === index));
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex <= index));
        counter.textContent = 'Paso ' + (index + 1) + ' de 5';
        progressTitle.textContent = titles[index];
        progressBar.style.width = ((index + 1) * 20) + '%';
        navigation.classList.toggle('hidden', index === 0);
        backButton.classList.toggle('invisible', index === 0);
        nextButton.innerHTML = index === 4 ? '<i class="fas fa-check"></i> Finalizar' : 'Siguiente <i class="fas fa-arrow-right"></i>';
        clearError();
        wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (index === 4) renderFinishSummary();
    }
    function validateStep() {
        if (currentStep === 1 && !document.getElementById('wizard-plan-name').value.trim()) {
            showError('Escribe el nombre del plan para continuar.');
            return false;
        }
        if (currentStep === 2) {
            const price = Number.parseFloat(document.getElementById('wizard-plan-price').value);
            if (Number.isNaN(price) || price < 0) {
                showError('Ingresa un precio válido para continuar.');
                return false;
            }
        }
        if (currentStep === 3 && !document.querySelector('.wizard-feature-item input:checked')) {
            showError('Selecciona y configura al menos una característica.');
            return false;
        }
        return true;
    }
    startButton.addEventListener('click', () => showStep(1));
    backButton.addEventListener('click', () => showStep(Math.max(0, currentStep - 1)));
    nextButton.addEventListener('click', function () {
        if (!validateStep()) return;
        if (currentStep < 4) showStep(currentStep + 1);
        else finishWizard();
    });

    const featureList = document.getElementById('wizard-feature-list');
    const configModal = document.getElementById('wizard-feature-config-modal');
    const configTitle = document.getElementById('wizard-config-title');
    const quantityInput = document.getElementById('wizard-feature-quantity');
    const frequencyInput = document.getElementById('wizard-feature-frequency');
    let activeFeatureItem = null;
    let initialFeatureSelection = false;

    function resetFeatureItem(item) {
        item.dataset.quantity = '1';
        item.dataset.frequency = '';
        item.dataset.configured = 'false';
        item.classList.remove('is-selected');
        item.querySelector('.wizard-feature-name small').textContent = 'Seleccionar y configurar';
    }
    function openFeatureConfig(item, isInitial) {
        activeFeatureItem = item;
        initialFeatureSelection = isInitial;
        configTitle.textContent = item.dataset.featureName;
        quantityInput.value = item.dataset.quantity || '1';
        frequencyInput.value = item.dataset.frequency || '';
        const frequencyOption = configModal.querySelector('[data-value="' + frequencyInput.value + '"]');
        if (frequencyOption) frequencyOption.click();
        configModal.classList.remove('hidden');
        configModal.classList.add('is-open');
        document.body.classList.add('overflow-hidden');
    }
    function closeFeatureConfig(cancelled) {
        if (cancelled && initialFeatureSelection && activeFeatureItem) {
            activeFeatureItem.querySelector('input').checked = false;
            resetFeatureItem(activeFeatureItem);
        }
        configModal.classList.add('hidden');
        configModal.classList.remove('is-open');
        document.body.classList.remove('overflow-hidden');
        activeFeatureItem = null;
    }
    function bindFeatureItem(item) {
        const checkbox = item.querySelector('input');
        const nameButton = item.querySelector('.wizard-feature-name');
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) openFeatureConfig(item, true);
            else resetFeatureItem(item);
        });
        nameButton.addEventListener('click', function () {
            if (!checkbox.checked) {
                checkbox.checked = true;
                openFeatureConfig(item, true);
            } else {
                openFeatureConfig(item, false);
            }
        });
    }
    featureList.querySelectorAll('.wizard-feature-item').forEach(bindFeatureItem);
    document.querySelectorAll('[data-wizard-config-close]').forEach(button => button.addEventListener('click', () => closeFeatureConfig(true)));
    document.getElementById('wizard-save-feature-config').addEventListener('click', function () {
        const quantity = Math.max(1, Number.parseInt(quantityInput.value || '1', 10));
        if (!activeFeatureItem) return;
        activeFeatureItem.dataset.quantity = String(quantity);
        activeFeatureItem.dataset.frequency = frequencyInput.value;
        activeFeatureItem.dataset.configured = 'true';
        activeFeatureItem.classList.add('is-selected');
        activeFeatureItem.querySelector('input').checked = true;
        activeFeatureItem.querySelector('.wizard-feature-name small').textContent = frequencyInput.value ? quantity + ' · ' + frequencyInput.value : quantity + ' · sin frecuencia';
        initialFeatureSelection = false;
        closeFeatureConfig(false);
    });

    const newFeatureModal = document.getElementById('wizard-new-feature-modal');
    const newFeatureName = document.getElementById('wizard-new-feature-name');
    const newFeatureError = document.getElementById('wizard-new-feature-error');
    function closeNewFeatureModal() {
        newFeatureModal.classList.add('hidden');
        newFeatureModal.classList.remove('is-open');
        document.body.classList.remove('overflow-hidden');
        newFeatureName.value = '';
        newFeatureError.classList.add('hidden');
    }
    document.getElementById('wizard-open-new-feature').addEventListener('click', function () {
        newFeatureModal.classList.remove('hidden');
        newFeatureModal.classList.add('is-open');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => newFeatureName.focus(), 0);
    });
    document.querySelectorAll('[data-wizard-new-close]').forEach(button => button.addEventListener('click', closeNewFeatureModal));
    document.getElementById('wizard-save-new-feature').addEventListener('click', async function () {
        const name = newFeatureName.value.trim();
        if (!name) {
            newFeatureError.textContent = 'Escribe el nombre de la característica.';
            newFeatureError.classList.remove('hidden');
            return;
        }
        const saveButton = this;
        saveButton.disabled = true;
        try {
            const response = await fetch(@json(route('administrador.planes.caracteristicas.store')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify({ nombre: name }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo crear la característica.');

            const item = document.createElement('article');
            item.className = 'wizard-feature-item';
            item.dataset.featureId = data.caracteristica.id;
            item.dataset.featureName = data.caracteristica.nombre;
            item.dataset.quantity = '1';
            item.dataset.frequency = '';
            item.dataset.configured = 'false';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = 'wizard-feature-' + data.caracteristica.id;
            const nameButton = document.createElement('button');
            nameButton.type = 'button';
            nameButton.className = 'wizard-feature-name';
            const nameLabel = document.createElement('span');
            nameLabel.textContent = data.caracteristica.nombre;
            const detail = document.createElement('small');
            detail.textContent = 'Seleccionar y configurar';
            nameButton.append(nameLabel, detail);
            const check = document.createElement('span');
            check.className = 'wizard-feature-check';
            check.innerHTML = '<i class="fas fa-check"></i>';
            item.append(checkbox, nameButton, check);
            featureList.appendChild(item);
            bindFeatureItem(item);

            document.querySelectorAll('.feature-select').forEach(function (select) {
                if (!Array.from(select.options).some(option => option.value === String(data.caracteristica.id))) {
                    select.add(new Option(data.caracteristica.nombre, data.caracteristica.id));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            closeNewFeatureModal();
            checkbox.checked = true;
            openFeatureConfig(item, true);
        } catch (error) {
            newFeatureError.textContent = error.message;
            newFeatureError.classList.remove('hidden');
        } finally {
            saveButton.disabled = false;
        }
    });

    function selectedFeatures() {
        return [...featureList.querySelectorAll('.wizard-feature-item')].filter(item => item.querySelector('input').checked);
    }
    function renderFinishSummary() {
        const summary = document.getElementById('wizard-finish-summary');
        summary.innerHTML = '';
        [document.getElementById('wizard-plan-name').value.trim(), document.getElementById('wizard-plan-price').value + ' ' + document.getElementById('wizard-plan-currency').value, selectedFeatures().length + ' característica(s)'].forEach(function (text) {
            const badge = document.createElement('span');
            badge.textContent = text;
            summary.appendChild(badge);
        });
    }
    function setFormValue(id, value) {
        const field = document.getElementById(id);
        field.value = value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function finishWizard() {
        setFormValue('nombre', document.getElementById('wizard-plan-name').value.trim());
        setFormValue('subtitulo', document.getElementById('wizard-plan-subtitle').value.trim());
        setFormValue('descripcion', document.getElementById('wizard-plan-description').value.trim());
        setFormValue('precio', document.getElementById('wizard-plan-price').value);
        setFormValue('moneda', document.getElementById('wizard-plan-currency').value);
        setFormValue('periodo_facturacion', document.getElementById('wizard-plan-period').value);
        const active = document.getElementById('activo');
        active.checked = true;
        active.dispatchEvent(new Event('change', { bubbles: true }));

        document.querySelectorAll('#features-container [data-feature-card] .remove-feature').forEach(button => button.click());
        selectedFeatures().forEach(function (item) {
            document.getElementById('add-feature').click();
            const cards = document.querySelectorAll('#features-container [data-feature-card]');
            const card = cards[cards.length - 1];
            const select = card.querySelector('.feature-select');
            if (!Array.from(select.options).some(option => option.value === String(item.dataset.featureId))) {
                select.add(new Option(item.dataset.featureName, item.dataset.featureId));
            }
            select.value = item.dataset.featureId;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            const quantity = card.querySelector('input[name$="[cantidad]"]');
            const frequency = card.querySelector('input[name$="[frecuencia]"]');
            quantity.value = item.dataset.quantity;
            frequency.value = item.dataset.frequency;
            quantity.dispatchEvent(new Event('input', { bubbles: true }));
            frequency.dispatchEvent(new Event('input', { bubbles: true }));
        });

        const form = document.querySelector('.plan-editor-form');
        wizard.classList.add('hidden');
        form.classList.remove('is-wizard-pending');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
@endpush
