<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    user: { type: Object, required: true },
});

const isUpdateModalOpen = ref(false);
const search = ref('');
const filterCategory = ref('');

const selectedProduct = ref(null);
const updateStockQty = ref(0);
const updateAction = ref('tambah'); // tambah atau kurang

const stocks = ref([
    { id: 1, name: 'Beras Premium Ciwuni', category: 'Pangan Utama', stock: 2500, unit: 'kg', rack: 'Gudang A-1', status: 'aman' },
    { id: 2, name: 'Beras Medium Ciwuni', category: 'Pangan Utama', stock: 3200, unit: 'kg', rack: 'Gudang A-2', status: 'aman' },
    { id: 3, name: 'Pupuk Urea Non-Subsidi', category: 'Saprotan', stock: 800, unit: 'kg', rack: 'Silo Barat', status: 'aman' },
    { id: 4, name: 'Pupuk Phonska Subsidi', category: 'Saprotan', stock: 120, unit: 'kg', rack: 'Silo Timur', status: 'kritis' },
    { id: 5, name: 'Bibit Padi Inpari 32', category: 'Benih', stock: 120, unit: 'kantong (5kg)', rack: 'Kamar Benih-A', status: 'aman' },
    { id: 6, name: 'Bibit Jagung Pioneer', category: 'Benih', stock: 15, unit: 'kantong (5kg)', rack: 'Kamar Benih-B', status: 'kritis' },
]);

const filteredStocks = computed(() => {
    return stocks.value.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(search.value.toLowerCase()) || 
                              item.rack.toLowerCase().includes(search.value.toLowerCase());
        const matchesCategory = filterCategory.value === '' || item.category === filterCategory.value;
        return matchesSearch && matchesCategory;
    });
});

const totalKgStocks = computed(() => {
    return stocks.value
        .filter(item => item.unit === 'kg')
        .reduce((sum, item) => sum + item.stock, 0);
});

const logout = () => {
    router.post(route('logout'));
};

const openUpdate = (item) => {
    selectedProduct.value = item;
    updateStockQty.value = 0;
    updateAction.value = 'tambah';
    isUpdateModalOpen.value = true;
};

const submitUpdate = () => {
    if (selectedProduct.value && updateStockQty.value > 0) {
        const item = stocks.value.find(s => s.id === selectedProduct.value.id);
        if (item) {
            if (updateAction.value === 'tambah') {
                item.stock += updateStockQty.value;
            } else {
                item.stock = Math.max(0, item.stock - updateStockQty.value);
            }
            // Update status
            item.status = item.stock < 150 ? 'kritis' : 'aman';
        }
        isUpdateModalOpen.value = false;
        selectedProduct.value = null;
    }
};
</script>

