@extends('layouts.app')

@section('title', 'Solicitudes web')

@section('content')
<div class="contact-requests-page min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <section class="contact-hero relative overflow-hidden rounded-2xl mb-7">
            <div class="contact-hero-shape" aria-hidden="true"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 px-6 sm:px-8 py-8">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-white/20">
                        <i class="fas fa-comments text-2xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-cyan-100 uppercase mb-1">Contáctanos</p>
                        <h1 class="text-3xl font-bold text-white">Hablemos de tu Proyecto</h1>
                        <p class="text-sm text-blue-100 mt-1">Solicitudes recibidas desde el formulario del sitio web.</p>
                    </div>
                </div>
                <a href="{{ url('/#contact') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center justify-center rounded-xl border border-white/25 bg-white/15 px-4 py-2.5 font-semibold text-white transition hover:bg-white/25">
                    <i class="fas fa-external-link-alt mr-2 text-sm"></i>
                    Ver formulario público
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-7" aria-label="Resumen de solicitudes">
            <div class="contact-stat bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total recibidas</p>
                <div class="flex items-end justify-between mt-2">
                    <strong class="text-3xl text-gray-900">{{ $stats['total'] }}</strong>
                    <i class="fas fa-inbox text-2xl text-indigo-500"></i>
                </div>
            </div>
            <div class="contact-stat bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Este mes</p>
                <div class="flex items-end justify-between mt-2">
                    <strong class="text-3xl text-gray-900">{{ $stats['este_mes'] }}</strong>
                    <i class="fas fa-calendar-alt text-2xl text-cyan-600"></i>
                </div>
            </div>
            <div class="contact-stat bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Confirmación enviada</p>
                <div class="flex items-end justify-between mt-2">
                    <strong class="text-3xl text-gray-900">{{ $stats['correo_enviado'] }}</strong>
                    <i class="fas fa-envelope-circle-check text-2xl text-emerald-500"></i>
                </div>
            </div>
            <div class="contact-stat bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sin confirmación</p>
                <div class="flex items-end justify-between mt-2">
                    <strong class="text-3xl text-gray-900">{{ $stats['correo_pendiente'] }}</strong>
                    <i class="fas fa-envelope-open-text text-2xl text-amber-500"></i>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-7">
            <form method="GET" action="{{ route('administrador.solicitudes-contacto.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label for="buscar" class="block text-sm font-semibold text-gray-700 mb-1.5">Buscar solicitud</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input id="buscar" name="buscar" type="search" value="{{ request('buscar') }}"
                               placeholder="Nombre, correo, teléfono o mensaje"
                               class="w-full rounded-xl border border-gray-300 py-2.5 pl-10 pr-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label for="servicio" class="block text-sm font-semibold text-gray-700 mb-1.5">Servicio</label>
                    <select id="servicio" name="servicio" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <option value="">Todos los servicios</option>
                        @foreach ($servicios as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(request('servicio') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="envio" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmación</label>
                    <select id="envio" name="envio" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                        <option value="">Todos</option>
                        <option value="enviado" @selected(request('envio') === 'enviado')>Enviada</option>
                        <option value="no_enviado" @selected(request('envio') === 'no_enviado')>No enviada</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 font-semibold text-white transition hover:bg-indigo-700">
                        Filtrar
                    </button>
                    @if (request()->hasAny(['buscar', 'servicio', 'envio']))
                        <a href="{{ route('administrador.solicitudes-contacto.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 px-3 text-gray-600 hover:bg-gray-50" title="Limpiar filtros" aria-label="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <div class="flex items-center justify-between gap-4 mb-4">
            <p class="text-sm text-gray-600">
                Mostrando <strong>{{ $solicitudes->count() }}</strong> de <strong>{{ $solicitudes->total() }}</strong> solicitudes
            </p>
        </div>

        <section class="space-y-4">
            @forelse ($solicitudes as $solicitud)
                <article class="contact-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-12 w-12 flex-none items-center justify-center rounded-xl bg-indigo-50 text-lg font-bold text-indigo-600">
                                    {{ mb_strtoupper(mb_substr($solicitud->nombre, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg font-bold text-gray-900">{{ $solicitud->nombre }}</h2>
                                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                            {{ $solicitud->servicio_nombre }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-5 gap-y-2 mt-2 text-sm text-gray-600">
                                        <a href="mailto:{{ $solicitud->correo }}" class="hover:text-indigo-600">
                                            <i class="fas fa-envelope mr-1.5 text-gray-400"></i>{{ $solicitud->correo }}
                                        </a>
                                        @if ($solicitud->telefono)
                                            <a href="tel:{{ $solicitud->telefono }}" class="hover:text-indigo-600">
                                                <i class="fas fa-phone mr-1.5 text-gray-400"></i>{{ $solicitud->telefono }}
                                            </a>
                                        @else
                                            <span class="text-gray-400"><i class="fas fa-phone-slash mr-1.5"></i>Sin teléfono</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col lg:items-end gap-2 text-sm">
                                <time class="font-medium text-gray-700" datetime="{{ $solicitud->created_at->toIso8601String() }}">
                                    <i class="far fa-clock mr-1 text-gray-400"></i>{{ $solicitud->created_at->format('d/m/Y H:i') }}
                                </time>
                                @if ($solicitud->correo_enviado_at)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <i class="fas fa-check-circle mr-1.5"></i>Confirmación enviada
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        <i class="fas fa-exclamation-circle mr-1.5"></i>Confirmación no enviada
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2">Cuéntanos sobre tu proyecto</p>
                            <p class="whitespace-pre-line break-words text-sm leading-6 text-gray-700">{{ $solicitud->mensaje }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-4">
                            <a href="mailto:{{ $solicitud->correo }}?subject={{ rawurlencode('Respuesta a tu solicitud en PRODOVI') }}"
                               class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                <i class="fas fa-reply mr-2"></i>Responder por correo
                            </a>
                            @if ($solicitud->telefono)
                                @php
                                    $telefonoWhatsApp = ltrim((string) $solicitud->telefono, '0');
                                    $telefonoWhatsApp = str_starts_with($telefonoWhatsApp, '591')
                                        ? $telefonoWhatsApp
                                        : '591'.$telefonoWhatsApp;
                                @endphp
                                <a href="https://wa.me/{{ $telefonoWhatsApp }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    <i class="fab fa-whatsapp mr-2"></i>Contactar por WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                        <i class="fas fa-inbox text-2xl text-gray-400"></i>
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-gray-800">No hay solicitudes para mostrar</h2>
                    <p class="mt-1 text-sm text-gray-500">Cuando alguien complete “Hablemos de tu Proyecto”, aparecerá aquí.</p>
                </div>
            @endforelse
        </section>

        @if ($solicitudes->hasPages())
            <div class="mt-7">{{ $solicitudes->links() }}</div>
        @endif
    </div>
</div>

<style>
    .contact-requests-page {
        background: linear-gradient(135deg, #eef2ff 0%, #ffffff 48%, #ecfeff 100%);
    }
    .contact-hero {
        background: linear-gradient(135deg, #312e81 0%, #4f46e5 52%, #0891b2 100%);
        box-shadow: 0 18px 45px rgba(49, 46, 129, .2);
    }
    .contact-hero-shape {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 10% 0%, rgba(255,255,255,.24), transparent 34%),
                    radial-gradient(circle at 92% 100%, rgba(255,255,255,.17), transparent 38%);
    }
    .contact-stat, .contact-card { transition: transform .2s ease, box-shadow .2s ease; }
    .contact-stat:hover, .contact-card:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(15, 23, 42, .08); }
</style>
@endsection
