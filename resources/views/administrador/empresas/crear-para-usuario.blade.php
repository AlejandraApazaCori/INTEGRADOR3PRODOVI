@extends('layouts.app')

@section('title', 'Crear Empresa y Cuestionario')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header de navegación -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Crear Empresa para {{ $user->name }}</h1>
            <a href="{{ route('administrador.campañas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>

        <form action="{{ route('administrador.empresas.guardar-con-cuestionario') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="usuario_id" value="{{ $user->id }}">

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Información Básica de la Empresa</h2>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Nombre de la Empresa <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_empresa" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                   placeholder="Ej: Tech Solutions S.A.">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-700">Tipo de Empresa <span class="text-red-500">*</span></label>
                            <input type="text" name="tipo_empresa" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                   placeholder="Ej: Tecnología / Servicios">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Descripción General</label>
                        <textarea name="descripcion" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                                  placeholder="Breve descripción de la empresa..."></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Cuestionario Estratégico</h2>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-600 mb-8 bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                        Por favor, completa la mayor cantidad de información posible para ayudar a generar un plan de marketing preciso.
                    </p>

                    @foreach($temas as $tema)
                        <div class="mb-10">
                            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3 text-sm">
                                    {{ $loop->iteration }}
                                </span>
                                {{ $tema->nombre_tema }}
                            </h3>
                            
                            <div class="space-y-8">
                                @foreach($tema->preguntas as $pregunta)
                                    <div class="space-y-3">
                                        <label class="block text-gray-800 font-semibold">
                                            {{ $pregunta->pregunta }}
                                            @if($pregunta->requerido)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        
                                        @if($pregunta->ayuda)
                                            <p class="text-xs text-gray-500">{{ $pregunta->ayuda }}</p>
                                        @endif
                                        
                                        @if($pregunta->tipo_respuesta === 'texto_largo')
                                            <textarea name="respuesta_{{ $pregunta->id }}" rows="4" {{ $pregunta->requerido ? 'required' : '' }}
                                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                      placeholder="Escribe tu respuesta aquí..."></textarea>
                                        @else
                                            <input type="text" name="respuesta_{{ $pregunta->id }}" {{ $pregunta->requerido ? 'required' : '' }}
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                                   placeholder="Escribe tu respuesta aquí...">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-10 border-gray-100">
                        @endif
                    @endforeach

                    <div class="flex justify-end pt-6 border-t border-gray-100">
                        <button type="submit" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-1">
                            Guardar Empresa y Cuestionario
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    input:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
</style>
@endsection
