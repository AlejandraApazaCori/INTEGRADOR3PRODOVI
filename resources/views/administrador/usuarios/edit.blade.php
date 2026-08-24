@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
@php
    $editFromAccount = request('from') === 'view';
    $editBackUrl = $editFromAccount
        ? route('administrador.usuarios.view', $user->id)
        : route('administrador.usuarios.index');
@endphp
<div class="user-edit-page">
    <header class="user-edit-hero">
        <div class="user-edit-hero-copy">
            <span><i class="fas fa-user-pen"></i> Gestión de usuarios</span>
            <h1>Editar usuario</h1>
            <p>Actualiza la información y los permisos de <strong>{{ $user->name }}</strong>.</p>
        </div>
        <a href="{{ $editBackUrl }}" class="user-edit-back"><i class="fas fa-arrow-left"></i> Volver atrás</a>
    </header>

    <div class="user-edit-content">
        @if ($errors->any())
            <div class="user-edit-alert" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <div><strong>Revisa la información ingresada</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            </div>
        @endif

        <form id="user-edit-form" action="{{ route('administrador.usuarios.update', $user->id) }}" method="POST" class="user-edit-form">
            @csrf
            @method('PUT')

            <div class="user-edit-form-head">
                <div class="user-edit-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                <div><span>Cuenta seleccionada</span><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p></div>
            </div>

            <section class="user-edit-section">
                <div class="user-edit-section-title">
                    <i class="fas fa-address-card"></i>
                    <div><h3>Información personal</h3><p>Datos principales para identificar y contactar al usuario.</p></div>
                </div>
                <div class="user-edit-grid">
                    <div class="user-edit-field">
                        <label for="name">Nombre completo <b>*</b></label>
                        <div class="user-edit-input-wrap @error('name') has-error @enderror">
                            <i class="fas fa-user"></i><input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Nombre completo">
                        </div>
                        @error('name')<small class="user-edit-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="user-edit-field">
                        <label for="email">Correo electrónico <b>*</b></label>
                        <div class="user-edit-input-wrap @error('email') has-error @enderror">
                            <i class="fas fa-envelope"></i><input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="correo@ejemplo.com">
                        </div>
                        @error('email')<small class="user-edit-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="user-edit-field user-edit-field-wide">
                        <label for="phone">Teléfono <span>Opcional</span></label>
                        <div class="user-edit-input-wrap @error('phone') has-error @enderror">
                            <i class="fas fa-phone"></i><input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Número de teléfono">
                        </div>
                        @error('phone')<small class="user-edit-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </section>

            <section class="user-edit-section">
                <div class="user-edit-section-title">
                    <i class="fas fa-lock security-section-icon" aria-hidden="true"></i>
                    <div><h3>Seguridad</h3><p>Deja ambos campos vacíos si deseas conservar la contraseña actual.</p></div>
                </div>
                <div class="user-edit-grid">
                    <div class="user-edit-field">
                        <label for="password">Nueva contraseña</label>
                        <div class="user-edit-input-wrap @error('password') has-error @enderror">
                            <i class="fas fa-lock"></i><input type="password" id="password" name="password" placeholder="••••••••">
                            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                        </div>
                        @error('password')<small class="user-edit-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="user-edit-field">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <div class="user-edit-input-wrap @error('password_confirmation') has-error @enderror">
                            <i class="fas fa-key"></i><input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••">
                            <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                        </div>
                        @error('password_confirmation')<small class="user-edit-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </section>

            <section class="user-edit-section user-edit-roles-section">
                <div class="user-edit-section-title">
                    <i class="fas fa-user-shield"></i>
                    <div><h3>Roles y permisos</h3><p>Selecciona al menos un rol para definir el acceso del usuario.</p></div>
                </div>
                @if($roles->isEmpty())
                    <div class="user-edit-empty"><i class="fas fa-circle-info"></i> No hay roles disponibles.</div>
                @else
                    <div class="user-edit-roles" id="user-edit-roles">
                        @foreach($roles as $role)
                            <label class="user-edit-role">
                                <input id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}" type="checkbox" {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                <span class="user-edit-role-check"><i class="fas fa-check"></i></span>
                                <span class="user-edit-role-icon"><i class="fas fa-user-tag"></i></span>
                                <span class="user-edit-role-copy"><strong>{{ $role->nombre_rol }}</strong><small>Permisos asociados al rol</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')<small class="user-edit-error">{{ $message }}</small>@enderror
                @endif
            </section>

            <footer class="user-edit-actions">
                <a href="{{ $editBackUrl }}"><i class="fas fa-xmark"></i> Cancelar</a>
                <button type="submit"><i class="fas fa-floppy-disk"></i> Guardar cambios</button>
            </footer>
        </form>
    </div>
