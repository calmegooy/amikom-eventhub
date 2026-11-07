<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillOrganizations();
        $this->backfillTransactionsAndTickets();
    }

    public function down(): void
    {
        // Data hasil backfill sengaja dipertahankan. Schema migration menangani rollback tabel.
    }

    private function backfillOrganizations(): void
    {
        $eventOwnerIds = DB::table('events')
            ->whereNotNull('organizer_id')
            ->pluck('organizer_id');

        $users = DB::table('users')
            ->where('role', 'organizer')
            ->orWhereIn('id', $eventOwnerIds)
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                $organizationId = DB::table('organizations')
                    ->where('owner_id', $user->id)
                    ->value('id');

                if (! $organizationId) {
                    $baseSlug = Str::slug(
                        $user->organization_slug ?: ($user->organization_name ?: $user->name)
                    );
                    $baseSlug = $baseSlug ?: 'organization-'.$user->id;
                    $slug = $baseSlug;
                    $suffix = 1;

                    while (DB::table('organizations')->where('slug', $slug)->exists()) {
                        $slug = $baseSlug.'-'.$suffix++;
                    }

                    $approved = $user->role === 'admin' || (bool) $user->is_approved;
                    $organizationId = DB::table('organizations')->insertGetId([
                        'owner_id' => $user->id,
                        'name' => $user->organization_name ?: $user->name,
                        'slug' => $slug,
                        'bio' => $user->organization_bio,
                        'status' => $approved ? 'approved' : 'pending',
                        'approved_at' => $approved ? now() : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('organization_user')->insertOrIgnore([
                    'organization_id' => $organizationId,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('events')
                    ->where('organizer_id', $user->id)
                    ->update(['organization_id' => $organizationId]);

                DB::table('coupons')
                    ->where('organizer_id', $user->id)
                    ->update(['organization_id' => $organizationId]);
            });
        }

        DB::table('reviews')->orderBy('id')->each(function ($review) {
            $organizationId = DB::table('events')
                ->where('id', $review->event_id)
                ->value('organization_id');

            DB::table('reviews')
                ->where('id', $review->id)
                ->update(['organization_id' => $organizationId]);
        });
    }

    private function backfillTransactionsAndTickets(): void
    {
        DB::table('transactions')->orderBy('id')->each(function ($transaction) {
            DB::transaction(function () use ($transaction) {
                $organizationId = DB::table('events')
                    ->where('id', $transaction->event_id)
                    ->value('organization_id');

                $paymentStatus = match (strtolower((string) $transaction->status)) {
                    'success', 'settlement', 'paid' => 'paid',
                    'expire', 'expired' => 'expired',
                    'cancel', 'cancelled' => 'cancelled',
                    'deny', 'failed' => 'failed',
                    'challenge' => 'challenged',
                    default => 'pending',
                };

                $isPaid = $paymentStatus === 'paid';
                $isExpired = $paymentStatus === 'expired';

                DB::table('transactions')->where('id', $transaction->id)->update([
                    'organization_id' => $organizationId,
                    'payment_status' => $paymentStatus,
                    'reserved_until' => $paymentStatus === 'pending' ? $transaction->expires_at : null,
                    'paid_at' => $isPaid ? $transaction->updated_at : null,
                    'expired_at' => $isExpired ? $transaction->updated_at : null,
                    // Existing paid orders have already passed the legacy stock handler.
                    'stock_decremented_at' => $isPaid ? $transaction->updated_at : null,
                ]);

                if (! $isPaid) {
                    return;
                }

                $quantity = max(1, (int) $transaction->quantity);
                $existing = DB::table('tickets')
                    ->where('transaction_id', $transaction->id)
                    ->count();
                $used = strtolower((string) $transaction->ticket_status) === 'used';

                for ($sequence = $existing + 1; $sequence <= $quantity; $sequence++) {
                    DB::table('tickets')->insert([
                        'transaction_id' => $transaction->id,
                        'event_id' => $transaction->event_id,
                        'organization_id' => $organizationId,
                        'user_id' => $transaction->user_id,
                        'code' => 'TKT-'.strtoupper(Str::random(16)),
                        'qr_token' => hash(
                            'sha256',
                            Str::uuid()->toString().Str::random(40).$transaction->id.$sequence
                        ),
                        'holder_name' => $transaction->customer_name,
                        'holder_email' => $transaction->customer_email,
                        'status' => $used ? 'used' : 'issued',
                        'checked_in_at' => $used ? $transaction->updated_at : null,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                    ]);
                }
            });
        });
    }
};
