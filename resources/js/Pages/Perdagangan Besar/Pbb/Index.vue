<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    transaksi: { type: Object, required: true },
    summary: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const search = ref(props.filters.search || '');
const filterDate = ref(props.filters.date || '');
const filterStatus = ref(props.filters.status || '');
const filterTahun = ref(props.filters.tahun || '');

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    waktu: new Date().toTimeString().split(' ')[0],
    nama_wajib_pajak: '',
    nop: '',
    alamat_objek_pajak: '',
    tahun_pajak: new Date().getFullYear().toString(),
    nominal_pokok: 0,
    biaya_admin: 5000,
    metode_pembayaran: 'CASH',
    status: 'SUCCES',
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const calculatedTotal = computed(() => {
    const pokok = Number(form.nominal_pokok) || 0;
    const admin = Number(form.biaya_admin) || 0;
    return pokok + admin;
});

const applyFilter = () => {
    router.get(route('pb.pbb.index'), {
        search: search.value || undefined,
        date: filterDate.value || undefined,
        status: filterStatus.value || undefined,
        tahun: filterTahun.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    filterDate.value = '';
    filterStatus.value = '';
    filterTahun.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.pbb.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
            form.waktu = new Date().toTimeString().split(' ')[0];
            form.biaya_admin = 5000;
            form.tahun_pajak = new Date().getFullYear().toString();
        },
    });
};

const deleteTransaksi = (id) => {
    if (confirm('Yakin ingin menghapus catatan transaksi PBB ini?')) {
        router.delete(route('pb.pbb.destroy', id));
    }
};

const openStruk = (id) => {
    window.open(route('pb.pbb.struk', id), '_blank', 'width=420,height=600');
};
</script>

