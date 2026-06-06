<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ nasabah: Object, tabungan: Object, defaultAdminFee: { type: Number, default: 0 } });

const form = useForm({
    nasabah_id:   props.nasabah.id,
    tanggal:      new Date().toISOString().split('T')[0],
    nominal:      '',
    jenis_transaksi: 'tarik_tunai',
    keterangan:   '',
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const saldoBaru = computed(() => {
    const n = Number(form.nominal) || 0;
    return Number(props.tabungan.saldo) - n;
});

const isInvalid = computed(() => saldoBaru.value < 20000);

const submit = () => form.post(route('tabungan.tarik.store', props.nasabah.id));
</script>

<template>
    <Head title="Tarik Tabungan" />
    <AuthenticatedLayout>
        <template #header>Tarik Tabungan</template>
        <div class="mx-auto max-w-lg">
            <Link :href="route('tabungan.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="mb-6 rounded-lg bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase text-blue-600">Nasabah</p>
                    <p class="mt-1 font-bold text-blue-900">{{ nasabah.nama }}</p>
                    <p class="font-mono text-sm text-blue-700">{{ nasabah.nomor_rekening }}</p>
                    <p class="mt-2 text-sm text-blue-700">Saldo Saat Ini: <span class="font-bold">{{ formatCurrency(tabungan.saldo) }}</span></p>
                </div>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal Penarikan <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Jenis Penarikan <span class="text-red-500">*</span></label>
                        <select v-model="form.jenis_transaksi" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none">
                            <option value="tarik_tunai">Tarik Tunai</option>
                            <option value="tarik_sembako">Pencairan Sembako/Barang</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nominal Penarikan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.nominal" type="number" min="1000" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:border-[color:var(--color-primary)] focus:outline-none"
                                :class="form.errors.nominal ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.nominal" class="mt-1 text-xs text-red-500">{{ form.errors.nominal }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Keterangan</label>
                        <input v-model="form.keterangan" type="text" placeholder="Penarikan tabungan (opsional)"
                            class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                    </div>
                    <div v-if="form.nominal" class="rounded-lg p-4 text-sm" :class="isInvalid ? 'bg-red-50' : 'bg-[color:var(--color-surface-container-low)]'">
                        <div class="flex justify-between"><span class="text-[color:var(--color-secondary)]">Saldo Saat Ini</span><span>{{ formatCurrency(tabungan.saldo) }}</span></div>
                        <div class="flex justify-between text-red-600"><span>- Penarikan</span><span>{{ formatCurrency(form.nominal) }}</span></div>
                        <div class="mt-2 flex justify-between border-t pt-2 font-bold" :class="isInvalid ? 'text-red-700 border-red-200' : 'border-[color:var(--color-outline-variant)]'">
                            <span>Saldo Baru</span><span>{{ formatCurrency(saldoBaru) }}</span>
                        </div>
                        <p v-if="isInvalid" class="mt-2 text-xs text-red-600">Saldo setelah penarikan minimal Rp 20.000 (Endapan Wajib).</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('tabungan.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing || isInvalid" class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">remove_circle</span> Simpan Penarikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
