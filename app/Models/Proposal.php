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

        'company_approval',
        'agency_approval',

        'n_reels',
        'n_stories',
        'n_carrousels',

        'status',
    ];

    protected $with = ['agency'];

    public function change_logs()
    {
        return $this->hasMany(ProposalChangeLog::class);
    }

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
        return $this->belongsToMany(User::class)->withPivot('reels_price', 'stories_price', 'carrousel_price', 'influencer_approval');
    }
}
