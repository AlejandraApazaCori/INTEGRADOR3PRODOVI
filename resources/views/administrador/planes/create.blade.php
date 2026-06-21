@extends('layouts.app')

@section('title', 'Crear Nuevo Plan')

@section('content')
@php
    $oldFeatures = old('caracteristicas', []);
    $featuresToShow = !empty($oldFeatures) ? $oldFeatures : [];
    $selectedFeatureIds = collect($featuresToShow)->pluck('id')->filter();
    $featuredCount = collect($featuresToShow)->filter(function ($feature) {
        return !empty($feature['es_destacado']);
    })->count();
@endphp
<div class="min-h-screen" style="background-color: #f3e8ff;">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl text-white shadow-2xl" style="background: linear-gradient(90deg, #581c87 0%, #6d28d9 50%, #c026d3 100%);">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-2 lg:px-10">
                <div>
                    <div class="inline-flex items-center rounded-full border border-white border-opacity-20 bg-white bg-opacity-10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-blue-100">
                        Administrador de planes
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Crear nuevo plan</h1>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('administrador.planes.index') }}" class="inline-flex items-center rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-blue-50">
                            Volver a planes
                        </a>
                        
                    </div>
                </div>
                <div class="grid gap-5 md:grid-cols-2 lg:col-span-1 xl:grid-cols-3">
                    <div class="rounded-3xl border border-white border-opacity-20 bg-white bg-opacity-10 p-6 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Precio actual</p>
                        <p class="mt-3 text-3xl font-black leading-none">{{ number_format((float) old('precio', 0), 2, '.', ',') }}</p>
                        <p class="mt-2 text-sm text-gray-200">{{ old('moneda', 'BS') }} por {{ old('periodo_facturacion', 'mes') }}</p>
                    </div>
                    <div class="rounded-3xl border border-white border-opacity-20 bg-white bg-opacity-10 p-6 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Caracteristicas</p>
                        <p class="mt-3 text-3xl font-black leading-none">{{ count($featuresToShow) }}</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $featuredCount }} destacadas</p>
                    </div>
                    <div class="rounded-3xl border border-white border-opacity-20 bg-white bg-opacity-10 p-6 backdrop-blur md:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Estado del plan</p>
                        <p class="mt-3 text-xl font-bold leading-tight">{{ old('activo', true) ? 'Activo' : 'Inactivo' }}</p>
                        <p class="mt-2 text-sm text-gray-200">Orden de visualizacion: {{ old('orden', 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-red-100 font-bold text-red-600">!</div>
                    <div>
                        <p class="font-semibold">Hay datos por revisar antes de guardar.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach (collect($errors->all())->unique() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('administrador.planes.store') }}" method="POST" class="mt-6 grid gap-6 xl:grid-cols-3">
            @csrf

            <div class="space-y-6 xl:col-span-2">
                <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Bloque 1</p>
                        <h2 class="mt-2 text-2xl font-bold text-gray-900">Informacion principal</h2>
                        <p class="mt-2 text-sm text-gray-500">Define como se presenta el plan y cuanto se cobra.</p>
                    </div>

                    <div class="grid gap-6 px-6 py-6 sm:px-8 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label for="nombre" class="mb-2 block text-sm font-semibold text-gray-700">Nombre del plan *</label>
                            <input type="text" id="nombre" name="nombre" required value="{{ old('nombre') }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            @error('nombre')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label for="subtitulo" class="mb-2 block text-sm font-semibold text-gray-700">Subtitulo</label>
                            <input type="text" id="subtitulo" name="subtitulo" value="{{ old('subtitulo') }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Resume la propuesta del plan en una frase corta">
                        </div>

                        <div>
                            <label for="precio" class="mb-2 block text-sm font-semibold text-gray-700">Precio *</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-gray-400">Bs/$</span>
                                <input type="number" step="0.01" min="0" id="precio" name="precio" required value="{{ old('precio') }}"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-14 pr-4 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                            @error('precio')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="moneda" class="mb-2 block text-sm font-semibold text-gray-700">Moneda *</label>
                            <select id="moneda" name="moneda" required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <option value="BS" {{ old('moneda', 'BS') == 'BS' ? 'selected' : '' }}>Bolivianos (Bs)</option>
                                <option value="USD" {{ old('moneda', 'BS') == 'USD' ? 'selected' : '' }}>Dolares (USD)</option>
                            </select>
                        </div>

                        <div>
                            <label for="periodo_facturacion" class="mb-2 block text-sm font-semibold text-gray-700">Periodo de facturacion *</label>
                            <select id="periodo_facturacion" name="periodo_facturacion" required
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <option value="mes" {{ old('periodo_facturacion', 'mes') == 'mes' ? 'selected' : '' }}>Mes</option>
                                <option value="trimestre" {{ old('periodo_facturacion', 'mes') == 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                                <option value="semestre" {{ old('periodo_facturacion', 'mes') == 'semestre' ? 'selected' : '' }}>Semestre</option>
                                <option value="aÒo" {{ old('periodo_facturacion', 'mes') == 'aÒo' ? 'selected' : '' }}>Anual</option>
                            </select>
                            @error('periodo_facturacion')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="orden" class="mb-2 block text-sm font-semibold text-gray-700">Orden de visualizacion</label>
                            <input type="number" min="0" id="orden" name="orden" value="{{ old('orden', 0) }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="0">
                        </div>

                        <div class="flex items-end">
                            <label for="activo" class="flex w-full items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Plan activo</p>
                                    <p class="text-xs text-gray-500">Disponible para nuevas contrataciones</p>
                                </div>
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </label>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="descripcion" class="mb-2 block text-sm font-semibold text-gray-700">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" rows="4"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Describe el enfoque, alcance y valor del plan.">{{ old('descripcion') }}</textarea>
                        </div>

                        
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Bloque 2</p>
                            <h2 class="mt-2 text-2xl font-bold text-gray-900">Caracteristicas del plan</h2>
                            <p class="mt-2 text-sm text-gray-500">Ordena beneficios y marca como destacados los mas importantes.</p>
                        </div>
                        <button type="button" id="add-feature" class="inline-flex items-center justify-center rounded-full bg-purple-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <span class="mr-2 text-base">+</span>
                            Agregar caracteristica
                        </button>
                    </div>

                    <div class="px-6 py-6 sm:px-8">
                        <div id="features-container" class="space-y-4">
                            <p class="rounded-2xl border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-900">Arrastra las tarjetas para cambiar el orden en que se guardaran las caracteristicas.</p>
                            @foreach($featuresToShow as $index => $caracteristica)
                                <div class="feature-card rounded-3xl border border-gray-200 bg-gray-50 p-5 shadow-sm transition hover:border-blue-300 hover:bg-white cursor-move" data-feature-card draggable="true">
                                    <div class="mb-4 flex items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-purple-700 ring-1 ring-purple-200">
                <span class="text-sm">::</span>
                Arrastrar
            </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Caracter√≠stica <span class="feature-number text-blue-700">{{ $index + 1 }}</span></p>
                                            <p class="text-xs text-gray-500">Configura cantidad, frecuencia y prioridad visual.</p>
                                        </div>
                                        <button type="button" class="remove-feature inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
                                        <div class="xl:col-span-6">
                                            <label class="mb-2 block text-sm font-semibold text-gray-700">Caracter√≠stica *</label>
                                            <div class="flex items-center gap-3">
                                            <select class="feature-select w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" name="caracteristicas[{{ $index }}][id]" required>
                                                <option value="">Selecciona una caracteristica</option>
                                                @foreach($caracteristicas as $car)
                                                    <option value="{{ $car->id }}" {{ $caracteristica['id'] == $car->id ? 'selected' : '' }}>{{ $car->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="open-feature-modal inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-900 text-xl font-bold text-white transition hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-100" title="Crear nueva caracteristica" aria-label="Crear nueva caracteristica">
                                                +
                                            </button>
                                        </div>
                                            @error('caracteristicas.'.$index.'.id')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="xl:col-span-2">
                                            <label class="mb-2 block text-sm font-semibold text-gray-700">Cantidad</label>
                                            <input type="number" min="1" name="caracteristicas[{{ $index }}][cantidad]" value="{{ $caracteristica['cantidad'] ?? 1 }}"
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                        </div>

                                        <div class="xl:col-span-4">
                                            <label class="mb-2 block text-sm font-semibold text-gray-700">Frecuencia</label>
                                            <input type="text" name="caracteristicas[{{ $index }}][frecuencia]" value="{{ $caracteristica['frecuencia'] ?? '' }}"
                                                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                                placeholder="Ej: semanal o mensual">
                                        </div>
                                    </div>

                                    <label class="mt-4 flex items-center justify-between rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                                        <div>
                                            <p class="font-semibold">Marcar como destacado</p>
                                            <p class="text-xs text-yellow-700">Ayuda a resaltar este beneficio en la interfaz del cliente.</p>
                                        </div>
                                        <input type="checkbox" name="caracteristicas[{{ $index }}][es_destacado]" value="1" {{ isset($caracteristica['es_destacado']) && $caracteristica['es_destacado'] ? 'checked' : '' }}
                                            class="h-5 w-5 rounded border-yellow-300 text-yellow-500 focus:ring-yellow-400">
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex justify-center">
                            <button type="button" class="add-feature-trigger inline-flex items-center justify-center rounded-full bg-purple-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                <span class="mr-2 text-base">+</span>
                                Agregar caracteristica
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
                <section class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Vista rapida</p>
                        <h2 class="mt-2 text-xl font-bold text-gray-900">Resumen del plan</h2>
                    </div>
                    <div class="space-y-4 px-6 py-6">
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Nombre</p>
                            <p id="plan-summary-name" class="mt-2 text-base font-semibold text-gray-900">{{ old('nombre') ?: 'Sin nombre' }}</p>
                            <p id="plan-summary-subtitle" class="mt-1 text-sm text-gray-500">{{ old('subtitulo') ?: 'Agrega un subtitulo corto para este plan.' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Precio</p>
                            <p id="plan-summary-price" class="mt-2 text-base font-semibold text-gray-900">{{ number_format((float) old('precio', 0), 2, '.', ',') }} {{ old('moneda', 'BS') }}</p>
                            <p id="plan-summary-period" class="mt-1 text-sm text-gray-500">Facturacion por {{ old('periodo_facturacion', 'mes') }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Moneda</p>
                                <p id="plan-summary-currency" class="mt-2 text-base font-semibold text-gray-900">{{ old('moneda', 'BS') }}</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Periodo</p>
                                <p id="plan-summary-billing" class="mt-2 text-base font-semibold text-gray-900">{{ old('periodo_facturacion', 'mes') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Estado</p>
                                <p id="plan-summary-status" class="mt-2 text-base font-semibold text-gray-900">{{ old('activo', true) ? 'Activo' : 'Inactivo' }}</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Orden</p>
                                <p id="plan-summary-order" class="mt-2 text-base font-semibold text-gray-900">{{ old('orden', 0) }}</p>
                            </div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Descripcion</p>
                            <p id="plan-summary-description" class="mt-2 text-sm leading-6 text-gray-700">{{ old('descripcion') ?: 'La descripcion aparecera aqui cuando empieces a escribir.' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Caracteristicas en uso</p>
    <div id="feature-summary-list" class="mt-3 flex flex-wrap gap-2">
        @forelse($caracteristicas->whereIn('id', $selectedFeatureIds) as $feature)
            <button type="button"
                class="open-feature-edit-modal inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 transition hover:bg-gray-100"
                data-feature-id="{{ $feature->id }}"
                data-feature-name="{{ $feature->nombre }}"
                title="Editar caracteristica {{ $feature->nombre }}">
                <span>{{ $feature->nombre }}</span>
                <span class="text-sm text-purple-700">&#9998;</span>
            </button>
        @empty
            <span class="text-sm text-gray-500">Agrega al menos una caracteristica.</span>
        @endforelse
    </div>
</div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl bg-gray-900 text-white shadow-sm">
                    <div class="px-6 py-6">
                        <h2 class="text-xl font-bold">Listo para guardar</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-300">Cuando termines, guarda los cambios para crear el plan y sus caracteristicas asociadas.</p>
                        <div class="mt-6 space-y-3">
                            <button type="submit" class="w-full rounded-2xl bg-purple-400 px-5 py-3 text-sm font-semibold text-gray-900 transition hover:bg-blue-300">
                                Crear plan
                            </button>
                            <a href="{{ route('administrador.planes.index') }}" class="block w-full rounded-2xl border border-white border-opacity-20 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-white hover:bg-opacity-10">
                                Cancelar
                            </a>
                        </div>

                        
                    </div>
                </section>
            </aside>
        </form>
    </div>
</div>

<div id="feature-template" class="hidden">
    <div class="feature-card rounded-3xl border border-gray-200 bg-gray-50 p-5 shadow-sm transition hover:border-blue-300 hover:bg-white cursor-move" data-feature-card draggable="true">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-purple-700 ring-1 ring-purple-200">
                <span class="text-sm">::</span>
                Arrastrar
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Caracteristica <span class="feature-number text-blue-700"></span></p>
                <p class="text-xs text-gray-500">Configura cantidad, frecuencia y prioridad visual.</p>
            </div>
            <button type="button" class="remove-feature inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                Eliminar
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
            <div class="xl:col-span-6">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Caracteristica *</label>
                <div class="flex items-center gap-3">
                    <select class="feature-select w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" name="caracteristicas[__index__][id]" required>
                        <option value="">Selecciona una caracteristica</option>
                        @foreach($caracteristicas as $caracteristica)
                            <option value="{{ $caracteristica->id }}">{{ $caracteristica->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="open-feature-modal inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-900 text-xl font-bold text-white transition hover:bg-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-100" title="Crear nueva caracteristica" aria-label="Crear nueva caracteristica">
                        +
                    </button>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Cantidad</label>
                <input type="number" min="1" name="caracteristicas[__index__][cantidad]" value="1"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="xl:col-span-4">
                <label class="mb-2 block text-sm font-semibold text-gray-700">Frecuencia</label>
                <input type="text" name="caracteristicas[__index__][frecuencia]"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    placeholder="Ej: semanal o mensual">
            </div>
        </div>

        <label class="mt-4 flex items-center justify-between rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
            <div>
                <p class="font-semibold">Marcar como destacado</p>
                <p class="text-xs text-yellow-700">Ayuda a resaltar este beneficio en la interfaz del cliente.</p>
            </div>
            <input type="checkbox" name="caracteristicas[__index__][es_destacado]" value="1"
                class="h-5 w-5 rounded border-yellow-300 text-yellow-500 focus:ring-yellow-400">
        </label>
    </div>
</div>

<div id="feature-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 px-4">
    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p id="feature-modal-kicker" class="text-xs font-semibold uppercase tracking-[0.24em] text-purple-700">Nueva caracteristica</p>
                <h3 id="feature-modal-title" class="mt-2 text-2xl font-bold text-gray-900">Crear caracteristica</h3>
                <p id="feature-modal-description-text" class="mt-2 text-sm text-gray-500">Agrega una nueva opcion sin salir de la creacion del plan.</p>
            </div>
            <button type="button" id="close-feature-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-500 transition hover:bg-gray-200 hover:text-gray-700" aria-label="Cerrar modal">
                x
            </button>
        </div>

        <form id="feature-modal-form" class="mt-6 space-y-4">
            <div>
                <label for="feature-modal-name" class="mb-2 block text-sm font-semibold text-gray-700">Nombre *</label>
                <input type="text" id="feature-modal-name" name="nombre" required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-100"
                    placeholder="Ej: Reportes avanzados">
            </div>
<div id="feature-modal-error" class="hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" id="cancel-feature-modal" class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" id="save-feature-modal" class="rounded-2xl bg-purple-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800" data-create-label="Guardar caracteristica" data-edit-label="Guardar cambios">
                    Guardar caracteristica
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-feature');
    const addButtons = document.querySelectorAll('.add-feature-trigger');
    const container = document.getElementById('features-container');
    const template = document.getElementById('feature-template');
    const form = container.closest('form');
    const modal = document.getElementById('feature-modal');
    const modalKicker = document.getElementById('feature-modal-kicker');
    const modalTitle = document.getElementById('feature-modal-title');
    const modalDescriptionText = document.getElementById('feature-modal-description-text');
    const modalForm = document.getElementById('feature-modal-form');
    const modalName = document.getElementById('feature-modal-name');
    const modalError = document.getElementById('feature-modal-error');
    const closeModalButton = document.getElementById('close-feature-modal');
    const cancelModalButton = document.getElementById('cancel-feature-modal');
    const saveModalButton = document.getElementById('save-feature-modal');
    const summaryName = document.getElementById('plan-summary-name');
    const summarySubtitle = document.getElementById('plan-summary-subtitle');
    const summaryPrice = document.getElementById('plan-summary-price');
    const summaryPeriod = document.getElementById('plan-summary-period');
    const summaryCurrency = document.getElementById('plan-summary-currency');
    const summaryBilling = document.getElementById('plan-summary-billing');
    const summaryStatus = document.getElementById('plan-summary-status');
    const summaryOrder = document.getElementById('plan-summary-order');
    const summaryDescription = document.getElementById('plan-summary-description');
    const nameInput = document.getElementById('nombre');
    const subtitleInput = document.getElementById('subtitulo');
    const priceInput = document.getElementById('precio');
    const currencyInput = document.getElementById('moneda');
    const billingInput = document.getElementById('periodo_facturacion');
    const orderInput = document.getElementById('orden');
    const activeInput = document.getElementById('activo');
    const descriptionInput = document.getElementById('descripcion');
    const storeFeatureUrl = @json(route('administrador.planes.caracteristicas.store'));
    const updateFeatureUrlTemplate = @json(route('administrador.planes.caracteristicas.update', ['caracteristica' => '__ID__']));
    const csrfToken = @json(csrf_token());
    let featureCount = {{ count($featuresToShow) }};
    let activeSelect = null;
    let activeFeature = null;
    let draggedCard = null;
    function closeAllSearchableSelects(exceptWrapper) {
        document.querySelectorAll('[data-searchable-select]').forEach(function (wrapper) {
            if (exceptWrapper && wrapper === exceptWrapper) {
                return;
            }
            wrapper.classList.remove('is-open');
            const dropdown = wrapper.querySelector('[data-searchable-dropdown]');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        });
    }
    function refreshSearchableSelectOptions(select) {
        const wrapper = select.parentElement.querySelector('[data-searchable-select]');
        if (!wrapper) {
            return;
        }
        const dropdown = wrapper.querySelector('[data-searchable-dropdown]');
        const searchInput = wrapper.querySelector('[data-searchable-input]');
        if (!dropdown || !searchInput) {
            return;
        }
        const searchTerm = searchInput.value.trim().toLowerCase();
        const selectedOption = select.options[select.selectedIndex];
        const placeholder = select.dataset.placeholder || 'Selecciona una caracteristica';
        let visibleCount = 0;
        dropdown.innerHTML = '';
        const selectedIdsInOtherRows = getSelectedFeatureIds(select);
        Array.from(select.options).forEach(function (option) {
            if (!option.value) {
                return;
            }
            if (selectedIdsInOtherRows.includes(String(option.value)) && select.value !== option.value) {
                return;
            }
            if (searchTerm && !option.text.toLowerCase().includes(searchTerm)) {
                return;
            }
            visibleCount += 1;
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'w-full rounded-xl px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-blue-50 hover:text-blue-700';
            optionButton.dataset.searchableOptionValue = option.value;
            optionButton.textContent = option.text;
            if (select.value === option.value) {
                optionButton.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
            }
            optionButton.addEventListener('click', function () {
                select.value = option.value;
                searchInput.value = option.text;
                searchInput.dataset.selectedLabel = option.text;
                closeAllSearchableSelects();
                refreshSearchableSelectOptions(select);
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
            dropdown.appendChild(optionButton);
        });
        if (!visibleCount) {
            const emptyState = document.createElement('div');
            emptyState.className = 'px-3 py-2 text-sm text-gray-500';
            emptyState.textContent = 'No se encontraron coincidencias.';
            dropdown.appendChild(emptyState);
        }
        if (selectedOption && selectedOption.value) {
            searchInput.dataset.selectedLabel = selectedOption.text;
            if (!wrapper.classList.contains('is-open')) {
                searchInput.value = selectedOption.text;
            }
        } else if (!wrapper.classList.contains('is-open')) {
            searchInput.value = '';
            searchInput.dataset.selectedLabel = '';
            searchInput.placeholder = placeholder;
        }
    }
    function initializeSearchableSelect(select) {
        if (!select || select.dataset.searchableInitialized === 'true') {
            return;
        }
        select.dataset.searchableInitialized = 'true';
        select.dataset.placeholder = select.options[0] ? select.options[0].text : 'Selecciona una caracteristica';
        select.classList.add('hidden');
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';
        wrapper.setAttribute('data-searchable-select', 'true');
        const trigger = document.createElement('div');
        trigger.className = 'flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-100';
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.autocomplete = 'off';
        searchInput.className = 'w-full bg-transparent text-gray-900 outline-none placeholder:text-gray-400';
        searchInput.placeholder = select.dataset.placeholder;
        searchInput.setAttribute('data-searchable-input', 'true');
        const icon = document.createElement('button');
        icon.type = 'button';
        icon.className = 'shrink-0 text-sm font-semibold text-gray-400 transition hover:text-blue-600';
        icon.innerHTML = '&#9662;';
        const dropdown = document.createElement('div');
        dropdown.className = 'absolute left-0 right-0 z-20 mt-2 hidden max-h-64 overflow-y-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-xl';
        dropdown.setAttribute('data-searchable-dropdown', 'true');
        trigger.appendChild(searchInput);
        trigger.appendChild(icon);
        wrapper.appendChild(trigger);
        wrapper.appendChild(dropdown);
        select.insertAdjacentElement('afterend', wrapper);
        function openDropdown() {
            closeAllSearchableSelects(wrapper);
            wrapper.classList.add('is-open');
            dropdown.classList.remove('hidden');
            refreshSearchableSelectOptions(select);
        }
        searchInput.addEventListener('focus', openDropdown);
        searchInput.addEventListener('click', openDropdown);
        icon.addEventListener('click', function () {
            if (wrapper.classList.contains('is-open')) {
                closeAllSearchableSelects();
            } else {
                openDropdown();
                searchInput.focus();
            }
        });
        searchInput.addEventListener('input', function () {
            wrapper.classList.add('is-open');
            dropdown.classList.remove('hidden');
            refreshSearchableSelectOptions(select);
        });
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllSearchableSelects();
                searchInput.value = searchInput.dataset.selectedLabel || '';
                return;
            }
            if (event.key === 'Enter') {
                const firstOption = dropdown.querySelector('[data-searchable-option-value]');
                if (firstOption) {
                    event.preventDefault();
                    firstOption.click();
                }
            }
        });
        searchInput.addEventListener('blur', function () {
            setTimeout(function () {
                if (!wrapper.contains(document.activeElement)) {


                    closeAllSearchableSelects();
                    searchInput.value = searchInput.dataset.selectedLabel || '';
                }
            }, 120);
        });
        select.addEventListener('change', function () {
            validateUniqueFeatures();
            renderFeatureSummary();
            document.querySelectorAll('.feature-select').forEach(function (currentSelect) {
                refreshSearchableSelectOptions(currentSelect);
            });
        });
        refreshSearchableSelectOptions(select);
    }
    function initializeAllSearchableSelects(scope) {
        (scope || document).querySelectorAll('.feature-select').forEach(function (select) {
            initializeSearchableSelect(select);
        });
    }

    function formatPreviewPrice(value) {
        const numericValue = Number.parseFloat(value);
        if (Number.isNaN(numericValue)) {
            return '0.00';
        }

        return numericValue.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }
    function renderPlanSummary() {
        if (summaryName) {
            summaryName.textContent = nameInput && nameInput.value.trim() ? nameInput.value.trim() : 'Sin nombre';
        }
        if (summarySubtitle) {
            summarySubtitle.textContent = subtitleInput && subtitleInput.value.trim()
                ? subtitleInput.value.trim()
                : 'Agrega un subtitulo corto para este plan.';
        }
        if (summaryPrice) {
            const currencyValue = currencyInput ? currencyInput.value : 'BS';
            summaryPrice.textContent = formatPreviewPrice(priceInput ? priceInput.value : 0) + ' ' + currencyValue;
        }
        if (summaryPeriod) {
            summaryPeriod.textContent = 'Facturacion por ' + (billingInput && billingInput.value ? billingInput.value : 'mes');
        }
        if (summaryCurrency) {
            summaryCurrency.textContent = currencyInput && currencyInput.value ? currencyInput.value : 'BS';
        }
        if (summaryBilling) {
            summaryBilling.textContent = billingInput && billingInput.value ? billingInput.value : 'mes';
        }
        if (summaryStatus) {
            summaryStatus.textContent = activeInput && activeInput.checked ? 'Activo' : 'Inactivo';
        }
        if (summaryOrder) {
            summaryOrder.textContent = orderInput && orderInput.value !== '' ? orderInput.value : '0';
        }
        if (summaryDescription) {
            summaryDescription.textContent = descriptionInput && descriptionInput.value.trim()
                ? descriptionInput.value.trim()
                : 'La descripcion aparecera aqui cuando empieces a escribir.';
        }
    }
    function syncFeatureIndexes() {
        container.querySelectorAll('[data-feature-card]').forEach(function (card, index) {
            const number = card.querySelector('.feature-number');
            if (number) {
                number.textContent = index + 1;
            }
            card.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (field) {
                field.name = field.name.replace(/caracteristicas\[\d+\]/, 'caracteristicas[' + index + ']');
            });
        });
        validateUniqueFeatures();
        renderFeatureSummary();
    }

    function updateFeatureBadges(feature) {
        document.querySelectorAll('.open-feature-edit-modal[data-feature-id="' + feature.id + '"]').forEach(function (button) {
            button.dataset.featureName = feature.nombre;
            const label = button.querySelector('span');
            if (label) {
                label.textContent = feature.nombre;
            }
            button.title = 'Editar caracteristica ' + feature.nombre;
        });
    }

    function renderFeatureSummary() {
        const summaryList = document.getElementById('feature-summary-list');
        if (!summaryList) {
            return;
        }

        const orderedFeatureIds = [];
        container.querySelectorAll('[data-feature-card] .feature-select').forEach(function (select) {
            if (select.value && !orderedFeatureIds.includes(select.value)) {
                orderedFeatureIds.push(select.value);
            }
        });

        if (!orderedFeatureIds.length) {
            summaryList.innerHTML = '<span class="text-sm text-gray-500">Agrega al menos una caracteristica.</span>';
            return;
        }

        summaryList.innerHTML = '';
        orderedFeatureIds.forEach(function (featureId) {
            const option = Array.from(document.querySelectorAll('.feature-select option')).find(function (item) {
                return item.value === featureId;
            });

            if (!option) {
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'open-feature-edit-modal inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 transition hover:bg-gray-100';
            button.dataset.featureId = featureId;
            button.dataset.featureName = option.textContent;
            button.title = 'Editar caracteristica ' + option.textContent;
            button.innerHTML = '<span>' + option.textContent + '</span><span class="text-sm text-purple-700">&#9998;</span>';
            summaryList.appendChild(button);
        });
    }

    function getSelectedFeatureIds(currentSelect) {
        const selectedIds = [];
        container.querySelectorAll('.feature-select').forEach(function (select) {
            if (select !== currentSelect && select.value) {
                selectedIds.push(String(select.value));
            }
        });
        return selectedIds;
    }
    function setFeatureDuplicateState(select, hasDuplicate) {
        const card = select.closest('[data-feature-card]');
        const wrapper = select.parentElement.querySelector('[data-searchable-select]');
        let errorMessage = card ? card.querySelector('[data-feature-duplicate-error]') : null;
        if (!card || !wrapper) {
            return;
        }
        if (!errorMessage) {
            errorMessage = document.createElement('p');
            errorMessage.className = 'mt-2 hidden text-sm text-red-600';
            errorMessage.setAttribute('data-feature-duplicate-error', 'true');
            wrapper.insertAdjacentElement('afterend', errorMessage);
        }
        if (hasDuplicate) {
            wrapper.classList.add('border-red-300', 'ring-4', 'ring-red-100');
            errorMessage.textContent = 'Esta caracteristica ya fue seleccionada en otra fila.';
            errorMessage.classList.remove('hidden');
        } else {
            wrapper.classList.remove('border-red-300', 'ring-4', 'ring-red-100');
            errorMessage.textContent = '';
            errorMessage.classList.add('hidden');
        }
    }
    function validateUniqueFeatures() {
        const seen = new Set();
        let hasDuplicates = false;
        container.querySelectorAll('.feature-select').forEach(function (select) {
            setFeatureDuplicateState(select, false);
        });
        container.querySelectorAll('.feature-select').forEach(function (select) {
            if (!select.value) {
                refreshSearchableSelectOptions(select);
                return;
            }
            const selectedValue = String(select.value);
            if (seen.has(selectedValue)) {
                hasDuplicates = true;
                setFeatureDuplicateState(select, true);
            } else {
                seen.add(selectedValue);
            }
            refreshSearchableSelectOptions(select);
        });
        return !hasDuplicates;
    }

    function addFeatureOptionToAllSelects(feature) {
        const selects = document.querySelectorAll('.feature-select');
        selects.forEach(function (select) {
            const existingOption = Array.from(select.options).find(function (option) {
                return option.value === String(feature.id);
            });
            if (existingOption) {
                existingOption.textContent = feature.nombre;
                refreshSearchableSelectOptions(select);
                return;
            }
            const option = document.createElement('option');
            option.value = feature.id;
            option.textContent = feature.nombre;
            select.appendChild(option);
            refreshSearchableSelectOptions(select);
        });
    }

    function openFeatureModal(selectElement, featureData) {
        activeSelect = selectElement || null;
        activeFeature = featureData || null;
        modalForm.reset();
        modalError.textContent = '';
        modalError.classList.add('hidden');

        if (activeFeature) {
            modalKicker.textContent = 'Editar caracteristica';
            modalTitle.textContent = 'Actualizar caracteristica';
            modalDescriptionText.textContent = 'Modifica el nombre de esta caracteristica.';
            modalName.value = activeFeature.nombre || '';
            saveModalButton.textContent = saveModalButton.dataset.editLabel;
        } else {
            modalKicker.textContent = 'Nueva caracteristica';
            modalTitle.textContent = 'Crear caracteristica';
            modalDescriptionText.textContent = 'Agrega una nueva opcion sin salir de la creacion del plan.';
            saveModalButton.textContent = saveModalButton.dataset.createLabel;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(function () {
            modalName.focus();
        }, 0);
    }

    function closeFeatureModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeSelect = null;
        activeFeature = null;
        saveModalButton.textContent = saveModalButton.dataset.createLabel;
    }

    function addFeature() {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replace(/__index__/g, featureCount).trim();
        const newFeature = wrapper.firstElementChild;
        container.appendChild(newFeature);
        initializeAllSearchableSelects(newFeature);
        featureCount += 1;
        syncFeatureIndexes();
    }

    function getDragAfterElement(containerElement, y) {
        const draggableElements = [...containerElement.querySelectorAll('[data-feature-card]:not(.dragging)')];

        return draggableElements.reduce(function (closest, child) {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    [addButton, ...addButtons].forEach(function (button) {
        if (!button) {
            return;
        }
        button.addEventListener('click', function () {
            addFeature();
        });
    });
    [nameInput, subtitleInput, priceInput, currencyInput, billingInput, orderInput, activeInput, descriptionInput].forEach(function (field) {
        if (!field) {
            return;
        }
        field.addEventListener('input', renderPlanSummary);
        field.addEventListener('change', renderPlanSummary);
    });
    form.addEventListener('submit', function (event) {
        syncFeatureIndexes();
        if (!validateUniqueFeatures()) {
            event.preventDefault();
        }
    });
    closeModalButton.addEventListener('click', closeFeatureModal);
    cancelModalButton.addEventListener('click', closeFeatureModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeFeatureModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('flex')) {
            closeFeatureModal();
        }
    });
    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-searchable-select]')) {
            closeAllSearchableSelects();
        }
    });

    container.addEventListener('dragstart', function (event) {
        const card = event.target.closest('[data-feature-card]');
        if (!card) {
            return;
        }

        draggedCard = card;
        card.classList.add('dragging', 'opacity-60', 'ring-2', 'ring-purple-300');
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    container.addEventListener('dragend', function (event) {
        const card = event.target.closest('[data-feature-card]');
        if (!card) {
            return;
        }

        card.classList.remove('dragging', 'opacity-60', 'ring-2', 'ring-purple-300');
        draggedCard = null;
        syncFeatureIndexes();
    });

    container.addEventListener('dragover', function (event) {
        event.preventDefault();
        if (!draggedCard) {
            return;
        }

        const afterElement = getDragAfterElement(container, event.clientY);
        if (!afterElement) {
            container.appendChild(draggedCard);
        } else if (afterElement !== draggedCard) {
            container.insertBefore(draggedCard, afterElement);
        }
    });

    container.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-feature');
        if (removeButton) {
            const card = removeButton.closest('[data-feature-card]');
            if (card) {
                card.remove();
                syncFeatureIndexes();
            }
            return;
        }

        const openModalButton = event.target.closest('.open-feature-modal');
        if (openModalButton) {
            const card = openModalButton.closest('[data-feature-card]');
            const select = card ? card.querySelector('.feature-select') : null;
            openFeatureModal(select, null);
        }
    });

    document.addEventListener('click', function (event) {
        const editModalButton = event.target.closest('.open-feature-edit-modal');
        if (!editModalButton) {
            return;
        }

        openFeatureModal(null, {
            id: editModalButton.dataset.featureId,
            nombre: editModalButton.dataset.featureName,
        });
    });

    modalForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        modalError.textContent = '';
        modalError.classList.add('hidden');
        saveModalButton.disabled = true;
        saveModalButton.textContent = 'Guardando...';

        try {
            const isEditing = Boolean(activeFeature && activeFeature.id);
            const requestUrl = isEditing
                ? updateFeatureUrlTemplate.replace('__ID__', activeFeature.id)
                : storeFeatureUrl;
            const requestMethod = isEditing ? 'PUT' : 'POST';

            const response = await fetch(requestUrl, {
                method: requestMethod,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    nombre: modalName.value.trim(),
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(firstError || data.message || 'No se pudo guardar la caracteristica.');
            }

            addFeatureOptionToAllSelects(data.caracteristica);
            updateFeatureBadges(data.caracteristica);

            if (activeSelect) {
                activeSelect.value = String(data.caracteristica.id);
                activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
            document.querySelectorAll('.feature-select').forEach(function (select) {
                refreshSearchableSelectOptions(select);
            });

            renderFeatureSummary();
            closeFeatureModal();
        } catch (error) {
            modalError.textContent = error.message;
            modalError.classList.remove('hidden');
        } finally {
            saveModalButton.disabled = false;
            saveModalButton.textContent = activeFeature ? saveModalButton.dataset.editLabel : saveModalButton.dataset.createLabel;
        }
    });

    if (featureCount === 0) {
        addFeature();
    } else {
        initializeAllSearchableSelects(container);
        syncFeatureIndexes();
    }
    renderPlanSummary();
    renderFeatureSummary();
});
</script>
@endpush
@endsection


























