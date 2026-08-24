<table>
    <tr><td colspan="15">{{ $reportTitle }}</td></tr>
    <tr><td colspan="15">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</td></tr>
    <tr>
        <td colspan="8"></td>
        <th>Tipo de rol</th><th>Cantidad</th><th>Porcentaje</th><td></td>
        <th>Estado</th><th>Cantidad</th><th>Porcentaje</th>
    </tr>
    @for($index = 0; $index < 16; $index++)
        <tr>
            <td colspan="8"></td>
            @if(isset($roleStats[$index]))
                <td>{{ $roleStats[$index]['label'] }}</td><td>{{ $roleStats[$index]['count'] }}</td><td>{{ $roleStats[$index]['percentage'] }}</td>
            @else
                <td></td><td></td><td></td>
            @endif
            <td></td>
            @if(isset($statusStats[$index]))
                <td>{{ $statusStats[$index]['label'] }}</td><td>{{ $statusStats[$index]['count'] }}</td><td>{{ $statusStats[$index]['percentage'] }}</td>
            @else
                <td></td><td></td><td></td>
            @endif
        </tr>
    @endfor
    <tr><th>N.º</th><th>Nombre</th><th>Correo</th><th>Celular</th><th>Roles</th><th>Planes</th><th>Estado</th><th>Fecha de registro</th></tr>
    @foreach($users as $user)
        @php
            $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty();
            $isActive = $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty();
            $status = $isAdmin ? 'Administrador' : ($isActive ? 'Activo' : ($user->suscripciones->isEmpty() ? 'Sin plan' : 'Inactivo'));
        @endphp
        <tr><td>{{ $user->registration_number }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone ?: 'No registrado' }}</td><td>{{ $user->roles->pluck('nombre_rol')->implode(', ') ?: 'Sin rol' }}</td><td>{{ $user->suscripciones->pluck('plan.nombre')->filter()->unique()->implode(', ') ?: 'Sin plan' }}</td><td>{{ $status }}</td><td>{{ optional($user->created_at)->format('d/m/Y H:i') }}</td></tr>
    @endforeach
</table>
