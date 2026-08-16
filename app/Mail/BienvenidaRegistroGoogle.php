<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaRegistroGoogle extends Mailable
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public User $user)
    {
        $this->dashboardUrl = route('clientes.home');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: '¡Bienvenido a PRODOVI! Elige el plan ideal para tu marca',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida-registro-google',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
