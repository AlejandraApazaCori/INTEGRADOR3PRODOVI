<?php

namespace Tests\Feature;

use App\Mail\ConfirmacionPago;
use App\Mail\ConfirmacionPagoQr;
use App\Models\ComprobantePago;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\PaymentConfirmationNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_qr_payment_sends_its_own_email_with_backup_receipt(): void
    {
        Mail::fake();

        $payment = $this->createPayment('qr');
        $notifier = app(PaymentConfirmationNotifier::class);

        $this->assertTrue($notifier->send($payment));
        $this->assertFalse($notifier->send($payment->fresh()));

        Mail::assertSent(ConfirmacionPagoQr::class, function (ConfirmacionPagoQr $mail) use ($payment) {
            return $mail->hasTo($payment->usuario->email)
                && $mail->envelope()->subject === 'Pago QR confirmado: comprobante de respaldo PRODOVI'
                && $mail->content()->view === 'emails.confirmacion-pago'
                && count($mail->attachments()) === 1;
        });
        Mail::assertSentCount(1);
    }

    public function test_confirmed_physical_payment_keeps_its_existing_email(): void
    {
        Mail::fake();

        $payment = $this->createPayment('fisico');

        $this->assertTrue(app(PaymentConfirmationNotifier::class)->send($payment));

        Mail::assertSent(ConfirmacionPago::class, fn (ConfirmacionPago $mail) =>
            get_class($mail) === ConfirmacionPago::class
            && $mail->hasTo($payment->usuario->email)
            && count($mail->attachments()) === 1
        );
        Mail::assertNotSent(ConfirmacionPagoQr::class);
    }

    private function createPayment(string $method): Pago
    {
        $client = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Marketing Pro',
            'subtitulo' => 'Marketing Pro',
            'precio' => 250,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
            'activo' => true,
        ]);
        $subscription = Suscripcion::create([
            'usuario_id' => $client->id,
            'plan_id' => $plan->id,
            'estado' => 'activa',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonth(),
            'metodo_pago' => $method,
        ]);
        $payment = Pago::create([
            'usuario_id' => $client->id,
            'suscripcion_id' => $subscription->id,
            'plan_id' => $plan->id,
            'monto' => 250,
            'moneda' => 'BS',
            'metodo' => $method,
            'estado' => 'completado',
            'fecha_pago' => now(),
        ]);
        ComprobantePago::create(['pago_id' => $payment->id]);

        return $payment;
    }
}
