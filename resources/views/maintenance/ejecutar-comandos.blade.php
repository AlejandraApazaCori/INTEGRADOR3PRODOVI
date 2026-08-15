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
        @media (max-width: 640px) { .commands, .mail-config { grid-template-columns: 1fr; } .command-card p { min-height: 0; } }
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

            <p class="warning">Por seguridad, no compartas la dirección de esta página. La prueba usa únicamente el remitente configurado y nunca muestra la contraseña.</p>
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
