<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    biaya: { type: Object, required: true },
    total_biaya: { type: Number, default: 0 },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const periodMode = ref(props.filters.period_mode || 'month');
const filterDate = ref(props.filters.date || '');
const filterMonth = ref(props.filters.month || new Date().toISOString().substring(0, 7));
const filterKategori = ref(props.filters.kategori || '');

const kategoriOptions = [
    'Biaya Admin Bank',
    'Administrasi',
    'ATK (Alat Tulis Kantor)',
    'Honor / Insentif Petugas',
    'Listrik & Internet',
    'Perawatan & Perbaikan',
    'Lain-lain',
];

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    kategori_biaya: 'Administrasi',
    uraian: '',
    nominal: 0,
    keterangan: '',
    catat_ke_kas: true,
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const applyFilter = () => {
    router.get(route('pb.biaya.index'), {
        period_mode: periodMode.value,
        date: periodMode.value === 'date' ? (filterDate.value || undefined) : undefined,
        month: periodMode.value === 'month' ? (filterMonth.value || undefined) : undefined,
        kategori: filterKategori.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    periodMode.value = 'month';
    filterDate.value = '';
    filterMonth.value = new Date().toISOString().substring(0, 7);
    filterKategori.value = '';
    applyFilter();
};

const submitForm = () => {
    form.post(route('pb.biaya.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            form.tanggal = new Date().toISOString().split('T')[0];
            form.catat_ke_kas = true;
        },
    });
};

const deleteBiaya = (id) => {
    if (confirm('Yakin ingin menghapus catatan biaya operasional ini?')) {
        router.delete(route('pb.biaya.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Biaya Operasional Unit" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Header Banner Metric -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm md:col-span-2 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Pengeluaran Operasional (Bulan Terpilih)</span>
                        <h3 class="text-3xl font-black text-rose-600 mt-1">{{ formatRupiah(total_biaya) }}</h3>
                        <p class="text-xs text-slate-400 mt-1">Biaya ATK, honor petugas, utilitas, dan administrasi unit perdagangan besar.</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl">receipt_long</span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-5 rounded-2xl text-white shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider block">Efisiensi Kas</span>
                        <h4 class="text-sm font-semibold mt-1">Sinkronisasi Kas Otomatis</h4>
                        <p class="text-[11px] text-emerald-100 mt-1">Pengeluaran operasional dapat otomatis dicatat ke Kas Keluar untuk transparansi akuntansi.</p>
                    </div>
                    <button
                        @click="isModalOpen = true"
                        class="mt-3 px-4 py-2 bg-white text-emerald-800 rounded-xl text-xs font-bold hover:bg-emerald-50 transition flex items-center justify-center gap-1.5 shadow-sm"
                    >
                        <span class="material-symbols-outlined text-sm">add_circle</span>
                        <span>Catat Biaya Baru</span>
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                    <div class="flex flex-wrap items-center gap-2 flex-1 max-w-2xl">
                        <!-- Toggle Mode Periode -->
                        <div class="inline-flex rounded-xl p-0.5 bg-slate-100 border border-slate-200">
                            <button
                                type="button"
                                @click="periodMode = 'month'; applyFilter()"
                                :class="[
                                    'px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1',
                                    periodMode === 'month' ? 'bg-white text-rose-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">calendar_month</span>
                                <span>Per Bulan</span>
                            </button>
                            <button
                                type="button"
                                @click="periodMode = 'date'; applyFilter()"
                                :class="[
                                    'px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1',
                                    periodMode === 'date' ? 'bg-white text-rose-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">calendar_today</span>
                                <span>Per Tanggal</span>
                            </button>
                        </div>

                        <!-- Input Bulan / Tanggal -->
                        <input
                            v-if="periodMode === 'month'"
                            v-model="filterMonth"
                            type="month"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500"
                            @change="applyFilter"
                        />
                        <input
                            v-else
                            v-model="filterDate"
                            type="date"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500"
                            @change="applyFilter"
                        />

                        <select
                            v-model="filterKategori"
                            class="text-xs px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500"
                            @change="applyFilter"
                        >
                            <option value="">Semua Kategori Biaya</option>
                            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Tombol Cetak PDF -->
                        <a
                            :href="route('pb.biaya.cetak-pdf', {
                                period_mode: periodMode,
                                date: periodMode === 'date' ? filterDate : undefined,
                                month: periodMode === 'month' ? filterMonth : undefined,
                                kategori: filterKategori || undefined
                            })"
                            target="_blank"
                            class="px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                        >
                            <span class="material-symbols-outlined text-sm">print</span>
                            <span>Cetak PDF</span>
                        </a>

                        <button
                            @click="applyFilter"
                            class="px-3 py-2 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition"
                        >
                            Terapkan
                        </button>
                        <button
                            v-if="filterKategori || filterDate || filterMonth !== new Date().toISOString().substring(0, 7)"
                            @click="resetFilter"
                            class="px-3 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium hover:bg-slate-200 transition"
                        >
                            Reset
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
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Uraian Kebutuhan</th>
                                <th class="p-3 text-right">Nominal</th>
                                <th class="p-3">Keterangan</th>
                                <th class="p-3">Petugas</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="b in biaya.data" :key="b.id" class="hover:bg-slate-50/80 transition">
                                <td class="p-3 whitespace-nowrap font-medium text-slate-800">
                                    {{ b.tanggal }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border">
                                        {{ b.kategori_biaya }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold text-slate-900">
                                    {{ b.uraian }}
                                </td>
                                <td class="p-3 text-right font-black text-rose-600 whitespace-nowrap">
                                    {{ formatRupiah(b.nominal) }}
                                </td>
                                <td class="p-3 text-slate-500">
                                    {{ b.keterangan || '-' }}
                                </td>
                                <td class="p-3 whitespace-nowrap text-slate-500">
                                    {{ b.nama_admin || 'Admin' }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button
                                        @click="deleteBiaya(b.id)"
                                        class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition"
                                        title="Hapus Biaya"
                                    >
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!biaya.data || biaya.data.length === 0">
                                <td colspan="7" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                                    <p>Tidak ada pengeluaran operasional pada periode ini.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="biaya.links && biaya.links.length > 3" class="p-3 border-t border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <p class="text-[11px] text-slate-500">
                        Menampilkan {{ biaya.from || 0 }} - {{ biaya.to || 0 }} dari {{ biaya.total || 0 }} catatan
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in biaya.links"
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

        <!-- Modal Tambah Biaya Operasional -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800">Catat Biaya Operasional</h3>
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

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kategori Biaya</label>
                        <select
                            v-model="form.kategori_biaya"
                            required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                            <option v-for="opt in kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Uraian Kebutuhan</label>
                        <input
                            v-model="form.uraian"
                            type="text"
                            required
                            placeholder="Contoh: Kertas HVS & Tinta Printer"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input
                            v-model.number="form.nominal"
                            type="number"
                            min="1"
                            required
                            placeholder="Contoh: 75000"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-slate-800"
                        />
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

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2">
                        <input
                            id="catat_ke_kas"
                            v-model="form.catat_ke_kas"
                            type="checkbox"
                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        />
                        <label for="catat_ke_kas" class="text-xs text-slate-700 cursor-pointer">
                            Otomatis catat ke <span class="font-bold text-slate-900">Buku Kas Keluar (Tunai)</span>
                        </label>
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
                            <span>Simpan Biaya</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>
