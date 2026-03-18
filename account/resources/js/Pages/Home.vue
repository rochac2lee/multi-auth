<script setup>
import AccountLayout from "@/Layouts/AccountLayout.vue";
import { usePage, router } from "@inertiajs/vue3";
import UserIcon from "@/assets/icons/user.svg";
import EditIcon from "@/assets/icons/edit.svg";
import UploadIconUrl from "@/assets/icons/uploading.svg?url";
import RefreshIconUrl from "@/assets/icons/refresh.svg?url";
import TrashIconUrl from "@/assets/icons/trash-dark.svg?url";
import axios from "axios";
import { computed, onBeforeUnmount, ref } from "vue";
import EditAccountModal from "@/Components/modals/EditAccountModal.vue";
import AdditionalAccountInfoModal from "@/Components/modals/AdditionalAccountInfoModal.vue";

const props = defineProps({
    user: Object,
    apps: Array,
    avatarCdnUrl: String,
});

const csrf = usePage().props.csrf;

const avatarInput = ref(null);
const avatarPreview = ref(null);
const avatarLoading = ref(false);

const isEditModalOpen = ref(false);
const isAdditionalModalOpen = ref(false);

const editForm = ref({
    nome_completo: props.user?.name ?? "",
    email: props.user?.email ?? "",
    senha: "",
    country_id: props.user?.country_id ?? props.user?.country?.id ?? null,
    country_name: props.user?.country?.name ?? "",
    country_flag_url: props.user?.country?.flag_url ?? "",
});

const additionalForm = ref({
    nome_estudio: props.user?.photography_studio ?? "",
    apelido: props.user?.surname ?? "",
    nichos_principais: props.user?.niches ?? [],
    instagram: props.user?.instagram ?? "",
});

const closeAllModals = () => {
    isEditModalOpen.value = false;
    isAdditionalModalOpen.value = false;
};

const openEditModal = () => {
    // Sincroniza valores atuais quando abre o modal.
    editForm.value = {
        nome_completo: props.user?.name ?? "",
        email: props.user?.email ?? "",
        senha: "",
        country_id: props.user?.country_id ?? props.user?.country?.id ?? null,
        country_name: props.user?.country?.name ?? "",
        country_flag_url: props.user?.country?.flag_url ?? "",
    };
    isEditModalOpen.value = true;
};

const saveEditModal = async () => {
    try {
        await axios.post(
            route("account.profile.update"),
            {
                nome_completo: editForm.value.nome_completo,
                senha: editForm.value.senha ? editForm.value.senha : null,
                country_id: editForm.value.country_id,
            },
            {
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                withCredentials: true,
            },
        );

        await router.reload({ only: ["user"] });
        editForm.value.senha = "";
        closeAllModals();
    } catch (error) {
        console.error(
            "Edit profile save error:",
            error?.response?.data ?? error,
        );
    }
};

const saveAdditionalModal = async () => {
    try {
        await axios.post(
            route("account.profile.update"),
            {
                nome_completo: props.user?.name ?? editForm.value.nome_completo,
                country_id:
                    props.user?.country_id ??
                    props.user?.country?.id ??
                    editForm.value.country_id ??
                    null,
                photography_studio: additionalForm.value.nome_estudio ?? null,
                surname: additionalForm.value.apelido ?? null,
                instagram: additionalForm.value.instagram ?? null,
                nichos_principais: Array.isArray(
                    additionalForm.value.nichos_principais,
                )
                    ? additionalForm.value.nichos_principais
                    : [],
            },
            {
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                withCredentials: true,
            },
        );

        await router.reload({ only: ["user"] });
        closeAllModals();
    } catch (error) {
        console.error(
            "Additional profile save error:",
            error?.response?.data ?? error,
        );
    }
};

const openAdditionalModal = () => {
    additionalForm.value = {
        nome_estudio: props.user?.photography_studio ?? "",
        apelido: props.user?.surname ?? "",
        nichos_principais: props.user?.niches ?? [],
        instagram: props.user?.instagram ?? "",
    };
    isAdditionalModalOpen.value = true;
};

const hasProfilePhoto = computed(() => {
    return !!(avatarPreview.value || props.user?.avatar);
});

