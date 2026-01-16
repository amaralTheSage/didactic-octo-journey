<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeValue extends Model
{
    protected $fillable = ['title', 'attribute_id', 'editable'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function campaign_announcements(): BelongsToMany
    {
        return $this->belongsToMany(
            CampaignAnnouncement::class,
            'attribute_value_campaign_announcement',
            'attribute_value_id',
            'campaign_announcement_id'
        )->withTimestamps();
    }

    # quase certo q não utilizamos mais
    public function influencer_infos(): BelongsToMany
    {
        return $this->belongsToMany(
            InfluencerInfo::class,
            'attribute_value_influencer_info',
            'attribute_value_id',
            'influencer_info_id'
        )->withPivot('title')->withTimestamps();
    }
}
