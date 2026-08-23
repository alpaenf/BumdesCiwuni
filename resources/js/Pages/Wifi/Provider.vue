<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    unit:      { type: Object, required: true },
    user:      { type: Object, required: true },
    providers: { type: Array,  default: () => [] },
    filters:   { type: Object, default: () => ({}) },
});

const isSidebarOpen = ref(false);
const logout = () => router.post(route('logout'));

const search = ref(props.filters.search ?? '');
const modalMode = ref(''); // 'add' | 'edit' | 'delete'
const selectedProvider = ref(null);

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('wifi.provider.index'), { search: search.value || undefined }, { preserveScroll: true, replace: true });
    }, 350);
});

const form = useForm({
    nama_provider:    '',
    header_wa:        '',
    bank_accounts:    [],
    tipe_bagi_hasil:  'PERSENTASE',
    nilai_bagi_hasil: 9,
    penanggung_jawab: '',
    no_telepon:       '',
    keterangan:       '',
});

const addBankAccount = () => {
    form.bank_accounts.push({ bank: '', no_rek: '', atas_nama: '' });
};

const removeBankAccount = (index) => {
    form.bank_accounts.splice(index, 1);
};

const openModal = (mode, item = null) => {
    modalMode.value = mode;
    selectedProvider.value = item ? { ...item } : null;

    if (mode === 'edit' && item) {
        form.reset();
        form.nama_provider    = item.nama_provider;
        form.header_wa        = item.header_wa ?? '';
        form.bank_accounts    = item.bank_accounts && Array.isArray(item.bank_accounts) ? JSON.parse(JSON.stringify(item.bank_accounts)) : [];
        form.tipe_bagi_hasil  = item.tipe_bagi_hasil;
        form.nilai_bagi_hasil = item.nilai_bagi_hasil;
        form.penanggung_jawab = item.penanggung_jawab ?? '';
        form.no_telepon       = item.no_telepon ?? '';
        form.keterangan       = item.keterangan ?? '';
    } else if (mode === 'add') {
        form.reset();
        form.nama_provider    = '';
        form.header_wa        = 'PT. MEDIA CEPAT INDONESIA';
        form.bank_accounts    = [
            { bank: 'BRI', no_rek: '3117-01-022918-53-6', atas_nama: 'Rasmini' },
            { bank: 'Mandiri', no_rek: '180-00-1106813-9', atas_nama: 'Rasmini' },
            { bank: 'BCA', no_rek: '4220318198', atas_nama: 'Rasmini' }
        ];
        form.tipe_bagi_hasil  = 'PERSENTASE';
        form.nilai_bagi_hasil = 9;
    }
};

const closeModal = () => {
    modalMode.value = '';
    selectedProvider.value = null;
};

