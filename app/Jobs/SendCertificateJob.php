<?php

namespace App\Jobs;

use App\Mail\EventCertificateMail;
use App\Models\Transaction;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCertificateJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(public Transaction $transaction)
    {
    }

    public function handle(): void
    {
        if ($this->transaction->certificate_sent) {
            return;
        }

        if (! $this->transaction->isPaid() || $this->transaction->ticket_status !== 'used') {
            return;
        }

        try {
            Mail::to($this->transaction->customer_email)->send(new EventCertificateMail($this->transaction));
            $this->transaction->update(['certificate_sent' => true]);
        } catch (Exception $e) {
            Log::error("Failed to send certificate to {$this->transaction->order_id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
