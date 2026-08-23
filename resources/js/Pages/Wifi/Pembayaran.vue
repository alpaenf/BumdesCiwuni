<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, reactive } from 'vue';

const props = defineProps({
    unit:         { type: Object, required: true },
    user:         { type: Object, required: true },
    pelanggan:    { type: Object, required: true },
    paketOptions: { type: Array,  default: () => [] },
    filters:      { type: Object, default: () => ({}) },
    stats:        { type: Object, default: () => ({}) },
});

// ── State ──────────────────────────────────────────────────────────────────
const isSidebarOpen = ref(false);
const logout = () => router.post(route('logout'));

const todayDay = ref(new Date().getDate());

const defaultGelombang = new Date().getDate() <= 15 ? '1_15' : '16_30';

const selectedBulan     = ref(props.filters.bulan     ?? new Date().getMonth() + 1);
const selectedTahun     = ref(props.filters.tahun     ?? new Date().getFullYear());
const selectedGelombang = ref(props.filters.gelombang ?? defaultGelombang);

const search            = ref(props.filters.search  ?? '');
const filterStatus      = ref(props.filters.status  ?? '');
const filterPaket       = ref(props.filters.paket   ?? '');
const perPage           = ref(props.filters.per_page ?? '25');

const selectedIds       = ref([]);
const selectAll         = ref(false);

const modalMode         = ref(''); // 'pay' | 'bayar_masal' | 'history' | 'broadcast_wa'
const selectedCustomer  = ref(null);
const customerHistory   = ref([]);
const historyLoading    = ref(false);

const getWaReminderUrl = (item) => {
    if (!item || !item.no_wa) return '#';
    let phone = item.no_wa.replace(/\D/g, '');
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    const nama = item.nama || 'Pelanggan';
    const nominal = rupiah(item.total_tarikan || 0);
    const bulan = getBulanName(selectedBulan.value);
    const tahun = selectedTahun.value;

    const message = `Halo Bpk/Ibu ${nama}, mengingatkan tagihan WiFi BUMDes Ciwuni sebesar ${nominal} untuk bulan ${bulan} ${tahun} jatuh tempo hari ini (Tanggal 10). Silakan melakukan pembayaran sebelum pukul 23:59 WIB agar layanan tetap aktif. Terima kasih!`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
};

const openWaBroadcastModal = () => {
    modalMode.value = 'broadcast_wa';
};

// Helper auto-gelombang dari tanggal_bayar (Tgl 1-15 -> 1_15, Tgl >15 -> 16_30)
const detectGelombang = (tglString) => {
    if (!tglString) return '1_15';
    const day = parseInt(tglString.split('-')[2]) || new Date(tglString).getDate();
    return day <= 15 ? '1_15' : '16_30';
};

// ── Filter Watchers ────────────────────────────────────────────────────────
let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

const applyFilters = () => {
    router.get(route('wifi.pembayaran.index'), {
        bulan:     selectedBulan.value,
        tahun:     selectedTahun.value,
        gelombang: selectedGelombang.value,
        search:    search.value    || undefined,
        status:    filterStatus.value || undefined,
        paket:     filterPaket.value  || undefined,
        per_page:  perPage.value   || undefined,
    }, { preserveScroll: true, replace: true });
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedIds.value = props.pelanggan.data.map(p => p.id);
    } else {
        selectedIds.value = [];
    }
};

watch(selectedIds, (newVal) => {
    if (props.pelanggan.data.length > 0 && newVal.length === props.pelanggan.data.length) {
        selectAll.value = true;
    } else {
        selectAll.value = false;
    }
});

// ── Form Single Pay ────────────────────────────────────────────────────────
const todayStr = new Date().toISOString().split('T')[0];

const payForm = useForm({
    pelanggan_wifi_id: '',
    periode_bulan:     selectedBulan.value,
    periode_tahun:     selectedTahun.value,
    gelombang:         detectGelombang(todayStr),
    tanggal_bayar:     todayStr,
    jumlah_bayar:      0,
    metode_pembayaran: 'TUNAI',
    status:            'LUNAS',
    catatan:           '',
});

const openPayModal = (item) => {
    selectedCustomer.value = item;
    payForm.reset();
    payForm.pelanggan_wifi_id = item.id;
    payForm.periode_bulan     = selectedBulan.value;
    payForm.periode_tahun     = selectedTahun.value;
    payForm.gelombang         = selectedGelombang.value;
    payForm.tanggal_bayar     = todayStr;
    payForm.jumlah_bayar      = item.total_tarikan ?? 0;
    payForm.metode_pembayaran = 'TUNAI';
    payForm.status            = 'LUNAS';
    payForm.catatan           = '';
    modalMode.value           = 'pay';
};

