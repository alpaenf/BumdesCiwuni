<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    status: { type: String },
});

const form = useForm({
    email: 'perdagangan@bumdes.com',
    password: '',
    remember: false,
    role: 'admin',
});

const selectedRole = ref('admin');
const passwordVisible = ref(false);

const togglePassword = () => {
    passwordVisible.value = !passwordVisible.value;
};

const selectRole = (role) => {
    selectedRole.value = role;
    form.role = role;
    if (role === 'admin' && !form.email) {
        form.email = 'perdagangan@bumdes.com';
    }
};

const roleButtonClass = (role) =>
    selectedRole.value === role
        ? 'border-[color:var(--color-primary)] bg-blue-50/80 text-[color:var(--color-primary)] shadow-sm'
        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';

const roleIconFill = (role) => (selectedRole.value === role ? '"FILL" 1' : '"FILL" 0');

const submit = () => {
    form.post(route('unit.login', { slug: props.unit?.slug || 'perdagangan-besar' }), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Admin Unit Perdagangan Besar" />

    <div class="flex min-h-screen w-full flex-col md:flex-row text-[color:var(--color-on-surface)] bg-white font-sans">
        <!-- Left Side: Branding Banner (Visible on Desktop) -->
        <div class="relative hidden md:flex md:w-1/2 flex-col justify-between bg-white border-r border-slate-100 p-12 text-slate-800 overflow-hidden">
            <!-- Decorative Background Grid & Gradients matching Simpan Pinjam -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(30,58,138,0.10),transparent_60%)] pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-[color:var(--color-primary)]/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Top Navigation: Back to Landing Page -->
            <div class="relative z-10">
                <Link :href="route('unit.welcome', { slug: 'perdagangan-besar' })" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-slate-900 font-semibold bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-lg border border-slate-200/80 transition shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke Landing Page
                </Link>
            </div>

            <!-- Center Content: Logo & Brand Info -->
            <div class="relative z-10 my-auto max-w-md space-y-6">
                <div class="inline-flex items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-md w-20 h-20 mb-2">
                    <img src="/logo.png" alt="Logo BUMDes" class="w-14 h-14 object-contain" />
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight leading-tight text-slate-900">
                        Unit Perdagangan Besar
                    </h1>
                    <p class="text-[color:var(--color-primary)] font-semibold mt-1 uppercase tracking-wider text-xs">
                        Layanan Transaksi & Keuangan Digital Desa
                    </p>
                </div>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Sistem informasi terpadu pengelolaan layanan perbankan Laku Pandai, Samsat Budiman, Pajak PBB, dan Transaksi Umum PPOB BUMDes Dammar Wulan Desa Ciwuni.
                </p>

                <!-- Service Highlights Pills -->
                <div class="grid grid-cols-2 gap-2.5 pt-2">
                    <div class="flex items-center gap-2.5 p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs font-medium text-slate-700">
                        <span class="material-symbols-outlined text-blue-600 text-lg">account_balance</span>
                        <span>Laku Pandai</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs font-medium text-slate-700">
                        <span class="material-symbols-outlined text-amber-600 text-lg">directions_car</span>
                        <span>Samsat Budiman</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs font-medium text-slate-700">
                        <span class="material-symbols-outlined text-emerald-600 text-lg">receipt_long</span>
                        <span>Pajak PBB</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs font-medium text-slate-700">
                        <span class="material-symbols-outlined text-indigo-600 text-lg">point_of_sale</span>
                        <span>Transaksi PPOB</span>
                    </div>
                </div>
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
                    <Link :href="route('unit.welcome', { slug: 'perdagangan-besar' })" class="self-start inline-flex items-center gap-1 text-xs text-[color:var(--color-primary)] hover:underline font-semibold bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        Kembali ke Landing Page
                    </Link>
                    
                    <img src="/logo.png" alt="Logo" class="h-14 w-14 object-contain" />
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Unit Perdagangan Besar</h1>
                        <p class="text-xs text-[color:var(--color-primary)] uppercase tracking-wider font-semibold">Layanan Keuangan & PPOB</p>
                    </div>
                </div>

                <!-- Header Greeting -->
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Selamat Datang</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Masuk untuk mengakses layanan operasional dan transaksi Unit Perdagangan Besar.
                    </p>
                </div>

                <div v-if="status" class="text-sm font-medium text-emerald-600 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                    {{ status }}
                </div>

                <!-- Role Selection -->
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="flex flex-col items-center justify-center rounded-lg border px-3 py-2.5 text-xs font-semibold transition"
                        :class="roleButtonClass('admin')"
                        @click="selectRole('admin')"
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
                        @click="selectRole('manager')"
                    >
                        <span
                            class="material-symbols-outlined mb-1 text-lg"
                            :style="{ fontVariationSettings: roleIconFill('manager') }"
                        >
                            person_check
                        </span>
                        Petugas / Kasir
                    </button>
                </div>

                <!-- Form Section -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email field -->
                    <div>
                        <label class="mb-1 ml-1 block text-xs font-semibold text-slate-700">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                alternate_email
                            </span>
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="perdagangan@bumdes.com"
                                class="w-full rounded-lg border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20 transition"
                                required
                                autofocus
                            />
                        </div>
                        <InputError class="mt-2 text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Password field -->
                    <div>
                        <div class="mb-1 ml-1 flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700">
                                Kata Sandi
                            </label>
                            <span class="text-xs text-[color:var(--color-primary)] cursor-pointer hover:underline">Lupa Sandi?</span>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                lock
                            </span>
                            <input
                                v-model="form.password"
                                :type="passwordVisible ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi"
                                class="w-full rounded-lg border border-slate-200 bg-white py-3 pl-10 pr-12 text-sm text-slate-800 placeholder-slate-400 focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20 transition"
                                required
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
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
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]"
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

                <!-- Akun Default Uji Coba Info Card -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-center">
                    <p class="text-xs text-slate-500">
                        Akun Uji Coba: <span class="font-bold text-slate-700">perdagangan@bumdes.com</span> • Sandi: <span class="font-bold text-slate-700">password</span>
                    </p>
                </div>

                <!-- Help & Security Indicators -->
                <div class="text-center text-xs text-slate-500">
                    <p>Butuh bantuan? Hubungi <span class="font-semibold text-[color:var(--color-primary)] cursor-pointer hover:underline">Support Desa</span></p>
                    <div class="mt-4 flex items-center justify-center gap-4 border-t border-slate-100 pt-4 text-slate-400">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-base text-emerald-600">verified_user</span>
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
