@extends('layouts.app')

@section('title', 'Gestión de Pagos')

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="mb-6 flex items-center justify-between rounded-xl border-l-4 border-green-500 bg-green-50 p-4 text-green-700 shadow-sm animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 flex items-center justify-between rounded-xl border-l-4 border-red-500 bg-red-50 p-4 text-red-700 shadow-sm animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

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

            <!-- Banner con fondo geométrico -->
            <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay absolute inset-0"></div>
                <div class="relative z-10 px-8 py-8">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-credit-card text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Gestión de Pagos</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Administra y monitorea el estado de todas las suscripciones</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="overflow-hidden rounded-2xl bg-white shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-gray-100">
                    <div class="h-2 bg-gradient-to-r from-green-500 to-green-600"></div>
                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-xl bg-green-100 p-3">
                                <i class="fas fa-check-circle text-xl text-green-600"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-gray-800">{{ $countActivos }}</div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-800">Suscripciones Activas</h3>
                        <p class="mb-4 text-sm text-gray-600">Usuarios con pagos al día</p>
                        <a href="{{ route('administrador.pagos.realizados') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-indigo-700 shadow-md hover:shadow-indigo-200/50">
                            <i class="fas fa-eye mr-2"></i>
                            Ver detalles
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-gray-100">
                    <div class="h-2 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-xl bg-yellow-100 p-3">
                                <i class="fas fa-clock text-xl text-yellow-600"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-gray-800">{{ $countPendientes }}</div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-800">Pagos Pendientes</h3>
                        <p class="mb-4 text-sm text-gray-600">Requieren atención inmediata</p>
                        <a href="{{ route('administrador.pagos.pendientes-fisicos') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-indigo-700 shadow-md hover:shadow-indigo-200/50">
                            <i class="fas fa-clock mr-2"></i>
                            Ver detalles
                        </a>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-gray-100">
                    <div class="h-2 bg-gradient-to-r from-gray-500 to-gray-600"></div>
                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="rounded-xl bg-gray-100 p-3">
                                <i class="fas fa-archive text-xl text-gray-600"></i>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-gray-800">{{ $countFinalizados }}</div>
                            </div>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-800">Finalizados/Cancelados</h3>
                        <p class="mb-4 text-sm text-gray-600">Suscripciones completadas</p>
                        <a href="{{ route('administrador.pagos.finalizados') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 hover:bg-indigo-700 shadow-md hover:shadow-indigo-200/50">
                            <i class="fas fa-archive mr-2"></i>
                            Ver detalles
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabla de pagos mejorada -->
            <div class="mb-8 rounded-2xl bg-white shadow-xl p-6 border border-gray-100">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Todos los registros de pagos</h2>
                        <p class="text-gray-600 text-sm">Se muestran del último al primero</p>
                    </div>
                    <div class="text-sm text-gray-600 font-medium">
                        Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} registros
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">ID</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Cliente</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Plan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Método</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Monto</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado del pago</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado de suscripción</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha de pago</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Inicio</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fin</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($pagos as $index => $pago)
                                @php
                                    $estadoPagoClase = match($pago->estado) {
                                        'completado' => 'bg-green-100 text-green-800 border-green-200',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'cancelado' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                                    };

                                    $estadoSuscripcion = optional($pago->suscripcion)->estado;
                                    $estadoSuscripcionClase = match($estadoSuscripcion) {
                                        'activa' => 'bg-green-100 text-green-800 border-green-200',
                                        'finalizada' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        'cancelada' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    };
                                    
                                    $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                                @endphp
                                <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-indigo-600 border-r border-gray-100">#{{ $pago->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium border-r border-gray-100">{{ optional($pago->usuario)->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ optional($pago->plan)->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ ucfirst($pago->metodo ?? 'N/A') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border {{ $estadoPagoClase }}">{{ ucfirst($pago->estado ?? 'N/A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border {{ $estadoSuscripcionClase }}">{{ $estadoSuscripcion ? ucfirst($estadoSuscripcion) : 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ optional($pago->suscripcion)->fecha_inicio ? $pago->suscripcion->fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ optional($pago->suscripcion)->fecha_fin ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="verComprobante({{ $pago->id }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                                <i class="fas fa-file-invoice mr-1.5"></i>Ver
                                            </button>
                                            <a href="{{ route('administrador.pagos.descargar-recibo', $pago->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                                <i class="fas fa-download mr-1.5"></i>Descargar
                                            </a>
                                            @if($pago->estado === 'completado')
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-200"
                                                    data-resend-email="{{ optional($pago->usuario)->email }}"
                                                    data-resend-url="{{ route('administrador.pagos.reenviar-correo', $pago) }}"
                                                    onclick="abrirModalReenvio(this)"
                                                >
                                                    <i class="fas fa-paper-plane mr-1.5"></i>Reenviar correo
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">No hay registros de pagos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <form method="GET" action="{{ route('administrador.pagos.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label for="per_page" class="text-sm font-medium text-gray-700">Registros por página</label>
                        <input
                            type="number"
                            min="1"
                            max="100"
                            name="per_page"
                            id="per_page"
                            value="{{ $perPage }}"
                            class="w-28 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-md hover:shadow-indigo-200/50">
                            Aplicar
                        </button>
                    </form>

                    <div>
                        {{ $pagos->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar el reenvío del correo -->
    <div id="reenvioCorreoModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/55 px-4 py-8 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="reenvioCorreoTitulo">
        <div id="reenvioCorreoDialog" class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-200">
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-purple-800 px-7 pb-7 pt-8 text-white">
                <div class="absolute -right-10 -top-12 h-36 w-36 rounded-full border-[24px] border-white/10"></div>
                <button type="button" onclick="cerrarModalReenvio()" class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
                <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-500 text-2xl shadow-lg shadow-purple-950/20">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3 id="reenvioCorreoTitulo" class="relative mt-5 text-2xl font-bold">Reenviar confirmación</h3>
                <p class="relative mt-2 text-sm leading-6 text-purple-100">Enviaremos nuevamente el resumen del pago y su comprobante PDF.</p>
            </div>

            <div class="px-7 py-7">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-500">Destinatario</p>
                    <div class="mt-2 flex items-center gap-3 text-sm font-semibold text-slate-800">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm"><i class="fas fa-envelope"></i></span>
                        <span id="reenvioCorreoDestino" class="min-w-0 break-all"></span>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-6 text-slate-600">¿Confirmas que deseas reenviar este correo de confirmación?</p>

                <form id="reenvioCorreoForm" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    <button type="button" onclick="cerrarModalReenvio()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</button>
                    <button id="confirmarReenvioBtn" type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-orange-600">
                        <i class="fas fa-paper-plane"></i> Sí, reenviar correo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reporte Mensual -->
    <div id="reporteMensualModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
        <div class="relative top-20 mx-auto w-96 rounded-2xl border bg-white p-5 shadow-2xl">
            <div class="mt-3 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-purple-100">
                    <i class="fas fa-chart-bar text-2xl text-purple-600"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium leading-6 text-gray-900">Reporte mensual</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Se generará un reporte con todas las transacciones del mes actual.
                        Elige el formato que prefieres:
                    </p>
                </div>
                <div class="mt-4 flex justify-center space-x-4">
                    <button id="btnPdfMensual" class="rounded-xl bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                        Descargar PDF
                    </button>
                    <button id="btnExcelMensual" class="rounded-xl bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        Descargar Excel
                    </button>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="cerrarModalReporte" class="w-full rounded-xl bg-gray-200 px-4 py-2 text-base font-medium text-gray-800 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Comprobante -->
    <div id="comprobanteModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-600 bg-opacity-50 px-4 py-8">
        <div class="mx-auto max-w-4xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-900">Comprobante de pago</h3>
                <button type="button" onclick="cerrarComprobanteModal()" class="rounded-xl border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    Cerrar
                </button>
            </div>
            <div id="comprobanteModalBody" class="max-h-[70vh] overflow-y-auto px-6 py-4"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const abrirReporteMensual = document.getElementById('abrirReporteMensual');
            const reporteMensualModal = document.getElementById('reporteMensualModal');
            const cerrarModalReporte = document.getElementById('cerrarModalReporte');
            const btnPdfMensual = document.getElementById('btnPdfMensual');
            const btnExcelMensual = document.getElementById('btnExcelMensual');

            if (abrirReporteMensual) {
                abrirReporteMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.remove('hidden');
                });
            }

            if (cerrarModalReporte) {
                cerrarModalReporte.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                });
            }

            if (btnPdfMensual) {
                btnPdfMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                    window.open('/administrador/pagos/reporte-mensual/pdf', '_blank');
                });
            }

            if (btnExcelMensual) {
                btnExcelMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                    window.open('/administrador/pagos/reporte-mensual/excel', '_blank');
                });
            }

            const reenvioModal = document.getElementById('reenvioCorreoModal');
            const reenvioForm = document.getElementById('reenvioCorreoForm');
            const confirmarReenvioBtn = document.getElementById('confirmarReenvioBtn');

            reenvioModal?.addEventListener('click', function (event) {
                if (event.target === reenvioModal) cerrarModalReenvio();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !reenvioModal?.classList.contains('hidden')) cerrarModalReenvio();
            });

            reenvioForm?.addEventListener('submit', function () {
                confirmarReenvioBtn.disabled = true;
                confirmarReenvioBtn.classList.add('opacity-70', 'cursor-wait');
                confirmarReenvioBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando...';
            });
        });

        function abrirModalReenvio(button) {
            const modal = document.getElementById('reenvioCorreoModal');
            const dialog = document.getElementById('reenvioCorreoDialog');
            const form = document.getElementById('reenvioCorreoForm');
            const destination = document.getElementById('reenvioCorreoDestino');

            form.action = button.dataset.resendUrl;
            destination.textContent = button.dataset.resendEmail || 'Correo no disponible';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            dialog.animate([
                { opacity: 0, transform: 'translateY(18px) scale(.96)' },
                { opacity: 1, transform: 'translateY(0) scale(1)' }
            ], { duration: 220, easing: 'cubic-bezier(.22,.61,.36,1)' });
        }

        function cerrarModalReenvio() {
            const modal = document.getElementById('reenvioCorreoModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function verComprobante(pagoId) {
            fetch(`/administrador/pagos/ver-recibo/${pagoId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('comprobanteModalBody').innerHTML = data.html;
                    document.getElementById('comprobanteModal').classList.remove('hidden');
                })
                .catch(() => {
                    alert('No se pudo cargar el comprobante.');
                });
        }

        function cerrarComprobanteModal() {
            document.getElementById('comprobanteModal').classList.add('hidden');
        }
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

        /* Estilos de los botones de acción */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 999px;
            border: 1.5px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: all 200ms cubic-bezier(0.22, 0.61, 0.36, 1);
            text-decoration: none;
            color: white;
            position: relative;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .btn-action:active {
            transform: translateY(1px);
        }

        .btn-action i {
            font-size: 0.875rem;
        }

        .btn-action__mark {
            display: inline-grid;
            place-items: center;
            width: 16px;
            height: 16px;
            color: rgba(255, 255, 255, 0.8);
            transform: rotate(-14deg);
            transition: transform 600ms cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .btn-action__mark svg {
            width: 100%;
            height: 100%;
            fill: currentColor;
            display: block;
        }

        .btn-action:hover .btn-action__mark {
            transform: rotate(18deg);
        }

        /* Colores de los botones */
        .btn-blue {
            background: #2563EB;
            border-color: #2563EB;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);
        }

        .btn-blue:hover {
            background: #1D4ED8;
            border-color: #1D4ED8;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.35);
        }

        .btn-indigo {
            background: #4F46E5;
            border-color: #4F46E5;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);
        }

        .btn-indigo:hover {
            background: #4338CA;
            border-color: #4338CA;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.35);
        }

        .btn-purple {
            background: #7C3AED;
            border-color: #7C3AED;
            box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.25);
        }

        .btn-purple:hover {
            background: #6D28D9;
            border-color: #6D28D9;
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.35);
        }

        /* Animación */
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
            
            .btn-action {
                justify-content: center;
                width: 100%;
                padding: 0.75rem 1.25rem;
            }

            .btn-action__mark {
                display: none;
            }
        }
    </style>
@endsection
