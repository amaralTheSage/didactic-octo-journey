<?php

namespace App\Filament\Resources\CampaignAnnouncements\Schemas;

use App\Actions\Filament\ProposeAction;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

                        TextEntry::make('categories.title')->limitList(2)->listWithLineBreaks()->expandableLimitedList()
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



                        Action::make('validateNow')
                            ->label('Validar')
                            ->color('success')
                            ->action(function ($record) {
                                return redirect(route('payments.qrcode') . '?campaign_id=' . $record->id);
                            }),




                        Action::make('influencerWantsToParticipate')->visible(Gate::allows('is_influencer'))->label('Quero Participar')->action(function ($record) {
                            $userName = Auth::user()->name;

                            $record->company->notify(Notification::make()
                                ->title("Influenciador se interessou na sua campanha")
                                ->body("{$userName} demonstrou interesse na campanha {$record->name}")
                                ->actions([
                                    Action::make('view')
                                        ->label('Ver influenciador')
                                        ->url(route('filament.admin.resources.influencers.index', [
                                            'search' => $userName,
                                            'tableAction' => 'viewInfluencerDetails',
                                            'tableActionRecord' => Auth::user()->getKey(),
                                        ])),
                                ])
                                ->toDatabase());

                            Auth::user()->influencer_info?->agency?->notify(Notification::make()
                                ->title("{$userName} se interessou em uma campanha")
                                ->body("O influenciador demonstrou interesse na campanha {$record->name}")
                                ->toDatabase());

                            Notification::make()
                                ->title('Interesse registrado!')
                                ->body('A empresa recebeu sua notificação.')
                                ->success()
                                ->send();
                        }),

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

                                Action::make('viewCompany')
                                    ->label('Ver Empresa')
                                    ->icon('heroicon-o-building-office')->color('primary')
                                    ->url(fn($record) => route('filament.admin.resources.companies.index', [
                                        'search' => $record->company->name,
                                        'tableAction' => 'viewCompanyDetails',
                                        'tableActionRecord' => $record->company->getKey(),
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

                        Action
                            ::make('remove_proposal')
                            ->label('Remover Interesse')
                            ->color('danger')
                            ->visible(
                                fn($record) => Gate::allows('is_agency')
                                    && $record->proposals()
                                    ->where('agency_id', Auth::id())
                                    ->exists()
                            )
                            ->action(
                                fn($record) => $record->proposals()->where('agency_id', Auth::id())->delete()
                            ),
                    ]),
                ]),

            ]);
    }
}
