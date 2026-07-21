<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\RespuestaCuestionario;
use App\Models\TemaCuestionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuestionarioController extends Controller
{
    /**
     * Mostrar formulario del cuestionario para una empresa
     */
    public function show($empresaId)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($empresaId);
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();

        $respuestasExistentes = RespuestaCuestionario::where('empresa_id', $empresaId)
            ->pluck('respuesta', 'pregunta_id')
            ->toArray();

        return view('clientes.cuestionario.show', compact('empresa', 'temas', 'respuestasExistentes'));
    }

    /**
     * Guardar respuestas del cuestionario
     */
    public function store(Request $request, $empresaId)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($empresaId);
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

        return redirect()->route('empresas.show', $empresaId)
            ->with('success', 'Cuestionario completado correctamente.');
    }
}