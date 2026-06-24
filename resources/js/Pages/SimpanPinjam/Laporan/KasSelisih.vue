<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ summary: Object, rincian: Object, filters: Object });

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);

const rincianSelisih = computed(() => {
    if (!props.rincian) return [];
    
    const map = {};
    
    // Tambah Pinjaman (Pokok)
    props.rincian.keluar_pinjaman?.forEach(p => {
        const nasabahId = p.nasabah?.id || 'unknown';
        const nama = p.nasabah?.nama || 'Hamba Allah';
        if (!map[nasabahId]) map[nasabahId] = { id: nasabahId, nama, pinjaman: 0, angsuran: 0, selisih: 0 };
        map[nasabahId].pinjaman += Number(p.pinjaman_pokok);
        map[nasabahId].selisih += Number(p.pinjaman_pokok);
    });
    
    // Kurang Angsuran (Total)
    props.rincian.masuk_angsuran?.forEach(a => {
        const p = a.pinjaman;
        if (!p) return;
        const nasabahId = p.nasabah?.id || 'unknown';
        const nama = p.nasabah?.nama || 'Hamba Allah';
        
        if (!map[nasabahId]) map[nasabahId] = { id: nasabahId, nama, pinjaman: 0, angsuran: 0, selisih: 0 };
        
        const total = Number(a.jumlah_bayar);
        
        map[nasabahId].angsuran += total;
        map[nasabahId].selisih -= total;
    });
    
    return Object.values(map).sort((a, b) => b.selisih - a.selisih);
});

const getFilterLabel = computed(() => {
    if (props.filters?.tanggal) return `Tanggal: ${props.filters.tanggal}`;
    let res = '';
    if (props.filters?.bulan) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        res += months[parseInt(props.filters.bulan) - 1] + ' ';
    }
    res += props.filters?.tahun || new Date().getFullYear();
    return res;
});
</script>

<template>
    <Head title="Rincian Selisih Pinjaman & Angsuran" />
    <AuthenticatedLayout>
        <template #header>Rincian Selisih Pinjaman & Angsuran</template>
        
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.kas', filters)" class="inline-flex items-center gap-1 text-sm text-[color:var(--color-secondary)] hover:text-[color:var(--color-on-surface)]">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali ke Laporan Kas
                </Link>
                <h2 class="text-lg font-semibold">Rincian Selisih Pinjaman & Angsuran</h2>
            </div>

            <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-6 shadow-sm">
                <div class="mb-4 bg-amber-50 p-4 rounded-lg border border-amber-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-amber-800 mb-1">Periode: {{ getFilterLabel }}</h3>
                        <p class="text-sm text-amber-700">
                            Menampilkan total nilai pinjaman (pokok) yang dicairkan dikurangi angsuran (total) yang dibayarkan oleh masing-masing nasabah pada periode ini.
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-amber-700 uppercase mb-1">Total Selisih Keseluruhan</p>
                        <p class="text-2xl font-black text-slate-800" :class="Number(summary.total_pinjaman_all) - Number(summary.total_angsuran_all) < 0 ? 'text-red-600' : 'text-amber-600'">
                            {{ formatCurrency(Number(summary.total_pinjaman_all) - Number(summary.total_angsuran_all)) }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-500 bg-slate-50 uppercase border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Nasabah</th>
                                <th class="px-6 py-4 text-right">Pinjaman Pokok<br/><span class="text-[10px] lowercase font-normal text-blue-600">(+) Masuk/Cair ke Nasabah</span></th>
                                <th class="px-6 py-4 text-right">Angsuran Total<br/><span class="text-[10px] lowercase font-normal text-teal-600">(-) Dibayar oleh Nasabah</span></th>
                                <th class="px-6 py-4 text-right">Selisih Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in rincianSelisih" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ item.nama }}</td>
                                <td class="px-6 py-4 text-right text-blue-600 font-medium">{{ formatCurrency(item.pinjaman) }}</td>
                                <td class="px-6 py-4 text-right text-teal-600 font-medium">{{ formatCurrency(item.angsuran) }}</td>
                                <td class="px-6 py-4 text-right font-bold text-base" :class="item.selisih < 0 ? 'text-red-600' : 'text-slate-800'">
                                    {{ formatCurrency(item.selisih) }}
                                </td>
                            </tr>
                            <tr v-if="rincianSelisih.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">inbox</span>
                                    <p>Tidak ada data transaksi pinjaman atau angsuran pada periode ini.</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="rincianSelisih.length > 0" class="bg-slate-50 border-t-2 border-slate-200">
                            <tr>
                                <td class="px-6 py-4 font-bold text-slate-800 text-right uppercase">Total</td>
                                <td class="px-6 py-4 text-right font-bold text-blue-600">{{ formatCurrency(rincianSelisih.reduce((acc, curr) => acc + curr.pinjaman, 0)) }}</td>
                                <td class="px-6 py-4 text-right font-bold text-teal-600">{{ formatCurrency(rincianSelisih.reduce((acc, curr) => acc + curr.angsuran, 0)) }}</td>
                                <td class="px-6 py-4 text-right font-bold text-amber-600 text-lg">
                                    {{ formatCurrency(Number(summary.total_pinjaman_all) - Number(summary.total_angsuran_all)) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
