<?php

use App\Models\App;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\NichesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'home'])->name('home');

// Rotas nomeadas exigidas pela view "minha-conta" (copiada do youfocus).
// Aqui elas só evitam quebra do Blade no account.
Route::get('/selpics/pricing', fn() => redirect('/'))->name('selpics.pricing');
Route::get('/selpics', fn() => redirect('/'))->name('selpics');
Route::get('/workspace/attach-youbox', fn() => redirect('/'))->name('workspace.attach-youbox');
Route::get('/account/terminate', fn() => redirect('/'))->name('account.terminate.index');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login/verify/{token}', [AuthController::class, 'verifyLoginToken'])->name('login.verify');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/generate-token', [AuthController::class, 'generateToken'])->middleware('web');

Route::middleware('web')->group(function () {
    Route::post('/account/avatar', [UserController::class, 'uploadAvatar'])->name('account.avatar.upload');
    Route::delete('/account/avatar', [UserController::class, 'deleteAvatar'])->name('account.avatar.delete');
    Route::post('/account/profile', [UserController::class, 'updateProfile'])->name('account.profile.update');

    Route::post('/account/email/change', [UserController::class, 'requestEmailChangeCode']);
    Route::post('/account/email/verify', [UserController::class, 'verifyEmailChangeCode']);
});

Route::get('/countries', [CountriesController::class, 'index'])->name('countries');

Route::get('/niches', [NichesController::class, 'index'])->name('niches');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::options('/api/generate-token', function (Request $request) {
    $origin = $request->headers->get('Origin');
    $response = response('', 200);
    $allowedOrigins = App::getAllowedOrigins();


    if ($origin && in_array($origin, $allowedOrigins)) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
});

Route::get('/api/check-auth', function (Request $request) {
    $origin = $request->headers->get('Origin');
    $allowedOrigins = App::getAllowedOrigins();

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

    if ($origin && in_array($origin, $allowedOrigins)) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
})->middleware('web');

Route::options('/api/check-auth', function (Request $request) {
    $origin = $request->headers->get('Origin');
    $response = response('', 200);
    $allowedOrigins = App::getAllowedOrigins();

    $response->header('Access-Control-Allow-Credentials', 'true');
    $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

    if ($origin && in_array($origin, $allowedOrigins)) {
        $response->header('Access-Control-Allow-Origin', $origin);
    } else {
        $response->header('Access-Control-Allow-Origin', '*');
    }

    return $response;
});
