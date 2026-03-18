<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YouFocus | Crie sua conta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://youbox.youfocus.com.br/css/matrix-nucleus-styles.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="register-page">
    <header class="register-header">
        <img src="{{ asset('images/logo-youfocus.png') }}" alt="YouFocus">
    </header>
    <div class="register-page__wrap">
        <h1 class="register-title">Crie sua conta em segundos</h1>
        <p class="register-subtitle">
            Já possui uma conta?
            <a href="{{ route('login', request('app_id') ? ['app_id' => request('app_id')] : []) }}">Entrar</a>
        </p>
        <section class="register-layout">
        <main class="register-card">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                @if(request('app_id') || old('app_id'))
                    <input type="hidden" name="app_id" value="{{ old('app_id', request('app_id')) }}">
                @endif

                <div class="form-field">
                    <label for="name" class="mx-label">* Nome</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        class="mx-input mx-input--large @error('name') mx-input--error @enderror"
                    >
                    @error('name')
                        <span class="mx-input-text">
                            <svg class="mx-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5 11.84 3-3 3 3 .84-.84-3-3 3-3-.84-.84-3 3-3-3-.84.84 3 3-3 3zM8 16a7.7 7.7 0 0 1-3.1-.63A8.06 8.06 0 0 1 .63 11.1a8 8 0 0 1 0-6.22 8 8 0 0 1 1.72-2.54A8.2 8.2 0 0 1 4.9.63 7.991 7.991 0 0 1 15.37 11.1a8.2 8.2 0 0 1-1.71 2.55 8 8 0 0 1-2.54 1.72A7.8 7.8 0 0 1 8 16" fill="#FF3942"/></svg>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-field">
                    <label for="email" class="mx-label">* E-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="mx-input mx-input--large @error('email') mx-input--error @enderror"
                    >
                    @error('email')
                        <span class="mx-input-text">
                            <svg class="mx-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m5 11.84 3-3 3 3 .84-.84-3-3 3-3-.84-.84-3 3-3-3-.84.84 3 3-3 3zM8 16a7.7 7.7 0 0 1-3.1-.63A8.06 8.06 0 0 1 .63 11.1a8 8 0 0 1 0-6.22 8 8 0 0 1 1.72-2.54A8.2 8.2 0 0 1 4.9.63 7.991 7.991 0 0 1 15.37 11.1a8.2 8.2 0 0 1-1.71 2.55 8 8 0 0 1-2.54 1.72A7.8 7.8 0 0 1 8 16" fill="#FF3942"/></svg>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-field">
                    <button type="submit" class="mx-button mx-button--green mx-button--large">
                        Cadastrar agora
                    </button>
                </div>
            </form>

            <div class="register-terms">
                Ao se cadastrar, você concorda com os nossos <a href="#">Termos de Uso</a> e <a href="#">Políticas de Privacidade</a>.
            </div>
        </main>

        <aside class="solutions">
            <div class="solutions-title">
                Tenha acesso às nossas soluções para fotógrafos
            </div>
            <div class="solutions-grid">
                <div class="solution-card solution-card--selpics">
                    <div class="solution-title">
                        <img src="{{ asset('images/selpics.svg') }}" alt="SELPICS">
                    </div>
                    <div class="solution-subtitle">Seleção de Fotos Online</div>
                </div>
                <div class="solution-card solution-card--youbox">
                    <div class="solution-title">
                        <img src="{{ asset('images/youbox.svg') }}" alt="YouBOX">
                    </div>
                    <div class="solution-subtitle">Entrega de Fotos Online</div>
                </div>
            </div>
        </aside>
    </section>
</div>
</body>
</html>


