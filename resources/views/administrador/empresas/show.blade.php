@extends('layouts.app')

@section('title', 'Detalles de la Empresa')

@section('content')
<div class="company-detail-page min-h-screen">
    <div class="company-detail-shell">

        <header class="company-detail-hero rp-banner">
            <div class="rp-banner-overlay"></div>
            <div class="company-detail-hero-content">
                <div class="company-detail-identity">
                    @if($empresa->logo)
                        <div class="company-detail-logo company-detail-logo-image">
                            <img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}">
                        </div>
                    @else
                        <div class="company-detail-logo" aria-hidden="true">
                            <i class="fas fa-building"></i>
                        </div>
                    @endif
                    <div>
                        <span class="company-detail-eyebrow">Administración empresarial</span>
                        <h1>{{ $empresa->nombre_empresa }}</h1>
                        <p>{{ $empresa->tipo_empresa }} <span aria-hidden="true">•</span> {{ $empresa->usuario->name }} ({{ $empresa->usuario->email }})</p>
                    </div>
                </div>
                <div class="company-detail-hero-actions">
                    @if($campaniaActiva)
                        <a href="{{ route('administrador.campañas.show', $campaniaActiva) }}" class="company-detail-campaign">
                            <i class="fas fa-bullhorn"></i>
                            Ver campaña
                        </a>
                    @endif
                    <a href="{{ route('administrador.empresas.index') }}" class="company-detail-back">
                        <i class="fas fa-arrow-left"></i>
                        Volver a empresas
                    </a>
                </div>
            </div>
        </header>

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div class="company-detail-alert mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Contenido principal -->
        <div class="company-detail-content bg-white">
            <!-- Contenido -->
            <div class="company-detail-content-inner p-8">
                <div class="company-detail-layout grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Información Principal -->
                    <main class="company-detail-main lg:col-span-2 space-y-6">
                        <section class="detail-section detail-about">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Información de la Empresa</h2>
                            @if($empresa->descripcion)
                                <p class="text-gray-600 leading-relaxed">{{ $empresa->descripcion }}</p>
                            @else
                                <p class="text-gray-400 italic">No se ha proporcionado una descripción.</p>
                            @endif
                        </section>

                        <!-- Estado del Cuestionario -->
                        <section class="detail-section detail-questionnaire">
                            <div class="detail-summary-heading">
                                <div><span>Documento base</span><h2>Cuestionario empresarial</h2><p>Información utilizada para elaborar el diagnóstico y la estrategia.</p></div>
                                <span class="detail-summary-status {{ $empresa->cuestionario_completado ? '' : 'is-pending' }}"><i class="fas {{ $empresa->cuestionario_completado ? 'fa-circle-check' : 'fa-clock' }}"></i>{{ $empresa->cuestionario_completado ? 'Completado' : 'Pendiente' }}</span>
                            </div>
                            <div class="detail-summary-preview">
                                <span><i class="fas fa-clipboard-list"></i></span>
                                <div><small>{{ $empresa->cuestionario_completado ? 'Información registrada' : 'Información requerida' }}</small><p>{{ $empresa->cuestionario_completado ? 'Las respuestas empresariales están disponibles para consulta, edición y generación de documentos.' : 'Completa las respuestas empresariales antes de generar el resumen ejecutivo y el plan de marketing.' }}</p><a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}">{{ $empresa->cuestionario_completado ? 'Ver respuestas completas' : 'Completar cuestionario' }} <i class="fas fa-arrow-right"></i></a></div>
                            </div>
                            <div class="detail-summary-actions">
                                <a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" class="is-primary"><i class="fas fa-eye"></i>{{ $empresa->cuestionario_completado ? 'Ver cuestionario' : 'Completar cuestionario' }}</a>
                                @if($empresa->cuestionario_completado)
                                    <a href="{{ route('administrador.empresas.cuestionario.pdf', $empresa->id) }}" class="is-pdf"><i class="fas fa-file-pdf"></i>PDF</a>
                                    <button type="button" id="company-detail-drive-open" class="is-doc"><i class="fab fa-google-drive"></i>Google Docs</button>
                                @endif
                            </div>
                        </section>

                        {{-- Sección para mostrar el resumen ejecutivo --}}
                        @if($empresa->resumen_ejecutivo)
                            <section class="detail-section detail-summary">
                                <div class="detail-summary-heading">
                                    <div><span>Documento estratégico</span><h2>Resumen ejecutivo</h2><p>{{ count($resumenSecciones) }} secciones elaboradas a partir del cuestionario empresarial.</p></div>
                                    <span class="detail-summary-status"><i class="fas fa-circle-check"></i>Generado</span>
                                </div>
                                <div class="detail-summary-preview">
                                    <span><i class="fas fa-file-lines"></i></span>
                                    <div><small>Vista resumida</small><p>{{ $resumenVistaPrevia ?: 'El documento está disponible para su consulta.' }}</p><a href="{{ route('administrador.empresas.reporte', $empresa->id) }}">Leer documento completo <i class="fas fa-arrow-right"></i></a></div>
                                </div>
                                <div class="detail-summary-actions">
                                    <a href="{{ route('administrador.empresas.reporte', $empresa->id) }}" class="is-primary"><i class="fas fa-eye"></i>Ver resumen completo</a>
                                    <a href="{{ route('administrador.empresas.editar-resumen', $empresa->id) }}"><i class="fas fa-pen"></i>Editar</a>
                                    <a href="{{ route('administrador.empresas.reporte.pdf', $empresa->id) }}" class="is-pdf"><i class="fas fa-file-pdf"></i>PDF</a>
                                    <form action="{{ route('administrador.empresas.eliminar-resumen', $empresa->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este Resumen Ejecutivo?');">@csrf @method('DELETE')<button type="submit" title="Eliminar resumen"><i class="fas fa-trash-can"></i></button></form>
                                </div>
                            </section>
                        @endif

                        {{-- NUEVA SECCIÓN: Mostrar Planes de Marketing Existentes --}}
                        @if($empresa->planesMarketing->isNotEmpty())
                            <section class="detail-section detail-marketing">
                                <div class="detail-summary-heading">
                                    <div><span>Documento operativo</span><h2>Plan de marketing</h2><p>{{ $empresa->planesMarketing->count() }} {{ $empresa->planesMarketing->count() === 1 ? 'plan generado' : 'planes generados' }} para esta empresa.</p></div>
                                    <span class="detail-summary-status"><i class="fas fa-circle-check"></i>Activo</span>
                                </div>
                                <div class="detail-marketing-list">
                                    @foreach($empresa->planesMarketing as $plan)
                                        <article class="detail-plan-item">
                                            <div class="detail-summary-preview">
                                                <span><i class="fas fa-bullseye"></i></span>
                                                <div><small>{{ $plan->suscripcion->plan->nombre }}</small><p>Plan estratégico creado el {{ $plan->created_at->format('d/m/Y H:i') }}. Suscripción {{ $plan->suscripcion->estado }} y documento {{ $plan->estado }}.</p><a href="{{ route('administrador.empresas.planes-marketing.show', $plan) }}">Consultar plan completo <i class="fas fa-arrow-right"></i></a></div>
                                            </div>
                                            <div class="detail-summary-actions">
                                                <a href="{{ route('administrador.empresas.planes-marketing.show', $plan) }}" class="is-primary"><i class="fas fa-eye"></i>Ver plan completo</a>
                                                <a href="{{ route('administrador.empresas.planes-marketing.edit', $plan) }}"><i class="fas fa-pen"></i>Editar</a>
                                                <a href="{{ route('administrador.empresas.planes-marketing.download-pdf', $plan) }}" class="is-pdf"><i class="fas fa-file-pdf"></i>PDF</a>
                                                <form action="{{ route('administrador.empresas.planes-marketing.destroy', $plan) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este Plan de Marketing?');">@csrf @method('DELETE')<button type="submit" title="Eliminar plan"><i class="fas fa-trash-can"></i></button></form>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                    </main>

                    <!-- Acciones -->
                    <aside class="company-detail-aside lg:col-span-1">
                        <div class="company-actions bg-gray-50 rounded-xl p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h3>
                            <div class="space-y-3">
                                <a href="{{ route('administrador.usuarios.edit', $empresa->usuario_id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Editar Usuario
                                </a>
                                
                                <a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                    @if($empresa->cuestionario_completado)
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Ver Cuestionario
                                    @else
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Ver Cuestionario
                                    @endif
                                </a>

                                {{-- Botón para generar el resumen (SIEMPRE VISIBLE PARA ADMIN) --}}
                                @if($empresa->cuestionario_completado && !$empresa->resumen_ejecutivo)
                                    <button id="generate-summary-btn" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:from-green-700 hover:to-teal-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        Generar Resumen Ejecutivo
                                    </button>
                                @endif

                                {{-- MODIFICADO: Botón para crear plan de marketing --}}
                                @php
                                    // Lógica para determinar si se puede crear un nuevo plan
                                    $puedeCrearPlan = false;
                                    $motivoDeshabilitado = '';

                                    if ($empresa->cuestionario_completado && $empresa->resumen_ejecutivo) {
                                        // Buscar la suscripción más reciente que NO tenga plan de marketing.
                                        $suscripcionActivaSinPlan = $empresa->usuario->suscripciones()
                                            ->whereDoesntHave('planMarketing')
                                            ->latest()
                                            ->first();

                                        if ($suscripcionActivaSinPlan) {
                                            $puedeCrearPlan = true;
                                        } else {
                                            $motivoDeshabilitado = 'No hay una suscripción sin un plan asociado.';
                                        }
                                    } else {
                                        $motivoDeshabilitado = 'El cuestionario y/o el resumen ejecutivo deben estar completados.';
                                    }
                                @endphp

                                @if($puedeCrearPlan)
                                    <a href="{{ route('administrador.empresas.crear-plan', $empresa->id) }}" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Crear Plan de Marketing
                                    </a>
                                @else
                                    <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed" title="{{ $motivoDeshabilitado }}">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Crear Plan de Marketing
                                    </button>
                                @endif

                                <a href="{{ route('administrador.usuarios.view', $empresa->usuario_id) }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Volver al Perfil
                                </a>

                                <form action="{{ route('administrador.empresas.destroy', $empresa->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta empresa? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Eliminar Empresa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="company-detail-drive-modal" class="company-detail-drive-modal hidden" role="dialog" aria-modal="true" aria-labelledby="company-detail-drive-title">
    <div class="company-detail-drive-dialog">
        <div class="company-detail-drive-head">
            <div><h3 id="company-detail-drive-title">Guardar cuestionario en Drive</h3><p>Consulta la ubicación actual o cambia dónde se guardará el documento.</p></div>
            <button type="button" id="company-detail-drive-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('administrador.empresas.cuestionario.google-doc', $empresa->id) }}" method="POST">
            @csrf
            <label class="company-drive-label">Ubicación actual</label>
            <div class="company-detail-drive-current-box">
                <span><i class="fas fa-folder-open"></i></span>
                <div><strong id="company-detail-drive-current">Consultando...</strong><small id="company-detail-drive-detail">Buscando el documento en Drive</small></div>
                <button type="button" id="company-detail-drive-change" class="hidden"><i class="fas fa-location-dot"></i>Cambiar ubicación</button>
            </div>
            <div id="company-detail-drive-editor" class="company-detail-drive-editor hidden">
                <p><i class="fas fa-folder-tree"></i> PRODOVI / Empresas / {{ $empresa->nombre_empresa }}</p>
                <label for="company-detail-drive-folder">Carpeta de destino</label>
                <select id="company-detail-drive-folder" name="folder_id" disabled><option value="">Consultando carpetas...</option></select>
                <div class="company-drive-divider"><span></span><b>o crea una</b><span></span></div>
                <label for="company-detail-drive-new-folder">Nueva subcarpeta</label>
                <div class="company-drive-new"><i class="fas fa-folder-plus"></i><input id="company-detail-drive-new-folder" name="new_folder" type="text" maxlength="80" placeholder="Ej.: Cuestionarios 2026"></div>
            </div>
            <p id="company-detail-drive-status" class="company-detail-drive-status">Consultando las carpetas de la empresa...</p>
            <div class="company-detail-drive-buttons"><button type="button" id="company-detail-drive-cancel">Cancelar</button><button type="submit" id="company-detail-drive-save" disabled><i class="fab fa-google-drive"></i>Guardar y abrir</button></div>
        </form>
    </div>
