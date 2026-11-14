<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    stats: Object,
    commodities: Array,
    recentSales: Array,
});

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Dashboard Unit Nabati" :unit="unit" :user="user">
        <!-- 4 Stats Nabati -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Jumlah Komoditas</p>
                <h4 class="text-2xl font-bold text-teal-700 mt-1">{{ stats.commodities_count }} <span class="text-xs font-normal text-slate-400">jenis</span></h4>
                <p class="mt-2 text-[11px] text-slate-400">Padi, jagung, cabai, sayuran, dll</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Stok Hasil Produksi</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ Number(stats.total_stok_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                <p class="mt-2 text-[11px] text-teal-600 font-semibold">Tersedia di gudang panen</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Penjualan Nabati</p>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">{{ formatRp(stats.total_penjualan_rp) }}</h4>
                <p class="mt-2 text-[11px] text-slate-400">Pemasukan penjualan komoditas</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Laba / Rugi Bersih</p>
                <h4 :class="['text-2xl font-bold mt-1', stats.laba_rugi >= 0 ? 'text-teal-700' : 'text-rose-600']">
                    {{ formatRp(stats.laba_rugi) }}
                </h4>
                <p class="mt-2 text-[11px] text-slate-400">Total penjualan - biaya produksi</p>
            </div>
        </div>

        <!-- Persediaan Komoditas Nabati -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-800">Persediaan Komoditas Tanaman Pangan</h4>
                    <a :href="route('kp.nabati.komoditas')" class="text-xs font-semibold text-teal-600 hover:text-teal-700">Kelola Komoditas</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                            <tr>
                                <th class="p-2.5">Kode</th>
                                <th class="p-2.5">Komoditas</th>
                                <th class="p-2.5 text-right">Stok Tersedia</th>
                                <th class="p-2.5 text-right">Harga Acuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="c in commodities" :key="c.id" class="hover:bg-slate-50/50">
                                <td class="p-2.5 font-mono text-slate-500">{{ c.kode }}</td>
                                <td class="p-2.5 font-bold text-slate-800">{{ c.nama }}</td>
                                <td class="p-2.5 text-right font-extrabold text-teal-700">{{ Number(c.stok_tersedia).toFixed(1) }} {{ c.satuan }}</td>
                                <td class="p-2.5 text-right font-medium text-slate-700">{{ formatRp(c.harga_default) }} / {{ c.satuan }}</td>
                            </tr>
                            <tr v-if="commodities.length === 0">
                                <td colspan="4" class="text-center py-6 text-slate-400">
                                    Belum ada master komoditas nabati yang terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Penjualan Terakhir Nabati -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-800">Transaksi Panen Terakhir</h4>
                    <a :href="route('kp.nabati.penjualan')" class="text-xs font-semibold text-teal-600 hover:text-teal-700">Semua</a>
                </div>

                <div v-if="recentSales && recentSales.length > 0" class="space-y-3">
                    <div v-for="s in recentSales" :key="s.id" class="p-3 rounded-xl border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">{{ s.nama_pelanggan }}</span>
                            <span class="text-xs font-extrabold text-teal-700">{{ formatRp(s.total_harga) }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ s.commodity?.nama }} ({{ Number(s.jumlah).toFixed(1) }} {{ s.satuan }})</span>
                            <span>{{ s.tanggal }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-xs text-slate-400">
                    Belum ada transaksi penjualan hasil panen.
                </div>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

