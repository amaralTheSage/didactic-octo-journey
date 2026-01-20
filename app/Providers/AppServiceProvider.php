<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;
use App\Observers\CampaignObserver;
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
                ->formatStateUsing(fn($state) => is_numeric($state) ? number_format((float) $state, 2, ',', '.') : $state)
                ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], ['', '.'], $state));
        });

        Campaign::observe(CampaignObserver::class);

        Gate::define('is_admin', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        Gate::define('is_agency', function (User $user) {
            return $user->role === UserRole::AGENCY;
        });

        Gate::define('is_company', function (User $user) {
            return $user->role === UserRole::COMPANY;
        });

        Gate::define('is_curator', function (User $user) {
            return $user->role === UserRole::CURATOR;
        });

        Gate::define('is_company_or_curator', function (User $user) {
            return $user->role === UserRole::COMPANY || $user->role === UserRole::CURATOR;
        });

        Gate::define('is_influencer', function (User $user) {
            return $user->role === UserRole::INFLUENCER;
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
