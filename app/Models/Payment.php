<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'abacate_id',
        'campaign_id',
        'user_id',
        'brcode',
        'amount',
        'status',
        'metadata',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CampaignAnnouncement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
