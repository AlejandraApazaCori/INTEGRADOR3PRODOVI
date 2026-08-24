@extends('layouts.app')

@section('title', 'Gestión de Campañas')

@section('content') 
<div class="campaigns-page">
    <div class="campaigns-shell">
        <nav class="campaigns-top-actions" aria-label="Acciones de campañas">
            <a href="{{ route('administrador.campañas.index') }}" class="campaign-top-action is-active"><i class="fas fa-table-columns"></i>General</a>
            <a href="{{ route('administrador.campañas.analiticas') }}" class="campaign-top-action"><i class="fas fa-chart-line"></i>Analíticas</a>
            <a href="{{ route('administrador.dashboard') }}" class="campaign-top-action"><i class="fas fa-arrow-left"></i>Volver al panel</a>
        </nav>

        <!-- Banner -->
        <header class="campaigns-hero overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="campaigns-hero-content relative z-10">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-4 sm:gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-bullhorn text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Gestión de Campañas</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Administra y supervisa todas las campañas de marketing</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="campaign-alert campaign-alert-success"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="campaign-alert campaign-alert-error"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>
        @endif

        <section class="campaign-kpis" aria-label="Resumen de campañas">
            <article class="campaign-kpi campaign-kpi-pending">
                <div>
                    <span>Sin campaña</span>
                    <strong>{{ $clientesSinCampania->count() }}</strong>
                    <small>Clientes pendientes de configuración</small>
                </div>
                <i class="fas fa-user-clock"></i>
            </article>
            <article class="campaign-kpi campaign-kpi-active">
                <div>
                    <span>Campañas activas</span>
                    <strong>{{ $campaniasActivas->count() }}</strong>
                    <small>En ejecución o temporalmente pausadas</small>
                </div>
                <i class="fas fa-bolt"></i>
            </article>
            <article class="campaign-kpi campaign-kpi-finished">
                <div>
                    <span>Finalizadas</span>
                    <strong>{{ $campaniasFinalizadas->count() }}</strong>
                    <small>Registros del historial de campañas</small>
                </div>
                <i class="fas fa-check-circle"></i>
            </article>
        </section>

        <!-- Sección de clientes sin campaña -->
        <section class="campaign-section campaign-section-pending bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
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
                <div class="campaign-filter-bar mb-4">
                    <input type="text" id="filtro-clientes-sin-campania"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, email o plan">
                </div>
                <div class="campaign-table-wrap overflow-x-auto rounded-xl border border-gray-200 custom-scrollbar">
                    <table class="campaign-table min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Empresa</th>
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
                                        @if($cliente['tiene_empresa'])
                                            <a href="{{ route('administrador.empresas.show', $cliente['empresa_id']) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600">
                                                {{ $cliente['empresa_nombre'] }}
                                            </a>
                                            <div class="mt-1">
                                                @if($cliente['tiene_plan_marketing'])
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700">
                                                        <i class="fas fa-circle-check"></i> Plan de marketing activo
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700">
                                                        <i class="fas fa-triangle-exclamation"></i> Sin plan de marketing
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Sin empresa</span>
                                        @endif
                                    </td>
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
                                            @elseif($cliente['tiene_plan_marketing'])
                                                <button type="button"
                                                        data-plan-url="{{ route('administrador.campañas.plan-ia', ['empresa' => $cliente['empresa_id'], 'suscripcion_id' => $cliente['suscripcion_id']]) }}"
                                                        onclick="llenarConIA('{{ $cliente['suscripcion_id'] }}', this)"
                                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    CREAR CON IA
                                                </button>
                                            @else
                                                <div class="relative group">
                                                    <button type="button" disabled
                                                            class="inline-flex items-center px-4 py-2 bg-gray-400 text-white text-sm font-medium rounded-lg cursor-not-allowed shadow-sm">
                                                        <i class="fas fa-wand-magic-sparkles mr-2"></i>
                                                        CREAR CON IA
                                                    </button>
                                                    <div class="absolute bottom-full left-1/2 z-50 mb-2 hidden min-w-[240px] -translate-x-1/2 group-hover:block">
                                                        <div class="rounded-xl bg-gray-900 p-4 text-center text-white shadow-2xl">
                                                            <p class="mb-1 text-xs font-bold text-amber-400">ESTA EMPRESA NO TIENE PLAN</p>
                                                            <p class="mb-3 text-[10px] text-gray-300">Genera primero el plan de marketing de {{ $cliente['empresa_nombre'] }}.</p>
                                                            <a href="{{ route('administrador.empresas.show', $cliente['empresa_id']) }}"
                                                               class="inline-flex w-full items-center justify-center rounded-lg bg-purple-600 px-3 py-2 text-[10px] font-bold text-white hover:bg-purple-700">
                                                                <i class="fas fa-building mr-1"></i> IR A LA EMPRESA
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
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
                                <tr id="form-{{ $cliente['suscripcion_id'] }}" data-form-for="{{ $cliente['suscripcion_id'] }}" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50">
                                    <td colspan="6" class="px-6 py-8">
                                        <div class="campaign-create-card bg-white">
                                            <header class="campaign-create-header">
                                                <div class="campaign-create-heading">
                                                    <span class="campaign-create-icon"><i class="fas fa-bullhorn"></i></span>
                                                    <div>
                                                        <span class="campaign-create-eyebrow">Configuración de campaña</span>
                                                        <h3>Nueva campaña para {{ $cliente['nombre'] }}</h3>
                                                        <p>Define al responsable y la información principal antes de crearla.</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="campaign-create-close" onclick="ocultarFormulario('{{ $cliente['suscripcion_id'] }}')" aria-label="Cerrar formulario">
                                                    <i class="fas fa-xmark"></i>
                                                </button>
                                            </header>

                                            <form id="crear-campania-form-{{ $cliente['suscripcion_id'] }}" 
                                                  action="{{ route('administrador.campañas.guardar') }}" 
                                                  method="POST" 
                                                  class="campaign-create-form"
                                                  onsubmit="return validarFormulario({{ $cliente['suscripcion_id'] }})">
                                                @csrf
                                                <input type="hidden" name="usuario_cliente_id" value="{{ $cliente['id'] }}">
                                                <input type="hidden" name="suscripcion_id" value="{{ $cliente['suscripcion_id'] }}">

                                                <div class="campaign-create-context">
                                                    <div class="campaign-context-item">
                                                        <span class="campaign-context-avatar">{{ mb_strtoupper(mb_substr($cliente['nombre'], 0, 1)) }}</span>
                                                        <div>
                                                            <small>Cliente</small>
                                                            <strong>{{ $cliente['nombre'] }}</strong>
                                                            <span>{{ $cliente['plan'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="campaign-context-item">
                                                        <span class="campaign-context-avatar is-admin"><i class="fas fa-user-shield"></i></span>
                                                        <div>
                                                            <small>Creada por</small>
                                                            <strong>{{ $adminActual->name }}</strong>
                                                            <span>Administrador del sistema</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="campaign-create-fields">
                                                    <div class="campaign-create-field">
                                                        <label for="cm-{{ $cliente['suscripcion_id'] }}">
                                                            <span>Community Manager <b>*</b></span>
                                                            <small>Responsable de ejecutar la campaña</small>
                                                        </label>
                                                        <div class="campaign-assignee-picker">
                                                        <div class="campaign-custom-select" data-campaign-cm-select>
                                                            <select name="community_manager_id" id="cm-{{ $cliente['suscripcion_id'] }}" class="campaign-native-select" required tabindex="-1" aria-hidden="true">
                                                                <option value="">Selecciona un responsable</option>
                                                                @foreach($communityManagers as $cm)
                                                                    <option value="{{ $cm->id }}">{{ $cm->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="campaign-custom-trigger" aria-haspopup="listbox" aria-expanded="false">
                                                                <i class="fas fa-user-tie"></i>
                                                                <span data-campaign-selected>Selecciona un responsable</span>
                                                                <i class="fas fa-chevron-down"></i>
                                                            </button>
                                                            <div class="campaign-custom-menu" role="listbox">
                                                                <button type="button" data-value="" class="is-selected" role="option" aria-selected="true">
                                                                    <span class="campaign-option-icon"><i class="fas fa-user-plus"></i></span>
                                                                    <span><strong>Selecciona un responsable</strong><small>Community Manager asignado</small></span>
                                                                    <i class="fas fa-check campaign-option-check"></i>
                                                                </button>
                                                                @foreach($communityManagers as $cm)
                                                                    <button type="button" data-value="{{ $cm->id }}" role="option" aria-selected="false">
                                                                        <span class="campaign-option-avatar">{{ mb_strtoupper(mb_substr($cm->name, 0, 1)) }}</span>
                                                                        <span><strong>{{ $cm->name }}</strong><small>Community Manager</small></span>
                                                                        <i class="fas fa-check campaign-option-check"></i>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <button type="button"
                                                                class="campaign-recommend-button"
                                                                data-recommend-url="{{ route('administrador.campañas.recomendar-community-manager', ['suscripcion_id' => $cliente['suscripcion_id']]) }}"
                                                                onclick="recomendarCommunityManager('{{ $cliente['suscripcion_id'] }}', this)">
                                                            <i class="fas fa-wand-magic-sparkles"></i>
                                                            Recomendar
                                                        </button>
                                                        </div>
                                                        <div class="campaign-recommendation hidden" data-campaign-recommendation role="status" aria-live="polite"></div>
                                                    </div>

                                                    <div class="campaign-create-field">
                                                        <label for="nombre-{{ $cliente['suscripcion_id'] }}">
                                                            <span>Nombre de la campaña <b>*</b></span>
                                                            <small>Usa un nombre corto y fácil de identificar</small>
                                                        </label>
                                                        <div class="campaign-input-wrap">
                                                            <i class="fas fa-signature"></i>
                                                            <input type="text" name="nombre" id="nombre-{{ $cliente['suscripcion_id'] }}" required placeholder="Ej.: Lanzamiento de invierno 2026">
                                                        </div>
                                                    </div>

                                                    <div class="campaign-create-field campaign-create-field-full">
                                                        <label for="descripcion-{{ $cliente['suscripcion_id'] }}">
                                                            <span>Descripción y objetivos <b>*</b></span>
                                                            <small>Resume el público, el objetivo y la estrategia principal</small>
                                                        </label>
                                                        <div class="campaign-input-wrap campaign-textarea-wrap">
                                                            <i class="fas fa-align-left"></i>
                                                            <textarea name="descripcion" id="descripcion-{{ $cliente['suscripcion_id'] }}" rows="5" required placeholder="Describe qué se quiere conseguir, a quién se dirigirá la campaña y cuál será el enfoque..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <footer class="campaign-create-footer">
                                                    <p><i class="fas fa-circle-info"></i> Los campos marcados con * son obligatorios.</p>
                                                    <div>
                                                        <button type="button" onclick="ocultarFormulario('{{ $cliente['suscripcion_id'] }}')" class="campaign-create-cancel">
                                                            Cancelar
                                                        </button>
                                                        <button type="submit" class="campaign-create-submit">
                                                            <i class="fas fa-check"></i>
                                                            Crear campaña
                                                        </button>
                                                    </div>
                                                </footer>
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
        </section>
        
        <!-- Sección de campañas activas -->
        <section class="campaign-section campaign-section-active bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
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
                <div class="campaign-filter-bar mb-4">
                    <input type="text" id="filtro-campanias-activas"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, campaña o community manager">
                </div>
                <div class="campaign-table-wrap overflow-x-auto rounded-xl border border-gray-200">
                    <table class="campaign-table min-w-full divide-y divide-gray-200">
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
        </section>
        
        <!-- Sección de campañas finalizadas -->
        <section class="campaign-section campaign-section-finished bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
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
                <div class="campaign-filter-bar campaign-filter-grid grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <input type="text" id="filtro-campanias-finalizadas"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="Buscar por cliente, campaña o community manager">
                    <select id="filtro-mes-campanias-finalizadas"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Todos los meses</option>
                        @foreach($mesesFinalizadasDisponibles as $mes)
                            <option value="{{ str_pad($mes['numero'], 2, '0', STR_PAD_LEFT) }}">{{ $mes['nombre'] }}</option>
                        @endforeach
                    </select>
                    <select id="filtro-anio-campanias-finalizadas"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Todos los años</option>
                        @foreach($aniosFinalizadasDisponibles as $anio)
                            <option value="{{ $anio }}">{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campaign-table-wrap overflow-x-auto rounded-xl border border-gray-200 custom-scrollbar">
                    <table class="campaign-table min-w-full divide-y divide-gray-200">
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
        </section>
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
    function llenarConIA(formKey, btn) {
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

        fetch(btn.dataset.planUrl, {
            headers: { 'Accept': 'application/json' }
        })
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
                        const cmDropdown = cmSelect?.closest('[data-campaign-cm-select]');
                        const cmTrigger = cmDropdown?.querySelector('.campaign-custom-trigger');
                        cmTrigger?.focus();
                        cmDropdown?.classList.add('is-highlighted');
                        setTimeout(() => cmDropdown?.classList.remove('is-highlighted'), 3000);
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

    async function recomendarCommunityManager(formKey, button) {
        const form = document.getElementById('crear-campania-form-' + formKey);
        const dropdown = form?.querySelector('[data-campaign-cm-select]');
        const result = form?.querySelector('[data-campaign-recommendation]');
        if (!form || !dropdown || !result || !button?.dataset.recommendUrl) return;

        const originalContent = button.innerHTML;
        button.disabled = true;
        button.classList.add('is-loading');
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analizando';
        result.classList.add('hidden');
        result.classList.remove('is-error');

        try {
            const response = await fetch(button.dataset.recommendUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();

            if (!response.ok) throw new Error(data.message || 'No fue posible calcular una recomendación.');

            const recommendation = data.recommended;
            const option = dropdown.querySelector(`.campaign-custom-menu [data-value="${recommendation.id}"]`);
            if (!option) throw new Error('El responsable recomendado ya no está disponible en la lista.');

            option.click();
            dropdown.classList.add('is-recommended');
            setTimeout(() => dropdown.classList.remove('is-recommended'), 2400);

            const heading = document.createElement('strong');
            heading.textContent = `Recomendado: ${recommendation.name}`;
            const explanation = document.createElement('span');
            explanation.textContent = `${recommendation.reason} Se evaluaron ${data.evaluated} responsables para una campaña hasta el ${data.campaign_ends_at}.`;
            result.replaceChildren(heading, explanation);
            result.classList.remove('hidden');
        } catch (error) {
            const message = document.createElement('span');
            message.textContent = error.message || 'No fue posible calcular una recomendación.';
            result.replaceChildren(message);
            result.classList.remove('hidden');
            result.classList.add('is-error');
        } finally {
            button.disabled = false;
            button.classList.remove('is-loading');
            button.innerHTML = originalContent;
        }
    }

    function inicializarDropdownsResponsables() {
        const dropdowns = Array.from(document.querySelectorAll('[data-campaign-cm-select]'));

        const cerrarDropdowns = (excepto = null) => {
            dropdowns.forEach((dropdown) => {
                if (dropdown === excepto) return;
                dropdown.classList.remove('is-open');
                dropdown.querySelector('.campaign-custom-trigger')?.setAttribute('aria-expanded', 'false');
            });
        };

        dropdowns.forEach((dropdown) => {
            const select = dropdown.querySelector('select[name="community_manager_id"]');
            const trigger = dropdown.querySelector('.campaign-custom-trigger');
            const label = dropdown.querySelector('[data-campaign-selected]');
            const options = Array.from(dropdown.querySelectorAll('.campaign-custom-menu [data-value]'));

            const seleccionar = (option, emitirCambio = true) => {
                const value = option.dataset.value || '';
                select.value = value;
                label.textContent = option.querySelector('strong')?.textContent.trim() || 'Selecciona un responsable';
                trigger.classList.toggle('has-value', value !== '');
                dropdown.classList.remove('is-invalid', 'is-open');
                trigger.setAttribute('aria-expanded', 'false');

                options.forEach((item) => {
                    const selected = item === option;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                });

                if (emitirCambio) select.dispatchEvent(new Event('change', { bubbles: true }));
            };

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const abrir = !dropdown.classList.contains('is-open');
                cerrarDropdowns(dropdown);
                dropdown.classList.toggle('is-open', abrir);
                trigger.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            });

            options.forEach((option) => option.addEventListener('click', () => seleccionar(option)));

            const initialOption = options.find((option) => option.dataset.value === select.value) || options[0];
            seleccionar(initialOption, false);
        });

        document.addEventListener('click', () => cerrarDropdowns());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') cerrarDropdowns();
        });
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
            const dropdown = cmSelect?.closest('[data-campaign-cm-select]');
            dropdown?.classList.add('is-invalid');
            dropdown?.querySelector('.campaign-custom-trigger')?.focus();
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
        inicializarDropdownsResponsables();

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

    /* Rediseño alineado con Pagos, Empresas, Logs y Backups */
    .campaigns-page { min-height:100vh; padding:0 0 48px; background:#fff; color:#302834; }
    .campaigns-shell { position:relative; display:flex; flex-direction:column; width:100%; }
    .campaigns-hero { order:1; width:100%; min-height:180px; margin:0; border-radius:0; box-shadow:none; }
    .campaigns-hero .rp-banner-overlay { background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%); background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%; background-position:0 0,0 0,100% 0,100% 100%,0 100%; background-repeat:no-repeat; }
    .campaigns-hero-content { display:flex; align-items:center; min-height:180px; padding:30px 48px; }
    .campaigns-hero-content>div { width:100%; padding-right:440px; }
    .campaigns-hero h1 { margin:0 0 4px; color:#fff; font-size:clamp(1.55rem,3vw,2.25rem); font-weight:900; letter-spacing:-.04em; }
    .campaigns-hero h1::before { content:'Operación de marketing'; display:block; margin-bottom:7px; color:#dbeafe; font-size:.68rem; font-weight:900; letter-spacing:.15em; text-transform:uppercase; }
    .campaigns-hero p { color:#dbeafe!important; font-size:.74rem!important; font-weight:600; }
    .campaigns-hero .h-14.w-14 { width:52px; height:52px; border:1px solid rgba(255,255,255,.24); border-radius:14px; background:rgba(255,255,255,.14)!important; backdrop-filter:blur(5px); }
    .campaigns-top-actions { position:absolute; z-index:20; top:67px; right:48px; display:flex; justify-content:flex-end; gap:9px; padding:0; }
    .campaign-top-action { min-height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 13px; border:1px solid rgba(255,255,255,.24); border-radius:.65rem; background:rgba(255,255,255,.12); color:#fff; font-size:.69rem; font-weight:900; text-decoration:none; backdrop-filter:blur(4px); transition:.18s; }
    .campaign-top-action.is-active { border-color:#fff; background:#fff; color:#4f46e5; }
    .campaign-top-action:hover { transform:translateY(-2px); border-color:#fff; background:#fff; color:#4f46e5; box-shadow:0 8px 20px rgba(31,41,55,.16); }
    .campaign-alert { order:2; width:calc(100% - 48px); margin:24px auto 0; padding:13px 16px; display:flex; align-items:center; gap:10px; border:1px solid; border-radius:12px; font-size:.76rem; font-weight:800; }
    .campaign-alert-success { border-color:#bfe3c5; background:#ecf8ee; color:#276738; }
    .campaign-alert-error { border-color:#f3c4c4; background:#fff0f0; color:#a72d2d; }
    .campaign-kpis { order:3; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; margin:24px 24px; }
    .campaign-kpi { --kpi-accent:#117e8c; --kpi-soft:#e6f4f5; --kpi-rgb:17,126,140; position:relative; isolation:isolate; overflow:hidden; min-height:132px; padding:21px; display:flex; align-items:center; justify-content:space-between; gap:18px; border:1px solid rgba(var(--kpi-rgb),.22); border-radius:1rem; background:linear-gradient(135deg,#fff 35%,var(--kpi-soft)); box-shadow:inset 0 4px 0 var(--kpi-accent),0 10px 24px rgba(45,66,34,.09); transition:.22s; }
    .campaign-kpi::before { content:''; position:absolute; z-index:-1; top:-42px; right:-34px; width:125px; height:125px; border:22px solid rgba(var(--kpi-rgb),.09); border-radius:50%; }
    .campaign-kpi::after { content:''; position:absolute; z-index:-1; right:13px; bottom:8px; width:88px; height:45px; opacity:.22; background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px); background-size:9px 9px; }
    .campaign-kpi:hover { transform:translateY(-5px); border-color:rgba(var(--kpi-rgb),.38); box-shadow:inset 0 4px 0 var(--kpi-accent),0 17px 32px rgba(var(--kpi-rgb),.16); }
    .campaign-kpi span,.campaign-kpi small { display:block; }
    .campaign-kpi span { color:#596170; font-size:.7rem; font-weight:900; letter-spacing:.025em; text-transform:uppercase; }
    .campaign-kpi strong { display:block; margin-top:9px; color:#263024; font-size:1.85rem; font-weight:900; line-height:1.1; }
    .campaign-kpi small { margin-top:8px; color:#7f8878; font-size:.62rem; font-weight:600; }
    .campaign-kpi>i { width:52px; height:52px; display:grid; place-items:center; flex:0 0 auto; border:1px solid rgba(255,255,255,.55); border-radius:14px; background:var(--kpi-accent); color:#fff; font-size:1.18rem; box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27),inset 0 1px 0 rgba(255,255,255,.28); }
    .campaign-kpi-pending { --kpi-accent:#e3a122; --kpi-soft:#fff6df; --kpi-rgb:227,161,34; }
    .campaign-kpi-active { --kpi-accent:#7da533; --kpi-soft:#f0f6e7; --kpi-rgb:125,165,51; }
    .campaign-kpi-finished { --kpi-accent:#5b2b76; --kpi-soft:#f3edf6; --kpi-rgb:91,43,118; }
    .campaign-section { --section-accent:#117e8c; order:4; overflow:visible; margin:0 24px 22px!important; padding:0!important; border:1px solid #e1e3de!important; border-radius:16px!important; background:#fff!important; box-shadow:0 9px 22px rgba(55,60,52,.06)!important; }
    .campaign-section-active { --section-accent:#7da533; }
    .campaign-section-pending { --section-accent:#e3a122; }
    .campaign-section-finished { --section-accent:#5b2b76; }
    .campaign-section>.flex.items-center.mb-6 { margin:0!important; padding:20px 22px 16px; border-bottom:1px solid #eceeea; }
    .campaign-section>.flex.items-center.mb-6>div:first-child { width:42px; height:42px; margin-right:13px!important; border-radius:12px!important; background:var(--section-accent)!important; box-shadow:0 7px 15px rgba(55,60,52,.14); }
    .campaign-section>.flex.items-center.mb-6 h2 { color:#25272b; font-size:.96rem; font-weight:900; letter-spacing:-.015em; }
    .campaign-section>.flex.items-center.mb-6 p { margin-top:3px; color:#737a70; font-size:.7rem; font-weight:600; }
    .campaign-filter-bar { position:relative; margin:18px 20px 14px!important; padding:14px; border:1px solid #e1e3de; border-radius:14px; background:#f8f8f6; box-shadow:0 5px 14px rgba(55,60,52,.04); }
    .campaign-filter-bar:not(.campaign-filter-grid)::before { content:'\f002'; position:absolute; z-index:1; top:30px; left:31px; color:#737a70; font-family:'Font Awesome 6 Free'; font-size:.85rem; font-weight:900; transform:translateY(-50%); }
    .campaign-filter-bar:not(.campaign-filter-grid) input { padding-left:43px!important; }
    .campaign-filter-bar input,.campaign-filter-bar select { width:100%; height:48px; padding:0 14px; border:1px solid #d9dcd6!important; border-radius:12px!important; background:#fff!important; color:#3f443d; box-shadow:0 2px 5px rgba(55,60,52,.07); font-size:.78rem; font-weight:650; outline:0; }
    .campaign-filter-bar input:focus,.campaign-filter-bar select:focus { border-color:#8a9186!important; box-shadow:0 0 0 3px rgba(98,104,95,.12)!important; }
    .campaign-table-wrap { margin:0 20px 18px; border:1px solid color-mix(in srgb,var(--section-accent) 28%,#e5e7eb)!important; border-radius:14px!important; background:#fff; box-shadow:0 7px 18px rgba(55,60,52,.06); }
    .campaign-table { width:100%!important; min-width:980px!important; border-collapse:collapse; table-layout:auto; }
    .campaign-table thead,.campaign-table thead tr,.campaign-table th { background:var(--section-accent)!important; }
    .campaign-table th { padding:14px 16px!important; border-right:1px solid rgba(255,255,255,.25); color:#fff!important; font-size:.61rem!important; font-weight:900!important; letter-spacing:.055em!important; }
    .campaign-table th:last-child,.campaign-table td:last-child { border-right:0; }
    .campaign-table td { padding:14px 16px!important; border-right:1px solid #e4e8e1; border-bottom:1px solid #e4e8e1; color:#4b5563; font-size:.72rem!important; }
    .campaign-table tbody tr:nth-child(odd)>td { background:#fff; }
    .campaign-table tbody tr:nth-child(even)>td { background:#f7f8f6; }
    .campaign-table tbody tr:hover>td { background:#f0f4ec!important; }
    .campaign-table tbody tr:last-child>td { border-bottom:0; }
    .campaign-table td .text-sm { font-size:.72rem!important; }
    .campaign-table td .w-10.h-10 { width:38px; height:38px; border-radius:11px; background:var(--section-accent)!important; box-shadow:0 5px 12px rgba(55,60,52,.15); }
    .campaign-table td .inline-flex.rounded-full { padding:5px 9px; font-size:.61rem!important; font-weight:800; }
    .campaign-table td .flex.space-x-2 { gap:6px; }
    .campaign-table td .flex.space-x-2>* { margin-left:0!important; }
    .campaign-table td a[class*='bg-'],.campaign-table td button[class*='bg-'] { min-height:32px; padding:0 10px!important; border-radius:9px!important; font-size:.62rem!important; font-weight:850!important; box-shadow:none!important; }
    .campaign-table tr[data-form-for]>td { padding:18px!important; background:#f4f7fd!important; }
    .campaign-table tr[data-form-for]>td>.bg-white { margin:0; padding:20px!important; border:1px solid #dce4ef; border-radius:13px!important; box-shadow:none!important; }
    .campaign-table tr[data-form-for]>td>.campaign-create-card { overflow:hidden; padding:0!important; border:1px solid #d9e2ef; border-radius:17px!important; background:#fff; box-shadow:0 12px 30px rgba(30,64,175,.08)!important; }
    .campaign-create-header { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; padding:20px 22px; border-bottom:1px solid #e4eaf2; background:linear-gradient(135deg,#f8fbff 0%,#f3f6ff 100%); }
    .campaign-create-heading { min-width:0; display:flex; align-items:center; gap:14px; }
    .campaign-create-icon { width:45px; height:45px; display:grid; place-items:center; flex:0 0 45px; border-radius:13px; background:#4f46e5; color:#fff; font-size:1rem; box-shadow:0 7px 16px rgba(79,70,229,.22); }
    .campaign-create-eyebrow { display:block; margin-bottom:3px; color:#4f46e5; font-size:.59rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    .campaign-create-heading h3 { margin:0; color:#20242b; font-size:.96rem; font-weight:900; letter-spacing:-.015em; }
    .campaign-create-heading p { margin:4px 0 0; color:#747d8a; font-size:.67rem; line-height:1.45; }
    .campaign-create-close { width:34px; height:34px; display:grid; place-items:center; flex:0 0 34px; border:1px solid #dce2eb; border-radius:10px; background:#fff; color:#7b8491; cursor:pointer; transition:.18s; }
    .campaign-create-close:hover { border-color:#fecaca; background:#fff1f2; color:#dc2626; }
    .campaign-create-form { padding:20px 22px 22px; }
    .campaign-create-context { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
    .campaign-context-item { min-width:0; display:flex; align-items:center; gap:11px; padding:12px 14px; border:1px solid #e2e7ee; border-radius:12px; background:#fafbfc; }
    .campaign-context-avatar { width:38px; height:38px; display:grid; place-items:center; flex:0 0 38px; border-radius:11px; background:#e3a122; color:#fff; font-size:.82rem; font-weight:900; }
    .campaign-context-avatar.is-admin { background:#117e8c; }
    .campaign-context-item div { min-width:0; }
    .campaign-context-item small,.campaign-context-item strong,.campaign-context-item div>span { display:block; }
    .campaign-context-item small { color:#8a93a0; font-size:.56rem; font-weight:900; letter-spacing:.07em; text-transform:uppercase; }
    .campaign-context-item strong { margin-top:2px; overflow:hidden; color:#2f3540; font-size:.72rem; font-weight:850; text-overflow:ellipsis; white-space:nowrap; }
    .campaign-context-item div>span { margin-top:2px; color:#8a93a0; font-size:.59rem; }
    .campaign-create-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:17px; }
    .campaign-create-field { min-width:0; }
    .campaign-create-field-full { grid-column:1/-1; }
    .campaign-create-field>label { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin:0 2px 7px; }
    .campaign-create-field>label span { color:#3d4652; font-size:.69rem; font-weight:900; }
    .campaign-create-field>label b { color:#dc2626; }
    .campaign-create-field>label small { color:#929aa6; font-size:.57rem; font-weight:600; text-align:right; }
    .campaign-input-wrap { position:relative; min-height:48px; display:flex; align-items:center; border:1px solid #d7dde6; border-radius:12px; background:#fff; box-shadow:0 2px 5px rgba(15,23,42,.04); transition:.18s; }
    .campaign-input-wrap:focus-within { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.11),0 3px 8px rgba(15,23,42,.05); }
    .campaign-input-wrap>i:first-child { width:44px; flex:0 0 44px; color:#7d8795; text-align:center; font-size:.83rem; }
    .campaign-input-wrap input,.campaign-input-wrap select,.campaign-input-wrap textarea { width:100%; min-width:0; border:0!important; background:transparent!important; color:#303846; font-size:.72rem; font-weight:650; outline:0; box-shadow:none!important; }
    .campaign-input-wrap input,.campaign-input-wrap select { height:46px; padding:0 40px 0 0; }
    .campaign-input-wrap select { appearance:none; cursor:pointer; }
    .campaign-select-arrow { position:absolute; right:14px; color:#929aa6!important; font-size:.62rem!important; pointer-events:none; }
    .campaign-textarea-wrap { align-items:flex-start; min-height:125px; }
    .campaign-textarea-wrap>i:first-child { padding-top:16px; }
    .campaign-input-wrap textarea { min-height:123px; padding:14px 14px 14px 0; resize:vertical; line-height:1.55; }
    .campaign-input-wrap input::placeholder,.campaign-input-wrap textarea::placeholder { color:#a4abb5; font-weight:500; }
    .campaign-custom-select { position:relative; z-index:20; }
    .campaign-assignee-picker { display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:start; gap:9px; }
    .campaign-recommend-button { min-height:48px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:0 14px; border:1px solid #0f7480; border-radius:12px; background:#117e8c; color:#fff; font-size:.66rem; font-weight:900; cursor:pointer; box-shadow:0 5px 12px rgba(17,126,140,.18); transition:.18s; }
    .campaign-recommend-button:hover { transform:translateY(-1px); background:#0d6973; box-shadow:0 8px 16px rgba(17,126,140,.23); }
    .campaign-recommend-button:disabled { opacity:.72; cursor:wait; transform:none; }
    .campaign-custom-select.is-recommended .campaign-custom-trigger { border-color:#117e8c; box-shadow:0 0 0 4px rgba(17,126,140,.14); }
    .campaign-recommendation { margin-top:9px; padding:10px 12px; border:1px solid #b9dfe2; border-radius:10px; background:#f0fafb; color:#315e63; }
    .campaign-recommendation strong,.campaign-recommendation span { display:block; }
    .campaign-recommendation strong { color:#0f6872; font-size:.65rem; font-weight:900; }
    .campaign-recommendation span { margin-top:3px; font-size:.58rem; font-weight:650; line-height:1.45; }
    .campaign-recommendation.is-error { border-color:#f3c4c4; background:#fff0f0; color:#a72d2d; }
    .campaign-custom-select.is-open { z-index:80; }
    .campaign-native-select { position:absolute!important; width:1px!important; height:1px!important; overflow:hidden!important; opacity:0!important; pointer-events:none!important; }
    .campaign-custom-trigger { position:relative; width:100%; min-height:48px; display:flex; align-items:center; gap:11px; padding:0 42px 0 14px; border:1px solid #d7dde6; border-radius:12px; background:#fff; color:#89929f; text-align:left; box-shadow:0 2px 5px rgba(15,23,42,.04); cursor:pointer; transition:.18s; }
    .campaign-custom-trigger>i:first-child { width:18px; color:#7d8795; text-align:center; font-size:.83rem; }
    .campaign-custom-trigger>span { min-width:0; overflow:hidden; flex:1; font-size:.72rem; font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
    .campaign-custom-trigger>i:last-child { position:absolute; right:15px; color:#929aa6; font-size:.62rem; transition:transform .18s; }
    .campaign-custom-trigger.has-value { color:#303846; }
    .campaign-custom-select.is-open .campaign-custom-trigger,.campaign-custom-trigger:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.11),0 3px 8px rgba(15,23,42,.05); outline:0; }
    .campaign-custom-select.is-open .campaign-custom-trigger>i:last-child { transform:rotate(180deg); }
    .campaign-custom-select.is-highlighted .campaign-custom-trigger { border-color:#8b5cf6; box-shadow:0 0 0 4px rgba(139,92,246,.15); }
    .campaign-custom-select.is-invalid .campaign-custom-trigger { border-color:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.11); }
    .campaign-custom-menu { position:absolute; top:calc(100% + 8px); right:0; left:0; display:none; max-height:245px; overflow-y:auto; padding:7px; border:1px solid #d8dee8; border-radius:13px; background:#fff; box-shadow:0 18px 42px rgba(15,23,42,.18); }
    .campaign-custom-select.is-open .campaign-custom-menu { display:grid; gap:3px; animation:campaignDropdownIn .16s ease both; }
    .campaign-custom-menu>button { width:100%; min-height:50px; display:grid; grid-template-columns:34px minmax(0,1fr) 20px; align-items:center; gap:10px; padding:7px 9px; border:0; border-radius:9px; background:transparent; color:#46505d; text-align:left; cursor:pointer; transition:.15s; }
    .campaign-custom-menu>button:hover,.campaign-custom-menu>button.is-selected { background:#f0f2ff; color:#3730a3; }
    .campaign-option-avatar,.campaign-option-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:9px; background:#eef2ff; color:#4f46e5; font-size:.68rem; font-weight:900; }
    .campaign-custom-menu>button.is-selected .campaign-option-avatar,.campaign-custom-menu>button.is-selected .campaign-option-icon { background:#4f46e5; color:#fff; }
    .campaign-custom-menu>button>span:nth-child(2) { min-width:0; }
    .campaign-custom-menu strong,.campaign-custom-menu small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .campaign-custom-menu strong { font-size:.69rem; font-weight:850; }
    .campaign-custom-menu small { margin-top:2px; color:#929aa6; font-size:.56rem; font-weight:600; }
    .campaign-option-check { visibility:hidden; color:#4f46e5; font-size:.68rem; }
    .campaign-custom-menu>button.is-selected .campaign-option-check { visibility:visible; }
    @keyframes campaignDropdownIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:none; } }
    .campaign-create-footer { margin-top:20px; padding-top:17px; display:flex; align-items:center; justify-content:space-between; gap:18px; border-top:1px solid #e6e9ee; }
    .campaign-create-footer p { margin:0; display:flex; align-items:center; gap:7px; color:#8a93a0; font-size:.6rem; font-weight:650; }
    .campaign-create-footer p i { color:#6366f1; }
    .campaign-create-footer>div { display:flex; align-items:center; gap:9px; }
    .campaign-create-cancel,.campaign-create-submit { min-height:40px; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:0 16px; border-radius:10px; font-size:.68rem; font-weight:900; cursor:pointer; transition:.18s; }
    .campaign-create-cancel { border:1px solid #d7dde6; background:#fff; color:#66707e; }
    .campaign-create-cancel:hover { background:#f3f4f6; color:#374151; }
    .campaign-create-submit { border:1px solid #4f46e5; background:#4f46e5; color:#fff; box-shadow:0 7px 15px rgba(79,70,229,.2); }
    .campaign-create-submit:hover { transform:translateY(-1px); background:#4338ca; box-shadow:0 10px 20px rgba(79,70,229,.25); }
    .campaign-section>[id^='paginacion-'] { margin:0 20px 20px!important; padding:13px 14px; border:1px solid #e5e7eb; border-radius:12px; background:#f8fafc; }
    .campaign-section>[id^='paginacion-'] button { min-height:34px; border-radius:8px; font-size:.7rem; font-weight:800; }
    .campaign-section>.text-center { margin:0 20px 20px; border:1px dashed #d8ddd4; border-radius:14px; background:#fafbf9; }
    #mensaje-modal>div:last-child>div { border:1px solid #dde3ea; border-radius:18px; box-shadow:0 28px 75px rgba(15,23,42,.28); }
    @media(max-width:980px) { .campaigns-hero-content>div { padding-right:0; } .campaigns-top-actions { position:static; order:2; justify-content:center; margin:14px 24px 0; } .campaign-top-action { border-color:#dce4f3; background:#f4f7fd; color:#4f46e5; } .campaign-top-action.is-active { background:#4f46e5; color:#fff; } .campaign-alert{order:3}.campaign-kpis{order:4;grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-section{order:5} }
    @media(max-width:640px) { .campaigns-hero{min-height:205px}.campaigns-hero-content{min-height:205px;padding:28px 20px}.campaigns-hero-content .flex.items-center.gap-4{align-items:center}.campaigns-top-actions{display:grid;grid-template-columns:1fr;margin-right:12px;margin-left:12px}.campaign-top-action{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.campaign-top-action.is-active{background:#4f46e5;color:#fff}.campaign-alert{width:calc(100% - 24px)}.campaign-kpis{grid-template-columns:1fr;margin-right:12px;margin-left:12px}.campaign-section{margin-right:12px!important;margin-left:12px!important}.campaign-section>.flex.items-center.mb-6{padding:18px 16px}.campaign-filter-bar{margin-right:12px!important;margin-left:12px!important;padding:10px}.campaign-filter-grid{grid-template-columns:1fr!important}.campaign-filter-bar:not(.campaign-filter-grid)::before{top:26px;left:27px}.campaign-table-wrap{margin-right:12px;margin-left:12px}.campaign-section>[id^='paginacion-']{margin-right:12px!important;margin-left:12px!important}.campaign-table tr[data-form-for]>td{padding:10px!important}.campaign-create-header{padding:16px}.campaign-create-heading{align-items:flex-start}.campaign-create-heading p{display:none}.campaign-create-form{padding:16px}.campaign-create-context,.campaign-create-fields{grid-template-columns:1fr}.campaign-create-field-full{grid-column:1}.campaign-create-field>label{align-items:flex-start;flex-direction:column;gap:2px}.campaign-create-field>label small{text-align:left}.campaign-assignee-picker{grid-template-columns:1fr}.campaign-recommend-button{width:100%}.campaign-create-footer{align-items:stretch;flex-direction:column}.campaign-create-footer>div{display:grid;grid-template-columns:1fr 1fr}.campaign-create-cancel,.campaign-create-submit{width:100%} }
</style>
@endsection


