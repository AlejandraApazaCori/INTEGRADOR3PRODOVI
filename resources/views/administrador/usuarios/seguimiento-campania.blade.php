@extends('layouts.app')

@section('title', 'Seguimiento de campaña')

@section('content')
@php
    $doneStates = ['entregado', 'aprobado', 'publicado'];
    $stateNames = ['no_iniciado' => 'No iniciada', 'pendiente' => 'Pendiente', 'en_curso' => 'En curso', 'entregado' => 'Entregada', 'reformular' => 'Reformular', 'aprobado' => 'Aprobada', 'publicado' => 'Publicada'];
@endphp
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-green-700">Seguimiento / {{ $user->name }}</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $campania->nombre }}</h1>
                <p class="mt-2 text-gray-600">{{ $campania->suscripcion?->empresa?->nombre_empresa ?? $campania->cliente?->name ?? 'Sin empresa' }} · Estado: {{ ucfirst($campania->estado) }}</p>
                <p class="mt-1 text-sm text-gray-500">Creada por {{ $campania->creador?->name ?? 'Sin registro' }} · {{ $isCreator ? 'Vista de todas las tareas de la campaña' : 'Tareas asignadas y tareas en las que subió archivos' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('administrador.usuarios.seguimiento', $user) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Volver al seguimiento</a>
                <a href="{{ route('administrador.campañas.show', $campania) }}" class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Abrir campaña</a>
            </div>
        </header>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900">Tareas y entregables</h2>
            <p class="mt-1 text-sm text-gray-500">La entrega se atribuye a quien subió el archivo. El estado de la tarea corresponde a la campaña.</p>
            @forelse($tasks as $task)
                @php
                    $isAssigned = (int) $task->asignado_id === (int) $user->id || $task->responsables->isNotEmpty();
                    $done = in_array($task->estado, $doneStates, true);
                    $late = ! $done && $task->fecha_limite && $task->fecha_limite->lt(today());
                @endphp
                <article class="mt-4 rounded-xl border border-gray-200 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $task->titulo }}</h3>
                            <p class="mt-1 text-sm text-gray-500">Responsable principal: {{ $task->asignado?->name ?? 'Sin registro' }} · Límite: {{ $task->fecha_limite?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            @if($isAssigned)<span class="rounded-full bg-blue-100 px-3 py-1 text-blue-800">Asignada</span>@endif
                            <span class="rounded-full px-3 py-1 {{ $done ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $stateNames[$task->estado] ?? ucfirst($task->estado) }}</span>
                            @if($late && $isAssigned)<span class="rounded-full bg-red-100 px-3 py-1 text-red-800">Vencida</span>@endif
                        </div>
                    </div>
                    @if($isAssigned && ! $done)<p class="mt-3 text-sm font-medium text-amber-800">Pendiente de completar por {{ $user->name }}.</p>@endif
                    <div class="mt-4 border-t border-gray-100 pt-3">
                        <p class="text-sm font-semibold text-gray-700">Archivos subidos por {{ $user->name }}: {{ $task->archivos_usuario_count }}</p>
                        @forelse($task->archivos as $file)
                            <p class="mt-2 text-sm text-gray-600"><i class="fas fa-paperclip mr-1" aria-hidden="true"></i>{{ $file->nombre_original }} <span class="text-gray-400">· {{ $file->created_at?->format('d/m/Y H:i') }} · {{ ucfirst($file->estado) }}</span></p>
                        @empty
                            <p class="mt-2 text-sm text-gray-500">No registró archivos en esta tarea.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('administrador.tareas.ver-subidas', $task) }}" class="mt-3 inline-block text-sm font-semibold text-green-700 hover:underline">Ver detalle y archivos de la tarea</a>
                </article>
            @empty
                <p class="mt-5 rounded-xl bg-gray-50 p-6 text-center text-gray-500">No hay tareas vinculadas a esta persona en la campaña.</p>
            @endforelse
            @if($tasks->hasPages())<div class="mt-6">{{ $tasks->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
