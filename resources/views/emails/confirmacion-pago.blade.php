<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light">
    <title>Pago confirmado en PRODOVI</title>
    <style>
        :root { color-scheme:light only; supported-color-schemes:light; }
        body,.email-background { background-color:#f1f4f4!important; }
        .brand-header { background-color:#117e8c!important; }
        .brand-button { background-color:#ef6c22!important; }
    </style>
</head>
<body bgcolor="#f1f4f4" style="margin:0;padding:0;background-color:#f1f4f4;font-family:Arial,Helvetica,sans-serif;color:#273238;">
<div class="email-background" style="width:100%;padding:36px 16px;box-sizing:border-box;background-color:#f1f4f4;">
    <div style="max-width:720px;margin:0 auto;">
        <div style="overflow:hidden;border:1px solid #d9e4e5;border-radius:22px;background:#ffffff;box-shadow:0 20px 45px rgba(17,126,140,.14);">
            <div class="brand-header" bgcolor="#117e8c" style="padding:28px 20px;background-color:#117e8c;text-align:center;">
                <img src="{{ $message->embed(public_path('imagenes/logoblanco.png')) }}" alt="PRODOVI" width="170" style="display:block;width:170px;max-width:70%;height:auto;margin:0 auto;">
            </div>
            <div style="height:6px;background-color:#ef6c22;background-image:linear-gradient(90deg,#5b2b76 0 20%,#ef6c22 20% 40%,#f5a900 40% 60%,#7da533 60% 80%,#117e8c 80% 100%);"></div>

            <div style="padding:38px 34px 34px;">
                <div style="width:62px;height:62px;margin:0 auto 18px;border-radius:20px;background:#edf8ee;color:#4f8d2d;font-size:30px;line-height:62px;text-align:center;font-weight:800;">✓</div>
                <h1 style="margin:0 0 14px;color:#117e8c;font-size:28px;line-height:1.25;text-align:center;font-weight:800;">Pago confirmado correctamente</h1>
                <p style="max-width:570px;margin:0 auto 28px;color:#607078;font-size:15px;line-height:1.7;text-align:center;">Tu pago fue aprobado y tu suscripción en PRODOVI ya se encuentra activa.</p>

                <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#33444b;">Hola <strong style="color:#5b2b76;">{{ $pago->usuario?->name ?: 'bienvenido' }}</strong>:</p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#33444b;">Confirmamos que recibimos tu pago mediante <strong style="color:#ef6c22;">{{ $pago->metodo === 'qr' ? 'QR' : 'pago físico' }}</strong>. Gracias por confiar en PRODOVI para hacer crecer la presencia digital de tu marca.</p>

                <div style="margin:0 0 26px;padding:21px 22px;border:1px solid #cce2e5;border-radius:16px;background:#f5fbfc;">
                    <p style="margin:0 0 6px;color:#5b2b76;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">Resumen del pago</p>
                    <div style="width:54px;height:3px;margin:0 0 17px;border-radius:999px;background:#ef6c22;"></div>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;color:#4b5b62;font-size:14px;line-height:1.6;">
                        <tr><td style="padding:0 12px 10px 0;font-weight:700;color:#117e8c;">Comprobante</td><td style="padding:0 0 10px;text-align:right;font-weight:700;color:#5b2b76;">N.º {{ $pago->comprobantePago?->numero_formateado ?? str_pad((string) $pago->id, 5, '0', STR_PAD_LEFT) }}</td></tr>
                        <tr><td style="padding:0 12px 10px 0;font-weight:700;color:#117e8c;">Plan</td><td style="padding:0 0 10px;text-align:right;">{{ $pago->plan?->nombre ?? 'Plan PRODOVI' }}</td></tr>
                        <tr><td style="padding:0 12px 10px 0;font-weight:700;color:#117e8c;">Método</td><td style="padding:0 0 10px;text-align:right;">{{ $pago->metodo === 'qr' ? 'QR' : 'Pago físico' }}</td></tr>
                        <tr><td style="padding:0 12px 10px 0;font-weight:700;color:#117e8c;">Monto</td><td style="padding:0 0 10px;text-align:right;font-weight:800;color:#5b2b76;">{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td></tr>
                        <tr><td style="padding:0 12px 10px 0;font-weight:700;color:#117e8c;">Fecha</td><td style="padding:0 0 10px;text-align:right;">{{ ($pago->fecha_pago ?? $pago->fecha_aprobacion ?? $pago->updated_at)->format('d/m/Y H:i') }}</td></tr>
                        <tr><td style="padding:0 12px 0 0;font-weight:700;color:#117e8c;">Estado</td><td style="padding:0;text-align:right;"><span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#dcfce7;color:#15803d;font-size:13px;font-weight:700;">Aprobado</span></td></tr>
                    </table>
                </div>

                <div style="margin:0 0 27px;padding:18px 20px;border:1px solid #eadfc2;border-radius:14px;background:#fffaf0;">
                    <p style="margin:0 0 7px;color:#5b2b76;font-size:14px;font-weight:800;">Tu comprobante está adjunto</p>
                    <p style="margin:0;color:#4b5b62;font-size:14px;line-height:1.65;">Incluimos el comprobante en formato PDF para que puedas guardarlo o consultarlo cuando lo necesites.</p>
                </div>

                <div style="margin:0 0 28px;text-align:center;">
                    <a href="{{ $dashboardUrl }}" class="brand-button" style="display:inline-block;padding:15px 34px;border:1px solid #f5a900;border-radius:10px;background-color:#ef6c22;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 8px 22px rgba(239,108,34,.25);">Ir a mi dashboard</a>
                </div>

                <div style="padding:18px 20px;border:1px solid #e0e5e7;border-radius:14px;background:#f7f8f8;">
                    <p style="margin:0;color:#5d6b72;font-size:14px;line-height:1.7;">Si no reconoces este pago, responde a este correo para que nuestro equipo pueda ayudarte.</p>
                </div>
                <div style="margin-top:28px;text-align:center;"><p style="margin:0 0 6px;color:#718087;font-size:14px;">Atentamente,</p><p style="margin:0;color:#117e8c;font-size:15px;font-weight:800;">Equipo PRODOVI</p></div>
            </div>
        </div>
        <p style="margin:20px 0 0;color:#829097;font-size:12px;line-height:1.6;text-align:center;">Este mensaje confirma automáticamente un pago aprobado en PRODOVI.</p>
    </div>
</div>
</body>
</html>
