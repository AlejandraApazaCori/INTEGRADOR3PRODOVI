<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad | PRODOVI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('imagenes/favicon-prodovi.svg') }}?v=3">
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
            background: rgba(59, 130, 246, 0.16);
            color: #93c5fd;
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
            color: #93c5fd;
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
                <h1>Política de Privacidad</h1>
                <p>Conoce cómo recopilamos, utilizamos y protegemos la información de los usuarios que interactúan con la plataforma PRODOVI.</p>
            </section>

            <section class="legal-card">
                <p class="legal-meta"><strong>Política de Privacidad de PRODOVI</strong></p>
                <p class="legal-meta"><strong>Última actualización:</strong> 23 de junio de 2026</p>

                <h2>1. Introducción</h2>
                <p>PRODOVI es una plataforma digital diseñada para ayudar a empresas, emprendedores y profesionales a planificar, organizar y optimizar sus estrategias de marketing digital.</p>
                <p>La presente Política de Privacidad describe cómo recopilamos, utilizamos y protegemos la información de nuestros usuarios.</p>

                <h2>2. Información que recopilamos</h2>
                <p><strong>Información de registro</strong></p>
                <p>Podemos recopilar:</p>
                <ul>
                    <li>Nombre completo.</li>
                    <li>Dirección de correo electrónico.</li>
                    <li>Imagen de perfil.</li>
                    <li>Información de autenticación.</li>
                </ul>

                <p><strong>Inicio de sesión con Facebook</strong></p>
                <p>Cuando un usuario inicia sesión mediante Facebook, podemos recibir:</p>
                <ul>
                    <li>Nombre público.</li>
                    <li>Correo electrónico.</li>
                    <li>Imagen de perfil pública.</li>
                    <li>Identificador único asociado a la cuenta.</li>
                </ul>

                <p><strong>Inicio de sesión con Google</strong></p>
                <p>Cuando un usuario inicia sesión mediante Google, podemos recibir:</p>
                <ul>
                    <li>Nombre.</li>
                    <li>Correo electrónico.</li>
                    <li>Imagen de perfil.</li>
                    <li>Identificador único asociado a la cuenta.</li>
                </ul>

                <p><strong>Información técnica</strong></p>
                <p>También podemos recopilar:</p>
                <ul>
                    <li>Dirección IP.</li>
                    <li>Tipo de navegador.</li>
                    <li>Sistema operativo.</li>
                    <li>Información de uso de la plataforma.</li>
                </ul>

                <h2>3. Uso de la información</h2>
                <p>Utilizamos la información para:</p>
                <ul>
                    <li>Crear y administrar cuentas de usuario.</li>
                    <li>Permitir el acceso seguro a la plataforma.</li>
                    <li>Personalizar la experiencia del usuario.</li>
                    <li>Mejorar nuestros servicios.</li>
                    <li>Brindar soporte técnico.</li>
                    <li>Mantener la seguridad de la plataforma.</li>
                </ul>

                <h2>4. Compartición de información</h2>
                <p>PRODOVI no vende ni comercializa información personal.</p>
                <p>La información podrá compartirse únicamente con proveedores tecnológicos necesarios para el funcionamiento de la plataforma.</p>

                <h2>5. Seguridad</h2>
                <p>Aplicamos medidas razonables de seguridad para proteger la información de accesos no autorizados, alteraciones o pérdidas.</p>

                <h2>6. Derechos del usuario</h2>
                <p>Los usuarios pueden solicitar:</p>
                <ul>
                    <li>Acceso a sus datos.</li>
                    <li>Corrección de información.</li>
                    <li>Eliminación de datos.</li>
                    <li>Eliminación de su cuenta.</li>
                </ul>

                <h2>7. Eliminación de datos</h2>
                <p>Las solicitudes de eliminación pueden realizarse siguiendo las instrucciones publicadas en:</p>
                <p><a href="{{ route('legal.data-deletion') }}">https://prodovidigital.com/data-deletion</a></p>

                <h2>8. Contacto</h2>
                <p>Correo electrónico:</p>
                <p><a href="mailto:alejandraapazacori@gmail.com">alejandraapazacori@gmail.com</a></p>
            </section>
        </div>
    </main>

    @include('componentes.footer')
</body>
</html>
