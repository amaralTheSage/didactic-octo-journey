<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    protected $fillable = ['agency_id', 'campaign_announcement_id'];


    protected $with = ['agency'];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_id');
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(CampaignAnnouncement::class,         'campaign_announcement_id');
    }
}
