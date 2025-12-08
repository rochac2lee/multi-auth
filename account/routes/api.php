<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Rota para verificar autenticação via sessão web
Route::middleware('web')->get('/check-auth', function (Request $request) {
    if (Auth::check()) {
        return response()->json(['authenticated' => true, 'user' => Auth::user()])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
    return response()->json(['authenticated' => false], 401)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Credentials', 'true');
});

