<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use App\Models\User;
use App\Notifications\ReunionAgendadaNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ReunionClienteController extends Controller
{
    public function index(Request $request, Campania $campania)
    {
        $this->authorizeClient($request, $campania);
        $campania->loadMissing(['suscripcion.empresa', 'reuniones.participantes']);

        $reuniones = $campania->reuniones
            ->where('estado', 'agendada')
            ->sortBy('fecha_inicio')
            ->values();
        $cupoMensual = max(0, (int) $campania->reuniones_cliente_por_mes);
        $solicitudesDelMes = $campania->reuniones()
            ->where('origen', 'cliente')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('clientes.campanias.reuniones', compact('campania', 'reuniones', 'cupoMensual', 'solicitudesDelMes'));
    }

    public function store(Request $request, Campania $campania)
    {
        $this->authorizeClient($request, $campania);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'plataforma' => ['required', Rule::in(['zoom', 'meet', 'teams', 'otro'])],
            'enlace' => ['required', 'url:http,https', 'max:2048'],
            'fecha_inicio' => ['required', 'date', 'after:now'],
        ], [
            'titulo.required' => 'Escribe el motivo de la reunión.',
            'plataforma.required' => 'Selecciona una plataforma.',
            'enlace.required' => 'Agrega el enlace de la reunión.',
            'enlace.url' => 'Ingresa un enlace válido que comience con https:// o http://.',
            'fecha_inicio.required' => 'Selecciona una fecha y hora.',
            'fecha_inicio.after' => 'La reunión debe programarse para una fecha futura.',
        ]);

        $cupoMensual = max(0, (int) $campania->reuniones_cliente_por_mes);
        $solicitudesDelMes = $campania->reuniones()
            ->where('origen', 'cliente')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        if ($cupoMensual === 0 || $solicitudesDelMes >= $cupoMensual) {
            return back()->withErrors(['reunion' => 'Ya alcanzaste el límite de reuniones que puedes agendar este mes.'])->withInput();
        }

        $campania->loadMissing('disenadores');
        $staffIds = collect([
            $campania->usuario_creador_id,
            $campania->community_manager_id,
            $campania->disenador_id,
            ...$campania->disenadores->pluck('id')->all(),
        ])->filter()->unique();
        if ($staffIds->isEmpty()) {
            return back()->withErrors(['reunion' => 'Aún no hay un responsable asignado a esta campaña.'])->withInput();
        }

        $reunion = $campania->reuniones()->create([
            'creador_id' => $request->user()->id,
            'titulo' => trim($validated['titulo']),
            'descripcion' => $validated['descripcion'] ?? null,
            'plataforma' => $validated['plataforma'],
            'enlace' => $validated['enlace'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => Carbon::parse($validated['fecha_inicio'])->addHour(),
            'estado' => 'agendada',
            'origen' => 'cliente',
        ]);
        $recipientIds = $staffIds->push($request->user()->id)->unique();
        $reunion->participantes()->sync($recipientIds->all());
        Notification::send(User::whereIn('id', $recipientIds)->get(), new ReunionAgendadaNotification($reunion));

        return redirect()->route('clientes.campanias.reuniones.index', $campania)
            ->with('success', 'Reunión agendada correctamente. El equipo recibió una notificación.');
    }

    private function authorizeClient(Request $request, Campania $campania): void
    {
        abort_unless((int) $campania->usuario_cliente_id === (int) $request->user()->id, 403);
    }
}
