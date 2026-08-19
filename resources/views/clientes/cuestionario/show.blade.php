@extends('layouts.app2')

@section('title', 'Cuestionario de Información de Empresa')

@section('content')

@php
    $temasAgrupados = $temas->groupBy('nombre_tema')->map(function ($grupo) {
        $temaBase = $grupo->first();

        $temaBase->descripcion_tema = $grupo
            ->pluck('descripcion_tema')
            ->filter()
            ->first();

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


    function seccionCompleta($tema, $respuestasExistentes)
    {
        foreach ($tema->preguntas as $pregunta) {
            if (
                $pregunta->requerido &&
                empty($respuestasExistentes[$pregunta->id])
            ) {
                return false;
            }
        }

        return true;
    }
@endphp


<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    rel="stylesheet"
>


<div id="company-questionnaire">

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <header class="questionnaire-hero">

        <div class="questionnaire-hero-content">

            <span class="questionnaire-kicker">
                <i class="fas fa-list-check"></i>
                Información de tu marca
            </span>

            <h1>
                Cuestionario de tu
                <span>empresa</span>
            </h1>

            <p>
                Responde cada sección paso a paso.
                Esta información nos permitirá conocer mejor tu empresa
                y preparar una estrategia adaptada a tus necesidades.
            </p>

        </div>


        <div class="questionnaire-hero-side">

            <div class="questionnaire-hero-company">

                <small>
                    Empresa
                </small>

                <strong>
                    {{ $empresa->nombre_empresa }}
                </strong>

                <span>
                    {{ $empresa->tipo_empresa }}
                </span>

            </div>


            <div class="questionnaire-hero-status">

                <small>
                    Cuestionario
                </small>

                <strong>
                    {{ $empresa->cuestionario_completado ? 'Completado' : 'En proceso' }}
                </strong>

            </div>


            <div class="questionnaire-mosaic" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>

        </div>

    </header>



    {{-- =========================================================
        CONTENIDO
    ========================================================== --}}
    <main class="questionnaire-content">


        {{-- =====================================================
            MENSAJES
        ====================================================== --}}
        <div class="questionnaire-messages">

            @if($cuestionarioBloqueado)

                <div class="questionnaire-alert questionnaire-alert-locked">
                    <div class="questionnaire-alert-icon"><i class="fas fa-lock"></i></div>
                    <div>
                        <strong>Cuestionario en modo lectura</strong>
                        <p>La campaña ya comenzó. Puedes consultar tus respuestas, pero ya no es posible modificarlas.</p>
                    </div>
                </div>

            @endif

            @if(session('success'))

                <div class="questionnaire-alert questionnaire-alert-success">

                    <div class="questionnaire-alert-icon">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div>
                        <strong>Información guardada</strong>
                        <p>{{ session('success') }}</p>
                    </div>

                </div>

            @endif


            @if(session('error'))

                <div class="questionnaire-alert questionnaire-alert-error">

                    <div class="questionnaire-alert-icon">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>

                    <div>
                        <strong>No se pudo completar la acción</strong>
                        <p>{{ session('error') }}</p>
                    </div>

                </div>

            @endif


            @if($errors->any())

                <div class="questionnaire-alert questionnaire-alert-error">

                    <div class="questionnaire-alert-icon">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>

                    <div>

                        <strong>
                            No se pudo guardar el cuestionario
                        </strong>

                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>

                </div>

            @endif

        </div>



        {{-- =====================================================
            PANEL DE PROGRESO
        ====================================================== --}}
        <section class="progress-panel">

            <div class="progress-panel-heading">

                <div class="progress-heading-icon">
                    <i class="fas fa-chart-line"></i>
                </div>

                <div class="progress-heading-text">

                    <span>
                        Avance del cuestionario
                    </span>

                    <h2>
                        Sección
                        <strong id="current-step-label">1</strong>
                        de
                        {{ $temasAgrupados->count() }}
                    </h2>

                </div>


                <div class="progress-company-summary">

                    <small>
                        Empresa seleccionada
                    </small>

                    <strong>
                        {{ $empresa->nombre_empresa }}
                    </strong>

                    @if($empresa->descripcion)
                        <p>
                            {{ $empresa->descripcion }}
                        </p>
                    @endif

                </div>

            </div>


            {{-- BARRA DE PROGRESO --}}
            <div class="progress-bar-wrapper">

                <div class="progress-bar-header">

                    <span>
                        Progreso
                    </span>

                    <strong id="progress-percent">
                        0%
                    </strong>

                </div>

                <div class="progress-bar">

                    <div
                        class="progress-bar-value"
                        id="progress-bar-value"
                    ></div>

                </div>

            </div>


            {{-- INDICADORES DE SECCIONES --}}
            <div
                class="steps-indicator"
                id="steps-indicator"
            >

                @foreach($temasAgrupados as $tema)

                    @php
                        $completa = seccionCompleta(
                            $tema,
                            $respuestasExistentes
                        );
                    @endphp


                    <div
                        class="step-chip {{ $completa ? 'is-complete' : '' }}"
                        data-step-chip="{{ $loop->index }}"
                        data-complete="{{ $completa ? '1' : '0' }}"
                    >

                        <div class="step-chip-number">

                            <span class="step-number">
                                {{ $loop->iteration }}
                            </span>

                            <i class="fas fa-check"></i>

                        </div>


                        <div class="step-chip-content">

                            <span>
                                Sección {{ $loop->iteration }}
                            </span>

                            <strong>
                                {{ $tema->nombre_tema }}
                            </strong>

                        </div>

                    </div>

                @endforeach

            </div>

        </section>



        {{-- =====================================================
            FORMULARIO
        ====================================================== --}}
        <form
            action="{{ route('empresas.cuestionario.store', $empresa->id) }}"
            method="POST"
            id="cuestionario-form"
            novalidate
        >

            @csrf

            <fieldset class="questionnaire-fields {{ $cuestionarioBloqueado ? 'is-locked' : '' }}" {{ $cuestionarioBloqueado ? 'disabled' : '' }}>


            @foreach($temasAgrupados as $tema)

                <section
                    class="question-step"
                    data-step="{{ $loop->index }}"
                    {{ !$loop->first ? 'hidden' : '' }}
                >

                    <div class="question-section-panel">


                        {{-- =========================================
                            ENCABEZADO DE SECCIÓN
                        ========================================== --}}
                        <div class="question-section-heading">

                            <div class="question-section-heading-main">

                                <div class="section-number">
                                    {{ $loop->iteration }}
                                </div>


                                <div>

                                    <span class="section-kicker">
                                        Sección {{ $loop->iteration }}
                                    </span>

                                    <h3>
                                        {{ $tema->nombre_tema }}
                                    </h3>


                                    @if($tema->descripcion_tema)

                                        <p>
                                            {{ $tema->descripcion_tema }}
                                        </p>

                                    @endif

                                </div>

                            </div>


                            <div class="section-question-count">

                                <i class="fas fa-message"></i>

                                <div>

                                    <small>
                                        Preguntas
                                    </small>

                                    <strong>
                                        {{ $tema->preguntas->count() }}
                                    </strong>

                                </div>

                            </div>

                        </div>



                        {{-- =========================================
                            PREGUNTAS
                        ========================================== --}}
                        <div class="questions-container">


                            @foreach($tema->preguntas as $pregunta)

                                @php

                                    $respuestaActual =
                                        $respuestasExistentes[$pregunta->id]
                                        ?? '';

                                    $respuestasMarcadas =
                                        array_map(
                                            'trim',
                                            explode(
                                                ' | ',
                                                $respuestaActual
                                            )
                                        );

                                    $respuestaOtro =
                                        collect($respuestasMarcadas)
                                            ->first(
                                                fn ($valor) =>
                                                str_starts_with(
                                                    $valor,
                                                    'Otro: '
                                                )
                                            );

                                @endphp



                                <article class="question-card">


                                    {{-- PREGUNTA --}}
                                    <div class="question-header">

                                        <div class="question-number">
                                            {{ $loop->iteration }}
                                        </div>


                                        <div class="question-title-wrapper">

                                            <label
                                                for="respuesta_{{ $pregunta->id }}"
                                                class="question-title"
                                            >

                                                {{ $pregunta->pregunta }}

                                                @if($pregunta->requerido)

                                                    <span
                                                        class="required-mark"
                                                        title="Pregunta obligatoria"
                                                    >
                                                        *
                                                    </span>

                                                @endif

                                            </label>


                                            @if($pregunta->ayuda)

                                                <p class="question-help">

                                                    <i class="far fa-circle-question"></i>

                                                    {{ $pregunta->ayuda }}

                                                </p>

                                            @endif

                                        </div>

                                    </div>



                                    {{-- =====================================
                                        TEXTO LARGO
                                    ====================================== --}}
                                    @if($pregunta->tipo_respuesta === 'texto_largo')

                                        <div class="field-wrapper">

                                            <textarea
                                                id="respuesta_{{ $pregunta->id }}"
                                                name="respuesta_{{ $pregunta->id }}"
                                                rows="5"
                                                class="question-field auto-grow"
                                                placeholder="Escribe tu respuesta aquí..."
                                                data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                @if($pregunta->requerido) required @endif
                                            >{{ $respuestaActual }}</textarea>

                                        </div>



                                    {{-- =====================================
                                        OPCIÓN ÚNICA / RADIO
                                    ====================================== --}}
                                    @elseif($pregunta->tipo_respuesta === 'opcion_multiple')

                                        <div class="choices-grid">

                                            @foreach($pregunta->opciones ?? [] as $opcion)

                                                @php

                                                    $seleccionada =
                                                        in_array(
                                                            $opcion,
                                                            $respuestasMarcadas,
                                                            true
                                                        )
                                                        ||
                                                        (
                                                            $opcion === 'Otro'
                                                            &&
                                                            $respuestaOtro
                                                        );

                                                @endphp


                                                <label
                                                    class="choice-card radio-choice {{ $seleccionada ? 'is-selected' : '' }}"
                                                >

                                                    <input
                                                        type="radio"
                                                        name="respuesta_{{ $pregunta->id }}"
                                                        value="{{ $opcion }}"
                                                        class="choice-native question-choice"
                                                        data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                        {{ $seleccionada ? 'checked' : '' }}
                                                        {{ $pregunta->requerido ? 'required' : '' }}
                                                    >


                                                    <span class="choice-control"></span>


                                                    <span class="choice-text">

                                                        {{ $opcion }}

                                                    </span>

                                                </label>

                                            @endforeach

                                        </div>



                                        {{-- CAMPO OTRO --}}
                                        @if(in_array('Otro', $pregunta->opciones ?? [], true))

                                            <div class="other-answer">

                                                <label>
                                                    Especifica tu respuesta
                                                </label>

                                                <textarea
                                                    name="respuesta_{{ $pregunta->id }}_otro"
                                                    rows="1"
                                                    class="question-field auto-grow"
                                                    placeholder="Especifica otra opción..."
                                                >{{ $respuestaOtro ? str_replace('Otro: ', '', $respuestaOtro) : '' }}</textarea>

                                            </div>

                                        @endif



                                    {{-- =====================================
                                        CHECKBOX PERSONALIZADO
                                    ====================================== --}}
                                    @elseif($pregunta->tipo_respuesta === 'checkbox')

                                        <div
                                            class="choices-grid"
                                            data-checkbox-group
                                            data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                        >

                                            @foreach($pregunta->opciones ?? [] as $opcion)

                                                @php

                                                    $seleccionada =
                                                        in_array(
                                                            $opcion,
                                                            $respuestasMarcadas,
                                                            true
                                                        )
                                                        ||
                                                        (
                                                            $opcion === 'Otro'
                                                            &&
                                                            $respuestaOtro
                                                        );

                                                @endphp


                                                <label
                                                    class="choice-card checkbox-choice {{ $seleccionada ? 'is-selected' : '' }}"
                                                >

                                                    <input
                                                        type="checkbox"
                                                        name="respuesta_{{ $pregunta->id }}[]"
                                                        value="{{ $opcion }}"
                                                        class="choice-native question-choice"
                                                        {{ $seleccionada ? 'checked' : '' }}
                                                    >


                                                    <span class="choice-control">

                                                        <i class="fas fa-check"></i>

                                                    </span>


                                                    <span class="choice-text">

                                                        {{ $opcion }}

                                                    </span>

                                                </label>

                                            @endforeach

                                        </div>



                                        {{-- CAMPO OTRO --}}
                                        @if(in_array('Otro', $pregunta->opciones ?? [], true))

                                            <div class="other-answer">

                                                <label>
                                                    Cuéntanos cuál
                                                </label>

                                                <textarea
                                                    name="respuesta_{{ $pregunta->id }}_otro"
                                                    rows="1"
                                                    class="question-field auto-grow"
                                                    placeholder="Escribe otra opción..."
                                                >{{ $respuestaOtro ? str_replace('Otro: ', '', $respuestaOtro) : '' }}</textarea>

                                            </div>

                                        @endif



                                    {{-- =====================================
                                        SELECT / DROPDOWN PERSONALIZADO
                                    ====================================== --}}
                                    @elseif(
                                        in_array(
                                            $pregunta->tipo_respuesta,
                                            [
                                                'select',
                                                'dropdown',
                                                'lista_desplegable'
                                            ],
                                            true
                                        )
                                    )

                                        <div class="custom-select-wrapper">

                                            <select
                                                id="respuesta_{{ $pregunta->id }}"
                                                name="respuesta_{{ $pregunta->id }}"
                                                class="custom-select question-select"
                                                data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                @if($pregunta->requerido) required @endif
                                            >

                                                <option value="">
                                                    Selecciona una opción
                                                </option>


                                                @foreach($pregunta->opciones ?? [] as $opcion)

                                                    <option
                                                        value="{{ $opcion }}"
                                                        {{ $respuestaActual === $opcion ? 'selected' : '' }}
                                                    >
                                                        {{ $opcion }}
                                                    </option>

                                                @endforeach

                                            </select>


                                            <span class="custom-select-icon">

                                                <i class="fas fa-chevron-down"></i>

                                            </span>

                                        </div>



                                    {{-- =====================================
                                        TEXTO NORMAL
                                        AHORA AUTOEXPANDIBLE
                                    ====================================== --}}
                                    @else

                                        <div class="field-wrapper">

                                            <textarea
                                                id="respuesta_{{ $pregunta->id }}"
                                                name="respuesta_{{ $pregunta->id }}"
                                                rows="1"
                                                class="question-field auto-grow compact-textarea"
                                                placeholder="Escribe tu respuesta aquí..."
                                                data-required="{{ $pregunta->requerido ? '1' : '0' }}"
                                                @if($pregunta->requerido) required @endif
                                            >{{ $respuestaActual }}</textarea>

                                        </div>

                                    @endif

                                </article>

                            @endforeach

                        </div>

                    </div>

                </section>

            @endforeach

            </fieldset>



            {{-- =================================================
                NAVEGACIÓN
            ================================================== --}}
            <div class="questionnaire-navigation">


                <div class="navigation-left">

                    <a
                        href="{{ route('empresas.show', $empresa->id) }}"
                        class="questionnaire-button button-exit"
                    >

                        <i class="fas fa-arrow-left"></i>

                        <span>
                            Salir
                        </span>

                    </a>


                    <button
                        type="button"
                        id="prev-step"
                        class="questionnaire-button button-secondary"
                        hidden
                    >

                        <i class="fas fa-chevron-left"></i>

                        <span>
                            Anterior
                        </span>

                    </button>

                </div>



                <div class="navigation-right">

                    <button
                        type="button"
                        id="next-step"
                        class="questionnaire-button button-primary"
                    >

                        <span>
                            Continuar
                        </span>

                        <i class="fas fa-chevron-right"></i>

                    </button>


                    <button
                        type="submit"
                        id="submit-form"
                        class="questionnaire-button button-complete"
                        hidden
                    >

                        <i class="fas fa-circle-check"></i>

                        <span>
                            Completar cuestionario
                        </span>

                    </button>

                </div>

            </div>

        </form>

    </main>

</div>



{{-- =============================================================
    JAVASCRIPT
============================================================== --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const questionnaireLocked = @json($cuestionarioBloqueado);

    const questionnaire =
        document.getElementById('company-questionnaire');

    const form =
        document.getElementById('cuestionario-form');

    const steps =
        Array.from(
            document.querySelectorAll('.question-step')
        );

    const stepChips =
        Array.from(
            document.querySelectorAll('[data-step-chip]')
        );

    const currentStepLabel =
        document.getElementById('current-step-label');

    const progressPercent =
        document.getElementById('progress-percent');

    const progressBar =
        document.getElementById('progress-bar-value');

    const prevButton =
        document.getElementById('prev-step');

    const nextButton =
        document.getElementById('next-step');

    const submitButton =
        document.getElementById('submit-form');


    let currentStep = 0;



    /* =========================================================
        AUTO EXPANDIR TEXTAREA
    ========================================================== */

    function autoGrow(field) {

        if (!field) {
            return;
        }

        field.style.height = 'auto';

        field.style.height =
            Math.max(
                field.scrollHeight,
                field.classList.contains('compact-textarea')
                    ? 52
                    : 110
            ) + 'px';

    }



    function resizeFieldsInside(container) {

        if (!container) {
            return;
        }

        const fields =
            container.querySelectorAll('.auto-grow');

        fields.forEach(function (field) {

            autoGrow(field);

        });

    }



    document
        .querySelectorAll('.auto-grow')
        .forEach(function (field) {

            autoGrow(field);

            field.addEventListener(
                'input',
                function () {
                    autoGrow(this);
                }
            );

        });



    /* =========================================================
        ESTADO DE CHECKBOX / RADIO
    ========================================================== */

    function syncChoiceState(input) {

        const card =
            input.closest('.choice-card');

        if (!card) {
            return;
        }

        card.classList.toggle(
            'is-selected',
            input.checked
        );

    }



    document
        .querySelectorAll('.question-choice')
        .forEach(function (input) {

            syncChoiceState(input);

            input.addEventListener(
                'change',
                function () {

                    /*
                     * RADIO:
                     * quitamos visual seleccionado
                     * a los hermanos.
                     */
                    if (this.type === 'radio') {

                        const group =
                            document.querySelectorAll(
                                'input[type="radio"][name="' +
                                CSS.escape(this.name) +
                                '"]'
                            );

                        group.forEach(function (radio) {

                            syncChoiceState(radio);

                        });

                    } else {

                        syncChoiceState(this);

                    }

                }
            );

        });



    /* =========================================================
        HABILITAR / DESHABILITAR CAMPOS DE SECCIONES
    ========================================================== */

    function setStepState(stepElement, enabled) {

        const fields =
            stepElement.querySelectorAll(
                'input, textarea, select'
            );

        fields.forEach(function (field) {

            if (questionnaireLocked) {

                field.disabled = true;
                field.required = false;
                return;

            }

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



    /* =========================================================
        ACTUALIZAR INTERFAZ
    ========================================================== */

    function updateStepUI() {

        if (!steps.length) {
            return;
        }



        steps.forEach(function (step, index) {

            const isActive =
                index === currentStep;

            step.hidden = !isActive;

            setStepState(
                step,
                isActive
            );

            if (isActive) {

                requestAnimationFrame(function () {

                    resizeFieldsInside(step);

                });

            }

        });



        stepChips.forEach(function (chip, index) {

            chip.classList.remove(
                'is-active',
                'is-complete',
                'is-future'
            );


            const wasComplete =
                chip.dataset.complete === '1';


            if (index === currentStep) {

                chip.classList.add('is-active');

            } else if (
                index < currentStep ||
                wasComplete
            ) {

                chip.classList.add('is-complete');

            } else {

                chip.classList.add('is-future');

            }

        });



        currentStepLabel.textContent =
            currentStep + 1;


        const percentage =
            Math.round(
                ((currentStep + 1) / steps.length) * 100
            );


        progressPercent.textContent =
            percentage + '%';


        progressBar.style.width =
            percentage + '%';



        prevButton.hidden =
            currentStep === 0;


        const hasNextSection =
            currentStep < steps.length - 1;

        nextButton.hidden = false;
        nextButton.disabled = !hasNextSection;
        nextButton.setAttribute(
            'aria-disabled',
            String(!hasNextSection)
        );


        submitButton.hidden =
            questionnaireLocked || currentStep !== steps.length - 1;

    }



    /* =========================================================
        VALIDACIÓN
    ========================================================== */

    function validateCurrentStep() {

        const currentSection =
            steps[currentStep];


        /*
         * CHECKBOX OBLIGATORIO:
         * al menos uno debe estar seleccionado.
         */
        const requiredCheckboxGroups =
            currentSection.querySelectorAll(
                '[data-checkbox-group][data-required="1"]'
            );


        for (const group of requiredCheckboxGroups) {

            const checked =
                group.querySelector(
                    'input[type="checkbox"]:checked'
                );


            if (!checked) {

                group.classList.add('has-error');


                const firstOption =
                    group.querySelector('.choice-card');


                if (firstOption) {

                    firstOption.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                }


                setTimeout(function () {

                    group.classList.remove('has-error');

                }, 2400);


                alert(
                    'Selecciona al menos una opción para continuar.'
                );


                return false;

            }

        }



        const currentFields =
            currentSection.querySelectorAll(
                'input, textarea, select'
            );


        for (const field of currentFields) {

            if (!field.checkValidity()) {

                field.reportValidity();

                field.focus({
                    preventScroll: true
                });


                const questionCard =
                    field.closest('.question-card');


                if (questionCard) {

                    questionCard.classList.add(
                        'has-validation-error'
                    );


                    questionCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });


                    setTimeout(function () {

                        questionCard.classList.remove(
                            'has-validation-error'
                        );

                    }, 2400);

                }


                return false;

            }

        }


        return true;

    }



    /* =========================================================
        SIGUIENTE
    ========================================================== */

    nextButton.addEventListener(
        'click',
        function () {

            if (!questionnaireLocked && !validateCurrentStep()) {

                return;

            }


            if (
                currentStep <
                steps.length - 1
            ) {

                currentStep += 1;

                updateStepUI();


                questionnaire.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }

        }
    );



    /* =========================================================
        ANTERIOR
    ========================================================== */

    prevButton.addEventListener(
        'click',
        function () {

            if (currentStep > 0) {

                currentStep -= 1;

                updateStepUI();


                questionnaire.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }

        }
    );



    /* =========================================================
        SUBMIT
    ========================================================== */

    form.addEventListener(
        'submit',
        function (event) {

            if (questionnaireLocked) {

                event.preventDefault();
                return;

            }

            if (!questionnaireLocked && !validateCurrentStep()) {

                event.preventDefault();

                return;

            }


            /*
             * Importante:
             * habilitamos las demás secciones antes
             * del submit para enviar todas las respuestas.
             */
            steps.forEach(function (step) {

                setStepState(
                    step,
                    true
                );

            });

        }
    );



    /* =========================================================
        INICIALIZAR
    ========================================================== */

    updateStepUI();

});
</script>



{{-- =============================================================
    ESTILOS
============================================================== --}}
<style>

/* =============================================================
   VARIABLES / BASE
============================================================= */

#company-questionnaire {

    --purple: #5b2b76;
    --purple-hover: #4d2365;
    --purple-soft: #f4eef7;

    --turquoise: #117e8c;
    --turquoise-hover: #0d6d79;
    --turquoise-soft: #edf7f8;

    --orange: #ee9f2b;
    --orange-soft: #fff7ea;

    --green: #7da533;
    --green-soft: #f3f7eb;

    --red: #c74a4a;
    --red-soft: #fff0f0;

    --dark: #242426;

    --text: #302834;
    --text-secondary: #6f6573;
    --text-muted: #887d8c;

    --border: #ded7e1;
    --border-light: #ebe6ed;

    --panel-heading: #f7f5f8;

    min-height: 100vh;

    background:
        #ffffff;

    color:
        var(--text);

}


/* =============================================================
   HERO
============================================================= */

#company-questionnaire .questionnaire-hero {

    min-height: 175px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 32px;

    padding: 30px 34px;

    background: var(--dark);

    color: #ffffff;

}


#company-questionnaire .questionnaire-hero-content {

    max-width: 720px;

}


