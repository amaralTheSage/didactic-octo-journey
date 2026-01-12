<?php

namespace App\Actions\Filament;

use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;

class ViewChangeLogs extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'viewChangeLogs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Histórico');
        $this->icon('heroicon-o-archive-box');

        $this->color(Color::Amber);

        $this->modalContent(fn(Proposal $record) => view(
            'filament.pages.view-change-logs',
            [
                'logs' => $record->change_logs()
                    ->with('user')
                    ->latest()
                    ->get(),
            ]
        ));

        $this->modalHeading('Histórico de mudanças na Proposta');
        $this->modalWidth('3xl');
        $this->stickyModalHeader();

        $this->modalSubmitAction(false);
        $this->modalCancelActionLabel('Fechar');
    }
}
