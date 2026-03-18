<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YouFocus | Acessar conta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://youbox.youfocus.com.br/css/matrix-nucleus-styles.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="login-page">
    <header class="login-header">
        <div class="logo-container">
            <img src="{{ asset('images/logo-youfocus.png') }}" alt="YouFocus">
        </div>
        <div class="login-title">
            <h1>Acessar conta</h1>
            <p>
                Não possui uma conta?
                <a href="{{ route('register', request('app_id') ? ['app_id' => request('app_id')] : []) }}">Cadastrar</a>
            </p>
        </div>
    </header>

    @if($errors->has('email'))
        <div class="failed-auth mx-warning mx-warning--error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M6.25 14.8L10 11.05L13.75 14.8L14.8 13.75L11.05 10L14.8 6.25L13.75 5.2L10 8.95L6.25 5.2L5.2 6.25L8.95 10L5.2 13.75L6.25 14.8ZM10 20C8.63333 20 7.34167 19.7375 6.125 19.2125C4.90833 18.6875 3.84583 17.9708 2.9375 17.0625C2.02917 16.1542 1.3125 15.0917 0.7875 13.875C0.2625 12.6583 0 11.3667 0 10C0 8.61667 0.2625 7.31667 0.7875 6.1C1.3125 4.88333 2.02917 3.825 2.9375 2.925C3.84583 2.025 4.90833 1.3125 6.125 0.7875C7.34167 0.2625 8.63333 0 10 0C11.3833 0 12.6833 0.2625 13.9 0.7875C15.1167 1.3125 16.175 2.025 17.075 2.925C17.975 3.825 18.6875 4.88333 19.2125 6.1C19.7375 7.31667 20 8.61667 20 10C20 11.3667 19.7375 12.6583 19.2125 13.875C18.6875 15.0917 17.975 16.1542 17.075 17.0625C16.175 17.9708 15.1167 18.6875 13.9 19.2125C12.6833 19.7375 11.3833 20 10 20Z" fill="#FF3942"/>
            </svg>
            <div class="mx-warning__text">{{ $errors->first('email') }}</div>
        </div>
    @endif

    @if(session('status'))
        <div class="status-box mx-warning mx-warning--success">
            <div class="mx-warning__text">{{ session('status') }}</div>
        </div>
    @endif

    <main class="login-main">
        <div class="form-container">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if(request('app_id') || old('app_id'))
                    <input type="hidden" name="app_id" value="{{ old('app_id', request('app_id')) }}">
                @endif

                <div class="form-field">
                    <label for="email" class="mx-label">* E-mail de cadastro</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="E-mail"
                        class="mx-input mx-input--large @error('email') mx-input--error @enderror"
                    >
                    @error('email')
                        <span class="mx-input-text">
                            <svg class="mx-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5 11.84 3-3 3 3 .84-.84-3-3 3-3-.84-.84-3 3-3-3-.84.84 3 3-3 3zM8 16a7.7 7.7 0 0 1-3.1-.63A8.06 8.06 0 0 1 .63 11.1a8 8 0 0 1 0-6.22 8 8 0 0 1 1.72-2.54A8.2 8.2 0 0 1 4.9.63 7.991 7.991 0 0 1 15.37 11.1a8.2 8.2 0 0 1-1.71 2.55 8 8 0 0 1-2.54 1.72A7.8 7.8 0 0 1 8 16" fill="#FF3942"/></svg>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <button type="submit" class="send-button mx-button mx-button--green mx-button--large">
                    Continuar acesso
                </button>

                <div class="remember-container">
                    <input class="mx-checkbox" id="remember" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : 'checked' }}>
                    <label for="remember" class="text-[#363646] text-sm">Continuar conectado</label>
                </div>
            </form>
        <div class="divider">
            <span>ou</span>
        </div>

        <a
            href="{{ route('auth.google', request('app_id') ? ['app_id' => request('app_id')] : []) }}"
            class="btn-google"
        >
            <svg viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Entrar com Google
        </a>
        </div>

    </main>
</body>
</html>

