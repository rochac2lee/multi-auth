<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Account App</title>
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
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background: #5568d3;
        }
        .error {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .checkbox-group {
            margin: 1rem 0;
        }
        .checkbox-group input {
            width: auto;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login - Account App</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            @if(request('redirect_uri') || old('redirect_uri'))
                <input type="hidden" name="redirect_uri" value="{{ old('redirect_uri', request('redirect_uri')) }}">
            @endif

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            @if(session('status'))
                <div style="background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 5px; margin-bottom: 1rem;">
                    {{ session('status') }}
                </div>
            @endif

            <button type="submit">Enviar Link de Login</button>
        </form>

        <div style="margin: 1.5rem 0; text-align: center; position: relative;">
            <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #ddd;"></div>
            <span style="background: white; padding: 0 1rem; position: relative; color: #666;">ou</span>
        </div>

        <a href="{{ route('auth.google', ['redirect_uri' => old('redirect_uri', request('redirect_uri'))]) }}"
           style="display: block; width: 100%; padding: 0.75rem; background: white; color: #333; border: 1px solid #ddd; border-radius: 5px; text-align: center; text-decoration: none; margin-top: 1rem; box-sizing: border-box;">
            <svg style="width: 18px; height: 18px; vertical-align: middle; margin-right: 8px;" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Entrar com Google
        </a>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="{{ route('register') }}" style="color: #667eea; text-decoration: none;">Não tem uma conta? Cadastre-se</a>
        </div>
    </div>
</body>
</html>

