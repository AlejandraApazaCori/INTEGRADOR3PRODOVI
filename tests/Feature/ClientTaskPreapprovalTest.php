<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\User;
use App\Notifications\TaskReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTaskPreapprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_review_then_client_approval_unlocks_publication_and_records_feedback(): void
    {
        [$assignee, $client, $company, $campaign, $task, $file] = $this->context();

        $this->actingAs($assignee)->patch(route('administrador.tareas.revision-cliente', $task), [
            'revision_cliente_habilitada' => 1,
        ])->assertRedirect();

        $this->actingAs($client)->post(route('clientes.tareas.archivos.revision', $file), [
            'estado' => 'aprobado',
        ])->assertStatus(409);

        $this->actingAs($assignee)->put(route('administrador.tareas.archivos.update-estado', $file), [
            'estado' => 'aprobado',
        ])->assertRedirect();

        $this->assertSame('entregado', $task->fresh()->estado);
        $this->assertDatabaseHas('notifications', [
            'type' => TaskReviewNotification::class,
            'notifiable_id' => $client->id,
        ]);
        $this->actingAs($assignee)->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task));

        $this->actingAs($client)->get(route('empresas.campanias', $company))->assertOk()->assertSee($campaign->nombre);
        $this->get(route('empresas.campanias.show', [$company, $campaign]))->assertOk()->assertSee($file->nombre_original);

        $this->post(route('clientes.tareas.archivos.revision', $file), [
            'estado' => 'aprobado',
            'comentario' => 'La pieza está lista.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tarea_archivos', [
            'id' => $file->id,
            'estado' => 'aprobado',
            'revision_cliente_estado' => 'aprobado',
            'revisado_cliente_por' => $client->id,
        ]);
        $this->assertDatabaseHas('tarea_comentarios', [
            'comentable_id' => $task->id,
            'comentable_type' => Tarea::class,
            'user_id' => $client->id,
            'contenido' => 'La pieza está lista.',
        ]);
        $this->assertSame('aprobado', $task->fresh()->estado);
        $this->assertDatabaseHas('notifications', [
            'type' => TaskReviewNotification::class,
            'notifiable_id' => $assignee->id,
        ]);
        $this->actingAs($assignee)->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))->assertOk();
    }

    public function test_client_rejection_keeps_internal_approval_but_blocks_publication(): void
    {
        [$assignee, $client, , , $task, $file] = $this->context();
        $task->update(['revision_cliente_habilitada' => true]);
        $this->actingAs($assignee)->put(route('administrador.tareas.archivos.update-estado', $file), [
            'estado' => 'aprobado',
        ])->assertRedirect();

        $this->actingAs($client)->post(route('clientes.tareas.archivos.revision', $file), [
            'estado' => 'rechazado',
            'comentario' => 'Cambiar el color de fondo.',
        ])->assertRedirect();

        $this->assertSame('aprobado', $file->fresh()->estado);
        $this->assertSame('rechazado', $file->fresh()->revision_cliente_estado);
        $this->assertSame('reformular', $task->fresh()->estado);
        $this->actingAs($assignee)->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task));
    }

    public function test_review_permissions_notifications_and_scheduled_publication_are_respected(): void
    {
        [$assignee, $client, $company, $campaign, $task, $file] = $this->context();
        $otherAdmin = User::factory()->create();
        $otherAdmin->roles()->attach(Role::where('nombre_rol', 'Administrador')->firstOrFail());
        $otherClient = User::factory()->create();

        $this->actingAs($otherAdmin)->put(route('administrador.tareas.archivos.update-estado', $file), [
            'estado' => 'aprobado',
        ])->assertForbidden();
        $this->actingAs($otherClient)->get(route('empresas.campanias.show', [$company, $campaign]))->assertNotFound();
        $this->post(route('clientes.tareas.archivos.revision', $file), ['estado' => 'aprobado'])->assertForbidden();

        $task->update(['publication_status' => 'scheduled', 'publication_scheduled_at' => now()->addDay()]);
        $this->actingAs($assignee)->patch(route('administrador.tareas.revision-cliente', $task), [
            'revision_cliente_habilitada' => 1,
        ])->assertRedirect();
        $this->assertSame('failed', $task->fresh()->publication_status);

        $this->put(route('administrador.tareas.archivos.update-estado', $file), [
            'estado' => 'aprobado',
        ])->assertRedirect();
        $notification = $client->notifications()->where('type', TaskReviewNotification::class)->firstOrFail();
        $this->actingAs($client)->get(route('clientes.notificaciones.show', $notification->id))
            ->assertRedirect(route('empresas.campanias.show', [$company, $campaign]).'#revision-archivo-'.$file->id);
    }

    public function test_all_approved_deliverables_wait_for_client_decision(): void
    {
        [$assignee, $client, , , $task, $firstFile] = $this->context();
        $secondFile = $firstFile->replicate();
        $secondFile->nombre_original = 'segunda.png';
        $secondFile->save();
        $task->update(['revision_cliente_habilitada' => true]);

        $this->actingAs($assignee)->put(route('administrador.tareas.archivos.update-estado', $firstFile), ['estado' => 'aprobado'])->assertRedirect();
        $this->put(route('administrador.tareas.archivos.update-estado', $secondFile), ['estado' => 'aprobado'])->assertRedirect();
        $this->actingAs($client)->post(route('clientes.tareas.archivos.revision', $firstFile), ['estado' => 'aprobado'])->assertRedirect();

        $this->assertSame('entregado', $task->fresh()->estado);
        $this->actingAs($assignee)->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task));

        $this->actingAs($client)->post(route('clientes.tareas.archivos.revision', $secondFile), ['estado' => 'aprobado'])->assertRedirect();
        $this->assertSame('aprobado', $task->fresh()->estado);
    }

    private function context(): array
    {
        $role = Role::create(['nombre_rol' => 'Administrador']);
        $assignee = User::factory()->create();
        $assignee->roles()->attach($role);
        $client = User::factory()->create();
        $company = Empresa::create([
            'usuario_id' => $client->id,
            'nombre_empresa' => 'Empresa de prueba',
            'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña para revisión',
            'descripcion' => 'Campaña de prueba',
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->endOfMonth(),
            'estado' => 'activa',
            'usuario_creador_id' => $assignee->id,
            'community_manager_id' => $assignee->id,
            'usuario_cliente_id' => $client->id,
        ]);
        $campaign->empresas()->attach($company);
        $task = Tarea::create([
            'titulo' => 'Publicación de prueba',
            'descripcion' => 'Contenido de prueba',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'estado' => 'entregado',
            'prioridad' => 'media',
            'campania_id' => $campaign->id,
            'creador_id' => $assignee->id,
            'asignado_id' => $assignee->id,
        ]);
        $file = TareaArchivo::create([
            'tarea_id' => $task->id,
            'user_id' => $assignee->id,
            'nombre_original' => 'publicacion.png',
            'ruta_archivo' => 'tareas/publicacion.png',
            'extension' => 'png',
            'mime_type' => 'image/png',
            'tamanio' => 1024,
        ]);

        return [$assignee, $client, $company, $campaign, $task, $file];
    }
}
