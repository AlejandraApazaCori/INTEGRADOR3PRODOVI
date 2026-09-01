<?php

namespace App\Mail;

use App\Models\Campania;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaniaCreada extends Mailable
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public function __construct(public User $user, public Campania $campania)
    {
        $this->dashboardUrl = route('clientes.dashboard');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.from.address'), config('mail.from.name'))],
            subject: '¡Tu campaña ya está activa en PRODOVI!',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.campania-creada');
    }

    public function attachments(): array
    {
        return [];
    }
}
