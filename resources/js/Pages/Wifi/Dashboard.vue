<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    unit:  { type: Object, required: true },
    user:  { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const isSidebarOpen = ref(false);
const logout = () => { router.post(route('logout')); };

// ── Format helpers ─────────────────────────────────────────────────────────
const rupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
    }).format(val);
};

const formatDate = (val) => {
    if (!val) return '-';
    try {
        return new Date(val).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    } catch { return val; }
};

// ── Computed from stats ────────────────────────────────────────────────────
const total      = computed(() => props.stats.total_pelanggan   ?? 0);
const bulanIni   = computed(() => props.stats.daftar_bulan_ini  ?? 0);
const totalTarikan = computed(() => props.stats.total_tarikan   ?? 0);
const hasilBumdes  = computed(() => props.stats.hasil_bumdes    ?? 0);
const totalProvider = computed(() => props.stats.total_provider ?? 0);
const recent     = computed(() => props.stats.recent            ?? []);
const perPaket   = computed(() => props.stats.per_paket         ?? []);
const s115       = computed(() => props.stats.status_115        ?? { LUNAS: 0, TUNGGAKAN: 0, ISOLIR: 0, kosong: 0 });
const s1630      = computed(() => props.stats.status_1630       ?? { LUNAS: 0, TUNGGAKAN: 0, ISOLIR: 0, kosong: 0 });

const statusSummary = computed(() => props.stats.status_summary ?? {
    LUNAS: (s115.value.LUNAS || 0) + (s1630.value.LUNAS || 0),
    TUNGGAKAN: (s115.value.TUNGGAKAN || 0) + (s1630.value.TUNGGAKAN || 0),
    ISOLIR: (s115.value.ISOLIR || 0) + (s1630.value.ISOLIR || 0),
    kosong: (s115.value.kosong || 0) + (s1630.value.kosong || 0),
});
const statusTotal = computed(() => (statusSummary.value.LUNAS + statusSummary.value.TUNGGAKAN + statusSummary.value.ISOLIR + statusSummary.value.kosong) || 1);

const gel1Count = computed(() => props.stats.gel1 ?? 0);
const gel2Count = computed(() => props.stats.gel2 ?? 0);
const gelTotal = computed(() => (gel1Count.value + gel2Count.value) || 1);

const pct = (count, total) => Math.round((count / total) * 100);
</script>

