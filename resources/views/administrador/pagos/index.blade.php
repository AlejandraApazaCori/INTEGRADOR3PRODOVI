@extends('layouts.app')

@section('title', 'Gestión de Pagos')

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <div class="payments-page">
        <div class="payments-shell">
            @if(session('success'))
                <div class="payments-alert payments-alert-success mb-6 flex items-center justify-between rounded-xl p-4 animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-xl"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="payments-alert payments-alert-error mb-6 flex items-center justify-between rounded-xl p-4 animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.style.display='none'" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="payments-tabs flex flex-wrap gap-3">
                <a href="{{ route('administrador.pagos.index') }}" class="btn-action btn-blue">
                    <i class="fas fa-table-columns"></i>
                    General
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </a>
                <a href="{{ route('administrador.pagos.analiticas') }}" class="btn-action btn-indigo">
                    <i class="fas fa-chart-line"></i>
                    Analíticas
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </a>
                <button type="button" id="abrirReporteMensual" class="btn-action btn-purple">
                    <i class="fas fa-file-pdf"></i>
                    Reporte mensual
                    <span class="btn-action__mark" aria-hidden="true">
                        <svg viewBox="0 0 392.94 418.13">
                            <path d="M243.7,418.13C198.37,312.3,118.14,268.5,0,294.73,135.19,238.54,203.38,148.99,149.24,0c49.45,103.91,130.68,145.05,243.7,123.4-127.69,63.18-168.91,165.26-149.24,294.73Z"></path>
                        </svg>
                    </span>
                </button>
                <a href="{{ route('administrador.pagos.manual.crear') }}" class="btn-action payments-register-action">
                    <i class="fas fa-plus-circle"></i>
                    Registrar pago
                </a>
            </div>

            <!-- Banner con fondo geométrico -->
            <div class="payments-hero overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay absolute inset-0"></div>
                <div class="relative z-10 px-8 py-8">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-credit-card text-white text-2xl"></i>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-3xl font-bold text-white mb-1">Gestión de Pagos</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Administra y monitorea el estado de todas las suscripciones</p>
                        </div>
                    </div>
                </div>
            </div>

            <section class="payments-summary-grid" aria-label="Resumen de pagos filtrados">
                <article class="payments-summary-card payments-summary-income">
                    <div><span>Total de Ingresos</span><strong>{{ $paymentSummary['total_income'] }}</strong><small>Pagos completados según los filtros actuales</small></div>
                    <i class="fas fa-dollar-sign"></i>
                </article>
                <article class="payments-summary-card payments-summary-plan">
                    <div><span>Plan Más Contratado</span><strong>{{ $paymentSummary['most_hired_plan'] }}</strong><small>Mayor cantidad dentro del resultado filtrado</small></div>
                    <i class="fas fa-trophy"></i>
                </article>
                <article class="payments-summary-card payments-summary-records">
                    <div><span>Total de Registros</span><strong>{{ $paymentSummary['total_records'] }}</strong><small>Pagos que coinciden con los filtros actuales</small></div>
                    <i class="fas fa-chart-bar"></i>
                </article>
            </section>

            <div class="payments-users-content">
                <form id="payments-filter-form" method="GET" action="{{ route('administrador.pagos.index') }}" class="payments-users-filter-grid">
                    <div class="payments-users-search">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="Buscar por cliente, correo o código...">
                    </div>
                    @php
                        $paymentStatusLabels = ['completado' => 'Completados', 'pendiente' => 'Pendientes', 'rechazado' => 'Rechazados', 'cancelado' => 'Cancelados'];
                        $subscriptionStatusLabels = ['activa' => 'Activas', 'pendiente' => 'Pendientes', 'finalizada' => 'Finalizadas', 'cancelada' => 'Canceladas'];
                        $methodLabels = ['qr' => 'QR', 'fisico' => 'Físico'];
                    @endphp
                    <div class="payments-custom-dropdown" data-payment-dropdown>
                        <input type="hidden" name="plan" value="{{ request('plan') }}">
                        <button type="button" class="payments-custom-trigger" data-payment-trigger><i class="fas fa-layer-group"></i><span data-payment-label>{{ optional($planes->firstWhere('id', request('plan')))->nombre ?? 'Todos los planes' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="payments-custom-menu" data-payment-menu>
                            <button type="button" data-value="">Todos los planes</button>
                            @foreach($planes as $plan)<button type="button" data-value="{{ $plan->id }}">{{ $plan->nombre }}</button>@endforeach
                        </div>
                    </div>
                    <div class="payments-custom-dropdown" data-payment-dropdown>
                        <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                        <button type="button" class="payments-custom-trigger" data-payment-trigger><i class="fas fa-circle-check"></i><span data-payment-label>{{ $paymentStatusLabels[request('payment_status')] ?? 'Todos los pagos' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="payments-custom-menu" data-payment-menu><button type="button" data-value="">Todos los pagos</button><button type="button" data-value="completado">Completados</button><button type="button" data-value="pendiente">Pendientes</button><button type="button" data-value="rechazado">Rechazados</button><button type="button" data-value="cancelado">Cancelados</button></div>
                    </div>
                    <div class="payments-custom-dropdown" data-payment-dropdown>
                        <input type="hidden" name="subscription_status" value="{{ request('subscription_status') }}">
                        <button type="button" class="payments-custom-trigger" data-payment-trigger><i class="fas fa-arrows-rotate"></i><span data-payment-label>{{ $subscriptionStatusLabels[request('subscription_status')] ?? 'Todas las suscripciones' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="payments-custom-menu" data-payment-menu><button type="button" data-value="">Todas las suscripciones</button><button type="button" data-value="activa">Activas</button><button type="button" data-value="pendiente">Pendientes</button><button type="button" data-value="finalizada">Finalizadas</button><button type="button" data-value="cancelada">Canceladas</button></div>
                    </div>
                    <div class="payments-custom-dropdown" data-payment-dropdown>
                        <input type="hidden" name="method" value="{{ request('method') }}">
                        <button type="button" class="payments-custom-trigger" data-payment-trigger><i class="fas fa-wallet"></i><span data-payment-label>{{ $methodLabels[request('method')] ?? 'Todos los métodos' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="payments-custom-menu" data-payment-menu><button type="button" data-value="">Todos los métodos</button><button type="button" data-value="qr">QR</button><button type="button" data-value="fisico">Físico</button></div>
                    </div>
                    <label class="payments-users-date"><span>Pago desde</span><div><i class="fas fa-calendar-day"></i><input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()"></div></label>
                    <label class="payments-users-date"><span>Pago hasta</span><div><i class="fas fa-calendar-check"></i><input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()"></div></label>
                    <div class="payments-custom-dropdown" data-payment-dropdown>
                        <input type="hidden" name="order" value="{{ request('order', 'newest') }}">
                        <button type="button" class="payments-custom-trigger" data-payment-trigger><i class="fas fa-arrow-down-wide-short"></i><span data-payment-label>{{ request('order') === 'oldest' ? 'Más antiguos primero' : 'Más recientes primero' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="payments-custom-menu" data-payment-menu><button type="button" data-value="newest">Más recientes primero</button><button type="button" data-value="oldest">Más antiguos primero</button></div>
                    </div>
                    @if(request()->hasAny(['search', 'plan', 'payment_status', 'subscription_status', 'method', 'date_from', 'date_to', 'order']))
                        <a href="{{ route('administrador.pagos.index') }}" class="payments-clear-filters"><i class="fas fa-rotate-left"></i>Limpiar filtros</a>
                    @endif
                </form>

                @if(session('drive_success'))
                    <div class="payment-drive-alert payment-drive-success">
                        <span><i class="fas fa-circle-check"></i>{{ session('drive_success.message') }}</span>
                        <a href="{{ session('drive_success.url') }}" target="_blank" rel="noopener noreferrer">Abrir en Google Sheets</a>
                    </div>
                @endif
                @if(session('drive_error'))
                    <div class="payment-drive-alert payment-drive-error"><i class="fas fa-circle-exclamation"></i>{{ session('drive_error') }}</div>
                @endif

                @php
                    $activePaymentReportFilters = array_filter(
                        request()->only(['search', 'plan', 'payment_status', 'subscription_status', 'method', 'date_from', 'date_to', 'order']),
                        fn ($value) => $value !== null && $value !== ''
                    );
                @endphp

                <section class="payments-reports-section">
                    <div class="payments-reports-heading"><span class="payments-reports-eyebrow"><i class="fas fa-file-export"></i> Reportes</span></div>
                    <div class="payments-reports-grid">
                        <article class="payments-report-card payments-report-filtered">
                            <div class="payments-report-card-copy">
                                <span class="payments-report-card-icon"><i class="fas fa-filter"></i></span>
                                <div><h3>Listado filtrado</h3><p>Exporta exactamente los pagos que coinciden con los filtros activos.</p></div>
                            </div>
                            <div class="payments-report-actions">
                                <a href="{{ route('administrador.pagos.reportes.excel', ['report' => 'filtered'] + $activePaymentReportFilters) }}" class="payments-report-excel"><i class="fas fa-file-excel"></i>Excel</a>
                                <a href="{{ route('administrador.pagos.reportes.pdf', ['report' => 'filtered'] + $activePaymentReportFilters) }}" class="payments-report-pdf"><i class="fas fa-file-pdf"></i>PDF</a>
                                <button type="button" class="payment-drive-report-btn" data-report="filtered" data-search="{{ request('search') }}" data-plan="{{ request('plan') }}" data-payment-status="{{ request('payment_status') }}" data-subscription-status="{{ request('subscription_status') }}" data-method="{{ request('method') }}" data-date-from="{{ request('date_from') }}" data-date-to="{{ request('date_to') }}" data-order="{{ request('order') }}"><i class="fab fa-google-drive"></i>Drive</button>
                            </div>
                        </article>

                        <article class="payments-report-card payments-report-general">
                            <div class="payments-report-card-copy">
                                <span class="payments-report-card-icon"><i class="fas fa-credit-card"></i></span>
                                <div><h3>Listado general</h3><p>Incluye todos los pagos registrados, sin aplicar ningún filtro.</p></div>
                            </div>
                            <div class="payments-report-actions">
                                <a href="{{ route('administrador.pagos.reportes.excel', ['report' => 'general']) }}" class="payments-report-excel"><i class="fas fa-file-excel"></i>Excel</a>
                                <a href="{{ route('administrador.pagos.reportes.pdf', ['report' => 'general']) }}" class="payments-report-pdf"><i class="fas fa-file-pdf"></i>PDF</a>
                                <button type="button" class="payment-drive-report-btn" data-report="general"><i class="fab fa-google-drive"></i>Drive</button>
                            </div>
                        </article>
                    </div>
                </section>
            </div>

            <div id="payment-drive-modal" class="fixed inset-0 hidden items-center justify-center p-4" style="z-index:12000;background:rgba(17,24,39,.58);">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6" style="box-shadow:0 24px 60px rgba(0,0,0,.25);">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Guardar reporte en Drive</h3>
                            <p class="mt-1 text-sm text-gray-500">Consulta dónde está guardado el reporte o cambia su ubicación.</p>
                        </div>
                        <button type="button" id="payment-drive-close" class="h-9 w-9 rounded-full text-gray-500 hover:bg-gray-100"><i class="fas fa-times"></i></button>
                    </div>

                    <form id="payment-drive-form" method="POST" data-action-template="{{ route('administrador.pagos.reportes.drive', ['report' => '__REPORT__']) }}">
                        @csrf
                        <div id="payment-drive-filters"></div>
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-bold text-gray-700">Ubicación actual</label>
                            <div class="flex items-center justify-between gap-3 rounded-xl px-4 py-3" style="border:1px solid #dce7cc;background:#f7faf2;">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" style="background:#e4efd4;color:#638524;"><i class="fas fa-folder-open"></i></span>
                                    <span class="min-w-0"><strong id="payment-drive-current" class="block truncate text-sm text-gray-800">Consultando...</strong><small id="payment-drive-detail" class="block text-xs text-gray-500">Buscando el reporte en Drive</small></span>
                                </div>
                                <button id="payment-drive-change-location" type="button" class="hidden shrink-0 rounded-lg px-3 py-2 text-xs font-bold" style="background:#e6f4f5;color:#0d6975;"><i class="fas fa-location-dot mr-1"></i>Cambiar ubicación</button>
                            </div>
                        </div>

                        <div id="payment-drive-location-editor" class="hidden rounded-xl p-4" style="border:1px solid #dce7cc;background:#fbfcf9;">
                            <label class="mb-2 block text-sm font-bold text-gray-700">Carpeta de destino</label>
                            <div id="payment-drive-folder-dropdown" class="payments-custom-dropdown payment-drive-folder-dropdown">
                                <input id="payment-drive-folder" type="hidden" name="folder_id" value="">
                                <button id="payment-drive-folder-trigger" type="button" class="payments-custom-trigger" disabled aria-haspopup="listbox" aria-expanded="false">
                                    <i class="fas fa-folder"></i>
                                    <span id="payment-drive-folder-label">Consultando carpetas...</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div id="payment-drive-folder-menu" class="payments-custom-menu" role="listbox"></div>
                            </div>

                            <div class="my-4 flex items-center gap-3"><span class="h-px flex-1 bg-gray-200"></span><span class="text-xs font-bold uppercase text-gray-400">o crea una</span><span class="h-px flex-1 bg-gray-200"></span></div>
                            <label for="payment-drive-new-folder" class="mb-2 block text-sm font-bold text-gray-700">Nueva subcarpeta</label>
                            <div class="relative">
                                <i class="fas fa-folder-plus absolute left-4" style="top:16px;color:#7da533;"></i>
                                <input id="payment-drive-new-folder" name="new_folder" type="text" maxlength="80" class="w-full border pl-11 pr-4" style="height:48px;border-radius:12px;border-color:#d7dce2;" placeholder="Ej.: Reportes agosto">
                            </div>
                        </div>
                        <p id="payment-drive-status" class="mt-3 text-sm text-gray-500"></p>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" id="payment-drive-cancel" class="rounded-xl py-3 font-bold text-gray-600" style="background:#f3f4f6;">Cancelar</button>
                            <button type="submit" id="payment-drive-save" class="rounded-xl py-3 font-bold text-white" style="background:#7da533;"><i class="fab fa-google-drive mr-2"></i>Guardar en Drive</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="payments-users-table-area">
                <div class="payments-users-table-toolbar">
                    <div><i class="fas fa-credit-card"></i><span><strong>Pagos registrados</strong><small>{{ $pagos->total() }} resultado(s) con los filtros actuales</small></span></div>
                    <form id="payments-per-page-form" method="GET" action="{{ route('administrador.pagos.index') }}">
                        @foreach(request()->except(['page', 'per_page']) as $key => $value)
                            @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                        @endforeach
                        <label for="per_page">Mostrar</label>
                        <input id="per_page" name="per_page" type="number" min="5" max="100" step="1" list="payment-per-page-options" value="{{ $pagos->perPage() }}" aria-label="Pagos por página" onchange="this.form.submit()">
                        <datalist id="payment-per-page-options"><option value="5"><option value="10"><option value="25"><option value="50"><option value="100"></datalist>
                        <span>por página</span>
                    </form>
                </div>

                <div class="payments-users-green-table">
                <div class="payments-table-wrap overflow-x-auto">
                    <table class="payments-table min-w-full">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th scope="col" class="payments-number-heading px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider">N.º</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Cliente</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Plan</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Método</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Monto</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado del pago</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado de suscripción</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fecha de pago</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Inicio</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Fin</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">ID transacción (sistema)</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">ID transacción (Libélula)</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Comprobante</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($pagos as $index => $pago)
                                @php
                                    $estadoPagoClase = match($pago->estado) {
                                        'completado' => 'bg-green-100 text-green-800 border-green-200',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'cancelado' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                                    };

                                    $estadoSuscripcion = optional($pago->suscripcion)->estado;
                                    $estadoSuscripcionClase = match($estadoSuscripcion) {
                                        'activa' => 'bg-green-100 text-green-800 border-green-200',
                                        'finalizada' => 'bg-gray-100 text-gray-800 border-gray-200',
                                        'cancelada' => 'bg-red-100 text-red-800 border-red-200',
                                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    };

                                    $transaccionLibelula = $pago->libelulaTransaction;
                                    $esPagoLibelula = $pago->provider === 'libelula' || filled($pago->provider_transaction_id);
                                    $idTransaccionSistema = $transaccionLibelula?->identifier
                                        ?: ($pago->codigo_pago ?: 'PAGO-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
                                    $idTransaccionLibelula = $transaccionLibelula?->libelula_transaction_id
                                        ?: $pago->provider_transaction_id;
                                @endphp
                                <tr class="payments-users-table-row transition-colors duration-150">
                                    <td class="payments-row-number px-4 py-4 whitespace-nowrap text-center font-bold">{{ ($pagos->firstItem() ?? 1) + $index }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="payments-user-cell"><span>{{ mb_strtoupper(mb_substr(optional($pago->usuario)->name ?? 'N', 0, 1)) }}</span><strong>{{ optional($pago->usuario)->name ?? 'N/A' }}</strong></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ optional($pago->plan)->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 border-r border-gray-100">{{ ucfirst($pago->metodo ?? 'N/A') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border {{ $estadoPagoClase }}">{{ ucfirst($pago->estado ?? 'N/A') }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border {{ $estadoSuscripcionClase }}">{{ $estadoSuscripcion ? ucfirst($estadoSuscripcion) : 'N/A' }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ optional($pago->suscripcion)->fecha_inicio ? $pago->suscripcion->fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ optional($pago->suscripcion)->fecha_fin ? $pago->suscripcion->fecha_fin->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-3 border-r border-gray-100">
                                        <span class="payment-transaction-id" title="{{ $idTransaccionSistema }}">{{ $idTransaccionSistema }}</span>
                                    </td>
                                    <td class="px-4 py-3 border-r border-gray-100">
                                        @if($idTransaccionLibelula)
                                            <span class="payment-transaction-id" title="{{ $idTransaccionLibelula }}">{{ $idTransaccionLibelula }}</span>
                                        @else
                                            <span class="payment-not-applicable">No aplica</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-100">
                                        <div class="payment-receipt-actions">
                                            @if($esPagoLibelula)
                                                <button type="button" onclick="verComprobante({{ $pago->id }})" class="payment-receipt-primary" title="Abrir comprobante">
                                                    <i class="fas fa-file-invoice"></i>Comprobante
                                                </button>
                                            @else
                                                <a href="{{ route('administrador.pagos.descargar-recibo', $pago->id) }}" class="payment-receipt-primary" title="Descargar comprobante">
                                                    <i class="fas fa-download"></i>Descargar
                                                </a>
                                            @endif
                                            <a href="{{ route('administrador.pagos.ver-recibo-pdf', $pago->id) }}" target="_blank" rel="noopener" class="payment-receipt-shortcut" title="Ver archivo PDF" aria-label="Ver archivo PDF">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="verComprobante({{ $pago->id }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                                <i class="fas fa-file-invoice mr-1.5"></i>Ver detalle
                                            </button>
                                            @if($pago->estado === 'completado')
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors duration-200"
                                                    data-resend-email="{{ optional($pago->usuario)->email }}"
                                                    data-resend-url="{{ route('administrador.pagos.reenviar-correo', $pago) }}"
                                                    onclick="abrirModalReenvio(this)"
                                                >
                                                    <i class="fas fa-paper-plane mr-1.5"></i>Reenviar correo
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="px-4 py-10 text-center text-sm text-gray-500">No hay registros de pagos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="payments-users-pagination">{{ $pagos->onEachSide(1)->links('componentes.paginacion-es') }}</div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar el reenvío del correo -->
    <div id="reenvioCorreoModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/55 px-4 py-8 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="reenvioCorreoTitulo">
        <div id="reenvioCorreoDialog" class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-200">
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-purple-800 px-7 pb-7 pt-8 text-white">
                <div class="absolute -right-10 -top-12 h-36 w-36 rounded-full border-[24px] border-white/10"></div>
                <button type="button" onclick="cerrarModalReenvio()" class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
                <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-500 text-2xl shadow-lg shadow-purple-950/20">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3 id="reenvioCorreoTitulo" class="relative mt-5 text-2xl font-bold">Reenviar confirmación</h3>
                <p class="relative mt-2 text-sm leading-6 text-purple-100">Enviaremos nuevamente el resumen del pago y su comprobante PDF.</p>
            </div>

            <div class="px-7 py-7">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-500">Destinatario</p>
                    <div class="mt-2 flex items-center gap-3 text-sm font-semibold text-slate-800">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm"><i class="fas fa-envelope"></i></span>
                        <span id="reenvioCorreoDestino" class="min-w-0 break-all"></span>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-6 text-slate-600">¿Confirmas que deseas reenviar este correo de confirmación?</p>

                <form id="reenvioCorreoForm" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    <button type="button" onclick="cerrarModalReenvio()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</button>
                    <button id="confirmarReenvioBtn" type="submit" class="resend-confirm-button inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold shadow-lg transition hover:-translate-y-0.5" style="background-color:#ef6c22;color:#ffffff;border:1px solid #ef6c22;">
                        <i class="fas fa-paper-plane"></i> Sí, reenviar correo
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reporte Mensual -->
    <div id="reporteMensualModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
        <div class="relative top-20 mx-auto w-96 rounded-2xl border bg-white p-5 shadow-2xl">
            <div class="mt-3 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-purple-100">
                    <i class="fas fa-chart-bar text-2xl text-purple-600"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium leading-6 text-gray-900">Reporte mensual</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Se generará un reporte con todas las transacciones del mes actual.
                        Elige el formato que prefieres:
                    </p>
                </div>
                <div class="mt-4 flex justify-center space-x-4">
                    <button id="btnPdfMensual" class="rounded-xl bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                        Descargar PDF
                    </button>
                    <button id="btnExcelMensual" class="rounded-xl bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        Descargar Excel
                    </button>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="cerrarModalReporte" class="w-full rounded-xl bg-gray-200 px-4 py-2 text-base font-medium text-gray-800 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Comprobante -->
    <div id="comprobanteModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="comprobanteModalTitle" onclick="cerrarComprobanteDesdeFondo(event)">
        <div id="comprobanteModalDialog" class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex flex-none items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-7">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-orange-100 text-lg text-orange-600"><i class="fas fa-file-invoice"></i></span>
                    <div class="min-w-0">
                        <h3 id="comprobanteModalTitle" class="text-lg font-bold text-slate-900">Comprobante de pago</h3>
                        <p class="truncate text-xs text-slate-500">Detalle completo de la transacción</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarComprobanteModal()" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Cerrar comprobante">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="comprobanteModalBody" class="min-h-[260px] flex-1 overflow-y-auto bg-slate-100 p-3 sm:p-6"></div>
            <div class="flex flex-none flex-col-reverse gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-7">
                <button type="button" onclick="cerrarComprobanteModal()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cerrar</button>
                <a id="comprobanteDownloadLink" href="#" class="hidden items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-5 py-2.5 text-sm font-semibold text-orange-700 transition hover:bg-orange-100">
                    <i class="fas fa-download"></i>Descargar PDF
                </a>
                <a id="comprobanteViewLink" href="#" target="_blank" rel="noopener" class="hidden items-center justify-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                    <i class="fas fa-external-link-alt"></i>Ver PDF
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const abrirReporteMensual = document.getElementById('abrirReporteMensual');
            const reporteMensualModal = document.getElementById('reporteMensualModal');
            const cerrarModalReporte = document.getElementById('cerrarModalReporte');
            const btnPdfMensual = document.getElementById('btnPdfMensual');
            const btnExcelMensual = document.getElementById('btnExcelMensual');
            const paymentFilterForm = document.getElementById('payments-filter-form');
            const paymentDropdowns = [...document.querySelectorAll('[data-payment-dropdown]')];

            const closePaymentDropdowns = (except = null) => {
                paymentDropdowns.forEach(dropdown => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        dropdown.querySelector('[data-payment-trigger]')?.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            paymentDropdowns.forEach(dropdown => {
                const input = dropdown.querySelector('input[type="hidden"]');
                const trigger = dropdown.querySelector('[data-payment-trigger]');
                const label = dropdown.querySelector('[data-payment-label]');
                const options = [...dropdown.querySelectorAll('[data-payment-menu] button[data-value]')];
                trigger.setAttribute('aria-haspopup', 'listbox');
                trigger.setAttribute('aria-expanded', 'false');

                const sync = () => {
                    const selected = options.find(option => option.dataset.value === input.value) || options[0];
                    label.textContent = selected.textContent.trim();
                    options.forEach(option => option.classList.toggle('is-selected', option === selected));
                };

                trigger.addEventListener('click', event => {
                    event.stopPropagation();
                    const opening = !dropdown.classList.contains('is-open');
                    closePaymentDropdowns(dropdown);
                    dropdown.classList.toggle('is-open', opening);
                    trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
                });

                options.forEach(option => option.addEventListener('click', event => {
                    event.stopPropagation();
                    input.value = option.dataset.value;
                    sync();
                    closePaymentDropdowns();
                    paymentFilterForm.requestSubmit();
                }));

                sync();
            });

            document.addEventListener('click', () => closePaymentDropdowns());

            if (abrirReporteMensual) {
                abrirReporteMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.remove('hidden');
                });
            }

            if (cerrarModalReporte) {
                cerrarModalReporte.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                });
            }

            if (btnPdfMensual) {
                btnPdfMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                    window.open('/administrador/pagos/reporte-mensual/pdf', '_blank');
                });
            }

            if (btnExcelMensual) {
                btnExcelMensual.addEventListener('click', function () {
                    reporteMensualModal.classList.add('hidden');
                    window.open('/administrador/pagos/reporte-mensual/excel', '_blank');
                });
            }

            const reenvioModal = document.getElementById('reenvioCorreoModal');
            const reenvioForm = document.getElementById('reenvioCorreoForm');
            const confirmarReenvioBtn = document.getElementById('confirmarReenvioBtn');

            reenvioModal?.addEventListener('click', function (event) {
                if (event.target === reenvioModal) cerrarModalReenvio();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closePaymentDropdowns();
                if (event.key === 'Escape' && !reenvioModal?.classList.contains('hidden')) cerrarModalReenvio();
            });

            reenvioForm?.addEventListener('submit', function () {
                confirmarReenvioBtn.disabled = true;
                confirmarReenvioBtn.classList.add('opacity-70', 'cursor-wait');
                confirmarReenvioBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando...';
            });

            initializePaymentDriveReports();
        });

        function initializePaymentDriveReports() {
            const modal = document.getElementById('payment-drive-modal');
            if (!modal) return;

            const form = document.getElementById('payment-drive-form');
            const filters = document.getElementById('payment-drive-filters');
            const folderSelect = document.getElementById('payment-drive-folder');
            const folderDropdown = document.getElementById('payment-drive-folder-dropdown');
            const folderTrigger = document.getElementById('payment-drive-folder-trigger');
            const folderLabel = document.getElementById('payment-drive-folder-label');
            const folderMenu = document.getElementById('payment-drive-folder-menu');
            const newFolder = document.getElementById('payment-drive-new-folder');
            const locationEditor = document.getElementById('payment-drive-location-editor');
            const changeLocation = document.getElementById('payment-drive-change-location');
            const currentFolder = document.getElementById('payment-drive-current');
            const currentDetail = document.getElementById('payment-drive-detail');
            const status = document.getElementById('payment-drive-status');
            const saveButton = document.getElementById('payment-drive-save');
            const foldersUrl = @json(route('administrador.pagos.reportes.drive-folders'));
            const filterKeys = ['search', 'plan', 'payment_status', 'subscription_status', 'method', 'date_from', 'date_to', 'order'];

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                folderDropdown.classList.remove('is-open');
                folderTrigger.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');
            };

            const selectFolder = (id, name, selectedButton = null) => {
                folderSelect.value = id;
                folderLabel.textContent = name;
                folderMenu.querySelectorAll('button').forEach(option => option.classList.remove('is-selected'));
                selectedButton?.classList.add('is-selected');
                folderDropdown.classList.remove('is-open');
                folderTrigger.setAttribute('aria-expanded', 'false');
            };

            const addFolderOption = (id, name) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.dataset.value = id;
                option.setAttribute('role', 'option');
                const icon = document.createElement('i');
                icon.className = 'fas fa-folder';
                const label = document.createElement('span');
                label.textContent = name;
                option.append(icon, label);
                option.addEventListener('click', event => {
                    event.stopPropagation();
                    selectFolder(id, name, option);
                });
                folderMenu.appendChild(option);
            };

            const addFilter = (name, value) => {
                if (!value) return;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                filters.appendChild(input);
            };

            document.querySelectorAll('.payment-drive-report-btn').forEach(button => {
                button.addEventListener('click', async () => {
                    filters.innerHTML = '';
                    filterKeys.forEach(key => {
                        const attribute = `data-${key.replaceAll('_', '-')}`;
                        addFilter(key, button.getAttribute(attribute));
                    });
                    form.action = form.dataset.actionTemplate.replace('__REPORT__', button.dataset.report);
                    folderSelect.value = '';
                    folderLabel.textContent = 'Consultando carpetas...';
                    folderMenu.innerHTML = '';
                    folderTrigger.disabled = true;
                    newFolder.value = '';
                    locationEditor.classList.add('hidden');
                    changeLocation.classList.add('hidden');
                    currentFolder.textContent = 'Consultando...';
                    currentDetail.textContent = 'Buscando el reporte en Drive';
                    status.textContent = 'Consultando las carpetas dentro de PRODOVI...';
                    status.style.color = '';
                    saveButton.disabled = true;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');

                    try {
                        const url = new URL(foldersUrl, window.location.origin);
                        url.searchParams.set('report', button.dataset.report);
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'No se pudieron consultar las carpetas.');

                        const locations = [
                            { id: data.root.id, name: `${data.root.name} (carpeta principal)` },
                            ...data.folders,
                        ];
                        locations.forEach(folder => addFolderOption(folder.id, folder.name));

                        if (data.current_folder) {
                            if (!locations.some(folder => folder.id === data.current_folder.id)) {
                                addFolderOption(data.current_folder.id, data.current_folder.name);
                            }
                            const selectedButton = [...folderMenu.querySelectorAll('button')]
                                .find(option => option.dataset.value === data.current_folder.id);
                            selectFolder(data.current_folder.id, data.current_folder.name, selectedButton);
                            currentFolder.textContent = data.current_folder.name;
                            currentDetail.textContent = 'El reporte ya está guardado en esta carpeta';
                            changeLocation.classList.remove('hidden');
                            status.textContent = 'Al guardar, se actualizará el mismo reporte y conservará su enlace.';
                        } else {
                            const rootButton = [...folderMenu.querySelectorAll('button')]
                                .find(option => option.dataset.value === data.root.id);
                            selectFolder(data.root.id, `${data.root.name} (carpeta principal)`, rootButton);
                            currentFolder.textContent = 'Reporte aún no creado';
                            currentDetail.textContent = 'Elige dónde deseas guardarlo por primera vez';
                            locationEditor.classList.remove('hidden');
                            status.textContent = data.folders.length
                                ? `${data.folders.length} carpeta(s) disponible(s).`
                                : 'No hay subcarpetas. Puedes guardar en PRODOVI o crear una nueva.';
                        }

                        folderTrigger.disabled = false;
                        saveButton.disabled = false;
                    } catch (error) {
                        currentFolder.textContent = 'No se pudo consultar la ubicación';
                        currentDetail.textContent = 'Inténtalo nuevamente';
                        status.textContent = error.message;
                        status.style.color = '#b91c1c';
                    }
                });
            });

            document.getElementById('payment-drive-close').addEventListener('click', closeModal);
            document.getElementById('payment-drive-cancel').addEventListener('click', closeModal);
            changeLocation.addEventListener('click', () => {
                locationEditor.classList.remove('hidden');
                changeLocation.classList.add('hidden');
                status.textContent = 'Selecciona otra carpeta o escribe el nombre de una nueva subcarpeta.';
            });
            folderTrigger.addEventListener('click', event => {
                event.stopPropagation();
                if (folderTrigger.disabled) return;
                const opening = !folderDropdown.classList.contains('is-open');
                folderDropdown.classList.toggle('is-open', opening);
                folderTrigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
            });
            document.addEventListener('click', () => {
                folderDropdown.classList.remove('is-open');
                folderTrigger.setAttribute('aria-expanded', 'false');
            });
            modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });
            form.addEventListener('submit', () => {
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
            });
        }

        function abrirModalReenvio(button) {
            const modal = document.getElementById('reenvioCorreoModal');
            const dialog = document.getElementById('reenvioCorreoDialog');
            const form = document.getElementById('reenvioCorreoForm');
            const destination = document.getElementById('reenvioCorreoDestino');

            form.action = button.dataset.resendUrl;
            destination.textContent = button.dataset.resendEmail || 'Correo no disponible';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            dialog.animate([
                { opacity: 0, transform: 'translateY(18px) scale(.96)' },
                { opacity: 1, transform: 'translateY(0) scale(1)' }
            ], { duration: 220, easing: 'cubic-bezier(.22,.61,.36,1)' });
        }

        function cerrarModalReenvio() {
            const modal = document.getElementById('reenvioCorreoModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function verComprobante(pagoId) {
            const modal = document.getElementById('comprobanteModal');
            const body = document.getElementById('comprobanteModalBody');
            const viewLink = document.getElementById('comprobanteViewLink');
            const downloadLink = document.getElementById('comprobanteDownloadLink');

            body.innerHTML = '<div class="flex min-h-[260px] flex-col items-center justify-center gap-3 text-slate-500"><i class="fas fa-circle-notch fa-spin text-3xl text-orange-500"></i><span class="text-sm font-semibold">Cargando comprobante...</span></div>';
            viewLink.classList.add('hidden');
            viewLink.classList.remove('inline-flex');
            downloadLink.classList.add('hidden');
            downloadLink.classList.remove('inline-flex');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            document.getElementById('comprobanteModalDialog').animate([
                { opacity: 0, transform: 'translateY(20px) scale(.97)' },
                { opacity: 1, transform: 'translateY(0) scale(1)' }
            ], { duration: 220, easing: 'cubic-bezier(.22,.61,.36,1)' });

            fetch(`${@json(url('/administrador/pagos/ver-recibo'))}/${pagoId}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No se pudo cargar el comprobante.');
                    }
                    return response.json();
                })
                .then(data => {
                    body.innerHTML = data.html;
                    viewLink.href = data.view_url;
                    downloadLink.href = data.download_url;
                    viewLink.classList.remove('hidden');
                    viewLink.classList.add('inline-flex');
                    downloadLink.classList.remove('hidden');
                    downloadLink.classList.add('inline-flex');
                })
                .catch(() => {
                    body.innerHTML = '<div class="flex min-h-[260px] flex-col items-center justify-center gap-3 text-center"><span class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-xl text-red-600"><i class="fas fa-exclamation-triangle"></i></span><p class="font-semibold text-slate-800">No se pudo cargar el comprobante.</p><p class="text-sm text-slate-500">Intenta nuevamente en unos segundos.</p></div>';
                });
        }

        function cerrarComprobanteModal() {
            const modal = document.getElementById('comprobanteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function cerrarComprobanteDesdeFondo(event) {
            if (event.target === event.currentTarget) {
                cerrarComprobanteModal();
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('comprobanteModal').classList.contains('hidden')) {
                cerrarComprobanteModal();
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
                linear-gradient(rgba(15,23,42,0.28), rgba(15,23,42,0.28)),
                radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
            background-size:     100% 100%, 50% 50%, 50% 50%, 50% 50%, 50% 50%;
            background-position: 0 0, 0 0, 100% 0, 100% 100%, 0 100%;
            background-repeat:   no-repeat;
        }

        /* Estilos de los botones de acción */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 999px;
            border: 1.5px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            transition: all 200ms cubic-bezier(0.22, 0.61, 0.36, 1);
            text-decoration: none;
            color: white;
            position: relative;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .btn-action:active {
            transform: translateY(1px);
        }

        .btn-action i {
            font-size: 0.875rem;
        }

        .btn-action__mark {
            display: inline-grid;
            place-items: center;
            width: 16px;
            height: 16px;
            color: rgba(255, 255, 255, 0.8);
            transform: rotate(-14deg);
            transition: transform 600ms cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .btn-action__mark svg {
            width: 100%;
            height: 100%;
            fill: currentColor;
            display: block;
        }

        .btn-action:hover .btn-action__mark {
            transform: rotate(18deg);
        }

        /* Colores de los botones */
        .btn-blue {
            background: #2563EB;
            border-color: #2563EB;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);
        }

        .btn-blue:hover {
            background: #1D4ED8;
            border-color: #1D4ED8;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.35);
        }

        .btn-indigo {
            background: #4F46E5;
            border-color: #4F46E5;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);
        }

        .btn-indigo:hover {
            background: #4338CA;
            border-color: #4338CA;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.35);
        }

        .btn-purple {
            background: #7C3AED;
            border-color: #7C3AED;
            box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.25);
        }

        .btn-purple:hover {
            background: #6D28D9;
            border-color: #6D28D9;
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.35);
        }

        /* Animación */
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

        .resend-confirm-button {
            background-color: #ef6c22 !important;
            color: #ffffff !important;
            border-color: #ef6c22 !important;
            box-shadow: 0 10px 22px rgba(239, 108, 34, 0.25);
        }

        .resend-confirm-button:hover {
            background-color: #d95d16 !important;
            border-color: #d95d16 !important;
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
            
            .btn-action {
                justify-content: center;
                width: 100%;
                padding: 0.75rem 1.25rem;
            }

            .btn-action__mark {
                display: none;
            }
        }

        /* Rediseño de pagos alineado con Gestión de usuarios */
        .payments-page { min-height: 100vh; padding: 20px 0 48px; background: #fff; color: #302834; }
        .payments-shell { position: relative; display: flex; flex-direction: column; width: 100%; }
        .payments-hero { order: 1; width: 100%; min-height: 180px; margin: 0; border-radius: 0; box-shadow: none; }
        .payments-hero .relative.z-10 { display: flex; align-items: center; min-height: 180px; padding: 30px 48px; }
        .payments-hero .relative.z-10 > div { width: 100%; padding-right: 570px; }
        .payments-hero h1 { margin: 0 0 4px; color: #fff; font-size: clamp(1.55rem,3vw,2.25rem); font-weight: 900; letter-spacing: -.04em; }
        .payments-hero h1::before { content: 'Administración financiera'; display: block; margin-bottom: 7px; color: #dbeafe; font-size: .68rem; font-weight: 900; letter-spacing: .15em; text-transform: uppercase; }
        .payments-hero p { color: #dbeafe !important; font-size: .74rem !important; font-weight: 600; }
        .payments-hero .h-14.w-14 { width: 52px; height: 52px; border: 1px solid rgba(255,255,255,.24); border-radius: 14px; background: rgba(255,255,255,.14) !important; backdrop-filter: blur(5px); }
        .payments-tabs { position: absolute; z-index: 20; top: 67px; right: 48px; display: flex; justify-content: flex-end; padding: 0; }
        .payments-tabs .btn-action { min-height: 42px; gap: 8px; padding: 10px 13px; border: 1px solid rgba(255,255,255,.24); border-radius: .65rem; background: rgba(255,255,255,.12); color: #fff; box-shadow: none; font-size: .69rem; font-weight: 900; backdrop-filter: blur(4px); }
        .payments-tabs .btn-action:first-child { border-color: #fff; background: #fff; color: #4f46e5; }
        .payments-tabs .payments-register-action { border-color:#ef6c22; background:#ef6c22; color:#fff; }
        .payments-tabs .btn-action:hover { transform: translateY(-2px); border-color: #fff; background: #fff; color: #4f46e5; box-shadow: 0 8px 20px rgba(31,41,55,.16); }
        .payments-tabs .btn-action__mark { display: none; }
        .payments-alert { order: 3; width: calc(100% - 48px); margin: 24px auto 0 !important; border: 1px solid; box-shadow: none; font-size: .76rem; font-weight: 700; }
        .payments-alert-success { border-color: #bfe3c5; background: #ecf8ee; color: #276738; }.payments-alert-error { border-color: #f3c4c4; background: #fff0f0; color: #a72d2d; }
        .payments-alert button { color: inherit; opacity: .65; }.payments-alert button:hover { opacity: 1; }
        .payments-summary-grid{order:4;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:24px 24px 0}.payments-summary-card{--sum-accent:#117e8c;--sum-soft:#e6f4f5;--sum-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px;border:1px solid rgba(var(--sum-rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--sum-soft));box-shadow:inset 0 4px 0 var(--sum-accent),0 10px 24px rgba(45,66,34,.09);transition:.22s}.payments-summary-card::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--sum-rgb),.09);border-radius:50%}.payments-summary-card::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--sum-accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.payments-summary-card:hover{transform:translateY(-5px);border-color:rgba(var(--sum-rgb),.38);box-shadow:inset 0 4px 0 var(--sum-accent),0 17px 32px rgba(var(--sum-rgb),.16)}.payments-summary-card>div,.payments-summary-card>i{position:relative;z-index:1}.payments-summary-card span,.payments-summary-card small{display:block}.payments-summary-card span{color:#596170;font-size:.7rem;font-weight:900;letter-spacing:.025em;text-transform:uppercase}.payments-summary-card strong{display:block;max-width:260px;margin-top:9px;overflow:hidden;color:#263024;font-size:1.85rem;font-weight:900;line-height:1.1;text-overflow:ellipsis;white-space:nowrap}.payments-summary-card small{margin-top:8px;color:#7f8878;font-size:.62rem;font-weight:600}.payments-summary-card>i{width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.55);border-radius:14px;background:var(--sum-accent);color:#fff;font-size:1.18rem;box-shadow:0 8px 17px rgba(var(--sum-rgb),.27),inset 0 1px 0 rgba(255,255,255,.28);transition:.22s}.payments-summary-card:hover>i{transform:rotate(-6deg) scale(1.06)}.payments-summary-income{--sum-accent:#117e8c;--sum-soft:#e6f4f5;--sum-rgb:17,126,140}.payments-summary-plan{--sum-accent:#7da533;--sum-soft:#f0f6e7;--sum-rgb:125,165,51}.payments-summary-records{--sum-accent:#e3a122;--sum-soft:#fff6df;--sum-rgb:227,161,34}
        .payments-users-content{order:5;margin:24px 24px 0;padding:20px;border:1px solid #e1e3de;border-radius:16px;background:#f8f8f6;box-shadow:0 9px 22px rgba(55,60,52,.06)}.payments-users-filter-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px;align-items:end}.payments-users-search{position:relative;grid-column:span 3}.payments-users-search>i{position:absolute;z-index:1;top:17px;left:16px;color:#737a70;font-size:.9rem}.payments-users-search input{width:100%;height:50px;padding:0 16px 0 46px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;color:#3f443d;box-shadow:0 2px 5px rgba(55,60,52,.08);font-size:.82rem;outline:0}.payments-users-search input:focus{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12),0 3px 8px rgba(55,60,52,.06)}
        .payment-drive-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;padding:13px 16px;border:1px solid;border-radius:12px;font-size:.75rem;font-weight:700}.payment-drive-alert span{display:flex;align-items:center;gap:8px}.payment-drive-alert a{text-decoration:underline}.payment-drive-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.payment-drive-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}.payments-reports-section{margin-top:20px;padding-top:5px}.payments-reports-heading{margin-bottom:16px;padding:0 2px 12px;border-bottom:1px solid #e1e3de}.payments-reports-eyebrow{display:flex;align-items:center;gap:7px;color:#737a70;font-size:.65rem;font-weight:900;letter-spacing:.11em;text-transform:uppercase}.payments-reports-eyebrow i{color:#62685f}.payments-reports-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.payments-report-card{--report-accent:#ef6c22;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:118px;padding:18px;border:1px solid #dfe3e7;border-left:4px solid var(--report-accent);border-radius:15px;background:#fff;box-shadow:0 5px 14px rgba(45,51,58,.06)}.payments-report-general{--report-accent:#6259f7}.payments-report-card-copy{display:flex;align-items:flex-start;gap:13px;min-width:0}.payments-report-card-icon{width:44px;height:44px;display:grid;place-items:center;flex:0 0 auto;border-radius:13px;background:var(--report-accent);color:#fff;font-size:1rem;box-shadow:0 7px 15px color-mix(in srgb,var(--report-accent) 25%,transparent)}.payments-report-card h3{margin:1px 0 4px;color:#25272b;font-size:.88rem;font-weight:900}.payments-report-card p{max-width:250px;margin:0;color:#737a70;font-size:.68rem;line-height:1.55}.payments-report-actions{display:grid;grid-template-columns:repeat(3,minmax(72px,1fr));gap:7px;flex:0 0 auto}.payments-report-actions a,.payments-report-actions button{min-height:39px;display:flex;align-items:center;justify-content:center;gap:6px;padding:0 10px;border-radius:10px;font-size:.66rem;font-weight:900;transition:.18s}.payments-report-actions a:hover,.payments-report-actions button:hover{transform:translateY(-1px);filter:brightness(.96)}.payments-report-excel{background:#00a36c;color:#fff}.payments-report-pdf{background:#dc2626;color:#fff}.payment-drive-report-btn{border:1px solid #bccaff;background:#fff;color:#4f46e5}
        .payment-drive-folder-dropdown{position:relative!important;z-index:13000!important;width:100%;grid-column:auto!important}.payment-drive-folder-dropdown .payments-custom-trigger{height:50px;border-color:#dedede;box-shadow:0 2px 5px rgba(0,0,0,.1)}.payment-drive-folder-dropdown .payments-custom-trigger:hover,.payment-drive-folder-dropdown.is-open .payments-custom-trigger{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.12),0 3px 8px rgba(0,0,0,.08)}.payment-drive-folder-dropdown .payments-custom-trigger>i:first-child{color:#117e8c}.payment-drive-folder-dropdown .payments-custom-menu{z-index:13001;max-height:190px;border-color:#e2e8d8;box-shadow:0 16px 35px rgba(31,41,55,.16)}.payment-drive-folder-dropdown .payments-custom-menu button{display:flex;align-items:center;gap:9px}.payment-drive-folder-dropdown .payments-custom-menu button:hover,.payment-drive-folder-dropdown .payments-custom-menu button.is-selected{background:#edf4e4;color:#587923}.payment-drive-folder-dropdown .payments-custom-menu button i{width:16px;color:#7da533}.payment-drive-folder-dropdown .payments-custom-menu button span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .payments-custom-dropdown{position:relative;z-index:40;grid-column:span 3}.payments-custom-dropdown.is-open{z-index:90}.payments-custom-trigger{position:relative;width:100%;height:50px;display:flex;align-items:center;gap:10px;padding:0 40px 0 16px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;color:#3f443d;text-align:left;box-shadow:0 2px 5px rgba(55,60,52,.08);cursor:pointer;transition:.18s}.payments-custom-trigger:hover,.payments-custom-dropdown.is-open .payments-custom-trigger{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12),0 3px 8px rgba(55,60,52,.06)}.payments-custom-trigger>i:first-child{width:18px;flex:0 0 auto;color:#737a70;text-align:center;font-size:.9rem}.payments-custom-trigger>span{min-width:0;overflow:hidden;flex:1;font-size:.8rem;font-weight:600;text-overflow:ellipsis;white-space:nowrap}.payments-custom-trigger>i:last-child{position:absolute;right:16px;color:#8a9186;font-size:.68rem;transition:transform .18s}.payments-custom-dropdown.is-open .payments-custom-trigger>i:last-child{transform:rotate(180deg)}.payments-custom-menu{position:absolute;top:calc(100% + 8px);right:0;left:0;display:none;max-height:260px;overflow-y:auto;padding:7px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;box-shadow:0 18px 40px rgba(55,60,52,.16)}.payments-custom-dropdown.is-open .payments-custom-menu{display:block}.payments-custom-menu button{width:100%;min-height:39px;padding:8px 10px;border:0;border-radius:8px;background:transparent;color:#565d53;text-align:left;font-size:.72rem;font-weight:700;cursor:pointer}.payments-custom-menu button:hover,.payments-custom-menu button.is-selected{background:#eff0ed;color:#3f443d}.payments-custom-menu button.is-selected::after{content:'✓';float:right;color:#62685f;font-weight:900}
        .payments-users-date{grid-column:span 3}.payments-users-date>span{display:block;margin:0 0 6px 2px;color:#565d53;font-size:.7rem;font-weight:800}.payments-users-date>div{height:50px;display:flex;align-items:center;gap:11px;padding:0 15px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;box-shadow:0 2px 5px rgba(55,60,52,.08)}.payments-users-date>div:focus-within{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}.payments-users-date i{color:#737a70}.payments-users-date input{min-width:0;flex:1;border:0;background:transparent;color:#3f443d;font-size:.8rem;font-weight:700;outline:0}.payments-clear-filters{grid-column:1/-1;min-height:50px;display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #d7dad4;border-radius:14px;background:#fff;color:#62685f;font-size:.78rem;font-weight:800;text-decoration:none}.payments-clear-filters:hover{background:#eff0ed;color:#3f443d}
        .payments-users-table-area{order:6;margin:24px 24px 0}.payments-users-table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:12px;padding:12px 15px;border:1px solid #e2ead5;border-radius:14px;background:#f9fbf5}.payments-users-table-toolbar>div{display:flex;align-items:center;gap:11px}.payments-users-table-toolbar>div>i{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#edf4e4;color:#7da533}.payments-users-table-toolbar strong,.payments-users-table-toolbar small{display:block}.payments-users-table-toolbar strong{color:#31382b;font-size:.8rem}.payments-users-table-toolbar small{margin-top:2px;color:#8a9380;font-size:.62rem}#payments-per-page-form{display:flex;align-items:center;gap:8px;color:#66705c;font-size:.72rem;font-weight:700}#payments-per-page-form #per_page{width:76px;height:38px;padding:0 8px;border:1px solid #ccd9bb;border-radius:10px;background:#fff;color:#405128;text-align:center;font-weight:900;outline:0}#payments-per-page-form #per_page:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.13)}
        .payments-users-green-table{overflow:hidden;border:1px solid #d8e3c7;border-radius:16px;background:#fff;box-shadow:0 9px 24px rgba(91,121,38,.12)}.payments-users-green-table .payments-table-wrap{margin:0;border:0;border-radius:0}.payments-users-green-table .payments-table{width:100%;min-width:1780px;border-collapse:collapse}.payments-users-green-table .payments-table thead,.payments-users-green-table .payments-table thead tr,.payments-users-green-table .payments-table th{background:#7da533!important}.payments-users-green-table .payments-table th{padding:16px 18px;border-right:1px solid rgba(255,255,255,.3)!important;border-bottom:0!important;color:#fff!important;text-align:left;font-size:.62rem;font-weight:900;letter-spacing:.055em;text-transform:uppercase}.payments-users-green-table .payments-table th:last-child,.payments-users-green-table .payments-table td:last-child{border-right:0!important}.payments-users-green-table .payments-table td{padding:16px 18px;border-right:1px solid #d8e3c7!important;border-bottom:1px solid #dfe8d1!important;color:#4b5563;font-size:.72rem;vertical-align:middle}.payments-users-green-table .payments-table tbody tr:nth-child(odd) td{background:#fff!important}.payments-users-green-table .payments-table tbody tr:nth-child(even) td{background:#f1f7e8!important}.payments-users-green-table .payments-table tbody tr:hover td{background:#e6f0d8!important}.payments-users-green-table .payments-table tbody tr:last-child td{border-bottom:0!important}.payments-number-heading{width:72px;text-align:center!important}.payments-row-number{color:#638524!important}.payments-user-cell{display:flex;align-items:center;gap:12px}.payments-user-cell>span{width:40px;height:40px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:linear-gradient(135deg,#7da533,#117e8c);color:#fff;font-weight:900;box-shadow:0 4px 10px rgba(91,121,38,.2)}.payments-user-cell strong{color:#111827;font-size:.75rem}.payments-users-green-table .payments-table td .inline-flex.rounded-full{border-radius:999px;font-size:.59rem}.payments-users-green-table .payments-table td .flex.items-center{flex-wrap:wrap;gap:5px}.payments-users-green-table .payments-table td button,.payments-users-green-table .payments-table td a{font-size:.59rem;font-weight:800}.payments-users-pagination{margin-top:32px}
        .payment-transaction-id{display:block;max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;border-radius:8px;background:#f1f5f9;padding:7px 9px;color:#334155;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:.62rem;font-weight:700}.payment-not-applicable{display:inline-flex;border-radius:999px;background:#f1f5f9;padding:5px 9px;color:#64748b;font-size:.59rem;font-weight:800}.payment-receipt-actions{display:flex;align-items:center;gap:5px}.payment-receipt-primary,.payment-receipt-shortcut{display:inline-flex;align-items:center;justify-content:center;border:1px solid #fed7aa;background:#fff7ed;color:#c2410c;transition:.18s ease}.payment-receipt-primary{gap:6px;border-radius:9px;padding:7px 10px}.payment-receipt-shortcut{width:30px;height:30px;border-radius:9px}.payment-receipt-primary:hover,.payment-receipt-shortcut:hover{background:#ffedd5;color:#9a3412;transform:translateY(-1px)}
        #reenvioCorreoDialog > div:first-child { background:linear-gradient(135deg,#2563eb,#4f46e5 58%,#117e8c) !important; }#reporteMensualModal>div { overflow:hidden; border:1px solid #d9e5ef; border-radius:18px; }
        #comprobanteModal{position:fixed!important;inset:0!important;z-index:9999!important;width:100vw;height:100vh;align-items:center!important;justify-content:center!important;overflow:hidden;background:rgba(15,23,42,.68)!important;padding:24px;backdrop-filter:blur(6px)}#comprobanteModal.flex{display:flex!important}#comprobanteModalDialog{position:relative;margin:auto;width:min(960px,calc(100vw - 48px));max-height:calc(100vh - 48px);border:1px solid #d8dee8;border-radius:24px!important;background:#fff;box-shadow:0 30px 90px rgba(15,23,42,.38)}#comprobanteModalBody{overscroll-behavior:contain}#comprobanteModalBody>.bg-white{border:1px solid #e2e8f0;border-radius:16px;box-shadow:none!important}#comprobanteModalBody header{border-radius:15px 15px 0 0}
        @media(max-width:1100px){.payments-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.payments-users-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.payments-users-filter-grid>*,.payments-users-search,.payments-custom-dropdown,.payments-users-date,.payments-clear-filters{grid-column:span 1}.payments-users-search{grid-column:1/-1}.payments-reports-grid{grid-template-columns:1fr}}
        @media(max-width:980px) { .payments-hero .relative.z-10 > div { padding-right:0; }.payments-tabs { position:static; order:2; justify-content:center; margin:14px 24px 0; }.payments-tabs .btn-action { border-color:#dce4f3; background:#f4f7fd; color:#4f46e5; }.payments-tabs .btn-action:first-child { background:#4f46e5;color:#fff; }.payments-alert { order:3; }.payments-users-content{margin-top:18px} }
        @media(max-width:640px){.payments-page{padding-top:20px}.payments-hero{min-height:205px}.payments-hero .relative.z-10{min-height:205px;padding:28px 20px}.payments-tabs{display:grid;grid-template-columns:1fr;margin-right:12px;margin-left:12px}.payments-summary-grid,.payments-users-content,.payments-users-table-area{grid-template-columns:1fr;margin-right:12px;margin-left:12px}.payments-users-filter-grid{grid-template-columns:1fr}.payments-users-filter-grid>*,.payments-users-search{grid-column:1}.payments-users-table-toolbar{align-items:flex-start;flex-direction:column}.payments-alert{width:calc(100% - 24px)}.payments-report-card{align-items:stretch;flex-direction:column}.payments-report-card p{max-width:none}.payments-report-actions{width:100%;grid-template-columns:repeat(3,1fr)}.payment-drive-alert{align-items:flex-start;flex-direction:column}#comprobanteModal{padding:12px}#comprobanteModalDialog{width:calc(100vw - 24px);max-height:calc(100vh - 24px);border-radius:18px!important}#comprobanteModalBody{padding:10px}}
    </style>
@endsection
