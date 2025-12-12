<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerInfo extends Model
{
    protected $fillable = [
        'user_id',
        'agency_id',
        'association_status',
        'n_followers',
        'instagram',
        'twitter',
        'facebook',
        'youtube'
    ];
}
