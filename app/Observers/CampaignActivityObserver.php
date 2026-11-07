<?php

namespace App\Observers;

use App\Models\Campania;
use App\Models\CampaignActivityLog;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CampaignActivityObserver
{
    private const CAMPAIGN_FIELDS = [
        'nombre', 'descripcion', 'objetivo_general', 'publico_objetivo', 'mensaje_principal',
        'tono_comunicacion', 'canales', 'indicadores', 'modo_creacion', 'es_borrador',
        'fecha_inicio', 'fecha_fin', 'estado', 'community_manager_id', 'disenador_id',
    ];

    private const TASK_FIELDS = [
        'titulo', 'descripcion', 'entregable', 'tipo_contenido', 'fecha_inicio',
        'fecha_limite', 'estado', 'prioridad', 'asignado_id', 'requiere_aprobacion',
        'visible_cliente', 'publication_status', 'published_at',
    ];

    public function created(Model $model): void
    {
        $this->record($model, $model instanceof Campania ? 'campaign_created' : 'task_created', []);
    }

    public function updated(Model $model): void
    {
        $fields = $model instanceof Campania ? self::CAMPAIGN_FIELDS : self::TASK_FIELDS;
        $changes = [];

        foreach ($fields as $field) {
            if (! $model->wasChanged($field)) {
                continue;
            }
            $changes[$field] = [
                'from' => $this->displayValue($model->getOriginal($field)),
                'to' => $this->displayValue($model->getAttribute($field)),
            ];
        }

        if ($changes === []) {
            return;
        }

        if ($model instanceof Campania) {
            $action = $model->wasChanged('es_borrador') && ! $model->es_borrador
                ? 'campaign_published' : 'campaign_updated';
        } elseif (($model->wasChanged('estado') && $model->estado === 'publicado')
            || ($model->wasChanged('publication_status') && $model->publication_status === 'published')
            || ($model->wasChanged('published_at') && $model->published_at !== null)) {
            $action = 'task_published';
        } elseif ($model->wasChanged('estado')) {
            $action = 'task_status_changed';
        } else {
            $action = 'task_updated';
        }

        $this->record($model, $action, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, $model instanceof Campania ? 'campaign_deleted' : 'task_deleted', []);
    }

    public function designersChanged(Campania $campaign, array $before, array $after): void
    {
        $this->recordPeopleChange($campaign, 'campaign_team_changed', 'disenadores', $before, $after);
    }

    public function assigneesChanged(Tarea $task, array $before, array $after): void
    {
        $this->recordPeopleChange($task, 'task_assignees_changed', 'responsables', $before, $after);
    }

    private function recordPeopleChange(Model $model, string $action, string $field, array $before, array $after): void
    {
        $before = array_map('intval', $before);
        $after = array_map('intval', $after);
        sort($before);
        sort($after);
        if ($before === $after) return;

        $names = User::whereIn('id', array_unique([...$before, ...$after]))->pluck('name', 'id');
        $label = fn (array $ids) => implode(', ', array_map(fn ($id) => $names[$id] ?? "Usuario #{$id}", $ids));
        $this->record($model, $action, [$field => ['from' => $label($before), 'to' => $label($after)]]);
    }

    private function record(Model $model, string $action, array $changes): void
    {
        $campaign = $model instanceof Campania ? $model : $model->campania;
        if (! $campaign) {
            return;
        }

        $user = Auth::user();
        $client = $campaign->cliente;
        CampaignActivityLog::create([
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->nombre,
            'client_id' => $campaign->usuario_cliente_id,
            'client_name' => $client?->name,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistema',
            'action' => $action,
            'task_id' => $model instanceof Tarea ? $model->id : null,
            'task_title' => $model instanceof Tarea ? $model->titulo : null,
            'changes' => $changes ?: null,
        ]);
    }

    private function displayValue(mixed $value): ?string
    {
        if ($value === null) return null;
        if (is_bool($value)) return $value ? 'Sí' : 'No';
        if (is_array($value)) return implode(', ', array_map('strval', $value));
        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d H:i:s');

        return mb_strimwidth((string) $value, 0, 180, '…');
    }
}
