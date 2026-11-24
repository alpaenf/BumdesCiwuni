<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    unit:             { type: Object, required: true },
    user:             { type: Object, required: true },
    providersList:    { type: Array,  default: () => [] },
    pelangganList:    { type: Array,  default: () => [] },
    rekapPerProvider: { type: Array,  default: () => [] },
    filters:          { type: Object, default: () => ({}) },
    stats:            { type: Object, default: () => ({}) },
});

const isSidebarOpen = ref(false);
const logout = () => router.post(route('logout'));

const selectedBulan    = ref(props.filters.bulan    ?? new Date().getMonth() + 1);
const selectedTahun    = ref(props.filters.tahun    ?? new Date().getFullYear());
const selectedProvider = ref(props.filters.provider_id ?? '');
const selectedTglDari  = ref(props.filters.tanggal_dari ?? '');
const selectedTglSampai = ref(props.filters.tanggal_sampai ?? '');

const activeTabStatus = ref(props.filters.status || 'terpisah'); // 'terpisah', 'lunas', 'isolir'
const searchQuery     = ref('');

const lunasList = computed(() => {
    return props.pelangganList.filter(p => p.status_bayar === 'LUNAS');
});

const isolirList = computed(() => {
    return props.pelangganList.filter(p => p.status_bayar !== 'LUNAS');
});

const filterByQuery = (list) => {
    if (!searchQuery.value || !searchQuery.value.trim()) return list;
    const q = searchQuery.value.toLowerCase().trim();
    return list.filter(item => {
        return (item.nama && item.nama.toLowerCase().includes(q)) ||
               (item.no_id_pel && item.no_id_pel.toLowerCase().includes(q)) ||
               (item.no_wa && item.no_wa.toLowerCase().includes(q)) ||
               (item.alamat && item.alamat.toLowerCase().includes(q)) ||
               (item.paket && item.paket.toLowerCase().includes(q)) ||
               (item.provider?.nama_provider && item.provider.nama_provider.toLowerCase().includes(q));
    });
};

const filteredLunasList = computed(() => filterByQuery(lunasList.value));
const filteredIsolirList = computed(() => filterByQuery(isolirList.value));

const subtotalLunas = computed(() => {
    return filteredLunasList.value.reduce((acc, item) => {
        acc.tarikan  += Number(item.nominal_masuk || item.total_tarikan) || 0;
        acc.bumdes   += Number(item.bumdes_masuk || item.calc_hasil_bumdes || item.hasil_bumdes) || 0;
        acc.provider += Number(item.provider_masuk || item.calc_total_provider || item.total_provider) || 0;
        return acc;
    }, { tarikan: 0, bumdes: 0, provider: 0 });
});

const subtotalIsolir = computed(() => {
    return filteredIsolirList.value.reduce((acc, item) => {
        acc.tarikan  += Number(item.total_tarikan) || 0;
        acc.bumdes   += Number(item.calc_hasil_bumdes || item.hasil_bumdes) || 0;
        acc.provider += Number(item.calc_total_provider || item.total_provider) || 0;
        return acc;
    }, { tarikan: 0, bumdes: 0, provider: 0 });
});

// ── Pagination Pelanggan LUNAS & ISOLIR ───────────────────────────
const perPageLunas = ref(20);
const pageLunas    = ref(1);

const perPageIsolir = ref(20);
const pageIsolir    = ref(1);

const totalLunasPages = computed(() => {
    return Math.ceil(filteredLunasList.value.length / (Number(perPageLunas.value) || 20)) || 1;
});

const totalIsolirPages = computed(() => {
    return Math.ceil(filteredIsolirList.value.length / (Number(perPageIsolir.value) || 20)) || 1;
});

const paginatedLunasList = computed(() => {
    const perPage = Number(perPageLunas.value) || 20;
    const page = Number(pageLunas.value) || 1;
    const start = (page - 1) * perPage;
    return filteredLunasList.value.slice(start, start + perPage);
});

const paginatedIsolirList = computed(() => {
    const perPage = Number(perPageIsolir.value) || 20;
    const page = Number(pageIsolir.value) || 1;
    const start = (page - 1) * perPage;
    return filteredIsolirList.value.slice(start, start + perPage);
});

watch([searchQuery, activeTabStatus, selectedProvider, selectedBulan, selectedTahun, perPageLunas, perPageIsolir], () => {
    pageLunas.value = 1;
    pageIsolir.value = 1;
});

const getPageNumbers = (currentPage, totalPages) => {
    currentPage = Number(currentPage) || 1;
    totalPages = Number(totalPages) || 1;
    if (totalPages <= 7) {
        return Array.from({ length: totalPages }, (_, i) => i + 1);
    }
    const pages = [];
    if (currentPage <= 4) {
        pages.push(1, 2, 3, 4, 5, '...', totalPages);
    } else if (currentPage >= totalPages - 3) {
        pages.push(1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages);
    } else {
        pages.push(1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages);
    }
    return pages;
};

