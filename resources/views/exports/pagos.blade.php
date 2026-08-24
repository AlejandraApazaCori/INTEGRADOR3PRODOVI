<table>
    <tr>
        <td colspan="13"><strong>{{ isset($summary['monthly_report']) ? 'Reporte mensual de pagos - '.$summary['monthly_report'] : ($reportTitle ?? 'Reporte de pagos') }}</strong></td>
    </tr>
    <tr>
        <td colspan="13">Generado: {{ ($generatedAt ?? now())->format('d/m/Y H:i') }} · Total de registros: {{ $pagos->count() }}</td>
    </tr>
    <tr><td colspan="13"></td></tr>
    <tr>
        <th>Total de ingresos</th>
        <th>Plan más contratado</th>
        <th>Cantidad del plan</th>
        <th>Total de registros</th>
        <th colspan="9"></th>
    </tr>
    <tr>
        <td>{{ $summary['total_income'] ?? '0,00 BS' }}</td>
        <td>{{ $summary['most_hired_plan'] ?? 'N/A' }}</td>
        <td>{{ $summary['most_hired_plan_count'] ?? 0 }}</td>
        <td>{{ $summary['total_records'] ?? 0 }}</td>
        <td colspan="9"></td>
    </tr>
    <tr><td colspan="13"></td></tr>
    <tr>
        <th>N.º</th>
        <th>Cliente</th>
        <th>Plan</th>
        <th>Método</th>
        <th>Monto</th>
        <th>Estado del pago</th>
        <th>Estado de suscripción</th>
        <th>Fecha de pago</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>ID transacción (sistema)</th>
        <th>ID transacción (Libélula)</th>
        <th>Referencia</th>
    </tr>
    @forelse($pagos as $index => $pago)
        @php
            $libelula = $pago->libelulaTransaction;
            $systemId = $libelula?->identifier ?: ($pago->codigo_pago ?: 'PAGO-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
            $libelulaId = $libelula?->libelula_transaction_id ?: $pago->provider_transaction_id;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ optional($pago->usuario)->name ?? 'N/A' }}</td>
            <td>{{ optional($pago->plan)->nombre ?? 'N/A' }}</td>
            <td>{{ $pago->metodo === 'qr' ? 'QR' : ucfirst((string) $pago->metodo) }}</td>
            <td>{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td>
            <td>{{ ucfirst((string) $pago->estado) }}</td>
            <td>{{ ucfirst((string) (optional($pago->suscripcion)->estado ?? 'N/A')) }}</td>
            <td>{{ optional($pago->fecha_pago)->format('d/m/Y H:i') ?? 'N/A' }}</td>
            <td>{{ optional(optional($pago->suscripcion)->fecha_inicio)->format('d/m/Y') ?? 'N/A' }}</td>
            <td>{{ optional(optional($pago->suscripcion)->fecha_fin)->format('d/m/Y') ?? 'N/A' }}</td>
            <td>{{ $systemId }}</td>
            <td>{{ $libelulaId ?: 'No aplica' }}</td>
            <td>{{ $pago->provider_reference ?: 'N/A' }}</td>
        </tr>
    @empty
        <tr><td colspan="13">No existen pagos para este reporte.</td></tr>
    @endforelse
</table>
