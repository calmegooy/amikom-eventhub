<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class GoogleSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_login_page_has_continue_with_google_button(): void
    {
        $this->get(route('user.login'))
            ->assertOk()
            ->assertSee('Continue with Google');
    }

    public function test_login_page_explains_when_google_sso_is_not_configured(): void
    {
        config()->set('services.google.client_id');
        config()->set('services.google.client_secret');

        $this->get(route('user.login'))
            ->assertOk()
            ->assertSee('Google SSO belum dikonfigurasi')
            ->assertDontSee('href="'.route('user.google.redirect').'"', false);
    }

    public function test_google_redirect_fails_safely_when_credentials_are_missing(): void
    {
        config()->set('services.google.client_id');
        config()->set('services.google.client_secret');
        Socialite::shouldReceive('driver')->never();

        $this->get(route('user.google.redirect'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_google_redirect_uses_stateful_provider(): void
    {
        $provider = Mockery::mock(Provider::class);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['openid', 'profile', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));

        $this->get(route('user.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_verified_google_user_is_created_and_authenticated(): void
    {
        $this->mockGoogleUser(
            id: 'google-123',
            email: 'new-google-user@example.com',
            verified: true
        );

        $this->get(route('user.google.callback'))->assertRedirect(route('home'));

        $user = User::where('google_id', 'google-123')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('user', $user->role);
    }

    public function test_authenticated_google_user_name_is_shown_in_the_public_header(): void
    {
        $this->mockGoogleUser(
            id: 'google-header',
            email: 'google-header@example.com',
            verified: true
        );

        $this->get(route('user.google.callback'))->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hai, Google User')
            ->assertSee('title="Masuk sebagai Google User"', false)
            ->assertSee('src="https://example.com/avatar.png"', false)
            ->assertSee('alt="Foto profil Google User"', false);
    }

    public function test_verified_google_email_can_link_normal_customer_account(): void
    {
        $customer = User::create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $this->mockGoogleUser('google-existing', 'existing@example.com', true);

        $this->get(route('user.google.callback'))->assertRedirect(route('home'));

        $this->assertSame('google-existing', $customer->fresh()->google_id);
        $this->assertAuthenticatedAs($customer->fresh());
    }

    public function test_privileged_account_is_not_auto_linked_by_email(): void
    {
        $organizer = User::create([
            'name' => 'Organizer',
            'email' => 'organizer-google@example.com',
            'password' => bcrypt('password'),
            'role' => 'organizer',
            'is_approved' => true,
        ]);
        $this->mockGoogleUser('google-privileged', $organizer->email, true);

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertNull($organizer->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_privileged_account_already_containing_google_id_cannot_use_customer_sso(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-google@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'google_id' => 'google-admin',
        ]);
        $this->mockGoogleUser('google-admin', $admin->email, true);

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_existing_customer_link_is_not_silently_replaced_by_another_google_id(): void
    {
        $customer = User::create([
            'name' => 'Linked Customer',
            'email' => 'linked@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'google_id' => 'google-original',
        ]);
        $this->mockGoogleUser('google-different', $customer->email, true);

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertSame('google-original', $customer->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_google_callback_rejects_email_collision_for_an_existing_google_id(): void
    {
        $linkedCustomer = User::create([
            'name' => 'Linked Customer',
            'email' => 'old-google@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'google_id' => 'google-linked',
        ]);
        User::create([
            'name' => 'Other Customer',
            'email' => 'current-google@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);
        $this->mockGoogleUser('google-linked', 'current-google@example.com', true);

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertSame('old-google@example.com', $linkedCustomer->fresh()->email);
        $this->assertGuest();
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->mockGoogleUser('google-unverified', 'unverified@example.com', false);

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
    }

    public function test_cancelled_google_callback_returns_to_login_without_calling_provider(): void
    {
        Socialite::shouldReceive('driver')->never();

        $this->get(route('user.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_provider_failure_returns_a_generic_login_error(): void
    {
        $provider = Mockery::mock(Provider::class);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('provider failed'));

        $this->get(route('user.google.callback'))
            ->assertRedirect(route('user.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function mockGoogleUser(string $id, string $email, bool $verified): void
    {
        $googleUser = (new SocialiteUser)
            ->setRaw(['email_verified' => $verified])
            ->map([
                'id' => $id,
                'name' => 'Google User',
                'email' => $email,
                'avatar' => 'https://example.com/avatar.png',
            ]);

        $provider = Mockery::mock(Provider::class);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
    }
}
