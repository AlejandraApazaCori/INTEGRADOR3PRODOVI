@extends('layouts.app')

@section('title', 'Editar tarea')

@section('content')
@php
    $campania = $tarea->campania;
    $inicioCampania = \Carbon\Carbon::parse($campania->fecha_inicio);
    $finCampania = \Carbon\Carbon::parse($campania->fecha_fin);
    $estadoClase = match($tarea->estado) {
        'entregado', 'aprobado', 'publicado' => 'is-complete',
        'en_curso' => 'is-progress',
        'reformular' => 'is-rejected',
        default => 'is-pending',
    };
@endphp

<div class="task-edit-page">
    <div class="task-edit-shell">
        <nav class="task-edit-actions" aria-label="Navegación de tarea">
            <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}"><i class="fas fa-arrow-left"></i> Campaña</a>
            <a href="{{ route('administrador.campañas.calendario', $tarea->campania_id) }}"><i class="fas fa-calendar-days"></i> Planificación</a>
            <a href="{{ route('administrador.tareas.ver-subidas', $tarea->id) }}"><i class="fas fa-folder-open"></i> Entregables</a>
        </nav>

        <header class="task-edit-hero">
            <div class="task-edit-overlay"></div>
            <div class="task-edit-hero-content">
                <div>
                    <span>Gestión de tareas</span>
                    <h1>Editar tarea</h1>
                    <p>{{ $tarea->titulo }}</p>
                </div>
                <span class="task-edit-status {{ $estadoClase }}"><i></i>{{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}</span>
            </div>
        </header>

        <main class="task-edit-content">
            @if($errors->any())
                <div class="task-edit-alert" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
            @endif

            <form action="{{ route('administrador.tareas.update', $tarea->id) }}" method="POST" class="task-edit-card">
                @csrf
                @method('PUT')

                <header class="task-edit-card-header">
                    <div>
                        <span>Información editable</span>
                        <h2>Datos de la tarea</h2>
                        <p>Modifica los campos necesarios y guarda los cambios.</p>
                    </div>
                    <small>Tarea #{{ $tarea->id }}</small>
                </header>

                <div class="task-edit-context">
                    <div><small>Campaña</small><strong>{{ $campania->nombre }}</strong></div>
                    <div><small>Periodo permitido</small><strong>{{ $inicioCampania->format('d/m/Y') }} — {{ $finCampania->format('d/m/Y') }}</strong></div>
                    <div><small>Responsable actual</small><strong>{{ $tarea->asignado?->name ?? 'Sin asignar' }}</strong></div>
                </div>

                <div class="task-edit-form">
                    <div class="task-edit-field task-edit-full">
                        <label for="titulo"><span>Título</span><small>Nombre corto y fácil de identificar</small></label>
                        <input type="text" name="titulo" id="titulo" maxlength="100" value="{{ old('titulo', $tarea->titulo) }}" required class="@error('titulo') is-invalid @enderror">
                        @error('titulo')<p>{{ $message }}</p>@enderror
                    </div>

                    <div class="task-edit-field task-edit-full">
                        <label for="descripcion"><span>Descripción</span><small>Objetivo, alcance y resultado esperado</small></label>
                        <textarea name="descripcion" id="descripcion" rows="5" required class="@error('descripcion') is-invalid @enderror">{{ old('descripcion', $tarea->descripcion) }}</textarea>
                        @error('descripcion')<p>{{ $message }}</p>@enderror
                    </div>

                    <div class="task-edit-field task-edit-full">
                        <label for="asignado_id"><span>Responsable</span><small>Persona encargada de completar la tarea</small></label>
                        <select name="asignado_id" id="asignado_id" required data-custom-select data-placeholder="Selecciona un responsable" class="@error('asignado_id') is-invalid @enderror">
                            <option value="">Selecciona un responsable</option>
                            @foreach($asignables->sortBy('name') as $user)
                                @php
                                    $rolesTexto = $user->roles->pluck('nombre_rol')->filter()->implode(', ') ?: 'Sin rol';
                                @endphp
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-roles="{{ $user->roles->pluck('nombre_rol')->filter()->implode('|') ?: 'Sin rol' }}" {{ (string) old('asignado_id', $tarea->asignado_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->name }} — {{ $rolesTexto }}</option>
                            @endforeach
                        </select>
                        @error('asignado_id')<p>{{ $message }}</p>@enderror
                    </div>

                    <div class="task-edit-field">
                        <label for="prioridad"><span>Prioridad</span><small>Nivel de atención requerido</small></label>
                        <select name="prioridad" id="prioridad" required data-custom-select data-placeholder="Selecciona una prioridad" class="@error('prioridad') is-invalid @enderror">
                            <option value="baja" {{ old('prioridad', $tarea->prioridad) === 'baja' ? 'selected' : '' }}>Baja</option>
                            <option value="media" {{ old('prioridad', $tarea->prioridad) === 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ old('prioridad', $tarea->prioridad) === 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="urgente" {{ old('prioridad', $tarea->prioridad) === 'urgente' ? 'selected' : '' }}>Urgente</option>
                        </select>
                        @error('prioridad')<p>{{ $message }}</p>@enderror
                    </div>

                    <div class="task-edit-dates">
                        <div class="task-edit-field">
                            <label for="fecha_inicio"><span>Fecha de inicio</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" min="{{ $inicioCampania->format('Y-m-d') }}" max="{{ $finCampania->format('Y-m-d') }}" value="{{ old('fecha_inicio', $tarea->fecha_inicio->format('Y-m-d')) }}" required data-custom-date class="@error('fecha_inicio') is-invalid @enderror">
                            @error('fecha_inicio')<p>{{ $message }}</p>@enderror
                        </div>
                        <div class="task-edit-field">
                            <label for="fecha_limite"><span>Fecha límite</span></label>
                            <input type="date" name="fecha_limite" id="fecha_limite" min="{{ old('fecha_inicio', $tarea->fecha_inicio->format('Y-m-d')) }}" max="{{ $finCampania->format('Y-m-d') }}" value="{{ old('fecha_limite', $tarea->fecha_limite->format('Y-m-d')) }}" required data-custom-date class="@error('fecha_limite') is-invalid @enderror">
                            @error('fecha_limite')<p>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <footer class="task-edit-footer">
                    <span>Todos los campos son obligatorios.</span>
                    <div>
                        <a href="{{ route('administrador.campañas.show', $tarea->campania_id) }}"><i class="fas fa-xmark"></i> Cancelar</a>
                        <button type="submit"><i class="fas fa-check"></i> Guardar cambios</button>
                    </div>
                </footer>
            </form>
        </main>
    </div>
</div>

<style>
    .task-edit-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}.task-edit-shell{position:relative;width:100%}
    .task-edit-hero{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#1d4ed8}.task-edit-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .task-edit-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:22px;padding:30px 450px 30px max(48px,calc((100% - 980px)/2))}.task-edit-hero-content>div>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.task-edit-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.task-edit-hero p{margin:5px 0 0;color:#dbeafe;font-size:.74rem;font-weight:600}.task-edit-status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(255,255,255,.42);border-radius:999px;background:rgba(255,255,255,.14);color:#fff;font-size:.61rem;font-weight:900;text-transform:uppercase}.task-edit-status i{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.task-edit-status.is-complete i{background:#bef264}.task-edit-status.is-progress i{background:#fde047}.task-edit-status.is-rejected i{background:#fda4af}
    .task-edit-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:8px}.task-edit-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 12px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.66rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}.task-edit-actions a:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .task-edit-content{width:min(980px,calc(100% - 48px));margin:24px auto 0}.task-edit-alert{margin-bottom:14px;padding:12px 14px;display:flex;align-items:center;gap:9px;border:1px solid #f3c4c4;border-radius:10px;background:#fff0f0;color:#a72d2d;font-size:.64rem;font-weight:800}.task-edit-card{overflow:visible;border:1px solid #e1e3de;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(55,60,52,.055)}
    .task-edit-card-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:19px 21px 15px;border-bottom:1px solid #e8ebe5}.task-edit-card-header>div>span{display:block;margin-bottom:3px;color:#117e8c;font-size:.56rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.task-edit-card-header h2{margin:0;color:#302832;font-size:1rem;font-weight:900}.task-edit-card-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.task-edit-card-header p{margin:7px 0 0;color:#7e867b;font-size:.59rem}.task-edit-card-header>small{padding:6px 9px;border-radius:999px;background:#edf7f8;color:#117e8c;font-size:.54rem;font-weight:900}
    .task-edit-context{display:grid;grid-template-columns:1.2fr 1fr 1fr;border-bottom:1px solid #e8ebe5;background:#fafbf9}.task-edit-context>div{min-width:0;padding:12px 16px;border-right:1px solid #e5e8e2}.task-edit-context>div:last-child{border-right:0}.task-edit-context small,.task-edit-context strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-edit-context small{color:#899087;font-size:.52rem;font-weight:850;text-transform:uppercase}.task-edit-context strong{margin-top:4px;color:#343a32;font-size:.62rem;font-weight:900}
    .task-edit-form{display:grid;grid-template-columns:180px minmax(0,1fr);gap:17px;padding:21px}.task-edit-full{grid-column:1/-1}.task-edit-field label{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin:0 2px 7px}.task-edit-field label span{color:#3d453b;font-size:.64rem;font-weight:900}.task-edit-field label small{color:#92998f;font-size:.54rem;font-weight:600;text-align:right}.task-edit-field input,.task-edit-field select,.task-edit-field textarea{width:100%;border:1px solid #d7dcd4;border-radius:10px;background:#fff;color:#30372e;font-family:inherit;font-size:.68rem;font-weight:650;outline:0;box-shadow:0 2px 5px rgba(55,60,52,.04);transition:.18s}.task-edit-field input,.task-edit-field select{height:45px;padding:0 12px}.task-edit-field textarea{min-height:128px;padding:12px;line-height:1.55;resize:vertical}.task-edit-field input:focus,.task-edit-field select:focus,.task-edit-field textarea:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.task-edit-field .is-invalid{border-color:#dc5b5b;box-shadow:0 0 0 3px rgba(220,91,91,.1)}.task-edit-field>p{margin:5px 2px 0;color:#b53c3c;font-size:.55rem;font-weight:750}.task-edit-dates{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .task-custom-control{position:relative;min-width:0}.task-native-control{position:absolute!important;width:1px!important;height:1px!important;margin:0!important;padding:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important;opacity:0!important;pointer-events:none!important}.task-custom-trigger{width:100%;height:45px;display:flex;align-items:center;gap:10px;padding:0 12px;border:1px solid #d7dcd4;border-radius:10px;background:#fff;color:#30372e;font-family:inherit;font-size:.68rem;font-weight:750;text-align:left;cursor:pointer;outline:0;box-shadow:0 2px 5px rgba(55,60,52,.04);transition:.18s}.task-custom-trigger>i:first-child{width:17px;color:#117e8c;text-align:center}.task-custom-trigger span{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-custom-trigger .task-control-chevron{color:#98a096;font-size:.56rem;transition:transform .18s}.task-custom-trigger:hover{border-color:#aeb9ac}.task-custom-trigger:focus-visible,.task-custom-control.is-open>.task-custom-trigger{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.1)}.task-custom-control.is-open>.task-custom-trigger .task-control-chevron{transform:rotate(180deg)}.task-custom-control.has-error>.task-custom-trigger{border-color:#dc5b5b;box-shadow:0 0 0 3px rgba(220,91,91,.1)}
    .task-custom-menu{position:absolute;z-index:70;top:calc(100% + 6px);right:0;left:0;display:none;padding:5px;border:1px solid #dfe4dc;border-radius:11px;background:#fff;box-shadow:0 16px 35px rgba(45,54,43,.16)}.task-custom-menu.is-searchable{min-width:min(480px,calc(100vw - 48px))}.task-custom-control.is-open>.task-custom-menu{display:block;animation:taskControlIn .14s ease-out}.task-custom-options{max-height:250px;overflow-y:auto}.task-select-tools{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(135px,.8fr);gap:6px;margin-bottom:5px;padding:3px 3px 8px;border-bottom:1px solid #edf0eb}.task-select-search{position:relative}.task-select-search i{position:absolute;top:50%;left:10px;color:#117e8c;font-size:.58rem;transform:translateY(-50%);pointer-events:none}.task-select-search input,.task-role-filter{width:100%;height:36px;border:1px solid #dbe1d8;border-radius:8px;background:#fafcf9;color:#3f473d;font-family:inherit;font-size:.59rem;font-weight:700;outline:0}.task-select-search input{padding:0 9px 0 29px}.task-role-filter{padding:0 27px 0 9px;cursor:pointer}.task-select-search input:focus,.task-role-filter:focus{border-color:#117e8c;box-shadow:0 0 0 2px rgba(17,126,140,.09)}.task-custom-option{width:100%;min-height:37px;display:flex;align-items:center;gap:8px;padding:8px 9px;border:0;border-radius:7px;background:transparent;color:#4a5248;font-family:inherit;font-size:.62rem;font-weight:700;text-align:left;cursor:pointer}.task-custom-option span{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-custom-option i{color:#117e8c;opacity:0}.task-custom-option:hover,.task-custom-option:focus-visible{background:#f0f7f7;color:#117e8c;outline:0}.task-custom-option.is-selected{background:#e7f4f5;color:#0d707c;font-weight:900}.task-custom-option.is-selected i{opacity:1}.task-select-empty{display:none;padding:18px 10px;color:#8b9388;font-size:.58rem;font-weight:750;text-align:center}.task-select-empty.is-visible{display:block}
    .task-role-dropdown{position:relative;min-width:0}.task-role-filter{display:flex;align-items:center;gap:7px;text-align:left}.task-role-filter span{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-role-filter i{color:#117e8c;font-size:.5rem;transition:transform .16s}.task-role-dropdown.is-open .task-role-filter{border-color:#117e8c;box-shadow:0 0 0 2px rgba(17,126,140,.09)}.task-role-dropdown.is-open .task-role-filter i{transform:rotate(180deg)}.task-role-menu{position:absolute;z-index:12;top:calc(100% + 5px);right:0;left:0;display:none;max-height:190px;padding:4px;overflow-y:auto;border:1px solid #dfe4dc;border-radius:9px;background:#fff;box-shadow:0 12px 28px rgba(45,54,43,.17)}.task-role-dropdown.is-open .task-role-menu{display:block;animation:taskControlIn .12s ease-out}.task-role-option{width:100%;min-height:33px;display:flex;align-items:center;gap:7px;padding:7px 8px;border:0;border-radius:6px;background:transparent;color:#596157;font-family:inherit;font-size:.57rem;font-weight:750;text-align:left;cursor:pointer}.task-role-option span{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.task-role-option i{color:#117e8c;opacity:0}.task-role-option:hover,.task-role-option:focus-visible{background:#f0f7f7;color:#117e8c;outline:0}.task-role-option.is-selected{background:#e7f4f5;color:#0d707c;font-weight:900}.task-role-option.is-selected i{opacity:1}
    .task-date-popover{position:absolute;z-index:80;top:calc(100% + 6px);right:0;display:none;width:294px;padding:13px;border:1px solid #dfe4dc;border-radius:13px;background:#fff;box-shadow:0 18px 40px rgba(45,54,43,.18)}.task-custom-control.is-open>.task-date-popover{display:block;animation:taskControlIn .14s ease-out}.task-date-header{display:grid;grid-template-columns:34px 1fr 34px;align-items:center;gap:5px;margin-bottom:11px}.task-date-header strong{text-align:center;color:#343c32;font-size:.68rem;font-weight:900;text-transform:capitalize}.task-date-nav{width:34px;height:32px;border:0;border-radius:8px;background:#f1f5f1;color:#5d675b;cursor:pointer}.task-date-nav:hover{background:#e5f2f3;color:#117e8c}.task-date-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}.task-date-weekday{padding:3px 0 5px;color:#9aa198;font-size:.49rem;font-weight:900;text-align:center}.task-date-day{aspect-ratio:1;border:0;border-radius:8px;background:transparent;color:#424a40;font-family:inherit;font-size:.58rem;font-weight:750;cursor:pointer}.task-date-day:hover:not(:disabled){background:#e7f4f5;color:#117e8c}.task-date-day.is-other{color:#b5bbb3}.task-date-day.is-today{box-shadow:inset 0 0 0 1px #117e8c;color:#117e8c}.task-date-day.is-selected{background:#117e8c!important;color:#fff!important;box-shadow:0 4px 9px rgba(17,126,140,.22)}.task-date-day:disabled{color:#d4d8d2;cursor:not-allowed}.task-date-footer{display:flex;justify-content:space-between;gap:8px;margin-top:11px;padding-top:10px;border-top:1px solid #edf0eb}.task-date-footer button{padding:6px 9px;border:0;border-radius:7px;background:#f0f4ef;color:#687065;font-family:inherit;font-size:.54rem;font-weight:900;cursor:pointer}.task-date-footer button:first-child{background:#e7f4f5;color:#117e8c}.task-date-footer button:disabled{opacity:.45;cursor:not-allowed}@keyframes taskControlIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
    .task-edit-footer{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:15px 21px;border-top:1px solid #e8ebe5;border-radius:0 0 14px 14px;background:#fafbf9}.task-edit-footer>span{color:#8a9187;font-size:.55rem}.task-edit-footer>div{display:flex;gap:8px}.task-edit-footer a,.task-edit-footer button{min-height:38px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 14px;border-radius:8px;font-size:.61rem;font-weight:900;text-decoration:none;cursor:pointer;transition:.18s}.task-edit-footer a{border:1px solid #d7dcd4;background:#fff;color:#626a60}.task-edit-footer button{border:1px solid #4f46e5;background:#4f46e5;color:#fff;box-shadow:0 5px 12px rgba(79,70,229,.18)}.task-edit-footer a:hover{background:#f2f4f0}.task-edit-footer button:hover{transform:translateY(-1px);background:#4338ca}
    @media(max-width:900px){.task-edit-actions{position:static;justify-content:center;padding:14px 24px 0}.task-edit-actions a{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.task-edit-hero{margin-top:14px}.task-edit-hero-content{padding:28px 24px}}
    @media(max-width:640px){.task-edit-page{padding-bottom:24px}.task-edit-actions{display:grid;grid-template-columns:1fr;padding:12px}.task-edit-actions a{width:100%}.task-edit-hero{min-height:195px;margin-top:0}.task-edit-hero-content{min-height:195px;align-items:flex-start;flex-direction:column;justify-content:center;padding:26px 20px}.task-edit-content{width:calc(100% - 24px);margin-top:14px}.task-edit-card-header{align-items:flex-start}.task-edit-card-header>small{display:none}.task-edit-context{grid-template-columns:1fr}.task-edit-context>div{border-right:0;border-bottom:1px solid #e5e8e2}.task-edit-context>div:last-child{border-bottom:0}.task-edit-form{grid-template-columns:1fr;padding:17px}.task-edit-full{grid-column:auto}.task-edit-field label{align-items:flex-start;flex-direction:column;gap:2px}.task-edit-field label small{text-align:left}.task-edit-dates{grid-template-columns:1fr}.task-edit-footer{align-items:stretch;flex-direction:column}.task-edit-footer>div{display:grid;grid-template-columns:1fr 1fr}.task-edit-footer a,.task-edit-footer button{width:100%}}
    @media(max-width:640px){.task-custom-menu.is-searchable{right:auto;left:0;min-width:min(340px,calc(100vw - 58px))}.task-select-tools{grid-template-columns:1fr}.task-date-popover{position:fixed;top:50%;right:12px;left:12px;width:auto;max-width:340px;margin:auto;transform:translateY(-50%)}.task-custom-control.is-open>.task-date-popover{animation:taskDateMobileIn .14s ease-out}@keyframes taskDateMobileIn{from{opacity:0;transform:translateY(calc(-50% - 5px))}to{opacity:1;transform:translateY(-50%)}}}
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const monthNames = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const weekdayNames = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];

        const createIcon = (classes) => {
            const icon = document.createElement('i');
            icon.className = classes;
            icon.setAttribute('aria-hidden', 'true');
            return icon;
        };

        const parseDate = (value) => {
            if (!value) return null;
            const parts = value.split('-').map(Number);
            return parts.length === 3 ? new Date(parts[0], parts[1] - 1, parts[2]) : null;
        };

        const toIsoDate = (date) => [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0'),
        ].join('-');

        const formatDate = (value) => {
            const date = parseDate(value);
            return date
                ? new Intl.DateTimeFormat('es-BO', { day: '2-digit', month: 'short', year: 'numeric' }).format(date)
                : 'Selecciona una fecha';
        };

        const normalizeText = (value) => String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('es');

        const closeCustomControls = (except = null) => {
            document.querySelectorAll('.task-custom-control.is-open').forEach((control) => {
                if (control !== except) {
                    control.classList.remove('is-open');
                    control.querySelector('.task-custom-trigger')?.setAttribute('aria-expanded', 'false');
                    control.querySelector('.task-role-dropdown.is-open')?.classList.remove('is-open');
                    control.querySelector('.task-role-filter')?.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.querySelectorAll('select[data-custom-select]').forEach((select) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'task-custom-control task-custom-select';
            if (select.classList.contains('is-invalid')) wrapper.classList.add('has-error');

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
            select.classList.add('task-native-control');

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'task-custom-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.appendChild(createIcon(select.id === 'asignado_id' ? 'fas fa-user-tie' : 'fas fa-flag'));

            const triggerLabel = document.createElement('span');
            trigger.appendChild(triggerLabel);
            const chevron = createIcon('fas fa-chevron-down task-control-chevron');
            trigger.appendChild(chevron);

            const menu = document.createElement('div');
            menu.className = 'task-custom-menu';
            const optionsPanel = document.createElement('div');
            optionsPanel.className = 'task-custom-options';
            optionsPanel.setAttribute('role', 'listbox');
            let searchInput = null;
            let roleFilter = null;
            let roleFilterValue = '';

            if (select.id === 'asignado_id') {
                menu.classList.add('is-searchable');
                const tools = document.createElement('div');
                tools.className = 'task-select-tools';

                const searchBox = document.createElement('div');
                searchBox.className = 'task-select-search';
                searchBox.appendChild(createIcon('fas fa-magnifying-glass'));
                searchInput = document.createElement('input');
                searchInput.type = 'search';
                searchInput.placeholder = 'Buscar por nombre o rol';
                searchInput.setAttribute('aria-label', 'Buscar responsable por nombre o rol');
                searchBox.appendChild(searchInput);

                const roleDropdown = document.createElement('div');
                roleDropdown.className = 'task-role-dropdown';
                roleFilter = document.createElement('button');
                roleFilter.type = 'button';
                roleFilter.className = 'task-role-filter';
                roleFilter.setAttribute('aria-label', 'Filtrar responsables por rol');
                roleFilter.setAttribute('aria-haspopup', 'listbox');
                roleFilter.setAttribute('aria-expanded', 'false');
                const roleFilterLabel = document.createElement('span');
                roleFilterLabel.textContent = 'Todos los roles';
                roleFilter.append(roleFilterLabel, createIcon('fas fa-chevron-down'));

                const roleMenu = document.createElement('div');
                roleMenu.className = 'task-role-menu';
                roleMenu.setAttribute('role', 'listbox');

                const roles = new Set();
                Array.from(select.options).forEach((option) => {
                    String(option.dataset.roles || '').split('|').filter(Boolean).forEach((role) => roles.add(role));
                });
                const roleButtons = ['', ...Array.from(roles).sort((a, b) => a.localeCompare(b, 'es'))].map((role) => {
                    const roleOption = document.createElement('button');
                    roleOption.type = 'button';
                    roleOption.className = 'task-role-option';
                    roleOption.dataset.value = role;
                    roleOption.setAttribute('role', 'option');
                    const roleLabel = document.createElement('span');
                    roleLabel.textContent = role || 'Todos los roles';
                    roleOption.append(roleLabel, createIcon('fas fa-check'));
                    roleMenu.appendChild(roleOption);

                    roleOption.addEventListener('click', (event) => {
                        event.stopPropagation();
                        roleFilterValue = role;
                        roleFilterLabel.textContent = role || 'Todos los roles';
                        roleButtons.forEach((button) => {
                            const isSelected = button.dataset.value === role;
                            button.classList.toggle('is-selected', isSelected);
                            button.setAttribute('aria-selected', String(isSelected));
                        });
                        roleDropdown.classList.remove('is-open');
                        roleFilter.setAttribute('aria-expanded', 'false');
                        filterOptions();
                    });

                    return roleOption;
                });
                roleButtons[0].classList.add('is-selected');
                roleButtons[0].setAttribute('aria-selected', 'true');

                roleFilter.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const opening = !roleDropdown.classList.contains('is-open');
                    roleDropdown.classList.toggle('is-open', opening);
                    roleFilter.setAttribute('aria-expanded', String(opening));
                });

                roleDropdown.append(roleFilter, roleMenu);
                tools.append(searchBox, roleDropdown);
                menu.appendChild(tools);
            }

            const optionButtons = Array.from(select.options).map((option) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'task-custom-option';
                button.dataset.value = option.value;
                button.setAttribute('role', 'option');

                const label = document.createElement('span');
                label.textContent = option.text;
                button.append(label, createIcon('fas fa-check'));
                optionsPanel.appendChild(button);

                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    wrapper.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                });

                return button;
            });

            const emptyState = document.createElement('div');
            emptyState.className = 'task-select-empty';
            emptyState.textContent = 'No encontramos responsables con esos criterios.';
            optionsPanel.appendChild(emptyState);
            menu.appendChild(optionsPanel);

            const filterOptions = () => {
                const query = normalizeText(searchInput?.value);
                const selectedRole = roleFilterValue;
                let visibleResults = 0;

                optionButtons.forEach((button, index) => {
                    const option = select.options[index];
                    const roles = String(option.dataset.roles || '').split('|').filter(Boolean);
                    const matchesSearch = !query || normalizeText(`${option.dataset.name || ''} ${option.text} ${roles.join(' ')}`).includes(query);
                    const matchesRole = !selectedRole || roles.includes(selectedRole);
                    const isPlaceholder = option.value === '';
                    const isVisible = matchesSearch && matchesRole && !(isPlaceholder && (query || selectedRole));
                    button.style.display = isVisible ? '' : 'none';
                    if (isVisible && !isPlaceholder) visibleResults += 1;
                });

                emptyState.classList.toggle('is-visible', visibleResults === 0 && Boolean(searchInput));
            };

            searchInput?.addEventListener('input', filterOptions);

            const syncSelect = () => {
                const selected = select.options[select.selectedIndex];
                triggerLabel.textContent = selected?.text || select.dataset.placeholder || 'Selecciona una opción';
                optionButtons.forEach((button) => {
                    const isSelected = button.dataset.value === select.value;
                    button.classList.toggle('is-selected', isSelected);
                    button.setAttribute('aria-selected', String(isSelected));
                });
                wrapper.classList.toggle('has-error', select.matches(':invalid'));
            };

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const opening = !wrapper.classList.contains('is-open');
                closeCustomControls(wrapper);
                wrapper.classList.toggle('is-open', opening);
                trigger.setAttribute('aria-expanded', String(opening));
                if (!opening) {
                    wrapper.querySelector('.task-role-dropdown.is-open')?.classList.remove('is-open');
                    wrapper.querySelector('.task-role-filter')?.setAttribute('aria-expanded', 'false');
                }
                if (opening && searchInput) requestAnimationFrame(() => searchInput.focus());
            });

            menu.addEventListener('click', (event) => event.stopPropagation());
            select.addEventListener('change', syncSelect);
            wrapper.append(trigger, menu);
            syncSelect();
            filterOptions();
        });

        document.querySelectorAll('input[data-custom-date]').forEach((input) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'task-custom-control task-custom-date';
            if (input.classList.contains('is-invalid')) wrapper.classList.add('has-error');

            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            input.classList.add('task-native-control');

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'task-custom-trigger';
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.appendChild(createIcon('fas fa-calendar-days'));
            const triggerLabel = document.createElement('span');
            trigger.append(triggerLabel, createIcon('fas fa-chevron-down task-control-chevron'));

            const popover = document.createElement('div');
            popover.className = 'task-date-popover';
            popover.setAttribute('role', 'dialog');
            popover.setAttribute('aria-label', 'Seleccionar fecha');

            let viewDate = parseDate(input.value) || parseDate(input.min) || new Date();
            viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);

            const isAllowed = (iso) => (!input.min || iso >= input.min) && (!input.max || iso <= input.max);

            const closePicker = () => {
                wrapper.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            };

            const selectDate = (date) => {
                const iso = toIsoDate(date);
                if (!isAllowed(iso)) return;
                input.value = iso;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closePicker();
                trigger.focus();
            };

            const renderCalendar = () => {
                popover.replaceChildren();

                const header = document.createElement('div');
                header.className = 'task-date-header';
                const previous = document.createElement('button');
                previous.type = 'button';
                previous.className = 'task-date-nav';
                previous.setAttribute('aria-label', 'Mes anterior');
                previous.appendChild(createIcon('fas fa-chevron-left'));
                const title = document.createElement('strong');
                title.textContent = `${monthNames[viewDate.getMonth()]} ${viewDate.getFullYear()}`;
                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'task-date-nav';
                next.setAttribute('aria-label', 'Mes siguiente');
                next.appendChild(createIcon('fas fa-chevron-right'));
                header.append(previous, title, next);

                previous.addEventListener('click', () => {
                    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
                    renderCalendar();
                });
                next.addEventListener('click', () => {
                    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
                    renderCalendar();
                });

                const grid = document.createElement('div');
                grid.className = 'task-date-grid';
                weekdayNames.forEach((name) => {
                    const weekday = document.createElement('span');
                    weekday.className = 'task-date-weekday';
                    weekday.textContent = name;
                    grid.appendChild(weekday);
                });

                const firstDayOffset = (new Date(viewDate.getFullYear(), viewDate.getMonth(), 1).getDay() + 6) % 7;
                const gridStart = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1 - firstDayOffset);
                const todayIso = toIsoDate(new Date());

                for (let index = 0; index < 42; index += 1) {
                    const date = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + index);
                    const iso = toIsoDate(date);
                    const day = document.createElement('button');
                    day.type = 'button';
                    day.className = 'task-date-day';
                    day.textContent = date.getDate();
                    day.disabled = !isAllowed(iso);
                    day.classList.toggle('is-other', date.getMonth() !== viewDate.getMonth());
                    day.classList.toggle('is-today', iso === todayIso);
                    day.classList.toggle('is-selected', iso === input.value);
                    day.setAttribute('aria-label', formatDate(iso));
                    if (!day.disabled) day.addEventListener('click', () => selectDate(date));
                    grid.appendChild(day);
                }

                const footer = document.createElement('div');
                footer.className = 'task-date-footer';
                const todayButton = document.createElement('button');
                todayButton.type = 'button';
                todayButton.textContent = 'Hoy';
                todayButton.disabled = !isAllowed(todayIso);
                todayButton.addEventListener('click', () => selectDate(new Date()));
                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.textContent = 'Cerrar';
                closeButton.addEventListener('click', () => {
                    closePicker();
                    trigger.focus();
                });
                footer.append(todayButton, closeButton);
                popover.append(header, grid, footer);
            };

            const refreshPicker = () => {
                triggerLabel.textContent = formatDate(input.value);
                wrapper.classList.toggle('has-error', input.matches(':invalid'));
                const selected = parseDate(input.value);
                if (selected) viewDate = new Date(selected.getFullYear(), selected.getMonth(), 1);
                if (wrapper.classList.contains('is-open')) renderCalendar();
            };

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const opening = !wrapper.classList.contains('is-open');
                closeCustomControls(wrapper);
                wrapper.classList.toggle('is-open', opening);
                trigger.setAttribute('aria-expanded', String(opening));
                if (opening) renderCalendar();
            });

            popover.addEventListener('click', (event) => event.stopPropagation());
            input.addEventListener('change', refreshPicker);
            input.refreshCustomDate = refreshPicker;
            wrapper.append(trigger, popover);
            refreshPicker();
        });

        const start = document.getElementById('fecha_inicio');
        const deadline = document.getElementById('fecha_limite');
        if (start && deadline) {
            const syncDeadline = () => {
                deadline.min = start.value;
                if (deadline.value && deadline.value < start.value) {
                    deadline.value = start.value;
                    deadline.dispatchEvent(new Event('change', { bubbles: true }));
                }
                deadline.refreshCustomDate?.();
            };
            start.addEventListener('change', syncDeadline);
            syncDeadline();
        }

        document.addEventListener('click', () => closeCustomControls());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeCustomControls();
        });
    });
</script>
@endsection
