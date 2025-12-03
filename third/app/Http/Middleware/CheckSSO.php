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
        // Tentar obter token do cookie ou query string
        $token = $request->cookie('sso_token') ?? $request->query('token');

        if ($token) {
            // Se o token estiver no cookie, pode estar encryptado
            if ($request->cookie('sso_token')) {
                try {
                    $token = decrypt($token);
                } catch (\Exception $e) {
                    Log::warning('Failed to decrypt sso_token cookie in client: ' . $e->getMessage());
                    return $this->redirectToMasterLogin($request);
                }
            }

            // Validar token com o master
            if ($this->validateTokenWithMaster($token)) {
                // Se o token veio na query string, salvar no cookie e remover da URL
                if ($request->has('token')) {
                    $url = $request->url();
                    $query = $request->query();
                    unset($query['token']);
                    $cleanUrl = $url . (!empty($query) ? '?' . http_build_query($query) : '');

                    return redirect($cleanUrl)
                        ->cookie('sso_token', encrypt($token), 60 * 24 * 15, '/', null, false, false);
                }

                // Token válido, continuar
                return $next($request);
            }
        }

        // Se não houver token válido, redirecionar para o master login
        return $this->redirectToMasterLogin($request);
    }

    /**
     * Valida o token com o master
     */
    private function validateTokenWithMaster(string $token): bool
    {
        try {
            // Validar token com o master
            $response = Http::timeout(5)->withHeaders([
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

                // Autenticar o usuário localmente para Auth::check() funcionar
                Auth::login($user);

                return true;
            }
        } catch (Exception $e) {
            Log::error('Erro ao validar token SSO com master: ' . $e->getMessage());
        }

        return false;
    }

    private function redirectToMasterLogin(Request $request): Response
    {
        $baseUrl = $request->fullUrl();
        $redirectUri = urlencode($baseUrl);
        $masterLoginUrl = 'http://localhost:8001/login?redirect_uri=' . $redirectUri;
        return redirect($masterLoginUrl);
    }
}

