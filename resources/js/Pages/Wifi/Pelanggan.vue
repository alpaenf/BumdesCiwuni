<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, reactive } from 'vue';

const props = defineProps({
    unit:          { type: Object, required: true },
    user:          { type: Object, required: true },
    pelanggan:     { type: Object, required: true },
    paketOptions:  { type: Array, default: () => [] },
    rtOptions:     { type: Array, default: () => [] },
    rwOptions:     { type: Array, default: () => [] },
    providersList: { type: Array, default: () => [] },
    filters:       { type: Object, default: () => ({}) },
});

// ─── Sidebar ──────────────────────────────────────────────
const isSidebarOpen = ref(false);

const logout = () => { router.post(route('logout')); };

// ─── Search & Filters ──────────────────────────────────────
const search          = ref(props.filters.search          ?? '');
const filterPaket     = ref(props.filters.paket           ?? '');
const filterGelombang = ref(props.filters.gelombang       ?? '');
const filterStatus    = ref(props.filters.status          ?? '');
const filterRt        = ref(props.filters.rt              ?? '');
const filterRw        = ref(props.filters.rw              ?? '');
const filterTglDari   = ref(props.filters.tanggal_dari    ?? '');
const filterTglSampai = ref(props.filters.tanggal_sampai  ?? '');
const sortField       = ref(props.filters.sort            ?? 'no');
const sortDir         = ref(props.filters.dir             ?? 'asc');
const perPage         = ref(props.filters.per_page        ?? '25');

const isFilterOpen = ref(false);

let searchTimeout = null;
const applySearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
};
watch(search, applySearch);

const setGelombangFilter = (gel) => {
    filterGelombang.value = gel;
    applyFilters();
};

const applyFilters = () => {
    router.get(route('wifi.pelanggan.index'), {
        search:         search.value          || undefined,
        paket:          filterPaket.value     || undefined,
        gelombang:      filterGelombang.value || undefined,
        status:         filterStatus.value    || undefined,
        rt:             filterRt.value        || undefined,
        rw:             filterRw.value        || undefined,
        tanggal_dari:   filterTglDari.value   || undefined,
        tanggal_sampai: filterTglSampai.value || undefined,
        sort:           sortField.value       || undefined,
        dir:            sortDir.value         || undefined,
        per_page:       perPage.value         || undefined,
    }, { preserveScroll: true, replace: true });
};

const resetFilters = () => {
    search.value = ''; filterPaket.value = ''; filterGelombang.value = '';
    filterStatus.value = '';
    filterRt.value = ''; filterRw.value = '';
    filterTglDari.value = ''; filterTglSampai.value = '';
    applyFilters();
};

const setSort = (field) => {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value   = 'asc';
    }
    applyFilters();
};

const changePerPage = (val) => {
    perPage.value = val;
    applyFilters();
};

const doPrintPDF = () => {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (filterPaket.value) params.set('paket', filterPaket.value);
    if (filterGelombang.value) params.set('gelombang', filterGelombang.value);
    if (filterRt.value) params.set('rt', filterRt.value);
    if (filterRw.value) params.set('rw', filterRw.value);

    window.open(route('wifi.pelanggan.cetak-pdf') + '?' + params.toString(), '_blank');
};

