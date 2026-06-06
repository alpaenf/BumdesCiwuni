<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tahun: Number,
    bulan: [Number, null],
    tahunOptions: Array,
    bulanOptions: Array,
    labaTabungan: Number,
    labaSembako: Number,
    bungaPinjaman: Number,
    pendapatanKotor: Number,
    distribusi: Array,
    detailTabungan: Array,
    detailSembako: Array,
    detailPinjaman: Array,
});

const selectedTahun = ref(props.tahun);
const selectedBulan = ref(props.bulan ?? '');
const applyFilter = () => {
    const params = { tahun: selectedTahun.value };
    if (selectedBulan.value) params.bulan = selectedBulan.value;
    router.get(route('pendapatan.index'), params, { preserveState: true });
};

const fmt = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const pct = (v, total) => total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';

const bulanNama = props.bulanOptions?.find(b => b.value === (props.bulan ?? ''))?.label ?? 'Semua Bulan';

const activeTab = ref('tabungan');
</script>

<template>
    <Head title="Pendapatan Kotor" />
    <AuthenticatedLayout>
        <template #header>Pendapatan Kotor</template>

        <div class="space-y-6">
            <!-- Filter Tahun & Bulan -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Ringkasan Pendapatan Kotor</h2>
                    <p class="text-sm text-slate-500">Periode: {{ bulanNama }} {{ tahun }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <select v-model="selectedBulan" @change="applyFilter" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option v-for="b in bulanOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                    </select>
                    <select v-model="selectedTahun" @change="applyFilter" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option v-for="y in tahunOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>

            <!-- Pendapatan Kotor Total -->
            <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 p-6 text-white shadow-lg">
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-2xl opacity-80">account_balance_wallet</span>
                    <p class="text-sm font-semibold uppercase tracking-wider opacity-80">Total Pendapatan Kotor {{ tahun }}</p>
                </div>
                <p class="text-3xl font-bold">{{ fmt(pendapatanKotor) }}</p>
                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-white/15 backdrop-blur-sm p-3">
                        <p class="text-xs opacity-75">Laba Tabungan</p>
                        <p class="text-lg font-bold">{{ fmt(labaTabungan) }}</p>
                        <p class="text-xs opacity-60">{{ pct(labaTabungan, pendapatanKotor) }}%</p>
                    </div>
                    <div class="rounded-xl bg-white/15 backdrop-blur-sm p-3">
                        <p class="text-xs opacity-75">Laba Sembako</p>
                        <p class="text-lg font-bold">{{ fmt(labaSembako) }}</p>
                        <p class="text-xs opacity-60">{{ pct(labaSembako, pendapatanKotor) }}%</p>
                    </div>
                    <div class="rounded-xl bg-white/15 backdrop-blur-sm p-3">
                        <p class="text-xs opacity-75">Bunga Pinjaman</p>
                        <p class="text-lg font-bold">{{ fmt(bungaPinjaman) }}</p>
                        <p class="text-xs opacity-60">{{ pct(bungaPinjaman, pendapatanKotor) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Distribusi Pendapatan -->
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-lg text-emerald-600">pie_chart</span>
                    Distribusi Pendapatan Kotor (100%)
                </h3>
                <div class="space-y-3">
                    <div v-for="item in distribusi" :key="item.nama" class="flex items-center gap-4">
                        <div class="w-40 text-sm font-medium text-slate-700">{{ item.nama }}</div>
                        <div class="flex-1">
                            <div class="h-6 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 flex items-center px-2"
                                    :style="{ width: item.persen + '%' }"
                                    :class="{
                                        'bg-emerald-500': item.nama === 'Laba Bersih',
                                        'bg-blue-500': item.nama === 'Biaya Gaji',
                                        'bg-amber-500': item.nama === 'Biaya Operasional',
                                        'bg-purple-500': item.nama === 'Biaya Asuransi',
                                        'bg-cyan-500': item.nama === 'Biaya ATK',
                                        'bg-rose-400': item.nama === 'Biaya Perlengkapan',
                                    }">
                                    <span class="text-[10px] font-bold text-white whitespace-nowrap">{{ item.persen }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-36 text-right text-sm font-semibold text-slate-800">{{ fmt(item.nominal) }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-800">Total</span>
                    <span class="text-sm font-bold text-emerald-700">{{ fmt(pendapatanKotor) }}</span>
                </div>
            </div>

            <!-- Detail Tabs -->
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center gap-1 mb-4 border-b border-slate-100 pb-3">
                    <button @click="activeTab = 'tabungan'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition"
                        :class="activeTab === 'tabungan' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'">
                        <span class="material-symbols-outlined text-xs align-middle mr-1">savings</span>Tabungan Reguler
                    </button>
                    <button @click="activeTab = 'sembako'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition"
                        :class="activeTab === 'sembako' ? 'bg-orange-500 text-white' : 'text-slate-600 hover:bg-slate-100'">
                        <span class="material-symbols-outlined text-xs align-middle mr-1">shopping_basket</span>Tabungan Sembako
                    </button>
                    <button @click="activeTab = 'pinjaman'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition"
                        :class="activeTab === 'pinjaman' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100'">
                        <span class="material-symbols-outlined text-xs align-middle mr-1">account_balance</span>Pinjaman
                    </button>
                </div>

                <!-- Tab: Tabungan Reguler -->
                <div v-if="activeTab === 'tabungan'">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-slate-700">Detail Laba Tabungan Reguler</h4>
                        <span class="text-xs text-slate-500">{{ detailTabungan.length }} transaksi terakhir</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase tracking-wider text-slate-500">
                                    <th class="py-2 pe-4">Tanggal</th>
                                    <th class="py-2 pe-4">Nasabah</th>
                                    <th class="py-2 pe-4">Keterangan</th>
                                    <th class="py-2 text-right">Laba Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in detailTabungan" :key="i" class="border-b border-slate-50">
                                    <td class="py-2.5 pe-4 text-slate-600">{{ item.tanggal }}</td>
                                    <td class="py-2.5 pe-4 font-medium text-slate-800">{{ item.nasabah }}</td>
                                    <td class="py-2.5 pe-4 text-slate-500">{{ item.keterangan }}</td>
                                    <td class="py-2.5 text-right font-semibold text-emerald-700">{{ fmt(item.laba) }}</td>
                                </tr>
                                <tr v-if="detailTabungan.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data laba tabungan di tahun ini.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="detailTabungan.length > 0">
                                <tr class="border-t-2 border-slate-200">
                                    <td colspan="3" class="py-2.5 font-bold text-slate-800">Total Laba Tabungan Reguler</td>
                                    <td class="py-2.5 text-right font-bold text-emerald-700">{{ fmt(labaTabungan) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab: Tabungan Sembako -->
                <div v-if="activeTab === 'sembako'">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-slate-700">Detail Laba Tabungan Sembako</h4>
                        <span class="text-xs text-slate-500">{{ detailSembako.length }} transaksi terakhir</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase tracking-wider text-slate-500">
                                    <th class="py-2 pe-4">Tanggal</th>
                                    <th class="py-2 pe-4">Nasabah</th>
                                    <th class="py-2 pe-4">Keterangan</th>
                                    <th class="py-2 text-right">Laba Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in detailSembako" :key="i" class="border-b border-slate-50">
                                    <td class="py-2.5 pe-4 text-slate-600">{{ item.tanggal }}</td>
                                    <td class="py-2.5 pe-4 font-medium text-slate-800">{{ item.nasabah }}</td>
                                    <td class="py-2.5 pe-4 text-slate-500">{{ item.keterangan }}</td>
                                    <td class="py-2.5 text-right font-semibold text-orange-600">{{ fmt(item.laba) }}</td>
                                </tr>
                                <tr v-if="detailSembako.length === 0">
                                    <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data laba sembako di tahun ini.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="detailSembako.length > 0">
                                <tr class="border-t-2 border-slate-200">
                                    <td colspan="3" class="py-2.5 font-bold text-slate-800">Total Laba Tabungan Sembako</td>
                                    <td class="py-2.5 text-right font-bold text-orange-600">{{ fmt(labaSembako) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab: Pinjaman -->
                <div v-if="activeTab === 'pinjaman'">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-slate-700">Detail Pendapatan Bunga Pinjaman</h4>
                        <span class="text-xs text-slate-500">{{ detailPinjaman.length }} pinjaman terakhir</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase tracking-wider text-slate-500">
                                    <th class="py-2 pe-4">Tanggal Akad</th>
                                    <th class="py-2 pe-4">Nasabah</th>
                                    <th class="py-2 pe-4 text-right">Pokok</th>
                                    <th class="py-2 pe-4 text-right">Bunga (%)</th>
                                    <th class="py-2 pe-4 text-right">Pendapatan Bunga</th>
                                    <th class="py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in detailPinjaman" :key="i" class="border-b border-slate-50">
                                    <td class="py-2.5 pe-4 text-slate-600">{{ item.tanggal }}</td>
                                    <td class="py-2.5 pe-4 font-medium text-slate-800">{{ item.nasabah }}</td>
                                    <td class="py-2.5 pe-4 text-right text-slate-600">{{ fmt(item.pokok) }}</td>
                                    <td class="py-2.5 pe-4 text-right text-slate-600">{{ item.bunga_persen }}%</td>
                                    <td class="py-2.5 pe-4 text-right font-semibold text-blue-700">{{ fmt(item.bunga_nominal) }}</td>
                                    <td class="py-2.5 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                            :class="item.status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="detailPinjaman.length === 0">
                                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data pinjaman di tahun ini.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="detailPinjaman.length > 0">
                                <tr class="border-t-2 border-slate-200">
                                    <td colspan="4" class="py-2.5 font-bold text-slate-800">Total Pendapatan Bunga</td>
                                    <td class="py-2.5 text-right font-bold text-blue-700">{{ fmt(bungaPinjaman) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
