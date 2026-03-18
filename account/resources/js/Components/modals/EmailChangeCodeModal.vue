<template>
    <AccountModalBase
        :open="open"
        title="Verificar código"
        :textButton="textButton"
        @close="emitClose"
        @save="submit"
        customClass="w-[520px]"
    >
        <div class="space-y-6">
            <div class="bg-[#FFFAE7] p-6 flex items-center gap-2">
                <InfoIcon class="w-5 h-5" />
                <p class="text-[#363646] text-[14px]">
                    Digite o código de 6 dígitos enviado para
                    <b>{{ pendingEmail }}</b>.
                </p>
            </div>

            <div>
                <label
                    class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Código de verificação</label
                >
                <input
                    v-model="code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                    placeholder="000000"
                />
            </div>
        </div>
    </AccountModalBase>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import AccountModalBase from "./AccountModalBase.vue";
import InfoIcon from "@/assets/icons/info.svg";

const props = defineProps({
    open: { type: Boolean, default: false },
    pendingEmail: { type: String, default: "" },
    textButton: { type: String, default: "Verificar" },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");

const csrf = usePage().props.csrf;

const code = ref("");

const submit = async () => {
    const normalized = String(code.value ?? "").trim();
    if (!/^\d{6}$/.test(normalized)) {
        return;
    }

    try {
        await axios.post(
            "/account/email/verify",
            {
                newMail: props.pendingEmail,
                code: normalized,
            },
            {
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                withCredentials: true,
            }
        );

        emit("save");
    } catch (error) {
        console.error(
            "Email verify code error:",
            error?.response?.data ?? error
        );
    }
};
</script>

