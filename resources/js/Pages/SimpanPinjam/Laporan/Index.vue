<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const reports = [
    { title: 'Laporan Nasabah', desc: 'Data nasabah berdasarkan periode bergabung', icon: 'people', route: 'laporan.nasabah', color: 'blue' },
    { title: 'Laporan Tabungan', desc: 'Rekapitulasi setoran dan penarikan tabungan', icon: 'savings', route: 'laporan.tabungan', color: 'emerald' },
    { title: 'Laporan Pinjaman', desc: 'Status pinjaman aktif, lunas, dan bermasalah', icon: 'account_balance', route: 'laporan.pinjaman', color: 'purple' },
    { title: 'Laporan Angsuran', desc: 'Riwayat pembayaran angsuran per periode', icon: 'payments', route: 'laporan.angsuran', color: 'orange' },
    { title: 'Laporan Kas', desc: 'Arus kas masuk dan keluar BUMDes', icon: 'account_balance_wallet', route: 'laporan.kas', color: 'teal' },
];

const colorMap = {
    blue:    { bg: 'bg-blue-50', text: 'text-blue-600', btn: 'bg-blue-600', border: 'border-blue-200' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600', btn: 'bg-emerald-600', border: 'border-emerald-200' },
    purple:  { bg: 'bg-purple-50', text: 'text-purple-600', btn: 'bg-purple-600', border: 'border-purple-200' },
    orange:  { bg: 'bg-orange-50', text: 'text-orange-600', btn: 'bg-orange-600', border: 'border-orange-200' },
    teal:    { bg: 'bg-teal-50', text: 'text-teal-600', btn: 'bg-teal-600', border: 'border-teal-200' },
};
</script>

<template>
    <Head title="Laporan" />
    <AuthenticatedLayout>
        <template #header>Laporan</template>
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold">Modul Laporan</h2>
                <p class="mt-1 text-sm text-[color:var(--color-secondary)]">Pilih jenis laporan yang ingin dicetak atau diekspor.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="r in reports"
                    :key="r.route"
                    :href="route(r.route)"
                    class="group rounded-xl border p-6 shadow-sm transition hover:shadow-md"
                    :class="[colorMap[r.color].bg, colorMap[r.color].border]"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl" :class="colorMap[r.color].btn + '/10'">
                        <span class="material-symbols-outlined text-2xl" :class="colorMap[r.color].text" style="font-variation-settings: 'FILL' 1">{{ r.icon }}</span>
                    </div>
                    <h3 class="font-semibold text-[color:var(--color-on-surface)]">{{ r.title }}</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-secondary)]">{{ r.desc }}</p>
                    <div class="mt-4 flex items-center gap-1 text-sm font-semibold" :class="colorMap[r.color].text">
                        <span>Lihat Laporan</span>
                        <span class="material-symbols-outlined text-base transition group-hover:translate-x-1">arrow_forward</span>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
