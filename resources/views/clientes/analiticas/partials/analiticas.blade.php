

<!-- Primera fila de métricas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Engagement -->
    <a href="#" onclick="exportEngagementReport(event)" class="relative group block bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
<!-- Overlay de hover -->
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
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['engagement']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">
                        @if($data['engagement']['trend'] === 'up')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $data['engagement']['vs_previous'] }}
                    </span>
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

    <!-- Alcance Total -->
    <div onclick="exportReachReport(event)" class="relative group bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <!-- Overlay de hover -->
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
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['reach']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">
                        @if($data['reach']['trend'] === 'up')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $data['reach']['vs_previous'] }}
                    </span>
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

    <!-- Nuevos Seguidores -->
    <div onclick="exportFollowersReport(event)" class="relative group bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <!-- Overlay de hover -->
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
                <p class="text-sm font-medium text-gray-500 mb-1">Nuevos Seguidores</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['followers']['new'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['followers']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">
                        @if($data['followers']['trend'] === 'up')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $data['followers']['vs_previous'] }}
                    </span>
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

    <!-- CTR Click Through Rate -->
    <div onclick="exportCTRReport(event)" class="relative group bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 border border-gray-100 cursor-pointer overflow-hidden">
        <!-- Overlay de hover -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center z-20">
            <div class="bg-white px-4 py-2 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-sm font-semibold text-green-700">Descargar Reporte</span>
            </div>
        </div>

        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">CTR (Click Through Rate)</p>
                <p class="text-3xl font-bold text-gray-800">{{ $data['conversion']['rate'] }}</p>
                <div class="flex items-center mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $data['conversion']['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} flex items-center">
                        @if($data['conversion']['trend'] === 'up')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $data['conversion']['vs_previous'] }}
                    </span>
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

<!-- Segunda fila - Gráficos principales -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Gráfico de crecimiento de seguidores -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Crecimiento de Seguidores</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-full hover:bg-indigo-200 transition-colors">Facebook</button>
                <button class="px-3 py-1 text-xs bg-pink-100 text-pink-800 rounded-full hover:bg-pink-200 transition-colors">Instagram</button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="followersGrowthChart"></canvas>
        </div>
    </div>

    <!-- Distribución de engagement por plataforma -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Distribución de Engagement</h3>
            <select id="engagementDistribution" class="bg-gray-50 border border-gray-300 text-gray-700 py-1 px-3 pr-8 rounded-full focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 text-xs">
                <option value="platform">Por Plataforma</option>
                <option value="hour">Por Hora del Día</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="engagementDistributionChart"></canvas>
        </div>
    </div>
</div>

<!-- Tercera fila - Métricas secundarias -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Horario óptimo -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Horario Óptimo</h3>
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
        <div class="bg-gray-50 p-3 rounded-lg">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">{{ $data['optimal_time']['range'] }}</span>
                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">{{ $data['optimal_time']['engagement_boost'] }} engagement</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $data['optimal_time']['percent'] }}%"></div>
            </div>
        </div>
    </div>

    <!-- Demografía -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Audiencia Principal</h3>
        <div class="flex items-center mb-4">
            <div class="bg-pink-100 p-2 rounded-lg mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-medium text-gray-800">Perfil Demográfico</p>
                <p class="text-xs text-gray-500">Segmento más comprometido</p>
            </div>
        </div>
        <div class="space-y-2">
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Mujeres 25-34</span>
                    <span>{{ $data['demographics']['female_25_34'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-pink-500 h-1.5 rounded-full" style="width: {{ $data['demographics']['female_25_34'] }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Hombres 25-34</span>
                    <span>{{ $data['demographics']['male_25_34'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $data['demographics']['male_25_34'] }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Mujeres 18-24</span>
                    <span>{{ $data['demographics']['female_18_24'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $data['demographics']['female_18_24'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables data para gráficos de Chart.js
    window.analiticasData = @json($data);

    function exportEngagementReport(event) {
        event.preventDefault();
        
        const btnExportar = event.currentTarget.querySelector('p:last-child');
        
        // Obtener la vista seleccionada (7, 30, 365) para enviar al controlador
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = '30dias';
        
        if (timeRangeSelect) {
            switch(timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        
        // Hacer la petición al servidor
        fetch(`{{ route('clientes.analiticas.reporte-engagement') }}?view=${viewName}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al generar el informe');
            return response.blob();
        })
        .then(blob => {
            // Crear un enlace para descargar el PDF
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `informe_engagement_${viewName}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error al exportar reporte de engagement:', error);
            alert('Ocurrió un error al generar el informe. Por favor, inténtalo de nuevo.');
        });
    }

    function exportReachReport(event) {
        event.preventDefault();
        
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = '30dias';
        
        if (timeRangeSelect) {
            switch(timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        
        fetch(`{{ route('clientes.analiticas.reporte-alcance') }}?view=${viewName}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al generar el informe de alcance');
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `informe_alcance_${viewName}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error al exportar reporte de alcance:', error);
            alert('Ocurrió un error al generar el informe de alcance. Por favor, inténtalo de nuevo.');
        });
    }

    function exportFollowersReport(event) {
        event.preventDefault();
        
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = '30dias';
        
        if (timeRangeSelect) {
            switch(timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        
        fetch(`{{ route('clientes.analiticas.reporte-seguidores') }}?view=${viewName}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al generar el informe de seguidores');
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `informe_seguidores_${viewName}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error al exportar reporte de seguidores:', error);
            alert('Ocurrió un error al generar el informe de seguidores. Por favor, inténtalo de nuevo.');
        });
    }

    function exportCTRReport(event) {
        event.preventDefault();
        
        const timeRangeSelect = document.getElementById('timeRange');
        let viewName = '30dias';
        
        if (timeRangeSelect) {
            switch(timeRangeSelect.value) {
                case '7': viewName = '7dias'; break;
                case '30': viewName = '30dias'; break;
                case '365': viewName = 'anual'; break;
            }
        }
        
        // Mostrar feedback de carga si fuera necesario, similar a los otros reportes
        fetch(`{{ route('clientes.analiticas.reporte-ctr') }}?view=${viewName}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al generar el informe de CTR');
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `reporte_ctr_plataforma.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Error al exportar reporte de CTR:', error);
            alert('Ocurrió un error al generar el informe de CTR. Por favor, inténtalo de nuevo.');
        });
    }
</script>


