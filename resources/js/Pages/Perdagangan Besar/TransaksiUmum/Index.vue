<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    transaksi: { type: Object, required: true },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    nasabah_list: { type: Array, default: () => [] },
});

const isModalOpen = ref(false);
const search = ref(props.filters.search || '');
const filterDate = ref(props.filters.date || '');
const filterStatus = ref(props.filters.status || '');
const filterJenis = ref(props.filters.jenis_transaksi || '');
const selectedNasabahId = ref('');
const selectedAccountIndex = ref('');

const selectedNasabah = computed(() => {
    return props.nasabah_list.find(n => n.id == selectedNasabahId.value);
});

const availableAccounts = computed(() => {
    if (!selectedNasabah.value) return [];
    if (selectedNasabah.value.rekening_list && Array.isArray(selectedNasabah.value.rekening_list) && selectedNasabah.value.rekening_list.length > 0) {
        return selectedNasabah.value.rekening_list;
    }
    if (selectedNasabah.value.no_rekening || selectedNasabah.value.nama_bank) {
        return [{
            nama_bank: selectedNasabah.value.nama_bank || 'Layanan',
            no_rekening: selectedNasabah.value.no_rekening || '',
            atas_nama: selectedNasabah.value.nama || '',
        }];
    }
    return [];
});

const onSelectNasabah = (e) => {
    const val = e.target.value;
    selectedNasabahId.value = val;
    selectedAccountIndex.value = '';
    if (!val) return;

    const n = props.nasabah_list.find(item => item.id == val);
    if (n) {
        form.nama_pelanggan = n.nama;
        // Jika hanya punya 1 nomor rekening/ID pelanggan, langsung auto-fill
        const accounts = (n.rekening_list && n.rekening_list.length > 0)
            ? n.rekening_list
            : (n.no_rekening ? [{ nama_bank: n.nama_bank, no_rekening: n.no_rekening }] : []);

        if (accounts.length === 1) {
            form.nomor_tujuan = accounts[0].no_rekening || '';
            selectedAccountIndex.value = 0;
        }
    }
};

const onSelectAccount = (e) => {
    const idx = e.target.value;
    selectedAccountIndex.value = idx;
    if (idx !== '' && availableAccounts.value[idx]) {
        form.nomor_tujuan = availableAccounts.value[idx].no_rekening || '';
    }
};

const jenisTransaksiOptions = [
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

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    waktu: new Date().toTimeString().split(' ')[0],
    nama_pelanggan: '',
    jenis_transaksi: 'Pulsa',
    nomor_tujuan: '',
    biaya_pokok: 0,
    biaya_admin: 0,
    biaya_jasa_agen: 2000,
    metode_pembayaran: 'CASH',
    status: 'SUCCES',
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const calculatedTotal = computed(() => {
    const pokok = Number(form.biaya_pokok) || 0;
    const admin = Number(form.biaya_admin) || 0;
    const jasa = Number(form.biaya_jasa_agen) || 0;
    return pokok + admin + jasa;
});

const applyFilter = () => {
    router.get(route('pb.transaksi-umum.index'), {
        search: search.value || undefined,
        date: filterDate.value || undefined,
        status: filterStatus.value || undefined,
        jenis_transaksi: filterJenis.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    filterDate.value = '';
    filterStatus.value = '';
    filterJenis.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.transaksi-umum.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
            form.waktu = new Date().toTimeString().split(' ')[0];
            form.biaya_admin = 0;
            form.biaya_jasa_agen = 2000;
        },
    });
};

const deleteTransaksi = (id) => {
    if (confirm('Yakin ingin menghapus catatan transaksi umum ini?')) {
        router.delete(route('pb.transaksi-umum.destroy', id));
    }
};

const openStruk = (id) => {
    window.open(route('pb.transaksi-umum.struk', id), '_blank', 'width=420,height=600');
};

