<table>
    <tr><td colspan="20">{{ $reportTitle }}</td></tr>
    <tr><td colspan="20">Generado: {{ $generatedAt->format('d/m/Y H:i') }} · Total de registros: {{ $pagos->count() }}</td></tr>
    <tr>
        <td colspan="13"></td>
        <th>Estado del pago</th><th>Cantidad</th><th>Porcentaje</th><td></td>
        <th>Método de pago</th><th>Cantidad</th><th>Porcentaje</th>
    </tr>
    @for($index = 0; $index < 16; $index++)
        <tr>
            <td colspan="13"></td>
            @if(isset($statusStats[$index]))
                <td>{{ $statusStats[$index]['label'] }}</td><td>{{ $statusStats[$index]['count'] }}</td><td>{{ $statusStats[$index]['percentage'] }}</td>
            @else
                <td></td><td></td><td></td>
            @endif
            <td></td>
            @if(isset($methodStats[$index]))
                <td>{{ $methodStats[$index]['label'] }}</td><td>{{ $methodStats[$index]['count'] }}</td><td>{{ $methodStats[$index]['percentage'] }}</td>
            @else
                <td></td><td></td><td></td>
            @endif
        </tr>
    @endfor
    <tr>
        <th>N.º</th><th>Cliente</th><th>Plan</th><th>Método</th><th>Monto</th><th>Estado del pago</th><th>Estado de suscripción</th>
        <th>Fecha de pago</th><th>Inicio</th><th>Fin</th><th>ID transacción (sistema)</th><th>ID transacción (Libélula)</th><th>Referencia</th>
    </tr>
    @foreach($pagos as $index => $pago)
        @php
            $libelula = $pago->libelulaTransaction;
            $systemId = $libelula?->identifier ?: ($pago->codigo_pago ?: 'PAGO-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
            $libelulaId = $libelula?->libelula_transaction_id ?: $pago->provider_transaction_id;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td><td>{{ optional($pago->usuario)->name ?? 'N/A' }}</td><td>{{ optional($pago->plan)->nombre ?? 'N/A' }}</td>
            <td>{{ $pago->metodo === 'qr' ? 'QR' : ucfirst((string) $pago->metodo) }}</td><td>{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td>
            <td>{{ ucfirst((string) $pago->estado) }}</td><td>{{ ucfirst((string) (optional($pago->suscripcion)->estado ?? 'N/A')) }}</td>
            <td>{{ optional($pago->fecha_pago)->format('d/m/Y H:i') ?? 'N/A' }}</td><td>{{ optional(optional($pago->suscripcion)->fecha_inicio)->format('d/m/Y') ?? 'N/A' }}</td>
            <td>{{ optional(optional($pago->suscripcion)->fecha_fin)->format('d/m/Y') ?? 'N/A' }}</td><td>{{ $systemId }}</td><td>{{ $libelulaId ?: 'No aplica' }}</td><td>{{ $pago->provider_reference ?: 'N/A' }}</td>
        </tr>
    @endforeach
</table>
