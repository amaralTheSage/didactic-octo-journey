<?php

namespace App\Filament\Pages;

use App\Models\Proposal;
use Filament\Pages\Page;

class ViewChangeLogs extends Page
{
    protected string $view = 'filament.pages.view-change-logs';

    public Proposal $proposal;

    protected static bool $shouldRegisterNavigation = false;
    public $logs;

    public function mount(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    protected function getViewData(): array
    {
        return [
            'logs' => $this->logs,
        ];
    }
}
