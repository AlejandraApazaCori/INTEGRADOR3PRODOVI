@extends('layouts.app')

@section('title', 'Crear Empresa')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h1 class="text-2xl font-bold text-white">Crear Empresa para {{ $user->name }}</h1>
            </div>
            
            <form action="{{ route('administrador.empresas.guardar', $user->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="nombre_empresa" class="block text-gray-800 font-medium mb-2">
                            Nombre de la empresa <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nombre_empresa" 
                            name="nombre_empresa" 
                            value="{{ old('nombre_empresa') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                        @error('nombre_empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="tipo_empresa" class="block text-gray-800 font-medium mb-2">
                            Tipo de empresa <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="tipo_empresa" 
                            name="tipo_empresa" 
                            value="{{ old('tipo_empresa') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                        @error('tipo_empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-gray-800 font-medium mb-2">
                            Descripción
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            rows="4" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="logo" class="block text-gray-800 font-medium mb-2">
                            Logo
                        </label>
                        <input 
                            type="file" 
                            id="logo" 
                            name="logo" 
                            accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="flex justify-between mt-8">
                    <a href="{{ route('administrador.empresas.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Cancelar
                    </a>
                    
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-lg">
                        Crear empresa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
