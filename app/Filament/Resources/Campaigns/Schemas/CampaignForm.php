<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\InfluencerInfo;
use App\Models\User; // Import the InfluencerInfo model
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CampaignForm
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
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('price')
                                ->numeric()
                                ->inputMode('decimal')->prefix('R$')
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
                            fn($action) =>
                            $action->modalHeading('Criar Produto')
                        ),

                    Select::make('category_id')
                        ->relationship(
                            'category',
                            'title',
                        )
                        ->label('Categoria')
                        ->required(),

                    TextInput::make('budget')
                        ->label('Orçamento')
                        ->numeric()
                        ->prefix('R$')
                        ->formatStateUsing(fn($state) => number_format($state / 100, 2, ',', '.'))
                        ->dehydrateStateUsing(fn($state) => (int) (str_replace(['.', ','], ['', '.'], $state) * 100))->required()
                        ->placeholder('0,00')
                ]),

                Hidden::make('company_id')
                    ->default(Auth::id()),

                Section::make()->schema([
                    TextInput::make('agency_cut')->label('Parcela da Agência')->numeric()->required()->prefix('%')->inputMode('decimal')->maxValue(100)->minValue(0.01)->placeholder('30,00'),

                    Select::make('agency_id')
                        ->label('Agência')
                        ->options(
                            User::where('role', 'agency')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->live(),

                    Select::make('influencer_id')
                        ->label('Influencer')
                        ->options(
                            fn(Get $get) => User::where('role', 'influencer')
                                ->whereHas('influencer_info', function ($query) use ($get) {
                                    $query->where('agency_id', $get('agency_id'));
                                })
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->hidden(fn(Get $get) => ! $get('agency_id'))
                        ->disabled(fn(Get $get) => ! $get('agency_id')),
                ]),
            ]);
    }
}
