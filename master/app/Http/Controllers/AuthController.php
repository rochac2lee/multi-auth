<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Verificar se já estiver logado na sessão e houver redirect_uri, gerar token e redirecionar
        if (Auth::check() && $request->has('redirect_uri')) {
            $user = Auth::user();
            $token = $user->createToken('SSO Token')->accessToken;
            $redirectUri = urldecode($request->get('redirect_uri'));
            $cleanUri = preg_replace('/[?#].*$/', '', $redirectUri);
            if (!parse_url($cleanUri, PHP_URL_PATH)) {
                $cleanUri = rtrim($cleanUri, '/') . '/';
            }
            return redirect()->away($cleanUri . '?token=' . urlencode($token));
        }

        // Verificar se há uma sessão ativa no banco de dados com user_id
        // Buscar nas últimas sessões ativas (últimas 2 horas) ordenadas por last_activity
        if ($request->has('redirect_uri')) {
            $twoHoursAgo = now()->subHours(2)->timestamp;
            $existingSession = Session::where('last_activity', '>', $twoHoursAgo)
                ->whereNotNull('user_id')
                ->orderBy('last_activity', 'desc')
                ->first();

            if ($existingSession && $existingSession->user_id) {
                $user = User::find($existingSession->user_id);

                if ($user) {
                    // Reutilizar a sessão existente em vez de criar uma nova
                    $currentSessionId = $request->session()->getId();

                    // Limpar todas as outras sessões do mesmo usuário, mantendo apenas a existente
                    Session::where('user_id', $user->id)
                        ->whereRaw('id != ?', [$existingSession->id])
                        ->delete();

                    // Se a sessão atual é diferente da existente, deletar a atual
                    if ($currentSessionId !== $existingSession->id) {
                        Session::where('id', $currentSessionId)->delete();
                        // Usar a sessão existente
                        $request->session()->setId($existingSession->id);
                    }

                    // Autenticar o usuário reutilizando a sessão existente
                    Auth::setUser($user);

                    $token = $user->createToken('SSO Token')->accessToken;
                    $redirectUri = urldecode($request->get('redirect_uri'));
                    $cleanUri = preg_replace('/[?#].*$/', '', $redirectUri);
                    if (!parse_url($cleanUri, PHP_URL_PATH)) {
                        $cleanUri = rtrim($cleanUri, '/') . '/';
                    }
                    return redirect()->away($cleanUri . '?token=' . urlencode($token));
                }
            }
        }

        return view('auth.login');
    }

    public function checkAuth(Request $request)
    {
        // Esta view será usada pelos client apps para verificar autenticação via JavaScript
        // O JavaScript fará uma requisição com credentials: 'include' para acessar os cookies de sessão
        $redirectUri = $request->get('redirect_uri', 'http://localhost:8001/');
        return view('auth.verify', [
            'redirectUri' => $redirectUri,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Regenerar a sessão após o login (isso cria uma nova sessão)
            $oldSessionId = $request->session()->getId();
            $request->session()->regenerate();

            // Limpar TODAS as sessões antigas do mesmo usuário após regenerar
            // Isso garante que apenas a sessão atual (nova) seja mantida
            $newSessionId = $request->session()->getId();
            Session::where('user_id', $user->id)
                ->whereRaw('id != ?', [$newSessionId])
                ->delete();

            // Forçar salvar a sessão primeiro
            $request->session()->save();

            // Agora atualizar o user_id na tabela sessions
            $sessionId = $request->session()->getId();
            $updated = Session::where('id', $sessionId)
                ->update(['user_id' => $user->id]);

            // Se não atualizou (sessão ainda não existe), criar/atualizar manualmente
            if ($updated === 0) {
                // Forçar salvar novamente e tentar atualizar
                $request->session()->save();
                Session::where('id', $sessionId)
                    ->update(['user_id' => $user->id]);
            }

            // Garantir que a sessão está sendo mantida
            \Log::info('Login - Session ID após regenerate: ' . $sessionId);
            \Log::info('Login - Auth::check(): ' . (Auth::check() ? 'true' : 'false'));
            \Log::info('Login - User ID: ' . $user->id);
            \Log::info('Login - Updated rows: ' . $updated);

            // Criar token de acesso para o usuário
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

        return back()->withInput($request->only('redirect_uri'))->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ]);
    }

    public function logout(Request $request)
    {
        // Obter usuário - pode ser via web (Auth::user()) ou via API ($request->user())
        // O middleware auth:api já configura $request->user() automaticamente
        $user = $request->user() ?? Auth::user();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        // Se ainda não conseguiu obter o usuário e houver token Bearer, tentar obter do token
        if (!$user && $request->bearerToken()) {
            try {
                $token = $request->bearerToken();
                // Passport armazena tokens com hash SHA256
                $tokenHash = hash('sha256', $token);
                $tokenData = \Laravel\Passport\Token::where('id', $tokenHash)->first();
                if ($tokenData && $tokenData->user_id) {
                    $user = User::find($tokenData->user_id);
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao obter usuário do token: ' . $e->getMessage());
            }
        }

        // Revogar todos os tokens do usuário e limpar sessões
        if ($user) {
            // Deletar todos os tokens do usuário
            $user->tokens()->delete();

            // Limpar user_id de TODAS as sessões do usuário no banco de dados
            // Isso garante que o middleware CheckAuth não vai reautenticar o usuário
            Session::where('user_id', $user->id)
                ->update(['user_id' => null]);

            \Log::info('Logout - Limpou sessões do usuário ID: ' . $user->id);
        } else {
            // Se não houver usuário autenticado, limpar a sessão atual mesmo assim (se houver)
            if ($sessionId) {
                Session::where('id', $sessionId)
                    ->update(['user_id' => null]);
            }
            \Log::warning('Logout - Nenhum usuário encontrado para fazer logout');
        }

        // Fazer logout e invalidar sessão (apenas se houver sessão web)
        if ($request->hasSession()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Se for uma requisição API, retornar JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully'])
                ->cookie('sso_token', '', -1, '/', null, false, false);
        }

        // Se for requisição web, redirecionar
        $response = redirect('/');
        return $response->cookie('sso_token', '', -1, '/', null, false, false);
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

    public function generateToken(Request $request)
    {
        // Verificar se está autenticado via sessão web
        if (!Auth::check()) {
            $origin = $request->headers->get('Origin');
            $response = response()->json(['error' => 'Unauthenticated'], 401);

            $response->header('Access-Control-Allow-Credentials', 'true');
            if ($origin && in_array($origin, ['http://localhost:8002', 'http://localhost:8003'])) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                $response->header('Access-Control-Allow-Origin', '*');
            }

            return $response;
        }

        $user = Auth::user();
        $tokenResult = $user->createToken('SSO Token');

        $origin = $request->headers->get('Origin');
        $response = response()->json([
            'token' => $tokenResult->accessToken,
        ]);

        $response->header('Access-Control-Allow-Credentials', 'true');
        if ($origin && in_array($origin, ['http://localhost:8002', 'http://localhost:8003'])) {
            $response->header('Access-Control-Allow-Origin', $origin);
        } else {
            $response->header('Access-Control-Allow-Origin', '*');
        }

        return $response;
    }
}

