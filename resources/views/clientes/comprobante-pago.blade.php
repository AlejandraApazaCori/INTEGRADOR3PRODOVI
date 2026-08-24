@php
    $transaccionLibelula = $pago->libelulaTransaction;
    $idTransaccionSistema = $transaccionLibelula?->identifier
        ?: ($pago->codigo_pago ?: 'PAGO-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
    $idTransaccionLibelula = $transaccionLibelula?->libelula_transaction_id
        ?: $pago->provider_transaction_id;
@endphp

<div class="bg-white rounded-lg shadow-xl overflow-hidden">
    <!-- Encabezado -->
    <header class="bg-gradient-to-r from-gray-700 to-gray-900 p-6 text-white text-center">
        <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" class="h-12 mx-auto mb-3 opacity-90">
        
    </header>

    <main class="p-6 md:p-8">
        <!-- Información Principal en Dos Columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Columna Izquierda: Información del Pago -->
            <section class="bg-gray-50 rounded-lg p-5">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-receipt mr-2 text-blue-600"></i> Información del Pago
                </h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">N° de Comprobante:</dt>
                        <dd class="font-semibold text-gray-900">{{ $pago->comprobantePago->numero_formateado ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-600">ID transacción (sistema):</dt>
                        <dd class="max-w-[55%] break-all text-right font-mono text-xs font-semibold text-gray-900">{{ $idTransaccionSistema }}</dd>
                    </div>
                    @if($idTransaccionLibelula)
                    <div class="flex justify-between gap-4">
                        <dt class="font-medium text-gray-600">ID transacción (Libélula):</dt>
                        <dd class="max-w-[55%] break-all text-right font-mono text-xs font-semibold text-gray-900">{{ $idTransaccionLibelula }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Fecha de Emisión:</dt>
                        <dd class="text-gray-900">{{ $pago->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Fecha de Pago:</dt>
                        <dd class="text-gray-900">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Método:</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $pago->metodo === 'qr' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($pago->metodo) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Estado:</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $pago->estado === 'completado' ? 'bg-green-100 text-green-800' :
                                    ($pago->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800') }}">
                                {{ ucfirst($pago->estado) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- Columna Derecha: Datos del Cliente -->
            <section class="bg-gray-50 rounded-lg p-5">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-blue-600"></i> Datos del Cliente
                </h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Nombre:</dt>
                        <dd class="text-gray-900">{{ optional($pago->usuario)->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">Email:</dt>
                        <dd class="text-gray-900">{{ optional($pago->usuario)->email ?? 'N/A' }}</dd>
                    </div>
                    @if($pago->fecha_aprobacion)
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-600">F. Aprobación:</dt>
                        <dd class="text-gray-900">{{ $pago->fecha_aprobacion->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </section>
        </div>

        <!-- Detalles del Pago (Estilo Factura) -->
        <section class="border-t-2 border-gray-200 pt-6">
            <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                <i class="fas fa-list-alt mr-2 text-blue-600"></i> Detalles del Cargo
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 font-semibold text-gray-700">Concepto</th>
                            <th class="text-right py-2 font-semibold text-gray-700">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-4 text-gray-800">
                                <p class="font-medium">{{ optional($pago->plan)->nombre ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Suscripción al plan</p>
                            </td>
                            <td class="py-4 text-right">
                                <p class="text-2xl font-bold text-gray-900">{{ $pago->moneda }} {{ number_format($pago->monto, 2) }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Pie de Página / Aviso de Aprobación -->
    @if($pago->estado === 'completado')
    <footer class="bg-green-50 border-t-2 border-green-200 p-4">
        <p class="text-center text-green-800 font-semibold">
            <i class="fas fa-check-circle mr-2"></i> Pago verificado y aprobado correctamente
        </p>
    </footer>
    @endif
</div>