const getBadgeColor = (jenis) => {
    switch (jenis) {
        case 'Pulsa':
        case 'Pulsa data':
            return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'Pulsa listrik':
            return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'PDAM':
            return 'bg-cyan-50 text-cyan-700 border-cyan-200';
        case 'BPJS':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'WIFI':
            return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'Virtual account':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        case 'Transfer sesama':
        case 'Transfer antarbank':
            return 'bg-violet-50 text-violet-700 border-violet-200';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Transaksi Umum & PPOB" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Transaksi</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ (summary.total_transaksi || 0).toLocaleString() }}</h4>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-semibold">{{ summary.total_sukses || 0 }} sukses diselesaikan</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Biaya Pokok</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(summary.total_biaya_pokok) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Tagihan & pulsa terproses</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pendapatan Jasa Agen</p>
                    <h4 class="text-xl font-black text-emerald-600 mt-1">{{ formatRupiah(summary.total_jasa_agen) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Keuntungan operasional agen</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Pembayaran Pelanggan</p>
                    <h4 class="text-xl font-black text-blue-600 mt-1">{{ formatRupiah(summary.total_bayar) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Arus dana masuk ke kas</p>
                </div>
            </div>

            <!-- Filter & Action Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari pelanggan / nomor tujuan..."
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @keyup.enter="applyFilter"
                        />
                        <select
                            v-model="filterJenis"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Layanan Transaksi</option>
                            <option v-for="opt in jenisTransaksiOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <select
                            v-model="filterStatus"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Status</option>
                            <option value="SUCCES">SUCCES</option>
                            <option value="PENDING">PENDING</option>
                            <option value="CANCEL">CANCEL</option>
                        </select>
                        <input
                            v-model="filterDate"
                            type="date"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="applyFilter"
                            class="px-3 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition"
                        >
                            Filter
                        </button>
                        <button
                            v-if="search || filterDate || filterStatus || filterJenis"
                            @click="resetFilter"
                            class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium hover:bg-slate-200 transition"
                        >
                            Reset
                        </button>
                        <button
                            @click="isModalOpen = true"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm"
                        >
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            <span>Tambah Transaksi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                                <th class="p-3">No. Transaksi</th>
                                <th class="p-3">Tanggal & Waktu</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3">Layanan & No. Tujuan</th>
                                <th class="p-3 text-right">Biaya Pokok</th>
                                <th class="p-3 text-right">Jasa Agen</th>
                                <th class="p-3 text-right">Total Bayar</th>
                                <th class="p-3 text-center">Metode</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="t in transaksi.data" :key="t.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ t.nomor_transaksi }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ t.tanggal }}</div>
                                    <div class="text-[10px] text-slate-400">{{ t.waktu }}</div>
                                </td>
                                <td class="p-3 font-semibold text-slate-900">
                                    {{ t.nama_pelanggan }}
                                    <div v-if="t.keterangan" class="text-[10px] text-slate-400 font-normal italic">
                                        {{ t.keterangan }}
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-bold border inline-block mb-1', getBadgeColor(t.jenis_transaksi)]">
                                        {{ t.jenis_transaksi }}
                                    </span>
                                    <div class="font-mono text-slate-600 text-[11px]">{{ t.nomor_tujuan }}</div>
                                </td>
                                <td class="p-3 text-right font-medium">
                                    {{ formatRupiah(t.biaya_pokok) }}
                                </td>
                                <td class="p-3 text-right font-bold text-emerald-600">
                                    {{ formatRupiah(t.biaya_jasa_agen) }}
                                </td>
                                <td class="p-3 text-right font-black text-slate-900">
                                    {{ formatRupiah(t.total_bayar) }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        {{ t.metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                            t.status === 'SUCCES' ? 'bg-emerald-100 text-emerald-800' :
                                            t.status === 'PENDING' ? 'bg-amber-100 text-amber-800' :
                                            'bg-rose-100 text-rose-800'
                                        ]"
                                    >
                                        {{ t.status }}
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openStruk(t.id)"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold border border-emerald-200 transition"
                                            title="Cetak Struk Transaksi"
                                        >
                                            <span class="material-symbols-outlined text-sm">print</span>
                                            <span>Struk</span>
                                        </button>
                                        <button
                                            @click="deleteTransaksi(t.id)"
                                            class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition"
                                            title="Hapus Transaksi"
                                        >
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!transaksi.data || transaksi.data.length === 0">
                                <td colspan="10" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                                    <p>Belum ada transaksi umum yang dicatat.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transaksi.links && transaksi.links.length > 3" class="p-3 border-t border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <p class="text-[11px] text-slate-500">
                        Menampilkan {{ transaksi.from || 0 }} - {{ transaksi.to || 0 }} dari {{ transaksi.total || 0 }} transaksi
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in transaksi.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-2.5 py-1 rounded text-xs transition',
                                link.active ? 'bg-emerald-600 text-white font-bold' : 'bg-white border text-slate-600 hover:bg-slate-100',
                                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Transaksi -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">Catat Transaksi Umum / PPOB</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="mt-4 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input
                                v-model="form.tanggal"
                                type="date"
                                required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Waktu</label>
                            <input
                                v-model="form.waktu"
                                type="time"
                                step="1"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pilih dari Master Nasabah (Opsional)</label>
                            <select
                                :value="selectedNasabahId"
                                @change="onSelectNasabah"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs bg-emerald-50/50 border-emerald-200"
                            >
                                <option value="">-- Pelanggan Bebas / Manual --</option>
                                <option v-for="n in nasabah_list" :key="n.id" :value="n.id">
                                    {{ n.nama }} {{ n.no_hp ? '(' + n.no_hp + ')' : '' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Pelanggan *</label>
                            <input
                                v-model="form.nama_pelanggan"
                                type="text"
                                required
                                placeholder="Nama pembeli / nasabah"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenis Layanan Transaksi *</label>
                            <select
                                v-model="form.jenis_transaksi"
                                required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs"
                            >
                                <option v-for="opt in jenisTransaksiOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-semibold text-slate-700">Nomor Tujuan / ID Pelanggan *</label>
                                <span v-if="availableAccounts.length > 1" class="text-[10px] text-emerald-600 font-semibold">Ada {{ availableAccounts.length }} akun tersimpan</span>
                            </div>
                            
                            <!-- Dropdown pilih rekening jika nasabah memiliki lebih dari 1 rekening tersimpan -->
                            <div v-if="availableAccounts.length > 1" class="mb-1.5">
                                <select
                                    :value="selectedAccountIndex"
                                    @change="onSelectAccount"
                                    class="w-full px-2.5 py-1.5 border border-emerald-300 bg-emerald-50 text-emerald-900 rounded-lg text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                >
                                    <option value="">-- Pilih Akun / No. Meter Nasabah --</option>
                                    <option v-for="(acc, aIdx) in availableAccounts" :key="aIdx" :value="aIdx">
                                        [{{ acc.nama_bank || 'Layanan' }}] {{ acc.no_rekening }} {{ acc.atas_nama ? '- ' + acc.atas_nama : '' }}
                                    </option>
                                </select>
                            </div>

                            <input
                                v-model="form.nomor_tujuan"
                                type="text"
                                required
                                placeholder="No HP / Meter / Rekening"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Biaya Pokok (Rp)</label>
                            <input
                                v-model.number="form.biaya_pokok"
                                type="number"
                                min="0"
                                required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Biaya Admin (Rp)</label>
                            <input
                                v-model.number="form.biaya_admin"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jasa Agen (Rp)</label>
                            <input
                                v-model.number="form.biaya_jasa_agen"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Total Preview Banner -->
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-emerald-800 font-bold uppercase tracking-wider block">Total Tagihan Pelanggan</span>
                            <span class="text-xs text-emerald-600">Pokok + Admin + Jasa Agen</span>
                        </div>
                        <span class="text-base font-black text-emerald-700">{{ formatRupiah(calculatedTotal) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                            <select
                                v-model="form.metode_pembayaran"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="CASH">CASH (Tunai)</option>
                                <option value="TRANSFER">TRANSFER (Rekening)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Status Transaksi</label>
                            <select
                                v-model="form.status"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="SUCCES">SUCCES</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCEL">CANCEL</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Keterangan (Opsional)</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="2"
                            placeholder="Catatan tambahan..."
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                            <span>Simpan Transaksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

