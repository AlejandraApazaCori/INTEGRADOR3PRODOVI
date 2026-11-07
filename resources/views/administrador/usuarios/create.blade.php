@extends('layouts.app')

@section('title', 'Crear Usuario')

@push('styles')
<style>
    .users-create-page { min-height: 100vh; background: #f7f8fa; color: #263024; padding-bottom: 40px; }
    .users-create-hero { position: relative; isolation: isolate; overflow: hidden; width: 100%; min-height: 180px; display: flex; align-items: center; justify-content: space-between; gap: 28px; padding: 30px 48px; color: #fff; background: linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0, linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0, linear-gradient(315deg, #4f46e5 25%, transparent 25%), linear-gradient(45deg, #4f46e5 25%, transparent 25%), linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%); background-color: #1d4ed8; background-size: 100px 100px, 100px 100px, 100px 100px, 100px 100px, 100% 100%; }
    .users-create-hero::after { content: ''; position: absolute; z-index: -1; inset: 0; background: linear-gradient(rgba(15,23,42,.22), rgba(15,23,42,.22)), radial-gradient(circle at 0 0, rgba(255,255,255,.2), transparent 50%), radial-gradient(circle at 100% 100%, rgba(255,255,255,.16), transparent 50%); }
    .users-create-hero-copy span { display: block; margin-bottom: 7px; color: #dbeafe; font-size: .68rem; font-weight: 900; letter-spacing: .15em; text-transform: uppercase; }
    .users-create-hero-copy h1 { margin: 0; color: #fff; font-size: clamp(1.55rem,3vw,2.25rem); font-weight: 900; letter-spacing: -.04em; }
    .users-create-hero-copy p { margin-top: 8px; color: #dbeafe; font-size: .85rem; }
    .users-create-back { display: inline-flex; align-items: center; gap: 9px; padding: 11px 14px; border: 1px solid #fff; border-radius: .65rem; background: #fff; color: #4f46e5; font-size: .72rem; font-weight: 900; transition: .18s; white-space: nowrap; }
    .users-create-back:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(31,41,55,.16); }
    .users-create-content { max-width: 1024px; margin: 24px auto 0; padding: 0 24px; }
    .users-create-card { overflow: hidden; border: 1px solid #e2e8f0; border-top: 4px solid #7da533; border-radius: 16px; background: #fff; box-shadow: 0 9px 24px rgba(45,66,34,.09); }
    .users-create-page .users-create-heading { color: #263024; }
    .users-create-page .users-create-heading svg { color: #117e8c; }
    .users-create-page .users-create-field { background: #fff; color: #263024; border-color: #d8e3c7; }
    .users-create-page .users-create-field:focus { border-color: #117e8c; box-shadow: 0 0 0 3px rgba(17,126,140,.15); outline: none; }
    .users-create-page .users-create-field.border-red-500,
    html.admin-dark-nav .users-create-page .users-create-field.border-red-500 { border-color: #ef4444; }
    .users-create-page .users-create-roles { background: #f1f7e8; border-color: #d8e3c7; }
    .users-create-page .users-create-cancel { color: #0d6975; border-color: #cce2e5; }
    .users-create-page .users-create-cancel:hover { background: #e6f4f5; }
    .users-create-page .users-create-check { accent-color: #7da533; }
    .users-create-page .users-create-submit { background: #7da533; }
    .users-create-page .users-create-submit:hover { background: #638524; }
    .users-create-generate { display: inline-flex; align-items: center; gap: 8px; margin: -2px 0 18px; padding: 10px 14px; border: 1px solid #cce2e5; border-radius: 9px; background: #e6f4f5; color: #0d6975; font-size: .82rem; font-weight: 700; transition: .18s; }
    .users-create-generate:hover { background: #d4ecee; }
    .users-create-generate-note { margin-left: 10px; color: #596170; font-size: .78rem; }
    html.admin-dark-nav .users-create-page { background: #141414; color: #e5e5e5; }
    html.admin-dark-nav .users-create-card { background: #202020; border-color: #353535; border-top-color: #7da533; box-shadow: 0 10px 28px rgba(0,0,0,.25); }
    html.admin-dark-nav .users-create-page .users-create-heading,
    html.admin-dark-nav .users-create-page label { color: #f5f5f5; }
    html.admin-dark-nav .users-create-page .users-create-heading svg { color: #a3e635; }
    html.admin-dark-nav .users-create-page .users-create-field { background: #141414; color: #f5f5f5; border-color: #4a4a4a; }
    html.admin-dark-nav .users-create-page .users-create-field::placeholder { color: #9c9c9c; }
    html.admin-dark-nav .users-create-page .users-create-field:focus { border-color: #a3e635; box-shadow: 0 0 0 3px rgba(103,212,223,.18); }
    html.admin-dark-nav .users-create-page .users-create-roles { background: #292929; border-color: #4a4a4a; }
    html.admin-dark-nav .users-create-page .users-create-roles > p { color: #cccccc; }
    html.admin-dark-nav .users-create-page .users-create-actions { border-color: #4a4a4a; }
    html.admin-dark-nav .users-create-page .users-create-cancel { background: #353535; border-color: #4a4a4a; color: #f5f5f5; }
    html.admin-dark-nav .users-create-page .users-create-cancel:hover { background: #4a4a4a; }
    html.admin-dark-nav .users-create-page .users-create-error { background: #451a25; border-color: #ef4444; }
    html.admin-dark-nav .users-create-page .users-create-error h3,
    html.admin-dark-nav .users-create-page .users-create-error div,
    html.admin-dark-nav .users-create-page .users-create-error li { color: #fecaca; }
    html.admin-dark-nav .users-create-page .role-error-alert { background: #451a25; border-color: #ef4444; color: #fecaca; }
    html.admin-dark-nav .users-create-generate { background: #303030; border-color: #484848; color: #f5f5f5; }
    html.admin-dark-nav .users-create-generate:hover { background: #484848; }
    html.admin-dark-nav .users-create-generate-note { color: #cccccc; }
    @media (max-width: 980px) { .users-create-hero { min-height: 205px; flex-direction: column; justify-content: center; text-align: center; } }
    @media (max-width: 640px) { .users-create-hero { padding: 24px 20px; } .users-create-back { justify-content: center; width: 100%; } .users-create-content { padding: 0 12px; } }
</style>
@endpush

@section('content')
<div class="users-create-page">
    <header class="users-create-hero">
        <div class="users-create-hero-copy">
            <h1>Crear nuevo usuario</h1>
        </div>
        <a href="{{ route('administrador.usuarios.index') }}" class="users-create-back">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a la lista
        </a>
    </header>
    <div class="users-create-content">

        {{-- Tarjeta del Formulario --}}
        <div class="users-create-card animate-fade-up" style="animation-delay: 0.1s">
            {{-- Alerta de Errores --}}
            @if ($errors->any())
                <div class="users-create-error border-l-4 border-red-500 bg-red-50 p-4 m-6 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Por favor, corrige los siguientes errores:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form id="createUserForm" action="{{ route('administrador.usuarios.store') }}" method="POST" class="p-6 sm:p-8">
                @csrf

                {{-- Información Personal --}}
                <div class="mb-8">
                    <h2 class="users-create-heading text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Información Personal
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nombre --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                            <div class="relative">
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="users-create-field @error('name') border-red-500 @enderror appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 transition"
                                    placeholder="Juan Pérez">
                                @error('name')
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                            <div class="relative">
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="users-create-field @error('email') border-red-500 @enderror appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 transition"
                                    placeholder="correo@ejemplo.com">
                                @error('email')
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Celular --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Celular (Opcional)</label>
                            <div class="relative">
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" inputmode="numeric" pattern="[0-9]*" maxlength="20"
                                    class="users-create-field appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 transition"
                                    placeholder="62397902">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Seguridad --}}
                <div class="mb-8">
                    <h2 class="users-create-heading text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Seguridad
                    </h2>
                    <button type="button" id="generateSecurePassword" class="users-create-generate">
                        <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Generar contraseña segura
                    </button>
                    <span id="generatedPasswordNote" class="users-create-generate-note" role="status" aria-live="polite"></span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Contraseña --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="users-create-field @error('password') border-red-500 @enderror appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 transition"
                                    placeholder="••••••••">
                                @error('password')
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirmar Contraseña --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="users-create-field @error('password_confirmation') border-red-500 @enderror appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 transition"
                                    placeholder="••••••••">
                                @error('password_confirmation')
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @enderror
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Roles --}}
                <div class="mb-8">
                    <h2 class="users-create-heading text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Asignar Roles
                    </h2>
                    <div class="users-create-roles bg-gray-50 rounded-lg p-4 border border-gray-200">
                        @if($roles->isEmpty())
                            <p class="text-sm text-gray-500">No hay roles disponibles. Por favor, crea roles primero.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($roles as $role)
                                    <div class="flex items-center">
                                        <input id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}" type="checkbox"
                                            class="users-create-check h-4 w-4 border-gray-300 rounded transition"
                                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                        <label for="role_{{ $role->id }}" class="ml-3 block text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ $role->nombre_rol }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="users-create-actions flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('administrador.usuarios.index') }}" class="users-create-cancel px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="users-create-submit px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phone = document.getElementById('phone');
    phone.addEventListener('input', function() {
        phone.value = phone.value.replace(/[^0-9]/g, '');
    });
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const form = document.getElementById('createUserForm');
    const generateButton = document.getElementById('generateSecurePassword');
    const generatedPasswordNote = document.getElementById('generatedPasswordNote');

    generateButton.addEventListener('click', function() {
        if (!window.crypto?.getRandomValues) {
            generatedPasswordNote.textContent = 'Tu navegador no permite generar contraseñas seguras.';
            return;
        }

        const groups = [
            'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'abcdefghijkmnopqrstuvwxyz',
            '23456789',
            '!@#$%&*?'
        ];
        const allCharacters = groups.join('');
        const randomIndex = (length) => {
            const limit = Math.floor(4294967296 / length) * length;
            const value = new Uint32Array(1);
            do { window.crypto.getRandomValues(value); } while (value[0] >= limit);
            return value[0] % length;
        };
        const characters = groups.map(group => group[randomIndex(group.length)]);
        while (characters.length < 20) characters.push(allCharacters[randomIndex(allCharacters.length)]);
        for (let i = characters.length - 1; i > 0; i--) {
            const j = randomIndex(i + 1);
            [characters[i], characters[j]] = [characters[j], characters[i]];
        }

        password.value = characters.join('');
        passwordConfirmation.value = password.value;
        password.type = 'text';
        passwordConfirmation.setCustomValidity('');
        generatedPasswordNote.textContent = 'Contraseña generada y confirmada. Puedes copiarla del campo.';
        password.focus();
        password.select();
    });

    // Validación en tiempo real de la coincidencia de contraseñas
    function validatePasswordMatch() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Las contraseñas no coinciden.');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        }
    }

    password.addEventListener('change', validatePasswordMatch);
    passwordConfirmation.addEventListener('keyup', validatePasswordMatch);

    // Validación de rol seleccionado antes de enviar
    form.addEventListener('submit', function(event) {
        const checkboxes = document.querySelectorAll('input[name="roles[]"]:checked');
        if (checkboxes.length === 0) {
            event.preventDefault();
            // Crear una alerta visual en lugar de un alert()
            const rolesSection = document.querySelector('.users-create-roles').parentElement;
            const existingAlert = rolesSection.querySelector('.role-error-alert');
            if (existingAlert) existingAlert.remove();

            const errorDiv = document.createElement('div');
            errorDiv.className = 'role-error-alert mt-2 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm';
            errorDiv.textContent = 'Debe seleccionar al menos un rol para el usuario.';
            rolesSection.appendChild(errorDiv);

            // Hacer scroll al error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Remover la alerta después de 5 segundos
            setTimeout(() => errorDiv.remove(), 5000);
        }
    });
});
</script>
@endpush
