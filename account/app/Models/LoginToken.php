<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\App;

class LoginToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'app_id',
        'redirect_uri',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function generate(string $email, ?string $appId = null, ?string $redirectUri = null): self
    {
        self::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->delete();

        return self::create([
            'email' => $email,
            'token' => Str::random(64),
            'app_id' => $appId,
            'redirect_uri' => $redirectUri,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function isValid(): bool
    {
        return $this->used_at === null
            && $this->expires_at->isFuture();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}


