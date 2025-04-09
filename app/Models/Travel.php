<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Travel extends Model
{
    use HasFactory;

    protected $table = 'travels';
//    protected $guarded = [];
    protected $guarded = false;

    // Для "один ко многим" (создатель путешествия)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Для "многие ко многим" (участники путешествия)
    public function users(): BelongsToMany
    {
//        return $this->belongsToMany(User::class);
        return $this->belongsToMany(User::class, 'travel_user', 'travel_id', 'user_id')
            ->withTimestamps(); // если нужно сохранять created_at/updated_at
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
