<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
class ReporteController extends Controller
{
    /**
     * Muestra el reporte ejecutivo formateado para una empresa
     */
    public function show($id)
    {
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::with('usuario')->findOrFail($id);

        // 3. Verificar si la empresa tiene un resumen
        if (!$empresa->resumen_ejecutivo) {
            return redirect()->route('administrador.empresas.show', $empresa->id)
                ->with('error', 'Esta empresa no tiene un resumen ejecutivo para mostrar.');
        }

        // 4. Procesar el resumen para extraer las secciones
        $resumen = $empresa->resumen_ejecutivo;
        $secciones = $this->procesarResumen($resumen);

        // 5. Pasar los datos a la vista
        return view('administrador.empresas.reporte', compact(
            'empresa',
            'secciones',
            'resumen'
        ));
    }

    public function downloadPdf($id)
{
    // Verificar si el usuario es administrador
    if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
        abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
    }

    // Obtener la empresa
    $empresa = Empresa::with('usuario')->findOrFail($id);

    // Verificar si la empresa tiene un resumen
    if (!$empresa->resumen_ejecutivo) {
        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('error', 'Esta empresa no tiene un resumen ejecutivo para mostrar.');
    }

    // Procesar el resumen
    $resumen = $empresa->resumen_ejecutivo;
    $secciones = $this->procesarResumen($resumen);

    // Generar el PDF
    $pdf = Pdf::loadView('administrador.empresas.pdf-reporte', compact(
        'empresa',
        'secciones'
    ));

    // Descargar el PDF
    return $pdf->download('reporte-ejecutivo-' . $empresa->nombre_empresa . '.pdf');
}
    /**
     * Procesa el texto del resumen para extraer las secciones
     */
    private function procesarResumen($resumen)
    {
        $secciones = [];
        $lineas = explode("\n", $resumen);
        $seccionActual = null;
        $contenidoActual = [];

        foreach ($lineas as $linea) {
            // Detectar encabezados de secciÃ³n (##)
            if (preg_match('/^##\s+(.+)$/', $linea, $matches)) {
                // Guardar la secciÃ³n anterior si existe
                if ($seccionActual) {
                    $secciones[] = [
                        'titulo' => $seccionActual,
                        'contenido' => implode("\n", array_filter($contenidoActual))
                    ];
                }

                // Iniciar nueva secciÃ³n
                $seccionActual = $matches[1];
                $contenidoActual = [];
            } else {
                // AÃ±adir lÃ­nea al contenido de la secciÃ³n actual
                if ($seccionActual) {
                    $contenidoActual[] = $linea;
                }
            }
        }

        // Guardar la Ãºltima secciÃ³n
        if ($seccionActual) {
            $secciones[] = [
                'titulo' => $seccionActual,
                'contenido' => implode("\n", array_filter($contenidoActual))
            ];
        }

        return $secciones;
    }
}