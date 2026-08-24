@extends('layouts.app')

@section('title', ($isCreating ?? false) ? 'Crear Plan' : 'Editar Plan')

@section('content')
@php
    $isCreating = $isCreating ?? false;
    $oldFeatures = old('caracteristicas', []);
    $planFeatures = $plan->planCaracteristicas->isEmpty() ? [] : $plan->planCaracteristicas->map(function ($item) {
        return [
            'id' => $item->caracteristica_id,
            'cantidad' => $item->cantidad,
            'frecuencia' => $item->frecuencia,
            'orden' => $item->orden,
            'es_destacado' => $item->es_destacado,
        ];
    })->toArray();

    $featuresToShow = !empty($oldFeatures) ? $oldFeatures : $planFeatures;
    $selectedFeatureIds = collect($featuresToShow)->pluck('id')->filter();
    $featuredCount = collect($featuresToShow)->filter(function ($feature) {
        return !empty($feature['es_destacado']);
    })->count();
@endphp

<style>
    .plan-editor{--pe-blue:#2563eb;--pe-blue-dark:#1d4ed8;--pe-orange:#ef6c22;--pe-turquoise:#117e8c;--pe-green:#7da533;--pe-ink:#302834;--pe-muted:#756a7a;background:#f7f8fa;color:var(--pe-ink);padding-bottom:48px}.plan-editor-shell{max-width:none!important;padding:0!important}.plan-editor-hero{position:relative;isolation:isolate;min-height:210px;border-radius:0!important;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;box-shadow:none!important}.plan-editor-hero:after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.2),rgba(15,23,42,.2)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 48%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 45%)}.plan-editor-hero-grid{align-items:center;max-width:1536px;margin:auto;padding:32px 48px!important}.plan-editor-kicker{gap:8px;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.12);color:#dbeafe}.plan-editor-intro{max-width:590px;margin-top:10px;color:#dbeafe;font-size:.85rem;line-height:1.65}.plan-editor-back{gap:8px;color:#2563eb}.plan-editor-back:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,23,42,.18)}.plan-editor-context{gap:8px;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.11);color:#eff6ff}.plan-editor-stats{gap:12px!important}.plan-editor-stat{position:relative;overflow:hidden;border:1px solid rgba(255,255,255,.24);border-radius:14px!important;background:rgba(255,255,255,.12)!important;backdrop-filter:blur(8px)}.plan-editor-stat:after{content:'';position:absolute;right:-18px;bottom:-22px;width:65px;height:65px;border:12px solid rgba(255,255,255,.08);border-radius:50%}.plan-editor-form{max-width:1536px;margin:24px auto 0!important;padding:0 24px}.plan-editor-panel,.plan-editor-preview{border:1px solid #e3e0e5;border-radius:16px!important;box-shadow:0 10px 28px rgba(48,40,52,.07)!important}.plan-editor-panel{border-top:4px solid var(--pe-orange)}.plan-editor-features{border-top-color:var(--pe-turquoise)}.plan-editor-preview{border-top:4px solid var(--pe-green)}.plan-editor-panel-head{position:relative;background:linear-gradient(135deg,#fff,#faf9fb);border-color:#ebe8ed!important}.plan-editor-panel-head>p:first-child{display:flex;align-items:center;gap:7px;color:var(--pe-turquoise)!important}.plan-editor-panel-head h2{color:var(--pe-ink)!important;font-weight:900!important;letter-spacing:-.025em}.plan-editor-fields label,.plan-editor-features label{color:#514957}.plan-editor-fields input[type=text],.plan-editor-fields input[type=number],.plan-editor-fields select,.plan-editor-fields textarea,.plan-editor-features input[type=text],.plan-editor-features input[type=number],.plan-editor-features select{min-height:48px;border:1px solid #ded9e1!important;border-radius:10px!important;background:#fbfafc!important;box-shadow:none!important;transition:.18s}.plan-editor-fields textarea{min-height:118px}.plan-editor-fields input:focus,.plan-editor-fields select:focus,.plan-editor-fields textarea:focus,.plan-editor-features input:focus,.plan-editor-features select:focus{border-color:var(--pe-turquoise)!important;background:#fff!important;box-shadow:0 0 0 3px rgba(17,126,140,.12)!important}.plan-editor-fields select{appearance:none;background-image:linear-gradient(45deg,transparent 50%,#7a717d 50%),linear-gradient(135deg,#7a717d 50%,transparent 50%)!important;background-position:calc(100% - 18px) 21px,calc(100% - 13px) 21px!important;background-repeat:no-repeat!important;background-size:5px 5px!important;padding-right:40px!important}.plan-editor-fields label[for=activo]{min-height:73px;border:1px solid #ded9e1!important;border-radius:12px!important;background:#f8faf7!important;box-shadow:none!important}.plan-editor-fields #activo{width:42px!important;height:23px!important;appearance:none;border:0!important;border-radius:999px!important;background:#c9cbd0!important;box-shadow:inset 0 0 0 1px rgba(0,0,0,.08)!important;cursor:pointer;transition:.2s}.plan-editor-fields #activo:before{content:'';display:block;width:17px;height:17px;margin:3px;border-radius:50%;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,.2);transition:.2s}.plan-editor-fields #activo:checked{background:var(--pe-green)!important}.plan-editor-fields #activo:checked:before{transform:translateX(19px)}.plan-editor-add-feature,.plan-editor .add-feature-trigger{border-radius:9px!important;background:var(--pe-turquoise)!important;box-shadow:0 7px 16px rgba(17,126,140,.18)}.plan-editor-add-feature:hover,.plan-editor .add-feature-trigger:hover{filter:brightness(.93);transform:translateY(-1px)}.plan-editor #features-container>p{border-color:#cce2e5!important;border-radius:10px!important;background:#edf7f8!important;color:#0d6975!important}.plan-editor .feature-card{position:relative;border:1px solid #dfdbe2!important;border-left:4px solid var(--pe-orange)!important;border-radius:12px!important;background:#fff!important;box-shadow:0 6px 18px rgba(48,40,52,.06)!important}.plan-editor .feature-card:hover{border-color:#cec8d1!important;transform:translateY(-2px)}.plan-editor .feature-card>div:first-child>div:first-child{border-radius:7px!important;background:#f1f3f5!important;color:#6d6570!important;box-shadow:inset 0 0 0 1px #ddd9df!important}.plan-editor .feature-card .feature-number{color:var(--pe-orange)!important}.plan-editor .feature-card .remove-feature{border-radius:8px!important}.plan-editor .feature-card .open-feature-modal{border-radius:9px!important;background:var(--pe-turquoise)!important}.plan-editor .feature-card>label:last-child{border-color:#f0dfbd!important;border-radius:10px!important;background:#fff9eb!important}.plan-editor-preview .rounded-2xl{border:1px solid #ebe8ed;border-radius:10px!important;background:#faf9fb!important}.plan-editor-preview .open-feature-edit-modal{background:#fff!important}.plan-editor-save{border-radius:16px!important;background:linear-gradient(145deg,#302834,#43394a)!important;box-shadow:0 12px 26px rgba(48,40,52,.18)!important}.plan-editor-submit{display:flex;align-items:center;justify-content:center;gap:8px;background:var(--pe-orange)!important;color:#fff!important}.plan-editor-submit:hover{filter:brightness(.93);transform:translateY(-1px)}
    .plan-editor-errors{max-width:calc(1536px - 48px);margin-right:auto!important;margin-left:auto!important}.plan-editor-errors{margin-top:24px!important}
    .plan-custom-select{position:relative}.plan-custom-select-native{position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important}.plan-custom-select-trigger{width:100%;height:48px;display:flex;align-items:center;gap:11px;padding:0 42px 0 14px;border:1px solid #ded9e1;border-radius:10px;background:#fbfafc;color:#514957;text-align:left;transition:.18s}.plan-custom-select-trigger:hover,.plan-custom-select.is-open .plan-custom-select-trigger{border-color:#117e8c;background:#fff;box-shadow:0 0 0 3px rgba(17,126,140,.12)}.plan-custom-select-trigger>i:first-child{width:21px;color:#117e8c;text-align:center}.plan-custom-select-trigger span{min-width:0;overflow:hidden;flex:1;font-size:.82rem;font-weight:700;text-overflow:ellipsis;white-space:nowrap}.plan-custom-select-trigger .fa-chevron-down{position:absolute;right:15px;color:#817983;font-size:.68rem;transition:.18s}.plan-custom-select.is-open .fa-chevron-down{transform:rotate(180deg)}.plan-custom-select-menu{position:absolute;z-index:100;top:calc(100% + 7px);right:0;left:0;display:none;padding:7px;border:1px solid #ded9e1;border-radius:11px;background:#fff;box-shadow:0 18px 38px rgba(48,40,52,.18)}.plan-custom-select.is-open .plan-custom-select-menu{display:grid;gap:3px}.plan-custom-select-option{min-height:40px;display:flex;align-items:center;justify-content:space-between;padding:8px 11px;border-radius:7px;color:#605763;text-align:left;font-size:.78rem;font-weight:700}.plan-custom-select-option:hover,.plan-custom-select-option.is-selected{background:#edf7f8;color:#0d6975}.plan-custom-select-option.is-selected:after{content:'✓';width:20px;height:20px;display:grid;place-items:center;border-radius:50%;background:#117e8c;color:#fff;font-size:.6rem}.plan-editor .feature-card input[type=checkbox]{position:relative;width:24px!important;height:24px!important;flex:none;appearance:none;border:1px solid #cfc4ab!important;border-radius:7px!important;background:#fff!important;box-shadow:0 2px 5px rgba(80,64,35,.08)!important;cursor:pointer;transition:.18s}.plan-editor .feature-card input[type=checkbox]:hover{border-color:#ef6c22!important}.plan-editor .feature-card input[type=checkbox]:checked{border-color:#ef6c22!important;background:#ef6c22!important}.plan-editor .feature-card input[type=checkbox]:checked:after{content:'✓';position:absolute;inset:0;display:grid;place-items:center;color:#fff;font-size:.78rem;font-weight:900}.plan-editor .feature-card input[type=checkbox]:focus{box-shadow:0 0 0 3px rgba(239,108,34,.15)!important}.plan-editor .feature-card>label:last-child:has(input:checked){border-color:#efc69e!important;background:#fff5eb!important}
    #feature-modal{z-index:12000!important;background:rgba(15,23,42,.66)!important;backdrop-filter:blur(5px)}#feature-modal>div{overflow:hidden;border:1px solid #e0dce3;border-top:5px solid #117e8c;border-radius:16px!important;box-shadow:0 28px 70px rgba(15,23,42,.32)!important}#feature-modal-kicker{color:#117e8c!important}#feature-modal-name{border-radius:10px!important}#close-feature-modal{font-size:0!important}#close-feature-modal:after{content:'×';font-size:25px;line-height:1}#save-feature-modal{border-radius:9px!important;background:#117e8c!important}#cancel-feature-modal{border-radius:9px!important}
    @media(max-width:1279px){.plan-editor-form{grid-template-columns:1fr!important}.plan-editor-sidebar{display:grid;grid-template-columns:1fr 1fr;position:static!important}.plan-editor-hero-grid{grid-template-columns:1fr!important}.plan-editor-stats{grid-template-columns:repeat(3,1fr)!important}}@media(max-width:767px){.plan-editor-hero-grid{padding:26px 18px!important}.plan-editor-stats{grid-template-columns:1fr!important}.plan-editor-stat{grid-column:auto!important;padding:18px!important}.plan-editor-form{padding:0 12px}.plan-editor-sidebar{grid-template-columns:1fr}.plan-editor-panel-head,.plan-editor-fields,.plan-editor-features>div:last-child{padding-right:18px!important;padding-left:18px!important}.plan-editor .feature-card>div:first-child{align-items:flex-start;flex-wrap:wrap}.plan-editor .feature-card>div:first-child>div:nth-child(2){order:-1;width:100%}}
</style>

<div class="plan-editor min-h-screen">
    <div class="plan-editor-shell mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="plan-editor-hero overflow-hidden text-white">
            <div class="plan-editor-hero-grid grid gap-8 px-6 py-8 lg:grid-cols-2 lg:px-10">
                <div>
                    <div class="plan-editor-kicker inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em]">
                        <i class="fas fa-layer-group"></i> Administrador de planes
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">{{ $isCreating ? 'Crear nuevo plan' : 'Editar ' . $plan->nombre }}</h1>
                    <p class="plan-editor-intro">{{ $isCreating ? 'Diseña la presentación, el precio y los beneficios del nuevo plan.' : 'Actualiza la presentación, el precio y los beneficios que verán tus clientes.' }}</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('administrador.planes.index') }}" class="plan-editor-back inline-flex items-center rounded-full bg-white px-5 py-2.5 text-sm font-semibold transition">
                            <i class="fas fa-arrow-left"></i> Volver a planes
                        </a>
                        <span class="plan-editor-context inline-flex items-center rounded-full px-4 py-2 text-sm">
                            <i class="fas {{ $isCreating ? 'fa-wand-magic-sparkles' : 'fa-pen-to-square' }}"></i> {{ $isCreating ? 'Creación con vista previa en vivo' : 'Edición del plan seleccionado' }}
                        </span>
                    </div>
                </div>

                <div class="plan-editor-stats grid gap-5 md:grid-cols-2 lg:col-span-1 xl:grid-cols-3">
                    <div class="plan-editor-stat p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">{{ $isCreating ? 'Precio inicial' : 'Precio actual' }}</p>
                        <p class="mt-3 text-3xl font-black leading-none">{{ number_format((float) $plan->precio, 2, '.', ',') }}</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $plan->moneda }} por {{ $plan->periodo_facturacion }}</p>
                    </div>
                    <div class="plan-editor-stat p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Caracteristicas</p>
                        <p class="mt-3 text-3xl font-black leading-none">{{ count($featuresToShow) }}</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $featuredCount }} destacadas</p>
                    </div>
                    <div class="plan-editor-stat p-6 md:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Estado del plan</p>
                        <p class="mt-3 text-xl font-bold leading-tight">{{ old('activo', $plan->activo) ? 'Activo' : 'Inactivo' }}</p>
                        <p class="mt-2 text-sm text-gray-200">Orden de visualizacion: {{ old('orden', $plan->orden ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="plan-editor-errors mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-sm">
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

        @if($isCreating)
            @include('administrador.planes.partials.create-wizard')
        @endif

        <form action="{{ $isCreating ? route('administrador.planes.store') : route('administrador.planes.update', $plan->id) }}" method="POST" class="plan-editor-form {{ $isCreating ? 'is-wizard-pending' : '' }} mt-6 grid gap-6 xl:grid-cols-3">
            @csrf
            @unless($isCreating)
                @method('PUT')
            @endunless

            <div class="plan-editor-main space-y-6 xl:col-span-2">
                <section class="plan-editor-panel overflow-hidden bg-white">
                    <div class="plan-editor-panel-head border-b border-gray-200 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700"><i class="fas fa-sliders"></i> Información comercial</p>
                        <h2 class="mt-2 text-2xl font-bold text-gray-900">Informacion principal</h2>
                        <p class="mt-2 text-sm text-gray-500">Define como se presenta el plan y cuanto se cobra.</p>
                    </div>

                    <div class="plan-editor-fields grid gap-6 px-6 py-6 sm:px-8 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label for="nombre" class="mb-2 block text-sm font-semibold text-gray-700">Nombre del plan *</label>
                            <input type="text" id="nombre" name="nombre" required value="{{ old('nombre', $plan->nombre) }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            @error('nombre')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label for="subtitulo" class="mb-2 block text-sm font-semibold text-gray-700">Subtitulo</label>
                            <input type="text" id="subtitulo" name="subtitulo" value="{{ old('subtitulo', $plan->subtitulo) }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Resume la propuesta del plan en una frase corta">
                        </div>

                        <div>
                            <label for="precio" class="mb-2 block text-sm font-semibold text-gray-700">Precio *</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-gray-400">Bs/$</span>
                                <input type="number" step="0.01" min="0" id="precio" name="precio" required value="{{ old('precio', $plan->precio) }}"
                                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-14 pr-4 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                            @error('precio')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="moneda" class="mb-2 block text-sm font-semibold text-gray-700">Moneda *</label>
                            <select id="moneda" name="moneda" required data-plan-custom-select data-custom-icon="fa-coins"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <option value="BS" {{ old('moneda', $plan->moneda) == 'BS' ? 'selected' : '' }}>Bolivianos (Bs)</option>
                                <option value="USD" {{ old('moneda', $plan->moneda) == 'USD' ? 'selected' : '' }}>Dolares (USD)</option>
                            </select>
                        </div>

                        <div>
                            <label for="periodo_facturacion" class="mb-2 block text-sm font-semibold text-gray-700">Periodo de facturacion *</label>
                            <select id="periodo_facturacion" name="periodo_facturacion" required data-plan-custom-select data-custom-icon="fa-calendar-days"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <option value="mes" {{ old('periodo_facturacion', $plan->periodo_facturacion) == 'mes' ? 'selected' : '' }}>Mes</option>
                                <option value="trimestre" {{ old('periodo_facturacion', $plan->periodo_facturacion) == 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                                <option value="semestre" {{ old('periodo_facturacion', $plan->periodo_facturacion) == 'semestre' ? 'selected' : '' }}>Semestre</option>
                                <option value="año" {{ old('periodo_facturacion', $plan->periodo_facturacion) == 'año' ? 'selected' : '' }}>Anual</option>
                            </select>
                            @error('periodo_facturacion')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="orden" class="mb-2 block text-sm font-semibold text-gray-700">Orden de visualizacion</label>
                            <input type="number" min="0" id="orden" name="orden" value="{{ old('orden', $plan->orden) }}"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="0">
                        </div>

                        <div class="flex items-end">
                            <label for="activo" class="flex w-full items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 shadow-sm transition hover:border-blue-300 hover:bg-blue-50/60">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Plan activo</p>
                                    <p class="text-xs text-gray-500">Disponible para nuevas contrataciones</p>
                                </div>
                                <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $plan->activo) ? 'checked' : '' }}
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </label>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="descripcion" class="mb-2 block text-sm font-semibold text-gray-700">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" rows="4"
                                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                placeholder="Describe el enfoque, alcance y valor del plan.">{{ old('descripcion', $plan->descripcion) }}</textarea>
                        </div>

                        
                    </div>
                </section>

                <section class="plan-editor-panel plan-editor-features overflow-hidden bg-white">
                    <div class="plan-editor-panel-head flex flex-col gap-4 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700"><i class="fas fa-list-check"></i> Beneficios incluidos</p>
                            <h2 class="mt-2 text-2xl font-bold text-gray-900">Caracteristicas del plan</h2>
                            <p class="mt-2 text-sm text-gray-500">Ordena beneficios y marca como destacados los mas importantes.</p>
                        </div>
                        <button type="button" id="add-feature" class="plan-editor-add-feature inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold text-white transition">
                            <i class="fas fa-plus mr-2"></i>
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
                                            <p class="text-sm font-semibold text-gray-900">Característica <span class="feature-number text-blue-700">{{ $index + 1 }}</span></p>
                                            <p class="text-xs text-gray-500">Configura cantidad, frecuencia y prioridad visual.</p>
                                        </div>
                                        <button type="button" class="remove-feature inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
                                        <div class="xl:col-span-6">
                                            <label class="mb-2 block text-sm font-semibold text-gray-700">Característica *</label>
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

            <aside class="plan-editor-sidebar space-y-6 xl:sticky xl:top-6 xl:self-start">
                @include('administrador.planes.partials.live-preview-card', [
                    'previewName' => old('nombre', $plan->nombre),
                    'previewSubtitle' => old('subtitulo', $plan->subtitulo ?: $plan->descripcion),
                    'previewPrice' => number_format((float) old('precio', $plan->precio), 0, ',', '.') . ' ' . (old('moneda', $plan->moneda) === 'BS' ? 'Bs.' : old('moneda', $plan->moneda)),
                    'previewPeriod' => old('periodo_facturacion', $plan->periodo_facturacion),
                    'previewActive' => (bool) old('activo', $plan->activo),
                    'previewFeatures' => $caracteristicas->whereIn('id', $selectedFeatureIds),
                ])

                <section class="plan-editor-save overflow-hidden text-white">
                    <div class="px-6 py-6">
                        <h2 class="text-xl font-bold">Listo para guardar</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-300">{{ $isCreating ? 'Cuando termines, guarda el nuevo plan y sus características asociadas.' : 'Cuando termines, guarda los cambios para actualizar el plan y sus características asociadas.' }}</p>
                        <div class="mt-6 space-y-3">
                            <button type="submit" class="plan-editor-submit w-full rounded-2xl px-5 py-3 text-sm font-semibold transition">
                                <i class="fas fa-floppy-disk"></i> {{ $isCreating ? 'Crear plan' : 'Actualizar plan' }}
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
                <p id="feature-modal-description-text" class="mt-2 text-sm text-gray-500">Agrega una nueva opción sin salir de {{ $isCreating ? 'la creación' : 'la edición' }} del plan.</p>
            </div>
            <button type="button" id="close-feature-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-500 transition hover:bg-gray-200 hover:text-gray-700" aria-label="Cerrar modal">
                �
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
    const nameInput = document.getElementById('nombre');
    const subtitleInput = document.getElementById('subtitulo');
    const priceInput = document.getElementById('precio');
    const currencyInput = document.getElementById('moneda');
    const billingInput = document.getElementById('periodo_facturacion');
    const activeInput = document.getElementById('activo');
    const descriptionInput = document.getElementById('descripcion');
    const summaryName = document.getElementById('plan-summary-name');
    const summarySubtitle = document.getElementById('plan-summary-subtitle');
    const summaryPrice = document.getElementById('plan-summary-price');
    const summaryPeriod = document.getElementById('plan-summary-period');
    const summaryStatus = document.getElementById('plan-summary-status');
    const storeFeatureUrl = @json(route('administrador.planes.caracteristicas.store'));
    const updateFeatureUrlTemplate = @json(route('administrador.planes.caracteristicas.update', ['caracteristica' => '__ID__']));
    const csrfToken = @json(csrf_token());
    let featureCount = {{ count($featuresToShow) }};
    let activeSelect = null;
    let activeFeature = null;
    let draggedCard = null;

    function renderPlanPreview() {
        const price = Number.parseFloat(priceInput?.value || 0);
        const currency = currencyInput?.value || 'BS';
        summaryName.textContent = nameInput?.value.trim() || 'Sin nombre';
        summarySubtitle.textContent = subtitleInput?.value.trim() || descriptionInput?.value.trim() || 'Agrega un subtítulo corto para este plan.';
        summaryPrice.textContent = (Number.isNaN(price) ? 0 : price).toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' ' + (currency === 'BS' ? 'Bs.' : currency);
        summaryPeriod.textContent = '/ ' + (billingInput?.value || 'mes');
        const isActive = Boolean(activeInput?.checked);
        summaryStatus.classList.toggle('is-active', isActive);
        summaryStatus.classList.toggle('is-inactive', !isActive);
        summaryStatus.innerHTML = '<i class="fas ' + (isActive ? 'fa-circle-check' : 'fa-circle-pause') + '"></i>' + (isActive ? 'Activo' : 'Inactivo');
    }

    const customSelectWrappers = [];
    function closePlanCustomSelects(exceptWrapper) {
        customSelectWrappers.forEach(function (wrapper) {
            if (wrapper === exceptWrapper) {
                return;
            }
            wrapper.classList.remove('is-open');
            wrapper.querySelector('.plan-custom-select-trigger')?.setAttribute('aria-expanded', 'false');
        });
    }
    document.querySelectorAll('[data-plan-custom-select]').forEach(function (select) {
        const wrapper = document.createElement('div');
        const trigger = document.createElement('button');
        const leadingIcon = document.createElement('i');
        const selectedLabel = document.createElement('span');
        const chevron = document.createElement('i');
        const menu = document.createElement('div');

        wrapper.className = 'plan-custom-select';
        trigger.type = 'button';
        trigger.className = 'plan-custom-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        leadingIcon.className = 'fas ' + (select.dataset.customIcon || 'fa-list');
        chevron.className = 'fas fa-chevron-down';
        menu.className = 'plan-custom-select-menu';
        menu.setAttribute('role', 'listbox');
        select.classList.add('plan-custom-select-native');

        trigger.appendChild(leadingIcon);
        trigger.appendChild(selectedLabel);
        trigger.appendChild(chevron);
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        select.insertAdjacentElement('afterend', wrapper);
        customSelectWrappers.push(wrapper);

        function synchronizeCustomSelect() {
            const selectedOption = select.options[select.selectedIndex];
            selectedLabel.textContent = selectedOption ? selectedOption.textContent : 'Seleccionar';
            menu.querySelectorAll('.plan-custom-select-option').forEach(function (optionButton) {
                optionButton.classList.toggle('is-selected', optionButton.dataset.value === select.value);
            });
        }

        Array.from(select.options).forEach(function (option) {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'plan-custom-select-option';
            optionButton.dataset.value = option.value;
            optionButton.textContent = option.textContent;
            optionButton.setAttribute('role', 'option');
            optionButton.addEventListener('click', function (event) {
                event.stopPropagation();
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                synchronizeCustomSelect();
                closePlanCustomSelects();
            });
            menu.appendChild(optionButton);
        });

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            const shouldOpen = !wrapper.classList.contains('is-open');
            closePlanCustomSelects(wrapper);
            wrapper.classList.toggle('is-open', shouldOpen);
            trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        });
        select.addEventListener('change', synchronizeCustomSelect);
        synchronizeCustomSelect();
    });
    document.addEventListener('click', function () {
        closePlanCustomSelects();
    });

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
            summaryList.innerHTML = '<span class="plan-live-empty">Agrega al menos una característica.</span>';
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
            const checkIcon = document.createElement('i');
            const label = document.createElement('span');
            const editIcon = document.createElement('i');
            const featureSelect = Array.from(container.querySelectorAll('.feature-select')).find(function (select) {
                return select.value === featureId;
            });
            const featureCard = featureSelect?.closest('[data-feature-card]');
            const amount = featureCard?.querySelector('input[name$="[cantidad]"]')?.value;
            const frequency = featureCard?.querySelector('input[name$="[frecuencia]"]')?.value.trim();
            button.type = 'button';
            button.className = 'open-feature-edit-modal plan-live-feature';
            button.dataset.featureId = featureId;
            button.dataset.featureName = option.textContent;
            button.title = 'Editar característica ' + option.textContent;
            checkIcon.className = 'fas fa-check';
            label.textContent = option.textContent;
            editIcon.className = 'fas fa-pen';
            if (amount || frequency) {
                const detail = document.createElement('small');
                detail.textContent = [amount, frequency].filter(Boolean).join(' · ');
                label.appendChild(detail);
            }
            button.appendChild(checkIcon);
            button.appendChild(label);
            button.appendChild(editIcon);
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
            modalDescriptionText.textContent = 'Agrega una nueva opcion sin salir de la edicion del plan.';
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
        if (event.key === 'Escape') {
            closePlanCustomSelects();
            if (modal.classList.contains('flex')) {
                closeFeatureModal();
            }
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

            closeFeatureModal();
        } catch (error) {
            modalError.textContent = error.message;
            modalError.classList.remove('hidden');
        } finally {
            saveModalButton.disabled = false;
            saveModalButton.textContent = activeFeature ? saveModalButton.dataset.editLabel : saveModalButton.dataset.createLabel;
        }
    });

    [nameInput, subtitleInput, priceInput, currencyInput, billingInput, activeInput, descriptionInput].forEach(function (field) {
        field?.addEventListener('input', renderPlanPreview);
        field?.addEventListener('change', renderPlanPreview);
    });
    container.addEventListener('input', function (event) {
        if (event.target.matches('input[name$="[cantidad]"], input[name$="[frecuencia]"]')) {
            renderFeatureSummary();
        }
    });

    if (featureCount === 0) {
        addFeature();
    } else {
        initializeAllSearchableSelects(container);
        syncFeatureIndexes();
    }
    renderPlanPreview();
    renderFeatureSummary();
});
</script>
@endpush
@endsection
























