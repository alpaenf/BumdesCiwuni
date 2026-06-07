<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pengaturan: Object,
    flash: Object,
});

const form = useForm({
    endapan_wajib: Number(props.pengaturan?.endapan_wajib ?? 20000),
});

const showConfirmModal = ref(false);
const bulkForm = useForm({});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

function submit() {
    form.put(route('pengaturan.tabungan.update'));
}

function executeTutupBuku() {
    bulkForm.post(route('pengaturan.tabungan.tutup-buku-masal'), {
        onSuccess: () => {
            showConfirmModal.value = false;
        }
    });
}
</script>

<template>
    <Head title="Pengaturan Tabungan" />
    <AuthenticatedLayout>
        <template #header>Pengaturan Tabungan</template>

        <div class="mx-auto max-w-xl space-y-5">

            <!-- Flash -->
            <div v-if="flash?.success" class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                {{ flash.success }}
            </div>

            <!-- Card -->
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-base font-semibold text-slate-800">Pengaturan Endapan Wajib</h2>
                    <p class="mt-1 text-xs text-[color:var(--color-secondary)]">
                        Atur nominal minimal saldo yang tidak dapat ditarik oleh nasabah (Endapan Wajib).
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Endapan Wajib -->
                    <div class="rounded-xl border border-[color:var(--color-outline-variant)] p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                                <span class="material-symbols-outlined text-xl text-orange-500">lock</span>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-slate-800">
                                    Nominal Endapan Wajib Tabungan
                                </label>
                                <p class="mt-0.5 text-xs text-[color:var(--color-secondary)]">
                                    Saldo minimum yang harus tersisa di rekening dan akan dipotong saat tutup periode.
                                </p>
                                <div class="mt-3 relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                                    <input v-model="form.endapan_wajib"
                                        type="number" min="0" step="500"
                                        class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                        :class="form.errors.endapan_wajib ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                                </div>
                                <p v-if="form.errors.endapan_wajib" class="mt-1 text-xs text-red-500">{{ form.errors.endapan_wajib }}</p>
                                <p class="mt-1.5 text-xs text-slate-400">Saat ini: <strong>{{ formatCurrency(props.pengaturan?.endapan_wajib ?? 20000) }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="flex items-start gap-2 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-xs text-blue-700">
                        <span class="material-symbols-outlined text-base shrink-0 mt-0.5">info</span>
                        <span>Perubahan nominal ini akan langsung berlaku pada transaksi penarikan tabungan dan proses tutup periode berikutnya.</span>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-6 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60 transition">
                            <span class="material-symbols-outlined text-base">save</span>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tutup Buku Masal Card -->
            <div class="rounded-xl border border-red-100 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-base font-semibold text-red-800">Tutup Buku Masal (Tutup Periode)</h2>
                    <p class="mt-1 text-xs text-[color:var(--color-secondary)]">
                        Lakukan pemotongan biaya administrasi secara massal untuk seluruh rekening nasabah aktif di akhir periode/akhir tahun (misal: saat lebaran).
                    </p>
                </div>

                <div class="rounded-xl border border-red-100 bg-red-50/50 p-4 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100">
                            <span class="material-symbols-outlined text-xl text-red-600">published_with_changes</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-red-900">Alur Tutup Buku Massal</h3>
                            <ul class="mt-2 text-xs text-red-700 list-disc list-inside space-y-1">
                                <li>Semua rekening (Reguler & Sembako) yang memiliki saldo minimal Rp 20.000 akan dipotong Rp 20.000 (Endapan Wajib).</li>
                                <li>Transaksi akan dicatat sebagai <strong>Tutup Periode (Potongan Administrasi Masal)</strong>.</li>
                                <li>Sisa saldo nasabah tidak hilang dan tetap aman di dalam rekening untuk periode berikutnya.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showConfirmModal = true"
                            class="flex items-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition">
                            <span class="material-symbols-outlined text-base">lock_reset</span>
                            Proses Tutup Buku Masal
                        </button>
                    </div>
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
                        PENTING: Tindakan ini akan memotong saldo masing-masing rekening sebesar Rp 20.000 secara otomatis dan permanen sebagai biaya administrasi periode tahunan. Tindakan ini tidak dapat dibatalkan!
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
