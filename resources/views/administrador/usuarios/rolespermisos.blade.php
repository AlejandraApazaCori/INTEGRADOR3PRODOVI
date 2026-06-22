@extends('layouts.app')

@section('title', 'Roles y Permisos')

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Banner Header con fondo geométrico y tarjetas con colores personalizados -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-user-shield text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-1">Roles y Permisos</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Administra los roles del sistema y sus permisos de acceso</p>
                    </div>
                </div>
                
                <!-- Stats Cards con colores personalizados -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl p-4 hover:scale-[1.02] transition-all duration-300" style="background: #e37225; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Roles</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-user-gear text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ $roles->count() }}</div>
                    </div>
                    <div class="rounded-2xl p-4 hover:scale-[1.02] transition-all duration-300" style="background: #ea9f21; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Permisos</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-key text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ $permissions->count() }}</div>
                    </div>
                    <div class="rounded-2xl p-4 hover:scale-[1.02] transition-all duration-300" style="background: #a7b838; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Usuarios</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-users text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ $users->total() }}</div>
                    </div>
                    <div class="rounded-2xl p-4 hover:scale-[1.02] transition-all duration-300" style="background: #14697b; border: 1px solid rgba(255,255,255,0.2);">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-semibold uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Asignaciones</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-link text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-white">{{ $roles->sum(fn($role) => $role->permissions->count()) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-start">
                <i class="fas fa-exclamation-circle mr-3 text-red-500 mt-0.5"></i>
                <div>
                    <div class="font-semibold">Revisa los datos del formulario.</div>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <!-- Columna Izquierda - Roles del sistema -->
            <section class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-xl border border-gray-100">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: linear-gradient(135deg, #f43f5e, #f97316); box-shadow: 0 4px 14px rgba(244,63,94,0.35);">
                            <i class="fas fa-users-cog" style="color: white; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Roles del sistema</h2>
                            <p class="mt-1 text-sm text-gray-500">Aquí aparecen todos los roles registrados actualmente.</p>
                        </div>
                    </div>

                    <form action="{{ route('administrador.roles.store') }}" method="POST" class="mb-6 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <div class="relative flex-1">
                            <i class="fas fa-tag absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input id="nombre_rol" name="nombre_rol" type="text" value="{{ old('nombre_rol') }}" 
                                class="w-full rounded-xl border border-gray-200 pl-11 pr-4 py-3 text-sm focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-200 transition-all" 
                                placeholder="Ej. Administrador, Editor, Instructor...">
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:-translate-y-0.5" style="background: linear-gradient(to right, #f43f5e, #f97316); box-shadow: 0 4px 14px rgba(244,63,94,0.35);">
                            <i class="fas fa-plus mr-2" style="color: white;"></i>
                            Añadir rol
                        </button>
                    </form>

                    <div class="space-y-3">
                        @forelse ($roles as $index => $role)
                            @php
                                $badgeColors = [
                                    'bg-rose-50 text-rose-600 border-rose-200',
                                    'bg-amber-50 text-amber-600 border-amber-200',
                                    'bg-sky-50 text-sky-600 border-sky-200',
                                    'bg-violet-50 text-violet-600 border-violet-200',
                                    'bg-emerald-50 text-emerald-600 border-emerald-200',
                                ];
                                $badgeColor = $badgeColors[$index % count($badgeColors)];
                                $icons = ['fa-user-tie', 'fa-user-edit', 'fa-user-graduate', 'fa-user-gear', 'fa-user-plus'];
                                $icon = $icons[$index % count($icons)];
                            @endphp
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 hover:bg-white transition-all hover:shadow-md">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <i class="fas {{ $icon }} text-gray-500 text-base"></i>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold border {{ $badgeColor }}">
                                            {{ $role->nombre_rol }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-key mr-1 text-gray-400"></i>{{ $role->permissions->count() }} permisos
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-users mr-1 text-gray-400"></i>{{ $role->users->count() }} usuarios
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500">
                                <i class="fas fa-user-gear text-3xl text-gray-300 block mb-3"></i>
                                No hay roles registrados todavía.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- Columna Derecha - Matriz de Permisos -->
            <section class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-xl border border-gray-100">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 text-white shadow-lg">
                            <i class="fas fa-table text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Matriz de Permisos</h2>
                            <p class="mt-1 text-sm text-gray-500">Visualiza qué permisos tiene cada rol del sistema.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                                        <i class="fas fa-list mr-2 text-gray-400"></i>Permiso / Funcionalidad
                                    </th>
                                    @foreach ($roles as $role)
                                        <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-600">
                                            <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-[11px] text-rose-600 border border-rose-200">
                                                {{ $role->nombre_rol }}
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($permissions as $permission)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-5">
                                            <div class="text-base font-bold text-gray-900 flex items-center gap-2">
                                                <i class="fas fa-check-circle text-gray-400 text-sm"></i>
                                                {{ $permission->nombre_permiso }}
                                            </div>
                                            <div class="mt-1 max-w-sm text-sm text-gray-500">{{ $permission->descripcion ?: 'Sin descripción registrada.' }}</div>
                                        </td>
                                        @foreach ($roles as $role)
                                            @php
                                                $hasPermission = $role->permissions->contains('id', $permission->id);
                                            @endphp
                                            <td class="px-4 py-5 text-center">
                                                <form action="{{ route('administrador.roles.update', $role) }}" method="POST" class="inline-flex">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="nombre_rol" value="{{ $role->nombre_rol }}">
                                                    @foreach ($role->permissions as $rolePermission)
                                                        @if ($rolePermission->id !== $permission->id)
                                                            <input type="hidden" name="permissions[]" value="{{ $rolePermission->id }}">
                                                        @endif
                                                    @endforeach
                                                    @if (! $hasPermission)
                                                        <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                                    @endif
                                                    <button type="submit" class="group inline-flex h-10 w-16 items-center justify-center rounded-full border transition-all {{ $hasPermission ? 'border-green-300 bg-green-50 text-green-600 hover:bg-green-100' : 'border-gray-200 bg-white text-gray-300 hover:border-gray-300 hover:text-gray-500' }}">
                                                        @if ($hasPermission)
                                                            <i class="fas fa-check-circle text-green-500 text-base"></i>
                                                        @else
                                                            <i class="fas fa-circle text-gray-300 text-base"></i>
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $roles->count() + 1 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <i class="fas fa-inbox text-3xl text-gray-300 block mb-3"></i>
                                            No hay permisos creados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
    /* Banner geométrico */
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
        .grid.gap-6.xl\:grid-cols-2 { grid-template-columns: 1fr; }
    }
</style>
@endsection