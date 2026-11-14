<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    activeTab: String,
    feedStock: Object,
    usages: Object,
    purchases: Object,
    filters: Object,
    summary: Object,
});

const currentTab = ref(props.activeTab || 'pemakaian');
const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const switchTab = (tab) => {
    currentTab.value = tab;
    router.get(route('kp.feed.index'), {
        tab: tab,
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const applyFilter = () => {
    router.get(route('kp.feed.index'), {
        tab: currentTab.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

// Modal Pembelian
const isPurchaseModalOpen = ref(false);
const purchaseForm = ref({
    tanggal: new Date().toISOString().split('T')[0],
    nama_pakan: 'Pakan Layer SP-100',
    jumlah_kg: 50,
    harga_per_kg: 8500,
    supplier: '',
    keterangan: '',
});

const submitPurchase = () => {
    router.post(route('kp.feed.purchase.store'), purchaseForm.value, {
        onSuccess: () => { isPurchaseModalOpen.value = false; }
    });
};

const deletePurchase = (id) => {
    if (confirm('Batalkan pembelian ini? Stok pakan dan pengeluaran terkait akan di-rollback.')) {
        router.delete(route('kp.feed.purchase.destroy', id));
    }
};

// Modal Pemakaian Harian
const isUsageModalOpen = ref(false);
const usageForm = ref({
    tanggal: new Date().toISOString().split('T')[0],
    hari: '',
    hari_ke: 1,
    umur_minggu_ke: 24,
    umur_minggu_hari_ke: 1,
    populasi_ayam: 1000,
    kebutuhan_per_ekor_gram: 115,
    harga_per_kg: 8500,
    keterangan: '',
});

const submitUsage = () => {
    router.post(route('kp.feed.usage.store'), usageForm.value, {
        onSuccess: () => { isUsageModalOpen.value = false; }
    });
};

const deleteUsage = (id) => {
    if (confirm('Hapus pencatatan pemakaian ini? Stok pakan akan dikembalikan.')) {
        router.delete(route('kp.feed.usage.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Logistik Pakan Ayam Petelur" :unit="unit" :user="user">
        <!-- Summary Cards Sesuai PRD -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Stok Pakan Saat Ini</p>
                <h4 class="text-2xl font-bold text-purple-700 mt-1">
                    {{ Number(summary.stok_saat_ini_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-slate-400">Pakan Layer Utama Tersedia</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Pembelian (Periode)</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ Number(summary.total_beli_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ formatRp(summary.total_biaya_beli) }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Pemakaian (Periode)</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ Number(summary.total_pakai_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-amber-600 font-medium">Konsumsi harian populasi</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-center items-start gap-2">
                <button 
                    @click="isUsageModalOpen = true"
                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">edit_calendar</span>
                    Catat Pemakaian Harian
                </button>
                <button 
                    @click="isPurchaseModalOpen = true"
                    class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                    Beli Pakan Baru
                </button>
            </div>
        </div>

        <!-- Filter & Tab Switcher -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
                <button 
                    @click="switchTab('pemakaian')"
                    :class="[
                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all',
                        currentTab === 'pemakaian' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Catatan Pemakaian Harian
                </button>
                <button 
                    @click="switchTab('pembelian')"
                    :class="[
                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all',
                        currentTab === 'pembelian' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                    ]"
                >
                    Riwayat Pembelian Pakan
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
            </div>
        </div>

        <!-- Tab 1: Pemakaian Harian Sesuai Format PRD BUMDes -->
        <div v-if="currentTab === 'pemakaian'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Pencatatan Budidaya Ayam Ras Petelur (Pemakaian Pakan)
                </h4>
                <span class="text-xs text-slate-500">Kebutuhan: (Jml Ayam × Kebutuhan/Ekor) / 1000</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">Hari Ke</th>
                            <th class="p-3">Hari & Tgl</th>
                            <th class="p-3">Umur Ayam</th>
                            <th class="p-3 text-right">Stok Awal (kg)</th>
                            <th class="p-3 text-right">Kebutuhan/Ekor</th>
                            <th class="p-3 text-right">Jml Ayam</th>
                            <th class="p-3 text-right">Total Kebutuhan (kg)</th>
                            <th class="p-3 text-right">Sisa Pakan (kg)</th>
                            <th class="p-3 text-right">Biaya Pemakaian</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="usage in usages.data" :key="usage.id" class="hover:bg-slate-50/50">
                            <td class="p-3 font-semibold text-slate-700">{{ usage.hari_ke || '-' }}</td>
                            <td class="p-3 text-slate-800 font-medium">
                                <div>{{ usage.hari || '' }}</div>
                                <div class="text-[10px] text-slate-400">{{ usage.tanggal }}</div>
                            </td>
                            <td class="p-3 text-slate-600">
                                Mgg {{ usage.umur_minggu_ke || '-' }} (Hr {{ usage.umur_minggu_hari_ke || '-' }})
                            </td>
                            <td class="p-3 text-right font-medium text-slate-600">{{ Number(usage.stok_awal_kg).toFixed(1) }}</td>
                            <td class="p-3 text-right text-slate-600">{{ Number(usage.kebutuhan_per_ekor_gram).toFixed(1) }} gr</td>
                            <td class="p-3 text-right font-bold text-amber-600">{{ usage.populasi_ayam }}</td>
                            <td class="p-3 text-right font-bold text-rose-600">{{ Number(usage.total_kebutuhan_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right font-bold text-emerald-600">{{ Number(usage.sisa_pakan_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right text-slate-700 font-medium">{{ formatRp(usage.total_biaya_pemakaian) }}</td>
                            <td class="p-3 text-center">
                                <button @click="deleteUsage(usage.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="usages.data.length === 0">
                            <td colspan="10" class="text-center py-8 text-slate-400">
                                Belum ada data pemakaian pakan pada rentang tanggal ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Pembelian Pakan -->
        <div v-if="currentTab === 'pembelian'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Daftar Pembelian Pakan (Otomatis Masuk Pengeluaran & Arus Kas)
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Nama Pakan</th>
                            <th class="p-3">Supplier</th>
                            <th class="p-3 text-right">Jumlah (kg)</th>
                            <th class="p-3 text-right">Harga / kg</th>
                            <th class="p-3 text-right">Total Biaya</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="pur in purchases.data" :key="pur.id" class="hover:bg-slate-50/50">
                            <td class="p-3 text-slate-700 font-medium">{{ pur.tanggal }}</td>
                            <td class="p-3 font-bold text-slate-800">{{ pur.nama_pakan }}</td>
                            <td class="p-3 text-slate-600">{{ pur.supplier || '-' }}</td>
                            <td class="p-3 text-right font-bold text-purple-600">{{ Number(pur.jumlah_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right text-slate-600">{{ formatRp(pur.harga_per_kg) }}</td>
                            <td class="p-3 text-right font-bold text-rose-600">{{ formatRp(pur.total_biaya) }}</td>
                            <td class="p-3 text-slate-500">{{ pur.keterangan || '-' }}</td>
                            <td class="p-3 text-center">
                                <button @click="deletePurchase(pur.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Batal Pembelian">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="purchases.data.length === 0">
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                Belum ada riwayat pembelian pakan pada rentang tanggal ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Beli Pakan -->
        <div v-if="isPurchaseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Input Pembelian Pakan Ayam</h4>
                <form @submit.prevent="submitPurchase" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pembelian</label>
                        <input type="date" v-model="purchaseForm.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama/Jenis Pakan</label>
                        <input type="text" v-model="purchaseForm.nama_pakan" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah (kg)</label>
                            <input type="number" step="0.1" v-model="purchaseForm.jumlah_kg" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga per kg</label>
                            <input type="number" v-model="purchaseForm.harga_per_kg" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-xl text-xs flex justify-between font-bold text-purple-900">
                        <span>Total Biaya:</span>
                        <span>{{ formatRp(purchaseForm.jumlah_kg * purchaseForm.harga_per_kg) }}</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Toko</label>
                        <input type="text" v-model="purchaseForm.supplier" placeholder="Nama toko pakan" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan</label>
                        <textarea v-model="purchaseForm.keterangan" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isPurchaseModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl">Simpan Pembelian</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Pemakaian Pakan Harian -->
        <div v-if="isUsageModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Input Pemakaian Pakan Harian</h4>
                <form @submit.prevent="submitUsage" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date" v-model="usageForm.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Ke (Periode)</label>
                            <input type="number" v-model="usageForm.hari_ke" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Umur Minggu Ke</label>
                            <input type="number" v-model="usageForm.umur_minggu_ke" class="w-full text-xs rounded-xl border-slate-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Dalam Minggu</label>
                            <input type="number" v-model="usageForm.umur_minggu_hari_ke" min="1" max="7" class="w-full text-xs rounded-xl border-slate-200" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Populasi Ayam</label>
                            <input type="number" v-model="usageForm.populasi_ayam" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kebutuhan / Ekor (gram)</label>
                            <input type="number" step="0.1" v-model="usageForm.kebutuhan_per_ekor_gram" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl text-xs space-y-1 text-amber-900 font-medium">
                        <div class="flex justify-between">
                            <span>Total Pakan Dibutuhkan:</span>
                            <span class="font-bold text-sm text-rose-700">
                                {{ Number((usageForm.populasi_ayam * usageForm.kebutuhan_per_ekor_gram) / 1000).toFixed(2) }} kg
                            </span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>Stok Pakan Saat Ini:</span>
                            <span>{{ Number(summary.stok_saat_ini_kg).toFixed(1) }} kg</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Kondisi Pakan</label>
                        <input type="text" v-model="usageForm.keterangan" placeholder="Normal, cuaca panas, dll" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isUsageModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">Simpan Pemakaian</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

