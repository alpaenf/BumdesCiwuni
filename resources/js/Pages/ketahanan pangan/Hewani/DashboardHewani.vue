<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';
import Chart from 'chart.js/auto';

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

// Canvas Refs for Charts
const produksiChartRef = ref(null);
const penjualanChartRef = ref(null);
let produksiChartInstance = null;
let penjualanChartInstance = null;

onMounted(() => {
    if (produksiChartRef.value) {
        const labels = props.chartData.map(d => d.tanggal);
        const dataProduksi = props.chartData.map(d => d.produksi_kg);
        
        produksiChartInstance = new Chart(produksiChartRef.value, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Produksi Telur (kg)',
                        data: dataProduksi,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.raw} kg`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    if (penjualanChartRef.value) {
        const labels = props.chartData.map(d => d.tanggal);
        const dataPenjualanKg = props.chartData.map(d => d.penjualan_kg);
        const dataPenjualanRp = props.chartData.map(d => d.penjualan_rp);

        penjualanChartInstance = new Chart(penjualanChartRef.value, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penjualan Telur (kg)',
                        data: dataPenjualanKg,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const index = ctx.dataIndex;
                                const rp = dataPenjualanRp[index] || 0;
                                return `${ctx.raw} kg (Rp ${Number(rp).toLocaleString('id-ID')})`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }
});

// Modal Ubah Populasi Ayam Cepat
const isPopulasiModalOpen = ref(false);
const newPopulasi = ref(props.stats.populasi_ayam || 1000);
const isUpdatingPopulasi = ref(false);

const openPopulasiModal = () => {
    newPopulasi.value = props.stats.populasi_ayam || 1000;
    isPopulasiModalOpen.value = true;
};

const savePopulasi = () => {
    isUpdatingPopulasi.value = true;
    router.post(route('kp.hewani.populasi.update'), {
        populasi_ayam: newPopulasi.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isPopulasiModalOpen.value = false;
            isUpdatingPopulasi.value = false;
        },
        onError: (errs) => {
            isUpdatingPopulasi.value = false;
            alert(Object.values(errs).join('\n'));
        },
        onFinish: () => {
            isUpdatingPopulasi.value = false;
        }
    });
};

// Modal Atur & Reset Saldo Kas
const isSaldoModalOpen = ref(false);
const inputModalAwal = ref(props.stats.modal_awal || 0);
const cleanArusKas = ref(true);
const isUpdatingSaldo = ref(false);

const openSaldoModal = () => {
    inputModalAwal.value = props.stats.modal_awal || 0;
    cleanArusKas.value = true;
    isSaldoModalOpen.value = true;
};

const saveModalAwal = () => {
    isUpdatingSaldo.value = true;
    router.post(route('kp.hewani.saldo.update'), {
        modal_awal: inputModalAwal.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSaldoModalOpen.value = false;
            isUpdatingSaldo.value = false;
        },
        onError: (errs) => {
            isUpdatingSaldo.value = false;
            alert(Object.values(errs).join('\n'));
        },
        onFinish: () => {
            isUpdatingSaldo.value = false;
        }
    });
};

const executeResetSaldo = (target = 0) => {
    if (!confirm(`Konfirmasi: Apakah Anda yakin ingin mereset saldo kas ke Rp ${Number(target).toLocaleString('id-ID')}?`)) {
        return;
    }
    isUpdatingSaldo.value = true;
    router.post(route('kp.hewani.saldo.reset'), {
        target_saldo: target,
        bersihkan_arus_kas: cleanArusKas.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSaldoModalOpen.value = false;
            isUpdatingSaldo.value = false;
        },
        onError: (errs) => {
            isUpdatingSaldo.value = false;
            alert(Object.values(errs).join('\n'));
        },
        onFinish: () => {
            isUpdatingSaldo.value = false;
        }
    });
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

        <!-- 5 Kartu Statistik Operasional & Rekanan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- 1. Populasi Ayam -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-slate-500">Populasi Ayam</p>
                        <h4 class="text-xl font-bold text-slate-800 mt-1">{{ stats.populasi_ayam.toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">ekor</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">pest_control</span>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">Ayam ras aktif</span>
                    <button 
                        @click="openPopulasiModal" 
                        class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center gap-0.5 cursor-pointer"
                    >
                        Ubah
                        <span class="material-symbols-outlined notranslate text-xs">edit</span>
                    </button>
                </div>
            </div>

            <!-- 2. Total Produksi Telur -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-slate-500">Produksi Telur</p>
                        <h4 class="text-xl font-bold text-emerald-700 mt-1">{{ Number(stats.total_produksi_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">egg</span>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center gap-1.5 text-[10.5px] text-slate-500">
                    <span class="text-amber-600 font-semibold">{{ stats.total_telur_retak }} retak</span>
                    <span>•</span>
                    <span class="text-rose-600 font-semibold">{{ stats.total_telur_rusak }} rusak</span>
                </div>
            </div>

            <!-- 3. Penjualan Telur -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-slate-500">Penjualan Telur</p>
                        <h4 class="text-xl font-bold text-slate-800 mt-1">{{ Number(stats.total_penjualan_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">shopping_bag</span>
                    </div>
                </div>
                <div class="mt-2.5 text-[10.5px] text-blue-600 font-medium">
                    Stok kandang: <span class="font-bold">{{ Number(stats.stok_telur_kg).toFixed(1) }} kg</span>
                </div>
            </div>

            <!-- 4. Card Rekanan BUMDes -->
            <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-xs relative overflow-hidden bg-gradient-to-br from-indigo-50/30 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-indigo-800">Pembelian Rekanan</p>
                        <h4 class="text-xl font-bold text-indigo-700 mt-1">
                            {{ Number(stats.total_beli_rekanan_kg || 0).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                        </h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">handshake</span>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center justify-between text-[10.5px]">
                    <span class="text-slate-500">Sisa stok rekanan:</span>
                    <span class="font-bold text-indigo-700">{{ Number(stats.sisa_stok_rekanan_kg || 0).toFixed(1) }} kg</span>
                </div>
            </div>

            <!-- 5. Stok Pakan Tersedia -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-slate-500">Stok Pakan Layer</p>
                        <h4 class="text-xl font-bold text-purple-700 mt-1">{{ Number(stats.stok_pakan_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span></h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">inventory_2</span>
                    </div>
                </div>
                <div class="mt-2.5 text-[10.5px] text-purple-600 font-medium">
                    Siap konsumsi harian
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

            <div class="bg-white p-5 rounded-2xl border border-blue-100 shadow-xs bg-gradient-to-br from-blue-50/40 to-white relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-800">Saldo Kas Usaha</p>
                        <h4 class="text-xl font-extrabold text-blue-600 mt-1">{{ formatRp(stats.saldo_kas) }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                </div>
                <div class="mt-2.5 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">
                        <span v-if="stats.is_saldo_adjusted" class="text-amber-600 font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined notranslate text-xs">info</span>
                            Kas seimbang
                        </span>
                        <span v-else>Arus kas riil saat ini</span>
                    </span>
                    <button 
                        @click="openSaldoModal" 
                        class="font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-0.5 cursor-pointer"
                    >
                        Atur / Reset
                        <span class="material-symbols-outlined notranslate text-xs">tune</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid Grafik Harian: Produksi Telur & Penjualan Telur (Atas - Bawah Full Width) -->
        <div class="space-y-6 mb-6">
            <!-- 1. Grafik Harian Produksi Telur (Full Width) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Grafik Harian Produksi Telur (1 Bulan)</h4>
                        <p class="text-xs text-slate-500">Hasil produksi harian kandang (kg) per tanggal pada periode monitoring</p>
                    </div>
                    <span class="text-[11px] px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Produksi (kg)
                    </span>
                </div>
                <div class="h-64 sm:h-72 w-full">
                    <canvas ref="produksiChartRef"></canvas>
                </div>
            </div>

            <!-- 2. Grafik Harian Penjualan Telur (Full Width) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Grafik Harian Penjualan Telur (1 Bulan)</h4>
                        <p class="text-xs text-slate-500">Volume dan tren penjualan telur (kg) per tanggal pada periode monitoring</p>
                    </div>
                    <span class="text-[11px] px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-100 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Penjualan (kg)
                    </span>
                </div>
                <div class="h-64 sm:h-72 w-full">
                    <canvas ref="penjualanChartRef"></canvas>
                </div>
            </div>
        </div>

        <!-- Grid: Tabel Rekap Harian & Transaksi Penjualan Terakhir -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabel Rekap Produksi & Penjualan Harian -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Rekap Data Harian (Periode 1 Bulan)</h4>
                        <p class="text-xs text-slate-500">Rincian produksi telur bagus, kondisi retak/rusak, dan penjualan</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-medium">Harian</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                            <tr>
                                <th class="p-2.5 rounded-l-lg">Tanggal</th>
                                <th class="p-2.5 text-right">Produksi (kg)</th>
                                <th class="p-2.5 text-right">Retak (btr)</th>
                                <th class="p-2.5 text-right">Rusak (btr)</th>
                                <th class="p-2.5 text-right">Terjual (kg)</th>
                                <th class="p-2.5 text-right rounded-r-lg">Nilai Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in chartData" :key="item.tanggal" class="hover:bg-slate-50/50">
                                <td class="p-2.5 font-medium text-slate-800">{{ item.tanggal }}</td>
                                <td class="p-2.5 text-right font-bold text-emerald-600">{{ item.produksi_kg.toFixed(1) }}</td>
                                <td class="p-2.5 text-right text-amber-600">{{ item.retak }}</td>
                                <td class="p-2.5 text-right text-rose-600">{{ item.rusak }}</td>
                                <td class="p-2.5 text-right font-bold text-blue-600">{{ Number(item.penjualan_kg || 0).toFixed(1) }}</td>
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

        <!-- Modal Ubah Populasi Ayam Cepat -->
        <div v-if="isPopulasiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">Ubah Populasi Ayam Petelur</h4>
                    <button @click="isPopulasiModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="savePopulasi" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Populasi (Ekor)</label>
                        <input 
                            type="number" 
                            min="1" 
                            v-model="newPopulasi" 
                            class="w-full text-sm font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500" 
                            required 
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Mengubah jumlah ayam produktif aktif yang dipelihara saat ini.</p>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isPopulasiModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl" :disabled="isUpdatingPopulasi">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5" :disabled="isUpdatingPopulasi">
                            <span v-if="isUpdatingPopulasi" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            {{ isUpdatingPopulasi ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Atur / Reset Saldo Kas Usaha -->
        <div v-if="isSaldoModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">Atur Saldo Kas / Modal Awal</h4>
                    </div>
                    <button @click="isSaldoModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <!-- Info Status Saldo Saat Ini -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Saldo Kas Tampil:</span>
                            <span class="font-bold text-blue-700">{{ formatRp(stats.saldo_kas) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500 text-[11px]">
                            <span>Modal Awal BUMDes Aktif:</span>
                            <span class="font-semibold">{{ formatRp(stats.modal_awal) }}</span>
                        </div>
                        <div v-if="stats.is_saldo_adjusted" class="mt-2 pt-2 border-t border-slate-200 text-[11px] text-amber-700 bg-amber-50/50 p-2 rounded-lg flex items-start gap-1.5">
                            <span class="material-symbols-outlined text-sm shrink-0">info</span>
                            <span>Terdapat riwayat pengeluaran sampel sebelum ada modal awal. Saldo kas telah diamankan agar tidak minus. Anda dapat mengatur Modal Awal atau Reset Kas ke Rp 0 di bawah.</span>
                        </div>
                    </div>

                    <!-- Form Tab: Set Modal Awal -->
                    <form @submit.prevent="saveModalAwal" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Modal Awal Kas BUMDes (Rp)
                            </label>
                            <input 
                                type="number" 
                                min="0" 
                                step="1000"
                                v-model="inputModalAwal" 
                                class="w-full text-sm font-bold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500" 
                                placeholder="Contoh: 100000000"
                                required 
                            />
                            <p class="text-[11px] text-slate-400 mt-1">
                                Alokasi saldo kas awal yang disediakan BUMDes untuk unit ketahanan pangan.
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5"
                                :disabled="isUpdatingSaldo"
                            >
                                <span v-if="isUpdatingSaldo" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                                Simpan Modal Awal
                            </button>
                        </div>
                    </form>

                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-xs font-semibold text-slate-700 mb-2">Opsi Reset Kas & Pembersihan Sampel</p>
                        <div class="p-3 rounded-xl bg-rose-50/40 border border-rose-100 space-y-2">
                            <p class="text-[11px] text-rose-800">
                                Gunakan tombol ini jika seluruh data sampel telah dihapus dan Anda ingin saldo kas kembali bersih ke <strong>Rp 0</strong>.
                            </p>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    id="cleanCheck" 
                                    v-model="cleanArusKas" 
                                    class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 text-xs"
                                />
                                <label for="cleanCheck" class="text-[11px] text-slate-600 select-none cursor-pointer">
                                    Bersihkan seluruh riwayat arus kas sampel lama
                                </label>
                            </div>
                            <button 
                                type="button" 
                                @click="executeResetSaldo(0)" 
                                class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                                :disabled="isUpdatingSaldo"
                            >
                                <span class="material-symbols-outlined text-sm">restart_alt</span>
                                Reset Saldo Kas Menjadi Rp 0
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

