@extends('layouts.app')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Banner con fondo geométrico -->
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl flex-shrink-0" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-upload text-white text-2xl"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-white mb-1">Subir archivos</h1>
                        <p style="color: #bfdbfe; font-size: 0.9rem;">Para la tarea: <strong style="color: white;">{{ $tarea->titulo }}</strong></p>
                    </div>
                    <a href="{{ route('administrador.tareas.show', $tarea->id) }}" 
                       class="inline-flex items-center px-4 py-2.5 rounded-xl font-semibold text-white transition-all hover:-translate-y-0.5 flex-shrink-0" 
                       style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario mejorado -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #ea9f21;">
                        <i class="fas fa-file-upload text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Subir archivos</h2>
                        <p class="text-gray-600 text-sm mt-0.5">Comparte archivos con tu equipo</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('administrador.tareas.archivos.store', $tarea->id) }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf

                <!-- Archivos -->
                <div>
                    <label for="archivos" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-files mr-2 text-gray-400"></i>
                        Seleccionar archivos <span class="text-red-500">*</span>
                    </label>
                    <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-all duration-200 bg-gray-50/50 hover:bg-gray-50" style="border-color: #d1d5db; hover:border-color: #818cf8;">
                        <input type="file" name="archivos[]" id="archivos" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                               multiple required accept=".jpg,.jpeg,.png,.gif,.mp4,.mov,.avi,.pdf,.ai,.mp3,.wav,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.psd,.svg,.webp">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-sm text-gray-500 font-medium">Haz clic o arrastra tus archivos aquí</p>
                            <p class="text-xs text-gray-400 mt-2 max-w-md">
                                <i class="fas fa-info-circle mr-1"></i>
                                Formatos: mp4, mp3, pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif, webp, zip. 
                                Máx. 10MB por archivo.
                            </p>
                            <div id="fileList" class="mt-3 w-full max-w-md hidden">
                                <div class="bg-white rounded-lg border border-gray-200 p-3">
                                    <p class="text-xs font-semibold text-gray-600 mb-2">
                                        <i class="fas fa-list mr-1"></i> Archivos seleccionados:
                                    </p>
                                    <ul id="fileNames" class="text-xs text-gray-500 space-y-1 max-h-24 overflow-y-auto"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-2 text-gray-400"></i>
                        Descripción <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="3" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white resize-none"
                        placeholder="Describe el propósito de estos archivos..."></textarea>
                </div>

                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('administrador.tareas.show', $tarea->id) }}" 
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                        <i class="fas fa-upload mr-2"></i>
                        Subir Archivos
                    </button>
                </div>
            </form>
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

    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('archivos');
    const fileList = document.getElementById('fileList');
    const fileNames = document.getElementById('fileNames');

    fileInput.addEventListener('change', function() {
        fileList.classList.remove('hidden');
        fileNames.innerHTML = '';
        
        if (this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2 py-1 border-b border-gray-100 last:border-0';
                const icon = document.createElement('i');
                icon.className = 'fas fa-file text-gray-400 text-xs';
                li.appendChild(icon);
                const span = document.createElement('span');
                span.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                li.appendChild(span);
                fileNames.appendChild(li);
            });
        } else {
            fileList.classList.add('hidden');
        }
    });
});
</script>
@endsection
