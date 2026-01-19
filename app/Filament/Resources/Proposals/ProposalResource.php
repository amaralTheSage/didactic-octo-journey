<?php

namespace App\Filament\Resources\Proposals;

use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\Proposals\Pages\ListProposals;
use App\Filament\Resources\Proposals\Pages\ViewProposal;
use App\Filament\Resources\Proposals\Schemas\ProposalForm;
use App\Filament\Resources\Proposals\Schemas\ProposalInfolist;
use App\Filament\Resources\Proposals\Tables\ProposalsTable;
use App\Models\Proposal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $modelLabel = 'Proposta';

    public static function form(Schema $schema): Schema
    {
        return ProposalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProposalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProposalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function modifyGlobalSearchQuery(Builder $query, string $search): void
    {
        parent::modifyGlobalSearchQuery($query, $search);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
            // 'create' => CreateProposal::route('/create'),
            // 'view' => ViewProposal::route('/{record}'),
            // 'edit' => EditProposal::route('/{record}/edit'),
        ];
    }
}
