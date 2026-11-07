<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\AuditLog;
use App\Models\SecurityLog;
use App\Models\Campania;
use App\Models\CampaignActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdministrator();
        $request->validate([
            'campaign_status' => ['nullable', 'in:all,active,inactive'],
            'campaign_id' => ['nullable', 'integer', 'min:1'],
            'client_id' => ['nullable', 'integer', 'min:1'],
            'event_type' => ['nullable', 'in:all,campaign_created,campaign_updated,campaign_deleted,campaign_published,campaign_team_changed,task_created,task_updated,task_deleted,task_status_changed,task_published,task_assignees_changed'],
        ]);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $queryAccess = AccessLog::with('user')
            ->where('url', 'not like', '%/administrador/notificaciones/conteo%')
            ->orderBy('created_at', 'desc');
        if ($fechaInicio) {
            $queryAccess->whereDate('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $queryAccess->whereDate('created_at', '<=', $fechaFin);
        }
        $accessLogs = $queryAccess->paginate(15, ['*'], 'access_page');

        $allErrorLogs = collect($this->getErrorLogs($fechaInicio, $fechaFin));
        $perPage = 15;
        $currentPage = Paginator::resolveCurrentPage('error_page');
        $errorLogs = new LengthAwarePaginator(
            $allErrorLogs->forPage($currentPage, $perPage),
            $allErrorLogs->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'error_page']
        );

        $querySecurity = SecurityLog::with('user')->orderBy('created_at', 'desc');
        if ($fechaInicio) {
            $querySecurity->whereDate('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $querySecurity->whereDate('created_at', '<=', $fechaFin);
        }
        $securityLogs = $querySecurity->paginate(15, ['*'], 'security_page');

        $queryAudit = AuditLog::with('user')->orderBy('created_at', 'desc');
        if ($fechaInicio) {
            $queryAudit->whereDate('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $queryAudit->whereDate('created_at', '<=', $fechaFin);
        }
        $auditLogs = $queryAudit->paginate(15, ['*'], 'audit_page');

        $queryCampaign = CampaignActivityLog::with('campaign')->latest('created_at')->latest('id');
        if ($fechaInicio) $queryCampaign->whereDate('created_at', '>=', $fechaInicio);
        if ($fechaFin) $queryCampaign->whereDate('created_at', '<=', $fechaFin);
        if ($request->filled('campaign_id')) $queryCampaign->where('campaign_id', $request->integer('campaign_id'));
        if ($request->filled('client_id')) $queryCampaign->where('client_id', $request->integer('client_id'));
        if ($request->filled('event_type') && $request->input('event_type') !== 'all') {
            $queryCampaign->where('action', $request->input('event_type'));
        }
        $activeCampaigns = Campania::query()->select('id')->where('estado', 'activa')->where('es_borrador', false);
        if ($request->input('campaign_status') === 'active') {
            $queryCampaign->whereIn('campaign_id', $activeCampaigns);
        } elseif ($request->input('campaign_status') === 'inactive') {
            $queryCampaign->whereNotIn('campaign_id', $activeCampaigns);
        }
        $campaignLogs = $queryCampaign->paginate(15, ['*'], 'campaign_page')->withQueryString();
        $latestCampaignEvents = DB::table('campaign_activity_logs')->selectRaw('campaign_id, MAX(id) as latest_id')->groupBy('campaign_id');
        $campaignOptions = DB::table('campaign_activity_logs as events')
            ->joinSub($latestCampaignEvents, 'latest', 'events.id', '=', 'latest.latest_id')
            ->select('events.campaign_id', 'events.campaign_name', 'events.client_id')
            ->orderBy('events.campaign_name')->get();
        $latestClientEvents = DB::table('campaign_activity_logs')->selectRaw('client_id, MAX(id) as latest_id')
            ->whereNotNull('client_id')->groupBy('client_id');
        $clientOptions = DB::table('campaign_activity_logs as events')
            ->joinSub($latestClientEvents, 'latest', 'events.id', '=', 'latest.latest_id')
            ->select('events.client_id', 'events.client_name')->orderBy('events.client_name')->get();

        return view('administrador.logs.index', compact(
            'accessLogs',
            'errorLogs',
            'securityLogs',
            'auditLogs',
            'campaignLogs',
            'campaignOptions',
            'clientOptions',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function exportPdf($type, Request $request)
    {
        $this->ensureAdministrator();
        abort_unless(in_array($type, ['access', 'security', 'audit', 'error'], true), 404);
        $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'page_from' => ['nullable', 'integer', 'min:1'],
            'page_to' => ['nullable', 'integer', 'min:1'],
        ]);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $rangeRequested = $request->filled('page_from') || $request->filled('page_to');
        $pageFrom = max(1, (int) $request->input('page_from', 1));
        $pageTo = $request->filled('page_to') ? max($pageFrom, (int) $request->input('page_to')) : null;
        $offset = ($pageFrom - 1) * 15;
        $limit = $pageTo ? ($pageTo - $pageFrom + 1) * 15 : null;
        $applyQueryRange = function ($query) use ($rangeRequested, $offset, $limit) {
            if ($rangeRequested) {
                $query->skip($offset);
                if ($limit !== null) $query->take($limit);
            }
            return $query;
        };
        $data = [];
        $view = '';
        $title = '';
        $chartBase64 = null;

        switch ($type) {
            case 'access':
                $query = AccessLog::with('user')->where('url', 'not like', '%/administrador/notificaciones/conteo%')->orderBy('created_at', 'desc');
                if ($fechaInicio) {
                    $query->whereDate('created_at', '>=', $fechaInicio);
                }
                if ($fechaFin) {
                    $query->whereDate('created_at', '<=', $fechaFin);
                }
                $data = $applyQueryRange($query)->get();
                $view = 'administrador.logs.access_pdf';
                $title = 'Reporte de Logs de Acceso';

                $chartLabels = [];
                $chartValues = [];
                $statusCounts = $data->groupBy('status_code')->map->count();
                foreach ($statusCounts as $status => $count) {
                    $chartLabels[] = 'Status ' . ($status ?: 'N/A');
                    $chartValues[] = $count;
                }
                $chartBase64 = $this->generateChartBase64($chartLabels, $chartValues, 'Distribucion de Codigos de Estado');
                break;

            case 'security':
                $query = SecurityLog::with('user')->orderBy('created_at', 'desc');
                if ($fechaInicio) {
                    $query->whereDate('created_at', '>=', $fechaInicio);
                }
                if ($fechaFin) {
                    $query->whereDate('created_at', '<=', $fechaFin);
                }
                $data = $applyQueryRange($query)->get();
                $view = 'administrador.logs.security_pdf';
                $title = 'Reporte de Logs de Seguridad';

                $chartLabels = [];
                $chartValues = [];
                $eventCounts = $data->groupBy('event_type')->map->count();
                $eventLabelsMapping = [
                    'login_success' => 'Logueo Exitoso',
                    'login_failed' => 'Logueo Fallido',
                ];
                foreach ($eventCounts as $event => $count) {
                    $chartLabels[] = $eventLabelsMapping[$event] ?? strtoupper(str_replace('_', ' ', $event));
                    $chartValues[] = $count;
                }
                $chartBase64 = $this->generateChartBase64($chartLabels, $chartValues, 'Distribucion de Eventos de Seguridad');
                break;

            case 'audit':
                $query = AuditLog::with('user')->orderBy('created_at', 'desc');
                if ($fechaInicio) {
                    $query->whereDate('created_at', '>=', $fechaInicio);
                }
                if ($fechaFin) {
                    $query->whereDate('created_at', '<=', $fechaFin);
                }
                $data = $applyQueryRange($query)->get();
                $view = 'administrador.logs.audit_pdf';
                $title = 'Reporte de Logs de Actividad';

                $chartLabels = [];
                $chartValues = [];
                $actionCounts = $data->groupBy('action')->map->count();
                foreach ($actionCounts as $action => $count) {
                    $chartLabels[] = strtoupper($action);
                    $chartValues[] = $count;
                }
                $chartBase64 = $this->generateChartBase64($chartLabels, $chartValues, 'Distribucion de Acciones');
                break;

            case 'error':
                $data = collect($this->getErrorLogs($fechaInicio, $fechaFin));
                if ($rangeRequested) $data = $data->slice($offset, $limit)->values();
                $view = 'administrador.logs.error_pdf';
                $title = 'Reporte de Logs de Errores';

                $chartLabels = [];
                $chartValues = [];
                $typeCounts = $data->groupBy('type')->map->count();
                foreach ($typeCounts as $errorType => $count) {
                    $chartLabels[] = $errorType;
                    $chartValues[] = $count;
                }
                $chartBase64 = $this->generateChartBase64($chartLabels, $chartValues, 'Tipos de Eventos Recientes');
                break;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
            'logs' => $data,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'title' => $title,
            'chartBase64' => $chartBase64,
            'page_from' => $rangeRequested ? $pageFrom : null,
            'page_to' => $rangeRequested ? $pageTo : null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('logs_' . $type . '_' . now()->format('Ymd_His') . '.pdf');
    }

    private function getErrorLogs($fechaInicio = null, $fechaFin = null)
    {
        $logPath = storage_path('logs/laravel.log');
        $errorLogs = [];

        if (! File::exists($logPath)) {
            return $errorLogs;
        }

        $logFile = file($logPath);
        $logFile = array_reverse($logFile);

        $pattern = '/^\[(.*?)\] (.*?): (.*?) in (.*?):([0-9]+)/';
        $simplePattern = '/^\[(.*?)\] (.*?): (.*)/';

        foreach ($logFile as $line) {
            if (! preg_match('/^\[([0-9]{4}-[0-9]{2}-[0-9]{2}) [0-9]{2}:[0-9]{2}:[0-9]{2}\]/', $line, $dateMatches)) {
                continue;
            }

            $logDate = $dateMatches[1];

            if ($fechaInicio && $logDate < $fechaInicio) {
                continue;
            }
            if ($fechaFin && $logDate > $fechaFin) {
                continue;
            }

            if (preg_match($pattern, $line, $matches)) {
                $errorLogs[] = [
                    'datetime' => $matches[1],
                    'type' => $matches[2],
                    'message' => $matches[3],
                    'file' => $matches[4],
                    'line' => $matches[5],
                    'is_fatal' => str_contains(strtolower($matches[2]), 'error') || str_contains(strtolower($matches[2]), 'exception'),
                ];
            } elseif (preg_match($simplePattern, $line, $matches)) {
                $errorLogs[] = [
                    'datetime' => $matches[1],
                    'type' => $matches[2],
                    'message' => $matches[3],
                    'file' => 'N/A',
                    'line' => 'N/A',
                    'is_fatal' => str_contains(strtolower($matches[2]), 'error') || str_contains(strtolower($matches[2]), 'exception'),
                ];
            }

            if (count($errorLogs) >= 1000) {
                break;
            }
        }

        return $errorLogs;
    }

    private function ensureAdministrator(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Administrador', 'Administrador']), 403);
    }

    private function generateChartBase64($labels, $dataVals, $chartTitle)
    {
        if (empty($labels) || empty($dataVals)) {
            return null;
        }

        $chartConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $dataVals,
                    ],
                ],
            ],
            'options' => [
                'title' => [
                    'display' => true,
                    'text' => $chartTitle,
                ],
            ],
        ];

        $encodedConfig = urlencode(json_encode($chartConfig));
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $imageUrl = "https://quickchart.io/chart?c={$encodedConfig}&w=500&h=300";
        $imageData = @file_get_contents($imageUrl, false, $context);

        if ($imageData) {
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        return null;
    }
}
