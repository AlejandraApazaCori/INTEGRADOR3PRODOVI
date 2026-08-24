@extends('layouts.app')

@section('title', 'Planificación de campaña')

@section('content')
@php
    $inicioCampania = \Carbon\Carbon::parse($campania->fecha_inicio);
    $finCampania = \Carbon\Carbon::parse($campania->fecha_fin);
    $totalTareas = count($eventos);
    $tareasCompletadas = collect($eventos)->where('extendedProps.estado', 'completada')->count();
    $tareasEnProgreso = collect($eventos)->where('extendedProps.estado', 'en_progreso')->count();
@endphp

<div class="planning-page">
    <div class="planning-shell">
        <nav class="planning-top-actions" aria-label="Navegación de campaña">
            <a href="{{ route('administrador.campañas.show', $campania->id) }}"><i class="fas fa-eye"></i> Ver detalle</a>
            <a href="{{ route('administrador.campañas.edit', $campania->id) }}"><i class="fas fa-pen"></i> Editar</a>
            <a href="{{ route('administrador.tareas.create', $campania->id) }}" class="is-primary"><i class="fas fa-plus"></i> Nueva tarea</a>
        </nav>

        <header class="planning-hero">
            <div class="planning-hero-overlay"></div>
            <div class="planning-hero-content">
                <div>
                    <span>Planificación de campaña</span>
                    <h1>Calendario de tareas</h1>
                    <p>{{ $campania->nombre }}</p>
                </div>
            </div>
        </header>

        <main class="planning-content">
            <section class="planning-summary" aria-label="Resumen de planificación">
                <div><span>Periodo</span><strong>{{ $inicioCampania->format('d/m/Y') }} — {{ $finCampania->format('d/m/Y') }}</strong></div>
                <div><span>Total de tareas</span><strong>{{ $totalTareas }}</strong></div>
                <div><span>En progreso</span><strong>{{ $tareasEnProgreso }}</strong></div>
                <div><span>Completadas</span><strong>{{ $tareasCompletadas }}</strong></div>
            </section>

            <section class="planning-workspace">
                <header class="planning-workspace-header">
                    <div>
                        
                        <h2>Planificación</h2>
                       
                    </div>
                    <div class="planning-view-switch" role="group" aria-label="Cambiar vista">
                        <button type="button" class="is-active" data-planning-view="calendar" aria-pressed="true"><i class="fas fa-calendar-days"></i> Calendario</button>
                        <button type="button" data-planning-view="gantt" aria-pressed="false"><i class="fas fa-chart-gantt"></i> Diagrama de Gantt</button>
                    </div>
                </header>

                <div id="calendar-view" class="planning-view">
                    <div class="calendar-toolbar">
                        <h3 id="calendar-title"></h3>
                        <div>
                            <button type="button" id="calendar-prev" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" id="calendar-today">Hoy</button>
                            <button type="button" id="calendar-next" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="calendar-scroll"><div id="calendar-grid" class="calendar-grid"></div></div>
                </div>

                <div id="gantt-view" class="planning-view hidden">
                    <div class="gantt-toolbar">
                        <div><h3>Línea de tiempo</h3><p id="gantt-range"></p></div>
                        <span>Desplázate horizontalmente para consultar todo el periodo.</span>
                    </div>
                    <div id="gantt-scroll" class="gantt-scroll"><div id="gantt-board" class="gantt-board"></div></div>
                </div>
            </section>

            <section class="planning-legend" aria-label="Leyenda de prioridades">
                <strong>Prioridad</strong>
                <span><i class="is-urgent"></i> Urgente</span>
                <span><i class="is-high"></i> Alta</span>
                <span><i class="is-medium"></i> Media</span>
                <span><i class="is-low"></i> Baja</span>
            </section>
        </main>
    </div>
</div>

