<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Configura tu cuenta | PRODOVI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600;700&family=Varela+Round&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --purple:#7130a7; --purple-dark:#431760; --orange:#f47b20; --green:#72bf44; --turquoise:#19b9b2; --ink:#17131d; --muted:#716978; --soft:#f7f4f8; color-scheme:light; }
        * { box-sizing:border-box; }
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
        .brand-mark { width:42px; height:42px; display:grid; place-items:center; border-radius:13px; background:var(--purple); color:#fff; font-family:'Rowdies',sans-serif; font-size:1.25rem; box-shadow:0 10px 30px rgba(113,48,167,.35); }
        .brand-name { font-family:'Rowdies',sans-serif; font-size:1.25rem; letter-spacing:.03em; }
        .user-chip { display:flex; align-items:center; gap:9px; color:#d8d1dc; font-size:.87rem; }
        .user-avatar { width:34px; height:34px; display:grid; place-items:center; border-radius:50%; background:rgba(255,255,255,.1); color:var(--turquoise); }
        .shell { width:min(1240px,calc(100% - 40px)); min-height:calc(100vh - 112px); margin:0 auto 26px; display:grid; grid-template-columns:290px minmax(0,1fr); border:1px solid rgba(255,255,255,.1); border-radius:30px; overflow:hidden; position:relative; z-index:2; background:rgba(255,255,255,.04); box-shadow:0 32px 90px rgba(0,0,0,.3); backdrop-filter:blur(15px); }
        .sidebar { padding:38px 28px; color:#fff; background:rgba(0,0,0,.2); border-right:1px solid rgba(255,255,255,.08); }
        .sidebar-kicker { margin:0 0 8px; color:var(--turquoise); font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
        .sidebar-title { margin:0; font-family:'Rowdies',sans-serif; font-size:1.62rem; font-weight:400; line-height:1.15; }
        .steps { display:grid; gap:11px; margin-top:34px; }
        .step-indicator { display:grid; grid-template-columns:42px 1fr; gap:12px; align-items:center; width:100%; padding:12px; border:0; border-radius:14px; background:transparent; color:#aaa2af; text-align:left; cursor:default; transition:.25s ease; }
        .step-indicator.is-active { color:#fff; background:rgba(255,255,255,.09); transform:translateX(4px); }
        .step-indicator.is-complete { color:#e4dfe7; }
        .step-number { width:38px; height:38px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.16); border-radius:12px; font-family:'Rowdies',sans-serif; }
        .is-active .step-number { border-color:var(--orange); background:var(--orange); color:#fff; box-shadow:0 8px 20px rgba(244,123,32,.25); }
        .is-complete .step-number { border-color:var(--green); background:rgba(114,191,68,.15); color:var(--green); }
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
        h1, h2 { margin:0; color:var(--ink); font-family:'Rowdies',sans-serif; font-weight:600; letter-spacing:-.035em; }
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
        .promise i { color:var(--turquoise); font-size:1.15rem; }
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
        .social-card h3 { margin:22px 0 6px; font-family:'Rowdies',sans-serif; font-size:1.3rem; }
        .social-card p { margin:0; color:var(--muted); font-size:.83rem; line-height:1.5; }
        .social-action { margin-top:auto; padding-top:15px; color:var(--purple); font-size:.82rem; font-weight:700; }
        .is-linked .social-action { color:#4e8a29; }
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
        .existing-company h3 { margin:0 0 5px; font-family:'Rowdies',sans-serif; }
        .existing-company p { margin:0; color:var(--muted); font-size:.83rem; }
        .finish { text-align:center; }
        .finish-mark { width:94px; height:94px; display:grid; place-items:center; margin:0 auto 25px; border-radius:28px; background:var(--green); color:#fff; font-size:2.55rem; box-shadow:0 18px 40px rgba(114,191,68,.3); transform:rotate(-4deg); }
        .finish .lead { margin-inline:auto; }
        .finish .actions { justify-content:center; }
        .summary { display:flex; justify-content:center; flex-wrap:wrap; gap:9px; margin-top:25px; }
        .summary span { padding:8px 12px; border-radius:999px; background:#fff; color:#5e5663; font-size:.78rem; }
        .summary i { margin-right:6px; color:var(--green); }
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
    </style>
</head>
<body>
<main class="onboarding">
    <div class="shape shape-one"></div><div class="shape shape-two"></div>
    <header class="topbar">
        <div class="brand"><span class="brand-mark">P</span><span class="brand-name">PRODOVI</span></div>
        <div class="user-chip"><span>{{ $user->name }}</span><span class="user-avatar"><i class="fa-solid fa-user"></i></span></div>
    </header>
    <section class="shell" aria-label="Configuración inicial">
        <aside class="sidebar">
            <p class="sidebar-kicker">Primeros pasos</p><h3 class="sidebar-title">Preparemos todo para comenzar.</h3>
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
                    <span class="eyebrow">Bienvenido a PRODOVI</span>
                    <h1>Nos encargaremos del manejo de <span>tus redes.</span></h1>
                    <p class="lead">Configuremos tu cuenta para conocer tu negocio, conectar con tu comunidad y convertir tus objetivos en una estrategia digital clara.</p>
                    <div class="promise-grid">
                        <div class="promise"><i class="fa-solid fa-wand-magic-sparkles"></i><strong>Contenido con propósito</strong><small>Publicaciones alineadas con la identidad de tu marca.</small></div>
                        <div class="promise"><i class="fa-solid fa-chart-line"></i><strong>Decisiones con datos</strong><small>Seguimiento sencillo del rendimiento de tus redes.</small></div>
                        <div class="promise"><i class="fa-solid fa-comments"></i><strong>Todo conectado</strong><small>Tu empresa y sus canales desde un solo lugar.</small></div>
                    </div>
                    <div class="actions"><button type="button" class="btn btn-primary" data-next="2">Configura tu cuenta <i class="fa-solid fa-arrow-right"></i></button></div>
                </div></article>

                <article class="slide" data-step="2"><div class="content">
                    <span class="eyebrow">Conecta tu comunidad</span><h2>Vincula alguna de <span>tus cuentas.</span></h2>
                    <p class="lead">Así podremos identificar la página de tu negocio y preparar la gestión de contenido. Necesitas vincular al menos una para continuar.</p>
                    @if(session('social_accounts_success'))<div class="notice notice-success"><i class="fa-solid fa-circle-check"></i> {{ session('social_accounts_success') }}</div>@endif
                    @if(session('social_accounts_error'))<div class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('social_accounts_error') }}</div>@endif
                    @if(session('onboarding_error'))<div class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('onboarding_error') }}</div>@endif
                    <div class="social-grid">
                        <a href="{{ $facebookLinked ? '#' : route('clientes.social.redirect', 'facebook') }}" class="social-card {{ $facebookLinked ? 'is-linked' : '' }}" {{ $facebookLinked ? 'onclick=return false' : '' }}>
                            <div class="social-top"><span class="social-icon facebook"><i class="fa-brands fa-facebook-f"></i></span><span class="badge">{{ $facebookLinked ? 'Vinculado' : 'Disponible' }}</span></div>
                            <h3>Facebook</h3><p>Autoriza tu página principal y permite que PRODOVI la reconozca.</p><span class="social-action">{{ $facebookLinked ? 'Cuenta conectada ✓' : 'Conectar con Facebook →' }}</span>
                        </a>
                        @if($instagramLinked)
                            <a href="#" onclick="return false" class="social-card is-linked">
                        @elseif($facebookLinked)
                            <a href="{{ route('clientes.social.redirect', 'instagram') }}" class="social-card">
                        @else
                            <div class="social-card is-disabled">
                        @endif
                            <div class="social-top"><span class="social-icon instagram"><i class="fa-brands fa-instagram"></i></span><span class="badge">{{ $instagramLinked ? 'Vinculado' : ($facebookLinked ? 'Disponible' : 'Bloqueado') }}</span></div>
                            <h3>Instagram</h3><p>{{ $facebookLinked ? 'Conecta el perfil de Instagram asociado a tu negocio.' : 'Primero vincula Facebook para habilitar esta opción.' }}</p><span class="social-action">{{ $instagramLinked ? 'Cuenta conectada ✓' : ($facebookLinked ? 'Conectar Instagram →' : 'Esperando Facebook') }}</span>
                        @if($instagramLinked || $facebookLinked)</a>@else</div>@endif
                    </div>
                    <div class="actions"><button type="button" class="btn btn-secondary" data-next="1"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="button" class="btn btn-primary" data-next="3" {{ $anyAccountLinked ? '' : 'disabled' }}>Continuar <i class="fa-solid fa-arrow-right"></i></button></div>
                </div></article>

                <article class="slide" data-step="3"><div class="content">
                    <span class="eyebrow">La identidad de tu marca</span><h2>Crea <span>tu empresa.</span></h2>
                    <p class="lead">Usaremos esta información para reconocer la marca que publica y adaptar cada propuesta a su personalidad.</p>
                    @if(session('onboarding_success'))<div class="notice notice-success"><i class="fa-solid fa-circle-check"></i> {{ session('onboarding_success') }}</div>@endif
                    @if($empresa)
                        <div class="existing-company">
                            <span class="company-logo">@if($empresa->logo)<img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}">@else<i class="fa-solid fa-building"></i>@endif</span>
                            <div><h3>{{ $empresa->nombre_empresa }}</h3><p>{{ $empresa->tipo_empresa }} · Esta empresa ya está lista para trabajar con PRODOVI.</p></div>
                        </div>
                        <div class="actions"><button type="button" class="btn btn-secondary" data-next="2"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="button" class="btn btn-primary" data-next="4">Continuar <i class="fa-solid fa-arrow-right"></i></button></div>
                    @else
                        <form class="form-card" action="{{ route('clientes.onboarding.empresa') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-head"><span class="form-head-icon"><i class="fa-solid fa-building"></i></span><span><strong>Datos de la empresa</strong><small>Los campos con * son obligatorios</small></span></div>
                            <div class="form-body"><div class="form-grid">
                                <div class="field-full"><label for="nombre_empresa">Nombre de la empresa *</label><input id="nombre_empresa" name="nombre_empresa" type="text" required maxlength="255" value="{{ old('nombre_empresa', $suggestedCompanyName) }}" placeholder="Ej: Mi negocio">@error('nombre_empresa')<p class="field-error">{{ $message }}</p>@enderror</div>
                                <div><label for="tipo_empresa">Tipo de empresa *</label><input id="tipo_empresa" name="tipo_empresa" type="text" required maxlength="255" value="{{ old('tipo_empresa') }}" placeholder="Ej: Comercio, servicios...">@error('tipo_empresa')<p class="field-error">{{ $message }}</p>@enderror</div>
                                <div><label for="logo">Logo de la empresa</label><input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/gif">@error('logo')<p class="field-error">{{ $message }}</p>@enderror</div>
                                <div class="field-full"><label for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" placeholder="Cuéntanos qué hace tu empresa, sus servicios y sus objetivos...">{{ old('descripcion') }}</textarea>@error('descripcion')<p class="field-error">{{ $message }}</p>@enderror</div>
                            </div><div class="actions"><button type="button" class="btn btn-secondary" data-next="2"><i class="fa-solid fa-arrow-left"></i> Atrás</button><button type="submit" class="btn btn-primary">Crear y continuar <i class="fa-solid fa-arrow-right"></i></button></div></div>
                        </form>
                    @endif
                </div></article>

                <article class="slide" data-step="4"><div class="content finish">
                    <div class="finish-mark"><i class="fa-solid fa-check"></i></div><span class="eyebrow">Configuración completada</span>
                    <h2>Gracias por configurar <span>tu cuenta.</span></h2><p class="lead">Ya conocemos tu empresa y tenemos el canal necesario para comenzar a construir su presencia digital.</p>
                    <div class="summary"><span><i class="fa-solid fa-check"></i> Red social vinculada</span><span><i class="fa-solid fa-check"></i> Empresa registrada</span><span><i class="fa-solid fa-check"></i> Espacio preparado</span></div>
                    <form class="actions" action="{{ route('clientes.onboarding.complete') }}" method="POST">@csrf<button type="submit" class="btn btn-green">Empezar en PRODOVI <i class="fa-solid fa-arrow-right"></i></button></form>
                </div></article>
            </div>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const slides=Array.from(document.querySelectorAll('[data-step]'));
    const indicators=Array.from(document.querySelectorAll('[data-indicator]'));
    const progressBar=document.getElementById('progress-bar');
    const maximumStep={{ $anyAccountLinked ? ($empresa ? 4 : 3) : 2 }};
    let currentStep=Math.min({{ $initialStep }},maximumStep);
    function showStep(step){
        currentStep=Math.max(1,Math.min(Number(step),maximumStep));
        slides.forEach(slide=>slide.classList.toggle('is-active',Number(slide.dataset.step)===currentStep));
        indicators.forEach(indicator=>{const stepNumber=Number(indicator.dataset.indicator);indicator.classList.toggle('is-active',stepNumber===currentStep);indicator.classList.toggle('is-complete',stepNumber<currentStep);indicator.querySelector('.step-number').textContent=stepNumber<currentStep?'✓':stepNumber;});
        progressBar.style.width=(currentStep*25)+'%';
        window.scrollTo({top:0,behavior:'smooth'});
    }
    document.querySelectorAll('[data-next]').forEach(button=>button.addEventListener('click',()=>{if(!button.disabled)showStep(button.dataset.next);}));
    showStep(currentStep);
});
</script>
</body>
</html>
