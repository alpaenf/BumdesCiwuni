<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    productions: Array,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('kp.report.produksi'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const printReport = () => window.print();
const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Produksi Telur" :unit="unit" :user="user">
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rekapitulasi Produksi Telur Harian</h4>
                <p class="text-[11px] text-slate-500">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>
            <div class="flex items-center gap-2">
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
                <h3 class="text-sm font-semibold text-slate-700 mt-1 uppercase">REKAP HASIL PRODUKSI BUDIDAYA AYAM PETELUR</h3>
                <p class="text-xs text-slate-500 mt-0.5">Periode: {{ startDate }} s/d {{ endDate }}</p>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="p-2.5">No</th>
                            <th class="p-2.5">Tanggal</th>
                            <th class="p-2.5">Hari</th>
                            <th class="p-2.5 text-right">Produksi Telur Bagus (kg)</th>
                            <th class="p-2.5 text-center">Telur Retak (btr)</th>
                            <th class="p-2.5 text-center">Telur Rusak (btr)</th>
                            <th class="p-2.5 text-right">Harga @ Rp/kg</th>
                            <th class="p-2.5 text-right">Estimasi Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(p, idx) in productions" :key="p.id">
                            <td class="p-2.5 text-slate-400">{{ idx + 1 }}</td>
                            <td class="p-2.5 font-medium text-slate-800">{{ p.tanggal }}</td>
                            <td class="p-2.5 text-slate-600">{{ p.hari }}</td>
                            <td class="p-2.5 text-right font-bold text-emerald-700">{{ Number(p.produksi_kg).toFixed(1) }}</td>
                            <td class="p-2.5 text-center text-amber-600 font-semibold">{{ p.telur_retak_butir }}</td>
                            <td class="p-2.5 text-center text-rose-600 font-semibold">{{ p.telur_rusak_butir }}</td>
                            <td class="p-2.5 text-right text-slate-600">{{ formatRp(p.harga_per_kg) }}</td>
                            <td class="p-2.5 text-right font-bold text-slate-800">{{ formatRp(p.pendapatan_estimasi) }}</td>
                        </tr>
                        <tr class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300">
                            <td colspan="3" class="p-2.5 text-right">TOTAL PRODUKSI:</td>
                            <td class="p-2.5 text-right font-extrabold text-emerald-800">{{ Number(summary.total_produksi_kg).toFixed(1) }} kg</td>
                            <td class="p-2.5 text-center font-extrabold text-amber-800">{{ summary.total_retak_butir }} btr</td>
                            <td class="p-2.5 text-center font-extrabold text-rose-800">{{ summary.total_rusak_butir }} btr</td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
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

