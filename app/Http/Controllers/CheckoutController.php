<?php

namespace App\Http\Controllers;

use App\Mail\EventTicketMail;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Transaction;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use App\Services\PaymentLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly MidtransService $midtransService,
        private readonly PaymentLifecycleService $paymentLifecycle,
    ) {}

    public function create(Event $event)
    {
        abort_unless(
            Event::query()->publiclyVisible()->whereKey($event->getKey())->exists(),
            404
        );

        $categories = Category::all();
        $user = Auth::user();

        $checkoutToken = Str::uuid()->toString();

        return view('checkout.create', compact('event', 'categories', 'user', 'checkoutToken'));
    }

    public function checkCoupon(Request $request, Event $event)
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($event)) {
            return response()->json([
                'valid' => false,
                'message' => 'Kupon tidak berlaku untuk event ini atau sudah kedaluwarsa/habis.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => $coupon->calculateDiscount((int) $event->active_price),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'checkout_token' => ['required', 'uuid'],
        ]);

        try {
            $transaction = $this->checkoutService->createReservation(
                $event,
                $request->user(),
                $validated
            );
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($transaction->isPaid()) {
            return redirect()->route('checkout.success', $transaction->order_id);
        }

        if (! $transaction->isPending()) {
            return redirect()
                ->route('checkout.create', $event)
                ->with('error', 'Reservasi sebelumnya sudah tidak dapat digunakan. Silakan buat pesanan baru.');
        }

        if ((int) $transaction->total_price === 0) {
            $result = $this->paymentLifecycle->markPaid($transaction);

            if (! $result['transaction']->isPaid()) {
                return redirect()
                    ->route('checkout.create', $event)
                    ->with('error', 'Reservasi sebelumnya sudah tidak dapat digunakan. Silakan buat pesanan baru.');
            }

            $this->sendTicketEmail($result['transaction']);

            return redirect()->route('checkout.success', $transaction->order_id);
        }

        try {
            $this->midtransService->createSnapToken($transaction);
        } catch (Throwable $exception) {
            Log::error('Midtrans Snap token gagal dibuat.', [
                'order_id' => $transaction->order_id,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('checkout.payment', $transaction->order_id)
                ->with('error', 'Pesanan sudah disimpan, tetapi koneksi pembayaran belum tersedia.');
        }

        return redirect()->route('checkout.payment', $transaction->order_id);
    }

    public function payment(string $order_id)
    {
        $transaction = $this->ownedTransaction($order_id);

        if ($transaction->isPaid()) {
            return redirect()->route('checkout.success', $order_id);
        }

        if (
            ! $transaction->isPending()
            || ($transaction->reserved_until && $transaction->reserved_until->isPast())
        ) {
            return redirect()
                ->route('user.tickets')
                ->with('error', 'Reservasi ini sudah tidak dapat dibayar.');
        }

        try {
            $snapToken = $this->midtransService->createSnapToken($transaction);
        } catch (Throwable $exception) {
            Log::error('Midtrans Snap token gagal dibuat.', [
                'order_id' => $transaction->order_id,
                'message' => $exception->getMessage(),
            ]);
            $snapToken = null;
        }

        $categories = Category::all();

        return view('checkout.payment', compact('transaction', 'snapToken', 'categories'));
    }

    public function finish(Request $request)
    {
        $orderId = $request->string('order_id')->toString();

        if ($orderId === '') {
            return redirect()
                ->route('user.tickets')
                ->with('error', 'Pesanan pembayaran tidak ditemukan.');
        }

        return $this->reconcileAndRedirect($orderId);
    }

    public function success(string $order_id)
    {
        $transaction = $this->ownedTransaction($order_id);

        if ($transaction->isPending()) {
            return $this->reconcileAndRedirect($order_id);
        }

        return $this->showSuccess($transaction);
    }

    private function reconcileAndRedirect(string $orderId)
    {
        $transaction = $this->ownedTransaction($orderId);

        try {
            $result = $this->midtransService->reconcile($transaction);
            $transaction = $result['transaction'];

            if ($result['newly_paid']) {
                $this->sendTicketEmail($transaction);
            }
        } catch (Throwable $exception) {
            Log::warning('Fallback status Midtrans gagal diselaraskan.', [
                'order_id' => $orderId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('checkout.payment', $orderId)
                ->with('error', 'Status pembayaran belum dapat dikonfirmasi. Silakan coba lagi beberapa saat.');
        }

        if ($transaction->isPaid()) {
            return $this->showSuccess($transaction);
        }

        return redirect()
            ->route('checkout.payment', $orderId)
            ->with('error', 'Pembayaran belum berstatus lunas di Midtrans.');
    }

    private function showSuccess(Transaction $transaction)
    {
        $transaction->loadMissing('tickets');
        $categories = Category::all();

        return view('checkout.success', compact('transaction', 'categories'));
    }

    public function devSuccess(string $order_id)
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $transaction = $this->ownedTransaction($order_id);
        $result = $this->paymentLifecycle->markPaid($transaction, 'local-simulation');
        $this->sendTicketEmail($result['transaction']);

        return redirect()->route('checkout.success', $order_id);
    }

    private function ownedTransaction(string $orderId): Transaction
    {
        return Transaction::with('event')
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function sendTicketEmail(Transaction $transaction): void
    {
        if ($transaction->ticket_email_sent_at || ! $transaction->isPaid()) {
            return;
        }

        try {
            Mail::to($transaction->customer_email)->send(new EventTicketMail($transaction));
            $transaction->update(['ticket_email_sent_at' => now()]);
        } catch (Throwable $exception) {
            Log::error('E-ticket gagal dikirim.', [
                'order_id' => $transaction->order_id,
                'message' => $exception->getMessage(),
            ]);
        }

        // Dispatch WhatsApp notification jika ada nomor HP
        if ($transaction->customer_phone && ! $transaction->whatsapp_ticket_sent) {
            try {
                \App\Jobs\SendTicketWhatsAppJob::dispatch($transaction);
            } catch (Throwable $exception) {
                Log::error('WhatsApp job gagal di-dispatch.', [
                    'order_id' => $transaction->order_id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
