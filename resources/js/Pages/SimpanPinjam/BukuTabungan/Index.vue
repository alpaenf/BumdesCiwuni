<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    nasabahOptions: {
        type: Array,
        required: true,
    },
    nasabah: {
        type: Object,
        default: null,
    },
    tabungan: {
        type: Object,
        default: null,
    },
    summary: {
        type: Object,
        required: true,
    },
    transactions: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    search: props.filters.search || '',
    nasabah_id: props.filters.nasabah_id || '',
    nomor_rekening: props.filters.nomor_rekening || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    jenis_tabungan: props.filters.jenis_tabungan || '',
});

const submit = () => {
    form.get(route('buku-tabungan.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportFilters = computed(() => {
    const payload = {
        search: form.search || undefined,
        nasabah_id: form.nasabah_id || undefined,
        nomor_rekening: form.nomor_rekening || undefined,
        start_date: form.start_date || undefined,
        end_date: form.end_date || undefined,
        jenis_tabungan: form.jenis_tabungan || undefined,
    };

    Object.keys(payload).forEach((key) => {
        if (!payload[key]) {
            delete payload[key];
        }
    });

    return payload;
});

const jenisTabunganLabel = computed(() => {
    if (!props.tabungan) return '-';

    if (props.tabungan.jenis_tabungan === 'reguler') {
        return 'Reguler';
    }

    if (props.tabungan.jenis_tabungan === 'sembako') {
        return 'Sembako';
    }

    return props.tabungan.jenis_tabungan;
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
</script>

<template>
    <Head title="Buku Tabungan Nasabah" />

    <AuthenticatedLayout>
        <template #header>Buku Tabungan Nasabah</template>

        <div class="space-y-6">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold">Filter Buku Tabungan</h3>
                        <p class="text-sm text-slate-500">Cari histori transaksi tabungan berdasarkan nasabah dan periode.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a
                            :href="route('buku-tabungan.pdf', exportFilters)"
                            target="_blank"
                            class="rounded-lg border border-blue-200 px-4 py-2 text-sm text-slate-900"
                        >
                            Cetak PDF
                        </a>
                        <a
                            :href="route('buku-tabungan.excel', exportFilters)"
                            class="rounded-lg border border-blue-200 px-4 py-2 text-sm text-slate-900"
                        >
                            Export Excel
                        </a>
                        <a
                            :href="route('buku-tabungan.print', exportFilters)"
                            target="_blank"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white"
                        >
                            Print
                        </a>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Cari Nasabah</label>
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Nama atau nomor registrasi"
                            class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Nomor Rekening</label>
                        <input
                            v-model="form.nomor_rekening"
                            type="text"
                            placeholder="00001.2026"
                            class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm"
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Jenis Tabungan</label>
                        <select v-model="form.jenis_tabungan" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm">
                            <option value="">Semua Jenis</option>
                            <option value="reguler">Reguler</option>
                            <option value="sembako">Sembako</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-900">Nasabah</label>
                        <select v-model="form.nasabah_id" class="mt-2 w-full rounded-xl border border-blue-100 px-3 py-2 text-sm">
                            <option value="">Semua Nasabah</option>
                            <option v-for="nasabahItem in nasabahOptions" :key="nasabahItem.id" :value="nasabahItem.id">
                                {{ nasabahItem.nama }} - {{ nasabahItem.nomor_rekening }}
                            </option>
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
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Total Masuk</p>
                    <p class="mt-2 text-xl font-semibold">{{ formatCurrency(summary.total_setoran) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Total Keluar</p>
                    <p class="mt-2 text-xl font-semibold">{{ formatCurrency(summary.total_penarikan) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Total Laba Unit</p>
                    <p class="mt-2 text-xl font-semibold">{{ formatCurrency(summary.total_administrasi) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Saldo Saat Ini</p>
                    <p class="mt-2 text-xl font-semibold">{{ formatCurrency(summary.saldo_saat_ini) }}</p>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">Data Nasabah</h3>
                    <p class="text-sm text-slate-500">Pastikan memilih nasabah untuk menampilkan detail.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Nomor Registrasi</p>
                        <p class="mt-2 text-sm font-semibold">{{ nasabah?.nomor_registrasi || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Nomor Rekening</p>
                        <p class="mt-2 text-sm font-semibold">{{ nasabah?.nomor_rekening || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Nama</p>
                        <p class="mt-2 text-sm font-semibold">{{ nasabah?.nama || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Alamat</p>
                        <p class="mt-2 text-sm font-semibold">{{ nasabah?.alamat || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Nomor WhatsApp</p>
                        <p class="mt-2 text-sm font-semibold">{{ nasabah?.no_hp || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-900">Jenis Tabungan</p>
                        <p class="mt-2 text-sm font-semibold">{{ jenisTabunganLabel }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Buku Tabungan</h3>
                    <span class="text-sm text-slate-500">{{ transactions.length }} transaksi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs uppercase tracking-[0.2em] text-slate-900">
                                <th class="py-3 pe-4">Tanggal</th>
                                <th class="py-3 pe-4">Nomor Transaksi</th>
                                <th class="py-3 pe-4">Uraian</th>
                                <th class="py-3 pe-4">Masuk</th>
                                <th class="py-3 pe-4">Keluar</th>
                                <th class="py-3 pe-4">Saldo</th>
                                <th class="py-3">Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in transactions" :key="item.id" class="border-b last:border-none">
                                <td class="py-3 pe-4">{{ item.tanggal }}</td>
                                <td class="py-3 pe-4">{{ item.nomor_transaksi }}</td>
                                <td class="py-3 pe-4">{{ item.uraian }}</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.masuk) }}</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.keluar) }}</td>
                                <td class="py-3 pe-4">{{ formatCurrency(item.saldo) }}</td>
                                <td class="py-3">{{ formatCurrency(item.laba) }}</td>
                            </tr>
                            <tr v-if="!transactions.length">
                                <td colspan="7" class="py-6 text-center text-sm text-slate-500">Belum ada transaksi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
