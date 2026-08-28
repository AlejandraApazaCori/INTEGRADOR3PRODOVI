<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Plan de marketing - {{ $planMarketing->empresa->nombre_empresa }}</title>
    <style>
        @page{margin:22px 20px 66px}*{box-sizing:border-box}body{margin:0;color:#374151;font-family:DejaVu Sans,sans-serif;font-size:9px;line-height:1.55}.report-head{margin:-22px -20px 14px;padding:10px 22px;background:#343a40}.report-head table{width:100%;border-collapse:collapse}.report-head td{padding:0;border:0;vertical-align:middle}.report-head-logo{width:125px}.report-head-logo img{display:block;width:92px}.report-head-copy{text-align:right}.report-head h1{margin:0;color:#fff;font-size:17px}.report-head p{margin:4px 0 0;color:#d9ded6;font-size:8px}.pdf-footer{position:fixed;right:-20px;bottom:-66px;left:-20px;height:52px;background:#343a40}.pdf-footer table{width:100%;height:52px;border-collapse:collapse}.pdf-footer td{height:52px;padding:0 28px;border:0;vertical-align:middle}.pdf-footer-logo{width:120px}.pdf-footer-logo img{display:block;width:84px}.pdf-footer-copy{color:#d9ded6;font-size:7px;letter-spacing:.04em;text-align:center;text-transform:uppercase}.pdf-footer-page-space{width:120px}.company-block{margin-bottom:22px;padding:13px 15px;border:1px solid #d7d9dc;background:#f3f4f6}.company-block h2{margin:0;color:#343a40;font-size:16px}.company-block p{margin:4px 0 0;color:#6b7280}.document-label{margin-top:8px;color:#687064;font-size:8px;font-style:italic}.plan-section{margin-bottom:22px;page-break-inside:auto}.section-title{margin:0 0 12px;padding:7px 9px;border-left:4px solid #565d64;background:#f3f4f6;color:#343a40;font-size:13px;page-break-after:avoid}.section-content{color:#4b5563}.section-content p{margin:0 0 9px}.section-content h3{margin:14px 0 7px;color:#343a40;font-size:10px}.section-content strong{color:#374151;font-weight:bold}.section-content ul,.section-content ol{margin:6px 0 10px;padding-left:21px}.section-content li{margin:0 0 4px}.section-content table{width:100%;margin:9px 0 13px;border-collapse:collapse;page-break-inside:avoid;font-size:8px}.section-content th{padding:7px 8px;border:1px solid #c7cbd1;background:#e5e7eb;color:#343a40;text-align:left}.section-content td{padding:7px 8px;border:1px solid #d7d9dc;vertical-align:top}.section-content tr:nth-child(even) td{background:#f7f7f8}.section-content blockquote{margin:9px 0;padding:7px 9px;border-left:4px solid #565d64;background:#f3f4f6;color:#596156}
    </style>
</head>
<body>
    <footer class="pdf-footer"><table><tr>
        <td class="pdf-footer-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
        <td class="pdf-footer-copy">PRODOVI · Plan de marketing empresarial</td>
        <td class="pdf-footer-page-space"></td>
    </tr></table></footer>

    <header class="report-head"><table><tr>
        <td class="report-head-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
        <td class="report-head-copy"><h1>Plan de marketing empresarial</h1><p>Documento generado el {{ now()->format('d/m/Y H:i') }} · {{ $planMarketing->empresa->nombre_empresa }}</p></td>
    </tr></table></header>

    <section class="company-block">
        <h2>{{ $planMarketing->empresa->nombre_empresa }}</h2>
        <p>{{ $planMarketing->suscripcion->plan->nombre }} · Estado: {{ ucfirst($planMarketing->estado) }} · Creado el {{ $planMarketing->created_at->format('d/m/Y') }}</p>
        <div class="document-label">Estrategia operativa elaborada a partir del cuestionario, el resumen ejecutivo y los recursos contratados.</div>
    </section>

    @forelse($seccionesParseadas as $seccion)
        <section class="plan-section">
            <h2 class="section-title">{{ $loop->iteration }}. {{ $seccion['titulo'] }}</h2>
            <div class="section-content">{!! $seccion['html'] !!}</div>
        </section>
    @empty
        <section class="company-block"><p>No hay información estructurada disponible para mostrar.</p></section>
    @endforelse

    <script type="text/php">
        if (isset($pdf, $fontMetrics)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $pdf->page_text($pdf->get_width() - 108, $pdf->get_height() - 24, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7, [1,1,1]);
        }
    </script>
</body>
</html>
