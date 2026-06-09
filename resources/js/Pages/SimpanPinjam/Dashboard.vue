<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LoanStatusChart from '@/Components/Charts/LoanStatusChart.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    stats: { type: Object, required: true },
    loanChart: { type: Object, required: true },
    recentTransactions: { type: Array, default: () => [] },
    availableBulan: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const bulan = ref(props.filters?.bulan ?? '');

let timeout;
watch(bulan, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('dashboard'), { bulan: bulan.value }, { preserveState: true, replace: true });
    }, 300);
});

const formatBulanLabel = (val) => {
    if (!val) return 'Hari Ini';
    const [y, m] = val.split('-');
    return new Date(y, parseInt(m) - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>Dashboard</template>

        <!-- Dashboard Content -->
        <div class="space-y-6">
        <!-- Header Section -->
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-primary">Operasional Harian</h2>
                    <p class="text-sm text-secondary">Selamat datang kembali, berikut ringkasan transaksi {{ bulan ? formatBulanLabel(bulan) : 'hari ini' }}.</p>
                </div>
                <!-- Filter Bulan -->
                <select v-model="bulan" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none shadow-sm">
                    <option value="">Hari Ini</option>
                    <option v-for="b in availableBulan" :key="b" :value="b">{{ formatBulanLabel(b) }}</option>
                </select>
            </div>

            <!-- Bento Grid Stats -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Saldo Kas -->
                <div class="col-span-12 md:col-span-4 bg-white p-5 border border-outline-variant rounded-xl flex flex-col justify-between group hover:border-primary transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-blue-50 rounded-lg text-blue-700">
                            <span class="material-symbols-outlined">account_balance_wallet</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-xs flex items-center gap-1">
                            Aktif
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-sm font-medium">Total Saldo Kas</p>
                        <h3 class="text-xl font-bold text-slate-900 font-mono mt-1">{{ formatCurrency(stats.totalSaldoKas) }}</h3>
                        <p class="text-[11px] text-outline mt-1">Akumulasi tabungan reguler & sembako</p>
                    </div>
                </div>

                <!-- Piutang Berjalan -->
                <div class="col-span-12 md:col-span-4 bg-white p-5 border border-outline-variant rounded-xl flex flex-col justify-between hover:border-indigo-500 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="p-2.5 bg-indigo-50 rounded-lg text-indigo-700">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <span class="text-slate-700 font-semibold text-xs">
                            Outstanding
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-secondary text-sm font-medium">Piutang Berjalan</p>
                        <h3 class="text-xl font-bold text-slate-900 font-mono mt-1">{{ formatCurrency(stats.totalPiutangBerjalan) }}</h3>
                        <p class="text-[11px] text-outline mt-1">Total pinjaman aktif yang belum lunas</p>
                    </div>
                </div>

                <!-- Nasabah Aktif -->
                <div class="col-span-12 md:col-span-4 bg-primary text-white p-5 rounded-xl flex flex-col justify-between relative overflow-hidden shadow-sm">
                    <div class="absolute -right-4 -bottom-4 opacity-10">
                        <span class="material-symbols-outlined text-[100px]">groups</span>
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="p-2.5 bg-white/20 rounded-lg">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Nasabah</span>
                    </div>
                    <div class="mt-4 relative z-10">
                        <p class="text-blue-100 text-sm font-medium">Total Nasabah Terdaftar</p>
                        <h3 class="text-3xl font-bold mt-1">{{ stats.totalNasabah }} <span class="text-sm font-normal opacity-70">Orang</span></h3>
                        <p class="text-[11px] text-blue-100 mt-1">Tergabung dalam unit simpan pinjam</p>
                    </div>
                </div>
            </div>

            <!-- Interactive Operations Row -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Quick Actions -->
                <div class="col-span-12 lg:col-span-8 bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-bold text-slate-800">Aksi Cepat</h4>
                        <span class="material-symbols-outlined text-outline text-lg">bolt</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link :href="route('tabungan.index')" class="flex flex-col items-center justify-center gap-3 p-5 border border-outline-variant hover:border-primary hover:bg-blue-50/20 rounded-xl transition-all group text-center">
                            <div class="w-12 h-12 rounded-full bg-white border border-outline-variant flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all text-primary">
                                <span class="material-symbols-outlined text-[24px]">move_to_inbox</span>
                            </div>
                            <span class="font-semibold text-sm text-slate-700 group-hover:text-primary">Setor Tunai</span>
                        </Link>

                        <Link :href="route('tabungan.index')" class="flex flex-col items-center justify-center gap-3 p-5 border border-outline-variant hover:border-primary hover:bg-blue-50/20 rounded-xl transition-all group text-center">
                            <div class="w-12 h-12 rounded-full bg-white border border-outline-variant flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all text-primary">
                                <span class="material-symbols-outlined text-[24px]">outbox</span>
                            </div>
                            <span class="font-semibold text-sm text-slate-700 group-hover:text-primary">Tarik Tunai</span>
                        </Link>

                        <Link :href="isAdmin ? route('nasabah.create') : route('nasabah.index')" class="flex flex-col items-center justify-center gap-3 p-5 border border-outline-variant hover:border-primary hover:bg-blue-50/20 rounded-xl transition-all group text-center">
                            <div class="w-12 h-12 rounded-full bg-white border border-outline-variant flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all text-primary">
                                <span class="material-symbols-outlined text-[24px]">person_add</span>
                            </div>
                            <span class="font-semibold text-sm text-slate-700 group-hover:text-primary">Anggota Baru</span>
                        </Link>
                    </div>
                </div>

                <!-- Info summary / Period Stats -->
                <div class="col-span-12 lg:col-span-4 bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
                    <h4 class="text-base font-bold text-slate-800 mb-4">{{ bulan ? formatBulanLabel(bulan) : 'Hari Ini' }}</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-outline-variant">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-xl">sync_alt</span>
                                <span class="text-sm font-medium text-slate-700">Transaksi Tabungan</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-outline-variant text-xs">{{ stats.transaksiPeriode }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-outline-variant">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-700 text-xl">payments</span>
                                <span class="text-sm font-medium text-slate-700">Angsuran Pinjaman</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-outline-variant text-xs">{{ stats.angsuranPeriode }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoring & Recent Transactions Section -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Loan Status Chart -->
                <div class="col-span-12 lg:col-span-6 bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
                    <div class="mb-4">
                        <h4 class="text-base font-bold text-slate-800">Monitoring Pinjaman</h4>
                        <p class="text-xs text-slate-500 mt-1">Status dan kondisi portofolio pinjaman aktif.</p>
                    </div>
                    <LoanStatusChart :data="loanChart" />
                    
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <Link :href="route('pinjaman.index', { status: 'aktif' })" class="flex items-center justify-between p-3.5 rounded-xl border border-blue-200 bg-blue-50 text-slate-900 hover:bg-blue-100 hover:border-blue-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Aktif</span>
                                <span class="text-xl font-bold mt-0.5">{{ loanChart.aktif }}</span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>
                        
                        <Link :href="route('pinjaman.index', { status: 'lunas' })" class="flex items-center justify-between p-3.5 rounded-xl border border-sky-200 bg-sky-50 text-slate-900 hover:bg-sky-100 hover:border-sky-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Lunas</span>
                                <span class="text-xl font-bold mt-0.5">{{ loanChart.lunas }}</span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>

                        <Link :href="route('tunggakan.index', { status: 'menunggak' })" class="flex items-center justify-between p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/70 text-slate-900 hover:bg-indigo-100 hover:border-indigo-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Menunggak</span>
                                <span class="text-xl font-bold mt-0.5">{{ loanChart.menunggak }}</span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>

                        <Link :href="route('tunggakan.index', { status: 'kredit-macet' })" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 hover:bg-slate-100 hover:border-slate-300 hover:shadow-sm transition-all duration-200 active:scale-95 group cursor-pointer">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kredit Macet</span>
                                <span class="text-xl font-bold mt-0.5">{{ loanChart.kredit_macet }}</span>
                            </div>
                            <span class="material-symbols-outlined text-xl text-slate-500 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all">chevron_right</span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div class="col-span-12 lg:col-span-6 bg-white border border-outline-variant rounded-xl overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="px-5 py-4 border-b border-outline-variant flex justify-between items-center bg-white">
                            <h4 class="text-base font-bold text-slate-800">Transaksi Terakhir</h4>
                            <Link :href="route('tabungan.index')" class="text-xs text-primary font-semibold hover:underline">Semua Transaksi</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-white text-slate-500 font-bold uppercase border-b border-outline-variant">
                                    <tr>
                                        <th class="px-5 py-3">Nasabah</th>
                                        <th class="px-5 py-3">Jenis</th>
                                        <th class="px-5 py-3 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant">
                                    <tr v-for="tx in recentTransactions" :key="tx.id" class="hover:bg-[#f8fafc]">
                                        <td class="px-5 py-3 font-semibold text-slate-700">
                                            <div>{{ tx.nasabah_nama }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ tx.nomor_transaksi }}</div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span :class="tx.jenis_transaksi === 'setor' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-rose-50 text-rose-700 border-rose-100'" class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase">
                                                {{ tx.jenis_transaksi }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-mono font-bold" :class="tx.jenis_transaksi === 'setor' ? 'text-blue-700' : 'text-rose-700'">
                                            {{ formatCurrency(tx.nominal) }}
                                        </td>
                                    </tr>
                                    <tr v-if="recentTransactions.length === 0">
                                        <td colspan="3" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi hari ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
