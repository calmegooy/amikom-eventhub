<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin-dash@example.com', 'password' => bcrypt('password'), 'role' => User::ROLE_ADMIN]);
    }

    public function test_admin_dashboard_returns_all_required_view_variables(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertViewHasAll(['totalRevenue', 'ticketsSold', 'activeEvents', 'pendingOrders', 'totalUsers', 'recentTransactions', 'chartData']);
    }

    public function test_total_users_count_is_accurate(): void
    {
        User::create(['name' => 'User 1', 'email' => 'u1@ex.com', 'password' => bcrypt('p'), 'role' => 'user']);
        User::create(['name' => 'User 2', 'email' => 'u2@ex.com', 'password' => bcrypt('p'), 'role' => 'user']);
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $this->assertSame(3, $response->viewData('totalUsers'));
    }

    public function test_chart_data_contains_users_key_with_six_months(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $chartData = $response->viewData('chartData');
        $this->assertArrayHasKey('users', $chartData);
        $this->assertCount(6, $chartData['users']);
        $this->assertCount(6, $chartData['labels']);
        $this->assertCount(6, $chartData['revenue']);
        $this->assertCount(6, $chartData['events']);
    }

    public function test_new_user_this_month_appears_in_current_month_bucket(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $chartData = $response->viewData('chartData');
        $this->assertGreaterThanOrEqual(1, $chartData['users'][5]);
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $customer = User::create(['name' => 'Customer', 'email' => 'cust-dash@example.com', 'password' => bcrypt('password'), 'role' => 'user']);
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertRedirect();
    }
}
