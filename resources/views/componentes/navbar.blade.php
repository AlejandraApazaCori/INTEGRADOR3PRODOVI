<!-- resources/views/componentes/navbar.blade.php -->
<nav class="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <div class="navbar-brand">
            <a href="#inicio">
                <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" class="navbar-logo">
            </a>
        </div>

        <!-- Desktop Menu -->
        <div class="navbar-menu">
            <!-- Navigation Links -->
            <ul class="navbar-links">
                <li class="navbar-item">
                    <a href="#inicio" class="navbar-link">Inicio</a>
                </li>
                <li class="navbar-item">
                    <a href="#servicios" class="navbar-link">Servicios</a>
                </li>
                <li class="navbar-item">
                    <a href="#conocenos" class="navbar-link">Conócenos</a>
                </li>
                <li class="navbar-item">
                    <a href="#proyectos" class="navbar-link">Proyectos</a>
                </li>
                
            </ul>
            
        
            <!-- Social Icons -->
            <div class="social-icons">
                <a href="https://www.facebook.com/PRODOVI" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/prodovi_agencia" target="_blank" rel="noopener noreferrer" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@prodovi" target="_blank" rel="noopener noreferrer" title="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
            </div>
            
            <!-- Login Button -->
            <div class="navbar-actions">
                <a href="{{ route('login') }}" class="login-button">
                    Iniciar Sesión
                </a>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <div class="navbar-mobile">
            <button class="mobile-menu-button" id="mobileMenuButton">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <ul class="mobile-nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#conocenos">Conócenos</a></li>
                <li><a href="#proyectos">Proyectos</a></li>
                <li><a href="#servicios">Servicios</a></li>
            </ul>
            
           
            
            <div class="mobile-social-icons">
                <a href="#" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" title="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
            </div>
            
            <a href="{{ route('login') }}" class="mobile-login-button">Iniciar Sesión</a>
        </div>
    </div>
</nav>

