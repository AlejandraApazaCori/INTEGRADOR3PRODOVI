@php
    $clienteReunion = $campania->cliente;
    $equipoReunion = collect([
        $campania->creador,
        $campania->communityManager,
    ])->concat($disenadoresCampania)->filter()->unique('id')->values();
    $reuniones = $campania->reuniones->sortBy('fecha_inicio');
@endphp

<section class="meetings-workspace">
    <header class="meetings-header">
        <div>
           
            <h2>Reuniones agendadas</h2>
        </div>
        <button type="button" class="meetings-new" data-open-meeting-drawer><i class="fas fa-plus"></i> Nueva reunión</button>
    </header>

    <div class="meetings-layout">
        <div class="meetings-list">
            @forelse($reuniones as $reunion)
                <article class="meeting-card">
                    <div class="meeting-date">
                        <strong>{{ $reunion->fecha_inicio->format('d') }}</strong>
                        <span>{{ strtoupper($reunion->fecha_inicio->locale('es')->translatedFormat('M')) }}</span>
                    </div>
                    <div class="meeting-main">
                        <div class="meeting-title-row">
                            <div>
                                <small>{{ ucfirst($reunion->plataforma) }} · {{ $reunion->fecha_inicio->format('H:i') }}–{{ $reunion->fecha_fin->format('H:i') }}</small>
                                <h3>{{ $reunion->titulo }}</h3>
                            </div>
                            <span class="meeting-status"><i></i> Agendada</span>
                        </div>
                        @if($reunion->descripcion)<p>{{ $reunion->descripcion }}</p>@endif
                        <div class="meeting-footer">
                            <div class="meeting-people">
                                @foreach($reunion->participantes->take(5) as $participante)
                                    <span title="{{ $participante->name }}">{{ strtoupper(substr($participante->name, 0, 2)) }}</span>
                                @endforeach
                                <small>{{ $reunion->participantes->count() }} participantes</small>
                            </div>
                            <a href="{{ $reunion->enlace }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-video"></i> Abrir enlace</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="meetings-empty">
                    <i class="fas fa-video"></i>
                    <h3>Aún no hay reuniones</h3>
                    <p>Agenda la primera reunión con el equipo y el cliente.</p>
                    <button type="button" data-open-meeting-drawer>Agendar reunión</button>
                </div>
            @endforelse
        </div>

        <aside class="meeting-client-access">
            <span class="meeting-access-icon"><i class="fas fa-user-clock"></i></span>
            <small>Próxima etapa · acceso del cliente</small>
            <h3>Intentos para coordinar</h3>
            <p>Define cuántas veces por mes podrá el cliente solicitar una reunión con el equipo.</p>
            <form action="{{ route('administrador.campañas.reuniones.acceso-cliente', $campania) }}" method="POST">
                @csrf @method('PATCH')
                <label for="reuniones_cliente_por_mes">Intentos mensuales</label>
                <div class="meeting-quota-control">
                    <input id="reuniones_cliente_por_mes" name="reuniones_cliente_por_mes" type="number" min="0" max="50" value="{{ old('reuniones_cliente_por_mes', $campania->reuniones_cliente_por_mes ?? 0) }}" required>
                    <span>por mes</span>
                </div>
                <small>Usa 0 para mantener deshabilitada la agenda del cliente.</small>
                <button type="submit">Guardar acceso</button>
            </form>
        </aside>
    </div>
</section>

