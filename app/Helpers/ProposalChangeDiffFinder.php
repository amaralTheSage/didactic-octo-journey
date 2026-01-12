<?php

namespace App\Helpers;

class ProposalChangeDiffFinder
{
    public static function findDiff(array $before, array $after): array
    {
        $diff = [];

        foreach ($after as $key => $value) {
            if (! array_key_exists($key, $before) || $before[$key] !== $value) {
                $diff[$key] = [
                    'from' => $before[$key] ?? null,
                    'to' => $value,
                ];
            }
        }

        return $diff;
    }
}
