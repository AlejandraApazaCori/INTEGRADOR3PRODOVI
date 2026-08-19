@extends('layouts.app2')

@section('title', 'Editar Empresa')

@section('content')

<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    rel="stylesheet"
>

<div id="company-edit">

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <header class="company-edit-hero">

        <div class="company-edit-hero-content">

            <span class="company-edit-kicker">
                <i class="fas fa-pen-to-square"></i>
                Administración de tu marca
            </span>

            <h1>
                Editar
                <span>empresa</span>
            </h1>

            <p>
                Actualiza la información principal de tu empresa.
                Revisa los datos antes de guardar los cambios.
            </p>

        </div>


        <div class="company-edit-hero-side">

            <div class="company-edit-hero-company">

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


            <a
                href="{{ route('empresas.show', $empresa->id) }}"
                class="company-edit-hero-back"
            >

                <i class="fas fa-arrow-left"></i>

                <span>
                    Volver
                </span>

            </a>


            <div
                class="company-edit-mosaic"
                aria-hidden="true"
            >
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
    <main class="company-edit-content">


        {{-- =====================================================
            ERRORES GENERALES
        ====================================================== --}}
        @if($errors->any())

            <div class="company-edit-alert">

                <div class="company-edit-alert-icon">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>

                <div>

                    <strong>
                        Revisa la información ingresada
                    </strong>

                    <p>
                        Hay algunos datos que necesitan ser corregidos.
                    </p>

                </div>

            </div>

        @endif



        {{-- =====================================================
            PANEL
        ====================================================== --}}
        <section class="company-edit-panel">


            {{-- =================================================
                CABECERA DEL PANEL
            ================================================== --}}
            <div class="company-edit-panel-heading">

                <div class="company-edit-panel-heading-icon">

                    <i class="fas fa-building"></i>

                </div>


                <div>

                    <h2>
                        Datos de la empresa
                    </h2>

                    <p>
                        Modifica la información registrada y guarda los cambios
                    </p>

                </div>

            </div>



            {{-- =================================================
                FORMULARIO
            ================================================== --}}
            <form
                action="{{ route('empresas.update', $empresa->id) }}"
                method="POST"
                enctype="multipart/form-data"
                class="company-edit-form"
                id="company-edit-form"
            >

                @csrf
                @method('PUT')


                <div class="company-edit-fields">


                    {{-- =========================================
                        NOMBRE DE EMPRESA
                    ========================================== --}}
                    <div class="company-field company-field-full">

                        <label
                            for="nombre_empresa"
                            class="company-field-label"
                        >

                            <span class="company-field-label-icon">
                                <i class="fas fa-store"></i>
                            </span>

                            <span>
                                Nombre de la empresa
                            </span>

                            <span class="company-required">
                                *
                            </span>

                        </label>


                        <div class="company-input-wrapper">

                            <textarea
                                id="nombre_empresa"
                                name="nombre_empresa"
                                rows="1"
                                class="company-input company-auto-grow company-single-line"
                                placeholder="Ej: Prodovi S.A."
                                required
                            >{{ old('nombre_empresa', $empresa->nombre_empresa) }}</textarea>

                        </div>


                        <span class="company-field-hint">

                            <i class="far fa-circle-question"></i>

                            Escribe el nombre comercial o razón social de tu empresa.

                        </span>


                        @error('nombre_empresa')

                            <p class="company-field-error">

                                <i class="fas fa-circle-exclamation"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>



                    {{-- =========================================
                        TIPO DE EMPRESA
                    ========================================== --}}
                    <div class="company-field company-field-full">

                        <label
                            for="tipo_empresa"
                            class="company-field-label"
                        >

                            <span class="company-field-label-icon">
                                <i class="fas fa-layer-group"></i>
                            </span>

                            <span>
                                Tipo de empresa
                            </span>

                            <span class="company-required">
                                *
                            </span>

                        </label>


                        <div class="company-input-wrapper">

                            @php
                                $tiposEmpresa = ['Tecnología', 'Comercio', 'Servicios', 'Gastronomía', 'Salud', 'Educación', 'Belleza', 'Inmobiliaria', 'Otro'];
                                $tipoActual = old('tipo_empresa', $empresa->tipo_empresa);
                            @endphp
                            <div class="company-type-select" id="company-type-select">
                                <input type="hidden" id="tipo_empresa" name="tipo_empresa" value="{{ $tipoActual }}" required>
                                <button type="button" class="company-type-trigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span>{{ $tipoActual ?: 'Selecciona una categoría' }}</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="company-type-menu" role="listbox">
                                    @if($tipoActual && !in_array($tipoActual, $tiposEmpresa, true))
                                        <button type="button" class="company-type-option is-selected" data-value="{{ $tipoActual }}">{{ $tipoActual }}</button>
                                    @endif
                                    @foreach($tiposEmpresa as $tipo)
                                        <button type="button" class="company-type-option {{ $tipoActual === $tipo ? 'is-selected' : '' }}" data-value="{{ $tipo }}">{{ $tipo }}</button>
                                    @endforeach
                                </div>
                            </div>

                        </div>


                        <span class="company-field-hint">

                            <i class="far fa-circle-question"></i>

                            Indica el sector o actividad principal de la empresa.

                        </span>


                        @error('tipo_empresa')

                            <p class="company-field-error">

                                <i class="fas fa-circle-exclamation"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>



                    {{-- =========================================
                        DIRECCIÓN
                    ========================================== --}}
                    <div class="company-field company-field-full">
                        <label for="direccion" class="company-field-label">
                            <span class="company-field-label-icon"><i class="fas fa-location-dot"></i></span>
                            <span>Dirección</span>
                            <span class="company-optional">Opcional</span>
                        </label>
                        <div class="company-input-wrapper">
                            <textarea id="direccion" name="direccion" rows="2" maxlength="500" class="company-input company-auto-grow" placeholder="Ej: Av. Principal #123, Zona Central">{{ old('direccion', $empresa->direccion) }}</textarea>
                        </div>
                        <span class="company-field-hint"><i class="far fa-circle-question"></i>Indica la ubicación física de tu empresa.</span>
                        @error('direccion')
                            <p class="company-field-error"><i class="fas fa-circle-exclamation"></i>{{ $message }}</p>
                        @enderror
                    </div>


                    {{-- =========================================
                        DESCRIPCIÓN
                    ========================================== --}}
                    <div class="company-field company-field-full">

                        <label
                            for="descripcion"
                            class="company-field-label"
                        >

                            <span class="company-field-label-icon">
                                <i class="fas fa-align-left"></i>
                            </span>

                            <span>
                                Descripción
                            </span>

                            <span class="company-optional">
                                Opcional
                            </span>

                        </label>


                        <div class="company-input-wrapper">

                            <textarea
                                id="descripcion"
                                name="descripcion"
                                rows="4"
                                class="company-input company-description-input company-auto-grow"
                                placeholder="Describe brevemente tu empresa, sus servicios y objetivos..."
                            >{{ old('descripcion', $empresa->descripcion) }}</textarea>

                        </div>


                        <span class="company-field-hint">

                            <i class="far fa-circle-question"></i>

                            Puedes incluir qué hace tu empresa, qué ofrece y a qué tipo de clientes se dirige.

                        </span>


                        @error('descripcion')

                            <p class="company-field-error">

                                <i class="fas fa-circle-exclamation"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>



                    {{-- =========================================
                        LOGO
                    ========================================== --}}
                    <div class="company-field company-field-full">

                        <label
                            for="logo"
                            class="company-field-label"
                        >

                            <span class="company-field-label-icon">
                                <i class="fas fa-image"></i>
                            </span>

                            <span>
                                Logo de la empresa
                            </span>

                            <span class="company-optional">
                                Opcional
                            </span>

                        </label>


                        <div
                            class="company-logo-upload"
                            id="company-logo-upload"
                        >

                            <input
                                type="file"
                                id="logo"
                                name="logo"
                                accept="image/*"
                                class="company-logo-input"
                            >


                            <div
                                class="company-logo-preview"
                                id="logo-preview"
                            >

                                @if($empresa->logo)

                                    <div class="company-current-logo">

                                        <div class="company-current-logo-image">

                                            <img
                                                src="{{ Storage::url($empresa->logo) }}"
                                                alt="Logo actual de {{ $empresa->nombre_empresa }}"
                                            >

                                        </div>


                                        <div class="company-current-logo-info">

                                            <span class="company-logo-status">

                                                <i class="fas fa-circle-check"></i>

                                                Logo actual

                                            </span>

                                            <strong>
                                                {{ $empresa->nombre_empresa }}
                                            </strong>

                                            <p>
                                                Haz clic o arrastra una nueva imagen para reemplazarlo.
                                            </p>

                                        </div>

                                    </div>

                                @else

                                    <div class="company-upload-placeholder">

                                        <div class="company-upload-icon">

                                            <i class="fas fa-cloud-arrow-up"></i>

                                        </div>

                                        <strong>
                                            Agregar logo de empresa
                                        </strong>

                                        <p>
                                            Haz clic aquí o arrastra una imagen
                                        </p>

                                        <span>
                                            JPG, PNG, GIF · Máximo 2 MB
                                        </span>

                                    </div>

                                @endif

                            </div>


                            <div
                                class="company-logo-hover"
                                aria-hidden="true"
                            >

                                <i class="fas fa-image"></i>

                                <span>
                                    Seleccionar nueva imagen
                                </span>

                            </div>

                        </div>



                        <div
                            id="logo-file-name"
                            class="company-file-name"
                            hidden
                        >

                            <i class="fas fa-paperclip"></i>

                            <span id="logo-file-name-text"></span>

                        </div>


                        <span class="company-field-hint">

                            <i class="far fa-circle-question"></i>

                            Para mejores resultados utiliza una imagen clara, preferiblemente cuadrada y con fondo transparente.

                        </span>


                        @error('logo')

                            <p class="company-field-error">

                                <i class="fas fa-circle-exclamation"></i>

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </div>



                {{-- =================================================
                    ACCIONES
                ================================================== --}}
                <div class="company-edit-actions">


                    <a
                        href="{{ route('empresas.show', $empresa->id) }}"
                        class="company-button company-button-cancel"
                    >

                        <i class="fas fa-xmark"></i>

                        <span>
                            Cancelar
                        </span>

                    </a>


                    <button
                        type="submit"
                        class="company-button company-button-save"
                    >

                        <i class="fas fa-floppy-disk"></i>

                        <span>
                            Guardar cambios
                        </span>

                    </button>

                </div>

            </form>

        </section>

    </main>

