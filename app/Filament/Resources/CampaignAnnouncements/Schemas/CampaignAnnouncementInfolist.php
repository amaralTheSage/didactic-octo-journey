<?php

namespace App\Filament\Resources\CampaignAnnouncements\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class CampaignAnnouncementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campanha')
                    ->icon(Heroicon::OutlinedPresentationChartLine)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->hiddenLabel()
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->placeholder('Sem descrição'),

                        TextEntry::make('category.title')
                            ->label('Categoria')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('product.name')
                            ->label('Produto')
                            ->icon('heroicon-o-cube'),

                        Section::make('Informações Financeiras')
                            ->columns(5)
                            ->schema([
                                TextEntry::make('budget')
                                    ->label('Orçamento ')
                                    ->money('BRL')
                                    ->columnSpan(2)
                                    ->size(TextSize::Large)
                                    ->weight('bold'),

                                TextEntry::make('agency_cut')
                                    ->label('Porcentagem da Agência')
                                    ->suffix('%')
                                    ->numeric()
                                    ->columnSpan(3)
                                    ->size(TextSize::Large)
                                    ->weight('bold'),

                            ])->columnSpan(2),

                        TextEntry::make('created_at')
                            ->label('Criada em')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-plus-circle')
                            ->color('success'),

                        TextEntry::make('updated_at')
                            ->label('Última atualização')
                            ->dateTime('d/m/Y H:i')
                            ->since()
                            ->icon('heroicon-o-arrow-path')
                            ->color('gray'),

                    ]),

                Section::make('Empresa')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('company.avatar_url')
                            ->hiddenLabel()->circular()
                            ->imageSize(80),

                        TextEntry::make('company.name')
                            ->hiddenLabel()->size(TextSize::Large)
                            ->weight('semibold'),

                        TextEntry::make('company.bio')->hiddenLabel()->columnSpan('full'),

                        Action::make('viewCompany')
                            ->label('Ver Empresa')
                            ->icon('heroicon-o-building-office')
                            ->url(fn ($record) => route('filament.admin.resources.companies.index', [
                                'search' => $record->company->name,
                            ])),
                    ]),

            ]);
    }
}