#company-questionnaire .questionnaire-kicker {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 11px;

    color: var(--turquoise);

    font-size: .68rem;

    font-weight: 900;

    letter-spacing: .13em;

    text-transform: uppercase;

}


#company-questionnaire .questionnaire-hero h1 {

    margin: 0;

    color: #ffffff;

    font-size: clamp(
        1.75rem,
        3vw,
        2.45rem
    );

    font-weight: 850;

    line-height: 1.08;

    letter-spacing: -.035em;

}


#company-questionnaire .questionnaire-hero h1 span {

    color: var(--turquoise);

}


#company-questionnaire .questionnaire-hero-content > p {

    max-width: 700px;

    margin: 12px 0 0;

    color: #aaa5ad;

    font-size: .86rem;

    line-height: 1.6;

}


#company-questionnaire .questionnaire-hero-side {

    display: flex;

    align-items: center;

    gap: 18px;

}


#company-questionnaire .questionnaire-hero-company,
#company-questionnaire .questionnaire-hero-status {

    min-width: 145px;

    padding: 13px 16px;

    border-left:
        4px solid var(--turquoise);

    background: #303033;

}


#company-questionnaire .questionnaire-hero-company {

    min-width: 190px;

    border-left-color:
        var(--purple);

}


#company-questionnaire .questionnaire-hero-company small,
#company-questionnaire .questionnaire-hero-company strong,
#company-questionnaire .questionnaire-hero-company span,
#company-questionnaire .questionnaire-hero-status small,
#company-questionnaire .questionnaire-hero-status strong {

    display: block;

}


