    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f3;
            margin-left: 280px;
           
            transition: margin-left 0.3s ease, margin-top 0.3s ease;
        }

        body.sidebar-collapsed {
            margin-left: 76px;
        }

        /* Sidebar Principal */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: #ffffff;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            border-right: 1px solid #e1e4e2;
            box-shadow: 5px 0 18px rgba(31, 35, 38, 0.07);
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: #c5c9c6 #f0f1f0;
        }

        /* Logo */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e1e4e2;
            background: #ffffff;
            justify-content: center;
            display: flex;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #202326;
            text-decoration: none;
        }

        .logo-icon img {
            width: 160px
        }

        /* Menú */
        .sidebar-menu {
            padding: 20px 0;
            flex: 1;
        }

        .menu-label {
            color: #858b88;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px 12px 20px;
            margin-top: 20px;
        }

        .menu-label:first-child {
            margin-top: 0;
        }

        .menu-item {
            --menu-color: #5b2b76;
            --menu-active-text: #ffffff;
            --menu-hover-text: var(--menu-color);
            margin: 2px 12px;
        }

        .menu-item.menu-purple { --menu-color: #5b2b76; }
        .menu-item.menu-orange-dark { --menu-color: #ef6c22; }
        .menu-item.menu-orange { --menu-color: #e9a51a; --menu-active-text: #ffffff; --menu-hover-text: #956300; }
        .menu-item.menu-green { --menu-color: #a7b838; }
        .menu-item.menu-teal { --menu-color: #117e8c; }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #5f6562;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-link:hover {
            background: color-mix(in srgb, var(--menu-color) 12%, transparent);
            color: var(--menu-hover-text);
            transform: translateX(2px);
        }

        .menu-link.active {
            background: var(--menu-color);
            color: var(--menu-active-text);
            font-weight: 750;
            box-shadow: 0 4px 11px color-mix(in srgb, var(--menu-color) 20%, transparent);
        }

        .menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .menu-text {
            flex: 1;
        }

        @media (min-width: 769px) {
            .sidebar.collapsed {
                width: 76px;
                overflow: visible;
            }

            .sidebar.collapsed .sidebar-header {
                min-height: 76px;
                padding: 18px 10px;
            }

            .sidebar.collapsed .logo-img {
                display: block;
                width: 54px;
                height: auto;
                object-fit: contain;
            }

            .sidebar.collapsed .sidebar-menu {
                padding: 12px 0;
            }

            .sidebar.collapsed .menu-label,
            .sidebar.collapsed .menu-arrow,
            .sidebar.collapsed .menu-badge {
                display: none;
            }

            .sidebar.collapsed .menu-item {
                position: relative;
                margin: 4px 10px;
            }

            .sidebar.collapsed .menu-item:has(.submenu)::after {
                content: '';
                position: absolute;
                z-index: 1198;
                top: 0;
                left: 100%;
                width: 16px;
                height: 100%;
            }

            .sidebar.collapsed .menu-link {
                min-height: 48px;
                justify-content: center;
                gap: 0;
                padding: 12px;
            }

            .sidebar.collapsed .menu-link:hover {
                transform: none;
            }

            .sidebar.collapsed .menu-icon {
                width: 22px;
                height: 22px;
            }

            .sidebar.collapsed .menu-text {
                position: absolute;
                z-index: 1200;
                top: 50%;
                left: calc(100% + 14px);
                width: max-content;
                max-width: 220px;
                padding: 8px 11px;
                border-radius: 7px;
                background: #292c2f;
                color: #ffffff;
                box-shadow: 0 7px 18px rgba(24, 27, 29, 0.18);
                font-size: 12px;
                font-weight: 750;
                line-height: 1.2;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translate(5px, -50%);
                transition: opacity 0.16s ease, transform 0.16s ease, visibility 0.16s ease;
            }

            .sidebar.collapsed .menu-text::before {
                content: '';
                position: absolute;
                top: 50%;
                right: 100%;
                border: 5px solid transparent;
                border-right-color: #292c2f;
                transform: translateY(-50%);
            }

            .sidebar.collapsed .menu-link:hover .menu-text,
            .sidebar.collapsed .menu-link:focus-visible .menu-text {
                opacity: 1;
                visibility: visible;
                transform: translate(0, -50%);
            }

            .sidebar.collapsed .menu-item:has(.submenu) > .menu-link .menu-text {
                display: none;
            }

            .sidebar.collapsed .submenu,
            .sidebar.collapsed .submenu.open {
                position: absolute;
                z-index: 1199;
                top: -8px;
                left: calc(100% + 14px);
                display: block;
                width: 230px;
                max-height: none;
                margin: 0;
                padding: 8px 8px 8px 0;
                overflow: visible;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                background: #ffffff;
                box-shadow: 0 12px 28px rgba(24,27,29,.16);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateX(5px);
                transition: opacity .16s ease,transform .16s ease,visibility .16s ease;
            }

            .sidebar.collapsed .menu-item:hover > .submenu,
            .sidebar.collapsed .menu-item:focus-within > .submenu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateX(0);
            }

            .sidebar.collapsed .submenu-item {
                padding-right: 0;
            }

            .sidebar.collapsed .submenu-title {
                display: block;
                margin: 0 8px 7px;
                padding: 5px 10px 10px;
                border-bottom: 1px solid #ececef;
                color: var(--menu-color);
                font-size: 12px;
                font-weight: 900;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .sidebar.collapsed .submenu::before {
                top: 53px;
            }

            .sidebar.collapsed .user-section {
                padding: 10px;
            }

            .sidebar.collapsed .user-button {
                justify-content: center;
                padding: 9px;
            }

            .sidebar.collapsed .user-avatar {
                margin-right: 0;
            }

            .sidebar.collapsed .user-details,
            .sidebar.collapsed .user-button > svg:last-child {
                display: none;
            }

            .sidebar.collapsed .dropdown-content {
                right: auto;
                bottom: 0;
                left: 66px;
                width: 270px;
                margin: 0;
            }
        }

        .menu-arrow {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .menu-badge {
            background: #5b2b76;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .menu-badge.hot {
            background: #ef4444;
        }

        /* Submenu */
        .submenu {
            position: relative;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: transparent;
            border: 0;
            margin: 4px 0;
            border-radius: 8px;
        }

        .submenu::before {
            content: '';
            position: absolute;
            top: 12px;
            bottom: 12px;
            left: 23px;
            width: 2px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--menu-color) 28%, #e5e7eb);
        }

        .submenu.open {
            max-height: 300px;
        }

        .submenu-title {
            display: none;
        }

        .submenu-item {
            padding: 4px 10px 4px 42px;
        }

        .submenu-link {
            position: relative;
            color: #6d726f;
            text-decoration: none;
            font-size: 13px;
            display: block;
            padding: 8px 10px;
            border-radius: 7px;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .submenu-link::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -22px;
            width: 8px;
            height: 8px;
            border: 2px solid color-mix(in srgb, var(--menu-color) 48%, #d1d5db);
            border-radius: 50%;
            background: #ffffff;
            transform: translateY(-50%);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .submenu-link:hover {
            background: color-mix(in srgb, var(--menu-color) 11%, transparent);
            color: var(--menu-hover-text);
        }

        .submenu-link.active {
            background: var(--menu-color);
            color: var(--menu-active-text);
            font-weight: 850;
        }

        .submenu-link.active::before {
            border-color: var(--menu-color);
            background: var(--menu-color);
            box-shadow: 0 0 0 3px #ffffff;
        }

        @media (min-width: 769px) {
            .sidebar.collapsed .submenu-link {
                color: #6d6870;
            }

            .sidebar.collapsed .submenu-link:hover {
                color: var(--menu-hover-text);
            }
        }

        /* Sección de usuario mejorada */
        .user-section {
            position: relative;
            padding: 20px;
            border-top: 1px solid #e1e4e2;
            background: #f7f8f7;
        }

        .user-dropdown {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            width: 100%;
            background: #eef0ee;
            border: 1px solid #e0e3e1;
            color: #444a47;
            cursor: pointer;
            padding: 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            background: #e7eae8;
            border-color: #d5d9d6;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #dfe2e0;
            color: #555b58;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .user-details {
            flex: 1;
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .user-status {
            color: #a7b838;
            font-size: 12px;
        }

        /* Dropdown Menu */
        .dropdown-content {
            position: absolute;
            bottom: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            margin-bottom: 8px;
            overflow: hidden;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .dropdown-content.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .dropdown-header {
            display: flex;
            padding: 20px;
            align-items: center;
            background: #f4f5f4;
            color: #454a47;
        }

        .dropdown-avatar {
            width: 50px;
            height: 50px;
            background: #dfe2e0;
            color: #555b58;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .dropdown-user-info {
            display: flex;
            flex-direction: column;
        }

        .dropdown-user-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .dropdown-user-email {
            font-size: 13px;
            opacity: 0.8;
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 0;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .dropdown-item svg {
            margin-right: 15px;
            color: #6b7280;
        }

        .dropdown-item:hover svg {
            color: #374151;
        }

        .logout-button {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: inherit;
            font: inherit;
            cursor: pointer;
            width: 100%;
            padding: 0;
            text-align: left;
        }

        .logout-button svg {
            margin-right: 15px;
        }

        /* Contenido principal */
        .main-content {
            padding: 30px;
            min-height: 100vh;
        }

        .content-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        /* Popup de confirmación */
        .confirmation-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .confirmation-popup.show {
            opacity: 1;
            visibility: visible;
        }

        .confirmation-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .confirmation-popup.show .confirmation-box {
            transform: translateY(0);
        }

        .confirmation-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #374151;
        }

        .confirmation-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .confirmation-button {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .confirmation-button.cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .confirmation-button.cancel:hover {
            background: #e5e7eb;
        }

        .confirmation-button.confirm {
            background: #ef4444;
            color: white;
        }

        .confirmation-button.confirm:hover {
            background: #dc2626;
        }

        /* ===== TOP BAR ===== */
        .topbar {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 60px;
            background: rgba(255, 255, 255, 0.97);
            border-bottom: 1px solid #dedfdf;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 999;
            transition: left 0.3s ease;
            box-shadow: 0 2px 10px rgba(31, 35, 38, 0.06);
        }

        .topbar.sidebar-collapsed,
        html.admin-sidebar-collapsed .topbar {
            left: 76px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
        }

        .topbar-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            color: #555b60;
            transition: all 0.2s ease;
        }

        .topbar-toggle-btn:hover {
            background: #eeeeed;
            color: #202326;
        }

        .topbar-toggle-btn svg {
            width: 22px;
            height: 22px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-notification-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            color: #555b60;
            transition: all 0.2s ease;
        }

        .topbar-notification-btn:hover {
            background: #eeeeed;
            color: #202326;
        }

        .topbar-notification-btn svg {
            width: 22px;
            height: 22px;
        }

        .topbar-notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .topbar-user:hover {
            background: #eeeeed;
        }

        .topbar-profile-container {
            position: relative;
        }

        .topbar-profile-dropdown {
            top: calc(100% + 8px);
            right: 0;
            bottom: auto;
            left: auto;
            width: 285px;
            margin: 0;
        }

        .topbar-user-chevron {
            width: 15px;
            height: 15px;
            color: #777d79;
            transition: transform 0.2s ease;
        }

        .topbar-user[aria-expanded="true"] .topbar-user-chevron {
            transform: rotate(180deg);
        }

        /* ===== DROPDOWN DE NOTIFICACIONES ===== */
        .topbar-notifications-container {
            position: relative;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            margin-top: 10px;
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-dropdown-header {
            padding: 15px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown-body {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-group {
            padding: 10px 0;
        }

        .notification-group-title {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            padding: 5px 20px;
            letter-spacing: 0.5px;
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #334155;
            transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }

        .notification-item:hover {
            background: #f1f5f9;
        }

        .notification-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 16px;
        }

        .notification-item-content {
            flex: 1;
        }

        .notification-dropdown-footer {
            padding: 10px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        .topbar-user-avatar {
            width: 36px;
            height: 36px;
            background: #dfe2e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555b58;
        }

        .topbar-user-avatar svg {
            width: 20px;
            height: 20px;
        }

        .topbar-user-name {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            body {
                margin-left: 0;
            }

            .topbar {
                left: 0;
            }

            html.admin-sidebar-collapsed .topbar {
                left: 0;
            }
        }

        /* Scrollbar personalizada */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f0f1f0;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #c5c9c6;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #aeb3af;
        }

    </style>

     <script>
        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.menu-arrow');
            
            if (submenu && submenu.classList.contains('submenu')) {
                submenu.classList.toggle('open');
                
                if (arrow) {
                    if (submenu.classList.contains('open')) {
                        arrow.style.transform = 'rotate(90deg)';
                    } else {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                }
            }
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userDropdownMenu');
            menu.classList.toggle('show');
        }

        function showLogoutConfirmation() {
            const popup = document.getElementById('logoutConfirmationPopup');
            popup.classList.add('show');
            // También cerramos el menú de usuario si está abierto
            document.getElementById('userDropdownMenu').classList.remove('show');
        }

        function hideLogoutConfirmation() {
            const popup = document.getElementById('logoutConfirmationPopup');
            popup.classList.remove('show');
        }

        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const topbar = document.querySelector('.topbar');
            const body = document.body;

            if (window.matchMedia('(max-width: 768px)').matches) {
                sidebar.classList.toggle('open');
                return;
            }

            const isCurrentlyCollapsed = sidebar.classList.contains('collapsed')
                || document.documentElement.classList.contains('admin-sidebar-collapsed');
            const isCollapsed = !isCurrentlyCollapsed;
            sidebar.classList.toggle('collapsed', isCollapsed);
            body.classList.toggle('sidebar-collapsed', isCollapsed);
            topbar.classList.toggle('sidebar-collapsed', isCollapsed);
            document.documentElement.classList.toggle('admin-sidebar-collapsed', isCollapsed);
            try {
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? '1' : '0');
            } catch (error) {}
        }

        // Cerrar el menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            const userDropdown = document.querySelector('.user-dropdown');
            if (!userDropdown.contains(event.target)) {
                document.getElementById('userDropdownMenu').classList.remove('show');
            }
            
            // Cerrar el popup de confirmación si se hace clic fuera
            const popup = document.getElementById('logoutConfirmationPopup');
            if (event.target === popup) {
                hideLogoutConfirmation();
            }
        });


    </script>
