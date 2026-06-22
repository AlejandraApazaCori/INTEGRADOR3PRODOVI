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
                                    <a href="{{ url('administrador/pagos/pendientes-fisicos') }}" class="notification-item font-semibold">
                                        <div class="notification-item-icon bg-amber-100 text-amber-600">💰</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs font-semibold">{{ $pago->usuario->name }}</p>
                                            <p class="text-[10px] text-gray-500">Realizó un pago — plan {{ $pago->plan->nombre ?? '—' }}</p>
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
                                    <a href="{{ url('administrador/pagos/pendientes-fisicos') }}" class="notification-item opacity-60">
                                        <div class="notification-item-icon bg-gray-100 text-gray-400">💰</div>
                                        <div class="notification-item-content">
                                            <p class="text-xs">{{ $pago->usuario->name }}</p>
                                            <p class="text-[10px] text-gray-400">Pago — {{ $pago->plan->nombre ?? '—' }}</p>
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


