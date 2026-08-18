<?php

namespace App\Services;

use App\Mail\ConfirmacionPago;
use App\Models\Pago;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PaymentConfirmationNotifier
{
    public function send(Pago $pago): bool
    {
        if ($pago->estado !== 'completado') {
            return false;
        }

        $hasEmailTrackingColumn = Schema::hasColumn('pagos', 'confirmacion_email_enviada_at');
        $claimed = $hasEmailTrackingColumn
            ? Pago::query()
                ->whereKey($pago->id)
                ->whereNull('confirmacion_email_enviada_at')
                ->update(['confirmacion_email_enviada_at' => now()])
            : 1;

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
            if ($hasEmailTrackingColumn) {
                Pago::query()->whereKey($pago->id)->update(['confirmacion_email_enviada_at' => null]);
            }

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

        if (Schema::hasColumn('pagos', 'confirmacion_email_enviada_at')) {
            Pago::query()->whereKey($pago->id)->update(['confirmacion_email_enviada_at' => null]);
        }

        return $this->send($pago->fresh());
    }
}
