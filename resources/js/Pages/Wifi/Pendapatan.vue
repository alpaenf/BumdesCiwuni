<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    unit:                 { type: Object, required: true },
    user:                 { type: Object, required: true },
    tahun:                { type: Number, required: true },
    bulan:                { type: [Number, null], default: null },
    tanggal:              { type: [String, null], default: null },
    tahunOptions:         { type: Array,  default: () => [] },
    bulanOptions:         { type: Array,  default: () => [] },
    pendapatanPersentase: { type: Number, default: 0 },
    pendapatanAdminFlat:  { type: Number, default: 0 },
    totalTarikanBruto:    { type: Number, default: 0 },
    totalHakProvider:     { type: Number, default: 0 },
    pendapatanKotor:      { type: Number, default: 0 },
    distribusi:           { type: Array,  default: () => [] },
    biayaGaji:            { type: Number, default: 560000 },
    biayaOps:             { type: Number, default: 240000 },
    biayaAsuransi:        { type: Number, default: 40000 },
    penarikanLaba:        { type: Number, default: 0 },
    detailPersentase:     { type: Array,  default: () => [] },
    detailAdminFlat:      { type: Array,  default: () => [] },
    detailSemua:          { type: Array,  default: () => [] },
    bulanNama:            { type: String, default: 'Semua Bulan' },
});

const isSidebarOpen   = ref(false);
const logout          = () => router.post(route('logout'));

const selectedTahun   = ref(props.tahun);
const selectedBulan   = ref(props.bulan ?? '');
const selectedTanggal = ref(props.tanggal ?? '');

const inputBiayaGaji     = ref(props.biayaGaji ?? 560000);
const inputBiayaOps      = ref(props.biayaOps ?? 240000);
const inputBiayaAsuransi = ref(props.biayaAsuransi ?? 40000);
const inputPenarikanLaba = ref(props.penarikanLaba ?? 0);

const activeTab = ref('persentase'); // 'persentase' | 'admin_flat' | 'semua'

const applyFilter = () => {
    const params = { tahun: selectedTahun.value };
    if (selectedBulan.value)   params.bulan   = selectedBulan.value;
    if (selectedTanggal.value) params.tanggal = selectedTanggal.value;
    params.biaya_gaji     = inputBiayaGaji.value;
    params.biaya_ops      = inputBiayaOps.value;
    params.biaya_asuransi = inputBiayaAsuransi.value;
    params.penarikan_laba = inputPenarikanLaba.value;
    router.get(route('wifi.pendapatan.index'), params, { preserveState: true });
};

const rupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
    }).format(val);
};

