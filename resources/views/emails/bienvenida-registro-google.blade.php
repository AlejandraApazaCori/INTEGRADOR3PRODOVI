<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light">
    <title>Bienvenido a PRODOVI</title>
    <style>
        :root { color-scheme: light only; supported-color-schemes: light; }
        body, .email-background { background-color: #f1f4f4 !important; }
        .brand-header { background-color: #117e8c !important; }
        .brand-button { background-color: #ef6c22 !important; }
    </style>
</head>
<body bgcolor="#f1f4f4" style="margin:0;padding:0;background-color:#f1f4f4;font-family:Arial,Helvetica,sans-serif;color:#273238;">
    <div class="email-background" style="width:100%;padding:36px 16px;box-sizing:border-box;background-color:#f1f4f4;">
        <div style="max-width:720px;margin:0 auto;">
            <div style="overflow:hidden;border:1px solid #d9e4e5;border-radius:22px;background:#ffffff;box-shadow:0 20px 45px rgba(17,126,140,.14);">
                <div class="brand-header" bgcolor="#117e8c" style="padding:28px 20px;background-color:#117e8c;text-align:center;">
                    <img
                        src="{{ $message->embed(public_path('imagenes/logoblanco.png')) }}"
                        alt="PRODOVI"
                        width="170"
                        style="display:block;width:170px;max-width:70%;height:auto;margin:0 auto;"
                    >
                </div>

                <div style="height:6px;background-color:#ef6c22;background-image:linear-gradient(90deg,#5b2b76 0 20%,#ef6c22 20% 40%,#f5a900 40% 60%,#7da533 60% 80%,#117e8c 80% 100%);"></div>

                <div style="padding:38px 34px 34px;">
                    <h1 style="margin:0 0 14px;color:#117e8c;font-size:28px;line-height:1.25;text-align:center;font-weight:800;">
                        ¡Bienvenido a PRODOVI!
                    </h1>

                    <p style="max-width:570px;margin:0 auto 28px;color:#607078;font-size:15px;line-height:1.7;text-align:center;">
                        Tu cuenta fue creada correctamente con Google. Nos alegra darte la bienvenida a una comunidad donde las ideas se convierten en estrategias que conectan.
                    </p>

                    <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#33444b;">
                        Hola <strong style="color:#5b2b76;">{{ $user->name ?: 'bienvenido' }}</strong>:
                    </p>

                    <div style="margin:0 0 24px;padding:20px;border:1px solid #cce2e5;border-left:5px solid #117e8c;border-radius:14px;background:#f5fbfc;">
                        <p style="margin:0 0 12px;font-size:15px;line-height:1.7;color:#33444b;">
                            Ya puedes ingresar a tu espacio en PRODOVI, conocer nuestros planes y comenzar a construir la presencia digital que tu proyecto necesita.
                        </p>
                        <p style="margin:0;font-size:15px;line-height:1.7;color:#33444b;">
                            <strong style="color:#ef6c22;">Tu próxima gran idea empieza aquí.</strong> Nosotros te ayudaremos a convertirla en resultados reales.
                        </p>
                    </div>

                    <div style="margin:0 0 26px;padding:19px 20px;border:1px solid #eadfc2;border-radius:14px;background:#fffaf0;">
                        <p style="margin:0 0 13px;color:#5b2b76;font-size:14px;font-weight:800;">Acceso a tu cuenta</p>
                        <p style="margin:0 0 9px;color:#4b5b62;font-size:14px;line-height:1.6;">
                            <strong style="display:inline-block;min-width:90px;color:#117e8c;">Correo:</strong>
                            {{ $user->email }}
                        </p>
                        <p style="margin:0;color:#4b5b62;font-size:14px;line-height:1.6;">
                            <strong style="display:inline-block;min-width:90px;color:#117e8c;">Método:</strong>
                            <span style="display:inline-block;padding:4px 10px;border-radius:10px;background:#efe7f3;color:#5b2b76;font-size:13px;font-weight:700;">Google</span>
                        </p>
                    </div>

                    <div style="margin:0 0 28px;text-align:center;">
                        <p style="max-width:540px;margin:0 auto 18px;color:#33444b;font-size:15px;line-height:1.7;">
                            <strong style="color:#5b2b76;">Da el siguiente paso para hacer crecer tu marca.</strong><br>
                            Descubre nuestros planes, elige el que mejor se adapte a tus objetivos y comienza a trabajar junto a la agencia PRODOVI.
                        </p>
                        <a
                            href="{{ $dashboardUrl }}"
                            class="brand-button"
                            style="display:inline-block;padding:15px 34px;border:1px solid #f5a900;border-radius:10px;background-color:#ef6c22;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 8px 22px rgba(239,108,34,.25);"
                        >
                            Ver planes
                        </a>
                    </div>

                    <div style="padding:18px 20px;border:1px solid #e0e5e7;border-radius:14px;background:#f7f8f8;">
                        <p style="margin:0;color:#5d6b72;font-size:14px;line-height:1.7;">
                            Si no reconoces este registro, responde a este correo para que nuestro equipo pueda ayudarte.
                        </p>
                    </div>

                    <div style="margin-top:28px;text-align:center;">
                        <p style="margin:0 0 6px;color:#718087;font-size:14px;">Atentamente,</p>
                        <p style="margin:0;color:#117e8c;font-size:15px;font-weight:800;">Equipo PRODOVI</p>
                    </div>
                </div>
            </div>

            <p style="margin:20px 0 0;color:#829097;font-size:12px;line-height:1.6;text-align:center;">
                Este mensaje confirma la creación de tu cuenta mediante Google en PRODOVI.
            </p>
        </div>
    </div>
</body>
</html>
