<script setup>
import AccountModalBase from "./AccountModalBase.vue";
import EmailChangeCodeModal from "./EmailChangeCodeModal.vue";
import axios from "axios";
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    open: { type: Boolean, default: false },
    model: { type: Object, required: true },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");

const csrf = usePage().props.csrf;

const isCodeModalOpen = ref(false);
const pendingEmail = ref("");

const onContinue = async () => {
    const newMail = String(props.model?.newMail ?? "").trim();
    if (!newMail || !/^\S+@\S+\.\S+$/.test(newMail)) {
        return;
    }

    try {
        await axios.post(
            "/account/email/change",
            { newMail },
            {
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                withCredentials: true,
            },
        );

        pendingEmail.value = newMail;
        isCodeModalOpen.value = true;
    } catch (error) {
        console.error(
            "Email change request error:",
            error?.response?.data ?? error,
        );
    }
};
</script>

<template>
    <AccountModalBase
        :open="open"
        title="Alterar E-mail"
        @close="emitClose"
        @save="onContinue"
        :has-info-text="true"
        info-text="Informe seu novo e-mail no campo abaixo para receber o código de verificação e concluir a alteração."
        text-button="Continuar"
    >
        <div class="space-y-6">
            <!-- Full name -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Novo e-mail de cadastro</label
                >
                <input
                    v-model="model.newMail"
                    type="email"
                    maxlength="100"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>
        </div>
    </AccountModalBase>

    <EmailChangeCodeModal
        :open="isCodeModalOpen"
        :pendingEmail="pendingEmail"
        @close="isCodeModalOpen = false"
        @save="
            isCodeModalOpen = false;
            emit('save');
            emitClose();
            router.reload({ only: ['user'] });
        "
        text-button="Verificar"
    />
</template>
