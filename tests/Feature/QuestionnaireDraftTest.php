<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\PreguntaCuestionario;
use App\Models\RespuestaCuestionario;
use App\Models\TemaCuestionario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_answers_are_saved_without_completing_and_can_be_cleared(): void
    {
        [$user, $company, $question] = $this->questionnaire();
        $url = route('empresas.cuestionario.draft', $company);
        $this->actingAs($user)->postJson($url, ["respuesta_{$question->id}" => 'A medias'])
            ->assertOk()->assertJson(['saved' => true]);
        $this->assertFalse((bool) $company->fresh()->cuestionario_completado);
        $this->assertDatabaseHas('respuestas_cuestionario', ['empresa_id' => $company->id, 'respuesta' => 'A medias', 'deleted_at' => null]);
        $this->get(route('empresas.cuestionario', $company))->assertOk()->assertSee('A medias');
        $this->postJson($url, ["respuesta_{$question->id}" => ''])->assertOk();
        $this->assertSame(0, RespuestaCuestionario::where('empresa_id', $company->id)->count());
    }

    public function test_drafts_respect_ownership_and_cannot_overwrite_a_completed_submission(): void
    {
        [$user, $company, $question, $required] = $this->questionnaire();
        $url = route('empresas.cuestionario.draft', $company);
        $this->actingAs(User::factory()->create())->postJson($url, ["respuesta_{$question->id}" => 'Ajena'])->assertNotFound();
        $this->actingAs($user)->postJson(route('empresas.cuestionario.store', $company), ["respuesta_{$question->id}" => 'Parcial'])->assertUnprocessable();
        $this->post(route('empresas.cuestionario.store', $company), [
            "respuesta_{$question->id}" => 'Final', "respuesta_{$required->id}" => 'Completa',
        ])->assertRedirect();
        $this->assertTrue((bool) $company->fresh()->cuestionario_completado);
        $this->postJson($url, ["respuesta_{$question->id}" => 'Tardía'])->assertConflict();
        $this->assertDatabaseHas('respuestas_cuestionario', ['empresa_id' => $company->id, 'pregunta_id' => $question->id, 'respuesta' => 'Final']);
    }

    public function test_checkbox_other_and_deselection_are_saved(): void
    {
        [$user, $company, $question] = $this->questionnaire();
        $question->update(['tipo_respuesta' => 'checkbox', 'opciones' => ['Uno', 'Otro']]);
        $url = route('empresas.cuestionario.draft', $company);
        $this->actingAs($user)->postJson($url, ["respuesta_{$question->id}" => ['Uno', 'Otro'], "respuesta_{$question->id}_otro" => 'Detalle'])->assertOk();
        $this->assertDatabaseHas('respuestas_cuestionario', ['empresa_id' => $company->id, 'respuesta' => 'Uno | Otro: Detalle']);
        $this->postJson($url, ["respuesta_{$question->id}" => []])->assertOk();
        $this->assertSame(0, RespuestaCuestionario::where('empresa_id', $company->id)->count());
    }

    private function questionnaire(): array
    {
        $user = User::factory()->create();
        $company = Empresa::create(['usuario_id' => $user->id, 'nombre_empresa' => 'Prueba', 'tipo_empresa' => 'Servicios']);
        $topic = TemaCuestionario::create(['nombre_tema' => 'Marca']);
        $question = PreguntaCuestionario::create(['tema_id' => $topic->id, 'pregunta' => 'Nombre', 'tipo_respuesta' => 'texto', 'requerido' => true]);
        $required = PreguntaCuestionario::create(['tema_id' => $topic->id, 'pregunta' => 'Descripción', 'tipo_respuesta' => 'texto', 'requerido' => true]);
        return [$user, $company, $question, $required];
    }
}
