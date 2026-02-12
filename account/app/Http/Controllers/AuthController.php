<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Session;
use App\Models\User;
use App\Models\LoginToken;
use App\Mail\LoginLinkMail;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // SEMPRE verificar se a sessão existe no banco antes de reautenticar
        // Isso evita reautenticação após logout quando a sessão foi deletada
        if (Auth::check() && $request->has('redirect_uri')) {
            $user = Auth::user();
            $sessionId = $request->session()->getId();
            $sessionExists = false;

            // Verificar se a sessão realmente existe no banco de dados
            // Se não existir, significa que foi deletada no logout
            if (config('session.driver') === 'database') {
                $sessionsTable = config('session.table', 'sessions');
                $sessionExists = DB::table($sessionsTable)
                    ->where('id', $sessionId)
                    ->where('user_id', $user->id)
                    ->exists();
            } else {
                // Para outros drivers de sessão, verificar se a sessão tem user_id
                $sessionExists = $request->session()->has('login_web_' . sha1(User::class));
            }

            // Se a sessão não existir no banco, fazer logout FORÇADO e mostrar tela de login
                if (!$sessionExists) {
                // Forçar logout mesmo que Auth::check() retorne true
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                // Deletar cookie de sessão explicitamente
                $sessionName = config('session.cookie');
                $cookie = cookie()->forget($sessionName);

                return response()
                    ->view('auth.login')
                    ->withCookie($cookie)
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

            // Se a sessão existe, gerar token e redirecionar
            $token = $user->createToken('SSO Token')->accessToken;
            $redirectUri = urldecode($request->get('redirect_uri'));
            $cleanUri = preg_replace('/[?#].*$/', '', $redirectUri);
            if (!parse_url($cleanUri, PHP_URL_PATH)) {
                $cleanUri = rtrim($cleanUri, '/') . '/';
            }
            return redirect()->away($cleanUri . '?token=' . urlencode($token));
        }

        // NÃO reautenticar automaticamente baseado em sessões do banco
        // Isso evita reautenticação após logout
        // O usuário deve fazer login explicitamente

        return view('auth.login');
    }

    public function checkAuth(Request $request)
    {
        // Esta view será usada pelos client apps para verificar autenticação via JavaScript
        // O JavaScript fará uma requisição com credentials: 'include' para acessar os cookies de sessão
        $redirectUri = $request->get('redirect_uri', env('APP_URL') . '/');
        return view('auth.verify', [
            'redirectUri' => $redirectUri,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $redirectUri = $request->get('redirect_uri');

        // Verificar se o usuário existe
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Por segurança, não revelar se o email existe ou não
            return back()->with('status', 'Se o email estiver cadastrado, você receberá um link de login em breve.');
        }

        // Gerar token de login
        $loginToken = LoginToken::generate($email, $redirectUri);

        // Criar URL de login
        $loginUrl = route('login.verify', ['token' => $loginToken->token]);
        if ($redirectUri) {
            $loginUrl .= (strpos($loginUrl, '?') !== false ? '&' : '?') . 'redirect_uri=' . urlencode($redirectUri);
        }

        // Enviar email com link de login (síncrono, sem fila)
        try {
            Log::info('Tentando enviar email para: ' . $email);
            Log::info('URL de login gerada: ' . $loginUrl);
            Log::info('Mailer configurado: ' . config('mail.default'));
            Log::info('SMTP Host: ' . config('mail.mailers.smtp.host'));
            Log::info('SMTP Port: ' . config('mail.mailers.smtp.port'));

            // Forçar uso do mailer SMTP explicitamente
            $result = Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));

            Log::info('Resultado do envio: ' . ($result ? 'sucesso' : 'falhou'));
            Log::info('Email de login enviado com sucesso para: ' . $email);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de login: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors([
                'email' => 'Erro ao enviar email. Tente novamente mais tarde.',
            ]);
        }

        return back()->with('status', 'Se o email estiver cadastrado, você receberá um link de login em breve.');
    }

    public function verifyLoginToken(Request $request, string $token)
    {
        $loginToken = LoginToken::where('token', $token)->first();

        if (!$loginToken || !$loginToken->isValid()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link de login inválido ou expirado.']);
        }

        // Buscar usuário pelo email
        $user = User::where('email', $loginToken->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Usuário não encontrado.']);
        }

        // Fazer login do usuário
        Auth::login($user);

        // Regenerar a sessão após o login
            $request->session()->regenerate();

            // Limpar TODAS as sessões antigas do mesmo usuário após regenerar
            $newSessionId = $request->session()->getId();
            Session::where('user_id', $user->id)
                ->whereRaw('id != ?', [$newSessionId])
                ->delete();

            // Forçar salvar a sessão primeiro
            $request->session()->save();

        // Atualizar o user_id na tabela sessions
            $sessionId = $request->session()->getId();
            $updated = Session::where('id', $sessionId)
                ->update(['user_id' => $user->id]);

            if ($updated === 0) {
                $request->session()->save();
                Session::where('id', $sessionId)
                    ->update(['user_id' => $user->id]);
            }

        // Marcar token como usado
        $loginToken->markAsUsed();

            // Criar token de acesso para o usuário
        $ssoToken = $user->createToken('SSO Token')->accessToken;

        // Se houver redirect_uri, redirecionar para ele com o token
        $redirectUri = $request->get('redirect_uri') ?? $loginToken->redirect_uri;
            if ($redirectUri) {
                $decodedUri = urldecode($redirectUri);
                $cleanUri = preg_replace('/[?#].*$/', '', $decodedUri);
                if (!parse_url($cleanUri, PHP_URL_PATH)) {
                    $cleanUri = rtrim($cleanUri, '/') . '/';
                }
            $finalUri = $cleanUri . '?token=' . urlencode($ssoToken);

                return redirect()->away($finalUri)
                ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
            }

            // Caso contrário, redirecionar para todas as apps
        $secondAppUrl = env('SECOND_APP_URL', 'http://second.test') . '?token=' . $ssoToken;
        $thirdAppUrl = env('THIRD_APP_URL', 'http://third.test') . '?token=' . $ssoToken;

            return response()
                ->view('auth.redirect', [
                'token' => $ssoToken,
                    'secondAppUrl' => $secondAppUrl,
                    'thirdAppUrl' => $thirdAppUrl,
                ])
            ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
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
                Log::error('Erro ao obter usuário do token: ' . $e->getMessage());
            }
        }

        // Revogar todos os tokens do usuário e limpar sessões
        if ($user) {
            // Fazer logout em todos os sistemas (second e third) ANTES de deletar os tokens
            // Isso permite criar um token temporário para autenticar as chamadas
            $this->logoutFromOtherSystems($user);

            // Deletar todos os tokens do usuário
            $user->tokens()->delete();

            // Deletar TODAS as sessões do usuário no banco de dados (não apenas limpar user_id)
            // Isso garante que o middleware CheckAuth não vai reautenticar o usuário
            Session::where('user_id', $user->id)->delete();

            Log::info('Logout - Deletou todas as sessões do usuário ID: ' . $user->id);
        } else {
            // Se não houver usuário autenticado, deletar a sessão atual mesmo assim (se houver)
            if ($sessionId) {
                Session::where('id', $sessionId)->delete();
            }
            Log::warning('Logout - Nenhum usuário encontrado para fazer logout');
        }

        // Fazer logout e invalidar sessão (apenas se houver sessão web)
        if ($request->hasSession()) {
            $sessionName = config('session.cookie');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Deletar o cookie de sessão explicitamente
            $cookie = cookie()->forget($sessionName);
        }

        // Se for uma requisição API, retornar JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully'])
                ->cookie('sso_token', '', -1, '/', null, false, false)
                ->withCookie($cookie ?? cookie()->forget(config('session.cookie')));
        }

        // Se for requisição web, retornar view que limpa localStorage e depois redireciona
        // Isso garante que o localStorage seja limpo no navegador
        // Redirecionar para login que não passa pelo middleware CheckAuth
        return redirect()->route('login')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])
            ->cookie('sso_token', '', -1, '/', null, false, false)
            ->withCookie($cookie ?? cookie()->forget(config('session.cookie')));
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

            $allowedOrigins = [
                'http://second.test',
                'http://third.test',
                'http://localhost:8002',
                'http://localhost:8003',
            ];

            $response->header('Access-Control-Allow-Credentials', 'true');
            if ($origin && in_array($origin, $allowedOrigins)) {
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

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        // Criar usuário sem senha (já que usamos login por email)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(uniqid()), // Senha aleatória que nunca será usada
        ]);

        // Redirecionar para login com mensagem de sucesso
        return redirect()->route('login')
            ->with('status', 'Cadastro realizado com sucesso! Faça login com seu email.');
    }

    /**
     * Redirecionar para o Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        $redirectUri = $request->get('redirect_uri');

        // Armazenar redirect_uri na sessão para usar no callback
        if ($redirectUri) {
            $request->session()->put('oauth_redirect_uri', $redirectUri);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Lidar com o callback do Google OAuth
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Buscar ou criar usuário
            $user = User::firstOrNew(['email' => $googleUser->getEmail()]);

            if (!$user->exists) {
                // Novo usuário - criar com dados do Google
                $user->name = $googleUser->getName();
                $user->password = Hash::make(uniqid()); // Senha aleatória que nunca será usada
                $user->save();
            } else {
                // Usuário existente - atualizar nome se necessário
                if (!$user->name || $user->name !== $googleUser->getName()) {
                    $user->name = $googleUser->getName();
                    $user->save();
                }
            }

            // Fazer login do usuário
            Auth::login($user);

            // Regenerar a sessão após o login
            $request->session()->regenerate();

            // Limpar TODAS as sessões antigas do mesmo usuário após regenerar
            $newSessionId = $request->session()->getId();
            Session::where('user_id', $user->id)
                ->whereRaw('id != ?', [$newSessionId])
                ->delete();

            // Forçar salvar a sessão primeiro
            $request->session()->save();

            // Atualizar o user_id na tabela sessions
            $sessionId = $request->session()->getId();
            $updated = Session::where('id', $sessionId)
                ->update(['user_id' => $user->id]);

            if ($updated === 0) {
                $request->session()->save();
                Session::where('id', $sessionId)
                    ->update(['user_id' => $user->id]);
            }

            // Criar token de acesso para o usuário
            $ssoToken = $user->createToken('SSO Token')->accessToken;

            // Recuperar redirect_uri da sessão
            $redirectUri = $request->session()->pull('oauth_redirect_uri') ?? $request->get('redirect_uri');

            if ($redirectUri) {
                $decodedUri = urldecode($redirectUri);
                $cleanUri = preg_replace('/[?#].*$/', '', $decodedUri);
                if (!parse_url($cleanUri, PHP_URL_PATH)) {
                    $cleanUri = rtrim($cleanUri, '/') . '/';
                }
                $finalUri = $cleanUri . '?token=' . urlencode($ssoToken);

                return redirect()->away($finalUri)
                    ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
            }

            // Caso contrário, redirecionar para todas as apps
            $secondAppUrl = env('SECOND_APP_URL', 'http://second.test') . '?token=' . $ssoToken;
            $thirdAppUrl = env('THIRD_APP_URL', 'http://third.test') . '?token=' . $ssoToken;

            return response()
                ->view('auth.redirect', [
                    'token' => $ssoToken,
                    'secondAppUrl' => $secondAppUrl,
                    'thirdAppUrl' => $thirdAppUrl,
                ])
                ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);

        } catch (\Exception $e) {
            Log::error('Erro no callback do Google OAuth: ' . $e->getMessage());
            return redirect()->route('login')
                ->withErrors(['email' => 'Erro ao fazer login com Google. Tente novamente.']);
        }
    }

    /**
     * Fazer logout em todos os sistemas (second e third)
     */
    private function logoutFromOtherSystems($user)
    {
        $secondAppUrl = env('SECOND_APP_URL', 'http://second.test:8002');
        $thirdAppUrl = env('THIRD_APP_URL', 'http://third.test:8003');

        // Para comunicação interna no Docker, usar os nomes dos serviços
        $secondApiUrl = str_contains($secondAppUrl, '.test') ? 'http://second-laravel:8000' : $secondAppUrl;
        $thirdApiUrl = str_contains($thirdAppUrl, '.test') ? 'http://third-laravel:8000' : $thirdAppUrl;

        // Criar um token temporário para o logout (antes de deletar todos os tokens)
        try {
            $token = $user->createToken('Logout Token')->accessToken;

            // Fazer logout no second
            try {
                $response = Http::timeout(3)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($secondApiUrl . '/api/logout', [
                    'email' => $user->email,
                ]);

                if ($response->successful()) {
                    Log::info('Logout no second realizado com sucesso para: ' . $user->email);
                } else {
                    Log::warning('Logout no second falhou: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao fazer logout no second: ' . $e->getMessage());
            }

            // Fazer logout no third
            try {
                $response = Http::timeout(3)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($thirdApiUrl . '/api/logout', [
                    'email' => $user->email,
                ]);

                if ($response->successful()) {
                    Log::info('Logout no third realizado com sucesso para: ' . $user->email);
                } else {
                    Log::warning('Logout no third falhou: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::warning('Erro ao fazer logout no third: ' . $e->getMessage());
            }

            // Revogar o token temporário
            $user->tokens()->where('name', 'Logout Token')->delete();
        } catch (\Exception $e) {
            Log::warning('Erro ao criar token para logout nos outros sistemas: ' . $e->getMessage());
        }
    }
}

