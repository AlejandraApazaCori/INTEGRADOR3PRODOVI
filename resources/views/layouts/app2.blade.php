<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>document.documentElement.dataset.clientTheme=localStorage.getItem('clientTheme')||'light';</script>
    <title>Cliente - @yield('title')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('imagenes/favicon-prodovi.svg') }}?v=3">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-white">
    @include('componentes.navbar-cliente')
    
    <main class="client-main w-full">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

