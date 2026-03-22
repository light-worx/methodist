<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $pin,
        public readonly string $appName = 'Connexion',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->appName} verification code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.verification-pin',
        );
    }
}