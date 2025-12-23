<?php

namespace App\Filament\Resources\CampaignAnnouncements\Schemas;

use App\Actions\Filament\ProposeAction;
use App\Services\ChatService;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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


                        TextEntry::make('product.name')
                            ->label('Produto')
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('category.title')
                            ->label('Categoria')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('product.description')
                            ->label(' ')
                            ->columnSpan(2),


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


                Group::make()->schema([

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

                            Actions::make([

                                Action::make('newChat')
                                    ->label('Conversar')
                                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                                    ->color('secondary')
                                    ->action(function ($record) {

                                        $chat = ChatService::createChat(
                                            [
                                                $record->company->id,
                                            ]

                                        );

                                        if (is_array($chat) && isset($chat['error'])) {
                                            Notification::make()
                                                ->title('Erro')
                                                ->body($chat['error'])
                                                ->danger()
                                                ->send();

                                            return;
                                        }

                                        return redirect()->route('chats.show', ['chat' => $chat]);
                                    }),

                                Action::make('viewCompany')
                                    ->label('Ver Empresa')
                                    ->icon('heroicon-o-building-office')
                                    ->url(fn($record) => route('filament.admin.resources.companies.index', [
                                        'search' => $record->company->name,
                                    ])),
                            ])->columnSpan(2),


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

                        ]),

                    Actions::make([
                        ProposeAction::make(),

                        Action::make('remove_proposal')
                            ->label('Remover Interesse')
                            ->color('danger')
                            ->visible(
                                fn($record) =>
                                Gate::allows('is_agency')
                                    && $record->proposals()
                                    ->where('agency_id', Auth::id())
                                    ->exists()
                            )
                            ->action(
                                fn($record) => $record->proposals()->where('agency_id', Auth::id())->delete()
                            ),
                    ]),
                ])
            ]);
    }
}
