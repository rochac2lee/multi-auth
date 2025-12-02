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

        // Se não estiver autenticado, redirecionar para o master login
        // O master verificará se já está logado e redirecionará com token
        $baseUrl = $request->url();
        $redirectUri = urlencode($baseUrl);
        $masterLoginUrl = 'http://localhost:8001/login?redirect_uri=' . $redirectUri;

        return redirect($masterLoginUrl);
    }

    private function handleToken(Request $request, string $token, Closure $next): Response
    {
        try {
            // Validar token com o master
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get('http://master-laravel:8000/api/user');

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
                $url = $request->url();
                $query = $request->query();
                unset($query['token']);
                $cleanUrl = $url . (!empty($query) ? '?' . http_build_query($query) : '');

                return redirect($cleanUrl);
            }
        } catch (Exception $e) {
            Log::error('Erro ao validar token SSO: ' . $e->getMessage());
        }

        // Token inválido, redirecionar para login
        // IMPORTANTE: Usar apenas a URL base, sem query parameters
        $baseUrl = $request->url();
        $redirectUri = urlencode($baseUrl);
        return redirect('http://localhost:8001/login?redirect_uri=' . $redirectUri);
    }
}

