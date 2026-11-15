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
    nik: '',
    alamat_lengkap: '',
    nomor_hp: '',
    jenis_kendaraan: 'Sepeda Motor',
    nomor_plat: '',
    merek_tipe: '',
    tahun_kendaraan: '',
    tanggal_jatuh_tempo: '',
});

const applySearch = () => {
    router.get(route('pb.samsat.wajib-pajak.index'), {
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const openAddModal = () => {
    editingWp.value = null;
    form.reset();
    form.jenis_kendaraan = 'Sepeda Motor';
    form.tanggal_jatuh_tempo = '';
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingWp.value = item;
    form.nama_wajib_pajak = item.nama_wajib_pajak;
    form.nik = item.nik || '';
    form.alamat_lengkap = item.alamat_lengkap || '';
    form.nomor_hp = item.nomor_hp || '';
    form.jenis_kendaraan = item.jenis_kendaraan || 'Sepeda Motor';
    form.nomor_plat = item.nomor_plat || '';
    form.merek_tipe = item.merek_tipe || '';
    form.tahun_kendaraan = item.tahun_kendaraan || '';
    form.tanggal_jatuh_tempo = item.tanggal_jatuh_tempo ? item.tanggal_jatuh_tempo.split('T')[0] : '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingWp.value) {
        form.put(route('pb.samsat.wajib-pajak.update', editingWp.value.id), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('pb.samsat.wajib-pajak.store'), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteWp = (id) => {
    if (confirm('Yakin ingin menghapus data wajib pajak ini?')) {
        router.delete(route('pb.samsat.wajib-pajak.destroy', id));
    }
};
</script>

<template>
    <PerdaganganBesarLayout title="Database Wajib Pajak & Kendaraan" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Header & Action Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="applySearch"
                        type="text"
                        placeholder="Cari nama, plat nomor, NIK, merek..."
                        class="text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 w-72 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <button
                        @click="applySearch"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                    >
                        Cari
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('pb.samsat.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                    >
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Kembali ke Transaksi
                    </Link>
                    <button
                        @click="openAddModal"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-amber-600/20 transition"
                    >
                        <span class="material-symbols-outlined text-base">add</span>
                        Tambah Data Kendaraan / WP
                    </button>
                </div>
            </div>

            <!-- Table Wajib Pajak -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-3 text-center" width="5%">No</th>
                                <th class="px-4 py-3">Nama Wajib Pajak</th>
                                <th class="px-4 py-3">Nomor Plat</th>
                                <th class="px-4 py-3">Merek / Tipe</th>
                                <th class="px-4 py-3">Jatuh Tempo</th>
                                <th class="px-4 py-3">Jenis & Tahun</th>
                                <th class="px-4 py-3">No. HP</th>
                                <th class="px-4 py-3 text-center">Jml Trx</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!wajib_pajak.data?.length">
                                <td colspan="9" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">directions_car</span>
                                    <p>Belum ada data Wajib Pajak & Kendaraan.</p>
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
                                    <span class="font-mono font-black text-amber-800 bg-amber-50 px-2 py-0.5 rounded text-xs border border-amber-200">
                                        {{ item.nomor_plat }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-800 font-semibold">{{ item.merek_tipe || '-' }}</td>
                                <td class="px-4 py-3 font-medium">
                                    <span v-if="item.tanggal_jatuh_tempo" class="text-slate-800">
                                        {{ item.tanggal_jatuh_tempo.split('T')[0] }}
                                    </span>
                                    <span v-else class="text-slate-400 italic text-[11px]">Belum diatur</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <div>{{ item.jenis_kendaraan }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Thn {{ item.tahun_kendaraan || '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ item.nomor_hp || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">
                                        {{ item.transaksi_count || 0 }} trx
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-1 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition"
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
                <div v-if="wajib_pajak.links && wajib_pajak.links.length > 3" class="p-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>Menampilkan {{ wajib_pajak.from || 0 }} - {{ wajib_pajak.to || 0 }} dari {{ wajib_pajak.total || 0 }} data</div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in wajib_pajak.links"
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

        <!-- Modal Tambah / Edit Wajib Pajak -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ editingWp ? 'Edit Data Wajib Pajak' : 'Tambah Data Wajib Pajak & Kendaraan' }}</h3>
                        <p class="text-xs text-slate-500">Database kendaraan bermotor Samsat Budiman</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Wajib Pajak *</label>
                        <input v-model="form.nama_wajib_pajak" type="text" placeholder="Nama pemilik kendaraan..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">NIK Pemilik</label>
                            <input v-model="form.nik" type="text" placeholder="16 digit NIK..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor HP</label>
                            <input v-model="form.nomor_hp" type="text" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Plat (Nopol) *</label>
                            <input v-model="form.nomor_plat" type="text" placeholder="R 1234 AB" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 uppercase font-mono font-bold" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Kendaraan *</label>
                            <select v-model="form.jenis_kendaraan" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                <option value="Sepeda Motor">Sepeda Motor</option>
                                <option value="Mobil Penumpang">Mobil Penumpang</option>
                                <option value="Truk / Pick Up">Truk / Pick Up</option>
                                <option value="Kendaraan Lainnya">Kendaraan Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merek / Tipe Kendaraan</label>
                            <input v-model="form.merek_tipe" type="text" placeholder="Honda Beat / Toyota Avanza..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Perakitan</label>
                            <input v-model="form.tahun_kendaraan" type="text" placeholder="Contoh: 2021" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Jatuh Tempo STNK / Pajak</label>
                        <input v-model="form.tanggal_jatuh_tempo" type="date" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Tanggal jatuh tempo tahunan untuk memicu peringatan keterlambatan / denda otomatis saat input transaksi.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea v-model="form.alamat_lengkap" rows="2" placeholder="Alamat wajib pajak..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold shadow-md shadow-amber-600/20">
                            {{ editingWp ? 'Simpan Perubahan' : 'Tambah Wajib Pajak' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

