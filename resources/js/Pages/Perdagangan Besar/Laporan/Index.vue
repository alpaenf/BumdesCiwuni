<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    tab: { type: String, default: 'rekap' },
    filters: { type: Object, default: () => ({}) },
    rekap_layanan: { type: Array, default: () => [] },
    total_pendapatan: { type: Number, default: 0 },
    total_biaya: { type: Number, default: 0 },
    laba_bersih: { type: Number, default: 0 },
    data_detail: { type: [Object, Array], default: () => ({}) },
});

const activeTab = ref(props.tab || 'rekap');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const subLayananUmum = ref(props.filters.sub_layanan_umum || '');
const statusUmum = ref(props.filters.status_umum || '');

const jenisUmumOptions = [
    'Pulsa',
    'Pulsa data',
    'Pulsa listrik',
    'PDAM',
    'BPJS',
    'WIFI',
    'Virtual account',
    'Pajak kendaraan',
    'Pajak PBB',
    'Deposit',
    'Transfer sesama',
    'Transfer antarbank',
    'Tarik tunai',
    'Pelunasan hutang',
    'Multi payment',
    'Lain-lain',
];

const tabs = [
    { key: 'rekap', label: 'Ringkasan Layanan', icon: 'pie_chart' },
    { key: 'laba_rugi', label: 'Laba Rugi Unit', icon: 'query_stats' },
    { key: 'laku_pandai', label: 'Laku Pandai', icon: 'currency_exchange' },
    { key: 'samsat', label: 'Samsat Budiman', icon: 'two_wheeler' },
    { key: 'pbb', label: 'Pajak PBB', icon: 'home_work' },
    { key: 'umum', label: 'Transaksi Umum', icon: 'receipt_long' },
];

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const switchTab = (tabKey) => {
    activeTab.value = tabKey;
    router.get(route('pb.laporan.index'), {
        tab: tabKey,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        sub_layanan_umum: tabKey === 'umum' ? (subLayananUmum.value || undefined) : undefined,
        status_umum: tabKey === 'umum' ? (statusUmum.value || undefined) : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const applyFilter = () => {
    router.get(route('pb.laporan.index'), {
        tab: activeTab.value,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        sub_layanan_umum: activeTab.value === 'umum' ? (subLayananUmum.value || undefined) : undefined,
        status_umum: activeTab.value === 'umum' ? (statusUmum.value || undefined) : undefined,
    }, { preserveState: true, preserveScroll: true });
};

const downloadPdf = () => {
    const url = route('pb.laporan.cetak-pdf', {
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
        layanan: activeTab.value === 'rekap' || activeTab.value === 'laba_rugi' ? 'semua' : activeTab.value,
    });
    window.open(url, '_blank');
};
</script>

<template>
    <PerdaganganBesarLayout title="Laporan & Rekapitulasi Operasional" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Filter & Action Header -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Periode:</span>
                    <input
                        v-model="startDate"
                        type="date"
                        class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                    <span class="text-xs text-slate-400">s/d</span>
                    <input
                        v-model="endDate"
                        type="date"
                        class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                    <button
                        @click="applyFilter"
                        class="px-3 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition"
                    >
                        Terapkan
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="downloadPdf"
                        class="px-4 py-2 bg-rose-600 text-white rounded-lg text-xs font-bold hover:bg-rose-700 transition flex items-center gap-1.5 shadow-sm"
                    >
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                        <span>Cetak Laporan PDF</span>
                    </button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200 pb-2">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    @click="switchTab(t.key)"
                    :class="[
                        'flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition',
                        activeTab === t.key
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
                    ]"
                >
                    <span class="material-symbols-outlined text-sm">{{ t.icon }}</span>
                    <span>{{ t.label }}</span>
                </button>
            </div>

            <!-- TAB 1: REKAP RINGKASAN LAYANAN -->
            <div v-if="activeTab === 'rekap'" class="space-y-5">
                <!-- Summary Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Pendapatan Jasa Unit</span>
                        <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ formatRupiah(total_pendapatan) }}</h3>
                        <p class="text-xs text-slate-400 mt-1">Akumulasi laba jasa seluruh transaksi</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Biaya Operasional Unit</span>
                        <h3 class="text-2xl font-black text-rose-600 mt-1">{{ formatRupiah(total_biaya) }}</h3>
                        <p class="text-xs text-slate-400 mt-1">Pengeluaran administrasi & ATK</p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl text-white shadow-sm">
                        <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">Laba Bersih Operasional</span>
                        <h3 class="text-2xl font-black text-emerald-400 mt-1">{{ formatRupiah(laba_bersih) }}</h3>
                        <p class="text-xs text-slate-300 mt-1">Pendapatan jasa - biaya operasional</p>
                    </div>
                </div>

                <!-- Table Rekapitulasi Per Layanan -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100">
                        <h4 class="text-sm font-bold text-slate-800">Rekapitulasi Transaksi Per Subunit Layanan</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                    <th class="p-3">Nama Layanan</th>
                                    <th class="p-3 text-center">Jumlah Transaksi</th>
                                    <th class="p-3 text-right">Volume / Nilai Transaksi</th>
                                    <th class="p-3 text-right">Pendapatan Jasa (Keuntungan)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr v-for="r in rekap_layanan" :key="r.layanan" class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 font-bold text-slate-800">
                                        {{ r.layanan }}
                                    </td>
                                    <td class="p-3 text-center font-semibold">
                                        {{ r.transaksi }} transaksi
                                    </td>
                                    <td class="p-3 text-right font-medium">
                                        {{ formatRupiah(r.volume) }}
                                    </td>
                                    <td class="p-3 text-right font-black text-emerald-600">
                                        {{ formatRupiah(r.pendapatan_jasa) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 font-black text-slate-900 border-t border-slate-200">
                                    <td class="p-3 uppercase">Total Seluruh Layanan</td>
                                    <td class="p-3 text-center">
                                        {{ rekap_layanan.reduce((sum, r) => sum + r.transaksi, 0) }} transaksi
                                    </td>
                                    <td class="p-3 text-right">
                                        {{ formatRupiah(rekap_layanan.reduce((sum, r) => sum + r.volume, 0)) }}
                                    </td>
                                    <td class="p-3 text-right text-emerald-600">
                                        {{ formatRupiah(total_pendapatan) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: LABA RUGI UNIT -->
            <div v-else-if="activeTab === 'laba_rugi'" class="space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-4xl mx-auto space-y-6">
                    <div class="border-b border-slate-100 pb-4 text-center">
                        <h3 class="text-lg font-black text-slate-900">LAPORAN LABA / RUGI OPERASIONAL</h3>
                        <p class="text-xs text-slate-500 mt-1">Periode: {{ startDate }} s/d {{ endDate }}</p>
                    </div>

                    <!-- Pendapatan Jasa -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider border-b border-slate-100 pb-1">I. Pendapatan Operasional (Jasa Agen)</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1">
                                <span class="text-slate-700 pl-4">Pendapatan Jasa Laku Pandai</span>
                                <span class="font-semibold text-slate-900">{{ formatRupiah(data_detail.pendapatan_laku_pandai) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-700 pl-4">Pendapatan Jasa Samsat Budiman</span>
                                <span class="font-semibold text-slate-900">{{ formatRupiah(data_detail.pendapatan_samsat) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-700 pl-4">Pendapatan Jasa Pajak PBB</span>
                                <span class="font-semibold text-slate-900">{{ formatRupiah(data_detail.pendapatan_pbb) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-700 pl-4">Pendapatan Jasa Transaksi Umum & PPOB</span>
                                <span class="font-semibold text-slate-900">{{ formatRupiah(data_detail.pendapatan_umum) }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-slate-200 font-bold bg-emerald-50 px-3 rounded-lg text-emerald-900">
                                <span>TOTAL PENDAPATAN JASA</span>
                                <span>{{ formatRupiah(data_detail.total_pendapatan) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Beban Biaya Operasional -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider border-b border-slate-100 pb-1">II. Beban Operasional</h4>
                        <div class="space-y-2 text-xs">
                            <div v-for="b in data_detail.rincian_biaya" :key="b.kategori_biaya" class="flex justify-between py-1">
                                <span class="text-slate-700 pl-4">Biaya {{ b.kategori_biaya }}</span>
                                <span class="font-semibold text-rose-600">{{ formatRupiah(b.total) }}</span>
                            </div>
                            <div v-if="!data_detail.rincian_biaya || data_detail.rincian_biaya.length === 0" class="text-slate-400 italic pl-4 py-1">
                                Tidak ada catatan beban operasional pada periode ini.
                            </div>
                            <div class="flex justify-between py-2 border-t border-slate-200 font-bold bg-rose-50 px-3 rounded-lg text-rose-900">
                                <span>TOTAL BEBAN OPERASIONAL</span>
                                <span>{{ formatRupiah(data_detail.total_biaya) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Laba Bersih Final -->
                    <div class="pt-4 border-t-2 border-slate-300">
                        <div class="flex justify-between items-center p-4 bg-slate-900 text-white rounded-xl">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider block">Laba Bersih Operasional Unit</span>
                                <span class="text-[11px] text-slate-400">Total Pendapatan Jasa - Total Beban Biaya</span>
                            </div>
                            <span class="text-xl font-black text-emerald-400">{{ formatRupiah(data_detail.laba_bersih) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: DETAIL LAKU PANDAI -->
            <div v-else-if="activeTab === 'laku_pandai'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">Detail Transaksi Laku Pandai</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="p-3">Tanggal & Waktu</th>
                                <th class="p-3">Nasabah</th>
                                <th class="p-3">Uraian Transaksi</th>
                                <th class="p-3 text-right">Cash Masuk</th>
                                <th class="p-3 text-right">Cash Keluar</th>
                                <th class="p-3 text-right">Rek Masuk</th>
                                <th class="p-3 text-right">Rek Keluar</th>
                                <th class="p-3 text-right">Jasa Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="t in data_detail.data" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap">{{ t.tanggal }} {{ t.waktu }}</td>
                                <td class="p-3 font-bold text-slate-900">{{ t.nasabah?.nama_lengkap || '-' }}</td>
                                <td class="p-3">{{ t.uraian }}</td>
                                <td class="p-3 text-right text-emerald-600 font-semibold">{{ formatRupiah(t.cash_masuk) }}</td>
                                <td class="p-3 text-right text-rose-600 font-semibold">{{ formatRupiah(t.cash_keluar) }}</td>
                                <td class="p-3 text-right text-blue-600 font-semibold">{{ formatRupiah(t.rekening_masuk) }}</td>
                                <td class="p-3 text-right text-amber-600 font-semibold">{{ formatRupiah(t.rekening_keluar) }}</td>
                                <td class="p-3 text-right font-black text-emerald-600">
                                    {{ formatRupiah(Number(t.pendapatan_jasa_cash || 0) + Number(t.pendapatan_jasa_rekening || 0)) }}
                                </td>
                            </tr>
                            <tr v-if="!data_detail.data || data_detail.data.length === 0">
                                <td colspan="8" class="text-center py-6 text-slate-400">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: DETAIL SAMSAT -->
            <div v-else-if="activeTab === 'samsat'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">Detail Transaksi Samsat Budiman</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">No. Polisi & Wajib Pajak</th>
                                <th class="p-3 text-right">Nominal Pajak</th>
                                <th class="p-3 text-right">Biaya Admin</th>
                                <th class="p-3 text-right">Jasa BUMDes</th>
                                <th class="p-3 text-right">Total Bayar</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="t in data_detail.data" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap">{{ t.tanggal }}</td>
                                <td class="p-3">
                                    <span class="font-mono font-bold text-slate-900 block">{{ t.nomor_polisi }}</span>
                                    <span class="text-[11px] text-slate-500">{{ t.wajib_pajak?.nama_lengkap || '-' }}</span>
                                </td>
                                <td class="p-3 text-right font-medium">{{ formatRupiah(t.nominal_pajak) }}</td>
                                <td class="p-3 text-right font-medium">{{ formatRupiah(t.biaya_admin) }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600">{{ formatRupiah(t.biaya_jasa_bumdes) }}</td>
                                <td class="p-3 text-right font-black text-slate-900">{{ formatRupiah(t.total_pembayaran) }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ t.status_transaksi }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!data_detail.data || data_detail.data.length === 0">
                                <td colspan="7" class="text-center py-6 text-slate-400">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: DETAIL PBB -->
            <div v-else-if="activeTab === 'pbb'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">Detail Transaksi Pajak PBB</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Wajib Pajak & NOP</th>
                                <th class="p-3 text-center">Tahun</th>
                                <th class="p-3 text-right">Pokok PBB</th>
                                <th class="p-3 text-right">Biaya Admin (Jasa)</th>
                                <th class="p-3 text-right">Total Bayar</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="t in data_detail.data" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap">{{ t.tanggal }}</td>
                                <td class="p-3">
                                    <span class="font-bold text-slate-900 block">{{ t.nama_wajib_pajak }}</span>
                                    <span class="font-mono text-[11px] text-slate-500">NOP: {{ t.nop }}</span>
                                </td>
                                <td class="p-3 text-center font-bold">{{ t.tahun_pajak }}</td>
                                <td class="p-3 text-right font-medium">{{ formatRupiah(t.nominal_pokok) }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600">{{ formatRupiah(t.biaya_admin) }}</td>
                                <td class="p-3 text-right font-black text-slate-900">{{ formatRupiah(t.total_bayar) }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ t.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!data_detail.data || data_detail.data.length === 0">
                                <td colspan="7" class="text-center py-6 text-slate-400">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 6: DETAIL TRANSAKSI UMUM -->
            <div v-else-if="activeTab === 'umum'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Detail Layanan Umum & PPOB</h4>
                        <p class="text-[11px] text-slate-400">Filter berdasarkan jenis layanan atau status transaksi</p>
                    </div>
                    <!-- Filter Jenis Layanan & Status -->
                    <div class="flex flex-wrap items-center gap-2">
                        <select
                            v-model="subLayananUmum"
                            @change="applyFilter"
                            class="text-xs px-3 py-1.5 border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700"
                        >
                            <option value="">Semua Jenis Layanan</option>
                            <option v-for="opt in jenisUmumOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <select
                            v-model="statusUmum"
                            @change="applyFilter"
                            class="text-xs px-3 py-1.5 border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700"
                        >
                            <option value="">Semua Status</option>
                            <option value="SUCCES">SUCCES</option>
                            <option value="PENDING">PENDING</option>
                            <option value="CANCEL">CANCEL</option>
                        </select>
                        <button
                            v-if="subLayananUmum || statusUmum"
                            @click="subLayananUmum = ''; statusUmum = ''; applyFilter();"
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition"
                            title="Reset Filter Layanan"
                        >
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="p-3">No. Trx & Tanggal</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3">Layanan & Tujuan</th>
                                <th class="p-3 text-right">Biaya Pokok</th>
                                <th class="p-3 text-right">Jasa Agen</th>
                                <th class="p-3 text-right">Total Bayar</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="t in data_detail.data" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ t.nomor_transaksi }}</span>
                                    <span class="text-[10px] text-slate-400">{{ t.tanggal }}</span>
                                </td>
                                <td class="p-3 font-semibold text-slate-800">{{ t.nama_pelanggan }}</td>
                                <td class="p-3">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 border text-slate-700 block w-max mb-0.5">
                                        {{ t.jenis_transaksi }}
                                    </span>
                                    <span class="font-mono text-[11px] text-slate-500">{{ t.nomor_tujuan }}</span>
                                </td>
                                <td class="p-3 text-right font-medium">{{ formatRupiah(t.biaya_pokok) }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600">{{ formatRupiah(t.biaya_jasa_agen) }}</td>
                                <td class="p-3 text-right font-black text-slate-900">{{ formatRupiah(t.total_bayar) }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ t.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!data_detail.data || data_detail.data.length === 0">
                                <td colspan="7" class="text-center py-6 text-slate-400">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

