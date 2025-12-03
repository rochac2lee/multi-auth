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
        // Redirecionar para as outras aplicações usando iframes invisíveis
        const secondAppUrl = '{{ $secondAppUrl }}';
        const thirdAppUrl = '{{ $thirdAppUrl }}';

        // Criar iframes invisíveis para propagar o token
        const iframeSecond = document.createElement('iframe');
        iframeSecond.style.display = 'none';
        iframeSecond.style.width = '0';
        iframeSecond.style.height = '0';
        iframeSecond.style.border = 'none';
        iframeSecond.src = secondAppUrl;
        document.body.appendChild(iframeSecond);

        const iframeThird = document.createElement('iframe');
        iframeThird.style.display = 'none';
        iframeThird.style.width = '0';
        iframeThird.style.height = '0';
        iframeThird.style.border = 'none';
        iframeThird.src = thirdAppUrl;
        document.body.appendChild(iframeThird);

        // Redirecionar a janela atual com token na URL para garantir que o middleware leia
        const token = '{{ $token }}';
        setTimeout(() => {
            window.location.href = '/?token=' + encodeURIComponent(token);
        }, 2000);
    </script>
</body>
</html>

