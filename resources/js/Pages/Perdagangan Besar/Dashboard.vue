<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    user: { type: Object, required: true },
});

const isAddModalOpen = ref(false);
const search = ref('');
const filterStatus = ref('');

const newCompany = ref('');
const newContact = ref('');
const newCommodity = ref('Kopi Robusta Ciwuni Grade A');
const newVolume = ref(100);
const newStatus = ref('Menunggu Pembayaran');

const orders = ref([
    { id: 1, company: 'PT Indofood Makmur', contact: 'Bp. Hermawan', phone: '081199887766', commodity: 'Kopi Robusta Ciwuni Grade A', volume: '2.5 Ton', total: 'Rp 162.500.000', status: 'Dalam Pengiriman', date: '2026-06-01' },
    { id: 2, company: 'CV Nusantara Spices', contact: 'Ibu Ratna', phone: '085211223344', commodity: 'Cengkeh Kering Kualitas Ekspor', volume: '500 kg', total: 'Rp 62.500.000', status: 'Selesai', date: '2026-06-03' },
    { id: 3, company: 'Singapore Trade Corp', contact: 'Mr. David Lee', phone: '+6591234567', commodity: 'Minyak Kelapa Murni (VCO)', volume: '1 Ton', total: 'Rp 70.000.000', status: 'Menunggu Pembayaran', date: '2026-06-05' },
    { id: 4, company: 'PT Unilever Indonesia', contact: 'Bp. Santoso', phone: '081233445566', commodity: 'Kopra Putih Reguler', volume: '12 Ton', total: 'Rp 138.000.000', status: 'Selesai', date: '2026-06-06' },
    { id: 5, company: 'Koperasi Tani Jaya', contact: 'Bp. Sukiman', phone: '087788990011', commodity: 'Kopra Putih Reguler', volume: '2 Ton', total: 'Rp 23.000.000', status: 'Batal', date: '2026-06-08' },
]);

const filteredOrders = computed(() => {
    return orders.value.filter(order => {
        const matchesSearch = order.company.toLowerCase().includes(search.value.toLowerCase()) || 
                              order.contact.toLowerCase().includes(search.value.toLowerCase()) || 
                              order.commodity.toLowerCase().includes(search.value.toLowerCase());
        const matchesStatus = filterStatus.value === '' || order.status === filterStatus.value;
        return matchesSearch && matchesStatus;
    });
});

const totalTransactionVal = computed(() => {
    const total = orders.value
        .filter(o => o.status === 'Selesai' || o.status === 'Dalam Pengiriman')
        .reduce((sum, o) => {
            const val = parseInt(o.total.replace(/[^\d]/g, ''));
            return sum + val;
        }, 0);
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
});

const logout = () => {
    router.post(route('logout'));
};

const updateStatus = (id, nextStatus) => {
    const o = orders.value.find(order => order.id === id);
    if (o) {
        o.status = nextStatus;
    }
};

const createOrder = () => {
    if (newCompany.value && newContact.value && newVolume.value > 0) {
        const prices = {
            'Kopi Robusta Ciwuni Grade A': 65000,
            'Cengkeh Kering Kualitas Ekspor': 125000,
            'Minyak Kelapa Murni (VCO)': 35000,
            'Kopra Putih Reguler': 11500
        };
        
        const pricePerUnit = prices[newCommodity.value];
        const multiplier = newCommodity.value.includes('VCO') ? newVolume.value : newVolume.value; // simple approximation
        const calculatedTotal = pricePerUnit * multiplier;
        const formattedTotal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(calculatedTotal);
        
        orders.value.unshift({
            id: orders.value.length + 1,
            company: newCompany.value,
            contact: newContact.value,
            phone: '08120000000',
            commodity: newCommodity.value,
            volume: newVolume.value.toLocaleString() + ' ' + (newCommodity.value.includes('VCO') ? 'Liter' : 'kg'),
            total: formattedTotal,
            status: newStatus.value,
            date: new Date().toISOString().split('T')[0]
        });
        
        // Reset
        newCompany.value = '';
        newContact.value = '';
        newVolume.value = 100;
        newStatus.value = 'Menunggu Pembayaran';
        isAddModalOpen.value = false;
    }
};
</script>