#company-questionnaire .questionnaire-hero-company small,
#company-questionnaire .questionnaire-hero-status small {

    color: #aaa5ad;

    font-size: .62rem;

    font-weight: 800;

    letter-spacing: .08em;

    text-transform: uppercase;

}


#company-questionnaire .questionnaire-hero-company strong,
#company-questionnaire .questionnaire-hero-status strong {

    margin-top: 4px;

    color: #ffffff;

    font-size: .86rem;

    font-weight: 850;

}


#company-questionnaire .questionnaire-hero-company span {

    margin-top: 3px;

    color: #aaa5ad;

    font-size: .68rem;

}


/* =============================================================
   MOSAICO
============================================================= */

#company-questionnaire .questionnaire-mosaic {

    width: 144px;

    height: 96px;

    display: grid;

    flex: 0 0 auto;

    grid-template-columns:
        repeat(3, 1fr);

    grid-template-rows:
        repeat(2, 1fr);

}


#company-questionnaire .questionnaire-mosaic span:nth-child(1) {

    background: #ef6c22;

    border-radius:
        100% 0 0 0;

}


#company-questionnaire .questionnaire-mosaic span:nth-child(2) {

    background: #f5a900;

    border-radius:
        0 0 0 100%;

}


#company-questionnaire .questionnaire-mosaic span:nth-child(3) {

    background:
        var(--purple);

    border-radius:
        100% 0 100% 0;

}


