<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PagosChartReportExport implements FromView, ShouldAutoSize, WithCharts, WithStyles, WithTitle
{
    private array $statusStats;

    private array $methodStats;

    private int $tableHeaderRow = 20;

    public function __construct(
        private readonly mixed $pagos,
        private readonly array $filters,
        private readonly array $summary,
        private readonly string $reportTitle,
    ) {
        $this->statusStats = $this->buildStats(fn ($pago) => match ($pago->estado) {
            'completado' => 'Completado',
            'pendiente' => 'Pendiente',
            'rechazado' => 'Rechazado',
            'cancelado' => 'Cancelado',
            default => ucfirst((string) ($pago->estado ?: 'Sin definir')),
        });
        $this->methodStats = $this->buildStats(fn ($pago) => match (strtolower((string) $pago->metodo)) {
            'qr' => 'QR',
            'fisico' => 'Físico',
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            default => ucfirst((string) ($pago->metodo ?: 'Sin definir')),
        });
    }

    public function view(): View
    {
        return view('exports.pagos-con-graficos', [
            'pagos' => $this->pagos,
            'filters' => $this->filters,
            'summary' => $this->summary,
            'reportTitle' => $this->reportTitle,
            'generatedAt' => now(),
            'statusStats' => $this->statusStats,
            'methodStats' => $this->methodStats,
        ]);
    }

    public function title(): string
    {
        return 'Pagos';
    }

    public function statusStats(): array
    {
        return $this->statusStats;
    }

    public function methodStats(): array
    {
        return $this->methodStats;
    }

    public function charts(): array
    {
        if ($this->pagos->isEmpty()) {
            return [];
        }

        return [
            $this->doughnutChart('payment_status_chart', 'Distribución por estado del pago', $this->statusStats, 'N', 'O', 'A4', 'F18'),
            $this->doughnutChart('payment_method_chart', 'Distribución por método de pago', $this->methodStats, 'R', 'S', 'G4', 'M18'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $firstDataRow = $this->tableHeaderRow + 1;
        $lastDataRow = max($firstDataRow, $this->tableHeaderRow + $this->pagos->count());
        $statusLastRow = 3 + max(count($this->statusStats), 1);
        $methodLastRow = 3 + max(count($this->methodStats), 1);

        $sheet->freezePane("A{$firstDataRow}");
        $sheet->setAutoFilter("A{$this->tableHeaderRow}:M{$lastDataRow}");
        $sheet->setShowGridlines(false);
        for ($row = 4; $row <= 18; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(24);
        }
        $sheet->getRowDimension(19)->setRowHeight(12);
        $sheet->getStyle("P4:P{$statusLastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_0);
        $sheet->getStyle("T4:T{$methodLastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_0);
        $sheet->getStyle('N3:P3')->applyFromArray($this->summaryHeaderStyle());
        $sheet->getStyle('R3:T3')->applyFromArray($this->summaryHeaderStyle());
        $sheet->getStyle("N4:P{$statusLastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $sheet->getStyle("R4:T{$methodLastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');

        $tableRange = "A{$this->tableHeaderRow}:M{$lastDataRow}";
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D7D9DC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle("A{$this->tableHeaderRow}:M{$this->tableHeaderRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension($this->tableHeaderRow)->setRowHeight(32);

        for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
            if (($row - $firstDataRow) % 2 === 1) {
                $sheet->getStyle("A{$row}:M{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F3F4F6');
            }
        }

        foreach ([
            'A' => 7, 'B' => 22, 'C' => 18, 'D' => 12, 'E' => 16,
            'F' => 16, 'G' => 18, 'H' => 18, 'I' => 13, 'J' => 13,
            'K' => 36, 'L' => 30, 'M' => 20,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setAutoSize(false);
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$firstDataRow}:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$firstDataRow}:J{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K{$firstDataRow}:M{$lastDataRow}")->getFont()->setName('Consolas')->setSize(9);
        $sheet->getStyle("A1:M2")->getFont()->setName('Arial');

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '343A40']]],
            $this->tableHeaderRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
            ],
        ];
    }

    private function buildStats(callable $labelResolver): array
    {
        $counts = [];
        foreach ($this->pagos as $pago) {
            $label = $labelResolver($pago);
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }
        arsort($counts);
        $total = array_sum($counts);

        return collect($counts)->map(fn (int $count, string $label) => [
            'label' => $label,
            'count' => $count,
            'percentage' => $total > 0 ? $count / $total : 0,
        ])->values()->all();
    }

    private function doughnutChart(string $name, string $title, array $stats, string $categoryColumn, string $valueColumn, string $topLeft, string $bottomRight): Chart
    {
        $pointCount = count($stats);
        $endRow = 3 + $pointCount;
        $labels = [new DataSeriesValues('String', $this->cellRange($valueColumn, 3, 3), null, 1, ['Cantidad'])];
        $categories = [new DataSeriesValues('String', $this->cellRange($categoryColumn, 4, $endRow), null, $pointCount, array_column($stats, 'label'))];
        $values = [new DataSeriesValues('Number', $this->cellRange($valueColumn, 4, $endRow), null, $pointCount, array_column($stats, 'count'))];
        $series = new DataSeries(DataSeries::TYPE_DOUGHNUTCHART, null, [0], $labels, $categories, $values);
        $layout = (new Layout())->setShowVal(true)->setShowPercent(true)->setShowLeaderLines(true);
        $chart = new Chart($name, new Title($title), new Legend(Legend::POSITION_RIGHT, null, false), new PlotArea($layout, [$series]));
        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);

        return $chart;
    }

    private function cellRange(string $column, int $startRow, int $endRow): string
    {
        return sprintf("'Pagos'!$%s$%d:$%s$%d", $column, $startRow, $column, $endRow);
    }

    private function summaryHeaderStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
        ];
    }
}
