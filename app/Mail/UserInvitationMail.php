<?php

namespace App\Mail;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $maildata;

    /**
     * Create a new message instance.
     */
    public function __construct($maildata)
    {
        $this->maildata = $maildata;
        if (isset($maildata['districts'])) {
            $this->maildata['permissions'] = "Districts: " . District::whereIn('id', explode(',', $maildata['districts']))->pluck('district')->implode(', ');
        } elseif (isset($maildata['circuits'])) {
            $this->maildata['permissions'] = "Circuits: " . Circuit::whereIn('id', explode(',', $maildata['circuits']))->pluck('circuit')->implode(', ');
        } elseif (isset($maildata['societies'])) {
            $this->maildata['permissions'] = "Societies: " . Society::whereIn('id', explode(',', $maildata['societies']))->pluck('society')->implode(', ');
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $churchnet = new Address('admin@lightworx.co.za', 'ChurchNet Admin');
        return new Envelope(
            from: $churchnet,
            replyTo: [$churchnet],
            to: [new Address($this->maildata['email'])],
            subject: 'Preaching plan software: User sign up invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.userinvitation',
            with: [
                'maildata' => $this->maildata,
            ],
        );
    }
}
