@extends('layouts.app2')

@section('title', 'Cuestionario de Informacion de Empresa')

@section('content')
@php
    $temasAgrupados = $temas->groupBy('nombre_tema')->map(function ($grupo) {
        $temaBase = $grupo->first();
        $temaBase->descripcion_tema = $grupo->pluck('descripcion_tema')->filter()->first();
        $temaBase->preguntas = $grupo
            ->flatMap(function ($tema) {
                return $tema->preguntas;
            })
            ->unique(function ($pregunta) {
                return mb_strtolower(trim($pregunta->pregunta));
            })
            ->sortBy('orden')
            ->values();

        return $temaBase;
    })->values();

    // Calcular si una sección está completa (todas las preguntas tienen respuesta)
    function seccionCompleta($tema, $respuestasExistentes) {
        foreach ($tema->preguntas as $pregunta) {
            if ($pregunta->requerido && empty($respuestasExistentes[$pregunta->id])) {
                return false;
            }
        }
        return true;
    }
@endphp

<div class="min-h-screen" style="background: linear-gradient(135deg, #EEF2FF 0%, #FFFFFF 50%, #F5F3FF 100%);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 rounded-2xl overflow-hidden relative rp-banner">
            <div class="rp-banner-overlay absolute inset-0"></div>
            <div class="relative z-10 px-8 py-8 md:px-10 md:py-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold text-white/90 mb-4" style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.22);">
                            <i class="fas fa-list-check text-sm"></i>
                            Cuestionario empresarial
                        </div>
                        <h1 class="text-3xl font-bold text-white">Completa la información de tu empresa</h1>
                        <p class="mt-2 max-w-2xl text-sm md:text-base" style="color: #dbeafe;">
                            Responde cada sección paso a paso para que podamos entender mejor tu empresa y ayudarte con tu estrategia digital.
                        </p>
                    </div>
                    <div class="rounded-2xl px-6 py-5 text-white min-w-[260px]" style="background: rgba(15,23,42,0.26); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/70">Empresa</p>
                        <p class="mt-2 text-2xl font-bold">{{ $empresa->nombre_empresa }}</p>
                        <p class="mt-1 text-sm text-white/80">{{ $empresa->tipo_empresa }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4 mb-6">
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold">No se pudo guardar el cuestionario.</p>
                    <ul class="mt-2 list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-6 md:px-8 border-b border-gray-100 bg-white">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Avance del cuestionario</p>
                            <h2 class="mt-1 text-2xl font-bold text-gray-900">Sección <span id="current-step-label">1</span> de {{ $temasAgrupados->count() }}</h2>
                        </div>
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-gray-700">
                            <p class="font-semibold text-gray-900">{{ $empresa->nombre_empresa }}</p>
                            @if($empresa->descripcion)
                                <p class="mt-1 text-gray-600">{{ $empresa->descripcion }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3" id="steps-indicator">
                        @foreach($temasAgrupados as $tema)
                            @php
                                $completa = seccionCompleta($tema, $respuestasExistentes);
                            @endphp
                            <div class="step-chip flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-3 py-3 min-w-[150px] {{ $completa ? 'border-emerald-200 bg-emerald-50' : '' }}" data-step-chip="{{ $loop->index }}">
                                <div class="step-chip-number flex h-10 w-10 items-center justify-center rounded-full {{ $completa ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500' }} text-sm font-bold transition-all duration-200">
                                    {{ $loop->iteration }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide {{ $completa ? 'text-emerald-600' : 'text-gray-400' }}">Sección {{ $loop->iteration }}</p>
                                    <p class="text-sm font-semibold {{ $completa ? 'text-emerald-700' : 'text-gray-700' }} truncate">{{ $tema->nombre_tema }}</p>
                                </div>
                                @if($completa)
                                    <i class="fas fa-check-circle text-emerald-500 text-sm flex-shrink-0"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <form action="{{ route('empresas.cuestionario.store', $empresa->id) }}" method="POST" class="p-6 md:p-8" id="cuestionario-form">
                @csrf

                @foreach($temasAgrupados as $tema)
                    <section class="question-step {{ $loop->first ? '' : 'hidden' }}" data-step="{{ $loop->index }}">
                        <div class="rounded-3xl border border-indigo-100 overflow-hidden bg-gradient-to-br from-white via-indigo-50/40 to-blue-50/60">
                            <div class="px-6 py-6 md:px-8 border-b border-indigo-100 bg-white/80">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700 border border-indigo-200">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-indigo-700">{{ $loop->iteration }}</span>
                                            Sección {{ $loop->iteration }}
                                        </div>
                                        <h3 class="mt-4 text-2xl font-bold text-gray-900">{{ $tema->nombre_tema }}</h3>
                                        @if($tema->descripcion_tema)
                                            <p class="mt-2 text-gray-600 leading-7 max-w-3xl">{{ $tema->descripcion_tema }}</p>
                                        @endif
                                    </div>
                                    <div class="rounded-2xl px-4 py-3 text-sm text-slate-700 border border-slate-200 bg-white shadow-sm">
                                        <p class="font-semibold text-slate-900">Paso {{ $loop->iteration }}</p>
                                        <p>{{ $tema->preguntas->count() }} preguntas en esta sección</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 md:p-8 space-y-6">
                                @foreach($tema->preguntas as $pregunta)
                                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                        <label for="respuesta_{{ $pregunta->id }}" class="block text-base font-semibold text-gray-900 mb-2">
                                            {{ $pregunta->pregunta }}
                                            @if($pregunta->requerido)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>

                                        @if($pregunta->ayuda)
                                            <p class="text-sm text-gray-500 mb-3">{{ $pregunta->ayuda }}</p>
                                        @endif

                                        @if($pregunta->tipo_respuesta === 'texto_largo')
                                            <textarea
                                                id="respuesta_{{ $pregunta->id }}"
                                                name="respuesta_{{ $pregunta->id }}"
                                                rows="5"
                                                class="question-input w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-all duration-200 resize-none"
                                                data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                @if($pregunta->requerido) required @endif
                                            >{{ $respuestasExistentes[$pregunta->id] ?? '' }}</textarea>
                                        @else
                                            <input
                                                type="text"
                                                id="respuesta_{{ $pregunta->id }}"
                                                name="respuesta_{{ $pregunta->id }}"
                                                value="{{ $respuestasExistentes[$pregunta->id] ?? '' }}"
                                                class="question-input w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 focus:bg-white transition-all duration-200"
                                                data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                @if($pregunta->requerido) required @endif
                                            >
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="mt-8 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-gray-100 pt-6">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('empresas.show', $empresa->id) }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Salir
                        </a>
                        <button type="button" id="prev-step" class="hidden inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200">
                            <i class="fas fa-chevron-left mr-2"></i>
                            Anterior
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" id="next-step" class="inline-flex items-center justify-center px-6 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #4f46e5;">
                            Continuar
                            <i class="fas fa-chevron-right ml-2"></i>
                        </button>
                        <button type="submit" id="submit-form" class="hidden inline-flex items-center justify-center px-6 py-3 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5" style="background: #a7b838;">
                            <i class="fas fa-check-circle mr-2"></i>
                            Completar cuestionario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const steps = Array.from(document.querySelectorAll('.question-step'));
        const stepChips = Array.from(document.querySelectorAll('[data-step-chip]'));
        const currentStepLabel = document.getElementById('current-step-label');
        const prevButton = document.getElementById('prev-step');
        const nextButton = document.getElementById('next-step');
        const submitButton = document.getElementById('submit-form');
        const form = document.getElementById('cuestionario-form');
        let currentStep = 0;

        function setStepState(stepElement, enabled) {
            const fields = stepElement.querySelectorAll('input, textarea, select');
            fields.forEach(function (field) {
                if (enabled) {
                    field.disabled = false;
                    if (field.dataset.required === '1') {
                        field.required = true;
                    }
                } else {
                    field.disabled = true;
                    field.required = false;
                }
            });
        }

        function updateStepUI() {
            steps.forEach(function (step, index) {
                const isActive = index === currentStep;
                step.classList.toggle('hidden', !isActive);
                setStepState(step, isActive);
            });

            stepChips.forEach(function (chip, index) {
                const circle = chip.querySelector('.step-chip-number');
                const title = chip.querySelector('p:last-child');

                chip.classList.remove('border-indigo-200', 'bg-indigo-50', 'border-emerald-200', 'bg-emerald-50');
                circle.classList.remove('bg-indigo-600', 'text-white', 'bg-emerald-500', 'text-gray-500', 'bg-gray-100');
                title.classList.remove('text-indigo-700', 'text-emerald-700', 'text-gray-700');

                if (index < currentStep) {
                    chip.classList.add('border-emerald-200', 'bg-emerald-50');
                    circle.classList.add('bg-emerald-500', 'text-white');
                    title.classList.add('text-emerald-700');
                } else if (index === currentStep) {
                    chip.classList.add('border-indigo-200', 'bg-indigo-50');
                    circle.classList.add('bg-indigo-600', 'text-white');
                    title.classList.add('text-indigo-700');
                } else {
                    circle.classList.add('bg-gray-100', 'text-gray-500');
                    title.classList.add('text-gray-700');
                }
            });

            currentStepLabel.textContent = currentStep + 1;
            prevButton.classList.toggle('hidden', currentStep === 0);
            nextButton.classList.toggle('hidden', currentStep === steps.length - 1);
            submitButton.classList.toggle('hidden', currentStep !== steps.length - 1);
        }

        function validateCurrentStep() {
            const currentFields = steps[currentStep].querySelectorAll('input, textarea, select');

            for (const field of currentFields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    field.focus();
                    return false;
                }
            }

            return true;
        }

        nextButton.addEventListener('click', function () {
            if (!validateCurrentStep()) {
                return;
            }

            if (currentStep < steps.length - 1) {
                currentStep += 1;
                updateStepUI();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        prevButton.addEventListener('click', function () {
            if (currentStep > 0) {
                currentStep -= 1;
                updateStepUI();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        form.addEventListener('submit', function () {
            steps.forEach(function (step) {
                setStepState(step, true);
            });
        });

        updateStepUI();
    });
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

        #steps-indicator {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
