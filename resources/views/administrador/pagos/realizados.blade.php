@extends('layouts.app')

@section('title', 'Pagos Realizados')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="container mx-auto px-4 py-8">
        <!-- Header con título y decoración -->
        <div class="relative mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl opacity-5"></div>
            <div class="relative bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center space-x-4">
                    <!-- Botón de Atrás -->
                    <a href="{{ route('administrador.pagos.index') }}" class="text-gray-500 hover:text-gray-700 transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Pagos Realizados</h1>
                        <p class="text-gray-600 mt-1">Gestiona y consulta todos los pagos activos</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulario de búsqueda mejorado -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Filtros de Búsqueda</h2>
                </div>
                
                <form id="filterForm" action="{{ route('administrador.pagos.realizados') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Campo de búsqueda por usuario -->
                    <div class="space-y-2">
                        <label for="search" class="block text-sm font-semibold text-gray-700">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Buscar usuario</span>
                            </div>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                id="search"
                                placeholder="Ingresa el nombre del usuario..." 
                                value="{{ request('search') }}"
                                class="w-full px-4 py-3 pl-10 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                                oninput="debounceFilter()"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Select para filtrar por plan -->
                    <div class="space-y-2">
                        <label for="plan" class="block text-sm font-semibold text-gray-700">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>Filtrar por plan</span>
                            </div>
                        </label>
                        <div class="relative">
                            <select 
                                name="plan" 
                                id="plan"
                                class="w-full px-4 py-3 pl-10 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white appearance-none"
                                onchange="filterResults()"
                            >
                                <option value="">Todos los planes disponibles</option>
                                @foreach($planes as $plan)
                                    <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="flex items-end space-x-3">
                        <button 
                            type="submit" 
                            id="filterButton"
                            class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold transition duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span>Filtrar</span>
                        </button>
                        
                        @if(request('search') || request('plan'))
                            <a 
                                href="{{ route('administrador.pagos.realizados') }}" 
                                class="px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition duration-200 flex items-center space-x-2 border border-gray-200"
                                title="Limpiar filtros"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="hidden sm:inline">Limpiar</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de resultados mejorada -->
        <div id="resultsContainer" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Resultados</h3>
                </div>
            </div>
            <div class="overflow-hidden">
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Usuario</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Plan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Método</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Monto</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha de Pago</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Inicio</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fin</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($pagos as $index => $pago)
                                @php
                                    $estadoClase = match($pago['estado']) {
                                        'completado' => 'bg-green-100 text-green-800 border-green-200',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'cancelado' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                                    };
                                    $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                                @endphp
                                <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-indigo-600 border-r border-gray-100">#{{ $pago['id'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium border-r border-gray-100">{{ $pago['usuario'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ $pago['plan'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ ucfirst($pago['tipo_pago'] ?? 'N/A') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">{{ $pago['monto'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border {{ $estadoClase }}">{{ ucfirst($pago['estado'] ?? 'N/A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago['fecha_pago'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago['fecha_inicio'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago['fecha_fin'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="verComprobante({{ $pago['id'] }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                                <i class="fas fa-file-invoice mr-1.5"></i>Ver
                                            </button>
                                            <button type="button" onclick="descargarComprobante({{ $pago['id'] }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                                <i class="fas fa-download mr-1.5"></i>Descargar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">No hay registros de pagos realizados para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             id="modalContentPanel">
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
            <main id="contenidoComprobante" class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                <!-- Contenido AJAX -->
            </main>
            <footer class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex justify-end">
                    <button onclick="cerrarModal()" type="button"
                        class="inline-flex justify-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-gray-600 hover:bg-gray-700 transition-colors">
                        Cerrar
                    </button>
                </div>
            </footer>
        </div>
    </div>
</div>

<script>
// Función para filtrar resultados cuando cambia el select
function filterResults() {
    document.getElementById('filterForm').submit();
}

// Función debounce para el input de búsqueda
let debounceTimer;
function debounceFilter() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        filterResults();
    }, 500);
}

// Funciones para el comprobante
function verComprobante(pagoId) {
    const modal = document.getElementById('modalComprobante');
    const contenidoComprobante = document.getElementById('contenidoComprobante');

    modal.classList.remove('hidden');
    
    contenidoComprobante.innerHTML = `
        <div class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>
    `;

    fetch(`/administrador/pagos/ver-recibo/${pagoId}`)
        .then(response => response.json())
        .then(data => {
            contenidoComprobante.innerHTML = data.html;
        })
        .catch(error => {
            console.error('Error:', error);
            contenidoComprobante.innerHTML = '<p class="text-red-500 text-center">Error al cargar el comprobante.</p>';
        });
}

function descargarComprobante(pagoId) {
    window.location.href = `/administrador/pagos/descargar-recibo/${pagoId}`;
}

function cerrarModal() {
    document.getElementById('modalComprobante').classList.add('hidden');
}

// Cerrar con Escape
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        cerrarModal();
    }
});
</script>

<style>
    /* Animación para los modales */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
</style>
@endsection
