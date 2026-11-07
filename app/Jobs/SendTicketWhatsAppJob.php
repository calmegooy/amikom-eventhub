<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\WhatsAppService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTicketWhatsAppJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(public Transaction $transaction)
    {
    }

    public function handle(): void
    {
        if ($this->transaction->whatsapp_ticket_sent) {
            return;
        }

        if (!$this->transaction->isPaid()) {
            return;
        }

        $ticketUrl = route('ticket', $this->transaction->order_id);
        $message = "Halo *{$this->transaction->customer_name}*,\n\nPembayaran tiket event *{$this->transaction->event->title}* telah BERHASIL.\n\nE-Ticket Anda dapat diakses pada link berikut:\n{$ticketUrl}\n\nTerima kasih!";

        try {
            $sent = WhatsAppService::send($this->transaction->customer_phone, $message);
            
            if ($sent) {
                $this->transaction->update(['whatsapp_ticket_sent' => true]);
            }
        } catch (Exception $e) {
            Log::error("Failed to send WhatsApp ticket to {$this->transaction->order_id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
