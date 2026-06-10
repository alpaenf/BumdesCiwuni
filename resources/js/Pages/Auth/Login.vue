<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
    app: {
        type: String,
        default: 'portal',
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    role: 'admin',
});

const selectedRole = ref('admin');
const passwordVisible = ref(false);

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};

const roleButtonClass = (role) =>
    selectedRole.value === role
        ? 'border-[color:var(--color-primary)] bg-[color:var(--color-surface-container-high)] text-[color:var(--color-primary)]'
        : 'border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)] text-[color:var(--color-secondary)]';

const roleIconFill = (role) => (selectedRole.value === role ? '"FILL" 1' : '"FILL" 0');

const submit = () => {
    form.role = selectedRole.value;
    const postRoute = props.app === 'portal' ? route('portal.login') : route('login');
    form.post(postRoute, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="flex min-h-screen w-full flex-col md:flex-row text-[color:var(--color-on-surface)] bg-white">
        <!-- Left Side: Branding Banner (Visible on Desktop) -->
        <div class="relative hidden md:flex md:w-1/2 flex-col justify-between bg-white border-r border-slate-100 p-12 text-slate-800 overflow-hidden">
            <!-- Decorative Background Grid & Gradients -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.15),transparent_60%)]"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-[color:var(--color-primary)]/10 rounded-full blur-3xl"></div>
            
            <!-- Top Navigation: Back to Home -->
            <div class="relative z-10">
                <Link :href="route('portal.home')" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-slate-900 font-semibold bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-lg border border-slate-200/80 transition shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke Portal Utama
                </Link>
            </div>

            <!-- Center Content: Logo & Brand Info -->
            <div class="relative z-10 my-auto max-w-md space-y-6">
                <div class="inline-flex items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-md w-20 h-20 mb-2">
                    <img :src="app === 'unit' ? '/logo.png' : '/logo2.png'" alt="Logo" class="w-14 h-14 object-contain" />
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight leading-tight text-slate-900">
                        {{ app === 'unit' ? 'Unit Simpan Pinjam' : 'BUMDES Dammar Wulan' }}
                    </h1>
                    <p class="text-blue-600 font-semibold mt-1 uppercase tracking-wider text-xs">
                        {{ app === 'unit' ? 'Layanan Keuangan Mikro' : 'Portal BUMDES Terintegrasi' }}
                    </p>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">
                    {{ app === 'unit' 
                        ? 'Sistem informasi pengelolaan layanan keuangan mikro, tabungan, dan pinjaman untuk masyarakat Desa Ciwuni.'
                        : 'Akses terpadu sistem informasi unit usaha BUMDES Dammar Wulan Desa Ciwuni, termasuk layanan keuangan mikro Simpan Pinjam, air bersih KP-SPAMS, toko desa, dan manajemen eksekutif.' 
                    }}
                </p>
            </div>

            <!-- Bottom: Footer Info -->
            <div class="relative z-10 text-xs text-slate-400">
                <p>© 2026 BUMDES Dammar Wulan • Desa Ciwuni</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex w-full md:w-1/2 flex-col justify-center bg-white px-6 py-8 md:px-12 lg:px-20 min-h-screen">
            <div class="mx-auto w-full max-w-md space-y-6">
                
                <!-- Logo & Navigation for Mobile screens only -->
                <div class="md:hidden flex flex-col items-center text-center space-y-3 mb-4">
                    <Link :href="route('portal.home')" class="self-start inline-flex items-center gap-1 text-xs text-[color:var(--color-primary)] hover:underline font-semibold bg-white px-3 py-1.5 rounded-lg border border-[color:var(--color-outline-variant)] shadow-sm">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        Kembali ke Portal
                    </Link>
                    
                    <img :src="app === 'unit' ? '/logo.png' : '/logo2.png'" alt="Logo" class="h-14 w-14 object-contain" />
                    <div>
                        <h1 class="text-xl font-bold">{{ app === 'unit' ? 'Unit Simpan Pinjam' : 'BUMDES Dammar Wulan' }}</h1>
                        <p class="text-xs text-[color:var(--color-secondary)] uppercase tracking-wider font-semibold">{{ app === 'unit' ? 'Layanan Keuangan Mikro' : 'Portal Terintegrasi' }}</p>
                    </div>
                </div>

                <!-- Header Greeting -->
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Selamat Datang</h2>
                    <p class="mt-1 text-sm text-[color:var(--color-on-surface-variant)]">
                        {{ app === 'unit' ? 'Masuk untuk mengakses layanan keuangan unit Simpan Pinjam.' : 'Masuk untuk mengelola portal dan unit usaha secara aman.' }}
                    </p>
                </div>

                <div v-if="status" class="text-sm font-medium text-[color:var(--color-success)]">
                    {{ status }}
                </div>

                <!-- Form Section -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Role Selection -->
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="flex flex-col items-center justify-center rounded-lg border px-3 py-2.5 text-xs font-semibold transition"
                            :class="roleButtonClass('admin')"
                            @click="selectedRole = 'admin'"
                        >
                            <span
                                class="material-symbols-outlined mb-1 text-lg"
                                :style="{ fontVariationSettings: roleIconFill('admin') }"
                            >
                                admin_panel_settings
                            </span>
                            Administrator
                        </button>
                        <button
                            type="button"
                            class="flex flex-col items-center justify-center rounded-lg border px-3 py-2.5 text-xs font-semibold transition"
                            :class="roleButtonClass('manager')"
                            @click="selectedRole = 'manager'"
                        >
                            <span
                                class="material-symbols-outlined mb-1 text-lg"
                                :style="{ fontVariationSettings: roleIconFill('manager') }"
                            >
                                person_check
                            </span>
                            Manager
                        </button>
                    </div>

                    <!-- Email field -->
                    <div>
                        <label class="mb-1 ml-1 block text-xs font-semibold text-[color:var(--color-on-surface-variant)]">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[color:var(--color-on-surface-variant)]"
                            >
                                alternate_email
                            </span>
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="Masukkan email"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-3 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                                required
                                autofocus
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password field -->
                    <div>
                        <div class="mb-1 ml-1 flex items-center justify-between">
                            <label class="text-xs font-semibold text-[color:var(--color-on-surface-variant)]">
                                Kata Sandi
                            </label>
                            <span class="text-xs text-[color:var(--color-primary)] cursor-pointer hover:underline">Lupa Sandi?</span>
                        </div>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[color:var(--color-on-surface-variant)]"
                            >
                                lock
                            </span>
                            <input
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-3 pl-10 pr-12 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                                required
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[color:var(--color-on-surface-variant)]"
                                @click="togglePassword"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ passwordVisible ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <label class="flex items-center gap-2 text-sm text-[color:var(--color-on-surface-variant)] cursor-pointer">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-[color:var(--color-outline-variant)] text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]"
                        />
                        Ingat saya di perangkat ini
                    </label>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-[color:var(--color-primary)] py-3.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98] hover:bg-blue-800"
                        :class="{ 'opacity-70': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>Masuk ke Sistem</span>
                        <span class="material-symbols-outlined text-lg">login</span>
                    </button>
                </form>

                <!-- Help & Security Indicators -->
                <div class="text-center text-xs text-[color:var(--color-secondary)]">
                    <p>Butuh bantuan? Hubungi <span class="font-semibold text-[color:var(--color-primary)] cursor-pointer hover:underline">Support Desa</span></p>
                    <div class="mt-4 flex items-center justify-center gap-4 border-t border-[color:var(--color-outline-variant)] pt-4">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">verified_user</span>
                            <span>SSL Secured</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">language</span>
                            <span>Bahasa Indonesia</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
