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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('coupon_id');
            $table->boolean('stock_released')->default(false)->after('expires_at');
            $table->boolean('whatsapp_recovery_sent')->default(false)->after('stock_released');
            $table->boolean('whatsapp_ticket_sent')->default(false)->after('whatsapp_recovery_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'expires_at',
                'stock_released',
                'whatsapp_recovery_sent',
                'whatsapp_ticket_sent',
            ]);
        });
    }
};
