<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    sales: Object,
    commodities: Array,
});

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    nama_pelanggan: '',
    commodity_id: props.commodities.length > 0 ? props.commodities[0].id : '',
    jumlah: 10,
    satuan: props.commodities.length > 0 ? props.commodities[0].satuan : 'kg',
    harga_satuan: props.commodities.length > 0 ? props.commodities[0].harga_default : 15000,
    jumlah_pembayaran: 150000,
    metode_pembayaran: 'Tunai',
    keterangan: '',
});

const onSelectCommodity = (e) => {
    const cid = e.target.value;
    const found = props.commodities.find(c => c.id == cid);
    if (found) {
        form.value.satuan = found.satuan;
        form.value.harga_satuan = found.harga_default;
        updateTotal();
    }
};

const updateTotal = () => {
    form.value.jumlah_pembayaran = Math.round(form.value.jumlah * form.value.harga_satuan);
};

const submitForm = () => {
    router.post(route('kp.nabati.penjualan.store'), form.value, {
        onSuccess: () => { isModalOpen.value = false; }
    });
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Penjualan Hasil Panen Nabati" :unit="unit" :user="user">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Transaksi Penjualan Komoditas Nabati</h3>
                <p class="text-xs text-slate-500">Pencatatan penjualan hasil pertanian dan pengurangan stok gudang otomatis</p>
            </div>
            <button 
                @click="isModalOpen = true"
                class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-base">point_of_sale</span>
                Penjualan Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">No. Transaksi</th>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5">Pelanggan</th>
                            <th class="p-3.5">Komoditas</th>
                            <th class="p-3.5 text-right">Qty</th>
                            <th class="p-3.5 text-right">Harga Satuan</th>
                            <th class="p-3.5 text-right">Total Transaksi</th>
                            <th class="p-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="s in sales.data" :key="s.id" class="hover:bg-slate-50/50">
                            <td class="p-3.5 font-mono font-bold text-slate-700">{{ s.nomor_transaksi }}</td>
                            <td class="p-3.5 text-slate-600">{{ s.tanggal }}</td>
                            <td class="p-3.5 font-bold text-slate-800">{{ s.nama_pelanggan }}</td>
                            <td class="p-3.5 font-medium text-slate-700">{{ s.commodity?.nama }}</td>
                            <td class="p-3.5 text-right font-bold text-slate-800">{{ Number(s.jumlah).toFixed(1) }} {{ s.satuan }}</td>
                            <td class="p-3.5 text-right text-slate-600">{{ formatRp(s.harga_satuan) }}</td>
                            <td class="p-3.5 text-right font-extrabold text-teal-700">{{ formatRp(s.total_harga) }}</td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ s.status_pembayaran }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                Belum ada transaksi penjualan hasil panen.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Penjualan Nabati -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Kasir Penjualan Nabati</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pembeli</label>
                            <input type="text" v-model="form.nama_pelanggan" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Komoditas</label>
                        <select v-model="form.commodity_id" @change="onSelectCommodity" class="w-full text-xs rounded-xl border-slate-200" required>
                            <option v-for="c in commodities" :key="c.id" :value="c.id">{{ c.nama }} (Stok: {{ c.stok_tersedia }} {{ c.satuan }})</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah</label>
                            <input type="number" step="0.1" v-model="form.jumlah" @input="updateTotal" class="w-full text-xs rounded-xl border-slate-200 font-bold" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Satuan</label>
                            <input type="number" v-model="form.harga_satuan" @input="updateTotal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div class="p-3 bg-teal-50 rounded-xl border border-teal-100 flex items-center justify-between text-teal-900 font-bold text-xs">
                        <span>Total Tagihan:</span>
                        <span class="text-base text-teal-700">{{ formatRp(form.jumlah * form.harga_satuan) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Uang Diterima</label>
                            <input type="number" v-model="form.jumlah_pembayaran" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode</label>
                            <select v-model="form.metode_pembayaran" class="w-full text-xs rounded-xl border-slate-200" required>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Qris">QRIS</option>
                                <option value="Hutang">Hutang</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

