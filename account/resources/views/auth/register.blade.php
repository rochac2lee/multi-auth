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
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
<div class="register-page">
    <header class="register-header">
        <img src="{{ asset('images/logo-youfocus.png') }}" alt="YouFocus">
        <h1>Crie sua conta em segundos</h1>
        <p>
            Já possui uma conta?
            <a href="{{ route('login') }}">Entrar</a>
        </p>
    </header>

    <section class="register-layout">
        <main class="register-card">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="field">
                    <label for="name" class="field-label">* Nome</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="input @error('name') input--error @enderror"
                    >
                    @error('name')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="email" class="field-label">* E-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="input @error('email') input--error @enderror"
                    >
                    @error('email')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">
                    Cadastrar agora
                </button>
            </form>

            <div class="register-login-link">
                Ao se cadastrar, você concorda com nossos termos de uso e políticas de privacidade.
            </div>
        </main>

        <aside class="solutions">
            <div class="solutions-title">
                Tenha acesso às nossas soluções para fotógrafos
            </div>
            <div class="solutions-grid">
                <div class="solution-card solution-card--selpics">
                    <div class="solution-title">SELPICS</div>
                    <div class="solution-subtitle">Seleção de Fotos Online</div>
                </div>
                <div class="solution-card solution-card--youbox">
                    <div class="solution-title">YouBOX</div>
                    <div class="solution-subtitle">Entrega de Fotos Online</div>
                </div>
            </div>
        </aside>
    </section>
</div>
</body>
</html>


