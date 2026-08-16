<style>

/* =========================================================
   RESET
========================================================= */

*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


:root {

    color-scheme: dark only;

    /* PRODOVI */

    --purple: #5B2B76;

    --orange: #EF6C22;

    --gold: #F5A900;

    --green: #7DA533;

    --turquoise: #117E8C;

    --gray: #607078;


    /* UI */

    --page-bg: #363737;

    --card-bg: #242426;

    --card-soft: #2b2b2e;

    --white: #ffffff;

    --text: #f4f4f5;

    --muted: #96969c;

    --border: #5d5d62;

    --border-soft: #48484d;
}



/* =========================================================
   BASE
========================================================= */

html {

    min-height: 100%;

    background: var(--page-bg);
}


body {

    width: 100%;

    min-height: 100vh;

    overflow-x: hidden;

    font-family:
        'Inter',
        sans-serif;

    color: var(--text);

    background: var(--page-bg);
}



/* =========================================================
   PAGE
========================================================= */

.auth-page {

    width: 100%;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 28px;
}



/* =========================================================
   CARD
========================================================= */

.auth-card {

    width: min(
        1180px,
        100%
    );

    min-height: 720px;

    display: grid;

    grid-template-columns:
        minmax(0, 55%)
        minmax(400px, 45%);

    overflow: hidden;

    border-radius: 28px;

    background: var(--card-bg);

    box-shadow:
        0
        24px
        60px
        rgba(0, 0, 0, .22);
}



/* =========================================================
   ART
========================================================= */

.auth-art {

    position: relative;

    min-width: 0;

    min-height: 720px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 70px;

    overflow: hidden;

    background: var(--card-bg);
}



/* =========================================================
   MOSAICO
========================================================= */

.prodovi-mosaic {

    position: relative;

    width: min(
        500px,
        100%
    );

    aspect-ratio: 1 / 1.22;

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    grid-template-rows:
        repeat(5, 1fr);

    gap: 0;
}


.mosaic-cell {

    position: relative;

    display: block;

    overflow: hidden;

    background: transparent;

    isolation: isolate;
}



/* =========================================================
   FORMAS
========================================================= */

/*
 * Las piezas están hechas completamente con CSS.
 * No utilizan imágenes.
 */


/* 01 */

.cell-01 {

    background:
        var(--orange);

    border-radius:
        100% 0 0 0;
}



/* 02 */

.cell-02 {

    background:
        var(--gold);

    border-radius:
        0 0 0 100%;
}


.cell-02::after {

    content: '';

    position: absolute;

    width: 72%;

    height: 72%;

    right: 0;

    top: 0;

    border:

        1px

        solid

        rgba(
            255,
            255,
            255,
            .45
        );

    border-radius:
        0 0 0 100%;

    background:
        var(--card-bg);
}



/* 03 */

.cell-03 {

    background:
        var(--purple);

    border-radius:
        100% 0 100% 0;
}



/* 04 */

.cell-04 {

    background:
        var(--turquoise);

    border-radius:
        0 100% 0 100%;
}



/* 05 */

.cell-05 {

    background:
        var(--green);

    border-radius:
        0 100% 0 0;
}



/* 06 */

.cell-06 {

    background:
        var(--gold);
}


.cell-06::after {

    content: '';

    position: absolute;

    inset: 50% 0 0 50%;

    background:
        var(--card-bg);

    border-radius:
        100% 0 0 0;
}



/* 07 */

.cell-07 {

    background:
        var(--gray);

    border-radius:
        0 0 100% 0;
}



/* 08 */

.cell-08 {

    background:
        var(--orange);

    border-radius:
        50%;
}


.cell-08::before {

    content: '';

    position: absolute;

    inset: 22%;

    border-radius: 50%;

    background:
        #f4d5b4;
}



/* 09 */

.cell-09 {

    background:
        var(--purple);
}


.cell-09::before {

    content: '';

    position: absolute;

    left: 0;

    bottom: 0;

    width: 100%;

    height: 52%;

    border-radius:
        100% 100% 0 0;

    background:
        var(--gray);
}



/* 10 */

.cell-10 {

    background:
        var(--turquoise);

    border-radius:
        100% 0 0 0;
}



/* 11 */

.cell-11 {

    background:
        var(--orange);

    border-radius:
        100% 0 100% 0;
}



/* 12 */

.cell-12 {

    background:
        var(--turquoise);

    border-radius:
        100% 0 100% 0;
}



/* 13 */