</div>

<style>
    .company-detail-page {
        min-height: 100vh;
        padding: 20px 0 48px;
        background: #fff;
        color: #302834;
    }

    .company-detail-shell {
        width: 100%;
    }

    .rp-banner {
        position: relative;
        background:
            linear-gradient(135deg, #789d32 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, #789d32 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, #789d32 25%, transparent 25%),
            linear-gradient(45deg, #789d32 25%, transparent 25%),
            linear-gradient(to bottom, #8aae3e 0%, #638522 100%);
        background-color: #638522;
        background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%;
    }

    .company-detail-hero {
        position: relative;
        overflow: hidden;
        width: 100%;
        min-height: 180px;
    }

    .company-detail-hero .rp-banner-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(rgba(26,46,13,.22), rgba(26,46,13,.22)),
            radial-gradient(circle at 0 0, rgba(255,255,255,.2), transparent 50%),
            radial-gradient(circle at 100% 0, rgba(255,255,255,.2), transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,.2), transparent 50%),
            radial-gradient(circle at 0 100%, rgba(255,255,255,.2), transparent 50%);
        background-position: 0 0, 0 0, 100% 0, 100% 100%, 0 100%;
        background-size: 100% 100%, 50% 50%, 50% 50%, 50% 50%, 50% 50%;
        background-repeat: no-repeat;
    }

    .company-detail-hero-content {
        position: relative;
        z-index: 1;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        padding: 30px 48px;
    }

    .company-detail-identity {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .company-detail-logo {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 14px;
        background: rgba(255,255,255,.14);
        color: #fff;
        font-size: 1.45rem;
        backdrop-filter: blur(5px);
    }

    .company-detail-logo-image {
        padding: 7px;
        background: #fff;
    }

    .company-detail-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .company-detail-eyebrow {
        display: block;
        margin-bottom: 7px;
        color: #ecfccb;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .company-detail-hero h1 {
        margin: 0 0 5px;
        color: #fff;
        font-size: clamp(1.55rem, 3vw, 2.25rem);
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -.04em;
    }

    .company-detail-hero p {
        margin: 0;
        color: #f0fdf4;
        font-size: .74rem;
        font-weight: 600;
    }

    .company-detail-hero-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        flex: 0 0 auto;
    }

    .company-detail-back,
    .company-detail-campaign {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 10px 13px;
        border: 1px solid #fff;
        border-radius: .65rem;
        background: #fff;
        color: #638522;
        font-size: .69rem;
        font-weight: 900;
        transition: transform .18s, box-shadow .18s;
    }

    .company-detail-back:hover,
    .company-detail-campaign:hover {
        color: #4f6c1b;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15,23,42,.18);
    }

    .company-detail-alert {
        width: calc(100% - 48px);
        margin: 24px auto 0 !important;
        border-color: #bfe3c5 !important;
        background: #ecf8ee !important;
        color: #276738 !important;
        box-shadow: none;
    }

    .company-detail-content {
        overflow: visible !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .company-detail-content-inner {
        max-width: 1500px;
        margin: 0 auto;
        padding: 34px 48px 0 !important;
    }

    .company-detail-layout {
        grid-template-columns: minmax(0, 1fr) minmax(280px, 340px) !important;
        gap: 48px !important;
    }

    .company-detail-main {
        min-width: 0;
        grid-column: 1;
    }

    .company-detail-aside {
        min-width: 0;
        grid-column: 2;
        align-self: start;
    }

    .detail-section {
        margin: 0 !important;
        padding: 0 0 30px !important;
        border: 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .detail-section + .detail-section {
        padding-top: 30px !important;
    }

    .detail-section:last-child {
        border-bottom: 0 !important;
    }

    .detail-section > h2,
    .detail-section > div:first-child h2 {
        margin: 0 0 16px !important;
        color: #302834 !important;
        font-size: 1.15rem !important;
        font-weight: 900 !important;
    }

    .detail-section > h2::after,
    .detail-section > div:first-child h2::after {
        content: '';
        display: block;
        width: 44px;
        height: 3px;
        margin-top: 8px;
        border-radius: 999px;
        background: #117e8c;
    }

    .detail-about > p {
        max-width: 850px;
        color: #62685f !important;
        font-size: .82rem;
        line-height: 1.75;
    }

    .detail-questionnaire > div {
        border-radius: 12px !important;
        box-shadow: none !important;
    }

    .questionnaire-status-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        padding-left: 18px;
    }

    .questionnaire-status-actions a,
    .questionnaire-status-actions button {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 11px;
        border: 1px solid;
        border-radius: 9px;
        background: #fff;
        font-size: .66rem;
        font-weight: 900;
        transition: transform .18s, background .18s;
    }

    .questionnaire-status-actions a {
        border-color: #f3c4c4;
        color: #b42323;
    }

    .questionnaire-status-actions button {
        border-color: #cfd8f6;
        color: #4f46e5;
    }

    .questionnaire-status-actions a:hover,
    .questionnaire-status-actions button:hover {
        background: #fff;
        transform: translateY(-1px);
    }

    .company-detail-drive-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:16px;background:rgba(17,24,39,.58)}.company-detail-drive-modal.flex{display:flex}.company-detail-drive-dialog{width:100%;max-width:520px;padding:24px;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(0,0,0,.25)}.company-detail-drive-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}.company-detail-drive-head h3{margin:0;color:#1f2937;font-size:1.18rem;font-weight:900}.company-detail-drive-head p{margin:5px 0 0;color:#6b7280;font-size:.74rem}.company-detail-drive-head>button{width:36px;height:36px;display:grid;place-items:center;flex:none;border-radius:50%;color:#6b7280}.company-detail-drive-head>button:hover{background:#f3f4f6}.company-drive-label{display:block;margin-bottom:7px;color:#374151;font-size:.7rem;font-weight:900}.company-detail-drive-current-box{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dce7cc;border-radius:13px;background:#f7faf2}.company-detail-drive-current-box>span{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:11px;background:#e4efd4;color:#638524}.company-detail-drive-current-box>div{min-width:0;flex:1}.company-detail-drive-current-box strong,.company-detail-drive-current-box small{display:block}.company-detail-drive-current-box strong{overflow:hidden;color:#30382b;font-size:.73rem;text-overflow:ellipsis;white-space:nowrap}.company-detail-drive-current-box small{margin-top:3px;color:#7a8275;font-size:.63rem}.company-detail-drive-current-box>button{display:flex;align-items:center;gap:5px;flex:none;padding:7px 9px;border-radius:8px;background:#e6f4f5;color:#0d6975;font-size:.62rem;font-weight:900}.company-detail-drive-editor{margin-top:15px;padding:15px;border:1px solid #dce7cc;border-radius:13px;background:#fbfcf9}.company-detail-drive-editor>p{margin:0 0 13px;color:#638524;font-size:.67rem;font-weight:800}.company-detail-drive-editor label{display:block;margin-bottom:7px;color:#374151;font-size:.7rem;font-weight:900}.company-detail-drive-editor select,.company-detail-drive-editor input{width:100%;height:46px;border:1px solid #d7dce2;border-radius:11px;background:#fff;color:#374151;font-size:.75rem;outline:0}.company-detail-drive-editor select{padding:0 12px}.company-detail-drive-editor select:focus,.company-detail-drive-editor input:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}.company-drive-divider{display:flex;align-items:center;gap:10px;margin:14px 0}.company-drive-divider span{height:1px;flex:1;background:#e5e7eb}.company-drive-divider b{color:#9ca3af;font-size:.58rem;text-transform:uppercase}.company-drive-new{position:relative}.company-drive-new i{position:absolute;top:15px;left:14px;color:#7da533}.company-drive-new input{padding:0 12px 0 40px}.company-detail-drive-status{margin:12px 0 0;color:#6b7280;font-size:.7rem}.company-detail-drive-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:20px}.company-detail-drive-buttons button{min-height:44px;border-radius:11px;font-size:.72rem;font-weight:900}.company-detail-drive-buttons button:first-child{background:#f3f4f6;color:#5f6670}.company-detail-drive-buttons button:last-child{display:flex;align-items:center;justify-content:center;gap:7px;background:#7da533;color:#fff}.company-detail-drive-buttons button:disabled{cursor:not-allowed;opacity:.55}

    .detail-summary > div:first-child,
    .detail-marketing > h2 {
        margin-bottom: 18px !important;
    }

    .detail-summary .prose {
        max-width: 920px;
        color: #565d53 !important;
        line-height: 1.75;
    }

    .detail-summary-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:18px}.detail-summary-heading>div>span{display:block;margin-bottom:5px;color:#7da533;font-size:.6rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.detail-summary-heading h2{margin:0!important;color:#302834!important;font-size:1.15rem!important;font-weight:900}.detail-summary-heading h2:after{content:'';display:block;width:44px;height:3px;margin-top:8px;border-radius:999px;background:#7da533}.detail-summary-heading p{margin:9px 0 0;color:#7b8277;font-size:.68rem}.detail-summary-status{display:inline-flex;align-items:center;gap:6px;flex:none;padding:7px 10px;border-radius:999px;background:#edf7e3;color:#567622;font-size:.62rem;font-weight:900}.detail-summary-status.is-pending{background:#fff5dc;color:#9a6814}.detail-summary-preview{display:flex;align-items:flex-start;gap:14px;padding:17px;border:1px solid #dfe4da;border-radius:13px;background:#fafbf9}.detail-summary-preview>span{width:42px;height:42px;display:grid;place-items:center;flex:none;border-radius:11px;background:#eef5e5;color:#638522}.detail-summary-preview>div{min-width:0}.detail-summary-preview small{display:block;color:#8b9187;font-size:.59rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.detail-summary-preview p{display:-webkit-box;overflow:hidden;margin:6px 0 9px;color:#535a50;font-size:.73rem;line-height:1.65;-webkit-box-orient:vertical;-webkit-line-clamp:3}.detail-summary-preview a{display:inline-flex!important;align-items:center;gap:6px;min-height:0!important;color:#638522!important;font-size:.65rem!important;font-weight:900}.detail-summary-actions{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-top:13px}.detail-summary-actions>a,.detail-summary-actions button{min-height:38px!important;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:8px 11px;border:1px solid #d9dcd6!important;border-radius:9px!important;background:#fff!important;color:#62685f!important;font-size:.66rem!important;font-weight:900}.detail-summary-actions>a.is-primary{border-color:#7da533!important;background:#7da533!important;color:#fff!important}.detail-summary-actions>a.is-pdf{border-color:#f3c4c4!important;color:#b42323!important}.detail-summary-actions button.is-doc{border-color:#cfd8f6!important;color:#3158a5!important}.detail-summary-actions form{margin-left:auto}.detail-summary-actions form button{width:38px;padding:0;color:#b42323!important}.detail-summary-actions>a:hover,.detail-summary-actions button:hover{transform:translateY(-1px)}.detail-marketing-list{display:grid;gap:22px}.detail-plan-item+.detail-plan-item{padding-top:22px;border-top:1px solid #e5e7eb}

    .detail-summary a,
    .detail-summary button,
    .marketing-plan-row a,
    .marketing-plan-row button {
        min-height: 36px;
        border-radius: 9px !important;
        box-shadow: none !important;
        font-size: .68rem !important;
        font-weight: 800;
    }

    .marketing-plan-row {
        gap: 18px;
        padding: 17px 0 !important;
        border: 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .marketing-plan-row:last-child {
        border-bottom: 0 !important;
    }

    .company-actions {
        top: 24px;
        padding: 2px 0 2px 28px !important;
        border-left: 1px solid #e1e3de;
        border-radius: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .company-actions h3 {
        margin-bottom: 16px !important;
        color: #302834 !important;
        font-size: .9rem !important;
        font-weight: 900 !important;
    }

    .company-actions h3::after {
        content: '';
        display: block;
        width: 38px;
        height: 3px;
        margin-top: 7px;
        border-radius: 999px;
        background: #7da533;
    }

    .company-actions a,
    .company-actions button {
        min-height: 46px;
        border: 1px solid #d9dcd6 !important;
        border-radius: 12px !important;
        background: #fff !important;
        color: #4b5148 !important;
        box-shadow: none !important;
        font-size: .72rem;
        font-weight: 800;
    }

    .company-actions a:hover,
    .company-actions button:not(:disabled):hover {
        border-color: #bfc5bc !important;
        background: #f4f5f2 !important;
        color: #302834 !important;
        transform: translateY(-1px);
    }

    .company-actions #generate-summary-btn {
        border-color: #117e8c !important;
        background: #117e8c !important;
        color: #fff !important;
    }

    .company-actions a[href*="crear-plan"] {
        border-color: #7da533 !important;
        background: #7da533 !important;
        color: #fff !important;
    }

    .company-actions button:disabled {
        border-color: #e1e3de !important;
        background: #f1f2ef !important;
        color: #9a9f97 !important;
    }

    .company-actions form:last-child button {
        border-color: #f3c4c4 !important;
        color: #b42323 !important;
    }

    @media (max-width: 760px) {
        .company-detail-layout {
            grid-template-columns: 1fr !important;
            gap: 30px !important;
        }

        .company-detail-main,
        .company-detail-aside {
            grid-column: 1;
        }

        .company-actions {
            position: static !important;
            padding: 28px 0 0 !important;
            border-top: 1px solid #e1e3de;
            border-left: 0;
        }

        .company-actions > div {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .company-detail-page {
            padding-top: 20px;
        }

        .company-detail-hero,
        .company-detail-hero-content {
            min-height: 225px;
        }

        .company-detail-hero-content {
            align-items: stretch;
            flex-direction: column;
            justify-content: center;
            padding: 28px 20px;
        }

        .company-detail-identity {
            align-items: flex-start;
        }

        .company-detail-hero-actions {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .company-detail-back,
        .company-detail-campaign {
            width: 100%;
        }

        .company-detail-alert {
            width: calc(100% - 24px);
        }

        .company-detail-content-inner {
            padding: 28px 20px 0 !important;
        }

        .detail-summary-heading,
        .marketing-plan-row {
            align-items: stretch !important;
            flex-direction: column;
        }

        .detail-summary-actions,
        .marketing-plan-row > div:last-child {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .detail-summary-actions form,
        .detail-summary-actions form button {
            width: 100%;
        }

        .company-actions > div {
            grid-template-columns: 1fr;
        }

        .detail-questionnaire > div {
            align-items: flex-start !important;
            flex-wrap: wrap;
        }

        .questionnaire-status-actions {
            width: 100%;
            margin-left: 0;
            padding: 12px 0 0 44px;
        }
    }
</style>

{{-- Script para manejar la llamada a la API --}}
@if($empresa->cuestionario_completado && !$empresa->resumen_ejecutivo)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-summary-btn');

    if (generateBtn) {
        generateBtn.addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generando...
            `;

            try {
                const response = await fetch(`/empresas/{{ $empresa->id }}/generar-resumen`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor.');
                }

                const data = await response.json();

                if (data.success) {
                    // Recargar la página para mostrar el nuevo resumen
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo generar el resumen.'));
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un error en la petición. Por favor, inténtalo de nuevo.');
            } finally {
                // Restaurar el botón en caso de error
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Generar Resumen Ejecutivo
                `;
            }
        });
    }
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal=document.getElementById('company-detail-drive-modal'),open=document.getElementById('company-detail-drive-open'),folder=document.getElementById('company-detail-drive-folder'),newFolder=document.getElementById('company-detail-drive-new-folder'),current=document.getElementById('company-detail-drive-current'),detail=document.getElementById('company-detail-drive-detail'),change=document.getElementById('company-detail-drive-change'),editor=document.getElementById('company-detail-drive-editor'),status=document.getElementById('company-detail-drive-status'),save=document.getElementById('company-detail-drive-save');
    const foldersUrl=@json(route('administrador.empresas.cuestionario.drive-folders',$empresa->id));
    const closeModal=()=>{modal.classList.add('hidden');modal.classList.remove('flex');document.body.classList.remove('overflow-hidden')};
    open?.addEventListener('click',async()=>{
        modal.classList.remove('hidden');modal.classList.add('flex');document.body.classList.add('overflow-hidden');folder.innerHTML='<option value="">Consultando carpetas...</option>';folder.disabled=true;newFolder.value='';save.disabled=true;editor.classList.add('hidden');change.classList.add('hidden');current.textContent='Consultando...';detail.textContent='Buscando el documento en Drive';status.textContent='Consultando PRODOVI / Empresas / {{ $empresa->nombre_empresa }}...';status.style.color='';
        try{
            const response=await fetch(foldersUrl,{headers:{Accept:'application/json'}}),data=await response.json();if(!response.ok)throw new Error(data.message||'No se pudieron consultar las carpetas.');
            folder.innerHTML='';const locations=[{id:data.root.id,name:`${data.root.name} (carpeta principal)`},...data.folders];locations.forEach(item=>{const option=document.createElement('option');option.value=item.id;option.textContent=item.name;folder.appendChild(option)});
            if(data.current_folder){if(!locations.some(item=>item.id===data.current_folder.id)){const option=document.createElement('option');option.value=data.current_folder.id;option.textContent=data.current_folder.name;folder.appendChild(option)}folder.value=data.current_folder.id;current.textContent=data.current_folder.name;detail.textContent='El cuestionario ya está guardado en esta carpeta';change.classList.remove('hidden');status.textContent='Al guardar se actualizará el documento registrado y conservará su ubicación.'}
            else{folder.value=data.root.id;current.textContent='Documento aún no creado';detail.textContent='Elige dónde guardarlo por primera vez';editor.classList.remove('hidden');status.textContent='Puedes guardarlo en la carpeta principal de la empresa o crear una subcarpeta.'}
            folder.disabled=false;save.disabled=false;
        }catch(error){current.textContent='No se pudo consultar Drive';detail.textContent='Inténtalo nuevamente';status.textContent=error.message;status.style.color='#b91c1c'}
    });
    change?.addEventListener('click',()=>{editor.classList.remove('hidden');change.classList.add('hidden');status.textContent='Selecciona otra carpeta o crea una subcarpeta dentro de la empresa.'});
    document.getElementById('company-detail-drive-close')?.addEventListener('click',closeModal);document.getElementById('company-detail-drive-cancel')?.addEventListener('click',closeModal);modal?.addEventListener('click',event=>{if(event.target===modal)closeModal()});document.addEventListener('keydown',event=>{if(event.key==='Escape'&&modal?.classList.contains('flex'))closeModal()});
});
</script>
@endsection
