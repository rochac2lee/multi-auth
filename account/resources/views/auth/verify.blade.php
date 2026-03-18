<!DOCTYPE html>
<html>
<head>
    <title>Verificando autenticação...</title>
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

        fetch('/api/check-auth', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                return fetch('/api/generate-token', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
            }
            window.location.href = loginUrl();
            return Promise.reject('Não autenticado');
        })
        .then(response => response.json())
        .then(tokenData => {
            if (tokenData.token && redirectUri) {
                const separator = redirectUri.includes('?') ? '&' : '?';
                window.location.href = redirectUri + separator + 'token=' + encodeURIComponent(tokenData.token);
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

