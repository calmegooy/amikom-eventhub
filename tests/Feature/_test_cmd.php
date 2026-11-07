<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$abandonedTransactions = App\Models\Transaction::with('event')
    ->where('payment_status', App\Models\Transaction::PAYMENT_PENDING)
    ->where('reserved_until', '>', now())
    ->where('created_at', '<=', now()->subMinutes(5))
    ->where('whatsapp_recovery_sent', false)
    ->where('total_price', '>', 0)
    ->get();
echo 'Count: ' . count($abandonedTransactions);
