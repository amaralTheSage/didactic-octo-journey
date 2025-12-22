<?php

namespace App\Models;

use App\UserRoles;
use Illuminate\Database\Eloquent\Model;

class InfluencerInfo extends Model
{
    protected $table = 'influencer_info';

    protected $fillable = [
        'user_id',
        'agency_id',
        'association_status',
        'instagram',
        'twitter',
        'facebook',
        'youtube',
        'tiktok',
        'instagram_followers',
        'twitter_followers',
        'facebook_followers',
        'youtube_followers',
        'tiktok_followers',
        'reels_price',
        'stories_price',
        'carrousel_price',
    ];

    public function agency()
    {
        return $this->belongsTo(User::class, 'agency_id')->where('role', UserRoles::Agency);
    }
}
