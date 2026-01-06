<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampaignAnnouncement extends Model
{
    public ?array $temp_influencer_ids = null;

    protected $fillable = [
        'name',
        'description',
        'product_id',
        'company_id',
        'budget',
        'agency_cut',
        'announcement_status',
        'n_reels',
        'n_carrousels',
        'n_stories',
        'location',

        'influencer_ids' // not actually in the db
    ];


    protected $with = ['proposals', 'attribute_values'];

    protected function influencerIds(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->temp_influencer_ids,
            set: function ($value) {
                // Save to the public property for the Observer to use
                $this->temp_influencer_ids = $value;

                // Return an empty array so Eloquent DOES NOT try to save 
                // 'influencer_ids' or 'temp_influencer_ids' to the DB column.
                return [];
            }
        );
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function attribute_values()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_value_campaign_announcement',
            'campaign_announcement_id',
            'attribute_value_id'
        )->withPivot('title');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_announcement_category');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}
