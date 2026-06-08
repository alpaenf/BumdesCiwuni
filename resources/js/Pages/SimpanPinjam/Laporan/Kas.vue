<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ExportButtons from '@/Components/ExportButtons.vue';

const props = defineProps({ summary: Object, rincian: Object, filters: Object });
const startDate = ref(props.filters?.start_date ?? '');
const endDate = ref(props.filters?.end_date ?? '');

let timeout;
const applyFilter = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const params = {};
        if (startDate.value) params.start_date = startDate.value;
        if (endDate.value) params.end_date = endDate.value;
        router.get(route('laporan.kas'), params, { preserveState: true, replace: true });
    }, 400);
};

watch([startDate, endDate], applyFilter);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const buildQuery = computed(() => {
    const params = new URLSearchParams();
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    return params.toString();
});
const pdfUrl = computed(() => `${route('laporan.kas.pdf')}?${buildQuery.value}`);
</script>

<template>
    <Head title="Laporan Kas" />
    <AuthenticatedLayout>
        <template #header>Laporan Kas & Rekap Saldo</template>
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </Link>
                <h2 class="text-lg font-semibold">Laporan Kas & Rekap Saldo</h2>
            </div>

            <!-- Total Saldo Keseluruhan (Sepanjang Waktu) -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Total Akumulasi Keseluruhan (Sepanjang Waktu)</h3>
                        <p class="text-xs text-[color:var(--color-secondary)]">Akumulasi total dari awal berdirinya sistem (mengabaikan filter tanggal)</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase mb-1">Total Saldo Reguler</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_reguler) }}</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 bg-orange-50/50 p-4">
                        <p class="text-xs font-semibold text-orange-700 uppercase mb-1">Total Saldo Sembako</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.saldo_sembako) }}</p>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50/50 p-4">
                        <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Total Angsuran Masuk</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.total_angsuran_all) }}</p>
                    </div>
                    <div class="rounded-lg border border-rose-100 bg-rose-50/50 p-4">
                        <p class="text-xs font-semibold text-rose-700 uppercase mb-1">Total Pinjaman Disalurkan</p>
                        <p class="text-xl font-bold text-slate-800">{{ formatCurrency(summary.total_pinjaman_all) }}</p>
                    </div>
                </div>
                
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-teal-100 bg-teal-50 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-teal-700 uppercase">Total Uang BUMDes</p>
                            <span class="text-[10px] bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-medium">Reguler + Sembako + Angsuran</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 mt-2">{{ formatCurrency(Number(summary.saldo_reguler) + Number(summary.saldo_sembako) + Number(summary.total_angsuran_all)) }}</p>
                    </div>
                    <div class="rounded-lg border border-sky-100 bg-sky-50 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-sky-700 uppercase">Sisa Uang BUMDes (Setelah Dipinjamkan)</p>
                            <span class="text-[10px] bg-sky-100 text-sky-700 px-2 py-0.5 rounded-full font-medium">Total Uang - Pinjaman</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 mt-2">{{ formatCurrency((Number(summary.saldo_reguler) + Number(summary.saldo_sembako) + Number(summary.total_angsuran_all)) - Number(summary.total_pinjaman_all)) }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters + Export -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-[color:var(--color-secondary)] mb-1">Mulai Tanggal</label>
                        <input v-model="startDate" type="date" :max="endDate"
                            class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs font-semibold text-[color:var(--color-secondary)] mb-1">Sampai Tanggal</label>
                        <input v-model="endDate" type="date" :min="startDate"
                            class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div class="flex items-end h-full pt-5">
                         <button v-if="startDate || endDate" @click="startDate = ''; endDate = ''" class="text-xs font-semibold text-red-600 hover:text-red-800">✕ Reset Filter</button>
                    </div>
                </div>
                <div class="pt-5">
                     <ExportButtons :pdf-url="pdfUrl" />
                </div>
            </div>

            <!-- Detail Breakdown -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                        <span class="material-symbols-outlined text-xl">analytics</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Arus Kas (Berdasarkan Filter Tanggal)</h3>
                        <p class="text-xs text-[color:var(--color-secondary)]">Rincian uang masuk dan keluar pada periode yang dipilih</p>
                    </div>
                </div>
                <div class="space-y-4 text-sm mt-4 border-t border-[color:var(--color-outline-variant)] pt-4">
                    <!-- Pemasukan -->
                    <div>
                        <h4 class="font-bold text-blue-700 mb-2 border-b border-blue-100 pb-1">Total Uang Masuk</h4>
                        <div class="space-y-4 pl-2">
                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">+ Setoran Tabungan Reguler</span>
                                    <span class="font-semibold text-blue-600">{{ formatCurrency(summary.masuk_reguler) }}</span>
                                </div>
                                <details v-if="rincian?.masuk_reguler?.length" class="text-xs text-slate-600 bg-blue-50/50 rounded-lg p-2 border border-blue-100 cursor-pointer">
                                    <summary class="font-medium hover:text-blue-700 outline-none">Lihat Rincian ({{ rincian.masuk_reguler.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-blue-200 cursor-default">
                                        <div v-for="item in rincian.masuk_reguler" :key="item.id" class="flex justify-between py-1 border-b border-blue-100/50 last:border-0">
                                            <span>{{ item.tanggal }} - {{ item.tabungan?.nasabah?.nama || '-' }}</span>
                                            <span class="font-medium">{{ formatCurrency(item.nominal) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">+ Setoran Tabungan Sembako</span>
                                    <span class="font-semibold text-blue-600">{{ formatCurrency(summary.masuk_sembako) }}</span>
                                </div>
                                <details v-if="rincian?.masuk_sembako?.length" class="text-xs text-slate-600 bg-blue-50/50 rounded-lg p-2 border border-blue-100 cursor-pointer">
                                    <summary class="font-medium hover:text-blue-700 outline-none">Lihat Rincian ({{ rincian.masuk_sembako.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-blue-200 cursor-default">
                                        <div v-for="item in rincian.masuk_sembako" :key="item.id" class="flex justify-between py-1 border-b border-blue-100/50 last:border-0">
                                            <span>{{ item.tanggal }} - {{ item.tabungan?.nasabah?.nama || '-' }}</span>
                                            <span class="font-medium">{{ formatCurrency(item.nominal) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">+ Pembayaran Angsuran Pinjaman (Masuk)</span>
                                    <span class="font-semibold text-blue-600">{{ formatCurrency(summary.masuk_angsuran) }}</span>
                                </div>
                                <details v-if="rincian?.masuk_angsuran?.length" class="text-xs text-slate-600 bg-blue-50/50 rounded-lg p-2 border border-blue-100 cursor-pointer">
                                    <summary class="font-medium hover:text-blue-700 outline-none">Lihat Rincian ({{ rincian.masuk_angsuran.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-blue-200 cursor-default">
                                        <div v-for="item in rincian.masuk_angsuran" :key="item.id" class="flex justify-between py-1 border-b border-blue-100/50 last:border-0">
                                            <span>{{ item.tanggal }} - {{ item.pinjaman?.nasabah?.nama || '-' }} (Angsuran ke-{{ item.angsuran_ke }})</span>
                                            <span class="font-medium">{{ formatCurrency(item.jumlah_bayar) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-blue-800 bg-blue-50 p-3 mt-4 rounded-lg">
                            <span>Subtotal Pemasukan</span>
                            <span>{{ formatCurrency(summary.total_masuk) }}</span>
                        </div>
                    </div>

                    <!-- Pengeluaran -->
                    <div class="pt-6">
                        <h4 class="font-bold text-red-700 mb-2 border-b border-red-100 pb-1">Total Uang Keluar</h4>
                        <div class="space-y-4 pl-2">
                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">- Penarikan Tabungan Reguler</span>
                                    <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_reguler) }}</span>
                                </div>
                                <details v-if="rincian?.keluar_reguler?.length" class="text-xs text-slate-600 bg-red-50/50 rounded-lg p-2 border border-red-100 cursor-pointer">
                                    <summary class="font-medium hover:text-red-700 outline-none">Lihat Rincian ({{ rincian.keluar_reguler.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-red-200 cursor-default">
                                        <div v-for="item in rincian.keluar_reguler" :key="item.id" class="flex justify-between py-1 border-b border-red-100/50 last:border-0">
                                            <span>{{ item.tanggal }} - {{ item.tabungan?.nasabah?.nama || '-' }}</span>
                                            <span class="font-medium">{{ formatCurrency(Number(item.nominal) + Number(item.administrasi || 0)) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">- Penarikan Tabungan Sembako</span>
                                    <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_sembako) }}</span>
                                </div>
                                <details v-if="rincian?.keluar_sembako?.length" class="text-xs text-slate-600 bg-red-50/50 rounded-lg p-2 border border-red-100 cursor-pointer">
                                    <summary class="font-medium hover:text-red-700 outline-none">Lihat Rincian ({{ rincian.keluar_sembako.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-red-200 cursor-default">
                                        <div v-for="item in rincian.keluar_sembako" :key="item.id" class="flex justify-between py-1 border-b border-red-100/50 last:border-0">
                                            <span>{{ item.tanggal }} - {{ item.tabungan?.nasabah?.nama || '-' }}</span>
                                            <span class="font-medium">{{ formatCurrency(Number(item.nominal) + Number(item.administrasi || 0)) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-600">- Pencairan Dana Pinjaman Baru (Keluar)</span>
                                    <span class="font-semibold text-red-600">{{ formatCurrency(summary.keluar_pinjaman) }}</span>
                                </div>
                                <details v-if="rincian?.keluar_pinjaman?.length" class="text-xs text-slate-600 bg-red-50/50 rounded-lg p-2 border border-red-100 cursor-pointer">
                                    <summary class="font-medium hover:text-red-700 outline-none">Lihat Rincian ({{ rincian.keluar_pinjaman.length }} data)</summary>
                                    <div class="mt-2 space-y-1 pl-4 border-l-2 border-red-200 cursor-default">
                                        <div v-for="item in rincian.keluar_pinjaman" :key="item.id" class="flex justify-between py-1 border-b border-red-100/50 last:border-0">
                                            <span>{{ item.tanggal_akad }} - {{ item.nasabah?.nama || '-' }}</span>
                                            <span class="font-medium">{{ formatCurrency(item.pinjaman_pokok) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                        <div class="flex justify-between font-bold text-red-800 bg-red-50 p-3 mt-4 rounded-lg">
                            <span>Subtotal Pengeluaran</span>
                            <span>{{ formatCurrency(summary.total_keluar) }}</span>
                        </div>
                    </div>

                    <!-- Laba/Rugi Kas -->
                    <div class="flex justify-between pt-6 font-bold text-xl border-t border-[color:var(--color-outline-variant)]">
                        <span class="text-slate-800">Saldo Kas Bersih (Periode Ini)</span>
                        <span :class="summary.saldo_kas >= 0 ? 'text-blue-600' : 'text-red-600'">{{ formatCurrency(summary.saldo_kas) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
