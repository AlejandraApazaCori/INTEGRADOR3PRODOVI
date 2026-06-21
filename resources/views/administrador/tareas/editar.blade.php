@extends('layouts.app')

@section('title', 'Editar Tarea')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-semibold mb-6">Editar Tarea: {{ $tarea->titulo }}</h1>

        <form action="{{ route('administrador.tareas.update', $tarea->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1">
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">TÃ­tulo *</label>
                    <input type="text" name="titulo" id="titulo" value="{{ $tarea->titulo }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="col-span-1">
                    <label for="prioridad" class="block text-sm font-medium text-gray-700 mb-1">Prioridad *</label>
                    <select name="prioridad" id="prioridad" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="media" {{ $tarea->prioridad == 'media' ? 'selected' : '' }}>Media</option>
                        <option value="baja" {{ $tarea->prioridad == 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="alta" {{ $tarea->prioridad == 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="urgente" {{ $tarea->prioridad == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                <div class="col-span-1">
                    <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio *</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $tarea->fecha_inicio->format('Y-m-d') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        min="{{ \Carbon\Carbon::parse($tarea->campania->fecha_inicio)->format('Y-m-d') }}"
                        max="{{ \Carbon\Carbon::parse($tarea->campania->fecha_fin)->format('Y-m-d') }}">
                </div>

                <div class="col-span-1">
                    <label for="fecha_limite" class="block text-sm font-medium text-gray-700 mb-1">Fecha LÃ­mite *</label>
                    <input type="date" name="fecha_limite" id="fecha_limite" value="{{ $tarea->fecha_limite->format('Y-m-d') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        min="{{ \Carbon\Carbon::parse($tarea->campania->fecha_inicio)->format('Y-m-d') }}"
                        max="{{ \Carbon\Carbon::parse($tarea->campania->fecha_fin)->format('Y-m-d') }}">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar usuarios</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="filtro_rol" class="block text-xs font-medium text-gray-500 mb-1">Por rol</label>
                            <select id="filtro_rol" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los roles</option>
                                <option value="Super Administrador">Super Administrador</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Community Manager">Community Manager</option>
                                <option value="Diseñador">Diseñador</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="filtro_nombre" class="block text-xs font-medium text-gray-500 mb-1">Por nombre</label>
                            <input type="text" id="filtro_nombre" placeholder="Buscar por nombre..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <label for="asignado_id" class="block text-sm font-medium text-gray-700 mb-1">Asignar a *</label>
                    <select name="asignado_id" id="asignado_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach($asignables as $user)
                            <option value="{{ $user->id }}" data-rol="{{ $user->roles->first()->nombre_rol ?? 'Sin rol' }}"
                                {{ $tarea->asignado_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->roles->first()->nombre_rol ?? 'Sin rol' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">DescripciÃ³n *</label>
                    <textarea name="descripcion" id="descripcion" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $tarea->descripcion }}</textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('administrador.campaÃ±as.show', $tarea->campania_id) }}"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Actualizar Tarea
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtroRol = document.getElementById('filtro_rol');
    const filtroNombre = document.getElementById('filtro_nombre');
    const selectAsignado = document.getElementById('asignado_id');
    const options = Array.from(selectAsignado.options);
    
    function filtrarUsuarios() {
        const rolSeleccionado = filtroRol.value.toLowerCase();
        const nombreBusqueda = filtroNombre.value.toLowerCase();
        
        options.forEach(option => {
            const rol = option.getAttribute('data-rol').toLowerCase();
            const nombre = option.text.toLowerCase();
            
            const coincideRol = rolSeleccionado === '' || rol.includes(rolSeleccionado);
            const coincideNombre = nombre.includes(nombreBusqueda);
            
            option.style.display = (coincideRol && coincideNombre) ? '' : 'none';
        });
    }
    
    filtroRol.addEventListener('change', filtrarUsuarios);
    filtroNombre.addEventListener('input', filtrarUsuarios);
});
</script>
@endsection