<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\App;
use App\Models\Session;
use App\Models\User;
use App\Models\LoginToken;
use App\Services\SsoAppUserService;
use App\Mail\LoginLinkMail;
use Illuminate\Support\Facades\Storage;
use Exception;
use Laravel\Passport\Token;
use Throwable;

class AuthController extends Controller
{
    private function decodeAppId(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = base64_decode($value, true);
        return $decoded !== false ? $decoded : null;
    }

    private function encodeAppId(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return base64_encode($value);
    }

    public function showLoginForm(Request $request)
    {
        $appIdRaw = $request->get('app_id');
        $appId = $this->decodeAppId($appIdRaw);

        if (Auth::check() && $appId) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $sessionId = $request->session()->getId();
            $sessionExists = false;

            if (config('session.driver') === 'database') {
                $sessionsTable = config('session.table', 'sessions');
                $sessionExists = DB::table($sessionsTable)
                    ->where('id', $sessionId)
                    ->where('user_id', $user->id)
                    ->exists();
            } else {
                $sessionExists = $request->session()->has('login_web_' . sha1(User::class));
            }

            if (!$sessionExists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $sessionName = config('session.cookie');
                $cookie = cookie()->forget($sessionName);
                return response()
                    ->view('auth.login', ['app_id' => $appIdRaw])
                    ->withCookie($cookie)
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

            $app = $appId ? App::find($appId) : null;
            if (!$app || !$app->redirect_uri) {
                return view('auth.login', ['app_id' => $appIdRaw]);
            }

            $linked = $user->apps()->where('apps.id', $appId)->exists();
            if (!$linked) {
                // Sem `app_id` no redirect para evitar loop infinito nesse GET.
                return redirect()->route('login')
                    ->withErrors(['email' => 'Você não está vinculado a este aplicativo.']);
            }

            $token = $user->createToken('SSO Token')->accessToken;
            $cleanUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
            if (!parse_url($cleanUri, PHP_URL_PATH)) {
                $cleanUri = rtrim($cleanUri, '/') . '/';
            }
            return redirect()->away($cleanUri . '?token=' . urlencode($token));
        }

        return view('auth.login', ['app_id' => $appIdRaw]);
    }

    public function checkAuth(Request $request)
    {
        $appIdRaw = $request->get('app_id');
        $redirectUri = null;
        if ($appIdRaw) {
            $appId = $this->decodeAppId($appIdRaw);
            $app = $appId ? App::find($appId) : null;
            if ($app && $app->redirect_uri) {
                $redirectUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
            }
        }
        return view('auth.verify', [
            'appId' => $appIdRaw,
            'redirectUri' => $redirectUri,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'app_id' => 'nullable|string',
        ]);

        $appIdRaw = $request->get('app_id');
        $appId = $appIdRaw ? $this->decodeAppId($appIdRaw) : null;
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('status', 'Se o email estiver cadastrado, você receberá um link de login em breve.');
        }

        if ($appId) {
            $linked = $user->apps()->where('apps.id', $appId)->exists();
            if (!$linked) {
                return back()->withErrors(['email' => 'Você não está vinculado a este aplicativo.']);
            }
        }

        $redirectUri = null;
        if ($appId) {
            $app = App::find($appId);
            if ($app && $app->redirect_uri) {
                $redirectUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
            }
        }

        $loginToken = LoginToken::generate($email, $appId, $redirectUri);

        $loginUrl = route('login.verify', ['token' => $loginToken->token]);
        if ($appId) {
            $loginUrl .= (strpos($loginUrl, '?') !== false ? '&' : '?') . 'app_id=' . urlencode($this->encodeAppId($appId));
        }

