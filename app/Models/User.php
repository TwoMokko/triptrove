<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Отношение "один ко многим" (если пользователь создаёт путешествия)
    public function createdTravels(): HasMany
    {
        return $this->hasMany(Travel::class)->orderBy('order');
    }

    // Отношение "многие ко многим" (если пользователь участвует в путешествиях)
    public function sharedTravels(): BelongsToMany
    {
        return $this->belongsToMany(Travel::class);
    }

    public function emailVerification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmailVerification::class);
    }
}
