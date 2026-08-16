<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark only">
    <title>Verifica tu correo | PRODOVI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('a.css.login.login')
    <style>
        .verification-content { text-align:center; }
        .verification-content .auth-topbar { margin-bottom:clamp(28px,6vh,58px); }
        .verification-icon {
            width:74px; height:74px; margin:0 auto 22px; display:grid; place-items:center;
            border:1px solid rgba(245,169,0,.65); border-radius:18px; color:#fff;
            background:linear-gradient(145deg,#117e8c,#0d6672); box-shadow:0 14px 34px rgba(17,126,140,.32);
        }
        .verification-icon i { font-size:1.85rem; }
        .verification-title { margin:0 0 12px; color:#fff; font:700 clamp(1.75rem,3vw,2.35rem)/1.15 'Poppins',sans-serif; letter-spacing:-.035em; }
        .verification-description { margin:0 auto 22px; max-width:430px; color:#b5b5ba; font-size:.9rem; line-height:1.7; }
        .verification-email {
            margin:0 auto 18px; padding:14px 16px; border:1px solid #48484d; border-radius:10px;
            background:#101010; color:#fff; font-size:.88rem; font-weight:700; overflow-wrap:anywhere;
        }
        .verification-hint { margin:0 0 24px; color:#8f8f95; font-size:.76rem; line-height:1.6; }
        .verification-alert { margin:0 0 18px; padding:12px 14px; border-radius:10px; font-size:.8rem; line-height:1.5; }
        .verification-alert.success { border:1px solid rgba(125,165,51,.55); background:rgba(125,165,51,.12); color:#cce3a0; }
        .verification-alert.error { border:1px solid rgba(239,108,34,.55); background:rgba(239,108,34,.1); color:#ffc39e; }
        .resend-button { margin-bottom:13px; }
        .verification-login-link { display:inline-block; color:#b8b8bd; font-size:.78rem; text-decoration:none; }
        .verification-login-link:hover { color:#f5a900; }
        @media(max-width:760px) {
            .verification-content .auth-topbar { margin-bottom:25px; }
            .verification-icon { width:62px; height:62px; margin-bottom:17px; }
            .verification-title { font-size:1.65rem; }
            .verification-description { margin-bottom:16px; }
            .verification-email { margin-bottom:14px; }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <aside class="auth-art" aria-hidden="true">
                <div class="mosaic-headline">
                    <span class="mosaic-kicker">SOLO FALTA UN PASO</span>
                    <div class="rotating-messages">
                        <span>Confirma tu correo y comienza a crear.</span>
                        <span>Tu próxima gran idea está por empezar.</span>
                        <span>Activa tu cuenta y descubre nuestros planes.</span>
                    </div>
                </div>
                @include('clientes.verificacion.partials.mosaico')
            </aside>

            <section class="auth-content">
                <div class="auth-content-inner verification-content">
                    <div class="auth-topbar">
                        <a href="{{ url('/') }}" class="auth-logo" aria-label="Ir al inicio">
                            <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI">
                        </a>
                        <a href="{{ route('login') }}#register" class="back-button">
                            <i class="fas fa-chevron-left"></i>
                            <span>Volver al registro</span>
                        </a>
                    </div>

                    <div class="verification-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <h1 class="verification-title">Revisa tu correo</h1>
                    <p class="verification-description">Enviamos un enlace de verificación a:</p>
                    <div class="verification-email">{{ $email }}</div>
                    <p class="verification-hint">Abre el mensaje y confirma tu dirección. Tu cuenta todavía no ha sido creada y el enlace vence en 60 minutos.</p>

                    @if(session('success'))
                        <div class="verification-alert success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="verification-alert error">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('registro.verificacion.reenviar') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary resend-button">Reenviar correo de verificación</button>
                    </form>

                    <a href="{{ route('login') }}" class="verification-login-link">Usar otra dirección de correo</a>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
