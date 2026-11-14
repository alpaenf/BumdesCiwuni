<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    commodities: Object,
});

const isModalOpen = ref(false);
const form = ref({
    kode: 'KMD-' + Math.floor(100 + Math.random() * 900),
    nama: '',
    satuan: 'kg',
    harga_default: 15000,
    stok_tersedia: 0,
    keterangan: '',
});

const submitForm = () => {
    router.post(route('kp.nabati.komoditas.store'), form.value, {
        onSuccess: () => { 
            isModalOpen.value = false;
            form.value.kode = 'KMD-' + Math.floor(100 + Math.random() * 900);
            form.value.nama = '';
        }
    });
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Data Komoditas Nabati" :unit="unit" :user="user">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Master Data Komoditas Tanaman Pangan</h3>
                <p class="text-xs text-slate-500">Kelola daftar varietas komoditas hasil tani dan tanaman pangan BUMDes Ciwuni</p>
            </div>
            <button 
                @click="isModalOpen = true"
                class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-base">add</span>
                Tambah Komoditas Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">Kode</th>
                            <th class="p-3.5">Nama Komoditas</th>
                            <th class="p-3.5">Satuan</th>
                            <th class="p-3.5 text-right">Harga Jual Acuan</th>
                            <th class="p-3.5 text-right">Stok Gudang</th>
                            <th class="p-3.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="c in commodities.data" :key="c.id" class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-700">{{ c.kode }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ c.nama }}</td>
                            <td class="p-3.5 text-slate-600">{{ c.satuan }}</td>
                            <td class="p-3.5 text-right text-slate-700">{{ formatRp(c.harga_default) }}</td>
                            <td class="p-3.5 text-right font-extrabold text-teal-700">{{ Number(c.stok_tersedia).toFixed(1) }} {{ c.satuan }}</td>
                            <td class="p-3.5 text-slate-500">{{ c.keterangan || '-' }}</td>
                        </tr>
                        <tr v-if="commodities.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                Belum ada data komoditas nabati yang terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Komoditas -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Tambah Komoditas Nabati</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Komoditas</label>
                        <input type="text" v-model="form.kode" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Komoditas</label>
                        <input type="text" v-model="form.nama" placeholder="Cth: Beras Pandan Wangi / Jagung Manis" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan</label>
                            <input type="text" v-model="form.satuan" placeholder="kg, ikat, kuintal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Acuan</label>
                            <input type="number" v-model="form.harga_default" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Awal</label>
                        <input type="number" step="0.1" v-model="form.stok_tersedia" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl">Simpan Komoditas</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

