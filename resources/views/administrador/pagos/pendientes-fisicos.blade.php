@extends('layouts.app')

@section('title', 'Pagos Pendientes Físicos')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-orange-50 to-red-50">
    <div class="container mx-auto px-4 py-8">
       <!-- Header con título y decoración -->
<div class="relative mb-8">
    <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl opacity-5"></div>
    <div class="relative bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex items-center space-x-4">
            <!-- Botón de Atrás -->
            <a href="{{ route('administrador.pagos.index') }}" class="text-gray-500 hover:text-gray-700 transition duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Pagos Pendientes Físicos</h1>
                <p class="text-gray-600 mt-1">Gestiona y aprueba los pagos pendientes de verificación</p>
            </div>
        </div>
    </div>
</div>
        
        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div class="mb-6">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-r-xl p-4 shadow-md">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulario de búsqueda mejorado -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Búsqueda de Pagos</h2>
                </div>
                
                <form action="{{ route('administrador.pagos.pendientes-fisicos') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Buscar por código de pago o nombre de usuario..." 
                                value="{{ request('search') }}"
                                class="w-full px-4 py-3 pl-10 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold transition duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center space-x-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>Buscar</span>
                        </button>
                        
                        @if(request('search'))
                            <a 
                                href="{{ route('administrador.pagos.pendientes-fisicos') }}" 
                                class="px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition duration-200 flex items-center space-x-2 border border-gray-200"
                                title="Limpiar búsqueda"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="hidden sm:inline">Limpiar</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de resultados -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Pagos Pendientes</h3>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-orange-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">{{ $pagos->count() }} pendiente(s)</span>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Usuario</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                    </svg>
                                    <span>Código</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span>Plan</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <span>Monto</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider border-r border-indigo-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Fecha Generación</span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                    </svg>
                                    <span>Acciones</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($pagos as $pago)
                        <tr class="{{ $loop->odd ? 'bg-white' : 'bg-indigo-50/20' }} hover:bg-indigo-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white text-sm font-bold">{{ substr($pago->usuario->name, 0, 1) }}</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">{{ $pago->usuario->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border bg-gray-100 text-gray-800 border-gray-200 font-mono">{{ $pago->codigoPago->codigo }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border-r border-gray-100">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold border bg-blue-100 text-blue-800 border-blue-200">{{ $pago->plan->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-100">{{ $pago->monto }} <span class="font-medium text-gray-500">{{ $pago->moneda }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                <form action="{{ route('administrador.pagos.aprobar', $pago->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Aprobar</span>
                                    </button>
                                </form>
                                <form action="{{ route('administrador.pagos.pendientes-fisicos.eliminar', $pago->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago pendiente y su codigo? La usuaria podra generar uno nuevo.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 1 12h6l1-12m-6 0V4h4v3"></path>
                                        </svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No se encontraron pagos físicos pendientes de aprobación.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Mostrar paginación si es necesario -->
            @if($pagos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} resultados
                        </div>
                        <div class="pagination-wrapper">
                            {{ $pagos->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.pagination-wrapper .pagination {
    display: flex;
    list-style: none;
    border-radius: 0.75rem;
    overflow: hidden;
}
.pagination-wrapper .page-link {
    padding: 0.5rem 0.75rem;
    margin: 0 1px;
    color: #4B5563;
    background-color: white;
    border: 1px solid #D1D5DB;
    transition: all 0.2s;
}
.pagination-wrapper .page-link:hover {
    background-color: #F3F4F6;
    color: #1F2937;
}
.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(to right, #3B82F6, #6366F1);
    color: white;
    border-color: #3B82F6;
}
</style>
@endsection
