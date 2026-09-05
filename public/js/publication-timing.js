(() => {
    const panel = document.getElementById('optimization-panel');
    if (!panel) return;
    const results = document.getElementById('optimization-results');
    const status = document.getElementById('optimization-status');
    const refresh = document.getElementById('optimization-refresh');
    let data = null;
    let pending = false;
    let charts = [];
    const dateLabel = value => new Intl.DateTimeFormat('es-BO', {
        timeZone: 'America/La_Paz', weekday: 'short', day: '2-digit', month: '2-digit',
        hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
    }).format(new Date(value));
    const node = (tag, text, classes = '') => {
        const el = document.createElement(tag);
        el.textContent = text;
        el.className = classes;
        return el;
    };
    function applySlot(slot) {
        if (new Date(slot.timestamp).getTime() <= Date.now() + 60000) {
            status.textContent = 'Este horario ya no está disponible. Actualiza las estimaciones.';
            return;
        }
        document.getElementById('schedule-later').checked = true;
        document.getElementById('schedule-datetime-container').classList.remove('hidden');
        window.initializeSchedulePicker();
        // El backend devuelve America/La_Paz: conservar su fecha civil, sin convertirla a UTC.
        document.getElementById('schedule-date-ui').value = slot.timestamp.slice(0, 10);
        document.getElementById('schedule-time-ui').value = slot.timestamp.slice(11, 16);
        window.syncCustomScheduleInputs();
        window.updatePreview();
        status.textContent = `Horario aplicado: ${dateLabel(slot.timestamp)} (America/La_Paz). Revisa las redes seleccionadas y guarda la programación.`;
    }
    function render() {
        charts.forEach(chart => chart.destroy());
        charts = [];
        results.replaceChildren();
        if (!data) return;
        const selected = ['facebook', 'instagram'].filter(network => document.getElementById(`${network}-checkbox`)?.checked);
        status.textContent = selected.length ? (data.has_meta_errors ? 'Meta no entregó todos los datos. Se muestra el histórico disponible; revisa la fecha de consulta de cada cuenta.' : '') : 'Selecciona una red social para consultar sus horarios.';
        selected.forEach(network => {
            const platform = data.platforms[network];
            if (!platform) return;
            const card = node('section', '', 'border rounded-xl p-3 space-y-3');
            card.append(node('h4', `${network === 'facebook' ? 'Facebook' : 'Instagram'} · ${platform.account_name || 'Sin cuenta vinculada'}`, 'font-semibold'));
            if (platform.generated_at) card.append(node('p', `${platform.history_count} publicaciones con métricas · Consulta Meta: ${dateLabel(platform.generated_at)}`, 'text-xs text-gray-600'));
            if (platform.status === 'ok') {
                card.append(node('p', platform.experimental
                    ? (platform.training_sources?.includes('synthetic')
                        ? 'LSTM experimental: entrenada con datos de simulación y aplicada al histórico real de esta cuenta. Su eficacia en esta cuenta aún no está validada.'
                        : 'LSTM experimental: estimación basada en esta cuenta, pendiente de validación prospectiva.')
                    : 'Estimación LSTM basada en el histórico de esta cuenta.', 'text-xs text-gray-600'));
                const buttons = node('div', '', 'flex flex-wrap gap-2');
                const current = platform.slots.filter(slot => new Date(slot.timestamp).getTime() > Date.now() + 60000);
                [...current].sort((a, b) => b.predicted_score - a.predicted_score).slice(0, 5).forEach(slot => {
                    const button = node('button', `${dateLabel(slot.timestamp)} · ${Number(slot.predicted_score).toFixed(1)} pts · ${slot.samples} publicaciones en esta franja`, 'rp-optimization-time');
                    button.type = 'button';
                    button.title = slot.unseen_slot ? 'Franja sin observaciones: estimación extrapolada, sin respaldo histórico específico.' : 'Aplicar esta fecha y hora a la publicación';
                    if (slot.unseen_slot) button.append(node('small', ' · Sin evidencia en esta franja'));
                    button.addEventListener('click', () => applySlot(slot));
                    buttons.append(button);
                });
                card.append(buttons);
                if (typeof Chart !== 'undefined' && current.length) {
                    const wrapper = node('div');
                    wrapper.style.height = '240px';
                    const canvas = document.createElement('canvas');
                    wrapper.append(canvas);
                    card.append(wrapper);
                    results.append(card);
                    charts.push(new Chart(canvas, {
                        type: 'line',
                        data: {labels: current.map(slot => dateLabel(slot.timestamp)), datasets: [
                            {label: 'Puntaje estimado LSTM', data: current.map(slot => slot.predicted_score), borderColor: '#117e8c', pointRadius: 0, borderWidth: 2},
                            {label: 'Referencia histórica suavizada', data: current.map(slot => slot.historical_score), borderColor: '#5b2b76', borderDash: [5, 4], pointRadius: 0, borderWidth: 1.5}
                        ]},
                        options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}, x: {ticks: {maxTicksLimit: 8}}}, plugins: {legend: {position: 'bottom'}}}
                    }));
                }
            } else {
                const messages = {
                    not_connected: 'Esta red no tiene una cuenta vinculada.',
                    insufficient_data: `Histórico insuficiente para la LSTM. Se necesitan al menos ${platform.window || 3} publicaciones con reacciones/me gusta y comentarios disponibles.`,
                    unavailable: 'No se pudo ejecutar la predicción. Puedes usar la programación manual y consultar el histórico.'
                };
                card.append(node('p', messages[platform.status] || 'Predicción no disponible.', 'text-sm text-gray-600'));
            }
            if (platform.historical_best?.length) {
                card.append(node('p', 'Mejor hora histórica (método estadístico del panel de Meta): ' + platform.historical_best.map(slot => `${slot.label} (${slot.samples} publicaciones)`).join(' · '), 'text-xs text-gray-600'));
            }
            results.append(card);
        });
    }
    async function load() {
        if (pending) return;
        pending = true;
        refresh.disabled = true;
        status.textContent = 'Consultando el histórico de Meta y calculando horarios por cuenta…';
        try {
            const response = await fetch(panel.dataset.endpoint, {headers: {Accept: 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error('No se pudieron cargar las estimaciones. Comprueba tu sesión y vuelve a intentarlo.');
            data = await response.json();
            render();
        } catch (error) {
            charts.forEach(chart => chart.destroy());
            charts = [];
            data = null;
            results.replaceChildren();
            status.textContent = error.message;
        } finally {
            pending = false;
            refresh.disabled = false;
        }
    }
    document.getElementById('use-optimization').addEventListener('change', function () {
        panel.classList.toggle('hidden', !this.checked);
        if (this.checked) data ? render() : load();
    });
    ['facebook', 'instagram'].forEach(network => document.getElementById(`${network}-checkbox`)?.addEventListener('change', () => {
        if (!panel.classList.contains('hidden')) data ? render() : load();
    }));
    refresh.addEventListener('click', load);
})();
