<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Crecimiento de Audiencia - {{ $data['period_label'] ?? 'PRODOVI' }}</title>
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
                    <div class="reporte-titulo">Análisis de Crecimiento de Audiencia - {{ $data['period_label'] ?? 'Consolidado' }}</div>
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
                    <div class="kpi-valor">{{ $data['followers']['total'] ?? $data['followers']['new'] ?? '0' }}</div>
                    <span class="tendencia {{ ($data['followers']['trend'] ?? 'up') === 'up' ? 'tendencia-up' : 'tendencia-down' }}">
                        {{ $data['followers']['vs_previous'] ?? '0%' }} vs periodo anterior
                    </span>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <div class="insight-box">
                        <strong>Insight de Adquisición:</strong><br>
                        {{ $data['acquisition_insights'] ?? 'No hay suficientes datos para generar un insight este periodo.' }}
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
                        <tr>
                            <td>Facebook</td>
                            <td style="text-align: right; font-weight: bold;">{{ $data['followers']['facebook_count'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td>Instagram</td>
                            <td style="text-align: right; font-weight: bold;">{{ $data['followers']['instagram_count'] ?? 0 }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                    <div class="seccion-titulo" style="font-size: 12px; margin-bottom: 10px;">Balance de Adquisición</div>
                    <div style="margin-top: 10px;">
                        <span style="font-size: 11px;">Facebook ({{ $data['followers']['facebook_percent'] ?? 0 }}%)</span>
                        <div class="progreso-contenedor" style="width: 100%; margin-bottom: 10px;">
                            <div class="progreso-barra" style="width: {{ $data['followers']['facebook_percent'] ?? 0 }}%; background-color: #3b82f6;"></div>
                        </div>
                        <span style="font-size: 11px;">Instagram ({{ 100 - ($data['followers']['facebook_percent'] ?? 0) }}%)</span>
                        <div class="progreso-contenedor" style="width: 100%;">
                            <div class="progreso-barra" style="width: {{ 100 - ($data['followers']['facebook_percent'] ?? 0) }}%; background-color: #ec4899;"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="seccion-encabezado">
            <div class="seccion-titulo">Publicaciones con Mayor Conversión de Seguidores</div>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th>Formato</th>
                    <th>Fecha</th>
                    <th style="text-align: right;">Seguidores Totales</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['growth_publications'] ?? [] as $post)
                <tr>
                    <td>{{ $post['platform'] }}</td>
                    <td>{{ $post['type'] }}</td>
                    <td>{{ $post['date'] }}</td>
                    <td style="text-align: right; color: #2563eb; font-weight: bold;">+{{ $post['new_followers'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="pie-pagina">
        Este reporte de crecimiento de audiencia ha sido diseñado para el análisis estratégico de PRODOVI.<br>
        &copy; {{ date('Y') }} Marketing de Crecimiento.
    </div>

</body>
</html>
