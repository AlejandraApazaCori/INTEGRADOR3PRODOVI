@php
    $navbarEmpresas = auth()->user()->empresas()
        ->orderBy('nombre_empresa')
        ->get(['id', 'nombre_empresa']);
    $navbarFeedbackService = app(\App\Services\CampaignFeedbackService::class);
    $navbarRouteCampaign = request()->route('campania');
    if (! $navbarRouteCampaign instanceof \App\Models\Campania) {
        $navbarRouteCampaign = is_numeric($navbarRouteCampaign) ? (int) $navbarRouteCampaign : null;
    }
    $navbarCompanyId = request()->integer('empresa') ?: null;
    $navbarMessageCampaign = $navbarFeedbackService->clientCampaign(auth()->user(), $navbarCompanyId, $navbarRouteCampaign);
    $navbarUnreadMessages = $navbarMessageCampaign
        ? $navbarFeedbackService->unreadCount($navbarMessageCampaign, auth()->user())
        : 0;
    $navbarUnreadUrl = route('clientes.mensajes.no-leidos', array_filter([
        'empresa' => $navbarCompanyId,
        'campania' => $navbarMessageCampaign?->id,
    ]));
    $navbarNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications')
        ? auth()->user()->notifications()->latest()->take(5)->get()
        : collect();
    $navbarUnreadNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications')
        ? auth()->user()->unreadNotifications()->count()
        : 0;
@endphp

