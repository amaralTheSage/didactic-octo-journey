<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['image','name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
