@extends('layouts.app')

@section('title', 'Gestión de Usuarios')
@include('a.css.admin.index-usuarios')

@section('content')
    <div class="min-h-screen">
        <div class="container mx-auto px-6 py-8">
            <!-- Banner con fondo geométrico estilo Roles y Permisos -->
            <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay absolute inset-0"></div>
                <div class="relative z-10 px-8 py-8">
                    <!-- Header con título y botones -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white mb-1">Gestión de Usuarios</h1>
                                <p style="color: #bfdbfe; font-size: 0.9rem;">Administra y supervisa todos los usuarios del sistema</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('administrador.usuarios.eliminados') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5" style="background: #ed0551; box-shadow: 0 4px 14px rgba(237,5,81,0.35);">
                                <i class="fas fa-trash-alt mr-2 text-sm"></i>
                                Usuarios Eliminados
                            </a>
                            <a href="{{ route('administrador.usuarios.create') }}" class="inline-flex items-center px-6 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5" style="background: linear-gradient(to right, #3b82f6, #2563eb); box-shadow: 0 4px 14px rgba(59,130,246,0.35);">
                                <i class="fas fa-user-plus mr-2 text-sm"></i>
                                Agregar Usuario
                            </a>
                        </div>
                    </div>

                    <!-- Stats Cards sin transparencia -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #e37225; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Total Usuarios</p>
                                    <p class="text-3xl font-bold text-white mt-1">{{ $users->total() }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-users text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Usuarios Activos</p>
                                    <p class="text-3xl font-bold text-white mt-1">
                                        {{ $users->filter(function($user) { 
                                            return $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty(); 
                                        })->count() }}
                                    </p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-user-check text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Administradores</p>
                                    <p class="text-3xl font-bold text-white mt-1">
                                        {{ $users->filter(function($user) { 
                                            return $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty(); 
                                        })->count() }}
                                    </p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-user-shield text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rounded-2xl p-5 hover:scale-[1.02] transition-all duration-300" style="background: #14697b; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Sin Plan</p>
                                    <p class="text-3xl font-bold text-white mt-1">
                                        {{ $users->filter(function($user) { 
                                            return $user->suscripciones->isEmpty() && $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isEmpty(); 
                                        })->count() }}
                                    </p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-user-slash text-white text-base"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="card-glass p-6 rounded-2xl shadow-xl mb-8 animate-fade-up" style="animation-delay: 0.2s">
                <form action="{{ route('administrador.usuarios.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Buscar Usuario</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   class="search-input w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-0" 
                                   placeholder="Nombre del usuario...">
                            <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Filtrar por Rol</label>
                        <select name="role" id="role" class="search-input w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-0">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->nombre_rol }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                        <select name="status" id="status" class="search-input w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-0">
                            <option value="">Todos los estados</option>
                            <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            <option value="no_plan" {{ request('status') == 'no_plan' ? 'selected' : '' }}>Sin plan</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-3">
                        <button type="submit" class="btn-primary text-white px-6 py-3 rounded-xl flex items-center font-semibold flex-1">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Buscar
                        </button>
                        <a href="{{ route('administrador.usuarios.index') }}" class="btn-secondary px-6 py-3 rounded-xl flex items-center font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Users Table Mejorada -->
            <div class="table-container animate-fade-up" style="animation-delay: 0.3s">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Usuario</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Email</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Rol</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">Estado</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($users as $index => $user)
                                @php
                                    $rowClass = $index % 2 === 0 ? 'bg-white' : 'bg-indigo-50/20';
                                @endphp
                                <tr class="{{ $rowClass }} hover:bg-indigo-50/50 transition-colors duration-150 cursor-pointer" onclick="window.location='{{ route('administrador.usuarios.view', $user->id) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold mr-3 shadow-md">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                        <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                    {{ $role->nombre_rol }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('administrador.usuarios.view', $user->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors duration-200">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </a>
                                            <a href="{{ route('administrador.usuarios.edit', $user->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
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
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <style>
        /* Banner geométrico - Mismo estilo que Roles y Permisos */
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
            .rp-banner .px-8 { padding-left: 1.25rem; padding-right: 1.25rem; }
        }
    </style>

    @push('scripts')
    <script>
        document.getElementById('add-user-btn').addEventListener('click', function() {
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

        // Efecto de búsqueda en tiempo real (opcional)
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase();
            
            if (searchTerm.length > 0) {
                searchTimeout = setTimeout(() => {
                    console.log('Buscando:', searchTerm);
                }, 300);
            }
        });
    </script>
    @endpush
@endsection