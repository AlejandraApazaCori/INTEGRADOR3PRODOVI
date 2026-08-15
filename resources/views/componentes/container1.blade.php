<style>
    html {
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #117e8c #111;
    }

    body {
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        width: 11px;
        height: 0;
    }

    ::-webkit-scrollbar-track {
        background: #111;
    }

    ::-webkit-scrollbar-thumb {
        min-height: 45px;
        border: 2px solid #111;
        border-radius: 10px;
        background: #117e8c;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #ef6c22;
    }

    ::-webkit-scrollbar-corner {
        background: #111;
    }

    .animate-container {
        --section-entry-x: -70px;
    }

    .animate-container.slide-right {
        --section-entry-x: 70px;
    }

    .container1 > .hero-content,
    .container2 > .section-header,
    .container2 > .services-grid,
    .container3 > .about-content,
    .container4 > .section-header,
    .container4 > .portfolio-filters,
    .container4 > .portfolio-grid,
    .container5 > .section-header,
    .container5 > .testimonials-slider,
    .container5 > .testimonial-controls,
    .container6 > .contact-content,
    .site-footer > .footer-container {
        opacity: 0;
        transform: translate3d(var(--section-entry-x), 38px, 0) scale(.98);
    }

    .container1.section-entered > .hero-content,
    .container2.section-entered > .section-header,
    .container2.section-entered > .services-grid,
    .container3.section-entered > .about-content,
    .container4.section-entered > .section-header,
    .container4.section-entered > .portfolio-filters,
    .container4.section-entered > .portfolio-grid,
    .container5.section-entered > .section-header,
    .container5.section-entered > .testimonials-slider,
    .container5.section-entered > .testimonial-controls,
    .container6.section-entered > .contact-content,
    .site-footer.section-entered > .footer-container {
        animation: section-content-enter .9s cubic-bezier(.22, 1, .36, 1) both;
    }

    .container2.section-entered > .services-grid,
    .container4.section-entered > .portfolio-filters,
    .container5.section-entered > .testimonials-slider { animation-delay: .14s; }

    .container4.section-entered > .portfolio-grid,
    .container5.section-entered > .testimonial-controls { animation-delay: .28s; }

    .animate-container .hero-text,
    .animate-container .hero-image {
        animation: none !important;
    }

    @keyframes section-content-enter {
        from {
            opacity: 0;
            transform: translate3d(var(--section-entry-x), 38px, 0) scale(.98);
            filter: blur(7px);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .container1 > .hero-content,
        .container2 > .section-header,
        .container2 > .services-grid,
        .container3 > .about-content,
        .container4 > .section-header,
        .container4 > .portfolio-filters,
        .container4 > .portfolio-grid,
        .container5 > .section-header,
        .container5 > .testimonials-slider,
        .container5 > .testimonial-controls,
        .container6 > .contact-content,
        .site-footer > .footer-container {
            opacity: 1;
            transform: none;
        }

        .animate-container.section-entered > * {
            animation: none;
        }
    }

    .container1 .hero-color-strip {
        display: flex;
        align-items: center;
        gap: 4px;
        width: min(100%, 190px);
        height: 7px;
        margin-bottom: 1.35rem;
    }

    .container1 .hero-color-strip span {
        display: block;
        height: 100%;
        flex: 1;
        border-radius: 1px;
        box-shadow: 0 5px 14px rgba(0, 0, 0, .18);
        transform: scaleX(0);
        transform-origin: left;
        animation: hero-color-reveal .55s ease forwards;
    }

    .container1 .hero-color-strip span:nth-child(1) { background: #5b2b76; animation-delay: .15s; }
    .container1 .hero-color-strip span:nth-child(2) { background: #ef6c22; animation-delay: .21s; }
    .container1 .hero-color-strip span:nth-child(3) { background: #f5a900; animation-delay: .27s; }
    .container1 .hero-color-strip span:nth-child(4) { background: #7da533; animation-delay: .33s; }
    .container1 .hero-color-strip span:nth-child(5) { background: #117e8c; animation-delay: .39s; }
    .container1 .hero-color-strip span:nth-child(6) { background: #b9b9b9; animation-delay: .45s; }

    .container1 .hero-title-accent {
        text-shadow: none;
    }

    .container1 .hero-title-accent span { color: #117e8c; }

    .container1 .hero-plans-button {
        background: linear-gradient(135deg, #117e8c 0%, #0d6672 100%);
        border: 1px solid rgba(245, 169, 0, .72);
        color: #fff;
        box-shadow: 0 14px 32px rgba(17, 126, 140, .32);
    }

    .container1 .hero-plans-button:hover {
        background: linear-gradient(135deg, #1594a4 0%, #117e8c 100%);
        border-color: #f5a900;
        box-shadow: 0 19px 40px rgba(17, 126, 140, .42);
    }

    .container1 .scroll-down-btn {
        background: #ef6c22;
        color: #fff4dc;
        border-radius: 10px;
        border: 2px solid #f5a900;
        outline: 1px solid rgba(91, 43, 24, .7);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .28), 0 5px 0 #8f3e17, 0 10px 22px rgba(0, 0, 0, .3);
        backdrop-filter: none;
        animation: vintage-float 2.4s ease-in-out infinite;
    }

    .container1 .scroll-down-btn:hover {
        background: #d95b18;
        color: #fff4dc;
        border-color: #ffd166;
        transform: translateX(-50%) translateY(3px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25), 0 2px 0 #8f3e17, 0 6px 15px rgba(0, 0, 0, .3);
    }

    .container1 .pulse-ring {
        border-color: #f5a900;
        border-radius: 10px;
        animation: vintage-ring 2.4s ease-out infinite;
    }

    @keyframes vintage-float {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50% { transform: translateX(-50%) translateY(-6px); }
    }

    @keyframes vintage-ring {
        0% { transform: scale(.9); opacity: .65; }
        75%, 100% { transform: scale(1.45); opacity: 0; }
    }

    .container1 .hero-reactions {
        position: absolute;
        top: 80px;
        right: clamp(1rem, 3vw, 3.5rem);
        bottom: 0;
        width: clamp(90px, 9vw, 140px);
        overflow: hidden;
        pointer-events: none;
        z-index: 4;
    }

    .container1 .hero-reaction {
        position: absolute;
        bottom: -70px;
        width: var(--reaction-size);
        height: var(--reaction-size);
        object-fit: contain;
        opacity: 0;
        filter: drop-shadow(0 8px 12px rgba(0, 0, 0, .3));
        animation: hero-reaction-rise 20.4s linear infinite;
        animation-delay: var(--reaction-delay);
    }

    .container1 .hero-reaction:nth-child(odd) { left: 8%; }
    .container1 .hero-reaction:nth-child(even) { right: 6%; }

    @keyframes hero-reaction-rise {
        0% { transform: translateY(0) scale(.72); opacity: 0; }
        10% { opacity: 1; }
        82% { opacity: 1; }
        100% { transform: translateY(calc(-100vh - 150px)) scale(1.05); opacity: 0; }
    }

    @media (max-width: 768px) {
        .container1 .hero-reactions {
            right: .5rem;
            width: 70px;
            opacity: .85;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .container1 .hero-reactions { display: none; }
    }

    .container1 .hero-person {
        --reveal-x: 50%;
        --reveal-y: 50%;
        cursor: none;
        overflow: visible;
    }

    .container1 .hero-image {
        padding-bottom: clamp(2.5rem, 5vw, 5rem);
    }

    .container1 .hero-person .hero-person-color {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: contain;
        pointer-events: none;
        clip-path: circle(0 at var(--reveal-x) var(--reveal-y));
        transition: clip-path .28s ease;
        z-index: 2;
    }

    .container1 .hero-person.is-revealing .hero-person-color {
        clip-path: circle(105px at var(--reveal-x) var(--reveal-y));
        transition: clip-path .08s linear;
    }

    .container1 .hero-reveal-cursor {
        position: absolute;
        left: var(--reveal-x);
        top: var(--reveal-y);
        width: 210px;
        height: 210px;
        border: 2px solid #f5a900;
        border-radius: 50%;
        box-shadow: 0 0 0 5px rgba(17, 126, 140, .18), 0 0 28px rgba(245, 169, 0, .35);
        opacity: 0;
        pointer-events: none;
        transform: translate(-50%, -50%) scale(.75);
        transition: opacity .2s ease, transform .2s ease;
        z-index: 3;
    }

    .container1 .hero-person.is-revealing .hero-reveal-cursor {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    @media (hover: none) {
        .container1 .hero-person { cursor: auto; }
        .container1 .hero-person .hero-person-color { clip-path: circle(150% at 50% 50%); }
        .container1 .hero-reveal-cursor { display: none; }
    }

    @keyframes hero-color-reveal {
        to { transform: scaleX(1); }
    }

    @media (min-width: 1025px) {
        .container1 .hero-content {
            grid-template-columns: minmax(540px, 1.08fr) minmax(390px, .92fr);
            gap: clamp(2.5rem, 5vw, 5.5rem);
            max-width: 1380px;
            margin-inline: auto;
        }

        .container1 .hero-text {
            width: 100%;
            max-width: 620px;
            padding: 2.25rem 0;
        }

        .container1 .hero-badge {
            margin-bottom: 1.35rem;
            padding: .58rem 1rem;
            border: 1px solid rgba(196, 120, 255, .32);
            background: rgba(123, 46, 190, .18);
            box-shadow: 0 10px 30px rgba(120, 35, 190, .14);
            letter-spacing: .12em;
            backdrop-filter: blur(10px);
        }

        .container1 .hero-title {
            width: 100%;
            max-width: 620px;
            margin: 0 0 1.35rem;
            font-size: 3.7rem;
            font-weight: 700;
            line-height: .98;
            letter-spacing: -.045em;
            text-wrap: balance;
            text-shadow: 0 10px 35px rgba(0, 0, 0, .28);
        }

        .container1 .hero-title-line,
        .container1 .hero-title-accent {
            display: block;
        }

        .container1 .hero-title-line {
            margin-bottom: .12em;
            color: #fff;
        }

        .container1 .hero-title-accent {
            width: 100%;
            padding-right: .06em;
        }

        .container1 .hero-subtitle {
            width: 100%;
            max-width: 620px;
            margin-bottom: 1.8rem;
            color: rgba(255, 255, 255, .82);
            font-size: clamp(1rem, 1.15vw, 1.16rem);
            line-height: 1.75;
            text-wrap: pretty;
        }

        .container1 .hero-plans-button {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            padding: .9rem 1.5rem;
            border-radius: 10px;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .container1 .hero-plans-button::after {
            content: "→";
            font-size: 1.15em;
            transition: transform .25s ease;
        }

        .container1 .hero-plans-button:hover {
            transform: translateY(-3px);
        }

        .container1 .hero-plans-button:hover::after {
            transform: translateX(4px);
        }
    }

    @media (max-width: 1024px) {
        .container1 .hero-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
        }

        .container1 .hero-text { display: contents; }
        .container1 .hero-color-strip { order: 1; margin: 0 auto; }
        .container1 .hero-image { order: 2; width: 100%; padding-bottom: 0; }
        .container1 .hero-title { order: 3; width: 100%; margin: 0; }
        .container1 .hero-subtitle { order: 4; width: min(100%, 620px); margin: 0; }
        .container1 .hero-plans-button { order: 5; }

        .container1 .hero-title-accent {
            display: block;
            width: 100%;
            overflow: visible;
            padding: 0 .12em .08em;
            line-height: 1.18;
        }

        .container1 .hero-title-accent span {
            display: inline-block;
            white-space: nowrap;
        }

        .container3 .about-text {
            width: 100%;
            text-align: center;
        }

        .container3 .section-badge {
            justify-content: center;
            margin-inline: auto;
        }

        .container3 .section-subtitle {
            max-width: 680px;
            margin-inline: auto;
        }

        .container3 .features-list {
            width: min(100%, 680px);
            margin-inline: auto;
        }

        .container3 .feature-item {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .container3 .feature-icon {
            margin: 0 0 .75rem;
        }
    }

    /* Tablet */
    @media (min-width: 769px) and (max-width: 1024px) {
        .container1.container1 {
            min-height: auto;
            padding: 110px 36px 80px;
            border-radius: 0 0 80px 80px;
        }

        .container1 .hero-content {
            gap: 1.1rem;
            padding-top: 0;
        }

        .container1 .hero-title {
            font-size: clamp(2.6rem, 5vw, 3.25rem);
            line-height: 1.05;
        }

        .container1 .hero-subtitle { font-size: 1rem; }
        .container1 .hero-person { width: min(100%, 430px); margin-inline: auto; }
        .container1 .hero-person img { max-width: 390px; }
        .container1 .hero-image { padding-bottom: 0; }
        .container1 .hero-reactions { right: .5rem; width: 78px; }

        .container2,
        .container3,
        .container4,
        .container5,
        .container6 {
            padding: 4.5rem 2rem;
        }

        .container2 .services-grid,
        .container4 .portfolio-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .container3 .about-content,
        .container6 .contact-content {
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        .container3 .about-image {
            width: min(100%, 680px);
            margin-inline: auto;
            order: 2;
        }

        .container3 .about-text { order: 1; }
        .container5 .testimonials-slider { max-width: 720px; }
    }

    /* Celulares */
    @media (max-width: 768px) {
        .container1.container1 {
            min-height: auto;
            padding: 100px 18px 64px;
            border-radius: 0 0 42px 42px;
        }

        .container1 .hero-content {
            display: flex;
            flex-direction: column;
            gap: .9rem;
            padding: 0;
            text-align: center;
        }

        .container1 .hero-text {
            order: 1;
            width: 100%;
            padding: 0;
        }

        .container1 .hero-image {
            order: 2;
            width: 100%;
            padding-bottom: 0;
        }

        .container1 .hero-color-strip {
            width: min(70%, 190px);
            margin-inline: auto;
        }

        .container1 .hero-title {
            margin: 0;
            font-size: clamp(1.8rem, 8.4vw, 2.65rem);
            line-height: 1.08;
            letter-spacing: -.025em;
            overflow: visible;
        }

        .container1 .hero-title-line,
        .container1 .hero-title-accent { display: block; }

        .container1 .hero-subtitle {
            max-width: 560px;
            margin: 0 auto;
            font-size: .98rem;
            line-height: 1.6;
        }

        .container1 .hero-plans-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: min(100%, 290px);
            padding: .9rem 1.2rem;
            border-radius: 10px;
        }

        .container1 .hero-person {
            width: min(100%, 390px);
            margin-inline: auto;
        }

        .container1 .hero-person img {
            width: 100%;
            max-width: 340px;
            max-height: 390px;
        }

        .container1 .hero-reactions,
        .container1 .scroll-down-btn { display: none; }

        .container2,
        .container3,
        .container4,
        .container5,
        .container6 {
            padding: 3.75rem 1.15rem;
            border-radius: 0;
        }

        .container2 .section-header,
        .container4 .section-header,
        .container5 .section-header {
            margin-bottom: 2.25rem;
        }

        .container2 .section-title,
        .container3 .section-title,
        .container4 .section-title,
        .container5 .section-title,
        .container6 .section-title {
            font-size: clamp(1.75rem, 8vw, 2.25rem);
            line-height: 1.15;
        }

        .container2 .section-subtitle,
        .container3 .section-subtitle,
        .container4 .section-subtitle,
        .container5 .section-subtitle,
        .container6 .section-subtitle {
            font-size: .96rem;
            line-height: 1.6;
        }

        .container2 .services-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
        }

        .container4 .portfolio-grid { grid-template-columns: 1fr; gap: 1.25rem; }

        .container2 .service-card {
            padding: 1.6rem 1.25rem;
            border-radius: 12px;
        }

        .container2 .service-icon {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
        }

        .container2 .service-icon svg { width: 36px; height: 36px; }
        .container2 .service-title { font-size: 1.3rem; }

        .container3 .about-content,
        .container6 .contact-content {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        .container3 .about-text { order: 1; }
        .container3 .about-image { order: 2; width: 100%; }
        .container3 .about-image img { border-radius: 10px; }

        .container3 .experience-badge {
            right: 12px;
            bottom: -18px;
            padding: 1rem;
            border-radius: 10px;
        }

        .container3 .feature-item { gap: .8rem; }
        .container3 .feature-icon { margin-right: 0; }
        .container3 .feature-text { min-width: 0; }

        .container4 .portfolio-filters {
            flex-wrap: wrap;
            gap: .65rem;
            margin-bottom: 2rem;
        }

        .container4 .filter-btn {
            flex: 1 1 calc(50% - .65rem);
            min-width: 130px;
            padding: .65rem .8rem;
            border-radius: 10px;
        }

        .container4 .portfolio-image { height: 240px; }
        .container4 .portfolio-overlay { padding: 1.25rem; }

        .container5 .testimonial-card {
            padding: 1.5rem 1.2rem;
            border-radius: 10px;
        }

        .container5 .testimonial-text {
            font-size: 1rem;
            line-height: 1.6;
        }

        .container5 .testimonial-author { align-items: flex-start; }
        .container5 .author-avatar { width: 62px; height: 62px; flex-shrink: 0; }
        .container5 .author-info { min-width: 0; }
        .container5 .testimonial-prev,
        .container5 .testimonial-next { width: 44px; height: 44px; }

        .container6 .contact-item { align-items: flex-start; }
        .container6 .contact-icon { width: 44px; height: 44px; }
        .container6 .contact-text { min-width: 0; }
        .container6 .contact-text p { overflow-wrap: anywhere; }

        .container6 .contact-form {
            padding: 1.35rem;
            border-radius: 10px;
        }

        .container6 .form-group input,
        .container6 .form-group textarea,
        .container6 .custom-select-trigger { font-size: 16px; }

        .container6 .social-links { flex-wrap: wrap; }
    }

    @media (max-width: 420px) {
        .container1.container1 { padding-inline: 14px; }
        .container1 .hero-title { font-size: 1.9rem; }
        .container1 .hero-subtitle { font-size: .9rem; }
        .container1 .hero-person img { max-width: 285px; }

        .container2,
        .container3,
        .container4,
        .container5,
        .container6 { padding-inline: .9rem; }

        .container2 .services-grid { gap: .75rem; }
        .container2 .service-card { padding: 1.1rem .75rem; }
        .container2 .service-icon { width: 54px; height: 54px; }
        .container2 .service-icon svg { width: 30px; height: 30px; }
        .container2 .service-title { font-size: 1.02rem; overflow-wrap: anywhere; }
        .container2 .service-description { font-size: .82rem; line-height: 1.45; }

        .container3 .feature-item { flex-direction: column; align-items: center; text-align: center; }
        .container4 .filter-btn { flex-basis: 100%; }
        .container5 .testimonial-author { flex-direction: column; gap: .8rem; }
        .container5 .author-avatar { margin-right: 0; }
        .container6 .contact-form { padding: 1rem; }
    }

    @media (max-width: 1024px) {
        .container1 .hero-reactions {
            display: block;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: .78;
            overflow: hidden;
        }

        .container1 .hero-reaction {
            right: auto;
            bottom: -60px;
            animation-name: hero-reaction-rise-responsive;
            animation-duration: 22s;
        }

        .container1 .hero-reaction:nth-child(1) { left: 7%; }
        .container1 .hero-reaction:nth-child(2) { left: 23%; }
        .container1 .hero-reaction:nth-child(3) { left: 39%; }
        .container1 .hero-reaction:nth-child(4) { left: 56%; }
        .container1 .hero-reaction:nth-child(5) { left: 73%; }
        .container1 .hero-reaction:nth-child(6) { left: 88%; }

        @keyframes hero-reaction-rise-responsive {
            0% { transform: translateY(0) scale(.65); opacity: 0; }
            12% { opacity: .9; }
            82% { opacity: .8; }
            100% { transform: translateY(-125vh) scale(.9); opacity: 0; }
        }
    }

    @media (max-width: 768px) {
        .container2.container2,
        .container5.container5 {
            border-radius: 0;
        }

        .container1 .hero-reactions { display: block; }
    }
</style>

     <div class="container1 animate-container slide-left" id="inicio">
        <div class="grid-pattern"></div>
        
       
        
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-color-strip" aria-hidden="true">
                    <span></span><span></span><span></span>
                    <span></span><span></span><span></span>
                </div>
                <h1 class="hero-title">
                    <span class="hero-title-line">Somos expertos</span>
                    <span class="hero-title-accent"><span>potenciando</span> <span>tus</span> <span>redes</span></span>
                </h1>
                <p class="hero-subtitle">
                    Creatividad, producción y estrategia para llevar tu marca al siguiente nivel. Tenemos lo necesario para ser tu empresa de marketing digital.
                </p>
               
                <a href="{{ route('login') }}" class="cta-button hero-plans-button">Conoce nuestros planes</a>
            </div>
            <div class="hero-image">
                <div class="hero-person">
                    <img class="hero-person-base" src="{{ asset('imagenes/personahero.png') }}" alt="Hero Person">
                    <img class="hero-person-color" src="{{ asset('imagenes/hombre-color.png') }}" alt="" aria-hidden="true">
                    <span class="hero-reveal-cursor" aria-hidden="true"></span>
                </div>
            </div>
        </div>
        <div class="hero-reactions" aria-hidden="true">
            <img class="hero-reaction" style="--reaction-size: 48px; --reaction-delay: 0s;" src="{{ asset('imagenes/landing/icono-like.png') }}" alt="">
            <img class="hero-reaction" style="--reaction-size: 42px; --reaction-delay: 3.4s;" src="{{ asset('imagenes/landing/icono-corazon.png') }}" alt="">
            <img class="hero-reaction" style="--reaction-size: 34px; --reaction-delay: 6.8s;" src="{{ asset('imagenes/landing/icono-like.png') }}" alt="">
            <img class="hero-reaction" style="--reaction-size: 52px; --reaction-delay: 10.2s;" src="{{ asset('imagenes/landing/icono-corazon.png') }}" alt="">
            <img class="hero-reaction" style="--reaction-size: 38px; --reaction-delay: 13.6s;" src="{{ asset('imagenes/landing/icono-like.png') }}" alt="">
            <img class="hero-reaction" style="--reaction-size: 32px; --reaction-delay: 17s;" src="{{ asset('imagenes/landing/icono-corazon.png') }}" alt="">
        </div>
       
    </div>

<div class="container2 animate-container slide-right" id="servicios">
    <div class="section-header">
        <h2 class="section-title">Nuestros Servicios</h2>
        <p class="section-subtitle">Ofrecemos soluciones integrales de marketing digital adaptadas a tus necesidades</p>
    </div>
    
    <div class="services-grid">
        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11v2"></path><path d="M6 9v6"></path><path d="M6 10l13-5v14L6 14"></path><path d="M8 15l2 5H7l-2-6"></path>
                </svg>
            </div>
            <h3 class="service-title">Publicidad y marketing</h3>
            <p class="service-description">Creamos campañas estratégicas para posicionar tu marca, atraer clientes y aumentar tus ventas.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </div>
            <h3 class="service-title">Redes sociales</h3>
            <p class="service-description">Gestionamos contenido y comunidades para fortalecer la presencia digital de tu marca.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="14" height="14" rx="2"></rect><path d="m17 10 4-2v8l-4-2"></path><path d="m8 9 5 3-5 3z"></path>
                </svg>
            </div>
            <h3 class="service-title">Producción audiovisual</h3>
            <p class="service-description">Producimos fotografías y videos creativos que comunican la esencia de tu proyecto.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M8 2v4M16 2v4M3 9h18"></path><path d="m9 15 2 2 4-4"></path>
                </svg>
            </div>
            <h3 class="service-title">Planificación de eventos</h3>
            <p class="service-description">Diseñamos y coordinamos experiencias memorables cuidando cada detalle del evento.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z"></path>
                </svg>
            </div>
            <h3 class="service-title">Planificación de bodas</h3>
            <p class="service-description">Convertimos cada boda en una celebración única, elegante y completamente personalizada.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path><path d="m19 3 .7 1.5L21 5l-1.3.5L19 7l-.7-1.5L17 5l1.3-.5z"></path>
                </svg>
            </div>
            <h3 class="service-title">Manejo de influencers</h3>
            <p class="service-description">Conectamos tu marca con creadores estratégicos y gestionamos colaboraciones de impacto.</p>
        </div>
    </div>
</div>


<div class="container3 animate-container slide-left" id="conocenos">
    <div class="about-content">
        <div class="about-image">
            <img src="{{ asset('imagenes/Equipo-Marketing.jpg') }}" alt="About Us">
            <div class="experience-badge">
                <span class="experience-number">5+</span>
                <span class="experience-text">Años de Experiencia</span>
            </div>
        </div>
        <div class="about-text">
            <div class="section-badge">SOBRE NOSOTROS</div>
            <h2 class="section-title">Tu Socio Estratégico en el Mundo Digital</h2>
            <p class="section-subtitle">En PRODOVI, combinamos creatividad y datos para crear estrategias de marketing que generan resultados reales y medibles.</p>
            
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <h4>Estrategias Personalizadas</h4>
                        <p>Adaptamos nuestras soluciones a las necesidades específicas de tu negocio.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <h4>Resultados Medibles</h4>
                        <p>Utilizamos analytics avanzados para demostrar el impacto de nuestras campañas.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">
                        <h4>Equipo Experto</h4>
                        <p>Contamos con especialistas en cada área del marketing digital.</p>
                    </div>
                </div>
            </div>
            
            <a href="#contact" class="cta-button">Conócenos</a>
        </div>
    </div>
</div>

<!-- Container 4: Portfolio Section -->
<div class="container4 animate-container slide-right" id="proyectos">
    <div class="section-header">
        <div class="section-badge">CASOS DE ÉXITO</div>
        <h2 class="section-title">Proyectos que han transformado marcas</h2>
        <p class="section-subtitle">Descubre cómo hemos ayudado a empresas como la tuya a alcanzar sus objetivos</p>
    </div>
    
    <div class="portfolio-filters">
        <button class="filter-btn active" data-filter="all">Todos</button>
        <button class="filter-btn" data-filter="social">Redes Sociales</button>
        <button class="filter-btn" data-filter="ads">Publicidad</button>
        <button class="filter-btn" data-filter="content">Contenido</button>
    </div>
    
    <div class="portfolio-grid">
        <div class="portfolio-item" data-category="social">
            <div class="portfolio-image">
                <img src="{{ asset('imagenes/proyecto1.gif') }}" alt="Portfolio Item 1">
                <div class="portfolio-overlay">
                    <div class="portfolio-info">
                        <h3>Campaña de Instagram para ModaLocal</h3>
                        <p>Aumento del 250% en engagement en 3 meses</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="portfolio-item" data-category="ads">
            <div class="portfolio-image">
                <img src="{{ asset('imagenes/proyecto2.jpg') }}" alt="Portfolio Item 2">
                <div class="portfolio-overlay">
                    <div class="portfolio-info">
                        <h3>Anuncios en Facebook para TechStart</h3>
                        <p>Reducción del 40% en costo por adquisición</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="portfolio-item" data-category="content">
            <div class="portfolio-image">
                <img src="{{ asset('imagenes/proyecto3.png') }}" alt="Portfolio Item 3">
                <div class="portfolio-overlay">
                    <div class="portfolio-info">
                        <h3>Estrategia de Contenido para FoodiePlace</h3>
                        <p>Posicionamiento en las primeras 3 búsquedas de Google</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Container 5: Testimonials Section -->
<div class="container5 animate-container slide-left">
    <div class="section-header">
        <div class="section-badge">TESTIMONIOS</div>
        <h2 class="section-title">Lo que Dicen Nuestros Clientes</h2>
        <p class="section-subtitle">La satisfacción de nuestros clientes es nuestra mejor carta de presentación</p>
    </div>
    
    <div class="testimonials-slider">
        <div class="testimonial-card active">
            <div class="testimonial-content">
                <div class="testimonial-text">
                    “PRODOVI comprendió el carácter institucional de nuestro club y nos ayudó a comunicar cada actividad con una imagen organizada, elegante y cercana a nuestros miembros.”
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="{{ asset('imagenes/landing/clientes/club-oficiales-navales.jpg') }}" alt="Club de Oficiales Navales">
                    </div>
                    <div class="author-info">
                        <h4>Club de Oficiales Navales</h4>
                        <p>Comunicación institucional y eventos</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-content">
                <div class="testimonial-text">
                    “El contenido refleja la personalidad de nuestra barbería. Ahora mostramos mejor nuestro trabajo y mantenemos una comunicación mucho más activa con nuestros clientes.”
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="{{ asset('imagenes/landing/clientes/guille-barber-shop.jpg') }}" alt="Guille Barber Shop">
                    </div>
                    <div class="author-info">
                        <h4>Guille Barber Shop</h4>
                        <p>Redes sociales y contenido</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-content">
                <div class="testimonial-text">
                    “Lograron presentar nuestros servicios odontológicos de manera profesional y sencilla. La nueva comunicación transmite confianza y facilita que más pacientes conozcan nuestros tratamientos.”
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="{{ asset('imagenes/landing/clientes/Marfil-odontologia.jpg') }}" alt="Marfil Odontología">
                    </div>
                    <div class="author-info">
                        <h4>Marfil Odontología</h4>
                        <p>Publicidad y marketing digital</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-content">
                <div class="testimonial-text">
                    “Las fotografías y piezas audiovisuales capturan la esencia de nuestra cocina. PRODOVI consiguió que cada publicación invite a descubrir nuestra propuesta mediterránea y española.”
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="{{ asset('imagenes/landing/clientes/sancho-panza-comida-mediterranea-espanola.jpg') }}" alt="Sancho Panza">
                    </div>
                    <div class="author-info">
                        <h4>Sancho Panza</h4>
                        <p>Producción audiovisual y redes sociales</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-content">
                <div class="testimonial-text">
                    “El equipo organizó nuestra comunicación digital y convirtió servicios técnicos en mensajes claros y atractivos. El resultado representa mejor la calidad de nuestra empresa.”
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="{{ asset('imagenes/landing/clientes/sirio-telecomunicaciones.jpeg') }}" alt="Sirio Telecomunicaciones">
                    </div>
                    <div class="author-info">
                        <h4>Sirio Telecomunicaciones</h4>
                        <p>Estrategia de comunicación digital</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="testimonial-controls">
        <button class="testimonial-prev">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button class="testimonial-next">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>
</div>

<!-- Container 6: Contact Section -->
<div class="container6 animate-container slide-right" id="contact">
    <div class="contact-content">
        <div class="contact-info">
            <div class="section-badge">CONTÁCTANOS</div>
            <h2 class="section-title">Hablemos de tu Proyecto</h2>
            <p class="section-subtitle">Estamos listos para llevar tu marca al siguiente nivel. Cuéntanos tus objetivos y te presentaremos una estrategia personalizada.</p>
            
            <div class="contact-details">
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="contact-text">
                        <h4>Teléfono</h4>
                        <p>79561365</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <div class="contact-text">
                        <h4>Email</h4>
                        <p>info@prodovi.com</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="contact-text">
                        <h4>Ubicación</h4>
                        <p>
              Real Plaza Hotel & Convention Center. Av. Arce #2177 (Frente a la Plaza Bolivia), La Paz, Bolivia, La Paz, Bolivia
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="social-links">
                <a href="https://www.facebook.com/PRODOVI" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/prodovi_agencia" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@prodovi" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
                <a href="https://wa.me/59179561365" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-1.76-.88-2.91-1.57-4.08-3.57-.31-.53.31-.49.88-1.64.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49 1.89.81 2.63.88 3.58.74 1.15-.17 1.76-1.02 2.01-2 .25-.98.25-1.81.17-1.98-.07-.17-.27-.25-.57-.4zM12.04 2a9.84 9.84 0 0 0-8.41 14.94L2.05 22l5.2-1.53A9.95 9.95 0 1 0 12.04 2zm0 17.98a8.04 8.04 0 0 1-4.1-1.12l-.29-.17-3.09.91.92-3.01-.19-.31a8.04 8.04 0 1 1 6.75 3.7z"/>
                    </svg>
                </a>
            </div>
        </div>
        
        <div class="contact-form">
            @php
                $serviciosContacto = [
                    'publicidad' => 'Publicidad y marketing',
                    'social' => 'Redes sociales',
                    'audiovisual' => 'Producción audiovisual',
                    'eventos' => 'Planificación de eventos',
                    'bodas' => 'Planificación de bodas',
                    'influencers' => 'Manejo de influencers',
                    'other' => 'Otro',
                ];
                $servicioSeleccionado = old('servicio');
            @endphp

            @if (session('contact_success'))
                <div class="contact-alert contact-alert-success" role="status">
                    {{ session('contact_success') }}
                </div>
            @endif

            @if (session('contact_warning'))
                <div class="contact-alert contact-alert-warning" role="alert">
                    {{ session('contact_warning') }}
                </div>
            @endif

            <div id="contactFormStatus" class="contact-alert" role="status" aria-live="polite" hidden></div>

            <form method="POST" action="{{ route('landing.contacto.store') }}" id="contactForm">
                @csrf
                <div class="form-group">
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre completo" maxlength="120" autocomplete="name" required>
                    @error('nombre')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <input type="email" name="correo" value="{{ old('correo') }}" placeholder="Correo electrónico" inputmode="email" autocomplete="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" maxlength="190" title="Ingresa un correo electrónico válido" required>
                    @error('correo')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <input type="tel" name="telefono" value="{{ old('telefono') }}" placeholder="Teléfono" inputmode="numeric" autocomplete="tel" pattern="[0-9]{7,15}" maxlength="15" title="Ingresa únicamente números" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('telefono')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <div class="custom-select @error('servicio') has-error @enderror" data-custom-select>
                        <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false" aria-required="true">
                            <span>{{ $serviciosContacto[$servicioSeleccionado] ?? 'Servicio de interés' }}</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                        <div class="custom-select-options" role="listbox">
                            @foreach ($serviciosContacto as $valor => $etiqueta)
                                <button type="button"
                                        class="custom-select-option {{ $servicioSeleccionado === $valor ? 'selected' : '' }}"
                                        role="option"
                                        aria-selected="{{ $servicioSeleccionado === $valor ? 'true' : 'false' }}"
                                        data-value="{{ $valor }}">{{ $etiqueta }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="servicio" value="{{ $servicioSeleccionado }}">
                    </div>
                    @error('servicio')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <textarea name="mensaje" placeholder="Cuéntanos sobre tu proyecto" rows="5" minlength="10" maxlength="2000" required>{{ old('mensaje') }}</textarea>
                    @error('mensaje')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group turnstile-group">
                    @if (config('services.turnstile.site_key'))
                        <div id="contactTurnstile" class="cf-turnstile"></div>
                    @else
                        <p class="turnstile-config-error">La verificación de seguridad no está configurada.</p>
                    @endif
                    @error('cf-turnstile-response')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="submit-btn" {{ config('services.turnstile.site_key') ? 'disabled' : '' }}>Enviar Mensaje</button>
            </form>
        </div>
    </div>
</div>

<script>
window.contactTurnstileWidgetId = null;

window.onTurnstileLoad = function() {
    window.contactTurnstileWidgetId = window.turnstile.render('#contactTurnstile', {
        sitekey: @json(config('services.turnstile.site_key')),
        theme: 'light',
        language: 'es',
        size: 'flexible',
        action: @json(config('services.turnstile.action')),
        callback: window.onTurnstileVerified,
        'expired-callback': window.onTurnstileExpired,
        'error-callback': window.onTurnstileError
    });
};

window.onTurnstileVerified = function() {
    const form = document.getElementById('contactForm');
    const button = form?.querySelector('.submit-btn');
    const statusBox = document.getElementById('contactFormStatus');

    if (button) button.disabled = false;
    if (statusBox && statusBox.classList.contains('contact-alert-error')) {
        statusBox.hidden = true;
    }
};

window.onTurnstileExpired = function() {
    const button = document.querySelector('#contactForm .submit-btn');
    if (button) button.disabled = true;
};

window.onTurnstileError = function() {
    const button = document.querySelector('#contactForm .submit-btn');
    const statusBox = document.getElementById('contactFormStatus');

    if (button) button.disabled = true;
    if (statusBox) {
        statusBox.className = 'contact-alert contact-alert-error';
        statusBox.textContent = 'No pudimos cargar la verificación de seguridad. Recarga la página e inténtalo nuevamente.';
        statusBox.hidden = false;
    }
};

    // Testimonial Slider
document.addEventListener('DOMContentLoaded', function() {
    const animatedSections = document.querySelectorAll('.animate-container');
    const heroSection = document.querySelector('.container1.animate-container');
    const preloader = document.getElementById('gallery-tunnel-preloader');

    const enterSection = section => {
        if (section) section.classList.add('section-entered');
    };

    const sectionObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                enterSection(entry.target);
                sectionObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: .18,
        rootMargin: '0px 0px -8% 0px'
    });

    animatedSections.forEach(section => {
        if (section !== heroSection) sectionObserver.observe(section);
    });

    if (!preloader || preloader.classList.contains('is-finished')) {
        requestAnimationFrame(() => enterSection(heroSection));
    } else {
        const loaderObserver = new MutationObserver(() => {
            if (preloader.classList.contains('is-finished')) {
                enterSection(heroSection);
                loaderObserver.disconnect();
            }
        });
        loaderObserver.observe(preloader, { attributes: true, attributeFilter: ['class'] });
    }

    const heroPerson = document.querySelector('.hero-person');

    if (heroPerson && window.matchMedia('(hover: hover)').matches) {
        heroPerson.addEventListener('mouseenter', () => heroPerson.classList.add('is-revealing'));
        heroPerson.addEventListener('mouseleave', () => heroPerson.classList.remove('is-revealing'));
        heroPerson.addEventListener('mousemove', function(e) {
            const bounds = this.getBoundingClientRect();
            this.style.setProperty('--reveal-x', `${e.clientX - bounds.left}px`);
            this.style.setProperty('--reveal-y', `${e.clientY - bounds.top}px`);
        });
    }

    const testimonialCards = document.querySelectorAll('.testimonial-card');
    const prevBtn = document.querySelector('.testimonial-prev');
    const nextBtn = document.querySelector('.testimonial-next');
    let currentIndex = 0;
    
    function showTestimonial(index) {
        testimonialCards.forEach(card => {
            card.classList.remove('active');
        });
        
        testimonialCards[index].classList.add('active');
    }
    
    function nextTestimonial() {
        currentIndex = (currentIndex + 1) % testimonialCards.length;
        showTestimonial(currentIndex);
    }
    
    function prevTestimonial() {
        currentIndex = (currentIndex - 1 + testimonialCards.length) % testimonialCards.length;
        showTestimonial(currentIndex);
    }
    
    nextBtn.addEventListener('click', nextTestimonial);
    prevBtn.addEventListener('click', prevTestimonial);
    
    // Auto-rotate testimonials
    setInterval(nextTestimonial, 5000);
    
    // Portfolio Filter
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter portfolio items
            const filter = this.getAttribute('data-filter');
            
            portfolioItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Form submission
    const contactForm = document.querySelector('.contact-form form');

    document.querySelectorAll('[data-custom-select]').forEach(customSelect => {
        const trigger = customSelect.querySelector('.custom-select-trigger');
        const triggerText = trigger.querySelector('span');
        const hiddenInput = customSelect.querySelector('input[type="hidden"]');
        const options = customSelect.querySelectorAll('.custom-select-option');

        trigger.addEventListener('click', () => {
            const isOpen = customSelect.classList.toggle('open');
            trigger.setAttribute('aria-expanded', String(isOpen));
        });

        options.forEach(option => {
            option.addEventListener('click', () => {
                options.forEach(item => {
                    item.classList.remove('selected');
                    item.setAttribute('aria-selected', 'false');
                });
                option.classList.add('selected');
                option.setAttribute('aria-selected', 'true');
                triggerText.textContent = option.textContent;
                hiddenInput.value = option.dataset.value;
                customSelect.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', event => {
            if (!customSelect.contains(event.target)) {
                customSelect.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        customSelect.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                customSelect.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            }
        });

        customSelect.closest('form')?.addEventListener('reset', () => {
            triggerText.textContent = 'Servicio de interés';
            hiddenInput.value = '';
            options.forEach(option => option.classList.remove('selected'));
        });
    });
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('.submit-btn');
            const statusBox = document.getElementById('contactFormStatus');
            const originalText = submitBtn.textContent;
            const turnstileWidget = this.querySelector('#contactTurnstile');
            const turnstileToken = this.querySelector('[name="cf-turnstile-response"]')?.value;

            if (turnstileWidget && !turnstileToken) {
                statusBox.className = 'contact-alert contact-alert-error';
                statusBox.textContent = 'Completa la verificación de seguridad antes de enviar.';
                statusBox.hidden = false;
                submitBtn.disabled = true;
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            statusBox.hidden = true;
            statusBox.textContent = '';
            statusBox.className = 'contact-alert';

            this.querySelectorAll('.form-error.ajax-error').forEach(error => error.remove());

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await response.json();
                } catch (_) {
                    data = { message: 'No pudimos procesar la respuesta del servidor. Inténtalo nuevamente.' };
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = this.querySelector(`[name="${field}"]`);
                            const formGroup = input?.closest('.form-group');

                            if (formGroup) {
                                const error = document.createElement('span');
                                error.className = 'form-error ajax-error';
                                error.textContent = messages[0];
                                formGroup.appendChild(error);
                            }
                        });
                    }

                    throw new Error(data.message || 'No pudimos enviar tu solicitud. Revisa los datos e inténtalo nuevamente.');
                }

                statusBox.className = data.status === 'warning'
                    ? 'contact-alert contact-alert-warning'
                    : 'contact-alert contact-alert-success';
                statusBox.textContent = data.message;
                statusBox.hidden = false;
                this.reset();
            } catch (error) {
                statusBox.className = 'contact-alert contact-alert-error';
                statusBox.textContent = error.message;
                statusBox.hidden = false;
            } finally {
                submitBtn.textContent = originalText;

                if (turnstileWidget && window.turnstile) {
                    window.turnstile.reset(window.contactTurnstileWidgetId);
                    submitBtn.disabled = true;
                } else {
                    submitBtn.disabled = false;
                }
            }
        });
    }
});
</script>
