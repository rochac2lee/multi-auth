<?php

namespace App\Http\Controllers;

use App\Models\Niche;
use Illuminate\Http\JsonResponse;

class NichesController extends Controller
{
    public function index(): JsonResponse
    {
        $niches = Niche::query()
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(function ($niche) {
                return [
                    'id' => $niche->id,
                    'name' => $niche->name,
                ];
            })
            ->values();

        return response()->json($niches);
    }
}
