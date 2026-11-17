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
const editingPurchase = ref(null);
const purchaseForm = ref({
    tanggal: new Date().toISOString().split('T')[0],
    nama_pakan: 'Pakan Layer SP-100',
    jumlah_kg: 50,
    harga_per_kg: 8500,
    supplier: '',
    keterangan: '',
});

const isSubmittingPurchase = ref(false);
const isSubmittingUsage = ref(false);

const openCreatePurchaseModal = () => {
    editingPurchase.value = null;
    purchaseForm.value = {
        tanggal: new Date().toISOString().split('T')[0],
        nama_pakan: 'Pakan Layer SP-100',
        jumlah_kg: 50,
        harga_per_kg: 8500,
        supplier: '',
        keterangan: '',
    };
    isPurchaseModalOpen.value = true;
};

const openEditPurchaseModal = (pur) => {
    editingPurchase.value = pur;
    purchaseForm.value = {
        tanggal: pur.tanggal,
        nama_pakan: pur.nama_pakan,
        jumlah_kg: Number(pur.jumlah_kg),
        harga_per_kg: Number(pur.harga_per_kg),
        supplier: pur.supplier || '',
        keterangan: pur.keterangan || '',
    };
    isPurchaseModalOpen.value = true;
};

const submitPurchase = () => {
    isSubmittingPurchase.value = true;
    if (editingPurchase.value) {
        router.put(route('kp.feed.purchase.update', editingPurchase.value.id), purchaseForm.value, {
            onSuccess: () => { 
                isPurchaseModalOpen.value = false; 
                isSubmittingPurchase.value = false;
            },
            onError: (errs) => {
                isSubmittingPurchase.value = false;
                alert(Object.values(errs).join('\n'));
            },
            onFinish: () => { isSubmittingPurchase.value = false; }
        });
    } else {
        router.post(route('kp.feed.purchase.store'), purchaseForm.value, {
            onSuccess: () => { 
                isPurchaseModalOpen.value = false; 
                isSubmittingPurchase.value = false;
            },
            onError: (errs) => {
                isSubmittingPurchase.value = false;
                alert(Object.values(errs).join('\n'));
            },
            onFinish: () => { isSubmittingPurchase.value = false; }
        });
    }
};

const deletePurchase = (id) => {
    if (confirm('Batalkan pembelian ini? Stok pakan dan pengeluaran terkait akan di-rollback.')) {
        router.delete(route('kp.feed.purchase.destroy', id));
    }
};

// Modal Pemakaian Harian
const isUsageModalOpen = ref(false);
const editingUsage = ref(null);
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

const openCreateUsageModal = () => {
    editingUsage.value = null;
    usageForm.value = {
        tanggal: new Date().toISOString().split('T')[0],
        hari: '',
        hari_ke: (props.usages.total || 0) + 1,
        umur_minggu_ke: 24,
        umur_minggu_hari_ke: 1,
        populasi_ayam: 1000,
        kebutuhan_per_ekor_gram: 115,
        harga_per_kg: 8500,
        keterangan: '',
    };
    isUsageModalOpen.value = true;
};

const openEditUsageModal = (usage) => {
    editingUsage.value = usage;
    usageForm.value = {
        tanggal: usage.tanggal,
        hari: usage.hari || '',
        hari_ke: usage.hari_ke || 1,
        umur_minggu_ke: usage.umur_minggu_ke || 24,
        umur_minggu_hari_ke: usage.umur_minggu_hari_ke || 1,
        populasi_ayam: usage.populasi_ayam || 1000,
        kebutuhan_per_ekor_gram: Number(usage.kebutuhan_per_ekor_gram) || 115,
        harga_per_kg: Number(usage.harga_per_kg) || 8500,
        keterangan: usage.keterangan || '',
    };
    isUsageModalOpen.value = true;
};

