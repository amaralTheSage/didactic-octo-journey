<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignAnnouncement extends Model
{
    protected $fillable = [
        'name',
        'description',
        'product_id',
        'company_id',
        'budget',
        'agency_cut',
        'category_id',
        'announcement_status',
        'n_reels',
        'n_carrousels',
        'n_stories',
        'location',
    ];

    protected $with = ['proposals', 'attribute_values'];

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function attribute_values()
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
