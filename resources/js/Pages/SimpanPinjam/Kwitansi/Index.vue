<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ kwitansi: Object, filters: Object });
const search    = ref(props.filters?.search ?? '');
const startDate = ref(props.filters?.start_date ?? '');
const endDate   = ref(props.filters?.end_date ?? '');

let timeout;
watch([search, startDate, endDate], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('kwitansi.index'), { search: search.value, start_date: startDate.value, end_date: endDate.value }, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const showModal = ref(false);
const activePhotoUrl = ref('');
const openPreview = (url) => {
    activePhotoUrl.value = url;
    showModal.value = true;
};
</script>

<template>
    <Head title="Kwitansi" />
    <AuthenticatedLayout>
        <template #header>Kwitansi</template>
        <div class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Daftar Kwitansi</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ kwitansi.total }} kwitansi</p>
                </div>
                <Link :href="route('kwitansi.create')" class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    <span class="material-symbols-outlined text-base">receipt_long</span> Buat Kwitansi
                </Link>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input v-model="search" type="text" placeholder="Cari nomor atau keperluan..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                </div>
                <input v-model="startDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
                <input v-model="endDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
            </div>
            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">No. Kwitansi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nasabah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Keperluan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                        <tr v-if="kwitansi.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data kwitansi</td>
                        </tr>
                        <tr v-for="row in kwitansi.data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-[color:var(--color-primary)]">{{ row.nomor_kwitansi }}</td>
                            <td class="px-4 py-3 font-medium">{{ row.nasabah?.nama }}</td>
                            <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ row.keperluan }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ formatCurrency(row.nominal) }}</td>
                            <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a :href="route('kwitansi.print', row.id)" target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg bg-[color:var(--color-surface-container)] px-2.5 py-1 text-xs hover:bg-[color:var(--color-surface-container-high)]">
                                        <span class="material-symbols-outlined text-xs">print</span> Print
                                    </a>
                                    <button v-if="row.foto_url" @click="openPreview(row.foto_url)"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-50 text-blue-700 px-2.5 py-1 text-xs hover:bg-blue-100">
                                        <span class="material-symbols-outlined text-xs">image</span> Lihat Bukti
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Preview Bukti -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showModal = false">
                <div class="relative max-w-2xl w-full rounded-xl bg-white p-4 shadow-xl">
                    <button @click="showModal = false" class="absolute right-4 top-4 text-gray-500 hover:text-gray-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    <h3 class="mb-4 text-base font-semibold">Bukti Kwitansi / Lampiran</h3>
                    <div class="flex items-center justify-center overflow-hidden rounded-lg border bg-white">
                        <img :src="activePhotoUrl" class="max-h-[70vh] max-w-full object-contain" alt="Bukti Kwitansi" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
