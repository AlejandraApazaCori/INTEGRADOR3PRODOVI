@extends('layouts.app')

@section('title', 'Gestión de Planes')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<div class="plans-page">
    <header class="plans-control-hero">
        <div class="plans-control-copy">
            <span>Administración comercial</span>
            <h1>Gestión de planes</h1>
        </div>
        <div class="plans-quick-actions">
            <a href="{{ route('administrador.planes.vista-usuario') }}" target="_blank" rel="noopener"><i class="fas fa-eye"></i>Vista de usuario</a>
            <a class="primary" href="{{ route('administrador.planes.create') }}"><i class="fas fa-plus-circle"></i>Crear nuevo plan</a>
        </div>
    </header>

    @if(session('success'))
        <div class="plans-alert plans-alert-success"><span><i class="fas fa-circle-check"></i>{{ session('success') }}</span><button type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>
    @endif
    @if(session('error'))
        <div class="plans-alert plans-alert-error"><span><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</span><button type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>
    @endif

    <section class="plans-kpi-grid" aria-label="Resumen de planes">
        <article class="plans-kpi-card plans-kpi-total"><div><span>Total planes</span><strong>{{ $planSummary['total'] }}</strong><small>Registrados en el sistema</small></div><i class="fas fa-layer-group"></i></article>
        <article class="plans-kpi-card plans-kpi-active"><div><span>Planes activos</span><strong>{{ $planSummary['active'] }}</strong><small>Disponibles para contratación</small></div><i class="fas fa-circle-check"></i></article>
        <article class="plans-kpi-card plans-kpi-inactive"><div><span>Planes inactivos</span><strong>{{ $planSummary['inactive'] }}</strong><small>No visibles para clientes</small></div><i class="fas fa-circle-pause"></i></article>
        <article class="plans-kpi-card plans-kpi-subscriptions"><div><span>Suscripciones activas</span><strong>{{ $planSummary['subscriptions'] }}</strong><small>Vigentes y asociadas a los planes</small></div><i class="fas fa-users"></i></article>
    </section>

    <main class="plans-page-content">
        <section class="plans-filter-panel">
            <form id="plans-filter-form" method="GET" action="{{ route('administrador.planes.index') }}" class="plans-filter-grid">
                <div class="plans-search-filter">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, subtítulo o descripción...">
                </div>

                @php
                    $statusLabels = ['active' => 'Planes activos', 'inactive' => 'Planes inactivos'];
                    $currencyLabels = ['BS' => 'Bolivianos (BS)', 'USD' => 'Dólares (USD)'];
                    $periodLabels = ['mes' => 'Mensual', 'trimestre' => 'Trimestral', 'semestre' => 'Semestral', 'año' => 'Anual'];
                    $orderLabels = ['position' => 'Orden del catálogo', 'price_asc' => 'Menor precio', 'price_desc' => 'Mayor precio', 'name' => 'Nombre A-Z', 'newest' => 'Más recientes'];
                @endphp
                @php($canReorderPlans = request('order', 'position') === 'position')

                <div class="plans-dropdown" data-plans-dropdown>
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <button type="button" class="plans-dropdown-trigger" data-plans-trigger><i class="fas fa-toggle-on"></i><span data-plans-label>{{ $statusLabels[request('status')] ?? 'Todos los estados' }}</span><i class="fas fa-chevron-down"></i></button>
                    <div class="plans-dropdown-menu"><button type="button" data-value="">Todos los estados</button><button type="button" data-value="active">Planes activos</button><button type="button" data-value="inactive">Planes inactivos</button></div>
                </div>

                <div class="plans-dropdown" data-plans-dropdown>
                    <input type="hidden" name="currency" value="{{ request('currency') }}">
                    <button type="button" class="plans-dropdown-trigger" data-plans-trigger><i class="fas fa-coins"></i><span data-plans-label>{{ $currencyLabels[request('currency')] ?? 'Todas las monedas' }}</span><i class="fas fa-chevron-down"></i></button>
                    <div class="plans-dropdown-menu"><button type="button" data-value="">Todas las monedas</button><button type="button" data-value="BS">Bolivianos (BS)</button><button type="button" data-value="USD">Dólares (USD)</button></div>
                </div>

                <div class="plans-dropdown" data-plans-dropdown>
                    <input type="hidden" name="period" value="{{ request('period') }}">
                    <button type="button" class="plans-dropdown-trigger" data-plans-trigger><i class="fas fa-calendar-days"></i><span data-plans-label>{{ $periodLabels[request('period')] ?? 'Todos los periodos' }}</span><i class="fas fa-chevron-down"></i></button>
                    <div class="plans-dropdown-menu"><button type="button" data-value="">Todos los periodos</button>@foreach($periodLabels as $value => $label)<button type="button" data-value="{{ $value }}">{{ $label }}</button>@endforeach</div>
                </div>

                <div class="plans-dropdown" data-plans-dropdown>
                    <input type="hidden" name="order" value="{{ request('order', 'position') }}">
                    <button type="button" class="plans-dropdown-trigger" data-plans-trigger><i class="fas fa-arrow-down-wide-short"></i><span data-plans-label>{{ $orderLabels[request('order', 'position')] }}</span><i class="fas fa-chevron-down"></i></button>
                    <div class="plans-dropdown-menu">@foreach($orderLabels as $value => $label)<button type="button" data-value="{{ $value }}">{{ $label }}</button>@endforeach</div>
                </div>

                @if(request()->hasAny(['search', 'status', 'currency', 'period', 'order']))
                    <a href="{{ route('administrador.planes.index') }}" class="plans-clear-filters"><i class="fas fa-rotate-left"></i>Limpiar filtros</a>
                @endif
            </form>
        </section>

        <section class="plans-table-section">
            <div class="plans-table-toolbar">
                <div><i class="fas fa-layer-group"></i><span><strong>Planes registrados</strong><small id="plans-order-feedback">{{ $canReorderPlans ? 'Arrastra las cards desde el tirador lateral para cambiar su orden' : $planes->total() . ' resultado(s) con los filtros actuales' }}</small></span></div>
                <form method="GET" action="{{ route('administrador.planes.index') }}" class="plans-per-page-form">
                    @foreach(request()->except(['page', 'per_page']) as $key => $value)
                        @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <label for="plans_per_page">Mostrar</label>
                    <input id="plans_per_page" name="per_page" type="number" min="5" max="100" step="1" list="plans-per-page-options" value="{{ $planes->perPage() }}" onchange="this.form.submit()">
                    <datalist id="plans-per-page-options"><option value="5"><option value="10"><option value="25"><option value="50"><option value="100"></datalist>
                    <span>por página</span>
                </form>
            </div>

            <div class="admin-plans-grid">
                @forelse($planes as $index => $plan)
                    <article class="admin-plan-card" data-plan-card data-plan-id="{{ $plan->id }}" draggable="false">
                        @if($canReorderPlans)
                            <button type="button" class="admin-plan-drag-handle" title="Arrastrar para cambiar el orden" aria-label="Cambiar el orden de {{ $plan->nombre }}"><i class="fas fa-grip-vertical"></i><span>Ordenar</span></button>
                        @endif
                        <div class="admin-plan-summary">
                            <div class="admin-plan-symbol-row">
                                <span class="admin-plan-symbol" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                                <span class="admin-plan-status {{ $plan->activo ? 'is-active' : 'is-inactive' }}">
                                    <i class="fas {{ $plan->activo ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <span class="admin-plan-index">Plan {{ str_pad(($planes->firstItem() ?? 1) + $index, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $plan->nombre }}</h3>
                            <p class="admin-plan-subtitle">{{ $plan->subtitulo ?: ($plan->descripcion ?: 'Plan disponible para tus clientes.') }}</p>

                            <div class="admin-plan-price-row">
                                <strong>{{ number_format((float) $plan->precio, 0, ',', '.') }} <small>{{ strtoupper($plan->moneda) === 'BS' ? 'Bs.' : $plan->moneda }}</small></strong>
                                <span>/ {{ $periodLabels[$plan->periodo_facturacion] ?? ucfirst($plan->periodo_facturacion) }}</span>
                            </div>

                            <div class="admin-plan-meta">
                                <span><i class="fas fa-users"></i>{{ $plan->suscripciones_count }} activa{{ $plan->suscripciones_count === 1 ? '' : 's' }}</span>
                                <span><i class="fas fa-arrow-down-1-9"></i>Orden {{ $plan->orden }}</span>
                            </div>

                            <div class="admin-plan-actions">
                                <a href="{{ route('administrador.planes.edit', $plan) }}" class="admin-plan-edit"><i class="fas fa-pen"></i>Editar plan</a>
                                <button type="button" class="admin-plan-delete" data-delete-url="{{ route('administrador.planes.destroy', $plan) }}" data-plan-name="{{ $plan->nombre }}" aria-label="Eliminar {{ $plan->nombre }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>

                        <div class="admin-plan-benefits">
                            <span class="admin-benefits-title">Este plan incluye</span>
                            <div class="admin-plan-features">
                                @forelse($plan->planCaracteristicas as $feature)
                                    <div class="admin-plan-feature">
                                        <i class="fas fa-check"></i>
                                        <span>
                                            {{ optional($feature->caracteristica)->nombre ?? 'Característica incluida' }}
                                            @if($feature->cantidad || $feature->frecuencia)
                                                <small>
                                                    @if($feature->cantidad){{ $feature->cantidad }}@endif
                                                    @if($feature->cantidad && $feature->frecuencia) · @endif
                                                    @if($feature->frecuencia){{ $feature->frecuencia }}@endif
                                                </small>
                                            @endif
                                        </span>
                                    </div>
                                @empty
                                    <div class="admin-plan-feature is-empty"><i class="fas fa-minus"></i><span>Sin características registradas</span></div>
                                @endforelse
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-plans-empty"><i class="fas fa-clipboard-list"></i><strong>No hay planes para mostrar</strong><span>Prueba modificando los filtros o crea un nuevo plan.</span></div>
                @endforelse
            </div>

            <div class="plans-pagination">{{ $planes->onEachSide(1)->links('componentes.paginacion-es') }}</div>
        </section>
    </main>
