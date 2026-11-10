<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tarea;
use App\Services\GroqImageService;
use App\Services\PublicationTimingService;
use App\Services\SocialPublicationService;
use App\Services\TaskReviewWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicacionController extends Controller
{
    protected GroqImageService $groqImageService;

    protected SocialPublicationService $socialPublicationService;
    protected TaskReviewWorkflow $reviewWorkflow;

    public function __construct(GroqImageService $groqImageService, SocialPublicationService $socialPublicationService, TaskReviewWorkflow $reviewWorkflow)
    {
        $this->groqImageService = $groqImageService;
        $this->socialPublicationService = $socialPublicationService;
        $this->reviewWorkflow = $reviewWorkflow;
    }

    public function index(Request $request)
    {
        $tareaId = $request->input('tarea_id');

        if (! $tareaId) {
            return redirect()->back()->with('error', 'No se especifico una tarea para publicar');
        }

        $tarea = $this->loadPublishingTask($tareaId);
        if ($tarea->revision_cliente_habilitada && ! $this->reviewWorkflow->canPublish($tarea)) {
            return redirect()->route('administrador.tareas.ver-subidas', $tarea)
                ->with('error', 'Espera la aprobación del cliente antes de publicar.');
        }
        $cliente = $tarea->campania?->cliente;
        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        $facebookPage = $this->findSocialAccount($cliente, 'facebook_page', $empresaId);
        $instagramAccount = $this->findSocialAccount($cliente, 'instagram', $empresaId);

        return view('administrador.publicacion.publicar', compact('tarea', 'cliente', 'facebookPage', 'instagramAccount'));
    }

    public function horarios(Request $request, PublicationTimingService $timing)
    {
        $this->authorizePublisher($request);
        $validated = $request->validate(['tarea_id' => 'required|integer|exists:tareas,id']);
        $tarea = $this->loadPublishingTask($validated['tarea_id']);
        abort_if($tarea->revision_cliente_habilitada && ! $this->reviewWorkflow->canPublish($tarea), 409);
        $user = $request->user();
        abort_unless($user->hasAnyRole(['Super Administrador', 'Administrador'])
            || in_array((int) $user->id, array_map('intval', array_filter([
                $tarea->creador_id, $tarea->asignado_id,
                $tarea->campania?->usuario_creador_id, $tarea->campania?->community_manager_id,
            ])), true), 403);
        set_time_limit(240);
        $cliente = $tarea->campania?->cliente;
        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        $accounts = [
            'facebook' => $this->findSocialAccount($cliente, 'facebook_page', $empresaId),
            'instagram' => $this->findSocialAccount($cliente, 'instagram', $empresaId),
        ];

        return response()->json($timing->forAccounts($accounts));
    }

    public function store(Request $request)
    {
        $this->authorizePublisher($request);
        $validated = $request->validate([
            'tarea_id' => 'required|integer|exists:tareas,id',
            'message' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram',
            'schedule_type' => 'required|string|in:now,later',
            'scheduled_at' => 'exclude_unless:schedule_type,later|required|date|after:now',
        ]);

        $tarea = $this->loadPublishingTask($validated['tarea_id']);
        if ($tarea->revision_cliente_habilitada && ! $this->reviewWorkflow->canPublish($tarea)) {
            return back()->withInput()->with('error', 'Espera la aprobación del cliente antes de publicar o programar.');
        }
        $cliente = $tarea->campania?->cliente;

        if (! $cliente) {
            return back()->withInput()->with('error', 'La tarea no tiene un cliente asociado para publicar.');
        }

        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        $providers = ['facebook' => 'facebook_page', 'instagram' => 'instagram'];

        foreach ($validated['platforms'] as $platform) {
            $account = $this->findSocialAccount($cliente, $providers[$platform], $empresaId);

            if (! $account || ! filled($account->provider_user_id) || ! filled($account->access_token)) {
                return back()->withInput()->with('error', 'La cuenta de '.ucfirst($platform).' no esta vinculada o no tiene un token valido.');
            }
        }

        if ($validated['schedule_type'] === 'later') {
            $scheduledAt = Carbon::parse($validated['scheduled_at'], config('app.timezone'));

            $tarea->forceFill([
                'publication_status' => 'scheduled',
                'publication_scheduled_at' => $scheduledAt,
                'publication_message' => $validated['message'],
                'publication_platforms' => $validated['platforms'],
                'publication_error' => null,
                'published_at' => null,
                'facebook_post_id' => null,
                'instagram_media_id' => null,
            ])->save();

            return back()->with('success', 'La publicacion quedo programada para '.$scheduledAt->format('Y-m-d H:i').'. Se publicara automaticamente cuando llegue esa fecha y hora.');
        }

        $result = $this->socialPublicationService->publish($cliente, $tarea, $validated['message'], $validated['platforms']);
        $published = $result['success'] || $result['partial'];

        $tarea->forceFill([
            'estado' => $published ? 'publicado' : $tarea->estado,
            'publication_status' => $result['success'] ? 'published' : ($result['partial'] ? 'partial' : 'failed'),
            'publication_scheduled_at' => null,
            'published_at' => $published ? now() : null,
            'facebook_post_id' => $result['facebook_post_id'] ?? null,
            'instagram_media_id' => $result['instagram_media_id'] ?? null,
            'publication_error' => $result['success'] ? null : ($result['error'] ?? 'Error desconocido'),
            'publication_message' => $validated['message'],
            'publication_platforms' => $validated['platforms'],
        ])->save();

        if (! $published) {
            return back()->withInput()->with('error', 'No se pudo publicar. '.($result['error'] ?? 'Error desconocido de Meta.'));
        }

        return redirect()->to(route('administrador.campañas.show', $tarea->campania_id).'#tareas')
            ->with('publication_success', [
                'task' => $tarea->titulo,
                'platforms' => $result['successful_platforms'],
                'error' => $result['partial'] ? ($result['error'] ?? 'Una de las redes no pudo publicar el contenido.') : null,
            ]);
    }

    public function generateCopy(Request $request)
    {
        $this->authorizePublisher($request);
        $request->validate([
            'tarea_id' => 'required|integer|exists:tareas,id',
        ]);

        $copy = $this->groqImageService->generateCopyFromImage($request->tarea_id);

        return response()->json([
            'success' => true,
            'copy' => $copy,
        ]);
    }

    private function authorizePublisher(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['Super Administrador', 'Administrador', 'Community Manager']), 403);
    }

    private function loadPublishingTask(int $tareaId): Tarea
    {
        $task = Tarea::with([
            'archivos' => function ($query) {
                $query->where('estado', 'aprobado');
            },
            'campania.cliente.socialAccounts',
            'campania.suscripcion.empresa.socialAccounts',
        ])->findOrFail($tareaId);

        if ($task->revision_cliente_habilitada) {
            $task->setRelation('archivos', $task->archivos->where('revision_cliente_estado', 'aprobado')->values());
        }

        return $task;
    }

    private function findSocialAccount(?\App\Models\User $cliente, string $provider, ?int $empresaId): ?SocialAccount
    {
        if (! $cliente) {
            return null;
        }

        $account = $cliente->socialAccounts()
            ->where('provider', $provider)
            ->when($empresaId, fn ($query) => $query->where('empresa_id', $empresaId), fn ($query) => $query->whereNull('empresa_id'))
            ->first();

        if (! $account && $empresaId) {
            $account = $cliente->socialAccounts()
                ->whereNull('empresa_id')
                ->where('provider', $provider)
                ->first();
        }

        return $account;
    }
}
