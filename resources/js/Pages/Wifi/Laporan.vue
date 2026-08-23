<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const applyFilters = () => {
    router.get(route('wifi.laporan.index'), {
        bulan:          selectedBulan.value,
        tahun:          selectedTahun.value,
        provider_id:    selectedProvider.value || undefined,
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
    window.location.href = route('wifi.laporan.export') + '?' + params.toString();
};

const doPrintPDF = () => {
    const params = new URLSearchParams();
    params.set('bulan', selectedBulan.value);
    params.set('tahun', selectedTahun.value);
    if (selectedProvider.value)  params.set('provider_id', selectedProvider.value);
    if (selectedTglDari.value)   params.set('tanggal_dari', selectedTglDari.value);
    if (selectedTglSampai.value) params.set('tanggal_sampai', selectedTglSampai.value);
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
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Tarikan Bruto</span>
                        <p class="text-xl font-black text-slate-900">{{ rupiah(stats.total_tarikan_bruto) }}</p>
                        <span class="text-[10px] text-slate-400">seluruh tagihan warga</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block">Pendapatan Bersih BUMDes</span>
                        <p class="text-xl font-black text-emerald-600">{{ rupiah(stats.total_hasil_bumdes) }}</p>
                        <span class="text-[10px] text-slate-400">hasil bagi BUMDes</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest block">Setoran Hak Provider</span>
                        <p class="text-xl font-black text-blue-600">{{ rupiah(stats.total_hak_provider) }}</p>
                        <span class="text-[10px] text-slate-400">porsi provider ISP</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Pelanggan</span>
                        <p class="text-xl font-black text-slate-800">{{ stats.total_pelanggan }} <span class="text-xs font-normal text-slate-400">warga</span></p>
                        <span class="text-[10px] text-emerald-600 font-bold">{{ stats.aktif_count ?? 0 }} Aktif &bull; {{ stats.isolir_count ?? 0 }} Isolir</span>
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
                                    <span class="text-slate-400">Bruto:</span>
                                    <span class="font-mono font-bold text-slate-900 ml-1">{{ rupiah(r.total_tarikan) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400">BUMDes:</span>
                                    <span class="font-mono font-bold text-emerald-600 ml-1">{{ rupiah(r.total_hasil_bumdes) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-400">Provider:</span>
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
                                    <th class="p-3.5 text-center">Jumlah Pelanggan</th>
                                    <th class="p-3.5 text-right">Total Tarikan Bruto</th>
                                    <th class="p-3.5 text-right">Hasil BUMDes</th>
                                    <th class="p-3.5 text-right">Setoran Provider</th>
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
                                    <td class="p-3.5 text-right font-mono font-extrabold text-emerald-600">{{ rupiah(r.total_hasil_bumdes) }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-blue-600">{{ rupiah(r.total_hak_provider) }}</td>
                                    <td class="p-3.5 text-center">
                                        <span class="text-[11px] font-semibold text-emerald-600">{{ r.aktif_count ?? 0 }} Aktif</span>
                                        <span v-if="r.isolir_count > 0" class="ml-2 text-[11px] font-semibold text-red-600">{{ r.isolir_count }} Isolir</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── SECTION 2: RINCIAN DAFTAR PELANGGAN ────────────────────────── -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Rincian Tagihan Pelanggan Per Provider</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">Daftar pelanggan dengan pembagian porsi BUMDes &amp; Provider</p>
                        </div>
                    </div>

                    <!-- Mobile: Card List -->
                    <div class="md:hidden divide-y divide-slate-100">
                        <div v-if="pelangganList.length === 0" class="p-6 text-center text-slate-400 text-xs">Tidak ada data pelanggan.</div>
                        <div v-for="item in pelangganList" :key="item.id" class="p-4 space-y-2 hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs">{{ item.nama }}</p>
                                    <p class="text-[10px] text-slate-400">ID: {{ item.no_id_pel || '-' }}</p>
                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        <span v-if="item.provider" class="px-1.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded text-[10px] font-bold">{{ item.provider.nama_provider }}</span>
                                        <span v-else class="text-[10px] text-slate-400">Umum</span>
                                        <span v-if="item.paket" class="text-[10px] font-medium text-slate-600">{{ item.paket }}</span>
                                    </div>
                                </div>
                                <span v-if="(item.current_status || item.status_1_15) === 'ISOLIR'"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 border border-red-200 rounded-full text-[10px] font-black uppercase shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>ISOLIR
                                </span>
                                <span v-else
                                      class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-black uppercase shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>AKTIF
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-[11px]">
                                <div class="bg-slate-50 rounded-lg p-2">
                                    <p class="text-slate-400 text-[10px]">Tarikan</p>
                                    <p class="font-mono font-bold text-slate-900">{{ rupiah(item.total_tarikan) }}</p>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-2">
                                    <p class="text-slate-400 text-[10px]">BUMDes</p>
                                    <p class="font-mono font-bold text-emerald-600">{{ rupiah(item.hasil_bumdes) }}</p>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-2">
                                    <p class="text-slate-400 text-[10px]">Provider</p>
                                    <p class="font-mono font-bold text-blue-600">{{ rupiah(item.total_provider) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Total Footer -->
                        <div v-if="pelangganList.length > 0" class="p-4 bg-slate-100 border-t-2 border-slate-300">
                            <p class="text-[10px] font-black text-slate-600 uppercase mb-2">Total Keseluruhan</p>
                            <div class="grid grid-cols-3 gap-2 text-[11px]">
                                <div>
                                    <p class="text-slate-400 text-[10px]">Bruto</p>
                                    <p class="font-mono font-bold text-slate-900">{{ rupiah(stats.total_tarikan_bruto) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 text-[10px]">BUMDes</p>
                                    <p class="font-mono font-bold text-emerald-700">{{ rupiah(stats.total_hasil_bumdes) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 text-[10px]">Provider</p>
                                    <p class="font-mono font-bold text-blue-700">{{ rupiah(stats.total_hak_provider) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-3.5 text-center w-12">No</th>
                                    <th class="p-3.5">Nama Pelanggan</th>
                                    <th class="p-3.5">Provider</th>
                                    <th class="p-3.5">Paket</th>
                                    <th class="p-3.5 text-center">Masa Bayar</th>
                                    <th class="p-3.5 text-right">Tarikan Warga</th>
                                    <th class="p-3.5 text-right">Hasil BUMDes</th>
                                    <th class="p-3.5 text-right">Hak Provider</th>
                                    <th class="p-3.5 text-center">Status Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in pelangganList" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3.5 text-center font-mono text-slate-400">{{ item.no ?? '-' }}</td>
                                    <td class="p-3.5">
                                        <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">ID: {{ item.no_id_pel || '-' }}</p>
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
                                    <td class="p-3.5 text-right font-mono font-bold text-slate-900">{{ rupiah(item.total_tarikan) }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-emerald-600">{{ rupiah(item.hasil_bumdes) }}</td>
                                    <td class="p-3.5 text-right font-mono font-bold text-blue-600">{{ rupiah(item.total_provider) }}</td>
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <span v-if="(item.current_status || item.status_1_15) === 'ISOLIR'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-red-100 text-red-700 border border-red-200 rounded-full text-[10px] font-black uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>ISOLIR
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-black uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>AKTIF
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="pelangganList.length === 0">
                                    <td colspan="9" class="p-8 text-center text-slate-400">Tidak ada data pelanggan untuk filter provider ini.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="pelangganList.length > 0" class="bg-slate-100 font-bold border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="5" class="p-3.5 text-right uppercase text-[10px] font-black text-slate-600">Total Keseluruhan:</td>
                                    <td class="p-3.5 text-right font-mono text-slate-900">{{ rupiah(stats.total_tarikan_bruto) }}</td>
                                    <td class="p-3.5 text-right font-mono text-emerald-700">{{ rupiah(stats.total_hasil_bumdes) }}</td>
                                    <td class="p-3.5 text-right font-mono text-blue-700">{{ rupiah(stats.total_hak_provider) }}</td>
                                    <td class="p-3.5 text-center text-[10px] text-slate-600">{{ stats.aktif_count }} Aktif</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
