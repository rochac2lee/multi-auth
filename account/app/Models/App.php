<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['name', 'redirect_uri', 'config'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public static function getAllowedOrigins(): array
    {
        $uris = self::whereNotNull('redirect_uri')->pluck('redirect_uri');
        $origins = [];
        foreach ($uris as $uri) {
            $parsed = parse_url($uri);
            if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
                $origin = $parsed['scheme'] . '://' . $parsed['host'];
                if (!empty($parsed['port'])) {
                    $origin .= ':' . $parsed['port'];
                }
                $origins[$origin] = true;
            }
        }
        return array_keys($origins);
    }
}
