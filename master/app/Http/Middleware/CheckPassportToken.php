<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Laravel\Passport\Token as PassportToken;
use Symfony\Component\HttpFoundation\Response;

class CheckPassportToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Permitir acesso público às rotas de login
        if ($request->is('login') || $request->is('api/login')) {
            return $next($request);
        }

        // Tentar obter token do cookie ou query string
        $token = $request->cookie('sso_token') ?? $request->query('token');

        if ($token) {
            // Se o token estiver no cookie, pode estar encryptado
            if ($request->cookie('sso_token')) {
                try {
                    $token = decrypt($token);
                } catch (\Exception $e) {
                    Log::warning('Failed to decrypt sso_token cookie: ' . $e->getMessage());
                    return $this->redirectToLogin($request);
                }
            }

            // Validar token usando Passport - decodificar JWT para obter jti
            try {
                $tokenParts = explode('.', $token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

                    if (isset($payload['jti'])) {
                        // O jti no JWT corresponde ao ID na tabela oauth_access_tokens
                        $tokenData = PassportToken::where('id', $payload['jti'])
                            ->where('revoked', false)
                            ->first();

                        if ($tokenData && $tokenData->user_id) {
                            // Verificar se o token não expirou
                            if ($tokenData->expires_at && $tokenData->expires_at->isPast()) {
                                Log::info('Passport token expired for user_id: ' . $tokenData->user_id);
                                return $this->redirectToLogin($request);
                            }

                            $user = User::find($tokenData->user_id);
                            if ($user) {
                                // Autenticar o usuário usando o guard API (Passport)
                                Auth::guard('api')->setUser($user);
                                // Também definir no guard web para compatibilidade com @auth no Blade
                                Auth::setUser($user);

                                // Se o token veio da query string, salvar no cookie e limpar da URL
                                if ($request->has('token') && !$request->cookie('sso_token')) {
                                    $url = $request->url();
                                    $query = $request->query();
                                    unset($query['token']);
                                    $cleanUrl = $url . (!empty($query) ? '?' . http_build_query($query) : '');

                                    return redirect($cleanUrl)
                                        ->cookie('sso_token', encrypt($token), 60 * 24 * 15, '/', null, false, false);
                                }

                                return $next($request);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao validar token Passport: ' . $e->getMessage());
            }
        }

        // Se não houver token válido, redirecionar para login
        return $this->redirectToLogin($request);
    }

    private function redirectToLogin(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Se for uma requisição web, redirecionar para login
        $redirectUri = $request->fullUrl();
        return redirect()->route('login', ['redirect_uri' => $redirectUri]);
    }
}

