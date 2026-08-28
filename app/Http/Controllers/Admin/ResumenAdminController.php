<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\ExecutiveSummaryFormatter;
use App\Services\GroqService;
use Illuminate\Http\Request;

class ResumenAdminController extends Controller
{
    protected $groqService;

    // Inyectamos el servicio de Groq a travÃ©s del constructor
    public function __construct(GroqService $groqService, private readonly ExecutiveSummaryFormatter $formatter)
    {
        $this->groqService = $groqService;
    }

    /**
     * Muestra el formulario para editar el resumen ejecutivo.
     */
    public function edit($id)
    {
        // 1. Verificar si el usuario es administrador
        if (! auth()->check() || ! auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::with('usuario')->findOrFail($id);

        // 3. Verificar si la empresa tiene un resumen
        if (! $empresa->resumen_ejecutivo) {
            return redirect()->route('administrador.empresas.show', $empresa->id)
                ->with('error', 'Esta empresa no tiene un resumen ejecutivo para editar. Debes generarlo primero.');
        }

        $secciones = $this->formatter->sections($empresa->resumen_ejecutivo);
        $previous = session()->getOldInput('secciones');
        $editorSections = collect(is_array($previous) ? $previous : $secciones)
            ->map(fn (array $seccion) => [
                'titulo' => trim((string) ($seccion['titulo'] ?? '')),
                'contenido_html' => $this->formatter->sanitizeEditorHtml(
                    (string) ($seccion['contenido_html'] ?? $seccion['html'] ?? '')
                ),
            ])->values()->all();

        return view('administrador.empresas.editar-resumen', compact('empresa', 'editorSections'));
    }

    /**
     * Actualiza el resumen ejecutivo con los cambios del administrador.
     */
    public function update(Request $request, $id)
    {
        // 1. Verificar si el usuario es administrador
        if (! auth()->check() || ! auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // 2. Validar la solicitud
        $validated = $request->validate([
            'secciones' => ['required', 'array', 'min:1', 'max:20'],
            'secciones.*.titulo' => ['required', 'string', 'max:160'],
            'secciones.*.contenido_html' => ['required', 'string'],
        ], [
            'secciones.required' => 'El resumen debe contener al menos una sección.',
            'secciones.min' => 'El resumen debe contener al menos una sección.',
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

        // 3. Obtener la empresa
        $empresa = Empresa::findOrFail($id);

        // 4. Actualizar el resumen
        $empresa->resumen_ejecutivo = $sections
            ->values()
            ->map(fn (array $seccion, int $index) => sprintf(
                "## %d %s\n%s",
                $index + 1,
                trim(preg_replace('/[*_`#]+/u', '', $seccion['titulo'])),
                $seccion['contenido']
            ))
            ->implode("\n\n");
        $empresa->save();

        // 5. Redirigir con mensaje de Ã©xito
        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', 'Resumen ejecutivo actualizado correctamente.');
    }

    public function regenerate(Empresa $empresa)
    {
        if (! auth()->check() || ! auth()->user()->roles()
            ->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $empresa->loadMissing('respuestasCuestionario.pregunta');
        $respuestas = $empresa->respuestasCuestionario
            ->filter(fn ($respuesta) => filled($respuesta->respuesta) && $respuesta->pregunta)
            ->map(fn ($respuesta) => [
                'pregunta' => $respuesta->pregunta->pregunta,
                'respuesta' => $respuesta->respuesta,
            ])->values()->all();

        if ($respuestas === []) {
            return back()->with('error', 'No se puede regenerar el resumen porque el cuestionario no contiene respuestas útiles.');
        }

        $resumen = $this->groqService->generateSummary($empresa->nombre_empresa, $respuestas);
        if (blank($resumen) || str_contains(mb_strtolower($resumen), 'hubo un error')) {
            return back()->with('error', 'La IA no pudo regenerar el resumen. Inténtalo nuevamente.');
        }

        $empresa->update(['resumen_ejecutivo' => $resumen]);

        return redirect()->route('administrador.empresas.reporte', $empresa->id)
            ->with('success', 'Resumen ejecutivo regenerado correctamente a partir del cuestionario empresarial.');
    }

    /**
     * Elimina el resumen ejecutivo de la empresa.
     */
    public function destroy($id)
    {
        // 1. Verificar si el usuario es administrador
        if (! auth()->check() || ! auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::findOrFail($id);

        // 3. Eliminar el resumen
        $empresa->resumen_ejecutivo = null;
        $empresa->save();

        // 4. Redirigir con mensaje de éxito
        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', 'Resumen ejecutivo eliminado correctamente.');
    }
}