.cell-13 {

    background:
        var(--purple);

    border-radius:
        0 0 100% 0;
}



/* 14 */

.cell-14 {

    background:
        var(--gold);

    border-radius:
        50%;
}



/* 15 */

.cell-15 {

    border:

        1px

        solid

        rgba(
            255,
            255,
            255,
            .42
        );

    border-radius:
        0 100% 0 0;
}



/* 16 */

.cell-16 {

    border:

        1px

        solid

        rgba(
            255,
            255,
            255,
            .42
        );

    border-radius:
        100% 0 0 0;
}



/* 17 */

.cell-17 {

    background:
        var(--green);

    border-radius:
        0 0 0 100%;
}



/* 18 */

.cell-18 {

    background:
        var(--gray);

    border-radius:
        100% 0 0 100%;
}



/* 19 */

.cell-19 {

    background:
        var(--turquoise);

    border-radius:
        100% 100% 0 0;
}



/* 20 */

.cell-20 {

    background:
        var(--orange);

    border-radius:
        0 0 100% 0;
}



/* 21 */

.cell-21 {

    background:
        var(--green);

    border-radius:
        100% 0 0 0;
}



/* 22 */

.cell-22 {

    background:
        var(--purple);

    border-radius:
        100% 0 0 100%;
}



/* 23 */

.cell-23 {

    background:
        var(--gray);

    border-radius:
        0 100% 0 100%;
}



/* 24 */

.cell-24 {

    background:
        var(--gold);

    border-radius:
        0 0 100% 0;
}



/* 25 */

.cell-25 {

    border:

        1px

        solid

        rgba(
            255,
            255,
            255,
            .42
        );

    border-radius:
        100% 100% 0 0;
}



/* =========================================================
   PEQUEÑOS DETALLES DEL MOSAICO
========================================================= */

.cell-01::after {

    content: '';

    position: absolute;

    width: 62%;

    height: 3px;

    right: -8%;

    top: 32%;

    background:
        rgba(
            255,
            255,
            255,
            .72
        );

    transform:
        rotate(-16deg);
}


.cell-03::after {

    content: '';

    position: absolute;

    width: 130%;

    height: 1px;

    left: -16%;

    top: 50%;

    background:
        rgba(
            255,
            255,
            255,
            .45
        );

    transform:
        rotate(48deg);
}


.cell-12::after {

    content: '';

    position: absolute;

    width: 150%;

    height: 2px;

    left: -20%;

    top: 48%;

    transform:
        rotate(-25deg);

    background:
        rgba(
            255,
            255,
            255,
            .60
        );
}



/* =========================================================
   CONTENT
========================================================= */

.auth-content {

    position: relative;

    min-width: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    padding:
        62px
        68px;

    background:
        var(--card-bg);
}


.auth-content-inner {

    width: 100%;

    max-width: 430px;
}



/* =========================================================
   BACK
========================================================= */

.back-button {

    width: fit-content;

    display: inline-flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 52px;

    color: #dddddf;

    text-decoration: none;

    font-size: .74rem;

    font-weight: 500;

    transition:
        color .2s ease,
        transform .2s ease;
}


.back-button i {

    font-size: .65rem;
}


.back-button:hover {

    color: var(--gold);

    transform:
        translateX(-3px);
}



/* =========================================================
   LOGO
========================================================= */

.auth-logo {

    display: inline-block;

    margin-bottom: 26px;
}


.auth-logo img {

    display: block;

    width: 125px;

    height: auto;

    object-fit: contain;
}



/* =========================================================
   HEADER
========================================================= */

.auth-header {

    margin-bottom: 38px;
}


.auth-title {

    margin-bottom: 14px;

    color:
        var(--white);

    font-family:
        'Poppins',
        sans-serif;

    font-size:
        clamp(
            1.85rem,
            3vw,
            2.35rem
        );

    font-weight: 600;

    line-height: 1.1;

    letter-spacing: -.035em;
}


.auth-subtitle {

    color:
        var(--muted);

    font-size: .76rem;

    line-height: 1.7;
}


.inline-register-link {

    color:
        var(--white);

    font-weight: 600;

    text-decoration:
        underline;

    text-underline-offset:
        3px;

    transition:
        color .2s ease;
}


.inline-register-link:hover {

    color:
        var(--gold);
}



/* =========================================================
   FORMS
========================================================= */

.form-container {

    position: relative;

    width: 100%;

    overflow: hidden;
}


