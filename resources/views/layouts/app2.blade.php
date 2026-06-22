<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente - @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/iconoweb.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-100">
    @include('componentes.navbar-cliente')
    
    <main class="container mx-auto ">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

