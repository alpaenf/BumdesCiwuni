<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    nasabah: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const editingNasabah = ref(null);
const search = ref(props.filters.search || '');
const activeTab = ref('rekening'); // 'rekening' | 'samsat' | 'pbb'

const form = useForm({
    nama: '',
    nik: '',
    no_hp: '',
    no_rekening: '',
    nama_bank: '',
    rekening_list: [],
    kendaraan_list: [],
    pbb_list: [],
    alamat: '',
    keterangan: '',
});

// Helper Rekening
const addRekeningItem = () => {
    form.rekening_list.push({
        nama_bank: '',
        no_rekening: '',
        atas_nama: '',
    });
};
const removeRekeningItem = (idx) => {
    form.rekening_list.splice(idx, 1);
};

// Helper Kendaraan Samsat
const addKendaraanItem = () => {
    form.kendaraan_list.push({
        nomor_plat: '',
        jenis_kendaraan: 'Sepeda Motor',
        merek_tipe: '',
        tahun_kendaraan: '',
        tanggal_jatuh_tempo: '',
    });
};
const removeKendaraanItem = (idx) => {
    form.kendaraan_list.splice(idx, 1);
};

// Helper Objek PBB
const addPbbItem = () => {
    form.pbb_list.push({
        nop: '',
        alamat_objek_pajak: '',
        nominal_sppt_terakhir: 0,
    });
};
const removePbbItem = (idx) => {
    form.pbb_list.splice(idx, 1);
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const applySearch = () => {
    router.get(route('pb.laku-pandai.nasabah.index'), {
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const openAddModal = () => {
    editingNasabah.value = null;
    form.reset();
    form.rekening_list = [{ nama_bank: '', no_rekening: '', atas_nama: '' }];
    form.kendaraan_list = [];
    form.pbb_list = [];
    activeTab.value = 'rekening';
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingNasabah.value = item;
    form.nama = item.nama;
    form.nik = item.nik || '';
    form.no_hp = item.no_hp || '';
    form.no_rekening = item.no_rekening || '';
    form.nama_bank = item.nama_bank || '';
    form.alamat = item.alamat || '';
    form.keterangan = item.keterangan || '';
    
    // Load rekening_list
    if (item.rekening_list && Array.isArray(item.rekening_list) && item.rekening_list.length > 0) {
        form.rekening_list = JSON.parse(JSON.stringify(item.rekening_list));
    } else if (item.no_rekening || item.nama_bank) {
        form.rekening_list = [{ nama_bank: item.nama_bank || '', no_rekening: item.no_rekening || '', atas_nama: item.nama || '' }];
    } else {
        form.rekening_list = [{ nama_bank: '', no_rekening: '', atas_nama: '' }];
    }

    // Load kendaraan_list
    if (item.kendaraan_list && Array.isArray(item.kendaraan_list)) {
        form.kendaraan_list = JSON.parse(JSON.stringify(item.kendaraan_list));
    } else {
        form.kendaraan_list = [];
    }

    // Load pbb_list
    if (item.pbb_list && Array.isArray(item.pbb_list)) {
        form.pbb_list = JSON.parse(JSON.stringify(item.pbb_list));
    } else {
        form.pbb_list = [];
    }
    
    activeTab.value = 'rekening';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (form.rekening_list.length > 0) {
        form.nama_bank = form.rekening_list[0].nama_bank || '';
        form.no_rekening = form.rekening_list[0].no_rekening || '';
    }

    if (editingNasabah.value) {
        form.put(route('pb.laku-pandai.nasabah.update', editingNasabah.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('pb.laku-pandai.nasabah.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteNasabah = (id) => {
    if (confirm('Yakin ingin menghapus data nasabah ini?')) {
        router.delete(route('pb.laku-pandai.nasabah.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Data Pelanggan & Warga (Terpadu)" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-3xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-1">
                            <span class="material-symbols-outlined text-sm">badge</span>
                            Database Terpadu Warga / Nasabah BUMDes
                        </div>
                        <h1 class="text-2xl font-black tracking-tight">Data Nasabah & Pelanggan Terpadu</h1>
                        <p class="text-blue-100 text-xs mt-1 max-w-xl">
                            Satu identitas warga untuk seluruh layanan: Rekening Bank Laku Pandai, ID Pelanggan PPOB, Plat Kendaraan Samsat, dan NOP Pajak PBB.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="openAddModal"
                            class="px-5 py-3 bg-white text-blue-900 font-bold text-xs rounded-2xl shadow-lg hover:bg-blue-50 transition flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-lg">person_add</span>
                            Tambah Nasabah Baru
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toolbar Search -->
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative w-full">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                        <input
                            v-model="search"
                            @keyup.enter="applySearch"
                            type="text"
                            placeholder="Cari nama, NIK, no. HP, rekening, plat nopol, NOP..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 transition"
                        />
                    </div>
                    <button
                        @click="applySearch"
                        class="px-3.5 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition"
                    >
                        Cari
                    </button>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="font-bold text-slate-900">{{ nasabah.total || 0 }}</span> Warga Terdaftar
                </div>
            </div>

            <!-- Table Nasabah Terpadu -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-3 text-center" width="4%">No</th>
                                <th class="px-4 py-3">Warga / Nasabah</th>
                                <th class="px-4 py-3">Kontak & Alamat</th>
                                <th class="px-4 py-3">Rekening Bank / ID PPOB</th>
                                <th class="px-4 py-3">Kendaraan (Samsat)</th>
                                <th class="px-4 py-3">Objek Pajak (PBB)</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!nasabah.data?.length">
                                <td colspan="7" class="text-center py-10 text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-1 text-slate-300">group_off</span>
                                    <p>Belum ada data warga/nasabah terdaftar.</p>
                                </td>
                            </tr>
                            <tr v-for="(item, idx) in nasabah.data" :key="item.id" class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3 text-center text-slate-400 text-[11px]">
                                    {{ (nasabah.current_page - 1) * nasabah.per_page + idx + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-black text-slate-900 text-sm">{{ item.nama }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        NIK: {{ item.nik || '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ item.no_hp || '-' }}</div>
                                    <div class="text-[10px] text-slate-500 max-w-[200px] truncate" :title="item.alamat">
                                        {{ item.alamat || '-' }}
                                    </div>
                                </td>
                                <!-- Rekening / PPOB -->
                                <td class="px-4 py-3">
                                    <div v-if="item.rekening_list && item.rekening_list.length > 0" class="space-y-1">
                                        <div v-for="(rek, rIdx) in item.rekening_list.slice(0, 2)" :key="rIdx" class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">
                                                {{ rek.nama_bank || 'Layanan' }}
                                            </span>
                                            <span class="font-mono text-slate-800 font-bold text-xs">
                                                {{ rek.no_rekening || '-' }}
                                            </span>
                                        </div>
                                        <div v-if="item.rekening_list.length > 2" class="text-[10px] text-blue-600 font-semibold">
                                            +{{ item.rekening_list.length - 2 }} rekening lainnya
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-[11px]">-</span>
                                </td>
                                <!-- Kendaraan Samsat -->
                                <td class="px-4 py-3">
                                    <div v-if="item.kendaraan_list && item.kendaraan_list.length > 0" class="space-y-1">
                                        <div v-for="(kdr, kIdx) in item.kendaraan_list.slice(0, 2)" :key="kIdx" class="flex items-center gap-1.5">
                                            <span class="font-mono font-black text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded text-[11px] border border-amber-200">
                                                {{ kdr.nomor_plat }}
                                            </span>
                                            <span class="text-[10px] text-slate-500">
                                                {{ kdr.jenis_kendaraan || 'Kendaraan' }}
                                            </span>
                                        </div>
                                        <div v-if="item.kendaraan_list.length > 2" class="text-[10px] text-amber-600 font-semibold">
                                            +{{ item.kendaraan_list.length - 2 }} kendaraan lainnya
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-[11px]">-</span>
                                </td>
                                <!-- PBB -->
                                <td class="px-4 py-3">
                                    <div v-if="item.pbb_list && item.pbb_list.length > 0" class="space-y-1">
                                        <div v-for="(pbb, pIdx) in item.pbb_list.slice(0, 2)" :key="pIdx" class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded text-[11px] border border-emerald-200">
                                                {{ pbb.nop }}
                                            </span>
                                        </div>
                                        <div v-if="item.pbb_list.length > 2" class="text-[10px] text-emerald-600 font-semibold">
                                            +{{ item.pbb_list.length - 2 }} objek lainnya
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-[11px]">-</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-1.5 text-slate-500 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                                            title="Edit Lengkap Data Warga"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button
                                            @click="deleteNasabah(item.id)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                            title="Hapus"
                                        >
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="nasabah.links && nasabah.links.length > 3" class="p-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>Menampilkan {{ nasabah.from || 0 }} - {{ nasabah.to || 0 }} dari {{ nasabah.total || 0 }} data</div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in nasabah.links"
                            :key="i"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-2.5 py-1 rounded-lg text-xs font-semibold',
                                link.active ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url && 'opacity-40 pointer-events-none'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal All-In-One: Tambah / Edit Warga & Layanan -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ editingNasabah ? 'Edit Data Warga & Layanan' : 'Tambah Data Warga Baru' }}</h3>
                        <p class="text-xs text-slate-500">Satu formulir untuk Rekening, Samsat Motor/Mobil, dan Pajak PBB</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 space-y-4 text-xs overflow-y-auto">
                    <!-- Bagian 1: Identitas Utama Warga -->
                    <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-200 space-y-3">
                        <span class="font-bold text-slate-900 text-xs block uppercase tracking-wider">1. Identitas Warga / Pelanggan</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                                <input v-model="form.nama" type="text" placeholder="Contoh: Bpk. Yuliantoro" required class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">NIK (KTP)</label>
                                <input v-model="form.nik" type="text" placeholder="16 digit NIK..." class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2 font-mono" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                                <input v-model="form.no_hp" type="text" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Alamat (RT/RW)</label>
                                <input v-model="form.alamat" type="text" placeholder="RT 05 RW 03 Desa Ciwuni..." class="w-full text-xs rounded-xl border border-slate-200 bg-white px-3 py-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 2: Tab Layanan Terpadu -->
                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <!-- Navigation Tab Buttons -->
                        <div class="flex border-b border-slate-200 bg-slate-50 text-xs font-bold">
                            <button
                                type="button"
                                @click="activeTab = 'rekening'"
                                :class="[
                                    'flex-1 py-2.5 px-3 flex items-center justify-center gap-1.5 transition',
                                    activeTab === 'rekening' ? 'bg-white text-blue-700 border-b-2 border-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                                ]"
                            >
                                <span class="material-symbols-outlined text-base">account_balance</span>
                                Rekening / Bank / PPOB ({{ form.rekening_list.length }})
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'samsat'"
                                :class="[
                                    'flex-1 py-2.5 px-3 flex items-center justify-center gap-1.5 transition',
                                    activeTab === 'samsat' ? 'bg-white text-amber-700 border-b-2 border-amber-600 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                                ]"
                            >
                                <span class="material-symbols-outlined text-base">two_wheeler</span>
                                Kendaraan Samsat ({{ form.kendaraan_list.length }})
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'pbb'"
                                :class="[
                                    'flex-1 py-2.5 px-3 flex items-center justify-center gap-1.5 transition',
                                    activeTab === 'pbb' ? 'bg-white text-emerald-700 border-b-2 border-emerald-600 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                                ]"
                            >
                                <span class="material-symbols-outlined text-base">home_work</span>
                                Pajak PBB ({{ form.pbb_list.length }})
                            </button>
                        </div>

                        <!-- TAB CONTENT 1: REKENING & PPOB -->
                        <div v-show="activeTab === 'rekening'" class="p-4 space-y-3 bg-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">Akun Bank & Nomor Rekening / Meteran</span>
                                    <span class="text-[10px] text-slate-500">Dapat menampung banyak bank atau nomor pelanggan</span>
                                </div>
                                <button
                                    type="button"
                                    @click="addRekeningItem"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] rounded-lg border border-blue-200"
                                >
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Tambah Rekening
                                </button>
                            </div>

                            <div v-if="!form.rekening_list.length" class="text-center py-4 text-slate-400">
                                Belum ada rekening bank. Klik "Tambah Rekening" di atas.
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(rek, idx) in form.rekening_list"
                                    :key="idx"
                                    class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row gap-2 items-center"
                                >
                                    <div class="w-full sm:w-1/3">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Bank / Provider</label>
                                        <input
                                            v-model="rek.nama_bank"
                                            type="text"
                                            placeholder="BCA / BRI / PLN..."
                                            class="w-full text-xs rounded-lg border border-slate-200 bg-white px-2 py-1.5"
                                        />
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">No. Rek / ID / Meter</label>
                                        <input
                                            v-model="rek.no_rekening"
                                            type="text"
                                            placeholder="8025065600"
                                            class="w-full text-xs rounded-lg border border-slate-200 bg-white px-2 py-1.5 font-mono font-bold"
                                        />
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Atas Nama (Opsional)</label>
                                        <input
                                            v-model="rek.atas_nama"
                                            type="text"
                                            placeholder="Nama pemilik..."
                                            class="w-full text-xs rounded-lg border border-slate-200 bg-white px-2 py-1.5"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeRekeningItem(idx)"
                                        class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 self-end sm:self-center mt-2 sm:mt-4"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB CONTENT 2: KENDARAAN SAMSAT -->
                        <div v-show="activeTab === 'samsat'" class="p-4 space-y-3 bg-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-amber-900 text-xs block">Daftar Kendaraan Bermotor (Samsat)</span>
                                    <span class="text-[10px] text-amber-700">Otomatis muncul saat warga ini bayar pajak di Samsat Budiman</span>
                                </div>
                                <button
                                    type="button"
                                    @click="addKendaraanItem"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] rounded-lg border border-amber-200"
                                >
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Tambah Kendaraan
                                </button>
                            </div>

                            <div v-if="!form.kendaraan_list.length" class="text-center py-4 text-slate-400">
                                Belum ada kendaraan terdaftar. Klik "Tambah Kendaraan" di atas jika nasabah memiliki motor/mobil.
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(kdr, idx) in form.kendaraan_list"
                                    :key="idx"
                                    class="p-2.5 bg-amber-50/40 rounded-xl border border-amber-200 flex flex-col sm:flex-row gap-2 items-center"
                                >
                                    <div class="w-full sm:w-1/4">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Plat Nomor (Nopol) *</label>
                                        <input
                                            v-model="kdr.nomor_plat"
                                            type="text"
                                            placeholder="R 1234 AB"
                                            class="w-full text-xs rounded-lg border border-amber-200 bg-white px-2 py-1.5 uppercase font-mono font-bold"
                                        />
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Jenis Kendaraan</label>
                                        <select v-model="kdr.jenis_kendaraan" class="w-full text-xs rounded-lg border border-amber-200 bg-white px-2 py-1.5">
                                            <option value="Sepeda Motor">Sepeda Motor</option>
                                            <option value="Mobil Penumpang">Mobil Penumpang</option>
                                            <option value="Truk / Pick Up">Truk / Pick Up</option>
                                        </select>
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Merek / Tipe</label>
                                        <input
                                            v-model="kdr.merek_tipe"
                                            type="text"
                                            placeholder="Beat / Avanza"
                                            class="w-full text-xs rounded-lg border border-amber-200 bg-white px-2 py-1.5"
                                        />
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Jatuh Tempo Pajak</label>
                                        <input
                                            v-model="kdr.tanggal_jatuh_tempo"
                                            type="date"
                                            class="w-full text-xs rounded-lg border border-amber-200 bg-white px-2 py-1.5"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeKendaraanItem(idx)"
                                        class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 self-end sm:self-center mt-2 sm:mt-4"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB CONTENT 3: OBJEK PAJAK PBB -->
                        <div v-show="activeTab === 'pbb'" class="p-4 space-y-3 bg-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-emerald-900 text-xs block">Daftar Objek Pajak Bumi & Bangunan (PBB)</span>
                                    <span class="text-[10px] text-emerald-700">Otomatis muncul saat warga ini bayar PBB di loket BUMDes</span>
                                </div>
                                <button
                                    type="button"
                                    @click="addPbbItem"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] rounded-lg border border-emerald-200"
                                >
                                    <span class="material-symbols-outlined text-sm">add</span>
                                    Tambah Objek PBB
                                </button>
                            </div>

                            <div v-if="!form.pbb_list.length" class="text-center py-4 text-slate-400">
                                Belum ada objek PBB terdaftar. Klik "Tambah Objek PBB" jika memiliki tanah/rumah di desa.
                            </div>

                            <div class="space-y-2">
                                <div
                                    v-for="(pbb, idx) in form.pbb_list"
                                    :key="idx"
                                    class="p-2.5 bg-emerald-50/40 rounded-xl border border-emerald-200 flex flex-col sm:flex-row gap-2 items-center"
                                >
                                    <div class="w-full sm:w-1/2">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Nomor Objek Pajak (NOP) *</label>
                                        <input
                                            v-model="pbb.nop"
                                            type="text"
                                            placeholder="33.02.xxx.xxx.xxx-xxxx.x"
                                            class="w-full text-xs rounded-lg border border-emerald-200 bg-white px-2 py-1.5 font-mono font-bold"
                                        />
                                    </div>
                                    <div class="w-full sm:w-1/2">
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Letak Objek Pajak (Alamat/Blok)</label>
                                        <input
                                            v-model="pbb.alamat_objek_pajak"
                                            type="text"
                                            placeholder="RT 02 / RW 03 Desa Ciwuni"
                                            class="w-full text-xs rounded-lg border border-emerald-200 bg-white px-2 py-1.5"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removePbbItem(idx)"
                                        class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 self-end sm:self-center mt-2 sm:mt-4"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <input v-model="form.keterangan" type="text" placeholder="Catatan nasabah..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-600/20">
                            {{ editingNasabah ? 'Simpan Perubahan Data Warga' : 'Simpan Data Warga' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>
