<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, reactive } from 'vue';

const props = defineProps({
    unit:         { type: Object, required: true },
    user:         { type: Object, required: true },
    pelanggan:    { type: Object, required: true },
    paketOptions: { type: Array,  default: () => [] },
    wifiSettings: { type: Object, default: () => ({}) },
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

const search              = ref(props.filters.search         ?? '');
const filterStatusBayar   = ref(props.filters.status_bayar   ?? '');
const filterStatusKoneksi = ref(props.filters.status_koneksi ?? '');
const filterPaket         = ref(props.filters.paket          ?? '');
const perPage             = ref(props.filters.per_page       ?? '25');

const selectedIds       = ref([]);
const selectAll         = ref(false);

const modalMode         = ref(''); // 'pay' | 'bayar_masal' | 'history' | 'broadcast_wa'
const selectedCustomer  = ref(null);
const customerHistory   = ref([]);
const historyLoading    = ref(false);

// ── Formatting Helpers ──────────────────────────────────────────────────────
const namaBulanMap = [
    { id: 1, name: 'Januari' }, { id: 2, name: 'Februari' }, { id: 3, name: 'Maret' },
    { id: 4, name: 'April' },   { id: 5, name: 'Mei' },      { id: 6, name: 'Juni' },
    { id: 7, name: 'Juli' },     { id: 8, name: 'Agustus' },  { id: 9, name: 'September' },
    { id: 10, name: 'Oktober' },{ id: 11, name: 'November' },{ id: 12, name: 'Desember' },
];

function getBulanName(id) {
    if (!id && id !== 0) return '-';
    const val = (id && typeof id === 'object' && 'value' in id) ? id.value : id;
    const found = namaBulanMap.find(b => Number(b.id) === Number(val));
    return found ? found.name : String(val);
}

function rupiah(val) {
    if (val === null || val === undefined || val === '') return 'Rp 0';
    try {
        const num = Number(val) || 0;
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    } catch {
        return 'Rp ' + val;
    }
}

function formatDate(val) {
    if (!val) return '-';
    try {
        return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch {
        return String(val);
    }
}

function getWaReminderUrl(item) {
    if (!item || !item.no_wa) return '#';
    try {
        let phone = String(item.no_wa).replace(/\D/g, '');
        if (phone.startsWith('0')) phone = '62' + phone.slice(1);

        const blnVal = (selectedBulan && typeof selectedBulan === 'object' && 'value' in selectedBulan) ? selectedBulan.value : selectedBulan;
        const thnVal = (selectedTahun && typeof selectedTahun === 'object' && 'value' in selectedTahun) ? selectedTahun.value : selectedTahun;
        const bulan = getBulanName(blnVal);
        const tahun = thnVal;
        const nominal = rupiah(item.total_tarikan || 0);

        const provider = item.provider;
        const companyName = provider?.header_wa || provider?.nama_provider || 'PT. MEDIA CEPAT INDONESIA';
        const bankAccounts = (provider?.bank_accounts && provider.bank_accounts.length)
            ? provider.bank_accounts
            : (props.wifiSettings?.bank_accounts && props.wifiSettings.bank_accounts.length)
                ? props.wifiSettings.bank_accounts
                : [
                    { bank: 'BRI', no_rek: '3117-01-022918-53-6', atas_nama: 'Rasmini' },
                    { bank: 'Mandiri', no_rek: '180-00-1106813-9', atas_nama: 'Rasmini' },
                    { bank: 'BCA', no_rek: '4220318198', atas_nama: 'Rasmini' }
                ];

        const bankText = bankAccounts.map(b => `• *${b.bank}*: ${b.no_rek} a/n ${b.atas_nama}`).join('\n');

        const message = `*PENGINGAT TAGIHAN INTERNET BUMDES CIWUNI*\n----------------------------------------\nKepada Yth. Bpk/Ibu *${item.nama}*\nID Pelanggan : *${item.no_id_pel || '-'}*\nPaket        : *${item.paket || '-'}*\nTagihan      : *${bulan} ${tahun}*\nTotal        : *${nominal}*\n\n*Masa Pembayaran: Tanggal 1 s.d. 10*\nMohon melakukan pembayaran sebelum tanggal 10 agar jaringan internet tetap AKTIF dan tidak ter-ISOLIR.\n\n*PILIHAN REKENING TRANSFER:*\n${bankText}\n\n(Setelah transfer, mohon kirimkan bukti transfer ke nomor ini. Abaikan pesan ini jika sudah bayar.)\n\nTerima kasih atas perhatian & kerja samanya.\n\n_${companyName} / BUMDes Ciwuni_`;

        return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
    } catch {
        return '#';
    }
}

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
        bulan:          selectedBulan.value,
        tahun:          selectedTahun.value,
        gelombang:      selectedGelombang.value,
        search:         search.value              || undefined,
        status_bayar:   filterStatusBayar.value   || undefined,
        status_koneksi: filterStatusKoneksi.value || undefined,
        paket:          filterPaket.value         || undefined,
        per_page:       perPage.value             || undefined,
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

// ── Form Single / Multi Pay ────────────────────────────────────────────────
const todayStr = new Date().toISOString().split('T')[0];

const payBulanMulai  = ref(Number(selectedBulan.value));
const payTahunMulai  = ref(Number(selectedTahun.value));
const payDurasiBulan = ref(1); // 1 = 1 bulan, 2 = 2 bulan sekaligus, dst.

const payForm = useForm({
    pelanggan_wifi_id: '',
    periode_bulan:     selectedBulan.value,
    periode_tahun:     selectedTahun.value,
    periode_list:      [],
    gelombang:         detectGelombang(todayStr),
    tanggal_bayar:     todayStr,
    jumlah_bayar:      0,
    metode_pembayaran: 'TUNAI',
    status:            'LUNAS',
    catatan:           '',
});

const updatePeriodeDanTagihan = () => {
    if (!selectedCustomer.value) return;
    const durasi = Math.max(1, parseInt(payDurasiBulan.value) || 1);
    const startB = parseInt(payBulanMulai.value) || 1;
    const startY = parseInt(payTahunMulai.value) || new Date().getFullYear();

    const list = [];
    let curB = startB;
    let curY = startY;
    for (let i = 0; i < durasi; i++) {
        list.push({ bulan: curB, tahun: curY });
        curB++;
        if (curB > 12) {
            curB = 1;
            curY++;
        }
    }
    payForm.periode_list  = list;
    payForm.periode_bulan = list[0].bulan;
    payForm.periode_tahun = list[0].tahun;

    const tarifPerBulan  = Number(selectedCustomer.value.total_tarikan) || 0;
    payForm.jumlah_bayar = tarifPerBulan * list.length;
};

const setPayPeriod = (bulan, tahun, durasi) => {
    payBulanMulai.value  = Number(bulan);
    payTahunMulai.value  = Number(tahun);
    payDurasiBulan.value = Number(durasi);
    updatePeriodeDanTagihan();
};

const openPayModal = (item) => {
    selectedCustomer.value = item;
    payForm.reset();
    payForm.pelanggan_wifi_id = item.id;

    payBulanMulai.value  = Number(selectedBulan.value);
    payTahunMulai.value  = Number(selectedTahun.value);
    payDurasiBulan.value = 1;

    payForm.gelombang         = selectedGelombang.value;
    payForm.tanggal_bayar     = todayStr;
    payForm.metode_pembayaran = 'TUNAI';
    payForm.status            = 'LUNAS';
    payForm.catatan           = '';

    updatePeriodeDanTagihan();
    modalMode.value           = 'pay';
};

const submitSinglePay = () => {
    updatePeriodeDanTagihan();
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
    paymentToDelete.value = null;
};

// ── Delete Payment (Senior-Friendly & Safe) ────────────────────────────────
const paymentToDelete = ref(null);
const isDeleting      = ref(false);

const confirmDeletePayment = (payment, customer = null) => {
    paymentToDelete.value = {
        ...payment,
        customer_nama: customer?.nama || selectedCustomer.value?.nama || 'Pelanggan',
        customer_id_pel: customer?.no_id_pel || selectedCustomer.value?.no_id_pel || '-',
        customer_paket: customer?.paket || selectedCustomer.value?.paket || '-',
    };
    modalMode.value = 'confirm_delete';
};

const executeDeletePayment = () => {
    if (!paymentToDelete.value) return;
    isDeleting.value = true;

    router.delete(route('wifi.pembayaran.destroy', paymentToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleting.value = false;
            if (customerHistory.value && customerHistory.value.length > 0) {
                customerHistory.value = customerHistory.value.filter(h => h.id !== paymentToDelete.value.id);
            }
            paymentToDelete.value = null;
            modalMode.value = selectedCustomer.value ? 'history' : '';
        },
        onError: () => {
            isDeleting.value = false;
        }
    });
};

const cancelDeleteModal = () => {
    paymentToDelete.value = null;
    if (selectedCustomer.value) {
        modalMode.value = 'history';
    } else {
        modalMode.value = '';
    }
};

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
                    <Link :href="route('wifi.pendapatan.index')"
                          class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                        Pendapatan Kotor
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
                                Masa Pembayaran (Tgl 1 - 10)
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

                        <!-- Masa Pembayaran Info -->
                        <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-50 text-emerald-700 px-3 py-2 rounded-xl font-black border border-emerald-200 shadow-sm">
                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                            Tenggat Tgl 10
                        </span>
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
                                <span class="px-2.5 py-0.5 bg-red-700 text-white text-[10px] font-black uppercase rounded-md tracking-wider shadow-sm flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">warning</span>
                                    {{ todayDay === 10 ? 'HARI INI JATUH TEMPO (TGL 10)' : 'H-2 TENGGAT PEMBAYARAN (TGL 10)' }}
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
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Terkumpul</span>
                        <p class="text-base font-black text-slate-900">{{ rupiah(stats.total_nominal_terkumpul) }}</p>
                        <span class="text-[10px] text-slate-400">Periode ini</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Sudah Lunas</span>
                        <p class="text-xl font-black text-emerald-600">{{ stats.total_lunas ?? stats.total_aktif }}</p>
                        <span class="text-[10px] text-slate-400">pelanggan</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1">
                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Belum Bayar</span>
                        <p class="text-xl font-black text-amber-600">{{ stats.total_belum_bayar }}</p>
                        <span class="text-[10px] text-slate-400">pelanggan ({{ stats.total_isolir }} isolir)</span>
                    </div>

                    <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-sm space-y-1 col-span-2 lg:col-span-1">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Kasir Hari Ini</span>
                        <p class="text-base font-black text-blue-600">{{ rupiah(stats.kas_hari_ini) }}</p>
                        <span class="text-[10px] text-slate-400">penerimaan tunai/tf</span>
                    </div>
                </div>

                <!-- PANDUAN EDUKASI STATUS RAMAH LANSIA (NO EMOJI - CLEAN SVG) -->
                <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start gap-4 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 space-y-1.5 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-black text-slate-900 text-sm">Panduan Kasir: Memahami Status Bayar vs Koneksi WiFi</h4>
                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-bold text-[10px] rounded-full border border-blue-200">
                                {{ todayDay <= 10 ? 'Periode Saat Ini: Masa Pembayaran (Tgl 1 - 10)' : 'Periode Saat Ini: Masa Penertiban / Isolir (Lewat Tgl 10)' }}
                            </span>
                        </div>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            Sistem memisahkan antara <strong>Status Bayar</strong> (catatan kas) dan <strong>Koneksi WiFi</strong> (layanan internet warga) agar tidak membingungkan:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                            <div class="bg-white/90 p-3 rounded-xl border border-blue-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span>Masa Bayar (Tanggal 1 s.d. 10)</span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">
                                    Warga yang <strong>Belum Bayar</strong> bulan ini internetnya tetap <strong>AKTIF</strong> jika <em>tagihan bulan lalunya lunas</em>. Kasir dapat menekan tombol <em>Pengingat WA</em>.
                                </p>
                            </div>
                            <div class="bg-white/90 p-3 rounded-xl border border-blue-100 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-slate-900 text-xs">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                    <span>Koneksi Terisolir (ISOLIR)</span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">
                                    Koneksi otomatis <strong>ISOLIR</strong> jika: (1) Menunggak di bulan sebelumnya, atau (2) Belum bayar bulan ini dan sudah lewat tanggal 10.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOOLBAR & FILTERS -->
                <div class="bg-white border border-slate-200 rounded-2xl p-3 flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <!-- Search -->
                        <div class="relative flex-1 min-w-[180px] max-w-xs">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                            <input v-model="search" type="search" placeholder="Cari nama, ID, WA..."
                                   class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 bg-slate-50 rounded-xl focus:border-blue-500 focus:outline-none" />
                        </div>

                        <!-- Filter 1: Status Pembayaran -->
                        <select v-model="filterStatusBayar" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-slate-700 focus:border-blue-500 focus:outline-none font-semibold">
                            <option value="">Semua Pembayaran</option>
                            <option value="LUNAS">Sudah Bayar (Lunas)</option>
                            <option value="BELUM_BAYAR">Belum Bayar</option>
                        </select>

                        <!-- Filter 2: Status Koneksi WiFi -->
                        <select v-model="filterStatusKoneksi" @change="applyFilters"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-slate-700 focus:border-blue-500 focus:outline-none font-semibold">
                            <option value="">Semua Koneksi</option>
                            <option value="AKTIF">Koneksi Aktif</option>
                            <option value="ISOLIR">Koneksi Isolir</option>
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

                    <!-- Mobile: Card List -->
                    <div class="md:hidden divide-y divide-slate-100">
                        <div v-if="pelanggan.data.length === 0" class="p-8 text-center text-slate-400">
                            Tidak menemukan data tagihan pelanggan.
                        </div>
                        <div v-for="item in pelanggan.data" :key="item.id"
                             class="p-4 hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-start gap-2 min-w-0">
                                    <input type="checkbox" :value="item.id" v-model="selectedIds"
                                           class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer shrink-0" />
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-xs truncate">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">ID: {{ item.no_id_pel || '-' }} &bull; {{ item.no_wa || 'No WA -' }}</p>
                                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                            <span v-if="item.paket" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">{{ item.paket }}</span>
                                            <span class="text-[10px] text-slate-500">{{ item.alamat || '-' }} RT {{ item.rt || '-' }}/RW {{ item.rw || '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right space-y-1">
                                    <p class="font-mono font-bold text-slate-800 text-xs">{{ rupiah(item.total_tarikan) }}</p>
                                    <div class="flex flex-col items-end gap-1">
                                        <span v-if="item.status_bayar === 'LUNAS'"
                                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>LUNAS
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>BELUM BAYAR
                                        </span>

                                        <span v-if="item.status_koneksi === 'ISOLIR'"
                                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-rose-100 text-rose-700 border border-rose-300">
                                            Koneksi: ISOLIR
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                            Koneksi: AKTIF
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button @click="openPayModal(item)"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                    <span class="material-symbols-outlined text-xs">payments</span>Bayar
                                </button>
                                <a v-if="!item.pembayaran_periode && item.no_wa"
                                   :href="getWaReminderUrl(item)" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                    <span class="material-symbols-outlined text-xs">chat</span>WA
                                </a>
                                <a v-if="item.pembayaran_periode?.wa_struk_link"
                                   :href="item.pembayaran_periode.wa_struk_link" target="_blank"
                                   class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 transition" title="Kirim Struk WA">
                                    <span class="material-symbols-outlined text-sm">send</span>
                                </a>
                                <a v-if="item.pembayaran_periode"
                                   :href="route('wifi.pembayaran.struk', item.pembayaran_periode.id)" target="_blank"
                                   class="p-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition" title="Cetak Struk">
                                    <span class="material-symbols-outlined text-sm">print</span>
                                </a>
                                <button v-if="item.pembayaran_periode"
                                        @click="confirmDeletePayment(item.pembayaran_periode, item)"
                                        title="Batalkan Pembayaran Bulan Ini"
                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 hover:text-rose-700 transition"
                                        aria-label="Batalkan Pembayaran">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                <button @click="openHistoryModal(item)"
                                        class="p-1.5 rounded-lg bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200 transition" title="Histori">
                                    <span class="material-symbols-outlined text-sm">history</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Table -->
                    <div class="hidden md:block overflow-x-auto">
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
                                    <th class="p-3.5 text-right whitespace-nowrap">Tagihan / Bln</th>
                                    <th class="p-3.5 text-center whitespace-nowrap">Status Bayar</th>
                                    <th class="p-3.5 text-center whitespace-nowrap">Koneksi WiFi</th>
                                    <th class="p-3.5 text-center whitespace-nowrap">Aksi Kasir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in pelanggan.data" :key="item.id" class="hover:bg-blue-50/20 transition-colors">
                                    <td class="p-3.5 text-center">
                                        <input type="checkbox" :value="item.id" v-model="selectedIds"
                                               class="rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer" />
                                    </td>
                                    <td class="p-3.5 text-center font-mono text-slate-500">{{ item.no ?? '-' }}</td>
                                    <td class="p-3.5">
                                        <p class="font-bold text-slate-900">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                            ID: {{ item.no_id_pel || '-' }} &bull; WA: {{ item.no_wa || '-' }}
                                        </p>
                                    </td>
                                    <td class="p-3.5 whitespace-nowrap">
                                        <span v-if="item.paket" class="px-2 py-0.5 bg-blue-100 text-blue-700 border border-blue-200 rounded text-[10px] font-semibold">
                                            {{ item.paket }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="p-3.5 text-slate-600 max-w-xs truncate">
                                        {{ item.alamat || '-' }} RT {{ item.rt || '-' }}/RW {{ item.rw || '-' }}
                                    </td>
                                    <td class="p-3.5 text-right font-mono font-bold text-slate-800 whitespace-nowrap">
                                        {{ rupiah(item.total_tarikan) }}
                                    </td>
                                    <!-- Status Bayar -->
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <span v-if="item.status_bayar === 'LUNAS'"
                                              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            LUNAS
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            BELUM BAYAR
                                        </span>
                                    </td>
                                    <!-- Koneksi WiFi -->
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <div class="inline-flex flex-col items-center">
                                            <span v-if="item.status_koneksi === 'ISOLIR'"
                                                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-100 text-rose-700 border border-rose-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                ISOLIR
                                            </span>
                                            <span v-else
                                                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                AKTIF
                                            </span>
                                            <span class="text-[9px] mt-0.5 font-semibold" :class="item.status_koneksi === 'ISOLIR' ? 'text-rose-600' : 'text-slate-400'">{{ item.koneksi_note }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button @click="openPayModal(item)" title="Input / Update Pembayaran"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                                <span class="material-symbols-outlined text-xs">payments</span>Bayar
                                            </button>
                                            <a v-if="!item.pembayaran_periode && item.no_wa"
                                               :href="getWaReminderUrl(item)" target="_blank"
                                               title="Kirim WA Pengingat"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg transition shadow-sm">
                                                <span class="material-symbols-outlined text-xs">chat</span>
                                                <span>Pengingat WA</span>
                                            </a>
                                            <a v-if="item.pembayaran_periode?.wa_struk_link"
                                               :href="item.pembayaran_periode.wa_struk_link" target="_blank"
                                               title="Kirim Struk WA"
                                               class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 transition">
                                                <span class="material-symbols-outlined text-sm">send</span>
                                            </a>
                                            <a v-if="item.pembayaran_periode"
                                               :href="route('wifi.pembayaran.struk', item.pembayaran_periode.id)" target="_blank"
                                               title="Cetak Struk Thermal 80mm"
                                               class="p-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition">
                                                <span class="material-symbols-outlined text-sm">print</span>
                                            </a>
                                            <button v-if="item.pembayaran_periode"
                                                    @click="confirmDeletePayment(item.pembayaran_periode, item)"
                                                    title="Batalkan Pembayaran Bulan Ini"
                                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 hover:text-rose-700 transition"
                                                    aria-label="Batalkan Pembayaran">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                            <button @click="openHistoryModal(item)" title="Lihat Histori Pembayaran"
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
         MODAL: BAYAR SINGLE / MULTI-BULAN
    ══════════════════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
    <div v-if="modalMode === 'pay' && selectedCustomer"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         @click.self="closeModal">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-5 sm:p-6 space-y-4 my-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 text-xl">payments</span>
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase">Input Pembayaran WiFi</h3>
                </div>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitSinglePay" class="space-y-3.5">
                <!-- Alert Error jika ada kegagalan -->
                <div v-if="Object.keys(payForm.errors).length > 0" class="p-3 bg-red-50 border border-red-200 rounded-xl space-y-1">
                    <p class="text-xs font-bold text-red-800 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">warning</span>
                        Gagal Menyimpan Pembayaran:
                    </p>
                    <ul class="text-[11px] text-red-700 list-disc list-inside">
                        <li v-for="(err, field) in payForm.errors" :key="field">{{ err }}</li>
                    </ul>
                </div>

                <!-- Info Pelanggan -->
                <div class="bg-blue-50/70 border border-blue-100 p-3 rounded-xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-900">{{ selectedCustomer.nama }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">ID: {{ selectedCustomer.no_id_pel || '-' }} &bull; Paket: {{ selectedCustomer.paket || '-' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Tarif / Bln</span>
                        <span class="text-xs font-mono font-bold text-blue-700">{{ rupiah(selectedCustomer.total_tarikan) }}</span>
                    </div>
                </div>

                <!-- Alert Shortcut jika Belum Lunas Bulan Lalu -->
                <div v-if="!selectedCustomer.lunas_bulan_lalu && !selectedCustomer.is_new_this_month"
                     class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-base shrink-0 mt-0.5">warning</span>
                        <div>
                            <p class="text-xs font-bold text-amber-900">
                                Tagihan {{ getBulanName(selectedCustomer.prev_bulan) }} {{ selectedCustomer.prev_tahun }} (Bulan Lalu) Belum Lunas
                            </p>
                            <p class="text-[10px] text-amber-700 mt-0.5">
                                Pilih tombol cepat di bawah ini untuk membayar bulan lalu saja atau 2 bulan sekaligus:
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-1 flex-wrap">
                        <button type="button"
                                @click="setPayPeriod(selectedCustomer.prev_bulan, selectedCustomer.prev_tahun, 1)"
                                :class="payBulanMulai === selectedCustomer.prev_bulan && payDurasiBulan === 1 ? 'bg-amber-600 text-white font-bold' : 'bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 font-semibold'"
                                class="px-2.5 py-1 text-[11px] rounded-lg transition shadow-2xs">
                            Bayar {{ getBulanName(selectedCustomer.prev_bulan) }} Saja (1 Bln)
                        </button>
                        <button type="button"
                                @click="setPayPeriod(selectedCustomer.prev_bulan, selectedCustomer.prev_tahun, 2)"
                                :class="payBulanMulai === selectedCustomer.prev_bulan && payDurasiBulan === 2 ? 'bg-amber-600 text-white font-bold' : 'bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 font-semibold'"
                                class="px-2.5 py-1 text-[11px] rounded-lg transition shadow-2xs">
                            Bayar 2 Bulan Sekaligus ({{ getBulanName(selectedCustomer.prev_bulan) }} + {{ getBulanName(selectedBulan) }})
                        </button>
                    </div>
                </div>

                <!-- Pemilihan Periode & Multi-Bulan -->
                <div class="space-y-2.5 bg-slate-50 border border-slate-200 p-3 rounded-xl">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-slate-600 uppercase">Periode Tagihan</label>
                        <span class="text-[10px] font-bold text-blue-600 font-mono">
                            {{ payDurasiBulan === 1 ? '1 Bulan' : `${payDurasiBulan} Bulan Sekaligus` }}
                        </span>
                    </div>

                    <!-- Pilihan Bulan Mulai & Tahun Mulai -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-400 uppercase mb-0.5">Mulai Bulan</label>
                            <select v-model="payBulanMulai" @change="updatePeriodeDanTagihan"
                                    class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold bg-white focus:border-blue-500 focus:outline-none">
                                <option v-for="b in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="b" :value="b">
                                    {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][b-1] }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-400 uppercase mb-0.5">Tahun</label>
                            <select v-model="payTahunMulai" @change="updatePeriodeDanTagihan"
                                    class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 font-bold bg-white focus:border-blue-500 focus:outline-none">
                                <option v-for="y in [2025, 2026, 2027, 2028]" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quick Buttons Durasi Bulan (1, 2, 3, 6, 12 Bln) -->
                    <div>
                        <label class="block text-[9px] font-semibold text-slate-400 uppercase mb-1">Jumlah Bulan yang Dibayar</label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button v-for="d in [1, 2, 3, 6, 12]" :key="d" type="button"
                                    @click="payDurasiBulan = d; updatePeriodeDanTagihan();"
                                    :class="payDurasiBulan === d ? 'bg-blue-600 text-white shadow-2xs font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold'"
                                    class="px-2.5 py-1 text-xs rounded-lg transition">
                                {{ d }} Bulan {{ d > 1 ? 'Sekaligus' : '' }}
                            </button>
                        </div>
                    </div>

                    <!-- Rincian Periode yang Dilunasi -->
                    <div v-if="payForm.periode_list && payForm.periode_list.length > 0"
                         class="pt-2 border-t border-slate-200 text-[11px] space-y-1">
                        <p class="font-bold text-slate-600 text-[10px] uppercase">Rincian Periode yang Akan Dilunasi:</p>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-for="(p, idx) in payForm.periode_list" :key="idx"
                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[11px] font-bold font-mono">
                                <span class="material-symbols-outlined text-xs">check</span>
                                {{ getBulanName(p.bulan) }} {{ p.tahun }}
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                    <select v-model="payForm.status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none font-bold">
                        <option value="LUNAS">LUNAS / AKTIF</option>
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
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Total Bayar (Rp)</label>
                        <span v-if="payDurasiBulan > 1" class="text-[10px] text-slate-400 font-mono">
                            {{ payDurasiBulan }} × {{ rupiah(selectedCustomer.total_tarikan) }}
                        </span>
                    </div>
                    <input v-model="payForm.jumlah_bayar" type="number" step="1000" min="0" required
                           class="w-full text-sm font-bold text-slate-900 border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none" />
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan (Opsional)</label>
                    <input v-model="payForm.catatan" type="text" placeholder="Misal: titip via Pak RT"
                           class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:outline-none" />
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="payForm.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow transition disabled:opacity-60">
                        {{ payForm.processing ? 'Menyimpan...' : (payDurasiBulan > 1 ? `Simpan Pembayaran (${payDurasiBulan} Bulan)` : 'Simpan Pembayaran') }}
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
                    <p class="text-[11px] mt-0.5">Periode: {{ getBulanName(selectedBulan) }} {{ selectedTahun }} (Masa Bayar Tgl 1 - 10)</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
                        <select v-model="massForm.status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option value="LUNAS">LUNAS / AKTIF</option>
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
                            <span class="font-mono text-xs font-bold text-slate-900">{{ h.no_transaksi?.replace(/^TRX-?/i, '') }}</span>
                            <span :class="h.status === 'ISOLIR' ? 'bg-red-100 text-red-700 border-red-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200'"
                                  class="px-2 py-0.5 border rounded text-[10px] font-extrabold uppercase">
                                {{ h.status === 'ISOLIR' ? 'ISOLIR' : 'AKTIF' }}
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
                            <button type="button"
                                    @click="confirmDeletePayment(h, selectedCustomer)"
                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 hover:text-rose-700 text-xs font-bold transition"
                                    title="Hapus / Batalkan Transaksi">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-100 shrink-0">
                <button @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: KONFIRMASI HAPUS TRANSAKSI (SENIOR-FRIENDLY & NO EMOJI)
    ══════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'confirm_delete' && paymentToDelete"
         class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
         @click.self="cancelDeleteModal">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 sm:p-7 space-y-5 border border-slate-200">
            <!-- Header with Warning SVG Icon -->
            <div class="flex items-start gap-3.5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-extrabold text-slate-900">Batalkan / Hapus Transaksi?</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Mohon periksa rincian data di bawah ini sebelum menghapus.</p>
                </div>
                <button @click="cancelDeleteModal" class="text-slate-400 hover:text-slate-700 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Detail Transaksi Card -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">No. Struk Transaksi</span>
                    <span class="font-mono font-extrabold text-slate-900">#{{ paymentToDelete.no_transaksi?.replace(/^TRX-?/i, '') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">Nama Pelanggan</span>
                    <span class="font-bold text-slate-900">{{ paymentToDelete.customer_nama }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">Periode Tagihan</span>
                    <span class="font-bold text-slate-800">{{ getBulanName(paymentToDelete.periode_bulan) }} {{ paymentToDelete.periode_tahun }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">Tanggal Pembayaran</span>
                    <span class="font-semibold text-slate-700">{{ formatDate(paymentToDelete.tanggal_bayar) }}</span>
                </div>
                <div class="flex justify-between items-center text-xs border-t border-slate-200 pt-2.5">
                    <span class="text-slate-600 font-bold">Total Pembayaran</span>
                    <span class="font-mono font-black text-lg text-rose-600">{{ rupiah(paymentToDelete.jumlah_bayar) }}</span>
                </div>
            </div>

            <!-- Notice Box with Warning SVG -->
            <div class="flex items-start gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs leading-relaxed">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p>
                    <strong>Perhatian:</strong> Menghapus transaksi ini akan mengembalikan status tagihan pelanggan menjadi <strong>Belum Bayar</strong>, serta mengurangi pembukuan kas hari ini.
                </p>
            </div>

            <!-- Buttons Action -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="cancelDeleteModal" :disabled="isDeleting"
                        class="px-5 py-2.5 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 rounded-xl transition">
                    Batal / Kembali
                </button>
                <button type="button" @click="executeDeletePayment" :disabled="isDeleting"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 rounded-xl shadow transition">
                    <svg v-if="isDeleting" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ isDeleting ? 'Menghapus Transaksi...' : 'Ya, Hapus Transaksi Ini' }}
                </button>
            </div>
        </div>
    </div>
    </Teleport>
</template>