const avatarDisplayUrl = computed(() => {
    if (avatarPreview.value) {
        return avatarPreview.value;
    }

    const avatarPath = props.user?.avatar;
    if (!avatarPath || !props.avatarCdnUrl) {
        return null;
    }

    // user.avatar é um path relativo (ex: "avatar/qyam....jpg").
    // Queremos: `${AVATAR_CDN_URL}/${avatarPath}`.
    const cleanAvatarPath = String(avatarPath).replace(/^\/+/, "");
    return `${props.avatarCdnUrl}/${cleanAvatarPath}`;
});

const openFilePicker = () => {
    avatarInput.value?.click();
};

const onAvatarFileChange = (event) => {
    const file = event.target?.files?.[0];
    if (!file) {
        return;
    }

    avatarLoading.value = true;

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarPreview.value = URL.createObjectURL(file);

    const formData = new FormData();
    formData.append("avatar", file);

    axios
        .post(route("account.avatar.upload"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
            withCredentials: true,
        })
        .then(() => {
            router.reload({ only: ["user"] });

            if (avatarPreview.value) {
                URL.revokeObjectURL(avatarPreview.value);
            }
            avatarPreview.value = null;
        })
        .catch((error) => {
            console.error(
                "Avatar upload error:",
                error?.response?.data ?? error,
            );
        })
        .finally(() => {
            avatarLoading.value = false;
            event.target.value = "";
        });
};

const deleteAvatar = async () => {
    if (avatarLoading.value) {
        return;
    }

    avatarLoading.value = true;

    try {
        const response = await fetch(route("account.avatar.delete"), {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content ?? "",
                Accept: "application/json",
            },
            credentials: "same-origin",
        });

        if (response.ok) {
            router.reload({ only: ["user"] });
        }
    } finally {
        avatarLoading.value = false;
    }
};

onBeforeUnmount(() => {
    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }
});
</script>

