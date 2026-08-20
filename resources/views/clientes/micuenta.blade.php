@extends('layouts.app2')

@section('title', 'Mi Cuenta')

@section('content')
<div id="mi-cuenta-page" class="min-h-screen">
    <section class="cuenta-banner">
        <div class="cuenta-banner-content">
            <span class="cuenta-banner-kicker">Tu espacio personal</span>
            <h1>Mi <span>cuenta</span></h1>
            <p>Administra tus datos, consulta tu plan y revisa las empresas vinculadas a tu cuenta.</p>
        </div>
        <div class="cuenta-banner-side">
            <div class="cuenta-banner-total">
                <small>Empresas registradas</small>
                <strong>{{ $empresas->count() }}</strong>
            </div>
            <div class="cuenta-banner-mosaic" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-8 cuenta-content">
        @if(session('account_success'))
            <div class="cuenta-notice cuenta-notice-success"><i class="fas fa-circle-check"></i>{{ session('account_success') }}</div>
        @endif
        @if(session('password_link_sent'))
            <div class="cuenta-notice cuenta-notice-mail"><i class="fas fa-envelope-circle-check"></i>{{ session('password_link_sent') }}</div>
        @endif
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna izquierda - Datos del cliente -->
            <div class="lg:col-span-1">
                <div class="cuenta-panel overflow-hidden">
                    <!-- Header -->
                    <div class="px-8 py-6 cuenta-panel-header">
                        <h2 class="text-2xl font-bold">Mis Datos</h2>
                        <p class="text-indigo-100 mt-1">Información de tu cuenta</p>
                    </div>
                    
                    <!-- Contenido -->
                    <div class="p-8">
                        <div class="flex flex-col items-center mb-6">
                            <div class="w-24 h-24 cuenta-avatar rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">{{ Auth::user()->name }}</h3>
                            <p class="text-gray-600">{{ Auth::user()->email }}</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex-shrink-0 pt-1">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ Auth::user()->phone ?? 'No registrado' }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex-shrink-0 pt-1">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-500">Miembro desde</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <button type="button" id="open-account-edit" class="cuenta-button w-full inline-flex items-center justify-center px-4 py-3 text-white font-medium rounded-xl transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar datos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha - Plan contratado -->
            <div class="lg:col-span-2">
                <!-- Plan Contratado Section -->
                <div class="cuenta-panel overflow-hidden">
                    <div class="px-8 py-6 cuenta-panel-header">
                        <h2 class="text-2xl font-bold">Plan Contratado</h2>
                        <p class="text-indigo-100 mt-1">Detalles de tu suscripción actual</p>
                    </div>
                    
                    <div class="p-8">
                        <div class="cuenta-plan-container rounded-2xl" id="plan-contratado-container">
                            <div class="text-center py-16">
                                <div class="relative inline-block">
                                    <div class="w-20 h-20 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl mx-auto flex items-center justify-center animate-pulse shadow-xl">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 rounded-3xl bg-indigo-600 opacity-20 animate-ping"></div>
                                </div>
                                <p class="text-gray-700 mt-6 font-medium text-lg">Cargando información del plan...</p>
                                <p class="text-sm text-gray-500 mt-2">Esto tomará unos segundos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ======================================== -->
        <!-- SECCIÓN NUEVA PARA MOSTRAR EMPRESAS -->
        <!-- ======================================== -->
        <div class="mt-12">
            <div class="cuenta-panel overflow-hidden">
                <!-- Header -->
                <div class="px-8 py-6 cuenta-panel-header">
                    <h2 class="text-2xl font-bold flex items-center">
                        <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Mis Empresas
                    </h2>
                    <p class="text-indigo-100 mt-1">Gestiona la información de tus empresas</p>
                </div>

                <div class="p-8">
                    @if($empresas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($empresas as $empresa)
                                @php
                                    $companySocialAccounts = $empresa->relationLoaded('socialAccounts')
                                        ? $empresa->socialAccounts->keyBy('provider')
                                        : collect();
                                    $legacySocialAccounts = $loop->first ? $globalSocialAccounts : collect();
                                    $companyFacebookLinked = filled(($companySocialAccounts->get('facebook') ?? $legacySocialAccounts->get('facebook'))?->provider_user_id);
                                    $companyInstagramLinked = filled(($companySocialAccounts->get('instagram') ?? $legacySocialAccounts->get('instagram'))?->provider_user_id);
                                @endphp
                                <div class="company-social-card bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                    <button type="button" class="open-company-social-modal"
                                        data-company-id="{{ $empresa->id }}"
                                        data-company-name="{{ $empresa->nombre_empresa }}"
                                        data-facebook-linked="{{ $companyFacebookLinked ? '1' : '0' }}"
                                        data-instagram-linked="{{ $companyInstagramLinked ? '1' : '0' }}"
                                        data-facebook-url="{{ route('clientes.social.redirect', ['provider' => 'facebook', 'empresa_id' => $empresa->id]) }}"
                                        data-instagram-url="{{ route('clientes.social.redirect', ['provider' => 'instagram', 'empresa_id' => $empresa->id]) }}">
                                        <i class="fas fa-share-nodes"></i> Vincular redes
                                    </button>
                                    <div class="p-6">
                                        <div class="flex items-center space-x-4 mb-4">
                                            @if($empresa->logo)
                                                <div class="w-16 h-16 bg-white rounded-xl p-2 shadow-lg">
                                                    <img src="{{ Storage::url($empresa->logo) }}" alt="Logo de {{ $empresa->nombre_empresa }}" class="w-full h-full object-contain">
                                                </div>
                                            @else
                                                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                                                    {{ substr($empresa->nombre_empresa, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-900">{{ $empresa->nombre_empresa }}</h3>
                                                <p class="text-sm text-gray-600">{{ $empresa->tipo_empresa }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($empresa->descripcion)
                                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $empresa->descripcion }}</p>
                                        @else
                                            <p class="text-gray-400 text-sm mb-4 italic">Sin descripción</p>
                                        @endif
                                        

                                    </div>
                                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                                        <a href="{{ route('empresas.show', $empresa->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                            Ver detalles
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('clientes.planes.comprar') }}" class="cuenta-button group relative overflow-hidden inline-flex items-center px-6 py-3 text-white font-medium rounded-xl transition-all duration-300">
                                <div class="relative flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Agregar nueva empresa
                                </div>
                            </a>
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="relative inline-block">
                                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl mx-auto flex items-center justify-center mb-6 shadow-lg">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 rounded-3xl bg-gray-300 opacity-10 animate-pulse"></div>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aún no tienes empresas registradas</h3>
                            <p class="text-gray-600 mb-8 max-w-md mx-auto">Registra tu primera empresa para que podamos empezar a trabajar contigo.</p>
                            <a href="{{ route('clientes.planes.comprar') }}" class="cuenta-button group relative overflow-hidden inline-flex items-center px-8 py-3 text-white font-medium rounded-xl transition-all duration-300">
                                <div class="relative flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Crear mi primera empresa
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div id="account-edit-modal" class="account-edit-modal hidden" role="dialog" aria-modal="true" aria-labelledby="account-edit-title">
    <div class="account-edit-backdrop" data-close-account-modal></div>
    <div class="account-edit-dialog">
        <div class="account-edit-header">
            <div>
                <span>CONFIGURACIÓN PERSONAL</span>
                <h3 id="account-edit-title">Editar mis datos</h3>
            </div>
            <button type="button" data-close-account-modal aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>

        <div class="account-edit-body">
            @if($errors->any())
                <div class="account-form-errors">
                    <i class="fas fa-circle-exclamation"></i>
                    <div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
                </div>
            @endif

            <form method="POST" action="{{ route('clientes.micuenta.datos') }}" class="account-data-form">
                @csrf
                @method('PATCH')
                <div class="account-edit-field">
                    <label for="account-name">Nombre completo</label>
                    <div><input id="account-name" name="name" type="text" value="{{ old('name', Auth::user()->name) }}" required><i class="fas fa-pencil"></i></div>
                </div>
                <div class="account-edit-field">
                    <label for="account-phone">Teléfono</label>
                    <div><input id="account-phone" name="phone" type="tel" value="{{ old('phone', Auth::user()->phone) }}" placeholder="Ej. +591 70000000"><i class="fas fa-pencil"></i></div>
                </div>
                <div class="account-edit-field is-locked">
                    <label for="account-email">Correo electrónico</label>
                    <div><input id="account-email" type="email" value="{{ Auth::user()->email }}" readonly><i class="fas fa-lock"></i></div>
                    <small>El correo está protegido comunicate con un administrador para cambiarlo.</small>
                </div>
                <div class="account-edit-actions">
                    <button type="button" data-close-account-modal>Cancelar</button>
                    <button type="submit"><i class="fas fa-floppy-disk"></i> Guardar cambios</button>
                </div>
            </form>

            <section class="account-password-section">
                <div class="account-password-title">
                    <i class="fas fa-key"></i>
                    <div><h4>Cambiar contraseña</h4><p>Por seguridad, primero confirmaremos la solicitud desde tu correo.</p></div>
                </div>
                <form method="POST" action="{{ route('clientes.password.request') }}">
                    @csrf
                    <button type="submit"><i class="fas fa-paper-plane"></i> Enviar correo de confirmación</button>
                </form>
            </section>
        </div>
    </div>
</div>

<div id="company-social-modal" class="company-social-modal hidden" role="dialog" aria-modal="true" aria-labelledby="company-social-title">
    <div class="company-social-backdrop" data-close-company-social></div>
    <div class="company-social-dialog">
        <header class="company-social-header">
            <div class="company-social-header-icon"><i class="fas fa-share-nodes"></i></div>
            <div><span>CANALES DE TU EMPRESA</span><h3 id="company-social-title">Vincular redes sociales</h3></div>
            <button type="button" data-close-company-social aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </header>
        <div class="company-social-body">
            <div class="company-social-company"><i class="fas fa-building"></i><span>Configurarás las redes de <strong id="company-social-name"></strong></span></div>
            @if(session('social_accounts_success'))<div class="company-social-notice success"><i class="fas fa-circle-check"></i>{{ session('social_accounts_success') }}</div>@endif
            @if(session('social_accounts_error'))<div class="company-social-notice error"><i class="fas fa-circle-exclamation"></i>{{ session('social_accounts_error') }}</div>@endif
            <p class="company-social-intro">Conecta los perfiles que representan a esta empresa. Facebook debe vincularse antes de habilitar Instagram.</p>
            <div class="company-social-grid">
                <a href="#" id="company-facebook-option" class="company-social-option facebook">
                    <div><span class="company-social-platform-icon"><i class="fab fa-facebook-f"></i></span><span class="company-social-badge">Disponible</span></div>
                    <h4>Facebook</h4><p>Autoriza la página de esta empresa y permite que PRODOVI la reconozca.</p><strong>Conectar con Facebook <i class="fas fa-arrow-right"></i></strong>
                </a>
                <a href="#" id="company-instagram-option" class="company-social-option instagram is-disabled" aria-disabled="true">
                    <div><span class="company-social-platform-icon"><i class="fab fa-instagram"></i></span><span class="company-social-badge">Bloqueado</span></div>
                    <h4>Instagram</h4><p>Conecta el perfil de Instagram asociado al negocio después de Facebook.</p><strong>Esperando Facebook</strong>
                </a>
            </div>
        </div>
        <footer class="company-social-footer"><button type="button" data-close-company-social>Listo</button></footer>
    </div>
</div>

<style>
    #mi-cuenta-page {
        --cuenta-orange: #ee9f2b;
        --cuenta-purple: #5b2b76;
        --cuenta-turquoise: #117e8c;
        background: #ffffff;
        color: #302834;
    }
    #mi-cuenta-page .cuenta-banner {
        position: relative;
        min-height: 168px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        overflow: hidden;
        padding: 30px 34px;
        background: #242426;
        color: #ffffff;
    }
    #mi-cuenta-page .cuenta-banner-content {
        max-width: 720px;
    }
    #mi-cuenta-page .cuenta-banner-kicker {
        display: block;
        margin-bottom: 10px;
        color: var(--cuenta-orange);
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }
    #mi-cuenta-page .cuenta-banner h1 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(1.65rem, 3vw, 2.35rem);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -.035em;
    }
    #mi-cuenta-page .cuenta-banner h1 span { color: var(--cuenta-orange); }
    #mi-cuenta-page .cuenta-banner-content > p {
        margin-top: 11px;
        color: #aaa5ad;
        font-size: .86rem;
        line-height: 1.55;
    }
    #mi-cuenta-page .cuenta-banner-side { display: flex; align-items: center; gap: 26px; }
    #mi-cuenta-page .cuenta-banner-total {
        min-width: 112px;
        padding: 13px 16px;
        border-left: 4px solid var(--cuenta-orange);
        background: #303033;
    }
    #mi-cuenta-page .cuenta-banner-total small,
    #mi-cuenta-page .cuenta-banner-total strong { display: block; }
    #mi-cuenta-page .cuenta-banner-total small {
        color: #aaa5ad;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    #mi-cuenta-page .cuenta-banner-total strong {
        margin-top: 3px;
        color: #ffffff;
        font-size: 1.55rem;
        line-height: 1;
    }
    #mi-cuenta-page .cuenta-banner-mosaic {
        width: 144px;
        height: 96px;
        display: grid;
        flex: 0 0 auto;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
    }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(1) { background: #ef6c22; border-radius: 100% 0 0 0; }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(2) { background: #f5a900; border-radius: 0 0 0 100%; }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(3) { background: #5b2b76; border-radius: 100% 0 100% 0; }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(4) { background: #117e8c; border-radius: 0 100% 0 100%; }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(5) { background: #7da533; border-radius: 50%; }
    #mi-cuenta-page .cuenta-banner-mosaic span:nth-child(6) { border: 12px solid #607078; border-top-color: transparent; border-left-color: transparent; border-radius: 50%; transform: rotate(45deg); }
    #mi-cuenta-page .cuenta-content { max-width: 1440px; }
    #mi-cuenta-page .cuenta-notice { display:flex; align-items:center; gap:10px; margin-bottom:18px; padding:13px 16px; border-left:4px solid; font-size:.8rem; font-weight:800; }
    #mi-cuenta-page .cuenta-notice-success { border-color:#7da533; background:#f3f7eb; color:#587923; }
    #mi-cuenta-page .cuenta-notice-mail { border-color:#117e8c; background:#edf7f8; color:#0e6d78; }
    #mi-cuenta-page .cuenta-panel {
        height: 100%;
        border: 1px solid #ded7e1;
        border-radius: 5px;
        background: #ffffff;
        box-shadow: 0 10px 28px #ded9e0;
    }
    #mi-cuenta-page .cuenta-panel-header {
        border-left: 5px solid var(--cuenta-orange);
        border-bottom: 1px solid #ded7e1;
        background: #f7f5f8;
        color: #302834;
    }
    #mi-cuenta-page .cuenta-panel-header p { color: #817585; }
    #mi-cuenta-page .cuenta-avatar { background: var(--cuenta-purple); box-shadow: 0 8px 0 #432056; }
    #mi-cuenta-page .cuenta-button {
        border: 0;
        background: var(--cuenta-purple);
        box-shadow: 0 5px 0 #432056;
        text-decoration: none;
    }
    #mi-cuenta-page .cuenta-button:hover { background: #6a3488; transform: translateY(-1px); }
    #mi-cuenta-page .cuenta-plan-container { border: 1px solid #ded7e1; background: #faf9fb; }
    #mi-cuenta-page .text-indigo-600 { color: var(--cuenta-purple) !important; }
    #mi-cuenta-page .bg-gradient-to-br.from-indigo-500,
    #mi-cuenta-page .bg-gradient-to-r.from-indigo-600 { background: var(--cuenta-purple) !important; }
    #mi-cuenta-page .border-white\/40,
    #mi-cuenta-page .border-indigo-200\/50 { border-color: #ded7e1 !important; }
    #mi-cuenta-page .bg-white\/60 { background: #ffffff !important; }

    html[data-client-theme="dark"] #mi-cuenta-page { background: #141216; color: #e9e5eb; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-panel { border-color: #403943; background: #1e1b21; box-shadow: 0 10px 28px #0d0b0e; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-panel-header { border-bottom-color: #403943; background: #29252c; color: #f3eef5; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-panel-header p { color: #b4abb8; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-plan-container { border-color: #403943; background: #242127; }
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-900,
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-800,
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-700 { color: #f0ebf2 !important; }
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-600,
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-500,
    html[data-client-theme="dark"] #mi-cuenta-page .text-gray-400 { color: #b4abb8 !important; }
    html[data-client-theme="dark"] #mi-cuenta-page .border-gray-100,
    html[data-client-theme="dark"] #mi-cuenta-page .border-gray-200 { border-color: #403943 !important; }
    html[data-client-theme="dark"] #mi-cuenta-page .bg-white,
    html[data-client-theme="dark"] #mi-cuenta-page .bg-white\/60,
    html[data-client-theme="dark"] #mi-cuenta-page .bg-gray-50,
    html[data-client-theme="dark"] #mi-cuenta-page .from-white { background: #29252c !important; }
    html[data-client-theme="dark"] #mi-cuenta-page .hover\:bg-gray-50:hover { background: #29252c !important; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-notice-success { background:#28321f; color:#b5d17e; }
    html[data-client-theme="dark"] #mi-cuenta-page .cuenta-notice-mail { background:#173136; color:#78c3cb; }

    .account-edit-modal { position:fixed; z-index:2147483000; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .account-edit-modal.hidden { display:none; }
    .account-edit-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    .account-edit-dialog { position:relative; width:min(620px,100%); max-height:calc(100vh - 40px); overflow:hidden; border-radius:5px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.34); }
    .account-edit-header { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:21px 24px; border-bottom:5px solid #ee9f2b; background:#242426; color:#fff; }
    .account-edit-header span { display:block; margin-bottom:5px; color:#ee9f2b; font-size:.64rem; font-weight:900; letter-spacing:.12em; }
    .account-edit-header h3 { margin:0; font-size:1.2rem; font-weight:900; }
    .account-edit-header button { width:36px; height:36px; display:grid; place-items:center; border:1px solid #565259; border-radius:3px; background:#343436; color:#fff; cursor:pointer; }
    .account-edit-body { max-height:calc(100vh - 132px); overflow-y:auto; padding:24px; }
    .account-form-errors { display:flex; align-items:flex-start; gap:10px; margin-bottom:18px; padding:12px 14px; border-left:4px solid #b63b3b; background:#fff1f1; color:#9b2929; font-size:.76rem; }
    .account-form-errors p { margin:0 0 3px; }
    .account-data-form { display:grid; gap:16px; }
    .account-edit-field label { display:block; margin-bottom:6px; color:#665b6b; font-size:.68rem; font-weight:900; letter-spacing:.06em; text-transform:uppercase; }
    .account-edit-field > div { position:relative; }
    .account-edit-field input { width:100%; height:46px; padding:0 42px 0 13px; border:1px solid #d8cfdc; border-radius:4px; background:#fff; color:#302834; font-size:.84rem; outline:none; }
    .account-edit-field input:focus { border-color:#5b2b76; box-shadow:0 0 0 3px #eee6f2; }
    .account-edit-field > div > i { position:absolute; right:14px; top:50%; color:#5b2b76; font-size:.77rem; transform:translateY(-50%); pointer-events:none; }
    .account-edit-field.is-locked input { background:#f2eff3; color:#817585; cursor:not-allowed; }
    .account-edit-field.is-locked > div > i { color:#9b929e; }
    .account-edit-field small { display:block; margin-top:6px; color:#8c818f; font-size:.68rem; }
    .account-edit-actions { display:flex; justify-content:flex-end; gap:9px; padding-top:3px; }
    .account-edit-actions button { padding:10px 15px; border:1px solid #d8cfdc; border-radius:4px; background:#fff; color:#665b6b; font-size:.76rem; font-weight:900; cursor:pointer; }
    .account-edit-actions button[type="submit"] { border-color:#5b2b76; background:#5b2b76; color:#fff; }
    .account-password-section { margin-top:24px; padding-top:22px; border-top:1px solid #ded7e1; }
    .account-password-title { display:flex; align-items:flex-start; gap:12px; }
    .account-password-title > i { width:38px; height:38px; display:grid; place-items:center; flex:0 0 auto; border-radius:50%; background:#fff5e6; color:#b56e09; }
    .account-password-title h4 { margin:0; color:#302834; font-size:.92rem; font-weight:900; }
    .account-password-title p { margin:4px 0 0; color:#817585; font-size:.74rem; line-height:1.5; }
    .account-password-section form { margin-top:14px; }
    .account-password-section button { width:100%; padding:11px 15px; border:1px solid #ee9f2b; border-radius:4px; background:#ee9f2b; color:#242426; font-size:.76rem; font-weight:900; cursor:pointer; }
    html[data-client-theme="dark"] .account-edit-dialog { background:#1e1b21; color:#e9e5eb; }
    html[data-client-theme="dark"] .account-edit-field label { color:#b4abb8; }
    html[data-client-theme="dark"] .account-edit-field input { border-color:#4a434e; background:#29252c; color:#f1edf3; }
    html[data-client-theme="dark"] .account-edit-field input:focus { border-color:#9b62b5; box-shadow:0 0 0 3px #392b40; }
    html[data-client-theme="dark"] .account-edit-field.is-locked input { background:#252127; color:#928997; }
    html[data-client-theme="dark"] .account-edit-actions button { border-color:#4a434e; background:#29252c; color:#d9d2dc; }
    html[data-client-theme="dark"] .account-edit-actions button[type="submit"] { border-color:#754391; background:#754391; color:#fff; }
    html[data-client-theme="dark"] .account-password-section { border-color:#403943; }
    html[data-client-theme="dark"] .account-password-title h4 { color:#f1edf3; }
    html[data-client-theme="dark"] .account-password-title p,
    html[data-client-theme="dark"] .account-edit-field small { color:#aaa1ae; }

    .company-social-card { position:relative; }
    .company-social-card > .p-6 { padding-top:4.4rem; }
    .open-company-social-modal { position:absolute; z-index:2; top:14px; right:14px; display:inline-flex; align-items:center; gap:7px; padding:8px 10px; border:1px solid #c9e1e4; border-radius:3px; background:#edf7f8; color:#117e8c; font-size:.68rem; font-weight:900; cursor:pointer; transition:.2s ease; }
    .open-company-social-modal:hover { border-color:#117e8c; background:#117e8c; color:#fff; transform:translateY(-1px); }
    .company-social-modal { position:fixed; z-index:2147483001; inset:0; display:flex; align-items:center; justify-content:center; padding:20px; }
    .company-social-modal.hidden { display:none; }
    .company-social-backdrop { position:absolute; inset:0; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    .company-social-dialog { position:relative; width:min(690px,100%); max-height:calc(100vh - 40px); display:flex; flex-direction:column; overflow:hidden; border-radius:5px; background:#fff; box-shadow:0 28px 80px rgba(0,0,0,.38); }
    .company-social-header { display:flex; align-items:center; gap:13px; padding:20px 22px; border-bottom:5px solid #117e8c; background:#242426; color:#fff; }
    .company-social-header-icon { width:40px; height:40px; display:grid; place-items:center; flex:0 0 auto; border-radius:3px; background:#117e8c; }
    .company-social-header > div:nth-child(2) { flex:1; }
    .company-social-header span { display:block; color:#76c5ce; font-size:.6rem; font-weight:900; letter-spacing:.12em; }
    .company-social-header h3 { margin:4px 0 0; font-size:1.15rem; font-weight:900; }
    .company-social-header > button { width:36px; height:36px; display:grid; place-items:center; border:1px solid #565259; border-radius:3px; background:#343436; color:#fff; cursor:pointer; }
    .company-social-body { overflow-y:auto; padding:22px; }
    .company-social-company { display:flex; align-items:center; gap:10px; margin-bottom:15px; padding:11px 13px; border-left:4px solid #ee9f2b; background:#fff5e6; color:#70572f; font-size:.75rem; }
    .company-social-company > i { color:#ee9f2b; }
    .company-social-intro { margin:0 0 17px; color:#756a7a; font-size:.78rem; line-height:1.55; }
    .company-social-notice { display:flex; align-items:flex-start; gap:9px; margin-bottom:14px; padding:11px 13px; border-left:4px solid; font-size:.73rem; font-weight:800; }
    .company-social-notice.success { border-color:#7da533; background:#f3f7eb; color:#587923; }
    .company-social-notice.error { border-color:#b63b3b; background:#fff1f1; color:#9b2929; }
    .company-social-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:13px; }
    .company-social-option { min-width:0; display:flex; flex-direction:column; padding:18px; border:1px solid #ded7e1; border-top:4px solid #1877f2; border-radius:4px; background:#fff; color:#302834; text-decoration:none; transition:.2s ease; }
    .company-social-option.instagram { border-top-color:#d62976; }
    .company-social-option:hover { transform:translateY(-3px); box-shadow:0 12px 25px #ded9e0; }
    .company-social-option > div { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .company-social-platform-icon { width:38px; height:38px; display:grid; place-items:center; border-radius:50%; background:#1877f2; color:#fff; }
    .company-social-option.instagram .company-social-platform-icon { background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045); }
    .company-social-badge { padding:5px 7px; border-radius:2px; background:#edf7f8; color:#117e8c; font-size:.58rem; font-weight:900; text-transform:uppercase; }
    .company-social-option h4 { margin:14px 0 0; font-size:.96rem; font-weight:900; }
    .company-social-option p { flex:1; margin:6px 0 14px; color:#756a7a; font-size:.7rem; line-height:1.5; }
    .company-social-option > strong { color:#5b2b76; font-size:.68rem; font-weight:900; }
    .company-social-option.is-linked { border-color:#7da533; border-top-color:#7da533; background:#f7faF1; }
    .company-social-option.is-linked .company-social-badge { background:#eaf3da; color:#587923; }
    .company-social-option.is-disabled { border-color:#ddd8df; background:#f4f2f5; opacity:.62; cursor:not-allowed; transform:none; box-shadow:none; }
    .company-social-footer { display:flex; justify-content:flex-end; padding:14px 22px; border-top:1px solid #ded7e1; background:#f7f5f8; }
    .company-social-footer button { padding:10px 18px; border:1px solid #5b2b76; border-radius:3px; background:#5b2b76; color:#fff; font-size:.74rem; font-weight:900; cursor:pointer; }
    html[data-client-theme="dark"] .open-company-social-modal { border-color:#376b72; background:#173136; color:#78c3cb; }
    html[data-client-theme="dark"] .company-social-dialog { background:#1e1b21; color:#e9e5eb; }
    html[data-client-theme="dark"] .company-social-company { background:#3a3020; color:#efcf9e; }
    html[data-client-theme="dark"] .company-social-intro { color:#b4abb8; }
    html[data-client-theme="dark"] .company-social-option { border-color:#403943; background:#29252c; color:#f1edf3; }
    html[data-client-theme="dark"] .company-social-option p { color:#b4abb8; }
    html[data-client-theme="dark"] .company-social-option.is-linked { border-color:#627f2f; background:#28321f; }
    html[data-client-theme="dark"] .company-social-option.is-disabled { border-color:#403943; background:#242127; }
    html[data-client-theme="dark"] .company-social-footer { border-color:#403943; background:#29252c; }

    /* Detalles del plan */
    #plan-modal { position:fixed !important; z-index:2147483000 !important; inset:0 !important; display:flex; align-items:center; justify-content:center; overflow:auto; padding:20px; }
    #plan-modal.hidden { display:none !important; }
    #plan-modal > div { min-height:0 !important; display:contents !important; padding:0 !important; text-align:left !important; }
    #plan-modal > div > div:first-child { position:fixed !important; z-index:0; inset:0 !important; background:rgba(18,14,20,.76); backdrop-filter:blur(5px); }
    #plan-modal > div > div:first-child > div { position:absolute; inset:0; background:transparent !important; }
    #plan-modal > div > div:nth-child(2) { position:relative; z-index:1; width:min(720px,100%) !important; max-width:none !important; max-height:calc(100vh - 40px); display:flex !important; flex-direction:column; overflow:hidden !important; margin:0 !important; border:1px solid #ded7e1 !important; border-radius:5px !important; background:#fff !important; box-shadow:0 28px 80px rgba(0,0,0,.38) !important; transform:none !important; }
    #plan-modal > div > div:nth-child(2) > div:first-child { flex:0 0 auto; padding:20px 24px !important; border-bottom:5px solid #ee9f2b; background:#242426 !important; }
    #plan-modal #modal-plan-title { margin:0; color:#fff; font-size:1.15rem; font-weight:900; }
    #plan-modal #close-modal { width:36px; height:36px; display:grid; place-items:center; padding:0 !important; border:1px solid #565259; border-radius:3px !important; background:#343436; color:#fff; cursor:pointer; }
    #plan-modal #close-modal:hover { border-color:#ee9f2b; background:#ee9f2b; color:#242426; }
    #plan-modal > div > div:nth-child(2) > div:nth-child(2) { flex:1 1 auto; overflow-y:auto; padding:24px !important; background:#fff !important; }
    #plan-modal > div > div:nth-child(2) > div:nth-child(2) > div:first-child { margin-bottom:20px !important; padding:16px 18px !important; border:1px solid #c9e1e4 !important; border-left:5px solid #117e8c !important; border-radius:4px !important; background:#edf7f8 !important; }
    #plan-modal > div > div:nth-child(2) > div:nth-child(2) > div:nth-child(2) { margin-bottom:22px !important; padding-bottom:20px; border-bottom:1px solid #ded7e1; }
    #plan-modal > div > div:nth-child(2) h4 { color:#302834 !important; font-size:.82rem; font-weight:900; }
    #plan-modal > div > div:nth-child(2) h4 svg { color:#5b2b76 !important; }
    #plan-modal #modal-plan-dates { color:#514557 !important; font-size:.82rem; }
    #plan-modal #modal-plan-description { color:#756a7a !important; font-size:.8rem; line-height:1.65; }
    #plan-modal #modal-plan-features { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; }
    #plan-modal #modal-plan-features > div { min-width:0; margin:0 !important; padding:11px 12px !important; border:1px solid #e2dce4 !important; border-radius:4px !important; background:#f7f5f8 !important; box-shadow:none !important; }
    #plan-modal > div > div:nth-child(2) > div:last-child { flex:0 0 auto; padding:14px 22px !important; border-top:1px solid #ded7e1 !important; background:#f7f5f8 !important; }
    #plan-modal #close-modal-footer { padding:10px 18px !important; border:1px solid #5b2b76; border-radius:3px !important; background:#5b2b76 !important; color:#fff; font-size:.76rem; font-weight:900; box-shadow:none !important; transform:none !important; cursor:pointer; }
    #plan-modal #close-modal-footer:hover { background:#432056 !important; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) { border-color:#403943 !important; background:#1e1b21 !important; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) > div:nth-child(2) { background:#1e1b21 !important; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) > div:nth-child(2) > div:first-child { border-color:#376b72 !important; background:#173136 !important; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) > div:nth-child(2) > div:nth-child(2) { border-color:#403943; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) h4,
    html[data-client-theme="dark"] #plan-modal #modal-plan-dates { color:#f1edf3 !important; }
    html[data-client-theme="dark"] #plan-modal #modal-plan-description { color:#b4abb8 !important; }
    html[data-client-theme="dark"] #plan-modal #modal-plan-features > div { border-color:#403943 !important; background:#29252c !important; }
    html[data-client-theme="dark"] #plan-modal > div > div:nth-child(2) > div:last-child { border-color:#403943 !important; background:#29252c !important; }

    @media (max-width: 720px) {
        #mi-cuenta-page .cuenta-banner { min-height: 190px; padding: 26px 20px; }
        #mi-cuenta-page .cuenta-banner-side { margin-left: auto; }
        #mi-cuenta-page .cuenta-banner-mosaic { display: none; }
        #plan-modal { padding:10px; }
        #plan-modal > div > div:nth-child(2) { max-height:calc(100vh - 20px); }
        #plan-modal > div > div:nth-child(2) > div:first-child,
        #plan-modal > div > div:nth-child(2) > div:nth-child(2) { padding:18px !important; }
        #plan-modal #modal-plan-features { grid-template-columns:1fr; }
        .company-social-modal { padding:10px; }
        .company-social-dialog { max-height:calc(100vh - 20px); }
        .company-social-grid { grid-template-columns:1fr; }
    }
    @media (max-width: 500px) {
        #mi-cuenta-page .cuenta-banner { align-items: flex-start; flex-direction: column; gap: 20px; }
        #mi-cuenta-page .cuenta-banner-side { width: 100%; margin-left: 0; }
        #mi-cuenta-page .cuenta-banner-total { width: 100%; }
    }
</style>

<!-- Modal para detalles del plan -->
<div id="plan-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fondo del modal con blur -->
        <div class="fixed inset-0 transition-opacity backdrop-blur-sm" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/60"></div>
        </div>
        
        <!-- Contenido del modal -->
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
            
            <!-- Header del modal -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center" id="modal-plan-title">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Detalles del Plan
                    </h3>
                    <button type="button" id="close-modal" class="text-white/80 hover:text-white transition-colors duration-200 p-1 rounded-full hover:bg-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contenido del modal -->
            <div class="bg-white px-6 py-6">
                
                <!-- Información del ciclo -->
                <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200/50">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Ciclo de facturación
                    </h4>
                    <p class="text-gray-700 font-medium" id="modal-plan-dates"></p>
                    <p class="text-sm mt-1" id="modal-plan-status"></p>
                </div>
                
                <!-- Descripción -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descripción
                    </h4>
                    <p class="text-gray-600 leading-relaxed" id="modal-plan-description"></p>
                </div>
                
                <!-- Características -->
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Características incluidas
                    </h4>
                    <div class="space-y-2" id="modal-plan-features">
                        <!-- Características se llenarán aquí -->
                    </div>
                </div>
            </div>

            <!-- Footer del modal -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" id="close-modal-footer" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountModal = document.getElementById('account-edit-modal');
    const openAccountModal = document.getElementById('open-account-edit');
    const closeAccountModalButtons = document.querySelectorAll('[data-close-account-modal]');

    function showAccountModal() {
        accountModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideAccountModal() {
        accountModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    openAccountModal?.addEventListener('click', showAccountModal);
    closeAccountModalButtons.forEach(button => button.addEventListener('click', hideAccountModal));

    const companySocialModal = document.getElementById('company-social-modal');
    const companySocialName = document.getElementById('company-social-name');
    const facebookOption = document.getElementById('company-facebook-option');
    const instagramOption = document.getElementById('company-instagram-option');
    const companySocialButtons = document.querySelectorAll('.open-company-social-modal');
    let activeCompanySocialButton = null;

    document.body.appendChild(companySocialModal);

    function configureSocialOption(option, linked, enabled, url, platform) {
        option.classList.toggle('is-linked', linked);
        option.classList.toggle('is-disabled', !enabled);
        option.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        option.href = enabled ? url : '#';
        option.querySelector('.company-social-badge').textContent = linked ? 'Vinculado' : (enabled ? 'Disponible' : 'Bloqueado');
        option.querySelector('strong').innerHTML = linked
            ? (platform === 'Facebook' ? 'Vinculado · Volver a conectar <i class="fas fa-arrow-right"></i>' : 'Cuenta conectada <i class="fas fa-check"></i>')
            : (enabled ? `Conectar con ${platform} <i class="fas fa-arrow-right"></i>` : 'Esperando Facebook');
    }

    function showCompanySocialModal(button) {
        activeCompanySocialButton = button;
        const facebookLinked = button.dataset.facebookLinked === '1';
        const instagramLinked = button.dataset.instagramLinked === '1';
        companySocialName.textContent = button.dataset.companyName;
        configureSocialOption(facebookOption, facebookLinked, true, button.dataset.facebookUrl, 'Facebook');
        configureSocialOption(instagramOption, instagramLinked, facebookLinked, button.dataset.instagramUrl, 'Instagram');
        companySocialModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => companySocialModal.querySelector('.company-social-header > button').focus(), 50);
    }

    function hideCompanySocialModal() {
        companySocialModal.classList.add('hidden');
        document.body.style.overflow = '';
        activeCompanySocialButton?.focus();
    }

    companySocialButtons.forEach(button => button.addEventListener('click', () => showCompanySocialModal(button)));
    document.querySelectorAll('[data-close-company-social]').forEach(button => button.addEventListener('click', hideCompanySocialModal));
    [facebookOption, instagramOption].forEach(option => option.addEventListener('click', event => {
        if (option.classList.contains('is-disabled') || (option === instagramOption && option.classList.contains('is-linked'))) event.preventDefault();
    }));

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !accountModal.classList.contains('hidden')) {
            hideAccountModal();
        }
        if (event.key === 'Escape' && !companySocialModal.classList.contains('hidden')) {
            hideCompanySocialModal();
        }
    });

    const requestedSocialCompany = @json((string) request('redes_empresa', ''));
    if (requestedSocialCompany) {
        const requestedButton = Array.from(companySocialButtons).find(button => button.dataset.companyId === requestedSocialCompany);
        if (requestedButton) showCompanySocialModal(requestedButton);
    }

    @if($errors->any())
        showAccountModal();
    @endif

    fetchPlanContratado();
    
    function fetchPlanContratado() {
        fetch('/cliente/plan-contratado')
            .then(response => {
                if (!response.ok) {
                    throw new Error('No se pudo obtener la información del plan');
                }
                return response.json();
            })
            .then(data => {
                renderPlanContratado(data.plan);
                setupPlanModal(data.plan);
            })
            .catch(error => {
                console.error('Error:', error);
                renderErrorPlanContratado(error.message);
            });
    }
    
    function renderPlanContratado(plan) {
        const container = document.getElementById('plan-contratado-container');
        
        // Crear características HTML con diseño moderno
        let caracteristicasHtml = '';
        if (plan.caracteristicas && plan.caracteristicas.length > 0) {
            plan.caracteristicas.forEach((caracteristica, index) => {
                const colors = [
                    'from-blue-500 to-indigo-600',
                    'from-purple-500 to-pink-600', 
                    'from-green-500 to-emerald-600',
                    'from-orange-500 to-red-600',
                    'from-cyan-500 to-blue-600'
                ];
                const colorClass = colors[index % colors.length];
                
                caracteristicasHtml += `
                    <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br ${colorClass} opacity-10 rounded-full -translate-y-4 translate-x-4 group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-2 bg-gradient-to-br ${colorClass} rounded-xl shadow-lg">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <h4 class="font-semibold text-gray-700 text-sm mb-2">${caracteristica.nombre}</h4>
                            <p class="text-3xl font-bold bg-gradient-to-r ${colorClass} bg-clip-text text-transparent">
                                ${caracteristica.cantidad}${caracteristica.unidad || ''}
                            </p>
                        </div>
                    </div>
                `;
            });
        }
        
        const statusColors = {
            'activa': 'bg-green-100 text-green-800 border-green-200',
            'pendiente': 'bg-amber-100 text-amber-800 border-amber-200',
            'inactiva': 'bg-red-100 text-red-800 border-red-200'
        };
        
        container.innerHTML = `
            <div class="p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-8">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">${plan.nombre}</h3>
                                <p class="text-gray-600">${plan.descripcion || 'Plan de marketing digital'}</p>
                            </div>
                        </div>
                        
                        <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/40">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-600">Fecha de pago:</span>
                                        <span class="text-sm font-semibold text-gray-800">${plan.fecha_pago || 'No registrada'}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-600">Ciclo actual:</span>
                                        <span class="text-sm font-semibold text-gray-800">${plan.vigencia_activada ? `${plan.fecha_inicio} - ${plan.fecha_fin}` : 'No definido'}</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full border ${statusColors[plan.estado] || statusColors['inactiva']}">
                                    <div class="w-2 h-2 rounded-full bg-current mr-2"></div>
                                    ${plan.estado}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 lg:mt-0 lg:ml-8">
                        <button id="ver-detalles-btn" class="group relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl">
                            <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <div class="relative flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Ver detalles</span>
                            </div>
                        </button>
                    </div>
                </div>
                
                ${caracteristicasHtml ? `
                    <div class="mt-8">
                        <h4 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Características principales
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            ${caracteristicasHtml}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    function setupPlanModal(plan) {
        const modal = document.getElementById('plan-modal');
        const verDetallesBtn = document.getElementById('ver-detalles-btn');
        const closeModalBtn = document.getElementById('close-modal');
        const closeModalFooterBtn = document.getElementById('close-modal-footer');
        const modalDialog = modal.querySelector(':scope > div > div:nth-child(2)');

        document.body.appendChild(modal);
        
        // Llenar datos del modal
        document.getElementById('modal-plan-title').textContent = `Plan ${plan.nombre}`;
        document.getElementById('modal-plan-dates').textContent = plan.vigencia_activada
            ? `${plan.fecha_inicio} - ${plan.fecha_fin}`
            : 'No definido hasta que comience la campaña';
        
        const statusColors = {
            'activa': 'text-green-600',
            'pendiente': 'text-amber-600',
            'inactiva': 'text-red-600'
        };
        
        document.getElementById('modal-plan-status').innerHTML = `
            Estado: <span class="${statusColors[plan.estado] || statusColors['inactiva']} font-semibold">${plan.estado}</span>
        `;
        document.getElementById('modal-plan-description').textContent = plan.descripcion || 'No hay descripción disponible';
        
        // Llenar características con diseño moderno
        const featuresList = document.getElementById('modal-plan-features');
        featuresList.innerHTML = '';
        
        if (plan.todas_caracteristicas && plan.todas_caracteristicas.length > 0) {
            plan.todas_caracteristicas.forEach(caracteristica => {
                const div = document.createElement('div');
                div.className = 'flex items-center p-3 bg-gradient-to-r from-gray-50 to-indigo-50 rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200';
                
                const starIcon = caracteristica.es_destacado ? 
                    '<svg class="w-5 h-5 text-amber-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>' : 
                    '<svg class="w-5 h-5 text-indigo-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                
                div.innerHTML = `
                    ${starIcon}
                    <div class="flex-1">
                        <span class="font-medium text-gray-800">${caracteristica.nombre}</span>
                        <span class="text-gray-600">: ${caracteristica.cantidad}${caracteristica.unidad || ''}</span>
                        ${caracteristica.frecuencia ? `<span class="text-sm text-gray-500 block">${caracteristica.frecuencia}</span>` : ''}
                    </div>
                `;
                featuresList.appendChild(div);
            });
        } else {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-center p-6 text-gray-500';
            div.innerHTML = `
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                No se encontraron características
            `;
            featuresList.appendChild(div);
        }
        
        // Event listeners
        function openModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            window.setTimeout(() => closeModalBtn.focus(), 50);
        }
        
        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            verDetallesBtn.focus();
        }
        
        verDetallesBtn?.addEventListener('click', openModal);
        closeModalBtn?.addEventListener('click', closeModal);
        closeModalFooterBtn?.addEventListener('click', closeModal);
        
        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
        
        // Cerrar al hacer clic en el fondo, fuera del contenido.
        modal.addEventListener('click', (e) => {
            if (!modalDialog.contains(e.target)) {
                closeModal();
            }
        });
    }  
        
    function renderErrorPlanContratado(message) {
        const container = document.getElementById('plan-contratado-container');
        container.innerHTML = `
            <div class="text-center py-16">
                <div class="relative">
                    <div class="w-20 h-20 bg-red-100 rounded-2xl mx-auto flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Error al cargar el plan</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <button onclick="fetchPlanContratado()" class="group relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <div class="absolute inset-0 bg-white/20 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <div class="relative flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Reintentar</span>
                    </div>
                </button>
            </div>
        `;
    }
    
    // Hacer la función accesible globalmente para el botón de reintento
    window.fetchPlanContratado = fetchPlanContratado;
});
</script>
@endpush

@endsection
