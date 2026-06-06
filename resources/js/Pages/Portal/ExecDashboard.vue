<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    ringkasan: { type: Object, required: true },
    loanStats: { type: Object, required: true },
    monthlyData: { type: Array, default: () => [] },
    unitSummaries: { type: Array, default: () => [] },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value || 0);

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <Head title="Dashboard Eksekutif" />

    <PortalLayout>
        <template #header>Dashboard Eksekutif</template>

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h2 class="text-2xl font-bold text-emerald-800">Ringkasan Eksekutif BUMDes</h2>
                <p class="text-sm text-slate-500">Data gabungan seluruh unit usaha BUMDes Dammar Wulan.</p>
            </div>

            <!-- Top Stats - Row 1 (Small Stats) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-emerald-600 text-lg">hub</span>
                    </div>
                    <p class="text-xl font-bold text-slate-800">{{ ringkasan.unitAktif }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wider font-semibold">Unit Aktif</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-emerald-600 text-lg">group</span>
                    </div>
                    <p class="text-xl font-bold text-slate-800">{{ formatNumber(ringkasan.totalNasabah) }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wider font-semibold">Nasabah</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-amber-300 transition-all shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-amber-600 text-lg">warning</span>
                    </div>
                    <p class="text-xl font-bold text-amber-700">{{ ringkasan.totalTunggakan }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wider font-semibold">Tunggakan</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-red-300 transition-all shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-red-600 text-lg">dangerous</span>
                    </div>
                    <p class="text-xl font-bold text-red-700">{{ ringkasan.totalKreditMacet }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wider font-semibold">Kredit Macet</p>
                </div>
                <div class="bg-emerald-600 text-white rounded-xl p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-lg">sync_alt</span>
                    </div>
                    <p class="text-xl font-bold">{{ formatNumber(ringkasan.totalTransaksi) }}</p>
                    <p class="text-[10px] text-emerald-100 mt-0.5 uppercase tracking-wider font-semibold">Transaksi</p>
                </div>
            </div>

            <!-- Top Stats - Row 2 (Large Monetary Stats) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-emerald-400 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Total Tabungan</p>
                            <p class="text-2xl font-bold text-slate-800 font-mono truncate" :title="formatCurrency(ringkasan.totalTabungan)">{{ formatCurrency(ringkasan.totalTabungan) }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined">savings</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-rose-300 transition-all shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Total Piutang</p>
                            <p class="text-2xl font-bold text-rose-700 font-mono truncate" :title="formatCurrency(ringkasan.totalPiutang)">{{ formatCurrency(ringkasan.totalPiutang) }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Monthly Performance -->
                <div class="col-span-12 lg:col-span-8 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-base font-bold text-slate-800">Kinerja Bulanan</h4>
                            <p class="text-xs text-slate-500">Perbandingan setor, tarik, dan angsuran 6 bulan terakhir.</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(month, idx) in monthlyData" :key="idx" class="flex items-center gap-4 p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <p class="text-xs font-bold text-slate-600 w-20 shrink-0">{{ month.bulan }}</p>
                            <div class="flex-1 grid grid-cols-3 gap-3 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-slate-500">Setor</span>
                                    <span class="font-mono font-bold text-emerald-700 ml-auto">{{ formatCurrency(month.setor) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span class="text-slate-500">Tarik</span>
                                    <span class="font-mono font-bold text-rose-700 ml-auto">{{ formatCurrency(month.tarik) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="text-slate-500">Angsuran</span>
                                    <span class="font-mono font-bold text-blue-700 ml-auto">{{ formatCurrency(month.angsuran) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Status Summary -->
                <div class="col-span-12 lg:col-span-4 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Status Pinjaman</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="text-sm font-semibold text-emerald-800">Aktif</span>
                            </div>
                            <span class="text-lg font-bold text-emerald-700">{{ loanStats.aktif }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-slate-400"></span>
                                <span class="text-sm font-semibold text-slate-700">Lunas</span>
                            </div>
                            <span class="text-lg font-bold text-slate-700">{{ loanStats.lunas }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-semibold text-amber-800">Menunggak</span>
                            </div>
                            <span class="text-lg font-bold text-amber-700">{{ loanStats.menunggak }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-rose-50 border border-rose-100">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <span class="text-sm font-semibold text-rose-800">Kredit Macet</span>
                            </div>
                            <span class="text-lg font-bold text-rose-700">{{ loanStats.kredit_macet }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Per-Unit Summary -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-bold text-slate-800">Ringkasan Per Unit</h4>
                        <p class="text-xs text-slate-500">Data singkat setiap unit usaha aktif.</p>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="unit in unitSummaries" :key="unit.id" class="border border-slate-200 rounded-xl p-4 hover:border-emerald-400 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">{{ unit.icon || 'business' }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ unit.nama }}</p>
                                <span :class="unit.tipe === 'internal' ? 'text-emerald-600' : 'text-blue-600'" class="text-[9px] font-bold uppercase tracking-wider">{{ unit.tipe }}</span>
                            </div>
                        </div>

                        <template v-if="unit.data">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="p-2 rounded bg-slate-50">
                                    <p class="text-slate-500">Nasabah</p>
                                    <p class="font-bold text-slate-800">{{ formatNumber(unit.data.nasabah) }}</p>
                                </div>
                                <div class="p-2 rounded bg-slate-50">
                                    <p class="text-slate-500">Tabungan</p>
                                    <p class="font-bold text-slate-800 font-mono text-[11px]">{{ formatCurrency(unit.data.total_tabungan) }}</p>
                                </div>
                                <div class="p-2 rounded bg-slate-50">
                                    <p class="text-slate-500">Piutang</p>
                                    <p class="font-bold text-rose-700 font-mono text-[11px]">{{ formatCurrency(unit.data.piutang) }}</p>
                                </div>
                                <div class="p-2 rounded bg-slate-50">
                                    <p class="text-slate-500">Macet</p>
                                    <p class="font-bold text-red-700">{{ unit.data.kredit_macet }}</p>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="text-center py-4 text-xs text-slate-400">
                                <span class="material-symbols-outlined text-2xl mb-1 block">cloud_off</span>
                                Data tidak tersedia (unit eksternal)
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
