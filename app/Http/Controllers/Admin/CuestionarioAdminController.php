<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\TemaCuestionario;
use App\Models\PreguntaCuestionario; // AsegÃºrate de importar este modelo
use App\Models\RespuestaCuestionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importar DB para transacciones

class CuestionarioAdminController extends Controller
{
    /**
     * Muestra el cuestionario de una empresa para que el administrador pueda editarlo.
     */
    public function show($id)
    {
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::with('usuario')->findOrFail($id);

        // 3. Obtener todos los temas con sus preguntas
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();

        // 4. Obtener las respuestas existentes del cuestionario de esta empresa
        $respuestasExistentes = RespuestaCuestionario::where('empresa_id', $empresa->id)
            ->pluck('respuesta', 'pregunta_id')
            ->toArray();

        // 5. Pasar los datos a la vista
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
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acciÃ³n.');
        }

        // 2. Validar que la empresa existe
        $empresa = Empresa::findOrFail($id);

        // 3. Obtener todas las preguntas para saber quÃ© campos esperar
        $preguntas = PreguntaCuestionario::all();
        $rules = [];
        foreach ($preguntas as $pregunta) {
            if ($pregunta->requerido) {
                $rules["respuesta_{$pregunta->id}"] = 'required|string';
            } else {
                $rules["respuesta_{$pregunta->id}"] = 'nullable|string';
            }
        }
        $request->validate($rules);

        // 4. Usar una transacciÃ³n para asegurar que todo se guarde o nada se guarde
        DB::transaction(function () use ($request, $empresa, $preguntas) {
            foreach ($preguntas as $pregunta) {
                $respuestaTexto = $request->input("respuesta_{$pregunta->id}");
                
                // Usar updateOrCreate para guardar la respuesta (crea nueva o actualiza existente)
                RespuestaCuestionario::updateOrCreate(
                    ['empresa_id' => $empresa->id, 'pregunta_id' => $pregunta->id],
                    ['respuesta' => $respuestaTexto]
                );
            }

            // Marcar el cuestionario como completado
            $empresa->cuestionario_completado = true;
            $empresa->save();
        });

        // 5. Redirigir con un mensaje de Ã©xito
        return redirect()->route('administrador.empresas.cuestionario.show', $empresa->id)
            ->with('success', 'Las respuestas del cuestionario se han guardado correctamente.');
    }
}