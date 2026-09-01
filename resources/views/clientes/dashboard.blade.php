@extends('layouts.app2')

@section('title', 'Dashboard del Cliente')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    $suscripcionActiva->loadMissing('plan.planCaracteristicas.caracteristica');
    $plan = $suscripcionActiva->plan;
    $planCaracteristicas = $plan?->planCaracteristicas ?? collect();
    $dashboardFacebookAccount = $dashboardSocialAccounts->get('facebook');
    $dashboardFacebookPage = $dashboardSocialAccounts->get('facebook_page');
    $dashboardInstagramAccount = $dashboardSocialAccounts->get('instagram');
    $dashboardFacebookLinked = filled($dashboardFacebookAccount?->provider_user_id) || filled($dashboardFacebookPage?->provider_user_id);
    $dashboardInstagramLinked = filled($dashboardInstagramAccount?->provider_user_id);
    $dashboardHasSocialAccounts = $dashboardFacebookLinked || $dashboardInstagramLinked;
    $dashboardFacebookName = $dashboardFacebookPage?->display_name
        ?? data_get($dashboardFacebookPage?->metadata, 'page_name')
        ?? $dashboardFacebookAccount?->display_name
        ?? $dashboardFacebookAccount?->username
        ?? 'Facebook';
    $dashboardInstagramName = $dashboardInstagramAccount?->display_name
        ?? $dashboardInstagramAccount?->username
        ?? 'Instagram';
    $dashboardFacebookIdentifier = $dashboardFacebookPage?->provider_user_id
        ?? $dashboardFacebookAccount?->provider_user_id
        ?? $dashboardFacebookAccount?->username;
    $dashboardInstagramUsername = ltrim((string) $dashboardInstagramAccount?->username, '@');
    $dashboardInstagramTooltip = filled($dashboardInstagramUsername)
        ? '@'.$dashboardInstagramUsername
        : $dashboardInstagramName;
    $dashboardFacebookUrl = filled($dashboardFacebookIdentifier)
        ? 'https://www.facebook.com/'.rawurlencode($dashboardFacebookIdentifier)
        : 'https://www.facebook.com/';
    $dashboardInstagramUrl = filled($dashboardInstagramUsername)
        ? 'https://www.instagram.com/'.rawurlencode($dashboardInstagramUsername).'/'
        : 'https://www.instagram.com/';
@endphp

