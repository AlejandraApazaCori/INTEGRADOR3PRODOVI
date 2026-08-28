<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\GoogleDriveReport;
use App\Models\RespuestaCuestionario;
use App\Models\TemaCuestionario;
use App\Services\GoogleDriveReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\ListItem;

class CuestionarioAdminController extends Controller
{
    /**
     * Muestra el cuestionario de una empresa para que el administrador pueda editarlo.
     */
    public function show($id)
    {
        if (! auth()->check() || ! auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pagina.');
        }

        $empresa = Empresa::with('usuario')->findOrFail($id);
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();
        $respuestasExistentes = RespuestaCuestionario::where('empresa_id', $empresa->id)
            ->pluck('respuesta', 'pregunta_id')
            ->toArray();

        return view('administrador.empresas.cuestionario', compact(
            'empresa',
            'temas',
            'respuestasExistentes'
        ));
    }

    /**
     * Actualiza las respuestas del cuestionario de una empresa.
     */
    public function update(Request $request, $id)
    {
        if (! auth()->check() || ! auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta accion.');
        }

        $empresa = Empresa::findOrFail($id);
        $preguntas = TemaCuestionario::with('preguntas')->orderBy('orden')->get()
            ->flatMap(fn ($tema) => $tema->preguntas)
            ->unique('id')
            ->values();
        $rules = [];

        foreach ($preguntas as $pregunta) {
            if ($pregunta->tipo_respuesta === 'checkbox') {
                $rules["respuesta_{$pregunta->id}"] = $pregunta->requerido ? 'required|array|min:1' : 'nullable|array';
                $rules["respuesta_{$pregunta->id}.*"] = 'string|max:255';
            } else {
                $rules["respuesta_{$pregunta->id}"] = ($pregunta->requerido ? 'required' : 'nullable').'|string';
            }
            $rules["respuesta_{$pregunta->id}_otro"] = 'nullable|string|max:500';
        }

        $request->validate($rules);

        $request->validate([
            'continuar_campania' => 'nullable|integer|exists:suscripciones,id',
        ]);

        DB::transaction(function () use ($request, $empresa, $preguntas) {
            foreach ($preguntas as $pregunta) {
                $respuestaTexto = $this->formatAnswer(
                    $request->input("respuesta_{$pregunta->id}"),
                    $request->input("respuesta_{$pregunta->id}_otro")
                );

                $respuesta = RespuestaCuestionario::withTrashed()->firstOrNew([
                    'empresa_id' => $empresa->id,
                    'pregunta_id' => $pregunta->id,
                ]);

                if (blank($respuestaTexto)) {
                    if ($respuesta->exists) {
                        $respuesta->delete();
                    }

                    continue;
                }

                $respuesta->respuesta = trim((string) $respuestaTexto);
                $respuesta->save();

                if ($respuesta->trashed()) {
                    $respuesta->restore();
                }
            }

            $empresa->cuestionario_completado = true;
            $empresa->save();
        });

        if ($request->filled('continuar_campania')
            && (int) $request->continuar_campania === (int) $empresa->suscripcion_id) {
            return redirect()->route('administrador.campañas.preparar', $empresa->suscripcion_id)
                ->with('success', 'Cuestionario completado. Ahora prepararemos el resumen y el plan de marketing.');
        }

        return redirect()->route('administrador.empresas.cuestionario.show', $empresa->id)
            ->with('success', 'Las respuestas del cuestionario se han guardado correctamente.');
    }

    private function formatAnswer($answer, ?string $other): string
    {
        $values = is_array($answer) ? $answer : [$answer];
        $values = collect($values)->filter(fn ($value) => filled($value))->map(fn ($value) => trim((string) $value));

        if (filled($other) && $values->contains('Otro')) {
            $values = $values->reject(fn ($value) => $value === 'Otro')->push('Otro: '.trim($other));
        }

        return $values->implode(' | ');
    }

    public function downloadPdf($id)
    {
        $this->authorizeAdmin();
        $data = $this->documentData($id);
        $pdf = Pdf::loadView('pdf.cuestionario-empresa', $data)->setPaper('letter');

        return $pdf->download('cuestionario-'.str($data['empresa']->nombre_empresa)->slug().'.pdf');
    }

