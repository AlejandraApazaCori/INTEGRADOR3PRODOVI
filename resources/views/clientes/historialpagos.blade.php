@extends('layouts.app2')

@section('title', 'Historial de Pagos')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-credit-card text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Historial de Pagos</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Consulta todos tus pagos realizados y su estado actual</p>
                    </div>
                    <div class="rounded-2xl px-4 py-2" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                        <span style="color: rgba(255,255,255,0.8); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Total pagos</span>
                        <div class="text-2xl font-bold text-white">{{ $pagos->total() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de historial de pagos mejorada -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #a7b838;">
                        <i class="fas fa-list text-white text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Registros de pagos</h2>
                        <p class="text-sm text-gray-500">Listado completo de tus transacciones</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Código de Pago</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Plan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Monto</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Método</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha de Pago</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pagos as $index => $pago)
                            @php
                                $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                            @endphp
                            <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #ea9f21;">
                                            <i class="fas fa-receipt text-white text-xs"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $pago->comprobantePago->numero_formateado ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">
                                    {{ $pago->plan->nombre ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">
                                    {{ $pago->moneda }} {{ number_format($pago->monto, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border
                                        {{ $pago->metodo === 'qr' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                                        <i class="fas {{ $pago->metodo === 'qr' ? 'fa-qrcode' : 'fa-money-bill-wave' }} mr-1.5"></i>
                                        {{ ucfirst($pago->metodo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    @php
                                        $estadoClasses = [
                                            'completado' => 'bg-green-100 text-green-800 border-green-200',
                                            'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'cancelado' => 'bg-red-100 text-red-800 border-red-200',
                                        ];
                                        $estadoIcons = [
                                            'completado' => 'fa-check-circle',
                                            'pendiente' => 'fa-clock',
                                            'cancelado' => 'fa-times-circle',
                                        ];
                                        $class = $estadoClasses[$pago->estado] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                        $icon = $estadoIcons[$pago->estado] ?? 'fa-circle';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $class }}">
                                        <i class="fas {{ $icon }} mr-1.5"></i>
                                        {{ ucfirst($pago->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">
                                    <i class="fas fa-calendar-alt mr-1.5 text-gray-400"></i>
                                    {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        <button onclick="verComprobante({{ $pago->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                            <i class="fas fa-eye mr-1.5"></i> Ver
                                        </button>
                                        @if($pago->estado === 'completado')
                                            <button onclick="descargarComprobante({{ $pago->id }})"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                                <i class="fas fa-download mr-1.5"></i> Descargar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-credit-card text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500 font-medium">No se encontraron registros de pagos</p>
                                        <p class="text-gray-400 text-sm mt-1">Aún no has realizado ningún pago</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if(method_exists($pagos, 'links'))
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $pagos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para ver comprobante -->
<div id="modalComprobante" 
     class="fixed inset-0 z-50 overflow-y-auto hidden"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 transition-opacity">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="cerrarModal()"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             id="modalContentPanel">
            
            <header class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800" id="modal-title">
                        <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i>
                        Comprobante de Pago
                    </h3>
                    <button onclick="cerrarModal()" type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </header>

            <main id="contenidoComprobante" class="bg-white px-6 py-4 max-h-[70vh] overflow-y-auto">
                <!-- El contenido del comprobante o el spinner de carga se cargarán aquí -->
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

<style>
    /* Banner geométrico */
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

    /* Animación para el modal */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-enter {
        animation: slideIn 0.3s ease-out forwards;
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
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                cerrarModal();
            }
        });
    });

    function verComprobante(pagoId) {
        const modal = document.getElementById('modalComprobante');
        const contentPanel = document.getElementById('modalContentPanel');
        const contenidoComprobante = document.getElementById('contenidoComprobante');

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            contentPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            contentPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        }, 10);

        contenidoComprobante.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>
        `;

        fetch(`/clientes/pagos/comprobante/${pagoId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                contenidoComprobante.innerHTML = data.html;
            })
            .catch(error => {
                console.error('Error al cargar el comprobante:', error);
                contenidoComprobante.innerHTML = `
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 my-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Error:</strong> No se pudo cargar el comprobante. Inténtelo de nuevo más tarde.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
    }

    function descargarComprobante(pagoId) {
        window.location.href = `/clientes/pagos/descargar/${pagoId}`;
    }

    function cerrarModal() {
        const modal = document.getElementById('modalComprobante');
        const contentPanel = document.getElementById('modalContentPanel');

        modal.classList.remove('opacity-100');
        contentPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        contentPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endsection