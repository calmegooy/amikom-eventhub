<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('reviews')
            ->whereNotNull('user_id')
            ->select('user_id', 'event_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('user_id', 'event_id')
            ->having('total', '>', 1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException(
                'Unique review tidak dapat dibuat karena ada duplikasi user-event. Data tidak dihapus otomatis.'
            );
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'event_id'], 'reviews_user_event_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_event_unique');
        });
    }
};
