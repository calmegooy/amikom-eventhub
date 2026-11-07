<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StabilizationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_only_show_upcoming_events_from_approved_or_legacy_platform_owners(): void
    {
        $category = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        [$approvedOrganizer, $approvedOrganization] = $this->organizer('Approved', 'approved');
        [$pendingOrganizer, $pendingOrganization] = $this->organizer('Pending', 'pending');

        $approvedEvent = $this->event(
            $category,
            'Event Approved',
            now()->addWeek(),
            $approvedOrganizer,
            $approvedOrganization
        );
        $pendingEvent = $this->event(
            $category,
            'Event Pending',
            now()->addWeek(),
            $pendingOrganizer,
            $pendingOrganization
        );
        $pastEvent = $this->event(
            $category,
            'Event Selesai',
            now()->subDay(),
            $approvedOrganizer,
            $approvedOrganization
        );
        $legacyPlatformEvent = $this->event(
            $category,
            'Event Platform Lama',
            now()->addDays(10)
        );

        foreach ([route('home'), route('katalog')] as $url) {
            $response = $this->get($url)->assertOk();
            $eventIds = $response->viewData('events')->pluck('id');

            $this->assertTrue($eventIds->contains($approvedEvent->id));
            $this->assertTrue($eventIds->contains($legacyPlatformEvent->id));
            $this->assertFalse($eventIds->contains($pendingEvent->id));
            $this->assertFalse($eventIds->contains($pastEvent->id));
        }

        $this->get(route('events.show', $pendingEvent))->assertNotFound();
        $this->get(route('events.show', $pastEvent))->assertNotFound();

        $customer = User::create([
            'name' => 'Customer Visibility',
            'email' => 'customer-visibility@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $this->actingAs($customer)
            ->get(route('checkout.create', $pendingEvent))
            ->assertNotFound();
        $this->actingAs($customer)
            ->get(route('checkout.create', $pastEvent))
            ->assertNotFound();
    }

    public function test_admin_dashboard_uses_normalized_payment_status_and_sums_ticket_quantity(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $category = Category::create(['name' => 'Bisnis', 'slug' => 'bisnis']);
        $event = $this->event($category, 'Dashboard Event', now()->addWeek());

        $this->transaction($event, 'PAID-1', 3, 150000, Transaction::PAYMENT_PAID, 'success');
        $this->transaction($event, 'PENDING-1', 2, 100000, Transaction::PAYMENT_PENDING, 'Pending');
        $this->transaction($event, 'LEGACY-MISMATCH', 4, 200000, Transaction::PAYMENT_PENDING, 'success');

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertSame(150000, (int) $response->viewData('totalRevenue'));
        $this->assertSame(3, (int) $response->viewData('ticketsSold'));
        $this->assertSame(2, (int) $response->viewData('pendingOrders'));
        $this->assertSame(150000, array_sum($response->viewData('chartData')['revenue']));
    }

    public function test_admin_event_validation_rejects_negative_price_and_stock(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-validation@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $category = Category::create(['name' => 'Seminar', 'slug' => 'seminar']);

        $this->actingAs($admin)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), [
                'category_id' => $category->id,
                'title' => 'Event Invalid',
                'description' => 'Harga dan stok tidak boleh negatif.',
                'date' => now()->addWeek()->toDateTimeString(),
                'location' => 'Yogyakarta',
                'price' => -1000,
                'stock' => -1,
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ])
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors(['price', 'stock']);

        $this->assertDatabaseMissing('events', ['title' => 'Event Invalid']);
    }

    public function test_checkout_and_event_detail_display_available_stock_after_active_reservations(): void
    {
        $category = Category::create(['name' => 'Workshop', 'slug' => 'workshop']);
        [$organizer, $organization] = $this->organizer('Stock', 'approved');
        $event = $this->event(
            $category,
            'Reserved Stock Event',
            now()->addWeek(),
            $organizer,
            $organization,
            stock: 5
        );
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer-stock@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->transaction(
            $event,
            'ACTIVE-RESERVATION',
            3,
            300000,
            Transaction::PAYMENT_PENDING,
            'Pending',
            now()->addMinutes(15)
        );

        $this->actingAs($customer)
            ->get(route('checkout.create', $event))
            ->assertOk()
            ->assertSee('max="2"', false)
            ->assertSee('Sisa stok: 2');

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('2 Tiket lagi!');
    }

    public function test_checkout_page_displays_active_price_badge_and_strikethrough_normal_price(): void
    {
        $category = Category::create(['name' => 'Workshop Badge', 'slug' => 'workshop-badge']);
        [$organizer, $organization] = $this->organizer('Badge', 'approved');
        $event = $this->event(
            $category,
            'Badge Event',
            now()->addWeek(),
            $organizer,
            $organization,
            stock: 5
        );
        $event->update([
            'price' => 100000,
            'early_bird_price' => 50000,
            'early_bird_until' => now()->addDays(2),
        ]);

        $customer = User::create([
            'name' => 'Customer Badge',
            'email' => 'customer-badge@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->actingAs($customer)
            ->get(route('checkout.create', $event))
            ->assertOk()
            ->assertSee('Early Bird')
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 100.000')
            ->assertSee('line-through');
    }

    public function test_event_poster_url_uses_category_asset_until_a_poster_is_uploaded(): void
    {
        $category = Category::create(['name' => 'Entertainment', 'slug' => 'entertainment']);
        $event = $this->event($category, 'Konser Kampus', now()->addWeek());

        $this->assertSame(asset('assets/concert.png'), $event->poster_url);

        $event->poster_path = 'posters/konser-kampus.webp';

        $this->assertSame(asset('storage/posters/konser-kampus.webp'), $event->poster_url);
    }

    public function test_service_worker_only_caches_public_static_assets(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('CACHEABLE_PATH_PREFIXES', $serviceWorker);
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringNotContainsString("'/katalog'", $serviceWorker);
        $this->assertStringNotContainsString("'/bantuan'", $serviceWorker);
        $this->assertStringNotContainsString("'/kontak'", $serviceWorker);
    }

    public function test_database_seeder_builds_consistent_multi_tenant_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $organizationCount = Organization::count();
        $eventCount = Event::count();
        $transactionCount = Transaction::count();

        $this->assertGreaterThan(0, Organization::count());
        $this->assertSame(0, Event::whereNotNull('organizer_id')->whereNull('organization_id')->count());
        $this->assertSame(0, Transaction::whereNull('organization_id')->count());
        $this->assertSame(0, Event::where('poster_path', 'like', 'http%')->count());

        User::where('role', 'organizer')->each(function (User $organizer): void {
            $this->assertNotNull($organizer->activeOrganization());
            $this->assertTrue($organizer->belongsToOrganization($organizer->activeOrganization()->id));
        });

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($organizationCount, Organization::count());
        $this->assertSame($eventCount, Event::count());
        $this->assertSame($transactionCount, Transaction::count());
    }

    public function test_event_seeder_uses_the_current_event_and_tenant_schema(): void
    {
        $this->seed(EventSeeder::class);

        $this->assertGreaterThan(0, Event::count());
        $this->assertSame(0, Event::whereNull('organization_id')->count());
        $this->assertSame(0, Event::where('poster_path', 'like', 'http%')->count());
    }

    private function organizer(string $suffix, string $status): array
    {
        $slug = strtolower($suffix);
        $organizer = User::create([
            'name' => 'Organizer '.$suffix,
            'email' => 'organizer-'.$slug.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => $status === 'approved',
            'organization_name' => 'Organization '.$suffix,
            'organization_slug' => 'organization-'.$slug,
        ]);
        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Organization '.$suffix,
            'slug' => 'organization-'.$slug,
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);

        return [$organizer, $organization];
    }

    private function event(
        Category $category,
        string $title,
        $date,
        ?User $organizer = null,
        ?Organization $organization = null,
        int $stock = 10
    ): Event {
        return Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer?->id,
            'organization_id' => $organization?->id,
            'title' => $title,
            'description' => 'Deskripsi event untuk pengujian.',
            'date' => $date,
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => $stock,
        ]);
    }

    private function transaction(
        Event $event,
        string $orderId,
        int $quantity,
        int $totalPrice,
        string $paymentStatus,
        string $legacyStatus,
        $reservedUntil = null
    ): Transaction {
        return Transaction::create([
            'event_id' => $event->id,
            'organization_id' => $event->organization_id,
            'order_id' => $orderId,
            'customer_name' => 'Customer Test',
            'customer_email' => strtolower($orderId).'@example.com',
            'customer_phone' => '081234567890',
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'status' => $legacyStatus,
            'payment_status' => $paymentStatus,
            'reserved_until' => $reservedUntil,
        ]);
    }
}