<template>
    <AccountLayout>
        <!-- Topbar -->
        <div
            class="bg-white h-[88px] flex items-center justify-center border-b border-[#EDEFF2]"
        >
            <img
                src="/images/logo-youfocus.png"
                alt="YouFocus"
                class="h-9 w-auto"
            />
        </div>
        <div class="account__header">
            <div class="account__header-container">
                <!-- Hero wrapper (hero escuro + avatar sobrepostos) -->
                <div class="relative h-[200px]">
                    <!-- Hero escuro -->
                    <div
                        class="absolute left-0 bottom-0 translate-y-[50%] w-8 h-8 rounded-full bg-[#76C3F2]"
                    ></div>

                    <div
                        class="bg-[#363646] rounded-br-[64px] relative overflow-hidden pt-[44px] pb-20 flex flex-col items-center"
                    >
                        <!-- Formas decorativas -->
                        <svg
                            class="absolute left-[106px] top-0 opacity-90"
                            width="200"
                            height="102"
                            viewBox="0 0 200 102"
                            fill="none"
                        >
                            <mask
                                id="mask-hero"
                                style="mask-type: alpha"
                                maskUnits="userSpaceOnUse"
                                x="0"
                                y="0"
                                width="200"
                                height="102"
                            >
                                <path
                                    d="M0 101.058L0 0L200 0L200 101.058H0Z"
                                    fill="#D9D9D9"
                                />
                            </mask>
                            <g mask="url(#mask-hero)">
                                <path
                                    d="M100 59.5238C67.127 59.5238 40.4762 32.873 40.4762 0C40.4762 -32.873 67.127 -59.5238 100 -59.5238C132.873 -59.5238 159.524 -32.873 159.524 0C159.484 32.8572 132.857 59.4841 100 59.5238ZM4.7619 0C4.7619 52.5952 47.4048 95.2381 100 95.2381C152.595 95.2381 195.238 52.5952 195.238 0C195.238 -52.5952 152.595 -95.2381 100 -95.2381C47.4048 -95.2381 4.7619 -52.5952 4.7619 0Z"
                                    fill="#76C3F2"
                                />
                            </g>
                        </svg>
                        <svg
                            class="absolute right-6 top-[44px]"
                            width="136"
                            height="112"
                            viewBox="0 0 136 112"
                            fill="none"
                        >
                            <path
                                d="M114 10H122V2C122 0.9 122.9 0 124 0C125.1 0 126 0.9 126 2V10H134C135.1 10 136 10.9 136 12C136 13.1 135.1 14 134 14H126V22C126 23.1 125.1 24 124 24C122.9 24 122 23.1 122 22V14H114C112.9 14 112 13.1 112 12C112 10.9 112.9 10 114 10Z"
                                fill="#76C3F2"
                            />
                            <circle
                                cx="44"
                                cy="44"
                                r="44"
                                transform="matrix(-1 0 0 1 88 24)"
                                fill="#76C3F2"
                            />
                        </svg>

                        <!-- Nome -->
                        <p
                            class="relative z-10 text-white text-[30px] font-bold text-center"
                        >
                            {{ user.surname ? user.surname : user.name }}
                        </p>
                    </div>

                    <!-- Avatar flutuante -->
                    <div
                        class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-10"
                    >
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            class="sr-only"
                            @change="onAvatarFileChange"
                        />

                        <div v-if="hasProfilePhoto" class="relative">
                            <div
                                class="relative w-[144px] h-[144px] rounded-full bg-[#EDEFF2] border-4 border-white overflow-hidden cursor-pointer group"
                                @click="openFilePicker"
                            >
                                <img
                                    v-if="avatarDisplayUrl"
                                    :src="avatarDisplayUrl"
                                    alt="Foto do perfil"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center"
                                >
                                    <UserIcon class="w-[50.67px] h-[50.67px]" />
                                </div>

                                <div
                                    v-if="avatarLoading"
                                    class="absolute inset-0 flex items-center justify-center bg-black/40"
                                >
                                    <div
                                        class="w-10 h-10 rounded-full border-4 border-white border-t-transparent animate-spin"
                                    ></div>
                                </div>

                                <div
                                    class="absolute inset-0 flex items-center justify-center gap-2 bg-black bg-opacity-50 rounded-full opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                >
                                    <button
                                        type="button"
                                        class="bg-white px-2 py-1 rounded-[4px] text-xs font-semibold text-[#363646] w-8 h-8"
                                        @click.stop="openFilePicker"
                                    >
                                        <img
                                            :src="RefreshIconUrl"
                                            alt="Refresh"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        class="bg-white px-2 py-1 rounded-[4px] text-xs font-semibold text-[#ff3942] w-8 h-8"
                                        @click.stop="deleteAvatar"
                                    >
                                        <img
                                            :src="TrashIconUrl"
                                            alt="Trash"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <div
                                class="relative w-[144px] h-[144px] rounded-full bg-[#EDEFF2] border-4 border-white flex items-center justify-center cursor-pointer group"
                                @click="openFilePicker"
                            >
                                <UserIcon class="w-[50.67px] h-[50.67px]" />

                                <div
                                    class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center gap-1 rounded-full opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                >
                                    <div
                                        v-if="avatarLoading"
                                        class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-full border-4 border-white border-t-transparent animate-spin"
                                        ></div>
                                    </div>
                                    <img
                                        :src="UploadIconUrl"
                                        alt="Add Imagem"
                                        class="w-8 h-8"
                                    />
                                    <span
                                        class="text-white text-xs font-semibold"
                                    >
                                        Add Imagem
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#F9FAFB]">
            <!-- Conteúdo -->
            <div class="pt-24 pb-12">
                <div class="max-w-[1224px] mx-auto">
                    <!-- Cards: Dados de cadastro + Informações adicionais -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <!-- Dados de cadastro -->
                        <div>
                            <h3 class="text-lg font-bold text-[#363646] mb-4">
                                Dados de cadastro
                            </h3>
                            <div
                                class="min-h-[295px] bg-white border border-[#E0E5EA] rounded-[4px] p-6 relative"
                            >
                                <button
                                    type="button"
                                    @click="openEditModal"
                                    class="absolute top-4 right-4 w-8 h-8 hover:bg-[#EDEFF2] transition-colors duration-300 rounded-[4px] flex items-center justify-center group cursor-pointer"
                                >
                                    <EditIcon
                                        class="w-4 h-4 text-[#8496AA] group-hover:text-[#363646]"
                                    />
                                </button>
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Nome completo:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.name || "—" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            E-mail de cadastro:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.email || "—" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Senha:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            ***********
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            País:
                                        </p>
                                        <p
                                            class="text-base text-[#363646] flex items-center gap-1"
                                        >
                                            <img
                                                v-if="user.country?.flag_url"
                                                :src="user.country.flag_url"
                                                alt=""
                                                class="w-7 h-5"
                                            />
                                            <span>
                                                {{ user.country?.name || "—" }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações adicionais -->
                        <div>
                            <h3 class="text-lg font-bold text-[#363646] mb-4">
                                Informações adicionais
                            </h3>
                            <div
                                class="min-h-[295px] bg-white border border-[#E0E5EA] rounded-[4px] p-6 relative"
                            >
                                <button
                                    type="button"
                                    @click="openAdditionalModal"
                                    class="absolute top-4 right-4 w-8 h-8 hover:bg-[#EDEFF2] transition-colors duration-300 rounded-[4px] flex items-center justify-center group cursor-pointer"
                                >
                                    <EditIcon
                                        class="w-4 h-4 text-[#8496AA] group-hover:text-[#363646]"
                                    />
                                </button>
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Nome do estúdio:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.photography_studio || "—" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Como deseja ser chamado (Nome ou
                                            Apelido):
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.surname || "—" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Nichos Principais:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.niches.join(", ") || "—" }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-[#8496AA]"
                                        >
                                            Instagram:
                                        </p>
                                        <p class="text-base text-[#363646]">
                                            {{ user.instagram || "—" }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Minhas assinaturas -->
                    <div v-if="apps && apps.length > 0" class="mb-8">
                        <h3 class="text-lg font-bold text-[#363646] mb-4">
                            Minhas assinaturas
                        </h3>

                        <div
                            v-for="app in apps"
                            :key="app.id"
                            class="bg-white border border-[#E0E5EA] rounded-[4px] h-24 flex items-center px-6 gap-6 mb-2"
                        >
                            <div class="w-[132px] flex-shrink-0">
                                <span
                                    class="text-sm font-bold text-[#363646]"
                                    >{{ app.name }}</span
                                >
                            </div>

                            <span
                                class="inline-flex items-center h-6 px-3 rounded-[20px] bg-[#25D060] text-white text-xs font-semibold flex-shrink-0"
                            >
                                Ativa
                            </span>

                            <div class="flex-1"></div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a
                                    v-if="app.redirect_uri"
                                    :href="app.redirect_uri"
                                    class="inline-flex items-center justify-center h-8 px-3 rounded-[4px] bg-[#25D060] border border-[#21BB56] text-white text-[13px] font-semibold"
                                >
                                    Acessar produto
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-[#EDEFF2] my-8"></div>

                    <!-- Ações -->
                    <div
                        class="bg-[#FFEBEB] rounded-[4px] px-6 py-8 flex items-center gap-4"
                    >
                        <button
                            class="inline-flex items-center justify-center h-11 px-5 rounded-[4px] bg-[#494958] border border-[#363646] text-white text-base font-semibold"
                        >
                            Alterar senha
                        </button>

                        <form
                            id="logout-form"
                            method="POST"
                            :action="route('logout')"
                        >
                            <input type="hidden" name="_token" :value="csrf" />
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center h-11 px-5 rounded-[4px] bg-[#FF3942] border border-[#E5333B] text-white text-base font-semibold"
                            >
                                Sair da conta
                            </button>
                        </form>

                        <a
                            :href="route('account.terminate.index')"
                            class="inline-flex items-center justify-center h-11 px-5 rounded-[4px] bg-[#FF3942] border border-[#E5333B] text-white text-base font-semibold"
                        >
                            Excluir conta na YouFocus
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center py-6 text-sm font-semibold text-[#A3B0BF]">
                Feito com ♥ e ☁ para os Fotógrafos
            </div>
        </div>

        <EditAccountModal
            :open="isEditModalOpen"
            :model="editForm"
            @close="closeAllModals"
            @save="saveEditModal"
        />

        <AdditionalAccountInfoModal
            :open="isAdditionalModalOpen"
            :model="additionalForm"
            @close="closeAllModals"
            @save="saveAdditionalModal"
        />
    </AccountLayout>
</template>

<style scoped>
.account__header {
    height: 200px;
    border-radius: 0 0 64px 0;
    background: #363646;
}
.account__header-container {
    width: 1224px;
    height: 100%;
    margin-left: auto;
    margin-right: auto;
    position: relative;
}
</style>
