<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cuenta creada en PRODOVI</title></head>
<body style="margin:0;padding:0;background:#f1f4f4;font-family:Arial,Helvetica,sans-serif;color:#33444b;">
    <div style="padding:36px 16px;">
        <div style="max-width:680px;margin:auto;overflow:hidden;border:1px solid #d9e4e5;border-radius:18px;background:#fff;">
            <div style="padding:26px;background:#242426;text-align:center;">
                <img src="{{ $message->embed(public_path('imagenes/logoblanco.png')) }}" alt="PRODOVI" width="160" style="max-width:70%;height:auto;">
            </div>
            <div style="height:6px;background:#ee9f2b;"></div>
            <div style="padding:36px 32px;">
                <h1 style="margin:0 0 14px;color:#5b2b76;font-size:26px;text-align:center;">Tu cuenta fue creada exitosamente</h1>
                <p style="margin:0 0 22px;line-height:1.7;">Hola <strong>{{ $user->name }}</strong>. Ya tienes una cuenta en PRODOVI.</p>
                <p style="margin:0 0 26px;line-height:1.7;">Adjuntamos un archivo TXT con tu correo y contraseña para que puedas acceder.</p>
                <div style="text-align:center;">
                    <a href="{{ route('login') }}" style="display:inline-block;padding:14px 28px;border-radius:6px;background:#5b2b76;color:#fff;font-weight:700;text-decoration:none;">Iniciar sesión</a>
                </div>
                <div style="margin-top:26px;padding:16px;border-left:4px solid #ee9f2b;background:#fff8ed;font-size:13px;line-height:1.6;">
                    Por seguridad, cambia tu contraseña después de iniciar sesión y guarda el archivo adjunto en un lugar seguro.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
