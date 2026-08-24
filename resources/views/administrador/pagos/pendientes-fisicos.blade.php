@extends('layouts.app')

@section('title', 'Pagos Pendientes Físicos')

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <div class="pending-payments-page">
        <div class="pending-payments-shell">
            @if(session('success'))
                <div class="pending-alert pending-alert-success flex items-center justify-between rounded-xl p-4">
                    <div class="flex items-center gap-3"><i class="fas fa-circle-check"></i><span>{{ session('success') }}</span></div>
                    <button type="button" onclick="this.parentElement.style.display='none'" aria-label="Cerrar"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="pending-alert pending-alert-error flex items-center justify-between rounded-xl p-4">
                    <div class="flex items-center gap-3"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>
                    <button type="button" onclick="this.parentElement.style.display='none'" aria-label="Cerrar"><i class="fas fa-times"></i></button>
                </div>
            @endif

            <nav class="pending-tabs" aria-label="Secciones de pagos">
                <a href="{{ route('administrador.pagos.index') }}" class="btn-action"><i class="fas fa-table-columns"></i>General</a>
                <a href="{{ route('administrador.pagos.analiticas') }}" class="btn-action"><i class="fas fa-chart-line"></i>Analíticas</a>
                <a href="{{ route('administrador.pagos.pendientes-fisicos') }}" class="btn-action is-active" aria-current="page"><i class="fas fa-receipt"></i>Pendientes físicos</a>
                <a href="{{ route('administrador.pagos.manual.crear') }}" class="btn-action pending-register-action"><i class="fas fa-plus-circle"></i>Registrar pago</a>
            </nav>

            <header class="pending-hero rp-banner">
                <div class="rp-banner-overlay"></div>
                <div class="pending-hero-content">
                    <div class="pending-hero-layout">
                        <div class="pending-hero-icon"><i class="fas fa-receipt"></i></div>
                        <div>
                            <h1>Pagos Pendientes Físicos</h1>
                            <p>Gestiona y aprueba los pagos pendientes de verificación</p>
                        </div>
                    </div>
                </div>
            </header>

            <section class="pending-filter-panel" aria-labelledby="pending-filter-title">
                <div class="pending-filter-heading">
                    <div class="pending-filter-icon"><i class="fas fa-magnifying-glass"></i></div>
                    <div>
                        <h2 id="pending-filter-title">Buscar pagos pendientes</h2>
                    </div>
                </div>
                <form action="{{ route('administrador.pagos.pendientes-fisicos') }}" method="GET" class="pending-filter-form">
                    <div class="pending-filter-field">
                        <label for="search"><i class="fas fa-magnifying-glass"></i> Cliente o código</label>
                        <input type="search" name="search" id="search" value="{{ request('search') }}" placeholder="Buscar por código o nombre de usuario...">
                    </div>
                    <div class="pending-filter-actions">
                        <a href="{{ route('administrador.pagos.pendientes-fisicos') }}"><i class="fas fa-rotate-left"></i>Limpiar</a>
                        <button type="submit"><i class="fas fa-magnifying-glass"></i>Buscar</button>
                    </div>
                </form>
            </section>

            <section class="pending-table-panel" aria-labelledby="pending-table-title">
                <div class="pending-table-heading">
                    <div class="pending-table-heading-copy">
                        <i class="fas fa-receipt"></i>
                        <span>
                            <h2 id="pending-table-title">Pagos pendientes</h2>
                            <p>Solicitudes físicas que requieren validación manual.</p>
                        </span>
                    </div>
                    <span>Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} registros</span>
                </div>

                <div class="pending-table-wrap">
                    <table class="pending-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Código</th>
                                <th>Plan</th>
                                <th>Monto</th>
                                <th>Fecha de generación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr>
                                    <td>
                                        <div class="pending-user">
                                            <span>{{ mb_strtoupper(mb_substr(optional($pago->usuario)->name ?? 'N', 0, 1)) }}</span>
                                            <strong>{{ optional($pago->usuario)->name ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td><span class="pending-code">{{ optional($pago->codigoPago)->codigo ?? 'N/A' }}</span></td>
                                    <td><span class="pending-plan">{{ optional($pago->plan)->nombre ?? 'N/A' }}</span></td>
                                    <td><strong>{{ number_format((float) $pago->monto, 2, ',', '.') }} {{ $pago->moneda }}</strong></td>
                                    <td>{{ optional($pago->created_at)->format('d/m/Y H:i') ?? 'N/A' }}</td>
                                    <td>
                                        <div class="pending-actions">
                                            <form action="{{ route('administrador.pagos.aprobar', $pago->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="pending-approve"><i class="fas fa-circle-check"></i>Aprobar</button>
                                            </form>
                                            <form action="{{ route('administrador.pagos.pendientes-fisicos.eliminar', $pago->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago pendiente y su código? La persona podrá generar uno nuevo.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="pending-delete"><i class="fas fa-trash"></i>Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="pending-empty"><i class="fas fa-circle-check"></i>No se encontraron pagos físicos pendientes de aprobación.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pagos->hasPages())
                    <div class="pending-pagination">
                        <span>Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} resultados</span>
                        <div>{{ $pagos->withQueryString()->onEachSide(1)->links() }}</div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <style>
        .rp-banner{position:relative;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}
        .rp-banner-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-repeat:no-repeat}
        .pending-payments-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.pending-payments-shell{position:relative;display:flex;flex-direction:column;width:100%}
        .pending-hero{order:1;width:100%;min-height:180px;overflow:hidden}.pending-hero-content{position:relative;z-index:1;min-height:180px;display:flex;align-items:center;padding:30px 48px}.pending-hero-layout{display:flex;align-items:center;gap:16px}.pending-hero-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.14);color:#fff;font-size:1.25rem;backdrop-filter:blur(5px)}.pending-hero h1{margin:0 0 4px;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.pending-hero h1::before{content:'Administración financiera';display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.pending-hero p{margin:0;color:#dbeafe;font-size:.74rem;font-weight:600}
        .pending-tabs{position:absolute;z-index:20;top:67px;right:48px;display:flex;justify-content:flex-end;gap:12px}.btn-action{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;white-space:nowrap;backdrop-filter:blur(4px);transition:.2s}.btn-action:hover,.btn-action.is-active{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}.pending-register-action{border-color:#ef6c22;background:#ef6c22;color:#fff}.pending-register-action:hover{color:#4f46e5}
        .pending-alert{order:3;width:calc(100% - 48px);margin:24px auto 0;border:1px solid;font-size:.76rem;font-weight:700}.pending-alert-success{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.pending-alert-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}.pending-alert button{border:0;background:transparent;color:inherit;cursor:pointer;opacity:.65}
        .pending-filter-panel{order:4;margin:24px 24px 0;padding:20px;border:1px solid #e1e3de;border-radius:16px;background:#f8f8f6;box-shadow:0 9px 22px rgba(55,60,52,.06)}.pending-filter-heading{display:flex;align-items:center;gap:12px;margin-bottom:18px}.pending-filter-icon{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border-radius:11px;background:#62685f;color:#fff;box-shadow:0 7px 15px rgba(55,60,52,.13)}.pending-filter-heading h2{margin:0;color:#343833;font-size:.95rem;font-weight:900}.pending-filter-form{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:end;gap:13px}.pending-filter-field label{display:block;margin-bottom:7px;color:#565d53;font-size:.64rem;font-weight:900}.pending-filter-field label i{width:15px;color:#737a70}.pending-filter-field input{width:100%;height:46px;padding:0 12px;border:1px solid #d9dcd6;border-radius:11px;background:#fff;color:#3f443d;font-size:.69rem;font-weight:700;outline:0}.pending-filter-field input:focus{border-color:#8a9186;box-shadow:0 0 0 3px rgba(98,104,95,.12)}.pending-filter-actions{display:flex;gap:8px}.pending-filter-actions a,.pending-filter-actions button{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 14px;border-radius:10px;font-size:.67rem;font-weight:900;text-decoration:none;transition:.18s}.pending-filter-actions a{border:1px solid #d7dad4;background:#fff;color:#62685f}.pending-filter-actions button{border:0;background:#62685f;color:#fff;cursor:pointer}.pending-filter-actions a:hover,.pending-filter-actions button:hover{transform:translateY(-2px);box-shadow:0 7px 14px rgba(55,60,52,.12)}
        .pending-table-panel{order:5;margin:24px 24px 0;border:0;background:transparent;box-shadow:none;overflow:visible}.pending-table-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:12px;padding:12px 15px;border:1px solid #e2ead5;border-radius:14px;background:#f9fbf5}.pending-table-heading-copy{display:flex;align-items:center;gap:11px}.pending-table-heading-copy>i{width:36px;height:36px;display:grid;place-items:center;flex:0 0 auto;border-radius:10px;background:#edf4e4;color:#7da533;font-size:.88rem}.pending-table-heading-copy>span{display:block}.pending-table-heading h2{margin:0;color:#31382b;font-size:.8rem;font-weight:900}.pending-table-heading p{margin:4px 0 0;color:#8a9380;font-size:.62rem}.pending-table-heading>span{color:#66705c;font-size:.72rem;font-weight:700}.pending-table-wrap{margin:0;border:1px solid #d8e3c7;border-radius:16px;background:#fff;box-shadow:0 9px 24px rgba(91,121,38,.12);overflow-x:auto}.pending-table{width:100%;min-width:900px;border-collapse:separate;border-spacing:0}.pending-table th{padding:16px 18px;border-right:1px solid rgba(255,255,255,.3);background:#7da533;color:#fff;text-align:left;font-size:.62rem;font-weight:900;letter-spacing:.05em;text-transform:uppercase}.pending-table th:last-child,.pending-table td:last-child{border-right:0}.pending-table td{padding:16px 18px;border-right:1px solid #d8e3c7;border-bottom:1px solid #dfe8d1;color:#50584a;font-size:.72rem;vertical-align:middle}.pending-table tbody tr:nth-child(odd) td{background:#fff}.pending-table tbody tr:nth-child(even) td{background:#f1f7e8}.pending-table tbody tr:hover td{background:#e6f0d8}.pending-table tbody tr:last-child td{border-bottom:0}.pending-user{display:flex;align-items:center;gap:11px}.pending-user>span{width:40px;height:40px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:linear-gradient(135deg,#7da533,#117e8c);color:#fff;font-size:.72rem;font-weight:900}.pending-user strong{color:#31382b}.pending-code,.pending-plan{display:inline-flex;padding:5px 9px;border:1px solid #cedfb4;border-radius:999px;background:#edf4e4;color:#638524;font-size:.6rem;font-weight:900}.pending-actions{display:flex;align-items:center;gap:6px}.pending-actions button{min-height:32px;display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid;border-radius:8px;font-size:.58rem;font-weight:900;cursor:pointer;transition:.18s}.pending-approve{border-color:#bbdfc2!important;background:#edf8ef;color:#26713a}.pending-delete{border-color:#f1c1c1!important;background:#fff1f1;color:#b43131}.pending-actions button:hover{transform:translateY(-1px);filter:brightness(.96)}.pending-empty{padding:42px!important;text-align:center;color:#66705c!important}.pending-empty i{margin-right:8px;color:#7da533}.pending-pagination{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:32px 0 0;padding:0;color:#66705c;font-size:.72rem}
        @media(max-width:1100px){.pending-hero-content{padding-right:20px}.pending-tabs{position:static;order:2;justify-content:center;margin:14px 24px 0}.pending-tabs .btn-action{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.pending-tabs .is-active{background:#4f46e5;color:#fff}}
        @media(max-width:700px){.pending-payments-page{padding-top:20px}.pending-hero,.pending-hero-content{min-height:205px}.pending-hero-content{padding:28px 20px}.pending-hero-layout{width:100%;justify-content:center;text-align:center}.pending-hero-icon{display:none}.pending-tabs{display:grid;grid-template-columns:1fr;margin-right:12px;margin-left:12px}.pending-filter-panel,.pending-table-panel{margin-right:12px;margin-left:12px}.pending-filter-form{grid-template-columns:1fr}.pending-filter-actions{flex-direction:column}.pending-filter-actions>*{width:100%}.pending-table-heading,.pending-pagination{align-items:flex-start;flex-direction:column}}
    </style>
@endsection