</div>



{{-- =============================================================
    JAVASCRIPT
============================================================== --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const companyTypeSelect = document.getElementById('company-type-select');

    if (companyTypeSelect) {
        const trigger = companyTypeSelect.querySelector('.company-type-trigger');
        const input = companyTypeSelect.querySelector('input[name="tipo_empresa"]');
        const value = trigger.querySelector('span');
        const options = companyTypeSelect.querySelectorAll('.company-type-option');

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const opening = !companyTypeSelect.classList.contains('is-open');
            companyTypeSelect.classList.toggle('is-open', opening);
            trigger.setAttribute('aria-expanded', String(opening));
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                input.value = option.dataset.value;
                value.textContent = option.dataset.value;
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                companyTypeSelect.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', function () {
            companyTypeSelect.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    const logoInput =
        document.getElementById('logo');

    const logoPreview =
        document.getElementById('logo-preview');

    const logoUpload =
        document.getElementById('company-logo-upload');

    const logoFileName =
        document.getElementById('logo-file-name');

    const logoFileNameText =
        document.getElementById('logo-file-name-text');


    /*
    |--------------------------------------------------------------------------
    | CAMPOS AUTOEXPANDIBLES
    |--------------------------------------------------------------------------
    |
    | Los campos crecen verticalmente para mostrar todo el contenido.
    |
    */

    function autoGrow(field) {

        if (!field) {
            return;
        }


        field.style.height = 'auto';


        const minimumHeight =
            field.classList.contains('company-single-line')
                ? 52
                : 118;


        field.style.height =
            Math.max(
                field.scrollHeight,
                minimumHeight
            ) + 'px';

    }



    document
        .querySelectorAll('.company-auto-grow')
        .forEach(function (field) {

            autoGrow(field);


            field.addEventListener(
                'input',
                function () {

                    autoGrow(this);

                }
            );

        });



    /*
    |--------------------------------------------------------------------------
    | PREVISUALIZACIÓN DEL LOGO
    |--------------------------------------------------------------------------
    */

    function previewLogo(file) {

        if (!file) {
            return;
        }


        if (!file.type.startsWith('image/')) {

            alert(
                'Selecciona un archivo de imagen válido.'
            );

            if (logoInput) {
                logoInput.value = '';
            }

            return;

        }


        const reader =
            new FileReader();


        reader.onload = function (event) {

            logoPreview.innerHTML = `

                <div class="company-new-logo">

                    <div class="company-new-logo-image">

                        <img
                            src="${event.target.result}"
                            alt="Vista previa del nuevo logo"
                        >

                    </div>


                    <div class="company-new-logo-info">

                        <span>

                            <i class="fas fa-circle-check"></i>

                            Nuevo logo seleccionado

                        </span>

                        <strong>
                            Listo para guardar
                        </strong>

                        <p>
                            Guarda los cambios para actualizar el logo de la empresa.
                        </p>

                    </div>

                </div>

            `;


            logoFileNameText.textContent =
                file.name;

            logoFileName.hidden =
                false;


            logoUpload.classList.add(
                'has-new-file'
            );

        };


        reader.readAsDataURL(file);

    }



    if (logoInput) {

        logoInput.addEventListener(
            'change',
            function () {

                if (
                    this.files &&
                    this.files[0]
                ) {

                    previewLogo(
                        this.files[0]
                    );

                }

            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | DRAG & DROP VISUAL
    |--------------------------------------------------------------------------
    */

    if (logoUpload) {

        [
            'dragenter',
            'dragover'
        ].forEach(function (eventName) {

            logoUpload.addEventListener(
                eventName,
                function () {

                    logoUpload.classList.add(
                        'is-dragging'
                    );

                }
            );

        });



        [
            'dragleave',
            'drop'
        ].forEach(function (eventName) {

            logoUpload.addEventListener(
                eventName,
                function () {

                    logoUpload.classList.remove(
                        'is-dragging'
                    );

                }
            );

        });

    }

});
</script>



{{-- =============================================================
    ESTILOS
============================================================== --}}
<style>

/* =============================================================
   VARIABLES
============================================================= */

#company-edit {

    --purple: #5b2b76;
    --purple-hover: #4d2365;
    --purple-soft: #f4eef7;

    --orange: #ee9f2b;
    --orange-soft: #fff7ea;

    --turquoise: #117e8c;
    --turquoise-hover: #0d6d79;
    --turquoise-soft: #edf7f8;

    --green: #7da533;
    --green-hover: #6c902b;
    --green-soft: #f3f7eb;

    --red: #c74a4a;
    --red-soft: #fff1f1;

    --dark: #242426;

    --text: #302834;
    --text-secondary: #6f6573;
    --text-muted: #887d8c;

    --border: #ded7e1;
    --border-light: #ebe6ed;

    --panel-heading: #f7f5f8;

    min-height: 100vh;

    background: #ffffff;

    color: var(--text);

}


/* =============================================================
   HERO
============================================================= */

#company-edit .company-edit-hero {

    min-height: 160px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 32px;

    padding: 29px 32px;

    background: var(--dark);

    color: #ffffff;

}


#company-edit .company-edit-hero-content {

    max-width: 700px;

}


#company-edit .company-edit-kicker {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 10px;

    color: var(--turquoise);

    font-size: .68rem;

    font-weight: 900;

    letter-spacing: .13em;

    text-transform: uppercase;

}


