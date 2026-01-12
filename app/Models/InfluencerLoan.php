<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerLoan extends Model
{
    protected $fillable = [
        'influencer_id',
        'agency_id',
    ];

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function agency()
    {
        return $this->belongsTo(User::class, 'agency_id');
    }
}
