<table>
    <tr><td colspan="8">Usuarios sin ningún plan</td></tr>
    <tr><td colspan="8">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</td></tr>
    <tr><th>N.º</th><th>Nombre</th><th>Correo</th><th>Celular</th><th>Roles</th><th>Planes</th><th>Estado</th><th>Fecha de registro</th></tr>
    @foreach($users as $user)
        @php $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty(); @endphp
        <tr><td>{{ $user->registration_number }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone ?: 'No registrado' }}</td><td>{{ $user->roles->pluck('nombre_rol')->implode(', ') ?: 'Sin rol' }}</td><td>Sin plan</td><td>{{ $isAdmin ? 'Administrador' : 'Sin plan' }}</td><td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td></tr>
    @endforeach
</table>
