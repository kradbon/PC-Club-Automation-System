<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'avatar_path', 'email', 'password', 'role', 'balance_minutes', 'preferred_locale', 'membership_tier', 'account_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance_minutes' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ClubOrder::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'administrator'], true);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function tournamentRegistrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function foodOrders(): HasMany
    {
        return $this->hasMany(FoodOrder::class);
    }
}
