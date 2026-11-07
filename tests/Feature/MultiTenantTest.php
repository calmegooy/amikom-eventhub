<?php

namespace Tests\Feature;

use App\Mail\OrganizerApprovedMail;
use App\Mail\OrganizerRejectedMail;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MultiTenantTest extends TestCase
{
    use RefreshDatabase;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        // Buat kategori default untuk relasi event
        $this->category = Category::create([
            'name' => 'Tech Event',
            'slug' => 'tech-event',
        ]);
    }

    /**
     * Test 1: Organizer yang belum di-approve dialihkan ke halaman waiting.
     */
    public function test_unapproved_organizer_redirected_to_waiting_page()
    {
        $organizer = User::create([
            'name' => 'Organizer Pending',
            'email' => 'pending@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => false,
            'organization_name' => 'Pending Org',
            'organization_slug' => 'pending-org',
        ]);

        $response = $this->actingAs($organizer)->get(route('organizer.dashboard'));
        $response->assertRedirect(route('organizer.waiting'));

        // Pastikan halaman waiting bisa diakses tanpa loop redirect
        $waitingResponse = $this->actingAs($organizer)->get(route('organizer.waiting'));
        $waitingResponse->assertStatus(200);
        $waitingResponse->assertSee('Akun Menunggu Persetujuan');
    }

    /**
     * Test 2: Organizer yang disetujui bisa mengakses dashboard utama.
     */
    public function test_approved_organizer_can_access_dashboard()
    {
        $organizer = User::create([
            'name' => 'Organizer Active',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
            'organization_name' => 'Active Org',
            'organization_slug' => 'active-org',
        ]);

        $response = $this->actingAs($organizer)->get(route('organizer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Organizer');

        // Pastikan jika mengakses halaman waiting, diarahkan kembali ke dashboard
        $waitingResponse = $this->actingAs($organizer)->get(route('organizer.waiting'));
        $waitingResponse->assertRedirect(route('organizer.dashboard'));
    }

    /**
     * Test 3: Celah Keamanan Multi-Tenant - Organizer A tidak bisa edit/hapus event milik Organizer B.
     */
    public function test_organizer_cannot_modify_other_organizer_events()
    {
        $organizerA = User::create([
            'name' => 'Org A',
            'email' => 'orga@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        $organizerB = User::create([
            'name' => 'Org B',
            'email' => 'orgb@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        // Event milik Organizer B
        $eventB = Event::create([
            'category_id' => $this->category->id,
            'organizer_id' => $organizerB->id,
            'title' => 'Event B',
            'description' => 'Desc B',
            'date' => now()->addDays(5),
            'location' => 'Jogja',
            'price' => 10000,
            'stock' => 100,
        ]);

        // Organizer A mencoba membuka halaman edit event milik B
        $response = $this->actingAs($organizerA)->get(route('organizer.events.edit', $eventB->id));
        $response->assertStatus(404); // Harus 404 karena findOrFail gagal memuat di bawah scope A

        // Organizer A mencoba mengupdate event milik B
        $updateResponse = $this->actingAs($organizerA)->put(route('organizer.events.update', $eventB->id), [
            'category_id' => $this->category->id,
            'title' => 'Hacked Title',
            'description' => 'Hacked Desc',
            'date' => now()->addDays(5)->toDateTimeString(),
            'location' => 'Hack Location',
            'price' => 5000,
            'stock' => 10,
        ]);
        $updateResponse->assertStatus(404);

        // Pastikan data di DB tidak berubah
        $this->assertDatabaseHas('events', [
            'id' => $eventB->id,
            'title' => 'Event B',
        ]);

        // Organizer A mencoba menghapus event milik B
        $deleteResponse = $this->actingAs($organizerA)->delete(route('organizer.events.destroy', $eventB->id));
        $deleteResponse->assertStatus(404);

        // Pastikan event B masih eksis
        $this->assertDatabaseHas('events', [
            'id' => $eventB->id,
        ]);
    }

    /**
     * Test 4: Celah Keamanan Multi-Tenant - Organizer A tidak bisa memodifikasi kupon milik Organizer B.
     */
    public function test_organizer_cannot_modify_other_organizer_coupons()
    {
        $organizerA = User::create([
            'name' => 'Org A',
            'email' => 'orga@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        $organizerB = User::create([
            'name' => 'Org B',
            'email' => 'orgb@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        $couponB = Coupon::create([
            'organizer_id' => $organizerB->id,
            'code' => 'DISKONB',
            'type' => 'fixed',
            'value' => 5000,
            'is_active' => true,
        ]);

        // Organizer A mencoba toggle kupon milik B
        $toggleResponse = $this->actingAs($organizerA)->patch(route('organizer.coupons.toggle', $couponB->id));
        $toggleResponse->assertStatus(404);

        // Organizer A mencoba menghapus kupon milik B
        $deleteResponse = $this->actingAs($organizerA)->delete(route('organizer.coupons.destroy', $couponB->id));
        $deleteResponse->assertStatus(404);

        // Pastikan kupon B masih aktif dan ada di DB
        $this->assertDatabaseHas('coupons', [
            'id' => $couponB->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test 5: Analitik Dashboard Organizer terisolasi hanya untuk event & transaksi miliknya.
     */
    public function test_organizer_dashboard_metrics_are_properly_scoped()
    {
        $organizerA = User::create([
            'name' => 'Org A',
            'email' => 'orga@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        $organizerB = User::create([
            'name' => 'Org B',
            'email' => 'orgb@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);

        // Event milik A (mendatang)
        $eventA = Event::create([
            'category_id' => $this->category->id,
            'organizer_id' => $organizerA->id,
            'title' => 'Event A',
            'description' => 'Desc A',
            'date' => now()->addDays(5),
            'location' => 'Jogja',
            'price' => 10000,
            'stock' => 100,
        ]);

        // Event milik B (mendatang)
        $eventB = Event::create([
            'category_id' => $this->category->id,
            'organizer_id' => $organizerB->id,
            'title' => 'Event B',
            'description' => 'Desc B',
            'date' => now()->addDays(10),
            'location' => 'Sleman',
            'price' => 20000,
            'stock' => 50,
        ]);

        // Transaksi Lunas untuk Event A (milik Organizer A)
        Transaction::create([
            'event_id' => $eventA->id,
            'order_id' => 'TRX-A-1',
            'customer_name' => 'Customer A1',
            'customer_email' => 'customer_a1@example.com',
            'customer_phone' => '08123456789',
            'total_price' => 10000,
            'status' => 'success',
        ]);

        // Transaksi Pending untuk Event A
        Transaction::create([
            'event_id' => $eventA->id,
            'order_id' => 'TRX-A-2',
            'customer_name' => 'Customer A2',
            'customer_email' => 'customer_a2@example.com',
            'customer_phone' => '08123456780',
            'total_price' => 10000,
            'status' => 'Pending',
        ]);

        // Transaksi Lunas untuk Event B (milik Organizer B)
        Transaction::create([
            'event_id' => $eventB->id,
            'order_id' => 'TRX-B-1',
            'customer_name' => 'Customer B1',
            'customer_email' => 'customer_b1@example.com',
            'customer_phone' => '08123456781',
            'total_price' => 40000, // beli 2 tiket
            'status' => 'settlement',
        ]);

        $response = $this->actingAs($organizerA)->get(route('organizer.dashboard'));
        $response->assertStatus(200);

        // Ambil data variabel yang dikirim ke view
        $viewData = $response->original->getData();

        // Pastikan Pendapatan A hanya menghitung TRX-A-1 (Rp 10.000), bukan TRX-B-1 (Rp 40.000)
        $this->assertEquals(10000, $viewData['totalRevenue']);
        // Pastikan Tiket Terjual A hanya menghitung TRX-A-1 (1 buah)
        $this->assertEquals(1, $viewData['ticketsSold']);
        // Pastikan Event Mendatang A hanya menghitung Event A (1 buah)
        $this->assertEquals(1, $viewData['upcomingEvents']);
        // Pastikan Pesanan Pending A hanya menghitung TRX-A-2 (1 buah)
        $this->assertEquals(1, $viewData['pendingOrders']);
    }

    /**
     * Test 6: Verifikasi alur persetujuan admin benar-benar mengubah status dan mengirimkan email.
     */
    public function test_admin_can_approve_and_reject_organizer_with_emails()
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin Super',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $organizer = User::create([
            'name' => 'Penyelenggara Baru',
            'email' => 'org_baru@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => false,
            'organization_name' => 'Penyelenggara Event',
        ]);

        // Admin menyetujui organizer
        $approveResponse = $this->actingAs($admin)->patch(route('admin.organizers.approve', $organizer->id));
        $approveResponse->assertRedirect();

        $organizer->refresh();
        $this->assertTrue($organizer->is_approved);

        // Pastikan email persetujuan terkirim
        Mail::assertSent(OrganizerApprovedMail::class, function ($mail) use ($organizer) {
            return $mail->hasTo($organizer->email) && $mail->organizer->id === $organizer->id;
        });

        // Admin menangguhkan/reject organizer
        $rejectResponse = $this->actingAs($admin)->patch(route('admin.organizers.reject', $organizer->id));
        $rejectResponse->assertRedirect();

        $organizer->refresh();
        $this->assertFalse($organizer->is_approved);

        // Pastikan email penangguhan terkirim
        Mail::assertSent(OrganizerRejectedMail::class, function ($mail) use ($organizer) {
            return $mail->hasTo($organizer->email) && $mail->organizer->id === $organizer->id;
        });
    }
}
