@extends('layouts.app')

@section('title', 'Editar Tarea')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-edit text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Editar Tarea</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Actualizando: <strong style="color: white;">{{ $tarea->titulo }}</strong></p>
                    </div>
                    <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}" 
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" 
                       style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario mejorado -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #ea9f21;">
                        <i class="fas fa-pen-to-square text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Editar Tarea</h2>
                        <p class="text-gray-600 text-sm mt-0.5">Actualiza los detalles de la tarea asignada</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('administrador.tareas.update', $tarea->id) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <!-- BLOQUE 1: Título y Prioridad -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="titulo" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heading mr-2 text-gray-400"></i>
                            Título <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="titulo" id="titulo" value="{{ $tarea->titulo }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Ej: Diseñar post para redes sociales">
                    </div>

                    <div>
                        <label for="prioridad" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-flag mr-2 text-gray-400"></i>
                            Prioridad <span class="text-red-500">*</span>
                        </label>
                        <select name="prioridad" id="prioridad" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white">
                            <option value="baja" {{ $tarea->prioridad == 'baja' ? 'selected' : '' }}>Baja</option>
                            <option value="media" {{ $tarea->prioridad == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ $tarea->prioridad == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ $tarea->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>
                </div>

                <!-- SEPARADOR NARANJA -->
                <div class="my-8 flex items-center gap-4">
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full" style="background: #ea9f21;">
                        <i class="fas fa-calendar-alt text-white text-xs"></i>
                    </div>
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                </div>

                <!-- BLOQUE 2: Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>
                            Fecha de Inicio <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $tarea->fecha_inicio->format('Y-m-d') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white date-input"
                                min="{{ \Carbon\Carbon::parse($tarea->campania->fecha_inicio)->format('Y-m-d') }}"
                                max="{{ \Carbon\Carbon::parse($tarea->campania->fecha_fin)->format('Y-m-d') }}">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-day text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="fecha_limite" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-2 text-gray-400"></i>
                            Fecha Límite <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" name="fecha_limite" id="fecha_limite" value="{{ $tarea->fecha_limite->format('Y-m-d') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white date-input"
                                min="{{ \Carbon\Carbon::parse($tarea->campania->fecha_inicio)->format('Y-m-d') }}"
                                max="{{ \Carbon\Carbon::parse($tarea->campania->fecha_fin)->format('Y-m-d') }}">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-day text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEPARADOR NARANJA -->
                <div class="my-8 flex items-center gap-4">
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full" style="background: #ea9f21;">
                        <i class="fas fa-user-check text-white text-xs"></i>
                    </div>
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                </div>

                <!-- BLOQUE 3: Asignar a -->
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-check mr-2 text-gray-400"></i>
                            Asignar a <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="filtro_rol" class="block text-xs font-medium text-gray-500 mb-1">
                                    <i class="fas fa-users mr-1"></i> Filtrar por rol
                                </label>
                                <select id="filtro_rol" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white text-sm">
                                    <option value="">Todos los roles</option>
                                    <option value="Super Administrador">Super Administrador</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Community Manager">Community Manager</option>
                                    <option value="Diseñador">Diseñador</option>
                                </select>
                            </div>
                            <div>
                                <label for="filtro_nombre" class="block text-xs font-medium text-gray-500 mb-1">
                                    <i class="fas fa-search mr-1"></i> Buscar por nombre
                                </label>
                                <input type="text" id="filtro_nombre" placeholder="Escribe el nombre..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white text-sm">
                            </div>
                        </div>
                        
                        <select name="asignado_id" id="asignado_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white">
                            @foreach($asignables as $user)
                                @php
                                    $rolesUsuario = $user->roles->pluck('nombre_rol')->filter()->values();
                                    $rolesTexto = $rolesUsuario->isNotEmpty() ? $rolesUsuario->implode(', ') : 'Sin rol';
                                @endphp
                                <option value="{{ $user->id }}" data-rol="{{ $rolesTexto }}"
                                    {{ $tarea->asignado_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $rolesTexto }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Usa los filtros para encontrar rápidamente al responsable
                        </p>
                    </div>
                </div>

                <!-- SEPARADOR NARANJA -->
                <div class="my-8 flex items-center gap-4">
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full" style="background: #ea9f21;">
                        <i class="fas fa-align-left text-white text-xs"></i>
                    </div>
                    <div class="flex-1 h-0.5" style="background: #ea9f21;"></div>
                </div>

                <!-- BLOQUE 4: Descripción -->
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-2 text-gray-400"></i>
                            Descripción <span class="text-red-500">*</span>
                        </label>
                        <textarea name="descripcion" id="descripcion" rows="4" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                            placeholder="Describe detalladamente la tarea, objetivos y entregables esperados...">{{ $tarea->descripcion }}</textarea>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}"
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    
                    <button type="submit"
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                        <i class="fas fa-save mr-2"></i>
                        Actualizar Tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    /* Estilo para inputs de fecha personalizados */
    input[type="date"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: textfield;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .date-input {
        position: relative;
    }

    .date-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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
        .rp-banner a {
            justify-content: center;
            width: 100%;
        }
        .my-8 {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtroRol = document.getElementById('filtro_rol');
    const filtroNombre = document.getElementById('filtro_nombre');
    const selectAsignado = document.getElementById('asignado_id');
    const options = Array.from(selectAsignado.options);
    
    function filtrarUsuarios() {
        const rolSeleccionado = filtroRol.value.toLowerCase();
        const nombreBusqueda = filtroNombre.value.toLowerCase();
        
        options.forEach(option => {
            const rol = option.getAttribute('data-rol').toLowerCase();
            const nombre = option.text.toLowerCase();
            
            const coincideRol = rolSeleccionado === '' || rol.includes(rolSeleccionado);
            const coincideNombre = nombre.includes(nombreBusqueda);
            
            option.style.display = (coincideRol && coincideNombre) ? '' : 'none';
        });
        
        // Seleccionar la primera opción visible
        const visibleOptions = Array.from(selectAsignado.options).filter(opt => opt.style.display !== 'none');
        if (visibleOptions.length > 0) {
            selectAsignado.value = visibleOptions[0].value;
        }
    }
    
    filtroRol.addEventListener('change', filtrarUsuarios);
    filtroNombre.addEventListener('input', filtrarUsuarios);
});
</script>
@endsection