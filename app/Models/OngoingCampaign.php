<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OngoingCampaign extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'company_id',
        'influencer_id',
        'agency_id',
        'budget',
        'agency_cut',
        'status_agency',
        'category_id',
        'status_influencer',
    ];

    protected $casts = [
        'status_agency' => ApprovalStatus::class,
        'status_influencer' => ApprovalStatus::class,
    ];

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

    public function agency(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agency_id');
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
}
