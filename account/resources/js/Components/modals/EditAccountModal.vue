<script setup>
import AccountModalBase from "./AccountModalBase.vue";
import ArrowDownIcon from "@/assets/icons/arrow-down.svg";
import axios from "axios";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    model: { type: Object, required: true },
});

const emit = defineEmits(["close", "save"]);

const emitClose = () => emit("close");
const emitSave = () => emit("save");

const countries = ref([]);
const isCountryDropdownOpen = ref(false);
const countrySearch = ref("");

const selectedCountry = computed(() => {
    const rawId = props.model?.country_id ?? null;
    if (rawId === null || rawId === undefined || rawId === "") {
        return null;
    }

    const id = Number(rawId);
    if (Number.isNaN(id)) {
        return null;
    }

    const fromList = countries.value.find((c) => Number(c.id) === id) ?? null;
    if (fromList) {
        return fromList;
    }

    // Fallback: quando a lista ainda não carregou, usa o nome vindo do model.
    const nameFallback = props.model?.country_name
        ? String(props.model.country_name).trim()
        : null;

    if (!nameFallback) {
        return null;
    }

    const cf = props.model?.country_flag_url;
    const flagTrimmed = typeof cf === "string" ? cf.trim() : null;

    return {
        id,
        name: nameFallback,
        flag_url: flagTrimmed || null,
    };
});

const getCountries = async () => {
    try {
        // Preferimos a rota nameada (quando disponível no Ziggy).
        const response = await axios.get(route("countries"));
        countries.value = response.data ?? [];
        return;
    } catch (error) {
        // Fallback: endpoint direto (evita problema do Ziggy não exportar a rota nova).
        try {
            const response = await axios.get("/countries");
            countries.value = response.data ?? [];
        } catch (e) {
            console.error("Failed to load countries:", e);
            countries.value = [];
        }
    }
};

const toggleCountryDropdown = () => {
    isCountryDropdownOpen.value = !isCountryDropdownOpen.value;
    if (isCountryDropdownOpen.value) {
        countrySearch.value = "";
    }
};

const filteredCountries = computed(() => {
    const q = String(countrySearch.value ?? "")
        .trim()
        .toLowerCase();
    if (!q) {
        return countries.value;
    }

    return countries.value.filter((c) => {
        const name = String(c?.name ?? "").toLowerCase();
        return name.includes(q);
    });
});

const selectCountry = (countryId) => {
    props.model.country_id = countryId;
    if (countryId) {
        const found =
            countries.value.find((c) => Number(c.id) === Number(countryId)) ??
            null;
        props.model.country_name = found?.name ?? props.model.country_name;
        props.model.country_flag_url =
            found?.flag_url ?? props.model.country_flag_url;
    } else {
        props.model.country_name = "";
        props.model.country_flag_url = "";
    }
    isCountryDropdownOpen.value = false;
};

const onDocClick = (event) => {
    const target = event.target;
    if (!target) {
        return;
    }

    // Fechamento se o clique foi fora do dropdown/button.
    const el = target.closest?.("[data-country-dropdown]");
    if (!el) {
        isCountryDropdownOpen.value = false;
    }
};

onMounted(async () => {
    await getCountries();
    document.addEventListener("click", onDocClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocClick);
});
</script>

<template>
    <AccountModalBase
        :open="open"
        title="Dados de cadastro"
        @close="emitClose"
        @save="emitSave"
    >
        <div class="space-y-6">
            <!-- Full name -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Nome completo</label
                >
                <input
                    v-model="model.nome_completo"
                    type="text"
                    maxlength="100"
                    class="w-full border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646]"
                />
            </div>

            <!-- Registration email (disabled) -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* E-mail de cadastro</label
                >
                <input
                    v-model="model.email"
                    type="email"
                    disabled
                    class="w-full rounded-[4px] px-3 py-2 text-[#A3B0BF] bg-[#EDEFF2] cursor-not-allowed"
                />
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* Senha</label
                >
                <input
                    v-model="model.senha"
                    type="password"
                    maxlength="100"
                    class="w-full rounded-[4px] px-3 py-2 text-[#A3B0BF] bg-[#EDEFF2]"
                />
            </div>

            <!-- Country select -->
            <div>
                <label class="block text-sm font-semibold text-[#363646] mb-1"
                    >* País</label
                >
                <div
                    class="relative vf input fluid select-pais"
                    data-country-dropdown
                >
                    <button
                        type="button"
                        class="w-full flex items-center justify-between border border-[#BBC5D0] rounded-[4px] px-3 py-2 text-[#363646] bg-white"
                        @click="toggleCountryDropdown"
                    >
                        <span class="flex items-center gap-2">
                            <img
                                v-if="selectedCountry?.flag_url"
                                :src="selectedCountry.flag_url"
                                alt=""
                                class="select__image"
                            />
                            <span>{{
                                selectedCountry?.name ||
                                model?.country_name ||
                                "Selecione um país"
                            }}</span>
                        </span>
                        <ArrowDownIcon class="w-4 h-4" />
                    </button>

                    <div
                        v-if="isCountryDropdownOpen"
                        class="absolute left-0 right-0 mt-2 bg-white border border-[#E0E5EA] rounded-[4px] max-h-64 overflow-auto z-[80]"
                    >
                        <div class="px-3 py-2">
                            <input
                                v-model="countrySearch"
                                type="text"
                                maxlength="80"
                                placeholder="Buscar país"
                                class="w-full border border-[#E0E5EA] rounded-[4px] px-3 py-2 text-[#363646] focus:outline-none"
                            />
                        </div>

                        <button
                            v-for="c in filteredCountries"
                            :key="c.id"
                            type="button"
                            class="w-full px-3 py-2 text-left hover:bg-[#F9FAFB] flex items-center gap-2"
                            :class="{
                                'bg-[#F9FAFB]':
                                    model?.country_id != null &&
                                    Number(model.country_id) === Number(c.id),
                            }"
                            @click="selectCountry(c.id)"
                        >
                            <img
                                v-if="c.flag_url"
                                :src="c.flag_url"
                                alt=""
                                class="select__image"
                            />
                            <span class="text-[#363646]">{{ c.name }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AccountModalBase>
</template>

<style scoped>
.select-pais .select__image {
    min-width: 25px !important;
    max-height: 25px !important;
    justify-content: left !important;
    overflow: hidden !important;
    margin-right: 3px !important;
    margin-left: -2px !important;
}

.select-pais img {
    height: 20px !important;
    transform: translateX(-3px) !important;
}
</style>
