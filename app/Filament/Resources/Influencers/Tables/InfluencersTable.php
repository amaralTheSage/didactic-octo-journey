<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Actions\Filament\ChatAction;
use App\Actions\Filament\ViewInfluencerDetails;
use App\Models\Category;
use App\Models\User;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InfluencersTable
{
    public static function getEloquentQuery(): Builder
    {
        $query = User::query()
            ->where('role', UserRoles::Influencer)
            ->whereHas('influencer_info', function (Builder $query) {
                $query->where('agency_id', Auth::id());
            });

        return $query->with('subcategories');
    }

    public static function configure(Table $table): Table
    {
        $table->recordAction('viewInfluencerDetails');

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),
                TextColumn::make('name')->label('Nome')
                    ->searchable(),
                TextColumn::make('influencer_info.agency.name')->label('Agência')->default('___')
                    ->searchable(),

                TextColumn::make('first_category')
                    ->label('Categoria')
                    ->state(function (Model $record) {
                        return $record->subcategories->first()?->category?->title;
                    })
                    ->badge()
                    ->sortable()
                    ->tooltip(function (Model $record): string {
                        return $record->subcategories->pluck('title')->join(', ');
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subcategory.0.category')->label('Categoria')
                    ->options(
                        Category::query()->pluck('title', 'id')->toArray(),
                    ),
            ])
            ->recordActions([
                Action::make('Aprovar Vínculo')
                    ->label('Aprovar')
                    ->visible(fn ($livewire): bool => $livewire->activeTab === 'Pedidos de Vínculo')
                    ->action(function ($record) {
                        $record->influencer_info->update(['association_status' => 'approved']);
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Influenciador vinculado')
                            ->body('Vínculo com influenciador criado com sucesso.')
                    ),

                ChatAction::make(),
                ViewInfluencerDetails::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
