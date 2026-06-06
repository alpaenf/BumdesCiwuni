<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({ nasabahOptions: Array });

const form = useForm({
    nasabah_id:      '',
    tanggal_akad:    new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0],
    pinjaman_pokok:  '',
    bunga:           10,
    nominal_setoran: '',
    foto_perjanjian: null,
});

const preview = ref(null);
const loadingPreview = ref(false);

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

let previewTimeout;
const updatePreview = () => {
    clearTimeout(previewTimeout);
    if (!form.pinjaman_pokok || !form.nominal_setoran) { preview.value = null; return; }
    previewTimeout = setTimeout(async () => {
        loadingPreview.value = true;
        try {
            const res = await axios.post(route('pinjaman.kalkulasi'), {
                pinjaman_pokok: form.pinjaman_pokok,
                bunga: form.bunga,
                nominal_setoran: form.nominal_setoran,
            });
            preview.value = res.data;
        } finally {
            loadingPreview.value = false;
        }
    }, 600);
};

watch([() => form.pinjaman_pokok, () => form.bunga], ([newPokok, newBunga]) => {
    if (newPokok) {
        const pokok = parseFloat(newPokok) || 0;
        const bunga = parseFloat(newBunga) || 0;
        const total = pokok + (pokok * bunga / 100);
        // Default ke 22 kali angsuran
        form.nominal_setoran = Math.ceil(total / 22);
    }
});

watch([() => form.pinjaman_pokok, () => form.bunga, () => form.nominal_setoran], updatePreview);

const submit = () => form.post(route('pinjaman.store'));
</script>

<template>
    <Head title="Pinjaman Baru" />
    <AuthenticatedLayout>
        <template #header>Pinjaman Baru</template>
        <div class="mx-auto max-w-xl">
            <Link :href="route('pinjaman.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h2 class="mb-6 font-semibold">Pengajuan Pinjaman</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nasabah <span class="text-red-500">*</span></label>
                        <select v-model="form.nasabah_id" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                            :class="form.errors.nasabah_id ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'">
                            <option value="">-- Pilih Nasabah --</option>
                            <option v-for="n in nasabahOptions" :key="n.id" :value="n.id">{{ n.nama }} ({{ n.nomor_rekening }})</option>
                        </select>
                        <p v-if="form.errors.nasabah_id" class="mt-1 text-xs text-red-500">{{ form.errors.nasabah_id }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal Akad <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal_akad" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Pinjaman Pokok <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-[color:var(--color-secondary)]">Rp</span>
                                <input v-model="form.pinjaman_pokok" type="number" min="1" placeholder="0"
                                    class="w-full rounded-lg border px-4 py-2.5 pl-9 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                    :class="form.errors.pinjaman_pokok ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                            </div>
                            <p v-if="form.errors.pinjaman_pokok" class="mt-1 text-xs text-red-500">{{ form.errors.pinjaman_pokok }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Bunga (%) <span class="text-red-500">*</span></label>
                            <input v-model="form.bunga" type="number" min="0" max="100" step="0.5"
                                class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nominal Setoran per Angsuran <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.nominal_setoran" type="number" min="1" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-9 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                :class="form.errors.nominal_setoran ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.nominal_setoran" class="mt-1 text-xs text-red-500">{{ form.errors.nominal_setoran }}</p>
                    </div>

                    <!-- Foto Perjanjian -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Foto Dokumen Perjanjian</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="(e) => form.foto_perjanjian = e.target.files[0]"
                            class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                        />
                        <p class="mt-1.5 text-xs text-[color:var(--color-secondary)]">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</p>
                        <p v-if="form.errors.foto_perjanjian" class="mt-1 text-xs text-red-500">{{ form.errors.foto_perjanjian }}</p>
                    </div>

                    <!-- Preview Kalkulasi -->
                    <div v-if="preview" class="rounded-lg bg-[color:var(--color-primary)]/5 border border-[color:var(--color-primary)]/20 p-4 text-sm space-y-4">
                        <p class="font-semibold text-[color:var(--color-primary)]">Preview Kalkulasi</p>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-white p-3 border border-[color:var(--color-outline-variant)]">
                                <p class="text-xs text-[color:var(--color-secondary)]">Total Bunga</p>
                                <p class="font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(preview.total_bunga) }}</p>
                            </div>
                            <div class="rounded-lg bg-white p-3 border border-[color:var(--color-outline-variant)]">
                                <p class="text-xs text-[color:var(--color-secondary)]">Total Tagihan (Pokok + Bunga)</p>
                                <p class="font-bold text-[color:var(--color-on-surface)]">{{ formatCurrency(preview.total_tagihan) }}</p>
                            </div>
                            <div class="col-span-2 rounded-lg bg-white p-3 border border-[color:var(--color-outline-variant)]">
                                <div class="flex justify-between items-center mb-2">
                                    <p class="text-xs font-semibold text-[color:var(--color-secondary)]">Estimasi Rincian per Setoran</p>
                                    <p class="font-bold text-[color:var(--color-primary)]">{{ preview.jumlah_angsuran }}x Angsuran</p>
                                </div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-[color:var(--color-on-surface-variant)]">Porsi Pokok:</span>
                                    <span class="font-medium">{{ formatCurrency(preview.porsi_pokok) }}</span>
                                </div>
                                <div class="flex justify-between text-xs border-b border-dashed border-[color:var(--color-outline-variant)] pb-1 mb-1">
                                    <span class="text-[color:var(--color-on-surface-variant)]">Porsi Bunga:</span>
                                    <span class="font-medium">{{ formatCurrency(preview.porsi_bunga) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-bold">
                                    <span>Total Setoran:</span>
                                    <span>{{ formatCurrency(preview.nominal_setoran) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="loadingPreview" class="text-center text-sm text-[color:var(--color-secondary)]">Menghitung...</div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('pinjaman.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">account_balance</span> Buat Pinjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
