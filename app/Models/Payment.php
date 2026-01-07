<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'abacate_id',
        'campaign_id',
        'user_id',
        'amount',
        'status',
        'qrcode_base64',
        'qrcode_url',
        'metadata',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
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

    public function getAmountInReais(): float
    {
        return $this->amount / 100;
    }
}
