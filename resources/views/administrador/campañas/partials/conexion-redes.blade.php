@php
    $facebookAccount = $campaignSocialAccounts->get('facebook');
    $facebookPage = $campaignSocialAccounts->get('facebook_page');
    $instagramAccount = $campaignSocialAccounts->get('instagram');
    $facebookLinked = filled($facebookAccount?->provider_user_id) || filled($facebookPage?->provider_user_id);
    $instagramLinked = filled($instagramAccount?->provider_user_id);
    $facebookName = $facebookPage?->display_name
        ?? data_get($facebookPage?->metadata, 'page_name')
        ?? $facebookAccount?->display_name
        ?? $facebookAccount?->username
        ?? $facebookAccount?->provider_user_id;
    $instagramName = $instagramAccount?->display_name
        ?? $instagramAccount?->username
        ?? $instagramAccount?->provider_user_id;
    $connectionParameters = [
        'empresa_id' => $empresa?->id,
        'return_to' => 'admin_campaign',
        'campania_id' => $campania->id,
    ];
@endphp

<section class="admin-social-connections">
    <header>
        <span><i class="fas fa-share-nodes"></i></span>
        <div>
            <small>CANALES DE LA EMPRESA</small>
            <h2>Conectar redes sociales</h2>
            <p>Las cuentas que autorices quedarán vinculadas exclusivamente a <strong>{{ $empresa?->nombre_empresa ?? 'esta empresa' }}</strong>.</p>
        </div>
        <div class="admin-social-status {{ $facebookLinked || $instagramLinked ? 'is-connected' : '' }}">
            <i class="fas {{ $facebookLinked || $instagramLinked ? 'fa-circle-check' : 'fa-link-slash' }}"></i>
            <span><small>Estado</small><strong>{{ $facebookLinked || $instagramLinked ? 'Conectado' : 'No conectado' }}</strong></span>
        </div>
    </header>

    @if(session('social_accounts_success'))
        <div class="admin-social-notice is-success"><i class="fas fa-circle-check"></i><span>{{ session('social_accounts_success') }}</span></div>
    @endif
    @if(session('social_accounts_error'))
        <div class="admin-social-notice is-error"><i class="fas fa-circle-exclamation"></i><span>{{ session('social_accounts_error') }}</span></div>
    @endif

    @if($empresa)
        <div class="admin-social-options">
            <a class="admin-social-option facebook {{ $facebookLinked ? 'is-linked' : '' }}" href="{{ route('clientes.social.redirect', ['provider' => 'facebook'] + $connectionParameters) }}">
                <div class="admin-social-option-top"><span><i class="fab fa-facebook-f"></i></span><b>{{ $facebookLinked ? 'Vinculado' : 'Disponible' }}</b></div>
                <h3>Facebook</h3>
                <p>Autoriza la página de Facebook que corresponde a esta empresa.</p>
                @if($facebookLinked)
                    <aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $facebookName ?: 'Página de Facebook' }}</strong></span></aside>
                @endif
                <em>{{ $facebookLinked ? 'Volver a conectar' : 'Conectar con Facebook' }} <i class="fas fa-arrow-right"></i></em>
            </a>

            <a class="admin-social-option instagram {{ $instagramLinked ? 'is-linked' : '' }} {{ ! $facebookLinked ? 'is-disabled' : '' }}"
                href="{{ $facebookLinked ? route('clientes.social.redirect', ['provider' => 'instagram'] + $connectionParameters) : '#' }}"
                aria-disabled="{{ $facebookLinked ? 'false' : 'true' }}">
                <div class="admin-social-option-top"><span><i class="fab fa-instagram"></i></span><b>{{ $instagramLinked ? 'Vinculado' : ($facebookLinked ? 'Disponible' : 'Bloqueado') }}</b></div>
                <h3>Instagram</h3>
                <p>Sincroniza el perfil profesional asociado a la página de Facebook autorizada.</p>
                @if($instagramLinked)
                    <aside><i class="fas fa-circle-check"></i><span><small>Cuenta vinculada</small><strong>{{ $instagramName ?: 'Cuenta de Instagram' }}</strong></span></aside>
                @endif
                <em>{{ $instagramLinked ? 'Volver a sincronizar' : ($facebookLinked ? 'Conectar con Instagram' : 'Primero conecta Facebook') }} @if($facebookLinked)<i class="fas fa-arrow-right"></i>@endif</em>
            </a>
        </div>
        <p class="admin-social-help"><i class="fas fa-circle-info"></i> Instagram profesional se obtiene desde la página de Facebook. Debes conectar Facebook primero y aceptar los permisos solicitados por Meta.</p>
    @else
        <div class="admin-social-missing"><i class="fas fa-building-circle-exclamation"></i><strong>La campaña no tiene una empresa asociada.</strong></div>
    @endif
