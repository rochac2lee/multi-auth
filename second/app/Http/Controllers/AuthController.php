<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function redirectToMaster()
    {
        $redirectUri = urlencode('http://localhost:8002');
        $masterLoginUrl = 'http://localhost:8001/login?redirect_uri=' . $redirectUri;

        return redirect($masterLoginUrl);
    }

    public function logout(Request $request)
    {
        $token = $request->cookie('sso_token');

        // Revogar token no master
        if ($token) {
            try {
                $decryptedToken = decrypt($token);
                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $decryptedToken,
                ])->post('http://master-laravel:8000/api/logout');

                if ($response->successful()) {
                    Log::info('Token revogado no master pelo cliente.');
                } else {
                    Log::warning('Falha ao revogar token no master pelo cliente: ' . $response->body());
                }
            } catch (Exception $e) {
                Log::error('Erro ao revogar token no master pelo cliente: ' . $e->getMessage());
            }
        }

        // Fazer logout local
        Auth::logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Limpar cookie local e redirecionar para o master login
        return redirect()->away('http://localhost:8001/login')
            ->cookie('sso_token', '', -1, '/', null, false, false);
    }
}