<style>
    .planning-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}.planning-shell{position:relative;width:100%}.hidden{display:none!important}
    .planning-hero{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#1d4ed8}.planning-hero-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .planning-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;padding:30px 470px 30px max(48px,calc((100% - 1280px)/2))}.planning-hero-content span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.planning-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.planning-hero p{margin:5px 0 0;color:#dbeafe;font-size:.74rem;font-weight:600}
    .planning-top-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:9px}.planning-top-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}.planning-top-actions a.is-primary,.planning-top-actions a:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .planning-content{width:min(1280px,calc(100% - 48px));margin:24px auto 0}.planning-summary{display:grid;grid-template-columns:1.6fr repeat(3,1fr);overflow:hidden;margin-bottom:16px;border:1px solid #e2e5df;border-radius:12px;background:#fafbf9;box-shadow:0 5px 15px rgba(55,60,52,.04)}.planning-summary>div{padding:14px 18px;border-right:1px solid #e5e8e2}.planning-summary>div:last-child{border-right:0}.planning-summary span,.planning-summary strong{display:block}.planning-summary span{color:#7c8479;font-size:.56rem;font-weight:900;letter-spacing:.05em;text-transform:uppercase}.planning-summary strong{margin-top:5px;color:#30362e;font-size:.76rem;font-weight:900}
    .planning-workspace{overflow:hidden;border:1px solid #e1e3de;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(55,60,52,.055)}.planning-workspace-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:19px 21px 15px;border-bottom:1px solid #e8ebe5}.planning-workspace-header>div:first-child>span{display:block;margin-bottom:3px;color:#117e8c;font-size:.57rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.planning-workspace-header h2{margin:0;color:#302832;font-size:1rem;font-weight:900}.planning-workspace-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.planning-workspace-header p{margin:7px 0 0;color:#7b8378;font-size:.62rem}
    .planning-view-switch{display:flex;padding:4px;border:1px solid #dfe3dc;border-radius:10px;background:#f5f7f3}.planning-view-switch button{min-height:36px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 12px;border:0;border-radius:7px;background:transparent;color:#697067;font-size:.62rem;font-weight:900;cursor:pointer}.planning-view-switch button.is-active{background:#fff;color:#4f46e5;box-shadow:0 3px 9px rgba(55,60,52,.1)}
    .calendar-toolbar,.gantt-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 17px;border-bottom:1px solid #e8ebe5;background:#fafbf9}.calendar-toolbar h3,.gantt-toolbar h3{margin:0;color:#343a32;font-size:.8rem;font-weight:900;text-transform:capitalize}.calendar-toolbar>div{display:flex;gap:5px}.calendar-toolbar button{min-width:34px;height:34px;padding:0 10px;border:1px solid #dce0d9;border-radius:7px;background:#fff;color:#555d52;font-size:.6rem;font-weight:900;cursor:pointer}.calendar-toolbar button:hover{border-color:#4f46e5;color:#4f46e5}.calendar-scroll{overflow-x:auto}.calendar-grid{min-width:840px;display:grid;grid-template-columns:repeat(7,minmax(120px,1fr));background:#e7eae4;gap:1px}.calendar-weekday{padding:10px;background:#f3f5f1;color:#747c70;font-size:.56rem;font-weight:900;letter-spacing:.06em;text-align:center;text-transform:uppercase}.calendar-day{min-height:116px;padding:8px;background:#fff}.calendar-day.is-other{background:#fafbf9;color:#a3a9a0}.calendar-day.is-today{box-shadow:inset 0 0 0 2px #117e8c;background:#f5fbfb}.calendar-day-number{width:25px;height:25px;display:grid;place-items:center;margin-bottom:6px;border-radius:50%;color:#555d52;font-size:.64rem;font-weight:900}.calendar-day.is-today .calendar-day-number{background:#117e8c;color:#fff}.calendar-event{overflow:hidden;margin-top:4px;padding:5px 6px;border-left:3px solid var(--event-color);border-radius:5px;background:color-mix(in srgb,var(--event-color) 11%,#fff);color:#424941;font-size:.56rem;font-weight:850;text-overflow:ellipsis;white-space:nowrap;cursor:pointer}.calendar-event:hover{background:color-mix(in srgb,var(--event-color) 18%,#fff)}.calendar-more{margin-top:5px;color:#70677a;font-size:.53rem;font-weight:850}
    .gantt-toolbar h3{margin-bottom:3px}.gantt-toolbar p,.gantt-toolbar>span{margin:0;color:#7e867b;font-size:.57rem}.gantt-scroll{overflow:auto;max-height:620px}.gantt-board{min-width:100%;width:max-content;background:#fff}.gantt-row{display:grid;position:relative;min-height:54px;border-bottom:1px solid #e8ebe5}.gantt-row:last-child{border-bottom:0}.gantt-row.is-header{position:sticky;z-index:6;top:0;min-height:48px;background:#f5f7f3}.gantt-task-label,.gantt-corner{position:sticky;z-index:4;left:0;display:flex;align-items:center;border-right:1px solid #dfe3dc;background:#fff}.gantt-corner{z-index:8;padding:0 15px;background:#f5f7f3;color:#697067;font-size:.57rem;font-weight:900;text-transform:uppercase}.gantt-task-label{min-width:0;padding:8px 12px}.gantt-task-label strong,.gantt-task-label span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.gantt-task-label strong{color:#343a32;font-size:.62rem;font-weight:900}.gantt-task-label span{margin-top:3px;color:#899087;font-size:.52rem}.gantt-day-head,.gantt-cell{border-right:1px solid #eceeea}.gantt-day-head{display:grid;place-items:center;background:#f5f7f3;color:#777f74;font-size:.5rem;font-weight:800;line-height:1.2;text-align:center}.gantt-day-head b{font-size:.58rem}.gantt-cell{background:#fff}.gantt-cell.is-weekend{background:#fafbf9}.gantt-cell.is-today{background:#edf8f8}.gantt-bar{z-index:3;align-self:center;height:27px;display:flex;align-items:center;overflow:hidden;padding:0 8px;border-radius:6px;background:var(--bar-color);color:#fff;font-size:.54rem;font-weight:900;text-decoration:none;text-overflow:ellipsis;white-space:nowrap;box-shadow:0 4px 9px color-mix(in srgb,var(--bar-color) 25%,transparent)}.gantt-empty{padding:45px 24px;color:#7c8479;font-size:.68rem;text-align:center}
    .planning-legend{display:flex;align-items:center;flex-wrap:wrap;gap:18px;margin-top:14px;padding:12px 15px;border:1px solid #e2e5df;border-radius:10px;background:#fafbf9}.planning-legend strong{color:#485046;font-size:.61rem}.planning-legend span{display:flex;align-items:center;gap:6px;color:#747c70;font-size:.58rem;font-weight:750}.planning-legend i{width:9px;height:9px;border-radius:3px}.planning-legend i.is-urgent{background:#dc3545}.planning-legend i.is-high{background:#fd7e14}.planning-legend i.is-medium{background:#007bff}.planning-legend i.is-low{background:#28a745}
    @media(max-width:900px){.planning-top-actions{position:static;justify-content:center;padding:14px 24px 0}.planning-top-actions a{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.planning-top-actions a.is-primary{background:#4f46e5;color:#fff}.planning-hero{margin-top:14px}.planning-hero-content{padding:28px 24px}.planning-summary{grid-template-columns:repeat(2,1fr)}.planning-summary>div:nth-child(2){border-right:0}.planning-summary>div:nth-child(-n+2){border-bottom:1px solid #e5e8e2}}
    @media(max-width:640px){.planning-page{padding-bottom:24px}.planning-top-actions{display:grid;grid-template-columns:1fr;padding:12px}.planning-top-actions a{width:100%}.planning-hero{min-height:190px;margin-top:0}.planning-hero-content{min-height:190px;padding:26px 20px}.planning-content{width:calc(100% - 24px);margin-top:14px}.planning-summary{grid-template-columns:1fr}.planning-summary>div{border-right:0;border-bottom:1px solid #e5e8e2}.planning-summary>div:last-child{border-bottom:0}.planning-workspace-header{align-items:stretch;flex-direction:column}.planning-view-switch{display:grid;grid-template-columns:1fr 1fr}.calendar-toolbar,.gantt-toolbar{align-items:flex-start;flex-direction:column}.gantt-toolbar>span{display:none}.planning-legend{gap:12px}}
</style>

<script>
    const planningEvents = @json($eventos);
    const campaignRange = { start: @json($inicioCampania->format('Y-m-d')), end: @json($finCampania->format('Y-m-d')) };
    const monthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const weekdayNames = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const shortWeekdays = ['D','L','M','M','J','V','S'];
    let currentMonthDate;
    let ganttRendered = false;

    function parseLocalDate(value) {
        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day);
    }
    function isoDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    function addDays(date, amount) {
        const copy = new Date(date);
        copy.setDate(copy.getDate() + amount);
        return copy;
    }
    function daysBetween(start, end) {
        return Math.round((end - start) / 86400000);
    }
    function priorityColor(priority) {
        return { urgente:'#dc3545', alta:'#fd7e14', media:'#007bff', baja:'#28a745' }[priority] || '#6c757d';
    }
    function eventTooltip(event) {
        const props = event.extendedProps || {};
        return `${event.title}\nResponsable: ${props.asignado || 'Sin asignar'}\nPrioridad: ${props.prioridad || 'Sin prioridad'}\nEstado: ${(props.estado || '').replace('_',' ')}`;
    }

    function renderCalendar() {
        const year = currentMonthDate.getFullYear();
        const month = currentMonthDate.getMonth();
        document.getElementById('calendar-title').textContent = `${monthNames[month]} ${year}`;
        const grid = document.getElementById('calendar-grid');
        grid.replaceChildren();

        weekdayNames.forEach(name => {
            const header = document.createElement('div');
            header.className = 'calendar-weekday';
            header.textContent = name;
            grid.appendChild(header);
        });

        const first = new Date(year, month, 1);
        const rangeStart = addDays(first, -first.getDay());
        const todayISO = isoDate(new Date());

        for (let index = 0; index < 42; index++) {
            const date = addDays(rangeStart, index);
            const dateISO = isoDate(date);
            const cell = document.createElement('div');
            cell.className = 'calendar-day';
            if (date.getMonth() !== month) cell.classList.add('is-other');
            if (dateISO === todayISO) cell.classList.add('is-today');

            const number = document.createElement('div');
            number.className = 'calendar-day-number';
            number.textContent = date.getDate();
            cell.appendChild(number);

            const events = planningEvents.filter(event => dateISO >= event.start && dateISO <= (event.end || event.start));
            events.slice(0, 3).forEach(event => {
                const item = document.createElement('div');
                item.className = 'calendar-event';
                item.style.setProperty('--event-color', event.color || priorityColor(event.extendedProps?.prioridad));
                item.textContent = event.title;
                item.title = eventTooltip(event);
                item.addEventListener('click', () => event.url && (window.location.href = event.url));
                cell.appendChild(item);
            });
            if (events.length > 3) {
                const more = document.createElement('div');
                more.className = 'calendar-more';
                more.textContent = `+${events.length - 3} tareas más`;
                cell.appendChild(more);
            }
            grid.appendChild(cell);
        }
    }

    function renderGantt() {
        if (ganttRendered) return;
        ganttRendered = true;
        const taskStarts = planningEvents.map(event => parseLocalDate(event.start));
        const taskEnds = planningEvents.map(event => parseLocalDate(event.end || event.start));
        const start = new Date(Math.min(parseLocalDate(campaignRange.start), ...taskStarts));
        const end = new Date(Math.max(parseLocalDate(campaignRange.end), ...taskEnds));
        const totalDays = Math.max(1, daysBetween(start, end) + 1);
        const dayWidth = 32;
        const labelWidth = 250;
        const columns = `${labelWidth}px repeat(${totalDays},${dayWidth}px)`;
        const board = document.getElementById('gantt-board');
        board.style.width = `${labelWidth + totalDays * dayWidth}px`;
        document.getElementById('gantt-range').textContent = `${start.toLocaleDateString('es-BO')} — ${end.toLocaleDateString('es-BO')}`;

        const header = document.createElement('div');
        header.className = 'gantt-row is-header';
        header.style.gridTemplateColumns = columns;
        const corner = document.createElement('div');
        corner.className = 'gantt-corner';
        corner.textContent = 'Tarea / Responsable';
        corner.style.gridColumn = '1';
        header.appendChild(corner);
        for (let day = 0; day < totalDays; day++) {
            const date = addDays(start, day);
            const cell = document.createElement('div');
            cell.className = 'gantt-day-head';
            cell.style.gridColumn = String(day + 2);
            cell.innerHTML = `<span>${shortWeekdays[date.getDay()]}</span><b>${date.getDate()}</b>`;
            cell.title = date.toLocaleDateString('es-BO', { day:'numeric', month:'long', year:'numeric' });
            header.appendChild(cell);
        }
        board.appendChild(header);

        if (!planningEvents.length) {
            const empty = document.createElement('div');
            empty.className = 'gantt-empty';
            empty.textContent = 'No hay tareas registradas para mostrar en el diagrama.';
            board.appendChild(empty);
            return;
        }

        const todayISO = isoDate(new Date());
        planningEvents.forEach(event => {
            const row = document.createElement('div');
            row.className = 'gantt-row';
            row.style.gridTemplateColumns = columns;
            const label = document.createElement('div');
            label.className = 'gantt-task-label';
            label.style.gridColumn = '1';
            label.innerHTML = `<div><strong></strong><span></span></div>`;
            label.querySelector('strong').textContent = event.title;
            label.querySelector('span').textContent = `${event.extendedProps?.asignado || 'Sin asignar'} · ${(event.extendedProps?.estado || '').replace('_',' ')}`;
            row.appendChild(label);

            for (let day = 0; day < totalDays; day++) {
                const date = addDays(start, day);
                const cell = document.createElement('div');
                cell.className = 'gantt-cell';
                if ([0,6].includes(date.getDay())) cell.classList.add('is-weekend');
                if (isoDate(date) === todayISO) cell.classList.add('is-today');
                cell.style.gridColumn = String(day + 2);
                cell.style.gridRow = '1';
                row.appendChild(cell);
            }

            const taskStart = Math.max(0, daysBetween(start, parseLocalDate(event.start)));
            const taskEnd = Math.min(totalDays - 1, daysBetween(start, parseLocalDate(event.end || event.start)));
            const bar = document.createElement(event.url ? 'a' : 'div');
            bar.className = 'gantt-bar';
            bar.style.setProperty('--bar-color', event.color || priorityColor(event.extendedProps?.prioridad));
            bar.style.gridColumn = `${taskStart + 2} / ${taskEnd + 3}`;
            bar.style.gridRow = '1';
            bar.textContent = event.title;
            bar.title = eventTooltip(event);
            if (event.url) bar.href = event.url;
            row.appendChild(bar);
            board.appendChild(row);
        });
    }

    function changePlanningView(view) {
        const isCalendar = view === 'calendar';
        document.getElementById('calendar-view').classList.toggle('hidden', !isCalendar);
        document.getElementById('gantt-view').classList.toggle('hidden', isCalendar);
        document.querySelectorAll('[data-planning-view]').forEach(button => {
            const active = button.dataset.planningView === view;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        if (!isCalendar) renderGantt();
        localStorage.setItem('campaign-planning-view-{{ $campania->id }}', view);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const today = new Date();
        const campaignStart = parseLocalDate(campaignRange.start);
        const campaignEnd = parseLocalDate(campaignRange.end);
        currentMonthDate = today >= campaignStart && today <= campaignEnd ? new Date(today.getFullYear(), today.getMonth(), 1) : new Date(campaignStart.getFullYear(), campaignStart.getMonth(), 1);
        renderCalendar();
        document.getElementById('calendar-prev').addEventListener('click', () => { currentMonthDate.setMonth(currentMonthDate.getMonth() - 1); renderCalendar(); });
        document.getElementById('calendar-next').addEventListener('click', () => { currentMonthDate.setMonth(currentMonthDate.getMonth() + 1); renderCalendar(); });
        document.getElementById('calendar-today').addEventListener('click', () => { const date = new Date(); currentMonthDate = new Date(date.getFullYear(), date.getMonth(), 1); renderCalendar(); });
        document.querySelectorAll('[data-planning-view]').forEach(button => button.addEventListener('click', () => changePlanningView(button.dataset.planningView)));
        const savedView = localStorage.getItem('campaign-planning-view-{{ $campania->id }}');
        changePlanningView(savedView === 'gantt' ? 'gantt' : 'calendar');
    });
</script>
@endsection
