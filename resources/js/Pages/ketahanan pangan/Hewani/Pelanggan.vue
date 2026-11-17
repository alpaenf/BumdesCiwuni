<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const customerTypeFilter = ref(props.filters.customer_type || '');

const isModalOpen = ref(false);
const editingCustomer = ref(null);

const form = ref({
    tanggal: new Date().toISOString().split('T')[0],
    nama: '',
    customer_type: 'Eceran',
    no_hp: '',
    alamat: '',
    keterangan: '',
});

const handleSearch = () => {
    router.get(route('kp.customers.index'), {
        search: search.value,
        customer_type: customerTypeFilter.value,
    }, { preserveState: true, replace: true });
};

const openCreateModal = () => {
    editingCustomer.value = null;
    form.value = {
        tanggal: new Date().toISOString().split('T')[0],
        nama: '',
        customer_type: 'Eceran',
        no_hp: '',
        alamat: '',
        keterangan: '',
    };
    isModalOpen.value = true;
};

const openEditModal = (cust) => {
    editingCustomer.value = cust;
    form.value = {
        tanggal: cust.tanggal || new Date().toISOString().split('T')[0],
        nama: cust.nama,
        customer_type: cust.customer_type || 'Eceran',
        no_hp: cust.no_hp || '',
        alamat: cust.alamat || '',
        keterangan: cust.keterangan || '',
    };
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingCustomer.value) {
        router.put(route('kp.customers.update', editingCustomer.value.id), form.value, {
            onSuccess: () => { isModalOpen.value = false; }
        });
    } else {
        router.post(route('kp.customers.store'), form.value, {
            onSuccess: () => { isModalOpen.value = false; }
        });
    }
};

const deleteCustomer = (id) => {
    if (confirm('Yakin ingin menghapus data pelanggan ini?')) {
        router.delete(route('kp.customers.destroy', id));
    }
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');
</script>

<template>
    <KetahananPanganLayout title="Data Pelanggan Hewani" :unit="unit" :user="user">
        <!-- Header & Action -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Data Base Pelanggan Unit Hewani</h3>
                <p class="text-xs text-slate-500">Pencatatan database pelanggan telur ayam petelur</p>
            </div>
            <button 
                @click="openCreateModal"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2"
            >
                <span class="material-symbols-outlined text-base">person_add</span>
                Tambah Pelanggan Baru
            </button>
        </div>

        <!-- Filter & Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex-1 min-w-[240px] relative">
                <span class="material-symbols-outlined text-slate-400 absolute left-3 top-2.5 text-lg">search</span>
                <input 
                    type="text" 
                    v-model="search"
                    @keyup.enter="handleSearch"
                    placeholder="Cari nama pelanggan, nomor HP, atau alamat..."
                    class="w-full text-xs pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                />
            </div>
            <div class="flex items-center gap-2">
                <select
                    v-model="customerTypeFilter"
                    @change="handleSearch"
                    class="text-xs px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-700 font-semibold"
                >
                    <option value="">Semua Kategori</option>
                    <option value="Eceran">Eceran</option>
                    <option value="Agen">Agen</option>
                </select>
                <button 
                    @click="handleSearch"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors"
                >
                    Cari
                </button>
            </div>
        </div>

        <!-- Tabel Pelanggan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200/80">
                        <tr>
                            <th class="p-3.5">No.</th>
                            <th class="p-3.5">Tanggal</th>
                            <th class="p-3.5">Nama Customer</th>
                            <th class="p-3.5 text-center">Kategori</th>
                            <th class="p-3.5">No. HP / WA</th>
                            <th class="p-3.5 text-right">Harga Satuan per kg</th>
                            <th class="p-3.5 text-right">Jml Pembelian</th>
                            <th class="p-3.5 text-right">Total Transaksi</th>
                            <th class="p-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="(cust, idx) in customers.data" :key="cust.id" class="hover:bg-slate-50/50">
                            <td class="p-3.5 text-slate-400 font-medium">{{ (customers.current_page - 1) * customers.per_page + idx + 1 }}</td>
                            <td class="p-3.5 text-slate-600">{{ cust.tanggal || '-' }}</td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-800">{{ cust.nama }}</div>
                            </td>
                            <td class="p-3.5 text-center">
                                <span :class="[
                                    'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    cust.customer_type === 'Agen'
                                        ? 'bg-purple-100 text-purple-700 border border-purple-200'
                                        : 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                                ]">
                                    {{ cust.customer_type || 'Eceran' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-slate-600">{{ cust.no_hp || '-' }}</td>
                            <td class="p-3.5 text-right font-medium text-slate-700">
                                {{ cust.egg_sales && cust.egg_sales.length > 0 ? formatRp(cust.egg_sales[0].harga_per_kg) : '-' }}
                            </td>
                            <td class="p-3.5 text-right font-medium text-slate-700">{{ Number(cust.egg_sales_sum_jumlah_kg || 0).toFixed(1) }} kg</td>
                            <td class="p-3.5 text-right font-bold text-emerald-600">{{ formatRp(cust.egg_sales_sum_total_harga || 0) }}</td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        @click="openEditModal(cust)" 
                                        class="p-1 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50"
                                        title="Edit"
                                    >
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button 
                                        @click="deleteCustomer(cust.id)" 
                                        class="p-1 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50"
                                        title="Hapus"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                Tidak ada data pelanggan yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="customers.total > 0" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ customers.from || 0 }} - {{ customers.to || 0 }} dari {{ customers.total }} data</span>
                <div v-if="customers.links && customers.links.length > 3" class="flex gap-1">
                    <button 
                        v-for="(link, i) in customers.links" 
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

        <!-- Modal Tambah / Edit -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">
                        {{ editingCustomer ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' }}
                    </h4>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="mt-4 space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pendaftaran</label>
                        <input type="date" v-model="form.tanggal" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Customer (Wajib)</label>
                        <input type="text" v-model="form.nama" placeholder="Contoh: Toko Berkah / Ibu Siti" class="w-full text-xs rounded-xl border-slate-200" required />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Pelanggan *</label>
                        <select v-model="form.customer_type" class="w-full text-xs rounded-xl border-slate-200" required>
                            <option value="Eceran">Eceran (Konsumen Langsung)</option>
                            <option value="Agen">Agen (Toko / Reseller / Rekanan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" v-model="form.no_hp" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border-slate-200" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
                        <textarea v-model="form.alamat" rows="2" placeholder="Alamat pelanggan / domisili RT/RW" class="w-full text-xs rounded-xl border-slate-200"></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl">
                            Simpan Pelanggan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>

