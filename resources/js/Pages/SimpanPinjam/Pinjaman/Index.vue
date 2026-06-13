<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    pinjaman: Object,
    filters: Object,
    summaryAll: Object,
    summaryFiltered: Object,
    availableBulan: Array,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');
const bulan  = ref(props.filters?.bulan  ?? '');

let timeout;
watch([search, status, bulan], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('pinjaman.index'), {
            search: search.value,
            status: status.value,
            bulan:  bulan.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const statusClass = (s) => {
    if (s === 'aktif') return 'bg-blue-50 text-blue-700';
    if (s === 'macet') return 'bg-red-50 text-red-700';
    return 'bg-gray-100 text-gray-600';
};

// Format YYYY-MM ke nama bulan Indonesia
const formatBulanLabel = (val) => {
    if (!val) return '';
    const [y, m] = val.split('-');
    const date = new Date(y, parseInt(m) - 1, 1);
    return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const isFiltered = computed(() => !!bulan.value || !!status.value);
</script>

<template>
    <Head title="Data Pinjaman" />
    <AuthenticatedLayout>
        <template #header>Pinjaman</template>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Daftar Pinjaman</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ pinjaman.total }} data pinjaman{{ bulan ? ' pada ' + formatBulanLabel(bulan) : '' }}</p>
                </div>
                <Link :href="route('pinjaman.create')" class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 active:scale-95">
                    <span class="material-symbols-outlined text-base">add</span> Pinjaman Baru
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <!-- Jika ada filter: tampilkan summary hasil filter -->
                <template v-if="isFiltered">
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ bulan ? formatBulanLabel(bulan) : 'Filter Aktif' }} — Pinjaman</p>
                        <p class="mt-1 text-lg font-bold text-[color:var(--color-on-surface)]">{{ summaryFiltered?.total_pinjaman ?? 0 }} akad</p>
                    </div>
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ bulan ? formatBulanLabel(bulan) : 'Filter Aktif' }} — Total Pokok</p>
                        <p class="mt-1 text-base font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(summaryFiltered?.total_pokok) }}</p>
                    </div>
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ bulan ? formatBulanLabel(bulan) : 'Filter Aktif' }} — Total Tagihan</p>
                        <p class="mt-1 text-base font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(summaryFiltered?.total_tagihan) }}</p>
                    </div>
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ bulan ? formatBulanLabel(bulan) : 'Filter Aktif' }} — Sisa Tagihan</p>
                        <p class="mt-1 text-base font-bold text-red-600">{{ formatCurrency(summaryFiltered?.total_sisa) }}</p>
                    </div>
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ bulan ? formatBulanLabel(bulan) : 'Filter Aktif' }} — Sisa Pokok</p>
                        <p class="mt-1 text-base font-bold text-blue-600">{{ formatCurrency(summaryFiltered?.total_sisa_pokok) }}</p>
                    </div>
                </template>

                <!-- Summary total keseluruhan (selalu tampil) -->
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Pinjaman</p>
                    <p class="mt-1 text-lg font-bold text-[color:var(--color-on-surface)]">{{ summaryAll?.total_pinjaman ?? 0 }} akad</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Total Pokok</p>
                    <p class="mt-1 text-base font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(summaryAll?.total_pokok) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Total Tagihan</p>
                    <p class="mt-1 text-base font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(summaryAll?.total_tagihan) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Total Sisa</p>
                    <p class="mt-1 text-base font-bold text-red-500">{{ formatCurrency(summaryAll?.total_sisa) }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Sisa Pokok</p>
                    <p class="mt-1 text-base font-bold text-blue-600">{{ formatCurrency(summaryAll?.total_sisa_pokok) }}</p>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input v-model="search" type="text" placeholder="Cari nama atau nomor rekening..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                </div>
                <!-- Filter Bulan -->
                <select v-model="bulan" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none">
                    <option value="">Semua Bulan</option>
                    <option v-for="b in availableBulan" :key="b" :value="b">{{ formatBulanLabel(b) }}</option>
                </select>
                <!-- Filter Status -->
                <select v-model="status" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="macet">Macet</option>
                    <option value="lunas">Lunas</option>
                </select>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nasabah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal Akad</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Pokok</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Total Tagihan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Sisa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Angsuran</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="pinjaman.data.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data pinjaman</td>
                            </tr>
                            <tr v-for="row in pinjaman.data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ row.nasabah?.nama }}</p>
                                    <p class="font-mono text-xs text-[color:var(--color-secondary)]">{{ row.nasabah?.nomor_rekening }}</p>
                                </td>
                                <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal_akad) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ formatCurrency(row.pinjaman_pokok) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatCurrency(row.total_tagihan) }}</td>
                                <td class="px-4 py-3 text-right" :class="row.sisa_pinjaman > 0 ? 'text-red-600 font-semibold' : 'text-blue-600'">{{ formatCurrency(row.sisa_pinjaman) }}</td>
                                <td class="px-4 py-3 text-center text-xs text-[color:var(--color-secondary)]">{{ row.jumlah_angsuran }} kali / {{ formatCurrency(row.nominal_setoran) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusClass(row.status)">{{ row.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('pinjaman.show', row.id)" class="rounded-lg p-1.5 text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)]">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="pinjaman.last_page > 1" class="flex items-center justify-between border-t border-[color:var(--color-outline-variant)] px-4 py-3">
                    <p class="text-sm text-[color:var(--color-secondary)]">{{ pinjaman.from }}–{{ pinjaman.to }} dari {{ pinjaman.total }}</p>
                    <div class="flex gap-1">
                        <Link v-for="link in pinjaman.links" :key="link.label" :href="link.url ?? '#'" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active ? 'bg-[color:var(--color-primary)] text-white' : link.url ? 'text-[color:var(--color-on-surface-variant)] hover:bg-[color:var(--color-surface-container)]' : 'cursor-not-allowed text-[color:var(--color-outline-variant)]'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
