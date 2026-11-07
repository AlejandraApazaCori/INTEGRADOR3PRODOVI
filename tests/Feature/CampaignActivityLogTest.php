<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\CampaignActivityLog;
use App\Models\Role;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_and_task_changes_keep_actor_and_filter_by_current_campaign(): void
    {
        $admin = User::factory()->create(['name' => 'Administradora']);
        $admin->roles()->attach(Role::create(['nombre_rol' => 'Administrador']));
        $client = User::factory()->create(['name' => 'Cliente Uno']);
        $otherClient = User::factory()->create(['name' => 'Cliente Dos']);
        $this->actingAs($admin);

        $campaign = $this->campaign($admin, $client, 'Campaña Alfa');
        $other = $this->campaign($admin, $otherClient, 'Campaña Beta');
        $campaign->update(['nombre' => 'Campaña Alfa editada']);

        $task = Tarea::create([
            'titulo' => 'Diseñar pieza', 'descripcion' => 'Contenido',
            'fecha_inicio' => now()->toDateString(), 'fecha_limite' => now()->addDay()->toDateString(),
            'prioridad' => 'media', 'campania_id' => $campaign->id,
            'creador_id' => $admin->id, 'asignado_id' => $admin->id,
        ]);
        $task->update(['estado' => 'en_curso']);
        $task->update(['estado' => 'publicado']);
        $other->update(['estado' => 'pausada']);

        $this->assertDatabaseHas('campaign_activity_logs', [
            'campaign_id' => $campaign->id, 'user_id' => $admin->id,
            'user_name' => 'Administradora', 'action' => 'campaign_created',
        ]);
        $this->assertSame('Campaña Alfa', CampaignActivityLog::where('action', 'campaign_updated')->first()->changes['nombre']['from']);
        $this->assertDatabaseHas('campaign_activity_logs', ['task_id' => $task->id, 'action' => 'task_status_changed']);
        $this->assertDatabaseHas('campaign_activity_logs', ['task_id' => $task->id, 'action' => 'task_published']);

        $this->get(route('administrador.logs.index', ['tab' => 'campaign', 'campaign_status' => 'active', 'client_id' => $client->id]))
            ->assertOk()->assertViewHas('campaignLogs', fn ($logs) => $logs->total() === 5 && $logs->every(fn ($log) => $log->campaign_id === $campaign->id));
        $this->get(route('administrador.logs.index', ['tab' => 'campaign', 'campaign_status' => 'inactive', 'event_type' => 'campaign_updated']))
            ->assertOk()->assertViewHas('campaignLogs', fn ($logs) => $logs->total() === 1 && $logs->first()->campaign_id === $other->id);
        $this->get(route('administrador.logs.index', ['tab' => 'campaign', 'campaign_id' => $campaign->id, 'event_type' => 'task_published']))
            ->assertOk()->assertViewHas('campaignLogs', fn ($logs) => $logs->total() === 1 && $logs->first()->task_id === $task->id);

        $task->delete();
        $this->assertDatabaseHas('campaign_activity_logs', ['task_id' => $task->id, 'action' => 'task_deleted']);
        $campaign->delete();
        $this->assertDatabaseHas('campaign_activity_logs', [
            'campaign_id' => $campaign->id, 'campaign_name' => 'Campaña Alfa editada',
            'client_name' => 'Cliente Uno', 'user_name' => 'Administradora', 'action' => 'campaign_deleted',
        ]);
    }

    public function test_non_administrators_cannot_read_campaign_logs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('administrador.logs.index', ['tab' => 'campaign']))
            ->assertForbidden();
    }

    private function campaign(User $admin, User $client, string $name): Campania
    {
        return Campania::create([
            'nombre' => $name, 'descripcion' => 'Descripción de prueba',
            'fecha_inicio' => now()->toDateString(), 'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa', 'usuario_creador_id' => $admin->id,
            'community_manager_id' => $admin->id, 'usuario_cliente_id' => $client->id,
        ]);
    }
}
