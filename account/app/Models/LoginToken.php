<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'redirect_uri',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Gerar um novo token de login
     */
    public static function generate(string $email, ?string $redirectUri = null): self
    {
        // Deletar tokens antigos não utilizados do mesmo email
        self::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->delete();

        return self::create([
            'email' => $email,
            'token' => Str::random(64),
            'redirect_uri' => $redirectUri,
            'expires_at' => now()->addMinutes(15), // Token válido por 15 minutos
        ]);
    }

    /**
     * Verificar se o token é válido
     */
    public function isValid(): bool
    {
        return $this->used_at === null
            && $this->expires_at->isFuture();
    }

    /**
     * Marcar token como usado
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}


