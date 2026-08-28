<?php

namespace App\Services;

use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class CampaignBlueprintService
{
    public function __construct(
        private readonly CampaignTaskPrefillService $taskPrefillService,
        private readonly CampaignBriefPrefillService $briefPrefillService,
        private readonly CampaignAudienceService $audienceService,
    ) {}

    public function generate(Suscripcion $suscripcion, PlanMarketing $plan, string $mode): array
    {
        $suscripcion->loadMissing('empresa.respuestasCuestionario.pregunta', 'plan.planCaracteristicas.caracteristica');
        $empresa = $suscripcion->empresa;
        $inicio = now()->toDateString();
        $fin = ($suscripcion->vigencia_activada_at
            ? $suscripcion->fecha_fin
            : now()->copy()->addMonthNoOverflow())->toDateString();
        $recursos = $suscripcion->plan?->planCaracteristicas
            ?->map(fn ($item) => [
                'nombre' => $item->caracteristica?->nombre,
                'cantidad' => $item->cantidad,
                'frecuencia' => $item->frecuencia,
            ])->values()->all() ?? [];
        $cuestionario = $empresa->respuestasCuestionario
            ->map(fn ($answer) => [
                'pregunta' => $answer->pregunta?->pregunta,
                'respuesta' => $answer->respuesta,
            ])->filter(fn (array $answer) => filled($answer['pregunta']) && filled($answer['respuesta']))
            ->values()->all();
        $calendarTasks = $this->taskPrefillService->build($plan, $inicio, $fin);

        if ($mode === 'automatico') {
            $automaticBlueprint = $this->deterministicBlueprint(
                $suscripcion,
                $plan,
                $calendarTasks,
                $inicio,
                $fin
            );
            $result = $this->normalize(
                $this->sanitize($automaticBlueprint),
                $suscripcion,
                $plan,
                $calendarTasks,
                $inicio,
                $fin
            );
            $result['generation_source'] = 'automatic_rules';
            $result['generation_warning'] = null;

            return $result;
        }

        $reglasCanales = SocialContentPolicy::promptRules();
        $modo = 'Genera una propuesta completa y editable que ayude al administrador a tomar las decisiones finales.';

        $prompt = <<<PROMPT
Actúa como director senior de operaciones de una agencia de marketing digital.
{$modo}

Debes convertir el brief ejecutivo y el plan de marketing en UNA campaña ejecutable. No hagas un simple resumen.

EMPRESA: {$empresa->nombre_empresa}
FECHA INICIAL PERMITIDA: {$inicio}
FECHA FINAL MÁXIMA: {$fin}

BRIEF EJECUTIVO:
---
{$empresa->resumen_ejecutivo}
---

PLAN DE MARKETING:
---
{$plan->contenido}
---

CUESTIONARIO EMPRESARIAL (JSON, FUENTE DIRECTA DEL CLIENTE):
{$this->json($cuestionario)}

RECURSOS CONTRATADOS (JSON):
{$this->json($recursos)}

REGLAS:
{$reglasCanales}
- Respeta estrictamente cantidades, frecuencias, canales y recursos contratados.
- Crea una campaña lista para ejecutar, no una propuesta genérica.
- Usa el calendario operativo del plan como fuente exacta para publicaciones: no cambies cantidades, ideas, formatos ni frecuencia.
- Crea como máximo 20 tareas concretas y breves. Cada descripción debe tener hasta 700 caracteres.
- El nombre no debe superar 100 caracteres, el tono 120 y cada indicador 100.
- Divide el público en segmentos. Para cada uno devuelve solamente tipo o perfil, rango de edades y una descripción esencial de hasta 300 caracteres.
- No copies encabezados de tablas ni juntes todos los públicos en un solo texto.
- Devuelve como máximo 6 indicadores y usa únicamente Facebook, Instagram, TikTok o WhatsApp como canales.
- Incluye planificación, copy, diseño, revisión, aprobación, programación, monitoreo y reporte solo cuando correspondan.
- Las fechas deben estar entre {$inicio} y {$fin}.
- Cada tarea debe tener un entregable verificable, tipo de contenido y uno o más roles sugeridos.
- rol_sugerido solo puede ser "Community Manager", "Diseñador" o "Administrador".
- roles_sugeridos solo puede contener "Community Manager", "Diseñador" o "Administrador" y debe delegar la tarea a todos los perfiles que intervienen.
- tipo_contenido solo puede ser "reel", "post", "historia", "carrusel", "guion" u "otro".
- Marca requiere_aprobacion=true para toda pieza que deba validarse antes de publicarse.
- Marca visible_cliente=true en entregables y piezas que el cliente deba consultar; usa false para coordinación interna.
- No repitas el plan completo dentro de una tarea. Escribe instrucciones cortas, accionables y sin ambigüedad.
- Evita tareas duplicadas y ordena el trabajo según dependencias reales: planificación, producción, revisión, publicación y medición.
- prioridad solo puede ser baja, media, alta o urgente.
- Los indicadores deben ser medibles; no inventes metas numéricas que no estén en las fuentes.
- Responde exclusivamente con JSON válido, sin Markdown ni texto adicional.

FORMATO EXACTO:
{
  "nombre": "Nombre breve y específico",
  "descripcion": "Síntesis operativa de la estrategia",
  "objetivo_general": "Objetivo concreto de la campaña",
  "publicos_objetivo": [
    {"tipo_edades": "Adultos planificadores (35-55 años)", "descripcion": "Buscan entender sus aportes y asegurar la estabilidad financiera de su familia."}
  ],
  "mensaje_principal": "Idea central de comunicación",
  "tono_comunicacion": "Tono recomendado",
  "canales": ["Facebook", "Instagram", "TikTok", "WhatsApp"],
  "indicadores": ["Alcance", "Interacciones"],
  "tareas": [
    {
      "titulo": "Título",
      "descripcion": "Objetivo, instrucciones y criterio de finalización",
      "entregable": "Resultado verificable",
      "fecha_inicio": "YYYY-MM-DD",
      "fecha_limite": "YYYY-MM-DD",
      "prioridad": "media",
      "rol_sugerido": "Community Manager",
      "roles_sugeridos": ["Community Manager", "Diseñador"],
      "tipo_contenido": "carrusel",
      "tipo_contenido_otro": null,
      "requiere_aprobacion": true,
      "visible_cliente": true
    }
  ]
}
PROMPT;

        $blueprint = null;
        $usedFallback = false;
        $models = collect([config('services.groq.model'), ...config('services.groq.fallback_models', [])])
            ->filter()->unique()->values();

        foreach ($models as $model) {
            try {
                $response = Http::withToken(config('services.groq.key'))
                    ->acceptJson()
                    ->withOptions(['verify' => false])
                    ->connectTimeout(15)
                    ->timeout(120)
                    ->post(config('services.groq.url'), [
                        'model' => $model,
                        'messages' => [['role' => 'user', 'content' => $prompt]],
                        'temperature' => 0.1,
                        'response_format' => ['type' => 'json_object'],
                        'stream' => false,
                    ]);

                if (! $response->successful()) {
                    Log::warning('Groq no pudo generar la campaña.', [
                        'model' => $model,
                        'status' => $response->status(),
                        'response' => Str::limit($response->body(), 1000),
                    ]);

                    continue;
                }

                $blueprint = $this->decode((string) $response->json('choices.0.message.content'));
                if ($blueprint && filled($blueprint['nombre'] ?? null)) {
                    break;
                }

                Log::warning('Groq devolvió una campaña sin JSON utilizable.', ['model' => $model]);
            } catch (\Throwable $exception) {
                Log::warning('No se pudo conectar con Groq para generar la campaña.', [
                    'model' => $model,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if (! $blueprint || blank($blueprint['nombre'] ?? null)) {
            $blueprint = $this->deterministicBlueprint($suscripcion, $plan, $calendarTasks, $inicio, $fin);
            $usedFallback = true;
        }

        $result = $this->normalize($this->sanitize($blueprint), $suscripcion, $plan, $calendarTasks, $inicio, $fin);
        $result['generation_source'] = $usedFallback ? 'automatic_fallback' : 'artificial_intelligence';
        $result['generation_warning'] = null;

        return $result;
    }

    private function deterministicBlueprint(
        Suscripcion $suscripcion,
        PlanMarketing $plan,
        array $calendarTasks,
        string $startsAt,
        string $endsAt
    ): array {
        $brief = $this->briefPrefillService->build($suscripcion, $plan);
        $tasks = $calendarTasks;

        if ($tasks === []) {
            $tasks = [
                [
                    'titulo' => 'Planificar la ejecución de la campaña',
                    'descripcion' => 'Revisar el plan aprobado, confirmar el calendario y organizar los entregables con el equipo.',
                    'entregable' => 'Cronograma de ejecución confirmado',
                    'fecha_inicio' => $startsAt,
                    'fecha_limite' => $startsAt,
                    'prioridad' => 'alta',
                    'roles_sugeridos' => ['Community Manager', 'Administrador'],
                    'tipo_contenido' => 'otro',
                    'tipo_contenido_otro' => 'Planificación',
                    'requiere_aprobacion' => false,
                    'visible_cliente' => false,
                ],
                [
                    'titulo' => 'Preparar contenidos de la campaña',
                    'descripcion' => 'Producir las piezas y textos definidos en el plan de marketing, respetando el mensaje, tono y línea visual aprobados.',
                    'entregable' => 'Piezas y copys listos para revisión',
                    'fecha_inicio' => $startsAt,
                    'fecha_limite' => Carbon::parse($startsAt)->addDays(7)->min(Carbon::parse($endsAt))->toDateString(),
                    'prioridad' => 'alta',
                    'roles_sugeridos' => ['Diseñador', 'Community Manager'],
                    'tipo_contenido' => 'otro',
                    'tipo_contenido_otro' => 'Producción',
                    'requiere_aprobacion' => true,
                    'visible_cliente' => true,
                ],
                [
                    'titulo' => 'Monitorear y reportar resultados',
                    'descripcion' => 'Medir los indicadores definidos y documentar resultados, aprendizajes y recomendaciones.',
                    'entregable' => 'Informe de rendimiento de campaña',
                    'fecha_inicio' => $endsAt,
                    'fecha_limite' => $endsAt,
                    'prioridad' => 'media',
                    'roles_sugeridos' => ['Community Manager', 'Administrador'],
                    'tipo_contenido' => 'otro',
                    'tipo_contenido_otro' => 'Analítica',
                    'requiere_aprobacion' => false,
                    'visible_cliente' => true,
                ],
            ];
        } else {
            array_unshift($tasks, [
                'titulo' => 'Planificar la ejecución de la campaña',
                'descripcion' => 'Revisar el plan aprobado, confirmar el calendario y organizar los entregables con el equipo.',
                'entregable' => 'Cronograma de ejecución confirmado',
                'fecha_inicio' => $startsAt,
                'fecha_limite' => $startsAt,
                'prioridad' => 'alta',
                'roles_sugeridos' => ['Community Manager', 'Administrador'],
                'tipo_contenido' => 'otro',
                'tipo_contenido_otro' => 'Planificación',
                'requiere_aprobacion' => false,
                'visible_cliente' => false,
            ]);
            $tasks[] = [
                'titulo' => 'Monitorear y reportar resultados',
                'descripcion' => 'Medir los indicadores definidos y documentar resultados, aprendizajes y recomendaciones.',
                'entregable' => 'Informe de rendimiento de campaña',
                'fecha_inicio' => $endsAt,
                'fecha_limite' => $endsAt,
                'prioridad' => 'media',
                'roles_sugeridos' => ['Community Manager', 'Administrador'],
                'tipo_contenido' => 'otro',
                'tipo_contenido_otro' => 'Analítica',
                'requiere_aprobacion' => false,
                'visible_cliente' => true,
            ];
        }

        return [...$brief, 'tareas' => $tasks];
    }

    private function normalize(
        array $blueprint,
        Suscripcion $suscripcion,
        PlanMarketing $plan,
        array $calendarTasks,
        string $startsAt,
        string $endsAt
    ): array {
        $fallback = $this->briefPrefillService->build($suscripcion, $plan);
        $limits = [
            'nombre' => 100,
            'descripcion' => 5000,
            'objetivo_general' => 2500,
            'mensaje_principal' => 1500,
            'tono_comunicacion' => 120,
        ];

        foreach ($limits as $field => $limit) {
            $blueprint[$field] = Str::limit(trim((string) ($blueprint[$field] ?? $fallback[$field] ?? '')), $limit, '');
        }

        $blueprint['publicos_objetivo'] = $this->audienceService->normalize(
            $blueprint['publicos_objetivo'] ?? $fallback['publicos_objetivo'] ?? [],
            $blueprint['publico_objetivo'] ?? $fallback['publico_objetivo'] ?? ''
        );
        $blueprint['publico_objetivo'] = $this->audienceService->serialize($blueprint['publicos_objetivo']);

        $blueprint['canales'] = collect($blueprint['canales'] ?? $fallback['canales'] ?? [])
            ->map(fn ($channel) => match (Str::lower(trim((string) $channel))) {
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'tiktok', 'tik tok' => 'TikTok',
                'whatsapp', 'whats app' => 'WhatsApp',
                default => null,
            })
            ->filter()
            ->unique()->values()->all();
        $blueprint['indicadores'] = collect($blueprint['indicadores'] ?? $fallback['indicadores'] ?? [])
            ->map(fn ($indicator) => Str::limit(trim((string) $indicator), 100, ''))
            ->filter()->unique()->take(6)->values()->all();

        $aiTasks = collect($blueprint['tareas'] ?? [])
            ->filter(fn ($task) => is_array($task))
            ->map(fn (array $task) => $this->normalizeTask($task, $startsAt, $endsAt));
        $exactCalendarTasks = collect($calendarTasks)
            ->map(fn (array $task) => $this->normalizeTask($task, $startsAt, $endsAt));

        $tasks = $exactCalendarTasks->isNotEmpty()
            ? $aiTasks->filter(fn (array $task) => $this->isOperationalTask($task))
                ->concat($exactCalendarTasks)
                ->sortBy(fn (array $task) => $task['fecha_limite'].'|'.($task['tipo_contenido'] === 'otro' ? '0' : '1'))
            : $aiTasks;
        $blueprint['tareas'] = $tasks
            ->filter(fn (array $task) => filled($task['titulo']) && filled($task['descripcion']))
            ->unique(fn (array $task) => Str::lower(Str::ascii($task['titulo'])).'|'.$task['fecha_limite'])
            ->take(20)->values()->all();

        if ($blueprint['tareas'] === []) {
            throw new RuntimeException('La IA no generó tareas operativas válidas para esta campaña.');
        }

        return $blueprint;
    }

    private function normalizeTask(array $task, string $startsAt, string $endsAt): array
    {
        $allowedRoles = ['Community Manager', 'Diseñador', 'Administrador'];
        $allowedTypes = ['reel', 'post', 'historia', 'carrusel', 'guion', 'otro'];
        $allowedPriorities = ['baja', 'media', 'alta', 'urgente'];
        $title = Str::limit(trim((string) ($task['titulo'] ?? 'Tarea operativa')), 100, '');
        $description = Str::limit(trim((string) ($task['descripcion'] ?? '')), 700, '');
        $type = Str::lower(trim((string) ($task['tipo_contenido'] ?? '')));
        if (! in_array($type, $allowedTypes, true)) {
            $type = $this->inferContentType($title.' '.$description);
        }

        $roles = collect($task['roles_sugeridos'] ?? [])
            ->push($task['rol_sugerido'] ?? null)
            ->filter(fn ($role) => in_array($role, $allowedRoles, true))
            ->unique()->values();
        if ($roles->isEmpty()) {
            $roles = collect(in_array($type, ['post', 'reel', 'historia', 'carrusel'], true)
                ? ['Diseñador', 'Community Manager']
                : ['Community Manager']);
        }

        $allowedStart = Carbon::parse($startsAt)->startOfDay();
        $allowedEnd = Carbon::parse($endsAt)->startOfDay();
        $start = $this->safeDate($task['fecha_inicio'] ?? null, $startsAt);
        if ($start->lt($allowedStart)) {
            $start = $allowedStart->copy();
        }
        if ($start->gt($allowedEnd)) {
            $start = $allowedEnd->copy();
        }
        $end = $this->safeDate($task['fecha_limite'] ?? null, $start->toDateString());
        if ($end->lt($start)) {
            $end = $start->copy();
        }
        if ($end->gt($allowedEnd)) {
            $end = $allowedEnd->copy();
        }
        $isContent = in_array($type, ['post', 'reel', 'historia', 'carrusel', 'guion'], true);

        return [
            'titulo' => $title,
            'descripcion' => $description,
            'entregable' => Str::limit(trim((string) ($task['entregable'] ?? 'Entregable verificable de '.$title)), 1500, ''),
            'fecha_inicio' => $start->toDateString(),
            'fecha_limite' => $end->toDateString(),
            'prioridad' => in_array($task['prioridad'] ?? null, $allowedPriorities, true) ? $task['prioridad'] : 'media',
            'rol_sugerido' => $roles->first(),
            'roles_sugeridos' => $roles->all(),
            'tipo_contenido' => $type,
            'tipo_contenido_otro' => $type === 'otro'
                ? Str::limit(trim((string) ($task['tipo_contenido_otro'] ?? 'Tarea operativa')), 30, '')
                : null,
            'requiere_aprobacion' => array_key_exists('requiere_aprobacion', $task)
                ? (bool) $task['requiere_aprobacion'] : $isContent,
            'visible_cliente' => array_key_exists('visible_cliente', $task)
                ? (bool) $task['visible_cliente'] : $isContent,
        ];
    }

    private function isOperationalTask(array $task): bool
    {
        $text = Str::lower(Str::ascii($task['titulo'].' '.$task['descripcion']));

        return collect([
            'planific', 'linea grafica', 'sesion fotograf', 'configur', 'investig',
            'guion', 'copy', 'redaccion', 'revision', 'aprobacion', 'programacion',
            'monitore', 'medicion', 'informe', 'reporte', 'analitica',
        ])->contains(fn (string $term) => str_contains($text, $term));
    }

    private function inferContentType(string $text): string
    {
        $text = Str::lower(Str::ascii($text));

        return match (true) {
            str_contains($text, 'carrusel'), str_contains($text, 'carousel') => 'carrusel',
            str_contains($text, 'reel'), str_contains($text, 'video') => 'reel',
            str_contains($text, 'historia'), str_contains($text, 'story') => 'historia',
            str_contains($text, 'guion'), str_contains($text, 'script') => 'guion',
            str_contains($text, 'post'), str_contains($text, 'publicacion') => 'post',
            default => 'otro',
        };
    }

    private function safeDate(mixed $value, string $fallback): Carbon
    {
        try {
            return Carbon::parse($value ?: $fallback)->startOfDay();
        } catch (\Throwable) {
            return Carbon::parse($fallback)->startOfDay();
        }
    }

    private function decode(string $content): ?array
    {
        $content = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($content)));
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            $start = strpos($content, '{');
            $end = strrpos($content, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function sanitize(array $value): array
    {
        return collect($value)->map(function ($item) {
            if (is_array($item)) {
                return $this->sanitize($item);
            }

            return is_string($item) ? SocialContentPolicy::sanitize(trim($item)) : $item;
        })->all();
    }

    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '[]';
    }
}
