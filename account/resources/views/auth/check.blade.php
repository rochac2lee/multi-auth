<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificando autenticação...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>
    <p>Verificando autenticação...</p>
    <script>
        const appId = '{{ $appId ?? "" }}';
        const redirectUri = '{{ $redirectUri ?? "" }}';
        const appUrl = '{{ config('app.url') }}';

        function loginUrl() {
            if (appId) return appUrl + '/login?app_id=' + encodeURIComponent(appId);
            return appUrl + '/login';
        }

        fetch(appUrl + '/api/check-auth', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.ok ? response.json() : Promise.reject(new Error('Não autenticado')))
        .then(data => {
            if (data.authenticated) {
                return fetch(appUrl + '/api/generate-token', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }
            return Promise.reject(new Error('Não autenticado'));
        })
        .then(response => response.json())
        .then(tokenData => {
            if (tokenData.token && redirectUri) {
                window.location.href = redirectUri + (redirectUri.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(tokenData.token);
            } else {
                window.location.href = loginUrl();
            }
        })
        .catch(() => {
            window.location.href = loginUrl();
        });
    </script>
</body>
</html>

