@php
    $absolutePath = asset('images/emails/magic-link');
@endphp
<div
        style="
            font-size: 16px;
            background: #edeff2;
            font-family: 'Open Sans', Arial, Helvetica, sans-serif;"
>
    <center style="padding-top: 48px; padding-bottom: 40px">
        <div style="width: 584px; border-radius: 4px; background: #ffffff">
            <!-- inicio -->
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
                    /></a>
            </div>
            <!-- corpo -->
            <div
                    style="
              color: #363646;
              padding: 24px 40px 23px 40px;
              text-align: left;
            "
            >
                <p style="line-height: 27px">
                    <b>Olá</b>. <br /><br />
                    Para entrar na sua conta <b>YouFocus</b>, é simples:
                    <b
                    >basta clicar no <br />
                        botão abaixo e pronto</b
                    >. 😊
                </p>
            </div>

            <!-- Botão -->
            <div style="width: 584px; text-align: left; padding-left: 40px">
                <a
                        href="{{ $loginUrl }}"
                        target="_blank"
                        style="
                width: 228px;
                height: 46px;
                text-decoration: none;
                display: inline-block;
                line-height: 48px;
                text-align: center;
                font-size: 16px;
                font-weight: 700;
                border: 1px solid #21bb56;
                background: #25d060;
                color: #fff;
                border-radius: 4px;
              "
                        class="mx-button mx-button--green"
                >
                    Acessar painel YouFocus</a
                >
            </div>
            <div style="padding: 8px 40px 8px 40px; text-align: left">
                <p
                        style="
                margin: 0;
                color: #363646;
                font-size: 16px;
                font-weight: 400;
                line-height: 27px;
              "
                >
                    Por segurança, este link é válido por apenas 15 minutos.
                </p>
                <p
                        style="
                margin: 0;
                color: #363646;
                font-size: 16px;
                font-weight: 400;
                line-height: 27px;
                padding-top: 40px;
              "
                >
                    Se preferir, você também pode
                    <b>copiar e colar</b> este link <br />
                    diretamente no seu navegador:
                </p>
            </div>
            <div
                    style="
              width: 504px;
              height: 92px;
              border-radius: 8px;
              background: #f9fafb;
              text-align: left;
            "
            >
                <div style="padding-top: 24px; padding-left: 24px; padding-right: 24px; word-break: break-all">
                    <a href="{{ $loginUrl }}" style="color: #1c9cea">{{ $loginUrl }}</a>
                </div>
            </div>

            <div style="padding: 41px 40px 39px 40px; text-align: left">
                <p style="color: #363646; margin: 0; line-height: 27px">
                    Um abraço, <br /><b>Equipe YouFocus</b> <br /><br />#FocoNoSucesso
                    🚀
                </p>
            </div>
            <div
                    style="
              text-align: center;
              width: 503px;
              height: 269px;
              border-radius: 4px;
              border: 1px solid #dcc2e7;
              background: #f6eff9;
            "
            >
                <div
                        style="
                padding-top: 39px;
                padding-left: 206px;
                padding-right: 206px;
              "
                >
                    <img
                            style="width: 92px; height: 40px"
                            src="{{ $absolutePath }}/Interrogacao.png"
                            alt=""
                    />
                </div>
                <div
                        style="
                padding: 0px 32px 40px 32px;
                color: #363646;
                text-align: center;
                font-size: 16px;
                line-height: 27px;
              "
                >
                    <p style="margin-top: 12px">
                        <b>Precisa de auxílio para usar o YouBOX?</b>
                        <br />Acesse nossa <b>Central de Ajuda</b> ou envie uma mensagem
                        para nossa equipe de atendimento no WhatsApp. <br /><br />
                        Nossos agentes são super atenciosos e eficientes. 😉
                    </p>
                </div>
            </div>

            <div
                    style="border-bottom: 1px solid #edeff2; padding-top: 40px"
            ></div>
            <div style="width: 584px">
                <div style="width: 140px; padding: 40px 222px 35px 222px">
                    <a
                            style="text-decoration: none"
                            href="https://blog.youfocus.com.br/"
                            target="_blank"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="36"
                                height="36"
                                viewBox="0 0 36 36"
                                fill="none"
                        >
                            <path
                                    d="M18.0001 36.0001C13.223 36.0131 8.64009 34.1151 5.27205 30.7291C1.88509 27.3601 -0.0139608 22.777 7.72789e-05 18.0001C-0.0129232 13.2231 1.88509 8.64008 5.27205 5.27204C8.64009 1.88508 13.223 -0.0129341 18.0001 6.63478e-05C22.7761 -0.0129341 27.3591 1.88508 30.7271 5.27204C34.1141 8.64008 36.012 13.2231 36.0001 18.0001C36.0131 22.7761 34.1151 27.3591 30.728 30.7281C27.3601 34.1151 22.7761 36.0131 18.0001 36.0001ZM14.319 25.5721C14.6121 25.6241 16.775 25.652 18.6601 25.652C20.314 25.652 21.4501 25.6321 21.6991 25.5991C22.7711 25.467 23.762 24.9611 24.496 24.1681C25.077 23.5801 25.4921 22.8481 25.6981 22.0471C25.8441 20.324 25.874 18.592 25.788 16.8651C25.7351 16.6061 25.582 16.3801 25.361 16.2351C25.0801 16.1761 24.7941 16.1451 24.5081 16.1451C23.8211 16.1141 23.7481 16.1021 23.5271 15.9741C23.1851 15.7731 23.09 15.5561 23.0861 14.9661C23.056 13.7811 22.5531 12.657 21.689 11.8461C21.0721 11.2001 20.301 10.7221 19.4481 10.4591C18.9091 10.3851 18.3671 10.3481 17.8231 10.3481C17.6431 10.3481 17.4631 10.3481 17.2831 10.3601C16.9021 10.3601 16.6011 10.3531 16.3381 10.3531C15.4061 10.2661 14.4661 10.3851 13.5851 10.7021C12.0991 11.2921 10.999 12.5781 10.6461 14.1381C10.5231 15.3171 10.4871 16.5041 10.5391 17.6881C10.5181 21.1921 10.5391 21.6711 10.7571 22.35C11.2861 23.9841 12.6461 25.212 14.3241 25.5721H14.319ZM16.8391 21.6321C16.2471 21.6621 15.655 21.6371 15.0681 21.5571C14.806 21.4261 14.6191 21.1821 14.5601 20.8951C14.5351 20.6111 14.6381 20.3301 14.84 20.1291C15.0831 19.9111 15.176 19.9041 18.1641 19.9011C21.2081 19.9011 21.2081 19.9011 21.4931 20.1661H21.4981C21.8331 20.4901 21.8431 21.0241 21.5201 21.3601C21.4641 21.4191 21.3991 21.4691 21.3291 21.509L20.8201 21.5911C20.4221 21.5911 19.8391 21.5981 19.223 21.6061C18.4501 21.621 17.5791 21.6321 16.8391 21.6321ZM16.4161 16.1351C15.9941 16.1351 15.5661 16.1191 15.1411 16.0861C14.689 15.9261 14.452 15.4291 14.6121 14.9771C14.6491 14.8721 14.7051 14.7761 14.7791 14.6931C15.0491 14.423 15.124 14.4131 16.7861 14.4131C18.28 14.4131 18.3381 14.4131 18.5571 14.5301C18.8541 14.6561 19.038 14.957 19.0141 15.2791C19.0261 15.5851 18.8631 15.871 18.5941 16.0171C18.4361 16.1171 18.34 16.1231 16.8761 16.1301C16.723 16.1331 16.5681 16.1351 16.4151 16.1351H16.4161Z"
                                    fill="#194283"
                            />
                        </svg>
                    </a>
                    <a
                            style="
                  padding-right: 10px;
                  text-decoration: none;
                  padding-left: 10px;
                "
                            href="https://www.instagram.com/youfocus.oficial/"
                            target="_blank"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="36"
                                height="36"
                                viewBox="0 0 36 36"
                                fill="none"
                        >
                            <path
                                    d="M18.0001 36.0001C13.223 36.0131 8.64009 34.1151 5.27205 30.7291C1.88509 27.3601 -0.0139608 22.777 7.72789e-05 18.0001C-0.0129232 13.2231 1.88509 8.64008 5.27205 5.27204C8.64009 1.88508 13.223 -0.0129341 18.0001 6.63478e-05C22.7761 -0.0129341 27.3591 1.88508 30.7271 5.27204C34.1141 8.64008 36.012 13.2231 36.0001 18.0001C36.0131 22.7761 34.1151 27.3591 30.728 30.7281C27.3601 34.1151 22.7761 36.0131 18.0001 36.0001ZM14.2831 9.90009C11.866 9.90809 9.9081 11.8671 9.90004 14.285V21.7141C9.90608 24.1331 11.8651 26.092 14.2831 26.1H21.7141C24.1331 26.0941 26.0941 24.1351 26.1001 21.7151V14.285C26.0931 11.8661 24.1331 9.90607 21.7141 9.90009H14.2831ZM18.0001 22.4301C15.5541 22.4271 13.5721 20.4451 13.569 18.0001C13.5661 16.8251 14.0331 15.6971 14.866 14.868C15.6951 14.0341 16.8221 13.5671 17.9981 13.569C20.444 13.5721 22.426 15.5541 22.4281 18.0001C22.4221 20.4431 20.4431 22.423 18.0001 22.4301ZM18.0001 15.0331C16.3971 15.035 15.098 16.3331 15.0961 17.936C15.098 19.5391 16.3971 20.837 18.0001 20.8391C19.6021 20.837 20.9011 19.5391 20.9031 17.936C20.9 16.3331 19.6021 15.0341 18.0001 15.0301V15.0331ZM22.5361 14.6151C21.814 14.6141 21.2291 14.0291 21.228 13.3071C21.2291 12.5851 21.814 12.0001 22.5361 11.9991C23.2591 12.0001 23.8441 12.5851 23.845 13.3071C23.8421 14.0291 23.2561 14.6141 22.5341 14.6151H22.5361Z"
                                    fill="#194283"
                            />
                        </svg>
                    </a>
                    <a
                            href="https://www.youtube.com/@youfocus.oficial/videos"
                            target="_blank"
                    >
                        <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="36"
                                height="36"
                                viewBox="0 0 36 36"
                                fill="none"
                        >
                            <path
                                    d="M18.0001 36.0001C13.223 36.0131 8.64009 34.1151 5.27205 30.7291C1.88509 27.3601 -0.0139608 22.777 7.72789e-05 18.0001C-0.0129232 13.2231 1.88509 8.64008 5.27205 5.27204C8.64009 1.88508 13.223 -0.0129341 18.0001 6.63478e-05C22.7761 -0.0129341 27.3591 1.88508 30.7271 5.27204C34.114 8.64008 36.0121 13.2231 36 18.0001C36.0131 22.7761 34.1151 27.3591 30.728 30.7281C27.3601 34.1151 22.7761 36.0131 18.0001 36.0001ZM9.39406 22.6801C9.58907 23.4631 10.1741 24.0911 10.9411 24.3411H10.9561C12.3411 24.7411 17.9491 24.7451 18.0061 24.7451C18.0631 24.7451 23.6531 24.7411 25.056 24.3411C25.8301 24.0961 26.4221 23.467 26.6201 22.6791V22.6621C26.874 21.1811 27.0011 19.6821 27.0001 18.1801V17.8201C27.0001 16.264 26.8671 14.7111 26.6021 13.178L26.6261 13.332C26.4351 12.5421 25.8471 11.907 25.0741 11.6551H25.0591C23.6711 11.2531 18.0651 11.2481 18.0091 11.2481H17.9851C17.4081 11.2481 12.3101 11.2601 10.9601 11.6511C10.1851 11.8981 9.59309 12.5291 9.39705 13.3181V13.3351C8.86507 16.475 8.87209 19.6831 9.41707 22.8201L9.39607 22.6791L9.39406 22.6801ZM16.2051 20.8981V15.1091L20.905 18.009L16.207 20.9001L16.2051 20.8981Z"
                                    fill="#194283"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div
                style="
            font-size: 14px;
            padding-top: 16px;
            text-align: center;
            color: #8496aa;
            line-height: 24px;
          "
        >
            <div>Se não deseja receber mais mensagens como esta, <br /></div>

            <a style="color: #1c9cea; text-decoration: none" href="#">
                clique aqui</a
            >
            para remover seu e-mail da lista.
        </div>
        <div style="padding-top: 30px; padding-bottom: 40px">
            <img src="{{ $absolutePath }}/YouFocus-final.png" alt="" />
        </div>
    </center>
</div>
