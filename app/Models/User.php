<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\UserRoles;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Impersonate, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'bio',
    ];

    protected $with = ['subcategories'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function subcategories()
    {
        return $this->belongsToMany(Subcategory::class);
    }

    public function campaigns()
    {
        return OngoingCampaign::where('agency_id', $this->id)
            ->orWhere('company_id', $this->id)
            ->orWhere('influencer_id', $this->id);
    }

    public function influencer_info(): HasOne
    {
        return $this->hasOne(InfluencerInfo::class);
    }

    public function influencers()
    {
        return $this->hasManyThrough(
            User::class,
            InfluencerInfo::class,
            'agency_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected $appends = ['avatar_url']; // Makes it accessible in react

    // --------------------------
    //  Filament Stuff
    // --------------------------

    public function getFilamentAvatarUrl(): ?string
    {
        return asset('storage/'.$this->avatar);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return asset('storage/'.$this->avatar);
    }

    /**
     * Decide if this user may access the given panel.
     * Here, only users with verified emails are allowed.
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRoles::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
