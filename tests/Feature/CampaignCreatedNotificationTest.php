<?php

namespace Tests\Feature;

use App\Mail\CampaniaCreada;
use App\Models\Campania;
use App\Models\User;
use App\Services\CampaignCreatedNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CampaignCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_campaign_notifies_the_client_by_email_and_navbar(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $manager = User::factory()->create();
        $client = User::factory()->create();
        $campaign = Campania::create([
            'nombre' => 'Lanzamiento septiembre',
            'descripcion' => 'Campaña activa para el cliente.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
        ]);

        app(CampaignCreatedNotifier::class)->send($campaign);
        app(CampaignCreatedNotifier::class)->send($campaign);

        Mail::assertSent(CampaniaCreada::class, fn ($mail) =>
            $mail->hasTo($client->email) && $mail->campania->is($campaign)
        );
        Mail::assertSentCount(1);
        $this->assertCount(1, $client->fresh()->notifications);

        $notification = $client->fresh()->notifications->first();
        $this->actingAs($client)
            ->get(route('clientes.notificaciones.show', $notification->id))
            ->assertRedirect(route('clientes.dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
