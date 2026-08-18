<?php

namespace App\Services;

use App\Mail\ConfirmacionPago;
use App\Models\Pago;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentConfirmationNotifier
{
    public function send(Pago $pago): bool
    {
        if ($pago->estado !== 'completado') {
            return false;
        }

        $claimed = Pago::query()
            ->whereKey($pago->id)
            ->whereNull('confirmacion_email_enviada_at')
            ->update(['confirmacion_email_enviada_at' => now()]);

        if ($claimed === 0) {
            return false;
        }

        try {
            $pago = Pago::with(['usuario', 'plan', 'suscripcion', 'comprobantePago'])->findOrFail($pago->id);

            if (! filled($pago->usuario?->email)) {
                throw new \RuntimeException('El usuario no tiene un correo válido para recibir la confirmación.');
            }

            Mail::to($pago->usuario->email)->send(new ConfirmacionPago($pago));

            return true;
        } catch (Throwable $exception) {
            Pago::query()->whereKey($pago->id)->update(['confirmacion_email_enviada_at' => null]);

            Log::error('No se pudo enviar el correo de confirmación de pago.', [
                'pago_id' => $pago->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function resend(Pago $pago): bool
    {
        if ($pago->estado !== 'completado') {
            return false;
        }

        Pago::query()->whereKey($pago->id)->update(['confirmacion_email_enviada_at' => null]);

        return $this->send($pago->fresh());
    }
}
