<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonedCartRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.whatsapp.api_key', 'dummy-token');

        \Illuminate\Support\Facades\Http::fake([
            'api.fonnte.com/send' => \Illuminate\Support\Facades\Http::response(['status' => true], 200),
        ]);

        $organizer = User::create(['name' => 'Organizer', 'email' => 'org-cart@example.com', 'password' => bcrypt('password'), 'role' => 'organizer', 'is_approved' => true]);
        $organization = Organization::create(['owner_id' => $organizer->id, 'name' => 'Cart Org', 'slug' => 'cart-org', 'status' => 'approved', 'approved_at' => now()]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);
        $category = Category::create(['name' => 'Cart Test', 'slug' => 'cart-test']);
        $this->event = Event::create(['category_id' => $category->id, 'organizer_id' => $organizer->id, 'organization_id' => $organization->id, 'title' => 'Cart Recovery Event', 'description' => 'test', 'date' => now()->addWeek(), 'location' => 'Yogyakarta', 'price' => 75000, 'stock' => 20]);
        $this->customer = User::create(['name' => 'Customer Cart', 'email' => 'customer-cart@example.com', 'password' => bcrypt('password'), 'role' => 'user']);
    }

    public function test_pending_transaction_with_active_reservation_receives_recovery_message(): void
    {
        $t = $this->pendingTransaction(now()->addMinutes(10), 10, 75000);
        $this->artisan('tickets:recover-carts')->assertSuccessful();
        $this->assertTrue($t->fresh()->whatsapp_recovery_sent);
    }

    public function test_transaction_with_expired_reservation_is_not_sent(): void
    {
        $t = $this->pendingTransaction(now()->subMinutes(1), 20, 75000);
        $this->artisan('tickets:recover-carts')->assertSuccessful();
        $this->assertFalse($t->fresh()->whatsapp_recovery_sent);
    }

    public function test_paid_transaction_is_not_sent(): void
    {
        $t = $this->pendingTransaction(now()->addMinutes(10), 10, 75000);
        $t->update(['payment_status' => Transaction::PAYMENT_PAID]);
        $this->artisan('tickets:recover-carts')->assertSuccessful();
        $this->assertFalse($t->fresh()->whatsapp_recovery_sent);
    }

    public function test_free_transaction_is_not_sent(): void
    {
        $t = $this->pendingTransaction(now()->addMinutes(10), 10, 0);
        $this->artisan('tickets:recover-carts')->assertSuccessful();
        $this->assertFalse($t->fresh()->whatsapp_recovery_sent);
    }

    public function test_already_recovered_transaction_is_not_sent_again(): void
    {
        $t = $this->pendingTransaction(now()->addMinutes(10), 10, 75000);
        $t->update(['whatsapp_recovery_sent' => true]);
        $this->artisan('tickets:recover-carts')->assertSuccessful();
        $this->assertDatabaseHas('transactions', ['id' => $t->id, 'whatsapp_recovery_sent' => true]);
    }

    private function pendingTransaction(\DateTimeInterface $reservedUntil, int $createdMinutesAgo, int $totalPrice): Transaction
    {
        $transaction = Transaction::create(['event_id' => $this->event->id, 'organization_id' => $this->event->organization_id, 'user_id' => $this->customer->id, 'order_id' => 'TRX-CART-'.uniqid(), 'customer_name' => $this->customer->name, 'customer_email' => $this->customer->email, 'customer_phone' => '08123456789', 'quantity' => 1, 'total_price' => $totalPrice, 'status' => 'Pending', 'payment_status' => Transaction::PAYMENT_PENDING, 'reserved_until' => $reservedUntil, 'whatsapp_recovery_sent' => false]);
        $transaction->created_at = now()->subMinutes($createdMinutesAgo);
        $transaction->saveQuietly();

        return $transaction->fresh();
    }
}
