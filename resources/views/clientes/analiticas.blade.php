@extends('layouts.app2')

@section('title', 'Analiticas')

@section('content')
<div id="client-analytics" class="min-h-screen">
    <header class="analytics-hero">
        <div class="analytics-hero-content">
            <span class="analytics-kicker">Resultados de tu estrategia</span>
            <h1>Analíticas de <span>rendimiento</span></h1>
            <p>Consulta el desempeño de tu campaña y descubre cómo está creciendo tu presencia digital.</p>
        </div>
        <div class="analytics-hero-side">
            <div class="analytics-status"><small>Estado</small><strong>{{ $campaniaActual ? 'Campaña activa' : 'En preparación' }}</strong></div>
            <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>
    </header>
    <main class="analytics-content">
    <section class="analytics-panel {{ $campaniaActual ? '' : 'is-waiting' }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Analiticas de Rendimiento</h2>
            @if($campaniaActual)
                <p class="mt-2 text-sm font-medium text-indigo-600">Campaña: {{ $campaniaActual->nombre }}</p>
            @endif
        </div>

        @if($campaniaActual)
            <div class="flex space-x-3 mt-4 sm:mt-0">
                <select id="timeRange" class="bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-sm">
                    <option value="7">Ultimos 7 dias</option>
                    <option value="30" selected>Ultimos 30 dias</option>
                    <option value="365">Este ano</option>
                </select>
                <button onclick="exportData(event)" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exportar
                </button>
            </div>
        @endif
    </div>

    @if($campaniaActual)
        <div id="metricsContainer">
            @include('clientes.analiticas.partials.analiticas')
        </div>
    @else
        <section class="analytics-wait" aria-labelledby="analytics-wait-title">
            <div class="analytics-wait-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></div>
            <h2 id="analytics-wait-title">Aún no comenzó tu campaña</h2>
            <p>Cuando tu campaña esté activa, aquí verás sus analíticas de rendimiento.</p>
        </section>
    @endif
    </section>
    </main>
</div>

<style>
    #client-analytics { --purple:#5B2B76; --orange:#EF6C22; --green:#7DA533; --turquoise:#117E8C; padding-bottom:42px; background:#fff; color:#17131d; }
    #client-analytics .analytics-hero { min-height:150px; display:flex; align-items:center; justify-content:space-between; gap:32px; padding:28px 32px; background:#242426; color:#fff; }
    #client-analytics .analytics-hero-content { max-width:730px; }
    #client-analytics .analytics-kicker { display:block; margin-bottom:10px; color:#4fc3ca; font-size:.68rem; font-weight:900; letter-spacing:.13em; text-transform:uppercase; }
    #client-analytics .analytics-hero h1 { margin:0; font-size:clamp(1.65rem,3vw,2.35rem); font-weight:800; line-height:1.08; letter-spacing:-.035em; }
    #client-analytics .analytics-hero h1 span { color:#4fc3ca; }
    #client-analytics .analytics-hero p { max-width:650px; margin-top:11px; color:#aaa5ad; font-size:.86rem; line-height:1.55; }
    #client-analytics .analytics-hero-side { display:flex; align-items:center; gap:26px; }
    #client-analytics .analytics-status { min-width:138px; padding:13px 16px; border-left:4px solid var(--turquoise); background:#303033; }
    #client-analytics .analytics-status small, #client-analytics .analytics-status strong { display:block; }
    #client-analytics .analytics-status small { color:#aaa5ad; font-size:.63rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    #client-analytics .analytics-status strong { margin-top:4px; color:#fff; font-size:.82rem; }
    #client-analytics .login-mosaic { width:144px; height:96px; display:grid; flex:0 0 auto; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(2,1fr); }
    #client-analytics .login-mosaic span:nth-child(1) { background:var(--orange); border-radius:100% 0 0 0; }
    #client-analytics .login-mosaic span:nth-child(2) { background:#F5A900; border-radius:0 0 0 100%; }
    #client-analytics .login-mosaic span:nth-child(3) { background:var(--purple); border-radius:100% 0 100% 0; }
    #client-analytics .login-mosaic span:nth-child(4) { background:var(--turquoise); border-radius:0 100% 0 100%; }
    #client-analytics .login-mosaic span:nth-child(5) { background:var(--green); border-radius:50%; }
    #client-analytics .login-mosaic span:nth-child(6) { border:12px solid #607078; border-top-color:transparent; border-left-color:transparent; border-radius:50%; transform:rotate(45deg); }
    #client-analytics .analytics-content { margin:32px; }
    #client-analytics .analytics-panel { padding:24px; border-top:1px solid #d9d2dc; border-bottom:1px solid #d9d2dc; background:#fff; }
    #client-analytics .analytics-panel.is-waiting { padding:0; border:0; background:transparent; }
    #client-analytics .analytics-panel.is-waiting > div:first-child { display:none; }
    #client-analytics .analytics-wait { padding:58px 24px 48px; text-align:center; }
    #client-analytics .analytics-wait-icon { width:68px; height:68px; display:grid; place-items:center; margin:0 auto 18px; border-radius:50%; background:#e5f2f3; color:var(--turquoise); font-size:1.7rem; }
    #client-analytics .analytics-wait h2 { margin:0; color:#302834; font-size:1.25rem; font-weight:900; letter-spacing:-.025em; }
    #client-analytics .analytics-wait p { max-width:580px; margin:9px auto 0; color:#756a7a; font-size:.9rem; line-height:1.65; }
    html[data-client-theme="dark"] #client-analytics { background:#141216; color:#e9e5eb; }
    html[data-client-theme="dark"] #client-analytics .analytics-panel { border-color:#3b3540; background:#1e1b21; }
    html[data-client-theme="dark"] #client-analytics .analytics-panel.is-waiting { background:transparent; }
    html[data-client-theme="dark"] #client-analytics .analytics-wait-icon { background:#19383c; color:#78c3cb; }
    html[data-client-theme="dark"] #client-analytics .analytics-wait h2 { color:#f1edf3; }
    html[data-client-theme="dark"] #client-analytics .analytics-wait p { color:#b4abb8; }
    @media (max-width:720px) {
        #client-analytics .analytics-hero { padding:26px 20px; }
        #client-analytics .login-mosaic { display:none; }
        #client-analytics .analytics-content { margin:20px 16px; }
        #client-analytics .analytics-panel { padding:18px; }
    }
    @media (max-width:500px) {
        #client-analytics .analytics-hero { align-items:flex-start; flex-direction:column; }
        #client-analytics .analytics-hero-side, #client-analytics .analytics-status { width:100%; }
    }
