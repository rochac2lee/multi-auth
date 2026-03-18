<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma o usuário no payload consumido pelo front (Inertia).
     *
     * O front monta a URL final como `${AVATAR_CDN_URL}/${avatar}`.
     */
    public function toArray(Request $request): array
    {
        $country = $this->country;
        $flag = $country?->flag;
        $flagUrl = null;

        if ($flag) {
            $flagUrl = str_starts_with($flag, '/')
                ? $flag
                : '/' . $flag;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'country_id' => $country?->id,
            'country' => $country
                ? [
                    'id' => $country->id,
                    'name' => $country->name,
                    'flag_url' => $flagUrl,
                ]
                : null,
            'photography_studio' => $this->photography_studio,
            'surname' => $this->surname,
            'instagram' => $this->instagram,
            'niches' => $this->niches?->pluck('name')->values()->all() ?? [],
        ];
    }
}

