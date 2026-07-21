@extends('layouts.app')

@section('title', 'Cuestionario de Informacion de Empresa (Vista Admin)')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header de navegación -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Cuestionario de la Empresa</h1>
            <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a la Empresa
            </a>
        </div>

        <div class="space-y-4 mb-6">
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold">No se pudo guardar el cuestionario.</p>
                    <ul class="mt-2 list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-white">Cuestionario de Información de Empresa</h1>
                    <div class="text-white/80">
                        {{ $empresa->nombre_empresa }}
                    </div>
                </div>
            </div>
            
            <!-- Información de la empresa -->
            <div class="p-6">
                <div class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $empresa->nombre_empresa }}</h3>
                    <p class="text-gray-600">{{ $empresa->tipo_empresa }}</p>
                    <p class="text-sm text-gray-500 mt-2">Propietario: {{ $empresa->usuario->name }} ({{ $empresa->usuario->email }})</p>
                    @if($empresa->descripcion)
                        <p class="text-gray-600 mt-2">{{ $empresa->descripcion }}</p>
                    @endif
                </div>
                
                <form action="{{ route('administrador.empresas.cuestionario.update', $empresa->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Preguntas del cuestionario -->
                    @foreach($temas as $tema)
                        <div class="mb-12">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3 text-sm">
                                    {{ $loop->iteration }}
                                </span>
                                {{ $tema->nombre_tema }}
                            </h2>
                            
                            @if($tema->descripcion_tema)
                                <p class="text-sm text-gray-500 mb-6 italic">{{ $tema->descripcion_tema }}</p>
                            @endif
                            
                            <div class="space-y-8">
                                @foreach($tema->preguntas as $pregunta)
                                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 shadow-sm">
                                        <label class="block text-gray-800 font-bold mb-3">
                                            {{ $pregunta->pregunta }}
                                            @if($pregunta->requerido)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        
                                        @if($pregunta->ayuda)
                                            <p class="text-xs text-gray-500 mb-4">{{ $pregunta->ayuda }}</p>
                                        @endif
                                        
                                        @if($pregunta->tipo_respuesta === 'texto_largo')
                                            <textarea name="respuesta_{{ $pregunta->id }}" rows="4" {{ $pregunta->requerido ? 'required' : '' }}
                                                      class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
                                                      placeholder="Escribe la respuesta aquí...">{{ $respuestasExistentes[$pregunta->id] ?? '' }}</textarea>
                                        @else
                                            <input type="text" name="respuesta_{{ $pregunta->id }}" value="{{ $respuestasExistentes[$pregunta->id] ?? '' }}" {{ $pregunta->requerido ? 'required' : '' }}
                                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                   placeholder="Escribe la respuesta aquí...">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-12 border-gray-100">
                        @endif
                    @endforeach
                    
                    <!-- Botones de acción -->
                    <div class="flex items-center justify-end space-x-4 mt-12 pt-8 border-t border-gray-100">
                        <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="px-6 py-3 text-gray-600 font-medium hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-1">
                            Guardar Cambios del Cuestionario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection