<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_wrong_roles_cannot_open_protected_panels(): void
    {
        $customer = $this->user('customer', 'user');
        $organizer = $this->user('organizer', 'organizer', true);
        $admin = $this->user('admin', 'admin');

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('organizer.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
        $this->actingAs($customer)
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('home'));

        $this->actingAs($organizer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('organizer.dashboard'));
        $this->actingAs($admin)
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_rejected_organization_blocks_panel_and_event_policy_even_if_legacy_flag_is_true(): void
    {
        $organizer = $this->user('rejected-organizer', 'organizer', true);
        $organization = $this->organization($organizer, 'rejected');
        $event = $this->event($organizer, $organization);

        $this->actingAs($organizer)
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('organizer.waiting'));
        $this->actingAs($organizer)
            ->get(route('organizer.waiting'))
            ->assertOk();

        $this->assertFalse(Gate::forUser($organizer)->allows('update', $event));
        $this->assertFalse(Gate::forUser($organizer)->allows('scan', $event));
    }

    public function test_only_customer_role_can_use_customer_ownership_in_resource_policies(): void
    {
        $organizer = $this->user('resource-organizer', 'organizer', true);
        $organization = $this->organization($organizer, 'rejected');
        $event = $this->event($organizer, $organization, now()->subDays(3));
        $transaction = $this->transaction($event, $organizer);
        $ticket = $this->ticket($transaction, $organizer);

        $this->assertFalse(Gate::forUser($organizer)->allows('view', $transaction));
        $this->assertFalse(Gate::forUser($organizer)->allows('view', $ticket));
        $this->assertFalse(
            Gate::forUser($organizer)->allows('createForTransaction', [Review::class, $transaction])
        );
    }

    public function test_approved_organization_and_customer_ownership_remain_authorized(): void
    {
        $organizer = $this->user('approved-organizer', 'organizer', true);
        $organization = $this->organization($organizer, 'approved');
        $event = $this->event($organizer, $organization, now()->subDays(3));
        $customer = $this->user('authorized-customer', 'user');
        $transaction = $this->transaction($event, $customer);
        $ticket = $this->ticket($transaction, $customer);

        $this->assertTrue(Gate::forUser($organizer)->allows('update', $event));
        $this->assertTrue(Gate::forUser($organizer)->allows('scan', $event));
        $this->assertTrue(Gate::forUser($organizer)->allows('view', $transaction));
        $this->assertTrue(Gate::forUser($organizer)->allows('view', $ticket));

        $this->assertTrue(Gate::forUser($customer)->allows('view', $transaction));
        $this->assertTrue(Gate::forUser($customer)->allows('view', $ticket));
        $this->assertTrue(
            Gate::forUser($customer)->allows('createForTransaction', [Review::class, $transaction])
        );
    }

    public function test_admin_approval_keeps_user_and_organization_status_in_sync(): void
    {
        Mail::fake();

        $admin = $this->user('approval-admin', 'admin');
        $organizer = $this->user('approval-organizer', 'organizer');
        $organization = $this->organization($organizer, 'pending');

        $this->actingAs($admin)
            ->patch(route('admin.organizers.approve', $organizer))
            ->assertRedirect();

        $this->assertTrue($organizer->fresh()->is_approved);
        $this->assertSame('approved', $organization->fresh()->status);
        $this->assertSame($admin->id, $organization->fresh()->approved_by);

        $this->actingAs($admin)
            ->patch(route('admin.organizers.reject', $organizer))
            ->assertRedirect();

        $this->assertFalse($organizer->fresh()->is_approved);
        $this->assertSame('rejected', $organization->fresh()->status);
        $this->assertNull($organization->fresh()->approved_by);
    }

    public function test_customer_registration_cannot_escalate_submitted_role(): void
    {
        $this->post(route('user.register.post'), [
            'name' => 'Customer Register',
            'email' => 'customer-register@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
        ])->assertRedirect(route('home'));

        $customer = User::where('email', 'customer-register@example.com')->firstOrFail();

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->is_approved);
        $this->assertAuthenticatedAs($customer);
    }

    public function test_organizer_registration_always_starts_pending_with_owner_membership(): void
    {
        $this->post(route('user.register.organizer.post'), [
            'name' => 'Organizer Register',
            'organization_name' => 'Organization Register',
            'organization_bio' => 'Organization pending untuk pengujian.',
            'email' => 'organizer-register@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_ADMIN,
            'is_approved' => true,
            'status' => Organization::STATUS_APPROVED,
        ])->assertRedirect(route('organizer.waiting'));

        $organizer = User::where('email', 'organizer-register@example.com')->firstOrFail();
        $organization = $organizer->activeOrganization();

        $this->assertTrue($organizer->isOrganizer());
        $this->assertFalse($organizer->is_approved);
        $this->assertNotNull($organization);
        $this->assertSame(Organization::STATUS_PENDING, $organization->status);
        $this->assertTrue($organizer->belongsToOrganization($organization->id));
        $this->assertSame(
            'owner',
            $organization->members()->whereKey($organizer->id)->firstOrFail()->pivot->role
        );
    }

    public function test_authorization_lookup_indexes_exist(): void
    {
        $userIndexes = collect(Schema::getIndexes('users'))->pluck('name');
        $organizationIndexes = collect(Schema::getIndexes('organizations'))->pluck('name');

        $this->assertContains('users_role_approval_index', $userIndexes);
        $this->assertContains('organizations_status_index', $organizationIndexes);
    }

    private function user(string $suffix, string $role, bool $approved = false): User
    {
        return User::create([
            'name' => ucfirst($suffix),
            'email' => $suffix.'@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'is_approved' => $approved,
        ]);
    }

    private function organization(User $organizer, string $status): Organization
    {
        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Organization '.$organizer->id,
            'slug' => 'organization-'.$organizer->id,
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);

        return $organization;
    }

    private function event(
        User $organizer,
        Organization $organization,
        $date = null
    ): Event {
        $category = Category::firstOrCreate(
            ['slug' => 'authorization'],
            ['name' => 'Authorization']
        );

        return Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $organization->id,
            'title' => 'Authorization Event '.$organizer->id,
            'description' => 'Event untuk pengujian authorization.',
            'date' => $date ?: now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    private function transaction(Event $event, User $user): Transaction
    {
        return Transaction::create([
            'event_id' => $event->id,
            'organization_id' => $event->organization_id,
            'user_id' => $user->id,
            'order_id' => 'AUTH-'.$event->id.'-'.$user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '081234567890',
            'quantity' => 1,
            'total_price' => 50000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now()->subDays(2),
        ]);
    }

    private function ticket(Transaction $transaction, User $user): Ticket
    {
        return Ticket::create([
            'transaction_id' => $transaction->id,
            'event_id' => $transaction->event_id,
            'organization_id' => $transaction->organization_id,
            'user_id' => $user->id,
            'code' => 'AUTH-TICKET-'.$transaction->id,
            'qr_token' => hash('sha256', 'authorization-'.$transaction->id),
            'holder_name' => $user->name,
            'holder_email' => $user->email,
            'status' => Ticket::STATUS_ISSUED,
        ]);
    }
}
