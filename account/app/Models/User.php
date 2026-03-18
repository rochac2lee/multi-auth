<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'id',
        'name',
        'avatar',
        'email',
        'password',
        'photography_studio',
        'surname',
        'instagram',
        'country_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function apps()
    {
        return $this->belongsToMany(App::class)->withTimestamps();
    }

    public function niches()
    {
        return $this->belongsToMany(Niche::class)->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
