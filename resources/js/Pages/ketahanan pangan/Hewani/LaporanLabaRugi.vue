<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    filters: Object,
    report: Object,
});

const bulan = ref(props.filters.bulan);
const tahun = ref(props.filters.tahun);

const months = [
    { num: 1, name: 'Januari' }, { num: 2, name: 'Februari' }, { num: 3, name: 'Maret' },
    { num: 4, name: 'April' }, { num: 5, name: 'Mei' }, { num: 6, name: 'Juni' },
    { num: 7, name: 'Juli' }, { num: 8, name: 'Agustus' }, { num: 9, name: 'September' },
    { num: 10, name: 'Oktober' }, { num: 11, name: 'November' }, { num: 12, name: 'Desember' }
];

const applyFilter = () => {
    router.get(route('kp.report.laba-rugi'), {
        bulan: bulan.value,
        tahun: tahun.value,
    }, { preserveState: true });
};

const printReport = () => {
    window.print();
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Laba Rugi Hewani" :unit="unit" :user="user">
        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Laporan Laba Rugi Komprehensif (Unit Hewani)
                </h4>
                <p class="text-[11px] text-slate-500">Standar Format Laporan Laba Rugi Administrasi BUMDes Ciwuni</p>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="bulan" class="text-xs rounded-xl border-slate-200">
                    <option v-for="m in months" :key="m.num" :value="m.num">{{ m.name }}</option>
                </select>
                <input type="number" v-model="tahun" class="w-24 text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Tampilkan
                </button>
                <button @click="printReport" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Tabel Laba Rugi Format Administrasi BUMDes -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden printable-area max-w-4xl mx-auto">
            <div class="p-8 border-b border-slate-100 text-center">
                <h2 class="text-base font-bold text-slate-900 uppercase tracking-wider">BUMDES CIWUNI</h2>
                <h3 class="text-sm font-bold text-slate-800 mt-1 uppercase">UNIT KETAHANAN PANGAN (BUDIDAYA AYAM RAS PETELUR)</h3>
                <h4 class="text-xs font-semibold text-slate-600 mt-0.5">LAPORAN LABA RUGI</h4>
                <p class="text-xs text-slate-500 mt-1">Periode: {{ months.find(m => m.num === Number(bulan))?.name }} {{ tahun }}</p>
            </div>

            <div class="p-6 sm:p-8">
                <!-- A. PENDAPATAN -->
                <div class="mb-6">
                    <h5 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-2 border-b-2 border-slate-800 pb-1">
                        A. PENDAPATAN
                    </h5>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-700">1. Hasil Penjualan Telur</span>
                            <span class="font-bold text-slate-900">{{ formatRp(report.pendapatan.hasil_penjualan) }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-700">2. Pendapatan Lain-Lain (Pupuk Kandang, Karung, dll)</span>
                            <span class="font-bold text-slate-900">{{ formatRp(report.pendapatan.pendapatan_lain) }}</span>
                        </div>
                        <div class="flex justify-between py-2 bg-emerald-50/60 px-3 rounded-lg font-bold text-emerald-900">
                            <span>JUMLAH PENDAPATAN</span>
                            <span class="text-sm font-extrabold">{{ formatRp(report.pendapatan.total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- B. PENGELUARAN / BIAYA -->
                <div class="mb-6">
                    <h5 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-2 border-b-2 border-slate-800 pb-1">
                        B. PENGELUARAN / BIAYA
                    </h5>

                    <!-- 1. Biaya Tetap (Fixed Cost) -->
                    <div class="mb-4">
                        <h6 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5 text-blue-900">
                            1. Biaya Tetap (Fixed Cost)
                        </h6>
                        <div class="space-y-1.5 text-xs pl-3">
                            <div v-for="(val, label) in report.biaya_tetap.items" :key="label" class="flex justify-between py-1 border-b border-slate-100">
                                <span class="text-slate-600">• {{ label }}</span>
                                <span class="font-medium text-slate-800">{{ formatRp(val) }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 bg-slate-100/70 px-2 rounded font-bold text-slate-800">
                                <span>Subtotal Biaya Tetap</span>
                                <span>{{ formatRp(report.biaya_tetap.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Biaya Variabel (Variable Cost) -->
                    <div>
                        <h6 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5 text-amber-900">
                            2. Biaya Variabel (Variable Cost)
                        </h6>
                        <div class="space-y-1.5 text-xs pl-3">
                            <div v-for="(val, label) in report.biaya_variabel.items" :key="label" class="flex justify-between py-1 border-b border-slate-100">
                                <span class="text-slate-600">• {{ label }}</span>
                                <span class="font-medium text-slate-800">{{ formatRp(val) }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 bg-slate-100/70 px-2 rounded font-bold text-slate-800">
                                <span>Subtotal Biaya Variabel</span>
                                <span>{{ formatRp(report.biaya_variabel.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pengeluaran -->
                    <div class="mt-4 flex justify-between py-2 bg-rose-50/60 px-3 rounded-lg font-bold text-rose-900 text-xs">
                        <span>TOTAL PENGELUARAN / BIAYA</span>
                        <span class="text-sm font-extrabold">{{ formatRp(report.total_pengeluaran) }}</span>
                    </div>
                </div>

                <!-- C. HASIL AKHIR (LABA / RUGI) -->
                <div :class="[
                    'p-4 rounded-xl border-2 flex items-center justify-between text-xs',
                    report.is_laba 
                        ? 'bg-emerald-50 border-emerald-300 text-emerald-950' 
                        : 'bg-rose-50 border-rose-300 text-rose-950'
                ]">
                    <div>
                        <span class="text-xs font-bold tracking-wider uppercase block">
                            {{ report.is_laba ? 'LABA TAHUN/BULAN BERJALAN' : 'RUGI TAHUN/BULAN BERJALAN' }}
                        </span>
                        <span class="text-[11px] opacity-75">Total Pendapatan dikurangi Total Pengeluaran</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black block">
                            {{ formatRp(report.laba_rugi) }}
                        </span>
                        <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full inline-block mt-0.5', report.is_laba ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-200 text-rose-900']">
                            {{ report.is_laba ? 'SURPLUS' : 'DEFISIT' }}
                        </span>
                    </div>
                </div>

                <!-- Tanda Tangan Cetak -->
                <div class="grid grid-cols-2 text-center text-xs text-slate-700 mt-12 pt-6 border-t border-slate-200">
                    <div>
                        <p>Mengetahui,</p>
                        <p class="font-bold mt-1">Direktur BUMDes Ciwuni</p>
                        <div class="h-20"></div>
                        <p class="font-bold underline">( ............................................ )</p>
                    </div>
                    <div>
                        <p>Ciwuni, {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                        <p class="font-bold mt-1">Pengelola Unit Ketahanan Pangan</p>
                        <div class="h-20"></div>
                        <p class="font-bold underline">( ............................................ )</p>
                    </div>
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

