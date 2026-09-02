<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Crecimiento de Audiencia - {{ $data['company'] ?? 'PRODOVI' }}</title>
    <style>
        @page { margin: 0cm 0cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .texto-blue { color: #2563eb; }
        .fondo-blue-900 { background-color: #1e3a8a; }
        
        .cabecera-banda {
            background-color: #1e3a8a;
            color: white;
            padding: 30px 40px;
            width: 100%;
        }
        .cabecera-tabla { width: 100%; border-collapse: collapse; }
        .agencia-nombre { font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .reporte-titulo {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
            color: #93c5fd;
        }
        .generacion-fecha {
            text-align: right;
            font-size: 12px;
            vertical-align: bottom;
            color: #dbeafe;
            padding-right: 80px;
        }

        .contenedor { padding: 40px; }
        .seccion-encabezado {
            border-bottom: 2px solid #e5e7eb;
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .seccion-titulo {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            letter-spacing: 1px;
        }

        .kpi-tabla { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; }
        .kpi-tarjeta {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            width: 50%;
        }
        .kpi-etiqueta { font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase; }
        .kpi-valor { font-size: 42px; font-weight: bold; color: #2563eb; margin: 5px 0; }
        .tendencia { font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 12px; }
        .tendencia-up { background-color: #d1fae5; color: #065f46; }
        .tendencia-down { background-color: #fee2e2; color: #991b1b; }

        .tabla-datos { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .tabla-datos th {
            text-align: left;
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 5px;
        }
        .tabla-datos td { font-size: 13px; padding: 10px 5px; border-bottom: 1px solid #f3f4f6; }

        .progreso-contenedor { height: 8px; background-color: #e5e7eb; border-radius: 4px; overflow: hidden; width: 100px; }
        .progreso-barra { height: 100%; border-radius: 4px; }

        .pie-pagina {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            background-color: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }

        .insight-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="cabecera-banda">
        <table class="cabecera-tabla">
            <tr>
                <td>
                    <img src="{{ public_path('imagenes/logoblanco.png') }}" style="height: 40px;">
                    <div class="reporte-titulo">{{ $data['company'] ?? 'PRODOVI' }} - {{ $data['period_label'] ?? 'Consolidado' }}</div>
                </td>
                <td class="generacion-fecha">
                    Emitido el: {{ $fecha_generacion ?? date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="contenedor">
        
        <div class="seccion-encabezado" style="margin-top: 0;">
            <div class="seccion-titulo">Métricas de Crecimiento</div>
        </div>
        
        <table class="kpi-tabla">
            <tr>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Seguidores Totales</div>
                    <div class="kpi-valor">{{ data_get($data, 'followers.available') ? number_format(data_get($data, 'followers.total', 0), 0, ',', '.') : 'N/D' }}</div>
                    @if(data_get($data, 'followers.growth_available'))
                        <span class="tendencia {{ data_get($data, 'followers.net_growth', 0) >= 0 ? 'tendencia-up' : 'tendencia-down' }}">
                            {{ data_get($data, 'followers.net_growth', 0) >= 0 ? '+' : '' }}{{ number_format(data_get($data, 'followers.net_growth', 0), 0, ',', '.') }}
                            @if(data_get($data, 'followers.growth_percent') !== null)
                                ({{ number_format(data_get($data, 'followers.growth_percent'), 2, ',', '.') }}%)
                            @endif
                            en la serie disponible
                        </span>
                    @else
                        <span class="tendencia tendencia-up">Total actual obtenido de {{ $data['data_source'] ?? 'Meta Insights' }}</span>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <div class="insight-box">
                        <strong>Análisis de adquisición:</strong><br>
                        @if(data_get($data, 'followers.growth_available'))
                            La audiencia registró una variación neta de
                            <strong>{{ data_get($data, 'followers.net_growth', 0) >= 0 ? '+' : '' }}{{ number_format(data_get($data, 'followers.net_growth', 0), 0, ',', '.') }}</strong> seguidores.
                            @if(data_get($data, 'growth_leader.platform'))
                                La mayor variación correspondió a <strong>{{ data_get($data, 'growth_leader.platform') }}</strong>.
                            @endif
                        @else
                            Meta entrega el total actual, pero no una serie histórica suficiente para calcular crecimiento sin estimaciones.
                        @endif
                        @if(data_get($data, 'measurement.since') && data_get($data, 'measurement.until'))
                            Medición disponible: {{ data_get($data, 'measurement.since') }} al {{ data_get($data, 'measurement.until') }}.
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="seccion-encabezado">
            <div class="seccion-titulo">Distribución por Plataforma</div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; vertical-align: top; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                    <div class="seccion-titulo" style="font-size: 12px; margin-bottom: 10px;">Seguidores por Red Social</div>
                    <table class="tabla-datos">
                        @foreach($data['platforms'] ?? [] as $platform)
                        <tr>
                            <td>{{ $platform['platform'] }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $platform['current'] !== null ? number_format($platform['current'], 0, ',', '.') : 'N/D' }}</td>
                        </tr>
                        @endforeach
                        @if(empty($data['platforms']))
                        <tr><td style="color: #6b7280;">No hay cuentas de Meta conectadas.</td></tr>
                        @endif
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                    <div class="seccion-titulo" style="font-size: 12px; margin-bottom: 10px;">Balance de Adquisición</div>
                    <div style="margin-top: 10px;">
                        @foreach($data['platforms'] ?? [] as $platform)
                        <span style="font-size: 11px;">{{ $platform['platform'] }} ({{ $platform['percentage'] !== null ? number_format($platform['percentage'], 1, ',', '.').'%' : 'N/D' }})</span>
                        <div class="progreso-contenedor" style="width: 100%; margin-bottom: 10px;">
                            <div class="progreso-barra" style="width: {{ $platform['percentage'] ?? 0 }}%; background-color: {{ strtolower($platform['platform']) === 'facebook' ? '#3b82f6' : '#ec4899' }};"></div>
                        </div>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>

        <div class="seccion-encabezado">
            <div class="seccion-titulo">Crecimiento Medido por Plataforma</div>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th style="text-align: right;">Inicio de serie</th>
                    <th style="text-align: right;">Total actual</th>
                    <th style="text-align: right;">Variación neta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['platforms'] ?? [] as $platform)
                <tr>
                    <td>{{ $platform['platform'] }}</td>
                    <td style="text-align: right;">{{ $platform['initial'] !== null ? number_format($platform['initial'], 0, ',', '.') : 'N/D' }}</td>
                    <td style="text-align: right;">{{ $platform['current'] !== null ? number_format($platform['current'], 0, ',', '.') : 'N/D' }}</td>
                    <td style="text-align: right; color: #2563eb; font-weight: bold;">
                        @if($platform['growth_available'])
                            {{ $platform['change'] >= 0 ? '+' : '' }}{{ number_format($platform['change'], 0, ',', '.') }}
                            @if($platform['growth_percent'] !== null) ({{ number_format($platform['growth_percent'], 2, ',', '.') }}%) @endif
                        @else
                            N/D
                        @endif
                    </td>
                </tr>
                @endforeach
                @if(empty($data['platforms']))
                <tr><td colspan="4" style="text-align: center; color: #6b7280;">No existen cuentas conectadas para este reporte.</td></tr>
                @endif
            </tbody>
        </table>

    </div>

    <div class="pie-pagina">
        Fuente: {{ $data['data_source'] ?? 'Meta Insights' }}. Las publicaciones no se atribuyen a nuevos seguidores porque Meta no entrega esa relación.<br>
        &copy; {{ date('Y') }} Marketing de Crecimiento.
    </div>

</body>
</html>
