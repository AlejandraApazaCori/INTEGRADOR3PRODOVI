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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosChartReportExport implements FromView, ShouldAutoSize, WithCharts, WithStyles, WithTitle
{
    private array $roleStats;

    private array $statusStats;

    private int $tableHeaderRow;

    public function __construct(
        private readonly string $viewName,
        private readonly mixed $users,
        private readonly mixed $plan = null,
        private readonly array $filters = [],
    ) {
        $this->roleStats = $this->buildRoleStats();
        $this->statusStats = $this->buildStatusStats();
        $this->tableHeaderRow = 20;
    }

    public function view(): View
    {
        return view($this->viewName, [
            'users' => $this->users,
            'plan' => $this->plan,
            'filters' => $this->filters,
            'generatedAt' => now(),
            'roleStats' => $this->roleStats,
            'statusStats' => $this->statusStats,
            'tableHeaderRow' => $this->tableHeaderRow,
        ]);
    }

    public function title(): string
    {
        return 'Usuarios';
    }

    public function roleStats(): array
    {
        return $this->roleStats;
    }

    public function statusStats(): array
    {
        return $this->statusStats;
    }

    public function charts(): array
    {
        if ($this->users->isEmpty()) {
            return [];
        }

        return [
            $this->doughnutChart('roles_chart', 'Distribución por tipo de rol', $this->roleStats, 'I', 'J', 'A4', 'D18'),
            $this->doughnutChart('status_chart', 'Distribución por estado', $this->statusStats, 'M', 'N', 'E4', 'H18'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $firstDataRow = $this->tableHeaderRow + 1;
        $lastDataRow = max($firstDataRow, $this->tableHeaderRow + $this->users->count());
        $roleLastRow = 3 + max(count($this->roleStats), 1);
        $statusLastRow = 3 + max(count($this->statusStats), 1);

        $sheet->freezePane("A{$firstDataRow}");
        $sheet->setAutoFilter("A{$this->tableHeaderRow}:H{$lastDataRow}");
        $sheet->setShowGridlines(false);
        for ($row = 4; $row <= 18; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(24);
        }
        $sheet->getRowDimension(19)->setRowHeight(12);
        $sheet->getStyle("K4:K{$roleLastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_0);
        $sheet->getStyle("O4:O{$statusLastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_0);
        $sheet->getStyle('I3:K3')->applyFromArray($this->summaryHeaderStyle('565D64'));
        $sheet->getStyle('M3:O3')->applyFromArray($this->summaryHeaderStyle('565D64'));
        $sheet->getStyle("I4:K{$roleLastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $sheet->getStyle("M4:O{$statusLastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '343A40']]],
            $this->tableHeaderRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
            ],
        ];
    }

    private function buildRoleStats(): array
    {
        $counts = [];

        foreach ($this->users as $user) {
            $roles = $user->roles->pluck('nombre_rol')->filter()->unique();
            if ($roles->isEmpty()) {
                $roles = collect(['Sin rol']);
            }

            foreach ($roles as $role) {
                $counts[$role] = ($counts[$role] ?? 0) + 1;
            }
        }

        arsort($counts);

        return $this->withPercentages($counts);
    }

    private function buildStatusStats(): array
    {
        $counts = [];

        foreach ($this->users as $user) {
            $isAdmin = $user->roles->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->isNotEmpty();
            $isActive = $user->suscripciones->where('estado', 'activa')->where('fecha_fin', '>', now())->isNotEmpty();
            $status = $isAdmin
                ? 'Administrador'
                : ($isActive ? 'Activo' : ($user->suscripciones->isEmpty() ? 'Sin plan' : 'Inactivo'));
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        arsort($counts);

        return $this->withPercentages($counts);
    }

    private function withPercentages(array $counts): array
    {
        $total = array_sum($counts);

        return collect($counts)->map(fn (int $count, string $label) => [
            'label' => $label,
            'count' => $count,
            'percentage' => $total > 0 ? $count / $total : 0,
        ])->values()->all();
    }

    private function doughnutChart(
        string $name,
        string $title,
        array $stats,
        string $categoryColumn,
        string $valueColumn,
        string $topLeft,
        string $bottomRight,
    ): Chart {
        $categoryValues = array_column($stats, 'label');
        $countValues = array_column($stats, 'count');
        $pointCount = count($stats);
        $endRow = 3 + $pointCount;
        $labels = [new DataSeriesValues('String', $this->cellRange($valueColumn, 3, 3), null, 1, ['Cantidad'])];
        $categories = [new DataSeriesValues('String', $this->cellRange($categoryColumn, 4, $endRow), null, $pointCount, $categoryValues)];
        $values = [new DataSeriesValues('Number', $this->cellRange($valueColumn, 4, $endRow), null, $pointCount, $countValues)];
        $series = new DataSeries(DataSeries::TYPE_DOUGHNUTCHART, null, [0], $labels, $categories, $values);
        $layout = (new Layout())
            ->setShowVal(true)
            ->setShowPercent(true)
            ->setShowLeaderLines(true);
        $plotArea = new PlotArea($layout, [$series]);
        $chart = new Chart($name, new Title($title), new Legend(Legend::POSITION_RIGHT, null, false), $plotArea);
        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);

        return $chart;
    }

    private function cellRange(string $column, int $startRow, int $endRow): string
    {
        return sprintf("'Usuarios'!$%s$%d:$%s$%d", $column, $startRow, $column, $endRow);
    }

    private function summaryHeaderStyle(string $color): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
        ];
    }

}
