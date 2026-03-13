<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Group;
use App\Models\GroupMember;
use App\Observers\EventObserver;
use App\Observers\GroupMemberObserver;
use App\Policies\EventPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\GroupPolicy;


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
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);

        Event::observe(EventObserver::class);
        GroupMember::observe(GroupMemberObserver::class);
    }
}
