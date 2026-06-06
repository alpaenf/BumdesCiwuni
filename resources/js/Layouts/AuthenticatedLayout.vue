<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);

// Super Admin = role 'admin' — bisa akses CMS & kelola pengguna. Manager pusat bisa lihat laporan
const isSuperAdmin = computed(() => user.value?.isSuperAdmin ?? user.value?.role === 'admin');
const isManagerPusat = computed(() => user.value?.role === 'manager_pusat');

// Admin level dalam unit (admin_unit atau super admin)
const isAdmin = computed(() => isSuperAdmin.value || user.value?.role === 'admin_unit');

const mainNav = [
    { name: 'Dashboard', route: 'dashboard', icon: 'dashboard' },
    { name: 'Data Nasabah', route: 'nasabah.index', icon: 'people' },
    { name: 'Tabungan Reguler', route: 'tabungan.index', icon: 'savings' },
    { name: 'Tabungan Sembako', route: 'tabungan-sembako.index', icon: 'shopping_basket' },
    { name: 'Periode Tabungan', route: 'periode-tabungan.index', icon: 'date_range' },
    { name: 'Pinjaman', route: 'pinjaman.index', icon: 'account_balance' },
    { name: 'Angsuran', route: 'angsuran.index', icon: 'payments' },
];

const reportNav = [
    { name: 'Buku Tabungan', route: 'buku-tabungan.index', icon: 'menu_book' },
    { name: 'Tunggakan', route: 'tunggakan.index', icon: 'warning' },
    { name: 'Kwitansi', route: 'kwitansi.index', icon: 'receipt_long' },
    { name: 'Laporan', route: 'laporan.index', icon: 'bar_chart' },
];

const adminNav = [
    { name: 'Pengaturan Tabungan', route: 'pengaturan.tabungan', icon: 'tune' },
    { name: 'Landing Page Unit', route: 'admin.landing-page.edit', icon: 'web' },
    { name: 'Galeri Unit', route: 'admin.galeri.index', icon: 'photo_library' },
];
</script>

<template>
    <div class="min-h-screen bg-[color:var(--color-background)]">
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
            <!-- Logo -->
            <div class="flex h-16 items-center gap-1.5 border-b border-[color:var(--color-outline-variant)] px-5">
                <img src="/logo.png" alt="Logo" class="h-9 w-9 object-contain" />
                <div>
                    <p class="text-xs font-semibold leading-tight text-[color:var(--color-on-surface)]">Simpan Pinjam</p>
                    <p class="text-[10px] text-[color:var(--color-secondary)]">BUMDes Dammar Wulan</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <div class="px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Utama</p>
                    <NavLink
                        v-for="item in mainNav"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current(item.route)
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: route().current(item.route) ? '\'FILL\' 1' : '\'FILL\' 0' }">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </NavLink>
                </div>

                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Laporan & Dokumen</p>
                    <NavLink
                        v-for="item in reportNav"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current(item.route)
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: route().current(item.route) ? '\'FILL\' 1' : '\'FILL\' 0' }">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </NavLink>
                </div>

                <div v-if="isAdmin" class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Pengaturan</p>
                    <NavLink
                        v-for="item in adminNav"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current(item.route)
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </NavLink>
                </div>

                <div v-if="isSuperAdmin || isManagerPusat" class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Portal BUMDes</p>
                    <NavLink
                        :href="route('portal.exec.dashboard')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">hub</span>
                        <span>Dashboard Portal</span>
                    </NavLink>
                    <NavLink
                        v-if="isSuperAdmin"
                        :href="route('portal.cms.dashboard')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        <span>Kelola Website</span>
                    </NavLink>
                </div>

                <div v-if="isSuperAdmin" class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Pengaturan</p>
                    <NavLink
                        :href="route('admin.landing-page.edit')"
                        :active="route().current('admin.landing-page.edit')"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current('admin.landing-page.edit')
                            ? 'bg-primary text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">settings_applications</span>
                        <span>Konten Landing Page</span>
                    </NavLink>
                </div>
            </nav>

            <!-- User section -->
            <div class="border-t border-[color:var(--color-outline-variant)] p-3">
                <NavLink
                    :href="route('profile.edit')"
                    class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[color:var(--color-on-surface-variant)] transition hover:bg-[color:var(--color-surface-container)]"
                    @click="showingSidebar = false"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)]">
                        <span class="material-symbols-outlined text-base">person</span>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="truncate text-xs font-semibold text-[color:var(--color-on-surface)]">{{ user?.nama }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-[color:var(--color-secondary)]">
                            {{ user?.role === 'admin' ? 'Super Admin' : user?.role === 'manager_pusat' ? 'Manager Pusat' : user?.role === 'admin_unit' ? 'Admin Unit' : user?.role === 'manager' ? 'Manager' : user?.role }}
                        </p>
                    </div>
                </NavLink>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[color:var(--color-error)] transition hover:bg-red-50"
                >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Keluar</span>
                </Link>
            </div>
        </aside>

        <!-- Main content -->
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
                    <div v-if="$slots.header" class="flex items-center gap-2">
                        <h1 class="text-base font-semibold text-[color:var(--color-on-surface)]">
                            <slot name="header" />
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div v-if="isAdmin" class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold text-emerald-700">Administrator</span>
                    </div>
                    <div v-else class="hidden items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-700">Manager</span>
                    </div>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex h-9 w-9 items-center justify-center rounded-full bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)]">
                                <span class="material-symbols-outlined text-xl">person</span>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profil Saya</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page content -->
            <main class="min-h-screen bg-[color:var(--color-background)] px-4 pb-10 pt-20 sm:px-6">
                <div class="mx-auto w-full max-w-[1440px]">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
