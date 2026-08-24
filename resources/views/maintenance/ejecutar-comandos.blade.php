<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="color-scheme" content="light">
    <title>Mantenimiento PRODOVI</title>
    <style>
        :root { color-scheme: only light; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #0d1113;
            color: #edf7f8;
            font-family: Arial, Helvetica, sans-serif;
        }
        .panel {
            width: min(100%, 760px);
            overflow: hidden;
            border: 1px solid rgba(17,126,140,.7);
            border-radius: 14px;
            background: #161c1f;
            box-shadow: 0 24px 60px rgba(0,0,0,.45);
        }
        .brand-strip {
            height: 7px;
            background: linear-gradient(90deg,#5b2b76 0 20%,#ef6c22 20% 40%,#f5a900 40% 60%,#7da533 60% 80%,#117e8c 80% 100%);
        }
        .content { padding: clamp(24px,5vw,42px); }
        h1 { margin: 0 0 10px; color: #5fc2ce; font-size: clamp(24px,5vw,34px); }
        .intro { margin: 0 0 28px; color: #b9c4c8; line-height: 1.65; }
        .status {
            margin-bottom: 24px;
            padding: 14px 16px;
            border: 1px solid rgba(125,165,51,.55);
            border-radius: 10px;
            background: rgba(125,165,51,.1);
            color: #cfe5a8;
        }
        .commands { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
        .command-card {
            padding: 20px;
            border: 1px solid #303b3f;
            border-radius: 12px;
            background: #101517;
        }
        .command-card h2 { margin: 0 0 8px; color: #f5a900; font-size: 18px; }
        .command-card p { min-height: 62px; margin: 0 0 18px; color: #aab6ba; line-height: 1.5; font-size: 14px; }
        button {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #f5a900;
            border-radius: 10px;
            background: #117e8c;
            color: white;
            cursor: pointer;
            font-weight: 700;
        }
        button:hover { background: #1594a4; }
        button:disabled { cursor: wait; opacity: .65; }
        .result {
            margin-top: 24px;
            padding: 18px;
            border: 1px solid #7da533;
            border-radius: 10px;
            background: rgba(125,165,51,.08);
        }
        .result.error { border-color: #ef6c22; background: rgba(239,108,34,.08); }
        .result h2 { margin: 0 0 8px; color: #f5a900; font-size: 17px; }
        .result-meta { margin: 0 0 12px; color: #aab6ba; font-size: 13px; }
        pre {
            max-height: 340px;
            margin: 0;
            overflow: auto;
            padding: 14px;
            border-radius: 8px;
            background: #090c0d;
            color: #dce8ea;
            font: 13px/1.55 Consolas, monospace;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .warning { margin: 24px 0 0; color: #efb07d; font-size: 13px; line-height: 1.55; }
        .mail-panel {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid #303b3f;
            border-radius: 12px;
            background: #101517;
        }
        .mail-panel h2 { margin: 0 0 8px; color: #5fc2ce; font-size: 19px; }
        .mail-panel > p { margin: 0 0 16px; color: #aab6ba; line-height: 1.5; font-size: 14px; }
        .mail-config {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 8px 18px;
            margin: 0 0 18px;
            color: #cbd6d9;
            font-size: 13px;
        }
        .mail-config span { overflow-wrap: anywhere; }
        .mail-config strong { color: #f5a900; }
        .mail-result { margin-top: 16px; padding: 14px; border: 1px solid #7da533; border-radius: 10px; color: #dce8ea; }
        .mail-result.error { border-color: #ef6c22; }
        .mail-result p { margin: 0; line-height: 1.55; }
        .mail-result pre { margin-top: 10px; }
        .danger-zone { margin-top: 28px; padding: 22px; border: 1px solid #dc2626; border-radius: 12px; background: rgba(127,29,29,.12); }
        .danger-zone h2 { margin: 0 0 8px; color: #fca5a5; font-size: 20px; }
        .danger-intro { margin: 0 0 18px; color: #fecaca; font-size: 13px; line-height: 1.6; }
        .danger-steps { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 14px; }
        .danger-step { padding: 17px; border: 1px solid #4b2528; border-radius: 10px; background: #100d0e; }
        .danger-step h3 { margin: 0 0 8px; color: #fff; font-size: 16px; }
        .danger-step p { min-height: 64px; margin: 0 0 14px; color: #b9aaad; font-size: 13px; line-height: 1.5; }
        .step-number { display: inline-grid; place-items: center; width: 24px; height: 24px; margin-right: 7px; border-radius: 6px; background: #5b2b76; color: #fff; font-size: 12px; }
        .confirmation-input { width: 100%; margin-bottom: 10px; padding: 11px 12px; border: 1px solid #713239; border-radius: 8px; outline: 0; background: #080808; color: #fff; }
        .confirmation-input:focus { border-color: #ef6c22; box-shadow: 0 0 0 3px rgba(239,108,34,.13); }
        button.danger-button { border-color: #fca5a5; background: #b91c1c; }
        button.danger-button:hover { background: #dc2626; }
        button.seed-button { border-color: #7da533; background: #587722; }
        button.seed-button:hover { background: #6e922d; }
        .format-state { margin: 14px 0 0; color: {{ $formatPending ? '#cfe5a8' : '#b9aaad' }}; font-size: 12px; line-height: 1.5; }
        .credentials { margin-top: 18px; padding: 18px; border: 1px solid #7da533; border-radius: 10px; background: rgba(125,165,51,.1); }
        .credentials h3 { margin: 0 0 12px; color: #cfe5a8; }
        .credential-row { display: grid; grid-template-columns: 95px 1fr; gap: 10px; margin-top: 8px; color: #eef7df; font: 14px/1.5 Consolas,monospace; overflow-wrap: anywhere; }
        .credential-row strong { color: #f5a900; }
        .credentials a { display: inline-block; margin-top: 16px; color: #5fc2ce; font-weight: 700; }
        @media (max-width: 640px) { .commands, .mail-config, .danger-steps { grid-template-columns: 1fr; } .command-card p, .danger-step p { min-height: 0; } }
    </style>
</head>
<body>
    <main class="panel">
        <div class="brand-strip"></div>
        <div class="content">
            <h1>Mantenimiento PRODOVI</h1>
            <p class="intro">Ejecuta únicamente las operaciones necesarias para actualizar la base de datos y publicar el enlace de almacenamiento.</p>

            <div class="status">
                Estado de <code>public/storage</code>:
                <strong>{{ $storageLinkExists ? 'ya existe' : 'todavía no existe' }}</strong>
            </div>

            <section class="commands">
                <article class="command-card">
                    <h2>Instalar dependencias</h2>
                    <p>Instala desde <code>composer.lock</code> las librerías de producción, incluida la API de Google Drive, y limpia la caché de Laravel.</p>
                    <form method="POST" action="{{ route('mantenimiento.web.execute', 'composer-install') }}" data-command-form>
                        @csrf
                        <button type="submit">Ejecutar composer install</button>
                    </form>
                </article>

                <article class="command-card">
                    <h2>Ejecutar migraciones</h2>
                    <p>Aplica todas las migraciones pendientes con la opción segura para producción.</p>
                    <form method="POST" action="{{ route('mantenimiento.web.execute', 'migrate') }}" data-command-form>
                        @csrf
                        <button type="submit">php artisan migrate</button>
                    </form>
                </article>

                <article class="command-card">
                    <h2>Crear enlace de storage</h2>
                    <p>Crea el enlace público <code>public/storage</code> hacia el almacenamiento de Laravel.</p>
                    <form method="POST" action="{{ route('mantenimiento.web.execute', 'storage-link') }}" data-command-form>
                        @csrf
                        <button type="submit">php artisan storage:link</button>
                    </form>
                </article>
            </section>

            <section class="mail-panel">
                <h2>Diagnóstico de correo SMTP</h2>
                <p>Envía una prueba solamente al correo remitente configurado. La contraseña nunca se muestra.</p>

                <div class="mail-config">
                    <span><strong>Mailer:</strong> {{ $mailConfiguration['mailer'] }}</span>
                    <span><strong>Servidor:</strong> {{ $mailConfiguration['host'] }}:{{ $mailConfiguration['port'] }}</span>
                    <span><strong>Seguridad:</strong> {{ $mailConfiguration['scheme'] }}</span>
                    <span><strong>Remitente:</strong> {{ $mailConfiguration['from'] }}</span>
                    <span><strong>Usuario:</strong> {{ $mailConfiguration['usernameConfigured'] ? 'configurado' : 'falta configurar' }}</span>
                    <span><strong>Contraseña:</strong> {{ $mailConfiguration['passwordConfigured'] ? 'configurada' : 'falta configurar' }}</span>
                    <span><strong>Caché de configuración:</strong> {{ $mailConfiguration['configurationCached'] ? 'activa' : 'inactiva' }}</span>
                </div>

                <form method="POST" action="{{ route('mantenimiento.web.mail-test') }}" data-command-form>
                    @csrf
                    <button type="submit">Probar envío SMTP</button>
                </form>

                @if (session('mail_test_result'))
                    @php($mailResult = session('mail_test_result'))
                    <div class="mail-result {{ $mailResult['success'] ? '' : 'error' }}" aria-live="polite">
                        <p><strong>{{ $mailResult['success'] ? 'Prueba correcta.' : 'No se pudo enviar.' }}</strong> {{ $mailResult['message'] }}</p>
                        @isset($mailResult['technical'])
                            <pre>{{ $mailResult['technical'] }}</pre>
                        @endisset
                    </div>
                @endif
            </section>

            <section class="danger-zone">
                <h2>Formateo completo de la página</h2>
                <p class="danger-intro"><strong>Advertencia:</strong> esta operación elimina definitivamente todos los datos, archivos registrados, usuarios y configuraciones guardadas en la base de datos. No se puede deshacer.</p>

                <div class="danger-steps">
                    <article class="danger-step">
                        <h3><span class="step-number">1</span>Formatear página</h3>
                        <p>Ejecuta <code>migrate:fresh</code>, recrea todas las tablas vacías y reinicia sus IDs desde 1.</p>
                        <form method="POST" action="{{ route('mantenimiento.web.format') }}" data-command-form onsubmit="return confirm('Se eliminarán TODOS los datos de PRODOVI. ¿Deseas continuar?');">
                            @csrf
                            <input class="confirmation-input" type="text" name="confirmation" placeholder="Escribe: {{ $formatConfirmation }}" autocomplete="off" required>
                            <button type="submit" class="danger-button">Formatear página</button>
                        </form>
                    </article>

                    <article class="danger-step">
                        <h3><span class="step-number">2</span>Crear datos iniciales</h3>
                        <p>Ejecuta los seeders de roles, permisos, planes, cuestionarios y crea el primer administrador.</p>
                        <form method="POST" action="{{ route('mantenimiento.web.seed-initial-admin') }}" data-command-form>
                            @csrf
                            <button type="submit" class="seed-button" {{ $formatPending ? '' : 'disabled' }}>Ejecutar seeder inicial</button>
                        </form>
                        <p class="format-state">{{ $formatPending ? 'Paso 1 completado. Ya puedes ejecutar el seeder.' : 'El paso 2 se habilitará después del formateo.' }}</p>
                    </article>
                </div>

                @if(session('format_result'))
                    @php($formatResult = session('format_result'))
                    <div class="result {{ $formatResult['success'] ? '' : 'error' }}" aria-live="polite">
                        <h2>{{ $formatResult['success'] ? 'Formateo completado' : 'El formateo falló' }}</h2>
                        <p>{{ $formatResult['message'] }}</p>
                        @if(!empty($formatResult['output']))<pre>{{ $formatResult['output'] }}</pre>@endif
                    </div>
                @endif

                @if(session('seed_result'))
                    @php($seedResult = session('seed_result'))
                    <div class="result {{ $seedResult['success'] ? '' : 'error' }}" aria-live="polite">
                        <h2>{{ $seedResult['success'] ? 'Datos iniciales creados' : 'El seeder falló' }}</h2>
                        <p>{{ $seedResult['message'] }}</p>
                        @if(!empty($seedResult['output']))<pre>{{ $seedResult['output'] }}</pre>@endif
                    </div>
                @endif

                @if(session('initial_admin_credentials'))
                    @php($credentials = session('initial_admin_credentials'))
                    <div class="credentials" aria-live="polite">
                        <h3>Credenciales del administrador inicial</h3>
                        <div class="credential-row"><strong>Correo:</strong><span>{{ $credentials['email'] }}</span></div>
                        <div class="credential-row"><strong>Contraseña:</strong><span>{{ $credentials['password'] }}</span></div>
                        <a href="{{ route('login') }}">Ir al inicio de sesión</a>
                    </div>
                @endif
            </section>

            @if (session('maintenance_result'))
                @php($result = session('maintenance_result'))
                <section class="result {{ $result['success'] ? '' : 'error' }}" aria-live="polite">
                    <h2>{{ $result['success'] ? 'Comando completado' : 'El comando no se completó' }}</h2>
                    <p class="result-meta">
                        {{ $result['command'] }} · {{ $result['executed_at'] }}
                        @isset($result['exit_code']) · código {{ $result['exit_code'] }} @endisset
                    </p>
                    <pre>{{ $result['output'] }}</pre>
                </section>
            @endif

            <p class="warning">Por seguridad, no compartas la dirección de esta página. Después de crear el administrador inicial, guarda las credenciales y cambia la contraseña desde la plataforma.</p>
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-command-form]').forEach(form => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('button');
                button.disabled = true;
                button.textContent = 'Ejecutando...';
            });
        });
    </script>
</body>
</html>
