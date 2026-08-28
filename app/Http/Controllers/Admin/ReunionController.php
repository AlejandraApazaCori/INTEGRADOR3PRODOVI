<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReunionController extends Controller
{
    public function updateClientAccess(Request $request, Campania $campania)
    {
        $validated = $request->validate([
            'reuniones_cliente_por_mes' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $campania->update($validated);

        return redirect()
            ->route('administrador.campañas.show', $campania)
            ->withFragment('reuniones')
            ->with('success', 'Acceso mensual del cliente actualizado.');
    }

    public function store(Request $request, Campania $campania)
    {
        $request->merge([
            'participantes_ids' => collect($request->input('participantes_ids', []))
                ->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
        ]);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'plataforma' => ['required', Rule::in(['zoom', 'meet', 'teams', 'otro'])],
            'enlace' => ['required', 'url:http,https', 'max:2048'],
            'fecha_inicio' => ['required', 'date'],
            'participantes_ids' => ['required', 'array', 'min:2'],
            'participantes_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $campania->loadMissing(['disenadores']);
        $personasPermitidas = collect([
            $campania->usuario_creador_id,
            $campania->community_manager_id,
            $campania->disenador_id,
            $campania->usuario_cliente_id,
            ...$campania->disenadores->pluck('id')->all(),
        ])->filter()->map(fn ($id) => (int) $id)->unique();

        if (collect($validated['participantes_ids'])->diff($personasPermitidas)->isNotEmpty()) {
            return back()->withErrors([
                'participantes_ids' => 'Sólo puedes invitar al cliente y a personas del equipo de esta campaña.',
            ])->withInput()->withFragment('reuniones');
        }

        $reunion = $campania->reuniones()->create([
            'creador_id' => Auth::id(),
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'plataforma' => $validated['plataforma'],
            'enlace' => $validated['enlace'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => Carbon::parse($validated['fecha_inicio'])->addHour(),
            'estado' => 'agendada',
            'origen' => 'administrador',
        ]);
        $reunion->participantes()->sync($validated['participantes_ids']);

        return redirect()
            ->route('administrador.campañas.show', $campania)
            ->withFragment('reuniones')
            ->with('success', 'Reunión agendada correctamente.');
    }
}
