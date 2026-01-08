<?php

namespace App\Providers;

use App\Models\CampaignAnnouncement;
use App\Models\User;
use App\Observers\CampaignAnnouncementObserver;
use App\Enums\UserRoles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Instagram\InstagramExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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

        CampaignAnnouncement::observe(CampaignAnnouncementObserver::class);

        Gate::define('is_admin', function (User $user) {
            return $user->role === UserRoles::Admin;
        });

        Gate::define('is_agency', function (User $user) {
            return $user->role === UserRoles::Agency;
        });

        Gate::define('is_company', function (User $user) {
            return $user->role === UserRoles::Company;
        });

        Gate::define('is_influencer', function (User $user) {
            return $user->role === UserRoles::Influencer;
        });

        Gate::define('is_influencers_agency', function (User $user, User $influencer) {
            return Gate::allows('is_agency') && $influencer->influencer_info->agency_id === Auth::id();
        });

        Event::listen(
            SocialiteWasCalled::class,
            InstagramExtendSocialite::class
        );
    }
}
