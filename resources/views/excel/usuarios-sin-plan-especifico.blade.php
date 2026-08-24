<table>
    <tr><td colspan="8">Usuarios no inscritos al plan: {{ $plan->nombre }}</td></tr>
    <tr><td colspan="8">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</td></tr>
    <tr><th>N.º</th><th>Nombre</th><th>Correo</th><th>Celular</th><th>Roles</th><th>Otros planes</th><th>Estado</th><th>Fecha de registro</th></tr>
    @foreach($users as $user)
        @php
            $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty();
            $isActive = $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty();
            $status = $isAdmin ? 'Administrador' : ($isActive ? 'Activo' : ($user->suscripciones->isEmpty() ? 'Sin plan' : 'Inactivo'));
        @endphp
        <tr><td>{{ $user->registration_number }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone ?: 'No registrado' }}</td><td>{{ $user->roles->pluck('nombre_rol')->implode(', ') ?: 'Sin rol' }}</td><td>{{ $user->suscripciones->pluck('plan.nombre')->filter()->unique()->implode(', ') ?: 'Sin plan' }}</td><td>{{ $status }}</td><td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td></tr>
    @endforeach
</table>
