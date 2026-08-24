@extends('layouts.app')

@section('title', 'Roles y Permisos')

@section('content')
<div class="roles-page">
    <header class="roles-hero">
        <div><span>Control de accesos</span><h1>Roles y Permisos</h1><p>Administra los roles del sistema y sus permisos de acceso</p></div>
        <div class="roles-hero-icon"><i class="fas fa-user-shield"></i></div>
    </header>

    <section class="roles-kpi-grid">
        <article class="roles-kpi kpi-orange"><div><span>Roles</span><strong>{{ $roles->count() }}</strong><small>Perfiles registrados</small></div><i class="fas fa-user-gear"></i></article>
        <article class="roles-kpi kpi-yellow"><div><span>Permisos</span><strong>{{ $permissions->count() }}</strong><small>Accesos disponibles</small></div><i class="fas fa-key"></i></article>
        <article class="roles-kpi kpi-green"><div><span>Usuarios</span><strong>{{ $users->total() }}</strong><small>En el sistema</small></div><i class="fas fa-users"></i></article>
        <article class="roles-kpi kpi-turquoise"><div><span>Asignaciones</span><strong>{{ $roles->sum(fn ($role) => $role->permissions->count()) }}</strong><small>Permisos vinculados</small></div><i class="fas fa-link"></i></article>
    </section>

    <main class="roles-content">
        @if(session('success'))<div class="roles-alert success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>@endif
        @if(isset($errors) && $errors->any())
            <div class="roles-alert error"><i class="fas fa-circle-exclamation"></i><div><strong>Revisa los datos del formulario.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        @endif

        <div class="roles-layout">
            <section class="roles-panel">
                <header class="roles-panel-heading">
                    <span class="roles-panel-icon orange"><i class="fas fa-users-gear"></i></span>
                    <div><h2>Roles del sistema</h2><p>Todos los perfiles registrados actualmente.</p><i></i></div>
                </header>
                <form action="{{ route('administrador.roles.store') }}" method="POST" class="new-role-form">
                    @csrf
                    <div><i class="fas fa-tag"></i><input id="nombre_rol" name="nombre_rol" type="text" value="{{ old('nombre_rol') }}" placeholder="Ej.: Editor de contenido"></div>
                    <button type="submit"><i class="fas fa-plus"></i>Añadir rol</button>
                </form>
                <div class="roles-list">
                    @forelse($roles as $index => $role)
                        @php $icons = ['fa-user-tie', 'fa-user-pen', 'fa-user-graduate', 'fa-user-gear', 'fa-user-plus']; @endphp
                        <article class="role-row">
                            <span class="role-row-icon"><i class="fas {{ $icons[$index % count($icons)] }}"></i></span>
                            <div class="role-row-copy"><strong>{{ $role->nombre_rol }}</strong><small><i class="fas fa-key"></i>{{ $role->permissions->count() }} permisos</small></div>
                            <span class="role-users"><strong>{{ $role->users->count() }}</strong><small>usuarios</small></span>
                        </article>
                    @empty
                        <div class="roles-empty"><i class="fas fa-user-gear"></i><strong>No hay roles registrados</strong><span>Añade el primer rol desde el formulario superior.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="roles-panel permissions-panel">
                <header class="roles-panel-heading">
                    <span class="roles-panel-icon turquoise"><i class="fas fa-table-cells-large"></i></span>
                    <div><h2>Matriz de permisos</h2><p>Activa o desactiva los accesos disponibles para cada rol.</p><i></i></div>
                </header>
                <div class="permissions-table-scroll">
                    <table class="permissions-table">
                        <thead><tr><th>Permiso / Funcionalidad</th>@foreach($roles as $role)<th>{{ $role->nombre_rol }}</th>@endforeach</tr></thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td><strong><i class="fas fa-circle-check"></i>{{ $permission->nombre_permiso }}</strong><small>{{ $permission->descripcion ?: 'Sin descripción registrada.' }}</small></td>
                                    @foreach($roles as $role)
                                        @php $hasPermission = $role->permissions->contains('id', $permission->id); @endphp
                                        <td>
                                            <form action="{{ route('administrador.roles.update', $role) }}" method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="nombre_rol" value="{{ $role->nombre_rol }}">
                                                @foreach($role->permissions as $rolePermission)
                                                    @if($rolePermission->id !== $permission->id)<input type="hidden" name="permissions[]" value="{{ $rolePermission->id }}">@endif
                                                @endforeach
                                                @if(!$hasPermission)<input type="hidden" name="permissions[]" value="{{ $permission->id }}">@endif
                                                <button type="submit" class="permission-toggle {{ $hasPermission ? 'is-active' : '' }}" aria-label="{{ $hasPermission ? 'Quitar' : 'Asignar' }} permiso {{ $permission->nombre_permiso }} a {{ $role->nombre_rol }}">
                                                    <span><i class="fas {{ $hasPermission ? 'fa-check' : 'fa-xmark' }}"></i></span><small>{{ $hasPermission ? 'Activo' : 'Inactivo' }}</small>
                                                </button>
                                            </form>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ $roles->count() + 1 }}"><div class="roles-empty"><i class="fas fa-inbox"></i><strong>No hay permisos creados</strong><span>Los permisos disponibles aparecerán aquí.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<style>
    .roles-page{min-height:100vh;background:#fff}.roles-hero{position:relative;isolation:isolate;overflow:hidden;min-height:168px;display:flex;align-items:center;justify-content:space-between;gap:30px;padding:28px 48px;color:#fff;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}.roles-hero:after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%)}.roles-hero>div:first-child>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.roles-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.roles-hero p{margin:7px 0 0;color:#dbeafe;font-size:.8rem}.roles-hero-icon{width:62px;height:62px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.25);border-radius:16px;background:rgba(255,255,255,.14);font-size:1.5rem}
    .roles-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:24px}.roles-kpi{--kpi-accent:#117e8c;--kpi-soft:#e6f4f5;--kpi-rgb:17,126,140;position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:132px;padding:21px;border:1px solid rgba(var(--kpi-rgb),.22);border-radius:16px;background:linear-gradient(135deg,#fff 35%,var(--kpi-soft));box-shadow:inset 0 4px 0 var(--kpi-accent),0 10px 24px rgba(45,66,34,.09);transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease}.roles-kpi:before{content:'';position:absolute;z-index:-1;top:-42px;right:-34px;width:125px;height:125px;border:22px solid rgba(var(--kpi-rgb),.09);border-radius:50%}.roles-kpi:after{content:'';position:absolute;z-index:-1;right:13px;bottom:8px;width:88px;height:45px;opacity:.22;background-image:radial-gradient(circle,var(--kpi-accent) 1.4px,transparent 1.6px);background-size:9px 9px;transform:rotate(-5deg)}.roles-kpi:hover{transform:translateY(-5px);border-color:rgba(var(--kpi-rgb),.38);box-shadow:inset 0 4px 0 var(--kpi-accent),0 17px 32px rgba(var(--kpi-rgb),.16)}.roles-kpi>div,.roles-kpi>i{position:relative;z-index:1}.roles-kpi span,.roles-kpi small{display:block}.roles-kpi span{color:#596170;font-size:.7rem;font-weight:900;letter-spacing:.025em;text-transform:uppercase}.roles-kpi strong{display:block;margin-top:9px;color:#263024;font-size:1.85rem;font-weight:900;line-height:1}.roles-kpi small{margin-top:8px;color:#7f8878;font-size:.62rem;font-weight:600}.roles-kpi>i{width:52px;height:52px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.55);border-radius:14px;background:var(--kpi-accent);color:#fff;font-size:1.18rem;box-shadow:0 8px 17px rgba(var(--kpi-rgb),.27),inset 0 1px 0 rgba(255,255,255,.28);transition:transform .22s ease}.roles-kpi:hover>i{transform:rotate(-6deg) scale(1.06)}.kpi-orange{--kpi-accent:#e37225;--kpi-soft:#fff0e6;--kpi-rgb:227,114,37}.kpi-yellow{--kpi-accent:#e3a122;--kpi-soft:#fff6df;--kpi-rgb:227,161,34}.kpi-green{--kpi-accent:#7da533;--kpi-soft:#f0f6e7;--kpi-rgb:125,165,51}.kpi-turquoise{--kpi-accent:#117e8c;--kpi-soft:#e6f4f5;--kpi-rgb:17,126,140}
    .roles-content{margin:0 24px 30px}.roles-alert{display:flex;align-items:flex-start;gap:11px;margin-bottom:16px;padding:13px 16px;border-radius:12px;font-size:.78rem}.roles-alert.success{border:1px solid #bfe3c5;background:#ecf8ee;color:#276738}.roles-alert.error{border:1px solid #f3c4c4;background:#fff0f0;color:#a72d2d}.roles-alert ul{margin:5px 0 0;padding-left:17px}.roles-layout{display:grid;grid-template-columns:minmax(300px,.72fr) minmax(540px,1.28fr);gap:18px;align-items:start}.roles-panel{overflow:hidden;border:1px solid #d8e3c7;border-radius:16px;background:#fff;box-shadow:0 9px 24px rgba(91,121,38,.1)}.roles-panel-heading{display:flex;align-items:center;gap:13px;padding:18px 20px;border-bottom:1px solid #e5ecd9}.roles-panel-icon{width:44px;height:44px;display:grid;place-items:center;flex:0 0 auto;border-radius:12px;font-size:1rem}.roles-panel-icon.orange{background:#ffedd5;color:#e37225}.roles-panel-icon.turquoise{background:#e6f4f5;color:#117e8c}.roles-panel-heading h2{margin:0;color:#30372b;font-size:1rem;font-weight:900}.roles-panel-heading p{margin:4px 0 0;color:#8a9380;font-size:.67rem}.roles-panel-heading div>i{display:block;width:54px;height:3px;margin-top:9px;border-radius:5px;background:#117e8c}.new-role-form{display:flex;gap:9px;padding:16px 20px;background:#fbfcf9}.new-role-form>div{position:relative;min-width:0;flex:1}.new-role-form>div i{position:absolute;top:16px;left:15px;color:#117e8c;font-size:.78rem}.new-role-form input{width:100%;height:46px;padding:0 13px 0 40px;border:1px solid #d8e3c7;border-radius:12px;background:#fff;color:#374151;font-size:.76rem;outline:0}.new-role-form input:focus{border-color:#7da533;box-shadow:0 0 0 3px rgba(125,165,51,.12)}.new-role-form>button{display:flex;align-items:center;justify-content:center;gap:8px;padding:0 16px;border:0;border-radius:12px;background:#7da533;color:#fff;font-size:.72rem;font-weight:900;box-shadow:0 6px 14px rgba(125,165,51,.22);transition:.18s}.new-role-form>button:hover{transform:translateY(-2px);background:#6d922c}.roles-list{display:grid;gap:1px;padding:0 20px 18px}.role-row{display:grid;grid-template-columns:38px minmax(0,1fr) auto;align-items:center;gap:11px;padding:13px 10px;border-bottom:1px solid #e5ecd9}.role-row:nth-child(even){background:#f4f8ed}.role-row-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#edf4e4;color:#638524}.role-row-copy strong,.role-row-copy small,.role-users strong,.role-users small{display:block}.role-row-copy strong{color:#35402d;font-size:.77rem}.role-row-copy small{margin-top:4px;color:#90998a;font-size:.62rem}.role-row-copy small i{margin-right:5px;color:#d58b13}.role-users{text-align:right}.role-users strong{color:#117e8c;font-size:.9rem}.role-users small{color:#9ca3af;font-size:.58rem}.roles-empty{padding:38px 20px;text-align:center}.roles-empty>i,.roles-empty strong,.roles-empty span{display:block}.roles-empty>i{margin-bottom:9px;color:#aabd8b;font-size:1.7rem}.roles-empty strong{color:#48513e;font-size:.78rem}.roles-empty span{margin-top:5px;color:#92998a;font-size:.66rem}
    .permissions-table-scroll{overflow-x:auto}.permissions-table{width:100%;min-width:650px;border-collapse:collapse}.permissions-table th{padding:13px 15px;border-right:1px solid rgba(255,255,255,.3);background:#7da533;color:#fff;text-align:center;font-size:.62rem;font-weight:900;letter-spacing:.03em;text-transform:uppercase}.permissions-table th:first-child{min-width:245px;text-align:left}.permissions-table th:last-child{border-right:0}.permissions-table td{padding:14px 15px;border-right:1px solid #d8e3c7;border-bottom:1px solid #dfe8d1;text-align:center;vertical-align:middle}.permissions-table td:first-child{text-align:left}.permissions-table td:last-child{border-right:0}.permissions-table tbody tr:nth-child(odd){background:#fff}.permissions-table tbody tr:nth-child(even){background:#f1f7e8}.permissions-table tbody tr:hover{background:#e9f2dc}.permissions-table td>strong,.permissions-table td>small{display:block}.permissions-table td>strong{color:#36402e;font-size:.72rem}.permissions-table td>strong i{margin-right:7px;color:#7da533}.permissions-table td>small{max-width:330px;margin-top:4px;color:#8d9585;font-size:.6rem;line-height:1.4}.permission-toggle{display:inline-flex;align-items:center;gap:7px;padding:4px 8px 4px 4px;border:1px solid #d8ded2;border-radius:999px;background:#fff;color:#969d90;transition:.18s}.permission-toggle>span{width:25px;height:25px;display:grid;place-items:center;border-radius:50%;background:#eef0eb;color:#a8aea2;font-size:.59rem}.permission-toggle small{font-size:.59rem;font-weight:900}.permission-toggle.is-active{border-color:#b8cf96;background:#edf4e4;color:#587923}.permission-toggle.is-active>span{background:#7da533;color:#fff}.permission-toggle:hover{transform:translateY(-1px);box-shadow:0 5px 11px rgba(91,121,38,.13)}
    @media(max-width:1100px){.roles-layout{grid-template-columns:1fr}.roles-kpi-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:640px){.roles-hero{min-height:180px;padding:24px 20px}.roles-hero-icon{display:none}.roles-kpi-grid{grid-template-columns:1fr;margin:14px 12px}.roles-content{margin:0 12px 20px}.new-role-form{flex-direction:column}.new-role-form>button{height:44px}}
</style>
@endsection
