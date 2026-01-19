<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    public ?array $temp_influencer_ids = null;

    protected $fillable = [
        'name',
        'description',
        'product_id',
        'company_id',
        'budget',
        'agency_cut',
        'campaign_status',
        'n_reels',
        'n_carrousels',
        'n_stories',
        'location',

        'validated_at',

        'influencer_ids', // not actually in the db
    ];

    protected $with = ['proposals', 'attribute_values'];

    public function attribute_values()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_value_campaign',
            'campaign_id',
            'attribute_value_id'
        )->withPivot('title');
    }

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

    public function payments()
    {
        return $this->hasMany(Payment::class, 'campaign_id', 'id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'campaign_subcategory');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function scopeWhereMatchesUser($query, User $user, int $minPercent, int $maxPercent = 100)
    {
        $min = $minPercent / 100;
        $max = $maxPercent / 100;

        return $query->whereIn('id', function ($sub) use ($user, $min, $max) {
            $sub->select('c.id')
                ->from('campaigns as c')
                ->join('attribute_value_campaign as avc', 'c.id', '=', 'avc.campaign_id')
                ->join('attribute_value_user as avu', function ($join) use ($user) {
                    $join->on('avc.attribute_value_id', '=', 'avu.attribute_value_id')
                        ->where('avu.user_id', $user->id)
                        // Match de Title 
                        ->whereRaw('COALESCE(avc.title, \'\') = COALESCE(avu.title, \'\')');
                })
                ->groupBy('c.id')
                ->havingRaw('CAST(COUNT(avu.id) AS FLOAT) / NULLIF((SELECT count(*) FROM attribute_value_campaign WHERE campaign_id = c.id), 0) >= ?', [$min]);;
        });
    }
}
