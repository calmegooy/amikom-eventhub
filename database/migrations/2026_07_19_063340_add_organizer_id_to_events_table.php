<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->after('category_id')->constrained('users')->cascadeOnDelete();
            $table->integer('early_bird_price')->nullable()->after('price');
            $table->dateTime('early_bird_until')->nullable()->after('early_bird_price');
            $table->integer('presale_price')->nullable()->after('early_bird_until');
            $table->dateTime('presale_until')->nullable()->after('presale_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizer_id');
            $table->dropColumn([
                'early_bird_price',
                'early_bird_until',
                'presale_price',
                'presale_until',
            ]);
        });
    }
};
