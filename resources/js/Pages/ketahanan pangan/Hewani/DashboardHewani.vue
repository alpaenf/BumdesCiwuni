<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    filters: Object,
    stats: Object,
    chartData: Array,
    recentSales: Array,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('kp.hewani.dashboard'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const formatRp = (val) => {
    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
};
</script>

<template>
    <KetahananPanganLayout title="Dashboard Unit Hewani" :unit="unit" :user="user">
        <!-- Filter Date Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Periode Monitoring</h3>
                <p class="text-xs text-slate-500">Sesuaikan tanggal untuk merefresh statistik operasional dan keuangan</p>
            </div>
            <div class="flex items-center gap-2">
                <input 
                    type="date" 
                    v-model="startDate" 
                    class="text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                />
                <span class="text-xs text-slate-400">s/d</span>
                <input 
                    type="date" 
                    v-model="endDate" 
                    class="text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                />
                <button 
                    @click="applyFilter"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">filter_alt</span>
                    Filter
                </button>
            </div>
        </div>

        <!-- 7 Kartu Statistik Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- 1. Populasi Ayam -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Populasi Ayam</p>
                        <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ stats.populasi_ayam.toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">ekor</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">pest_control</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1 text-[11px] text-amber-600">
                    <span class="material-symbols-outlined text-sm">info</span>
                    <span>Ayam ras petelur aktif</span>
                </div>
            </div>

            <!-- 2. Total Produksi Telur -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Produksi Telur (Periode)</p>
                        <h4 class="text-2xl font-bold text-emerald-700 mt-1">{{ Number(stats.total_produksi_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">egg</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-500">
                    <span class="text-amber-600 font-semibold">{{ stats.total_telur_retak }} butir retak</span>
                    <span>•</span>
                    <span class="text-rose-600 font-semibold">{{ stats.total_telur_rusak }} rusak</span>
                </div>
            </div>

            <!-- 3. Total Penjualan & Stok Telur -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Penjualan Telur</p>
                        <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ Number(stats.total_penjualan_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">shopping_bag</span>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-blue-600 font-medium">
                    Sisa stok siap jual: <span class="font-bold">{{ Number(stats.stok_telur_kg).toFixed(1) }} kg</span>
                </div>
            </div>

            <!-- 4. Stok Pakan Tersedia -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Stok Pakan Tersedia</p>
                        <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ Number(stats.stok_pakan_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                </div>
                <div class="mt-3 text-[11px] text-purple-600 font-medium">
                    Pakan layer ayam petelur
                </div>
            </div>
        </div>

        <!-- Row Keuangan: Pendapatan, Pengeluaran, Laba/Rugi, Saldo Kas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-xs bg-gradient-to-br from-emerald-50/40 to-white">
                <p class="text-xs font-semibold text-emerald-800">Total Pendapatan</p>
                <h4 class="text-xl font-extrabold text-emerald-600 mt-1">{{ formatRp(stats.total_pendapatan) }}</h4>
                <div class="mt-2 text-[10px] text-slate-500 flex justify-between">
                    <span>Penjualan: {{ formatRp(stats.pendapatan_penjualan) }}</span>
                    <span>Lain: {{ formatRp(stats.pendapatan_lain) }}</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-xs bg-gradient-to-br from-rose-50/40 to-white">
                <p class="text-xs font-semibold text-rose-800">Total Pengeluaran</p>
                <h4 class="text-xl font-extrabold text-rose-600 mt-1">{{ formatRp(stats.total_pengeluaran) }}</h4>
                <p class="mt-2 text-[10px] text-slate-500">Biaya pakan, obat, operasional, & biaya tetap</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-semibold text-slate-600">Laba / Rugi Bersih</p>
                <h4 :class="['text-xl font-extrabold mt-1', stats.laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600']">
                    {{ formatRp(stats.laba_bersih) }}
                </h4>
                <span :class="['mt-2 inline-block px-2 py-0.5 rounded text-[10px] font-bold', stats.laba_bersih >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800']">
                    {{ stats.laba_bersih >= 0 ? 'SURPLUS / LABA' : 'DEFISIT / RUGI' }}
                </span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-xs bg-gradient-to-br from-blue-50/40 to-white">
                <p class="text-xs font-semibold text-blue-800">Saldo Kas Usaha</p>
                <h4 class="text-xl font-extrabold text-blue-600 mt-1">{{ formatRp(stats.saldo_kas) }}</h4>
                <p class="mt-2 text-[10px] text-slate-500">Arus kas rill saat ini</p>
            </div>
        </div>

        <!-- Grid: Tren Produksi & Transaksi Terakhir -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tren Produksi Telur (Tabel & Bar Visual) -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Tren Produksi Telur Harian (14 Hari Terakhir)</h4>
                        <p class="text-xs text-slate-500">Jumlah telur bagus (kg) dan kondisi fisik retak/rusak</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-medium">Harian</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                            <tr>
                                <th class="p-2.5 rounded-l-lg">Tanggal</th>
                                <th class="p-2.5 text-right">Produksi (kg)</th>
                                <th class="p-2.5 text-right">Retak (butir)</th>
                                <th class="p-2.5 text-right">Rusak (butir)</th>
                                <th class="p-2.5 text-right rounded-r-lg">Nilai Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in chartData" :key="item.tanggal" class="hover:bg-slate-50/50">
                                <td class="p-2.5 font-medium text-slate-800">{{ item.tanggal }}</td>
                                <td class="p-2.5 text-right font-bold text-emerald-600">{{ item.produksi.toFixed(1) }}</td>
                                <td class="p-2.5 text-right text-amber-600">{{ item.retak }}</td>
                                <td class="p-2.5 text-right text-rose-600">{{ item.rusak }}</td>
                                <td class="p-2.5 text-right font-medium text-slate-700">{{ formatRp(item.penjualan_rp) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Penjualan Terakhir -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-800">Transaksi Penjualan Terbaru</h4>
                    <a :href="route('kp.sale.index')" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
                </div>

                <div v-if="recentSales && recentSales.length > 0" class="space-y-3">
                    <div 
                        v-for="sale in recentSales" 
                        :key="sale.id"
                        class="p-3 rounded-xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/20 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800">{{ sale.nama_pelanggan }}</span>
                                <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded font-medium bg-slate-100 text-slate-600">{{ sale.customer_type }}</span>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-600">{{ formatRp(sale.total_harga) }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ sale.nomor_transaksi }} • {{ Number(sale.jumlah_kg).toFixed(1) }} kg</span>
                            <span>{{ sale.tanggal }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-xs text-slate-400">
                    Belum ada transaksi penjualan yang tercatat.
                </div>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

