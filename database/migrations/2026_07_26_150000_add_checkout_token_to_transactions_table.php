<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('checkout_token')->nullable()->after('order_id');
            $table->unique('checkout_token', 'transactions_checkout_token_unique');
            $table->index(
                ['user_id', 'event_id', 'total_price', 'payment_status'],
                'transactions_complimentary_order_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_complimentary_order_index');
            $table->dropUnique('transactions_checkout_token_unique');
            $table->dropColumn('checkout_token');
        });
    }
};
