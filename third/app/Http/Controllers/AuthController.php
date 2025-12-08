<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function redirectToAccount()
    {
        $redirectUri = urlencode(env('APP_URL'));
        $accountUrl = config('app.account_url', env('ACCOUNT_URL', 'http://account.test:8001'));
        $accountLoginUrl = $accountUrl . '/login?redirect_uri=' . $redirectUri;

        return redirect($accountLoginUrl);
    }

    public function logout(Request $request)
    {
        $token = Session::get('sso_token');
        $user = Auth::user();

        // Revogar token no account ANTES do logout local
        $accountUrl = config('app.account_url', env('ACCOUNT_URL', 'http://account.test:8001'));

        if ($token) {
            try {
                // Chamar logout no account para limpar todas as sessões
                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($accountUrl . '/api/logout');

                // Aguardar um pouco para garantir que o account processou o logout
                if ($response->successful()) {
                    usleep(500000); // 0.5 segundos
                }
            } catch (Exception $e) {
                Log::error('Erro ao revogar token no account: ' . $e->getMessage());
            }
        }

        // Fazer logout local e destruir completamente a sessão
        $sessionId = $request->session()->getId();
        $userId = $user ? $user->id : null;

        // Fazer logout ANTES de deletar sessões (para garantir que Auth::logout() funcione)
        Auth::logout();

        // Deletar TODAS as sessões do usuário no banco de dados
        if (config('session.driver') === 'database') {
            $sessionsTable = config('session.table', 'sessions');
            // Deletar a sessão atual
            DB::table($sessionsTable)
                ->where('id', $sessionId)
                ->delete();

            // Deletar todas as outras sessões do usuário (se houver)
            if ($userId) {
                DB::table($sessionsTable)
                    ->where('user_id', $userId)
                    ->delete();
            }
        }

        // Limpar todos os dados da sessão
        Session::flush();
        Session::forget('sso_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Forçar salvar a sessão vazia para garantir que não seja recriada
        $request->session()->save();

        // Redirecionar para uma view que limpa localStorage e depois redireciona
        // Isso garante que o localStorage seja limpo no navegador
        $accountLoginUrl = $accountUrl . '/login';

        return response()->view('auth.logout', [
            'accountLoginUrl' => $accountLoginUrl
        ])->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
}

