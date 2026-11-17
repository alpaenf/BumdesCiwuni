<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';
import PbRevenueCompositionChart from './Partials/PbRevenueCompositionChart.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    user: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const selectedPeriod = ref(props.stats?.period || 'today');

const periodLabel = computed(() => {
    if (selectedPeriod.value === 'today') return 'Hari Ini';
    if (selectedPeriod.value === 'this_week') return 'Minggu Ini';
    if (selectedPeriod.value === 'this_month') return 'Bulan Ini';
    return 'Hari Ini';
});

const totalPendapatanSubunit = computed(() => {
    const b = props.stats?.layanan_breakdown || {};
    return Number(b.laku_pandai?.pendapatan || 0)
        + Number(b.samsat?.pendapatan || 0)
        + Number(b.pbb?.pendapatan || 0)
        + Number(b.umum?.pendapatan || 0);
});

const getPercentage = (val) => {
    const total = totalPendapatanSubunit.value;
    if (!total || total === 0) return '0%';
    return Math.round(((Number(val) || 0) / total) * 100) + '%';
};

const applyFilter = (period) => {
    selectedPeriod.value = period;
    router.get(route('pb.dashboard'), {
        period: period,
    }, { preserveState: true, preserveScroll: true });
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Dashboard" />

    <PerdaganganBesarLayout title="Dashboard" :unit="unit">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-primary">Operasional Harian</h2>
                    <p class="text-sm text-secondary">
                        Selamat datang kembali, berikut ringkasan transaksi {{ periodLabel.toLowerCase() }}.
                    </p>
                </div>
                <!-- Filter Periode -->
                <select
                    v-model="selectedPeriod"
                    @change="applyFilter(selectedPeriod)"
                    class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none shadow-sm cursor-pointer"
                >
                    <option value="today">Hari Ini</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                </select>
            </div>

            <!-- Bento Grid Stats (6 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Card 1: Total Transaksi -->
                <div class="bg-white p-4 border border-outline-variant rounded-xl flex flex-col justify-between group hover:border-primary transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-blue-50 rounded-lg text-blue-700">
                            <span class="material-symbols-outlined">receipt_long</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-[10px] flex items-center gap-1">
                            Aktif
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-xs font-medium">Total Transaksi</p>
                        <h3 class="text-lg font-bold text-slate-900 font-mono mt-1">
                            {{ (stats.total_transaksi || 0).toLocaleString('id-ID') }}
                        </h3>
                        <p class="text-[10px] text-outline mt-1 leading-tight">
                            {{ stats.today_count || 0 }} transaksi hari ini
                        </p>
                    </div>
                </div>

                <!-- Card 2: Pendapatan Jasa -->
                <div class="bg-white p-4 border border-outline-variant rounded-xl flex flex-col justify-between group hover:border-orange-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-orange-50 rounded-lg text-orange-700">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-[10px] flex items-center gap-1">
                            BUMDes
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-xs font-medium">Pendapatan Jasa</p>
                        <h3 class="text-lg font-bold text-slate-900 font-mono mt-1">
                            {{ formatRupiah(stats.total_pendapatan_jasa) }}
                        </h3>
                        <p class="text-[10px] text-outline mt-1 leading-tight">
                            Komisi / laba jasa agen
                        </p>
                    </div>
                </div>

                <!-- Card 3: Biaya Operasional -->
                <div class="bg-white p-4 border border-outline-variant rounded-xl flex flex-col justify-between hover:border-indigo-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-indigo-50 rounded-lg text-indigo-700">
                            <span class="material-symbols-outlined">price_change</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-[10px]">
                            Beban
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-xs font-medium">Biaya Operasional</p>
                        <h3 class="text-lg font-bold text-slate-900 font-mono mt-1">
                            {{ formatRupiah(stats.biaya_operasional) }}
                        </h3>
                        <p class="text-[10px] text-outline mt-1 leading-tight">
                            Pengeluaran operasional unit
                        </p>
                    </div>
                </div>

                <!-- Card 4: Beban Biaya Admin Bank (Permintaan Klien) -->
                <div class="bg-white p-4 border border-outline-variant rounded-xl flex flex-col justify-between hover:border-rose-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-rose-50 rounded-lg text-rose-700">
                            <span class="material-symbols-outlined">account_balance</span>
                        </div>
                        <span class="text-rose-700 font-semibold text-[10px] bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100">
                            Bank
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-xs font-medium">Beban Admin Bank</p>
                        <h3 class="text-lg font-bold text-rose-700 font-mono mt-1">
                            {{ formatRupiah(stats.beban_admin_bank) }}
                        </h3>
                        <p class="text-[10px] text-outline mt-1 leading-tight">
                            Biaya potongan & adm bank
                        </p>
                    </div>
                </div>

                <!-- Card 5: Laba Bersih Unit -->
                <div class="bg-white p-4 border border-outline-variant rounded-xl flex flex-col justify-between hover:border-teal-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-teal-50 rounded-lg text-teal-700">
                            <span class="material-symbols-outlined">query_stats</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-[10px]">
                            Bersih
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-xs font-medium">Laba Bersih Unit</p>
                        <h3 class="text-lg font-bold text-slate-900 font-mono mt-1">
                            {{ formatRupiah(stats.laba_bersih) }}
                        </h3>
                        <p class="text-[10px] text-outline mt-1 leading-tight">
                            Pendapatan jasa - operasional
                        </p>
                    </div>
                </div>

                <!-- Card 6: Hero Navy Card (Total Saldo Kas & Rekening) -->
                <div class="bg-primary text-white p-4 rounded-xl flex flex-col justify-between relative overflow-hidden shadow-sm">
                    <div class="absolute -right-4 -bottom-4 opacity-10">
                        <span class="material-symbols-outlined text-[80px]">account_balance_wallet</span>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="p-2.5 bg-white/20 rounded-lg">
                            <span class="material-symbols-outlined">account_balance_wallet</span>
                        </div>
                        <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                            Kas & Bank
                        </span>
                    </div>
                    <div class="mt-4 relative z-10">
                        <p class="text-blue-100 text-xs font-medium">Total Kas & Saldo</p>
                        <h3 class="text-lg font-bold font-mono mt-1">
                            {{ formatRupiah(stats.total_saldo) }}
                        </h3>
                        <p class="text-[10px] text-blue-100 mt-1 leading-tight">
                            Tunai: {{ formatRupiah(stats.saldo_cash) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Komposisi Pendapatan Layanan (8 cols) + Info Periode (4 cols) -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Komposisi Pendapatan Layanan (8 cols) -->
                <div class="col-span-12 lg:col-span-8 bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-base font-bold text-slate-800">Komposisi Pendapatan Layanan</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Kontribusi pendapatan jasa dari 4 subunit layanan {{ periodLabel.toLowerCase() }}.</p>
                        </div>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold">
                            <span class="material-symbols-outlined text-sm">pie_chart</span>
                            <span>Rasio Subunit</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                        <!-- Donut Chart (5 cols) -->
                        <div class="md:col-span-5 relative flex items-center justify-center py-2">
                            <PbRevenueCompositionChart :data="stats.layanan_breakdown" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Total Jasa</span>
                                <span class="text-xs font-bold text-slate-800 font-mono mt-0.5">{{ formatRupiah(stats.total_pendapatan_jasa) }}</span>
                            </div>
                        </div>

                        <!-- Breakdown Legend List (7 cols) -->
                        <div class="md:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Laku Pandai -->
                            <div class="p-3 rounded-lg border border-outline-variant bg-[#f8fafc] flex flex-col justify-between hover:border-blue-300 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                        <span class="text-xs font-semibold text-slate-700">Laku Pandai</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100">
                                        {{ getPercentage(stats.layanan_breakdown?.laku_pandai?.pendapatan) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-baseline justify-between">
                                    <span class="text-xs font-bold font-mono text-slate-900">{{ formatRupiah(stats.layanan_breakdown?.laku_pandai?.pendapatan) }}</span>
                                    <span class="text-[10px] text-slate-400">{{ stats.layanan_breakdown?.laku_pandai?.count || 0 }} tx</span>
                                </div>
                            </div>

                            <!-- Samsat Budiman -->
                            <div class="p-3 rounded-lg border border-outline-variant bg-[#f8fafc] flex flex-col justify-between hover:border-amber-300 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        <span class="text-xs font-semibold text-slate-700">Samsat Budiman</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100">
                                        {{ getPercentage(stats.layanan_breakdown?.samsat?.pendapatan) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-baseline justify-between">
                                    <span class="text-xs font-bold font-mono text-slate-900">{{ formatRupiah(stats.layanan_breakdown?.samsat?.pendapatan) }}</span>
                                    <span class="text-[10px] text-slate-400">{{ stats.layanan_breakdown?.samsat?.count || 0 }} tx</span>
                                </div>
                            </div>

                            <!-- Pajak PBB -->
                            <div class="p-3 rounded-lg border border-outline-variant bg-[#f8fafc] flex flex-col justify-between hover:border-emerald-300 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs font-semibold text-slate-700">Pajak PBB</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                        {{ getPercentage(stats.layanan_breakdown?.pbb?.pendapatan) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-baseline justify-between">
                                    <span class="text-xs font-bold font-mono text-slate-900">{{ formatRupiah(stats.layanan_breakdown?.pbb?.pendapatan) }}</span>
                                    <span class="text-[10px] text-slate-400">{{ stats.layanan_breakdown?.pbb?.count || 0 }} tx</span>
                                </div>
                            </div>

                            <!-- Transaksi Umum -->
                            <div class="p-3 rounded-lg border border-outline-variant bg-[#f8fafc] flex flex-col justify-between hover:border-indigo-300 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                        <span class="text-xs font-semibold text-slate-700">Transaksi Umum</span>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                        {{ getPercentage(stats.layanan_breakdown?.umum?.pendapatan) }}
                                    </span>
                                </div>
                                <div class="mt-2.5 flex items-baseline justify-between">
                                    <span class="text-xs font-bold font-mono text-slate-900">{{ formatRupiah(stats.layanan_breakdown?.umum?.pendapatan) }}</span>
                                    <span class="text-[10px] text-slate-400">{{ stats.layanan_breakdown?.umum?.count || 0 }} tx</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Period Stats (4 cols) -->
                <div class="col-span-12 lg:col-span-4 bg-white border border-outline-variant rounded-xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="text-base font-bold text-slate-800 mb-4">{{ periodLabel }}</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-outline-variant">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-xl">sync_alt</span>
                                    <span class="text-sm font-medium text-slate-700">Total Transaksi</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-outline-variant text-xs">
                                    {{ stats.total_transaksi || 0 }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-outline-variant">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-emerald-700 text-xl">payments</span>
                                    <span class="text-sm font-medium text-slate-700">Kas Tunai</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-outline-variant text-xs">
                                    {{ formatRupiah(stats.saldo_cash) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-outline-variant">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-blue-700 text-xl">account_balance</span>
                                    <span class="text-sm font-medium text-slate-700">Rekening Bank</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-outline-variant text-xs">
                                    {{ formatRupiah(stats.saldo_rekening) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-outline-variant flex items-center justify-between text-xs text-slate-500">
                        <span>Status Kas Harian</span>
                        <span class="font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Terkontrol
                        </span>
                    </div>
                </div>
            </div>

            <!-- Monitoring & Recent Transactions Section -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Monitoring Layanan (6 cols) -->
                <div class="col-span-12 lg:col-span-6 bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="mb-4">
                        <h4 class="text-base font-bold text-slate-800">Kinerja Layanan Transaksi</h4>
                        <p class="text-xs text-slate-500 mt-1">Breakdown volume dan pendapatan per subunit layanan.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Laku Pandai -->
                        <Link
                            :href="route('pb.laku-pandai.index')"
                            class="flex items-center justify-between p-3.5 rounded-xl border border-blue-200 bg-blue-50 text-slate-900 hover:bg-blue-100 hover:border-blue-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Laku Pandai</span>
                                <span class="text-lg font-bold font-mono mt-0.5">
                                    {{ formatRupiah(stats.layanan_breakdown?.laku_pandai?.pendapatan) }}
                                </span>
                                <span class="text-[10px] text-slate-500 mt-0.5">
                                    {{ stats.layanan_breakdown?.laku_pandai?.count || 0 }} transaksi tercatat
                                </span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>

                        <!-- Samsat Budiman -->
                        <Link
                            :href="route('pb.samsat.index')"
                            class="flex items-center justify-between p-3.5 rounded-xl border border-sky-200 bg-sky-50 text-slate-900 hover:bg-sky-100 hover:border-sky-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Samsat Budiman</span>
                                <span class="text-lg font-bold font-mono mt-0.5">
                                    {{ formatRupiah(stats.layanan_breakdown?.samsat?.pendapatan) }}
                                </span>
                                <span class="text-[10px] text-slate-500 mt-0.5">
                                    {{ stats.layanan_breakdown?.samsat?.count || 0 }} transaksi tercatat
                                </span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>

                        <!-- Pajak PBB -->
                        <Link
                            :href="route('pb.pbb.index')"
                            class="flex items-center justify-between p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/70 text-slate-900 hover:bg-indigo-100 hover:border-indigo-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pajak PBB</span>
                                <span class="text-lg font-bold font-mono mt-0.5">
                                    {{ formatRupiah(stats.layanan_breakdown?.pbb?.pendapatan) }}
                                </span>
                                <span class="text-[10px] text-slate-500 mt-0.5">
                                    {{ stats.layanan_breakdown?.pbb?.count || 0 }} transaksi tercatat
                                </span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>

                        <!-- Transaksi Umum -->
                        <Link
                            :href="route('pb.transaksi-umum.index')"
                            class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 hover:bg-slate-100 hover:border-slate-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer"
                        >
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Transaksi Umum</span>
                                <span class="text-lg font-bold font-mono mt-0.5">
                                    {{ formatRupiah(stats.layanan_breakdown?.umum?.pendapatan) }}
                                </span>
                                <span class="text-[10px] text-slate-500 mt-0.5">
                                    {{ stats.layanan_breakdown?.umum?.count || 0 }} transaksi tercatat
                                </span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Transactions Table (6 cols) -->
                <div class="col-span-12 lg:col-span-6 bg-white border border-outline-variant rounded-xl overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="px-5 py-4 border-b border-outline-variant flex justify-between items-center bg-white">
                            <h4 class="text-base font-bold text-slate-800">Transaksi Terakhir</h4>
                            <Link :href="route('pb.laporan.index')" class="text-xs text-primary font-semibold hover:underline">
                                Semua Transaksi
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-white text-slate-500 font-bold uppercase border-b border-outline-variant">
                                    <tr>
                                        <th class="px-5 py-3">Nasabah / WP</th>
                                        <th class="px-5 py-3">Layanan</th>
                                        <th class="px-5 py-3 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant">
                                    <tr v-for="tx in stats.recent" :key="tx.id" class="hover:bg-[#f8fafc]">
                                        <td class="px-5 py-3 font-semibold text-slate-700">
                                            <div>{{ tx.nama }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ tx.kode }}</div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span
                                                :class="[
                                                    'px-2 py-0.5 rounded border text-[10px] font-bold uppercase',
                                                    tx.layanan === 'Laku Pandai' ? 'bg-blue-50 text-blue-700 border-blue-100' :
                                                    tx.layanan === 'Samsat Budiman' ? 'bg-orange-50 text-orange-700 border-orange-100' :
                                                    tx.layanan === 'PBB' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' :
                                                    'bg-indigo-50 text-indigo-700 border-indigo-100'
                                                ]"
                                            >
                                                {{ tx.layanan }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">
                                            {{ formatRupiah(tx.nominal) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!stats.recent || stats.recent.length === 0">
                                        <td colspan="3" class="px-5 py-8 text-center text-slate-400">
                                            Belum ada transaksi hari ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>
