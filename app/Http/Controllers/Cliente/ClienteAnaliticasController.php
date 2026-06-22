<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ClienteAnaliticasController extends Controller
{
    public function index()
    {
        $campaniaActual = Auth::user()
            ->campaniasCliente()
            ->whereIn('estado', ['activa', 'pausada'])
            ->latest('fecha_inicio')
            ->first();

        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData['last30days'] ?? [];
        } else {
            $data = [];
        }

        return view('clientes.analiticas', compact('data', 'campaniaActual'));
    }

    public function loadView(Request $request)
    {
        $view = $request->input('view');
        
        // Mapeo del parámetro select a las llaves del JSON
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear'
        ];
        
        $periodKey = $periodMap[$view] ?? 'last30days';
        
        // Cargar los datos desde el archivo JSON
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData[$periodKey] ?? [];
        } else {
            $data = []; 
        }
        
        return view("clientes.analiticas.partials.analiticas", compact('data'));
    }

    public function exportarPDF(Request $request)
    {
        $periodo = $request->input('periodo', '30dias');

       
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear' 
        ];
        
        $periodKey = $periodMap[$periodo] ?? 'last30days';
        
        // Cargar los datos desde el archivo JSON
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $jsonData = $allData[$periodKey] ?? [];
        } else {
            $jsonData = []; // Fallback por si no existe
        }

        $data = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $jsonData,
        ];

        $pdf = Pdf::loadView('pdf.analiticasEmpresa', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $nombreArchivo = "informe_analiticas_{$periodo}.pdf";

        return $pdf->download($nombreArchivo);
    }


    public function exportarReporteEngagement(Request $request)
    {
        $view = $request->input('view', 'last30days');
        
        // Mapeo del parámetro select a las llaves del JSON
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear'
        ];
        
        $periodKey = $periodMap[$view] ?? 'last30days';
        
        // Cargar los datos desde el archivo JSON
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData[$periodKey] ?? [];
        } else {
            $data = []; // Fallback por si no existe
        }
        
        $pdfData = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $data
        ];

        $pdf = Pdf::loadView('pdf.reporte_engagement', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download("informe_engagement_{$view}.pdf");
    }

    public function exportarReporteAlcance(Request $request)
    {
        $view = $request->input('view', 'last30days');
        
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear'
        ];
        
        $periodKey = $periodMap[$view] ?? 'last30days';
        
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData[$periodKey] ?? [];
        } else {
            $data = [];
        }
        
        $pdfData = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $data
        ];

        $pdf = Pdf::loadView('pdf.reporte_alcance', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download("informe_alcance_{$view}.pdf");
    }

    public function exportarReporteSeguidores(Request $request)
    {
        $view = $request->input('view', 'last30days');
        
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear'
        ];
        
        $periodKey = $periodMap[$view] ?? 'last30days';
        
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData[$periodKey] ?? [];
        } else {
            $data = [];
        }
        
        $pdfData = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $data
        ];

        $pdf = Pdf::loadView('pdf.reporte_seguidores', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        
        return $pdf->download("informe_seguidores_{$view}.pdf");
    }
    
    public function exportarReporteCTR(Request $request)
    {
        $view = $request->input('view', 'last30days');
        
        $periodMap = [
            '7dias' => 'last7days',
            '30dias' => 'last30days',
            'anual' => 'thisyear'
        ];
        
        $periodKey = $periodMap[$view] ?? 'last30days';
        
        $jsonPath = resource_path('data/analiticas.json');
        if (file_exists($jsonPath)) {
            $jsonString = file_get_contents($jsonPath);
            $allData = json_decode($jsonString, true);
            $data = $allData[$periodKey] ?? [];
        } else {
            $data = [];
        }
        
        $pdfData = [
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'data' => $data
        ];

        $pdf = Pdf::loadView('pdf.reporte_ctr', $pdfData);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download("reporte_ctr_plataforma.pdf");
    }
}
