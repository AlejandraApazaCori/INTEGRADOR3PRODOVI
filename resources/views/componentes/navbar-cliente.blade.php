@include('a.css.componentes.navbar-admin')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Header con Logo -->
        <div class="sidebar-header">
            <a href="{{ route('clientes.dashboard') }}" class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" class="logo-img">
                </div>
            </a>
        </div>

        <!-- Menú de navegación -->
        <div class="sidebar-menu">
            <div class="menu-label">MENU</div>
            
            <!-- DASHBOARD -->
            <div class="menu-item">
                <a href="{{ route('clientes.dashboard') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span class="menu-text">Dashboard</span>
                </a>
            </div>

            <!-- PAGOS -->
            <div class="menu-item">
                <a href="#" class="menu-link" onclick="toggleSubmenu(this)">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span class="menu-text">Pagos</span>
                    <svg class="menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
                <div class="submenu">
                    <div class="submenu-item">
                        <a href="{{ route('clientes.historial.pagos') }}" class="submenu-link">Historial de pagos</a>
                    </div>
                </div>
            </div>

            <!-- CAMPAÑAS 
            <div class="menu-item">
                <a href="#" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="menu-text">Campañas</span>
                </a>
            </div>

            <!-- RECURSOS 
            <div class="menu-item">
                <a href="#" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span class="menu-text">Recursos</span>
                </a>
            </div>
            -->

            <!-- ANALÍTICAS -->
            <div class="menu-item">
                <a href="{{ route('clientes.analiticas') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span class="menu-text">Analíticas</span>
                </a>
            </div>  
        </div>

        <!-- Sección de usuario mejorada -->
        <div class="user-section">
            <div class="user-dropdown">
                <button class="user-button" onclick="toggleUserMenu()">
                    <div class="user-avatar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-status">En línea</div>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                
                <div class="dropdown-content" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                         <div class="dropdown-user-info">
                            <span class="dropdown-user-name">{{ auth()->user()->name }}</span>
                            <span class="dropdown-user-email">{{ auth()->user()->email ?? 'usuario@prodovi.com' }}</span>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <a href="{{ route('clientes.micuenta') }}" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Mi Perfil</span>
                    </a>
                    
                    <a href="#" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6"/>
                            <path d="M9 12h6"/>
                        </svg>
                        <span>Configuración</span>
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    
                    <div class="dropdown-item">
                        <button class="logout-button" onclick="showLogoutConfirmation()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span>Cerrar sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup de confirmación para cerrar sesión -->
    <div class="confirmation-popup" id="logoutConfirmationPopup">
        <div class="confirmation-box">
            <div class="confirmation-title">¿Estás seguro de cerrar sesión?</div>
            <div class="confirmation-buttons">
                <button class="confirmation-button cancel" onclick="hideLogoutConfirmation()">No</button>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="confirmation-button confirm">Sí, cerrar sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>