#company-edit .company-edit-hero h1 {

    margin: 0;

    color: #ffffff;

    font-size: clamp(
        1.7rem,
        3vw,
        2.4rem
    );

    font-weight: 850;

    line-height: 1.08;

    letter-spacing: -.035em;

}


#company-edit .company-edit-hero h1 span {

    color: var(--turquoise);

}


#company-edit .company-edit-hero-content > p {

    max-width: 680px;

    margin: 11px 0 0;

    color: #aaa5ad;

    font-size: .86rem;

    line-height: 1.55;

}


#company-edit .company-edit-hero-side {

    display: flex;

    align-items: center;

    gap: 14px;

}


/* =============================================================
   EMPRESA HERO
============================================================= */

#company-edit .company-edit-hero-company {

    min-width: 190px;

    padding: 13px 16px;

    border-left:
        4px solid var(--purple);

    background: #303033;

}


#company-edit .company-edit-hero-company small,
#company-edit .company-edit-hero-company strong,
#company-edit .company-edit-hero-company span {

    display: block;

}


#company-edit .company-edit-hero-company small {

    color: #aaa5ad;

    font-size: .62rem;

    font-weight: 800;

    letter-spacing: .08em;

    text-transform: uppercase;

}


#company-edit .company-edit-hero-company strong {

    max-width: 210px;

    overflow: hidden;

    margin-top: 4px;

    color: #ffffff;

    font-size: .83rem;

    font-weight: 900;

    text-overflow: ellipsis;

    white-space: nowrap;

}


