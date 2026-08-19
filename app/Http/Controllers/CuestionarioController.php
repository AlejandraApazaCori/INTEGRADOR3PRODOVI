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
        $cuestionarioBloqueado = $empresa->suscripcion()
            ->whereNotNull('vigencia_activada_at')
            ->exists();
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();

        $respuestasExistentes = RespuestaCuestionario::where('empresa_id', $empresaId)
            ->pluck('respuesta', 'pregunta_id')
            ->toArray();

        return view('clientes.cuestionario.show', compact('empresa', 'temas', 'respuestasExistentes', 'cuestionarioBloqueado'));
    }

    /**
     * Guardar respuestas del cuestionario
     */
    public function store(Request $request, $empresaId)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($empresaId);

        if ($empresa->suscripcion()->whereNotNull('vigencia_activada_at')->exists()) {
            return redirect()->route('empresas.cuestionario', $empresa->id)
                ->with('error', 'El cuestionario ya no puede editarse porque la campaña comenzó.');
        }

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

        if ($request->boolean('from_dashboard')) {
            return redirect()->route('clientes.dashboard')
                ->with('success', 'Cuestionario completado correctamente.');
        }

        return redirect()->route('empresas.show', $empresaId)
            ->with('success', 'Cuestionario completado correctamente.');
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
}
