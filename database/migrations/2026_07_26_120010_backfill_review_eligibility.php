<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reviews')
            ->orderBy('id')
            ->each(function ($review) {
                $transaction = DB::table('transactions')->find($review->transaction_id);
                $event = DB::table('events')->find($review->event_id);

                if (! $transaction || ! $event) {
                    DB::table('reviews')->where('id', $review->id)->update([
                        'status' => 'legacy_hidden',
                    ]);

                    return;
                }

                $eligibleAt = Carbon::parse($event->date)->addDay();
                $submittedAt = Carbon::parse($review->created_at);

                DB::table('reviews')->where('id', $review->id)->update([
                    'user_id' => $review->user_id ?: $transaction->user_id,
                    'organization_id' => $review->organization_id ?: $transaction->organization_id,
                    'status' => $submittedAt->gte($eligibleAt) ? 'published' : 'legacy_hidden',
                ]);
            });
    }

    public function down(): void
    {
        // Data legacy tidak dikembalikan ke status published karena dapat melanggar aturan H+1.
    }
};
