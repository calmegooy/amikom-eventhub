<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $organizer = User::updateOrCreate(
            ['email' => 'event-seeder@amikom.ac.id'],
            [
                'name' => 'Organizer Demo',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ORGANIZER,
                'is_approved' => true,
                'organization_name' => 'Amikom Event Organizer',
                'organization_slug' => 'amikom-event-organizer',
                'organization_bio' => 'Organisasi demo untuk data event praktikum.',
            ]
        );
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

        // 1. Pastikan Kategori Utama Tersedia
        $categories = [
            'Technology & AI',
            'Business & Startup',
            'Music & Art',
            'Workshop & Seminar',
            'Career & Academic',
        ];

        $categoryModels = [];
        foreach ($categories as $catName) {
            $categoryModels[] = Category::firstOrCreate([
                'name' => $catName,
                'slug' => Str::slug($catName),
            ]);
        }

        // 2. Daftar Topik Event yang Menarik & Realistis
        $eventTopics = [
            'National Seminar: Generative AI in Higher Education',
            'Workshop Hands-On: Fullstack Laravel & Vue.js',
            'Amikom Digital Creative Fest 2026',
            'Bootcamp UI/UX Design: From Zero to Hero',
            'Cyber Security Awareness & Ethical Hacking',
            'Tech Startup Founder Meetup & Pitching Day',
            'Konser Musik Kampus: Sound of Amikom',
            'Webinar Karir: Lolos BUMN & Tech Company',
        ];

        // 3. Generate Data Event Dinamis
        foreach ($eventTopics as $index => $title) {
            $isPaid = $faker->boolean(70); // 70% event berbayar, 30% gratis
            $regularPrice = $isPaid ? $faker->randomElement([25000, 50000, 75000, 100000, 150000]) : 0;

            // Logika presale/early bird dinamis jika berbayar
            $hasPresale = $isPaid && $faker->boolean(50);
            $presalePrice = $hasPresale ? ($regularPrice * 0.7) : null; // Diskon 30% untuk presale

            $startDate = $faker->dateTimeBetween('now', '+2 months');

            Event::updateOrCreate(
                ['title' => $title],
                [
                    'category_id' => $faker->randomElement($categoryModels)->id,
                    'organizer_id' => $organizer->id,
                    'organization_id' => $organization->id,
                    'description' => $faker->paragraphs(3, true),
                    'location' => $faker->randomElement(['Ruang Cinema Amikom', 'Gedung Ruang Utama', 'Auditorium Kampus 1', 'Online via Zoom Meeting', 'Amikom Hall']),
                    'date' => $startDate,
                    'stock' => $faker->numberBetween(10, 200),
                    'price' => $regularPrice,
                    'presale_price' => $presalePrice,
                    'presale_until' => $hasPresale ? $faker->dateTimeBetween('now', $startDate) : null,
                    'poster_path' => null,
                ]
            );
        }
    }
}