#company-edit .company-edit-hero-company span {

    max-width: 210px;

    overflow: hidden;

    margin-top: 2px;

    color: #aaa5ad;

    font-size: .66rem;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* =============================================================
   BOTÓN VOLVER HERO
============================================================= */

#company-edit .company-edit-hero-back {

    min-height: 44px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 14px;

    border:
        1px solid #4b4b4e;

    border-radius: 4px;

    background: #303033;

    color: #ffffff;

    font-size: .7rem;

    font-weight: 850;

    text-decoration: none;

    transition:
        .18s ease;

}


#company-edit .company-edit-hero-back:hover {

    border-color:
        var(--turquoise);

    background: #353538;

    color:
        var(--turquoise);

    transform:
        translateX(-2px);

}


/* =============================================================
   MOSAICO
============================================================= */

#company-edit .company-edit-mosaic {

    width: 144px;

    height: 96px;

    display: grid;

    flex: 0 0 auto;

    grid-template-columns:
        repeat(3, 1fr);

    grid-template-rows:
        repeat(2, 1fr);

}


#company-edit .company-edit-mosaic span:nth-child(1) {

    background: #ef6c22;

    border-radius:
        100% 0 0 0;

}


#company-edit .company-edit-mosaic span:nth-child(2) {

    background: #f5a900;

    border-radius:
        0 0 0 100%;

}


