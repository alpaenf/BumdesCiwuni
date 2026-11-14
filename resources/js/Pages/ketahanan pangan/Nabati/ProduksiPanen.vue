<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    productions: Object,
    commodities: Array,
});

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    commodity_id: props.commodities.length > 0 ? props.commodities[0].id : '',
    jumlah_panen: 100,
    satuan: props.commodities.length > 0 ? props.commodities[0].satuan : 'kg',
    lokasi_lahan: 'Lahan Kas Desa Blok Barat',
    keterangan: '',
});

const onSelectCommodity = (e) => {
    const commId = e.target.value;
    const found = props.commodities.find(c => c.id == commId);
    if (found) {
        form.value.satuan = found.satuan;
    }
};

const submitForm = () => {
    router.post(route('kp.nabati.produksi.store'), form.value, {
        onSuccess: () => { isModalOpen.value = false; }
    });
};
</script>

<template>
    <KetahananPanganLayout title="Pencatatan Panen Nabati" :unit="unit" :user="user">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Pencatatan Hasil Panen Nabati</h3>
                <p class="text-xs text-slate-500">Mencatat hasil panen tanaman pangan dan menambah persediaan hasil panen otomatis</p>
            </div>
            <button 
                @click="isModalOpen = true"
                class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-base">add</span>
                Catat Panen Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">Tanggal Panen</th>
                            <th class="p-3.5">Komoditas</th>
                            <th class="p-3.5 text-right">Jumlah Panen</th>
                            <th class="p-3.5">Lokasi Lahan</th>
                            <th class="p-3.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="p in productions.data" :key="p.id" class="hover:bg-slate-50/50">
                            <td class="p-3.5 text-slate-600 font-medium">{{ p.tanggal }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ p.commodity?.nama }}</td>
                            <td class="p-3.5 text-right font-extrabold text-teal-700">{{ Number(p.jumlah_panen).toFixed(1) }} {{ p.satuan }}</td>
                            <td class="p-3.5 text-slate-700">{{ p.lokasi_lahan || '-' }}</td>
                            <td class="p-3.5 text-slate-500">{{ p.keterangan || '-' }}</td>
                        </tr>
                        <tr v-if="productions.data.length === 0">
                            <td colspan="5" class="text-center py-8 text-slate-400">
                                Belum ada riwayat hasil panen yang tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Hasil Panen -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Input Hasil Panen</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Panen</label>
                        <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Komoditas</label>
                        <select v-model="form.commodity_id" @change="onSelectCommodity" class="w-full text-xs rounded-xl border-slate-200" required>
                            <option v-for="c in commodities" :key="c.id" :value="c.id">{{ c.nama }} ({{ c.satuan }})</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Panen</label>
                            <input type="number" step="0.1" v-model="form.jumlah_panen" class="w-full text-xs rounded-xl border-slate-200 font-bold text-teal-700" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan</label>
                            <input type="text" v-model="form.satuan" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Lahan / Blok</label>
                        <input type="text" v-model="form.lokasi_lahan" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl">Simpan Panen</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