        try {
            Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));
        } catch (Exception $e) {
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

        $user = User::where('email', $loginToken->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Usuário não encontrado.']);
        }

        Auth::login($user);

        $request->session()->regenerate();
        $newSessionId = $request->session()->getId();
        Session::where('user_id', $user->id)
            ->whereRaw('id != ?', [$newSessionId])
            ->delete();

        $request->session()->save();
        $sessionId = $request->session()->getId();
        $updated = Session::where('id', $sessionId)
            ->update(['user_id' => $user->id]);

        if ($updated === 0) {
            $request->session()->save();
            Session::where('id', $sessionId)
                ->update(['user_id' => $user->id]);
        }

        $loginToken->markAsUsed();

        $ssoToken = $user->createToken('SSO Token')->accessToken;

        $redirectUri = $loginToken->redirect_uri;
        if (!$redirectUri) {
            $appIdFromToken = $loginToken->app_id;
            if ($appIdFromToken) {
                $app = App::find($appIdFromToken);
                if ($app && $app->redirect_uri) {
                    $redirectUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
                }
            }
        }

        if ($redirectUri) {
            $cleanUri = preg_replace('/[?#].*$/', '', $redirectUri);
            if (!parse_url($cleanUri, PHP_URL_PATH)) {
                $cleanUri = rtrim($cleanUri, '/') . '/';
            }
            $separator = str_contains($cleanUri, '?') ? '&' : '?';
            $finalUri = $cleanUri . $separator . 'token=' . urlencode($ssoToken);
            return redirect()->away($finalUri)
                ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
        }

        $appIdFromRequest = $request->get('app_id');
        $appId = $appIdFromRequest ? $this->decodeAppId($appIdFromRequest) : $loginToken->app_id;
        if ($appId) {
            $linked = $user->apps()->where('apps.id', $appId)->exists();
            if (!$linked) {
                return redirect()->route('login', ['app_id' => $this->encodeAppId($appId)])
                    ->withErrors(['email' => 'Você não está vinculado a este aplicativo.']);
            }
            $app = $app ?? App::find($appId);
            if ($app && $app->redirect_uri) {
                $cleanUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
                if (!parse_url($cleanUri, PHP_URL_PATH)) {
                    $cleanUri = rtrim($cleanUri, '/') . '/';
                }
                $finalUri = $cleanUri . '?token=' . urlencode($ssoToken);
                return redirect()->away($finalUri)
                    ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
            }
        }

        $appUrls = $user->apps()
            ->whereNotNull('redirect_uri')
            ->pluck('redirect_uri')
            ->map(fn($uri) => rtrim(preg_replace('/[?#].*$/', '', $uri), '/') . '/?token=' . urlencode($ssoToken))
            ->values()
            ->all();

        return response()
            ->view('auth.redirect', [
                'token' => $ssoToken,
                'appUrls' => $appUrls,
            ])
            ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
    }

    public function logout(Request $request)
    {
        $user = $request->user() ?? Auth::user();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        if (!$user && $request->bearerToken()) {
            try {
                $token = $request->bearerToken();
                $tokenHash = hash('sha256', $token);
                $tokenData = Token::where('id', $tokenHash)->first();
                if ($tokenData && $tokenData->user_id) {
                    $user = User::find($tokenData->user_id);
                }
            } catch (Exception $e) {
            }
        }

        if ($user) {
            $this->logoutFromOtherSystems($user);
            $user->tokens()->delete();
            Session::where('user_id', $user->id)->delete();
        } else {
            if ($sessionId) {
                Session::where('id', $sessionId)->delete();
            }
        }

        if ($request->hasSession()) {
            $sessionName = config('session.cookie');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $cookie = cookie()->forget($sessionName);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Logged out successfully'])
                ->cookie('sso_token', '', -1, '/', null, false, false)
                ->withCookie($cookie ?? cookie()->forget(config('session.cookie')));
        }

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

    public function getUserApps(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $apps = $user->apps()->get(['apps.id', 'apps.name', 'apps.redirect_uri']);

        return response()->json($apps->map(fn($app) => [
            'id'   => $app->id,
            'name' => $app->name,
        ])->values());
    }

    public function linkCurrentUserToApp(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'app_id' => 'required|exists:apps,id',
        ]);

        $app = App::find($validated['app_id']);

        if (!$app) {
            return response()->json(['error' => 'App not found'], 404);
        }

        $user->apps()->syncWithoutDetaching([$app->id]);

        return response()->json([
            'linked' => true,
            'app_id' => $app->id,
        ]);
    }

    public function generateToken(Request $request)
    {
        $allowedOrigins = App::getAllowedOrigins();

        if (!Auth::check()) {
            $origin = $request->headers->get('Origin');
            $response = response()->json(['error' => 'Unauthenticated'], 401);

            $response->header('Access-Control-Allow-Credentials', 'true');
            if ($origin && in_array($origin, $allowedOrigins)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                $response->header('Access-Control-Allow-Origin', '*');
            }

            return $response;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tokenResult = $user->createToken('SSO Token');

        $origin = $request->headers->get('Origin');
        $response = response()->json([
            'token' => $tokenResult->accessToken,
        ]);

        $response->header('Access-Control-Allow-Credentials', 'true');
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
        } else {
            $response->header('Access-Control-Allow-Origin', '*');
        }

        return $response;
    }

    public function showRegisterForm(Request $request)
    {
        return view('auth.register', ['app_id' => $request->get('app_id')]);
    }

    public function register(Request $request, SsoAppUserService $ssoAppUserService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'app_id' => 'nullable|string',
        ]);

        $appIdRaw = $validated['app_id'] ?? null;
        $appId = $appIdRaw ? $this->decodeAppId($appIdRaw) : null;

        Log::info('Account register: incoming request', [
            'email' => $validated['email'],
            'app_id_raw' => $appIdRaw,
            'app_id_decoded' => $appId,
        ]);

        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            $email = $existingUser->email;

            if ($appId) {
                $app = App::find($appId);
                if ($app) {
                    $existingUser->apps()->syncWithoutDetaching([$app->id]);
                    $ssoAppUserService->syncRemoteUserForApp(
                        $app,
                        $existingUser->name ?? $validated['name'],
                        $existingUser->email,
                        (int) $existingUser->id
                    );
                }

                $redirectUri = null;
                if ($app && $app->redirect_uri) {
                    $redirectUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
                }

                $loginToken = LoginToken::generate($email, $appId, $redirectUri);

                $loginUrl = route('login.verify', ['token' => $loginToken->token]);
                $loginUrl .= (strpos($loginUrl, '?') !== false ? '&' : '?') . 'app_id=' . urlencode($this->encodeAppId($appId));

                try {
                    Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));
                } catch (Exception $e) {
                    return redirect()->route('login', ['app_id' => $this->encodeAppId($appId)])
                        ->withErrors(['email' => 'Erro ao enviar email. Tente novamente mais tarde.']);
                }

                return redirect()->route('login', ['app_id' => $this->encodeAppId($appId)])
                    ->with('status', 'Você já possui uma conta. Vinculamos este aplicativo ao seu usuário. Enviamos um link de acesso para seu email.');
            }

            $loginToken = LoginToken::generate($email, null, null);
            $loginUrl = route('login.verify', ['token' => $loginToken->token]);

            try {
                Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));
            } catch (Exception $e) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Erro ao enviar email. Tente novamente mais tarde.']);
            }

            return redirect()->route('login')
                ->with('status', 'Você já possui uma conta. Enviamos um link de acesso para seu email.');
        }

        // Cria o usuário no account primeiro — o account é a fonte de verdade do ID
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make(uniqid()),
        ]);

        // Sincroniza com o app remoto passando o ID gerado pelo account
        if ($appId) {
            $app = App::find($appId);
            [, $externalName] = $ssoAppUserService->syncRemoteUserForApp(
                $app,
                $validated['name'],
                $validated['email'],
                (int) $user->id
            );
            // Atualiza o nome se o app remoto retornou um nome diferente (ex.: usuário legado)
            if ($externalName !== $validated['name']) {
                $user->name = $externalName;
                $user->save();
            }
        }

        $email = $user->email;

        if ($appId) {
            $app = App::find($appId);
            if ($app) {
                $user->apps()->syncWithoutDetaching([$app->id]);
                Log::info('Account register: linked user to app', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'app_id' => $app->id,
                ]);
            }

            $redirectUri = null;
            if ($app && $app->redirect_uri) {
                $redirectUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
            }

            $loginToken = LoginToken::generate($email, $appId, $redirectUri);

            $loginUrl = route('login.verify', ['token' => $loginToken->token]);
            $loginUrl .= (strpos($loginUrl, '?') !== false ? '&' : '?') . 'app_id=' . urlencode($this->encodeAppId($appId));

            try {
                Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));
            } catch (Exception $e) {
                return redirect()->route('login', ['app_id' => $this->encodeAppId($appId)])
                    ->withErrors(['email' => 'Erro ao enviar email. Tente novamente mais tarde.']);
            }

            return redirect()->route('login', ['app_id' => $this->encodeAppId($appId)])
                ->with('status', 'Cadastro realizado com sucesso! Enviamos um link de acesso para seu email.');
        }

        $loginToken = LoginToken::generate($email, null, null);
        $loginUrl = route('login.verify', ['token' => $loginToken->token]);

        try {
            Mail::mailer('smtp')->to($email)->send(new LoginLinkMail($loginUrl));
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Erro ao enviar email. Tente novamente mais tarde.']);
        }

        return redirect()->route('login')
            ->with('status', 'Cadastro realizado com sucesso! Enviamos um link de acesso para seu email.');
    }

    public function redirectToGoogle(Request $request)
    {
        $appId = $this->decodeAppId($request->get('app_id'));
        if ($appId) {
            $request->session()->put('oauth_app_id', $appId);
        }
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::firstOrNew(['email' => $googleUser->getEmail()]);

            if (!$user->exists) {
                $user->name = $googleUser->getName();
                $user->password = Hash::make(uniqid());
                $user->save();
            } else {
                if (!$user->name || $user->name !== $googleUser->getName()) {
                    $user->name = $googleUser->getName();
                    $user->save();
                }
            }

            Auth::login($user);
            $request->session()->regenerate();
            $newSessionId = $request->session()->getId();
            Session::where('user_id', $user->id)
                ->whereRaw('id != ?', [$newSessionId])
                ->delete();

            $request->session()->save();
            $sessionId = $request->session()->getId();
            $updated = Session::where('id', $sessionId)
                ->update(['user_id' => $user->id]);

            if ($updated === 0) {
                $request->session()->save();
                Session::where('id', $sessionId)
                    ->update(['user_id' => $user->id]);
            }

            $ssoToken = $user->createToken('SSO Token')->accessToken;

            $appIdFromSession = $request->session()->pull('oauth_app_id');
            $appIdFromRequest = $request->get('app_id');
            $appId = $appIdFromSession ?? ($appIdFromRequest ? $this->decodeAppId($appIdFromRequest) : null);
            if ($appId) {
                $linked = $user->apps()->where('apps.id', $appId)->exists();
                if ($linked) {
                    $app = App::find($appId);
                    if ($app && $app->redirect_uri) {
                        $cleanUri = preg_replace('/[?#].*$/', '', $app->redirect_uri);
                        if (!parse_url($cleanUri, PHP_URL_PATH)) {
                            $cleanUri = rtrim($cleanUri, '/') . '/';
                        }
                        $finalUri = $cleanUri . '?token=' . urlencode($ssoToken);
                        return redirect()->away($finalUri)
                            ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
                    }
                }
            }

            $appUrls = $user->apps()
                ->whereNotNull('redirect_uri')
                ->pluck('redirect_uri')
                ->map(fn($uri) => rtrim(preg_replace('/[?#].*$/', '', $uri), '/') . '/?token=' . urlencode($ssoToken))
                ->values()
                ->all();

            return response()
                ->view('auth.redirect', [
                    'token' => $ssoToken,
                    'appUrls' => $appUrls,
                ])
                ->cookie('sso_token', encrypt($ssoToken), 60 * 24 * 15, '/', null, false, false);
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Erro ao fazer login com Google. Tente novamente.']);
        }
    }

    private function logoutFromOtherSystems($user)
    {
        // Apenas apps vinculados ao usuário e que tenham redirect_uri
        $apps = $user->apps()->whereNotNull('redirect_uri')->get();
        if ($apps->isEmpty()) {
            return;
        }

        try {
            $token = $user->createToken('Logout Token')->accessToken;

            foreach ($apps as $app) {
                $config = is_array($app->config)
                    ? $app->config
                    : (array) json_decode($app->config ?? '{}', true);

                // Preferência: api_url explícita no config; senão deriva do redirect_uri
                $apiBase = !empty($config['api_url'])
                    ? rtrim($config['api_url'], '/')
                    : null;

                if (!$apiBase && $app->redirect_uri) {
                    $parsed = parse_url($app->redirect_uri);
                    $host = $parsed['host'] ?? '';

                    // Em ambiente local .test, mapeia para o host Docker interno
                    if (str_ends_with($host, '.youfocus.test')) {
                        // Fotovibe usa porta 8082, youfocus usa porta 80
                        if (str_starts_with($host, 'fotovibe.')) {
                            $apiBase = 'http://host.docker.internal:8082';
                        } else {
                            $apiBase = 'http://youfocus-app:8000';
                        }
                    } elseif (!empty($parsed['scheme']) && !empty($host)) {
                        $apiBase = $parsed['scheme'] . '://' . $host;
                        if (!empty($parsed['port'])) {
                            $apiBase .= ':' . $parsed['port'];
                        }
                    }
                }

                if (!$apiBase) {
                    continue;
                }

                try {
                    Http::timeout(3)->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])->post($apiBase . '/api/sso/logout', [
                        'email' => $user->email,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('SSO logout: falha ao notificar app', [
                        'app_id' => $app->id,
                        'api_base' => $apiBase,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $user->tokens()->where('name', 'Logout Token')->delete();
        } catch (\Exception $e) {
            Log::warning('SSO logout: erro geral', ['error' => $e->getMessage()]);
        }
    }
}
