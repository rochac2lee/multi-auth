<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSSO
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se já estiver autenticado localmente, continuar
        if (Auth::check()) {
            return $next($request);
        }

        // Verificar se há token na query string (vindo do callback)
        $token = $request->get('token');
        if ($token) {
            // Token fornecido, validar e criar sessão
            return $this->handleToken($request, $token, $next);
        }

        // Se não estiver autenticado, redirecionar para o account login
        // O account verificará se já está logado e redirecionará com token
        $baseUrl = $request->url();
        $redirectUri = urlencode($baseUrl);
        $accountUrl = env('ACCOUNT_URL', 'http://account.test:8001');
        $accountLoginUrl = $accountUrl . '/login?redirect_uri=' . $redirectUri;

        return redirect($accountLoginUrl);
    }

    private function handleToken(Request $request, string $token, Closure $next): Response
    {
        try {
            // Validar token com o account
            // Usar o nome do serviço Docker para comunicação interna
            $accountUrl = env('ACCOUNT_URL', 'http://account-laravel:8000');
            // Se estiver usando domínio .test, usar o nome do serviço para comunicação interna
            if (str_contains($accountUrl, '.test')) {
                $accountUrl = 'http://account-laravel:8000';
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get($accountUrl . '/api/user');

            if ($response->successful()) {
                $userData = $response->json();

                // Criar ou atualizar usuário local
                $user = User::firstOrNew(['email' => $userData['email']]);
                $user->name = $userData['name'];
                if (!$user->exists) {
                    // Se é um novo usuário, gerar uma senha aleatória (não será usada para login)
                    $user->password = bcrypt(str()->random(32));
                }
                $user->save();

                // Fazer login do usuário
                Auth::login($user);

                // Armazenar token na sessão
                session(['sso_token' => $token]);

                // Remover token da URL e redirecionar
                // IMPORTANTE: Usar redirect()->to() para evitar que o middleware seja aplicado novamente
                $url = $request->url();
                $query = $request->query();
                unset($query['token']);
                $cleanUrl = $url . (!empty($query) ? '?' . http_build_query($query) : '');

                // Fazer redirect sem passar pelo middleware novamente
                return redirect()->to($cleanUrl);
            }
        } catch (Exception $e) {
            Log::error('Erro ao validar token SSO: ' . $e->getMessage());
        }

        // Token inválido, redirecionar para login
        // IMPORTANTE: Usar apenas a URL base, sem query parameters
        $baseUrl = $request->url();
        $redirectUri = urlencode($baseUrl);
        $accountUrl = env('ACCOUNT_URL', 'http://account.test:8001');
        return redirect($accountUrl . '/login?redirect_uri=' . $redirectUri);
    }
}

