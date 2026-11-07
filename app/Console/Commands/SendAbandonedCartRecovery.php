<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendAbandonedCartRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:recover-carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp payment reminders for abandoned pending transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Hanya transaksi berbayar (total_price > 0) yang berstatus pending,
        // reservasi masih aktif (reserved_until > now()), dibuat minimal 5 menit lalu,
        // dan belum pernah dikirim notifikasi recovery.
        $abandonedTransactions = Transaction::with('event')
            ->where('payment_status', Transaction::PAYMENT_PENDING)
            ->where('reserved_until', '>', now())
            ->where('created_at', '<=', now()->subMinutes(5))
            ->where('whatsapp_recovery_sent', false)
            ->where('total_price', '>', 0)
            ->get();

        if ($abandonedTransactions->isEmpty()) {
            $this->info('No abandoned carts found for recovery.');
            return;
        }

        $this->info("Found {$abandonedTransactions->count()} pending transactions for recovery. Sending alerts...");

        foreach ($abandonedTransactions as $transaction) {
            $paymentUrl = route('checkout.payment', $transaction->order_id);
            $message = "Halo *{$transaction->customer_name}*,\n\nKami melihat Anda belum menyelesaikan pembayaran untuk event *{$transaction->event->title}*.\n\nStok tiket Anda hanya kami *tahan selama 15 menit* agar tidak diambil oleh pembeli lain.\n\nSegera selesaikan pembayaran Anda melalui link berikut sebelum tiket dilepas kembali:\n{$paymentUrl}\n\nTerima kasih!";

            $sent = WhatsAppService::send($transaction->customer_phone, $message);

            if ($sent) {
                $transaction->update(['whatsapp_recovery_sent' => true]);
                $this->info("Recovery message sent to {$transaction->customer_phone} for Order {$transaction->order_id}");
            } else {
                $this->error("Failed to send recovery message to {$transaction->customer_phone}");
            }
        }

        $this->info('Abandoned cart recovery scan complete.');
    }
}
