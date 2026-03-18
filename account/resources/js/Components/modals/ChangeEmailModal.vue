<script setup>
import AccountModalBase from "./AccountModalBase.vue";
import InfoIcon from "@/assets/icons/info.svg";

const props = defineProps({
    open: { type: Boolean, default: false },
    model: { type: Object, required: true },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");
const emitSave = () => emit("save");
</script>

<template>
    <AccountModalBase
        :open="open"
        title="Alterar E-mail"
        @close="emitClose"
        @save="emitSave"
        text-button="Continuar"
    >
        <div class="space-y-6">
            <div class="bg-[#FFFAE7] p-6 flex items-center gap-2">
                <InfoIcon />
                <p class="text-[#363646] text-[14px]">
                    Informe seu novo e-mail no campo abaixo para receber o
                    código de verificação e concluir a alteração.
                </p>
            </div>
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
</template>
