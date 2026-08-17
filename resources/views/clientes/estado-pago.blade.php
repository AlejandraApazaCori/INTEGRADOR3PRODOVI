<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago pendiente | PRODOVI Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600&family=Varela+Round&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --purple: #5b2b76;
            --orange: #ef6c22;
            --turquoise: #117e8c;
            --green: #7da533;
            --gold: #f5a900;
            --surface: #0d0d0f;
            --surface-soft: #151417;
            --border: rgba(255,255,255,.09);
            --text: #f7f5f8;
            --muted: #a49da7;
        }

        * { box-sizing: border-box; }
        html { color-scheme: dark; }
        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            background: #000;
            color: var(--text);
            font-family: 'Varela Round', sans-serif;
        }

        a, button { font: inherit; }
        .status-page {
            width: min(1080px, calc(100% - 40px));
            min-height: 100vh;
            margin-inline: auto;
            padding: 118px 0 70px;
        }

        .status-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 26px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--border);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 13px;
            color: #70c6ce;
            font-size: .72rem;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
        }

        .status-heading h1 {
            margin: 0;
            color: #fff;
            font-family: 'Rowdies', sans-serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 600;
            line-height: 1.05;
        }

        .status-heading p { max-width: 560px; margin: 10px 0 0; color: var(--muted); }
        .status-badge {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border: 1px solid rgba(245,169,0,.3);
            border-radius: 7px;
            background: rgba(245,169,0,.09);
            color: #ffd27b;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
            gap: 20px;
            align-items: start;
        }

        .payment-panel,
        .instructions-panel {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .payment-panel { padding: 28px; border-top: 4px solid var(--orange); }
        .instructions-panel { padding: 24px; border-top: 4px solid var(--turquoise); }
        .panel-label {
            margin: 0 0 18px;
            color: #8b848e;
            font-size: .68rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .plan-name { margin: 0 0 22px; color: #fff; font-family: 'Rowdies', sans-serif; font-size: 1.55rem; font-weight: 400; }
        .payment-data { margin: 0; }
        .payment-row {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: 18px;
            padding: 13px 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .payment-row dt { color: var(--muted); font-size: .8rem; }
        .payment-row dd { margin: 0; color: #fff; font-size: .83rem; font-weight: 700; text-align: right; }
        .payment-code-block { margin-top: 24px; text-align: center; }
        .payment-code-block span { display: block; margin-bottom: 9px; color: var(--muted); font-size: .75rem; }
        .code-display {
            padding: 18px;
            border: 2px dashed var(--orange);
            border-radius: 7px;
            background: #080809;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: clamp(1.6rem, 6vw, 2.15rem);
            font-weight: 800;
            letter-spacing: 0;
            overflow-wrap: anywhere;
        }

        .instructions-panel h2 { margin: 0 0 20px; color: #fff; font-family: 'Rowdies', sans-serif; font-size: 1.15rem; font-weight: 400; }
        .step-list { display: grid; gap: 16px; margin: 0; padding: 0; list-style: none; counter-reset: payment-step; }
        .step-list li {
            position: relative;
            min-height: 34px;
            padding-left: 44px;
            color: #c8c2ca;
            font-size: .8rem;
            line-height: 1.55;
            counter-increment: payment-step;
        }

        .step-list li::before {
            content: counter(payment-step);
            position: absolute;
            top: 0;
            left: 0;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 7px;
            background: rgba(17,126,140,.18);
            color: #78ccd4;
            font-size: .72rem;
            font-weight: 800;
        }

        .office-links { display: grid; gap: 8px; margin-top: 22px; }
        .office-links a {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--surface-soft);
            color: #e8e3ea;
            font-size: .74rem;
            line-height: 1.4;
            text-decoration: none;
        }

        .office-links i { width: 18px; flex: 0 0 18px; color: #78ccd4; text-align: center; }
        .office-links a:last-child i { color: #65d27b; }
        .status-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .action-button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 7px;
            color: #fff;
            font-size: .76rem;
            font-weight: 800;
            text-decoration: none;
        }

        .action-button.primary { border-color: var(--orange); background: var(--orange); }
        .action-button.secondary { background: var(--surface-soft); }

        @media (max-width: 780px) {
            .status-page { width: min(100% - 28px, 1080px); padding-top: 94px; }
            .status-heading { align-items: flex-start; flex-direction: column; }
            .status-layout { grid-template-columns: 1fr; }
            .payment-panel, .instructions-panel { padding: 21px; }
        }

        @media (max-width: 440px) {
            .payment-row { grid-template-columns: 1fr; gap: 4px; }
            .payment-row dd { text-align: left; }
            .status-actions { flex-direction: column; }
            .action-button { width: 100%; }
        }
    </style>
</head>
<body>
    @include('componentes.navbar2')

    <main class="status-page">
        <header class="status-heading">
            <div>
                <a href="{{ route('clientes.home') }}" class="back-link"><i class="fas fa-arrow-left"></i> Volver a planes</a>
                <h1>Pago pendiente</h1>
                <p>Conserva tu código y preséntalo en la oficina. Tu plan se activará cuando confirmemos el pago.</p>
            </div>
            <span class="status-badge"><i class="fas fa-clock"></i> En espera</span>
        </header>

        <div class="status-layout">
            <section class="payment-panel" aria-labelledby="payment-summary-title">
                <p class="panel-label" id="payment-summary-title">Resumen de la solicitud</p>
                <h2 class="plan-name">{{ $pagoPendiente->plan->nombre }}</h2>
                <dl class="payment-data">
                    <div class="payment-row">
                        <dt>Monto</dt>
                        <dd>{{ number_format($pagoPendiente->monto, 2) }} {{ $pagoPendiente->moneda === 'BS' ? 'Bs' : $pagoPendiente->moneda }}</dd>
                    </div>
                    <div class="payment-row">
                        <dt>Método</dt>
                        <dd>Pago físico</dd>
                    </div>
                    <div class="payment-row">
                        <dt>Generado</dt>
                        <dd>{{ $pagoPendiente->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                @if($codigoPago)
                    <div class="payment-code-block">
                        <span>Tu último código de pago</span>
                        <div class="code-display">{{ $codigoPago->codigo }}</div>
                    </div>
                @endif

                <div class="status-actions">
                    @if($codigoPago)
                        <a href="{{ route('pago.fisico.codigo.pdf', $pagoPendiente) }}" class="action-button primary">
                            <i class="fas fa-file-pdf"></i> Descargar PDF
                        </a>
                    @endif
                    <a href="{{ route('clientes.home') }}" class="action-button secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </section>

            <aside class="instructions-panel" aria-labelledby="instructions-title">
                <h2 id="instructions-title">Cómo completar el pago</h2>
                <ol class="step-list">
                    <li>Descarga o guarda el código mostrado en esta página.</li>
                    <li>Preséntalo al personal de PRODOVI en nuestra oficina.</li>
                    <li>Después de confirmar el pago, tu suscripción se activará automáticamente.</li>
                </ol>

                <div class="office-links">
                    <a href="https://www.bing.com/maps/search?v=2&amp;pc=FACEBK&amp;mid=8100&amp;mkt=es-MX&amp;FORM=FBKPL1&amp;q=Real+Plaza+Hotel+%26+Convention+Center.+Av.+Arce+%232177+%28Frente+a+la+Plaza+Bolivia%29%2C+La+Paz%2C+Bolivia%2C+La+Paz%2C+Bolivia&amp;cp=-16.506655%7E-68.127258&amp;lvl=16&amp;style=r" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-location-dot"></i>
                        <span>Real Plaza Hotel, Av. Arce #2177, frente a Plaza Bolivia</span>
                    </a>
                    <a href="https://wa.me/59179561365" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp: +591 79561365</span>
                    </a>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
