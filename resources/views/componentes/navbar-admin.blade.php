@include('a.css.componentes.navbar-admin')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Header con Logo -->
        <div class="sidebar-header">
            <a href="{{ route('administrador.dashboard') }}" class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" class="logo-img">
                </div>
            </a>
        </div>

        <!-- MenÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Âº de navegaciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n -->
        <div class="sidebar-menu">
            <div class="menu-label">MENU</div>
            
            <!-- Dashboard -->
            <div class="menu-item">
                <a href="{{ route('administrador.dashboard') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span class="menu-text">Dashboard</span>
                </a>
            </div>

            <!-- Usuarios -->
            <div class="menu-item">
                <a href="{{ route('administrador.usuarios.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="menu-text">Usuarios</span>
                </a>
            </div>

            <!-- Empresas -->
            <div class="menu-item">
                <a href="{{ route('administrador.empresas.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-3"></path>
                        <rect x="9" y="9" width="4" height="4"></rect>
                        <rect x="9" y="14" width="4" height="4"></rect>
                    </svg>
                    <span class="menu-text">Empresas</span>
                </a>
            </div>

            <!-- Cuestionario briefing -->
            <div class="menu-item">
                <a href="{{ route('administrador.cuestionario.estructura.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span class="menu-text">Cuestionario briefing</span>
                </a>
            </div>

            <!-- Planes -->
            <div class="menu-item">
                <a href="{{ route('administrador.planes.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="15" y2="9"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    <span class="menu-text">Planes</span>
                </a>
            </div>

            <!-- Pagos -->
            @php
                $pagosMenuOpen = request()->routeIs('administrador.pagos.*') || request()->routeIs('admin.analiticas.*');
            @endphp
            <div class="menu-item">
                <a href="#" class="menu-link {{ $pagosMenuOpen ? 'active' : '' }}" onclick="toggleSubmenu(this); return false;">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span class="menu-text">Pagos</span>
                    <svg class="menu-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="{{ $pagosMenuOpen ? 'transform: rotate(180deg);' : '' }}">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <div class="submenu {{ $pagosMenuOpen ? 'open' : '' }}">
                    <div class="submenu-item">
                        <a href="{{ route('administrador.pagos.analiticas') }}" class="submenu-link {{ request()->routeIs('administrador.pagos.analiticas') ? 'active' : '' }}">Analíticas</a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ route('administrador.pagos.index') }}" class="submenu-link {{ request()->routeIs('administrador.pagos.index') ? 'active' : '' }}">General</a>
                    </div>
                    <div class="submenu-item">
                        <a href="{{ route('administrador.pagos.manual.crear') }}" class="submenu-link {{ request()->routeIs('administrador.pagos.manual.*') ? 'active' : '' }}">Registrar pago</a>
                    </div>
                </div>
            </div>

            <!-- Campañas -->
            <div class="menu-item">
                <a href="{{ route('administrador.campañas.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <rect x="9" y="9" width="6" height="6"/>
                    </svg>
                    <span class="menu-text">Campañas</span>
                </a>
            </div>


            <!-- Logs -->
            <div class="menu-item">
                <a href="{{ route('administrador.logs.index') }}" class="menu-link">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        <path d="M12 11h4"></path>
                        <path d="M12 16h4"></path>
                        <path d="M8 11h.01"></path>
                        <path d="M8 16h.01"></path>
                    </svg>
                    <span class="menu-text">Logs</span>
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
                        <div class="user-status">En linea</div>
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
                    
                    <a href="#" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Mi Perfil</span>
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

