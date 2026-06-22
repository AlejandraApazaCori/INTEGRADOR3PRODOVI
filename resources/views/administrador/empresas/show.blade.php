@extends('layouts.app')

@section('title', 'Detalles de la Empresa')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header de navegación -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Detalles de la Empresa</h1>
            <button onclick="goBack()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Atrás
            </button>
        </div>

        <!-- Tarjeta principal de la empresa -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
                <div class="flex items-center space-x-6">
                    @if($empresa->logo)
                        <div class="w-24 h-24 bg-white rounded-xl p-2 shadow-lg">
                            <img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-24 h-24 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $empresa->nombre_empresa }}</h1>
                        <p class="text-indigo-100 text-lg mt-1">{{ $empresa->tipo_empresa }}</p>
                        <p class="text-indigo-200 text-sm mt-1">Propietario: {{ $empresa->usuario->name }} ({{ $empresa->usuario->email }})</p>
                    </div>
                </div>
            </div>
            
            <!-- Contenido -->
            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Información Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Información de la Empresa</h2>
                            @if($empresa->descripcion)
                                <p class="text-gray-600 leading-relaxed">{{ $empresa->descripcion }}</p>
                            @else
                                <p class="text-gray-400 italic">No se ha proporcionado una descripción.</p>
                            @endif
                        </div>

                        <!-- Estado del Cuestionario -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Estado del Cuestionario</h2>
                            @if($empresa->cuestionario_completado)
                                <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-xl">
                                    <svg class="w-8 h-8 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-green-800">Cuestionario Completado</p>
                                        <p class="text-sm text-green-600">Gracias por proporcionar toda la información necesaria.</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center p-4 bg-amber-50 border border-amber-200 rounded-xl">
                                    <svg class="w-8 h-8 text-amber-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-amber-800">Cuestionario Pendiente</p>
                                        <p class="text-sm text-amber-600">Es importante que completes el cuestionario para poder empezar.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Sección para mostrar el resumen ejecutivo --}}
                        @if($empresa->resumen_ejecutivo)
                            <div class="mt-6 p-6 bg-blue-50 border border-blue-200 rounded-xl">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-2xl font-bold text-blue-900">Resumen Ejecutivo Generado</h2>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('administrador.empresas.editar-resumen', $empresa->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Editar
                                        </a>
                                        <a href="{{ route('administrador.empresas.reporte', $empresa->id) }}" class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Ver Reporte
                                        </a>
                                        <a href="{{ route('administrador.empresas.reporte.pdf', $empresa->id) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Descargar PDF
                                        </a>
                                        <form action="{{ route('administrador.empresas.eliminar-resumen', $empresa->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este Resumen Ejecutivo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-800 text-white text-sm rounded-lg hover:bg-red-900 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="prose prose-sm max-w-none text-gray-700">
                                    <p>{!! nl2br(e($empresa->resumen_ejecutivo)) !!}</p>
                                </div>
                            </div>
                        @endif

                        {{-- NUEVA SECCIÓN: Mostrar Planes de Marketing Existentes --}}
                        @if($empresa->planesMarketing->isNotEmpty())
                            <div class="mt-6 p-6 bg-purple-50 border border-purple-200 rounded-xl">
                                <h2 class="text-2xl font-bold text-purple-900 mb-4">Planes de Marketing Generados</h2>
                                <div class="space-y-3">
                                    @foreach($empresa->planesMarketing as $plan)
                                        <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow-sm border border-purple-100">
                                            <div>
                                                <p class="font-semibold text-gray-900">Plan</p>
                                                <p class="text-sm text-gray-600">Creado el: {{ $plan->created_at->format('d/m/Y H:i') }}</p>
                                                <p class="text-sm text-gray-600">Basado en suscripción: {{ $plan->suscripcion->plan->nombre }} (Estado: {{ $plan->suscripcion->estado }})</p>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('administrador.empresas.planes-marketing.show', $plan->id) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Ver Plan
                                                </a>
                                                <form action="{{ route('administrador.empresas.planes-marketing.destroy', $plan->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este Plan de Marketing?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- Acciones -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 rounded-xl p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h3>
                            <div class="space-y-3">
                                <a href="{{ route('administrador.usuarios.edit', $empresa->usuario_id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Editar Usuario
                                </a>
                                
                                <a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                    @if($empresa->cuestionario_completado)
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Ver Cuestionario
                                    @else
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Ver Cuestionario
                                    @endif
                                </a>

                                {{-- Botón para generar el resumen (SIEMPRE VISIBLE PARA ADMIN) --}}
                                @if($empresa->cuestionario_completado && !$empresa->resumen_ejecutivo)
                                    <button id="generate-summary-btn" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Generar Resumen Ejecutivo
                                    </button>
                                @endif

                                {{-- MODIFICADO: Botón para crear plan de marketing --}}
                                @php
                                    // Lógica para determinar si se puede crear un nuevo plan
                                    $puedeCrearPlan = false;
                                    $motivoDeshabilitado = '';

                                    if ($empresa->cuestionario_completado && $empresa->resumen_ejecutivo) {
                                        // Buscar la suscripción más reciente que NO tenga plan de marketing.
                                        $suscripcionActivaSinPlan = $empresa->usuario->suscripciones()
                                            ->whereDoesntHave('planMarketing')
                                            ->latest()
                                            ->first();

                                        if ($suscripcionActivaSinPlan) {
                                            $puedeCrearPlan = true;
                                        } else {
                                            $motivoDeshabilitado = 'No hay una suscripción sin un plan asociado.';
                                        }
                                    } else {
                                        $motivoDeshabilitado = 'El cuestionario y/o el resumen ejecutivo deben estar completados.';
                                    }
                                @endphp

                                @if($puedeCrearPlan)
                                    <a href="{{ route('administrador.empresas.crear-plan', $empresa->id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Crear Plan de Marketing
                                    </a>
                                @else
                                    <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed" title="{{ $motivoDeshabilitado }}">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Crear Plan de Marketing
                                    </button>
                                @endif

                                <a href="{{ route('administrador.usuarios.view', $empresa->usuario_id) }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Volver al Perfil
                                </a>

                                <form action="{{ route('administrador.empresas.destroy', $empresa->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta empresa? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Eliminar Empresa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script para manejar la llamada a la API --}}
@if($empresa->cuestionario_completado && !$empresa->resumen_ejecutivo)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-summary-btn');

    if (generateBtn) {
        generateBtn.addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generando...
            `;

            try {
                const response = await fetch(`/empresas/{{ $empresa->id }}/generar-resumen`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor.');
                }

                const data = await response.json();

                if (data.success) {
                    // Recargar la página para mostrar el nuevo resumen
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo generar el resumen.'));
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un error en la petición. Por favor, inténtalo de nuevo.');
            } finally {
                // Restaurar el botón en caso de error
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Generar Resumen Ejecutivo
                `;
            }
        });
    }
});
</script>
@endif
<script>
function goBack() {
    window.history.back();
}
</script>
@endsection