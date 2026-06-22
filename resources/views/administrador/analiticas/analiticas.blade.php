@extends('layouts.app')

@section('title', 'Analíticas mensuales')

@section('content')
    @php
        $monthName = now()->locale('es')->translatedFormat('F Y');

        $campaignsByUser = [
            ['user' => 'María Fernández', 'campaigns' => 7, 'reach' => 38400, 'interactions' => 4910, 'engagement' => 12.8],
            ['user' => 'Comercial Andina', 'campaigns' => 6, 'reach' => 35100, 'interactions' => 4180, 'engagement' => 11.9],
            ['user' => 'Tecnored Bolivia', 'campaigns' => 5, 'reach' => 29800, 'interactions' => 3325, 'engagement' => 11.2],
            ['user' => 'Sabores del Valle', 'campaigns' => 4, 'reach' => 24100, 'interactions' => 2540, 'engagement' => 10.5],
            ['user' => 'Ñandú Boutique', 'campaigns' => 3, 'reach' => 18750, 'interactions' => 1860, 'engagement' => 9.9],
        ];

        $dailyPerformance = [240, 255, 278, 265, 290, 315, 332, 340, 326, 348, 367, 359, 378, 390, 412, 428, 439, 421, 448, 462, 470, 489, 501, 494, 516, 528, 547, 559, 571, 584];

        $campaignReach = [
            ['campaign' => 'Promo Verano 2026', 'reach' => 18200, 'engagement' => 12.8],
            ['campaign' => 'Lanzamiento App Norte', 'reach' => 16540, 'engagement' => 11.6],
            ['campaign' => 'Rebajas de Invierno', 'reach' => 14980, 'engagement' => 10.9],
            ['campaign' => 'Back to School', 'reach' => 13620, 'engagement' => 10.4],
            ['campaign' => 'Impulso Ñandú', 'reach' => 12110, 'engagement' => 9.8],
            ['campaign' => 'Campaña Express', 'reach' => 10940, 'engagement' => 9.3],
        ];

        $statusDistribution = [
            'Pendiente' => 5,
            'En proceso' => 8,
            'Activa' => 12,
            'Finalizada' => 9,
            'Cancelada' => 2,
        ];

        $heatmapHours = ['08:00', '10:00', '12:00', '14:00', '15:00', '16:00', '17:00', '19:00', '21:00'];
        $heatmapRows = [
            'Lunes' => [48, 57, 66, 78, 88, 93, 89, 72, 60],
            'Martes' => [52, 61, 70, 82, 91, 95, 92, 76, 63],
            'Miércoles' => [50, 59, 68, 84, 93, 97, 94, 79, 65],
            'Jueves' => [55, 64, 73, 86, 96, 99, 97, 82, 68],
            'Viernes' => [58, 67, 76, 88, 98, 100, 98, 85, 71],
            'Sábado' => [44, 53, 61, 71, 79, 83, 80, 74, 62],
            'Domingo' => [39, 48, 56, 65, 72, 75, 73, 68, 57],
        ];

        $totalCampaigns = array_sum(array_column($campaignsByUser, 'campaigns'));
        $totalReach = array_sum(array_column($campaignsByUser, 'reach'));
        $totalInteractions = array_sum(array_column($campaignsByUser, 'interactions'));
        $averageEngagement = round(array_sum(array_column($campaignsByUser, 'engagement')) / count($campaignsByUser), 1);

        $recommendedCampaign = collect($campaignReach)->sortByDesc('engagement')->first();
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .analytics-shell {
            background: #ffffff;
            min-height: 100vh;
        }

        .analytics-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }

        .analytics-kpi {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .analytics-kpi:hover {
            transform: translateY(-4px);
        }

        .analytics-kpi::after {
            content: '';
            position: absolute;
            inset: auto -20% -35% auto;
            width: 120px;
            height: 120px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .chart-box {
            position: relative;
            height: 320px;
        }

        .chart-box--small {
            height: 280px;
        }

        .heatmap-grid {
            display: grid;
            grid-template-columns: 120px repeat(9, minmax(64px, 1fr));
            gap: 0.65rem;
            align-items: center;
        }

        .heatmap-pill {
            border-radius: 1rem;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.83rem;
            font-weight: 700;
            color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .heatmap-scale {
            background: linear-gradient(90deg, #dbeafe 0%, #93c5fd 35%, #38bdf8 65%, #0f766e 100%);
        }

        /* Banner geométrico */
        .rp-banner {
            background:
                linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(315deg, #4f46e5 25%, transparent 25%),
                linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
                linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
            background-size:
                100px 100px,
                100px 100px,
                100px 100px,
                100px 100px,
                100% 100%;
            background-color: #1d4ed8;
            position: relative;
        }

        .rp-banner-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
            background-size:     50% 50%;
            background-position: 0 0, 100% 0, 100% 100%, 0 100%;
            background-repeat:   no-repeat;
        }

        @media (max-width: 1024px) {
            .heatmap-grid {
                grid-template-columns: 110px repeat(9, minmax(58px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .heatmap-grid {
                min-width: 760px;
            }
            .rp-banner .px-8 { 
                padding-left: 1.25rem; 
                padding-right: 1.25rem; 
            }
        }
    </style>

    <div class="analytics-shell">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Banner con fondo geométrico -->
            <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay"></div>
                <div class="relative z-10 px-8 py-8">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Analíticas mensuales de campañas</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Vista consolidada del mes de {{ ucfirst($monthName) }} con comportamiento de campañas, rendimiento diario y distribución operativa</p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <div class="rounded-2xl px-4 py-2" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                                <span style="color: rgba(255,255,255,0.8); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Periodo</span>
                                <div class="text-lg font-bold text-white">Mes actual</div>
                            </div>
                            <div class="rounded-2xl px-4 py-2" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
                                <span style="color: rgba(255,255,255,0.8); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Cobertura</span>
                                <div class="text-lg font-bold text-white">Todos los usuarios</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards mejoradas -->
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
                <article class="analytics-kpi rounded-2xl p-6 shadow-md border border-gray-100" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-sky-100">Total campañas</p>
                            <h2 class="mt-2 text-4xl font-black text-white">{{ $totalCampaigns }}</h2>
                            <p class="mt-3 text-sm text-sky-50">Campañas activas, finalizadas y en preparación durante el mes.</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bullhorn text-white text-xl"></i>
                        </div>
                    </div>
                </article>
                <article class="analytics-kpi rounded-2xl p-6 shadow-md border border-gray-100" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-100">Alcance total</p>
                            <h2 class="mt-2 text-4xl font-black text-white">{{ number_format($totalReach) }}</h2>
                            <p class="mt-3 text-sm text-indigo-50">Visibilidad acumulada entre todas las campañas del periodo.</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-eye text-white text-xl"></i>
                        </div>
                    </div>
                </article>
                <article class="analytics-kpi rounded-2xl p-6 shadow-md border border-gray-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-100">Interacciones</p>
                            <h2 class="mt-2 text-4xl font-black text-white">{{ number_format($totalInteractions) }}</h2>
                            <p class="mt-3 text-sm text-emerald-50">Suma de clics, reacciones, comentarios y respuestas del mes.</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-hand-pointer text-white text-xl"></i>
                        </div>
                    </div>
                </article>
                <article class="analytics-kpi rounded-2xl p-6 shadow-md border border-gray-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-amber-50">Engagement promedio</p>
                            <h2 class="mt-2 text-4xl font-black text-white">{{ number_format($averageEngagement, 1) }}%</h2>
                            <p class="mt-3 text-sm text-orange-50">Promedio general de rendimiento considerando todos los clientes.</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-heart text-white text-xl"></i>
                        </div>
                    </div>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
                <article class="analytics-card rounded-2xl p-6 xl:col-span-7 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Rendimiento diario del mes</h2>
                            <p class="text-sm text-slate-500">Evolución de interacciones generales a lo largo del mes.</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 border border-blue-100">Gráfico de líneas</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="dailyPerformanceChart"></canvas>
                    </div>
                </article>

                <article class="analytics-card rounded-2xl p-6 xl:col-span-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Estado de campañas</h2>
                            <p class="text-sm text-slate-500">Distribución general del trabajo operativo del mes.</p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 border border-cyan-100">Gráfico de dona</span>
                    </div>
                    <div class="chart-box chart-box--small">
                        <canvas id="campaignStatusChart"></canvas>
                    </div>
                </article>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
                <article class="analytics-card rounded-2xl p-6 xl:col-span-5 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Campañas por usuario</h2>
                            <p class="text-sm text-slate-500">Comparación mensual de actividad entre clientes.</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-100">Gráfico de barras</span>
                    </div>
                    <div class="chart-box chart-box--small">
                        <canvas id="campaignsByUserChart"></canvas>
                    </div>
                </article>

                <article class="analytics-card rounded-2xl p-6 xl:col-span-7 shadow-sm border border-gray-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Alcance por campaña</h2>
                            <p class="text-sm text-slate-500">Campañas con mayor visibilidad total dentro del periodo.</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">Gráfico de barras</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="reachByCampaignChart"></canvas>
                    </div>
                </article>
            </section>

            <section class="analytics-card rounded-2xl p-6 mb-8 shadow-sm border border-gray-100">
                <div class="flex flex-col gap-2 mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Rendimiento por usuario / cliente</h2>
                    <p class="text-sm text-slate-500">Detalle comparativo de carga operativa, alcance e interacción mensual.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500 uppercase tracking-[0.14em] text-xs">
                                <th class="px-4 py-3 font-semibold">Usuario</th>
                                <th class="px-4 py-3 font-semibold">Campañas</th>
                                <th class="px-4 py-3 font-semibold">Alcance</th>
                                <th class="px-4 py-3 font-semibold">Interacciones</th>
                                <th class="px-4 py-3 font-semibold">Engagement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($campaignsByUser as $row)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-4 font-semibold text-slate-900">{{ $row['user'] }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $row['campaigns'] }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($row['reach']) }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($row['interactions']) }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-700 border border-emerald-100">
                                            {{ number_format($row['engagement'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="analytics-card rounded-2xl p-6 mb-8 shadow-sm border border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Mapa de calor: horarios con mayor interacción</h2>
                        <p class="text-sm text-slate-500">Cruce entre día de la semana y hora del día con engagement promedio.</p>
                    </div>
                    <div class="w-full lg:w-64">
                        <div class="heatmap-scale rounded-full h-3"></div>
                        <div class="mt-2 flex justify-between text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                            <span>Bajo</span>
                            <span>Medio</span>
                            <span>Alto</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto pb-2">
                    <div class="heatmap-grid">
                        <div></div>
                        @foreach ($heatmapHours as $hour)
                            <div class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $hour }}</div>
                        @endforeach

                        @foreach ($heatmapRows as $day => $values)
                            <div class="pr-3 text-sm font-bold text-slate-700">{{ $day }}</div>
                            @foreach ($values as $value)
                                @php
                                    $intensity = max(0.18, min(1, $value / 100));
                                    $background = 'background-color: rgba(14, 165, 233, ' . $intensity . ');';
                                @endphp
                                <div class="heatmap-pill" style="{{ $background }}">
                                    {{ $value }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <article class="analytics-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-2xl bg-cyan-100 p-3">
                            <svg class="w-6 h-6 text-cyan-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Recomendación automática del mes</h2>
                            <p class="text-sm text-slate-500">Sugerencia generada a partir del mapa de calor mensual.</p>
                        </div>
                    </div>
                    <p id="automaticRecommendation" class="text-base leading-7 text-slate-700"></p>
                </article>

                <article class="analytics-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-2xl bg-amber-100 p-3">
                            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.19 3.674a1 1 0 00.95.69h3.864c.969 0 1.371 1.24.588 1.81l-3.126 2.272a1 1 0 00-.364 1.118l1.194 3.674c.3.922-.755 1.688-1.539 1.118l-3.127-2.272a1 1 0 00-1.175 0l-3.127 2.272c-.783.57-1.838-.196-1.539-1.118l1.194-3.674a1 1 0 00-.364-1.118L2.397 9.101c-.783-.57-.38-1.81.588-1.81h3.864a1 1 0 00.95-.69l1.19-3.674z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Campaña recomendada para análisis</h2>
                            <p class="text-sm text-slate-500">La pieza con mejor engagement del periodo.</p>
                        </div>
                    </div>
                    <p class="text-base leading-7 text-slate-700">
                        <span class="font-bold text-slate-900">"{{ $recommendedCampaign['campaign'] }}"</span>
                        obtuvo el mayor engagement con
                        <span class="font-bold text-emerald-700">{{ number_format($recommendedCampaign['engagement'], 1) }}%</span>.
                    </p>
                </article>
            </section>
        </div>
    </div>

    <script>
        const dashboardData = {
            campaignsByUser: @json($campaignsByUser),
            dailyPerformance: @json($dailyPerformance),
            campaignReach: @json($campaignReach),
            statusDistribution: @json($statusDistribution),
            heatmapHours: @json($heatmapHours),
            heatmapRows: @json($heatmapRows),
        };

        const chartPalette = {
            primary: '#0f766e',
            secondary: '#0284c7',
            accent: '#2563eb',
            soft: '#7dd3fc',
            warm: '#f59e0b',
            dark: '#0f172a',
        };

        function destroyExistingChart(id) {
            const chart = Chart.getChart(id);
            if (chart) {
                chart.destroy();
            }
        }

        function buildAutomaticRecommendation() {
            const rows = dashboardData.heatmapRows;
            const hours = dashboardData.heatmapHours;
            const preferredHours = ['15:00', '16:00', '17:00'];
            let total = 0;
            let samples = 0;

            Object.values(rows).forEach((values) => {
                preferredHours.forEach((hour) => {
                    const hourIndex = hours.indexOf(hour);
                    if (hourIndex !== -1) {
                        total += values[hourIndex];
                        samples += 1;
                    }
                });
            });

            const average = samples ? (total / samples).toFixed(1) : '0.0';
            document.getElementById('automaticRecommendation').textContent =
                `El sistema recomienda publicar con mayor frecuencia entre las 15:00 y 17:00, debido a que ese rango presenta el mayor engagement promedio del mes (${average} puntos).`;
        }

        function initMonthlyCharts() {
            destroyExistingChart('dailyPerformanceChart');
            destroyExistingChart('campaignStatusChart');
            destroyExistingChart('campaignsByUserChart');
            destroyExistingChart('reachByCampaignChart');

            new Chart(document.getElementById('dailyPerformanceChart'), {
                type: 'line',
                data: {
                    labels: dashboardData.dailyPerformance.map((_, index) => `Día ${index + 1}`),
                    datasets: [{
                        label: 'Interacciones',
                        data: dashboardData.dailyPerformance,
                        borderColor: chartPalette.accent,
                        backgroundColor: 'rgba(37, 99, 235, 0.14)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: chartPalette.accent,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            },
                            ticks: {
                                color: '#64748b'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('campaignStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(dashboardData.statusDistribution),
                    datasets: [{
                        data: Object.values(dashboardData.statusDistribution),
                        backgroundColor: ['#cbd5e1', '#38bdf8', '#2563eb', '#10b981', '#f97316'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 18,
                                color: '#334155'
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('campaignsByUserChart'), {
                type: 'bar',
                data: {
                    labels: dashboardData.campaignsByUser.map((item) => item.user),
                    datasets: [{
                        label: 'Campañas',
                        data: dashboardData.campaignsByUser.map((item) => item.campaigns),
                        backgroundColor: ['#bae6fd', '#7dd3fc', '#38bdf8', '#0ea5e9', '#0369a1'],
                        borderRadius: 12,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#334155'
                            }
                        },
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#64748b'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('reachByCampaignChart'), {
                type: 'bar',
                data: {
                    labels: dashboardData.campaignReach.map((item) => item.campaign),
                    datasets: [{
                        label: 'Alcance total',
                        data: dashboardData.campaignReach.map((item) => item.reach),
                        backgroundColor: '#0f766e',
                        borderRadius: 14,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Alcance: ${context.raw.toLocaleString()}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#64748b',
                                callback: (value) => value.toLocaleString()
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#334155'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMonthlyCharts();
            buildAutomaticRecommendation();
        });
    </script>
@endsection