<?php

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;

class ExpandableBadges extends Column
{
    protected string $view = 'filament.tables.columns.expandable-badges';

    protected int $limit = 3;

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
