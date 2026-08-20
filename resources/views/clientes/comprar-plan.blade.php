@extends('layouts.app2')

@section('title', 'Comprar otro plan')

@section('content')
<div id="buy-plan-page" class="min-h-screen">
    <header class="plans-hero">
        <div class="plans-hero-content">
            <span class="plans-kicker">Haz crecer otra marca</span>
            <h1>Compra otro <span>plan</span></h1>
            <p>Elige una nueva suscripción para registrar otra empresa y gestionar cada negocio de manera independiente.</p>
        </div>
        <div class="plans-hero-side">
            <div class="plans-count"><small>Opciones disponibles</small><strong>{{ $planes->count() }}</strong></div>
            <div class="login-mosaic" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
        </div>
    </header>

    <main class="plans-content">
        @if($pagoPendiente)
            <section class="pending-payment-notice" aria-label="Pago pendiente">
                <div class="pending-payment-icon"><i class="fas fa-clock"></i></div>
                <div class="pending-payment-copy">
                    <span>Pago físico pendiente</span>
                    <h2>Tienes una solicitud esperando confirmación</h2>
                    <p>
                        Plan <strong>{{ $pagoPendiente->plan->nombre ?? 'seleccionado' }}</strong>
                        por <strong>{{ number_format($pagoPendiente->monto, 2, ',', '.') }} {{ strtoupper($pagoPendiente->moneda) === 'BS' ? 'Bs.' : $pagoPendiente->moneda }}</strong>.
                        Presenta tu código para completar el pago.
                    </p>
                </div>
                <div class="pending-payment-data">
                    <span class="pending-status"><i class="fas fa-clock"></i> Pendiente</span>
                    @if($pagoPendiente->codigoPago)
                        <small>Código de pago</small>
                        <strong>{{ $pagoPendiente->codigoPago->codigo }}</strong>
                        <a href="{{ route('pago.fisico.codigo.pdf', $pagoPendiente) }}"><i class="fas fa-download"></i> Descargar código</a>
                    @else
                        <span class="pending-code-loading"><i class="fas fa-spinner fa-spin"></i> Generando código</span>
                    @endif
                    <a class="pending-history-link" href="{{ route('clientes.historial.pagos', ['estado' => 'pendiente']) }}">Ver en historial</a>
                </div>
                <form class="pending-delete-form" action="{{ route('clientes.pagos.pendientes.eliminar', $pagoPendiente) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta solicitud pendiente? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pending-delete-button" aria-label="Eliminar solicitud" title="Eliminar solicitud"><i class="fas fa-trash-can" aria-hidden="true"></i></button>
                </form>
            </section>
        @endif

        <div class="plans-heading">
            <div>
                <span>Planes PRODOVI</span>
                <h2>Encuentra el impulso ideal para tu próximo negocio.</h2>
                <p>Cada plan adquirido permite registrar una empresa diferente y tendrá su propia campaña y vigencia.</p>
            </div>
            <a href="{{ route('clientes.historial.pagos') }}"><i class="fas fa-clock-rotate-left"></i> Ver historial de pagos</a>
        </div>

        <section class="plans-grid" aria-label="Planes disponibles">
            @forelse($planes as $plan)
                <article class="purchase-plan-card {{ $loop->iteration === 2 ? 'is-featured' : '' }}">
                    <div class="plan-summary">
                        <div class="plan-symbol-row">
                            <span class="plan-symbol" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                            @if($loop->iteration === 2)<span class="featured-badge"><i class="fas fa-star"></i> Más elegido</span>@endif
                        </div>

                        <span class="plan-index">Plan {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $plan->nombre }}</h3>
                        <p class="plan-subtitle">{{ $plan->subtitulo ?: $plan->descripcion }}</p>

                        <div class="plan-price-row">
                            <strong>{{ number_format($plan->precio, 0, ',', '.') }} <small>{{ strtoupper($plan->moneda) === 'BS' ? 'Bs.' : '$' }}</small></strong>
                            <span>/ {{ $plan->periodo_facturacion }}</span>
                        </div>

                        <p class="plan-helper"><i class="fas fa-building"></i> Incluye el registro de una nueva empresa.</p>
                        <a href="{{ route('clientes.pago', ['plan' => \Illuminate\Support\Str::slug($plan->nombre), 'origen' => 'comprar-plan']) }}" class="choose-plan-button">
                            Elegir este plan <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="plan-benefits">
                        <span class="benefits-title">Este plan incluye</span>
                        <div class="plan-features">
                            @forelse($plan->planCaracteristicas as $planCaracteristica)
                                <div class="plan-feature">
                                    <i class="fas fa-check"></i>
                                    <span>
                                        {{ $planCaracteristica->caracteristica->nombre ?? 'Beneficio del plan' }}
                                        @if($planCaracteristica->cantidad || $planCaracteristica->frecuencia)
                                            <small>
                                                @if($planCaracteristica->cantidad){{ $planCaracteristica->cantidad }}@endif
                                                @if($planCaracteristica->cantidad && $planCaracteristica->frecuencia) · @endif
                                                @if($planCaracteristica->frecuencia){{ $planCaracteristica->frecuencia }}@endif
                                            </small>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <div class="plan-feature"><i class="fas fa-check"></i><span>Acompañamiento personalizado</span></div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <div class="plans-empty"><i class="fas fa-box-open"></i><h3>Estamos preparando nuevos planes</h3><p>Vuelve pronto para conocer las próximas opciones.</p></div>
            @endforelse
        </section>

        <aside class="purchase-note">
            <i class="fas fa-circle-info"></i>
            <p><strong>Una suscripción, una empresa.</strong> Después de confirmar el pago podrás registrar los datos del nuevo negocio y comenzar su configuración.</p>
        </aside>
    </main>
