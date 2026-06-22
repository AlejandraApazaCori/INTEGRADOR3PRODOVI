<!-- TAREAS -->
@php
    $tareas = $campania->tareas;
    $totalTareas = $tareas->count();
    $tareasPendientes = $tareas->where('estado', 'pendiente')->count();
    $tareasEnProgreso = $tareas->where('estado', 'en_progreso')->count();
    $tareasCompletadas = $tareas->where('estado', 'completada')->count();
@endphp

<div class="mt-0 m-8">
    <section class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Header con gradiente personalizado -->
        <div class="px-6 py-6 md:px-8" style="background: linear-gradient(135deg, #1e293b 0%, #1e3a5f 50%, #1a237e 100%);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                   
                    <h2 class="mt-3 text-2xl md:text-3xl font-bold text-white">Tareas de la Campaña</h2>
                    <p class="mt-2 max-w-2xl text-sm md:text-base" style="color: #93c5fd;">
                        Visualiza el avance del equipo, identifica bloqueos rápido y gestiona las entregas de esta campaña desde un solo lugar.
                    </p>
                </div>

                <a href="{{ route('administrador.tareas.create', $campania->id) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold shadow-lg transition-all duration-200 hover:-translate-y-0.5" style="background: #ffffff; color: #1e293b;">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Nueva Tarea
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl px-4 py-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: #93c5fd;">Total</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $totalTareas }}</p>
                </div>
                <div class="rounded-2xl px-4 py-4" style="background: rgba(234, 159, 33, 0.15); border: 1px solid rgba(234, 159, 33, 0.25);">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: #fcd34d;">Pendientes</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $tareasPendientes }}</p>
                </div>
                <div class="rounded-2xl px-4 py-4" style="background: rgba(227, 114, 37, 0.15); border: 1px solid rgba(227, 114, 37, 0.25);">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: #fb923c;">En progreso</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $tareasEnProgreso }}</p>
                </div>
                <div class="rounded-2xl px-4 py-4" style="background: rgba(167, 184, 56, 0.15); border: 1px solid rgba(167, 184, 56, 0.25);">
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color: #a3e635;">Completadas</p>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $tareasCompletadas }}</p>
                </div>
            </div>
        </div>

        @if($totalTareas > 0)
            <div class="p-6 md:p-8">
                <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Panel de tareas</h3>
                        <p class="text-sm text-gray-500">Consulta responsables, prioridad, fecha límite y accesos rápidos para cada tarea.</p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-medium" style="background: #f1f5f9; color: #475569;">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: #a7b838;"></span>
                        {{ $tareasCompletadas }} de {{ $totalTareas }} completadas
                    </div>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-gray-200" style="background: linear-gradient(to bottom, #ffffff, #f8fafc);">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead style="background: rgba(241, 245, 249, 0.8); backdrop-filter: blur(4px);">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Tarea</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Responsable</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Prioridad</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Fecha límite</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.2em]" style="color: #64748b;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($tareas as $tarea)
                                @php
                                    $estadoClases = match($tarea->estado) {
                                        'pendiente' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                        'en_progreso' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
                                        'completada' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                        default => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                    };

                                    $prioridadClases = match($tarea->prioridad) {
                                        'baja' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                        'media' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                                        'alta' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                        default => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                    };
                                @endphp
                                <tr class="transition-colors duration-200 hover:bg-slate-50/80">
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-2xl text-white shadow-sm" style="background: linear-gradient(135deg, #3b82f6, #4f46e5);">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-7 8h.01M15 5h4m0 0v4m0-4L10 14"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $tarea->titulo }}</p>
                                                <p class="mt-1 text-xs text-gray-500">ID #{{ $tarea->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold text-white" style="background: linear-gradient(135deg, #8b5cf6, #d946ef);">
                                                {{ strtoupper(substr($tarea->asignado->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $tarea->asignado->name }}</p>
                                                <p class="text-xs text-gray-500">Responsable principal</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estadoClases }}">
                                            {{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $prioridadClases }}">
                                            {{ ucfirst($tarea->prioridad) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="text-sm font-medium text-gray-900">{{ $tarea->fecha_limite->format('d/m/Y') }}</div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false) >= 0
                                                ? 'Faltan ' . now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay()) . ' días'
                                                : 'Vencida hace ' . abs(now()->startOfDay()->diffInDays($tarea->fecha_limite->copy()->startOfDay(), false)) . ' días' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            @if($tarea->asignado_id == auth()->id())
                                                <a href="{{ route('administrador.tareas.show', $tarea->id) }}"
                                                   class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                                                    Subir Tarea
                                                </a>
                                            @endif

                                            <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}"
                                               class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-slate-800" style="background: #475569;">
                                                Aprobar Tarea
                                            </a>

                                            <a href="{{ route('administrador.tareas.edit', $tarea->id) }}"
                                               class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600" style="background: #ea9f21;">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="p-8 md:p-10">
                <div class="rounded-3xl border border-dashed px-6 py-12 text-center" style="border-color: #cbd5e1; background: #f8fafc;">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm" style="ring: 1px solid #e2e8f0;">
                        <svg class="h-8 w-8" style="color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-7 8h4m-4-4h6m-6-4h8"></path>
                        </svg>
                    </div>
                    <h3 class="mt-5 text-xl font-semibold text-gray-900">No hay tareas registradas</h3>
                    <p class="mt-2 text-sm text-gray-500">Crea la primera tarea para empezar a organizar entregas, responsables y fechas clave de esta campaña.</p>
                    <a href="{{ route('administrador.tareas.create', $campania->id) }}"
                       class="mt-6 inline-flex items-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:-translate-y-0.5" style="background: linear-gradient(135deg, #2563eb, #4f46e5);">
                        Crear primera tarea
                    </a>
                </div>
            </div>
        @endif
    </section>
</div>