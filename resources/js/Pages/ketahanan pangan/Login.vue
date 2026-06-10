<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const passwordVisible = ref(false);

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};

const submit = () => {
    form.post(`/unit/ketahanan-pangan/login`, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Admin Unit Ketahanan Pangan" />

    <div class="min-h-screen bg-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
        <!-- Background circle styling -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-700/5 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 space-y-4">
            <!-- Back button -->
            <div class="flex justify-start">
                <Link :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-blue-700 transition bg-white border border-slate-250 px-3.5 py-2 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke Landing Page
                </Link>
            </div>

            <!-- Logo and header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center p-3 bg-blue-700 text-white rounded-2xl w-16 h-16 shadow-lg mx-auto">
                    <span class="material-symbols-outlined text-blue-600 text-3xl font-bold">agriculture</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">Kelola Ketahanan Pangan</h2>
                <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">Portal Pengurus Gudang & Lumbung Desa</p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-white py-8 px-6 shadow-xl border border-slate-200 rounded-3xl sm:px-10">
                <div v-if="status" class="mb-4 text-xs font-semibold text-blue-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email address -->
                    <div>
                        <label for="email" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">alternate_email</span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="pangan@ciwuni.desa.id"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 pl-11 pr-4 text-xs text-slate-850 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500/25 transition-all"
                                required
                                autofocus
                            />
                        </div>
                        <InputError class="mt-2 text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kata Sandi</label>
                            <span class="text-[10px] text-blue-600 hover:text-blue-600 font-bold uppercase cursor-pointer">Lupa Sandi?</span>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-3 pl-11 pr-12 text-xs text-slate-850 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500/25 transition-all"
                                required
                            />
                            <button
                                type="button"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                                @click="togglePassword"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ passwordVisible ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <InputError class="mt-2 text-xs" :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input
                            id="remember_me"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4.5 w-4.5 rounded border-slate-300 bg-slate-50 text-blue-500 focus:ring-blue-500/35 accent-blue-600"
                        />
                        <label for="remember_me" class="ml-2 block text-xs text-slate-500 cursor-pointer">
                            Ingat sesi saya di perangkat ini
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all active:scale-[0.98]"
                            :class="{ 'opacity-70': form.processing }"
                            :disabled="form.processing"
                        >
                            Masuk ke Sistem Pengelolaan
                        </button>
                    </div>
                </form>

                <!-- Seed Note info -->
                <div class="mt-6 border-t border-slate-150 pt-5 text-center">
                    <p class="text-[10px] text-slate-550 leading-relaxed">
                        Akun Default Uji Coba: <br />
                        Email: <span class="font-bold text-blue-600">pangan@bumdes.com</span> • Sandi: <span class="font-bold text-blue-600">password</span>
                    </p>
                </div>
            </div>

            <!-- Bottom security info -->
            <div class="mt-6 text-center text-[10px] text-slate-500 flex items-center justify-center gap-4">
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">verified_user</span> SSL Secured</span>
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">shield</span> BUMDes Ciwuni Guard</span>
            </div>
        </div>
    </div>
</template>
