@extends('layouts.app')

@section('title', 'Usuarios eliminados')

@section('content')
<div class="deleted-users-page">
    <header class="deleted-users-hero">
        <div>
            @include('administrador.usuarios.dashboard-back')
            <h1>Usuarios eliminados</h1>
        </div>
        <a href="{{ route('administrador.usuarios.index') }}"><i class="fas fa-arrow-left"></i>Volver a usuarios</a>
    </header>

    <main class="deleted-users-content">
        @if(session('success'))<div class="deleted-alert success"><i class="fas fa-circle-check"></i>{{ session('success') }}</div>@endif
        @if(session('error'))<div class="deleted-alert error"><i class="fas fa-circle-exclamation"></i>{{ session('error') }}</div>@endif

        <div class="deleted-table-heading">
            <div><i class="fas fa-user-clock"></i><span><strong>Usuarios en la papelera</strong><small>{{ $users->total() }} usuario(s) eliminado(s)</small></span></div>
            <span class="deleted-order"><i class="fas fa-arrow-down"></i>Eliminados recientemente</span>
        </div>

        <section class="deleted-table-wrap">
            <div class="deleted-table-scroll">
                <table class="deleted-table">
                    <thead><tr><th>Usuario</th><th>Correo electrónico</th><th>Rol</th><th>Fecha de eliminación</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $initials = collect(preg_split('/\s+/u', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
                                $roles = $user->roles->pluck('nombre_rol')->implode(', ') ?: 'Sin rol asignado';
                            @endphp
                            <tr>
                                <td><div class="deleted-user"><span>{{ $initials ?: 'U' }}</span><strong>{{ $user->name }}</strong></div></td>
                                <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                                <td><span class="deleted-role">{{ $roles }}</span></td>
                                <td><span class="deleted-date"><strong>{{ $user->deleted_at->format('d/m/Y') }}</strong><small>{{ $user->deleted_at->format('H:i') }} · {{ $user->deleted_at->diffForHumans() }}</small></span></td>
                                <td>
                                    <div class="deleted-actions">
                                        <form action="{{ route('administrador.usuarios.restore', $user->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="restore-user"><i class="fas fa-rotate-left"></i>Restaurar</button>
                                        </form>
                                        <form action="{{ route('administrador.usuarios.force-destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Eliminar permanentemente a {{ addslashes($user->name) }}? Esta acción no se puede deshacer.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="destroy-user"><i class="fas fa-trash-can"></i>Eliminar definitivamente</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="deleted-empty"><i class="fas fa-trash-arrow-up"></i><strong>La papelera está vacía</strong><span>No hay usuarios eliminados actualmente.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())<div class="deleted-pagination">{{ $users->links('componentes.paginacion-es') }}</div>@endif
        </section>
    </main>
</div>

<style>
    .deleted-users-page{min-height:100vh;background:#fff}.deleted-users-hero{position:relative;isolation:isolate;overflow:hidden;min-height:168px;display:flex;align-items:center;justify-content:space-between;gap:30px;padding:28px 48px;color:#fff;background:linear-gradient(135deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(225deg,#4f46e5 25%,transparent 25%) -50px 0,linear-gradient(315deg,#4f46e5 25%,transparent 25%),linear-gradient(45deg,#4f46e5 25%,transparent 25%),linear-gradient(to bottom,#3b82f6,#2563eb);background-color:#1d4ed8;background-size:100px 100px,100px 100px,100px 100px,100px 100px,100% 100%}.deleted-users-hero:after{content:'';position:absolute;z-index:-1;inset:0;background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%)}.deleted-users-hero>div>span{display:block;margin-bottom:7px;color:#dbeafe;font-size:.68rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.deleted-users-hero h1{margin:0;color:#fff;font-size:clamp(1.55rem,3vw,2.25rem);font-weight:900;letter-spacing:-.04em}.deleted-users-hero p{margin:7px 0 0;color:#dbeafe;font-size:.8rem}.deleted-users-hero>a{display:flex;align-items:center;gap:9px;padding:11px 15px;border:1px solid rgba(255,255,255,.3);border-radius:11px;background:rgba(255,255,255,.14);color:#fff;font-size:.72rem;font-weight:900;transition:.18s}.deleted-users-hero>a:hover{transform:translateY(-2px);background:#fff;color:#4f46e5}
    .deleted-users-content{margin:24px}.deleted-alert{display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:13px 16px;border-radius:12px;font-size:.78rem;font-weight:700}.deleted-alert.success{border:1px solid #bfe3c5;background:#ecf8ee;color:#276738}.deleted-alert.error{border:1px solid #f3c4c4;background:#fff0f0;color:#a72d2d}.deleted-table-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:12px;padding:12px 15px;border:1px solid #e2ead5;border-radius:14px;background:#f9fbf5}.deleted-table-heading>div{display:flex;align-items:center;gap:11px}.deleted-table-heading>div>i{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:#ffedd5;color:#e37225}.deleted-table-heading strong,.deleted-table-heading small{display:block}.deleted-table-heading strong{color:#31382b;font-size:.8rem}.deleted-table-heading small{margin-top:2px;color:#8a9380;font-size:.62rem}.deleted-order{display:flex;align-items:center;gap:7px;color:#638524;font-size:.69rem;font-weight:800}
    .deleted-table-wrap{overflow:hidden;border:1px solid #d8e3c7;border-radius:16px;background:#fff;box-shadow:0 9px 24px rgba(91,121,38,.12)}.deleted-table-scroll{overflow-x:auto}.deleted-table{width:100%;min-width:950px;border-collapse:collapse}.deleted-table th{padding:15px 18px;border-right:1px solid rgba(255,255,255,.3);background:#7da533;color:#fff;text-align:left;font-size:.67rem;font-weight:900;letter-spacing:.04em;text-transform:uppercase}.deleted-table th:last-child{border-right:0}.deleted-table td{padding:15px 18px;border-right:1px solid #d8e3c7;border-bottom:1px solid #dfe8d1;color:#4b5563;font-size:.76rem;vertical-align:middle}.deleted-table td:last-child{border-right:0}.deleted-table tbody tr:nth-child(odd){background:#fff}.deleted-table tbody tr:nth-child(even){background:#f1f7e8}.deleted-table tbody tr:hover{background:#e9f2dc}.deleted-user{display:flex;align-items:center;gap:10px}.deleted-user>span{width:39px;height:39px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:linear-gradient(135deg,#7da533,#117e8c);color:#fff;font-size:.67rem;font-weight:900}.deleted-user strong{color:#30372b;font-size:.78rem}.deleted-table td>a{color:#0d6975;font-weight:650}.deleted-table td>a:hover{text-decoration:underline}.deleted-role{display:inline-flex;padding:6px 9px;border-radius:8px;background:#edf4e4;color:#587923;font-size:.65rem;font-weight:800}.deleted-date strong,.deleted-date small{display:block}.deleted-date strong{color:#39432f;font-size:.74rem}.deleted-date small{margin-top:3px;color:#89917f;font-size:.61rem}.deleted-actions{display:flex;align-items:center;gap:7px}.deleted-actions button{display:flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:0 11px;border:0;border-radius:9px;font-size:.64rem;font-weight:900;transition:.18s}.deleted-actions button:hover{transform:translateY(-2px)}.restore-user{background:#edf4e4;color:#587923}.restore-user:hover{background:#7da533;color:#fff}.destroy-user{background:#fff0ed;color:#b23e2c}.destroy-user:hover{background:#c94b37;color:#fff}.deleted-empty{padding:48px 20px;text-align:center}.deleted-empty i,.deleted-empty strong,.deleted-empty span{display:block}.deleted-empty i{margin-bottom:10px;color:#aabd8b;font-size:2rem}.deleted-empty strong{color:#48513e}.deleted-empty span{margin-top:5px;color:#92998a}.deleted-pagination{padding:15px 18px;border-top:1px solid #dfe8d1;background:#fbfcf9}
    html.admin-dark-nav .deleted-users-page{background:#141414;color:#e5e5e5}
    html.admin-dark-nav .deleted-alert.success{background:#1c3928;border-color:#376f49;color:#b8edc4}
    html.admin-dark-nav .deleted-alert.error{background:#472a2a;border-color:#855050;color:#ffc1c1}
    html.admin-dark-nav .deleted-table-heading,html.admin-dark-nav .deleted-table-wrap{background:#202020;border-color:#353535;box-shadow:0 9px 24px rgba(0,0,0,.2)}
    html.admin-dark-nav .deleted-table-heading>div>i{background:#493021;color:#ffad70}
    html.admin-dark-nav .deleted-table-heading strong,html.admin-dark-nav .deleted-user strong,html.admin-dark-nav .deleted-date strong,html.admin-dark-nav .deleted-empty strong{color:#f5f5f5}
    html.admin-dark-nav .deleted-table-heading small,html.admin-dark-nav .deleted-date small,html.admin-dark-nav .deleted-empty span{color:#c0c0c0}
    html.admin-dark-nav .deleted-order{color:#b9df91}
    html.admin-dark-nav .deleted-table th{background:#426129;border-color:#66813d}
    html.admin-dark-nav .deleted-table td{border-color:#3b3b3b;color:#dedede}
    html.admin-dark-nav .deleted-table tbody tr:nth-child(odd){background:#202020}
    html.admin-dark-nav .deleted-table tbody tr:nth-child(even){background:#292929}
    html.admin-dark-nav .deleted-table tbody tr:hover{background:#333d2d}
    html.admin-dark-nav .deleted-table td>a{color:#8dd7e3}
    html.admin-dark-nav .deleted-role,html.admin-dark-nav .restore-user{background:#35442b;color:#c8eba5}
    html.admin-dark-nav .restore-user:hover{background:#7da533;color:#fff}
    html.admin-dark-nav .destroy-user{background:#472a2a;color:#ffc1c1}
    html.admin-dark-nav .destroy-user:hover{background:#c94b37;color:#fff}
    html.admin-dark-nav .deleted-empty i{color:#9cbe61}
    html.admin-dark-nav .deleted-pagination{background:#242424;border-color:#353535;color:#e5e5e5}
    html.admin-dark-nav .deleted-pagination a,html.admin-dark-nav .deleted-pagination span{color:#e5e5e5}
    html.admin-dark-nav .deleted-pagination .bg-gray-100,html.admin-dark-nav .deleted-pagination .bg-white{background:#303030!important;border-color:#4a4a4a!important}
    @media(max-width:700px){.deleted-users-hero{min-height:195px;align-items:flex-start;flex-direction:column;padding:24px 20px}.deleted-users-hero>a{width:100%;justify-content:center}.deleted-users-content{margin:14px 12px}.deleted-table-heading{align-items:flex-start;flex-direction:column}.deleted-order{padding-left:47px}}
    @media(max-width:1100px){
        .deleted-users-hero{justify-content:center;text-align:center}
        .deleted-users-hero>a{position:absolute;right:24px;top:50%;transform:translateY(-50%)}
        .deleted-users-hero>a:hover{transform:translateY(calc(-50% - 2px))}
    }
    @media(max-width:700px){
        .deleted-users-hero{min-height:170px;align-items:center;flex-direction:column;gap:16px;padding:22px 16px}
        .deleted-users-hero>a{position:static;width:100%;max-width:260px;justify-content:center;transform:none}
        .deleted-users-hero>a:hover{transform:translateY(-2px)}
    }
</style>
@endsection
