<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\Empresa;
use App\Models\RecursoEmpresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecursoCampaniaController extends Controller
{
    public function store(Request $request, Campania $campania)
    {
        $empresa = $this->empresaDeCampania($campania);
        abort_unless($empresa, 404, 'La campaña no tiene una empresa asociada.');

        $validator = Validator::make($request->all(), [
            'imagenes' => ['nullable', 'array', 'max:20'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            'enlaces' => ['nullable', 'array', 'max:20'],
            'enlaces.*' => ['nullable', 'string', 'max:2048'],
            'visible_cliente' => ['nullable', 'boolean'],
        ]);

        $enlaces = collect($request->input('enlaces', []))
            ->map(fn ($url) => trim((string) $url))
            ->filter()
            ->values();

        $validator->after(function ($validator) use ($request, $enlaces) {
            foreach ($enlaces as $url) {
                if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                    $validator->errors()->add('enlaces', "El enlace {$url} no es una URL válida.");
                }
            }

            if (! $request->hasFile('imagenes') && $enlaces->isEmpty()) {
                $validator->errors()->add('recursos', 'Selecciona al menos una imagen o agrega un enlace.');
            }
        });

        if ($validator->fails()) {
            return redirect()->to(route('administrador.campañas.show', $campania).'#recursos')
                ->withErrors($validator)
                ->withInput();
        }

        $attributes = [
            'origen' => 'administracion',
            'visible_cliente' => $request->boolean('visible_cliente'),
            'creado_por_id' => $request->user()->id,
        ];

        foreach ($request->file('imagenes', []) as $imagen) {
            $path = $imagen->store("recursos-empresa/{$empresa->id}", 'public');
            $empresa->recursos()->create($attributes + [
                'tipo' => 'imagen',
                'nombre' => $imagen->getClientOriginalName(),
                'archivo_path' => $path,
            ]);
        }

        foreach ($enlaces as $url) {
            $host = parse_url($url, PHP_URL_HOST) ?: 'Enlace';
            $empresa->recursos()->create($attributes + [
                'tipo' => 'enlace',
                'nombre' => preg_replace('/^www\./', '', $host),
                'url' => $url,
            ]);
        }

        return $this->redirectToResources($campania, 'Recursos agregados correctamente.');
    }

    public function updateVisibility(Request $request, Campania $campania, RecursoEmpresa $recurso)
    {
        $this->assertAdministrativeResource($campania, $recurso);

        $validated = $request->validate([
            'visible_cliente' => ['required', 'boolean'],
        ]);

        $recurso->update(['visible_cliente' => (bool) $validated['visible_cliente']]);

        return $this->redirectToResources(
            $campania,
            $recurso->visible_cliente ? 'El recurso ahora es visible para el cliente.' : 'El recurso se ocultó para el cliente.'
        );
    }

    public function updateName(Request $request, Campania $campania, RecursoEmpresa $recurso)
    {
        $this->assertCampaignResource($campania, $recurso);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->to(route('administrador.campañas.show', $campania).'#recursos')
                ->withErrors($validator, 'renameResource')
                ->withInput()
                ->with('rename_resource_id', $recurso->id);
        }

        $recurso->update(['nombre' => trim($validator->validated()['nombre'])]);

        return $this->redirectToResources($campania, 'El nombre del recurso fue actualizado correctamente.');
    }

    public function destroy(Campania $campania, RecursoEmpresa $recurso)
    {
        $this->assertCampaignResource($campania, $recurso);

        if ($recurso->archivo_path) {
            Storage::disk('public')->delete($recurso->archivo_path);
        }

        $recurso->delete();

        return $this->redirectToResources($campania, 'Recurso eliminado correctamente.');
    }

    private function empresaDeCampania(Campania $campania): ?Empresa
    {
        $campania->loadMissing(['suscripcion.empresa', 'empresas', 'cliente.empresas']);

        return $campania->suscripcion?->empresa
            ?? $campania->empresas->first()
            ?? $campania->cliente?->empresas?->first();
    }

    private function assertAdministrativeResource(Campania $campania, RecursoEmpresa $recurso): void
    {
        $this->assertCampaignResource($campania, $recurso);

        abort_unless($recurso->origen === 'administracion', 404);
    }

    private function assertCampaignResource(Campania $campania, RecursoEmpresa $recurso): void
    {
        $empresa = $this->empresaDeCampania($campania);

        abort_unless(
            $empresa && $recurso->empresa_id === $empresa->id,
            404
        );
    }

    private function redirectToResources(Campania $campania, string $message)
    {
        return redirect()->to(route('administrador.campañas.show', $campania).'#recursos')->with('success', $message);
    }
}
