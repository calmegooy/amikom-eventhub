<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Policies\EventPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\TicketPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
    }
}
