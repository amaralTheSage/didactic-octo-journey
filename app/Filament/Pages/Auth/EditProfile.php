<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Auth\Notifications\NoticeOfEmailChangeRequest;
use Filament\Auth\Notifications\VerifyEmailChange;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Concerns;
use Filament\Panel;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Js;
use Illuminate\Validation\Rules\Password;
use League\Uri\Components\Query;
use Leandrocfe\FilamentPtbrFormFields\Money;
use LogicException;
use Throwable;

/**
 * @property-read Schema $form
 */
class EditProfile extends BaseEditProfile
{
    use Concerns\CanUseDatabaseTransactions;
    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static bool $isDiscovered = false;

    protected array $influencerData;

    protected string $view;

    public function getLayout(): string
    {
        return static::$layout ?? (static::isSimple() ? 'filament-panels::components.layout.simple' : 'filament-panels::components.layout.index');
    }

    public static function isSimple(): bool
    {
        return Filament::isProfilePageSimple();
    }

    public function getView(): string
    {
        return $this->view ?? 'filament-panels::auth.pages.edit-profile';
    }

    public static function getLabel(): string
    {
        return static::$title ?? __('filament-panels::auth/pages/edit-profile.label');
    }

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'profile';
    }

    public static function isTenantSubscriptionRequired(Panel $panel): bool
    {
        return false;
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    public function getUser(): Authenticatable&Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            throw new LogicException('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        if ($user->role === UserRole::INFLUENCER) {
            return $user->load(['influencer_info', 'subcategories']);
        }

        return $user;
    }

    protected function fillForm(): void
    {

        $data = $this->getUser()->attributesToArray();

        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    public static function registerRoutes(Panel $panel): void
    {
        if (filled(static::getCluster())) {
            Route::name(static::prependClusterRouteBaseName($panel, ''))
                ->prefix(static::prependClusterSlug($panel, ''))
                ->group(fn() => static::routes($panel));

            return;
        }

        static::routes($panel);
    }

    public static function getRouteName(?Panel $panel = null): string
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return $panel->generateRouteName('auth.' . static::getRelativeRouteName($panel));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getUser();

        if ($user->role === UserRole::INFLUENCER && $user->influencer_info) {
            $data['influencer_data'] = $user->influencer_info->toArray();
            $data['influencer_data']['subcategories'] = $user->subcategories->pluck('id')->toArray();
        }

        if (! empty($user->influencer_info->location)) {
            [$country, $state, $city] = array_pad(
                explode('|', $user->influencer_info->location),
                3,
                null
            );

            $data['location_data'] = compact('country', 'state', 'city');
        }

        $existingSelections = $user->attribute_values()
            ->withPivot('title')
            ->get()
            ->groupBy('attribute_id');

        // 2. Map Attributes
        $data['attribute_values'] = Attribute::with('values')->get()
            ->map(function ($attribute) use ($existingSelections) {
                $selected = $existingSelections->get($attribute->id);

                return [
                    'attribute_id' => $attribute->id,

                    'attribute_title' => $attribute->title,
                    'attribute_value_id' => $selected ? $selected->pluck('id')->toArray() : [],
                    'title' => $selected ? $selected->first()->pivot->title : null,
                ];
            })->toArray();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {

        $this->influencerData = $data['influencer_data'] ?? [];

        $locData = $data['influencer_data']['location_data'] ?? $data['location_data'] ?? null;

        if ($locData && is_array($locData)) {

            if (array_key_exists(0, $locData) && is_array($locData[0])) {
                $loc = $locData[0];
            } else {
                $loc = $locData;
            }

            $country = $loc['country'] ?? '';
            $state = $loc['state'] ?? '';
            $city = $loc['city'] ?? '';

            $this->influencerData['location'] = implode('|', [$country, $state, $city]);
        }

        unset($this->influencerData['location_data']);
        unset($data['location_data']);

        unset($data['influencer_data']);

        return $data;
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, mixed $data): Model
    {
        $repeaterRows = $data['attribute_values'] ?? [];
        $influencerData = $this->influencerData ?? [];
        $subcategoryIds = $this->influencerData['subcategories'] ?? [];

        unset($data['attribute_values'], $data['influencer_data']);

        $pivotData = [];
        foreach ($repeaterRows as $row) {
            $ids = $row['attribute_value_id'] ?? [];
            $ids = is_array($ids) ? $ids : [$ids]; // Handle single/array

            foreach ($ids as $id) {
                if ($id) {
                    $pivotData[$id] = ['title' => $row['title'] ?? null];
                }
            }
        }

        $record->attribute_values()->sync($pivotData);

        // SUBCATEGORIES
        $record->subcategories()->sync($subcategoryIds);

        if (Filament::hasEmailChangeVerification() && array_key_exists('email', $data)) {
            $this->sendEmailChangeVerification($record, $data['email']);

            unset($data['email']);
        }

        $record->update($data);

        if ($record->role->value === 'influencer') {
            unset($influencerData['subcategories']);

            $record->influencer_info()->updateOrCreate(
                ['user_id' => $record->id],
                $influencerData
            );
        }

        return $record;
    }

    protected function sendEmailChangeVerification(Model $record, string $newEmail): void
    {
        if ($record->getAttributeValue('email') === $newEmail) {
            return;
        }

        $notification = app(VerifyEmailChange::class);
        $notification->url = Filament::getVerifyEmailChangeUrl($record, $newEmail);

        $verificationSignature = Query::new($notification->url)->get('signature');

        cache()->put($verificationSignature, true, ttl: now()->addHour());

        $record->notify(app(NoticeOfEmailChangeRequest::class, [
            /** @phpstan-ignore-line */
            'blockVerificationUrl' => Filament::getBlockEmailChangeVerificationUrl($record, $newEmail, $verificationSignature),
            'newEmail' => $newEmail,
        ]));

        Notification::route('mail', $newEmail)
            ->notify($notification);

        $this->getEmailChangeVerificationSentNotification($newEmail)?->send();

        $this->data['email'] = $record->getAttributeValue('email');
    }

    protected function getSavedNotification(): ?FilamentNotification
    {
        $title = $this->getSavedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return FilamentNotification::make()
            ->success()
            ->title($title);
    }

    protected function getEmailChangeVerificationSentNotification(string $newEmail): ?FilamentNotification
    {
        return FilamentNotification::make()
            ->success()
            ->title(__('filament-panels::auth/pages/edit-profile.notifications.email_change_verification_sent.title', ['email' => $newEmail]))
            ->body(__('filament-panels::auth/pages/edit-profile.notifications.email_change_verification_sent.body', ['email' => $newEmail]));
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('filament-panels::auth/pages/edit-profile.notifications.saved.title');
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::auth/pages/edit-profile.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->live(debounce: 500);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/edit-profile.form.password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->showAllValidationMessages()
            ->autocomplete('new-password')
            ->dehydrated(fn($state): bool => filled($state))
            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.validation_attribute'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn(Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
            ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
            ->belowContent(__('filament-panels::auth/pages/edit-profile.form.current_password.below_content'))
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn(Get $get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
            ->dehydrated(false);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(! static::isSimple())
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('data');
    }

    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // FORM
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------

    protected function getBaseInfoColumn(): Group
    {
        return Group::make()
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

                $this->getEmailFormComponent(),

                Group::make()
                    ->statePath('location_data')->columns(3)
                    ->schema([
                        Select::make('country')
                            ->label('País')
                            ->placeholder('')
                            ->columnSpan(fn(Get $get) => $get('country') === 'BR' ? 1 : 3)
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
                            ->disabled(fn(Get $get) => $get('country') !== 'BR')
                            ->visible(fn(Get $get) => $get('country') === 'BR'),


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
                            ->disabled(fn(Get $get) => $get('country') !== 'BR')
                            ->visible(fn(Get $get) => $get('country') === 'BR'),
                    ]),

                Text::make('Caso a lista de estados e cidades não carregue, tente novamente mais tarde.')->visible(fn(Get $get) => $get('role') === 'influencer' && $get('location_data.country') === 'BR')->live(),


                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                TextInput::make('pix_address')->label('Endereço Pix'),

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
                $user = $this->getUser();
                $influencerInfo = $user->influencer_info;

                return Attribute::with('values')->get()->map(function ($attribute) use ($influencerInfo) {
                    $data = [
                        'attribute_id' => $attribute->id,
                        'attribute' => $attribute,
                    ];

                    if ($influencerInfo) {
                        // Load existing values for this attribute
                        $existingValues = $influencerInfo->attributeValues()
                            ->where('attribute_id', $attribute->id)
                            ->get();

                        if ($existingValues->isNotEmpty()) {
                            $data['attribute_value_id'] = $existingValues->pluck('id')->toArray();

                            // Get custom title if exists
                            $customTitle = $existingValues->first()->pivot->title;
                            if ($customTitle) {
                                $data['title'] = $customTitle;
                            }
                        }
                    }

                    return $data;
                })->toArray();
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
                            fn(Get $get) => Attribute::find($get('attribute_id'))
                                ?->values()
                                ->pluck('title', 'id') ?? []
                        )->multiple()
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
                        ->columnSpan(function (Get $get) {
                            $state = $get('attribute_value_id');
                            if (filled($state)) {
                                $hasOutro = AttributeValue::whereIn('id', $state)
                                    ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                    ->exists();

                                return $hasOutro ? 1 : 2;
                            }

                            return 2;
                        }),

                    TextInput::make('title')
                        ->label('Especifique')
                        ->placeholder('Especifique...')
                        ->visible(function (Get $get) {
                            $attribute = Attribute::find($get('attribute_id'));

                            if (! $attribute || ! $attribute->values()->exists()) {
                                return true;
                            }

                            $state = $get('attribute_value_id');
                            if (filled($state)) {
                                return AttributeValue::whereIn('id', $state)
                                    ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                    ->exists();
                            }

                            return false;
                        })
                        ->columnSpan(1),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function getMaxContentWidth(): Width
    {
        return Width::FourExtraLarge;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make(
                [
                    Step::make('Dados Pessoais')
                        ->schema([
                            $this->getBaseInfoColumn(),
                        ]),

                    Step::make('Meu Perfil')->schema([
                        $this->getInfluencerColumn(),
                    ])->visible(Gate::allows('is_influencer')),

                    Step::make('Detalhes')->schema([
                        $this->getAttributesRepeater(),
                    ])->visible(Gate::allows('is_influencer')),
                ]
            ),
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return $this->backAction();
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::auth/pages/edit-profile.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::Start;
    }

    public function getTitle(): string|Htmlable
    {
        return static::getLabel();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return static::$slug ?? 'profile';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    /**
     * @deprecated Use `getCancelFormAction()` instead.
     */
    public function backAction(): Action
    {
        $url = filament()->getUrl();

        return Action::make('back')
            ->label(__('filament-panels::auth/pages/edit-profile.actions.cancel.label'))
            ->alpineClickHandler(
                FilamentView::hasSpaMode($url)
                    ? 'document.referrer ? window.history.back() : Livewire.navigate(' . Js::from($url) . ')'
                    : 'document.referrer ? window.history.back() : (window.location.href = ' . Js::from($url) . ')',
            )
            ->color('gray');
    }

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxContentWidth' => $maxContentWidth = $this->getMaxWidth() ?? $this->getMaxContentWidth(),
            'maxWidth' => $maxContentWidth,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                ...Arr::wrap($this->getMultiFactorAuthenticationContentComponent()),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky((! static::isSimple()) && $this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    public function getMultiFactorAuthenticationContentComponent(): ?Component
    {
        if (! Filament::hasMultiFactorAuthentication()) {
            return null;
        }

        $user = Filament::auth()->user();

        return Section::make()
            ->label(__('filament-panels::auth/pages/edit-profile.multi_factor_authentication.label'))
            ->compact()
            ->divided()
            ->secondary()
            ->schema(collect(Filament::getMultiFactorAuthenticationProviders())
                ->sort(fn(MultiFactorAuthenticationProvider $multiFactorAuthenticationProvider): int => $multiFactorAuthenticationProvider->isEnabled($user) ? 0 : 1)
                ->map(fn(MultiFactorAuthenticationProvider $multiFactorAuthenticationProvider): Component => Group::make($multiFactorAuthenticationProvider->getManagementSchemaComponents())
                    ->statePath($multiFactorAuthenticationProvider->getId()))
                ->all());
    }
}
