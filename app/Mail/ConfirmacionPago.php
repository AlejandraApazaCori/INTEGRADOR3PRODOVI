<?php

namespace App\Mail;

use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacionPago extends Mailable
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public Pago $pago)
    {
        $this->pago->loadMissing(['usuario', 'plan', 'suscripcion', 'comprobantePago']);
        $this->dashboardUrl = route('clientes.dashboard');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Pago confirmado en PRODOVI',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.confirmacion-pago');
    }

    public function attachments(): array
    {
        $numero = $this->pago->comprobantePago?->numero_formateado ?? str_pad((string) $this->pago->id, 5, '0', STR_PAD_LEFT);

        return [
            Attachment::fromData(
                fn () => Pdf::loadView('clientes.comprobante-pago-pdf', [
                    'pago' => $this->pago,
                    'comprobante' => $this->pago->comprobantePago,
                ])->output(),
                'comprobante-PRODOVI-'.$numero.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
