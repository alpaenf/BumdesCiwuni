<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    transaksi: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    saldo: { type: Object, default: () => ({}) },
    day_summary: { type: Object, default: () => ({}) },
    nasabah_list: { type: Array, default: () => [] },
});

const isModalOpen = ref(false);
const search = ref(props.filters.search || '');
const filterDate = ref(props.filters.date || '');
const filterJenis = ref(props.filters.jenis_transaksi || '');
const filterStatus = ref(props.filters.status_pembayaran || '');

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    nasabah_id: null,
    nama_nasabah: '',
    jenis_transaksi: 'Tarik tunai',
    rekening_masuk: 0,
    rekening_keluar: 0,
    cash_masuk: 0,
    cash_keluar: 0,
    harga_agen: 0,
    harga_konsumen: 0,
    jenis_pembayaran: 'Cash',
    status_pembayaran: 'Lunas',
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const jenisTransaksiOptions = [
    'Deposit',
    'Tarik tunai',
    'Transfer sesama',
    'Transfer antarbank',
    'Administrasi bank',
    'Bunga',
    'Pulsa data',
    'Pulsa listrik',
    'Pajak PBB',
    'Pajak kendaraan',
    'PDAM',
    'Virtual account',
    'Multi payment',
    'Pelunasan hutang',
];

const calculatedJasa = computed(() => {
    const konsumen = Number(form.harga_konsumen) || 0;
    const agen = Number(form.harga_agen) || 0;
    return Math.max(0, konsumen - agen);
});

const onSelectNasabah = (e) => {
    const selectedId = e.target.value;
    if (!selectedId) return;
    const n = props.nasabah_list.find(item => item.id == selectedId);
    if (n) {
        form.nasabah_id = n.id;
        form.nama_nasabah = n.nama;
    }
};

const applyFilter = () => {
    router.get(route('pb.laku-pandai.index'), {
        search: search.value || undefined,
        date: filterDate.value || undefined,
        jenis_transaksi: filterJenis.value || undefined,
        status_pembayaran: filterStatus.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    filterDate.value = '';
    filterJenis.value = '';
    filterStatus.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.laku-pandai.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
        },
    });
};

