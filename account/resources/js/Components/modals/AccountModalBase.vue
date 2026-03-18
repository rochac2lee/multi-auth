<script setup>
import CloseIcon from "@/assets/icons/close.svg";

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, required: true },
});

defineEmits(["close", "save"]);
</script>
<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] bg-black/50 flex items-center justify-center p-4"
        @click.self="$emit('close')"
    >
        <div
            class="bg-white border border-[#E0E5EA] rounded-[4px] w-full max-w-[464px] overflow-hidden"
        >
            <div
                class="px-6 py-5 border-b border-[#EDEFF2] bg-[#F9FAFB] flex items-center justify-between"
            >
                <h3 class="text-lg font-bold text-[#363646]">
                    {{ title }}
                </h3>
                <CloseIcon
                    @click="$emit('close')"
                    class="w-4 h-4 cursor-pointer"
                />
            </div>

            <div class="px-6 py-5">
                <slot />
            </div>

            <div
                class="px-6 py-6 flex items-center justify-end gap-6 border-t border-[#EDEFF2]"
            >
                <button
                    type="button"
                    class="h-10 px-5 rounded-[4px] border border-[#BBC5D0] text-[#363646] font-semibold"
                    @click="$emit('close')"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="h-10 px-5 rounded-[4px] bg-[#25D060] border border-[#21BB56] text-white font-semibold"
                    @click="$emit('save')"
                >
                    Salvar
                </button>
            </div>
        </div>
    </div>
</template>
