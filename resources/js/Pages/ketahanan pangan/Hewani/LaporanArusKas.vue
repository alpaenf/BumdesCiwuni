<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    flows: Array,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('kp.report.arus-kas'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const printReport = () => {
    window.print();
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Arus Kas Hewani" :unit="unit" :user="user">
        <!-- Summary Cards Sesuai Format Laporan Arus Kas BUMDes Ciwuni -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Saldo Awal</p>
                <h4 class="text-xl font-bold text-slate-700 mt-1">{{ formatRp(summary.saldo_awal) }}</h4>
                <p class="mt-2 text-[10px] text-slate-400">Sebelum {{ startDate }}</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Debit (Pemasukan)</p>
                <h4 class="text-xl font-bold text-emerald-600 mt-1">{{ formatRp(summary.total_debit) }}</h4>
                <p class="mt-2 text-[10px] text-slate-400">Penjualan & kas masuk</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Kredit (Pengeluaran)</p>
                <h4 class="text-xl font-bold text-rose-600 mt-1">{{ formatRp(summary.total_kredit) }}</h4>
                <p class="mt-2 text-[10px] text-slate-400">Pakan & biaya operasional</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-xs bg-gradient-to-br from-blue-50/40 to-white">
                <p class="text-xs font-semibold text-blue-800">Saldo Kas Akhir</p>
                <h4 class="text-2xl font-extrabold text-blue-600 mt-1">{{ formatRp(summary.saldo_akhir) }}</h4>
                <p class="mt-2 text-[10px] text-slate-400">Saldo awal + Debit - Kredit</p>
            </div>
        </div>

        <!-- Filter & Print Action Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Laporan Arus Kas Unit Ketahanan Pangan (Hewani)
                </h4>
                <p class="text-[11px] text-slate-500">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
                <button @click="printReport" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Tabel Arus Kas Fisik BUMDes -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden printable-area">
            <div class="p-6 border-b border-slate-100 text-center">
                <h2 class="text-base font-bold text-slate-800 uppercase tracking-wide">BUMDES CIWUNI - UNIT KETAHANAN PANGAN</h2>
                <h3 class="text-sm font-semibold text-slate-700 mt-0.5">LAPORAN ARUS KAS (UNIT HEWANI)</h3>
                <p class="text-xs text-slate-500 mt-1">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3 w-12 text-center">No</th>
                            <th class="p-3 w-28">Tanggal</th>
                            <th class="p-3">Uraian Transaksi</th>
                            <th class="p-3 text-right">Debit (Rp)</th>
                            <th class="p-3 text-right">Kredit (Rp)</th>
                            <th class="p-3 text-right">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Baris Saldo Awal -->
                        <tr class="bg-slate-50/70 font-semibold text-slate-700">
                            <td class="p-3 text-center">-</td>
                            <td class="p-3">{{ startDate }}</td>
                            <td class="p-3">SALDO AWAL KAS</td>
                            <td class="p-3 text-right">-</td>
                            <td class="p-3 text-right">-</td>
                            <td class="p-3 text-right font-bold text-slate-800">{{ formatRp(summary.saldo_awal) }}</td>
                        </tr>

                        <!-- Baris Transaksi -->
                        <tr v-for="(f, idx) in flows" :key="f.id" class="hover:bg-slate-50/50">
                            <td class="p-3 text-center text-slate-400">{{ idx + 1 }}</td>
                            <td class="p-3 text-slate-600">{{ f.tanggal }}</td>
                            <td class="p-3 font-medium text-slate-800">{{ f.uraian }}</td>
                            <td class="p-3 text-right font-bold text-emerald-600">
                                {{ f.debit > 0 ? formatRp(f.debit) : '-' }}
                            </td>
                            <td class="p-3 text-right font-bold text-rose-600">
                                {{ f.kredit > 0 ? formatRp(f.kredit) : '-' }}
                            </td>
                            <td class="p-3 text-right font-extrabold text-slate-800">
                                {{ formatRp(f.saldo_berjalan) }}
                            </td>
                        </tr>

                        <tr v-if="flows.length === 0">
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                Tidak ada mutasi arus kas pada rentang tanggal ini.
                            </td>
                        </tr>

                        <!-- Total Footer -->
                        <tr class="bg-slate-100 font-bold text-slate-800 border-t-2 border-slate-300">
                            <td colspan="3" class="p-3 text-right uppercase">JUMLAH MUTASI PERIODE INI:</td>
                            <td class="p-3 text-right text-emerald-700 font-extrabold">{{ formatRp(summary.total_debit) }}</td>
                            <td class="p-3 text-right text-rose-700 font-extrabold">{{ formatRp(summary.total_kredit) }}</td>
                            <td class="p-3 text-right text-blue-700 font-extrabold">{{ formatRp(summary.saldo_akhir) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tanda Tangan Cetak -->
            <div class="p-8 grid grid-cols-2 text-center text-xs text-slate-700 mt-6">
                <div>
                    <p>Mengetahui,</p>
                    <p class="font-bold mt-1">Direktur BUMDes Ciwuni</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">( ............................................ )</p>
                </div>
                <div>
                    <p>Ciwuni, {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                    <p class="font-bold mt-1">Pengelola Unit Ketahanan Pangan</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">( ............................................ )</p>
                </div>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .printable-area, .printable-area * {
        visibility: visible;
    }
    .printable-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

