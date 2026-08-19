<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CambioContrasenaCuenta extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirma el cambio de contraseña de tu cuenta PRODOVI');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cambio-contrasena-cuenta');
    }

    public function attachments(): array
    {
        return [];
    }
}
