<section class="campaign-documents-workspace">
    @if($empresa)
        <article class="campaign-document-card">
            <div class="campaign-document-icon is-questionnaire"><i class="fas fa-clipboard-list"></i></div>
            <div class="campaign-document-copy">
                <span>Documento base</span>
                <h2>Cuestionario empresarial</h2>
                <p>{{ $empresa->cuestionario_completado ? 'Las respuestas empresariales están disponibles para consulta y generación de documentos.' : 'El cuestionario empresarial todavía está pendiente.' }}</p>
                <small><i class="fas {{ $empresa->cuestionario_completado ? 'fa-circle-check' : 'fa-clock' }}"></i> {{ $empresa->cuestionario_completado ? 'Completado' : 'Pendiente' }}</small>
            </div>
            <div class="campaign-document-actions">
                <a href="{{ route('administrador.empresas.cuestionario.show', $empresa->id) }}" class="is-primary"><i class="fas fa-eye"></i> Ver cuestionario</a>
                @if($empresa->cuestionario_completado)<a href="{{ route('administrador.empresas.cuestionario.pdf', $empresa->id) }}"><i class="fas fa-file-pdf"></i> PDF</a>@endif
            </div>
        </article>

        <article class="campaign-document-card">
            <div class="campaign-document-icon is-brief"><i class="fas fa-file-lines"></i></div>
            <div class="campaign-document-copy">
                <span>Documento estratégico</span>
                <h2>Brief / Resumen ejecutivo</h2>
                <p>{{ $empresa->resumen_ejecutivo ? \Illuminate\Support\Str::limit(trim(strip_tags($empresa->resumen_ejecutivo)), 220) : 'El brief ejecutivo todavía no ha sido generado para esta empresa.' }}</p>
                <small><i class="fas {{ $empresa->resumen_ejecutivo ? 'fa-circle-check' : 'fa-clock' }}"></i> {{ $empresa->resumen_ejecutivo ? 'Generado' : 'Pendiente' }}</small>
            </div>
            <div class="campaign-document-actions">
                @if($empresa->resumen_ejecutivo)
                    <a href="{{ route('administrador.empresas.reporte', $empresa->id) }}" class="is-primary"><i class="fas fa-eye"></i> Ver brief completo</a>
                    <a href="{{ route('administrador.empresas.reporte.pdf', $empresa->id) }}"><i class="fas fa-file-pdf"></i> PDF</a>
                @else
                    <a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="is-primary"><i class="fas fa-building"></i> Ver empresa</a>
                @endif
            </div>
        </article>

        <article class="campaign-document-card is-marketing">
            <div class="campaign-document-icon is-plan"><i class="fas fa-bullseye"></i></div>
            <div class="campaign-document-copy">
                <span>Documento operativo</span>
                <h2>Plan de marketing</h2>
                <p>{{ $empresa->planesMarketing->isNotEmpty() ? $empresa->planesMarketing->count().' '.($empresa->planesMarketing->count() === 1 ? 'plan generado' : 'planes generados').' para esta empresa.' : 'Todavía no existe un plan de marketing generado.' }}</p>
                <small><i class="fas {{ $empresa->planesMarketing->isNotEmpty() ? 'fa-circle-check' : 'fa-clock' }}"></i> {{ $empresa->planesMarketing->isNotEmpty() ? 'Disponible' : 'Pendiente' }}</small>
            </div>
            @if($empresa->planesMarketing->isNotEmpty())
                <div class="campaign-plan-list">
                    @foreach($empresa->planesMarketing as $plan)
                        <div>
                            <span><strong>{{ $plan->suscripcion?->plan?->nombre ?? 'Plan de marketing' }}</strong><small>Generado el {{ $plan->created_at->format('d/m/Y H:i') }}</small></span>
                            <span class="campaign-document-actions"><a href="{{ route('administrador.empresas.planes-marketing.show', $plan) }}" class="is-primary"><i class="fas fa-eye"></i> Ver plan</a><a href="{{ route('administrador.empresas.planes-marketing.download-pdf', $plan) }}"><i class="fas fa-file-pdf"></i> PDF</a></span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="campaign-document-actions"><a href="{{ route('administrador.empresas.show', $empresa->id) }}" class="is-primary"><i class="fas fa-building"></i> Ver empresa</a></div>
            @endif
        </article>
    @else
        <div class="campaign-documents-empty"><i class="fas fa-folder-open"></i><h2>Sin empresa vinculada</h2><p>No se encontraron documentos empresariales para esta campaña.</p></div>
    @endif
</section>

<style>
    .campaign-documents-workspace{display:grid;gap:14px}.campaign-document-card{display:grid;grid-template-columns:54px minmax(0,1fr) auto;align-items:center;gap:16px;padding:18px;border:1px solid #eaded5;border-left:4px solid #c94f0c;border-radius:12px;background:#fff;box-shadow:0 5px 15px rgba(74,55,43,.05)}.campaign-document-icon{width:50px;height:50px;display:grid;place-items:center;border-radius:12px;background:#fff0e8;color:#c94f0c;font-size:1rem}.campaign-document-icon.is-brief{background:#f3edf6;color:#5b2b76}.campaign-document-icon.is-plan{background:#edf4e4;color:#638524}.campaign-document-copy>span{display:block;color:#a76642;font-size:.55rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}.campaign-document-copy h2{margin:3px 0 6px;color:#332d29;font-size:.92rem;font-weight:900}.campaign-document-copy p{max-width:760px;margin:0;color:#74706c;font-size:.65rem;font-weight:600;line-height:1.55}.campaign-document-copy>small{display:block;margin-top:8px;color:#638524;font-size:.57rem;font-weight:900}.campaign-document-actions{display:flex;align-items:center;flex-wrap:wrap;justify-content:flex-end;gap:6px}.campaign-document-actions a{min-height:34px;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:0 10px;border:1px solid #e3d9d2;border-radius:8px;background:#fff;color:#6b625d;font-size:.57rem;font-weight:900;text-decoration:none}.campaign-document-actions a.is-primary,.campaign-document-actions a:hover{border-color:#c94f0c;background:#c94f0c;color:#fff}.campaign-document-card.is-marketing{grid-template-columns:54px minmax(180px,.55fr) minmax(320px,1fr)}.campaign-plan-list{display:grid;gap:7px}.campaign-plan-list>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px;border:1px solid #e4e9df;border-radius:9px;background:#fafcf8}.campaign-plan-list>div>span:first-child strong,.campaign-plan-list>div>span:first-child small{display:block}.campaign-plan-list strong{color:#3e4937;font-size:.62rem}.campaign-plan-list small{margin-top:3px;color:#858d80;font-size:.52rem}.campaign-documents-empty{padding:50px 20px;border:1px dashed #dbcabb;border-radius:12px;background:#fffaf7;text-align:center}.campaign-documents-empty i{color:#c94f0c;font-size:1.7rem}.campaign-documents-empty h2{margin:10px 0 5px;font-size:.9rem}.campaign-documents-empty p{margin:0;color:#7d746e;font-size:.65rem}@media(max-width:850px){.campaign-document-card,.campaign-document-card.is-marketing{grid-template-columns:50px minmax(0,1fr)}.campaign-document-actions,.campaign-plan-list{grid-column:1/-1;justify-content:flex-start}.campaign-plan-list>div{align-items:flex-start;flex-direction:column}.campaign-plan-list .campaign-document-actions{width:100%}}@media(max-width:520px){.campaign-document-card,.campaign-document-card.is-marketing{grid-template-columns:1fr}.campaign-document-actions a{flex:1}.campaign-document-icon{width:42px;height:42px}}
</style>
