<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminación de Datos | PRODOVI</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/iconoweb.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@300;400;700&family=Varela+Round&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: "Varela Round", sans-serif;
            color: #e2e8f0;
            background: linear-gradient(180deg, rgba(8, 15, 35, 0.94), rgba(13, 27, 62, 0.96)), url('{{ asset('imagenes/herofondo.png') }}') center/cover fixed;
        }
        .legal-shell {
            min-height: 100vh;
            padding: 130px 20px 80px;
        }
        .legal-wrap {
            max-width: 980px;
            margin: 0 auto;
        }
        .legal-hero {
            margin-bottom: 28px;
        }
        .legal-kicker {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.16);
            color: #86efac;
            font-size: 0.9rem;
            letter-spacing: 0.04em;
        }
        .legal-hero h1 {
            font-family: "Rowdies", sans-serif;
            font-weight: 300;
            font-size: clamp(2.3rem, 5vw, 4.1rem);
            margin: 18px 0 12px;
            color: #ffffff;
        }
        .legal-hero p {
            max-width: 720px;
            line-height: 1.8;
            color: rgba(226, 232, 240, 0.82);
            margin: 0;
        }
        .legal-card {
            background: rgba(15, 23, 42, 0.76);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 28px;
            padding: 34px;
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
        }
        .legal-card h2 {
            font-family: "Rowdies", sans-serif;
            font-weight: 300;
            color: #ffffff;
            margin: 30px 0 12px;
            font-size: 1.35rem;
        }
        .legal-card h2:first-of-type {
            margin-top: 10px;
        }
        .legal-card p,
        .legal-card li {
            color: rgba(226, 232, 240, 0.88);
            line-height: 1.85;
            font-size: 1rem;
        }
        .legal-card ul {
            margin: 10px 0 18px;
            padding-left: 22px;
        }
        .legal-card a {
            color: #86efac;
            text-decoration: none;
        }
        .legal-card a:hover {
            text-decoration: underline;
        }
        .legal-meta {
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        @media (max-width: 768px) {
            .legal-shell {
                padding-top: 110px;
            }
            .legal-card {
                padding: 24px;
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    @include('componentes.navbar')

    <main class="legal-shell">
        <div class="legal-wrap">
            <section class="legal-hero">
                <span class="legal-kicker">PRODOVI Legal</span>
                <h1>Eliminación de Datos</h1>
                <p>Aquí explicamos cómo un usuario puede solicitar la eliminación de su información personal y qué datos pueden ser eliminados o anonimizados.</p>
            </section>

            <section class="legal-card">
                <p class="legal-meta"><strong>Eliminación de Datos de Usuario de PRODOVI</strong></p>
                <p class="legal-meta"><strong>Última actualización:</strong> 23 de junio de 2026</p>

                <p>Los usuarios pueden solicitar la eliminación de los datos asociados a su cuenta en cualquier momento.</p>

                <h2>Cómo solicitar la eliminación</h2>
                <p>Enviar un correo electrónico a:</p>
                <p><a href="mailto:alejandraapazacori@gmail.com">alejandraapazacori@gmail.com</a></p>

                <p>Incluyendo:</p>
                <ul>
                    <li>Nombre completo.</li>
                    <li>Correo electrónico asociado a la cuenta.</li>
                    <li>Solicitud expresa de eliminación.</li>
                </ul>

                <h2>Tiempo de respuesta</h2>
                <p>Las solicitudes serán procesadas normalmente dentro de los 30 días posteriores a su recepción.</p>

                <h2>Información eliminada</h2>
                <p>Dependiendo de las obligaciones legales aplicables, podremos eliminar o anonimizar:</p>
                <ul>
                    <li>Datos de perfil.</li>
                    <li>Información de autenticación.</li>
                    <li>Información generada dentro de la plataforma.</li>
                    <li>Datos asociados a la cuenta del usuario.</li>
                </ul>
            </section>
        </div>
    </main>

    @include('componentes.footer')
</body>
</html>
