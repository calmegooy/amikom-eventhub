<?php

namespace Tests\Feature;

use App\Mail\EventTicketMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MidtransWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('midtrans.server_key', 'server-key-for-test');
        Mail::fake();

        $organizer = User::create([
            'name' => 'Organizer',
            'email' => 'webhook-organizer@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Webhook Org',
            'slug' => 'webhook-org',
            'status' => 'approved',
        ]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);
        $category = Category::create(['name' => 'Webhook', 'slug' => 'webhook']);
        $event = Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $organization->id,
            'title' => 'Webhook Event',
            'description' => 'Webhook test',
            'date' => now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => 10,
        ]);
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'webhook-customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $this->transaction = Transaction::create([
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'user_id' => $customer->id,
            'order_id' => 'TRX-WEBHOOK-001',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081234567890',
            'quantity' => 2,
            'total_price' => 100000,
            'status' => 'Pending',
            'payment_status' => Transaction::PAYMENT_PENDING,
            'reserved_until' => now()->addMinutes(15),
        ]);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['signature_key'] = str_repeat('0', 128);

        $this->postJson('/midtrans/callback', $payload)
            ->assertStatus(400)
            ->assertJson(['message' => 'Signature Midtrans tidak valid.']);

        $this->assertSame(Transaction::PAYMENT_PENDING, $this->transaction->fresh()->payment_status);
    }

    public function test_duplicate_settlement_is_idempotent(): void
    {
        $payload = $this->payload();

        $this->postJson('/midtrans/callback', $payload)->assertOk();
        $this->postJson('/midtrans/callback', $payload)
            ->assertOk()
            ->assertJson(['message' => 'Already processed']);

        $transaction = $this->transaction->fresh();
        $this->assertTrue($transaction->isPaid());
        $this->assertSame(8, $transaction->event->stock);
        $this->assertSame(2, $transaction->tickets()->count());
        $this->assertSame(1, $transaction->paymentNotifications()->count());
        Mail::assertSent(EventTicketMail::class, 1);
    }

    private function payload(): array
    {
        $payload = [
            'order_id' => $this->transaction->order_id,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'MIDTRANS-TRANSACTION-001',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key')
        );

        return $payload;
    }
}
