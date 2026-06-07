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
        <template #header>Laporan Kas & Rekap Saldo</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Kas & Rekap Saldo</h2>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row items-center">
                    <label class="text-sm font-medium text-[color:var(--color-secondary)]">Filter Bulan / Hari:</label>
                    <input v-model="bulan" type="month"
                        class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    <button v-if="bulan" @click="bulan = ''" class="text-xs text-[color:var(--color-secondary)] hover:text-red-500">✕ Reset</button>
                    <span v-if="bulan" class="text-sm font-semibold text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }}</span>
                </div>
                <ExportButtons :pdf-url="pdfUrl" />
            </div>

            <!-- Total Saldo Keseluruhan (Realtime) -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Total Saldo Simpanan Nasabah</h3>
                        <p class="text-xs text-[color:var(--color-secondary)]">Akumulasi seluruh tabungan yang mengendap saat ini</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase mb-1">Tabungan Reguler</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_reguler) }}</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 bg-orange-50/50 p-4">
                        <p class="text-xs font-semibold text-orange-700 uppercase mb-1">Tabungan Sembako</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_sembako) }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-600 uppercase mb-1">Total Keseluruhan</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.total_saldo_all) }}</p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards Arus Kas (Berdasarkan Filter) -->
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
                    <p class="text-xs font-semibold uppercase text-[color:var(--color-primary)]">Total Masuk (Periode Ini)</p>
                    <p class="mt-2 text-2xl font-bold text-[color:var(--color-primary)]">{{ formatCurrency(summary.total_masuk) }}</p>
                </div>
                <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                    <p class="text-xs font-semibold uppercase text-red-600">Total Keluar (Penarikan Tabungan)</p>
                    <p class="mt-2 text-2xl font-bold text-red-700">{{ formatCurrency(summary.keluar_tabungan) }}</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-xs font-semibold uppercase text-amber-700">Total Keluar (Pencairan Pinjaman)</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800">{{ formatCurrency(summary.keluar_pinjaman) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-slate-600">Total Uang Keluar (Periode Ini)</p>
                    <p class="mt-2 text-2xl font-bold text-slate-800">{{ formatCurrency(summary.total_keluar) }}</p>
                </div>
            </div>

            <!-- Detail Breakdown -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h3 class="mb-4 font-semibold">Rincian Arus Kas Bersih (Periode Ini)</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-emerald-600">+ Setoran Tabungan (Reguler + Sembako)</span>
                        <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.masuk_tabungan) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-blue-600">+ Pembayaran Angsuran Pinjaman Masuk</span>
                        <span class="font-semibold text-blue-600">{{ formatCurrency(summary.masuk_angsuran) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-red-600">- Penarikan Tabungan + Administrasi Keluar</span>
                        <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_tabungan) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[color:var(--color-outline-variant)] pb-3">
                        <span class="text-amber-700">- Pencairan Pinjaman Baru (Dana Keluar)</span>
                        <span class="font-semibold text-amber-700">{{ formatCurrency(summary.keluar_pinjaman) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 font-bold text-lg">
                        <span>Saldo Kas Bersih (Periode Ini)</span>
                        <span :class="summary.saldo_kas >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(summary.saldo_kas) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
