<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    biaya: Object,
    flash: Object,
});

const form = useForm({
    biaya_admin_sembako:       Number(props.biaya?.biaya_admin_sembako ?? 20000),
    biaya_admin_tarik_reguler: Number(props.biaya?.biaya_admin_tarik_reguler ?? 0),
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

function submit() {
    form.put(route('pengaturan.biaya.update'));
}
</script>

<template>
    <Head title="Pengaturan Biaya" />
    <AuthenticatedLayout>
        <template #header>Pengaturan Biaya Administrasi</template>

        <div class="mx-auto max-w-xl space-y-5">

            <!-- Flash -->
            <div v-if="flash?.success" class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                {{ flash.success }}
            </div>

            <!-- Card -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-base font-semibold text-slate-800">Biaya Administrasi</h2>
                    <p class="mt-1 text-xs text-[color:var(--color-secondary)]">
                        Atur biaya administrasi yang otomatis dikenakan saat transaksi. Nilai 0 berarti tidak ada biaya administrasi.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Biaya Admin Sembako -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                                <span class="material-symbols-outlined text-xl text-orange-500">shopping_basket</span>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-slate-800">
                                    Biaya Admin Pengambilan Sembako
                                </label>
                                <p class="mt-0.5 text-xs text-[color:var(--color-secondary)]">
                                    Dikenakan otomatis setiap kali nasabah mengambil tabungan sembako.
                                </p>
                                <div class="mt-3 relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                                    <input v-model="form.biaya_admin_sembako"
                                        type="number" min="0" step="500"
                                        class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                        :class="form.errors.biaya_admin_sembako ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                                </div>
                                <p v-if="form.errors.biaya_admin_sembako" class="mt-1 text-xs text-red-500">{{ form.errors.biaya_admin_sembako }}</p>
                                <p class="mt-1.5 text-xs text-slate-400">Saat ini: <strong>{{ formatCurrency(props.biaya?.biaya_admin_sembako ?? 20000) }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Biaya Admin Tarik Reguler -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                                <span class="material-symbols-outlined text-xl text-emerald-600">savings</span>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-slate-800">
                                    Biaya Admin Default Penarikan Tabungan Reguler
                                </label>
                                <p class="mt-0.5 text-xs text-[color:var(--color-secondary)]">
                                    Nilai default yang muncul di form penarikan tabungan reguler. Petugas masih bisa mengubah nilainya secara manual.
                                </p>
                                <div class="mt-3 relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                                    <input v-model="form.biaya_admin_tarik_reguler"
                                        type="number" min="0" step="500"
                                        class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                        :class="form.errors.biaya_admin_tarik_reguler ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                                </div>
                                <p v-if="form.errors.biaya_admin_tarik_reguler" class="mt-1 text-xs text-red-500">{{ form.errors.biaya_admin_tarik_reguler }}</p>
                                <p class="mt-1.5 text-xs text-slate-400">Saat ini: <strong>{{ formatCurrency(props.biaya?.biaya_admin_tarik_reguler ?? 0) }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="flex items-start gap-2 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-xs text-blue-700">
                        <span class="material-symbols-outlined text-base shrink-0 mt-0.5">info</span>
                        <span>Perubahan biaya administrasi akan langsung berlaku untuk transaksi berikutnya. Transaksi yang sudah tercatat tidak akan terpengaruh.</span>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-6 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60 transition">
                            <span class="material-symbols-outlined text-base">save</span>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