#company-edit .company-edit-mosaic span:nth-child(3) {

    background:
        var(--purple);

    border-radius:
        100% 0 100% 0;

}


#company-edit .company-edit-mosaic span:nth-child(4) {

    background:
        var(--turquoise);

    border-radius:
        0 100% 0 100%;

}


#company-edit .company-edit-mosaic span:nth-child(5) {

    background:
        var(--green);

    border-radius: 50%;

}


#company-edit .company-edit-mosaic span:nth-child(6) {

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
   CONTENIDO
============================================================= */

#company-edit .company-edit-content {

    max-width: 1040px;

    margin: 0 auto;

    padding: 32px;

}


/* =============================================================
   ALERTA
============================================================= */

#company-edit .company-edit-alert {

    display: flex;

    align-items: flex-start;

    gap: 12px;

    margin-bottom: 18px;

    padding: 14px 16px;

    border-left:
        4px solid var(--red);

    background:
        var(--red-soft);

    color: #963b3b;

}


#company-edit .company-edit-alert-icon {

    width: 34px;

    height: 34px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 50%;

    background:
        var(--red);

    color: #ffffff;

}


#company-edit .company-edit-alert strong {

    display: block;

    margin-bottom: 3px;

    font-size: .8rem;

    font-weight: 900;

}


#company-edit .company-edit-alert p {

    margin: 0;

    font-size: .72rem;

}


/* =============================================================
   PANEL
============================================================= */

#company-edit .company-edit-panel {

    overflow: hidden;

    border:
        1px solid var(--border);

    border-radius: 5px;

    background: #ffffff;

    box-shadow:
        0 10px 28px #ded9e0;

}


/* =============================================================
   CABECERA DEL PANEL
============================================================= */

#company-edit .company-edit-panel-heading {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 18px 20px;

    border-bottom:
        1px solid var(--border);

    border-left:
        4px solid var(--turquoise);

    background:
        var(--panel-heading);

}


#company-edit .company-edit-panel-heading-icon {

    width: 40px;

    height: 40px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 3px;

    background:
        var(--turquoise);

    color: #ffffff;

}


#company-edit .company-edit-panel-heading h2 {

    margin: 0;

    color:
        var(--text);

    font-size: 1rem;

    font-weight: 900;

}


#company-edit .company-edit-panel-heading p {

    margin: 3px 0 0;

    color:
        var(--text-muted);

    font-size: .72rem;

}


/* =============================================================
   FORMULARIO
============================================================= */

#company-edit .company-edit-form {

    padding: 26px;

}


#company-edit .company-edit-fields {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 21px 18px;

}


#company-edit .company-field {

    min-width: 0;

}


#company-edit .company-field-full {

    grid-column:
        1 / -1;

}


/* =============================================================
   LABEL
============================================================= */

#company-edit .company-field-label {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 8px;

    color:
        var(--text);

    font-size: .74rem;

    font-weight: 900;

}


#company-edit .company-field-label-icon {

    width: 27px;

    height: 27px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border-radius: 3px;

    background:
        var(--turquoise-soft);

    color:
        var(--turquoise);

    font-size: .65rem;

}


#company-edit .company-required {

    color:
        var(--red);

    font-size: .92rem;

}


#company-edit .company-optional {

    margin-left: auto;

    padding: 3px 6px;

    border-radius: 3px;

    background:
        #f0edf1;

    color:
        var(--text-muted);

    font-size: .52rem;

    font-weight: 850;

    letter-spacing: .05em;

    text-transform: uppercase;

}


/* =============================================================
   INPUTS / TEXTAREAS
============================================================= */

#company-edit .company-input-wrapper {

    width: 100%;

    position: relative;

}

#company-edit .company-select {
    height: 52px;
    padding-right: 48px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

#company-edit .company-select-icon {
    position: absolute;
    top: 50%;
    right: 14px;
    width: 27px;
    height: 27px;
    display: grid;
    place-items: center;
    border-radius: 3px;
    background: var(--turquoise-soft);
    color: var(--turquoise);
    font-size: .62rem;
    pointer-events: none;
    transform: translateY(-50%);
}


#company-edit .company-input {

    width: 100%;

    min-height: 52px;

    box-sizing: border-box;

    overflow: hidden;

    padding: 13px 14px;

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


#company-edit .company-description-input {

    min-height: 118px;

}


#company-edit .company-input::placeholder {

    color: #aaa1ad;

}


#company-edit .company-input:hover {

    border-color:
        #bfb5c3;

}


