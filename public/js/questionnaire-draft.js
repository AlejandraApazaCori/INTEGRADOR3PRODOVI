window.initQuestionnaireDraft = function (form, options) {
    const key = 'questionnaire-draft:v1:' + options.user + ':' + options.company;
    let cached = null;
    try { cached = JSON.parse(localStorage.getItem(key)); } catch (_) {}
    if (!options.enabled) {
        try { localStorage.removeItem(key); } catch (_) {}
        return { step: 0, setStep() {}, stop() {} };
    }

    const fields = Array.from(form.querySelectorAll('[name^="respuesta_"]'));
    const status = document.createElement('p');
    status.setAttribute('role', 'status');
    status.style.cssText = 'margin:0;padding:8px 16px;font-size:12px;color:#756b7a';
    form.prepend(status);
    let pending = Boolean(cached?.pending);
    let positions = cached?.positions || {};
    let busy = false;
    let stopped = false;
    let timer;
    let localSaved = false;

    if (pending && cached?.answers) {
        fields.forEach(field => {
            const name = field.name.replace(/\[\]$/, '');
            if (!Object.prototype.hasOwnProperty.call(cached.answers, name)) return;
            const value = cached.answers[name];
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = (Array.isArray(value) ? value : [value]).includes(field.value);
            } else if (!Array.isArray(value)) {
                field.value = value ?? '';
            }
        });
    }

    function answers() {
        const result = {};
        // Incluye secciones deshabilitadas y casillas desmarcadas.
        fields.forEach(field => {
            const name = field.name.replace(/\[\]$/, '');
            if (field.type === 'checkbox') {
                result[name] ??= [];
                if (field.checked) result[name].push(field.value);
            } else if (field.type === 'radio') {
                result[name] ??= '';
                if (field.checked) result[name] = field.value;
            } else {
                result[name] = field.value;
            }
        });
        return result;
    }

    function persist() {
        try {
            localStorage.setItem(key, JSON.stringify({ answers: answers(), pending, positions }));
            localSaved = true;
        } catch (_) { localSaved = false; }
    }

    function pendingMessage() {
        status.textContent = localSaved
            ? 'Borrador guardado en este navegador. Pendiente de sincronizar.'
            : 'No se pudo guardar el borrador en este navegador. Pendiente de sincronizar.';
    }

    async function sync() {
        if (!pending || busy || stopped) return;
        if (!navigator.onLine) { pendingMessage(); return; }
        busy = true;
        const snapshot = answers();
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 10000);
        try {
            const response = await fetch(options.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: JSON.stringify(snapshot),
                signal: controller.signal
            });
            if (!response.ok) throw new Error('save');
            const result = await response.json();
            if (result.saved !== true) throw new Error('save');
            if (JSON.stringify(snapshot) === JSON.stringify(answers())) {
                pending = false;
                persist();
                status.textContent = 'Borrador guardado automáticamente.';
            }
        } catch (_) { pendingMessage(); }
        finally { clearTimeout(timeout); busy = false; }
        if (pending && !stopped) timer = setTimeout(sync, 15000);
    }

    function changed() {
        pending = true;
        persist();
        pendingMessage();
        clearTimeout(timer);
        timer = setTimeout(sync, 800);
    }
    form.addEventListener('input', changed);
    form.addEventListener('change', changed);
    window.addEventListener('online', sync);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') { persist(); sync(); }
    });
    window.addEventListener('pagehide', persist);
    window.addEventListener('pageshow', () => { stopped = false; sync(); });
    form.addEventListener('submit', event => {
        // Se conserva hasta confirmar el envío en el servidor.
        pending = true;
        persist();
        queueMicrotask(() => {
            if (!event.defaultPrevented) { stopped = true; clearTimeout(timer); }
        });
    });
    if (pending) { persist(); pendingMessage(); timer = setTimeout(sync, 800); }
    else status.textContent = 'Tus respuestas se guardan automáticamente como borrador.';

    return {
        step: Number(positions[options.view]) || 0,
        setStep(step) { positions[options.view] = step; persist(); },
        stop() { pending = true; persist(); stopped = true; clearTimeout(timer); }
    };
};
