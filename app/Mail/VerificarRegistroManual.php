<?php

namespace App\Mail;

use App\Models\RegistroPendiente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificarRegistroManual extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RegistroPendiente $registro,
        public string $verificationUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: 'Confirma tu correo y activa tu cuenta en PRODOVI',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verificar-registro-manual');
    }

    public function attachments(): array
    {
        return [];
    }
}
