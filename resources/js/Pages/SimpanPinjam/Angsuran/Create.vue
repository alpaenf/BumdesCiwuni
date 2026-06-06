<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    nasabahOptions:  Array,
    pinjamanOptions: Array,
    filters:         Object,
});

const selectedNasabahId = ref(props.filters?.nasabah_id ?? '');
const pinjamanList = ref(props.pinjamanOptions ?? []);
const loadingPinjaman = ref(false);

const form = useForm({
    pinjaman_id:  '',
    tanggal:      new Date().toISOString().split('T')[0],
    jumlah_bayar: '',
    pasaran:      '',
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const selectedPinjaman = ref(null);

watch(selectedNasabahId, async (id) => {
    form.pinjaman_id = '';
    selectedPinjaman.value = null;
    form.jumlah_bayar = '';
    if (!id) {
        pinjamanList.value = [];
        return;
    }
    loadingPinjaman.value = true;
    try {
        const res = await axios.get(route('nasabah.pinjaman-aktif', { nasabah: id }));
        pinjamanList.value = res.data;
    } catch (err) {
        console.error(err);
        pinjamanList.value = [];
    } finally {
        loadingPinjaman.value = false;
    }
});

watch(() => form.pinjaman_id, (id) => {
    const found = pinjamanList.value.find(p => p.id == id);
    selectedPinjaman.value = found ?? null;
    if (found) {
        form.jumlah_bayar = Math.round(parseFloat(found.nominal_setoran));
    } else {
        form.jumlah_bayar = '';
    }
});

const PASARAN = ['legi', 'pahing', 'pon', 'wage', 'kliwon'];

const submit = () => form.post(route('angsuran.store'));
</script>

<template>
    <Head title="Bayar Angsuran" />
    <AuthenticatedLayout>
        <template #header>Bayar Angsuran</template>
        <div class="mx-auto max-w-lg">
            <Link :href="route('angsuran.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <h2 class="mb-6 font-semibold">Form Pembayaran Angsuran</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nasabah <span class="text-red-500">*</span></label>
                        <select v-model="selectedNasabahId" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]">
                            <option value="">-- Pilih Nasabah --</option>
                            <option v-for="n in nasabahOptions" :key="n.id" :value="n.id">{{ n.nama }} ({{ n.nomor_rekening }})</option>
                        </select>
                    </div>

                    <div v-if="selectedNasabahId">
                        <label class="mb-1.5 block text-sm font-medium">Pinjaman <span class="text-red-500">*</span></label>
                        <select v-model="form.pinjaman_id" class="w-full rounded-lg border px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                            :class="form.errors.pinjaman_id ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'">
                            <option value="">-- Pilih Pinjaman --</option>
                            <option v-for="p in pinjamanList" :key="p.id" :value="p.id">
                                Pokok {{ formatCurrency(p.pinjaman_pokok) }} | Sisa {{ formatCurrency(p.sisa_pinjaman) }}
                            </option>
                        </select>
                        <p v-if="form.errors.pinjaman_id" class="mt-1 text-xs text-red-500">{{ form.errors.pinjaman_id }}</p>
                    </div>

                    <!-- Info Pinjaman -->
                    <div v-if="selectedPinjaman" class="rounded-lg bg-[color:var(--color-surface-container-low)] p-4 text-sm space-y-3 border border-[color:var(--color-outline-variant)]">
                        <div class="border-b border-[color:var(--color-outline-variant)] pb-2">
                            <h4 class="font-semibold text-emerald-800">Detail Pinjaman Aktif</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-[color:var(--color-secondary)]">Pokok Pinjaman</p>
                                <p class="font-semibold text-[color:var(--color-on-surface)]">{{ formatCurrency(selectedPinjaman.pinjaman_pokok) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[color:var(--color-secondary)]">Total Tagihan (+Bunga)</p>
                                <p class="font-semibold text-[color:var(--color-on-surface)]">{{ formatCurrency(selectedPinjaman.total_tagihan) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[color:var(--color-secondary)]">Sisa Pinjaman</p>
                                <p class="font-bold text-red-600">{{ formatCurrency(selectedPinjaman.sisa_pinjaman) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[color:var(--color-secondary)]">Setoran Normal / Bulan</p>
                                <p class="font-bold text-emerald-700">{{ formatCurrency(selectedPinjaman.nominal_setoran) }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-[color:var(--color-secondary)]">Status Pembayaran</p>
                                <p class="font-semibold text-[color:var(--color-on-surface)]">
                                    Angsuran Ke-{{ selectedPinjaman.angsuran_ke }} dari {{ selectedPinjaman.jumlah_angsuran }} kali
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal Bayar <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Jumlah Bayar <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.jumlah_bayar" type="number" min="1" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-9 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                :class="form.errors.jumlah_bayar ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.jumlah_bayar" class="mt-1 text-xs text-red-500">{{ form.errors.jumlah_bayar }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Hari Pasaran</label>
                        <div class="grid grid-cols-5 gap-2">
                            <button v-for="p in PASARAN" :key="p" type="button" @click="form.pasaran = form.pasaran === p ? '' : p"
                                class="rounded-lg border py-2 text-xs font-semibold capitalize transition"
                                :class="form.pasaran === p
                                    ? 'border-[color:var(--color-primary)] bg-[color:var(--color-primary)] text-white'
                                    : 'border-[color:var(--color-outline-variant)] text-[color:var(--color-on-surface-variant)] hover:border-[color:var(--color-primary)] hover:text-[color:var(--color-primary)]'">
                                {{ p }}
                            </button>
                        </div>
                        <p v-if="form.errors.pasaran" class="mt-1 text-xs text-red-500">{{ form.errors.pasaran }}</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('angsuran.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">payments</span> Bayar Angsuran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
