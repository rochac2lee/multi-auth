@php
    $absolutePath = asset('images/emails/magic-link');
@endphp

<div
    style="
        font-size: 16px;
        background: #edeff2;
        font-family: 'Open Sans', Arial, Helvetica, sans-serif;
    "
>
    <center style="padding-top: 48px; padding-bottom: 40px">
        <div style="width: 584px; border-radius: 4px; background: #ffffff">
            <div
                style="
                    background-color: #194283;
                    height: 80px;
                    border-radius: 4px 4px 0px 0px;
                    border-bottom: 1px solid #edeff2;
                "
            >
                <a href="https://youfocus.com.br/">
                    <img
                        style="margin-top: 22px"
                        src="{{ $absolutePath }}/youfocus-inicial.png"
                        alt="YouFocus"
                    />
                </a>
            </div>

            <div
                style="
                    color: #363646;
                    padding: 24px 40px 23px 40px;
                    text-align: left;
                "
            >
                <p style="line-height: 27px; margin: 0">
                    <b>Olá!</b><br /><br />
                    Use o código abaixo para confirmar a alteração do seu e-mail.
                </p>

                <div
                    style="
                        margin-top: 24px;
                        width: 504px;
                        padding: 18px 24px;
                        border-radius: 8px;
                        background: #f9fafb;
                        border: 1px solid #EDEFF2;
                        text-align: center;
                    "
                >
                    <span style="font-size: 32px; font-weight: 800; color: #194283;">
                        {{ $code }}
                    </span>
                </div>

                <p style="margin: 0; padding-top: 16px; line-height: 27px;">
                    Por segurança, este código é válido por apenas 15 minutos.
                </p>

                <p style="margin: 0; padding-top: 22px; line-height: 27px;">
                    Um abraço, <br />
                    <b>Equipe YouFocus</b> <br /><br />
                    #FocoNoSucesso 🚀
                </p>
            </div>
        </div>
    </center>
</div>

