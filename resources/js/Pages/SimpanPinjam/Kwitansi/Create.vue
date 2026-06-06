<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ nasabahOptions: Array });

const form = useForm({
    nasabah_id: '',
    nominal:    '',
    keperluan:  '',
    tanggal:    new Date().toISOString().split('T')[0],
    foto:       null,
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const submit = () => form.post(route('kwitansi.store'));
</script>

<template>
    <Head title="Buat Kwitansi" />
    <AuthenticatedLayout>
        <template #header>Buat Kwitansi</template>
        <div class="mx-auto max-w-lg">
            <Link :href="route('kwitansi.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h2 class="mb-6 font-semibold">Form Kwitansi Baru</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nama Nasabah / Penerima <span class="text-red-500">*</span></label>
                        <select v-model="form.nasabah_id" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                            :class="form.errors.nasabah_id ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'">
                            <option value="">-- Pilih Nasabah --</option>
                            <option v-for="n in nasabahOptions" :key="n.id" :value="n.id">{{ n.nama }}</option>
                        </select>
                        <p v-if="form.errors.nasabah_id" class="mt-1 text-xs text-red-500">{{ form.errors.nasabah_id }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nominal <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.nominal" type="number" min="1" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                :class="form.errors.nominal ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.nominal" class="mt-1 text-xs text-red-500">{{ form.errors.nominal }}</p>
                        <p v-if="form.nominal" class="mt-1 text-xs text-[color:var(--color-secondary)]">{{ formatCurrency(form.nominal) }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Keperluan <span class="text-red-500">*</span></label>
                        <textarea v-model="form.keperluan" rows="3" placeholder="Misal: Pencairan pinjaman BUMDes, Setoran tabungan..."
                            class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                            :class="form.errors.keperluan ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'"></textarea>
                        <p v-if="form.errors.keperluan" class="mt-1 text-xs text-red-500">{{ form.errors.keperluan }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Foto Bukti / Lampiran</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="(e) => form.foto = e.target.files[0]"
                            class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                        />
                        <p class="mt-1.5 text-xs text-[color:var(--color-secondary)]">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</p>
                        <p v-if="form.errors.foto" class="mt-1 text-xs text-red-500">{{ form.errors.foto }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('kwitansi.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">receipt_long</span> Buat & Cetak Kwitansi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
