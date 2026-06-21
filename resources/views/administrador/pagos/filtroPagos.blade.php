            <!-- Advanced Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="flex items-center mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-500 rounded-lg p-3 mr-4">
                        <i class="fas fa-filter text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Búsqueda Avanzada</h2>
                        <p class="text-gray-600">Filtra pagos por diferentes criterios y obten reportes</p>
                    </div>
                </div>

                <form id="filterForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Filtro por nombre de cliente -->
                        <div>
                            <label for="clientName" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1"></i> Nombre del Cliente
                            </label>
                            <input type="text" id="clientName" name="clientName" placeholder="Buscar por nombre..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        </div>

                        <!-- Filtro por plan -->
                        <div>
                            <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-list mr-1"></i> Plan
                            </label>
                            <select id="plan" name="plan"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <option value="">Todos los planes</option>
                                @if(isset($planes))
                                    @foreach($planes as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Filtro por estado de suscripción -->
                        <div>
                            <label for="subscriptionStatus" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-1"></i> Estado de Suscripción
                            </label>
                            <select id="subscriptionStatus" name="subscriptionStatus"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <option value="">Todos los estados</option>
                                <option value="active">Solo activas</option>
                                <option value="completed">Completadas</option>
                                <option value="cancelled">Canceladas</option>
                                <option value="all">Todas (incluyendo concluidas)</option>
                            </select>
                        </div>

                        <!-- Filtro por rango de fechas -->
                        <div>
                            <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Fecha Inicio
                            </label>
                            <input type="date" id="startDate" name="startDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        </div>

                        <div>
                            <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Fecha Fin
                            </label>
                            <input type="date" id="endDate" name="endDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" id="resetFilters"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                            <i class="fas fa-redo mr-2"></i> Reiniciar Filtros
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" class="bg-white rounded-xl shadow-md p-6 hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Resultados de Búsqueda</h2>
                    <div class="flex items-center space-x-3">
                        <span id="resultCount" class="text-sm text-gray-600"></span>
                        <div id="downloadButtons" class="hidden space-x-2">
                            <button id="downloadPDF"
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                                <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                            </button>
                            <button id="downloadExcel"
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-file-excel mr-1"></i> Descargar Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div id="summaryCards" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Total de Ingresos</p>
                                <p class="text-2xl font-bold" id="totalIncomeSummary">-</p>
                            </div>
                            <i class="fas fa-dollar-sign text-3xl text-green-200"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Plan Más Contratado</p>
                                <p class="text-xl font-bold" id="mostHiredPlanSummary">-</p>
                            </div>
                            <i class="fas fa-trophy text-3xl text-blue-200"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-400 to-purple-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Total de Registros</p>
                                <p class="text-2xl font-bold" id="totalRecordsSummary">-</p>
                            </div>
                            <i class="fas fa-chart-bar text-3xl text-purple-200"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div id="chartsSection" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 text-center">Distribución por Plan</h3>
                        <div style="height: 300px;">
                            <canvas id="planChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 text-center">Distribución por Estado</h3>
                        <div style="height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Results will be inserted here via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div id="paginationContainer" class="mt-4 flex justify-center">
                    <!-- Pagination will be inserted here via JavaScript -->
                </div>
            </div>
        </div>

    <!-- Modal para ver comprobante -->
    <div id="modalComprobante" 
         class="fixed inset-0 z-50 overflow-y-auto hidden"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                 id="modalContentPanel">
                <header class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                            <i class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>
                            Comprobante de Pago
                        </h3>
                        <button onclick="cerrarModal()" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </header>
                <main id="contenidoComprobante" class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Contenido AJAX -->
                </main>
                <footer class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button onclick="cerrarModal()" type="button"
                            class="inline-flex justify-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    </div>


                <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-hide alerts después de 5 segundos
            document.querySelectorAll('.bg-green-100, .bg-red-100').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });

            // Agregar interactividad a las action cards
            document.querySelectorAll('.cursor-pointer').forEach(card => {
                card.addEventListener('click', function () {
                    // Animación de feedback visual
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 100);
                });
            });

            const filterForm = document.getElementById('filterForm');
            const resetButton = document.getElementById('resetFilters');
            const resultsSection = document.getElementById('resultsSection');
            const resultsTableBody = document.getElementById('resultsTableBody');
            const resultCount = document.getElementById('resultCount');
            const paginationContainer = document.getElementById('paginationContainer');
            let currentPage = 1;
            let totalPages = 1;
            
            // Variables globales para los gráficos
            let planChartInstance = null;
            let statusChartInstance = null;

            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                currentPage = 1;
                fetchFilteredResults();
            });

            resetButton.addEventListener('click', function () {
                filterForm.reset();
                resultsSection.classList.add('hidden');
            });

            function fetchFilteredResults() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                for (const [key, value] of formData.entries()) {
                    if (value) params.append(key, value);
                }
                params.append('page', currentPage);

                resultsTableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando resultados...</td></tr>';
                resultsSection.classList.remove('hidden');

                fetch(`/administrador/pagos/buscar?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => {
                        console.log('Respuesta del servidor:', response);
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        if (data.success) {
                            renderResults(data.data);
                            renderPagination(data.pagination);
                            resultCount.textContent = `Mostrando ${data.data.length} de ${data.pagination.total} resultados`;
                            
                            // Renderizar resumen y gráficos
                            if (data.summary) {
                                renderSummary(data.summary);
                            }
                            if (data.charts) {
                                renderCharts(data.charts);
                            }
                        } else {
                            resultsTableBody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">${data.message || 'Error al cargar los resultados.'}</td></tr>`;
                            resultCount.textContent = '0 resultados';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsTableBody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error: ${error.message}</td></tr>`;
                    });
            }

            function renderResults(results) {
                if (results.length === 0) {
                    resultsTableBody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No se encontraron resultados.</td></tr>';
                    document.getElementById('downloadButtons').classList.add('hidden');
                    return;
                }

                resultsTableBody.innerHTML = results.map(result => {
                    let statusBadge = '';
                    switch(result.estado) {
                        case 'activa':
                            statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activa</span>';
                            break;
                        case 'finalizada':
                            statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Finalizada</span>';
                            break;
                        case 'cancelada':
                            statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelada</span>';
                            break;
                        default:
                            statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>';
                    }

                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.usuario}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.plan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.monto}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.fecha_inicio}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${result.fecha_fin}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="verComprobante(${result.id})" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-2" 
                                        title="Ver Recibo">
                                        <i class="fas fa-eye mr-1"></i>Recibo
                                    </button>
                                    
                                    <button onclick="descargarComprobante(${result.id})" 
                                        class="text-green-600 hover:text-green-900 mr-2" 
                                        title="Descargar Recibo">
                                        <i class="fas fa-download mr-1"></i>Descargar
                                    </button>

                                    ${result.estado === 'cancelada' ? 
                                        `<button onclick="reactivarSuscripcion(${result.id})" class="text-blue-600 hover:text-blue-900">Reactivar</button>` : 
                                        `<button onclick="cancelarSuscripcion(${result.id})" class="text-red-600 hover:text-red-900">Cancelar</button>`
                                    }
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                document.getElementById('downloadButtons').classList.remove('hidden');
                document.getElementById('downloadPDF').onclick = function() { downloadReport('pdf'); };
                document.getElementById('downloadExcel').onclick = function() { downloadReport('excel'); };
            }

            function downloadReport(type) {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams();
                
                for (const [key, value] of formData.entries()) {
                    if (value) params.append(key, value);
                }
                
                window.open(`/administrador/pagos/descargar-${type}?${params.toString()}`, '_blank');
            }

            function renderSummary(summary) {
                document.getElementById('totalIncomeSummary').textContent = summary.total_income;
                document.getElementById('mostHiredPlanSummary').textContent = summary.most_hired_plan;
                document.getElementById('totalRecordsSummary').textContent = summary.total_records;
                document.getElementById('summaryCards').classList.remove('hidden');
            }

            function renderCharts(charts) {
                if (planChartInstance) planChartInstance.destroy();
                if (statusChartInstance) statusChartInstance.destroy();

                const planCtx = document.getElementById('planChart').getContext('2d');
                planChartInstance = new Chart(planCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(charts.plan_distribution),
                        datasets: [{
                            label: 'Contratos',
                            data: Object.values(charts.plan_distribution),
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                            ],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return (context.label || '') + ': ' + context.parsed + ' contratos';
                                    }
                                }
                            }
                        }
                    }
                });

                const statusCtx = document.getElementById('statusChart').getContext('2d');
                statusChartInstance = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(charts.status_distribution).map(key => {
                            const translations = {
                                'activa': 'Activa',
                                'finalizada': 'Finalizada',
                                'cancelada': 'Cancelada',
                                'pendiente': 'Pendiente'
                            };
                            return translations[key] || key;
                        }),
                        datasets: [{
                            label: 'Suscripciones',
                            data: Object.values(charts.status_distribution),
                            backgroundColor: ['#10B981', '#6B7280', '#EF4444', '#F59E0B'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
                
                document.getElementById('chartsSection').classList.remove('hidden');
            }

            function renderPagination(pagination) {
    if (pagination.total_pages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let paginationHTML = '<div class="flex space-x-1">';
    
    // Botón anterior
    paginationHTML += `
        <button ${pagination.current_page <= 1 ? 'disabled' : `onclick="changePage(${pagination.current_page - 1})"`} 
                class="px-3 py-1 rounded ${pagination.current_page <= 1 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Números de página
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)) {
            paginationHTML += `
                <button onclick="changePage(${i})" 
                        class="px-3 py-1 rounded ${i === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300">
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 2 || i === pagination.current_page + 2) {
            paginationHTML += '<span class="px-2">...</span>';
        }
    }
    
    // Botón siguiente
    paginationHTML += `
        <button ${pagination.current_page >= pagination.total_pages ? 'disabled' : `onclick="changePage(${pagination.current_page + 1})"`} 
                class="px-3 py-1 rounded ${pagination.current_page >= pagination.total_pages ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100'} border border-gray-300">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    paginationHTML += '</div>';
    paginationContainer.innerHTML = paginationHTML;
    totalPages = pagination.total_pages;
}

