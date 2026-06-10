<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    unit: { type: Object, required: true },
    user: { type: Object, required: true },
});

const isAddModalOpen = ref(false);
const search = ref('');
const filterPlan = ref('');

const newName = ref('');
const newAddress = ref('');
const newPlan = ref('Lite Desa');
const newPhone = ref('');

const customers = ref([
    { id: 1, name: 'Budi Raharjo', address: 'RT 02 / RW 04, Dusun Karanganyar', phone: '081298765432', plan: 'Lite Desa', price: 'Rp 150.000', status: 'aktif', join_date: '2026-01-15' },
    { id: 2, name: 'Siti Khasanah', address: 'RT 01 / RW 01, Dusun Ciwuni Tengah', phone: '085322119988', plan: 'Keluarga Harmoni', price: 'Rp 250.000', status: 'aktif', join_date: '2026-02-10' },
    { id: 3, name: 'Supardi Purwanto', address: 'RT 04 / RW 02, Dusun Wanasari', phone: '087766554433', plan: 'Keluarga Harmoni', price: 'Rp 250.000', status: 'aktif', join_date: '2026-03-01' },
    { id: 4, name: 'Warung Kopi Digital', address: 'RT 03 / RW 04, Dusun Karanganyar', phone: '082188887777', plan: 'Bisnis & UMKM', price: 'Rp 450.000', status: 'aktif', join_date: '2026-03-22' },
    { id: 5, name: 'Rahmat Hidayat', address: 'RT 02 / RW 03, Dusun Ciwuni Tengah', phone: '089912345678', plan: 'Lite Desa', price: 'Rp 150.000', status: 'nonaktif', join_date: '2026-04-05' },
    { id: 6, name: 'SDN 1 Ciwuni', address: 'RT 01 / RW 02, Dusun Ciwuni Tengah', phone: '081122334455', plan: 'Bisnis & UMKM', price: 'Rp 450.000', status: 'aktif', join_date: '2026-05-12' },
]);

const filteredCustomers = computed(() => {
    return customers.value.filter(cust => {
        const matchesSearch = cust.name.toLowerCase().includes(search.value.toLowerCase()) || 
                              cust.address.toLowerCase().includes(search.value.toLowerCase()) || 
                              cust.phone.includes(search.value);
        const matchesPlan = filterPlan.value === '' || cust.plan === filterPlan.value;
        return matchesSearch && matchesPlan;
    });
});

const totalBilling = computed(() => {
    const total = customers.value
        .filter(c => c.status === 'aktif')
        .reduce((sum, c) => {
            const val = parseInt(c.price.replace(/[^\d]/g, ''));
            return sum + val;
        }, 0);
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
});

const logout = () => {
    router.post(route('logout'));
};

const toggleStatus = (id) => {
    const c = customers.value.find(cust => cust.id === id);
    if (c) {
        c.status = c.status === 'aktif' ? 'nonaktif' : 'aktif';
    }
};

const addCustomer = () => {
    if (newName.value && newAddress.value && newPhone.value) {
        const prices = {
            'Lite Desa': 'Rp 150.000',
            'Keluarga Harmoni': 'Rp 250.000',
            'Bisnis & UMKM': 'Rp 450.000'
        };
        
        customers.value.unshift({
            id: customers.value.length + 1,
            name: newName.value,
            address: newAddress.value,
            phone: newPhone.value,
            plan: newPlan.value,
            price: prices[newPlan.value],
            status: 'aktif',
            join_date: new Date().toISOString().split('T')[0]
        });
        
        // Reset
        newName.value = '';
        newAddress.value = '';
        newPhone.value = '';
        newPlan.value = 'Lite Desa';
        isAddModalOpen.value = false;
    }
};
</script>