.form {

    width: 100%;

    transition:
        opacity .25s ease,
        transform .25s ease;
}


.form.hidden {

    position: absolute;

    left: 0;

    top: 0;

    width: 100%;

    opacity: 0;

    pointer-events: none;

    transform:
        translateX(30px);
}


.form.slide-left {

    opacity: 0;

    pointer-events: none;

    transform:
        translateX(-30px);
}



/* =========================================================
   INPUT GROUP
========================================================= */

.input-group {

    width: 100%;

    margin-bottom: 22px;
}


.input-label {

    display: block;

    margin-bottom: 9px;

    color:
        #d2d2d5;

    font-size:
        .67rem;

    font-weight:
        500;
}



/* =========================================================
   INPUTS
========================================================= */

.input-wrapper,
.password-input-wrapper {

    position: relative;

    width: 100%;
}


.input-field {

    width: 100%;

    height: 49px;

    padding:
        0
        17px;

    border:

        1px

        solid

        #68686d;

    border-radius:
        999px;

    outline: none;

    background:
        transparent;

    color:
        #ffffff;

    font-family:
        'Inter',
        sans-serif;

    font-size:
        .8rem;

    font-weight:
        400;

    caret-color:
        var(--gold);

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}


.password-input-wrapper
.input-field {

    padding-right:
        48px;
}


.input-field::placeholder {

    color:
        #77777d;
}


.input-field:hover {

    border-color:
        #828287;
}


.input-field:focus {

    border-color:
        var(--gold);

    background:
        rgba(
            255,
            255,
            255,
            .018
        );

    box-shadow:
        0
        0
        0
        3px
        rgba(
            245,
            169,
            0,
            .08
        );
}



/* =========================================================
   PASSWORD EYE
========================================================= */

.toggle-password {

    position: absolute;

    top: 50%;

    right: 17px;

    transform:
        translateY(-50%);

    color:
        #bfc0c3;

    font-size:
        .75rem;

    cursor: pointer;

    transition:
        color .2s ease;
}


.toggle-password:hover {

    color:
        var(--gold);
}



/* =========================================================
   FORGOT
========================================================= */

.forgot-row {

    display: flex;

    justify-content: flex-end;

    margin:
        -9px
        0
        24px;
}


.forgot-password {

    color:
        #efeff0;

    font-size:
        .68rem;

    text-decoration:
        underline;

    text-underline-offset:
        3px;

    transition:
        color .2s ease;
}


.forgot-password:hover {

    color:
        var(--gold);
}



/* =========================================================
   BUTTON
========================================================= */

.btn {

    width: 100%;

    min-height: 49px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 9px;

    padding:
        12px
        20px;

    border-radius:
        999px;

    font-family:
        'Inter',
        sans-serif;

    font-size:
        .78rem;

    font-weight:
        700;

    line-height:
        1;

    text-decoration:
        none;

    cursor:
        pointer;

    transition:
        transform .2s ease,
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}



/* =========================================================
   PRIMARY
========================================================= */

.btn-primary {

    margin-top:
        2px;

    border:
        1px
        solid
        #ffffff;

    background:
        #ffffff;

    color:
        #242426;
}


.btn-primary:hover {

    border-color:
        var(--gold);

    background:
        var(--gold);

    color:
        #242426;

    transform:
        translateY(-1px);

    box-shadow:
        0
        8px
        18px
        rgba(
            245,
            169,
            0,
            .14
        );
}



/* =========================================================
   GOOGLE
========================================================= */

.btn-google {

    margin-top:
        25px;

    border:

        1px

        solid

        #535358;

    background:
        transparent;

    color:
        #f1f1f2;
}


.btn-google:hover {

    border-color:
        var(--turquoise);

    background:
        rgba(
            17,
            126,
            140,
            .08
        );

    transform:
        translateY(-1px);
}



/* =========================================================
   GOOGLE LOGO
========================================================= */

.google-mark {

    width: 20px;

    height: 20px;

    display: grid;

    place-items: center;

    border-radius:
        50%;

    color:
        #ffffff;

    font-family:
        Arial,
        sans-serif;

    font-size:
        .67rem;

    font-weight:
        700;

    background:

        conic-gradient(

            from -45deg,

            #4285f4
            0 25%,

            #34a853
            0 50%,

            #fbbc05
            0 75%,

            #ea4335
            0

        );
}



/* =========================================================
   SWITCH
========================================================= */