<div class="meeting-drawer-backdrop" data-meeting-backdrop hidden></div>
<aside class="meeting-drawer" data-meeting-drawer aria-hidden="true" aria-labelledby="meeting-drawer-title">
    <header>
        <div><small>Nueva coordinación</small><h2 id="meeting-drawer-title">Agendar reunión</h2></div>
        <button type="button" data-close-meeting-drawer aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
    </header>
    <form action="{{ route('administrador.campañas.reuniones.store', $campania) }}" method="POST">
        @csrf
        <input type="hidden" name="_meeting_form" value="1">
        <div class="meeting-drawer-body">
            @if($errors->any() && old('_meeting_form'))
                <div class="meeting-form-errors"><strong>Revisa los datos:</strong>{{ $errors->first() }}</div>
            @endif
            <label>Título<input name="titulo" type="text" maxlength="150" value="{{ old('titulo') }}" placeholder="Ej. Revisión de avances" required></label>
            <label>Descripción<textarea name="descripcion" rows="3" maxlength="2000" placeholder="Temas a revisar y acuerdos esperados">{{ old('descripcion') }}</textarea></label>
            <div class="meeting-form-grid">
                <label>Plataforma
                    <div class="meeting-custom-select" data-meeting-select>
                        <input type="hidden" name="plataforma" value="{{ old('plataforma', 'meet') }}">
                        <button type="button" data-select-trigger><span>{{ ['meet' => 'Google Meet', 'zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'otro' => 'Otro'][old('plataforma', 'meet')] ?? 'Google Meet' }}</span><i class="fas fa-chevron-down"></i></button>
                        <div class="meeting-select-menu" data-select-menu hidden>
                            @foreach(['meet' => 'Google Meet', 'zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'otro' => 'Otro'] as $value => $label)
                                <button type="button" data-select-option="{{ $value }}" class="{{ old('plataforma', 'meet') === $value ? 'is-selected' : '' }}"><span>{{ $label }}</span><i class="fas fa-check"></i></button>
                            @endforeach
                        </div>
                    </div>
                </label>
                <label>Enlace<input name="enlace" type="url" maxlength="2048" value="{{ old('enlace') }}" placeholder="https://meet.google.com/..." required></label>
            </div>
            <label>Fecha y hora de inicio
                <div class="meeting-date-picker" data-meeting-date-picker>
                    <input type="hidden" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                    <button type="button" class="meeting-date-trigger" data-date-trigger><i class="fas fa-calendar-days"></i><span>Selecciona fecha y hora</span><i class="fas fa-chevron-down"></i></button>
                    <div class="meeting-calendar-popover" data-calendar-popover hidden>
                        <header><button type="button" data-calendar-prev aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></button><strong data-calendar-title></strong><button type="button" data-calendar-next aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></button></header>
                        <div class="meeting-calendar-week"><span>DO</span><span>LU</span><span>MA</span><span>MI</span><span>JU</span><span>VI</span><span>SÁ</span></div>
                        <div class="meeting-calendar-days" data-calendar-days></div>
                        <footer>
                            <div class="meeting-time-picker" data-calendar-time>
                                <span>Hora</span>
                                <div class="meeting-time-controls">
                                    <div class="meeting-time-dropdown" data-time-dropdown="hour">
                                        <button type="button" data-time-trigger><span>09</span><i class="fas fa-chevron-down"></i></button>
                                        <div data-time-menu hidden>
                                            @for($hour = 0; $hour < 24; $hour++)
                                                <button type="button" data-time-value="{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</button>
                                            @endfor
                                        </div>
                                    </div>
                                    <b>:</b>
                                    <div class="meeting-time-dropdown" data-time-dropdown="minute">
                                        <button type="button" data-time-trigger><span>00</span><i class="fas fa-chevron-down"></i></button>
                                        <div data-time-menu hidden>
                                            @foreach(['00', '15', '30', '45'] as $minute)
                                                <button type="button" data-time-value="{{ $minute }}">{{ $minute }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" data-calendar-apply>Aplicar</button>
                        </footer>
                    </div>
                </div>
                <small class="meeting-duration-note"><i class="fas fa-clock"></i> Duración automática: 1 hora</small>
            </label>
            <fieldset>
                <legend>Participantes <small>Selecciona al menos 2</small></legend>
                <div class="meeting-participants">
                    @if($clienteReunion)
                        <div class="meeting-participant-heading"><i class="fas fa-user"></i> Cliente</div>
                        <label>
                            <input type="checkbox" name="participantes_ids[]" value="{{ $clienteReunion->id }}" @checked(in_array($clienteReunion->id, old('participantes_ids', [])))>
                            <span class="meeting-person-avatar is-client">{{ strtoupper(substr($clienteReunion->name, 0, 2)) }}</span>
                            <span><strong>{{ $clienteReunion->name }}</strong><small>Cliente</small></span>
                            <i class="fas fa-check"></i>
                        </label>
                    @endif
                    <div class="meeting-participant-heading is-team"><i class="fas fa-users"></i> Equipo de campaña</div>
                    @foreach($equipoReunion as $persona)
                        @php
                            $rolReunion = $persona->id === $campania->community_manager_id ? 'Community Manager' : ($disenadoresCampania->contains('id', $persona->id) ? 'Diseño' : 'Administrador');
                        @endphp
                        <label>
                            <input type="checkbox" name="participantes_ids[]" value="{{ $persona->id }}" @checked(in_array($persona->id, old('participantes_ids', [])))>
                            <span class="meeting-person-avatar">{{ strtoupper(substr($persona->name, 0, 2)) }}</span>
                            <span><strong>{{ $persona->name }}</strong><small>{{ $rolReunion }}</small></span>
                            <i class="fas fa-check"></i>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>
        <footer><button type="button" data-close-meeting-drawer>Cancelar</button><button type="submit"><i class="fas fa-calendar-check"></i> Agendar reunión</button></footer>
    </form>
</aside>

<style>
    .meetings-workspace{border:1px solid #e6e1e9;border-radius:14px;background:#fff;box-shadow:0 7px 22px rgba(48,40,52,.05)}.meetings-header{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:22px 24px;border-bottom:1px solid #eeeaf0}.meetings-header>div>span{color:#7da533;font-size:.61rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.meetings-header h2{margin:4px 0;color:#302834;font-size:1.08rem;font-weight:900}.meetings-header h2:after{content:'';display:block;width:44px;height:3px;margin-top:7px;border-radius:999px;background:#5b2b76}.meetings-header p{margin:0;color:#7a737d;font-size:.67rem}.meetings-new,.meetings-empty button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 15px;border:0;border-radius:9px;background:#5b2b76;color:#fff;font-size:.65rem;font-weight:900;cursor:pointer}.meetings-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:0}.meetings-list{min-width:0;padding:20px 24px}.meeting-card{display:grid;grid-template-columns:64px minmax(0,1fr);gap:17px;padding:16px 0;border-bottom:1px solid #eeeaf0}.meeting-card:first-child{padding-top:0}.meeting-card:last-child{padding-bottom:0;border-bottom:0}.meeting-date{height:64px;display:grid;place-content:center;border-radius:10px;background:#f4eef7;color:#5b2b76;text-align:center}.meeting-date strong{font-size:1.25rem;line-height:1}.meeting-date span{margin-top:4px;font-size:.54rem;font-weight:900}.meeting-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:15px}.meeting-main small{color:#8a828d;font-size:.57rem;font-weight:750}.meeting-main h3{margin:4px 0 0;color:#302834;font-size:.82rem;font-weight:900}.meeting-main>p{margin:8px 0;color:#706974;font-size:.63rem;line-height:1.55}.meeting-status{display:flex;align-items:center;gap:6px;padding:5px 8px;border-radius:999px;background:#eff6e6;color:#65872a;font-size:.53rem;font-weight:900}.meeting-status i{width:6px;height:6px;border-radius:50%;background:#7da533}.meeting-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:11px}.meeting-people{display:flex;align-items:center}.meeting-people>span{width:27px;height:27px;display:grid;place-items:center;margin-left:-6px;border:2px solid #fff;border-radius:50%;background:#ece4f1;color:#5b2b76;font-size:.48rem;font-weight:900}.meeting-people>span:first-child{margin-left:0}.meeting-people small{margin-left:7px}.meeting-footer>a{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:36px;padding:9px 14px;border:1px solid #5b2b76;border-radius:9px;background:#5b2b76;color:#fff;font-size:.61rem;font-weight:900;text-decoration:none;box-shadow:0 6px 14px rgba(91,43,118,.22);transition:transform .18s ease,background .18s ease,box-shadow .18s ease}.meeting-footer>a i{font-size:.66rem}.meeting-footer>a:hover{transform:translateY(-2px);background:#47215d;box-shadow:0 9px 19px rgba(91,43,118,.3)}.meetings-empty{display:grid;justify-items:center;padding:55px 20px;text-align:center}.meetings-empty>i{width:52px;height:52px;display:grid;place-items:center;border-radius:14px;background:#f4eef7;color:#5b2b76;font-size:1.15rem}.meetings-empty h3{margin:13px 0 5px;font-size:.86rem}.meetings-empty p{margin:0 0 16px;color:#7a737d;font-size:.63rem}.meeting-client-access{padding:24px;border-left:1px solid #eeeaf0;background:#faf8fb}.meeting-access-icon{width:38px;height:38px;display:grid;place-items:center;margin-bottom:15px;border-radius:10px;background:#eaf1dc;color:#688c29}.meeting-client-access>small{color:#7da533;font-size:.54rem;font-weight:900;text-transform:uppercase}.meeting-client-access h3{margin:5px 0 8px;color:#302834;font-size:.85rem}.meeting-client-access p{margin:0 0 18px;color:#756e78;font-size:.62rem;line-height:1.55}.meeting-client-access form>label{display:block;margin-bottom:6px;color:#554d58;font-size:.58rem;font-weight:900}.meeting-quota-control{display:flex;align-items:center;border:1px solid #ddd6e1;border-radius:9px;background:#fff}.meeting-quota-control input{width:75px;padding:10px;border:0;border-right:1px solid #eee8f0;background:transparent;color:#302834;font-weight:900;outline:0}.meeting-quota-control span{padding:0 10px;color:#817984;font-size:.58rem;font-weight:800}.meeting-client-access form>small{display:block;margin:7px 0 12px;color:#918a93;font-size:.52rem;line-height:1.4}.meeting-client-access form>button{width:100%;padding:9px;border:1px solid #d8cee0;border-radius:8px;background:#fff;color:#5b2b76;font-size:.59rem;font-weight:900;cursor:pointer}
    .meeting-drawer-backdrop{position:fixed;z-index:1040;inset:0;background:rgba(31,23,34,.38);backdrop-filter:blur(2px)}.meeting-drawer{position:fixed;z-index:1050;top:0;right:0;width:min(500px,100%);height:100vh;display:flex;flex-direction:column;transform:translateX(102%);background:#fff;box-shadow:-14px 0 45px rgba(36,25,40,.18);transition:transform .24s ease}.meeting-drawer.is-open{transform:translateX(0)}.meeting-drawer>header{display:flex;align-items:center;justify-content:space-between;padding:20px 22px;border-bottom:1px solid #eee9f0}.meeting-drawer>header small{color:#7da533;font-size:.55rem;font-weight:900;text-transform:uppercase}.meeting-drawer>header h2{margin:3px 0 0;color:#302834;font-size:1.05rem}.meeting-drawer>header button{width:34px;height:34px;border:1px solid #e1dae4;border-radius:9px;background:#fff;color:#6f6672;cursor:pointer}.meeting-drawer form{min-height:0;display:flex;flex:1;flex-direction:column}.meeting-drawer-body{overflow-y:auto;padding:21px 22px}.meeting-drawer-body>label,.meeting-form-grid>label{display:grid;gap:6px;margin-bottom:15px;color:#554d58;font-size:.59rem;font-weight:900}.meeting-drawer input,.meeting-drawer textarea{width:100%;padding:10px 11px;border:1px solid #ddd6e1;border-radius:8px;background:#fff;color:#403744;font:inherit;font-size:.65rem;outline:0}.meeting-drawer textarea{resize:vertical}.meeting-drawer input:focus,.meeting-drawer textarea:focus{border-color:#7c5591;box-shadow:0 0 0 3px #f2ebf6}.meeting-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.meeting-drawer fieldset{margin:3px 0 0;padding:0;border:0}.meeting-drawer legend{width:100%;display:flex;justify-content:space-between;margin-bottom:9px;color:#554d58;font-size:.61rem;font-weight:900}.meeting-drawer legend small{color:#918a93;font-size:.52rem}.meeting-participants{display:grid;gap:7px}.meeting-participant-heading{display:flex;align-items:center;gap:7px;margin:2px 0 1px;color:#7d7580;font-size:.54rem;font-weight:900;letter-spacing:.07em;text-transform:uppercase}.meeting-participant-heading.is-team{margin-top:11px;padding-top:11px;border-top:1px solid #eee9f0}.meeting-participants>label{display:grid;grid-template-columns:auto 34px minmax(0,1fr) auto;align-items:center;gap:9px;padding:9px;border:1px solid #e5dfe8;border-radius:9px;cursor:pointer}.meeting-participants input{width:14px;height:14px}.meeting-person-avatar{width:32px;height:32px;display:grid;place-items:center;border-radius:8px;background:#f0e9f4;color:#5b2b76;font-size:.5rem;font-weight:900}.meeting-person-avatar.is-client{background:#e9f3d8;color:#618228}.meeting-participants label>span:nth-of-type(2){display:grid}.meeting-participants strong{font-size:.61rem}.meeting-participants small{margin-top:2px;color:#8a828d;font-size:.5rem}.meeting-participants label>i{color:#7da533;opacity:0}.meeting-participants label:has(input:checked){border-color:#b7c98e;background:#f8faf3}.meeting-participants label:has(input:checked)>i{opacity:1}.meeting-form-errors{margin-bottom:14px;padding:10px;border-radius:8px;background:#fff0ed;color:#a53e2d;font-size:.59rem}.meeting-form-errors strong{display:block;margin-bottom:3px}.meeting-drawer footer{display:flex;justify-content:flex-end;gap:8px;padding:15px 22px;border-top:1px solid #eee9f0;background:#faf9fb}.meeting-drawer footer button{padding:10px 14px;border:1px solid #ddd6e1;border-radius:8px;background:#fff;color:#655d68;font-size:.61rem;font-weight:900;cursor:pointer}.meeting-drawer footer button[type=submit]{border-color:#5b2b76;background:#5b2b76;color:#fff}
    .meeting-custom-select{position:relative}.meeting-custom-select>button,.meeting-date-trigger{width:100%;min-height:39px;display:flex;align-items:center;justify-content:space-between;gap:9px;padding:9px 11px;border:1px solid #ddd6e1;border-radius:8px;background:#fff;color:#403744;font-size:.63rem;font-weight:750;text-align:left;cursor:pointer}.meeting-custom-select>button:focus,.meeting-date-trigger:focus{border-color:#7c5591;box-shadow:0 0 0 3px #f2ebf6}.meeting-custom-select>button i,.meeting-date-trigger>i:last-child{color:#8f8493;font-size:.52rem;transition:.18s}.meeting-custom-select.is-open>button i,.meeting-date-picker.is-open .meeting-date-trigger>i:last-child{transform:rotate(180deg)}.meeting-select-menu{position:absolute;z-index:25;top:calc(100% + 6px);right:0;left:0;padding:5px;border:1px solid #e0d8e4;border-radius:10px;background:#fff;box-shadow:0 12px 28px rgba(48,40,52,.15)}.meeting-select-menu button{width:100%;display:flex;align-items:center;justify-content:space-between;padding:9px;border:0;border-radius:7px;background:#fff;color:#514954;font-size:.61rem;font-weight:750;text-align:left;cursor:pointer}.meeting-select-menu button:hover,.meeting-select-menu button.is-selected{background:#f4eef7;color:#5b2b76}.meeting-select-menu button i{opacity:0;color:#7da533}.meeting-select-menu button.is-selected i{opacity:1}
    .meeting-date-picker{position:relative}.meeting-date-trigger{justify-content:flex-start}.meeting-date-trigger>i:first-child{color:#5b2b76}.meeting-date-trigger span{flex:1}.meeting-duration-note{display:flex!important;align-items:center;gap:5px;margin-top:1px!important;color:#8b838e!important;font-size:.51rem!important;font-weight:700!important}.meeting-calendar-popover{position:absolute;z-index:24;top:calc(100% + 6px);right:0;width:310px;padding:12px;border:1px solid #dfd7e3;border-radius:12px;background:#fff;box-shadow:0 16px 35px rgba(48,40,52,.17)}.meeting-calendar-popover>header{display:grid;grid-template-columns:30px 1fr 30px;align-items:center;margin-bottom:10px}.meeting-calendar-popover>header strong{text-align:center;color:#403744;font-size:.68rem;text-transform:capitalize}.meeting-calendar-popover>header button{width:29px;height:29px;border:1px solid #e7e0e9;border-radius:7px;background:#fff;color:#655b69;cursor:pointer}.meeting-calendar-week,.meeting-calendar-days{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}.meeting-calendar-week span{padding:5px 0;color:#9a929d;font-size:.47rem;font-weight:900;text-align:center}.meeting-calendar-days button{aspect-ratio:1;border:0;border-radius:7px;background:#fff;color:#514954;font-size:.56rem;font-weight:800;cursor:pointer}.meeting-calendar-days button:hover{background:#f2ebf6;color:#5b2b76}.meeting-calendar-days button.is-outside{color:#c4bec6}.meeting-calendar-days button.is-today{box-shadow:inset 0 0 0 1px #7da533;color:#668928}.meeting-calendar-days button.is-selected{background:#5b2b76;color:#fff;box-shadow:none}.meeting-calendar-popover>footer{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid #eee9f0}.meeting-calendar-popover>footer label{display:grid;gap:4px;color:#716974;font-size:.51rem;font-weight:900}.meeting-calendar-popover>footer input{width:105px;padding:7px 8px}.meeting-calendar-popover>footer button{padding:8px 12px;border:0;border-radius:7px;background:#5b2b76;color:#fff;font-size:.55rem;font-weight:900;cursor:pointer}
    .meeting-time-picker{display:grid;gap:4px}.meeting-time-picker>span{color:#716974;font-size:.51rem;font-weight:900}.meeting-time-controls{display:flex;align-items:center;gap:5px}.meeting-time-controls>b{color:#625968;font-size:.72rem}.meeting-time-dropdown{position:relative}.meeting-time-dropdown>[data-time-trigger]{width:58px;display:flex;align-items:center;justify-content:space-between;padding:8px;border:1px solid #ddd6e1;border-radius:7px;background:#fff;color:#403744;font-size:.58rem;font-weight:900}.meeting-time-dropdown>[data-time-trigger] i{color:#958b99;font-size:.44rem}.meeting-time-dropdown.is-open>[data-time-trigger]{border-color:#7c5591;box-shadow:0 0 0 3px #f2ebf6}.meeting-time-dropdown.is-open>[data-time-trigger] i{transform:rotate(180deg)}.meeting-time-dropdown>[data-time-menu]{position:absolute;z-index:35;bottom:calc(100% + 5px);left:0;width:58px;max-height:172px;overflow-y:auto;padding:4px;border:1px solid #ded6e2;border-radius:8px;background:#fff;box-shadow:0 11px 25px rgba(48,40,52,.17);scrollbar-width:thin;scrollbar-color:#c4b7ca transparent}.meeting-time-dropdown>[data-time-menu] button{width:100%;display:block;padding:7px 4px;border:0;border-radius:5px;background:#fff;color:#504653;font-size:.55rem;font-weight:800;text-align:center;cursor:pointer}.meeting-time-dropdown>[data-time-menu] button:hover,.meeting-time-dropdown>[data-time-menu] button.is-selected{background:#f1eaf5;color:#5b2b76}
    @media(max-width:850px){.meetings-layout{grid-template-columns:1fr}.meeting-client-access{border-top:1px solid #eeeaf0;border-left:0}.meeting-form-grid{grid-template-columns:1fr}}@media(max-width:560px){.meetings-header{align-items:stretch;flex-direction:column}.meeting-card{grid-template-columns:50px minmax(0,1fr)}.meeting-date{height:50px}.meeting-title-row,.meeting-footer{align-items:flex-start;flex-direction:column}.meetings-list{padding:18px}.meeting-drawer-body{padding:18px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const drawer = document.querySelector('[data-meeting-drawer]');
    const backdrop = document.querySelector('[data-meeting-backdrop]');
    if (!drawer || !backdrop) return;
    const open = () => { backdrop.hidden = false; requestAnimationFrame(() => drawer.classList.add('is-open')); drawer.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; };
    const close = () => { drawer.classList.remove('is-open'); drawer.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; setTimeout(() => { backdrop.hidden = true; }, 240); };
    document.querySelectorAll('[data-open-meeting-drawer]').forEach(button => button.addEventListener('click', open));
    document.querySelectorAll('[data-close-meeting-drawer]').forEach(button => button.addEventListener('click', close));
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });

    document.querySelectorAll('[data-meeting-select]').forEach(select => {
        const input = select.querySelector('input[type="hidden"]');
        const trigger = select.querySelector('[data-select-trigger]');
        const menu = select.querySelector('[data-select-menu]');
        trigger.addEventListener('click', event => {
            event.stopPropagation();
            const opening = menu.hidden;
            document.querySelectorAll('[data-select-menu]').forEach(item => { item.hidden = true; item.closest('[data-meeting-select]')?.classList.remove('is-open'); });
            menu.hidden = !opening;
            select.classList.toggle('is-open', opening);
        });
        menu.querySelectorAll('[data-select-option]').forEach(option => option.addEventListener('click', () => {
            input.value = option.dataset.selectOption;
            trigger.querySelector('span').textContent = option.querySelector('span').textContent;
            menu.querySelectorAll('[data-select-option]').forEach(item => item.classList.toggle('is-selected', item === option));
            menu.hidden = true;
            select.classList.remove('is-open');
        }));
    });

    const picker = document.querySelector('[data-meeting-date-picker]');
    if (picker) {
        const hiddenInput = picker.querySelector('input[name="fecha_inicio"]');
        const trigger = picker.querySelector('[data-date-trigger]');
        const triggerText = trigger.querySelector('span');
        const popover = picker.querySelector('[data-calendar-popover]');
        const title = picker.querySelector('[data-calendar-title]');
        const days = picker.querySelector('[data-calendar-days]');
        const timePicker = picker.querySelector('[data-calendar-time]');
        let selectedHour = '09';
        let selectedMinute = '00';
        const months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        let selectedDate = hiddenInput.value ? new Date(hiddenInput.value) : new Date();
        let viewMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);

        const isoDay = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        const sameDay = (a, b) => a && isoDay(a) === isoDay(b);
        const updateTrigger = () => {
            if (!hiddenInput.value) return;
            triggerText.textContent = selectedDate.toLocaleDateString('es-BO', { weekday:'short', day:'2-digit', month:'long', year:'numeric' }) + ` · ${selectedHour}:${selectedMinute}`;
        };
        if (hiddenInput.value) {
            const roundedMinutes = Math.round(selectedDate.getMinutes() / 15) * 15;
            if (roundedMinutes === 60) selectedDate.setHours(selectedDate.getHours() + 1, 0, 0, 0);
            selectedHour = String(selectedDate.getHours()).padStart(2, '0');
            selectedMinute = String(roundedMinutes % 60).padStart(2, '0');
            updateTrigger();
        }

        timePicker.querySelectorAll('[data-time-dropdown]').forEach(dropdown => {
            const type = dropdown.dataset.timeDropdown;
            const timeTrigger = dropdown.querySelector('[data-time-trigger]');
            const timeMenu = dropdown.querySelector('[data-time-menu]');
            const refreshTime = () => {
                const value = type === 'hour' ? selectedHour : selectedMinute;
                timeTrigger.querySelector('span').textContent = value;
                timeMenu.querySelectorAll('[data-time-value]').forEach(option => option.classList.toggle('is-selected', option.dataset.timeValue === value));
            };
            refreshTime();
            timeTrigger.addEventListener('click', event => {
                event.stopPropagation();
                const opening = timeMenu.hidden;
                timePicker.querySelectorAll('[data-time-menu]').forEach(menu => { menu.hidden = true; menu.closest('[data-time-dropdown]')?.classList.remove('is-open'); });
                timeMenu.hidden = !opening;
                dropdown.classList.toggle('is-open', opening);
                if (opening) timeMenu.querySelector('.is-selected')?.scrollIntoView({ block: 'center' });
            });
            timeMenu.querySelectorAll('[data-time-value]').forEach(option => option.addEventListener('click', event => {
                event.stopPropagation();
                if (type === 'hour') selectedHour = option.dataset.timeValue;
                else selectedMinute = option.dataset.timeValue;
                refreshTime();
                timeMenu.hidden = true;
                dropdown.classList.remove('is-open');
            }));
        });

        const renderCalendar = () => {
            title.textContent = `${months[viewMonth.getMonth()]} ${viewMonth.getFullYear()}`;
            days.replaceChildren();
            const first = new Date(viewMonth.getFullYear(), viewMonth.getMonth(), 1);
            const start = new Date(first); start.setDate(first.getDate() - first.getDay());
            const today = new Date();
            for (let index = 0; index < 42; index++) {
                const date = new Date(start); date.setDate(start.getDate() + index);
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = date.getDate();
                if (date.getMonth() !== viewMonth.getMonth()) button.classList.add('is-outside');
                if (sameDay(date, today)) button.classList.add('is-today');
                if (sameDay(date, selectedDate)) button.classList.add('is-selected');
                button.addEventListener('click', () => { selectedDate = new Date(date); viewMonth = new Date(date.getFullYear(), date.getMonth(), 1); renderCalendar(); });
                days.appendChild(button);
            }
        };
        trigger.addEventListener('click', event => {
            event.stopPropagation();
            popover.hidden = !popover.hidden;
            picker.classList.toggle('is-open', !popover.hidden);
            renderCalendar();
        });
        picker.querySelector('[data-calendar-prev]').addEventListener('click', () => { viewMonth.setMonth(viewMonth.getMonth() - 1); renderCalendar(); });
        picker.querySelector('[data-calendar-next]').addEventListener('click', () => { viewMonth.setMonth(viewMonth.getMonth() + 1); renderCalendar(); });
        picker.querySelector('[data-calendar-apply]').addEventListener('click', () => {
            hiddenInput.value = `${isoDay(selectedDate)}T${selectedHour}:${selectedMinute}`;
            updateTrigger();
            popover.hidden = true;
            picker.classList.remove('is-open');
        });
        popover.addEventListener('click', event => event.stopPropagation());
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-select-menu]').forEach(menu => { menu.hidden = true; menu.closest('[data-meeting-select]')?.classList.remove('is-open'); });
        document.querySelectorAll('[data-calendar-popover]').forEach(popover => { popover.hidden = true; popover.closest('[data-meeting-date-picker]')?.classList.remove('is-open'); });
    });
    @if($errors->any() && old('_meeting_form')) open(); @endif
});
</script>
