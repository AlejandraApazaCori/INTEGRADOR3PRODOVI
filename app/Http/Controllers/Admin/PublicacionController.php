<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Services\FacebookService;
use App\Services\GroqImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicacionController extends Controller
{
    protected GroqImageService $groqImageService;
    protected FacebookService $facebookService;

    public function __construct(GroqImageService $groqImageService, FacebookService $facebookService)
    {
        $this->groqImageService = $groqImageService;
        $this->facebookService = $facebookService;
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
        $facebookPage = $cliente?->socialAccounts()
            ->where('provider', 'facebook_page')
            ->when(
                $empresaId,
                fn ($query) => $query->where('empresa_id', $empresaId),
                fn ($query) => $query->whereNull('empresa_id')
            )
            ->first();

        if (! $facebookPage && $empresaId) {
            $facebookPage = $cliente?->socialAccounts()
                ->whereNull('empresa_id')
                ->where('provider', 'facebook_page')
                ->first();
        }

        return view('administrador.publicacion.publicar', compact('tarea', 'cliente', 'facebookPage'));
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

        if (in_array('instagram', $validated['platforms'], true) && ! in_array('facebook', $validated['platforms'], true)) {
            return back()->withInput()->with('error', 'Por ahora la publicacion automatica solo esta implementada para Facebook.');
        }

        if ($validated['schedule_type'] === 'later') {
            $scheduledAt = Carbon::parse($validated['scheduled_at'], config('app.timezone'));

            $tarea->forceFill([
                'publication_status' => 'scheduled',
                'publication_scheduled_at' => $scheduledAt,
                'publication_message' => $validated['message'],
                'publication_error' => null,
                'published_at' => null,
                'facebook_post_id' => null,
            ])->save();

            return back()->with('success', 'La publicacion quedo programada para ' . $scheduledAt->format('Y-m-d H:i') . '. Se publicara automaticamente cuando llegue esa fecha y hora.');
        }

        $result = $this->facebookService->publishTaskForUser($cliente, $tarea, $validated['message']);

        $tarea->forceFill([
            'publication_status' => $result['success'] ? 'published' : 'failed',
            'publication_scheduled_at' => null,
            'published_at' => $result['success'] ? now() : null,
            'facebook_post_id' => $result['facebook_post_id'] ?? null,
            'publication_error' => $result['success'] ? null : ($result['error'] ?? 'Error desconocido'),
            'publication_message' => $validated['message'],
        ])->save();

        if (! $result['success']) {
            return back()->withInput()->with('error', 'No se pudo publicar en Facebook: ' . ($result['error'] ?? 'Error desconocido de Meta.'));
        }

        return back()->with('success', 'Publicacion realizada correctamente en Facebook. ID devuelto por Meta: ' . ($result['facebook_post_id'] ?? 'sin ID'));
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
}

