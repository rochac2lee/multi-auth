<!DOCTYPE html>
<html>
<head>
    <title>Verificando autenticação...</title>
</head>
<body>
    <p>Verificando autenticação...</p>
    <script>
        const redirectUri = '{{ $redirectUri }}';

        // Verificar se está autenticado no master
        // Como estamos no mesmo domínio (master), os cookies de sessão devem ser enviados automaticamente
        fetch('/api/check-auth', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                // Se estiver autenticado, obter token
                return fetch('/api/generate-token', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
            } else {
                // Se não estiver autenticado, redirecionar para login
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
                return Promise.reject('Não autenticado');
            }
        })
        .then(response => response.json())
        .then(tokenData => {
            if (tokenData.token) {
                // Redirecionar para o client app com o token
                const cleanUri = redirectUri.replace(/[?#].*$/, '');
                const separator = cleanUri.includes('?') ? '&' : '?';
                window.location.href = cleanUri + separator + 'token=' + encodeURIComponent(tokenData.token);
            } else {
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
            }
        })
        .catch(() => {
            // Em caso de erro, redirecionar para login
            window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
        });
    </script>
</body>
</html>

