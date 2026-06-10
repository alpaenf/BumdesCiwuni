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

const isSidebarOpen = ref(false);

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

    <div class="min-h-screen bg-slate-100 text-slate-900 flex font-sans">
        <!-- Mobile Sidebar Backdrop -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- Sidebar -->
        <aside :class="['fixed md:static inset-y-0 left-0 z-50 w-64 bg-blue-800 border-r border-blue-900 shrink-0 flex flex-col justify-between p-6 text-white transition-transform duration-300', isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
            <div class="space-y-8">
                <!-- Branding -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-700 rounded-xl border border-blue-600 text-blue-100">
                            <span class="material-symbols-outlined font-bold">agriculture</span>
                        </div>
                        <div>
                            <h2 class="text-xs font-black text-white leading-tight">Admin Pangan</h2>
                            <p class="text-[9px] text-blue-200 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                        </div>
                    </div>
                    <button @click="isSidebarOpen = false" class="md:hidden text-blue-200 hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Nav -->
                <nav class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-700 text-white font-bold text-xs rounded-xl shadow">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </a>
                    <Link :href="route('admin.landing-page.edit')" class="flex items-center gap-3 px-4 py-3 text-blue-200 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">web</span>
                        Pengaturan Landing Page
                    </Link>
                </nav>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-emerald-850 pt-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-blue-200 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-blue-900 hover:bg-blue-950 border border-blue-700 text-blue-200 hover:text-white text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
            <!-- Top Nav -->
            <header class="sticky top-0 z-30 h-16 border-b border-slate-200 bg-white/90 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <button @click="isSidebarOpen = true" class="md:hidden p-1.5 -ml-2 text-slate-500 hover:text-slate-800 rounded-lg transition mr-1">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <span class="material-symbols-outlined text-blue-600 text-lg hidden sm:block">explore</span>
                    <span class="text-xs font-bold text-slate-600 truncate">Selamat Bekerja, {{ user.nama }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        <span class="hidden sm:inline">Lihat Landing Page</span>
                    </Link>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 space-y-6 max-w-6xl w-full mx-auto">
                <!-- Overview Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Pangan Utama</span>
                            <span class="material-symbols-outlined text-blue-700 text-lg">grain</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-850">{{ (totalKgStocks / 1000).toFixed(1) }} Ton</h3>
                            <p class="text-[9px] text-blue-700 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">check_circle</span> Gudang Aman
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Petani Binaan</span>
                            <span class="material-symbols-outlined text-blue-700 text-lg">groups</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-850">85 KK</h3>
                            <p class="text-[9px] text-blue-700 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">trending_up</span> +3 Bulan Ini
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Dana Yarnen Bergulir</span>
                            <span class="material-symbols-outlined text-blue-700 text-lg">payments</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-850">Rp 48,5 M</h3>
                            <p class="text-[9px] text-slate-500 font-semibold mt-0.5">Lancar Terdistribusi</p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status Alsintan</span>
                            <span class="material-symbols-outlined text-blue-700 text-lg">handyman</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-850">8 Unit</h3>
                            <p class="text-[9px] text-blue-700 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[10px]">check</span> Siap Pakai
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stock Management Panel -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-850 uppercase tracking-wider">Kelola Inventori Gudang</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Mencatat stok masuk/keluar beras, pupuk, dan benih lumbung pangan.</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <input v-model="search" type="text" placeholder="Cari barang / lokasi rak" class="rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:outline-none" />
                            
                            <select v-model="filterCategory" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-800 focus:border-blue-600 focus:outline-none">
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
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                                    <th class="p-4">Nama Barang</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4">Lokasi Penyimpanan</th>
                                    <th class="p-4">Jumlah Stok</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-for="item in filteredStocks" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 font-bold text-slate-850">{{ item.name }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded text-[10px] font-semibold">{{ item.category }}</span>
                                    </td>
                                    <td class="p-4 text-slate-600 font-semibold">{{ item.rack }}</td>
                                    <td class="p-4 text-slate-800 font-black">{{ item.stock.toLocaleString('id-ID') }} {{ item.unit }}</td>
                                    <td class="p-4">
                                        <span v-if="item.status === 'aman'" class="px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-extrabold uppercase">Aman</span>
                                        <span v-else class="px-2.5 py-0.5 bg-amber-100 text-amber-850 rounded-full text-[10px] font-extrabold uppercase">Kritis</span>
                                    </td>
                                    <td class="p-4 flex items-center justify-center gap-2">
                                        <button @click="openUpdate(item)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 text-slate-600 text-[10px] font-bold uppercase transition">
                                            Update Stok
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredStocks.length === 0">
                                    <td colspan="6" class="p-8 text-center text-slate-400">
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
        <div v-if="isUpdateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="isUpdateModalOpen = false" class="absolute top-4 right-4 text-slate-450 hover:text-slate-700 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <h3 class="text-base font-extrabold text-slate-850 uppercase tracking-wider">Mutasi Stok Gudang</h3>
                <p class="text-xs text-slate-500 mt-1">Mengubah kuantitas barang: <span class="font-bold text-slate-800">{{ selectedProduct.name }}</span></p>

                <form @submit.prevent="submitUpdate" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jenis Mutasi</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="updateAction = 'tambah'" class="py-2.5 rounded-xl border text-xs font-bold transition" 
                                    :class="updateAction === 'tambah' ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-250 bg-slate-50 text-slate-600'">
                                Tambah (Barang Masuk)
                            </button>
                            <button type="button" @click="updateAction = 'kurang'" class="py-2.5 rounded-xl border text-xs font-bold transition"
                                    :class="updateAction === 'kurang' ? 'border-red-650 bg-red-50/50 text-red-700' : 'border-slate-250 bg-slate-50 text-slate-600'">
                                Kurang (Barang Keluar)
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Jumlah Mutasi ({{ selectedProduct.unit }})</label>
                        <input v-model="updateStockQty" type="number" min="1" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-800 focus:border-blue-500 focus:outline-none" required />
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition">
                        Simpan Penyesuaian
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
