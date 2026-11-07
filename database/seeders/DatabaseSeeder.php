<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TicketService;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ============================================================
        // 1. ADMIN
        // ============================================================
        User::firstOrCreate(['email' => 'admin@amikom.ac.id'], [
            'name' => 'Admin Amikom',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        // ============================================================
        // 2. ORGANIZERS (Sudah disetujui)
        // ============================================================
        $organizer1 = User::updateOrCreate(['email' => 'himpsi@amikom.ac.id'], [
            'name' => 'Ketua HIMPSI',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZER,
            'is_approved' => true,
            'organization_name' => 'HIMPSI Amikom',
            'organization_slug' => 'himpsi-amikom',
            'organization_bio' => 'Himpunan Mahasiswa Program Studi Informatika Universitas Amikom Yogyakarta.',
        ]);

        $organizer2 = User::updateOrCreate(['email' => 'bem@amikom.ac.id'], [
            'name' => 'Ketua BEM',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORGANIZER,
            'is_approved' => true,
            'organization_name' => 'BEM AMIKOM',
            'organization_slug' => 'bem-amikom',
            'organization_bio' => 'Badan Eksekutif Mahasiswa Universitas Amikom Yogyakarta.',
        ]);

        $organizers = [$organizer1, $organizer2];
        $organizations = [];

        foreach ($organizers as $organizer) {
            $organization = Organization::updateOrCreate(
                ['owner_id' => $organizer->id],
                [
                    'name' => $organizer->organization_name,
                    'slug' => $organizer->organization_slug,
                    'bio' => $organizer->organization_bio,
                    'status' => Organization::STATUS_APPROVED,
                    'approved_at' => now(),
                ]
            );
            $organization->members()->syncWithoutDetaching([
                $organizer->id => ['role' => 'owner'],
            ]);
            $organizations[$organizer->id] = $organization;
        }

        // ============================================================
        // 3. CATEGORIES
        // ============================================================
        $catSeminar = Category::firstOrCreate(['slug' => 'seminar-it'], ['name' => 'Seminar IT']);
        $catEntertain = Category::firstOrCreate(['slug' => 'entertainment'], ['name' => 'Entertainment']);
        $catWorkshop = Category::firstOrCreate(['slug' => 'workshop'], ['name' => 'Workshop']);
        $catBusiness = Category::firstOrCreate(['slug' => 'business-startup'], ['name' => 'Business & Startup']);

        $allCategories = [$catSeminar, $catEntertain, $catWorkshop, $catBusiness];

        // ============================================================
        // 4. EVENTS (DINAMIS Sesuai Skema Tabel SQL)
        // ============================================================
        $eventTitles = [
            'Hackathon Developer Amikom 2026',
            'AI Future Summit & Machine Learning',
            'Jazz Night Live in Amikom Outdoor',
            'Workshop Gratis: Belajar Laravel dari Nol',
            'UI/UX Design Masterclass & Portfolio Review',
            'Cyber Security Awareness & Ethical Hacking',
            'Amikom Digital Creative Fest 2026',
            'Tech Startup Founder Meetup & Pitching',
        ];

        $createdEvents = [];

        foreach ($eventTitles as $index => $title) {
            $isFree = ($index === 3); // Event ke-4 dibuat gratis untuk tes bypass Midtrans
            $price = $isFree ? 0 : $faker->randomElement([25000, 50000, 75000, 100000]);

            // Presale Diskon
            $hasPresale = $index >= 2 && ! $isFree && $faker->boolean(60);
            $presalePrice = $hasPresale ? ($price * 0.8) : null;

            // Dua event pertama sengaja sudah selesai agar review demo memenuhi H+1.
            $eventDate = $index < 2
                ? now()->subDays(10 - $index)
                : $faker->dateTimeBetween('+3 days', '+2 months');
            $eventOrganizer = $organizers[$index % count($organizers)];

            $event = Event::updateOrCreate(
                ['title' => $title],
                [
                    'category_id' => $allCategories[$index % count($allCategories)]->id,
                    'organizer_id' => $eventOrganizer->id,
                    'organization_id' => $organizations[$eventOrganizer->id]->id,
                    'description' => $faker->paragraphs(3, true),
                    'date' => $eventDate,
                    'location' => $faker->randomElement(['Amikom Outdoor Stage', 'Cinema Unit 6', 'Inkubator Amikom', 'Auditorium Kampus 1', 'Lab Komputer 3']),
                    'price' => $price,
                    'early_bird_price' => null,
                    'early_bird_until' => null,
                    'presale_price' => $presalePrice,
                    'presale_until' => $hasPresale ? $faker->dateTimeBetween('now', $eventDate) : null,
                    'stock' => $faker->numberBetween(20, 150),
                    'poster_path' => null,
                ]
            );

            $createdEvents[] = $event;
        }

        // ============================================================
        // 5. DEMO BUYER
        // ============================================================
        $buyer = User::firstOrCreate(['email' => 'budi@example.com'], [
            'name' => 'Budi Santoso',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // ============================================================
        // 6. DEMO TRANSACTIONS (Presisi dengan Kolom SQL)
        // ============================================================
        $trx1 = Transaction::updateOrCreate(['order_id' => 'TRX-DEMO-001'], [
            'event_id' => $createdEvents[0]->id,
            'organization_id' => $createdEvents[0]->organization_id,
            'user_id' => $buyer->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'quantity' => 1,
            'total_price' => 55000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now()->subDays(9),
            'stock_decremented_at' => now()->subDays(9),
            'ticket_status' => 'unused',
            'certificate_sent' => false,
            'stock_released' => false,
            'whatsapp_recovery_sent' => false,
            'whatsapp_ticket_sent' => false,
        ]);

        $trx2 = Transaction::updateOrCreate(['order_id' => 'TRX-DEMO-002'], [
            'event_id' => $createdEvents[1]->id,
            'organization_id' => $createdEvents[1]->organization_id,
            'user_id' => $buyer->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'quantity' => 2,
            'total_price' => 205000,
            'status' => 'success',
            'payment_status' => Transaction::PAYMENT_PAID,
            'paid_at' => now()->subDays(8),
            'stock_decremented_at' => now()->subDays(8),
            'ticket_status' => 'used', // Siap untuk tes unduh E-Sertifikat
            'certificate_sent' => false,
            'stock_released' => false,
            'whatsapp_recovery_sent' => false,
            'whatsapp_ticket_sent' => false,
        ]);

        app(TicketService::class)->issueFor($trx1);
        app(TicketService::class)->issueFor($trx2);

        // ============================================================
        // 7. DEMO REVIEWS
        // ============================================================
        Review::updateOrCreate(['transaction_id' => $trx1->id], [
            'event_id' => $createdEvents[0]->id,
            'organization_id' => $createdEvents[0]->organization_id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'Event luar biasa! Hackathon-nya sangat menantang dan edukatif.',
            'status' => Review::STATUS_PUBLISHED,
        ]);

        Review::updateOrCreate(['transaction_id' => $trx2->id], [
            'event_id' => $createdEvents[1]->id,
            'organization_id' => $createdEvents[1]->organization_id,
            'user_id' => $buyer->id,
            'rating' => 4,
            'comment' => 'Pembicaranya sangat inspiratif. Sayang durasinya terlalu singkat.',
            'status' => Review::STATUS_PUBLISHED,
        ]);

        // ============================================================
        // 8. DEMO COUPONS
        // ============================================================
        Coupon::updateOrCreate(['code' => 'AMIKOM10'], [
            'organizer_id' => $organizer1->id,
            'organization_id' => $organizations[$organizer1->id]->id,
            'type' => 'percent',
            'value' => 10,
            'max_usage' => 50,
            'used_count' => 0,
            'is_active' => true,
        ]);

        Coupon::updateOrCreate(['code' => 'DISKON5K'], [
            'organizer_id' => $organizer2->id,
            'organization_id' => $organizations[$organizer2->id]->id,
            'type' => 'fixed',
            'value' => 5000,
            'max_usage' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);
    }
}
