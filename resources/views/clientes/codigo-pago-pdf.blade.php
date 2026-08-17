<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Codigo de pago {{ $codigoPago->codigo }}</title>
    <style>
        body { margin: 0; padding: 42px; color: #222; font-family: DejaVu Sans, sans-serif; }
        .document { border: 2px solid #117e8c; padding: 34px; }
        .brand { color: #5b2b76; font-size: 24px; font-weight: bold; }
        .title { margin: 28px 0 8px; color: #117e8c; font-size: 20px; }
        .subtitle { margin: 0 0 28px; color: #666; font-size: 12px; }
        .code { margin: 25px 0; padding: 22px; border: 2px dashed #ef6c22; background: #f7f7f7; font-size: 30px; font-weight: bold; letter-spacing: 4px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 0; border-bottom: 1px solid #ddd; font-size: 13px; }
        td:first-child { width: 38%; color: #666; }
        .office { margin-top: 28px; padding: 18px; background: #f1f7f8; font-size: 12px; line-height: 1.7; }
        .notice { margin-top: 24px; color: #555; font-size: 11px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="document">
        <div class="brand">PRODOVI Digital</div>
        <h1 class="title">Codigo para pago fisico</h1>
        <p class="subtitle">Presenta este documento en la oficina para completar la contratacion.</p>

        <div class="code">{{ $codigoPago->codigo }}</div>

        <table>
            <tr><td>Cliente</td><td>{{ $pago->usuario->name }}</td></tr>
            <tr><td>Plan</td><td>{{ $pago->plan->nombre }}</td></tr>
            <tr><td>Monto</td><td>{{ number_format($pago->monto, 2) }} {{ $pago->moneda === 'BS' ? 'Bs' : $pago->moneda }}</td></tr>
            <tr><td>Estado</td><td>Pendiente de pago</td></tr>
            <tr><td>Fecha de emision</td><td>{{ $pago->created_at->format('d/m/Y H:i') }}</td></tr>
        </table>

        <div class="office">
            <strong>Oficina:</strong> Real Plaza Hotel &amp; Convention Center, Av. Arce #2177,<br>
            frente a Plaza Bolivia, La Paz, Bolivia.<br>
            <strong>WhatsApp:</strong> +591 79561365
        </div>

        <p class="notice">La suscripcion se activara cuando el personal de PRODOVI confirme el pago asociado a este codigo.</p>
    </div>
</body>
</html>
