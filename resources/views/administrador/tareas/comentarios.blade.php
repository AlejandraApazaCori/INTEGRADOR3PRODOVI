<!-- Sección de Comentarios -->
<div class="border-t border-gray-200">
    <div class="p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #ea9f21;">
                <i class="fas fa-comments text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">Comentarios</h3>
                <p class="text-sm text-gray-500">Comparte ideas y feedback con tu equipo</p>
            </div>
        </div>
        
        <!-- Formulario para nuevo comentario -->
        <div class="mb-6 rounded-xl p-4 md:p-6" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <form action="{{ route('administrador.tareas.comentarios.store', $tarea->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="contenido" class="sr-only">Nuevo comentario</label>
                    <textarea name="contenido" id="contenido" rows="3" 
                              class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm rounded-xl transition-all duration-200 bg-white resize-none" style="border: 1px solid #d1d5db;" 
                              placeholder="Escribe tu comentario..." required></textarea>
                </div>
                <div class="mb-4">
                    <label for="archivos" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-paperclip mr-2 text-gray-400"></i>
                        Archivos adjuntos
                    </label>
                    <div id="comentarios-dropzone" class="relative border-2 border-dashed rounded-xl p-4 text-center transition-all duration-200 bg-white/50 hover:bg-white cursor-pointer" style="border-color: #d1d5db;">
                        <input type="file" name="archivos[]" id="archivos" multiple
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="flex flex-col items-center justify-center pointer-events-none">
                            <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-500">Haz clic o arrastra archivos aquí</p>
                            <p class="text-xs text-gray-400 mt-1">Máx. 10MB por archivo · Formatos: JPG, PNG, PDF, DOC, etc.</p>
                        </div>
                    </div>
                    <div id="comentarios-archivos-preview" class="hidden mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Archivos seleccionados</p>
                        <ul id="comentarios-archivos-lista" class="space-y-1 text-sm text-gray-600"></ul>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                        <i class="fas fa-paper-plane"></i>
                        Publicar comentario
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Lista de comentarios -->
        <div class="space-y-4">
            @forelse($tarea->comentarios as $comentario)
            <div class="rounded-xl p-4 md:p-6 transition-all duration-200 hover:shadow-lg" style="background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05); hover:box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.03);">
                <!-- Indicador visual de comentario -->
                <div class="flex items-start gap-2 mb-3">
                    <div class="w-1 h-6 rounded-full" style="background: #ea9f21;"></div>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: #ea9f21;">Comentario</p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-between items-start gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 h-9 w-9 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                            {{ strtoupper(substr($comentario->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $comentario->user->name }}</p>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                <i class="fas fa-clock text-gray-300"></i>
                                {{ $comentario->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @if(Auth::id() == $comentario->user_id || Auth::user()->hasRole('admin'))
                    <form action="{{ route('administrador.tareas.comentarios.destroy', ['tarea' => $tarea->id, 'comentario' => $comentario->id]) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors duration-200" title="Eliminar comentario">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </form>
                    @endif
                </div>
                
                <!-- Contenido del comentario con fondo resaltado -->
                <div class="mt-3 text-sm text-gray-700 leading-relaxed p-4 rounded-xl" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-quote-left text-xs" style="color: #a7b838;"></i>
                        <div>
                            {!! nl2br(e($comentario->contenido)) !!}
                        </div>
                    </div>
                </div>
                
                <!-- Archivos adjuntos al comentario -->
                @if($comentario->archivos->count() > 0)
                <div class="mt-3 pt-3" style="border-top: 1px solid #e5e7eb;">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-paperclip"></i>
                        Archivos adjuntos
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($comentario->archivos as $archivo)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border transition-all duration-200" style="background: #f8fafc; border-color: #e2e8f0; hover:background: #ffffff; hover:border-color: #818cf8;">
                            <div class="flex items-center gap-2">
                                <span class="text-base">
                                    @switch($archivo->extension)
                                        @case('pdf') <i class="fas fa-file-pdf text-red-500"></i> @break
                                        @case('doc') @case('docx') <i class="fas fa-file-word text-blue-500"></i> @break
                                        @case('xls') @case('xlsx') <i class="fas fa-file-excel text-green-500"></i> @break
                                        @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fas fa-image text-purple-500"></i> @break
                                        @case('zip') @case('rar') <i class="fas fa-file-archive text-amber-500"></i> @break
                                        @default <i class="fas fa-file text-gray-500"></i>
                                    @endswitch
                                </span>
                                <span class="text-xs font-medium text-gray-700 truncate max-w-[120px] sm:max-w-[200px]">{{ $archivo->nombre_original }}</span>
                            </div>
                            <a href="{{ Storage::url($archivo->ruta_archivo) }}" download 
                               class="text-gray-400 hover:text-indigo-600 transition-colors duration-200" title="Descargar">
                                <i class="fas fa-download text-sm"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-12 rounded-xl border border-dashed" style="background: #f8fafc; border-color: #d1d5db;">
                <i class="fas fa-comment-slash text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 font-medium">No hay comentarios aún</p>
                <p class="text-xs text-gray-400 mt-1">Sé el primero en dejar un comentario</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    (function () {
        const input = document.getElementById('archivos');
        const dropzone = document.getElementById('comentarios-dropzone');
        const preview = document.getElementById('comentarios-archivos-preview');
        const list = document.getElementById('comentarios-archivos-lista');

        if (!input || !dropzone || !preview || !list) {
            return;
        }

        const baseBorderColor = '#d1d5db';
        const activeBorderColor = '#818cf8';
        const activeBackground = '#eef2ff';
        const baseBackground = 'rgba(255,255,255,0.5)';

        const setDropzoneState = (isActive) => {
            dropzone.style.borderColor = isActive ? activeBorderColor : baseBorderColor;
            dropzone.style.background = isActive ? activeBackground : baseBackground;
        };

        const renderFiles = (files) => {
            list.innerHTML = '';

            if (!files.length) {
                preview.classList.add('hidden');
                return;
            }

            Array.from(files).forEach((file) => {
                const item = document.createElement('li');
                item.className = 'flex items-center justify-between gap-3';
                item.innerHTML = `
                    <span class="truncate">${file.name}</span>
                    <span class="text-xs text-gray-400 whitespace-nowrap">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                `;
                list.appendChild(item);
            });

            preview.classList.remove('hidden');
        };

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                setDropzoneState(true);
            });
        });

        ['dragleave', 'dragend'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                setDropzoneState(false);
            });
        });

        dropzone.addEventListener('drop', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setDropzoneState(false);

            const droppedFiles = event.dataTransfer ? event.dataTransfer.files : null;

            if (!droppedFiles || !droppedFiles.length) {
                return;
            }

            input.files = droppedFiles;
            renderFiles(droppedFiles);
        });

        input.addEventListener('change', () => {
            renderFiles(input.files);
        });
    })();
</script>
