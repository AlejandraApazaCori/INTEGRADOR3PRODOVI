<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResumenController extends Controller
{
    protected $groqService;

    // Inyectamos el servicio para no crear una instancia manualmente
    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Genera y guarda el resumen ejecutivo para una empresa.
     *
     * @param int $empresaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(int $empresaId)
    {
        // 1. Encontrar la empresa y cargar sus respuestas con la pregunta asociada
        $empresa = Empresa::with('respuestasCuestionario.pregunta')->findOrFail($empresaId);

        // 2. Formatear las respuestas para nuestro servicio
        $datosParaIa = $empresa->respuestasCuestionario->map(function ($respuesta) {
            return [
                'pregunta' => $respuesta->pregunta->pregunta,
                'respuesta' => $respuesta->respuesta,
            ];
        })->toArray();

        // 3. Llamar al servicio de Groq para generar el resumen
        $resumen = $this->groqService->generateSummary($empresa->nombre_empresa, $datosParaIa);

        // 4. Guardar el resumen en la base de datos
        $empresa->resumen_ejecutivo = $resumen;
        $empresa->save();

        // 5. Devolver una respuesta (por ejemplo, JSON)
        return response()->json([
            'success' => true,
            'message' => 'Resumen ejecutivo generado con éxito.',
            'summary' => $resumen
        ]);
    }
}