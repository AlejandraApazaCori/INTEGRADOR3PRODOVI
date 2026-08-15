<!-- resources/views/componentes/footer.blade.php -->
<footer class="site-footer animate-container slide-left">
    <div class="footer-container">
        <!-- Sección Superior del Footer -->
        <div class="footer-top">
            <div class="footer-brand">
                <div class="imgen">
                     <img  src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo" >
                </div>

               
                <p class="footer-description">
                    Potenciamos tu marca con estrategias de marketing digital innovadoras que generan resultados reales.
                </p>
                <div class="social-links">
                    <a href="https://www.facebook.com/PRODOVI" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/prodovi_agencia" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.tiktok.com/@prodovi" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://wa.me/59179561365" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <div class="footer-links">
                <div class="links-column">
                    <h3 class="links-title">Navegación</h3>
                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li><a href="#conocenos">Conócenos</a></li>
                        <li><a href="#proyectos">Proyectos</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                    </ul>
                </div>

             

                <div class="links-column">
                    <h3 class="links-title">Contacto</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Zona Miraflores, Stadium Av. Hugo Estrada , Edificio Olímpia # 1354, lado Banco Sol y Karaoke Love City, Piso 1 Oficina 3, La Paz, Bolivia</li>
                        <li><i class="fas fa-phone"></i> +591 79561365</li>
                        <li><i class="fas fa-envelope"></i> info@prodovi.com</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sección Inferior del Footer -->
        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} PRODOVI. Todos los derechos reservados.
            </div>
            <div class="legal-links">
                <a href="{{ route('legal.privacy-policy') }}">Política de Privacidad</a>
                <a href="{{ route('legal.terms') }}">Términos de Servicio</a>
                <a href="{{ route('legal.data-deletion') }}">Eliminación de datos</a>
            </div>
        </div>
    </div>
</footer>

<a href="https://www.bing.com/maps/search?v=2&amp;pc=FACEBK&amp;mid=8100&amp;mkt=es-MX&amp;FORM=FBKPL1&amp;q=Real+Plaza+Hotel+%26+Convention+Center.+Av.+Arce+%232177+%28Frente+a+la+Plaza+Bolivia%29%2C+La+Paz%2C+Bolivia%2C+La+Paz%2C+Bolivia&amp;cp=-16.506655%7E-68.127258&amp;lvl=16&amp;style=r"
   class="location-floating"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Ver nuestra ubicación"
   data-tooltip="Ubicación">
    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
</a>

<a href="https://wa.me/59179561365"
   class="whatsapp-floating"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Contáctanos por WhatsApp"
   data-tooltip="Contáctanos">
    <i class="fab fa-whatsapp" aria-hidden="true"></i>
</a>

<style>
    /* Estilos del Footer */
    .site-footer {
        background-color: #101010;
        color: white;
        padding: 4rem 0 0;
        position: relative;
        overflow: hidden;
        font-family: "Varela Round", sans-serif;
        background-image: linear-gradient(rgba(0, 0, 0, .7), rgba(0, 0, 0, .7)), url('../imagenes/herofondo.png');
        background-size: cover;
        background-position: center;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;

    }

    .footer-top {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 3rem;
        margin-bottom: 3rem;
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
    }



    .footer-description {
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: rgba(17, 126, 140, .18);
        border: 1px solid rgba(245, 169, 0, .65);
        border-radius: 10px;
        color: #5fc2ce;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: #ef6c22;
        color: white;
        transform: translateY(-3px);
    }

    .footer-links {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .links-column h3 {
        color: #f5a900;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.5rem;
        font-family: "Rowdies", sans-serif;
        font-weight: 300;
    }

    .links-column h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background: #117e8c;
    }

    .links-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .links-column li {
        margin-bottom: 0.8rem;
    }

    .links-column a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .links-column a:hover {
        color: #5fc2ce;
        padding-left: 5px;
    }

    .contact-info li {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .contact-info i {
        color: #ef8b3a;
        width: 20px;
        text-align: center;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .copyright {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.9rem;
    }

    .legal-links {
        display: flex;
        gap: 1.5rem;
    }

    .legal-links a {
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .legal-links a:hover {
        color: #5fc2ce;
    }

    /* Efectos decorativos */
    .site-footer:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #5b2b76, #ef6c22, #f5a900, #7da533, #117e8c);
    }
    .footer-brand img {
        height: 40px;
        width: auto;
        margin-bottom: 1rem;
    }

    .whatsapp-floating {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        border: 2px solid #f5a900;
        border-radius: 50%;
        background: #25d366;
        color: white;
        font-size: 2rem;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .whatsapp-floating::before {
        content: attr(data-tooltip);
        position: absolute;
        right: calc(100% + 12px);
        padding: .55rem .8rem;
        border-radius: 8px;
        background: #111;
        color: #fff;
        font-size: .85rem;
        line-height: 1;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transform: translateX(8px);
        transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
    }

    .whatsapp-floating:hover {
        color: white;
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 30px rgba(37, 211, 102, .35);
    }

    .whatsapp-floating:hover::before,
    .whatsapp-floating:focus-visible::before {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    .location-floating {
        position: fixed;
        right: 24px;
        bottom: 96px;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        border: 2px solid #f5a900;
        border-radius: 50%;
        background: #ef6c22;
        color: white;
        font-size: 1.65rem;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .35);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .location-floating::before {
        content: attr(data-tooltip);
        position: absolute;
        right: calc(100% + 12px);
        padding: .55rem .8rem;
        border-radius: 8px;
        background: #111;
        color: #fff;
        font-size: .85rem;
        line-height: 1;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transform: translateX(8px);
        transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
    }

    .location-floating:hover {
        color: white;
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 30px rgba(239, 108, 34, .38);
    }

    .location-floating:hover::before,
    .location-floating:focus-visible::before {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .footer-top {
            grid-template-columns: 1fr;
        }

        .footer-links {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .whatsapp-floating {
            right: 16px;
            bottom: 16px;
            width: 52px;
            height: 52px;
            font-size: 1.75rem;
        }

        .location-floating {
            right: 16px;
            bottom: 80px;
            width: 52px;
            height: 52px;
            font-size: 1.5rem;
        }

        .footer-links {
            grid-template-columns: 1fr;
        }

        .footer-bottom {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .legal-links {
            justify-content: center;
            flex-wrap: wrap;
        }
    }
</style>

<!-- Incluir Font Awesome para los íconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
