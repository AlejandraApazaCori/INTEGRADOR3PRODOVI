<section class="client-company-insights" aria-labelledby="client-company-insights-title">
    @if($empresas->isNotEmpty())
        @php
            $empresaInicial = $empresas->first();
            $initialProviders = collect($empresaInicial->analytics_providers ?? []);
            $initialNetworks = collect([
                $initialProviders->contains('facebook') ? 'Facebook' : null,
                $initialProviders->contains('instagram') ? 'Instagram' : null,
            ])->filter()->implode(' + ') ?: 'Sin cuentas vinculadas';
        @endphp
    @endif
    <header class="client-company-insights-head">
    

        @if($empresas->isNotEmpty())
            <div class="client-company-field">
                <span class="client-company-field-label">Empresa</span>
                <select id="client-analytics-company" class="client-custom-native" tabindex="-1" aria-hidden="true">
                    @foreach($empresas as $empresa)
                        @php
                            $providers = collect($empresa->analytics_providers ?? []);
                            $networks = collect([
                                $providers->contains('facebook') ? 'Facebook' : null,
                                $providers->contains('instagram') ? 'Instagram' : null,
                            ])->filter()->implode(' + ');
                        @endphp
                        <option
                            value="{{ $empresa->id }}"
                            data-company-name="{{ $empresa->nombre_empresa }}"
                            data-endpoint="{{ route('clientes.analiticas.empresa.datos', $empresa) }}"
                            data-fallback-endpoint="{{ route('clientes.analiticas.load-view', ['meta' => 1, 'empresa_id' => $empresa->id]) }}">
                            {{ $empresa->nombre_empresa }}{{ $networks ? ' — '.$networks : ' — Sin cuentas vinculadas' }}
                        </option>
                    @endforeach
                </select>
                <div class="client-company-dropdown" data-company-dropdown>
                    <button type="button" class="client-company-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span class="client-company-trigger-icon"><i class="fas fa-building"></i></span>
                        <span class="client-company-trigger-copy"><strong data-company-dropdown-value>{{ $empresaInicial->nombre_empresa }}</strong><small data-company-dropdown-detail>{{ $initialNetworks }}</small></span>
                        <i class="fas fa-chevron-down client-company-chevron"></i>
                    </button>
                    <div class="client-company-menu" role="listbox" hidden>
                        @foreach($empresas as $empresa)
                            @php
                                $providers = collect($empresa->analytics_providers ?? []);
                                $networks = collect([
                                    $providers->contains('facebook') ? 'Facebook' : null,
                                    $providers->contains('instagram') ? 'Instagram' : null,
                                ])->filter()->implode(' + ') ?: 'Sin cuentas vinculadas';
                            @endphp
                            <button type="button" role="option" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-company-option data-value="{{ $empresa->id }}" data-company-name="{{ $empresa->nombre_empresa }}" data-company-networks="{{ $networks }}">
                                <span><i class="fas fa-building"></i></span><span><strong>{{ $empresa->nombre_empresa }}</strong><small>{{ $networks }}</small></span><i class="fas fa-check"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </header>

    @if($empresas->isNotEmpty())
        <div class="client-company-current">
            <span><i class="fas fa-building"></i>Empresa seleccionada</span>
            <strong id="client-analytics-company-name">{{ $empresaInicial->nombre_empresa }}</strong>
        </div>

        @include('administrador.analiticas.analiticasporcuentas', [
            'analyticsEndpoint' => route('clientes.analiticas.empresa.datos', $empresaInicial),
            'analyticsFallbackEndpoint' => route('clientes.analiticas.load-view', ['meta' => 1, 'empresa_id' => $empresaInicial->id]),
            'loadChartJs' => false,
            'analyticsEmptyMessage' => 'Esta empresa no tiene cuentas de Meta vinculadas',
            'analyticsEmptyDetail' => 'Vincula su página de Facebook o cuenta profesional de Instagram desde Mi cuenta.',
        ])
    @else
        <div class="client-company-insights-empty">
            <i class="fas fa-building-circle-exclamation"></i>
            <strong>No tienes empresas registradas</strong>
            <p>Registra una empresa y vincula sus cuentas sociales para consultar estadísticas reales.</p>
        </div>
    @endif
</section>

