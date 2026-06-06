<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ summary: Object, filters: Object });
const bulan = ref(props.filters?.bulan ?? '');

const formatBulanLabel = (val) => {
    if (!val) return '';
    const [y, m] = val.split('-');
    return new Date(y, parseInt(m) - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

let timeout;
watch(bulan, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = {};
        if (bulan.value) {
            const [y, m] = bulan.value.split('-');
            const lastDay = new Date(y, m, 0).getDate();
            params.start_date = `${y}-${m}-01`;
            params.end_date   = `${y}-${m}-${lastDay}`;
            params.bulan      = bulan.value;
        }
        router.get(route('laporan.kas'), params, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const buildQuery = computed(() => {
    if (!bulan.value) return '';
    const [y, m] = bulan.value.split('-');
    const lastDay = new Date(y, m, 0).getDate();
    return new URLSearchParams({ start_date: `${y}-${m}-01`, end_date: `${y}-${m}-${lastDay}`, bulan: bulan.value }).toString();
});
const pdfUrl = computed(() => `${route('laporan.kas.pdf')}?${buildQuery.value}`);
</script>

<template>
    <Head title="Laporan Kas" />
    <AuthenticatedLayout>
        <template #header>Laporan Kas</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Kas</h2>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row items-center">
                    <label class="text-sm font-medium text-[color:var(--color-secondary)]">Filter Bulan:</label>
                    <input v-model="bulan" type="month"
                        class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    <button v-if="bulan" @click="bulan = ''" class="text-xs text-[color:var(--color-secondary)] hover:text-red-500">✕ Reset</button>
                    <span v-if="bulan" class="text-sm font-semibold text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }}</span>
                </div>
                <ExportButtons :pdf-url="pdfUrl" />
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase text-emerald-600">Uang Masuk (Tabungan)</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-800">{{ formatCurrency(summary.masuk_tabungan) }}</p>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <p class="text-xs font-semibold uppercase text-blue-600">Uang Masuk (Angsuran)</p>
                    <p class="mt-2 text-2xl font-bold text-blue-800">{{ formatCurrency(summary.masuk_angsuran) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-primary)]/20 bg-[color:var(--color-primary)]/5 p-5">
                    <p class="text-xs font-semibold uppercase text-[color:var(--color-primary)]">Total Masuk</p>
                    <p class="mt-2 text-2xl font-bold text-[color:var(--color-primary)]">{{ formatCurrency(summary.total_masuk) }}</p>
                </div>
                <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                    <p class="text-xs font-semibold uppercase text-red-600">Total Keluar (Penarikan)</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">{{ formatCurrency(summary.total_keluar) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                    <p class="text-xs font-semibold uppercase text-slate-600">Saldo Kas Bersih</p>
                    <p class="mt-2 text-3xl font-bold" :class="summary.saldo_kas >= 0 ? 'text-emerald-700' : 'text-red-700'">
                        {{ formatCurrency(summary.saldo_kas) }}
                    </p>
                </div>
            </div>

            <!-- Detail Breakdown -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h3 class="mb-4 font-semibold">Rincian Arus Kas</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-emerald-600">+ Setoran Tabungan (Reguler + Sembako)</span>
                        <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.masuk_tabungan) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-blue-600">+ Pembayaran Angsuran Pinjaman</span>
                        <span class="font-semibold text-blue-600">{{ formatCurrency(summary.masuk_angsuran) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-red-600">- Penarikan Tabungan + Administrasi</span>
                        <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_tabungan) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 font-bold text-base">
                        <span>Saldo Kas Bersih</span>
                        <span :class="summary.saldo_kas >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(summary.saldo_kas) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
