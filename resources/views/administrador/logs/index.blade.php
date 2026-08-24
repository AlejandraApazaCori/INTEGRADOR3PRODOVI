@extends('layouts.app')

@section('title', 'Logs del Sistema')

@section('head')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
@endsection

@section('content')
<div class="logs-page min-h-screen">
    <div class="logs-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geomÃ©trico -->
        <div class="logs-hero mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-list-alt text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Logs del Sistema</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Monitorea los accesos y errores del servidor</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Globales -->
        <div class="logs-filters bg-white p-6 rounded-2xl shadow-lg border border-gray-100 mb-8">
            <form action="{{ route('administrador.logs.index') }}" method="GET" id="filterForm" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" id="activeTabInput" value="{{ request('tab', 'access') }}">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>Fecha Inicio
                    </label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>Fecha Fin
                    </label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-indigo-200/50">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                    <a href="{{ route('administrador.logs.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">
                        <i class="fas fa-redo mr-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabs -->
        <div class="logs-tabs mb-6 overflow-x-auto">
            <div class="border-b border-gray-200 min-w-max">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button onclick="switchTab('access')" id="tab-access" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Logs de Acceso
                    </button>
                    <button onclick="switchTab('security')" id="tab-security" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-shield-alt mr-2"></i>Logs de Seguridad
                    </button>
                    <button onclick="switchTab('audit')" id="tab-audit" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-history mr-2"></i>Logs de Actividad
                    </button>
                    <button onclick="switchTab('error')" id="tab-error" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Logs de Errores
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content: Access Logs -->
        <div id="content-access" class="logs-panel bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 mb-8">
            <div class="logs-panel-head px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-indigo-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Registros de Acceso</h2>
                </div>
                <button onclick="exportToPdf('access')" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors border border-indigo-100">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="logs-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha y Hora</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">IP / Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Método / URL</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado / Tiempo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">User-Agent</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($accessLogs as $index => $log)
                        @php
                            $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <div class="text-sm font-medium text-gray-900">{{ $log->ip_address }}</div>
                                <div class="text-sm text-gray-500">{{ $log->user ? $log->user->name : 'Invitado' }}</div>
                            </td>
                            <td class="px-6 py-4 border-r border-gray-100">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->method === 'GET' ? 'bg-green-100 text-green-800 border border-green-200' : ($log->method === 'POST' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-yellow-100 text-yellow-800 border border-yellow-200') }}">{{ $log->method }}</span>
                                <span class="ml-2 text-sm text-gray-600 break-all">{{ Str::limit($log->url, 50) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->status_code >= 400 ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200' }}">{{ $log->status_code }}</span>
                                <div class="text-xs text-gray-500 mt-1">{{ $log->response_time_ms }}ms</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $log->user_agent }}">
                                {{ Str::limit($log->user_agent, 40) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 block mb-2"></i>
                                No hay registros de acceso.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($accessLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $accessLogs->appends(['tab' => 'access'])->links() }}
            </div>
            @endif
        </div>

        <!-- Tab Content: Security Logs -->
        <div id="content-security" class="logs-panel hidden bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 mb-8">
            <div class="logs-panel-head px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-red-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Registros de Seguridad</h2>
                </div>
                <button onclick="exportToPdf('security')" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors border border-indigo-100">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="logs-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha / Hora</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Usuario / IP</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Evento</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Detalles</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($securityLogs as $index => $log)
                        @php
                            $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                            $eventColors = [
                                'login_success' => 'bg-green-100 text-green-800 border-green-200',
                                'login_failed' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $eventLabels = [
                                'login_success' => 'LOGUEO EXITOSO',
                                'login_failed' => 'LOGUEO FALLIDO',
                            ];
                            $colorClass = $eventColors[$log->event_type] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            $labelText = $eventLabels[$log->event_type] ?? str_replace('_', ' ', strtoupper($log->event_type));
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <div class="text-sm font-medium text-gray-900">{{ $log->user ? $log->user->name : 'Desconocido' }}</div>
                                <div class="text-sm text-gray-500">{{ $log->ip_address }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $colorClass }}">
                                    {{ $labelText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($log->details)
                                    <pre class="text-xs bg-gray-50 p-2 rounded border border-gray-100 max-w-xs overflow-x-auto">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    - 
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 block mb-2"></i>
                                No hay registros de seguridad.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($securityLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $securityLogs->appends(['tab' => 'security'])->links() }}
            </div>
            @endif
        </div>

        <!-- Tab Content: Audit Logs -->
        <div id="content-audit" class="logs-panel hidden bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 mb-8">
            <div class="logs-panel-head px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-history text-amber-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Registros de Actividad (Auditoría)</h2>
                </div>
                <button onclick="exportToPdf('audit')" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors border border-indigo-100">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="logs-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha / Hora</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Acción / Recurso</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Cambios</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($auditLogs as $index => $log)
                        @php
                            $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                            $actionColors = [
                                'create' => 'bg-green-100 text-green-800 border-green-200',
                                'update' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'delete' => 'bg-red-100 text-red-800 border-red-200',
                            ];
                            $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            $resourceName = class_basename($log->auditable_type);
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-100">
                                {{ $log->user ? $log->user->name : 'Sistema/Consola' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $colorClass }} mb-1">
                                    {{ strtoupper($log->action) }}
                                </span>
                                <div class="text-xs text-gray-500">Recurso: <span class="font-semibold">{{ $resourceName }}</span> (#{{ $log->auditable_id }})</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @if($log->old_values)
                                    <div>
                                        <span class="text-xs font-bold text-red-500 block mb-1">Anterior:</span>
                                        <div class="bg-red-50 p-2 rounded border border-red-100 text-xs overflow-x-auto max-w-[200px] lg:max-w-xs max-h-32 overflow-y-auto">
                                            @foreach($log->old_values as $key => $value)
                                                <div><span class="font-semibold">{{ $key }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($log->new_values)
                                    <div>
                                        <span class="text-xs font-bold text-green-500 block mb-1">Nuevo:</span>
                                        <div class="bg-green-50 p-2 rounded border border-green-100 text-xs overflow-x-auto max-w-[200px] lg:max-w-xs max-h-32 overflow-y-auto">
                                            @foreach($log->new_values as $key => $value)
                                                <div><span class="font-semibold">{{ $key }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 block mb-2"></i>
                                No hay registros de actividad.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($auditLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $auditLogs->appends(['tab' => 'audit'])->links() }}
            </div>
            @endif
        </div>

        <!-- Tab Content: Error Logs -->
        <div id="content-error" class="logs-panel hidden bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100 mb-8">
            <div class="logs-panel-head px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Registros de Errores</h2>
                </div>
                <button onclick="exportToPdf('error')" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors border border-indigo-100">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="logs-table min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha y Hora</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Mensaje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($errorLogs as $index => $log)
                        @php
                            $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $log['datetime'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $log['is_fatal'] ? 'bg-red-100 text-red-800 border-red-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200' }}">
                                    {{ Str::limit($log['type'], 20) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                                <div class="line-clamp-2" title="{{ $log['message'] }}">
                                    {{ $log['message'] }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                <i class="fas fa-inbox text-3xl text-gray-300 block mb-2"></i>
                                No hay registros de errores recientes.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($errorLogs, 'hasPages') && $errorLogs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $errorLogs->appends(['tab' => 'error'])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<div id="logs-export-modal" class="logs-export-modal hidden" role="dialog" aria-modal="true" aria-labelledby="logs-export-title">
    <div class="logs-export-dialog">
        <div class="logs-export-head">
            <div><h3 id="logs-export-title">Exportar logs en PDF</h3><p>Combina un rango de fechas con las páginas de registros que necesitas.</p></div>
            <button type="button" id="logs-export-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
        <form id="logs-export-form" method="GET">
            <div class="logs-export-section">
                <span><i class="fas fa-calendar-days"></i>Rango de fechas</span>
                <div class="logs-export-grid"><label>Desde<input type="date" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}"></label><label>Hasta<input type="date" name="fecha_fin" value="{{ $fechaFin ?? '' }}"></label></div>
            </div>
            <div class="logs-export-section">
                <span><i class="fas fa-file-lines"></i>Rango de páginas</span>
                <p>Cada página equivale a 15 registros ordenados desde el más reciente.</p>
                <div class="logs-export-grid"><label>Página desde<input type="number" name="page_from" min="1" placeholder="1"></label><label>Página hasta<input type="number" name="page_to" min="1" placeholder="Todas"></label></div>
            </div>
            <p id="logs-export-error" class="logs-export-error"></p>
            <div class="logs-export-buttons"><button type="button" id="logs-export-cancel">Cancelar</button><button type="submit"><i class="fas fa-file-pdf"></i>Generar PDF</button></div>
        </form>
    </div>
</div>

<style>
    /* Banner geomÃ©trico - Mismo estilo que las otras vistas */
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
        background:
            radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size:     50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat:   no-repeat;
    }

    @media (max-width: 640px) {
        .rp-banner .px-8 { 
            padding-left: 1.25rem; 
            padding-right: 1.25rem; 
        }
        .rp-banner .flex.flex-col.sm\:flex-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    /* Vista de logs alineada con el panel administrativo actual */
    .logs-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.logs-shell{max-width:none!important;padding:0!important}.logs-hero{width:100%;min-height:180px;margin:0 0 24px!important;border-radius:0!important;box-shadow:none}.logs-hero>.relative{min-height:180px;display:flex;align-items:center;padding:30px 48px!important}.logs-hero h1{margin:0 0 4px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.logs-hero h1:before{content:'Auditoría del sistema';display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.logs-hero p{color:#dbeafe!important;font-size:.74rem!important;font-weight:600}.logs-hero .h-14.w-14{width:52px;height:52px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14)!important;backdrop-filter:blur(5px)}.logs-hero .rp-banner-overlay{background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-repeat:no-repeat}
    .logs-filters{margin:24px 24px 0!important;padding:20px!important;border:1px solid #e1e3de!important;border-radius:16px!important;background:#f8f8f6!important;box-shadow:0 9px 22px rgba(55,60,52,.06)!important}.logs-filters form{display:grid!important;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px!important}.logs-filters form>div{grid-column:span 4;min-width:0!important}.logs-filters form>div:last-child{display:grid;grid-template-columns:1fr 1fr;gap:8px}.logs-filters label{margin:0 0 6px 2px!important;color:#565d53!important;font-size:.66rem!important}.logs-filters input{height:50px;border:1px solid #d9dcd6!important;border-radius:14px!important;background:#fff!important;color:#3f443d;box-shadow:0 2px 5px rgba(55,60,52,.08)}.logs-filters input:focus{border-color:#8a9186!important;box-shadow:0 0 0 3px rgba(98,104,95,.12)!important}.logs-filters button,.logs-filters a{min-height:50px;display:flex;align-items:center;justify-content:center;border-radius:14px!important;box-shadow:none!important;font-size:.72rem!important;font-weight:900!important}.logs-filters button{background:#117e8c!important;color:#fff!important}.logs-filters a{border:1px solid #d7dad4;background:#fff!important;color:#62685f!important}.logs-filters a:hover{background:#eff0ed!important;color:#3f443d!important}
    .logs-tabs{margin:20px 24px 14px!important;overflow:visible!important}.logs-tabs>div{border:0!important}.logs-tabs nav{display:flex;gap:7px;margin:0!important;padding:6px;border:1px solid #e1e3de;border-radius:14px;background:#f8f8f6}.logs-tabs button{min-height:40px;padding:9px 13px!important;border:0!important;border-radius:9px;color:#737a70!important;font-size:.68rem!important;font-weight:900!important}.logs-tabs button:hover{background:#eff0ed;color:#3f443d!important}.logs-tabs button.text-indigo-600{background:#fff!important;color:#117e8c!important;box-shadow:0 3px 9px rgba(55,60,52,.1)}
    .logs-panel{margin:0 24px 28px!important;border:1px solid #d8e3c7!important;border-radius:16px!important;background:#fff!important;box-shadow:0 9px 24px rgba(91,121,38,.1)!important}.logs-panel-head{padding:14px 16px!important;border-bottom:1px solid #e2ead5!important;background:#fff!important}.logs-panel-head>div>div{background:#edf4e4!important;color:#7da533!important}.logs-panel-head>div>div i{color:#7da533!important}.logs-panel-head h2{color:#31382b!important;font-size:.9rem!important}.logs-panel-head>button{min-height:38px;border:1px solid #f3c4c4!important;border-radius:10px!important;background:#fff!important;color:#b42323!important;font-size:.66rem!important;font-weight:900!important}.logs-panel-head>button:hover{background:#fff0f0!important}.logs-table{width:100%;border-collapse:collapse}.logs-table thead,.logs-table thead tr,.logs-table th{background:#7da533!important}.logs-table th{padding:13px 14px!important;border-right:1px solid rgba(255,255,255,.28)!important;border-bottom:0!important;color:#fff!important;font-size:.61rem!important;font-weight:900!important;letter-spacing:.045em}.logs-table td{padding:13px 14px!important;border-right:1px solid #d8e3c7!important;border-bottom:1px solid #dfe8d1!important;color:#4b5563;font-size:.7rem!important;vertical-align:middle}.logs-table th:last-child,.logs-table td:last-child{border-right:0!important}.logs-table tbody tr:nth-child(odd){background:#fff!important}.logs-table tbody tr:nth-child(even){background:#f1f7e8!important}.logs-table tbody tr:hover{background:#e6f0d8!important}.logs-panel>div:last-child{border-color:#e2ead5!important;background:#f9fbf5!important}
    .logs-export-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:16px;background:rgba(17,24,39,.58)}.logs-export-modal.flex{display:flex}.logs-export-dialog{width:100%;max-width:520px;padding:24px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.25)}.logs-export-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:19px}.logs-export-head h3{margin:0;color:#1f2937;font-size:1.18rem;font-weight:900}.logs-export-head p{margin:5px 0 0;color:#6b7280;font-size:.74rem}.logs-export-head>button{width:36px;height:36px;display:grid;place-items:center;flex:none;border-radius:50%;color:#6b7280}.logs-export-head>button:hover{background:#f3f4f6}.logs-export-section{padding:15px;border:1px solid #e1e3de;border-radius:13px;background:#fbfcf9}.logs-export-section+.logs-export-section{margin-top:12px}.logs-export-section>span{display:block;margin-bottom:10px;color:#3f443d;font-size:.71rem;font-weight:900}.logs-export-section>span i{width:18px;color:#7da533}.logs-export-section>p{margin:-4px 0 10px;color:#7b8376;font-size:.64rem}.logs-export-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.logs-export-grid label{color:#565d53;font-size:.64rem;font-weight:800}.logs-export-grid input{width:100%;height:44px;margin-top:6px;padding:0 11px;border:1px solid #d7dce2;border-radius:10px;background:#fff;color:#374151;font-size:.73rem;outline:0}.logs-export-grid input:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.logs-export-error{min-height:17px;margin:8px 0 0;color:#b91c1c;font-size:.67rem}.logs-export-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:10px}.logs-export-buttons button{min-height:44px;border-radius:11px;font-size:.72rem;font-weight:900}.logs-export-buttons button:first-child{background:#f3f4f6;color:#5f6670}.logs-export-buttons button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#b42323;color:#fff}
    @media(max-width:900px){.logs-filters form{grid-template-columns:repeat(2,minmax(0,1fr))!important}.logs-filters form>div{grid-column:span 1!important}.logs-tabs{overflow-x:auto!important}.logs-tabs nav{min-width:max-content}}
    @media(max-width:640px){.logs-hero,.logs-hero>.relative{min-height:205px}.logs-hero>.relative{padding:28px 20px!important}.logs-filters,.logs-tabs,.logs-panel{margin-right:12px!important;margin-left:12px!important}.logs-filters form{grid-template-columns:1fr!important}.logs-filters form>div{grid-column:1!important}.logs-filters form>div:last-child{grid-template-columns:1fr}.logs-panel-head{align-items:flex-start!important;flex-direction:column;gap:12px}.logs-panel-head>button{width:100%}.logs-export-grid,.logs-export-buttons{grid-template-columns:1fr}.logs-export-dialog{max-height:calc(100vh - 24px);overflow-y:auto;padding:20px}}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        let initialTab = 'access';

        if (urlParams.has('tab')) {
            initialTab = urlParams.get('tab');
        } else if (urlParams.has('security_page')) {
            initialTab = 'security';
        } else if (urlParams.has('audit_page')) {
            initialTab = 'audit';
        } else if (urlParams.has('error_page')) {
            initialTab = 'error';
        }

        switchTab(initialTab);
    });

    function switchTab(tab) {
        const tabs = ['access', 'security', 'audit', 'error'];
        
        // Ocultar todos los contenidos y resetear estilos de botones
        tabs.forEach(t => {
            document.getElementById('content-' + t).classList.add('hidden');
            const btn = document.getElementById('tab-' + t);
            if (btn) {
                btn.classList.remove('border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        });
        
        // Mostrar el contenido seleccionado
        const content = document.getElementById('content-' + tab);
        if (content) content.classList.remove('hidden');
        
        // Aplicar estilo activo al tab seleccionado
        const activeTab = document.getElementById('tab-' + tab);
        if (activeTab) {
            activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        }
        
        // Update URL to preserve tab on reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);

        // Update hidden input for form filters
        document.getElementById('activeTabInput').value = tab;
    }

    function exportToPdf(type) {
        const modal = document.getElementById('logs-export-modal');
        const form = document.getElementById('logs-export-form');
        form.action = `{{ url('/administrador/logs/export') }}/${type}`;
        form.querySelector('[name="fecha_inicio"]').value = document.querySelector('.logs-filters [name="fecha_inicio"]').value;
        form.querySelector('[name="fecha_fin"]').value = document.querySelector('.logs-filters [name="fecha_fin"]').value;
        document.getElementById('logs-export-error').textContent = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal=document.getElementById('logs-export-modal'),form=document.getElementById('logs-export-form'),error=document.getElementById('logs-export-error');
        const closeModal=()=>{modal.classList.add('hidden');modal.classList.remove('flex');document.body.classList.remove('overflow-hidden')};
        document.getElementById('logs-export-close')?.addEventListener('click',closeModal);
        document.getElementById('logs-export-cancel')?.addEventListener('click',closeModal);
        modal?.addEventListener('click',event=>{if(event.target===modal)closeModal()});
        document.addEventListener('keydown',event=>{if(event.key==='Escape'&&modal?.classList.contains('flex'))closeModal()});
        form?.addEventListener('submit',event=>{
            const from=parseInt(form.querySelector('[name="page_from"]').value||'1',10),toValue=form.querySelector('[name="page_to"]').value,to=toValue?parseInt(toValue,10):null;
            if(to!==null&&to<from){event.preventDefault();error.textContent='La página final debe ser igual o mayor que la página inicial.';return}
            error.textContent='';
        });
    });
</script>
@endsection
