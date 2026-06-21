<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
public function create(Campania $campania)
{
    // Obtener usuarios con roles asignables (incluyendo administradores)
    $asignables = User::whereHas('roles', function($query) {
        $query->whereIn('nombre_rol', ['Diseñador', 'Community Manager', 'Administrador', 'Super Administrador']);
    })->get();

    // Asegurarse de incluir al CM de la campaÃ±a aunque no tenga el rol
    $cm = User::find($campania->community_manager_id);
    if ($cm && !$asignables->contains($cm)) {
        $asignables->push($cm);
    }

    // Incluir tambiÃ©n al administrador actual si no estÃ¡ ya en la lista
    $adminActual = Auth::user();
    if (!in_array($adminActual->id, $asignables->pluck('id')->toArray())) {
        $asignables->push($adminActual);
    }

    return view('administrador.tareas.crear', compact('campania', 'asignables'));
}
    public function store(Request $request, Campania $campania)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_limite' => 'required|date|after_or_equal:fecha_inicio',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'asignado_id' => 'required|exists:users,id',
        ]);

        Tarea::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_limite' => $request->fecha_limite,
            'prioridad' => $request->prioridad,
            'campania_id' => $campania->id,
            'creador_id' => Auth::id(),
            'asignado_id' => $request->asignado_id,
        ]);

        return redirect()->route('administrador.campaÃ±as.show', $campania->id)
            ->with('success', 'Tarea creada exitosamente');
    }
    public function show(Tarea $tarea)
    {
        return view('administrador.tareas.show', compact('tarea'));
    }

    public function edit(Tarea $tarea)
{
    // Obtener usuarios con roles asignables (similar al mÃ©todo create)
    $asignables = User::whereHas('roles', function($query) {
        $query->whereIn('nombre_rol', ['Diseñador', 'Community Manager', 'Administrador', 'Super Administrador']);
    })->get();

    // Asegurarse de incluir al CM de la campaÃ±a aunque no tenga el rol
    $cm = User::find($tarea->campania->community_manager_id);
    if ($cm && !$asignables->contains($cm)) {
        $asignables->push($cm);
    }

    // Incluir tambiÃ©n al administrador actual si no estÃ¡ ya en la lista
    $adminActual = Auth::user();
    if (!in_array($adminActual->id, $asignables->pluck('id')->toArray())) {
        $asignables->push($adminActual);
    }

    return view('administrador.tareas.editar', compact('tarea', 'asignables'));
}

public function update(Request $request, Tarea $tarea)
{
    $request->validate([
        'titulo' => 'required|string|max:100',
        'descripcion' => 'required|string',
        'fecha_inicio' => 'required|date',
        'fecha_limite' => 'required|date|after_or_equal:fecha_inicio',
        'prioridad' => 'required|in:baja,media,alta,urgente',
        'asignado_id' => 'required|exists:users,id',
    ]);

    $tarea->update([
        'titulo' => $request->titulo,
        'descripcion' => $request->descripcion,
        'fecha_inicio' => $request->fecha_inicio,
        'fecha_limite' => $request->fecha_limite,
        'prioridad' => $request->prioridad,
        'asignado_id' => $request->asignado_id,
    ]);

    return redirect()->route('administrador.campaÃ±as.show', $tarea->campania_id)
        ->with('success', 'Tarea actualizada exitosamente');
}
    public function calendario(Campania $campania)
{
    $tareas = $campania->tareas()
        ->with('asignado')
        ->get();
    
    $eventos = [];
    
    foreach ($tareas as $tarea) {
        $color = $this->getColorForPriority($tarea->prioridad);
        
        $eventos[] = [
            'title' => $tarea->titulo . ' - ' . $tarea->asignado->name,
            'start' => $tarea->fecha_inicio->format('Y-m-d'),
            'end' => $tarea->fecha_limite->format('Y-m-d'),
            'color' => $color,
            'url' => route('administrador.tareas.ver-subidas', $tarea->id),
            'extendedProps' => [
                'prioridad' => $tarea->prioridad,
                'estado' => $tarea->estado,
                'asignado' => $tarea->asignado->name
            ]
        ];
    }
    
    return view('administrador.campaÃ±as.calendario', [
        'campania' => $campania,
        'eventos' => $eventos
    ]);
}

private function getColorForPriority($prioridad)
{
    switch ($prioridad) {
        case 'urgente': return '#dc3545';
        case 'alta': return '#fd7e14';
        case 'media': return '#007bff';
        case 'baja': return '#28a745';
        default: return '#6c757d';
    }
}

}