<?php

namespace App\Mail;

use App\Models\SolicitudContacto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudContactoConfirmacion extends Mailable
{
    use Queueable, SerializesModels;

    public string $planesUrl;

    public function __construct(public SolicitudContacto $solicitud)
    {
        $this->planesUrl = route('login');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Gracias por contarnos sobre tu proyecto | PRODOVI',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-contacto-confirmacion',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
