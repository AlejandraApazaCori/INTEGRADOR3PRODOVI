<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\MetaCampaignAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $data = $this->loadAnalyticsData('last30days');
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

        return view('clientes.analiticas', compact('data', 'campaniaActual', 'empresas'));
    }

    public function companyData(Request $request, Empresa $empresa, MetaCampaignAnalyticsService $analyticsService)
    {
        abort_unless($empresa->usuario_id === Auth::id(), 404);

        $validated = $request->validate([
            'days' => 'nullable|integer|in:7,30,90',
        ]);

        return response()->json(
            $analyticsService->forCompany($empresa, (int) ($validated['days'] ?? 30))
        );
    }

    public function loadView(Request $request, MetaCampaignAnalyticsService $analyticsService)
    {
        if ($request->boolean('meta')) {
            $validated = $request->validate([
                'empresa_id' => 'required|integer',
                'days' => 'nullable|integer|in:7,30,90',
            ]);
            $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($validated['empresa_id']);

            return response()->json(
                $analyticsService->forCompany($empresa, (int) ($validated['days'] ?? 30))
            );
        }

        $periodKey = $this->resolvePeriodKey($request->input('view', '30dias'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);

        return view('clientes.analiticas.partials.analiticas', compact('data'));
    }

    public function exportarPDF(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('periodo', '30dias'));
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

        return $pdf->download('informe_analiticas_'.$request->input('periodo', '30dias').'.pdf');
    }

    public function exportarReporteEngagement(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('view', '30dias'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_engagement', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_engagement_'.$request->input('view', '30dias').'.pdf');
    }

    public function exportarReporteAlcance(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('view', '30dias'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_alcance', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_alcance_'.$request->input('view', '30dias').'.pdf');
    }

    public function exportarReporteSeguidores(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('view', '30dias'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_seguidores', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('informe_seguidores_'.$request->input('view', '30dias').'.pdf');
    }

    public function exportarReporteCTR(Request $request)
    {
        $periodKey = $this->resolvePeriodKey($request->input('view', '30dias'));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $data = $this->loadAnalyticsData($periodKey, $userId);
        $pdfData = ['fecha_generacion' => now()->format('d/m/Y H:i'), 'data' => $data];

        $pdf = Pdf::loadView('pdf.reporte_ctr', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download('reporte_ctr_plataforma.pdf');
    }

    private function resolvePeriodKey(string $view): string
    {
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear',
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