    public function googleDoc(Request $request, $id)
    {
        $this->authorizeAdmin();
        try {
            $drive = app(GoogleDriveReportService::class);
            $data = $this->documentData($id);
            $empresa = $data['empresa'];
            $request->validate([
                'folder_id' => ['nullable', 'string', 'max:255'],
                'new_folder' => ['nullable', 'string', 'max:80', 'regex:~^[\p{L}\p{N} _().-]+$~u'],
            ]);
            $reportKey = 'questionnaire_company_'.$empresa->id;
            $stored = GoogleDriveReport::where('report_key', $reportKey)->first();
            $folderId = $drive->resolveCompanyDocumentFolder($empresa->nombre_empresa, $request->input('folder_id'), $request->input('new_folder'));
            $fileName = 'Cuestionario - '.$empresa->nombre_empresa;
            $uploaded = $drive->saveDocxAsGoogleDoc($fileName, $this->questionnaireDocx($data), $folderId, $stored?->file_id);

            GoogleDriveReport::updateOrCreate(['report_key' => $reportKey], [
                'file_id' => $uploaded['id'], 'folder_id' => $folderId,
                'file_name' => $uploaded['name'], 'web_view_link' => $uploaded['url'],
            ]);

            return redirect()->away($uploaded['url']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('drive_error', 'No se pudo crear el documento en Google Drive. Inténtalo nuevamente.');
        }
    }

    public function driveFolders($id)
    {
        $this->authorizeAdmin();
        try {
            $empresa = Empresa::findOrFail($id);
            $drive = app(GoogleDriveReportService::class);
            $locations = $drive->companyDocumentFolders($empresa->nombre_empresa);
            $stored = GoogleDriveReport::where('report_key', 'questionnaire_company_'.$empresa->id)->first();
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

    private function questionnaireDocx(array $data): string
    {
        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(['marginTop' => 900, 'marginRight' => 900, 'marginBottom' => 900, 'marginLeft' => 900]);
        $header = $section->addHeader();
        $table = $header->addTable(['cellMarginTop' => 90, 'cellMarginBottom' => 90, 'cellMarginLeft' => 120, 'cellMarginRight' => 120]);
        $table->addRow(620);
        $left = $table->addCell(3600, ['bgColor' => '343A40', 'valign' => 'center']);
        $logoPath = public_path('imagenes/logoblanco.png');
        if (is_file($logoPath)) {
            $left->addImage($logoPath, ['width' => 92, 'alignment' => 'left']);
        } else {
            $left->addText('PRODOVI', ['bold' => true, 'size' => 16, 'color' => 'FFFFFF']);
        }
        $right = $table->addCell(5800, ['bgColor' => '343A40', 'valign' => 'center']);
        $right->addText('Cuestionario empresarial', ['bold' => true, 'size' => 12, 'color' => 'FFFFFF'], ['alignment' => 'right', 'spaceAfter' => 40]);
        $right->addText('Documento generado el '.now()->format('d/m/Y H:i'), ['size' => 8, 'color' => 'D9DED6'], ['alignment' => 'right']);

        $empresa = $data['empresa'];
        $section->addTitle($empresa->nombre_empresa, 1);
        $section->addText($empresa->tipo_empresa.' · '.$empresa->usuario->name.' · '.$empresa->usuario->email, ['color' => '687064']);
        $section->addTextBreak();
        foreach ($data['temas'] as $index => $tema) {
            $section->addTitle(($index + 1).'. '.$tema->nombre_tema, 2);
            if ($tema->descripcion_tema) {
                $section->addText($tema->descripcion_tema, ['italic' => true, 'color' => '7D847A']);
            }
            foreach ($tema->preguntas as $pregunta) {
                $section->addText($pregunta->pregunta, ['bold' => true, 'color' => '3E463B'], ['spaceBefore' => 160, 'spaceAfter' => 60]);
                $answer = trim((string) ($data['respuestas'][$pregunta->id] ?? ''));
                if ($answer === '') {
                    $section->addText('Sin respuesta', ['italic' => true, 'color' => '92988F']);
                } elseif ($pregunta->tipo_respuesta === 'checkbox') {
                    foreach (preg_split('/\s*\|\s*/', $answer, -1, PREG_SPLIT_NO_EMPTY) as $value) {
                        $section->addListItem($value, 0, ['color' => '596156'], ListItem::TYPE_BULLET_FILLED);
                    }
                } else {
                    $section->addText($answer, ['color' => '596156']);
                }
            }
            $section->addTextBreak();
        }

        $temporary = tempnam(sys_get_temp_dir(), 'prodovi-questionnaire-');
        try {
            IOFactory::createWriter($phpWord, 'Word2007')->save($temporary);

            return (string) file_get_contents($temporary);
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    private function documentData($id): array
    {
        $empresa = Empresa::with('usuario')->findOrFail($id);
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();
        $respuestas = RespuestaCuestionario::where('empresa_id', $empresa->id)
            ->pluck('respuesta', 'pregunta_id');

        return compact('empresa', 'temas', 'respuestas');
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
