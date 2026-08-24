{{-- Encabezado y métricas de Gestión de Usuarios. --}}
<header class="users-control-hero">
    <div class="users-control-copy">
        <span>Administración de clientes</span>
        <h1>Gestión de usuarios</h1>
    </div>
    <div class="users-quick-actions" aria-label="Acciones de usuarios">
        <a href="{{ route('administrador.usuarios.eliminados') }}">
            <i class="fas fa-trash-can"></i>Usuarios eliminados
        </a>
        <a class="primary" href="{{ route('administrador.usuarios.create') }}">
            <i class="fas fa-user-plus"></i>Agregar usuario
        </a>
    </div>
</header>

<div class="users-kpi-grid">
    <div class="users-kpi-card users-kpi-total">
        <div><span>Total usuarios</span><strong>{{ $users->total() }}</strong><small>Registrados en el sistema</small></div>
        <i class="fas fa-users"></i>
    </div>
    <div class="users-kpi-card users-kpi-active">
        <div><span>Usuarios activos</span><strong>{{ $users->filter(fn ($user) => $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty())->count() }}</strong><small>Con suscripción vigente</small></div>
        <i class="fas fa-user-check"></i>
    </div>
    <div class="users-kpi-card users-kpi-admin">
        <div><span>Administradores</span><strong>{{ $users->filter(fn ($user) => $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty())->count() }}</strong><small>Con permisos administrativos</small></div>
        <i class="fas fa-user-shield"></i>
    </div>
    <div class="users-kpi-card users-kpi-no-plan">
        <div><span>Sin plan</span><strong>{{ $users->filter(fn ($user) => $user->suscripciones->isEmpty() && $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isEmpty())->count() }}</strong><small>Requieren seguimiento</small></div>
        <i class="fas fa-user-slash"></i>
    </div>
</div>

<style>
    .users-control-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 100%;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        padding: 30px 48px;
        color: #fff;
        background:
            linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, #4f46e5 25%, transparent 25%),
            linear-gradient(45deg, #4f46e5 25%, transparent 25%),
            linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
        background-color: #1d4ed8;
        background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%;
    }
    .users-control-hero::after {
        content: '';
        position: absolute;
        z-index: -1;
        inset: 0;
        background: linear-gradient(rgba(15,23,42,.22), rgba(15,23,42,.22)), radial-gradient(circle at 0 0, rgba(255,255,255,.2), transparent 50%), radial-gradient(circle at 100% 100%, rgba(255,255,255,.16), transparent 50%);
    }
    .users-control-copy span { display: block; margin-bottom: 7px; color: #dbeafe; font-size: .68rem; font-weight: 900; letter-spacing: .15em; text-transform: uppercase; }
    .users-control-copy h1 { margin: 0; color: #fff; font-size: clamp(1.55rem,3vw,2.25rem); font-weight: 900; letter-spacing: -.04em; }
    .users-quick-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
    .users-quick-actions a { display: inline-flex; align-items: center; gap: 9px; padding: 11px 14px; border: 1px solid rgba(255,255,255,.2); border-radius: .65rem; background: rgba(255,255,255,.12); color: #fff; font-size: .72rem; font-weight: 900; transition: .18s; }
    .users-quick-actions a.primary { border-color: #fff; background: #fff; color: #4f46e5; }
    .users-quick-actions a:hover { transform: translateY(-2px); border-color: #fff; background: #fff; color: #4f46e5; box-shadow: 0 8px 20px rgba(31,41,55,.16); }
    .users-quick-actions i { font-size: .85rem; }
    .users-kpi-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 16px; margin: 24px; }
    .users-kpi-card { --kpi-accent:#117e8c; --kpi-soft:#e8f5f6; --kpi-rgb:17,126,140; position: relative; isolation: isolate; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 18px; min-height: 132px; padding: 21px; border: 1px solid rgba(var(--kpi-rgb),.22); border-radius: 1rem; background: linear-gradient(135deg,#fff 35%,var(--kpi-soft) 100%); box-shadow: inset 0 4px 0 var(--kpi-accent),0 10px 24px rgba(45,66,34,.09); transition: transform .22s ease,box-shadow .22s ease,border-color .22s ease; }
    .users-kpi-card::before { content:''; position:absolute; z-index:-1; top:-42px; right:-34px; width:125px; height:125px; border:22px solid rgba(var(--kpi-rgb),.09); border-radius:50%; }
    .users-kpi-card::after { content:''; position:absolute; z-index:-1; right:13px; bottom:8px; width:88px; height:45px; opacity:.22; background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px); background-size:9px 9px; transform:rotate(-5deg); }
    .users-kpi-card:hover { transform:translateY(-5px); border-color:rgba(var(--kpi-rgb),.38); box-shadow:inset 0 4px 0 var(--kpi-accent),0 17px 32px rgba(var(--kpi-rgb),.16); }
    .users-kpi-card > div,.users-kpi-card > i { position:relative; z-index:1; }.users-kpi-card span,.users-kpi-card small { display:block; }.users-kpi-card span { color:#596170; font-size:.7rem; font-weight:900; letter-spacing:.025em; text-transform:uppercase; }.users-kpi-card strong { display:block; margin-top:9px; color:#263024; font-size:1.85rem; font-weight:900; line-height:1; }.users-kpi-card small { margin-top:8px; color:#7f8878; font-size:.62rem; font-weight:600; }
    .users-kpi-card > i { width:52px; height:52px; display:grid; place-items:center; flex:0 0 auto; border:1px solid rgba(255,255,255,.55); border-radius:14px; background:var(--kpi-accent); color:#fff; font-size:1.18rem; box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27),inset 0 1px 0 rgba(255,255,255,.28); transition:transform .22s ease; }.users-kpi-card:hover > i { transform:rotate(-6deg) scale(1.06); }
    .users-kpi-total { --kpi-accent:#117e8c; --kpi-soft:#e6f4f5; --kpi-rgb:17,126,140; }.users-kpi-active { --kpi-accent:#7da533; --kpi-soft:#f0f6e7; --kpi-rgb:125,165,51; }.users-kpi-admin { --kpi-accent:#e3a122; --kpi-soft:#fff6df; --kpi-rgb:227,161,34; }.users-kpi-no-plan { --kpi-accent:#e37225; --kpi-soft:#fff0e6; --kpi-rgb:227,114,37; }
    @media(max-width: 980px) { .users-control-hero { min-height: 205px; flex-direction: column; justify-content: center; text-align: center; }.users-quick-actions { justify-content: center; }.users-kpi-grid { grid-template-columns: repeat(2,1fr); } }
    @media(max-width: 640px) { .users-control-hero { padding: 24px 20px; }.users-quick-actions { width: 100%; }.users-quick-actions a { flex: 1; justify-content: center; }.users-kpi-grid { grid-template-columns: 1fr; margin-right: 12px; margin-left: 12px; } }
</style>
