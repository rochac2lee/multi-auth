<template>
    <AccountModalBase
        :open="open"
        title="Nichos Principais"
        @close="emitClose"
        @save="emitSave"
        custom-class="w-[552px]"
    >
        <div class="px-1">
            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                <label
                    v-for="niche in niches"
                    :key="niche.id"
                    class="flex items-center gap-2 cursor-pointer"
                >
                    <input
                        type="checkbox"
                        :value="niche.name"
                        v-model="localSelected"
                        class="w-5 h-5 border border-[#8496AA] rounded-[4px]"
                    />
                    <span class="text-[#363646] text-[14px]">{{
                        niche.name
                    }}</span>
                </label>
            </div>
        </div>
    </AccountModalBase>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import AccountModalBase from "./AccountModalBase.vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    selectedNiches: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");
const emitSave = () => emit("save", localSelected.value);

const niches = ref([]);
const localSelected = ref([]);

const loadNiches = async () => {
    try {
        const response = await axios.get("/niches");
        niches.value = response.data ?? [];
    } catch (error) {
        console.error("Failed to load niches:", error?.response?.data ?? error);
        niches.value = [];
    }
};

watch(
    () => props.selectedNiches,
    (value) => {
        localSelected.value = Array.isArray(value) ? [...value] : [];
    },
    { immediate: true },
);

onMounted(async () => {
    await loadNiches();
});
</script>