#company-questionnaire .questionnaire-mosaic span:nth-child(4) {

    background:
        var(--turquoise);

    border-radius:
        0 100% 0 100%;

}


#company-questionnaire .questionnaire-mosaic span:nth-child(5) {

    background:
        var(--green);

    border-radius: 50%;

}


#company-questionnaire .questionnaire-mosaic span:nth-child(6) {

    border:
        12px solid #607078;

    border-top-color:
        transparent;

    border-left-color:
        transparent;

    border-radius: 50%;

    transform:
        rotate(45deg);

}


/* =============================================================
   CONTENT
============================================================= */

#company-questionnaire .questionnaire-content {

    max-width: 1280px;

    margin: 0 auto;

    padding:
        32px;

}


/* =============================================================
   ALERTAS
============================================================= */

#company-questionnaire .questionnaire-messages {

    display: grid;

    gap: 12px;

    margin-bottom: 18px;

}


#company-questionnaire .questionnaire-alert {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    padding: 14px 16px;

    border-left:
        4px solid;

    font-size: .8rem;

}


#company-questionnaire .questionnaire-alert-icon {

    width: 32px;

    height: 32px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 50%;

}


#company-questionnaire .questionnaire-alert strong {

    display: block;

    margin-bottom: 3px;

    font-size: .78rem;

    font-weight: 900;

}


