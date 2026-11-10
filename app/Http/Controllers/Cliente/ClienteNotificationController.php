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

        if ($item->type === \App\Notifications\TaskReviewNotification::class
            && ($item->data['kind'] ?? null) === 'client_review_requested') {
            $company = Auth::user()->empresas()->find($item->data['company_id'] ?? null);
            $campaign = $company ? \App\Models\Campania::query()
                ->where('usuario_cliente_id', Auth::id())
                ->find($item->data['campaign_id'] ?? null) : null;
            if ($company && $campaign && (($company->suscripcion_id && (int) $campaign->suscripcion_id === (int) $company->suscripcion_id)
                || $campaign->empresas()->whereKey($company->id)->exists())) {
                return redirect()->to(route('empresas.campanias.show', [$company, $campaign]).'#revision-archivo-'.(int) ($item->data['file_id'] ?? 0));
            }
        }

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
