<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements HasAvatar, FilamentUser
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
        'pix_address',
    ];

    protected $with = ['subcategories'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    public function influencer_info(): HasOne
    {
        return $this->hasOne(InfluencerInfo::class);
    }


    // if agency
    public function agency_loans()
    {
        return $this->belongsToMany(
            User::class,
            'borrowed_influencer_agency',
            'agency_id',
            'influencer_id',
        );
    }

    // if influencer
    public function agency_loans_influencer()
    {
        return $this->belongsToMany(
            User::class,
            'borrowed_influencer_agency',
            'influencer_id',
            'agency_id'
        );
    }


    public function attribute_values(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            // 'attribute_value_user',
            // 'user_id',
            // 'attribute_value_id'
        )->withPivot('title')->withTimestamps();
    }

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

    public function borrowed_influencers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'borrowed_influencer_agency',
            'agency_id',
            'influencer_id'
        )->withTimestamps();
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
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return asset('storage/' . $this->avatar);
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
