<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('imagenes/iconoweb.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-notification-stack {
            position: fixed;
            z-index: 10000;
            top: 76px;
            right: 20px;
            width: min(370px, calc(100% - 32px));
            display: grid;
            gap: 9px;
        }
        .dashboard-notification-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2px 2px;
            color: #374151;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .dashboard-notification-toast {
            position: relative;
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr) 28px;
            align-items: start;
            gap: 11px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #ef6c22;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(15,23,42,.2);
            animation: adminNotificationIn .28s ease both;
        }
        .dashboard-notification-toast.is-payment-complete { border-left-color: #16a34a; }
        .dashboard-notification-toast.is-campaign { border-left-color: #7c3aed; }
        .dashboard-notification-toast.is-task { border-left-color: #2563eb; }
        .dashboard-toast-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 7px;
            background: #fff3e8;
            color: #d95d16;
        }
        .is-payment-complete .dashboard-toast-icon { background: #ecfdf3; color: #15803d; }
        .is-campaign .dashboard-toast-icon { background: #f4f0ff; color: #7c3aed; }
        .is-task .dashboard-toast-icon { background: #eff6ff; color: #2563eb; }
        .dashboard-toast-copy { min-width: 0; color: #111827; text-decoration: none; }
        .dashboard-toast-copy strong { display: block; padding-right: 4px; font-size: .79rem; }
        .dashboard-toast-copy span { display: block; margin-top: 3px; color: #6b7280; font-size: .7rem; line-height: 1.4; }
        .dashboard-toast-copy time { display: block; margin-top: 5px; color: #9ca3af; font-size: .63rem; }
        .dashboard-toast-close {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #9ca3af;
            cursor: pointer;
        }
        .dashboard-toast-close:hover { background: #f3f4f6; color: #374151; }
        .dashboard-notification-toast.is-leaving { opacity: 0; transform: translateX(24px); transition: opacity .2s ease, transform .2s ease; }
        @keyframes adminNotificationIn { from { opacity: 0; transform: translateX(28px); } to { opacity: 1; transform: none; } }
        @media (max-width: 640px) { .dashboard-notification-stack { top: 68px; right: 16px; } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 ">
    @include('componentes.navbar-admin')

    <!-- Barra superior -->
    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle-btn" onclick="toggleSidebar()" title="Mostrar/ocultar menú lateral">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="topbar-right">
            <div class="topbar-notifications-container">
                <button class="topbar-notification-btn" title="Notificaciones" id="notificationBtn" onclick="toggleNotificationDropdown(event)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="topbar-notification-badge" id="notifBadge"
                        style="{{ ($notificationCount ?? 0) > 0 ? '' : 'display:none' }}">
                        {{ $notificationCount ?? 0 }}
                    </span>
                </button>

                <!-- Dropdown de notificaciones -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <h3 class="text-sm font-bold">Notificaciones</h3>
                        <span class="text-xs text-gray-500" id="notifCountLabel">
                            {{ $notificationCount ?? 0 }} sin leer
                        </span>
                    </div>

                    <div class="notification-dropdown-body" id="notifBody">

                        {{-- ── SECCIÓN: NO VISTAS ───────────────────────────────── --}}
                        @php
                            $hayNoVistas = (($pagosNoVistos ?? collect())->count()
                                + ($campaniasNoVistas ?? collect())->count()
                                + ($tareasNoVistas ?? collect())->count()) > 0;
                        @endphp

                        @if($hayNoVistas)
                            <div class="notification-group" id="grupoNoVistas">
                                <div class="notification-group-title text-yellow-700 bg-yellow-50 px-3 py-1">
                                    🔔 Nuevas
                                </div>

                                {{-- Pagos no vistos --}}
                                @foreach($pagosNoVistos ?? [] as $pago)
                                    @php
                                        $esCodigoFisico = $pago->metodo === 'fisico' && $pago->estado === 'pendiente';
                                    @endphp
                                    <a href="{{ $esCodigoFisico ? route('administrador.pagos.pendientes-fisicos') : route('administrador.pagos.realizados') }}" class="notification-item font-semibold">
                                        <div class="notification-item-icon {{ $esCodigoFisico ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-600' }}">
                                            <i class="fas {{ $esCodigoFisico ? 'fa-receipt' : 'fa-circle-check' }}"></i>
                                        </div>
                                        <div class="notification-item-content">
                                            <p class="text-xs font-semibold">{{ $pago->usuario->name }}</p>
                                            <p class="text-[10px] text-gray-500">
                                                @if($esCodigoFisico)
                                                    Generó un código de pago físico para el plan {{ $pago->plan->nombre ?? '—' }}
                                                    @if($pago->codigoPago)
                                                        · {{ $pago->codigoPago->codigo }}
                                                    @endif
                                                @else
                                                    Realizó un pago — plan {{ $pago->plan->nombre ?? '—' }}
                                                @endif
                                            </p>
                                            <p class="text-[10px] text-gray-400">{{ $pago->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @endforeach

                                {{-- Campañas no vistas --}}
                                @foreach($campaniasNoVistas ?? [] as $campania)
                                    <a href="{{ route('administrador.campañas.show', $campania->id) }}" class="notification-item font-semibold">
                                        <div class="notification-item-icon bg-purple-100 text-purple-600">📢</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs font-semibold">Nueva campaña: {{ $campania->nombre }}</p>
                                            <p class="text-[10px] text-gray-500">
                                                {{ $campania->cliente ? 'Cliente: ' . $campania->cliente->name : 'Sin cliente asignado' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400">{{ $campania->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @endforeach

                                {{-- Tareas no vistas --}}
                                @foreach($tareasNoVistas ?? [] as $archivo)
                                    <a href="{{ route('administrador.tareas.show', $archivo->tarea_id) }}" class="notification-item font-semibold">
                                        <div class="notification-item-icon bg-blue-100 text-blue-600">📎</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs font-semibold">{{ $archivo->user->name }}</p>
                                            <p class="text-[10px] text-gray-500">Subió un archivo a: {{ $archivo->tarea->nombre ?? '—' }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $archivo->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center" id="sinPendientes">
                                <p class="text-sm text-gray-400">Sin notificaciones nuevas</p>
                            </div>
                        @endif

                        {{-- ── SECCIÓN: YA VISTAS ───────────────────────────────── --}}
                        @php
                            $hayVistas = (($pagosVistos ?? collect())->count()
                                + ($campaniasVistas ?? collect())->count()
                                + ($tareasVistas ?? collect())->count()) > 0;
                        @endphp

                        @if($hayVistas)
                            <div class="notification-group border-t border-gray-100 mt-1 pt-1">
                                <div class="notification-group-title text-gray-400 bg-gray-50 px-3 py-1">
                                    ✓ Ya vistas
                                </div>

                                @foreach($pagosVistos ?? [] as $pago)
                                    @php
                                        $esCodigoFisico = $pago->metodo === 'fisico' && $pago->estado === 'pendiente';
                                    @endphp
                                    <a href="{{ $esCodigoFisico ? route('administrador.pagos.pendientes-fisicos') : route('administrador.pagos.realizados') }}" class="notification-item opacity-60">
                                        <div class="notification-item-icon bg-gray-100 text-gray-400"><i class="fas {{ $esCodigoFisico ? 'fa-receipt' : 'fa-circle-check' }}"></i></div>
                                        <div class="notification-item-content">
                                            <p class="text-xs">{{ $pago->usuario->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $esCodigoFisico ? 'Código físico generado' : 'Pago realizado' }} — {{ $pago->plan->nombre ?? '—' }}</p>
                                        </div>
                                    </a>
                                @endforeach

                                @foreach($campaniasVistas ?? [] as $campania)
                                    <a href="{{ route('administrador.campañas.show', $campania->id) }}" class="notification-item opacity-60">
                                        <div class="notification-item-icon bg-gray-100 text-gray-400">📢</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs">{{ $campania->nombre }}</p>
                                            <p class="text-[10px] text-gray-400">Campaña creada</p>
                                        </div>
                                    </a>
                                @endforeach

                                @foreach($tareasVistas ?? [] as $archivo)
                                    <a href="{{ route('administrador.tareas.show', $archivo->tarea_id) }}" class="notification-item opacity-60">
                                        <div class="notification-item-icon bg-gray-100 text-gray-400">📎</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs">{{ $archivo->user->name }}</p>
                                            <p class="text-[10px] text-gray-400">Archivo en: {{ $archivo->tarea->nombre ?? '—' }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    <div class="notification-dropdown-footer border-t border-gray-100 pt-2">
                        <a href="{{ route('administrador.notificaciones.historial') }}"
                           class="block text-center text-xs text-indigo-600 hover:text-indigo-800 font-medium py-1">
                            Ver historial completo →
                        </a>
                    </div>
                </div>
            </div>

            <div class="topbar-user">
                <div class="topbar-user-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <span class="topbar-user-name">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    @if(request()->routeIs('administrador.dashboard') && isset($dashboardNotifications) && $dashboardNotifications->isNotEmpty())
        <aside class="dashboard-notification-stack" id="dashboardNotificationStack" aria-label="Nuevas notificaciones">
            <div class="dashboard-notification-heading">
                <span>Nuevas notificaciones</span>
                <span>{{ $notificationCount }}</span>
            </div>
            @foreach($dashboardNotifications as $notification)
                <article class="dashboard-notification-toast is-{{ $notification['type'] }}" data-dashboard-notification>
                    <div class="dashboard-toast-icon"><i class="fas {{ $notification['icon'] }}"></i></div>
                    <a href="{{ $notification['url'] }}" class="dashboard-toast-copy">
                        <strong>{{ $notification['title'] }}</strong>
                        <span>{{ $notification['message'] }}</span>
                        <time>{{ $notification['date']->diffForHumans() }}</time>
                    </a>
                    <button type="button" class="dashboard-toast-close" aria-label="Cerrar notificación">
                        <i class="fas fa-times"></i>
                    </button>
                </article>
            @endforeach
        </aside>
    @endif

    <main class="mt-10">
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleNotificationDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            const isOpening = !dropdown.classList.contains('show');
            dropdown.classList.toggle('show');

            const userMenu = document.getElementById('userDropdownMenu');
            if (userMenu) userMenu.classList.remove('show');

            if (isOpening) {
                marcarNotificacionesVistas();
            }
        }

        function marcarNotificacionesVistas() {
            fetch('{{ route('administrador.notificaciones.marcar-vistas') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    ocultarBadge();
                }
            })
            .catch(() => {});
        }

        function ocultarBadge() {
            const badge = document.getElementById('notifBadge');
            const label = document.getElementById('notifCountLabel');
            if (badge) badge.style.display = 'none';
            if (label) label.textContent = '0 sin leer';
        }

        // Polling cada 30 segundos para detectar notificaciones nuevas
        function verificarNotificaciones() {
            fetch('{{ route('administrador.notificaciones.conteo') }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                const label = document.getElementById('notifCountLabel');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = '';
                    }
                    if (label) label.textContent = data.count + ' sin leer';
                } else {
                    ocultarBadge();
                }
            })
            .catch(() => {});
        }

        setInterval(verificarNotificaciones, 30000);

        document.querySelectorAll('[data-dashboard-notification]').forEach((toast, index) => {
            const closeToast = () => {
                if (toast.classList.contains('is-leaving')) return;
                toast.classList.add('is-leaving');
                setTimeout(() => toast.remove(), 220);
            };
            toast.querySelector('.dashboard-toast-close')?.addEventListener('click', closeToast);
            setTimeout(closeToast, 10000 + (index * 900));
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const btn = document.getElementById('notificationBtn');
            if (dropdown && !dropdown.contains(event.target) && !btn.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>


