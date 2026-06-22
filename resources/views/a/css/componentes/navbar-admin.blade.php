    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
            margin-left: 280px;
           
            transition: margin-left 0.3s ease, margin-top 0.3s ease;
        }

        body.sidebar-collapsed {
            margin-left: 0;
        }

        /* Sidebar Principal */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            /* Gradiente semitransparente sobre la imagen */
            background: 
                linear-gradient(135deg, rgba(102, 126, 234, 0.953) 0%, rgba(118, 75, 162, 0.945) 100%),
                url('{{ asset('imagenes/sidebar.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        /* Logo */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            justify-content: center;
            display: flex;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
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
            color: rgba(255, 255, 255, 0.6);
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
            margin: 2px 12px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }

        .menu-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .menu-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .menu-text {
            flex: 1;
        }

        .menu-arrow {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .menu-badge {
            background: #10b981;
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
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.1);
            margin: 4px 0;
            border-radius: 8px;
        }

        .submenu.open {
            max-height: 300px;
        }

        .submenu-item {
            padding: 8px 16px 8px 48px;
        }

        .submenu-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 13px;
            display: block;
            padding: 6px 0;
            transition: color 0.3s ease;
        }

        .submenu-link:hover {
            color: white;
        }

        /* Sección de usuario mejorada */
        .user-section {
            position: relative;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .user-dropdown {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            cursor: pointer;
            padding: 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
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
            color: #10b981;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .dropdown-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
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
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 999;
            transition: left 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .topbar.sidebar-collapsed {
            left: 0;
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
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .topbar-toggle-btn:hover {
            background: #f3f4f6;
            color: #1f2937;
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
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .topbar-notification-btn:hover {
            background: #f3f4f6;
            color: #1f2937;
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
            cursor: default;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .topbar-user:hover {
            background: #f3f4f6;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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
        }

        /* Scrollbar personalizada */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
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

            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                sidebar.style.transform = 'translateX(-100%)';
                body.classList.add('sidebar-collapsed');
                topbar.classList.add('sidebar-collapsed');
            } else {
                sidebar.style.transform = 'translateX(0)';
                body.classList.remove('sidebar-collapsed');
                topbar.classList.remove('sidebar-collapsed');
            }
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