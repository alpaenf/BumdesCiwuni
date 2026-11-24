<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import KetahananPanganLayout from '@/Layouts/KetahananPangan/KetahananPanganLayout.vue';

const props = defineProps({
    unit: Object,
    user: Object,
    product: Object,
    productions: Object,
    latestProduction: Object,
    filters: Object,
    summary: Object,
});

const getLocalDate = () => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const formatDateId = (dateStr) => {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
};

const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

const updateHariName = (tanggalStr) => {
    if (!tanggalStr) return '';
    const date = new Date(tanggalStr + 'T00:00:00');
    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return dayNames[date.getDay()] || '';
};

// Filter State
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const activePreset = ref(props.filters.filter_preset || '');
const isAllData = ref(props.filters.is_all || false);

// Dropdown Bulan & Tahun Picker
const months = [
    { value: 1, name: 'Januari' },
    { value: 2, name: 'Februari' },
    { value: 3, name: 'Maret' },
    { value: 4, name: 'April' },
    { value: 5, name: 'Mei' },
    { value: 6, name: 'Juni' },
    { value: 7, name: 'Juli' },
    { value: 8, name: 'Agustus' },
    { value: 9, name: 'September' },
    { value: 10, name: 'Oktober' },
    { value: 11, name: 'November' },
    { value: 12, name: 'Desember' },
];

const selectedMonth = ref('');
const selectedYear = ref(new Date().getFullYear());

// Inisialisasi selectedMonth jika filter mencocokkan bulan tertentu
if (props.filters.start_date && props.filters.end_date) {
    const s = new Date(props.filters.start_date);
    const e = new Date(props.filters.end_date);
    if (s.getFullYear() === e.getFullYear() && s.getMonth() === e.getMonth() && s.getDate() === 1) {
        selectedMonth.value = s.getMonth() + 1;
        selectedYear.value = s.getFullYear();
    }
}

const applyFilter = () => {
    isAllData.value = false;
    activePreset.value = 'custom';
    router.get(route('kp.production.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
        filter_preset: 'custom',
    }, { preserveState: true });
};

const filterToday = () => {
    const today = props.filters.today || getLocalDate();
    startDate.value = today;
    endDate.value = today;
    activePreset.value = 'today';
    isAllData.value = false;
    selectedMonth.value = '';
    router.get(route('kp.production.index'), {
        start_date: today,
        end_date: today,
        filter_preset: 'today',
    }, { preserveState: true });
};

const filterThisMonth = () => {
    const d = new Date();
    const year = d.getFullYear();
    const month = d.getMonth() + 1;
    const today = props.filters.today || getLocalDate();
    const start = `${year}-${String(month).padStart(2, '0')}-01`;
    
    startDate.value = start;
    endDate.value = today;
    activePreset.value = 'this_month';
    isAllData.value = false;
    selectedMonth.value = month;
    selectedYear.value = year;
    
    router.get(route('kp.production.index'), {
        start_date: start,
        end_date: today,
        filter_preset: 'this_month',
    }, { preserveState: true });
};

const filterLastMonth = () => {
    const d = new Date();
    d.setDate(1);
    d.setMonth(d.getMonth() - 1);
    const year = d.getFullYear();
    const month = d.getMonth() + 1;
    const start = `${year}-${String(month).padStart(2, '0')}-01`;
    const lastDay = new Date(year, month, 0).getDate();
    const end = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
    
    startDate.value = start;
    endDate.value = end;
    activePreset.value = 'last_month';
    isAllData.value = false;
    selectedMonth.value = month;
    selectedYear.value = year;
    
    router.get(route('kp.production.index'), {
        start_date: start,
        end_date: end,
        filter_preset: 'last_month',
    }, { preserveState: true });
};

const onMonthSelect = () => {
    if (!selectedMonth.value) return;
    const year = Number(selectedYear.value) || new Date().getFullYear();
    const month = Number(selectedMonth.value);
    
    const start = `${year}-${String(month).padStart(2, '0')}-01`;
    const lastDay = new Date(year, month, 0).getDate();
    const end = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
    
    startDate.value = start;
    endDate.value = end;
    activePreset.value = 'month_picker';
    isAllData.value = false;
    
    router.get(route('kp.production.index'), {
        start_date: start,
        end_date: end,
        filter_preset: 'month_picker',
    }, { preserveState: true });
};

