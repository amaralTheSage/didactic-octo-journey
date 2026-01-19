<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    protected $table = 'company_info';
    protected $fillable = ['curator_id', 'company_id'];


    public function company()
    {
        return $this->belongsTo(User::class, 'company_id')->where('role', UserRole::COMPANY);
    }

    public function curator()
    {
        return $this->belongsTo(User::class, 'curator_id')->where('role', UserRole::CURATOR);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'company_id', 'company_id');
    }

    public function proposals(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            Proposal::class,
            Campaign::class,
            'company_id', // Chave estrangeira em campaigns
            'campaign_id', // Chave estrangeira em proposals
            'company_id',          // Chave local em users
            'id'           // Chave local em campaigns
        );
    }
}
