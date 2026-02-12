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

        // IMPORTANTE: Fazer logout nos outros sistemas ANTES de chamar o account
        // porque o account vai deletar todos os tokens, invalidando o token atual

        // Fazer logout no third PRIMEIRO (antes de invalidar o token)
        $thirdAppUrl = env('THIRD_APP_URL', 'http://third.test:8003');
        $thirdApiUrl = str_contains($thirdAppUrl, '.test') ? 'http://third-laravel:8000' : $thirdAppUrl;

        if ($token && $user) {
            try {
                $response = Http::timeout(3)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($thirdApiUrl . '/api/logout', [
                    'email' => $user->email,
                ]);

                if ($response->successful()) {
                    Log::info('Logout no third realizado com sucesso do second para: ' . $user->email);
                } else {
                    Log::warning('Logout no third falhou do second: ' . $response->body());
                }
            } catch (Exception $e) {
                Log::warning('Erro ao fazer logout no third: ' . $e->getMessage());
            }
        }

        // Agora fazer logout no account (que vai deletar todos os tokens)
        $accountUrl = config('app.account_url', env('ACCOUNT_URL', 'http://account.test:8001'));

        // Para comunicação interna no Docker, usar o nome do serviço
        $accountApiUrl = $accountUrl;
        if (str_contains($accountUrl, '.test')) {
            $accountApiUrl = 'http://account-laravel:8000';
        }

        if ($token) {
            try {
                // Chamar logout no account para limpar todas as sessões
                $response = Http::timeout(5)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($accountApiUrl . '/api/logout');

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

        // Deletar o cookie de sessão explicitamente
        $sessionName = config('session.cookie');
        $cookie = cookie()->forget($sessionName);

        // Forçar salvar a sessão vazia para garantir que não seja recriada
        $request->session()->save();

        // Redirecionar para login sem passar pelo middleware
        $accountLoginUrl = $accountUrl . '/login';

        return redirect($accountLoginUrl)
            ->withCookie($cookie)
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }

    /**
     * Logout via API (chamado por outros sistemas)
     * Recebe o email do usuário e faz logout de todas as sessões desse usuário
     */
    public function apiLogout(Request $request)
    {
        $email = $request->input('email');

        if (!$email) {
            return response()->json(['error' => 'Email is required'], 400);
        }

        // Encontrar o usuário pelo email
        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            // Fazer logout se o usuário estiver autenticado
            if (Auth::check() && Auth::user()->id === $user->id) {
                Auth::logout();
            }

            // Deletar TODAS as sessões do usuário no banco de dados
            if (config('session.driver') === 'database') {
                $sessionsTable = config('session.table', 'sessions');
                DB::table($sessionsTable)->where('user_id', $user->id)->delete();
            }
        }

        // Limpar sessão atual se houver
        if ($request->hasSession()) {
            Session::flush();
            Session::forget('sso_token');
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}