#company-questionnaire .questionnaire-alert p {

    margin: 0;

    line-height: 1.5;

}


#company-questionnaire .questionnaire-alert ul {

    margin: 7px 0 0 18px;

    padding: 0;

}


#company-questionnaire .questionnaire-alert-success {

    border-color:
        var(--green);

    background:
        var(--green-soft);

    color: #587923;

}


#company-questionnaire .questionnaire-alert-success
.questionnaire-alert-icon {

    background:
        var(--green);

    color: #ffffff;

}


#company-questionnaire .questionnaire-alert-error {

    border-color:
        var(--red);

    background:
        var(--red-soft);

    color: #9c3737;

}


#company-questionnaire .questionnaire-alert-error
.questionnaire-alert-icon {

    background:
        var(--red);

    color: #ffffff;

}


/* =============================================================
   PANEL DE PROGRESO
============================================================= */

#company-questionnaire .progress-panel {

    overflow: hidden;

    margin-bottom: 24px;

    border:
        1px solid var(--border);

    border-radius: 5px;

    background: #ffffff;

    box-shadow:
        0 10px 28px #ded9e0;

}


#company-questionnaire .progress-panel-heading {

    display: grid;

    grid-template-columns:
        auto minmax(0, 1fr) minmax(230px, 360px);

    align-items: center;

    gap: 13px;

    padding:
        18px 20px;

    border-bottom:
        1px solid var(--border);

    border-left:
        4px solid var(--turquoise);

    background:
        var(--panel-heading);

}


#company-questionnaire .progress-heading-icon {

    width: 40px;

    height: 40px;

    display: grid;

    place-items: center;

    border-radius: 3px;

    background:
        var(--turquoise);

    color: #ffffff;

}


#company-questionnaire .progress-heading-text > span {

    display: block;

    margin-bottom: 2px;

    color:
        var(--turquoise);

    font-size: .61rem;

    font-weight: 900;

    letter-spacing: .09em;

    text-transform: uppercase;

}


#company-questionnaire .progress-heading-text h2 {

    margin: 0;

    color:
        var(--text);

    font-size: 1rem;

    font-weight: 900;

}


#company-questionnaire .progress-heading-text h2 strong {

    color:
        var(--purple);

}


#company-questionnaire .progress-company-summary {

    padding-left: 15px;

    border-left:
        1px solid var(--border);

}


#company-questionnaire .progress-company-summary small,
#company-questionnaire .progress-company-summary strong {

    display: block;

}


#company-questionnaire .progress-company-summary small {

    color:
        var(--text-muted);

    font-size: .59rem;

    font-weight: 800;

    letter-spacing: .07em;

    text-transform: uppercase;

}


#company-questionnaire .progress-company-summary strong {

    margin-top: 3px;

    color:
        var(--text);

    font-size: .78rem;

    font-weight: 900;

}


#company-questionnaire .progress-company-summary p {

    display: -webkit-box;

    overflow: hidden;

    margin: 3px 0 0;

    color:
        var(--text-muted);

    font-size: .67rem;

    line-height: 1.4;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

}


/* =============================================================
   BARRA DE PROGRESO
============================================================= */

#company-questionnaire .progress-bar-wrapper {

    padding:
        18px 20px 5px;

}


#company-questionnaire .progress-bar-header {

    display: flex;

    justify-content: space-between;

    margin-bottom: 7px;

}


#company-questionnaire .progress-bar-header span {

    color:
        var(--text-muted);

    font-size: .62rem;

    font-weight: 800;

    letter-spacing: .06em;

    text-transform: uppercase;

}


#company-questionnaire .progress-bar-header strong {

    color:
        var(--turquoise);

    font-size: .68rem;

    font-weight: 900;

}


#company-questionnaire .progress-bar {

    width: 100%;

    height: 7px;

    overflow: hidden;

    border-radius: 100px;

    background: #ece8ed;

}


#company-questionnaire .progress-bar-value {

    width: 0;

    height: 100%;

    border-radius: inherit;

    background:
        var(--turquoise);

    transition:
        width .35s ease;

}


/* =============================================================
   STEPS
============================================================= */

#company-questionnaire .steps-indicator {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(180px, 1fr)
        );

    gap: 10px;

    padding: 16px 20px 20px;

}


#company-questionnaire .step-chip {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 0;

    padding: 10px 11px;

    border:
        1px solid var(--border);

    border-radius: 4px;

    background: #ffffff;

    transition:
        .2s ease;

}


#company-questionnaire .step-chip-number {

    position: relative;

    width: 34px;

    height: 34px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 50%;

    background: #eeebef;

    color:
        var(--text-muted);

    font-size: .7rem;

    font-weight: 900;

    transition:
        .2s ease;

}


#company-questionnaire .step-chip-number i {

    display: none;

    font-size: .68rem;

}


#company-questionnaire .step-chip-content {

    min-width: 0;

}


#company-questionnaire .step-chip-content span,
#company-questionnaire .step-chip-content strong {

    display: block;

}


#company-questionnaire .step-chip-content span {

    margin-bottom: 2px;

    color:
        var(--text-muted);

    font-size: .55rem;

    font-weight: 900;

    letter-spacing: .06em;

    text-transform: uppercase;

}


#company-questionnaire .step-chip-content strong {

    overflow: hidden;

    color:
        var(--text);

    font-size: .69rem;

    font-weight: 850;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* ACTIVO */

#company-questionnaire .step-chip.is-active {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise-soft);

}


#company-questionnaire
.step-chip.is-active
.step-chip-number {

    background:
        var(--turquoise);

    color: #ffffff;

}


#company-questionnaire
.step-chip.is-active
.step-chip-content strong {

    color:
        var(--turquoise);

}


