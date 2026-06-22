@extends('layouts.app')

@section('title', 'Analíticas de Campaña')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/20">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 mt-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-700 to-purple-700 bg-clip-text text-transparent">Analíticas de Campaña</h1>
                <p class="mt-2 text-gray-600">Vista administrativa de la campaña activa de {{ $user->name }}</p>
            </div>
            <a href="{{ route('administrador.usuarios.view', $user->id) }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al usuario
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Analíticas de Rendimiento</h2>
                    @if($campaniaActual)
                        <p class="mt-2 text-sm font-medium text-indigo-600">Campaña: {{ $campaniaActual->nombre }}</p>
                    @else
                        <p class="mt-2 text-sm font-medium text-gray-500">El usuario aún no tiene una campaña activa.</p>
                    @endif
                </div>

                @if($campaniaActual)
                    <div class="flex space-x-3 mt-4 sm:mt-0">
                        <select id="timeRange" class="bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-sm">
                            <option value="7">Últimos 7 días</option>
                            <option value="30" selected>Últimos 30 días</option>
                            <option value="365">Este año</option>
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
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center">
                    <p class="text-base font-semibold text-gray-700">Aún no se empezó con la campaña.</p>
                    <p class="mt-2 text-sm text-gray-500">Cuando la campaña del usuario esté activa, aquí verás sus analíticas de rendimiento.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($campaniaActual)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.getElementById('timeRange').addEventListener('change', function() {
        const timeRange = this.value;
        let viewName;

        switch (timeRange) {
            case '7':
                viewName = '7dias';
                break;
            case '30':
                viewName = '30dias';
                break;
            case '365':
                viewName = 'anual';
                break;
            default:
                viewName = '30dias';
        }

        fetch(`{{ route('clientes.analiticas.load-view') }}?view=${viewName}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('metricsContainer').innerHTML = html;
                initCharts();
            });
    });

    function initCharts() {
        if (!window.analiticasData) return;
        const data = window.analiticasData;

        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        if (window.engagementChartInstance) window.engagementChartInstance.destroy();
        window.engagementChartInstance = new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                datasets: [{
                    data: data.engagement.chart_data,
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });

        const reachCtx = document.getElementById('reachChart').getContext('2d');
        if (window.reachChartInstance) window.reachChartInstance.destroy();
        window.reachChartInstance = new Chart(reachCtx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                datasets: [{
                    data: data.reach.chart_data,
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });

        const conversionCtx = document.getElementById('conversionChart').getContext('2d');
        if (window.conversionChartInstance) window.conversionChartInstance.destroy();
        window.conversionChartInstance = new Chart(conversionCtx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                datasets: [{
                    data: data.conversion.chart_data,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });

        const growthCtx = document.getElementById('followersGrowthChart').getContext('2d');
        if (window.followersGrowthChartInstance) window.followersGrowthChartInstance.destroy();
        window.followersGrowthChartInstance = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: data.followers.growth_labels,
                datasets: [
                    {
                        label: 'Facebook',
                        data: data.followers.growth_facebook,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Instagram',
                        data: data.followers.growth_instagram,
                        borderColor: '#EC4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const distributionCtx = document.getElementById('engagementDistributionChart').getContext('2d');
        if (window.engagementDistributionChartInstance) window.engagementDistributionChartInstance.destroy();

        const platformData = {
            labels: ['Facebook', 'Instagram'],
            datasets: [{
                data: [data.distribution.platform.facebook, data.distribution.platform.instagram],
                backgroundColor: ['#3B82F6', '#EC4899'],
                borderWidth: 0,
                cutout: '70%'
            }]
        };

        window.engagementDistributionChartInstance = new Chart(distributionCtx, {
            type: 'doughnut',
            data: platformData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });

        const selectDist = document.getElementById('engagementDistribution');
        if (selectDist) {
            const newSelectDist = selectDist.cloneNode(true);
            selectDist.parentNode.replaceChild(newSelectDist, selectDist);

            newSelectDist.addEventListener('change', function() {
                if (window.engagementDistributionChartInstance) {
                    window.engagementDistributionChartInstance.destroy();
                }

                let newData;
                let colors;

                if (this.value === 'hour') {
                    newData = {
                        labels: data.distribution.by_hour.labels,
                        datasets: [{
                            data: data.distribution.by_hour.values,
                            backgroundColor: ['#818CF8', '#A78BFA', '#C4B5FD', '#DDD6FE', '#EDE9FE'],
                            borderWidth: 0,
                            cutout: '70%'
                        }]
                    };
                } else {
                    newData = {
                        labels: ['Facebook', 'Instagram'],
                        datasets: [{
                            data: [data.distribution.platform.facebook, data.distribution.platform.instagram],
                            backgroundColor: ['#3B82F6', '#EC4899'],
                            borderWidth: 0,
                            cutout: '70%'
                        }]
                    };
                }

                window.engagementDistributionChartInstance = new Chart(distributionCtx, {
                    type: 'doughnut',
                    data: newData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.label}: ${context.raw}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }
    }

    function exportData(event) {
        const timeRange = document.getElementById('timeRange').value;
        let periodo;

        switch (timeRange) {
            case '7':
                periodo = '7dias';
                break;
            case '30':
                periodo = '30dias';
                break;
            case '365':
                periodo = 'anual';
                break;
            default:
                periodo = '30dias';
        }

        window.location.href = `{{ route('clientes.analiticas.exportar-pdf') }}?periodo=${periodo}`;
    }

    document.addEventListener('DOMContentLoaded', initCharts);
</script>
@endif
@endsection


