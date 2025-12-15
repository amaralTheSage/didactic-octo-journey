<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Actions\Filament\ViewInfluencerDetails;
use App\Models\User;
use App\UserRoles;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InfluencersTable
{
    public static function getEloquentQuery(): Builder
    {
        $query =  User::query()
            ->where('role', UserRoles::Influencer)
            ->whereHas('influencer_info', function (Builder $query) {
                $query->where('agency_id', Auth::id());
            });

        return $query->with('subcategories');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),
                TextColumn::make('name')->label('Nome')
                    ->searchable(),
                TextColumn::make('influencer_info.agency.name')->label('Agência')->default('___')
                    ->searchable(),

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
                //
            ])
            ->recordActions([
                Action::make('Aprovar Vínculo')
                    ->label('Aprovar')
                    ->visible(fn($livewire): bool => $livewire->activeTab === 'Pedidos de Vínculo')
                    ->action(function ($record) {
                        $record->influencer_info->update(['association_status' => 'approved']);
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Influenciador vinculado')
                            ->body('Vínculo com influenciador criado com sucesso.')
                    ),
                ViewInfluencerDetails::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
