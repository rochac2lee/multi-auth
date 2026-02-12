<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rota de logout via API (para ser chamada por outros sistemas)
// Não usa middleware auth:api porque o token vem do account
Route::post('/logout', [AuthController::class, 'apiLogout']);

