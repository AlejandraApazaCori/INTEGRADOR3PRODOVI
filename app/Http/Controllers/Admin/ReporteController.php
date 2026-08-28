<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\GoogleDriveReport;
use App\Services\ExecutiveSummaryFormatter;
use App\Services\GoogleDriveReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class ReporteController extends Controller
{
    public function __construct(private readonly ExecutiveSummaryFormatter $formatter) {}

    public function show($id)
    {
        $this->authorizeAdmin();

        return view('administrador.empresas.reporte', $this->reportData($id));
    }

    public function downloadPdf($id)
    {
        $this->authorizeAdmin();
        $data = $this->reportData($id);
        $pdf = Pdf::loadView('administrador.empresas.pdf-reporte', $data)->setPaper('letter');

        return $pdf->download('resumen-ejecutivo-'.str($data['empresa']->nombre_empresa)->slug().'.pdf');
    }

    public function googleDoc(Request $request, $id)
    {
        $this->authorizeAdmin();

        try {
            $request->validate([
                'folder_id' => ['nullable', 'string', 'max:255'],
                'new_folder' => ['nullable', 'string', 'max:80', 'regex:~^[\p{L}\p{N} _().-]+$~u'],
            ]);

            $data = $this->reportData($id);
            $empresa = $data['empresa'];
            $drive = app(GoogleDriveReportService::class);
            $reportKey = 'executive_summary_company_'.$empresa->id;
            $stored = GoogleDriveReport::where('report_key', $reportKey)->first();
            $folderId = $drive->resolveCompanyDocumentFolder(
                $empresa->nombre_empresa,
                $request->input('folder_id'),
                $request->input('new_folder')
            );
            $uploaded = $drive->saveDocxAsGoogleDoc(
                'Resumen ejecutivo - '.$empresa->nombre_empresa,
                $this->reportDocx($data),
                $folderId,
                $stored?->file_id
            );

            GoogleDriveReport::updateOrCreate(['report_key' => $reportKey], [
                'file_id' => $uploaded['id'],
                'folder_id' => $folderId,
                'file_name' => $uploaded['name'],
                'web_view_link' => $uploaded['url'],
            ]);

            return redirect()->away($uploaded['url']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('drive_error', 'No se pudo crear el resumen ejecutivo en Google Drive. Inténtalo nuevamente.');
        }
    }

    public function driveFolders($id)
    {
        $this->authorizeAdmin();

        try {
            $empresa = Empresa::findOrFail($id);
            $drive = app(GoogleDriveReportService::class);
            $locations = $drive->companyDocumentFolders($empresa->nombre_empresa);
            $stored = GoogleDriveReport::where('report_key', 'executive_summary_company_'.$empresa->id)->first();
            $currentChild = $stored ? collect($locations['folders'])->firstWhere('id', $stored->folder_id) : null;
            $locations['current_folder'] = $stored ? [
                'id' => $stored->folder_id === $locations['root']['id'] || $currentChild ? $stored->folder_id : $locations['root']['id'],
                'name' => $stored->folder_id === $locations['root']['id'] || ! $currentChild ? $locations['root']['name'] : $currentChild['name'],
            ] : null;
            $locations['current_document'] = $stored ? [
                'name' => $stored->file_name,
                'url' => $stored->web_view_link,
            ] : null;

            return response()->json($locations);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'No se pudieron consultar las carpetas de la empresa.'], 500);
        }
    }

    private function reportData($id): array
    {
        $empresa = Empresa::with('usuario')->findOrFail($id);

        if (! $empresa->resumen_ejecutivo) {
            abort(404, 'Esta empresa no tiene un resumen ejecutivo para mostrar.');
        }

        $resumen = $empresa->resumen_ejecutivo;
        $secciones = $this->formatter->sections($resumen);

        return compact('empresa', 'secciones', 'resumen');
    }

    private function reportDocx(array $data): string
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginRight' => 900,
            'marginBottom' => 900,
            'marginLeft' => 900,
        ]);

        // El encabezado se repite automáticamente en todas las páginas del documento.
        $header = $section->addHeader();
        $headerTable = $header->addTable([
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
            'layout' => 'fixed',
            'cellMarginTop' => 90,
            'cellMarginBottom' => 90,
            'cellMarginLeft' => 120,
            'cellMarginRight' => 120,
        ]);
        $headerTable->addRow(620);
        $left = $headerTable->addCell(3900, ['bgColor' => '343A40', 'valign' => 'center']);
        $logoPath = public_path('imagenes/logoblanco.png');
        if (is_file($logoPath)) {
            $left->addImage($logoPath, ['width' => 92, 'alignment' => 'left']);
        } else {
            $left->addText('PRODOVI', ['bold' => true, 'size' => 16, 'color' => 'FFFFFF']);
        }
        $right = $headerTable->addCell(6500, ['bgColor' => '343A40', 'valign' => 'center']);
        $right->addText('Resumen ejecutivo empresarial', ['bold' => true, 'size' => 12, 'color' => 'FFFFFF'], ['alignment' => 'right', 'spaceAfter' => 40]);
        $right->addText('Documento generado el '.now()->format('d/m/Y H:i'), ['size' => 8, 'color' => 'D9DED6'], ['alignment' => 'right']);

        $empresa = $data['empresa'];
        $section->addTitle($empresa->nombre_empresa, 1);
        $section->addText(
            collect([$empresa->tipo_empresa, $empresa->usuario?->name, $empresa->usuario?->email])->filter()->implode(' · '),
            ['color' => '687064']
        );
        $section->addText('Diagnóstico estratégico elaborado a partir del cuestionario empresarial.', ['italic' => true, 'color' => '7D847A']);
        $section->addTextBreak();

        foreach ($data['secciones'] as $index => $reportSection) {
            $section->addTitle(($index + 1).'. '.$reportSection['titulo'], 2);
            $this->addDocumentContent($section, $reportSection['html']);
            $section->addTextBreak();
        }

        $temporary = tempnam(sys_get_temp_dir(), 'prodovi-executive-summary-');
        try {
            IOFactory::createWriter($phpWord, 'Word2007')->save($temporary);

            return (string) file_get_contents($temporary);
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    private function addDocumentContent(Section $section, string $html): void
    {
        $parts = preg_split('/(<table\b.*?<\/table>)/isu', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts ?: [] as $part) {
            if (preg_match('/^\s*<table\b/iu', $part)) {
                $this->addDocumentTable($section, $part);

                continue;
            }

            if (trim(strip_tags($part)) !== '') {
                Html::addHtml($section, $part, false, false);
            }
        }
    }

    private function addDocumentTable(Section $section, string $tableHtml): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$tableHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $rows = [];
        foreach ($dom->getElementsByTagName('tr') as $rowNode) {
            $cells = [];
            foreach ($rowNode->childNodes as $cellNode) {
                if ($cellNode instanceof DOMElement && in_array(strtolower($cellNode->tagName), ['th', 'td'], true)) {
                    $cells[] = [
                        'text' => trim((string) preg_replace('/\s+/u', ' ', $cellNode->textContent)),
                        'header' => strtolower($cellNode->tagName) === 'th',
                    ];
                }
            }
            if ($cells !== []) {
                $rows[] = $cells;
            }
        }

        if ($rows === []) {
            return;
        }

        $columnCount = max(array_map('count', $rows));
        $cellWidth = (int) floor(10400 / max(1, $columnCount));
        $table = $section->addTable([
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
            'layout' => 'fixed',
            'borderSize' => 6,
            'borderColor' => 'C7CBD1',
            'cellMarginTop' => 80,
            'cellMarginRight' => 90,
            'cellMarginBottom' => 80,
            'cellMarginLeft' => 90,
        ]);

        foreach ($rows as $row) {
            $table->addRow();
            foreach ($row as $cell) {
                $element = $table->addCell($cellWidth, [
                    'bgColor' => $cell['header'] ? 'E5E7EB' : 'FFFFFF',
                    'valign' => 'center',
                ]);
                $element->addText(
                    $cell['text'],
                    ['bold' => $cell['header'], 'size' => 9, 'color' => $cell['header'] ? '343A40' : '4B5563'],
                    ['spaceAfter' => 0]
                );
            }
            for ($index = count($row); $index < $columnCount; $index++) {
                $table->addCell($cellWidth, ['bgColor' => 'FFFFFF']);
            }
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists(),
            403,
            'No tienes permisos para realizar esta acción.'
        );
    }
}
