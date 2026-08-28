@php
    $inicioCalendario = \Carbon\Carbon::parse($campania->fecha_inicio);
    $finCalendario = \Carbon\Carbon::parse($campania->fecha_fin);
    $eventosTareas = $campania->tareas->map(function ($tarea) {
        $color = match($tarea->prioridad) {
            'urgente' => '#c94f0c',
            'alta' => '#ef6c22',
            'media' => '#5b2b76',
            'baja' => '#7da533',
            default => '#117e8c',
        };

        return [
            'id' => $tarea->id,
            'title' => $tarea->titulo,
            'start' => \Carbon\Carbon::parse($tarea->fecha_inicio)->format('Y-m-d'),
            'end' => \Carbon\Carbon::parse($tarea->fecha_limite)->format('Y-m-d'),
            'color' => $color,
            'url' => route('administrador.tareas.ver-subidas', $tarea->id),
            'extendedProps' => [
                'tipo' => 'tarea',
                'prioridad' => $tarea->prioridad,
                'estado' => $tarea->estado,
                'asignado' => $tarea->asignado?->name ?? 'Sin asignar',
            ],
        ];
    });
    $eventosReuniones = $campania->reuniones->map(function ($reunion) {
        return [
            'id' => 'reunion-'.$reunion->id,
            'title' => $reunion->titulo,
            'start' => $reunion->fecha_inicio->format('Y-m-d'),
            'end' => $reunion->fecha_fin->format('Y-m-d'),
            'color' => '#117e8c',
            'url' => $reunion->enlace,
            'external' => true,
            'extendedProps' => [
                'tipo' => 'reunión',
                'prioridad' => null,
                'estado' => $reunion->estado,
                'asignado' => $reunion->participantes->pluck('name')->join(', '),
                'hora' => $reunion->fecha_inicio->format('H:i').'–'.$reunion->fecha_fin->format('H:i'),
            ],
        ];
    });
    $eventosCalendario = $eventosTareas->concat($eventosReuniones)->values();
@endphp

<section class="embedded-planning-workspace">
    <header class="embedded-planning-header">
        <h2>Planificación</h2>
        <div class="embedded-planning-switch" role="group" aria-label="Cambiar vista de planificación">
            <button type="button" data-embedded-planning-view="calendar" aria-pressed="false"><i class="fas fa-calendar-days"></i> Calendario</button>
            <button type="button" class="is-active" data-embedded-planning-view="gantt" aria-pressed="true"><i class="fas fa-chart-gantt"></i> Diagrama de Gantt</button>
        </div>
    </header>

    <div id="embedded-calendar-view" class="embedded-planning-view is-hidden">
        <div class="embedded-calendar-toolbar">
            <h3 id="embedded-calendar-title"></h3>
            <div>
                <button type="button" id="embedded-calendar-prev" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                <button type="button" id="embedded-calendar-today">Hoy</button>
                <button type="button" id="embedded-calendar-next" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="embedded-calendar-scroll"><div id="embedded-calendar-grid" class="embedded-calendar-grid"></div></div>
    </div>

    <div id="embedded-gantt-view" class="embedded-planning-view">
        <div class="embedded-gantt-toolbar">
            <div><h3>Línea de tiempo</h3><p id="embedded-gantt-range"></p></div>
            <span>Desplázate horizontalmente para consultar todo el periodo.</span>
        </div>
        <div id="embedded-gantt-scroll" class="embedded-gantt-scroll"><div id="embedded-gantt-board" class="embedded-gantt-board"></div></div>
    </div>
</section>

<section class="embedded-planning-legend" aria-label="Leyenda de actividades">
    <strong>Leyenda</strong>
    <span><i class="is-meeting"></i> Reunión</span>
    <span><i class="is-urgent"></i> Urgente</span>
    <span><i class="is-high"></i> Alta</span>
    <span><i class="is-medium"></i> Media</span>
    <span><i class="is-low"></i> Baja</span>
</section>

