<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ nasabah: Object, tabungan: Object, biayaAdmin: { type: Number, default: 20000 } });
const ADMIN = props.biayaAdmin;
const form = useForm({ nasabah_id: props.nasabah.id, tanggal: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0], nominal: '', jenis_pengambilan: 'uang', keterangan: '', foto_barang: null });
const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const saldoBaru = computed(() => {
    return Number(props.tabungan.saldo) - (Number(form.nominal) || 0);
});

const isInvalid = computed(() => {
    return saldoBaru.value < ADMIN;
});

const submit = () => form.post(route('tabungan-sembako.ambil.store', props.nasabah.id));
</script>
<template>
    <Head title="Ambil Sembako" />
    <AuthenticatedLayout>
        <template #header>Pengambilan Tabungan Sembako</template>
        <div class="mx-auto max-w-lg">
            <Link :href="route('tabungan-sembako.index')" class="mb-6 inline-flex items-center gap-2 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </Link>
            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="mb-6 rounded-lg bg-orange-50 p-4">
                    <p class="text-xs font-semibold uppercase text-orange-600">Nasabah</p>
                    <p class="mt-1 font-bold text-orange-900">{{ nasabah.nama }}</p>
                    <p class="font-mono text-sm text-orange-700">{{ nasabah.nomor_rekening }}</p>
                    <p class="mt-2 text-sm text-orange-700">Saldo Sembako: <span class="font-bold">{{ formatCurrency(tabungan.saldo) }}</span></p>
                </div>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal <span class="text-red-500">*</span></label>
                        <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-4 py-2.5 text-sm focus:outline-none focus:border-[color:var(--color-primary)]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Jenis Pengambilan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="form.jenis_pengambilan = 'uang'"
                                class="flex items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-semibold transition"
                                :class="form.jenis_pengambilan === 'uang' ? 'border-[color:var(--color-primary)] bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)]' : 'border-[color:var(--color-outline-variant)] text-[color:var(--color-secondary)]'">
                                <span class="material-symbols-outlined text-base">payments</span> Uang
                            </button>
                            <button type="button" @click="form.jenis_pengambilan = 'barang'"
                                class="flex items-center justify-center gap-2 rounded-lg border px-4 py-3 text-sm font-semibold transition"
                                :class="form.jenis_pengambilan === 'barang' ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-[color:var(--color-outline-variant)] text-[color:var(--color-secondary)]'">
                                <span class="material-symbols-outlined text-base">shopping_basket</span> Barang/Sembako
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nominal Pengambilan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[color:var(--color-secondary)]">Rp</span>
                            <input v-model="form.nominal" type="number" min="1000" placeholder="0"
                                class="w-full rounded-lg border px-4 py-2.5 pl-10 text-sm focus:outline-none focus:border-[color:var(--color-primary)]"
                                :class="form.errors.nominal ? 'border-red-400' : 'border-[color:var(--color-outline-variant)]'" />
                        </div>
                        <p v-if="form.errors.nominal" class="mt-1 text-xs text-red-500">{{ form.errors.nominal }}</p>
                    </div>

                    <div v-if="form.jenis_pengambilan === 'barang'" class="bg-amber-50 border border-amber-200 p-4 rounded-xl space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-amber-900">Nama/Keterangan Barang <span class="text-red-500">*</span></label>
                            <input v-model="form.keterangan" type="text" placeholder="Misal: Beras 10kg"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white focus:outline-none focus:border-amber-500 border-amber-300" />
                            <p v-if="form.errors.keterangan" class="mt-1 text-xs text-red-500">{{ form.errors.keterangan }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-amber-900">Foto Barang (Opsional)</label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="(e) => form.foto_barang = e.target.files[0]"
                                class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm focus:outline-none focus:border-amber-500"
                            />
                            <p class="mt-1.5 text-xs text-amber-700">Format: JPG, JPEG, PNG. Maksimal 5MB.</p>
                            <p v-if="form.errors.foto_barang" class="mt-1 text-xs text-red-500">{{ form.errors.foto_barang }}</p>
                        </div>
                    </div>
                    <div v-if="form.nominal" class="rounded-lg p-4 text-sm" :class="isInvalid ? 'bg-red-50' : 'bg-[color:var(--color-surface-container-low)]'">
                        <div class="flex justify-between"><span>Saldo Saat Ini</span><span>{{ formatCurrency(tabungan.saldo) }}</span></div>
                        <div class="flex justify-between text-red-600"><span>- Pengambilan</span><span>{{ formatCurrency(form.nominal) }}</span></div>
                        <div class="mt-2 flex justify-between border-t pt-2 font-bold" :class="isInvalid ? 'text-red-700 border-red-200' : 'border-[color:var(--color-outline-variant)]'">
                            <span>Saldo Baru</span><span>{{ formatCurrency(saldoBaru) }}</span>
                        </div>
                        <p v-if="isInvalid" class="mt-2 text-xs text-red-600">Saldo setelah pengambilan minimal {{ formatCurrency(ADMIN) }} (Endapan Wajib).</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <Link :href="route('tabungan-sembako.index')" class="rounded-lg border border-[color:var(--color-outline-variant)] px-5 py-2.5 text-sm font-medium hover:bg-[color:var(--color-surface-container)]">Batal</Link>
                        <button type="submit" :disabled="form.processing || isInvalid" class="flex items-center gap-2 rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            <span class="material-symbols-outlined text-base">shopping_basket</span> Proses Pengambilan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
