<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante {{ $comprobante->numero_formateado }} - PRODOVI</title>
    <style>
        @page { size: letter portrait; margin: 24px 28px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #fff; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .page { width: 100%; }
        .header-table td { width: 33.333%; }
        .company-name { font-size: 12px; font-weight: 700; }
        .company-copy { padding-top: 5px; line-height: 1.45; }
        .brand { text-align: center; }
        .brand img { width: 145px; margin-top: 7px; }
        .document-title { margin-top: 5px; font-size: 15px; font-weight: 700; }
        .document-subtitle { font-size: 8px; }
        .document-data { padding: 9px 0 0 22px; }
        .document-data td { padding: 1px 0; }
        .document-data .label { width: 46%; font-weight: 700; }
        .document-data .value { width: 54%; word-break: break-all; }
        .accent-line { height: 4px; margin: 14px 0 13px; background: #5a4ba8; border-right: 110px solid #f28a2f; }
        .client-table { margin-bottom: 7px; }
        .client-table td { padding: 1px 0; }
        .client-table .label { width: 18%; font-weight: 700; }
        .client-table .value { width: 42%; }
        .client-table .label-right { width: 18%; padding-left: 16px; font-weight: 700; }
        .client-table .value-right { width: 22%; }
        .items { table-layout: fixed; }
        .items thead { border-top: 1px solid #111; border-bottom: 1px solid #111; }
        .items th { padding: 5px 3px; font-size: 7px; line-height: 1.1; text-align: center; text-transform: uppercase; }
        .items td { height: 48px; padding: 9px 3px; font-size: 8px; }
        .items tbody { border-bottom: 1px solid #111; }
        .center { text-align: center; }
        .right { text-align: right; }
        .description { padding-left: 8px !important; }
        .summary-layout { margin-top: 6px; }
        .summary-layout > tbody > tr > td:first-child { width: 64%; padding-top: 52px; }
        .summary-layout > tbody > tr > td:last-child { width: 36%; padding-left: 18px; }
        .amount-words { font-size: 8px; }
        .totals td { padding: 3px 0; font-size: 8px; }
        .totals .total-label { width: 70%; font-weight: 700; }
        .totals .total-value { width: 30%; text-align: right; }
        .totals .grand-total td { padding-top: 5px; border-top: 1px solid #111; font-size: 9px; }
        .footer-layout { margin-top: 10px; }
        .footer-layout > tbody > tr > td:first-child { width: 77%; padding-right: 14px; }
        .footer-layout > tbody > tr > td:last-child { width: 23%; text-align: right; }
        .legal { padding-top: 8px; border-top: 1px solid #777; font-size: 7px; line-height: 1.5; }
        .legal strong { display: block; margin-top: 5px; }
        .qr { width: 112px; height: 112px; }
        .qr-caption { margin-top: 3px; color: #555; font-size: 6px; text-align: center; }
        .status-stamp { display: inline-block; margin-top: 9px; padding: 4px 11px; border: 1px solid #5a4ba8; color: #5a4ba8; font-size: 8px; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body>
@php
    $currency = strtoupper((string) ($pago->moneda ?? 'BOB'));
    $currencyLabel = $currency === 'BOB' ? 'Bs.' : $currency;
    $amount = number_format((float) $pago->monto, 2, '.', ',');
    $issuedAt = $pago->fecha_pago ?? $pago->fecha_aprobacion ?? $pago->created_at;
    $reference = $pago->provider_reference ?: $pago->provider_transaction_id ?: '—';
@endphp

<div class="page">
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">PRODOVI</div>
                <div class="company-copy">
                    Real Plaza Hotel &amp; Convention Center<br>
                    Av. Arce N.º 2177, frente a Plaza Bolivia<br>
                    La Paz, Bolivia<br>
                    Teléfono: +591 79561365<br>
                    Email: info@prodovi.com
                </div>
            </td>
            <td class="brand">
                <img src="{{ public_path('imagenes/logonegro.png') }}" alt="PRODOVI">
                <div class="document-title">COMPROBANTE DE PAGO</div>
                <div class="document-subtitle">Documento de respaldo de transacción</div>
                <div class="status-stamp">{{ ucfirst($pago->estado) }}</div>
            </td>
            <td>
                <table class="document-data">
                    <tr><td class="label">Comprobante N.º</td><td class="value">{{ $comprobante->numero_formateado }}</td></tr>
                    <tr><td class="label">Pago N.º</td><td class="value">{{ $pago->id }}</td></tr>
                    <tr><td class="label">Método</td><td class="value">{{ ucfirst($pago->metodo) }}</td></tr>
                    <tr><td class="label">Proveedor</td><td class="value">{{ $pago->provider ? ucfirst($pago->provider) : 'PRODOVI' }}</td></tr>
                    <tr><td class="label">Referencia</td><td class="value">{{ $reference }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="accent-line"></div>

    <table class="client-table">
        <tr>
            <td class="label">Fecha:</td><td class="value">{{ $issuedAt->format('d/m/Y H:i:s') }}</td>
            <td class="label-right">Estado:</td><td class="value-right">{{ ucfirst($pago->estado) }}</td>
        </tr>
        <tr>
            <td class="label">Nombre/Razón social:</td><td class="value">{{ $pago->usuario?->name ?? 'Cliente no disponible' }}</td>
            <td class="label-right">Código cliente:</td><td class="value-right">{{ str_pad((string) $pago->usuario_id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">Correo:</td><td class="value">{{ $pago->usuario?->email ?? '—' }}</td>
            <td class="label-right">Teléfono:</td><td class="value-right">{{ $pago->usuario?->phone ?? '—' }}</td>
        </tr>
    </table>

    <table class="items">
        <colgroup>
            <col style="width:9%"><col style="width:8%"><col style="width:10%"><col style="width:38%">
            <col style="width:13%"><col style="width:10%"><col style="width:12%">
        </colgroup>
        <thead><tr>
            <th>Código<br>servicio</th><th>Cantidad</th><th>Unidad de<br>medida</th><th>Descripción</th>
            <th>Precio<br>unitario</th><th>Descuento</th><th>Subtotal</th>
        </tr></thead>
        <tbody><tr>
            <td class="center">{{ str_pad((string) $pago->plan_id, 3, '0', STR_PAD_LEFT) }}</td>
            <td class="center">1.00</td><td class="center">SERVICIO</td>
            <td class="description"><strong>{{ $pago->plan?->nombre ?? 'Servicio PRODOVI' }}</strong><br>{{ $pago->plan?->descripcion ?? 'Servicio de suscripción' }}</td>
            <td class="right">{{ $amount }}</td><td class="right">0.00</td><td class="right">{{ $amount }}</td>
        </tr></tbody>
    </table>

    <table class="summary-layout">
        <tr>
            <td><div class="amount-words">Son: {{ $currencyLabel }} {{ $amount }}</div></td>
            <td><table class="totals">
                <tr><td class="total-label">SUBTOTAL {{ $currencyLabel }}</td><td class="total-value">{{ $amount }}</td></tr>
                <tr><td class="total-label">DESCUENTO {{ $currencyLabel }}</td><td class="total-value">0.00</td></tr>
                <tr class="grand-total"><td class="total-label">TOTAL PAGADO {{ $currencyLabel }}</td><td class="total-value">{{ $amount }}</td></tr>
            </table></td>
        </tr>
    </table>

    <table class="footer-layout">
        <tr>
            <td><div class="legal">
                Este comprobante certifica el registro del pago indicado y fue generado electrónicamente por PRODOVI.
                <strong>Conserve este documento como respaldo de su transacción.</strong>
                La validez de la operación está sujeta a la confirmación del medio de pago y a los términos del servicio contratado.
            </div></td>
            <td>
                <img class="qr" src="{{ public_path('imagenes/qr-code-example.png') }}" alt="Código QR">
                <div class="qr-caption">COMPROBANTE {{ $comprobante->numero_formateado }}</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
