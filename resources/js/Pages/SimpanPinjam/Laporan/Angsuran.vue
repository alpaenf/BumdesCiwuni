<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ angsuran: Array, filters: Object, summary: Object });
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
        router.get(route('laporan.angsuran'), params, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const pasaranLabel = { legi: 'Legi', pahing: 'Pahing', pon: 'Pon', wage: 'Wage', kliwon: 'Kliwon' };

const buildQuery = computed(() => {
    if (!bulan.value) return '';
    const [y, m] = bulan.value.split('-');
    const lastDay = new Date(y, m, 0).getDate();
    return new URLSearchParams({ start_date: `${y}-${m}-01`, end_date: `${y}-${m}-${lastDay}`, bulan: bulan.value }).toString();
});
const pdfUrl   = computed(() => `${route('laporan.angsuran.pdf')}?${buildQuery.value}`);
const excelUrl = computed(() => `${route('laporan.angsuran.excel')}?${buildQuery.value}`);
</script>

<template>
    <Head title="Laporan Angsuran" />
    <AuthenticatedLayout>
        <template #header>Laporan Angsuran</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Angsuran</h2>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-[color:var(--color-secondary)]">Total Transaksi</p>
                    <p class="mt-1 text-2xl font-bold">{{ summary.total_transaksi }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                    <p class="text-xs uppercase text-emerald-600">Total Bayar</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">{{ formatCurrency(summary.total_bayar) }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-3 items-center">
                    <label class="text-sm font-medium text-[color:var(--color-secondary)]">Filter Bulan:</label>
                    <input v-model="bulan" type="month"
                        class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    <button v-if="bulan" @click="bulan = ''" class="text-xs text-[color:var(--color-secondary)] hover:text-red-500">✕ Reset</button>
                    <span v-if="bulan" class="text-sm font-semibold text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }}</span>
                </div>
                <ExportButtons :pdf-url="pdfUrl" :excel-url="excelUrl" />
            </div>

            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nasabah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Ke-</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Hari Pasaran</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Jumlah Bayar</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Sisa Pinjaman</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                        <tr v-if="angsuran.length === 0"><td colspan="6" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Tidak ada data</td></tr>
                        <tr v-for="row in angsuran" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ row.pinjaman?.nasabah?.nama }}</p>
                                <p class="font-mono text-xs text-[color:var(--color-secondary)]">{{ row.pinjaman?.nasabah?.nomor_rekening }}</p>
                            </td>
                            <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal) }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ row.angsuran_ke }}</td>
                            <td class="px-4 py-3 capitalize text-[color:var(--color-secondary)]">{{ pasaranLabel[row.pasaran] ?? row.pasaran }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ formatCurrency(row.jumlah_bayar) }}</td>
                            <td class="px-4 py-3 text-right" :class="Number(row.sisa_pinjaman) <= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(row.sisa_pinjaman) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
