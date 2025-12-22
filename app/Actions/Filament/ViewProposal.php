<?php

namespace App\Actions\Filament;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;

class ViewProposal
{
    public static function make(): ViewAction
    {
        return ViewAction::make('viewProposal')
            ->label('Ver Proposta')
            ->slideOver()
            ->modalWidth('xl')
            ->schema(fn($record) => [
                Section::make('Campanha')
                    ->schema([
                        ImageEntry::make('announcement.company.avatar_url')
                            ->label('Empresa')
                            ->circular()
                            ->imageSize(60),
                        TextEntry::make('announcement.name')
                            ->label('Nome da Campanha')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('announcement.product.name')
                            ->label('Produto'),
                        TextEntry::make('announcement.budget')
                            ->label('Orçamento')
                            ->money('BRL'),
                        TextEntry::make('announcement.category.title')
                            ->label('Categoria')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Proposta')
                    ->schema([
                        TextEntry::make('message')
                            ->label('Mensagem')
                            ->columnSpanFull(),
                        TextEntry::make('proposed_agency_cut')
                            ->label('Parcela Proposta')
                            ->suffix('%')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('created_at')
                            ->label('Enviada em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),


                Section::make('Agência e Influenciador')->schema([

                    ImageEntry::make('agency.avatar_url')
                        ->hiddenLabel()
                        ->circular()
                        ->imageSize(60),

                    Group::make()->schema([
                        TextEntry::make('agency.name')->weight(FontWeight::Bold)
                            ->hiddenLabel(),
                        TextEntry::make('agency.email')
                            ->hiddenLabel()->copyable()
                    ])->columnSpan(4),



                    ImageEntry::make('influencer.avatar_url')
                        ->hiddenLabel()
                        ->circular()
                        ->imageSize(60),

                    Group::make()->schema([
                        TextEntry::make('influencer.name')->weight(FontWeight::Bold)
                            ->hiddenLabel(),
                        TextEntry::make('influencer.email')
                            ->hiddenLabel()->copyable()
                    ])->columnSpan(4),

                ])->columns(5),

                Actions::make([
                    Action::make('viewAgency')
                        ->label('Ver Agência')
                        ->icon('heroicon-o-building-storefront')
                        ->url(fn($record) => route('filament.admin.resources.agencies.index', [
                            'search' => $record->agency->name,
                        ])),

                    Action::make('viewInfluencer')
                        ->label('Ver Influenciador')
                        ->icon('heroicon-o-user-circle')
                        ->visible(fn($record) => isset($record->influencer))
                        ->url(fn($record) => route('filament.admin.resources.influencers.index', [
                            'search' => $record->influencer?->name,
                        ])),

                    AcceptProposal::make(),
                ]),

            ]);
    }
}
