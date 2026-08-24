<?php

namespace App\Exports;

use App\Models\Pago;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PagosExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $pagos;
    protected $filters;
    protected $summary;
    protected $reportTitle;

    public function __construct($pagos, $filters = [], $summary = [], $reportTitle = 'Reporte de pagos')
    {
        $this->pagos = $pagos;
        $this->filters = $filters;
        $this->summary = $summary;
        $this->reportTitle = $reportTitle;
    }

    public function view(): View
    {
        return view('exports.pagos', [
            'pagos' => $this->pagos,
            'filters' => $this->filters,
            'summary' => $this->summary,
            'reportTitle' => $this->reportTitle,
            'generatedAt' => now(),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(8, 7 + $this->pagos->count());
        $sheet->freezePane('A8');
        $sheet->setAutoFilter("A7:M{$lastRow}");
        $sheet->setShowGridlines(false);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '343A40']]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
            ],
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
            ],
        ];
    }
}
