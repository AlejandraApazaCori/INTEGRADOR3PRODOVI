<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4f46e5; font-size: 18px; }
        .info { margin-bottom: 15px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th { background-color: #f3f4f6; color: #374151; font-weight: bold; text-transform: uppercase; border: 1px solid #d1d5db; padding: 6px; }
        td { border: 1px solid #d1d5db; padding: 6px; word-wrap: break-word; }
        .badge { padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="info">
        <p><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        @if($fecha_inicio || $fecha_fin)
            <p><strong>Rango de Fechas:</strong> 
                {{ $fecha_inicio ? \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') : 'Inicio' }} 
                - 
                {{ $fecha_fin ? \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') : 'Hoy' }}
            </p>
        @endif
        @if($page_from)
            <p><strong>Rango de páginas:</strong> {{ $page_from }} - {{ $page_to ?: 'última disponible' }} <span style="color:#6b7280;">(15 registros por página)</span></p>
        @endif
        <p><strong>Total de Registros:</strong> {{ count($logs) }}</p>
    </div>

    @yield('chart')

    @yield('table')

    <div class="footer">
        Generado automáticamente por el Sistema LLAJUAS - Integrador PRODOVI
    </div>
</body>
</html>
