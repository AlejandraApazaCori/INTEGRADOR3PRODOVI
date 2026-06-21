@extends('layouts.app')

@section('title', 'Analíticas de Pagos')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Action buttons -->
            <div class="flex flex-wrap gap-3 pb-6">
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
                <button type="button" id="abrirReporteMensual" class="btn-action btn-purple">
                    <i class="fas fa-file-pdf"></i>
                    Reporte mensual
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </button>
            </div>

            <!-- Header mejorado -->
            <div class="page-header">
                <div class="header-content">
                    <h1><i class="fas fa-chart-line"></i> Analíticas de Pagos</h1>
                    <p class="subtitle">Visualiza y analiza los pagos realizados en tu plataforma</p>
                </div>
                <a href="{{ route('administrador.pagos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Pagos
                </a>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-100">
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
                            <select id="plan" name="plan"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="">Todos los planes</option>
                                @foreach($planes as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="subscriptionStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-1"></i> Estado de Suscripción
                            </label>
                            <select id="subscriptionStatus" name="subscriptionStatus"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                                <option value="">Todos los estados</option>
                                <option value="active">Solo activas</option>
                                <option value="completed">Finalizadas</option>
                                <option value="cancelled">Canceladas</option>
                                <option value="all">Todas</option>
                            </select>
                        </div>

                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Desde
                            </label>
                            <input type="date" id="startDate" name="startDate" value="{{ $defaultStartDate }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        </div>

                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Hasta
                            </label>
                            <input type="date" id="endDate" name="endDate" value="{{ $defaultEndDate }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
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
            <div id="resultsSection" class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Resumen del rango consultado</h2>
                        <p class="text-gray-600 text-sm">Las métricas se actualizan según los filtros aplicados</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span id="resultCount" class="text-sm text-gray-600 font-medium"></span>
                        <div id="downloadButtons" class="flex items-center gap-2">
                            <button id="downloadPDF"
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-xl hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-red-200/50">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </button>
                            <button id="downloadExcel"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-green-200/50">
                                <i class="fas fa-file-excel mr-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>

                <div id="appliedFilters" class="mb-6 flex flex-wrap gap-2"></div>

                <!-- Tarjetas de resumen mejoradas -->
                <div id="summaryCards" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
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
                    <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
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
                    <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
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

                <!-- Gráficas -->
                <div id="chartsSection" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-2xl shadow-md border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 text-center">Distribución por Plan</h3>
                        <div style="height: 300px;">
                            <canvas id="planChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl shadow-md border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 text-center">Distribución por Estado</h3>
                        <div style="height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla mejorada -->
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
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

                <div id="paginationContainer" class="mt-4 flex justify-center"></div>
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
            const resultCount = document.getElementById('resultCount');
            const paginationContainer = document.getElementById('paginationContainer');
            const appliedFilters = document.getElementById('appliedFilters');
            const defaultStartDate = @json($defaultStartDate);
            const defaultEndDate = @json($defaultEndDate);
            let currentPage = 1;
            let totalPages = 1;
            let planChartInstance = null;
            let statusChartInstance = null;

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                currentPage = 1;
                fetchFilteredResults();
            });

            resetButton.addEventListener('click', function () {
                filterForm.reset();
                document.getElementById('startDate').value = defaultStartDate;
                document.getElementById('endDate').value = defaultEndDate;
                currentPage = 1;
                fetchFilteredResults();
            });

            document.getElementById('downloadPDF').addEventListener('click', function () {
                downloadReport('pdf');
            });

            document.getElementById('downloadExcel').addEventListener('click', function () {
                downloadReport('excel');
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
                        resultCount.textContent = `Mostrando ${data.data.length} de ${data.pagination.total} resultados`;
                    })
                    .catch(error => {
                        resultsTableBody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">${error.message}</td></tr>`;
                        resultCount.textContent = '0 resultados';
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
                if (planChartInstance) {
                    planChartInstance.destroy();
                }

                if (statusChartInstance) {
                    statusChartInstance.destroy();
                }

                const planCtx = document.getElementById('planChart').getContext('2d');
                planChartInstance = new Chart(planCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(charts.plan_distribution),
                        datasets: [{
                            data: Object.values(charts.plan_distribution),
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });

                const statusCtx = document.getElementById('statusChart').getContext('2d');
                statusChartInstance = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(charts.status_distribution).map(key => ({
                            activa: 'Activa',
                            finalizada: 'Finalizada',
                            cancelada: 'Cancelada',
                            pendiente: 'Pendiente'
                        }[key] || key)),
                        datasets: [{
                            data: Object.values(charts.status_distribution),
                            backgroundColor: ['#10B981', '#6B7280', '#EF4444', '#F59E0B'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
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

            function downloadReport(type) {
                const params = getFormParams();
                window.open(`/administrador/pagos/descargar-${type}?${params.toString()}`, '_blank');
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
        /* Estilos del page-header */
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
    </style>
@endsection