.form-switch {

    margin-top:
        28px;

    text-align:
        center;

    color:
        var(--muted);

    font-size:
        .71rem;

    line-height:
        1.6;
}


.switch-link {

    margin-left:
        5px;

    color:
        #ffffff;

    font-weight:
        600;

    text-decoration:
        underline;

    text-underline-offset:
        3px;

    transition:
        color .2s ease;
}


.switch-link:hover {

    color:
        var(--gold);
}



/* =========================================================
   ERRORS
========================================================= */

.error-text,
.invalid-feedback {

    display:
        block;

    margin-top:
        7px;

    padding-left:
        8px;

    color:
        #ff8b73;

    font-size:
        .67rem;

    line-height:
        1.4;
}


.input-error {

    border-color:
        var(--orange)
        !important;
}



/* =========================================================
   LOADING
========================================================= */

.btn-loading {

    opacity:
        .65;

    pointer-events:
        none;
}



/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1020px) {

    .auth-page {

        padding:
            22px;
    }


    .auth-card {

        grid-template-columns:
            48%
            52%;

        min-height:
            680px;

        border-radius:
            24px;
    }


    .auth-art {

        min-height:
            680px;

        padding:
            45px;
    }


    .auth-content {

        padding:
            55px
            46px;
    }


    .prodovi-mosaic {

        width:
            min(
                390px,
                100%
            );
    }


    .back-button {

        margin-bottom:
            42px;
    }

}



/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 760px) {

    html,
    body {

        background:
            var(--page-bg);
    }


    .auth-page {

        align-items:
            flex-start;

        padding:
            22px
            16px;
    }


    .auth-card {

        width:
            min(
                430px,
                100%
            );

        min-height:
            auto;

        display:
            flex;

        flex-direction:
            column;

        border-radius:
            24px;

        overflow:
            hidden;
    }


    /* =============================================
       MOSAICO ARRIBA
    ============================================== */

    .auth-art {

        min-height:
            0;

        height:
            164px;

        flex:
            0
            0
            164px;

        display:
            block;

        padding:
            0;

        overflow:
            hidden;
    }


    .prodovi-mosaic {

        position:
            absolute;

        top:
            0;

        left:
            0;

        width:
            100%;

        height:
            164px;

        aspect-ratio:
            auto;

        display:
            grid;

        grid-template-columns:
            repeat(
                5,
                1fr
            );

        grid-template-rows:
            repeat(
                2,
                82px
            );

        overflow:
            hidden;
    }


    /*
     * En móvil mostramos solo las
     * primeras diez piezas.
     */

    .mosaic-cell:nth-child(n+11) {

        display:
            none;
    }



    /* =============================================
       CONTENT
    ============================================== */

    .auth-content {

        min-height:
            600px;

        display:
            block;

        padding:
            30px
            27px
            40px;
    }


    .auth-content-inner {

        max-width:
            none;
    }



    /* =============================================
       BACK
    ============================================== */

    .back-button {

        margin-bottom:
            39px;

        font-size:
            .7rem;
    }



    /* =============================================
       LOGO
    ============================================== */

    .auth-logo {

        margin-bottom:
            22px;
    }


    .auth-logo img {

        width:
            110px;
    }



    /* =============================================
       HEADER
    ============================================== */

    .auth-header {

        margin-bottom:
            35px;
    }


    .auth-title {

        font-size:
            1.75rem;
    }


    .auth-subtitle {

        max-width:
            330px;

        font-size:
            .72rem;
    }



    /* =============================================
       INPUTS
    ============================================== */

    .input-group {

        margin-bottom:
            20px;
    }


    .input-field {

        height:
            49px;
    }



    /* =============================================
       BUTTONS
    ============================================== */

    .btn {

        min-height:
            49px;
    }


    .btn-google {

        margin-top:
            24px;
    }

}



/* =========================================================
   MOBILE PEQUEÑO
========================================================= */

@media (max-width: 400px) {

    .auth-page {

        padding:
            14px;
    }


    .auth-card {

        border-radius:
            21px;
    }


    .auth-art {

        height:
            145px;

        flex-basis:
            145px;
    }


    .prodovi-mosaic {

        height:
            145px;

        grid-template-rows:
            repeat(
                2,
                72.5px
            );
    }


    .auth-content {

        padding:
            27px
            20px
            35px;
    }


    .back-button {

        margin-bottom:
            34px;
    }


    .auth-title {

        font-size:
            1.62rem;
    }

}



/* =========================================================
   ALTURA PEQUEÑA DESKTOP
========================================================= */

