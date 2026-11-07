<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizerRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $organizer;

    public function __construct(User $organizer)
    {
        $this->organizer = $organizer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Status Akun Penyelenggara Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organizer-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
