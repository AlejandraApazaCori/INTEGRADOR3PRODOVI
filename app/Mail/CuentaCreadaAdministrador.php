<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CuentaCreadaAdministrador extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        private string $plainPassword,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu cuenta en PRODOVI fue creada exitosamente');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cuenta-creada-administrador');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => "Credenciales de acceso a PRODOVI\r\n\r\nCorreo: {$this->user->email}\r\nContraseña: {$this->plainPassword}\r\n",
                'credenciales-PRODOVI.txt'
            )->withMime('text/plain'),
        ];
    }
}
