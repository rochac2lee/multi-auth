<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link de Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background: #5568d3;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Olá!</h1>
        <p>Você solicitou um link de login. Clique no botão abaixo para fazer login:</p>
        <a href="{{ $loginUrl }}" class="button">Fazer Login</a>
        <p>Ou copie e cole este link no seu navegador:</p>
        <p style="word-break: break-all; color: #667eea;">{{ $loginUrl }}</p>
        <p><strong>Este link expira em 15 minutos.</strong></p>
        <p>Se você não solicitou este link, ignore este email.</p>
        <div class="footer">
            <p>Esta é uma mensagem automática, por favor não responda.</p>
        </div>
    </div>
</body>
</html>


