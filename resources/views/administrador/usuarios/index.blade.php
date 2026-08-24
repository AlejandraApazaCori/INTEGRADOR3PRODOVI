@extends('layouts.app')

@section('title', 'Gestión de Usuarios')
@include('a.css.admin.index-usuarios')

@section('content')
    <div class="min-h-screen" style="background: #ffffff;">
        <div class="w-full pb-8">
            <!-- Header Section -->
          

        @include('administrador.usuarios.cardsusu')

        <div class="users-page-content">
            
            <!-- Search and Filters -->
            <div class="users-filter-panel mb-8 animate-fade-up" style="animation-delay: 0.2s">
                <form id="users-filter-form" action="{{ route('administrador.usuarios.index') }}" method="GET" class="users-filter-grid">
                    <div class="relative users-search-filter">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               class="w-full pl-12 pr-4 border bg-white focus:outline-none"
                               style="height: 50px; border-color: #dedede; border-radius: 14px; box-shadow: 0 2px 5px rgba(0,0,0,.1);"
                               placeholder="Buscar por usuario, nombre o correo...">
                        <svg class="absolute left-4 w-5 h-5 text-gray-400" style="top: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="custom-filter-dropdown relative xl:w-56" data-custom-dropdown>
                        <input type="hidden" name="role" id="role" value="{{ request('role') }}">
                        <button type="button" class="custom-filter-trigger" data-dropdown-trigger>
                            <i class="fas fa-user-tag custom-filter-leading"></i>
                            <span data-dropdown-label>{{ optional($roles->firstWhere('id', request('role')))->nombre_rol ?? 'Todos los roles' }}</span>
                            <i class="fas fa-chevron-down custom-filter-chevron"></i>
                        </button>
                        <div class="custom-filter-menu hidden" data-dropdown-menu>
                            <button type="button" data-value="">Todos los roles</button>
                            @foreach($roles as $role)
                                <button type="button" data-value="{{ $role->id }}">{{ $role->nombre_rol }}</button>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $statusLabels = ['admin' => 'Administrador', 'active' => 'Activo', 'inactive' => 'Inactivo', 'no_plan' => 'Sin plan'];
                    @endphp
                    <div class="custom-filter-dropdown relative xl:w-60" data-custom-dropdown>
                        <input type="hidden" name="status" id="status" value="{{ request('status') }}">
                        <button type="button" class="custom-filter-trigger" data-dropdown-trigger>
                            <i class="fas fa-users custom-filter-leading"></i>
                            <span data-dropdown-label>{{ $statusLabels[request('status')] ?? 'Todos los usuarios' }}</span>
                            <i class="fas fa-chevron-down custom-filter-chevron"></i>
                        </button>
                        <div class="custom-filter-menu hidden" data-dropdown-menu>
                            <button type="button" data-value="">Todos los usuarios</button>
                            <button type="button" data-value="admin">Administrador</button>
                            <button type="button" data-value="active">Activo</button>
                            <button type="button" data-value="inactive">Inactivo</button>
                        </div>
                    </div>

                    <div class="custom-filter-dropdown relative xl:w-60" data-custom-dropdown>
                        <input type="hidden" name="plan" id="plan" value="{{ request('plan') }}">
                        <button type="button" class="custom-filter-trigger" data-dropdown-trigger>
                            <i class="fas fa-layer-group custom-filter-leading"></i>
                            <span data-dropdown-label>{{ optional($planes->firstWhere('id', request('plan')))->nombre ?? 'Todos los planes' }}</span>
                            <i class="fas fa-chevron-down custom-filter-chevron"></i>
                        </button>
                        <div class="custom-filter-menu hidden" data-dropdown-menu>
                            <button type="button" data-value="">Todos los planes</button>
                            @foreach($planes as $plan)
                                <button type="button" data-value="{{ $plan->id }}">{{ $plan->nombre }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="date-filter-control" data-date-picker>
                        <label for="date_from_trigger">Registrado desde</label>
                        <input id="date_from" name="date_from" type="hidden" value="{{ request('date_from') }}">
                        <button id="date_from_trigger" type="button" class="custom-date-trigger" data-date-trigger>
                            <i class="fas fa-calendar-day"></i><span data-date-label>Seleccionar fecha</span><i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="custom-calendar hidden" data-calendar-panel>
                            <div class="custom-calendar-header"><button type="button" data-calendar-prev aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button><strong data-calendar-title></strong><button type="button" data-calendar-next aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button></div>
                            <div class="custom-calendar-weekdays"><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span></div>
                            <div class="custom-calendar-days" data-calendar-days></div>
                            <button type="button" class="custom-calendar-clear" data-calendar-clear><i class="fas fa-eraser"></i>Quitar fecha</button>
                        </div>
                    </div>

                    <div class="date-filter-control" data-date-picker>
                        <label for="date_to_trigger">Registrado hasta</label>
                        <input id="date_to" name="date_to" type="hidden" value="{{ request('date_to') }}">
                        <button id="date_to_trigger" type="button" class="custom-date-trigger" data-date-trigger>
                            <i class="fas fa-calendar-check"></i><span data-date-label>Seleccionar fecha</span><i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="custom-calendar hidden" data-calendar-panel>
                            <div class="custom-calendar-header"><button type="button" data-calendar-prev aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button><strong data-calendar-title></strong><button type="button" data-calendar-next aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button></div>
                            <div class="custom-calendar-weekdays"><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span></div>
                            <div class="custom-calendar-days" data-calendar-days></div>
                            <button type="button" class="custom-calendar-clear" data-calendar-clear><i class="fas fa-eraser"></i>Quitar fecha</button>
                        </div>
                    </div>

                    <div class="custom-filter-dropdown relative" data-custom-dropdown>
                        <input type="hidden" name="order" id="order" value="{{ request('order', 'newest') }}">
                        <button type="button" class="custom-filter-trigger" data-dropdown-trigger>
                            <i class="fas fa-arrow-down-wide-short custom-filter-leading"></i>
                            <span data-dropdown-label>{{ request('order') === 'oldest' ? 'Más antiguos primero' : 'Más recientes primero' }}</span>
                            <i class="fas fa-chevron-down custom-filter-chevron"></i>
                        </button>
                        <div class="custom-filter-menu hidden" data-dropdown-menu>
                            <button type="button" data-value="newest">Más recientes primero</button>
                            <button type="button" data-value="oldest">Más antiguos primero</button>
                        </div>
                    </div>

                    <label class="without-plan-filter xl:w-64 {{ request()->boolean('without_any_plan') ? 'is-active' : '' }}">
                        <input type="checkbox" name="without_any_plan" value="1" class="sr-only" {{ request()->boolean('without_any_plan') ? 'checked' : '' }}>
                        <i class="fas fa-user-slash"></i>
                        <span><strong>Sin ningún plan</strong><small>Usuarios sin suscripción</small></span>
                        <i class="fas fa-check without-plan-check"></i>
                    </label>

                    @if(request()->hasAny(['search', 'role', 'status', 'plan', 'without_any_plan', 'date_from', 'date_to', 'order']))
                        <a href="{{ route('administrador.usuarios.index') }}" class="clear-user-filters"><i class="fas fa-rotate-left"></i>Limpiar filtros</a>
                    @endif
                </form>
            </div>

            @if(session('drive_success'))
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl px-5 py-4" style="background: #ecf8ee; border: 1px solid #bfe3c5; color: #276738;">
                    <span><i class="fas fa-circle-check mr-2"></i>{{ session('drive_success.message') }}</span>
                    <a href="{{ session('drive_success.url') }}" target="_blank" rel="noopener noreferrer" class="font-bold underline">Abrir en Google Sheets</a>
                </div>
            @endif
            @if(session('drive_error'))
                <div class="mb-6 rounded-xl px-5 py-4" style="background: #fff0f0; border: 1px solid #f3c4c4; color: #a72d2d;">
                    <i class="fas fa-circle-exclamation mr-2"></i>{{ session('drive_error') }}
                </div>
            @endif

            @php
                $activeReportFilters = array_filter(request()->only(['search', 'role', 'status', 'plan', 'without_any_plan', 'date_from', 'date_to', 'order']), fn ($value) => $value !== null && $value !== '');
                $selectedPlan = request('plan');
                $selectedRole = request('role');
            @endphp

            <section class="users-reports-section mb-8">
                <div class="users-reports-heading">
                    <span class="users-reports-eyebrow"><i class="fas fa-file-export"></i> Reportes</span>
                </div>

                <div class="users-reports-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <article class="report-card" style="--report-accent: #ff2457; --report-soft: #fff0f4;">
                        <div class="report-card-body flex gap-4">
                            <div class="report-card-icon" style="background: #ff2457;"><i class="fas fa-filter"></i></div>
                            <div><h3 class="font-bold text-gray-900">Listado filtrado</h3><p class="text-sm leading-6 text-gray-500 mt-1">Exporta exactamente los resultados de los filtros activos.</p></div>
                        </div>
                        <div class="report-card-actions grid grid-cols-3 gap-2">
                            <a href="{{ route('administrador.usuarios.reportes.excel', ['report' => 'filtered'] + $activeReportFilters) }}" class="flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white" style="background: #00a36c;"><i class="fas fa-file-excel"></i>Excel</a>
                            <a href="{{ route('administrador.usuarios.reportes.pdf', ['report' => 'filtered'] + $activeReportFilters) }}" class="report-pdf-button flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white"><i class="fas fa-file-pdf"></i>PDF</a>
                            <button type="button" class="drive-report-btn w-full flex items-center justify-center gap-2 rounded-xl py-3 font-bold" data-report="filtered" data-search="{{ request('search') }}" data-role="{{ request('role') }}" data-status="{{ request('status') }}" data-plan="{{ request('plan') }}" data-without_any_plan="{{ request('without_any_plan') }}" data-date_from="{{ request('date_from') }}" data-date_to="{{ request('date_to') }}" data-order="{{ request('order') }}" style="border: 1px solid #bccaff; color: #4f46e5;"><i class="fab fa-google-drive"></i>Drive</button>
                        </div>
                    </article>

                    <article class="report-card" style="--report-accent: #6259f7; --report-soft: #f0efff;">
                        <div class="report-card-body flex gap-4">
                            <div class="report-card-icon" style="background: #6259f7;"><i class="fas fa-users"></i></div>
                            <div><h3 class="font-bold text-gray-900">Listado general</h3><p class="text-sm leading-6 text-gray-500 mt-1">Incluye todos los usuarios registrados, sin aplicar filtros.</p></div>
                        </div>
                        <div class="report-card-actions grid grid-cols-3 gap-2">
                            <a href="{{ route('administrador.usuarios.reportes.excel', ['report' => 'general']) }}" class="flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white" style="background: #00a36c;"><i class="fas fa-file-excel"></i>Excel</a>
                            <a href="{{ route('administrador.usuarios.reportes.pdf', ['report' => 'general']) }}" class="report-pdf-button flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white"><i class="fas fa-file-pdf"></i>PDF</a>
                            <button type="button" class="drive-report-btn w-full flex items-center justify-center gap-2 rounded-xl py-3 font-bold" data-report="general" style="border: 1px solid #bccaff; color: #4f46e5;"><i class="fab fa-google-drive"></i>Drive</button>
                        </div>
                    </article>

                    <article class="report-card" style="--report-accent: #ff9400; --report-soft: #fff7e8;">
                        <div class="report-card-body flex gap-4">
                            <div class="report-card-icon" style="background: #ff9400;"><i class="fas fa-user-minus"></i></div>
                            <div><h3 class="font-bold text-gray-900">No inscritos al plan</h3><p class="text-sm leading-6 text-gray-500 mt-1">Selecciona un plan en los filtros para generar este reporte.</p></div>
                        </div>
                        <div class="report-card-actions grid grid-cols-3 gap-2">
                            @if($selectedPlan)
                                <a href="{{ route('administrador.usuarios.reportes.excel', ['report' => 'without_plan', 'plan' => $selectedPlan, 'role' => $selectedRole]) }}" class="flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white" style="background: #00a36c;"><i class="fas fa-file-excel"></i>Excel</a>
                                <a href="{{ route('administrador.usuarios.reportes.pdf', ['report' => 'without_plan', 'plan' => $selectedPlan, 'role' => $selectedRole]) }}" class="report-pdf-button flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white"><i class="fas fa-file-pdf"></i>PDF</a>
                            @else
                                <button disabled class="rounded-xl py-3 font-bold text-white opacity-50" style="background: #65bea1;"><i class="fas fa-file-excel mr-2"></i>Excel</button>
                                <button disabled class="report-pdf-button rounded-xl py-3 font-bold text-white opacity-40"><i class="fas fa-file-pdf mr-2"></i>PDF</button>
                            @endif
                            <button type="button" @disabled(!$selectedPlan) class="drive-report-btn w-full flex items-center justify-center gap-2 rounded-xl py-3 font-bold {{ !$selectedPlan ? 'opacity-40 cursor-not-allowed' : '' }}" data-report="without_plan" data-plan="{{ $selectedPlan }}" data-role="{{ $selectedRole }}" style="border: 1px solid #bccaff; color: #4f46e5;"><i class="fab fa-google-drive"></i>Drive</button>
                        </div>
                    </article>

                    <article class="report-card" style="--report-accent: #00b879; --report-soft: #eafaf4;">
                        <div class="report-card-body flex gap-4">
                            <div class="report-card-icon" style="background: #00b879;"><i class="fas fa-user-slash"></i></div>
                            <div><h3 class="font-bold text-gray-900">Sin ningún plan</h3><p class="text-sm leading-6 text-gray-500 mt-1">Lista todos los usuarios que no tienen una suscripción.</p></div>
                        </div>
                        <div class="report-card-actions grid grid-cols-3 gap-2">
                            <a href="{{ route('administrador.usuarios.reportes.excel', ['report' => 'without_any_plan']) }}" class="flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white" style="background: #00a36c;"><i class="fas fa-file-excel"></i>Excel</a>
                            <a href="{{ route('administrador.usuarios.reportes.pdf', ['report' => 'without_any_plan']) }}" class="report-pdf-button flex items-center justify-center gap-2 rounded-xl py-3 font-bold text-white"><i class="fas fa-file-pdf"></i>PDF</a>
                            <button type="button" class="drive-report-btn w-full flex items-center justify-center gap-2 rounded-xl py-3 font-bold" data-report="without_any_plan" style="border: 1px solid #bccaff; color: #4f46e5;"><i class="fab fa-google-drive"></i>Drive</button>
                        </div>
                    </article>
                </div>
            </section>

            <style>
                .users-page-content { position: relative; isolation: isolate; margin-right: 24px; margin-left: 24px; }
                .users-filter-panel { position: relative; z-index: 300; overflow: visible; }
                .users-filter-grid { position: relative; overflow: visible; display: grid; grid-template-columns: repeat(12,minmax(0,1fr)); gap: 12px; align-items: end; }
                .users-filter-grid > * { width: auto !important; }.users-search-filter { grid-column: span 3; }.users-filter-grid > .custom-filter-dropdown { grid-column: span 3; }.users-filter-grid > .without-plan-filter { grid-column: span 3; }.users-filter-grid > .date-filter-control { grid-column: span 3; }.users-filter-grid > .clear-user-filters { grid-column: 1 / -1; }
                .custom-filter-dropdown { z-index: 40; }
                .custom-filter-dropdown.is-open { z-index: 80; }
                .custom-filter-trigger { position: relative; width: 100%; height: 50px; display: flex; align-items: center; gap: 10px; padding: 0 40px 0 16px; border: 1px solid #dedede; border-radius: 14px; background: #fff; color: #25272b; text-align: left; box-shadow: 0 2px 5px rgba(0,0,0,.1); transition: border-color .18s ease, box-shadow .18s ease; }
                .custom-filter-trigger:hover,.custom-filter-dropdown.is-open .custom-filter-trigger { border-color: #7da533; box-shadow: 0 0 0 3px rgba(125,165,51,.12),0 3px 8px rgba(0,0,0,.08); }
                .custom-filter-trigger:disabled { cursor: wait; opacity: .65; background: #f9fafb; }
                .custom-filter-trigger span { min-width: 0; overflow: hidden; flex: 1; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
                .custom-filter-leading { width: 18px; color: #117e8c; text-align: center; font-size: .9rem; }
                .custom-filter-chevron { position: absolute; right: 16px; color: #9ca3af; font-size: .68rem; transition: transform .18s ease; }
                .custom-filter-dropdown.is-open .custom-filter-chevron { transform: rotate(180deg); }
                .custom-filter-menu { position: absolute; z-index: 1000; top: calc(100% + 8px); right: 0; left: 0; overflow-y: auto; max-height: 250px; padding: 7px; border: 1px solid #e2e8d8; border-radius: 13px; background: #fff; box-shadow: 0 16px 35px rgba(31,41,55,.16); }
                .custom-filter-menu button { width: 100%; display: flex; align-items: center; min-height: 40px; padding: 8px 11px; border: 0; border-radius: 9px; background: transparent; color: #4b5563; text-align: left; font-size: .84rem; font-weight: 600; transition: .15s ease; }
                .custom-filter-menu button:hover,.custom-filter-menu button.is-selected { background: #edf4e4; color: #587923; }
                .without-plan-filter { height: 50px; min-height: 50px; box-sizing: border-box; display: flex; align-items: center; gap: 10px; padding: 7px 12px; border: 1px solid #dedede; border-radius: 14px; background: #fff; color: #4b5563; box-shadow: 0 2px 5px rgba(0,0,0,.1); cursor: pointer; transition: .18s ease; }
                .without-plan-filter > i:first-of-type { width: 30px; height: 30px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 9px; background: #edf4e4; color: #7da533; }
                .without-plan-filter span { min-width: 0; flex: 1; }.without-plan-filter strong,.without-plan-filter small { display: block; }.without-plan-filter strong { color: #30343a; font-size: .78rem; }.without-plan-filter small { margin-top: 2px; color: #9ca3af; font-size: .61rem; }
                .without-plan-check { width: 20px; height: 20px; display: grid; place-items: center; border: 1px solid #d1d5db; border-radius: 6px; color: transparent; font-size: .65rem; }
                .without-plan-filter:hover,.without-plan-filter.is-active { border-color: #7da533; background: #f7faef; box-shadow: 0 0 0 3px rgba(125,165,51,.12); }.without-plan-filter.is-active .without-plan-check { border-color: #7da533; background: #7da533; color: #fff; }
                .date-filter-control { position: relative; z-index: 40; }.date-filter-control.is-open { z-index: 90; }.date-filter-control > label { display: block; margin: 0 0 6px 2px; color: #4b5563; font-size: .7rem; font-weight: 800; }
                .custom-date-trigger { width: 100%; height: 50px; display: flex; align-items: center; gap: 11px; padding: 0 15px; border: 1px solid #dedede; border-radius: 14px; background: #fff; color: #374151; box-shadow: 0 2px 5px rgba(0,0,0,.1); text-align: left; transition: .18s ease; }.custom-date-trigger:hover,.date-filter-control.is-open .custom-date-trigger { border-color: #7da533; box-shadow: 0 0 0 3px rgba(125,165,51,.12); }.custom-date-trigger > i:first-child { color: #117e8c; }.custom-date-trigger span { flex: 1; font-size: .82rem; font-weight: 700; }.custom-date-trigger > i:last-child { color: #9ca3af; font-size: .68rem; transition: transform .18s ease; }.date-filter-control.is-open .custom-date-trigger > i:last-child { transform: rotate(180deg); }
                .custom-calendar { position: absolute; z-index: 1000; top: calc(100% + 8px); left: 0; width: min(310px,calc(100vw - 28px)); padding: 14px; border: 1px solid #dce7cc; border-radius: 16px; background: #fff; box-shadow: 0 18px 40px rgba(31,41,55,.18); }.custom-calendar-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }.custom-calendar-header strong { color: #34451f; font-size: .86rem; text-transform: capitalize; }.custom-calendar-header button { width: 34px; height: 34px; display: grid; place-items: center; border: 0; border-radius: 9px; background: #edf4e4; color: #638524; }.custom-calendar-weekdays,.custom-calendar-days { display: grid; grid-template-columns: repeat(7,1fr); gap: 4px; }.custom-calendar-weekdays { margin-bottom: 5px; }.custom-calendar-weekdays span { color: #9aa18f; text-align: center; font-size: .61rem; font-weight: 900; }.custom-calendar-days button,.custom-calendar-days span { aspect-ratio: 1; display: grid; place-items: center; border: 0; border-radius: 9px; background: transparent; color: #48513e; font-size: .73rem; font-weight: 700; }.custom-calendar-days button:hover { background: #edf4e4; color: #587923; }.custom-calendar-days button.is-today { box-shadow: inset 0 0 0 1px #7da533; }.custom-calendar-days button.is-selected { background: #7da533; color: #fff; box-shadow: 0 5px 12px rgba(125,165,51,.25); }.custom-calendar-days button:disabled { cursor: not-allowed; color: #d1d5db; background: transparent; box-shadow: none; }.custom-calendar-clear { width: 100%; margin-top: 10px; padding: 9px; border: 0; border-radius: 9px; background: #f4f6f1; color: #758069; font-size: .7rem; font-weight: 800; }.custom-calendar-clear i { margin-right: 6px; }
                .clear-user-filters { min-height: 50px; display: flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid #dfe7d2; border-radius: 14px; background: #f7faef; color: #638524; font-size: .78rem; font-weight: 800; }
                #users-filter-form.is-loading { pointer-events: none; opacity: .68; }
                .users-filter-panel { padding: 20px; border: 1px solid #e1e3de; border-radius: 16px; background: #f8f8f6; box-shadow: 0 9px 22px rgba(55,60,52,.06); }
                #users-filter-form .users-search-filter input { border-color: #d9dcd6 !important; color: #3f443d; box-shadow: 0 2px 5px rgba(55,60,52,.08) !important; }
                #users-filter-form .users-search-filter input:focus { border-color: #8a9186 !important; box-shadow: 0 0 0 3px rgba(98,104,95,.12),0 3px 8px rgba(55,60,52,.06) !important; }
                #users-filter-form .users-search-filter svg { color: #737a70; }
                #users-filter-form .custom-filter-trigger { border-color: #d9dcd6; color: #3f443d; box-shadow: 0 2px 5px rgba(55,60,52,.08); }
                #users-filter-form .custom-filter-trigger:hover,#users-filter-form .custom-filter-dropdown.is-open .custom-filter-trigger { border-color: #8a9186; box-shadow: 0 0 0 3px rgba(98,104,95,.12),0 3px 8px rgba(55,60,52,.06); }
                #users-filter-form .custom-filter-leading { color: #737a70; }
                #users-filter-form .custom-filter-chevron { color: #8a9186; }
                #users-filter-form .custom-filter-menu { border-color: #d9dcd6; box-shadow: 0 18px 40px rgba(55,60,52,.16); }
                #users-filter-form .custom-filter-menu button { color: #565d53; }
                #users-filter-form .custom-filter-menu button:hover,#users-filter-form .custom-filter-menu button.is-selected { background: #eff0ed; color: #3f443d; }
                #users-filter-form .without-plan-filter { border-color: #d9dcd6; color: #565d53; box-shadow: 0 2px 5px rgba(55,60,52,.08); }
                #users-filter-form .without-plan-filter > i:first-of-type { background: #eff0ed; color: #62685f; }
                #users-filter-form .without-plan-filter:hover,#users-filter-form .without-plan-filter.is-active { border-color: #8a9186; background: #f1f2ef; box-shadow: 0 0 0 3px rgba(98,104,95,.12); }
                #users-filter-form .without-plan-filter.is-active .without-plan-check { border-color: #62685f; background: #62685f; color: #fff; }
                #users-filter-form .date-filter-control > label { color: #565d53; }
                #users-filter-form .custom-date-trigger { border-color: #d9dcd6; color: #3f443d; box-shadow: 0 2px 5px rgba(55,60,52,.08); }
                #users-filter-form .custom-date-trigger:hover,#users-filter-form .date-filter-control.is-open .custom-date-trigger { border-color: #8a9186; box-shadow: 0 0 0 3px rgba(98,104,95,.12); }
                #users-filter-form .custom-date-trigger > i:first-child { color: #737a70; }
                #users-filter-form .custom-date-trigger > i:last-child { color: #8a9186; }
                #users-filter-form .custom-calendar { border-color: #d9dcd6; box-shadow: 0 18px 40px rgba(55,60,52,.16); }
                #users-filter-form .custom-calendar-header strong { color: #3f443d; }
                #users-filter-form .custom-calendar-header button { background: #eff0ed; color: #62685f; }
                #users-filter-form .custom-calendar-days button:hover { background: #eff0ed; color: #3f443d; }
                #users-filter-form .custom-calendar-days button.is-today { box-shadow: inset 0 0 0 1px #8a9186; }
                #users-filter-form .custom-calendar-days button.is-selected { background: #62685f; color: #fff; box-shadow: 0 5px 12px rgba(55,60,52,.2); }
                #users-filter-form .custom-calendar-clear { background: #eff0ed; color: #62685f; }
                #users-filter-form .clear-user-filters { border-color: #d7dad4; background: #fff; color: #62685f; }
                #users-filter-form .clear-user-filters:hover { background: #eff0ed; color: #3f443d; }
                .users-table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin: 0 0 12px; padding: 12px 15px; border: 1px solid #e2ead5; border-radius: 14px; background: #f9fbf5; }.users-table-toolbar > div { display: flex; align-items: center; gap: 11px; }.users-table-toolbar > div > i { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 10px; background: #edf4e4; color: #7da533; }.users-table-toolbar strong,.users-table-toolbar small { display: block; }.users-table-toolbar strong { color: #31382b; font-size: .8rem; }.users-table-toolbar small { margin-top: 2px; color: #8a9380; font-size: .62rem; }
                #per-page-form { display: flex; align-items: center; gap: 8px; color: #66705c; font-size: .72rem; font-weight: 700; }#per_page { width: 76px; height: 38px; padding: 0 8px; border: 1px solid #ccd9bb; border-radius: 10px; background: #fff; color: #405128; text-align: center; font-weight: 900; outline: 0; }#per_page:focus { border-color: #7da533; box-shadow: 0 0 0 3px rgba(125,165,51,.13); }
                .users-reports-section { position: relative; z-index: 1; padding-top: 5px; }
                .users-reports-heading { margin-bottom: 16px; padding: 0 2px 12px; border-bottom: 1px solid #e1e3de; }
                .users-reports-eyebrow { display: flex; align-items: center; gap: 7px; color: #737a70; font-size: .65rem; font-weight: 900; letter-spacing: .11em; text-transform: uppercase; }
                .users-reports-eyebrow i { color: #62685f; }
                .users-reports-grid { align-items: stretch; }
                .report-card { position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: stretch; min-height: 205px; padding: 20px; border: 1px solid #e1e3de; border-radius: 15px; background: #fff; box-shadow: 0 5px 14px rgba(55,60,52,.055); transition: transform .2s ease,border-color .2s ease,box-shadow .2s ease; }
                .report-card::before { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 4px; background: var(--report-accent); }
                .report-card:hover { transform: translateY(-2px); border-color: #cfd3cc; box-shadow: 0 10px 22px rgba(55,60,52,.09); }
                .report-card-body { min-width: 0; flex: 1; align-items: flex-start; }
                .report-card-body h3 { color: #2f342e; font-size: .94rem; }
                .report-card-body p { max-width: 340px; color: #747b71; font-size: .75rem; line-height: 1.55; }
                .report-card-icon { width: 46px; height: 46px; display: grid; place-items: center; flex: 0 0 46px; border-radius: 12px; color: #fff; font-size: 1rem; box-shadow: 0 6px 14px color-mix(in srgb,var(--report-accent) 22%,transparent); }
                .report-card-actions { width: 100%; margin-top: 12px; padding: 12px 0 0; border-top: 1px solid #e8eae6; border-left: 0; }
                .report-card-actions a,.report-card-actions button { min-height: 42px; border-radius: 10px; font-size: .72rem; transition: transform .18s ease,filter .18s ease,background-color .18s ease; }
                .report-pdf-button { border: 1px solid #dc2626; background: #dc2626; }
                .report-card-actions a:hover,.report-card-actions button:not(:disabled):hover { transform: translateY(-1px); filter: brightness(.96); }
                .report-card-actions i { display: inline-block; min-width: 16px; font-size: .88rem; }
                @media(max-width:1100px) { .users-filter-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }.users-filter-grid > *,.users-search-filter,.users-filter-grid > .custom-filter-dropdown,.users-filter-grid > .without-plan-filter,.users-filter-grid > .date-filter-control,.users-filter-grid > .clear-user-filters { grid-column: span 1; }.users-search-filter { grid-column: 1 / -1; } }
                @media(max-width:640px) { .users-page-content { margin-right: 12px; margin-left: 12px; }.users-filter-panel { padding: 14px; }.users-filter-grid { grid-template-columns: 1fr; }.users-filter-grid > *,.users-search-filter { grid-column: 1; }.users-table-toolbar { align-items: flex-start; flex-direction: column; } }
            </style>

            <div id="drive-folder-modal" class="fixed inset-0 hidden items-center justify-center p-4" style="z-index: 12000; background: rgba(17,24,39,.58);">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6" style="box-shadow: 0 24px 60px rgba(0,0,0,.25);">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Guardar reporte en Drive</h3>
                            <p class="text-sm text-gray-500 mt-1">Consulta dónde está guardado el reporte o cambia su ubicación.</p>
                        </div>
                        <button type="button" id="close-drive-modal" class="h-9 w-9 rounded-full text-gray-500 hover:bg-gray-100"><i class="fas fa-times"></i></button>
                    </div>

                    <form id="drive-folder-form" method="POST" data-action-template="{{ route('administrador.usuarios.reportes.drive', ['report' => '__REPORT__']) }}">
                        @csrf
                        <div id="drive-report-filters"></div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ubicación actual</label>
                            <div class="flex items-center justify-between gap-3 rounded-xl px-4 py-3" style="border:1px solid #dce7cc;background:#f7faF2;">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" style="background:#e4efd4;color:#638524;"><i class="fas fa-folder-open"></i></span>
                                    <span class="min-w-0"><strong id="drive-current-folder" class="block truncate text-sm text-gray-800">Consultando...</strong><small id="drive-current-detail" class="block text-xs text-gray-500">Buscando el reporte en Drive</small></span>
                                </div>
                                <button id="change-drive-location" type="button" class="hidden shrink-0 rounded-lg px-3 py-2 text-xs font-bold" style="background:#e6f4f5;color:#0d6975;"><i class="fas fa-location-dot mr-1"></i>Cambiar ubicación</button>
                            </div>
                        </div>

                        <div id="drive-location-editor" class="hidden rounded-xl p-4" style="border:1px solid #dce7cc;background:#fbfcf9;">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Carpeta de destino</label>
                            <div id="drive-folder-dropdown" class="custom-filter-dropdown relative">
                                <input id="drive-folder-select" type="hidden" name="folder_id" value="">
                                <button id="drive-folder-trigger" type="button" class="custom-filter-trigger" disabled>
                                    <i class="fas fa-folder custom-filter-leading"></i>
                                    <span id="drive-folder-label">Consultando carpetas...</span>
                                    <i class="fas fa-chevron-down custom-filter-chevron"></i>
                                </button>
                                <div id="drive-folder-menu" class="custom-filter-menu hidden"></div>
                            </div>

                            <div class="flex items-center gap-3 my-4"><span class="h-px flex-1 bg-gray-200"></span><span class="text-xs font-bold uppercase text-gray-400">o crea una</span><span class="h-px flex-1 bg-gray-200"></span></div>

                            <label for="drive-new-folder" class="block text-sm font-bold text-gray-700 mb-2">Nueva subcarpeta</label>
                            <div class="relative">
                                <i class="fas fa-folder-plus absolute left-4" style="top: 16px; color: #7da533;"></i>
                                <input id="drive-new-folder" name="new_folder" type="text" maxlength="80" class="w-full border pl-11 pr-4" style="height: 48px; border-radius: 12px; border-color: #d7dce2;" placeholder="Ej.: Reportes agosto">
                            </div>
                        </div>
                        <p id="drive-folder-status" class="mt-3 text-sm text-gray-500"></p>

                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <button type="button" id="cancel-drive-modal" class="rounded-xl py-3 font-bold text-gray-600" style="background: #f3f4f6;">Cancelar</button>
                            <button type="submit" id="save-drive-report" class="rounded-xl py-3 font-bold text-white" style="background: #7da533;"><i class="fab fa-google-drive mr-2"></i>Guardar en Drive</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table Mejorada -->
            <div class="users-table-toolbar">
                <div><i class="fas fa-users"></i><span><strong>Usuarios registrados</strong><small>{{ $users->total() }} resultado(s) con los filtros actuales</small></span></div>
                <form id="per-page-form" method="GET" action="{{ route('administrador.usuarios.index') }}">
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <label for="per_page">Mostrar</label>
                    <input id="per_page" name="per_page" type="number" min="5" max="200" step="1" list="per-page-options" value="{{ $users->perPage() }}" aria-label="Usuarios por página">
                    <datalist id="per-page-options"><option value="5"><option value="10"><option value="25"><option value="50"><option value="100"></datalist>
                    <span>por página</span>
                </form>
            </div>
            <div class="table-container users-green-table animate-fade-up" style="animation-delay: 0.3s; border: 1px solid #d8e3c7; border-radius: 16px; background: #ffffff; box-shadow: 0 9px 24px rgba(91, 121, 38, 0.12);">
                <div class="bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full users-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="width: 72px; background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">N.º</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">Usuario</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">Email</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">Celular</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">Rol</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff; border-right: 1px solid rgba(255,255,255,.3);">Estado</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="background: #7da533; color: #ffffff;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr class="users-table-row transition-colors duration-150 cursor-pointer" style="background: {{ $loop->even ? '#f1f7e8' : '#ffffff' }};" onclick="window.location='{{ route('administrador.usuarios.view', $user->id) }}'">
                                    <td class="px-4 py-4 whitespace-nowrap text-center font-bold" style="color: #638524; border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">{{ $user->registration_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">
                                        <div class="flex items-center">
                                            <div class="users-avatar w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold mr-3 shadow-md" style="background: linear-gradient(135deg, #7da533, #117e8c);">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">
                                        <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">
                                        <span class="text-sm text-gray-600"><i class="fas fa-phone mr-2 text-gray-400"></i>{{ $user->phone ?: 'No registrado' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->roles as $role)
                                                <span class="users-role-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" style="color: #638524; background: #edf4e4; border-color: #cedfb4;">
                                                    {{ $role->nombre_rol }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #d8e3c7; border-bottom: 1px solid #dfe8d1;">
                                        @php
                                            $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty();
                                            $hasActiveSubscription = $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty();
                                            $hasInactiveSubscription = $user->suscripciones->where('estado', '!=', 'activa')->isNotEmpty();
                                        @endphp
                                        
                                        @if($isAdmin)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200 inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1.002 1.002 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z" clip-rule="evenodd"></path>
                                                </svg>
                                                Administrador
                                            </span>
                                        @elseif($hasActiveSubscription)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200 inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Activo
                                            </span>
                                        @elseif($hasInactiveSubscription)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-200 inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Inactivo
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200 inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Sin Plan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="border-bottom: 1px solid #dfe8d1;" onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('administrador.usuarios.view', $user->id) }}" class="user-action user-action-view inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-200" style="color: #638524; background: #edf4e4;">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </a>
                                            <a href="{{ route('administrador.usuarios.edit', $user->id) }}" class="user-action user-action-edit inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors duration-200" style="color: #0d6975; background: #e6f4f5;">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Editar
                                            </a>
                                            <form action="{{ route('administrador.usuarios.destroy', $user->id) }}" method="POST" class="inline" onclick="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-200">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Pagination -->
            <div class="mt-8 animate-fade-up" style="animation-delay: 0.4s">
                {{ $users->links('componentes.paginacion-es') }}
            </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('add-user-btn')?.addEventListener('click', function() {
            alert('La funcionalidad de agregar usuario estará disponible pronto.');
        });

        // Animación de carga suave para los elementos
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            // Observar elementos con animación
            document.querySelectorAll('.animate-fade-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                observer.observe(el);
            });
        });

        // Filtros y búsqueda dinámica
        const usersFilterForm = document.getElementById('users-filter-form');
        const submitUserFilters = () => {
            usersFilterForm.classList.add('is-loading');
            usersFilterForm.requestSubmit();
        };
        let dynamicSearchTimeout;
        document.getElementById('search').addEventListener('input', () => {
            clearTimeout(dynamicSearchTimeout);
            dynamicSearchTimeout = setTimeout(submitUserFilters, 450);
        });
        document.querySelector('.without-plan-filter input').addEventListener('change', function() {
            this.closest('.without-plan-filter').classList.toggle('is-active', this.checked);
            if (this.checked) {
                document.getElementById('plan').value = '';
                document.getElementById('plan').closest('[data-custom-dropdown]').querySelector('[data-dropdown-label]').textContent = 'Todos los planes';
            }
            submitUserFilters();
        });

        const calendarMonths = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const datePickers = document.querySelectorAll('[data-date-picker]');
        const parseCalendarDate = value => {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
            const [year, month, day] = value.split('-').map(Number);
            return new Date(year, month - 1, day);
        };
        const calendarDateValue = date => [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0'),
        ].join('-');
        const calendarDateLabel = value => {
            const date = parseCalendarDate(value);
            return date ? new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }).format(date) : 'Seleccionar fecha';
        };
        const closeDatePickers = except => datePickers.forEach(picker => {
            if (picker !== except) {
                picker.classList.remove('is-open');
                picker.querySelector('[data-calendar-panel]').classList.add('hidden');
            }
        });

        datePickers.forEach(picker => {
            const input = picker.querySelector('input[type="hidden"]');
            const trigger = picker.querySelector('[data-date-trigger]');
            const label = picker.querySelector('[data-date-label]');
            const panel = picker.querySelector('[data-calendar-panel]');
            const title = picker.querySelector('[data-calendar-title]');
            const daysContainer = picker.querySelector('[data-calendar-days]');
            let visibleMonth = parseCalendarDate(input.value) || new Date();
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
            label.textContent = calendarDateLabel(input.value);

            const renderCalendar = () => {
                const year = visibleMonth.getFullYear();
                const month = visibleMonth.getMonth();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const leadingSpaces = (new Date(year, month, 1).getDay() + 6) % 7;
                const todayValue = calendarDateValue(new Date());
                const fromValue = document.getElementById('date_from').value;
                const toValue = document.getElementById('date_to').value;
                title.textContent = `${calendarMonths[month]} ${year}`;
                daysContainer.innerHTML = '';

                for (let blank = 0; blank < leadingSpaces; blank++) daysContainer.appendChild(document.createElement('span'));
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const value = calendarDateValue(date);
                    const dayButton = document.createElement('button');
                    dayButton.type = 'button';
                    dayButton.textContent = day;
                    dayButton.classList.toggle('is-today', value === todayValue);
                    dayButton.classList.toggle('is-selected', value === input.value);
                    dayButton.disabled = input.id === 'date_from'
                        ? Boolean(toValue && value > toValue)
                        : Boolean(fromValue && value < fromValue);
                    dayButton.addEventListener('click', () => {
                        input.value = value;
                        label.textContent = calendarDateLabel(value);
                        closeDatePickers();
                        submitUserFilters();
                    });
                    daysContainer.appendChild(dayButton);
                }
            };

            trigger.addEventListener('click', event => {
                event.stopPropagation();
                const willOpen = panel.classList.contains('hidden');
                closeDatePickers(picker);
                closeCustomDropdowns();
                picker.classList.toggle('is-open', willOpen);
                panel.classList.toggle('hidden', !willOpen);
                if (willOpen) renderCalendar();
            });
            panel.addEventListener('click', event => event.stopPropagation());
            picker.querySelector('[data-calendar-prev]').addEventListener('click', () => {
                visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
                renderCalendar();
            });
            picker.querySelector('[data-calendar-next]').addEventListener('click', () => {
                visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
                renderCalendar();
            });
            picker.querySelector('[data-calendar-clear]').addEventListener('click', () => {
                input.value = '';
                label.textContent = 'Seleccionar fecha';
                closeDatePickers();
                submitUserFilters();
            });
        });
        document.addEventListener('click', () => closeDatePickers());

        const perPageForm = document.getElementById('per-page-form');
        const perPageInput = document.getElementById('per_page');
        const submitPerPage = () => {
            const requestedAmount = Number.parseInt(perPageInput.value, 10);
            perPageInput.value = Number.isNaN(requestedAmount)
                ? 10
                : Math.min(200, Math.max(5, requestedAmount));
            perPageForm.submit();
        };
        perPageInput.addEventListener('change', submitPerPage);
        perPageInput.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitPerPage();
            }
        });

        document.querySelectorAll('.table-row').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.background = 'linear-gradient(135deg, #F8FAFC, #F1F5F9)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.background = '';
            });
        });

        const customDropdowns = document.querySelectorAll('[data-custom-dropdown]');
        const closeCustomDropdowns = except => {
            customDropdowns.forEach(dropdown => {
                if (dropdown !== except) {
                    dropdown.classList.remove('is-open');
                    dropdown.querySelector('[data-dropdown-menu]').classList.add('hidden');
                }
            });
        };

        customDropdowns.forEach(dropdown => {
            const input = dropdown.querySelector('input[type="hidden"]');
            const trigger = dropdown.querySelector('[data-dropdown-trigger]');
            const label = dropdown.querySelector('[data-dropdown-label]');
            const menu = dropdown.querySelector('[data-dropdown-menu]');
            const options = menu.querySelectorAll('[data-value]');

            options.forEach(option => {
                option.classList.toggle('is-selected', option.dataset.value === input.value);
                option.addEventListener('click', () => {
                    input.value = option.dataset.value;
                    if (input.name === 'plan' && option.dataset.value) {
                        const withoutPlanInput = document.querySelector('.without-plan-filter input');
                        withoutPlanInput.checked = false;
                        withoutPlanInput.closest('.without-plan-filter').classList.remove('is-active');
                    }
                    label.textContent = option.textContent.trim();
                    options.forEach(item => item.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    dropdown.classList.remove('is-open');
                    menu.classList.add('hidden');
                    submitUserFilters();
                });
            });

            trigger.addEventListener('click', event => {
                event.stopPropagation();
                const willOpen = menu.classList.contains('hidden');
                closeDatePickers();
                closeCustomDropdowns(dropdown);
                dropdown.classList.toggle('is-open', willOpen);
                menu.classList.toggle('hidden', !willOpen);
            });
        });

        document.addEventListener('click', () => closeCustomDropdowns());
        document.addEventListener('keydown', event => { if (event.key === 'Escape') closeCustomDropdowns(); });

        const driveModal = document.getElementById('drive-folder-modal');
        const driveForm = document.getElementById('drive-folder-form');
        const driveFolderSelect = document.getElementById('drive-folder-select');
        const driveFolderDropdown = document.getElementById('drive-folder-dropdown');
        const driveFolderTrigger = document.getElementById('drive-folder-trigger');
        const driveFolderLabel = document.getElementById('drive-folder-label');
        const driveFolderMenu = document.getElementById('drive-folder-menu');
        const driveNewFolder = document.getElementById('drive-new-folder');
        const driveLocationEditor = document.getElementById('drive-location-editor');
        const driveCurrentFolder = document.getElementById('drive-current-folder');
        const driveCurrentDetail = document.getElementById('drive-current-detail');
        const changeDriveLocation = document.getElementById('change-drive-location');
        const driveStatus = document.getElementById('drive-folder-status');
        const driveFilters = document.getElementById('drive-report-filters');
        const driveSubmit = document.getElementById('save-drive-report');
        const driveFoldersUrl = @json(route('administrador.usuarios.reportes.drive-folders'));

        function closeDriveModal() {
            driveModal.classList.add('hidden');
            driveModal.classList.remove('flex');
            driveFolderDropdown.classList.remove('is-open');
            driveFolderMenu.classList.add('hidden');
        }

        changeDriveLocation.addEventListener('click', () => {
            driveLocationEditor.classList.remove('hidden');
            changeDriveLocation.classList.add('hidden');
            driveStatus.textContent = 'Selecciona otra carpeta o escribe el nombre de una nueva subcarpeta.';
        });

        function selectDriveFolder(id, name, selectedButton = null) {
            driveFolderSelect.value = id;
            driveFolderLabel.textContent = name;
            driveFolderMenu.querySelectorAll('button').forEach(option => option.classList.remove('is-selected'));
            selectedButton?.classList.add('is-selected');
            driveFolderDropdown.classList.remove('is-open');
            driveFolderMenu.classList.add('hidden');
        }

        function addDriveFolderOption(id, name, selected = false) {
            const option = document.createElement('button');
            option.type = 'button';
            option.dataset.value = id;
            const icon = document.createElement('i');
            icon.className = 'fas fa-folder mr-2';
            icon.style.color = '#7da533';
            const text = document.createElement('span');
            text.textContent = name;
            option.append(icon, text);
            option.addEventListener('click', () => selectDriveFolder(id, name, option));
            option.classList.toggle('is-selected', selected);
            driveFolderMenu.appendChild(option);
        }

        driveFolderTrigger.addEventListener('click', event => {
            event.stopPropagation();
            if (driveFolderTrigger.disabled) return;
            const willOpen = driveFolderMenu.classList.contains('hidden');
            driveFolderDropdown.classList.toggle('is-open', willOpen);
            driveFolderMenu.classList.toggle('hidden', !willOpen);
        });
        document.addEventListener('click', () => {
            driveFolderDropdown.classList.remove('is-open');
            driveFolderMenu.classList.add('hidden');
        });

        function addDriveFilter(name, value) {
            if (!value) return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            driveFilters.appendChild(input);
        }

        document.querySelectorAll('.drive-report-btn:not([disabled])').forEach(button => {
            button.addEventListener('click', async () => {
                driveFilters.innerHTML = '';
                ['search', 'role', 'status', 'plan', 'without_any_plan', 'date_from', 'date_to', 'order'].forEach(key => addDriveFilter(key, button.dataset[key]));
                driveNewFolder.value = '';
                driveFolderSelect.value = '';
                driveFolderLabel.textContent = 'Consultando carpetas...';
                driveFolderMenu.innerHTML = '';
                driveFolderTrigger.disabled = true;
                driveSubmit.disabled = true;
                driveLocationEditor.classList.add('hidden');
                changeDriveLocation.classList.add('hidden');
                driveCurrentFolder.textContent = 'Consultando...';
                driveCurrentDetail.textContent = 'Buscando el reporte en Drive';
                driveStatus.style.color = '';
                driveStatus.textContent = 'Consultando las carpetas dentro de PRODOVI...';
                driveForm.action = driveForm.dataset.actionTemplate.replace('__REPORT__', button.dataset.report);
                driveModal.classList.remove('hidden');
                driveModal.classList.add('flex');

                try {
                    const foldersRequestUrl = new URL(driveFoldersUrl, window.location.origin);
                    foldersRequestUrl.searchParams.set('report', button.dataset.report);
                    const response = await fetch(foldersRequestUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'No se pudieron consultar las carpetas.');

                    driveFolderMenu.innerHTML = '';
                    const rootLabel = `${data.root.name} (carpeta principal)`;
                    addDriveFolderOption(data.root.id, rootLabel);
                    data.folders.forEach(folder => addDriveFolderOption(folder.id, folder.name));
                    const currentFolder = data.current_folder;
                    const selectedFolderId = currentFolder?.id || data.root.id;
                    const selectedFolderName = currentFolder?.name || rootLabel;
                    const selectedFolderButton = [...driveFolderMenu.querySelectorAll('button')]
                        .find(option => option.dataset.value === selectedFolderId);
                    selectDriveFolder(selectedFolderId, selectedFolderName, selectedFolderButton);
                    driveFolderTrigger.disabled = false;
                    driveSubmit.disabled = false;

                    if (currentFolder) {
                        driveCurrentFolder.textContent = currentFolder.name;
                        driveCurrentDetail.textContent = 'El reporte ya está guardado en esta carpeta';
                        changeDriveLocation.classList.remove('hidden');
                        driveStatus.textContent = 'Al guardar, se actualizará el mismo reporte y conservará su enlace.';
                    } else {
                        driveCurrentFolder.textContent = 'Reporte aún no creado';
                        driveCurrentDetail.textContent = 'Elige dónde deseas guardarlo por primera vez';
                        driveLocationEditor.classList.remove('hidden');
                        driveStatus.textContent = data.folders.length
                            ? `${data.folders.length} carpeta(s) disponible(s).`
                            : 'No hay subcarpetas. Puedes guardar en PRODOVI o crear una nueva.';
                    }
                } catch (error) {
                    driveCurrentFolder.textContent = 'No se pudo consultar la ubicación';
                    driveCurrentDetail.textContent = 'Inténtalo nuevamente';
                    driveStatus.textContent = error.message;
                    driveStatus.style.color = '#b91c1c';
                }
            });
        });

        document.getElementById('close-drive-modal').addEventListener('click', closeDriveModal);
        document.getElementById('cancel-drive-modal').addEventListener('click', closeDriveModal);
        driveModal.addEventListener('click', event => { if (event.target === driveModal) closeDriveModal(); });
        driveForm.addEventListener('submit', () => {
            driveSubmit.disabled = true;
            driveSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
        });
    </script>
    @endpush
@endsection
