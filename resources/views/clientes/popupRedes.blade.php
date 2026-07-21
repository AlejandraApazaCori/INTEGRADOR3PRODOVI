@php
    $socialAccountsTableExists = auth()->user()?->socialAccountsTableExists() ?? false;
    $socialAccounts = auth()->user()?->linkedSocialAccounts() ?? collect();
    $facebookLinked = $socialAccounts->has('facebook') && filled(optional($socialAccounts->get('facebook'))->provider_user_id);
    $instagramLinked = $socialAccounts->has('instagram') && filled(optional($socialAccounts->get('instagram'))->provider_user_id);
    $allAccountsLinked = $facebookLinked && $instagramLinked;
    $facebookAppId = config('services.facebook.client_id');
    $facebookApiVersion = config('facebook.api_version', 'v18.0');
@endphp

@if(! $allAccountsLinked)
<div id="social-accounts-alert" class="fixed top-0 left-0 right-0 z-40 bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-3 shadow-lg transform transition-transform duration-300">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-small">Para aprovechar al m?ximo nuestros servicios, vincula tus cuentas de redes sociales</p>
            </div>
            <button id="link-now-btn" class="bg-white text-blue-600 hover:bg-blue-50 font-medium py-2 px-4 rounded-md transition-colors duration-200">
                Vincular ahora
            </button>
        </div>
    </div>
</div>

