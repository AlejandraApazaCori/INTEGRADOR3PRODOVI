<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\MetaCampaignAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteAnaliticasController extends Controller
{
    public function index(MetaCampaignAnalyticsService $analyticsService)
    {
        $campaniaActual = Auth::user()
            ->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();
        $campaniaIniciada = $campaniaActual
            && Carbon::parse($campaniaActual->fecha_inicio, config('app.timezone'))
                ->startOfDay()
                ->lte(today(config('app.timezone')));

        $data = $this->loadAnalyticsData('thisyear');
        $empresas = Auth::user()->empresas()
            ->with('socialAccounts')
            ->orderBy('nombre_empresa')
            ->get();

        $empresas->each(function (Empresa $empresa) use ($analyticsService) {
            $empresa->setAttribute(
                'analytics_providers',
                $analyticsService->connectedProvidersForCompany($empresa)
            );
        });

        return view('clientes.analiticas', compact('data', 'campaniaActual', 'campaniaIniciada', 'empresas'));
    }

    public function companyData(Request $request, Empresa $empresa, MetaCampaignAnalyticsService $analyticsService)
    {
        abort_unless($empresa->usuario_id === Auth::id(), 404);

        $validated = $request->validate([
            'days' => 'nullable|in:7,30,90,365,730,all',
        ]);

        return response()->json(
            $analyticsService->forCompany($empresa, $validated['days'] ?? 'all')
        );
    }

    public function loadView(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        if ($request->boolean('meta')) {
            $validated = $request->validate([
                'empresa_id' => 'required|integer',
                'days' => 'nullable|in:7,30,90,365,730,all',
            ]);
            $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($validated['empresa_id']);

            return response()->json(
                $analyticsService->forCompany($empresa, $validated['days'] ?? 'all')
            );
        }

        $periodKey = $this->resolvePeriodKey($request->input('view', 'historial'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);

        return view('clientes.analiticas.partials.analiticas', compact('data'));
    }

    public function exportarPDF(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('periodo', 'historial'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $jsonData = $this->loadAnalyticsData($periodKey, $userId);

        $data = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $jsonData,
        ];

        $pdf = Pdf::loadView('pdf.analiticasEmpresa', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_analiticas_'.$request->input('periodo', 'historial').'.pdf');
    }

    public function exportarReporteEngagement(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'view' => 'nullable|in:7dias,30dias,anual,historial',
            'empresa_id' => 'nullable|integer',
        ]);
        $empresa = Empresa::where('usuario_id', Auth::id())
            ->when(
                filled($validated['empresa_id'] ?? null),
                fn ($query) => $query->whereKey($validated['empresa_id'])
            )
            ->orderBy('id')
            ->firstOrFail();
        $days = match ($validated['view'] ?? 'historial') {
            '7dias' => 7,
            '30dias' => 30,
            'anual' => 365,
            default => 'all',
        };
        $analytics = $analyticsService->forCompany($empresa, $days);
        $data = $this->engagementReportData($analytics, $empresa);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_engagement', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_engagement_'.$request->input('view', 'historial').'.pdf');
    }

    private function engagementReportData(array $analytics, Empresa $empresa): array
    {
        $summary = $analytics['summary'] ?? [];
        $totals = $summary['totals'] ?? [];
        $reach = (float) ($totals['reach'] ?? 0);
        $engagement = (float) ($totals['engagement'] ?? 0);
        $rate = $reach > 0 ? round(($engagement / $reach) * 100, 2) : null;
        $platforms = collect($analytics['platforms'] ?? [])->filter(fn (array $platform) => $platform['connected'] ?? false);
        $interaction = fn (string $metric) => $platforms->sum(fn (array $platform) => (float) data_get($platform, 'engagement.'.$metric, 0));
        $contentTypes = collect($summary['content_types'] ?? [])->map(fn (array $item) => [
            'type' => $item['type'] ?? 'Contenido',
            'posts' => (int) ($item['posts'] ?? 0),
            'interactions' => (float) ($item['engagement'] ?? 0),
            'average' => (float) ($item['average_engagement'] ?? 0),
        ])->values()->all();
        $platformPerformance = $platforms->map(fn (array $platform) => [
            'platform' => ucfirst($platform['platform'] ?? 'Meta'),
            'posts' => (int) data_get($platform, 'totals.posts', 0),
            'interactions' => (float) data_get($platform, 'totals.engagement', 0),
            'average' => (float) data_get($platform, 'totals.average_engagement', 0),
        ])->values()->all();
        $bestTime = collect(data_get($summary, 'best_posting_times.best', []))->first();
        $age = collect(data_get($summary, 'audience.age_gender', []))
            ->filter(fn (array $item) => str_starts_with($item['name'] ?? '', 'Edad '))
            ->sortByDesc('value')->first();
        $gender = collect(data_get($summary, 'audience.age_gender', []))
            ->filter(fn (array $item) => str_starts_with($item['name'] ?? '', 'Sexo '))
            ->sortByDesc('value')->first();

        return [
            'company' => $empresa->nombre_empresa,
            'period_label' => data_get($analytics, 'period.days') === 'all'
                ? 'Todo el historial disponible'
                : 'Últimos '.data_get($analytics, 'period.days', 30).' días',
            'period' => $analytics['period'] ?? [],
            'totals' => $totals,
            'engagement' => [
                'rate' => $rate,
                'interactions' => $engagement,
                'reach' => $totals['reach'] ?? null,
                'average_per_post' => $totals['average_engagement'] ?? null,
            ],
            'interactions_breakdown' => [
                'likes' => $interaction('reactions'),
                'comments' => $interaction('comments'),
                'shares' => $interaction('shares'),
                'saves' => $interaction('saves'),
            ],
            'engagement_by_type' => $contentTypes,
            'engagement_by_platform' => $platformPerformance,
            'optimal_time' => [
                'range' => $bestTime['label'] ?? '18:00 a 21:00',
                'samples' => $bestTime['samples'] ?? 0,
                'estimated' => $bestTime === null,
            ],
            'audience' => [
                'age' => $age,
                'gender' => $gender,
                'estimated' => ! $age && ! $gender,
            ],
            'data_source' => 'Meta Insights',
            'generated_at' => $analytics['generated_at'] ?? now()->toIso8601String(),
        ];
    }

    public function exportarReporteAlcance(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'view' => 'nullable|in:7dias,30dias,anual,historial',
            'empresa_id' => 'nullable|integer',
        ]);
        $empresa = Empresa::where('usuario_id', Auth::id())
            ->when(
                filled($validated['empresa_id'] ?? null),
                fn ($query) => $query->whereKey($validated['empresa_id'])
            )
            ->orderBy('id')
            ->firstOrFail();
        $days = match ($validated['view'] ?? 'historial') {
            '7dias' => 7,
            '30dias' => 30,
            'anual' => 365,
            default => 'all',
        };
        $analytics = $analyticsService->forCompany($empresa, $days);
        $data = $this->reachReportData($analytics, $empresa);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_alcance', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_alcance_'.$request->input('view', 'historial').'.pdf');
    }

    private function reachReportData(array $analytics, Empresa $empresa): array
    {
        $summary = $analytics['summary'] ?? [];
        $totals = $summary['totals'] ?? [];
        $platforms = collect($analytics['platforms'] ?? [])
            ->filter(fn (array $platform) => $platform['connected'] ?? false);
        $platformReachTotal = $platforms->sum(
            fn (array $platform) => (float) data_get($platform, 'totals.reach', 0)
        );
        $reachByPlatform = $platforms->map(function (array $platform) use ($platformReachTotal) {
            $reach = data_get($platform, 'totals.reach');

            return [
                'platform' => ucfirst($platform['platform'] ?? 'Meta'),
                'reach' => $reach !== null ? (float) $reach : null,
                'percentage' => $reach !== null && $platformReachTotal > 0
                    ? round(((float) $reach / $platformReachTotal) * 100, 1)
                    : null,
            ];
        })->values();
        $postsWithReach = $platforms
            ->flatMap(fn (array $platform) => $platform['posts'] ?? [])
            ->filter(fn (array $post) => ($post['reach'] ?? null) !== null)
            ->values();
        $postReachTotal = (float) $postsWithReach->sum('reach');
        $reachByType = $postsWithReach->groupBy(fn (array $post) => $post['type'] ?? 'POST')
            ->map(function ($posts, string $type) use ($postReachTotal) {
                $reach = (float) $posts->sum('reach');

                return [
                    'type' => $type,
                    'posts' => $posts->count(),
                    'reach' => $reach,
                    'average_reach' => round((float) $posts->avg('reach'), 1),
                    'percentage' => $postReachTotal > 0 ? round(($reach / $postReachTotal) * 100, 1) : 0,
                ];
            })->sortByDesc('reach')->values()->all();
        $topPublications = $postsWithReach->sortByDesc('reach')->take(5)->map(fn (array $post) => [
            'platform' => ucfirst($post['platform'] ?? 'Meta'),
            'type' => $post['type'] ?? 'POST',
            'date' => filled($post['timestamp'] ?? null)
                ? Carbon::parse($post['timestamp'])->setTimezone(config('app.timezone'))->format('d/m/Y')
                : 'Sin fecha',
            'reach' => (float) ($post['reach'] ?? 0),
            'caption' => $post['caption'] ?? null,
        ])->values()->all();
        $location = collect(data_get($summary, 'audience.cities', []))->sortByDesc('value')->first()
            ?? collect(data_get($summary, 'audience.countries', []))->sortByDesc('value')->first();
        $leadingPlatform = $reachByPlatform->whereNotNull('reach')->sortByDesc('reach')->first();

        return [
            'company' => $empresa->nombre_empresa,
            'period_label' => data_get($analytics, 'period.days') === 'all'
                ? 'Todo el historial disponible'
                : 'Últimos '.data_get($analytics, 'period.days', 30).' días',
            'period' => $analytics['period'] ?? [],
            'reach' => [
                'total' => $totals['reach'] ?? null,
                'available' => ($totals['reach'] ?? null) !== null,
            ],
            'reach_by_platform' => $reachByPlatform->all(),
            'reach_by_type' => $reachByType,
            'top_publications' => $topPublications,
            'analysis' => [
                'leading_platform' => $leadingPlatform,
                'location' => $location,
            ],
            'data_source' => 'Meta Insights',
            'generated_at' => $analytics['generated_at'] ?? now()->toIso8601String(),
        ];
    }

    public function exportarReporteSeguidores(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'view' => 'nullable|in:7dias,30dias,anual,historial',
            'empresa_id' => 'nullable|integer',
        ]);
        $empresa = Empresa::where('usuario_id', Auth::id())
            ->when(
                filled($validated['empresa_id'] ?? null),
                fn ($query) => $query->whereKey($validated['empresa_id'])
            )
            ->orderBy('id')
            ->firstOrFail();
        $days = match ($validated['view'] ?? 'historial') {
            '7dias' => 7,
            '30dias' => 30,
            'anual' => 365,
            default => 'all',
        };
        $analytics = $analyticsService->forCompany($empresa, $days);
        $data = $this->followersReportData($analytics, $empresa);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_seguidores', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_seguidores_'.$request->input('view', 'historial').'.pdf');
    }

    private function followersReportData(array $analytics, Empresa $empresa): array
    {
        $platforms = collect($analytics['platforms'] ?? [])
            ->filter(fn (array $platform) => $platform['connected'] ?? false)
            ->map(function (array $platform) {
                $current = data_get($platform, 'totals.followers');
                $series = collect(data_get($platform, 'followers.values', []))
                    ->filter(fn ($value) => $value !== null)
                    ->values();
                $hasGrowth = $series->count() >= 2;
                $initial = $hasGrowth ? (float) $series->first() : null;
                $latest = $hasGrowth ? (float) $series->last() : null;
                $change = $hasGrowth ? $latest - $initial : null;

                return [
                    'platform' => ucfirst($platform['platform'] ?? 'Meta'),
                    'current' => $current !== null ? (float) $current : null,
                    'initial' => $initial,
                    'change' => $change,
                    'growth_percent' => $hasGrowth && $initial > 0
                        ? round(($change / $initial) * 100, 2)
                        : null,
                    'growth_available' => $hasGrowth,
                ];
            })->values();
        $knownFollowers = $platforms->whereNotNull('current');
        $total = data_get($analytics, 'summary.totals.followers');
        $distributionTotal = (float) $knownFollowers->sum('current');
        $platforms = $platforms->map(function (array $platform) use ($distributionTotal) {
            $platform['percentage'] = $platform['current'] !== null && $distributionTotal > 0
                ? round(($platform['current'] / $distributionTotal) * 100, 1)
                : null;

            return $platform;
        });
        $growthAvailable = $platforms->isNotEmpty()
            && $platforms->every(fn (array $platform) => $platform['growth_available']);
        $netGrowth = $growthAvailable ? (float) $platforms->sum('change') : null;
        $initialTotal = $growthAvailable ? (float) $platforms->sum('initial') : null;
        $growthPercent = $growthAvailable && $initialTotal > 0
            ? round(($netGrowth / $initialTotal) * 100, 2)
            : null;
        $leader = $platforms->where('growth_available', true)->sortByDesc('change')->first();
        $measurementSince = data_get($analytics, 'period.insights_since')
            ?? data_get($analytics, 'period.since');
        $measurementUntil = data_get($analytics, 'period.until');

        return [
            'company' => $empresa->nombre_empresa,
            'period_label' => data_get($analytics, 'period.days') === 'all'
                ? 'Todo el historial disponible'
                : 'Últimos '.data_get($analytics, 'period.days', 30).' días',
            'followers' => [
                'total' => $total,
                'available' => $total !== null,
                'net_growth' => $netGrowth,
                'growth_percent' => $growthPercent,
                'growth_available' => $growthAvailable,
            ],
            'platforms' => $platforms->all(),
            'growth_leader' => $leader,
            'measurement' => [
                'since' => filled($measurementSince) ? Carbon::parse($measurementSince)->format('d/m/Y') : null,
                'until' => filled($measurementUntil) ? Carbon::parse($measurementUntil)->format('d/m/Y') : null,
                'limited' => (bool) data_get($analytics, 'period.insights_limited', false),
            ],
            'data_source' => 'Meta Insights',
            'generated_at' => $analytics['generated_at'] ?? now()->toIso8601String(),
        ];
    }

    public function exportarReporteCTR(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'view' => 'nullable|in:7dias,30dias,anual,historial',
            'empresa_id' => 'nullable|integer',
        ]);
        $empresa = Empresa::where('usuario_id', Auth::id())
            ->when(
                filled($validated['empresa_id'] ?? null),
                fn ($query) => $query->whereKey($validated['empresa_id'])
            )
            ->orderBy('id')
            ->firstOrFail();
        $days = match ($validated['view'] ?? 'historial') {
            '7dias' => 7,
            '30dias' => 30,
            'anual' => 365,
            default => 'all',
        };
        $analytics = $analyticsService->forCompany($empresa, $days);
        $data = $this->ctrReportData($analytics, $empresa);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_ctr', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('reporte_ctr_'.$request->input('view', 'historial').'.pdf');
    }

    private function ctrReportData(array $analytics, Empresa $empresa): array
    {
        $platforms = collect($analytics['platforms'] ?? [])
            ->filter(fn (array $platform) => $platform['connected'] ?? false)
            ->map(function (array $platform) {
                $posts = collect($platform['posts'] ?? []);
                $views = data_get($platform, 'totals.views');
                $viewsEstimated = false;

                if ($views === null) {
                    $postViews = $posts->filter(fn (array $post) => ($post['views'] ?? null) !== null);
                    if ($postViews->isNotEmpty()) {
                        $views = (float) $postViews->sum('views');
                    } elseif (data_get($platform, 'totals.reach') !== null) {
                        // Aproximación conservadora de frecuencia cuando Meta solo libera alcance.
                        $views = round((float) data_get($platform, 'totals.reach') * 1.15);
                        $viewsEstimated = true;
                    }
                }

                $clicks = data_get($platform, 'totals.clicks');
                $clicksEstimated = false;
                if ($clicks === null) {
                    $postsWithClicks = $posts->filter(fn (array $post) => ($post['clicks'] ?? null) !== null);
                    if ($postsWithClicks->isNotEmpty()) {
                        $clicks = (float) $postsWithClicks->sum('clicks');
                    } elseif ($views !== null && data_get($platform, 'totals.engagement') !== null) {
                        // Proxy: una fracción conservadora de las interacciones observadas.
                        $clicks = min(
                            (float) $views,
                            round((float) data_get($platform, 'totals.engagement') * 0.12)
                        );
                        $clicksEstimated = true;
                    }
                }

                $ctr = $views !== null && (float) $views > 0 && $clicks !== null
                    ? round(((float) $clicks / (float) $views) * 100, 2)
                    : null;

                return [
                    'platform' => ucfirst($platform['platform'] ?? 'Meta'),
                    'impressions' => $views !== null ? (float) $views : null,
                    'clicks' => $clicks !== null ? (float) $clicks : null,
                    'ctr' => $ctr,
                    'estimated' => $viewsEstimated || $clicksEstimated,
                    'views_estimated' => $viewsEstimated,
                    'clicks_estimated' => $clicksEstimated,
                ];
            })->values();
        $comparable = $platforms->whereNotNull('ctr');
        $totalImpressions = $comparable->sum('impressions');
        $totalClicks = $comparable->sum('clicks');
        $rate = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : null;

        return [
            'company' => $empresa->nombre_empresa,
            'period_label' => data_get($analytics, 'period.days') === 'all'
                ? 'Todo el historial disponible'
                : 'Últimos '.data_get($analytics, 'period.days', 30).' días',
            'conversion' => [
                'rate' => $rate,
                'clicks' => $comparable->isNotEmpty() ? (float) $totalClicks : null,
                'impressions' => $comparable->isNotEmpty() ? (float) $totalImpressions : null,
                'estimated' => $comparable->contains(fn (array $platform) => $platform['estimated']),
                'platform_metrics' => $platforms->all(),
                'best_platform' => $comparable->sortByDesc('ctr')->first(),
            ],
            'data_source' => 'Meta Insights',
            'generated_at' => $analytics['generated_at'] ?? now()->toIso8601String(),
        ];
    }

    private function resolvePeriodKey(string $view): string
    {
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear',
            'historial' => 'thisyear',
        ];

        return $periodMap[$view] ?? 'last30days';
    }

    private function loadAnalyticsData(string $periodKey, ?int $userId = null): array
    {
        $resolvedUserId = $userId ?: Auth::id();
        $campaignJsonPath = resource_path('data/analiticas_por_campania.json');

        if ($resolvedUserId && file_exists($campaignJsonPath)) {
            $campaignJson = json_decode(file_get_contents($campaignJsonPath), true);
            $campaignData = $campaignJson['usuarios'][(string) $resolvedUserId]['periodos'][$periodKey] ?? null;

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
