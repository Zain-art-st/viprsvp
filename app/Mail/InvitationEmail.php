<?php

namespace App\Mail;

use App\Models\InvitationContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvitationContact $contact,
    ) {}

    public function envelope(): Envelope
{
    return new Envelope(
        subject: 'Independence Day Attendance Confirmation — '.$this->contact->invitation->vip_name,
        replyTo: [config('mail.reply_to_address', 'afifnaufalzaidi@gmail.com')],
    );
}

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invitation',
            with: [
                'vipName' => $this->contact->invitation->vip_name,
                'contactName' => $this->contact->name,
                'rsvpUrl' => route('rsvp.show', $this->contact->token),
            ],
        );
    }
}