#company-edit .company-input:focus {

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
   AYUDA DE CAMPOS
============================================================= */

#company-edit .company-field-hint {

    display: flex;

    align-items: flex-start;

    gap: 5px;

    margin-top: 7px;

    color:
        var(--text-muted);

    font-size: .62rem;

    line-height: 1.45;

}


#company-edit .company-field-hint i {

    margin-top: 1px;

    color:
        var(--turquoise);

}


/* =============================================================
   ERROR CAMPO
============================================================= */

#company-edit .company-field-error {

    display: flex;

    align-items: flex-start;

    gap: 6px;

    margin:
        7px 0 0;

    color:
        var(--red);

    font-size: .67rem;

    font-weight: 750;

}


/* =============================================================
   CARGA DE LOGO
============================================================= */

#company-edit .company-logo-upload {

    position: relative;

    min-height: 180px;

    overflow: hidden;

    border:
        2px dashed #ccc4cf;

    border-radius: 5px;

    background:
        #faf9fa;

    cursor: pointer;

    transition:
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;

}


#company-edit .company-logo-upload:hover,
#company-edit .company-logo-upload.is-dragging {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise-soft);

    box-shadow:
        0 0 0 3px rgba(
            17,
            126,
            140,
            .08
        );

}


#company-edit .company-logo-input {

    position: absolute;

    inset: 0;

    z-index: 5;

    width: 100%;

    height: 100%;

    opacity: 0;

    cursor: pointer;

}


/* =============================================================
   PREVIEW LOGO
============================================================= */

#company-edit .company-logo-preview {

    min-height: 180px;

    display: grid;

    place-items: center;

    padding: 22px;

}


/* =============================================================
   LOGO ACTUAL
============================================================= */

#company-edit .company-current-logo,
#company-edit .company-new-logo {

    width: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 22px;

}


#company-edit .company-current-logo-image,
#company-edit .company-new-logo-image {

    width: 125px;

    height: 125px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    overflow: hidden;

    border:
        1px solid var(--border);

    border-radius: 5px;

    background: #ffffff;

}


#company-edit .company-current-logo-image img,
#company-edit .company-new-logo-image img {

    width: 100%;

    height: 100%;

    padding: 9px;

    object-fit: contain;

}


#company-edit .company-current-logo-info,
#company-edit .company-new-logo-info {

    max-width: 410px;

}


#company-edit .company-logo-status,
#company-edit .company-new-logo-info > span {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    margin-bottom: 7px;

    color:
        var(--green);

    font-size: .59rem;

    font-weight: 900;

    letter-spacing: .05em;

    text-transform: uppercase;

}


#company-edit .company-current-logo-info strong,
#company-edit .company-new-logo-info strong {

    display: block;

    color:
        var(--text);

    font-size: .88rem;

    font-weight: 900;

}


#company-edit .company-current-logo-info p,
#company-edit .company-new-logo-info p {

    margin: 5px 0 0;

    color:
        var(--text-muted);

    font-size: .68rem;

    line-height: 1.5;

}


/* =============================================================
   PLACEHOLDER UPLOAD
============================================================= */

#company-edit .company-upload-placeholder {

    text-align: center;

}


#company-edit .company-upload-icon {

    width: 55px;

    height: 55px;

    display: grid;

    place-items: center;

    margin:
        0 auto 12px;

    border-radius: 50%;

    background:
        var(--turquoise-soft);

    color:
        var(--turquoise);

    font-size: 1.25rem;

}


#company-edit .company-upload-placeholder strong {

    display: block;

    color:
        var(--text);

    font-size: .8rem;

    font-weight: 900;

}


#company-edit .company-upload-placeholder p {

    margin:
        4px 0;

    color:
        var(--text-secondary);

    font-size: .7rem;

}


#company-edit .company-upload-placeholder span {

    color:
        var(--text-muted);

    font-size: .61rem;

}


/* =============================================================
   OVERLAY HOVER UPLOAD
============================================================= */

#company-edit .company-logo-hover {

    position: absolute;

    inset: 0;

    z-index: 2;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    background:
        rgba(
            17,
            126,
            140,
            .92
        );

    color: #ffffff;

    font-size: .72rem;

    font-weight: 900;

    opacity: 0;

    pointer-events: none;

    transition:
        opacity .18s ease;

}


#company-edit .company-logo-upload:hover
.company-logo-hover {

    opacity: 1;

}


#company-edit .company-logo-upload.has-new-file
.company-logo-hover {

    display: none;

}


/* =============================================================
   NOMBRE ARCHIVO
============================================================= */

