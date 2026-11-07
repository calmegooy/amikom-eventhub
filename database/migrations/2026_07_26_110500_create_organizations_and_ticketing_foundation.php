<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('organizer_id')
                ->constrained()->nullOnDelete();
            $table->index(['organization_id', 'date']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('organizer_id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('event_id')
                ->constrained()->nullOnDelete();
            $table->string('payment_status')->default('pending')->after('status');
            $table->timestamp('reserved_until')->nullable()->after('expires_at');
            $table->timestamp('paid_at')->nullable()->after('reserved_until');
            $table->timestamp('expired_at')->nullable()->after('paid_at');
            $table->timestamp('cancelled_at')->nullable()->after('expired_at');
            $table->timestamp('stock_decremented_at')->nullable()->after('cancelled_at');
            $table->timestamp('coupon_released_at')->nullable()->after('stock_decremented_at');
            $table->timestamp('ticket_email_sent_at')->nullable()->after('coupon_released_at');
            $table->string('midtrans_transaction_id')->nullable()->after('snap_token');
            $table->index(['event_id', 'payment_status', 'reserved_until'], 'transactions_reservation_index');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('event_id')
                ->constrained()->nullOnDelete();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('qr_token', 64)->unique();
            $table->string('holder_name');
            $table->string('holder_email');
            $table->string('status')->default('issued');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['organization_id', 'event_id', 'status']);
        });

        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('midtrans');
            $table->string('provider_transaction_id')->nullable();
            $table->string('order_id');
            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();
            $table->string('signature_key', 128)->unique();
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'transaction_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('tickets');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_reservation_index');
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn([
                'payment_status',
                'reserved_until',
                'paid_at',
                'expired_at',
                'cancelled_at',
                'stock_decremented_at',
                'coupon_released_at',
                'ticket_email_sent_at',
                'midtrans_transaction_id',
            ]);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'date']);
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
