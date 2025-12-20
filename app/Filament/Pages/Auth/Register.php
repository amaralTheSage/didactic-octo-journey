<?php

namespace App\Filament\Pages\Auth;

use App\Models\Category;
use App\Models\InfluencerInfo;
use App\Models\Subcategory;
use App\Models\User;
use App\UserRoles;
use Closure;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\Events\Registered;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use LogicException;

/**
 * @property-read Action $loginAction
 * @property-read Schema $form
 */
class Register extends SimplePage
{
    use CanUseDatabaseTransactions;
    use WithRateLimiting;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    /**
     * @var class-string<Model>
     */
    protected string $userModel;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }



        $user = $this->wrapInDatabaseTransaction(function (): Model {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();


            $this->callHook('afterValidate');

            $influencerData = $data['influencer_data'] ?? [];

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            if ($data['role'] === 'influencer') {
                InfluencerInfo::create([
                    'user_id' => $user->id,
                    ...$influencerData,
                ]);
            }

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        $data = $this->form->getState();

            if ($data['role'] === 'influencer' && isset($data['subcategories']))  {
        
        foreach ($data['subcategories'] as $sub) {
            $user->subcategories()->attach($sub);
        }
    }


        $agency = User::whereId($data['influencer_data']['agency_id'])->first();

        $agency->notify(
            Notification::make()->title('Convite de associação de ' . $user->name)->body('Revise o pedido na página de influenciadores.')->toDatabase()
        );

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::auth/pages/register.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::auth/pages/register.notifications.throttled') ?: []) ? __('filament-panels::auth/pages/register.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {

        return $this->getUserModel()::create($data);
    }

    protected function sendEmailVerificationNotification(Model $user): void
    {
        if (! $user instanceof MustVerifyEmail) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new LogicException("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getVerifyEmailUrl($user);

        $user->notify($notification);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // FORM
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('avatar')
                ->label('Avatar')
                ->disk('public')
                ->directory('avatars')
                ->alignCenter()
                ->image()
                ->avatar()
                ->circleCropper()
                ->imageEditor()
                ->imagePreviewHeight('100'),

            $this->getNameFormComponent(),
            Textarea::make('bio')->rows(5)->placeholder('Sou Youtuber e Streamer na área da tecnologia...')->required(),

            Section::make()->schema([
                Select::make('role')
                    ->options([
                        'influencer' => 'Influenciador',
                        'company' => 'Empresa',
                        'agency' => 'Agency',
                    ])
                    ->required()
                    ->live(),

                Section::make('Canais de Mídia Social')
                    ->description('Informe o @ do seu perfil e número de seguidores em cada plataforma.')
                    ->schema([

                        Select::make('subcategories')
                            ->multiple()
                            ->label('Categoria')
                            ->options(
                                Category::with('subcategories')->get()
                                    ->mapWithKeys(function ($category) {
                                        return [
                                            $category->title => $category->subcategories
                                                ->filter(fn($subcategory) => $subcategory->title !== null)
                                                ->pluck('title', 'id')
                                                ->toArray(),
                                        ];
                                    })
                                    ->toArray()
                            )->rules([
                                fn(): Closure => function (string $attribute, $value, Closure $fail) {
                                    $categories = Subcategory::whereIn('id', $value)
                                        ->distinct('category_id')
                                        ->count('category_id');

                                    if ($categories > 1) {
                                        $fail('Selecione subcategorias de apenas uma categoria.');
                                    }
                                },
                            ]),

                        Group::make()->columns(2)->dehydrated()->statePath('influencer_data')->schema([
                            Select::make('agency_id')
                                ->label('Agência Vinculada')->columnSpan(2)
                                ->helperText('Selecione a agência responsável pelo seu perfil.')
                                ->searchable()
                                ->preload()

                                ->getSearchResultsUsing(
                                    fn(string $search): array => User::query()
                                        ->where('role', UserRoles::Agency)
                                        ->where('name', 'ilike', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->getOptionLabelUsing(fn($value): ?string => User::find($value)?->name),

                            Group::make()->columns(2)->schema([
                                TextEntry::make('handle_label')->label('@ do Perfil'),
                                TextEntry::make('followers_label')->label('Seguidores'),
                            ])->columnSpan(2),

                            Group::make()->schema([
                                TextInput::make('instagram')->hiddenLabel()->placeholder('@ do Instagram'),
                                TextInput::make('twitter')->hiddenLabel()->placeholder('@ do Twitter'),
                                TextInput::make('youtube')->hiddenLabel()->placeholder('@ do Youtube'),
                                TextInput::make('tiktok')->hiddenLabel()->placeholder('@ do TikTok'),
                                TextInput::make('facebook')->hiddenLabel()->placeholder('@ do Facebook'),
                            ])->columnSpan(1),

                            Group::make()->schema([
                                TextInput::make('instagram_followers')->hiddenLabel()->numeric(),
                                TextInput::make('twitter_followers')->hiddenLabel()->numeric(),
                                TextInput::make('youtube_followers')->hiddenLabel()->numeric(),
                                TextInput::make('tiktok_followers')->hiddenLabel()->numeric(),
                                TextInput::make('facebook_followers')->hiddenLabel()->numeric(),
                            ])->columnSpan(1),
                        ]),
                    ])
                    ->visible(fn(Get $get): bool => $get('role') === 'influencer'),
            ]),

            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    //
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::auth/pages/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->showAllValidationMessages()
            ->dehydrateStateUsing(fn($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::auth/pages/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::auth/pages/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::auth/pages/register.actions.login.label'))
            ->url(filament()->getLoginUrl());
    }

    /**
     * @return class-string<Model>
     */
    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-panels::auth/pages/register.title');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('filament-panels::auth/pages/register.heading');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('filament-panels::auth/pages/register.form.actions.register.label'))
            ->submit('register');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {

        return $data;
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return new HtmlString(__('filament-panels::auth/pages/register.actions.login.before') . ' ' . $this->loginAction->toHtml());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE),
                $this->getFormContentComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_AFTER),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('register')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }
}
