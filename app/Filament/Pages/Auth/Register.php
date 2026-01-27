<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
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
use Filament\Forms\Components\ToggleButtons;
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
use Filament\Schemas\Components\Text;
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
use Illuminate\Support\Facades\Log;
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

            $data = $this->mutateFormDataBeforeRegister($data);

            $isInfluencer = ($data['role'] === 'influencer');

            $influencerData = $data['influencer_data'] ?? [];
            $subcategories = $influencerData['subcategories'] ?? [];

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
            ToggleButtons::make('role')
                ->label('Sou...')
                ->helperText('Defina como você vai utilizar a plataforma')
                ->options([
                    'influencer' => 'Influenciador',
                    'company' => 'Empresa',
                    'agency' => 'Agência',
                    'curator' => 'Curadoria',
                ])
                ->icons(function (Get $get) {
                    $selectedRole = $get('role');

                    return [
                        'influencer' => $selectedRole === 'influencer'
                            ? 'heroicon-s-user'
                            : 'heroicon-o-user',
                        'company' => $selectedRole === 'company'
                            ? 'heroicon-s-building-office-2'
                            : 'heroicon-o-building-office-2',
                        'agency' => $selectedRole === 'agency'
                            ? 'heroicon-s-building-storefront'
                            : 'heroicon-o-building-storefront',
                        'curator' => $selectedRole === 'curator'
                            ? 'heroicon-s-magnifying-glass-circle'
                            : 'heroicon-o-magnifying-glass-circle',
                    ];
                })
                ->colors([
                    'influencer' => 'secondary',
                    'company' => 'secondary',
                    'agency' => 'secondary',
                    'curator' => 'secondary',
                ])
                ->grouped()
                ->extraFieldWrapperAttributes(['class' => 'text-center flex flex-col w-fit mx-auto gap-4'])
                ->extraAttributes(['class' => 'text-center flex justify-center'])
                ->required()
                ->live()
                ->inline(),

            Wizard::make(
                [
                    Step::make('Dados Pessoais')
                        ->schema([
                            $this->getBaseInfoColumn(),
                        ]),

                    Step::make('Meu Perfil')->schema([
                        $this->getInfluencerColumn(),
                    ])->visible(fn(Get $get) => $get('role') === 'influencer'),

                    Step::make('Detalhes')->schema([
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

                        TextInput::make('pix_address')->label('Chave Pix'),

                        $this->getEmailFormComponent(),
                        Group::make()
                            ->statePath('location_data')
                            ->columns(3)
                            ->visible(fn(Get $get) => $get('role') === 'influencer')

                            ->schema([

                                Select::make('country')
                                    ->label('País')
                                    ->placeholder('')

                                    ->options([
                                        'BR' => 'Brasil',
                                        'US' => 'Estados Unidos',
                                        'AR' => 'Argentina',
                                        'UY' => 'Uruguai',
                                        'PY' => 'Paraguai',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('state', null);
                                        $set('city', null);
                                    }),

                                Select::make('state')
                                    ->label('Estado')
                                    ->placeholder('')
                                    ->options(function () {
                                        try {
                                            // Definimos um timeout baixo (ex: 3 segundos) para não travar o form
                                            $response = Http::timeout(1)->get('https://servicodados.ibge.gov.br/api/v1/localidades/estados');

                                            if ($response->failed()) return [];

                                            return $response->collect()
                                                ->sortBy('nome')
                                                ->pluck('nome', 'sigla')
                                                ->toArray();
                                        } catch (\Throwable $e) {
                                            Log::warning("IBGE API (States) failed: " . $e->getMessage());
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('city', null))
                                    ->disabled(fn(Get $get) => $get('country') !== 'BR'),

                                Select::make('city')
                                    ->label('Cidade')
                                    ->placeholder('')
                                    ->options(function (Get $get) {
                                        $state = $get('state');
                                        if (!$state || $get('country') !== 'BR') return [];

                                        try {
                                            return Http::timeout(1)
                                                ->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$state}/municipios")
                                                ->collect()
                                                ->pluck('nome', 'nome')
                                                ->toArray();
                                        } catch (\Throwable $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->disabled(fn(Get $get) => $get('country') !== 'BR'),
                            ]),

                        Text::make('Caso a lista de estados e cidades não carregue, você poderá preencher seu endereço na página de edição de perfil.'),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
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
                    ->label('Público Alvo')
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
                    ->nullable()
                    ->helperText('Opcional, você pode se afiliar a uma agência quando quiser.')
                    ->getSearchResultsUsing(
                        fn(string $search): array => User::where('role', UserRole::AGENCY)
                            ->where('name', 'ilike', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value) => User::find($value)?->name
                    ),

                Section::make('Redes Sociais')->collapsed()->collapsible()
                    ->schema([
                        Group::make()->columns(2)->schema([
                            TextInput::make('instagram')->placeholder('@Instagram'),
                            TextInput::make('instagram_followers')->label('Seguidores')->integerInputFormatted(),

                            TextInput::make('youtube')->placeholder('@YouTube'),
                            TextInput::make('youtube_followers')->label('Seguidores')->integerInputFormatted(),

                            TextInput::make('tiktok')->placeholder('@TikTok'),
                            TextInput::make('tiktok_followers')->label('Seguidores')->integerInputFormatted(),

                            TextInput::make('twitter')->placeholder('@Twitter'),
                            TextInput::make('twitter_followers')->label('Seguidores')->integerInputFormatted(),

                            TextInput::make('facebook')->placeholder('@Facebook'),
                            TextInput::make('facebook_followers')->label('Seguidores')->integerInputFormatted(),
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
                            ->label('Comissão da Agência')
                            ->suffix('%')
                            ->extraInputAttributes(['style' => 'text-align: right;'])
                            ->mask('99')
                            ->minValue(0)
                            ->maxValue(99),

                        Text::make('Valor tabelado para ser cobrado da empresa')->columnSpan(4),
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
            ->label('Mais informações sobre você')
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
        return Width::ThreeExtraLarge;
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
            ->visible(fn(Get $get) => filled($get('role') || filled($get('name'))))
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
        if (isset($data['location_data'])) {
            $loc = $data['location_data'];

            $locationString = implode('|', [
                $loc['country'] ?? '',
                $loc['state'] ?? '',
                $loc['city'] ?? '',
            ]);

            if (!isset($data['influencer_data'])) {
                $data['influencer_data'] = [];
            }
            $data['influencer_data']['location'] = $locationString;

            unset($data['location_data']);
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
