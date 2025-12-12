<?php

namespace App\Models;

use App\CampaignStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'company_id',
        'influencer_id',
        'agency_id',
        'budget',
        'status',
        'agency_cut'
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_id');
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
}