<template>
    <Head title="Dashboard Admin - Unit WiFi" />

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
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </a>
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
                        <a :href="route('unit.welcome', { slug: 'wifi' })" target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                            <span class="material-symbols-outlined text-lg">wifi</span>
                            Unit Wifi
                        </a>
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
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">explore</span>
                    <span class="text-xs font-bold text-slate-600 truncate">Selamat Bekerja, {{ user.nama }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="route('unit.welcome', { slug: 'wifi' })" target="_blank" rel="noopener"
                          class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span class="hidden sm:inline">Lihat Landing Page</span>
                    </a>
                </div>
            </header>

            <!-- ══ CONTENT ══════════════════════════════════════════════════════ -->
            <div class="p-4 sm:p-6 space-y-6 max-w-6xl w-full mx-auto">

                <!-- Page title -->
                <div>
                    <h1 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Dashboard WiFi</h1>
                    <p class="text-[11px] text-slate-500 mt-0.5">Ringkasan data real-time dari database pelanggan WiFi BUMDes Damar Wulan</p>
                </div>

                <!-- ── ROW 1: Key Metrics ────────────────────────────────────── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                    <!-- Total Pelanggan -->
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Pelanggan</span>
                            <span class="material-symbols-outlined text-blue-500 text-lg">group</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ total.toLocaleString('id-ID') }}</h3>
                            <p class="text-[9px] text-blue-500 font-bold flex items-center gap-0.5 mt-0.5">
                                <span class="material-symbols-outlined text-xs">calendar_month</span>
                                +{{ bulanIni }} bulan ini
                            </p>
                        </div>
                    </div>

                    <!-- Total Tarikan -->
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Tarikan</span>
                            <span class="material-symbols-outlined text-blue-500 text-lg">payments</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 leading-tight">{{ rupiah(totalTarikan) }}</h3>
                            <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Akumulasi semua pelanggan</p>
                        </div>
                    </div>

                    <!-- Hasil BUMDes -->
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hasil BUMDes</span>
                            <span class="material-symbols-outlined text-emerald-500 text-lg">account_balance</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-emerald-600 leading-tight">{{ rupiah(hasilBumdes) }}</h3>
                            <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Bagi hasil BUMDes</p>
                        </div>
                    </div>

                    <!-- Total Provider -->
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Provider</span>
                            <span class="material-symbols-outlined text-slate-400 text-lg">cell_tower</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-700 leading-tight">{{ rupiah(totalProvider) }}</h3>
                            <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Biaya ke provider</p>
                        </div>
                    </div>
                </div>

                <!-- ── ROW 2: Status Tagihan, Gelombang, Per Paket ───────────── -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Status Tagihan -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Status Tagihan Pelanggan</h2>
                            <span class="material-symbols-outlined text-slate-400 text-base">receipt_long</span>
                        </div>
                        <div class="space-y-2.5">
                            <!-- LUNAS -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-bold text-emerald-600">Lunas</span>
                                    <span class="text-slate-500">{{ statusSummary.LUNAS }} <span class="text-slate-400">({{ pct(statusSummary.LUNAS, statusTotal) }}%)</span></span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                         :style="{ width: pct(statusSummary.LUNAS, statusTotal) + '%' }"></div>
                                </div>
                            </div>
                            <!-- TUNGGAKAN -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-bold text-amber-600">Tunggakan</span>
                                    <span class="text-slate-500">{{ statusSummary.TUNGGAKAN }} <span class="text-slate-400">({{ pct(statusSummary.TUNGGAKAN, statusTotal) }}%)</span></span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full transition-all duration-500"
                                         :style="{ width: pct(statusSummary.TUNGGAKAN, statusTotal) + '%' }"></div>
                                </div>
                            </div>
                            <!-- ISOLIR -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-bold text-red-600">Isolir</span>
                                    <span class="text-slate-500">{{ statusSummary.ISOLIR }} <span class="text-slate-400">({{ pct(statusSummary.ISOLIR, statusTotal) }}%)</span></span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full transition-all duration-500"
                                         :style="{ width: pct(statusSummary.ISOLIR, statusTotal) + '%' }"></div>
                                </div>
                            </div>
                            <!-- Belum diisi -->
                            <div v-if="statusSummary.kosong > 0" class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-semibold text-slate-400">Belum diisi</span>
                                    <span class="text-slate-400">{{ statusSummary.kosong }}</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-slate-300 rounded-full"
                                         :style="{ width: pct(statusSummary.kosong, statusTotal) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Gelombang -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Jadwal Gelombang Tagihan</h2>
                            <span class="material-symbols-outlined text-slate-400 text-base">schedule</span>
                        </div>
                        <div class="space-y-3 pt-1">
                            <!-- Gelombang 1 -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-bold text-indigo-600">Gelombang 1 (Tgl 1-15)</span>
                                    <span class="text-slate-500 font-mono font-bold">{{ gel1Count }} <span class="text-slate-400 font-normal">({{ pct(gel1Count, gelTotal) }}%)</span></span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                         :style="{ width: pct(gel1Count, gelTotal) + '%' }"></div>
                                </div>
                            </div>
                            <!-- Gelombang 2 -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="font-bold text-violet-600">Gelombang 2 (Tgl 16-Akhir)</span>
                                    <span class="text-slate-500 font-mono font-bold">{{ gel2Count }} <span class="text-slate-400 font-normal">({{ pct(gel2Count, gelTotal) }}%)</span></span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-violet-500 rounded-full transition-all duration-500"
                                         :style="{ width: pct(gel2Count, gelTotal) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Per Paket -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Per Paket</h2>
                            <span class="material-symbols-outlined text-slate-400 text-base">wifi</span>
                        </div>

                        <div v-if="perPaket.length > 0" class="space-y-3">
                            <div v-for="p in perPaket" :key="p.paket" class="flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-bold text-slate-700 truncate">{{ p.paket }}</p>
                                    <p class="text-[10px] text-slate-400">{{ rupiah(p.total) }}</p>
                                </div>
                                <span class="shrink-0 px-2.5 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded-full text-[11px] font-extrabold">
                                    {{ p.jumlah }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="flex flex-col items-center justify-center py-4 text-slate-400">
                            <span class="material-symbols-outlined text-3xl mb-1">wifi_off</span>
                            <p class="text-[11px]">Belum ada data paket</p>
                        </div>
                    </div>
                </div>

                <!-- ── ROW 3: Pelanggan Terbaru ───────────────────────────────── -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Pelanggan Terbaru</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">10 pelanggan terakhir yang terdaftar</p>
                        </div>
                        <Link :href="route('wifi.pelanggan.index')"
                              class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                            Lihat Semua
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-if="recent.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <span class="material-symbols-outlined text-5xl mb-3">group_off</span>
                        <p class="text-sm font-semibold">Belum ada data pelanggan</p>
                        <p class="text-xs mt-1">Tambahkan pelanggan pertama via menu Pelanggan.</p>
                        <Link :href="route('wifi.pelanggan.index')"
                              class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base">add</span>
                            Tambah Pelanggan
                        </Link>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Pelanggan</th>
                                    <th class="px-4 py-3">Paket</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3">Tgl Daftar</th>
                                    <th class="px-4 py-3">Total Tarikan</th>
                                    <th class="px-4 py-3 text-center">Gelombang</th>
                                    <th class="px-4 py-3 text-center">Status Tagihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in recent" :key="row.id"
                                    class="border-b border-slate-100 hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-slate-500 whitespace-nowrap">{{ row.no ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <p class="font-bold text-slate-900">{{ row.nama }}</p>
                                        <p v-if="row.no_wa" class="text-[10px] text-slate-400">{{ row.no_wa }}</p>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span v-if="row.paket"
                                              class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">
                                            {{ row.paket }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 max-w-[180px] truncate" :title="[row.alamat, row.rt ? 'RT '+row.rt : '', row.rw ? 'RW '+row.rw : ''].filter(Boolean).join(' ')">
                                        {{ [row.alamat, row.rt ? 'RT '+row.rt : '', row.rw ? 'RW '+row.rw : ''].filter(Boolean).join(' ') || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ formatDate(row.tanggal_daftar) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold text-slate-700 whitespace-nowrap">
                                        {{ row.total_tarikan ? new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(row.total_tarikan) : '-' }}
                                    </td>
                                    <!-- Gelombang -->
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span v-if="row.gelombang === '1_15'"
                                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">
                                            Gel. 1
                                        </span>
                                        <span v-else-if="row.gelombang === '16_30'"
                                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-violet-100 text-violet-700 border border-violet-200">
                                            Gel. 2
                                        </span>
                                        <span v-else class="text-slate-400 font-mono text-[10px]">-</span>
                                    </td>
                                    <!-- Status Tagihan -->
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span :class="{
                                                'bg-emerald-100 text-emerald-700 border-emerald-300': (row.current_status || row.status_1_15) === 'LUNAS',
                                                'bg-amber-100 text-amber-700 border-amber-300':    (row.current_status || row.status_1_15) === 'TUNGGAKAN',
                                                'bg-red-100 text-red-700 border-red-300':           (row.current_status || row.status_1_15) === 'ISOLIR',
                                                'bg-slate-100 text-slate-400 border-slate-200':     !(row.current_status || row.status_1_15),
                                              }"
                                              class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-extrabold border uppercase">
                                            {{ row.current_status || row.status_1_15 || 'Belum bayar' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /content -->
        </main>
    </div>
</template>
