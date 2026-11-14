<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    sales: Array,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const customerType = ref(props.filters.customer_type || '');

const applyFilter = () => {
    router.get(route('kp.report.penjualan'), {
        start_date: startDate.value,
        end_date: endDate.value,
        customer_type: customerType.value,
    }, { preserveState: true });
};

const printReport = () => window.print();
const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Penjualan Telur" :unit="unit" :user="user">
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rekapitulasi Penjualan Telur</h4>
                <p class="text-[11px] text-slate-500">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="customerType" @change="applyFilter" class="text-xs rounded-xl border-slate-200">
                    <option value="">Semua Customer</option>
                    <option value="Eceran">Eceran</option>
                    <option value="Agen">Agen</option>
                </select>
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">Filter</button>
                <button @click="printReport" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Cetak
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden printable-area p-6">
            <div class="text-center pb-6 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-900 uppercase">BUMDES CIWUNI - UNIT KETAHANAN PANGAN</h2>
                <h3 class="text-sm font-semibold text-slate-700 mt-1 uppercase">LAPORAN REKAPITULASI PENJUALAN TELUR AYAM</h3>
                <p class="text-xs text-slate-500 mt-0.5">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="p-2.5">No</th>
                            <th class="p-2.5">No. Struk</th>
                            <th class="p-2.5">Tanggal</th>
                            <th class="p-2.5">Pelanggan</th>
                            <th class="p-2.5">Kategori</th>
                            <th class="p-2.5 text-right">Qty (kg)</th>
                            <th class="p-2.5 text-right">Harga @ Rp/kg</th>
                            <th class="p-2.5 text-right">Total Penjualan</th>
                            <th class="p-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(s, idx) in sales" :key="s.id">
                            <td class="p-2.5 text-slate-400">{{ idx + 1 }}</td>
                            <td class="p-2.5 font-mono font-medium">{{ s.nomor_transaksi }}</td>
                            <td class="p-2.5 text-slate-600">{{ s.tanggal }}</td>
                            <td class="p-2.5 font-bold text-slate-800">{{ s.nama_pelanggan }}</td>
                            <td class="p-2.5">{{ s.customer_type }}</td>
                            <td class="p-2.5 text-right font-bold text-slate-800">{{ Number(s.jumlah_kg).toFixed(1) }} kg</td>
                            <td class="p-2.5 text-right text-slate-600">{{ formatRp(s.harga_per_kg) }}</td>
                            <td class="p-2.5 text-right font-extrabold text-emerald-700">{{ formatRp(s.total_harga) }}</td>
                            <td class="p-2.5 font-medium text-slate-700">{{ s.status_pembayaran }}</td>
                        </tr>
                        <tr class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300">
                            <td colspan="5" class="p-2.5 text-right">TOTAL KESELURUHAN ({{ summary.total_transaksi }} TRANSAKSI):</td>
                            <td class="p-2.5 text-right font-extrabold text-slate-900">{{ Number(summary.total_kg).toFixed(1) }} kg</td>
                            <td></td>
                            <td class="p-2.5 text-right font-extrabold text-emerald-800">{{ formatRp(summary.total_rp) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 p-4 bg-slate-50 rounded-xl text-xs flex justify-between font-semibold text-slate-700">
                <span>Penjualan Konsumen Eceran: <strong class="text-emerald-700">{{ formatRp(summary.total_eceran_rp) }}</strong></span>
                <span>Penjualan Agen / Warung: <strong class="text-purple-700">{{ formatRp(summary.total_agen_rp) }}</strong></span>
            </div>

            <!-- Tanda Tangan Cetak -->
            <div class="grid grid-cols-2 text-center text-xs text-slate-700 mt-10 pt-6 border-t border-slate-200">
                <div>
                    <p>Mengetahui,</p>
                    <p class="font-bold mt-1">Direktur BUMDes Ciwuni</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">( ............................................ )</p>
                </div>
                <div>
                    <p>Ciwuni, {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                    <p class="font-bold mt-1">Pengelola Unit Hewani</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">( ............................................ )</p>
                </div>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-area, .printable-area * { visibility: visible; }
    .printable-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
}
</style>

