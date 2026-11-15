<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    product: Object,
    customers: Array,
    sales: Object,
    filters: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const customerType = ref(props.filters.customer_type || '');
const search = ref(props.filters.search || '');

const applyFilter = () => {
    router.get(route('kp.sale.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
        customer_type: customerType.value,
        search: search.value,
    }, { preserveState: true });
};

// Modal Penjualan Baru
const isModalOpen = ref(false);
const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    customer_id: '',
    nama_pelanggan: '',
    customer_type: 'Eceran',
    jumlah_kg: 5.0,
    harga_per_kg: props.product.harga_default || 22000,
    jumlah_pembayaran: 110000,
    metode_pembayaran: 'Tunai',
    keterangan: '',
});

const onSelectCustomer = (e) => {
    const custId = e.target.value;
    if (custId) {
        const found = props.customers.find(c => c.id == custId);
        if (found) {
            form.value.nama_pelanggan = found.nama;
            form.value.customer_type = found.customer_type;
        }
    }
};

const updatePayment = () => {
    form.value.jumlah_pembayaran = Math.round(form.value.jumlah_kg * form.value.harga_per_kg);
};

const isSubmitting = ref(false);
const formErrors = ref({});

const submitForm = () => {
    isSubmitting.value = true;
    formErrors.value = {};
    router.post(route('kp.sale.store'), form.value, {
        onSuccess: () => { 
            isModalOpen.value = false; 
            isSubmitting.value = false;
        },
        onError: (errs) => {
            isSubmitting.value = false;
            formErrors.value = errs;
            alert(Object.values(errs).join('\n'));
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const deleteSale = (id) => {
    if (confirm('Batalkan transaksi penjualan ini? Stok telur akan dikembalikan dan catatan arus kas akan dihapus.')) {
        router.delete(route('kp.sale.destroy', id));
    }
};

const openStruk = (saleId) => {
    window.open(route('kp.sale.struk', saleId), '_blank', 'width=420,height=600');
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Transaksi Penjualan Telur" :unit="unit" :user="user">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Stok Telur Bagus Tersedia</p>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ Number(summary.stok_tersedia_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-slate-400">Siap ditransaksikan langsung ke pembeli</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Telur Terjual (Periode)</p>
                <h4 class="text-2xl font-bold text-slate-800 mt-1">
                    {{ Number(summary.total_terjual_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-emerald-600 font-semibold">{{ formatRp(summary.total_pendapatan_rp) }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-800">Kasir / Buat Nota Baru</p>
                    <p class="text-[11px] text-slate-400">Cetak struk thermal & kurangi stok otomatis</p>
                </div>
                <button 
                    @click="isModalOpen = true"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">point_of_sale</span>
                    Transaksi Baru
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex-1 min-w-[200px] relative">
                <input 
                    type="text" 
                    v-model="search"
                    @keyup.enter="applyFilter"
                    placeholder="Cari No. Struk atau nama pembeli..."
                    class="w-full text-xs pl-3 pr-4 py-2 rounded-xl border-slate-200"
                />
            </div>
            <div class="flex items-center gap-2">
                <select v-model="customerType" @change="applyFilter" class="text-xs rounded-xl border-slate-200">
                    <option value="">Semua Kategori</option>
                    <option value="Eceran">Eceran</option>
                    <option value="Agen">Agen</option>
                </select>
                <input type="date" v-model="startDate" class="text-xs rounded-xl border-slate-200" />
                <span class="text-xs text-slate-400">s/d</span>
                <input type="date" v-model="endDate" class="text-xs rounded-xl border-slate-200" />
                <button @click="applyFilter" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                    Filter
                </button>
            </div>
        </div>

        <!-- Tabel Transaksi Penjualan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3">No. Struk</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Nama Customer</th>
                            <th class="p-3 text-right">Qty (kg)</th>
                            <th class="p-3 text-right">Harga Satuan per kg</th>
                            <th class="p-3 text-right">Total Transaksi</th>
                            <th class="p-3">Metode & Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-slate-50/50">
                            <td class="p-3 font-mono font-bold text-slate-800">{{ sale.nomor_transaksi }}</td>
                            <td class="p-3 text-slate-600">{{ sale.tanggal }}</td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800">{{ sale.nama_pelanggan }}</div>
                                <span :class="[
                                    'inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold',
                                    sale.customer_type === 'Agen' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700'
                                ]">
                                    {{ sale.customer_type }}
                                </span>
                            </td>
                            <td class="p-3 text-right font-bold text-slate-800">{{ Number(sale.jumlah_kg).toFixed(1) }} kg</td>
                            <td class="p-3 text-right text-slate-600">{{ formatRp(sale.harga_per_kg) }}</td>
                            <td class="p-3 text-right font-extrabold text-emerald-600">{{ formatRp(sale.total_harga) }}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-700">{{ sale.metode_pembayaran }}</div>
                                <span :class="[
                                    'text-[10px] font-bold',
                                    sale.status_pembayaran === 'Lunas' ? 'text-emerald-600' : 'text-rose-600'
                                ]">
                                    {{ sale.status_pembayaran }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        @click="openStruk(sale.id)"
                                        class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold flex items-center gap-1"
                                        title="Cetak Struk"
                                    >
                                        <span class="material-symbols-outlined text-sm">print</span>
                                        Struk
                                    </button>
                                    <button 
                                        @click="deleteSale(sale.id)"
                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                        title="Batalkan Transaksi"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                Belum ada riwayat transaksi penjualan pada periode ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="sales.links && sales.links.length > 3" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Total {{ sales.total }} transaksi</span>
                <div class="flex gap-1">
                    <button 
                        v-for="(link, i) in sales.links" 
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

        <!-- Modal Transaksi Penjualan Baru -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Kasir Penjualan Telur Ayam</h4>
                        <p class="text-xs text-slate-400">Stok telur bagus: {{ summary.stok_tersedia_kg }} kg</p>
                    </div>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi</label>
                            <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih dari Database Pelanggan</label>
                            <select v-model="form.customer_id" @change="onSelectCustomer" class="w-full text-xs rounded-xl border-slate-200">
                                <option value="">-- Pelanggan Bebas / Baru --</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.nama }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Customer (Wajib)</label>
                            <input type="text" v-model="form.nama_pelanggan" class="w-full text-xs rounded-xl border-slate-200" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Customer</label>
                            <select v-model="form.customer_type" class="w-full text-xs rounded-xl border-slate-200" required>
                                <option value="Eceran">Eceran</option>
                                <option value="Agen">Agen</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Pembelian (kg)</label>
                            <input 
                                type="number" 
                                step="0.1" 
                                v-model="form.jumlah_kg" 
                                @input="updatePayment"
                                class="w-full text-xs rounded-xl border-slate-200 font-bold" 
                                required 
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Satuan per kg</label>
                            <input 
                                type="number" 
                                v-model="form.harga_per_kg" 
                                @input="updatePayment"
                                class="w-full text-xs rounded-xl border-slate-200" 
                                required 
                            />
                        </div>
                    </div>

                    <!-- Total Tagihan Box -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center justify-between text-emerald-900">
                        <span class="text-xs font-semibold">Total Tagihan:</span>
                        <span class="text-lg font-extrabold">{{ formatRp(form.jumlah_kg * form.harga_per_kg) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Uang Diterima</label>
                            <input type="number" v-model="form.jumlah_pembayaran" class="w-full text-xs rounded-xl border-slate-200 font-semibold" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran</label>
                            <select v-model="form.metode_pembayaran" class="w-full text-xs rounded-xl border-slate-200" required>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="Qris">QRIS</option>
                                <option value="Hutang">Hutang / Piutang</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.jumlah_pembayaran > (form.jumlah_kg * form.harga_per_kg)" class="text-xs text-slate-600 font-medium text-right">
                        Kembalian: <span class="font-bold text-emerald-600">{{ formatRp(form.jumlah_pembayaran - (form.jumlah_kg * form.harga_per_kg)) }}</span>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl" :disabled="isSubmitting">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl flex items-center gap-1.5" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="material-symbols-outlined notranslate text-xs animate-spin">progress_activity</span>
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan & Cetak Struk' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

