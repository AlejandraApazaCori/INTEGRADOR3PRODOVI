<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tarea;
use App\Services\GroqImageService;
use App\Services\SocialPublicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicacionController extends Controller
{
    protected GroqImageService $groqImageService;
    protected SocialPublicationService $socialPublicationService;

    public function __construct(GroqImageService $groqImageService, SocialPublicationService $socialPublicationService)
    {
        $this->groqImageService = $groqImageService;
        $this->socialPublicationService = $socialPublicationService;
    }

    public function index(Request $request)
    {
        $tareaId = $request->input('tarea_id');

        if (! $tareaId) {
            return redirect()->back()->with('error', 'No se especifico una tarea para publicar');
        }

        $tarea = $this->loadPublishingTask($tareaId);
        $cliente = $tarea->campania?->cliente;
        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        $facebookPage = $this->findSocialAccount($cliente, 'facebook_page', $empresaId);
        $instagramAccount = $this->findSocialAccount($cliente, 'instagram', $empresaId);

        return view('administrador.publicacion.publicar', compact('tarea', 'cliente', 'facebookPage', 'instagramAccount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => 'required|integer|exists:tareas,id',
            'message' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram',
            'schedule_type' => 'required|string|in:now,later',
            'scheduled_at' => 'nullable|required_if:schedule_type,later|date|after:now',
        ]);

        $tarea = $this->loadPublishingTask($validated['tarea_id']);
        $cliente = $tarea->campania?->cliente;

        if (! $cliente) {
            return back()->withInput()->with('error', 'La tarea no tiene un cliente asociado para publicar.');
        }

        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        $providers = ['facebook' => 'facebook_page', 'instagram' => 'instagram'];

        foreach ($validated['platforms'] as $platform) {
            $account = $this->findSocialAccount($cliente, $providers[$platform], $empresaId);

            if (! $account || ! filled($account->provider_user_id) || ! filled($account->access_token)) {
                return back()->withInput()->with('error', 'La cuenta de ' . ucfirst($platform) . ' no esta vinculada o no tiene un token valido.');
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

            return back()->with('success', 'La publicacion quedo programada para ' . $scheduledAt->format('Y-m-d H:i') . '. Se publicara automaticamente cuando llegue esa fecha y hora.');
        }

        $result = $this->socialPublicationService->publish($cliente, $tarea, $validated['message'], $validated['platforms']);
        $published = $result['success'] || $result['partial'];

        $tarea->forceFill([
            'publication_status' => $result['success'] ? 'published' : ($result['partial'] ? 'partial' : 'failed'),
            'publication_scheduled_at' => null,
            'published_at' => $published ? now() : null,
            'facebook_post_id' => $result['facebook_post_id'] ?? null,
            'instagram_media_id' => $result['instagram_media_id'] ?? null,
            'publication_error' => $result['success'] ? null : ($result['error'] ?? 'Error desconocido'),
            'publication_message' => $validated['message'],
            'publication_platforms' => $validated['platforms'],
        ])->save();

        if (! $result['success']) {
            $prefix = $result['partial'] ? 'La publicacion fue parcial. ' : 'No se pudo publicar. ';
            return back()->withInput()->with('error', $prefix . ($result['error'] ?? 'Error desconocido de Meta.'));
        }

        $platformNames = collect($result['successful_platforms'])->map(fn ($platform) => ucfirst($platform))->implode(' e ');

        return back()->with('success', 'Publicacion realizada correctamente en ' . $platformNames . '.');
    }

    public function generateCopy(Request $request)
    {
        $request->validate([
            'tarea_id' => 'required|integer|exists:tareas,id',
        ]);

        $copy = $this->groqImageService->generateCopyFromImage($request->tarea_id);

        return response()->json([
            'success' => true,
            'copy' => $copy,
        ]);
    }

    private function loadPublishingTask(int $tareaId): Tarea
    {
        return Tarea::with([
            'archivos' => function ($query) {
                $query->where('estado', 'aprobado');
            },
            'campania.cliente.socialAccounts',
            'campania.suscripcion.empresa.socialAccounts',
        ])->findOrFail($tareaId);
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

