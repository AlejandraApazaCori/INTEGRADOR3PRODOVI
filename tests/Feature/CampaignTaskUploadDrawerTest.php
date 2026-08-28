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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignTaskUploadDrawerTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_content_can_be_uploaded_from_campaign_side_drawer(): void
    {
        Storage::fake('public');
        [$admin, $campaign, $task] = $this->campaignWithTask();

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#tareas')
            ->assertOk()
            ->assertSee('data-open-task-upload', false)
            ->assertSee('task-upload-drawer', false)
            ->assertSee('Subir contenido');

        $response = $this->actingAs($admin)->post(
            route('administrador.tareas.archivos.store', $task),
            [
                'contexto' => 'campania',
                'archivos' => [UploadedFile::fake()->create('contenido.pdf', 120, 'application/pdf')],
                'descripcion' => 'Entregable desde el panel lateral',
            ]
        );

        $response->assertRedirect(route('administrador.campañas.show', $campaign).'#tareas');
        $this->assertDatabaseHas('tarea_archivos', [
            'tarea_id' => $task->id,
            'nombre_original' => 'contenido.pdf',
            'descripcion' => 'Entregable desde el panel lateral',
        ]);
        $storedFile = $task->archivos()->firstOrFail();
        Storage::disk('public')->assertExists($storedFile->ruta_archivo);
    }

    public function test_upload_errors_return_to_tasks_and_reopen_the_drawer(): void
    {
        [$admin, $campaign, $task] = $this->campaignWithTask();

        $response = $this->actingAs($admin)->post(
            route('administrador.tareas.archivos.store', $task),
            ['contexto' => 'campania']
        );

        $response
            ->assertRedirect(route('administrador.campañas.show', $campaign).'#tareas')
            ->assertSessionHasErrors('archivos', null, 'taskUpload');

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#tareas')
            ->assertOk()
            ->assertSee('data-open-on-load="true"', false)
            ->assertSee($task->titulo);
    }

    public function test_review_page_uses_the_upload_drawer_and_returns_there_after_uploading(): void
    {
        Storage::fake('public');
        [$admin, $campaign, $task] = $this->campaignWithTask();

        $this->actingAs($admin)
            ->get(route('administrador.tareas.archivos.create', $task))
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task).'?subir=1');

        $this->actingAs($admin)
            ->get(route('administrador.tareas.ver-subidas', $task).'?subir=1')
            ->assertOk()
            ->assertSee('review-upload-drawer', false)
            ->assertSee('data-open-on-load="true"', false);

        $this->actingAs($admin)
            ->post(route('administrador.tareas.archivos.store', $task), [
                'contexto' => 'revision',
                'archivos' => [UploadedFile::fake()->create('revision.pdf', 120, 'application/pdf')],
            ])
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task));

        $this->assertDatabaseHas('tarea_archivos', [
            'tarea_id' => $task->id,
            'nombre_original' => 'revision.pdf',
        ]);
    }

    public function test_publish_action_is_prominent_on_a_card_with_approved_content(): void
    {
        [$admin, $campaign, $task] = $this->campaignWithTask();
        TareaArchivo::create([
            'tarea_id' => $task->id,
            'user_id' => $admin->id,
            'nombre_original' => 'pieza-aprobada.png',
            'ruta_archivo' => 'tareas/archivos/pieza-aprobada.png',
            'extension' => 'png',
            'mime_type' => 'image/png',
            'tamanio' => 1024,
            'estado' => 'aprobado',
        ]);

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign).'#tareas')
            ->assertOk()
            ->assertSee('task-card-publish', false)
            ->assertSee('Publicar contenido');
    }

    public function test_review_upload_errors_reopen_its_drawer(): void
    {
        [$admin, $campaign, $task] = $this->campaignWithTask();

        $this->actingAs($admin)
            ->post(route('administrador.tareas.archivos.store', $task), ['contexto' => 'revision'])
            ->assertRedirect(route('administrador.tareas.ver-subidas', $task))
            ->assertSessionHasErrors('archivos', null, 'reviewUpload');

        $this->actingAs($admin)
            ->get(route('administrador.tareas.ver-subidas', $task))
            ->assertOk()
            ->assertSee('data-open-on-load="true"', false);
    }

    public function test_images_and_videos_have_a_popup_preview_on_the_review_page(): void
    {
        [$admin, $campaign, $task] = $this->campaignWithTask();
        foreach ([
            ['publicacion.png', 'png', 'image/png'],
            ['video.mp4', 'mp4', 'video/mp4'],
        ] as [$name, $extension, $mimeType]) {
            TareaArchivo::create([
                'tarea_id' => $task->id,
                'user_id' => $admin->id,
                'nombre_original' => $name,
                'ruta_archivo' => 'tareas/archivos/'.$name,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'tamanio' => 1024,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('administrador.tareas.ver-subidas', $task))
            ->assertOk()
            ->assertSee('review-preview-modal', false)
            ->assertSee('data-preview-type="image"', false)
            ->assertSee('data-preview-type="video"', false)
            ->assertSee('data-open-file-preview', false);
    }

    public function test_publishing_screen_uses_the_prodovi_workflow_layout(): void
    {
        [$admin, $campaign, $task] = $this->campaignWithTask();
        $task->update(['tipo_contenido' => 'carrusel']);

        $this->actingAs($admin)
            ->get(route('administrador.publicaciones.publicar', ['tarea_id' => $task->id]))
            ->assertOk()
            ->assertSee('Preparar publicación')
            ->assertSee('publication-step-platforms', false)
            ->assertSee('publication-preview-column', false)
            ->assertSee('const noPlatformSelected = !facebookChecked && !instagramChecked;', false)
            ->assertSee('linear-gradient(135deg,#ef6c22,#c94f0c)', false)
            ->assertSee('publication-meta-modal', false)
            ->assertSee('data-auto-open="true"', false)
            ->assertSee('No se puede publicar todavía')
            ->assertSee('Formato')
            ->assertSee('Carrusel')
            ->assertSee('.publication-step-platforms{background:#fff}', false)
            ->assertSee('.publication-step-platforms>label:after,.publication-step-platforms>div>label:after{display:none!important}', false)
            ->assertSee('.publication-step-platforms .rp-checkbox-pill input:checked', false);
    }

    public function test_campaign_detail_opens_summary_and_separates_target_audiences(): void
    {
        [$admin, $campaign] = $this->campaignWithTask();
        $campaign->update([
            'publico_objetivo' => "Adultos planificadores (35-55 años): Buscan organizar sus compras con anticipación.\nJóvenes digitales (20-34 años): Valoran contenido visual y respuestas rápidas.",
        ]);

        $this->actingAs($admin)
            ->get(route('administrador.campañas.show', $campaign))
            ->assertOk()
            ->assertSee('campaign-subtab is-active', false)
            ->assertSee('data-campaign-panel="documents" role="tabpanel" aria-labelledby="campaign-tab-documents" hidden', false)
            ->assertSee('data-campaign-panel="summary" role="tabpanel" aria-labelledby="campaign-tab-summary">', false)
            ->assertSee("initialTabs[window.location.hash] || 'summary'", false)
            ->assertSee('campaign-audiences', false)
            ->assertSee('id="campaign-audiences-title" class="campaign-underlined-title"', false)
            ->assertSee('background:#5b2b76', false)
            ->assertDontSee('Segmentación estratégica')
            ->assertDontSee('Cada segmento muestra por separado')
            ->assertSee('Adultos planificadores (35-55 años)')
            ->assertSee('Jóvenes digitales (20-34 años)')
            ->assertSee('Buscan organizar sus compras con anticipación.')
            ->assertSee('Valoran contenido visual y respuestas rápidas.');
    }

    private function campaignWithTask(): array
    {
        $admin = User::factory()->create();
        $client = User::factory()->create();
        $manager = User::factory()->create();
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
        Empresa::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $subscription->id,
            'nombre_empresa' => 'Empresa con tareas',
            'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña operativa',
            'descripcion' => 'Campaña para subir contenidos',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $manager->id,
            'usuario_cliente_id' => $client->id,
            'suscripcion_id' => $subscription->id,
        ]);
        $task = Tarea::create([
            'titulo' => 'Diseñar publicación principal',
            'descripcion' => 'Preparar el contenido final',
            'fecha_inicio' => now()->toDateString(),
            'fecha_limite' => now()->addWeek()->toDateString(),
            'prioridad' => 'media',
            'estado' => 'pendiente',
            'campania_id' => $campaign->id,
            'creador_id' => $admin->id,
            'asignado_id' => $manager->id,
        ]);

        return [$admin, $campaign, $task];
    }
}
