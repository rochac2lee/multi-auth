<?php

namespace App\Services;

use App\Models\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoAppUserService
{
    /**
     * Sincroniza/cria o usuário no app remoto e retorna [id, name].
     *
     * O $accountId é o ID já definido pelo account (fonte de verdade).
     * - Se o usuário já existe no app remoto → retorna o ID existente (pode divergir em dados legados).
     * - Se não existe → cria no app remoto passando o $accountId para garantir consistência.
     * - Se o app não tiver endpoint SSO configurado → retorna [$accountId, $name] sem alterar nada.
     */
    public function syncRemoteUserForApp(?App $app, string $name, string $email, ?int $accountId = null): array
    {
        $externalId = $accountId;
        $externalName = $name;

        if (!$app) {
            return [$externalId, $externalName];
        }

        // config pode ser array ou JSON (jsonb) dependendo de como foi salvo
        $config = is_array($app->config) ? $app->config : (array) json_decode($app->config ?? '{}', true);
        $apiUrl = $config['api_url'] ?? null;

        // Se o app ainda não tiver api_url configurado, inferimos a partir do redirect_uri
        if (!$apiUrl && $app->redirect_uri) {
            $parsed = parse_url($app->redirect_uri);
            $host = $parsed['host'] ?? '';

            // Caso específico para o Selpics em desenvolvimento:
            // se o host for selpics.youfocus.test, usamos o serviço Docker youfocus-app.
            if ($host === 'selpics.youfocus.test') {
                $apiUrl = 'http://youfocus-app:8000';
            } elseif ($host === 'fotovibe.youfocus.test') {
                // fotovibe roda em stack Docker separado; acessa via host com porta publicada
                $apiUrl = 'http://host.docker.internal:8082';
            } elseif (str_ends_with($host, '.youfocus.test')) {
                $apiUrl = 'http://youfocus-app:8000';
            } elseif (!empty($parsed['scheme']) && !empty($host)) {
                $apiUrl = $parsed['scheme'] . '://' . $host;
                if (!empty($parsed['port'])) {
                    $apiUrl .= ':' . $parsed['port'];
                }
            }
        }

        if (!$apiUrl) {
            Log::info('SSO sync: skipping remote sync, no api_url or usable redirect_uri', [
                'app_id' => $app->id ?? null,
                'redirect_uri' => $app->redirect_uri,
            ]);
            return [$externalId, $externalName];
        }

        $apiBase = rtrim($apiUrl, '/');

        try {
            // 1) Verifica se usuário já existe no app remoto (idempotente)
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get($apiBase . '/api/sso/user-by-email', ['email' => $email]);

            if ($response->successful()) {
                $body = $response->json();
                if (!empty($body['exists'])) {
                    // Usuário já existe no app remoto: usa o nome remoto se disponível,
                    // mas o ID continua sendo o do account (fonte de verdade)
                    if (!empty($body['name'])) {
                        $externalName = $body['name'];
                    }
                    return [$externalId, $externalName];
                }
            }
        } catch (\Exception $e) {
            Log::warning('SSO sync: failed to check remote user by email', [
                'app_id' => $app->id ?? null,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            // Continua tentando criar abaixo
        }

        try {
            // 2) Não existe ainda: cria usuário remoto passando o id do account
            $payload = ['name' => $name, 'email' => $email];
            if ($accountId !== null) {
                $payload['id'] = $accountId;
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($apiBase . '/api/sso/users', $payload);

            if ($response->successful()) {
                $body = $response->json();
                if (!empty($body['name'])) {
                    $externalName = $body['name'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('SSO sync: failed to create remote user', [
                'app_id' => $app->id ?? null,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
        }

        return [$externalId, $externalName];
    }
}

