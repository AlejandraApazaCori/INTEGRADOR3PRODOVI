@extends('layouts.app')

@section('title', 'Publicar en Redes Sociales')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Encabezado con gradiente -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-white">Crear Nueva Publicación</h1>
                
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="p-6 space-y-8">
            <!-- Panel de publicación -->
            <div class="bg-gray-50 rounded-lg p-6">
                <!-- Mensaje de publicación exitosa (oculto por defecto) -->
                <div id="success-alert" class="hidden mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex justify-between items-start">
                        <div>
                            <h5 class="font-bold">✅ Publicación completada</h5>
                            <p class="mt-1"><strong>Plataformas:</strong> <span id="published-platforms">Facebook, Instagram</span></p>
                            <p><strong>Programado para:</strong> <span id="published-time">Ahora mismo</span></p>
                            <p class="mt-2 text-sm">Tu contenido está siendo distribuido en las plataformas seleccionadas.</p>
                        </div>
                        <button onclick="hideAlert()" class="text-green-700 hover:text-green-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Formulario de publicación -->
                <form id="publishing-form">
                    <!-- Selección de cuenta y plataforma -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cuenta y Plataformas del cliente</label>
                        
                        <!-- Selector de cuenta - Dinámico según plataformas seleccionadas -->
                        <div class="mb-4 space-y-3" id="account-display">
                            <!-- Cuenta de Facebook (ALNI) - visible por defecto -->
                            <div id="facebook-account" class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200">
                                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold">
                                    A
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-gray-800">ALNI</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">facebook</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cuenta de Instagram (la_llajuitaa) - oculta por defecto -->
                            <div id="instagram-account" class="hidden flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200">
                                <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white font-bold">
                                    L
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-gray-800">la_llajuitaa</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">instagram</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Plataformas -->
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center">
                                <input id="facebook-checkbox" type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" onchange="updateAccountDisplay()">
                                <label for="facebook-checkbox" class="ml-2 block text-sm text-gray-700 flex items-center">
                                    <svg class="w-5 h-5 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                    </svg>
                                    Facebook
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="instagram-checkbox" type="checkbox" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded" onchange="updateAccountDisplay()">
                                <label for="instagram-checkbox" class="ml-2 block text-sm text-gray-700 flex items-center">
                                    <svg class="w-5 h-5 mr-1 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                    </svg>
                                    Instagram
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenido multimedia -->
                  <!-- Contenido multimedia -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Contenido Multimedia Aprobado</label>
    
    @if($tarea->archivos->count() > 0)
        <div class="mt-4 border border-gray-200 rounded-lg divide-y divide-gray-200">
            @foreach($tarea->archivos as $archivo)
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600">
                                @if(in_array($archivo->extension, ['jpg', 'jpeg', 'png', 'gif']))
                                    📷
                                @elseif(in_array($archivo->extension, ['pdf']))
                                    📄
                                @elseif(in_array($archivo->extension, ['doc', 'docx']))
                                    📝
                                @elseif(in_array($archivo->extension, ['xls', 'xlsx']))
                                    📊
                                @elseif(in_array($archivo->extension, ['mp4', 'mov', 'avi']))
                                    🎥
                                @elseif(in_array($archivo->extension, ['mp3', 'wav']))
                                    🎵
                                @elseif(in_array($archivo->extension, ['zip', 'rar']))
                                    🗄️
                                @else
                                    📁
                                @endif
                            </span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">{{ $archivo->nombre_original }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($archivo->tamanio / 1024, 2) }} KB · {{ strtoupper($archivo->extension) }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ Storage::url($archivo->ruta_archivo) }}" download class="text-blue-600 hover:text-blue-800" title="Descargar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                    </div>
                </div>
                
                @if($archivo->descripcion)
                <div class="mt-2 text-sm text-gray-600">
                    <p>{{ $archivo->descripcion }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <div class="mt-4 text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500">No hay archivos aprobados para publicar</p>
        </div>
    @endif
    

</div>
                    
                   <!-- Contenido a publicar -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-2">
        <label for="content" class="block text-sm font-medium text-gray-700">Texto de la Publicación</label>
        {{-- BOTÓN ACTUALIZADO PARA PLAN DE MARKETING --}}
        <button type="button" id="generate-copy-btn" data-tarea-id="{{ $tarea->id }}" class="px-3 py-1 text-xs font-medium text-white bg-gradient-to-r from-purple-500 to-indigo-500 rounded-md hover:from-purple-600 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            Generar Copy con IA
        </button>
    </div>
    <textarea id="content" rows="5" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Escribe el mensaje que acompañará a tu publicación..."></textarea>
</div>
                    
                    <!-- Configuración de publicación -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Configuración de Publicación</label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Programación -->
                            <div>
                                <div class="flex items-center mb-2">
                                    <input id="publish-now" name="schedule_type" type="radio" value="now" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="publish-now" class="ml-2 block text-sm text-gray-700">
                                        Publicar ahora
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="schedule-later" name="schedule_type" type="radio" value="later" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="schedule-later" class="ml-2 block text-sm text-gray-700">
                                        Programar para más tarde
                                    </label>
                                </div>
                                <div id="schedule-datetime-container" class="mt-2 hidden">
                                    <input type="datetime-local" id="schedule-datetime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            
                            <!-- Optimización inteligente -->
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Optimización Inteligente</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>Nuestro sistema analiza automáticamente:</p>
                                            <ul class="list-disc pl-5 mt-1">
                                                <li>Horarios de mayor engagement</li>
                                                <li>Comportamiento de tu audiencia</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center">
                                        <input id="use-optimization" type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="use-optimization" class="ml-2 block text-sm text-blue-800">
                                            Optimizar tiempo de publicación
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                   <!-- Vista previa de la publicación -->
<div class="mb-6 hidden" id="publication-preview">
    <label class="block text-sm font-medium text-gray-700 mb-2">Vista Previa</label>
    <div class="space-y-4">
        <!-- Vista previa para Facebook (ALNI) -->
        <div id="facebook-preview" class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                    A
                </div>
                <div>
                    <div class="font-semibold">ALNI</div>
                    <div class="text-xs text-gray-500">Ahora mismo · <span class="text-blue-500">🌐 Público</span></div>
                </div>
            </div>
            <div class="mb-3" id="preview-content-facebook">
                El texto de tu publicación aparecerá aquí...
            </div>
            <div class="bg-gray-100 rounded-lg w-full h-48 flex items-center justify-center text-gray-400" id="preview-media-facebook">
                Vista previa de multimedia aparecerá aquí
            </div>
            <div class="mt-3 flex justify-between text-gray-500 text-sm">
                <span>👍 0</span>
                <span>💬 0 comentarios</span>
                <span>↗️ 0 compartidos</span>
            </div>
        </div>
        
        <!-- Vista previa para Instagram (la_llajuitaa) -->
        <div id="instagram-preview" class="hidden bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white font-bold mr-3">
                    L
                </div>
                <div>
                    <div class="font-semibold">la_llajuitaa</div>
                    <div class="text-xs text-gray-500">Ahora mismo · <span class="text-pink-500">📷 Instagram</span></div>
                </div>
            </div>
            <div class="mb-3" id="preview-content-instagram">
                El texto de tu publicación aparecerá aquí...
            </div>
            <div class="bg-gray-100 rounded-lg w-full h-48 flex items-center justify-center text-gray-400" id="preview-media-instagram">
                Vista previa de multimedia aparecerá aquí
            </div>
            <div class="mt-3 flex justify-between text-gray-500 text-sm">
                <span>❤️ 0</span>
                <span>💬 0 comentarios</span>
                <span>📤 0 compartidos</span>
            </div>
        </div>
    </div>
</div>
                    
                    <!-- Botones de acción -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <button type="button" onclick="showPreview()" class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                            Vista Previa
                        </button>
                        <div class="flex space-x-3">
                            <button type="button" onclick="publishContent()" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-medium rounded-lg shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Publicar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const RECOMMENDED_HOUR = 11;
const RECOMMENDED_MINUTE = 0;
const RECOMMENDED_TIME_LABEL = '11:00 AM';

function getRecommendedScheduleDate() {
    const now = new Date();
    const recommendedDate = new Date(now);
    recommendedDate.setHours(RECOMMENDED_HOUR, RECOMMENDED_MINUTE, 0, 0);

    if (recommendedDate <= now) {
        recommendedDate.setDate(recommendedDate.getDate() + 1);
    }

    return recommendedDate;
}

function toLocalDatetimeValue(date) {
    const timezoneOffset = date.getTimezoneOffset() * 60000;
    return new Date(date - timezoneOffset).toISOString().slice(0, 16);
}

function applyRecommendedSchedule() {
    const scheduleLater = document.getElementById('schedule-later');
    const datetimeContainer = document.getElementById('schedule-datetime-container');
    const datetimeInput = document.getElementById('schedule-datetime');

    scheduleLater.checked = true;
    datetimeContainer.classList.remove('hidden');
    datetimeInput.min = toLocalDatetimeValue(new Date());
    datetimeInput.value = toLocalDatetimeValue(getRecommendedScheduleDate());
}

document.getElementById('use-optimization').addEventListener('change', function() {
    if (this.checked) {
        simulateOptimization();
    }
});

function simulateOptimization() {
    const optimizationBox = document.querySelector('.bg-blue-50');
    const checkbox = document.getElementById('use-optimization');
    
    // Deshabilitar el checkbox durante el "análisis"
    checkbox.disabled = true;
    
    // Cambiar texto y mostrar animación de carga
    optimizationBox.innerHTML = `
        <div class="flex items-center p-4">
            <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-blue-800">Analizando audiencia y horarios óptimos...</span>
        </div>
    `;
    
    // Simular tiempo de análisis (2 segundos)
    setTimeout(() => {
        // Mostrar resultado "automático"
        optimizationBox.innerHTML = `
            <div id="optimization-result" class="p-4 rounded-lg cursor-pointer transition hover:bg-blue-100" title="Aplicar horario recomendado">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mt-0.5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Optimización completada</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="font-semibold">Recomendación del sistema:</p>
                            <ul class="list-disc pl-5 mt-1">
                                <li>Publicar hoy a las <strong>${RECOMMENDED_TIME_LABEL}</strong> (mayor engagement)</li>
                                <li>Incluir 2 hashtags para mayor alcance</li>
                            </ul>
                            <p class="mt-2 text-xs font-medium text-blue-800">Haz clic aquí para llenar automáticamente "Programar para más tarde".</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center">
                    <input id="use-optimization" type="checkbox" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="use-optimization" class="ml-2 block text-sm text-blue-800">
                        Usar recomendaciones
                    </label>
                </div>
            </div>
        `;
        
        // Actualizar el selector de fecha automáticamente
        document.getElementById('optimization-result').addEventListener('click', function(event) {
            if (event.target.id !== 'use-optimization') {
                applyRecommendedSchedule();
            }
        });
        
        // Re-asignar el event listener al nuevo checkbox
        document.getElementById('use-optimization').addEventListener('change', function() {
            if (!this.checked) {
                optimizationBox.innerHTML = `
                    <div class="flex items-start">
                        <!-- Contenido original de la caja -->
                    </div>
                `;
            }
        });
    }, 2000);
}
    // Función para actualizar la visualización de cuentas según las plataformas seleccionadas
    function updateAccountDisplay() {
        const facebookChecked = document.getElementById('facebook-checkbox').checked;
        const instagramChecked = document.getElementById('instagram-checkbox').checked;
        
        // Mostrar/ocultar cuentas según selección
        document.getElementById('facebook-account').classList.toggle('hidden', !facebookChecked);
        document.getElementById('instagram-account').classList.toggle('hidden', !instagramChecked);
    }
    
    // Mostrar/ocultar selector de fecha para programación
    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const datetimeContainer = document.getElementById('schedule-datetime-container');
            if (this.value === 'later') {
                datetimeContainer.classList.remove('hidden');
                
                // Establecer fecha mínima como ahora
                const now = new Date();
                const localISOTime = toLocalDatetimeValue(now);
                document.getElementById('schedule-datetime').min = localISOTime;

                if (!document.getElementById('schedule-datetime').value) {
                    document.getElementById('schedule-datetime').value = toLocalDatetimeValue(getRecommendedScheduleDate());
                }
            } else {
                datetimeContainer.classList.add('hidden');
            }
        });
    });
    
   // Mostrar vista previa
