<!DOCTYPE html>
<html>
<head>
    <title>Verificando autenticação...</title>
</head>
<body>
    <script>
        // Verificar se há token no localStorage
        const ssoToken = localStorage.getItem('sso_token');
        const redirectUri = '{{ $redirectUri }}';
        const accountUrl = '{{ config('app.account_url', env('ACCOUNT_URL', 'http://account.test:8001')) }}';

        if (ssoToken) {
            // Se houver token, enviar para o account validar
            const accountLoginUrl = accountUrl + '/login?redirect_uri=' + encodeURIComponent(redirectUri) + '&sso_token=' + encodeURIComponent(ssoToken);
            window.location.href = accountLoginUrl;
        } else {
            // Se não houver token, redirecionar para login no account
            const accountLoginUrl = accountUrl + '/login?redirect_uri=' + encodeURIComponent(redirectUri);
            window.location.href = accountLoginUrl;
        }
    </script>
    <p>Verificando autenticação...</p>
</body>
</html>

