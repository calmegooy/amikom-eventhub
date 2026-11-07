<?php

namespace App\Http\Controllers;

use App\Mail\EventTicketMail;
use App\Services\MidtransWebhookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(private readonly MidtransWebhookService $webhookService) {}

    public function handle(Request $request)
    {
        try {
            $result = $this->webhookService->handle($request->all());
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Transaction not found'], 404);
        } catch (RuntimeException $exception) {
            Log::warning('Notifikasi Midtrans ditolak.', [
                'order_id' => $request->input('order_id'),
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => $exception->getMessage()], 400);
        } catch (Throwable $exception) {
            Log::error('Notifikasi Midtrans gagal diproses.', [
                'order_id' => $request->input('order_id'),
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }

        $transaction = $result['transaction'];
        if ($result['newly_paid'] && ! $transaction->ticket_email_sent_at) {
            try {
                Mail::to($transaction->customer_email)->send(new EventTicketMail($transaction));
                $transaction->update(['ticket_email_sent_at' => now()]);
            } catch (Throwable $exception) {
                Log::error('E-ticket gagal dikirim setelah webhook.', [
                    'order_id' => $transaction->order_id,
                    'message' => $exception->getMessage(),
                ]);
            }

            // Kirim WhatsApp setelah pembayaran dikonfirmasi Midtrans
            if ($transaction->customer_phone && ! $transaction->whatsapp_ticket_sent) {
                try {
                    \App\Jobs\SendTicketWhatsAppJob::dispatch($transaction);
                } catch (Throwable $exception) {
                    Log::error('WhatsApp job gagal di-dispatch dari webhook.', [
                        'order_id' => $transaction->order_id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => $result['duplicate'] ? 'Already processed' : 'OK',
        ]);
    }
}
