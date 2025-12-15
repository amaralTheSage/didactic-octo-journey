<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\User;
use App\Models\InfluencerInfo; // Import the InfluencerInfo model
use Filament\Forms\Components\Hidden;
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
                        ->required(),
                ]),

                Hidden::make('company_id')
                    ->default(Auth::id()),

                Section::make()->schema([

                    Select::make('agency_id')
                        ->label('Agência')
                        ->options(
                            User::where('role', 'agency')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required()
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
                        ->required()
                        ->hidden(fn(Get $get) => !$get('agency_id'))
                        ->disabled(fn(Get $get) => !$get('agency_id')),
                ]),
            ]);
    }
}
