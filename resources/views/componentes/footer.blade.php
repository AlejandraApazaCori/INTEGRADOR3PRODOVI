<!-- resources/views/componentes/footer.blade.php -->
<footer class="site-footer">
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
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
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

<style>
    /* Estilos del Footer */
    .site-footer {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        padding: 4rem 0 0;
        position: relative;
        overflow: hidden;
        font-family: "Varela Round", sans-serif;
                  background-image: url('../imagenes/herofondo.png');
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
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: linear-gradient(45deg, #a855f7, #3b82f6);
        transform: translateY(-3px);
    }

    .footer-links {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .links-column h3 {
        color: white;
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
        background: linear-gradient(45deg, #a855f7, #3b82f6);
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
        color: #a855f7;
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
        color: #a855f7;
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
        color: #a855f7;
    }

    /* Efectos decorativos */
    .site-footer:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #a855f7, #3b82f6);
    }
    .footer-brand img {
        height: 40px;
        width: auto;
        margin-bottom: 1rem;
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

