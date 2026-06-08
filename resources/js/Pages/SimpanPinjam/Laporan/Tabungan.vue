<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ transaksi: Array, jenis: String, filters: Object, summary: Object });
const bulan = ref(props.filters?.bulan ?? '');
const jenis = ref(props.filters?.jenis ?? 'reguler');

const formatBulanLabel = (val) => {
    if (!val) return '';
    const [y, m] = val.split('-');
    return new Date(y, parseInt(m) - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

let timeout;
watch([bulan, jenis], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = { jenis: jenis.value };
        if (bulan.value) {
            const [y, m] = bulan.value.split('-');
            const lastDay = new Date(y, m, 0).getDate();
            params.start_date = `${y}-${m}-01`;
            params.end_date   = `${y}-${m}-${lastDay}`;
            params.bulan      = bulan.value;
        }
        router.get(route('laporan.tabungan'), params, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const buildQuery = computed(() => {
    const q = { jenis: jenis.value };
    if (bulan.value) {
        const [y, m] = bulan.value.split('-');
        const lastDay = new Date(y, m, 0).getDate();
        q.start_date = `${y}-${m}-01`;
        q.end_date   = `${y}-${m}-${lastDay}`;
        q.bulan      = bulan.value;
    }
    return new URLSearchParams(q).toString();
});
const pdfUrl   = computed(() => `${route('laporan.tabungan.pdf')}?${buildQuery.value}`);
const excelUrl = computed(() => `${route('laporan.tabungan.excel')}?${buildQuery.value}`);
</script>

<template>
    <Head title="Laporan Tabungan" />
    <AuthenticatedLayout>
        <template #header>Laporan Tabungan</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Tabungan</h2>
            </div>

            <!-- Summary -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center">
                    <p class="text-xs uppercase text-blue-600">Total Setoran</p>
                    <p class="mt-1 text-lg font-bold text-blue-700">{{ formatCurrency(summary.total_setoran) }}</p>
                </div>
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                    <p class="text-xs uppercase text-red-600">Total Penarikan</p>
                    <p class="mt-1 text-lg font-bold text-red-700">{{ formatCurrency(summary.total_penarikan) }}</p>
                </div>
                <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 text-center">
                    <p class="text-xs uppercase text-orange-600">Total Administrasi</p>
                    <p class="mt-1 text-lg font-bold text-orange-700">{{ formatCurrency(summary.total_admin) }}</p>
                </div>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row items-center">
                    <div class="flex rounded-lg border border-[color:var(--color-outline-variant)] overflow-hidden">
                        <button @click="jenis = 'reguler'" class="px-4 py-2.5 text-sm font-semibold transition"
                            :class="jenis === 'reguler' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-white text-[color:var(--color-secondary)] hover:bg-[color:var(--color-surface-container-low)]'">
                            Reguler
                        </button>
                        <button @click="jenis = 'sembako'" class="px-4 py-2.5 text-sm font-semibold transition"
                            :class="jenis === 'sembako' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-white text-[color:var(--color-secondary)] hover:bg-[color:var(--color-surface-container-low)]'">
                            Sembako
                        </button>
                    </div>
                    <label class="text-sm font-medium text-[color:var(--color-secondary)]">Filter Bulan:</label>
                    <input v-model="bulan" type="month"
                        class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    <button v-if="bulan" @click="bulan = ''" class="text-xs text-[color:var(--color-secondary)] hover:text-red-500">✕ Reset</button>
                    <span v-if="bulan" class="text-sm font-semibold text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }}</span>
                </div>
                <ExportButtons :pdf-url="pdfUrl" :excel-url="excelUrl" />
            </div>

            <div class="overflow-x-auto rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">No. Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nasabah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Jenis</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nominal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Admin</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                        <tr v-if="transaksi.length === 0"><td colspan="7" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Tidak ada data</td></tr>
                        <tr v-for="row in transaksi" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                            <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal) }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-[color:var(--color-primary)]">{{ row.nomor_transaksi }}</td>
                            <td class="px-4 py-3 font-semibold">{{ row.tabungan?.nasabah?.nama }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="row.jenis_transaksi === 'setor' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700'">
                                    {{ row.jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold" :class="row.jenis_transaksi === 'setor' ? 'text-blue-600' : 'text-red-600'">{{ formatCurrency(row.nominal) }}</td>
                            <td class="px-4 py-3 text-right text-orange-600">{{ formatCurrency(row.administrasi) }}</td>
                            <td class="px-4 py-3 text-right">{{ formatCurrency(row.saldo_setelah) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
