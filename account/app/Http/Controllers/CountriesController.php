<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountriesController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::query()
            ->orderBy('order')
            ->get()
            ->map(function (Country $country) {
                $flag = (string) $country->flag;
                $flagUrl = str_starts_with($flag, '/')
                    ? $flag
                    : '/' . $flag;

                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'alpha2' => $country->alpha2,
                    'flag_url' => $flagUrl,
                ];
            })
            ->values();

        return response()->json($countries);
    }
}

