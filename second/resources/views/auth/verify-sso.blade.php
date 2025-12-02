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
            background: #f5f5f5;
        }
        .loading {
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading">
        <div class="spinner"></div>
        <p>Verificando autenticação...</p>
    </div>
    <script>
        const redirectUri = '{{ $redirectUri }}';

        // Verificar se está logado no master
        fetch('http://localhost:8001/api/check-auth', {
            method: 'GET',
            credentials: 'include', // Incluir cookies
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Não autenticado');
            }
            return response.json();
        })
        .then(data => {
            if (data.authenticated) {
                // Está logado, obter token
                return fetch('http://localhost:8001/api/generate-token', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            } else {
                // Não está logado, redirecionar para login
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
                return Promise.reject('Não autenticado');
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao obter token');
            }
            return response.json();
        })
        .then(tokenData => {
            if (tokenData.token) {
                // Redirecionar com token para criar sessão local
                const separator = redirectUri.includes('?') ? '&' : '?';
                window.location.href = redirectUri + separator + 'token=' + tokenData.token;
            } else {
                window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
            }
        })
        .catch(() => {
            // Erro ou não autenticado, redirecionar para login
            window.location.href = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
        });
    </script>
</body>
</html>

