<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\TemaCuestionario;
use App\Models\RespuestaCuestionario;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumenAdminController extends Controller
{
    protected $groqService;

    // Inyectamos el servicio de Groq a travÃ©s del constructor
    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    /**
     * Muestra el formulario para editar el resumen ejecutivo.
     */
    public function edit($id)
    {
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para acceder a esta pÃ¡gina.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::with('usuario')->findOrFail($id);

        // 3. Verificar si la empresa tiene un resumen
        if (!$empresa->resumen_ejecutivo) {
            return redirect()->route('administrador.empresas.show', $empresa->id)
                ->with('error', 'Esta empresa no tiene un resumen ejecutivo para editar. Debes generarlo primero.');
        }

        // 4. Pasar los datos a la vista
        return view('administrador.empresas.editar-resumen', compact('empresa'));
    }

    /**
     * Actualiza el resumen ejecutivo con los cambios del administrador.
     */
    public function update(Request $request, $id)
    {
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acciÃ³n.');
        }

        // 2. Validar la solicitud
        $request->validate([
            'resumen_ejecutivo' => 'required|string|min:50',
        ], [
            'resumen_ejecutivo.required' => 'El resumen ejecutivo es obligatorio.',
            'resumen_ejecutivo.min' => 'El resumen debe tener al menos 50 caracteres.',
        ]);

        // 3. Obtener la empresa
        $empresa = Empresa::findOrFail($id);

        // 4. Actualizar el resumen
        $empresa->resumen_ejecutivo = $request->input('resumen_ejecutivo');
        $empresa->save();

        // 5. Redirigir con mensaje de Ã©xito
        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', 'Resumen ejecutivo actualizado correctamente.');
    }

    /**
     * Elimina el resumen ejecutivo de la empresa.
     */
    public function destroy($id)
    {
        // 1. Verificar si el usuario es administrador
        if (!auth()->check() || !auth()->user()->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists()) {
            abort(403, 'No tienes permisos para realizar esta acciÃ³n.');
        }

        // 2. Obtener la empresa
        $empresa = Empresa::findOrFail($id);

        // 3. Eliminar el resumen
        $empresa->resumen_ejecutivo = null;
        $empresa->save();

        // 4. Redirigir con mensaje de Ã©xito
        return redirect()->route('administrador.empresas.show', $empresa->id)
            ->with('success', 'Resumen ejecutivo eliminado correctamente.');
    }
}