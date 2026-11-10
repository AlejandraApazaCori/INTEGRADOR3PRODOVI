@extends('layouts.app')

@section('title', 'Seguimiento del equipo')

@section('content')
@php
    $totalCampaigns = $campaigns->total();
    $totalTasks = (int) ($taskCounts->total ?? 0);
    $completed = (int) ($taskCounts->completed ?? 0);
    $overdue = (int) ($taskCounts->overdue ?? 0);
    $statuses = ['activa' => ['Activas', '#16a34a'], 'pausada' => ['Pausadas', '#d97706'], 'finalizada' => ['Finalizadas', '#64748b']];
@endphp
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-green-700">Usuarios / Equipo</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">Seguimiento de {{ $user->name }}</h1>
                <p class="mt-2 text-gray-600">{{ $user->email }} · {{ $user->roles->pluck('nombre_rol')->join(', ') ?: 'Sin rol asignado' }}</p>
                <p class="mt-1 text-sm text-gray-500">Las tareas cuentan solo cuando esta persona figura como responsable. Los archivos cuentan solo cuando los subió esta persona.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('administrador.usuarios.edit', $user) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Editar perfil</a>
                <a href="{{ route('administrador.usuarios.index') }}" class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Volver a usuarios</a>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5" aria-label="Resumen del trabajo">
            @foreach([['Campañas vinculadas', $totalCampaigns], ['Tareas asignadas', $totalTasks], ['Tareas terminadas', $completed], ['Tareas vencidas', $overdue], ['Archivos subidos', $uploadedFiles]] as [$label, $value])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-2" aria-label="Gráficas de seguimiento">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Estado de las campañas</h2>
                <div class="mt-5 space-y-4">
                    @foreach($statuses as $key => [$label, $color])
                        @php
                            $count = (int) ($statusCounts[$key] ?? 0);
                        @endphp
                        <div>
                            <div class="mb-1 flex justify-between text-sm"><span>{{ $label }}</span><strong>{{ $count }}</strong></div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100" role="img" aria-label="{{ $label }}: {{ $count }}">
                                <div class="h-full rounded-full" style="width: {{ $totalCampaigns ? round($count / $totalCampaigns * 100) : 0 }}%; background: {{ $color }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Avance de tareas asignadas</h2>
                <p class="mt-2 text-sm text-gray-500">{{ $completed }} de {{ $totalTasks }} terminadas</p>
                <div class="mt-5 flex h-5 overflow-hidden rounded-full bg-gray-100" role="img" aria-label="{{ $completed }} tareas terminadas de {{ $totalTasks }}">
                    <div class="h-full bg-green-600" style="width: {{ $totalTasks ? round($completed / $totalTasks * 100) : 0 }}%"></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600"><span>🟢 Terminadas: {{ $completed }}</span><span>⚪ Por realizar: {{ max(0, $totalTasks - $completed) }}</span><span>🔴 Vencidas: {{ $overdue }}</span></div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900">Campañas en las que participó</h2>
            <p class="mt-1 text-sm text-gray-500">Incluye campañas creadas, asignadas y campañas con tareas o entregas registradas.</p>
            @forelse($campaigns as $campaign)
                @php
                    $roles = collect();
                    if ((int) $campaign->usuario_creador_id === (int) $user->id) $roles->push('Creador');
                    if ((int) $campaign->community_manager_id === (int) $user->id) $roles->push('Community manager');
                    if ((int) $campaign->disenador_id === (int) $user->id || $campaign->disenadores->contains('id', $user->id)) $roles->push('Diseño');
                    if ($roles->isEmpty()) $roles->push('Participó en tareas');
                @endphp
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-gray-200 p-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $campaign->nombre }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $campaign->suscripcion?->empresa?->nombre_empresa ?? $campaign->cliente?->name ?? 'Sin empresa' }} · {{ ucfirst($campaign->estado) }} · {{ $campaign->tareas_count }} tareas en la campaña</p>
                        <p class="mt-1 text-sm font-medium text-green-800">{{ $campaign->tareas_terminadas_count }} de {{ $campaign->tareas_asignadas_count }} tareas asignadas terminadas</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $roles->join(' · ') }} · {{ $campaign->fecha_inicio ? \Carbon\Carbon::parse($campaign->fecha_inicio)->format('d/m/Y') : 'Sin fecha de inicio' }}</p>
                    </div>
                    <a href="{{ route('administrador.usuarios.seguimiento.campania', [$user, $campaign]) }}" class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white">Ver tareas y entregas</a>
                </div>
            @empty
                <p class="mt-5 rounded-xl bg-gray-50 p-6 text-center text-gray-500">Todavía no hay campañas vinculadas a este usuario.</p>
            @endforelse
            @if($campaigns->hasPages())<div class="mt-6">{{ $campaigns->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