</style>

@if($campaniaActual)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.analyticsUserId = {{ auth()->id() }};

    document.getElementById('timeRange').addEventListener('change', function() {
        const timeRange = this.value;
        let viewName;

        switch(timeRange) {
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

        const engagementCtx = document.getElementById('engagementChart')?.getContext('2d');
        if (engagementCtx) {
            if (window.engagementChartInstance) window.engagementChartInstance.destroy();
            window.engagementChartInstance = new Chart(engagementCtx, {
                type: 'line',
                data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.engagement.chart_data, borderColor: '#6366F1', backgroundColor: 'rgba(99, 102, 241, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
            });
        }

        const reachCtx = document.getElementById('reachChart')?.getContext('2d');
        if (reachCtx) {
            if (window.reachChartInstance) window.reachChartInstance.destroy();
            window.reachChartInstance = new Chart(reachCtx, {
                type: 'line',
                data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.reach.chart_data, borderColor: '#8B5CF6', backgroundColor: 'rgba(139, 92, 246, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
            });
        }

        const conversionCtx = document.getElementById('conversionChart')?.getContext('2d');
        if (conversionCtx) {
            if (window.conversionChartInstance) window.conversionChartInstance.destroy();
            window.conversionChartInstance = new Chart(conversionCtx, {
                type: 'line',
                data: { labels: ['1','2','3','4','5','6','7','8','9','10'], datasets: [{ data: data.conversion.chart_data, borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
            });
        }

        const growthCtx = document.getElementById('followersGrowthChart')?.getContext('2d');
        if (growthCtx) {
            if (window.followersGrowthChartInstance) window.followersGrowthChartInstance.destroy();
            window.followersGrowthChartInstance = new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: data.followers.growth_labels,
                    datasets: [
                        { label: 'Facebook', data: data.followers.growth_facebook, borderColor: '#3B82F6', backgroundColor: 'rgba(59, 130, 246, 0.05)', borderWidth: 2, tension: 0.4, fill: true },
                        { label: 'Instagram', data: data.followers.growth_instagram, borderColor: '#EC4899', backgroundColor: 'rgba(236, 72, 153, 0.05)', borderWidth: 2, tension: 0.4, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } }, tooltip: { mode: 'index', intersect: false } },
                    scales: { y: { beginAtZero: false, grid: { drawBorder: false } }, x: { grid: { display: false } } }
                }
            });
        }

        const distributionCtx = document.getElementById('engagementDistributionChart')?.getContext('2d');
        if (distributionCtx) {
            if (window.engagementDistributionChartInstance) window.engagementDistributionChartInstance.destroy();
            const hourLabels = data.distribution.by_hour?.labels || data.distribution.time?.labels || [];
            const hourValues = data.distribution.by_hour?.values || data.distribution.time?.data || [];
            window.engagementDistributionChartInstance = new Chart(distributionCtx, {
                type: 'doughnut',
                data: { labels: ['Facebook', 'Instagram'], datasets: [{ data: [data.distribution.platform.facebook, data.distribution.platform.instagram], backgroundColor: ['#3B82F6', '#EC4899'], borderWidth: 0, cutout: '70%' }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }, tooltip: { callbacks: { label: function(context) { return `${context.label}: ${context.raw}%`; } } } }
                }
            });

            const selectDist = document.getElementById('engagementDistribution');
            if (selectDist) {
                const newSelectDist = selectDist.cloneNode(true);
                selectDist.parentNode.replaceChild(newSelectDist, selectDist);
                newSelectDist.addEventListener('change', function() {
                    if (this.value === 'hour') {
                        window.engagementDistributionChartInstance.data.labels = hourLabels;
                        window.engagementDistributionChartInstance.data.datasets[0].data = hourValues;
                        window.engagementDistributionChartInstance.data.datasets[0].backgroundColor = ['#6366F1', '#8B5CF6', '#EC4899', '#F97316', '#10B981', '#3B82F6'];
                    } else {
                        window.engagementDistributionChartInstance.data.labels = ['Facebook', 'Instagram'];
                        window.engagementDistributionChartInstance.data.datasets[0].data = [data.distribution.platform.facebook, data.distribution.platform.instagram];
                        window.engagementDistributionChartInstance.data.datasets[0].backgroundColor = ['#3B82F6', '#EC4899'];
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
                data: {
                    labels: data.audience.age_gender.labels,
                    datasets: [
                        { label: `Mujeres ${data.audience.age_gender.women_total_display}`, data: data.audience.age_gender.women, backgroundColor: '#9edbff', borderRadius: 6, categoryPercentage: 0.72, barPercentage: 0.9 },
                        { label: `Hombres ${data.audience.age_gender.men_total_display}`, data: data.audience.age_gender.men, backgroundColor: '#2563eb', borderRadius: 6, categoryPercentage: 0.72, barPercentage: 0.9 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: '#334155' } } },
                    scales: {
                        y: { beginAtZero: true, max: 40, ticks: { callback: (value) => `${value}%`, color: '#64748b' }, grid: { color: 'rgba(148, 163, 184, 0.18)' } },
                        x: { ticks: { color: '#475569' }, grid: { display: false } }
                    }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initCharts);

    function exportData(event) {
        const btnExportar = event ? (event.currentTarget || event.target) : document.querySelector('button[onclick="exportData(event)"]');
        const originalHtml = btnExportar.innerHTML;
        btnExportar.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generando informe...';
        btnExportar.disabled = true;
        const timeRangeSelect = document.getElementById('timeRange');
        let periodo = '30dias';
        if (timeRangeSelect) {
            switch (timeRangeSelect.value) {
                case '7': periodo = '7dias'; break;
                case '30': periodo = '30dias'; break;
                case '365': periodo = 'anual'; break;
            }
        }
        fetch(`{{ route('clientes.analiticas.exportar-pdf') }}?periodo=${periodo}&user_id=${window.analyticsUserId}`, { method: 'GET', headers: { 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => { if (!response.ok) throw new Error('Error al generar el informe'); return response.blob(); })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `informe_analiticas_${periodo}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                btnExportar.innerHTML = originalHtml;
                btnExportar.disabled = false;
            })
            .catch(error => {
                console.error('Error al exportar:', error);
                btnExportar.innerHTML = originalHtml;
                btnExportar.disabled = false;
                alert('Ocurrio un error al generar el informe. Por favor, intentalo de nuevo.');
            });
    }
</script>
@endif
@endsection
