<?php

namespace App\Models;

use App\UserRoles;
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

    public function agency()
    {
        return $this->belongsTo(User::class, 'agency_id')->where('role', UserRoles::Agency);
    }
}
