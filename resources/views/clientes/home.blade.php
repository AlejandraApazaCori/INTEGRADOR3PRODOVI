<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Planes | PRODOVI Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600;700&family=Varela+Round&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #7130a7;
            --purple-dark: #431760;
            --orange: #f47b20;
            --green: #72bf44;
            --turquoise: #19b9b2;
            --ink: #17131d;
            --soft: #f7f4f8;
            color-scheme: light;
        }
        * { box-sizing: border-box; }
        html {
            scroll-behavior: smooth;
            overflow-x: hidden;
            background: #000;
            scrollbar-width: thin;
            scrollbar-color: #117e8c #111;
        }
        body { margin: 0; min-width: 320px; overflow-x: hidden; background: #000 !important; color: var(--ink); font-family: 'Varela Round', sans-serif; }
        ::-webkit-scrollbar { width: 11px; height: 0; }
        ::-webkit-scrollbar-track { background: #111; }
        ::-webkit-scrollbar-thumb { min-height: 45px; border: 2px solid #111; border-radius: 10px; background: #117e8c; }
        ::-webkit-scrollbar-thumb:hover { background: #ef6c22; }
        ::-webkit-scrollbar-corner { background: #111; }
        button, a { font: inherit; }
        .client-home { position: relative; padding-top: 88px; overflow: hidden; background: #000; }
        .client-home::before, .client-home::after { content: ''; position: absolute; z-index: -1; border-radius: 34px; transform: rotate(18deg); pointer-events: none; }
       
        .home-shell { width: min(1180px, calc(100% - 40px)); margin-inline: auto; }
        .experience-hero { display: grid; grid-template-columns: minmax(0,1.15fr) minmax(320px,.85fr); align-items: center; gap: 60px; min-height: 560px; padding: 72px 0 58px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 20px; color: var(--purple); font-size: .78rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        .eyebrow::before { content: ''; width: 34px; height: 4px; border-radius: 5px; background: var(--turquoise); }
        .hero-title { max-width: 720px; margin: 0; color: #fff; font-family: 'Rowdies', sans-serif; font-size: clamp(2.8rem,5.7vw,5.6rem); font-weight: 600; letter-spacing: -.055em; line-height: .97; }
        .hero-title span { color: var(--turquoise); }
        .hero-copy { max-width: 620px; margin: 25px 0 0; color: #c9c1cd; font-size: clamp(1rem,1.7vw,1.18rem); line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
        .primary-action, .secondary-action { display: inline-flex; min-height: 50px; align-items: center; justify-content: center; gap: 9px; padding: 12px 21px; border: 2px solid var(--orange); border-radius: 10px; font-weight: 700; text-decoration: none; transition: transform .25s, box-shadow .25s, background .25s; }
        .primary-action { background: var(--orange); color: #fff; box-shadow: 0 12px 24px rgba(244,123,32,.22); }
        .secondary-action { border-color: rgba(113,48,167,.18); background: #fff; color: var(--purple); }
        .primary-action:hover, .secondary-action:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(67,23,96,.15); }
        .hero-visual { position: relative; min-height: 410px; display: grid; place-items: center; }
        .strategy-board { position: relative; width: min(100%,420px); padding: 30px; border: 1px solid rgba(113,48,167,.12); border-radius: 28px; background: rgba(255,255,255,.94); box-shadow: 0 30px 70px rgba(67,23,96,.16); transform: rotate(-2deg); }
        .board-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .board-label { color: var(--purple-dark); font-weight: 700; }
        .board-live { color: var(--green); font-size: .8rem; font-weight: 700; }
        .metric-main { padding: 24px; border-radius: 20px; background: var(--purple); color: #fff; }
        .metric-main small { opacity: .76; }
        .metric-main strong { display: block; margin-top: 4px; font-size: 2.45rem; }
        .chart-bars { height: 90px; display: flex; align-items: end; gap: 9px; margin-top: 22px; }
        .chart-bars span { flex: 1; min-height: 18px; border-radius: 6px 6px 3px 3px; background: rgba(255,255,255,.32); animation: bars 3s ease-in-out infinite alternate; }
        .chart-bars span:nth-child(1) { height: 32%; } .chart-bars span:nth-child(2) { height: 48%; animation-delay: .2s; } .chart-bars span:nth-child(3) { height: 58%; animation-delay: .4s; }
        .chart-bars span:nth-child(4) { height: 80%; animation-delay: .6s; background: var(--orange); } .chart-bars span:nth-child(5) { height: 100%; animation-delay: .8s; background: var(--turquoise); }
        @keyframes bars { to { filter: brightness(1.15); transform: scaleY(.91); } }
        .floating-note { position: absolute; display: flex; align-items: center; gap: 10px; padding: 13px 16px; border-radius: 14px; background: #fff; color: var(--purple-dark); box-shadow: 0 15px 35px rgba(37,25,43,.14); font-size: .87rem; font-weight: 700; animation: float 3.2s ease-in-out infinite; }
        .floating-note::before { content: ''; width: 10px; height: 10px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 5px rgba(114,191,68,.14); }
        .note-one { top: 30px; right: -25px; } .note-two { bottom: 25px; left: -30px; animation-delay: .8s; }
        .note-two::before { background: var(--orange); box-shadow: 0 0 0 5px rgba(244,123,32,.14); }
        @keyframes float { 50% { transform: translateY(-9px); } }
        .quick-path { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin: 0 auto 70px; }
        .path-item { display: flex; align-items: center; gap: 14px; padding: 18px; border: 1px solid #ede8ef; border-radius: 16px; background: #fff; color: #5b5261; }
        .path-number { width: 38px; height: 38px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 10px; background: rgba(113,48,167,.1); color: var(--purple); font-family: 'Rowdies', sans-serif; }
        .path-item:nth-child(2) .path-number { background: rgba(244,123,32,.12); color: var(--orange); } .path-item:nth-child(3) .path-number { background: rgba(25,185,178,.12); color: #118983; }
        .path-item strong { display: block; color: var(--ink); } .path-item small { display: block; margin-top: 2px; }
        /* =========================================================
           MODAL / ONBOARDING PRODOVI — versión compacta
        ========================================================= */
        .tutorial-modal {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: grid;
            place-items: center;
            padding: 18px;
            background: rgba(4, 3, 7, .82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            opacity: 0;
            visibility: hidden;
            transition: opacity .28s ease, visibility .28s ease;
        }

        .tutorial-modal.is-open {
            opacity: 1;
            visibility: visible;
        }

        .tutorial-dialog {
            --tutorial-accent: var(--turquoise);
            position: relative;
            width: min(760px, calc(100vw - 32px));
            height: min(480px, calc(100vh - 32px));
            min-height: 420px;
            display: grid;
            grid-template-rows: auto auto 1fr auto;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 24px;
            background: #fff;
            color: #201925;
            box-shadow: 0 30px 90px rgba(0,0,0,.55);
            transform: translateY(14px) scale(.985);
            transition: transform .35s cubic-bezier(.22,1,.36,1);
        }

        .tutorial-modal.is-open .tutorial-dialog {
            transform: translateY(0) scale(1);
        }

        .tutorial-dialog::before {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            right: -110px;
            top: 40px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(25,185,178,.14), transparent 70%);
            pointer-events: none;
        }

        .tutorial-dialog::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            left: -95px;
            bottom: 40px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(113,48,167,.1), transparent 72%);
            pointer-events: none;
        }

        .tutorial-topbar {
            position: relative;
            z-index: 8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px 13px;
            border-bottom: 1px solid #eee8f0;
            background: rgba(255,255,255,.92);
        }

        .tutorial-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .tutorial-brand-mark {
            width: 30px;
            height: 30px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--purple), var(--turquoise));
            color: #fff;
            font-family: 'Rowdies', sans-serif;
            font-size: .74rem;
            box-shadow: 0 6px 16px rgba(113,48,167,.22);
        }

        .tutorial-brand-copy {
            min-width: 0;
        }

        .tutorial-brand-copy strong {
            display: block;
            color: #241829;
            font-size: .82rem;
        }

        .tutorial-brand-copy span {
            display: block;
            margin-top: 1px;
            color: #8a808f;
            font-size: .68rem;
        }

        .tutorial-top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tutorial-time {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #f5f1f7;
            color: #706476;
            font-size: .66rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .tutorial-time::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 3px rgba(114,191,68,.13);
        }

        .tutorial-close {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: 1px solid #e5dfe7;
            border-radius: 50%;
            background: #fff;
            color: #4a3f4f;
            cursor: pointer;
            font-size: 1.1rem;
            line-height: 1;
            transition: background .2s ease, color .2s ease, transform .2s ease, border-color .2s ease;
        }

        .tutorial-close:hover {
            border-color: var(--orange);
            background: var(--orange);
            color: #fff;
            transform: rotate(7deg);
        }

        .tutorial-progress {
            position: relative;
            z-index: 10;
            height: 3px;
            background: #f2edf4;
        }

        .tutorial-progress-bar {
            width: 25%;
            height: 100%;
            border-radius: 0 10px 10px 0;
            background: linear-gradient(90deg, var(--purple), var(--orange), var(--turquoise));
            transition: width .45s cubic-bezier(.22,1,.36,1);
        }

        .tutorial-slides {
            position: relative;
            z-index: 3;
            min-height: 0;
            overflow: hidden;
        }

        .tutorial-slide {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: minmax(0,1fr) minmax(200px,.85fr);
            align-items: center;
            gap: clamp(18px,3vw,36px);
            padding: 24px 26px 20px;
            opacity: 0;
            visibility: hidden;
            transform: translateX(30px);
            transition: opacity .4s ease, transform .45s cubic-bezier(.22,1,.36,1), visibility .4s ease;
            overflow-y: auto;
        }

        .tutorial-slide.is-active {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .tutorial-copy {
            position: relative;
            z-index: 2;
        }

        .slide-step {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #f3eef6;
            color: var(--purple);
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .slide-step::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 0 3px rgba(113,48,167,.1);
        }

        .tutorial-slide[data-slide="1"] .slide-step {
            background: #fff2e8;
            color: var(--orange);
        }

        .tutorial-slide[data-slide="2"] .slide-step {
            background: #eaf9f8;
            color: #118983;
        }

        .tutorial-slide[data-slide="3"] .slide-step {
            background: #eef8e8;
            color: #57952f;
        }

        .tutorial-slide h2 {
            max-width: 340px;
            margin: 0;
            color: #201625;
            font-family: 'Rowdies', sans-serif;
            font-size: clamp(1.35rem,2.4vw,1.7rem);
            font-weight: 600;
            letter-spacing: -.02em;
            line-height: 1.12;
        }

        .tutorial-slide h2 span {
            color: var(--orange);
        }

        .tutorial-slide[data-slide="1"] h2 span { color: var(--purple); }
        .tutorial-slide[data-slide="2"] h2 span { color: var(--turquoise); }
        .tutorial-slide[data-slide="3"] h2 span { color: var(--green); }

        .tutorial-slide p {
            max-width: 340px;
            margin: 10px 0 0;
            color: #7a707e;
            font-size: .86rem;
            line-height: 1.55;
        }

        .tutorial-points {
            display: grid;
            gap: 7px;
            margin: 15px 0 0;
            padding: 0;
            list-style: none;
        }

        .tutorial-points li {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4f4554;
            font-size: .8rem;
            line-height: 1.4;
        }

        .tutorial-points li::before {
            content: '✓';
            width: 19px;
            height: 19px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 6px;
            background: rgba(25,185,178,.11);
            color: #118983;
            font-size: .6rem;
            font-weight: 900;
        }

        .slide-visual {
            position: relative;
            min-height: 200px;
            display: grid;
            place-items: center;
            padding: 16px;
            border: 1px solid #eee7f0;
            border-radius: 20px;
            background:
                radial-gradient(circle at 10% 10%, rgba(113,48,167,.13), transparent 34%),
                radial-gradient(circle at 92% 82%, rgba(25,185,178,.16), transparent 31%),
                #f8f5f9;
            overflow: hidden;
        }

        .slide-visual::before,
        .slide-visual::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .slide-visual::before {
            width: 70px;
            height: 70px;
            right: -20px;
            top: -18px;
            background: var(--orange);
            opacity: .12;
        }

        .slide-visual::after {
            width: 56px;
            height: 56px;
            left: -16px;
            bottom: -16px;
            background: var(--turquoise);
            opacity: .13;
        }

        .visual-card {
            position: relative;
            z-index: 2;
            width: 100%;
            padding: 15px;
            border: 1px solid rgba(74,49,84,.11);
            border-radius: 16px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 16px 36px rgba(49,32,57,.12);
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .tutorial-slide.is-active .visual-card {
            animation: visualPop .5s .08s both cubic-bezier(.22,1,.36,1);
        }

        @keyframes visualPop {
            from { opacity: 0; transform: translateY(10px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .visual-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 44px rgba(49,32,57,.16);
        }

        .visual-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 11px;
            color: #2b2130;
            font-weight: 700;
            font-size: .74rem;
        }

        .visual-label::after {
            content: 'PRODOVI';
            padding: 4px 7px;
            border-radius: 999px;
            background: #f3edf5;
            color: var(--purple);
            font-size: .5rem;
            letter-spacing: .08em;
        }

        .discover-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .discover-grid span {
            min-height: 68px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 10px;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: .78rem;
            box-shadow: inset 0 -20px 30px rgba(0,0,0,.08);
            transition: transform .2s ease;
        }

        .discover-grid span:hover { transform: translateY(-2px); }
        .discover-grid span:nth-child(1) { background: var(--purple); }
        .discover-grid span:nth-child(2) { background: var(--orange); }
        .discover-grid span:nth-child(3) { background: var(--turquoise); }
        .discover-grid span:nth-child(4) { background: var(--green); }
        .discover-grid small { display: block; margin-top: 3px; opacity: .72; font-size: .58rem; font-weight: 400; }

        .choice-stack {
            display: grid;
            gap: 7px;
        }

        .choice-row {
            position: relative;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 10px;
            padding: 10px 11px;
            border: 1px solid #e8e0eb;
            border-radius: 11px;
            background: #fff;
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .choice-row:hover {
            transform: translateX(3px);
            border-color: rgba(113,48,167,.35);
        }

        .choice-row.is-selected {
            border: 2px solid var(--purple);
            background: #fbf7fd;
            box-shadow: 0 8px 18px rgba(113,48,167,.1);
        }

        .choice-row span {
            color: #302536;
            font-weight: 700;
            font-size: .82rem;
        }

        .choice-row small {
            display: block;
            margin-top: 2px;
            color: #918696;
            font-size: .62rem;
            font-weight: 400;
        }

        .choice-row b {
            padding: 5px 8px;
            border-radius: 999px;
            background: #f0e7f6;
            color: var(--purple);
            font-size: .58rem;
            white-space: nowrap;
        }

        .choice-row.is-selected b {
            background: var(--purple);
            color: #fff;
        }

        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
        }

        .payment-option {
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px;
            border: 1px solid #e9e1eb;
            border-radius: 15px;
            background: #fff;
            color: #34293a;
            text-align: center;
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }

        .payment-option:hover {
            transform: translateY(-3px);
            border-color: var(--turquoise);
            box-shadow: 0 12px 26px rgba(25,185,178,.12);
        }

        .payment-option:last-child {
            background: linear-gradient(145deg, var(--orange), #e76813);
            color: #fff;
            border-color: transparent;
        }

        .payment-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border: 4px solid currentColor;
            border-radius: 13px;
            font-family: 'Rowdies', sans-serif;
            font-size: .78rem;
        }

        .payment-option:last-child .payment-icon {
            border-radius: 50%;
            font-size: 1.05rem;
        }

        .payment-option strong { font-size: .82rem; }
        .payment-option small { max-width: 130px; opacity: .72; line-height: 1.4; font-size: .64rem; }

        .dashboard-preview {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .dashboard-stat {
            padding: 11px;
            border-radius: 12px;
            background: #f6f2f8;
        }

        .dashboard-stat small {
            display: block;
            color: #8c818f;
            font-size: .6rem;
        }

        .dashboard-stat strong {
            display: block;
            margin-top: 3px;
            color: #2f2434;
            font-size: 1.02rem;
        }

        .dashboard-stat:first-child strong { color: var(--green); }
        .dashboard-stat:nth-child(2) strong { color: var(--turquoise); }

        .growth-chart {
            grid-column: 1 / -1;
            height: 96px;
            display: flex;
            align-items: end;
            gap: 8px;
            padding: 14px 12px 0;
            border-radius: 14px;
            background: #24182a;
            overflow: hidden;
        }

        .growth-chart span {
            flex: 1;
            border-radius: 7px 7px 0 0;
            background: var(--purple);
            transform-origin: bottom;
            animation: tutorialBars 2.4s ease-in-out infinite alternate;
        }

        .growth-chart span:nth-child(1) { height: 28%; }
        .growth-chart span:nth-child(2) { height: 46%; background: var(--orange); }
        .growth-chart span:nth-child(3) { height: 68%; background: var(--turquoise); }
        .growth-chart span:nth-child(4) { height: 91%; background: var(--green); }

        @keyframes tutorialBars {
            to { filter: brightness(1.12); transform: scaleY(.95); }
        }

        .tutorial-nav {
            position: relative;
            z-index: 8;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 14px;
            padding: 13px 20px 16px;
            border-top: 1px solid #eee8f0;
            background: rgba(255,255,255,.94);
        }

        .tutorial-skip {
            justify-self: start;
            padding: 6px 0;
            border: 0;
            background: transparent;
            color: #817587;
            cursor: pointer;
            font-size: .72rem;
            font-weight: 700;
            transition: color .2s ease;
        }

        .tutorial-skip:hover { color: var(--orange); }

        .tutorial-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
        }

        .tutorial-step-counter {
            color: #66596c;
            font-size: .68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .tutorial-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tutorial-dot {
            width: 7px;
            height: 7px;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: #d9d1dc;
            cursor: pointer;
            transition: width .25s ease, background .25s ease, transform .25s ease;
        }

        .tutorial-dot:hover { transform: scale(1.25); }

        .tutorial-dot.is-active {
            width: 22px;
            background: var(--turquoise);
        }

        .tutorial-buttons {
            justify-self: end;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tutorial-button {
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid #dfd6e2;
            border-radius: 10px;
            background: #fff;
            color: #4e4353;
            cursor: pointer;
            font-size: .74rem;
            font-weight: 700;
            transition: transform .2s ease, background .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .tutorial-button:hover:not(:disabled) {
            transform: translateY(-2px);
            border-color: var(--purple);
            color: var(--purple);
        }

        .tutorial-prev:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .tutorial-next {
            min-width: 116px;
            border-color: var(--purple);
            background: var(--purple);
            color: #fff;
            box-shadow: 0 7px 16px rgba(113,48,167,.2);
        }

        .tutorial-next:hover:not(:disabled) {
            border-color: var(--orange);
            background: var(--orange);
            color: #fff;
            box-shadow: 0 10px 20px rgba(244,123,32,.22);
        }

        body.tutorial-open { overflow: hidden; }

        .subscription-alert { display: flex; align-items: center; justify-content: space-between; gap: 22px; margin-bottom: 38px; padding: 22px 24px; border-radius: 18px; border: 1px solid rgba(25,185,178,.22); background: #effbf9; color: #155b57; }
        .subscription-alert.warning { border-color: rgba(244,123,32,.24); background: #fff7ef; color: #7c421a; }
        .alert-copy { display: flex; align-items: center; gap: 15px; } .alert-icon { font-size: 1.5rem; }
        .alert-copy strong, .alert-copy p { display: block; margin: 0; } .alert-copy p { margin-top: 3px; font-size: .9rem; opacity: .8; }
        .alert-code { padding: 3px 7px; border-radius: 6px; background: #fff; font-family: monospace; } .alert-link { color: inherit; font-weight: 700; white-space: nowrap; }
        .plans-section { padding: 78px 0 96px; scroll-margin-top: 100px; }
        .section-heading { max-width: 650px; margin-bottom: 42px; }
        .section-heading h2 { margin: 0; color: #fff; font-family: 'Rowdies', sans-serif; font-size: clamp(2rem,4vw,3.2rem); line-height: 1.08; }
        .section-heading p { margin: 14px 0 0; color: #bdb4c1; font-size: 1.04rem; line-height: 1.7; }
        .plans-container { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 22px; }
        .plan-card { --plan-color: var(--orange); display: flex; min-height: 100%; flex-direction: column; padding: 30px; border: 1px solid #eae4ed; border-top: 6px solid var(--plan-color); border-radius: 22px; background: #fff; box-shadow: 0 18px 45px rgba(55,34,65,.08); transition: transform .3s, box-shadow .3s; }
        .plan-card:nth-child(2) { --plan-color: var(--turquoise); } .plan-card:nth-child(3n) { --plan-color: var(--purple); }
        .plan-card:hover { transform: translateY(-9px); box-shadow: 0 28px 60px rgba(55,34,65,.14); }
        .plan-index { align-self: flex-start; margin-bottom: 23px; padding: 6px 10px; border-radius: 7px; background: #f7f0fa; color: var(--plan-color); font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .plan-title { margin: 0; color: var(--purple-dark); font-family: 'Rowdies', sans-serif; font-size: 1.65rem; }
        .plan-subtitle { min-height: 48px; margin: 7px 0 22px; color: #756c79; line-height: 1.5; }
        .plan-price { color: var(--plan-color); font-family: 'Rowdies', sans-serif; font-size: 2.25rem; line-height: 1; }
        .plan-price small { color: #827887; font-family: 'Varela Round', sans-serif; font-size: .84rem; font-weight: 400; }
        .plan-features { flex: 1; margin: 25px 0 28px; padding-top: 23px; border-top: 1px solid #eee9f0; }
        .feature { display: flex; gap: 10px; margin-bottom: 13px; color: #514957; font-size: .93rem; line-height: 1.45; }
        .feature::before { content: '✓'; flex: 0 0 auto; color: var(--plan-color); font-weight: 800; }
        .choose-btn { width: 100%; min-height: 49px; display: grid; place-items: center; border: 2px solid var(--plan-color); border-radius: 10px; background: var(--plan-color); color: #fff; font-weight: 700; text-decoration: none; transition: filter .25s, transform .25s; }
        .choose-btn:hover { filter: brightness(.92); transform: translateY(-2px); }
        .no-plans { grid-column: 1/-1; padding: 50px 25px; border: 2px dashed rgba(113,48,167,.22); border-radius: 20px; background: var(--soft); color: #65596a; text-align: center; }
        .active-experience { margin: 0 0 90px; padding: 46px; border-radius: 26px; background: var(--purple-dark); color: #fff; }
        .active-experience h2 { margin: 0 0 12px; font-family: 'Rowdies', sans-serif; font-size: clamp(1.8rem,4vw,3rem); }
        .active-experience p { max-width: 650px; margin: 0 0 25px; color: rgba(255,255,255,.78); line-height: 1.7; }
        @media (max-width: 900px) { .experience-hero { grid-template-columns: 1fr; gap: 30px; padding-top: 55px; } .hero-copy { max-width: 720px; } .hero-visual { min-height: 390px; } .plans-container { grid-template-columns: repeat(2,minmax(0,1fr)); } .quick-path { grid-template-columns: 1fr; } }
        @media (max-width: 760px) {
            .tutorial-modal { padding: 0; }
            .tutorial-dialog {
                width: 100%;
                height: 100dvh;
                min-height: 0;
                border: 0;
                border-radius: 0;
            }
            .tutorial-topbar { padding: 13px 16px 11px; }
            .tutorial-brand-mark { width: 28px; height: 28px; border-radius: 8px; }
            .tutorial-brand-copy strong { font-size: .76rem; }
            .tutorial-brand-copy span { display: none; }
            .tutorial-time { display: none; }
            .tutorial-close { width: 32px; height: 32px; }
            .tutorial-slide {
                grid-template-columns: 1fr;
                align-content: start;
                gap: 18px;
                padding: 22px 18px 22px;
            }
            .slide-step { margin-bottom: 9px; font-size: .6rem; }
            .tutorial-slide h2 { max-width: none; font-size: clamp(1.4rem,6.5vw,1.8rem); line-height: 1.1; }
            .tutorial-slide p { max-width: none; margin-top: 9px; font-size: .82rem; line-height: 1.55; }
            .tutorial-points { margin-top: 13px; gap: 6px; }
            .tutorial-points li { font-size: .76rem; }
            .slide-visual { min-height: 170px; padding: 14px; border-radius: 18px; }
            .visual-card { max-width: 360px; padding: 13px; border-radius: 14px; }
            .visual-label { margin-bottom: 10px; font-size: .72rem; }
            .discover-grid { gap: 7px; }
            .discover-grid span { min-height: 62px; padding: 9px; border-radius: 11px; font-size: .72rem; }
            .discover-grid small { font-size: .55rem; }
            .choice-row { padding: 9px 10px; border-radius: 10px; }
            .choice-row span { font-size: .76rem; }
            .payment-option { min-height: 120px; padding: 10px; border-radius: 13px; }
            .payment-icon { width: 42px; height: 42px; border-width: 3px; border-radius: 11px; font-size: .7rem; }
            .payment-option:last-child .payment-icon { font-size: .9rem; }
            .payment-option strong { font-size: .76rem; }
            .payment-option small { font-size: .6rem; }
            .growth-chart { height: 78px; padding: 12px 10px 0; gap: 6px; }
            .dashboard-stat { padding: 9px; }
            .dashboard-stat strong { font-size: .88rem; }
            .tutorial-nav {
                grid-template-columns: auto 1fr;
                grid-template-areas:
                    'indicator indicator'
                    'skip buttons';
                gap: 9px 12px;
                padding: 10px 16px 13px;
            }
            .tutorial-skip { grid-area: skip; align-self: center; font-size: .68rem; }
            .tutorial-indicator { grid-area: indicator; justify-content: space-between; }
            .tutorial-buttons { grid-area: buttons; }
            .tutorial-button { min-height: 37px; padding: 0 11px; font-size: .68rem; }
            .tutorial-prev { min-width: 40px; font-size: 0; }
            .tutorial-prev::before { content: '←'; font-size: .85rem; }
            .tutorial-next { min-width: 106px; }
        }

        @media (max-width: 620px) { .client-home { padding-top: 74px; } .home-shell { width: min(100% - 28px,1180px); } .experience-hero { min-height: auto; padding: 46px 0 35px; } .hero-title { font-size: clamp(2.45rem,13vw,4rem); } .hero-actions { flex-direction: column; } .primary-action, .secondary-action { width: 100%; } .hero-visual { min-height: 330px; } .strategy-board { padding: 22px; } .note-one { top: 4px; right: -5px; } .note-two { bottom: 3px; left: -3px; } .floating-note { padding: 10px 12px; font-size: .76rem; } .subscription-alert { align-items: flex-start; flex-direction: column; } .plans-section { padding: 55px 0 70px; } .plans-container { grid-template-columns: 1fr; } .plan-card { padding: 25px 22px; } .active-experience { padding: 30px 22px; } }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *,*::before,*::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; } }


        /* =========================================================
           PÁGINA DE PLANES — REDISEÑO
           El modal conserva el diseño actual.
        ========================================================= */

        :root {
            --prodovi-purple: #5B2B76;
            --prodovi-orange: #EF6C22;
            --prodovi-gold: #F5A900;
            --prodovi-green: #7DA533;
            --prodovi-turquoise: #117E8C;
            --prodovi-gray: #607078;
        }

        .client-home {
            isolation: isolate;
            min-height: 100vh;
            background:
                radial-gradient(circle at 9% 8%, rgba(91,43,118,.20), transparent 28%),
                radial-gradient(circle at 92% 20%, rgba(17,126,140,.15), transparent 25%),
                #000;
        }

        .client-home::before,
        .client-home::after {
            pointer-events: none !important;
        }

        .home-shell {
            position: relative;
            z-index: 2;
        }

        /* El modal cerrado no puede bloquear ningún botón de la página. */
        .tutorial-modal:not(.is-open) {
            pointer-events: none !important;
        }

        .tutorial-modal.is-open {
            pointer-events: auto !important;
        }


        /* =========================================================
           HERO
        ========================================================= */

        .plans-hero-v2 {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0,1.08fr) minmax(340px,.92fr);
            align-items: center;
            gap: clamp(42px,7vw,85px);
            min-height: 590px;
            padding: 68px 0 58px;
        }

        .plans-hero-copy {
            position: relative;
            z-index: 3;
            max-width: 700px;
        }

        .plans-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 20px;
            padding: 8px 12px;
            border: 1px solid rgba(17,126,140,.32);
            border-radius: 999px;
            background: rgba(17,126,140,.09);
            color: #77d2d8;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .plans-hero-kicker::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--prodovi-turquoise);
            box-shadow: 0 0 0 5px rgba(17,126,140,.13);
        }

        .plans-hero-v2 h1 {
            max-width: 760px;
            margin: 0;
            color: #fff;
            font-family: 'Rowdies',sans-serif;
            font-size: clamp(3rem,6vw,5.5rem);
            font-weight: 600;
            letter-spacing: -.055em;
            line-height: .95;
        }

        .plans-hero-v2 h1 span {
            color: var(--prodovi-gold);
        }

        .plans-hero-description {
            max-width: 640px;
            margin: 23px 0 0;
            color: #b9b3bc;
            font-size: clamp(.98rem,1.55vw,1.12rem);
            line-height: 1.72;
        }

        .plans-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
            margin-top: 30px;
        }

        .plans-main-action,
        .plans-ghost-action {
            min-height: 51px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: .84rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .22s ease, background .22s ease, border-color .22s ease, box-shadow .22s ease;
        }

        .plans-main-action {
            border: 2px solid var(--prodovi-orange);
            background: var(--prodovi-orange);
            color: #fff;
            box-shadow: 0 13px 28px rgba(239,108,34,.22);
        }

        .plans-ghost-action {
            border: 1px solid rgba(255,255,255,.17);
            background: rgba(255,255,255,.05);
            color: #fff;
        }

        .plans-main-action:hover,
        .plans-ghost-action:hover {
            transform: translateY(-3px);
        }

        .plans-ghost-action:hover {
            border-color: var(--prodovi-turquoise);
            background: rgba(17,126,140,.1);
        }

        .plans-hero-benefits {
            display: flex;
            flex-wrap: wrap;
            gap: 13px 22px;
            margin-top: 28px;
            color: #89838c;
            font-size: .72rem;
        }

        .plans-hero-benefits span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .plans-hero-benefits span::before {
            content: '✓';
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(125,165,51,.14);
            color: #9ec55b;
            font-size: .55rem;
            font-weight: 900;
        }


        /* =========================================================
           VISUAL HERO
        ========================================================= */

        .plans-hero-visual {
            position: relative;
            min-height: 430px;
            display: grid;
            place-items: center;
        }

        .brand-system {
            position: relative;
            width: min(100%,440px);
            aspect-ratio: 1;
        }

        .brand-system::before,
        .brand-system::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-system::before {
            inset: 4%;
            border: 1px solid rgba(255,255,255,.08);
        }

        .brand-system::after {
            inset: 20%;
            border: 1px dashed rgba(255,255,255,.09);
        }

        .brand-center {
            position: absolute;
            z-index: 4;
            left: 50%;
            top: 50%;
            width: 160px;
            height: 160px;
            display: grid;
            place-items: center;
            transform: translate(-50%,-50%);
            border-radius: 34px;
            background: linear-gradient(145deg,var(--prodovi-purple),var(--prodovi-turquoise));
            box-shadow: 0 28px 70px rgba(0,0,0,.45);
            color: #fff;
            text-align: center;
        }

        .brand-center strong {
            display: block;
            font-family: 'Rowdies',sans-serif;
            font-size: 1.85rem;
        }

        .brand-center small {
            display: block;
            margin-top: 4px;
            color: rgba(255,255,255,.7);
            font-size: .61rem;
        }

        .brand-float {
            position: absolute;
            z-index: 5;
            min-width: 145px;
            padding: 14px 15px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 15px;
            background: rgba(17,17,20,.96);
            box-shadow: 0 18px 45px rgba(0,0,0,.3);
            animation: brandFloat 4.5s ease-in-out infinite;
        }

        .brand-float small {
            display: block;
            color: #77717b;
            font-size: .58rem;
        }

        .brand-float strong {
            display: block;
            margin-top: 3px;
            color: #fff;
            font-family: 'Rowdies',sans-serif;
            font-size: 1rem;
        }

        .brand-float b {
            display: inline-flex;
            margin-top: 7px;
            padding: 4px 7px;
            border-radius: 999px;
            font-size: .54rem;
        }

        .brand-float-one { left: 0; top: 7%; }
        .brand-float-one b { background: rgba(239,108,34,.14); color: #ff9f68; }

        .brand-float-two { right: 0; top: 34%; animation-delay: .8s; }
        .brand-float-two b { background: rgba(17,126,140,.16); color: #70ccd3; }

        .brand-float-three { left: 9%; bottom: 3%; animation-delay: 1.5s; }
        .brand-float-three b { background: rgba(125,165,51,.15); color: #a4c767; }

        .brand-shape {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-shape.orange { width: 70px; height: 70px; right: 16%; top: 2%; background: var(--prodovi-orange); }
        .brand-shape.gold { width: 38px; height: 38px; left: 3%; top: 46%; background: var(--prodovi-gold); }
        .brand-shape.green { width: 56px; height: 56px; right: 10%; bottom: 3%; background: var(--prodovi-green); }

        @keyframes brandFloat {
            50% { transform: translateY(-8px); }
        }


        /* =========================================================
           BANNER PARA VOLVER A VER EL TUTORIAL
        ========================================================= */

        .tutorial-replay-banner {
            position: relative;
            z-index: 30;
            display: none;
            grid-template-columns: auto minmax(0,1fr) auto;
            align-items: center;
            gap: 15px;
            margin: 0 0 46px;
            padding: 16px 18px;
            overflow: hidden;
            border: 1px solid rgba(17,126,140,.34);
            border-radius: 17px;
            background: linear-gradient(110deg,rgba(17,126,140,.14),rgba(91,43,118,.11)),#101012;
        }

        .tutorial-replay-banner.is-visible {
            display: grid;
            animation: replayIn .38s cubic-bezier(.22,1,.36,1) both;
        }

        .tutorial-replay-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--prodovi-turquoise);
            color: #fff;
            font-size: .92rem;
            box-shadow: 0 9px 22px rgba(17,126,140,.23);
        }

        .tutorial-replay-copy strong {
            display: block;
            color: #fff;
            font-size: .88rem;
        }

        .tutorial-replay-copy span {
            display: block;
            margin-top: 3px;
            color: #928c95;
            font-size: .74rem;
            line-height: 1.45;
        }

        .tutorial-replay-button {
            min-height: 41px;
            padding: 9px 14px;
            border: 0;
            border-radius: 10px;
            background: #fff;
            color: #211b24;
            cursor: pointer;
            font-size: .73rem;
            font-weight: 700;
            white-space: nowrap;
            transition: transform .2s ease, background .2s ease;
        }

        .tutorial-replay-button:hover {
            transform: translateY(-2px);
            background: var(--prodovi-gold);
        }

        @keyframes replayIn {
            from { opacity: 0; transform: translateY(-9px); }
            to { opacity: 1; transform: none; }
        }


        /* =========================================================
           CAMINO RÁPIDO
        ========================================================= */

        .plans-journey {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 12px;
            margin-bottom: 32px;
        }

        .journey-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
            min-height: 88px;
            padding: 20px 16px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            background: #0d0d0f;
            transition: transform .22s ease, border-color .22s ease;
        }

        .journey-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255,255,255,.17);
        }

        .journey-card .journey-number {
            width: 40px;
            height: 40px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            margin-top: 0;
            border-radius: 11px;
            font-family: 'Rowdies',sans-serif;
            font-size: 1.25rem;
            line-height: 1;
            text-align: center;
        }

        .journey-card:nth-child(1) .journey-number { background: rgba(91,43,118,.22); color: #c593df; }
        .journey-card:nth-child(2) .journey-number { background: rgba(239,108,34,.16); color: #ff9a61; }
        .journey-card:nth-child(3) .journey-number { background: rgba(17,126,140,.18); color: #6bcbd2; }

        .journey-card strong {
            display: block;
            color: #f7f5f8;
            font-size: .8rem;
        }

        .journey-card span {
            display: block;
            margin-top: 3px;
            color: #7e7881;
            font-size: .68rem;
            line-height: 1.4;
            text-align: center;
        }


        /* =========================================================
           ALERTAS
        ========================================================= */

        .subscription-alert {
            position: relative;
            z-index: 5;
            min-height: 104px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 38px;
            padding: 20px 22px;
            overflow: hidden;
            border: 1px solid rgba(17,126,140,.28);
            border-radius: 8px;
            border-color: rgba(17,126,140,.28);
            background: #0e1010;
            color: #d6efed;
        }

        .subscription-alert.warning {
            border-color: rgba(239,108,34,.28);
            background: #100f0e;
            color: #f2d4c1;
        }

        .subscription-alert.warning::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--prodovi-orange);
        }

        .alert-copy { min-width: 0; display: flex; align-items: center; gap: 16px; }
        .alert-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(239,108,34,.3);
            border-radius: 8px;
            background: rgba(239,108,34,.12);
            color: #ff9b5d;
            font-size: 1.45rem;
        }

        .alert-content { min-width: 0; }
        .alert-eyebrow {
            display: block;
            margin-bottom: 2px;
            color: #ef8b4d;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .alert-copy strong { display: block; margin: 0; color: #fff; font-size: 1rem; }
        .alert-details { display: flex; flex-wrap: wrap; align-items: center; gap: 7px 15px; margin-top: 7px; }
        .alert-details > span { color: #bdb6c0; font-size: .78rem; }
        .alert-details > span + span { position: relative; }
        .alert-details > span + span::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -8px;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: #6f6872;
        }

        .alert-details .alert-code {
            padding: 4px 7px;
            border: 1px solid rgba(245,169,0,.25);
            border-radius: 5px;
            background: rgba(245,169,0,.09);
            color: #ffd27b;
            font-family: 'Courier New', monospace;
            font-weight: 800;
        }

        .alert-link {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 9px 14px;
            border: 1px solid rgba(239,108,34,.4);
            border-radius: 7px;
            background: #ef6c22;
            color: #fff;
            font-size: .76rem;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
            transition: filter .2s ease, transform .2s ease;
        }

        .alert-link:hover { filter: brightness(1.08); transform: translateY(-2px); }

        @media (max-width: 720px) {
            .subscription-alert { min-height: 0; align-items: stretch; flex-direction: column; gap: 16px; padding: 18px; }
            .alert-copy { align-items: flex-start; }
            .alert-icon { width: 42px; height: 42px; flex-basis: 42px; }
            .alert-details { align-items: flex-start; flex-direction: column; gap: 5px; }
            .alert-details > span + span::before { content: none; }
            .alert-link { width: 100%; }
        }


        /* =========================================================
           PLANES
        ========================================================= */

        .plans-section {
            position: relative;
            z-index: 5;
            padding: 32px 0 105px;
        }

        .plans-heading-v2 {
            display: grid;
            grid-template-columns: minmax(0,1fr) auto;
            align-items: end;
            gap: 25px;
            margin-bottom: 36px;
        }

        .plans-heading-v2 .section-heading {
            max-width: 720px;
            margin: 0;
        }

        .plans-heading-v2 .section-heading h2 {
            font-size: clamp(2.15rem,4vw,3.55rem);
            letter-spacing: -.035em;
        }

        .plans-heading-v2 .section-heading p {
            max-width: 650px;
            color: #98919b;
            font-size: .93rem;
        }

        .plans-available {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 11px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 999px;
            background: rgba(255,255,255,.04);
            color: #9c969e;
            font-size: .68rem;
            white-space: nowrap;
        }

        .plans-available::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--prodovi-green);
        }

        .plans-container {
            position: relative !important;
            z-index: 10 !important;
            isolation: isolate;
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 18px;
            align-items: stretch;
        }

        .plan-card {
            --plan-color: var(--prodovi-orange);
            position: relative !important;
            z-index: 1 !important;
            isolation: isolate;
            min-width: 0;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            overflow: visible;
            padding: 27px;
            border: 1px solid rgba(255,255,255,.1);
            border-top: 1px solid rgba(255,255,255,.1);
            border-radius: 22px;
            background: #101012;
            box-shadow: 0 20px 50px rgba(0,0,0,.2);
            pointer-events: auto !important;
            transition: transform .26s ease, border-color .26s ease, box-shadow .26s ease;
        }

        .plan-card:nth-child(2) { --plan-color: var(--prodovi-turquoise); }
        .plan-card:nth-child(3n) { --plan-color: var(--prodovi-purple); }

        .plan-card::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            border-radius: inherit;
            background: linear-gradient(145deg,rgba(255,255,255,.035),transparent 43%);
            pointer-events: none !important;
        }

        .plan-card::after {
            content: '';
            position: absolute;
            z-index: 0;
            top: 0;
            left: 25px;
            right: 25px;
            height: 3px;
            border-radius: 0 0 6px 6px;
            background: var(--plan-color);
            pointer-events: none !important;
        }

        .plan-card > * {
            position: relative;
            z-index: 2;
        }

        .plan-card:hover {
            z-index: 20 !important;
            transform: translateY(-7px);
            border-color: rgba(255,255,255,.18);
            box-shadow: 0 28px 65px rgba(0,0,0,.31);
        }

        .plan-card.is-featured {
            border-color: rgba(17,126,140,.46);
            background:
                radial-gradient(circle at 100% 0%,rgba(17,126,140,.16),transparent 35%),
                #111315;
        }

        .plan-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 22px;
        }

        .plan-card .plan-index {
            margin: 0;
            border-radius: 999px;
            background: rgba(255,255,255,.055);
            color: var(--plan-color);
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 9px;
            border-radius: 999px;
            background: var(--prodovi-turquoise);
            color: #fff;
            font-size: .57rem;
            font-weight: 700;
            white-space: nowrap;
            pointer-events: none !important;
        }

        .plan-badge::before { content: '★'; }

        .plan-card .plan-title {
            color: #fff;
            font-size: clamp(1.55rem,2.2vw,1.88rem);
        }

        .plan-card .plan-subtitle {
            min-height: 46px;
            margin: 9px 0 23px;
            color: #8d8790;
            font-size: .8rem;
        }

        .plan-card .plan-price {
            color: #fff;
            font-size: clamp(2.1rem,3vw,2.7rem);
        }

        .plan-card .plan-price small {
            color: var(--plan-color);
            font-size: .72rem;
            font-weight: 700;
        }

        .plan-price-helper {
            display: block;
            margin-top: 8px;
            color: #666169;
            font-size: .63rem;
        }

        .plan-card .plan-features {
            margin: 23px 0 24px;
            padding-top: 21px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .plan-card .feature {
            color: #c3bec6;
            font-size: .8rem;
        }

        .plan-card .feature::before {
            width: 19px;
            height: 19px;
            display: grid;
            place-items: center;
            border-radius: 6px;
            background: rgba(255,255,255,.05);
            color: var(--plan-color);
            font-size: .58rem;
        }

        /*
         * FIX CARD DEL MEDIO:
         * El enlace queda en su propia capa y recibe siempre los clicks.
         */
        .choose-btn {
            position: relative !important;
            z-index: 100 !important;
            width: 100%;
            min-height: 50px;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--plan-color);
            border-radius: 12px;
            background: var(--plan-color);
            color: #fff;
            cursor: pointer !important;
            pointer-events: auto !important;
            touch-action: manipulation;
            text-decoration: none;
            font-size: .77rem;
            font-weight: 700;
        }

        .choose-btn::after {
            content: '→';
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: rgba(255,255,255,.14);
            pointer-events: none !important;
        }

        .choose-btn:hover {
            filter: brightness(1.08);
            transform: translateY(-2px);
        }

        .choose-btn:focus-visible {
            outline: 3px solid rgba(245,169,0,.55);
            outline-offset: 3px;
        }


        /* =========================================================
           RESPONSIVE CONTENIDO
        ========================================================= */

        @media (max-width: 980px) {
            .plans-hero-v2 {
                grid-template-columns: 1fr;
                gap: 25px;
                min-height: auto;
                padding: 58px 0 48px;
            }

            .plans-hero-visual { min-height: 380px; }
            .brand-system { width: min(430px,100%); }
            .plans-journey { grid-template-columns: 1fr; }
            .plans-heading-v2 { grid-template-columns: 1fr; }
            .plans-available { justify-self: start; }
            .plans-container { grid-template-columns: repeat(2,minmax(0,1fr)); }
        }

        @media (max-width: 680px) {
            .home-shell { width: min(100% - 28px,1180px); }

            .plans-hero-v2 {
                padding: 43px 0 38px;
            }

            .plans-hero-v2 h1 {
                font-size: clamp(2.6rem,13vw,4rem);
            }

            .plans-hero-description {
                font-size: .9rem;
            }

            .plans-hero-actions {
                flex-direction: column;
            }

            .plans-main-action,
            .plans-ghost-action {
                width: 100%;
            }

            .plans-hero-benefits {
                display: grid;
                gap: 9px;
            }

            .plans-hero-visual {
                min-height: 320px;
            }

            .brand-system {
                width: min(340px,100%);
            }

            .brand-center {
                width: 125px;
                height: 125px;
                border-radius: 27px;
            }

            .brand-center strong { font-size: 1.45rem; }

            .brand-float {
                min-width: 122px;
                padding: 10px 11px;
            }

            .brand-float strong { font-size: .82rem; }
            .brand-shape.orange { width: 50px; height: 50px; }
            .brand-shape.green { width: 42px; height: 42px; }

            .tutorial-replay-banner {
                grid-template-columns: auto 1fr;
                gap: 11px;
                padding: 14px;
            }

            .tutorial-replay-button {
                grid-column: 1/-1;
                width: 100%;
            }

            .plans-journey {
                margin-bottom: 28px;
            }

            .plans-section {
                padding: 20px 0 72px;
            }

            .plans-heading-v2 {
                margin-bottom: 27px;
            }

            .plans-heading-v2 .section-heading h2 {
                font-size: clamp(2rem,10vw,2.9rem);
            }

            .plans-container {
                grid-template-columns: 1fr;
            }

            .plan-card {
                padding: 23px 20px;
            }

            .plan-card .plan-subtitle {
                min-height: 0;
            }
        }



        /* =========================================================
           PLANES V3 — COMPOSICIÓN CLÁSICA
           Inspirada en la referencia:
           - bloque superior blanco
           - CTA negro
           - beneficios en base gris cálida
           - segundo plan destacado
        ========================================================= */

        .plans-container {
            position: relative !important;
            z-index: 20 !important;
            isolation: isolate;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
        }


        .plan-card {
            --plan-accent: var(--prodovi-orange);

            position: relative !important;
            z-index: 1 !important;

            min-width: 0;
            min-height: 100%;

            display: flex;
            flex-direction: column;

            padding: 0 !important;

            overflow: visible;

            border: 1px solid #d9d4d1 !important;
            border-radius: 20px !important;

            background: #ebe7e4 !important;

            box-shadow:
                0 12px 30px
                rgba(0, 0, 0, .12) !important;

            pointer-events: auto !important;

            transition:
                transform .25s ease,
                box-shadow .25s ease,
                border-color .25s ease !important;
        }


        .plan-card:nth-child(1) {
            --plan-accent: var(--prodovi-orange);
        }


        .plan-card:nth-child(2) {
            --plan-accent: var(--prodovi-purple);
        }


        .plan-card:nth-child(3) {
            --plan-accent: var(--prodovi-turquoise);
        }


        /*
         * Desactivamos los adornos anteriores para que nada
         * pueda quedar encima de los enlaces.
         */
        .plan-card::before,
        .plan-card::after {
            display: none !important;
            pointer-events: none !important;
        }


        .plan-card > * {
            position: relative;
            z-index: 2;
        }


        .plan-card:hover {
            z-index: 25 !important;

            transform: translateY(-6px);

            border-color: #c5bfbb !important;

            box-shadow:
                0 20px 44px
                rgba(0, 0, 0, .18) !important;
        }


        /* =========================================================
           PLAN DESTACADO
        ========================================================= */

        .plan-card.is-featured {
            z-index: 3 !important;

            border:
                2px
                solid
                var(--prodovi-purple) !important;

            box-shadow:
                0 18px 44px
                rgba(91, 43, 118, .16) !important;
        }


        .plan-card.is-featured:hover {
            z-index: 30 !important;

            box-shadow:
                0 24px 55px
                rgba(91, 43, 118, .21) !important;
        }


        /* =========================================================
           PARTE SUPERIOR
        ========================================================= */

        .plan-summary {
            position: relative;
            z-index: 5;

            min-height: 348px;

            display: flex;
            flex-direction: column;

            padding:
                28px
                28px
                26px;

            overflow: hidden;

            border-radius:
                19px
                19px
                17px
                17px;

            background: #ffffff;

            color: #111214;

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, .055);
        }


        .plan-card.is-featured .plan-summary {
            border-radius:
                18px
                18px
                16px
                16px;
        }


        /* =========================================================
           ICONO / SÍMBOLO DE CADA PLAN
        ========================================================= */

        .plan-symbol-row {
            min-height: 44px;

            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 12px;

            margin-bottom: 25px;
        }


        .plan-symbol {
            width: 46px;
            height: 46px;
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 3px;
        }


        .plan-symbol i {
            position: static;
            width: 100%;
            height: 100%;
            display: block;
            margin: 0;
            border-radius: 4px;
            opacity: 1;
        }


        .plan-symbol i:nth-child(1) {
            background: var(--prodovi-orange);
            clip-path: polygon(0 0,100% 0,100% 68%,68% 68%,68% 100%,0 100%);
        }


        .plan-symbol i:nth-child(2) {
            background: var(--prodovi-purple);
            clip-path: polygon(18% 0,100% 0,100% 82%,82% 100%,0 100%,0 18%);
        }


        .plan-symbol i:nth-child(3) {
            background: var(--prodovi-turquoise);
            clip-path: polygon(0 0,74% 0,74% 24%,100% 24%,100% 100%,0 100%);
        }


        .plan-symbol i:nth-child(4) {
            background: var(--prodovi-green);
            clip-path: polygon(0 0,100% 0,100% 78%,78% 100%,0 100%);
        }


        .plan-card:nth-child(2) .plan-symbol {
            transform: rotate(90deg);
        }


        .plan-card:nth-child(3) .plan-symbol {
            transform: rotate(180deg);
        }


        /* =========================================================
           BADGE
        ========================================================= */

        .plan-badge {
            position: relative;

            display: inline-flex;
            align-items: center;

            gap: 5px;

            margin:
                -19px
                -19px
                0
                0;

            padding:
                9px
                13px;

            border-radius: 999px;

            background: #101112 !important;

            color: #ffffff;

            font-size: .66rem;
            font-weight: 700;

            letter-spacing: .01em;

            white-space: nowrap;

            pointer-events: none !important;
        }


        .plan-badge::before {
            display: none;
        }


        /* =========================================================
           TEXTOS
        ========================================================= */

        .plan-summary .plan-index {
            display: none;
        }


        .plan-summary .plan-title {
            margin: 0 0 7px;

            color: #101112 !important;

            font-family:
                'Varela Round',
                sans-serif !important;

            font-size:
                clamp(1.22rem, 2vw, 1.48rem) !important;

            font-weight: 700;

            line-height: 1.2;
        }


        .plan-summary .plan-subtitle {
            min-height: 42px !important;

            margin:
                0
                0
                27px !important;

            color: #554d48 !important;

            font-size:
                .82rem !important;

            line-height: 1.5 !important;
        }


        /* =========================================================
           PRECIO
        ========================================================= */

        .plan-price-row {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;

            gap: 6px;

            margin-top: auto;
        }


        .plan-summary .plan-price {
            margin: 0;

            color: #111214 !important;

            font-family:
                'Varela Round',
                sans-serif !important;

            font-size:
                clamp(2.25rem, 3.7vw, 2.85rem) !important;

            font-weight: 700;

            letter-spacing: -.045em;

            line-height: .95;
        }


        .plan-summary .plan-price small {
            display: none;
        }


        .plan-period {
            padding-bottom: 4px;

            color: #5d5854;

            font-size: .72rem;

            line-height: 1.25;
        }


        .plan-price-helper {
            display: none !important;
        }


        /* =========================================================
           CTA SUPERIOR
        ========================================================= */

        .choose-btn {
            position: relative !important;
            z-index: 100 !important;

            width: 100%;

            min-height: 54px;

            display: flex !important;
            align-items: center;
            justify-content: center;

            margin-top: 28px;

            padding:
                12px
                18px;

            border:
                1px
                solid
                #101112 !important;

            border-radius: 8px !important;

            background:
                #101112 !important;

            color: #ffffff !important;

            cursor: pointer !important;

            pointer-events: auto !important;

            touch-action: manipulation;

            text-decoration: none;

            font-size: .82rem !important;
            font-weight: 700;

            box-shadow: none !important;

            transition:
                transform .2s ease,
                background .2s ease,
                color .2s ease !important;
        }


        .choose-btn::after {
            display: none !important;
        }


        .choose-btn:hover {
            transform: translateY(-2px) !important;

            background:
                var(--plan-accent) !important;

            border-color:
                var(--plan-accent) !important;

            filter: none !important;
        }


        .choose-btn:focus-visible {
            outline:
                3px
                solid
                rgba(245, 169, 0, .55);

            outline-offset: 3px;
        }


        /* =========================================================
           PARTE INFERIOR / CARACTERÍSTICAS
        ========================================================= */

        .plan-benefits {
            position: relative;
            z-index: 2;

            flex: 1;

            padding:
                27px
                28px
                31px;

            color: #151515;
        }


        .plan-benefits-title {
            display: block;

            margin-bottom: 17px;

            color: #706964;

            font-size: .66rem;
            font-weight: 700;

            letter-spacing: .09em;

            text-transform: uppercase;
        }


        .plan-benefits .plan-features {
            margin: 0 !important;
            padding: 0 !important;

            border: 0 !important;
        }


        .plan-benefits .feature {
            display: flex;

            align-items: flex-start;

            gap: 9px;

            margin:
                0
                0
                13px !important;

            color:
                #161616 !important;

            font-size:
                .82rem !important;

            line-height: 1.48 !important;
        }


        .plan-benefits .feature:last-child {
            margin-bottom: 0 !important;
        }


        .plan-benefits .feature::before {
            content: '✓';

            width: auto !important;
            height: auto !important;

            flex: 0 0 auto;

            display: block !important;

            margin-top: 0 !important;

            padding: 0 !important;

            border-radius: 0 !important;

            background:
                transparent !important;

            color:
                #111214 !important;

            font-size:
                .78rem !important;

            font-weight: 900;
        }


        /* =========================================================
           BANNER TUTORIAL — ESTÉTICA CLÁSICA
        ========================================================= */

        .tutorial-replay-banner {
            position: relative;

            z-index: 30;

            display: none;

            grid-template-columns: minmax(0, 1fr) minmax(210px, 34%);

            align-items: center;

            gap: 4px 20px;

            margin:
                0
                0
                48px;

            min-height: 170px;

            padding: 34px 38px 27px;

            overflow: hidden;

            border:
                1px
                solid
                #e3e7e8 !important;

            border-radius:
                16px !important;

            background:
                #ffffff !important;

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, .06) !important;
        }


        .tutorial-replay-banner::before {
            content: 'GUÍA RÁPIDA';
            position: absolute;
            top: 22px;
            left: 38px;
            color: #777f82;
            font-size: .56rem;
            font-weight: 800;
            letter-spacing: .13em;
        }

        .tutorial-replay-banner::after {
            display: none !important;
        }

        .tutorial-replay-mosaic {
            position: absolute;
            z-index: 1;
            right: 28px;
            bottom: 22px;
            width: 220px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 58px);
            gap: 7px;
            pointer-events: none;
        }

        .tutorial-replay-mosaic span {
            display: block;
            border-radius: 7px;
        }

        .tutorial-replay-mosaic span:nth-child(1) {
            background: #ef6c22;
            clip-path: polygon(0 0,100% 0,100% 68%,68% 68%,68% 100%,0 100%);
        }

        .tutorial-replay-mosaic span:nth-child(2) {
            background: #5b2b76;
            clip-path: polygon(18% 0,100% 0,100% 82%,82% 100%,0 100%,0 18%);
        }

        .tutorial-replay-mosaic span:nth-child(3) {
            background: #117e8c;
            clip-path: polygon(0 0,74% 0,74% 24%,100% 24%,100% 100%,0 100%);
        }

        .tutorial-replay-mosaic span:nth-child(4) {
            background: #7da533;
            clip-path: polygon(0 0,100% 0,100% 78%,78% 100%,0 100%);
        }

        .tutorial-replay-mosaic span:nth-child(5) {
            background: #f5a900;
            clip-path: polygon(0 0,100% 0,100% 100%,24% 100%,24% 76%,0 76%);
        }

        .tutorial-replay-mosaic span:nth-child(6) {
            background: #ef6c22;
            clip-path: polygon(0 0,82% 0,100% 18%,100% 100%,18% 100%,0 82%);
        }


        .tutorial-replay-banner.is-visible {
            display: grid;

            animation:
                replayClassicIn
                .3s
                ease
                both !important;
        }


        @keyframes replayClassicIn {

            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }


        .tutorial-replay-icon {
            display: none !important;
        }


        .tutorial-replay-copy {
            position: relative;
            z-index: 2;
            grid-column: 1;
            align-self: end;
            max-width: 570px;
        }


        .tutorial-replay-copy strong {
            display: block;

            color:
                #191817 !important;

            font-family: 'Rowdies', sans-serif;

            font-size: clamp(1.2rem, 2.2vw, 1.75rem);

            font-weight: 500;

            line-height: 1.18;
        }


        .tutorial-replay-copy span {
            display: block;

            margin-top: 4px;

            color:
                #687276 !important;

            font-size:
                .78rem;

            line-height: 1.5;
        }


        .tutorial-replay-button {
            position: relative;
            z-index: 2;
            grid-column: 1;
            justify-self: start;
            min-height: auto;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 7px 0 0;

            border:
                1px
                solid
                transparent !important;

            border-radius:
                0 !important;

            background:
                transparent !important;

            color:
                #117e8c !important;

            cursor: pointer;

            font-size:
                .73rem;

            font-weight: 700;

            white-space: nowrap;

            transition:
                color .2s ease !important;
        }

        .tutorial-replay-button::after {
            content: '→';
            margin-left: 9px;
            font-size: 1rem;
        }


        .tutorial-replay-button:hover {
            color: var(--prodovi-orange) !important;
            background: transparent !important;
            border-color: transparent !important;
        }

        .journey-card:nth-child(1) .journey-number { background: #5b2b76 !important; color: #fff !important; }
        .journey-card:nth-child(2) .journey-number { background: #ef6c22 !important; color: #fff !important; }
        .journey-card:nth-child(3) .journey-number { background: #117e8c !important; color: #fff !important; }


        /* =========================================================
           RESPONSIVE PLANES
        ========================================================= */

        @media (max-width: 980px) {

            .home-shell {
                width: min(100% - 32px, 1180px);
            }

            .plans-hero-v2 {
                grid-template-columns: 1fr;
                gap: 24px;
                min-height: auto;
                padding: 52px 0 42px;
            }

            .plans-hero-copy {
                max-width: 760px;
            }

            .plans-hero-visual {
                min-height: 350px;
            }

            .plans-journey {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .plans-container {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }


            .plan-card:nth-child(3) {
                grid-column:
                    1 / -1;

                width: min(50%, 100%);

                justify-self: center;
            }
        }


        @media (max-width: 680px) {

            .client-home {
                padding-top: 72px;
            }

            .home-shell {
                width: min(100% - 24px, 1180px);
            }

            .plans-hero-v2 {
                padding: 38px 0 32px;
            }

            .plans-hero-v2 h1 {
                font-size: clamp(2.25rem, 12vw, 3.5rem);
            }

            .plans-hero-actions {
                flex-direction: column;
            }

            .plans-main-action,
            .plans-ghost-action {
                width: 100%;
            }

            .plans-hero-visual {
                min-height: 285px;
            }

            .plans-journey {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .journey-card {
                min-height: 0;
                flex-direction: row;
                justify-content: flex-start;
                padding: 16px;
                text-align: left;
            }

            .journey-card div,
            .journey-card span {
                text-align: left;
            }

            .plans-container {
                grid-template-columns: 1fr;
            }


            .plan-card:nth-child(3) {
                grid-column: auto;

                width: 100%;
            }


            .plan-card {
                border-radius:
                    18px !important;
            }


            .plan-summary {
                min-height: 0;

                padding:
                    24px
                    22px
                    23px;

                border-radius:
                    17px
                    17px
                    15px
                    15px;
            }


            .plan-symbol-row {
                margin-bottom: 22px;
            }


            .plan-summary .plan-subtitle {
                min-height: 0 !important;

                margin-bottom:
                    24px !important;
            }


            .plan-benefits {
                padding:
                    24px
                    22px
                    27px;
            }


            .choose-btn {
                min-height: 52px;

                margin-top: 25px;
            }


            .tutorial-replay-banner {
                grid-template-columns: minmax(0, 1fr);
                gap: 5px;
                min-height: 190px;
                padding: 43px 22px 22px;
            }


            .tutorial-replay-banner::before {
                top: 22px;
                left: 22px;
            }


            .tutorial-replay-mosaic {
                right: 14px;
                bottom: 15px;
                width: 116px;
                grid-template-rows: repeat(2, 34px);
                gap: 4px;
            }


            .tutorial-replay-copy {
                max-width: calc(100% - 112px);
            }


            .tutorial-replay-button {
                grid-column: 1;
                width: auto;
            }
        }


        @media (max-width: 430px) {

            .plans-hero-kicker {
                font-size: .61rem;
            }

            .plans-hero-description {
                font-size: .88rem;
                line-height: 1.6;
            }

            .plans-hero-visual {
                min-height: 250px;
            }

            .tutorial-replay-banner {
                min-height: 205px;
                margin-bottom: 30px;
                padding-inline: 18px;
            }

            .tutorial-replay-banner::before {
                left: 18px;
            }

            .tutorial-replay-copy {
                max-width: calc(100% - 86px);
            }

            .tutorial-replay-mosaic {
                right: 10px;
                bottom: 12px;
                width: 88px;
                grid-template-rows: repeat(2, 27px);
                gap: 3px;
            }

            .tutorial-replay-copy strong {
                font-size: 1.08rem;
            }

            .tutorial-replay-copy span {
                font-size: .7rem;
            }

            .plan-card,
            .plan-summary,
            .plan-benefits {
                min-width: 0;
            }
        }


        /* Modal centrado y siempre visible sobre la interfaz */
        @media (max-width: 980px) {

            .tutorial-modal {
                position: fixed !important;
                inset: 0 !important;
                z-index: 2147483647 !important;
                width: 100vw;
                height: 100dvh;
                display: grid;
                place-items: center;
                padding: 16px !important;
                overflow: hidden;
            }

            .tutorial-dialog {
                position: relative;
                width: min(720px, calc(100vw - 32px)) !important;
                height: min(620px, calc(100dvh - 32px)) !important;
                min-height: 0 !important;
                max-height: calc(100dvh - 32px);
                margin: auto;
                border: 1px solid rgba(255,255,255,.2) !important;
                border-radius: 20px !important;
                box-shadow: 0 28px 90px rgba(0,0,0,.72) !important;
            }

            .tutorial-topbar {
                position: relative;
                z-index: 20;
                padding-right: 14px;
            }

            .tutorial-close {
                position: relative;
                z-index: 30;
                width: 36px !important;
                height: 36px !important;
                display: grid !important;
                place-items: center;
                flex: 0 0 36px;
                border: 0 !important;
                background: #1d1820 !important;
                color: #fff !important;
                font-size: 1.35rem !important;
                font-weight: 400;
            }
        }


        @media (max-width: 480px) {

            .tutorial-modal {
                padding: 10px !important;
            }

            .tutorial-dialog {
                width: calc(100vw - 20px) !important;
                height: min(600px, calc(100dvh - 20px)) !important;
                max-height: calc(100dvh - 20px);
                border-radius: 16px !important;
            }
        }


        @media (prefers-reduced-motion: reduce) {

            .tutorial-replay-banner.is-visible {
                animation: none !important;
            }
        }

    </style>
</head>
<body>
    @if($isAdminPreview ?? false)
        <style>
            .admin-client-preview-bar{position:fixed;z-index:25000;right:20px;bottom:20px;display:flex;align-items:center;gap:13px;padding:11px 12px 11px 16px;border:1px solid rgba(255,255,255,.16);border-radius:14px;background:rgba(23,19,29,.94);color:#fff;box-shadow:0 18px 45px rgba(0,0,0,.38);backdrop-filter:blur(12px)}.admin-client-preview-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#19b9b2;color:#fff;font-size:1rem}.admin-client-preview-copy strong,.admin-client-preview-copy span{display:block}.admin-client-preview-copy strong{font-size:.74rem}.admin-client-preview-copy span{margin-top:2px;color:#bfb7c4;font-size:.61rem}.admin-client-preview-back{display:inline-flex;align-items:center;gap:6px;padding:9px 11px;border-radius:9px;background:#f47b20;color:#fff;font-size:.67rem;font-weight:800;text-decoration:none}.admin-preview-disabled{cursor:not-allowed!important;opacity:.82}@media(max-width:640px){.admin-client-preview-bar{right:10px;bottom:10px;left:10px}.admin-client-preview-copy{flex:1}.admin-client-preview-copy span{display:none}}
        </style>
        <aside class="admin-client-preview-bar" aria-label="Modo de previsualización">
            <span class="admin-client-preview-icon" aria-hidden="true">◉</span>
            <div class="admin-client-preview-copy"><strong>Vista de usuario</strong><span>Previsualización: las acciones de compra están desactivadas</span></div>
            <a href="{{ route('administrador.planes.index') }}" class="admin-client-preview-back">← Volver</a>
        </aside>
    @endif
    @include('componentes.navbar2')
    <main class="client-home">
        <div class="home-shell">
       

            <div class="tutorial-modal" id="prodoviTutorial" role="dialog" aria-modal="true" aria-labelledby="tutorialTitle" aria-describedby="tutorialDescription">
                <div class="tutorial-dialog">

                    <div class="tutorial-topbar">
                        <div class="tutorial-brand">
                            <span class="tutorial-brand-mark">P</span>
                            <div class="tutorial-brand-copy">
                                <strong>Guía rápida PRODOVI</strong>
                                <span>Cómo empezar en 4 pasos</span>
                            </div>
                        </div>

                        <div class="tutorial-top-actions">
                            <span class="tutorial-time">Menos de 1 min</span>
                            <button class="tutorial-close" type="button" aria-label="Cerrar guía">×</button>
                        </div>
                    </div>

                    <div class="tutorial-progress" aria-hidden="true">
                        <div class="tutorial-progress-bar"></div>
                    </div>

                    <div class="tutorial-slides" aria-live="polite">

                        <section class="tutorial-slide is-active" data-slide="0">
                            <div class="tutorial-copy">
                                <span class="slide-step">Paso 1 · Descubre</span>
                                <h2 id="tutorialTitle">Encuentra tu <span>plan ideal.</span></h2>
                                <p id="tutorialDescription">Revisa qué incluye cada plan y elige según el objetivo de tu marca.</p>

                                <ul class="tutorial-points">
                                    <li>Compara servicios y beneficios.</li>
                                    <li>Elige con calma, sin presión.</li>
                                </ul>
                            </div>

                            <div class="slide-visual" aria-hidden="true">
                                <div class="visual-card">
                                    <div class="visual-label">¿Qué quieres potenciar?</div>
                                    <div class="discover-grid">
                                        <span>Contenido<small>Que te recuerden</small></span>
                                        <span>Publicidad<small>Más alcance</small></span>
                                        <span>Redes<small>Tu comunidad</small></span>
                                        <span>Estrategia<small>Crecimiento</small></span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="tutorial-slide" data-slide="1">
                            <div class="tutorial-copy">
                                <span class="slide-step">Paso 2 · Compara</span>
                                <h2>Elige sin <span>letra pequeña.</span></h2>
                                <p>Cada plan muestra lo esencial: qué incluye, cuánto cuesta y para quién es.</p>

                                <ul class="tutorial-points">
                                    <li>Revisa las características incluidas.</li>
                                    <li>Selecciona el nivel que te convenga.</li>
                                </ul>
                            </div>

                            <div class="slide-visual" aria-hidden="true">
                                <div class="visual-card">
                                    <div class="visual-label">Tres caminos, un objetivo</div>
                                    <div class="choice-stack">
                                        <div class="choice-row">
                                            <span>Marketing Junior<small>Para comenzar</small></span>
                                            <b>Empezar</b>
                                        </div>
                                        <div class="choice-row is-selected">
                                            <span>Marketing Pro<small>Más alcance</small></span>
                                            <b>Más elegido</b>
                                        </div>
                                        <div class="choice-row">
                                            <span>Marketing Super Pro<small>Todo incluido</small></span>
                                            <b>Potenciar</b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="tutorial-slide" data-slide="2">
                            <div class="tutorial-copy">
                                <span class="slide-step">Paso 3 · Activa</span>
                                <h2>Paga fácil y <span>seguro.</span></h2>
                                <p>Elige tu plan y completa el pago con la modalidad que prefieras.</p>

                                <ul class="tutorial-points">
                                    <li>QR: confirma desde tu banca móvil.</li>
                                    <li>Efectivo: usa tu código generado.</li>
                                </ul>
                            </div>

                            <div class="slide-visual" aria-hidden="true">
                                <div class="visual-card payment-options">
                                    <div class="payment-option">
                                        <span class="payment-icon">QR</span>
                                        <strong>Con QR</strong>
                                        <small>Escanea y confirma.</small>
                                    </div>
                                    <div class="payment-option">
                                        <span class="payment-icon">Bs</span>
                                        <strong>En efectivo</strong>
                                        <small>Con tu código.</small>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="tutorial-slide" data-slide="3">
                            <div class="tutorial-copy">
                                <span class="slide-step">Paso 4 · Avanza</span>
                                <h2>Listo, a crecer <span>tu marca.</span></h2>
                                <p>Tu dashboard será tu punto de control para seguir tu suscripción.</p>

                                <ul class="tutorial-points">
                                    <li>Consulta el estado de tu plan.</li>
                                    <li>Vuelve cuando quieras.</li>
                                </ul>
                            </div>

                            <div class="slide-visual" aria-hidden="true">
                                <div class="visual-card">
                                    <div class="visual-label">Tu progreso</div>
                                    <div class="dashboard-preview">
                                        <div class="dashboard-stat"><small>Suscripción</small><strong>Activa ✓</strong></div>
                                        <div class="dashboard-stat"><small>Seguimiento</small><strong>En marcha</strong></div>
                                        <div class="growth-chart"><span></span><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                    </div>

                    <div class="tutorial-nav">
                        <button class="tutorial-skip" type="button">Omitir</button>

                        <div class="tutorial-indicator">
                            <span class="tutorial-step-counter">1 de 4</span>
                            <div class="tutorial-dots" aria-label="Pasos de la guía">
                                <button class="tutorial-dot is-active" type="button" data-go="0" aria-label="Ir al paso 1"></button>
                                <button class="tutorial-dot" type="button" data-go="1" aria-label="Ir al paso 2"></button>
                                <button class="tutorial-dot" type="button" data-go="2" aria-label="Ir al paso 3"></button>
                                <button class="tutorial-dot" type="button" data-go="3" aria-label="Ir al paso 4"></button>
                            </div>
                        </div>

                        <div class="tutorial-buttons">
                            <button class="tutorial-button tutorial-prev" type="button" aria-label="Paso anterior">← Anterior</button>
                            <button class="tutorial-button tutorial-next" type="button">Siguiente →</button>
                        </div>
                    </div>

                </div>
            </div>


          


            {{-- =====================================================
                 BANNER PARA VOLVER A VER EL TUTORIAL
                 Aparece después de cerrar el modal.
            ====================================================== --}}

            <aside
                class="tutorial-replay-banner"
                id="tutorialReplayBanner"
                aria-live="polite"
            >

                <span class="tutorial-replay-icon" aria-hidden="true">
                    ▶
                </span>

                <div class="tutorial-replay-copy">
                    <strong>¿Necesitas una mano para elegir?</strong>
                    <span>
                        Puedes volver a consultar la guía rápida cuando quieras.
                        Te mostramos los planes, el pago y los siguientes pasos.
                    </span>
                </div>

                <button
                    type="button"
                    class="tutorial-replay-button js-open-tutorial"
                >
                    Ver tutorial
                </button>

                <div class="tutorial-replay-mosaic" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

            </aside>


            {{-- =====================================================
                 CAMINO RÁPIDO
            ====================================================== --}}

            <section class="plans-journey" aria-label="Cómo comenzar">

                <article class="journey-card">
                    <span class="journey-number">01</span>

                    <div>
                        <strong>Elige tu plan</strong>
                        <span>Compara beneficios y encuentra tu mejor opción.</span>
                    </div>
                </article>


                <article class="journey-card">
                    <span class="journey-number">02</span>

                    <div>
                        <strong>Completa el pago</strong>
                        <span>Selecciona la modalidad que te resulte más cómoda.</span>
                    </div>
                </article>


                <article class="journey-card">
                    <span class="journey-number">03</span>

                    <div>
                        <strong>Empieza a avanzar</strong>
                        <span>Sigue tu suscripción y próximos pasos desde tu cuenta.</span>
                    </div>
                </article>

            </section>


            @auth
                @if($tieneSuscripcionActiva)
                    <div class="subscription-alert">
                        <div class="alert-copy"><span class="alert-icon">✓</span><span><strong>Tu suscripción está activa</strong><p>Tu estrategia ya está en marcha. Administra todos sus detalles desde el dashboard.</p></span></div>
                        <a href="{{ route('clientes.dashboard') }}" class="alert-link">Ver dashboard →</a>
                    </div>
                @elseif($tieneSuscripcionPendiente && $suscripcionPendiente)
                    <div class="subscription-alert warning" role="status">
                        <div class="alert-copy">
                            <span class="alert-icon" aria-hidden="true">◷</span>
                            <div class="alert-content">
                                <span class="alert-eyebrow">Pago en revisión</span>
                                <strong>Tu suscripción está pendiente</strong>
                                <div class="alert-details">
                                    <span>{{ $pagoPendiente->plan->nombre }}</span>
                                    <span>{{ number_format($pagoPendiente->monto, 2) }} {{ $pagoPendiente->moneda === 'BS' ? 'Bs' : '$' }}</span>
                                    @if($pagoPendiente->metodo === 'fisico' && $pagoPendiente->codigoPago)
                                        <span class="alert-code">Código {{ $pagoPendiente->codigoPago->codigo }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('clientes.pago.estado') }}" class="alert-link">
                            <span>Ver detalles</span><span aria-hidden="true">→</span>
                        </a>
                    </div>
                @endif
            @endauth

            @unless($tieneSuscripcionActiva ?? false)

                <section
                    class="plans-section"
                    id="planes"
                    aria-labelledby="plans-title"
                >

                    <div class="plans-heading-v2">

                        <div class="section-heading">

                            <span class="eyebrow">
                                Planes PRODOVI
                            </span>

                            <h2 id="plans-title">
                                Elige cuánto quieres impulsar tu marca.
                            </h2>

                            <p>
                                Compara lo que incluye cada opción y selecciona
                                el nivel de acompañamiento que mejor encaje con
                                el momento actual de tu negocio.
                            </p>

                        </div>

                        <span class="plans-available">
                            {{ $planes->count() }}
                            {{ $planes->count() === 1 ? 'opción disponible' : 'opciones disponibles' }}
                        </span>

                    </div>


                    <div class="plans-container">

                        @forelse($planes as $plan)

                            <article
                                class="plan-card {{ $loop->iteration === 2 ? 'is-featured' : '' }}"
                            >

                                {{-- ==========================================
                                     BLOQUE SUPERIOR
                                =========================================== --}}
                                <div class="plan-summary">


                                    <div class="plan-symbol-row">

                                        <span
                                            class="plan-symbol"
                                            aria-hidden="true"
                                        >
                                            <i></i>
                                            <i></i>
                                            <i></i>
                                            <i></i>
                                        </span>


                                        @if($loop->iteration === 2)

                                            <span class="plan-badge">
                                                Más elegido
                                            </span>

                                        @endif

                                    </div>


                                    <span class="plan-index">
                                        Plan {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>


                                    <h3 class="plan-title">
                                        {{ $plan->nombre }}
                                    </h3>


                                    <p class="plan-subtitle">
                                        {{ $plan->subtitulo }}
                                    </p>


                                    <div class="plan-price-row">

                                        <div class="plan-price">
                                            {{ number_format($plan->precio) }}
                                            {{ $plan->moneda === 'BS' ? 'Bs' : '$' }}
                                        </div>

                                        <span class="plan-period">
                                            / {{ $plan->periodo_facturacion }}
                                        </span>

                                    </div>


                                    <span class="plan-price-helper">
                                        Revisa todo lo incluido antes de continuar.
                                    </span>


                                    <a
                                        class="choose-btn"
                                        href="{{ route('clientes.pago', \Illuminate\Support\Str::slug($plan->nombre)) }}"
                                        aria-label="{{ auth()->check() ? 'Elegir el plan '.$plan->nombre : 'Registrarme y elegir el plan '.$plan->nombre }}"
                                    >
                                        {{ auth()->check() ? 'Elegir este plan' : 'Registrarme y elegir' }}
                                    </a>

                                </div>


                                {{-- ==========================================
                                     BENEFICIOS
                                =========================================== --}}
                                <div class="plan-benefits">

                                    <span class="plan-benefits-title">
                                        Este plan incluye
                                    </span>


                                    <div class="plan-features">

                                        @foreach($plan->planCaracteristicas as $pc)

                                            <div class="feature">

                                                <span>

                                                    {{ $pc->caracteristica->nombre }}

                                                    @if($pc->frecuencia)
                                                        · {{ $pc->frecuencia }}
                                                    @endif

                                                </span>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            </article>

                        @empty

                            <div class="no-plans">
                                Estamos preparando nuevas opciones para ti.
                                Vuelve muy pronto o conversa con nuestro equipo.
                            </div>

                        @endforelse

                    </div>

                </section>

            @else

                <section class="active-experience">

                    <span
                        class="eyebrow"
                        style="color:#fff"
                    >
                        Todo en un solo lugar
                    </span>

                    <h2>
                        Tu estrategia ya está en movimiento.
                    </h2>

                    <p>
                        Consulta tu plan, revisa su estado y mantén
                        el control de tu experiencia con PRODOVI
                        desde tu espacio personal.
                    </p>

                    <a
                        href="{{ route('clientes.dashboard') }}"
                        class="primary-action"
                    >
                        Abrir mi dashboard →
                    </a>

                </section>

            @endunless
        </div>
    </main>
    @include('componentes.footer')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const modal = document.getElementById('prodoviTutorial');

            if (!modal) {
                return;
            }

            /* Lo saca de cualquier contexto de apilamiento del contenido. */
            document.body.appendChild(modal);


            const dialog = modal.querySelector('.tutorial-dialog');

            const slides = [
                ...modal.querySelectorAll('.tutorial-slide')
            ];

            const dots = [
                ...modal.querySelectorAll('.tutorial-dot')
            ];

            const closeButton = modal.querySelector('.tutorial-close');
            const skipButton = modal.querySelector('.tutorial-skip');
            const nextButton = modal.querySelector('.tutorial-next');
            const prevButton = modal.querySelector('.tutorial-prev');

            const progressBar = modal.querySelector(
                '.tutorial-progress-bar'
            );

            const stepCounter = modal.querySelector(
                '.tutorial-step-counter'
            );

            const replayBanner = document.getElementById(
                'tutorialReplayBanner'
            );

            const openTutorialButtons = [
                ...document.querySelectorAll('.js-open-tutorial')
            ];

            const plansSection = document.getElementById('planes');

            const activeExperience = document.querySelector(
                '.active-experience'
            );


            let currentSlide = 0;
            let touchStartX = 0;
            let touchEndX = 0;


            /* =====================================================
               ACTUALIZAR PASO
            ====================================================== */

            const updateTutorial = (index) => {

                currentSlide = Math.max(
                    0,
                    Math.min(index, slides.length - 1)
                );


                slides.forEach((slide, position) => {

                    const active =
                        position === currentSlide;

                    slide.classList.toggle(
                        'is-active',
                        active
                    );

                    slide.setAttribute(
                        'aria-hidden',
                        active ? 'false' : 'true'
                    );

                });


                dots.forEach((dot, position) => {

                    const active =
                        position === currentSlide;

                    dot.classList.toggle(
                        'is-active',
                        active
                    );

                    dot.setAttribute(
                        'aria-current',
                        active ? 'step' : 'false'
                    );

                });


                const progress =
                    ((currentSlide + 1) / slides.length) * 100;


                if (progressBar) {
                    progressBar.style.width =
                        `${progress}%`;
                }


                if (stepCounter) {
                    stepCounter.textContent =
                        `${currentSlide + 1} de ${slides.length}`;
                }


                if (prevButton) {
                    prevButton.disabled =
                        currentSlide === 0;
                }


                if (nextButton) {

                    nextButton.textContent =
                        currentSlide === slides.length - 1
                            ? 'Ver planes →'
                            : 'Siguiente →';

                }

            };


            /* =====================================================
               BANNER
            ====================================================== */

            const showReplayBanner = () => {

                replayBanner?.classList.add(
                    'is-visible'
                );

            };


            const hideReplayBanner = () => {

                replayBanner?.classList.remove(
                    'is-visible'
                );

            };


            /* =====================================================
               ABRIR MODAL
            ====================================================== */

            const openTutorial = () => {

                hideReplayBanner();

                updateTutorial(0);

                modal.classList.add('is-open');

                document.body.classList.add(
                    'tutorial-open'
                );


                window.setTimeout(() => {

                    closeButton?.focus();

                }, 100);

            };


            /* =====================================================
               CERRAR MODAL
            ====================================================== */

            const closeTutorial = () => {

                modal.classList.remove('is-open');

                document.body.classList.remove(
                    'tutorial-open'
                );


                /*
                 * Al cerrarlo aparece el banner
                 * "Ver tutorial".
                 */
                window.setTimeout(() => {

                    showReplayBanner();

                }, 150);

            };


            /* =====================================================
               TERMINAR TUTORIAL
            ====================================================== */

            const finishTutorial = () => {

                closeTutorial();


                const destination =
                    plansSection ||
                    activeExperience;


                window.setTimeout(() => {

                    destination?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                }, 220);

            };


            /* =====================================================
               SIGUIENTE / ANTERIOR
            ====================================================== */

            nextButton?.addEventListener(
                'click',
                () => {

                    if (
                        currentSlide ===
                        slides.length - 1
                    ) {

                        finishTutorial();
                        return;

                    }


                    updateTutorial(
                        currentSlide + 1
                    );

                }
            );


            prevButton?.addEventListener(
                'click',
                () => {

                    updateTutorial(
                        currentSlide - 1
                    );

                }
            );


            dots.forEach(dot => {

                dot.addEventListener(
                    'click',
                    () => {

                        updateTutorial(
                            Number(dot.dataset.go)
                        );

                    }
                );

            });


            /* =====================================================
               CERRAR
            ====================================================== */

            closeButton?.addEventListener(
                'click',
                closeTutorial
            );


            skipButton?.addEventListener(
                'click',
                closeTutorial
            );


            modal.addEventListener(
                'click',
                event => {

                    if (event.target === modal) {
                        closeTutorial();
                    }

                }
            );


            /* =====================================================
               REABRIR TUTORIAL
            ====================================================== */

            openTutorialButtons.forEach(button => {

                button.addEventListener(
                    'click',
                    openTutorial
                );

            });


            /* =====================================================
               TECLADO
            ====================================================== */

            document.addEventListener(
                'keydown',
                event => {

                    if (
                        !modal.classList.contains(
                            'is-open'
                        )
                    ) {
                        return;
                    }


                    if (event.key === 'Escape') {
                        closeTutorial();
                    }


                    if (event.key === 'ArrowRight') {

                        updateTutorial(
                            currentSlide + 1
                        );

                    }


                    if (event.key === 'ArrowLeft') {

                        updateTutorial(
                            currentSlide - 1
                        );

                    }

                }
            );


            /* =====================================================
               SWIPE MOBILE
            ====================================================== */

            dialog?.addEventListener(
                'touchstart',
                event => {

                    touchStartX =
                        event.changedTouches[0].screenX;

                },
                {
                    passive: true
                }
            );


            dialog?.addEventListener(
                'touchend',
                event => {

                    touchEndX =
                        event.changedTouches[0].screenX;


                    const distance =
                        touchEndX - touchStartX;


                    if (
                        Math.abs(distance) < 55
                    ) {
                        return;
                    }


                    if (
                        distance < 0 &&
                        currentSlide <
                        slides.length - 1
                    ) {

                        updateTutorial(
                            currentSlide + 1
                        );

                    }


                    if (
                        distance > 0 &&
                        currentSlide > 0
                    ) {

                        updateTutorial(
                            currentSlide - 1
                        );

                    }

                },
                {
                    passive: true
                }
            );


            /* =====================================================
               FIX EXTRA DE CLICKS EN CARDS
            ====================================================== */

            document
                .querySelectorAll('.choose-btn')
                .forEach(link => {

                    link.style.pointerEvents = 'auto';

                    link.addEventListener(
                        'click',
                        event => {

                            /*
                             * No prevenimos la navegación.
                             * Evitamos que un padre capture el evento.
                             */
                            event.stopPropagation();

                        }
                    );

                });


            /* =====================================================
               INICIO
            ====================================================== */

            openTutorial();

        });
    </script>
    @if($isAdminPreview ?? false)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('a').forEach(function (link) {
                    if (link.closest('.admin-client-preview-bar')) return;
                    link.classList.add('admin-preview-disabled');
                    link.setAttribute('title', 'Acción desactivada en la vista previa');
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                    });
                });
                document.querySelectorAll('form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                    });
                });
            });
        </script>
    @endif
</body>
</html>
