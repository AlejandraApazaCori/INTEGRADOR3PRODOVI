@extends('layouts.app')

@section('title', 'Gestionar Cuestionario')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header con estilo page-header -->
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-layer-group"></i> Estructura del Cuestionario</h1>
                <p class="subtitle">Organiza los temas y preguntas de tu cuestionario</p>
            </div>
            <a href="{{ route('administrador.cuestionario.estructura.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Añadir Nuevo Tema
            </a>
        </div>

        <!-- Alertas mejoradas -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-xl shadow-sm animate-slideIn">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-700 font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Lista de temas mejorada -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Arrastra los temas para reordenarlos</span>
                <span class="text-sm text-gray-500" id="temas-count">{{ $temas->count() }} temas</span>
            </div>
            
            <ul class="divide-y divide-gray-100" id="temas-list">
                @forelse($temas as $index => $tema)
                    <li class="px-6 py-4 flex items-center justify-between hover:bg-indigo-50/30 transition-colors duration-150 group" data-id="{{ $tema->id }}">
                        <div class="flex items-center flex-1">
                            <div class="flex items-center cursor-move mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path>
                                </svg>
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
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $tema->preguntas->count() }} pregunta(s)
                                        </span>
                                        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                            ID: {{ $tema->id }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('administrador.cuestionario.estructura.edit', $tema->id) }}" 
                               class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('administrador.cuestionario.estructura.destroy', $tema->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este tema y todas sus preguntas?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <p class="text-gray-500 font-medium mb-2">No hay temas creados</p>
                            <p class="text-gray-400 text-sm mb-4">Comienza creando el primer tema de tu cuestionario</p>
                            <a href="{{ route('administrador.cuestionario.estructura.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Crear primer tema
                            </a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Footer informativo -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>💡 Los temas se muestran en el orden que aparecen aquí. Arrástralos para reordenarlos.</p>
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
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"></path>
                    </svg>
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
    /* Estilos del page-header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(79, 70, 229, 0.1);
    }
    
    .header-content h1 {
        font-size: 1.875rem;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }
    
    .header-content h1 i {
        color: #4F46E5;
        font-size: 1.75rem;
    }
    
    .header-content .subtitle {
        margin-top: 0.25rem;
        font-size: 0.95rem;
        color: #6B7280;
        margin-bottom: 0;
    }
    
    .btn-primary {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1.5rem;
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: 0.75rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        gap: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #4338CA 0%, #6D28D9 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.35);
        text-decoration: none;
        color: white;
    }
    
    .btn-primary i {
        font-size: 0.875rem;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
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

    /* Responsive para el header */
    @media (max-width: 640px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            padding: 1.25rem;
        }
        
        .header-content h1 {
            font-size: 1.5rem;
        }
        
        .btn-primary {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endsection