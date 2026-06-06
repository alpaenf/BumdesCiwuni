<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    nasabah: Object,
    tabungan: Object,
});

const form = useForm({
    nasabah_id: props.nasabah.id,
    tanggal:    new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0],
    nominal:    '',
    keterangan: '',
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const submit = () => {
    form.post(route('tabungan.setor.store', props.nasabah.id));
};
</script>

<template>
    <Head title="Setor Tabungan" />
    <AuthenticatedLayout>
        <template #header>Setor Tabungan</template>
        <div class="mx-auto max-w-lg">
            <Link :href="route('tabungan.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <!-- Info Nasabah -->
                <div class="mb-6 rounded-lg bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase text-emerald-600">Nasabah</p>
                    <p class="mt-1 font-bold text-emerald-900">{{ nasabah.nama }}</p>
                    <p class="font-mono text-sm text-emerald-700">{{ nasabah.nomor_rekening }}</p>
                    <p class="mt-2 text-sm text-emerald-700">Saldo Saat Ini: <span class="font-bold">{{ formatCurrency(tabungan.saldo) }}</span></p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal Setoran <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20" />
                        <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-500">{{ form.errors.tanggal }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nominal Setoran <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.nominal" type="number" min="1000" step="1000" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-primary)]/20"
                                :class="form.errors.nominal ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.nominal" class="mt-1 text-xs text-red-500">{{ form.errors.nominal }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Keterangan</label>
                        <input v-model="form.keterangan" type="text" placeholder="Setoran tabungan (opsional)"
                            class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                    </div>

                    <!-- Preview Saldo -->
                    <div v-if="form.nominal" class="rounded-lg bg-[color:var(--color-surface-container-low)] p-4 text-sm">
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Saldo Saat Ini</span><span>{{ formatCurrency(tabungan.saldo) }}</span></div>
                        <div class="flex justify-between text-emerald-600"><span>+ Setoran</span><span>{{ formatCurrency(form.nominal) }}</span></div>
                        <div class="mt-2 flex justify-between border-t border-[color:var(--color-outline-variant)] pt-2 font-bold"><span>Saldo Baru</span><span>{{ formatCurrency(Number(tabungan.saldo) + Number(form.nominal)) }}</span></div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('tabungan.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">add_circle</span> Simpan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
