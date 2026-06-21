<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Suscripcion;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmpresaAdminController extends Controller
{
    /**
     * Mostrar lista de empresas (para administrador)
     */
    public function index(Request $request)
    {
        // 1. Verificar si el usuario estÃ¡ autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Verificar si el usuario tiene el rol de "Administrador"
        if (!$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
        }

        // 4. Obtener todos los usuarios para el filtro
        $usuarios = User::orderBy('name')->get();

        // 5. Obtener todos los planes para el filtro
        $planes = Plan::orderBy('nombre')->get();

        // 6. Construir la consulta de empresas con filtros
        $empresasQuery = Empresa::with(['usuario', 'usuario.suscripciones.plan']);

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $empresasQuery->where('usuario_id', $request->usuario_id);
        }

        // Filtro por plan
        if ($request->filled('plan_id')) {
            $empresasQuery->whereHas('usuario.suscripciones', function ($query) use ($request) {
                $query->where('plan_id', $request->plan_id);
            });
        }

        // Filtro por estado (activo/inactivo)
        if ($request->filled('estado')) {
            if ($request->estado === 'activa') {
                $empresasQuery->whereHas('usuario.suscripciones', function ($query) {
                    $query->where('estado', 'activa');
                });
            } elseif ($request->estado === 'inactiva') {
                $empresasQuery->whereDoesntHave('usuario.suscripciones', function ($query) {
                    $query->where('estado', 'activa');
                });
            }
        }

        // Paginar los resultados
        $empresas = $empresasQuery->orderBy('created_at', 'desc')->paginate(12);

        return view('administrador.empresas.index', compact('empresas', 'usuarios', 'planes'));
    }

    /**
     * Mostrar detalles de una empresa (para administrador)
     */
    public function show($id)
    {
        // 1. Verificar si el usuario estÃ¡ autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Verificar si el usuario tiene el rol de "Administrador"
        if (!$user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
        }

        // 4. Obtener la empresa con sus relaciones
        $empresa = Empresa::with([
            'usuario', 
            'planesMarketing.suscripcion.plan'
        ])->findOrFail($id);
        
        // 5. Obtener la suscripciÃ³n activa del usuario
        $suscripcionActiva = Suscripcion::with('plan.caracteristicas')
            ->where('usuario_id', $empresa->usuario_id)
            ->where('estado', 'activa')
            ->first();
            
        return view('administrador.empresas.show', compact('empresa', 'suscripcionActiva'));
    }

    /**
     * Mostrar formulario para crear empresa para un usuario especÃ­fico
     */
    public function crearParaUsuario($usuario_id)
    {
        $user = User::findOrFail($usuario_id);
        $temas = \App\Models\TemaCuestionario::with('preguntas')->orderBy('orden')->get();
        return view('administrador.empresas.crear-para-usuario', compact('user', 'temas'));
    }

    /**
     * Guardar empresa y respuestas del cuestionario
     */
    public function guardarParaUsuario(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'nombre_empresa' => 'required|string|max:255',
            'tipo_empresa' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $empresa = Empresa::create([
                'usuario_id' => $request->usuario_id,
                'nombre_empresa' => $request->nombre_empresa,
                'tipo_empresa' => $request->tipo_empresa,
                'descripcion' => $request->descripcion,
                'cuestionario_completado' => true,
            ]);

            $preguntas = \App\Models\PreguntaCuestionario::all();
            foreach ($preguntas as $pregunta) {
                $respuestaTexto = $request->input("respuesta_{$pregunta->id}");
                if ($respuestaTexto) {
                    \App\Models\RespuestaCuestionario::create([
                        'empresa_id' => $empresa->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $respuestaTexto
                    ]);
                }
            }
        });

        return redirect()->route('administrador.campañas.index')
            ->with('success', 'Empresa creada y cuestionario guardado correctamente.');
    }
}