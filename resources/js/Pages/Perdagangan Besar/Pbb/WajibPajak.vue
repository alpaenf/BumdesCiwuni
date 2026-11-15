<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerdaganganBesarLayout from '@/Layouts/PerdaganganBesar/PerdaganganBesarLayout.vue';

const props = defineProps({
    unit: { type: Object, required: true },
    wajib_pajak: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const isModalOpen = ref(false);
const editingWp = ref(null);
const search = ref(props.filters.search || '');

const form = useForm({
    nama_wajib_pajak: '',
    nop: '',
    nik: '',
    nomor_hp: '',
    alamat_objek_pajak: '',
    alamat_wajib_pajak: '',
    nominal_sppt_terakhir: 0,
    keterangan: '',
});

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const applySearch = () => {
    router.get(route('pb.pbb.wajib-pajak.index'), {
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const openAddModal = () => {
    editingWp.value = null;
    form.reset();
    form.nominal_sppt_terakhir = 0;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingWp.value = item;
    form.nama_wajib_pajak = item.nama_wajib_pajak;
    form.nop = item.nop || '';
    form.nik = item.nik || '';
    form.nomor_hp = item.nomor_hp || '';
    form.alamat_objek_pajak = item.alamat_objek_pajak || '';
    form.alamat_wajib_pajak = item.alamat_wajib_pajak || '';
    form.nominal_sppt_terakhir = item.nominal_sppt_terakhir || 0;
    form.keterangan = item.keterangan || '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingWp.value) {
        form.put(route('pb.pbb.wajib-pajak.update', editingWp.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('pb.pbb.wajib-pajak.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteWp = (id) => {
    if (confirm('Yakin ingin menghapus data wajib pajak PBB ini?')) {
        router.delete(route('pb.pbb.wajib-pajak.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Database Wajib Pajak & Objek Pajak PBB" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 text-white shadow-lg">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-200 text-xs font-semibold uppercase tracking-wider mb-1">
                            <span class="material-symbols-outlined text-sm">home_work</span>
                            Master Wajib Pajak PBB Desa Ciwuni
                        </div>
                        <h1 class="text-2xl font-black tracking-tight">Database Objek & Wajib Pajak PBB</h1>
                        <p class="text-emerald-100 text-xs mt-1 max-w-xl">
                            Kelola data NOP (Nomor Objek Pajak) dan nama wajib pajak warga desa Ciwuni untuk kemudahan pencatatan dan penagihan PBB tahunan.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('pb.pbb.index')"
                            class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-xs font-semibold backdrop-blur-sm transition flex items-center gap-2 border border-white/20"
                        >
                            <span class="material-symbols-outlined text-sm">receipt_long</span>
                            Transaksi PBB
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Toolbar Action & Search -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative w-full">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                        <input
                            v-model="search"
                            @keyup.enter="applySearch"
                            type="text"
                            placeholder="Cari Nama, NOP, NIK, alamat..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition"
                        />
                    </div>
                    <button
                        @click="applySearch"
                        class="px-3.5 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition flex items-center gap-1"
                    >
                        <span>Cari</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="openAddModal"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-base">add</span>
                        Tambah Data NOP / Wajib Pajak
                    </button>
                </div>
            </div>

            <!-- Table Wajib Pajak -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-3 text-center" width="5%">No</th>
                                <th class="px-4 py-3">Nama Wajib Pajak</th>
                                <th class="px-4 py-3">Nomor Objek Pajak (NOP)</th>
                                <th class="px-4 py-3">Alamat Objek Pajak</th>
                                <th class="px-4 py-3 text-right">SPPT Terakhir</th>
                                <th class="px-4 py-3">No. HP</th>
                                <th class="px-4 py-3 text-center">Jml Bayar</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!wajib_pajak.data?.length">
                                <td colspan="8" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">home_work</span>
                                    <p>Belum ada data Wajib Pajak & NOP PBB.</p>
                                </td>
                            </tr>
                            <tr v-for="(item, idx) in wajib_pajak.data" :key="item.id" class="hover:bg-slate-50/75 transition">
                                <td class="px-4 py-3 text-center text-slate-400 text-[11px]">
                                    {{ (wajib_pajak.current_page - 1) * wajib_pajak.per_page + idx + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ item.nama_wajib_pajak }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">NIK: {{ item.nik || '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono font-black text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded text-xs border border-emerald-200 tracking-wider">
                                        {{ item.nop }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 max-w-xs truncate" :title="item.alamat_objek_pajak">
                                    {{ item.alamat_objek_pajak || '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                    {{ item.nominal_sppt_terakhir > 0 ? formatRupiah(item.nominal_sppt_terakhir) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ item.nomor_hp || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                        {{ item.transaksi_count || 0 }}x bayar
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-1 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition"
                                            title="Edit"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button
                                            @click="deleteWp(item.id)"
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
                <div v-if="wajib_pajak.links && wajib_pajak.links.length > 3" class="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Menampilkan <span class="font-bold">{{ wajib_pajak.from || 0 }}</span> - <span class="font-bold">{{ wajib_pajak.to || 0 }}</span> dari <span class="font-bold">{{ wajib_pajak.total }}</span> data
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in wajib_pajak.links"
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

        <!-- Modal Tambah / Edit Wajib Pajak PBB -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ editingWp ? 'Edit Wajib Pajak PBB' : 'Tambah Wajib Pajak & NOP PBB' }}</h3>
                        <p class="text-xs text-slate-500">Master database PBB Pajak Bumi dan Bangunan</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Wajib Pajak (SPPT) *</label>
                        <input v-model="form.nama_wajib_pajak" type="text" placeholder="Nama pemilik di SPPT..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Objek Pajak (NOP) *</label>
                            <input v-model="form.nop" type="text" placeholder="33.02.xxx.xxx.xxx-xxxx.x" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono font-bold" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nominal Pokok Terakhir (Rp)</label>
                            <input v-model.number="form.nominal_sppt_terakhir" type="number" min="0" placeholder="0" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">NIK Pemilik (Opsional)</label>
                            <input v-model="form.nik" type="text" placeholder="16 digit NIK..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                            <input v-model="form.nomor_hp" type="text" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Objek Pajak (Letak Tanah / Bangunan)</label>
                        <input v-model="form.alamat_objek_pajak" type="text" placeholder="RT 02 / RW 03 Desa Ciwuni..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Wajib Pajak (Tempat Tinggal)</label>
                        <input v-model="form.alamat_wajib_pajak" type="text" placeholder="Alamat domisili jika berbeda..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan (Opsional)</label>
                        <input v-model="form.keterangan" type="text" placeholder="Catatan tambahan..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-md shadow-emerald-600/20">
                            {{ editingWp ? 'Simpan Perubahan' : 'Tambah Wajib Pajak' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