<style>
    /* Google Fonts Import */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    
    /* CSS Variables */
    :root {
        --primary-color: #8b5cf6;
        --secondary-color: #3b82f6;
        --text-color: #ffffff;
        --bg-color: rgba(0, 0, 0, 0.7);
        --scrolled-bg: rgba(0, 0, 0, 0.9);
        --transition: all 0.3s ease;
    }
    
    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    /* Navbar Styles */
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 1rem 2rem;
        transition: var(--transition);
        background: var(--bg-color);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .navbar.scrolled {
        background: var(--scrolled-bg);
        padding: 0.7rem 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }
    
    .navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    /* Logo Styles */
    .navbar-brand {
        flex-shrink: 0;
    }
    
    .navbar-logo {
        height: 40px;
        width: auto;
        transition: var(--transition);
    }
    
    .navbar.scrolled .navbar-logo {
        height: 35px;
    }
    
    /* Desktop Menu Styles */
    .navbar-menu {
        display: flex;
        align-items: center;
        gap: 2rem;
    }
    
    /* Navigation Links */
    .navbar-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .navbar-item {
        position: relative;
        margin: 0 0.5rem;
    }
    
    .navbar-link {
        color: var(--text-color);
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        font-weight: 500;
        padding: 0.5rem 0;
        position: relative;
        transition: var(--transition);
    }
    
    .navbar-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        background: #117e8c;
        transition: width 0.3s ease;
    }
    
    .navbar-link:hover::after {
        width: 100%;
    }
    
    .navbar-link:hover {
        color: var(--primary-color);
    }
    
    /* Contact Info Styles */
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .contact-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-color);
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        opacity: 0.9;
        transition: var(--transition);
    }
    
    .contact-item:hover {
        opacity: 1;
    }
    
    .contact-item svg {
        color: var(--primary-color);
    }
    
    /* Social Icons Styles */
    .social-icons {
        display: flex;
        gap: 0.8rem;
    }
    
    .social-icons a {
        color: var(--text-color);
        opacity: 0.8;
        transition: var(--transition);
        display: flex;
        align-items: center;
    }
    
    .social-icons a:hover {
        color: var(--primary-color);
        opacity: 1;
        transform: translateY(-3px);
    }
    
    /* Login Button Styles */
    .login-button {
        background: linear-gradient(135deg, #117e8c 0%, #0d6672 100%);
        color: white;
        border: 1px solid rgba(245, 169, 0, .72);
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: 0 14px 32px rgba(17, 126, 140, .32);
    }
    
    .login-button:hover {
        background: linear-gradient(135deg, #1594a4 0%, #117e8c 100%);
        border-color: #f5a900;
        transform: translateY(-2px);
        box-shadow: 0 19px 40px rgba(17, 126, 140, .42);
    }
    
    /* Mobile Menu Styles */
    .navbar-mobile {
        display: none;
    }
    
    .mobile-menu-button {
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 24px;
        width: 30px;
        padding: 0;
    }
    
    .mobile-menu-button span {
        display: block;
        width: 100%;
        height: 3px;
        background: var(--text-color);
        transition: var(--transition);
    }
    
    .mobile-menu-button.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }
    
    .mobile-menu-button.active span:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-button.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -6px);
    }
    
    .mobile-menu {
        position: fixed;
        top: 0;
        right: -100%;
        width: 80%;
        max-width: 400px;
        height: 100vh;
        background: rgba(0, 0, 0, 0.95);
        backdrop-filter: blur(10px);
        transition: right 0.4s ease;
        z-index: 999;
        overflow-y: auto;
    }
    
    .mobile-menu.active {
        right: 0;
    }
    
    .mobile-menu-content {
        padding: 5rem 2rem 2rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .mobile-nav-links {
        list-style: none;
        padding: 0;
        margin: 0 0 2rem;
    }
    
    .mobile-nav-links li {
        margin: 1.5rem 0;
    }
    
    .mobile-nav-links a {
        color: var(--text-color);
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-size: 1.2rem;
        font-weight: 500;
        transition: var(--transition);
        display: block;
        padding: 0.5rem 0;
    }
    
    .mobile-nav-links a:hover {
        color: var(--primary-color);
    }
    
    .mobile-contact-info {
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .mobile-social-icons {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .mobile-social-icons a {
        color: var(--text-color);
        opacity: 0.8;
        transition: var(--transition);
        display: flex;
        align-items: center;
    }
    
    .mobile-social-icons a:hover {
        color: var(--primary-color);
        opacity: 1;
        transform: translateY(-3px);
    }
    
    .mobile-login-button {
        background: linear-gradient(135deg, #117e8c 0%, #0d6672 100%);
        color: white;
        border: 1px solid rgba(245, 169, 0, .72);
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        text-align: center;
        margin-top: auto;
        box-shadow: 0 14px 32px rgba(17, 126, 140, .32);
    }
    
    .mobile-login-button:hover {
        background: linear-gradient(135deg, #1594a4 0%, #117e8c 100%);
        border-color: #f5a900;
        transform: translateY(-2px);
        box-shadow: 0 19px 40px rgba(17, 126, 140, .42);
    }
    
    /* Overlay for mobile menu */
    .mobile-menu-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .mobile-menu-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .navbar-menu {
            display: none;
        }
        
        .navbar-mobile {
            display: block;
        }
    }
    
    @media (max-width: 768px) {
        .navbar {
            padding: 1rem;
        }
        
        .navbar.scrolled {
            padding: 0.7rem 1rem;
        }
        
        .navbar-logo {
            height: 35px;
        }
        
        .navbar.scrolled .navbar-logo {
            height: 30px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        
        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        document.body.appendChild(overlay);
        
        // Scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Mobile menu toggle
        mobileMenuButton.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
        
        // Close menu when clicking on overlay
        overlay.addEventListener('click', function() {
            mobileMenuButton.classList.remove('active');
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Close menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuButton.classList.remove('active');
                mobileMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992 && mobileMenu.classList.contains('active')) {
                mobileMenuButton.classList.remove('active');
                mobileMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
</script>
