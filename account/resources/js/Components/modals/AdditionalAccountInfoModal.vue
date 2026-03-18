<template>
    <AccountModalBase
        :open="open"
        title="Informações adicionais"
        @close="emitClose"
        @save="emitSave"
    >
        <div class="space-y-6">
            <!-- Studio Name -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Nome do estúdio</label
                >
                <input
                    v-model="model.nome_estudio"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>

            <!-- Display name -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Como deseja ser chamado (Nome ou Apelido)</label
                >
                <input
                    v-model="model.apelido"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>

            <!-- Main niches (disabled) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-semibold text-[#363646]"
                        >* Nichos Principais</label
                    >
                    <a
                        class="text-[#1C9CEA] font-semibold text-sm cursor-pointer"
                        @click="changeNiche"
                        >Alterar nichos</a
                    >
                </div>
                <input
                    :value="
                        Array.isArray(model.nichos_principais) &&
                        model.nichos_principais.length > 0
                            ? model.nichos_principais.join(', ')
                            : '—'
                    "
                    disabled
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646] bg-[#F9FAFB] cursor-not-allowed"
                />
            </div>

            <!-- Instagram -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Instagram</label
                >
                <input
                    v-model="model.instagram"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>
        </div>
    </AccountModalBase>

    <MainNichesModal
        :open="isMainNichesModalOpen"
        :selected-niches="
            Array.isArray(model?.nichos_principais)
                ? model.nichos_principais
                : []
        "
        @close="isMainNichesModalOpen = false"
        @save="onMainNichesSave"
    />
</template>

<script setup>
import AccountModalBase from "./AccountModalBase.vue";
import MainNichesModal from "./MainNichesModal.vue";
import { ref } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    model: { type: Object, required: true },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");
const emitSave = () => emit("save");

const isMainNichesModalOpen = ref(false);

const changeNiche = () => {
    isMainNichesModalOpen.value = true;
};

const onMainNichesSave = (selected) => {
    if (!Array.isArray(selected)) {
        return;
    }

    // Evita problemas de reatividade em props: muta o array em-place.
    if (Array.isArray(props.model.nichos_principais)) {
        props.model.nichos_principais.splice(
            0,
            props.model.nichos_principais.length,
            ...selected
        );
    } else {
        // fallback (deveria cair pouco, já que o Home inicializa como array)
        props.model.nichos_principais = selected;
    }
    isMainNichesModalOpen.value = false;
};
</script>
