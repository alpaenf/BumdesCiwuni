<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    kas: { type: Object, required: true },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const search = ref(props.filters.search || '');
const filterDate = ref(props.filters.date || '');
const filterJenis = ref(props.filters.jenis || '');
const filterTipeKas = ref(props.filters.tipe_kas || '');
const filterKategori = ref(props.filters.kategori || '');

const kategoriOptions = [
    'Laku Pandai',
    'Samsat',
    'Pajak PBB',
    'Transaksi Umum',
    'Operasional',
    'Modal / Kas Awal',
    'Tarik Tunai Bank',
    'Setor Tunai Bank',
    'Lain-lain',
];

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    kategori_layanan: 'Lain-lain',
    jenis: 'masuk',
    nominal: 0,
    tipe_kas: 'Tunai',
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const applyFilter = () => {
    router.get(route('pb.kas.index'), {
        search: search.value || undefined,
        date: filterDate.value || undefined,
        jenis: filterJenis.value || undefined,
        tipe_kas: filterTipeKas.value || undefined,
        kategori: filterKategori.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    filterDate.value = '';
    filterJenis.value = '';
    filterTipeKas.value = '';
    filterKategori.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.kas.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
        },
    });
};

const deleteKas = (id) => {
    if (confirm('Yakin ingin menghapus catatan kas ini?')) {
        router.delete(route('pb.kas.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Buku Kas & Rekapitulasi Saldo" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Saldo Cards Header -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl text-white shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block">Total Saldo Kas Bersih</span>
                        <h3 class="text-2xl font-black mt-1">{{ formatRupiah(summary.saldo_akhir) }}</h3>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-300">
                        <span>Total Masuk - Total Keluar</span>
                        <span class="material-symbols-outlined text-sm text-emerald-400">account_balance_wallet</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Saldo Kas Tunai (Fisik)</span>
                        <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ formatRupiah(summary.saldo_tunai) }}</h3>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Uang cash di laci operasional</span>
                        <span class="material-symbols-outlined text-sm text-emerald-600">payments</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Saldo Rekening Bank</span>
                        <h3 class="text-2xl font-black text-blue-600 mt-1">{{ formatRupiah(summary.saldo_rekening) }}</h3>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Dana di rekening agen</span>
                        <span class="material-symbols-outlined text-sm text-blue-600">account_balance</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Arus Transaksi</span>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Total Masuk:</span>
                                <span class="font-bold text-emerald-600">{{ formatRupiah(summary.total_masuk) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Total Keluar:</span>
                                <span class="font-bold text-rose-600">{{ formatRupiah(summary.total_keluar) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-100 text-[10px] text-slate-400">
                        Akumulasi seluruh unit layanan
                    </div>
                </div>
            </div>

            <!-- Filter & Action Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari uraian / keterangan..."
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @keyup.enter="applyFilter"
                        />
                        <select
                            v-model="filterKategori"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <select
                            v-model="filterJenis"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Arus (Masuk/Keluar)</option>
                            <option value="masuk">Kas Masuk</option>
                            <option value="keluar">Kas Keluar</option>
                        </select>
                        <select
                            v-model="filterTipeKas"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Tipe Kas</option>
                            <option value="Tunai">Tunai (Cash)</option>
                            <option value="Rekening">Rekening (Bank)</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="applyFilter"
                            class="px-3 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition"
                        >
                            Filter
                        </button>
                        <button
                            v-if="search || filterDate || filterJenis || filterTipeKas || filterKategori"
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
                            <span>Catat Kas</span>
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
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Kategori Layanan</th>
                                <th class="p-3">Keterangan / Uraian</th>
                                <th class="p-3 text-center">Tipe Kas</th>
                                <th class="p-3 text-right">Kas Masuk</th>
                                <th class="p-3 text-right">Kas Keluar</th>
                                <th class="p-3">Petugas</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="item in kas.data" :key="item.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap font-medium text-slate-800">
                                    {{ item.tanggal }}
                                </td>
                                <td class="p-3 whitespace-nowrap font-semibold">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 border">
                                        {{ item.kategori_layanan }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-900">
                                    {{ item.keterangan }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-[10px] font-bold',
                                            item.tipe_kas === 'Tunai' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'
                                        ]"
                                    >
                                        {{ item.tipe_kas }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-black whitespace-nowrap">
                                    <span v-if="item.jenis === 'masuk'" class="text-emerald-600">
                                        + {{ formatRupiah(item.nominal) }}
                                    </span>
                                    <span v-else class="text-slate-300">-</span>
                                </td>
                                <td class="p-3 text-right font-black whitespace-nowrap">
                                    <span v-if="item.jenis === 'keluar'" class="text-rose-600">
                                        - {{ formatRupiah(item.nominal) }}
                                    </span>
                                    <span v-else class="text-slate-300">-</span>
                                </td>
                                <td class="p-3 whitespace-nowrap text-slate-500">
                                    {{ item.nama_admin || 'Admin' }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button
                                        @click="deleteKas(item.id)"
                                        class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition"
                                        title="Hapus Catatan"
                                    >
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!kas.data || kas.data.length === 0">
                                <td colspan="8" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">account_balance_wallet</span>
                                    <p>Belum ada catatan buku kas.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="kas.links && kas.links.length > 3" class="p-3 border-t border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <p class="text-[11px] text-slate-500">
                        Menampilkan {{ kas.from || 0 }} - {{ kas.to || 0 }} dari {{ kas.total || 0 }} catatan
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in kas.links"
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

        <!-- Modal Tambah Kas -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">Catat Arus Kas Masuk / Keluar</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="mt-4 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                        <input
                            v-model="form.tanggal"
                            type="date"
                            required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenis Arus</label>
                            <select
                                v-model="form.jenis"
                                required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="masuk">Kas Masuk (+)</option>
                                <option value="keluar">Kas Keluar (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipe Saldo</label>
                            <select
                                v-model="form.tipe_kas"
                                required
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                                <option value="Tunai">Tunai (Cash di Laci)</option>
                                <option value="Rekening">Rekening (Bank)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kategori Layanan</label>
                        <select
                            v-model="form.kategori_layanan"
                            required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input
                            v-model.number="form.nominal"
                            type="number"
                            min="1"
                            required
                            placeholder="Contoh: 100000"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Keterangan / Uraian</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="2"
                            required
                            placeholder="Alasan penerimaan / pengeluaran dana..."
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
                            <span>Simpan Kas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>
