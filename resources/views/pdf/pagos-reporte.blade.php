<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page { margin: 22px 20px 66px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #374151; font-family: DejaVu Sans, sans-serif; font-size: 7px; }
        .report-head { margin: -22px -20px 14px; padding: 10px 22px; background: #343a40; }
        .report-head table { width: 100%; border-collapse: collapse; }
        .report-head td { padding: 0; border: 0; vertical-align: middle; }
        .report-head-logo { width: 125px; }
        .report-head-logo img { display: block; width: 92px; }
        .report-head-copy { text-align: right; }
        .report-head h1 { margin: 0; color: #fff; font-size: 17px; }
        .report-head p { margin: 4px 0 0; color: #d9ded6; font-size: 8px; }
        .summary { width: 100%; margin-bottom: 13px; border-collapse: separate; border-spacing: 7px 0; table-layout: fixed; }
        .summary td { padding: 8px; border: 1px solid #d7d9dc; background: #f3f4f6; text-align: center; }
        .summary strong { display: block; margin-bottom: 3px; color: #343a40; font-size: 11px; }
        .summary span { color: #6b7280; font-size: 7px; text-transform: uppercase; }
        .charts { width: 100%; margin: 0 0 13px; border-collapse: separate; border-spacing: 8px 0; table-layout: fixed; }
        .charts td { width: 50%; padding: 0; border: 1px solid #e1e3de; border-radius: 10px; vertical-align: top; }
        .charts img { display: block; width: 100%; max-height: 205px; }
        .payments-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .payments-table thead { display: table-header-group; }
        .payments-table th { padding: 6px 4px; border: 1px solid #444a50; background: #565d64; color: #fff; font-size: 6px; text-align: left; }
        .payments-table td { padding: 5px 4px; border: 1px solid #d7d9dc; vertical-align: top; word-wrap: break-word; }
        .payments-table tbody tr:nth-child(even) td { background: #f3f4f6; }
        .payments-table tr { page-break-inside: avoid; }
        .number { width: 3%; text-align: center; }
        .client { width: 10%; }.plan { width: 8%; }.method { width: 5%; }.amount { width: 7%; }
        .status { width: 7%; }.date-time { width: 8%; }.date { width: 6%; }.transaction { width: 13%; }.reference { width: 9%; }
        .empty { padding: 24px !important; color: #737a70; text-align: center; }
        .pdf-footer { position: fixed; right: -20px; bottom: -66px; left: -20px; height: 52px; background: #343a40; }
        .pdf-footer table { width: 100%; height: 52px; border-collapse: collapse; }
        .pdf-footer td { height: 52px; padding: 0 28px; border: 0; vertical-align: middle; }
        .pdf-footer-logo { width: 120px; }.pdf-footer-logo img { display: block; width: 84px; }
        .pdf-footer-copy { color: #d9ded6; font-size: 7px; letter-spacing: .04em; text-align: center; text-transform: uppercase; }
        .pdf-footer-page-space { width: 120px; }
    </style>
</head>
<body>
    <footer class="pdf-footer">
        <table><tr>
            <td class="pdf-footer-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
            <td class="pdf-footer-copy">PRODOVI · Reporte administrativo de pagos</td>
            <td class="pdf-footer-page-space"></td>
        </tr></table>
    </footer>

    <header class="report-head">
        <table><tr>
            <td class="report-head-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
            <td class="report-head-copy">
                <h1>{{ $reportTitle }}</h1>
                <p>Generado: {{ now()->format('d/m/Y H:i') }} · Total de registros: {{ $pagos->count() }}</p>
            </td>
        </tr></table>
    </header>

    <table class="summary"><tr>
        <td><strong>{{ $summary['total_income'] }}</strong><span>Total de ingresos</span></td>
        <td><strong>{{ $summary['most_hired_plan'] }}</strong><span>Plan más contratado ({{ $summary['most_hired_plan_count'] }})</span></td>
        <td><strong>{{ $summary['total_records'] }}</strong><span>Total de registros</span></td>
    </tr></table>

    @if($statusChart && $methodChart)
        <table class="charts"><tr>
            <td><img src="{{ $statusChart }}" alt="Distribución por estado del pago"></td>
            <td><img src="{{ $methodChart }}" alt="Distribución por método de pago"></td>
        </tr></table>
    @endif

    <table class="payments-table">
        <thead><tr>
            <th class="number">N.º</th><th class="client">Cliente</th><th class="plan">Plan</th><th class="method">Método</th>
            <th class="amount">Monto</th><th class="status">Estado pago</th><th class="status">Suscripción</th>
            <th class="date-time">Fecha pago</th><th class="date">Inicio</th><th class="date">Fin</th>
            <th class="transaction">ID sistema</th><th class="transaction">ID Libélula</th><th class="reference">Referencia</th>
        </tr></thead>
        <tbody>
        @forelse($pagos as $index => $pago)
            @php
                $libelula = $pago->libelulaTransaction;
                $systemId = $libelula?->identifier ?: ($pago->codigo_pago ?: 'PAGO-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
                $libelulaId = $libelula?->libelula_transaction_id ?: $pago->provider_transaction_id;
            @endphp
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td>{{ optional($pago->usuario)->name ?? 'N/A' }}</td><td>{{ optional($pago->plan)->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->metodo === 'qr' ? 'QR' : ucfirst((string) $pago->metodo) }}</td>
                <td>{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td>
                <td>{{ ucfirst((string) $pago->estado) }}</td><td>{{ ucfirst((string) (optional($pago->suscripcion)->estado ?? 'N/A')) }}</td>
                <td>{{ optional($pago->fecha_pago)->format('d/m/Y H:i') ?? 'N/A' }}</td>
                <td>{{ optional(optional($pago->suscripcion)->fecha_inicio)->format('d/m/Y') ?? 'N/A' }}</td>
                <td>{{ optional(optional($pago->suscripcion)->fecha_fin)->format('d/m/Y') ?? 'N/A' }}</td>
                <td>{{ $systemId }}</td><td>{{ $libelulaId ?: 'No aplica' }}</td><td>{{ $pago->provider_reference ?: 'N/A' }}</td>
            </tr>
        @empty
            <tr><td colspan="13" class="empty">No existen pagos para este reporte.</td></tr>
        @endforelse
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf, $fontMetrics)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $pdf->page_text($pdf->get_width() - 108, $pdf->get_height() - 24, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7, [1, 1, 1]);
        }
    </script>
</body>
</html>
