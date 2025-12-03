<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Verificar se já tem token válido no cookie
        $token = $request->cookie('sso_token');

        if ($token) {
            try {
                $decryptedToken = decrypt($token);

                // Validar token Passport
                $tokenParts = explode('.', $decryptedToken);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

                    if (isset($payload['jti'])) {
                        $tokenData = \Laravel\Passport\Token::where('id', $payload['jti'])
                            ->where('revoked', false)
                            ->first();

                        if ($tokenData && $tokenData->user_id && (!$tokenData->expires_at || !$tokenData->expires_at->isPast())) {
                            // Token válido - se houver redirect_uri, redirecionar com token
                            if ($request->has('redirect_uri')) {
                                $redirectUri = urldecode($request->get('redirect_uri'));
                                $cleanUri = preg_replace('/[?#].*$/', '', $redirectUri);
                                if (!parse_url($cleanUri, PHP_URL_PATH)) {
                                    $cleanUri = rtrim($cleanUri, '/') . '/';
                                }
                                return redirect()->away($cleanUri . '?token=' . urlencode($decryptedToken));
                            }

                            // Se não houver redirect_uri, redirecionar para home
                            return redirect('/');
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao validar token no showLoginForm: ' . $e->getMessage());
            }
        }

        return view('auth.login');
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verificar credenciais
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('redirect_uri', 'email'))->withErrors([
                'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
            ]);
        }

        // Criar token de acesso para o usuário usando Passport
        $token = $user->createToken('SSO Token')->accessToken;

        // Se houver redirect_uri específico, redirecionar para ele com o token
        $redirectUri = $request->get('redirect_uri');
        if ($redirectUri) {
            $decodedUri = urldecode($redirectUri);

            // Limpar qualquer token ou query parameters existentes
            // Remover tudo após ? ou # para garantir URL limpa
            $cleanUri = preg_replace('/[?#].*$/', '', $decodedUri);

            // Se não tiver path, adicionar /
            if (!parse_url($cleanUri, PHP_URL_PATH)) {
                $cleanUri = rtrim($cleanUri, '/') . '/';
            }

            // Adicionar token à URL limpa
            $finalUri = $cleanUri . '?token=' . urlencode($token);

            // Fazer redirect absoluto para garantir que funcione
            // Usar domínio .localhost para compartilhar cookie entre portas
                return redirect()->away($finalUri)
                    ->cookie('sso_token', encrypt($token), 60 * 24 * 15, '/', null, false, false);
        }

        // Caso contrário, redirecionar para todas as apps
        $secondAppUrl = 'http://localhost:8002?token=' . $token;
        $thirdAppUrl = 'http://localhost:8003?token=' . $token;

        return response()
            ->view('auth.redirect', [
                'token' => $token,
                'secondAppUrl' => $secondAppUrl,
                'thirdAppUrl' => $thirdAppUrl,
            ])
                ->cookie('sso_token', encrypt($token), 60 * 24 * 15, '/', null, false, false);
    }

    public function logout(Request $request)
    {
        // Obter usuário do token
        $user = $request->user();

        // Se não conseguir obter do token, tentar obter do token Bearer ou cookie
        if (!$user && $request->bearerToken()) {
            $user = $this->getUserFromToken($request->bearerToken());
        }

        // Se ainda não conseguir, tentar do cookie
        if (!$user && $request->cookie('sso_token')) {
            try {
                $token = decrypt($request->cookie('sso_token'));
                $user = $this->getUserFromToken($token);
            } catch (\Exception $e) {
                Log::error('Erro ao descriptografar token do cookie: ' . $e->getMessage());
            }
        }

        // Revogar todos os tokens do usuário
        if ($user) {
            $user->tokens()->delete();
            Log::info('Logout - Revogou tokens do usuário ID: ' . $user->id);
        }

        // Limpar cookie e redirecionar
        $response = redirect('/');
        return $response->cookie('sso_token', '', -1, '/', null, false, false);
    }

    /**
     * Obtém o usuário a partir de um token Passport
     */
    private function getUserFromToken(string $token): ?User
    {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) === 3) {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

                if (isset($payload['jti'])) {
                    $tokenData = \Laravel\Passport\Token::where('id', $payload['jti'])
                        ->where('revoked', false)
                        ->first();

                    if ($tokenData && $tokenData->user_id) {
                        return User::find($tokenData->user_id);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao obter usuário do token: ' . $e->getMessage());
        }

        return null;
    }

    public function getUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

}

