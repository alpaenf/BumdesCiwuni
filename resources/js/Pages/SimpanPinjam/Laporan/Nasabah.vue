<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ data: Array, filters: Object, summary: Object });
const startDate = ref(props.filters?.start_date ?? '');
const endDate   = ref(props.filters?.end_date ?? '');
const status    = ref(props.filters?.status ?? '');

let timeout;
watch([startDate, endDate, status], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('laporan.nasabah'), { start_date: startDate.value, end_date: endDate.value, status: status.value }, { preserveState: true, replace: true });
    }, 400);
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';

const buildQuery = () => new URLSearchParams({ start_date: startDate.value, end_date: endDate.value, status: status.value }).toString();
const pdfUrl   = computed(() => `${route('laporan.nasabah.pdf')}?${buildQuery()}`);
const excelUrl = computed(() => `${route('laporan.nasabah.excel')}?${buildQuery()}`);
</script>

<template>
    <Head title="Laporan Nasabah" />
    <AuthenticatedLayout>
        <template #header>Laporan Nasabah</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Data Nasabah</h2>
            </div>

            <!-- Summary -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-[color:var(--color-secondary)]">Total Nasabah</p>
                    <p class="mt-1 text-2xl font-bold text-[color:var(--color-on-surface)]">{{ summary.total }}</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                    <p class="text-xs uppercase text-emerald-600">Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">{{ summary.aktif }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 text-center shadow-sm">
                    <p class="text-xs uppercase text-slate-600">Tidak Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-slate-700">{{ summary.tidak_aktif }}</p>
                </div>
            </div>

            <!-- Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input v-model="startDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
                    <input v-model="endDate" type="date" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none" />
                    <select v-model="status" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak aktif">Tidak Aktif</option>
                    </select>
                </div>
                <ExportButtons :pdf-url="pdfUrl" :excel-url="excelUrl" />
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <table class="w-full text-sm min-w-max">
                    <thead>
                        <tr class="border-b border-[color:var(--color-outline-variant)] bg-white">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">No. Reg</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">No. Rekening</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">NIK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Bergabung</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-[color:var(--color-secondary)]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                        <tr v-if="data.length === 0"><td colspan="6" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Tidak ada data</td></tr>
                        <tr v-for="row in data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                            <td class="px-4 py-3 font-mono text-xs">{{ row.nomor_registrasi }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-[color:var(--color-primary)]">{{ row.nomor_rekening }}</td>
                            <td class="px-4 py-3 font-semibold">{{ row.nama }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-[color:var(--color-secondary)]">{{ row.nik }}</td>
                            <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal_bergabung) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="row.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'">{{ row.status }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
