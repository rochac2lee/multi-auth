<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
})->middleware('check.auth');

Route::get('/auth/check', [AuthController::class, 'checkAuth'])->name('auth.check');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/generate-token', [AuthController::class, 'generateToken'])->middleware('web');

Route::options('/api/generate-token', function (Request $request) {
    $origin = $request->headers->get('Origin');
    $response = response('', 200);

    $response->header('Access-Control-Allow-Credentials', 'true');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');

    if ($origin && in_array($origin, ['http://localhost:8002', 'http://localhost:8003'])) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
});

Route::get('/api/check-auth', function (Request $request) {
    $origin = $request->headers->get('Origin');

    \Log::info('check-auth - Session ID: ' . $request->session()->getId());
    \Log::info('check-auth - Auth::check(): ' . (Auth::check() ? 'true' : 'false'));
    \Log::info('check-auth - Cookies: ' . json_encode($request->cookies->all()));

    if (Auth::check()) {
        $response = response()->json(['authenticated' => true, 'user' => [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ]]);
    } else {
        $response = response()->json(['authenticated' => false], 401);
    }

    $response->header('Access-Control-Allow-Credentials', 'true');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

    if ($origin && in_array($origin, ['http://localhost:8002', 'http://localhost:8003'])) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
})->middleware('web');

Route::options('/api/check-auth', function (Request $request) {
    $origin = $request->headers->get('Origin');
    $response = response('', 200);

    $response->header('Access-Control-Allow-Credentials', 'true');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

    if ($origin && in_array($origin, ['http://localhost:8002', 'http://localhost:8003'])) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
});
