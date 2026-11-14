<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    expenses: Object,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const jenisBiaya = ref(props.filters.jenis_biaya || '');

const applyFilter = () => {
    router.get(route('kp.finance.pengeluaran'), {
        start_date: startDate.value,
        end_date: endDate.value,
        jenis_biaya: jenisBiaya.value,
    }, { preserveState: true });
};

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    uraian: '',
    jenis_biaya: 'variabel',
    kategori_biaya: 'Biaya Operasional',
    nominal: 50000,
    keterangan: '',
});

const kategoriOptions = {
    tetap: ['Biaya Sewa', 'Biaya Pajak', 'Biaya Tenaga Kerja', 'Biaya Penyusutan Bibit Ayam'],
    variabel: ['Biaya Bahan Baku Pakan', 'Biaya Vaksin dan Obat', 'Biaya Operasional', 'Biaya Promosi Penjualan', 'Biaya Lain-Lain']
};

const submitForm = () => {
    router.post(route('kp.finance.pengeluaran.store'), form.value, {
        onSuccess: () => { isModalOpen.value = false; }
    });
};

const deleteExpense = (id) => {
    if (confirm('Hapus pencatatan pengeluaran ini?')) {
        router.delete(route('kp.finance.pengeluaran.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Laporan Pengeluaran Hewani" :unit="unit" :user="user">
        <!-- Summary Cards Sesuai PRD (Biaya Tetap & Variabel) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Biaya Tetap (Fixed Cost)</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">{{ formatRp(summary.total_tetap) }}</h4>
                <p class="mt-2 text-[11px] text-slate-400">Tenaga kerja, sewa, pajak, penyusutan bibit</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Biaya Variabel (Variable Cost)</p>
                <h4 class="text-2xl font-bold text-amber-600 mt-1">{{ formatRp(summary.total_variabel) }}</h4>
                <p class="mt-2 text-[11px] text-slate-400">Pakan, vaksin & obat, operasional harian</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-xs bg-gradient-to-br from-rose-50/40 to-white flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-rose-800">Total Pengeluaran</p>
                    <h4 class="text-2xl font-extrabold text-rose-600 mt-1">{{ formatRp(summary.total_pengeluaran) }}</h4>
                </div>
                <button 
                    @click="isModalOpen = true"
                    class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">add</span>
                    Tambah Biaya
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                    Rekap Beban Pengeluaran Unit Hewani
                </h4>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="jenisBiaya" @change="applyFilter" class="text-xs rounded-xl border-slate-200">
                    <option value="">Semua Jenis Biaya</option>
                    <option value="tetap">Biaya Tetap</option>
                    <option value="variabel">Biaya Variabel</option>
                </select>
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
            </div>
        </div>

        <!-- Tabel Pengeluaran -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Uraian Pengeluaran</th>
                            <th class="p-3">Jenis Biaya</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3 text-right">Nominal</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-slate-50/50">
                            <td class="p-3 text-slate-600">{{ exp.tanggal }}</td>
                            <td class="p-3 font-bold text-slate-800">{{ exp.uraian }}</td>
                            <td class="p-3">
                                <span :class="[
                                    'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                    exp.jenis_biaya === 'tetap' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'
                                ]">
                                    {{ exp.jenis_biaya === 'tetap' ? 'Biaya Tetap' : 'Biaya Variabel' }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-700 font-medium">{{ exp.kategori_biaya }}</td>
                            <td class="p-3 text-right font-bold text-rose-600">{{ formatRp(exp.nominal) }}</td>
                            <td class="p-3 text-slate-400">{{ exp.keterangan || '-' }}</td>
                            <td class="p-3 text-center">
                                <button 
                                    v-if="!exp.referensi_tipe" 
                                    @click="deleteExpense(exp.id)" 
                                    class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                    title="Hapus"
                                >
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                                <span v-else class="text-[10px] text-slate-400 italic">Otomatis Sistem</span>
                            </td>
                        </tr>
                        <tr v-if="expenses.data.length === 0">
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                Tidak ada catatan pengeluaran pada rentang tanggal ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Pengeluaran -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Catat Biaya Pengeluaran</h4>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian Biaya</label>
                        <input type="text" v-model="form.uraian" placeholder="Misal: Beli vitamin ayam / gaji operator" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Biaya</label>
                            <select v-model="form.jenis_biaya" class="w-full text-xs rounded-xl border-slate-200">
                                <option value="variabel">Biaya Variabel</option>
                                <option value="tetap">Biaya Tetap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Biaya</label>
                            <select v-model="form.kategori_biaya" class="w-full text-xs rounded-xl border-slate-200">
                                <option v-for="cat in kategoriOptions[form.jenis_biaya]" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                        <input type="number" v-model="form.nominal" class="w-full text-xs rounded-xl border-slate-200 font-bold text-rose-600" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                        <textarea v-model="form.keterangan" rows="2" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl">Simpan Pengeluaran</button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

