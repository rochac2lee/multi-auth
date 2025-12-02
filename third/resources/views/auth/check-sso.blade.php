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

        if (ssoToken) {
            // Se houver token, enviar para o master validar
            const masterLoginUrl = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri) + '&sso_token=' + encodeURIComponent(ssoToken);
            window.location.href = masterLoginUrl;
        } else {
            // Se não houver token, redirecionar para login no master
            const masterLoginUrl = 'http://localhost:8001/login?redirect_uri=' + encodeURIComponent(redirectUri);
            window.location.href = masterLoginUrl;
        }
    </script>
    <p>Verificando autenticação...</p>
</body>
</html>

