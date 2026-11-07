<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_organizer_and_paid_order_backfill_is_idempotent(): void
    {
        $organizer = User::create([
            'name' => 'Legacy Organizer',
            'email' => 'legacy-organizer@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
            'organization_name' => 'Legacy Community',
            'organization_slug' => 'legacy-community',
        ]);
        $customer = User::create([
            'name' => 'Legacy Customer',
            'email' => 'legacy-customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $category = Category::create(['name' => 'Legacy', 'slug' => 'legacy']);
        $event = Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => null,
            'title' => 'Legacy Event',
            'description' => 'Legacy data',
            'date' => now()->subDay(),
            'location' => 'Yogyakarta',
            'price' => 25000,
            'stock' => 98,
        ]);
        $transaction = Transaction::create([
            'event_id' => $event->id,
            'organization_id' => null,
            'user_id' => $customer->id,
            'order_id' => 'TRX-LEGACY-001',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081234567890',
            'quantity' => 2,
            'total_price' => 50000,
            'status' => 'success',
            'ticket_status' => 'unused',
        ]);

        $migration = require database_path(
            'migrations/2026_07_26_110510_backfill_organizations_and_tickets.php'
        );
        $migration->up();
        $migration->up();

        $organization = $organizer->ownedOrganizations()->first();
        $this->assertNotNull($organization);
        $this->assertSame($organization->id, $event->fresh()->organization_id);
        $this->assertSame($organization->id, $transaction->fresh()->organization_id);
        $this->assertSame(Transaction::PAYMENT_PAID, $transaction->fresh()->payment_status);
        $this->assertSame(2, $transaction->tickets()->count());
        $this->assertSame(1, $organization->members()->whereKey($organizer->id)->count());
    }
}
