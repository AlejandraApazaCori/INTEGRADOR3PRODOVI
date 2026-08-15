<div id="gallery-tunnel-preloader" aria-label="Cargando sitio">

    <div class="gallery-tunnel__scene" id="gallery-tunnel-scene" aria-hidden="true">
        <div class="gallery-tunnel__world" id="gallery-tunnel-world">

            <div class="gallery-tunnel__wall gallery-tunnel__wall--top" data-wall="top"></div>

            <div class="gallery-tunnel__wall gallery-tunnel__wall--right" data-wall="right"></div>

            <div class="gallery-tunnel__wall gallery-tunnel__wall--bottom" data-wall="bottom"></div>

            <div class="gallery-tunnel__wall gallery-tunnel__wall--left" data-wall="left"></div>

        </div>

        <div class="gallery-tunnel__center-shadow"></div>
    </div>

    <div class="gallery-tunnel__logo" aria-hidden="true">
        <span class="gallery-tunnel__logo-glow"></span>
        <img src="{{ asset('imagenes/logoblanco.png') }}" alt="PRODOVI Logo">
    </div>
</div>

<style>
    html.gallery-is-loading,
    html.gallery-is-loading body {
        overflow: hidden !important;
    }

    /* =========================================================
       PRELOADER
    ========================================================= */

    #gallery-tunnel-preloader {
        --grid-color: rgba(160, 164, 170, .43);

        /*
         * La abertura frontal del túnel.
         * En la referencia es bastante angosta horizontalmente,
         * pero ocupa casi toda la altura.
         */
        --tunnel-width: min(66vw, 980px);
        --tunnel-height: 88vh;

        /*
         * Profundidad total y tamaño de cada fila en profundidad.
         * Cada imagen ocupa solamente UNA celda.
         */
        --tunnel-depth: 2700px;
        --depth-step: 285px;

        position: fixed;
        inset: 0;

        z-index: 999999;

        overflow: hidden;

        background: #000;

        opacity: 1;
        visibility: visible;

        transition:
            opacity .55s ease,
            visibility .55s ease;
    }

    #gallery-tunnel-preloader.is-finished {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }


    /* =========================================================
       ESCENA 3D
    ========================================================= */

    .gallery-tunnel__scene {
        position: absolute;
        inset: 0;

        perspective: 760px;
        perspective-origin: 50% 50%;

        overflow: hidden;

        background: #000;

        transition:
            opacity .35s ease,
            transform .6s cubic-bezier(.22, 1, .36, 1);

        will-change: opacity, transform;
    }

    .gallery-tunnel__world {
        position: absolute;

        left: 50%;
        top: 50%;

        width: 0;
        height: 0;

        transform-style: preserve-3d;

        /*
         * Movimiento hacia la cámara.
         * Es considerablemente más suave que mover cada marco
         * individualmente.
         */
        animation:
            gallery-tunnel-flight
            3.65s
            cubic-bezier(.28, .02, .42, 1)
            forwards;

        will-change: transform;
    }


    /* =========================================================
       PAREDES
    ========================================================= */

    .gallery-tunnel__wall {
        position: absolute;

        left: 0;
        top: 0;

        margin: 0;

        background: #000;

        transform-style: preserve-3d;
        backface-visibility: visible;

        overflow: hidden;
    }


    /*
     * TECHO
     *
     * El eje vertical del elemento pasa a ser profundidad después
     * de rotateX().
     */
    .gallery-tunnel__wall--top {
        width: var(--tunnel-width);
        height: var(--tunnel-depth);

        margin-left: calc(var(--tunnel-width) / -2);
        margin-top: calc(var(--tunnel-depth) / -2);

        transform:
            translate3d(
                0,
                calc(var(--tunnel-height) / -2),
                calc(var(--tunnel-depth) / -2)
            )
            rotateX(-90deg);
    }


    /*
     * PISO
     */
    .gallery-tunnel__wall--bottom {
        width: var(--tunnel-width);
        height: var(--tunnel-depth);

        margin-left: calc(var(--tunnel-width) / -2);
        margin-top: calc(var(--tunnel-depth) / -2);

        transform:
            translate3d(
                0,
                calc(var(--tunnel-height) / 2),
                calc(var(--tunnel-depth) / -2)
            )
            rotateX(-90deg);
    }


    /*
     * PARED IZQUIERDA
     *
     * El eje horizontal pasa a ser la profundidad.
     */
    .gallery-tunnel__wall--left {
        width: var(--tunnel-depth);
        height: var(--tunnel-height);

        margin-left: calc(var(--tunnel-depth) / -2);
        margin-top: calc(var(--tunnel-height) / -2);

        transform:
            translate3d(
                calc(var(--tunnel-width) / -2),
                0,
                calc(var(--tunnel-depth) / -2)
            )
            rotateY(90deg);
    }


    /*
     * PARED DERECHA
     */
    .gallery-tunnel__wall--right {
        width: var(--tunnel-depth);
        height: var(--tunnel-height);

        margin-left: calc(var(--tunnel-depth) / -2);
        margin-top: calc(var(--tunnel-height) / -2);

        transform:
            translate3d(
                calc(var(--tunnel-width) / 2),
                0,
                calc(var(--tunnel-depth) / -2)
            )
            rotateY(90deg);
    }


    /* =========================================================
       GRILLA
    ========================================================= */

    /*
     * La grilla va ENCIMA de imágenes y colores.
     * Esto es importante para obtener el aspecto de la referencia.
     */
    .gallery-tunnel__wall::after {
        content: "";

        position: absolute;
        inset: 0;

        z-index: 10;

        pointer-events: none;
    }


    /*
     * Techo y piso:
     * 4 columnas horizontales + divisiones en profundidad.
     */
    .gallery-tunnel__wall--top::after,
    .gallery-tunnel__wall--bottom::after {
        background-image:

            linear-gradient(
                to right,
                transparent calc(100% - 1px),
                var(--grid-color) calc(100% - 1px),
                var(--grid-color) 100%
            ),

            linear-gradient(
                to bottom,
                transparent calc(100% - 1px),
                var(--grid-color) calc(100% - 1px),
                var(--grid-color) 100%
            );

        background-size:
            25% 100%,
            100% var(--depth-step);

        background-repeat: repeat;
    }


    /*
     * Laterales:
     * divisiones en profundidad + 4 divisiones verticales.
     */
    .gallery-tunnel__wall--left::after,
    .gallery-tunnel__wall--right::after {
        background-image:

            linear-gradient(
                to right,
                transparent calc(100% - 1px),
                var(--grid-color) calc(100% - 1px),
                var(--grid-color) 100%
            ),

            linear-gradient(
                to bottom,
                transparent calc(100% - 1px),
                var(--grid-color) calc(100% - 1px),
                var(--grid-color) 100%
            );

        background-size:
            var(--depth-step) 100%,
            100% 25%;

        background-repeat: repeat;
    }


    /* =========================================================
       CELDAS
    ========================================================= */

    .gallery-tunnel__tile {
        position: absolute;

        z-index: 2;

        overflow: hidden;

        background-color: #000;
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;

        /*
         * El pequeño inset evita que una imagen tape completamente
         * las líneas de la grilla.
         */
        box-shadow: inset 0 0 0 .5px rgba(0, 0, 0, .16);
    }


    /*
     * Las del techo/piso tienen 1/4 del ancho.
     */
    .gallery-tunnel__wall--top .gallery-tunnel__tile,
    .gallery-tunnel__wall--bottom .gallery-tunnel__tile {
        left: calc(var(--column) * 25%);
        top: calc(var(--depth-index) * var(--depth-step));

        width: 25%;
        height: var(--depth-step);
    }


    /*
     * Las laterales tienen una fila de 1/4 de altura.
     */
    .gallery-tunnel__wall--left .gallery-tunnel__tile,
    .gallery-tunnel__wall--right .gallery-tunnel__tile {
        left: calc(var(--depth-index) * var(--depth-step));
        top: calc(var(--row) * 25%);

        width: var(--depth-step);
        height: 25%;
    }


    .gallery-tunnel__tile--image {
        filter:
            saturate(1.08)
            contrast(1.05)
            brightness(.94);
    }


    /* =========================================================
       SOMBRA DEL CENTRO
    ========================================================= */

    /*
     * Ayuda a que el final del túnel quede negro, como la captura,
     * en lugar de mostrar demasiadas imágenes en el fondo.
     */
    .gallery-tunnel__center-shadow {
        position: absolute;

        left: 50%;
        top: 50%;

        width: min(35vw, 480px);
        height: min(43vh, 390px);

        transform: translate(-50%, -50%);

        pointer-events: none;

        background: radial-gradient(
            ellipse at center,
            rgba(0, 0, 0, .98) 0%,
            rgba(0, 0, 0, .92) 35%,
            rgba(0, 0, 0, .42) 65%,
            transparent 100%
        );

        z-index: 3;
    }


    /* =========================================================
       ANIMACIÓN
    ========================================================= */

    @keyframes gallery-tunnel-flight {
        0% {
            transform: translateZ(-90px);
        }

        100% {
            transform: translateZ(230px);
        }
    }


    /* =========================================================
       TRANSICIÓN HACIA LOGO
    ========================================================= */

    #gallery-tunnel-preloader.show-logo .gallery-tunnel__scene {
        opacity: 0;
        transform: scale(1.08);
    }


    /* =========================================================
       LOGO
    ========================================================= */

    .gallery-tunnel__logo {
        position: absolute;
        inset: 0;

        z-index: 20;

        display: grid;
        place-items: center;

        opacity: 0;

        transform: scale(.72);

        pointer-events: none;

        transition:
            opacity .4s ease,
            transform .7s cubic-bezier(.18, .89, .32, 1.28);
    }


    .gallery-tunnel__logo img {
        position: relative;

        z-index: 2;

        display: block;

        width: clamp(170px, 26vw, 390px);

        max-height: 38vh;

        object-fit: contain;

        filter:
            drop-shadow(
                0 12px 38px
                rgba(255, 255, 255, .20)
            );
    }


    .gallery-tunnel__logo-glow {
        position: absolute;

        width: min(48vw, 560px);
        aspect-ratio: 1;

        border-radius: 50%;

        background: radial-gradient(
            circle,
            rgba(137, 105, 255, .24),
            rgba(0, 183, 255, .08) 42%,
            transparent 70%
        );

        transform: scale(.55);

        transition: transform 1s ease;
    }


    #gallery-tunnel-preloader.show-logo .gallery-tunnel__logo {
        opacity: 1;
        transform: scale(1);
    }


    #gallery-tunnel-preloader.show-logo .gallery-tunnel__logo-glow {
        transform: scale(1);
    }


    /* =========================================================
       RESPONSIVE
       IMPORTANTE:
       - Escritorio (> 1100px) conserva EXACTAMENTE los valores
         y comportamiento originales definidos arriba.
       - Aquí solo se recalibra la geometría para tablet y móvil.
    ========================================================= */


    /* =========================================================
       TABLET
       701px - 1100px
    ========================================================= */

    @media (min-width: 701px) and (max-width: 1100px) {

        #gallery-tunnel-preloader {
            /*
             * Abrimos un poco más el túnel que en desktop para
             * aprovechar mejor el ancho disponible de la tablet.
             */
            --tunnel-width: min(76vw, 760px);
            --tunnel-height: 84dvh;

            /*
             * Reducimos profundidad y tamaño de celda para evitar
             * que los primeros cuadros se vean excesivamente grandes.
             */
            --depth-step: 250px;
            --tunnel-depth: 2450px;
        }

        .gallery-tunnel__scene {
            perspective: 660px;
        }

        .gallery-tunnel__center-shadow {
            width: min(42vw, 390px);
            height: min(40dvh, 350px);
        }

        .gallery-tunnel__logo img {
            width: clamp(180px, 31vw, 320px);
            max-height: 34dvh;
        }

        .gallery-tunnel__logo-glow {
            width: min(58vw, 500px);
        }

        @keyframes gallery-tunnel-flight {
            from {
                transform: translateZ(-80px);
            }

            to {
                transform: translateZ(195px);
            }
        }
    }


    /* =========================================================
       TABLET HORIZONTAL
    ========================================================= */

    @media (min-width: 701px) and (max-width: 1100px) and (orientation: landscape) {

        #gallery-tunnel-preloader {
            --tunnel-width: min(72vw, 820px);
            --tunnel-height: 78dvh;
            --depth-step: 245px;
            --tunnel-depth: 2400px;
        }

        .gallery-tunnel__scene {
            perspective: 690px;
        }

        .gallery-tunnel__center-shadow {
            width: min(36vw, 380px);
            height: min(42dvh, 300px);
        }

        .gallery-tunnel__logo img {
            width: clamp(175px, 27vw, 300px);
            max-height: 38dvh;
        }
    }


    /* =========================================================
       MOBILE
       Hasta 700px
    ========================================================= */

    @media (max-width: 700px) {

        #gallery-tunnel-preloader {
            /*
             * En móvil el túnel mantiene bastante altura para que
             * siga sintiéndose inmersivo, pero gana ancho visual.
             */
            --tunnel-width: 78vw;
            --tunnel-height: 82dvh;

            /*
             * Celdas y profundidad más cortas:
             * mejora la escala en pantallas angostas.
             */
            --depth-step: 205px;
            --tunnel-depth: 2050px;
        }

        .gallery-tunnel__scene {
            perspective: 540px;
            perspective-origin: 50% 50%;
        }

        .gallery-tunnel__center-shadow {
            width: 46vw;
            height: 33dvh;
        }

        .gallery-tunnel__logo img {
            width: clamp(155px, 48vw, 245px);
            max-height: 30dvh;
        }

        .gallery-tunnel__logo-glow {
            width: min(78vw, 370px);
        }

        @keyframes gallery-tunnel-flight {
            from {
                transform: translateZ(-65px);
            }

            to {
                transform: translateZ(145px);
            }
        }
    }


    /* =========================================================
       MOBILE PEQUEÑO
       Hasta 430px
    ========================================================= */

    @media (max-width: 430px) {

        #gallery-tunnel-preloader {
            --tunnel-width: 80vw;
            --tunnel-height: 80dvh;
            --depth-step: 185px;
            --tunnel-depth: 1850px;
        }

        .gallery-tunnel__scene {
            perspective: 500px;
        }

        .gallery-tunnel__center-shadow {
            width: 48vw;
            height: 31dvh;
        }

        .gallery-tunnel__logo img {
            width: clamp(145px, 52vw, 220px);
            max-height: 28dvh;
        }

        .gallery-tunnel__logo-glow {
            width: min(84vw, 330px);
        }

        @keyframes gallery-tunnel-flight {
            from {
                transform: translateZ(-55px);
            }

            to {
                transform: translateZ(125px);
            }
        }
    }


    /* =========================================================
       MOBILE HORIZONTAL
       Ej.: 812x375, 844x390, 932x430
    ========================================================= */

    @media (max-width: 950px) and (max-height: 500px) and (orientation: landscape) {

        #gallery-tunnel-preloader {
            --tunnel-width: 72vw;
            --tunnel-height: 72dvh;
            --depth-step: 185px;
            --tunnel-depth: 1850px;
        }

        .gallery-tunnel__scene {
            perspective: 520px;
        }

        .gallery-tunnel__center-shadow {
            width: 37vw;
            height: 42dvh;
        }

        .gallery-tunnel__logo img {
            width: clamp(145px, 25vw, 220px);
            max-height: 42dvh;
        }

        .gallery-tunnel__logo-glow {
            width: min(48vw, 320px);
        }

        @keyframes gallery-tunnel-flight {
            from {
                transform: translateZ(-55px);
            }

            to {
                transform: translateZ(135px);
            }
        }
    }


    /* Respeta usuarios que desactivan animaciones */
    @media (prefers-reduced-motion: reduce) {

        .gallery-tunnel__world {
            animation-duration: .01ms;
        }

        #gallery-tunnel-preloader,
        .gallery-tunnel__scene,
        .gallery-tunnel__logo,
        .gallery-tunnel__logo-glow {
            transition-duration: .01ms;
        }
    }