<div id="link-social-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity backdrop-blur-sm" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/50"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">Vincula tus cuentas</h3>
                    <span class="text-xs font-medium uppercase tracking-wide text-white/80">OAuth</span>
                </div>
            </div>

            <div class="bg-white px-6 py-6">
                <p class="text-gray-700 mb-2">Conecta tus cuentas de redes sociales para que podamos gestionar tu presencia en linea de manera efectiva.</p>
                <p class="text-sm text-gray-500 mb-3">Instagram se habilitará después de vincular Facebook.</p>
                <p id="facebook-login-status" class="mb-4 text-sm text-blue-600"></p>

                @if(session('social_accounts_error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('social_accounts_error') }}
                    </div>
                @endif

                @if(session('social_accounts_success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('social_accounts_success') }}
                    </div>
                @endif

                                @if(! $socialAccountsTableExists)
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                        <p class="font-semibold">La tabla <code>social_accounts</code> aún no está lista en este entorno.</p>
                        <p class="mt-1">Este botón ejecuta una sola vez la reparación automática: elimina cualquier tabla incompleta y corre las migraciones necesarias.</p>
                        <form method="POST" action="{{ route('clientes.social.setup-social-accounts') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-amber-600">
                                Ejecutar migración automática
                            </button>
                        </form>
                    </div>
                @endif

                <div class="space-y-4">
                    <a
                        href="{{ route('clientes.social.redirect', 'facebook') }}"
                        id="facebook-connect-btn"
                        data-facebook-login-url="{{ route('clientes.social.redirect', 'facebook') }}"
                        class="w-full flex items-center justify-between p-4 rounded-xl border transition-all duration-200 {{ $facebookLinked ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 hover:bg-blue-50 border-gray-200 hover:border-blue-300' }}"
                    >
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="font-medium text-gray-800 block">Facebook</span>
                                <span id="facebook-connect-subtitle" class="text-xs {{ $facebookLinked ? 'text-emerald-600' : 'text-gray-500' }}">
                                    {{ $facebookLinked ? 'Cuenta conectada' : 'Conectar con Meta OAuth' }}
                                </span>
                            </div>
                        </div>
                        <span id="facebook-connect-badge" class="text-sm font-medium {{ $facebookLinked ? 'text-emerald-600' : 'text-gray-500' }}">
                            {{ $facebookLinked ? 'Vinculado' : 'No vinculado' }}
                        </span>
                    </a>

                    @if($facebookLinked)
                        <a href="{{ route('clientes.social.redirect', 'instagram') }}" class="w-full flex items-center justify-between p-4 rounded-xl border transition-all duration-200 {{ $instagramLinked ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 hover:bg-pink-50 border-gray-200 hover:border-pink-300' }}">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                                        <path d="M12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4z"/>
                                        <circle cx="18.406" cy="5.594" r="1.44"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800 block">Instagram</span>
                                    <span class="text-xs {{ $instagramLinked ? 'text-emerald-600' : 'text-gray-500' }}">
                                        {{ $instagramLinked ? 'Cuenta conectada' : 'Listo para el siguiente paso OAuth' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-sm font-medium {{ $instagramLinked ? 'text-emerald-600' : 'text-gray-500' }}">
                                {{ $instagramLinked ? 'Vinculado' : 'No vinculado' }}
                            </span>
                        </a>
                    @else
                        <button type="button" disabled aria-disabled="true" class="w-full flex items-center justify-between p-4 rounded-xl border transition-all duration-200 opacity-60 cursor-not-allowed bg-gray-100 border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                                        <path d="M12 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4z"/>
                                        <circle cx="18.406" cy="5.594" r="1.44"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800 block">Instagram</span>
                                    <span class="text-xs text-gray-500">Primero vincula Facebook</span>
                                </div>
                            </div>
                            <span class="text-sm font-medium text-gray-500">Bloqueado</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('link-social-modal');
        const openModalButton = document.getElementById('link-now-btn');
        const facebookConnectButton = document.getElementById('facebook-connect-btn');
        const facebookStatusText = document.getElementById('facebook-login-status');
        const facebookSubtitle = document.getElementById('facebook-connect-subtitle');
        const facebookBadge = document.getElementById('facebook-connect-badge');
        const shouldOpenOnLoad = {{ session()->has('social_accounts_error') || session()->has('social_accounts_success') ? 'true' : 'false' }};
        const facebookAppId = @json($facebookAppId);
        const facebookApiVersion = @json($facebookApiVersion);
        const isFacebookLinked = {{ $facebookLinked ? 'true' : 'false' }};
        let facebookSdkReady = false;

        if (!modal || !openModalButton) {
            return;
        }

        function showModal() {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function updateFacebookVisualState(subtitle, badge, statusMessage) {
            if (facebookSubtitle) {
                facebookSubtitle.textContent = subtitle;
            }

            if (facebookBadge) {
                facebookBadge.textContent = badge;
            }

            if (facebookStatusText) {
                facebookStatusText.textContent = statusMessage;
            }
        }

        function statusChangeCallback(response) {
            if (!response) {
                updateFacebookVisualState('Conectar con Meta OAuth', 'No vinculado', 'No se pudo verificar el estado de Facebook en este momento.');
                return;
            }

            if (response.status === 'connected') {
                updateFacebookVisualState(
                    isFacebookLinked ? 'Cuenta conectada' : 'Sesion de Facebook detectada',
                    isFacebookLinked ? 'Vinculado' : 'Listo para vincular',
                    isFacebookLinked
                        ? 'Tu cuenta de Facebook ya esta vinculada con PRODOVI.'
                        : 'Facebook reconocio tu sesion. Ahora puedes continuar con el boton de vinculacion.'
                );

                return;
            }

            if (response.status === 'not_authorized') {
                updateFacebookVisualState('Autoriza el acceso con Meta OAuth', 'Pendiente', 'Has iniciado sesion en Facebook, pero todavia no autorizaste esta app.');
                return;
            }

            updateFacebookVisualState('Conectar con Meta OAuth', 'No vinculado', 'Inicia sesion en Facebook para vincular tu cuenta con PRODOVI.');
        }

        window.checkLoginState = function () {
            if (!facebookSdkReady || typeof FB === 'undefined') {
                updateFacebookVisualState('Conectar con Meta OAuth', 'No vinculado', 'El SDK de Facebook aun no termino de cargar.');
                return;
            }

            FB.getLoginStatus(function (response) {
                statusChangeCallback(response);
            });
        };

        openModalButton.addEventListener('click', showModal);

        if (facebookConnectButton && facebookAppId) {
            window.fbAsyncInit = function () {
                FB.init({
                    appId: facebookAppId,
                    cookie: true,
                    xfbml: true,
                    version: facebookApiVersion
                });

                FB.AppEvents.logPageView();
                facebookSdkReady = true;
                window.checkLoginState();
            };

            (function (d, s, id) {
                let js;
                const fjs = d.getElementsByTagName(s)[0];

                if (d.getElementById(id)) {
                    return;
                }

                js = d.createElement(s);
                js.id = id;
                js.src = 'https://connect.facebook.net/en_US/sdk.js';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            facebookConnectButton.addEventListener('click', function () {
                if (facebookSdkReady && typeof FB !== 'undefined') {
                    window.checkLoginState();
                }
            });
        }

        if (shouldOpenOnLoad) {
            showModal();
        }
    });
</script>
@endif








