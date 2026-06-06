<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ pinjaman: Array, filters: Object, summary: Object });
const bulan  = ref(props.filters?.bulan  ?? '');
const status = ref(props.filters?.status ?? '');

const formatBulanLabel = (val) => {
    if (!val) return '';
    const [y, m] = val.split('-');
    return new Date(y, parseInt(m) - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

let timeout;
watch([bulan, status], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = { status: status.value };
        if (bulan.value) {
            const [y, m] = bulan.value.split('-');
            const lastDay = new Date(y, m, 0).getDate();
            params.start_date = `${y}-${m}-01`;
            params.end_date   = `${y}-${m}-${lastDay}`;
            params.bulan      = bulan.value;
        }
        router.get(route('laporan.pinjaman'), params, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const statusColor = {
    aktif: 'bg-emerald-50 text-emerald-700',
    lunas: 'bg-gray-100 text-gray-600',
    menunggak: 'bg-orange-50 text-orange-700',
    'perhatian-khusus': 'bg-yellow-50 text-yellow-700',
    'kredit-macet': 'bg-red-50 text-red-700',
};

const buildQuery = computed(() => {
    const q = { status: status.value };
    if (bulan.value) {
        const [y, m] = bulan.value.split('-');
        const lastDay = new Date(y, m, 0).getDate();
        q.start_date = `${y}-${m}-01`;
        q.end_date   = `${y}-${m}-${lastDay}`;
        q.bulan      = bulan.value;
    }
    return new URLSearchParams(q).toString();
});
const pdfUrl   = computed(() => `${route('laporan.pinjaman.pdf')}?${buildQuery.value}`);
const excelUrl = computed(() => `${route('laporan.pinjaman.excel')}?${buildQuery.value}`);
</script>

<template>
    <Head title="Laporan Pinjaman" />
    <AuthenticatedLayout>
        <template #header>Laporan Pinjaman</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Pinjaman</h2>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm col-span-2 sm:col-span-1">
                    <p class="text-xs uppercase text-[color:var(--color-secondary)]">Total</p>
                    <p class="mt-1 text-xl font-bold">{{ summary.total }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                    <p class="text-xs uppercase text-emerald-600">Aktif</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">{{ summary.aktif }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-slate-600">Lunas</p>
                    <p class="mt-1 text-xl font-bold text-slate-700">{{ summary.lunas }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-[color:var(--color-secondary)]">Total Pokok</p>
                    <p class="mt-1 text-sm font-bold">{{ formatCurrency(summary.total_pokok) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-[color:var(--color-secondary)]">Total Tagihan</p>
                    <p class="mt-1 text-sm font-bold">{{ formatCurrency(summary.total_tagihan) }}</p>
                </div>
                <div class="rounded-xl border border-red-100 bg-red-50 p-4 text-center">
                    <p class="text-xs uppercase text-red-600">Total Sisa</p>
                    <p class="mt-1 text-sm font-bold text-red-700">{{ formatCurrency(summary.total_sisa) }}</p>
                </div>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row items-center">
                    <label class="text-sm font-medium text-[color:var(--color-secondary)]">Filter Bulan:</label>
                    <input v-model="bulan" type="month"
                        class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    <button v-if="bulan" @click="bulan = ''" class="text-xs text-[color:var(--color-secondary)] hover:text-red-500">✕ Reset</button>
                    <span v-if="bulan" class="text-sm font-semibold text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }}</span>
                    <select v-model="status" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <ExportButtons :pdf-url="pdfUrl" :excel-url="excelUrl" />
            </div>

            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-white">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nasabah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Tanggal Akad</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Pokok</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Tagihan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Sisa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="pinjaman.length === 0"><td colspan="6" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Tidak ada data</td></tr>
                            <tr v-for="row in pinjaman" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ row.nasabah?.nama }}</p>
                                    <p class="font-mono text-xs text-[color:var(--color-secondary)]">{{ row.nasabah?.nomor_rekening }}</p>
                                </td>
                                <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal_akad) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(row.pinjaman_pokok) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(row.total_tagihan) }}</td>
                                <td class="px-4 py-3 text-right" :class="Number(row.sisa_pinjaman) > 0 ? 'text-red-600' : 'text-emerald-600'">{{ formatCurrency(row.sisa_pinjaman) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor[row.computed_status] ?? statusColor[row.status]">{{ row.computed_status ?? row.status }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
