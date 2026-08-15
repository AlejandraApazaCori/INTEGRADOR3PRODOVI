<?php

namespace Tests\Feature;

use App\Mail\SolicitudContactoConfirmacion;
use App\Models\SolicitudContacto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LandingContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.turnstile.secret', 'turnstile-test-secret');
        config()->set('services.turnstile.hostnames', ['prodovidigital.com']);
        config()->set('services.turnstile.action', 'landing_contacto');

        Http::fake(function (Request $request) {
            $isValid = $request['response'] !== 'invalid-turnstile-token';

            return Http::response([
                'success' => $isValid,
                'hostname' => 'prodovidigital.com',
                'action' => 'landing_contacto',
                'error-codes' => $isValid ? [] : ['invalid-input-response'],
            ]);
        });
    }

    public function test_a_landing_contact_request_is_stored_and_emailed(): void
    {
        Mail::fake();

        $response = $this->post(route('landing.contacto.store'), [
            'nombre' => 'Cliente de prueba',
            'correo' => 'cliente@example.com',
            'telefono' => '79561365',
            'servicio' => 'publicidad',
            'mensaje' => 'Necesito una campaña para presentar mi nueva marca.',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertRedirect('/#contact');
        $response->assertSessionHas('contact_success');

        $this->assertDatabaseHas('solicitudes_contacto', [
            'nombre' => 'Cliente de prueba',
            'correo' => 'cliente@example.com',
            'telefono' => '79561365',
            'servicio' => 'publicidad',
        ]);

        Mail::assertSent(SolicitudContactoConfirmacion::class, function ($mail) {
            return $mail->hasTo('cliente@example.com')
                && $mail->hasReplyTo(config('mail.from.address'));
        });

        Mail::assertSentCount(1);

        $html = (new SolicitudContactoConfirmacion(SolicitudContacto::firstOrFail()))->render();

        $this->assertStringContainsString('¡Gracias por hablarnos de tu proyecto!', $html);
        $this->assertStringContainsString('Un encargado se comunicará contigo muy pronto', $html);
    }

    public function test_invalid_contact_data_is_rejected(): void
    {
        Mail::fake();

        $response = $this->from('/#contact')->post(route('landing.contacto.store'), [
            'nombre' => '',
            'correo' => 'correo-invalido',
            'telefono' => 'abc',
            'servicio' => 'servicio-inexistente',
            'mensaje' => 'corto',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertRedirect(url('/').'#contact');
        $response->assertSessionHasErrors([
            'nombre',
            'correo',
            'telefono',
            'servicio',
            'mensaje',
        ]);

        $this->assertDatabaseCount('solicitudes_contacto', 0);
        Mail::assertNothingSent();
    }

    public function test_ajax_contact_request_returns_a_success_message_without_redirecting(): void
    {
        Mail::fake();

        $response = $this->postJson(route('landing.contacto.store'), [
            'nombre' => 'Cliente AJAX',
            'correo' => 'ajax@example.com',
            'telefono' => '76543210',
            'servicio' => 'social',
            'mensaje' => 'Quiero mejorar la presencia de mi marca en redes sociales.',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'status' => 'success',
                'message' => '¡Gracias! Recibimos tu solicitud y nos pondremos en contacto contigo pronto.',
            ]);

        $this->assertDatabaseHas('solicitudes_contacto', [
            'correo' => 'ajax@example.com',
            'servicio' => 'social',
        ]);
    }

    public function test_an_invalid_turnstile_token_blocks_the_contact_request(): void
    {
        Mail::fake();

        $response = $this->postJson(route('landing.contacto.store'), [
            'nombre' => 'Bot de prueba',
            'correo' => 'bot@example.com',
            'telefono' => '76543210',
            'servicio' => 'social',
            'mensaje' => 'Este envío no debe guardarse ni enviar un correo.',
            'cf-turnstile-response' => 'invalid-turnstile-token',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cf-turnstile-response');

        $this->assertDatabaseCount('solicitudes_contacto', 0);
        Mail::assertNothingSent();
    }
}
