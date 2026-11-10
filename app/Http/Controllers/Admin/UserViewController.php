<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Cliente\PlanController;
use App\Models\User;
use App\Models\Campania;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserViewController extends Controller
{
    public function show($id)
    {
        $user = User::with(['roles', 'suscripciones.plan.caracteristicas', 'empresas'])->findOrFail($id);

        if (! $user->roles->contains('nombre_rol', 'Cliente')) {
            return redirect()->route('administrador.usuarios.seguimiento', $user);
        }

        $suscripcionActiva = $user->suscripciones()
            ->with('plan.caracteristicas')
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->first();

        $campaniaActual = $user->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();

        $diasRestantes = 0;
        $porcentajeRestante = 0;

        if ($suscripcionActiva) {
            $fechaFin = Carbon::parse($suscripcionActiva->fecha_fin);
            $diasRestantes = now()->diffInDays($fechaFin, false);
            $diasTotales = $suscripcionActiva->fecha_inicio->diffInDays($fechaFin);
            $porcentajeRestante = $diasRestantes > 0 ? round(($diasRestantes / $diasTotales) * 100) : 0;
        }

        $empresas = $user->empresas;
        if ($user->socialAccountsTableExists()) {
            $empresas->load('socialAccounts');
        }
        $globalSocialAccounts = $user->linkedSocialAccounts();
        $planResponse = app(PlanController::class)->getPlanContratado((int) $user->id, true);
        $accountPlans = $planResponse->getStatusCode() === 200
            ? $planResponse->getData(true)
            : ['plan' => null, 'plans' => []];
        $accountUser = $user;
        $adminPreview = true;

        return view('clientes.micuenta', compact(
            'user',
            'accountUser',
            'adminPreview',
            'accountPlans',
            'suscripcionActiva',
            'campaniaActual',
            'diasRestantes',
            'porcentajeRestante',
            'empresas',
            'globalSocialAccounts'
        ));
    }

    public function campaignAnalytics($id)
    {
        $user = User::findOrFail($id);

        $campaniaActual = $user->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();

        $data = $this->loadAnalyticsData('last30days', (int) $id);

        return view('administrador.campanias.analiticasusuario', compact('user', 'campaniaActual', 'data'));
    }

    public function campaignHistory(User $user)
    {
        $campaigns = $user->campaniasCliente()
            ->withTrashed()
            ->with(['suscripcion.plan', 'suscripcion.empresa', 'empresas'])
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->paginate(12);

        return view('administrador.usuarios.historial-campanias', compact('user', 'campaigns'));
    }

    private function staffCampaigns(User $user): Builder
    {
        return Campania::query()->where(function (Builder $query) use ($user) {
            $query->where('usuario_creador_id', $user->id)
                ->orWhere('community_manager_id', $user->id)
                ->orWhere('disenador_id', $user->id)
                ->orWhereHas('disenadores', fn (Builder $q) => $q->where('users.id', $user->id))
                ->orWhereHas('tareas', fn (Builder $q) => $this->staffTasks($q, $user))
                ->orWhereHas('tareas.archivos', fn (Builder $q) => $q->where('user_id', $user->id));
        });
    }

    private function staffTasks(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('asignado_id', $user->id)
                ->orWhereHas('responsables', fn (Builder $responsables) => $responsables->where('users.id', $user->id));
        });
    }

    public function staffTracking(User $user)
    {
        $user->load('roles');
        abort_if($user->roles->contains('nombre_rol', 'Cliente'), 404);

        $campaignIds = $this->staffCampaigns($user)->pluck('id');
        $campaigns = $this->staffCampaigns($user)
            ->with(['cliente:id,name', 'suscripcion.empresa', 'disenadores:id,name'])
            ->withCount([
                'tareas',
                'tareas as tareas_asignadas_count' => fn (Builder $q) => $this->staffTasks($q, $user),
                'tareas as tareas_terminadas_count' => fn (Builder $q) => $this->staffTasks($q, $user)
                    ->whereIn('estado', ['entregado', 'aprobado', 'publicado']),
            ])
            ->orderByDesc('fecha_inicio')->orderByDesc('id')
            ->paginate(12);

        $assigned = $this->staffTasks(Tarea::query()->whereIn('campania_id', $campaignIds), $user);
        $taskCounts = (clone $assigned)->selectRaw("COUNT(*) as total, SUM(CASE WHEN estado IN ('entregado','aprobado','publicado') THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN estado NOT IN ('entregado','aprobado','publicado') AND fecha_limite < ? THEN 1 ELSE 0 END) as overdue", [now()->toDateString()])->first();
        $uploadedFiles = TareaArchivo::query()->where('user_id', $user->id)
            ->whereHas('tarea', fn (Builder $q) => $q->whereIn('campania_id', $campaignIds))->count();
        $statusCounts = Campania::query()->whereIn('id', $campaignIds)
            ->select('estado', DB::raw('COUNT(*) as total'))->groupBy('estado')->pluck('total', 'estado');

        return view('administrador.usuarios.seguimiento', compact('user', 'campaigns', 'taskCounts', 'uploadedFiles', 'statusCounts'));
    }

    public function staffCampaign(User $user, Campania $campania)
    {
        $user->load('roles');
        abort_if($user->roles->contains('nombre_rol', 'Cliente'), 404);
        abort_unless($this->staffCampaigns($user)->whereKey($campania->id)->exists(), 404);

        $campania->load(['cliente:id,name', 'creador:id,name', 'communityManager:id,name', 'disenadores:id,name', 'suscripcion.empresa']);
        $isCreator = (int) $campania->usuario_creador_id === (int) $user->id;
        $tasksQuery = Tarea::query()->where('campania_id', $campania->id);
        if (! $isCreator) {
            $tasksQuery->where(function (Builder $q) use ($user) {
                $this->staffTasks($q, $user)
                    ->orWhereHas('archivos', fn (Builder $files) => $files->where('user_id', $user->id));
            });
        }
        $tasks = $tasksQuery->with(['asignado:id,name', 'responsables' => fn ($q) => $q->where('users.id', $user->id), 'archivos' => fn ($q) => $q->where('user_id', $user->id)->latest()])
            ->withCount(['archivos as archivos_usuario_count' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderBy('fecha_limite')->orderBy('id')->paginate(20);

        return view('administrador.usuarios.seguimiento-campania', compact('user', 'campania', 'tasks', 'isCreator'));
    }

    private function loadAnalyticsData(string $periodKey, int $userId): array
    {
        $campaignJsonPath = resource_path('data/analiticas_por_campania.json');
        if (file_exists($campaignJsonPath)) {
            $campaignJson = json_decode(file_get_contents($campaignJsonPath), true);
            $campaignData = $campaignJson['usuarios'][(string) $userId]['periodos'][$periodKey] ?? null;

            if (is_array($campaignData)) {
                return $campaignData;
            }
        }

        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            return $allData[$periodKey] ?? [];
        }

        return [];
    }
}
