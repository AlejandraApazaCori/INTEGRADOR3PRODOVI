<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosReportExport implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly string $viewName,
        private readonly mixed $users,
        private readonly mixed $plan = null,
        private readonly array $filters = [],
    ) {
    }

    public function view(): View
    {
        return view($this->viewName, [
            'users' => $this->users,
            'plan' => $this->plan,
            'filters' => $this->filters,
            'generatedAt' => now(),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A4');
        $sheet->setAutoFilter('A3:H3');

        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '343A40']]],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '565D64']],
            ],
        ];
    }
}
