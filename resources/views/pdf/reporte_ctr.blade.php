<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de CTR por Plataforma - {{ $data['company'] ?? 'PRODOVI' }}</title>
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
        .texto-verde { color: #059669; }
        .texto-gris-500 { color: #6b7280; }
        .fondo-indigo-900 { background-color: #1e1b4b; }
        .fondo-gris-50 { background-color: #f9fafb; }
        
        /* Layout */
        .contenedor {
            padding: 40px;
        }
        
        /* Header */
        .cabecera-banda {
            background-color: #065f46; /* Verde oscuro para CTR */
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
            color: #a7f3d0;
        }
        .generacion-fecha {
            text-align: right;
            font-size: 12px;
            vertical-align: bottom;
            color: #ecfdf5;
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
            width: 50%;
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
            font-size: 42px;
            font-weight: bold;
            color: #059669;
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

        /* Tabla de Datos */
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
            background-color: #f9fafb;
        }
        .tabla-datos td {
            font-size: 13px;
            padding: 12px 5px;
            border-bottom: 1px solid #f3f4f6;
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
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
        }

        .formula-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .formula-text {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            color: #334155;
        }
    </style>
</head>
<body>

    <div class="cabecera-banda">
        <table class="cabecera-tabla">
            <tr>
                <td>
                    <img src="{{ public_path('imagenes/logoblanco.png') }}" style="height: 40px; width: auto;">
                    <div class="reporte-titulo">{{ $data['company'] ?? 'PRODOVI' }} - CTR por Plataforma</div>
                </td>
                <td class="generacion-fecha">
                    Emitido el: {{ $fecha_generacion ?? date('d/m/Y') }}<br>
                    Período: {{ $data['period_label'] ?? 'General' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="contenedor">
        
        <div class="seccion-encabezado" style="margin-top: 0;">
            <div class="seccion-titulo">Descripción del Reporte</div>
        </div>
        <p style="font-size: 13px; line-height: 1.6; color: #4b5563;">
            Este reporte compara la tasa operativa de clics y acciones de Facebook e Instagram usando los datos disponibles en <strong>Meta Insights</strong>. Cuando Meta no entrega impresiones, se utilizan visualizaciones; cualquier componente aproximado aparece identificado como estimado.
        </p>

        <div class="formula-box">
            <div class="kpi-etiqueta" style="margin-bottom: 5px;">Fórmula utilizada</div>
            <div class="formula-text">CTR operativo = (Clics o acciones / Visualizaciones disponibles) * 100</div>
        </div>

        <!-- KPI Principal -->
        <table class="kpi-tabla">
            <tr>
                <td class="kpi-tarjeta">
                    <div class="kpi-etiqueta">CTR Operativo del Período</div>
                    <div class="kpi-valor">{{ data_get($data, 'conversion.rate') !== null ? number_format(data_get($data, 'conversion.rate'), 2, ',', '.').'%' : 'N/D' }}</div>
                    <span class="tendencia-distintivo tendencia-alza">
                        {{ data_get($data, 'conversion.estimated') ? 'Resultado mixto: contiene estimaciones' : 'Calculado con datos entregados por Meta' }}
                    </span>
                </td>
                <td style="vertical-align: top; padding-left: 20px;">
                    <div class="insight-box">
                        <strong>Análisis Automático:</strong>
                        @if(data_get($data, 'conversion.best_platform'))
                            La plataforma con la tasa más alta es <strong>{{ data_get($data, 'conversion.best_platform.platform') }}</strong>, con
                            <strong>{{ number_format(data_get($data, 'conversion.best_platform.ctr'), 2, ',', '.') }}%</strong>.
                            @if(data_get($data, 'conversion.best_platform.estimated'))
                                Este resultado es orientativo porque uno o más componentes fueron estimados.
                            @else
                                El resultado utiliza exclusivamente métricas disponibles en Meta.
                            @endif
                        @else
                            No existen suficientes visualizaciones y clics para calcular una tasa en este periodo.
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Tabla Comparativa -->
        <div class="seccion-encabezado">
            <div class="seccion-titulo">Comparativa por Plataforma</div>
        </div>
        
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th style="text-align: right;">Visualizaciones</th>
                    <th style="text-align: right;">Clics / acciones</th>
                    <th style="text-align: right;">CTR (%)</th>
                    <th style="text-align: right;">Calidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['conversion']['platform_metrics'] ?? [] as $metric)
                    <tr>
                        <td style="font-weight: bold;">{{ $metric['platform'] }}</td>
                        <td style="text-align: right;">{{ $metric['impressions'] !== null ? number_format($metric['impressions'], 0, ',', '.').($metric['views_estimated'] ? ' *' : '') : 'N/D' }}</td>
                        <td style="text-align: right;">{{ $metric['clicks'] !== null ? number_format($metric['clicks'], 0, ',', '.').($metric['clicks_estimated'] ? ' *' : '') : 'N/D' }}</td>
                        <td style="text-align: right; color: #059669; font-weight: bold;">{{ $metric['ctr'] !== null ? number_format($metric['ctr'], 2, ',', '.').'%' : 'N/D' }}</td>
                        <td style="text-align: right; color: {{ $metric['estimated'] ? '#92400e' : '#065f46' }};">{{ $metric['estimated'] ? 'Estimado' : 'Meta' }}</td>
                    </tr>
                @endforeach
                @if(empty($data['conversion']['platform_metrics']))
                    <tr><td colspan="5" style="text-align: center; color: #6b7280;">No hay cuentas de Meta conectadas.</td></tr>
                @endif
            </tbody>
        </table>

        <!-- Espacio para notas -->
        <div class="seccion-encabezado" style="margin-top: 40px;">
            <div class="seccion-titulo">Interpretación y Recomendaciones</div>
        </div>
        <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <p style="font-size: 12px; color: #475569; line-height: 1.6; margin: 0;">
                Esta tasa sirve como señal comparativa del interés generado, pero no sustituye el CTR publicitario de Ads Manager. Conviene revisar llamadas a la acción, enlaces y formatos de
                <strong>{{ data_get($data, 'conversion.best_platform.platform', 'la plataforma con mejores resultados') }}</strong>.
                @if(data_get($data, 'conversion.estimated')) Los valores marcados con * son aproximaciones construidas con alcance o interacciones reales disponibles. @endif
            </p>
        </div>

    </div>

    <div class="pie-pagina">
        Fuente: {{ $data['data_source'] ?? 'Meta Insights' }}. Los valores estimados se identifican con * y no equivalen a datos oficiales de Ads Manager.<br>
        &copy; {{ date('Y') }} Marketing Digital Inteligente.
    </div>

</body>
</html>
