@extends('layouts.app')

@section('title', 'Publicar en Redes Sociales')

@section('content')
@php
    $facebookPageName = optional($facebookPage)->display_name ?? optional($facebookPage)->username ?? 'Sin página vinculada';
    $facebookPageInitial = strtoupper(substr($facebookPageName, 0, 1));
    $facebookReady = filled(optional($facebookPage)->provider_user_id) && filled(optional($facebookPage)->access_token);
@endphp
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 45%, #F5F3FF 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-9">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl flex-shrink-0 rp-icon-pulse" style="background: rgba(255,255,255,0.22); backdrop-filter: blur(6px); box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                        <i class="fas fa-share-alt text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1 tracking-tight">Crear Nueva Publicación</h1>
                        <p style="color: #dbe6ff; font-size: 0.92rem;">Comparte contenido en las redes sociales de tu cliente</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-full" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                        <i class="fas fa-bolt text-white text-xs"></i>
                        <span class="text-white text-xs font-semibold">Listo para publicar</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden rp-card">
            <!-- Encabezado con gradiente -->
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md" style="background: linear-gradient(135deg, #ea9f21, #e37225);">
                        <i class="fas fa-rocket text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Nueva Publicación</h2>
                        <p class="text-gray-500 text-sm mt-0.5">Comparte contenido en redes sociales</p>
                    </div>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="p-6 md:p-8 space-y-6">
                <!-- Panel de publicación -->
                <div class="rounded-xl p-6" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <!-- Mensaje de publicación exitosa -->
                    @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl flex items-start gap-3 rp-alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; box-shadow: 0 4px 14px rgba(34,197,94,0.12);">
                        <i class="fas fa-check-circle text-lg" style="color: #16a34a;"></i>
                        <div class="flex-1 text-sm" style="color: #166534;">{{ session('success') }}</div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl flex items-start gap-3" style="background: #fef2f2; border: 1px solid #fecaca;">
                        <i class="fas fa-triangle-exclamation text-lg" style="color: #dc2626;"></i>
                        <div class="flex-1 text-sm" style="color: #991b1b;">{{ session('error') }}</div>
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div id="success-alert" class="hidden mb-6 p-4 rounded-xl flex items-start gap-3 rp-alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; box-shadow: 0 4px 14px rgba(34,197,94,0.12);">
                        <i class="fas fa-check-circle text-lg" style="color: #16a34a;"></i>
                        <div class="flex-1">
                            <h5 class="font-bold" style="color: #166534;">✅ Publicación completada</h5>
                            <p class="mt-1 text-sm" style="color: #166534;"><strong>Plataformas:</strong> <span id="published-platforms">Facebook, Instagram</span></p>
                            <p class="text-sm" style="color: #166534;"><strong>Programado para:</strong> <span id="published-time">Ahora mismo</span></p>
                            <p class="mt-2 text-xs" style="color: #166534;">Tu contenido está siendo distribuido en las plataformas seleccionadas.</p>
                        </div>
                        <button onclick="hideAlert()" class="flex-shrink-0 rp-close-btn" style="color: #166534;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Formulario de publicación -->
                    <form id="publishing-form" method="POST" action="{{ route('administrador.publicaciones.publicar.store') }}">
                        @csrf
                        <input type="hidden" name="tarea_id" value="{{ $tarea->id }}">
                        <!-- Selección de cuenta y plataforma -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-users mr-2 text-indigo-400"></i>
                                Cuenta y Plataformas
                            </label>
                            
                            <div class="mb-4 space-y-3" id="account-display">
                                <div id="facebook-account" class="flex items-center space-x-3 p-3 rounded-xl border rp-account-card" style="background: #ffffff; border-color: #e2e8f0;">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm" style="background: linear-gradient(135deg, #1877f2, #0d5dc7);">
                                        {{ $facebookPageInitial }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-800">{{ $facebookPageName }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: #eef2ff; color: #4f46e5;">
                                                <i class="fab fa-facebook-f mr-1"></i>facebook
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="instagram-account" class="hidden flex items-center space-x-3 p-3 rounded-xl border rp-account-card" style="background: #ffffff; border-color: #e2e8f0;">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm" style="background: linear-gradient(135deg, #e4405f, #c1306d);">
                                        L
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-800">la_llajuitaa</span>
                                            <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: #fdf2f8; color: #db2777;">
                                                <i class="fab fa-instagram mr-1"></i>instagram
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3">
                                <label class="rp-checkbox-pill" for="facebook-checkbox">
                                    <input id="facebook-checkbox" name="platforms[]" value="facebook" type="checkbox" checked class="h-4 w-4 rounded focus:ring-2" style="accent-color: #1877f2; border-color: #d1d5db;" onchange="updateAccountDisplay(); updatePreview();">
                                    <span class="ml-2 text-sm text-gray-700 flex items-center">
                                        <i class="fab fa-facebook mr-1.5" style="color: #1877f2;"></i>
                                        Facebook
                                    </span>
                                </label>
                                <label class="rp-checkbox-pill" for="instagram-checkbox">
                                    <input id="instagram-checkbox" name="platforms[]" value="instagram" type="checkbox" class="h-4 w-4 rounded focus:ring-2" style="accent-color: #e4405f; border-color: #d1d5db;" onchange="updateAccountDisplay(); updatePreview();">
                                    <span class="ml-2 text-sm text-gray-700 flex items-center">
                                        <i class="fab fa-instagram mr-1.5" style="color: #e4405f;"></i>
                                        Instagram
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Contenido multimedia aprobado -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-images mr-2 text-indigo-400"></i>
                                Contenido Multimedia Aprobado
                            </label>
                            
                            @if($tarea->archivos->count() > 0)
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($tarea->archivos as $archivo)
                                    <div class="flex items-center justify-between p-3 rounded-xl border rp-file-card" style="background: #ffffff; border-color: #e2e8f0;">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center text-lg" style="background: linear-gradient(135deg, #e0e7ff, #ede9fe);">
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
                                                @else
                                                    <i class="fas fa-file text-gray-500"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $archivo->nombre_original }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($archivo->tamanio / 1024, 2) }} KB · {{ strtoupper($archivo->extension) }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ Storage::url($archivo->ruta_archivo) }}" download class="text-gray-400 hover:text-indigo-600 transition-colors rp-download-btn">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 rounded-xl border border-dashed" style="background: #f8fafc; border-color: #d1d5db;">
                                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                                    <p class="text-gray-500 text-sm">No hay archivos aprobados para publicar</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Texto de la publicación -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <label for="content" class="block text-sm font-semibold text-gray-700">
                                    <i class="fas fa-align-left mr-2 text-indigo-400"></i>
                                    Texto de la Publicación
                                </label>
                                <button type="button" id="generate-copy-btn" data-tarea-id="{{ $tarea->id }}" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white rounded-lg transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg" style="background: linear-gradient(135deg, #ea9f21, #e37225); box-shadow: 0 2px 8px rgba(234,159,33,0.35);">
                                    <i class="fas fa-wand-magic-sparkles"></i>
                                    Generar Copy con IA
                                </button>
                            </div>
                            <textarea id="content" name="message" rows="4" oninput="updatePreview()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                                placeholder="Escribe el mensaje que acompañará a tu publicación...">{{ old('message') }}</textarea>
                        </div>
                        
                        <!-- Configuración de publicación -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-gear mr-2 text-indigo-400"></i>
                                Configuración de Publicación
                            </label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-4 rounded-xl border" style="background: #ffffff; border-color: #e2e8f0;">
                                    <div class="flex items-center mb-3 rp-radio-option">
                                        <input id="publish-now" name="schedule_type" type="radio" value="now" {{ old('schedule_type', 'now') === 'now' ? 'checked' : '' }} class="h-4 w-4 focus:ring-2" style="accent-color: #4f46e5; border-color: #d1d5db;">
                                        <label for="publish-now" class="ml-2 block text-sm text-gray-700 font-medium">
                                            <i class="fas fa-bolt mr-1 text-amber-400"></i>
                                            Publicar ahora
                                        </label>
                                    </div>
                                    <div class="flex items-center rp-radio-option">
                                        <input id="schedule-later" name="schedule_type" type="radio" value="later" {{ old('schedule_type') === 'later' ? 'checked' : '' }} class="h-4 w-4 focus:ring-2" style="accent-color: #4f46e5; border-color: #d1d5db;">
                                        <label for="schedule-later" class="ml-2 block text-sm text-gray-700 font-medium">
                                            <i class="fas fa-clock mr-1 text-indigo-400"></i>
                                            Programar para más tarde
                                        </label>
                                    </div>
                                    <div id="schedule-datetime-container" class="mt-4 hidden">
                                        <div class="rp-schedule-card">
                                            <div class="rp-schedule-card__header">
                                                <div class="rp-schedule-card__icon">
                                                    <i class="fas fa-calendar-check"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-800">Programa tu publicación</p>
                                                    <p class="text-xs text-gray-500">Elige la fecha y hora exacta en que quieres publicar el contenido.</p>
                                                </div>
                                            </div>

                                            <input type="datetime-local" id="schedule-datetime" name="scheduled_at" class="sr-only" value="{{ old('scheduled_at') }}">

                                            <div class="rp-schedule-grid mt-4">
                                                <label class="rp-schedule-field">
                                                    <span class="rp-schedule-field__label">Fecha</span>
                                                    <div class="rp-schedule-field__control">
                                                        <i class="fas fa-calendar-day"></i>
                                                        <input type="date" id="schedule-date-ui" class="rp-schedule-input">
                                                    </div>
                                                </label>

                                                <label class="rp-schedule-field">
                                                    <span class="rp-schedule-field__label">Hora</span>
                                                    <div class="rp-schedule-field__control">
                                                        <i class="fas fa-clock"></i>
                                                        <input type="time" id="schedule-time-ui" class="rp-schedule-input" step="60">
                                                    </div>
                                                </label>
                                            </div>

                                            <div class="mt-4">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Atajos rápidos</p>
                                                <div class="rp-schedule-chips">
                                                    <button type="button" class="rp-schedule-chip" data-schedule-preset="today-0900">Hoy 09:00</button>
                                                    <button type="button" class="rp-schedule-chip" data-schedule-preset="today-1800">Hoy 18:00</button>
                                                    <button type="button" class="rp-schedule-chip" data-schedule-preset="tomorrow-0900">Mañana 09:00</button>
                                                </div>
                                            </div>

                                            <div class="rp-schedule-summary mt-4">
                                                <span class="rp-schedule-summary__label">Se publicará:</span>
                                                <span id="schedule-readable" class="rp-schedule-summary__value">Selecciona una fecha y hora</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 rounded-xl border" style="background: linear-gradient(135deg, #eff6ff, #f5f3ff); border-color: #bfdbfe;">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(79,70,229,0.12);">
                                            <i class="fas fa-lightbulb text-sm" style="color: #4f46e5;"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold" style="color: #1e3a5f;">Optimización Inteligente</h3>
                                            <div class="mt-2 text-xs" style="color: #1e40af;">
                                                <p>Nuestro sistema analiza automáticamente:</p>
                                                <ul class="list-disc pl-4 mt-1 space-y-0.5">
                                                    <li>Horarios de mayor engagement</li>
                                                    <li>Comportamiento de tu audiencia</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center pt-3" style="border-top: 1px solid rgba(79,70,229,0.12);">
                                        <input id="use-optimization" type="checkbox" class="h-4 w-4 rounded focus:ring-2" style="accent-color: #4f46e5; border-color: #d1d5db;">
                                        <label for="use-optimization" class="ml-2 block text-sm font-medium" style="color: #1e3a5f;">
                                            Optimizar tiempo de publicación
                                        </label>
                                    </div>
                                    <div id="optimization-panel" class="hidden mt-4 pt-4" style="border-top: 1px solid rgba(79,70,229,0.12);">
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <button type="button" class="rp-optimization-time" data-optimized-time="12:00">12 pm</button>
                                            <button type="button" class="rp-optimization-time" data-optimized-time="20:00">8 pm</button>
                                        </div>
                                        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-2.5 rounded-xl">
                                                    <i class="fas fa-chart-line text-white text-sm"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-800">Predicción de Horarios de Publicación</h3>
                                                    <p class="text-xs text-gray-500">Modelo LSTM – Engagement estimado para Instagram y Facebook</p>
                                                </div>
                                            </div>
                                            <div style="position:relative; height:220px; width:100%;">
                                                <canvas id="optimizationEngagementChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vista previa de la publicación - visible por defecto -->
                        @php
                            $approvedImageFiles = $tarea->archivos->filter(function ($archivo) {
                                return in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'gif']);
                            })->values();
                            $previewImage = $approvedImageFiles->first();
                            $hasCarouselPreview = $approvedImageFiles->count() > 1;
                        @endphp
                        <div class="mb-6" id="publication-preview">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-eye mr-2 text-indigo-400"></i>
                                Vista Previa
                            </label>
                            <div class="space-y-4">
                                <div id="facebook-preview" class="bg-white overflow-hidden rp-facebook-preview" style="border: 1px solid #dddfe2; border-radius: 0;">
                                    <div class="px-3 pt-3 pb-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-2.5">
                                                <div class="rp-facebook-avatar">P</div>
                                                <div>
                                                    <div class="flex items-center gap-1 flex-wrap">
                                                        <span class="text-[14px] font-semibold text-black">PRODOVI</span>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-[12px] text-gray-500 leading-none mt-1">
                                                        <span id="preview-facebook-time">Ahora mismo</span>
                                                        <i class="fas fa-globe-americas text-[11px]"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="text-gray-500 text-sm leading-none">...</button>
                                        </div>
                                        <div class="mt-3 text-[14px] text-[#1c1e21] leading-5" id="preview-content-facebook">Escribe tu mensaje aquí...</div>
                                    </div>
                                    <div class="w-full bg-[#f0f2f5]" id="preview-media-facebook" style="min-height: 420px;">
                                        @if($previewImage && $hasCarouselPreview)
                                            <div class="rp-preview-carousel" data-carousel>
                                                <div class="rp-preview-carousel__track" data-carousel-track>
                                                    @foreach($approvedImageFiles as $imageFile)
                                                        <div class="rp-preview-carousel__slide">
                                                            <img src="{{ Storage::url($imageFile->ruta_archivo) }}" alt="Vista previa multimedia" class="w-full h-full object-cover block">
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="rp-preview-carousel__nav rp-preview-carousel__nav--prev" data-carousel-prev>
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="rp-preview-carousel__nav rp-preview-carousel__nav--next" data-carousel-next>
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                                <div class="rp-preview-carousel__dots">
                                                    @foreach($approvedImageFiles as $imageIndex => $imageFile)
                                                        <button type="button" class="rp-preview-carousel__dot {{ $imageIndex === 0 ? 'is-active' : '' }}" data-carousel-dot="{{ $imageIndex }}"></button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif($previewImage)
                                            <img src="{{ Storage::url($previewImage->ruta_archivo) }}" alt="Vista previa multimedia" class="w-full h-full object-cover block">
                                        @else
                                            <div class="w-full h-[420px] flex items-center justify-center text-gray-400">
                                                <i class="fas fa-image text-3xl mr-2"></i>
                                                Vista previa de multimedia
                                            </div>
                                        @endif
                                    </div>
                                    <div class="px-3 py-2 text-[13px] text-gray-500 border-b border-[#dadde1] flex items-center gap-1.5">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[11px]" style="background:#1877f2;">👍</span>
                                        <span>5</span>
                                    </div>
                                    <div class="grid grid-cols-3 text-[15px] text-[#65676b] font-semibold">
                                        <button type="button" class="rp-facebook-action"><i class="far fa-thumbs-up"></i><span>Me gusta</span></button>
                                        <button type="button" class="rp-facebook-action"><i class="far fa-comment"></i><span>Comentar</span></button>
                                        <button type="button" class="rp-facebook-action"><i class="far fa-share-square"></i><span>Compartir</span></button>
                                    </div>
                                </div>
                                
                                <div id="instagram-preview" class="hidden bg-white overflow-hidden rp-facebook-preview" style="border: 1px solid #dddfe2; border-radius: 0;">
                                    <div class="px-3 pt-3 pb-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-2.5">
                                                <div class="rp-facebook-avatar">P</div>
                                                <div>
                                                    <div class="flex items-center gap-1 flex-wrap">
                                                        <span class="text-[14px] font-semibold text-black">PRODOVI</span>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-[12px] text-gray-500 leading-none mt-1">
                                                        <span id="preview-instagram-time">Ahora mismo</span>
                                                        <span>·</span>
                                                        <i class="fas fa-globe-americas text-[11px]"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="text-gray-500 text-sm leading-none">...</button>
                                        </div>
                                        <div class="mt-3 text-[14px] text-[#1c1e21] leading-5" id="preview-content-instagram">Escribe tu mensaje aquí...</div>
                                    </div>
                                    <div class="w-full bg-[#f0f2f5]" id="preview-media-instagram" style="min-height: 420px;">
                                        @if($previewImage && $hasCarouselPreview)
                                            <div class="rp-preview-carousel" data-carousel>
                                                <div class="rp-preview-carousel__track" data-carousel-track>
                                                    @foreach($approvedImageFiles as $imageFile)
                                                        <div class="rp-preview-carousel__slide">
                                                            <img src="{{ Storage::url($imageFile->ruta_archivo) }}" alt="Vista previa multimedia" class="w-full h-full object-cover block">
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="rp-preview-carousel__nav rp-preview-carousel__nav--prev" data-carousel-prev>
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <button type="button" class="rp-preview-carousel__nav rp-preview-carousel__nav--next" data-carousel-next>
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                                <div class="rp-preview-carousel__dots">
                                                    @foreach($approvedImageFiles as $imageIndex => $imageFile)
                                                        <button type="button" class="rp-preview-carousel__dot {{ $imageIndex === 0 ? 'is-active' : '' }}" data-carousel-dot="{{ $imageIndex }}"></button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif($previewImage)
                                            <img src="{{ Storage::url($previewImage->ruta_archivo) }}" alt="Vista previa multimedia" class="w-full h-full object-cover block">
                                        @else
                                            <div class="w-full h-[420px] flex items-center justify-center text-gray-400">
                                                <i class="fas fa-image text-3xl mr-2"></i>
                                                Vista previa de multimedia
                                            </div>
                                        @endif
                                    </div>
                                    <div class="px-3 py-2 text-[13px] text-gray-500 border-b border-[#dadde1] flex items-center gap-1.5">
                                        <span>5</span>
                                    </div>
                                    <div class="grid grid-cols-3 text-[15px] text-[#65676b] font-semibold">
                                        <button type="button" class="rp-facebook-action"><i class="far fa-thumbs-up"></i><span>Me gusta</span></button>
                                        <button type="button" class="rp-facebook-action"><i class="far fa-comment"></i><span>Comentar</span></button>
                                        <button type="button" class="rp-facebook-action"><i class="far fa-share-square"></i><span>Compartir</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        @if(! $facebookReady)
                        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Este cliente todavía no tiene una página de Facebook autorizada con token válido. Vincula Facebook desde su panel antes de publicar.
                        </div>
                        @endif

                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-5 border-t border-gray-200">
                            <button type="button" onclick="togglePreview()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 rp-secondary-btn" style="color: #4f46e5; background: #eef2ff;">
                                <i class="fas fa-eye"></i>
                                <span id="preview-toggle-text">Ocultar Vista Previa</span>
                            </button>
                            <button type="submit" id="publish-submit-btn" {{ ! $facebookReady ? 'disabled' : '' }} 
                                    class="inline-flex items-center gap-2 px-7 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-2xl hover:-translate-y-0.5 rp-publish-btn" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                <i class="fas fa-rocket"></i>
                                Publicar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rp-banner {
        background:
            linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, #4f46e5 25%, transparent 25%),
            linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
            linear-gradient(to bottom right, #4338ca 0%, #2563eb 60%, #1d4ed8 100%);
        background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%;
        background-color: #1d4ed8;
        position: relative;
        box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.5);
    }

    .rp-banner-overlay {
        background:
            radial-gradient(circle at 0% 0%, rgba(255,255,255,0.22) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(255,255,255,0.18) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.18) 0%, transparent 50%),
            radial-gradient(circle at 0% 100%, rgba(255,255,255,0.22) 0%, transparent 50%);
        background-size: 50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat: no-repeat;
    }

    .rp-icon-pulse {
        animation: rp-pulse 3s ease-in-out infinite;
    }
    @keyframes rp-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }

    .rp-card {
        transition: box-shadow 0.3s ease;
    }

    .rp-account-card, .rp-file-card, .rp-preview-card {
        transition: all 0.2s ease;
    }
    .rp-account-card:hover, .rp-file-card:hover {
        border-color: #c7d2fe !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);
        transform: translateY(-1px);
    }
    .rp-preview-card {
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }

    .rp-facebook-preview {
        max-width: 566px;
        margin: 0 auto;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        font-family: Helvetica, Arial, sans-serif;
    }

    .rp-facebook-avatar {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        background: radial-gradient(circle at 30% 30%, #4d2a82, #1d112c 70%);
        box-shadow: inset 0 0 0 2px rgba(255,255,255,0.12);
        flex-shrink: 0;
    }

    .rp-facebook-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 0.5rem;
        border-top: 1px solid #dadde1;
        transition: background 0.2s ease;
    }

    .rp-facebook-action:hover {
        background: #f2f2f2;
    }

    .rp-checkbox-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.9rem;
        border-radius: 0.75rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .rp-checkbox-pill:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.08);
    }

    .rp-radio-option {
        padding: 0.35rem 0.5rem;
        border-radius: 0.5rem;
        transition: background 0.2s ease;
    }
    .rp-radio-option:hover {
        background: #f8fafc;
    }

    .rp-schedule-card {
        padding: 1rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 55%, #f8fafc 100%);
        border: 1px solid #c7d2fe;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 10px 24px rgba(79,70,229,0.08);
    }

    .rp-schedule-card__header {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .rp-schedule-card__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        box-shadow: 0 8px 20px rgba(79,70,229,0.28);
        flex-shrink: 0;
    }

    .rp-schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .rp-schedule-field {
        display: block;
    }

    .rp-schedule-field__label {
        display: block;
        margin-bottom: 0.45rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6366f1;
    }

    .rp-schedule-field__control {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-radius: 0.95rem;
        border: 1px solid #cbd5e1;
        background: rgba(255,255,255,0.92);
        padding: 0.85rem 0.95rem;
        transition: all 0.2s ease;
    }

    .rp-schedule-field__control:focus-within {
        border-color: #818cf8;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
        transform: translateY(-1px);
    }

    .rp-schedule-field__control i {
        color: #6366f1;
    }

    .rp-schedule-input {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 0;
        color: #111827;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .rp-schedule-input:focus {
        box-shadow: none;
    }

    .rp-schedule-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .rp-schedule-chip {
        border: 1px solid #c7d2fe;
        background: #ffffff;
        color: #4338ca;
        border-radius: 9999px;
        padding: 0.5rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 700;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(79,70,229,0.06);
    }

    .rp-schedule-chip:hover,
    .rp-schedule-chip.is-active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-color: transparent;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(79,70,229,0.22);
    }

    .rp-schedule-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        padding: 0.9rem 1rem;
        border-radius: 0.95rem;
        background: rgba(79,70,229,0.08);
        border: 1px solid rgba(99,102,241,0.14);
    }

    .rp-schedule-summary__label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #4f46e5;
    }

    .rp-schedule-summary__value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1f2937;
    }

    .rp-optimization-time {
        border: 1px solid #c7d2fe;
        background: #ffffff;
        color: #4338ca;
        border-radius: 9999px;
        padding: 0.55rem 0.9rem;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .rp-optimization-time:hover,
    .rp-optimization-time.is-active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 10px 18px rgba(79,70,229,0.18);
    }

    .rp-download-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .rp-download-btn:hover {
        background: #eef2ff;
        transform: translateY(-1px);
    }

    .rp-close-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: background 0.2s ease;
    }
    .rp-close-btn:hover {
        background: rgba(22, 101, 52, 0.1);
    }

    .rp-secondary-btn:hover {
        background: #e0e7ff !important;
        transform: translateY(-1px);
    }

    .rp-publish-btn {
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.4);
    }

    .rp-alert-success {
        animation: rp-fade-in 0.35s ease;
    }
    @keyframes rp-fade-in {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .rp-preview-carousel {
        position: relative;
        overflow: hidden;
        min-height: 420px;
        background: #f0f2f5;
    }

    .rp-preview-carousel__track {
        display: flex;
        width: 100%;
        min-height: 420px;
        transition: transform 0.35s ease;
    }

    .rp-preview-carousel__slide {
        width: 100%;
        min-height: 420px;
        flex: 0 0 100%;
    }

    .rp-preview-carousel__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        background: rgba(15, 23, 42, 0.7);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .rp-preview-carousel__nav--prev {
        left: 12px;
    }

    .rp-preview-carousel__nav--next {
        right: 12px;
    }

    .rp-preview-carousel__dots {
        position: absolute;
        left: 50%;
        bottom: 14px;
        transform: translateX(-50%);
        display: flex;
        gap: 0.45rem;
        z-index: 2;
    }

    .rp-preview-carousel__dot {
        width: 9px;
        height: 9px;
        border-radius: 9999px;
        background: rgba(255,255,255,0.45);
        border: 1px solid rgba(255,255,255,0.75);
    }

    .rp-preview-carousel__dot.is-active {
        background: #ffffff;
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
        .rp-schedule-grid {
            grid-template-columns: 1fr;
        }
        .rp-schedule-card__header {
            align-items: flex-start;
        }
    }
</style>

<script>
    // Estado de la vista previa
    let previewVisible = true;

    // Función para actualizar la visualización de cuentas
    function updateAccountDisplay() {
        const facebookChecked = document.getElementById('facebook-checkbox').checked;
        const instagramChecked = document.getElementById('instagram-checkbox').checked;
        
        document.getElementById('facebook-account').classList.toggle('hidden', !facebookChecked);
        document.getElementById('instagram-account').classList.toggle('hidden', !instagramChecked);
    }

    function getPreviewScheduleText() {
        if (document.getElementById('schedule-later').checked) {
            const hiddenInput = document.getElementById('schedule-datetime');
            if (hiddenInput?.value) {
                const date = new Date(hiddenInput.value);
                const datePart = new Intl.DateTimeFormat('es-BO', {
                    day: 'numeric',
                    month: 'long'
                }).format(date);
                const timePart = new Intl.DateTimeFormat('es-BO', {
                    hour: 'numeric',
                    minute: '2-digit'
                }).format(date);

                return `${datePart} a las ${timePart}`;
            }
        }

        return 'Ahora mismo';
    }

    // Función para actualizar la vista previa
    function updatePreview() {
        const facebookChecked = document.getElementById('facebook-checkbox').checked;
        const instagramChecked = document.getElementById('instagram-checkbox').checked;
        const contentText = document.getElementById('content').value || "Escribe tu mensaje aquí...";
        const scheduleText = getPreviewScheduleText();
        
        document.getElementById('facebook-preview').classList.toggle('hidden', !facebookChecked);
        document.getElementById('preview-content-facebook').textContent = contentText;
        document.getElementById('preview-facebook-time').textContent = scheduleText;
        
        document.getElementById('instagram-preview').classList.toggle('hidden', !instagramChecked);
        document.getElementById('preview-content-instagram').textContent = contentText;
        document.getElementById('preview-instagram-time').textContent = scheduleText;
    }

    // Función para toggle de vista previa
    function togglePreview() {
        previewVisible = !previewVisible;
        document.getElementById('publication-preview').classList.toggle('hidden', !previewVisible);
        document.getElementById('preview-toggle-text').textContent = previewVisible ? 'Ocultar Vista Previa' : 'Mostrar Vista Previa';
    }

    function formatScheduleDate(date) {
        return new Intl.DateTimeFormat('es-BO', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            hour: 'numeric',
            minute: '2-digit'
        }).format(date);
    }

    function markActivePreset(activePreset) {
        document.querySelectorAll('[data-schedule-preset]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.schedulePreset === activePreset);
        });
    }

    function syncCustomScheduleInputs(fromPreset = null) {
        const hiddenInput = document.getElementById('schedule-datetime');
        const dateInput = document.getElementById('schedule-date-ui');
        const timeInput = document.getElementById('schedule-time-ui');
        const readable = document.getElementById('schedule-readable');

        if (!hiddenInput || !dateInput || !timeInput || !readable) {
            return;
        }

        const hasDate = dateInput.value;
        const hasTime = timeInput.value;

        if (!hasDate || !hasTime) {
            hiddenInput.value = '';
            readable.textContent = 'Selecciona una fecha y hora';
            markActivePreset(null);
            return;
        }

        let composedValue = `${dateInput.value}T${timeInput.value}`;

        if (hiddenInput.min && composedValue < hiddenInput.min) {
            composedValue = hiddenInput.min;
            dateInput.value = composedValue.slice(0, 10);
            timeInput.value = composedValue.slice(11, 16);
        }

        hiddenInput.value = composedValue;

        const selectedDate = new Date(composedValue);
        readable.textContent = formatScheduleDate(selectedDate);
        markActivePreset(fromPreset);
    }

    function initializeSchedulePicker() {
        const hiddenInput = document.getElementById('schedule-datetime');
        const dateInput = document.getElementById('schedule-date-ui');
        const timeInput = document.getElementById('schedule-time-ui');

        if (!hiddenInput || !dateInput || !timeInput) {
            return;
        }

        const now = new Date();
        const minDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        hiddenInput.min = minDateTime;
        dateInput.min = minDateTime.slice(0, 10);

        if (!dateInput.value || !timeInput.value) {
            const defaultDate = new Date(now);
            defaultDate.setSeconds(0, 0);
            if (defaultDate <= now) {
                defaultDate.setMinutes(defaultDate.getMinutes() + 1);
            }

            dateInput.value = defaultDate.toISOString().slice(0, 10);
            timeInput.value = defaultDate.toTimeString().slice(0, 5);
        }

        syncCustomScheduleInputs();
        updatePreview();
    }

    function applySchedulePreset(preset) {
        const dateInput = document.getElementById('schedule-date-ui');
        const timeInput = document.getElementById('schedule-time-ui');

        if (!dateInput || !timeInput) {
            return;
        }

        const base = new Date();
        const presetDate = new Date(base);

        if (preset === 'tomorrow-0900') {
            presetDate.setDate(presetDate.getDate() + 1);
            presetDate.setHours(9, 0, 0, 0);
        }

        if (preset === 'today-0900') {
            presetDate.setHours(9, 0, 0, 0);
            if (presetDate <= base) {
                presetDate.setDate(presetDate.getDate() + 1);
            }
        }

        if (preset === 'today-1800') {
            presetDate.setHours(18, 0, 0, 0);
            if (presetDate <= base) {
                presetDate.setDate(presetDate.getDate() + 1);
            }
        }

        dateInput.value = presetDate.toISOString().slice(0, 10);
        timeInput.value = presetDate.toTimeString().slice(0, 5);
        syncCustomScheduleInputs(preset);
    }

    // Mostrar/ocultar selector de fecha para programación
    document.querySelectorAll('input[name="schedule_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const datetimeContainer = document.getElementById('schedule-datetime-container');
            if (this.value === 'later') {
                datetimeContainer.classList.remove('hidden');
                initializeSchedulePicker();
            } else {
                datetimeContainer.classList.add('hidden');
                updatePreview();
            }
        });
    });

    document.getElementById('schedule-date-ui')?.addEventListener('change', () => { syncCustomScheduleInputs();
        updatePreview(); });
    document.getElementById('schedule-time-ui')?.addEventListener('change', () => { syncCustomScheduleInputs();
        updatePreview(); });
    document.querySelectorAll('[data-schedule-preset]').forEach((button) => {
        button.addEventListener('click', () => { applySchedulePreset(button.dataset.schedulePreset); updatePreview(); });
    });

    let optimizationChart = null;

    function ensureOptimizationChart() {
        const canvas = document.getElementById('optimizationEngagementChart');
        if (!canvas || typeof Chart === 'undefined' || optimizationChart) {
            return;
        }

        optimizationChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['8 am', '10 am', '12 pm', '2 pm', '5 pm', '8 pm'],
                datasets: [
                    {
                        label: 'Instagram',
                        data: [22, 38, 82, 56, 48, 91],
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.12)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4
                    },
                    {
                        label: 'Facebook',
                        data: [18, 35, 76, 51, 44, 84],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.08)',
                        tension: 0.4,
                        borderDash: [6, 6],
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) { return value + '%'; }
                        }
                    }
                }
            }
        });
    }

    function applyOptimizedTime(timeValue) {
        document.getElementById('schedule-later').checked = true;
        document.getElementById('schedule-datetime-container').classList.remove('hidden');
        initializeSchedulePicker();

        const dateInput = document.getElementById('schedule-date-ui');
        const timeInput = document.getElementById('schedule-time-ui');
        const now = new Date();
        const selectedDate = new Date(now);
        const parts = timeValue.split(':');

        selectedDate.setHours(parseInt(parts[0]), parseInt(parts[1]), 0, 0);
        if (selectedDate <= now) {
            selectedDate.setDate(selectedDate.getDate() + 1);
        }

        dateInput.value = selectedDate.toISOString().slice(0, 10);
        timeInput.value = timeValue;
        syncCustomScheduleInputs();
        updatePreview();

        document.querySelectorAll('.rp-optimization-time').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.optimizedTime === timeValue);
        });
    }

    document.getElementById('use-optimization')?.addEventListener('change', function() {
        const panel = document.getElementById('optimization-panel');
        panel.classList.toggle('hidden', !this.checked);
        if (this.checked) {
            ensureOptimizationChart();
        }
    });

    document.querySelectorAll('.rp-optimization-time').forEach((button) => {
        button.addEventListener('click', () => applyOptimizedTime(button.dataset.optimizedTime));
    });

    // Publicar contenido
    function publishContent() {
        const successAlert = document.getElementById('success-alert');
        const platforms = [];
        
        if (document.getElementById('facebook-checkbox').checked) platforms.push('Facebook');
        if (document.getElementById('instagram-checkbox').checked) platforms.push('Instagram');
        
        document.getElementById('published-platforms').textContent = platforms.join(', ') || 'Ninguna plataforma seleccionada';
        
        let publishTime = 'Ahora mismo';
        if (document.getElementById('schedule-later').checked) {
            const datetimeInput = document.getElementById('schedule-datetime');
            const selectedDate = new Date(datetimeInput.value);
            publishTime = selectedDate.toLocaleString();
        }
        document.getElementById('published-time').textContent = publishTime;
        
        successAlert.classList.remove('hidden');
        successAlert.scrollIntoView({ behavior: 'smooth' });
        
        const publishBtn = document.querySelector('button[onclick="publishContent()"]');
        const originalText = publishBtn.innerHTML;
        publishBtn.disabled = true;
        publishBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Publicando...`;
        
        setTimeout(() => {
            publishBtn.disabled = false;
            publishBtn.innerHTML = originalText;
        }, 1500);
    }

    function hideAlert() {
        document.getElementById('success-alert').classList.add('hidden');
    }

    function initializePreviewCarousels() {
        document.querySelectorAll('[data-carousel]').forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]');
            const slides = carousel.querySelectorAll('.rp-preview-carousel__slide');
            const prevButton = carousel.querySelector('[data-carousel-prev]');
            const nextButton = carousel.querySelector('[data-carousel-next]');
            const dots = carousel.querySelectorAll('[data-carousel-dot]');
            let currentIndex = 0;

            if (!track || slides.length <= 1) {
                return;
            }

            const renderCarousel = () => {
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
                dots.forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === currentIndex);
                });
            };

            prevButton?.addEventListener('click', () => {
                currentIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
                renderCarousel();
            });

            nextButton?.addEventListener('click', () => {
                currentIndex = currentIndex === slides.length - 1 ? 0 : currentIndex + 1;
                renderCarousel();
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentIndex = index;
                    renderCarousel();
                });
            });

            renderCarousel();
        });
    }

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        updateAccountDisplay();

        if (document.getElementById('schedule-later').checked) {
            document.getElementById('schedule-datetime-container').classList.remove('hidden');
            initializeSchedulePicker();
        }

        updatePreview();
        initializePreviewCarousels();

        const form = document.getElementById('publishing-form');
        const submitButton = document.getElementById('publish-submit-btn');
        if (form && submitButton) {
            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Procesando...`;
            });
        }

        document.getElementById('publication-preview').classList.remove('hidden');
        document.getElementById('preview-toggle-text').textContent = 'Ocultar Vista Previa';
    });

    // Generar copy con IA
    document.getElementById('generate-copy-btn')?.addEventListener('click', async function() {
        const btn = this;
        const tareaId = btn.dataset.tareaId;
        const contentTextarea = document.getElementById('content');

        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Generando...`;

        try {
            const response = await fetch('{{ route("publicaciones.generate.copy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tarea_id: tareaId })
            });

            if (!response.ok) throw new Error('Error en el servidor.');
            const data = await response.json();

            if (data.success) {
                contentTextarea.value = data.copy;
                updatePreview();
            } else {
                alert('Error: ' + (data.copy || 'No se pudo generar el copy.'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error en la petición. Por favor, inténtalo de nuevo.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-wand-magic-sparkles mr-1"></i> Generar Copy con IA`;
        }
    });
</script>
@endsection
















