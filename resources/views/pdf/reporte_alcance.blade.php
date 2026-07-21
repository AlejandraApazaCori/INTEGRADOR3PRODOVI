<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Alcance Profesional - {{ $data['period_label'] ?? 'PRODOVI' }}</title>
    <style>
        @page { margin: 0cm 0cm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .texto-purple { color: #9333ea; }
        .fondo-purple-900 { background-color: #3b0764; }
        
        .cabecera-banda {
            background-color: #3b0764;
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
            color: #d8b4fe;
        }
        .generacion-fecha {
            text-align: right;
            font-size: 12px;
            vertical-align: bottom;
            color: #f5f3ff;
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
        .kpi-valor { font-size: 42px; font-weight: bold; color: #9333ea; margin: 5px 0; }
        .tendencia { font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 12px; }
        .tendencia-up { background-color: #d1fae5; color: #065f46; }
        .tendencia-down { background-color: #fee2e2; color: #991b1b; }

        .chart-placeholder {
            background-color: #f3f4f6;
            height: 150px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 15px;
        }

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
    </style>
</head>
<body>

    <div class="cabecera-banda">
        <table class="cabecera-tabla">
            <tr>
                <td>
                    <img src="{{ public_path('imagenes/logoblanco.png') }}" style="height: 40px;">
                    <div class="reporte-titulo">Análisis de Alcance (Reach) - {{ $data['period_label'] ?? 'Consolidado' }}</div>
                </td>
                <td class="generacion-fecha">
                    Emitido el: {{ $fecha_generacion ?? date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="contenedor">
        
        <div class="seccion-encabezado" style="margin-top: 0;">
            <div class="seccion-titulo">Métricas de Alcance Principal</div>
        </div>
        
        <table class="kpi-tabla">
            <tr>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Alcance Total del Periodo</div>
                    <div class="kpi-valor">{{ $data['reach']['total'] ?? '0' }}</div>
                    <span class="tendencia {{ ($data['reach']['trend'] ?? 'up') === 'up' ? 'tendencia-up' : 'tendencia-down' }}">
                        {{ $data['reach']['vs_previous'] ?? '0%' }} vs periodo anterior
                    </span>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <div style="background-color: #f5f3ff; border-left: 4px solid #9333ea; padding: 15px; border-radius: 0 8px 8px 0; font-size: 13px;">
                        <strong>Análisis:</strong> El alcance ha mostrado un crecimiento del <strong>{{ $data['reach']['vs_previous'] ?? '0%' }}</strong>, impulsado principalmente por el rendimiento en <strong>Facebook</strong> ({{ $data['reach_by_platform'][0]['percentage'] ?? 0 }}%) y por una audiencia concentrada en <strong>{{ $data['audience']['summary']['location'] ?? 'La Paz, Bolivia' }}</strong>.
                    </div>
                </td>
            </tr>
        </table>

        <div class="seccion-encabezado">
            <div class="seccion-titulo">Alcance por Plataforma y Formato</div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 48%; vertical-align: top; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                    <div class="seccion-titulo" style="font-size: 12px; margin-bottom: 10px;">Distribución por Plataforma</div>
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>Plataforma</th>
                                <th style="text-align: right;">Alcance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['reach_by_platform'] ?? [] as $platform)
                            <tr>
                                <td>{{ $platform['platform'] }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ $platform['reach'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                    <div class="seccion-titulo" style="font-size: 12px; margin-bottom: 10px;">Efectividad por Formato</div>
                    <table class="tabla-datos">
                        @foreach($data['reach_by_type'] ?? [] as $type)
                        <tr>
                            <td style="font-size: 11px;">{{ $type['type'] }}</td>
                            <td style="width: 100px;">
                                <div class="progreso-contenedor">
                                    <div class="progreso-barra" style="width: {{ $type['percentage'] }}%; background-color: #9333ea;"></div>
                                </div>
                            </td>
                            <td style="text-align: right; font-size: 11px; font-weight: bold;">{{ $type['percentage'] }}%</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        <div class="seccion-encabezado">
            <div class="seccion-titulo">Publicaciones con Mayor Alcance</div>
        </div>

        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th>Formato</th>
                    <th>Fecha</th>
                    <th style="text-align: right;">Alcance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_publications'] ?? [] as $post)
                <tr>
                    <td>{{ $post['platform'] ?? 'N/A' }}</td>
                    <td>{{ $post['type'] }}</td>
                    <td>{{ $post['date'] }}</td>
                    <td style="text-align: right; color: #9333ea; font-weight: bold;">{{ $post['reach'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="pie-pagina">
        Este informe técnico de alcance ha sido generado para el análisis de rendimiento de PRODOVI.<br>
        &copy; {{ date('Y') }} Marketing Estratégico.
    </div>

</body>
</html>
