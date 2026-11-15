<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    product: Object,
    productions: Object,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('kp.production.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    hari: '',
    umur_ke: 1,
    umur_minggu: 24,
    hari_ke: 1,
    produksi_butir: 800,
    butir_per_kg: 16.0,
    produksi_kg: 50.0,
    harga_per_kg: props.product.harga_default || 22000,
    telur_retak_butir: 0,
    keterangan_retak: '',
    telur_rusak_butir: 0,
    keterangan_rusak: '',
});

const hitungManualDariButir = () => {
    const butir = Number(form.value.produksi_butir) || 0;
    const rasio = Number(form.value.butir_per_kg) || 16;
    if (rasio > 0) {
        form.value.produksi_kg = Number((butir / rasio).toFixed(2));
    }
};

const hitungManualDariKg = () => {
    const kg = Number(form.value.produksi_kg) || 0;
    const rasio = Number(form.value.butir_per_kg) || 16;
    if (rasio > 0) {
        form.value.produksi_butir = Math.round(kg * rasio);
    }
};

const submitForm = () => {
    router.post(route('kp.production.store'), form.value, {
        onSuccess: () => { isModalOpen.value = false; }
    });
};

const deleteProduction = (id) => {
    if (confirm('Hapus catatan produksi ini? Stok telur bagus akan disesuaikan.')) {
        router.delete(route('kp.production.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Pencatatan Produksi Telur" :unit="unit" :user="user">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Butir (Periode)</p>
                <h4 class="text-2xl font-bold text-blue-600 mt-1">
                    {{ (summary.total_produksi_butir || 0).toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <div class="mt-2 text-[11px] text-blue-500 font-medium">Hasil panen fisik harian</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Stok Telur Bagus Siap Jual</p>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ Number(summary.stok_saat_ini_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-slate-400">Gudang Penyimpanan BUMDes</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Telur Retak</p>
                <h4 class="text-2xl font-bold text-amber-600 mt-1">
                    {{ summary.total_retak_butir }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <div class="mt-2 text-[11px] text-amber-600 font-medium">Dicatat terpisah dari stok utama</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-center items-start">
                <p class="text-xs font-medium text-slate-500">Telur Rusak / Pecah</p>
                <h4 class="text-2xl font-bold text-rose-600 mt-1">
                    {{ summary.total_rusak_butir }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <button 
                    @click="isModalOpen = true"
                    class="mt-2 w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Input Produksi Harian
                </button>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Produksi (Periode)</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ Number(summary.total_produksi_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-emerald-600 font-semibold">Telur Cokelat/Bagus</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Rekap Hasil Produksi Budidaya Ayam Ras Petelur (01462)
                </h4>
                <p class="text-[11px] text-slate-500">Sesuai Dokumen Administrasi Rekap Fisik BUMDes Ciwuni</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
            </div>
        </div>

        <!-- Tabel Produksi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">Hari & Tgl</th>
                            <th class="p-3">Umur Ayam</th>
                            <th class="p-3 text-right">Butir</th>
                            <th class="p-3 text-right">Produksi (kg)</th>
                            <th class="p-3 text-right">Stok Sblm (kg)</th>
                            <th class="p-3 text-right">Sisa Stok (kg)</th>
                            <th class="p-3 text-right">Harga @ Rp/kg</th>
                            <th class="p-3 text-right">Estimasi Nilai</th>
                            <th class="p-3 text-center">Retak (btr)</th>
                            <th class="p-3 text-center">Rusak (btr)</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="prod in productions.data" :key="prod.id" class="hover:bg-slate-50/50">
                            <td class="p-3 font-semibold text-slate-800">
                                <div>{{ prod.hari || '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ prod.tanggal }}</div>
                            </td>
                            <td class="p-3 text-slate-600">
                                Mgg {{ prod.umur_minggu || '-' }} (Hr {{ prod.hari_ke || '-' }})
                            </td>
                            <td class="p-3 text-right font-bold text-blue-600">{{ prod.produksi_butir ? Number(prod.produksi_butir).toLocaleString('id-ID') : '-' }}</td>
                            <td class="p-3 text-right font-bold text-emerald-600">{{ Number(prod.produksi_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right text-slate-500">{{ Number(prod.stok_sebelum_kg).toFixed(1) }}</td>
                            <td class="p-3 text-right font-bold text-slate-800">{{ Number(prod.sisa_stok_kg).toFixed(1) }}</td>
                            <td class="p-3 text-right text-slate-600">{{ formatRp(prod.harga_per_kg) }}</td>
                            <td class="p-3 text-right font-medium text-emerald-700">{{ formatRp(prod.pendapatan_estimasi) }}</td>
                            <td class="p-3 text-center">
                                <span v-if="prod.telur_retak_butir > 0" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">
                                    {{ prod.telur_retak_butir }}
                                </span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="p-3 text-center">
                                <span v-if="prod.telur_rusak_butir > 0" class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">
                                    {{ prod.telur_rusak_butir }}
                                </span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="p-3 text-center">
                                <button @click="deleteProduction(prod.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="productions.data.length === 0">
                            <td colspan="11" class="text-center py-8 text-slate-400">
                                Belum ada catatan hasil produksi pada periode ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="productions.links && productions.links.length > 3" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Total {{ productions.total }} catatan</span>
                <div class="flex gap-1">
                    <button 
                        v-for="(link, i) in productions.links" 
                        :key="i"
                        :disabled="!link.url"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                        :class="[
                            'px-2.5 py-1 rounded-lg text-xs font-medium',
                            link.active ? 'bg-emerald-600 text-white' : 'hover:bg-slate-100 text-slate-600',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Modal Input Produksi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Input Produksi Telur Harian</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Umur Minggu Ke</label>
                            <input type="number" v-model="form.umur_minggu" class="w-full text-xs rounded-xl border-slate-200" />
                        </div>
                    </div>
                    <!-- Kalkulator Manual Butir ke Kg -->
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-900 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">calculate</span>
                                Hitung Manual Butir & kg
                            </span>
                            <span class="text-[10px] text-blue-600 font-medium">Bisa diisi manual dua arah</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jumlah Butir</label>
                                <input 
                                    type="number" 
                                    v-model="form.produksi_butir" 
                                    @input="hitungManualDariButir" 
                                    placeholder="Cth: 800"
                                    class="w-full text-xs rounded-xl border-slate-200 font-bold text-blue-700" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Rasio (Butir/kg)</label>
                                <input 
                                    type="number" 
                                    step="0.1" 
                                    v-model="form.butir_per_kg" 
                                    @input="hitungManualDariButir" 
                                    placeholder="16"
                                    class="w-full text-xs rounded-xl border-slate-200 font-semibold" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Total kg (Masuk Stok)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    v-model="form.produksi_kg" 
                                    @input="hitungManualDariKg" 
                                    class="w-full text-xs rounded-xl border-slate-200 font-bold text-emerald-700" 
                                    required 
                                />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Acuan @ Rp/kg</label>
                        <input type="number" v-model="form.harga_per_kg" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-3 bg-amber-50/50 rounded-xl border border-amber-100">
                        <div>
                            <label class="block text-xs font-semibold text-amber-800 mb-1">Telur Retak (butir)</label>
                            <input type="number" v-model="form.telur_retak_butir" class="w-full text-xs rounded-xl border-amber-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-amber-800 mb-1">Keterangan Retak</label>
                            <input type="text" v-model="form.keterangan_retak" placeholder="Cth: saat panen pagi" class="w-full text-xs rounded-xl border-amber-200" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-3 bg-rose-50/50 rounded-xl border border-rose-100">
                        <div>
                            <label class="block text-xs font-semibold text-rose-800 mb-1">Telur Rusak/Pecah (butir)</label>
                            <input type="number" v-model="form.telur_rusak_butir" class="w-full text-xs rounded-xl border-rose-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-rose-800 mb-1">Keterangan Rusak</label>
                            <input type="text" v-model="form.keterangan_rusak" placeholder="Cth: cangkang lunak" class="w-full text-xs rounded-xl border-rose-200" />
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">Simpan Produksi</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

