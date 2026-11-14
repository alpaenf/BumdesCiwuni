<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    otherIncomes: Object,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilter = () => {
    router.get(route('kp.finance.pendapatan'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    uraian: '',
    kategori: 'Penjualan Kotoran/Pupuk Kandang',
    nominal: 50000,
    keterangan: '',
});

const submitForm = () => {
    router.post(route('kp.finance.pendapatan.store'), form.value, {
        onSuccess: () => { isModalOpen.value = false; }
    });
};

const deleteIncome = (id) => {
    if (confirm('Hapus pendapatan lain-lain ini?')) {
        router.delete(route('kp.finance.pendapatan.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Pendapatan Hewani" :unit="unit" :user="user">
        <!-- Summary Cards Sesuai PRD (Hasil Penjualan + Lain-lain) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Hasil Penjualan Telur</p>
                <h4 class="text-2xl font-bold text-emerald-700 mt-1">{{ formatRp(summary.penjualan_rp) }}</h4>
                <p class="mt-2 text-[11px] text-slate-400">Tercatat otomatis dari kasir penjualan telur</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Pendapatan Lain-Lain</p>
                <h4 class="text-2xl font-bold text-blue-600 mt-1">{{ formatRp(summary.pendapatan_lain_rp) }}</h4>
                <p class="mt-2 text-[11px] text-slate-400">Penjualan pupuk kandang/kotoran, karung bekas, dll</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-xs bg-gradient-to-br from-emerald-50/40 to-white flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-800">Total Pendapatan Unit</p>
                    <h4 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ formatRp(summary.total_pendapatan_rp) }}</h4>
                </div>
                <button 
                    @click="isModalOpen = true"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">add</span>
                    Tambah Lain-Lain
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Daftar Pendapatan Lain-Lain
                </h4>
                <p class="text-[11px] text-slate-500">Tidak termasuk penjualan telur untuk mencegah pencatatan ganda</p>
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

        <!-- Tabel Pendapatan Lain -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Uraian Pendapatan</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3 text-right">Nominal</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="inc in otherIncomes.data" :key="inc.id" class="hover:bg-slate-50/50">
                            <td class="p-3 text-slate-600">{{ inc.tanggal }}</td>
                            <td class="p-3 font-bold text-slate-800">{{ inc.uraian }}</td>
                            <td class="p-3 text-slate-600">{{ inc.kategori }}</td>
                            <td class="p-3 text-right font-bold text-emerald-600">{{ formatRp(inc.nominal) }}</td>
                            <td class="p-3 text-slate-400">{{ inc.keterangan || '-' }}</td>
                            <td class="p-3 text-center">
                                <button @click="deleteIncome(inc.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50" title="Hapus">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="otherIncomes.data.length === 0">
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                Belum ada pemasukan lain-lain pada rentang tanggal ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Pendapatan Lain -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Catat Pendapatan Lain-Lain</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian</label>
                        <input type="text" v-model="form.uraian" placeholder="Cth: Penjualan pupuk kotoran ayam 20 karung" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                        <select v-model="form.kategori" class="w-full text-xs rounded-xl border-slate-200">
                            <option value="Penjualan Kotoran/Pupuk Kandang">Penjualan Kotoran / Pupuk Kandang</option>
                            <option value="Penjualan Karung Bekas">Penjualan Karung Bekas</option>
                            <option value="Ayam Afkir">Penjualan Ayam Afkir</option>
                            <option value="Lain-Lain">Lain-Lain</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" v-model="form.nominal" class="w-full text-xs rounded-xl border-slate-200 font-bold text-emerald-600" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">Simpan Pemasukan</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

