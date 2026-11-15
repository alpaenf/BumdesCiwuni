<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    title: { type: String, default: 'Dashboard' },
    unit: { type: Object, default: () => ({ nama_unit: 'Unit Perdagangan Besar', slug: 'perdagangan-besar' }) },
});

const showingSidebar = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);

const isSuperAdmin = computed(() => user.value?.role === 'admin');
const isManagerPusat = computed(() => user.value?.role === 'manager_pusat');
const isAdmin = computed(() => isSuperAdmin.value || user.value?.role === 'admin_unit');

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-[color:var(--color-background)]">
        <Head :title="title + ' - Unit Perdagangan Besar BUMDes Ciwuni'" />

        <!-- Backdrop for mobile -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-30 bg-black/30 lg:hidden"
            @click="showingSidebar = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-40 flex h-screen w-[260px] -translate-x-full flex-col border-r border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface)] transition-transform duration-300 lg:translate-x-0"
            :class="{ 'translate-x-0': showingSidebar }"
        >
            <!-- Logo & Unit Header -->
            <div class="flex h-16 items-center justify-between border-b border-[color:var(--color-outline-variant)] px-5">
                <div>
                    <p class="text-xs font-semibold leading-tight text-[color:var(--color-on-surface)]">Perdagangan Besar</p>
                    <p class="text-[10px] text-[color:var(--color-secondary)]">BUMDes Dammar Wulan</p>
                </div>
                <!-- Close button for mobile -->
                <button
                    class="rounded-lg p-1 text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)] lg:hidden"
                    @click="showingSidebar = false"
                >
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4">
                <!-- Group: Utama -->
                <div class="px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Utama</p>
                    <Link
                        :href="route('pb.dashboard')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.endsWith('Dashboard')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.endsWith('Dashboard') ? '\'FILL\' 1' : '\'FILL\' 0' }">dashboard</span>
                        <span>Dashboard</span>
                    </Link>
                </div>

                <!-- Group: Layanan Transaksi -->
                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Layanan Transaksi</p>
                    
                    <!-- Laku Pandai -->
                    <Link
                        :href="route('pb.laku-pandai.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('LakuPandai/Index')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('LakuPandai/Index') ? '\'FILL\' 1' : '\'FILL\' 0' }">account_balance</span>
                        <span>Laku Pandai</span>
                    </Link>

                    <Link
                        :href="route('pb.laku-pandai.nasabah.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('LakuPandai/Nasabah')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('LakuPandai/Nasabah') ? '\'FILL\' 1' : '\'FILL\' 0' }">people</span>
                        <span>Nasabah Laku Pandai</span>
                    </Link>

                    <!-- Samsat Budiman -->
                    <Link
                        :href="route('pb.samsat.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Samsat/Index')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Samsat/Index') ? '\'FILL\' 1' : '\'FILL\' 0' }">two_wheeler</span>
                        <span>Samsat Budiman</span>
                    </Link>

                    <Link
                        :href="route('pb.samsat.wajib-pajak.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Samsat/WajibPajak')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Samsat/WajibPajak') ? '\'FILL\' 1' : '\'FILL\' 0' }">badge</span>
                        <span>Database Wajib Pajak</span>
                    </Link>

                    <!-- Pajak PBB -->
                    <Link
                        :href="route('pb.pbb.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Pbb/Index')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Pbb/Index') ? '\'FILL\' 1' : '\'FILL\' 0' }">home_work</span>
                        <span>Pajak PBB</span>
                    </Link>

                    <!-- Transaksi Umum -->
                    <Link
                        :href="route('pb.transaksi-umum.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('TransaksiUmum/Index')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('TransaksiUmum/Index') ? '\'FILL\' 1' : '\'FILL\' 0' }">receipt_long</span>
                        <span>Transaksi Umum / PPOB</span>
                    </Link>
                </div>

                <!-- Group: Keuangan & Kas -->
                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Keuangan & Kas</p>
                    <Link
                        :href="route('pb.kas.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Keuangan/Kas')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Keuangan/Kas') ? '\'FILL\' 1' : '\'FILL\' 0' }">account_balance_wallet</span>
                        <span>Buku Kas Masuk / Keluar</span>
                    </Link>

                    <Link
                        :href="route('pb.biaya.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Keuangan/BiayaOperasional')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Keuangan/BiayaOperasional') ? '\'FILL\' 1' : '\'FILL\' 0' }">price_change</span>
                        <span>Biaya Operasional</span>
                    </Link>
                </div>

                <!-- Group: Laporan & Dokumen -->
                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Laporan & Dokumen</p>
                    <Link
                        :href="route('pb.laporan.index')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="$page.component.includes('Laporan/Index')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: $page.component.includes('Laporan/Index') ? '\'FILL\' 1' : '\'FILL\' 0' }">bar_chart</span>
                        <span>Laporan & Rekapitulasi</span>
                    </Link>
                </div>

                <!-- Group: Pengaturan -->
                <div v-if="isAdmin" class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Pengaturan</p>
                    <Link
                        :href="route('unit.settings.edit', { slug: 'perdagangan-besar' })"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="($page.component.includes('LandingPage') || $page.component.includes('UnitSettings'))
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">web</span>
                        <span>Landing Page Unit</span>
                    </Link>
                </div>

                <!-- Group: Portal BUMDes -->
                <div v-if="isSuperAdmin || isManagerPusat" class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Portal BUMDes</p>
                    <Link
                        :href="route('portal.exec.dashboard')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">hub</span>
                        <span>Dashboard Portal</span>
                    </Link>
                    <Link
                        v-if="isSuperAdmin"
                        :href="route('portal.cms.dashboard')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        <span>Kelola Website</span>
                    </Link>
                    <a
                        :href="route('welcome')"
                        target="_blank"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">savings</span>
                        <span>Unit Simpan Pinjam</span>
                    </a>
                    <a
                        :href="route('unit.welcome', { slug: 'wifi' })"
                        target="_blank"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">wifi</span>
                        <span>Unit Wifi</span>
                    </a>
                    <a
                        :href="route('unit.welcome', { slug: 'ketahanan-pangan' })"
                        target="_blank"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">agriculture</span>
                        <span>Unit Ketahanan Pangan</span>
                    </a>
                </div>
            </nav>

            <!-- User section at bottom -->
            <div class="border-t border-[color:var(--color-outline-variant)] p-3">
                <Link
                    :href="route('profile.edit')"
                    class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[color:var(--color-on-surface-variant)] transition hover:bg-[color:var(--color-surface-container)]"
                    @click="showingSidebar = false"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)]">
                        <span class="material-symbols-outlined text-base">person</span>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="truncate text-xs font-semibold text-[color:var(--color-on-surface)]">{{ user?.nama || 'Admin BUMDes' }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-[color:var(--color-secondary)]">
                            {{ user?.role === 'admin' ? 'SUPER ADMIN' : user?.role === 'manager_pusat' ? 'MANAGER PUSAT' : user?.role === 'admin_unit' ? 'ADMIN UNIT' : 'ADMIN' }}
                        </p>
                    </div>
                </Link>
                <button
                    @click="logout"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[color:var(--color-error)] transition hover:bg-red-50"
                >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="ml-0 flex flex-col lg:ml-[260px]">
            <!-- Topbar -->
            <header class="fixed left-0 right-0 top-0 z-30 flex h-16 items-center justify-between border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface)] px-4 lg:left-[260px]">
                <div class="flex items-center gap-3">
                    <button
                        class="rounded-lg p-2 text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)] lg:hidden"
                        @click="showingSidebar = !showingSidebar"
                    >
                        <span class="material-symbols-outlined text-xl">menu</span>
                    </button>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-semibold text-[color:var(--color-on-surface)]">
                            {{ title }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div v-if="isAdmin" class="hidden items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-700">Administrator</span>
                    </div>
                    <div v-else class="hidden items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-700">Manager</span>
                    </div>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex h-9 w-9 items-center justify-center rounded-full bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)] hover:opacity-80 transition">
                                <span class="material-symbols-outlined text-xl">person</span>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profil Saya</DropdownLink>
                            <DropdownLink :href="route('unit.welcome', { slug: 'perdagangan-besar' })" target="_blank">Lihat Landing Page</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page content -->
            <main class="min-h-screen bg-[color:var(--color-background)] px-4 pb-10 pt-20 sm:px-6">
                <div class="mx-auto w-full max-w-[1440px]">
                    <!-- Flash Message -->
                    <div v-if="$page.props.flash?.success" class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
                        <span>{{ $page.props.flash.success }}</span>
                    </div>
                    <div v-if="$page.props.flash?.error" class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
                        <span>{{ $page.props.flash.error }}</span>
                    </div>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