@media (
    min-width: 761px
) and (
    max-height: 780px
) {

    .auth-page {

        align-items:
            flex-start;

        padding-top:
            25px;

        padding-bottom:
            25px;
    }


    .auth-card {

        min-height:
            660px;
    }


    .auth-art {

        min-height:
            660px;
    }


    .auth-content {

        padding-top:
            45px;

        padding-bottom:
            45px;
    }


    .back-button {

        margin-bottom:
            35px;
    }


    .auth-logo {

        margin-bottom:
            17px;
    }


    .auth-header {

        margin-bottom:
            28px;
    }


    .input-group {

        margin-bottom:
            17px;
    }

}



/* =========================================================
   REDUCED MOTION
========================================================= */

@media (
    prefers-reduced-motion:
    reduce
) {

    .form,
    .btn,
    .back-button,
    .input-field {

        transition:
            none;
    }

}

/* La tarjeta es ahora toda la página; no queda un fondo exterior visible. */
html,
body {
    background: var(--card-bg) !important;
}

.auth-page {
    min-height: 100vh !important;
    padding: 0 !important;
    align-items: stretch !important;
}

.auth-card {
    width: 100% !important;
    max-width: none !important;
    min-height: 100vh !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

@media (min-width: 761px) {
    .auth-art,
    .auth-content {
        min-height: 100vh !important;
    }
}

/* Mosaico geométrico: piezas más cuadradas y modulares. */
.prodovi-mosaic {
    gap: 7px;
}

.mosaic-cell {
    border-radius: 7px !important;
}

.mosaic-cell::before,
.mosaic-cell::after {
    content: none !important;
}

.cell-01 {
    background: var(--orange);
    clip-path: polygon(0 0, 100% 0, 100% 68%, 68% 68%, 68% 100%, 0 100%);
}

.cell-02 {
    background: var(--gold);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 24% 100%, 24% 76%, 0 76%);
}

.cell-03 {
    background: var(--purple);
    clip-path: polygon(18% 0, 100% 0, 100% 82%, 82% 100%, 0 100%, 0 18%);
}

.cell-04 {
    background: var(--turquoise);
    clip-path: polygon(0 0, 74% 0, 74% 24%, 100% 24%, 100% 100%, 0 100%);
}

.cell-05 {
    background: var(--green);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 34% 100%, 34% 66%, 0 66%);
}

.cell-06 {
    background: linear-gradient(90deg, var(--gold) 0 64%, transparent 64%);
    border: 1px solid rgba(245, 169, 0, .5);
}

.cell-07 {
    background: var(--gray);
    clip-path: polygon(0 0, 100% 0, 100% 72%, 72% 72%, 72% 100%, 0 100%);
}

.cell-08 {
    background: var(--orange);
    border: 12px solid #f4d5b4;
    border-radius: 10px !important;
}

.cell-09 {
    background: linear-gradient(to bottom, var(--purple) 0 58%, var(--gray) 58% 100%);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 16% 100%, 16% 84%, 0 84%);
}

.cell-10 {
    background: var(--turquoise);
    clip-path: polygon(0 0, 100% 0, 100% 32%, 62% 32%, 62% 100%, 0 100%);
}

.cell-11 {
    background: var(--orange);
    clip-path: polygon(0 0, 82% 0, 100% 18%, 100% 100%, 18% 100%, 0 82%);
}

.cell-12 {
    background: var(--turquoise);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 70% 100%, 70% 70%, 0 70%);
}

.cell-13 {
    background: var(--purple);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 34%, 32% 34%, 32% 0);
}

.cell-14 {
    background: var(--gold);
    border: 10px solid var(--card-bg);
    outline: 1px solid rgba(245, 169, 0, .65);
    border-radius: 9px !important;
}

.cell-15,
.cell-16,
.cell-25 {
    background: transparent;
    border: 2px solid rgba(255, 255, 255, .42);
    border-radius: 8px !important;
}

.cell-15 { clip-path: polygon(0 0, 100% 0, 100% 100%, 28% 100%, 28% 72%, 0 72%); }
.cell-16 { clip-path: polygon(0 0, 72% 0, 72% 28%, 100% 28%, 100% 100%, 0 100%); }

.cell-17 {
    background: var(--green);
    clip-path: polygon(0 0, 100% 0, 100% 78%, 78% 100%, 0 100%);
}

