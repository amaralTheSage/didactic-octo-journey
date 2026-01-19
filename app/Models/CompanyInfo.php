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
}
