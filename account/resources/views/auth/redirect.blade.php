<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionando...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p>Redirecionando para as outras aplicações...</p>
    </div>

    <script>
        // Redirecionar para as outras aplicações
        const secondAppUrl = '{{ $secondAppUrl }}';
        const thirdAppUrl = '{{ $thirdAppUrl }}';

        // Abrir em nova aba/janela ou usar iframe
        window.open(secondAppUrl, '_blank');
        window.open(thirdAppUrl, '_blank');

        // Redirecionar a janela atual após um pequeno delay
        setTimeout(() => {
            window.location.href = '/';
        }, 1000);
    </script>
</body>
</html>

