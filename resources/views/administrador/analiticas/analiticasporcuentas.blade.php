@php
    $analyticsDefaultPeriod = (string) ($defaultAnalyticsDays ?? '30');
    $analyticsPeriodLabels = [
        '7' => 'Últimos 7 días',
        '30' => 'Últimos 30 días',
        '90' => 'Últimos 90 días',
        '365' => 'Último año',
        '730' => 'Últimos 2 años',
        'all' => 'Todo el historial',
    ];
    $analyticsDefaultPeriodLabel = $analyticsPeriodLabels[$analyticsDefaultPeriod] ?? $analyticsPeriodLabels['30'];
@endphp
<section
    class="meta-analytics"
    id="campaign-meta-analytics"
    data-endpoint="{{ $analyticsEndpoint ?? route('administrador.campañas.analiticas.datos', $campania) }}"
    data-fallback-endpoint="{{ $analyticsFallbackEndpoint ?? '' }}"
    data-empty-message="{{ $analyticsEmptyMessage ?? 'No hay cuentas de Meta vinculadas a esta campaña' }}"
    data-empty-detail="{{ $analyticsEmptyDetail ?? 'El cliente debe vincular su página de Facebook o cuenta profesional de Instagram desde su panel.' }}">
    <header class="meta-analytics-head">
        <div>
            <h2>Analíticas por cuenta</h2>
        </div>
        @if(!($hideAnalyticsPeriod ?? false))
        <div class="meta-period-field">
            <span>Periodo</span>
            <select id="meta-analytics-period" class="meta-custom-native" tabindex="-1" aria-hidden="true">
                @foreach($analyticsPeriodLabels as $value => $label)
                    <option value="{{ $value }}" {{ $analyticsDefaultPeriod === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="meta-period-dropdown" data-period-dropdown>
                <button type="button" class="meta-period-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span><i class="far fa-calendar"></i></span><strong data-period-value>{{ $analyticsDefaultPeriodLabel }}</strong><i class="fas fa-chevron-down"></i>
                </button>
                <div class="meta-period-menu" role="listbox" hidden>
                    @foreach($analyticsPeriodLabels as $value => $label)
                        @php $shortLabel = ['7' => '7', '30' => '30', '90' => '90', '365' => '1A', '730' => '2A', 'all' => '∞'][(string) $value]; @endphp
                        <button type="button" role="option" aria-selected="{{ $analyticsDefaultPeriod === (string) $value ? 'true' : 'false' }}" data-period-option data-value="{{ $value }}"><span>{{ $shortLabel }}</span><strong>{{ $label }}</strong><i class="fas fa-check"></i></button>
                    @endforeach
                </div>
            </div>
        </div>
        @else
            <select id="meta-analytics-period" hidden aria-hidden="true">
                <option value="{{ $defaultAnalyticsDays ?? 'all' }}" selected>Todo el historial</option>
            </select>
        @endif
    </header>

    <nav class="meta-analytics-tabs" aria-label="Filtros de analíticas">
        <button type="button" class="is-active" data-analytics-scope="summary"><i class="fas fa-chart-pie"></i>Resumen</button>
        <button type="button" data-analytics-scope="facebook"><i class="fab fa-facebook-f"></i>Facebook</button>
        <button type="button" data-analytics-scope="instagram"><i class="fab fa-instagram"></i>Instagram</button>
        <button type="button" data-analytics-scope="audience"><i class="fas fa-users"></i>Audiencia</button>
    </nav>

    <div class="meta-analytics-body">
    <div class="meta-analytics-state" id="meta-analytics-state">
        <i class="fas fa-circle-notch fa-spin"></i><strong>Consultando Meta Insights</strong><span>Esto puede tardar unos segundos.</span>
    </div>

    <div id="meta-analytics-content" hidden>
        <div class="meta-account-strip" id="meta-account-strip"></div>
        <section class="meta-kpis" id="meta-kpis"></section>

        <div class="meta-chart-grid" data-analytics-general>
            <article class="meta-card"><header><div><small>Evolución</small><h3>Crecimiento de seguidores</h3></div></header><div class="meta-chart"><canvas id="meta-followers-chart"></canvas><div class="meta-no-data" data-empty="followers" hidden>Datos no disponibles</div></div></article>
            <article class="meta-card"><header><div><small>Interacciones reales</small><h3>Distribución de engagement</h3></div></header><div class="meta-chart"><canvas id="meta-engagement-chart"></canvas><div class="meta-no-data" data-empty="engagement" hidden>Datos no disponibles</div></div></article>
        </div>

        <div class="meta-chart-grid" data-analytics-general>
            <article class="meta-card meta-best-time"><header><div><small>Histórico por publicación</small><h3>Mejor hora para publicar</h3></div><span class="meta-evidence">Mínimo 2 publicaciones por franja</span></header><div id="meta-best-times"></div><div class="meta-heatmap-wrap" id="meta-heatmap"></div></article>
            <article class="meta-card"><header><div><small>Comparación</small><h3>Rendimiento por tipo de contenido</h3></div></header><div class="meta-chart"><canvas id="meta-content-chart"></canvas><div class="meta-no-data" data-empty="content" hidden>Datos no disponibles</div></div></article>
        </div>

        <section class="meta-audience" id="meta-audience">
            <div class="meta-section-title"><small>Datos demográficos disponibles</small><h3>Audiencia principal</h3></div>
            <div class="meta-audience-grid">
                <article class="meta-card"><header><div><h3>Edad y sexo</h3></div></header><div class="meta-chart"><canvas id="meta-age-chart"></canvas><div class="meta-no-data" data-empty="age" hidden>Datos no disponibles</div></div></article>
                <article class="meta-card meta-ranking"><header><div><h3>Principales ciudades</h3></div></header><div id="meta-cities"></div></article>
                <article class="meta-card meta-ranking"><header><div><h3>Principales países</h3></div></header><div id="meta-countries"></div></article>
            </div>
        </section>

        <section class="meta-card meta-top-posts" data-analytics-general><header><div><small>Contenido histórico del periodo</small><h3>Top 5 publicaciones</h3></div></header><div id="meta-top-posts"></div></section>
        <details class="meta-api-notes" id="meta-api-notes" hidden><summary>Algunas métricas no estuvieron disponibles</summary><div></div></details>
    </div>
    </div>
</section>

<style>
.meta-analytics{--ma-purple:#5b2b76;--ma-orange:#ef6c22;--ma-green:#7da533;--ma-teal:#117e8c;min-height:430px;padding-bottom:35px;color:#302834}.meta-analytics [hidden]{display:none!important}.meta-analytics-body{position:relative;min-height:310px}.meta-analytics-head{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;padding:23px 25px;border:1px solid #e2dee5;border-radius:13px 13px 0 0;background:#fff}.meta-analytics-head small,.meta-analytics-head h2,.meta-analytics-head p{display:block}.meta-analytics-head small{color:var(--ma-purple);font-size:.57rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.meta-analytics-head h2{margin:4px 0 0;color:#29222d;font-size:1.2rem;font-weight:900;letter-spacing:-.025em}.meta-analytics-head h2:after,.meta-section-title h3:after,.meta-card>header h3:after{content:'';display:block;width:42px;height:3px;margin-top:7px;border-radius:999px;background:var(--ma-purple)}.meta-analytics-head p{margin:7px 0 0;color:#7d7580;font-size:.67rem}.meta-analytics-head label{display:grid;gap:5px;color:#756d78;font-size:.57rem;font-weight:900;text-transform:uppercase}.meta-analytics-head select{min-width:155px;height:39px;padding:0 34px 0 11px;border:1px solid #dcd7df;border-radius:9px;background:#fff;color:#423947;font-size:.65rem;font-weight:800}.meta-analytics-tabs{display:flex;overflow-x:auto;border-right:1px solid #e2dee5;border-bottom:1px solid #e2dee5;border-left:1px solid #e2dee5;background:#fff}.meta-analytics-tabs button{min-width:130px;min-height:47px;display:flex;align-items:center;justify-content:center;gap:7px;flex:1;border:0;border-right:1px solid #eeeaf0;background:#fff;color:#79717c;font-size:.64rem;font-weight:900;cursor:pointer}.meta-analytics-tabs button:last-child{border-right:0}.meta-analytics-tabs button.is-active{box-shadow:inset 0 -3px 0 var(--ma-purple);color:var(--ma-purple);background:#faf8fb}.meta-analytics-tabs button:disabled{cursor:not-allowed;opacity:.42}.meta-analytics-state{position:absolute;z-index:50;inset:0;min-height:310px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:7px;border:1px solid #e7e3e9;border-top:0;background:rgba(255,255,255,.94);backdrop-filter:blur(3px);color:#817986}.meta-analytics-state>i{margin-bottom:6px;color:var(--ma-purple);font-size:1.35rem}.meta-analytics-state strong{color:#403646;font-size:.78rem}.meta-analytics-state span{font-size:.62rem}.meta-account-strip{display:flex;flex-wrap:wrap;gap:8px;padding:13px 0}.meta-account{display:flex;align-items:center;gap:9px;padding:8px 11px;border:1px solid #e2dee5;border-radius:10px;background:#fff}.meta-account>span{width:28px;height:28px;display:grid;place-items:center;border-radius:8px;color:#fff}.meta-account.is-facebook>span{background:#1877f2}.meta-account.is-instagram>span{background:linear-gradient(135deg,#f58529,#dd2a7b,#8134af)}.meta-account strong,.meta-account small{display:block}.meta-account strong{color:#3a323e;font-size:.62rem}.meta-account small{margin-top:2px;color:#918995;font-size:.52rem}.meta-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));overflow:hidden;border:1px solid #e4e0e6;border-radius:12px;background:#fff}.meta-kpi{min-width:0;padding:15px;border-right:1px solid #ece8ee;box-shadow:inset 0 3px 0 var(--kpi,var(--ma-purple))}.meta-kpi:last-child{border-right:0}.meta-kpi small,.meta-kpi strong,.meta-kpi span{display:block}.meta-kpi small{color:#8b838e;font-size:.51rem;font-weight:900;text-transform:uppercase}.meta-kpi strong{margin-top:6px;overflow:hidden;color:#322a36;font-size:1.05rem;font-weight:900;text-overflow:ellipsis}.meta-kpi span{margin-top:4px;color:#9a929d;font-size:.5rem}.meta-chart-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px;margin-top:13px}.meta-card{min-width:0;padding:17px;border:1px solid #e4e0e6;border-radius:12px;background:#fff;box-shadow:0 5px 14px rgba(48,40,52,.04)}.meta-card>header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:15px}.meta-card>header small,.meta-card>header h3{display:block}.meta-card>header small{color:#8c8490;font-size:.51rem;font-weight:900;text-transform:uppercase}.meta-card>header h3,.meta-section-title h3{margin:3px 0 0;color:#332b37;font-size:.78rem;font-weight:900}.meta-card>header h3:after{width:34px;height:2px;margin-top:6px}.meta-chart{position:relative;height:285px}.meta-no-data{position:absolute;inset:0;display:grid;place-items:center;border:1px dashed #dcd7df;border-radius:10px;background:#faf9fb;color:#928b96;font-size:.63rem;font-weight:800}.meta-evidence{padding:6px 8px;border-radius:999px;background:#f3edf6;color:var(--ma-purple);font-size:.5rem;font-weight:900}.meta-best-list{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:13px}.meta-best-slot{padding:8px 10px;border:1px solid #ddd3e2;border-radius:9px;background:#faf8fb}.meta-best-slot strong,.meta-best-slot small{display:block}.meta-best-slot strong{color:#493351;font-size:.59rem}.meta-best-slot small{margin-top:2px;color:#8d7c94;font-size:.49rem}.meta-empty-inline{padding:18px;border:1px dashed #dcd7df;border-radius:9px;color:#928b96;text-align:center;font-size:.61rem}.meta-heatmap-wrap{overflow-x:auto}.meta-heatmap{min-width:680px;display:grid;grid-template-columns:66px repeat(24,1fr);gap:3px}.meta-heatmap>span{min-height:19px;display:grid;place-items:center;border-radius:4px;color:#827a86;font-size:.42rem}.meta-heatmap .day{justify-content:start;font-size:.49rem;font-weight:900}.meta-heatmap .cell{background:rgba(91,43,118,var(--heat,.05))}.meta-audience{margin-top:18px;padding-top:18px;border-top:1px solid #e6e2e8}.meta-section-title{margin-bottom:12px}.meta-section-title small{color:#8c8490;font-size:.52rem;font-weight:900;text-transform:uppercase}.meta-audience-grid{display:grid;grid-template-columns:1.2fr .9fr .9fr;gap:13px}.meta-ranking>div{display:grid;gap:10px}.meta-rank{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:5px}.meta-rank>div{grid-column:1/-1;height:5px;overflow:hidden;border-radius:999px;background:#eeeaf0}.meta-rank>div>span{display:block;height:100%;border-radius:inherit;background:var(--ma-purple)}.meta-rank strong{overflow:hidden;color:#59505e;font-size:.58rem;text-overflow:ellipsis;white-space:nowrap}.meta-rank small{color:#7e7084;font-size:.53rem;font-weight:900}.meta-top-posts{margin-top:13px}.meta-post-list{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}.meta-post{min-width:0;overflow:hidden;border:1px solid #e6e2e8;border-radius:10px;background:#faf9fb}.meta-post-media{height:95px;display:grid;place-items:center;overflow:hidden;background:#eeebef;color:#a39ca6}.meta-post-media img{width:100%;height:100%;object-fit:cover}.meta-post-body{padding:10px}.meta-post-platform{display:inline-flex;align-items:center;gap:4px;color:var(--ma-purple);font-size:.48rem;font-weight:900;text-transform:uppercase}.meta-post p{height:34px;margin:6px 0!important;overflow:hidden;color:#5f5763!important;font-size:.54rem!important;line-height:1.45}.meta-post-metrics{display:flex;flex-wrap:wrap;gap:5px;color:#7e7582;font-size:.48rem}.meta-api-notes{margin-top:12px;padding:11px 13px;border:1px solid #f0d9aa;border-radius:9px;background:#fff9ed;color:#846324;font-size:.57rem}.meta-api-notes summary{font-weight:900;cursor:pointer}.meta-api-notes p{margin:6px 0 0}.meta-api-notes code{font-size:.52rem}.meta-analytics.is-audience [data-analytics-general],.meta-analytics.is-audience #meta-kpis,.meta-analytics.is-audience #meta-account-strip{display:none}.meta-analytics.is-audience .meta-audience{margin-top:13px;padding-top:0;border-top:0}
@media(max-width:1050px){.meta-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.meta-kpi:nth-child(3){border-right:0}.meta-kpi:nth-child(-n+3){border-bottom:1px solid #ece8ee}.meta-audience-grid{grid-template-columns:1fr 1fr}.meta-audience-grid>.meta-card:first-child{grid-column:1/-1}.meta-post-list{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:720px){.meta-analytics-head{align-items:stretch;flex-direction:column}.meta-analytics-head select{width:100%}.meta-chart-grid{grid-template-columns:1fr}.meta-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.meta-kpi:nth-child(2n){border-right:0}.meta-kpi:nth-child(3){border-right:1px solid #ece8ee}.meta-kpi:nth-child(-n+4){border-bottom:1px solid #ece8ee}.meta-audience-grid{grid-template-columns:1fr}.meta-audience-grid>.meta-card:first-child{grid-column:auto}.meta-post-list{grid-template-columns:1fr 1fr}}
@media(max-width:480px){.meta-post-list{grid-template-columns:1fr}.meta-analytics-tabs button{min-width:112px}.meta-card{padding:14px}}
.meta-period-field{position:relative;min-width:205px;display:grid;gap:5px}.meta-period-field>span{color:#756d78;font-size:.57rem;font-weight:900;text-transform:uppercase}.meta-custom-native{position:absolute!important;width:1px!important;height:1px!important;min-width:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important}.meta-period-dropdown{position:relative}.meta-period-trigger{width:100%;height:42px;display:grid;grid-template-columns:27px minmax(0,1fr) 14px;align-items:center;gap:7px;padding:0 10px;border:1px solid #dcd7df;border-radius:9px;background:#fff;color:#423947;text-align:left;box-shadow:0 4px 12px rgba(55,42,63,.04);cursor:pointer;transition:.18s}.meta-period-trigger:hover,.meta-period-trigger[aria-expanded="true"]{border-color:#8e6aa2;box-shadow:0 0 0 3px rgba(91,43,118,.09)}.meta-period-trigger>span{width:27px;height:27px;display:grid;place-items:center;border-radius:7px;background:#f3edf6;color:var(--ma-purple);font-size:.58rem}.meta-period-trigger strong{font-size:.62rem}.meta-period-trigger>i{color:#8e8392;font-size:.54rem;transition:transform .18s}.meta-period-trigger[aria-expanded="true"]>i{transform:rotate(180deg)}.meta-period-menu{position:absolute;z-index:90;top:calc(100% + 7px);right:0;width:100%;padding:6px;border:1px solid #ded7e2;border-radius:10px;background:#fff;box-shadow:0 15px 32px rgba(43,31,49,.18)}.meta-period-menu[hidden]{display:none}.meta-period-menu button{width:100%;display:grid;grid-template-columns:28px minmax(0,1fr) 14px;align-items:center;gap:8px;padding:8px;border:0;border-radius:7px;background:transparent;color:#493e4e;text-align:left;cursor:pointer}.meta-period-menu button:hover,.meta-period-menu button:focus-visible{outline:0;background:#f6f1f8}.meta-period-menu button[aria-selected="true"]{background:#f3edf6;color:var(--ma-purple)}.meta-period-menu button>span{width:28px;height:25px;display:grid;place-items:center;border-radius:6px;background:#eee9f1;font-size:.5rem;font-weight:900}.meta-period-menu button strong{font-size:.58rem}.meta-period-menu button>i{visibility:hidden;color:var(--ma-purple);font-size:.52rem}.meta-period-menu button[aria-selected="true"]>i{visibility:visible}
@media(max-width:720px){.meta-period-field{width:100%;min-width:0}}
</style>

@if($loadChartJs ?? true)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
<script>
(() => {
    const root = document.getElementById('campaign-meta-analytics');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';
    const state = document.getElementById('meta-analytics-state');
    const content = document.getElementById('meta-analytics-content');
    const period = document.getElementById('meta-analytics-period');
    const periodDropdown = root.querySelector('[data-period-dropdown]');
    const periodTrigger = periodDropdown?.querySelector('.meta-period-trigger');
    const periodMenu = periodDropdown?.querySelector('.meta-period-menu');
    const periodOptions = Array.from(periodDropdown?.querySelectorAll('[data-period-option]') || []);
    const charts = {};
    let analytics = null;
    let currentScope = 'summary';
    let loadedDays = null;
    let loadSequence = 0;
    const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const number = value => value === null || value === undefined ? 'Datos no disponibles' : new Intl.NumberFormat('es-BO', {maximumFractionDigits:1}).format(value);
    const hasValues = values => Array.isArray(values) && values.some(value => value !== null && value !== undefined);
    const chart = (key, elementId, config) => {
        charts[key]?.destroy();
        const canvas = document.getElementById(elementId);
        if (!canvas || typeof Chart === 'undefined') return;
        charts[key] = new Chart(canvas, config);
    };
    const setEmpty = (name, empty) => {
        const node = root.querySelector(`[data-empty="${name}"]`);
        if (node) node.hidden = !empty;
    };
    const scopeData = () => currentScope === 'summary' || currentScope === 'audience' ? analytics.summary : analytics.platforms[currentScope];
    const closePeriodDropdown = (restoreFocus = false) => {
        if (!periodMenu || !periodTrigger) return;
        periodMenu.hidden = true;
        periodTrigger.setAttribute('aria-expanded', 'false');
        if (restoreFocus) periodTrigger.focus();
    };
    periodTrigger?.addEventListener('click', () => {
        const willOpen = periodMenu.hidden;
        periodMenu.hidden = !willOpen;
        periodTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        if (willOpen) periodOptions.find(option => option.getAttribute('aria-selected') === 'true')?.focus();
    });
    periodTrigger?.addEventListener('keydown', event => {
        if (!['ArrowDown', 'Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        periodMenu.hidden = false;
        periodTrigger.setAttribute('aria-expanded', 'true');
        periodOptions.find(option => option.getAttribute('aria-selected') === 'true')?.focus();
    });
    periodOptions.forEach((option, index) => {
        option.addEventListener('keydown', event => {
            if (event.key === 'Escape') { event.preventDefault(); closePeriodDropdown(true); }
            if (event.key === 'ArrowDown') { event.preventDefault(); periodOptions[(index + 1) % periodOptions.length].focus(); }
            if (event.key === 'ArrowUp') { event.preventDefault(); periodOptions[(index - 1 + periodOptions.length) % periodOptions.length].focus(); }
        });
        option.addEventListener('click', () => {
            period.value = option.dataset.value;
            periodOptions.forEach(item => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
            periodDropdown.querySelector('[data-period-value]').textContent = option.querySelector('strong').textContent;
            period.dispatchEvent(new Event('change', {bubbles:true}));
            closePeriodDropdown(true);
        });
    });
    document.addEventListener('click', event => {
        if (periodDropdown && !periodDropdown.contains(event.target)) closePeriodDropdown();
    });

    function renderAccounts() {
        const connected = Object.values(analytics.platforms).filter(item => item.connected);
        document.getElementById('meta-account-strip').innerHTML = connected.map(item => `<article class="meta-account is-${item.platform}"><span><i class="fab fa-${item.platform}${item.platform === 'facebook' ? '-f' : ''}"></i></span><div><strong>${esc(item.account?.name || item.account?.username || item.platform)}</strong><small>${item.platform === 'facebook' ? 'Página de Facebook' : 'Cuenta profesional de Instagram'}</small></div></article>`).join('');
        ['facebook','instagram'].forEach(platform => {
            const button = root.querySelector(`[data-analytics-scope="${platform}"]`);
            button.disabled = !analytics.platforms[platform].connected;
            button.title = button.disabled ? 'Cuenta no conectada a esta campaña' : '';
        });
        const audienceButton = root.querySelector('[data-analytics-scope="audience"]');
        if (audienceButton) {
            audienceButton.disabled = !analytics.platforms.instagram.connected;
            audienceButton.title = audienceButton.disabled ? 'Los datos demográficos están disponibles solamente para Instagram' : '';
        }
    }

    function renderKpis(data) {
        const insightsPeriod = analytics?.period?.insights_limited ? 'Últimos 90 días disponibles' : 'Periodo seleccionado';
        const items = [
            ['Seguidores', data.totals.followers, 'fa-users', '#5b2b76', 'Total actual'],
            ['Alcance', data.totals.reach, 'fa-signal', '#117e8c', insightsPeriod],
            ['Visualizaciones', data.totals.views, 'fa-eye', '#7da533', insightsPeriod],
            ['Engagement', data.totals.engagement, 'fa-heart', '#ef6c22', 'Periodo seleccionado'],
            ['Publicaciones', data.totals.posts, 'fa-photo-film', '#c94f0c', 'Periodo seleccionado'],
            ['Promedio / post', data.totals.average_engagement, 'fa-chart-line', '#5b2b76', 'Periodo seleccionado'],
        ];
        document.getElementById('meta-kpis').innerHTML = items.map(([label,value,icon,color,note]) => `<article class="meta-kpi" style="--kpi:${color}"><small><i class="fas ${icon}"></i> ${label}</small><strong>${number(value)}</strong><span>${note}</span></article>`).join('');
    }

    function renderFollowers(data) {
        let labels, datasets;
        if (currentScope === 'summary' || currentScope === 'audience') {
            labels = data.followers.labels;
            datasets = [
                {label:'Facebook',data:data.followers.facebook,borderColor:'#1877f2',backgroundColor:'rgba(24,119,242,.08)',tension:.3,spanGaps:true},
                {label:'Instagram',data:data.followers.instagram,borderColor:'#dd2a7b',backgroundColor:'rgba(221,42,123,.08)',tension:.3,spanGaps:true},
            ].filter(dataset => hasValues(dataset.data));
        } else {
            labels = data.followers.labels;
            datasets = [{label:currentScope === 'facebook' ? 'Facebook' : 'Instagram',data:data.followers.values,borderColor:currentScope === 'facebook' ? '#1877f2' : '#dd2a7b',backgroundColor:currentScope === 'facebook' ? 'rgba(24,119,242,.08)' : 'rgba(221,42,123,.08)',tension:.3,spanGaps:true}].filter(dataset => hasValues(dataset.data));
        }
        setEmpty('followers', datasets.length === 0);
        chart('followers','meta-followers-chart',{type:'line',data:{labels,datasets},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{labels:{usePointStyle:true}}},scales:{x:{grid:{display:false}},y:{beginAtZero:false}}}});
    }

    function renderEngagement(data) {
        const categories = ['reactions','comments','shares','saves','clicks'];
        const labels = ['Reacciones/Me gusta','Comentarios','Compartidos','Guardados','Clics'];
        let datasets;
        if (currentScope === 'summary' || currentScope === 'audience') {
            datasets = ['facebook','instagram'].map(platform => ({label:platform === 'facebook' ? 'Facebook' : 'Instagram',data:categories.map(key => data.engagement[platform]?.[key] ?? null),backgroundColor:platform === 'facebook' ? '#1877f2' : '#dd2a7b'})).filter(dataset => hasValues(dataset.data));
        } else {
            datasets = [{label:currentScope === 'facebook' ? 'Facebook' : 'Instagram',data:categories.map(key => data.engagement[key] ?? null),backgroundColor:currentScope === 'facebook' ? '#1877f2' : '#dd2a7b'}].filter(dataset => hasValues(dataset.data));
        }
        if (datasets.length === 0 && data.top_posts?.length) {
            const postsByPlatform = data.top_posts.reduce((groups, post) => {
                const platform = post.platform || currentScope;
                (groups[platform] ||= []).push(post);
                return groups;
            }, {});
            datasets = Object.entries(postsByPlatform).map(([platform, posts]) => ({
                label: platform === 'facebook' ? 'Facebook' : 'Instagram',
                data: categories.map(key => {
                    const postKey = key === 'reactions' ? 'likes' : key;
                    const available = posts.filter(post => post[postKey] !== null && post[postKey] !== undefined);
                    return available.length ? available.reduce((sum, post) => sum + Number(post[postKey] || 0), 0) : null;
                }),
                backgroundColor: platform === 'facebook' ? '#1877f2' : '#dd2a7b',
            })).filter(dataset => hasValues(dataset.data));
        }
        setEmpty('engagement', datasets.length === 0);
        chart('engagement','meta-engagement-chart',{type:'bar',data:{labels,datasets},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{usePointStyle:true}}},scales:{x:{grid:{display:false}},y:{beginAtZero:true}}}});
    }

    function renderBestTimes(data) {
        const best = data.best_posting_times || {best:[],slots:[],sufficient_data:false};
        document.getElementById('meta-best-times').innerHTML = best.sufficient_data
            ? `<div class="meta-best-list">${best.best.map(slot => `<div class="meta-best-slot"><strong>${esc(slot.label)}</strong><small>${slot.samples} publicaciones · puntaje ${number(slot.adjusted_score)}</small></div>`).join('')}</div>`
            : '<div class="meta-empty-inline">Aún no existen al menos dos publicaciones en una misma franja horaria. No se asumirá una mejor hora con evidencia insuficiente.</div>';
        const slots = best.slots || [];
        const max = Math.max(...slots.map(slot => Number(slot.adjusted_score || 0)), 0);
        const days = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
        let html = '<div class="meta-heatmap"><span></span>' + Array.from({length:24},(_,hour)=>`<span>${String(hour).padStart(2,'0')}</span>`).join('');
        days.forEach((day,index) => {
            html += `<span class="day">${day.slice(0,3)}</span>`;
            for (let hour=0;hour<24;hour++) {
                const slot = slots.find(item => Number(item.day) === index+1 && Number(item.hour) === hour);
                const heat = slot && max > 0 ? Math.max(.08, Number(slot.adjusted_score)/max) : .03;
                html += `<span class="cell" style="--heat:${heat}" title="${slot ? `${esc(slot.label)} · ${slot.samples} publicaciones · ${number(slot.adjusted_score)}` : `${day} ${String(hour).padStart(2,'0')}:00 · sin publicaciones`}"></span>`;
            }
        });
        document.getElementById('meta-heatmap').innerHTML = html + '</div>';
    }

    function renderContent(data) {
        let items = data.content_types || [];
        if (items.length === 0 && data.top_posts?.length) {
            const postsByType = data.top_posts.reduce((groups, post) => {
                const type = post.type || 'POST';
                (groups[type] ||= []).push(post);
                return groups;
            }, {});
            items = Object.entries(postsByType).map(([type, posts]) => ({
                type,
                posts: posts.length,
                engagement: posts.reduce((sum, post) => sum + Number(post.engagement || post.likes || post.reactions || 0), 0),
            }));
        }
        const useEngagement = items.some(item => Number(item.engagement || 0) > 0);
        setEmpty('content', items.length === 0);
        chart('content','meta-content-chart',{type:'doughnut',data:{labels:items.map(item => item.type),datasets:[{data:items.map(item => useEngagement ? item.engagement : item.posts),backgroundColor:['#5b2b76','#ef6c22','#7da533','#117e8c','#c94f0c','#1877f2'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'62%',plugins:{legend:{position:'right',labels:{usePointStyle:true}}}}});
    }

    function renderAudience(data) {
        const audienceSection = document.getElementById('meta-audience');
        const showAudience = currentScope !== 'facebook' && Boolean(analytics.platforms.instagram.connected);
        audienceSection.hidden = !showAudience;
        if (!showAudience) return;

        const audience = data.audience || {age_gender:[],cities:[],countries:[]};
        const status = data.audience_status || {};
        const instagramStatus = status.instagram || (currentScope === 'instagram' ? status : null);
        let unavailableMessage = 'Meta aún no liberó datos demográficos para esta audiencia.';

        if (instagramStatus?.permission === false) {
            unavailableMessage = 'Falta conceder el permiso instagram_manage_insights. Vuelve a conectar Facebook y acepta todos los permisos.';
        } else if (instagramStatus?.followers !== null && instagramStatus?.followers !== undefined && Number(instagramStatus.followers) < Number(instagramStatus.minimum_followers || 100)) {
            unavailableMessage = `La cuenta de Instagram tiene ${number(instagramStatus.followers)} seguidores. Meta exige al menos ${number(instagramStatus.minimum_followers || 100)} para habilitar datos demográficos.`;
        } else if ((analytics.errors || []).some(error => String(error.scope || '').startsWith('audience_'))) {
            unavailableMessage = 'Meta rechazó la consulta demográfica. Revisa el detalle técnico mostrado debajo de las analíticas.';
        }

        setEmpty('age', audience.age_gender.length === 0);
        const ageEmpty = root.querySelector('[data-empty="age"]');
        if (ageEmpty && audience.age_gender.length === 0) ageEmpty.textContent = unavailableMessage;
        chart('age','meta-age-chart',{type:'bar',data:{labels:audience.age_gender.map(item => item.name),datasets:[{label:'Audiencia',data:audience.age_gender.map(item => item.value),backgroundColor:'#5b2b76'}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true},y:{grid:{display:false}}}}});
        renderRanking('meta-cities', audience.cities, unavailableMessage);
        renderRanking('meta-countries', audience.countries, unavailableMessage);
    }

    function renderRanking(id, items, emptyMessage = 'Datos no disponibles') {
        const node = document.getElementById(id);
        if (!items?.length) { node.innerHTML = `<div class="meta-empty-inline">${esc(emptyMessage)}</div>`; return; }
        const total = items.reduce((sum,item)=>sum+Number(item.value || 0),0);
        const max = Math.max(...items.map(item=>Number(item.value || 0)),1);
        node.innerHTML = items.slice(0,10).map(item => `<div class="meta-rank"><strong>${esc(item.name)}</strong><small>${number(item.value)} · ${total > 0 ? number(item.value/total*100)+'%' : ''}</small><div><span style="width:${Number(item.value || 0)/max*100}%"></span></div></div>`).join('');
    }

    function renderPosts(data) {
        const posts = data.top_posts || [];
        document.getElementById('meta-top-posts').innerHTML = posts.length ? `<div class="meta-post-list">${posts.map(post => `<article class="meta-post">${post.thumbnail ? `<a class="meta-post-media" href="${esc(post.permalink || '#')}" target="_blank" rel="noopener"><img src="${esc(post.thumbnail)}" alt="Publicación"></a>` : '<div class="meta-post-media"><i class="fas fa-photo-film"></i></div>'}<div class="meta-post-body"><span class="meta-post-platform"><i class="fab fa-${post.platform}${post.platform === 'facebook' ? '-f' : ''}"></i>${esc(post.platform)} · ${esc(post.type)}</span><p>${esc(post.caption || 'Publicación sin texto')}</p><div class="meta-post-metrics"><span>♥ ${number(post.likes ?? post.reactions)}</span><span>💬 ${number(post.comments)}</span>${post.shares !== null ? `<span>↗ ${number(post.shares)}</span>` : ''}${post.saves !== null ? `<span>🔖 ${number(post.saves)}</span>` : ''}${post.views !== null ? `<span>◉ ${number(post.views)}</span>` : ''}</div></div></article>`).join('')}</div>` : '<div class="meta-empty-inline">No hay publicaciones reales disponibles para este periodo.</div>';
    }

    function renderErrors() {
        const notes = document.getElementById('meta-api-notes');
        notes.hidden = !analytics.errors?.length;
        if (analytics.errors?.length) notes.querySelector('div').innerHTML = analytics.errors.map(error => `<p><strong>${esc(error.platform)}</strong> · <code>${esc(error.scope)}</code>: ${esc(error.message)}</p>`).join('');
    }

    function render() {
        const data = scopeData();
        root.classList.toggle('is-audience', currentScope === 'audience');
        root.querySelectorAll('[data-analytics-scope]').forEach(button => button.classList.toggle('is-active', button.dataset.analyticsScope === currentScope));
        renderAccounts(); renderKpis(data); renderFollowers(data); renderEngagement(data); renderBestTimes(data); renderContent(data); renderAudience(data); renderPosts(data); renderErrors();
    }

    async function load(force = false) {
        const days = period.value || '{{ $defaultAnalyticsDays ?? '30' }}';
        if (!force && analytics && loadedDays === days) { content.hidden = false; render(); state.hidden = true; return; }
        const currentLoad = ++loadSequence;
        state.hidden = false;
        content.hidden = !analytics;
        state.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i><strong>Consultando Meta Insights</strong><span>Esto puede tardar unos segundos.</span>';
        try {
            const request = endpoint => {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('days', days);
                return fetch(url.toString(), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            };
            let response = await request(root.dataset.endpoint);
            if (response.status === 404 && root.dataset.fallbackEndpoint) {
                response = await request(root.dataset.fallbackEndpoint);
            }
            if (!response.ok) throw new Error(`Solicitud rechazada (${response.status})`);
            const responseAnalytics = await response.json();
            if (currentLoad !== loadSequence) return;
            analytics = responseAnalytics; loadedDays = days;
            window.hydrateRealEngagementCard?.(analytics);
            const connected = Object.values(analytics.platforms || {}).some(item => item.connected);
            if (!connected) {
                state.innerHTML = `<i class="fas fa-link"></i><strong>${esc(root.dataset.emptyMessage)}</strong><span>${esc(root.dataset.emptyDetail)}</span>`;
                return;
            }
            content.hidden = false;
            render();
            state.hidden = true;
        } catch (error) {
            if (currentLoad !== loadSequence) return;
            state.innerHTML = `<i class="fas fa-triangle-exclamation"></i><strong>No se pudieron cargar las analíticas</strong><span>${esc(error.message)}</span>`;
        }
    }

    root.querySelectorAll('[data-analytics-scope]').forEach(button => button.addEventListener('click', () => { if (button.disabled) return; currentScope = button.dataset.analyticsScope; render(); }));
    period?.addEventListener('change', () => load(true));
    window.loadCampaignAnalytics = () => load(false);
    window.reloadMetaAnalytics = (endpoint, fallbackEndpoint = '') => {
        root.dataset.endpoint = endpoint;
        root.dataset.fallbackEndpoint = fallbackEndpoint;
        analytics = null;
        loadedDays = null;
        currentScope = 'summary';
        load(true);
    };
    if (window.location.hash === '#analiticas') load(false);
})();
</script>
