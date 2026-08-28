<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\GoogleDriveReport;
use App\Models\PlanMarketing;
use App\Models\Suscripcion;
use App\Services\ExecutiveSummaryFormatter;
use App\Services\GoogleDriveReportService;
use App\Services\MarketingPlanService;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMElement;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class PlanMarketingController extends Controller
{
    protected $marketingPlanService;

    /**
     * Inyecta el servicio de planes de marketing.
     */
    public function __construct(MarketingPlanService $marketingPlanService, private readonly ExecutiveSummaryFormatter $formatter)
    {
        $this->marketingPlanService = $marketingPlanService;
    }

    /**
     * Muestra el formulario para crear un nuevo plan de marketing para una empresa.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create(Empresa $empresa)
    {
        if (! $empresa->resumen_ejecutivo) {
            return back()->with('error', 'No se puede crear un plan de marketing sin un resumen ejecutivo generado primero.');
        }

        $suscripcionActiva = $empresa->suscripcion_id
            ? $empresa->suscripcion()->where('estado', 'activa')->first()
            : $empresa->usuario->suscripciones()
                ->where('estado', 'activa')
                ->latest()
                ->first();

        if (! $suscripcionActiva) {
            return back()->with('error', 'El usuario no tiene una suscripción activa para generar un plan de marketing.');
        }

        if ($suscripcionActiva->planMarketing()->exists()) {
            return back()->with('error', 'Ya existe un plan de marketing para la suscripción actual.');
        }

        $caracteristicasPlan = $suscripcionActiva->plan->caracteristicas;

        return view('administrador.empresas.crear-plan', compact('empresa', 'suscripcionActiva', 'caracteristicasPlan'));
    }

    /**
     * Almacena un nuevo plan de marketing en la base de datos.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Empresa $empresa)
    {
        $request->validate([
            'suscripcion_id' => 'required|exists:suscripciones,id',
        ]);

        $suscripcion = Suscripcion::with([
            'plan.caracteristicas',
            'plan.planCaracteristicas.caracteristica',
        ])->findOrFail($request->suscripcion_id);

        if (
            $suscripcion->usuario_id !== $empresa->usuario_id
            || $suscripcion->estado !== 'activa'
            || ($empresa->suscripcion_id && $suscripcion->id !== $empresa->suscripcion_id)
        ) {
            return back()->with('error', 'Suscripción no válida para esta empresa.');
        }

        if ($suscripcion->planMarketing()->exists()) {
            return back()->with('error', 'Ya existe un plan para esta suscripción.');
        }

        $caracteristicas = $suscripcion->plan->planCaracteristicas
            ->sortBy('orden')
            ->values()
            ->map(function ($planCaracteristica) {
                $nombre = $planCaracteristica->caracteristica->nombre ?? '';

                return [
                    'nombre' => $nombre,
                    'cantidad' => $planCaracteristica->cantidad,
                    'unidad' => $this->inferirUnidadDesdeNombre($nombre),
                    'frecuencia' => $planCaracteristica->frecuencia,
                    'descripcion' => $planCaracteristica->caracteristica->descripcion,
                    'orden' => $planCaracteristica->orden,
                    'es_destacado' => (bool) $planCaracteristica->es_destacado,
                ];
            })->toArray();

        $planContexto = [
            'nombre' => $suscripcion->plan->nombre,
            'descripcion' => $suscripcion->plan->descripcion,
            'periodo_facturacion' => $suscripcion->plan->periodo_facturacion,
            'precio' => $suscripcion->plan->precio,
            'moneda' => $suscripcion->plan->moneda,
        ];

        $contenidoPlan = $this->marketingPlanService->generateMarketingPlan(
            $empresa->nombre_empresa,
            $empresa->resumen_ejecutivo,
            $caracteristicas,
            $planContexto
        );

        if (! $contenidoPlan || str_contains($contenidoPlan, 'Hubo un error')) {
            return back()->with('error', 'No se pudo generar el contenido del plan de marketing. Inténtelo de nuevo.');
        }

        PlanMarketing::create([
            'empresa_id' => $empresa->id,
            'suscripcion_id' => $suscripcion->id,
            'contenido' => $contenidoPlan,
            'estado' => 'activo',
        ]);

        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', '¡Plan de marketing creado y guardado con éxito!');
    }

    /**
     * Muestra un plan de marketing específico.
     *
     * @return \Illuminate\View\View
     */
    public function show(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);
        $secciones = $this->formatter->sections($planMarketing->contenido);

        return view('administrador.planes-marketing.show', compact('planMarketing', 'secciones'));
    }

    public function edit(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa.usuario', 'suscripcion.plan']);
        $secciones = $this->formatter->sections($planMarketing->contenido);
        $previous = session()->getOldInput('secciones');
        $editorSections = collect(is_array($previous) ? $previous : $secciones)
            ->map(fn (array $seccion) => [
                'titulo' => trim((string) ($seccion['titulo'] ?? '')),
                'contenido_html' => $this->formatter->sanitizeEditorHtml(
                    (string) ($seccion['contenido_html'] ?? $seccion['html'] ?? '')
                ),
            ])->values()->all();
        $empresa = $planMarketing->empresa;
        $editorConfig = [
            'page_title' => 'Editar plan de marketing',
            'eyebrow' => 'Documento estratégico y operativo',
            'hero_title' => 'Editar plan de marketing',
            'hero_description' => 'Organiza y revisa cada estrategia antes de guardar',
            'back_label' => 'Volver al plan',
            'back_url' => route('administrador.empresas.planes-marketing.show', $planMarketing),
            'form_action' => route('administrador.empresas.planes-marketing.update', $planMarketing),
            'intro_label' => 'Editor visual del plan',
            'intro_title' => 'Estrategias y acciones',
            'intro_description' => 'Edita directamente el resultado final: las negritas, viñetas y tablas se muestran como aparecerán en el plan.',
            'document_note' => 'Este plan será la base operativa para crear y ejecutar la campaña publicitaria.',
            'view_label' => 'Ver plan actual',
        ];

        return view('administrador.empresas.editar-resumen', compact('empresa', 'editorSections', 'editorConfig'));
    }

    public function update(Request $request, PlanMarketing $planMarketing)
    {
        $validated = $request->validate([
            'secciones' => ['required', 'array', 'min:1', 'max:30'],
            'secciones.*.titulo' => ['required', 'string', 'max:160'],
            'secciones.*.contenido_html' => ['required', 'string'],
        ], [
            'secciones.required' => 'El plan debe contener al menos una sección.',
            'secciones.*.titulo.required' => 'Todas las secciones necesitan un título.',
            'secciones.*.contenido_html.required' => 'Todas las secciones necesitan contenido.',
        ]);

        $sections = collect($validated['secciones'])->map(function (array $section) {
            $section['contenido'] = $this->formatter->markdownFromHtml($section['contenido_html']);

            return $section;
        });

        if ($sections->contains(fn (array $section) => mb_strlen(strip_tags($section['contenido'])) < 10)) {
            return back()->withErrors(['secciones' => 'El contenido de cada sección debe tener al menos 10 caracteres.'])->withInput();
        }

        $planMarketing->contenido = $sections->values()->map(fn (array $section, int $index) => sprintf(
            "## %d %s\n%s",
            $index + 1,
            trim(preg_replace('/[*_`#]+/u', '', $section['titulo'])),
            $section['contenido']
        ))->implode("\n\n");
        $planMarketing->save();

        return redirect()->route('administrador.empresas.planes-marketing.show', $planMarketing->id)
            ->with('success', '¡Plan de marketing actualizado con éxito!');
    }

    public function downloadPDF(PlanMarketing $planMarketing)
    {
        $planMarketing->load(['empresa', 'suscripcion.plan']);

        $seccionesParseadas = $this->formatter->sections($planMarketing->contenido);

        $pdf = Pdf::loadView('administrador.planes-marketing.pdf', compact('planMarketing', 'seccionesParseadas'))
            ->setPaper('letter', 'portrait')
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->download('plan-marketing-'.$planMarketing->empresa->nombre_empresa.'.pdf');
    }

    public function googleDoc(Request $request, PlanMarketing $planMarketing)
    {
        try {
            $request->validate([
                'folder_id' => ['nullable', 'string', 'max:255'],
                'new_folder' => ['nullable', 'string', 'max:80', 'regex:~^[\p{L}\p{N} _().-]+$~u'],
            ]);
            $planMarketing->load(['empresa', 'suscripcion.plan']);
            $drive = app(GoogleDriveReportService::class);
            $reportKey = 'marketing_plan_'.$planMarketing->id;
            $stored = GoogleDriveReport::where('report_key', $reportKey)->first();
            $folderId = $drive->resolveCompanyDocumentFolder(
                $planMarketing->empresa->nombre_empresa,
                $request->input('folder_id'),
                $request->input('new_folder')
            );
            $uploaded = $drive->saveDocxAsGoogleDoc(
                'Plan de marketing - '.$planMarketing->empresa->nombre_empresa,
                $this->marketingPlanDocx($planMarketing),
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

            return back()->with('drive_error', 'No se pudo crear el plan de marketing en Google Drive. Inténtalo nuevamente.');
        }
    }

    public function driveFolders(PlanMarketing $planMarketing)
    {
        try {
            $planMarketing->loadMissing('empresa');
            $drive = app(GoogleDriveReportService::class);
            $locations = $drive->companyDocumentFolders($planMarketing->empresa->nombre_empresa);
            $stored = GoogleDriveReport::where('report_key', 'marketing_plan_'.$planMarketing->id)->first();
            $currentChild = $stored ? collect($locations['folders'])->firstWhere('id', $stored->folder_id) : null;
            $locations['current_folder'] = $stored ? [
                'id' => $stored->folder_id === $locations['root']['id'] || $currentChild ? $stored->folder_id : $locations['root']['id'],
                'name' => $stored->folder_id === $locations['root']['id'] || ! $currentChild ? $locations['root']['name'] : $currentChild['name'],
            ] : null;
            $locations['current_document'] = $stored ? ['name' => $stored->file_name, 'url' => $stored->web_view_link] : null;

            return response()->json($locations);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'No se pudieron consultar las carpetas de la empresa.'], 500);
        }
    }

    /**
     * Elimina el plan de marketing de la empresa.
     */
    public function destroy(PlanMarketing $planMarketing)
    {
        $planMarketing->delete();

        return redirect()->route('administrador.empresas.show', $planMarketing->empresa_id)
            ->with('success', '¡Plan de marketing eliminado con éxito!');
    }

    private function marketingPlanDocx(PlanMarketing $planMarketing): string
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(['marginTop' => 900, 'marginRight' => 900, 'marginBottom' => 900, 'marginLeft' => 900]);
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
        $right->addText('Plan de marketing empresarial', ['bold' => true, 'size' => 12, 'color' => 'FFFFFF'], ['alignment' => 'right', 'spaceAfter' => 40]);
        $right->addText('Documento generado el '.now()->format('d/m/Y H:i'), ['size' => 8, 'color' => 'D9DED6'], ['alignment' => 'right']);

        $section->addTitle($planMarketing->empresa->nombre_empresa, 1);
        $section->addText(
            $planMarketing->suscripcion->plan->nombre.' · Estado: '.ucfirst($planMarketing->estado).' · Creado el '.$planMarketing->created_at->format('d/m/Y'),
            ['color' => '687064']
        );
        $section->addText('Estrategia operativa basada en el cuestionario, el resumen ejecutivo y los recursos contratados.', ['italic' => true, 'color' => '7D847A']);
        $section->addTextBreak();

        foreach ($this->formatter->sections($planMarketing->contenido) as $index => $planSection) {
            $section->addTitle(($index + 1).'. '.$planSection['titulo'], 2);
            $this->addDocumentContent($section, $planSection['html']);
            $section->addTextBreak();
        }

        $temporary = tempnam(sys_get_temp_dir(), 'prodovi-marketing-plan-');
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
            } elseif (trim(strip_tags($part)) !== '') {
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
                    $cells[] = ['text' => trim((string) preg_replace('/\s+/u', ' ', $cellNode->textContent)), 'header' => strtolower($cellNode->tagName) === 'th'];
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
                $element = $table->addCell($cellWidth, ['bgColor' => $cell['header'] ? 'E5E7EB' : 'FFFFFF', 'valign' => 'center']);
                $element->addText($cell['text'], ['bold' => $cell['header'], 'size' => 9, 'color' => $cell['header'] ? '343A40' : '4B5563'], ['spaceAfter' => 0]);
            }
            for ($index = count($row); $index < $columnCount; $index++) {
                $table->addCell($cellWidth, ['bgColor' => 'FFFFFF']);
            }
        }
    }

    private function inferirUnidadDesdeNombre(string $nombreCaracteristica): ?string
    {
        $nombre = mb_strtolower(trim($nombreCaracteristica));

        return match (true) {
            str_contains($nombre, 'post') => 'posts',
            str_contains($nombre, 'diseño'), str_contains($nombre, 'diseno') => 'disenos',
            str_contains($nombre, 'video') => 'videos',
            str_contains($nombre, 'gif') => 'gifs',
            str_contains($nombre, 'foto'), str_contains($nombre, 'fotografía'), str_contains($nombre, 'fotografia') => 'sesiones',
            str_contains($nombre, 'tiktok'), str_contains($nombre, 'tik tok') => 'piezas',
            str_contains($nombre, 'catálogo'), str_contains($nombre, 'catalogo') => 'catalogos',
            default => null,
        };
    }
}