<style>
.client-company-insights{margin-top:34px;padding-top:28px;border-top:2px solid #e2dce5}.client-company-insights-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:15px}.client-company-insights-head small,.client-company-insights-head h2,.client-company-insights-head p{display:block}.client-company-insights-head small{color:#5b2b76;font-size:.62rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.client-company-insights-head h2{margin:5px 0 0;color:#302834;font-size:1.25rem;font-weight:900;letter-spacing:-.025em}.client-company-insights-head h2:after{content:'';display:block;width:46px;height:3px;margin-top:8px;border-radius:999px;background:#5b2b76}.client-company-insights-head p{max-width:680px;margin:8px 0 0;color:#766d79;font-size:.72rem;line-height:1.55}.client-company-insights-head label{min-width:270px;display:grid;gap:6px;color:#766d79;font-size:.58rem;font-weight:900;text-transform:uppercase}.client-company-insights-head select{width:100%;height:42px;padding:0 36px 0 12px;border:1px solid #d9d2dc;border-radius:9px;background:#fff;color:#3b3240;font-size:.66rem;font-weight:800}.client-company-current{display:flex;align-items:center;gap:10px;margin-bottom:10px;padding:11px 14px;border:1px solid #e3dee6;border-radius:10px;background:#faf8fb}.client-company-current span{display:flex;align-items:center;gap:6px;color:#8a7c91;font-size:.55rem;font-weight:900;text-transform:uppercase}.client-company-current strong{color:#4b3655;font-size:.68rem}.client-company-insights-empty{display:grid;place-items:center;min-height:240px;padding:30px;border:1px dashed #d9d2dc;border-radius:12px;background:#faf9fb;text-align:center}.client-company-insights-empty>i{margin-bottom:12px;color:#5b2b76;font-size:1.6rem}.client-company-insights-empty strong{color:#3b3240;font-size:.85rem}.client-company-insights-empty p{margin:7px 0 0;color:#817786;font-size:.68rem}
.client-company-field{position:relative;min-width:310px;display:grid;gap:6px}.client-company-field-label{color:#766d79;font-size:.58rem;font-weight:900;text-transform:uppercase}.client-custom-native{position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important}.client-company-dropdown{position:relative}.client-company-trigger{width:100%;min-height:48px;display:grid;grid-template-columns:30px minmax(0,1fr) 18px;align-items:center;gap:9px;padding:7px 11px;border:1px solid #d9d2dc;border-radius:10px;background:#fff;color:#3b3240;text-align:left;box-shadow:0 4px 12px rgba(55,42,63,.04);cursor:pointer;transition:.18s}.client-company-trigger:hover,.client-company-trigger[aria-expanded="true"]{border-color:#8e6aa2;box-shadow:0 0 0 3px rgba(91,43,118,.09)}.client-company-trigger-icon{width:30px;height:30px;display:grid;place-items:center;border-radius:8px;background:#f3edf6;color:#5b2b76}.client-company-trigger-copy{min-width:0}.client-company-trigger-copy strong,.client-company-trigger-copy small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.client-company-trigger-copy strong{font-size:.66rem}.client-company-trigger-copy small{margin-top:2px;color:#8d8391;font-size:.51rem;font-weight:700;text-transform:none}.client-company-chevron{color:#8e8392;font-size:.58rem;transition:transform .18s}.client-company-trigger[aria-expanded="true"] .client-company-chevron{transform:rotate(180deg)}.client-company-menu{position:absolute;z-index:80;top:calc(100% + 7px);right:0;width:100%;max-height:260px;padding:6px;overflow-y:auto;border:1px solid #ded7e2;border-radius:11px;background:#fff;box-shadow:0 16px 35px rgba(43,31,49,.18)}.client-company-menu[hidden]{display:none}.client-company-menu button{width:100%;display:grid;grid-template-columns:28px minmax(0,1fr) 16px;align-items:center;gap:8px;padding:9px;border:0;border-radius:8px;background:transparent;color:#433849;text-align:left;cursor:pointer}.client-company-menu button:hover,.client-company-menu button:focus-visible{outline:0;background:#f6f1f8}.client-company-menu button[aria-selected="true"]{background:#f3edf6;color:#5b2b76}.client-company-menu button>span:first-child{width:28px;height:28px;display:grid;place-items:center;border-radius:7px;background:#eee9f1;color:#5b2b76}.client-company-menu button strong,.client-company-menu button small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.client-company-menu button strong{font-size:.61rem}.client-company-menu button small{margin-top:2px;color:#8b808f;font-size:.49rem;font-weight:700;text-transform:none}.client-company-menu button>i{visibility:hidden;color:#5b2b76;font-size:.58rem}.client-company-menu button[aria-selected="true"]>i{visibility:visible}
html[data-client-theme="dark"] .client-company-insights{border-color:#3b3540}html[data-client-theme="dark"] .client-company-insights-head h2{color:#f1edf3}html[data-client-theme="dark"] .client-company-insights-head p,html[data-client-theme="dark"] .client-company-field-label{color:#b4abb8}html[data-client-theme="dark"] .client-company-trigger,html[data-client-theme="dark"] .client-company-menu,html[data-client-theme="dark"] .client-company-current{border-color:#49414e;background:#211e24;color:#eee9f0}html[data-client-theme="dark"] .client-company-menu button{color:#eee9f0}html[data-client-theme="dark"] .client-company-menu button:hover,html[data-client-theme="dark"] .client-company-menu button[aria-selected="true"]{background:#312837}html[data-client-theme="dark"] .client-company-current strong{color:#eee9f0}
@media(max-width:760px){.client-company-insights-head{align-items:stretch;flex-direction:column}.client-company-field{min-width:0}.client-company-current{align-items:flex-start;flex-direction:column}}
</style>

@if($empresas->isNotEmpty())
<script>
const companySelect = document.getElementById('client-analytics-company');
const companyDropdown = document.querySelector('[data-company-dropdown]');
const companyTrigger = companyDropdown?.querySelector('.client-company-trigger');
const companyMenu = companyDropdown?.querySelector('.client-company-menu');
const companyOptions = Array.from(companyDropdown?.querySelectorAll('[data-company-option]') || []);
const closeCompanyDropdown = (restoreFocus = false) => {
    if (!companyMenu || !companyTrigger) return;
    companyMenu.hidden = true;
    companyTrigger.setAttribute('aria-expanded', 'false');
    if (restoreFocus) companyTrigger.focus();
};
companyTrigger?.addEventListener('click', () => {
    const willOpen = companyMenu.hidden;
    companyMenu.hidden = !willOpen;
    companyTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    if (willOpen) companyOptions.find(option => option.getAttribute('aria-selected') === 'true')?.focus();
});
companyTrigger?.addEventListener('keydown', event => {
    if (!['ArrowDown', 'Enter', ' '].includes(event.key)) return;
    event.preventDefault();
    companyMenu.hidden = false;
    companyTrigger.setAttribute('aria-expanded', 'true');
    companyOptions.find(option => option.getAttribute('aria-selected') === 'true')?.focus();
});
companyOptions.forEach((option, index) => {
    option.addEventListener('keydown', event => {
        if (event.key === 'Escape') { event.preventDefault(); closeCompanyDropdown(true); }
        if (event.key === 'ArrowDown') { event.preventDefault(); companyOptions[(index + 1) % companyOptions.length].focus(); }
        if (event.key === 'ArrowUp') { event.preventDefault(); companyOptions[(index - 1 + companyOptions.length) % companyOptions.length].focus(); }
    });
    option.addEventListener('click', () => {
        companySelect.value = option.dataset.value;
        companyOptions.forEach(item => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
        companyDropdown.querySelector('[data-company-dropdown-value]').textContent = option.dataset.companyName;
        companyDropdown.querySelector('[data-company-dropdown-detail]').textContent = option.dataset.companyNetworks;
        companySelect.dispatchEvent(new Event('change', {bubbles:true}));
        closeCompanyDropdown(true);
    });
});
document.addEventListener('click', event => {
    if (companyDropdown && !companyDropdown.contains(event.target)) closeCompanyDropdown();
});
companySelect?.addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    document.getElementById('client-analytics-company-name').textContent = option.dataset.companyName;
    window.reloadMetaAnalytics?.(option.dataset.endpoint, option.dataset.fallbackEndpoint);
});
window.loadCampaignAnalytics?.();
</script>
@endif