</style>

<script>
(() => {

    const preloader = document.getElementById('gallery-tunnel-preloader');

    if (!preloader) {
        return;
    }

    document.documentElement.classList.add('gallery-is-loading');


    /* =========================================================
       IMÁGENES
    ========================================================= */

        const images = [
            '{{ asset('imagenes/Imagenes_loader/post1.webp') }}',
            '{{ asset('imagenes/Imagenes_loader/post2.webp') }}',
            '{{ asset('imagenes/Imagenes_loader/post3.webp') }}',
            '{{ asset('imagenes/Imagenes_loader/post4.webp') }}',
            '{{ asset('imagenes/Imagenes_loader/post5.webp') }}'
        ];


    const colors = {
        orange: '#f36b00',
        purple: '#9848e5',
        red: '#df5c30',
        blue: '#027a7f',
        green: '#9fb128',
        yellow: '#f6ae00'
    };


    /* =========================================================
       DISTRIBUCIÓN
    ========================================================= */

    /*
     * IMPORTANTE:
     *
     * d = profundidad
     *
     * Techo/piso:
     * c = columna (0,1,2,3)
     *
     * Laterales:
     * r = fila (0,1,2,3)
     *
     * Esta distribución está hecha manualmente para acercarse
     * mucho más a la captura de referencia.
     *
     * No llenamos todas las celdas.
     */


    const layout = {

        /* =========================
           TECHO
        ========================= */

        top: [

            // Primera fila: muy parecida a la captura
            { d: 0, c: 0, image: 0 },
            { d: 0, c: 1, color: colors.blue },
            { d: 0, c: 2, color: colors.red },
            { d: 0, c: 3, image: 1 },

            // Segundo grupo
            { d: 2, c: 0, color: colors.green },
            { d: 2, c: 1, image: 2 },
            { d: 2, c: 3, color: colors.red },

            // Más hacia el fondo
            { d: 4, c: 2, color: '#b37c00' },
            { d: 5, c: 3, image: 0 },

            { d: 7, c: 0, color: '#643300' },
            { d: 8, c: 2, image: 3 }
        ],


        /* =========================
           PISO
        ========================= */

        bottom: [

            /*
             * En la referencia el frente inferior tiene varias
             * imágenes consecutivas.
             */
            { d: 0, c: 1, image: 4 },
            { d: 0, c: 2, image: 2 },
            { d: 0, c: 3, image: 3 },

            { d: 1, c: 1, color: colors.blue },
            { d: 1, c: 2, image: 3 },
            { d: 1, c: 3, image: 1 },

            { d: 3, c: 1, image: 0 },
            { d: 3, c: 2, color: colors.yellow },
            { d: 3, c: 3, color: colors.red },

            { d: 5, c: 0, image: 4 },
            { d: 6, c: 3, image: 0 },

            { d: 8, c: 1, color: '#174e69' }
        ],


        /* =========================
           IZQUIERDA
        ========================= */

        left: [

            // Zona frontal
            { d: 0, r: 0, image: 4 },
            { d: 0, r: 1, color: colors.orange },
            { d: 0, r: 3, image: 0 },

            // Zona media
            { d: 2, r: 0, color: '#d55b00' },
            { d: 2, r: 1, image: 2 },
            { d: 2, r: 2, color: colors.green },

            { d: 4, r: 1, color: '#713600' },
            { d: 4, r: 3, image: 4 },

            { d: 6, r: 2, color: '#065c30' },

            { d: 8, r: 0, image: 1 }
        ],


        /* =========================
           DERECHA
        ========================= */

        right: [

            // Frente similar a captura: amarillo + violeta
            { d: 0, r: 1, color: colors.yellow },
            { d: 0, r: 2, color: colors.purple },

            { d: 2, r: 0, image: 0 },
            { d: 2, r: 2, color: colors.blue },

            { d: 3, r: 1, image: 1 },

            { d: 4, r: 2, color: '#552f84' },

            { d: 5, r: 0, image: 0 },

            { d: 7, r: 3, color: '#004e70' },

            { d: 8, r: 1, image: 3 }
        ]
    };


    /* =========================================================
       CREAR CELDA
    ========================================================= */

    function createTile(wallName, config) {

        const wall = preloader.querySelector(
            `[data-wall="${wallName}"]`
        );

        if (!wall) {
            return;
        }

        const tile = document.createElement('span');

        tile.className = 'gallery-tunnel__tile';


        /*
         * Profundidad
         */
        tile.style.setProperty(
            '--depth-index',
            config.d
        );


        /*
         * Coordenada dependiendo de la pared.
         */
        if (
            wallName === 'top' ||
            wallName === 'bottom'
        ) {

            tile.style.setProperty(
                '--column',
                config.c
            );

        } else {

            tile.style.setProperty(
                '--row',
                config.r
            );
        }


        /*
         * Imagen
         */
        if (
            typeof config.image === 'number'
        ) {

            tile.classList.add(
                'gallery-tunnel__tile--image'
            );

            tile.style.backgroundImage =
                `url("${images[config.image % images.length]}")`;
        }


        /*
         * Color
         */
        if (config.color) {

            tile.style.backgroundColor =
                config.color;
        }


        wall.appendChild(tile);
    }


    /* =========================================================
       GENERAR DISTRIBUCIÓN
    ========================================================= */

    Object.entries(layout).forEach(
        ([wallName, tiles]) => {

            tiles.forEach(tile => {
                createTile(wallName, tile);
            });

        }
    );


    /* =========================================================
       PRECARGAR IMÁGENES
    ========================================================= */

    images.forEach(src => {

        const img = new Image();

        img.decoding = 'async';

        img.src = src;
    });


    /* =========================================================
       FINAL
    ========================================================= */

    window.setTimeout(() => {

        preloader.classList.add('show-logo');

    }, 3650);


    window.setTimeout(() => {

        preloader.classList.add('is-finished');

        document.documentElement.classList.remove(
            'gallery-is-loading'
        );


        window.setTimeout(() => {

            preloader.remove();

        }, 600);

    }, 5000);

})();
</script>