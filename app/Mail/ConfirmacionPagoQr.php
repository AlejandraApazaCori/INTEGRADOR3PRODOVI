<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

class ConfirmacionPagoQr extends ConfirmacionPago
{
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Pago QR confirmado: comprobante de respaldo PRODOVI',
        );
    }
}
