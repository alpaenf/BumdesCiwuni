<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    transaksi: { type: Object, required: true },
    summary: { type: Object, default: () => ({}) },
    wp_list: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const search = ref(props.filters.search || '');
const filterDate = ref(props.filters.date || '');
const filterStatus = ref(props.filters.status || '');
const filterKendaraan = ref(props.filters.jenis_kendaraan || '');

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    tanggal_jatuh_tempo: '',
    wajib_pajak_id: null,
    nama_wajib_pajak: '',
    nomor_polisi: '',
    jenis_kendaraan: 'Sepeda Motor',
    jenis_pembayaran: 'Cash',
    nominal_pajak: 0,
    denda_pajak: 0,
    biaya_admin: 0,
    biaya_jasa_bumdes: 10000,
    status_transaksi: 'SUCCES',
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

// Hitung keterlambatan (bulan) & estimasi denda otomatis
const keterlambatanInfo = computed(() => {
    if (!form.tanggal_jatuh_tempo || !form.tanggal) {
        return { isTelat: false, bulanTelat: 0, teks: 'Tepat Waktu' };
    }
    const tglTrx = new Date(form.tanggal);
    const tglTempo = new Date(form.tanggal_jatuh_tempo);

    if (tglTrx <= tglTempo) {
        return { isTelat: false, bulanTelat: 0, teks: 'Tepat Waktu' };
    }

    // Hitung selisih hari & bulan
    const diffTime = tglTrx.getTime() - tglTempo.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const bulanTelat = Math.min(24, Math.max(1, Math.ceil(diffDays / 30)));

    return {
        isTelat: true,
        bulanTelat,
        diffDays,
        teks: `Terlambat ${bulanTelat} Bulan (${diffDays} hari)`
    };
});

// Auto-suggest denda saat tanggal jatuh tempo / nominal pajak berubah
const hitungEstimasiDenda = () => {
    if (keterlambatanInfo.value.isTelat) {
        const pokok = Number(form.nominal_pajak) || 0;
        // Rumus PKB Jateng/standar: 2% per bulan telat (maksimal 24 bulan = 48%)
        const estimasiDendaPkb = Math.round(pokok * (keterlambatanInfo.value.bulanTelat * 0.02));
        form.denda_pajak = estimasiDendaPkb;
    } else {
        form.denda_pajak = 0;
    }
};

const calculatedTotal = computed(() => {
    const pajak = Number(form.nominal_pajak) || 0;
    const denda = Number(form.denda_pajak) || 0;
    const admin = Number(form.biaya_admin) || 0;
    const jasa = Number(form.biaya_jasa_bumdes) || 0;
    return pajak + denda + admin + jasa;
});

const onSelectWp = (e) => {
    const selectedId = e.target.value;
    if (!selectedId) return;
    const wp = props.wp_list.find(item => item.id == selectedId);
    if (wp) {
        form.wajib_pajak_id = wp.id;
        form.nama_wajib_pajak = wp.nama_wajib_pajak;
        form.nomor_polisi = wp.nomor_plat;
        form.jenis_kendaraan = wp.jenis_kendaraan;
        if (wp.tanggal_jatuh_tempo) {
            form.tanggal_jatuh_tempo = wp.tanggal_jatuh_tempo.split('T')[0];
            hitungEstimasiDenda();
        }
    }
};

const applyFilter = () => {
    router.get(route('pb.samsat.index'), {
        search: search.value || undefined,
        date: filterDate.value || undefined,
        status: filterStatus.value || undefined,
        jenis_kendaraan: filterKendaraan.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    search.value = '';
    filterDate.value = '';
    filterStatus.value = '';
    filterKendaraan.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.samsat.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
            form.tanggal_jatuh_tempo = '';
            form.denda_pajak = 0;
            form.biaya_jasa_bumdes = 10000;
        },
    });
};

const deleteTransaksi = (id) => {
    if (confirm('Yakin ingin menghapus catatan transaksi Samsat ini?')) {
        router.delete(route('pb.samsat.destroy', id));
    }
};

const openStruk = (id) => {
    window.open(route('pb.samsat.struk', id), '_blank', 'width=420,height=600');
};
</script>

