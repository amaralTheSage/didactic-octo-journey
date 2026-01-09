<?php

namespace App\Filament\Resources\CampaignAnnouncements\Schemas;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\User;
use App\Enums\UserRoles;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CampaignAnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([

                Group::make()->schema([
                    TextInput::make('name')
                        ->label('Nome da Campanha')
                        ->required(),

                    Select::make('product_id')
                        ->relationship(
                            'product',
                            'name',
                            fn($query) => $query->where('company_id', Auth::id())
                        )
                        ->label('Produto')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('price')
                                ->numeric()
                                ->inputMode('decimal')
                                ->prefix('R$')
                                ->formatStateUsing(fn($state) => number_format($state / 100, 2, ',', '.'))
                                ->dehydrateStateUsing(fn($state) => (int) (str_replace(['.', ','], ['', '.'], $state) * 100))->required()
                                ->placeholder('0,00')
                                ->step('0.01')
                                ->required(),
                            MarkdownEditor::make('description')
                                ->nullable()->columnSpan(2),
                            Hidden::make('company_id')->default(Auth::id()),
                        ])
                        ->createOptionAction(
                            fn($action) => $action->modalHeading('Criar Produto')
                        ),

                    Select::make('subcategory_ids')
                        ->relationship('subcategories', 'title')
                        ->label('Categorias')
                        ->multiple()
                        ->options(
                            Category::with('subcategories')->get()
                                ->mapWithKeys(fn($category) => [
                                    $category->title => $category->subcategories
                                        ->pluck('title', 'id')
                                        ->toArray(),
                                ])
                                ->toArray()
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

                Hidden::make('company_id')
                    ->default(Auth::id()),

                Section::make()->schema([
                    Select::make('influencer_ids')
                        ->label('Influenciadores')
                        ->multiple()
                        ->options(
                            User::where('role', UserRoles::Influencer)
                                ->whereHas('influencer_info', function ($query) {
                                    $query->whereNotNull('agency_id');
                                })
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->default(fn() => session('selected_influencers', []))
                        ->afterStateHydrated(function () {
                            // Clear session after loading
                            session()->forget('selected_influencers');
                        })
                        ->helperText('Selecione os influenciadores para criar propostas automaticamente'),
                ]),

                Group::make([
                    TextInput::make('budget')
                        ->label('Orçamento')
                        ->numeric()->required()
                        ->inputMode('decimal')
                        ->prefix('R$')
                        ->placeholder('0,00'),

                    TextInput::make('agency_cut')
                        ->label('Comissão da Campanha')
                        ->numeric()
                        ->required()
                        ->prefix('%')
                        ->maxValue(100)
                        ->minValue(0)
                        ->placeholder('30'),
                ])->columns(2)->columnSpanFull(),

                Group::make()->columns(3)->schema([
                    TextInput::make('n_stories')->default(0)->numeric()->label('Stories'),
                    TextInput::make('n_reels')->default(0)->numeric()->label('Reels'),
                    TextInput::make('n_carrousels')->default(0)->numeric()->label('Carrosséis'),
                ])->columnSpan(2),


                # CampaignAnnouncementForm
                Repeater::make('attribute_values')
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->label('Atributos Gerais')
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->default(function () {
                        return Attribute::with('values')->get()->map(function ($attribute) {
                            return [
                                'attribute_id' => $attribute->id,
                                'attribute' => $attribute,
                            ];
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
                                    // $state is now an array. Check if "Outro" is NOT in the selection.
                                    if (filled($state)) {
                                        $hasOutro = \App\Models\AttributeValue::whereIn('id', $state)
                                            ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                            ->exists();

                                        if (!$hasOutro) {
                                            $set('title', null);
                                        }
                                    }
                                })
                                ->columnSpan(function (Get $get) {
                                    $state = $get('attribute_value_id');
                                    if (filled($state)) {
                                        $hasOutro = \App\Models\AttributeValue::whereIn('id', $state)
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

                                    // If no predefined values exist, always show
                                    if (!$attribute || !$attribute->values()->exists()) {
                                        return true;
                                    }

                                    $state = $get('attribute_value_id');
                                    if (filled($state)) {
                                        return \App\Models\AttributeValue::whereIn('id', $state)
                                            ->whereRaw("LOWER(title) IN ('outro', 'outra', 'outros', 'outras')")
                                            ->exists();
                                    }

                                    return false;
                                })
                                ->columnSpan(1),
                        ])->columns(2)->columnSpanFull(),
                    ])
                    ->columnSpan(2),

                Repeater::make('location_data')
                    ->label('Localização')->addable(false)
                    ->table([
                        TableColumn::make('País'),
                        TableColumn::make('Estado'),
                        TableColumn::make('Cidade'),
                    ])
                    ->deletable(false)
                    ->schema([

                        Select::make('country')->columnSpan(1)
                            ->label('País')
                            ->placeholder('Selecione um país')
                            ->options([
                                'BR' => 'Brasil',
                                'US' => 'Estados Unidos',
                                'AR' => 'Argentina',
                                'UY' => 'Uruguai',
                                'PY' => 'Paraguai',
                                // Add more countries
                            ])
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('state', null);
                                $set('city', null);
                            })
                            ->required(),

                        Select::make('state')->columnSpan(1)
                            ->label('Estado')
                            ->placeholder('Selecione um estado')
                            ->options(function () {
                                return Http::get('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
                                    ->collect()
                                    ->sortBy('nome')
                                    ->pluck('nome', 'sigla')
                                    ->toArray();
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('city', null);
                            })
                            ->disabled(fn(Get $get) => $get('country') !== 'BR')
                            ->required(fn(Get $get) => $get('country') === 'BR'),

                        Select::make('city')->columnSpan(1)
                            ->label('Cidade')
                            ->placeholder('Selecione uma cidade')
                            ->options(function (Get $get) {
                                $state = $get('state');
                                if (! $state) {
                                    return [];
                                }

                                return Http::get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$state}/municipios")
                                    ->collect()
                                    ->pluck('nome', 'nome')
                                    ->toArray();
                            })
                            ->searchable()
                            ->disabled(fn(Get $get) => $get('country') !== 'BR')
                            ->required(fn(Get $get) => $get('country') === 'BR' && $get('state'))
                            ->disabled(fn(Get $get) => ! $get('state')),
                    ])->compact()
                    ->columnSpan(2),

                MarkdownEditor::make('description')->label('Descrição')->columnSpan(2),
            ]);
    }
}
