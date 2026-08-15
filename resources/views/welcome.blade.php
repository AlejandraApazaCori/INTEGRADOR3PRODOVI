<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>PRODOVI</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/iconoweb.png') }}">
     <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@300;400;700&family=Varela+Round&display=swap" rel="stylesheet">
</head>
<body >
    @include('componentes.preloader')
    @include('componentes.navbar')
    @include('componentes.container1')
    @include('componentes.footer')

    @if (config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&amp;onload=onTurnstileLoad" async defer></script>
    @endif


    <!-- GSAP for smooth animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>

    <script src="{{ asset('js/welcome.js') }}"></script>
    @include('a.css.hero.container1')
    @include('a.js.hero.welcome')
    
</body>
</html>


