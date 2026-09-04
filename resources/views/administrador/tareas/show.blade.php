@extends('layouts.app')

@section('title', 'Detalles de Tarea')

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
                        <i class="fas fa-tasks text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">{{ $tarea->titulo }}</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Detalles de la tarea asignada</p>
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

        <!-- Contenido principal -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Encabezado con estado -->
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-200" style="background: rgba(255,255,255,0.8);">
                            <i class="fas fa-tag text-indigo-500"></i>
                            ID: #{{ $tarea->id }}
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $tarea->titulo }}</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $estadoConfig = [
                                'entregado' => ['bg' => '#397c91', 'icon' => 'fa-box'],
                                'aprobado' => ['bg' => '#a7b838', 'icon' => 'fa-check-circle'],
                                'publicado' => ['bg' => '#6b4ea0', 'icon' => 'fa-bullhorn'],
                                'en_curso' => ['bg' => '#ea9f21', 'icon' => 'fa-spinner'],
                                'reformular' => ['bg' => '#ed0551', 'icon' => 'fa-times-circle'],
                                'no_iniciado' => ['bg' => '#475569', 'icon' => 'fa-circle'],
                                'pendiente' => ['bg' => '#475569', 'icon' => 'fa-clock'],
                            ];
                            $estado = $tarea->estado ?? 'no_iniciado';
                            $config = $estadoConfig[$estado] ?? $estadoConfig['no_iniciado'];
                        @endphp
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold shadow-sm" style="background: {{ $config['bg'] }};">
                            <i class="fas {{ $config['icon'] }}"></i>
                            {{ str_replace('_', ' ', ucfirst($tarea->estado)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contenido -->
            <div class="p-8 space-y-8">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-align-left text-indigo-500"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Descripción</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100">{{ $tarea->descripcion }}</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <i class="fas fa-calendar-alt text-indigo-500"></i>
                                <h3 class="text-lg font-semibold text-gray-900">Fechas</h3>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Inicio</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $tarea->fecha_inicio->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 pt-2">
                                    <span class="text-sm text-gray-500">Límite</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $tarea->fecha_limite->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-flag text-indigo-500"></i>
                                <h3 class="text-lg font-semibold text-gray-900">Prioridad</h3>
                            </div>
                            @php
                                $prioridadConfig = [
                                    'alta' => ['bg' => '#ed0551', 'icon' => 'fa-arrow-up'],
                                    'urgente' => ['bg' => '#ea9f21', 'icon' => 'fa-exclamation'],
                                    'media' => ['bg' => '#475569', 'icon' => 'fa-minus'],
                                    'baja' => ['bg' => '#a7b838', 'icon' => 'fa-arrow-down'],
                                ];
                                $prioridad = $tarea->prioridad ?? 'media';
                                $pConfig = $prioridadConfig[$prioridad] ?? $prioridadConfig['media'];
                            @endphp
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold shadow-sm" style="background: {{ $pConfig['bg'] }};">
                                <i class="fas {{ $pConfig['icon'] }}"></i>
                                {{ ucfirst($tarea->prioridad) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Responsables -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-users text-indigo-500"></i>
                        Responsables
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                                <i class="fas fa-user-plus mr-1"></i> Creada por
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                    {{ strtoupper(substr($tarea->creador->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $tarea->creador->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $tarea->creador->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                                <i class="fas fa-user-check mr-1"></i> Asignado a
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm" style="background: linear-gradient(135deg, #ea9f21, #e37225);">
                                    {{ strtoupper(substr($tarea->asignado->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $tarea->asignado->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $tarea->asignado->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archivos -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-paperclip text-indigo-500"></i>
                            Archivos adjuntos
                        </h3>
                        <a href="{{ route('administrador.tareas.archivos.create', $tarea->id) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold text-sm shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                            <i class="fas fa-upload"></i>
                            Subir archivo
                        </a>
                    </div>

                    @if($tarea->archivos->count() > 0)
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($tarea->archivos as $archivo)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-white hover:border-indigo-200 transition-all duration-200">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center text-lg" style="background: #e0e7ff;">
                                    @if(in_array($archivo->extension, ['jpg', 'jpeg', 'png', 'gif']))
                                        <i class="fas fa-image text-indigo-500"></i>
                                    @elseif(in_array($archivo->extension, ['pdf']))
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                    @elseif(in_array($archivo->extension, ['doc', 'docx']))
                                        <i class="fas fa-file-word text-blue-500"></i>
                                    @elseif(in_array($archivo->extension, ['xls', 'xlsx']))
                                        <i class="fas fa-file-excel text-green-500"></i>
                                    @elseif(in_array($archivo->extension, ['mp4', 'mov', 'avi']))
                                        <i class="fas fa-video text-purple-500"></i>
                                    @elseif(in_array($archivo->extension, ['mp3', 'wav']))
                                        <i class="fas fa-music text-pink-500"></i>
                                    @elseif(in_array($archivo->extension, ['zip', 'rar']))
                                        <i class="fas fa-file-archive text-amber-500"></i>
                                    @else
                                        <i class="fas fa-file text-gray-500"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $archivo->nombre_original }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($archivo->tamanio / 1024, 2) }} KB · {{ strtoupper($archivo->extension) }}</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($archivo->ruta_archivo) }}" download 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors" style="color: #4f46e5; background: #eef2ff; hover:bg: #e0e7ff;">
                                <i class="fas fa-download"></i>
                                Descargar
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-500">No hay archivos adjuntos</p>
                        <p class="text-xs text-gray-400 mt-1">Sube archivos para compartir información con tu equipo</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Pie de página -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}" 
                   class="inline-flex items-center gap-2 text-sm font-medium transition-colors" style="color: #4f46e5; hover:color: #4338ca;">
                    <i class="fas fa-arrow-left"></i>
                    Volver a la campaña
                </a>
                <a href="{{ route('administrador.tareas.edit', $tarea->id) }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white font-semibold shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5" style="background: #ea9f21;">
                    <i class="fas fa-edit"></i>
                    Editar tarea
                </a>
            </div>
        </div>

        <!-- Comentarios -->
        <div class="mt-8">
            @include('administrador.tareas.comentarios', ['tarea' => $tarea])
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
    }
</style>
@endsection
