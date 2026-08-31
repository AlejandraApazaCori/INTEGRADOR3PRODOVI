<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\CampaniaMensaje;
use App\Models\CampaniaMensajeContexto;
use App\Models\User;
use App\Services\CampaignFeedbackService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignFeedbackController extends Controller
{
    public function __construct(private readonly CampaignFeedbackService $feedbackService) {}

    public function clientPage(Campania $campania): View
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($this->feedbackService->isClient($campania, $user), 403);

        $campania->loadMissing(['tareas', 'suscripcion.empresa']);
        $feedbackParticipants = $this->feedbackService->participants($campania);

        return view('clientes.campanias.feedback', compact('campania', 'feedbackParticipants'));
    }

    public function clientUnreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $campaign = $this->feedbackService->clientCampaign(
            $user,
            $request->integer('empresa') ?: null,
            $request->integer('campania') ?: null
        );

        return response()->json([
            'count' => $campaign ? $this->feedbackService->unreadCount($campaign, $user) : 0,
            'url' => $campaign ? route('clientes.campanias.feedback', $campaign) : null,
        ]);
    }

    public function index(Request $request, Campania $campania): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->feedbackService->authorize($campania, $user);

        $validated = $request->validate([
            'filtro' => ['nullable', Rule::in(['todos', 'mios', 'cliente'])],
            'contexto' => ['nullable', 'string', 'max:30'],
            'vista' => ['nullable', Rule::in(['mensajes', 'contextos'])],
        ]);
        $filter = $validated['filtro'] ?? 'todos';
        $isClient = $this->feedbackService->isClient($campania, $user);

        $query = CampaniaMensaje::query()
            ->where('campania_id', $campania->id)
            ->visiblePara($user, $isClient);

        $context = $validated['contexto'] ?? null;
        if ($context === 'general') {
            $query->whereNull('tarea_id')->whereNull('contexto_id');
        } elseif (str_starts_with((string) $context, 'custom:')) {
            $contextId = substr((string) $context, 7);
            $customContextQuery = $campania->mensajeContextos()->whereKey((int) $contextId);
            if ($isClient) {
                $customContextQuery->where(function (Builder $builder) use ($user) {
                    $builder->where('creado_por_id', $user->id)
                        ->orWhereHas('mensajes', fn (Builder $messages) => $messages->visiblePara($user, true));
                });
            }
            abort_unless(ctype_digit($contextId) && $customContextQuery->exists(), 422, 'El contexto seleccionado no pertenece a la campaña.');
            $query->where('contexto_id', (int) $contextId);
        } elseif (filled($context)) {
            $taskQuery = $campania->tareas()->whereKey((int) $context);
            if ($isClient) {
                $taskQuery->where('visible_cliente', true);
            }
            abort_unless(ctype_digit($context) && $taskQuery->exists(), 422, 'El contexto seleccionado no pertenece a la campaña.');
            $query->where('tarea_id', (int) $context);
        }

        $this->applyFilter($query, $filter, $user);

        if ($context === null && ($validated['vista'] ?? null) === 'contextos') {
            if ($isClient) {
                $query->where(function (Builder $builder) {
                    $builder->whereNull('tarea_id')
                        ->orWhereHas('tarea', fn (Builder $task) => $task->where('visible_cliente', true));
                });
            }

            $contextRows = $query
                ->select(['tarea_id', 'contexto_id'])
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('MAX(created_at) as ultima_actividad')
                ->groupBy('tarea_id', 'contexto_id')
                ->orderByDesc('ultima_actividad')
                ->get();
            $tasks = $campania->tareas()
                ->when($isClient, fn (Builder $tasks) => $tasks->where('visible_cliente', true))
                ->whereIn('id', $contextRows->pluck('tarea_id')->filter())
                ->get()
                ->keyBy('id');
            $customContexts = $campania->mensajeContextos()
                ->whereIn('id', $contextRows->pluck('contexto_id')->filter())
                ->get()
                ->keyBy('id');

            return response()->json([
                'html' => view('campanias.feedback.context-list', compact('contextRows', 'tasks', 'customContexts'))->render(),
                'counts' => $this->counts($campania, $user, $isClient, null),
            ]);
        }

        $messages = $query
            ->with(['remitente.roles', 'destinatarios.roles', 'tarea', 'contexto', 'imagenes', 'padre.remitente'])
            ->latest('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        if ($messages->isNotEmpty()) {
            DB::table('campania_mensaje_destinatarios')
                ->where('user_id', $user->id)
                ->whereIn('mensaje_id', $messages->pluck('id'))
                ->whereNull('leido_at')
                ->update(['leido_at' => now(), 'updated_at' => now()]);
        }

        return response()->json([
            'html' => view('campanias.feedback.message-list', compact('messages', 'user'))->render(),
            'last_id' => $messages->last()?->id,
            'counts' => $this->counts($campania, $user, $isClient, $context),
        ]);
    }

    public function store(Request $request, Campania $campania): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->feedbackService->authorize($campania, $user);
        $isClient = $this->feedbackService->isClient($campania, $user);

        $allowedAudiences = $isClient ? ['cliente_equipo', 'directo'] : ['equipo', 'cliente_equipo', 'directo'];
        $validated = $request->validate([
            'contenido' => ['nullable', 'required_without:imagenes', 'string', 'max:5000'],
            'audiencia' => ['required', Rule::in($allowedAudiences)],
            'destinatario_id' => ['nullable', 'required_if:audiencia,directo', 'integer', 'exists:users,id'],
            'tarea_id' => [
                'nullable',
                'integer',
                Rule::exists('tareas', 'id')->where(fn ($query) => $query->where('campania_id', $campania->id)),
            ],
            'contexto_id' => [
                'nullable',
                'integer',
                Rule::exists('campania_mensaje_contextos', 'id')->where(fn ($query) => $query->where('campania_id', $campania->id)),
            ],
            'imagenes' => ['nullable', 'array', 'max:5'],
            'imagenes.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'mensaje_padre_id' => ['nullable', 'integer', 'exists:campania_mensajes,id'],
        ]);

        if (! empty($validated['contexto_id'])) {
            $validated['tarea_id'] = null;
            if ($isClient) {
                $allowedContext = $campania->mensajeContextos()
                    ->whereKey($validated['contexto_id'])
                    ->where(function (Builder $builder) use ($user) {
                        $builder->where('creado_por_id', $user->id)
                            ->orWhereHas('mensajes', fn (Builder $messages) => $messages->visiblePara($user, true));
                    })
                    ->exists();
                abort_unless($allowedContext, 422, 'El contexto seleccionado no está disponible para el cliente.');
            }
        }

        $parent = null;
        if (! empty($validated['mensaje_padre_id'])) {
            $parent = CampaniaMensaje::query()
                ->whereKey($validated['mensaje_padre_id'])
                ->where('campania_id', $campania->id)
                ->visiblePara($user, $isClient)
                ->with('destinatarios')
                ->firstOrFail();

            $validated['audiencia'] = $parent->audiencia;
            $validated['tarea_id'] = $parent->tarea_id;
            $validated['contexto_id'] = $parent->contexto_id;
            if ($parent->audiencia === 'directo') {
                $validated['destinatario_id'] = (int) $parent->remitente_id === (int) $user->id
                    ? $parent->destinatarios->first(fn ($recipient) => (int) $recipient->id !== (int) $user->id)?->id
                    : $parent->remitente_id;
            }
        }

        $recipients = $this->feedbackService->recipientsFor(
            $campania,
            $validated['audiencia'],
            $validated['destinatario_id'] ?? null,
            $user
        );

        abort_if($recipients->isEmpty(), 422, 'La campaña no tiene otros participantes para recibir este mensaje.');

        $storedPaths = [];

        try {
            $message = DB::transaction(function () use ($campania, $user, $validated, $recipients, $request, &$storedPaths) {
                $message = $campania->mensajes()->create([
                    'remitente_id' => $user->id,
                    'tarea_id' => $validated['tarea_id'] ?? null,
                    'contexto_id' => $validated['contexto_id'] ?? null,
                    'mensaje_padre_id' => $validated['mensaje_padre_id'] ?? null,
                    'audiencia' => $validated['audiencia'],
                    'contenido' => trim($validated['contenido'] ?? ''),
                ]);

                $message->destinatarios()->sync($recipients->pluck('id')->all());

                foreach ($request->file('imagenes', []) as $image) {
                    $path = $image->store("campanias/{$campania->id}/mensajes", 'public');
                    $storedPaths[] = $path;
                    $message->imagenes()->create([
                        'nombre_original' => $image->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'mime_type' => $image->getMimeType(),
                        'tamanio' => $image->getSize(),
                    ]);
                }

                return $message;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return response()->json(['message' => 'Mensaje enviado correctamente.', 'id' => $message->id], 201);
    }

    public function storeContext(Request $request, Campania $campania): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->feedbackService->authorize($campania, $user);
        $request->merge(['nombre' => trim((string) $request->input('nombre'))]);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('campania_mensaje_contextos', 'nombre')
                    ->where(fn ($query) => $query->where('campania_id', $campania->id)),
            ],
        ]);

        $context = $campania->mensajeContextos()->create([
            'creado_por_id' => $user->id,
            'nombre' => trim($validated['nombre']),
        ]);

        return response()->json([
            'message' => 'Contexto creado correctamente.',
            'id' => $context->id,
            'nombre' => $context->nombre,
        ], 201);
    }

    public function update(Request $request, Campania $campania, CampaniaMensaje $mensaje): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->feedbackService->authorize($campania, $user);
        abort_unless((int) $mensaje->campania_id === (int) $campania->id, 404);
        abort_unless((int) $mensaje->remitente_id === (int) $user->id, 403);

        $validated = $request->validate([
            'contenido' => ['required', 'string', 'max:5000'],
        ]);

        $mensaje->update(['contenido' => trim($validated['contenido'])]);

        return response()->json(['message' => 'Mensaje actualizado correctamente.']);
    }

    public function destroy(Campania $campania, CampaniaMensaje $mensaje): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->feedbackService->authorize($campania, $user);
        abort_unless((int) $mensaje->campania_id === (int) $campania->id, 404);

        $canDelete = (int) $mensaje->remitente_id === (int) $user->id
            || $user->hasAnyRole(['Super Administrador', 'Administrador']);
        abort_unless($canDelete, 403);

        $mensaje->loadMissing('imagenes');
        Storage::disk('public')->delete($mensaje->imagenes->pluck('ruta_archivo')->all());
        $mensaje->delete();

        return response()->json(['message' => 'Mensaje eliminado.']);
    }

    private function applyFilter(Builder $query, string $filter, User $user): void
    {
        if ($filter === 'mios') {
            $query->where('audiencia', 'directo')
                ->whereHas('destinatarios', fn (Builder $destinatarios) => $destinatarios->whereKey($user->id));
        }

        if ($filter === 'cliente') {
            $query->where('audiencia', 'cliente_equipo');
        }
    }

    private function counts(Campania $campania, User $user, bool $isClient, ?string $context): array
    {
        $base = CampaniaMensaje::query()
            ->where('campania_id', $campania->id)
            ->visiblePara($user, $isClient);

        if ($context === 'general') {
            $base->whereNull('tarea_id')->whereNull('contexto_id');
        } elseif (str_starts_with((string) $context, 'custom:')) {
            $base->where('contexto_id', (int) substr((string) $context, 7));
        } elseif (filled($context)) {
            $base->where('tarea_id', (int) $context);
        }

        return [
            'todos' => (clone $base)->count(),
            'mios' => (clone $base)->where('audiencia', 'directo')
                ->whereHas('destinatarios', fn (Builder $query) => $query->whereKey($user->id))->count(),
            'cliente' => (clone $base)->where('audiencia', 'cliente_equipo')->count(),
            'no_leidos' => (clone $base)->whereHas('destinatarios', function (Builder $query) use ($user) {
                $query->whereKey($user->id)->whereNull('campania_mensaje_destinatarios.leido_at');
            })->count(),
        ];
    }
}
