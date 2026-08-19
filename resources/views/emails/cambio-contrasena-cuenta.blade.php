<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cambio de contraseña</title></head>
<body style="margin:0;padding:0;background:#f1f4f4;font-family:Arial,Helvetica,sans-serif;color:#33444b;">
    <div style="padding:36px 16px;">
        <div style="max-width:680px;margin:auto;overflow:hidden;border:1px solid #d9e4e5;border-radius:18px;background:#fff;">
            <div style="padding:26px;background:#242426;text-align:center;">
                <img src="{{ $message->embed(public_path('imagenes/logoblanco.png')) }}" alt="PRODOVI" width="160" style="max-width:70%;height:auto;">
            </div>
            <div style="height:6px;background:#ee9f2b;"></div>
            <div style="padding:36px 32px;">
                <h1 style="margin:0 0 14px;color:#5b2b76;font-size:26px;text-align:center;">Confirma el cambio de contraseña</h1>
                <p style="margin:0 0 22px;line-height:1.7;">Hola <strong>{{ $user->name }}</strong>. Recibimos una solicitud para cambiar la contraseña de tu cuenta.</p>
                <p style="margin:0 0 26px;line-height:1.7;">Presiona el botón para abrir el formulario seguro donde podrás crear tu nueva contraseña.</p>
                <div style="text-align:center;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;border-radius:6px;background:#5b2b76;color:#fff;font-weight:700;text-decoration:none;">Crear nueva contraseña</a>
                </div>
                <div style="margin-top:26px;padding:16px;border-left:4px solid #ee9f2b;background:#fff8ed;font-size:13px;line-height:1.6;">
                    El enlace vence en 60 minutos y solo puede utilizarse una vez. Si no solicitaste este cambio, ignora este correo.
                </div>
                <p style="margin:24px 0 8px;color:#607078;font-size:12px;">Si el botón no funciona, copia este enlace:</p>
                <a href="{{ $resetUrl }}" style="color:#117e8c;font-size:12px;word-break:break-all;">{{ $resetUrl }}</a>
            </div>
        </div>
    </div>
</body>
</html>
