  <!-- BTN AGREGAR USUARIO Y HEADER -->   
<!-- BTN AGREGAR USUARIO Y HEADER -->   
<div class="animate-fade-up mb-8">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">
                Gestión de Usuarios
            </h1>
            <p class="text-gray-600 text-lg">Administra y supervisa todos los usuarios del sistema</p>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('administrador.usuarios.eliminados') }}" class="bg-red-100 text-red-600 hover:bg-red-200 px-4 py-2 rounded-xl font-semibold flex items-center transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a2 2 0 00-2-2H9a2 2 0 00-2 2h10z" />
                </svg>
                Usuarios Eliminados
            </a>

             <a href="{{ route('administrador.usuarios.create') }}" class="btn-primary text-white px-6 py-3 rounded-xl flex items-center font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Agregar Usuario
            </a>
        </div>
    </div>
</div>

    
    <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 animate-fade-up" style="animation-delay: 0.1s">
                <div class="stats-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $users->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ $users->filter(function($user) { 
                                    return $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty(); 
                                })->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Administradores</p>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ $users->filter(function($user) { 
                                    return $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty(); 
                                })->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="stats-card p-6 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sin Plan</p>
                            <p class="text-2xl font-bold text-gray-600">
                                {{ $users->filter(function($user) { 
                                    return $user->suscripciones->isEmpty() && $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isEmpty(); 
                                })->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>