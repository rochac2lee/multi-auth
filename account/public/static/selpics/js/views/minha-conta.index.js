const app = new Vue({
    el: '#app',
    data: function() {
        return {
            fotografo: fotografo,
            countries: [],
            isLoadingYoubox: true,
            isLoadingCard: true,
            youbox: {},
            card: {}
        };
    },
    computed: {
        pais(){
            return _.find(this.countries, { 'sigla2': fotografo.pais });
        },
        hasCard() {
            return !!this.card.last_digits
        },
        cardBrandImg() {
            let pathPrefix = 'static/common/images/payment/account-card-brands/';
            let brands = ['mastercard', 'visa', 'elo', 'amex', 'hipercard'];
            if (brands.includes(this.card.brand)) {
                return pathPrefix + `${this.card.brand}.svg`
            }
            return pathPrefix + `other.svg`
        },
        cardExpirationDate() {
            if (!this.card.expiration_date) {
                return {
                    month: '--',
                    year: '--'
                }
            }

            return {
                month: this.card.expiration_date.substr(0, 2),
                year: this.card.expiration_date.substr(2, 2)
            }
        }
    },
    mounted(){
        this.getCountries();
        this.getYouboxInfo();
        this.getCard();
        // this.addVideo('https://help.youfocus.com.br/category/youfocus/minha-conta');
    },
    methods: {
        refreshTooltip() {
            tippy('.vf.tooltip',{
                theme: 'vflight',
                arrow: true,
                offset: '140, 2',
                interactive: true,
                trigger: 'click',
                zIndex: 999
            });
        },
        getCountries(){
            axios.get(route('countries'))
            .then(response => {
                this.countries = response.data;
            })
        },
        senhaAlterada(){
            this.showHintBox('Senha alterada com sucesso!');
        },
        editarCadastro(){
            this.showModal('minha-conta-modal-editar-cadastro', { fotografo });
        },
        editarContato(){
            this.showModal('minha-conta-modal-editar-contatos', { fotografo });
        },
        openCropModal(file){
            this.showModal('minha-conta-crop-foto', { cliente: this.fotografo, image: file });
        },
        doneCrop(res){
            this.fotografo.foto = res.filename;
            this.fotografo.avatar.photo = res.path;
        },
        deleteFoto(){
            this.fotografo.foto = "";
            this.fotografo.avatar.photo = "";
            axios.delete(route('minha-conta.delete-photo', this.cliente));
        },
        doneUpdateEmail(newEmail) {
            this.fotografo.email = newEmail;
        },
        getCard() {
            this.isLoadingCard = true;
            axios.get(
              route('account.card')
            ).then(({data}) => {
                if (data.last_digits) {
                    this.card = data
                }
            }).finally(() => {
                this.isLoadingCard = false
            })
        },
        getYouboxInfo() {
            this.isLoadingYoubox = true;
            axios.get(
              route('workspace.youbox-info')
            ).then(({data}) => {
                if (data) {
                    this.youbox = data
                }
                setTimeout(() => {
                    this.refreshTooltip();
                }, 1000)
            }).finally(() => {
                this.isLoadingYoubox = false
            })
        },
        showYouBoxUnsubscriptionReasonModal() {
            this.$modal.show('unsubscription-reason', {
                onConfirm: (modal) => {
                    modal.close();
                    this.showCancelYouboxSubscriptionModal();
                    axios.post(route('account.unsubscription-reason'), {
                        reason: modal.reason,
                        reason_description: modal.reason_description,
                        product: 'YOUBOX'
                    }).then(response => {})

                }
            });
        },
        showCancelSelpicsSubscriptionModal() {
            this.$modal.show('cancel-selpics-subscription');
        },
        showCancelYouboxSubscriptionModal() {
            this.$modal.show('cancel-youbox-subscription', {
                shouldRefund: this.youbox.subscription_should_refund,
                expirationDate: this.youbox.subscription_expiration_formatted_date
            });
        },
        showUpdateCreditCardModal() {
            this.$modal.show('update-credit-card');
        },
        showUpdateCreditCardResponseModal() {
            this.$modal.show('update-credit-card-response', {
                isSuccess: true
            });
        },
        onCardUpdated(card) {
            this.getCard();
        }
    }
});