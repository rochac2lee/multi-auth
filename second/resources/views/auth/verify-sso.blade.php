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
        const accountUrl = '{{ config('app.account_url', env('ACCOUNT_URL', 'http://account.test:8001')) }}';

        // Como estamos em domínios diferentes, não podemos usar cookies compartilhados
        // Redirecionar diretamente para o account com redirect_uri
        window.location.href = accountUrl + '/login?redirect_uri=' + encodeURIComponent(redirectUri);
    </script>
</body>
</html>

