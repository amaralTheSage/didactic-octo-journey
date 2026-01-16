<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRoles;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\InfluencerInfo;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\Events\Registered;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
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
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Leandrocfe\FilamentPtbrFormFields\Money;
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

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');
            $data = $this->form->getState();
            $this->callHook('afterValidate');

            $isInfluencer = ($data['role'] === 'influencer');

            $influencerData = $data['influencer_data'] ?? [];
            $subcategories = $influencerData['subcategories'] ?? [];

            $data = $this->mutateFormDataBeforeRegister($data);
            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            if ($isInfluencer) {
                // Save Influencer Profile
                InfluencerInfo::create([
                    'user_id' => $user->id,
                    ...$influencerData,
                ]);

                // Save Subcategories
                if (! empty($subcategories)) {
                    $user->subcategories()->sync($subcategories);
                }

                // Save Attributes from Step 3
                if (! empty($data['attribute_values'])) {
                    $pivotData = [];
                    foreach ($data['attribute_values'] as $row) {
                        $ids = (array) ($row['attribute_value_id'] ?? []);
                        foreach ($ids as $id) {
                            if ($id) {
                                $pivotData[$id] = ['title' => $row['title'] ?? null];
                            }
                        }
                    }
                    $user->attribute_values()->sync($pivotData);
                }

                // Notify Agency
                $agencyId = $influencerData['agency_id'] ?? null;
                if ($agencyId) {
                    $agency = User::find($agencyId);
                    if ($agency) {
                        $agency->notify(
                            Notification::make()
                                ->title('Convite de associação de ' . $user->name)
                                ->body('Revise o pedido na página de influenciadores.')
                                ->toDatabase()
                        );
                    }
                }
            }

            $this->form->model($user)->saveRelationships();
            $this->callHook('afterRegister');

            return $user;
        });

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

            Select::make('role')
                ->label('Sou...')
                ->helperText('Defina como você vai utilizar a plataforma')
                ->options([
                    'influencer' => 'Influenciador',
                    'company' => 'Empresa',
                    'agency' => 'Agência',
                    'curator' => 'Curadoria'
                ])
                ->required()
                ->extraFieldWrapperAttributes(fn(Get $get) => !filled($get('role')) ? ['class' => '!mt-8 mb-16'] : [])
                ->live(),

            Wizard::make(
                [
                    Step::make('Informações Básicas')
                        ->schema([
                            $this->getBaseInfoColumn(),
                        ]),

                    Step::make('Informações de Influenciador')->schema([
                        $this->getInfluencerColumn(),
                    ])->visible(fn(Get $get) => $get('role') === 'influencer'),

                    Step::make('Atributos')->schema([
                        $this->getAttributesRepeater(),
                    ])->visible(fn(Get $get) => $get('role') === 'influencer'),
                ]
            )
                ->visible(fn(Get $get) => filled($get('role'))),
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    //
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------

    protected function getBaseInfoColumn(): Group
    {
        return Group::make()
            ->schema([


                Group::make()->visible(fn(Get $get) => filled($get('role')))

                    ->schema([
                        FileUpload::make('avatar')
                            ->hiddenLabel()
                            ->disk('public')
                            ->directory('avatars')
                            ->alignCenter()
                            ->image()
                            ->avatar()
                            ->circleCropper(),

                        $this->getNameFormComponent(),

                        Textarea::make('bio')
                            ->rows(5)
                            ->placeholder('Sou criador de conteúdo...')
                            ->required(),


                        TextInput::make('pix_address')->label('Endereço Pix'),

                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
            ]);
    }

    protected function getInfluencerColumn(): Group
    {
        return Group::make()
            ->statePath('influencer_data')
            ->dehydrated()
            ->schema([
                Select::make('subcategories')
                    ->multiple()
                    ->label('Categorias')
                    ->options(
                        Category::with('subcategories')->get()
                            ->mapWithKeys(fn($category) => [
                                $category->title => $category->subcategories
                                    ->pluck('title', 'id')
                                    ->toArray(),
                            ])
                            ->toArray()
                    ),

                Select::make('agency_id')
                    ->label('Agência Vinculada')
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(
                        fn(string $search): array => User::where('role', UserRoles::Agency)
                            ->where('name', 'ilike', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value) => User::find($value)?->name
                    ),

                Group::make()
                    ->statePath('location_data')->columns(2)
                    ->schema([
                        Select::make('country')
                            ->label('País')
                            ->placeholder('Selecione um país')
                            ->options([
                                'BR' => 'Brasil',
                                'US' => 'Estados Unidos',
                                'AR' => 'Argentina',
                                'UY' => 'Uruguai',
                                'PY' => 'Paraguai',
                            ])->columnSpan(2)
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('state', null);
                                $set('city', null);
                            }),

                        Select::make('state')
                            ->label('Estado')
                            ->placeholder('Selecione um estado')
                            ->options(
                                fn() => Http::get('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
                                    ->collect()
                                    ->sortBy('nome')
                                    ->pluck('nome', 'sigla')
                                    ->toArray()
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('city', null))
                            ->disabled(fn(Get $get) => $get('country') !== 'BR'),


                        Select::make('city')
                            ->label('Cidade')
                            ->placeholder('Selecione uma cidade')
                            ->options(function (Get $get) {
                                if (! $get('state')) {
                                    return [];
                                }

                                return Http::get(
                                    "https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$get('state')}/municipios"
                                )
                                    ->collect()
                                    ->pluck('nome', 'nome')
                                    ->toArray();
                            })
                            ->searchable()
                            ->disabled(fn(Get $get) => $get('country') !== 'BR'),
                    ]),

                Section::make('Redes Sociais')->collapsed()->collapsible()
                    ->schema([
                        Group::make()->columns(2)->schema([
                            TextInput::make('instagram')->placeholder('@Instagram'),
                            TextInput::make('instagram_followers')->label('Seguidores')->numeric(),

                            TextInput::make('youtube')->placeholder('@YouTube'),
                            TextInput::make('youtube_followers')->label('Seguidores')->numeric(),

                            TextInput::make('tiktok')->placeholder('@TikTok'),
                            TextInput::make('tiktok_followers')->label('Seguidores')->numeric(),

                            TextInput::make('twitter')->placeholder('@Twitter'),
                            TextInput::make('twitter_followers')->label('Seguidores')->numeric(),

                            TextInput::make('facebook')->placeholder('@Facebook'),
                            TextInput::make('facebook_followers')->label('Seguidores')->numeric(),
                        ]),
                    ]),

                Section::make('Tabela de Preços')
                    ->schema([
                        Money::make('reels_price')
                            ->label('Reels')
                            ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], ['', '.'], $state)),

                        Money::make('stories_price')
                            ->label('Stories')
                            ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], ['', '.'], $state)),

                        Money::make('carrousel_price')
                            ->label('Carrossel')
                            ->dehydrateStateUsing(fn($state) => (float) str_replace(['.', ','], ['', '.'], $state)),

                        TextInput::make('commission_cut')
                            ->label('Comissão')
                            ->suffix('%')
                            ->extraInputAttributes(['style' => 'text-align: right;'])
                            ->mask('999')
                            ->minValue(0)
                            ->maxValue(100),
                    ])->columns(4),
            ])
            ->visible(fn(Get $get) => $get('role') === 'influencer');
    }

    protected function getAttributesRepeater()
    {
        return Repeater::make('attribute_values')
            ->compact()
            ->collapsible()
            ->collapsed()
            ->label('Atributos Gerais')
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->default(function () {
                // Simply load all attributes for a fresh registration
                return Attribute::all()->map(fn($attribute) => [
                    'attribute_id' => $attribute->id,
                    'attribute_value_id' => [],
                    'title' => null,
                ])->toArray();
            })
            ->table([
                TableColumn::make('Atributo'),
                TableColumn::make('Valor'),
            ])
            ->schema([
                Hidden::make('attribute_id'),

                TextEntry::make('attribute_title')
                    ->label('Atributo')
                    ->state(fn(Get $get) => Attribute::find($get('attribute_id'))?->title),

                Group::make()->schema([
                    Select::make('attribute_value_id')
                        ->label('Valor')
                        ->options(
                            fn(Get $get) => AttributeValue::where('attribute_id', $get('attribute_id'))
                                ->pluck('title', 'id')
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (filled($state)) {
                                $hasOutro = AttributeValue::whereIn('id', $state)
                                    ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                    ->exists();

                                if (! $hasOutro) {
                                    $set('title', null);
                                }
                            }
                        })
                        ->columnSpan(
                            fn(Get $get) => AttributeValue::whereIn('id', (array) ($get('attribute_value_id') ?? []))
                                ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                ->exists() ? 1 : 2
                        ),

                    TextInput::make('title')
                        ->label('Especifique')
                        ->placeholder('Especifique...')
                        ->visible(
                            fn(Get $get) => AttributeValue::whereIn('id', (array) ($get('attribute_value_id') ?? []))
                                ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                ->exists()
                        )
                        ->columnSpan(1),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::FourExtraLarge;
    }

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
        if (isset($data['influencer_data']['location_data'])) {
            $loc = $data['influencer_data']['location_data'];

            $data['influencer_data']['location'] = implode('|', [
                $loc['country'] ?? '',
                $loc['state'] ?? '',
                $loc['city'] ?? '',
            ]);

            unset($data['influencer_data']['location_data']);
        }

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
