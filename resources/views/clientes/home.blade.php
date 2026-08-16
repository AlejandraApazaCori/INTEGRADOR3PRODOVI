<html>
    <head>
        <title>PLANES | PRODOVI Digital</title>
        <link href="https://fonts.googleapis.com/css2?family=Varela+Round:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Rowdies:wght@400;600;700&display=swap" rel="stylesheet">
        @include('a.css.cliente.planes')
        
    </head>
    <body>
        @include('componentes.navbar2')
        
        <div class="main-container">
            @auth
                <div class="welcome-section">
                    <div class="welcome-card">
                        @if($tieneSuscripcionActiva)
                            <div class="subscription-alert success">
                                <i class="fas fa-check-circle"></i>
                                ¡Ya tienes una suscripción activa!
                                <a href="{{ route('clientes.dashboard') }}" class="alert-link">Ir al Dashboard</a>
                            </div>
                        @elseif($tieneSuscripcionPendiente && $suscripcionPendiente)
                            <div class="subscription-alert warning">
                                <div class="alert-content">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <strong>Tienes una suscripción pendiente</strong>
                                        <p>Plan: {{ $suscripcionPendiente->plan->nombre }} - 
                                           {{ number_format($suscripcionPendiente->pagos->first()->monto) }} 
                                           {{ $suscripcionPendiente->pagos->first()->moneda == 'BS' ? 'Bs' : '$' }}</p>
                                        @if($suscripcionPendiente->metodo_pago == 'fisico' && $suscripcionPendiente->pagos->first()->codigoPago)
                                            <p class="code-info">
                                                <i class="fas fa-barcode"></i> Código: 
                                                <span class="code">{{ $suscripcionPendiente->pagos->first()->codigoPago->codigo }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('clientes.pago.estado') }}" class="alert-link">
                                    Ver detalles <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        @endif
                        
                        <h1 class="welcome-title">¡Bienvenido a PRODOVI Digital, {{ auth()->user()->name }}!</h1>
                        <p class="welcome-subtitle">
                            @if($tieneSuscripcionActiva)
                                <span class="highlight">Tu plan actual está activo.</span> Puedes explorar opciones adicionales o renovar cuando lo necesites.
                            @else
                                Tu transformación digital comienza aquí. <span class="highlight">Selecciona el plan perfecto</span> y descubre cómo revolucionaremos tu presencia online.
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="welcome-section">
                    <div class="welcome-card">
                        <h1 class="welcome-title">¡Bienvenido a PRODOVI Digital!</h1>
                        <p class="welcome-subtitle">
                            Descubre el poder del marketing digital profesional. <span class="highlight">Nuestros planes estratégicos</span> están diseñados para elevar tu marca.
                        </p>
                        <div class="auth-buttons">
                            <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>
                            <a href="{{ route('login') }}#show-register" class="btn-register">Registrarse</a>
                        </div>
                    </div>
                </div>
            @endif
            
            @unless($tieneSuscripcionActiva ?? false)
                <div class="plans-section">
                    <h2 class="section-title">Nuestros Planes Estratégicos</h2>
                    <div class="plans-container">
                        @forelse($planes as $plan)
                            <div class="plan {{ strtolower(str_replace(' ', '-', $plan->nombre)) }}">
                                <div class="plan-header">
                                    <div class="plan-title">{{ $plan->nombre }}</div>
                                    <div class="plan-subtitle">{{ $plan->subtitulo }}</div>
                                    <div class="plan-price">
                                       {{ number_format($plan->precio) }} {{ $plan->moneda == 'BS' ? 'Bs' : '$' }}/{{ $plan->periodo_facturacion }}
                                    </div>
                                </div>
                                <div class="plan-features">
                                    @foreach($plan->planCaracteristicas as $pc)
                                        <div class="feature">
                                           
                                            {{ $pc->caracteristica->nombre }}
                                            @if($pc->frecuencia)
                                                - {{ $pc->frecuencia }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button class="choose-btn" onclick="window.location.href='{{ route('clientes.pago', strtolower(str_replace(' ', '-', $plan->nombre))) }}'">
                                    {{ auth()->check() ? 'Seleccionar Plan' : 'Registrarse y Elegir' }}
                                </button>
                            </div>
                        @empty
                            <div class="no-plans">
                                <i class="fas fa-info-circle"></i>
                                Actualmente no hay planes disponibles. Por favor, vuelve más tarde.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endunless
            
        </div>
        
        @include('componentes.footer')
    </body>
</html>
<style>
    /* Para home.blade.php */
.subscription-alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.subscription-alert.success {
    background-color: #e6fffa;
    color: #065f46;
}

.subscription-alert.warning {
    background-color: #fffbeb;
    color: #92400e;
}

.alert-link {
    margin-left: auto;
    color: inherit;
    text-decoration: underline;
    font-weight: 600;
}
.code-info {
    font-size: 13px;
    margin-top: 2px;
}
.code {
    font-family: monospace;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
}

/* Para dashboard.blade.php */
.dashboard-container {
    display: flex;
    min-height: calc(100vh - 80px);
}

.sidebar {
    width: 280px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    padding: 20px;
}

.main-content {
    flex: 1;
    padding: 30px;
    background: #f9fafb;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.avatar {
    font-size: 40px;
    color: #4f46e5;
}

.subscription-status {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    display: inline-block;
}

.subscription-status.active {
    background: #e6fffa;
    color: #065f46;
}

.dashboard-nav {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.nav-item {
    padding: 12px 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
    text-decoration: none;
}

.nav-item:hover, .nav-item.active {
    background: #f1f5f9;
    color: #4f46e5;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.card-icon.blue { background: #3b82f6; }
.card-icon.green { background: #10b981; }
.card-icon.orange { background: #f59e0b; }
</style>