const applyFilters = () => {
    router.get(route('wifi.laporan.index'), {
        bulan:          selectedBulan.value,
        tahun:          selectedTahun.value,
        provider_id:    selectedProvider.value || undefined,
        status:         activeTabStatus.value !== 'terpisah' ? activeTabStatus.value : undefined,
        tanggal_dari:   selectedTglDari.value || undefined,
        tanggal_sampai: selectedTglSampai.value || undefined,
    }, { preserveScroll: true, replace: true });
};

const resetDateRange = () => {
    selectedTglDari.value = '';
    selectedTglSampai.value = '';
    applyFilters();
};

const doExport = () => {
    const params = new URLSearchParams();
    params.set('bulan', selectedBulan.value);
    params.set('tahun', selectedTahun.value);
    if (selectedProvider.value)  params.set('provider_id', selectedProvider.value);
    if (selectedTglDari.value)   params.set('tanggal_dari', selectedTglDari.value);
    if (selectedTglSampai.value) params.set('tanggal_sampai', selectedTglSampai.value);
    if (activeTabStatus.value === 'lunas' || activeTabStatus.value === 'isolir') {
        params.set('status', activeTabStatus.value);
    }
    window.location.href = route('wifi.laporan.export') + '?' + params.toString();
};

const doPrintPDF = () => {
    const params = new URLSearchParams();
    params.set('bulan', selectedBulan.value);
    params.set('tahun', selectedTahun.value);
    if (selectedProvider.value)  params.set('provider_id', selectedProvider.value);
    if (selectedTglDari.value)   params.set('tanggal_dari', selectedTglDari.value);
    if (selectedTglSampai.value) params.set('tanggal_sampai', selectedTglSampai.value);
    if (activeTabStatus.value === 'lunas' || activeTabStatus.value === 'isolir') {
        params.set('status', activeTabStatus.value);
    }
    window.open(route('wifi.laporan.cetak-pdf') + '?' + params.toString(), '_blank');
};

const rupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const namaBulanMap = [
    { id: 1, name: 'Januari' }, { id: 2, name: 'Februari' }, { id: 3, name: 'Maret' },
    { id: 4, name: 'April' },   { id: 5, name: 'Mei' },      { id: 6, name: 'Juni' },
    { id: 7, name: 'Juli' },     { id: 8, name: 'Agustus' },  { id: 9, name: 'September' },
    { id: 10, name: 'Oktober' },{ id: 11, name: 'November' },{ id: 12, name: 'Desember' },
];

const getBulanName = (id) => namaBulanMap.find(b => b.id === id)?.name ?? id;
</script>