/* COMPLETO */

#company-questionnaire .step-chip.is-complete {

    border-color:
        #cddcaa;

    background:
        var(--green-soft);

}


#company-questionnaire
.step-chip.is-complete
.step-chip-number {

    background:
        var(--green);

    color: #ffffff;

}


#company-questionnaire
.step-chip.is-complete
.step-chip-number .step-number {

    display: none;

}


#company-questionnaire
.step-chip.is-complete
.step-chip-number i {

    display: block;

}


#company-questionnaire
.step-chip.is-complete
.step-chip-content strong {

    color:
        #5d7c24;

}


/* =============================================================
   SECCIÓN
============================================================= */

#company-questionnaire .question-section-panel {

    overflow: hidden;

    border:
        1px solid var(--border);

    border-radius: 5px;

    background: #ffffff;

    box-shadow:
        0 10px 28px #ded9e0;

}


#company-questionnaire .question-section-heading {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding:
        22px 24px;

    border-bottom:
        1px solid var(--border);

    border-left:
        5px solid var(--purple);

    background:
        var(--panel-heading);

}


#company-questionnaire .question-section-heading-main {

    display: flex;

    align-items: flex-start;

    gap: 14px;

    min-width: 0;

}


#company-questionnaire .section-number {

    width: 43px;

    height: 43px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 3px;

    background:
        var(--purple);

    color: #ffffff;

    font-size: .88rem;

    font-weight: 900;

}


#company-questionnaire .section-kicker {

    display: block;

    margin-bottom: 4px;

    color:
        var(--purple);

    font-size: .59rem;

    font-weight: 900;

    letter-spacing: .1em;

    text-transform: uppercase;

}


#company-questionnaire .question-section-heading h3 {

    margin: 0;

    color:
        var(--text);

    font-size: 1.2rem;

    font-weight: 900;

    line-height: 1.25;

}


#company-questionnaire .question-section-heading p {

    max-width: 760px;

    margin:
        7px 0 0;

    color:
        var(--text-secondary);

    font-size: .78rem;

    line-height: 1.6;

}


#company-questionnaire .section-question-count {

    min-width: 115px;

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 10px 12px;

    border:
        1px solid var(--border);

    border-radius: 4px;

    background: #ffffff;

}


#company-questionnaire .section-question-count > i {

    color:
        var(--turquoise);

}


#company-questionnaire .section-question-count small,
#company-questionnaire .section-question-count strong {

    display: block;

}


#company-questionnaire .section-question-count small {

    color:
        var(--text-muted);

    font-size: .54rem;

    font-weight: 800;

    text-transform: uppercase;

}


#company-questionnaire .section-question-count strong {

    margin-top: 1px;

    color:
        var(--text);

    font-size: .78rem;

}


/* =============================================================
   CONTENEDOR DE PREGUNTAS
============================================================= */

#company-questionnaire .questions-container {

    display: grid;

    gap: 15px;

    padding: 24px;

    background: #fcfbfc;

}


/* =============================================================
   TARJETA DE PREGUNTA
============================================================= */

#company-questionnaire .question-card {

    position: relative;

    padding:
        20px;

    border:
        1px solid #e4dee6;

    border-left:
        4px solid transparent;

    border-radius: 4px;

    background: #ffffff;

    transition:
        border-color .2s ease,
        box-shadow .2s ease;

}


#company-questionnaire .question-card:focus-within {

    border-left-color:
        var(--turquoise);

    box-shadow:
        0 5px 18px rgba(
            48,
            40,
            52,
            .07
        );

}


#company-questionnaire .question-card.has-validation-error {

    border-color:
        var(--red);

    border-left-color:
        var(--red);

    animation:
        questionnaireShake .28s ease;

}


@keyframes questionnaireShake {

    0%,
    100% {
        transform:
            translateX(0);
    }

    25% {
        transform:
            translateX(-3px);
    }

    75% {
        transform:
            translateX(3px);
    }

}


/* =============================================================
   CABECERA DE PREGUNTA
============================================================= */

#company-questionnaire .question-header {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    margin-bottom: 14px;

}


#company-questionnaire .question-number {

    width: 28px;

    height: 28px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 50%;

    background:
        var(--purple-soft);

    color:
        var(--purple);

    font-size: .66rem;

    font-weight: 900;

}


#company-questionnaire .question-title-wrapper {

    min-width: 0;

    flex: 1;

}


#company-questionnaire .question-title {

    display: block;

    color:
        var(--text);

    font-size: .87rem;

    font-weight: 850;

    line-height: 1.5;

}


#company-questionnaire .required-mark {

    margin-left: 3px;

    color:
        #da4242;

    font-size: 1rem;

}


#company-questionnaire .question-help {

    display: flex;

    align-items: flex-start;

    gap: 6px;

    margin:
        5px 0 0;

    color:
        var(--text-muted);

    font-size: .69rem;

    line-height: 1.5;

}


#company-questionnaire .question-help i {

    margin-top: 2px;

    color:
        var(--turquoise);

}


/* =============================================================
   CAMPOS TEXTO
============================================================= */

#company-questionnaire .field-wrapper {

    width: 100%;

}


#company-questionnaire .question-field {

    width: 100%;

    min-height: 110px;

    box-sizing: border-box;

    overflow: hidden;

    padding:
        13px 14px;

    border:
        1px solid #d7d0da;

    border-radius: 4px;

    outline: none;

    background:
        #faf9fa;

    color:
        var(--text);

    font-family: inherit;

    font-size: .79rem;

    line-height: 1.6;

    resize: vertical;

    transition:
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;

}


#company-questionnaire .question-field.compact-textarea {

    min-height: 52px;

}


#company-questionnaire .question-field::placeholder {

    color: #aaa1ad;

}


#company-questionnaire .question-field:hover {

    border-color:
        #bfb5c3;

}


#company-questionnaire .question-field:focus {

    border-color:
        var(--turquoise);

    background: #ffffff;

    box-shadow:
        0 0 0 3px rgba(
            17,
            126,
            140,
            .10
        );

}


/* =============================================================
   OTRO
============================================================= */

#company-questionnaire .other-answer {

    margin-top: 13px;

    padding-top: 13px;

    border-top:
        1px dashed var(--border);

}


#company-questionnaire .other-answer > label {

    display: block;

    margin-bottom: 7px;

    color:
        var(--text-muted);

    font-size: .61rem;

    font-weight: 900;

    letter-spacing: .06em;

    text-transform: uppercase;

}


#company-questionnaire .other-answer .question-field {

    min-height: 52px;

}


/* =============================================================
   OPCIONES CHECKBOX / RADIO
============================================================= */

#company-questionnaire .choices-grid {

    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 9px;

}


#company-questionnaire .choice-card {

    position: relative;

    display: flex;

    align-items: center;

    gap: 11px;

    min-height: 49px;

    padding:
        10px 12px;

    border:
        1px solid #ddd6df;

    border-radius: 4px;

    background:
        #faf9fa;

    cursor: pointer;

    user-select: none;

    transition:
        border-color .18s ease,
        background .18s ease,
        transform .18s ease;

}


