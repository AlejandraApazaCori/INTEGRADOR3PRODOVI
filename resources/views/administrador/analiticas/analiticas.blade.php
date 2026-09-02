@extends('layouts.app')

@section('title', 'Analíticas de campañas')

@section('content')
    @php
        $dashboard = $dashboard ?? null;
        $usingFallback = $usingFallback ?? ($dashboard === null);
        $selectedDays = $selectedDays ?? 30;

        if ($dashboard) {
            $monthName = $dashboard['monthName'];
            $campaignMetrics = $dashboard['campaignMetrics'];
            $campaignsByUser = $dashboard['campaignsByUser'];
            $dailyLabels = $dashboard['dailyLabels'];
            $dailyPerformance = $dashboard['dailyPerformance'];
            $campaignReach = $dashboard['campaignReach'];
            $statusDistribution = $dashboard['statusDistribution'];
            $heatmapHours = $dashboard['heatmapHours'];
            $heatmapRows = $dashboard['heatmapRows'];
            $topHorarios = $dashboard['topHorarios'];
            $heatmapSummary = $dashboard['heatmapSummary'];
            $heatmapModel = $dashboard['heatmapModel'];
            $scoreMin = $dashboard['scoreMin'];
            $scoreMax = $dashboard['scoreMax'];
            $totalCampaigns = $dashboard['totalCampaigns'];
            $totalReach = $dashboard['totalReach'];
            $totalInteractions = $dashboard['totalInteractions'];
            $averageEngagement = $dashboard['averageEngagement'];
            $recommendedCampaign = $dashboard['recommendedCampaign'];
        } else {
        $monthName = now()->locale('es')->translatedFormat('F Y');

        $allCampaigns = \App\Models\Campania::with(['cliente', 'communityManager'])
            ->orderByDesc('fecha_inicio')
            ->get();

        $campaignMetrics = $allCampaigns->values()->map(function ($campaign, $index) {
            $durationDays = 30;
            if ($campaign->fecha_inicio && $campaign->fecha_fin) {
                $durationDays = max(7, \Carbon\Carbon::parse($campaign->fecha_inicio)->diffInDays(\Carbon\Carbon::parse($campaign->fecha_fin)) + 1);
            }

            $baseReach = 7200 + (($campaign->id * 137) % 9200) + (($index + 1) * 680) + min($durationDays, 90) * 92;
            $statusBoost = match ($campaign->estado) {
                'activa' => 2200,
                'pausada' => 1200,
                'finalizada' => 700,
                default => 500,
            };

            $reach = (int) round($baseReach + $statusBoost);
            $engagement = round(min(14.2, 8.1 + (($campaign->id * 17) % 38) / 10 + ($campaign->estado === 'activa' ? 1.1 : 0.4)), 1);
            $interactions = (int) round($reach * (($engagement / 100) * 1.08));

            return [
                'id' => $campaign->id,
                'campaign' => $campaign->nombre,
                'user' => $campaign->cliente->name ?? 'Cliente sin nombre',
                'reach' => $reach,
                'interactions' => $interactions,
                'engagement' => $engagement,
                'status' => $campaign->estado,
            ];
        });

        if ($campaignMetrics->isEmpty()) {
            $campaignMetrics = collect([
                ['id' => 1, 'campaign' => 'Campaña Base', 'user' => 'Cliente Base', 'reach' => 11800, 'interactions' => 1322, 'engagement' => 11.2, 'status' => 'activa'],
                ['id' => 2, 'campaign' => 'Campaña Impulso', 'user' => 'Cliente Base', 'reach' => 10400, 'interactions' => 1092, 'engagement' => 10.5, 'status' => 'pausada'],
            ]);
        }

        $campaignsByUser = $campaignMetrics
            ->groupBy('user')
            ->map(function ($items, $userName) {
                $campaignsCount = $items->count();
                $reach = (int) $items->sum('reach');
                $interactions = (int) $items->sum('interactions');
                $engagement = round($items->avg('engagement'), 1);

                return [
                    'user' => $userName,
                    'campaigns' => $campaignsCount,
                    'reach' => $reach,
                    'interactions' => $interactions,
                    'engagement' => $engagement,
                ];
            })
            ->sortByDesc('reach')
            ->take(5)
            ->values()
            ->all();

        $dailyPerformance = [];
        $daysInMonth = now()->daysInMonth;
        $dailyBase = max(180, (int) round($campaignMetrics->sum('interactions') / max($daysInMonth, 1) / 1.35));
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $wave = sin($day / 4.3) * 34;
            $growth = $day * 6.5;
            $dailyPerformance[] = (int) round($dailyBase + $wave + $growth + (($day % 5) * 11));
        }

        $campaignReach = $campaignMetrics
            ->sortByDesc('reach')
            ->take(6)
            ->map(fn ($item) => [
                'campaign' => $item['campaign'],
                'reach' => $item['reach'],
                'engagement' => $item['engagement'],
            ])
            ->values()
            ->all();

        $statusDistribution = [
            'Activa' => (int) $campaignMetrics->where('status', 'activa')->count(),
            'Pausada' => (int) $campaignMetrics->where('status', 'pausada')->count(),
            'Finalizada' => (int) $campaignMetrics->where('status', 'finalizada')->count(),
            'Planificada' => max(1, (int) ceil($campaignMetrics->count() * 0.15)),
            'Revision' => max(1, (int) ceil($campaignMetrics->count() * 0.08)),
        ];

        $heatmapJsonPath = resource_path('data/horarios_lstm_facebook.json');
        $heatmapSource = [];

        if (\Illuminate\Support\Facades\File::exists($heatmapJsonPath)) {
            $heatmapSource = json_decode(\Illuminate\Support\Facades\File::get($heatmapJsonPath), true) ?? [];
        }

        $dayNames = [
            0 => 'Lunes',
            1 => 'Martes',
            2 => 'Miércoles',
            3 => 'Jueves',
            4 => 'Viernes',
            5 => 'Sábado',
            6 => 'Domingo',
        ];

        $existingTopHorarios = collect($heatmapSource['topHorarios'] ?? [])
            ->map(function ($item) use ($dayNames) {
                if (isset($item['dia_semana']) && array_key_exists((int) $item['dia_semana'], $dayNames)) {
                    $item['dia'] = $dayNames[(int) $item['dia_semana']];
                }
                return $item;
            })
            ->filter(fn ($item) => isset($item['dia'], $item['hora'], $item['engagement_score']));

        $scoresExistentes = $existingTopHorarios->pluck('engagement_score')->map(fn ($v) => (float) $v);
        $scoreMinReal = $scoresExistentes->min() ?? 130;
        $scoreMaxReal = $scoresExistentes->max() ?? 140;

        $diasExistentes = $existingTopHorarios->pluck('dia')->unique()->values()->all();
        $horasExistentes = $existingTopHorarios->pluck('hora')->unique()->sort()->values()->all();

        if (empty($horasExistentes)) {
            $horasExistentes = ['09:00', '12:00', '15:00', '18:00', '20:00'];
        }

        $todosLosDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $diasFaltantes = array_diff($todosLosDias, $diasExistentes);

        $datosGenerados = [];
        foreach ($diasFaltantes as $dia) {
            $diaSemanaIndex = array_search($dia, $todosLosDias);
            $cantidadHorasConDato = rand(3, 5);
            $horasSeleccionadas = array_rand(array_flip($horasExistentes), $cantidadHorasConDato);
            if (!is_array($horasSeleccionadas)) {
                $horasSeleccionadas = [$horasSeleccionadas];
            }

            foreach ($horasSeleccionadas as $hora) {
                $score = rand(11000, 12800) / 100;
                if (in_array($hora, ['13:00', '18:00', '20:00']) && rand(1, 4) === 1) {
                    $score = rand(12500, 12950) / 100;
                }

                $datosGenerados[] = [
                    'dia_semana' => $diaSemanaIndex,
                    'dia' => $dia,
                    'hora' => $hora,
                    'engagement_score' => round($score, 4),
                    'es_generado' => true,
                ];
            }
        }

        $allTopHorarios = $existingTopHorarios->map(function ($item) {
            $item['es_generado'] = false;
            return $item;
        })->values()->toArray();
        $allTopHorarios = array_merge($allTopHorarios, $datosGenerados);
        $topHorarios = collect($allTopHorarios);

        $heatmapHours = $topHorarios->pluck('hora')->unique()->sort()->values()->all();
        $heatmapDays = $topHorarios->sortBy('dia_semana')->pluck('dia')->unique()->values()->all();

        $scoreValues = $topHorarios->pluck('engagement_score')->map(fn ($value) => (float) $value)->values();
        $scoreMin = $scoreValues->min() ?? 0;
        $scoreMax = $scoreValues->max() ?? 0;
        $scoreRange = max($scoreMax - $scoreMin, 1);
        $heatmapRows = [];

        foreach ($heatmapDays as $day) {
            $heatmapRows[$day] = [];
            foreach ($heatmapHours as $hour) {
                $match = $topHorarios->first(fn ($item) => $item['dia'] === $day && $item['hora'] === $hour);
                if ($match) {
                    $score = (float) $match['engagement_score'];
                    $heatmapRows[$day][] = [
                        'score' => round($score, 1),
                        'normalized' => ($score - $scoreMin) / $scoreRange,
                        'hasData' => true,
                        'esGenerado' => $match['es_generado'] ?? false,
                    ];
                } else {
                    $heatmapRows[$day][] = [
                        'score' => null,
                        'normalized' => null,
                        'hasData' => false,
                    ];
                }
            }
        }

        $topHorariosOrdenados = $topHorarios->sortByDesc('engagement_score')->values();
        $heatmapSummary = $topHorariosOrdenados->take(5)->map(fn ($item) => $item['dia'] . ' ' . $item['hora'])->implode(', ');
        $heatmapModel = $heatmapSource['modelo']['tipo'] ?? 'LSTM';

        $totalCampaigns = (int) $campaignMetrics->count();
        $totalReach = (int) $campaignMetrics->sum('reach');
        $totalInteractions = (int) $campaignMetrics->sum('interactions');
        $averageEngagement = round($campaignMetrics->avg('engagement'), 1);

        $recommendedCampaign = collect($campaignReach)->sortByDesc('engagement')->first();
        $dailyLabels = collect(range(1, count($dailyPerformance)))->map(fn ($day) => 'Día '.$day)->all();
        }
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .analytics-shell {
            background: #ffffff;
            min-height: 100vh;
        }

        .analytics-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }

        .analytics-kpi {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .analytics-kpi:hover {
            transform: translateY(-4px);
        }

        .analytics-kpi::after {
            content: '';
            position: absolute;
            inset: auto -20% -35% auto;
            width: 120px;
            height: 120px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .chart-box {
            position: relative;
            height: 320px;
        }

        .chart-box--small {
            height: 280px;
        }

        .heatmap-grid {
            display: grid;
            grid-template-columns: 130px repeat(var(--heatmap-columns, 1), minmax(78px, 1fr));
            gap: 0.65rem;
            align-items: center;
        }

        .heatmap-pill {
            border-radius: 1rem;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.83rem;
            font-weight: 700;
            color: #0f172a;
            border: 1px solid #dbeafe;
            flex-direction: column;
            gap: 0.15rem;
            padding: 0.45rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .heatmap-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.12);
        }

        .heatmap-pill--empty {
            background: #f8fafc;
            color: #94a3b8;
            border-style: dashed;
        }

        .heatmap-pill-score {
            font-size: 0.92rem;
            line-height: 1;
        }

        .heatmap-pill-meta {
            font-size: 0.66rem;
            font-weight: 600;
            line-height: 1;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .heatmap-top-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.9rem;
        }

        .heatmap-top-card {
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .heatmap-scale {
            background: linear-gradient(90deg, #dbeafe 0%, #93c5fd 35%, #38bdf8 65%, #0f766e 100%);
        }

        /* Banner geométrico */
        .rp-banner {
            background:
                linear-gradient(135deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(225deg, #4f46e5 25%, transparent 25%) -50px 0,
                linear-gradient(315deg, #4f46e5 25%, transparent 25%),
                linear-gradient(45deg,  #4f46e5 25%, transparent 25%),
                linear-gradient(to bottom, #3b82f6 0%, #2563eb 100%);
            background-size:
                100px 100px,
                100px 100px,
                100px 100px,
                100px 100px,
                100% 100%;
            background-color: #1d4ed8;
            position: relative;
        }

        .rp-banner-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 0%   0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%,   rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 0%   100%, rgba(255,255,255,0.2) 0%, transparent 50%);
            background-size:     50% 50%;
            background-position: 0 0, 100% 0, 100% 100%, 0 100%;
            background-repeat:   no-repeat;
        }

        @media (max-width: 1024px) {
            .heatmap-grid {
                grid-template-columns: 110px repeat(var(--heatmap-columns, 1), minmax(72px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .heatmap-grid {
                min-width: max-content;
            }
            .rp-banner .px-8 { 
                padding-left: 1.25rem; 
                padding-right: 1.25rem; 
            }
        }
    </style>

    <style>
        .analytics-shell{min-height:100vh;padding:0 0 54px;background:#fff;color:#30372e}.analytics-workspace{width:min(1180px,calc(100% - 48px));margin:0 auto;padding:0!important;max-width:none!important}.analytics-top-actions{display:flex;justify-content:flex-end;gap:8px;padding:18px 0 14px}.analytics-top-action{min-height:39px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 13px;border:1px solid #dfe4dc;border-radius:9px;background:#f8faf7;color:#687065;font-size:.61rem;font-weight:900;text-decoration:none;transition:.18s}.analytics-top-action:hover,.analytics-top-action.is-active{border-color:#117e8c;background:#e9f5f6;color:#117e8c}.analytics-top-action.is-active{box-shadow:inset 0 -2px 0 #117e8c}
        .analytics-hero{margin:0 0 22px!important;border-radius:16px!important;box-shadow:0 12px 28px rgba(37,99,235,.14)}.analytics-hero-body{padding:28px 31px!important}.analytics-hero-layout{display:flex;align-items:center;gap:18px}.analytics-hero-icon{width:55px;height:55px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.17);color:#fff;font-size:1.22rem}.analytics-hero-copy{min-width:0;flex:1}.analytics-hero-eyebrow{display:block;margin-bottom:4px;color:#bfdbfe;font-size:.55rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.analytics-hero h1{margin:0!important;color:#fff!important;font-size:clamp(1.45rem,3vw,2rem)!important;font-weight:900!important;letter-spacing:-.035em}.analytics-hero p{max-width:680px;margin:6px 0 0!important;color:#dbeafe!important;font-size:.68rem!important;line-height:1.55}.analytics-banner-meta{display:grid;grid-template-columns:repeat(2,minmax(130px,1fr));gap:7px;flex:0 0 auto}.analytics-banner-chip{padding:9px 11px!important;border:1px solid rgba(255,255,255,.25)!important;border-radius:10px!important;background:rgba(15,23,42,.14)!important;backdrop-filter:blur(4px)}.analytics-banner-chip span{display:block;color:#bfdbfe!important;font-size:.48rem!important;font-weight:900!important;letter-spacing:.1em!important;text-transform:uppercase}.analytics-banner-chip div{margin-top:2px;color:#fff!important;font-size:.65rem!important;font-weight:900!important;white-space:nowrap}
        .analytics-section-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin:0 1px 12px}.analytics-section-heading>div>span,.analytics-card-kicker{display:block;margin-bottom:3px;color:#117e8c;font-size:.51rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}.analytics-section-heading h2{margin:0;color:#302832;font-size:.92rem;font-weight:900}.analytics-section-heading h2:after{content:'';display:block;width:42px;height:3px;margin-top:6px;border-radius:999px;background:#117e8c}.analytics-section-heading p{margin:0;color:#8a9187;font-size:.57rem}
        .analytics-kpi-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important;margin-bottom:24px!important}.analytics-kpi{min-height:142px;padding:17px!important;border:1px solid #e1e5df!important;border-radius:13px!important;background:#fff!important;color:#30372e!important;box-shadow:0 5px 15px rgba(55,60,52,.05)!important}.analytics-kpi:hover{transform:translateY(-2px);border-color:#cdd6ca!important;box-shadow:0 10px 24px rgba(55,60,52,.09)!important}.analytics-kpi:before{content:'';position:absolute;top:0;right:0;left:0;height:3px;background:var(--kpi-color,#117e8c)}.analytics-kpi:after{right:-35px!important;bottom:-48px!important;width:115px!important;height:115px!important;background:color-mix(in srgb,var(--kpi-color,#117e8c) 8%,transparent)!important}.analytics-kpi:nth-child(1){--kpi-color:#117e8c}.analytics-kpi:nth-child(2){--kpi-color:#4f46e5}.analytics-kpi:nth-child(3){--kpi-color:#4f8a42}.analytics-kpi:nth-child(4){--kpi-color:#d68a14}.analytics-kpi>div{position:relative;z-index:2}.analytics-kpi p:first-child{margin:0;color:#7f887c!important;font-size:.52rem!important;font-weight:900!important;letter-spacing:.1em;text-transform:uppercase}.analytics-kpi h2{margin:8px 0 0!important;color:#30372e!important;font-size:1.65rem!important;font-weight:900!important;letter-spacing:-.04em}.analytics-kpi p:nth-child(3){max-width:190px;margin:7px 0 0!important;color:#8b9388!important;font-size:.54rem!important;line-height:1.45}.analytics-kpi>div>div:last-child{width:37px!important;height:37px!important;border-radius:10px!important;background:color-mix(in srgb,var(--kpi-color) 11%,white)!important}.analytics-kpi>div>div:last-child i{color:var(--kpi-color)!important;font-size:.85rem!important}
        .analytics-chart-grid{display:grid!important;grid-template-columns:repeat(12,minmax(0,1fr))!important;gap:13px!important;margin-bottom:13px!important}.analytics-card{border:1px solid #e1e5df!important;border-radius:13px!important;background:#fff!important;box-shadow:0 5px 16px rgba(55,60,52,.045)!important}.analytics-chart-grid>.analytics-card{padding:18px!important}.analytics-chart-grid>.analytics-card:first-child{grid-column:span 7}.analytics-chart-grid>.analytics-card:last-child{grid-column:span 5}.analytics-chart-grid--reverse>.analytics-card:first-child{grid-column:span 5}.analytics-chart-grid--reverse>.analytics-card:last-child{grid-column:span 7}.analytics-card-header{display:flex!important;align-items:flex-start!important;justify-content:space-between!important;gap:14px!important;margin-bottom:12px!important}.analytics-card-header h2{margin:0!important;color:#343a32!important;font-size:.78rem!important;font-weight:900!important}.analytics-card-header h2:after{content:'';display:block;width:34px;height:2px;margin-top:6px;border-radius:99px;background:#117e8c}.analytics-card-header p{margin:6px 0 0!important;color:#8a9187!important;font-size:.55rem!important}.analytics-card-badge{padding:5px 8px!important;border:1px solid #d6e9ea!important;border-radius:999px!important;background:#edf7f8!important;color:#117e8c!important;font-size:.48rem!important;font-weight:900!important;white-space:nowrap}.chart-box{height:285px!important;padding-top:4px}.chart-box--small{height:285px!important}
        .analytics-table-card,.analytics-heatmap,.analytics-recommendations{margin-top:13px!important;margin-bottom:0!important;padding:19px!important}.analytics-table-wrap{overflow:hidden;border:1px solid #e5e9e2;border-radius:10px}.analytics-table{width:100%;border-collapse:collapse}.analytics-table thead{background:#f6f8f5}.analytics-table thead tr{border:0!important}.analytics-table th{padding:11px 13px!important;color:#81897e!important;font-size:.49rem!important;font-weight:900!important;letter-spacing:.11em!important}.analytics-table td{padding:12px 13px!important;color:#596157!important;font-size:.59rem!important}.analytics-table tbody tr{border-top:1px solid #edf0eb}.analytics-table tbody tr:hover{background:#fafbf9}.analytics-client-cell{display:flex;align-items:center;gap:9px;color:#343a32!important;font-weight:900!important}.analytics-client-avatar{width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#e7f4f5;color:#117e8c;font-size:.57rem;font-weight:900}.analytics-engagement-pill{display:inline-flex;padding:4px 8px;border:1px solid #cde7dc;border-radius:999px;background:#edf8f2;color:#287453;font-size:.54rem;font-weight:900}
        .analytics-heatmap{padding:20px!important}.analytics-heatmap-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:17px}.analytics-heatmap-head h2{margin:0;color:#343a32;font-size:.81rem;font-weight:900}.analytics-heatmap-head h2:after{content:'';display:block;width:38px;height:2px;margin-top:6px;border-radius:99px;background:#117e8c}.analytics-heatmap-head p{margin:6px 0 0;color:#8a9187;font-size:.55rem}.heatmap-scale{height:7px!important;background:linear-gradient(90deg,#e5f3f4 0%,#9fd4d8 38%,#43a4ad 68%,#0d6873 100%)!important}.heatmap-top-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:8px!important;margin-bottom:16px!important}.heatmap-top-card{padding:11px!important;border-radius:9px!important;background:#fafbf9!important}.heatmap-top-card>p{color:#117e8c!important;font-size:.46rem!important}.heatmap-top-card .text-lg{font-size:.68rem!important}.heatmap-top-card .text-xl{font-size:.85rem!important}.heatmap-top-card .text-sm{font-size:.52rem!important}.heatmap-grid{grid-template-columns:105px repeat(var(--heatmap-columns,1),minmax(66px,1fr));gap:6px}.heatmap-pill{min-height:48px;border-radius:8px;font-size:.62rem}.heatmap-pill-score{font-size:.68rem}.heatmap-pill-meta{font-size:.43rem}.analytics-heatmap-notes{display:grid!important;grid-template-columns:1fr auto;gap:8px!important;margin-top:14px!important}.analytics-heatmap-note{padding:9px 11px!important;border-radius:8px!important;font-size:.53rem!important}
        .analytics-recommendations{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:12px!important;padding:0!important}.analytics-recommendation{position:relative;overflow:hidden;padding:18px!important}.analytics-recommendation:before{content:'';position:absolute;top:0;bottom:0;left:0;width:3px;background:#117e8c}.analytics-recommendation:last-child:before{background:#d68a14}.analytics-recommendation>div{margin-bottom:12px!important}.analytics-recommendation>div>div:first-child{width:38px;height:38px;display:flex;align-items:center;justify-content:center;padding:0!important;border-radius:10px!important}.analytics-recommendation h2{margin:0;color:#343a32!important;font-size:.74rem!important;font-weight:900!important}.analytics-recommendation h2:after{content:'';display:block;width:31px;height:2px;margin-top:5px;border-radius:99px;background:#117e8c}.analytics-recommendation:last-child h2:after{background:#d68a14}.analytics-recommendation p{margin:4px 0 0!important;color:#747d71!important;font-size:.57rem!important;line-height:1.6!important}
        .analytics-top-action{font-size:.68rem}.analytics-hero-eyebrow{font-size:.61rem}.analytics-hero p{font-size:.78rem!important}.analytics-banner-chip span{font-size:.54rem!important}.analytics-banner-chip div{font-size:.72rem!important}.analytics-section-heading>div>span{font-size:.57rem}.analytics-section-heading h2{font-size:1rem}.analytics-section-heading p{font-size:.65rem}.analytics-kpi p:first-child{font-size:.59rem!important}.analytics-kpi p:nth-child(3){font-size:.62rem!important}.analytics-card-header h2{font-size:.89rem!important}.analytics-card-header p{font-size:.63rem!important}.analytics-card-badge{font-size:.54rem!important}.analytics-table th{font-size:.54rem!important}.analytics-table td{font-size:.66rem!important}.analytics-heatmap-head h2{font-size:.9rem}.analytics-heatmap-head p{font-size:.63rem}.analytics-heatmap-note{font-size:.61rem!important}.analytics-recommendation h2{font-size:.84rem!important}.analytics-recommendation p{font-size:.65rem!important}
        /* Continuidad visual con el dashboard administrativo */
        .analytics-workspace{width:100%;max-width:none!important;padding:0!important}.analytics-hero{width:100%;min-height:180px;display:flex;align-items:center;margin:0!important;border:0!important;border-radius:0!important;box-shadow:none!important}.analytics-hero .rp-banner-overlay{background:linear-gradient(rgba(15,23,42,.22),rgba(15,23,42,.22)),radial-gradient(circle at 0 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 0,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 100% 100%,rgba(255,255,255,.2),transparent 50%),radial-gradient(circle at 0 100%,rgba(255,255,255,.2),transparent 50%);background-size:100% 100%,50% 50%,50% 50%,50% 50%,50% 50%;background-position:0 0,0 0,100% 0,100% 100%,0 100%;background-repeat:no-repeat}.analytics-hero-body{width:100%;padding:30px 48px!important}.analytics-hero-layout{display:flex;align-items:center;justify-content:space-between;gap:28px}.analytics-hero-copy{position:relative;z-index:1}.analytics-hero-eyebrow{margin-bottom:7px;color:#dbeafe;font-size:.68rem}.analytics-hero h1{font-size:clamp(1.55rem,3vw,2.25rem)!important}.analytics-hero p{max-width:700px;margin-top:8px!important;color:#e0e7ff!important;font-size:.84rem!important}.analytics-hero-actions{position:relative;z-index:1;display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.analytics-hero-action{min-height:41px;display:inline-flex;align-items:center;gap:8px;padding:11px 14px;border:1px solid rgba(255,255,255,.16);border-radius:.65rem;background:rgba(255,255,255,.12);color:#fff;font-size:.7rem;font-weight:900;text-decoration:none;white-space:nowrap;transition:.18s}.analytics-hero-action.is-primary{border-color:#fff;background:#fff;color:#4f46e5}.analytics-hero-action:not(.analytics-period):hover{transform:translateY(-2px);border-color:#fff;background:#fff;color:#4f46e5;box-shadow:0 8px 20px rgba(31,41,55,.16)}
        .analytics-workspace>.analytics-section-heading{margin:26px 24px 12px}.analytics-workspace>.analytics-kpi-grid,.analytics-workspace>.analytics-chart-grid,.analytics-workspace>.analytics-table-card,.analytics-workspace>.analytics-heatmap,.analytics-workspace>.analytics-recommendations{width:auto;margin-right:24px!important;margin-left:24px!important}.analytics-section-heading>div>span{color:#6366f1}.analytics-section-heading h2{color:#1f2937;font-size:1.08rem}.analytics-section-heading p{color:#6b7280;font-size:.7rem}
        .analytics-kpi-grid{gap:16px!important}.analytics-kpi{--accent:#117e8c;--soft:#e6f4f5;--accent-rgb:17,126,140;isolation:isolate;min-height:132px;display:flex;flex-direction:column;justify-content:space-between;padding:16px 17px!important;border:1px solid rgba(var(--accent-rgb),.22)!important;border-radius:1rem!important;background:linear-gradient(135deg,#fff 35%,var(--soft))!important;box-shadow:inset 0 4px 0 var(--accent),0 10px 24px rgba(45,66,34,.09)!important}.analytics-kpi:before{z-index:-1;top:-45px;right:-38px;left:auto;width:125px;height:125px;border:22px solid rgba(var(--accent-rgb),.09);border-radius:50%;background:transparent}.analytics-kpi:after{z-index:-1;right:12px!important;bottom:6px!important;width:86px!important;height:44px!important;border-radius:0!important;background-color:transparent!important;background-image:radial-gradient(circle,var(--accent) 1.4px,transparent 1.6px)!important;background-size:9px 9px!important;opacity:.2;transform:rotate(-5deg)}.analytics-kpi:hover{transform:translateY(-5px);border-color:rgba(var(--accent-rgb),.38)!important;box-shadow:inset 0 4px 0 var(--accent),0 17px 32px rgba(var(--accent-rgb),.16)!important}.analytics-kpi-campaigns{--accent:#117e8c;--soft:#e6f4f5;--accent-rgb:17,126,140}.analytics-kpi-reach{--accent:#7da533;--soft:#f0f6e7;--accent-rgb:125,165,51}.analytics-kpi-interactions{--accent:#e3a122;--soft:#fff6df;--accent-rgb:227,161,34}.analytics-kpi-engagement{--accent:#e37225;--soft:#fff0e6;--accent-rgb:227,114,37}.analytics-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.analytics-kpi-top span{min-width:0;overflow:hidden;color:#50594a;font-size:.63rem;font-weight:900;letter-spacing:.025em;text-overflow:ellipsis;text-transform:uppercase;white-space:nowrap}.analytics-kpi-top>i{width:26px;height:26px;display:grid;place-items:center;flex:0 0 auto;border:1px solid rgba(var(--accent-rgb),.2);border-radius:50%;background:#fff;color:var(--accent);font-size:.55rem;box-shadow:0 3px 8px rgba(var(--accent-rgb),.12);transform:rotate(45deg)}.analytics-kpi-body{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-top:13px}.analytics-kpi-copy{min-width:0}.analytics-kpi-copy strong{display:block;color:#263024;font-size:1.65rem;font-weight:900;line-height:1;letter-spacing:-.045em;white-space:nowrap}.analytics-kpi-copy small{display:block;max-width:150px;margin-top:6px;overflow:hidden;color:#7f8878;font-size:.58rem;font-weight:650;text-overflow:ellipsis;white-space:nowrap}.analytics-kpi .analytics-kpi-visual{width:auto!important;height:50px!important;min-width:64px;display:flex!important;align-items:flex-end!important;justify-content:center!important;gap:4px;padding:8px!important;border:1px solid rgba(255,255,255,.55)!important;border-radius:.8rem!important;background:var(--accent)!important;box-shadow:0 8px 17px rgba(var(--accent-rgb),.25),inset 0 1px 0 rgba(255,255,255,.28)!important}.analytics-kpi-visual i{width:5px;border-radius:4px 4px 1px 1px;background:#fff}.analytics-kpi-visual i:nth-child(1){height:25%}.analytics-kpi-visual i:nth-child(2){height:48%;opacity:.68}.analytics-kpi-visual i:nth-child(3){height:76%}.analytics-kpi-visual i:nth-child(4){height:56%;opacity:.7}.analytics-kpi-visual i:nth-child(5){height:92%}
        .analytics-card{border:1px solid #eee8f0!important;border-radius:1rem!important;background:linear-gradient(135deg,#fff 0%,#fbf8fc 58%,#f2fbfa 100%)!important;box-shadow:0 9px 22px rgba(61,23,79,.07)!important}.analytics-card-header h2,.analytics-heatmap-head h2,.analytics-recommendation h2{color:#302832!important;font-size:1rem!important}.analytics-card-header h2:after,.analytics-heatmap-head h2:after,.analytics-recommendation h2:after{width:44px;height:3px;background:#117e8c}.analytics-card-header p,.analytics-heatmap-head p,.analytics-recommendation p{color:#817786!important}.analytics-card-badge{background:#e4f3f4!important;color:#117e8c!important}.analytics-table-wrap{border-color:#eee8f0}.analytics-table thead{background:#f9fafb}.analytics-table th{color:#6b7280!important}.analytics-recommendation:last-child h2:after{background:#e37225}
        @media(max-width:1000px){.analytics-hero{min-height:205px}.analytics-hero-layout{justify-content:center;flex-direction:column;text-align:center}.analytics-hero-actions{justify-content:center}.analytics-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}.analytics-chart-grid>.analytics-card,.analytics-chart-grid--reverse>.analytics-card{grid-column:span 12!important}.heatmap-top-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.analytics-shell{padding-bottom:28px}.analytics-workspace{width:100%}.analytics-workspace>.analytics-section-heading{margin-right:12px;margin-left:12px}.analytics-workspace>.analytics-kpi-grid,.analytics-workspace>.analytics-chart-grid,.analytics-workspace>.analytics-table-card,.analytics-workspace>.analytics-heatmap,.analytics-workspace>.analytics-recommendations{margin-right:12px!important;margin-left:12px!important}.analytics-hero-body{padding:24px 20px!important}.analytics-hero-layout{align-items:center}.analytics-hero-actions{width:100%}.analytics-hero-action{flex:1;justify-content:center}.analytics-period{flex-basis:100%}.analytics-kpi-grid{grid-template-columns:1fr!important}.analytics-chart-grid{display:block!important}.analytics-chart-grid>.analytics-card{margin-bottom:12px}.analytics-card-header{flex-direction:column}.chart-box,.chart-box--small{height:255px!important}.analytics-table-card,.analytics-heatmap{padding:15px!important}.analytics-heatmap-head{align-items:flex-start;flex-direction:column}.heatmap-top-grid{grid-template-columns:1fr}.analytics-heatmap-notes{grid-template-columns:1fr!important}.analytics-recommendations{grid-template-columns:1fr!important}.analytics-section-heading{align-items:flex-start;flex-direction:column;gap:5px}}
    </style>

    <div class="analytics-shell">
        <div class="analytics-workspace max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <header class="analytics-hero mb-8 overflow-hidden relative rp-banner">
                <div class="rp-banner-overlay"></div>
                <div class="analytics-hero-body relative z-10 px-8 py-8">
                    <div class="analytics-hero-layout">
                        <div class="analytics-hero-copy">
                            <span class="analytics-hero-eyebrow">Dashboard de campañas</span>
                            <h1 class="text-3xl font-bold text-white mb-1">Analíticas de campañas</h1>
                            <p style="color: #bfdbfe; font-size: 0.9rem;">Vista consolidada de {{ $monthName }} con rendimiento, comparaciones estadísticas y recomendaciones basadas en evidencia.</p>
                        </div>
                        <nav class="analytics-hero-actions" aria-label="Acciones de analíticas">
                            <form method="GET" action="{{ route('administrador.campañas.analiticas') }}" class="analytics-hero-action analytics-period">
                                <i class="fas fa-calendar-days"></i>
                                <label for="analytics-days" class="sr-only">Periodo de análisis</label>
                                <select id="analytics-days" name="days" onchange="this.form.submit()" style="border:0;background:transparent;color:inherit;font:inherit;font-weight:900;outline:0;cursor:pointer;">
                                    <option value="7" @selected((string) $selectedDays === '7')>Últimos 7 días</option>
                                    <option value="30" @selected((string) $selectedDays === '30')>Últimos 30 días</option>
                                    <option value="90" @selected((string) $selectedDays === '90')>Últimos 90 días</option>
                                    <option value="365" @selected((string) $selectedDays === '365')>Último año</option>
                                    <option value="730" @selected((string) $selectedDays === '730')>Últimos 2 años</option>
                                    <option value="all" @selected((string) $selectedDays === 'all')>Todo el historial</option>
                                </select>
                            </form>
                            <a href="{{ route('administrador.campañas.index') }}" class="analytics-hero-action is-primary"><i class="fas fa-bullhorn"></i>Campañas</a>
                            <a href="{{ route('administrador.dashboard') }}" class="analytics-hero-action"><i class="fas fa-arrow-left"></i>Volver al panel</a>
                        </nav>
                    </div>
                </div>
            </header>

            <div style="margin:18px 24px 0;padding:12px 14px;border:1px solid {{ $usingFallback ? '#fde68a' : '#bbf7d0' }};border-radius:12px;background:{{ $usingFallback ? '#fffbeb' : '#f0fdf4' }};color:{{ $usingFallback ? '#92400e' : '#166534' }};font-size:.72rem;font-weight:700;">
                @if ($usingFallback)
                    <i class="fas fa-triangle-exclamation"></i>
                    Modo de respaldo demostrativo: ninguna campaña tiene datos de Meta disponibles. Se conservan temporalmente las cifras ficticias anteriores para mantener operativo el panel.
                @else
                    <i class="fas fa-circle-check"></i>
                    Datos reales consolidados desde Meta Insights: {{ $dashboard['connectedCampaigns'] }} campaña(s) con cuenta conectada y {{ $dashboard['campaignsWithData'] }} con actividad en el periodo.
                @endif
            </div>

            <!-- KPI Cards mejoradas -->
            <div class="analytics-section-heading">
                <div><span>Resumen ejecutivo</span><h2>Indicadores principales</h2></div>
                <p>Lectura consolidada de {{ $monthName }}</p>
            </div>
            <section class="analytics-kpi-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
                <article class="analytics-kpi analytics-kpi-campaigns">
                    <div class="analytics-kpi-top"><span>Total campañas</span><i class="fas fa-arrow-up"></i></div>
                    <div class="analytics-kpi-body"><div class="analytics-kpi-copy"><strong>{{ $totalCampaigns }}</strong><small>Activas, finalizadas y en preparación.</small></div><div class="analytics-kpi-visual" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div></div>
                </article>
                <article class="analytics-kpi analytics-kpi-reach">
                    <div class="analytics-kpi-top"><span>Alcance total</span><i class="fas fa-arrow-up"></i></div>
                    <div class="analytics-kpi-body"><div class="analytics-kpi-copy"><strong>{{ number_format($totalReach) }}</strong><small>Visibilidad acumulada del periodo.</small></div><div class="analytics-kpi-visual" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div></div>
                </article>
                <article class="analytics-kpi analytics-kpi-interactions">
                    <div class="analytics-kpi-top"><span>Interacciones</span><i class="fas fa-arrow-up"></i></div>
                    <div class="analytics-kpi-body"><div class="analytics-kpi-copy"><strong>{{ number_format($totalInteractions) }}</strong><small>Clics, reacciones y respuestas del mes.</small></div><div class="analytics-kpi-visual" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div></div>
                </article>
                <article class="analytics-kpi analytics-kpi-engagement">
                    <div class="analytics-kpi-top"><span>Engagement promedio</span><i class="fas fa-arrow-up"></i></div>
                    <div class="analytics-kpi-body"><div class="analytics-kpi-copy"><strong>{{ number_format($averageEngagement, 1) }}%</strong><small>Rendimiento promedio de los clientes.</small></div><div class="analytics-kpi-visual" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div></div>
                </article>
            </section>

            <div class="analytics-section-heading">
                <div><span>Comportamiento</span><h2>Evolución y estado operativo</h2></div>
                <p>Gráficos comparativos del periodo</p>
            </div>
            <section class="analytics-chart-grid grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
                <article class="analytics-card rounded-2xl p-6 xl:col-span-7 shadow-sm border border-gray-100">
                    <div class="analytics-card-header flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Rendimiento diario del mes</h2>
                            <p class="text-sm text-slate-500">Evolución de interacciones generales a lo largo del mes.</p>
                        </div>
                        <span class="analytics-card-badge rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 border border-blue-100">Tendencia diaria</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="dailyPerformanceChart"></canvas>
                    </div>
                </article>

                <article class="analytics-card rounded-2xl p-6 xl:col-span-5 shadow-sm border border-gray-100">
                    <div class="analytics-card-header flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Estado de campañas</h2>
                            <p class="text-sm text-slate-500">Distribución general del trabajo operativo del mes.</p>
                        </div>
                        <span class="analytics-card-badge rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 border border-cyan-100">Distribución</span>
                    </div>
                    <div class="chart-box chart-box--small">
                        <canvas id="campaignStatusChart"></canvas>
                    </div>
                </article>
            </section>

            <section class="analytics-chart-grid analytics-chart-grid--reverse grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
                <article class="analytics-card rounded-2xl p-6 xl:col-span-5 shadow-sm border border-gray-100">
                    <div class="analytics-card-header flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Campañas por usuario</h2>
                            <p class="text-sm text-slate-500">Comparación mensual de actividad entre clientes.</p>
                        </div>
                        <span class="analytics-card-badge rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-100">Carga por cliente</span>
                    </div>
                    <div class="chart-box chart-box--small">
                        <canvas id="campaignsByUserChart"></canvas>
                    </div>
                </article>

                <article class="analytics-card rounded-2xl p-6 xl:col-span-7 shadow-sm border border-gray-100">
                    <div class="analytics-card-header flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Alcance por campaña</h2>
                            <p class="text-sm text-slate-500">Campañas con mayor visibilidad total dentro del periodo.</p>
                        </div>
                        <span class="analytics-card-badge rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">Comparativo</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="reachByCampaignChart"></canvas>
                    </div>
                </article>
            </section>

            <section class="analytics-table-card analytics-card rounded-2xl p-6 mb-8 shadow-sm border border-gray-100">
                <div class="analytics-card-header flex flex-col gap-2 mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Rendimiento por usuario / cliente</h2>
                    <p class="text-sm text-slate-500">Detalle comparativo de carga operativa, alcance e interacción mensual.</p>
                </div>

                <div class="analytics-table-wrap overflow-x-auto">
                    <table class="analytics-table min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500 uppercase tracking-[0.14em] text-xs">
                                <th class="px-4 py-3 font-semibold">Usuario</th>
                                <th class="px-4 py-3 font-semibold">Campañas</th>
                                <th class="px-4 py-3 font-semibold">Alcance</th>
                                <th class="px-4 py-3 font-semibold">Interacciones</th>
                                <th class="px-4 py-3 font-semibold">Engagement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($campaignsByUser as $row)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="analytics-client-cell px-4 py-4 font-semibold text-slate-900"><span class="analytics-client-avatar">{{ mb_strtoupper(mb_substr($row['user'], 0, 1)) }}</span>{{ $row['user'] }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $row['campaigns'] }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($row['reach']) }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ number_format($row['interactions']) }}</td>
                                    <td class="px-4 py-4">
                                        <span class="analytics-engagement-pill inline-flex rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-700 border border-emerald-100">
                                            {{ number_format($row['engagement'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="analytics-heatmap analytics-card rounded-2xl p-6 mb-8 shadow-sm border border-gray-100">
                <div class="analytics-heatmap-head flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Mapa de calor: horarios con mayor interacción</h2>
                        <p class="text-sm text-slate-500 mt-1">Distribución de engagement por día y hora</p>
                    </div>
                    <div class="w-full lg:w-64">
                        <div class="heatmap-scale rounded-full h-3"></div>
                        <div class="mt-2 flex justify-between text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                            <span>Bajo</span>
                            <span>Medio</span>
                            <span>Alto</span>
                        </div>
                    </div>
                </div>

                @if ($topHorarios->isNotEmpty())
                    <div class="heatmap-top-grid mb-6">
                        @foreach ($topHorarios->sortByDesc('engagement_score')->take(4) as $slot)
                            <div class="heatmap-top-card rounded-2xl p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-sky-700">Top horario</p>
                                <div class="mt-2 flex items-end justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-bold text-slate-900">{{ $slot['dia'] }}</p>
                                        <p class="text-sm text-slate-500">{{ $slot['hora'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black text-cyan-700">{{ number_format($slot['engagement_score'], 1) }}</p>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">score</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="overflow-x-auto pb-2">
                    <div class="heatmap-grid" style="--heatmap-columns: {{ max(count($heatmapHours), 1) }};">
                        <div></div>
                        @foreach ($heatmapHours as $hour)
                            <div class="text-center text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $hour }}</div>
                        @endforeach

                        @foreach ($heatmapRows as $day => $values)
                            <div class="pr-3 text-sm font-bold text-slate-700">{{ $day }}</div>
                            @foreach ($values as $cell)
                                @if ($cell['hasData'])
                                    @php
                                        $opacity = 0.22 + ($cell['normalized'] * 0.78);
                                        $background = 'background-color: rgba(17, 126, 140, ' . $opacity . ');';
                                    @endphp
                                    <div class="heatmap-pill" style="{{ $background }}" title="Score {{ $cell['score'] }}">
                                        <span class="heatmap-pill-score">{{ $cell['score'] }}</span>
                                        <span class="heatmap-pill-meta">score</span>
                                    </div>
                                @else
                                    <div class="heatmap-pill heatmap-pill--empty" title="Sin dato en el JSON">
                                        <span class="heatmap-pill-score">-</span>
                                        <span class="heatmap-pill-meta">sin dato</span>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="analytics-heatmap-notes mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="analytics-heatmap-note rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600 border border-slate-200">
                        <span class="font-semibold text-slate-800">Picos detectados:</span>
                        {{ $heatmapSummary ?: 'Sin resumen disponible.' }}
                    </div>
                    <div class="analytics-heatmap-note rounded-2xl bg-cyan-50 px-4 py-3 text-sm text-cyan-800 border border-cyan-100">
                        <span class="font-semibold">Modelo:</span> {{ $heatmapModel }}
                        @if ($topHorarios->isNotEmpty())
                            <span class="mx-2 text-cyan-300">|</span>
                            <span class="font-semibold">Rango score:</span> {{ number_format($scoreMin, 1) }} - {{ number_format($scoreMax, 1) }}
                        @endif
                    </div>
                </div>

                @if ($topHorarios->isEmpty())
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        No se encontraron horarios utilizables en el archivo JSON.
                    </div>
                @endif
            </section>

            <div class="analytics-section-heading" style="margin-top: 14px;">
                <div><span>Decisiones sugeridas</span><h2>Recomendaciones del periodo</h2></div>
                <p>Hallazgos construidos con los datos actuales</p>
            </div>
            <section class="analytics-recommendations grid grid-cols-1 xl:grid-cols-2 gap-6">
                <article class="analytics-recommendation analytics-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-2xl bg-cyan-100 p-3">
                            <svg class="w-6 h-6 text-cyan-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Recomendación automática del mes</h2>
                            <p class="text-sm text-slate-500">Sugerencia estadística con al menos dos publicaciones por franja.</p>
                        </div>
                    </div>
                    <p id="automaticRecommendation" class="text-base leading-7 text-slate-700"></p>
                </article>

                <article class="analytics-recommendation analytics-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="rounded-2xl bg-amber-100 p-3">
                            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.19 3.674a1 1 0 00.95.69h3.864c.969 0 1.371 1.24.588 1.81l-3.126 2.272a1 1 0 00-.364 1.118l1.194 3.674c.3.922-.755 1.688-1.539 1.118l-3.127-2.272a1 1 0 00-1.175 0l-3.127 2.272c-.783.57-1.838-.196-1.539-1.118l1.194-3.674a1 1 0 00-.364-1.118L2.397 9.101c-.783-.57-.38-1.81.588-1.81h3.864a1 1 0 00.95-.69l1.19-3.674z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Campaña recomendada para análisis</h2>
                            <p class="text-sm text-slate-500">La pieza con mejor engagement del periodo.</p>
                        </div>
                    </div>
                    @if ($recommendedCampaign)
                        <p class="text-base leading-7 text-slate-700">
                            <span class="font-bold text-slate-900">"{{ $recommendedCampaign['campaign'] }}"</span>
                            obtuvo el mayor engagement con
                            <span class="font-bold text-emerald-700">{{ number_format($recommendedCampaign['engagement'], 1) }}%</span>.
                        </p>
                    @else
                        <p class="text-base leading-7 text-slate-700">Todavía no existen campañas con alcance suficiente para realizar esta comparación.</p>
                    @endif
                </article>
            </section>
        </div>
    </div>

    <script>
        const dashboardData = {
            campaignsByUser: @json($campaignsByUser),
            dailyLabels: @json($dailyLabels),
            dailyPerformance: @json($dailyPerformance),
            campaignReach: @json($campaignReach),
            statusDistribution: @json($statusDistribution),
            heatmapHours: @json($heatmapHours),
            topHorarios: @json($topHorarios->values()),
            picos: @json($heatmapSummary),
        };

        const chartPalette = {
            primary: '#0f766e',
            secondary: '#0284c7',
            accent: '#2563eb',
            soft: '#7dd3fc',
            warm: '#f59e0b',
            dark: '#0f172a',
        };

        function destroyExistingChart(id) {
            const chart = Chart.getChart(id);
            if (chart) {
                chart.destroy();
            }
        }

        function buildAutomaticRecommendation() {
            const recommendationNode = document.getElementById('automaticRecommendation');
            const topHorarios = dashboardData.topHorarios || [];

            if (!recommendationNode) {
                return;
            }

            if (!topHorarios.length) {
                recommendationNode.textContent =
                    'No hay al menos dos publicaciones en una misma franja horaria. Se necesitan más datos antes de recomendar un horario.';
                return;
            }

            const sortedTopHorarios = [...topHorarios].sort((a, b) => Number(b.engagement_score || 0) - Number(a.engagement_score || 0));
            const bestSlot = sortedTopHorarios[0];
            const highlightedSlots = sortedTopHorarios.slice(0, 5);
            const averageScore = highlightedSlots.reduce((sum, item) => sum + Number(item.engagement_score || 0), 0) / highlightedSlots.length;
            const peakSummary = dashboardData.picos ? ` Los picos reportados son: ${dashboardData.picos}.` : ''; 

            recommendationNode.textContent =
                `El mejor horario disponible es ${bestSlot.dia} a las ${bestSlot.hora}, con un score de ${Number(bestSlot.engagement_score).toFixed(1)}. El promedio de los horarios destacados es ${averageScore.toFixed(1)}.${peakSummary}`;
        }

        function initMonthlyCharts() {
            Chart.defaults.color = '#747d71';
            Chart.defaults.font.family = "Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
            Chart.defaults.font.size = 10;

            destroyExistingChart('dailyPerformanceChart');
            destroyExistingChart('campaignStatusChart');
            destroyExistingChart('campaignsByUserChart');
            destroyExistingChart('reachByCampaignChart');

            new Chart(document.getElementById('dailyPerformanceChart'), {
                type: 'line',
                data: {
                    labels: dashboardData.dailyLabels,
                    datasets: [{
                        label: 'Interacciones',
                        data: dashboardData.dailyPerformance,
                        borderColor: chartPalette.primary,
                        backgroundColor: 'rgba(17, 126, 140, 0.10)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: chartPalette.primary,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            },
                            ticks: {
                                color: '#64748b'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#64748b',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10,
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('campaignStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(dashboardData.statusDistribution),
                    datasets: [{
                        data: Object.values(dashboardData.statusDistribution),
                        backgroundColor: ['#117e8c', '#8dc8cd', '#4f46e5', '#4f8a42', '#d68a14'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 18,
                                color: '#334155'
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('campaignsByUserChart'), {
                type: 'bar',
                data: {
                    labels: dashboardData.campaignsByUser.map((item) => item.user),
                    datasets: [{
                        label: 'Campañas',
                        data: dashboardData.campaignsByUser.map((item) => item.campaigns),
                        backgroundColor: ['#d9edef', '#b8dde0', '#7fbfc5', '#43a4ad', '#117e8c'],
                        borderRadius: 7,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#334155'
                            }
                        },
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: '#64748b'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('reachByCampaignChart'), {
                type: 'bar',
                data: {
                    labels: dashboardData.campaignReach.map((item) => item.campaign),
                    datasets: [{
                        label: 'Alcance total',
                        data: dashboardData.campaignReach.map((item) => item.reach),
                        backgroundColor: '#117e8c',
                        borderRadius: 7,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Alcance: ${context.raw.toLocaleString()}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#64748b',
                                callback: (value) => value.toLocaleString()
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#334155'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMonthlyCharts();
            buildAutomaticRecommendation();
        });
    </script>
@endsection
