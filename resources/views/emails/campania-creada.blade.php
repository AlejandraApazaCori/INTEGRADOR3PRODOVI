<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light">
    <title>Tu campaña ya está activa</title>
</head>
<body bgcolor="#f1f4f4" style="margin:0;padding:0;background-color:#f1f4f4;font-family:Arial,Helvetica,sans-serif;color:#273238;">
    <div style="width:100%;padding:36px 16px;box-sizing:border-box;background-color:#f1f4f4;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="overflow:hidden;border:1px solid #d9e4e5;border-radius:22px;background:#ffffff;box-shadow:0 20px 45px rgba(17,126,140,.14);">
                <div bgcolor="#117e8c" style="padding:28px 20px;background-color:#117e8c;text-align:center;">
                    <img src="{{ $message->embed(public_path('imagenes/logoblanco.png')) }}" alt="PRODOVI" width="170" style="display:block;width:170px;max-width:70%;height:auto;margin:0 auto;">
                </div>
                <div style="height:6px;background-color:#ef6c22;background-image:linear-gradient(90deg,#5b2b76 0 20%,#ef6c22 20% 40%,#f5a900 40% 60%,#7da533 60% 80%,#117e8c 80% 100%);"></div>

                <div style="padding:38px 34px 34px;">
                    <h1 style="margin:0 0 14px;color:#117e8c;font-size:28px;line-height:1.25;text-align:center;font-weight:800;">
                        ¡Tu campaña ya está activa!
                    </h1>
                    <p style="max-width:570px;margin:0 auto 28px;color:#607078;font-size:15px;line-height:1.7;text-align:center;">
                        El equipo de PRODOVI terminó de preparar tu campaña. Desde ahora podrás seguir su avance, revisar el contenido y comunicarte con el equipo desde tu panel.
                    </p>

                    <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#33444b;">
                        Hola <strong style="color:#5b2b76;">{{ $user->name ?: 'bienvenido' }}</strong>:
                    </p>

                    <div style="margin:0 0 26px;padding:20px;border:1px solid #cce2e5;border-left:5px solid #117e8c;border-radius:14px;background:#f5fbfc;">
                        <p style="margin:0 0 8px;color:#5b2b76;font-size:14px;font-weight:800;">Campaña activa</p>
                        <p style="margin:0;color:#33444b;font-size:18px;line-height:1.5;font-weight:700;">{{ $campania->nombre }}</p>
                        <p style="margin:9px 0 0;color:#607078;font-size:14px;line-height:1.6;">
                            Vigencia: {{ \Carbon\Carbon::parse($campania->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($campania->fecha_fin)->format('d/m/Y') }}
                        </p>
                    </div>

                    <div style="margin:0 0 28px;text-align:center;">
                        <p style="max-width:540px;margin:0 auto 18px;color:#33444b;font-size:15px;line-height:1.7;">
                            Ingresa a tu dashboard para conocer el estado de la campaña y las actividades preparadas para tu marca.
                        </p>
                        <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:15px 34px;border:1px solid #f5a900;border-radius:10px;background-color:#ef6c22;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 8px 22px rgba(239,108,34,.25);">
                            Ver mi campaña
                        </a>
                    </div>

                    <div style="padding:18px 20px;border:1px solid #e0e5e7;border-radius:14px;background:#f7f8f8;">
                        <p style="margin:0;color:#5d6b72;font-size:14px;line-height:1.7;">
                            Si tienes alguna consulta, utiliza la mensajería de tu campaña para comunicarte directamente con nuestro equipo.
                        </p>
                    </div>

                    <div style="margin-top:28px;text-align:center;">
                        <p style="margin:0 0 6px;color:#718087;font-size:14px;">Atentamente,</p>
                        <p style="margin:0;color:#117e8c;font-size:15px;font-weight:800;">Equipo PRODOVI</p>
                    </div>
                </div>
            </div>
            <p style="margin:20px 0 0;color:#829097;font-size:12px;line-height:1.6;text-align:center;">
                Este mensaje confirma la activación de tu campaña en PRODOVI.
            </p>
        </div>
    </div>
</body>
</html>