const submitSinglePay = () => {
    payForm.post(route('wifi.pembayaran.store'), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

// ── Form Mass Pay ──────────────────────────────────────────────────────────
const massForm = useForm({
    pelanggan_ids:     [],
    periode_bulan:     selectedBulan.value,
    periode_tahun:     selectedTahun.value,
    gelombang:         selectedGelombang.value,
    tanggal_bayar:     todayStr,
    metode_pembayaran: 'TUNAI',
    status:            'LUNAS',
});

const openMassPayModal = () => {
    if (selectedIds.value.length === 0) return;
    massForm.reset();
    massForm.pelanggan_ids     = [...selectedIds.value];
    massForm.periode_bulan     = selectedBulan.value;
    massForm.periode_tahun     = selectedTahun.value;
    massForm.gelombang         = selectedGelombang.value;
    massForm.tanggal_bayar     = todayStr;
    massForm.metode_pembayaran = 'TUNAI';
    massForm.status            = 'LUNAS';
    modalMode.value            = 'bayar_masal';
};

const submitMassPay = () => {
    massForm.post(route('wifi.pembayaran.masal'), {
        onSuccess: () => {
            selectedIds.value = [];
            closeModal();
        },
        preserveScroll: true,
    });
};

// ── History ────────────────────────────────────────────────────────────────
const openHistoryModal = async (item) => {
    selectedCustomer.value = item;
    customerHistory.value  = [];
    historyLoading.value   = true;
    modalMode.value        = 'history';

    try {
        const res  = await fetch(route('wifi.pembayaran.history', item.id));
        const data = await res.json();
        customerHistory.value = data.history ?? [];
    } catch {
        customerHistory.value = [];
    }
    historyLoading.value = false;
};

const closeModal = () => {
    modalMode.value = '';
    selectedCustomer.value = null;
};

// ── Formatting ─────────────────────────────────────────────────────────────
const rupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const formatDate = (val) => {
    if (!val) return '-';
    try { return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); }
    catch { return val; }
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
    <Head title="Kasir & Pembayaran WiFi" />

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
                <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600" aria-label="Tutup sidebar">
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
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        Kasir &amp; Pembayaran
                    </a>
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

        <!-- ══ MAIN CONTENT ═════════════════════════════════════════════════════ -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">

            <!-- Top Nav -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-300 bg-white/80 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <button @click="isSidebarOpen = true" aria-label="Buka sidebar"
                            class="md:hidden p-1.5 -ml-2 text-slate-500 hover:text-slate-800 rounded-lg transition mr-1">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">receipt_long</span>
                    <span class="text-xs font-bold text-slate-600 truncate">Kasir &amp; Pengelolaan Pembayaran WiFi</span>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-6 space-y-6 flex-1 max-w-7xl mx-auto w-full">

                <!-- PERIODE SELECTOR BANNER -->
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Periode Aktif Penagihan</span>
                        <h1 class="text-lg font-black text-slate-900 mt-0.5">
                            {{ getBulanName(selectedBulan) }} {{ selectedTahun }} &mdash;
                            <span class="text-blue-600 font-bold">
                                {{ selectedGelombang === '1_15' ? 'Gelombang 1 (Tgl 1-15)' : 'Gelombang 2 (Tgl 16-Akhir Bulan)' }}
                            </span>
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola input pembayaran tunai / transfer dan cetak struk tagihan warga</p>
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <!-- Bulan -->
                        <select v-model="selectedBulan" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 font-bold text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option v-for="b in namaBulanMap" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>

                        <!-- Tahun -->
                        <select v-model="selectedTahun" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 font-bold text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option v-for="y in [2025, 2026, 2027, 2028]" :key="y" :value="y">{{ y }}</option>
                        </select>

                        <!-- Gelombang -->
                        <select v-model="selectedGelombang" @change="applyFilters"
                                class="text-xs border border-blue-200 bg-blue-50 text-blue-700 rounded-xl px-3 py-2 font-black focus:border-blue-500 focus:outline-none">
                            <option value="1_15">Gelombang 1 (Tgl 1-15)</option>
                            <option value="16_30">Gelombang 2 (Tgl 16-Akhir Bulan)</option>
                        </select>
                    </div>
                </div>

                <!-- ── DYNAMIC DEADLINE & WA REMINDER BANNER (TANGGAL 8 - 10 ATAU JIKA ADA TUNGGAKAN) ── -->
                <div v-if="todayDay >= 8 && todayDay <= 10"
                     class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 text-white rounded-2xl p-4 sm:p-5 shadow-lg flex flex-col md:flex-row items-center justify-between gap-4 border border-amber-600">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-white shrink-0 shadow-inner">
                            <span class="material-symbols-outlined text-2xl">notifications_active</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 bg-red-700 text-white text-[10px] font-black uppercase rounded-md tracking-wider shadow-sm">
                                    {{ todayDay === 10 ? '🚨 HARI INI JATUH TEMPO (TGL 10)' : '⚠️ H-2 TENGGAT PEMBAYARAN (TGL 10)' }}
                                </span>
                            </div>
                            <h3 class="text-sm font-black text-white mt-1">
                                Batas Pembayaran Tagihan WiFi Bulan Ini
                            </h3>
                            <p class="text-xs text-amber-100 mt-0.5">
                                Batas pembayaran warga adalah tanggal 10. Mulai tanggal 11 besok, pelanggan yang belum bayar akan di-ISOLIR otomatis oleh sistem.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                        <button @click="openWaBroadcastModal"
                                class="w-full md:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md flex items-center justify-center gap-2 transition border border-emerald-500">
                            <span class="material-symbols-outlined text-sm">send</span>
                            Broadcast WA Pengingat Masal
                        </button>
                    </div>
                </div>

                <!-- STATS SUMMARY CARDS -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Terkumpul</span>
                        <p class="text-base font-black text-slate-900">{{ rupiah(stats.total_nominal_terkumpul) }}</p>
                        <span class="text-[10px] text-slate-400">Periode ini</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Lunas</span>
                        <p class="text-xl font-black text-emerald-600">{{ stats.total_lunas }}</p>
                        <span class="text-[10px] text-slate-400">pelanggan</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Tunggakan</span>
                        <p class="text-xl font-black text-amber-600">{{ stats.total_tunggakan }}</p>
                        <span class="text-[10px] text-slate-400">pelanggan</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider block">Isolir</span>
                        <p class="text-xl font-black text-red-600">{{ stats.total_isolir }}</p>
                        <span class="text-[10px] text-slate-400">pelanggan</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1 col-span-2 lg:col-span-1">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Kasir Hari Ini</span>
                        <p class="text-base font-black text-blue-600">{{ rupiah(stats.kas_hari_ini) }}</p>
                        <span class="text-[10px] text-slate-400">penerimaan tunai/tf</span>
                    </div>
                </div>

                <!-- TOOLBAR & FILTERS -->
                <div class="bg-white border border-slate-200 rounded-2xl p-3 flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <!-- Search -->
                        <div class="relative flex-1 min-w-[200px] max-w-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                            <input v-model="search" type="search" placeholder="Cari nama, ID pelanggan, WA..."
                                   class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 bg-slate-50 rounded-xl focus:border-blue-500 focus:outline-none" />
                        </div>

                        <!-- Filter Status -->
                        <select v-model="filterStatus" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Status</option>
                            <option value="AKTIF">🟢 Aktif</option>
                            <option value="ISOLIR">🔴 Isolir</option>
                        </select>

                        <!-- Filter Paket -->
                        <select v-model="filterPaket" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Paket</option>
                            <option v-for="p in paketOptions" :key="p" :value="p">{{ p }}</option>
                        </select>
                    </div>

                    <!-- Bayar Masal Action Button -->
                    <div class="flex items-center gap-2">
                        <button v-if="selectedIds.length > 0" @click="openMassPayModal"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base">task_alt</span>
                            Tandai Lunas Masal ({{ selectedIds.length }})
                        </button>
                    </div>
                </div>

                <!-- TABLE KASIR PEMBAYARAN -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                    <th class="p-3.5 text-center w-10">
                                        <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                                               class="rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer" />
                                    </th>
                                    <th class="p-3.5 text-center w-12">No</th>
                                    <th class="p-3.5">Pelanggan</th>
                                    <th class="p-3.5">Paket</th>
                                    <th class="p-3.5">Alamat</th>
                                    <th class="p-3.5 text-right">Tagihan / Bln</th>
                                    <th class="p-3.5 text-center">Status Periode Ini</th>
                                    <th class="p-3.5 text-center">Aksi Kasir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in pelanggan.data" :key="item.id" class="hover:bg-blue-50/20 transition-colors">
                                    <!-- Checkbox -->
                                    <td class="p-3.5 text-center">
                                        <input type="checkbox" :value="item.id" v-model="selectedIds"
                                               class="rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer" />
                                    </td>

                                    <!-- No -->
                                    <td class="p-3.5 text-center font-mono text-slate-500">{{ item.no ?? '-' }}</td>

                                    <!-- Pelanggan -->
                                    <td class="p-3.5">
                                        <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                            ID: {{ item.no_id_pel || '-' }} &bull; WA: {{ item.no_wa || '-' }}
                                        </p>
                                    </td>

                                    <!-- Paket -->
                                    <td class="p-3.5 whitespace-nowrap">
                                        <span v-if="item.paket" class="px-2 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">
                                            {{ item.paket }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>

                                    <!-- Alamat -->
                                    <td class="p-3.5 text-slate-600 max-w-xs truncate">
                                        {{ item.alamat || '-' }} RT {{ item.rt || '-' }}/RW {{ item.rw || '-' }}
                                    </td>

                                    <!-- Nominal -->
                                    <td class="p-3.5 text-right font-mono font-bold text-slate-800 whitespace-nowrap">
                                        {{ rupiah(item.total_tarikan) }}
                                    </td>

                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <span v-if="item.current_status === 'ISOLIR'"
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-red-100 text-red-700 border border-red-300">
                                            🔴 ISOLIR
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700 border border-emerald-300">
                                            🟢 AKTIF
                                        </span>
                                    </td>

                                    <!-- Aksi Kasir -->
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Tombol Bayar Single -->
                                            <button @click="openPayModal(item)"
                                                    title="Input / Update Pembayaran"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                                <span class="material-symbols-outlined text-xs">payments</span>
                                                Bayar
                                            </button>

                                            <!-- Tombol Pengingat WA (Jika belum bayar & ada WA) -->
                                            <a v-if="!item.pembayaran_periode && item.no_wa"
                                               :href="getWaReminderUrl(item)" target="_blank"
                                               title="Kirim WA Pengingat Jatuh Tempo (Tgl 10)"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                                <span class="material-symbols-outlined text-xs">chat</span>
                                                <span>Pengingat WA</span>
                                            </a>

                                            <!-- WhatsApp Struk -->
                                            <a v-if="item.pembayaran_periode?.wa_struk_link"
                                               :href="item.pembayaran_periode.wa_struk_link" target="_blank"
                                               title="Kirim Struk WA"
                                               class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 transition">
                                                <span class="material-symbols-outlined text-sm">send</span>
                                            </a>

                                            <!-- Cetak Struk Thermal PDF/Print -->
                                            <a v-if="item.pembayaran_periode"
                                               :href="route('wifi.pembayaran.struk', item.pembayaran_periode.id)" target="_blank"
                                               title="Cetak Struk Thermal 80mm"
                                               class="p-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition">
                                                <span class="material-symbols-outlined text-sm">print</span>
                                            </a>

                                            <!-- Histori Bayar -->
                                            <button @click="openHistoryModal(item)"
                                                    title="Lihat Histori Pembayaran"
                                                    class="p-1.5 rounded-lg bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200 transition">
                                                <span class="material-symbols-outlined text-sm">history</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="pelanggan.data.length === 0">
                                    <td colspan="8" class="p-8 text-center text-slate-400">
                                        Tidak menemukan data tagihan pelanggan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="pelanggan.data.length > 0"
                         class="px-4 py-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50">
                        <p class="text-[11px] text-slate-500">
                            Menampilkan {{ pelanggan.from }}–{{ pelanggan.to }} dari {{ pelanggan.total }} pelanggan
                        </p>

                        <div class="flex items-center gap-1">
                            <template v-for="link in pelanggan.links" :key="link.label">
                                <a v-if="link.url" :href="link.url"
                                   @click.prevent="router.get(link.url, {}, { preserveScroll: true })"
                                   :class="['min-w-[30px] h-7 flex items-center justify-center rounded-md text-xs font-bold transition',
                                            link.active ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-blue-400']">
                                    <span v-html="link.label"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: BAYAR SINGLE
    ══════════════════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
    <div v-if="modalMode === 'pay' && selectedCustomer"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase">Input Pembayaran WiFi</h3>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitSinglePay" class="space-y-3">
                <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl">
                    <p class="text-xs font-bold text-slate-900">{{ selectedCustomer.nama }}</p>
                    <p class="text-[11px] text-slate-500">ID: {{ selectedCustomer.no_id_pel || '-' }} &bull; Paket: {{ selectedCustomer.paket || '-' }}</p>
                    <p class="text-[11px] text-blue-700 font-bold mt-1">
                        Periode: {{ getBulanName(selectedBulan) }} {{ selectedTahun }} ({{ selectedGelombang === '1_15' ? 'Gel. 1' : 'Gel. 2' }})
                    </p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                    <select v-model="payForm.status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none font-bold">
                        <option value="AKTIF">AKTIF (LUNAS)</option>
                        <option value="ISOLIR">ISOLIR</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal Bayar</label>
                        <input v-model="payForm.tanggal_bayar" type="date" required
                               class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Metode</label>
                        <select v-model="payForm.metode_pembayaran" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none font-bold">
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jumlah Bayar (Rp)</label>
                    <input v-model="payForm.jumlah_bayar" type="number" step="1000" min="0" required
                           class="w-full text-sm font-bold text-slate-900 border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none" />
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan (Opsional)</label>
                    <input v-model="payForm.catatan" type="text" placeholder="Misal: titip via Pak RT"
                           class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none" />
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Batal</button>
                    <button type="submit" :disabled="payForm.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow transition">
                        {{ payForm.processing ? 'Menyimpan...' : 'Simpan Pembayaran' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: BAYAR MASAL
    ══════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'bayar_masal'"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase">Pembayaran Masal / Kolektor</h3>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitMassPay" class="space-y-3">
                <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl text-xs text-emerald-800">
                    <p class="font-bold">Akan memproses {{ selectedIds.length }} pelanggan dipilih</p>
                    <p class="text-[11px] mt-0.5">Periode: {{ getBulanName(selectedBulan) }} {{ selectedTahun }} ({{ selectedGelombang === '1_15' ? 'Gel. 1' : 'Gel. 2' }})</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
                        <select v-model="massForm.status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option value="AKTIF">AKTIF (LUNAS)</option>
                            <option value="ISOLIR">ISOLIR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Metode</label>
                        <select v-model="massForm.metode_pembayaran" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal Bayar</label>
                    <input v-model="massForm.tanggal_bayar" type="date" required
                           class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2" />
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Batal</button>
                    <button type="submit" :disabled="massForm.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow transition">
                        {{ massForm.processing ? 'Memproses...' : `Proses ${selectedIds.length} Pelanggan` }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: HISTORI PEMBAYARAN PELANGGAN
    ══════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'history' && selectedCustomer"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Histori Transaksi Pembayaran</h3>
                    <p class="text-xs text-slate-500">{{ selectedCustomer.nama }} &bull; ID: {{ selectedCustomer.no_id_pel || '-' }}</p>
                </div>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div v-if="historyLoading" class="py-12 text-center text-slate-400 text-xs">
                Memuat riwayat transaksi...
            </div>

            <div v-else-if="customerHistory.length === 0" class="py-12 text-center text-slate-400 text-xs">
                Belum ada riwayat transaksi pembayaran tercatat.
            </div>

            <div v-else class="flex-1 overflow-y-auto space-y-2 pr-1">
                <div v-for="h in customerHistory" :key="h.id"
                     class="border border-slate-200 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-slate-50 transition">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-slate-900">{{ h.no_transaksi }}</span>
                            <span :class="h.status === 'LUNAS' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                  class="px-2 py-0.5 rounded text-[10px] font-black">
                                {{ h.status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Periode: <b>{{ getBulanName(h.periode_bulan) }} {{ h.periode_tahun }}</b> ({{ h.gelombang === '1_15' ? 'Gel. 1' : 'Gel. 2' }})
                            &bull; Tgl: {{ formatDate(h.tanggal_bayar) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 justify-between sm:justify-end border-t sm:border-t-0 pt-2 sm:pt-0">
                        <div class="text-right">
                            <p class="font-mono font-black text-sm text-slate-900">{{ rupiah(h.jumlah_bayar) }}</p>
                            <p class="text-[10px] text-slate-400">{{ h.metode_pembayaran }} &bull; Kasir: {{ h.kasir?.nama || '-' }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <a v-if="h.wa_struk_link" :href="h.wa_struk_link" target="_blank"
                               class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 text-xs font-bold hover:bg-emerald-100" title="Kirim Struk WA">
                                <span class="material-symbols-outlined text-sm">send</span>
                            </a>
                            <a :href="route('wifi.pembayaran.struk', h.id)" target="_blank"
                               class="p-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold hover:bg-slate-200" title="Cetak Struk Thermal">
                                <span class="material-symbols-outlined text-sm">print</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100 shrink-0">
                <button @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Tutup</button>
            </div>
        </div>
    </div>
    </Teleport>
</template>