<style>
    .embedded-planning-workspace{overflow:hidden;border:1px solid #e1e3de;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(55,60,52,.04)}.embedded-planning-view.is-hidden{display:none!important}
    .embedded-planning-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:19px 21px 15px;border-bottom:1px solid #e8ebe5}.embedded-planning-header h2{margin:0;color:#302832;font-size:1rem;font-weight:900}.embedded-planning-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#ef6c22}
    .embedded-planning-switch{display:flex;padding:4px;border:1px solid #f1d2c2;border-radius:10px;background:#fff7f2}.embedded-planning-switch button{min-height:36px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 12px;border:0;border-radius:7px;background:transparent;color:#697067;font-size:.62rem;font-weight:900;cursor:pointer}.embedded-planning-switch button.is-active{background:#fff;color:#ef6c22;box-shadow:0 3px 9px rgba(201,79,12,.14)}
    .embedded-calendar-toolbar,.embedded-gantt-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 17px;border-bottom:1px solid #e8ebe5;background:#fafbf9}.embedded-calendar-toolbar h3,.embedded-gantt-toolbar h3{margin:0;color:#343a32;font-size:.8rem;font-weight:900;text-transform:capitalize}.embedded-calendar-toolbar>div{display:flex;gap:5px}.embedded-calendar-toolbar button{min-width:34px;height:34px;padding:0 10px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#555d52;font-size:.6rem;font-weight:900;cursor:pointer}.embedded-calendar-toolbar button:hover{border-color:#ef6c22;color:#ef6c22}
    .embedded-calendar-scroll{overflow-x:auto}.embedded-calendar-grid{min-width:840px;display:grid;grid-template-columns:repeat(7,minmax(120px,1fr));gap:1px;background:#e7eae4}.embedded-calendar-weekday{padding:10px;background:#f3f5f1;color:#747c70;font-size:.56rem;font-weight:900;letter-spacing:.06em;text-align:center;text-transform:uppercase}.embedded-calendar-day{min-height:116px;padding:8px;background:#fff}.embedded-calendar-day.is-other{background:#fafbf9;color:#a3a9a0}.embedded-calendar-day.is-today{box-shadow:inset 0 0 0 2px #ef6c22;background:#fff7f2}.embedded-calendar-day-number{width:25px;height:25px;display:grid;place-items:center;margin-bottom:6px;border-radius:50%;color:#555d52;font-size:.64rem;font-weight:900}.embedded-calendar-day.is-today .embedded-calendar-day-number{background:#ef6c22;color:#fff}.embedded-calendar-event{overflow:hidden;margin-top:4px;padding:5px 6px;border-left:3px solid var(--event-color);border-radius:5px;background:color-mix(in srgb,var(--event-color) 11%,#fff);color:#424941;font-size:.56rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap;cursor:pointer}.embedded-calendar-event:hover{background:color-mix(in srgb,var(--event-color) 18%,#fff)}.embedded-calendar-more{margin-top:5px;color:#70677a;font-size:.53rem;font-weight:850}
    .embedded-gantt-toolbar h3{margin-bottom:3px}.embedded-gantt-toolbar p,.embedded-gantt-toolbar>span{margin:0;color:#7e867b;font-size:.57rem}.embedded-gantt-scroll{overflow:auto;max-height:620px}.embedded-gantt-board{min-width:100%;width:max-content;background:#fff}.embedded-gantt-row{position:relative;min-height:54px;display:grid;border-bottom:1px solid #e8ebe5}.embedded-gantt-row:last-child{border-bottom:0}.embedded-gantt-row.is-header{position:sticky;z-index:6;top:0;min-height:48px;background:#f5f7f3}.embedded-gantt-label,.embedded-gantt-corner{position:sticky;z-index:4;left:0;display:flex;align-items:center;border-right:1px solid #dfe3dc;background:#fff}.embedded-gantt-corner{z-index:8;padding:0 15px;background:#f5f7f3;color:#697067;font-size:.57rem;font-weight:900;text-transform:uppercase}.embedded-gantt-label{min-width:0;padding:8px 12px}.embedded-gantt-label strong,.embedded-gantt-label span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.embedded-gantt-label strong{color:#343a32;font-size:.62rem;font-weight:900}.embedded-gantt-label span{margin-top:3px;color:#899087;font-size:.52rem}.embedded-gantt-day,.embedded-gantt-cell{border-right:1px solid #eceeea}.embedded-gantt-day{display:grid;place-items:center;background:#f5f7f3;color:#777f74;font-size:.5rem;font-weight:800;line-height:1.2;text-align:center}.embedded-gantt-day b{font-size:.58rem}.embedded-gantt-cell{background:#fff}.embedded-gantt-cell.is-weekend{background:#fafbf9}.embedded-gantt-cell.is-today{background:#fff2e9}.embedded-gantt-bar{z-index:3;align-self:center;height:27px;display:flex;align-items:center;overflow:hidden;padding:0 8px;border-radius:6px;background:var(--bar-color);color:#fff;font-size:.54rem;font-weight:900;text-decoration:none;text-overflow:ellipsis;white-space:nowrap;box-shadow:0 4px 9px color-mix(in srgb,var(--bar-color) 25%,transparent)}.embedded-gantt-empty{padding:45px 24px;color:#7c8479;font-size:.68rem;text-align:center}
    .embedded-planning-legend{display:flex;align-items:center;flex-wrap:wrap;gap:18px;margin-top:14px;padding:12px 15px;border:1px solid #e2e5df;border-radius:10px;background:#fafbf9}.embedded-planning-legend strong{color:#485046;font-size:.61rem}.embedded-planning-legend span{display:flex;align-items:center;gap:6px;color:#747c70;font-size:.58rem;font-weight:750}.embedded-planning-legend i{width:9px;height:9px;border-radius:3px}.embedded-planning-legend i.is-meeting{background:#117e8c}.embedded-planning-legend i.is-urgent{background:#c94f0c}.embedded-planning-legend i.is-high{background:#ef6c22}.embedded-planning-legend i.is-medium{background:#5b2b76}.embedded-planning-legend i.is-low{background:#7da533}
    @media(max-width:640px){.embedded-planning-header{align-items:stretch;flex-direction:column}.embedded-planning-switch{display:grid;grid-template-columns:1fr 1fr}.embedded-calendar-toolbar,.embedded-gantt-toolbar{align-items:flex-start;flex-direction:column}.embedded-gantt-toolbar>span{display:none}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const events = @json($eventosCalendario);
    const range = { start: @json($inicioCalendario->format('Y-m-d')), end: @json($finCalendario->format('Y-m-d')) };
    const months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const weekdays = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const shortWeekdays = ['D','L','M','M','J','V','S'];
    let currentMonth;
    let ganttRendered = false;

    const parseDate = value => { const [year, month, day] = value.split('-').map(Number); return new Date(year, month - 1, day); };
    const isoDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const addDays = (date, amount) => { const copy = new Date(date); copy.setDate(copy.getDate() + amount); return copy; };
    const daysBetween = (start, end) => Math.round((end - start) / 86400000);
    const tooltip = event => event.extendedProps?.tipo === 'reunión'
        ? `${event.title}\nHorario: ${event.extendedProps?.hora || ''}\nParticipantes: ${event.extendedProps?.asignado || 'Sin participantes'}\nEstado: ${event.extendedProps?.estado || 'agendada'}`
        : `${event.title}\nResponsable: ${event.extendedProps?.asignado || 'Sin asignar'}\nPrioridad: ${event.extendedProps?.prioridad || 'Sin prioridad'}\nEstado: ${(event.extendedProps?.estado || '').replace('_', ' ')}`;

    function renderCalendar() {
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        document.getElementById('embedded-calendar-title').textContent = `${months[month]} ${year}`;
        const grid = document.getElementById('embedded-calendar-grid');
        grid.replaceChildren();
        weekdays.forEach(name => { const header = document.createElement('div'); header.className = 'embedded-calendar-weekday'; header.textContent = name; grid.appendChild(header); });
        const first = new Date(year, month, 1);
        const start = addDays(first, -first.getDay());
        const today = isoDate(new Date());
        for (let index = 0; index < 42; index++) {
            const date = addDays(start, index);
            const dateISO = isoDate(date);
            const cell = document.createElement('div');
            cell.className = 'embedded-calendar-day';
            if (date.getMonth() !== month) cell.classList.add('is-other');
            if (dateISO === today) cell.classList.add('is-today');
            const number = document.createElement('div'); number.className = 'embedded-calendar-day-number'; number.textContent = date.getDate(); cell.appendChild(number);
            const dayEvents = events.filter(event => dateISO >= event.start && dateISO <= (event.end || event.start));
            dayEvents.slice(0, 3).forEach(event => { const item = document.createElement('div'); item.className = 'embedded-calendar-event'; item.style.setProperty('--event-color', event.color); item.textContent = `${event.extendedProps?.tipo === 'reunión' ? '📹 ' : ''}${event.title}`; item.title = tooltip(event); item.addEventListener('click', () => { if (!event.url) return; event.external ? window.open(event.url, '_blank', 'noopener') : (window.location.href = event.url); }); cell.appendChild(item); });
            if (dayEvents.length > 3) { const more = document.createElement('div'); more.className = 'embedded-calendar-more'; more.textContent = `+${dayEvents.length - 3} eventos más`; cell.appendChild(more); }
            grid.appendChild(cell);
        }
    }

    function renderGantt() {
        if (ganttRendered) return;
        ganttRendered = true;
        const starts = events.map(event => parseDate(event.start));
        const ends = events.map(event => parseDate(event.end || event.start));
        const start = new Date(Math.min(parseDate(range.start), ...starts));
        const end = new Date(Math.max(parseDate(range.end), ...ends));
        const totalDays = Math.max(1, daysBetween(start, end) + 1);
        const columns = `250px repeat(${totalDays},32px)`;
        const board = document.getElementById('embedded-gantt-board');
        board.style.width = `${250 + totalDays * 32}px`;
        document.getElementById('embedded-gantt-range').textContent = `${start.toLocaleDateString('es-BO')} — ${end.toLocaleDateString('es-BO')}`;
        const header = document.createElement('div'); header.className = 'embedded-gantt-row is-header'; header.style.gridTemplateColumns = columns;
        const corner = document.createElement('div'); corner.className = 'embedded-gantt-corner'; corner.textContent = 'Actividad / Participantes'; corner.style.gridColumn = '1'; header.appendChild(corner);
        for (let day = 0; day < totalDays; day++) { const date = addDays(start, day); const cell = document.createElement('div'); cell.className = 'embedded-gantt-day'; cell.style.gridColumn = String(day + 2); cell.innerHTML = `<span>${shortWeekdays[date.getDay()]}</span><b>${date.getDate()}</b>`; header.appendChild(cell); }
        board.appendChild(header);
        if (!events.length) { const empty = document.createElement('div'); empty.className = 'embedded-gantt-empty'; empty.textContent = 'No hay tareas ni reuniones para mostrar en el diagrama.'; board.appendChild(empty); return; }
        const today = isoDate(new Date());
        events.forEach(event => {
            const row = document.createElement('div'); row.className = 'embedded-gantt-row'; row.style.gridTemplateColumns = columns;
            const label = document.createElement('div'); label.className = 'embedded-gantt-label'; label.style.gridColumn = '1'; label.innerHTML = '<div><strong></strong><span></span></div>'; label.querySelector('strong').textContent = event.title; label.querySelector('span').textContent = `${event.extendedProps?.asignado || 'Sin asignar'} · ${(event.extendedProps?.estado || '').replace('_', ' ')}`; row.appendChild(label);
            for (let day = 0; day < totalDays; day++) { const date = addDays(start, day); const cell = document.createElement('div'); cell.className = 'embedded-gantt-cell'; if ([0, 6].includes(date.getDay())) cell.classList.add('is-weekend'); if (isoDate(date) === today) cell.classList.add('is-today'); cell.style.gridColumn = String(day + 2); cell.style.gridRow = '1'; row.appendChild(cell); }
            const taskStart = Math.max(0, daysBetween(start, parseDate(event.start))); const taskEnd = Math.min(totalDays - 1, daysBetween(start, parseDate(event.end || event.start)));
            const bar = document.createElement(event.url ? 'a' : 'div'); bar.className = 'embedded-gantt-bar'; bar.style.setProperty('--bar-color', event.color); bar.style.gridColumn = `${taskStart + 2} / ${taskEnd + 3}`; bar.style.gridRow = '1'; bar.textContent = `${event.extendedProps?.tipo === 'reunión' ? '📹 ' : ''}${event.title}`; bar.title = tooltip(event); if (event.url) { bar.href = event.url; if (event.external) { bar.target = '_blank'; bar.rel = 'noopener noreferrer'; } } row.appendChild(bar); board.appendChild(row);
        });
    }

    function changeView(view) {
        const calendar = view === 'calendar';
        document.getElementById('embedded-calendar-view').classList.toggle('is-hidden', !calendar);
        document.getElementById('embedded-gantt-view').classList.toggle('is-hidden', calendar);
        document.querySelectorAll('[data-embedded-planning-view]').forEach(button => { const active = button.dataset.embeddedPlanningView === view; button.classList.toggle('is-active', active); button.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        if (!calendar) renderGantt();
    }

    const today = new Date();
    const campaignStart = parseDate(range.start);
    const campaignEnd = parseDate(range.end);
    currentMonth = today >= campaignStart && today <= campaignEnd ? new Date(today.getFullYear(), today.getMonth(), 1) : new Date(campaignStart.getFullYear(), campaignStart.getMonth(), 1);
    renderCalendar();
    changeView('gantt');
    document.getElementById('embedded-calendar-prev').addEventListener('click', () => { currentMonth.setMonth(currentMonth.getMonth() - 1); renderCalendar(); });
    document.getElementById('embedded-calendar-next').addEventListener('click', () => { currentMonth.setMonth(currentMonth.getMonth() + 1); renderCalendar(); });
    document.getElementById('embedded-calendar-today').addEventListener('click', () => { const date = new Date(); currentMonth = new Date(date.getFullYear(), date.getMonth(), 1); renderCalendar(); });
    document.querySelectorAll('[data-embedded-planning-view]').forEach(button => button.addEventListener('click', () => changeView(button.dataset.embeddedPlanningView)));
});
</script>
