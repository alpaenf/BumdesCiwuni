<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    periodes: Array,
    periodeAktif: Object,
});

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

const formBuka = useForm({
    nama: '',
    tanggal_mulai: new Date().toISOString().split('T')[0],
});

const formTutup = useForm({
    tanggal_tutup: new Date().toISOString().split('T')[0],
});

const submitBuka = () => {
    formBuka.post(route('periode-tabungan.store'), {
        preserveScroll: true,
        onSuccess: () => formBuka.reset(),
    });
};

const showTutupModal = ref(false);

const submitTutup = () => {
    if (!props.periodeAktif) return;
    formTutup.post(route('periode-tabungan.tutup', props.periodeAktif.id), {
        preserveScroll: true,
        onSuccess: () => {
            showTutupModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Manajemen Periode Tabungan" />
    <AuthenticatedLayout>
        <template #header>Manajemen Periode Tabungan</template>

        <div class="grid gap-6 lg:grid-cols-3">
            
            <!-- Left Column: Controls -->
            <div class="space-y-6 lg:col-span-1">
                
                <!-- Status Periode Aktif -->
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-bold text-[color:var(--color-on-surface)]">Status Periode Saat Ini</h3>
                    
                    <div v-if="periodeAktif" class="rounded-lg bg-emerald-50 p-4 border border-emerald-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-3 w-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold uppercase text-emerald-700">Periode Aktif</span>
                        </div>
                        <p class="text-lg font-bold text-emerald-900">{{ periodeAktif.nama }}</p>
                        <p class="text-sm text-emerald-700 mt-1">Sejak: {{ formatDate(periodeAktif.tanggal_mulai) }}</p>
                        
                        <button @click="showTutupModal = true" class="mt-4 w-full flex justify-center items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                            <span class="material-symbols-outlined text-sm">lock</span>
                            Tutup Periode Ini
                        </button>
                    </div>
                    
                    <div v-else class="rounded-lg bg-red-50 p-4 border border-red-100 text-center">
                        <span class="material-symbols-outlined text-4xl text-red-300 mb-2">error_outline</span>
                        <p class="text-sm font-semibold text-red-800">Tidak ada periode aktif saat ini.</p>
                        <p class="text-xs text-red-600 mt-1">Silakan buka periode baru untuk memulai transaksi tabungan.</p>
                    </div>
                </div>

                <!-- Form Buka Periode Baru -->
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm" :class="{ 'opacity-50 pointer-events-none': periodeAktif }">
                    <h3 class="mb-4 font-bold text-[color:var(--color-on-surface)]">Buka Periode Baru</h3>
                    
                    <form @submit.prevent="submitBuka" class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Nama Periode</label>
                            <input v-model="formBuka.nama" type="text" placeholder="Contoh: Periode 2026/2027" required class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-3 py-2 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                            <p v-if="formBuka.errors.nama" class="mt-1 text-xs text-red-500">{{ formBuka.errors.nama }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Tanggal Mulai</label>
                            <input v-model="formBuka.tanggal_mulai" type="date" required class="w-full rounded-lg border border-[color:var(--color-outline-variant)] px-3 py-2 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                            <p v-if="formBuka.errors.tanggal_mulai" class="mt-1 text-xs text-red-500">{{ formBuka.errors.tanggal_mulai }}</p>
                        </div>
                        <button type="submit" :disabled="formBuka.processing" class="w-full rounded-lg bg-[color:var(--color-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                            Buka Periode
                        </button>
                    </form>
                </div>

            </div>

            <!-- Right Column: History -->
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-bold text-[color:var(--color-on-surface)]">Riwayat Periode Tabungan</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)] text-xs uppercase text-[color:var(--color-on-surface-variant)]">
                                <tr>
                                    <th class="px-4 py-3">Nama Periode</th>
                                    <th class="px-4 py-3">Tgl Mulai</th>
                                    <th class="px-4 py-3">Tgl Tutup</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                                <tr v-for="p in periodes" :key="p.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                    <td class="px-4 py-3 font-semibold">{{ p.nama }}</td>
                                    <td class="px-4 py-3">{{ formatDate(p.tanggal_mulai) }}</td>
                                    <td class="px-4 py-3">{{ formatDate(p.tanggal_tutup) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                              :class="p.status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'">
                                            {{ p.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="periodes.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-[color:var(--color-secondary)]">Belum ada data periode.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Tutup Periode -->
        <div v-if="showTutupModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-red-600 flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span> Tutup Periode Aktif
                    </h3>
                    <button @click="showTutupModal = false" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-100">
                    <p class="font-bold mb-2">Peringatan Tindakan Tidak Dapat Dibatalkan!</p>
                    <p>Menutup periode akan secara otomatis:</p>
                    <ul class="list-disc pl-5 mt-1 space-y-1">
                        <li>Memotong endapan wajib (Maks. Rp 20.000) dari <b>seluruh</b> akun tabungan nasabah yang bersaldo.</li>
                        <li>Mencatat potongan tersebut sebagai pendapatan BUMDes.</li>
                        <li>Menutup periode transaksi saat ini.</li>
                    </ul>
                </div>

                <form @submit.prevent="submitTutup">
                    <div class="mb-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Tanggal Penutupan</label>
                        <input v-model="formTutup.tanggal_tutup" type="date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none" />
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showTutupModal = false" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100">Batal</button>
                        <button type="submit" :disabled="formTutup.processing" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">lock</span>
                            Ya, Tutup Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