#company-questionnaire .choice-card:hover {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise-soft);

    transform:
        translateY(-1px);

}


/* INPUT REAL OCULTO VISUALMENTE */

#company-questionnaire .choice-native {

    position: absolute;

    width: 1px;

    height: 1px;

    overflow: hidden;

    opacity: 0;

    pointer-events: none;

}


/* CONTROL VISUAL */

#company-questionnaire .choice-control {

    width: 21px;

    height: 21px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border:
        2px solid #bdb4c1;

    background:
        #ffffff;

    color: transparent;

    transition:
        .18s ease;

}


/* CHECKBOX */

#company-questionnaire .checkbox-choice
.choice-control {

    border-radius: 4px;

}


#company-questionnaire .checkbox-choice
.choice-control i {

    font-size: .62rem;

}


/* RADIO */

#company-questionnaire .radio-choice
.choice-control {

    position: relative;

    border-radius: 50%;

}


#company-questionnaire .radio-choice
.choice-control::after {

    content: "";

    width: 9px;

    height: 9px;

    border-radius: 50%;

    background:
        transparent;

    transform:
        scale(0);

    transition:
        .15s ease;

}


/* TEXTO DE OPCIÓN */

#company-questionnaire .choice-text {

    color:
        #514557;

    font-size: .75rem;

    font-weight: 750;

    line-height: 1.4;

}


/* OPCIÓN SELECCIONADA */

#company-questionnaire .choice-card.is-selected {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise-soft);

}


#company-questionnaire
.checkbox-choice.is-selected
.choice-control {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise);

    color: #ffffff;

}


#company-questionnaire
.radio-choice.is-selected
.choice-control {

    border-color:
        var(--turquoise);

}


#company-questionnaire
.radio-choice.is-selected
.choice-control::after {

    background:
        var(--turquoise);

    transform:
        scale(1);

}


#company-questionnaire
.choice-card.is-selected
.choice-text {

    color:
        var(--turquoise);

    font-weight: 850;

}


/* FOCUS ACCESIBLE */

#company-questionnaire
.choice-native:focus-visible
+
.choice-control {

    outline:
        3px solid rgba(
            17,
            126,
            140,
            .18
        );

    outline-offset: 2px;

}


/* ERROR CHECKBOX GROUP */

#company-questionnaire
.choices-grid.has-error {

    padding: 7px;

    border:
        1px solid var(--red);

    border-radius: 5px;

    background:
        var(--red-soft);

}


/* =============================================================
   SELECT / DROPDOWN CUSTOM
============================================================= */

#company-questionnaire .custom-select-wrapper {

    position: relative;

    width: 100%;

}


#company-questionnaire .custom-select {

    width: 100%;

    min-height: 52px;

    box-sizing: border-box;

    padding:
        12px 48px 12px 14px;

    border:
        1px solid #d7d0da;

    border-radius: 4px;

    outline: none;

    background:
        #faf9fa;

    color:
        var(--text);

    font-family: inherit;

    font-size: .79rem;

    font-weight: 650;

    cursor: pointer;

    appearance: none;

    -webkit-appearance: none;

    -moz-appearance: none;

    transition:
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;

}


#company-questionnaire .custom-select:hover {

    border-color:
        #bfb5c3;

}


#company-questionnaire .custom-select:focus {

    border-color:
        var(--turquoise);

    background: #ffffff;

    box-shadow:
        0 0 0 3px rgba(
            17,
            126,
            140,
            .10
        );

}


#company-questionnaire .custom-select-icon {

    position: absolute;

    top: 50%;

    right: 14px;

    width: 27px;

    height: 27px;

    display: grid;

    place-items: center;

    border-radius: 3px;

    background:
        var(--turquoise-soft);

    color:
        var(--turquoise);

    font-size: .62rem;

    pointer-events: none;

    transform:
        translateY(-50%);

}


/* =============================================================
   NAVEGACIÓN
============================================================= */

#company-questionnaire .questionnaire-navigation {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    margin-top: 22px;

    padding-top: 20px;

    border-top:
        1px solid var(--border-light);

}


#company-questionnaire .navigation-left,
#company-questionnaire .navigation-right {

    display: flex;

    align-items: center;

    gap: 9px;

}


#company-questionnaire .questionnaire-button {

    min-height: 45px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding:
        10px 17px;

    border:
        1px solid;

    border-radius: 4px;

    font-family: inherit;

    font-size: .72rem;

    font-weight: 850;

    text-decoration: none;

    cursor: pointer;

    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease,
        transform .18s ease;

}


#company-questionnaire
.questionnaire-button:hover {

    transform:
        translateY(-1px);

}


#company-questionnaire .button-exit {

    border-color:
        var(--border);

    background: #ffffff;

    color:
        var(--text-secondary);

}


#company-questionnaire .button-exit:hover {

    border-color:
        var(--purple);

    color:
        var(--purple);

}


#company-questionnaire .button-secondary {

    border-color:
        var(--border);

    background:
        var(--panel-heading);

    color:
        var(--text);

}


#company-questionnaire .button-secondary:hover {

    border-color:
        var(--purple);

    color:
        var(--purple);

}


#company-questionnaire .button-primary {

    border-color:
        var(--purple);

    background:
        var(--purple);

    color: #ffffff;

}


#company-questionnaire .button-primary:hover {

    border-color:
        var(--purple-hover);

    background:
        var(--purple-hover);

}


#company-questionnaire .button-complete {

    border-color:
        var(--green);

    background:
        var(--green);

    color: #ffffff;

}


#company-questionnaire .button-complete:hover {

    border-color:
        #6d9129;

    background:
        #6d9129;

}


/* =============================================================
   DARK MODE
============================================================= */

html[data-client-theme="dark"]
#company-questionnaire {

    --text: #f1edf3;
    --text-secondary: #c4bbc7;
    --text-muted: #aaa1ae;

    --border: #403943;
    --border-light: #403943;

    --panel-heading: #29252c;

    background:
        #141216;

    color:
        #e9e5eb;

}


html[data-client-theme="dark"]
#company-questionnaire
.progress-panel,

html[data-client-theme="dark"]
#company-questionnaire
.question-section-panel {

    border-color:
        #403943;

    background:
        #1e1b21;

    box-shadow:
        0 10px 28px #0d0b0e;

}


html[data-client-theme="dark"]
#company-questionnaire
.progress-panel-heading,