.cell-18 {
    background: var(--gray);
    clip-path: polygon(20% 0, 100% 0, 100% 100%, 20% 100%, 20% 78%, 0 78%, 0 22%, 20% 22%);
}

.cell-19 {
    background: var(--turquoise);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 66% 100%, 66% 64%, 0 64%);
}

.cell-20 {
    background: var(--orange);
    clip-path: polygon(0 0, 100% 0, 100% 76%, 76% 100%, 0 100%);
}

.cell-21 {
    background: var(--green);
    clip-path: polygon(0 0, 70% 0, 70% 30%, 100% 30%, 100% 100%, 0 100%);
}

.cell-22 {
    background: var(--purple);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 32% 100%, 32% 68%, 0 68%);
}

.cell-23 {
    background: var(--gray);
    clip-path: polygon(16% 0, 100% 0, 100% 84%, 84% 100%, 0 100%, 0 16%);
}

.cell-24 {
    background: var(--gold);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 36%, 36% 36%, 36% 0);
}

.cell-25 {
    clip-path: polygon(0 0, 100% 0, 100% 100%, 24% 100%, 24% 76%, 0 76%);
}

/* Mensajes creativos sobre el mosaico, únicamente en escritorio. */
:root {
    --page-bg: #000000;
    --card-bg: #000000;
    --card-soft: #101010;
}

html {
    scrollbar-width: thin;
    scrollbar-color: #117e8c #111111;
}

::-webkit-scrollbar {
    width: 11px;
    height: 0;
}

::-webkit-scrollbar-track {
    background: #111111;
}

::-webkit-scrollbar-thumb {
    min-height: 45px;
    border: 2px solid #111111;
    border-radius: 10px;
    background: #117e8c;
}

::-webkit-scrollbar-thumb:hover {
    background: #ef6c22;
}

::-webkit-scrollbar-corner {
    background: #111111;
}

html,
body,
.auth-card,
.auth-art,
.auth-content {
    background: #000000 !important;
}

.mosaic-headline {
    display: none;
}

.back-button {
    padding: 11px 16px !important;
    border: 1px solid #f5a900 !important;
    border-radius: 10px !important;
    background: var(--orange) !important;
    color: #ffffff !important;
    font-size: .8rem !important;
    font-weight: 700 !important;
    box-shadow: 0 10px 25px rgba(239, 108, 34, .3);
}

.back-button i {
    font-size: .72rem !important;
}

.back-button:hover {
    border-color: #ffffff !important;
    background: var(--turquoise) !important;
    color: #ffffff !important;
    box-shadow: 0 13px 30px rgba(17, 126, 140, .35);
}

.auth-topbar {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 26px;
}

.auth-topbar .auth-logo,
.auth-topbar .back-button {
    margin-bottom: 0 !important;
}

.auth-topbar .back-button {
    flex: 0 0 auto;
    white-space: nowrap;
}

@media (max-width: 420px) {
    .auth-topbar { gap: 10px; }
    .auth-topbar .back-button { padding: 9px 11px !important; font-size: .68rem !important; }
}

@media (min-width: 761px) {
    .mosaic-headline {
        position: absolute;
        top: clamp(30px, 5vh, 58px);
        left: 50%;
        z-index: 8;
        display: block;
        width: min(560px, calc(100% - 70px));
        transform: translateX(-50%);
        text-align: center;
        pointer-events: none;
    }

    .mosaic-kicker {
        display: block;
        margin-bottom: 10px;
        color: var(--gold);
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .18em;
    }

    .rotating-messages {
        position: relative;
        height: 70px;
        overflow: hidden;
    }

    .rotating-messages span {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px 7px;
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
        font-size: clamp(1.25rem, 2vw, 1.75rem);
        font-weight: 700;
        line-height: 1.3;
        opacity: 0;
        transform: translateY(18px);
        animation: rotateCreativeMessage 12s infinite;
    }

    .rotating-messages span:nth-child(2) { animation-delay: 4s; }
    .rotating-messages span:nth-child(3) { animation-delay: 8s; }

    .prodovi-mosaic {
        transform: translateY(54px);
    }
}

@keyframes rotateCreativeMessage {
    0% { opacity: 0; transform: translateY(18px); }
    7%, 27% { opacity: 1; transform: translateY(0); }
    33%, 100% { opacity: 0; transform: translateY(-18px); }
}

@media (prefers-reduced-motion: reduce) {
    .rotating-messages span { animation: none; }
    .rotating-messages span:first-child { opacity: 1; transform: none; }
}

</style>