const submitUsage = () => {
    isSubmittingUsage.value = true;
    if (editingUsage.value) {
        router.put(route('kp.feed.usage.update', editingUsage.value.id), usageForm.value, {
            onSuccess: () => { 
                isUsageModalOpen.value = false; 
                isSubmittingUsage.value = false;
            },
            onError: (errs) => {
                isSubmittingUsage.value = false;
                alert(Object.values(errs).join('\n'));
            },
            onFinish: () => { isSubmittingUsage.value = false; }
        });
    } else {
        router.post(route('kp.feed.usage.store'), usageForm.value, {
            onSuccess: () => { 
                isUsageModalOpen.value = false; 
                isSubmittingUsage.value = false;
            },
            onError: (errs) => {
                isSubmittingUsage.value = false;
                alert(Object.values(errs).join('\n'));
            },
            onFinish: () => { isSubmittingUsage.value = false; }
        });
    }
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
                <h4 class="text-2xl font-bold text-amber-600 mt-1">
                    {{ Number(summary.total_pakai_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-slate-400">Konsumsi harian populasi</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-center gap-2">
                <button 
                    @click="openCreateUsageModal"
                    class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 shadow-xs transition-colors"
                >
                    <span class="material-symbols-outlined text-base">edit_calendar</span>
                    Catat Pemakaian Harian
                </button>
                <button 
                    @click="openCreatePurchaseModal"
                    class="w-full py-2.5 px-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 shadow-xs transition-colors"
                >
                    <span class="material-symbols-outlined text-base">shopping_cart</span>
                    Beli Pakan Baru
                </button>
            </div>
        </div>

        <!-- Filter & Tab Nav -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold text-slate-600">
                <button 
                    @click="switchTab('pemakaian')"
                    :class="['px-4 py-2 rounded-lg transition-all', currentTab === 'pemakaian' ? 'bg-white text-slate-800 shadow-xs' : 'hover:text-slate-900']"
                >
                    Catatan Pemakaian Harian
                </button>
                <button 
                    @click="switchTab('pembelian')"
                    :class="['px-4 py-2 rounded-lg transition-all', currentTab === 'pembelian' ? 'bg-white text-slate-800 shadow-xs' : 'hover:text-slate-900']"
                >
                    Riwayat Pembelian Pakan
                </button>
            </div>

            <!-- Date Range Filter -->
            <div class="flex items-center gap-2">
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500" />
                <button @click="applyFilter" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
            </div>
        </div>

        <!-- Tab 1: Pemakaian Pakan Harian -->
        <div v-if="currentTab === 'pemakaian'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Pencatatan Budidaya Ayam Ras Petelur (Pemakaian Pakan)
                </h4>
                <div class="text-[11px] text-slate-400">
                    Kebutuhan: (Jml Ayam &times; Kebutuhan/Ekor) / 1000
                </div>
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
                            <th class="p-3 text-right">Harga / kg</th>
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
                            <td class="p-3 text-right text-slate-500 font-medium">{{ formatRp(usage.harga_per_kg || 0) }}</td>
                            <td class="p-3 text-right font-bold text-emerald-600">{{ Number(usage.sisa_pakan_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right text-emerald-700 font-bold">{{ formatRp(usage.total_biaya_pemakaian) }}</td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditUsageModal(usage)" class="p-1 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50" title="Edit Pemakaian">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button @click="deleteUsage(usage.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="usages.data.length === 0">
                            <td colspan="11" class="text-center py-8 text-slate-400">
                                Belum ada data pemakaian pakan pada rentang tanggal ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Pemakaian -->
            <div v-if="usages.total > 0" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ usages.from || 0 }} - {{ usages.to || 0 }} dari {{ usages.total }} data pemakaian</span>
                <div v-if="usages.links && usages.links.length > 3" class="flex gap-1">
                    <button 
                        v-for="(link, i) in usages.links" 
                        :key="i"
                        :disabled="!link.url"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                        :class="[
                            'px-2.5 py-1 rounded-lg text-xs font-medium',
                            link.active ? 'bg-emerald-600 text-white' : 'hover:bg-slate-100 text-slate-600',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
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
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditPurchaseModal(pur)" class="p-1 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50" title="Edit Pembelian">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button @click="deletePurchase(pur.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Batal Pembelian">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
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

            <!-- Pagination Pembelian -->
            <div v-if="purchases.total > 0" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ purchases.from || 0 }} - {{ purchases.to || 0 }} dari {{ purchases.total }} data pembelian</span>
                <div v-if="purchases.links && purchases.links.length > 3" class="flex gap-1">
                    <button 
                        v-for="(link, i) in purchases.links" 
                        :key="i"
                        :disabled="!link.url"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                        :class="[
                            'px-2.5 py-1 rounded-lg text-xs font-medium',
                            link.active ? 'bg-purple-600 text-white' : 'hover:bg-slate-100 text-slate-600',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Modal Beli Pakan (Tambah & Edit) -->
        <div v-if="isPurchaseModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">
                        {{ editingPurchase ? 'Edit Pembelian Pakan Ayam' : 'Input Pembelian Pakan Ayam' }}
                    </h4>
                    <button @click="isPurchaseModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
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
                        <button type="button" @click="isPurchaseModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl" :disabled="isSubmittingPurchase">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5" :disabled="isSubmittingPurchase">
                            <span v-if="isSubmittingPurchase" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            {{ isSubmittingPurchase ? 'Menyimpan...' : (editingPurchase ? 'Simpan Perubahan' : 'Simpan Pembelian') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Pemakaian Pakan Harian (Tambah & Edit) -->
        <div v-if="isUsageModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">
                        {{ editingUsage ? 'Edit Pemakaian Pakan Harian' : 'Input Pemakaian Pakan Harian' }}
                    </h4>
                    <button @click="isUsageModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
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
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Pakan per kg (Rp)</label>
                        <input 
                            type="number" 
                            step="100" 
                            v-model="usageForm.harga_per_kg" 
                            placeholder="Contoh: 8500"
                            class="w-full text-xs rounded-xl border-slate-200 font-bold text-slate-800" 
                            required 
                        />
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl text-xs space-y-1.5 text-amber-900 font-medium border border-amber-200/60">
                        <div class="flex justify-between items-center">
                            <span>Total Pakan Dibutuhkan:</span>
                            <span class="font-bold text-sm text-rose-700">
                                {{ Number((usageForm.populasi_ayam * usageForm.kebutuhan_per_ekor_gram) / 1000).toFixed(2) }} kg
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-amber-200/50">
                            <span class="font-bold text-slate-700">Hasil Biaya Pemakaian:</span>
                            <span class="font-extrabold text-sm text-emerald-700">
                                {{ formatRp(((usageForm.populasi_ayam * usageForm.kebutuhan_per_ekor_gram) / 1000) * (usageForm.harga_per_kg || 0)) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500 pt-0.5">
                            <span>Stok Pakan Saat Ini:</span>
                            <span>{{ Number(summary.stok_saat_ini_kg).toFixed(1) }} kg</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Kondisi Pakan</label>
                        <input type="text" v-model="usageForm.keterangan" placeholder="Normal, cuaca panas, dll" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isUsageModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl" :disabled="isSubmittingUsage">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5" :disabled="isSubmittingUsage">
                            <span v-if="isSubmittingUsage" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            {{ isSubmittingUsage ? 'Menyimpan...' : (editingUsage ? 'Simpan Perubahan' : 'Simpan Pemakaian') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>
