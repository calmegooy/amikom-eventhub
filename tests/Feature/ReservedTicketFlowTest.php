<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\PaymentLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReservedTicketFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('midtrans.server_key', '');

        $organizer = User::create([
            'name' => 'Organizer',
            'email' => 'organizer@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Organizer Test',
            'slug' => 'organizer-test',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);

        $category = Category::create([
            'name' => 'Teknologi',
            'slug' => 'teknologi',
        ]);
        $this->event = Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $organization->id,
            'title' => 'Laravel Conference',
            'description' => 'Event test',
            'date' => now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 100000,
            'stock' => 5,
        ]);
        $this->customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    public function test_reservation_holds_requested_quantity_without_decrementing_physical_stock(): void
    {
        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $this->checkoutData(quantity: 3)
        );

        $this->assertSame(3, $transaction->quantity);
        $this->assertSame(Transaction::PAYMENT_PENDING, $transaction->payment_status);
        $this->assertNotNull($transaction->reserved_until);
        $this->assertSame(5, $this->event->fresh()->stock);
        $this->assertSame(2, $this->event->fresh()->available_stock);
    }

    public function test_reposting_the_same_checkout_token_returns_the_existing_reservation(): void
    {
        $data = $this->checkoutData(quantity: 1);
        $first = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $data
        );
        $second = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $data
        );

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Transaction::where('checkout_token', $data['checkout_token'])->count());
        $this->assertSame(4, $this->event->fresh()->available_stock);
    }

    public function test_free_event_only_issues_one_ticket_for_a_customer_even_from_two_checkout_forms(): void
    {
        Mail::fake();
        $this->event->update(['price' => 0]);
        $firstData = $this->checkoutData(quantity: 1);
        $secondData = $this->checkoutData(quantity: 1);

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->event), $firstData)
            ->assertRedirect();

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->event), $secondData)
            ->assertRedirect();

        $transaction = Transaction::query()
            ->where('event_id', $this->event->id)
            ->where('user_id', $this->customer->id)
            ->where('total_price', 0)
            ->sole();

        $this->assertTrue($transaction->isPaid());
        $this->assertSame(1, $transaction->tickets()->count());
        $this->assertSame(4, $this->event->fresh()->stock);
    }

    public function test_paid_reservation_decrements_stock_and_issues_unique_tickets_once(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $this->checkoutData(quantity: 2)
        );
        $service = app(PaymentLifecycleService::class);

        $first = $service->markPaid($transaction, 'MIDTRANS-TEST-1');
        
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendTicketWhatsAppJob::class, 1);
        \Illuminate\Support\Facades\Queue::assertPushed(function (\App\Jobs\SendTicketWhatsAppJob $job) use ($transaction) {
            return $job->transaction->id === $transaction->id;
        });

        $second = $service->markPaid($transaction->fresh(), 'MIDTRANS-TEST-1');
        
        // Assert it wasn't pushed again
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendTicketWhatsAppJob::class, 1);

        $this->assertTrue($first['newly_paid']);
        $this->assertFalse($second['newly_paid']);
        $this->assertSame(3, $this->event->fresh()->stock);
        $this->assertSame(2, $transaction->tickets()->count());
        $this->assertSame(2, $transaction->tickets()->distinct('qr_token')->count('qr_token'));
        $this->assertSame(2, $transaction->tickets()->distinct('code')->count('code'));
    }

    public function test_expired_reservation_releases_coupon_without_incrementing_stock(): void
    {
        $coupon = Coupon::create([
            'event_id' => $this->event->id,
            'organizer_id' => $this->event->organizer_id,
            'organization_id' => $this->event->organization_id,
            'code' => 'RESERVE15',
            'type' => 'fixed',
            'value' => 10000,
            'max_usage' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $data = $this->checkoutData(quantity: 1);
        $data['coupon_code'] = $coupon->code;
        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $data
        );

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->travel(16)->minutes();
        $this->artisan('tickets:expire-reservations')->assertSuccessful();

        $transaction->refresh();
        $this->assertSame(Transaction::PAYMENT_EXPIRED, $transaction->payment_status);
        $this->assertNotNull($transaction->expired_at);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(5, $this->event->fresh()->stock);
    }

    public function test_legacy_expiry_command_delegates_to_the_official_reservation_lifecycle(): void
    {
        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $this->checkoutData(quantity: 1)
        );

        $this->travel(16)->minutes();
        $this->artisan('tickets:release')->assertSuccessful();

        $transaction->refresh();
        $this->assertSame(Transaction::PAYMENT_EXPIRED, $transaction->payment_status);
        $this->assertNotNull($transaction->expired_at);
        $this->assertTrue($transaction->stock_released);
        $this->assertSame(5, $this->event->fresh()->stock);
    }

    public function test_late_payment_expires_the_reservation_without_decrementing_stock(): void
    {
        $coupon = Coupon::create([
            'event_id' => $this->event->id,
            'organizer_id' => $this->event->organizer_id,
            'organization_id' => $this->event->organization_id,
            'code' => 'LATEPAY',
            'type' => 'fixed',
            'value' => 10000,
            'max_usage' => 1,
            'used_count' => 0,
            'is_active' => true,
        ]);
        $data = $this->checkoutData(quantity: 2);
        $data['coupon_code'] = $coupon->code;
        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $data
        );

        $this->travel(16)->minutes();
        $result = app(PaymentLifecycleService::class)->markPaid($transaction, 'MIDTRANS-LATE-PAYMENT');

        $this->assertFalse($result['newly_paid']);
        $this->assertSame(Transaction::PAYMENT_EXPIRED, $result['transaction']->payment_status);
        $this->assertSame(5, $this->event->fresh()->stock);
        $this->assertSame(0, $transaction->fresh()->tickets()->count());
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_payment_page_displays_the_reservation_countdown(): void
    {
        $transaction = app(CheckoutService::class)->createReservation(
            $this->event,
            $this->customer,
            $this->checkoutData(quantity: 1)
        );

        $this->actingAs($this->customer)
            ->get(route('checkout.payment', $transaction->order_id))
            ->assertOk()
            ->assertSee('Sisa waktu pembayaran')
            ->assertSee('data-reservation-deadline=', false);
    }

    private function checkoutData(int $quantity): array
    {
        return [
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '081234567890',
            'coupon_code' => null,
            'quantity' => $quantity,
            'checkout_token' => Str::uuid()->toString(),
        ];
    }
}