</div>

<style>
    .user-edit-page{min-height:100vh;padding:20px 0 48px;background:#fff;color:#302834}.user-edit-hero{position:relative;isolation:isolate;overflow:hidden;min-height:180px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 48px;color:#fff;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6 0%,#2563eb 100%);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}
    .user-edit-hero:before{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.16),transparent 50%)}.user-edit-hero:after{display:none}
    .user-edit-hero-copy>span{display:flex;align-items:center;gap:8px;margin-bottom:8px;color:#d9efff;font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.user-edit-hero h1{margin:0;color:#fff;font-size:clamp(1.7rem,3vw,2.35rem);font-weight:900;letter-spacing:-.04em}.user-edit-hero p{margin:7px 0 0;color:rgba(255,255,255,.84);font-size:.79rem}.user-edit-hero p strong{color:#fff}
    .user-edit-back{display:inline-flex;align-items:center;gap:9px;flex:0 0 auto;padding:11px 15px;border:1px solid rgba(255,255,255,.35);border-radius:.7rem;background:rgba(255,255,255,.14);color:#fff;font-size:.72rem;font-weight:900;backdrop-filter:blur(5px);transition:.18s}.user-edit-back:hover{transform:translateY(-2px);background:#fff;color:#245fa9;box-shadow:0 10px 22px rgba(19,55,105,.22)}
    .user-edit-content{width:calc(100% - 48px);max-width:1080px;margin:28px auto 0}.user-edit-alert{display:flex;align-items:flex-start;gap:13px;margin-bottom:18px;padding:15px 17px;border:1px solid #f3c4c4;border-radius:14px;background:#fff2f2;color:#a72d2d}.user-edit-alert>i{margin-top:2px}.user-edit-alert strong{font-size:.8rem}.user-edit-alert ul{margin:6px 0 0 17px;font-size:.7rem}
    .user-edit-form{overflow:hidden;border:1px solid #d8e7f1;border-radius:18px;background:#fff;box-shadow:0 12px 30px rgba(30,72,110,.09)}.user-edit-form-head{display:flex;align-items:center;gap:14px;padding:18px 24px;border-bottom:1px solid #dceaf2;background:linear-gradient(90deg,#f2f7fc,#eaf8f9)}.user-edit-avatar{width:48px;height:48px;display:grid;place-items:center;flex:0 0 auto;border-radius:14px;background:linear-gradient(135deg,#2563b9,#1593b5);color:#fff;font-size:1.15rem;font-weight:900;box-shadow:0 8px 18px rgba(21,147,181,.22)}.user-edit-form-head span,.user-edit-form-head p{display:block;color:#78909f;font-size:.62rem;font-weight:700}.user-edit-form-head h2{margin:2px 0;color:#24465f;font-size:.95rem;font-weight:900}
    .user-edit-section{padding:25px 28px;border-bottom:1px solid #e7eff5}.user-edit-section-title{display:flex;align-items:flex-start;gap:12px;margin-bottom:20px}.user-edit-section-title>i{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:11px;background:#e8f1fb;color:#2563b9;font-size:.9rem}.user-edit-section-title>i.security-section-icon{background:#2563b9;color:#fff;font-size:1rem;box-shadow:0 6px 14px rgba(37,99,185,.22)}.user-edit-section:nth-of-type(3) .user-edit-section-title>i{background:#e5f6f8;color:#1593b5}.user-edit-roles-section .user-edit-section-title>i{background:#e9effd;color:#4f46e5}.user-edit-section-title h3{margin:0;color:#263f52;font-size:.9rem;font-weight:900}.user-edit-section-title p{margin:3px 0 0;color:#78909f;font-size:.66rem;line-height:1.45}
    .user-edit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.user-edit-field-wide{grid-column:1/-1}.user-edit-field label{display:flex;align-items:center;gap:5px;margin-bottom:7px;color:#405568;font-size:.69rem;font-weight:900}.user-edit-field label b{color:#e37225}.user-edit-field label span{color:#91a2ad;font-size:.59rem;font-weight:700}.user-edit-input-wrap{height:49px;display:flex;align-items:center;gap:11px;padding:0 13px;border:1px solid #d8e4ec;border-radius:12px;background:#fff;transition:.18s}.user-edit-input-wrap:focus-within{border-color:#1593b5;box-shadow:0 0 0 3px rgba(21,147,181,.13)}.user-edit-input-wrap.has-error{border-color:#dc6c6c;background:#fffafa}.user-edit-input-wrap>i{width:18px;flex:0 0 auto;color:#1593b5;text-align:center;font-size:.78rem}.user-edit-input-wrap input{width:100%;height:100%;min-width:0;padding:0;border:0;outline:0;background:transparent;color:#304657;font-size:.78rem;font-weight:600}.user-edit-input-wrap input::placeholder{color:#a5b3bd;font-weight:500}
    .password-toggle{width:32px;height:32px;display:grid;place-items:center;flex:0 0 auto;border:0;border-radius:8px;background:#edf5fa;color:#66859a;cursor:pointer}.password-toggle:hover{background:#def1f5;color:#117e9b}.user-edit-error{display:block;margin-top:6px;color:#c33e3e;font-size:.63rem;font-weight:700}.user-edit-roles{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.user-edit-role{position:relative;display:flex;align-items:center;gap:11px;min-height:66px;padding:11px 43px 11px 12px;border:1px solid #dbe7ef;border-radius:13px;background:#fbfdff;cursor:pointer;transition:.18s}.user-edit-role:hover{transform:translateY(-2px);border-color:#8fc9d8;background:#f3fbfc;box-shadow:0 7px 16px rgba(30,100,135,.09)}.user-edit-role:has(input:checked){border-color:#1593b5;background:#eaf8fa;box-shadow:inset 3px 0 0 #1593b5}
    .user-edit-role input{position:absolute;opacity:0;pointer-events:none}.user-edit-role-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:10px;background:#e5f6f8;color:#1593b5}.user-edit-role-copy strong,.user-edit-role-copy small{display:block}.user-edit-role-copy strong{color:#30495b;font-size:.73rem}.user-edit-role-copy small{margin-top:3px;color:#879aa6;font-size:.58rem}.user-edit-role-check{position:absolute;top:50%;right:13px;width:20px;height:20px;display:grid;place-items:center;border:1px solid #c5d6e0;border-radius:6px;background:#fff;color:transparent;font-size:.58rem;transform:translateY(-50%)}.user-edit-role input:checked~.user-edit-role-check{border-color:#1593b5;background:#1593b5;color:#fff}.user-edit-empty{padding:14px;border:1px dashed #bdd6e3;border-radius:12px;background:#f4fafc;color:#668294;font-size:.72rem}.user-edit-empty i{margin-right:7px;color:#1593b5}
    .user-edit-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:18px 28px;background:#f7fafc}.user-edit-actions a,.user-edit-actions button{min-height:43px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:11px;font-size:.7rem;font-weight:900;transition:.18s}.user-edit-actions a{border:1px solid #d5e2eb;background:#fff;color:#62798a}.user-edit-actions button{border:1px solid #1593b5;background:linear-gradient(135deg,#2563b9,#1593b5);color:#fff;cursor:pointer;box-shadow:0 7px 16px rgba(21,147,181,.22)}.user-edit-actions a:hover{border-color:#9fc4d5;color:#176e8c}.user-edit-actions button:hover{transform:translateY(-2px);filter:brightness(.94);box-shadow:0 10px 20px rgba(21,147,181,.3)}.role-error-alert{margin-top:10px;padding:11px 13px;border:1px solid #efb6b6;border-radius:10px;background:#fff1f1;color:#b33434;font-size:.68rem;font-weight:800}
    @media(max-width:700px){.user-edit-hero{min-height:220px;flex-direction:column;justify-content:center;padding:28px 20px;text-align:center}.user-edit-hero-copy>span{justify-content:center}.user-edit-back{width:100%;justify-content:center}.user-edit-content{width:calc(100% - 24px);margin-top:18px}.user-edit-section{padding:21px 17px}.user-edit-grid,.user-edit-roles{grid-template-columns:1fr}.user-edit-field-wide{grid-column:auto}.user-edit-actions{flex-direction:column-reverse;padding:16px}.user-edit-actions a,.user-edit-actions button{width:100%}}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('user-edit-form');
    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const roles = document.getElementById('user-edit-roles');
    function validatePasswordMatch() {
        confirmation.setCustomValidity(password.value && confirmation.value && password.value !== confirmation.value ? 'Las contraseñas no coinciden.' : '');
    }
    password.addEventListener('input', validatePasswordMatch);
    confirmation.addEventListener('input', validatePasswordMatch);
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.querySelector('i').className = visible ? 'fas fa-eye' : 'fas fa-eye-slash';
            button.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });
    form.addEventListener('submit', function (event) {
        if (!roles || roles.querySelectorAll('input[name="roles[]"]:checked').length > 0) return;
        event.preventDefault();
        const current = document.querySelector('.role-error-alert');
        if (current) current.remove();
        const error = document.createElement('div');
        error.className = 'role-error-alert';
        error.innerHTML = '<i class="fas fa-circle-exclamation"></i> Debes seleccionar al menos un rol para el usuario.';
        roles.insertAdjacentElement('afterend', error);
        error.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>
@endpush
