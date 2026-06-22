@extends('layouts.app')

@section('title', 'Historial de Notificaciones')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Historial de notificaciones</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Registro completo de pagos, campañas y archivos subidos</p>
                    </div>
                    <a href="{{ url()->previous() }}" 
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" 
                       style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        @if($notificaciones->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-bell-slash text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-400 text-lg">No hay notificaciones registradas.</p>
                </div>
            </div>
        @else
            <div class="space-y-2">
                @foreach($notificaciones as $notif)
                    <a href="{{ $notif['url'] }}"
                       class="flex items-start gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4 hover:bg-gray-50 transition-all duration-200 hover:shadow-md {{ $notif['visto'] ? 'opacity-70' : 'border-l-4 border-indigo-500' }}">

                        <div class="text-2xl mt-0.5 flex-shrink-0">{{ $notif['icono'] }}</div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wide
                                    {{ $notif['tipo'] === 'pago' ? 'text-amber-600' : ($notif['tipo'] === 'campaña' ? 'text-purple-600' : 'text-blue-600') }}">
                                    {{ $notif['tipo'] }}
                                </span>
                                @if(!$notif['visto'])
                                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $notif['titulo'] }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $notif['detalle'] }}</p>
                        </div>

                        <div class="text-[10px] text-gray-400 whitespace-nowrap mt-1">
                            {{ $notif['fecha']->format('d/m/Y H:i') }}
                        </div>
                    </a>
                @endforeach
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                <i class="fas fa-info-circle mr-1"></i>
                Mostrando hasta 150 registros más recientes
            </p>
        @endif
    </div>
</div>

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
            radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size:     50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat:   no-repeat;
    }

    .border-l-4 {
        border-left-width: 4px;
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
        .rp-banner a {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endsection