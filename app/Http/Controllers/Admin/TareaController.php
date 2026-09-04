<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\PlanMarketing;
use App\Models\Tarea;
use App\Models\User;
use App\Services\SocialContentPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TareaController extends Controller
{
    public function create(Campania $campania)
    {
        $asignables = User::whereHas('roles', function ($query) {
            $query->whereIn('nombre_rol', ['Disenador', 'Community Manager', 'Administrador', 'Super Administrador'])
                ->orWhereIn('nombre_rol', ['Diseñador']);
        })->with('roles')->get();

        $cm = User::with('roles')->find($campania->community_manager_id);
        if ($cm && ! $asignables->contains('id', $cm->id)) {
            $asignables->push($cm);
        }

        $adminActual = Auth::user()?->loadMissing('roles');
        if ($adminActual && ! in_array($adminActual->id, $asignables->pluck('id')->toArray(), true)) {
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
            'estado' => 'no_iniciado',
            'prioridad' => $request->prioridad,
            'campania_id' => $campania->id,
            'creador_id' => Auth::id(),
            'asignado_id' => $request->asignado_id,
        ]);

        return redirect()->route('administrador.campañas.show', $campania->id)
            ->with('success', 'Tarea creada exitosamente');
    }

    public function show(Tarea $tarea)
    {
        return view('administrador.tareas.show', compact('tarea'));
    }

    public function edit(Tarea $tarea)
    {
        $asignables = User::whereHas('roles', function ($query) {
            $query->whereIn('nombre_rol', ['Disenador', 'Community Manager', 'Administrador', 'Super Administrador'])
                ->orWhereIn('nombre_rol', ['Diseñador']);
        })->with('roles')->get();

        $cm = User::with('roles')->find($tarea->campania->community_manager_id);
        if ($cm && ! $asignables->contains('id', $cm->id)) {
            $asignables->push($cm);
        }

        $adminActual = Auth::user()?->loadMissing('roles');
        if ($adminActual && ! in_array($adminActual->id, $asignables->pluck('id')->toArray(), true)) {
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

        return redirect()->route('administrador.campañas.show', $tarea->campania_id)
            ->with('success', 'Tarea actualizada exitosamente');
    }

    public function updateEstado(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'estado' => 'required|in:no_iniciado,pendiente,en_curso,entregado,reformular,aprobado,publicado',
        ]);

        $tarea->update(['estado' => $validated['estado']]);

        return response()->json([
            'message' => 'Estado de la tarea actualizado.',
            'estado' => $tarea->estado,
        ]);
    }

    public function obtenerRecomendacionIA(Campania $campania)
    {
        $campania->loadMissing('cliente.empresas');

        $fechaInicioCampania = $this->formatearFecha($campania->fecha_inicio);
        $fechaFinCampania = $this->formatearFecha($campania->fecha_fin);

        $empresa = $campania->cliente?->empresas?->sortByDesc('id')->first();
        if (! $empresa) {
            return response()->json(['error' => 'No se encontro una empresa asociada al cliente de esta campaña.'], 404);
        }

        $planMarketing = PlanMarketing::where('empresa_id', $empresa->id)
            ->latest()
            ->first();

        if (! $planMarketing) {
            return response()->json(['error' => 'No se encontro un plan de marketing para esta empresa.'], 404);
        }

        $seccionCalendario = $this->extraerSeccionCalendario($planMarketing->contenido);
        if ($seccionCalendario === '') {
            return response()->json(['error' => 'No se encontro la seccion de calendario operativo mensual en el plan de marketing.'], 422);
        }
        $seccionCalendario = SocialContentPolicy::sanitize($seccionCalendario);
        $reglasCanales = SocialContentPolicy::promptRules();

        $prompt = <<<EOT
Actua como coordinador senior de operaciones de marketing. Debes recomendar UNA sola tarea concreta y ejecutable para una campaña, usando exclusivamente la seccion "## 7 Calendario operativo mensual" del plan de marketing proporcionado.

OBJETIVO:
- Sugerir una tarea lista para registrarse en el formulario de tareas.
- La tarea debe ser especifica, accionable y coherente con el calendario operativo mensual.
- Debe representar una accion inmediata y realista dentro de la campaña.

DATOS DE LA CAMPANA:
- Nombre de la campaña: {$campania->nombre}
- Descripcion de la campaña: {$campania->descripcion}
- Fecha de inicio permitida: {$fechaInicioCampania}
- Fecha limite maxima permitida: {$fechaFinCampania}

SECCION DEL PLAN DE MARKETING A USAR:
---
{$seccionCalendario}
---

INSTRUCCIONES ESTRICTAS:
{$reglasCanales}
- Usa solo la informacion del calendario operativo mensual.
- No inventes acciones fuera del calendario.
- No devuelvas varias tareas. Solo una.
- El titulo debe ser corto y claro.
- La descripcion debe explicar objetivo, entregables y criterio de ejecucion.
- La prioridad debe ser una de estas: baja, media, alta, urgente.
- El rol sugerido debe ser exactamente uno de estos: Community Manager, Diseñador, Administrador, Super Administrador.
- fecha_inicio y fecha_limite deben estar dentro del rango permitido de la campaña.
- Si el calendario no trae una fecha exacta, propone una fecha razonable dentro del rango de la campaña.
- Responde SOLO en JSON valido, sin markdown, sin explicaciones y sin bloques de codigo.

FORMATO JSON OBLIGATORIO:
{
  "titulo": "...",
  "descripcion": "...",
  "prioridad": "media",
  "fecha_inicio": "YYYY-MM-DD",
  "fecha_limite": "YYYY-MM-DD",
  "rol_sugerido": "Community Manager"
}
EOT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.groq.key'),
            'Content-Type' => 'application/json',
        ])
            ->withOptions([
                'verify' => false,
            ])
            ->timeout(120)
            ->post(config('services.groq.url'), [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
                'stream' => false,
            ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'Hubo un error al generar la recomendacion con IA.'], 500);
        }

        $contenido = $response->json('choices.0.message.content') ?? '';
        $recomendacion = $this->decodificarJsonIA($contenido);

        if (! $recomendacion) {
            return response()->json(['error' => 'La IA devolvio una respuesta invalida para la recomendacion de tarea.'], 500);
        }

        return response()->json([
            'titulo' => SocialContentPolicy::sanitize(trim((string) ($recomendacion['titulo'] ?? ''))),
            'descripcion' => SocialContentPolicy::sanitize(trim((string) ($recomendacion['descripcion'] ?? ''))),
            'prioridad' => trim((string) ($recomendacion['prioridad'] ?? 'media')),
            'fecha_inicio' => trim((string) ($recomendacion['fecha_inicio'] ?? $fechaInicioCampania)),
            'fecha_limite' => trim((string) ($recomendacion['fecha_limite'] ?? $fechaFinCampania)),
            'rol_sugerido' => trim((string) ($recomendacion['rol_sugerido'] ?? 'Community Manager')),
        ]);
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
                'id' => $tarea->id,
                'title' => $tarea->titulo,
                'start' => $this->formatearFecha($tarea->fecha_inicio),
                'end' => $this->formatearFecha($tarea->fecha_limite),
                'color' => $color,
                'url' => route('administrador.tareas.ver-subidas', $tarea->id),
                'extendedProps' => [
                    'prioridad' => $tarea->prioridad,
                    'estado' => $tarea->estado,
                    'asignado' => $tarea->asignado?->name ?? 'Sin asignar',
                ],
            ];
        }

        return view('administrador.campañas.calendario', [
            'campania' => $campania,
            'eventos' => $eventos,
        ]);
    }

    private function extraerSeccionCalendario(string $contenidoPlan): string
    {
        if (preg_match('/^##\s*7\s+Calendario operativo mensual(.*?)(?=^##\s*\d+|\z)/msi', $contenidoPlan, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function formatearFecha($fecha): string
    {
        if ($fecha instanceof Carbon) {
            return $fecha->format('Y-m-d');
        }

        if ($fecha instanceof \DateTimeInterface) {
            return $fecha->format('Y-m-d');
        }

        return Carbon::parse((string) $fecha)->format('Y-m-d');
    }

    private function decodificarJsonIA(string $contenido): ?array
    {
        $contenido = trim($contenido);

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $contenido, $matches)) {
            $contenido = $matches[1];
        } elseif (preg_match('/(\{.*\})/s', $contenido, $matches)) {
            $contenido = $matches[1];
        }

        $data = json_decode($contenido, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($data) ? $data : null;
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
