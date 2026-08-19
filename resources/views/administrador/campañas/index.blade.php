@extends('layouts.app')

@section('title', 'Gestión de Campañas')

@section('content') 
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4 sm:gap-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-bullhorn text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Gestión de Campañas</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Administra y supervisa todas las campañas de marketing</p>
                        </div>
                    </div>
                    <a href="{{ route('administrador.dashboard') }}"
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0"
                       style="background: linear-gradient(to right, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver al Panel
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Sin Campaña</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $clientesSinCampania->count() }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-user-clock text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Campañas Activas</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $campaniasActivas->count() }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-bolt text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #475569; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Finalizadas</p>
                                <p class="text-3xl font-bold text-white mt-1">{{ $campaniasFinalizadas->count() }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-check-circle text-white text-base"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de clientes sin campaña -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4" style="background: #ea9f21;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Clientes sin Campaña Activa</h2>
                    <p class="text-gray-600">Clientes que necesitan una nueva campaña de marketing</p>
                </div>
            </div>
            
            @if($clientesSinCampania->isEmpty())
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">¡Excelente! Todos los clientes tienen campañas activas</p>
                </div>
            @else
                <div class="mb-4">
                    <input type="text" id="filtro-clientes-sin-campania"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, email o plan">
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fin Suscripción</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-clientes-sin-campania" class="bg-white divide-y divide-gray-200">
                            @foreach($clientesSinCampania as $cliente)
                                <tr class="hover:bg-gray-50 transition-colors duration-200 fila-cliente-sin-campania"
                                    data-item-id="{{ $cliente['suscripcion_id'] }}"
                                    data-nombre="{{ mb_strtolower($cliente['nombre']) }}"
                                    data-email="{{ mb_strtolower($cliente['email']) }}"
                                    data-plan="{{ mb_strtolower($cliente['plan']) }}"
                                    data-fecha="{{ $cliente['fecha_fin_suscripcion_raw'] ?? '' }}">

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ substr($cliente['nombre'], 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">{{ $cliente['nombre'] }}</div>
                                                @if(!$cliente['tiene_empresa'])
                                                    <div class="relative group mt-1">
                                                        <a href="{{ route('administrador.empresas.crear-con-cuestionario', $cliente['id']) }}" 
                                                           class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 flex items-center w-max cursor-pointer hover:bg-amber-100 transition-colors">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                            </svg>
                                                            Sin Empresa Registrada
                                                        </a>
                                                        <div class="absolute left-0 top-full mt-2 hidden group-hover:block z-50">
                                                            <div class="bg-gray-900 text-white p-3 rounded-xl shadow-2xl min-w-[200px]">
                                                                <p class="text-xs mb-3 text-gray-300">Este cliente no ha completado la información de su empresa.</p>
                                                                <a href="{{ route('administrador.empresas.crear-con-cuestionario', $cliente['id']) }}" 
                                                                   class="inline-flex items-center w-full justify-center px-3 py-1.5 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                    </svg>
                                                                    CREAR EMPRESA
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente['email'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $cliente['plan'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente['fecha_fin_suscripcion'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if(!$cliente['tiene_empresa'])
                                                <div class="relative group">
                                                    <button type="button" 
                                                            class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed transition-all duration-200 shadow-sm">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        CREAR CON IA
                                                    </button>
                                                    
                                                    <!-- Hover Tooltip -->
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                        <div class="bg-gray-900 text-white p-4 rounded-xl shadow-2xl min-w-[220px] text-center">
                                                            <p class="text-xs font-bold text-amber-400 mb-1">SIN EMPRESA REGISTRADA</p>
                                                            <p class="text-[10px] text-gray-400 mb-3">Se requiere información de la empresa para usar la IA.</p>
                                                            <a href="{{ route('administrador.empresas.crear-con-cuestionario', $cliente['id']) }}" 
                                                               class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                </svg>
                                                                CREAR EMPRESA AHORA
                                                            </a>
                                                            <!-- Flecha del tooltip -->
                                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                                                <div class="border-8 border-transparent border-t-gray-900"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <button onclick="llenarConIA('{{ $cliente['id'] }}', '{{ $cliente['suscripcion_id'] }}', this)" 
                                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    CREAR CON IA
                                                </button>
                                            @endif

                                            <button onclick="mostrarFormulario('{{ $cliente['suscripcion_id'] }}')" 
                                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Crear Campaña
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Formulario mejorado -->
                                <tr id="form-{{ $cliente['suscripcion_id'] }}" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50">
                                    <td colspan="5" class="px-6 py-8">
                                        <div class="bg-white rounded-xl p-6 shadow-sm border border-blue-100">
                                            <div class="flex items-center mb-6">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-900">Nueva Campaña para {{ $cliente['nombre'] }}</h3>
                                            </div>
                                            
                                            <form id="crear-campania-form-{{ $cliente['suscripcion_id'] }}" 
                                                  action="{{ route('administrador.campañas.guardar') }}" 
                                                  method="POST" 
                                                  class="space-y-6"
                                                  onsubmit="return validarFormulario({{ $cliente['suscripcion_id'] }})">
                                                @csrf
                                                <input type="hidden" name="usuario_cliente_id" value="{{ $cliente['id'] }}">
                                                <input type="hidden" name="suscripcion_id" value="{{ $cliente['suscripcion_id'] }}">
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <!-- Admin -->
                                                    <div class="space-y-2">
                                                        <label class="block text-sm font-semibold text-gray-700">Administrador</label>
                                                        <div class="relative">
                                                            <input type="text" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-600" 
                                                                   value="{{ $adminActual->name }}" readonly>
                                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Community Manager -->
                                                    <div class="space-y-2">
                                                        <label for="cm-{{ $cliente['suscripcion_id'] }}" class="block text-sm font-semibold text-gray-700">
                                                            Community Manager <span class="text-red-500">*</span>
                                                        </label>
                                                        <select name="community_manager_id" id="cm-{{ $cliente['suscripcion_id'] }}" 
                                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                                                            <option value=">Seleccione un Community Manager</option>
                                                            @foreach($communityManagers as $cm)
                                                                <option value="{{ $cm->id }}">{{ $cm->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    
                                                    <!-- Nombre -->
                                                    <div class="space-y-2">
                                                        <label for="nombre-{{ $cliente['suscripcion_id'] }}" class="block text-sm font-semibold text-gray-700">
                                                            Nombre de la Campaña <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="text" name="nombre" id="nombre-{{ $cliente['suscripcion_id'] }}" 
                                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                                               required placeholder="Ej: Campaña Q2 2025">
                                                    </div>
                                                    
                                                    <!-- Descripción -->
                                                    <div class="md:col-span-2 space-y-2">
                                                        <label for="descripcion-{{ $cliente['suscripcion_id'] }}" class="block text-sm font-semibold text-gray-700">
                                                            Descripción y Objetivos <span class="text-red-500">*</span>
                                                        </label>
                                                        <textarea name="descripcion" id="descripcion-{{ $cliente['suscripcion_id'] }}" rows="4"
                                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none" 
                                                                  required placeholder="Describe los objetivos principales, público objetivo y estrategias clave de la campaña..."></textarea>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                                                    <button type="button" onclick="ocultarFormulario('{{ $cliente['suscripcion_id'] }}')" 
                                                            class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Crear Campaña
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="paginacion-clientes-sin-campania" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"></div>
            @endif
        </div>
        
        <!-- Sección de campañas activas -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4" style="background: #a7b838;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Campañas Activas</h2>
                    <p class="text-gray-600">Campañas en ejecución y desarrollo</p>
                </div>
            </div>
            
            @if($campaniasActivas->isEmpty())
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0-13h10a2 2 0 012 2v11a2 2 0 01-2 2H9m0-13v13"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">No hay campañas activas en este momento</p>
                </div>
            @else
                <div class="mb-4">
                    <input type="text" id="filtro-campanias-activas"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, campaña o community manager">
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Campaña</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Community Manager</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-campanias-activas" class="bg-white divide-y divide-gray-200">
                            @foreach($campaniasActivas as $campania)
                                <tr class="hover:bg-gray-50 transition-colors duration-200 fila-campania-activa"
                                    data-campania="{{ mb_strtolower($campania->nombre) }}"
                                    data-cliente="{{ mb_strtolower($campania->cliente->name) }}"
                                    data-community-manager="{{ mb_strtolower($campania->communityManager->name) }}"
                                    data-fecha="{{ \Carbon\Carbon::parse($campania->fecha_fin)->format('Y-m-d') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center text-white font-semibold">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">{{ $campania->nombre }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $campania->cliente->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $campania->communityManager->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            {{ $campania->estado == 'activa' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            <div class="w-2 h-2 rounded-full mr-2 
                                                {{ $campania->estado == 'activa' ? 'bg-green-400' : 'bg-yellow-400' }}"></div>
                                            {{ ucfirst($campania->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($campania->fecha_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('administrador.campañas.show', $campania->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </a>
                                            <!-- Botón Editar Campaña -->
                                            <a href="{{ route('administrador.campañas.edit', $campania->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Editar
                                            </a>

                                            <!-- Botón Eliminar Campaña -->
                                            <form action="{{ route('administrador.campañas.destroy', $campania->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar esta campaña? El cliente volverá a aparecer en la lista de clientes sin campaña.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="paginacion-campanias-activas" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"></div>
            @endif
        </div>
        
        <!-- Sección de campañas finalizadas -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4" style="background: #475569;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Campañas Finalizadas</h2>
                    <p class="text-gray-600">Historial de campañas completadas</p>
                </div>
            </div>
            
            @if($campaniasFinalizadas->isEmpty())
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h2m0-13h10a2 2 0 012 2v11a2 2 0 01-2 2H9m0-13v13"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">No hay campañas finalizadas</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <input type="text" id="filtro-campanias-finalizadas"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, campaña o community manager">
                    <select id="filtro-mes-campanias-finalizadas"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value=">Todos los meses</option>
                        @foreach($mesesFinalizadasDisponibles as $mes)
                            <option value="{{ str_pad($mes['numero'], 2, '0', STR_PAD_LEFT) }}">{{ $mes['nombre'] }}</option>
                        @endforeach
                    </select>
                    <select id="filtro-anio-campanias-finalizadas"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value=">Todos los años</option>
                        @foreach($aniosFinalizadasDisponibles as $anio)
                            <option value="{{ $anio }}">{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Campaña</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Community Manager</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-campanias-finalizadas" class="bg-white divide-y divide-gray-200">
                            @foreach($campaniasFinalizadas as $campania)
                                @php
                                    $estadoMostrado = \Carbon\Carbon::parse($campania->fecha_fin)->isPast() ? 'Inactiva' : ucfirst($campania->estado);
                                @endphp
                                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors duration-200 fila-campania-finalizada" data-campania="{{ mb_strtolower($campania->nombre) }}" data-cliente="{{ mb_strtolower($campania->cliente->name) }}" data-community-manager="{{ mb_strtolower($campania->communityManager->name) }}" data-mes="{{ \Carbon\Carbon::parse($campania->fecha_fin)->format('m') }}" data-anio="{{ \Carbon\Carbon::parse($campania->fecha_fin)->format('Y') }}" data-fecha="{{ \Carbon\Carbon::parse($campania->fecha_fin)->format('Y-m-d') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-gray-400 to-gray-500 rounded-lg flex items-center justify-center text-white font-semibold">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">{{ $campania->nombre }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $campania->cliente->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $campania->communityManager->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white"
                                              style="background-color: {{ $estadoMostrado === 'Inactiva' ? '#ed0551' : '#a7b838' }};">
                                            <div class="w-2 h-2 rounded-full mr-2 bg-white"></div>
                                            {{ $estadoMostrado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($campania->fecha_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('administrador.campañas.show', $campania->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </a>
                                            
                                            @if($campania->cliente->suscripciones()->where('estado', 'activa')->exists())
                                                <form action="{{ route('administrador.campañas.activar', $campania->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                        Reactivar
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="paginacion-campanias-finalizadas" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"></div>
            @endif
        </div>
    </div>
</div>

<div id="mensaje-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/60" onclick="cerrarModalMensaje()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-7.938 4h15.876c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L2.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Aviso</h3>
                        <p class="text-sm text-gray-500">No fue posible completar la accion.</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-5">
                <p id="mensaje-modal-texto" class="text-sm leading-6 text-gray-700"></p>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50">
                <button type="button"
                        onclick="cerrarModalMensaje()"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Scripts mejorados -->
<script>
    function mostrarModalMensaje(mensaje) {
        const modal = document.getElementById('mensaje-modal');
        const texto = document.getElementById('mensaje-modal-texto');
        if (!modal || !texto) return;
        texto.textContent = mensaje || 'Ocurrio un error inesperado.';
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }
    function cerrarModalMensaje() {
        const modal = document.getElementById('mensaje-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }
    // Función para mostrar formulario con animación suave
    function mostrarFormulario(clienteId) {
        const form = document.getElementById('form-' + clienteId);
        form.classList.remove('hidden');
        
        // Animación suave
        setTimeout(() => {
            form.style.opacity = '0';
            form.style.transform = 'translateY(-10px)';
            form.style.transition = 'all 0.3s ease-in-out';
            
            requestAnimationFrame(() => {
                form.style.opacity = '1';
                form.style.transform = 'translateY(0)';
            });
        }, 10);
        
        // Focus en el primer campo
        const firstInput = form.querySelector('select, input[type="text"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 300);
        }
    }

    // Función para crear campaña con IA
    function llenarConIA(clienteId, formKey, btn) {
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Obteniendo Plan...
        `;

        fetch(`/administrador/campañas/obtener-plan-ia/${clienteId}?suscripcion_id=${formKey}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Error al obtener el plan'); });
                }
                return response.json();
            })
            .then(data => {
                // Llenar campos
                const formElement = document.getElementById('crear-campania-form-' + formKey);
                if (formElement) {
                    formElement.querySelector('input[name="nombre"]').value = data.nombre;
                    formElement.querySelector('textarea[name="descripcion"]').value = data.descripcion;

                    // Mostrar el contenedor del formulario
                    mostrarFormulario(formKey);

                    // Pequeña pausa para asegurar que el formulario está visible antes del scroll
                    setTimeout(() => {
                        const formRow = document.getElementById('form-' + formKey);
                        formRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Resaltar el campo de CM
                        const cmSelect = formElement.querySelector('select[name="community_manager_id"]');
                        cmSelect.focus();
                        cmSelect.classList.add('ring-4', 'ring-purple-200');
                        setTimeout(() => cmSelect.classList.remove('ring-4', 'ring-purple-200'), 3000);
                    }, 400);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarModalMensaje(error.message || 'Hubo un error al procesar la solicitud');
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                btn.innerHTML = originalContent;
            });
    }
    
    // Función para ocultar formulario con animación suave
    function ocultarFormulario(clienteId) {
        const form = document.getElementById('form-' + clienteId);
        
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
        form.style.transition = 'all 0.3s ease-in-out';
        
        requestAnimationFrame(() => {
            form.style.opacity = '0';
            form.style.transform = 'translateY(-10px)';
        });
        
        setTimeout(() => {
            form.classList.add('hidden');
            form.style.opacity = '';
            form.style.transform = '';
            form.style.transition = '';
        }, 300);
    }
    

    function normalizarTexto(valor) {
        return (valor || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .trim();
    }

    function inicializarTablaFiltrable(config) {
        const tbody = document.getElementById(config.tableBodyId);
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll(config.rowSelector));
        const buscador = config.searchInputId ? document.getElementById(config.searchInputId) : null;
        const selectorMes = config.monthSelectId ? document.getElementById(config.monthSelectId) : null;
        const selectorAnio = config.yearSelectId ? document.getElementById(config.yearSelectId) : null;
        const paginacion = document.getElementById(config.paginationId);
        const filasPorPagina = config.pageSize || 5;
        let paginaActual = 1;

        function textoFila(fila) {
            return normalizarTexto([
                fila.dataset.nombre,
                fila.dataset.email,
                fila.dataset.plan,
                fila.dataset.campania,
                fila.dataset.cliente,
                fila.dataset.communityManager,
                fila.dataset.fecha,
            ].filter(Boolean).join(' '));
        }

        function filasFiltradas() {
            const termino = normalizarTexto(buscador ? buscador.value : '');
            const mes = selectorMes ? selectorMes.value : '';
            const anio = selectorAnio ? selectorAnio.value : '';

            return filas.filter((fila) => {
                const coincideBusqueda = !termino || textoFila(fila).includes(termino);
                const coincideMes = !mes || fila.dataset.mes === mes;
                const coincideAnio = !anio || fila.dataset.anio === anio;
                return coincideBusqueda && coincideMes && coincideAnio;
            });
        }

        function renderizar(total, paginas) {
            if (!paginacion) return;
            paginacion.innerHTML = '';

            const resumen = document.createElement('div');
            resumen.className = 'text-sm text-gray-600';
            if (total === 0) {
                resumen.textContent = 'No hay resultados para mostrar.';
            } else {
                const inicio = ((paginaActual - 1) * filasPorPagina) + 1;
                const fin = Math.min(paginaActual * filasPorPagina, total);
                resumen.textContent = `Mostrando ${inicio}-${fin} de ${total} registros`;
            }

            const controles = document.createElement('div');
            controles.className = 'flex flex-wrap items-center gap-2';

            const crearBoton = (texto, pagina, disabled = false, activo = false) => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.textContent = texto;
                boton.disabled = disabled;
                boton.className = `px-3 py-1.5 rounded-lg border text-sm transition-colors ${activo ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'} ${disabled ? 'opacity-50 cursor-not-allowed hover:bg-white' : ''}`;
                if (!disabled) {
                    boton.addEventListener('click', () => {
                        paginaActual = pagina;
                        aplicar();
                    });
                }
                return boton;
            };

            controles.appendChild(crearBoton('Anterior', paginaActual - 1, paginaActual === 1));
            for (let pagina = 1; pagina <= paginas; pagina++) {
                controles.appendChild(crearBoton(String(pagina), pagina, false, pagina === paginaActual));
            }
            controles.appendChild(crearBoton('Siguiente', paginaActual + 1, paginaActual === paginas));

            paginacion.appendChild(resumen);
            paginacion.appendChild(controles);
        }

        function aplicar() {
            const filtradas = filasFiltradas();
            const paginas = Math.max(1, Math.ceil(filtradas.length / filasPorPagina));
            if (paginaActual > paginas) paginaActual = paginas;

            const inicio = (paginaActual - 1) * filasPorPagina;
            const visibles = new Set(filtradas.slice(inicio, inicio + filasPorPagina));

            filas.forEach((fila) => {
                const mostrar = visibles.has(fila);
                fila.style.display = mostrar ? '' : 'none';
                if (config.linkedRowAttribute) {
                    const relacionada = tbody.querySelector(`[${config.linkedRowAttribute}="${fila.dataset.itemId}"]`);
                    if (relacionada) {
                        if (mostrar) {
                            relacionada.style.display = '';
                        } else {
                            relacionada.classList.add('hidden');
                            relacionada.style.display = 'none';
                        }
                    }
                }
            });

            renderizar(filtradas.length, paginas);
        }

        if (buscador) buscador.addEventListener('input', () => { paginaActual = 1; aplicar(); });
        if (selectorMes) selectorMes.addEventListener('change', () => { paginaActual = 1; aplicar(); });
        if (selectorAnio) selectorAnio.addEventListener('change', () => { paginaActual = 1; aplicar(); });

        aplicar();
    }

    // Mejorar experiencia de usuario con efectos hover
    document.addEventListener('DOMContentLoaded', function() {
        // Añadir efecto de hover a las tarjetas principales
        const cards = document.querySelectorAll('.bg-white.rounded-2xl');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'transform 0.2s ease-in-out';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Validación en tiempo real para formularios
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
                        this.classList.remove('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
                    } else {
                        this.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
                        this.classList.add('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
                    }
                });
            });
        });
    });
    
    // Función para confirmar reactivación de campañas
    document.addEventListener('DOMContentLoaded', function() {
        const reactivateForms = document.querySelectorAll('form[action*="activar"]');
        reactivateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿Está seguro de que desea reactivar esta campaña? Esto la moverá de nuevo a campañas activas.')) {
                    e.preventDefault();
                }
            });
        });
    });

    // Validar el formulario de nueva campaña
    function validarFormulario(clienteId) {
        const form = document.getElementById('crear-campania-form-' + clienteId);
        if (!form) return false;
        
        const cmSelect = form.querySelector('select[name="community_manager_id"]');
        const nombreInput = form.querySelector('input[name="nombre"]');
        const descripcionInput = form.querySelector('textarea[name="descripcion"]');
        
        if (!cmSelect || !cmSelect.value) {
            alert('Por favor, seleccione un Community Manager');
            if (cmSelect) cmSelect.focus();
            return false;
        }
        
        if (!nombreInput || !nombreInput.value.trim()) {
            alert('Por favor, ingrese un nombre para la campaña');
            if (nombreInput) nombreInput.focus();
            return false;
        }
        
        if (!descripcionInput || !descripcionInput.value.trim()) {
            alert('Por favor, ingrese una descripción para la campaña');
            if (descripcionInput) descripcionInput.focus();
            return false;
        }
        
        // Si todo es válido, permitir que el botón muestre su estado de "Procesando..." 
        // y se realice el submit natural
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        inicializarTablaFiltrable({
            tableBodyId: 'tabla-clientes-sin-campania',
            rowSelector: '.fila-cliente-sin-campania',
            searchInputId: 'filtro-clientes-sin-campania',
            paginationId: 'paginacion-clientes-sin-campania',
            linkedRowAttribute: 'data-form-for',
            pageSize: 5,
        });

        inicializarTablaFiltrable({
            tableBodyId: 'tabla-campanias-activas',
            rowSelector: '.fila-campania-activa',
            searchInputId: 'filtro-campanias-activas',
            paginationId: 'paginacion-campanias-activas',
            pageSize: 5,
        });

        inicializarTablaFiltrable({
            tableBodyId: 'tabla-campanias-finalizadas',
            rowSelector: '.fila-campania-finalizada',
            searchInputId: 'filtro-campanias-finalizadas',
            monthSelectId: 'filtro-mes-campanias-finalizadas',
            yearSelectId: 'filtro-anio-campanias-finalizadas',
            paginationId: 'paginacion-campanias-finalizadas',
            pageSize: 5,
        });

        const reactivateForms = document.querySelectorAll('form[action*="activar"]');
        reactivateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿Está seguro de que desea reactivar esta campaña? Esto la moverá de nuevo a campañas activas.')) {
                    e.preventDefault();
                }
            });
        });
    });

</script>

<!-- Estilos adicionales para mejorar la experiencia -->
<style>
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
        background:
            radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size:     50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat:   no-repeat;
    }

    /* Animación para las estadísticas del header */
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .bg-white:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Mejoras en la tipografía */
    .text-3xl {
        letter-spacing: -0.025em;
    }

    /* Scrollbar personalizada para el "Slider" de tablas */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Asegurar que el contenido no se corte */
    .min-w-full {
        min-width: 900px;
    }
    
    /* Efectos de hover mejorados para botones */
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    /* Indicadores de estado más llamativos */
    .bg-green-100 {
        background-color: #dcfce7;
    }
    
    .text-green-800 {
        color: #166534;
    }
    
    .bg-yellow-100 {
        background-color: #fef3c7;
    }
    
    .text-yellow-800 {
        color: #92400e;
    }
    
    .bg-gray-100 {
        background-color: #f3f4f6;
    }
    
    .text-gray-800 {
        color: #1f2937;
    }
    
    /* Efectos de transición suaves */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
    
    /* Mejoras en formularios */
    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Responsividad mejorada */
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .bg-white.rounded-2xl {
            border-radius: 1rem;
            padding: 1.5rem;
        }
        
        .text-3xl {
            font-size: 1.875rem;
        }
    }
</style>
@endsection