</section>

<style>
.admin-social-connections{width:min(1280px,calc(100% - 48px));margin:16px auto;padding:20px;border:1px solid #ded7e1;border-radius:5px;background:#fff;box-shadow:0 10px 28px rgba(48,40,52,.07);color:#302834}.admin-social-connections>header{display:flex;align-items:center;gap:13px;padding-bottom:16px;border-bottom:1px solid #ebe6ed}.admin-social-connections>header>span{width:42px;height:42px;display:grid;place-items:center;flex:0 0 auto;border-radius:4px;background:#117e8c;color:#fff}.admin-social-connections>header>div:nth-child(2){min-width:0;flex:1}.admin-social-connections header small,.admin-social-connections header h2,.admin-social-connections header p{display:block}.admin-social-connections header small{color:#117e8c;font-size:.57rem;font-weight:900;letter-spacing:.11em}.admin-social-connections header h2{margin:3px 0;color:#302834;font-size:1rem;font-weight:900}.admin-social-connections header p{margin:0;color:#7d7580;font-size:.67rem}.admin-social-status{display:flex;align-items:center;gap:9px;padding:10px 13px;border:1px solid #ead5d5;border-radius:4px;background:#fff6f6;color:#b64a4a}.admin-social-status.is-connected{border-color:#cdddaf;background:#f3f7eb;color:#587923}.admin-social-status>span small,.admin-social-status>span strong{display:block}.admin-social-status small{font-size:.52rem!important;color:inherit!important}.admin-social-status strong{font-size:.68rem}.admin-social-notice{display:flex;align-items:center;gap:9px;margin-top:14px;padding:12px 14px;border-left:4px solid #7da533;background:#f3f7eb;color:#587923;font-size:.68rem;font-weight:800}.admin-social-notice.is-error{border-color:#ef6c22;background:#fff4ed;color:#a54b18}.admin-social-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}.admin-social-option{position:relative;display:flex;min-height:210px;flex-direction:column;padding:17px;border:1px solid #ddd7e0;border-radius:5px;background:#fff;color:#302834;text-decoration:none;transition:.18s}.admin-social-option:hover{border-color:#117e8c;box-shadow:0 10px 24px rgba(17,126,140,.12);transform:translateY(-2px)}.admin-social-option.is-linked{border-color:#bace94;background:#fbfdf8}.admin-social-option.is-disabled{pointer-events:none;opacity:.55;background:#f5f3f6}.admin-social-option-top{display:flex;align-items:center;justify-content:space-between}.admin-social-option-top>span{width:39px;height:39px;display:grid;place-items:center;border-radius:4px;color:#fff;font-size:1rem}.admin-social-option.facebook .admin-social-option-top>span{background:#1877f2}.admin-social-option.instagram .admin-social-option-top>span{background:linear-gradient(135deg,#f58529,#dd2a7b,#8134af)}.admin-social-option-top b{padding:5px 8px;border-radius:999px;background:#f0edf2;color:#716777;font-size:.53rem;text-transform:uppercase}.admin-social-option.is-linked .admin-social-option-top b{background:#eaf3da;color:#587923}.admin-social-option h3{margin:13px 0 5px;font-size:.9rem;font-weight:900}.admin-social-option>p{margin:0;color:#776e7b;font-size:.65rem;line-height:1.5}.admin-social-option aside{display:flex;align-items:center;gap:8px;margin-top:12px;padding:9px;border:1px solid #dce8c7;border-radius:4px;background:#fff;color:#587923}.admin-social-option aside span small,.admin-social-option aside span strong{display:block}.admin-social-option aside small{font-size:.5rem}.admin-social-option aside strong{max-width:360px;overflow:hidden;font-size:.62rem;text-overflow:ellipsis;white-space:nowrap}.admin-social-option em{display:flex;align-items:center;gap:6px;margin-top:auto;padding-top:14px;color:#5b2b76;font-size:.63rem;font-style:normal;font-weight:900}.admin-social-help{display:flex;align-items:flex-start;gap:8px;margin:13px 0 0;color:#7b727f;font-size:.61rem;line-height:1.5}.admin-social-help i{margin-top:2px;color:#117e8c}.admin-social-missing{display:flex;align-items:center;gap:9px;margin-top:16px;padding:16px;background:#fff4ed;color:#a54b18;font-size:.7rem}@media(max-width:720px){.admin-social-connections{width:calc(100% - 28px)}.admin-social-connections>header{align-items:flex-start;flex-wrap:wrap}.admin-social-status{width:100%}.admin-social-options{grid-template-columns:1fr}}
</style>