<template>
    <Head title="Dashboard Admin - Unit Wifi" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 border-r border-slate-850 shrink-0 hidden md:flex flex-col justify-between p-6">
            <div class="space-y-8">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-600/10 border border-indigo-500/30 rounded-xl text-indigo-400">
                        <span class="material-symbols-outlined font-bold">wifi</span>
                    </div>
                    <div>
                        <h2 class="text-xs font-black text-white leading-tight">Admin Unit Wifi</h2>
                        <p class="text-[9px] text-slate-400 uppercase tracking-widest font-semibold mt-0.5">BUMDes Ciwuni</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-indigo-600/10 text-indigo-300 font-bold text-xs rounded-xl border border-indigo-600/20">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">group</span>
                        Pelanggan
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">receipt_long</span>
                        Tagihan Bulanan
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white font-semibold text-xs rounded-xl transition">
                        <span class="material-symbols-outlined text-lg">settings</span>
                        Pengaturan Alat
                    </a>
                </nav>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-slate-850 pt-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                        {{ user.nama.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold text-white truncate">{{ user.nama }}</p>
                        <p class="text-[9px] text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                </div>
                <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 bg-slate-950 border border-slate-800 hover:bg-red-950/20 hover:border-red-900/30 text-slate-400 hover:text-red-400 text-xs font-bold rounded-xl transition">
                    Keluar Aplikasi
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Nav -->
            <header class="h-16 border-b border-slate-850 bg-slate-900/50 backdrop-blur-md px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-400 text-lg">explore</span>
                    <span class="text-xs font-bold text-slate-350">Selamat Bekerja, {{ user.nama }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('unit.welcome', { slug: 'wifi' })" class="text-xs text-indigo-400 hover:underline font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        Lihat Landing Page
                    </Link>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 space-y-6 max-w-6xl w-full mx-auto">
                <!-- Overview Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-900 border border-slate-850 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Total Pelanggan</span>
                            <span class="material-symbols-outlined text-indigo-400 text-lg">group</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-white">{{ customers.filter(c => c.status === 'aktif').length }}</h3>
                            <p class="text-[9px] text-emerald-400 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-xs">trending_up</span> +2 Bulan Ini
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-850 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Pendapatan Bulanan</span>
                            <span class="material-symbols-outlined text-indigo-400 text-lg">payments</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-white">{{ totalBilling }}</h3>
                            <p class="text-[9px] text-slate-500 font-semibold mt-0.5">Pendapatan Terproyeksi</p>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-850 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Penggunaan Bandwidth</span>
                            <span class="material-symbols-outlined text-indigo-400 text-lg">speed</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-white">4.8 Gbps</h3>
                            <p class="text-[9px] text-slate-500 font-semibold mt-0.5">Peak traffic tadi malam</p>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-850 p-5 rounded-2xl space-y-3 shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-450 uppercase tracking-widest">Uptime Router Desa</span>
                            <span class="material-symbols-outlined text-indigo-400 text-lg">settings_ethernet</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-emerald-450">99.98%</h3>
                            <p class="text-[9px] text-emerald-400 font-bold flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[10px]">check_circle</span> Sehat
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Management Panel -->
                <div class="bg-slate-900 border border-slate-850 rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-850 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider">Daftar Pelanggan Aktif</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Mengelola sambungan internet rumah warga Desa Ciwuni.</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <input v-model="search" type="text" placeholder="Cari nama / dusun / HP" class="rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2 text-xs text-slate-200 placeholder-slate-600 focus:border-indigo-500 focus:outline-none" />
                            
                            <select v-model="filterPlan" class="rounded-xl border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none">
                                <option value="">Semua Paket</option>
                                <option value="Lite Desa">Lite Desa</option>
                                <option value="Keluarga Harmoni">Keluarga Harmoni</option>
                                <option value="Bisnis & UMKM">Bisnis & UMKM</option>
                            </select>

                            <button @click="isAddModalOpen = true" class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg transition">
                                <span class="material-symbols-outlined text-sm font-bold">add</span>
                                Pasang Baru
                            </button>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-950 border-b border-slate-850 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="p-4">Nama Pelanggan</th>
                                    <th class="p-4">Alamat Rumah</th>
                                    <th class="p-4">Paket Internet</th>
                                    <th class="p-4">Nominal</th>
                                    <th class="p-4">Tanggal Gabung</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-850">
                                <tr v-for="cust in filteredCustomers" :key="cust.id" class="hover:bg-slate-850/30 transition-colors">
                                    <td class="p-4">
                                        <p class="font-bold text-white">{{ cust.name }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">{{ cust.phone }}</p>
                                    </td>
                                    <td class="p-4 text-slate-300 max-w-xs truncate">{{ cust.address }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 bg-indigo-950 text-indigo-400 border border-indigo-900 rounded text-[10px] font-semibold">{{ cust.plan }}</span>
                                    </td>
                                    <td class="p-4 text-slate-200 font-bold">{{ cust.price }}</td>
                                    <td class="p-4 text-slate-400">{{ cust.join_date }}</td>
                                    <td class="p-4">
                                        <span v-if="cust.status === 'aktif'" class="px-2.5 py-0.5 bg-emerald-950 text-emerald-450 border border-emerald-900/40 rounded-full text-[10px] font-extrabold uppercase">Aktif</span>
                                        <span v-else class="px-2.5 py-0.5 bg-red-950 text-red-400 border border-red-900/40 rounded-full text-[10px] font-extrabold uppercase">Nonaktif</span>
                                    </td>
                                    <td class="p-4 flex items-center justify-center gap-2">
                                        <button @click="toggleStatus(cust.id)" class="px-2.5 py-1.5 rounded-lg border text-[10px] font-extrabold uppercase transition" 
                                                :class="cust.status === 'aktif' ? 'bg-red-950/20 border-red-900/30 text-red-400 hover:bg-red-950' : 'bg-emerald-950/20 border-emerald-900/30 text-emerald-450 hover:bg-emerald-950'">
                                            {{ cust.status === 'aktif' ? 'Putus' : 'Aktifkan' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredCustomers.length === 0">
                                    <td colspan="7" class="p-8 text-center text-slate-500">
                                        Tidak menemukan data pelanggan yang sesuai dengan pencarian Anda.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Add Customer Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6 shadow-2xl relative">
                <button @click="isAddModalOpen = false" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <h3 class="text-base font-extrabold text-white uppercase tracking-wider">Pemasangan Wifi Baru</h3>
                <p class="text-xs text-slate-500 mt-1">Isi formulir berikut untuk menambahkan pelanggan baru.</p>

                <form @submit.prevent="addCustomer" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Lengkap</label>
                        <input v-model="newName" type="text" placeholder="Masukkan nama" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. WhatsApp</label>
                        <input v-model="newPhone" type="tel" placeholder="08..." class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Paket Kecepatan</label>
                        <select v-model="newPlan" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none">
                            <option value="Lite Desa">Lite Desa (Rp 150.000 / bln)</option>
                            <option value="Keluarga Harmoni">Keluarga Harmoni (Rp 250.000 / bln)</option>
                            <option value="Bisnis & UMKM">Bisnis & UMKM (Rp 450.000 / bln)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alamat Dusun / RT / RW</label>
                        <textarea v-model="newAddress" rows="2" placeholder="Masukkan alamat lengkap rumah" class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-xs text-slate-200 focus:border-indigo-500 focus:outline-none" required></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition">
                        Daftarkan Pelanggan Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
