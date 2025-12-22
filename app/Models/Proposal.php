<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Proposal extends Model
{
    protected $fillable = [
        'agency_id',
        'campaign_announcement_id',
        'message',
        'proposed_agency_cut',
        'influencer_approval',
        'company_approval',
        'agency_approval',
    ];

    protected $with = ['agency'];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_id');
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(CampaignAnnouncement::class, 'campaign_announcement_id');
    }

    public function influencers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
