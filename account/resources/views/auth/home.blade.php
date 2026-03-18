@extends('account.layouts.main')

@section('styles')
<style>
/* ── Layout geral ─────────────────────────────────────────────── */
.mc-page {
    background: #F9FAFB;
    min-height: 100vh;
    font-family: "Open Sans", sans-serif;
    color: #363646;
}

/* ── Topbar branca ────────────────────────────────────────────── */
.mc-topbar {
    background: #fff;
    height: 88px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #EDEFF2;
}
.mc-topbar img {
    height: 36px;
    width: auto;
}

/* ── Wrapper hero + avatar sobrepostos ────────────────────────── */
.mc-hero-wrap {
    position: relative;
}

/* ── Hero escuro ──────────────────────────────────────────────── */
.mc-hero {
    background: #363646;
    border-bottom-right-radius: 64px;
    position: relative;
    overflow: hidden;
    /* espaço extra embaixo para o avatar sobrepor */
    padding: 32px 0 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mc-hero__shapes {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.mc-hero__shape-circle-sm {
    position: absolute;
    left: 24px;
    bottom: 24px;
    width: 32px;
    height: 32px;
    background: #76C3F2;
    border-radius: 50%;
}
.mc-hero__shape-half {
    position: absolute;
    right: 220px;
    top: 0;
    opacity: .9;
}
.mc-hero__shape-plus {
    position: absolute;
    right: 24px;
    top: 0;
}
.mc-hero__name {
    font-size: 30px;
    font-weight: 700;
    color: #fff;
    text-align: center;
    margin-bottom: 0;
    position: relative;
    z-index: 1;
}

/* Avatar flutuante entre hero e conteúdo */
.mc-hero__avatar-wrap {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translate(-50%, 50%);
    z-index: 10;
}
.mc-hero__avatar-placeholder {
    width: 144px;
    height: 144px;
    border-radius: 50%;
    background: #EDEFF2;
    border: 4px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Container central ────────────────────────────────────────── */
.mc-container {
    max-width: 1224px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ── Seção de conteúdo ────────────────────────────────────────── */
.mc-content {
    /* metade do avatar (72px) + espaço extra */
    padding-top: 96px;
    padding-bottom: 48px;
}

/* ── Títulos de seção ─────────────────────────────────────────── */
.mc-section-title {
    font-size: 18px;
    font-weight: 700;
    color: #363646;
    margin: 0 0 16px;
}

/* ── Cards de dados ───────────────────────────────────────────── */
.mc-cards-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}
.mc-card {
    background: #fff;
    border: 1px solid #E0E5EA;
    border-radius: 4px;
    padding: 24px;
    position: relative;
}
.mc-card__edit-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    background: #EDEFF2;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #363646;
}
.mc-card__edit-btn svg {
    width: 16px;
    height: 16px;
}
.mc-card__title {
    font-size: 18px;
    font-weight: 700;
    color: #363646;
    margin: 0 0 20px;
}
.mc-field {
    margin-bottom: 16px;
}
.mc-field:last-child {
    margin-bottom: 0;
}
.mc-field__label {
    font-size: 14px;
    font-weight: 600;
    color: #8496AA;
    margin-bottom: 2px;
}
.mc-field__value {
    font-size: 16px;
    color: #363646;
}
.mc-field__country {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Assinaturas ──────────────────────────────────────────────── */
.mc-subscriptions {
    margin-bottom: 32px;
}
.mc-subscription-item {
    background: #fff;
    border: 1px solid #E0E5EA;
    border-radius: 4px;
    height: 96px;
    display: flex;
    align-items: center;
    padding: 0 24px;
    gap: 24px;
    margin-bottom: 8px;
    position: relative;
}
.mc-subscription-item--highlighted {
    border-width: 2px;
}
.mc-subscription-item__logo {
    flex-shrink: 0;
    width: 132px;
}
.mc-subscription-item__logo img,
.mc-subscription-item__logo svg {
    max-width: 100%;
    height: 32px;
}
.mc-badge {
    display: inline-flex;
    align-items: center;
    height: 24px;
    padding: 0 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    flex-shrink: 0;
}
.mc-badge--active    { background: #25D060; }
.mc-badge--suspended { background: #FD8D18; }
.mc-badge--awaiting  { background: #FF888D; }
.mc-badge--cancelled { background: #8496AA; }

.mc-subscription-item__info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 40px;
}
.mc-stat {
    font-size: 12px;
    font-weight: 600;
    color: #363646;
    line-height: 1.5;
}
.mc-stat strong {
    display: block;
    font-weight: 700;
}
.mc-stat-with-icon {
    display: flex;
    align-items: center;
    gap: 8px;
}
.mc-stat-with-icon svg {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
}
.mc-subscription-item__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.mc-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    padding: 0 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    font-family: "Open Sans", sans-serif;
    border: none;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}
.mc-btn--green {
    background: #25D060;
    border: 1px solid #21BB56;
    color: #fff;
}
.mc-btn--more {
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 50%;
    background: #8496AA;
    color: #fff;
    font-size: 18px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.mc-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0,0,0,.16);
    min-width: 185px;
    z-index: 10;
    overflow: hidden;
    display: none;
}
.mc-dropdown.open { display: block; }
.mc-dropdown a {
    display: block;
    padding: 10px 16px;
    font-size: 14px;
    color: #363646;
    text-decoration: none;
    background: #fff;
}
.mc-dropdown a:first-child { background: #EDEFF2; }
.mc-dropdown a:hover { background: #F9FAFB; }

/* ── Cartão de crédito ────────────────────────────────────────── */
.mc-credit-cards {
    margin-bottom: 32px;
}
.mc-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 8px;
}
.mc-alert--warning {
    background: #FFFAE7;
    border: 1px solid #FED1A2;
    color: #363646;
}
.mc-credit-card-item {
    background: #fff;
    border: 1px solid #E0E5EA;
    border-radius: 4px;
    height: 96px;
    display: flex;
    align-items: center;
    padding: 0 24px;
    gap: 24px;
}
.mc-credit-card-item__brand {
    width: 120px;
    height: 94px;
    background: #F9FAFB;
    border-radius: 4px 0 0 4px;
    margin-left: -24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mc-credit-card-item__info {
    flex: 1;
}
.mc-credit-card-item__number {
    font-size: 16px;
    font-weight: 600;
    color: #363646;
}
.mc-credit-card-item__expiry {
    font-size: 12px;
    color: #363646;
    margin-top: 4px;
}

/* ── Rodapé de ações ──────────────────────────────────────────── */
.mc-footer-actions {
    background: #FFEBEB;
    padding: 32px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 32px;
    border-radius: 4px;
}
.mc-btn--dark {
    height: 44px;
    padding: 0 20px;
    font-size: 16px;
    background: #494958;
    border: 1px solid #363646;
    color: #fff;
    border-radius: 4px;
}
.mc-btn--red {
    height: 44px;
    padding: 0 20px;
    font-size: 16px;
    background: #FF3942;
    border: 1px solid #E5333B;
    color: #fff;
    border-radius: 4px;
}

/* ── Footer "Feito com ♥" ─────────────────────────────────────── */
.mc-page-footer {
    text-align: center;
    padding: 24px;
    font-size: 14px;
    font-weight: 600;
    color: #A3B0BF;
}

/* ── Separador ────────────────────────────────────────────────── */
.mc-divider {
    height: 1px;
    background: #EDEFF2;
    margin: 32px 0;
}
</style>
@endsection

@section('body')
<div class="mc-page">

    {{-- Topbar --}}
    <div class="mc-topbar">
        <img src="{{ asset('images/logo-youfocus.png') }}" alt="YouFocus">
    </div>

    {{-- Hero + avatar sobrepostos --}}
    <div class="mc-hero-wrap">
        <div class="mc-hero">
            <div class="mc-hero__shapes">
                <div class="mc-hero__shape-circle-sm"></div>
                <svg class="mc-hero__shape-half" width="200" height="102" viewBox="0 0 200 102" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <mask id="mask0_1_244" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="200" height="102">
                        <path d="M0 101.058L-4.41739e-06 7.62939e-06L200 -1.11288e-06L200 101.058L0 101.058Z" fill="#D9D9D9"/>
                    </mask>
                    <g mask="url(#mask0_1_244)">
                        <path d="M100 59.5238C67.127 59.5238 40.4762 32.873 40.4762 1.36977e-05C40.4762 -32.873 67.127 -59.5238 100 -59.5238C132.873 -59.5238 159.524 -32.873 159.524 8.49393e-06C159.484 32.8572 132.857 59.4841 100 59.5238ZM4.7619 1.52588e-05C4.7619 52.5952 47.4048 95.2381 100 95.2381C152.595 95.2381 195.238 52.5952 195.238 6.93281e-06C195.238 -52.5952 152.595 -95.2381 100 -95.2381C47.4048 -95.2381 4.7619 -52.5952 4.7619 1.52588e-05Z" fill="#76C3F2"/>
                    </g>
                </svg>
                <svg class="mc-hero__shape-plus" width="136" height="112" viewBox="0 0 136 112" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M114 10H122V2C122 0.9 122.9 0 124 0C125.1 0 126 0.9 126 2V10H134C135.1 10 136 10.9 136 12C136 13.1 135.1 14 134 14H126V22C126 23.1 125.1 24 124 24C122.9 24 122 23.1 122 22V14H114C112.9 14 112 13.1 112 12C112 10.9 112.9 10 114 10Z" fill="#76C3F2"/>
                    <circle cx="44" cy="44" r="44" transform="matrix(-1 0 0 1 88 24)" fill="#76C3F2"/>
                </svg>
            </div>

            <div class="mc-hero__name">{{ $user->name }}</div>
        </div>

        <div class="mc-hero__avatar-wrap">
            <div class="mc-hero__avatar-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 96 96" fill="none">
                    <path d="M47.9987 47.6201C43.8948 47.7409 40.4686 44.4444 40.3502 40.253C40.3452 40.1064 40.3452 39.9598 40.3502 39.8107C40.2318 35.6193 43.464 32.1247 47.5704 32.0039C47.714 31.9987 47.8576 31.9987 47.9987 32.0039C52.1051 31.883 55.5313 35.1821 55.6497 39.3735C55.6547 39.5201 55.6547 39.6641 55.6497 39.8107C55.7681 44.0021 52.5384 47.4992 48.432 47.6201C48.2884 47.6252 48.1448 47.6252 47.9987 47.6201ZM32.0014 64V58.8983C31.9762 57.7309 32.2986 56.584 32.9259 55.6069C33.5255 54.6863 34.3644 53.9509 35.3495 53.488C37.4001 52.5109 39.534 51.7343 41.7257 51.1686C43.7789 50.6441 45.885 50.3766 47.9987 50.3766C50.1073 50.3766 52.2058 50.6415 54.249 51.1661C56.4457 51.7421 58.5871 52.5186 60.6479 53.488C61.6329 53.9509 62.4718 54.6838 63.0714 55.6043C63.7012 56.5815 64.0237 57.7309 63.9985 58.8983V64H32.0014Z" fill="#8496AA"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Conteúdo --}}
    <div class="mc-content">
        <div class="mc-container">

            {{-- Cards: Dados de cadastro + Informações adicionais --}}
            <div class="mc-cards-row">
                <div>
                    <h3 class="mc-section-title">Dados de cadastro</h3>
                    <div class="mc-card">
                        <a href="#" class="mc-card__edit-btn" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <div class="mc-field">
                            <div class="mc-field__label">Nome completo:</div>
                            <div class="mc-field__value">{{ $user->name ?: '—' }}</div>
                        </div>
                        <div class="mc-field">
                            <div class="mc-field__label">E-mail de cadastro:</div>
                            <div class="mc-field__value">{{ $user->email ?: '—' }}</div>
                        </div>
                        <div class="mc-field">
                            <div class="mc-field__label">Senha:</div>
                            <div class="mc-field__value">***********</div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="mc-section-title">Informações adicionais</h3>
                    <div class="mc-card">
                        <a href="#" class="mc-card__edit-btn" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <div class="mc-field">
                            <div class="mc-field__label">ID da conta:</div>
                            <div class="mc-field__value">{{ $user->id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Minhas assinaturas --}}
            @if($apps->count() > 0)
            <div class="mc-subscriptions">
                <h3 class="mc-section-title">Minhas assinaturas</h3>

                @foreach($apps as $app)
                <div class="mc-subscription-item">
                    <div class="mc-subscription-item__logo">
                        <span style="font-size:14px; font-weight:700; color:#363646;">{{ $app->name }}</span>
                    </div>

                    <span class="mc-badge mc-badge--active">Ativa</span>

                    <div class="mc-subscription-item__info">
                        <div class="mc-stat">
                            <span>Aplicativo vinculado</span>
                        </div>
                    </div>

                    <div class="mc-subscription-item__actions">
                        @if($app->redirect_uri)
                        <a href="{{ rtrim(preg_replace('/[?#].*$/', '', $app->redirect_uri), '/') . '/' }}" class="mc-btn mc-btn--green">
                            Acessar produto
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mc-divider"></div>

            {{-- Ações --}}
            <div class="mc-footer-actions">
                <a href="#" class="mc-btn mc-btn--dark">Alterar senha</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="mc-btn mc-btn--red">Sair da conta</button>
                </form>
                <a href="{{ route('account.terminate.index') }}" class="mc-btn mc-btn--red">Excluir conta na YouFocus</a>
            </div>

        </div>
    </div>

    {{-- Footer --}}
    <div class="mc-page-footer">
        Feito com ♥ e ☁ para os Fotógrafos
    </div>

</div>
@endsection