const submitForm = () => {
    if (modalMode.value === 'edit' && selectedProvider.value) {
        form.put(route('wifi.provider.update', selectedProvider.value.id), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('wifi.provider.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const confirmDelete = () => {
    if (selectedProvider.value) {
        router.delete(route('wifi.provider.destroy', selectedProvider.value.id), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const rupiah = (val) => {
    if (!val && val !== 0) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <Head title="Master Provider WiFi" />

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
                    <a href="#"
                       class="flex items-center gap-3 px-4 py-3 bg-blue-600/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-600/20">
                        <span class="material-symbols-outlined text-lg">cell_tower</span>
                        Master Provider
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
                    <span class="material-symbols-outlined text-blue-400 text-lg hidden sm:block">cell_tower</span>
                    <span class="text-xs font-bold text-slate-600">Master Data Provider &amp; Mitra ISP</span>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-6 space-y-6 flex-1 max-w-6xl mx-auto w-full">

                <!-- Header Banner -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Mitra Provider WiFi</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola skema bagi hasil (Persentase % atau Biaya Admin Flat Rp) per Provider ISP</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Search -->
                        <div class="relative min-w-[200px]">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                            <input v-model="search" type="search" placeholder="Cari provider..."
                                   class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 bg-white rounded-xl focus:border-blue-500 focus:outline-none" />
                        </div>

                        <button @click="openModal('add')"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base">add</span>
                            Tambah Provider
                        </button>
                    </div>
                </div>

                <!-- Provider Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="prov in providers" :key="prov.id"
                         class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between hover:border-blue-300 transition">
                        
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-xl">router</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight">{{ prov.nama_provider }}</h3>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            {{ prov.pelanggan_count ?? 0 }} pelanggan terhubung
                                        </p>
                                    </div>
                                </div>
                                <span :class="prov.tipe_bagi_hasil === 'PERSENTASE' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200'"
                                      class="px-2 py-0.5 rounded-full text-[10px] font-black border uppercase shrink-0">
                                    {{ prov.tipe_bagi_hasil === 'PERSENTASE' ? 'PERSENTASE %' : 'ADMIN FLAT' }}
                                </span>
                            </div>

                            <!-- Skema Box -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Bagi Hasil BUMDes</span>
                                <p class="text-sm font-black text-slate-900">
                                    <template v-if="prov.tipe_bagi_hasil === 'PERSENTASE'">
                                        <span class="text-blue-600">{{ prov.nilai_bagi_hasil }}%</span> BUMDes &bull; {{ 100 - prov.nilai_bagi_hasil }}% Provider
                                    </template>
                                    <template v-else>
                                        <span class="text-emerald-600">{{ rupiah(prov.nilai_bagi_hasil) }}</span> / pelanggan
                                    </template>
                                </p>
                            </div>

                            <!-- Header WA & Rekening Bank Info -->
                            <div v-if="prov.header_wa || (prov.bank_accounts && prov.bank_accounts.length)" class="space-y-1.5 pt-1 border-t border-slate-100 text-xs">
                                <p v-if="prov.header_wa" class="font-bold text-slate-800 flex items-center gap-1 text-[11px]">
                                    <span class="material-symbols-outlined text-blue-600 text-sm">domain</span>
                                    {{ prov.header_wa }}
                                </p>
                                <div v-if="prov.bank_accounts && prov.bank_accounts.length" class="bg-blue-50/50 border border-blue-100 rounded-lg p-2 space-y-1">
                                    <span class="text-[9px] font-bold uppercase text-blue-700 tracking-wider block">Rekening Transfer ({{ prov.bank_accounts.length }} Bank)</span>
                                    <div v-for="(acc, i) in prov.bank_accounts" :key="i" class="text-[11px] font-mono text-slate-700 flex justify-between">
                                        <span class="font-bold text-slate-900">{{ acc.bank }}:</span>
                                        <span>{{ acc.no_rek }} <span class="text-slate-500 text-[10px]">a/n {{ acc.atas_nama }}</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact info -->
                            <div class="space-y-1 text-xs text-slate-600">
                                <p v-if="prov.penanggung_jawab" class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-slate-400 text-sm">person</span>
                                    {{ prov.penanggung_jawab }}
                                </p>
                                <p v-if="prov.no_telepon" class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-slate-400 text-sm">call</span>
                                    {{ prov.no_telepon }}
                                </p>
                                <p v-if="prov.keterangan" class="text-[11px] text-slate-400 italic">
                                    "{{ prov.keterangan }}"
                                </p>
                            </div>
                        </div>

                        <!-- Card Action buttons -->
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button @click="openModal('edit', prov)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Edit Provider
                            </button>
                            <button @click="openModal('delete', prov)"
                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="providers.length === 0" class="col-span-full bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-400 space-y-3">
                        <span class="material-symbols-outlined text-5xl text-slate-300">cell_tower</span>
                        <p class="text-xs font-bold text-slate-600">Belum ada data Provider ISP</p>
                        <p class="text-xs text-slate-400">Tambahkan provider mitra pertama Anda untuk mengelompokkan skema bagi hasil dan laporan.</p>
                        <button @click="openModal('add')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base">add</span>
                            Tambah Provider
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL: TAMBAH / EDIT PROVIDER
    ══════════════════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
    <div v-if="modalMode === 'add' || modalMode === 'edit'"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase">
                    {{ modalMode === 'add' ? 'Tambah Provider ISP Baru' : 'Edit Skema & Data Provider' }}
                </h3>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Provider / Mitra ISP <span class="text-red-500">*</span></label>
                        <input v-model="form.nama_provider" type="text" placeholder="Misal: PT Fiber Desa, Indihome..." required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-900 focus:border-blue-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tipe Bagi Hasil</label>
                        <select v-model="form.tipe_bagi_hasil"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-blue-500 focus:outline-none">
                            <option value="PERSENTASE">PERSENTASE (%)</option>
                            <option value="FLAT_ADMIN">BIAYA ADMIN (Rp)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">
                            {{ form.tipe_bagi_hasil === 'PERSENTASE' ? 'Persen BUMDes (%)' : 'Admin BUMDes (Rp)' }}
                        </label>
                        <input v-model="form.nilai_bagi_hasil" type="number" step="any" min="0" required
                               class="w-full rounded-xl border border-blue-300 bg-white px-3 py-2 text-xs font-black text-blue-900 focus:border-blue-500 focus:outline-none text-right" />
                    </div>
                </div>

                <!-- Judul Header WA & Rekening Bank Payment Transfer -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base text-blue-600">account_balance</span>
                            Daftar Rekening Bank &amp; Header WA Tagihan
                        </h4>
                        <button type="button" @click="addBankAccount" class="px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-[11px] rounded-lg border border-blue-200 flex items-center gap-1 transition">
                            <span class="material-symbols-outlined text-xs">add</span>
                            Tambah Rekening Bank
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Perusahaan / Judul Header WA Tagihan</label>
                        <input v-model="form.header_wa" type="text" placeholder="PT. MEDIA CEPAT INDONESIA" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-900 focus:border-blue-500 focus:outline-none" />
                        <p class="text-[10px] text-slate-400 mt-1">Nama perusahaan ini akan tampil pada header nota &amp; pesan WhatsApp pengingat tagihan warga.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Daftar Rekening Pembayaran Transfer</label>
                        <div v-for="(acc, index) in form.bank_accounts" :key="index" class="p-2.5 bg-white border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                            <div class="sm:col-span-3">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Nama Bank</label>
                                <input v-model="acc.bank" type="text" placeholder="BRI / BCA / Mandiri" class="w-full rounded-lg border-slate-200 text-xs font-bold px-2.5 py-1.5" />
                            </div>
                            <div class="sm:col-span-5">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Nomor Rekening</label>
                                <input v-model="acc.no_rek" type="text" placeholder="3117-01-022918-53-6" class="w-full rounded-lg border-slate-200 text-xs font-mono font-bold px-2.5 py-1.5" />
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Atas Nama (a/n)</label>
                                <input v-model="acc.atas_nama" type="text" placeholder="Nama Pemilik" class="w-full rounded-lg border-slate-200 text-xs font-bold px-2.5 py-1.5" />
                            </div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeBankAccount(index)" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus Rekening">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                        <div v-if="!form.bank_accounts || form.bank_accounts.length === 0" class="p-3 border border-dashed border-slate-300 rounded-xl text-center text-slate-400 text-xs">
                            Belum ada rekening bank. Klik "Tambah Rekening Bank" di atas.
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Penanggung Jawab (Opsional)</label>
                        <input v-model="form.penanggung_jawab" type="text" placeholder="Nama kontak PIC provider"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">No. Telepon / WA PIC</label>
                        <input v-model="form.no_telepon" type="text" placeholder="08..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Keterangan / Catatan Kontrak</label>
                    <textarea v-model="form.keterangan" rows="2" placeholder="Catatan kesepakatan kerjasama..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Batal</button>
                    <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow transition">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Provider' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DELETE CONFIRM -->
    <div v-if="modalMode === 'delete' && selectedProvider"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         @click.self="closeModal">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-red-600">delete</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Hapus Provider</h2>
                    <p class="text-xs text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <p class="text-xs text-slate-600">
                Anda yakin ingin menghapus provider <span class="font-bold text-slate-900">{{ selectedProvider.nama_provider }}</span>?
            </p>
            <div class="flex justify-end gap-2 pt-2">
                <button @click="closeModal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Batal</button>
                <button @click="confirmDelete" class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow">Ya, Hapus</button>
            </div>
        </div>
    </div>
    </Teleport>
</template>
