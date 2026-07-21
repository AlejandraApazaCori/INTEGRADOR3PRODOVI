@extends('layouts.app2')

@section('title', 'Editar Empresa')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Editar Empresa</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Actualiza la informacion principal de tu empresa</p>
                    </div>
                    <a href="{{ route('empresas.show', $empresa->id) }}"
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0"
                       style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #ea9f21;">
                        <i class="fas fa-pen-to-square text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Datos de la empresa</h2>
                        <p class="text-gray-600 text-sm mt-0.5">Modifica la informacion y guarda los cambios</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="nombre_empresa" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-store mr-2 text-gray-400"></i>
                            Nombre de la empresa <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="nombre_empresa"
                            name="nombre_empresa"
                            value="{{ old('nombre_empresa', $empresa->nombre_empresa) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Ej: Prodovi S.A."
                            required
                        >
                        @error('nombre_empresa')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="tipo_empresa" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tags mr-2 text-gray-400"></i>
                            Tipo de empresa <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="tipo_empresa"
                            name="tipo_empresa"
                            value="{{ old('tipo_empresa', $empresa->tipo_empresa) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Ej: Tecnologia, Comercio, Servicios"
                            required
                        >
                        @error('tipo_empresa')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-2 text-gray-400"></i>
                            Descripcion
                        </label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                            placeholder="Describe brevemente tu empresa, sus servicios y objetivos..."
                        >{{ old('descripcion', $empresa->descripcion) }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="logo" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-image mr-2 text-gray-400"></i>
                            Logo de la empresa
                        </label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition-all duration-200 bg-gray-50/50 hover:bg-gray-50">
                            <input
                                type="file"
                                id="logo"
                                name="logo"
                                accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                onchange="previewLogo(event)"
                            >
                            <div id="logo-preview" class="flex flex-col items-center justify-center gap-3">
                                @if($empresa->logo)
                                    <img src="{{ Storage::url($empresa->logo) }}" alt="Logo actual de {{ $empresa->nombre_empresa }}" class="max-h-32 w-auto object-contain rounded-lg shadow-md">
                                    <p class="text-sm text-gray-500">Logo actual cargado</p>
                                @else
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-sm text-gray-500">Haz clic o arrastra tu logo aqui</p>
                                    <p class="text-xs text-gray-400 mt-1">Formatos: JPG, PNG, GIF (Max. 2MB)</p>
                                @endif
                            </div>
                            <div id="logo-file-name" class="hidden mt-2 text-sm font-medium text-indigo-600"></div>
                        </div>
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('empresas.show', $empresa->id) }}" class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                        <i class="fas fa-save mr-2"></i>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewLogo(event) {
        const input = event.target;
        const preview = document.getElementById('logo-preview');
        const fileName = document.getElementById('logo-file-name');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Logo preview" class="max-h-32 w-auto object-contain rounded-lg shadow-md">
                    <p class="text-sm text-gray-500 mt-2">Nuevo logo listo para guardar</p>
                `;
                fileName.textContent = input.files[0].name;
                fileName.classList.remove('hidden');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<style>
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
            radial-gradient(circle at 0% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 0%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
            radial-gradient(circle at 0% 100%, rgba(255,255,255,0.2) 0%, transparent 50%);
        background-size: 50% 50%;
        background-position: 0 0, 100% 0, 100% 100%, 0 100%;
        background-repeat: no-repeat;
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

        .flex.flex-col.sm\:flex-row.justify-between.items-center {
            flex-direction: column;
            width: 100%;
        }

        .flex.flex-col.sm\:flex-row.justify-between.items-center a,
        .flex.flex-col.sm\:flex-row.justify-between.items-center button {
            width: 100%;
        }
    }
</style>
@endsection
