<section class="plan-live-preview-shell">
    <div class="plan-live-preview-heading">
        <div>
            <span><i class="fas fa-eye"></i> Vista en clientes</span>
            <h2>Así se verá el plan</h2>
        </div>
        <small>Vista previa en vivo</small>
    </div>

    <article class="plan-live-card">
        <div class="plan-live-summary">
            <div class="plan-live-symbol-row">
                <span class="plan-live-symbol" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <span id="plan-summary-status" class="plan-live-status {{ $previewActive ? 'is-active' : 'is-inactive' }}">
                    <i class="fas {{ $previewActive ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                    {{ $previewActive ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            <span class="plan-live-index">Vista previa</span>
            <h3 id="plan-summary-name">{{ $previewName ?: 'Sin nombre' }}</h3>
            <p id="plan-summary-subtitle" class="plan-live-subtitle">{{ $previewSubtitle ?: 'Agrega un subtítulo corto para este plan.' }}</p>

            <div class="plan-live-price-row">
                <strong id="plan-summary-price">{{ $previewPrice }}</strong>
                <span id="plan-summary-period">/ {{ $previewPeriod }}</span>
            </div>

            <p class="plan-live-helper"><i class="fas fa-building"></i> Incluye el registro de una nueva empresa.</p>
        </div>

        <div class="plan-live-benefits">
            <span class="plan-live-benefits-title">Este plan incluye</span>
            <div id="feature-summary-list" class="plan-live-features">
                @forelse($previewFeatures as $feature)
                    <button type="button"
                        class="open-feature-edit-modal plan-live-feature"
                        data-feature-id="{{ $feature->id }}"
                        data-feature-name="{{ $feature->nombre }}"
                        title="Editar característica {{ $feature->nombre }}">
                        <i class="fas fa-check"></i>
                        <span>{{ $feature->nombre }}</span>
                        <i class="fas fa-pen"></i>
                    </button>
                @empty
                    <span class="plan-live-empty">Agrega al menos una característica.</span>
                @endforelse
            </div>
        </div>
    </article>
</section>

<style>
    .plan-live-preview-shell{overflow:hidden;border:1px solid #e3e0e5;border-top:4px solid #7da533;border-radius:16px;background:#fff;box-shadow:0 10px 28px rgba(48,40,52,.07)}.plan-live-preview-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;border-bottom:1px solid #ebe8ed;background:linear-gradient(135deg,#fff,#faf9fb)}.plan-live-preview-heading span{display:flex;align-items:center;gap:7px;color:#117e8c;font-size:.65rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}.plan-live-preview-heading h2{margin-top:5px;color:#302834;font-size:1.08rem;font-weight:900}.plan-live-preview-heading small{padding:5px 8px;border-radius:999px;background:#edf7f8;color:#0d6975;font-size:.58rem;font-weight:800;white-space:nowrap}.plan-live-card{--live-color:#ef6c22;overflow:hidden;border-top:5px solid var(--live-color);background:#fff}.plan-live-summary{padding:23px 22px 20px}.plan-live-symbol-row{display:flex;align-items:center;justify-content:space-between;min-height:39px;gap:10px}.plan-live-symbol{width:39px;height:39px;display:grid;grid-template:1fr 1fr/1fr 1fr;transform:rotate(45deg)}.plan-live-symbol i:nth-child(1){border-radius:100% 0 0;background:var(--live-color)}.plan-live-symbol i:nth-child(2){border-radius:0 100% 0 0;background:#f5a900}.plan-live-symbol i:nth-child(3){border-radius:0 0 0 100%;background:#117e8c}.plan-live-symbol i:nth-child(4){border-radius:0 0 100% 0;background:#7da533}.plan-live-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border:1px solid;border-radius:999px;font-size:.58rem;font-weight:900;text-transform:uppercase}.plan-live-status.is-active{border-color:#bfe3c5;background:#ecf8ee;color:#276738}.plan-live-status.is-inactive{border-color:#d7d9dc;background:#f3f4f6;color:#62685f}.plan-live-index{display:block;margin-top:19px;color:var(--live-color);font-size:.61rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.plan-live-summary h3{margin:6px 0 0;color:#302834;font-size:1.45rem;font-weight:900;line-height:1.15}.plan-live-subtitle{min-height:39px;margin-top:7px;color:#756a7a;font-size:.76rem;line-height:1.5}.plan-live-price-row{display:flex;align-items:flex-end;flex-wrap:wrap;gap:7px;margin-top:18px}.plan-live-price-row strong{color:#302834;font-size:1.75rem;font-weight:900;line-height:1}.plan-live-price-row>span{color:#887d8c;font-size:.68rem}.plan-live-helper{margin-top:11px;color:#756a7a;font-size:.67rem}.plan-live-helper i{margin-right:5px;color:#7da533}.plan-live-benefits{padding:19px 22px 22px;border-top:1px solid #e5dfe7;background:#f7f5f8}.plan-live-benefits-title{display:block;margin-bottom:12px;color:#514557;font-size:.64rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.plan-live-features{display:grid;gap:8px}.plan-live-feature{width:100%;display:grid;grid-template-columns:18px minmax(0,1fr) 15px;gap:7px;align-items:center;color:#5f5563;text-align:left;font-size:.72rem;line-height:1.4}.plan-live-feature>i:first-child{width:17px;height:17px;display:grid;place-items:center;border-radius:50%;background:#7da533;color:#fff;font-size:.5rem}.plan-live-feature>i:last-child{color:#aaa1ad;font-size:.58rem;opacity:0;transition:.18s}.plan-live-feature:hover>i:last-child{opacity:1;color:#117e8c}.plan-live-empty{padding:12px;border:1px dashed #d5cfd7;border-radius:8px;color:#918794;text-align:center;font-size:.7rem}
    .plan-live-feature small{display:block;margin-top:2px;color:#8b818f;font-size:.61rem}
</style>
