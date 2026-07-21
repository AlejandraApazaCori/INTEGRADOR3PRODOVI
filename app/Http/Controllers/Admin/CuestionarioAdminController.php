<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\RespuestaCuestionario;
use App\Models\TemaCuestionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuestionarioAdminController extends Controller
{
    /**
     * Muestra el cuestionario de una empresa para que el administrador pueda editarlo.
     */
    public function show($id)
    {
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
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
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta accion.');
        }

        $empresa = Empresa::findOrFail($id);
        $preguntas = TemaCuestionario::with('preguntas')->orderBy('orden')->get()
            ->flatMap(fn ($tema) => $tema->preguntas)
            ->unique('id')
            ->values();
        $rules = [];

        foreach ($preguntas as $pregunta) {
            $rules["respuesta_{$pregunta->id}"] = $pregunta->requerido ? 'required|string' : 'nullable|string';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $empresa, $preguntas) {
            foreach ($preguntas as $pregunta) {
                $respuestaTexto = $request->input("respuesta_{$pregunta->id}");

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

        return redirect()->route('administrador.empresas.cuestionario.show', $empresa->id)
            ->with('success', 'Las respuestas del cuestionario se han guardado correctamente.');
    }
}