<template>
    <Head title="Laporan WiFi Per Provider" />

    <div class="min-h-screen bg-slate-50 text-slate-800 flex font-sans">

        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- ══ SIDEBAR ════════════════════════════════════════════════════════ -->
        <!-- ══ SIDEBAR ════════════════════════════════════════════════════════ -->
        <aside :class="['fixed md:sticky top-0 h-screen z-50 w-64 bg-white border-r border-slate-300 shrink-0 flex flex-col transition-transform duration-300',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <!-- Branding (Fixed top) -->
            <div class="p-6 pb-4 shrink-0 flex items-center justify-between gap-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="/logowifi.png" alt="Logo WiFi" class="w-10 h-10 object-contain drop-shadow-sm" />
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

            <!-- Nav (Scrollable) -->
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
                    <Link :href="route('wifi.pendapatan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        Pendapatan Kotor
                    </Link>
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">summarize</span>
                        Laporan WiFi
                    </a>
                    <Link :href="route('unit.settings.edit', { slug: unit.slug })"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">web</span>
                        Pengaturan Landing Page
                    </Link>

                    <!-- Section Portal BUMDes (Hanya tampil untuk Super Admin / Manager Pusat) -->
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
                        <a :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">agriculture</span>
                            Unit Ketahanan Pangan
                        </a>
                        <a :href="route('unit.welcome', { slug: 'perdagangan-besar' })" target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                            Unit Perdagangan Besar
                        </a>
                    </div>
                </nav>
            </div>

            <!-- User Info & Logout (Fixed bottom) -->
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
                <button @click="logout" aria-label="Keluar aplikasi"
                        class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 border border-slate-200 hover:bg-red-950/20 hover:border-red-900/30 text-slate-500 hover:text-red-400 text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- ══ MAIN ═══════════════════════════════════════════════════════════ -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">

            <!-- Top Nav -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-300 bg-white/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <button @click="isSidebarOpen = true" aria-label="Buka sidebar"
                            class="md:hidden p-1.5 -ml-2 text-slate-500 hover:text-slate-800 rounded-lg transition mr-1">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">summarize</span>
                    <span class="text-xs font-bold text-slate-600">Laporan WiFi Per Provider &amp; Rekapitulasi</span>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-6 space-y-6 flex-1 max-w-7xl mx-auto w-full">

                <!-- PERIODE & FILTER HEADER -->
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Laporan Finansial WiFi</span>
                            <h1 class="text-lg font-black text-slate-900 mt-0.5">
                                <template v-if="selectedTglDari && selectedTglSampai">
                                    Rentang Tanggal: {{ selectedTglDari }} s/d {{ selectedTglSampai }}
                                </template>
                                <template v-else>
                                    Periode {{ getBulanName(selectedBulan) }} {{ selectedTahun }}
                                </template>
                            </h1>
                            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi pendapatan BUMDes dan setoran ke Provider ISP mitra</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                            <!-- Cetak PDF -->
                            <button @click="doPrintPDF"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition">
                                <span class="material-symbols-outlined text-base">print</span>
                                Cetak PDF
                            </button>

                            <!-- Export Button -->
                            <button @click="doExport"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow transition">
                                <span class="material-symbols-outlined text-base">download</span>
                                Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Filter Controls Bar -->
                    <div class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap items-start lg:items-center gap-3">
                        <!-- Provider Filter -->
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Provider</span>
                            <select v-model="selectedProvider" @change="applyFilters"
                                    class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 font-bold text-slate-700 focus:border-blue-500 focus:outline-none w-full">
                                <option value="">Semua Provider</option>
                                <option v-for="prov in providersList" :key="prov.id" :value="prov.id">
                                    {{ prov.nama_provider }} ({{ prov.tipe_bagi_hasil === 'PERSENTASE' ? prov.nilai_bagi_hasil + '%' : 'Flat' }})
                                </option>
                                <option value="tanpa_provider">Tanpa Provider / Umum</option>
                            </select>
                        </div>

                        <!-- Periode Bulan & Tahun -->
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Bulan / Tahun</span>
                            <div class="flex gap-1.5">
                                <select v-model="selectedBulan" @change="applyFilters"
                                        class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-2 font-bold text-slate-700 focus:border-blue-500 focus:outline-none flex-1">
                                    <option v-for="b in namaBulanMap" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                                <select v-model="selectedTahun" @change="applyFilters"
                                        class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-2 font-bold text-slate-700 focus:border-blue-500 focus:outline-none">
                                    <option v-for="y in [2025, 2026, 2027, 2028]" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Rentang Tanggal -->
                        <div class="flex flex-col gap-1 sm:col-span-2 lg:col-span-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Rentang Tanggal</span>
                            <div class="flex items-center gap-1.5 bg-slate-100/80 p-1.5 rounded-xl border border-slate-200">
                                <input type="date" v-model="selectedTglDari" @change="applyFilters"
                                       class="text-xs border border-slate-200 bg-white rounded-lg px-2 py-1 font-semibold text-slate-700 focus:outline-none focus:border-blue-500 flex-1 min-w-0" />
                                <span class="text-xs text-slate-400 font-bold shrink-0">s/d</span>
                                <input type="date" v-model="selectedTglSampai" @change="applyFilters"
                                       class="text-xs border border-slate-200 bg-white rounded-lg px-2 py-1 font-semibold text-slate-700 focus:outline-none focus:border-blue-500 flex-1 min-w-0" />
                                <button v-if="selectedTglDari || selectedTglSampai" @click="resetDateRange" title="Reset"
                                        class="p-1 text-slate-400 hover:text-red-500 transition shrink-0">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUMMARY METRICS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Realisasi Tarikan</span>
                        <p class="text-xl font-black text-slate-900">{{ rupiah(stats.total_tarikan_bruto) }}</p>
                        <span class="text-[10px] text-slate-400">Dari potensi: {{ rupiah(stats.potensi_tarikan) }}</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block">Pendapatan Bersih BUMDes</span>
                        <p class="text-xl font-black text-emerald-600">{{ rupiah(stats.total_hasil_bumdes) }}</p>
                        <span class="text-[10px] text-slate-400">Porsi hak BUMDes periode ini</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest block">Hak Provider</span>
                        <p class="text-xl font-black text-blue-600">{{ rupiah(stats.total_hak_provider) }}</p>
                        <span class="text-[10px] text-slate-400">Porsi provider ISP periode ini</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Status Pembayaran</span>
                        <p class="text-xl font-black text-slate-800">{{ stats.lunas_count ?? 0 }} <span class="text-xs font-bold text-emerald-600">Lunas</span></p>
                        <span class="text-[10px] text-rose-600 font-bold">{{ stats.belum_bayar_count ?? 0 }} Belum Bayar <span class="text-slate-400 font-normal">({{ stats.total_pelanggan }} total)</span></span>
                    </div>
                </div>

                <!-- ── SECTION 1: REKAPITULASI PER PROVIDER ──────────────────────── -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Rekapitulasi Per Provider / Mitra ISP</h2>
                        <span class="text-[10px] text-slate-400 font-mono">{{ rekapPerProvider.length }} provider</span>
                    </div>

                    <!-- Mobile: Card per provider -->
                    <div class="md:hidden divide-y divide-slate-100">
                        <div v-if="rekapPerProvider.length === 0" class="p-6 text-center text-slate-400 text-xs">Tidak ada data.</div>
                        <div v-for="r in rekapPerProvider" :key="r.id" class="p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-slate-900 text-xs">{{ r.nama_provider }}</p>
                                <span :class="r.tipe_bagi_hasil === 'PERSENTASE' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'"
                                      class="px-2 py-0.5 rounded text-[10px] font-black uppercase">
                                    {{ r.tipe_bagi_hasil === 'PERSENTASE' ? r.nilai_bagi_hasil + '%' : rupiah(r.nilai_bagi_hasil) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[11px]">
                                <div>
                                    <span class="text-slate-400">Pelanggan:</span>
                                    <span class="font-bold text-slate-800 ml-1">{{ r.total_pelanggan }}</span>
                                    <span class="text-emerald-600 ml-1">{{ r.aktif_count ?? 0 }} Aktif</span>
                                    <span v-if="r.isolir_count > 0" class="text-red-600 ml-1">{{ r.isolir_count }} Isolir</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400">Tarif Warga:</span>
                                    <span class="font-mono font-bold text-slate-900 ml-1">{{ rupiah(r.total_tarikan) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Dasar Non PPN:</span>
                                    <span class="font-mono font-bold text-slate-700 ml-1">{{ rupiah(r.total_dasar_non_ppn || r.total_tarikan) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400">Hak BUMDes:</span>
                                    <span class="font-mono font-bold text-emerald-600 ml-1">{{ rupiah(r.total_hasil_bumdes) }}</span>
                                </div>
                                <div class="col-span-2 text-right pt-1 border-t border-slate-100">
                                    <span class="text-slate-400">Hak Provider:</span>
                                    <span class="font-mono font-bold text-blue-600 ml-1">{{ rupiah(r.total_hak_provider) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-3.5">Provider / Mitra ISP</th>
                                    <th class="p-3.5 text-center">Skema Bagi Hasil</th>
                                    <th class="p-3.5 text-center">Pelanggan</th>
                                    <th class="p-3.5 text-right">Tarif Warga (Omset)</th>
                                    <th class="p-3.5 text-right">Dasar Non PPN</th>
                                    <th class="p-3.5 text-right">Hasil BUMDes</th>
                                    <th class="p-3.5 text-right">Hak Provider</th>
                                    <th class="p-3.5 text-center">Status Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="r in rekapPerProvider" :key="r.id" class="hover:bg-blue-50/20 transition-colors">
                                    <td class="p-3.5 font-bold text-slate-900">{{ r.nama_provider }}</td>
                                    <td class="p-3.5 text-center">
                                        <span :class="r.tipe_bagi_hasil === 'PERSENTASE' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'"
                                              class="px-2 py-0.5 rounded text-[10px] font-black uppercase">
                                            {{ r.tipe_bagi_hasil === 'PERSENTASE' ? r.nilai_bagi_hasil + '%' : rupiah(r.nilai_bagi_hasil) }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-center font-bold text-slate-800">{{ r.total_pelanggan }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-slate-900">{{ rupiah(r.total_tarikan) }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-slate-700">{{ rupiah(r.total_dasar_non_ppn || r.total_tarikan) }}</td>
                                    <td class="p-3.5 text-right font-mono font-extrabold text-emerald-600">
                                        <div>{{ rupiah(r.total_hasil_bumdes) }}</div>
                                        <span v-if="r.tipe_bagi_hasil === 'PERSENTASE'" class="text-[9px] text-slate-400 font-sans block">9% dari Dasar</span>
                                    </td>
                                    <td class="p-3.5 text-right font-mono font-bold text-blue-600">
                                        <div>{{ rupiah(r.total_hak_provider) }}</div>
                                        <span v-if="r.tipe_bagi_hasil === 'PERSENTASE'" class="text-[9px] text-slate-400 font-sans block">Tarikan - BUMDes</span>
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <span class="text-[11px] font-bold text-emerald-600">{{ r.lunas_count ?? 0 }} Lunas</span>
                                        <span v-if="(r.belum_bayar_count ?? 0) > 0" class="ml-2 text-[11px] font-bold text-rose-600">{{ r.belum_bayar_count }} Belum Bayar</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── SECTION 2: RINCIAN DAFTAR PELANGGAN TERPISAH (LUNAS & ISOLIR) ── -->
                <div class="space-y-6">
                    <!-- Header Bar & Filter Tabs / Search -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Rincian Tagihan Pelanggan Per Provider</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Daftar pelanggan otomatis terpisah antara yang Lunas dan Isolir / Belum Bayar</p>
                            </div>

                            <!-- Live Search Input -->
                            <div class="relative w-full md:w-72">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                                <input v-model="searchQuery" type="text"
                                       placeholder="Cari nama, ID, WA, alamat..."
                                       class="w-full text-xs pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white transition" />
                                <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        </div>

                        <!-- Tab Switcher -->
                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                            <button @click="activeTabStatus = 'terpisah'"
                                    :class="activeTabStatus === 'terpisah'
                                        ? 'bg-slate-900 text-white shadow-sm'
                                        : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                                <span class="material-symbols-outlined text-base">view_agenda</span>
                                Semua Terpisah ({{ pelangganList.length }})
                            </button>

                            <button @click="activeTabStatus = 'lunas'"
                                    :class="activeTabStatus === 'lunas'
                                        ? 'bg-emerald-600 text-white shadow-sm'
                                        : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200'"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                Hanya Lunas ({{ lunasList.length }})
                            </button>

                            <button @click="activeTabStatus = 'isolir'"
                                    :class="activeTabStatus === 'isolir'
                                        ? 'bg-rose-600 text-white shadow-sm'
                                        : 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200'"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                                <span class="material-symbols-outlined text-base">warning</span>
                                Hanya Isolir / Belum Bayar ({{ isolirList.length }})
                            </button>
                        </div>
                    </div>

                    <!-- ══ II.A. TABEL PELANGGAN LUNAS ══════════════════════════════════ -->
                    <div v-if="activeTabStatus === 'terpisah' || activeTabStatus === 'lunas'"
                         class="bg-white border border-emerald-200 rounded-2xl shadow-sm overflow-hidden">
                        
                        <!-- Lunas Header & Summary Badges -->
                        <div class="px-5 py-4 bg-emerald-50/60 border-b border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-black text-emerald-900 uppercase tracking-wider">II.A. Pelanggan LUNAS (Sudah Membayar)</h3>
                                <p class="text-[11px] text-emerald-700">Tagihan periode ini yang telah sukses dibayarkan pelanggan</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-black">
                                    {{ filteredLunasList.length }} Warga Lunas
                                </span>
                                <span class="px-2.5 py-1 bg-white text-slate-800 border border-emerald-200 rounded-lg text-xs font-bold font-mono">
                                    Tarikan: {{ rupiah(subtotalLunas.tarikan) }}
                                </span>
                                <span class="px-2.5 py-1 bg-white text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold font-mono">
                                    BUMDes: {{ rupiah(subtotalLunas.bumdes) }}
                                </span>
                                <span class="px-2.5 py-1 bg-white text-blue-700 border border-emerald-200 rounded-lg text-xs font-bold font-mono">
                                    Provider: {{ rupiah(subtotalLunas.provider) }}
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Card List LUNAS -->
                        <div class="md:hidden divide-y divide-slate-100">
                            <div v-if="filteredLunasList.length === 0" class="p-6 text-center text-slate-400 text-xs">
                                {{ searchQuery ? 'Tidak ada pelanggan lunas yang cocok dengan pencarian.' : 'Belum ada transaksi pembayaran lunas pada periode ini.' }}
                            </div>
                            <div v-for="(item, idx) in paginatedLunasList" :key="item.id" class="p-4 space-y-2 hover:bg-emerald-50/30 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-xs">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400">ID: {{ item.no_id_pel || '-' }} &bull; WA: {{ item.no_wa || '-' }}</p>
                                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                            <span v-if="item.provider" class="px-1.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold">{{ item.provider.nama_provider }}</span>
                                            <span v-else class="text-[10px] text-slate-400">Umum</span>
                                            <span v-if="item.paket" class="text-[10px] font-medium text-slate-600">{{ item.paket }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full text-[10px] font-extrabold uppercase shrink-0">
                                        LUNAS
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-[11px]">
                                    <div class="bg-slate-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">Realisasi</p>
                                        <p class="font-mono font-bold text-slate-900">{{ rupiah(item.nominal_masuk || item.total_tarikan) }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">BUMDes</p>
                                        <p class="font-mono font-bold text-emerald-600">{{ rupiah(item.bumdes_masuk || item.calc_hasil_bumdes) }}</p>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">Provider</p>
                                        <p class="font-mono font-bold text-blue-600">{{ rupiah(item.provider_masuk || item.calc_total_provider) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Table LUNAS -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50/70 border-b border-emerald-200 text-[10px] font-bold text-emerald-900 uppercase tracking-wider">
                                        <th class="p-3.5 text-center w-12">No</th>
                                        <th class="p-3.5">ID Pelanggan</th>
                                        <th class="p-3.5">Nama &amp; Kontak</th>
                                        <th class="p-3.5">Provider</th>
                                        <th class="p-3.5">Paket</th>
                                        <th class="p-3.5 text-center">Gelombang</th>
                                        <th class="p-3.5 text-right">Tarikan Lunas</th>
                                        <th class="p-3.5 text-right">Hasil BUMDes</th>
                                        <th class="p-3.5 text-right">Hak Provider</th>
                                        <th class="p-3.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(item, idx) in paginatedLunasList" :key="item.id" class="hover:bg-emerald-50/30 transition-colors">
                                        <td class="p-3.5 text-center font-mono text-slate-400">{{ item.no ?? ((pageLunas - 1) * perPageLunas + idx + 1) }}</td>
                                        <td class="p-3.5 font-mono font-bold text-slate-800">{{ item.no_id_pel || '-' }}</td>
                                        <td class="p-3.5">
                                            <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">{{ item.no_wa || '-' }} &bull; RT {{ item.rt || '-' }}/RW {{ item.rw || '-' }}</p>
                                        </td>
                                        <td class="p-3.5 whitespace-nowrap">
                                            <span v-if="item.provider" class="px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold">
                                                {{ item.provider.nama_provider }}
                                            </span>
                                            <span v-else class="text-slate-400">Umum</span>
                                        </td>
                                        <td class="p-3.5 whitespace-nowrap font-medium">{{ item.paket || '-' }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-extrabold">Tgl 1 - 10</span>
                                        </td>
                                        <td class="p-3.5 text-right font-mono font-bold text-slate-900">{{ rupiah(item.nominal_masuk || item.total_tarikan) }}</td>
                                        <td class="p-3.5 text-right font-mono font-bold text-emerald-600">{{ rupiah(item.bumdes_masuk || item.calc_hasil_bumdes) }}</td>
                                        <td class="p-3.5 text-right font-mono font-bold text-blue-600">{{ rupiah(item.provider_masuk || item.calc_total_provider) }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full text-[10px] font-extrabold uppercase">
                                                LUNAS
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredLunasList.length === 0">
                                        <td colspan="10" class="p-8 text-center text-slate-400">
                                            {{ searchQuery ? 'Tidak ada pelanggan lunas yang cocok dengan pencarian.' : 'Tidak ada data pelanggan lunas pada periode ini.' }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="filteredLunasList.length > 0" class="bg-emerald-50/80 font-bold border-t-2 border-emerald-300">
                                    <tr>
                                        <td colspan="6" class="p-3.5 text-right uppercase text-[10px] font-black text-emerald-900">TOTAL SELURUH LUNAS ({{ filteredLunasList.length }} Warga):</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-slate-900">{{ rupiah(subtotalLunas.tarikan) }}</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-emerald-700">{{ rupiah(subtotalLunas.bumdes) }}</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-blue-700">{{ rupiah(subtotalLunas.provider) }}</td>
                                        <td class="p-3.5 text-center text-[10px] text-emerald-800 font-extrabold">{{ filteredLunasList.length }} Lunas</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination Controls LUNAS -->
                        <div v-if="filteredLunasList.length > 0" class="px-5 py-3.5 bg-slate-50 border-t border-emerald-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3 text-xs text-slate-500">
                                <span>
                                    Menampilkan 
                                    <strong class="text-slate-800">{{ (Number(pageLunas) - 1) * Number(perPageLunas) + 1 }}</strong> 
                                    - 
                                    <strong class="text-slate-800">{{ Math.min(Number(pageLunas) * Number(perPageLunas), filteredLunasList.length) }}</strong> 
                                    dari 
                                    <strong class="text-emerald-700">{{ filteredLunasList.length }}</strong> 
                                    warga lunas
                                </span>
                                <span class="text-slate-300">|</span>
                                <label class="flex items-center gap-1.5 text-slate-600">
                                    <span>Tampilkan:</span>
                                    <select v-model.number="perPageLunas" class="text-xs bg-white border border-slate-300 rounded-lg px-2 py-1 font-bold text-slate-700 focus:outline-none focus:border-emerald-500">
                                        <option :value="10">10 baris</option>
                                        <option :value="20">20 baris</option>
                                        <option :value="50">50 baris</option>
                                        <option :value="100">100 baris</option>
                                    </select>
                                </label>
                            </div>

                            <!-- Page Buttons LUNAS -->
                            <div v-if="totalLunasPages > 1" class="flex items-center gap-1">
                                <button @click="pageLunas--" :disabled="Number(pageLunas) === 1"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    &laquo; Prev
                                </button>
                                <template v-for="p in getPageNumbers(pageLunas, totalLunasPages)" :key="'lunas-p-' + p">
                                    <span v-if="p === '...'" class="px-2 text-slate-400 text-xs">...</span>
                                    <button v-else @click="pageLunas = p"
                                            :class="Number(pageLunas) === Number(p) ? 'bg-emerald-600 text-white border-emerald-600 font-black' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-bold'"
                                            class="min-w-8 px-2 py-1 text-xs rounded-lg border transition">
                                        {{ p }}
                                    </button>
                                </template>
                                <button @click="pageLunas++" :disabled="Number(pageLunas) === Number(totalLunasPages)"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    Next &raquo;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ══ II.B. TABEL PELANGGAN ISOLIR / BELUM BAYAR ═══════════════════ -->
                    <div v-if="activeTabStatus === 'terpisah' || activeTabStatus === 'isolir'"
                         class="bg-white border border-rose-200 rounded-2xl shadow-sm overflow-hidden">
                        
                        <!-- Isolir Header & Summary Badges -->
                        <div class="px-5 py-4 bg-rose-50/60 border-b border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-black text-rose-900 uppercase tracking-wider">II.B. Pelanggan ISOLIR / BELUM BAYAR (Tunggakan)</h3>
                                <p class="text-[11px] text-rose-700">Daftar pelanggan yang belum membayar tagihan / terisolir</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 border border-rose-300 rounded-lg text-xs font-black">
                                    {{ filteredIsolirList.length }} Warga Isolir/Blm Bayar
                                </span>
                                <span class="px-2.5 py-1 bg-white text-rose-700 border border-rose-200 rounded-lg text-xs font-bold font-mono">
                                    Tunggakan: {{ rupiah(subtotalIsolir.tarikan) }}
                                </span>
                                <span class="px-2.5 py-1 bg-white text-slate-600 border border-rose-200 rounded-lg text-xs font-bold font-mono">
                                    Potensi BUMDes: {{ rupiah(subtotalIsolir.bumdes) }}
                                </span>
                                <span class="px-2.5 py-1 bg-white text-slate-600 border border-rose-200 rounded-lg text-xs font-bold font-mono">
                                    Potensi Provider: {{ rupiah(subtotalIsolir.provider) }}
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Card List ISOLIR -->
                        <div class="md:hidden divide-y divide-slate-100">
                            <div v-if="filteredIsolirList.length === 0" class="p-6 text-center text-slate-400 text-xs">
                                {{ searchQuery ? 'Tidak ada pelanggan isolir/belum bayar yang cocok dengan pencarian.' : 'Semua tagihan telah lunas. Tidak ada yang isolir/menunggak!' }}
                            </div>
                            <div v-for="(item, idx) in paginatedIsolirList" :key="item.id" class="p-4 space-y-2 hover:bg-rose-50/30 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-xs">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400">ID: {{ item.no_id_pel || '-' }} &bull; WA: {{ item.no_wa || '-' }}</p>
                                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                            <span v-if="item.provider" class="px-1.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold">{{ item.provider.nama_provider }}</span>
                                            <span v-else class="text-[10px] text-slate-400">Umum</span>
                                            <span v-if="item.paket" class="text-[10px] font-medium text-slate-600">{{ item.paket }}</span>
                                        </div>
                                    </div>
                                    <span v-if="item.current_status === 'ISOLIR'"
                                          class="inline-flex items-center px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-300 rounded-full text-[10px] font-extrabold uppercase shrink-0">
                                        ISOLIR
                                    </span>
                                    <span v-else
                                          class="inline-flex items-center px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-[10px] font-extrabold uppercase shrink-0">
                                        BELUM BAYAR
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-[11px]">
                                    <div class="bg-rose-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">Tunggakan</p>
                                        <p class="font-mono font-bold text-rose-600">{{ rupiah(item.total_tarikan) }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">Potensi BUMDes</p>
                                        <p class="font-mono font-bold text-slate-600">{{ rupiah(item.calc_hasil_bumdes ?? item.hasil_bumdes) }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-lg p-2">
                                        <p class="text-slate-400 text-[10px]">Potensi Provider</p>
                                        <p class="font-mono font-bold text-slate-600">{{ rupiah(item.calc_total_provider ?? item.total_provider) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Table ISOLIR -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-rose-50/70 border-b border-rose-200 text-[10px] font-bold text-rose-900 uppercase tracking-wider">
                                        <th class="p-3.5 text-center w-12">No</th>
                                        <th class="p-3.5">ID Pelanggan</th>
                                        <th class="p-3.5">Nama &amp; Kontak</th>
                                        <th class="p-3.5">Provider</th>
                                        <th class="p-3.5">Paket</th>
                                        <th class="p-3.5 text-center">Gelombang</th>
                                        <th class="p-3.5 text-right">Tagihan Tertunggak</th>
                                        <th class="p-3.5 text-right">Potensi BUMDes</th>
                                        <th class="p-3.5 text-right">Potensi Provider</th>
                                        <th class="p-3.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="(item, idx) in paginatedIsolirList" :key="item.id" class="hover:bg-rose-50/30 transition-colors">
                                        <td class="p-3.5 text-center font-mono text-slate-400">{{ item.no ?? ((pageIsolir - 1) * perPageIsolir + idx + 1) }}</td>
                                        <td class="p-3.5 font-mono font-bold text-slate-800">{{ item.no_id_pel || '-' }}</td>
                                        <td class="p-3.5">
                                            <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                            <p class="text-[10px] text-slate-400 font-mono">{{ item.no_wa || '-' }} &bull; RT {{ item.rt || '-' }}/RW {{ item.rw || '-' }}</p>
                                        </td>
                                        <td class="p-3.5 whitespace-nowrap">
                                            <span v-if="item.provider" class="px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold">
                                                {{ item.provider.nama_provider }}
                                            </span>
                                            <span v-else class="text-slate-400">Umum</span>
                                        </td>
                                        <td class="p-3.5 whitespace-nowrap font-medium">{{ item.paket || '-' }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-extrabold">Tgl 1 - 10</span>
                                        </td>
                                        <td class="p-3.5 text-right font-mono font-bold text-rose-600">{{ rupiah(item.total_tarikan) }}</td>
                                        <td class="p-3.5 text-right font-mono font-bold text-slate-600">{{ rupiah(item.calc_hasil_bumdes ?? item.hasil_bumdes) }}</td>
                                        <td class="p-3.5 text-right font-mono font-bold text-slate-600">{{ rupiah(item.calc_total_provider ?? item.total_provider) }}</td>
                                        <td class="p-3.5 text-center whitespace-nowrap">
                                            <span v-if="item.current_status === 'ISOLIR'" class="inline-flex items-center px-2.5 py-0.5 bg-rose-100 text-rose-700 border border-rose-300 rounded-full text-[10px] font-extrabold uppercase">
                                                ISOLIR
                                            </span>
                                            <span v-else class="inline-flex items-center px-2.5 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-[10px] font-extrabold uppercase">
                                                BELUM BAYAR
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredIsolirList.length === 0">
                                        <td colspan="10" class="p-8 text-center text-slate-400">
                                            {{ searchQuery ? 'Tidak ada pelanggan isolir/belum bayar yang cocok dengan pencarian.' : 'Semua pelanggan telah lunas. Tidak ada data isolir atau tunggakan!' }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="filteredIsolirList.length > 0" class="bg-rose-50/80 font-bold border-t-2 border-rose-300">
                                    <tr>
                                        <td colspan="6" class="p-3.5 text-right uppercase text-[10px] font-black text-rose-900">TOTAL SELURUH ISOLIR ({{ filteredIsolirList.length }} Warga):</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-rose-700">{{ rupiah(subtotalIsolir.tarikan) }}</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-slate-600">{{ rupiah(subtotalIsolir.bumdes) }}</td>
                                        <td class="p-3.5 text-right font-mono font-extrabold text-slate-600">{{ rupiah(subtotalIsolir.provider) }}</td>
                                        <td class="p-3.5 text-center text-[10px] text-rose-800 font-extrabold">{{ filteredIsolirList.length }} Isolir/Blm Bayar</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination Controls ISOLIR -->
                        <div v-if="filteredIsolirList.length > 0" class="px-5 py-3.5 bg-slate-50 border-t border-rose-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3 text-xs text-slate-500">
                                <span>
                                    Menampilkan 
                                    <strong class="text-slate-800">{{ (Number(pageIsolir) - 1) * Number(perPageIsolir) + 1 }}</strong> 
                                    - 
                                    <strong class="text-slate-800">{{ Math.min(Number(pageIsolir) * Number(perPageIsolir), filteredIsolirList.length) }}</strong> 
                                    dari 
                                    <strong class="text-rose-700">{{ filteredIsolirList.length }}</strong> 
                                    warga isolir/belum bayar
                                </span>
                                <span class="text-slate-300">|</span>
                                <label class="flex items-center gap-1.5 text-slate-600">
                                    <span>Tampilkan:</span>
                                    <select v-model.number="perPageIsolir" class="text-xs bg-white border border-slate-300 rounded-lg px-2 py-1 font-bold text-slate-700 focus:outline-none focus:border-rose-500">
                                        <option :value="10">10 baris</option>
                                        <option :value="20">20 baris</option>
                                        <option :value="50">50 baris</option>
                                        <option :value="100">100 baris</option>
                                    </select>
                                </label>
                            </div>

                            <!-- Page Buttons ISOLIR -->
                            <div v-if="totalIsolirPages > 1" class="flex items-center gap-1">
                                <button @click="pageIsolir--" :disabled="Number(pageIsolir) === 1"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    &laquo; Prev
                                </button>
                                <template v-for="p in getPageNumbers(pageIsolir, totalIsolirPages)" :key="'isolir-p-' + p">
                                    <span v-if="p === '...'" class="px-2 text-slate-400 text-xs">...</span>
                                    <button v-else @click="pageIsolir = p"
                                            :class="Number(pageIsolir) === Number(p) ? 'bg-rose-600 text-white border-rose-600 font-black' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 font-bold'"
                                            class="min-w-8 px-2 py-1 text-xs rounded-lg border transition">
                                        {{ p }}
                                    </button>
                                </template>
                                <button @click="pageIsolir++" :disabled="Number(pageIsolir) === Number(totalIsolirPages)"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    Next &raquo;
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ══ REKAPITULASI TOTAL GABUNGAN (JIKA MODE SEMUA TERPISAH) ══ -->
                    <div v-if="activeTabStatus === 'terpisah' && pelangganList.length > 0"
                         class="bg-slate-900 text-white rounded-2xl p-4 sm:p-5 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Total Realisasi Pelanggan Lunas Periode Ini</span>
                                <p class="text-sm text-slate-300 mt-0.5">Dari {{ stats.total_pelanggan }} total warga terdaftar ({{ stats.lunas_count }} Lunas, {{ stats.belum_bayar_count }} Isolir/Belum Bayar)</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs font-mono">
                                <div class="bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700">
                                    <span class="text-[10px] text-slate-400 block font-sans">Total Realisasi Tarikan</span>
                                    <strong class="text-emerald-400 text-sm">{{ rupiah(stats.total_tarikan_bruto) }}</strong>
                                </div>
                                <div class="bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700">
                                    <span class="text-[10px] text-slate-400 block font-sans">Pendapatan Bersih BUMDes</span>
                                    <strong class="text-emerald-300 text-sm">{{ rupiah(stats.total_hasil_bumdes) }}</strong>
                                </div>
                                <div class="bg-slate-800/80 px-3 py-2 rounded-xl border border-slate-700">
                                    <span class="text-[10px] text-slate-400 block font-sans">Setoran Hak Provider</span>
                                    <strong class="text-blue-300 text-sm">{{ rupiah(stats.total_hak_provider) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