</div>

<div id="plan-delete-modal" class="plans-delete-modal hidden" role="dialog" aria-modal="true" aria-labelledby="plan-delete-title">
    <div class="plans-delete-dialog">
        <button type="button" class="plans-delete-close" data-delete-close><i class="fas fa-times"></i></button>
        <span class="plans-delete-icon"><i class="fas fa-triangle-exclamation"></i></span>
        <h3 id="plan-delete-title">Eliminar plan</h3>
        <p>¿Confirmas que deseas eliminar <strong id="plan-delete-name"></strong>? Esta acción no se puede deshacer.</p>
        <form id="plan-delete-form" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" data-delete-close>Cancelar</button>
            <button type="submit"><i class="fas fa-trash"></i>Sí, eliminar</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('plans-filter-form');
    const dropdowns = [...document.querySelectorAll('[data-plans-dropdown]')];
    const closeDropdowns = except => dropdowns.forEach(dropdown => {
        if (dropdown !== except) {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-plans-trigger]')?.setAttribute('aria-expanded', 'false');
        }
    });

    dropdowns.forEach(dropdown => {
        const input = dropdown.querySelector('input[type="hidden"]');
        const trigger = dropdown.querySelector('[data-plans-trigger]');
        const label = dropdown.querySelector('[data-plans-label]');
        const options = [...dropdown.querySelectorAll('.plans-dropdown-menu button')];
        trigger.setAttribute('aria-expanded', 'false');
        options.forEach(option => {
            option.classList.toggle('is-selected', option.dataset.value === input.value);
            option.addEventListener('click', event => {
                event.stopPropagation();
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                closeDropdowns();
                form.requestSubmit();
            });
        });
        trigger.addEventListener('click', event => {
            event.stopPropagation();
            const opening = !dropdown.classList.contains('is-open');
            closeDropdowns(dropdown);
            dropdown.classList.toggle('is-open', opening);
            trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
        });
    });
    document.addEventListener('click', () => closeDropdowns());

    const plansGrid = document.querySelector('.admin-plans-grid');
    const orderFeedback = document.getElementById('plans-order-feedback');
    let draggedPlanCard = null;
    let originalPlanOrder = [];

    const planCards = () => [...plansGrid.querySelectorAll('[data-plan-card]')];
    const restorePlanOrder = ids => ids.forEach(id => {
        const card = plansGrid.querySelector('[data-plan-id="' + id + '"]');
        if (card) plansGrid.appendChild(card);
    });
    const updateVisibleOrder = orders => planCards().forEach((card, index) => {
        const visibleNumber = {{ (int) ($planes->firstItem() ?? 1) }} + index;
        const savedOrder = orders?.[card.dataset.planId] ?? visibleNumber;
        const indexLabel = card.querySelector('.admin-plan-index');
        const orderLabel = card.querySelector('.admin-plan-meta span:last-child');
        if (indexLabel) indexLabel.textContent = 'Plan ' + String(visibleNumber).padStart(2, '0');
        if (orderLabel) orderLabel.innerHTML = '<i class="fas fa-arrow-down-1-9"></i>Orden ' + savedOrder;
    });
    const persistPlanOrder = async () => {
        const planIds = planCards().map(card => Number(card.dataset.planId));
        plansGrid.classList.add('is-saving-order');
        orderFeedback.className = 'is-saving';
        orderFeedback.textContent = 'Guardando el nuevo orden...';
        try {
            const response = await fetch(@json(route('administrador.planes.reordenar')), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify({ plan_ids: planIds }),
            });
            if (!response.ok) throw new Error('No se pudo guardar el orden.');
            const data = await response.json();
            updateVisibleOrder(data.orders);
            orderFeedback.className = 'is-success';
            orderFeedback.textContent = 'Orden actualizado correctamente';
        } catch (error) {
            restorePlanOrder(originalPlanOrder);
            orderFeedback.className = 'is-error';
            orderFeedback.textContent = error.message;
        } finally {
            plansGrid.classList.remove('is-saving-order');
        }
    };

    plansGrid.querySelectorAll('.admin-plan-drag-handle').forEach(handle => {
        const card = handle.closest('[data-plan-card]');
        handle.addEventListener('mousedown', () => {
            card.setAttribute('draggable', 'true');
        });
        handle.addEventListener('mouseup', () => {
            if (!draggedPlanCard) card.setAttribute('draggable', 'false');
        });
    });
    planCards().forEach(card => {
        card.addEventListener('dragstart', event => {
            if (card.getAttribute('draggable') !== 'true') {
                event.preventDefault();
                return;
            }
            draggedPlanCard = card;
            originalPlanOrder = planCards().map(item => item.dataset.planId);
            card.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.planId);
        });
        card.addEventListener('dragenter', () => {
            if (draggedPlanCard && draggedPlanCard !== card) card.classList.add('is-drag-over');
        });
        card.addEventListener('dragleave', () => card.classList.remove('is-drag-over'));
        card.addEventListener('dragover', event => {
            if (!draggedPlanCard || draggedPlanCard === card) return;
            event.preventDefault();
            const cards = planCards();
            const draggedIndex = cards.indexOf(draggedPlanCard);
            const targetIndex = cards.indexOf(card);
            plansGrid.insertBefore(draggedPlanCard, draggedIndex < targetIndex ? card.nextSibling : card);
        });
        card.addEventListener('drop', event => event.preventDefault());
        card.addEventListener('dragend', () => {
            const changed = originalPlanOrder.join(',') !== planCards().map(item => item.dataset.planId).join(',');
            card.classList.remove('is-dragging');
            card.setAttribute('draggable', 'false');
            planCards().forEach(item => item.classList.remove('is-drag-over'));
            draggedPlanCard = null;
            if (changed) persistPlanOrder();
        });
    });

    const modal = document.getElementById('plan-delete-modal');
    const deleteForm = document.getElementById('plan-delete-form');
    const deleteName = document.getElementById('plan-delete-name');
    const closeDeleteModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('is-open');
        document.body.classList.remove('overflow-hidden');
    };
    document.querySelectorAll('[data-delete-url]').forEach(button => button.addEventListener('click', () => {
        deleteForm.action = button.dataset.deleteUrl;
        deleteName.textContent = button.dataset.planName;
        modal.classList.remove('hidden');
        modal.classList.add('is-open');
        document.body.classList.add('overflow-hidden');
    }));
    document.querySelectorAll('[data-delete-close]').forEach(button => button.addEventListener('click', closeDeleteModal));
    modal.addEventListener('click', event => { if (event.target === modal) closeDeleteModal(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeDropdowns();
            if (!modal.classList.contains('hidden')) closeDeleteModal();
        }
    });
});
</script>