// ─── Format helpers ────────────────────────────────────────
const rupiah = (val) => {
    if (val === null || val === undefined || val === '') return '-';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const formatDate = (val) => {
    if (!val) return '-';
    try {
        return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch { return val; }
};

const namaBulanMap = [
    { id: 1, name: 'Januari' }, { id: 2, name: 'Februari' }, { id: 3, name: 'Maret' },
    { id: 4, name: 'April' },   { id: 5, name: 'Mei' },      { id: 6, name: 'Juni' },
    { id: 7, name: 'Juli' },     { id: 8, name: 'Agustus' },  { id: 9, name: 'September' },
    { id: 10, name: 'Oktober' },{ id: 11, name: 'November' },{ id: 12, name: 'Desember' },
];

const getBulanName = (id) => namaBulanMap.find(b => b.id === Number(id))?.name ?? id;

const gpsUrl = (lat, lng) => {
    if (!lat || !lng) return null;
    return `https://www.google.com/maps?q=${lat},${lng}`;
};

// ─── Modals ────────────────────────────────────────────────
const modalMode    = ref('');   // 'add' | 'edit' | 'detail' | 'delete' | 'import' | 'quick_pay'
const selectedRow  = ref(null);
const lightboxUrl  = ref(null);

const detectGelombang = (tglString) => {
    if (!tglString) return '1_15';
    const day = parseInt(tglString.split('-')[2]) || new Date(tglString).getDate();
    return day <= 15 ? '1_15' : '16_30';
};

const todayStr = new Date().toISOString().split('T')[0];

const quickPayForm = useForm({
    pelanggan_wifi_id: '',
    periode_bulan:     new Date().getMonth() + 1,
    periode_tahun:     new Date().getFullYear(),
    gelombang:         detectGelombang(todayStr),
    tanggal_bayar:     todayStr,
    jumlah_bayar:      0,
    metode_pembayaran: 'TUNAI',
    status:            'LUNAS',
    catatan:           '',
});

watch(() => quickPayForm.tanggal_bayar, (newTgl) => {
    if (newTgl) {
        quickPayForm.gelombang = detectGelombang(newTgl);
    }
});

const openModal = (mode, row = null) => {
    modalMode.value   = mode;
    selectedRow.value = row ? { ...row } : null;
    if (mode === 'edit' && row) {
        form.reset();
        Object.keys(form).forEach(k => {
            if (k in row) form[k] = row[k] ?? '';
        });
        if (row.tanggal_daftar) {
            form.tanggal_daftar = row.tanggal_daftar.substring(0, 10);
        }
        if (row.total_tarikan > 0 && row.hasil_bumdes > 0) {
            persentaseBumdes.value = Math.round((row.hasil_bumdes / row.total_tarikan) * 100 * 10) / 10;
        } else {
            persentaseBumdes.value = 9;
        }
    }
    if (mode === 'add') {
        form.reset();
        persentaseBumdes.value = 9;
    }
    if (mode === 'quick_pay' && row) {
        quickPayForm.reset();
        quickPayForm.pelanggan_wifi_id = row.id;
        quickPayForm.periode_bulan     = new Date().getMonth() + 1;
        quickPayForm.periode_tahun     = new Date().getFullYear();
        quickPayForm.tanggal_bayar     = todayStr;
        quickPayForm.gelombang         = detectGelombang(todayStr);
        quickPayForm.jumlah_bayar      = row.total_tarikan ?? 0;
        quickPayForm.metode_pembayaran = 'TUNAI';
        quickPayForm.status            = 'LUNAS';
        quickPayForm.catatan           = '';
    }
};

const submitQuickPay = () => {
    quickPayForm.post(route('wifi.pembayaran.store'), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};
const customerHistory = ref([]);
const historyLoading  = ref(false);
const historyCustomer = ref(null);

const openHistoryModal = async (row) => {
    historyCustomer.value = row;
    customerHistory.value = [];
    historyLoading.value  = true;
    modalMode.value       = 'history';

    try {
        const res  = await fetch(route('wifi.pembayaran.history', row.id));
        const data = await res.json();
        customerHistory.value = data.history ?? [];
    } catch {
        customerHistory.value = [];
    } finally {
        historyLoading.value = false;
    }
};

const calcBumdes = (h) => {
    if (!historyCustomer.value) return 0;
    const provider = historyCustomer.value.provider;
    const tarikan  = h.jumlah_bayar || 0;
    if (provider && provider.tipe_bagi_hasil === 'FLAT_ADMIN') {
        return provider.nilai_bagi_hasil || 0;
    }
    const pct = historyCustomer.value.bagi_hasil_bumdes ?? 9;
    return Math.round(tarikan * (pct / 100));
};

const calcProvider = (h) => {
    const tarikan = h.jumlah_bayar || 0;
    return Math.max(0, tarikan - calcBumdes(h));
};

const closeModal = () => {
    modalMode.value       = '';
    selectedRow.value     = null;
    historyCustomer.value = null;
    customerHistory.value = [];
    importState.file  = null;
    importState.preview = null;
    importState.errors  = [];
    importState.loading = false;
};

// ─── Form ──────────────────────────────────────────────────
const persentaseBumdes = ref(9);

const form = useForm({
    provider_wifi_id:            '',
    no:                          '',
    nama:                        '',
    tanggal_daftar:              '',
    paket:                       '',
    nik:                         '',
    alamat:                      '',
    rt:                          '',
    rw:                          '',
    no_id_pel:                   '',
    no_wa:                       '',
    total_dasar_tarikan_non_ppn: '',
    ppn_dan_pph:                 '',
    ppn_pph:                     '',
    total_tarikan:               '',
    bagi_hasil_bumdes:           9,
    hasil_bumdes:                '',
    nota_bayar_provider:         '',
    total_provider:              '',
    gelombang:                   '1_15',
    gps_long:                    '',
    gps_lat:                     '',
    foto_rumah:                  null,
});

const selectedProviderObj = computed(() => {
    return props.providersList.find(p => p.id === form.provider_wifi_id) ?? null;
});

const autoCalculateKeuangan = () => {
    const total = parseFloat(form.total_tarikan) || 0;
    const provider = selectedProviderObj.value;

    if (provider && provider.tipe_bagi_hasil === 'FLAT_ADMIN') {
        const adminFee = parseFloat(provider.nilai_bagi_hasil) || 0;
        form.hasil_bumdes = adminFee;
        form.total_provider = Math.max(0, total - adminFee);
        form.bagi_hasil_bumdes = adminFee;
    } else {
        const pct = parseFloat(persentaseBumdes.value) || 9;
        if (total > 0) {
            form.hasil_bumdes = Math.round(total * (pct / 100));
            form.total_provider = total - form.hasil_bumdes;
            form.bagi_hasil_bumdes = pct;
        }
    }
    form.total_dasar_tarikan_non_ppn = total;
};

watch(() => form.provider_wifi_id, (newVal) => {
    const provider = selectedProviderObj.value;
    if (provider && provider.tipe_bagi_hasil === 'PERSENTASE') {
        persentaseBumdes.value = provider.nilai_bagi_hasil;
    }
    autoCalculateKeuangan();
});

watch(() => form.total_tarikan, autoCalculateKeuangan);
watch(persentaseBumdes, autoCalculateKeuangan);

const fotoInput = ref(null);
const fotoPreview = ref(null);
const onFotoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto_rumah = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
};

const submitForm = () => {
    const isEdit = modalMode.value === 'edit';
    const id     = selectedRow.value?.id;
    const opts   = {
        onSuccess: () => { closeModal(); },
        forceFormData: true,
    };
    if (isEdit) {
        form.transform(data => ({ ...data, _method: 'PUT' }))
            .post(route('wifi.pelanggan.update', id), opts);
    } else {
        form.post(route('wifi.pelanggan.store'), opts);
    }
};

const confirmDelete = () => {
    router.delete(route('wifi.pelanggan.destroy', selectedRow.value.id), {
        onSuccess: () => closeModal(),
        preserveScroll: true,
    });
};

// ─── Import ────────────────────────────────────────────────
const importState = reactive({
    file:    null,
    preview: null,
    errors:  [],
    loading: false,
    total:   0,
    valid:   0,
});

const onImportFileChange = (e) => {
    importState.file    = e.target.files[0];
    importState.preview = null;
    importState.errors  = [];
};

const previewImport = async () => {
    if (!importState.file) return;
    importState.loading = true;

    const fd = new FormData();
    fd.append('file', importState.file);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

    try {
        const res = await fetch(route('wifi.pelanggan.import'), {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        if (data.error) {
            importState.errors  = [{ line: 0, nama: '', errors: [data.error] }];
            importState.preview = null;
        } else {
            importState.preview = data.preview;
            importState.errors  = data.errors;
            importState.total   = data.total;
            importState.valid   = data.valid;
        }
    } catch {
        importState.errors = [{ line: 0, nama: '', errors: ['Terjadi kesalahan jaringan.'] }];
    }
    importState.loading = false;
};

const confirmImport = async () => {
    if (!importState.file || importState.errors.length > 0) return;
    importState.loading = true;

    const fd = new FormData();
    fd.append('file', importState.file);
    fd.append('confirm', '1');
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

    try {
        const res  = await fetch(route('wifi.pelanggan.import'), {
            method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        if (data.success) {
            closeModal();
            router.reload({ preserveScroll: true });
        } else {
            importState.errors = data.errors ?? [];
        }
    } catch {
        importState.errors = [{ line: 0, nama: '', errors: ['Terjadi kesalahan jaringan.'] }];
    }
    importState.loading = false;
};

// ─── Export ────────────────────────────────────────────────
const doExport = () => {
    const params = new URLSearchParams();
    if (search.value)          params.set('search',    search.value);
    if (filterPaket.value)     params.set('paket',     filterPaket.value);
    if (filterGelombang.value) params.set('gelombang', filterGelombang.value);
    if (filterStatus.value)    params.set('status',    filterStatus.value);
    window.location.href = route('wifi.pelanggan.export') + '?' + params.toString();
};

// ─── Active filter count ───────────────────────────────────
const activeFilterCount = computed(() =>
    [filterPaket.value, filterGelombang.value, filterStatus.value, filterRt.value, filterRw.value,
     filterTglDari.value, filterTglSampai.value].filter(Boolean).length
);
</script>

<template>
    <Head title="Pelanggan WiFi — Unit Internet BUMDes" />

    <!-- Lightbox Foto -->
    <div v-if="lightboxUrl" @click="lightboxUrl = null"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/90 p-4 cursor-zoom-out">
        <img :src="lightboxUrl" alt="Foto Rumah" class="max-h-[90vh] max-w-[90vw] object-contain rounded-xl shadow-2xl" />
    </div>

    <div class="min-h-screen bg-slate-50 text-slate-800 flex font-sans">

        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- ══ SIDEBAR ══════════════════════════════════════════════════════════ -->
        <!-- ══ SIDEBAR ══════════════════════════════════════════════════════════ -->
        <aside :class="['fixed md:sticky top-0 h-screen z-50 w-64 bg-white border-r border-slate-300 shrink-0 flex flex-col transition-transform duration-300',
                        isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <!-- Branding (Fixed top) -->
            <div class="p-6 pb-4 shrink-0 flex items-center justify-between gap-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="/logowifi.png" alt="Logo Wifi" class="w-10 h-10 object-contain drop-shadow-sm" />
                    <div>
                        <h2 class="text-xs font-black text-slate-900 leading-tight">Admin Unit Wifi</h2>
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
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">group</span>
                        Pelanggan
                    </a>
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
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">group</span>
                    <span class="text-xs font-bold text-slate-600 truncate">Data Pelanggan WiFi</span>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="route('unit.welcome', { slug: 'wifi' })" target="_blank" rel="noopener"
                          class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-full border border-blue-100">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span class="hidden sm:inline">Landing Page</span>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-6 space-y-4 flex-1">

                <!-- Page Title + Action Buttons -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Database Pelanggan WiFi</h1>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Menampilkan {{ pelanggan.total }} pelanggan terdaftar — Unit Internet BUMDes Damar Wulan
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <!-- Cetak PDF -->
                        <button @click="doPrintPDF" aria-label="Cetak PDF Laporan Pelanggan"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                            <span class="material-symbols-outlined text-base">print</span>
                            <span class="hidden sm:inline">Cetak PDF</span>
                            <span class="sm:hidden">PDF</span>
                        </button>
                        <!-- Export -->
                        <button @click="doExport" aria-label="Export ke Excel"
                                class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-sm transition">
                            <span class="material-symbols-outlined text-base">download</span>
                            Export Excel
                        </button>
                        <!-- Tambah -->
                        <button @click="openModal('add')" aria-label="Tambah pelanggan baru"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-lg transition">
                            <span class="material-symbols-outlined text-base font-bold">add</span>
                            Tambah Pelanggan
                        </button>
                    </div>
                </div>

                <!-- ── TOOLBAR: Search + Filter ─────────────────────────────── -->
                <div class="bg-white border border-slate-200 rounded-2xl p-3 flex flex-col sm:flex-row gap-2 sm:items-center">
                    <!-- Search -->
                    <div class="relative flex-1 max-w-sm">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                        <input v-model="search" type="search" id="search-pelanggan"
                               placeholder="Cari nama, ID, NIK, No WA, alamat..."
                               class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 bg-slate-50 rounded-xl focus:border-blue-500 focus:ring-0 focus:outline-none placeholder-slate-400" />
                    </div>

                    <!-- Filter Toggle -->
                    <button @click="isFilterOpen = !isFilterOpen" aria-label="Toggle filter"
                            :class="['inline-flex items-center gap-1.5 px-3 py-2 border rounded-xl text-xs font-semibold transition',
                                     isFilterOpen || activeFilterCount > 0
                                       ? 'bg-blue-600 border-blue-600 text-white'
                                       : 'bg-white border-slate-200 text-slate-600 hover:border-blue-400']">
                        <span class="material-symbols-outlined text-base">filter_list</span>
                        Filter
                        <span v-if="activeFilterCount > 0"
                              class="ml-1 bg-white text-blue-600 rounded-full text-[10px] font-black w-4 h-4 flex items-center justify-center leading-none">
                            {{ activeFilterCount }}
                        </span>
                    </button>

                    <!-- Per Page -->
                    <div class="flex items-center gap-2 ml-auto">
                        <span class="text-[11px] text-slate-500 font-medium whitespace-nowrap hidden sm:block">Tampilkan:</span>
                        <select :value="perPage" @change="changePerPage($event.target.value)"
                                class="text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-2 focus:border-blue-500 focus:outline-none">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- MASA PEMBAYARAN INFORMATIONAL BADGE -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <div class="px-4 py-2 rounded-xl text-xs font-black bg-blue-600 text-white shadow-sm flex items-center gap-2 shrink-0">
                        <span class="material-symbols-outlined text-base">calendar_month</span>
                        Masa Pembayaran Resmi Tagihan WiFi: Tanggal 1 s.d 10 (Tenggat Jatuh Tempo Tanggal 10)
                    </div>
                </div>

                <!-- Filter Panel -->
                <div v-if="isFilterOpen"
                     class="bg-white border border-slate-200 rounded-2xl p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    <!-- Paket -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Paket</label>
                        <select v-model="filterPaket" @change="applyFilters"
                                class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua</option>
                            <option v-for="p in paketOptions" :key="p" :value="p">{{ p }}</option>
                        </select>
                    </div>

                    <!-- Status Tagihan -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status Tagihan</label>
                        <select v-model="filterStatus" @change="applyFilters"
                                class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua Status</option>
                            <option value="AKTIF">AKTIF</option>
                            <option value="ISOLIR">ISOLIR</option>
                        </select>
                    </div>
                    <!-- RT -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">RT</label>
                        <select v-model="filterRt" @change="applyFilters"
                                class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua</option>
                            <option v-for="r in rtOptions" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <!-- RW -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">RW</label>
                        <select v-model="filterRw" @change="applyFilters"
                                class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none">
                            <option value="">Semua</option>
                            <option v-for="r in rwOptions" :key="r" :value="r">{{ r }}</option>
                        </select>
                    </div>
                    <!-- Tanggal Dari -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Dari</label>
                        <input v-model="filterTglDari" @change="applyFilters" type="date"
                               class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none" />
                    </div>
                    <!-- Tanggal Sampai -->
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sampai</label>
                        <input v-model="filterTglSampai" @change="applyFilters" type="date"
                               class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-2 py-1.5 focus:border-blue-500 focus:outline-none" />
                    </div>

                    <!-- Reset button (full-width row) -->
                    <div class="col-span-full flex justify-end mt-1">
                        <button @click="resetFilters" aria-label="Reset semua filter"
                                class="text-xs text-slate-500 hover:text-red-500 font-semibold flex items-center gap-1 transition">
                            <span class="material-symbols-outlined text-base">filter_list_off</span>
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- ── TABLE CONTAINER ────────────────────────────────────────── -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Empty State -->
                    <div v-if="pelanggan.data.length === 0" class="flex flex-col items-center justify-center py-16 px-4 text-center">
                        <span class="material-symbols-outlined text-slate-300 text-6xl mb-4">manage_search</span>
                        <h3 class="text-sm font-bold text-slate-600 mb-1">Belum ada data pelanggan</h3>
                        <p class="text-xs text-slate-400 max-w-xs">
                            Belum terdapat data pelanggan yang sesuai dengan pencarian atau filter yang diterapkan.
                        </p>
                        <button @click="openModal('add')"
                                class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base">add</span>
                            Tambah Pelanggan Pertama
                        </button>
                    </div>

                    <!-- Mobile: Card List (hidden md+) -->
                    <div v-else class="md:hidden divide-y divide-slate-100">
                        <div v-for="row in pelanggan.data" :key="row.id"
                             class="p-4 hover:bg-slate-50 transition">
                            <!-- Header row: Nama + Status -->
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-bold text-slate-900 text-xs">{{ row.nama }}</p>
                                        <span v-if="row.paket"
                                              class="px-2 py-0.5 bg-blue-950 text-blue-300 border border-blue-900 rounded text-[10px] font-bold font-mono shrink-0">
                                            {{ row.paket }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        ID: {{ row.no_id_pel || '-' }}
                                        <template v-if="row.provider"> &bull; <span class="font-semibold">{{ row.provider.nama_provider }}</span></template>
                                    </p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">
                                        {{ row.alamat || '-' }}<template v-if="row.rt || row.rw"> RT {{ row.rt || '-' }}/RW {{ row.rw || '-' }}</template>
                                    </p>
                                </div>
                                <span v-if="row.current_status === 'ISOLIR'"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-red-100 text-red-700 border border-red-300 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>ISOLIR
                                </span>
                                <span v-else
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700 border border-emerald-300 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>AKTIF
                                </span>
                            </div>

                            <!-- Stats row -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="text-[10px] text-slate-400">Total Tarikan</p>
                                        <p class="text-xs font-mono font-bold text-slate-800">{{ rupiah(row.total_tarikan) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-slate-400">BUMDes</p>
                                        <p class="text-xs font-mono font-bold text-emerald-600">{{ rupiah(row.hasil_bumdes) }}</p>
                                    </div>
                                </div>
                                <div v-if="row.no_wa">
                                    <a :href="`https://wa.me/${row.no_wa.replace(/\D/g,'')}`" target="_blank"
                                       class="text-blue-500 hover:underline text-[10px] flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">call</span>{{ row.no_wa }}
                                    </a>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button @click="openModal('quick_pay', row)" title="Bayar"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 text-white font-bold text-[11px] rounded-lg">
                                    <span class="material-symbols-outlined text-xs">payments</span>Bayar
                                </button>
                                <button @click="openHistoryModal(row)" title="Riwayat"
                                        class="p-1.5 rounded-lg bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200 transition">
                                    <span class="material-symbols-outlined text-sm">history</span>
                                </button>
                                <button @click="openModal('detail', row)" title="Detail"
                                        class="p-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 transition">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                                <button @click="openModal('edit', row)" title="Edit"
                                        class="p-1.5 rounded-lg bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100 transition">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click="openModal('delete', row)" title="Hapus"
                                        class="p-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                                <a v-if="row.gps_lat && row.gps_long" :href="gpsUrl(row.gps_lat, row.gps_long)" target="_blank"
                                   class="p-1.5 rounded-lg bg-slate-100 text-slate-500 border border-slate-200 hover:bg-blue-50 hover:text-blue-600 transition" title="Lihat di Maps">
                                    <span class="material-symbols-outlined text-sm">map</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Full Scroll Table (hidden on mobile) -->
                    <!-- Table Scroll Wrapper — Full Horizontal Scroll -->
                    <div v-if="pelanggan.data.length > 0" class="hidden md:block overflow-x-auto" style="overflow-x: auto;">

                        <table class="border-collapse text-xs" style="min-width: 2800px; width: max-content;">

                            <!-- ── THEAD ──────────────────────────────────────── -->
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">

                                    <!-- Sticky: No -->
                                    <th class="sticky left-0 z-20 bg-slate-50 border-r border-slate-200 px-3 py-3 text-center font-bold uppercase tracking-wider text-[10px] whitespace-nowrap" style="min-width:52px">
                                        <button @click="setSort('no')" aria-label="Sort No" class="inline-flex items-center gap-0.5 hover:text-blue-600 transition">
                                            No
                                            <span class="material-symbols-outlined text-xs">{{ sortField==='no' ? (sortDir==='asc'?'arrow_upward':'arrow_downward') : 'unfold_more' }}</span>
                                        </button>
                                    </th>

                                    <!-- Sticky: Nama -->
                                    <th class="sticky z-20 bg-slate-50 border-r border-slate-200 px-3 py-3 font-bold uppercase tracking-wider text-[10px] whitespace-nowrap" style="left:52px; min-width:180px">
                                        <button @click="setSort('nama')" aria-label="Sort Nama" class="inline-flex items-center gap-0.5 hover:text-blue-600 transition">
                                            Nama
                                            <span class="material-symbols-outlined text-xs">{{ sortField==='nama' ? (sortDir==='asc'?'arrow_upward':'arrow_downward') : 'unfold_more' }}</span>
                                        </button>
                                    </th>

                                    <!-- Regular columns -->
                                    <th v-for="col in [
                                            { key:'tanggal_daftar', label:'Tanggal Daftar', w:'120px' },
                                            { key:'provider',       label:'Provider / Mitra', w:'160px' },
                                            { key:'paket',          label:'Paket / Kecepatan', w:'140px' },
                                            { key:'nik',            label:'NIK',            w:'160px' },
                                            { key:'alamat',         label:'Alamat',         w:'200px' },
                                            { key:'rt',             label:'RT',             w:'60px'  },
                                            { key:'rw',             label:'RW',             w:'60px'  },
                                            { key:'no_id_pel',      label:'No ID Pel',      w:'120px' },
                                            { key:'no_wa',          label:'No WA',          w:'130px' },
                                            { key:'total_dasar_tarikan_non_ppn', label:'Total Dasar Tarikan Non PPN', w:'190px' },
                                            { key:'ppn_dan_pph',    label:'PPN dan PPH',    w:'130px' },
                                            { key:'ppn_pph',        label:'PPN/PPH',        w:'120px' },
                                            { key:'total_tarikan',  label:'Total Tarikan',  w:'140px' },
                                            { key:'bagi_hasil_bumdes', label:'Bagi Hasil BUMDes', w:'160px' },
                                            { key:'hasil_bumdes',   label:'Hasil BUMDes',   w:'140px' },
                                            { key:'nota_bayar_provider', label:'Nota Bayar Provider', w:'170px' },
                                            { key:'total_provider', label:'Total Provider', w:'140px' },
                                            { key:'status_tagihan', label:'Status Tagihan', w:'130px' },
                                            { key:'gps_long',       label:'GPS Long',       w:'110px' },
                                            { key:'gps_lat',        label:'GPS Lat',        w:'110px' },
                                        ]" :key="col.key"
                                       class="bg-slate-50 border-r border-slate-200 px-3 py-3 font-bold uppercase tracking-wider text-[10px] whitespace-nowrap"
                                       :style="{ minWidth: col.w }">
                                        <button v-if="['tanggal_daftar','paket','nik','no_id_pel','rt','rw','no_wa','total_dasar_tarikan_non_ppn','ppn_dan_pph','ppn_pph','total_tarikan','bagi_hasil_bumdes','hasil_bumdes','nota_bayar_provider','total_provider','gelombang'].includes(col.key)"
                                                @click="setSort(col.key)" :aria-label="`Sort ${col.label}`"
                                                class="inline-flex items-center gap-0.5 hover:text-blue-600 transition text-left">
                                            {{ col.label }}
                                            <span class="material-symbols-outlined text-xs">{{ sortField===col.key ? (sortDir==='asc'?'arrow_upward':'arrow_downward') : 'unfold_more' }}</span>
                                        </button>
                                        <span v-else>{{ col.label }}</span>
                                    </th>

                                    <!-- Foto Rumah -->
                                    <th class="bg-slate-50 border-r border-slate-200 px-3 py-3 font-bold uppercase tracking-wider text-[10px] whitespace-nowrap text-center" style="min-width:100px">
                                        Foto Rumah
                                    </th>

                                    <!-- Sticky Right: Aksi -->
                                    <th class="sticky right-0 z-20 bg-slate-100 border-l border-slate-200 px-3 py-3 font-bold uppercase tracking-wider text-[10px] whitespace-nowrap text-center" style="min-width:180px">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <!-- ── TBODY ──────────────────────────────────────── -->
                            <tbody>
                                <tr v-for="row in pelanggan.data" :key="row.id"
                                    class="border-b border-slate-100 hover:bg-blue-50/30 transition-colors">

                                    <!-- Sticky: No -->
                                    <td class="sticky left-0 z-10 bg-white border-r border-slate-200 px-3 py-2.5 text-center font-mono text-slate-600 whitespace-nowrap" style="min-width:52px">
                                        {{ row.no ?? '-' }}
                                    </td>

                                    <!-- Sticky: Nama -->
                                    <td class="sticky z-10 bg-white border-r border-slate-200 px-3 py-2.5 whitespace-nowrap" style="left:52px; min-width:180px">
                                        <p class="font-bold text-slate-900 truncate max-w-[160px]">{{ row.nama }}</p>
                                    </td>

                                    <!-- Tanggal Daftar -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap text-slate-600" style="min-width:120px">
                                        {{ formatDate(row.tanggal_daftar) }}
                                    </td>

                                    <!-- Provider -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap" style="min-width:160px">
                                        <span v-if="row.provider" class="px-2.5 py-1 bg-slate-100 text-slate-800 border border-slate-200 rounded-lg text-[10px] font-bold inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs text-blue-500">cell_tower</span>
                                            {{ row.provider.nama_provider }}
                                        </span>
                                        <span v-else class="text-slate-400 font-mono text-[11px]">-</span>
                                    </td>

                                    <!-- Paket / Kecepatan -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap" style="min-width:140px">
                                        <span v-if="row.paket" class="px-2.5 py-1 bg-blue-950 text-blue-300 border border-blue-900 rounded-lg text-[10px] font-bold font-mono">
                                            {{ row.paket }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>

                                    <!-- NIK -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap font-mono text-slate-600 text-[11px]" style="min-width:160px">
                                        {{ row.nik || '-' }}
                                    </td>

                                    <!-- Alamat -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-slate-600" style="min-width:200px; max-width:200px">
                                        <span class="block truncate" :title="row.alamat">{{ row.alamat || '-' }}</span>
                                    </td>

                                    <!-- RT -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-center text-slate-600 whitespace-nowrap" style="min-width:60px">
                                        {{ row.rt || '-' }}
                                    </td>

                                    <!-- RW -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-center text-slate-600 whitespace-nowrap" style="min-width:60px">
                                        {{ row.rw || '-' }}
                                    </td>

                                    <!-- No ID Pel -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap font-mono text-[11px] text-slate-700 font-semibold" style="min-width:120px">
                                        {{ row.no_id_pel || '-' }}
                                    </td>

                                    <!-- No WA -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap text-slate-600" style="min-width:130px">
                                        <a v-if="row.no_wa" :href="`https://wa.me/${row.no_wa.replace(/\D/g,'')}`" target="_blank"
                                           class="text-blue-600 hover:underline flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">call</span>
                                            {{ row.no_wa }}
                                        </a>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>

                                    <!-- Total Dasar Tarikan Non PPN -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-mono text-slate-700" style="min-width:190px">
                                        {{ rupiah(row.total_dasar_tarikan_non_ppn) }}
                                    </td>

                                    <!-- PPN dan PPH -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-mono text-slate-700" style="min-width:130px">
                                        {{ rupiah(row.ppn_dan_pph) }}
                                    </td>

                                    <!-- PPN/PPH -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-mono text-slate-700" style="min-width:120px">
                                        {{ rupiah(row.ppn_pph) }}
                                    </td>

                                    <!-- Total Tarikan -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-bold font-mono text-slate-800" style="min-width:140px">
                                        {{ rupiah(row.total_tarikan) }}
                                    </td>

                                    <!-- Bagi Hasil BUMDes -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-center whitespace-nowrap" style="min-width:160px">
                                        <span v-if="row.provider && row.provider.tipe_bagi_hasil === 'FLAT_ADMIN'"
                                              class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-[10px] font-black inline-flex items-center gap-1">
                                            <span class="text-[9px] text-emerald-600 font-normal uppercase">FLAT</span> {{ rupiah(row.provider.nilai_bagi_hasil) }}
                                        </span>
                                        <span v-else class="px-2.5 py-1 bg-blue-100 text-blue-800 border border-blue-200 rounded-lg text-[10px] font-black font-mono">
                                            {{ row.bagi_hasil_bumdes ?? 9 }}%
                                        </span>
                                    </td>

                                    <!-- Hasil BUMDes -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-mono text-emerald-600 font-semibold" style="min-width:140px">
                                        {{ rupiah(row.hasil_bumdes) }}
                                    </td>

                                    <!-- Nota Bayar Provider -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-mono text-slate-700" style="min-width:170px">
                                        {{ rupiah(row.nota_bayar_provider) }}
                                    </td>

                                    <!-- Total Provider -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-right whitespace-nowrap font-bold font-mono text-slate-800" style="min-width:140px">
                                        {{ rupiah(row.total_provider) }}
                                    </td>


                                    <td class="border-r border-slate-100 px-3 py-2.5 text-center whitespace-nowrap" style="min-width:120px">
                                        <span v-if="row.current_status === 'ISOLIR'"
                                              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-red-100 text-red-700 border border-red-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            ISOLIR
                                        </span>
                                        <span v-else
                                              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-700 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            AKTIF
                                        </span>
                                    </td>

                                    <!-- GPS Long -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap font-mono text-[11px] text-slate-600" style="min-width:110px">
                                        {{ row.gps_long ?? '-' }}
                                    </td>

                                    <!-- GPS Lat -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 whitespace-nowrap font-mono text-[11px] text-slate-600" style="min-width:110px">
                                        <span class="inline-flex items-center gap-1">
                                            {{ row.gps_lat ?? '-' }}
                                            <a v-if="row.gps_lat && row.gps_long" :href="gpsUrl(row.gps_lat, row.gps_long)"
                                               target="_blank" aria-label="Lihat lokasi di Google Maps"
                                               class="text-blue-500 hover:text-blue-700 transition" title="Lihat lokasi">
                                                <span class="material-symbols-outlined text-xs">map</span>
                                            </a>
                                        </span>
                                    </td>

                                    <!-- Foto Rumah -->
                                    <td class="border-r border-slate-100 px-3 py-2.5 text-center" style="min-width:100px">
                                        <button v-if="row.foto_rumah_url" @click="lightboxUrl = row.foto_rumah_url"
                                                aria-label="Lihat foto rumah" title="Lihat foto rumah" class="group inline-block">
                                            <img :src="row.foto_rumah_url" alt="Foto Rumah"
                                                 class="w-10 h-10 object-cover rounded-lg border border-slate-200 group-hover:ring-2 group-hover:ring-blue-500 transition" />
                                        </button>
                                        <span v-else class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 rounded-lg border border-slate-200 text-slate-400">
                                            <span class="material-symbols-outlined text-base">image</span>
                                        </span>
                                    </td>

                                    <!-- Sticky Right: Aksi -->
                                    <td class="sticky right-0 z-10 bg-white border-l border-slate-200 px-3 py-2.5 text-center whitespace-nowrap" style="min-width:180px">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="openModal('quick_pay', row)"
                                                    aria-label="Input Pembayaran Tagihan" title="Bayar Tagihan WiFi"
                                                    class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition">
                                                <span class="material-symbols-outlined text-base font-bold">payments</span>
                                            </button>
                                            <button @click="openHistoryModal(row)"
                                                    aria-label="Lihat riwayat transaksi" title="Riwayat Pembayaran"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                <span class="material-symbols-outlined text-base">history</span>
                                            </button>
                                            <button @click="openModal('detail', row)"
                                                    aria-label="Lihat detail pelanggan" title="Detail"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition">
                                                <span class="material-symbols-outlined text-base">visibility</span>
                                            </button>
                                            <button @click="openModal('edit', row)"
                                                    aria-label="Edit pelanggan" title="Edit"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition">
                                                <span class="material-symbols-outlined text-base">edit</span>
                                            </button>
                                            <button @click="openModal('delete', row)"
                                                    aria-label="Hapus pelanggan" title="Hapus"
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ── PAGINATION ─────────────────────────────────────────── -->
                    <div v-if="pelanggan.data.length > 0"
                         class="px-4 py-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50">
                        <!-- Info -->
                        <p class="text-[11px] text-slate-500">
                            Menampilkan
                            <span class="font-bold text-slate-700">{{ pelanggan.from }}–{{ pelanggan.to }}</span>
                            dari
                            <span class="font-bold text-slate-700">{{ pelanggan.total }}</span>
                            pelanggan
                        </p>

                        <!-- Page buttons -->
                        <div class="flex items-center gap-1">
                            <!-- First -->
                            <a v-if="pelanggan.current_page > 1"
                               :href="pelanggan.links[0].url" @click.prevent="router.get(pelanggan.links[0].url, {}, { preserveScroll: true })"
                               aria-label="Halaman pertama" title="Halaman pertama"
                               class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-400 transition">
                                <span class="material-symbols-outlined text-base">first_page</span>
                            </a>
                            <!-- Prev -->
                            <a v-if="pelanggan.prev_page_url"
                               :href="pelanggan.prev_page_url" @click.prevent="router.get(pelanggan.prev_page_url, {}, { preserveScroll: true })"
                               aria-label="Halaman sebelumnya" title="Sebelumnya"
                               class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-400 transition">
                                <span class="material-symbols-outlined text-base">chevron_left</span>
                            </a>

                            <!-- Page numbers -->
                            <template v-for="link in pelanggan.links.slice(1, -1)" :key="link.label">
                                <a v-if="link.url"
                                   :href="link.url" @click.prevent="router.get(link.url, {}, { preserveScroll: true })"
                                   :aria-label="`Halaman ${link.label}`"
                                   :class="['min-w-[32px] h-8 flex items-center justify-center rounded-lg border text-xs font-semibold transition',
                                            link.active
                                              ? 'bg-blue-600 border-blue-600 text-white'
                                              : 'border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-400']">
                                    {{ link.label }}
                                </a>
                                <span v-else-if="link.label === '...'"
                                      class="min-w-[32px] h-8 flex items-center justify-center text-slate-400 text-xs">
                                    ...
                                </span>
                            </template>

                            <!-- Next -->
                            <a v-if="pelanggan.next_page_url"
                               :href="pelanggan.next_page_url" @click.prevent="router.get(pelanggan.next_page_url, {}, { preserveScroll: true })"
                               aria-label="Halaman berikutnya" title="Berikutnya"
                               class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-400 transition">
                                <span class="material-symbols-outlined text-base">chevron_right</span>
                            </a>
                            <!-- Last -->
                            <a v-if="pelanggan.current_page < pelanggan.last_page"
                               :href="pelanggan.links[pelanggan.links.length - 1].url"
                               @click.prevent="router.get(pelanggan.links[pelanggan.links.length - 1].url, {}, { preserveScroll: true })"
                               aria-label="Halaman terakhir" title="Halaman terakhir"
                               class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-400 transition">
                                <span class="material-symbols-outlined text-base">last_page</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: TAMBAH / EDIT
    ══════════════════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
    <div v-if="modalMode === 'add' || modalMode === 'edit'"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         @click.self="closeModal">
        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl my-4">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    {{ modalMode === 'add' ? 'Tambah Pelanggan Baru' : 'Edit Data Pelanggan' }}
                </h2>
                <button @click="closeModal" aria-label="Tutup modal"
                        class="p-1 text-slate-400 hover:text-slate-700 rounded-lg transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-6">

                <!-- Alert Error Validation -->
                <div v-if="Object.keys(form.errors).length > 0" class="p-3 bg-red-50 border border-red-200 rounded-xl space-y-1">
                    <p class="text-xs font-bold text-red-800 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">warning</span>
                        Gagal Menyimpan Data. Mohon periksa kolom berikut:
                    </p>
                    <ul class="text-[11px] text-red-700 list-disc list-inside">
                        <li v-for="(err, field) in form.errors" :key="field">{{ err }}</li>
                    </ul>
                </div>

                <!-- ── IDENTITAS ───────────────────────────────────────── -->
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">badge</span>
                        Identitas
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">No</label>
                            <input v-model="form.no" type="number" placeholder="1" id="form-no"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama <span class="text-red-500">*</span></label>
                            <input v-model="form.nama" type="text" placeholder="Nama lengkap" id="form-nama" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                            <p v-if="form.errors.nama" class="text-red-500 text-[10px] mt-1">{{ form.errors.nama }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal Daftar</label>
                            <input v-model="form.tanggal_daftar" type="date" id="form-tanggal"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Paket / Kecepatan</label>
                            <input v-model="form.paket" type="text" placeholder="Misal: 10 Mbps, 20 Mbps" id="form-paket"
                                   list="paket-list"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono font-bold focus:border-blue-500 focus:outline-none" />
                            <datalist id="paket-list">
                                <option value="10 Mbps" />
                                <option value="15 Mbps" />
                                <option value="20 Mbps" />
                                <option value="30 Mbps" />
                                <option value="50 Mbps" />
                                <option value="100 Mbps" />
                                <option v-for="p in paketOptions" :key="p" :value="p" />
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">NIK</label>
                            <input v-model="form.nik" type="text" maxlength="16" placeholder="16 digit NIK" id="form-nik"
                                   @input="form.nik = form.nik.replace(/\D/g, '').slice(0, 16)"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">No ID Pelanggan</label>
                            <input v-model="form.no_id_pel" type="text" placeholder="ID unik pelanggan" id="form-no-id"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                            <p v-if="form.errors.no_id_pel" class="text-red-500 text-[10px] mt-1">{{ form.errors.no_id_pel }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Provider / Mitra ISP</label>
                            <select v-model="form.provider_wifi_id" id="form-provider-id" @change="onProviderChange"
                                    class="w-full rounded-xl border border-blue-200 bg-blue-50/50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-blue-500 focus:outline-none">
                                <option value="">— Umum / Tanpa Provider —</option>
                                <option v-for="prov in providersList" :key="prov.id" :value="prov.id">
                                    {{ prov.nama_provider }} ({{ prov.tipe_bagi_hasil === 'PERSENTASE' ? prov.nilai_bagi_hasil + '%' : 'Flat ' + rupiah(prov.nilai_bagi_hasil) }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">No WhatsApp</label>
                            <input v-model="form.no_wa" type="text" placeholder="08..." id="form-no-wa"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                    </div>
                </div>

                <!-- ── ALAMAT ──────────────────────────────────────────── -->
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">home</span>
                        Alamat
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="col-span-2 sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Alamat</label>
                            <textarea v-model="form.alamat" rows="2" placeholder="Alamat lengkap" id="form-alamat"
                                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">RT</label>
                            <input v-model="form.rt" type="text" placeholder="01" id="form-rt"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">RW</label>
                            <input v-model="form.rw" type="text" placeholder="01" id="form-rw"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                        </div>
                    </div>
                </div>

                <!-- ── KEUANGAN ────────────────────────────────────────── -->
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">payments</span>
                        Keuangan &amp; Bagi Hasil BUMDes
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 border border-slate-200 p-3.5 rounded-2xl">
                        <!-- Total Tarikan / Harga -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                                Total Tarikan (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.total_tarikan" type="number" step="1000" min="0" placeholder="165000" id="form-total-tarikan"
                                   class="w-full rounded-xl border border-blue-300 bg-white px-3 py-2 text-xs font-black text-blue-900 focus:border-blue-500 focus:outline-none text-right" />
                        </div>

                        <!-- Persentase BUMDes Flexible ATAU Badge Flat Admin -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                                {{ selectedProviderObj?.tipe_bagi_hasil === 'FLAT_ADMIN' ? 'Skema Bagi Hasil' : 'Persen BUMDes (%)' }}
                            </label>

                            <div v-if="selectedProviderObj?.tipe_bagi_hasil === 'FLAT_ADMIN'"
                                 class="w-full rounded-xl border border-emerald-200 bg-emerald-50/60 px-3 py-2 text-xs font-black text-emerald-800 flex items-center justify-between h-[38px]">
                                <span class="text-[10px] font-extrabold uppercase text-emerald-600">FLAT ADMIN</span>
                                <span class="font-mono text-emerald-900">{{ rupiah(selectedProviderObj.nilai_bagi_hasil) }}</span>
                            </div>

                            <div v-else class="relative">
                                <input v-model="persentaseBumdes" type="number" step="0.1" min="0" max="100" placeholder="9" id="form-persen-bumdes"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:border-blue-500 focus:outline-none text-right pr-6" />
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">%</span>
                            </div>
                        </div>

                        <!-- Hasil BUMDes (Otomatis) -->
                        <div>
                            <label class="block text-[10px] font-bold text-emerald-700 uppercase mb-1">
                                Hasil BUMDes (Otomatis)
                            </label>
                            <input v-model="form.hasil_bumdes" type="number" step="1" min="0" placeholder="Otomatis" id="form-hasil-bumdes"
                                   class="w-full rounded-xl border border-emerald-300 bg-emerald-50/50 px-3 py-2 text-xs font-black text-emerald-800 focus:border-emerald-500 focus:outline-none text-right" />
                        </div>

                        <!-- Total Provider (Otomatis) -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">
                                Total Provider (Sisa)
                            </label>
                            <input v-model="form.total_provider" type="number" step="1" min="0" placeholder="Otomatis" id="form-total-provider"
                                   class="w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 focus:border-slate-400 focus:outline-none text-right" />
                        </div>
                    </div>
                </div>

                <!-- ── JADWAL GELOMBANG ─────────────────────────────── -->
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        Jadwal Tagihan
                    </h3>
                    <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0">
                                <span class="material-symbols-outlined text-sm">event_available</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">Masa Pembayaran (Tgl 1 – 10)</h4>
                                <p class="text-[10px] text-slate-500 mt-0.5">Tenggat pembayaran tgl 10. Mulai tgl 11+ otomatis ISOLIR jika belum bayar.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-lg border border-indigo-200 shrink-0">Otomatis</span>
                    </div>
                </div>

                <!-- ── LOKASI & FOTO ───────────────────────────────────── -->
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">location_on</span>
                        Lokasi &amp; Dokumentasi
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">GPS Latitude (Lintang)</label>
                            <input v-model="form.gps_lat" type="number" step="any" placeholder="-7.331245" id="form-gps-lat"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none font-mono" />
                            <span v-if="form.errors.gps_lat" class="text-[10px] font-bold text-red-500 mt-1 block">{{ form.errors.gps_lat }}</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">GPS Longitude (Bujur)</label>
                            <input v-model="form.gps_long" type="number" step="any" placeholder="109.230521" id="form-gps-long"
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none font-mono" />
                            <span v-if="form.errors.gps_long" class="text-[10px] font-bold text-red-500 mt-1 block">{{ form.errors.gps_long }}</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Foto Rumah</label>
                            <div class="flex items-center gap-2">
                                <div v-if="fotoPreview || (modalMode === 'edit' && selectedRow?.foto_rumah_url)"
                                     class="w-10 h-10 shrink-0">
                                    <img :src="fotoPreview || selectedRow?.foto_rumah_url"
                                         class="w-10 h-10 object-cover rounded-lg border border-slate-200" alt="Preview foto" />
                                </div>
                                <label class="flex-1 flex items-center gap-1.5 cursor-pointer px-3 py-2 rounded-xl border border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 text-xs text-slate-500 hover:text-blue-600 transition">
                                    <span class="material-symbols-outlined text-base">image</span>
                                    <span>{{ form.foto_rumah ? form.foto_rumah.name : 'Pilih foto' }}</span>
                                    <input ref="fotoInput" type="file" accept="image/*" @change="onFotoChange" class="sr-only" id="form-foto-rumah" />
                                </label>
                            </div>
                            <span v-if="form.errors.foto_rumah" class="text-[10px] font-bold text-red-500 mt-1 block">{{ form.errors.foto_rumah }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="closeModal"
                            class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow transition disabled:opacity-60">
                        {{ form.processing ? 'Menyimpan...' : (modalMode === 'add' ? 'Simpan Pelanggan' : 'Perbarui Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: DETAIL
    ══════════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'detail' && selectedRow"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         @click.self="closeModal">
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl my-4">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Detail Pelanggan</h2>
                <button @click="closeModal" aria-label="Tutup modal" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <!-- Foto -->
                <div class="flex items-center gap-4">
                    <div v-if="selectedRow.foto_rumah_url" @click="lightboxUrl = selectedRow.foto_rumah_url" class="cursor-zoom-in">
                        <img :src="selectedRow.foto_rumah_url" alt="Foto Rumah"
                             class="w-24 h-24 object-cover rounded-xl border border-slate-200 shadow" />
                    </div>
                    <div v-else class="w-24 h-24 bg-slate-100 rounded-xl border border-slate-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-slate-300 text-4xl">image</span>
                    </div>
                    <div>
                        <p class="text-lg font-extrabold text-slate-900">{{ selectedRow.nama }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">No ID: <span class="font-mono font-semibold text-slate-700">{{ selectedRow.no_id_pel || '-' }}</span></p>
                        <p class="text-xs text-slate-500">Paket: <span class="font-semibold text-blue-600">{{ selectedRow.paket || '-' }}</span></p>
                        <p class="text-xs text-slate-500">Tgl Daftar: {{ formatDate(selectedRow.tanggal_daftar) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-xs">
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">NIK</span><span class="font-mono text-slate-700">{{ selectedRow.nik || '-' }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">No WA</span><span class="text-slate-700">{{ selectedRow.no_wa || '-' }}</span></div>
                    <div class="col-span-2"><span class="font-bold text-slate-500 block text-[10px] uppercase">Alamat</span><span class="text-slate-700">{{ selectedRow.alamat || '-' }} RT {{ selectedRow.rt || '-' }} / RW {{ selectedRow.rw || '-' }}</span></div>

                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Total Dasar Non PPN</span><span class="font-mono text-slate-700">{{ rupiah(selectedRow.total_dasar_tarikan_non_ppn) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">PPN dan PPH</span><span class="font-mono text-slate-700">{{ rupiah(selectedRow.ppn_dan_pph) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">PPN/PPH</span><span class="font-mono text-slate-700">{{ rupiah(selectedRow.ppn_pph) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Total Tarikan</span><span class="font-mono font-bold text-slate-900">{{ rupiah(selectedRow.total_tarikan) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Bagi Hasil BUMDes</span><span class="font-mono text-slate-700">{{ rupiah(selectedRow.bagi_hasil_bumdes) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Hasil BUMDes</span><span class="font-mono font-semibold text-emerald-600">{{ rupiah(selectedRow.hasil_bumdes) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Nota Bayar Provider</span><span class="font-mono text-slate-700">{{ rupiah(selectedRow.nota_bayar_provider) }}</span></div>
                    <div><span class="font-bold text-slate-500 block text-[10px] uppercase">Total Provider</span><span class="font-mono font-bold text-slate-900">{{ rupiah(selectedRow.total_provider) }}</span></div>

                    <div>
                        <span class="font-bold text-slate-500 block text-[10px] uppercase">Masa Pembayaran</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border uppercase mt-0.5 bg-blue-50 text-blue-700 border-blue-200">
                            <span class="material-symbols-outlined text-xs">schedule</span>
                            Tanggal 1 - 10
                        </span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-500 block text-[10px] uppercase">Status Tagihan Terkini</span>
                        <span :class="selectedRow.current_status === 'ISOLIR' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-emerald-100 text-emerald-700 border-emerald-300'"
                              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="selectedRow.current_status === 'ISOLIR' ? 'bg-red-600' : 'bg-emerald-600'"></span>
                            {{ selectedRow.current_status === 'ISOLIR' ? 'ISOLIR' : 'AKTIF' }}
                        </span>
                    </div>

                    <div>
                        <span class="font-bold text-slate-500 block text-[10px] uppercase">GPS</span>
                        <span class="font-mono text-slate-700 text-[11px]">{{ selectedRow.gps_lat ?? '-' }}, {{ selectedRow.gps_long ?? '-' }}</span>
                        <a v-if="selectedRow.gps_lat && selectedRow.gps_long"
                           :href="gpsUrl(selectedRow.gps_lat, selectedRow.gps_long)" target="_blank"
                           class="ml-2 text-blue-600 hover:underline text-[10px] font-semibold inline-flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-xs">map</span>
                            Lihat Lokasi
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 pb-5">
                <button @click="openModal('edit', selectedRow)"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs rounded-xl shadow transition">
                    <span class="material-symbols-outlined text-base">edit</span>
                    Edit Data
                </button>
                <button @click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: DELETE CONFIRM
    ══════════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'delete' && selectedRow"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-600">delete_forever</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Hapus Pelanggan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <p class="text-xs text-slate-600">
                Anda akan menghapus data pelanggan:
                <span class="font-bold text-slate-900">{{ selectedRow.nama }}</span>
                (No ID: <span class="font-mono">{{ selectedRow.no_id_pel || '-' }}</span>)
            </p>
            <div class="flex justify-end gap-3 pt-2">
                <button @click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Batal
                </button>
                <button @click="confirmDelete"
                        class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow transition">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: IMPORT
    ══════════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'import'"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         @click.self="closeModal">
        <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl my-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Import Data dari CSV</h2>
                <button @click="closeModal" aria-label="Tutup modal" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Panduan Import
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 ml-5 text-blue-600">
                        <li>Gunakan template CSV dari tombol <strong>Export</strong> sebagai panduan kolom.</li>
                        <li>Kolom wajib: <strong>Nama</strong></li>
                        <li>Status harus: AKTIF atau ISOLIR</li>
                        <li>No ID Pelanggan harus unik (tidak boleh duplikat)</li>
                    </ul>
                </div>

                <!-- File Input -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">File CSV</label>
                    <label class="flex flex-col items-center justify-center gap-2 w-full h-28 border-2 border-dashed border-slate-300 hover:border-blue-400 bg-slate-50 rounded-xl cursor-pointer transition">
                        <span class="material-symbols-outlined text-slate-400 text-3xl">upload_file</span>
                        <span class="text-xs text-slate-500">
                            {{ importState.file ? importState.file.name : 'Klik atau seret file CSV ke sini' }}
                        </span>
                        <input type="file" accept=".csv,text/csv" @change="onImportFileChange" class="sr-only" id="import-file-input" />
                    </label>
                </div>

                <!-- Errors -->
                <div v-if="importState.errors.length > 0"
                     class="bg-red-50 border border-red-200 rounded-xl p-3 space-y-1 max-h-36 overflow-y-auto">
                    <p class="text-xs font-bold text-red-700 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">error_outline</span>
                        {{ importState.errors.length }} baris bermasalah
                    </p>
                    <ul class="space-y-0.5">
                        <li v-for="err in importState.errors" :key="err.line" class="text-[11px] text-red-600">
                            <span class="font-mono font-bold">Baris {{ err.line }}</span>
                            ({{ err.nama || 'kosong' }}):
                            {{ err.errors.join(', ') }}
                        </li>
                    </ul>
                </div>

                <!-- Preview -->
                <div v-if="importState.preview && importState.errors.length === 0"
                     class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-700">
                    <p class="font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Validasi berhasil — {{ importState.total }} baris siap diimpor
                    </p>
                    <p class="mt-1 text-emerald-600">Preview 10 baris pertama:</p>
                    <ul class="list-disc list-inside mt-1 ml-3 space-y-0.5 text-emerald-600">
                        <li v-for="row in importState.preview" :key="row.line">
                            {{ row.data.nama }} ({{ row.data.no_id_pel || 'tanpa ID' }})
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 pb-5">
                <button @click="closeModal"
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                    Batal
                </button>
                <button v-if="!importState.preview" @click="previewImport" :disabled="!importState.file || importState.loading"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white font-bold text-xs rounded-xl shadow transition disabled:opacity-60">
                    <span class="material-symbols-outlined text-base">preview</span>
                    {{ importState.loading ? 'Memvalidasi...' : 'Validasi File' }}
                </button>
                <button v-if="importState.preview && importState.errors.length === 0"
                        @click="confirmImport" :disabled="importState.loading"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition disabled:opacity-60">
                    <span class="material-symbols-outlined text-base">upload</span>
                    {{ importState.loading ? 'Mengimpor...' : `Import ${importState.total} Data` }}
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: QUICK PAY PEMBAYARAN -->
    <div v-if="modalMode === 'quick_pay' && selectedRow"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase">Input Pembayaran WiFi</h3>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitQuickPay" class="space-y-3">
                <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl">
                    <p class="text-xs font-bold text-slate-900">{{ selectedRow.nama }}</p>
                    <p class="text-[11px] text-slate-500">ID: {{ selectedRow.no_id_pel || '-' }} &bull; Paket: {{ selectedRow.paket || '-' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Bulan</label>
                        <select v-model="quickPayForm.periode_bulan" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option v-for="b in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="b" :value="b">
                                {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][b-1] }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tahun</label>
                        <select v-model="quickPayForm.periode_tahun" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option v-for="y in [2025, 2026, 2027, 2028]" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Masa Pembayaran</label>
                        <div class="w-full text-xs border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 font-bold text-slate-700">
                            Tanggal 1 - 10
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
                        <select v-model="quickPayForm.status" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option value="AKTIF">AKTIF (LUNAS)</option>
                            <option value="ISOLIR">ISOLIR</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal Bayar</label>
                        <input v-model="quickPayForm.tanggal_bayar" type="date" required
                               class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Metode</label>
                        <select v-model="quickPayForm.metode_pembayaran" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 font-bold">
                            <option value="TUNAI">TUNAI</option>
                            <option value="TRANSFER">TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jumlah Bayar (Rp)</label>
                    <input v-model="quickPayForm.jumlah_bayar" type="number" step="1000" min="0" required
                           class="w-full text-sm font-bold text-slate-900 border border-slate-200 rounded-xl px-3 py-2" />
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Batal</button>
                    <button type="submit" :disabled="quickPayForm.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow transition">
                        {{ quickPayForm.processing ? 'Simpan...' : 'Simpan Pembayaran' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: RIWAYAT PEMBAYARAN PELANGGAN
    ══════════════════════════════════════════════════════════════════════════ -->
    <div v-if="modalMode === 'history' && historyCustomer"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl p-6 space-y-4 max-h-[92vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">history</span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-wide">Riwayat Transaksi Pembayaran</h3>
                        <p class="text-xs text-slate-500">ID Pelanggan: <strong class="text-slate-800 font-mono font-bold">{{ historyCustomer.no_id_pel || '-' }}</strong></p>
                    </div>
                </div>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Customer & Financial Summary -->
            <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl shrink-0 space-y-3">
                <!-- Customer Bio Row -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs pb-3 border-b border-slate-200/80">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Nama Pelanggan</span>
                        <strong class="text-slate-900 text-sm font-extrabold">{{ historyCustomer.nama }}</strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">NIK: {{ historyCustomer.nik || '-' }} &bull; WA: {{ historyCustomer.no_wa || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Paket Speed</span>
                        <span class="px-2.5 py-1 bg-blue-950 text-blue-300 font-mono font-extrabold text-xs rounded-lg inline-block">
                            {{ historyCustomer.paket || '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Provider / ISP</span>
                        <span class="font-extrabold text-slate-800 text-xs">
                            {{ historyCustomer.provider ? historyCustomer.provider.nama_provider : 'Umum' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Alamat Rumah</span>
                        <span class="text-slate-700 font-semibold">{{ historyCustomer.alamat || '-' }} (RT {{ historyCustomer.rt }}/RW {{ historyCustomer.rw }})</span>
                    </div>
                </div>

                <!-- Financial Summary Boxes -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-2xs">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block mb-0.5">Tarikan Bulanan Warga</span>
                        <span class="font-mono text-base font-black text-slate-900">{{ rupiah(historyCustomer.total_tarikan) }}</span>
                    </div>
                    <div class="bg-emerald-50/80 border border-emerald-200 rounded-xl p-3 shadow-2xs">
                        <span class="text-[10px] font-bold uppercase text-emerald-700 block mb-0.5">Hasil Bersih BUMDes</span>
                        <span class="font-mono text-base font-black text-emerald-700">{{ rupiah(historyCustomer.hasil_bumdes) }}</span>
                    </div>
                    <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-3 shadow-2xs">
                        <span class="text-[10px] font-bold uppercase text-blue-700 block mb-0.5">Hak Provider ISP</span>
                        <span class="font-mono text-base font-black text-blue-700">{{ rupiah(historyCustomer.total_provider) }}</span>
                    </div>
                </div>
            </div>

            <!-- History Table Content -->
            <div class="flex-1 overflow-x-auto overflow-y-auto border border-slate-200 rounded-2xl bg-white">
                <div v-if="historyLoading" class="p-12 text-center text-slate-500 text-xs font-semibold flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined animate-spin text-xl text-indigo-600">sync</span>
                    Memuat riwayat transaksi...
                </div>

                <table v-else class="w-full text-xs text-left text-slate-700 border-collapse min-w-[850px]">
                    <thead class="bg-slate-100/90 text-[10px] font-bold uppercase text-slate-600 sticky top-0 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 text-center w-10">No</th>
                            <th class="py-3 px-3 whitespace-nowrap">No. Struk</th>
                            <th class="py-3 px-3 whitespace-nowrap">Periode Tagihan</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Tgl Bayar</th>
                            <th class="py-3 px-3 text-right whitespace-nowrap">Tarikan Warga</th>
                            <th class="py-3 px-3 text-right whitespace-nowrap">Hasil BUMDes</th>
                            <th class="py-3 px-3 text-right whitespace-nowrap">Hak Provider</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Metode</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Status</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Kasir</th>
                            <th class="py-3 px-3 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(h, idx) in customerHistory" :key="h.id" class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ h.no_transaksi }}</td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="font-bold text-slate-900">Bulan {{ getBulanName(h.periode_bulan) }} {{ h.periode_tahun }}</div>
                                <span class="inline-block mt-0.5 text-[9px] font-bold px-1.5 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 rounded">
                                    Tgl 1 - 10
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-600 whitespace-nowrap">{{ formatDate(h.tanggal_bayar) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">{{ rupiah(h.jumlah_bayar) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-extrabold text-emerald-700 whitespace-nowrap">{{ rupiah(calcBumdes(h)) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-extrabold text-blue-700 whitespace-nowrap">{{ rupiah(calcProvider(h)) }}</td>
                            <td class="py-3 px-3 text-center font-bold text-slate-700 whitespace-nowrap">{{ h.metode_pembayaran }}</td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span v-if="h.status === 'ISOLIR'" class="px-2 py-0.5 bg-red-100 text-red-800 border border-red-300 rounded-md font-bold text-[10px]">
                                    ISOLIR
                                </span>
                                <span v-else class="px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-md font-bold text-[10px]">
                                    AKTIF
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center text-slate-600 whitespace-nowrap">{{ h.kasir ? h.kasir.nama : 'Admin' }}</td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <a :href="route('wifi.pembayaran.struk', h.id)" target="_blank"
                                   title="Cetak Struk Thermal"
                                   class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-[10px] transition shadow-sm">
                                    <span class="material-symbols-outlined text-xs">print</span>
                                    Struk
                                </a>
                            </td>
                        </tr>
                        <tr v-if="customerHistory.length === 0">
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                Belum ada riwayat transaksi pembayaran untuk pelanggan ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-2 shrink-0 border-t border-slate-100">
                <button type="button" @click="closeModal" class="px-5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition">
                    Tutup Modal
                </button>
            </div>
        </div>
    </div>
    </Teleport>
</template>

<style>
@media print {
    aside, header, button, select, .no-print, input[type="search"] {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    main {
        height: auto !important;
        overflow: visible !important;
    }
    .overflow-x-auto {
        overflow: visible !important;
    }
    table {
        font-size: 9px !important;
    }
}
</style>
