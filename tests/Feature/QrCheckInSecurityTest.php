<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCheckInSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_is_tenant_scoped_and_rejects_double_scan(): void
    {
        $category = Category::create(['name' => 'Check-in', 'slug' => 'check-in']);
        [$organizerA, $organizationA] = $this->organizer('A');
        [$organizerB, $organizationB] = $this->organizer('B');
        $eventA = $this->event($category, $organizerA, $organizationA, 'A');
        $eventB = $this->event($category, $organizerB, $organizationB, 'B');
        $ticketA = $this->paidTicket($eventA, $organizationA, 'A');

        $this->actingAs($organizerB)
            ->postJson(route('organizer.checkin', $eventA), ['ticket_token' => $ticketA->qr_token])
            ->assertForbidden();

        $this->actingAs($organizerA)
            ->postJson(route('organizer.checkin', $eventB), ['ticket_token' => $ticketA->qr_token])
            ->assertForbidden();

        \Illuminate\Support\Facades\Queue::fake();

        $this->actingAs($organizerA)
            ->postJson(route('organizer.checkin', $eventA), ['ticket_token' => $ticketA->qr_token])
            ->assertOk()
            ->assertJson(['success' => true]);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendCertificateJob::class, 1);
        \Illuminate\Support\Facades\Queue::assertPushed(function (\App\Jobs\SendCertificateJob $job) use ($ticketA) {
            return $job->transaction->id === $ticketA->transaction_id;
        });

        $this->actingAs($organizerA)
            ->postJson(route('organizer.checkin', $eventA), ['ticket_token' => $ticketA->qr_token])
            ->assertStatus(409)
            ->assertJson(['success' => false]);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendCertificateJob::class, 1);

        $ticketA->refresh();
        $this->assertSame(Ticket::STATUS_USED, $ticketA->status);
        $this->assertSame($organizerA->id, $ticketA->checked_in_by);
        $this->assertNotNull($ticketA->checked_in_at);
    }

    public function test_cancelled_ticket_is_rejected_before_check_in(): void
    {
        $category = Category::create(['name' => 'Cancelled check-in', 'slug' => 'cancelled-check-in']);
        [$organizer, $organization] = $this->organizer('Cancelled');
        $event = $this->event($category, $organizer, $organization, 'Cancelled');
        $ticket = $this->paidTicket($event, $organization, 'Cancelled');
        $ticket->update(['status' => Ticket::STATUS_CANCELLED]);

        $this->actingAs($organizer)
            ->postJson(route('organizer.checkin', $event), ['ticket_token' => $ticket->qr_token])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tiket telah dibatalkan dan tidak dapat digunakan.',
            ]);

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_CANCELLED, $ticket->status);
        $this->assertNull($ticket->checked_in_at);
    }

    public function test_e_ticket_uses_a_locally_generated_qr_code(): void
    {
        $category = Category::create(['name' => 'Local QR', 'slug' => 'local-qr']);
        [$organizer, $organization] = $this->organizer('Qr');
        $event = $this->event($category, $organizer, $organization, 'Qr');
        $ticket = $this->paidTicket($event, $organization, 'Qr');

        $this->actingAs($ticket->user)
            ->get(route('ticket', $ticket->transaction->order_id))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertDontSee('api.qrserver.com');
    }

    private function organizer(string $suffix): array
    {
        $organizer = User::create([
            'name' => 'Organizer '.$suffix,
            'email' => 'organizer'.strtolower($suffix).'@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $organization = Organization::create([
            'owner_id' => $organizer->id,
            'name' => 'Organization '.$suffix,
            'slug' => 'organization-'.strtolower($suffix),
            'status' => 'approved',
        ]);
        $organization->members()->attach($organizer->id, ['role' => 'owner']);

        return [$organizer, $organization];
    }

    private function event(
        Category $category,
        User $organizer,
        Organization $organization,
        string $suffix
    ): Event {
        return Event::create([
            'category_id' => $category->id,
            'organizer_id' => $organizer->id,
            'organization_id' => $organization->id,
            'title' => 'Event '.$suffix,
            'description' => 'Event check-in',
            'date' => now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    private function paidTicket(Event $event, Organization $organization, string $suffix): Ticket
    {
        $customer = User::create([
            'name' => 'Customer '.$suffix,
            'email' => 'customer'.strtolower($suffix).'@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $transaction = Transaction::create([
            'event_id' => $event->id,
            'organization_id' => $organization->id,
            'user_id' => $customer->id,
            'order_id' => 'TRX-CHECKIN-'.$suffix,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081234567890',
            'quantity' => 1,
            'total_price' => 50000,
            'status' => 'Pending',
            'payment_status' => Transaction::PAYMENT_PENDING,
            'reserved_until' => now()->addMinutes(15),
        ]);

        app(PaymentLifecycleService::class)->markPaid($transaction, 'CHECKIN-'.$suffix);

        return $transaction->tickets()->firstOrFail();
    }
}
