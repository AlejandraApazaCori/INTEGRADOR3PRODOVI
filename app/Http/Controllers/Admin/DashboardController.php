<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Tarea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = today();
        $riskLimit = $today->copy()->addDays(7);
        $periodStart = now()->subDays(29)->startOfDay();

        $heatmapMonth = $today->copy()->startOfMonth();
        $requestedHeatmapMonth = (string) $request->input('heatmap_month', '');
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestedHeatmapMonth)) {
            $requestedMonth = Carbon::createFromFormat('Y-m', $requestedHeatmapMonth)->startOfMonth();
            if ($requestedMonth->lte($heatmapMonth)) {
                $heatmapMonth = $requestedMonth;
            }
        }
        $heatmapPreviousMonth = $heatmapMonth->copy()->subMonthNoOverflow()->format('Y-m');
        $heatmapCanGoNext = $heatmapMonth->lt($today->copy()->startOfMonth());
        $heatmapNextMonth = $heatmapCanGoNext ? $heatmapMonth->copy()->addMonthNoOverflow()->format('Y-m') : null;

        $applyRiskConstraint = function (Builder $query) use ($today, $riskLimit): Builder {
            return $query
                ->where('estado', 'activa')
                ->whereDate('fecha_inicio', '<=', $today)
                ->whereDate('fecha_fin', '>=', $today)
                ->where(function (Builder $query) use ($today, $riskLimit) {
                    $query->whereDate('fecha_fin', '<=', $riskLimit)
                        ->orWhereHas('tareas', fn (Builder $tasks) => $tasks
                            ->whereDate('fecha_limite', '<', $today)
                            ->where('estado', '!=', 'completada'));
                });
        };

        $campaignBase = Campania::query();
        $activeCampaigns = (clone $campaignBase)
            ->where('estado', 'activa')
            ->whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->count();
        $atRiskCampaigns = $applyRiskConstraint(clone $campaignBase)->count();
        $pausedCampaigns = (clone $campaignBase)->where('estado', 'pausada')->count();

        $taskBase = Tarea::query();
        $tasksDueToday = (clone $taskBase)
            ->whereDate('fecha_limite', $today)
            ->where('estado', '!=', 'completada')
            ->count();
        $overdueTasks = (clone $taskBase)
            ->whereDate('fecha_limite', '<', $today)
            ->where('estado', '!=', 'completada')
            ->count();

        $pendingPayments = Pago::where('estado', 'pendiente')
            ->where('metodo', 'fisico')
            ->count();

        $expiringSubscriptions = Suscripcion::where('estado', 'activa')
            ->whereNotNull('vigencia_activada_at')
            ->whereBetween('fecha_fin', [$today->copy()->startOfDay(), $riskLimit->copy()->endOfDay()])
            ->count();

        $currentMonthPayments = Pago::with('plan')
            ->where('estado', 'completado')
            ->whereBetween('fecha_pago', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
        $currentMonthIncome = (float) $currentMonthPayments->sum('monto');
        $completedPaymentsThisMonth = $currentMonthPayments->count();
        $monitoringQuery = Campania::with(['cliente', 'communityManager', 'suscripcion.empresa'])
            ->withCount([
                'tareas',
                'tareas as tareas_completadas_count' => fn (Builder $query) => $query->where('estado', 'completada'),
                'tareas as tareas_vencidas_count' => fn (Builder $query) => $query
                    ->whereDate('fecha_limite', '<', $today)
                    ->where('estado', '!=', 'completada'),
            ]);
        $campaigns = $monitoringQuery
            ->whereDate('fecha_fin', '>=', $today)
            ->latest('created_at')
            ->take(5)
            ->get();

        $pipeline = [
            'por_iniciar' => (clone $campaignBase)
                ->whereIn('estado', ['activa', 'pausada'])
                ->whereDate('fecha_inicio', '>', $today)
                ->count(),
            'en_curso' => max(0, $activeCampaigns - $atRiskCampaigns),
            'en_riesgo' => $atRiskCampaigns,
            'pausadas' => $pausedCampaigns,
            'finalizadas' => (clone $campaignBase)
                ->where(function (Builder $query) use ($today) {
                    $query->where('estado', 'finalizada')->orWhereDate('fecha_fin', '<', $today);
                })
                ->count(),
        ];

        $communityManagers = User::whereHas('roles', fn (Builder $query) => $query->where('nombre_rol', 'Community Manager'))
            ->orderBy('name')
            ->get();
        $managerIds = $communityManagers->pluck('id');
        $managerCampaignStats = Campania::whereIn('community_manager_id', $managerIds)
            ->whereIn('estado', ['activa', 'pausada'])
            ->whereDate('fecha_fin', '>=', $today)
            ->get()
            ->groupBy('community_manager_id');
        $managerTaskStats = Tarea::whereIn('asignado_id', $managerIds)
            ->where('estado', '!=', 'completada')
            ->get()
            ->groupBy('asignado_id');
        $managerWorkload = $communityManagers
            ->map(function (User $manager) use ($managerCampaignStats, $managerTaskStats, $today) {
                $tasks = $managerTaskStats->get($manager->id, collect());

                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'initials' => collect(explode(' ', $manager->name))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode(''),
                    'campaigns' => $managerCampaignStats->get($manager->id, collect())->count(),
                    'pending_tasks' => $tasks->count(),
                    'overdue_tasks' => $tasks->filter(fn (Tarea $task) => $task->fecha_limite?->lt($today))->count(),
                ];
            })
            ->sortByDesc(fn (array $manager) => $manager['overdue_tasks'] * 100 + $manager['pending_tasks'])
            ->take(6)
            ->values();

        $clientsWithoutCampaign = Suscripcion::where('estado', 'activa')
            ->whereHas('empresa')
            ->whereHas('pagos', fn (Builder $query) => $query->where('estado', 'completado'))
            ->whereDoesntHave('campanias', fn (Builder $query) => $query
                ->whereIn('estado', ['activa', 'pausada'])
                ->whereDate('fecha_fin', '>=', $today))
            ->count();
        $clientsWithoutCompany = Suscripcion::where('estado', 'activa')
            ->whereDoesntHave('empresa')
            ->count();
        $companiesWithoutSocial = Schema::hasTable('social_accounts')
            ? Empresa::whereDoesntHave('socialAccounts')
                ->count()
            : 0;
        $clientsWithoutRecentActivity = Campania::query()
            ->whereIn('estado', ['activa', 'pausada'])
            ->where('updated_at', '<', now()->subDays(7))
            ->whereDoesntHave('tareas', fn (Builder $query) => $query->where('updated_at', '>=', now()->subDays(7)))
            ->distinct('usuario_cliente_id')
            ->count('usuario_cliente_id');

        $alerts = collect([
            ['level' => 'danger', 'icon' => 'fa-triangle-exclamation', 'title' => 'Tareas vencidas', 'message' => $overdueTasks === 1 ? '1 tarea necesita atención inmediata.' : "$overdueTasks tareas necesitan atención inmediata.", 'count' => $overdueTasks, 'url' => route('administrador.campañas.index')],
            ['level' => 'warning', 'icon' => 'fa-bullhorn', 'title' => 'Campañas en riesgo', 'message' => 'Finalizan pronto o contienen tareas atrasadas.', 'count' => $atRiskCampaigns, 'url' => route('administrador.campañas.index')],
            ['level' => 'warning', 'icon' => 'fa-receipt', 'title' => 'Pagos por aprobar', 'message' => 'Pagos físicos pendientes de revisión.', 'count' => $pendingPayments, 'url' => route('administrador.pagos.pendientes-fisicos')],
            ['level' => 'info', 'icon' => 'fa-user-clock', 'title' => 'Clientes sin campaña', 'message' => 'Tienen pago aprobado y empresa registrada.', 'count' => $clientsWithoutCampaign, 'url' => route('administrador.campañas.index')],
            ['level' => 'info', 'icon' => 'fa-hourglass-half', 'title' => 'Suscripciones por vencer', 'message' => 'Vencen dentro de los próximos 7 días.', 'count' => $expiringSubscriptions, 'url' => route('administrador.pagos.index', ['payment_status' => 'completado', 'subscription_status' => 'activa'])],
        ])->filter(fn (array $alert) => $alert['count'] > 0)->values();

        $incomeMonths = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->subMonthsNoOverflow($monthsAgo)->startOfMonth());
        $incomeLabels = $incomeMonths->map(fn (Carbon $month) => ucfirst($month->translatedFormat('M y')))->values();
        $incomePaymentsByPlanAndMonth = Pago::where('estado', 'completado')
            ->whereBetween('fecha_pago', [$incomeMonths->first()->copy()->startOfMonth(), $incomeMonths->last()->copy()->endOfMonth()])
            ->get(['plan_id', 'monto', 'fecha_pago'])
            ->groupBy(fn (Pago $payment) => $payment->plan_id.'-'.$payment->fecha_pago->format('Y-m'))
            ->map(fn ($payments) => (float) $payments->sum('monto'));
        $incomeSeriesByPlan = Plan::orderBy('nombre')->get(['id', 'nombre'])
            ->map(fn (Plan $plan) => [
                'plan' => $plan->nombre,
                'values' => $incomeMonths->map(fn (Carbon $month) => $incomePaymentsByPlanAndMonth->get($plan->id.'-'.$month->format('Y-m'), 0))->values(),
            ])
            ->values();
        $incomeByPlan = $currentMonthPayments
            ->groupBy(fn (Pago $payment) => $payment->plan?->nombre ?? 'Sin plan')
            ->map(fn ($payments, string $plan) => ['plan' => $plan, 'total' => (float) $payments->sum('monto')])
            ->sortByDesc('total')
            ->take(5)
            ->values();
        $mostContractedPlan = Plan::withCount(['suscripciones as activas_count' => fn (Builder $query) => $query
            ->where('estado', 'activa')
            ->whereDate('fecha_fin', '>=', $today)])
            ->orderByDesc('activas_count')
            ->first();

        $heatmapStart = $heatmapMonth->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $heatmapEnd = $heatmapMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $heatmapEvents = collect();
        $addHeatmapEvent = function ($date, string $type) use ($heatmapEvents): void {
            $key = Carbon::parse($date)->format('Y-m-d');
            $events = $heatmapEvents->get($key, ['tasks' => 0, 'campaigns' => 0, 'publications' => 0]);
            $events[$type]++;
            $heatmapEvents->put($key, $events);
        };

        (clone $taskBase)->whereBetween('fecha_limite', [$heatmapStart, $heatmapEnd])
            ->where('estado', '!=', 'completada')
            ->get(['fecha_limite'])
            ->each(fn (Tarea $task) => $addHeatmapEvent($task->fecha_limite, 'tasks'));
        (clone $campaignBase)->whereBetween('fecha_fin', [$heatmapStart, $heatmapEnd])
            ->get(['fecha_fin'])
            ->each(fn (Campania $campaign) => $addHeatmapEvent($campaign->fecha_fin, 'campaigns'));
        if (Schema::hasColumn('tareas', 'publication_status') && Schema::hasColumn('tareas', 'publication_scheduled_at')) {
            (clone $taskBase)->where('publication_status', 'scheduled')
                ->whereBetween('publication_scheduled_at', [$heatmapStart->copy()->startOfDay(), $heatmapEnd->copy()->endOfDay()])
                ->get(['publication_scheduled_at'])
                ->each(fn (Tarea $task) => $addHeatmapEvent($task->publication_scheduled_at, 'publications'));
        }

        $heatmapMaximum = max(1, (int) $heatmapEvents->map(fn (array $events) => array_sum($events))->max());
        $deliveryHeatmap = collect();
        for ($date = $heatmapStart->copy(); $date->lte($heatmapEnd); $date->addDay()) {
            $events = $heatmapEvents->get($date->format('Y-m-d'), ['tasks' => 0, 'campaigns' => 0, 'publications' => 0]);
            $total = array_sum($events);
            $deliveryHeatmap->push([
                'date' => $date->copy(),
                'events' => $events,
                'total' => $total,
                'level' => $total === 0 ? 0 : max(1, min(4, (int) ceil(($total / $heatmapMaximum) * 4))),
                'is_current_month' => $date->isSameMonth($heatmapMonth),
                'is_today' => $date->isSameDay($today),
            ]);
        }

        $calendarItems = collect();
        $calendarCampaigns = (clone $campaignBase)
            ->where(function (Builder $query) use ($today) {
                $query->whereBetween('fecha_inicio', [$today, $today->copy()->addDays(30)])
                    ->orWhereBetween('fecha_fin', [$today, $today->copy()->addDays(30)]);
            })
            ->get();
        foreach ($calendarCampaigns as $campaign) {
            if (Carbon::parse($campaign->fecha_inicio)->betweenIncluded($today, $today->copy()->addDays(30))) {
                $calendarItems->push(['date' => Carbon::parse($campaign->fecha_inicio), 'type' => 'campaign', 'label' => 'Inicia campaña', 'title' => $campaign->nombre, 'url' => route('administrador.campañas.show', $campaign)]);
            }
            if (Carbon::parse($campaign->fecha_fin)->betweenIncluded($today, $today->copy()->addDays(30))) {
                $calendarItems->push(['date' => Carbon::parse($campaign->fecha_fin), 'type' => 'deadline', 'label' => 'Finaliza campaña', 'title' => $campaign->nombre, 'url' => route('administrador.campañas.show', $campaign)]);
            }
        }
        (clone $taskBase)->with('campania')->whereBetween('fecha_limite', [$today, $today->copy()->addDays(14)])
            ->where('estado', '!=', 'completada')->get()->each(function (Tarea $task) use ($calendarItems) {
                $calendarItems->push(['date' => $task->fecha_limite, 'type' => 'task', 'label' => 'Entrega de tarea', 'title' => $task->titulo, 'url' => route('administrador.tareas.show', $task)]);
            });
        Suscripcion::with('usuario')->where('estado', 'activa')->whereNotNull('vigencia_activada_at')
            ->whereBetween('fecha_fin', [$today, $today->copy()->addDays(30)->endOfDay()])
            ->get()->each(function (Suscripcion $subscription) use ($calendarItems) {
                $calendarItems->push(['date' => $subscription->fecha_fin, 'type' => 'subscription', 'label' => 'Vence suscripción', 'title' => $subscription->usuario?->name ?? 'Cliente', 'url' => route('administrador.pagos.index', ['payment_status' => 'completado', 'subscription_status' => 'activa'])]);
            });
        if (Schema::hasColumn('tareas', 'publication_scheduled_at')) {
            (clone $taskBase)->where('publication_status', 'scheduled')
                ->whereBetween('publication_scheduled_at', [now(), now()->addDays(30)])
                ->get()->each(function (Tarea $task) use ($calendarItems) {
                    $calendarItems->push(['date' => $task->publication_scheduled_at, 'type' => 'publication', 'label' => 'Publicación programada', 'title' => $task->titulo, 'url' => route('administrador.tareas.show', $task)]);
                });
        }
        $calendarItems = $calendarItems->sortBy('date')->take(5)->values();

        $recentActivity = collect();
        Pago::with(['usuario', 'plan'])->where('estado', 'completado')->where('created_at', '>=', $periodStart)
            ->latest()->take(4)->get()->each(function (Pago $payment) use ($recentActivity) {
                $recentActivity->push(['date' => $payment->created_at, 'icon' => 'fa-circle-check', 'type' => 'payment', 'title' => 'Pago completado', 'message' => ($payment->usuario?->name ?? 'Cliente').' · '.number_format((float) $payment->monto, 2, ',', '.').' Bs', 'url' => route('administrador.pagos.index', ['payment_status' => 'completado'])]);
        });
        $recentCampaignsQuery = Campania::with('cliente')->where('created_at', '>=', $periodStart);
        $recentCampaignsQuery->latest()->take(4)->get()->each(function (Campania $campaign) use ($recentActivity) {
            $recentActivity->push(['date' => $campaign->created_at, 'icon' => 'fa-bullhorn', 'type' => 'campaign', 'title' => 'Campaña creada', 'message' => $campaign->nombre.' · '.($campaign->cliente?->name ?? 'Sin cliente'), 'url' => route('administrador.campañas.show', $campaign)]);
        });
        (clone $taskBase)->with('campania')->where('estado', 'completada')->where('updated_at', '>=', $periodStart)->latest('updated_at')->take(4)->get()->each(function (Tarea $task) use ($recentActivity) {
            $recentActivity->push(['date' => $task->updated_at, 'icon' => 'fa-list-check', 'type' => 'task', 'title' => 'Tarea completada', 'message' => $task->titulo.' · '.($task->campania?->nombre ?? 'Sin campaña'), 'url' => route('administrador.tareas.show', $task)]);
        });
        $recentActivity = $recentActivity->sortByDesc('date')->take(5)->values();

        return view('administrador.dashboard', compact(
            'activeCampaigns', 'atRiskCampaigns',
            'tasksDueToday', 'overdueTasks', 'pendingPayments', 'expiringSubscriptions',
            'currentMonthIncome', 'completedPaymentsThisMonth',
            'campaigns', 'pipeline', 'alerts', 'managerWorkload', 'clientsWithoutCampaign',
            'clientsWithoutCompany', 'companiesWithoutSocial', 'clientsWithoutRecentActivity', 'incomeLabels', 'incomeSeriesByPlan', 'incomeByPlan',
            'mostContractedPlan', 'deliveryHeatmap', 'heatmapMonth', 'heatmapPreviousMonth', 'heatmapNextMonth', 'heatmapCanGoNext',
            'calendarItems', 'recentActivity'
        ));
    }
}
