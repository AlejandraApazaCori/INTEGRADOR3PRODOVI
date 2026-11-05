<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ClienteNotificationController extends Controller
{
    public function show(string $notification): RedirectResponse
    {
        $item = Auth::user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        if ($item->type === \App\Notifications\ReunionAgendadaNotification::class && ! empty($item->data['campaign_id'])) {
            $campania = \App\Models\Campania::query()
                ->where('usuario_cliente_id', Auth::id())
                ->find($item->data['campaign_id']);
            if ($campania) {
                return redirect()->route('clientes.campanias.reuniones.index', $campania);
            }
        }

        return redirect()->route('clientes.dashboard');
    }
}
