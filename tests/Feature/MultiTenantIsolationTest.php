<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_managed_scopes_only_include_approved_member_tenants_and_legacy_unassigned_records(): void
    {
        $organizer = $this->organizer('scope');
        $approved = $this->organization($organizer, 'approved-scope', Organization::STATUS_APPROVED);
        $rejected = $this->organization($organizer, 'rejected-scope', Organization::STATUS_REJECTED);
        $otherOrganizer = $this->organizer('other');
        $other = $this->organization($otherOrganizer, 'other-scope', Organization::STATUS_APPROVED);

        $approvedEvent = $this->event($organizer, $approved, 'Approved event');
        $this->event($organizer, $rejected, 'Rejected event');
        $this->event($organizer, $other, 'Foreign tenant with legacy organizer');
        $legacyEvent = $this->event($organizer, null, 'Legacy unassigned event');

        $approvedCoupon = $this->coupon($organizer, $approved, $approvedEvent, 'APPROVED');
        $this->coupon($organizer, $rejected, null, 'REJECTED');
        $this->coupon($organizer, $other, null, 'FOREIGN');
        $legacyCoupon = $this->coupon($organizer, null, null, 'LEGACY');

        $this->assertEqualsCanonicalizing(
            [$approvedEvent->id, $legacyEvent->id],
            Event::query()->managedBy($organizer)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$approvedCoupon->id, $legacyCoupon->id],
            Coupon::query()->managedBy($organizer)->pluck('id')->all(),
        );
    }

    public function test_cross_tenant_coupon_is_never_valid_for_an_event(): void
    {
        $organizer = $this->organizer('coupon');
        $organizationA = $this->organization($organizer, 'coupon-a', Organization::STATUS_APPROVED);
        $organizationB = $this->organization($organizer, 'coupon-b', Organization::STATUS_APPROVED);
        $eventA = $this->event($organizer, $organizationA, 'Coupon event A');

        $couponB = $this->coupon($organizer, $organizationB, null, 'TENANTB');

        $this->assertFalse($couponB->isValidFor($eventA));
    }

    public function test_organizer_dashboard_never_reads_transactions_from_an_unapproved_tenant(): void
    {
        $organizer = $this->organizer('dashboard');
        $approved = $this->organization($organizer, 'dashboard-approved', Organization::STATUS_APPROVED);
        $rejected = $this->organization($organizer, 'dashboard-rejected', Organization::STATUS_REJECTED);
        $approvedEvent = $this->event($organizer, $approved, 'Dashboard approved event');
        $rejectedEvent = $this->event($organizer, $rejected, 'Dashboard rejected event');

        $this->transaction($approvedEvent, $approved, 'TENANT-APPROVED', 10000);
        $this->transaction($rejectedEvent, $rejected, 'TENANT-REJECTED', 90000);

        $response = $this->actingAs($organizer)->get(route('organizer.dashboard'));

        $response->assertOk();
        $data = $response->original->getData();
        $this->assertSame(10000, (int) $data['totalRevenue']);
        $this->assertSame(1, (int) $data['ticketsSold']);
        $this->assertSame(1, (int) $data['totalEvents']);
    }

    public function test_transaction_ticket_and_review_scopes_are_tenant_isolated(): void
    {
        $organizerA = $this->organizer('resources-a');
        $organizationA = $this->organization($organizerA, 'resources-a', Organization::STATUS_APPROVED);
        $organizerB = $this->organizer('resources-b');
        $organizationB = $this->organization($organizerB, 'resources-b', Organization::STATUS_APPROVED);
        $eventA = $this->event($organizerA, $organizationA, 'Resource event A');
        $eventB = $this->event($organizerB, $organizationB, 'Resource event B');
        $transactionA = $this->transaction($eventA, $organizationA, 'RESOURCE-A', 10000);
        $transactionB = $this->transaction($eventB, $organizationB, 'RESOURCE-B', 90000);
        $ticketA = $this->ticket($transactionA, $eventA, $organizationA, 'TICKET-A');
        $ticketB = $this->ticket($transactionB, $eventB, $organizationB, 'TICKET-B');
        $reviewA = $this->review($transactionA, $eventA, $organizationA, 'Review A');
        $reviewB = $this->review($transactionB, $eventB, $organizationB, 'Review B');

        $this->assertSame(
            [$transactionA->id],
            Transaction::query()->managedBy($organizerA)->pluck('id')->all(),
        );
        $this->assertSame(
            [$ticketA->id],
            Ticket::query()->managedBy($organizerA)->pluck('id')->all(),
        );
        $this->assertSame(
            [$reviewA->id],
            Review::query()->managedBy($organizerA)->pluck('id')->all(),
        );

        $this->assertNotSame($transactionB->id, $transactionA->id);
        $this->assertNotSame($ticketB->id, $ticketA->id);
        $this->assertNotSame($reviewB->id, $reviewA->id);
    }

    public function test_only_tenant_owner_can_update_organization_profile(): void
    {
        $owner = $this->organizer('profile-owner');
        $organization = $this->organization($owner, 'profile-owner', Organization::STATUS_APPROVED);
        $member = $this->organizer('profile-member');
        $organization->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($owner)
            ->get(route('organizer.organization.edit'))
            ->assertOk()
            ->assertSee('Profil Organisasi');

        $this->actingAs($member)
            ->put(route('organizer.organization.update'), [
                'name' => 'Attempted takeover',
                'slug' => 'attempted-takeover',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->put(route('organizer.organization.update'), [
                'name' => 'Updated Organization',
                'slug' => 'updated-organization',
                'description' => 'Deskripsi tenant yang sudah diperbarui.',
                'email' => 'public@example.com',
                'phone' => '081234567890',
                'address' => 'Yogyakarta',
            ])
            ->assertRedirect(route('organizer.organization.edit'));

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization',
            'slug' => 'updated-organization',
            'description' => 'Deskripsi tenant yang sudah diperbarui.',
            'email' => 'public@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'organization_name' => 'Updated Organization',
            'organization_slug' => 'updated-organization',
        ]);
    }

    public function test_organization_profile_schema_and_platform_event_ownership_are_explicit(): void
    {
        $this->assertTrue(Schema::hasColumns('organizations', [
            'description',
            'logo_path',
            'email',
            'phone',
            'address',
            'verified_at',
        ]));

        Storage::fake('public');
        $admin = User::create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ]);
        $category = Category::create([
            'name' => 'Platform',
            'slug' => 'platform',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'category_id' => $category->id,
                'title' => 'Platform Event',
                'description' => 'Event resmi platform.',
                'date' => now()->addWeek()->format('Y-m-d H:i:s'),
                'location' => 'Yogyakarta',
                'price' => 0,
                'stock' => 100,
                'poster' => UploadedFile::fake()->image('platform-event.jpg'),
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Platform Event',
            'organizer_id' => $admin->id,
            'organization_id' => null,
        ]);
    }

    public function test_public_event_identity_comes_from_organization_relation(): void
    {
        $organizer = $this->organizer('public-identity');
        $organizer->update([
            'organization_name' => 'Legacy identity must not win',
            'organization_slug' => 'legacy-identity',
        ]);
        $organization = $this->organization(
            $organizer,
            'relation-identity',
            Organization::STATUS_APPROVED,
        );
        $event = $this->event($organizer, $organization, 'Public identity event');

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee($organization->name)
            ->assertSee(route('organizer.profile', $organization->slug))
            ->assertDontSee('Legacy identity must not win');
    }

    private function organizer(string $suffix): User
    {
        return User::create([
            'name' => 'Organizer '.$suffix,
            'email' => $suffix.'@example.com',
            'password' => 'password',
            'role' => User::ROLE_ORGANIZER,
            'is_approved' => true,
        ]);
    }

    private function organization(User $owner, string $slug, string $status): Organization
    {
        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Organization '.$slug,
            'slug' => $slug,
            'status' => $status,
        ]);

        $organization->members()->attach($owner->id, ['role' => 'owner']);

        return $organization;
    }

    private function event(
        User $organizer,
        ?Organization $organization,
        string $title,
    ): Event {
        $category = Category::firstOrCreate(
            ['slug' => 'multi-tenant'],
            ['name' => 'Multi Tenant'],
        );

        return Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $organization?->id,
            'title' => $title,
            'description' => 'Regression test multi-tenant.',
            'date' => now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 10000,
            'stock' => 100,
        ]);
    }

    private function coupon(
        User $organizer,
        ?Organization $organization,
        ?Event $event,
        string $code,
    ): Coupon {
        return Coupon::create([
            'organizer_id' => $organizer->id,
            'organization_id' => $organization?->id,
            'event_id' => $event?->id,
            'code' => $code,
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => true,
        ]);
    }

    private function transaction(
        Event $event,
        Organization $organization,
        string $orderId,
        int $total,
    ): Transaction {
        return Transaction::create([
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'order_id' => $orderId,
            'customer_name' => 'Customer',
            'customer_email' => strtolower($orderId).'@example.com',
            'customer_phone' => '081234567890',
            'quantity' => 1,
            'total_price' => $total,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
        ]);
    }

    private function ticket(
        Transaction $transaction,
        Event $event,
        Organization $organization,
        string $code,
    ): Ticket {
        return Ticket::create([
            'transaction_id' => $transaction->id,
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'code' => $code,
            'qr_token' => hash('sha256', $code),
            'holder_name' => 'Customer',
            'holder_email' => strtolower($code).'@example.com',
            'status' => Ticket::STATUS_ISSUED,
        ]);
    }

    private function review(
        Transaction $transaction,
        Event $event,
        Organization $organization,
        string $comment,
    ): Review {
        $customer = User::create([
            'name' => $comment,
            'email' => str($comment)->slug().'-customer@example.com',
            'password' => 'password',
            'role' => User::ROLE_CUSTOMER,
        ]);

        return Review::create([
            'transaction_id' => $transaction->id,
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => $comment,
            'status' => Review::STATUS_PUBLISHED,
        ]);
    }
}
