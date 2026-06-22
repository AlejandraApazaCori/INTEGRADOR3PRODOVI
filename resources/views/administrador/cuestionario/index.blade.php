@extends('layouts.app')

@section('title', 'Gestionar Cuestionario')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-layer-group text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Estructura del Cuestionario</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Organiza los temas y preguntas de tu cuestionario</p>
                    </div>
                    <a href="{{ route('administrador.cuestionario.estructura.create') }}" 
                       class="inline-flex items-center px-6 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" 
                       style="background: linear-gradient(to right, #f43f5e, #f97316); box-shadow: 0 4px 14px rgba(244,63,94,0.35);">
                        <i class="fas fa-plus mr-2 text-sm"></i>
                        Añadir Nuevo Tema
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas mejoradas -->
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Lista de temas mejorada -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">
                    <i class="fas fa-arrows-alt mr-2 text-gray-400"></i>
                    Arrastra los temas para reordenarlos
                </span>
                <span class="text-sm text-gray-500" id="temas-count">
                    <i class="fas fa-list mr-1"></i>
                    {{ $temas->count() }} temas
                </span>
            </div>
            
            <ul class="divide-y divide-gray-100" id="temas-list">
                @forelse($temas as $index => $tema)
                    <li class="px-6 py-4 flex items-center justify-between hover:bg-indigo-50/30 transition-colors duration-150 group" data-id="{{ $tema->id }}">
                        <div class="flex items-center flex-1">
                            <div class="flex items-center cursor-move mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-grip-vertical text-lg"></i>
                            </div>
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                        {{ $tema->nombre_tema }}
                                    </p>
                                    <div class="flex items-center mt-1 gap-4">
                                        <span class="inline-flex items-center text-sm text-gray-500">
                                            <i class="fas fa-question-circle mr-1.5 text-gray-400"></i>
                                            {{ $tema->preguntas->count() }} pregunta(s)
                                        </span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                            <i class="fas fa-hashtag mr-1 text-gray-400"></i>
                                            ID: {{ $tema->id }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('administrador.cuestionario.estructura.edit', $tema->id) }}" 
                               class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('administrador.cuestionario.estructura.destroy', $tema->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este tema y todas sus preguntas?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 font-medium mb-2">No hay temas creados</p>
                            <p class="text-gray-400 text-sm mb-4">Comienza creando el primer tema de tu cuestionario</p>
                            <a href="{{ route('administrador.cuestionario.estructura.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Crear primer tema
                            </a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Footer informativo -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p><i class="fas fa-lightbulb mr-2 text-amber-400"></i> Los temas se muestran en el orden que aparecen aquí. Arrástralos para reordenarlos.</p>
        </div>
    </div>
</div>

<!-- Script para arrastrar y soltar (reordenar) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('temas-list');
        const temasCount = document.getElementById('temas-count');
        
        if (el) {
            new Sortable(el, {
                animation: 200,
                handle: '.cursor-move',
                ghostClass: 'bg-indigo-50',
                dragClass: 'shadow-lg',
                onEnd: function (evt) {
                    const temas = [...el.children].map(li => li.dataset.id);
                    
                    // Mostrar indicador de carga
                    const items = el.children;
                    for (let item of items) {
                        item.style.opacity = '0.5';
                    }
                    
                    fetch('{{ route("administrador.cuestionario.estructura.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ temas: temas })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Orden actualizado:', data);
                        // Restaurar opacidad
                        for (let item of items) {
                            item.style.opacity = '1';
                        }
                        // Actualizar numeración
                        if (temasCount) {
                            temasCount.textContent = `${temas.length} temas`;
                        }
                        // Mostrar notificación de éxito
                        showNotification('Orden actualizado correctamente', 'success');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Restaurar opacidad
                        for (let item of items) {
                            item.style.opacity = '1';
                        }
                        showNotification('Error al actualizar el orden', 'error');
                    });
                }
            });
        }
        
        // Función para mostrar notificaciones
        function showNotification(message, type = 'success') {
            const colors = {
                success: 'bg-green-50 border-green-500 text-green-700',
                error: 'bg-red-50 border-red-500 text-red-700'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 border-l-4 rounded-r-xl shadow-lg ${colors[type]} z-50 transform transition-all duration-500 translate-x-full`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-3 text-${type === 'success' ? 'green' : 'red'}-500"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
    });
</script>

<style>
    /* Banner geométrico - Mismo estilo que las otras vistas */
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

    /* Estilo para el drag */
    .sortable-ghost {
        opacity: 0.4;
        background: #eef2ff;
    }
    
    .sortable-drag {
        transform: scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
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