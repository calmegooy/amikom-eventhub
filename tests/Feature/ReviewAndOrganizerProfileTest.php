<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAndOrganizerProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Organization $organization;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $organizer = User::create([
            'name' => 'Profile Organizer',
            'email' => 'profile-organizer@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $this->organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Amikom Tech Community',
            'slug' => 'amikom-tech-community',
            'bio' => 'Komunitas teknologi kampus.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $this->organization->members()->attach($organizer->id, ['role' => 'owner']);
        $category = Category::create(['name' => 'Seminar', 'slug' => 'seminar']);
        $this->event = Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $this->organization->id,
            'title' => 'Past Laravel Seminar',
            'description' => 'Event yang sudah selesai.',
            'date' => now()->subDays(2),
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => 10,
        ]);
        $this->customer = User::create([
            'name' => 'Review Customer',
            'email' => 'review-customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    public function test_customer_can_review_once_per_event_after_h_plus_one(): void
    {
        $firstTransaction = $this->paidTransaction('TRX-REVIEW-001');

        $this->actingAs($this->customer)
            ->post(route('review.store', $firstTransaction->order_id), [
                'rating' => 5,
                'comment' => 'Materinya sangat bermanfaat.',
            ])
            ->assertRedirect(route('review.create', $firstTransaction->order_id));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->customer->id,
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'status' => Review::STATUS_PUBLISHED,
        ]);

        $secondTransaction = $this->paidTransaction('TRX-REVIEW-002');
        $this->actingAs($this->customer)
            ->get(route('review.create', $secondTransaction->order_id))
            ->assertOk()
            ->assertSee('Anda sudah memberikan ulasan');

        $this->assertSame(1, Review::where('user_id', $this->customer->id)->count());
    }

    public function test_customer_cannot_review_an_unpaid_ticket(): void
    {
        $transaction = $this->paidTransaction('TRX-REVIEW-UNPAID');
        $transaction->update([
            'status' => 'pending',
            'payment_status' => Transaction::PAYMENT_PENDING,
            'paid_at' => null,
        ]);

        $this->actingAs($this->customer)
            ->get(route('review.create', $transaction->order_id))
            ->assertForbidden();

        $this->actingAs($this->customer)
            ->post(route('review.store', $transaction->order_id), [
                'rating' => 5,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_review_before_h_plus_one(): void
    {
        $this->event->update(['date' => now()]);
        $transaction = $this->paidTransaction('TRX-REVIEW-TOO-EARLY');

        $this->actingAs($this->customer)
            ->get(route('review.create', $transaction->order_id))
            ->assertOk()
            ->assertSee('Ulasan Belum Bisa Ditulis');

        $this->actingAs($this->customer)
            ->post(route('review.store', $transaction->order_id), [
                'rating' => 5,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_cannot_review_another_customers_ticket(): void
    {
        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other-review-customer@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $otherCustomer->id,
            'order_id' => 'TRX-REVIEW-OTHER-CUSTOMER',
            'customer_name' => $otherCustomer->name,
            'customer_email' => $otherCustomer->email,
            'customer_phone' => '081234567891',
            'quantity' => 1,
            'total_price' => 50000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now(),
            'stock_decremented_at' => now(),
        ]);

        $this->actingAs($this->customer)
            ->get(route('review.create', $transaction->order_id))
            ->assertNotFound();

        $this->actingAs($this->customer)
            ->post(route('review.store', $transaction->order_id), [
                'rating' => 5,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_published_review_appears_on_approved_organization_profile(): void
    {
        $transaction = $this->paidTransaction('TRX-PROFILE-001');
        Review::create([
            'transaction_id' => $transaction->id,
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'comment' => 'Testimoni profil organisasi.',
            'status' => Review::STATUS_PUBLISHED,
        ]);

        $this->get(route('organizer.profile', $this->organization->slug))
            ->assertOk()
            ->assertSee($this->organization->name)
            ->assertSee('Testimoni profil organisasi.')
            ->assertSee('5.0');
    }

    public function test_legacy_hidden_review_is_not_displayed_on_profile(): void
    {
        $transaction = $this->paidTransaction('TRX-HIDDEN-001');
        Review::create([
            'transaction_id' => $transaction->id,
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->customer->id,
            'rating' => 1,
            'comment' => 'Review demo tidak valid.',
            'status' => Review::STATUS_LEGACY_HIDDEN,
        ]);

        $this->get(route('organizer.profile', $this->organization->slug))
            ->assertOk()
            ->assertDontSee('Review demo tidak valid.');
    }

    public function test_profile_rating_only_uses_published_reviews(): void
    {
        $publishedTransaction = $this->paidTransaction('TRX-PROFILE-PUBLISHED');
        Review::create([
            'transaction_id' => $publishedTransaction->id,
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->customer->id,
            'rating' => 5,
            'comment' => 'Review yang ditampilkan.',
            'status' => Review::STATUS_PUBLISHED,
        ]);

        $otherCustomer = User::create([
            'name' => 'Hidden Review Customer',
            'email' => 'hidden-review-customer@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        $hiddenTransaction = Transaction::create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $otherCustomer->id,
            'order_id' => 'TRX-PROFILE-HIDDEN',
            'customer_name' => $otherCustomer->name,
            'customer_email' => $otherCustomer->email,
            'customer_phone' => '081234567892',
            'quantity' => 1,
            'total_price' => 50000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now(),
            'stock_decremented_at' => now(),
        ]);
        Review::create([
            'transaction_id' => $hiddenTransaction->id,
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $otherCustomer->id,
            'rating' => 1,
            'comment' => 'Review tersembunyi.',
            'status' => Review::STATUS_LEGACY_HIDDEN,
        ]);

        $this->get(route('organizer.profile', $this->organization->slug))
            ->assertOk()
            ->assertSee('5.0')
            ->assertSee('dari 1 ulasan terverifikasi')
            ->assertSee('Review yang ditampilkan.')
            ->assertDontSee('Review tersembunyi.');
    }

    private function paidTransaction(string $orderId): Transaction
    {
        return Transaction::create([
            'event_id' => $this->event->id,
            'organization_id' => $this->organization->id,
            'user_id' => $this->customer->id,
            'order_id' => $orderId,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '081234567890',
            'quantity' => 1,
            'total_price' => 50000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now(),
            'stock_decremented_at' => now(),
        ]);
    }
}
