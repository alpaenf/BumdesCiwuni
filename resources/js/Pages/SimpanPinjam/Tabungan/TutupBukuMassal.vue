<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    affectedAccounts: Array,
    totalDeduction: Number,
    biayaAdmin: Object,
    flash: Object,
    unopenedRegulerCount: Number,
    unopenedSembakoCount: Number,
});

const search = ref('');
const showConfirmModal = ref(false);

const todayDate = new Date().toLocaleDateString('sv-SE'); // YYYY-MM-DD local format

const bulkForm = useForm({
    tanggal_tutup: todayDate,
});
const mulaiForm = useForm({
    jenis_tabungan: '',
    tanggal_mulai: todayDate,
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const filteredAccounts = computed(() => {
    const q = search.value.toLowerCase().trim();
    if (!q) return props.affectedAccounts;
    return props.affectedAccounts.filter(acc => 
        acc.nama.toLowerCase().includes(q) || 
        acc.nomor_rekening.toLowerCase().includes(q)
    );
});

function executeTutupBuku() {
    bulkForm.post(route('tabungan.tutup-buku-masal.store'), {
        onSuccess: () => {
            showConfirmModal.value = false;
        }
    });
}

function executeMulaiBuku(jenis) {
    if (confirm(`Apakah Anda yakin ingin memulai/membuka Buku Tabungan ${jenis === 'reguler' ? 'Reguler' : 'Sembako'} massal untuk seluruh nasabah yang belum memilikinya?`)) {
        mulaiForm.jenis_tabungan = jenis;
        mulaiForm.post(route('tabungan.mulai-buku-masal.store'));
    }
}
</script>

<template>
    <Head title="Tutup Buku Massal" />
    <AuthenticatedLayout>
        <template #header>Tutup Buku Massal (Tutup Periode)</template>

        <div class="space-y-5">
            <!-- Flash Message -->
            <div v-if="flash?.success" class="flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                <span class="material-symbols-outlined text-base text-blue-600">check_circle</span>
                {{ flash.success }}
            </div>

            <!-- Dashboard Stats Summary -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-[color:var(--color-secondary)] uppercase">Total Biaya Promosi Diperoleh</p>
                            <h3 class="mt-1 text-lg font-bold text-red-700">{{ formatCurrency(totalDeduction) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <span class="material-symbols-outlined text-2xl">groups</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-[color:var(--color-secondary)] uppercase">Rekening Terproses</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-800">{{ affectedAccounts.length }} Rekening</h3>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                            <span class="material-symbols-outlined text-2xl">info</span>
                        </div>
                        <div class="text-xs space-y-0.5">
                            <p class="font-semibold text-slate-700">Daftar Biaya Admin Periode:</p>
                            <p class="text-[color:var(--color-secondary)]">Tabungan Reguler: <strong>{{ formatCurrency(biayaAdmin.reguler) }}</strong></p>
                            <p class="text-[color:var(--color-secondary)]">Tabungan Sembako: <strong>{{ formatCurrency(biayaAdmin.sembako) }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mulai Buku Massal Section -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Mulai Buku Tabungan Massal</h3>
                    <p class="text-xs text-[color:var(--color-secondary)] mt-1">
                        Gunakan tombol di bawah ini untuk membuka buku tabungan secara massal bagi nasabah yang belum terdaftar di program tabungan terkait.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai Buku (Tanggal Pembukaan Rekening)</label>
                        <input v-model="mulaiForm.tanggal_mulai" type="date"
                            class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-xs focus:border-[color:var(--color-primary)] focus:outline-none w-full sm:w-auto" />
                    </div>

                    <!-- Tabungan Reguler -->
                    <div class="rounded-xl border border-blue-100 bg-blue-50/30 p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider">Tabungan Reguler</h4>
                            <p class="text-sm font-semibold text-slate-700 mt-2">
                                {{ unopenedRegulerCount }} Nasabah belum memiliki rekening.
                            </p>
                        </div>
                        <button type="button" @click="executeMulaiBuku('reguler')" :disabled="unopenedRegulerCount === 0 || mulaiForm.processing"
                            class="mt-4 flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition disabled:opacity-50">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Mulai Buku Reguler Massal
                        </button>
                    </div>

                    <!-- Tabungan Sembako -->
                    <div class="rounded-xl border border-orange-100 bg-orange-50/30 p-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-orange-700 uppercase tracking-wider">Tabungan Sembako</h4>
                            <p class="text-sm font-semibold text-slate-700 mt-2">
                                {{ unopenedSembakoCount }} Nasabah belum memiliki rekening.
                            </p>
                        </div>
                        <button type="button" @click="executeMulaiBuku('sembako')" :disabled="unopenedSembakoCount === 0 || mulaiForm.processing"
                            class="mt-4 flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-xs font-semibold text-white hover:bg-orange-700 transition disabled:opacity-50">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Mulai Buku Sembako Massal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Card & Search -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-5 shadow-sm space-y-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="space-y-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Preview Data Rekening Terkena Potongan</h3>
                            <p class="text-xs text-[color:var(--color-secondary)] mt-1">
                                Hanya rekening dengan saldo sama dengan atau melebihi batas endapan wajib yang akan dipotong biaya administrasi tahunan.
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Tutup Buku (Tanggal Transaksi Potongan)</label>
                            <input v-model="bulkForm.tanggal_tutup" type="date"
                                class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2 text-xs focus:border-[color:var(--color-primary)] focus:outline-none w-full sm:w-auto" />
                        </div>
                    </div>
                    <button type="button" @click="showConfirmModal = true" :disabled="affectedAccounts.length === 0"
                        class="flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition disabled:opacity-50">
                        <span class="material-symbols-outlined text-base">lock_reset</span>
                        Jalankan Tutup Buku Massal
                    </button>
                </div>

                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input v-model="search" type="text" placeholder="Cari berdasarkan nama nasabah atau nomor rekening..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                </div>
            </div>

            <!-- Affected Accounts Table -->
            <div class="overflow-hidden rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nomor Rekening</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nama Nasabah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Jenis</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Saldo Awal</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Potongan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="filteredAccounts.length === 0">
                                <td colspan="6" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data rekening terpengaruh yang cocok</td>
                            </tr>
                            <tr v-for="row in filteredAccounts" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-[color:var(--color-primary)]">{{ row.nomor_rekening }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ row.nama }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="row.jenis === 'sembako'" class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700">
                                        <span class="material-symbols-outlined text-[10px]">shopping_basket</span> Sembako
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        <span class="material-symbols-outlined text-[10px]">savings</span> Reguler
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ formatCurrency(row.saldo) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-600">- {{ formatCurrency(row.potongan) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ formatCurrency(row.saldo_setelah) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Konfirmasi Tutup Buku Masal -->
            <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl border border-red-100">
                    <div class="flex items-center gap-3 text-red-600">
                        <span class="material-symbols-outlined text-3xl">warning</span>
                        <h3 class="text-lg font-bold">Konfirmasi Tutup Buku Masal</h3>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">
                        Apakah Anda yakin ingin memproses Tutup Buku Masal untuk seluruh rekening tabungan?
                    </p>
                    <p class="mt-2 text-xs text-red-600 font-semibold bg-red-50 p-3 rounded-lg border border-red-100">
                        PENTING: Tindakan ini akan memotong saldo masing-masing rekening sebesar Rp 20.000 secara otomatis dan permanen sebagai biaya administrasi periode tahunan dengan tanggal transaksi <strong>{{ bulkForm.tanggal_tutup }}</strong>. Tindakan ini tidak dapat dibatalkan!
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showConfirmModal = false" :disabled="bulkForm.processing"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50">
                            Batal
                        </button>
                        <button type="button" @click="executeTutupBuku" :disabled="bulkForm.processing"
                            class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                            <span class="material-symbols-outlined text-base" :class="{'animate-spin': bulkForm.processing}">
                                {{ bulkForm.processing ? 'sync' : 'check_circle' }}
                            </span>
                            Ya, Proses Sekarang
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
