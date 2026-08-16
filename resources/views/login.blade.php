<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="color-scheme"
        content="dark only"
    >

    <title>PRODOVI - Iniciar sesión</title>


    {{-- =====================================================
         FUENTES
    ====================================================== --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet"
    >


    {{-- =====================================================
         ICONOS
    ====================================================== --}}

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    {{-- =====================================================
         CSS
    ====================================================== --}}

    @include('a.css.login.login')

</head>


<body>


    <main class="auth-page">


        <section class="auth-card">


            {{-- =================================================
                 MOSAICO PRODOVI
            ================================================== --}}

            <aside
                class="auth-art"
                aria-hidden="true"
            >

                <div class="mosaic-headline">
                    <span class="mosaic-kicker">TU PRÓXIMA GRAN IDEA EMPIEZA AQUÍ</span>
                    <div class="rotating-messages">
                        <span>Haz que tu marca sea imposible de ignorar.</span>
                        <span>Convierte creatividad en resultados reales.</span>
                        <span>Conecta, inspira y deja tu propia huella.</span>
                    </div>
                </div>

                <div class="prodovi-mosaic">


                    {{-- FILA 1 --}}

                    <span class="mosaic-cell cell-01"></span>
                    <span class="mosaic-cell cell-02"></span>
                    <span class="mosaic-cell cell-03"></span>
                    <span class="mosaic-cell cell-04"></span>
                    <span class="mosaic-cell cell-05"></span>


                    {{-- FILA 2 --}}

                    <span class="mosaic-cell cell-06"></span>
                    <span class="mosaic-cell cell-07"></span>
                    <span class="mosaic-cell cell-08"></span>
                    <span class="mosaic-cell cell-09"></span>
                    <span class="mosaic-cell cell-10"></span>


                    {{-- FILA 3 --}}

                    <span class="mosaic-cell cell-11"></span>
                    <span class="mosaic-cell cell-12"></span>
                    <span class="mosaic-cell cell-13"></span>
                    <span class="mosaic-cell cell-14"></span>
                    <span class="mosaic-cell cell-15"></span>


                    {{-- FILA 4 --}}

                    <span class="mosaic-cell cell-16"></span>
                    <span class="mosaic-cell cell-17"></span>
                    <span class="mosaic-cell cell-18"></span>
                    <span class="mosaic-cell cell-19"></span>
                    <span class="mosaic-cell cell-20"></span>


                    {{-- FILA 5 --}}

                    <span class="mosaic-cell cell-21"></span>
                    <span class="mosaic-cell cell-22"></span>
                    <span class="mosaic-cell cell-23"></span>
                    <span class="mosaic-cell cell-24"></span>
                    <span class="mosaic-cell cell-25"></span>

                </div>

            </aside>



            {{-- =================================================
                 FORMULARIOS
            ================================================== --}}

            <section class="auth-content">


                <div class="auth-content-inner">


                    {{-- =========================================
                         CABECERA
                    ========================================== --}}

                    <header class="auth-header">

                        <div class="auth-topbar">
                            <a
                                href="{{ url('/') }}"
                                class="auth-logo"
                                aria-label="Ir al inicio"
                            >
                                <img
                                    src="{{ asset('imagenes/logoblanco.png') }}"
                                    alt="PRODOVI"
                                >
                            </a>

                            <a
                                href="{{ url()->previous() }}"
                                class="back-button"
                            >
                                <i class="fas fa-chevron-left"></i>
                                <span>Volver al sitio</span>
                            </a>
                        </div>


                        <h1
                            class="auth-title"
                            id="form-title"
                        >
                            ¡Bienvenido!
                        </h1>


                        <p
                            class="auth-subtitle"
                            id="form-subtitle"
                        >
                            <a
                                href="#register"
                                class="inline-register-link"
                                id="header-register-link"
                            >
                                Crea una cuenta gratis
                            </a>

                            <span>
                                o inicia sesión para continuar en PRODOVI.
                            </span>
                        </p>

                    </header>



                    {{-- =========================================
                         CONTENEDOR
                    ========================================== --}}

                    <div class="form-container">


                        {{-- =====================================
                             LOGIN
                        ====================================== --}}

                        <form
                            class="form"
                            id="login-form"
                            method="POST"
                            action="{{ route('login.post') }}"
                        >

                            @csrf


                            {{-- EMAIL --}}

                            <div class="input-group">

                                <label
                                    class="input-label"
                                    for="login-email"
                                >
                                    Email
                                </label>


                                <div class="input-wrapper">

                                    <input
                                        type="email"
                                        class="input-field @error('email') input-error @enderror"
                                        id="login-email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="tu@email.com"
                                        autocomplete="email"
                                        required
                                    >

                                </div>


                                @error('email')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- PASSWORD --}}

                            <div class="input-group password-group">

                                <label
                                    class="input-label"
                                    for="login-password"
                                >
                                    Contraseña
                                </label>


                                <div class="password-input-wrapper">

                                    <input
                                        type="password"
                                        class="input-field @error('password') input-error @enderror"
                                        id="login-password"
                                        name="password"
                                        placeholder="••••••••"
                                        autocomplete="current-password"
                                        required
                                    >


                                    <i
                                        class="fas fa-eye toggle-password"
                                        toggle="#login-password"
                                        aria-label="Mostrar contraseña"
                                        role="button"
                                        tabindex="0"
                                    ></i>

                                </div>


                                @error('password')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- OLVIDASTE CONTRASEÑA --}}

                            @if (Route::has('password.request'))

                                <div class="forgot-row">

                                    <a
                                        href="{{ route('password.request') }}"
                                        class="forgot-password"
                                    >
                                        ¿Olvidaste tu contraseña?
                                    </a>

                                </div>

                            @endif



                            {{-- LOGIN --}}

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Iniciar sesión
                            </button>



                            {{-- GOOGLE --}}

                            <a
                                href="{{ route('auth.google.redirect') }}"
                                class="btn btn-google"
                                id="google-login"
                            >

                                <span class="google-mark">
                                    G
                                </span>

                                <span>
                                    Continuar con Google
                                </span>

                            </a>



                            {{-- CAMBIAR A REGISTRO --}}

                            <div class="form-switch">

                                <span>
                                    ¿No tienes una cuenta?
                                </span>

                                <a
                                    href="#register"
                                    class="switch-link"
                                    id="show-register"
                                >
                                    Crear cuenta
                                </a>

                            </div>

                        </form>



                        {{-- =====================================
                             REGISTRO
                        ====================================== --}}

                        <form
                            class="form hidden"
                            id="register-form"
                            method="POST"
                            action="{{ route('register') }}"
                        >

                            @csrf

                            @if(session('error'))
                                <div class="error-text" style="margin-bottom:16px;padding:11px 13px;border:1px solid rgba(239,108,34,.55);border-radius:10px;background:rgba(239,108,34,.1);">
                                    {{ session('error') }}
                                </div>
                            @endif



                            {{-- NOMBRE --}}

                            <div class="input-group">

                                <label
                                    class="input-label"
                                    for="register-name"
                                >
                                    Nombre completo
                                </label>


                                <input
                                    type="text"
                                    class="input-field @error('name') input-error @enderror"
                                    id="register-name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Tu nombre completo"
                                    autocomplete="name"
                                    required
                                >


                                @error('name')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- EMAIL --}}

                            <div class="input-group">

                                <label
                                    class="input-label"
                                    for="register-email"
                                >
                                    Email
                                </label>


                                <input
                                    type="email"
                                    class="input-field @error('email') input-error @enderror"
                                    id="register-email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="tu@email.com"
                                    autocomplete="email"
                                    required
                                >


                                @error('email')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- CELULAR --}}

                            <div class="input-group">

                                <label
                                    class="input-label"
                                    for="register-phone"
                                >
                                    Número de celular
                                </label>


                                <input
                                    type="tel"
                                    class="input-field @error('phone') input-error @enderror"
                                    id="register-phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="+591 62391234"
                                    autocomplete="tel"
                                    required
                                >


                                @error('phone')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- PASSWORD --}}

                            <div class="input-group password-group">

                                <label
                                    class="input-label"
                                    for="register-password"
                                >
                                    Contraseña
                                </label>


                                <div class="password-input-wrapper">

                                    <input
                                        type="password"
                                        class="input-field @error('password') input-error @enderror"
                                        id="register-password"
                                        name="password"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        required
                                    >


                                    <i
                                        class="fas fa-eye toggle-password"
                                        toggle="#register-password"
                                        aria-label="Mostrar contraseña"
                                        role="button"
                                        tabindex="0"
                                    ></i>

                                </div>


                                @error('password')

                                    <span class="error-text">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>



                            {{-- CONFIRMAR PASSWORD --}}

                            <div class="input-group password-group">

                                <label
                                    class="input-label"
                                    for="register-password-confirm"
                                >
                                    Confirmar contraseña
                                </label>


                                <div class="password-input-wrapper">

                                    <input
                                        type="password"
                                        class="input-field"
                                        id="register-password-confirm"
                                        name="password_confirmation"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        required
                                    >


                                    <i
                                        class="fas fa-eye toggle-password"
                                        toggle="#register-password-confirm"
                                        aria-label="Mostrar contraseña"
                                        role="button"
                                        tabindex="0"
                                    ></i>

                                </div>

                            </div>



                            {{-- CREAR CUENTA --}}

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Crear cuenta
                            </button>



                            {{-- GOOGLE --}}

                            <a
                                href="{{ route('auth.google.redirect') }}"
                                class="btn btn-google"
                                id="google-register"
                            >

                                <span class="google-mark">
                                    G
                                </span>

                                <span>
                                    Registrarse con Google
                                </span>

                            </a>



                            {{-- VOLVER LOGIN --}}

                            <div class="form-switch">

                                <span>
                                    ¿Ya tienes una cuenta?
                                </span>

                                <a
                                    href="#"
                                    class="switch-link"
                                    id="show-login"
                                >
                                    Iniciar sesión
                                </a>

                            </div>

                        </form>


                    </div>

                </div>

            </section>

        </section>

    </main>


    {{-- =====================================================
         TU JS ACTUAL
    ====================================================== --}}

    @include('a.js.login.login')


</body>
</html>