const filterAll = () => {
    startDate.value = '';
    endDate.value = '';
    activePreset.value = 'all';
    isAllData.value = true;
    selectedMonth.value = '';
    
    router.get(route('kp.production.index'), {
        all: 1,
        filter_preset: 'all',
    }, { preserveState: true });
};

// Modal Input / Edit Produksi State
const isModalOpen = ref(false);
const isSubmitting = ref(false);
const editingProd = ref(null);
const form = ref({
    tanggal: getLocalDate(),
    hari: '',
    umur_ke: 1,
    umur_minggu: 24,
    hari_ke: 1,
    produksi_butir: '',
    butir_per_kg: 16.0,
    produksi_kg: '',
    harga_per_kg: 22000,
    telur_retak_butir: 0,
    keterangan_retak: '',
    telur_rusak_butir: 0,
    keterangan_rusak: '',
});

// Hitung otomatis Rasio (Butir / kg) saat butir atau kg diisi
const hitungRasioOtomatis = () => {
    const butir = Number(form.value.produksi_butir) || 0;
    const kg = Number(form.value.produksi_kg) || 0;
    if (butir > 0 && kg > 0) {
        form.value.butir_per_kg = Number((butir / kg).toFixed(2));
    }
};

// Jika user mengubah rasio secara manual, hitung ulang kg
const hitungDariRasio = () => {
    const butir = Number(form.value.produksi_butir) || 0;
    const rasio = Number(form.value.butir_per_kg) || 0;
    if (butir > 0 && rasio > 0) {
        form.value.produksi_kg = Number((butir / rasio).toFixed(2));
    }
};

const hitungManualDariButir = () => {
    const butir = Number(form.value.produksi_butir) || 0;
    const rasio = Number(form.value.butir_per_kg) || 0;
    if (butir > 0 && rasio > 0 && !form.value.produksi_kg) {
        form.value.produksi_kg = Number((butir / rasio).toFixed(2));
    } else {
        hitungRasioOtomatis();
    }
};

const hitungManualDariKg = () => {
    hitungRasioOtomatis();
};

const onTanggalChange = () => {
    form.value.hari = updateHariName(form.value.tanggal);
};

const openCreateModal = () => {
    editingProd.value = null;
    let nextUmurKe = (props.productions.total || 0) + 1;
    let nextMinggu = 24;
    let nextHariKe = 1;
    let nextHarga = (props.product && props.product.harga_default) ? Number(props.product.harga_default) : 22000;
    let nextRasio = 16.0;

    if (props.latestProduction) {
        nextUmurKe = (props.latestProduction.umur_ke || 0) + 1;
        const lastHari = Number(props.latestProduction.hari_ke) || 1;
        const lastMinggu = Number(props.latestProduction.umur_minggu) || 24;
        if (lastHari >= 7) {
            nextMinggu = lastMinggu + 1;
            nextHariKe = 1;
        } else {
            nextMinggu = lastMinggu;
            nextHariKe = lastHari + 1;
        }
        if (props.latestProduction.harga_per_kg) {
            nextHarga = Number(props.latestProduction.harga_per_kg);
        }
        if (props.latestProduction.butir_per_kg) {
            nextRasio = Number(props.latestProduction.butir_per_kg);
        }
    }

    const tgl = getLocalDate();
    form.value = {
        tanggal: tgl,
        hari: updateHariName(tgl),
        umur_ke: nextUmurKe,
        umur_minggu: nextMinggu,
        hari_ke: nextHariKe,
        produksi_butir: '',
        butir_per_kg: nextRasio,
        produksi_kg: '',
        harga_per_kg: nextHarga,
        telur_retak_butir: 0,
        keterangan_retak: '',
        telur_rusak_butir: 0,
        keterangan_rusak: '',
    };
    isModalOpen.value = true;
};

const openEditModal = (prod) => {
    editingProd.value = prod;
    form.value = {
        tanggal: prod.tanggal,
        hari: prod.hari || updateHariName(prod.tanggal),
        umur_ke: prod.umur_ke || 1,
        umur_minggu: prod.umur_minggu || 24,
        hari_ke: prod.hari_ke || 1,
        produksi_butir: prod.produksi_butir || '',
        butir_per_kg: Number(prod.butir_per_kg) || 16,
        produksi_kg: Number(prod.produksi_kg) || '',
        harga_per_kg: Number(prod.harga_per_kg) || 22000,
        telur_retak_butir: prod.telur_retak_butir || 0,
        keterangan_retak: prod.keterangan_retak || '',
        telur_rusak_butir: prod.telur_rusak_butir || 0,
        keterangan_rusak: prod.keterangan_rusak || '',
    };
    isModalOpen.value = true;
};

