<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ summary: Object, filters: Object });
const startDate = ref(props.filters?.start_date ?? '');
const endDate = ref(props.filters?.end_date ?? '');

let timeout;
const applyFilter = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = {};
        if (startDate.value) params.start_date = startDate.value;
        if (endDate.value) params.end_date = endDate.value;
        router.get(route('laporan.kas'), params, { preserveState: true, replace: true });
    }, 400);
};

watch([startDate, endDate], applyFilter);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const buildQuery = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    return params.toString();
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

            <!-- Total Saldo Keseluruhan (Sepanjang Waktu) -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Total Akumulasi Keseluruhan (Sepanjang Waktu)</h3>
                        <p class="text-xs text-[color:var(--color-secondary)]">Akumulasi total dari awal berdirinya sistem (mengabaikan filter tanggal)</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase mb-1">Total Saldo Reguler</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_reguler) }}</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 bg-orange-50/50 p-4">
                        <p class="text-xs font-semibold text-orange-700 uppercase mb-1">Total Saldo Sembako</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_sembako) }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 p-4">
                        <p class="text-xs font-semibold text-emerald-700 uppercase mb-1">Total Angsuran Masuk</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.total_angsuran_all) }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-100 bg-rose-50/50 p-4">
                        <p class="text-xs font-semibold text-rose-700 uppercase mb-1">Total Pinjaman Disalurkan</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.total_pinjaman_all) }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-[color:var(--color-secondary)] mb-1">Mulai Tanggal</label>
                        <input v-model="startDate" type="date"
                            class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-[color:var(--color-secondary)] mb-1">Sampai Tanggal</label>
                        <input v-model="endDate" type="date"
                            class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div class="flex items-end h-full pt-5">
                         <button v-if="startDate || endDate" @click="startDate = ''; endDate = ''" class="text-xs font-semibold text-red-600 hover:text-red-800">✕ Reset Filter</button>
                    </div>
                </div>
                <div class="pt-5">
                     <ExportButtons :pdf-url="pdfUrl" />
                </div>
            </div>

            <!-- Detail Breakdown -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                        <span class="material-symbols-outlined text-xl">analytics</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Arus Kas (Berdasarkan Filter Tanggal)</h3>
                        <p class="text-xs text-[color:var(--color-secondary)]">Rincian uang masuk dan keluar pada periode yang dipilih</p>
                    </div>
                </div>
                <div class="space-y-4 text-sm mt-4 border-t border-[color:var(--color-outline-variant)] pt-4">
                    <!-- Pemasukan -->
                    <div>
                        <h4 class="font-bold text-emerald-700 mb-2 border-b border-emerald-100 pb-1">Total Uang Masuk</h4>
                        <div class="space-y-2 pl-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">+ Setoran Tabungan Reguler</span>
                                <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.masuk_reguler) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">+ Setoran Tabungan Sembako</span>
                                <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.masuk_sembako) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">+ Pembayaran Angsuran Pinjaman (Masuk)</span>
                                <span class="font-semibold text-emerald-600">{{ formatCurrency(summary.masuk_angsuran) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-emerald-800 bg-emerald-50 p-2 mt-2 rounded">
                            <span>Subtotal Pemasukan</span>
                            <span>{{ formatCurrency(summary.total_masuk) }}</span>
                        </div>
                    </div>

                    <!-- Pengeluaran -->
                    <div class="pt-4">
                        <h4 class="font-bold text-red-700 mb-2 border-b border-red-100 pb-1">Total Uang Keluar</h4>
                        <div class="space-y-2 pl-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600">- Penarikan Tabungan Reguler</span>
                                <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_reguler) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">- Penarikan Tabungan Sembako</span>
                                <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_sembako) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">- Pencairan Dana Pinjaman Baru (Keluar)</span>
                                <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_pinjaman) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-red-800 bg-red-50 p-2 mt-2 rounded">
                            <span>Subtotal Pengeluaran</span>
                            <span>{{ formatCurrency(summary.total_keluar) }}</span>
                        </div>
                    </div>

                    <!-- Laba/Rugi Kas -->
                    <div class="flex justify-between pt-4 font-bold text-xl border-t border-[color:var(--color-outline-variant)]">
                        <span class="text-slate-800">Saldo Kas Bersih (Periode Ini)</span>
                        <span :class="summary.saldo_kas >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(summary.saldo_kas) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
