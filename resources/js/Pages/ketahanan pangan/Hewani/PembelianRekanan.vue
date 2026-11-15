<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    purchases: Object,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const search = ref(props.filters.search || '');

const applyFilter = () => {
    router.get(route('kp.partner.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
        search: search.value,
    }, { preserveState: true });
};

const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    nama_rekanan: '',
    no_hp: '',
    jumlah_kg: 10,
    harga_per_kg: 22000,
    status_pembayaran: 'Lunas',
    metode_pembayaran: 'Tunai',
    keterangan: '',
});

const isSubmitting = ref(false);

const submitForm = () => {
    isSubmitting.value = true;
    router.post(route('kp.partner.store'), form.value, {
        onSuccess: () => { 
            isModalOpen.value = false; 
            isSubmitting.value = false;
        },
        onError: (errs) => {
            isSubmitting.value = false;
            alert(Object.values(errs).join('\n'));
        },
        onFinish: () => { isSubmitting.value = false; }
    });
};

const deletePurchase = (id) => {
    if (confirm('Hapus riwayat pembelian rekanan ini?')) {
        router.delete(route('kp.partner.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Pembelian Rekanan" :unit="unit" :user="user">
        <!-- Header & Action -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Pembelian Rekanan (Telur dari Luar)</h3>
                <p class="text-xs text-slate-500">Pencatatan riwayat pembayaran pembelian telur dari rekanan luar (tidak menambah stok telur internal)</p>
            </div>
            <button 
                @click="isModalOpen = true"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                Catat Pembelian Rekanan
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Pembelian Telur Rekanan</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ Number(summary.total_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-amber-600 font-medium">Berasal dari peternak rekanan luar</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Nominal Pembayaran</p>
                <h4 class="text-2xl font-bold text-emerald-700 mt-1">
                    {{ formatRp(summary.total_biaya) }}
                </h4>
                <div class="mt-2 text-[11px] text-slate-400">Tercatat dalam pengeluaran kas</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Status Stok</p>
                <h4 class="text-sm font-bold text-slate-700 mt-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-amber-500 text-lg">info</span>
                    Tanpa Masuk Stok
                </h4>
                <div class="mt-2 text-[11px] text-slate-500">Hanya mencatat riwayat transaksi pembayaran</div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <input 
                    type="text" 
                    v-model="search" 
                    @keyup.enter="applyFilter" 
                    placeholder="Cari rekanan / keterangan..." 
                    class="text-xs rounded-xl border-slate-200" 
                />
                <button 
                    @click="applyFilter"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors"
                >
                    Terapkan
                </button>
            </div>
        </div>

        <!-- Tabel Pembelian Rekanan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">No.</th>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5">Nama Rekanan</th>
                            <th class="p-3.5">No. HP</th>
                            <th class="p-3.5 text-right">Jumlah (kg)</th>
                            <th class="p-3.5 text-right">Harga / kg</th>
                            <th class="p-3.5 text-right">Total Biaya</th>
                            <th class="p-3.5 text-center">Metode / Status</th>
                            <th class="p-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(item, idx) in purchases.data" :key="item.id" class="hover:bg-slate-50/50">
                            <td class="p-3.5 text-slate-400 font-medium">{{ (purchases.current_page - 1) * purchases.per_page + idx + 1 }}</td>
                            <td class="p-3.5 text-slate-600">{{ item.tanggal }}</td>
                            <td class="p-3.5 font-bold text-slate-800">
                                {{ item.nama_rekanan }}
                                <div v-if="item.keterangan" class="text-[10px] font-normal text-slate-400 mt-0.5">{{ item.keterangan }}</div>
                            </td>
                            <td class="p-3.5 text-slate-600">{{ item.no_hp || '-' }}</td>
                            <td class="p-3.5 text-right font-medium text-slate-700">{{ Number(item.jumlah_kg).toFixed(1) }} kg</td>
                            <td class="p-3.5 text-right font-medium text-slate-700">{{ formatRp(item.harga_per_kg) }}</td>
                            <td class="p-3.5 text-right font-bold text-emerald-600">{{ formatRp(item.total_biaya) }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    {{ item.status_pembayaran }} ({{ item.metode_pembayaran }})
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <button 
                                    @click="deletePurchase(item.id)" 
                                    class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                    title="Hapus"
                                >
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="purchases.data.length === 0">
                            <td colspan="9" class="text-center py-8 text-slate-400">
                                Belum ada riwayat pembelian telur dari rekanan pada periode ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="purchases.links && purchases.links.length > 3" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Total {{ purchases.total }} transaksi pembelian rekanan</span>
                <div class="flex gap-1">
                    <button 
                        v-for="(link, i) in purchases.links" 
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

        <!-- Modal Catat Pembelian Rekanan -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Catat Pembelian Telur Rekanan</h4>
                        <p class="text-[11px] text-slate-400">Mencatat pembayaran telur dari luar (tidak menambah stok internal)</p>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="mt-4 space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi</label>
                        <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Rekanan / Peternak Luar</label>
                        <input type="text" v-model="form.nama_rekanan" placeholder="Contoh: Peternak Bpk. Slamet" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Kontak / HP</label>
                        <input type="text" v-model="form.no_hp" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah (kg)</label>
                            <input type="number" step="0.1" v-model="form.jumlah_kg" class="w-full text-xs rounded-xl border-slate-200 font-bold" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga per kg</label>
                            <input type="number" v-model="form.harga_per_kg" class="w-full text-xs rounded-xl border-slate-200 font-bold" required />
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 rounded-xl flex items-center justify-between text-xs text-emerald-900">
                        <span class="font-semibold">Total Pembayaran:</span>
                        <span class="text-base font-extrabold text-emerald-700">{{ formatRp(form.jumlah_kg * form.harga_per_kg) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pembayaran</label>
                            <select v-model="form.status_pembayaran" class="w-full text-xs rounded-xl border-slate-200">
                                <option value="Lunas">Lunas</option>
                                <option value="Hutang / Belum Lunas">Hutang / Belum Lunas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode</label>
                            <select v-model="form.metode_pembayaran" class="w-full text-xs rounded-xl border-slate-200">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="2" placeholder="Catatan pembelian rekanan..." class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl" :disabled="isSubmitting">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

