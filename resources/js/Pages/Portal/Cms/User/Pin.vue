<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref } from 'vue';

const form = useForm({
    pin: '',
});

const showPin = ref(false);

const submit = () => {
    form.post(route('portal.cms.pin.verify'));
};
</script>

<template>
    <Head title="Verifikasi Keamanan BUMDes" />

    <PortalLayout>
        <template #header>Sistem Keamanan Berlapis</template>

        <div class="max-w-md mx-auto mt-10">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
                <div class="bg-emerald-600 p-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 text-white mb-4">
                        <span class="material-symbols-outlined text-3xl">lock</span>
                    </div>
                    <h2 class="text-xl font-bold text-white">Area Sensitif BUMDes</h2>
                    <p class="text-emerald-100 text-sm mt-1">Kelola Akses Admin & Pengguna</p>
                </div>

                <div class="p-8">
                    <p class="text-sm text-slate-600 text-center mb-6">
                        Demi keamanan data BUMDes, silakan masukkan <strong>PIN Keamanan</strong> sebelum mengakses pengaturan ini.
                    </p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2 text-center">Masukkan 6 Digit PIN</label>
                            <div class="relative max-w-[240px] mx-auto">
                                <input
                                    id="pin"
                                    :type="showPin ? 'text' : 'password'"
                                    v-model="form.pin"
                                    class="w-full text-center tracking-[0.5em] font-mono text-2xl py-3 border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500"
                                    required
                                    autofocus
                                    maxlength="6"
                                    placeholder="••••••"
                                />
                                <button type="button" @click="showPin = !showPin" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-[20px]">{{ showPin ? 'visibility_off' : 'visibility' }}</span>
                                </button>
                            </div>
                            <p v-if="form.errors.pin" class="mt-2 text-sm text-rose-500 text-center font-medium">{{ form.errors.pin }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors"
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                        >
                            <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
                            Buka Brankas Pengaturan
                        </button>
                    </form>
                </div>
                <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Terlindungi dengan Enkripsi</p>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