#company-edit .company-file-name {

    width: fit-content;

    max-width: 100%;

    align-items: center;

    gap: 7px;

    margin-top: 9px;

    padding: 7px 10px;

    border-radius: 3px;

    background:
        var(--green-soft);

    color: #607f26;

    font-size: .65rem;

    font-weight: 800;

}


#company-edit .company-file-name:not([hidden]) {

    display: flex;

}


#company-edit .company-file-name span {

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* =============================================================
   SELECT PERSONALIZADO
   Preparado por si posteriormente agregas un select.
============================================================= */

#company-edit .company-select-wrapper {

    position: relative;

    width: 100%;

}


#company-edit .company-select {

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

}


#company-edit .company-select:focus {

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


#company-edit .company-select-arrow {

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

    font-size: .61rem;

    pointer-events: none;

    transform:
        translateY(-50%);

}


/* =============================================================
   CHECKBOX PERSONALIZADO
   Preparado para futuros campos.
============================================================= */

#company-edit .company-checkbox {

    position: relative;

    display: flex;

    align-items: center;

    gap: 10px;

    min-height: 48px;

    padding: 10px 12px;

    border:
        1px solid var(--border);

    border-radius: 4px;

    background:
        #faf9fa;

    cursor: pointer;

}


#company-edit .company-checkbox input {

    position: absolute;

    width: 1px;

    height: 1px;

    opacity: 0;

}


#company-edit .company-checkbox-control {

    width: 21px;

    height: 21px;

    display: grid;

    place-items: center;

    flex: 0 0 auto;

    border:
        2px solid #bdb4c1;

    border-radius: 4px;

    background: #ffffff;

    color: transparent;

    font-size: .62rem;

    transition:
        .18s ease;

}


#company-edit
.company-checkbox input:checked
+
.company-checkbox-control {

    border-color:
        var(--turquoise);

    background:
        var(--turquoise);

    color: #ffffff;

}


/* =============================================================
   ACCIONES
============================================================= */

#company-edit .company-edit-actions {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    margin-top: 27px;

    padding-top: 21px;

    border-top:
        1px solid var(--border-light);

}


#company-edit .company-button {

    min-height: 45px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 18px;

    border:
        1px solid;

    border-radius: 4px;

    font-family: inherit;

    font-size: .72rem;

    font-weight: 900;

    text-decoration: none;

    cursor: pointer;

    transition:
        .18s ease;

}


#company-edit .company-button:hover {

    transform:
        translateY(-1px);

}


/* CANCELAR */

#company-edit .company-button-cancel {

    border-color:
        var(--border);

    background: #ffffff;

    color:
        var(--text-secondary);

}


#company-edit .company-button-cancel:hover {

    border-color:
        var(--purple);

    color:
        var(--purple);

}


/* GUARDAR */

#company-edit .company-button-save {

    border-color:
        var(--purple);

    background:
        var(--purple);

    color: #ffffff;

}


#company-edit .company-button-save:hover {

    border-color:
        var(--purple-hover);

    background:
        var(--purple-hover);

}


/* =============================================================
   DARK MODE
============================================================= */

html[data-client-theme="dark"]
#company-edit {

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
#company-edit
.company-edit-panel {

    border-color:
        #403943;

    background:
        #1e1b21;

    box-shadow:
        0 10px 28px #0d0b0e;

}


