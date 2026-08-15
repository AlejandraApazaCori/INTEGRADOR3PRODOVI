<style>
    body{
   margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background-color: #000000ff;
}
.container1{
   
    background-image: linear-gradient(rgba(0, 0, 0, .42), rgba(0, 0, 0, .42)), url('../imagenes/herofondo.png');
    padding: 0 150px;
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
     padding-top: 60px;
    border-radius: 0px 0px 200px 200px;
   
    
}

 /* Hero */
       
        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            width: 100%;
        }

        .hero-text {
            z-index: 2;
        
            animation: slideInLeft 1s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(189, 53, 230, 0.2);
            color: #a417e1;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(177, 53, 230, 0.3);
            animation: pulse 2s infinite;
            font-weight: 600;
            font-family: "Varela Round", sans-serif;
            
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: bold;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-family: "Rowdies", sans-serif;
        }

       

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
            font-family: "Varela Round", sans-serif;
        }


        .hero-image {
            position: relative;
            animation: slideInRight 1s ease-out;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-person {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .hero-person:hover {
            transform: translateY(-10px);
        }

        .scroll-down-btn {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            animation: bounce 2s infinite;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(164, 23, 225, 0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .pulse-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 2px solid #a417e1;
            border-radius: 50%;
            animation: ringPulse 2s infinite;
            pointer-events: none;
        }

        @keyframes ringPulse {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .scroll-down-btn:hover {
            color: #a417e1;
            transform: translateX(-50%) translateY(5px);
            background: rgba(255, 255, 255, 0.2);
            border-color: #a417e1;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-10px);
            }
            60% {
                transform: translateX(-50%) translateY(-5px);
            }
        }

/* RESPONSIVE STYLES */

/* Tablets y pantallas medianas */
@media (max-width: 1024px) {
    .hero-content {
        padding-top: 80px;
    }
    .container1 {
        padding: 0 80px;
        padding-top: 60px;
    }
    
    .hero-content {
        gap: 3rem;
    }
    
    .hero-title {
        font-size: 3.2rem;
    }
    
}

/* Tablets pequeñas */
@media (max-width: 768px) {
    .hero-content {
        padding-top: 80px;
    }
    .container1 {
        padding: 0 40px;
        padding-top: 40px;
    }
    
    .hero-content {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .hero-title {
        font-size: 2.8rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .hero-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
}

/* Móviles */
/* Móviles */
@media (max-width: 480px) {
    .container1 {
        padding: 15px;
        padding-top: 30px;
        min-height: 100vh;
        overflow-x: hidden; /* Evita desbordamiento horizontal */
    }
    
    .hero-content {
        padding-top: 60px;
        padding-right: 15px;
        padding-left: 15px;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        max-width: 100%;
    }
    
    .hero-text {
        order: 2; /* Texto primero */
        width: 100%;
        text-align: center;
    }
    
    .hero-image {
        order: 1; /* Imagen después */
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 1rem;
    }
    
    .hero-badge {
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem;
        margin-bottom: 1rem;
        word-wrap: break-word;
        max-width: 100%;
        display: inline-block;
    }
    
    .hero-title {
        font-size: 1.8rem;
        line-height: 1.2;
        margin-bottom: 1rem;
        word-wrap: break-word;
        hyphens: auto;
        max-width: 100%;
    }
    
    .hero-subtitle {
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 1.5rem;
        word-wrap: break-word;
        hyphens: auto;
        max-width: 100%;
        text-align: center;
    }
    
    .hero-person img {
        max-width: 290px;
        height: auto;
        object-fit: contain;
    }
    
    /* Elementos decorativos menos intrusivos en móvil */
    .floating-element {
        display: none;
    }
    
    .grid-pattern {
        opacity: 0.3;
    }
}

/* Para móviles muy pequeños */
@media (max-width: 360px) {
    .container1 {
        padding: 10px;
    }
    
    .hero-content {
        padding-top: 50px;
        padding-right: 10px;
        padding-left: 10px;
    }
    
    .hero-title {
        font-size: 1.6rem;
    }
    
    .hero-subtitle {
        font-size: 0.85rem;
    }
    
    .hero-person img {
        max-width: 200px;
    }
    
}
/* Common styles for all sections */
.section-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(189, 53, 230, 0.2);
    color: #a417e1;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(177, 53, 230, 0.3);
    font-weight: 600;
    font-family: "Varela Round", sans-serif;
}

.section-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #ffffffff;
    margin-bottom: 1rem;
    line-height: 1.2;
    font-family: "Rowdies", sans-serif;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #e8e8e8ff;
    margin-bottom: 2rem;
    line-height: 1.6;
    font-family: "Varela Round", sans-serif;
    
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

/* Container 2: Services Section */
.container2 {
    position: relative;
    padding: 5rem 2rem;
    background-image: url(../imagenes/fondocont2.jpg);
    background-position: center;      
    background-repeat: no-repeat;     
    background-size: cover;           
    z-index: 1;
    border-radius: 200px 200px 0 0;
}

/* Capa transparente encima del fondo */
.container2::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.66); /* Color y opacidad de la capa (negro 40%) */
    z-index: 2;
}

/* Asegura que el contenido quede sobre la capa transparente */
.container2 > * {
    position: relative;
    z-index: 3;
}


.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.service-card {
    background: #f4f4f447;
    border-radius: 15px;
    padding: 2.5rem 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    text-align: center;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

/* Estilo base de los íconos */
.service-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    color: white;
    transition: transform 0.3s ease;
}

/* Efecto hover opcional */
.service-icon:hover {
    transform: scale(1.1);
}

/* Colores de cada servicio */
.services-grid .service-card:nth-child(1) .service-icon {
    background: #c45116;
}

.services-grid .service-card:nth-child(2) .service-icon {
    background: #ef8b3a;
}

.services-grid .service-card:nth-child(3) .service-icon {
    background: #9bcf53;
}

.services-grid .service-card:nth-child(4) .service-icon { background: #5b2b76; }
.services-grid .service-card:nth-child(5) .service-icon { background: #9b9b9b; }
.services-grid .service-card:nth-child(6) .service-icon { background: #117e8c; }

.services-grid .service-card:nth-child(1) .service-title { color: #c45116; }
.services-grid .service-card:nth-child(2) .service-title { color: #ef8b3a; }
.services-grid .service-card:nth-child(3) .service-title { color: #9bcf53; }
.services-grid .service-card:nth-child(4) .service-title { color: #8b5aa7; }
.services-grid .service-card:nth-child(5) .service-title { color: #b9b9b9; }
.services-grid .service-card:nth-child(6) .service-title { color: #2ba6b5; }

.service-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #ffffffff;
    font-family: "Rowdies", sans-serif;
}

.service-description {
    color: #e6e6e6ff;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-family: "Varela Round", sans-serif;
}

.service-link {
    color: #a855f7;
    text-decoration: none;
    font-weight: 600;
    font-family: "Varela Round", sans-serif;
    transition: all 0.3s ease;
}

.service-link:hover {
    color: #3b82f6;
}

/* Container 3: About Section */
.container3 {
    padding: 5rem 2rem;
    background-image: linear-gradient(rgba(0, 0, 0, .38), rgba(0, 0, 0, .38)), url('../imagenes/herofondo.png');
    background-position: center;      
    background-repeat: no-repeat;     
    background-size: cover;           
    z-index: 1;
}

.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.about-image {
    position: relative;
}

.about-image img {
    width: 100%;
    border-radius: 15px;
    border: 2px solid rgba(17, 126, 140, .55);
    box-shadow: 0 15px 30px rgba(0, 0, 0, .3);
}

.experience-badge {
    position: absolute;
    bottom: -20px;
    right: -20px;
    background: #ef6c22;
    border: 2px solid #f5a900;
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 24px rgba(239, 108, 34, .28);
}

.container3 .section-badge {
    background: rgba(17, 126, 140, .18);
    color: #5fc2ce;
    border: 1px solid rgba(17, 126, 140, .6);
}

.container3 .section-title {
    color: #f5a900;
}

.experience-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    font-family: "Rowdies", sans-serif;
}

.experience-text {
    font-size: 0.9rem;
    font-family: "Varela Round", sans-serif;
}

.about-text .section-header {
    text-align: left;
    margin-bottom: 2rem;
}

.features-list {
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.feature-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background: rgba(239, 108, 34, .15);
    border-radius: 50%;
    color: #ef6c22;
    margin-right: 1rem;
    flex-shrink: 0;
}

.container3 .feature-item:nth-child(2) .feature-icon {
    background: rgba(125, 165, 51, .16);
    color: #9bcf53;
}

.container3 .feature-item:nth-child(3) .feature-icon {
    background: rgba(91, 43, 118, .25);
    color: #a978c2;
}

.feature-text h4 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #d6d6d6ff;
    font-family: "Rowdies", sans-serif;
}

.feature-text p {
    color: #ebebebff;
    font-family: "Varela Round", sans-serif;
}

.cta-button {
    display: inline-block;
    background: linear-gradient(45deg, #a855f7, #3b82f6);
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-family: "Varela Round", sans-serif;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(168, 85, 247, 0.3);
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4);
}

.container3 .cta-button {
    background: #117e8c;
    border: 1px solid #f5a900;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(17, 126, 140, .3);
}

.container3 .cta-button:hover {
    background: #1594a4;
    box-shadow: 0 14px 30px rgba(17, 126, 140, .4);
}

/* Container 4: Portfolio Section */
.container4 {
    padding: 5rem 2rem;
    background: #080808;
}

.container4 .section-badge {
    background: rgba(239, 108, 34, .15);
    color: #ef8b3a;
    border: 1px solid rgba(239, 108, 34, .55);
}

.container4 .section-title { color: #5fc2ce; }

.portfolio-filters {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
}

.filter-btn {
    background: transparent;
    border: 1px solid #117e8c;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    cursor: pointer;
    font-family: "Varela Round", sans-serif;
    font-weight: 600;
    color: #b9b9b9;
    transition: all 0.3s ease;
}

.filter-btn.active,
.filter-btn:hover {
    background: #117e8c;
    color: white;
    border-color: #f5a900;
}

.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.portfolio-item {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    border: 1px solid rgba(17, 126, 140, .45);
    box-shadow: 0 10px 30px rgba(17, 126, 140, .12);
    cursor: pointer;
}

.portfolio-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.portfolio-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.portfolio-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.8));
    display: flex;
    align-items: flex-end;
    padding: 2rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.portfolio-item:hover .portfolio-overlay {
    opacity: 1;
}

.portfolio-item:hover .portfolio-image img {
    transform: scale(1.1);
}

.portfolio-info {
    color: white;
}

.portfolio-info h3 {
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
    font-family: "Rowdies", sans-serif;
}

.portfolio-info p {
    margin-bottom: 1rem;
    font-family: "Varela Round", sans-serif;
}

.portfolio-link {
    color: #f5a900;
    text-decoration: none;
    font-weight: 600;
    font-family: "Varela Round", sans-serif;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.portfolio-link:hover {
    transform: translateX(5px);
}

/* Container 5: Testimonials Section */
.container5 {
    padding: 5rem 2rem;
    background-color: #161616;
    position: relative;
    border-radius: 200px 200px 0 0;
}

.container5 .section-badge {
    background: rgba(91, 43, 118, .28);
    color: #b98bce;
    border: 1px solid rgba(91, 43, 118, .75);
}

.container5 .section-title { color: #ef8b3a; }

.testimonials-slider {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

.testimonial-card {
    display: none;
    background: rgba(185, 185, 185, .88);
    border: 2px solid #117e8c;
    border-left: 7px solid #ef6c22;
    border-radius: 15px;
    padding: 3rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.testimonial-card.active {
    display: block;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.testimonial-text {
    font-size: 1.2rem;
    color: #333;
    line-height: 1.6;
    margin-bottom: 2rem;
    font-style: italic;
    font-family: "Varela Round", sans-serif;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.author-avatar {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    overflow: hidden;
    margin-right: 1rem;
    border: 3px solid #7da533;
    background: white;
}

.author-avatar img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.author-info h4 {
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
    color: #333;
    font-family: "Rowdies", sans-serif;
}

.author-info p {
    color: #666;
    font-family: "Varela Round", sans-serif;
}

.testimonial-controls {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.testimonial-prev,
.testimonial-next {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: #ef6c22;
    border: 1px solid #f5a900;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.testimonial-prev:hover,
.testimonial-next:hover {
    background: #117e8c;
    color: white;
    border-color: #f5a900;
}

/* Container 6: Contact Section */
.container6 {
    padding: 5rem 2rem;
    background: none;
}

.container6 .section-badge {
    background: rgba(17, 126, 140, .18);
    color: #5fc2ce;
    border: 1px solid rgba(17, 126, 140, .6);
}

.container6 .section-title { color: #f5a900; }

.contact-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-info .section-header {
    text-align: left;
    margin-bottom: 2rem;
}

.contact-details {
    margin-bottom: 2rem;
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}
.contact-item svg {
    
    color: #fffefeff
}
.contact-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 50px;
    height: 50px;
    background: #ef6c22;
    border-radius: 50%;
    color: #ffffffff;
    margin-right: 1rem;
    flex-shrink: 0;
}

.contact-details .contact-item:nth-child(2) .contact-icon { background: #7da533; }
.contact-details .contact-item:nth-child(3) .contact-icon { background: #5b2b76; }

.contact-text h4 {
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
    color: #fffefeff;
    font-family: "Rowdies", sans-serif;
}

.contact-text p {
    color: #d8d6d6ff;
    font-family: "Varela Round", sans-serif;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-links a {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background: rgba(17, 126, 140, .18);
    border-radius: 50%;
    color: #5fc2ce;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: #ef6c22;
    color: white;
}

.contact-form {
    background: rgba(185, 185, 185, .94);
    border: 2px solid rgba(17, 126, 140, .65);
    border-radius: 15px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-family: "Varela Round", sans-serif;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #117e8c;
    box-shadow: 0 0 0 3px rgba(17, 126, 140, .15);
    outline: none;
}

.custom-select {
    position: relative;
    width: 100%;
    font-family: "Varela Round", sans-serif;
}

.custom-select-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: white;
    color: #555;
    text-align: left;
    cursor: pointer;
    transition: border-color .25s ease, box-shadow .25s ease;
}

.custom-select.open .custom-select-trigger {
    border-color: #117e8c;
    box-shadow: 0 0 0 3px rgba(17, 126, 140, .15);
}

.custom-select-trigger svg {
    color: #117e8c;
    transition: transform .25s ease;
}

.custom-select.open .custom-select-trigger svg { transform: rotate(180deg); }

.custom-select-options {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 8px);
    z-index: 20;
    max-height: 260px;
    overflow-y: auto;
    padding: .45rem;
    border: 1px solid rgba(17, 126, 140, .55);
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 16px 35px rgba(0, 0, 0, .22);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
}

.custom-select.open .custom-select-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.custom-select-option {
    display: block;
    width: 100%;
    padding: .78rem .9rem;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #333;
    text-align: left;
    cursor: pointer;
    transition: background .2s ease, color .2s ease;
}

.custom-select-option:hover,
.custom-select-option.selected {
    background: #117e8c;
    color: white;
}

.submit-btn {
    width: 100%;
    padding: 1rem;
    background: #117e8c;
    color: white;
    border: 1px solid #f5a900;
    border-radius: 10px;
    font-family: "Varela Round", sans-serif;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(17, 126, 140, .3);
}

.submit-btn:hover {
    background: #1594a4;
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgba(17, 126, 140, .4);
}

/* Container 7: Footer */
.container7 {
    background-color: #111;
    color: white;
    padding: 3rem 2rem 1rem;
}

.container7 .footer-column h4 { color: #f5a900; }

.footer-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
}

.footer-logo {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.footer-logo img {
    height: 50px;
    margin-bottom: 1rem;
}

.footer-logo p {
    color: #aaa;
    font-family: "Varela Round", sans-serif;
    max-width: 300px;
}

.footer-links {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.footer-column h4 {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    color: white;
    font-family: "Rowdies", sans-serif;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li {
    margin-bottom: 0.8rem;
}

.footer-column ul li a {
    color: #aaa;
    text-decoration: none;
    font-family: "Varela Round", sans-serif;
    transition: color 0.3s ease;
}

.footer-column ul li a:hover {
    color: #5fc2ce;
}

.footer-bottom {
    border-top: 1px solid rgba(17, 126, 140, .55);
    margin-top: 2rem;
    padding-top: 1.5rem;
    text-align: center;
    color: #aaa;
    font-family: "Varela Round", sans-serif;
}

/* Responsive Styles for All Containers */
@media (max-width: 1024px) {
    .services-grid,
    .portfolio-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .about-content,
    .contact-content {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    
    .about-image {
        order: 2;
    }
    
    .about-text {
        order: 1;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .footer-links {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 2rem;
    }
    
    .services-grid,
    .portfolio-grid {
        grid-template-columns: 1fr;
    }
    
    .testimonial-card {
        padding: 2rem;
    }
    
    .testimonial-text {
        font-size: 1.1rem;
    }
    
    .contact-form {
        padding: 2rem;
    }
    
    .footer-links {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (max-width: 480px) {
    .section-header {
        margin-bottom: 2rem;
    }
    
    .service-card,
    .contact-form {
        padding: 1.5rem;
    }
    
    .testimonial-card {
        padding: 1.5rem;
    }
    
    .testimonial-text {
        font-size: 1rem;
    }
    
    .portfolio-filters {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .filter-btn {
        padding: 0.4rem 1rem;
        font-size: 0.9rem;
    }
}


/*/*/


/* Mobile (480px and below) */
@media (max-width: 480px) {
    .container1 {
        padding: 90px 20px 30px;
        background-attachment: scroll; /* Disable fixed background for better performance */
    }
    
    .hero-content {
        gap: 1.5rem;
    }
    
    .hero-text {
        order: 1; /* Text appears first on mobile */
    }
    
    .hero-image {
        order: 2; /* Image appears after text on mobile */
    }
    
    .hero-badge {
        font-size: 0.75rem;
        padding: 0.3rem 0.7rem;
        margin-bottom: 0.6rem;
    }
    
    .hero-title {
        font-size: 2rem;
        line-height: 1.1;
        margin-bottom: 0.6rem;
    }
    
    .hero-subtitle {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1.2rem;
    }
    
    .hero-person {
        max-width: 300px;
        border-radius: 10px;
    }
    
    .hero-person img {
        max-height: 350px;
    }
    
    .floating-element {
        display: none; /* Hide floating elements on small mobile */
    }
    
    .grid-pattern {
        opacity: 0.3; /* Reduce pattern opacity on mobile */
    }
}

/* Small Mobile (360px and below) */
@media (max-width: 360px) {
    .container1 {
        padding: 80px 15px 20px;
    }
    
    .hero-badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    
    .hero-title {
        font-size: 1.8rem;
    }
    
    .hero-subtitle {
        font-size: 0.85rem;
    }
    
    .hero-person {
        max-width: 250px;
    }
    
    .hero-person img {
        max-height: 300px;
    }
}

/* Landscape Orientation */
@media (max-height: 600px) and (orientation: landscape) {
    .container1 {
        min-height: auto;
        padding: 80px 20px 40px;
    }
    
    .hero-content {
        gap: 2rem;
    }
    
    .hero-title {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .hero-subtitle {
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .hero-person {
        max-height: 300px;
    }
    
    .hero-person img {
        max-height: 300px;
        object-fit: cover;
    }
}



</style>