<template>
    <Head title="Dashboard Admin - Ketahanan Pangan" />

    <div class="min-h-screen bg-stone-100 text-stone-900 flex font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-emerald-900 border-r border-emerald-950 shrink-0 hidden md:flex flex-col justify-between p-6 text-white">
            <div class="space-y-8">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-800 rounded-xl border border-emerald-700 text-amber-300">
                        <span class="material-symbols-outlined font-bold">agriculture</span>
                    </div>
                    <div>
                        <h2 class="text-xs font-black text-white leading-tight">Admin Pangan</h2>
                        <p class="text-[9px] text-emerald-200 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-emerald-800 text-white font-bold text-xs rounded-xl shadow">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-emerald-150 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                        Stok Lumbung
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-emerald-150 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">assignment_ind</span>
                        Mitra Petani
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-emerald-150 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">receipt</span>
                        Pencatatan Panen
                    </a>
                </nav>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-emerald-850 pt-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold text-sm">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-emerald-250 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-950 hover:bg-emerald-990 border border-emerald-800 text-emerald-200 hover:text-white text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Nav -->
            <header class="h-16 border-b border-stone-200 bg-white px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-700 text-lg">explore</span>
                    <span class="text-xs font-bold text-stone-600">Selamat Bekerja, {{ user.nama }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" class="text-xs text-emerald-700 hover:underline font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        Lihat Landing Page
                    </Link>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 space-y-6 max-w-6xl w-full mx-auto">
                <!-- Overview Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-stone-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Total Pangan Utama</span>
                            <span class="material-symbols-outlined text-emerald-750 text-lg">grain</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-stone-850">{{ (totalKgStocks / 1000).toFixed(1) }} Ton</h3>
                            <p class="text-[9px] text-emerald-750 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">check_circle</span> Gudang Aman
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-stone-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Petani Binaan</span>
                            <span class="material-symbols-outlined text-emerald-750 text-lg">groups</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-stone-850">85 KK</h3>
                            <p class="text-[9px] text-emerald-750 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">trending_up</span> +3 Bulan Ini
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-stone-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Dana Yarnen Bergulir</span>
                            <span class="material-symbols-outlined text-emerald-750 text-lg">payments</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-stone-850">Rp 48,5 M</h3>
                            <p class="text-[9px] text-stone-500 font-semibold mt-0.5">Lancar Terdistribusi</p>
                        </div>
                    </div>

                    <div class="bg-white border border-stone-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Status Alsintan</span>
                            <span class="material-symbols-outlined text-emerald-750 text-lg">handyman</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-stone-850">8 Unit</h3>
                            <p class="text-[9px] text-emerald-750 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[10px]">check</span> Siap Pakai
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stock Management Panel -->
                <div class="bg-white border border-stone-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-stone-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-stone-850 uppercase tracking-wider">Kelola Inventori Gudang</h3>
                            <p class="text-[11px] text-stone-500 mt-1">Mencatat stok masuk/keluar beras, pupuk, dan benih lumbung pangan.</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <input v-model="search" type="text" placeholder="Cari barang / lokasi rak" class="rounded-xl border border-stone-300 bg-stone-50 px-3.5 py-2 text-xs text-stone-800 placeholder-stone-400 focus:border-emerald-600 focus:outline-none" />
                            
                            <select v-model="filterCategory" class="rounded-xl border border-stone-300 bg-stone-50 px-3 py-2 text-xs text-stone-800 focus:border-emerald-600 focus:outline-none">
                                <option value="">Semua Kategori</option>
                                <option value="Pangan Utama">Pangan Utama</option>
                                <option value="Saprotan">Saprotan</option>
                                <option value="Benih">Benih</option>
                            </select>
                        </div>
                    </div>

                    <!-- Stock Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 font-bold uppercase tracking-wider">
                                    <th class="p-4">Nama Barang</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Lokasi Penyimpanan</th>
                                    <th class="p-4">Jumlah Stok</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200">
                                <tr v-for="item in filteredStocks" :key="item.id" class="hover:bg-stone-50/50 transition-colors">
                                    <td class="p-4 font-bold text-stone-850">{{ item.name }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded text-[10px] font-semibold">{{ item.category }}</span>
                                    </td>
                                    <td class="p-4 text-stone-600 font-semibold">{{ item.rack }}</td>
                                    <td class="p-4 text-stone-800 font-black">{{ item.stock.toLocaleString('id-ID') }} {{ item.unit }}</td>
                                    <td class="p-4">
                                        <span v-if="item.status === 'aman'" class="px-2.5 py-0.5 bg-emerald-100 text-emerald-750 rounded-full text-[10px] font-extrabold uppercase">Aman</span>
                                        <span v-else class="px-2.5 py-0.5 bg-amber-100 text-amber-850 rounded-full text-[10px] font-extrabold uppercase">Kritis</span>
                                    </td>
                                    <td class="p-4 flex items-center justify-center gap-2">
                                        <button @click="openUpdate(item)" class="px-3 py-1.5 rounded-lg border border-stone-200 bg-stone-50 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 text-stone-600 text-[10px] font-bold uppercase transition">
                                            Update Stok
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredStocks.length === 0">
                                    <td colspan="6" class="p-8 text-center text-stone-400">
                                        Tidak menemukan data barang lumbung yang sesuai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Update Stock Modal -->
        <div v-if="isUpdateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white border border-stone-200 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="isUpdateModalOpen = false" class="absolute top-4 right-4 text-stone-450 hover:text-stone-700 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <h3 class="text-base font-extrabold text-stone-850 uppercase tracking-wider">Mutasi Stok Gudang</h3>
                <p class="text-xs text-stone-500 mt-1">Mengubah kuantitas barang: <span class="font-bold text-stone-800">{{ selectedProduct.name }}</span></p>

                <form @submit.prevent="submitUpdate" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-stone-500 uppercase mb-1">Jenis Mutasi</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="updateAction = 'tambah'" class="py-2.5 rounded-xl border text-xs font-bold transition" 
                                    :class="updateAction === 'tambah' ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-stone-250 bg-stone-50 text-stone-600'">
                                Tambah (Barang Masuk)
                            </button>
                            <button type="button" @click="updateAction = 'kurang'" class="py-2.5 rounded-xl border text-xs font-bold transition"
                                    :class="updateAction === 'kurang' ? 'border-red-650 bg-red-50/50 text-red-700' : 'border-stone-250 bg-stone-50 text-stone-600'">
                                Kurang (Barang Keluar)
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-stone-500 uppercase mb-1">Jumlah Mutasi ({{ selectedProduct.unit }})</label>
                        <input v-model="updateStockQty" type="number" min="1" class="w-full rounded-xl border border-stone-300 bg-stone-50 px-4 py-3 text-xs text-stone-800 focus:border-emerald-500 focus:outline-none" required />
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-650 text-white font-bold text-xs rounded-xl transition">
                        Simpan Penyesuaian
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
