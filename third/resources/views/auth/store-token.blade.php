<!DOCTYPE html>
<html>
<head>
    <title>Autenticando...</title>
</head>
<body>
    <script>
        // Armazenar token no localStorage
        localStorage.setItem('sso_token', '{{ $token }}');

        // Redirecionar para a URL limpa
        window.location.href = '{{ $redirectUrl }}';
    </script>
    <p>Autenticando...</p>
</body>
</html>

