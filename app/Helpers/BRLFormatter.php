<?php

namespace App\Helpers;

use NumberFormatter;

class BRLFormatter
{
    public function __invoke(float|int|null $value): string
    {
        if ($value === null) {
            return '—';
        }

        $formatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $value, 'BRL');
    }
}
