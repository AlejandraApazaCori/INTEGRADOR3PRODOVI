@extends('layouts.app')

@section('title', 'Publicar en Redes Sociales')

@section('content')
@php
    $facebookPageName = optional($facebookPage)->display_name ?? optional($facebookPage)->username ?? 'Sin página vinculada';
    $facebookPageInitial = strtoupper(substr($facebookPageName, 0, 1));
    $facebookReady = filled(optional($facebookPage)->provider_user_id) && filled(optional($facebookPage)->access_token);
    $instagramAccountName = optional($instagramAccount)->username ?? optional($instagramAccount)->display_name ?? 'Sin cuenta vinculada';
    $instagramAccountInitial = strtoupper(substr($instagramAccountName, 0, 1));
    $instagramReady = filled(optional($instagramAccount)->provider_user_id) && filled(optional($instagramAccount)->access_token);
    $metaReady = $facebookReady || $instagramReady;
    $selectedPlatforms = old('platforms', $facebookReady ? ['facebook'] : ($instagramReady ? ['instagram'] : []));
    $appUrlHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
    $instagramMediaPublic = parse_url(config('app.url'), PHP_URL_SCHEME) === 'https'
        && ! in_array($appUrlHost, ['localhost', '127.0.0.1', '::1'], true);
    $publicationFormat = $tarea->tipo_contenido
        ? \Illuminate\Support\Str::headline($tarea->tipo_contenido)
        : 'No definido';
