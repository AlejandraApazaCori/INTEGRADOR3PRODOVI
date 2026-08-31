<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\CampaniaMensaje;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_feedback_respects_team_client_and_direct_visibility(): void
    {
        [$admin, $client, $manager, $designer, $outsider, $campaign] = $this->campaignContext();

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#feedback')
            ->assertOk()
            ->assertSee('data-campaign-tab="feedback"', false)
            ->assertSee('Centro de mensajes')
            ->assertSee('Nuevo mensaje')
            ->assertSee('Escribir al equipo')
            ->assertSee('Crear un contexto personalizado')
            ->assertDontSee('Selecciona un contexto')
            ->assertSee('<div class="feedback-conversation" data-feedback-conversation>', false)
            ->assertSee('¿Eliminar este mensaje?')
            ->assertSee('data-feedback-delete-modal', false);

        $this->actingAs($client)
            ->get(route('clientes.campanias.feedback', $campaign))
            ->assertOk()
            ->assertSee('Mensajes con tu')
            ->assertSee($campaign->nombre);

        $this->actingAs($outsider)
            ->get(route('clientes.campanias.feedback', $campaign))
            ->assertForbidden();

        $teamResponse = $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'contenido' => 'Mensaje interno para producción.',
        ])->assertCreated();

        $teamMessage = CampaniaMensaje::findOrFail($teamResponse->json('id'));
        $this->assertFalse($teamMessage->destinatarios()->whereKey($client->id)->exists());
        $this->assertTrue($teamMessage->destinatarios()->whereKey($manager->id)->exists());
        $this->assertTrue($teamMessage->destinatarios()->whereKey($designer->id)->exists());

        $this->actingAs($client)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'contenido' => 'Este canal no está permitido para el cliente.',
        ])->assertUnprocessable();

        $clientResponse = $this->actingAs($client)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'cliente_equipo',
            'contenido' => 'Necesito confirmar el texto de la publicación.',
        ])->assertCreated();

        $directResponse = $this->actingAs($manager)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'directo',
            'destinatario_id' => $designer->id,
            'contenido' => 'Ajusta el archivo antes de compartirlo.',
        ])->assertCreated();

        $this->actingAs($client)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'filtro' => 'todos']))
            ->assertOk()
            ->assertSee('Necesito confirmar el texto')
            ->assertDontSee('Mensaje interno para producción')
            ->assertDontSee('Ajusta el archivo');

        $this->actingAs($designer)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'filtro' => 'mios']))
            ->assertOk()
            ->assertSee('Ajusta el archivo');

        $directMessage = CampaniaMensaje::findOrFail($directResponse->json('id'));
        $this->assertNotNull(
            $directMessage->destinatarios()->whereKey($designer->id)->first()?->pivot?->leido_at
        );
    }

    public function test_only_sender_or_administrator_can_delete_a_message(): void
    {
        [$admin, $client, $manager, $designer, , $campaign] = $this->campaignContext();

        $response = $this->actingAs($manager)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'directo',
            'destinatario_id' => $designer->id,
            'contenido' => 'Mensaje privado.',
        ])->assertCreated();

        $message = CampaniaMensaje::findOrFail($response->json('id'));

        $this->actingAs($client)
            ->deleteJson(route('campanias.mensajes.destroy', [$campaign, $message]))
            ->assertForbidden();

        $this->actingAs($manager)
            ->deleteJson(route('campanias.mensajes.destroy', [$campaign, $message]))
            ->assertOk();

        $this->assertSoftDeleted('campania_mensajes', ['id' => $message->id]);
    }

    public function test_messages_support_safe_formatting_links_and_pasted_images(): void
    {
        Storage::fake('public');
        [$admin, , , , , $campaign] = $this->campaignContext();

        $response = $this->actingAs($admin)->post(
            route('campanias.mensajes.store', $campaign),
            [
                'audiencia' => 'equipo',
                'contenido' => '**Importante:** revisa [el documento](https://example.com/documento).',
                'imagenes' => [UploadedFile::fake()->image('referencia.png', 900, 600)],
            ],
            ['Accept' => 'application/json']
        )->assertCreated();

        $message = CampaniaMensaje::with('imagenes')->findOrFail($response->json('id'));
        $this->assertCount(1, $message->imagenes);
        Storage::disk('public')->assertExists($message->imagenes->first()->ruta_archivo);

        $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'contenido' => "**Negrilla con espacio final **texto normal\nSegunda línea",
        ])->assertCreated();

        $feed = $this->actingAs($admin)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'filtro' => 'todos']))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('<strong>Importante:</strong>', $feed);
        $this->assertStringContainsString('<strong>Negrilla con espacio final</strong> texto normal', $feed);
        $this->assertStringContainsString("texto normal\nSegunda línea", $feed);
        $this->assertStringContainsString('href="https://example.com/documento"', $feed);
        $this->assertStringContainsString('referencia.png', $feed);
    }

    public function test_messages_are_loaded_only_inside_the_selected_context(): void
    {
        [$admin, , $manager, , , $campaign] = $this->campaignContext();
        $task = Tarea::create([
            'titulo' => 'Diseñar carrusel',
            'descripcion' => 'Preparar las piezas de la campaña.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);
        $emptyTask = Tarea::create([
            'titulo' => 'Tarea sin conversación',
            'descripcion' => 'Todavía no tiene mensajes.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);

        $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'contenido' => 'Mensaje del contexto general.',
        ])->assertCreated();
        $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'tarea_id' => $task->id,
            'contenido' => 'Mensaje exclusivo de la tarea.',
        ])->assertCreated();

        $this->actingAs($admin)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'contexto' => 'general']))
            ->assertOk()->assertSee('Mensaje del contexto general')->assertDontSee('Mensaje exclusivo de la tarea');
        $this->actingAs($admin)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'contexto' => (string) $task->id]))
            ->assertOk()->assertSee('Mensaje exclusivo de la tarea')->assertDontSee('Mensaje del contexto general');

        $contextsHtml = $this->actingAs($admin)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'vista' => 'contextos', 'filtro' => 'todos']))
            ->assertOk()->json('html');
        $this->assertStringContainsString('Campaña general', $contextsHtml);
        $this->assertStringContainsString($task->titulo, $contextsHtml);
        $this->assertStringNotContainsString($emptyTask->titulo, $contextsHtml);
        $emptyContextsHtml = $this->actingAs($admin)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'vista' => 'contextos', 'filtro' => 'cliente']))
            ->assertOk()->json('html');
        $this->assertStringContainsString('No hay conversaciones en esta subpestaña', $emptyContextsHtml);
        $this->assertStringNotContainsString($task->titulo, $emptyContextsHtml);
    }

    public function test_only_the_sender_can_edit_a_message(): void
    {
        [, , $manager, $designer, , $campaign] = $this->campaignContext();
        $response = $this->actingAs($manager)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'directo',
            'destinatario_id' => $designer->id,
            'contenido' => 'Texto original.',
        ])->assertCreated();
        $message = CampaniaMensaje::findOrFail($response->json('id'));

        $this->actingAs($designer)
            ->patchJson(route('campanias.mensajes.update', [$campaign, $message]), ['contenido' => 'Cambio no autorizado.'])
            ->assertForbidden();
        $this->actingAs($manager)
            ->patchJson(route('campanias.mensajes.update', [$campaign, $message]), ['contenido' => '**Texto corregido.**'])
            ->assertOk();

        $this->assertSame('**Texto corregido.**', $message->fresh()->contenido);
    }

    public function test_a_reply_keeps_the_parent_context_and_is_rendered_as_a_thread(): void
    {
        [$admin, , $manager, , , $campaign] = $this->campaignContext();
        $task = Tarea::create([
            'titulo' => 'Preparar reel',
            'descripcion' => 'Crear el contenido audiovisual.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);
        $parentResponse = $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'cliente_equipo',
            'tarea_id' => $task->id,
            'contenido' => '¿Está lista la primera versión?',
        ])->assertCreated();
        $parent = CampaniaMensaje::findOrFail($parentResponse->json('id'));

        $replyResponse = $this->actingAs($manager)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'cliente_equipo',
            'mensaje_padre_id' => $parent->id,
            'contenido' => 'Sí, ya está disponible.',
        ])->assertCreated();
        $reply = CampaniaMensaje::findOrFail($replyResponse->json('id'));

        $this->assertSame($parent->id, $reply->mensaje_padre_id);
        $this->assertSame($task->id, $reply->tarea_id);
        $this->assertSame($parent->audiencia, $reply->audiencia);
        $html = $this->actingAs($manager)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'contexto' => (string) $task->id]))
            ->assertOk()->assertSee('En respuesta a Administrador')->json('html');
        $this->assertStringContainsString('Sí, ya está disponible.', $html);
    }

    public function test_a_user_can_create_and_use_a_custom_message_context(): void
    {
        [$admin, , $manager, , , $campaign] = $this->campaignContext();
        $task = Tarea::create([
            'titulo' => 'Tarea auxiliar',
            'descripcion' => 'No debe mezclarse con el contexto personalizado.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);

        $contextResponse = $this->actingAs($manager)
            ->postJson(route('campanias.mensajes.contextos.store', $campaign), ['nombre' => '  Lanzamiento especial  '])
            ->assertCreated()
            ->assertJsonPath('nombre', 'Lanzamiento especial');
        $contextId = $contextResponse->json('id');

        $this->actingAs($manager)
            ->postJson(route('campanias.mensajes.contextos.store', $campaign), ['nombre' => 'Lanzamiento especial'])
            ->assertUnprocessable();

        $messageResponse = $this->actingAs($manager)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'equipo',
            'tarea_id' => $task->id,
            'contexto_id' => $contextId,
            'contenido' => 'Mensaje del contexto creado por el usuario.',
        ])->assertCreated();
        $message = CampaniaMensaje::findOrFail($messageResponse->json('id'));
        $this->assertSame($contextId, $message->contexto_id);
        $this->assertNull($message->tarea_id);

        $contextsHtml = $this->actingAs($manager)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'vista' => 'contextos', 'filtro' => 'todos']))
            ->assertOk()->json('html');
        $this->assertStringContainsString('Lanzamiento especial', $contextsHtml);
        $this->actingAs($manager)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'contexto' => 'custom:'.$contextId]))
            ->assertOk()->assertSee('Mensaje del contexto creado por el usuario.');
    }

    public function test_client_chat_badge_counts_only_unread_campaign_messages(): void
    {
        [$admin, $client, , , , $campaign] = $this->campaignContext();
        $this->actingAs($admin)->postJson(route('campanias.mensajes.store', $campaign), [
            'audiencia' => 'cliente_equipo',
            'contenido' => 'Nuevo mensaje pendiente de lectura.',
        ])->assertCreated();

        $this->actingAs($client)
            ->getJson(route('clientes.mensajes.no-leidos', ['campania' => $campaign->id]))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('url', route('clientes.campanias.feedback', $campaign));
        $this->actingAs($client)
            ->get(route('clientes.campanias.feedback', $campaign))
            ->assertOk()
            ->assertSee('data-client-message-button', false)
            ->assertSee('data-client-message-badge', false);

        $this->actingAs($client)
            ->getJson(route('campanias.mensajes.index', [$campaign, 'contexto' => 'general']))
            ->assertOk();
        $this->actingAs($client)
            ->getJson(route('clientes.mensajes.no-leidos', ['campania' => $campaign->id]))
            ->assertOk()
            ->assertJsonPath('count', 0);
    }

    private function campaignContext(): array
    {
        $admin = User::factory()->create(['name' => 'Administrador']);
        $client = User::factory()->create(['name' => 'Cliente']);
        $manager = User::factory()->create(['name' => 'Community Manager']);
        $designer = User::factory()->create(['name' => 'Diseñadora']);
        $outsider = User::factory()->create(['name' => 'Usuario externo']);
        $plan = Plan::create([
            'nombre' => 'Plan mensual',
            'subtitulo' => 'Marketing digital',
            'precio' => 100,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
        ]);
        $subscription = Suscripcion::create([
            'usuario_id' => $client->id,
            'plan_id' => $plan->id,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'vigencia_activada_at' => now(),
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña de mensajería',
            'descripcion' => 'Campaña para probar feedback.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'disenador_id' => $designer->id,
            'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);
        $campaign->disenadores()->attach($designer->id);

        return [$admin, $client, $manager, $designer, $outsider, $campaign];
    }
}