</div>

<style>
    #buy-plan-page { --purple:#5B2B76; --orange:#EF6C22; --green:#7DA533; --turquoise:#117E8C; background:#fff; color:#17131d; padding-bottom:45px; }
    #buy-plan-page .plans-hero { min-height:150px; display:flex; align-items:center; justify-content:space-between; gap:32px; padding:28px 32px; background:#242426; color:#fff; }
    #buy-plan-page .plans-hero-content { max-width:730px; }
    #buy-plan-page .plans-kicker { display:block; margin-bottom:10px; color:var(--green); font-size:.68rem; font-weight:900; letter-spacing:.13em; text-transform:uppercase; }
    #buy-plan-page .plans-hero h1 { margin:0; font-size:clamp(1.65rem,3vw,2.35rem); font-weight:800; line-height:1.08; letter-spacing:-.035em; }
    #buy-plan-page .plans-hero h1 span { color:var(--green); }
    #buy-plan-page .plans-hero p { max-width:650px; margin-top:11px; color:#aaa5ad; font-size:.86rem; line-height:1.55; }
    #buy-plan-page .plans-hero-side { display:flex; align-items:center; gap:26px; }
    #buy-plan-page .plans-count { min-width:126px; padding:13px 16px; border-left:4px solid var(--green); background:#303033; }
    #buy-plan-page .plans-count small, #buy-plan-page .plans-count strong { display:block; }
    #buy-plan-page .plans-count small { color:#aaa5ad; font-size:.63rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    #buy-plan-page .plans-count strong { margin-top:4px; font-size:1.55rem; line-height:1; }
    #buy-plan-page .login-mosaic { width:144px; height:96px; display:grid; flex:0 0 auto; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(2,1fr); }
    #buy-plan-page .login-mosaic span:nth-child(1) { background:var(--orange); border-radius:100% 0 0 0; }
    #buy-plan-page .login-mosaic span:nth-child(2) { background:#F5A900; border-radius:0 0 0 100%; }
    #buy-plan-page .login-mosaic span:nth-child(3) { background:var(--purple); border-radius:100% 0 100% 0; }
    #buy-plan-page .login-mosaic span:nth-child(4) { background:var(--turquoise); border-radius:0 100% 0 100%; }
    #buy-plan-page .login-mosaic span:nth-child(5) { background:var(--green); border-radius:50%; }
    #buy-plan-page .login-mosaic span:nth-child(6) { border:12px solid #607078; border-top-color:transparent; border-left-color:transparent; border-radius:50%; transform:rotate(45deg); }
    #buy-plan-page .plans-content { margin:34px 32px 0; }
    #buy-plan-page .pending-payment-notice { position:relative; display:grid; grid-template-columns:auto minmax(0,1fr) minmax(220px,auto); align-items:stretch; gap:0; overflow:hidden; margin-bottom:30px; border:1px solid #ded7e1; border-top:5px solid var(--turquoise); border-radius:4px; background:#fff; box-shadow:0 15px 36px #e5dfe7; }
    #buy-plan-page .pending-payment-icon { width:54px; height:54px; display:grid; place-items:center; align-self:center; margin:22px 0 22px 22px; border-radius:3px; background:#242426; color:#f5a900; font-size:1.2rem; box-shadow:inset 0 -4px 0 var(--orange); }
    #buy-plan-page .pending-payment-copy { align-self:center; padding:22px 24px 22px 18px; }
    #buy-plan-page .pending-payment-copy > span { display:block; color:var(--turquoise); font-size:.65rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    #buy-plan-page .pending-payment-copy h2 { margin:6px 0 0; color:#302834; font-size:clamp(1.05rem,2vw,1.28rem); font-weight:900; letter-spacing:-.02em; }
    #buy-plan-page .pending-payment-copy p { max-width:660px; margin:7px 0 0; color:#756a7a; font-size:.78rem; line-height:1.55; }
    #buy-plan-page .pending-payment-copy p strong { color:#514557; }
    #buy-plan-page .pending-payment-data { min-width:220px; display:flex; flex-direction:column; align-items:flex-start; justify-content:center; gap:6px; padding:42px 22px 18px; border-left:1px solid #e5dfe7; background:#f7f5f8; }
    #buy-plan-page .pending-status { display:inline-flex; align-items:center; gap:6px; margin-bottom:3px; padding:5px 8px; border-radius:2px; background:#fff0c7; color:#765700; font-size:.62rem; font-weight:900; letter-spacing:.07em; text-transform:uppercase; }
    #buy-plan-page .pending-payment-data small { color:#887d8c; font-size:.6rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    #buy-plan-page .pending-payment-data > strong { color:#302834; font-size:1.05rem; font-weight:900; letter-spacing:.08em; }
    #buy-plan-page .pending-payment-data > a { display:inline-flex; align-items:center; gap:6px; color:var(--turquoise); font-size:.7rem; font-weight:900; text-decoration:none; transition:.2s ease; }
    #buy-plan-page .pending-payment-data > a:not(.pending-history-link) { margin-top:2px; padding:7px 10px; border-radius:3px; background:var(--turquoise); color:#fff; }
    #buy-plan-page .pending-payment-data > a:not(.pending-history-link):hover { filter:brightness(.9); transform:translateY(-1px); }
    #buy-plan-page .pending-payment-data .pending-history-link { margin-top:2px; color:var(--purple); }
    #buy-plan-page .pending-payment-data .pending-history-link:hover { text-decoration:underline; }
    #buy-plan-page .pending-delete-form { position:absolute; z-index:2; top:12px; right:12px; margin:0; }
    #buy-plan-page .pending-delete-button { width:32px; height:32px; display:grid; place-items:center; padding:0; border:1px solid #dfb4b4; border-radius:50%; background:#fff; color:#b63b3b; font-size:.76rem; cursor:pointer; transition:.2s ease; }
    #buy-plan-page .pending-delete-button:hover { border-color:#b63b3b; background:#b63b3b; color:#fff; transform:translateY(-1px); }
    #buy-plan-page .pending-delete-button:focus-visible { outline:2px solid #b63b3b; outline-offset:4px; }
    #buy-plan-page .pending-code-loading { display:inline-flex; align-items:center; gap:6px; color:var(--turquoise); font-size:.72rem; font-weight:800; }
    #buy-plan-page .plans-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:30px; margin-bottom:24px; padding-bottom:18px; border-bottom:1px solid #ded7e1; }
    #buy-plan-page .plans-heading > div > span { color:var(--green); font-size:.67rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    #buy-plan-page .plans-heading h2 { max-width:680px; margin:7px 0 0; color:#302834; font-size:clamp(1.35rem,2.5vw,2rem); font-weight:900; letter-spacing:-.035em; }
    #buy-plan-page .plans-heading p { max-width:690px; margin-top:8px; color:#756a7a; font-size:.82rem; line-height:1.6; }
    #buy-plan-page .plans-heading > a { display:inline-flex; align-items:center; gap:8px; flex:0 0 auto; padding:10px 13px; border:1px solid #d8cfdc; border-radius:3px; color:var(--purple); font-size:.73rem; font-weight:800; text-decoration:none; }
    #buy-plan-page .plans-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; align-items:stretch; }
    #buy-plan-page .purchase-plan-card { --plan-color:var(--orange); min-width:0; display:flex; flex-direction:column; overflow:hidden; border:1px solid #ded7e1; border-top:5px solid var(--plan-color); border-radius:4px; background:#fff; box-shadow:0 15px 36px #e5dfe7; transition:.25s ease; }
    #buy-plan-page .purchase-plan-card:nth-child(2) { --plan-color:var(--turquoise); }
    #buy-plan-page .purchase-plan-card:nth-child(3n) { --plan-color:var(--purple); }
    #buy-plan-page .purchase-plan-card:hover { transform:translateY(-6px); box-shadow:0 22px 44px #d8d0db; }
    #buy-plan-page .purchase-plan-card.is-featured { border-color:var(--plan-color); }
    #buy-plan-page .plan-summary { padding:25px 24px 22px; }
    #buy-plan-page .plan-symbol-row { display:flex; align-items:center; justify-content:space-between; min-height:42px; gap:12px; }
    #buy-plan-page .plan-symbol { width:42px; height:42px; display:grid; grid-template-columns:1fr 1fr; grid-template-rows:1fr 1fr; transform:rotate(45deg); }
    #buy-plan-page .plan-symbol i:nth-child(1) { background:var(--plan-color); border-radius:100% 0 0; }
    #buy-plan-page .plan-symbol i:nth-child(2) { background:#F5A900; border-radius:0 100% 0 0; }
    #buy-plan-page .plan-symbol i:nth-child(3) { background:var(--turquoise); border-radius:0 0 0 100%; }
    #buy-plan-page .plan-symbol i:nth-child(4) { background:var(--green); border-radius:0 0 100%; }
    #buy-plan-page .featured-badge { padding:6px 9px; border-radius:2px; background:var(--turquoise); color:#fff; font-size:.65rem; font-weight:900; text-transform:uppercase; }
    #buy-plan-page .plan-index { display:block; margin-top:22px; color:var(--plan-color); font-size:.65rem; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }
    #buy-plan-page .plan-summary h3 { margin:7px 0 0; color:#302834; font-size:1.55rem; font-weight:900; letter-spacing:-.035em; }
    #buy-plan-page .plan-subtitle { min-height:42px; margin-top:8px; color:#756a7a; font-size:.8rem; line-height:1.55; }
    #buy-plan-page .plan-price-row { display:flex; align-items:flex-end; gap:8px; margin-top:21px; }
    #buy-plan-page .plan-price-row strong { color:#302834; font-size:2rem; font-weight:900; line-height:1; }
    #buy-plan-page .plan-price-row strong small { font-size:.85rem; }
    #buy-plan-page .plan-price-row > span { color:#887d8c; font-size:.72rem; }
    #buy-plan-page .plan-helper { margin-top:12px; color:#756a7a; font-size:.72rem; }
    #buy-plan-page .plan-helper i { margin-right:6px; color:var(--green); }
    #buy-plan-page .choose-plan-button { min-height:46px; display:flex; align-items:center; justify-content:center; gap:9px; margin-top:20px; border:2px solid var(--plan-color); border-radius:3px; background:var(--plan-color); color:#fff; font-size:.8rem; font-weight:900; text-decoration:none; transition:.2s ease; }
    #buy-plan-page .choose-plan-button:hover { filter:brightness(.9); transform:translateY(-2px); }
    #buy-plan-page .plan-benefits { flex:1; padding:21px 24px 24px; border-top:1px solid #e5dfe7; background:#f7f5f8; }
    #buy-plan-page .benefits-title { display:block; margin-bottom:13px; color:#514557; font-size:.68rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    #buy-plan-page .plan-features { display:grid; gap:10px; }
    #buy-plan-page .plan-feature { display:grid; grid-template-columns:20px minmax(0,1fr); gap:8px; align-items:start; color:#5f5563; font-size:.76rem; line-height:1.45; }
    #buy-plan-page .plan-feature > i { width:18px; height:18px; display:grid; place-items:center; border-radius:50%; background:var(--green); color:#fff; font-size:.55rem; }
    #buy-plan-page .plan-feature small { display:block; margin-top:2px; color:#8b818f; }
    #buy-plan-page .plans-empty { grid-column:1/-1; padding:60px 20px; border:1px solid #ded7e1; text-align:center; }
    #buy-plan-page .plans-empty i { color:var(--turquoise); font-size:2rem; }
    #buy-plan-page .plans-empty h3 { margin-top:12px; font-weight:900; }
    #buy-plan-page .plans-empty p { margin-top:5px; color:#756a7a; }
    #buy-plan-page .purchase-note { display:flex; align-items:flex-start; gap:11px; margin-top:22px; padding:15px 17px; border-left:4px solid var(--green); background:#f7f5f8; color:#625767; font-size:.78rem; line-height:1.55; }
    #buy-plan-page .purchase-note > i { margin-top:3px; color:var(--green); }

    html[data-client-theme="dark"] #buy-plan-page { background:#141216; color:#e9e5eb; }
    html[data-client-theme="dark"] #buy-plan-page .plans-heading { border-color:#3b3540; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-notice { border-color:#403944; border-top-color:var(--turquoise); background:#1e1b21; box-shadow:0 15px 36px #0d0b0e; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-data { border-color:#413a45; background:#29252c; }
    html[data-client-theme="dark"] #buy-plan-page .pending-status { background:#4a3e1d; color:#f7d36d; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-copy h2,
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-data > strong { color:#f1edf3; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-copy p,
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-data small { color:#c6bca7; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-copy p strong { color:#e0d9e3; }
    html[data-client-theme="dark"] #buy-plan-page .pending-payment-data .pending-history-link { color:#d0a8e2; }
    html[data-client-theme="dark"] #buy-plan-page .pending-delete-button { border-color:#754d52; background:#29252c; color:#e58b8b; }
    html[data-client-theme="dark"] #buy-plan-page .pending-delete-button:hover { border-color:#b63b3b; background:#b63b3b; color:#fff; }
    html[data-client-theme="dark"] #buy-plan-page .plans-heading h2,
    html[data-client-theme="dark"] #buy-plan-page .plan-summary h3,
    html[data-client-theme="dark"] #buy-plan-page .plan-price-row strong { color:#f1edf3; }
    html[data-client-theme="dark"] #buy-plan-page .plans-heading p,
    html[data-client-theme="dark"] #buy-plan-page .plan-subtitle,
    html[data-client-theme="dark"] #buy-plan-page .plan-helper,
    html[data-client-theme="dark"] #buy-plan-page .plan-feature { color:#b4abb8; }
    html[data-client-theme="dark"] #buy-plan-page .plans-heading > a { border-color:#4a434e; color:#d0a8e2; }
    html[data-client-theme="dark"] #buy-plan-page .purchase-plan-card { border-color:#403944; background:#1e1b21; box-shadow:0 15px 36px #0d0b0e; }
    html[data-client-theme="dark"] #buy-plan-page .purchase-plan-card.is-featured { border-color:var(--plan-color); }
    html[data-client-theme="dark"] #buy-plan-page .plan-benefits,
    html[data-client-theme="dark"] #buy-plan-page .purchase-note { border-color:#413a45; background:#29252c; color:#c5bdc8; }
    html[data-client-theme="dark"] #buy-plan-page .benefits-title { color:#ddd8df; }
    html[data-client-theme="dark"] #buy-plan-page .plan-feature small { color:#938a97; }

    @media (max-width:1050px) { #buy-plan-page .plans-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:720px) {
        #buy-plan-page .plans-hero { padding:26px 20px; }
        #buy-plan-page .login-mosaic { display:none; }
        #buy-plan-page .plans-content { margin:24px 16px 0; }
        #buy-plan-page .plans-heading { align-items:flex-start; flex-direction:column; }
        #buy-plan-page .plans-grid { grid-template-columns:1fr; }
        #buy-plan-page .pending-payment-notice { grid-template-columns:auto minmax(0,1fr); }
        #buy-plan-page .pending-payment-icon { align-self:start; margin:20px 0 20px 20px; }
        #buy-plan-page .pending-payment-copy { padding:20px 18px; }
        #buy-plan-page .pending-payment-data { grid-column:1/-1; width:100%; min-width:0; padding:17px 20px; border-top:1px solid #e5dfe7; border-left:0; }
    }
    @media (max-width:500px) {
        #buy-plan-page .plans-hero { align-items:flex-start; flex-direction:column; }
        #buy-plan-page .plans-hero-side, #buy-plan-page .plans-count { width:100%; }
        #buy-plan-page .pending-payment-notice { grid-template-columns:1fr; }
        #buy-plan-page .pending-payment-icon { width:46px; height:46px; margin:18px 18px 0; }
        #buy-plan-page .pending-payment-copy { padding:14px 18px 20px; }
    }
</style>
@endsection