const deleteTransaksi = (id) => {
    if (confirm('Yakin ingin menghapus catatan transaksi ini? Saldo berjalan akan disesuaikan.')) {
        router.delete(route('pb.laku-pandai.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Transaksi Laku Pandai" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Saldo & Day Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Saldo Tunai (Cash)</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(saldo.saldo_cash) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Fisik kas uang tunai di kasir</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Saldo Rekening Bank</p>
                    <h4 class="text-xl font-black text-purple-600 mt-1">{{ formatRupiah(saldo.saldo_rekening) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Saldo terdaftar di akun bank agen</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Gabungan Saldo</p>
                    <h4 class="text-xl font-black text-blue-600 mt-1">{{ formatRupiah(saldo.total_saldo) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Saldo Tunai + Saldo Rekening</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pendapatan Jasa Tanggal Ini</p>
                    <h4 class="text-xl font-black text-emerald-600 mt-1">
                        {{ formatRupiah((day_summary.pendapatan_cash || 0) + (day_summary.pendapatan_rekening || 0)) }}
                    </h4>
                    <p class="text-[10px] text-slate-500 mt-0.5">
                        Cash: {{ formatRupiah(day_summary.pendapatan_cash) }} | Rek: {{ formatRupiah(day_summary.pendapatan_rekening) }}
                    </p>
                </div>
            </div>

            <!-- Action Bar & Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari nasabah, no. transaksi..."
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 w-48 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <input
                        v-model="filterDate"
                        @change="applyFilter"
                        type="date"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <select
                        v-model="filterJenis"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">Semua Layanan</option>
                        <option v-for="j in jenisTransaksiOptions" :key="j" :value="j">{{ j }}</option>
                    </select>
                    <select
                        v-model="filterStatus"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="Lunas">Lunas</option>
                        <option value="Hutang">Hutang</option>
                    </select>
                    <button
                        @click="resetFilter"
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition"
                        title="Reset Filter"
                    >
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('pb.laku-pandai.nasabah.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                    >
                        <span class="material-symbols-outlined text-base">group</span>
                        Database Nasabah
                    </Link>
                    <button
                        @click="isModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 transition"
                    >
                        <span class="material-symbols-outlined text-base">add</span>
                        Catat Transaksi Laku Pandai
                    </button>
                </div>
            </div>

            <!-- Table Transaksi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 py-3 text-center">No</th>
                                <th class="px-3 py-3">Tanggal / No Trx</th>
                                <th class="px-3 py-3">Nasabah</th>
                                <th class="px-3 py-3">Jenis Layanan</th>
                                <th class="px-3 py-3 text-right">Rekening Masuk</th>
                                <th class="px-3 py-3 text-right">Rekening Keluar</th>
                                <th class="px-3 py-3 text-right">Cash Masuk</th>
                                <th class="px-3 py-3 text-right">Cash Keluar</th>
                                <th class="px-3 py-3 text-right">Harga Konsumen</th>
                                <th class="px-3 py-3 text-right">Harga Agen</th>
                                <th class="px-3 py-3 text-right text-emerald-600 font-extrabold">Jasa Cash</th>
                                <th class="px-3 py-3 text-right text-emerald-600 font-extrabold">Jasa Rek</th>
                                <th class="px-3 py-3 text-right">Saldo Rekening</th>
                                <th class="px-3 py-3 text-right">Saldo Cash</th>
                                <th class="px-3 py-3 text-right font-extrabold text-blue-600">Total Saldo</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!transaksi.data?.length">
                                <td colspan="17" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                                    <p>Tidak ada catatan transaksi Laku Pandai.</p>
                                </td>
                            </tr>
                            <tr v-for="(item, idx) in transaksi.data" :key="item.id" class="hover:bg-slate-50/75 transition">
                                <td class="px-3 py-2.5 text-center text-slate-400 text-[11px]">
                                    {{ (transaksi.current_page - 1) * transaksi.per_page + idx + 1 }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-bold text-slate-900">{{ item.tanggal }}</div>
                                    <div class="font-mono text-[10px] text-slate-500">{{ item.nomor_transaksi }}</div>
                                </td>
                                <td class="px-3 py-2.5 font-bold text-slate-800">{{ item.nama_nasabah }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[10px]">
                                        {{ item.jenis_transaksi }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-slate-700">
                                    {{ item.rekening_masuk > 0 ? formatRupiah(item.rekening_masuk) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-rose-600">
                                    {{ item.rekening_keluar > 0 ? formatRupiah(item.rekening_keluar) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-slate-700">
                                    {{ item.cash_masuk > 0 ? formatRupiah(item.cash_masuk) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-rose-600">
                                    {{ item.cash_keluar > 0 ? formatRupiah(item.cash_keluar) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-semibold text-slate-800">
                                    {{ item.harga_konsumen > 0 ? formatRupiah(item.harga_konsumen) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right text-slate-500">
                                    {{ item.harga_agen > 0 ? formatRupiah(item.harga_agen) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-600">
                                    {{ item.pendapatan_jasa_cash > 0 ? formatRupiah(item.pendapatan_jasa_cash) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-600">
                                    {{ item.pendapatan_jasa_rekening > 0 ? formatRupiah(item.pendapatan_jasa_rekening) : '-' }}
                                </td>
                                <td class="px-3 py-2.5 text-right text-slate-700">{{ formatRupiah(item.saldo_rekening) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-700">{{ formatRupiah(item.saldo_cash) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-blue-600">{{ formatRupiah(item.total_saldo) }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                        item.status_pembayaran === 'Lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                                    ]">
                                        {{ item.status_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <button
                                        @click="deleteTransaksi(item.id)"
                                        class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transaksi.links && transaksi.links.length > 3" class="p-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>Menampilkan {{ transaksi.from || 0 }} - {{ transaksi.to || 0 }} dari {{ transaksi.total || 0 }} data</div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in transaksi.links"
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

        <!-- Modal Tambah Transaksi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Catat Transaksi Harian Laku Pandai</h3>
                        <p class="text-xs text-slate-500">Input transaksi agen keuangan dan pergerakan saldo</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Transaksi *</label>
                            <input v-model="form.tanggal" type="date" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih dari Master Nasabah (Opsional)</label>
                            <select @change="onSelectNasabah" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="">-- Pilih Nasabah Terdaftar --</option>
                                <option v-for="n in nasabah_list" :key="n.id" :value="n.id">{{ n.nama }} ({{ n.nama_bank || 'Nasabah' }})</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Nasabah / Pelanggan *</label>
                            <input v-model="form.nama_nasabah" type="text" placeholder="Nama lengkap..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Layanan / Transaksi *</label>
                            <select v-model="form.jenis_transaksi" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option v-for="j in jenisTransaksiOptions" :key="j" :value="j">{{ j }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input Mutasi Nominal -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <span class="font-bold text-slate-700 text-[11px] uppercase tracking-wider block">Mutasi Rekening & Kas</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Rekening Masuk</label>
                                <input v-model.number="form.rekening_masuk" type="number" min="0" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Rekening Keluar</label>
                                <input v-model.number="form.rekening_keluar" type="number" min="0" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Cash Masuk</label>
                                <input v-model.number="form.cash_masuk" type="number" min="0" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Cash Keluar</label>
                                <input v-model.number="form.cash_keluar" type="number" min="0" class="w-full text-xs rounded-xl border border-slate-200 bg-white px-2.5 py-1.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Harga Konsumen, Harga Agen & Pendapatan -->
                    <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100 space-y-2">
                        <span class="font-bold text-emerald-800 text-[11px] uppercase tracking-wider block">Perhitungan Jasa Agen BUMDes</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Harga Konsumen (Rp)</label>
                                <input v-model.number="form.harga_konsumen" type="number" min="0" placeholder="Biaya dibayar nasabah" class="w-full text-xs rounded-xl border border-emerald-200 bg-white px-3 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Harga Agen / Modal (Rp)</label>
                                <input v-model.number="form.harga_agen" type="number" min="0" placeholder="Biaya agen/provider" class="w-full text-xs rounded-xl border border-emerald-200 bg-white px-3 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Estimasi Pendapatan Jasa</label>
                                <div class="px-3 py-1.5 bg-emerald-100/70 border border-emerald-200 rounded-xl font-bold text-emerald-800 text-xs">
                                    {{ formatRupiah(calculatedJasa) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Pembayaran Jasa</label>
                            <select v-model="form.jenis_pembayaran" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="Cash">Cash (Uang Tunai)</option>
                                <option value="Transfer">Transfer Rekening</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Pembayaran</label>
                            <select v-model="form.status_pembayaran" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="Lunas">Lunas</option>
                                <option value="Hutang">Hutang</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan / Catatan Transaksi</label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Catatan tambahan bila ada..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-md shadow-blue-600/20">
                            Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