<div id="client-dashboard" class="min-h-screen">
    <div class="dashboard-shell space-y-8">
        
        <!-- Banner con fondo geométrico -->
        <div class="client-hero relative">
            <div class="hero-content">
                <span class="hero-kicker">Centro de operaciones</span>
                <h1>Hola, <span>{{ $user->name }}</span></h1>
                <p>Todo lo que PRODOVI está construyendo para tí, organizado en un solo lugar.</p>
            </div>
            <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>

        <section class="dashboard-metrics" aria-label="Resumen de tu servicio">
            <article class="active-company-metric">
                <i class="fas fa-building"></i>
                <span><small>Empresa</small><strong>{{ $empresaActiva?->nombre_empresa ?? 'Por registrar' }}</strong></span>
                @if($dashboardCompanies->count() > 1)
                    <details class="company-options">
                        <summary aria-label="Opciones de empresa" title="Cambiar empresa"><i class="fas fa-ellipsis-vertical"></i></summary>
                        <div class="company-options-menu">
                            <small>Cambiar empresa</small>
                            @foreach($dashboardCompanies as $dashboardCompany)
                                <a href="{{ route('clientes.dashboard', ['empresa' => $dashboardCompany->id]) }}" class="{{ (int) $empresaActiva->id === (int) $dashboardCompany->id ? 'is-current' : '' }}">
                                    <i class="fas {{ (int) $empresaActiva->id === (int) $dashboardCompany->id ? 'fa-circle-check' : 'fa-building' }}"></i>
                                    <span>{{ $dashboardCompany->nombre_empresa }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </article>
            <article><i class="fas fa-crown"></i><span><small>Plan contratado</small><strong>{{ $plan->nombre }}</strong></span></article>
            <article class="social-networks-metric">
                <i class="fas fa-share-nodes"></i>
                <div class="social-metric-content">
                    <small>Redes sociales</small>
                    @if($dashboardHasSocialAccounts)
                        <div class="social-metric-links">
                            @if($dashboardFacebookLinked)<a class="social-pill facebook" href="{{ $dashboardFacebookUrl }}" target="_blank" rel="noopener noreferrer" data-tooltip="{{ $dashboardFacebookName }}" aria-label="Abrir Facebook: {{ $dashboardFacebookName }}"><i class="fab fa-facebook-f"></i> Facebook</a>@endif
                            @if($dashboardInstagramLinked)<a class="social-pill instagram" href="{{ $dashboardInstagramUrl }}" target="_blank" rel="noopener noreferrer" data-tooltip="{{ $dashboardInstagramTooltip }}" aria-label="Abrir Instagram: {{ $dashboardInstagramTooltip }}"><i class="fab fa-instagram"></i> Instagram</a>@endif
                        </div>
                        <button type="button" id="open-dashboard-social" class="edit-social-links" aria-label="Editar redes vinculadas" title="Editar redes vinculadas"><i class="fas fa-pen"></i></button>
                    @else
                        <button type="button" id="open-dashboard-social" class="connect-social-links"><i class="fas fa-link"></i> Vincular cuentas</button>
                    @endif
                </div>
            </article>
            <article><i class="fas fa-calendar-check"></i><span><small>Tiempo disponible</small>@if($diasRestantes !== null)<strong>{{ $diasRestantes }} días</strong>@else<strong class="time-pending-label">No definido hasta que comience la campaña</strong>@endif</span></article>
            <article><i class="fas fa-signal"></i><span><small>Estado del servicio</small><strong class="capitalize">{{ $suscripcionActiva->estado }}</strong></span></article>
        </section>

        @if($campaniaDashboard && $campaignDashboardSummary)
            @php
                $campaignStateLabel = match($campaniaDashboard->estado) {
                    'activa' => 'En curso',
                    'pausada' => 'Pausada',
                    'finalizada' => 'Finalizada',
                    'borrador' => 'En preparación',
                    default => ucfirst($campaniaDashboard->estado),
                };
                $dashboardReviewFiles = $campaignDashboardSummary['pending_review_files'];
            @endphp
            <section class="campaign-command" aria-labelledby="campaign-command-title">
                <div class="campaign-command-body">
                    <div class="campaign-progress-summary">
                        <div class="campaign-progress-copy">
                            <small>Avance de campaña</small>
                            <strong>{{ $campaignDashboardSummary['progress'] }}%</strong>
                            <span>{{ $campaignDashboardSummary['completed_tasks'] }} de {{ $campaignDashboardSummary['total_tasks'] }} tareas</span>
                        </div>
                        <div class="campaign-progress-track" role="progressbar" aria-valuenow="{{ $campaignDashboardSummary['progress'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $campaignDashboardSummary['progress'] }}%"></span></div>
                        <div class="campaign-period"><i class="far fa-calendar"></i><span><strong>{{ \Carbon\Carbon::parse($campaniaDashboard->fecha_inicio)->format('d M') }} — {{ \Carbon\Carbon::parse($campaniaDashboard->fecha_fin)->format('d M Y') }}</strong></span></div>
                    </div>

                    @if($dashboardHasSocialAccounts)
                        <section class="dashboard-results-panel is-loading" id="dashboard-meta-results" data-meta-url="{{ route('clientes.analiticas.empresa.datos', ['empresa' => $empresaActiva->id, 'days' => 'all']) }}" data-meta-fallback-url="{{ route('clientes.analiticas.load-view', ['meta' => 1, 'empresa_id' => $empresaActiva->id, 'days' => 'all']) }}" aria-labelledby="dashboard-results-title" aria-busy="true">
                            <header><div><h3 id="dashboard-results-title">Resultados hasta hoy</h3></div><a href="{{ route('clientes.analiticas') }}">Ver analíticas <i class="fas fa-arrow-right"></i></a></header>
                            <div class="dashboard-results-grid">
                                <article><span><i class="fas fa-signal"></i></span><div><small>Alcance</small><strong data-meta-total="reach">—</strong><em>Todo el historial</em></div></article>
                                <article><span><i class="fas fa-heart"></i></span><div><small>Engagement</small><strong data-meta-total="engagement">—</strong><em>Todo el historial</em></div></article>
                                <article><span><i class="fas fa-photo-film"></i></span><div><small>Publicaciones</small><strong data-meta-total="posts">—</strong><em>Todo el historial</em></div></article>
                                <article><span><i class="fas fa-chart-line"></i></span><div><small>Promedio / post</small><strong data-meta-total="average_engagement">—</strong><em>Todo el historial</em></div></article>
                            </div>
                            <div class="dashboard-results-loading" data-meta-loading><i class="fas fa-circle-notch fa-spin"></i><span>Consultando Meta Insights</span></div>
                        </section>
                    @endif

                    <div class="campaign-agenda-layout">
                    <div class="campaign-agenda" id="campaign-agenda">
                        <div class="campaign-agenda-head"><div><h3>Agenda</h3></div><button type="button" id="campaign-calendar-toggle" aria-pressed="false"><i class="far fa-calendar-alt"></i> <span>Ver calendario</span></button></div>
                        @if($campaignDashboardSummary['upcoming_tasks']->isNotEmpty())
                            <div class="campaign-agenda-list">
                                @foreach($campaignDashboardSummary['upcoming_tasks'] as $task)
                                    @php
                                        $agendaDate = \Carbon\Carbon::parse($task->dashboard_date);
                                        $taskStateLabel = match($task->estado) {'en_progreso' => 'En progreso', 'completada' => 'Completada', 'rechazada' => 'En ajustes', default => 'Pendiente'};
                                    @endphp
                                    <article>
                                        <time datetime="{{ $agendaDate->toDateString() }}"><strong>{{ $agendaDate->format('d') }}</strong><small>{{ strtoupper($agendaDate->translatedFormat('M')) }}</small></time>
                                        <span class="campaign-agenda-dot"></span>
                                        <div><small>{{ $task->publication_scheduled_at ? 'Publicación programada' : 'Entrega prevista' }}</small><strong>{{ $task->titulo }}</strong><p>{{ $task->entregable ?: 'El equipo actualizará los detalles de esta actividad.' }}</p></div>
                                        <em class="is-{{ $task->estado }}">{{ $taskStateLabel }}</em>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="campaign-agenda-empty"><i class="far fa-calendar-check"></i><span><strong>Aún no hay próximas fechas visibles</strong><small>El equipo agregará aquí las entregas y publicaciones confirmadas.</small></span></div>
                        @endif
                        <div class="campaign-calendar" id="campaign-calendar" hidden>
                            <div class="campaign-calendar-toolbar">
                                <button type="button" data-calendar-direction="prev" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                                <strong id="campaign-calendar-month"></strong>
                                <button type="button" data-calendar-direction="next" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div class="campaign-calendar-weekdays" aria-hidden="true"><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span></div>
                            <div class="campaign-calendar-grid" id="campaign-calendar-grid"></div>
                        </div>
                    </div>
                    <aside class="campaign-social-followers" aria-label="Seguidores en redes sociales">
                        @if($dashboardHasSocialAccounts)
                            <article class="facebook">
                                <span><i class="fab fa-facebook-f"></i></span>
                                <div><small>Facebook</small><strong data-meta-followers="facebook">—</strong><em>{{ $dashboardFacebookLinked ? 'seguidores' : 'No vinculada' }}</em></div>
                            </article>
                            <article class="instagram">
                                <span><i class="fab fa-instagram"></i></span>
                                <div><small>Instagram</small><strong data-meta-followers="instagram">—</strong><em>{{ $dashboardInstagramLinked ? 'seguidores' : 'No vinculada' }}</em></div>
                            </article>
                        @else
                            <button type="button" data-open-dashboard-social class="campaign-social-connect"><span><i class="fas fa-link"></i></span><strong>Vincular cuentas</strong><small>Conecta Facebook e Instagram</small></button>
                        @endif
                    </aside>
                    </div>

                    @if(session('dashboard_review_success'))
                        <div class="dashboard-review-notice"><i class="fas fa-circle-check"></i>{{ session('dashboard_review_success') }}</div>
                    @endif

                    @if($dashboardReviewFiles->isNotEmpty())
                        <div class="dashboard-client-focus is-single">
                                <section class="dashboard-review-panel" aria-labelledby="dashboard-review-title">
                                    <header><div><small>Tu aprobación</small><h3 id="dashboard-review-title">Piezas para revisar</h3></div><span>{{ $dashboardReviewFiles->count() }}</span></header>
                                    <div class="dashboard-review-list">
                                        @foreach($dashboardReviewFiles as $reviewItem)
                                            @php
                                                $reviewTask = $reviewItem['task'];
                                                $reviewFile = $reviewItem['file'];
                                                $reviewExtension = strtolower($reviewFile->extension ?: pathinfo($reviewFile->nombre_original, PATHINFO_EXTENSION));
                                                $reviewIsImage = in_array($reviewExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
                                                $reviewIsVideo = in_array($reviewExtension, ['mp4', 'mov', 'webm'], true);
                                            @endphp
                                            <article>
                                                <button type="button" data-open-piece-review="piece-review-{{ $reviewFile->id }}">
                                                    <span class="dashboard-review-thumb">
                                                        @if($reviewIsImage)
                                                            <img src="{{ Storage::url($reviewFile->ruta_archivo) }}" alt="{{ $reviewFile->nombre_original }}" loading="lazy">
                                                        @elseif($reviewIsVideo)
                                                            <i class="fas fa-circle-play"></i>
                                                        @else
                                                            <i class="fas fa-file"></i>
                                                        @endif
                                                    </span>
                                                    <span class="dashboard-review-copy"><small>{{ ucfirst($reviewTask->tipo_contenido ?: 'Contenido') }}</small><strong>{{ $reviewTask->titulo }}</strong><em>Ver y responder</em></span>
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                        </div>
                    @endif
                </div>
            </section>

            @foreach($dashboardReviewFiles as $reviewItem)
                @php
                    $reviewTask = $reviewItem['task'];
                    $reviewFile = $reviewItem['file'];
                    $reviewExtension = strtolower($reviewFile->extension ?: pathinfo($reviewFile->nombre_original, PATHINFO_EXTENSION));
                    $reviewIsImage = in_array($reviewExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
                    $reviewIsVideo = in_array($reviewExtension, ['mp4', 'mov', 'webm'], true);
                    $reviewIsVertical = in_array(strtolower((string) $reviewTask->tipo_contenido), ['historia', 'reel'], true);
                    $reviewPlatforms = collect($reviewTask->publication_platforms ?: $campaniaDashboard->canales ?: [])->map(fn ($platform) => strtolower((string) $platform))->unique();
                @endphp
                <div class="piece-review-modal" id="piece-review-{{ $reviewFile->id }}" hidden>
                    <button type="button" class="piece-review-backdrop" data-close-piece-review aria-label="Cerrar revisión"></button>
                    <section class="piece-review-dialog" role="dialog" aria-modal="true" aria-labelledby="piece-review-title-{{ $reviewFile->id }}">
                        <header>
                            <div><small>PIEZA PARA APROBACIÓN</small><h2 id="piece-review-title-{{ $reviewFile->id }}">{{ $reviewTask->titulo }}</h2></div>
                            <button type="button" data-close-piece-review aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
                        </header>
                        <div class="piece-review-body">
                            <div class="piece-publication-preview">
                                <div class="piece-platforms">
                                    @if($reviewPlatforms->contains(fn ($platform) => str_contains($platform, 'facebook')))<span class="facebook"><i class="fab fa-facebook-f"></i> Facebook</span>@endif
                                    @if($reviewPlatforms->contains(fn ($platform) => str_contains($platform, 'instagram')))<span class="instagram"><i class="fab fa-instagram"></i> Instagram</span>@endif
                                    @if($reviewPlatforms->isEmpty())<span><i class="fas fa-share-nodes"></i> Red por confirmar</span>@endif
                                </div>
                                <article class="piece-social-post {{ $reviewIsVertical ? 'is-vertical' : '' }}">
                                    <header><span>{{ strtoupper(mb_substr($empresaActiva->nombre_empresa, 0, 1)) }}</span><div><strong>{{ $empresaActiva->nombre_empresa }}</strong><small>Vista previa de publicación</small></div><i class="fas fa-ellipsis"></i></header>
                                    <div class="piece-social-media">
                                        @if($reviewIsImage)
                                            <img src="{{ Storage::url($reviewFile->ruta_archivo) }}" alt="{{ $reviewFile->nombre_original }}">
                                        @elseif($reviewIsVideo)
                                            <video src="{{ Storage::url($reviewFile->ruta_archivo) }}" controls playsinline preload="metadata"></video>
                                        @else
                                            <div class="piece-file-fallback"><i class="fas fa-file-arrow-down"></i><strong>{{ $reviewFile->nombre_original }}</strong><a href="{{ Storage::url($reviewFile->ruta_archivo) }}" target="_blank" rel="noopener">Abrir archivo</a></div>
                                        @endif
                                    </div>
                                    <div class="piece-social-actions"><span><i class="far fa-heart"></i><i class="far fa-comment"></i><i class="far fa-paper-plane"></i></span><i class="far fa-bookmark"></i></div>
                                    <p><strong>{{ $empresaActiva->nombre_empresa }}</strong> {{ $reviewFile->descripcion ?: $reviewTask->descripcion ?: 'Contenido preparado por el equipo para tu campaña.' }}</p>
                                </article>
                            </div>
                            <aside class="piece-review-sidebar">
                                <div class="piece-review-context"><small>Tarea</small><strong>{{ $reviewTask->titulo }}</strong><p>{{ $reviewTask->entregable ?: 'Pieza de contenido pendiente de aprobación.' }}</p></div>
                                @if($reviewTask->comentarios->isNotEmpty())
                                    <div class="piece-review-comments"><small>Comentarios recientes</small>@foreach($reviewTask->comentarios->take(3) as $comment)<article><strong>{{ $comment->user?->name ?? 'Usuario' }}</strong><p>{{ $comment->contenido }}</p></article>@endforeach</div>
                                @endif
                                <form method="POST" action="{{ route('clientes.tareas.archivos.revision', $reviewFile) }}" class="piece-review-form">
                                    @csrf
                                    <label><span>Comentario para el equipo <small>(opcional)</small></span><textarea name="comentario" rows="4" maxlength="2000" placeholder="Indica cambios o deja una observación sobre esta tarea..."></textarea></label>
                                    <div class="piece-review-actions">
                                        <button type="submit" class="is-comment" data-submit-comment><i class="far fa-comment"></i> Comentar</button>
                                        <button type="submit" name="estado" value="rechazado" class="is-change"><i class="fas fa-rotate-left"></i> Solicitar cambios</button>
                                        <button type="submit" name="estado" value="aprobado" class="is-approve"><i class="fas fa-check"></i> Aprobar pieza</button>
                                    </div>
                                </form>
                            </aside>
                        </div>
                    </section>
                </div>
            @endforeach
        @else
            <section class="campaign-progress" aria-labelledby="campaign-progress-title">
                <div class="campaign-progress-icon" aria-hidden="true"><i class="fas fa-gears"></i></div>
                <h2 id="campaign-progress-title">Estamos preparando tu campaña mensual</h2>
                <p>Cuando la campaña esté lista, encontrarás aquí su avance, próximas entregas y acciones pendientes.</p>
            </section>
        @endif

    </div>
</div>

@if($pendingSetupSubscription)
    <div id="payment-approved-modal" class="payment-approved-modal" role="dialog" aria-modal="true" aria-labelledby="payment-approved-title">
        <div class="payment-approved-backdrop"></div>
        <div class="payment-approved-dialog">
            <span class="payment-approved-icon"><i class="fas fa-circle-check"></i></span>
            <small>PAGO CONFIRMADO</small>
            <h2 id="payment-approved-title">Tu pago ha sido aprobado</h2>
            <p>Gracias por seguir confiando en PRODOVI. Tu plan <strong>{{ $pendingSetupSubscription->plan?->nombre }}</strong> ya está disponible; solo falta preparar los datos de tu nueva empresa.</p>
            <a href="{{ route('clientes.onboarding', ['suscripcion' => $pendingSetupSubscription->id, 'inicio' => 1]) }}">Realizar configuración <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
@endif

<div id="plan-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity backdrop-blur-sm" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/50"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white" id="modal-plan-title">Detalles del Plan</h3>
                    <button type="button" id="close-modal" class="text-white/80 hover:text-white transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="bg-white px-6 py-6">
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ciclo de facturación
                    </h4>
                    <p class="text-gray-700 font-medium" id="modal-plan-dates"></p>
                    <p class="text-sm mt-1" id="modal-plan-status"></p>
                </div>

                <div class="mb-6">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descripción
                    </h4>
                    <p class="text-gray-600 leading-relaxed" id="modal-plan-description"></p>
                </div>

                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Características incluidas
                    </h4>
                    <div class="space-y-2" id="modal-plan-features"></div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" id="close-modal-footer" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dashboard-social-modal" class="dashboard-social-modal hidden" role="dialog" aria-modal="true" aria-labelledby="dashboard-social-title">
    <div class="dashboard-social-backdrop" data-close-dashboard-social></div>
    <div class="dashboard-social-dialog">
        <header>
            <span><i class="fas fa-share-nodes"></i></span>
            <div><small>CANALES DE TU EMPRESA</small><h3 id="dashboard-social-title">Vincular redes sociales</h3></div>
            <button type="button" data-close-dashboard-social aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <div class="dashboard-social-body">
            <div class="dashboard-social-company"><i class="fas fa-building"></i> Configurarás las redes de <strong>{{ $empresaActiva->nombre_empresa }}</strong></div>
            @if(session('social_accounts_success'))<div class="dashboard-social-notice success"><i class="fas fa-circle-check"></i><span>{{ session('social_accounts_success') }}</span></div>@endif
            @if(session('social_accounts_error'))<div class="dashboard-social-notice error"><i class="fas fa-circle-exclamation"></i><span>{{ session('social_accounts_error') }}</span></div>@endif
            <p>Conecta Facebook y sincroniza automáticamente la cuenta profesional de Instagram asociada a la misma página.</p>
            <div class="dashboard-social-options">
                <a class="dashboard-social-option facebook {{ $dashboardFacebookLinked ? 'is-linked' : '' }}" href="{{ route('clientes.social.redirect', ['provider' => 'facebook', 'empresa_id' => $empresaActiva->id, 'return_to' => 'dashboard']) }}">
                    <div><span><i class="fab fa-facebook-f"></i></span><b>{{ $dashboardFacebookLinked ? 'Vinculado' : 'Disponible' }}</b></div>
                    <h4>Facebook</h4><p>Autoriza la página de esta empresa para conectarla con PRODOVI.</p>
                    @if($dashboardFacebookLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $dashboardFacebookName }}</strong></span></aside>@endif
                    <em>{{ $dashboardFacebookLinked ? 'Volver a conectar' : 'Conectar con Facebook' }} <i class="fas fa-arrow-right"></i></em>
                </a>
                <a class="dashboard-social-option instagram {{ $dashboardInstagramLinked ? 'is-linked' : '' }} {{ !$dashboardFacebookLinked ? 'is-disabled' : '' }}" href="{{ $dashboardFacebookLinked ? route('clientes.social.redirect', ['provider' => 'instagram', 'empresa_id' => $empresaActiva->id, 'return_to' => 'dashboard']) : '#' }}" aria-disabled="{{ $dashboardFacebookLinked ? 'false' : 'true' }}">
                    <div><span><i class="fab fa-instagram"></i></span><b>{{ $dashboardInstagramLinked ? 'Vinculado' : ($dashboardFacebookLinked ? 'Disponible' : 'Bloqueado') }}</b></div>
                    <h4>Instagram</h4><p>Sincroniza el perfil profesional asociado a la página de Facebook.</p>
                    @if($dashboardInstagramLinked)<aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $dashboardInstagramName }}</strong></span></aside>@endif
                    <em>{{ $dashboardInstagramLinked ? 'Volver a sincronizar' : ($dashboardFacebookLinked ? 'Conectar con Instagram' : 'Primero conecta Facebook') }} @if($dashboardFacebookLinked)<i class="fas fa-arrow-right"></i>@endif</em>
                </a>
            </div>
        </div>
        <footer><button type="button" data-close-dashboard-social>Listo</button></footer>
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

    #client-dashboard {
        --prodovi-purple: #5B2B76;
        --prodovi-purple-dark: #3d174f;
        --prodovi-orange: #EF6C22;
        --prodovi-gold: #F5A900;
        --prodovi-green: #7DA533;
        --prodovi-turquoise: #117E8C;
        background: #fff;
        color: #17131d;
    }
    #client-dashboard .dashboard-shell { width: 100%; padding-bottom: 40px; }
    #client-dashboard .dashboard-shell > :not(.client-hero) { margin-right: 2rem; margin-left: 2rem; }
    #client-dashboard .client-hero {
        position: relative;
        min-height: 178px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 36px;
        padding: 32px 38px;
        overflow: hidden;
        border-bottom: 0;
        background:
            linear-gradient(118deg, #4a205d 0%, #68317d 46%, #285b70 100%);
        color: #fff;
        isolation: isolate;
    }
    #client-dashboard .client-hero::before {
        content: '';
        position: absolute;
        z-index: 0;
        width: 330px;
        height: 330px;
        top: -230px;
        left: 34%;
        border: 54px solid rgba(255,255,255,.055);
        border-radius: 50%;
    }
    #client-dashboard .client-hero::after {
        content: '';
        position: absolute;
        z-index: 0;
        right: 8%;
        bottom: -125px;
        width: 250px;
        height: 250px;
        border-radius: 50%;
        background: radial-gradient(circle,rgba(245,169,0,.2),rgba(239,108,34,.06) 48%,transparent 70%);
    }
    #client-dashboard .hero-content { position: relative; z-index: 2; max-width: 760px; }
    #client-dashboard .hero-kicker { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 13px; color: rgba(255,255,255,.76); font-size: .68rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
    #client-dashboard .hero-kicker::before { content: ''; width: 24px; height: 3px; background: #64d3d5; }
    #client-dashboard .client-hero h1 { margin: 0; color: #fff; font-size: clamp(1.75rem,3.4vw,2.65rem); font-weight: 900; line-height: 1.05; letter-spacing: -.04em; }
    #client-dashboard .client-hero h1 span { color: var(--prodovi-gold); background: linear-gradient(90deg,var(--prodovi-gold),var(--prodovi-green)); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    #client-dashboard .client-hero p { max-width: 650px; margin-top: 12px; color: rgba(255,255,255,.72); font-size: .9rem; line-height: 1.6; }
    #client-dashboard .login-mosaic { position: relative; z-index: 2; width: 156px; height: 104px; flex: 0 0 auto; display: grid; grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(2,1fr); }
    #client-dashboard .login-mosaic span:nth-child(1) { background: var(--prodovi-orange); border-radius: 100% 0 0 0; }
    #client-dashboard .login-mosaic span:nth-child(2) { background: var(--prodovi-gold); border-radius: 0 0 0 100%; }
    #client-dashboard .login-mosaic span:nth-child(3) { background: var(--prodovi-purple); border-radius: 100% 0 100% 0; }
    #client-dashboard .login-mosaic span:nth-child(4) { background: var(--prodovi-turquoise); border-radius: 0 100% 0 100%; }
    #client-dashboard .login-mosaic span:nth-child(5) { background: var(--prodovi-green); border-radius: 50%; }
    #client-dashboard .login-mosaic span:nth-child(6) { border: 12px solid #607078; border-top-color: transparent; border-left-color: transparent; border-radius: 50%; transform: rotate(45deg); }
    #client-dashboard .dashboard-metrics {
        display: grid;
        grid-template-columns: repeat(5,minmax(0,1fr));
        border-top: 1px solid #ded7e1;
        border-bottom: 1px solid #ded7e1;
        background: #fff;
    }
    #client-dashboard .dashboard-metrics article { min-width: 0; display: flex; align-items: center; gap: 13px; padding: 20px 22px; border-right: 1px solid #e7e1e9; }
    #client-dashboard .dashboard-metrics article:last-child { border-right: 0; }
    #client-dashboard .dashboard-metrics article > i { width: 39px; height: 39px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 3px; background: var(--prodovi-purple); color: #fff; }
    #client-dashboard .dashboard-metrics article:nth-child(2) > i { background: var(--prodovi-orange); }
    #client-dashboard .dashboard-metrics article:nth-child(3) > i { background: var(--prodovi-turquoise); }
    #client-dashboard .dashboard-metrics article:nth-child(4) > i { background: var(--prodovi-green); }
    #client-dashboard .dashboard-metrics article:nth-child(5) > i { background: var(--prodovi-turquoise); }
    #client-dashboard .dashboard-metrics span { min-width: 0; }
    #client-dashboard .dashboard-metrics small { display: block; color: #8a7f8e; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    #client-dashboard .dashboard-metrics strong { display: block; overflow: hidden; margin-top: 3px; color: #302834; font-size: .92rem; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
    #client-dashboard .dashboard-metrics .time-pending-label { overflow: visible; max-width: 190px; font-size: .72rem; line-height: 1.35; text-overflow: clip; white-space: normal; }
    #client-dashboard .social-metric-content { min-width:0; }
    #client-dashboard .social-metric-content > small { display:block; }
    #client-dashboard .social-metric-links { display:flex; flex-wrap:wrap; gap:5px; margin-top:5px; }
    #client-dashboard .social-pill { position:relative; display:inline-flex; align-items:center; gap:5px; padding:4px 6px; border-radius:2px; background:#edf7f8; color:#117e8c; font-size:.6rem; font-weight:900; text-decoration:none; }
    #client-dashboard .social-pill.facebook i { color:#1877f2; }
    #client-dashboard .social-pill.instagram i { color:#d62976; }
    #client-dashboard .social-pill::after { content:attr(data-tooltip); position:absolute; z-index:40; bottom:calc(100% + 8px); left:50%; width:max-content; max-width:210px; padding:7px 9px; border-radius:3px; background:#242426; color:#fff; font-size:.62rem; font-weight:700; line-height:1.25; text-align:center; opacity:0; pointer-events:none; transform:translate(-50%,4px); transition:.16s ease; }
    #client-dashboard .social-pill::before { content:''; position:absolute; z-index:41; bottom:calc(100% + 3px); left:50%; border:5px solid transparent; border-top-color:#242426; opacity:0; pointer-events:none; transform:translateX(-50%); transition:.16s ease; }
    #client-dashboard .social-pill:hover::after, #client-dashboard .social-pill:hover::before, #client-dashboard .social-pill:focus-visible::after, #client-dashboard .social-pill:focus-visible::before { opacity:1; transform:translate(-50%,0); }
    #client-dashboard .social-pill:hover { background:#dff0f2; }
    #client-dashboard .dashboard-metrics article.social-networks-metric { position:relative; padding-right:48px; }
    #client-dashboard .connect-social-links { display:inline-flex; align-items:center; gap:5px; margin-top:5px; padding:5px 7px; border:1px solid #117e8c; border-radius:3px; background:#117e8c; color:#fff; font-size:.61rem; font-weight:900; cursor:pointer; }
    #client-dashboard .edit-social-links { position:absolute; top:8px; right:8px; width:30px; height:30px; display:grid; place-items:center; border:0; border-radius:50%; background:transparent; color:#756a7a; cursor:pointer; transition:.18s ease; }
    #client-dashboard .edit-social-links:hover { background:rgba(17,126,140,.11); color:#117e8c; }
    #client-dashboard .active-company-metric { position: relative; padding-right: 50px; }
    #client-dashboard .company-options { position: absolute; z-index: 20; top: 8px; right: 8px; }
    #client-dashboard .company-options summary { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; color: #756a7a; cursor: pointer; list-style: none; }
    #client-dashboard .company-options summary::-webkit-details-marker { display: none; }
    #client-dashboard .company-options summary:hover { background: rgba(91,43,118,.1); color: var(--prodovi-purple); }
    #client-dashboard .company-options[open] summary { background:rgba(91,43,118,.1); color:var(--prodovi-purple); }
    #client-dashboard .company-options-menu { position: absolute; top: 34px; right: 0; width: 230px; padding: 6px; border: 1px solid #ded7e1; background: #fff; box-shadow: 0 12px 30px rgba(28,19,32,.18); }
    #client-dashboard .company-options-menu > small { padding:7px 10px 6px; color:#918696; font-size:.58rem; }
    #client-dashboard .company-options-menu a { display: flex; align-items: center; gap: 9px; min-width:0; padding: 10px; color: #514557; font-size: .78rem; font-weight: 700; text-decoration: none; }
    #client-dashboard .company-options-menu a span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    #client-dashboard .company-options-menu a:hover, #client-dashboard .company-options-menu a.is-current { background: #f4edf7; color: var(--prodovi-purple); }
    #client-dashboard .company-options-menu i { width: 16px; color: var(--prodovi-orange); }
    html[data-client-theme="dark"] #client-dashboard .company-options summary { color: #b4abb8; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu { border-color: #444047; background: #242426; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a { color: #ddd8df; }
    html[data-client-theme="dark"] #client-dashboard .company-options-menu a:hover, html[data-client-theme="dark"] #client-dashboard .company-options-menu a.is-current { background: rgba(17,126,140,.16); color: #fff; }
    #client-dashboard .campaign-progress { padding: 42px 24px; background: transparent; text-align: center; }
    #client-dashboard .campaign-progress-icon { width: 68px; height: 68px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 50%; background: rgba(91,43,118,.1); color: var(--prodovi-purple); font-size: 1.7rem; }
    #client-dashboard .campaign-progress h2 { margin: 0; color: #302834; font-size: 1.25rem; font-weight: 900; letter-spacing: -.025em; }
    #client-dashboard .campaign-progress p { max-width: 580px; margin: 9px auto 0; color: #756a7a; font-size: .9rem; line-height: 1.65; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress-icon { background: rgba(201,148,229,.12); color: #c994e5; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress h2 { color: #f1edf3; }
    html[data-client-theme="dark"] #client-dashboard .campaign-progress p { color: #b4abb8; }
    #client-dashboard .dashboard-grid { align-items: start; }
    #client-dashboard .service-panel,
    #client-dashboard .company-panel {
        border: 0;
        border-top: 1px solid #d9d2dc;
        border-bottom: 1px solid #d9d2dc;
        border-radius: 0;
        box-shadow: none;
    }
    #client-dashboard .service-panel { border-left: 4px solid var(--prodovi-purple); }
    #client-dashboard .section-heading { border-color: #ded7e1; background: #f7f5f8; }
    #client-dashboard .section-icon { border-radius: 3px; background: var(--prodovi-purple) !important; }
    #client-dashboard .section-heading h2 { letter-spacing: -.025em; }
    #client-dashboard .plan-overview { border: 0; border-radius: 0; background: #fff; }
    #client-dashboard .plan-badge { border: 1px solid rgba(17,126,140,.22); border-radius: 2px; background: rgba(17,126,140,.08); color: var(--prodovi-turquoise); }
    #client-dashboard .plan-status { position: relative; overflow: hidden; border-radius: 3px; background: #242426 !important; box-shadow: none; }
    #client-dashboard .plan-status::before { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 5px; background: var(--prodovi-orange); }
    #client-dashboard .service-progress { margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.12); }
    #client-dashboard .service-progress > div { display: flex; justify-content: space-between; gap: 12px; color: #c2bdc5; font-size: .72rem; }
    #client-dashboard .service-progress > div strong { color: #fff; }
    #client-dashboard .service-progress-track { height: 5px; display: block; margin-top: 8px; overflow: hidden; background: #454248; }
    #client-dashboard .service-progress-track i { height: 100%; display: block; background: linear-gradient(90deg,var(--prodovi-turquoise),var(--prodovi-green)); }
    #client-dashboard .feature-count { border-radius: 2px; background: #f1edf2; color: #756a7a; }
    #client-dashboard .feature-item { border-width: 0 0 1px 3px; border-color: #e5dfe7 #e5dfe7 #e5dfe7 var(--prodovi-turquoise); border-radius: 0; background: #fff; box-shadow: none; }
    #client-dashboard .feature-item:hover { border-left-color: var(--prodovi-orange); background: #faf8fb; transform: translateX(2px); }
    #client-dashboard .feature-check { border-radius: 2px; background: rgba(125,165,51,.13); color: var(--prodovi-green); }
    #client-dashboard .company-panel { position: sticky; top: 24px; border-top: 5px solid var(--prodovi-orange); }
    #client-dashboard .company-panel > div:first-child { border-radius: 3px !important; background: var(--prodovi-purple) !important; }
    #client-dashboard .company-panel h2 { letter-spacing: -.03em; }
    #client-dashboard .company-summary { border-width: 1px 0; border-color: #ded7e1 !important; border-radius: 0; background: #faf8fb !important; }
    #client-dashboard .company-summary .w-10 { border-radius: 2px !important; background: var(--prodovi-green) !important; }
    #client-dashboard .company-cta { width: 100%; border-radius: 3px; background: linear-gradient(135deg,var(--prodovi-purple),var(--prodovi-purple-dark)) !important; box-shadow: none; }
    #client-dashboard .company-cta:hover { background: var(--prodovi-orange) !important; }
    #plan-modal { z-index: 1000; }
    #plan-modal .inline-block { overflow: hidden; border: 1px solid #d9d2dc; border-radius: 3px; box-shadow: 0 28px 80px rgba(0,0,0,.3); }
    #plan-modal .bg-gradient-to-r { background: #242426 !important; }
    #plan-modal .bg-gray-50.rounded-xl { border-width: 0 0 0 4px; border-radius: 0; border-left-color: #117E8C; background: #f7f5f8; }
    #plan-modal #modal-plan-features > * { border-radius: 2px !important; box-shadow: none !important; }
    #plan-modal #close-modal-footer { border-radius: 3px; background: #5B2B76 !important; box-shadow: none; }

    .payment-approved-modal { position:fixed; z-index:2147483003; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .payment-approved-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.82); backdrop-filter:blur(6px); }
    .payment-approved-dialog { position:relative; width:min(470px,100%); padding:34px 32px 30px; border-top:5px solid #7da533; border-radius:5px; background:#fff; color:#302834; text-align:center; box-shadow:0 30px 90px rgba(0,0,0,.4); }
    .payment-approved-icon { width:60px; height:60px; display:grid; place-items:center; margin:0 auto 17px; border-radius:50%; background:#eaf3da; color:#6b922f; font-size:1.55rem; }
    .payment-approved-dialog > small { display:block; color:#6b922f; font-size:.62rem; font-weight:900; letter-spacing:.13em; }
    .payment-approved-dialog h2 { margin:7px 0 10px; color:#302834; font-size:1.35rem; font-weight:900; }
    .payment-approved-dialog p { margin:0; color:#756a7a; font-size:.82rem; line-height:1.65; }
    .payment-approved-dialog p strong { color:#5b2b76; }
    .payment-approved-dialog > a { display:inline-flex; align-items:center; justify-content:center; gap:8px; margin-top:23px; padding:11px 18px; border-radius:4px; background:#5b2b76; color:#fff; font-size:.76rem; font-weight:900; text-decoration:none; transition:.18s ease; }
    .payment-approved-dialog > a:hover { background:#432056; transform:translateY(-1px); }
    html[data-client-theme="dark"] .payment-approved-dialog { background:#1e1b21; color:#f1edf3; }
    html[data-client-theme="dark"] .payment-approved-dialog h2 { color:#f1edf3; }
    html[data-client-theme="dark"] .payment-approved-dialog p { color:#b4abb8; }
    html[data-client-theme="dark"] .payment-approved-icon { background:#28321f; color:#a9cb68; }

    .dashboard-social-modal { position:fixed; z-index:2147483001; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .dashboard-social-modal.hidden { display:none; }
    .dashboard-social-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    .dashboard-social-dialog { position:relative; width:min(690px,100%); max-height:calc(100vh - 40px); display:flex; flex-direction:column; overflow:hidden; border-radius:5px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.38); }
    .dashboard-social-dialog > header { display:flex; align-items:center; gap:12px; padding:19px 22px; border-bottom:5px solid #117e8c; background:#242426; color:#fff; }
    .dashboard-social-dialog > header > span { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:3px; background:#117e8c; }
    .dashboard-social-dialog > header > div { flex:1; }.dashboard-social-dialog header small { display:block; color:#76c5ce; font-size:.6rem; font-weight:900; letter-spacing:.12em; }.dashboard-social-dialog header h3 { margin:3px 0 0; font-size:1.12rem; font-weight:900; }
    .dashboard-social-dialog > header button { width:36px; height:36px; border:1px solid #565259; border-radius:3px; background:#343436; color:#fff; cursor:pointer; }
    .dashboard-social-body { overflow-y:auto; padding:22px; }.dashboard-social-company { margin-bottom:14px; padding:11px 13px; border-left:4px solid #ee9f2b; background:#fff5e6; color:#70572f; font-size:.75rem; }.dashboard-social-company i { margin-right:7px; color:#ee9f2b; }.dashboard-social-body > p { margin:0 0 16px; color:#756a7a; font-size:.77rem; line-height:1.55; }
    .dashboard-social-notice { display:flex; gap:8px; margin-bottom:13px; padding:11px; border-left:4px solid; font-size:.72rem; font-weight:800; }.dashboard-social-notice.success { border-color:#7da533; background:#f3f7eb; color:#587923; }.dashboard-social-notice.error { border-color:#b63b3b; background:#fff1f1; color:#9b2929; }
    .dashboard-social-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:13px; }.dashboard-social-option { display:flex; flex-direction:column; padding:17px; border:1px solid #ded7e1; border-top:4px solid #1877f2; border-radius:4px; color:#302834; text-decoration:none; transition:.2s ease; }.dashboard-social-option.instagram { border-top-color:#d62976; }.dashboard-social-option:hover { transform:translateY(-2px); box-shadow:0 10px 22px #ded9e0; }
    .dashboard-social-option > div { display:flex; align-items:center; justify-content:space-between; }.dashboard-social-option > div > span { width:37px; height:37px; display:grid; place-items:center; border-radius:50%; background:#1877f2; color:#fff; }.dashboard-social-option.instagram > div > span { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }.dashboard-social-option > div > b { padding:5px 7px; background:#edf7f8; color:#117e8c; font-size:.57rem; text-transform:uppercase; }.dashboard-social-option h4 { margin:13px 0 0; font-size:.94rem; font-weight:900; }.dashboard-social-option > p { flex:1; margin:6px 0 13px; color:#756a7a; font-size:.69rem; line-height:1.5; }.dashboard-social-option > em { color:#5b2b76; font-size:.67rem; font-style:normal; font-weight:900; }
    .dashboard-social-option aside { display:flex; align-items:center; gap:8px; margin-bottom:13px; padding:9px; border:1px solid #cdddaf; background:#f7faf1; color:#587923; }.dashboard-social-option aside span,.dashboard-social-option aside small,.dashboard-social-option aside strong { display:block; }.dashboard-social-option aside small { font-size:.52rem; font-weight:900; text-transform:uppercase; }.dashboard-social-option aside strong { color:#35451b; font-size:.68rem; }.dashboard-social-option.is-linked { border-color:#7da533; border-top-color:#7da533; }.dashboard-social-option.is-disabled { background:#f4f2f5; opacity:.58; pointer-events:none; }
    .dashboard-social-dialog > footer { display:flex; justify-content:flex-end; padding:13px 22px; border-top:1px solid #ded7e1; background:#f7f5f8; }.dashboard-social-dialog > footer button { padding:9px 17px; border:0; border-radius:3px; background:#5b2b76; color:#fff; font-size:.73rem; font-weight:900; cursor:pointer; }
    html[data-client-theme="dark"] #client-dashboard .social-pill { background:#173136; color:#78c3cb; }
    html[data-client-theme="dark"] #client-dashboard .social-pill:hover { background:#21444a; }
    html[data-client-theme="dark"] #client-dashboard .edit-social-links { color:#b4abb8; }
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article > i { background:rgba(91,43,118,.1); color:var(--prodovi-purple); }
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article:nth-child(2) > i { background:rgba(239,108,34,.11); color:var(--prodovi-orange); }
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article:nth-child(3) > i { background:rgba(17,126,140,.11); color:var(--prodovi-turquoise); }
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article:nth-child(4) > i { background:rgba(125,165,51,.13); color:var(--prodovi-green); }
    html[data-client-theme="dark"] #client-dashboard .dashboard-metrics article:nth-child(5) > i { background:rgba(17,126,140,.11); color:var(--prodovi-turquoise); }
    html[data-client-theme="dark"] .dashboard-social-dialog { background:#1e1b21; color:#f1edf3; } html[data-client-theme="dark"] .dashboard-social-company { background:#3a3020; color:#efcf9e; } html[data-client-theme="dark"] .dashboard-social-body > p { color:#b4abb8; } html[data-client-theme="dark"] .dashboard-social-option { border-color:#403943; background:#29252c; color:#f1edf3; } html[data-client-theme="dark"] .dashboard-social-option > p { color:#b4abb8; } html[data-client-theme="dark"] .dashboard-social-option.is-linked { border-color:#627f2f; } html[data-client-theme="dark"] .dashboard-social-option aside { border-color:#526b2b; background:#20291a; } html[data-client-theme="dark"] .dashboard-social-dialog > footer { border-color:#403943; background:#29252c; }

    #client-dashboard .campaign-command{overflow:hidden;border:1px solid #ded7e1;border-radius:5px;background:#fff;box-shadow:0 10px 28px #ded9e0}.campaign-command-head{display:flex;align-items:center;gap:12px;padding:17px 20px;border-bottom:1px solid #ded7e1;border-left:5px solid var(--prodovi-turquoise);background:#f7f5f8}.campaign-command-icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border-radius:3px;background:var(--prodovi-turquoise);color:#fff}.campaign-command-head>div{min-width:0;flex:1}.campaign-command-head small,.campaign-command-head h2,.campaign-command-head p{display:block}.campaign-command-head>div>small{color:var(--prodovi-turquoise);font-size:.58rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.campaign-command-head h2{margin:3px 0 0;color:#302834;font-size:1.05rem;font-weight:900}.campaign-command-head p{margin:3px 0 0;color:#887d8c;font-size:.7rem}.campaign-state{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid #bbd5a0;border-radius:999px;background:#f3f7eb;color:#5e7f2c;font-size:.61rem;font-weight:900}.campaign-state i{font-size:.42rem}.campaign-state.is-pausada{border-color:#ead39c;background:#fff8e8;color:#916817}.campaign-state.is-finalizada{border-color:#cfd5d7;background:#f3f5f5;color:#687276}.campaign-state.is-borrador{border-color:#b9dadd;background:#edf7f8;color:#117e8c}.campaign-command-body{display:grid;grid-template-columns:minmax(0,.82fr) minmax(0,1.18fr);gap:16px;padding:18px}.campaign-progress-summary,.campaign-priority{min-width:0;padding:17px;border:1px solid #e2dce4;border-radius:4px;background:#fff}.campaign-progress-copy{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:end;gap:3px 12px}.campaign-progress-copy small{color:#887d8c;font-size:.58rem;font-weight:900;text-transform:uppercase}.campaign-progress-copy strong{grid-column:2;grid-row:1/3;color:var(--prodovi-turquoise);font-size:1.65rem;font-weight:900;line-height:1}.campaign-progress-copy span{color:#5f5663;font-size:.69rem}.campaign-progress-track{height:8px;overflow:hidden;margin-top:14px;border-radius:999px;background:#ece8ee}.campaign-progress-track span{height:100%;display:block;border-radius:inherit;background:linear-gradient(90deg,var(--prodovi-turquoise),#54aeb8)}.campaign-period{display:flex;align-items:center;gap:9px;margin-top:15px;padding-top:13px;border-top:1px solid #ebe6ed}.campaign-period>i{width:32px;height:32px;display:grid;place-items:center;border-radius:3px;background:#edf7f8;color:var(--prodovi-turquoise)}.campaign-period span,.campaign-period small,.campaign-period strong{display:block}.campaign-period small{color:#918696;font-size:.52rem;font-weight:900;text-transform:uppercase}.campaign-period strong{margin-top:2px;color:#4d444f;font-size:.68rem}.campaign-priority{display:grid;grid-template-columns:45px minmax(0,1fr) auto;align-items:center;gap:13px;border-left:4px solid var(--prodovi-green);background:#f8faf4}.campaign-priority.needs-action{border-left-color:var(--prodovi-orange);background:#fff9ef}.campaign-priority>span{width:45px;height:45px;display:grid;place-items:center;border-radius:50%;background:#e8f1da;color:#6b922f}.campaign-priority.needs-action>span{background:#ffedd8;color:#cf651c}.campaign-priority small{color:#6f8c3d;font-size:.54rem;font-weight:900;text-transform:uppercase}.campaign-priority.needs-action small{color:#b75c1c}.campaign-priority h3{margin:3px 0 0;color:#302834;font-size:.86rem;font-weight:900}.campaign-priority p{margin:4px 0 0;color:#756a7a;font-size:.65rem;line-height:1.5}.campaign-priority>a{display:inline-flex;align-items:center;gap:6px;padding:9px 11px;border-radius:3px;background:var(--prodovi-purple);color:#fff;font-size:.62rem;font-weight:900;text-decoration:none;white-space:nowrap}.campaign-priority>a:hover{background:var(--prodovi-turquoise)}.campaign-quick-metrics{grid-column:1/-1;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));border:1px solid #e2dce4;border-radius:4px;background:#fff}.campaign-quick-metrics article{min-width:0;display:flex;align-items:center;gap:10px;padding:14px;border-right:1px solid #e7e1e9}.campaign-quick-metrics article:last-child{border-right:0}.campaign-quick-metrics article>span{width:35px;height:35px;display:grid;place-items:center;flex:0 0 auto;border-radius:3px;background:#edf7f8;color:var(--prodovi-turquoise)}.campaign-quick-metrics article:nth-child(2)>span{background:#fff2e6;color:var(--prodovi-orange)}.campaign-quick-metrics article:nth-child(3)>span{background:#f1ecf4;color:var(--prodovi-purple)}.campaign-quick-metrics small,.campaign-quick-metrics strong,.campaign-quick-metrics em{display:block}.campaign-quick-metrics small{color:#918696;font-size:.5rem;font-weight:900;text-transform:uppercase}.campaign-quick-metrics strong{margin-top:1px;color:#302834;font-size:1.05rem;font-weight:900}.campaign-quick-metrics em{overflow:hidden;color:#827786;font-size:.55rem;font-style:normal;text-overflow:ellipsis;white-space:nowrap}.campaign-agenda{grid-column:1/-1;overflow:hidden;border:1px solid #ded7e1;border-radius:4px;background:#fff}.campaign-agenda-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 16px;border-bottom:1px solid #e7e1e9;border-left:4px solid var(--prodovi-turquoise);background:#faf9fb}.campaign-agenda-head small,.campaign-agenda-head h3{display:block}.campaign-agenda-head small{color:var(--prodovi-turquoise);font-size:.52rem;font-weight:900;text-transform:uppercase}.campaign-agenda-head h3{margin:2px 0 0;color:#302834;font-size:.82rem;font-weight:900}.campaign-agenda-head>a{display:inline-flex;align-items:center;gap:6px;color:var(--prodovi-turquoise);font-size:.62rem;font-weight:900;text-decoration:none}.campaign-agenda-list{padding:3px 16px}.campaign-agenda-list article{display:grid;grid-template-columns:44px 12px minmax(0,1fr) auto;align-items:center;gap:11px;padding:11px 0;border-bottom:1px solid #eee9ef}.campaign-agenda-list article:last-child{border-bottom:0}.campaign-agenda-list time{width:44px;padding:5px 3px;border-radius:3px;background:#edf7f8;color:var(--prodovi-turquoise);text-align:center}.campaign-agenda-list time strong,.campaign-agenda-list time small{display:block}.campaign-agenda-list time strong{font-size:.88rem}.campaign-agenda-list time small{font-size:.46rem;font-weight:900}.campaign-agenda-dot{width:8px;height:8px;border:2px solid #fff;border-radius:50%;background:var(--prodovi-turquoise);box-shadow:0 0 0 2px #aed1d5}.campaign-agenda-list article>div{min-width:0}.campaign-agenda-list article>div small{display:block;color:#918696;font-size:.49rem;font-weight:900;text-transform:uppercase}.campaign-agenda-list article>div strong{display:block;overflow:hidden;margin-top:2px;color:#413745;font-size:.69rem;text-overflow:ellipsis;white-space:nowrap}.campaign-agenda-list article>div p{overflow:hidden;margin:2px 0 0;color:#817585;font-size:.57rem;text-overflow:ellipsis;white-space:nowrap}.campaign-agenda-list article>em{padding:5px 7px;border-radius:999px;background:#fff5e6;color:#a46b16;font-size:.51rem;font-style:normal;font-weight:900}.campaign-agenda-list article>em.is-en_progreso{background:#edf7f8;color:#117e8c}.campaign-agenda-list article>em.is-completada{background:#f2f7e9;color:#64852f}.campaign-agenda-list article>em.is-rechazada{background:#fff0f0;color:#ad3b3b}.campaign-agenda-empty{display:flex;align-items:center;justify-content:center;gap:11px;padding:28px;color:#8c818f}.campaign-agenda-empty>i{font-size:1.4rem;color:#9fc8cc}.campaign-agenda-empty span,.campaign-agenda-empty strong,.campaign-agenda-empty small{display:block}.campaign-agenda-empty strong{color:#5d535f;font-size:.7rem}.campaign-agenda-empty small{margin-top:2px;font-size:.57rem}
    html[data-client-theme="dark"] #client-dashboard .campaign-command,html[data-client-theme="dark"] #client-dashboard .campaign-progress-summary,html[data-client-theme="dark"] #client-dashboard .campaign-quick-metrics,html[data-client-theme="dark"] #client-dashboard .campaign-agenda{border-color:#403943;background:#1e1b21;box-shadow:none}html[data-client-theme="dark"] #client-dashboard .campaign-command-head,html[data-client-theme="dark"] #client-dashboard .campaign-agenda-head{border-color:#403943;background:#29252c}html[data-client-theme="dark"] #client-dashboard .campaign-command-head h2,html[data-client-theme="dark"] #client-dashboard .campaign-priority h3,html[data-client-theme="dark"] #client-dashboard .campaign-quick-metrics strong,html[data-client-theme="dark"] #client-dashboard .campaign-agenda-head h3,html[data-client-theme="dark"] #client-dashboard .campaign-agenda-list article>div strong{color:#f1edf3}html[data-client-theme="dark"] #client-dashboard .campaign-command-head p,html[data-client-theme="dark"] #client-dashboard .campaign-progress-copy span,html[data-client-theme="dark"] #client-dashboard .campaign-priority p,html[data-client-theme="dark"] #client-dashboard .campaign-agenda-list article>div p{color:#b4abb8}html[data-client-theme="dark"] #client-dashboard .campaign-priority{border-color:#53692e;background:#20281a}html[data-client-theme="dark"] #client-dashboard .campaign-priority.needs-action{border-color:#9a571f;background:#33271c}html[data-client-theme="dark"] #client-dashboard .campaign-quick-metrics article,html[data-client-theme="dark"] #client-dashboard .campaign-agenda-list article,html[data-client-theme="dark"] #client-dashboard .campaign-period{border-color:#403943}html[data-client-theme="dark"] #client-dashboard .campaign-period strong{color:#ddd8df}

    #client-dashboard .campaign-command,html[data-client-theme="dark"] #client-dashboard .campaign-command{overflow:visible;border:0;border-radius:0;background:transparent;box-shadow:none}#client-dashboard .campaign-progress-summary,html[data-client-theme="dark"] #client-dashboard .campaign-progress-summary{grid-column:1/-1;padding:4px 0 14px;border:0;border-radius:0;background:transparent;box-shadow:none}#client-dashboard .campaign-progress-track{height:10px;margin-top:12px}#client-dashboard .campaign-period{justify-content:flex-end;margin-top:9px;padding:0;border:0}#client-dashboard .campaign-period>i{width:auto;height:auto;background:transparent}#client-dashboard .campaign-period strong{font-size:.62rem}

    #client-dashboard .campaign-agenda-head > button { display:inline-flex; align-items:center; gap:7px; padding:8px 11px; border:1px solid #b9dadd; border-radius:3px; background:#edf7f8; color:var(--prodovi-turquoise); font-size:.62rem; font-weight:900; cursor:pointer; }
    #client-dashboard .campaign-agenda-head > button:hover { border-color:var(--prodovi-turquoise); background:var(--prodovi-turquoise); color:#fff; }
    #client-dashboard .campaign-agenda-layout { grid-column:1/-1; display:grid; grid-template-columns:minmax(0,1fr) 185px; align-items:stretch; gap:14px; }
    #client-dashboard .campaign-agenda-layout > .campaign-agenda { grid-column:auto; }
    #client-dashboard .campaign-social-followers { display:grid; grid-template-rows:repeat(2,minmax(0,1fr)); gap:10px; }
    #client-dashboard .campaign-social-followers article { min-height:0; display:flex; align-items:center; gap:11px; padding:14px; border:1px solid #ded7e1; border-radius:4px; background:#fff; }
    #client-dashboard .campaign-social-followers article > span { width:38px; height:38px; display:grid; place-items:center; flex:0 0 auto; border-radius:50%; background:#1877f2; color:#fff; font-size:1rem; }
    #client-dashboard .campaign-social-followers article.instagram > span { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }
    #client-dashboard .campaign-social-followers article div, #client-dashboard .campaign-social-followers article small, #client-dashboard .campaign-social-followers article strong, #client-dashboard .campaign-social-followers article em { min-width:0; display:block; }
    #client-dashboard .campaign-social-followers article small { color:#817585; font-size:.5rem; font-weight:900; text-transform:uppercase; }
    #client-dashboard .campaign-social-followers article strong { margin-top:2px; color:#302834; font-size:1.05rem; font-weight:900; line-height:1; }
    #client-dashboard .campaign-social-followers article em { margin-top:3px; color:#918696; font-size:.52rem; font-style:normal; }
    #client-dashboard .campaign-social-connect { width:100%; min-height:150px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:18px; border:1px dashed #9fc8cc; border-radius:4px; background:#f3fafb; color:var(--prodovi-turquoise); text-align:center; cursor:pointer; }
    #client-dashboard .campaign-social-connect > span { width:40px; height:40px; display:grid; place-items:center; border-radius:50%; background:var(--prodovi-turquoise); color:#fff; }
    #client-dashboard .campaign-social-connect strong { font-size:.7rem; font-weight:900; }
    #client-dashboard .campaign-social-connect small { color:#748a8d; font-size:.52rem; }
    #client-dashboard .campaign-social-connect:hover { border-style:solid; background:#e7f5f6; }
    #client-dashboard .campaign-calendar { padding:16px; }
    #client-dashboard .campaign-calendar[hidden], #client-dashboard .campaign-agenda-list[hidden], #client-dashboard .campaign-agenda-empty[hidden] { display:none; }
    #client-dashboard .campaign-calendar-toolbar { display:grid; grid-template-columns:34px 1fr 34px; align-items:center; gap:10px; margin-bottom:14px; }
    #client-dashboard .campaign-calendar-toolbar strong { color:#302834; font-size:.82rem; font-weight:900; text-align:center; text-transform:capitalize; }
    #client-dashboard .campaign-calendar-toolbar button { width:34px; height:34px; display:grid; place-items:center; border:1px solid #d8d0da; border-radius:3px; background:#fff; color:var(--prodovi-turquoise); cursor:pointer; }
    #client-dashboard .campaign-calendar-toolbar button:hover { border-color:var(--prodovi-turquoise); background:#edf7f8; }
    #client-dashboard .campaign-calendar-weekdays, #client-dashboard .campaign-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); }
    #client-dashboard .campaign-calendar-weekdays { border:1px solid #e4dfe6; border-bottom:0; background:#f7f5f8; }
    #client-dashboard .campaign-calendar-weekdays span { padding:8px 4px; color:#817585; font-size:.55rem; font-weight:900; text-align:center; text-transform:uppercase; }
    #client-dashboard .campaign-calendar-grid { border-top:1px solid #e4dfe6; border-left:1px solid #e4dfe6; }
    #client-dashboard .campaign-calendar-day { min-width:0; min-height:92px; padding:7px; border:0; border-right:1px solid #e4dfe6; border-bottom:1px solid #e4dfe6; background:#fff; text-align:left; }
    #client-dashboard .campaign-calendar-day.is-outside { background:#faf9fb; }
    #client-dashboard .campaign-calendar-day.is-today { background:#f1f9fa; box-shadow:inset 0 0 0 1px var(--prodovi-turquoise); }
    #client-dashboard .campaign-calendar-day > time { display:block; margin-bottom:5px; color:#5f5663; font-size:.62rem; font-weight:900; }
    #client-dashboard .campaign-calendar-day.is-outside > time { color:#b6afb8; }
    #client-dashboard .campaign-calendar-event { display:block; overflow:hidden; margin-top:3px; padding:4px 5px; border-left:3px solid var(--prodovi-turquoise); border-radius:2px; background:#edf7f8; color:#306d74; font-size:.5rem; font-weight:800; line-height:1.25; text-overflow:ellipsis; white-space:nowrap; }
    #client-dashboard .campaign-calendar-event.is-publication { border-left-color:var(--prodovi-orange); background:#fff3e8; color:#9a551e; }
    #client-dashboard .campaign-calendar-more { display:block; margin-top:4px; color:#817585; font-size:.48rem; font-weight:900; }
    html[data-client-theme="dark"] #client-dashboard .campaign-agenda-head > button { border-color:#356d73; background:#173136; color:#78c3cb; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-toolbar strong { color:#f1edf3; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-toolbar button, html[data-client-theme="dark"] #client-dashboard .campaign-calendar-day { border-color:#403943; background:#1e1b21; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-weekdays { border-color:#403943; background:#29252c; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-grid { border-color:#403943; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-day.is-outside { background:#19171b; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-day.is-today { background:#173136; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-day > time { color:#ddd8df; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-event { background:#173136; color:#91d0d6; }
    html[data-client-theme="dark"] #client-dashboard .campaign-calendar-event.is-publication { background:#38271c; color:#efb17d; }
    html[data-client-theme="dark"] #client-dashboard .campaign-social-followers article { border-color:#403943; background:#1e1b21; }
    html[data-client-theme="dark"] #client-dashboard .campaign-social-followers article strong { color:#f1edf3; }
    html[data-client-theme="dark"] #client-dashboard .campaign-social-connect { border-color:#356d73; background:#173136; }

    #client-dashboard .dashboard-review-notice { grid-column:1/-1; display:flex; align-items:center; gap:8px; padding:10px 13px; border-left:4px solid var(--prodovi-green); background:#f2f7e9; color:#587923; font-size:.67rem; font-weight:900; }
    #client-dashboard .dashboard-client-focus { grid-column:1/-1; display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:14px; }
    #client-dashboard .dashboard-client-focus.is-single { grid-template-columns:1fr; }
    #client-dashboard .dashboard-review-panel, #client-dashboard .dashboard-results-panel { overflow:hidden; border:1px solid #ded7e1; border-radius:4px; background:#fff; }
    #client-dashboard .dashboard-review-panel > header, #client-dashboard .dashboard-results-panel > header { min-height:58px; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 15px; border-bottom:1px solid #e7e1e9; background:#faf9fb; }
    #client-dashboard .dashboard-review-panel > header { border-left:4px solid var(--prodovi-orange); }
    #client-dashboard .dashboard-results-panel > header { border-left:4px solid var(--prodovi-turquoise); }
    #client-dashboard .dashboard-review-panel header small, #client-dashboard .dashboard-review-panel header h3, #client-dashboard .dashboard-results-panel header small, #client-dashboard .dashboard-results-panel header h3 { display:block; }
    #client-dashboard .dashboard-review-panel header small, #client-dashboard .dashboard-results-panel header small { color:#918696; font-size:.5rem; font-weight:900; text-transform:uppercase; }
    #client-dashboard .dashboard-review-panel header h3, #client-dashboard .dashboard-results-panel header h3 { margin:2px 0 0; color:#302834; font-size:.8rem; font-weight:900; }
    #client-dashboard .dashboard-review-panel > header > span { min-width:26px; height:26px; display:grid; place-items:center; border-radius:50%; background:#fff0e5; color:#c95e18; font-size:.63rem; font-weight:900; }
    #client-dashboard .dashboard-results-panel > header a { color:var(--prodovi-turquoise); font-size:.58rem; font-weight:900; text-decoration:none; }
    #client-dashboard .dashboard-review-list { padding:2px 14px; }
    #client-dashboard .dashboard-review-list article:not(:last-child) { border-bottom:1px solid #eee9ef; }
    #client-dashboard .dashboard-review-list button { width:100%; display:grid; grid-template-columns:54px minmax(0,1fr) 16px; align-items:center; gap:11px; padding:11px 0; border:0; background:transparent; text-align:left; cursor:pointer; }
    #client-dashboard .dashboard-review-list button:hover .dashboard-review-copy strong { color:var(--prodovi-turquoise); }
    #client-dashboard .dashboard-review-thumb { width:54px; height:54px; display:grid; place-items:center; overflow:hidden; border-radius:3px; background:#edf7f8; color:var(--prodovi-turquoise); font-size:1.15rem; }
    #client-dashboard .dashboard-review-thumb img { width:100%; height:100%; object-fit:cover; }
    #client-dashboard .dashboard-review-copy, #client-dashboard .dashboard-review-copy small, #client-dashboard .dashboard-review-copy strong, #client-dashboard .dashboard-review-copy em { min-width:0; display:block; }
    #client-dashboard .dashboard-review-copy small { color:#a06733; font-size:.48rem; font-weight:900; text-transform:uppercase; }
    #client-dashboard .dashboard-review-copy strong { overflow:hidden; margin-top:2px; color:#413745; font-size:.67rem; font-weight:900; text-overflow:ellipsis; white-space:nowrap; }
    #client-dashboard .dashboard-review-copy em { margin-top:4px; color:var(--prodovi-turquoise); font-size:.51rem; font-style:normal; font-weight:900; }
    #client-dashboard .dashboard-review-list button > i { color:#b1a9b4; font-size:.55rem; }
    #client-dashboard .dashboard-results-grid { min-height:78px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); }
    #client-dashboard .dashboard-results-grid article { min-width:0; display:grid; grid-template-columns:29px minmax(0,1fr); align-content:center; column-gap:7px; padding:13px 10px; border-right:1px solid #eee9ef; }
    #client-dashboard .dashboard-results-grid article:last-child { border-right:0; }
    #client-dashboard .dashboard-results-grid article > span { grid-row:1/3; width:29px; height:29px; display:grid; place-items:center; border-radius:3px; background:#edf7f8; color:var(--prodovi-turquoise); font-size:.68rem; }
    #client-dashboard .dashboard-results-grid small { overflow:hidden; color:#918696; font-size:.43rem; font-weight:900; text-overflow:ellipsis; text-transform:uppercase; white-space:nowrap; }
    #client-dashboard .dashboard-results-grid strong { overflow:hidden; margin-top:2px; color:#302834; font-size:.76rem; font-weight:900; text-overflow:ellipsis; }
    html[data-client-theme="dark"] #client-dashboard .dashboard-review-panel, html[data-client-theme="dark"] #client-dashboard .dashboard-results-panel { border-color:#403943; background:#1e1b21; }
    html[data-client-theme="dark"] #client-dashboard .dashboard-review-panel > header, html[data-client-theme="dark"] #client-dashboard .dashboard-results-panel > header { border-color:#403943; background:#29252c; }
    html[data-client-theme="dark"] #client-dashboard .dashboard-review-panel header h3, html[data-client-theme="dark"] #client-dashboard .dashboard-results-panel header h3, html[data-client-theme="dark"] #client-dashboard .dashboard-review-copy strong, html[data-client-theme="dark"] #client-dashboard .dashboard-results-grid strong { color:#f1edf3; }

    #client-dashboard .campaign-command-body > .dashboard-results-panel { position:relative; grid-column:1/-1; overflow:visible; border:0; border-radius:0; background:transparent; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel > header { min-height:0; padding:2px 0 10px; border:0; border-left:0; background:transparent; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel > header small { color:var(--prodovi-turquoise); letter-spacing:.07em; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel > header h3 { font-size:.88rem; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel > header a { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border:1px solid #b9dadd; border-radius:3px; background:#edf7f8; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid { min-height:0; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article { --result-color:var(--prodovi-turquoise); --result-soft:#edf7f8; min-height:84px; display:flex; align-items:center; gap:12px; padding:15px; border:1px solid #ded7e1; border-top:3px solid var(--result-color); border-radius:5px; background:linear-gradient(145deg,#fff 55%,var(--result-soft)); box-shadow:0 7px 17px rgba(48,40,52,.07); }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article:nth-child(2) { --result-color:#d95675; --result-soft:#fff0f3; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article:nth-child(3) { --result-color:var(--prodovi-purple); --result-soft:#f3edf6; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article:nth-child(4) { --result-color:var(--prodovi-orange); --result-soft:#fff2e8; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article > span { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:50%; background:var(--result-color); color:#fff; font-size:.82rem; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article > div { min-width:0; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid small { display:block; color:#817585; font-size:.49rem; letter-spacing:.04em; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid strong { display:block; margin-top:4px; color:#302834; font-size:1.12rem; line-height:1; }
    #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid em { display:block; margin-top:5px; color:#918696; font-size:.46rem; font-style:normal; }
    #client-dashboard .dashboard-results-loading { position:absolute; z-index:3; inset:36px 0 0; display:flex; align-items:center; justify-content:center; gap:8px; border-radius:5px; background:rgba(255,255,255,.88); backdrop-filter:blur(2px); color:var(--prodovi-purple); font-size:.62rem; font-weight:900; transition:opacity .2s ease; }
    #client-dashboard .dashboard-results-panel:not(.is-loading) .dashboard-results-loading { visibility:hidden; opacity:0; pointer-events:none; }
    #client-dashboard .dashboard-results-panel.has-error .dashboard-results-loading { color:#9b2929; }
    #client-dashboard .dashboard-results-panel.has-error .dashboard-results-loading i { display:none; }
    html[data-client-theme="dark"] #client-dashboard .campaign-command-body > .dashboard-results-panel { border:0; background:transparent; }
    html[data-client-theme="dark"] #client-dashboard .campaign-command-body > .dashboard-results-panel > header { border:0; background:transparent; }
    html[data-client-theme="dark"] #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article { border-color:#403943; background:linear-gradient(145deg,#1e1b21 55%,#29252c); box-shadow:none; }
    html[data-client-theme="dark"] #client-dashboard .dashboard-results-loading { background:rgba(30,27,33,.9); color:#c994e5; }

    .piece-review-modal { position:fixed; z-index:2147483005; inset:0; display:grid; place-items:center; padding:20px; }
    .piece-review-modal[hidden] { display:none; }
    .piece-review-backdrop { position:absolute; inset:0; border:0; background:rgba(18,14,20,.82); backdrop-filter:blur(5px); }
    .piece-review-dialog { position:relative; width:min(980px,100%); max-height:calc(100vh - 40px); display:flex; flex-direction:column; overflow:hidden; border-radius:6px; background:#fff; box-shadow:0 30px 90px rgba(0,0,0,.42); }
    .piece-review-dialog > header { display:flex; align-items:center; gap:15px; padding:16px 20px; border-bottom:4px solid #117e8c; background:#242426; color:#fff; }
    .piece-review-dialog > header > div { min-width:0; flex:1; }
    .piece-review-dialog > header small, .piece-review-dialog > header h2 { display:block; }
    .piece-review-dialog > header small { color:#72c2ca; font-size:.55rem; font-weight:900; letter-spacing:.1em; }
    .piece-review-dialog > header h2 { overflow:hidden; margin:3px 0 0; font-size:.92rem; font-weight:900; text-overflow:ellipsis; white-space:nowrap; }
    .piece-review-dialog > header button { width:34px; height:34px; display:grid; place-items:center; border:1px solid #555158; border-radius:3px; background:#333136; color:#fff; cursor:pointer; }
    .piece-review-body { min-height:0; display:grid; grid-template-columns:minmax(0,1.25fr) minmax(300px,.75fr); overflow-y:auto; }
    .piece-publication-preview { display:grid; place-items:center; align-content:start; padding:20px; background:#eeeaf0; }
    .piece-platforms { width:min(100%,470px); display:flex; flex-wrap:wrap; gap:6px; margin-bottom:9px; }
    .piece-platforms span { display:inline-flex; align-items:center; gap:5px; padding:5px 8px; border-radius:999px; background:#fff; color:#756a7a; font-size:.5rem; font-weight:900; }
    .piece-platforms .facebook i { color:#1877f2; }.piece-platforms .instagram i { color:#d62976; }
    .piece-social-post { width:min(100%,470px); overflow:hidden; border:1px solid #d9d3dc; border-radius:5px; background:#fff; box-shadow:0 12px 30px rgba(42,31,48,.12); }
    .piece-social-post > header { display:grid; grid-template-columns:35px minmax(0,1fr) 16px; align-items:center; gap:9px; padding:10px; }
    .piece-social-post > header > span { width:35px; height:35px; display:grid; place-items:center; border-radius:50%; background:linear-gradient(135deg,#5b2b76,#117e8c); color:#fff; font-size:.67rem; font-weight:900; }
    .piece-social-post > header strong, .piece-social-post > header small { display:block; }.piece-social-post > header strong { color:#302834; font-size:.62rem; }.piece-social-post > header small { margin-top:2px; color:#918696; font-size:.48rem; }
    .piece-social-post > header > i { color:#756a7a; font-size:.7rem; }
    .piece-social-media { display:grid; place-items:center; aspect-ratio:1/1; overflow:hidden; background:#171519; }
    .piece-social-post.is-vertical { max-width:350px; }.piece-social-post.is-vertical .piece-social-media { aspect-ratio:9/16; max-height:55vh; }
    .piece-social-media img, .piece-social-media video { width:100%; height:100%; object-fit:contain; }
    .piece-file-fallback { display:grid; place-items:center; gap:9px; padding:25px; color:#fff; text-align:center; }.piece-file-fallback > i { font-size:2rem; }.piece-file-fallback strong { max-width:280px; font-size:.65rem; }.piece-file-fallback a { padding:7px 10px; border-radius:3px; background:#117e8c; color:#fff; font-size:.56rem; font-weight:900; text-decoration:none; }
    .piece-social-actions { display:flex; align-items:center; justify-content:space-between; padding:10px 12px 4px; color:#302834; }.piece-social-actions span { display:flex; gap:13px; }
    .piece-social-post > p { margin:0; padding:7px 12px 13px; color:#514557; font-size:.58rem; line-height:1.5; }.piece-social-post > p strong { color:#302834; }
    .piece-review-sidebar { min-width:0; padding:20px; background:#fff; }
    .piece-review-context { padding-bottom:15px; border-bottom:1px solid #e6e0e8; }.piece-review-context small, .piece-review-context strong { display:block; }.piece-review-context small, .piece-review-comments > small { color:#117e8c; font-size:.5rem; font-weight:900; text-transform:uppercase; }.piece-review-context strong { margin-top:4px; color:#302834; font-size:.76rem; }.piece-review-context p { margin:6px 0 0; color:#756a7a; font-size:.58rem; line-height:1.5; }
    .piece-review-comments { display:grid; gap:7px; padding:14px 0; border-bottom:1px solid #e6e0e8; }.piece-review-comments article { padding:8px 9px; border-left:3px solid #b9dadd; background:#f8fafb; }.piece-review-comments article strong { color:#4d444f; font-size:.54rem; }.piece-review-comments article p { margin:3px 0 0; color:#756a7a; font-size:.54rem; line-height:1.4; }
    .piece-review-form { margin-top:15px; }.piece-review-form label > span { display:block; margin-bottom:6px; color:#514557; font-size:.56rem; font-weight:900; }.piece-review-form label > span small { color:#918696; font-weight:700; }.piece-review-form textarea { width:100%; resize:vertical; padding:10px; border:1px solid #d9d2dc; border-radius:3px; color:#302834; font-size:.62rem; line-height:1.5; }.piece-review-form textarea:focus { outline:2px solid rgba(17,126,140,.18); border-color:#117e8c; }
    .piece-review-actions { display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-top:10px; }.piece-review-actions button { min-height:36px; display:flex; align-items:center; justify-content:center; gap:6px; padding:8px; border:1px solid; border-radius:3px; font-size:.54rem; font-weight:900; cursor:pointer; }.piece-review-actions .is-comment { grid-column:1/-1; border-color:#d8d0da; background:#fff; color:#5b2b76; }.piece-review-actions .is-change { border-color:#edc7aa; background:#fff5ec; color:#b75918; }.piece-review-actions .is-approve { border-color:#6f982f; background:#7da533; color:#fff; }
    html[data-client-theme="dark"] .piece-review-dialog, html[data-client-theme="dark"] .piece-review-sidebar { background:#1e1b21; } html[data-client-theme="dark"] .piece-publication-preview { background:#151217; } html[data-client-theme="dark"] .piece-review-context strong { color:#f1edf3; } html[data-client-theme="dark"] .piece-review-context p, html[data-client-theme="dark"] .piece-review-comments article p { color:#b4abb8; } html[data-client-theme="dark"] .piece-review-comments article { background:#29252c; } html[data-client-theme="dark"] .piece-review-form textarea { border-color:#49414e; background:#29252c; color:#f1edf3; }

    @media (max-width: 1100px) and (min-width: 641px) {
        #client-dashboard .dashboard-metrics { grid-template-columns:repeat(3,minmax(0,1fr)); }
        #client-dashboard .campaign-command-body{grid-template-columns:1fr}.campaign-progress-summary,.campaign-priority{grid-column:1}.campaign-quick-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-quick-metrics article:nth-child(2){border-right:0}.campaign-quick-metrics article:nth-child(-n+2){border-bottom:1px solid #e7e1e9}
        #client-dashboard .campaign-agenda-layout { grid-template-columns:minmax(0,1fr) 170px; }
    }

    @media (max-width: 640px) {
        #client-dashboard .dashboard-shell > :not(.client-hero) { margin-right: 1rem; margin-left: 1rem; }
        #client-dashboard .client-hero { min-height: 205px; padding: 28px 20px; }
        #client-dashboard .login-mosaic { display: none; }
        #client-dashboard .dashboard-metrics { grid-template-columns: 1fr 1fr; }
        #client-dashboard .dashboard-metrics article:nth-child(even) { border-right: 0; }
        #client-dashboard .dashboard-metrics article:not(:last-child) { border-bottom: 1px solid #e7e1e9; }
        #client-dashboard .dashboard-metrics article { padding: 16px 13px; }
        #client-dashboard .campaign-command-head{align-items:flex-start;flex-wrap:wrap}.campaign-state{margin-left:54px}.campaign-command-body{grid-template-columns:1fr;padding:12px}.campaign-progress-summary,.campaign-priority{grid-column:1}.campaign-priority{grid-template-columns:42px minmax(0,1fr)}.campaign-priority>a{grid-column:1/-1;justify-content:center}.campaign-quick-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.campaign-quick-metrics article:nth-child(2){border-right:0}.campaign-quick-metrics article:nth-child(-n+2){border-bottom:1px solid #e7e1e9}.campaign-agenda-list article{grid-template-columns:40px 9px minmax(0,1fr)}.campaign-agenda-list article>em{grid-column:3;justify-self:start}.campaign-agenda-list article>div p{white-space:normal}
        #client-dashboard .campaign-agenda-layout { grid-template-columns:1fr; } #client-dashboard .campaign-social-followers { grid-template-rows:repeat(2,minmax(78px,auto)); }
        #client-dashboard .dashboard-client-focus { grid-template-columns:1fr; } #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article:nth-child(2) { border-right:1px solid #ded7e1; } #client-dashboard .campaign-command-body > .dashboard-results-panel .dashboard-results-grid article:nth-child(-n+2) { border-bottom:1px solid #ded7e1; }
        #client-dashboard .campaign-calendar { padding:10px; overflow-x:auto; } #client-dashboard .campaign-calendar-weekdays, #client-dashboard .campaign-calendar-grid { min-width:620px; } #client-dashboard .campaign-calendar-day { min-height:82px; }
        .piece-review-body { grid-template-columns:1fr; }.piece-publication-preview { padding:13px; }.piece-review-sidebar { padding:16px; }
        .dashboard-social-options { grid-template-columns:1fr; }
        #client-dashboard .company-panel { position: static; }
        .rp-banner .px-8 { 
            padding-left: 1.25rem; 
            padding-right: 1.25rem; 
        }
        .rp-banner .flex.flex-col.sm\:flex-row {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .grid.grid-cols-1.xl\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

@include('clientes.cuestionarioPopup')

@push('scripts')
<script>
    window.userHasCompanies = @json((bool) $empresaActiva);
    window.socialModalClosed = localStorage.getItem('socialModalClosed') === 'true';
</script>
<script src="/js/dashboardcliente.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($pendingSetupSubscription)
        document.body.style.overflow = 'hidden';
    @endif
    const modal = document.getElementById('dashboard-social-modal');
    const openButtons = document.querySelectorAll('#open-dashboard-social, [data-open-dashboard-social]');
    const openButton = openButtons[0] ?? null;
    const closeButtons = modal?.querySelectorAll('[data-close-dashboard-social]') ?? [];

    function openSocialModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        modal.querySelector('header button')?.focus();
    }

    function closeSocialModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        openButton?.focus();
    }

    openButtons.forEach(button => button.addEventListener('click', openSocialModal));
    closeButtons.forEach(button => button.addEventListener('click', closeSocialModal));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeSocialModal();
    });

    @if(session('social_accounts_success') || session('social_accounts_error'))
        openSocialModal();
    @endif

    const metaResults = document.getElementById('dashboard-meta-results');
    if (metaResults) {
        const number = value => value === null || value === undefined
            ? '—'
            : new Intl.NumberFormat('es-BO', { maximumFractionDigits: 1 }).format(value);
        const followerTotal = platform => {
            const currentTotal = platform?.totals?.followers;

            if (currentTotal !== null && currentTotal !== undefined) {
                return currentTotal;
            }

            const history = Array.isArray(platform?.followers?.values)
                ? platform.followers.values
                : [];

            return history.slice().reverse().find(value => value !== null && value !== undefined) ?? null;
        };
        const requestAnalytics = endpoint => fetch(endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        requestAnalytics(metaResults.dataset.metaUrl)
            .then(async response => {
                if (!response.ok && metaResults.dataset.metaFallbackUrl) {
                    response = await requestAnalytics(metaResults.dataset.metaFallbackUrl);
                }
                if (!response.ok) throw new Error('No fue posible consultar Meta Insights.');
                return response.json();
            })
            .then(analytics => {
                const totals = analytics?.summary?.totals ?? {};

                metaResults.querySelectorAll('[data-meta-total]').forEach(element => {
                    element.textContent = number(totals[element.dataset.metaTotal]);
                });

                document.querySelectorAll('[data-meta-followers]').forEach(element => {
                    const platform = analytics?.platforms?.[element.dataset.metaFollowers];
                    element.textContent = number(followerTotal(platform));
                });
            })
            .catch(() => {
                metaResults.classList.add('has-error');
                metaResults.querySelectorAll('.dashboard-results-grid em').forEach(element => {
                    element.textContent = 'Datos no disponibles';
                });
            })
            .finally(() => {
                metaResults.classList.remove('is-loading');
                metaResults.setAttribute('aria-busy', 'false');
            });
    }

    @php
        $dashboardCalendarEvents = collect(data_get($campaignDashboardSummary, 'tasks', []))
            ->map(function ($task) {
                $date = $task->publication_scheduled_at ?: $task->fecha_limite;

                if (!$date) {
                    return null;
                }

                return [
                    'date' => \Carbon\Carbon::parse($date)->toDateString(),
                    'title' => $task->titulo,
                    'publication' => (bool) $task->publication_scheduled_at,
                ];
            })
            ->filter()
            ->values();
    @endphp
    const calendarToggle = document.getElementById('campaign-calendar-toggle');
    const campaignCalendar = document.getElementById('campaign-calendar');
    const calendarGrid = document.getElementById('campaign-calendar-grid');
    const calendarMonth = document.getElementById('campaign-calendar-month');
    const agendaContent = document.querySelector('#campaign-agenda .campaign-agenda-list, #campaign-agenda .campaign-agenda-empty');
    const calendarEvents = @json($dashboardCalendarEvents);

    if (calendarToggle && campaignCalendar && calendarGrid && calendarMonth) {
        const firstFutureEvent = calendarEvents.find(item => new Date(`${item.date}T12:00:00`) >= new Date().setHours(0, 0, 0, 0));
        const initialDate = firstFutureEvent ? new Date(`${firstFutureEvent.date}T12:00:00`) : new Date();
        let displayedMonth = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);

        const dateKey = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

        function renderCampaignCalendar() {
            calendarMonth.textContent = displayedMonth.toLocaleDateString('es-BO', { month: 'long', year: 'numeric' });
            calendarGrid.replaceChildren();

            const firstDay = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth(), 1);
            const startOffset = (firstDay.getDay() + 6) % 7;
            const gridStart = new Date(firstDay);
            gridStart.setDate(firstDay.getDate() - startOffset);
            const lastDay = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 0);
            const totalCells = Math.ceil((startOffset + lastDay.getDate()) / 7) * 7;
            const todayKey = dateKey(new Date());

            for (let index = 0; index < totalCells; index++) {
                const day = new Date(gridStart);
                day.setDate(gridStart.getDate() + index);
                const key = dateKey(day);
                const dayEvents = calendarEvents.filter(item => item.date === key);
                const cell = document.createElement('div');
                cell.className = 'campaign-calendar-day';
                if (day.getMonth() !== displayedMonth.getMonth()) cell.classList.add('is-outside');
                if (key === todayKey) cell.classList.add('is-today');

                const number = document.createElement('time');
                number.dateTime = key;
                number.textContent = day.getDate();
                cell.appendChild(number);

                dayEvents.slice(0, 2).forEach(item => {
                    const event = document.createElement('span');
                    event.className = `campaign-calendar-event${item.publication ? ' is-publication' : ''}`;
                    event.textContent = item.title;
                    event.title = `${item.publication ? 'Publicación' : 'Entrega'}: ${item.title}`;
                    cell.appendChild(event);
                });

                if (dayEvents.length > 2) {
                    const more = document.createElement('span');
                    more.className = 'campaign-calendar-more';
                    more.textContent = `+${dayEvents.length - 2} más`;
                    cell.appendChild(more);
                }

                calendarGrid.appendChild(cell);
            }
        }

        calendarToggle.addEventListener('click', function () {
            const showingCalendar = !campaignCalendar.hidden;
            campaignCalendar.hidden = showingCalendar;
            if (agendaContent) agendaContent.hidden = !showingCalendar;
            calendarToggle.setAttribute('aria-pressed', String(!showingCalendar));
            calendarToggle.querySelector('i').className = showingCalendar ? 'far fa-calendar-alt' : 'fas fa-list';
            calendarToggle.querySelector('span').textContent = showingCalendar ? 'Ver calendario' : 'Ver lista';
            if (!showingCalendar) renderCampaignCalendar();
        });

        campaignCalendar.querySelectorAll('[data-calendar-direction]').forEach(button => {
            button.addEventListener('click', function () {
                displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + (button.dataset.calendarDirection === 'next' ? 1 : -1), 1);
                renderCampaignCalendar();
            });
        });
    }

    let activePieceReview = null;
    document.querySelectorAll('[data-open-piece-review]').forEach(button => {
        button.addEventListener('click', function () {
            activePieceReview = document.getElementById(button.dataset.openPieceReview);
            if (!activePieceReview) return;
            activePieceReview.hidden = false;
            document.body.style.overflow = 'hidden';
            activePieceReview.querySelector('[data-close-piece-review]')?.focus();
        });
    });

    function closePieceReview() {
        if (!activePieceReview) return;
        activePieceReview.hidden = true;
        activePieceReview.querySelectorAll('video').forEach(video => video.pause());
        document.body.style.overflow = '';
        activePieceReview = null;
    }

    document.querySelectorAll('[data-close-piece-review]').forEach(button => button.addEventListener('click', closePieceReview));
    document.querySelectorAll('.piece-review-form').forEach(form => {
        const comment = form.querySelector('textarea[name="comentario"]');
        form.querySelector('[data-submit-comment]')?.addEventListener('click', function (event) {
            if (comment?.value.trim()) return;
            event.preventDefault();
            comment?.focus();
            comment?.setCustomValidity('Escribe un comentario antes de enviarlo.');
            comment?.reportValidity();
            comment?.addEventListener('input', () => comment.setCustomValidity(''), {once:true});
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && activePieceReview) closePieceReview();
    });
});
</script>
@endpush

@endsection
