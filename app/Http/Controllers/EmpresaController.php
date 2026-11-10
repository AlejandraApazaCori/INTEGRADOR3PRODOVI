<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    /**
     * Mostrar lista de empresas del usuario
     */
    public function index()
    {
        $empresas = Auth::user()->empresas;
        return view('clientes.empresas.index', compact('empresas'));
    }

    /**
     * Mostrar formulario para crear nueva empresa
     */
    public function create()
    {
        return view('clientes.empresas.create');
    }

    /**
     * Guardar nueva empresa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'tipo_empresa' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string',
            // 'sitio_web' => 'nullable|url',  // <-- ELIMINA ESTA LÍNEA
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $suscripcion = Suscripcion::query()
            ->where('usuario_id', Auth::id())
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('vigencia_activada_at')
                    ->orWhere('fecha_fin', '>', now());
            })
            ->whereHas('pagos', fn ($query) => $query->where('estado', 'completado'))
            ->whereDoesntHave('empresa', fn ($query) => $query->withTrashed())
            ->latest('id')
            ->first();

        if (! $suscripcion) {
            return back()->withInput()->with('error', 'Compra otro plan antes de registrar una empresa adicional.');
        }

        $empresa = new Empresa();
        $empresa->usuario_id = Auth::id();
        $empresa->suscripcion_id = $suscripcion->id;
        $empresa->nombre_empresa = $request->nombre_empresa;
        $empresa->tipo_empresa = $request->tipo_empresa;
        if ($request->has('direccion')) {
            $empresa->direccion = $request->direccion;
        }
        $empresa->descripcion = $request->descripcion;
        // $empresa->sitio_web = $request->sitio_web; // <-- ELIMINA ESTA LÍNEA

        // Guardar logo si se proporciona
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $empresa->logo = $logoPath;
        }

        $empresa->save();

        return redirect()->route('empresas.cuestionario', $empresa->id)
            ->with('success', 'Empresa creada correctamente. Ahora completa el cuestionario.');
    }

    /**
     * Mostrar detalles de una empresa
     */
    public function show($id)
    {
        $user = Auth::user();
        $empresa = Empresa::where('usuario_id', $user->id)->findOrFail($id);

        if ($user->socialAccountsTableExists()) {
            $empresa->load('socialAccounts');
        }

        $companySocialAccounts = $empresa->relationLoaded('socialAccounts')
            ? $empresa->socialAccounts->keyBy('provider')
            : collect();
        $isFirstCompany = (int) $user->empresas()->oldest('id')->value('id') === (int) $empresa->id;
        $legacySocialAccounts = $isFirstCompany ? $user->linkedSocialAccounts() : collect();

        return view('clientes.empresas.show', compact(
            'empresa',
            'companySocialAccounts',
            'legacySocialAccounts'
        ));
    }

    public function campanias($id)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        $campanias = \App\Models\Campania::query()
            ->where('usuario_cliente_id', Auth::id())
            ->where(function ($query) use ($empresa) {
                $query->whereHas('empresas', fn ($companies) => $companies->whereKey($empresa->id));
                if ($empresa->suscripcion_id) {
                    $query->orWhere('suscripcion_id', $empresa->suscripcion_id);
                }
            })
            ->withCount('tareas')
            ->latest('fecha_inicio')
            ->get();

        return view('clientes.empresas.campanias', compact('empresa', 'campanias'));
    }

    public function campania($id, \App\Models\Campania $campania)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        abort_unless((int) $campania->usuario_cliente_id === (int) Auth::id()
            && (($empresa->suscripcion_id && (int) $campania->suscripcion_id === (int) $empresa->suscripcion_id)
                || $campania->empresas()->whereKey($empresa->id)->exists()), 404);

        $publishedTasks = $campania->tareas()
            ->whereIn('publication_status', ['published', 'partial'])
            ->with('archivos')
            ->latest('published_at')
            ->get();
        $reviewTasks = $campania->tareas()
            ->where('revision_cliente_habilitada', true)
            ->whereHas('archivos', fn ($files) => $files->where('estado', 'aprobado'))
            ->with(['archivos' => fn ($files) => $files->where('estado', 'aprobado')->with('revisorCliente')->latest('id')])
            ->latest('id')
            ->get();

        return view('clientes.empresas.campania', compact('empresa', 'campania', 'publishedTasks', 'reviewTasks'));
    }

    /**
     * Mostrar formulario para editar empresa
     */
    public function edit($id)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        return view('clientes.empresas.edit', compact('empresa'));
    }

    /**
     * Actualizar empresa
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'tipo_empresa' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string',
            // Quita la coma sobrante aquí también si la tienes
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        $empresa->nombre_empresa = $request->nombre_empresa;
        $empresa->tipo_empresa = $request->tipo_empresa;
        $empresa->direccion = $request->direccion;
        $empresa->descripcion = $request->descripcion;
        
        // No necesitas asignar sitio_web aquí tampoco

        // Actualizar logo si se proporciona
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $empresa->logo = $logoPath;
        }

        $empresa->save();

        if ($request->boolean('return_to_onboarding')) {
            return redirect()->route('clientes.onboarding', ['empresa' => 'editada'])
                ->with('onboarding_success', 'Los datos de tu empresa se actualizaron correctamente.');
        }

        return redirect()->route('empresas.show', $empresa->id)
            ->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * Eliminar empresa
     */
    public function destroy($id)
    {
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        
        // Eliminar logo si existe
        if ($empresa->logo) {
            Storage::disk('public')->delete($empresa->logo);
        }
        
        $empresa->delete();

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa eliminada correctamente.');
    }
}
