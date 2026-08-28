<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\ExecutiveSummaryFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmpresaAdminController extends Controller
{
    /**
     * Mostrar lista de empresas (para administrador)
     */
    public function index(Request $request)
    {
        // 1. Verificar si el usuario está autenticado
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Verificar si el usuario tiene el rol de "Administrador"
        if (! $user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // 4. Obtener todos los usuarios para el filtro
        $usuarios = User::orderBy('name')->get();

        // 5. Obtener todos los planes para el filtro
        $planes = Plan::orderBy('nombre')->get();

        $perPage = (int) $request->input('per_page', 6);
        $perPage = $perPage > 0 ? min($perPage, 100) : 6;

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

        $empresasFiltradas = (clone $empresasQuery)->count();

        $stats = [
            'total' => Empresa::count(),
            'filtradas' => $empresasFiltradas,
            'cuestionario_completado' => Empresa::where('cuestionario_completado', true)->count(),
            'sin_suscripcion_activa' => Empresa::whereDoesntHave('usuario.suscripciones', function ($query) {
                $query->where('estado', 'activa');
            })->count(),
        ];

        // Paginar los resultados
        $empresas = $empresasQuery
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('administrador.empresas.index', compact('empresas', 'usuarios', 'planes', 'perPage', 'stats'));
    }

    /**
     * Mostrar detalles de una empresa (para administrador)
     */
    public function show($id)
    {
        // 1. Verificar si el usuario está autenticado
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Verificar si el usuario tiene el rol de "Administrador"
        if (! $user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        // 4. Obtener la empresa con sus relaciones
        $empresa = Empresa::with([
            'usuario',
            'planesMarketing.suscripcion.plan',
        ])->findOrFail($id);

        // 5. Obtener la suscripción activa del usuario
        $suscripcionActiva = Suscripcion::with('plan.caracteristicas')
            ->where('usuario_id', $empresa->usuario_id)
            ->where('estado', 'activa')
            ->first();

        $campaniaActiva = Campania::query()
            ->where('estado', 'activa')
            ->where('fecha_fin', '>', now())
            ->where(function ($query) use ($empresa) {
                if ($empresa->suscripcion_id) {
                    $query->where('suscripcion_id', $empresa->suscripcion_id)
                        ->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->whereKey($empresa->id));

                    return;
                }

                $query->whereHas('empresas', fn ($empresaQuery) => $empresaQuery->whereKey($empresa->id));
            })
            ->latest('fecha_inicio')
            ->first();

        $resumenSecciones = $empresa->resumen_ejecutivo
            ? app(ExecutiveSummaryFormatter::class)->sections($empresa->resumen_ejecutivo)
            : [];
        $resumenHtml = collect($resumenSecciones)->pluck('html')->implode(' ');
        $resumenHtml = preg_replace('/<\/(?:p|li|tr|h[1-6])>/iu', '$0 ', $resumenHtml);
        $resumenVistaPrevia = $resumenSecciones === [] ? null : Str::limit(
            trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $resumenHtml))),
            360
        );

        return view('administrador.empresas.show', compact('empresa', 'suscripcionActiva', 'campaniaActiva', 'resumenSecciones', 'resumenVistaPrevia'));
    }

    /**
     * Mostrar formulario para crear empresa para un usuario específico
     */
    public function crearParaUsuario(Request $request, $usuario_id)
    {
        $user = User::findOrFail($usuario_id);
        $temas = \App\Models\TemaCuestionario::with('preguntas')->orderBy('orden')->get();
        $suscripcionesDisponibles = $user->suscripciones()
            ->with('plan')
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->whereHas('pagos', fn ($query) => $query->where('estado', 'completado'))
            ->whereDoesntHave('empresa', fn ($query) => $query->withTrashed())
            ->latest('id')
            ->get();
        $suscripcionSeleccionadaId = $request->integer('suscripcion_id');

        if (! $suscripcionesDisponibles->contains('id', $suscripcionSeleccionadaId)) {
            $suscripcionSeleccionadaId = $suscripcionesDisponibles->first()?->id;
        }

        return view('administrador.empresas.crear-para-usuario', compact(
            'user',
            'temas',
            'suscripcionesDisponibles',
            'suscripcionSeleccionadaId'
        ));
    }

    /**
     * Mostrar formulario simple para crear empresa para un usuario espec�fico
     */
    public function crearEmpresa($usuario_id)
    {
        $user = User::findOrFail($usuario_id);

        return view('administrador.empresas.crearempresa', compact('user'));
    }

    /**
     * Guardar empresa simple para un usuario espec�fico
     */
    public function guardarEmpresa(Request $request, $usuario_id)
    {
        $user = User::findOrFail($usuario_id);

        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'tipo_empresa' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $empresa = new Empresa;
        $empresa->usuario_id = $user->id;
        $empresa->nombre_empresa = $request->nombre_empresa;
        $empresa->tipo_empresa = $request->tipo_empresa;
        $empresa->descripcion = $request->descripcion;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $empresa->logo = $logoPath;
        }

        $empresa->save();

        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', 'Empresa creada correctamente.');
    }

    /**
     * Eliminar empresa
     */
    public function destroy($id)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        $empresa = Empresa::findOrFail($id);
        $usuario_id = $empresa->usuario_id;
        $empresa->delete();

        return redirect()->route('administrador.usuarios.view', $usuario_id)
            ->with('success', 'Empresa eliminada correctamente.');
    }

    /**
     * Guardar empresa y respuestas del cuestionario
     */
    public function guardarParaUsuario(Request $request)
    {
        $preguntas = \App\Models\PreguntaCuestionario::query()->orderBy('orden')->get();
        $rules = [
            'usuario_id' => 'required|exists:users,id',
            'suscripcion_id' => 'required|exists:suscripciones,id',
            'nombre_empresa' => 'required|string|max:255',
            'tipo_empresa' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'continuar_campania' => 'nullable|integer|exists:suscripciones,id',
        ];
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

        $empresa = DB::transaction(function () use ($request, $preguntas) {
            $suscripcion = Suscripcion::query()
                ->whereKey($request->integer('suscripcion_id'))
                ->where('usuario_id', $request->integer('usuario_id'))
                ->where('estado', 'activa')
                ->where(function ($query) {
                    $query->whereNull('vigencia_activada_at')
                        ->orWhere('fecha_fin', '>', now());
                })
                ->whereHas('pagos', fn ($query) => $query->where('estado', 'completado'))
                ->whereDoesntHave('empresa', fn ($query) => $query->withTrashed())
                ->lockForUpdate()
                ->first();

            if (! $suscripcion) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'suscripcion_id' => 'La suscripción seleccionada no está disponible o ya tiene una empresa asociada.',
                ]);
            }

            $empresa = Empresa::create([
                'usuario_id' => $request->usuario_id,
                'suscripcion_id' => $suscripcion->id,
                'nombre_empresa' => $request->nombre_empresa,
                'tipo_empresa' => $request->tipo_empresa,
                'descripcion' => $request->descripcion,
                'cuestionario_completado' => true,
            ]);

            foreach ($preguntas as $pregunta) {
                $respuesta = $request->input("respuesta_{$pregunta->id}");
                $valores = collect(is_array($respuesta) ? $respuesta : [$respuesta])
                    ->filter(fn ($valor) => filled($valor))
                    ->map(fn ($valor) => trim((string) $valor));
                $otro = trim((string) $request->input("respuesta_{$pregunta->id}_otro", ''));
                if ($otro !== '' && $valores->contains('Otro')) {
                    $valores = $valores->reject(fn ($valor) => $valor === 'Otro')->push('Otro: '.$otro);
                }
                $respuestaTexto = $valores->implode(' | ');

                if ($respuestaTexto !== '') {
                    \App\Models\RespuestaCuestionario::create([
                        'empresa_id' => $empresa->id,
                        'pregunta_id' => $pregunta->id,
                        'respuesta' => $respuestaTexto,
                    ]);
                }
            }

            return $empresa;
        });

        if ($request->filled('continuar_campania')
            && (int) $request->continuar_campania === (int) $empresa->suscripcion_id) {
            return redirect()->route('administrador.campañas.preparar', $empresa->suscripcion_id)
                ->with('success', 'Empresa y cuestionario guardados. Ahora prepararemos el resumen y el plan de marketing.');
        }

        return redirect()->route('administrador.campañas.index')
            ->with('success', 'Empresa creada y cuestionario guardado correctamente.');
    }
}
