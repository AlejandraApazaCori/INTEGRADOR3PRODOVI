<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Planes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @include('a.css.admin.planin')
</head>
<body>
    @include('componentes.navbar-admin')

    <div class="page-shell">
        <div class="page-width banner-wrap">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="rp-banner">
                <div class="rp-banner-overlay"></div>
                <div class="rp-banner-content">
                    <div class="banner-header">
                        <div class="banner-title-group">
                            <div class="banner-icon-box">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h1>Administrar Planes</h1>
                                <p>Gestiona los planes de suscripción con la misma vista destacada del módulo de empresas</p>
                            </div>
                        </div>
                        <a href="{{ route('administrador.planes.create') }}" class="banner-action">
                            <i class="fas fa-plus"></i>
                            Crear Nuevo Plan
                        </a>
                    </div>

                    <div class="banner-stats">
                        <div class="banner-stat-card stat-orange">
                            <div>
                                <p>Total Planes</p>
                                <strong>{{ $planes->count() }}</strong>
                            </div>
                            <span><i class="fas fa-layer-group"></i></span>
                        </div>
                        <div class="banner-stat-card stat-gold">
                            <div>
                                <p>Planes Activos</p>
                                <strong>{{ $planes->where('activo', true)->count() }}</strong>
                            </div>
                            <span><i class="fas fa-circle-check"></i></span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="page-width">
            <div class="container plans-container">
                @if($planes->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>No hay planes registrados</h3>
                        <p>¡Crea tu primer plan para comenzar!</p>
                        <a href="{{ route('administrador.planes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Crear Primer Plan
                        </a>
                    </div>
                @else
                    <div class="section-heading">
                        <div>
                            <h2>Planes disponibles</h2>
                            <p>Explora y administra cada plan desde sus tarjetas.</p>
                        </div>
                    </div>

                    <div class="plans-grid">
                        @foreach($planes as $plan)
                            <div class="plan-card">
                                <div class="plan-header">
                                    <div class="plan-badge">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h3 class="plan-title">{{ $plan->nombre }}</h3>
                                    <p class="plan-subtitle">{{ $plan->subtitulo }}</p>
                                    <div class="plan-price">
                                        <span class="price-amount">{{ number_format($plan->precio) }}</span>
                                        <span class="price-currency">{{ $plan->moneda == 'BS' ? 'Bs' : '$' }}</span>
                                        <span class="price-period">/{{ $plan->periodo_facturacion }}</span>
                                    </div>
                                </div>

                                <div class="plan-features">
                                    <h4><i class="fas fa-list-check"></i> Características</h4>
                                    <ul class="features-list">
                                        @foreach($plan->planCaracteristicas as $pc)
                                            <li class="feature-item">
                                                <i class="fas fa-check"></i>
                                                <span>{{ $pc->caracteristica->nombre }}</span>
                                                @if($pc->frecuencia)
                                                    <small class="feature-frequency">{{ $pc->frecuencia }}</small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="plan-actions">
                                    <a href="{{ route('administrador.planes.edit', $plan->id) }}" class="btn btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Editar
                                    </a>
                                    <form action="{{ route('administrador.planes.destroy', $plan->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-delete">
                                            <i class="fas fa-trash"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este plan? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-delete" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let formToSubmit = null;

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                formToSubmit = this;
                document.getElementById('deleteModal').style.display = 'block';
            });
        });

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            closeModal();
        });

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            formToSubmit = null;
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    </script>

    <style>
        body {
            background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);
        }

        .page-shell {
            min-height: 100vh;
            padding: 32px 20px 40px;
        }

        .page-width {
            max-width: 1400px;
            margin: 0 auto;
        }

        .banner-wrap {
            margin-bottom: 32px;
        }

        .rp-banner {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            background:
                linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(315deg, #4f46e5 25%, transparent 25%),
                linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
                linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
            background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%;
            background-color: #1d4ed8;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.18);
        }

        .rp-banner-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 0% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(255,255,255,0.2) 0%, transparent 50%);
            background-size: 50% 50%;
            background-position: 0 0, 100% 0, 100% 100%, 0 100%;
            background-repeat: no-repeat;
        }

        .rp-banner-content {
            position: relative;
            z-index: 1;
            padding: 32px;
        }

        .banner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
        }

        .banner-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .banner-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .banner-header h1 {
            color: white;
            font-size: 2rem;
            margin: 0 0 4px;
        }

        .banner-header p {
            margin: 0;
            color: #bfdbfe;
            font-size: 0.95rem;
        }

        .banner-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 14px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            background: linear-gradient(to right, #f43f5e, #f97316);
            box-shadow: 0 4px 14px rgba(244,63,94,0.35);
            transition: transform 0.2s ease;
            white-space: nowrap;
        }

        .banner-action:hover {
            transform: translateY(-2px);
            color: white;
        }

        .banner-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .banner-stat-card {
            border-radius: 18px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.25s ease;
        }

        .banner-stat-card:hover {
            transform: scale(1.02);
        }

        .banner-stat-card p {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: rgba(255,255,255,0.8);
        }

        .banner-stat-card strong {
            display: block;
            margin-top: 6px;
            font-size: 1.9rem;
            color: white;
        }

        .banner-stat-card span {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .stat-orange { background: #e37225; }
        .stat-gold { background: #ea9f21; }
        .stat-lime { background: #a7b838; }
        .stat-teal { background: #14697b; }

        .plans-container {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(148, 163, 184, 0.16);
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 2px solid #e1e8ed;
        }

        .section-heading h2 {
            margin: 0 0 6px;
            font-size: 1.8rem;
            color: #2c3e50;
        }

        .section-heading p {
            margin: 0;
            color: #6c757d;
        }

        @media (max-width: 1024px) {
            .banner-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-shell {
                padding: 20px 12px 32px;
            }

            .banner-header {
                flex-direction: column;
                align-items: stretch;
            }

            .banner-title-group {
                align-items: flex-start;
            }

            .banner-action {
                justify-content: center;
                width: 100%;
            }

            .banner-stats {
                grid-template-columns: 1fr;
            }

            .rp-banner-content {
                padding: 24px 20px;
            }

            .section-heading {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</body>
</html>