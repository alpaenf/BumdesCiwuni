<script setup>
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);

const cmsNav = [
    { name: 'Dashboard CMS', route: 'portal.cms.dashboard', icon: 'space_dashboard' },
    { name: 'Profil BUMDes', route: 'portal.cms.profil.edit', icon: 'apartment' },
    { name: 'Kelola Banner', route: 'portal.cms.banner.edit', icon: 'view_carousel' },
    { name: 'Kelola Berita', route: 'portal.cms.berita.index', icon: 'newspaper' },
    { name: 'Kelola Unit Usaha', route: 'portal.cms.unit.index', icon: 'business' },
    { name: 'Akses Admin', route: 'portal.cms.users.index', icon: 'admin_panel_settings' },
];

const portalNav = [
    { name: 'Dashboard Eksekutif', route: 'portal.exec.dashboard', icon: 'monitoring' },
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
            <div class="flex h-16 items-center gap-2 border-b border-[color:var(--color-outline-variant)] px-5">
                <img src="/logo2.png" alt="Logo" class="h-9 w-9 object-contain" />
                <div>
                    <p class="text-xs font-bold leading-tight text-[color:var(--color-on-surface)]">Portal BUMDes</p>
                    <p class="text-[10px] text-[color:var(--color-secondary)]">Dammar Wulan</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <!-- Portal Section -->
                <div class="px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Portal</p>
                    <NavLink
                        v-for="item in portalNav"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current(item.route)
                            ? 'bg-emerald-600 text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: route().current(item.route) ? '\'FILL\' 1' : '\'FILL\' 0' }">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </NavLink>
                </div>

                <!-- CMS Section -->
                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Kelola Website</p>
                    <NavLink
                        v-for="item in cmsNav"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all"
                        :class="route().current(item.route)
                            ? 'bg-emerald-600 text-white font-semibold shadow-sm'
                            : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]" :style="{ fontVariationSettings: route().current(item.route) ? '\'FILL\' 1' : '\'FILL\' 0' }">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </NavLink>
                </div>

                <!-- Quick Links -->
                <div class="mt-4 px-3">
                    <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.15em] text-[color:var(--color-secondary)]">Akses Cepat</p>
                    <a
                        :href="route('welcome')"
                        target="_blank"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                        @click="showingSidebar = false"
                    >
                        <span class="material-symbols-outlined text-[18px]">savings</span>
                        <span>Unit Simpan Pinjam</span>
                        <span class="ml-auto text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">Aktif</span>
                    </a>
                    <a
                        href="/"
                        target="_blank"
                        class="relative mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-all"
                    >
                        <span class="material-symbols-outlined text-[18px]">public</span>
                        <span>Lihat Website</span>
                        <span class="material-symbols-outlined text-[14px] ml-auto opacity-50">open_in_new</span>
                    </a>
                </div>
            </nav>

            <!-- User section -->
            <div class="border-t border-[color:var(--color-outline-variant)] p-3">
                <div class="mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-[color:var(--color-on-surface-variant)]">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600/10 text-emerald-600">
                        <span class="material-symbols-outlined text-base">person</span>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="truncate text-xs font-semibold text-[color:var(--color-on-surface)]">{{ user?.nama }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-[color:var(--color-secondary)]">{{ user?.role }}</p>
                    </div>
                </div>
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
                    <div class="hidden items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 sm:flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold text-emerald-700">Portal Admin</span>
                    </div>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600/10 text-emerald-600">
                                <span class="material-symbols-outlined text-xl">person</span>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('portal.exec.dashboard')">Dashboard Eksekutif</DropdownLink>
                            <DropdownLink :href="route('dashboard')">Unit Simpan Pinjam</DropdownLink>
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
