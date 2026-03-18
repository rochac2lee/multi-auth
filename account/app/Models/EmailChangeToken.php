<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmailChangeToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'new_email',
        'code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function createForUser(int $userId, string $newEmail, string $code): self
    {
        return self::create([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}

