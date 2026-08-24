@extends('layouts.app')

@section('title', 'Analíticas de campaña')

@section('content')
<style>
    .campaign-user-analytics{min-height:100vh;padding:0 0 48px;background:#fff;color:#302832;font-family:Inter,'Segoe UI',sans-serif}.cua-hero{position:relative;overflow:hidden;min-height:180px;display:flex;align-items:center;padding:30px 48px;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}.cua-hero:after{content:'';pointer-events:none;position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-repeat:no-repeat}.cua-hero-layout{position:relative;z-index:1;width:100%;display:flex;align-items:center;justify-content:space-between;gap:28px}.cua-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.cua-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.cua-hero p{max-width:690px;margin:8px 0 0;color:#e0e7ff;font-size:.84rem;line-height:1.55}.cua-hero-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.cua-hero-action{min-height:41px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 14px;border:1px solid rgba(255,255,255,.16);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:900;text-decoration:none;white-space:nowrap;transition:.18s}.cua-hero-action.is-primary{border-color:#fff;background:#fff;color:#4f46e5}.cua-hero-action:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .cua-content{margin:26px 24px 0}.cua-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:16px}.cua-section-head small{display:block;margin-bottom:3px;color:#6366f1;font-size:.62rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.cua-section-head h2{margin:0;color:#1f2937;font-size:1.08rem;font-weight:900;letter-spacing:-.02em}.cua-section-head h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.cua-section-head p{margin:5px 0 0;color:#6b7280;font-size:.7rem}.cua-toolbar{display:flex;align-items:center;gap:8px}.cua-period{height:39px;padding:0 34px 0 12px;border:1px solid #e1e5df;border-radius:.65rem;background:#f9fafb;color:#4b5563;font-family:inherit;font-size:.65rem;font-weight:800;outline:0;cursor:pointer}.cua-period:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.cua-export{height:39px;display:inline-flex;align-items:center;gap:7px;padding:0 13px;border:1px solid #117e8c;border-radius:.65rem;background:#117e8c;color:#fff;font-family:inherit;font-size:.65rem;font-weight:900;cursor:pointer;transition:.18s}.cua-export:hover{transform:translateY(-1px);background:#0e6c78}.cua-empty{padding:46px 24px;border:1px dashed #d9dee0;border-radius:1rem;background:#f9fafb;text-align:center}.cua-empty i{width:48px;height:48px;display:grid;place-items:center;margin:0 auto 13px;border-radius:.8rem;background:#e4f3f4;color:#117e8c;font-size:1.1rem}.cua-empty strong{display:block;color:#374151;font-size:.82rem}.cua-empty p{margin:6px 0 0;color:#6b7280;font-size:.68rem}
    .campaign-user-analytics #metricsContainer>div{margin-bottom:18px!important}.campaign-user-analytics #metricsContainer>.grid:first-child{gap:16px!important}.campaign-user-analytics #metricsContainer>.grid:first-child>*{--metric:#117e8c;--metric-soft:#e6f4f5;--metric-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;min-height:170px;padding:18px!important;border:1px solid rgba(var(--metric-rgb),.22)!important;border-radius:1rem!important;background:linear-gradient(135deg,#fff 35%,var(--metric-soft))!important;box-shadow:inset 0 4px 0 var(--metric),0 10px 24px rgba(45,66,34,.09)!important;transition:.22s}.campaign-user-analytics #metricsContainer>.grid:first-child>*:nth-child(2){--metric:#7da533;--metric-soft:#f0f6e7;--metric-rgb:125,165,51}.campaign-user-analytics #metricsContainer>.grid:first-child>*:nth-child(3){--metric:#e3a122;--metric-soft:#fff6df;--metric-rgb:227,161,34}.campaign-user-analytics #metricsContainer>.grid:first-child>*:nth-child(4){--metric:#e37225;--metric-soft:#fff0e6;--metric-rgb:227,114,37}.campaign-user-analytics #metricsContainer>.grid:first-child>*:before{content:'';position:absolute;z-index:-1;top:-46px;right:-38px;width:125px;height:125px;border:22px solid rgba(var(--metric-rgb),.09);border-radius:50%}.campaign-user-analytics #metricsContainer>.grid:first-child>*:hover{transform:translateY(-5px);box-shadow:inset 0 4px 0 var(--metric),0 17px 32px rgba(var(--metric-rgb),.16)!important}.campaign-user-analytics #metricsContainer>.grid:first-child>*>div:not(.absolute) p:first-child{color:#50594a!important;font-size:.65rem!important;font-weight:900!important;text-transform:uppercase}.campaign-user-analytics #metricsContainer>.grid:first-child .text-3xl{color:#263024!important;font-size:1.7rem!important;font-weight:900!important}.campaign-user-analytics #metricsContainer>.grid:first-child .bg-white{background:var(--metric)!important;color:#fff!important}.campaign-user-analytics #metricsContainer>.grid:first-child svg{color:inherit!important}.campaign-user-analytics #metricsContainer>.grid:first-child .absolute.inset-0{background:rgba(48,40,52,.86)!important}.campaign-user-analytics #metricsContainer>.grid:not(:first-child){gap:18px!important}.campaign-user-analytics #metricsContainer>.grid:not(:first-child)>div{border:1px solid #eee8f0!important;border-radius:1rem!important;background:linear-gradient(135deg,#fff 0%,#fbf8fc 58%,#f2fbfa 100%)!important;box-shadow:0 9px 22px rgba(61,23,79,.07)!important}.campaign-user-analytics #metricsContainer>.grid:not(:first-child)>div>div:first-child h3,.campaign-user-analytics #metricsContainer>.grid:not(:first-child)>div>h3{color:#302832!important;font-weight:900!important}.campaign-user-analytics #metricsContainer>.grid:not(:first-child)>div>div:first-child h3:after,.campaign-user-analytics #metricsContainer>.grid:not(:first-child)>div>h3:after{content:'';display:block;width:42px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.campaign-user-analytics #metricsContainer .bg-gray-50{background:#f9fafb!important}.campaign-user-analytics #metricsContainer .bg-indigo-600,.campaign-user-analytics #metricsContainer .bg-emerald-700{background:#117e8c!important}.campaign-user-analytics #metricsContainer select{border-color:#d9e3e4!important;background-color:#fff!important;color:#4b5563!important}.campaign-user-analytics #metricsContainer .rounded-xl{border-radius:.8rem}
    @media(max-width:980px){.cua-hero{min-height:205px}.cua-hero-layout{justify-content:center;flex-direction:column;text-align:center}.cua-hero-actions{justify-content:center}.cua-section-head{align-items:flex-start;flex-direction:column}.cua-toolbar{width:100%}.cua-period,.cua-export{flex:1}}
    @media(max-width:640px){.campaign-user-analytics{padding-bottom:32px}.cua-hero{padding:24px 20px}.cua-hero-actions{width:100%}.cua-hero-action{flex:1}.cua-content{margin:22px 12px 0}.cua-toolbar{display:grid;grid-template-columns:1fr}.cua-period,.cua-export{width:100%}.campaign-user-analytics #metricsContainer>.grid:first-child>*{min-height:155px}}
</style>

<div class="campaign-user-analytics">
    <header class="cua-hero">
        <div class="cua-hero-layout">
            <div>
                <span class="cua-eyebrow">Analíticas por cliente</span>
                <h1>Rendimiento de campaña</h1>
                <p>Consulta la evolución de la campaña activa de {{ $user->name }} y detecta oportunidades de optimización.</p>
            </div>
            <nav class="cua-hero-actions" aria-label="Acciones de analíticas">
                @if($campaniaActual)<a href="{{ route('administrador.campañas.show', $campaniaActual->id) }}" class="cua-hero-action is-primary"><i class="fas fa-bullhorn"></i>Ver campaña</a>@endif
                <a href="{{ route('administrador.usuarios.view', $user->id) }}" class="cua-hero-action"><i class="fas fa-arrow-left"></i>Volver al usuario</a>
            </nav>
        </div>
    </header>

    <main class="cua-content">
        <div class="cua-section-head">
            <div>
                <small>Resumen individual</small>
                <h2>Analíticas de rendimiento</h2>
                @if($campaniaActual)
                    <p>Campaña: <strong>{{ $campaniaActual->nombre }}</strong> · Cliente: {{ $user->name }}</p>
                @else
                    <p>El usuario aún no tiene una campaña activa.</p>
                @endif
            </div>
            @if($campaniaActual)
                <div class="cua-toolbar">
                    <select id="timeRange" class="cua-period" aria-label="Periodo de las analíticas">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="365">Este año</option>
                    </select>
                    <button type="button" onclick="exportData(event)" class="cua-export"><i class="fas fa-download"></i>Exportar informe</button>
                </div>
            @endif
        </div>

        @if($campaniaActual)
            <div id="metricsContainer">
                @include('clientes.analiticas.partials.analiticas')
            </div>
        @else
            <div class="cua-empty">
                <i class="fas fa-chart-line"></i>
                <strong>La campaña todavía no comenzó</strong>
                <p>Cuando la campaña del usuario esté activa, aquí encontrarás sus indicadores de rendimiento.</p>
            </div>
        @endif
    </main>
</div>

@if($campaniaActual)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.analyticsUserId = {{ $user->id }};

    document.getElementById('timeRange').addEventListener('change', function() {
        const timeRange = this.value;
        let viewName;
        switch (timeRange) {
            case '7': viewName = '7dias'; break;
            case '30': viewName = '30dias'; break;
            case '365': viewName = 'anual'; break;
            default: viewName = '30dias';
        }
        fetch(`{{ route('clientes.analiticas.load-view') }}?view=${viewName}&user_id=${window.analyticsUserId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('metricsContainer').innerHTML = html;
                initCharts();
            });
    });

    function hydrateAnalyticsData() {
        const jsonNode = document.getElementById('analytics-json');
        if (jsonNode) {
            try {
                window.analiticasData = JSON.parse(jsonNode.textContent);
            } catch (error) {
                console.error('No se pudo leer analytics-json', error);
            }
        }
        return window.analiticasData;
    }

    function initCharts() {
        const data = hydrateAnalyticsData();
        if (!data) return;
        Chart.defaults.color = '#746b78';
        Chart.defaults.font.family = "Inter, 'Segoe UI', sans-serif";

        const engagementCtx = document.getElementById('engagementChart')?.getContext('2d');
        if (engagementCtx) {
            if (window.engagementChartInstance) window.engagementChartInstance.destroy();
            window.engagementChartInstance = new Chart(engagementCtx, { type: 'line', data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.engagement.chart_data, borderColor: '#117e8c', backgroundColor: 'rgba(17, 126, 140, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } } });
        }

        const reachCtx = document.getElementById('reachChart')?.getContext('2d');
        if (reachCtx) {
            if (window.reachChartInstance) window.reachChartInstance.destroy();
            window.reachChartInstance = new Chart(reachCtx, { type: 'line', data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.reach.chart_data, borderColor: '#7da533', backgroundColor: 'rgba(125, 165, 51, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } } });
        }

        const conversionCtx = document.getElementById('conversionChart')?.getContext('2d');
        if (conversionCtx) {
            if (window.conversionChartInstance) window.conversionChartInstance.destroy();
            window.conversionChartInstance = new Chart(conversionCtx, { type: 'line', data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.conversion.chart_data, borderColor: '#e37225', backgroundColor: 'rgba(227, 114, 37, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } } });
        }

        const growthCtx = document.getElementById('followersGrowthChart')?.getContext('2d');
        if (growthCtx) {
            if (window.followersGrowthChartInstance) window.followersGrowthChartInstance.destroy();
            window.followersGrowthChartInstance = new Chart(growthCtx, {
                type: 'line',
                data: { labels: data.followers.growth_labels, datasets: [{ label: 'Facebook', data: data.followers.growth_facebook, borderColor: '#117e8c', backgroundColor: 'rgba(17, 126, 140, 0.05)', borderWidth: 2, tension: 0.4, fill: true }, { label: 'Instagram', data: data.followers.growth_instagram, borderColor: '#5b2b76', backgroundColor: 'rgba(91, 43, 118, 0.05)', borderWidth: 2, tension: 0.4, fill: true }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } }, tooltip: { mode: 'index', intersect: false } }, scales: { y: { beginAtZero: false, grid: { drawBorder: false } }, x: { grid: { display: false } } } }
            });
        }

        const distributionCtx = document.getElementById('engagementDistributionChart')?.getContext('2d');
        if (distributionCtx) {
            if (window.engagementDistributionChartInstance) window.engagementDistributionChartInstance.destroy();
            const hourLabels = data.distribution.by_hour?.labels || data.distribution.time?.labels || [];
            const hourValues = data.distribution.by_hour?.values || data.distribution.time?.data || [];
            window.engagementDistributionChartInstance = new Chart(distributionCtx, { type: 'doughnut', data: { labels: ['Facebook', 'Instagram'], datasets: [{ data: [data.distribution.platform.facebook, data.distribution.platform.instagram], backgroundColor: ['#117e8c', '#5b2b76'], borderWidth: 0, cutout: '70%' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }, tooltip: { callbacks: { label: function(context) { return `${context.label}: ${context.raw}%`; } } } } } });
            const selectDist = document.getElementById('engagementDistribution');
            if (selectDist) {
                const newSelectDist = selectDist.cloneNode(true);
                selectDist.parentNode.replaceChild(newSelectDist, selectDist);
                newSelectDist.addEventListener('change', function() {
                    if (this.value === 'hour') {
                        window.engagementDistributionChartInstance.data.labels = hourLabels;
                        window.engagementDistributionChartInstance.data.datasets[0].data = hourValues;
                        window.engagementDistributionChartInstance.data.datasets[0].backgroundColor = ['#117e8c', '#5b2b76', '#7da533', '#e3a122', '#e37225', '#8cc9ce'];
                    } else {
                        window.engagementDistributionChartInstance.data.labels = ['Facebook', 'Instagram'];
                        window.engagementDistributionChartInstance.data.datasets[0].data = [data.distribution.platform.facebook, data.distribution.platform.instagram];
                        window.engagementDistributionChartInstance.data.datasets[0].backgroundColor = ['#117e8c', '#5b2b76'];
                    }
                    window.engagementDistributionChartInstance.update();
                });
            }
        }

        const ageGenderCtx = document.getElementById('ageGenderChart')?.getContext('2d');
        if (ageGenderCtx) {
            if (window.ageGenderChartInstance) window.ageGenderChartInstance.destroy();
            window.ageGenderChartInstance = new Chart(ageGenderCtx, {
                type: 'bar',
                data: { labels: data.audience.age_gender.labels, datasets: [{ label: `Mujeres ${data.audience.age_gender.women_total_display}`, data: data.audience.age_gender.women, backgroundColor: '#8cc9ce', borderRadius: 6, categoryPercentage: 0.72, barPercentage: 0.9 }, { label: `Hombres ${data.audience.age_gender.men_total_display}`, data: data.audience.age_gender.men, backgroundColor: '#117e8c', borderRadius: 6, categoryPercentage: 0.72, barPercentage: 0.9 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: '#334155' } } }, scales: { y: { beginAtZero: true, max: 40, ticks: { callback: (value) => `${value}%`, color: '#64748b' }, grid: { color: 'rgba(148, 163, 184, 0.18)' } }, x: { ticks: { color: '#475569' }, grid: { display: false } } } }
            });
        }
    }

    function exportData() {
        const timeRange = document.getElementById('timeRange').value;
        let periodo;
        switch (timeRange) {
            case '7': periodo = '7dias'; break;
            case '30': periodo = '30dias'; break;
            case '365': periodo = 'anual'; break;
            default: periodo = '30dias';
        }
        window.location.href = `{{ route('clientes.analiticas.exportar-pdf') }}?periodo=${periodo}&user_id=${window.analyticsUserId}`;
    }

    document.addEventListener('DOMContentLoaded', initCharts);
</script>
@endif
@endsection
