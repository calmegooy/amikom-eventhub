<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public function createReservation(Event $event, User $user, array $data): Transaction
    {
        $checkoutToken = (string) $data['checkout_token'];

        return DB::transaction(function () use ($event, $user, $data, $checkoutToken) {
            $lockedEvent = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            $quantity = (int) $data['quantity'];

            if ($lockedEvent->date->isPast()) {
                throw new RuntimeException('Event ini sudah berlangsung dan tidak menerima pemesanan baru.');
            }

            $existingAttempt = Transaction::query()
                ->where('checkout_token', $checkoutToken)
                ->lockForUpdate()
                ->first();

            if ($existingAttempt) {
                if (
                    (int) $existingAttempt->user_id !== (int) $user->id
                    || (int) $existingAttempt->event_id !== (int) $lockedEvent->id
                ) {
                    throw new RuntimeException('Permintaan checkout tidak valid.');
                }

                return $existingAttempt->fresh(['event', 'coupon']);
            }

            $reserved = $lockedEvent->transactions()
                ->where('payment_status', Transaction::PAYMENT_PENDING)
                ->where('reserved_until', '>', now())
                ->sum('quantity');

            $availableStock = max(0, $lockedEvent->stock - $reserved);
            if ($availableStock < $quantity) {
                throw new RuntimeException(
                    "Sisa tiket ({$availableStock}) tidak mencukupi untuk pesanan Anda."
                );
            }

            $coupon = null;
            $discountPerTicket = 0;
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::query()
                    ->where('code', strtoupper(trim($data['coupon_code'])))
                    ->lockForUpdate()
                    ->first();

                if (! $coupon || ! $coupon->isValidFor($lockedEvent)) {
                    throw new RuntimeException('Kupon tidak valid, sudah habis, atau tidak berlaku untuk event ini.');
                }

                $discountPerTicket = $coupon->calculateDiscount((int) $lockedEvent->active_price);
            }

            $unitPrice = max(0, (int) $lockedEvent->active_price - $discountPerTicket);
            $serviceFee = $unitPrice > 0 ? 5000 : 0;
            $totalPrice = ($unitPrice * $quantity) + $serviceFee;

            if ($totalPrice === 0) {
                $existingComplimentaryOrder = Transaction::query()
                    ->where('event_id', $lockedEvent->id)
                    ->where('user_id', $user->id)
                    ->where('total_price', 0)
                    ->where(function ($query): void {
                        $query->where('payment_status', Transaction::PAYMENT_PAID)
                            ->orWhere(function ($pending): void {
                                $pending
                                    ->where('payment_status', Transaction::PAYMENT_PENDING)
                                    ->where('reserved_until', '>', now());
                            });
                    })
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if ($existingComplimentaryOrder) {
                    return $existingComplimentaryOrder->fresh(['event', 'coupon']);
                }
            }

            $reservedUntil = now()->addMinutes(15);

            $transaction = Transaction::create([
                'event_id' => $lockedEvent->id,
                'organization_id' => $lockedEvent->organization_id,
                'user_id' => $user->id,
                'order_id' => 'TRX-'.now()->format('YmdHis').'-'.strtoupper(Str::random(8)),
                'checkout_token' => $checkoutToken,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'status' => 'Pending',
                'payment_status' => Transaction::PAYMENT_PENDING,
                'coupon_id' => $coupon?->id,
                'expires_at' => $reservedUntil,
                'reserved_until' => $reservedUntil,
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            return $transaction->fresh(['event', 'coupon']);
        }, 3);
    }
}
