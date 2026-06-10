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
    form.post(`/unit/perdagangan-besar/login`, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Admin Unit Perdagangan Besar" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
        <!-- Background Ambient Gradients -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-amber-500/5 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-slate-800/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 space-y-4">
            <!-- Back button -->
            <div class="flex justify-start">
                <Link :href="route('unit.welcome', { slug: 'perdagangan-besar' })" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-lg">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke Landing Page
                </Link>
            </div>

            <!-- Logo and header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center p-3 bg-amber-500/10 border border-amber-500/30 rounded-2xl w-16 h-16 shadow-lg shadow-amber-500/10 mx-auto">
                    <span class="material-symbols-outlined text-amber-450 text-3xl font-bold">local_shipping</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-white">Kelola Perdagangan Besar</h2>
                <p class="text-xs text-slate-450 uppercase tracking-widest font-semibold">Portal Administrator & Logistik Kargo</p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-slate-900/60 backdrop-blur-md py-8 px-6 shadow-2xl border border-slate-850 rounded-3xl sm:px-10">
                <div v-if="status" class="mb-4 text-xs font-semibold text-amber-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email address -->
                    <div>
                        <label for="email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">alternate_email</span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="perdagangan@ciwuni.desa.id"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950/70 py-3 pl-11 pr-4 text-xs text-slate-100 placeholder-slate-605 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all"
                                required
                                autofocus
                            />
                        </div>
                        <InputError class="mt-2 text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kata Sandi</label>
                            <span class="text-[10px] text-amber-450 hover:text-amber-400 font-bold uppercase cursor-pointer">Lupa Sandi?</span>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-lg">lock</span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950/70 py-3 pl-11 pr-12 text-xs text-slate-100 placeholder-slate-605 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all"
                                required
                            />
                            <button
                                type="button"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-350 transition"
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
                            class="h-4.5 w-4.5 rounded border-slate-800 bg-slate-950 text-amber-500 focus:ring-amber-500/35 accent-amber-500"
                        />
                        <label for="remember_me" class="ml-2 block text-xs text-slate-400 cursor-pointer">
                            Ingat sesi saya di perangkat ini
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-amber-500/15 text-xs font-bold text-slate-950 bg-amber-500 hover:bg-amber-450 focus:outline-none transition-all active:scale-[0.98]"
                            :class="{ 'opacity-70': form.processing }"
                            :disabled="form.processing"
                        >
                            Masuk ke Sistem Pengelolaan
                        </button>
                    </div>
                </form>

                <!-- Seed Note info -->
                <div class="mt-6 border-t border-slate-850 pt-5 text-center">
                    <p class="text-[10px] text-slate-500 leading-relaxed">
                        Akun Default Uji Coba: <br />
                        Email: <span class="font-bold text-amber-400">perdagangan@bumdes.com</span> • Sandi: <span class="font-bold text-amber-400">password</span>
                    </p>
                </div>
            </div>

            <!-- Bottom security info -->
            <div class="mt-6 text-center text-[10px] text-slate-650 flex items-center justify-center gap-4">
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">verified_user</span> SSL Secured</span>
                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">shield</span> BUMDes Ciwuni Guard</span>
            </div>
        </div>
    </div>
</template>
