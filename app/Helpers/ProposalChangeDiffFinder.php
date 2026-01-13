<?php

namespace App\Helpers;

use App\Models\Proposal;
use App\Models\ProposalChangeLog;
use Illuminate\Support\Facades\Auth;

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

    public static function logProposalApproval(
        Proposal $proposal,
        string $role,
        string $from,
        string $to
    ): void {
        ProposalChangeLog::create([
            'proposal_id' => $proposal->id,
            'user_id' => Auth::id(),
            'changes' => [
                'approval' => [
                    'role' => $role,
                    'from' => $from,
                    'to' => $to,
                ],
            ],
        ]);
    }
}
