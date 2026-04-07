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
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Server;
use Illuminate\Support\Facades\URL;

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

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
                $openApi->info->setDescription(view('docs.api-introduction')->render());
            });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
