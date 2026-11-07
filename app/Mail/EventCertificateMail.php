<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class EventCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Certificate Kehadiran: ' . $this->transaction->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.certificate-notice');
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('certificates.template', ['transaction' => $this->transaction])
            ->setPaper('a4', 'landscape');

        return [
            Attachment::fromData(fn () => $pdf->output(), 'E-Certificate-' . $this->transaction->order_id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
