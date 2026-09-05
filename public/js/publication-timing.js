(() => {
    const panel = document.getElementById('optimization-panel');
    if (!panel) return;
    const results = document.getElementById('optimization-results');
    const status = document.getElementById('optimization-status');
    const refresh = document.getElementById('optimization-refresh');
    let data = null;
    let pending = false;
    let charts = [];

    const dateLabel = value => new Intl.DateTimeFormat('es-BO', {timeZone: 'America/La_Paz', weekday: 'short', day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'}).format(new Date(value));
    const optionDate = value => new Intl.DateTimeFormat('es-BO', {timeZone: 'America/La_Paz', weekday: 'short', day: 'numeric', month: 'short'}).format(new Date(value));
    const optionTime = value => new Intl.DateTimeFormat('es-BO', {timeZone: 'America/La_Paz', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'}).format(new Date(value));
    const node = (tag, text = '', classes = '') => {
        const el = document.createElement(tag);
        el.textContent = text;
        el.className = classes;
        return el;
    };
    const icon = classes => {
        const el = document.createElement('i');
        el.className = classes;
        el.setAttribute('aria-hidden', 'true');
        return el;
    };

    function applySlot(slot, button) {
        if (new Date(slot.timestamp).getTime() <= Date.now() + 60000) {
            status.textContent = 'Este horario ya no está disponible. Actualiza las estimaciones.';
            return;
        }
        document.getElementById('schedule-later').checked = true;
        document.getElementById('schedule-datetime-container').classList.remove('hidden');
        window.initializeSchedulePicker();
        document.getElementById('schedule-date-ui').value = slot.timestamp.slice(0, 10);
        document.getElementById('schedule-time-ui').value = slot.timestamp.slice(11, 16);
        window.syncCustomScheduleInputs();
        window.updatePreview();
        results.querySelectorAll('.rp-prediction-option').forEach(item => item.classList.remove('is-active'));
        button?.classList.add('is-active');
        status.textContent = `Horario aplicado: ${dateLabel(slot.timestamp)} (America/La_Paz). Ya puedes guardar la programación.`;
    }

    function accountHeader(network, platform) {
        const header = node('div', '', 'rp-prediction-card__head');
        const account = node('div', '', 'rp-prediction-account');
        const networkIcon = node('div', '', `rp-prediction-account__icon is-${network}`);
        networkIcon.append(icon(`fab fa-${network}`));
        const copy = node('div');
        copy.append(node('small', network === 'facebook' ? 'Facebook' : 'Instagram'));
        copy.append(node('h4', platform.account_name || 'Sin cuenta vinculada'));
        account.append(networkIcon, copy);
        header.append(account);
        if (platform.status === 'ok') header.append(node('span', 'Predicción disponible', 'rp-prediction-badge'));
        return header;
    }

    function predictionOption(slot, index) {
        const button = node('button', '', `rp-prediction-option${index === 0 ? ' is-best' : ''}`);
        button.type = 'button';
        button.title = slot.unseen_slot ? 'Franja sin observaciones directas; la estimación fue extrapolada.' : 'Aplicar esta fecha y hora a la publicación';
        button.append(node('span', `Opción ${index + 1}`, 'rp-prediction-option__rank'));
        button.append(node('span', `${optionDate(slot.timestamp)} · ${optionTime(slot.timestamp)}`, 'rp-prediction-option__date'));
        const score = node('span', Number(slot.predicted_score).toFixed(1), 'rp-prediction-option__score');
        score.append(node('small', ' pts'));
        button.append(score);
        button.append(node('span', slot.unseen_slot ? 'Estimación sin historial directo' : `${slot.samples} publicaciones similares`, 'rp-prediction-option__samples'));
        button.append(node('span', 'Usar este horario →', 'rp-prediction-option__action'));
        button.addEventListener('click', () => applySlot(slot, button));
        return button;
    }

    function renderChart(card, slots) {
        if (typeof Chart === 'undefined' || !slots.length) return;
        const chartSlots = [...slots].sort((a, b) => b.predicted_score - a.predicted_score).slice(0, 8);
        const wrapper = node('div', '', 'rp-prediction-chart');
        wrapper.append(node('p', 'Comparación de las 8 mejores franjas', 'rp-prediction-chart__title'));
        const canvas = document.createElement('canvas');
        wrapper.append(canvas);
        card.append(wrapper);
        charts.push(new Chart(canvas, {
            type: 'bar',
            data: {
                labels: chartSlots.map(slot => `${optionDate(slot.timestamp)}, ${optionTime(slot.timestamp)}`),
                datasets: [
                    {label: 'Puntaje estimado', data: chartSlots.map(slot => slot.predicted_score), backgroundColor: '#1594a2', borderColor: '#0f7580', borderWidth: 1, borderRadius: 5, barPercentage: .72},
                    {label: 'Referencia histórica', data: chartSlots.map(slot => slot.historical_score), backgroundColor: '#c9dda4', borderColor: '#7da533', borderWidth: 1, borderRadius: 5, barPercentage: .72}
                ]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false, interaction: {mode: 'index', intersect: false},
                scales: {x: {beginAtZero: true, grid: {color: '#edf1ea'}, ticks: {font: {size: 9}}}, y: {grid: {display: false}, ticks: {font: {size: 9}}}},
                plugins: {legend: {position: 'bottom', labels: {usePointStyle: true, boxWidth: 7, font: {size: 9}}}, tooltip: {callbacks: {label: context => `${context.dataset.label}: ${Number(context.raw).toFixed(1)} puntos`}}}
            }
        }));
    }

    function render() {
        charts.forEach(chart => chart.destroy());
        charts = [];
        results.replaceChildren();
        if (!data) return;
        const selected = ['facebook', 'instagram'].filter(network => document.getElementById(`${network}-checkbox`)?.checked);
        status.textContent = selected.length ? (data.has_meta_errors ? 'Meta no entregó todos los datos. Se muestra el historial disponible para cada cuenta.' : '') : 'Selecciona una red social para consultar sus horarios.';

        selected.forEach(network => {
            const platform = data.platforms[network];
            if (!platform) return;
            const card = node('section', '', 'rp-prediction-card');
            card.append(accountHeader(network, platform));
            if (platform.generated_at) {
                const meta = node('div', '', 'rp-prediction-meta');
                const history = node('span');
                history.append(icon('fas fa-database'), document.createTextNode(`${platform.history_count} publicaciones con métricas`));
                const consulted = node('span');
                consulted.append(icon('far fa-clock'), document.createTextNode(`Consulta Meta: ${dateLabel(platform.generated_at)}`));
                meta.append(history, consulted);
                card.append(meta);
            }

            if (platform.status === 'ok') {
                const note = node('div', '', 'rp-prediction-note');
                note.append(icon('fas fa-flask'));
                note.append(node('span', platform.experimental
                    ? (platform.training_sources?.includes('synthetic')
                        ? 'Modelo LSTM: combina entrenamiento con datos y el historial real de esta cuenta. Úsalo como recomendación.'
                        : 'Modelo LSTM basado en esta cuenta, pendiente de validación con nuevas publicaciones.')
                    : 'Estimación LSTM basada en el historial de esta cuenta.'));
                card.append(note);
                const current = platform.slots.filter(slot => new Date(slot.timestamp).getTime() > Date.now() + 60000);
                const bestSlots = [...current].sort((a, b) => b.predicted_score - a.predicted_score).slice(0, 5);
                card.append(node('p', 'Elige uno de los horarios recomendados', 'rp-prediction-title'));
                const options = node('div', '', 'rp-prediction-options');
                bestSlots.forEach((slot, index) => options.append(predictionOption(slot, index)));
                card.append(options);
                renderChart(card, current);
            } else {
                const messages = {
                    not_connected: 'Esta red no tiene una cuenta vinculada.',
                    insufficient_data: `Historial insuficiente para la LSTM. Se necesitan al menos ${platform.window || 3} publicaciones con reacciones/me gusta y comentarios disponibles.`,
                    unavailable: 'No se pudo ejecutar la predicción. Puedes usar la programación manual y consultar el historial.'
                };
                const note = node('div', '', 'rp-prediction-note');
                note.append(icon('fas fa-exclamation-circle'), node('span', messages[platform.status] || 'Predicción no disponible.'));
                card.append(note);
            }
            if (platform.historical_best?.length) {
                card.append(node('p', 'Referencia histórica de Meta: ' + platform.historical_best.map(slot => `${slot.label} (${slot.samples} publicaciones)`).join(' · '), 'rp-prediction-history'));
            }
            results.append(card);
        });
    }

    async function load() {
        if (pending) return;
        pending = true;
        refresh.disabled = true;
        refresh.querySelector('i')?.classList.add('fa-spin');
        status.textContent = 'Consultando el historial de Meta y calculando los mejores horarios…';
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
            refresh.querySelector('i')?.classList.remove('fa-spin');
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