@endphp
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="publication-page min-h-screen">
    <div class="publication-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="publication-hero overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="publication-hero-body relative z-10">
                <div class="publication-hero-layout">
                    <div class="publication-hero-copy">
                        <span>Operación de marketing</span>
                        <h1>Preparar publicación</h1>
                        <p>Revisa el contenido aprobado, define el mensaje y elige cuándo compartirlo en las redes del cliente.</p>
                    </div>
                    <nav class="publication-hero-actions" aria-label="Acciones de publicación">
                        <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}" class="publication-hero-action is-primary"><i class="fas fa-folder-open"></i>Entregables</a>
                        @if($tarea->campania)<a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}" class="publication-hero-action"><i class="fas fa-arrow-left"></i>Volver a campaña</a>@endif
                    </nav>
                </div>
            </div>
        </header>

        <section class="publication-context" aria-label="Contexto de la publicación">
            <div><small>Tarea</small><strong>{{ $tarea->titulo }}</strong></div>
            <div><small>Campaña</small><strong>{{ $tarea->campania?->nombre ?? 'Sin campaña' }}</strong></div>
            <div><small>Cliente</small><strong>{{ $cliente?->name ?? 'Sin cliente' }}</strong></div>
            <div><small>Formato</small><strong><i class="fas fa-photo-film"></i>{{ $publicationFormat }}</strong></div>
            <div><small>Contenido aprobado</small><strong>{{ $tarea->archivos->count() }} {{ $tarea->archivos->count() === 1 ? 'archivo' : 'archivos' }}</strong></div>
            <div class="{{ $metaReady ? 'is-ready' : 'is-pending' }}"><small>Conexión con Meta</small><strong><i class="fas fa-circle"></i>{{ $metaReady ? ($facebookReady && $instagramReady ? 'Facebook e Instagram' : ($facebookReady ? 'Facebook disponible' : 'Instagram disponible')) : 'Requiere vinculación' }}</strong></div>
        </section>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden rp-card">
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
                        <div class="publication-step publication-step-platforms mb-6">
                            <span class="publication-step-label">Paso 1 de 4</span>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-users mr-2 text-indigo-400"></i>
                                Cuenta y Plataformas
                            </label>
                            
                            <div class="mb-4 space-y-3" id="account-display">
                                <div id="facebook-account" class="{{ in_array('facebook', $selectedPlatforms, true) ? '' : 'hidden' }} flex items-center space-x-3 p-3 rounded-xl border rp-account-card" style="background: #ffffff; border-color: #e2e8f0;">
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
                                
                                <div id="instagram-account" class="{{ in_array('instagram', $selectedPlatforms, true) ? '' : 'hidden' }} flex items-center space-x-3 p-3 rounded-xl border rp-account-card" style="background: #ffffff; border-color: #e2e8f0;">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm" style="background: linear-gradient(135deg, #e4405f, #c1306d);">
                                        {{ $instagramAccountInitial }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-800">{{ $instagramAccountName }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full font-medium" style="background: #fdf2f8; color: #db2777;">
                                                <i class="fab fa-instagram mr-1"></i>instagram
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3">
                                <label class="rp-checkbox-pill {{ ! $facebookReady ? 'is-disabled' : '' }}" for="facebook-checkbox">
                                    <input id="facebook-checkbox" name="platforms[]" value="facebook" type="checkbox" {{ in_array('facebook', $selectedPlatforms, true) ? 'checked' : '' }} {{ ! $facebookReady ? 'disabled' : '' }} class="h-4 w-4 rounded focus:ring-2" style="accent-color: #1877f2; border-color: #d1d5db;" onchange="updateAccountDisplay(); updatePreview();">
                                    <span class="ml-2 text-sm text-gray-700 flex items-center">
                                        <i class="fab fa-facebook mr-1.5" style="color: #1877f2;"></i>
                                        Facebook
                                        @if(! $facebookReady)<small class="ml-1 text-gray-400">No vinculada</small>@endif
                                    </span>
                                </label>
                                <label class="rp-checkbox-pill {{ ! $instagramReady ? 'is-disabled' : '' }}" for="instagram-checkbox">
                                    <input id="instagram-checkbox" name="platforms[]" value="instagram" type="checkbox" {{ in_array('instagram', $selectedPlatforms, true) ? 'checked' : '' }} {{ ! $instagramReady ? 'disabled' : '' }} class="h-4 w-4 rounded focus:ring-2" style="accent-color: #e4405f; border-color: #d1d5db;" onchange="updateAccountDisplay(); updatePreview();">
                                    <span class="ml-2 text-sm text-gray-700 flex items-center">
                                        <i class="fab fa-instagram mr-1.5" style="color: #e4405f;"></i>
                                        Instagram
                                        @if(! $instagramReady)<small class="ml-1 text-gray-400">No vinculada</small>@endif
                                    </span>
                                </label>
                            </div>
                            @if($instagramReady && ! $instagramMediaPublic)
                                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                    <i class="fas fa-link mr-1"></i>
                                    Instagram está conectado, pero Meta necesita una URL pública HTTPS para descargar el contenido. Configura <strong>APP_URL</strong> con el dominio público antes de publicar.
                                </div>
                            @endif
                        </div>
                        
                        <!-- Contenido multimedia aprobado -->
                        <div class="publication-step publication-step-media mb-6">
                            <span class="publication-step-label">Paso 2 de 4</span>
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
                        <div class="publication-step publication-step-copy mb-6">
                            <span class="publication-step-label">Paso 3 de 4</span>
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
                        <div class="publication-step publication-step-schedule mb-6">
                            <span class="publication-step-label">Paso 4 de 4</span>
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
                                                    <li>Rendimiento histórico de cada cuenta</li>
                                                    <li>Horarios futuros estimados por la LSTM</li>
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
                                    <div id="optimization-panel" data-endpoint="{{ route('administrador.publicaciones.horarios', ['tarea_id' => $tarea->id]) }}" class="hidden mt-4 pt-4" style="border-top: 1px solid rgba(79,70,229,0.12);">
                                        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-2.5 rounded-xl">
                                                    <i class="fas fa-chart-line text-white text-sm"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-800">Predicción de Horarios de Publicación</h3>
                                                    <p class="text-xs text-gray-500">Estimaciones por cuenta y red · próximos 7 días · America/La_Paz</p>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-600 mb-3">Puntaje estimado: reacciones/me gusta + 2 × comentarios. La hora que apliques se usará para todas las redes seleccionadas en esta publicación.</p>
                                            <p id="optimization-status" class="text-sm text-gray-600" role="status" aria-live="polite"></p>
                                            <div id="optimization-results" class="space-y-4"></div>
                                            <button type="button" id="optimization-refresh" class="rp-optimization-time mt-3">Actualizar estimaciones</button>
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
                        <div class="publication-preview-column mb-6" id="publication-preview">
                            <span class="publication-step-label">Vista previa en tiempo real</span>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-eye mr-2 text-indigo-400"></i>
                                Vista Previa
                            </label>
                            <div class="space-y-4">
                                <div id="facebook-preview" class="bg-white overflow-hidden rp-facebook-preview" style="border: 1px solid #dddfe2; border-radius: 0;">
                                    <div class="px-3 pt-3 pb-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-2.5">
                                                <div class="rp-facebook-avatar">{{ $facebookPageInitial }}</div>
                                                <div>
                                                    <div class="flex items-center gap-1 flex-wrap">
                                                        <span class="text-[14px] font-semibold text-black">{{ $facebookPageName }}</span>
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
                                                <div class="rp-facebook-avatar">{{ $instagramAccountInitial }}</div>
                                                <div>
                                                    <div class="flex items-center gap-1 flex-wrap">
                                                        <span class="text-[14px] font-semibold text-black">{{ $instagramAccountName }}</span>
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
                        <div class="publication-form-actions flex flex-col sm:flex-row justify-between items-center gap-4 pt-5 border-t border-gray-200">
                            <button type="button" onclick="togglePreview()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 rp-secondary-btn" style="color: #4f46e5; background: #eef2ff;">
                                <i class="fas fa-eye"></i>
                                <span id="preview-toggle-text">Ocultar Vista Previa</span>
                            </button>
                            <button type="submit" id="publish-submit-btn" {{ ! $metaReady ? 'disabled' : '' }}
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

@if(! $metaReady)
<div class="publication-meta-modal" id="publication-meta-modal" hidden data-auto-open="true">
    <button type="button" class="publication-meta-backdrop" data-close-meta-modal aria-label="Cerrar aviso"></button>
    <section class="publication-meta-dialog" role="alertdialog" aria-modal="true" aria-labelledby="publication-meta-title" aria-describedby="publication-meta-description">
        <div class="publication-meta-icon"><i class="fas fa-share-nodes"></i><span><i class="fas fa-exclamation"></i></span></div>
        <span class="publication-meta-eyebrow">ACCIÓN REQUERIDA</span>
        <h2 id="publication-meta-title">No se puede publicar todavía</h2>
        <p id="publication-meta-description">Este cliente todavía no tiene una página de Facebook ni una cuenta profesional de Instagram autorizadas con un token válido.</p>
        <div class="publication-meta-instruction"><i class="fas fa-circle-info"></i><span>Solicita a <strong>{{ $cliente?->name ?? 'este cliente' }}</strong> que ingrese a su panel y vincule Meta antes de continuar.</span></div>
        <footer>
            <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}"><i class="fas fa-folder-open"></i> Revisar entregables</a>
            <button type="button" data-close-meta-modal>Entendido</button>
        </footer>
    </section>
</div>
@endif

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

<style>
    .publication-page{min-height:100vh;padding:0 0 48px;background:#fff!important;color:#302832;font-family:Inter,'Segoe UI',sans-serif}.publication-shell{width:100%;max-width:none!important;padding:0!important}.publication-hero{position:relative;min-height:180px;display:flex;align-items:center;border-radius:0!important;box-shadow:none}.publication-hero .rp-banner-overlay{background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-repeat:no-repeat}.publication-hero-body{width:100%;padding:30px 48px}.publication-hero-layout{display:flex;align-items:center;justify-content:space-between;gap:28px}.publication-hero-copy{min-width:0;flex:1}.publication-hero-copy>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.publication-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.publication-hero p{max-width:700px;margin:8px 0 0;color:#e0e7ff;font-size:.84rem;line-height:1.55}.publication-hero-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.publication-hero-action{min-height:41px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 14px;border:1px solid rgba(255,255,255,.16);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:900;text-decoration:none;white-space:nowrap;transition:.18s}.publication-hero-action.is-primary{border-color:#fff;background:#fff;color:#4f46e5}.publication-hero-action:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .publication-context{display:grid;grid-template-columns:1.25fr 1fr 1fr .72fr .9fr;margin:18px 24px 0;border:1px solid #e4e7e1;border-radius:1rem;background:linear-gradient(135deg,#fff 0%,#fbf8fc 58%,#f2fbfa 100%);box-shadow:0 7px 18px rgba(61,23,79,.055)}.publication-context>div{min-width:0;padding:13px 15px;border-right:1px solid #e8eae5}.publication-context>div:last-child{border-right:0}.publication-context small,.publication-context strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.publication-context small{color:#8a818e;font-size:.51rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.publication-context strong{margin-top:4px;color:#34303a;font-size:.64rem;font-weight:900}.publication-context strong i{margin-right:6px;font-size:.46rem}.publication-context .is-ready strong{color:#4f7a25}.publication-context .is-pending strong{color:#c45d1b}
    .publication-page .rp-card{margin:18px 24px 0;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;overflow:visible!important}.publication-page .rp-card>.px-8{display:none}.publication-page .rp-card>.p-6{padding:0!important}.publication-page .rp-card>.p-6>div{padding:0!important;border:0!important;background:transparent!important}.publication-page #publishing-form{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(390px,.72fr);align-items:start;gap:14px 18px}.publication-step{grid-column:1;min-width:0;margin:0!important;padding:18px;border:1px solid #e4e7e1;border-radius:.85rem;background:#fff;box-shadow:0 5px 15px rgba(48,40,52,.045)}.publication-step-label{display:block;margin-bottom:6px;color:#8b9288;font-size:.51rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.publication-step>label,.publication-step>div>label,.publication-preview-column>label{margin-bottom:12px!important;color:#302832!important;font-size:.75rem!important;font-weight:900!important}.publication-step>label>i,.publication-step>div>label>i,.publication-preview-column>label>i{color:#117e8c!important}.publication-preview-column{grid-column:2;grid-row:1/5;position:sticky;top:14px;min-width:0;margin:0!important;padding:18px;border:1px solid #e4e7e1;border-radius:.85rem;background:#fff;box-shadow:0 5px 15px rgba(48,40,52,.045)}.publication-page #publishing-form>.mb-4{grid-column:1;margin:0!important}.publication-form-actions{grid-column:1/-1;position:sticky;z-index:15;bottom:0;margin-top:3px;padding:13px 15px!important;border:1px solid #e4e7e1!important;border-radius:.85rem;background:rgba(255,255,255,.96);box-shadow:0 -6px 18px rgba(48,40,52,.055);backdrop-filter:blur(8px)}
    .publication-page .rp-account-card,.publication-page .rp-file-card{border-color:#e5e7eb!important;border-radius:.7rem!important;background:#fff!important;box-shadow:none}.publication-page .rp-account-card:hover,.publication-page .rp-file-card:hover{border-color:#bfdcdf!important;background:#fff!important;transform:translateX(2px)}.publication-page .rp-checkbox-pill{border-color:#e2e7df;border-radius:.65rem;background:#fff}.publication-page textarea{min-height:135px;border-color:#dfe3dd!important;border-radius:.7rem!important;background:#fff!important;color:#374151;font-family:inherit;font-size:.72rem;line-height:1.55}.publication-page textarea:focus,.publication-page input:focus,.publication-page select:focus{border-color:#117e8c!important;box-shadow:0 0 0 3px rgba(17,126,140,.1)!important}.publication-page #generate-copy-btn{border-radius:.6rem!important;background:#e3a122!important;box-shadow:0 5px 13px rgba(227,161,34,.2)!important;font-weight:900}.publication-page #generate-copy-btn:hover{background:#ca8914!important}.publication-page .publication-step-schedule>.grid>div{border-color:#e4e7e1!important;background:#fff!important}.publication-page .rp-schedule-card{border-color:#e1e5df;background:#fafbf9}.publication-page .rp-schedule-card__icon{background:#117e8c}.publication-page .rp-schedule-field__control:focus-within{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.publication-page .rp-schedule-chip:hover,.publication-page .rp-schedule-chip.is-active{border-color:#117e8c;background:#117e8c;color:#fff}.publication-page .rp-facebook-preview{overflow:hidden;border-color:#d9dde1!important;border-radius:.75rem!important;box-shadow:0 7px 18px rgba(15,23,42,.07)}.publication-page .rp-preview-carousel,.publication-page .rp-preview-carousel__track,.publication-page .rp-preview-carousel__slide{min-height:360px}.publication-page #preview-media-facebook,.publication-page #preview-media-instagram{min-height:360px!important}.publication-page .rp-secondary-btn{border:1px solid #dfe4dc;background:#f8faf7!important;color:#687065!important;font-weight:900}.publication-page .rp-secondary-btn:hover{border-color:#117e8c;background:#e9f5f6!important;color:#117e8c!important}.publication-page .rp-publish-btn{border-radius:.65rem!important;background:#117e8c!important;box-shadow:0 7px 17px rgba(17,126,140,.24)!important}.publication-page .rp-publish-btn:hover{background:#0e6c78!important}.publication-page .rp-publish-btn:disabled{cursor:not-allowed;opacity:.5;transform:none}.publication-page .rp-optimization-time:hover,.publication-page .rp-optimization-time.is-active{border-color:#117e8c;background:#117e8c}.publication-page .rp-download-btn:hover{background:#e4f3f4;color:#117e8c}
    @media(max-width:1050px){.publication-hero{min-height:205px}.publication-hero-layout{justify-content:center;flex-direction:column;text-align:center}.publication-hero-actions{justify-content:center}.publication-context{grid-template-columns:repeat(3,minmax(0,1fr))}.publication-context>div:nth-child(3){border-right:0}.publication-context>div:nth-child(-n+3){border-bottom:1px solid #e8eae5}.publication-page #publishing-form{grid-template-columns:1fr}.publication-step,.publication-preview-column{grid-column:1}.publication-preview-column{grid-row:auto;position:relative;top:auto}.publication-form-actions{grid-column:1}}
    @media(max-width:640px){.publication-page{padding-bottom:28px}.publication-hero-body{padding:24px 20px}.publication-hero-actions{width:100%}.publication-hero-action{flex:1}.publication-context{grid-template-columns:1fr;margin:14px 12px 0}.publication-context>div{border-right:0;border-bottom:1px solid #e8eae5}.publication-context>div:last-child{border-bottom:0}.publication-page .rp-card{margin:14px 12px 0}.publication-step,.publication-preview-column{padding:15px}.publication-step-copy>div:nth-child(2){align-items:flex-start;flex-direction:column;gap:8px}.publication-page #generate-copy-btn{width:100%;justify-content:center}.publication-form-actions{position:static;align-items:stretch}.publication-form-actions button{width:100%;justify-content:center}.publication-page .rp-preview-carousel,.publication-page .rp-preview-carousel__track,.publication-page .rp-preview-carousel__slide{min-height:300px}.publication-page #preview-media-facebook,.publication-page #preview-media-instagram{min-height:300px!important}}

    /* Sistema visual Prodovi compartido con campañas y revisión de entregables. */
    .publication-page .rp-banner{background:linear-gradient(135deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(225deg,#789d32 25%,transparent 25%) -50px 0,linear-gradient(315deg,#789d32 25%,transparent 25%),linear-gradient(45deg,#789d32 25%,transparent 25%),linear-gradient(to bottom,#8aae3e 0%,#638522 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#638522}
    .publication-hero .rp-banner-overlay{background:linear-gradient(rgba(26,46,13,.22),rgba(26,46,13,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .publication-hero-body{width:min(1280px,100%);margin:0 auto;padding:30px 24px}.publication-hero-copy>span{color:#edf6df}.publication-hero p{color:#edf6df}.publication-hero-action.is-primary,.publication-hero-action:hover{color:#638522}
    .publication-context,.publication-page .rp-card{width:min(1280px,calc(100% - 48px));margin-right:auto!important;margin-left:auto!important}.publication-context{grid-template-columns:1.2fr 1fr .95fr .62fr .72fr .92fr;overflow:hidden;border-color:#d8e3c7;border-top:4px solid #7da533;background:#fff;box-shadow:0 7px 20px rgba(91,121,38,.075)}.publication-context>div{padding:15px 17px}.publication-context small{color:#7e8778}.publication-context strong{color:#30362e;font-size:.67rem}
    .publication-step,.publication-preview-column{--publication-accent:#7da533;overflow:hidden;border-color:#e1e5de;border-radius:14px;background:linear-gradient(180deg,color-mix(in srgb,var(--publication-accent) 4%,#fff),#fff 74px);box-shadow:0 7px 20px rgba(48,40,52,.055)}.publication-step-platforms{--publication-accent:#5b2b76}.publication-step-media{--publication-accent:#ef6c22}.publication-step-copy{--publication-accent:#117e8c}.publication-step-schedule{--publication-accent:#7da533}.publication-preview-column{--publication-accent:#c94f0c}.publication-step-label{color:var(--publication-accent)}.publication-step>label>i,.publication-step>div>label>i,.publication-preview-column>label>i{color:var(--publication-accent)!important}.publication-step>label,.publication-step>div>label,.publication-preview-column>label{font-size:.78rem!important}.publication-step>label:after,.publication-step>div>label:after,.publication-preview-column>label:after{content:'';display:block;width:42px;height:3px;margin-top:7px;border-radius:999px;background:var(--publication-accent)}
    .publication-step-platforms{background:#fff}.publication-step-platforms .publication-step-label{color:#8b9288}.publication-step-platforms>label:after,.publication-step-platforms>div>label:after{display:none!important}.publication-step-platforms>label>i{color:#697067!important}.publication-step-platforms .rp-checkbox-pill{position:relative;min-height:42px;padding:0 14px 0 11px;border:1px solid #dfe4dc;border-radius:10px;background:#fff;box-shadow:0 2px 7px rgba(48,40,52,.04);transition:.18s}.publication-step-platforms .rp-checkbox-pill:hover{transform:translateY(-1px);box-shadow:0 6px 13px rgba(48,40,52,.08)}.publication-step-platforms .rp-checkbox-pill:has(input:checked){border-color:#b8c9aa;background:#f6f9f2;box-shadow:0 0 0 3px rgba(125,165,51,.1)}.publication-step-platforms .rp-checkbox-pill input{position:relative;width:21px!important;height:21px!important;display:grid;place-items:center;flex:0 0 21px;appearance:none;border:2px solid #b9c0b5!important;border-radius:6px;background:#fff;cursor:pointer;box-shadow:none!important}.publication-step-platforms .rp-checkbox-pill input:checked{border-color:#7da533!important;background:#7da533}.publication-step-platforms .rp-checkbox-pill input:checked:after{content:'\2713';color:#fff;font-size:.72rem;font-weight:900;line-height:1}.publication-step-platforms .rp-checkbox-pill input:focus-visible{outline:3px solid rgba(125,165,51,.2);outline-offset:2px}.publication-step-platforms .rp-checkbox-pill>span{margin-left:8px!important;font-size:.68rem;font-weight:850}.publication-step-platforms .rp-checkbox-pill:has(#facebook-checkbox:checked){border-color:#b7d3f8;background:#f2f7fe;box-shadow:0 0 0 3px rgba(24,119,242,.09)}.publication-step-platforms #facebook-checkbox:checked{border-color:#1877f2!important;background:#1877f2}.publication-step-platforms .rp-checkbox-pill:has(#instagram-checkbox:checked){border-color:#efc3d2;background:#fdf5f8;box-shadow:0 0 0 3px rgba(228,64,95,.08)}.publication-step-platforms #instagram-checkbox:checked{border-color:#e4405f!important;background:#e4405f}
    .publication-step-platforms .rp-checkbox-pill.is-disabled{cursor:not-allowed;opacity:.55;background:#f4f5f3;box-shadow:none}.publication-step-platforms .rp-checkbox-pill.is-disabled:hover{transform:none;box-shadow:none}.publication-step-platforms .rp-checkbox-pill input:disabled{cursor:not-allowed;background:#e5e7eb}
    .publication-page .rp-account-card,.publication-page .rp-file-card{border-color:#e1e5de!important;box-shadow:0 3px 9px rgba(48,40,52,.035)}.publication-page .rp-account-card:hover,.publication-page .rp-file-card:hover{border-color:color-mix(in srgb,var(--publication-accent,#117e8c) 35%,#dfe3dd)!important;transform:translateY(-1px)}.publication-page .rp-checkbox-pill{border-color:#dfe4dc;background:#fafbf9}.publication-page .rp-checkbox-pill:hover{border-color:#7da533;background:#f4f8ee;box-shadow:0 4px 10px rgba(91,121,38,.09)}
    .publication-page #generate-copy-btn{background:linear-gradient(135deg,#f5a900,#ef6c22)!important;box-shadow:0 7px 15px rgba(239,108,34,.23)!important}.publication-page #generate-copy-btn:hover{background:linear-gradient(135deg,#e79f00,#d85b16)!important}.publication-page textarea:focus,.publication-page input:focus,.publication-page select:focus{border-color:#117e8c!important;box-shadow:0 0 0 3px rgba(17,126,140,.11)!important}
    .publication-page .rp-schedule-card{border-color:#d8e3c7;background:linear-gradient(135deg,#fff,#f5f9ef)}.publication-page .rp-schedule-card__icon{background:#7da533;box-shadow:0 7px 16px rgba(125,165,51,.22)}.publication-page .rp-schedule-field__label{color:#638522}.publication-page .rp-schedule-field__control i{color:#7da533}.publication-page .rp-schedule-chip{border-color:#d8e3c7;color:#638522}.publication-page .rp-schedule-chip:hover,.publication-page .rp-schedule-chip.is-active{border-color:#7da533;background:#7da533;box-shadow:0 8px 17px rgba(125,165,51,.22)}.publication-page .rp-schedule-summary{border-color:#d8e3c7;background:#f3f8ec}.publication-page .rp-schedule-summary__label{color:#638522}.publication-page .rp-optimization-time{border-color:#cfe0e2;color:#117e8c}.publication-page .rp-optimization-time:hover,.publication-page .rp-optimization-time.is-active{border-color:#117e8c;background:#117e8c;box-shadow:0 8px 17px rgba(17,126,140,.2)}
    .publication-page .rp-facebook-preview{border-color:#d9ddd6!important;border-radius:12px!important;box-shadow:0 10px 25px rgba(35,45,30,.1)}.publication-page .rp-facebook-avatar{background:linear-gradient(135deg,#7da533,#117e8c)}.publication-page .rp-preview-carousel__dot.is-active{background:#ef6c22;border-color:#ef6c22}.publication-page .rp-download-btn{color:#117e8c}.publication-page .rp-download-btn:hover{background:#e7f4f5;color:#0d6975}
    .publication-form-actions{border-color:#d8e3c7!important;border-radius:14px;background:rgba(255,255,255,.97);box-shadow:0 -7px 22px rgba(48,40,52,.075)}.publication-page .rp-secondary-btn{min-height:43px;border-color:#d8e3c7;background:#f6f9f2!important;color:#638522!important}.publication-page .rp-secondary-btn:hover{border-color:#7da533;background:#edf4e4!important;color:#587923!important}.publication-page .rp-publish-btn{min-width:170px;min-height:46px;border:1px solid #d85b16;border-radius:10px!important;background:linear-gradient(135deg,#ef6c22,#c94f0c)!important;font-size:.72rem;font-weight:900;box-shadow:0 10px 22px rgba(201,79,12,.3)!important}.publication-page .rp-publish-btn:hover{background:linear-gradient(135deg,#e6631d,#b94608)!important;box-shadow:0 14px 27px rgba(201,79,12,.36)!important}.publication-page .rp-publish-btn:disabled{border-color:#d4d8d1;background:#b5bbb1!important;color:#f4f5f3;box-shadow:none!important;opacity:1}
    .publication-page .rp-card .rp-alert-success{border-left:4px solid #7da533!important}.publication-page .rp-card [class*="border-red-200"]{border-left:4px solid #b23e2c!important}.publication-page .rp-card [class*="border-amber-200"]{border-left:4px solid #ef6c22!important}
    .publication-meta-modal{position:fixed;z-index:10300;inset:0;display:grid;place-items:center;padding:20px}.publication-meta-modal[hidden]{display:none}.publication-meta-backdrop{position:absolute;inset:0;border:0;background:rgba(20,25,18,.78);backdrop-filter:blur(5px);cursor:pointer}.publication-meta-dialog{position:relative;width:min(480px,100%);padding:30px;border:1px solid #e5dfe7;border-radius:18px;background:#fff;text-align:center;box-shadow:0 30px 85px rgba(17,24,13,.38);animation:publicationMetaIn .24s ease both}@keyframes publicationMetaIn{from{transform:translateY(14px) scale(.97);opacity:0}to{transform:none;opacity:1}}.publication-meta-icon{position:relative;width:72px;height:72px;display:grid;place-items:center;margin:0 auto 18px;border-radius:22px;background:linear-gradient(135deg,#1877f2,#0d5dc7);color:#fff;font-size:1.8rem;box-shadow:0 13px 26px rgba(24,119,242,.24)}.publication-meta-icon>span{position:absolute;right:-6px;bottom:-6px;width:27px;height:27px;display:grid;place-items:center;border:4px solid #fff;border-radius:50%;background:#ef6c22;color:#fff;font-size:.58rem}.publication-meta-eyebrow{display:block;color:#c94f0c;font-size:.58rem;font-weight:900;letter-spacing:.13em}.publication-meta-dialog h2{margin:7px 0 0;color:#302834;font-size:1.4rem;font-weight:900;letter-spacing:-.025em}.publication-meta-dialog>p{max-width:390px;margin:9px auto 0;color:#756a7a;font-size:.74rem;line-height:1.6}.publication-meta-instruction{display:flex;align-items:flex-start;gap:9px;margin-top:20px;padding:13px 14px;border-left:4px solid #ef6c22;border-radius:8px;background:#fff5ed;color:#6d4a36;text-align:left;font-size:.68rem;line-height:1.55}.publication-meta-instruction>i{margin-top:2px;color:#ef6c22}.publication-meta-dialog footer{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:23px}.publication-meta-dialog footer a,.publication-meta-dialog footer button{min-height:44px;display:flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border-radius:10px;font-size:.67rem;font-weight:900;text-decoration:none;cursor:pointer}.publication-meta-dialog footer a{border:1px solid #d8e3c7;background:#f6f9f2;color:#638522}.publication-meta-dialog footer button{border:1px solid #d85b16;background:linear-gradient(135deg,#ef6c22,#c94f0c);color:#fff;box-shadow:0 8px 17px rgba(201,79,12,.22)}.publication-meta-dialog footer a:hover,.publication-meta-dialog footer button:hover{transform:translateY(-1px)}

    @media(max-width:1050px){.publication-context{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:640px){.publication-context,.publication-page .rp-card{width:calc(100% - 24px)}.publication-context{grid-template-columns:1fr}.publication-hero-body{padding:26px 20px}.publication-step>label:after,.publication-step>div>label:after,.publication-preview-column>label:after{margin-top:6px}.publication-page .rp-publish-btn{min-width:0}.publication-meta-modal{padding:12px}.publication-meta-dialog{padding:25px 18px}.publication-meta-dialog footer{grid-template-columns:1fr}}
</style>

<script>
    // Estado de la vista previa
    let previewVisible = true;

    // Función para actualizar la visualización de cuentas
    function updateAccountDisplay() {
        const facebookChecked = document.getElementById('facebook-checkbox')?.checked ?? false;
        const instagramChecked = document.getElementById('instagram-checkbox')?.checked ?? false;
        
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
        const facebookChecked = document.getElementById('facebook-checkbox')?.checked ?? false;
        const instagramChecked = document.getElementById('instagram-checkbox')?.checked ?? false;
        const noPlatformSelected = !facebookChecked && !instagramChecked;
        const contentText = document.getElementById('content').value || "Escribe tu mensaje aquí...";
        const scheduleText = getPreviewScheduleText();
        
        // La vista previa de referencia permanece visible aunque Meta no esté vinculado.
        document.getElementById('facebook-preview').classList.toggle('hidden', !facebookChecked && !noPlatformSelected);
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

    function publicationLocalNow() {
        const parts = new Intl.DateTimeFormat('sv-SE', {timeZone: 'America/La_Paz', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23'}).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
        return new Date(`${values.year}-${values.month}-${values.day}T${values.hour}:${values.minute}:${values.second}`);
    }

    function initializeSchedulePicker() {
        const hiddenInput = document.getElementById('schedule-datetime');
        const dateInput = document.getElementById('schedule-date-ui');
        const timeInput = document.getElementById('schedule-time-ui');

        if (!hiddenInput || !dateInput || !timeInput) {
            return;
        }

        const now = publicationLocalNow();
        const minDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        hiddenInput.min = minDateTime;
        dateInput.min = minDateTime.slice(0, 10);

        if (!dateInput.value || !timeInput.value) {
            const defaultDate = new Date(now);
            defaultDate.setSeconds(0, 0);
            if (defaultDate <= now) {
                defaultDate.setMinutes(defaultDate.getMinutes() + 1);
            }

            dateInput.value = new Date(defaultDate.getTime() - defaultDate.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
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

        const base = publicationLocalNow();
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

        dateInput.value = new Date(presetDate.getTime() - presetDate.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
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

    document.addEventListener('DOMContentLoaded', function () {
        const metaModal = document.getElementById('publication-meta-modal');
        if (!metaModal || metaModal.dataset.autoOpen !== 'true') return;

        const openMetaModal = () => {
            metaModal.hidden = false;
            document.body.style.overflow = 'hidden';
            metaModal.querySelector('.publication-meta-dialog [data-close-meta-modal]')?.focus();
        };
        const closeMetaModal = () => {
            metaModal.hidden = true;
            document.body.style.overflow = '';
        };

        metaModal.querySelectorAll('[data-close-meta-modal]').forEach(button => button.addEventListener('click', closeMetaModal));
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !metaModal.hidden) closeMetaModal();
        });
        openMetaModal();
    });
</script>
<script src="{{ asset('js/publication-timing.js') }}"></script>
@endsection
















