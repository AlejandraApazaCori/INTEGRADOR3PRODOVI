<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100">
    @include('componentes.navbar-admin')

    <!-- Top Bar -->
    <div class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
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
                    @if(isset($notificationCount) && $notificationCount > 0)
                        <span class="topbar-notification-badge">{{ $notificationCount }}</span>
                    @endif
                </button>

                <!-- Dropdown de Notificaciones -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <h3 class="text-sm font-bold">Notificaciones</h3>
                        <span class="text-xs text-gray-500">{{ $notificationCount ?? 0 }} pendientes</span>
                    </div>
                    
                    <div class="notification-dropdown-body">
                        @if(isset($notificationCount) && $notificationCount > 0)
                            {{-- Pagos Pendientes --}}
                            @if(isset($latestPendingPayments) && $latestPendingPayments->count() > 0)
                                <div class="notification-group">
                                    <div class="notification-group-title">PAGOS PENDIENTES</div>
                                    @foreach($latestPendingPayments as $pago)
                                        <a href="{{ url('administrador/pagos/pendientes-fisicos') }}" class="notification-item">
                                            <div class="notification-item-icon bg-amber-100 text-amber-600">ðŸ’°</div>
                                            <div class="notification-item-content">
                                                <p class="text-xs font-semibold">{{ $pago->usuario->name }}</p>
                                                <p class="text-[10px] text-gray-500">SolicitÃ³ plan {{ $pago->plan->nombre }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Tareas Pendientes --}}
                            @if(isset($latestPendingTasks) && $latestPendingTasks->count() > 0)
                                <div class="notification-group">
                                    <div class="notification-group-title">TAREAS SUBIDAS</div>
                                    @foreach($latestPendingTasks as $archivo)
                                        <a href="{{ route('administrador.tareas.show', $archivo->tarea_id) }}" class="notification-item">
                                            <div class="notification-item-icon bg-blue-100 text-blue-600">ðŸ“</div>
                                            <div class="notification-item-content">
                                                <p class="text-xs font-semibold">{{ $archivo->user->name }}</p>
                                                <p class="text-[10px] text-gray-500">SubiÃ³ archivo a: {{ $archivo->tarea->nombre }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="p-4 text-center">
                                <p class="text-sm text-gray-500">No hay notificaciones pendientes</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="notification-dropdown-footer">
                        <p class="text-[10px] text-gray-400 text-center">Resumen de actividad reciente</p>
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
    
    <main ">


        
        @yield('content')
       
    </main>

    @stack('scripts')

    
   


    <script>
        function toggleNotificationDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('show');
            
            // Cerrar el dropdown de usuario si estÃ¡ abierto
            const userMenu = document.getElementById('userDropdownMenu');
            if (userMenu) userMenu.classList.remove('show');
        }

        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', function(event) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBtn = document.getElementById('notificationBtn');
            
            if (notificationDropdown && !notificationDropdown.contains(event.target) && !notificationBtn.contains(event.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>
 