// Función para cambiar de página
window.changePage = function(page) {
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        fetchFilteredResults();
    }
};

            window.cancelarSuscripcion = function (pagoId) {
                if (confirm('¿Está seguro?')) {
                    fetch(`/administrador/pagos/cancelar/${pagoId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fetchFilteredResults();
                                showNotification('success', data.message);
                            } else {
                                showNotification('error', data.message);
                            }
                        })
                        .catch(error => showNotification('error', 'Error al cancelar'));
                }
            };

            window.reactivarSuscripcion = function (pagoId) {
                if (confirm('¿Está seguro?')) {
                    fetch(`/administrador/pagos/reactivar/${pagoId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fetchFilteredResults();
                                showNotification('success', data.message);
                            } else {
                                showNotification('error', data.message);
                            }
                        })
                        .catch(error => showNotification('error', 'Error al reactivar'));
                }
            };

            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 flex items-center ${type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700'}`;
                notification.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3 text-xl"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()" class="ml-4 ${type === 'success' ? 'text-green-500' : 'text-red-500'} hover:text-opacity-75">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-20px)';
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Funciones para el comprobante
            window.verComprobante = function(pagoId) {
                const modal = document.getElementById('modalComprobante');
                const contenidoComprobante = document.getElementById('contenidoComprobante');

                modal.classList.remove('hidden');
                
                contenidoComprobante.innerHTML = `
                    <div class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>
                `;

                fetch(`/administrador/pagos/ver-recibo/${pagoId}`)
                    .then(response => response.json())
                    .then(data => {
                        contenidoComprobante.innerHTML = data.html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contenidoComprobante.innerHTML = '<p class="text-red-500">Error al cargar el comprobante.</p>';
                    });
            };

            window.descargarComprobante = function(pagoId) {
                window.location.href = `/administrador/pagos/descargar-recibo/${pagoId}`;
            };

            window.cerrarModal = function() {
                document.getElementById('modalComprobante').classList.add('hidden');
            };

            // Cerrar con Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape") {
                    cerrarModal();
                }
            });
        });
    </script>