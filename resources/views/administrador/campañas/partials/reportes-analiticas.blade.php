@if($empresa)
@php
    $analyticsReports = [
        ['title' => 'Tasa de Engagement', 'description' => 'Interacciones, rendimiento y audiencia.', 'icon' => 'fa-heart', 'color' => 'purple', 'url' => route('clientes.analiticas.reporte-engagement')],
        ['title' => 'Alcance Total', 'description' => 'Cobertura, publicaciones y plataformas.', 'icon' => 'fa-signal', 'color' => 'teal', 'url' => route('clientes.analiticas.reporte-alcance')],
        ['title' => 'Seguidores', 'description' => 'Crecimiento y evolución por plataforma.', 'icon' => 'fa-users', 'color' => 'blue', 'url' => route('clientes.analiticas.reporte-seguidores')],
        ['title' => 'CTR', 'description' => 'Clics, visualizaciones y tasa de respuesta.', 'icon' => 'fa-arrow-pointer', 'color' => 'green', 'url' => route('clientes.analiticas.reporte-ctr')],
    ];
@endphp
<section class="campaign-report-panel" data-campaign-reports data-company-id="{{ $empresa->id }}">
    <header><div><small>REPORTES PDF</small><h2>Informes de rendimiento</h2><p>Los mismos reportes disponibles para el cliente, aplicados a {{ $empresa->nombre_empresa }}.</p></div><i class="fas fa-file-pdf"></i></header>
    <div class="campaign-report-grid">
        @foreach($analyticsReports as $report)
            <a href="{{ $report['url'] }}?view=historial&empresa_id={{ $empresa->id }}" data-report-base="{{ $report['url'] }}" class="is-{{ $report['color'] }}">
                <span><i class="fas {{ $report['icon'] }}"></i></span>
                <div><strong>{{ $report['title'] }}</strong><small>{{ $report['description'] }}</small></div>
                <b><i class="fas fa-download"></i> PDF</b>
            </a>
        @endforeach
    </div>
</section>

<style>
.campaign-report-panel{margin:0 0 16px;padding:20px;border:1px solid #e2dee5;border-radius:13px;background:#fff;color:#302834}.campaign-report-panel>header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:15px}.campaign-report-panel>header small{display:block;color:#5b2b76;font-size:.55rem;font-weight:900;letter-spacing:.1em}.campaign-report-panel>header h2{margin:3px 0 0;font-size:1rem;font-weight:900}.campaign-report-panel>header p{margin:4px 0 0;color:#817986;font-size:.63rem}.campaign-report-panel>header>i{color:#dc2626;font-size:1.5rem}.campaign-report-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.campaign-report-grid>a{display:grid;grid-template-columns:38px minmax(0,1fr);align-items:center;gap:10px;padding:13px;border:1px solid #e7e2e9;border-radius:10px;background:#faf9fb;color:#403646;text-decoration:none;transition:.18s}.campaign-report-grid>a:hover{transform:translateY(-2px);border-color:#bca9c7;box-shadow:0 7px 16px rgba(48,40,52,.09)}.campaign-report-grid>a>span{width:38px;height:38px;display:grid;place-items:center;border-radius:9px;background:#f1eaf5;color:#5b2b76}.campaign-report-grid>a.is-teal>span{background:#e7f4f5;color:#117e8c}.campaign-report-grid>a.is-blue>span{background:#eaf1ff;color:#2563eb}.campaign-report-grid>a.is-green>span{background:#edf6e7;color:#638524}.campaign-report-grid strong,.campaign-report-grid small{display:block}.campaign-report-grid strong{font-size:.67rem;font-weight:900}.campaign-report-grid small{margin-top:3px;color:#847b87;font-size:.53rem;line-height:1.35}.campaign-report-grid b{grid-column:1/-1;display:flex;align-items:center;justify-content:center;gap:5px;padding-top:9px;border-top:1px solid #ebe7ed;color:#5b2b76;font-size:.55rem}.campaign-report-grid .is-teal b{color:#117e8c}.campaign-report-grid .is-blue b{color:#2563eb}.campaign-report-grid .is-green b{color:#638524}@media(max-width:950px){.campaign-report-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:540px){.campaign-report-grid{grid-template-columns:1fr}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const reports = document.querySelector('[data-campaign-reports]');
    const period = document.getElementById('meta-analytics-period');
    if (!reports) return;
    const syncReportPeriods = function () {
        const view = ({'7':'7dias','30':'30dias','365':'anual'})[period?.value] || 'historial';
        reports.querySelectorAll('[data-report-base]').forEach(function (link) {
            const params = new URLSearchParams({view, empresa_id: reports.dataset.companyId});
            link.href = `${link.dataset.reportBase}?${params}`;
        });
    };
    period?.addEventListener('change', syncReportPeriods);
    syncReportPeriods();
});
</script>
@endif