html[data-client-theme="dark"]
#company-questionnaire
.question-section-heading {

    border-color:
        #403943;

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-questionnaire
.progress-company-summary {

    border-color:
        #403943;

}


html[data-client-theme="dark"]
#company-questionnaire
.progress-company-summary strong,

html[data-client-theme="dark"]
#company-questionnaire
.progress-heading-text h2,

html[data-client-theme="dark"]
#company-questionnaire
.question-section-heading h3,

html[data-client-theme="dark"]
#company-questionnaire
.question-title {

    color:
        #f1edf3;

}


html[data-client-theme="dark"]
#company-questionnaire
.progress-bar {

    background:
        #3b353e;

}


html[data-client-theme="dark"]
#company-questionnaire
.step-chip {

    border-color:
        #4a434e;

    background:
        #242127;

}


html[data-client-theme="dark"]
#company-questionnaire
.step-chip-content strong {

    color:
        #ddd6df;

}


html[data-client-theme="dark"]
#company-questionnaire
.step-chip.is-active {

    border-color:
        var(--turquoise);

    background:
        #173136;

}


html[data-client-theme="dark"]
#company-questionnaire
.step-chip.is-complete {

    border-color:
        #536635;

    background:
        #28321f;

}


html[data-client-theme="dark"]
#company-questionnaire
.step-chip.is-complete
.step-chip-content strong {

    color:
        #b5d17e;

}


html[data-client-theme="dark"]
#company-questionnaire
.questions-container {

    background:
        #18161a;

}


html[data-client-theme="dark"]
#company-questionnaire
.question-card {

    border-color:
        #403943;

    background:
        #1e1b21;

}


html[data-client-theme="dark"]
#company-questionnaire
.question-card:focus-within {

    border-left-color:
        var(--turquoise);

}


html[data-client-theme="dark"]
#company-questionnaire
.section-question-count {

    border-color:
        #4a434e;

    background:
        #242127;

}


html[data-client-theme="dark"]
#company-questionnaire
.section-question-count strong {

    color:
        #f1edf3;

}


html[data-client-theme="dark"]
#company-questionnaire
.question-number {

    background:
        #35283d;

    color:
        #cfa9e3;

}


html[data-client-theme="dark"]
#company-questionnaire
.question-field,

html[data-client-theme="dark"]
#company-questionnaire
.custom-select {

    border-color:
        #4a434e;

    background:
        #242127;

    color:
        #e9e5eb;

}


html[data-client-theme="dark"]
#company-questionnaire
.question-field:focus,

html[data-client-theme="dark"]
#company-questionnaire
.custom-select:focus {

    border-color:
        var(--turquoise);

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-card {

    border-color:
        #4a434e;

    background:
        #242127;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-card:hover {

    border-color:
        var(--turquoise);

    background:
        #173136;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-card.is-selected {

    border-color:
        var(--turquoise);

    background:
        #173136;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-text {

    color:
        #d8d0db;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-card.is-selected
.choice-text {

    color:
        #72c4ce;

}


html[data-client-theme="dark"]
#company-questionnaire
.choice-control {

    border-color:
        #756c79;

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-questionnaire
.button-exit,

html[data-client-theme="dark"]
#company-questionnaire
.button-secondary {

    border-color:
        #4a434e;

    background:
        #242127;

    color:
        #ddd6df;

}


html[data-client-theme="dark"]
#company-questionnaire
.questionnaire-alert-success {

    background:
        #28321f;

    color:
        #b5d17e;

}


html[data-client-theme="dark"]
#company-questionnaire
.questionnaire-alert-error {

    background:
        #3a2224;

    color:
        #e7a5a5;

}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1050px) {

    #company-questionnaire
    .questionnaire-hero-side {

        gap: 10px;

    }


    #company-questionnaire
    .questionnaire-hero-company {

        display: none;

    }

}


@media (max-width: 900px) {

    #company-questionnaire
    .progress-panel-heading {

        grid-template-columns:
            auto minmax(0, 1fr);

    }


    #company-questionnaire
    .progress-company-summary {

        grid-column:
            1 / -1;

        margin-top: 4px;

        padding:
            11px 0 0;

        border-top:
            1px solid var(--border);

        border-left: 0;

    }


    #company-questionnaire
    .choices-grid {

        grid-template-columns:
            1fr;

    }

}


@media (max-width: 720px) {

    #company-questionnaire
    .questionnaire-hero {

        min-height: 190px;

        padding:
            26px 20px;

    }


    #company-questionnaire
    .questionnaire-mosaic {

        display: none;

    }


    #company-questionnaire
    .questionnaire-content {

        padding:
            20px 16px;

    }


    #company-questionnaire
    .question-section-heading {

        flex-direction:
            column;

    }


    #company-questionnaire
    .section-question-count {

        width: 100%;

    }


    #company-questionnaire
    .questions-container {

        padding: 17px;

    }


    #company-questionnaire
    .question-card {

        padding: 17px;

    }

}


@media (max-width: 560px) {

    #company-questionnaire
    .questionnaire-hero {

        align-items:
            flex-start;

        flex-direction:
            column;

        gap: 19px;

    }


    #company-questionnaire
    .questionnaire-hero-side {

        width: 100%;

    }


    #company-questionnaire
    .questionnaire-hero-status {

        width: 100%;

    }


    #company-questionnaire
    .steps-indicator {

        grid-template-columns:
            1fr;

    }


    #company-questionnaire
    .question-section-heading-main {

        flex-direction:
            column;

    }


    #company-questionnaire
    .questionnaire-navigation {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    #company-questionnaire
    .navigation-left,
    #company-questionnaire
    .navigation-right {

        width: 100%;

    }


    #company-questionnaire
    .questionnaire-button {

        flex: 1;

    }

}


@media (max-width: 400px) {

    #company-questionnaire
    .navigation-left,
    #company-questionnaire
    .navigation-right {

        flex-direction:
            column;

    }


    #company-questionnaire
    .questionnaire-button {

        width: 100%;

    }

}

#company-questionnaire .questionnaire-fields {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

#company-questionnaire .questionnaire-fields.is-locked .question-field,
#company-questionnaire .questionnaire-fields.is-locked .custom-select,
#company-questionnaire .questionnaire-fields.is-locked .choice-card {
    cursor: not-allowed;
}

#company-questionnaire .questionnaire-fields.is-locked .question-card {
    opacity: .82;
}

#company-questionnaire .questionnaire-alert-locked {
    border-left-color: var(--turquoise);
    background: var(--turquoise-soft);
    color: var(--turquoise-hover);
}

#company-questionnaire .questionnaire-button:disabled,
#company-questionnaire .questionnaire-button[aria-disabled="true"] {
    border-color: #cfc8d2;
    background: #e4e0e6;
    color: #938a97;
    cursor: not-allowed;
    opacity: .72;
    transform: none;
}

html[data-client-theme="dark"] #company-questionnaire .questionnaire-alert-locked {
    background: #173136;
    color: #78c3cb;
}

html[data-client-theme="dark"] #company-questionnaire .questionnaire-button:disabled,
html[data-client-theme="dark"] #company-questionnaire .questionnaire-button[aria-disabled="true"] {
    border-color: #454047;
    background: #302c32;
    color: #79717d;
}

</style>

@endsection
