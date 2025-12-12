<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\UserRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Wirechat\Wirechat\Contracts\WirechatUser;
use Wirechat\Wirechat\Panel;
use Wirechat\Wirechat\Traits\InteractsWithWirechat;

class User extends Authenticatable implements WirechatUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Impersonate, InteractsWithWirechat, Notifiable, TwoFactorAuthenticatable;

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
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return asset('storage/' . $this->avatar);
    }

    public function campaigns()
    {
        return Campaign::where('agency_id', $this->id)
            ->orWhere('company_id', $this->id)
            ->orWhere('influencer_id', $this->id);
    }

    public function influencer_info(): HasOne
    {
        return $this->hasOne(InfluencerInfo::class);
    }

    public function agency()
    {
        return $this->belongsTo(User::class, 'agency_id')->where('role', UserRoles::Agency);
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

    /**
     * Decide if this user may access the given panel.
     * Here, only users with verified emails are allowed.
     */
    public function getWirechatAvatarUrlAttribute(): string
    {
        return $this->getAvatarUrlAttribute() ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    public function canAccessWirechatPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail();
    }

    /**
     * Control whether this user is allowed to create 1-to-1 chats.
     */
    public function canCreateChats(): bool
    {
        return Auth::user()->role !== UserRoles::Influencer;
    }

    /**
     * Control whether this user can create group conversations.
     */
    public function canCreateGroups(): bool
    {
        return Auth::user()->role !== UserRoles::Influencer;
    }

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