<template>
    <PerdaganganBesarLayout title="Samsat Budiman" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Transaksi Pajak</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ (summary.total_transaksi || 0).toLocaleString() }}</h4>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-semibold">{{ summary.total_sukses || 0 }} transaksi berhasil</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nominal Pajak Disetor</p>
                    <h4 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(summary.total_nominal_pajak) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pokok PKB wajib pajak</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pendapatan Jasa BUMDes</p>
                    <h4 class="text-xl font-black text-emerald-600 mt-1">{{ formatRupiah(summary.total_jasa_bumdes) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Komisi jasa layanan Samsat</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Penerimaan Kasir</p>
                    <h4 class="text-xl font-black text-blue-600 mt-1">{{ formatRupiah(summary.total_pembayaran) }}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pajak + Admin + Jasa BUMDes</p>
                </div>
            </div>

            <!-- Action Bar & Filters -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari WP, plat nomor, no. trx..."
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 w-56 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <input
                        v-model="filterDate"
                        @change="applyFilter"
                        type="date"
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
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

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('pb.samsat.wajib-pajak.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                    >
                        <span class="material-symbols-outlined text-base">badge</span>
                        Database Wajib Pajak
                    </Link>
                    <button
                        @click="isModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-amber-600/20 transition"
                    >
                        <span class="material-symbols-outlined text-base">add</span>
                        Input Transaksi Samsat
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
                                <th class="px-3 py-3">Wajib Pajak</th>
                                <th class="px-3 py-3">Plat / Kendaraan</th>
                                <th class="px-3 py-3 text-center">Metode</th>
                                <th class="px-3 py-3 text-right">Nominal Pajak</th>
                                <th class="px-3 py-3 text-right">Denda</th>
                                <th class="px-3 py-3 text-right">Biaya Admin</th>
                                <th class="px-3 py-3 text-right text-emerald-600 font-bold">Jasa BUMDes</th>
                                <th class="px-3 py-3 text-right font-bold text-slate-900">Total Bayar</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3">Admin</th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!transaksi.data?.length">
                                <td colspan="13" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">directions_car</span>
                                    <p>Belum ada data transaksi Samsat Budiman.</p>
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
                                <td class="px-3 py-2.5 font-bold text-slate-800">{{ item.nama_wajib_pajak }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="font-mono font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded text-[11px] border border-amber-200">
                                        {{ item.nomor_polisi }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 ml-1.5">{{ item.jenis_kendaraan }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ item.jenis_pembayaran }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-slate-700">
                                    <div>{{ formatRupiah(item.nominal_pajak) }}</div>
                                    <div v-if="item.tanggal_jatuh_tempo" class="text-[9px] text-slate-400">
                                        JT: {{ item.tanggal_jatuh_tempo }}
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <span v-if="item.denda_pajak > 0" class="font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                                        {{ formatRupiah(item.denda_pajak) }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-3 py-2.5 text-right text-slate-500">{{ formatRupiah(item.biaya_admin) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-600">{{ formatRupiah(item.biaya_jasa_bumdes) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-900">{{ formatRupiah(item.total_pembayaran) }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <span :class="[
                                        'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase',
                                        item.status_transaksi === 'SUCCES' ? 'bg-emerald-100 text-emerald-700' :
                                        item.status_transaksi === 'PENDING' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'
                                    ]">
                                        {{ item.status_transaksi }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-slate-500 text-[11px]">{{ item.nama_admin }}</td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            @click="openStruk(item.id)"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold border border-emerald-200 transition"
                                            title="Cetak Struk Samsat"
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
                                link.active ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url && 'opacity-40 pointer-events-none'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Input Transaksi Samsat -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Input Transaksi Samsat Budiman</h3>
                        <p class="text-xs text-slate-500">Pembayaran Pajak Kendaraan Bermotor (PKB)</p>
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
                            <label class="block font-bold text-slate-700 mb-1">Pilih dari Database WP (Opsional)</label>
                            <select @change="onSelectWp" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <option v-for="wp in wp_list" :key="wp.id" :value="wp.id">
                                    {{ wp.nama_wajib_pajak }} - {{ wp.nomor_plat }} ({{ wp.jenis_kendaraan }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Wajib Pajak *</label>
                            <input v-model="form.nama_wajib_pajak" type="text" placeholder="Nama pemilik di STNK..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Polisi (Plat) *</label>
                            <input v-model="form.nomor_polisi" type="text" placeholder="R 1234 AB" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 uppercase font-mono font-bold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Kendaraan</label>
                            <select v-model="form.jenis_kendaraan" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="Sepeda Motor">Sepeda Motor</option>
                                <option value="Mobil Penumpang">Mobil Penumpang</option>
                                <option value="Truk / Pick Up">Truk / Pick Up</option>
                                <option value="Kendaraan Lainnya">Kendaraan Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Pembayaran</label>
                            <select v-model="form.jenis_pembayaran" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>

                    <!-- Komponen Biaya & Status Keterlambatan -->
                    <div class="p-3.5 bg-amber-50/60 rounded-2xl border border-amber-200 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-200/80 pb-2.5">
                            <div>
                                <span class="font-bold text-amber-900 text-[11px] uppercase tracking-wider block">Rincian Komponen Biaya</span>
                                <span class="text-[10px] text-amber-700">Estimasi denda dihitung otomatis berdasarkan tanggal jatuh tempo</span>
                            </div>
                            <!-- Badge Status Keterlambatan -->
                            <div v-if="form.tanggal_jatuh_tempo">
                                <span v-if="keterlambatanInfo.isTelat" class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-700 font-bold rounded-lg text-[10px] border border-rose-300">
                                    <span class="material-symbols-outlined text-xs">warning</span>
                                    {{ keterlambatanInfo.teks }}
                                </span>
                                <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-lg text-[10px] border border-emerald-300">
                                    <span class="material-symbols-outlined text-xs">check_circle</span>
                                    Tepat Waktu (Tidak Ada Denda)
                                </span>
                            </div>
                        </div>

                        <!-- Tanggal Jatuh Tempo Input -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-0.5">Tanggal Jatuh Tempo STNK / Pajak</label>
                                <input
                                    v-model="form.tanggal_jatuh_tempo"
                                    type="date"
                                    @change="hitungEstimasiDenda"
                                    class="w-full text-xs rounded-xl border border-amber-200 bg-white px-2.5 py-1.5 font-medium"
                                />
                                <span class="text-[9px] text-slate-400">Diisi jika ingin deteksi denda otomatis</span>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-rose-700 mb-0.5">Denda Keterlambatan Pajak (Rp)</label>
                                <div class="relative">
                                    <input
                                        v-model.number="form.denda_pajak"
                                        type="number"
                                        min="0"
                                        class="w-full text-xs rounded-xl border border-rose-300 bg-white px-2.5 py-1.5 font-bold text-rose-700 focus:ring-rose-500"
                                    />
                                    <button
                                        type="button"
                                        @click="hitungEstimasiDenda"
                                        class="absolute right-1 top-1 bottom-1 px-2 text-[9px] font-semibold bg-rose-100 text-rose-700 rounded-lg hover:bg-rose-200"
                                        title="Hitung Ulang Estimasi Denda"
                                    >
                                        Auto Hitung
                                    </button>
                                </div>
                                <span class="text-[9px] text-slate-400">Bisa disesuaikan manual sesuai cetakan tagihan Samsat</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Nominal Pokok Pajak (PKB) *</label>
                                <input
                                    v-model.number="form.nominal_pajak"
                                    type="number"
                                    min="0"
                                    @input="hitungEstimasiDenda"
                                    required
                                    class="w-full text-xs rounded-xl border border-amber-200 bg-white px-2.5 py-1.5"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Biaya Admin</label>
                                <input v-model.number="form.biaya_admin" type="number" min="0" class="w-full text-xs rounded-xl border border-amber-200 bg-white px-2.5 py-1.5" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Biaya Jasa BUMDes</label>
                                <input v-model.number="form.biaya_jasa_bumdes" type="number" min="0" class="w-full text-xs rounded-xl border border-amber-200 bg-white px-2.5 py-1.5" />
                            </div>
                        </div>

                        <div class="pt-2 border-t border-amber-200 flex items-center justify-between text-xs">
                            <span class="font-bold text-amber-900">Total Pembayaran Konsumen:</span>
                            <span class="text-base font-black text-amber-950">{{ formatRupiah(calculatedTotal) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Transaksi</label>
                            <select v-model="form.status_transaksi" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="SUCCES">SUCCES</option>
                                <option value="PENDING">PENDING</option>
                                <option value="CANCEL">CANCEL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                            <input v-model="form.keterangan" type="text" placeholder="Catatan transaksi..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold shadow-md shadow-amber-600/20">
                            Simpan Transaksi Samsat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