<template>
    <Head title="Dashboard Admin - Perdagangan Besar" />

    <div class="min-h-screen bg-slate-50 text-slate-800 flex font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-slate-300 shrink-0 hidden md:flex flex-col justify-between p-6">
            <div class="space-y-8">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-500/10 border border-blue-500/30 rounded-xl text-amber-405">
                        <span class="material-symbols-outlined font-bold">local_shipping</span>
                    </div>
                    <div>
                        <h2 class="text-xs font-black text-slate-900 leading-tight">Admin Dagang</h2>
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-500/10 text-blue-600 font-bold text-xs rounded-xl border border-blue-500/20">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">assignment</span>
                        Kontrak Supply
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                        Jadwal Kargo
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-slate-900 font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">analytics</span>
                        Statistik Penjualan
                    </a>
                </nav>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-slate-300 pt-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-500 text-slate-950 flex items-center justify-center font-bold text-sm">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-50 border border-slate-200 hover:bg-red-950/20 hover:border-red-900/30 text-slate-500 hover:text-red-400 text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Nav -->
            <header class="h-16 border-b border-slate-300 bg-white/50 backdrop-blur-md px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-450 text-lg">explore</span>
                    <span class="text-xs font-bold text-slate-600">Selamat Bekerja, {{ user.nama }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('unit.welcome', { slug: 'perdagangan-besar' })" class="text-xs text-amber-450 hover:underline font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        Lihat Landing Page
                    </Link>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 space-y-6 max-w-6xl w-full mx-auto">
                <!-- Overview Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white border border-slate-300 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Omset Aktif</span>
                            <span class="material-symbols-outlined text-amber-450 text-lg">monetization_on</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ totalTransactionVal }}</h3>
                            <p class="text-[9px] text-blue-400 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">trending_up</span> +12% vs Bulan Lalu
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-300 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kargo Aktif</span>
                            <span class="material-symbols-outlined text-amber-450 text-lg">local_shipping</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">{{ orders.filter(o => o.status === 'Dalam Pengiriman').length }} Truk</h3>
                            <p class="text-[9px] text-slate-500 font-semibold mt-0.5">Sedang dalam perjalanan</p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-300 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Komoditas</span>
                            <span class="material-symbols-outlined text-amber-450 text-lg">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">18,2 Ton</h3>
                            <p class="text-[9px] text-slate-500 font-semibold mt-0.5">Volume keluar masuk gudang</p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-300 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mitra Korporasi</span>
                            <span class="material-symbols-outlined text-amber-450 text-lg">handshake</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900">18 Perusahaan</h3>
                            <p class="text-[9px] text-emerald-450 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">verified</span> Semua Aktif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Transaction Management Panel -->
                <div class="bg-white border border-slate-300 rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Kelola Transaksi Kontrak Grosir</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Mencatat pesanan komoditas hasil bumi dan status pengiriman cargo ke gudang mitra.</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <input v-model="search" type="text" placeholder="Cari perusahaan / komoditas" class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 placeholder-slate-600 focus:border-blue-500 focus:outline-none" />
                            
                            <select v-model="filterStatus" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:border-blue-500 focus:outline-none">
                                <option value="">Semua Status</option>
                                <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                                <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Batal">Batal</option>
                            </select>

                            <button @click="isAddModalOpen = true" class="inline-flex items-center gap-1 px-4 py-2 bg-blue-500 hover:bg-blue-500 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition">
                                <span class="material-symbols-outlined text-sm font-bold">add</span>
                                Buat Invoice
                            </button>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-300 text-slate-500 font-bold uppercase tracking-wider">
                                    <th class="p-4">Perusahaan & Kontak</th>
                                    <th class="p-4">Komoditas Pesanan</th>
                                    <th class="p-4">Volume</th>
                                    <th class="p-4">Total Nilai</th>
                                    <th class="p-4">Tanggal Kontrak</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-850">
                                <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-slate-100/30 transition-colors">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-900">{{ order.company }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">{{ order.contact }} • {{ order.phone }}</p>
                                    </td>
                                    <td class="p-4 text-slate-600 font-semibold">{{ order.commodity }}</td>
                                    <td class="p-4 text-slate-405 font-bold">{{ order.volume }}</td>
                                    <td class="p-4 text-slate-700 font-black">{{ order.total }}</td>
                                    <td class="p-4 text-slate-500">{{ order.date }}</td>
                                    <td class="p-4">
                                        <span v-if="order.status === 'Selesai'" class="px-2.5 py-0.5 bg-blue-900 text-emerald-450 border border-blue-800/40 rounded-full text-[10px] font-extrabold uppercase">Selesai</span>
                                        <span v-else-if="order.status === 'Dalam Pengiriman'" class="px-2.5 py-0.5 bg-blue-950 text-blue-400 border border-blue-900/40 rounded-full text-[10px] font-extrabold uppercase">Pengiriman</span>
                                        <span v-else-if="order.status === 'Menunggu Pembayaran'" class="px-2.5 py-0.5 bg-amber-950/50 text-blue-500 border border-amber-900/40 rounded-full text-[10px] font-extrabold uppercase">Menunggu</span>
                                        <span v-else class="px-2.5 py-0.5 bg-red-950 text-red-400 border border-red-900/40 rounded-full text-[10px] font-extrabold uppercase">Batal</span>
                                    </td>
                                    <td class="p-4 flex items-center justify-center gap-2">
                                        <div v-if="order.status === 'Menunggu Pembayaran'" class="flex gap-1.5">
                                            <button @click="updateStatus(order.id, 'Dalam Pengiriman')" class="px-2.5 py-1.5 bg-blue-650 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold transition">Kirim Kargo</button>
                                            <button @click="updateStatus(order.id, 'Batal')" class="px-2.5 py-1.5 bg-red-950/20 border border-red-900/30 text-red-400 rounded-lg text-[10px] font-bold transition">Batal</button>
                                        </div>
                                        <div v-else-if="order.status === 'Dalam Pengiriman'">
                                            <button @click="updateStatus(order.id, 'Selesai')" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold transition">Selesai Bongkar</button>
                                        </div>
                                        <span v-else class="text-slate-600 font-semibold italic text-[10px]">Lunas</span>
                                    </td>
                                </tr>
                                <tr v-if="filteredOrders.length === 0">
                                    <td colspan="7" class="p-8 text-center text-slate-500">
                                        Tidak menemukan kontrak transaksi grosir yang sesuai pencarian Anda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Create Invoice Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-50/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="isAddModalOpen = false" class="absolute top-4 right-4 text-slate-500 hover:text-slate-900 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <h3 class="text-base font-extrabold text-slate-900 uppercase tracking-wider">Buat Kontrak Invoice Baru</h3>
                <p class="text-xs text-slate-500 mt-1">Isi detail pesanan korporat untuk membuat invoice penagihan baru.</p>

                <form @submit.prevent="createOrder" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Perusahaan</label>
                            <input v-model="newCompany" type="text" placeholder="PT / CV..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Perwakilan</label>
                            <input v-model="newContact" type="text" placeholder="Nama Person" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Komoditas</label>
                        <select v-model="newCommodity" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="Kopi Robusta Ciwuni Grade A">Kopi Robusta Ciwuni Grade A (Rp 65.000 / kg)</option>
                            <option value="Cengkeh Kering Kualitas Ekspor">Cengkeh Kering Kualitas Ekspor (Rp 125.000 / kg)</option>
                            <option value="Minyak Kelapa Murni (VCO)">Minyak Kelapa Murni (VCO) (Rp 35.000 / Liter)</option>
                            <option value="Kopra Putih Reguler">Kopra Putih Reguler (Rp 11.500 / kg)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Volume (kg / Liter)</label>
                        <input v-model="newVolume" type="number" min="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Sesi</label>
                        <select v-model="newStatus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 focus:border-blue-500 focus:outline-none">
                            <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                            <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-blue-500 hover:bg-blue-500 text-slate-955 font-bold text-xs rounded-xl transition">
                        Daftarkan Kontrak Grosir
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
