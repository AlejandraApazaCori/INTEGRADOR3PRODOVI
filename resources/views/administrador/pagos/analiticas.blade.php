@extends('layouts.app')

@section('title', 'Analíticas de Pagos')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <div class="payments-analytics-page">
        <div class="payments-analytics-shell">
            <!-- Action buttons -->
            <div class="analytics-tabs flex flex-wrap gap-3">
                <a href="{{ route('administrador.pagos.index') }}" class="btn-action btn-blue">
                    <i class="fas fa-table-columns"></i>
                    General
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </a>
                <a href="{{ route('administrador.pagos.analiticas') }}" class="btn-action btn-indigo">
                    <i class="fas fa-chart-line"></i>
                    Analíticas
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </a>
                <a href="{{ route('administrador.pagos.mensual.pdf') }}" target="_blank" class="btn-action btn-purple">
                    <i class="fas fa-file-pdf"></i>
                    Reporte mensual
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </a>
                <a href="{{ route('administrador.pagos.manual.crear') }}" class="btn-action analytics-register-action">
                    <i class="fas fa-plus-circle"></i>Registrar pago
                </a>
            </div>

            <!-- Banner con fondo geométrico -->
            <div class="analytics-hero overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay absolute inset-0"></div>
                <div class="relative z-10 px-8 py-8">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Analíticas de Pagos</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Visualiza y analiza los pagos realizados en tu plataforma</p>
                        </div>
                        <a href="{{ route('administrador.pagos.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                            <i class="fas fa-arrow-left mr-2 text-sm"></i>
                            Volver a Pagos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="analytics-filters bg-white rounded-2xl p-6" style="display:none" aria-hidden="true">
                <div class="flex items-center mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg p-3 mr-4 shadow-lg">
                        <i class="fas fa-filter text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Filtros de Búsqueda</h2>
                        <p class="text-gray-600 text-sm">Ajusta el rango y los criterios para refinar las analíticas</p>
                    </div>
                </div>

                <form id="filterForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="clientName" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1"></i> Nombre del Cliente
                            </label>
                            <input type="text" id="clientName" name="clientName" placeholder="Buscar por nombre..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        </div>

                        <div>
                            <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-list mr-1"></i> Plan
                            </label>
                            <select id="plan" name="plan" class="analytics-native-control" tabindex="-1" aria-hidden="true">
                                <option value="">Todos los planes</option>
                                @foreach($planes as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="analytics-custom-dropdown" data-analytics-select="plan">
                                <button type="button" class="analytics-custom-trigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="analytics-control-icon"><i class="fas fa-layer-group"></i></span>
                                    <span data-analytics-select-label>Todos los planes</span>
                                    <i class="fas fa-chevron-down analytics-control-chevron"></i>
                                </button>
                                <div class="analytics-custom-menu is-hidden" role="listbox">
                                    <button type="button" data-value="">Todos los planes</button>
                                    @foreach($planes as $plan)
                                        <button type="button" data-value="{{ $plan->id }}">{{ $plan->nombre }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="subscriptionStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-1"></i> Estado de Suscripción
                            </label>
                            <select id="subscriptionStatus" name="subscriptionStatus" class="analytics-native-control" tabindex="-1" aria-hidden="true">
                                <option value="">Todos los estados</option>
                                <option value="active">Solo activas</option>
                                <option value="completed">Finalizadas</option>
                                <option value="cancelled">Canceladas</option>
                                <option value="all">Todas</option>
                            </select>
                            <div class="analytics-custom-dropdown" data-analytics-select="subscriptionStatus">
                                <button type="button" class="analytics-custom-trigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="analytics-control-icon"><i class="fas fa-toggle-on"></i></span>
                                    <span data-analytics-select-label>Todos los estados</span>
                                    <i class="fas fa-chevron-down analytics-control-chevron"></i>
                                </button>
                                <div class="analytics-custom-menu is-hidden" role="listbox">
                                    <button type="button" data-value="">Todos los estados</button>
                                    <button type="button" data-value="active">Solo activas</button>
                                    <button type="button" data-value="completed">Finalizadas</button>
                                    <button type="button" data-value="cancelled">Canceladas</button>
                                    <button type="button" data-value="all">Todas</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Desde
                            </label>
                            <input type="hidden" id="startDate" name="startDate" value="{{ $defaultStartDate }}">
                            <div class="analytics-date-control" data-analytics-calendar="startDate">
                                <button type="button" class="analytics-custom-trigger" aria-expanded="false">
                                    <span class="analytics-control-icon"><i class="fas fa-calendar-days"></i></span>
                                    <span data-calendar-label>Seleccionar fecha</span>
                                    <i class="fas fa-chevron-down analytics-control-chevron"></i>
                                </button>
                                <div class="analytics-calendar is-hidden">
                                    <div class="analytics-calendar-head"><button type="button" data-calendar-prev><i class="fas fa-chevron-left"></i></button><strong data-calendar-month></strong><button type="button" data-calendar-next><i class="fas fa-chevron-right"></i></button></div>
                                    <div class="analytics-calendar-week"><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span></div>
                                    <div class="analytics-calendar-days" data-calendar-days></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Hasta
                            </label>
                            <input type="hidden" id="endDate" name="endDate" value="{{ $defaultEndDate }}">
                            <div class="analytics-date-control" data-analytics-calendar="endDate">
                                <button type="button" class="analytics-custom-trigger" aria-expanded="false">
                                    <span class="analytics-control-icon"><i class="fas fa-calendar-check"></i></span>
                                    <span data-calendar-label>Seleccionar fecha</span>
                                    <i class="fas fa-chevron-down analytics-control-chevron"></i>
                                </button>
                                <div class="analytics-calendar is-hidden">
                                    <div class="analytics-calendar-head"><button type="button" data-calendar-prev><i class="fas fa-chevron-left"></i></button><strong data-calendar-month></strong><button type="button" data-calendar-next><i class="fas fa-chevron-right"></i></button></div>
                                    <div class="analytics-calendar-week"><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span></div>
                                    <div class="analytics-calendar-days" data-calendar-days></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" id="resetFilters"
                            class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 transition-all duration-200 font-medium">
                            <i class="fas fa-redo mr-2"></i> Volver al mes actual
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-lg hover:shadow-indigo-200/50">
                            <i class="fas fa-search mr-2"></i> Buscar y actualizar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resultados -->
            <div id="resultsSection" class="analytics-results bg-white rounded-2xl p-6">
                <div id="appliedFilters" class="mb-6 flex flex-wrap gap-2" style="display:none" aria-hidden="true"></div>

                <!-- Tarjetas de resumen mejoradas -->
                <div id="summaryCards" class="analytics-summary-grid grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="analytics-summary-card analytics-summary-income rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Total de Ingresos</p>
                                <p class="text-3xl font-bold mt-1" id="totalIncomeSummary">-</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
                                <i class="fas fa-dollar-sign text-3xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="analytics-summary-card analytics-summary-plan rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Plan Más Contratado</p>
                                <p class="text-2xl font-bold mt-1 truncate" id="mostHiredPlanSummary">-</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
                                <i class="fas fa-trophy text-3xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="analytics-summary-card analytics-summary-records rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Total de Registros</p>
                                <p class="text-3xl font-bold mt-1" id="totalRecordsSummary">-</p>
                            </div>
                            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
                                <i class="fas fa-chart-bar text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gr?ficas -->
                <div id="chartsSection" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="chart-card">
                        <h3 class="chart-card__title">Ingresos por mes</h3>
                        <div class="chart-card__canvas"><canvas id="monthlyIncomeChart"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h3 class="chart-card__title">Ingresos por plan</h3>
                        <div class="chart-card__canvas"><canvas id="incomeByPlanChart"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h3 class="chart-card__title">Pagos por estado</h3>
                        <div class="chart-card__canvas"><canvas id="paymentStatusChart"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <h3 class="chart-card__title">Método de pago más usado</h3>
                        <div class="chart-card__canvas"><canvas id="paymentMethodChart"></canvas></div>
                    </div>
                </div>

                <!-- Tabla mejorada -->
                <div class="analytics-table-wrap overflow-x-auto rounded-xl" style="display:none" aria-hidden="true">
                    <table class="analytics-table min-w-full">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Cliente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Plan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Monto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha Inicio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha Fin</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody" class="bg-white divide-y divide-gray-100"></tbody>
                    </table>
                </div>

                <div id="paginationContainer" class="mt-4 flex justify-center" style="display:none" aria-hidden="true"></div>
            </div>
        </div>
    </div>

    <!-- Modal Comprobante -->
    <div id="modalComprobante" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <header class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                            <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>
                            Comprobante de Pago
                        </h3>
                        <button onclick="cerrarModal()" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </header>
                <main id="contenidoComprobante" class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto"></main>
                <footer class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button onclick="cerrarModal()" type="button"
                            class="inline-flex justify-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-gray-600 hover:bg-gray-700 transition-all duration-200">
                            Cerrar
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const resetButton = document.getElementById('resetFilters');
            const resultsTableBody = document.getElementById('resultsTableBody');
            const paginationContainer = document.getElementById('paginationContainer');
            const appliedFilters = document.getElementById('appliedFilters');
            const defaultStartDate = @json($defaultStartDate);
            const defaultEndDate = @json($defaultEndDate);
            let currentPage = 1;
            let totalPages = 1;
            let chartInstances = {};
            const analyticsSelectControllers = [];
            const analyticsCalendarControllers = [];

            function closeAnalyticsControls(except = null) {
                document.querySelectorAll('.analytics-custom-dropdown,.analytics-date-control').forEach(control => {
                    if (control === except) return;
                    control.classList.remove('is-open');
                    control.querySelector('.analytics-custom-menu,.analytics-calendar')?.classList.add('is-hidden');
                    control.querySelector('.analytics-custom-trigger')?.setAttribute('aria-expanded', 'false');
                });
            }

            document.querySelectorAll('[data-analytics-select]').forEach(dropdown => {
                const nativeSelect = document.getElementById(dropdown.dataset.analyticsSelect);
                const trigger = dropdown.querySelector('.analytics-custom-trigger');
                const menu = dropdown.querySelector('.analytics-custom-menu');
                const label = dropdown.querySelector('[data-analytics-select-label]');
                const options = [...menu.querySelectorAll('button[data-value]')];

                function close() {
                    dropdown.classList.remove('is-open');
                    menu.classList.add('is-hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                function sync() {
                    const option = options.find(item => item.dataset.value === nativeSelect.value) || options[0];
                    label.textContent = option.textContent.trim();
                    options.forEach(item => {
                        const selected = item === option;
                        item.classList.toggle('is-selected', selected);
                        item.setAttribute('aria-selected', selected ? 'true' : 'false');
                    });
                }

                trigger.addEventListener('click', () => {
                    const opening = menu.classList.contains('is-hidden');
                    closeAnalyticsControls(dropdown);
                    dropdown.classList.toggle('is-open', opening);
                    menu.classList.toggle('is-hidden', !opening);
                    trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
                });

                options.forEach(option => option.addEventListener('click', () => {
                    nativeSelect.value = option.dataset.value;
                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    sync();
                    close();
                }));

                sync();
                analyticsSelectControllers.push({ sync });
            });

            function parseLocalDate(value) {
                if (!value) return new Date();
                const [year, month, day] = value.split('-').map(Number);
                return new Date(year, month - 1, day);
            }

            function toDateValue(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function calendarLabel(value) {
                if (!value) return 'Seleccionar fecha';
                return parseLocalDate(value).toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' }).replace('.', '');
            }

            document.querySelectorAll('[data-analytics-calendar]').forEach(control => {
                const input = document.getElementById(control.dataset.analyticsCalendar);
                const trigger = control.querySelector('.analytics-custom-trigger');
                const calendar = control.querySelector('.analytics-calendar');
                const label = control.querySelector('[data-calendar-label]');
                const monthLabel = control.querySelector('[data-calendar-month]');
                const daysContainer = control.querySelector('[data-calendar-days]');
                let viewDate = parseLocalDate(input.value);

                function close() {
                    control.classList.remove('is-open');
                    calendar.classList.add('is-hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                function render() {
                    label.textContent = calendarLabel(input.value);
                    monthLabel.textContent = viewDate.toLocaleDateString('es-BO', { month: 'long', year: 'numeric' });
                    daysContainer.innerHTML = '';
                    const year = viewDate.getFullYear();
                    const month = viewDate.getMonth();
                    const firstDayOffset = (new Date(year, month, 1).getDay() + 6) % 7;
                    const totalDays = new Date(year, month + 1, 0).getDate();
                    const startValue = document.getElementById('startDate').value;
                    const endValue = document.getElementById('endDate').value;

                    for (let blank = 0; blank < firstDayOffset; blank++) daysContainer.append(document.createElement('span'));
                    for (let day = 1; day <= totalDays; day++) {
                        const date = new Date(year, month, day);
                        const value = toDateValue(date);
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = day;
                        const invalidStart = input.id === 'startDate' && endValue && value > endValue;
                        const invalidEnd = input.id === 'endDate' && startValue && value < startValue;
                        button.disabled = invalidStart || invalidEnd;
                        button.classList.toggle('is-selected', input.value === value);
                        button.classList.toggle('is-today', value === toDateValue(new Date()));
                        button.addEventListener('click', () => {
                            input.value = value;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                            analyticsCalendarControllers.forEach(controller => controller.render());
                            close();
                        });
                        daysContainer.append(button);
                    }
                }

                trigger.addEventListener('click', () => {
                    const opening = calendar.classList.contains('is-hidden');
                    closeAnalyticsControls(control);
                    if (opening) viewDate = parseLocalDate(input.value);
                    control.classList.toggle('is-open', opening);
                    calendar.classList.toggle('is-hidden', !opening);
                    trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    render();
                });
                control.querySelector('[data-calendar-prev]').addEventListener('click', () => { viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1); render(); });
                control.querySelector('[data-calendar-next]').addEventListener('click', () => { viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1); render(); });
                const controller = { render, resetView: () => { viewDate = parseLocalDate(input.value); render(); } };
                analyticsCalendarControllers.push(controller);
                render();
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('.analytics-custom-dropdown,.analytics-date-control')) closeAnalyticsControls();
            });

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                currentPage = 1;
                fetchFilteredResults();
            });

            resetButton.addEventListener('click', function () {
                filterForm.reset();
                document.getElementById('startDate').value = defaultStartDate;
                document.getElementById('endDate').value = defaultEndDate;
                analyticsSelectControllers.forEach(controller => controller.sync());
                analyticsCalendarControllers.forEach(controller => controller.resetView());
                currentPage = 1;
                fetchFilteredResults();
            });

            function fetchFilteredResults() {
                const params = getFormParams();
                params.append('page', currentPage);

                resultsTableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando resultados...</td></tr>';

                fetch(`/administrador/pagos/buscar?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'No se pudieron cargar las analíticas.');
                        }

                        renderResults(data.data);
                        renderPagination(data.pagination);
                        renderSummary(data.summary);
                        renderCharts(data.charts);
                        renderAppliedFilters(data.filters);
                    })
                    .catch(error => {
                        resultsTableBody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">${error.message}</td></tr>`;
                    });
            }

            function getFormParams() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();

                for (const [key, value] of formData.entries()) {
                    if (value) {
                        params.append(key, value);
                    }
                }

                return params;
            }

            function renderAppliedFilters(filters) {
                const chips = [];
                const planSelect = document.getElementById('plan');
                const statusSelect = document.getElementById('subscriptionStatus');

                chips.push(`Rango: ${formatDate(filters.startDate || defaultStartDate)} al ${formatDate(filters.endDate || defaultEndDate)}`);

                if (filters.clientName) {
                    chips.push(`Cliente: ${filters.clientName}`);
                }

                if (filters.plan) {
                    chips.push(`Plan: ${planSelect.options[planSelect.selectedIndex].text}`);
                }

                if (filters.subscriptionStatus) {
                    chips.push(`Estado: ${statusSelect.options[statusSelect.selectedIndex].text}`);
                }

                appliedFilters.innerHTML = chips.map(chip => `
                    <span class="inline-flex items-center rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-sm border border-indigo-100">${chip}</span>
                `).join('');
            }

            function formatDate(value) {
                if (!value) {
                    return 'Sin definir';
                }

                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year}`;
            }

            function renderResults(results) {
                if (!results.length) {
                    resultsTableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron resultados para el rango consultado.</td></tr>';
                    return;
                }

                resultsTableBody.innerHTML = results.map((result, index) => {
                    let statusBadge = '';
                    switch (result.estado) {
                        case 'activa':
                            statusBadge = '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">Activa</span>';
                            break;
                        case 'finalizada':
                            statusBadge = '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200">Finalizada</span>';
                            break;
                        case 'cancelada':
                            statusBadge = '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">Cancelada</span>';
                            break;
                        default:
                            statusBadge = '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">Pendiente</span>';
                    }

                    const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';

                    return `
                        <tr class="${rowClass} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600 border-r border-gray-100">#${result.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium border-r border-gray-100">${result.usuario}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">${result.plan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">${result.monto}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">${result.fecha_inicio}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">${result.fecha_fin}</td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <button onclick="verComprobante(${result.id})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                        <i class="fas fa-file-invoice mr-1.5"></i>Recibo
                                    </button>
                                    <button onclick="descargarComprobante(${result.id})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                        <i class="fas fa-download mr-1.5"></i>Descargar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function renderSummary(summary) {
                document.getElementById('totalIncomeSummary').textContent = summary.total_income;
                document.getElementById('mostHiredPlanSummary').textContent = summary.most_hired_plan;
                document.getElementById('totalRecordsSummary').textContent = summary.total_records;
            }

            function renderCharts(charts) {
                destroyCharts();

                renderChart('paymentStatusChart', {
                    type: 'pie',
                    data: {
                        labels: Object.keys(charts.payment_status_distribution || {}).map(formatPaymentStatusLabel),
                        datasets: [{
                            data: Object.values(charts.payment_status_distribution || {}),
                            backgroundColor: ['#E3A122', '#15936F', '#D95D5D', '#78909F'],
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: buildCircularOptions()
                });

                renderChart('monthlyIncomeChart', {
                    type: 'line',
                    data: {
                        labels: charts.monthly_income?.labels || [],
                        datasets: [{
                            label: 'Ingresos',
                            data: charts.monthly_income?.values || [],
                            borderColor: '#1593B5',
                            backgroundColor: 'rgba(21, 147, 181, 0.16)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: buildCartesianOptions({ currency: true })
                });

                renderChart('incomeByPlanChart', {
                    type: 'bar',
                    data: {
                        labels: Object.keys(charts.income_by_plan || {}),
                        datasets: [{
                            label: 'Ingresos por plan',
                            data: Object.values(charts.income_by_plan || {}),
                            backgroundColor: ['#2563B9', '#1593B5', '#117E8C', '#7DA533', '#E3A122', '#6B5DB2'],
                            borderRadius: 12,
                            maxBarThickness: 46
                        }]
                    },
                    options: buildCartesianOptions({ currency: true, legend: false })
                });

                renderChart('paymentMethodChart', {
                    type: 'bar',
                    data: {
                        labels: Object.keys(charts.payment_method_distribution || {}),
                        datasets: [{
                            label: 'Cantidad de pagos',
                            data: Object.values(charts.payment_method_distribution || {}),
                            backgroundColor: ['#1593B5', '#2563B9', '#E37225', '#6B5DB2', '#E3A122'],
                            borderRadius: 12,
                            maxBarThickness: 48
                        }]
                    },
                    options: buildCartesianOptions({ integer: true, legend: false })
                });

            }

            function destroyCharts() {
                Object.values(chartInstances).forEach(instance => instance.destroy());
                chartInstances = {};
            }

            function renderChart(canvasId, config) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    return;
                }

                const container = canvas.parentElement;
                if (container) {
                    const emptyState = container.querySelector('.chart-card__empty');
                    if (emptyState) {
                        emptyState.remove();
                    }
                }

                const labels = config?.data?.labels || [];
                const datasets = config?.data?.datasets || [];
                const hasData = datasets.some(dataset => Array.isArray(dataset.data) && dataset.data.some(value => Number(value) > 0));

                if (!labels.length || !hasData) {
                    renderEmptyChartState(canvas, 'Sin datos para este gr?fico con los filtros actuales');
                    return;
                }

                chartInstances[canvasId] = new Chart(canvas.getContext('2d'), config);
            }

            function renderEmptyChartState(canvas, message) {
                const container = canvas.parentElement;
                if (!container) {
                    return;
                }

                let emptyState = container.querySelector('.chart-card__empty');
                if (!emptyState) {
                    emptyState = document.createElement('div');
                    emptyState.className = 'chart-card__empty';
                    container.appendChild(emptyState);
                }
                emptyState.textContent = message;
            }

            function buildCircularOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 16
                            }
                        }
                    }
                };
            }

            function buildCartesianOptions({ currency = false, integer = false, legend = true, indexAxis = 'x' } = {}) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis,
                    plugins: {
                        legend: {
                            display: legend,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    const value = context.parsed[indexAxis === 'y' ? 'x' : 'y'];
                                    if (currency) {
                                        return `${context.dataset.label || 'Monto'}: ${formatCurrency(value)}`;
                                    }

                                    return `${context.dataset.label || 'Valor'}: ${value}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: indexAxis !== 'y'
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                callback(value) {
                                    if (indexAxis === 'y' && currency) {
                                        return compactCurrency(value);
                                    }

                                    return this.getLabelForValue ? this.getLabelForValue(value) : value;
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)'
                            },
                            ticks: {
                                precision: integer ? 0 : undefined,
                                callback(value) {
                                    if (currency && indexAxis !== 'y') {
                                        return compactCurrency(value);
                                    }

                                    return value;
                                }
                            }
                        }
                    }
                };
            }

            function formatPaymentStatusLabel(status) {
                return ({
                    pendiente: 'Pendiente',
                    aprobado: 'Aprobado',
                    rechazado: 'Rechazado',
                    cancelado: 'Cancelado'
                }[status] || status);
            }

            function formatCurrency(value) {
                return new Intl.NumberFormat('es-BO', {
                    style: 'currency',
                    currency: 'BOB',
                    minimumFractionDigits: 2
                }).format(Number(value || 0));
            }

            function compactCurrency(value) {
                const amount = Number(value || 0);
                if (Math.abs(amount) >= 1000) {
                    return `${(amount / 1000).toFixed(1)}k`;
                }

                return amount.toFixed(0);
            }

            function renderPagination(pagination) {
                if (pagination.total_pages <= 1) {
                    paginationContainer.innerHTML = '';
                    return;
                }

                let paginationHTML = '<div class="flex space-x-1">';
                paginationHTML += `
                    <button ${pagination.current_page <= 1 ? 'disabled' : `onclick="changePage(${pagination.current_page - 1})"`}
                        class="px-3 py-1 rounded-lg ${pagination.current_page <= 1 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                `;

                for (let i = 1; i <= pagination.total_pages; i++) {
                    if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)) {
                        paginationHTML += `
                            <button onclick="changePage(${i})"
                                class="px-3 py-1 rounded-lg ${i === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300 transition-colors">
                                ${i}
                            </button>
                        `;
                    } else if (i === pagination.current_page - 2 || i === pagination.current_page + 2) {
                        paginationHTML += '<span class="px-2">...</span>';
                    }
                }

                paginationHTML += `
                    <button ${pagination.current_page >= pagination.total_pages ? 'disabled' : `onclick="changePage(${pagination.current_page + 1})"`}
                        class="px-3 py-1 rounded-lg ${pagination.current_page >= pagination.total_pages ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;

                paginationHTML += '</div>';
                paginationContainer.innerHTML = paginationHTML;
                totalPages = pagination.total_pages;
            }

            window.changePage = function(page) {
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    fetchFilteredResults();
                }
            };

            window.verComprobante = function(pagoId) {
                const modal = document.getElementById('modalComprobante');
                const contenidoComprobante = document.getElementById('contenidoComprobante');
                modal.classList.remove('hidden');
                contenidoComprobante.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div></div>';

                fetch(`/administrador/pagos/ver-recibo/${pagoId}`)
                    .then(response => response.json())
                    .then(data => {
                        contenidoComprobante.innerHTML = data.html;
                    })
                    .catch(() => {
                        contenidoComprobante.innerHTML = '<p class="text-red-500 text-center">Error al cargar el comprobante.</p>';
                    });
            };

            window.descargarComprobante = function(pagoId) {
                window.location.href = `/administrador/pagos/descargar-recibo/${pagoId}`;
            };

            window.cerrarModal = function() {
                document.getElementById('modalComprobante').classList.add('hidden');
            };

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    cerrarModal();
                }
            });

            fetchFilteredResults();
        });
    </script>

    <style>
        /* Banner geométrico - Mismo estilo que las otras vistas */
        .rp-banner {
            background:
                linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(315deg, #4f46e5 25%, transparent 25%),
                linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
                linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
            background-size:
                100px 100px,
                100px 100px,
                100px 100px,
                100px 100px,
                100% 100%;
            background-color: #1d4ed8;
            position: relative;
        }

        .rp-banner-overlay {
            background:
                radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
            background-size:     50% 50%;
            background-position: 0 0, 100% 0, 100% 100%, 0 100%;
            background-repeat:   no-repeat;
        }

        /* Estilos del page-header (legacy) */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem 2rem;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .header-content h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .header-content h1 i {
            color: #4F46E5;
            font-size: 1.75rem;
        }

        .header-content .subtitle {
            margin-top: 0.25rem;
            font-size: 0.95rem;
            color: #6B7280;
            margin-bottom: 0;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.5rem;
            background: #F3F4F6;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: #E5E7EB;
            transform: translateY(-2px);
            text-decoration: none;
            color: #111827;
        }

        @media (max-width: 640px) {
            .rp-banner .px-8 { 
                padding-left: 1.25rem; 
                padding-right: 1.25rem; 
            }
            .rp-banner .flex.flex-col.sm\:flex-row {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .rp-banner a {
                justify-content: center;
                width: 100%;
            }
            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1.25rem;
            }
            .header-content h1 {
                font-size: 1.5rem;
            }
            .btn-secondary {
                justify-content: center;
                width: 100%;
            }
            .btn-action {
                flex: 1;
                min-width: 120px;
            }
        }

        /* Action buttons styles */
        .btn-action {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 0.875rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        .btn-action__mark {
            position: absolute;
            right: -20px;
            top: -20px;
            width: 80px;
            height: 80px;
            opacity: 0.15;
            pointer-events: none;
            overflow: hidden;
        }

        .btn-action__mark svg {
            width: 100%;
            height: 100%;
            fill: currentColor;
        }

        .btn-blue {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }

        .btn-blue:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        .btn-indigo {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: white;
        }

        .btn-indigo:hover {
            background: linear-gradient(135deg, #4338ca, #3730a3);
        }

        .btn-purple {
            background: linear-gradient(135deg, #a855f7, #9333ea);
            color: white;
        }
        .btn-purple:hover {
            background: linear-gradient(135deg, #9333ea, #7e22ce);
        }

        .chart-card {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .chart-card__title {
            font-size: 1rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.85rem;
            text-align: center;
        }

        .chart-card__canvas {
            position: relative;
            height: 320px;
        }

        .chart-card__canvas--wide {
            height: 360px;
        }

        .chart-card__empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.75) 0%, rgba(248,250,252,0.95) 100%);
            border: 1px dashed #cbd5e1;
            border-radius: 0.9rem;
        }

        /* Rediseño unificado del panel de analíticas */
        .payments-analytics-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.payments-analytics-shell{position:relative;display:flex;flex-direction:column;width:100%}.analytics-hero{order:1;width:100%;min-height:180px;margin:0;border-radius:0;box-shadow:none}.analytics-hero .relative.z-10{display:flex;align-items:center;min-height:180px;padding:30px 48px}.analytics-hero .relative.z-10>div{width:100%;padding-right:570px}.analytics-hero .relative.z-10>div>a{display:none}.analytics-hero h1{margin:0 0 4px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.analytics-hero h1::before{content:'Administración financiera';display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.analytics-hero p{color:#dbeafe!important;font-size:.74rem!important;font-weight:600}.analytics-hero .h-14.w-14{width:52px;height:52px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14)!important;backdrop-filter:blur(5px)}.analytics-hero .rp-banner-overlay,.analytics-hero .absolute.inset-0{background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 50%);background-size:100% 100%,50% 50%,50% 50%;background-position:0 0,0 0,100% 100%}
        .analytics-tabs{position:absolute;z-index:20;top:67px;right:48px;justify-content:flex-end;padding:0}.analytics-tabs .btn-action{min-height:42px;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;box-shadow:none;font-size:.69rem;font-weight:900;backdrop-filter:blur(4px)}.analytics-tabs .btn-action:nth-child(2){border-color:#fff;background:#fff;color:#4f46e5}.analytics-tabs .analytics-register-action{border-color:#ef6c22;background:#ef6c22;color:#fff}.analytics-tabs .btn-action:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}.analytics-tabs .btn-action__mark{display:none}
        .analytics-filters{order:3;margin:24px 24px 0!important;padding:20px!important;border:1px solid #d9e7f0!important;border-radius:16px!important;background:linear-gradient(90deg,#f7faff,#eff9fa)!important;box-shadow:0 9px 22px rgba(30,72,110,.07)!important}.analytics-filters>.flex.items-center{margin-bottom:18px}.analytics-filters>.flex.items-center>div:first-child{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;margin-right:12px!important;padding:0!important;border-radius:11px!important;background:linear-gradient(135deg,#2563b9,#1593b5)!important;box-shadow:0 7px 15px rgba(21,147,181,.2)!important}.analytics-filters>.flex.items-center i{font-size:.9rem!important}.analytics-filters h2{color:#263f52;font-size:.95rem;font-weight:900}.analytics-filters h2::after{content:'';display:block;width:44px;height:3px;margin-top:6px;border-radius:999px;background:#1593b5}.analytics-filters h2+p{margin-top:5px;color:#78909f;font-size:.64rem}.analytics-filters form>.grid{grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}.analytics-filters label{margin-bottom:7px;color:#405568;font-size:.64rem;font-weight:900}.analytics-filters label i{color:#1593b5}.analytics-filters input,.analytics-filters select{width:100%;height:48px;padding:0 13px;border:1px solid #d8e4ec;border-radius:12px;background:#fff;color:#304657;font-size:.72rem;font-weight:600;outline:0;transition:.18s}.analytics-filters input:focus,.analytics-filters select:focus{border-color:#1593b5;box-shadow:0 0 0 3px rgba(21,147,181,.13)}.analytics-filters form>.flex button{min-height:41px;padding:9px 14px;border-radius:10px;font-size:.67rem;font-weight:900;box-shadow:none}.analytics-filters #resetFilters{border:1px solid #d4e2eb;background:#fff;color:#62798a}.analytics-filters button[type=submit]{border:0;background:linear-gradient(135deg,#2563b9,#1593b5);color:#fff}.analytics-filters button[type=submit]:hover{filter:brightness(.94)}
        .analytics-native-control{position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;padding:0!important;border:0!important;opacity:0!important;pointer-events:none!important}.analytics-custom-dropdown,.analytics-date-control{position:relative}.analytics-custom-trigger{width:100%;height:48px;display:flex;align-items:center;gap:9px;padding:0 11px;border:1px solid #d8e4ec;border-radius:12px;background:#fff;color:#304657;text-align:left;cursor:pointer;transition:.18s}.analytics-custom-trigger>span:nth-child(2){min-width:0;flex:1;overflow:hidden;font-size:.68rem;font-weight:800;text-overflow:ellipsis;white-space:nowrap}.analytics-custom-trigger:hover,.analytics-custom-dropdown.is-open .analytics-custom-trigger,.analytics-date-control.is-open .analytics-custom-trigger{border-color:#1593b5;box-shadow:0 0 0 3px rgba(21,147,181,.13)}.analytics-control-icon{width:30px;height:30px;display:grid;place-items:center;flex:0 0 auto;border-radius:8px;background:#e5f6f8;color:#1593b5}.analytics-control-icon i{font-size:.72rem!important}.analytics-control-chevron{color:#8ba0ad;font-size:.6rem!important;transition:transform .18s}.is-open .analytics-control-chevron{transform:rotate(180deg)}.analytics-custom-menu{position:absolute;z-index:100;top:calc(100% + 7px);right:0;left:0;max-height:245px;overflow-y:auto;padding:7px;border:1px solid #d5e4ed;border-radius:13px;background:#fff;box-shadow:0 16px 34px rgba(30,72,110,.17)}.analytics-custom-menu.is-hidden,.analytics-calendar.is-hidden{display:none}.analytics-custom-menu button{width:100%;min-height:39px;padding:8px 10px;border:0;border-radius:8px;background:transparent;color:#4c6271;text-align:left;font-size:.67rem;font-weight:700;cursor:pointer}.analytics-custom-menu button:hover,.analytics-custom-menu button.is-selected{background:#eaf7f9;color:#117e9b}.analytics-custom-menu button.is-selected::after{content:'✓';float:right;color:#1593b5;font-weight:900}
        .analytics-calendar{position:absolute;z-index:110;top:calc(100% + 7px);left:0;width:290px;padding:13px;border:1px solid #d5e4ed;border-radius:14px;background:#fff;box-shadow:0 18px 38px rgba(30,72,110,.18)}[data-analytics-calendar="endDate"] .analytics-calendar{right:0;left:auto}.analytics-calendar-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:11px}.analytics-calendar-head strong{color:#30495b;font-size:.78rem;font-weight:900;text-transform:capitalize}.analytics-calendar-head button{width:32px;height:32px;display:grid;place-items:center;border:0;border-radius:8px;background:#eaf7f9;color:#117e9b;cursor:pointer}.analytics-calendar-head button:hover{background:#1593b5;color:#fff}.analytics-calendar-week,.analytics-calendar-days{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}.analytics-calendar-week{margin-bottom:5px}.analytics-calendar-week span{color:#8ca0ad;text-align:center;font-size:.56rem;font-weight:900}.analytics-calendar-days button,.analytics-calendar-days>span{aspect-ratio:1;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:#405568;font-size:.66rem;font-weight:800}.analytics-calendar-days button{cursor:pointer}.analytics-calendar-days button:hover{background:#eaf7f9;color:#117e9b}.analytics-calendar-days button.is-today{box-shadow:inset 0 0 0 1px #1593b5}.analytics-calendar-days button.is-selected{background:linear-gradient(135deg,#2563b9,#1593b5);color:#fff;box-shadow:0 5px 11px rgba(21,147,181,.23)}.analytics-calendar-days button:disabled{cursor:not-allowed;color:#d1dbe1;background:transparent;box-shadow:none}
        .analytics-results{order:4;margin:24px 24px 0;padding:0!important;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important}.analytics-results #appliedFilters{margin-bottom:15px}.analytics-results #appliedFilters span{border-color:#b9dbe4!important;background:#eaf8fa!important;color:#117e9b!important;font-size:.61rem!important;font-weight:800}
        .analytics-summary-grid{gap:16px;margin-bottom:18px}.analytics-summary-card{--sum-accent:#117e8c;--sum-soft:#e6f4f5;--sum-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px!important;border:1px solid rgba(var(--sum-rgb),.22);border-radius:1rem!important;background:linear-gradient(135deg,#fff 35%,var(--sum-soft) 100%);color:#263024!important;box-shadow:inset 0 4px 0 var(--sum-accent),0 10px 24px rgba(45,66,34,.09);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease}.analytics-summary-card::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--sum-rgb),.09);border-radius:50%}.analytics-summary-card::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--sum-accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.analytics-summary-card:hover{transform:translateY(-5px);border-color:rgba(var(--sum-rgb),.38);box-shadow:inset 0 4px 0 var(--sum-accent),0 17px 32px rgba(var(--sum-rgb),.16)}.analytics-summary-card>div{position:relative;z-index:1;width:100%}.analytics-summary-income{--sum-accent:#117e8c;--sum-soft:#e6f4f5;--sum-rgb:17,126,140}.analytics-summary-plan{--sum-accent:#7da533;--sum-soft:#f0f6e7;--sum-rgb:125,165,51}.analytics-summary-records{--sum-accent:#e3a122;--sum-soft:#fff6df;--sum-rgb:227,161,34}.analytics-summary-card p:first-child{display:block;color:#596170!important;font-size:.7rem!important;font-weight:900!important;letter-spacing:.025em;text-transform:uppercase}.analytics-summary-card p[id]{display:block;margin-top:9px!important;color:#263024!important;font-size:1.85rem!important;font-weight:900!important;line-height:1}.analytics-summary-card>div>div:last-child{position:relative;z-index:1;width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;padding:0!important;border:1px solid rgba(255,255,255,.55);border-radius:14px!important;background:var(--sum-accent)!important;color:#fff;box-shadow:0 8px 17px rgba(var(--sum-rgb),.27),inset 0 1px 0 rgba(255,255,255,.28);transition:transform .22s ease}.analytics-summary-card:hover>div>div:last-child{transform:rotate(-6deg) scale(1.06)}.analytics-summary-card>div>div:last-child i{font-size:1.18rem!important}
        #chartsSection{gap:16px;margin-bottom:18px}.chart-card{position:relative;overflow:hidden;padding:18px;border:1px solid #dce7ee;border-radius:15px;background:linear-gradient(135deg,#fff,#f7fbfd);box-shadow:0 9px 22px rgba(30,72,110,.07)}.chart-card::before{content:'';position:absolute;top:0;right:0;left:0;height:3px;background:linear-gradient(90deg,#2563b9,#1593b5)}.chart-card__title{display:flex;align-items:center;justify-content:center;min-height:34px;margin-bottom:10px;color:#30495b;font-size:.78rem;font-weight:900}.chart-card__title::after{content:'';width:7px;height:7px;margin-left:8px;border-radius:50%;background:#1593b5;box-shadow:0 0 0 4px rgba(21,147,181,.1)}.chart-card__canvas{height:285px}.chart-card__canvas--wide{height:325px}
        .analytics-table-wrap{border:1px solid #fed7aa!important;border-radius:13px!important;background:#fff;box-shadow:0 9px 22px rgba(249,115,22,.09)!important}.analytics-table{border-collapse:separate;border-spacing:0}.analytics-table thead{background:#f97316!important}.analytics-table th{padding:12px 13px!important;border-right:1px solid rgba(255,255,255,.3)!important;background:#f97316!important;color:#fff!important;font-size:.57rem!important;font-weight:900!important;letter-spacing:.05em}.analytics-table th:last-child{border-right:0!important}.analytics-table td{padding:12px 13px!important;border-right:1px solid #fed7aa!important;border-bottom:1px solid #ffedd5!important;color:#57534e!important;font-size:.67rem!important}.analytics-table td:last-child{border-right:0!important}.analytics-table tbody tr:nth-child(odd) td{background:#fff!important}.analytics-table tbody tr:nth-child(even) td{background:#fff7ed!important}.analytics-table tbody tr:hover td{background:#ffedd5!important}.analytics-table tbody tr:last-child td{border-bottom:0!important}.analytics-table td:first-child{color:#c2410c!important;font-weight:900!important}.analytics-table td button{font-size:.57rem!important;font-weight:800!important}.analytics-results #paginationContainer{margin-top:16px;padding:12px;border:1px solid #fed7aa;border-radius:12px;background:#fffaf5}
        #modalComprobante>div>div.inline-block{border:1px solid #d9e7f0;border-radius:18px!important}#modalComprobante header{background:linear-gradient(90deg,#f4f8fd,#edf9fa)!important}#modalComprobante header i{color:#1593b5!important}
        @media(max-width:1150px){.analytics-filters form>.grid{grid-template-columns:repeat(3,minmax(0,1fr))}.analytics-hero .relative.z-10>div{padding-right:0}.analytics-tabs{position:static;order:2;justify-content:center;margin:14px 24px 0}.analytics-tabs .btn-action{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.analytics-tabs .btn-action:nth-child(2){background:#4f46e5;color:#fff}.analytics-filters{margin-top:18px!important}}
        @media(max-width:700px){.payments-analytics-page{padding-top:20px}.analytics-hero{min-height:210px}.analytics-hero .relative.z-10{min-height:210px;padding:28px 20px}.analytics-hero .h-14.w-14{display:none}.analytics-tabs{display:grid;grid-template-columns:1fr;margin-right:12px;margin-left:12px}.analytics-filters,.analytics-results{margin-right:12px!important;margin-left:12px!important}.analytics-filters form>.grid{grid-template-columns:1fr}.analytics-filters form>.flex{flex-direction:column}.analytics-filters form>.flex button{width:100%}.analytics-calendar,[data-analytics-calendar="endDate"] .analytics-calendar{right:auto;left:0;width:min(290px,calc(100vw - 66px))}.chart-card__canvas,.chart-card__canvas--wide{height:260px}}
</style>
@endsection
