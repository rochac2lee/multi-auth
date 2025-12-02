<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
        $token = Session::get('sso_token');
        $user = Auth::user();

        // Fazer logout local primeiro
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Session::forget('sso_token');

        // Revogar token no master DEPOIS do logout local
        // Isso garante que mesmo se houver erro, o logout local já foi feito
        if ($token) {
            try {
                // Chamar logout no master para limpar todas as sessões
                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post('http://master-laravel:8000/api/logout');

                // Aguardar um pouco para garantir que o master processou o logout
                if ($response->successful()) {
                    usleep(500000); // 0.5 segundos
                }
            } catch (Exception $e) {
                Log::error('Erro ao revogar token no master: ' . $e->getMessage());
            }
        }

        // Redirecionar para o master login (que não tem sessão ativa agora)
        return redirect()->away('http://localhost:8001/login');
    }
}

