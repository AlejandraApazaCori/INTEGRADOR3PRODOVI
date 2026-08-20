@extends('layouts.app2')

@section('title', 'Historial de Pagos')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div id="payment-history" class="min-h-screen">
    <div class="payments-shell">
        <header class="payments-hero">
            <div class="payments-hero-content">
                <span class="payments-kicker">Tus transacciones</span>
                <h1>Historial de <span>pagos</span></h1>
                <p>Consulta todos tus pagos realizados y revisa su estado actual.</p>
            </div>
            <div class="payments-hero-side">
                <div class="payments-total"><small>Total de pagos</small><strong>{{ $pagos->total() }}</strong></div>
                <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
            </div>
        </header>

        <main class="payments-content">
            @if(session('success'))
                <div class="payment-success-alert"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>
            @endif
            <section class="payments-panel" aria-labelledby="payments-list-title">
                <div class="payments-toolbar">
                    <span class="payments-toolbar-icon"><i class="fas fa-receipt"></i></span>
                    <div><h2 id="payments-list-title">Registros de pagos</h2><p>Listado completo de tus transacciones</p></div>
                    <a href="{{ route('clientes.planes.comprar') }}" class="buy-plan-button"><i class="fas fa-cart-plus"></i> Comprar otro plan</a>
                </div>

                <form method="GET" action="{{ route('clientes.historial.pagos') }}" class="payments-filters">
                    <div class="filter-field">
                        <span class="filter-label">Tipo de plan</span>
                        <div class="custom-filter-select" data-custom-select>
                            <input type="hidden" name="plan_id" value="{{ request('plan_id') }}">
                            <button type="button" class="custom-filter-trigger" aria-expanded="false">
                                <span>{{ $planes->firstWhere('id', (int) request('plan_id'))?->nombre ?? 'Todos los planes' }}</span><i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu">
                                <button type="button" data-value="">Todos los planes</button>
                                @foreach($planes as $plan)
                                    <button type="button" data-value="{{ $plan->id }}">{{ $plan->nombre }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="filter-field">
                        <span class="filter-label">Estado</span>
                        <div class="custom-filter-select" data-custom-select>
                            <input type="hidden" name="estado" value="{{ request('estado') }}">
                            <button type="button" class="custom-filter-trigger" aria-expanded="false">
                                <span>{{ request('estado') ? ucfirst(request('estado')) : 'Todos los estados' }}</span><i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu">
                                <button type="button" data-value="">Todos los estados</button>
                                <button type="button" data-value="completado">Completado</button>
                                <button type="button" data-value="pendiente">Pendiente</button>
                                <button type="button" data-value="cancelado">Cancelado</button>
                            </div>
                        </div>
                    </div>
                    <div class="filter-field">
                        <span class="filter-label">Empresa</span>
                        <div class="custom-filter-select" data-custom-select>
                            <input type="hidden" name="empresa_id" value="{{ request('empresa_id') }}">
                            <button type="button" class="custom-filter-trigger" aria-expanded="false">
                                <span>{{ $empresas->firstWhere('id', (int) request('empresa_id'))?->nombre_empresa ?? 'Todas las empresas' }}</span><i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu">
                                <button type="button" data-value="">Todas las empresas</button>
                                @foreach($empresas as $empresa)
                                    <button type="button" data-value="{{ $empresa->id }}">{{ $empresa->nombre_empresa }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="filter-field">
                        <span class="filter-label">Desde</span>
                        <div class="custom-date-picker" data-date-picker>
                            <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}">
                            <button type="button" class="custom-date-trigger" aria-expanded="false"><span>{{ request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : 'Seleccionar fecha' }}</span><i class="fas fa-calendar-days"></i></button>
                            <div class="custom-calendar"></div>
                        </div>
                    </div>
                    <div class="filter-field">
                        <span class="filter-label">Hasta</span>
                        <div class="custom-date-picker" data-date-picker>
                            <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                            <button type="button" class="custom-date-trigger" aria-expanded="false"><span>{{ request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : 'Seleccionar fecha' }}</span><i class="fas fa-calendar-days"></i></button>
                            <div class="custom-calendar"></div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
                        @if(request()->hasAny(['plan_id', 'empresa_id', 'estado', 'fecha_desde', 'fecha_hasta']))
                            <a href="{{ route('clientes.historial.pagos') }}" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
                        @endif
                    </div>
                </form>

                <div class="payments-table-wrap">
                    <table class="payments-table">
                        <thead>
                            <tr><th>Código de pago</th><th>Empresa</th><th>Plan</th><th>Monto</th><th>Método</th><th>Estado</th><th>Fecha de pago</th><th>Vigente hasta</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pagos as $pago)
                                <tr>
                                    <td><div class="payment-code"><span><i class="fas fa-receipt"></i></span><strong>{{ $pago->comprobantePago->numero_formateado ?? 'N/A' }}</strong></div></td>
                                    <td><span class="payment-company"><i class="fas fa-building"></i>{{ $pago->suscripcion?->empresa?->nombre_empresa ?? 'Sin asignar' }}</span></td>
                                    <td>{{ $pago->plan->nombre ?? 'N/A' }}</td>
                                    <td><strong class="payment-amount">{{ number_format($pago->monto, 2, ',', '.') }} {{ strtoupper($pago->moneda) === 'BS' ? 'Bs.' : $pago->moneda }}</strong></td>
                                    <td><span class="payment-badge method-{{ $pago->metodo === 'qr' ? 'qr' : 'other' }}"><i class="fas {{ $pago->metodo === 'qr' ? 'fa-qrcode' : 'fa-money-bill-wave' }}"></i>{{ ucfirst($pago->metodo) }}</span></td>
                                    <td>
                                        @php
                                            $estadoIcons = ['completado' => 'fa-check-circle', 'pendiente' => 'fa-clock', 'cancelado' => 'fa-times-circle'];
                                            $icon = $estadoIcons[$pago->estado] ?? 'fa-circle';
                                        @endphp
                                        <span class="payment-badge status-{{ $pago->estado }}"><i class="fas {{ $icon }}"></i>{{ ucfirst($pago->estado) }}</span>
                                    </td>
                                    <td class="payment-date"><i class="fas fa-calendar-alt"></i>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($pago->suscripcion?->vigencia_activada_at)
                                            <span class="valid-until"><i class="fas fa-hourglass-half"></i>{{ $pago->suscripcion->fecha_fin->format('d/m/Y') }}</span>
                                        @else
                                            <span class="validity-pending"><i class="fas fa-clock"></i>Pendiente de activación</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="payment-actions">
                                            @if($pago->estado === 'pendiente')
                                                @if($pago->codigoPago)
                                                    <button type="button" onclick="verCodigoPendiente(@js($pago->codigoPago->codigo), @js(route('pago.fisico.codigo.pdf', $pago)))" class="payment-action pending-code-action"><i class="fas fa-barcode"></i> Ver código</button>
                                                @else
                                                    <span class="pending-code-wait"><i class="fas fa-spinner fa-spin"></i> Generando código</span>
                                                @endif
                                                @if($pago->codigoPago)
                                                    <form action="{{ route('clientes.pagos.pendientes.eliminar', $pago) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este pedido pendiente?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="delete-order-action" aria-label="Eliminar pedido" data-tooltip="Eliminar pedido"><i class="fas fa-xmark"></i></button>
                                                    </form>
                                                @endif
                                            @else
                                                <button type="button" onclick="verComprobante({{ $pago->id }})" class="payment-action view-action"><i class="fas fa-eye"></i> Ver</button>
                                            @endif
                                            @if($pago->estado === 'completado')
                                                <button type="button" onclick="descargarComprobante({{ $pago->id }})" class="payment-action download-action"><i class="fas fa-download"></i> Descargar</button>
                                            @endif
                                            @php
                                                $mostrarRenovacion = $pago->suscripcion?->vigencia_activada_at
                                                    && $pago->suscripcion->fecha_fin->greaterThanOrEqualTo(now())
                                                    && $pago->suscripcion->fecha_fin->lessThanOrEqualTo(now()->copy()->addDays(5));
                                            @endphp
                                            @if($mostrarRenovacion)
                                                <a href="{{ route('clientes.planes.comprar') }}" class="renew-plan-action" aria-label="Renovación de plan" data-tooltip="Renovación de plan">
                                                    <i class="fas fa-rotate"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9"><div class="payments-empty"><i class="fas fa-credit-card"></i><strong>No se encontraron registros de pagos</strong><p>No hay pagos que coincidan con la búsqueda.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($pagos, 'links'))
                    <div class="payments-pagination">{{ $pagos->links() }}</div>
                @endif
            </section>
        </main>
    </div>