const formatDate = (val) => {
    if (!val || val === '-') return '-';
    try {
        return new Date(val).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch { return val; }
};

const exportFilters = computed(() => {
    const payload = {};
    if (selectedTahun.value)   payload.tahun   = selectedTahun.value;
    if (selectedBulan.value)   payload.bulan   = selectedBulan.value;
    if (selectedTanggal.value) payload.tanggal = selectedTanggal.value;
    payload.biaya_gaji     = inputBiayaGaji.value;
    payload.biaya_ops      = inputBiayaOps.value;
    payload.biaya_asuransi = inputBiayaAsuransi.value;
    payload.penarikan_laba = inputPenarikanLaba.value;
    return payload;
});

const totalPengambilanCalc = computed(() => {
    return Number(inputBiayaGaji.value) + Number(inputBiayaOps.value) + Number(inputBiayaAsuransi.value) + Number(inputPenarikanLaba.value);
});

const labaBersihCalc = computed(() => {
    return props.pendapatanKotor - totalPengambilanCalc.value;
});
</script>

<template>
    <Head title="Pendapatan Kotor - Unit WiFi" />

    <div class="min-h-screen bg-slate-50 text-slate-800 flex font-sans">

        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- ══ SIDEBAR ════════════════════════════════════════════════════════ -->
        <aside :class="['fixed md:sticky top-0 h-screen z-50 w-64 bg-white border-r border-slate-300 shrink-0 flex flex-col transition-transform duration-300',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <!-- Branding -->
            <div class="p-6 pb-4 shrink-0 flex items-center justify-between gap-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="/logo2.png" alt="Logo WiFi" class="w-10 h-10 object-contain drop-shadow-sm" onerror="this.src='/logowifi.png'" />
                    <div>
                        <h2 class="text-xs font-black text-slate-900 leading-tight">Admin Unit WiFi</h2>
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                    </div>
                </div>
                <button @click="isSidebarOpen = false" aria-label="Tutup sidebar"
                        class="md:hidden text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Nav Links -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <nav class="space-y-1">
                    <Link :href="route('unit.dashboard', { slug: unit.slug })"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </Link>
                    <Link :href="route('wifi.pelanggan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">group</span>
                        Pelanggan
                    </Link>
                    <Link :href="route('wifi.peta')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">map</span>
                        Peta Pelanggan
                    </Link>
                    <Link :href="route('wifi.pembayaran.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Kasir &amp; Pembayaran
                    </Link>
                    <Link :href="route('wifi.provider.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">cell_tower</span>
                        Master Provider
                    </Link>
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        Pendapatan Kotor
                    </a>
                    <Link :href="route('wifi.laporan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">summarize</span>
                        Laporan WiFi
                    </Link>
                    <Link :href="route('unit.settings.edit', { slug: unit.slug })"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">web</span>
                        Pengaturan Landing Page
                    </Link>

                    <!-- Section Portal BUMDes -->
                    <div v-if="(user?.role || $page?.props?.auth?.user?.role) === 'admin' || (user?.role || $page?.props?.auth?.user?.role) === 'manager_pusat'" class="pt-4 mt-4 border-t border-slate-200 space-y-1">
                        <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Portal BUMDes</p>
                        <Link :href="route('portal.exec.dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">hub</span>
                            Dashboard Portal
                        </Link>
                        <Link v-if="(user?.role || $page?.props?.auth?.user?.role) === 'admin'" :href="route('portal.cms.dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">edit_note</span>
                            Kelola Website
                        </Link>
                        <Link :href="route('dashboard')"
                              class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">account_balance</span>
                            Unit Simpan Pinjam
                        </Link>
                    </div>
                </nav>
            </div>

            <!-- Profile & Logout -->
            <div class="p-6 border-t border-slate-300 shrink-0 space-y-4 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout"
                        class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:border-red-200 text-slate-600 hover:text-red-600 text-xs font-bold rounded-xl transition">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    Keluar Aplikasi
                </button>
            </div>
        </aside>

        <!-- ══ MAIN CONTENT ═══════════════════════════════════════════════════ -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">

            <!-- Top Header Bar -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-300 bg-white/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="isSidebarOpen = true" aria-label="Buka sidebar"
                            class="md:hidden p-1.5 -ml-2 text-slate-500 hover:text-slate-800 rounded-lg transition mr-1">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="material-symbols-outlined text-blue-600 text-xl hidden sm:block">account_balance_wallet</span>
                    <div>
                        <h1 class="text-sm font-black text-slate-900 leading-tight">Pendapatan Kotor Unit WiFi</h1>
                        <p class="text-[10px] text-slate-500 hidden sm:block">Analisis bagi hasil 9% &amp; biaya admin flat dari pelanggan</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('unit.welcome', { slug: 'wifi' })" target="_blank"
                          class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1 bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-full transition">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span class="hidden sm:inline">Landing Page WiFi</span>
                    </Link>
                </div>
            </header>

            <!-- Page Body -->
            <div class="p-4 sm:p-6 space-y-6 max-w-7xl w-full mx-auto pb-16">

                <!-- Filter Card & Actions -->
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ringkasan Pendapatan Kotor WiFi</h2>
                        <p class="text-xs text-slate-500">
                            Periode: <span class="font-semibold text-slate-700">{{ selectedTanggal ? formatDate(selectedTanggal) : (bulanNama + ' ' + tahun) }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a :href="route('wifi.pendapatan.pdf', exportFilters)" target="_blank"
                           class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 flex items-center gap-1.5 shadow-sm transition">
                            <span class="material-symbols-outlined text-sm text-red-500">picture_as_pdf</span> Cetak PDF
                        </a>
                        <a :href="route('wifi.pendapatan.excel', exportFilters)"
                           class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 flex items-center gap-1.5 shadow-sm transition">
                            <span class="material-symbols-outlined text-sm text-emerald-600">description</span> Export Excel
                        </a>
                        
                        <!-- Filter Harian Tanggal -->
                        <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2.5 py-1 shadow-sm">
                            <span class="material-symbols-outlined text-xs text-slate-400">calendar_today</span>
                            <input type="date" v-model="selectedTanggal" @change="() => { selectedBulan = ''; applyFilter(); }"
                                   class="border-0 bg-transparent py-1 text-xs font-medium text-slate-800 focus:ring-0 focus:outline-none" />
                            <button v-if="selectedTanggal" @click="() => { selectedTanggal = ''; applyFilter(); }"
                                    class="text-[10px] text-red-500 font-bold hover:underline ml-1">Reset</button>
                        </div>

                        <!-- Filter Bulan & Tahun -->
                        <select v-model="selectedBulan" @change="() => { selectedTanggal = ''; applyFilter(); }"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option v-for="b in bulanOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                        </select>
                        <select v-model="selectedTahun" @change="applyFilter"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option v-for="y in tahunOptions" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>

                <!-- Pendapatan Kotor Total Hero Banner -->
                <div class="rounded-3xl bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="p-2 rounded-xl bg-white/10 backdrop-blur-md">
                                <span class="material-symbols-outlined text-2xl text-blue-200">account_balance_wallet</span>
                            </div>
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-blue-200">Total Pendapatan Kotor WiFi {{ tahun }}</p>
                                <p class="text-[11px] text-blue-300">Hak Bersih BUMDes sebelum dikurangi biaya operasional &amp; gaji</p>
                            </div>
                        </div>
                        <p class="text-3xl sm:text-4xl font-black mt-3 tracking-tight">{{ rupiah(pendapatanKotor) }}</p>
                        
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Box 1: Persentase (9%) -->
                            <div class="rounded-2xl bg-white/10 backdrop-blur-md p-4 border border-white/10 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-blue-200 mb-1">
                                        <span class="text-xs font-bold uppercase tracking-wider">Skema Persentase (9%)</span>
                                        <span class="material-symbols-outlined text-sm">percent</span>
                                    </div>
                                    <p class="text-xl font-extrabold">{{ rupiah(pendapatanPersentase) }}</p>
                                </div>
                                <p class="text-[10px] text-blue-200/80 mt-2 font-medium">
                                    {{ pendapatanKotor > 0 ? ((pendapatanPersentase / pendapatanKotor) * 100).toFixed(1) : 0 }}% dari pendapatan kotor
                                </p>
                            </div>

                            <!-- Box 2: Admin Flat -->
                            <div class="rounded-2xl bg-white/10 backdrop-blur-md p-4 border border-white/10 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-emerald-200 mb-1">
                                        <span class="text-xs font-bold uppercase tracking-wider">Skema Admin Flat</span>
                                        <span class="material-symbols-outlined text-sm">toll</span>
                                    </div>
                                    <p class="text-xl font-extrabold">{{ rupiah(pendapatanAdminFlat) }}</p>
                                </div>
                                <p class="text-[10px] text-emerald-200/80 mt-2 font-medium">
                                    {{ pendapatanKotor > 0 ? ((pendapatanAdminFlat / pendapatanKotor) * 100).toFixed(1) : 0 }}% dari pendapatan kotor
                                </p>
                            </div>

                            <!-- Box 3: Total Tarikan Bruto -->
                            <div class="rounded-2xl bg-white/5 backdrop-blur-md p-4 border border-white/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-slate-300 mb-1">
                                        <span class="text-xs font-bold uppercase tracking-wider">Total Tarikan (Omset)</span>
                                        <span class="material-symbols-outlined text-sm">point_of_sale</span>
                                    </div>
                                    <p class="text-xl font-bold">{{ rupiah(totalTarikanBruto) }}</p>
                                </div>
                                <p class="text-[10px] text-slate-300/80 mt-2">Semua iuran yang dibayar pelanggan</p>
                            </div>

                            <!-- Box 4: Hak Provider ISP -->
                            <div class="rounded-2xl bg-white/5 backdrop-blur-md p-4 border border-white/5 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-indigo-200 mb-1">
                                        <span class="text-xs font-bold uppercase tracking-wider">Hak Provider ISP</span>
                                        <span class="material-symbols-outlined text-sm">cell_tower</span>
                                    </div>
                                    <p class="text-xl font-bold">{{ rupiah(totalHakProvider) }}</p>
                                </div>
                                <p class="text-[10px] text-indigo-200/80 mt-2">Penyaluran ke ISP mitra BUMDes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rincian Pengurangan Pendapatan -->
                <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-7 shadow-sm">
                    <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg text-blue-600">receipt_long</span>
                                Rincian Pengurangan Pendapatan &amp; Laba Bersih
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Penyesuaian biaya operasional unit WiFi untuk mengetahui laba bersih riil</p>
                        </div>
                        <button @click="applyFilter" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-sm">
                            <span class="material-symbols-outlined text-sm">save</span> Terapkan Nilai Manual
                        </button>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Biaya Gaji -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Biaya Gaji Pengelola &amp; Teknisi</span>
                                    <p class="text-[10px] text-slate-400">Honor petugas operasional dan penanganan jaringan</p>
                                </div>
                            </div>
                            <div class="w-full sm:w-64 flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500">Rp</span>
                                <input type="number" v-model="inputBiayaGaji" class="w-full text-right text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50" />
                            </div>
                        </div>
                        
                        <!-- Biaya Operasional -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Biaya Operasional WiFi</span>
                                    <p class="text-[10px] text-slate-400">Listrik server/OLT, transportasi teknisi, &amp; konsumsi</p>
                                </div>
                            </div>
                            <div class="w-full sm:w-64 flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500">Rp</span>
                                <input type="number" v-model="inputBiayaOps" class="w-full text-right text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50" />
                            </div>
                        </div>

                        <!-- Biaya Pemeliharaan / Asuransi -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Biaya Pemeliharaan &amp; Asuransi Perangkat</span>
                                    <p class="text-[10px] text-slate-400">Cadangan penggantian router, kabel fiber, &amp; splitter rusak</p>
                                </div>
                            </div>
                            <div class="w-full sm:w-64 flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500">Rp</span>
                                <input type="number" v-model="inputBiayaAsuransi" class="w-full text-right text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50" />
                            </div>
                        </div>

                        <!-- Penarikan Laba / Lainnya -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Penarikan Laba / Pengeluaran Lainnya</span>
                                    <p class="text-[10px] text-slate-400">Penyetoran PADes atau pengeluaran tak terduga</p>
                                </div>
                            </div>
                            <div class="w-full sm:w-64 flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500">Rp</span>
                                <input type="number" v-model="inputPenarikanLaba" class="w-full text-right text-xs font-bold text-slate-800 border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 bg-slate-50/50" />
                            </div>
                        </div>

                        <!-- Total Pengambilan -->
                        <div class="flex items-center justify-between pb-2 border-t-2 border-slate-100 pt-3 mt-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-red-500">logout</span>
                                <span class="text-xs font-bold text-red-600">Total Pengambilan / Beban Usaha</span>
                            </div>
                            <div class="text-right text-sm font-black text-red-600">
                                {{ rupiah(totalPengambilanCalc) }}
                            </div>
                        </div>

                        <!-- Laba Bersih BUMDes -->
                        <div class="flex items-center justify-between pb-2">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-bold text-emerald-700">Laba Bersih BUMDes (Setelah Beban)</span>
                            </div>
                            <div class="text-right text-base font-black text-emerald-700">
                                {{ rupiah(labaBersihCalc) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 flex items-center justify-between border-t-2 border-slate-100 text-xs font-extrabold text-slate-900">
                        <span>Total Pendapatan Kotor BUMDes</span>
                        <span class="text-blue-700 font-black text-sm">{{ rupiah(pendapatanKotor) }}</span>
                    </div>
                </div>

                <!-- Detail Data Transaksi Tabs -->
                <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-7 shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-5 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button @click="activeTab = 'persentase'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                                    :class="activeTab === 'persentase' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
                                <span class="material-symbols-outlined text-sm">percent</span>
                                Skema Persentase (9%)
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'persentase' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                                    {{ detailPersentase.length }}
                                </span>
                            </button>

                            <button @click="activeTab = 'admin_flat'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                                    :class="activeTab === 'admin_flat' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
                                <span class="material-symbols-outlined text-sm">toll</span>
                                Skema Admin Flat
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'admin_flat' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                                    {{ detailAdminFlat.length }}
                                </span>
                            </button>

                            <button @click="activeTab = 'semua'"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                                    :class="activeTab === 'semua' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'">
                                <span class="material-symbols-outlined text-sm">list_alt</span>
                                Semua Transaksi
                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">
                                    {{ detailSemua.length }}
                                </span>
                            </button>
                        </div>

                        <span class="text-xs text-slate-400 font-medium">
                            Menampilkan riwayat penerimaan pendapatan kotor
                        </span>
                    </div>

                    <!-- TAB 1: SKEMA PERSENTASE -->
                    <div v-if="activeTab === 'persentase'">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Detail Pembayaran Skema Persentase (9%)</h4>
                            <span class="text-xs text-slate-500 font-medium">Total: {{ detailPersentase.length }} transaksi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] bg-slate-50/50">
                                        <th class="py-2.5 px-3">Tanggal</th>
                                        <th class="py-2.5 px-3">No. Struk</th>
                                        <th class="py-2.5 px-3">Pelanggan</th>
                                        <th class="py-2.5 px-3">Provider</th>
                                        <th class="py-2.5 px-3">Paket</th>
                                        <th class="py-2.5 px-3 text-right">Total Tarikan</th>
                                        <th class="py-2.5 px-3 text-center">Bagi Hasil</th>
                                        <th class="py-2.5 px-3 text-right">Hak BUMDes</th>
                                        <th class="py-2.5 px-3 text-right">Hak Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in detailPersentase" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                                        <td class="py-3 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">#{{ item.no_transaksi }}</td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900">{{ item.pelanggan }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ item.no_id_pel }}</div>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-slate-700 whitespace-nowrap">{{ item.provider }}</td>
                                        <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">{{ item.paket }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">{{ rupiah(item.total_tarikan) }}</td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[10px] font-bold">
                                                {{ item.nilai_skema }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 whitespace-nowrap">{{ rupiah(item.hak_bumdes) }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">{{ rupiah(item.hak_provider) }}</td>
                                    </tr>
                                    <tr v-if="detailPersentase.length === 0">
                                        <td colspan="9" class="py-8 text-center text-slate-400">
                                            Tidak ada transaksi dengan skema persentase pada periode yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="detailPersentase.length > 0">
                                    <tr class="border-t-2 border-slate-200 font-bold bg-slate-50/80">
                                        <td colspan="5" class="py-3 px-3 text-slate-900 uppercase">Total Skema Persentase</td>
                                        <td class="py-3 px-3 text-right font-mono text-slate-900">{{ rupiah(detailPersentase.reduce((acc, c) => acc + c.total_tarikan, 0)) }}</td>
                                        <td></td>
                                        <td class="py-3 px-3 text-right font-mono text-emerald-700 font-black">{{ rupiah(pendapatanPersentase) }}</td>
                                        <td class="py-3 px-3 text-right font-mono text-indigo-600">{{ rupiah(detailPersentase.reduce((acc, c) => acc + c.hak_provider, 0)) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: SKEMA ADMIN FLAT -->
                    <div v-if="activeTab === 'admin_flat'">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Detail Pembayaran Skema Admin Flat</h4>
                            <span class="text-xs text-slate-500 font-medium">Total: {{ detailAdminFlat.length }} transaksi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] bg-slate-50/50">
                                        <th class="py-2.5 px-3">Tanggal</th>
                                        <th class="py-2.5 px-3">No. Struk</th>
                                        <th class="py-2.5 px-3">Pelanggan</th>
                                        <th class="py-2.5 px-3">Provider</th>
                                        <th class="py-2.5 px-3">Paket</th>
                                        <th class="py-2.5 px-3 text-right">Total Tarikan</th>
                                        <th class="py-2.5 px-3 text-center">Admin Flat</th>
                                        <th class="py-2.5 px-3 text-right">Hak BUMDes</th>
                                        <th class="py-2.5 px-3 text-right">Hak Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in detailAdminFlat" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                                        <td class="py-3 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">#{{ item.no_transaksi }}</td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900">{{ item.pelanggan }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ item.no_id_pel }}</div>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-slate-700 whitespace-nowrap">{{ item.provider }}</td>
                                        <td class="py-3 px-3 font-mono text-slate-600 whitespace-nowrap">{{ item.paket }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">{{ rupiah(item.total_tarikan) }}</td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold">
                                                {{ item.nilai_skema }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 whitespace-nowrap">{{ rupiah(item.hak_bumdes) }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">{{ rupiah(item.hak_provider) }}</td>
                                    </tr>
                                    <tr v-if="detailAdminFlat.length === 0">
                                        <td colspan="9" class="py-8 text-center text-slate-400">
                                            Tidak ada transaksi dengan skema admin flat pada periode yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="detailAdminFlat.length > 0">
                                    <tr class="border-t-2 border-slate-200 font-bold bg-slate-50/80">
                                        <td colspan="5" class="py-3 px-3 text-slate-900 uppercase">Total Skema Admin Flat</td>
                                        <td class="py-3 px-3 text-right font-mono text-slate-900">{{ rupiah(detailAdminFlat.reduce((acc, c) => acc + c.total_tarikan, 0)) }}</td>
                                        <td></td>
                                        <td class="py-3 px-3 text-right font-mono text-emerald-700 font-black">{{ rupiah(pendapatanAdminFlat) }}</td>
                                        <td class="py-3 px-3 text-right font-mono text-indigo-600">{{ rupiah(detailAdminFlat.reduce((acc, c) => acc + c.hak_provider, 0)) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: SEMUA TRANSAKSI -->
                    <div v-if="activeTab === 'semua'">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Semua Pembayaran WiFi Masuk</h4>
                            <span class="text-xs text-slate-500 font-medium">Total: {{ detailSemua.length }} transaksi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] bg-slate-50/50">
                                        <th class="py-2.5 px-3">Tanggal</th>
                                        <th class="py-2.5 px-3">No. Struk</th>
                                        <th class="py-2.5 px-3">Pelanggan</th>
                                        <th class="py-2.5 px-3">Provider</th>
                                        <th class="py-2.5 px-3 text-right">Total Tarikan</th>
                                        <th class="py-2.5 px-3 text-center">Skema</th>
                                        <th class="py-2.5 px-3 text-right">Hak BUMDes</th>
                                        <th class="py-2.5 px-3 text-right">Hak Provider</th>
                                        <th class="py-2.5 px-3 text-center">Metode</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in detailSemua" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                                        <td class="py-3 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">#{{ item.no_transaksi }}</td>
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900">{{ item.pelanggan }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ item.no_id_pel }}</div>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-slate-700 whitespace-nowrap">{{ item.provider }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">{{ rupiah(item.total_tarikan) }}</td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span :class="item.skema === 'PERSENTASE' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200'"
                                                  class="px-2 py-0.5 border rounded text-[10px] font-bold">
                                                {{ item.nilai_skema }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-black text-emerald-700 whitespace-nowrap">{{ rupiah(item.hak_bumdes) }}</td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-indigo-600 whitespace-nowrap">{{ rupiah(item.hak_provider) }}</td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-semibold">
                                                {{ item.metode }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="detailSemua.length === 0">
                                        <td colspan="9" class="py-8 text-center text-slate-400">
                                            Belum ada data transaksi pembayaran WiFi pada periode yang dipilih.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="detailSemua.length > 0">
                                    <tr class="border-t-2 border-slate-200 font-bold bg-slate-50/80">
                                        <td colspan="4" class="py-3 px-3 text-slate-900 uppercase">Total Keseluruhan</td>
                                        <td class="py-3 px-3 text-right font-mono text-slate-900">{{ rupiah(totalTarikanBruto) }}</td>
                                        <td></td>
                                        <td class="py-3 px-3 text-right font-mono text-emerald-700 font-black">{{ rupiah(pendapatanKotor) }}</td>
                                        <td class="py-3 px-3 text-right font-mono text-indigo-600">{{ rupiah(totalHakProvider) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
