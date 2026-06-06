<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    units: { type: Array, required: true },
    roles: { type: Array, default: () => [] },
});

const form = useForm({
    nama: '',
    email: '',
    role: 'admin_unit',
    unit_id: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('portal.cms.users.store'));
};
</script>

<template>
    <Head title="Tambah Pengurus - CMS BUMDes" />

    <PortalLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('portal.cms.users.index')" class="text-slate-400 hover:text-emerald-600 transition">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </Link>
                <span>Tambah Pengurus</span>
            </div>
        </template>

        <div class="max-w-3xl">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Informasi Akun Pengurus</h3>
                    <p class="text-xs text-slate-500 mt-1">Buat akun untuk pengurus BUMDes pusat atau unit usaha.</p>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input v-model="form.nama" type="text" required class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: Budi Santoso" />
                            <p v-if="form.errors.nama" class="mt-1 text-xs text-rose-500">{{ form.errors.nama }}</p>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                            <input v-model="form.email" type="email" required class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: budi@bumdes.com" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10" placeholder="Minimal 8 karakter" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-[18px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-500">{{ form.errors.password }}</p>
                        </div>
                        
                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" required class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10" placeholder="Ulangi password" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 my-4 pt-5"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Peran (Role) <span class="text-rose-500">*</span></label>
                            <select v-model="form.role" class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                            <p class="mt-1.5 text-[11px] text-slate-500 leading-relaxed">
                                <strong>Admin Unit:</strong> Akses penuh ke operasional unit simpan pinjam.<br>
                                <strong>Manager Unit:</strong> Akses terbatas, tidak bisa mengubah pengaturan.
                            </p>
                            <p v-if="form.errors.role" class="mt-1 text-xs text-rose-500">{{ form.errors.role }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit Usaha (Kosongkan untuk Pusat)</label>
                            <select v-model="form.unit_id" class="w-full border-slate-300 rounded-lg text-sm focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                <option value="">-- Pengurus Pusat (Tanpa Unit) --</option>
                                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                    {{ unit.nama_unit }}
                                </option>
                            </select>
                            <p class="mt-1.5 text-[11px] text-slate-500">Kosongkan jika ini adalah Super Admin atau Manager Pusat.</p>
                            <p v-if="form.errors.unit_id" class="mt-1 text-xs text-rose-500">{{ form.errors.unit_id }}</p>
                        </div>
                    </div>

                    <div class="pt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <Link :href="route('portal.cms.users.index')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition text-center w-full sm:w-auto">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center justify-center gap-2 w-full sm:w-auto">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PortalLayout>
</template>
