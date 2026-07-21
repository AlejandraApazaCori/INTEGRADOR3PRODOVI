<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
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
            'descripcion' => 'nullable|string',
            // 'sitio_web' => 'nullable|url',  // <-- ELIMINA ESTA LÍNEA
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $empresa = new Empresa();
        $empresa->usuario_id = Auth::id();
        $empresa->nombre_empresa = $request->nombre_empresa;
        $empresa->tipo_empresa = $request->tipo_empresa;
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
        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        return view('clientes.empresas.show', compact('empresa'));
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
            'descripcion' => 'nullable|string',
            // Quita la coma sobrante aquí también si la tienes
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $empresa = Empresa::where('usuario_id', Auth::id())->findOrFail($id);
        $empresa->nombre_empresa = $request->nombre_empresa;
        $empresa->tipo_empresa = $request->tipo_empresa;
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