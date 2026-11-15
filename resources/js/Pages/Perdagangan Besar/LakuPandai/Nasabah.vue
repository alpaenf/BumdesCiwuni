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

const form = useForm({
    nama: '',
    no_hp: '',
    no_rekening: '',
    nama_bank: '',
    alamat: '',
    keterangan: '',
});

const applySearch = () => {
    router.get(route('pb.laku-pandai.nasabah.index'), {
        search: search.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const openAddModal = () => {
    editingNasabah.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    editingNasabah.value = item;
    form.nama = item.nama;
    form.no_hp = item.no_hp || '';
    form.no_rekening = item.no_rekening || '';
    form.nama_bank = item.nama_bank || '';
    form.alamat = item.alamat || '';
    form.keterangan = item.keterangan || '';
    isModalOpen.value = true;
};

const submitForm = () => {
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
    <PerdaganganBesarLayout title="Data Nasabah Laku Pandai" :unit="unit">
        <div class="space-y-5 max-w-7xl mx-auto">
            <!-- Header & Action Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1">
                    <input
                        v-model="search"
                        @keyup.enter="applySearch"
                        type="text"
                        placeholder="Cari nama, no. HP, no. rekening..."
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
                        :href="route('pb.laku-pandai.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition"
                    >
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Kembali ke Transaksi
                    </Link>
                    <button
                        @click="openAddModal"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-blue-600/20 transition"
                    >
                        <span class="material-symbols-outlined text-base">person_add</span>
                        Tambah Nasabah Baru
                    </button>
                </div>
            </div>

            <!-- Table Nasabah -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-3 text-center" width="5%">No</th>
                                <th class="px-4 py-3">Nama Nasabah / Pelanggan</th>
                                <th class="px-4 py-3">No. HP / WA</th>
                                <th class="px-4 py-3">Lembaga / Provider & ID / No. Rek</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="px-4 py-3 text-center">Jml Transaksi</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="!nasabah.data?.length">
                                <td colspan="7" class="text-center py-8 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">group_off</span>
                                    <p>Belum ada data nasabah Laku Pandai.</p>
                                </td>
                            </tr>
                            <tr v-for="(item, idx) in nasabah.data" :key="item.id" class="hover:bg-slate-50/75 transition">
                                <td class="px-4 py-3 text-center text-slate-400 text-[11px]">
                                    {{ (nasabah.current_page - 1) * nasabah.per_page + idx + 1 }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ item.nama }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.no_hp || '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded inline-block mb-0.5">{{ item.nama_bank || 'Umum / Lainnya' }}</div>
                                    <div class="font-mono text-slate-800 font-bold">{{ item.no_rekening || '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ item.alamat || '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px]">
                                        {{ item.transaksi_count || 0 }} trx
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button
                                            @click="openEditModal(item)"
                                            class="p-1 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                                            title="Edit"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button
                                            @click="deleteNasabah(item.id)"
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

        <!-- Modal Tambah / Edit Nasabah -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ editingNasabah ? 'Edit Data Nasabah' : 'Tambah Nasabah Baru' }}</h3>
                        <p class="text-xs text-slate-500">Master identitas nasabah agen keuangan Laku Pandai</p>
                    </div>
                    <button @click="isModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-4 sm:p-5 space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Nasabah *</label>
                        <input v-model="form.nama" type="text" placeholder="Masukkan nama nasabah..." required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                            <input v-model="form.no_hp" type="text" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Bank / Provider / Lembaga</label>
                            <input v-model="form.nama_bank" type="text" placeholder="Contoh: PLN, PDAM, IndiHome, BRI, Pamsimas..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Rekening / No. Meter / ID Pelanggan</label>
                        <input v-model="form.no_rekening" type="text" placeholder="Masukkan nomor rekening, ID pelanggan, atau no. meter..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Alamat Nasabah</label>
                        <textarea v-model="form.alamat" rows="2" placeholder="Alamat atau RT/RW..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                        <input v-model="form.keterangan" type="text" placeholder="Catatan nasabah..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-md shadow-blue-600/20">
                            {{ editingNasabah ? 'Simpan Perubahan' : 'Tambah Nasabah' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PerdaganganBesarLayout>
</template>

