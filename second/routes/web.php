<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/auth/login', [AuthController::class, 'redirectToAccount'])->name('login');
Route::get('/auth/callback', [AuthController::class, 'callback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('sso')->group(function () {
    Route::get('/', function () {
        return view('welcome', ['user' => auth()->user()]);
    });
});