<header class="client-navbar" id="client-navbar">
    <div class="client-navbar-inner">
        <a href="{{ route('clientes.dashboard') }}" class="client-brand" aria-label="Ir al dashboard"><img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI"></a>
        <button type="button" class="client-mobile-toggle" id="client-mobile-toggle" aria-label="Abrir navegación" aria-expanded="false"><i class="fas fa-bars"></i></button>

        <nav class="client-nav-links" aria-label="Navegación principal">
            <a href="{{ route('clientes.dashboard') }}" class="client-nav-link {{ request()->routeIs('clientes.dashboard') ? 'is-active' : '' }}"><i class="fas fa-table-columns"></i><span>Dashboard</span></a>
            <div class="client-nav-dropdown">
                <button type="button" class="client-nav-link {{ request()->routeIs('clientes.historial.pagos', 'clientes.planes.comprar') ? 'is-active' : '' }}" data-nav-dropdown aria-expanded="false"><i class="fas fa-wallet"></i><span>Pagos</span><i class="fas fa-chevron-down client-chevron"></i></button>
                <div class="client-nav-menu">
                    <a href="{{ route('clientes.historial.pagos') }}"><i class="fas fa-clock-rotate-left"></i> Historial de pagos</a>
                    <a href="{{ route('clientes.planes.comprar') }}"><i class="fas fa-cart-plus"></i> Comprar plan</a>
                </div>
            </div>
            <a href="{{ route('clientes.analiticas') }}" class="client-nav-link {{ request()->routeIs('clientes.analiticas') ? 'is-active' : '' }}"><i class="fas fa-chart-line"></i><span>Analíticas</span></a>
            <a href="{{ route('clientes.recursos') }}" class="client-nav-link {{ request()->routeIs('clientes.recursos*') ? 'is-active' : '' }}"><i class="fas fa-folder-open"></i><span>Recursos</span></a>
            @if($navbarEmpresas->isNotEmpty())
                <div class="client-nav-dropdown">
                    <button type="button" class="client-nav-link {{ request()->routeIs('empresas.*') ? 'is-active' : '' }}" data-nav-dropdown aria-expanded="false"><i class="fas fa-building"></i><span>Empresa</span><i class="fas fa-chevron-down client-chevron"></i></button>
                    <div class="client-nav-menu client-company-menu">
                        @foreach($navbarEmpresas as $navbarEmpresa)
                            <a href="{{ route('empresas.show', $navbarEmpresa->id) }}" class="{{ request()->routeIs('empresas.show') && (int) request()->route('id') === (int) $navbarEmpresa->id ? 'is-current' : '' }}"><i class="fas fa-building-circle-check"></i><span>{{ $navbarEmpresa->nombre_empresa }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif
            <a href="{{ route('clientes.micuenta') }}" class="client-nav-link {{ request()->routeIs('clientes.micuenta') ? 'is-active' : '' }}"><i class="fas fa-user-gear"></i><span>Mi cuenta</span></a>
        </nav>

        <div class="client-navbar-actions">
            <button type="button" class="theme-toggle-button" id="theme-toggle-button" aria-label="Cambiar a modo oscuro" title="Cambiar tema">
                <i class="fas fa-moon"></i>
            </button>
            @if($navbarMessageCampaign)
                <a href="{{ route('clientes.campanias.feedback', $navbarMessageCampaign) }}" class="client-message-button" data-client-message-button data-unread-url="{{ $navbarUnreadUrl }}" aria-label="Mensajes con el equipo{{ $navbarUnreadMessages ? ': '.$navbarUnreadMessages.' sin leer' : '' }}" title="Mensajes con el equipo">
                    <i class="fas fa-comment-dots"></i>
                    <span class="client-message-badge" data-client-message-badge {{ $navbarUnreadMessages > 0 ? '' : 'hidden' }}>{{ $navbarUnreadMessages }}</span>
                </a>
            @endif
            <div class="client-notifications">
                <button type="button" class="notification-button" id="notification-button" aria-label="Notificaciones{{ $navbarUnreadNotifications ? ': '.$navbarUnreadNotifications.' sin leer' : '' }}" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    @if($navbarUnreadNotifications)
                        <span class="notification-badge">{{ $navbarUnreadNotifications > 99 ? '99+' : $navbarUnreadNotifications }}</span>
                    @endif
                </button>
                <div class="notification-menu">
                    <div class="notification-head"><strong>Notificaciones</strong><span>Tu actividad reciente</span></div>
                    @forelse($navbarNotifications as $navbarNotification)
                        <a href="{{ route('clientes.notificaciones.show', $navbarNotification->id) }}" class="notification-item {{ $navbarNotification->read_at ? '' : 'is-unread' }}">
                            <span><i class="fas {{ $navbarNotification->data['icon'] ?? 'fa-bell' }}"></i></span>
                            <span><strong>{{ $navbarNotification->data['title'] ?? 'Notificación' }}</strong><small>{{ $navbarNotification->data['message'] ?? '' }}</small><time>{{ $navbarNotification->created_at->diffForHumans() }}</time></span>
                        </a>
                    @empty
                        <div class="notification-empty"><i class="far fa-bell"></i><span>No tienes notificaciones nuevas.</span></div>
                    @endforelse
                </div>
            </div>

            <div class="client-user-dropdown">
                <button type="button" class="client-user-chip" id="client-user-chip" aria-expanded="false">
                    <span class="client-user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="client-user-copy"><strong>{{ auth()->user()->name }}</strong><small>Cliente PRODOVI</small></span>
                    <i class="fas fa-chevron-down client-user-chevron"></i>
                </button>
                <div class="client-user-menu">
                    <div class="client-user-menu-head"><span class="client-user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div>
                    <a href="{{ route('clientes.micuenta') }}"><i class="fas fa-user"></i> Mi perfil</a>
                    <button type="button" id="client-logout-button"><i class="fas fa-arrow-right-from-bracket"></i> Cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="client-confirmation" id="client-logout-confirmation" aria-hidden="true">
    <div class="client-confirmation-backdrop"></div>
    <div class="client-confirmation-box">
        <span class="client-confirmation-icon"><i class="fas fa-arrow-right-from-bracket"></i></span>
        <h3>¿Cerrar sesión?</h3><p>Tendrás que iniciar sesión nuevamente para acceder a tu panel.</p>
        <div><button type="button" id="client-cancel-logout">Cancelar</button><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sí, cerrar sesión</button></form></div>
    </div>
</div>

<style>
    :root{--client-purple:#5B2B76;--client-orange:#EF6C22;--client-turquoise:#117E8C}
    html{scrollbar-width:thin;scrollbar-color:var(--client-turquoise) #ece8ee}
    *::-webkit-scrollbar{width:10px;height:10px}
    *::-webkit-scrollbar-track{background:#ece8ee}
    *::-webkit-scrollbar-thumb{border:2px solid #ece8ee;border-radius:0;background:var(--client-turquoise)}
    *::-webkit-scrollbar-thumb:hover{background:#0d6974}
    body{margin:0!important;margin-left:0!important;background:#fff;font-family:Inter,'Segoe UI',sans-serif}
    .client-navbar{position:sticky;z-index:1200;top:0;width:100%;background:#242426;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.16)}
    .client-navbar-inner{width:100%;min-height:72px;display:flex;align-items:center;gap:28px;padding:0 28px}.client-brand{display:flex;flex:0 0 auto}.client-brand img{width:136px;height:auto}
    .client-nav-links{display:flex;align-items:stretch;align-self:stretch;gap:3px;flex:1}.client-nav-link{position:relative;display:flex;align-items:center;gap:8px;padding:0 15px;border:0;background:transparent;color:#aaa5ad;font-size:.82rem;font-weight:800;text-decoration:none;cursor:pointer;transition:.2s}.client-nav-link:hover{background:transparent;color:#fff}.client-nav-link.is-active{background:linear-gradient(to top,rgba(17,126,140,.14) 0%,rgba(17,126,140,.055) 28%,transparent 58%);color:#fff}.client-nav-link.is-active::after{content:'';position:absolute;right:15px;bottom:0;left:15px;height:3px;background:var(--client-turquoise);box-shadow:0 -5px 14px rgba(17,126,140,.34)}.client-nav-link>i:first-child{color:var(--client-orange)}.client-chevron,.client-user-chevron{font-size:.62rem;transition:.2s}.is-open .client-chevron,.is-open .client-user-chevron{transform:rotate(180deg)}
    .client-nav-dropdown,.client-notifications,.client-user-dropdown{position:relative;display:flex}.client-nav-menu,.notification-menu,.client-user-menu{position:absolute;z-index:20;top:calc(100% + 10px);display:none;border:1px solid #ded7e1;border-radius:3px;background:#fff;color:#4c4350;box-shadow:0 18px 42px rgba(28,19,32,.2)}.is-open>.client-nav-menu,.is-open>.notification-menu,.is-open>.client-user-menu{display:block;animation:clientDrop .18s ease both}@keyframes clientDrop{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
    .client-nav-menu{left:0;min-width:210px;padding:7px}.client-nav-menu a{display:flex;align-items:center;gap:9px;padding:11px 12px;color:#514557;text-decoration:none;font-size:.8rem;font-weight:700}.client-nav-menu a:hover,.client-nav-menu a.is-current{background:#f4edf7;color:var(--client-purple)}.client-nav-menu i{color:var(--client-orange)}.client-company-menu{width:max-content;min-width:230px;max-width:320px}.client-company-menu a span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .client-navbar-actions{display:flex;align-items:center;gap:10px;flex:0 0 auto}.notification-button,.theme-toggle-button{position:relative;width:42px;height:42px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.16);border-radius:3px;background:rgba(255,255,255,.07);color:#fff;cursor:pointer;transition:.2s}.notification-button:hover,.client-notifications.is-open .notification-button{border-color:var(--client-orange);background:var(--client-orange)}.theme-toggle-button:hover{border-color:var(--client-turquoise);background:var(--client-turquoise)}.theme-toggle-button .fa-sun{color:#ffc14c}.notification-badge{position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;display:grid;place-items:center;padding:0 5px;border:2px solid #242426;border-radius:999px;background:#ef4444;color:#fff;font-size:.56rem;font-weight:900}
    .notification-menu{right:0;width:340px;max-height:430px;overflow-y:auto}.notification-head{padding:16px 18px;border-bottom:1px solid #e8e2ea}.notification-head strong,.notification-head span{display:block}.notification-head strong{color:#2e2731}.notification-head span{margin-top:2px;color:#918694;font-size:.7rem}.notification-item{display:grid;grid-template-columns:38px minmax(0,1fr);gap:11px;padding:14px 16px;border-bottom:1px solid #eee9f0;color:#514557;text-decoration:none}.notification-item:hover{background:#f8f4fa}.notification-item.is-unread{border-left:4px solid var(--client-orange);background:#fff9f3}.notification-item>span:first-child{width:38px;height:38px;display:grid;place-items:center;border-radius:8px;background:rgba(17,126,140,.11);color:var(--client-turquoise)}.notification-item strong,.notification-item small,.notification-item time{display:block}.notification-item strong{color:#302834;font-size:.76rem}.notification-item small{margin-top:4px;color:#766d79;font-size:.66rem;line-height:1.4}.notification-item time{margin-top:6px;color:#a095a4;font-size:.58rem}.notification-empty{display:flex;align-items:center;gap:11px;padding:22px 18px;color:#837888;font-size:.78rem}.notification-empty i{width:34px;height:34px;display:grid;place-items:center;border-radius:2px;background:rgba(17,126,140,.1);color:var(--client-turquoise)}
    .client-user-chip{display:flex;align-items:center;gap:10px;min-width:210px;padding:7px 10px;border:1px solid rgba(255,255,255,.14);border-radius:3px;background:rgba(255,255,255,.07);color:#fff;text-align:left;cursor:pointer}.client-user-avatar{width:34px;height:34px;display:grid;place-items:center;flex:0 0 auto;border-radius:2px;background:var(--client-purple);font-size:.76rem;font-weight:900}.client-user-copy{min-width:0;flex:1}.client-user-copy strong,.client-user-copy small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.client-user-copy strong{font-size:.78rem}.client-user-copy small{margin-top:2px;color:#aaa5ad;font-size:.65rem}
    .client-user-menu{right:0;width:265px;overflow:hidden}.client-user-menu-head{display:flex;align-items:center;gap:11px;padding:16px;border-bottom:1px solid #e8e2ea;background:#f7f5f8}.client-user-menu-head span:last-child{min-width:0}.client-user-menu-head strong,.client-user-menu-head small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.client-user-menu-head strong{color:#302834;font-size:.8rem}.client-user-menu-head small{color:#887d8c;font-size:.68rem}.client-user-menu>a,.client-user-menu>button{width:100%;display:flex;align-items:center;gap:10px;padding:12px 16px;border:0;background:#fff;color:#514557;text-align:left;text-decoration:none;font-size:.78rem;font-weight:700;cursor:pointer}.client-user-menu>a:hover,.client-user-menu>button:hover{background:#f4edf7;color:var(--client-purple)}.client-user-menu i{width:18px;color:var(--client-orange)}
    .client-user-chip .client-user-avatar{border:1px solid rgba(255,255,255,.3);background:var(--client-orange);color:#fff;box-shadow:0 0 0 3px rgba(239,108,34,.12)}
    .client-user-menu{border-color:#444047;background:#242426;color:#fff}.client-user-menu-head{border-bottom-color:#444047;background:#2d2d30}.client-user-menu-head .client-user-avatar{border:1px solid rgba(255,255,255,.28);background:var(--client-purple);color:#fff}.client-user-menu-head strong{color:#fff}.client-user-menu-head small{color:#aaa5ad}.client-user-menu>a,.client-user-menu>button{background:#242426;color:#ddd8df}.client-user-menu>a:hover,.client-user-menu>button:hover{background:rgba(17,126,140,.16);color:#fff}
    .client-mobile-toggle{display:none;width:42px;height:42px;margin-left:auto;border:1px solid rgba(255,255,255,.16);border-radius:3px;background:rgba(255,255,255,.07);color:#fff}
    .client-confirmation{position:fixed;z-index:2147483001;inset:0;display:none;align-items:center;justify-content:center;padding:20px}.client-confirmation.is-open{display:flex}.client-confirmation-backdrop{position:absolute;inset:0;background:rgba(15,11,17,.75);backdrop-filter:blur(5px)}.client-confirmation-box{position:relative;width:min(400px,100%);padding:28px;border-top:5px solid var(--client-orange);border-radius:3px;background:#fff;text-align:center;box-shadow:0 28px 80px rgba(0,0,0,.3)}.client-confirmation-icon{width:48px;height:48px;display:grid;place-items:center;margin:0 auto 15px;border-radius:2px;background:rgba(91,43,118,.1);color:var(--client-purple)}.client-confirmation-box h3{margin:0;color:#2d2630;font-size:1.25rem;font-weight:900}.client-confirmation-box p{margin:8px 0 20px;color:#7e7382;font-size:.8rem}.client-confirmation-box>div:last-child{display:flex;justify-content:center;gap:9px}.client-confirmation-box button{padding:10px 15px;border:1px solid #d8cedc;border-radius:3px;background:#fff;color:#625767;font-size:.78rem;font-weight:800}.client-confirmation-box form button{border-color:var(--client-purple);background:var(--client-purple);color:#fff}
    html[data-client-theme="dark"]{color-scheme:dark;scrollbar-color:var(--client-turquoise) #29252c}
    html[data-client-theme="dark"] *::-webkit-scrollbar-track{background:#29252c}
    html[data-client-theme="dark"] *::-webkit-scrollbar-thumb{border-color:#29252c}
    html[data-client-theme="dark"] body,html[data-client-theme="dark"] .client-main{background:#141216!important;color:#e9e5eb}
    html[data-client-theme="dark"] .client-main .bg-white{background-color:#1e1b21!important}
    html[data-client-theme="dark"] .client-main .bg-gray-50,html[data-client-theme="dark"] .client-main .bg-gray-100{background-color:#29252c!important}
    html[data-client-theme="dark"] .client-main .text-gray-900,html[data-client-theme="dark"] .client-main .text-gray-800{color:#f1edf3!important}
    html[data-client-theme="dark"] .client-main .text-gray-700,html[data-client-theme="dark"] .client-main .text-gray-600,html[data-client-theme="dark"] .client-main .text-gray-500{color:#b4abb8!important}
    html[data-client-theme="dark"] .client-main .border-gray-100,html[data-client-theme="dark"] .client-main .border-gray-200,html[data-client-theme="dark"] .client-main .border-indigo-100{border-color:#3b3540!important}
    html[data-client-theme="dark"] #client-dashboard{background:#141216!important}
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics,html[data-client-theme="dark"] #client-dashboard .service-panel,html[data-client-theme="dark"] #client-dashboard .plan-overview,html[data-client-theme="dark"] #client-dashboard .company-panel,html[data-client-theme="dark"] #client-dashboard .feature-item{background:#1e1b21!important;border-color:#3b3540!important}
    html[data-client-theme="dark"] #client-dashboard .section-heading,html[data-client-theme="dark"] #client-dashboard .company-summary{background:#29252c!important;border-color:#413a45!important}
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article{border-color:#3b3540}
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics small,html[data-client-theme="dark"] #client-dashboard .dashboard-metrics strong{color:#d0c8d3}
    html[data-client-theme="dark"] #client-dashboard .feature-item:hover{background:#27232a!important}
    html[data-client-theme="dark"] .notification-menu{border-color:#444047;background:#242426;color:#ddd8df}html[data-client-theme="dark"] .notification-item{border-color:#444047;color:#ddd8df}html[data-client-theme="dark"] .notification-item:hover,html[data-client-theme="dark"] .notification-item.is-unread{background:#302a33}html[data-client-theme="dark"] .notification-item strong{color:#fff}html[data-client-theme="dark"] .notification-item small{color:#bbb2be}
    html[data-client-theme="dark"] .notification-head{border-color:#444047}html[data-client-theme="dark"] .notification-head strong{color:#fff}
    html[data-client-theme="dark"] .client-confirmation-box{background:#242426}html[data-client-theme="dark"] .client-confirmation-box h3{color:#fff}html[data-client-theme="dark"] .client-confirmation-box p{color:#aaa5ad}html[data-client-theme="dark"] .client-confirmation-box>div button{border-color:#4b444f;background:#302c33;color:#ddd8df}
    html[data-client-theme="dark"] #plan-modal .inline-block,html[data-client-theme="dark"] #plan-modal .bg-white{background:#1e1b21!important;color:#e9e5eb}
    html[data-client-theme="dark"] .mq-dialog,html[data-client-theme="dark"] .mq-slides,html[data-client-theme="dark"] .mq-slide,html[data-client-theme="dark"] .mq-question textarea,html[data-client-theme="dark"] .mq-select-trigger,html[data-client-theme="dark"] .mq-select-menu{background:#1e1b21!important;color:#eeeaf0!important;border-color:#46404a!important}
    @media(max-width:900px){.client-navbar-inner{flex-wrap:wrap;gap:10px;padding:12px 16px}.client-mobile-toggle{display:grid;place-items:center}.client-nav-links{order:3;width:100%;display:none;flex-direction:column;align-items:stretch;padding-top:8px}.client-navbar.is-mobile-open .client-nav-links{display:flex}.client-nav-link{min-height:44px;padding:0 12px}.client-nav-link.is-active::after{top:8px;right:auto;bottom:8px;left:0;width:3px;height:auto}.client-nav-dropdown{display:block}.client-nav-dropdown .client-nav-link{width:100%}.client-nav-menu{position:static;margin:4px 0 0 14px;box-shadow:none}.client-navbar-actions{margin-left:auto}.client-user-chip{min-width:0}.client-user-copy{display:none}.client-user-menu,.notification-menu{position:fixed;top:76px;right:12px}.client-brand img{width:120px}}
    @media(max-width:520px){.client-navbar-inner{padding-inline:11px}.client-navbar-actions{gap:6px}.client-user-chip{padding:4px}.client-user-chevron{display:none}.notification-menu,.client-user-menu{right:8px;left:8px;width:auto}}
    .client-message-button{position:relative;width:42px;height:42px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.16);border-radius:3px;background:rgba(255,255,255,.07);color:#fff;text-decoration:none;transition:.2s}.client-message-button:hover,.client-message-button:focus{border-color:var(--client-turquoise);background:var(--client-turquoise);color:#fff}.client-message-badge{position:absolute;top:-6px;right:-6px;min-width:19px;height:19px;display:grid;place-items:center;padding:0 5px;border:2px solid #242426;border-radius:999px;background:#ef4444;color:#fff;font-size:.58rem;font-weight:900;line-height:1}.client-message-badge[hidden]{display:none!important}
</style>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const navbar=document.getElementById('client-navbar'),mobile=document.getElementById('client-mobile-toggle');
    const themeButton=document.getElementById('theme-toggle-button');
    const syncThemeButton=()=>{const dark=document.documentElement.dataset.clientTheme==='dark';themeButton.innerHTML=`<i class="fas ${dark?'fa-sun':'fa-moon'}"></i>`;themeButton.setAttribute('aria-label',dark?'Cambiar a modo claro':'Cambiar a modo oscuro');themeButton.title=dark?'Usar modo claro':'Usar modo oscuro'};
    themeButton?.addEventListener('click',()=>{const next=document.documentElement.dataset.clientTheme==='dark'?'light':'dark';document.documentElement.dataset.clientTheme=next;localStorage.setItem('clientTheme',next);syncThemeButton()});
    syncThemeButton();
    const notifications=document.querySelector('.client-notifications'),notificationButton=document.getElementById('notification-button');
    const user=document.querySelector('.client-user-dropdown'),userButton=document.getElementById('client-user-chip');
    const navDropdowns=Array.from(document.querySelectorAll('.client-nav-dropdown'));
    const dropdownPairs=[[notifications,notificationButton],[user,userButton],...navDropdowns.map(dropdown=>[dropdown,dropdown.querySelector('[data-nav-dropdown]')])];
    const closeMenus=except=>dropdownPairs.forEach(([menu,button])=>{if(menu!==except){menu?.classList.remove('is-open');button?.setAttribute('aria-expanded','false')}});
    mobile?.addEventListener('click',()=>{const open=navbar.classList.toggle('is-mobile-open');mobile.setAttribute('aria-expanded',String(open))});
    dropdownPairs.forEach(([menu,button])=>button?.addEventListener('click',event=>{event.stopPropagation();const open=!menu.classList.contains('is-open');closeMenus(menu);menu.classList.toggle('is-open',open);button.setAttribute('aria-expanded',String(open))}));
    document.addEventListener('click',()=>closeMenus());
    const confirmation=document.getElementById('client-logout-confirmation'),cancel=document.getElementById('client-cancel-logout');
    document.getElementById('client-logout-button')?.addEventListener('click',()=>{confirmation.classList.add('is-open');confirmation.setAttribute('aria-hidden','false')});
    cancel?.addEventListener('click',()=>{confirmation.classList.remove('is-open');confirmation.setAttribute('aria-hidden','true')});
    confirmation?.querySelector('.client-confirmation-backdrop')?.addEventListener('click',()=>cancel.click());
    const messageButton=document.querySelector('[data-client-message-button]'),messageBadge=document.querySelector('[data-client-message-badge]');
    const syncUnreadMessages=async()=>{
        if(!messageButton||!messageBadge||document.visibilityState!=='visible')return;
        try{
            const response=await fetch(messageButton.dataset.unreadUrl,{headers:{Accept:'application/json'}});
            if(!response.ok)return;
            const data=await response.json(),count=Number(data.count)||0;
            messageBadge.textContent=count>99?'99+':String(count);
            messageBadge.hidden=count===0;
            messageButton.setAttribute('aria-label',count?`Mensajes con el equipo: ${count} sin leer`:'Mensajes con el equipo');
            if(data.url)messageButton.href=data.url;
        }catch(error){}
    };
    window.setInterval(syncUnreadMessages,10000);
});
</script>
