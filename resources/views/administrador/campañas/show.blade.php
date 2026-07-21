@extends('layouts.app')

@section('title', 'Detalles de Campaña')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Header con navegación -->
        <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('administrador.campañas.index') }}" class="hover:text-blue-600 transition-colors">Campañas</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 font-medium">{{ $campania->nombre }}</span>
            </nav>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $campania->nombre }}</h1>
                        <p class="text-gray-600 mt-1">Gestión de campaña publicitaria</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 rounded-full text-sm font-semibold border-2
                        @if($campania->estado == 'activa') 
                            bg-emerald-50 text-emerald-700 border-emerald-200
                        @elseif($campania->estado == 'pausada') 
                            bg-amber-50 text-amber-700 border-amber-200
                        @else 
                            bg-gray-50 text-gray-700 border-gray-200 
                        @endif">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full 
                                @if($campania->estado == 'activa') bg-emerald-400
                                @elseif($campania->estado == 'pausada') bg-amber-400
                                @else bg-gray-400 @endif">
                            </div>
                            <span>{{ ucfirst($campania->estado) }}</span>
                        </div>
                    </span>
                </div>
            </div>
        </div>

        <!-- Cards principales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Información básica -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Información General
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="group">
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Nombre de la Campaña</label>
                                <p class="text-lg font-semibold text-gray-900 mt-1 group-hover:text-blue-600 transition-colors">{{ $campania->nombre }}</p>
                            </div>
                            <div class="group">
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Descripción</label>
                                <p class="text-gray-700 mt-1 leading-relaxed">{{ $campania->descripcion ?? 'Sin descripción disponible' }}</p>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-4 border border-gray-100">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Cronograma
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Inicio</span>
                                    <span class="font-semibold text-green-600">{{ \Carbon\Carbon::parse($campania->fecha_inicio)->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Finalización</span>
                                    <span class="font-semibold text-red-600">{{ \Carbon\Carbon::parse($campania->fecha_fin)->format('d M Y') }}</span>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Duración</span>
                                        <span class="font-medium text-blue-600">
                                            {{ \Carbon\Carbon::parse($campania->fecha_inicio)->diffInDays(\Carbon\Carbon::parse($campania->fecha_fin)) }} días
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="space-y-6">
                <!-- Cliente -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-teal-600 px-4 py-3">
                        <h3 class="font-semibold text-white flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Cliente
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-teal-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">{{ substr($campania->cliente->name, 0, 2) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $campania->cliente->name }}</p>
                                <p class="text-sm text-gray-600">{{ $campania->cliente->email }}</p>
                            </div>
                        </div>

                        <!-- Lógica de Empresa -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            @php
                                $empresa = $campania->cliente->empresas->first();
                            @endphp
                            
                            @if($empresa)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="text-gray-700 font-medium">Empresa Registrada</span>
                                    </div>
                                    <a href="{{ route('administrador.empresas.show', $empresa->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-600 text-xs font-semibold rounded-lg hover:bg-emerald-100 transition-colors">
                                        Ver Empresa
                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            @else
                                <div class="flex items-center text-sm text-amber-600 bg-amber-50 p-2.5 rounded-lg border border-amber-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span>Cliente sin empresa registrada</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Equipo -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-4 py-3">
                        <h3 class="font-semibold text-white flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Equipo de Trabajo
                        </h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Creador</p>
                                <p class="font-medium text-gray-900">{{ $campania->creador->name }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Community Manager</p>
                                <p class="font-medium text-gray-900">{{ $campania->communityManager->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class=" mb-8 mt-8 ml-auto w-full lg:w-[calc(33.333333%-0.5rem)] bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white/30 overflow-hidden hover:shadow-3xl transition-all duration-300">
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <h2 class="text-2xl font-bold">Campaña Activa</h2>
                <p class="text-indigo-100 mt-1">Resumen rápido de la campaña del usuario</p>
            </div>

            <div class="p-8">
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-5">
                    <p class="text-sm font-medium text-gray-500">Campaña actual</p>
                    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $campania->nombre }}</h3>
                            <p class="mt-1 text-sm text-gray-600">Estado: {{ ucfirst($campania->estado) }}</p>
                        </div>
                        <a href="{{ route('administrador.usuarios.analiticas-campania', $campania->cliente->id) }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-3 text-sm font-medium text-white shadow-lg transition-all duration-300 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl">
                            Ver analíticas de campaña
                        </a>
                    </div>
                </div>
            </div>
        </div>

<!-- Botones de acción -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="font-medium">Acciones disponibles</span>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('administrador.campañas.index') }}" 
                       class="group flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    
                    <a href="{{ route('administrador.campañas.calendario', $campania->id) }}" 
                       class="group flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ver Calendario
                    </a>
                    
                    <a href="{{ route('administrador.campañas.edit', $campania->id) }}" 
                       class="group flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Campaña
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('administrador.tareas.index')
@endsection
