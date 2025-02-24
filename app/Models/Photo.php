<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $table = 'photos';
//    protected $guarded = [];
    protected $guarded = false;

    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }
}
