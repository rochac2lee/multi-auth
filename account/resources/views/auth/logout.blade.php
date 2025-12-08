<!DOCTYPE html>
<html>
<head>
    <title>Saindo...</title>
</head>
<body>
    <script>
        // Limpar localStorage ANTES de redirecionar
        localStorage.removeItem('sso_token');
        sessionStorage.clear();

        // Limpar todos os cookies relacionados
        document.cookie.split(";").forEach(function(c) {
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });

        // Redirecionar para a página inicial
        window.location.href = '{{ url("/") }}';
    </script>
    <p>Saindo...</p>
</body>
</html>

