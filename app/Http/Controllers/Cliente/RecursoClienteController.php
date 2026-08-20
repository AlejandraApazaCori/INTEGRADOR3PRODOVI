<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\RecursoEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RecursoClienteController extends Controller
{
    public function index(Request $request)
    {
        $empresas = $request->user()->empresas()->orderBy('nombre_empresa')->get();
        $empresaSeleccionada = $empresas->firstWhere('id', (int) $request->query('empresa_id')) ?? $empresas->first();
        $recursos = $empresaSeleccionada ? $empresaSeleccionada->recursos()->latest()->get() : collect();

        return view('clientes.recursos.index', compact('empresas', 'empresaSeleccionada', 'recursos'));
    }

    public function store(Request $request)
    {
        $empresa = Empresa::where('usuario_id', $request->user()->id)->findOrFail($request->input('empresa_id'));

        $request->validate([
            'imagenes' => ['nullable', 'array', 'max:20'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            'enlaces' => ['nullable', 'array', 'max:20'],
            'enlaces.*' => ['nullable', 'string', 'max:2048'],
        ]);

        $enlaces = collect($request->input('enlaces', []))
            ->map(fn ($url) => trim($url))->filter()->values();

        foreach ($enlaces as $url) {
            if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw ValidationException::withMessages(['enlaces' => "El enlace {$url} no es una URL válida."]);
            }
        }

        if (! $request->hasFile('imagenes') && $enlaces->isEmpty()) {
            throw ValidationException::withMessages(['recursos' => 'Selecciona al menos una imagen o agrega un enlace.']);
        }

        foreach ($request->file('imagenes', []) as $imagen) {
            $path = $imagen->store("recursos-empresa/{$empresa->id}", 'public');
            $empresa->recursos()->create(['tipo' => 'imagen', 'nombre' => $imagen->getClientOriginalName(), 'archivo_path' => $path]);
        }

        foreach ($enlaces as $url) {
            $host = parse_url($url, PHP_URL_HOST) ?: 'Enlace de video';
            $empresa->recursos()->create(['tipo' => 'enlace', 'nombre' => preg_replace('/^www\./', '', $host), 'url' => $url]);
        }

        return redirect()->route('clientes.recursos', ['empresa_id' => $empresa->id])->with('success', 'Los recursos fueron agregados correctamente.');
    }

    public function destroy(Request $request, RecursoEmpresa $recurso)
    {
        abort_unless($recurso->empresa()->where('usuario_id', $request->user()->id)->exists(), 403);
        if ($recurso->archivo_path) Storage::disk('public')->delete($recurso->archivo_path);
        $empresaId = $recurso->empresa_id;
        $recurso->delete();

        return redirect()->route('clientes.recursos', ['empresa_id' => $empresaId])->with('success', 'Recurso eliminado correctamente.');
    }

    public function updateName(Request $request, RecursoEmpresa $recurso)
    {
        abort_unless($recurso->empresa()->where('usuario_id', $request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $recurso->update(['nombre' => trim($validated['nombre'])]);

        return redirect()->route('clientes.recursos', ['empresa_id' => $recurso->empresa_id])
            ->with('success', 'El nombre del recurso fue actualizado correctamente.');
    }
}
