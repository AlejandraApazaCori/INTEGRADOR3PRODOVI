<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\Tarea;
use App\Models\TareaArchivo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstagramPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_instagram_account_can_be_selected_and_published(): void
    {
        Storage::fake('public');
        config(['app.url' => 'https://prodovi.test']);
        [$admin, $client, $empresa, $task] = $this->publishingTask();

        $client->socialAccounts()->create([
            'empresa_id' => $empresa->id,
            'provider' => 'instagram',
            'provider_user_id' => '17841458503920416',
            'username' => 'cuenta_prodovi',
            'display_name' => 'Cuenta Prodovi',
            'access_token' => 'page-access-token',
        ]);

        Storage::disk('public')->put('tareas/pieza.jpg', 'jpeg-content');
        TareaArchivo::create([
            'tarea_id' => $task->id,
            'user_id' => $admin->id,
            'nombre_original' => 'pieza.jpg',
            'ruta_archivo' => 'tareas/pieza.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'tamanio' => 12,
            'estado' => 'aprobado',
        ]);

        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/17841458503920416/media_publish')) {
                return Http::response(['id' => 'instagram-media-123']);
            }

            if ($request->method() === 'GET') {
                return Http::response(['status_code' => 'FINISHED']);
            }

            return Http::response(['id' => 'instagram-container-123']);
        });

        $this->actingAs($admin)
            ->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))
            ->assertOk()
            ->assertSee('cuenta_prodovi')
            ->assertSee('value="instagram"', false)
            ->assertDontSee('value="instagram" type="checkbox" disabled', false);

        $this->actingAs($admin)
            ->post(route('administrador.publicaciones.publicar.store'), [
                'tarea_id' => $task->id,
                'message' => 'Publicación de prueba',
                'platforms' => ['instagram'],
                'schedule_type' => 'now',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tareas', [
            'id' => $task->id,
            'estado' => 'publicado',
            'publication_status' => 'published',
            'instagram_media_id' => 'instagram-media-123',
        ]);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/17841458503920416/media')
            && $request['image_url'] === 'https://prodovi.test/storage/tareas/pieza.jpg'
            && $request['caption'] === 'Publicación de prueba');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/17841458503920416/media_publish')
            && $request['creation_id'] === 'instagram-container-123');
    }

    public function test_scheduled_publication_remembers_instagram_selection(): void
    {
        [$admin, $client, $empresa, $task] = $this->publishingTask();
        $client->socialAccounts()->create([
            'empresa_id' => $empresa->id,
            'provider' => 'instagram',
            'provider_user_id' => '17841458503920416',
            'username' => 'cuenta_prodovi',
            'access_token' => 'page-access-token',
        ]);

        $this->actingAs($admin)
            ->post(route('administrador.publicaciones.publicar.store'), [
                'tarea_id' => $task->id,
                'message' => 'Contenido programado',
                'platforms' => ['instagram'],
                'schedule_type' => 'later',
                'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('success');

        $task->refresh();
        $this->assertSame('scheduled', $task->publication_status);
        $this->assertSame(['instagram'], $task->publication_platforms);
    }

    public function test_scheduled_instagram_publication_is_processed_automatically(): void
    {
        Storage::fake('public');
        config(['app.url' => 'https://prodovi.test']);
        [$admin, $client, $empresa, $task] = $this->publishingTask();
        $client->socialAccounts()->create([
            'empresa_id' => $empresa->id,
            'provider' => 'instagram',
            'provider_user_id' => '17841458503920416',
            'username' => 'cuenta_prodovi',
            'access_token' => 'page-access-token',
        ]);
        Storage::disk('public')->put('tareas/programada.jpg', 'jpeg-content');
        TareaArchivo::create([
            'tarea_id' => $task->id,
            'user_id' => $admin->id,
            'nombre_original' => 'programada.jpg',
            'ruta_archivo' => 'tareas/programada.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'tamanio' => 12,
            'estado' => 'aprobado',
        ]);
        $task->forceFill([
            'publication_status' => 'scheduled',
            'publication_scheduled_at' => now()->subMinute(),
            'publication_message' => 'Publicación automática',
            'publication_platforms' => ['instagram'],
        ])->save();

        Http::fake(function ($request) {
            if ($request->method() === 'GET') {
                return Http::response(['status_code' => 'FINISHED']);
            }

            return str_ends_with($request->url(), '/media_publish')
                ? Http::response(['id' => 'scheduled-instagram-media'])
                : Http::response(['id' => 'scheduled-container']);
        });

        $this->artisan('publicaciones:procesar-programadas')->assertSuccessful();

        $this->assertDatabaseHas('tareas', [
            'id' => $task->id,
            'estado' => 'publicado',
            'publication_status' => 'published',
            'instagram_media_id' => 'scheduled-instagram-media',
        ]);
    }

    private function publishingTask(): array
    {
        $admin = User::factory()->create();
        $client = User::factory()->create();
        $manager = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Plan redes',
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
        $empresa = Empresa::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $subscription->id,
            'nombre_empresa' => 'Empresa Instagram',
            'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña Instagram',
            'descripcion' => 'Contenido para redes',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);
        $task = Tarea::create([
            'titulo' => 'Post para Instagram',
            'descripcion' => 'Preparar contenido',
            'tipo_contenido' => 'post',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'prioridad' => 'media',
            'estado' => 'aprobado',
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);

        return [$admin, $client, $empresa, $task];
    }
}
