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
            @if(request('continuar_campania'))
                <input type="hidden" name="continuar_campania" value="{{ request('continuar_campania') }}">
            @endif

            <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                <label for="suscripcion_id" class="mb-2 block text-sm font-semibold text-indigo-950">Plan pagado que se asociará a la empresa</label>
                @if($suscripcionesDisponibles->isNotEmpty())
                    <select id="suscripcion_id" name="suscripcion_id" required
                            class="w-full rounded-xl border border-indigo-200 bg-white px-4 py-3 text-gray-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                        @foreach($suscripcionesDisponibles as $suscripcion)
                            <option value="{{ $suscripcion->id }}" @selected((int) $suscripcionSeleccionadaId === (int) $suscripcion->id)>
                                {{ $suscripcion->plan?->nombre ?? 'Plan' }} · Suscripción #{{ $suscripcion->id }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-indigo-700">Esta relación se conservará para la campaña, las tareas y los recursos contratados.</p>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Este cliente no tiene una suscripción pagada disponible para asociar a una nueva empresa.
                    </div>
                @endif
                @error('suscripcion_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

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
                                                      placeholder="Escribe tu respuesta aquí...">{{ old("respuesta_{$pregunta->id}") }}</textarea>
                                        @elseif($pregunta->tipo_respuesta === 'opcion_multiple')
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                @foreach($pregunta->opciones ?? [] as $opcion)
                                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 hover:border-indigo-300 hover:bg-indigo-50">
                                                        <input type="radio" name="respuesta_{{ $pregunta->id }}" value="{{ $opcion }}" @checked(old("respuesta_{$pregunta->id}") === $opcion) {{ $pregunta->requerido ? 'required' : '' }}>
                                                        <span class="text-sm text-gray-700">{{ $opcion }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                                <input type="text" name="respuesta_{{ $pregunta->id }}_otro" value="{{ old("respuesta_{$pregunta->id}_otro") }}" class="mt-3 w-full rounded-xl border border-gray-300 px-4 py-3" placeholder="Especifica otra opción">
                                            @endif
                                        @elseif($pregunta->tipo_respuesta === 'checkbox')
                                            @php($marcadas = old("respuesta_{$pregunta->id}", []))
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                @foreach($pregunta->opciones ?? [] as $opcion)
                                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 hover:border-indigo-300 hover:bg-indigo-50">
                                                        <input type="checkbox" name="respuesta_{{ $pregunta->id }}[]" value="{{ $opcion }}" @checked(in_array($opcion, (array) $marcadas, true))>
                                                        <span class="text-sm text-gray-700">{{ $opcion }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @if(in_array('Otro', $pregunta->opciones ?? [], true))
                                                <input type="text" name="respuesta_{{ $pregunta->id }}_otro" value="{{ old("respuesta_{$pregunta->id}_otro") }}" class="mt-3 w-full rounded-xl border border-gray-300 px-4 py-3" placeholder="Especifica otra opción">
                                            @endif
                                        @else
                                            <input type="{{ $pregunta->tipo_respuesta === 'numero' ? 'number' : 'text' }}" name="respuesta_{{ $pregunta->id }}" value="{{ old("respuesta_{$pregunta->id}") }}" {{ $pregunta->requerido ? 'required' : '' }}
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
                        <button type="submit" @disabled($suscripcionesDisponibles->isEmpty()) class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-1 disabled:cursor-not-allowed disabled:opacity-50">
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