<template>
    <PerdaganganBesarLayout title="Pajak Bumi dan Bangunan (PBB)" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Transaksi PBB</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ (summary.total_transaksi || 0).toLocaleString() }}</h4>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-semibold">{{ summary.total_sukses || 0 }} pembayaran berhasil</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pokok PBB Terkumpul</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(summary.total_nominal_pokok) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pajak bumi & bangunan disetor</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Biaya Admin (Jasa BUMDes)</p>
                    <h4 class="text-xl font-black text-emerald-600 mt-1">{{ formatRupiah(summary.total_biaya_admin) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pendapatan jasa agen PBB</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Penerimaan Kasir</p>
                    <h4 class="text-xl font-black text-blue-600 mt-1">{{ formatRupiah(summary.total_bayar) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pokok PBB + Biaya Admin</p>
                </div>
            </div>

            <!-- Action Bar & Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari NOP, nama WP, no. trx..."
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 w-56 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <input
                        v-model="filterDate"
                        @change="applyFilter"
                        type="date"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <input
                        v-model="filterTahun"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Tahun Pajak (2026)"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 w-36 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <select
                        v-model="filterStatus"
                        @change="applyFilter"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="SUCCES">SUCCES</option>
                        <option value="PENDING">PENDING</option>
                        <option value="CANCEL">CANCEL</option>
                    </select>
                    <button
                        @click="resetFilter"
                        class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition"
                        title="Reset Filter"
                    >
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                    </button>
                </div>

                <div>
                    <button
                        @click="isModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition"
                    >
                        <span class="material-symbols-outlined text-base">add</span>
                        Input Pembayaran PBB
                    </button>
                </div>
            </div>

            <!-- Table Transaksi PBB -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 py-3 text-center">No</th>
                                <th class="px-3 py-3">Tanggal / Waktu</th>
                                <th class="px-3 py-3">No. Transaksi</th>
                                <th class="px-3 py-3">Nama Wajib Pajak</th>
                                <th class="px-3 py-3">Nomor Objek Pajak (NOP)</th>
                                <th class="px-3 py-3 text-center">Tahun</th>
                                <th class="px-3 py-3 text-right">Pokok PBB</th>
                                <th class="px-3 py-3 text-right text-emerald-600 font-bold">Biaya Admin</th>
                                <th class="px-3 py-3 text-right font-bold text-slate-900">Total Bayar</th>
                                <th class="px-3 py-3 text-center">Metode</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3">Admin</th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!transaksi.data?.length">
                                <td colspan="13" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                                    <p>Belum ada catatan pembayaran Pajak PBB.</p>
                                </td>
                            </tr>
                            <tr v-for="(item, idx) in transaksi.data" :key="item.id" class="hover:bg-slate-50/75 transition">
                                <td class="px-3 py-2.5 text-center text-slate-400 text-[11px]">
                                    {{ (transaksi.current_page - 1) * transaksi.per_page + idx + 1 }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-bold text-slate-900">{{ item.tanggal }}</div>
                                    <div class="text-[10px] text-slate-400">{{ item.waktu || '-' }}</div>
                                </td>
                                <td class="px-3 py-2.5 font-mono text-[11px] text-slate-700">{{ item.nomor_transaksi }}</td>
                                <td class="px-3 py-2.5 font-bold text-slate-800">{{ item.nama_wajib_pajak }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="font-mono font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded text-[11px] border border-emerald-200">
                                        {{ item.nop }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center font-bold text-slate-700">{{ item.tahun_pajak }}</td>
                                <td class="px-3 py-2.5 text-right font-medium text-slate-700">{{ formatRupiah(item.nominal_pokok) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-600">{{ formatRupiah(item.biaya_admin) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-900">{{ formatRupiah(item.total_bayar) }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ item.metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                        item.status === 'SUCCES' ? 'bg-emerald-100 text-emerald-700' :
                                        item.status === 'PENDING' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'
                                    ]">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-slate-500 text-[11px]">{{ item.nama_admin }}</td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openStruk(item.id)"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold border border-emerald-200 transition"
                                            title="Cetak Struk PBB"
                                        >
                                            <span class="material-symbols-outlined text-sm">print</span>
                                            <span>Struk</span>
                                        </button>
                                        <button
                                            @click="deleteTransaksi(item.id)"
                                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
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
                                link.active ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url && 'opacity-40 pointer-events-none'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Input Transaksi PBB -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Input Transaksi Pembayaran PBB</h3>
                        <p class="text-xs text-slate-500">Pajak Bumi dan Bangunan Desa Ciwuni</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 overflow-y-auto space-y-3.5 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Transaksi *</label>
                            <input v-model="form.tanggal" type="date" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Waktu Transaksi</label>
                            <input v-model="form.waktu" type="time" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Wajib Pajak *</label>
                            <input v-model="form.nama_wajib_pajak" type="text" placeholder="Nama pemilik di SPPT..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Objek Pajak (NOP) *</label>
                            <input v-model="form.nop" type="text" placeholder="33.02.xxx.xxx.xxx-xxxx.x" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono font-bold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Pajak SPPT *</label>
                            <input v-model="form.tahun_pajak" type="text" placeholder="2026" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Metode Pembayaran</label>
                            <select v-model="form.metode_pembayaran" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="CASH">CASH (Tunai)</option>
                                <option value="TRANSFER">TRANSFER (Bank)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nominal Pokok & Biaya Admin -->
                    <div class="p-3 bg-emerald-50/60 rounded-2xl border border-emerald-200 space-y-2">
                        <span class="font-bold text-emerald-900 text-[11px] uppercase tracking-wider block">Rincian Pembayaran PBB</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Nominal Pokok PBB (Rp) *</label>
                                <input v-model.number="form.nominal_pokok" type="number" min="0" required class="w-full text-xs rounded-xl border border-emerald-200 bg-white px-2.5 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Biaya Admin BUMDes (Rp)</label>
                                <input v-model.number="form.biaya_admin" type="number" min="0" class="w-full text-xs rounded-xl border border-emerald-200 bg-white px-2.5 py-1.5" />
                            </div>
                        </div>
                        <div class="pt-2 border-t border-emerald-200 flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-900">Total Yang Dibayar Konsumen:</span>
                            <span class="text-base font-black text-emerald-950">{{ formatRupiah(calculatedTotal) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Objek Pajak</label>
                        <input v-model="form.alamat_objek_pajak" type="text" placeholder="RT/RW Desa Ciwuni..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Transaksi</label>
                            <select v-model="form.status" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="SUCCES">SUCCES</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCEL">CANCEL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
                            <input v-model="form.keterangan" type="text" placeholder="Catatan transaksi..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-md shadow-emerald-600/20">
                            Simpan Transaksi PBB
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

