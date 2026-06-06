<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ angsuran: Object, nasabahOptions: Array, filters: Object });
const search = ref(props.filters?.search ?? '');
const startDate = ref(props.filters?.start_date ?? '');
const endDate   = ref(props.filters?.end_date ?? '');

let timeout;
const applyFilter = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('angsuran.index'), { search: search.value, start_date: startDate.value, end_date: endDate.value }, { preserveState: true, replace: true });
    }, 400);
};
watch([search, startDate, endDate], applyFilter);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const pasaranLabel = { legi: 'Legi', pahing: 'Pahing', pon: 'Pon', wage: 'Wage', kliwon: 'Kliwon' };
</script>

<template>
    <Head title="Riwayat Angsuran" />
    <AuthenticatedLayout>
        <template #header>Angsuran</template>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Riwayat Angsuran</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ angsuran.total }} transaksi</p>
                </div>
                <Link :href="route('angsuran.create')" class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    <span class="material-symbols-outlined text-base">payments</span> Bayar Angsuran
                </Link>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input v-model="search" type="text" placeholder="Cari nama nasabah..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                </div>
                <input v-model="startDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
                <input v-model="endDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
            </div>

            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nasabah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Ke-</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Hari Pasaran</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Jumlah Bayar</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Sisa Pinjaman</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="angsuran.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data angsuran</td>
                            </tr>
                            <tr v-for="row in angsuran.data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ row.pinjaman?.nasabah?.nama }}</p>
                                    <p class="font-mono text-xs text-[color:var(--color-secondary)]">{{ row.pinjaman?.nasabah?.nomor_rekening }}</p>
                                </td>
                                <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal) }}</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ row.angsuran_ke }}</td>
                                <td class="px-4 py-3 capitalize text-[color:var(--color-secondary)]">{{ pasaranLabel[row.pasaran] ?? row.pasaran }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ formatCurrency(row.jumlah_bayar) }}</td>
                                <td class="px-4 py-3 text-right" :class="Number(row.sisa_pinjaman) <= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(row.sisa_pinjaman) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a :href="route('angsuran.struk', row.id)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-[color:var(--color-surface-container)] px-2.5 py-1 text-xs hover:bg-[color:var(--color-surface-container-high)]">
                                        <span class="material-symbols-outlined text-xs">print</span> Print
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="angsuran.last_page > 1" class="flex items-center justify-between border-t border-[color:var(--color-outline-variant)] px-4 py-3">
                    <p class="text-sm text-[color:var(--color-secondary)]">{{ angsuran.from }}–{{ angsuran.to }} dari {{ angsuran.total }}</p>
                    <div class="flex gap-1">
                        <Link v-for="link in angsuran.links" :key="link.label" :href="link.url ?? '#'" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active ? 'bg-[color:var(--color-primary)] text-white' : link.url ? 'hover:bg-[color:var(--color-surface-container)]' : 'cursor-not-allowed text-[color:var(--color-outline-variant)]'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
