<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificatePortalTest extends TestCase
{
    use RefreshDatabase;

    private $event;

    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $organizer = User::create([
            'name' => 'Demo Organizer',
            'email' => 'org@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        $category = Category::create([
            'name' => 'Tech Event',
            'slug' => 'tech-event',
        ]);

        $this->event = Event::create([
            'organizer_id' => $organizer->id,
            'category_id' => $category->id,
            'title' => 'Mastering Laravel 12',
            'slug' => 'mastering-laravel-12',
            'description' => 'Learn Laravel 12 advanced topics.',
            'date' => now()->addDays(5),
            'location' => 'Amikom Yogyakarta',
            'price' => 150000,
            'stock' => 100,
        ]);

        $this->customer = User::create([
            'name' => 'Budi Setiawan',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    /**
     * Test 1: Accessing certificate of non-existent transaction returns 404.
     */
    public function test_non_existent_transaction_returns_404()
    {
        $response = $this->actingAs($this->customer)
            ->get(route('certificate.index', 'TRX-INVALID'));
        $response->assertStatus(404);
    }

    /**
     * Test 2: Accessing certificate of unpaid transaction returns 403.
     */
    public function test_unpaid_transaction_returns_403()
    {
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $this->customer->id,
            'order_id' => 'TRX-UNPAID',
            'customer_name' => 'Budi Setiawan',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'total_price' => 150000,
            'status' => 'pending',
            'ticket_status' => 'unused',
        ]);

        $responseIndex = $this->actingAs($this->customer)->get(route('certificate.index', $transaction->order_id));
        $responseIndex->assertStatus(403);
        $responseIndex->assertSee('Tiket ini belum lunas.');

        $responsePreview = $this->get(route('certificate.preview', $transaction->order_id));
        $responsePreview->assertStatus(403);
        $responsePreview->assertSee('Tiket ini belum lunas.');

        $responseDownload = $this->get(route('certificate.download', $transaction->order_id));
        $responseDownload->assertStatus(403);
        $responseDownload->assertSee('Tiket ini belum lunas.');
    }

    /**
     * Test 3: Accessing certificate of paid but not checked-in transaction returns 403.
     */
    public function test_paid_but_not_checked_in_transaction_returns_403()
    {
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $this->customer->id,
            'order_id' => 'TRX-PAID-NOT-USED',
            'customer_name' => 'Budi Setiawan',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'total_price' => 150000,
            'status' => 'success',
            'ticket_status' => 'unused',
        ]);

        $responseIndex = $this->actingAs($this->customer)->get(route('certificate.index', $transaction->order_id));
        $responseIndex->assertStatus(403);
        $responseIndex->assertSee('Sertifikat hanya tersedia setelah check-in di lokasi acara.');

        $responsePreview = $this->get(route('certificate.preview', $transaction->order_id));
        $responsePreview->assertStatus(403);
        $responsePreview->assertSee('Sertifikat hanya tersedia setelah check-in di lokasi acara.');

        $responseDownload = $this->get(route('certificate.download', $transaction->order_id));
        $responseDownload->assertStatus(403);
        $responseDownload->assertSee('Sertifikat hanya tersedia setelah check-in di lokasi acara.');
    }

    /**
     * Test 4: Accessing certificate of paid and checked-in transaction returns 200/file.
     */
    public function test_paid_and_checked_in_transaction_succeeds()
    {
        $transaction = Transaction::create([
            'event_id' => $this->event->id,
            'user_id' => $this->customer->id,
            'order_id' => 'TRX-PAID-AND-USED',
            'customer_name' => 'Budi Setiawan',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'total_price' => 150000,
            'status' => 'success',
            'ticket_status' => 'used',
        ]);

        $responseIndex = $this->actingAs($this->customer)->get(route('certificate.index', $transaction->order_id));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Portal E-Sertifikat');
        $responseIndex->assertSee('Budi Setiawan');
        $responseIndex->assertSee('Mastering Laravel 12');

        $responsePreview = $this->get(route('certificate.preview', $transaction->order_id));
        $responsePreview->assertStatus(200);
        $responsePreview->assertHeader('Content-Type', 'application/pdf');

        $responseDownload = $this->get(route('certificate.download', $transaction->order_id));
        $responseDownload->assertStatus(200);
        $responseDownload->assertHeader('Content-Type', 'application/pdf');
    }
}
