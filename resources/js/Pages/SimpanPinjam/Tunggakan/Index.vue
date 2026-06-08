<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const submit = () => {
    form.get(route('tunggakan.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportFilters = computed(() => {
    const payload = {
        search: form.search || undefined,
        status: form.status || undefined,
        start_date: form.start_date || undefined,
        end_date: form.end_date || undefined,
    };

    Object.keys(payload).forEach((key) => {
        if (!payload[key]) {
            delete payload[key];
        }
    });

    return payload;
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
</script>

<template>
    <Head title="Tunggakan Pinjaman" />

    <AuthenticatedLayout>
        <template #header>Tunggakan Pinjaman</template>

        <div class="space-y-6">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold">Monitor Tunggakan</h3>
                        <p class="text-sm text-slate-500">Pantau status keterlambatan pinjaman dan kredit macet.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            :href="route('tunggakan.pdf', exportFilters)"
                            target="_blank"
                            class="rounded-lg border border-blue-200 px-4 py-2 text-sm text-blue-700"
                        >
                            Export PDF
                        </a>
                        <a
                            :href="route('tunggakan.excel', exportFilters)"
                            class="rounded-lg border border-blue-200 px-4 py-2 text-sm text-blue-700"
                        >
                            Export Excel
                        </a>
                        <a
                            :href="route('tunggakan.print', exportFilters)"
                            target="_blank"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white"
                        >
                            Cetak Laporan
                        </a>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Cari Nasabah</label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Nama atau nomor rekening"
                            class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Status</label>
                        <select v-model="form.status" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm">
                            <option value="">Semua Status</option>
                            <option value="lancar">Lancar</option>
                            <option value="perhatian-khusus">Perhatian Khusus</option>
                            <option value="menunggak">Menunggak</option>
                            <option value="kredit-macet">Kredit Macet</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Periode Mulai</label>
                        <input v-model="form.start_date" type="date" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Periode Akhir</label>
                        <input v-model="form.end_date" type="date" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm" />
                    </div>
                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-blue-50 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-blue-600">Nasabah Lancar</p>
                    <p class="mt-2 text-xl font-semibold text-blue-700">{{ summary.lancar }}</p>
                </div>
                <div class="rounded-2xl bg-orange-50 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-orange-600">Nasabah Menunggak</p>
                    <p class="mt-2 text-xl font-semibold text-orange-700">{{ summary.menunggak }}</p>
                </div>
                <div class="rounded-2xl bg-red-50 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-red-600">Kredit Macet</p>
                    <p class="mt-2 text-xl font-semibold text-red-700">{{ summary.kredit_macet }}</p>
                </div>
                <div class="rounded-2xl bg-white border border-outline-variant p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-600">Total Piutang Bermasalah</p>
                    <p class="mt-2 text-xl font-semibold text-slate-700">{{ formatCurrency(summary.total_piutang_bermasalah) }}</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Daftar Tunggakan</h3>
                    <span class="text-sm text-slate-500">{{ items.length }} pinjaman</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs font-semibold uppercase tracking-normal text-slate-900">
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Nasabah</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Nomor Rekening</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Tanggal Akad</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Pinjaman Pokok</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Bunga</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Total Tagihan</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Tenor</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Angsuran Terbayar</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Sisa Pinjaman</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Pasaran Terakhir</th>
                                <th class="py-3 pe-4 whitespace-normal leading-tight">Tanggal Terakhir Bayar</th>
                                <th class="py-3 whitespace-normal leading-tight">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id" class="border-b last:border-none">
                                <td class="py-3 pe-4">{{ item.nama }}</td>
                                <td class="py-3 pe-4">{{ item.nomor_rekening }}</td>
                                <td class="py-3 pe-4">{{ item.tanggal_akad }}</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.pinjaman_pokok) }}</td>
                                <td class="py-3 pe-4">{{ item.bunga }}%</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.total_tagihan) }}</td>
                                <td class="py-3 pe-4">{{ item.tenor }}x</td>
                                <td class="py-3 pe-4">{{ item.angsuran_terbayar }}</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.sisa_pinjaman) }}</td>
                                <td class="py-3 pe-4">{{ item.pasaran_terakhir || '-' }}</td>
                                <td class="py-3 pe-4">{{ item.tanggal_terakhir_bayar || '-' }}</td>
                                <td class="py-3">
                                    <StatusBadge :status="item.status" />
                                </td>
                            </tr>
                            <tr v-if="!items.length">
                                <td colspan="12" class="py-6 text-center text-sm text-slate-500">Belum ada data tunggakan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