</div>

<div id="modalComprobante" class="payment-modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="payment-modal-backdrop" aria-hidden="true" onclick="cerrarModal()"></div>
    <div class="payment-modal-dialog" id="modalContentPanel">
        <header class="payment-modal-header">
            <div><span>Detalle de transacción</span><h3 id="modal-title"><i class="fas fa-file-invoice-dollar"></i> Comprobante de pago</h3></div>
            <button onclick="cerrarModal()" type="button" aria-label="Cerrar comprobante"><i class="fas fa-times"></i></button>
        </header>
        <main id="contenidoComprobante" class="payment-modal-body"></main>
        <footer class="payment-modal-footer"><button onclick="cerrarModal()" type="button">Cerrar</button></footer>
    </div>
</div>

<style>
    #payment-history { --prodovi-purple:#5B2B76; --payments-accent:#7DA533; --prodovi-turquoise:#117E8C; --prodovi-green:#7DA533; background:#fff; color:#17131d; }
    #payment-history .payments-shell { width:100%; padding-bottom:40px; }
    #payment-history .payments-hero { min-height:150px; display:flex; align-items:center; justify-content:space-between; gap:32px; padding:28px 32px; background:#242426; color:#fff; }
    #payment-history .payments-hero-content { max-width:720px; }
    #payment-history .payments-kicker { display:block; margin-bottom:10px; color:var(--payments-accent); font-size:.68rem; font-weight:900; letter-spacing:.13em; text-transform:uppercase; }
    #payment-history .payments-hero h1 { margin:0; font-size:clamp(1.65rem,3vw,2.35rem); font-weight:800; line-height:1.08; letter-spacing:-.035em; }
    #payment-history .payments-hero h1 span { color:var(--payments-accent); }
    #payment-history .payments-hero p { margin-top:11px; color:#aaa5ad; font-size:.86rem; line-height:1.55; }
    #payment-history .payments-hero-side { display:flex; align-items:center; gap:26px; }
    #payment-history .payments-total { min-width:112px; padding:13px 16px; border-left:4px solid var(--payments-accent); background:#303033; }
    #payment-history .payments-total small, #payment-history .payments-total strong { display:block; }
    #payment-history .payments-total small { color:#aaa5ad; font-size:.65rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    #payment-history .payments-total strong { margin-top:3px; color:#fff; font-size:1.55rem; line-height:1; }
    #payment-history .login-mosaic { width:144px; height:96px; display:grid; flex:0 0 auto; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(2,1fr); }
    #payment-history .login-mosaic span:nth-child(1) { background:#EF6C22; border-radius:100% 0 0 0; }
    #payment-history .login-mosaic span:nth-child(2) { background:#F5A900; border-radius:0 0 0 100%; }
    #payment-history .login-mosaic span:nth-child(3) { background:var(--prodovi-purple); border-radius:100% 0 100% 0; }
    #payment-history .login-mosaic span:nth-child(4) { background:var(--prodovi-turquoise); border-radius:0 100% 0 100%; }
    #payment-history .login-mosaic span:nth-child(5) { background:var(--prodovi-green); border-radius:50%; }
    #payment-history .login-mosaic span:nth-child(6) { border:12px solid #607078; border-top-color:transparent; border-left-color:transparent; border-radius:50%; transform:rotate(45deg); }
    #payment-history .payments-content { margin:32px; }
    #payment-history .payment-success-alert { display:flex; align-items:center; gap:9px; margin-bottom:16px; padding:12px 15px; border-left:4px solid var(--green); background:#f4f7ee; color:#587923; font-size:.78rem; font-weight:800; }
    #payment-history .payments-panel { position:relative; overflow:visible; border-top:1px solid #d9d2dc; border-bottom:1px solid #d9d2dc; background:#fff; }
    #payment-history .payments-toolbar { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid #ded7e1; border-left:4px solid var(--prodovi-purple); background:#f7f5f8; }
    #payment-history .payments-toolbar-icon { width:36px; height:36px; display:grid; place-items:center; flex:0 0 auto; border-radius:2px; background:var(--prodovi-purple); color:#fff; }
    #payment-history .payments-toolbar h2 { margin:0; color:#302834; font-size:1rem; font-weight:900; }
    #payment-history .payments-toolbar p { margin-top:2px; color:#887d8c; font-size:.73rem; }
    #payment-history .buy-plan-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; margin-left:auto; padding:10px 15px; border:0; border-radius:3px; background:var(--payments-accent); color:#fff; font-size:.76rem; font-weight:900; text-decoration:none; cursor:pointer; box-shadow:0 5px 0 #587923; transition:.18s ease; }
    #payment-history .buy-plan-button:hover { background:#6c922d; transform:translateY(-1px); }
    #payment-history .payments-filters { position:relative; z-index:50; display:grid; grid-template-columns:minmax(150px,1.15fr) minmax(135px,.9fr) minmax(150px,1.15fr) minmax(135px,.9fr) minmax(135px,.9fr) auto; align-items:end; gap:12px; padding:18px 20px; border-bottom:1px solid #ded7e1; background:#fff; }
    #payment-history .filter-field { min-width:0; }
    #payment-history .filter-label { display:block; margin-bottom:6px; color:#756a7a; font-size:.65rem; font-weight:900; letter-spacing:.06em; text-transform:uppercase; }
    #payment-history .custom-filter-select, #payment-history .custom-date-picker { position:relative; }
    #payment-history .custom-filter-trigger, #payment-history .custom-date-trigger { width:100%; height:40px; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:0 11px; border:1px solid #d8cfdc; border-radius:3px; background:#fff; color:#413745; font-size:.78rem; text-align:left; cursor:pointer; }
    #payment-history .custom-filter-trigger span, #payment-history .custom-date-trigger span { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    #payment-history .custom-filter-trigger i, #payment-history .custom-date-trigger i { color:var(--payments-accent); font-size:.7rem; }
    #payment-history .custom-filter-select.is-open .custom-filter-trigger,
    #payment-history .custom-date-picker.is-open .custom-date-trigger { border-color:var(--payments-accent); box-shadow:0 0 0 2px #e6efd5; }
    #payment-history .custom-filter-select.is-open .custom-filter-trigger i { transform:rotate(180deg); }
    #payment-history .custom-filter-menu, #payment-history .custom-calendar { position:absolute; z-index:60; top:calc(100% + 6px); right:0; left:0; display:none; min-width:190px; padding:7px; border:1px solid #d8cfdc; border-radius:3px; background:#fff; box-shadow:0 16px 34px #cfc8d2; }
    #payment-history .custom-filter-select.is-open .custom-filter-menu,
    #payment-history .custom-date-picker.is-open .custom-calendar { display:block; }
    #payment-history .custom-filter-menu button { width:100%; display:block; padding:9px 10px; border:0; border-radius:2px; background:#fff; color:#514557; font-size:.78rem; text-align:left; cursor:pointer; }
    #payment-history .custom-filter-menu button:hover,
    #payment-history .custom-filter-menu button.is-selected { background:#edf3e2; color:#587923; font-weight:800; }
    #payment-history .custom-calendar { width:278px; left:auto; padding:12px; }
    #payment-history .calendar-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
    #payment-history .calendar-head strong { color:#302834; font-size:.8rem; text-transform:capitalize; }
    #payment-history .calendar-head button { width:30px; height:30px; display:grid; place-items:center; border:1px solid #d8cfdc; border-radius:2px; background:#fff; color:#5B2B76; cursor:pointer; }
    #payment-history .calendar-weekdays, #payment-history .calendar-days { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
    #payment-history .calendar-weekdays span { padding:5px 0; color:#887d8c; font-size:.6rem; font-weight:900; text-align:center; text-transform:uppercase; }
    #payment-history .calendar-days button { aspect-ratio:1; border:0; border-radius:2px; background:#fff; color:#514557; font-size:.7rem; cursor:pointer; }
    #payment-history .calendar-days button:hover { background:#edf3e2; color:#587923; }
    #payment-history .calendar-days button.is-selected { background:var(--payments-accent); color:#fff; font-weight:900; }
    #payment-history .calendar-days button.is-today { outline:1px solid var(--payments-accent); outline-offset:-2px; }
    #payment-history .calendar-days span { aspect-ratio:1; }
    #payment-history .calendar-clear { width:100%; margin-top:9px; padding:7px; border:1px solid #d8cfdc; border-radius:2px; background:#fff; color:#756a7a; font-size:.68rem; font-weight:800; cursor:pointer; }
    #payment-history .filter-actions { display:flex; gap:7px; }
    #payment-history .filter-actions button,
    #payment-history .filter-actions a { height:40px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:0 14px; border:1px solid var(--prodovi-purple); border-radius:3px; background:var(--prodovi-purple); color:#fff; font-size:.75rem; font-weight:800; text-decoration:none; }
    #payment-history .filter-actions a { width:40px; padding:0; border-color:#d8cfdc; background:#fff; color:#756a7a; }
    #payment-history .payments-table-wrap { position:relative; z-index:1; overflow-x:auto; }
    #payment-history .payments-table { width:100%; min-width:1300px; border-collapse:collapse; }
    #payment-history .payments-table th { padding:13px 18px; border-right:1px solid #94b45a; border-bottom:1px solid #6b902a; background:var(--payments-accent); color:#fff; text-align:left; font-size:.66rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    #payment-history .payments-table td { padding:15px 18px; border-right:1px solid #ebe6ed; border-bottom:1px solid #ebe6ed; color:#5f5563; font-size:.8rem; white-space:nowrap; }
    #payment-history .payments-table th:last-child, #payment-history .payments-table td:last-child { border-right:0; }
    #payment-history .payments-table tbody tr { border-left:4px solid transparent; transition:.18s ease; }
    #payment-history .payments-table tbody tr:hover { border-left-color:var(--payments-accent); background:#faf8fb; }
    #payment-history .payments-table tbody tr:last-child td { border-bottom:0; }
    #payment-history .payment-code { display:flex; align-items:center; gap:10px; }
    #payment-history .payment-code > span { width:24px; height:24px; display:grid; place-items:center; color:#628527; }
    #payment-history .payment-code strong, #payment-history .payment-amount { color:#302834; font-weight:900; }
    #payment-history .payment-company { display:inline-flex; align-items:center; gap:7px; color:#413745; font-weight:800; }
    #payment-history .payment-company i { color:var(--prodovi-turquoise); }
    #payment-history .payment-badge { display:inline-flex; align-items:center; gap:6px; padding:2px 0; border:0; background:none; font-size:.72rem; font-weight:900; }
    #payment-history .method-qr { color:#0e707c; }
    #payment-history .method-other, #payment-history .status-completado { color:#587923; }
    #payment-history .status-pendiente { color:#806000; }
    #payment-history .status-cancelado { color:#a52d2d; }
    #payment-history .payment-date i { margin-right:6px; color:#9a8f9e; }
    #payment-history .valid-until, #payment-history .validity-pending { display:inline-flex; align-items:center; gap:7px; font-size:.72rem; font-weight:900; }
    #payment-history .valid-until { color:#587923; }
    #payment-history .validity-pending { color:#806000; }
    #payment-history .payment-actions { display:flex; align-items:center; gap:7px; }
    #payment-history .payment-action { display:inline-flex; align-items:center; gap:6px; padding:6px 2px; border:0; border-bottom:2px solid currentColor; background:none; border-radius:0; font-size:.7rem; font-weight:900; cursor:pointer; transition:.18s ease; }
    #payment-history .view-action { color:var(--prodovi-purple); }
    #payment-history .download-action { color:var(--prodovi-turquoise); }
    #payment-history .pending-code-action { color:#806000; }
    #payment-history .pending-code-wait { display:inline-flex; align-items:center; gap:6px; color:#806000; font-size:.7rem; font-weight:800; }
    #payment-history .payment-action:hover { color:#587923; }
    #payment-history .payment-actions form { margin:0; }
    #payment-history .delete-order-action { position:relative; width:28px; height:28px; display:grid; place-items:center; border:1px solid #b63b3b; border-radius:50%; background:#fff; color:#b63b3b; cursor:pointer; }
    #payment-history .delete-order-action::after { content:attr(data-tooltip); position:absolute; z-index:80; right:0; bottom:calc(100% + 8px); width:max-content; padding:7px 10px; border-radius:3px; background:#242426; color:#fff; font-size:.68rem; font-weight:800; opacity:0; pointer-events:none; transform:translateY(4px); transition:.18s ease; }
    #payment-history .delete-order-action:hover::after,
    #payment-history .delete-order-action:focus-visible::after { opacity:1; transform:none; }
    #payment-history .renew-plan-action { position:relative; width:30px; height:30px; display:grid; place-items:center; border:1px solid #7DA533; border-radius:50%; background:#fff; color:#587923; text-decoration:none; cursor:pointer; }
    #payment-history .renew-plan-action::after { content:attr(data-tooltip); position:absolute; z-index:80; right:0; bottom:calc(100% + 8px); width:max-content; padding:7px 10px; border-radius:3px; background:#242426; color:#fff; font-size:.68rem; font-weight:800; opacity:0; pointer-events:none; transform:translateY(4px); transition:.18s ease; }
    #payment-history .renew-plan-action::before { content:''; position:absolute; right:10px; bottom:calc(100% + 3px); border:5px solid transparent; border-top-color:#242426; opacity:0; transition:.18s ease; }
    #payment-history .renew-plan-action:hover::after,
    #payment-history .renew-plan-action:hover::before,
    #payment-history .renew-plan-action:focus-visible::after,
    #payment-history .renew-plan-action:focus-visible::before { opacity:1; transform:none; }
    #payment-history .payments-empty { padding:60px 20px; text-align:center; }
    #payment-history .payments-empty > i { display:block; margin-bottom:13px; color:var(--prodovi-turquoise); font-size:2rem; }
    #payment-history .payments-empty strong { display:block; color:#302834; }
    #payment-history .payments-empty p { margin-top:5px; color:#887d8c; font-size:.8rem; }
    #payment-history .payments-pagination { padding:16px 20px; border-top:1px solid #ded7e1; background:#f7f5f8; }

    .payment-modal { position:fixed; z-index:2147483000; inset:0; display:flex; align-items:center; justify-content:center; padding:24px; }
    .payment-modal.hidden { display:none; }
    .payment-modal-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.74); backdrop-filter:blur(5px); }
    .payment-modal-dialog { position:relative; width:min(900px,100%); max-height:calc(100vh - 48px); display:flex; flex-direction:column; overflow:hidden; border:1px solid rgba(255,255,255,.15); border-radius:3px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.32); transition:.25s ease; }
    .payment-modal-header { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:22px 26px; border-bottom:5px solid #7DA533; background:#242426; color:#fff; }
    .payment-modal-header span { display:block; margin-bottom:6px; color:#9fc557; font-size:.65rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    .payment-modal-header h3 { margin:0; font-size:1.2rem; font-weight:900; }
    .payment-modal-header h3 i { margin-right:7px; color:#F5A900; }
    .payment-modal-header button { width:38px; height:38px; display:grid; place-items:center; flex:0 0 auto; border:1px solid #565259; border-radius:2px; background:#343436; color:#fff; cursor:pointer; }
    .payment-modal-header button:hover { border-color:#7DA533; background:#7DA533; }
    .payment-modal-body { min-height:180px; max-height:68vh; overflow-y:auto; padding:24px 26px; background:#fff; }
    .pending-code-card { max-width:520px; margin:10px auto; padding:34px 24px; border:1px solid #d8cfdc; border-top:5px solid #F5A900; text-align:center; }
    .pending-code-card > i { color:#806000; font-size:2rem; }
    .pending-code-card p { margin-top:12px; color:#756a7a; font-size:.8rem; line-height:1.55; }
    .pending-code-value { display:block; margin:18px 0; color:#302834; font-size:clamp(1.45rem,4vw,2rem); font-weight:900; letter-spacing:.12em; }
    .pending-code-contact { display:grid; gap:8px; margin:0 0 18px; text-align:left; }
    .pending-code-contact a { display:grid; grid-template-columns:30px minmax(0,1fr); align-items:center; gap:9px; padding:10px 11px; border:1px solid #ded7e1; border-radius:3px; color:#514557; font-size:.73rem; line-height:1.45; text-decoration:none; }
    .pending-code-contact a > i { width:30px; height:30px; display:grid; place-items:center; color:#117E8C; font-size:.9rem; }
    .pending-code-contact a:last-child > i { color:#587923; }
    .pending-code-contact a:hover { border-color:#7DA533; color:#587923; }
    .pending-code-download { display:inline-flex; align-items:center; gap:8px; padding:10px 15px; border-radius:3px; background:#5B2B76; color:#fff; font-size:.76rem; font-weight:900; text-decoration:none; }
    .payment-modal-footer { display:flex; justify-content:flex-end; padding:14px 26px; border-top:1px solid #e5e0e7; background:#f7f5f8; }
    .payment-modal-footer button { padding:9px 18px; border:0; border-radius:3px; background:#5B2B76; color:#fff; font-size:.78rem; font-weight:800; cursor:pointer; }

    html[data-client-theme="dark"] #payment-history { background:#141216; color:#e9e5eb; }
    html[data-client-theme="dark"] #payment-history .payments-panel { border-color:#3b3540; background:#1e1b21; }
    html[data-client-theme="dark"] #payment-history .payment-success-alert { background:#29331e; color:#b4d27a; }
    html[data-client-theme="dark"] #payment-history .payments-toolbar, html[data-client-theme="dark"] #payment-history .payments-pagination { border-color:#413a45; background:#29252c; }
    html[data-client-theme="dark"] #payment-history .payments-filters { border-color:#413a45; background:#1e1b21; }
    html[data-client-theme="dark"] #payment-history .filter-label { color:#b4abb8; }
    html[data-client-theme="dark"] #payment-history .custom-filter-trigger,
    html[data-client-theme="dark"] #payment-history .custom-date-trigger,
    html[data-client-theme="dark"] #payment-history .custom-filter-menu,
    html[data-client-theme="dark"] #payment-history .custom-calendar { border-color:#4a434e; background:#29252c; color:#eeeaf0; box-shadow:0 16px 34px #0d0b0e; }
    html[data-client-theme="dark"] #payment-history .custom-filter-select.is-open .custom-filter-trigger,
    html[data-client-theme="dark"] #payment-history .custom-date-picker.is-open .custom-date-trigger { border-color:#7DA533; box-shadow:0 0 0 2px #334022; }
    html[data-client-theme="dark"] #payment-history .custom-filter-menu button,
    html[data-client-theme="dark"] #payment-history .calendar-head button,
    html[data-client-theme="dark"] #payment-history .calendar-days button,
    html[data-client-theme="dark"] #payment-history .calendar-clear { border-color:#4a434e; background:#29252c; color:#ddd8df; }
    html[data-client-theme="dark"] #payment-history .custom-filter-menu button:hover,
    html[data-client-theme="dark"] #payment-history .custom-filter-menu button.is-selected,
    html[data-client-theme="dark"] #payment-history .calendar-days button:hover { background:#334022; color:#b9d77f; }
    html[data-client-theme="dark"] #payment-history .calendar-head strong { color:#f1edf3; }
    html[data-client-theme="dark"] #payment-history .calendar-weekdays span { color:#aaa1ae; }
    html[data-client-theme="dark"] #payment-history .calendar-days button.is-selected { background:#7DA533; color:#fff; }
    html[data-client-theme="dark"] #payment-history .filter-actions a { border-color:#4a434e; background:#29252c; color:#d0c8d3; }
    html[data-client-theme="dark"] #payment-history .payments-toolbar h2, html[data-client-theme="dark"] #payment-history .payment-code strong, html[data-client-theme="dark"] #payment-history .payment-amount, html[data-client-theme="dark"] #payment-history .payments-empty strong { color:#f1edf3; }
    html[data-client-theme="dark"] #payment-history .payments-toolbar p, html[data-client-theme="dark"] #payment-history .payments-table td, html[data-client-theme="dark"] #payment-history .payments-empty p { color:#b4abb8; }
    html[data-client-theme="dark"] #payment-history .payments-table th,
    html[data-client-theme="dark"] #payment-history .payments-table td { border-color:#38323c; }
    html[data-client-theme="dark"] #payment-history .payments-table tbody tr:hover { background:#27232a; }
    html[data-client-theme="dark"] #payment-history .payment-company { color:#e3dce6; }
    html[data-client-theme="dark"] #payment-history .payment-code > span { color:#a9ca68; }
    html[data-client-theme="dark"] #payment-history .method-qr { color:#78c3cb; }
    html[data-client-theme="dark"] #payment-history .method-other,
    html[data-client-theme="dark"] #payment-history .status-completado { color:#a9ca68; }
    html[data-client-theme="dark"] #payment-history .status-pendiente { color:#e1bf5a; }
    html[data-client-theme="dark"] #payment-history .status-cancelado { color:#e58b8b; }
    html[data-client-theme="dark"] #payment-history .valid-until { color:#a9ca68; }
    html[data-client-theme="dark"] #payment-history .validity-pending { color:#e1bf5a; }
    html[data-client-theme="dark"] #payment-history .view-action { color:#d0a8e2; }
    html[data-client-theme="dark"] #payment-history .download-action { color:#78c3cb; }
    html[data-client-theme="dark"] #payment-history .pending-code-action,
    html[data-client-theme="dark"] #payment-history .pending-code-wait { color:#e1bf5a; }
    html[data-client-theme="dark"] #payment-history .delete-order-action { border-color:#e58b8b; background:#1e1b21; color:#e58b8b; }
    html[data-client-theme="dark"] #payment-history .renew-plan-action { border-color:#a9ca68; background:#1e1b21; color:#a9ca68; }
    html[data-client-theme="dark"] .payment-modal-dialog, html[data-client-theme="dark"] .payment-modal-body { background:#1e1b21; color:#e9e5eb; }
    html[data-client-theme="dark"] .payment-modal-footer { border-color:#413a45; background:#29252c; }
    html[data-client-theme="dark"] .pending-code-card { border-color:#4a434e; }
    html[data-client-theme="dark"] .pending-code-card p { color:#b4abb8; }
    html[data-client-theme="dark"] .pending-code-value { color:#f1edf3; }
    html[data-client-theme="dark"] .pending-code-contact a { border-color:#4a434e; color:#d0c8d3; }
    html[data-client-theme="dark"] .pending-code-contact a:hover { border-color:#7DA533; color:#b4d27a; }

    @media (max-width:720px) {
        #payment-history .payments-hero { min-height:190px; padding:26px 20px; }
        #payment-history .payments-hero-side { margin-left:auto; }
        #payment-history .login-mosaic { display:none; }
        #payment-history .payments-content { margin:20px 16px; }
        #payment-history .payments-toolbar { align-items:flex-start; flex-wrap:wrap; }
        #payment-history .buy-plan-button { width:100%; margin-top:5px; margin-left:0; }
        #payment-history .payments-filters { grid-template-columns:1fr 1fr; }
        #payment-history .filter-actions { grid-column:1 / -1; }
        #payment-history .filter-actions button { flex:1; }
        .payment-modal { padding:10px; }
        .payment-modal-dialog { max-height:calc(100vh - 20px); }
        .payment-modal-header, .payment-modal-body, .payment-modal-footer { padding-left:18px; padding-right:18px; }
    }
    @media (max-width:500px) {
        #payment-history .payments-hero { align-items:flex-start; flex-direction:column; gap:20px; }
        #payment-history .payments-hero-side { width:100%; margin-left:0; }
        #payment-history .payments-total { width:100%; }
        #payment-history .payments-filters { grid-template-columns:1fr; }
        #payment-history .filter-actions { grid-column:auto; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') cerrarModal();
        });

        const closeFilterPopups = except => {
            document.querySelectorAll('[data-custom-select], [data-date-picker]').forEach(control => {
                if (control !== except) {
                    control.classList.remove('is-open');
                    control.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.querySelectorAll('[data-custom-select]').forEach(select => {
            const input = select.querySelector('input[type="hidden"]');
            const trigger = select.querySelector('.custom-filter-trigger');
            const options = [...select.querySelectorAll('.custom-filter-menu button')];
            options.forEach(option => option.classList.toggle('is-selected', option.dataset.value === input.value));

            trigger.addEventListener('click', event => {
                event.stopPropagation();
                const willOpen = !select.classList.contains('is-open');
                closeFilterPopups(select);
                select.classList.toggle('is-open', willOpen);
                trigger.setAttribute('aria-expanded', String(willOpen));
            });

            options.forEach(option => option.addEventListener('click', () => {
                input.value = option.dataset.value;
                trigger.querySelector('span').textContent = option.textContent.trim();
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                select.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }));
        });

        document.querySelectorAll('[data-date-picker]').forEach(picker => {
            const input = picker.querySelector('input[type="hidden"]');
            const trigger = picker.querySelector('.custom-date-trigger');
            const calendar = picker.querySelector('.custom-calendar');
            const initialDate = input.value ? new Date(`${input.value}T00:00:00`) : new Date();
            let viewYear = initialDate.getFullYear();
            let viewMonth = initialDate.getMonth();

            const toIso = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            const toDisplay = date => new Intl.DateTimeFormat('es-BO', { day:'2-digit', month:'2-digit', year:'numeric' }).format(date);

            const renderCalendar = () => {
                const firstDay = new Date(viewYear, viewMonth, 1);
                const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
                const leadingSpaces = (firstDay.getDay() + 6) % 7;
                const monthTitle = new Intl.DateTimeFormat('es-BO', { month:'long', year:'numeric' }).format(firstDay);
                const todayIso = toIso(new Date());
                let days = '<span></span>'.repeat(leadingSpaces);

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(viewYear, viewMonth, day);
                    const iso = toIso(date);
                    const classes = [iso === input.value ? 'is-selected' : '', iso === todayIso ? 'is-today' : ''].filter(Boolean).join(' ');
                    days += `<button type="button" class="${classes}" data-date="${iso}">${day}</button>`;
                }

                calendar.innerHTML = `
                    <div class="calendar-head">
                        <button type="button" data-calendar-prev aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button>
                        <strong>${monthTitle}</strong>
                        <button type="button" data-calendar-next aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="calendar-weekdays"><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span></div>
                    <div class="calendar-days">${days}</div>
                    <button type="button" class="calendar-clear">Limpiar fecha</button>`;

                calendar.querySelector('[data-calendar-prev]').addEventListener('click', () => {
                    viewMonth--;
                    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
                    renderCalendar();
                });
                calendar.querySelector('[data-calendar-next]').addEventListener('click', () => {
                    viewMonth++;
                    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
                    renderCalendar();
                });
                calendar.querySelectorAll('[data-date]').forEach(dayButton => dayButton.addEventListener('click', () => {
                    input.value = dayButton.dataset.date;
                    trigger.querySelector('span').textContent = toDisplay(new Date(`${input.value}T00:00:00`));
                    picker.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                }));
                calendar.querySelector('.calendar-clear').addEventListener('click', () => {
                    input.value = '';
                    trigger.querySelector('span').textContent = 'Seleccionar fecha';
                    picker.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                });
            };

            renderCalendar();
            trigger.addEventListener('click', event => {
                event.stopPropagation();
                const willOpen = !picker.classList.contains('is-open');
                closeFilterPopups(picker);
                picker.classList.toggle('is-open', willOpen);
                trigger.setAttribute('aria-expanded', String(willOpen));
            });
            calendar.addEventListener('click', event => event.stopPropagation());
        });

        document.addEventListener('click', () => closeFilterPopups());
    });

    function verComprobante(pagoId) {
        const modal = document.getElementById('modalComprobante');
        const contentPanel = document.getElementById('modalContentPanel');
        const contenidoComprobante = document.getElementById('contenidoComprobante');
        document.getElementById('modal-title').innerHTML = '<i class="fas fa-file-invoice-dollar"></i> Comprobante de pago';
        modal.classList.remove('hidden');
        setTimeout(() => {
            contentPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            contentPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        }, 10);
        contenidoComprobante.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div></div>';
        fetch(`/clientes/pagos/comprobante/${pagoId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => contenidoComprobante.innerHTML = data.html)
            .catch(error => {
                console.error('Error al cargar el comprobante:', error);
                contenidoComprobante.innerHTML = '<div class="bg-red-50 border-l-4 border-red-400 p-4 my-4"><p class="text-sm text-red-700"><i class="fas fa-exclamation-circle mr-2"></i><strong>Error:</strong> No se pudo cargar el comprobante. Inténtalo nuevamente más tarde.</p></div>';
            });
    }

    function verCodigoPendiente(codigo, downloadUrl) {
        const modal = document.getElementById('modalComprobante');
        const contentPanel = document.getElementById('modalContentPanel');
        const contenido = document.getElementById('contenidoComprobante');
        document.getElementById('modal-title').innerHTML = '<i class="fas fa-barcode"></i> Código de pago pendiente';

        const card = document.createElement('div');
        card.className = 'pending-code-card';
        card.innerHTML = '<i class="fas fa-barcode"></i><p>Presenta este código para completar el pago. Estará disponible mientras el pedido continúe pendiente.</p>';

        const code = document.createElement('strong');
        code.className = 'pending-code-value';
        code.textContent = codigo;
        card.appendChild(code);

        const contact = document.createElement('div');
        contact.className = 'pending-code-contact';
        contact.innerHTML = `
            <a href="https://www.bing.com/maps/search?q=Real+Plaza+Hotel+Av.+Arce+2177+La+Paz+Bolivia" target="_blank" rel="noopener noreferrer">
                <i class="fas fa-location-dot"></i><span>Real Plaza Hotel, Av. Arce #2177, frente a Plaza Bolivia</span>
            </a>
            <a href="https://wa.me/59179561365" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-whatsapp"></i><span>WhatsApp: +591 79561365</span>
            </a>`;
        card.appendChild(contact);

        const download = document.createElement('a');
        download.className = 'pending-code-download';
        download.href = downloadUrl;
        download.innerHTML = '<i class="fas fa-download"></i> Descargar código';
        card.appendChild(download);

        contenido.replaceChildren(card);
        modal.classList.remove('hidden');
        setTimeout(() => {
            contentPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            contentPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        }, 10);
    }

    function descargarComprobante(pagoId) {
        window.location.href = `/clientes/pagos/descargar/${pagoId}`;
    }

    function cerrarModal() {
        const modal = document.getElementById('modalComprobante');
        const contentPanel = document.getElementById('modalContentPanel');
        if (!modal || modal.classList.contains('hidden')) return;
        contentPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        setTimeout(() => modal.classList.add('hidden'), 250);
    }
</script>
@endsection
