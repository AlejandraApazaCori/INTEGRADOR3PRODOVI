<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Configura tu cuenta | PRODOVI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Candal&family=Lalezar&family=Varela+Round&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --purple:#7130a7; --purple-dark:#431760; --orange:#f47b20; --green:#72bf44; --turquoise:#19b9b2; --ink:#17131d; --muted:#716978; --soft:#f7f4f8; color-scheme:light; }
        * { box-sizing:border-box; }
        .hidden { display:none!important; }
        html, body { min-height:100%; }
        body { margin:0; min-width:320px; background:#0e0b12; color:var(--ink); font-family:'Varela Round',sans-serif; }
        button, input, textarea { font:inherit; }
        button, a { -webkit-tap-highlight-color:transparent; }
        .onboarding { min-height:100vh; position:relative; overflow:hidden; background:radial-gradient(circle at 8% 12%,rgba(113,48,167,.3),transparent 29%),radial-gradient(circle at 92% 84%,rgba(25,185,178,.2),transparent 28%),#0e0b12; }
        .shape { position:fixed; pointer-events:none; border-radius:32px; opacity:.9; }
        .shape-one { width:190px; height:190px; top:-80px; right:9%; background:var(--orange); transform:rotate(28deg); }
        .shape-two { width:120px; height:120px; bottom:-55px; left:7%; background:var(--green); transform:rotate(45deg); }
        .topbar { width:min(1240px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; justify-content:space-between; position:relative; z-index:3; }
        .brand { display:inline-flex; align-items:center; gap:12px; color:#fff; text-decoration:none; }
        .brand-mark { width:42px; height:42px; display:grid; place-items:center; border-radius:13px; background:var(--purple); color:#fff; font-family:'Lalezar',sans-serif; font-size:1.25rem; box-shadow:0 10px 30px rgba(113,48,167,.35); }
        .brand-name { font-family:'Lalezar',sans-serif; font-size:1.25rem; letter-spacing:.03em; }
        .user-chip { display:flex; align-items:center; gap:9px; color:#d8d1dc; font-size:.87rem; }
        .user-avatar { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; background:rgba(255,255,255,.1); color:var(--turquoise); }
        .shell { width:min(1240px,calc(100% - 40px)); min-height:calc(100vh - 112px); margin:0 auto 26px; display:grid; grid-template-columns:290px minmax(0,1fr); border:1px solid rgba(255,255,255,.1); border-radius:30px; overflow:hidden; position:relative; z-index:2; background:rgba(255,255,255,.04); box-shadow:0 32px 90px rgba(0,0,0,.3); backdrop-filter:blur(15px); }
        .sidebar { padding:38px 28px; color:#fff; background:rgba(0,0,0,.2); border-right:1px solid rgba(255,255,255,.08); }
        .sidebar-kicker { margin:0 0 8px; color:var(--turquoise); font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
        .sidebar-title { margin:0; font-family:'Lalezar',sans-serif; font-size:1.62rem; font-weight:400; line-height:1.15; }
        .steps { display:grid; gap:11px; margin-top:34px; }
        .step-indicator { display:grid; grid-template-columns:42px 1fr; gap:12px; align-items:center; width:100%; padding:12px; border:0; border-radius:14px; background:transparent; color:#aaa2af; text-align:left; cursor:default; transition:.25s ease; }
        .step-indicator.is-active { color:#fff; background:rgba(255,255,255,.09); transform:translateX(4px); }
        .step-indicator.is-complete { color:#e4dfe7; }
        .step-indicator.is-active,.step-indicator.is-complete { cursor:pointer; }
        .step-number { width:38px; height:38px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.16); border-radius:12px; font-family:'Lalezar',sans-serif; }
        .is-active .step-number { border-color:var(--orange); background:var(--orange); color:#fff; box-shadow:0 8px 20px rgba(244,123,32,.25); }
        .is-complete .step-number { border-color:#bcb9b3; background:#bcb9b3; color:#fff; }
        .step-indicator strong { display:block; font-size:.88rem; }
        .step-indicator small { display:block; margin-top:3px; font-size:.72rem; opacity:.7; }
        .sidebar-note { margin-top:34px; padding:16px; border-radius:15px; background:rgba(25,185,178,.1); color:#bde8e5; font-size:.78rem; line-height:1.55; }
        .sidebar-note i { margin-right:7px; color:var(--turquoise); }
        .stage { position:relative; min-width:0; background:var(--soft); }
        .progress { height:5px; background:#e9e2ec; }
        .progress-bar { width:25%; height:100%; border-radius:0 5px 5px 0; background:linear-gradient(90deg,var(--purple),var(--orange)); transition:width .45s ease; }
        .slides { min-height:calc(100% - 5px); position:relative; }
        .slide { display:none; min-height:100%; padding:clamp(34px,6vw,78px); animation:enter .45s ease both; }
        .slide.is-active { display:flex; flex-direction:column; justify-content:center; }
        @keyframes enter { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        .content { width:min(760px,100%); margin:auto; }
        .eyebrow { display:inline-flex; align-items:center; gap:9px; margin-bottom:16px; color:var(--purple); font-size:.75rem; font-weight:700; letter-spacing:.13em; text-transform:uppercase; }
        .eyebrow::before { content:''; width:30px; height:4px; border-radius:5px; background:var(--turquoise); }
        h1, h2 { margin:0; color:var(--ink); font-family:'Lalezar',sans-serif; font-weight:600; letter-spacing:-.035em; }
        h1 { max-width:760px; font-size:clamp(2.65rem,5.6vw,5.3rem); line-height:.98; }
        h2 { font-size:clamp(2.2rem,4vw,3.7rem); line-height:1.03; }
        h1 span, h2 span { color:var(--purple); }
        .lead { max-width:690px; margin:20px 0 0; color:var(--muted); font-size:clamp(1rem,1.7vw,1.14rem); line-height:1.75; }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:12px; margin-top:30px; }
        .btn { min-height:50px; display:inline-flex; align-items:center; justify-content:center; gap:9px; padding:12px 21px; border:2px solid transparent; border-radius:11px; font-weight:700; text-decoration:none; cursor:pointer; transition:transform .22s,box-shadow .22s,opacity .22s; }
        .btn:hover { transform:translateY(-2px); }
        .btn-primary { border-color:var(--orange); background:var(--orange); color:#fff; box-shadow:0 12px 25px rgba(244,123,32,.22); }
        .btn-secondary { border-color:#ded5e3; background:#fff; color:var(--purple); }
        .btn-green { border-color:var(--green); background:var(--green); color:#fff; box-shadow:0 12px 25px rgba(114,191,68,.23); }
        .btn[disabled] { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
        .promise-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:13px; margin-top:34px; }
        .promise { padding:17px; border:1px solid #e7dfea; border-radius:16px; background:#fff; }
        .promise i { color:var(--purple); font-size:1.15rem; }
        .promise strong { display:block; margin-top:12px; font-size:.88rem; }
        .promise small { display:block; margin-top:5px; color:var(--muted); line-height:1.45; }
        .notice { margin:20px 0 0; padding:13px 16px; border-radius:12px; font-size:.87rem; line-height:1.5; }
        .notice-success { border:1px solid rgba(114,191,68,.35); background:rgba(114,191,68,.1); color:#477f25; }
        .notice-error { border:1px solid rgba(220,38,38,.2); background:#fff1f1; color:#b42323; }
        .social-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-top:28px; }
        .social-card { min-height:210px; padding:22px; display:flex; flex-direction:column; border:2px solid #e5dfe8; border-radius:20px; background:#fff; color:var(--ink); text-decoration:none; transition:transform .25s,border-color .25s,box-shadow .25s; }
        .social-card:not(.is-disabled):hover { transform:translateY(-4px); border-color:var(--purple); box-shadow:0 18px 36px rgba(67,23,96,.1); }
        .social-card.is-linked { border-color:rgba(114,191,68,.65); background:linear-gradient(145deg,#fff,#f4faef); }
        .social-card.is-disabled { opacity:.57; cursor:not-allowed; }
        .social-top { display:flex; align-items:flex-start; justify-content:space-between; }
        .social-icon { width:48px; height:48px; display:grid; place-items:center; border-radius:14px; color:#fff; font-size:1.35rem; }
        .facebook { background:#1877f2; }
        .instagram { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }
        .badge { padding:6px 9px; border-radius:999px; background:#f0ebf2; color:var(--muted); font-size:.68rem; font-weight:700; text-transform:uppercase; }
        .is-linked .badge { background:rgba(114,191,68,.14); color:#4e8a29; }
        .social-card h3 { margin:22px 0 6px; font-family:'Lalezar',sans-serif; font-size:1.3rem; }
        .social-card h3 .social-lock { display:inline-grid; width:25px; height:25px; margin-left:5px; place-items:center; vertical-align:middle; border-radius:8px; background:#f0ebf2; color:#8b8191; font-size:.7rem; }
        .social-card p { margin:0; color:var(--muted); font-size:.83rem; line-height:1.5; }
        .social-action { margin-top:auto; padding-top:15px; color:var(--purple); font-size:.82rem; font-weight:700; }
        .is-linked .social-action { color:#4e8a29; }
        .no-social-option { margin-top:16px; }
        .no-social-button { width:100%; display:block; padding:16px 18px; border:1px solid #d5d5d5; border-radius:16px; background:#f3f3f3; color:#555; text-align:left; cursor:pointer; transition:border-color .22s,background .22s; }
        .no-social-button:hover { border-color:#bcbcbc; background:#eaeaea; }
        .no-social-copy strong { display:block; margin-bottom:3px; }
        .no-social-copy small { color:#777; line-height:1.4; }
        .form-card { margin-top:25px; overflow:hidden; border:1px solid #e5dfe8; border-radius:21px; background:#fff; box-shadow:0 18px 45px rgba(67,23,96,.07); }
        .form-head { display:flex; align-items:center; gap:13px; padding:19px 22px; background:linear-gradient(90deg,rgba(113,48,167,.08),rgba(25,185,178,.08)); }
        .form-head-icon { width:42px; height:42px; display:grid; place-items:center; border-radius:12px; background:var(--purple); color:#fff; }
        .form-head strong { display:block; }
        .form-head small { color:var(--muted); }
        .form-body { padding:22px; }
        .form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:17px; }
        .field-full { grid-column:1/-1; }
        label { display:block; margin-bottom:7px; color:#504857; font-size:.8rem; font-weight:700; }
        input, textarea { width:100%; border:1px solid #dcd4e0; border-radius:11px; padding:12px 13px; outline:0; background:#fbfafc; color:var(--ink); transition:border-color .2s,box-shadow .2s,background .2s; }
        input:focus, textarea:focus { border-color:var(--purple); background:#fff; box-shadow:0 0 0 4px rgba(113,48,167,.09); }
        textarea { min-height:92px; resize:vertical; }
        input[type="file"] { padding:10px; color:var(--muted); }
        .field-error { margin:6px 0 0; color:#c32626; font-size:.74rem; }
        .existing-company { display:flex; align-items:center; gap:18px; margin-top:27px; padding:23px; border:2px solid rgba(114,191,68,.5); border-radius:19px; background:#fff; }
        .company-logo { width:62px; height:62px; flex:0 0 auto; display:grid; place-items:center; overflow:hidden; border-radius:16px; background:rgba(113,48,167,.1); color:var(--purple); font-size:1.4rem; }
        .company-logo img { width:100%; height:100%; object-fit:cover; }
        .existing-company h3 { margin:0 0 5px; font-family:'Lalezar',sans-serif; }
        .existing-company p { margin:0; color:var(--muted); font-size:.83rem; }
        .company-edit-button { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; margin-left:auto; border:1px solid #d9cedd; border-radius:10px; background:#fff; color:var(--purple); text-decoration:none; transition:.2s ease; }
        .company-edit-button:hover { border-color:var(--orange); background:var(--orange); color:#fff; transform:translateY(-2px); }
        .finish { text-align:center; }
        .finish-mark { width:94px; height:94px; display:grid; place-items:center; margin:0 auto 25px; border-radius:28px; background:var(--green); color:#fff; font-size:2.55rem; box-shadow:0 18px 40px rgba(114,191,68,.3); transform:rotate(-4deg); }
        .finish .lead { margin-inline:auto; }
        .finish .actions { justify-content:center; }
        .summary { display:flex; justify-content:center; flex-wrap:wrap; gap:9px; margin-top:25px; }
        .summary span { padding:8px 12px; border-radius:999px; background:#fff; color:#5e5663; font-size:.78rem; }
        .summary i { margin-right:6px; color:var(--green); }
        .summary .fa-clock { color:#8b818f; }
        @media (max-width:900px) {
            .shell { grid-template-columns:1fr; }
            .sidebar { padding:22px; border-right:0; border-bottom:1px solid rgba(255,255,255,.08); }
            .sidebar-title,.sidebar-note,.step-indicator small { display:none; }
            .sidebar-kicker { text-align:center; }
            .steps { grid-template-columns:repeat(4,1fr); gap:6px; margin-top:16px; }
            .step-indicator { display:flex; justify-content:center; padding:7px; }
            .step-indicator strong { display:none; }
            .step-indicator.is-active { transform:none; }
            .stage { min-height:680px; }
        }
        @media (max-width:640px) {
            .topbar,.shell { width:min(100% - 22px,1240px); }
            .topbar { min-height:72px; }
            .user-chip span:first-child { display:none; }
            .shell { min-height:calc(100vh - 84px); border-radius:22px; }
            .sidebar { padding:17px 13px; }
            .slide { padding:32px 20px; }
            .social-grid,.promise-grid,.form-grid { grid-template-columns:1fr; }
            .field-full { grid-column:auto; }
            .social-card { min-height:180px; }
            .actions .btn { width:100%; }
            .existing-company { align-items:flex-start; }
        }

        /* Experiencia dividida inspirada en plataformas de gestión social */
        body { overflow-x:hidden; background:#fff; }
        .onboarding { min-height:100vh; background:#fff; overflow:visible; }
        .shape { display:none; }
        .topbar { position:absolute; inset:0 auto auto 0; width:50%; min-height:96px; padding:0 clamp(24px,4vw,68px); z-index:20; }
        .brand-logo { display:block; width:170px; height:auto; }
        .brand-mark,.brand-name { display:none; }
        .user-chip { color:#554d59; }
        .user-avatar { background:#f1ecf3; color:var(--purple); }
        .shell { width:100%; min-height:100vh; margin:0; display:block; border:0; border-radius:0; overflow:visible; background:#fff; box-shadow:none; backdrop-filter:none; }
        .sidebar { position:absolute; z-index:15; top:96px; left:0; width:50%; padding:0 clamp(24px,4vw,68px); border:0; background:transparent; color:var(--ink); }
        .sidebar-kicker,.sidebar-title,.sidebar-note,.step-indicator strong,.step-indicator small { display:none; }
        .steps { display:flex; align-items:center; gap:8px; margin:0; }
        .step-indicator { display:flex; width:auto; flex:1; padding:0; background:transparent!important; transform:none!important; }
        .step-number { width:100%; height:5px; overflow:hidden; border:0; border-radius:99px; background:#e7e1e9; color:transparent; font-size:0; box-shadow:none!important; }
        .step-indicator.is-active .step-number { background:var(--orange); }
        .step-indicator.is-complete .step-number { background:#bcb9b3; color:transparent; }
        .stage { min-height:100vh; display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); background:#fff; }
        .progress { display:none; }
        .slides { min-height:100vh; grid-column:1; }
        .slide { min-height:100vh; padding:145px clamp(28px,5.2vw,100px) 55px; }
        .content { width:min(610px,100%); }
        h1 { font-size:clamp(1.4rem,2.3vw,2.45rem); }
        h2 { font-size:clamp(2.25rem,3.6vw,3.7rem); }
        .lead { font-size:1rem; line-height:1.65; }
        .promise-grid { grid-template-columns:1fr; gap:10px; }
        .promise { display:grid; grid-template-columns:27px 1fr; align-items:center; padding:13px 15px; }
        .promise i { grid-row:1/3; }
        .promise strong,.promise small { margin:0; }
        .social-card { min-height:190px; }
        .form-card { margin-top:18px; }
        .setup-title { font-size:clamp(1.4rem,2.3vw,2.45rem); }
        .form-card { border:0; border-radius:24px; box-shadow:0 22px 55px rgba(67,23,96,.13); }
        .form-head { position:relative; padding:18px 20px; overflow:hidden; background:linear-gradient(110deg,#55515e,#34313b); color:#fff; }
        .form-head-icon { position:relative; z-index:1; background:var(--purple); box-shadow:0 9px 20px rgba(52,15,75,.25); }
        .form-head strong,.form-head small { position:relative; z-index:1; color:#fff; }
        .form-head small { display:block; margin-top:3px; opacity:.78; }
        .form-body { padding:22px; background:linear-gradient(180deg,#fff,#fdfbfe); }
        .form-grid { gap:13px; }
        .field-shell { position:relative; }
        .field-icon { position:absolute; z-index:2; left:14px; top:50%; transform:translateY(-50%); color:var(--purple); pointer-events:none; }
        .field-shell input { padding-left:42px; }
        .field-shell textarea { padding-left:42px; }
        .field-shell .textarea-icon { top:16px; transform:none; }
        .custom-select { position:relative; }
        .select-trigger { width:100%; min-height:46px; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:11px 13px 11px 42px; border:1px solid #dcd4e0; border-radius:11px; background:#fbfafc; color:#807786; text-align:left; cursor:pointer; transition:border-color .2s,box-shadow .2s,background .2s; }
        .select-trigger.has-value { color:var(--ink); }
        .select-trigger[aria-expanded="true"] { border-color:var(--purple); background:#fff; box-shadow:0 0 0 4px rgba(113,48,167,.09); }
        .select-trigger.is-invalid { border-color:#dc2626; box-shadow:0 0 0 4px rgba(220,38,38,.08); }
        .select-trigger .fa-chevron-down { color:#9d92a2; font-size:.75rem; transition:transform .2s; }
        .select-trigger[aria-expanded="true"] .fa-chevron-down { transform:rotate(180deg); }
        .select-menu { position:absolute; z-index:30; top:calc(100% + 8px); left:0; right:0; display:none; max-height:220px; overflow-y:auto; padding:7px; border:1px solid #ded5e3; border-radius:14px; background:#fff; box-shadow:0 18px 38px rgba(67,23,96,.18); }
        .select-menu.is-open { display:block; animation:dropdownIn .2s ease both; }
        @keyframes dropdownIn { from { opacity:0; transform:translateY(-7px); } to { opacity:1; transform:translateY(0); } }
        .select-option { width:100%; display:flex; align-items:center; gap:10px; padding:10px 11px; border:0; border-radius:9px; background:transparent; color:#514858; text-align:left; cursor:pointer; }
        .select-option:hover,.select-option.is-selected { background:#f2eaf7; color:var(--purple); }
        .select-option i { width:20px; color:var(--orange); text-align:center; }
        .logo-drop { position:relative; min-height:46px; display:flex; align-items:center; gap:11px; padding:9px 12px; border:1px dashed #bbaec2; border-radius:11px; background:#fbfafc; color:#776d7c; cursor:pointer; transition:.2s; }
        .logo-drop:hover { border-color:var(--turquoise); background:#f2fbfa; }
        .logo-drop.is-dragging,.logo-drop:focus-within { border-color:var(--purple); background:#f3eaf9; box-shadow:0 0 0 3px rgba(113,48,167,.1); }
        .logo-drop.is-invalid { border-color:#c32626; background:#fff6f6; }
        .logo-drop input { position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .logo-preview { width:30px; height:30px; display:grid; place-items:center; flex:0 0 auto; overflow:hidden; border-radius:8px; background:rgba(113,48,167,.12); color:var(--purple); }
        .logo-preview img { width:100%; height:100%; object-fit:cover; }
        .logo-copy { min-width:0; font-size:.76rem; }
        .logo-copy strong { display:block; overflow:hidden; color:#514858; text-overflow:ellipsis; white-space:nowrap; }
        .logo-copy small { color:#968b9b; }
        textarea { min-height:72px; }
        .actions { margin-top:22px; }

        .visual-panel { position:fixed; z-index:10; inset:0 0 0 50%; overflow:hidden; color:#fff; background:linear-gradient(145deg,var(--purple) 0%,var(--purple-dark) 58%,#26102f 100%); transition:background .5s ease; }
        .visual-panel.is-intro, .visual-panel.is-social, .visual-panel.is-brand, .visual-panel.is-finish { background:linear-gradient(145deg,#383443 0%,#292633 58%,#1c1a25 100%); }
        .visual-panel::before,.visual-panel::after { content:''; position:absolute; border-radius:50%; pointer-events:none; }
        .visual-panel::before { width:560px; height:560px; right:-170px; bottom:-190px; background:var(--orange); opacity:.88; }
        .visual-panel.is-intro::before { display:none; }
        .visual-panel::after { width:360px; height:360px; right:10%; bottom:-170px; border:75px solid rgba(25,185,178,.8); }
        .visual-panel.is-intro::after { display:none; }
        .visual-scene { position:absolute; inset:0; display:flex; flex-direction:column; padding:clamp(48px,6vw,92px); opacity:0; visibility:hidden; transform:translateX(35px); transition:opacity .5s ease,transform .5s ease,visibility .5s; }
        .visual-scene.is-active { opacity:1; visibility:visible; transform:translateX(0); }
        .visual-copy { position:relative; z-index:4; max-width:650px; margin:0; color:#fff; font-family:'Lalezar',sans-serif; font-size:clamp(2.3rem,4vw,4.25rem); font-weight:600; letter-spacing:-.035em; line-height:1.08; }
        .visual-copy span { color:#ffd24c; }
        .hero-person { position:absolute; z-index:3; right:-2%; bottom:-3%; width:min(76%,700px); max-height:72%; object-fit:contain; object-position:right bottom; filter:drop-shadow(-18px 20px 25px rgba(0,0,0,.22)); animation:personFloat 5s ease-in-out infinite; }
        @keyframes personFloat { 50% { transform:translateY(-10px); } }
        .floating-ui { position:absolute; z-index:5; padding:15px 18px; border-radius:16px; background:rgba(255,255,255,.96); color:var(--ink); box-shadow:0 18px 45px rgba(31,10,42,.3); animation:uiFloat 3.8s ease-in-out infinite; }
        @keyframes uiFloat { 50% { transform:translateY(-12px) rotate(1deg); } }
        .float-like { left:12%; bottom:24%; font-family:'Lalezar',sans-serif; font-size:1.25rem; }
        .float-like i { margin-right:9px; color:#e8324a; }
        .float-growth { right:9%; top:38%; animation-delay:.7s; }
        .float-growth strong { display:block; color:var(--green); font-size:1.5rem; }
        .float-post { left:11%; bottom:8%; width:210px; padding:10px; animation-delay:1.1s; }
        .float-post img { display:block; width:100%; height:105px; object-fit:cover; border-radius:10px; }
        .float-post span { display:block; padding:9px 4px 2px; color:#635a68; font-size:.75rem; }
        .visual-panel.is-social::before, .visual-panel.is-brand::before, .visual-panel.is-finish::before { width:460px; height:460px; right:-190px; top:-200px; bottom:auto; background:radial-gradient(circle,rgba(113,48,167,.5),transparent 70%); opacity:1; }
        .visual-panel.is-social::after, .visual-panel.is-brand::after, .visual-panel.is-finish::after { width:440px; height:440px; left:-180px; right:auto; bottom:-210px; border:0; background:radial-gradient(circle,rgba(25,185,178,.3),transparent 70%); }
        .visual-scene[data-visual="2"] { align-items:center; justify-content:center; padding:36px; }
        .social-illustration { position:relative; width:min(100%,540px); aspect-ratio:1; isolation:isolate; }
        .social-illustration::before { content:''; position:absolute; inset:9%; border:1px solid rgba(255,255,255,.1); border-radius:50%; box-shadow:0 0 0 52px rgba(255,255,255,.025),inset 0 0 80px rgba(113,48,167,.14); }
        .social-illustration::after { content:''; position:absolute; inset:0; z-index:-1; border-radius:50%; background:radial-gradient(circle,rgba(244,123,32,.13),transparent 60%); }
        .social-links { position:absolute; inset:0; width:100%; height:100%; overflow:visible; fill:none; stroke:rgba(255,255,255,.48); stroke-width:2; stroke-linecap:round; stroke-dasharray:5 9; }
        .social-links circle { fill:#fff; stroke:none; filter:drop-shadow(0 0 7px rgba(255,255,255,.8)); }
        .social-hub { position:absolute; z-index:3; top:50%; left:50%; width:29%; aspect-ratio:1; display:grid; place-items:center; border:1px solid rgba(255,255,255,.32); border-radius:30%; transform:translate(-50%,-50%) rotate(-8deg); background:linear-gradient(145deg,#9a55cd,#7130a7 65%,#4b1e72); box-shadow:0 24px 70px rgba(8,4,15,.42),inset 0 2px 2px rgba(255,255,255,.35); }
        .social-hub i { transform:rotate(8deg); color:#fff; font-size:clamp(2.4rem,5vw,4.2rem); filter:drop-shadow(0 5px 6px rgba(0,0,0,.18)); }
        .social-node { position:absolute; z-index:4; width:19%; aspect-ratio:1; display:grid; place-items:center; border:1px solid rgba(255,255,255,.55); border-radius:27%; background:#fff; box-shadow:0 18px 42px rgba(10,5,20,.38),inset 0 1px 0 #fff; }
        .social-node i { font-size:clamp(2rem,4.2vw,3.3rem); }
        .social-node-facebook { top:20%; left:9%; transform:rotate(-10deg); color:#1877f2; }
        .social-node-instagram { right:9%; bottom:19%; transform:rotate(9deg); color:#dc3982; }
        .social-signal { position:absolute; z-index:2; top:13%; right:12%; width:16%; aspect-ratio:1; display:grid; place-items:center; border:1px solid rgba(255,255,255,.35); border-radius:50%; background:rgba(255,255,255,.12); color:#ffd24c; font-size:clamp(1.5rem,3vw,2.2rem); box-shadow:0 15px 35px rgba(0,0,0,.15); backdrop-filter:blur(10px); }
        .social-spark { position:absolute; width:9px; height:9px; border-radius:50%; background:#ffd24c; box-shadow:0 0 17px #ffd24c; }
        .social-spark-one { top:10%; left:34%; }.social-spark-two { right:8%; top:50%; }.social-spark-three { left:20%; bottom:17%; }
        @media (prefers-reduced-motion:no-preference) { .social-hub { animation:hubFloat 5s ease-in-out infinite; }.social-node-facebook { animation:facebookFloat 5s ease-in-out infinite; }.social-node-instagram { animation:instagramFloat 5s ease-in-out infinite 1s; } }
        @keyframes hubFloat { 50% { transform:translate(-50%,-54%) rotate(-6deg); } }
        @keyframes facebookFloat { 50% { transform:translateY(-9px) rotate(-7deg); } }
        @keyframes instagramFloat { 50% { transform:translateY(-10px) rotate(6deg); } }
        .visual-scene[data-visual="3"] { align-items:center; justify-content:center; padding:36px; }
        .brand-illustration { position:relative; width:min(100%,560px); aspect-ratio:1; isolation:isolate; }
        .brand-illustration::before { content:''; position:absolute; inset:5%; z-index:-1; border:1px solid rgba(255,255,255,.2); border-radius:50%; box-shadow:0 0 0 50px rgba(255,255,255,.035); }
        .brand-board { position:absolute; inset:14% 13% 18% 13%; overflow:hidden; padding:5%; border:1px solid rgba(255,255,255,.8); border-radius:6%; background:#fff; box-shadow:0 34px 75px rgba(27,8,42,.4); transform:rotate(-6deg); }
        .board-row { display:flex; align-items:center; gap:4%; height:19%; }
        .board-logo { height:100%; aspect-ratio:1; display:grid; place-items:center; border-radius:25%; background:var(--purple); color:#fff; font-size:clamp(1.2rem,2.6vw,2.1rem); }
        .board-lines { flex:1; }
        .board-lines i { display:block; width:55%; height:8px; margin:8px 0; border-radius:8px; background:#e8e2eb; }
        .board-lines i:first-child { width:70%; background:#b7a0c8; }
        .board-artwork { position:relative; height:58%; margin-top:5%; overflow:hidden; border-radius:5%; background:linear-gradient(135deg,#7130a7 0%,#9551c5 52%,#efb143 100%); }
        .board-artwork::before { content:''; position:absolute; width:78%; aspect-ratio:1; right:-21%; top:-34%; border:clamp(18px,5vw,54px) solid rgba(255,255,255,.2); border-radius:50%; }
        .board-artwork::after { content:''; position:absolute; width:55%; aspect-ratio:1; left:-16%; bottom:-45%; border-radius:32%; background:#19b9b2; transform:rotate(25deg); box-shadow:0 -12px 35px rgba(29,11,50,.15); }
        .board-artwork i { position:absolute; z-index:1; top:50%; left:50%; color:#fff; font-size:clamp(3rem,7vw,6rem); transform:translate(-50%,-50%) rotate(6deg); filter:drop-shadow(0 8px 12px rgba(36,10,56,.3)); }
        .board-footer { display:flex; gap:3%; margin-top:6%; }
        .board-footer i { height:8px; width:30%; border-radius:8px; background:#ebe6ee; }
        .board-footer i:last-child { width:18%; }
        .brand-palette { position:absolute; z-index:4; right:1%; bottom:12%; display:flex; gap:clamp(5px,1vw,10px); padding:clamp(10px,2vw,18px); border:1px solid #fff; border-radius:20px; background:#fff; box-shadow:0 18px 38px rgba(27,8,42,.3); transform:rotate(7deg); }
        .brand-palette i { width:clamp(23px,4vw,38px); aspect-ratio:1; border-radius:35%; background:var(--purple); }
        .brand-palette i:nth-child(2) { background:var(--orange); }.brand-palette i:nth-child(3) { background:var(--turquoise); }.brand-palette i:nth-child(4) { background:var(--green); }
        .brand-sparkle { position:absolute; z-index:5; top:8%; right:4%; display:grid; place-items:center; width:15%; aspect-ratio:1; border-radius:26%; background:#ffd24c; color:#7130a7; font-size:clamp(1.4rem,3vw,2.4rem); box-shadow:0 16px 35px rgba(27,8,42,.25); transform:rotate(12deg); }
        @media (prefers-reduced-motion:no-preference) { .brand-palette { animation:paletteFloat 5s ease-in-out infinite; }.brand-sparkle { animation:sparkleFloat 4s ease-in-out infinite 1s; } }
        @keyframes paletteFloat { 50% { transform:translateY(-8px) rotate(5deg); } }
        @keyframes sparkleFloat { 50% { transform:translateY(-7px) rotate(18deg); } }
        .visual-scene[data-visual="4"] { align-items:center; justify-content:center; padding:36px; }
        .completion-illustration { position:relative; width:min(100%,540px); aspect-ratio:1; isolation:isolate; }
        .completion-illustration::before { content:''; position:absolute; inset:8%; z-index:-1; border:1px solid rgba(255,255,255,.18); border-radius:50%; box-shadow:0 0 0 46px rgba(255,255,255,.025),inset 0 0 70px rgba(114,191,68,.06); }
        .completion-illustration::after { content:''; position:absolute; inset:0; z-index:-1; border-radius:50%; background:radial-gradient(circle,rgba(114,191,68,.18),transparent 62%); }
        .completion-card { position:absolute; top:50%; left:50%; width:48%; aspect-ratio:1; display:grid; place-items:center; border:1px solid rgba(255,255,255,.8); border-radius:20%; transform:translate(-50%,-50%) rotate(-7deg); background:linear-gradient(145deg,#fff,#f1ebf5); box-shadow:0 30px 70px rgba(13,7,22,.38); }
        .completion-card::before { content:''; position:absolute; inset:11%; border:2px solid #e6dced; border-radius:17%; }
        .completion-card > i { position:relative; color:var(--purple); font-size:clamp(4rem,9vw,7rem); }
        .completion-check { position:absolute; z-index:2; right:-10%; bottom:-7%; width:37%; aspect-ratio:1; display:grid; place-items:center; border:6px solid #292633; border-radius:50%; background:var(--green); color:#fff; font-size:clamp(2rem,4vw,3.2rem); box-shadow:0 12px 26px rgba(0,0,0,.25); }
        .completion-node { position:absolute; display:grid; place-items:center; width:17%; aspect-ratio:1; border:1px solid rgba(255,255,255,.55); border-radius:27%; background:#fff; font-size:clamp(1.8rem,3.5vw,2.8rem); box-shadow:0 15px 35px rgba(10,5,20,.28); }
        .completion-node-rocket { top:15%; right:10%; color:var(--orange); transform:rotate(10deg); }
        .completion-node-social { bottom:14%; left:9%; color:var(--turquoise); transform:rotate(-10deg); }
        .completion-star { position:absolute; color:#ffd24c; font-size:clamp(1rem,2vw,1.5rem); }
        .completion-star-one { top:16%; left:18%; }.completion-star-two { right:14%; bottom:18%; }.completion-star-three { right:4%; top:48%; }
        @media (prefers-reduced-motion:no-preference) { .completion-card { animation:completionFloat 5s ease-in-out infinite; }.completion-node-rocket { animation:rocketFloat 4s ease-in-out infinite; }.completion-node-social { animation:socialFloat 4.5s ease-in-out infinite 1s; } }
        @keyframes completionFloat { 50% { transform:translate(-50%,-53%) rotate(-5deg); } }
        @keyframes rocketFloat { 50% { transform:translateY(-9px) rotate(14deg); } }
        @keyframes socialFloat { 50% { transform:translateY(-8px) rotate(-6deg); } }

        .visual-scene[data-visual="1"] { align-items:center; justify-content:center; padding:0; }
        .intro-backdrop { position:absolute; width:min(82%,680px); aspect-ratio:1; border:1px solid rgba(255,255,255,.15); border-radius:50%; background:radial-gradient(circle at 35% 28%,#625d6a 0%,#403c49 50%,#25232e 100%); box-shadow:0 35px 90px rgba(0,0,0,.28),inset 0 2px 12px rgba(255,255,255,.08); }
        .intro-backdrop::before { content:''; position:absolute; inset:-8%; border:1px dashed rgba(255,255,255,.22); border-radius:50%; }
        .intro-backdrop::after { content:''; position:absolute; inset:-8%; border-radius:50%; background:radial-gradient(circle at 50% 0,var(--turquoise) 0 7px,transparent 8px); filter:drop-shadow(0 0 8px rgba(25,185,178,.8)); }
        .visual-panel-image { position:relative; z-index:1; display:block; width:100%; height:100%; object-fit:contain; }
        @media (prefers-reduced-motion:no-preference) { .intro-backdrop { animation:introPulse 7s ease-in-out infinite; }.intro-backdrop::before, .intro-backdrop::after { animation:introOrbit 20s linear infinite; } }
        @keyframes introPulse { 50% { transform:scale(1.055); box-shadow:0 42px 100px rgba(0,0,0,.34),inset 0 2px 18px rgba(255,255,255,.12); } }
        @keyframes introOrbit { to { transform:rotate(360deg); } }

        @media (max-width:980px) {
            .topbar { width:100%; background:#fff; }
            .shell { padding-top:96px; }
            .sidebar { position:absolute; top:96px; width:100%; padding:0 24px; }
            .stage { display:block; min-height:auto; }
            .slides,.slide { min-height:calc(100vh - 96px); }
            .slide { padding:70px clamp(24px,7vw,70px) 46px; }
            .visual-panel { display:none; }
        }
        @media (max-width:640px) {
            .topbar { padding:0 22px; }
            .brand-logo { width:145px; }
            .sidebar { padding:0 22px; }
            .stage { min-height:auto; }
            .slides { min-height:calc(100vh - 96px); }
            .slide { min-height:calc(100vh - 96px); padding:62px 22px 35px; }
            .promise-grid { grid-template-columns:1fr; }
        }
        .admin-preview-bar { position:fixed; z-index:50; right:20px; bottom:20px; display:flex; align-items:center; gap:14px; padding:12px 15px; border-radius:13px; background:#17131d; color:#fff; box-shadow:0 16px 40px rgba(0,0,0,.3); font-size:.8rem; }
        .admin-preview-bar a { padding:9px 12px; border-radius:9px; background:var(--orange); color:#fff; font-weight:700; text-decoration:none; white-space:nowrap; }
        @media (max-width:640px) { .admin-preview-bar { right:10px; bottom:10px; left:10px; justify-content:space-between; } }
        .brand-mark, .brand-name, .sidebar-title, .step-number, h1, h2, .social-card h3, .existing-company h3, .visual-copy, .float-like { letter-spacing:.025em; }
    </style>
</head>
<body>
@if($isAdminPreview ?? false)
    <aside class="admin-preview-bar" aria-label="Previsualización de administrador">
        <span><i class="fa-solid fa-eye"></i> Vista previa del registro de empresa</span>
        <a href="{{ route('administrador.empresas.index') }}">Volver a empresas</a>
    </aside>
@endif
<main class="onboarding">
    <div class="shape shape-one"></div><div class="shape shape-two"></div>
    <header class="topbar">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('imagenes/logonegro.png') }}" alt="PRODOVI">
            <span class="brand-mark">P</span><span class="brand-name">PRODOVI</span>
        </div>
        <div class="user-chip"><span>{{ $user->name }}</span><span class="user-avatar"><i class="fa-solid fa-user"></i></span></div>
    </header>
    <section class="shell" aria-label="Configuración inicial">
        <aside class="sidebar">
            <p class="sidebar-kicker">Primeros pasos</p><h3 class="sidebar-title">{{ $isReturningSetup ? 'Preparemos tu nueva marca.' : 'Preparemos todo para comenzar.' }}</h3>
            <nav class="steps" aria-label="Progreso de configuración">
                <button type="button" class="step-indicator" data-indicator="1"><span class="step-number">1</span><span><strong>Bienvenida</strong><small>Conoce la experiencia</small></span></button>
                <button type="button" class="step-indicator" data-indicator="2"><span class="step-number">2</span><span><strong>Redes sociales</strong><small>Conecta tu comunidad</small></span></button>
                <button type="button" class="step-indicator" data-indicator="3"><span class="step-number">3</span><span><strong>Tu empresa</strong><small>Cuéntanos sobre ella</small></span></button>
                <button type="button" class="step-indicator" data-indicator="4"><span class="step-number">4</span><span><strong>Todo listo</strong><small>Entra a tu dashboard</small></span></button>
            </nav>
            <div class="sidebar-note"><i class="fa-solid fa-shield-halved"></i> Tu información se utiliza únicamente para gestionar tu estrategia y tus publicaciones.</div>
        </aside>
        <div class="stage">
            <div class="progress"><div id="progress-bar" class="progress-bar"></div></div>
            <div class="slides">
                <article class="slide" data-step="1"><div class="content">
                    <h1>Impulsamos tu marca en <span>redes sociales.</span></h1>
                    <div class="promise-grid">
                        <div class="promise"><i class="fa-solid fa-wand-magic-sparkles"></i><strong>Contenido para tu marca</strong><small>Publicaciones que reflejan tu identidad.</small></div>
                        <div class="promise"><i class="fa-solid fa-chart-line"></i><strong>Resultados claros</strong><small>Medimos el rendimiento de tus redes.</small></div>
                        <div class="promise"><i class="fa-solid fa-comments"></i><strong>Todo en un lugar</strong><small>Gestiona tus redes desde un solo sitio.</small></div>
                    </div>
                    <div class="actions"><button type="button" class="btn btn-primary" data-next="2">Configura tu cuenta <i class="fa-solid fa-arrow-right"></i></button></div>
                </div></article>

                <article class="slide" data-step="2"><div class="content">
                    <h2 class="setup-title">Conecta <span>tus redes</span></h2>
                    <p class="lead">Vincula al menos una cuenta para comenzar.</p>
                    @if(session('social_accounts_success'))<div class="notice notice-success"><i class="fa-solid fa-circle-check"></i> {{ session('social_accounts_success') }}</div>@endif
                    @if(session('social_accounts_error'))<div class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('social_accounts_error') }}</div>@endif
                    @if(session('onboarding_error'))<div class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('onboarding_error') }}</div>@endif
                    <div class="social-grid">
                        <a href="{{ ($isAdminPreview ?? false) ? '#' : route('clientes.social.redirect', 'facebook') }}" class="social-card {{ $facebookLinked ? 'is-linked' : '' }}">
                            <div class="social-top"><span class="social-icon facebook"><i class="fa-brands fa-facebook-f"></i></span><span class="badge">{{ $facebookLinked ? 'Vinculado' : 'Disponible' }}</span></div>
                            <h3>Facebook</h3><p>Conecta la página de tu negocio.</p><span class="social-action">{{ $facebookLinked ? 'Vinculado · Volver a conectar →' : 'Conectar con Facebook →' }}</span>
                        </a>
                        @if($instagramLinked)
                            <a href="#" onclick="return false" class="social-card is-linked">
                        @elseif($facebookLinked)
                            <a href="{{ route('clientes.social.redirect', 'instagram') }}" class="social-card">
                        @else
                            <div class="social-card is-disabled">
                        @endif
                            <div class="social-top"><span class="social-icon instagram"><i class="fa-brands fa-instagram"></i></span><span class="badge">{{ $instagramLinked ? 'Vinculado' : ($facebookLinked ? 'Disponible' : 'Bloqueado') }}</span></div>
                            <h3>Instagram @unless($facebookLinked || $instagramLinked)<span class="social-lock" aria-label="Bloqueado"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>@endunless</h3><p>{{ $facebookLinked ? 'Conecta el perfil de Instagram asociado a tu negocio.' : 'Conecta Facebook primero.' }}</p><span class="social-action">{{ $instagramLinked ? 'Cuenta conectada ✓' : ($facebookLinked ? 'Conectar Instagram →' : 'Esperando Facebook') }}</span>
                        @if($instagramLinked || $facebookLinked)</a>@else</div>@endif
                    </div>
                    @unless($anyAccountLinked)
                        <form class="no-social-option" action="{{ ($isAdminPreview ?? false) ? '#' : route('clientes.onboarding.skip-social') }}" method="POST">
                            @csrf
                            <button type="submit" class="no-social-button">
                                <span class="no-social-copy"><strong>Aún no tengo redes sociales</strong><small>Puedes vincularlas después.</small></span>
                            </button>
                        </form>
                    @endunless
                    <div class="actions"><button type="button" class="btn btn-secondary" data-next="1"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="button" class="btn btn-primary" data-next="3" {{ ($canContinueSocialSetup || ($isAdminPreview ?? false)) ? '' : 'disabled' }}>Continuar <i class="fa-solid fa-arrow-right"></i></button></div>
                </div></article>

                <article class="slide" data-step="3"><div class="content">
                   <h2 class="setup-title">Registra <span>tu empresa</span></h2>
                    <p class="lead">Completa los datos de tu negocio para personalizar tu contenido.</p>
                    @if(session('onboarding_success'))<div class="notice notice-success"><i class="fa-solid fa-circle-check"></i> {{ session('onboarding_success') }}</div>@endif
                    @if($empresa)
                        <div id="existing-company-summary" class="{{ $errors->any() ? 'hidden' : '' }}">
                            <div class="existing-company">
                                <span class="company-logo">@if($empresa->logo)<img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}">@else<i class="fa-solid fa-building"></i>@endif</span>
                                <div><h3>{{ $empresa->nombre_empresa }}</h3><p>{{ $empresa->tipo_empresa }} · Esta empresa ya está lista para trabajar con PRODOVI.</p></div>
                                <button type="button" id="edit-existing-company" class="company-edit-button" title="Editar empresa" aria-label="Editar empresa"><i class="fa-solid fa-pen"></i></button>
                            </div>
                            <div class="actions"><button type="button" class="btn btn-secondary" data-next="2"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="button" class="btn btn-primary" data-next="4">Continuar <i class="fa-solid fa-arrow-right"></i></button></div>
                        </div>
                        <form id="existing-company-edit-form" class="form-card {{ $errors->any() ? '' : 'hidden' }}" action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_to_onboarding" value="1">
                            <div class="form-head"><span class="form-head-icon"><i class="fa-solid fa-pen"></i></span><span><strong>Editar empresa</strong><small>Actualiza los datos sin salir de este paso</small></span></div>
                            <div class="form-body"><div class="form-grid">
                                <div class="field-full">
                                    <label for="nombre_empresa">Nombre de la empresa *</label>
                                    <div class="field-shell"><i class="field-icon fa-solid fa-store"></i><input id="nombre_empresa" name="nombre_empresa" type="text" required maxlength="255" value="{{ old('nombre_empresa', $empresa->nombre_empresa) }}" placeholder="Ej: Mi negocio"></div>
                                    @error('nombre_empresa')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label id="tipo_empresa_label">Tipo de empresa *</label>
                                    <div class="custom-select" id="company-type-select">
                                        <i class="field-icon fa-solid fa-briefcase"></i>
                                        <input id="tipo_empresa" name="tipo_empresa" type="hidden" value="{{ old('tipo_empresa', $empresa->tipo_empresa) }}" required>
                                        <button class="select-trigger has-value" type="button" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="tipo_empresa_label company-type-value"><span id="company-type-value">{{ old('tipo_empresa', $empresa->tipo_empresa) }}</span><i class="fa-solid fa-chevron-down"></i></button>
                                        <div class="select-menu" role="listbox">
                                            @foreach([['Tecnología','fa-laptop-code'],['Comercio','fa-cart-shopping'],['Servicios','fa-handshake'],['Gastronomía','fa-utensils'],['Salud','fa-heart-pulse'],['Educación','fa-graduation-cap'],['Belleza','fa-spa'],['Inmobiliaria','fa-house'],['Otro','fa-shapes']] as [$type,$icon])
                                                <button type="button" class="select-option {{ old('tipo_empresa', $empresa->tipo_empresa) === $type ? 'is-selected' : '' }}" role="option" data-value="{{ $type }}"><i class="fa-solid {{ $icon }}"></i>{{ $type }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('tipo_empresa')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="logo">Logo de la empresa</label>
                                    <div class="logo-drop"><input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/gif" aria-describedby="logo-help"><span class="logo-preview" id="company-logo-preview">@if($empresa->logo)<img src="{{ asset('storage/'.$empresa->logo) }}" alt="">@else<i class="fa-solid fa-cloud-arrow-up"></i>@endif</span><span class="logo-copy"><strong id="company-logo-name">Haz clic o arrastra otro logo</strong><small id="logo-help">PNG, JPG o GIF · Máx. 2 MB</small></span></div>
                                    <p class="field-error" id="logo-client-error" role="alert" hidden></p>
                                    @error('logo')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="field-full">
                                    <label for="direccion">Dirección de la empresa <span style="font-weight:400;color:#8b818f;">(Opcional)</span></label>
                                    <div class="field-shell"><i class="field-icon fa-solid fa-location-dot"></i><input id="direccion" name="direccion" type="text" maxlength="500" value="{{ old('direccion', $empresa->direccion) }}" placeholder="Ej: Av. Principal #123, Zona Central"></div>
                                    @error('direccion')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="field-full">
                                    <label for="descripcion">Descripción</label>
                                    <div class="field-shell"><i class="field-icon textarea-icon fa-solid fa-align-left"></i><textarea id="descripcion" name="descripcion" placeholder="Cuéntanos brevemente sobre tu negocio.">{{ old('descripcion', $empresa->descripcion) }}</textarea></div>
                                    @error('descripcion')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div><div class="actions"><button type="button" id="cancel-company-edit" class="btn btn-secondary">Cancelar</button><button type="submit" class="btn btn-primary">Guardar cambios <i class="fa-solid fa-check"></i></button></div></div>
                        </form>
                    @else
                        <form class="form-card" action="{{ ($isAdminPreview ?? false) ? '#' : route('clientes.onboarding.empresa') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-head"><span class="form-head-icon"><i class="fa-solid fa-building"></i></span><span><strong>Datos de la empresa</strong><small>Los campos con * son obligatorios.</small></span></div>
                            <div class="form-body"><div class="form-grid">
                                <div class="field-full">
                                    <label for="nombre_empresa">Nombre de la empresa *</label>
                                    <div class="field-shell"><i class="field-icon fa-solid fa-store"></i><input id="nombre_empresa" name="nombre_empresa" type="text" required maxlength="255" value="{{ old('nombre_empresa', $suggestedCompanyName) }}" placeholder="Ej: Mi negocio"></div>
                                    @error('nombre_empresa')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label id="tipo_empresa_label">Tipo de empresa *</label>
                                    <div class="custom-select" id="company-type-select">
                                        <i class="field-icon fa-solid fa-briefcase"></i>
                                        <input id="tipo_empresa" name="tipo_empresa" type="hidden" value="{{ old('tipo_empresa') }}" required>
                                        <button class="select-trigger {{ old('tipo_empresa') ? 'has-value' : '' }}" type="button" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="tipo_empresa_label company-type-value">
                                            <span id="company-type-value">{{ old('tipo_empresa', 'Selecciona una categoría.') }}</span><i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                        <div class="select-menu" role="listbox">
                                            @foreach([
                                                ['Tecnología', 'fa-laptop-code'], ['Comercio', 'fa-cart-shopping'], ['Servicios', 'fa-handshake'],
                                                ['Gastronomía', 'fa-utensils'], ['Salud', 'fa-heart-pulse'], ['Educación', 'fa-graduation-cap'],
                                                ['Belleza', 'fa-spa'], ['Inmobiliaria', 'fa-house'], ['Otro', 'fa-shapes']
                                            ] as [$type, $icon])
                                                <button type="button" class="select-option {{ old('tipo_empresa') === $type ? 'is-selected' : '' }}" role="option" data-value="{{ $type }}"><i class="fa-solid {{ $icon }}"></i>{{ $type }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('tipo_empresa')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="logo">Logo de la empresa</label>
                                    <div class="logo-drop">
                                        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/gif" aria-describedby="logo-help">
                                        <span class="logo-preview" id="company-logo-preview"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                                        <span class="logo-copy"><strong id="company-logo-name">Haz clic o arrastra tu logo</strong><small id="logo-help">PNG, JPG o GIF · Máx. 2 MB</small></span>
                                    </div>
                                    <p class="field-error" id="logo-client-error" role="alert" hidden></p>
                                    @error('logo')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="field-full">
                                    <label for="direccion">Dirección de la empresa <span style="font-weight:400;color:#8b818f;">(Opcional)</span></label>
                                    <div class="field-shell"><i class="field-icon fa-solid fa-location-dot"></i><input id="direccion" name="direccion" type="text" maxlength="500" value="{{ old('direccion') }}" placeholder="Ej: Av. Principal #123, Zona Central"></div>
                                    @error('direccion')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="field-full">
                                    <label for="descripcion">Descripción</label>
                                    <div class="field-shell"><i class="field-icon textarea-icon fa-solid fa-align-left"></i><textarea id="descripcion" name="descripcion" placeholder="Cuéntanos brevemente sobre tu negocio.">{{ old('descripcion') }}</textarea></div>
                                    @error('descripcion')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            </div><div class="actions"><button type="button" class="btn btn-secondary" data-next="2"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="submit" class="btn btn-primary">Crear y continuar <i class="fa-solid fa-arrow-right"></i></button></div></div>
                        </form>
                    @endif
                </div></article>

                <article class="slide" data-step="4"><div class="content finish">
                    <div class="finish-mark"><i class="fa-solid fa-check"></i></div>
                    <h2>¡Tu empresa está <span>registrada!</span></h2><p class="lead">Tu negocio ya está listo para comenzar. Conecta tus redes cuando quieras y prepara tu contenido digital.</p>
                    <div class="summary"><span><i class="fa-solid {{ $anyAccountLinked ? 'fa-check' : 'fa-clock' }}"></i> {{ $anyAccountLinked ? 'Redes conectadas' : 'Redes pendientes' }}</span><span><i class="fa-solid fa-check"></i> Empresa registrada</span><span><i class="fa-solid fa-check"></i> Todo listo</span></div>
                    <form class="actions" action="{{ ($isAdminPreview ?? false) ? '#' : route('clientes.onboarding.complete') }}" method="POST">@csrf<button type="submit" class="btn btn-green">Empezar<i class="fa-solid fa-arrow-right"></i></button></form>
                </div></article>
            </div>

            <aside class="visual-panel" aria-hidden="true">
                <section class="visual-scene" data-visual="1">
                    <span class="intro-backdrop"></span>
                    <img class="visual-panel-image" src="{{ asset('imagenes/configuracion/celular.png') }}" alt="">
                </section>

                <section class="visual-scene" data-visual="2">
                    <div class="social-illustration">
                        <svg class="social-links" viewBox="0 0 500 500" aria-hidden="true">
                            <path d="M250 250 C205 215 160 180 90 150 M250 250 C300 270 350 320 410 350 M250 250 C305 205 345 160 400 105" />
                            <circle cx="90" cy="150" r="5" /><circle cx="410" cy="350" r="5" /><circle cx="400" cy="105" r="5" />
                        </svg>
                        <span class="social-hub"><i class="fa-solid fa-bullhorn"></i></span>
                        <span class="social-node social-node-facebook"><i class="fa-brands fa-facebook-f"></i></span>
                        <span class="social-node social-node-instagram"><i class="fa-brands fa-instagram"></i></span>
                        <span class="social-signal"><i class="fa-solid fa-wifi"></i></span>
                        <span class="social-spark social-spark-one"></span><span class="social-spark social-spark-two"></span><span class="social-spark social-spark-three"></span>
                    </div>
                </section>

                <section class="visual-scene" data-visual="3">
                    <div class="brand-illustration">
                        <div class="brand-board">
                            <div class="board-row"><span class="board-logo"><i class="fa-solid fa-store"></i></span><span class="board-lines"><i></i><i></i></span></div>
                            <div class="board-artwork"><i class="fa-solid fa-gem"></i></div>
                            <div class="board-footer"><i></i><i></i><i></i></div>
                        </div>
                        <div class="brand-palette"><i></i><i></i><i></i><i></i></div>
                        <span class="brand-sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    </div>
                </section>

                <section class="visual-scene" data-visual="4">
                    <div class="completion-illustration">
                        <div class="completion-card"><i class="fa-solid fa-building"></i><span class="completion-check"><i class="fa-solid fa-check"></i></span></div>
                        <span class="completion-node completion-node-rocket"><i class="fa-solid fa-rocket"></i></span>
                        <span class="completion-node completion-node-social"><i class="fa-solid fa-share-nodes"></i></span>
                        <i class="completion-star completion-star-one fa-solid fa-star"></i><i class="completion-star completion-star-two fa-solid fa-star"></i><i class="completion-star completion-star-three fa-solid fa-star"></i>
                    </div>
                </section>
            </aside>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded',function(){
    @if(request()->boolean('inicio'))
        const canonicalUrl=new URL(window.location.href);
        canonicalUrl.searchParams.delete('inicio');
        window.history.replaceState({},'',canonicalUrl);
    @endif
    const slides=Array.from(document.querySelectorAll('[data-step]'));
    const visualPanel=document.querySelector('.visual-panel');
    const visualScenes=Array.from(document.querySelectorAll('[data-visual]'));
    const indicators=Array.from(document.querySelectorAll('[data-indicator]'));
    const progressBar=document.getElementById('progress-bar');
    const maximumStep={{ ($isAdminPreview ?? false) ? 4 : ($canContinueSocialSetup ? ($empresa ? 4 : 3) : 2) }};
    let currentStep=Math.min({{ $initialStep }},maximumStep);
    let furthestStep={{ ($isAdminPreview ?? false) ? 4 : $initialStep }};
    @if($isAdminPreview ?? false)
        document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',event=>{
            event.preventDefault();
            if(form.closest('[data-step="2"]'))showStep(3);
            if(form.closest('[data-step="3"]'))showStep(4);
        }));
        document.querySelectorAll('.social-card[href]').forEach(link=>link.addEventListener('click',event=>event.preventDefault()));
    @endif

    const customSelect=document.getElementById('company-type-select');
    if(customSelect){
        const trigger=customSelect.querySelector('.select-trigger');
        const menu=customSelect.querySelector('.select-menu');
        const input=customSelect.querySelector('#tipo_empresa');
        const valueLabel=customSelect.querySelector('#company-type-value');
        const options=Array.from(customSelect.querySelectorAll('.select-option'));
        const closeSelect=()=>{menu.classList.remove('is-open');trigger.setAttribute('aria-expanded','false');};
        trigger.addEventListener('click',()=>{const opening=!menu.classList.contains('is-open');closeSelect();if(opening){menu.classList.add('is-open');trigger.setAttribute('aria-expanded','true');}});
        options.forEach(option=>option.addEventListener('click',()=>{
            input.value=option.dataset.value;
            valueLabel.textContent=option.dataset.value;
            trigger.classList.add('has-value');trigger.classList.remove('is-invalid');
            options.forEach(item=>item.classList.toggle('is-selected',item===option));
            closeSelect();
        }));
        document.addEventListener('click',event=>{if(!customSelect.contains(event.target))closeSelect();});
        document.addEventListener('keydown',event=>{if(event.key==='Escape')closeSelect();});
        customSelect.closest('form')?.addEventListener('submit',event=>{if(!input.value){event.preventDefault();trigger.classList.add('is-invalid');menu.classList.add('is-open');trigger.setAttribute('aria-expanded','true');trigger.focus();}});
    }

    const logoInput=document.getElementById('logo');
    const logoPreview=document.getElementById('company-logo-preview');
    const logoName=document.getElementById('company-logo-name');
    const logoDrop=logoInput?.closest('.logo-drop');
    const logoError=document.getElementById('logo-client-error');
    const acceptedLogoTypes=['image/jpeg','image/png','image/gif'];
    const showLogoError=message=>{
        logoError.textContent=message;
        logoError.hidden=!message;
        logoDrop.classList.toggle('is-invalid',Boolean(message));
    };
    logoInput?.addEventListener('change',()=>{
        const file=logoInput.files?.[0];
        if(!file)return;
        if(!acceptedLogoTypes.includes(file.type)||file.size>2*1024*1024){
            showLogoError(!acceptedLogoTypes.includes(file.type)?'Selecciona una imagen PNG, JPG o GIF.':'La imagen debe pesar como máximo 2 MB.');
            logoInput.value='';
            return;
        }
        showLogoError('');
        logoName.textContent=file.name;
        const reader=new FileReader();
        reader.onload=event=>{logoPreview.innerHTML=`<img src="${event.target.result}" alt="Vista previa del logo">`;};
        reader.readAsDataURL(file);
    });
    if(logoDrop){
        let dragDepth=0;
        logoDrop.addEventListener('dragenter',event=>{event.preventDefault();dragDepth++;logoDrop.classList.add('is-dragging');});
        logoDrop.addEventListener('dragover',event=>{event.preventDefault();event.dataTransfer.dropEffect='copy';});
        logoDrop.addEventListener('dragleave',event=>{event.preventDefault();dragDepth=Math.max(0,dragDepth-1);if(!dragDepth)logoDrop.classList.remove('is-dragging');});
        logoDrop.addEventListener('drop',event=>{
            event.preventDefault();
            dragDepth=0;
            logoDrop.classList.remove('is-dragging');
            const file=event.dataTransfer?.files?.[0];
            if(!file)return;
            const transfer=new DataTransfer();
            transfer.items.add(file);
            logoInput.files=transfer.files;
            logoInput.dispatchEvent(new Event('change',{bubbles:true}));
        });
    }
    const companySummary=document.getElementById('existing-company-summary');
    const companyEditForm=document.getElementById('existing-company-edit-form');
    document.getElementById('edit-existing-company')?.addEventListener('click',()=>{
        companySummary?.classList.add('hidden');
        companyEditForm?.classList.remove('hidden');
    });
    document.getElementById('cancel-company-edit')?.addEventListener('click',()=>{
        companyEditForm?.classList.add('hidden');
        companySummary?.classList.remove('hidden');
    });
    function showStep(step){
        currentStep=Math.max(1,Math.min(Number(step),maximumStep));
        furthestStep=Math.max(furthestStep,currentStep);
        slides.forEach(slide=>slide.classList.toggle('is-active',Number(slide.dataset.step)===currentStep));
        visualPanel.classList.toggle('is-intro',currentStep===1);
        visualPanel.classList.toggle('is-social',currentStep===2);
        visualPanel.classList.toggle('is-brand',currentStep===3);
        visualPanel.classList.toggle('is-finish',currentStep===4);
        visualScenes.forEach(scene=>scene.classList.toggle('is-active',Number(scene.dataset.visual)===currentStep));
        indicators.forEach(indicator=>{const stepNumber=Number(indicator.dataset.indicator);const complete=stepNumber<=furthestStep&&stepNumber!==currentStep;indicator.classList.toggle('is-active',stepNumber===currentStep);indicator.classList.toggle('is-complete',complete);indicator.setAttribute('aria-disabled',stepNumber>furthestStep?'true':'false');indicator.querySelector('.step-number').textContent=complete?'✓':stepNumber;});
        progressBar.style.width=(currentStep*25)+'%';
        window.scrollTo({top:0,behavior:'smooth'});
    }
    document.querySelectorAll('[data-next]').forEach(button=>button.addEventListener('click',()=>{if(!button.disabled)showStep(button.dataset.next);}));
    indicators.forEach(indicator=>indicator.addEventListener('click',()=>{const targetStep=Number(indicator.dataset.indicator);if(targetStep<=furthestStep&&targetStep<=maximumStep)showStep(targetStep);}));
    showStep(currentStep);
});
</script>
</body>
</html>
