<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalChangeLog extends Model
{
    protected $fillable = [
        'proposal_id',
        'user_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
