<!-- resources/views/componentes/navbar.blade.php -->
<nav class="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <div class="navbar-brand">
            <a href="/">
                <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" class="navbar-logo">
            </a>
        </div>

        <!-- Menú usuario -->
        <div class="user-menu">
            @if(auth()->check())
                <div class="user-dropdown">
                    <button class="user-button">
                        <div class="user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="user-icon">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-status">En línea</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="dropdown-content">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="dropdown-user-info">
                                <span class="dropdown-user-name">{{ auth()->user()->name }}</span>
                                <span class="dropdown-user-email">{{ auth()->user()->email ?? 'usuario@prodovi.com' }}</span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-button">
                                <div class="logout-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logout-icon">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                </div>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</nav>

<style>
    /* Estilos del Navbar */
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 1rem 2rem;
        transition: all 0.3s ease;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navbar.scrolled {
        background: rgba(0, 0, 0, 0.9);
        padding: 0.5rem 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .navbar-brand img {
        height: 35px;
        width: auto;
        transition: all 0.3s ease;
    }

    .navbar.scrolled .navbar-brand img {
        height: 33px;
    }

    /* Estilos del menú de usuario mejorados */
    .user-menu {
        display: flex;
        align-items: center;
    }

    .user-dropdown {
        position: relative;
        display: inline-block;
    }

    .user-button {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        cursor: pointer;
        font-family: "Varela Round", sans-serif;
        font-size: 0.9rem;
        gap: 12px;
        padding: 8px 16px;
        border-radius: 50px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        min-width: 180px;
        justify-content: space-between;
    }

    .user-button:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .user-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #117e8c;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .user-icon {
        width: 20px;
        height: 20px;
        color: white;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        flex-grow: 1;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.9rem;
        line-height: 1;
        color: white;
    }

    .user-status {
        font-size: 0.75rem;
        color: #10b981;
        font-weight: 500;
        margin-top: 2px;
    }

    .chevron-icon {
        width: 16px;
        height: 16px;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .user-dropdown:hover .chevron-icon {
        transform: rotate(180deg);
    }

    /* Dropdown mejorado */
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(20px);
        min-width: 280px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        z-index: 1;
        border-radius: 16px;
        overflow: hidden;
        margin-top: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: dropdownSlide 0.3s ease;
    }

    @keyframes dropdownSlide {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-dropdown:hover .dropdown-content {
        display: block;
    }

    .user-dropdown.is-open .dropdown-content {
        display: block;
    }

    .dropdown-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px;
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(59, 130, 246, 0.1));
    }

    .dropdown-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #117e8c;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dropdown-user-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .dropdown-user-name {
        color: white;
        font-weight: 600;
        font-size: 1rem;
        font-family: "Varela Round", sans-serif;
    }

    .dropdown-user-email {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.8rem;
        font-family: "Varela Round", sans-serif;
    }

    .dropdown-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        margin: 0 16px;
    }

    .logout-button {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 16px 20px;
        text-decoration: none;
        color: white;
        background: none;
        border: none;
        text-align: left;
        cursor: pointer;
        font-family: "Varela Round", sans-serif;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .logout-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.1), transparent);
        transition: left 0.3s ease;
    }

    .logout-button:hover::before {
        left: 100%;
    }

    .logout-button:hover {
        background-color: rgba(239, 68, 68, 0.1);
        color: #f87171;
        transform: translateX(4px);
    }

    .logout-button:hover .logout-icon-wrapper {
        background: rgba(239, 68, 68, 0.2);
        transform: scale(1.1);
    }

    .logout-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .logout-icon {
        width: 18px;
        height: 18px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .navbar {
            padding: 1rem;
        }
        
        .navbar-brand img {
            height: 30px;
        }
        
        .user-button {
            min-width: 120px;
            padding: 6px 12px;
        }
        
        .user-name {
            font-size: 0.8rem;
        }
        
        .user-status {
            display: none;
        }
        
        .dropdown-content {
            min-width: 250px;
        }
    }

    @media (max-width: 480px) {
        .user-info {
            display: none;
        }
        
        .user-button {
            min-width: auto;
            padding: 8px;
            gap: 8px;
        }
        
        .dropdown-content {
            position: fixed;
            top: 72px;
            right: 12px;
            left: 12px;
            width: auto;
            min-width: 0;
            max-width: none;
            max-height: calc(100dvh - 84px);
            margin-top: 0;
            overflow-x: hidden;
            overflow-y: auto;
            z-index: 10001;
            border-radius: 14px;
        }

        .dropdown-header {
            min-width: 0;
            padding: 16px;
        }

        .dropdown-user-info {
            min-width: 0;
            flex: 1;
        }

        .dropdown-user-name,
        .dropdown-user-email {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .logout-button {
            width: 100%;
            padding: 15px 16px;
        }
    }
</style>

<script>
    // Script para el navbar
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');

        // Efecto de scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Dropdown por toque en responsive y por hover en escritorio
        const userDropdown = document.querySelector('.user-dropdown');
        if (userDropdown) {
            const dropdownContent = userDropdown.querySelector('.dropdown-content');
            const userButton = userDropdown.querySelector('.user-button');
            let hoverTimeout;

            const isResponsiveMenu = () => window.matchMedia(
                '(hover: none), (max-width: 768px)'
            ).matches;

            userButton?.setAttribute('aria-expanded', 'false');

            userButton?.addEventListener('click', function(event) {
                if (!isResponsiveMenu()) return;

                event.preventDefault();
                event.stopPropagation();

                const isOpen = userDropdown.classList.toggle('is-open');
                dropdownContent.style.display = isOpen ? 'block' : 'none';
                userButton.setAttribute('aria-expanded', String(isOpen));
            });

            userDropdown.addEventListener('mouseenter', function() {
                if (isResponsiveMenu()) return;
                clearTimeout(hoverTimeout);
                dropdownContent.style.display = 'block';
            });

            userDropdown.addEventListener('mouseleave', function() {
                if (isResponsiveMenu()) return;
                hoverTimeout = setTimeout(() => {
                    dropdownContent.style.display = 'none';
                }, 150);
            });

            document.addEventListener('click', function(event) {
                if (!userDropdown.contains(event.target)) {
                    userDropdown.classList.remove('is-open');
                    userButton?.setAttribute('aria-expanded', 'false');
                    dropdownContent.style.display = 'none';
                }
            });

            window.addEventListener('resize', function() {
                if (!isResponsiveMenu()) {
                    userDropdown.classList.remove('is-open');
                    userButton?.setAttribute('aria-expanded', 'false');
                    dropdownContent.style.display = '';
                }
            });
        }
    });
</script>
