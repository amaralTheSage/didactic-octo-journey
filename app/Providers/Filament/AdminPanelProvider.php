<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->navigationGroups([
                'Mídia',
            ])
            ->topbar(false)
            ->sidebarCollapsibleOnDesktop()
            ->colors(['primary' => 'oklch(0.3979 0.0632 231.2552)', 'secondary' => 'oklch(0.6546 0.1119 207.9244)'])
            ->font('Figtree')
            ->path('dashboard')
            ->databaseNotifications()
            ->databaseNotificationsPolling('45s')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationItems([
                NavigationItem::make('Chat')
                    ->url('/chats')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->sort(3),
            ])
            ->profile(EditProfile::class)
            ->registration(Register::class)
            ->login()
            ->brandLogo(asset('assets/hubinflu-logo.png'))->brandLogoHeight('28px')
            ->brandName('HubInflu')
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            // ->renderHook(
            //     PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
            //     fn(): string => Blade::render(<<<'BLADE'
            //         <div x-data="{ role: @entangle('data.role') }">
            //             <div x-show="role === 'influencer'">
            //                 <x-filament-socialite::buttons />
            //             </div>
            //         </div>
            //     BLADE)
            // )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('instagram')
                            ->label('Instagram')
                            ->icon('fab-instagram')
                            ->color('#C13584')
                            ->outlined(false)
                            ->stateless(false),
                    ])
                    ->registration(true),

            ]);
    }
}
