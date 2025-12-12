<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['channel', 'influencer_id', 'campaign_id', 'gross_value', 'net_value'];
}
