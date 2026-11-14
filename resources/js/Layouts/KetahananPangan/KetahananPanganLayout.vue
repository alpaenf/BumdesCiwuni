<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, default: 'Ketahanan Pangan' },
    unit: { type: Object, default: () => ({ name: 'Unit Ketahanan Pangan', slug: 'ketahanan-pangan' }) },
    user: { type: Object, default: () => null },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || props.user || {});
const isSuperAdmin = computed(() => authUser.value?.role === 'admin' || authUser.value?.role === 'manager_pusat' || authUser.value?.isSuperAdmin);

const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <Head :title="title + ' - Admin Pangan BUMDes Ciwuni'" />

        <!-- Overlay Mobile -->
        <div 
            v-if="isMobileSidebarOpen" 
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
            @click="isMobileSidebarOpen = false"
        ></div>

        <!-- Sidebar Putih Bersih (Clean White Theme) -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white text-slate-700 flex flex-col transition-all duration-300 shadow-md border-r border-slate-200',
                isSidebarOpen ? 'w-64' : 'w-20',
                isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo & Unit Header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold shadow-md shadow-emerald-600/20 shrink-0">
                        <span class="material-symbols-outlined notranslate text-2xl">agriculture</span>
                    </div>
                    <div v-show="isSidebarOpen" class="transition-opacity duration-200">
                        <h1 class="text-sm font-bold text-slate-900 tracking-wide">Admin Pangan</h1>
                        <p class="text-xs text-emerald-600 font-semibold">BUMDes Ciwuni</p>
                    </div>
                </div>
                <button 
                    @click="isSidebarOpen = !isSidebarOpen" 
                    class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                >
                    <span class="material-symbols-outlined notranslate text-xl">
                        {{ isSidebarOpen ? 'chevron_left' : 'chevron_right' }}
                    </span>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                <!-- Group: Main -->
                <div>
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Utama
                    </div>
                    <div class="space-y-1">
                        <Link 
                            :href="route('kp.hewani.dashboard')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/DashboardHewani')
                                    ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-semibold'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">dashboard</span>
                            <span v-show="isSidebarOpen">Dashboard Utama</span>
                        </Link>
                    </div>
                </div>

                <!-- Group: Unit Hewani -->
                <div>
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-emerald-700 uppercase tracking-wider">
                        Unit Hewani
                    </div>
                    <div class="space-y-1.5">
                        <Link 
                            :href="route('kp.customers.index')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/Pelanggan') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">group</span>
                            <span v-show="isSidebarOpen">Data Pelanggan</span>
                        </Link>

                        <Link 
                            :href="route('kp.feed.index')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/LogistikPakan') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">inventory_2</span>
                            <span v-show="isSidebarOpen">Logistik Pakan</span>
                        </Link>

                        <Link 
                            :href="route('kp.production.index')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/ProduksiTelur') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">egg</span>
                            <span v-show="isSidebarOpen">Produksi Telur</span>
                        </Link>

                        <Link 
                            :href="route('kp.sale.index')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/PenjualanTelur') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">shopping_cart</span>
                            <span v-show="isSidebarOpen">Penjualan Telur</span>
                        </Link>
                    </div>
                </div>

                <!-- Group: Keuangan & Laporan Hewani -->
                <div>
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Keuangan & Laporan
                    </div>
                    <div class="space-y-1.5">
                        <Link 
                            :href="route('kp.finance.pendapatan')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/Pendapatan') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">trending_up</span>
                            <span v-show="isSidebarOpen">Pendapatan</span>
                        </Link>

                        <Link 
                            :href="route('kp.finance.pengeluaran')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/Pengeluaran') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">trending_down</span>
                            <span v-show="isSidebarOpen">Pengeluaran</span>
                        </Link>

                        <Link 
                            :href="route('kp.report.arus-kas')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/LaporanArusKas') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">account_balance_wallet</span>
                            <span v-show="isSidebarOpen">Laporan Arus Kas</span>
                        </Link>

                        <Link 
                            :href="route('kp.report.laba-rugi')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/LaporanLabaRugi') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">assessment</span>
                            <span v-show="isSidebarOpen">Laporan Laba Rugi</span>
                        </Link>

                        <Link 
                            :href="route('kp.report.produksi')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/LaporanProduksi') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">description</span>
                            <span v-show="isSidebarOpen">Laporan Produksi</span>
                        </Link>

                        <Link 
                            :href="route('kp.report.penjualan')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Hewani/LaporanPenjualan') 
                                    ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">receipt_long</span>
                            <span v-show="isSidebarOpen">Laporan Penjualan</span>
                        </Link>
                    </div>
                </div>

                <!-- Group: Unit Nabati -->
                <div>
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-teal-700 uppercase tracking-wider">
                        Unit Nabati
                    </div>
                    <div class="space-y-1.5">
                        <Link 
                            :href="route('kp.nabati.dashboard')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Nabati/DashboardNabati') 
                                    ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">spa</span>
                            <span v-show="isSidebarOpen">Dashboard Nabati</span>
                        </Link>

                        <Link 
                            :href="route('kp.nabati.komoditas')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Nabati/Komoditas') 
                                    ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">category</span>
                            <span v-show="isSidebarOpen">Data Komoditas</span>
                        </Link>

                        <Link 
                            :href="route('kp.nabati.produksi')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Nabati/ProduksiPanen') 
                                    ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">agriculture</span>
                            <span v-show="isSidebarOpen">Produksi Panen</span>
                        </Link>

                        <Link 
                            :href="route('kp.nabati.penjualan')"
                            :class="[
                                'flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm transition-all relative overflow-hidden',
                                $page.component.includes('Nabati/PenjualanNabati') 
                                    ? 'bg-teal-600 text-white font-bold shadow-md shadow-teal-600/25' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            ]"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">point_of_sale</span>
                            <span v-show="isSidebarOpen">Penjualan Nabati</span>
                        </Link>
                    </div>
                </div>

                <!-- Unit Settings -->
                <div>
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Pengaturan
                    </div>
                    <div class="space-y-1">
                        <a 
                            :href="route('unit.settings.edit', { slug: 'ketahanan-pangan' })"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">settings</span>
                            <span v-show="isSidebarOpen">Landing Page</span>
                        </a>

                        <a 
                            :href="route('unit.welcome', { slug: 'ketahanan-pangan' })"
                            target="_blank"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">open_in_new</span>
                            <span v-show="isSidebarOpen">Lihat Publik</span>
                        </a>
                    </div>
                </div>

                <!-- Section Navigasi Portal BUMDes (Khusus Super Admin / Manager Pusat) Sesuai Gambar -->
                <div v-if="isSuperAdmin" class="pt-2 border-t border-slate-100">
                    <div v-show="isSidebarOpen" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        PORTAL BUMDES
                    </div>
                    <div class="space-y-1">
                        <Link 
                            :href="route('portal.exec.dashboard')"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">hub</span>
                            <span v-show="isSidebarOpen">Dashboard Portal</span>
                        </Link>

                        <Link 
                            v-if="authUser?.role === 'admin'"
                            :href="route('portal.cms.dashboard')"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">edit_note</span>
                            <span v-show="isSidebarOpen">Kelola Website</span>
                        </Link>

                        <a 
                            :href="route('unit.welcome', { slug: 'wifi' })" 
                            target="_blank"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">wifi</span>
                            <span v-show="isSidebarOpen">Unit Wifi</span>
                        </a>

                        <a 
                            :href="route('unit.welcome', { slug: 'ketahanan-pangan' })" 
                            target="_blank"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">agriculture</span>
                            <span v-show="isSidebarOpen">Unit Ketahanan Pangan</span>
                        </a>

                        <a 
                            :href="route('unit.welcome', { slug: 'perdagangan-besar' })" 
                            target="_blank"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            <span class="material-symbols-outlined notranslate text-xl">local_shipping</span>
                            <span v-show="isSidebarOpen">Unit Perdagangan Besar</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Footer (Sesuai Gambar 1 & 2) -->
            <div class="p-3 border-t border-slate-200 bg-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                            <span class="material-symbols-outlined notranslate text-xl">person</span>
                        </div>
                        <div v-show="isSidebarOpen" class="overflow-hidden">
                            <p class="text-xs font-bold text-slate-900 truncate">
                                {{ authUser?.nama || authUser?.name || 'Admin BUMDes' }}
                            </p>
                            <p class="text-[9.5px] font-semibold text-slate-500 uppercase tracking-wider truncate">
                                {{ authUser?.role === 'admin' ? 'SUPER ADMIN' : (authUser?.role === 'manager_pusat' ? 'MANAGER PUSAT' : (authUser?.email || 'ciwuni@desa.id')) }}
                            </p>
                        </div>
                    </div>
                    <button 
                        @click="logout"
                        title="Keluar"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                    >
                        <span class="material-symbols-outlined notranslate text-xl">logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div 
            :class="[
                'flex-1 flex flex-col min-w-0 transition-all duration-300',
                isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20'
            ]"
        >
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-6 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <button 
                        @click="isMobileSidebarOpen = true" 
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                    >
                        <span class="material-symbols-outlined notranslate text-2xl">menu</span>
                    </button>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">{{ title }}</h2>
                        <p class="text-xs text-slate-500 hidden sm:block">Sistem Informasi Unit Ketahanan Pangan BUMDes Ciwuni</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a 
                        :href="route('portal.home')"
                        class="text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 px-3 py-1.5 rounded-lg hover:bg-slate-200 transition-colors"
                    >
                        Portal BUMDes
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
