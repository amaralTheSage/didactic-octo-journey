<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;

class ExpandableBadges extends Column
{

    public bool $isExpanded = false;
    public int $limit = 3;



    public function toggleExpand()
    {
        $this->isExpanded = ! $this->isExpanded;
        return $this;
    }
    protected string $view = 'filament.tables.columns.expandable-badges';
}
