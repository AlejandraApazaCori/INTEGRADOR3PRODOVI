@extends('layouts.app')

@section('title', 'Editar campaña')

@section('content')
@php
    $fechaInicio = \Carbon\Carbon::parse($campania->fecha_inicio);
    $fechaFin = \Carbon\Carbon::parse($campania->fecha_fin);
    $cliente = $campania->cliente;
    $creador = $campania->creador;
    $estadoClase = match(old('estado', $campania->estado)) {
        'activa' => 'is-active',
        'pausada' => 'is-paused',
        default => 'is-finished',
    };
@endphp

<div class="campaign-edit-page">
    <div class="campaign-edit-shell">
        <nav class="campaign-edit-actions" aria-label="Navegación de campaña">
            <a href="{{ route('administrador.campañas.show', $campania->id) }}"><i class="fas fa-eye"></i> Ver detalle</a>
            <a href="{{ route('administrador.campañas.calendario', $campania->id) }}"><i class="fas fa-calendar-days"></i> Calendario</a>
            <a href="{{ route('administrador.campañas.index') }}"><i class="fas fa-table-columns"></i> General</a>
        </nav>

        <header class="campaign-edit-hero">
            <div class="campaign-edit-overlay"></div>
            <div class="campaign-edit-hero-content">
                <div>
                    <span class="campaign-edit-eyebrow">Operación de marketing</span>
                    <h1>Editar campaña</h1>
                    <p>{{ $campania->nombre }}</p>
                </div>
                <span id="campaign-edit-status" class="campaign-edit-status {{ $estadoClase }}"><span></span><b>{{ ucfirst(old('estado', $campania->estado)) }}</b></span>
            </div>
        </header>

        <main class="campaign-edit-content">
            @if($errors->any())
                <div class="campaign-edit-error" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>
                        <strong>No se pudieron guardar los cambios.</strong>
                        <span>Revisa los campos señalados e inténtalo nuevamente.</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('administrador.campañas.update', $campania->id) }}" method="POST" class="campaign-edit-card">
                @csrf
                @method('PUT')

                <header class="campaign-edit-card-header">
                    <div>
                        <span>Configuración</span>
                        <h2>Datos de la campaña</h2>
                    </div>
                    <p>Actualiza únicamente la información que necesite cambios.</p>
                </header>

                <div class="campaign-edit-context">
                    <div><small>Cliente</small><strong>{{ $cliente?->name ?? 'Sin cliente asignado' }}</strong></div>
                    <div><small>Fecha de inicio</small><strong>{{ $fechaInicio->format('d/m/Y') }}</strong></div>
                    <div><small>Creada por</small><strong>{{ $creador?->name ?? 'Sin información' }}</strong></div>
                </div>

                <div class="campaign-edit-grid">
                    <section class="campaign-edit-section">
                        <div class="campaign-edit-section-title">
                            <span>Información principal</span>
                            <h3>Contenido</h3>
                        </div>

                        <div class="campaign-edit-field">
                            <label for="nombre"><span>Nombre de la campaña</span><small>Máximo 100 caracteres</small></label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $campania->nombre) }}" maxlength="100" required class="@error('nombre') is-invalid @enderror">
                            @error('nombre')<p class="campaign-field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="campaign-edit-field">
                            <label for="descripcion"><span>Descripción y objetivos</span><small>Explica el enfoque y el propósito</small></label>
                            <textarea name="descripcion" id="descripcion" rows="8" required class="@error('descripcion') is-invalid @enderror">{{ old('descripcion', $campania->descripcion) }}</textarea>
                            @error('descripcion')<p class="campaign-field-error">{{ $message }}</p>@enderror
                        </div>
                    </section>

                    <section class="campaign-edit-section">
                        <div class="campaign-edit-section-title">
                            <span>Responsabilidad y fechas</span>
                            <h3>Asignación y vigencia</h3>
                        </div>

                        <div class="campaign-edit-field">
                            <label for="community_manager_id"><span>Community Manager</span><small>Responsable principal de ejecución</small></label>
                            <div class="campaign-edit-manager-control">
                                <select name="community_manager_id" id="community_manager_id" required class="@error('community_manager_id') is-invalid @enderror">
                                    <option value="">Selecciona un responsable</option>
                                    @foreach($communityManagers as $cm)
                                        <option value="{{ $cm->id }}" {{ (string) old('community_manager_id', $campania->community_manager_id) === (string) $cm->id ? 'selected' : '' }}>{{ $cm->name }}</option>
                                    @endforeach
                                </select>
                                @if($campania->suscripcion_id)
                                    <button type="button"
                                            class="campaign-edit-recommend"
                                            data-url="{{ route('administrador.campañas.recomendar-community-manager', ['suscripcion_id' => $campania->suscripcion_id, 'campania_id' => $campania->id]) }}"
                                            onclick="recomendarResponsableEdicion(this)">
                                        <i class="fas fa-wand-magic-sparkles"></i> Recomendar
                                    </button>
                                @endif
                            </div>
                            <div id="edit-manager-recommendation" class="campaign-edit-recommendation hidden" role="status" aria-live="polite"></div>
                            @error('community_manager_id')<p class="campaign-field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="campaign-edit-field">
                            <label for="estado"><span>Estado</span><small>Situación operativa actual</small></label>
                            <select name="estado" id="estado" required class="@error('estado') is-invalid @enderror">
                                <option value="activa" {{ old('estado', $campania->estado) === 'activa' ? 'selected' : '' }}>Activa</option>
                                <option value="pausada" {{ old('estado', $campania->estado) === 'pausada' ? 'selected' : '' }}>Pausada</option>
                                <option value="finalizada" {{ old('estado', $campania->estado) === 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                            </select>
                            @error('estado')<p class="campaign-field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="campaign-edit-field">
                            <label for="fecha_fin"><span>Fecha de finalización</span><small>Inicio: {{ $fechaInicio->format('d/m/Y') }}</small></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" min="{{ $fechaInicio->format('Y-m-d') }}" value="{{ old('fecha_fin', $fechaFin->format('Y-m-d')) }}" required class="@error('fecha_fin') is-invalid @enderror">
                            @error('fecha_fin')<p class="campaign-field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="campaign-edit-note">
                            Cambiar el responsable o el estado tendrá efecto inmediato después de guardar.
                        </div>
                    </section>
                </div>

                <footer class="campaign-edit-footer">
                    <span>Los campos son obligatorios.</span>
                    <div>
                        <a href="{{ route('administrador.campañas.show', $campania->id) }}" class="campaign-edit-cancel"><i class="fas fa-xmark"></i> Cancelar</a>
                        <button type="submit" class="campaign-edit-submit"><i class="fas fa-check"></i> Guardar cambios</button>
                    </div>
                </footer>
            </form>
        </main>
    </div>
</div>

<style>
    .campaign-edit-page{min-height:100vh;padding-bottom:48px;background:#fff;color:#302834}
    .campaign-edit-shell{position:relative;width:100%}
    .campaign-edit-hero{position:relative;min-height:180px;overflow:hidden;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%;background-color:#1d4ed8}
    .campaign-edit-overlay{position:absolute;inset:0;background:linear-gradient(rgba(15,23,42,.28),rgba(15,23,42,.28)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%)}
    .campaign-edit-hero-content{position:relative;z-index:2;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:30px 490px 30px max(48px,calc((100% - 1120px)/2))}
    .campaign-edit-eyebrow{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.campaign-edit-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.campaign-edit-hero p{margin:5px 0 0;color:#dbeafe;font-size:.74rem;font-weight:600}
    .campaign-edit-status{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.45);border-radius:999px;background:rgba(255,255,255,.16);color:#fff;font-size:.66rem;font-weight:900;text-transform:uppercase}.campaign-edit-status>span{width:6px;height:6px;border-radius:50%;background:#cbd5e1}.campaign-edit-status.is-active>span{background:#bef264}.campaign-edit-status.is-paused>span{background:#fde047}
    .campaign-edit-actions{position:absolute;z-index:20;top:67px;right:48px;display:flex;gap:9px}.campaign-edit-actions a{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 13px;border:1px solid rgba(255,255,255,.24);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.69rem;font-weight:900;text-decoration:none;backdrop-filter:blur(4px);transition:.18s}.campaign-edit-actions a:hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
    .campaign-edit-content{width:min(1120px,calc(100% - 48px));margin:24px auto 0}.campaign-edit-error{margin-bottom:14px;padding:13px 15px;display:flex;align-items:center;gap:10px;border:1px solid #f3c4c4;border-radius:11px;background:#fff0f0;color:#a72d2d}.campaign-edit-error strong,.campaign-edit-error span{display:block}.campaign-edit-error strong{font-size:.7rem}.campaign-edit-error span{margin-top:2px;font-size:.61rem}
    .campaign-edit-card{overflow:hidden;border:1px solid #e1e3de;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(55,60,52,.055)}
    .campaign-edit-card-header{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;padding:20px 22px 16px;border-bottom:1px solid #e8ebe5}.campaign-edit-card-header span,.campaign-edit-section-title>span{display:block;margin-bottom:3px;color:#117e8c;font-size:.58rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.campaign-edit-card-header h2{margin:0;color:#2d322c;font-size:1.05rem;font-weight:900}.campaign-edit-card-header h2:after,.campaign-edit-section-title h3:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#117e8c}.campaign-edit-card-header p{margin:0;color:#7e867b;font-size:.64rem;font-weight:600}
    .campaign-edit-context{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));border-bottom:1px solid #e8ebe5;background:#fafbf9}.campaign-edit-context>div{padding:13px 18px;border-right:1px solid #e8ebe5}.campaign-edit-context>div:last-child{border-right:0}.campaign-edit-context small,.campaign-edit-context strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.campaign-edit-context small{color:#899087;font-size:.55rem;font-weight:850;text-transform:uppercase}.campaign-edit-context strong{margin-top:4px;color:#343a32;font-size:.68rem;font-weight:900}
    .campaign-edit-grid{display:grid;grid-template-columns:1.12fr .88fr}.campaign-edit-section{min-width:0;padding:22px}.campaign-edit-section+.campaign-edit-section{border-left:1px solid #e8ebe5}.campaign-edit-section-title{margin-bottom:20px}.campaign-edit-section-title h3{margin:0;color:#302832;font-size:.95rem;font-weight:900}
    .campaign-edit-field+.campaign-edit-field{margin-top:17px}.campaign-edit-field label{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin:0 2px 7px}.campaign-edit-field label span{color:#3d453b;font-size:.66rem;font-weight:900}.campaign-edit-field label small{color:#92998f;font-size:.56rem;font-weight:600;text-align:right}
    .campaign-edit-field input,.campaign-edit-field select,.campaign-edit-field textarea{width:100%;border:1px solid #d7dcd4;border-radius:10px;background:#fff;color:#30372e;font-family:inherit;font-size:.7rem;font-weight:650;outline:0;box-shadow:0 2px 5px rgba(55,60,52,.04);transition:.18s}.campaign-edit-field input,.campaign-edit-field select{height:46px;padding:0 13px}.campaign-edit-field textarea{min-height:178px;padding:13px;line-height:1.55;resize:vertical}.campaign-edit-field input:focus,.campaign-edit-field select:focus,.campaign-edit-field textarea:focus{border-color:#117e8c;box-shadow:0 0 0 3px rgba(17,126,140,.11)}.campaign-edit-field .is-invalid{border-color:#dc5b5b;box-shadow:0 0 0 3px rgba(220,91,91,.1)}.campaign-field-error{margin:6px 2px 0;color:#b53c3c;font-size:.58rem;font-weight:750}
    .campaign-edit-manager-control{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px}.campaign-edit-recommend{height:46px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border:1px solid #0f7480;border-radius:10px;background:#117e8c;color:#fff;font-size:.62rem;font-weight:900;cursor:pointer;transition:.18s}.campaign-edit-recommend:hover{transform:translateY(-1px);background:#0d6973}.campaign-edit-recommend:disabled{opacity:.7;cursor:wait;transform:none}
    .campaign-edit-recommendation{margin-top:8px;padding:9px 11px;border:1px solid #b9dfe2;border-radius:9px;background:#f0fafb;color:#315e63}.campaign-edit-recommendation strong,.campaign-edit-recommendation span{display:block}.campaign-edit-recommendation strong{color:#0f6872;font-size:.63rem;font-weight:900}.campaign-edit-recommendation span{margin-top:3px;font-size:.56rem;line-height:1.45}.campaign-edit-recommendation.is-error{border-color:#f3c4c4;background:#fff0f0;color:#a72d2d}
    .campaign-edit-note{margin-top:18px;padding:11px 12px;border-left:3px solid #117e8c;background:#f5f9f8;color:#69736a;font-size:.59rem;font-weight:650;line-height:1.5}
    .campaign-edit-footer{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:16px 22px;border-top:1px solid #e8ebe5;background:#fafbf9}.campaign-edit-footer>span{color:#8a9187;font-size:.58rem;font-weight:650}.campaign-edit-footer>div{display:flex;gap:8px}.campaign-edit-cancel,.campaign-edit-submit{min-height:39px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 15px;border-radius:9px;font-size:.65rem;font-weight:900;text-decoration:none;cursor:pointer;transition:.18s}.campaign-edit-cancel{border:1px solid #d7dcd4;background:#fff;color:#626a60}.campaign-edit-submit{border:1px solid #4f46e5;background:#4f46e5;color:#fff;box-shadow:0 5px 12px rgba(79,70,229,.18)}.campaign-edit-cancel:hover{background:#f2f4f0}.campaign-edit-submit:hover{transform:translateY(-1px);background:#4338ca}
    @media(max-width:900px){.campaign-edit-actions{position:static;justify-content:center;padding:14px 24px 0}.campaign-edit-actions a{border-color:#dce4f3;background:#f4f7fd;color:#4f46e5}.campaign-edit-hero{margin-top:14px}.campaign-edit-hero-content{padding:28px 24px}.campaign-edit-grid{grid-template-columns:1fr}.campaign-edit-section+.campaign-edit-section{border-top:1px solid #e8ebe5;border-left:0}}
    @media(max-width:640px){.campaign-edit-page{padding-bottom:24px}.campaign-edit-actions{display:grid;grid-template-columns:1fr;padding:12px}.campaign-edit-actions a{width:100%}.campaign-edit-hero{min-height:190px;margin-top:0}.campaign-edit-hero-content{min-height:190px;align-items:flex-start;flex-direction:column;justify-content:center;padding:26px 20px}.campaign-edit-content{width:calc(100% - 24px);margin-top:14px}.campaign-edit-card-header{align-items:flex-start;flex-direction:column}.campaign-edit-card-header p{display:none}.campaign-edit-context{grid-template-columns:1fr}.campaign-edit-context>div{border-right:0;border-bottom:1px solid #e8ebe5}.campaign-edit-context>div:last-child{border-bottom:0}.campaign-edit-section{padding:18px}.campaign-edit-field label{align-items:flex-start;flex-direction:column;gap:2px}.campaign-edit-field label small{text-align:left}.campaign-edit-manager-control{grid-template-columns:1fr}.campaign-edit-footer{align-items:stretch;flex-direction:column}.campaign-edit-footer>div{display:grid;grid-template-columns:1fr 1fr}.campaign-edit-cancel,.campaign-edit-submit{width:100%}}
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stateSelect = document.getElementById('estado');
        const stateBadge = document.getElementById('campaign-edit-status');
        stateSelect?.addEventListener('change', () => {
            stateBadge.classList.remove('is-active', 'is-paused', 'is-finished');
            stateBadge.classList.add(stateSelect.value === 'activa' ? 'is-active' : stateSelect.value === 'pausada' ? 'is-paused' : 'is-finished');
            const label = stateBadge.querySelector('b');
            if (label) label.textContent = stateSelect.options[stateSelect.selectedIndex].text;
        });
    });

    async function recomendarResponsableEdicion(button) {
        const select = document.getElementById('community_manager_id');
        const result = document.getElementById('edit-manager-recommendation');
        if (!select || !result || !button?.dataset.url) return;

        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analizando';
        result.classList.add('hidden');
        result.classList.remove('is-error');

        try {
            const response = await fetch(button.dataset.url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'No fue posible calcular una recomendación.');

            select.value = String(data.recommended.id);
            select.dispatchEvent(new Event('change', { bubbles: true }));

            const title = document.createElement('strong');
            title.textContent = `Recomendado: ${data.recommended.name}`;
            const explanation = document.createElement('span');
            explanation.textContent = `${data.recommended.reason} Se evaluaron ${data.evaluated} responsables.`;
            result.replaceChildren(title, explanation);
            result.classList.remove('hidden');
            select.focus();
        } catch (error) {
            result.textContent = error.message || 'No fue posible calcular una recomendación.';
            result.classList.add('is-error');
            result.classList.remove('hidden');
        } finally {
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    }
</script>
@endsection
