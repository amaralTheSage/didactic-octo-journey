<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Actions\Filament\EditProposalAction;
use App\Actions\Filament\ViewProposal;
use App\Filament\Tables\Columns\ExpandableBadges;
use App\Helpers\ProposedBudgetCalculator;
use App\Models\Subcategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

class CampaignsTable
{

    public static function configure(Table $table): Table
    {
        $colorByStatus = fn(string $state) => match ($state) {
            'approved' => 'success',
            'finished' => 'success',
            'pending' => 'gray',
            'draft' => 'gray',
            'rejected' => 'danger',
            'cancelled' => 'danger',
        };

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $query->when(
                    Gate::allows('is_company'),
                    fn($q) => $q->where('company_id', Auth::id())
                )->orderByRaw(
                    'validated_at IS NULL, validated_at DESC, created_at DESC'
                );
            })
            ->filters([
                SelectFilter::make('match_level')
                    ->label('Compatibilidade')
                    ->options([
                        '90' => 'Alta Compatibilidade (90%)',
                        '50' => 'Média Compatibilidade (50%+)',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === '90') {
                            return $query->whereMatchesUser(Auth::user(), 90);
                        }
                        if ($data['value'] === '50') {
                            return $query->whereMatchesUser(Auth::user(), 50, 99);
                        }
                    })
                    ->visible(Gate::allows('is_influencer')),
            ])

            ->columns([

                // CAMPAIGNS TAB
                TextColumn::make('name')->label('Campanha')
                    ->searchable()->description(function ($record) {
                        $count = $record->proposals->count();

                        return $count === 1 ? $count . ' Proposta' : $count . ' Propostas';
                    })
                    ->icon(fn($record) => $record->validated_at
                        ? 'heroicon-o-check-badge'
                        : null)
                    ->iconPosition('after')
                    ->tooltip(fn($record) => $record->validated_at
                        ? 'Campanha Verificada'
                        : null)
                    ->iconColor('success'),

                ImageColumn::make('company.avatar_url')->circular()->label(' ')->toggleable()
                    ->visible(Gate::denies('is_company')),

                ExpandableBadges::make('subcategories')
                    ->label('Categorias')
                    ->limit(5)
                    ->grow(true)
                    ->width('120%')
                    ->extraAttributes([
                        'style' => 'min-width: 400px !important; display: block;',
                    ]),

                TextColumn::make('company.name')->label('Empresa')->toggleable()
                    ->searchable()->visible(Gate::denies('is_company')),
                TextColumn::make('product.name')->label('Produto')->toggleable()
                    ->searchable(),

                TextColumn::make('description')->label('Descrição')->limit(40)->tooltip(fn($record) => $record->description)->toggleable()->toggledHiddenByDefault(),

                TextColumn::make('budget')->label('Orçamento')->money('BRL')->toggleable()
                    ->sortable()
                    ->visible(Gate::denies('is_influencer'))
                    ->description(fn($record) => '+' . rtrim(rtrim(number_format($record->agency_cut, 2, '.', ''), '0'), '.') . '% de Comissão'),

                ColumnGroup::make('Mídias', [
                    TextColumn::make('n_reels')->label('Reels')->alignCenter(),
                    TextColumn::make('n_stories')->label('Stories')->alignCenter(),
                    TextColumn::make('n_carrousels')->label('Carrosseis')->alignCenter(),
                ]),

                TextColumn::make('campaign_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'open' => 'success',
                        'paused' => 'gray',
                        'finished' => 'info',
                    })
                    ->formatStateUsing(fn(string $state) => __("campaign_status.$state.label"))
                    ->toggleable(),

                TextColumn::make('created_at')->label('Anunciada em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])

            ->recordAction('view')
            ->recordActions([
                ViewAction::make()->hiddenLabel(),

                EditAction::make()->hiddenLabel()->defaultColor('gray')
                    ->visible(fn() => Gate::allows('is_company')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(Gate::allows('is_company')),
                ]),
            ]);
    }
}