const submitForm = () => {
    isSubmitting.value = true;
    if (editingProd.value) {
        router.put(route('kp.production.update', editingProd.value.id), form.value, {
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
    } else {
        router.post(route('kp.production.store'), form.value, {
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
    }
};

const deleteProduction = (id) => {
    if (confirm('Hapus catatan produksi ini? Stok telur di gudang akan disesuaikan otomatis.')) {
        router.delete(route('kp.production.destroy', id));
    }
};
</script>

<template>
    <KetahananPanganLayout title="Pencatatan Produksi Telur" :unit="unit" :user="user">
        <!-- Summary Cards Sesuai PRD & Standar Ketahanan Pangan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- Card 1: Total Butir (Periode) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-500">Total Butir (Periode)</p>
                    <span v-if="summary.produksi_hari_ini_butir > 0" class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200">
                        Hari Ini: {{ summary.produksi_hari_ini_butir }} btr
                    </span>
                    <span v-else class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                        Hari Ini: 0 btr
                    </span>
                </div>
                <h4 class="text-2xl font-bold text-blue-600 mt-1">
                    {{ (summary.total_produksi_butir || 0).toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <div class="mt-2 text-[11px] text-blue-500 font-medium">Hasil panen fisik harian</div>
            </div>

            <!-- Card 2: Stok Telur Bagus Siap Jual -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-slate-500">Stok Telur Bagus Siap Jual</p>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                        Siap Jual
                    </span>
                </div>
                <h4 class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ Number(summary.stok_saat_ini_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </h4>
                <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Gudang Penyimpanan</span>
                    <span class="text-slate-500 font-medium text-[10.5px]">± {{ Math.round(Number(summary.stok_saat_ini_kg) * 16) }} butir</span>
                </div>
            </div>

            <!-- Card 3: Telur Retak -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Telur Retak</p>
                <h4 class="text-2xl font-bold text-amber-600 mt-1">
                    {{ (summary.total_retak_butir || 0).toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <div class="mt-2 text-[11px] text-amber-500 font-medium">Dicatat terpisah dari stok utama</div>
            </div>

            <!-- Card 4: Telur Rusak / Pecah -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Telur Rusak / Pecah</p>
                <h4 class="text-2xl font-bold text-rose-600 mt-1">
                    {{ (summary.total_rusak_butir || 0).toLocaleString('id-ID') }} <span class="text-xs font-normal text-slate-400">butir</span>
                </h4>
                <div class="mt-2 text-[11px] text-rose-400 font-medium">Kerugian fisik panen</div>
            </div>

            <!-- Card 5: Total Produksi (Periode) & Input Button -->
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-xs bg-gradient-to-br from-emerald-50/50 to-white flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-emerald-800">Total Produksi (Periode)</p>
                        <span v-if="summary.produksi_hari_ini_kg > 0" class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                            Hari Ini: {{ Number(summary.produksi_hari_ini_kg).toFixed(1) }} kg
                        </span>
                    </div>
                    <h4 class="text-2xl font-extrabold text-emerald-700 mt-1">
                        {{ Number(summary.total_produksi_kg).toFixed(1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                    </h4>
                    <div class="mt-1 text-[11px] text-emerald-600 font-medium">
                        Estimasi Nilai: {{ formatRp(summary.total_estimasi_pendapatan) }}
                    </div>
                </div>
                <button 
                    @click="openCreateModal"
                    class="mt-3 w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                >
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    Input Produksi Harian
                </button>
            </div>
        </div>

        <!-- Filter Modern Multi-Tier Sesuai Karakteristik Logistik Pakan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 space-y-3.5">
            <!-- Row 1: Header Judul Rekap & Tombol Pintas Cepat -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Rekap Hasil Produksi Budidaya Ayam Ras Petelur (01462)
                    </h4>
                    <p class="text-[11px] text-slate-500">Sesuai Dokumen Administrasi Rekap Fisik BUMDes Ciwuni</p>
                </div>

                <!-- Tombol Pintas Cepat (Hari Ini, Bulan Ini, Bulan Lalu, Semua) -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-400 text-[11px] font-medium mr-1 hidden sm:inline">Pintas:</span>
                    <button 
                        @click="filterToday"
                        :class="['px-3 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1 cursor-pointer', activePreset === 'today' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700']"
                        title="Filter hanya data hari ini"
                    >
                        <span class="material-symbols-outlined text-sm">today</span>
                        Hari Ini
                    </button>
                    <button 
                        @click="filterThisMonth"
                        :class="['px-3 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1 cursor-pointer', activePreset === 'this_month' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700']"
                        title="Filter data bulan ini sampai hari ini"
                    >
                        <span class="material-symbols-outlined text-sm">calendar_month</span>
                        Bulan Ini
                    </button>
                    <button 
                        @click="filterLastMonth"
                        :class="['px-3 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1 cursor-pointer', activePreset === 'last_month' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700']"
                        title="Filter data 1 bulan lalu"
                    >
                        <span class="material-symbols-outlined text-sm">history</span>
                        Bulan Lalu
                    </button>
                    <button 
                        @click="filterAll"
                        :class="['px-3 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1 cursor-pointer', isAllData ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700']"
                        title="Tampilkan seluruh data tanpa batas tanggal"
                    >
                        <span class="material-symbols-outlined text-sm">all_inclusive</span>
                        Semua
                    </button>
                </div>
            </div>

            <!-- Row 2: Dropdown Pilih Bulan (Solusi 1-Klik) & Rentang Tanggal Manual -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Dropdown Pilih Bulan & Tahun -->
                <div class="flex items-center gap-2">
                    <span class="text-slate-500 font-medium text-[11px] flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-slate-400">filter_list</span>
                        Pilih Bulan:
                    </span>
                    <select 
                        v-model="selectedMonth" 
                        @change="onMonthSelect"
                        class="text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-emerald-500 focus:border-emerald-500 py-1.5 pl-3 pr-8 cursor-pointer"
                    >
                        <option value="">-- Pilih Bulan --</option>
                        <option v-for="m in months" :key="m.value" :value="m.value">
                            {{ m.name }}
                        </option>
                    </select>
                    <select 
                        v-model="selectedYear" 
                        @change="onMonthSelect"
                        class="text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-emerald-500 focus:border-emerald-500 py-1.5 pl-3 pr-8 cursor-pointer"
                    >
                        <option :value="2024">2024</option>
                        <option :value="2025">2025</option>
                        <option :value="2026">2026</option>
                        <option :value="2027">2027</option>
                    </select>
                </div>

                <!-- Rentang Tanggal Manual -->
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 text-[11px] hidden md:inline">Rentang:</span>
                    <input 
                        type="date" 
                        v-model="startDate" 
                        class="text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-2.5" 
                    />
                    <span class="text-xs text-slate-400">s/d</span>
                    <input 
                        type="date" 
                        v-model="endDate" 
                        class="text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-2.5" 
                    />
                    <button 
                        @click="applyFilter" 
                        class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition flex items-center gap-1 cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-sm">filter_alt</span>
                        Filter
                    </button>
                </div>
            </div>

            <!-- Active Filter Status Bar -->
            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium text-slate-700">Filter Aktif:</span>
                    <span v-if="isAllData" class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                        Seluruh Data (Tanpa Batas Tanggal)
                    </span>
                    <span v-else-if="activePreset === 'today'" class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                        Hari Ini ({{ startDate }})
                    </span>
                    <span v-else class="font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                        {{ formatDateId(startDate) }} s/d {{ formatDateId(endDate) }}
                    </span>
                </div>
                <button 
                    v-if="!isAllData && activePreset !== 'this_month'" 
                    @click="filterThisMonth" 
                    class="text-emerald-600 hover:underline cursor-pointer font-medium"
                >
                    Reset ke Bulan Ini
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
                            <td class="p-3 text-right font-bold text-blue-600">
                                {{ prod.produksi_butir ? Number(prod.produksi_butir).toLocaleString('id-ID') : '-' }}
                            </td>
                            <td class="p-3 text-right font-bold text-emerald-600">
                                {{ Number(prod.produksi_kg).toFixed(1) }} kg
                            </td>
                            <td class="p-3 text-right text-slate-500">
                                {{ Number(prod.stok_sebelum_kg).toFixed(1) }}
                            </td>
                            <td class="p-3 text-right font-bold text-slate-800">
                                {{ Number(prod.sisa_stok_kg).toFixed(1) }}
                            </td>
                            <td class="p-3 text-right text-slate-600">
                                {{ formatRp(prod.harga_per_kg) }}
                            </td>
                            <td class="p-3 text-right font-medium text-emerald-700">
                                {{ formatRp(prod.pendapatan_estimasi) }}
                            </td>
                            <td class="p-3 text-center">
                                <span v-if="prod.telur_retak_butir > 0" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold" :title="prod.keterangan_retak || ''">
                                    {{ prod.telur_retak_butir }}
                                </span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="p-3 text-center">
                                <span v-if="prod.telur_rusak_butir > 0" class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold" :title="prod.keterangan_rusak || ''">
                                    {{ prod.telur_rusak_butir }}
                                </span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal(prod)" class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 cursor-pointer" title="Edit">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button @click="deleteProduction(prod.id)" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer" title="Hapus">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="productions.data.length === 0">
                            <td colspan="11" class="text-center py-12 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-300">egg</span>
                                    <p class="font-semibold text-slate-600">Belum ada catatan hasil produksi pada periode ini.</p>
                                    <p class="text-[11px] text-slate-400">Gunakan tombol pintas "Semua" atau pilih bulan lain untuk melihat catatan produksi terdahulu.</p>
                                    <button @click="filterAll" class="mt-1 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-sm">all_inclusive</span>
                                        Lihat Seluruh Data
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Produksi -->
            <div v-if="productions.total > 0" class="p-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ productions.from || 0 }} - {{ productions.to || 0 }} dari {{ productions.total }} catatan</span>
                <div v-if="productions.links && productions.links.length > 3" class="flex gap-1">
                    <button 
                        v-for="(link, i) in productions.links" 
                        :key="i"
                        :disabled="!link.url"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                        :class="[
                            'px-2.5 py-1 rounded-lg text-xs font-medium cursor-pointer',
                            link.active ? 'bg-emerald-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-600',
                            !link.url ? 'opacity-40 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Modal Input / Edit Produksi -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-800">
                        {{ editingProd ? 'Edit Catatan Produksi Telur Harian' : 'Input Produksi Telur Harian' }}
                    </h4>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal</label>
                            <input 
                                type="date" 
                                v-model="form.tanggal" 
                                @change="onTanggalChange" 
                                class="w-full text-xs rounded-xl border-slate-200" 
                                required 
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari</label>
                            <input 
                                type="text" 
                                v-model="form.hari" 
                                placeholder="Cth: Senin" 
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50" 
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Umur Minggu Ke</label>
                            <input type="number" v-model="form.umur_minggu" class="w-full text-xs rounded-xl border-slate-200" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Ke (1-7)</label>
                            <input type="number" min="1" max="7" v-model="form.hari_ke" class="w-full text-xs rounded-xl border-slate-200" />
                        </div>
                    </div>

                    <!-- Kalkulator Otomatis Butir ke Kg -->
                    <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-900 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-blue-600">calculate</span>
                                Perhitungan Telur (Butir & Kg)
                            </span>
                            <span class="text-[10px] text-blue-600 font-medium">Otomatis sinkron</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Hasil Butir</label>
                                <input 
                                    type="number" 
                                    v-model="form.produksi_butir" 
                                    @input="hitungManualDariButir" 
                                    placeholder="Butir"
                                    class="w-full text-xs rounded-lg border-blue-200 font-bold text-blue-800" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Rasio (btr/kg)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    v-model="form.butir_per_kg" 
                                    @input="hitungDariRasio" 
                                    placeholder="Rasio"
                                    class="w-full text-xs rounded-lg border-blue-200 text-slate-700" 
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Produksi (kg)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    v-model="form.produksi_kg" 
                                    @input="hitungManualDariKg" 
                                    placeholder="Kg"
                                    class="w-full text-xs rounded-lg border-blue-200 font-bold text-emerald-700" 
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

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="isModalOpen = false" :disabled="isSubmitting" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl flex items-center gap-1 cursor-pointer">
                            <span v-if="isSubmitting" class="material-symbols-outlined text-sm animate-spin">refresh</span>
                            {{ editingProd ? 'Simpan Perubahan' : 'Simpan Produksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </KetahananPanganLayout>
</template>
