<?php

namespace App\Models;

use App\Traits\HasFilamentPanels;
use App\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasFilamentPanels;
    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'two_factor_grace_until' => 'datetime',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getCanAttribute(): array
    {
        return [
            'is_super_admin' => $this->isSuperAdmin(),
        ];
    }

    public function fullName(): Attribute
    {
        return new Attribute(fn () => trim($this->first_name.' '.$this->last_name));
    }

    public function hasTwoFactorAuthentication(): Attribute
    {
        // WARNING: This does not mean the user has confirmed it
        return new Attribute(
            fn () => FortifyFeatures::enabled(FortifyFeatures::twoFactorAuthentication()) &&
                filled($this->two_factor_secret)
        );
    }

    public function isUnlocked(): Attribute
    {
        return new Attribute(function () {
            // Give access if 2FA is enabled
            if ($this->hasEnabledTwoFactorAuthentication()) {
                return true;
            }

            // Get the period until when the user has a grace period
            $gracePeriodUntil = $this->two_factor_grace_until ?: now();

            // Only give access when the grace period is not overdue
            return now()->isBefore($gracePeriodUntil);
        });
    }

    public function isLocked(): Attribute
    {
        return new Attribute(fn () => ! $this->is_unlocked);
    }

    public function twoFactorGraceUntil(): Attribute
    {
        return new Attribute(function ($value) {
            $grace = $this->castAttribute('two_factor_grace_until', $value);

            return $grace ?: today()->addWeeks(2);
        });
    }

    public function twoFactorGraceRemaining(): Attribute
    {
        return new Attribute(function () {
            // Abort if the user has two-factor authentication
            if ($this->hasEnabledTwoFactorAuthentication()) {
                return null;
            }

            // Get the difference by default
            return $this->two_factor_grace_until->diffForHumans();
        });
    }

    protected function defaultProfilePhotoUrl(): string
    {
        if (blank($this->full_name)) {
            return '';
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&color=569FB2&background=EBF4FF';
    }
}
