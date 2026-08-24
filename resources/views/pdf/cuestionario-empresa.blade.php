<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cuestionario - {{ $empresa->nombre_empresa }}</title>
    <style>
        @page{margin:22px 20px 66px}*{box-sizing:border-box}body{margin:0;color:#374151;font-family:DejaVu Sans,sans-serif;font-size:9px;line-height:1.55}.report-head{margin:-22px -20px 14px;padding:10px 22px;background:#343a40}.report-head table{width:100%;border-collapse:collapse}.report-head td{padding:0;border:0;vertical-align:middle}.report-head-logo{width:125px}.report-head-logo img{display:block;width:92px}.report-head-copy{text-align:right}.report-head h1{margin:0;color:#fff;font-size:17px}.report-head p{margin:4px 0 0;color:#d9ded6;font-size:8px}.pdf-footer{position:fixed;right:-20px;bottom:-66px;left:-20px;height:52px;background:#343a40}.pdf-footer table{width:100%;height:52px;border-collapse:collapse}.pdf-footer td{height:52px;padding:0 28px;border:0;vertical-align:middle}.pdf-footer-logo{width:120px}.pdf-footer-logo img{display:block;width:84px}.pdf-footer-copy{color:#d9ded6;font-size:7px;letter-spacing:.04em;text-align:center;text-transform:uppercase}.pdf-footer-page-space{width:120px}.company-block{margin-bottom:22px;padding:13px 15px;border:1px solid #d7d9dc;background:#f3f4f6}.company-block h2{margin:0;color:#343a40;font-size:16px}.company-block p{margin:4px 0 0;color:#6b7280}.topic{margin-bottom:22px;page-break-inside:auto}.topic-title{margin:0 0 12px;padding:7px 9px;border-left:4px solid #565d64;background:#f3f4f6;color:#343a40;font-size:13px}.topic-description{margin:-5px 0 12px;color:#6b7280;font-size:8px}.answer{margin-bottom:13px;page-break-inside:avoid}.question{margin-bottom:4px;color:#374151;font-weight:bold}.response{padding:7px 9px;border:1px solid #d7d9dc;background:#fff;color:#4b5563;white-space:pre-wrap}.response.empty{color:#92988f;font-style:italic}.response ul{margin:2px 0;padding-left:19px}.response li{margin:0 0 4px}.required{color:#b42323}
    </style>
</head>
<body>
    <footer class="pdf-footer"><table><tr>
        <td class="pdf-footer-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
        <td class="pdf-footer-copy">PRODOVI · Cuestionario administrativo empresarial</td>
        <td class="pdf-footer-page-space"></td>
    </tr></table></footer>

    <header class="report-head"><table><tr>
        <td class="report-head-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
        <td class="report-head-copy"><h1>Cuestionario empresarial</h1><p>Documento generado el {{ now()->format('d/m/Y H:i') }} · {{ $empresa->nombre_empresa }}</p></td>
    </tr></table></header>

    <section class="company-block">
        <h2>{{ $empresa->nombre_empresa }}</h2>
        <p>{{ $empresa->tipo_empresa }} · Propietario: {{ $empresa->usuario->name }} · {{ $empresa->usuario->email }}</p>
    </section>

    @foreach($temas as $tema)
        <section class="topic">
            <h2 class="topic-title">{{ $loop->iteration }}. {{ $tema->nombre_tema }}</h2>
            @if($tema->descripcion_tema)<p class="topic-description">{{ $tema->descripcion_tema }}</p>@endif
            @foreach($tema->preguntas as $pregunta)
                @php
                    $respuesta = trim((string) ($respuestas[$pregunta->id] ?? ''));
                    $valores = collect(preg_split('/\s*\|\s*/', $respuesta, -1, PREG_SPLIT_NO_EMPTY));
                @endphp
                <div class="answer">
                    <div class="question">{{ $pregunta->pregunta }} @if($pregunta->requerido)<span class="required">*</span>@endif</div>
                    @if($respuesta === '')
                        <div class="response empty">Sin respuesta</div>
                    @elseif($pregunta->tipo_respuesta === 'checkbox')
                        <div class="response"><ul>@foreach($valores as $valor)<li>{{ $valor }}</li>@endforeach</ul></div>
                    @else
                        <div class="response">{{ $respuesta }}</div>
                    @endif
                </div>
            @endforeach
        </section>
    @endforeach
    <script type="text/php">
        if (isset($pdf, $fontMetrics)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $pdf->page_text($pdf->get_width() - 108, $pdf->get_height() - 24, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 7, [1,1,1]);
        }
    </script>
</body>
</html>
