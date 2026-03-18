<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>YouFocus</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://youbox.youfocus.com.br/css/matrix-nucleus-styles.css" rel="stylesheet" type="text/css">

    {{-- Base do account (já existe no projeto) --}}
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @vite(['resources/css/app.css'])

    @yield('styles')
</head>
<body>

@yield('body')

<script>
    // Evita quebra do JS do "minha-conta" caso não exista backend igual ao youfocus.
    // Mantém a página renderizando, mas sem tentar fazer integrações externas.
    window.fotografo = window.fotografo || {};
    window._ = window._ || undefined;
</script>

{{-- Dependências mínimas para o bundle legado do youfocus (Vue 2 + lodash). --}}
<script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

@yield('scripts')
</body>
</html>

