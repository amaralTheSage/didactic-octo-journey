<?php

namespace App\Providers;

use App\Enums\UserRoles;
use App\Models\CampaignAnnouncement;
use App\Models\User;
use App\Observers\CampaignAnnouncementObserver;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
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

        TextInput::macro('moneyBRL', function () {
            /** @var TextInput $this */
            return $this
                ->prefix('R$')
                ->placeholder('0,00')
                ->mask(RawJs::make(<<<'JS'
                $money($input, ',', '.', 2)
            JS))
                ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format((float) $state, 2, ',', '.') : $state)
                ->dehydrateStateUsing(fn ($state) => (float) str_replace(['.', ','], ['', '.'], $state));
        });

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
