<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page { margin: 22px 24px 68px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #374151; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .report-head { margin: -22px -24px 16px; padding: 11px 24px 10px; background: #343a40; }
        .report-head table { width: 100%; border-collapse: collapse; }
        .report-head td { padding: 0; border: 0; vertical-align: middle; }
        .report-head-logo { width: 135px; }
        .report-head-logo img { display: block; width: 100px; height: auto; }
        .report-head-copy { text-align: right; }
        .report-head h1 { margin: 0; color: #fff; font-size: 18px; }
        .report-head p { margin: 5px 0 0; color: #d9ded6; font-size: 9px; }
        .charts { width: 100%; margin: 0 0 15px; border-collapse: separate; border-spacing: 10px 0; table-layout: fixed; }
        .charts td { width: 50%; padding: 0; border: 1px solid #e1e3de; border-radius: 10px; vertical-align: top; }
        .charts img { display: block; width: 100%; max-height: 245px; }
        .users-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .users-table thead { display: table-header-group; }
        .users-table th { padding: 7px 6px; border: 1px solid #444a50; background: #565d64; color: #fff; font-size: 8px; text-align: left; }
        .users-table td { padding: 6px; border: 1px solid #d7d9dc; vertical-align: top; word-wrap: break-word; }
        .users-table tbody tr:nth-child(even) td { background: #f3f4f6; }
        .users-table tr { page-break-inside: avoid; }
        .users-table .number { width: 4%; text-align: center; }
        .users-table .name { width: 14%; }
        .users-table .email { width: 19%; }
        .users-table .phone { width: 12%; }
        .users-table .roles { width: 13%; }
        .users-table .plans { width: 15%; }
        .users-table .status { width: 10%; }
        .users-table .date { width: 13%; }
        .empty { padding: 24px !important; color: #737a70; text-align: center; }
        .pdf-footer { position: fixed; right: -24px; bottom: -68px; left: -24px; height: 54px; background: #343a40; }
        .pdf-footer table { width: 100%; height: 54px; border-collapse: collapse; }
        .pdf-footer td { height: 54px; padding: 0 32px; border: 0; vertical-align: middle; }
        .pdf-footer-logo { width: 125px; }
        .pdf-footer-logo img { display: block; width: 88px; height: auto; }
        .pdf-footer-copy { color: #d9ded6; font-size: 8px; letter-spacing: .04em; text-align: center; text-transform: uppercase; }
        .pdf-footer-page-space { width: 125px; }
    </style>
</head>
<body>
    <footer class="pdf-footer">
        <table>
            <tr>
                <td class="pdf-footer-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
                <td class="pdf-footer-copy">PRODOVI · Reporte administrativo de usuarios</td>
                <td class="pdf-footer-page-space"></td>
            </tr>
        </table>
    </footer>

    <header class="report-head">
        <table>
            <tr>
                <td class="report-head-logo"><img src="{{ public_path('imagenes/logoblanco.png') }}" alt="PRODOVI"></td>
                <td class="report-head-copy">
                    <h1>{{ $reportTitle }}</h1>
                    <p>Generado: {{ now()->format('d/m/Y H:i') }} · Total de registros: {{ $users->count() }}</p>
                </td>
            </tr>
        </table>
    </header>

    @if($roleChart && $statusChart)
        <table class="charts">
            <tr>
                <td><img src="{{ $roleChart }}" alt="Distribución por tipo de rol"></td>
                <td><img src="{{ $statusChart }}" alt="Distribución por estado"></td>
            </tr>
        </table>
    @endif

    <table class="users-table">
        <thead>
            <tr>
                <th class="number">N.º</th>
                <th class="name">Nombre</th>
                <th class="email">Correo</th>
                <th class="phone">Celular</th>
                <th class="roles">Roles</th>
                <th class="plans">{{ $report === 'without_plan' ? 'Otros planes' : 'Planes' }}</th>
                <th class="status">Estado</th>
                <th class="date">Fecha de registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                @php
                    $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty();
                    $isActive = $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty();
                    $status = $isAdmin ? 'Administrador' : ($isActive ? 'Activo' : ($user->suscripciones->isEmpty() ? 'Sin plan' : 'Inactivo'));
                @endphp
                <tr>
                    <td class="number">{{ $user->registration_number }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: 'No registrado' }}</td>
                    <td>{{ $user->roles->pluck('nombre_rol')->implode(', ') ?: 'Sin rol' }}</td>
                    <td>{{ $user->suscripciones->pluck('plan.nombre')->filter()->unique()->implode(', ') ?: 'Sin plan' }}</td>
                    <td>{{ $status }}</td>
                    <td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">No existen usuarios para este reporte.</td></tr>
            @endforelse
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf, $fontMetrics)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $text = 'Página {PAGE_NUM} de {PAGE_COUNT}';
            $pdf->page_text($pdf->get_width() - 112, $pdf->get_height() - 25, $text, $font, 8, [1, 1, 1]);
        }
    </script>
</body>
</html>
