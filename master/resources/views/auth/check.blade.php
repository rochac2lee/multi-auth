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
        const redirectUri = '{{ $redirectUri ?? "" }}';

        // Tentar verificar autenticação fazendo requisição com credentials
        fetch('http://localhost:8001/api/check-auth', {
            method: 'GET',
            credentials: 'include', // Importante: incluir cookies
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Não autenticado');
        })
        .then(data => {
            if (data.authenticated) {
                // Usuário está autenticado, obter token
                return fetch('http://localhost:8001/api/generate-token', {
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
            throw new Error('Não autenticado');
        })
        .then(response => response.json())
        .then(tokenData => {
            if (tokenData.token && redirectUri) {
                window.location.href = redirectUri + (redirectUri.includes('?') ? '&' : '?') + 'token=' + tokenData.token;
            } else if (redirectUri) {
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
            } else {
                window.location.href = 'http://localhost:8001/';
            }
        })
        .catch(() => {
            // Não autenticado ou erro, redirecionar para login
            if (redirectUri) {
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
            } else {
                window.location.href = 'http://localhost:8001/login';
            }
        });
    </script>
</body>
</html>

