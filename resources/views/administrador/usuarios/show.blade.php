@extends('layouts.app')

@section('title', 'Detalles del Usuario')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/20">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-700 to-purple-700 bg-clip-text text-transparent mb-2">Detalles del Usuario</h1>
                    <p class="text-gray-600">Información completa del usuario seleccionado</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('administrador.usuarios.edit', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Usuario
                    </a>
                    <a href="{{ route('administrador.usuarios.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la lista
                    </a>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna izquierda - Datos del cliente -->
            <div class="lg:col-span-1">
                <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white/30 overflow-hidden hover:shadow-3xl transition-all duration-300">
                    <!-- Header -->
                    <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <h2 class="text-2xl font-bold">Datos del Usuario</h2>
                        <p class="text-indigo-100 mt-1">Información de la cuenta</p>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="p-8">
                        <div class="flex flex-col items-center mb-6">
                            <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-xl mb-4 transform hover:scale-105 transition-transform duration-300">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-gray-600">{{ $user->email }}</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex-shrink-0 pt-1">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ $user->phone ?? 'No registrado' }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex-shrink-0 pt-1">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Rol</p>
                                    <p class="text-sm text-gray-900 font-medium">
                                        @foreach($user->roles as $role)
                                            {{ $role->nombre_rol }}
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex-shrink-0 pt-1">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Miembro desde</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ $user->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <a href="{{ route('administrador.usuarios.edit', $user->id) }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar perfil
                            </a>
                        </div>
                    </div>
                </div>
            
                <div class="mt-8 bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white/30 overflow-hidden hover:shadow-3xl transition-all duration-300">
                    <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <h2 class="text-2xl font-bold">Campaña Activa</h2>
                        <p class="text-indigo-100 mt-1">Resumen rápido de la campaña del usuario</p>
                    </div>

                    <div class="p-8">
                        @if($campaniaActual)
                            <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-5">
                                <p class="text-sm font-medium text-gray-500">Campaña actual</p>
                                <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ $campaniaActual->nombre }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">Estado: {{ ucfirst($campaniaActual->estado) }}</p>
                                    </div>
                                    <a href="{{ route('administrador.usuarios.analiticas-campania', $user->id) }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-3 text-sm font-medium text-white shadow-lg transition-all duration-300 hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl">
                                        Ver analíticas de campaña
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-center">
                                <p class="text-base font-semibold text-gray-700">Aún no existe una campaña activa.</p>
                                <p class="mt-2 text-sm text-gray-500">Cuando el usuario tenga una campaña activa, aparecerá aquí junto al acceso a sus analíticas.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            <!-- Columna derecha - Plan contratado -->
            <div class="lg:col-span-2">
                <!-- Plan Contratado Section -->
                <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white/30 overflow-hidden hover:shadow-3xl transition-all duration-300">
                    <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <h2 class="text-2xl font-bold">Plan Contratado</h2>
                        <p class="text-indigo-100 mt-1">Detalles de la suscripción actual</p>
                    </div>
                    
                    <div class="p-8">
                        @if($suscripcionActiva)
                            <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-3xl border border-indigo-200/50">
                                <div class="p-8">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-8">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-4">
                                                <div class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg">
                                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <!-- CORRECCIÓN: Asegurarse de que el plan y su nombre existen antes de acceder -->
                                                    <h3 class="text-2xl font-bold text-gray-900">{{ $suscripcionActiva->plan?->nombre ?? 'Plan sin nombre' }}</h3>
                                                    <p class="text-gray-600">{{ $suscripcionActiva->plan?->descripcion ?? 'Plan de marketing digital' }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span class="text-sm text-gray-600">Ciclo actual:</span>
                                                        <span class="text-sm font-semibold text-gray-800">{{ $suscripcionActiva->fecha_inicio->format('d/m/Y') }} - {{ $suscripcionActiva->fecha_fin->format('d/m/Y') }}</span>
                                                    </div>
                                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border bg-green-100 text-green-800 border-green-200">
                                                        <div class="w-2 h-2 rounded-full bg-current mr-2"></div>
                                                        {{ $suscripcionActiva->estado }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-6 lg:mt-0 lg:ml-8">
                                            <button id="ver-detalles-btn" class="group relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                                                <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                                <div class="relative flex items-center space-x-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Ver detalles</span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($suscripcionActiva->plan && $suscripcionActiva->plan->caracteristicas && $suscripcionActiva->plan->caracteristicas->count() > 0)
                                        <div class="mt-8">
                                            <h4 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                Características principales
                                            </h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                @foreach($suscripcionActiva->plan->caracteristicas as $index => $caracteristica)
                                                    <?php
                                                    $colors = [
                                                        'from-blue-500 to-indigo-600',
                                                        'from-purple-500 to-pink-600', 
                                                        'from-green-500 to-emerald-600',
                                                        'from-orange-500 to-red-600',
                                                        'from-cyan-500 to-blue-600'
                                                    ];
                                                    $colorClass = $colors[$index % count($colors)];
                                                    ?>
                                                    <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br {{ $colorClass }} opacity-10 rounded-full -translate-y-4 translate-x-4 group-hover:scale-110 transition-transform duration-300"></div>
                                                        <div class="relative">
                                                            <div class="flex items-center justify-between mb-3">
                                                                <div class="p-2 bg-gradient-to-br {{ $colorClass }} rounded-xl shadow-lg">
                                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <h4 class="font-semibold text-gray-700 text-sm mb-2">{{ $caracteristica->nombre }}</h4>
                                                            <p class="text-3xl font-bold bg-gradient-to-r {{ $colorClass }} bg-clip-text text-transparent">
                                                                {{ $caracteristica->pivot->cantidad }}{{ $caracteristica->pivot->unidad ?? '' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-3xl border border-gray-200/50">
                                <div class="text-center py-16">
                                    <div class="relative inline-block">
                                        <div class="w-20 h-20 bg-gradient-to-r from-gray-400 to-gray-600 rounded-3xl mx-auto flex items-center justify-center mb-6">
                                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin plan activo</h3>
                                    <p class="text-gray-600 mb-8">Este usuario no tiene un plan activo en este momento.</p>
                                    <a href="{{ route('administrador.usuarios.edit', $user->id) }}" class="group relative overflow-hidden inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                        <div class="relative flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Asignar un plan
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ======================================== -->
        <!-- SECCIÓN PARA MOSTRAR EMPRESAS -->
        <!-- ======================================== -->
        <div class="mt-12">
            <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl border border-white/30 overflow-hidden hover:shadow-3xl transition-all duration-300">
                <!-- Header -->
                <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                    <h2 class="text-2xl font-bold flex items-center">
                        <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Empresas del Usuario
                    </h2>
                    <p class="text-indigo-100 mt-1">Gestiona la información de las empresas de este usuario</p>
                </div>

                <div class="p-8">
                    @if($empresas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($empresas as $empresa)
                                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                    <div class="p-6">
                                        <div class="flex items-center space-x-4 mb-4">
                                            @if($empresa->logo)
                                                <div class="w-16 h-16 bg-white rounded-xl p-2 shadow-lg">
                                                    <img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}" class="w-full h-full object-contain">
                                                </div>
                                            @else
                                                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                                                    {{ substr($empresa->nombre_empresa, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-900">{{ $empresa->nombre_empresa }}</h3>
                                                <p class="text-sm text-gray-600">{{ $empresa->tipo_empresa }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($empresa->descripcion)
                                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $empresa->descripcion }}</p>
                                        @else
                                            <p class="text-gray-400 text-sm mb-4 italic">Sin descripción</p>
                                        @endif
                                        

                                    </div>
                                    <!-- En la sección de empresas -->
                                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                                        <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                            Ver detalles
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="relative inline-block">
                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl mx-auto flex items-center justify-center mb-6 shadow-lg">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 rounded-3xl bg-gray-300 opacity-10 animate-pulse"></div>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Este usuario no tiene empresas registradas</h3>
                            <p class="text-gray-600 mb-8 max-w-md mx-auto">Aún no hay empresas asociadas a este usuario.</p>
                            <a href="{{ route('administrador.empresas.crear-con-cuestionario', $user->id) }}" class="group relative overflow-hidden inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <div class="relative flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Crear empresa
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles del plan -->
@if($suscripcionActiva)
<div id="plan-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fondo del modal con blur -->
        <div class="fixed inset-0 transition-opacity backdrop-blur-sm" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/60"></div>
        </div>
        
        <!-- Contenido del modal -->
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
            
            <!-- Header del modal -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center" id="modal-plan-title">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Detalles del Plan
                    </h3>
                    <button type="button" id="close-modal" class="text-white/80 hover:text-white transition-colors duration-200 p-1 rounded-full hover:bg-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="bg-white px-6 py-6">
                
                <!-- Información del ciclo -->
                <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200/50">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ciclo de facturación
                    </h4>
                    <p class="text-gray-700 font-medium">{{ $suscripcionActiva->fecha_inicio->format('d/m/Y') }} - {{ $suscripcionActiva->fecha_fin->format('d/m/Y') }}</p>
                    <p class="text-sm mt-1">
                        Estado: <span class="text-green-600 font-semibold">{{ $suscripcionActiva->estado }}</span>
                    </p>
                </div>
                
                <!-- Descripción -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descripción
                    </h4>
                    <p class="text-gray-600 leading-relaxed">{{ $suscripcionActiva->plan?->descripcion ?? 'No hay descripción disponible' }}</p>
                </div>
                
                <!-- Características -->
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Características incluidas
                    </h4>
                    <div class="space-y-2" id="modal-plan-features">
                        @if($suscripcionActiva->plan && $suscripcionActiva->plan->caracteristicas && $suscripcionActiva->plan->caracteristicas->count() > 0)
                            @foreach($suscripcionActiva->plan->caracteristicas as $caracteristica)
                                <div class="flex items-center p-3 bg-gradient-to-r from-gray-50 to-indigo-50 rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                    @if($caracteristica->pivot->es_destacado)
                                        <svg class="w-5 h-5 text-amber-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-indigo-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-800">{{ $caracteristica->nombre }}</span>
                                        <span class="text-gray-600">: {{ $caracteristica->pivot->cantidad }}{{ $caracteristica->pivot->unidad ?? '' }}</span>
                                        @if($caracteristica->pivot->frecuencia)
                                            <span class="text-sm text-gray-500 block">{{ $caracteristica->pivot->frecuencia }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center justify-center p-6 text-gray-500">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                No se encontraron características
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer del modal -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" id="close-modal-footer" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($suscripcionActiva)
    const modal = document.getElementById('plan-modal');
    const verDetallesBtn = document.getElementById('ver-detalles-btn');
    const closeModalBtn = document.getElementById('close-modal');
    const closeModalFooterBtn = document.getElementById('close-modal-footer');
    
    // Event listeners
    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    verDetallesBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    closeModalFooterBtn.addEventListener('click', closeModal);
    
    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
    
    // Cerrar al hacer clic fuera del modal
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    @endif
});
</script>
@endpush
@endsection
