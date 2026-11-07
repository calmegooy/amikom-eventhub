<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMemberPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_organization_member_can_manage_and_scan_organization_event(): void
    {
        [$owner, $organization, $event] = $this->organizationWithEvent('shared');
        $member = User::create([
            'name' => 'Committee Member',
            'email' => 'committee-member@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $organization->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->get(route('organizer.events.edit', $event))
            ->assertOk()
            ->assertSee($event->title);

        $this->actingAs($member)
            ->get(route('organizer.scanner', $event))
            ->assertOk()
            ->assertSee($event->title);

        $this->actingAs($owner)
            ->get(route('organizer.dashboard'))
            ->assertOk();
    }

    public function test_member_of_other_organization_cannot_manage_event(): void
    {
        [, , $eventA] = $this->organizationWithEvent('a');
        [$ownerB] = $this->organizationWithEvent('b');

        $this->actingAs($ownerB)
            ->get(route('organizer.events.edit', $eventA))
            ->assertNotFound();

        $this->actingAs($ownerB)
            ->get(route('organizer.scanner', $eventA))
            ->assertForbidden();
    }

    private function organizationWithEvent(string $suffix): array
    {
        $owner = User::create([
            'name' => 'Owner '.strtoupper($suffix),
            'email' => 'owner-'.$suffix.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $organization = Organization::create([
            'owner_id' => $owner->id,
            'name' => 'Organization '.strtoupper($suffix),
            'slug' => 'organization-'.$suffix,
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $organization->members()->attach($owner->id, ['role' => 'owner']);
        $category = Category::firstOrCreate(
            ['slug' => 'policy-category'],
            ['name' => 'Policy Category']
        );
        $event = Event::create([
            'category_id' => $category->id,
            'organizer_id' => $owner->id,
            'organization_id' => $organization->id,
            'title' => 'Organization Event '.strtoupper($suffix),
            'description' => 'Policy test event.',
            'date' => now()->addWeek(),
            'location' => 'Yogyakarta',
            'price' => 50000,
            'stock' => 10,
        ]);

        return [$owner, $organization, $event];
    }
}