<style>
    .plans-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}.plans-control-hero{position:relative;isolation:isolate;overflow:hidden;width:100%;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px;color:#fff;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}.plans-control-hero::after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 50%)}.plans-control-copy span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.plans-control-copy h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.plans-quick-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.plans-quick-actions a{display:inline-flex;align-items:center;gap:9px;padding:11px 14px;border:1px solid #fff;border-radius:.65rem;background:#fff;color:#4f46e5;font-size:.72rem;font-weight:900;transition:.18s}.plans-quick-actions a:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .plans-alert{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:20px 24px 0;padding:13px 16px;border:1px solid;border-radius:12px;font-size:.76rem;font-weight:700}.plans-alert span{display:flex;align-items:center;gap:8px}.plans-alert button{opacity:.65}.plans-alert-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.plans-alert-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .plans-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:24px}.plans-kpi-card{--kpi-accent:#117e8c;--kpi-soft:#e8f5f6;--kpi-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px;border:1px solid rgba(var(--kpi-rgb),.22);border-radius:1rem;background:linear-gradient(135deg,#fff 35%,var(--kpi-soft));box-shadow:inset 0 4px 0 var(--kpi-accent),0 10px 24px rgba(45,66,34,.09);transition:.22s}.plans-kpi-card::before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--kpi-rgb),.09);border-radius:50%}.plans-kpi-card::after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.plans-kpi-card:hover{transform:translateY(-5px);box-shadow:inset 0 4px 0 var(--kpi-accent),0 17px 32px rgba(var(--kpi-rgb),.16)}.plans-kpi-card span,.plans-kpi-card small{display:block}.plans-kpi-card span{color:#596170;font-size:.7rem;font-weight:900;text-transform:uppercase}.plans-kpi-card strong{display:block;margin-top:9px;color:#263024;font-size:1.85rem;font-weight:900;line-height:1}.plans-kpi-card small{margin-top:8px;color:#7f8878;font-size:.62rem}.plans-kpi-card>i{width:52px;height:52px;display:grid;place-items:center;flex:none;border-radius:14px;background:var(--kpi-accent);color:#fff;font-size:1.18rem;box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27)}.plans-kpi-total{--kpi-accent:#117e8c;--kpi-soft:#e6f4f5;--kpi-rgb:17,126,140}.plans-kpi-active{--kpi-accent:#7da533;--kpi-soft:#f0f6e7;--kpi-rgb:125,165,51}.plans-kpi-inactive{--kpi-accent:#e3a122;--kpi-soft:#fff6df;--kpi-rgb:227,161,34}.plans-kpi-subscriptions{--kpi-accent:#e37225;--kpi-soft:#fff0e6;--kpi-rgb:227,114,37}
    .plans-page-content{margin:0 24px}.plans-filter-panel{position:relative;z-index:30;margin-bottom:24px;padding:20px;border:1px solid #e1e3de;border-radius:16px;background:#f8f8f6;box-shadow:0 9px 22px rgba(55,60,52,.06)}.plans-filter-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px;align-items:end}.plans-search-filter{position:relative;grid-column:span 4}.plans-search-filter>i{position:absolute;top:17px;left:16px;color:#737a70}.plans-search-filter input{width:100%;height:50px;padding:0 16px 0 46px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;color:#3f443d;box-shadow:0 2px 5px rgba(55,60,52,.08);font-size:.82rem;outline:0}.plans-search-filter input:focus{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}.plans-dropdown{position:relative;z-index:40;grid-column:span 2}.plans-dropdown.is-open{z-index:90}.plans-dropdown-trigger{position:relative;width:100%;height:50px;display:flex;align-items:center;gap:10px;padding:0 40px 0 16px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;color:#3f443d;text-align:left;box-shadow:0 2px 5px rgba(55,60,52,.08)}.plans-dropdown-trigger:hover,.plans-dropdown.is-open .plans-dropdown-trigger{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}.plans-dropdown-trigger>i:first-child{width:18px;color:#737a70;text-align:center}.plans-dropdown-trigger span{min-width:0;overflow:hidden;flex:1;font-size:.76rem;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.plans-dropdown-trigger>i:last-child{position:absolute;right:16px;color:#8a9186;font-size:.68rem;transition:.18s}.plans-dropdown.is-open .plans-dropdown-trigger>i:last-child{transform:rotate(180deg)}.plans-dropdown-menu{position:absolute;top:calc(100% + 8px);right:0;left:0;display:none;max-height:240px;overflow-y:auto;padding:7px;border:1px solid #d9dcd6;border-radius:14px;background:#fff;box-shadow:0 18px 40px rgba(55,60,52,.16)}.plans-dropdown.is-open .plans-dropdown-menu{display:block}.plans-dropdown-menu button{width:100%;min-height:39px;padding:8px 10px;border:0;border-radius:8px;background:transparent;color:#565d53;text-align:left;font-size:.72rem;font-weight:700}.plans-dropdown-menu button:hover,.plans-dropdown-menu button.is-selected{background:#eff0ed;color:#3f443d}.plans-dropdown-menu button.is-selected::after{content:'✓';float:right}.plans-clear-filters{grid-column:1/-1;min-height:50px;display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #d7dad4;border-radius:14px;background:#fff;color:#62685f;font-size:.78rem;font-weight:800}.plans-clear-filters:hover{background:#eff0ed;color:#3f443d}
    .plans-table-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:12px;padding:12px 15px;border:1px solid #e2ead5;border-radius:14px;background:#f9fbf5}.plans-table-toolbar>div{display:flex;align-items:center;gap:11px}.plans-table-toolbar>div>i{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#edf4e4;color:#7da533}.plans-table-toolbar strong,.plans-table-toolbar small{display:block}.plans-table-toolbar strong{color:#31382b;font-size:.8rem}.plans-table-toolbar small{margin-top:2px;color:#8a9380;font-size:.62rem}.plans-per-page-form{display:flex;align-items:center;gap:8px;color:#66705c;font-size:.72rem;font-weight:700}.plans-per-page-form input{width:76px;height:38px;padding:0 8px;border:1px solid #ccd9bb;border-radius:10px;background:#fff;color:#405128;text-align:center;font-weight:900;outline:0}.plans-green-table{overflow:hidden;border:1px solid #d8e3c7;border-radius:16px;background:#fff;box-shadow:0 9px 24px rgba(91,121,38,.12)}.plans-table-scroll{overflow-x:auto}.plans-table{width:100%;min-width:1320px;border-collapse:collapse}.plans-table th{padding:16px 18px;border-right:1px solid rgba(255,255,255,.3);background:#7da533;color:#fff;text-align:left;font-size:.62rem;font-weight:900;letter-spacing:.055em;text-transform:uppercase}.plans-table th:last-child,.plans-table td:last-child{border-right:0}.plans-table td{padding:15px 18px;border-right:1px solid #d8e3c7;border-bottom:1px solid #dfe8d1;color:#4b5563;font-size:.72rem;vertical-align:middle}.plans-table tbody tr:nth-child(even) td{background:#f1f7e8}.plans-table tbody tr:hover td{background:#e6f0d8}.plans-number-heading{width:72px;text-align:center!important}.plans-row-number{text-align:center;color:#638524!important;font-weight:900}.plans-name-cell{display:flex;align-items:center;gap:12px}.plans-name-cell>span{width:40px;height:40px;display:grid;place-items:center;flex:none;border-radius:50%;background:linear-gradient(135deg,#7da533,#117e8c);color:#fff;font-weight:900}.plans-name-cell strong,.plans-name-cell small{display:block}.plans-name-cell strong{color:#111827;font-size:.75rem}.plans-name-cell small{max-width:190px;margin-top:3px;overflow:hidden;color:#7b8378;font-size:.61rem;text-overflow:ellipsis;white-space:nowrap}.plans-price{color:#273224;font-size:.76rem}.plans-period,.plans-subscriptions-count{display:inline-flex;align-items:center;gap:6px}.plans-period i,.plans-subscriptions-count i{color:#7da533}.plans-features-cell{display:flex;flex-wrap:wrap;gap:4px;max-width:290px}.plans-features-cell span,.plans-features-cell b{max-width:125px;overflow:hidden;padding:4px 7px;border:1px solid #cedfb4;border-radius:999px;background:#edf4e4;color:#638524;font-size:.56rem;text-overflow:ellipsis;white-space:nowrap}.plans-features-cell b{background:#fff}.plans-status{display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border:1px solid;border-radius:999px;font-size:.59rem;font-weight:800}.plans-status.is-active{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.plans-status.is-inactive{border-color:#d7d9dc;background:#f3f4f6;color:#62685f}.plans-order{text-align:center;font-weight:900}.plans-actions{display:flex;align-items:center;gap:5px}.plans-actions a,.plans-actions button{display:inline-flex;align-items:center;gap:5px;padding:7px 9px;border-radius:8px;font-size:.59rem;font-weight:800}.plans-action-edit{background:#e6f4f5;color:#0d6975}.plans-action-delete{background:#fff0f0;color:#b13636}.plans-empty{padding:42px!important;text-align:center}.plans-empty i,.plans-empty strong,.plans-empty span{display:block}.plans-empty i{margin-bottom:10px;color:#8a9380;font-size:1.7rem}.plans-empty strong{color:#31382b}.plans-empty span{margin-top:5px;color:#8a9380}.plans-pagination{margin-top:28px}
    .admin-plans-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.admin-plan-card{--plan-color:#ef6c22;min-width:0;display:flex;flex-direction:column;overflow:hidden;border:1px solid #ded7e1;border-top:5px solid var(--plan-color);border-radius:5px;background:#fff;box-shadow:0 15px 36px #e5dfe7;transition:transform .25s ease,box-shadow .25s ease}.admin-plan-card:nth-child(3n+2){--plan-color:#117e8c}.admin-plan-card:nth-child(3n){--plan-color:#5b2b76}.admin-plan-card:hover{transform:translateY(-6px);box-shadow:0 22px 44px #d8d0db}.admin-plan-summary{padding:25px 24px 22px}.admin-plan-symbol-row{display:flex;align-items:center;justify-content:space-between;min-height:42px;gap:12px}.admin-plan-symbol{width:42px;height:42px;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;flex:none;transform:rotate(45deg)}.admin-plan-symbol i:nth-child(1){border-radius:100% 0 0;background:var(--plan-color)}.admin-plan-symbol i:nth-child(2){border-radius:0 100% 0 0;background:#f5a900}.admin-plan-symbol i:nth-child(3){border-radius:0 0 0 100%;background:#117e8c}.admin-plan-symbol i:nth-child(4){border-radius:0 0 100% 0;background:#7da533}.admin-plan-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid;border-radius:999px;font-size:.62rem;font-weight:900;text-transform:uppercase}.admin-plan-status.is-active{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.admin-plan-status.is-inactive{border-color:#d7d9dc;background:#f3f4f6;color:#62685f}.admin-plan-index{display:block;margin-top:22px;color:var(--plan-color);font-size:.65rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.admin-plan-summary h3{margin:7px 0 0;color:#302834;font-size:1.55rem;font-weight:900;line-height:1.15}.admin-plan-subtitle{min-height:42px;margin:8px 0 0;color:#756a7a;font-size:.8rem;line-height:1.55}.admin-plan-price-row{display:flex;align-items:flex-end;flex-wrap:wrap;gap:8px;margin-top:21px}.admin-plan-price-row strong{color:#302834;font-size:2rem;font-weight:900;line-height:1}.admin-plan-price-row strong small{font-size:.85rem}.admin-plan-price-row>span{color:#887d8c;font-size:.72rem}.admin-plan-meta{display:flex;flex-wrap:wrap;gap:8px 14px;margin-top:15px;color:#756a7a;font-size:.68rem}.admin-plan-meta span{display:inline-flex;align-items:center;gap:6px}.admin-plan-meta i{color:var(--plan-color)}.admin-plan-actions{display:grid;grid-template-columns:minmax(0,1fr) 44px;gap:9px;margin-top:20px}.admin-plan-edit,.admin-plan-delete{height:44px;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:4px;font-size:.72rem;font-weight:900;transition:.18s}.admin-plan-edit{background:var(--plan-color);color:#fff}.admin-plan-edit:hover{filter:brightness(.92);color:#fff}.admin-plan-delete{border:1px solid #efcaca;background:#fff;color:#c43d3d}.admin-plan-delete:hover{background:#fff0f0}.admin-plan-benefits{flex:1;padding:21px 24px 24px;border-top:1px solid #e5dfe7;background:#f7f5f8}.admin-benefits-title{display:block;margin-bottom:13px;color:#514557;font-size:.68rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.admin-plan-features{display:grid;gap:10px}.admin-plan-feature{display:grid;grid-template-columns:20px minmax(0,1fr);gap:8px;align-items:start;color:#5f5563;font-size:.76rem;line-height:1.45}.admin-plan-feature>i{width:18px;height:18px;display:grid;place-items:center;border-radius:50%;background:#7da533;color:#fff;font-size:.55rem}.admin-plan-feature small{display:block;margin-top:2px;color:#8b818f}.admin-plan-feature.is-empty>i{background:#a8a1aa}.admin-plans-empty{grid-column:1/-1;min-height:240px;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:36px;border:1px dashed #ccc5ce;border-radius:8px;background:#faf9fb;color:#8a818e;text-align:center}.admin-plans-empty i{margin-bottom:12px;font-size:2rem}.admin-plans-empty strong{color:#403743;font-size:.9rem}.admin-plans-empty span{margin-top:6px;font-size:.72rem}
    .admin-plan-card:has(.admin-plan-drag-handle){position:relative}.admin-plan-card:has(.admin-plan-drag-handle)>.admin-plan-summary,.admin-plan-card:has(.admin-plan-drag-handle)>.admin-plan-benefits{padding-left:55px}.admin-plan-drag-handle{position:absolute;z-index:8;top:0;bottom:0;left:0;width:34px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;border-right:1px solid #ded7e1;background:linear-gradient(180deg,#faf9fb,#f0edf2);color:#8e8491;cursor:grab;transition:.18s}.admin-plan-drag-handle:hover{width:39px;background:#edf7f8;color:#117e8c}.admin-plan-drag-handle:active{cursor:grabbing}.admin-plan-drag-handle i{font-size:.9rem}.admin-plan-drag-handle span{font-size:.48rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;writing-mode:vertical-rl;transform:rotate(180deg)}.admin-plan-card.is-dragging{z-index:20;opacity:.48;transform:scale(.98);box-shadow:0 8px 22px rgba(48,40,52,.14)}.admin-plan-card.is-drag-over{outline:3px solid rgba(17,126,140,.24);outline-offset:3px}.admin-plans-grid.is-saving-order{pointer-events:none;opacity:.72}#plans-order-feedback.is-saving{color:#b86a22}#plans-order-feedback.is-success{color:#638524}#plans-order-feedback.is-error{color:#b13636}
    .plans-delete-modal{position:fixed;z-index:12000;inset:0;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.62);backdrop-filter:blur(5px)}.plans-delete-modal.is-open{display:flex}.plans-delete-dialog{position:relative;width:100%;max-width:430px;padding:28px;border-radius:22px;background:#fff;text-align:center;box-shadow:0 28px 70px rgba(15,23,42,.3)}.plans-delete-close{position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;color:#737a70}.plans-delete-close:hover{background:#f3f4f6}.plans-delete-icon{width:58px;height:58px;display:grid;place-items:center;margin:0 auto 16px;border-radius:18px;background:#fff0f0;color:#dc2626;font-size:1.35rem}.plans-delete-dialog h3{margin:0;color:#25272b;font-size:1.2rem;font-weight:900}.plans-delete-dialog p{margin:10px 0 22px;color:#6b7280;font-size:.78rem;line-height:1.65}.plans-delete-dialog form{display:grid;grid-template-columns:1fr 1fr;gap:10px}.plans-delete-dialog form button{min-height:44px;border-radius:11px;background:#f3f4f6;color:#565d64;font-size:.75rem;font-weight:900}.plans-delete-dialog form button[type=submit]{display:flex;align-items:center;justify-content:center;gap:7px;background:#dc2626;color:#fff}
    @media(max-width:1100px){.plans-kpi-grid{grid-template-columns:repeat(2,1fr)}.plans-filter-grid{grid-template-columns:repeat(2,1fr)}.plans-search-filter{grid-column:1/-1}.plans-dropdown{grid-column:span 1}.admin-plans-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:700px){.plans-control-hero{min-height:205px;flex-direction:column;justify-content:center;padding:24px 20px;text-align:center}.plans-quick-actions{justify-content:center;width:100%}.plans-quick-actions a{justify-content:center;width:100%}.plans-kpi-grid{grid-template-columns:1fr;margin-right:12px;margin-left:12px}.plans-page-content{margin:0 12px}.plans-filter-panel{padding:14px}.plans-filter-grid{grid-template-columns:1fr}.plans-search-filter,.plans-dropdown{grid-column:1}.plans-table-toolbar{align-items:flex-start;flex-direction:column}.plans-alert{margin-right:12px;margin-left:12px}.admin-plans-grid{grid-template-columns:1fr}.admin-plan-summary,.admin-plan-benefits{padding-right:20px;padding-left:20px}}
</style>
@endsection
