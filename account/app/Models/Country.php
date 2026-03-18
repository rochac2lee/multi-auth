<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'name',
        'alpha2',
        'alpha3',
        'numeric_code',
        'flag',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

