<?php

namespace App\Filament\Resources\Agencies\Tables;

use App\Actions\Filament\ChatAction;
use App\Actions\Filament\ViewAgencyDetails;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AgenciesTable
{
    public static function configure(Table $table): Table
    {
        $table->recordAction('viewAgencyDetails');

        $table->recordActions([ViewAgencyDetails::make()]);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Nome')
                    ->circular(),

                TextColumn::make('name')->label(' ')
                    ->searchable(),

                TextColumn::make('influencer_categories')
                    ->label('Categorias')
                    ->badge()
                    ->getStateUsing(function ($record) {

                        return $record->influencers
                            ->flatMap(fn($inf) => $inf->subcategories)
                            ->pluck('title')
                            ->unique()
                            ->values()
                            ->toArray();
                    })
                    ->tooltip(
                        fn($record) =>
                        $record->influencers
                            ->flatMap(fn($inf) => $inf->subcategories)
                            ->pluck('title')
                            ->unique()
                            ->join(', ')
                    ),


                ColumnGroup::make('Seguidores')->columns([
                    TextColumn::make('igfollowers')
                        ->label('Instagram')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.instagram_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('twfollowers')
                        ->label('twitter')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.twitter_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('ytfollowers')
                        ->label('Youtube')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.youtube_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),

                    TextColumn::make('fbfollowers')
                        ->label('Facebook')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.facebook_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                    TextColumn::make('ttfollowers')
                        ->label('TikTok')
                        ->state(function ($record) {
                            $sum = $record->influencers
                                ->sum('influencer_info.tiktok_followers');

                            return $sum > 0
                                ? number_format($sum)
                                : '-';
                        }),
                ]),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('viewCampaigns')->hiddenLabel()
                    ->tooltip('Visualizar campanhas em comum')
                    ->icon('heroicon-o-presentation-chart-line')
                    ->url(fn($record) => route('filament.admin.resources.campaigns.index', [
                        'search' => $record->name,
                    ]))->visible(
                        fn(Model $record) => $record->campaigns()
                            ->where('company_id', Auth::id())
                            ->exists()
                    ),
                ViewAgencyDetails::make()->hiddenLabel(),
                ChatAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
