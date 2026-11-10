@extends('layouts.app')

@section('title', 'Historial de campañas')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-green-700">Usuarios / Historial</p>
                <h1 class="text-3xl font-bold text-gray-900">Historial de campañas</h1>
                <p class="mt-2 text-gray-600">{{ $user->name }} · {{ $user->email }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $campaigns->total() }} {{ $campaigns->total() === 1 ? 'campaña registrada' : 'campañas registradas' }}. El estado corresponde al último estado guardado de cada campaña.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('administrador.usuarios.view', $user) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100"><i class="fas fa-user" aria-hidden="true"></i>Ver usuario</a>
                <a href="{{ route('administrador.usuarios.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800"><i class="fas fa-arrow-left" aria-hidden="true"></i>Volver a usuarios</a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if($campaigns->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-clock-rotate-left mb-4 text-4xl text-gray-300" aria-hidden="true"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Este usuario todavía no tiene campañas</h2>
                    <p class="mt-2 text-gray-500">Las campañas que se le asignen aparecerán aquí, incluidas las finalizadas.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-green-700 text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <tr>
                                <th scope="col" class="px-5 py-4">Campaña</th>
                                <th scope="col" class="px-5 py-4">Empresa</th>
                                <th scope="col" class="px-5 py-4">Plan / suscripción</th>
                                <th scope="col" class="px-5 py-4">Periodo</th>
                                <th scope="col" class="px-5 py-4">Estado registrado</th>
                                <th scope="col" class="px-5 py-4 text-right">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($campaigns as $campaign)
                                @php
                                    $company = $campaign->suscripcion?->empresa ?? $campaign->empresas->first();
                                    $status = [
                                        'activa' => ['Activa', 'bg-green-100 text-green-800'],
                                        'pausada' => ['Pausada', 'bg-yellow-100 text-yellow-800'],
                                        'finalizada' => ['Finalizada', 'bg-gray-100 text-gray-700'],
                                    ][$campaign->estado] ?? [ucfirst((string) $campaign->estado), 'bg-gray-100 text-gray-700'];
                                @endphp
                                <tr class="hover:bg-green-50">
                                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $campaign->nombre }}</td>
                                    <td class="px-5 py-4 text-gray-700">{{ $company?->nombre_empresa ?? 'No registrada' }}</td>
                                    <td class="px-5 py-4 text-gray-700">
                                        <span class="block">{{ $campaign->suscripcion?->plan?->nombre ?? 'Sin plan asociado' }}</span>
                                        @if($campaign->suscripcion)<span class="text-xs text-gray-500">Suscripción #{{ $campaign->suscripcion->id }}</span>@endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-gray-700">
                                        <time datetime="{{ $campaign->fecha_inicio }}">{{ \Carbon\Carbon::parse($campaign->fecha_inicio)->format('d/m/Y') }}</time>
                                        <span class="text-gray-400">–</span>
                                        <time datetime="{{ $campaign->fecha_fin }}">{{ \Carbon\Carbon::parse($campaign->fecha_fin)->format('d/m/Y') }}</time>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $status[1] }}">{{ $status[0] }}</span>
                                        @if($campaign->trashed())<span class="ml-1 inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Eliminada</span>@endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @unless($campaign->trashed())
                                            <a href="{{ route('administrador.campañas.show', $campaign) }}" class="font-semibold text-green-700 hover:underline" aria-label="Ver campaña {{ $campaign->nombre }}">Ver campaña</a>
                                        @else
                                            <span class="text-gray-400">No disponible</span>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($campaigns->hasPages())
            <div class="mt-6">{{ $campaigns->links() }}</div>
        @endif
    </div>
</div>
@endsection