html[data-client-theme="dark"]
#company-edit
.company-edit-panel-heading {

    border-color:
        #403943;

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-edit
.company-edit-panel-heading h2,

html[data-client-theme="dark"]
#company-edit
.company-field-label,

html[data-client-theme="dark"]
#company-edit
.company-current-logo-info strong,

html[data-client-theme="dark"]
#company-edit
.company-new-logo-info strong,

html[data-client-theme="dark"]
#company-edit
.company-upload-placeholder strong {

    color:
        #f1edf3;

}


html[data-client-theme="dark"]
#company-edit
.company-input,

html[data-client-theme="dark"]
#company-edit
.company-select {

    border-color:
        #4a434e;

    background:
        #242127;

    color:
        #e9e5eb;

}


html[data-client-theme="dark"]
#company-edit
.company-input:focus,

html[data-client-theme="dark"]
#company-edit
.company-select:focus {

    border-color:
        var(--turquoise);

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-edit
.company-optional {

    background:
        #353039;

    color:
        #aaa1ae;

}


html[data-client-theme="dark"]
#company-edit
.company-logo-upload {

    border-color:
        #4a434e;

    background:
        #242127;

}


html[data-client-theme="dark"]
#company-edit
.company-logo-upload:hover,

html[data-client-theme="dark"]
#company-edit
.company-logo-upload.is-dragging {

    border-color:
        var(--turquoise);

    background:
        #173136;

}


html[data-client-theme="dark"]
#company-edit
.company-current-logo-image,

html[data-client-theme="dark"]
#company-edit
.company-new-logo-image {

    border-color:
        #4a434e;

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-edit
.company-button-cancel {

    border-color:
        #4a434e;

    background:
        #242127;

    color:
        #ddd6df;

}


html[data-client-theme="dark"]
#company-edit
.company-field-label-icon {

    background:
        #173136;

}


html[data-client-theme="dark"]
#company-edit
.company-checkbox {

    border-color:
        #4a434e;

    background:
        #242127;

}


html[data-client-theme="dark"]
#company-edit
.company-checkbox-control {

    border-color:
        #756c79;

    background:
        #29252c;

}


html[data-client-theme="dark"]
#company-edit
.company-edit-alert {

    background:
        #3a2224;

    color:
        #e7a5a5;

}


/* =============================================================
   RESPONSIVE
============================================================= */

@media (max-width: 1000px) {

    #company-edit
    .company-edit-hero-company {

        display: none;

    }

}


@media (max-width: 780px) {

    #company-edit
    .company-edit-mosaic {

        display: none;

    }


    #company-edit
    .company-edit-content {

        padding:
            22px 17px;

    }


    #company-edit
    .company-edit-form {

        padding:
            21px;

    }

}


@media (max-width: 600px) {

    #company-edit
    .company-edit-hero {

        min-height: auto;

        flex-direction:
            column;

        align-items:
            flex-start;

        gap: 20px;

        padding:
            26px 20px;

    }


    #company-edit
    .company-edit-hero-side {

        width: 100%;

    }


    #company-edit
    .company-edit-hero-back {

        width: 100%;

    }


    #company-edit
    .company-edit-fields {

        grid-template-columns:
            1fr;

    }


    #company-edit
    .company-field-full {

        grid-column: auto;

    }


    #company-edit
    .company-current-logo,
    #company-edit
    .company-new-logo {

        flex-direction:
            column;

        text-align: center;

    }


    #company-edit
    .company-edit-actions {

        flex-direction:
            column-reverse;

    }


    #company-edit
    .company-button {

        width: 100%;

    }

}


@media (max-width: 420px) {

    #company-edit
    .company-edit-panel-heading {

        align-items:
            flex-start;

    }


    #company-edit
    .company-edit-form {

        padding:
            17px;

    }


    #company-edit
    .company-current-logo-image,
    #company-edit
    .company-new-logo-image {

        width: 105px;

        height: 105px;

    }

}

#company-edit .company-edit-panel { overflow: visible; }

#company-edit .company-type-select { position:relative; width:100%; }
#company-edit .company-type-trigger { width:100%; min-height:52px; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 14px; border:1px solid #d7d0da; border-radius:4px; background:#faf9fa; color:var(--text); font-family:inherit; font-size:.79rem; font-weight:650; text-align:left; cursor:pointer; transition:.18s ease; }
#company-edit .company-type-trigger > i { width:27px; height:27px; display:grid; place-items:center; flex:0 0 auto; border-radius:3px; background:var(--turquoise-soft); color:var(--turquoise); font-size:.62rem; transition:transform .18s ease; }
#company-edit .company-type-select.is-open .company-type-trigger { border-color:var(--turquoise); background:#fff; box-shadow:0 0 0 3px rgba(17,126,140,.10); }
#company-edit .company-type-select.is-open .company-type-trigger > i { transform:rotate(180deg); }
#company-edit .company-type-menu { position:absolute; z-index:100; top:calc(100% + 7px); right:0; left:0; display:none; max-height:250px; overflow-y:auto; padding:7px; border:1px solid #d7d0da; border-radius:4px; background:#fff; box-shadow:0 16px 34px rgba(37,27,42,.18); }
#company-edit .company-type-select.is-open .company-type-menu { display:block; }
#company-edit .company-type-option { width:100%; display:block; padding:10px 11px; border:0; border-radius:3px; background:#fff; color:#514557; font-family:inherit; font-size:.77rem; text-align:left; cursor:pointer; }
#company-edit .company-type-option:hover, #company-edit .company-type-option.is-selected { background:var(--turquoise-soft); color:var(--turquoise); font-weight:850; }
html[data-client-theme="dark"] #company-edit .company-type-trigger, html[data-client-theme="dark"] #company-edit .company-type-menu, html[data-client-theme="dark"] #company-edit .company-type-option { border-color:#4a434e; background:#242127; color:#e9e5eb; }
html[data-client-theme="dark"] #company-edit .company-type-select.is-open .company-type-trigger { background:#29252c; }
html[data-client-theme="dark"] #company-edit .company-type-option:hover, html[data-client-theme="dark"] #company-edit .company-type-option.is-selected { background:#173136; color:#72c4ce; }

</style>

@endsection
