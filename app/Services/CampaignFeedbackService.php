<?php

namespace App\Services;

use App\Models\Campania;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignFeedbackService
{
    public function clientCampaign(User $user, ?int $companyId = null, Campania|int|null $currentCampaign = null): ?Campania
    {
        if (is_int($currentCampaign)) {
            $currentCampaign = Campania::find($currentCampaign);
        }

        if ($currentCampaign && (int) $currentCampaign->usuario_cliente_id === (int) $user->id) {
            return $currentCampaign;
        }

        return Campania::query()
            ->where('usuario_cliente_id', $user->id)
            ->when($companyId, function ($query) use ($companyId, $user) {
                $query->whereHas('suscripcion.empresa', function ($company) use ($companyId, $user) {
                    $company->whereKey($companyId)->where('usuario_id', $user->id);
                });
            })
            ->latest('id')
            ->first();
    }

    public function unreadCount(Campania $campania, User $user): int
    {
        return DB::table('campania_mensaje_destinatarios as destinatarios')
            ->join('campania_mensajes as mensajes', 'mensajes.id', '=', 'destinatarios.mensaje_id')
            ->where('destinatarios.user_id', $user->id)
            ->where('mensajes.campania_id', $campania->id)
            ->whereNull('destinatarios.leido_at')
            ->whereNull('mensajes.deleted_at')
            ->count();
    }

    public function authorize(Campania $campania, User $user): void
    {
        abort_unless($this->isClient($campania, $user) || $this->isInternalMember($campania, $user), 403);
    }

    public function isClient(Campania $campania, User $user): bool
    {
        return (int) $campania->usuario_cliente_id === (int) $user->id;
    }

    public function isInternalMember(Campania $campania, User $user): bool
    {
        if ($user->hasAnyRole(['Super Administrador', 'Administrador'])) {
            return true;
        }

        return $this->internalParticipantIds($campania)->contains($user->id);
    }

    public function participants(Campania $campania): Collection
    {
        $campania->loadMissing([
            'cliente.roles',
            'creador.roles',
            'communityManager.roles',
            'disenador.roles',
            'disenadores.roles',
            'tareas.responsables.roles',
        ]);

        return collect([
            $campania->cliente,
            $campania->creador,
            $campania->communityManager,
            $campania->disenador,
            ...$campania->disenadores,
            ...$campania->tareas->pluck('responsables')->flatten(),
        ])->filter()->unique('id')->values();
    }

    public function internalParticipants(Campania $campania): Collection
    {
        return $this->participants($campania)
            ->reject(fn (User $participant) => (int) $participant->id === (int) $campania->usuario_cliente_id)
            ->values();
    }

    public function recipientsFor(Campania $campania, string $audience, ?int $directRecipientId, User $sender): Collection
    {
        $participants = $this->participants($campania);

        if ($audience === 'directo') {
            $recipient = $participants->firstWhere('id', $directRecipientId);
            abort_unless($recipient && (int) $recipient->id !== (int) $sender->id, 422, 'Selecciona un destinatario válido de la campaña.');

            if ($this->isClient($campania, $sender)) {
                abort_unless((int) $recipient->id !== (int) $campania->usuario_cliente_id, 422);
            }

            return collect([$recipient]);
        }

        $recipients = $audience === 'equipo'
            ? $this->internalParticipants($campania)
            : $participants;

        return $recipients->reject(fn (User $participant) => (int) $participant->id === (int) $sender->id)->values();
    }

    private function internalParticipantIds(Campania $campania): Collection
    {
        return $this->internalParticipants($campania)->pluck('id')->map(fn ($id) => (int) $id);
    }
}
