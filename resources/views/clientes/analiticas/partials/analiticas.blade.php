
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <a href="#" onclick="exportEngagementReport(event)" class="relative group block bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
            <div class="bg-white px-4 py-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-semibold text-indigo-700">Descargar reporte</span>
            </div>
        </div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Tasa de Engagement</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['engagement']['rate'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['engagement']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">{{ $data['engagement']['vs_previous'] }}</span>
                    <span class="text-xs text-gray-500 ml-2">vs {{ $data['period_label'] }}</span>
                </div>
            </div>
            <div class="bg-white p-2 rounded-lg shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <canvas id="engagementChart" height="80"></canvas>
        </div>
    </a>

    <div onclick="exportReachReport(event)" class="relative group bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
            <div class="bg-white px-4 py-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-semibold text-purple-700">Descargar reporte</span>
            </div>
        </div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Alcance Total</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['reach']['total'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['reach']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">{{ $data['reach']['vs_previous'] }}</span>
                    <span class="text-xs text-gray-500 ml-2">vs {{ $data['period_label'] }}</span>
                </div>
            </div>
            <div class="bg-white p-2 rounded-lg shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <canvas id="reachChart" height="80"></canvas>
        </div>
    </div>

    <div onclick="exportFollowersReport(event)" class="relative group bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
            <div class="bg-white px-4 py-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-semibold text-blue-700">Descargar reporte</span>
            </div>
        </div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">{{ $data['followers']['label'] ?? 'Seguidores' }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['followers']['total'] ?? $data['followers']['new'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['followers']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">{{ $data['followers']['vs_previous'] }}</span>
                    <span class="text-xs text-gray-500 ml-2">vs {{ $data['period_label'] }}</span>
                </div>
            </div>
            <div class="bg-white p-2 rounded-lg shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>Facebook: {{ $data['followers']['facebook_count'] }}</span>
                <span>Instagram: {{ $data['followers']['instagram_count'] }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-2 rounded-full" style="width: {{ $data['followers']['facebook_percent'] }}%"></div>
            </div>
        </div>
    </div>

    <div onclick="exportCTRReport(event)" class="relative group bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
            <div class="bg-white px-4 py-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-semibold text-green-700">Descargar reporte</span>
            </div>
        </div>
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">CTR (Click Through Rate)</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['conversion']['rate'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['conversion']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">{{ $data['conversion']['vs_previous'] }}</span>
                    <span class="text-xs text-gray-500 ml-2">vs {{ $data['period_label'] }}</span>
                </div>
            </div>
            <div class="bg-white p-2 rounded-lg shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <canvas id="conversionChart" height="80"></canvas>
        </div>
    </div>
</div>

{{--
    BLOQUE ANTERIOR CONSERVADO PARA COMPARACIÓN / POSIBLE REACTIVACIÓN.
    Se oculta visualmente por solicitud; permanecen activos arriba los cuatro
    reportes principales y abajo sus datos y funciones de exportación.

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Crecimiento de Seguidores</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-full hover:bg-indigo-200 transition-colors">Facebook</button>
                <button class="px-3 py-1 text-xs bg-pink-100 text-pink-800 rounded-full hover:bg-pink-200 transition-colors">Instagram</button>
            </div>
        </div>
        <div class="h-80"><canvas id="followersGrowthChart"></canvas></div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Distribucion de Engagement</h3>
            <select id="engagementDistribution" class="bg-gray-50 border border-gray-300 text-gray-700 py-1 px-3 pr-8 rounded-full focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-xs">
                <option value="platform">Por Plataforma</option>
                <option value="hour">Por Hora del Dia</option>
            </select>
        </div>
        <div class="h-80"><canvas id="engagementDistributionChart"></canvas></div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Horario Optimo</h3>
        <div class="flex items-center mb-4">
            <div class="bg-yellow-100 p-2 rounded-lg mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-medium text-gray-800">Mejor hora para publicar</p>
                <p class="text-xs text-gray-500">Basado en datos de engagement</p>
            </div>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg mb-3">
            <div class="flex justify-between items-center mb-2 gap-3">
                <span class="text-sm font-medium text-gray-700">{{ $data['optimal_time']['range'] }}</span>
                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">{{ $data['optimal_time']['engagement_boost'] }} engagement</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $data['optimal_time']['percent'] }}%"></div>
            </div>
        </div>
        <p class="text-xs text-gray-500 leading-5">Horarios sugeridos: {{ $data['optimal_time']['source'] ?? $data['optimal_time']['range'] }}</p>
    </div>

    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 xl:col-span-2">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Audiencia Principal</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $data['audience']['summary']['primary'] ?? 'Sin informacion disponible.' }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-800">Edad y sexo</h4>
            </div>
            <div class="h-80"><canvas id="ageGenderChart"></canvas></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-100 p-4">
                <h4 class="text-base font-semibold text-gray-800 mb-4">Principales ciudades</h4>
                <div class="space-y-3">
                    @foreach(($data['audience']['cities'] ?? []) as $city)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm mb-1">
                                <span class="text-gray-700">{{ $city['name'] }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($city['percentage'], 1, ',', '.') }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-emerald-700" style="width: {{ min($city['percentage'], 100) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-100 p-4">
                <h4 class="text-base font-semibold text-gray-800 mb-4">Principales paises</h4>
                <div class="space-y-3">
                    @foreach(($data['audience']['countries'] ?? []) as $country)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm mb-1">
                                <span class="text-gray-700">{{ $country['name'] }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($country['percentage'], 1, ',', '.') }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-emerald-700" style="width: {{ min($country['percentage'], 100) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
--}}

<script type="application/json" id="analytics-json">@json($data)</script>
<script>
    function getAnalyticsUserQuery() {
        const params = new URLSearchParams();
        const companyId = document.getElementById('client-analytics-company')?.value;

        if (window.analyticsUserId) params.set('user_id', window.analyticsUserId);
        if (companyId) params.set('empresa_id', companyId);

        const query = params.toString();
        return query ? `&${query}` : '';
    }

    function exportEngagementReport(event) {
        event.preventDefault();
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = 'historial';
        if (timeRangeSelect) {
            switch (timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        fetch(`{{ route('clientes.analiticas.reporte-engagement') }}?view=${viewName}${getAnalyticsUserQuery()}`, { method: 'GET', headers: { 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => { if (!response.ok) throw new Error('Error al generar el informe'); return response.blob(); })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `informe_engagement_${viewName}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => { console.error('Error al exportar reporte de engagement:', error); alert('Ocurrio un error al generar el informe. Por favor, intentalo de nuevo.'); });
    }

    function exportReachReport(event) {
        event.preventDefault();
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = 'historial';
        if (timeRangeSelect) {
            switch (timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        fetch(`{{ route('clientes.analiticas.reporte-alcance') }}?view=${viewName}${getAnalyticsUserQuery()}`, { method: 'GET', headers: { 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => { if (!response.ok) throw new Error('Error al generar el informe de alcance'); return response.blob(); })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `informe_alcance_${viewName}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => { console.error('Error al exportar reporte de alcance:', error); alert('Ocurrio un error al generar el informe de alcance. Por favor, intentalo de nuevo.'); });
    }

    function exportFollowersReport(event) {
        event.preventDefault();
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = 'historial';
        if (timeRangeSelect) {
            switch (timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        fetch(`{{ route('clientes.analiticas.reporte-seguidores') }}?view=${viewName}${getAnalyticsUserQuery()}`, { method: 'GET', headers: { 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => { if (!response.ok) throw new Error('Error al generar el informe de seguidores'); return response.blob(); })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `informe_seguidores_${viewName}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => { console.error('Error al exportar reporte de seguidores:', error); alert('Ocurrio un error al generar el informe de seguidores. Por favor, intentalo de nuevo.'); });
    }

    function exportCTRReport(event) {
        event.preventDefault();
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = 'historial';
        if (timeRangeSelect) {
            switch (timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        fetch(`{{ route('clientes.analiticas.reporte-ctr') }}?view=${viewName}${getAnalyticsUserQuery()}`, { method: 'GET', headers: { 'Accept': 'application/pdf', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => { if (!response.ok) throw new Error('Error al generar el informe de CTR'); return response.blob(); })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'reporte_ctr_plataforma.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => { console.error('Error al exportar reporte de CTR:', error); alert('Ocurrio un error al generar el informe de CTR. Por favor, intentalo de nuevo.'); });
    }
</script>
