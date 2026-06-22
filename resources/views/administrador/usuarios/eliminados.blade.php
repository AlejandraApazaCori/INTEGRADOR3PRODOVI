@extends('layouts.app')

@section('title', 'Usuarios Eliminados')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="container mx-auto px-4 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-trash-alt text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-white mb-1">Usuarios Eliminados</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Usuarios que han sido eliminados del sistema</p>
                    </div>
                    <a href="{{ route('administrador.usuarios.index') }}" 
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5" 
                       style="background: linear-gradient(to right, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                        <i class="fas fa-users mr-2 text-sm"></i>
                        Ver usuarios activos
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Tabla mejorada -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Nombre</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Email</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Rol</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Eliminado el</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($users as $index => $user)
                            @php
                                $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                            @endphp
                            <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <div class="flex items-center">
                                        
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    @if($user->roles->isNotEmpty())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            {{ $user->roles->first()->nombre_rol }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">Usuario</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                    <span class="text-sm text-gray-600">{{ $user->deleted_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('administrador.usuarios.restore', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-medium rounded-lg text-white transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(to right, #10b981, #059669); box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
                                            <i class="fas fa-undo-alt mr-1.5"></i>
                                            Restaurar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <i class="fas fa-inbox text-3xl text-gray-300 block mb-3"></i>
                                    No hay usuarios eliminados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
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

    @media (max-width: 640px) {
        .rp-banner .px-8 { 
            padding-left: 1.25rem; 
            padding-right: 1.25rem; 
        }
        .rp-banner .flex.items-center.gap-4 {
            flex-direction: column;
            align-items: stretch;
        }
        .rp-banner .flex-1 {
            text-align: center;
        }
        .rp-banner a {
            justify-content: center;
        }
    }
</style>
@endsection