<template>
    <AccountModalBase
        :open="open"
        title="Informações adicionais"
        @close="emitClose"
        @save="emitSave"
    >
        <div class="space-y-4">
            <!-- Studio Name -->
            <div>
                <label class="block text-sm font-semibold text-[#8496AA] mb-1"
                    >Nome do estúdio</label
                >
                <input
                    v-model="model.nome_estudio"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#E0E5EA] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>

            <!-- Display name -->
            <div>
                <label class="block text-sm font-semibold text-[#8496AA] mb-1"
                    >Como deseja ser chamado (Nome ou Apelido)</label
                >
                <input
                    v-model="model.apelido"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#E0E5EA] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>

            <!-- Main niches (disabled) -->
            <div>
                <label class="block text-sm font-semibold text-[#8496AA] mb-1"
                    >Nichos Principais</label
                >
                <input
                    :value="Array.isArray(model.nichos_principais) ? (model.nichos_principais.length + ' niches') : '—'"
                    disabled
                    class="w-full border border-[#E0E5EA] rounded-[4px] px-3 py-2 text-[#363646] bg-[#F9FAFB] cursor-not-allowed"
                />
            </div>

            <!-- Instagram -->
            <div>
                <label class="block text-sm font-semibold text-[#8496AA] mb-1"
                    >Instagram</label
                >
                <input
                    v-model="model.instagram"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#E0E5EA] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>
        </div>
    </AccountModalBase>
</template>

<script setup>
import AccountModalBase from "./AccountModalBase.vue";

defineProps({
    open: { type: Boolean, default: false },
    model: { type: Object, required: true },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");
const emitSave = () => emit("save");
</script>

