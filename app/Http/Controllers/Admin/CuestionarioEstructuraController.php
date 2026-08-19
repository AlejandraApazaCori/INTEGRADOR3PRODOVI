<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemaCuestionario;
use App\Models\PreguntaCuestionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CuestionarioEstructuraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
                abort(403, 'No tienes permisos para acceder a esta página.');
            }
            return $next($request);
        });
    }

    /**
     * Muestra la lista de temas y preguntas para gestionarlos.
     */
    public function index()
    {
        $temas = TemaCuestionario::with('preguntas')->orderBy('orden')->get();
        return view('administrador.cuestionario.index', compact('temas'));
    }

    /**
     * Muestra el formulario para crear un nuevo tema.
     */
    public function create()
    {
        return view('administrador.cuestionario.form');
    }

    /**
     * Guarda un nuevo tema y sus preguntas en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_tema' => 'required|string|max:255',
            'descripcion_tema' => 'nullable|string',
            'preguntas' => 'required|array|min:1',
            'preguntas.*.pregunta' => 'required|string|max:1000',
            'preguntas.*.tipo_respuesta' => ['required', Rule::in(['texto', 'texto_largo', 'opcion_multiple', 'checkbox'])],
            'preguntas.*.ayuda' => 'nullable|string|max:1000',
            'preguntas.*.opciones' => 'nullable|string|max:5000',
            'preguntas.*.requerido' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $ultimoOrden = TemaCuestionario::max('orden') ?? 0;
            
            $tema = TemaCuestionario::create([
                'nombre_tema' => $request->nombre_tema,
                'descripcion_tema' => $request->descripcion_tema,
                'orden' => $ultimoOrden + 1,
            ]);

            foreach ($request->preguntas as $index => $preguntaData) {
                PreguntaCuestionario::create([
                    'tema_id' => $tema->id,
                    'pregunta' => $preguntaData['pregunta'],
                    'tipo_respuesta' => $preguntaData['tipo_respuesta'],
                    'opciones' => $this->parseOptions($preguntaData),
                    'ayuda' => $preguntaData['ayuda'] ?? null,
                    'requerido' => $preguntaData['requerido'] ?? false,
                    'orden' => $index + 1,
                ]);
            }
        });

        return redirect()->route('administrador.cuestionario.estructura.index')
            ->with('success', 'Tema y preguntas creados correctamente.');
    }

    /**
     * Muestra el formulario para editar un tema existente.
     */
    public function edit(TemaCuestionario $tema)
    {
        // Cargar el tema con sus preguntas ordenadas
        $tema->load('preguntas');
        return view('administrador.cuestionario.form', compact('tema'));
    }

    /**
     * Actualiza un tema y sus preguntas en la base de datos.
     */
    public function update(Request $request, TemaCuestionario $tema)
    {
        $request->validate([
            'nombre_tema' => 'required|string|max:255',
            'descripcion_tema' => 'nullable|string',
            'preguntas' => 'required|array|min:1',
            'preguntas.*.pregunta' => 'required|string|max:1000',
            'preguntas.*.tipo_respuesta' => ['required', Rule::in(['texto', 'texto_largo', 'opcion_multiple', 'checkbox'])],
            'preguntas.*.ayuda' => 'nullable|string|max:1000',
            'preguntas.*.opciones' => 'nullable|string|max:5000',
            'preguntas.*.requerido' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $tema) {
            $tema->update([
                'nombre_tema' => $request->nombre_tema,
                'descripcion_tema' => $request->descripcion_tema,
            ]);

            // Obtener IDs de las preguntas enviadas en el formulario
            $preguntasIdsFormulario = collect($request->preguntas)->pluck('id')->filter();

            // Eliminar preguntas que no estaban en el formulario
            $tema->preguntas()->whereNotIn('id', $preguntasIdsFormulario)->delete();

            // Actualizar o crear preguntas
            foreach ($request->preguntas as $index => $preguntaData) {
                PreguntaCuestionario::updateOrCreate(
                    ['id' => $preguntaData['id'] ?? null],
                    [
                        'tema_id' => $tema->id,
                        'pregunta' => $preguntaData['pregunta'],
                        'tipo_respuesta' => $preguntaData['tipo_respuesta'],
                        'opciones' => $this->parseOptions($preguntaData),
                        'ayuda' => $preguntaData['ayuda'] ?? null,
                        'requerido' => $preguntaData['requerido'] ?? false,
                        'orden' => $index + 1,
                    ]
                );
            }
        });

        return redirect()->route('administrador.cuestionario.estructura.index')
            ->with('success', 'Tema y preguntas actualizados correctamente.');
    }

    /**
     * Elimina un tema y todas sus preguntas asociadas.
     */
    public function destroy(TemaCuestionario $tema)
    {
        $tema->delete();
        return redirect()->route('administrador.cuestionario.estructura.index')
            ->with('success', 'Tema eliminado correctamente.');
    }
    
    /**
     * Actualiza el orden de los temas.
     */
    public function reorder(Request $request)
    {
        $temas = $request->get('temas');
        foreach ($temas as $index => $temaId) {
            TemaCuestionario::where('id', $temaId)->update(['orden' => $index + 1]);
        }
        return response()->json(['status' => 'success']);
    }

    private function parseOptions(array $preguntaData): ?array
    {
        if (! in_array($preguntaData['tipo_respuesta'], ['opcion_multiple', 'checkbox'], true)) {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', $preguntaData['opciones'] ?? ''))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
