<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Engagement - {{ $data['company'] ?? 'PRODOVI' }}</title>
    <style>
        /* Tipografía y general */
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Colores y Utilidades */
        .texto-indigo { color: #4f46e5; }
        .texto-gris-500 { color: #6b7280; }
        .fondo-indigo-900 { background-color: #1e1b4b; }
        .fondo-gris-50 { background-color: #f9fafb; }
        
        /* Layout */
        .contenedor {
            padding: 40px;
        }
        
        /* Header */
        .cabecera-banda {
            background-color: #1e1b4b;
            color: white;
            padding: 30px 40px;
            width: 100%;
        }
        .cabecera-tabla {
            width: 100%;
            border-collapse: collapse;
        }
        .agencia-nombre {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .reporte-titulo {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
            color: #a5b4fc;
        }
        .generacion-fecha {
            text-align: right;
            font-size: 12px;
            vertical-align: bottom;
            color: #e0e7ff;
            padding-right: 80px;
        }

        /* Secciones */
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

        /* Cuadrícula de KPIs */
        .kpi-tabla {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-left: -10px;
            margin-right: -10px;
        }
        .kpi-tarjeta {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            width: 25%;
            text-align: left;
        }
        .kpi-etiqueta {
            font-size: 11px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .kpi-valor {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            margin: 5px 0;
        }
        .tendencia-distintivo {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .tendencia-alza {
            background-color: #d1fae5;
            color: #065f46;
        }
        .tendencia-baja {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Contenido Principal (Gráficos/Tablas) */
        .contenido-principal-tabla {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .contenido-tarjeta {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            vertical-align: top;
        }
        .tabla-datos {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .tabla-datos th {
            text-align: left;
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 5px;
        }
        .tabla-datos td {
            font-size: 13px;
            padding: 10px 5px;
            border-bottom: 1px solid #f3f4f6;
        }

        /* Barras de progreso */
        .progreso-contenedor {
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            width: 100%;
            margin-top: 10px;
            overflow: hidden;
        }
        .progreso-barra {
            height: 100%;
            border-radius: 3px;
        }

        /* Estilos demográficos */
        .demo-fila {
            margin-bottom: 15px;
        }
        .demo-etiqueta {
            font-size: 12px;
            color: #4b5563;
        }
        .demo-porcentaje {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            float: right;
        }

        /* Footer */
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
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="cabecera-banda">
        <table class="cabecera-tabla">
            <tr>
                <td>
                    <img src="{{ public_path('imagenes/logoblanco.png') }}" style="height: 40px; width: auto;">
                    <div class="reporte-titulo">{{ $data['company'] ?? 'PRODOVI' }} - {{ $data['period_label'] ?? 'Consolidado' }}</div>
                </td>
                <td class="generacion-fecha">
                    Emitido el: {{ $fecha_generacion ?? date('d/m/Y') }}<br>
                </td>
            </tr>
        </table>
    </div>

    <div class="contenedor">
        
        <!-- KPIs Principales -->
        <div class="seccion-encabezado" style="margin-top: 0;">
            <div class="seccion-titulo">Métrica de Engagement Principal</div>
        </div>
        
        <table class="kpi-tabla">
            <tr>
                <!-- Engagement Rate -->
                <td class="kpi-tarjeta" style="width: 50%;">
                    <div class="kpi-etiqueta">Tasa de interacción sobre alcance</div>
                    <div class="kpi-valor" style="font-size: 42px; color: #4f46e5;">{{ $data['engagement']['rate'] !== null ? number_format($data['engagement']['rate'], 2, ',', '.').'%' : 'N/D' }}</div>
                    <span class="tendencia-distintivo tendencia-alza">
                        {{ number_format($data['engagement']['interactions'] ?? 0, 0, ',', '.') }} interacciones sobre {{ number_format($data['engagement']['reach'] ?? 0, 0, ',', '.') }} de alcance
                    </span>
                </td>
                
                <!-- Desglose Rápido -->
                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <div class="insight-box" style="margin-top: 0;">
                        <strong>{{ $data['optimal_time']['estimated'] ? 'Horario orientativo' : 'Pico de actividad real' }}:</strong>
                        <strong>{{ $data['optimal_time']['range'] }}</strong>.
                        @if($data['optimal_time']['estimated']) Esta franja es una recomendación estratégica porque todavía no existe una muestra suficiente. @else Calculado con {{ $data['optimal_time']['samples'] }} publicaciones comparables. @endif
                    </div>
                    <div class="insight-box" style="border-left-color: #10b981; background-color: #ecfdf5; margin-bottom: 0;">
                        <strong>Audiencia disponible:</strong>
                        @if(!$data['audience']['estimated'])
                            {{ data_get($data, 'audience.age.name', 'Edad no disponible') }} y {{ data_get($data, 'audience.gender.name', 'sexo no disponible') }}, según los datos liberados por Instagram.
                        @else
                            Meta todavía no liberó una muestra demográfica suficiente; no se presenta un segmento ficticio como dato real.
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Interacciones Totales -->
        <div class="seccion-encabezado">
            <div class="seccion-titulo">Desglose de Interacciones Totales</div>
        </div>
        
        <table class="kpi-tabla">
            <tr>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Likes</div>
                    <div class="kpi-valor" style="font-size: 22px;">{{ number_format($data['interactions_breakdown']['likes'] ?? 0) }}</div>
                </td>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Comentarios</div>
                    <div class="kpi-valor" style="font-size: 22px;">{{ number_format($data['interactions_breakdown']['comments'] ?? 0) }}</div>
                </td>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Compartidos</div>
                    <div class="kpi-valor" style="font-size: 22px;">{{ number_format($data['interactions_breakdown']['shares'] ?? 0) }}</div>
                </td>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">Guardados</div>
                    <div class="kpi-valor" style="font-size: 22px;">{{ number_format($data['interactions_breakdown']['saves'] ?? 0) }}</div>
                </td>
            </tr>
        </table>

        <!-- Detalle por Tipo y Plataforma -->
        <table class="contenido-principal-tabla">
            <tr>
                <td class="contenido-tarjeta" style="width: 60%; border-right: none;">
                    <div class="seccion-titulo" style="font-size: 13px; margin-bottom: 10px;">Efectividad por Tipo de Contenido</div>
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>Formato</th>
                                <th style="text-align: center;">Promedio / post</th>
                                <th style="text-align: right;">Interacciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['engagement_by_type'] ?? [] as $item)
                                <tr>
                                    <td style="font-weight: bold;">{{ $item['type'] }}</td>
                                    <td style="text-align: center;">{{ number_format($item['average'], 1, ',', '.') }}</td>
                                    <td style="text-align: right;">{{ number_format($item['interactions']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="contenido-tarjeta" style="width: 40%; background-color: #f9fafb;">
                    <div class="seccion-titulo" style="font-size: 13px; margin-bottom: 15px;">Rendimiento por Plataforma</div>
                    @php $maxPlatformAverage = max(1, collect($data['engagement_by_platform'] ?? [])->max('average') ?? 1); @endphp
                    @foreach($data['engagement_by_platform'] ?? [] as $platform)
                        <div style="margin-bottom: 15px;">
                            <span style="font-size: 12px; color: #4b5563;">{{ $platform['platform'] }}</span>
                            <span style="float: right; font-size: 12px; font-weight: bold;">{{ number_format($platform['average'], 1, ',', '.') }} por post</span>
                            <div class="progreso-contenedor">
                                @php $width = min(($platform['average'] / $maxPlatformAverage) * 100, 100); @endphp
                                <div class="progreso-barra" style="width: {{ $width }}%; background-color: {{ $platform['platform'] == 'Instagram' ? '#ec4899' : '#3b82f6' }};"></div>
                            </div>
                            <div style="font-size: 10px; margin-top: 4px;" class="tendencia-alza">
                                {{ number_format($platform['interactions'], 0, ',', '.') }} interacciones en {{ $platform['posts'] }} publicaciones
                            </div>
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>

        <!-- Insights de comportamiento -->
        <div class="seccion-encabezado">
            <div class="seccion-titulo">Interpretación Estratégica</div>
        </div>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 100%; vertical-align: top; background-color: #f1f5f9; padding: 20px; border-radius: 8px;">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #1e1b4b;">Análisis de Resultados</div>
                    <p style="font-size: 12px; color: #475569; line-height: 1.6; margin: 0;">
                        En {{ $data['period_label'] }}, Meta registró <strong>{{ number_format($data['engagement']['interactions'] ?? 0, 0, ',', '.') }} interacciones</strong>
                        en <strong>{{ number_format($data['totals']['posts'] ?? 0, 0, ',', '.') }} publicaciones</strong>, con un promedio de
                        <strong>{{ number_format($data['engagement']['average_per_post'] ?? 0, 1, ',', '.') }}</strong> interacciones por publicación.
                        @if($data['engagement']['rate'] !== null)
                            La tasa calculada sobre el alcance disponible es de <strong>{{ number_format($data['engagement']['rate'], 2, ',', '.') }}%</strong>.
                        @endif
                        Se recomienda priorizar los formatos con mayor promedio real de interacciones y publicar alrededor de <strong>{{ $data['optimal_time']['range'] }}</strong>.
                        @if($data['optimal_time']['estimated']) Esta recomendación horaria es orientativa y no representa una medición entregada por Meta. @endif
                    </p>
                </td>
            </tr>
        </table>

    </div>

    <div class="pie-pagina">
        Cifras obtenidas de {{ $data['data_source'] }}. Las recomendaciones marcadas como orientativas no representan mediciones de Meta.<br>
        &copy; {{ date('Y') }} Marketing Estratégico. Todos los derechos reservados.
    </div>

</body>
</html>