function showPreview() {
    const facebookChecked = document.getElementById('facebook-checkbox').checked;
    const instagramChecked = document.getElementById('instagram-checkbox').checked;
    const contentText = document.getElementById('content').value;
    
    // Actualizar vista previa de Facebook
    document.getElementById('facebook-preview').classList.toggle('hidden', !facebookChecked);
    document.getElementById('preview-content-facebook').textContent = contentText || "El texto de tu publicación aparecerá aquí...";
    
    // Actualizar vista previa de Instagram
    document.getElementById('instagram-preview').classList.toggle('hidden', !instagramChecked);
    document.getElementById('preview-content-instagram').textContent = contentText || "El texto de tu publicación aparecerá aquí...";
    
    // Mostrar la sección de vista previa
    document.getElementById('publication-preview').classList.remove('hidden');
}

// Actualizar vista previa cuando cambia el texto
document.getElementById('content').addEventListener('input', function() {
    const contentText = this.value;
    document.getElementById('preview-content-facebook').textContent = contentText || "El texto de tu publicación aparecerá aquí...";
    document.getElementById('preview-content-instagram').textContent = contentText || "El texto de tu publicación aparecerá aquí...";
});
    
    // Publicar contenido
    function publishContent() {
        const successAlert = document.getElementById('success-alert');
        const platforms = [];
        
        if (document.getElementById('facebook-checkbox').checked) platforms.push('Facebook');
        if (document.getElementById('instagram-checkbox').checked) platforms.push('Instagram');
        
        document.getElementById('published-platforms').textContent = platforms.join(', ') || 'Ninguna plataforma seleccionada';
        
        // Determinar el tiempo de publicación
        let publishTime = 'Ahora mismo';
        if (document.getElementById('schedule-later').checked) {
            const datetimeInput = document.getElementById('schedule-datetime');
            const selectedDate = new Date(datetimeInput.value);
            publishTime = selectedDate.toLocaleString();
        }
        document.getElementById('published-time').textContent = publishTime;
        
        // Mostrar alerta de éxito
        successAlert.classList.remove('hidden');
        
        // Desplazarse al inicio del formulario para ver la alerta
        successAlert.scrollIntoView({ behavior: 'smooth' });
        
        // Mostrar animación de carga ficticia
        const publishBtn = document.querySelector('button[onclick="publishContent()"]');
        const originalText = publishBtn.innerHTML;
        publishBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Publicando...`;
        
        setTimeout(() => {
            publishBtn.innerHTML = originalText;
        }, 1500);
    }
    
    // Ocultar alerta
    function hideAlert() {
        document.getElementById('success-alert').classList.add('hidden');
    }
    
    // Mostrar vista previa de multimedia cuando se selecciona
    document.querySelectorAll('button[type="button"]').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent.includes('Imágenes') || this.textContent.includes('Video')) {
                const mediaPreview = document.getElementById('media-preview');
                mediaPreview.classList.remove('hidden');
            }
        });
    });
    
    // Actualizar vista previa cuando cambia el texto
    document.getElementById('content').addEventListener('input', function() {
        const previewContent = document.getElementById('preview-content');
        if (previewContent) {
            previewContent.textContent = this.value || "El texto de tu publicación aparecerá aquí...";
        }
    });
    
    // Inicializar la visualización de cuentas
    updateAccountDisplay();
    // ... tu código JavaScript existente ...

// NUEVO: Manejador de clic para el botón de generar copy
document.getElementById('generate-copy-btn')?.addEventListener('click', async function() {
    const btn = this;
    const tareaId = btn.dataset.tareaId;
    const contentTextarea = document.getElementById('content');

    // 1. Mostrar estado de carga
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generando...
    `;

    try {
        // 2. Hacer la llamada a la API
        const response = await fetch('{{ route("publicaciones.generate.copy") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ tarea_id: tareaId })
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor.');
        }

        const data = await response.json();

        if (data.success) {
            // 3. Colocar el texto generado en el textarea
            contentTextarea.value = data.copy;
        } else {
            alert('Error: ' + (data.copy || 'No se pudo generar el copy.'));
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Hubo un error en la petición. Por favor, inténtalo de nuevo.');
    } finally {
        // 4. Restaurar el botón
        btn.disabled = false;
        btn.innerHTML = `
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            Generar Copy (Plan de Marketing)
        `;
    }
});
</script>
<style>
    .optimization-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
        100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }
</style>
@endsection
