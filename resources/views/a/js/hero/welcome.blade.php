<script>
      // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (header && window.scrollY > 100) {
                header.classList.add('scrolled');
            } else if (header) {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scrolling con GSAP para enlaces de navegación y botón hero
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const target = document.querySelector(targetId);
                
                if (target) {
                    // GSAP Scroll animation
                    gsap.to(window, {
                        duration: 1.5,
                        scrollTo: {
                            y: target,
                            offsetY: 0
                        },
                        ease: "power4.inOut"
                    });
                }
            });
        });

        // Efectos de mouse mejorados
        let mouseTrails = [];
        const maxTrails = 10;

        document.addEventListener('mousemove', (e) => {
            // Limpiar trails antiguos
            if (mouseTrails.length >= maxTrails) {
                const oldTrail = mouseTrails.shift();
                if (oldTrail && oldTrail.parentNode) {
                    oldTrail.parentNode.removeChild(oldTrail);
                }
            }

            const trail = document.createElement('div');
            trail.className = 'cursor-trail';
            trail.style.cssText = `
                position: fixed;
                left: ${e.clientX}px;
                top: ${e.clientY}px;
                width: 6px;
                height: 6px;
                background: linear-gradient(45deg, #a855f7, #3b82f6);
                border-radius: 50%;
                pointer-events: none;
                z-index: 9999;
                animation: cursorFade 1.2s ease-out forwards;
                box-shadow: 0 0 10px rgba(168, 85, 247, 0.5);
            `;
            
            document.body.appendChild(trail);
            mouseTrails.push(trail);
            
            setTimeout(() => {
                if (trail.parentNode) {
                    trail.parentNode.removeChild(trail);
                }
            }, 1200);
        });

        // Agregar animaciones CSS dinámicamente
        const style = document.createElement('style');
        style.textContent = `
            @keyframes cursorFade {
                0% {
                    transform: scale(1);
                    opacity: 0.8;
                }
                100% {
                    transform: scale(3);
                    opacity: 0;
                }
            }

            @keyframes ripple {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(4);
                    opacity: 0;
                }
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }

            .hero-person {
                cursor: pointer;
            }

            /* Scroll suave para toda la página */
            html {
                scroll-behavior: smooth;
            }
        `;
        document.head.appendChild(style);

        // Efecto parallax para elementos flotantes
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.floating-element');
            
            parallaxElements.forEach((element, index) => {
                const speed = 0.3 + (index * 0.1);
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px) rotate(${scrolled * 0.1}deg)`;
            });
        });

        // Mejorar la experiencia de carga
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
        });

        // Agregar clase cuando la página está cargada
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
    



        
</script>
