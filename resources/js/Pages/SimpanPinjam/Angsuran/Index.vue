<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditTransaksiModal from '@/Components/EditTransaksiModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';

const props = defineProps({
    angsuran: Object,
    nasabahOptions: Array,
    filters: Object,
    summary: Object,
    summaryAll: Object,
    availableBulan: Array,
    flash: Object,
});

// Auto-open receipt in new tab after submitting form
onMounted(() => {
    if (props.flash?.struk_url) {
        window.open(props.flash.struk_url, '_blank');
    }
});

const search = ref(props.filters?.search ?? '');
const bulan  = ref(props.filters?.bulan  ?? '');

let timeout;
watch([search, bulan], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('angsuran.index'), {
            search: search.value,
            bulan:  bulan.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const formatCurrency = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID') : '-';
const pasaranLabel = { legi: 'Legi', pahing: 'Pahing', pon: 'Pon', wage: 'Wage', kliwon: 'Kliwon' };

const formatBulanLabel = (val) => {
    if (!val) return '';
    const [y, m] = val.split('-');
    return new Date(y, parseInt(m) - 1, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const isFiltered = computed(() => !!bulan.value);

// ── Edit Modal ──────────────────────────────────────────────────────────────
const showEdit    = ref(false);
const editData    = ref(null);
const editUrl     = ref('');
const csrfToken   = usePage().props.csrf_token
                  ?? document.querySelector('meta[name="csrf-token"]')?.content;

function openEdit(row) {
    editData.value = {
        id:             row.id,
        nomor_transaksi: row.nomor_transaksi,
        tanggal:        row.tanggal,
        pasaran:        row.pasaran,
        jumlah_bayar:   row.jumlah_bayar,
    };
    editUrl.value  = route('angsuran.update', row.id);
    showEdit.value = true;
}

function handleSaved() {
    router.reload({ preserveScroll: true });
}

function kirimStrukWa(row) {
    const noHp = row.pinjaman?.nasabah?.no_hp;
    if (!noHp) {
        alert('Nomor HP Nasabah tidak tersedia.');
        return;
    }
    let no = noHp.replace(/\D/g, '');
    if (no.startsWith('0')) no = '62' + no.slice(1);
    if (!no.startsWith('62')) no = '62' + no;

    const strukUrl = route('angsuran.struk', row.id);
    const nama = row.pinjaman?.nasabah?.nama || '-';
    
    const p = row.pinjaman;
    const t = p?.total_tagihan > 0 ? p.total_tagihan : 1;
    const porsiPokok = p ? (p.pinjaman_pokok / t) * row.jumlah_bayar : 0;
    const porsiBunga = p ? ((p.pinjaman_pokok * p.bunga / 100) / t) * row.jumlah_bayar : 0;
    
    const pesan =
`Assalamu'alaikum, Bapak/Ibu *${nama}*.

Berikut informasi transaksi pembayaran angsuran Anda di *BUMDes Dammar Wulan*:

No. Transaksi : #${row.nomor_transaksi || '-'}
Tanggal       : ${formatDate(row.tanggal)}
Angsuran Ke   : ${row.angsuran_ke}
Jumlah Bayar  : ${formatCurrency(row.jumlah_bayar)}
(Pokok: ${formatCurrency(porsiPokok)}, Bunga: ${formatCurrency(porsiBunga)})
Sisa Pinjaman : ${formatCurrency(row.sisa_pinjaman)}

Lihat struk lengkap di:
${strukUrl}

Terima kasih.`;

    window.open(`https://wa.me/${no}?text=${encodeURIComponent(pesan)}`, '_blank');
}
</script>

<template>
    <Head title="Riwayat Angsuran" />
    <AuthenticatedLayout>
        <template #header>Angsuran</template>
        <div class="space-y-5">
            <!-- Flash Success Banner -->
            <div v-if="flash?.success" class="flex items-center justify-between gap-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-blue-600">check_circle</span>
                    <span>{{ flash.success }} — Struk dibuka di tab baru.</span>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Riwayat Angsuran</h2>
                    <p class="text-sm text-[color:var(--color-secondary)]">Total {{ angsuran.total }} transaksi{{ bulan ? ' pada ' + formatBulanLabel(bulan) : '' }}</p>
                </div>
                <Link :href="route('angsuran.create')" class="inline-flex items-center gap-2 rounded-lg bg-[color:var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    <span class="material-symbols-outlined text-base">payments</span> Bayar Angsuran
                </Link>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <!-- Filter aktif: summary bulan -->
                <template v-if="isFiltered">
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }} — Transaksi</p>
                        <p class="mt-1 text-lg font-bold text-[color:var(--color-on-surface)]">{{ summary?.total_transaksi ?? 0 }}</p>
                    </div>
                    <div class="rounded-xl border border-[color:var(--color-primary)]/30 bg-[color:var(--color-primary)]/5 p-4">
                        <p class="text-xs font-medium text-[color:var(--color-primary)]">{{ formatBulanLabel(bulan) }} — Total Bayar</p>
                        <p class="mt-1 text-base font-bold text-blue-700">{{ formatCurrency(summary?.total_bayar) }}</p>
                    </div>
                </template>

                <!-- Summary keseluruhan (selalu tampil) -->
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Transaksi</p>
                    <p class="mt-1 text-lg font-bold text-[color:var(--color-on-surface)]">{{ summaryAll?.total_transaksi ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-[color:var(--color-outline-variant)] bg-white p-4">
                    <p class="text-xs text-[color:var(--color-secondary)]">Semua Bulan — Total Bayar</p>
                    <p class="mt-1 text-base font-bold text-blue-700">{{ formatCurrency(summaryAll?.total_bayar) }}</p>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-base text-[color:var(--color-on-surface-variant)]">search</span>
                    <input v-model="search" type="text" placeholder="Cari nama nasabah..."
                        class="w-full rounded-lg border border-[color:var(--color-outline-variant)] bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[color:var(--color-primary)] focus:outline-none" />
                </div>
                <select v-model="bulan" class="rounded-lg border border-[color:var(--color-outline-variant)] bg-white px-3 py-2.5 text-sm focus:outline-none">
                    <option value="">Semua Bulan</option>
                    <option v-for="b in availableBulan" :key="b" :value="b">{{ formatBulanLabel(b) }}</option>
                </select>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[color:var(--color-outline-variant)] bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[color:var(--color-outline-variant)] bg-[color:var(--color-surface-container-low)]">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Nasabah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Ke-</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Hari Pasaran</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Jumlah Bayar</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Sisa Pinjaman</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[color:var(--color-secondary)]">Struk / Edit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-outline-variant)]">
                            <tr v-if="angsuran.data.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center text-[color:var(--color-secondary)]">Tidak ada data angsuran</td>
                            </tr>
                            <tr v-for="row in angsuran.data" :key="row.id" class="hover:bg-[color:var(--color-surface-container-low)]">
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ row.pinjaman?.nasabah?.nama }}</p>
                                    <p class="font-mono text-xs text-[color:var(--color-secondary)]">{{ row.pinjaman?.nasabah?.nomor_rekening }}</p>
                                </td>
                                <td class="px-4 py-3 text-[color:var(--color-secondary)]">{{ formatDate(row.tanggal) }}</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ row.angsuran_ke }}</td>
                                <td class="px-4 py-3 capitalize text-[color:var(--color-secondary)]">{{ pasaranLabel[row.pasaran] ?? row.pasaran }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-semibold text-blue-600">{{ formatCurrency(row.jumlah_bayar) }}</div>
                                    <div v-if="row.pinjaman" class="text-[10px] text-gray-500 mt-0.5">
                                        Pokok: {{ formatCurrency((row.pinjaman.pinjaman_pokok / (row.pinjaman.total_tagihan || 1)) * row.jumlah_bayar) }}
                                    </div>
                                    <div v-if="row.pinjaman" class="text-[10px] text-gray-500">
                                        Bunga: {{ formatCurrency(((row.pinjaman.pinjaman_pokok * row.pinjaman.bunga / 100) / (row.pinjaman.total_tagihan || 1)) * row.jumlah_bayar) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right" :class="Number(row.sisa_pinjaman) <= 0 ? 'text-blue-600' : 'text-red-600'">{{ formatCurrency(row.sisa_pinjaman) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <a :href="route('angsuran.struk', row.id)" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-[color:var(--color-surface-container)] px-2.5 py-1 text-xs hover:bg-[color:var(--color-surface-container-high)]">
                                            <span class="material-symbols-outlined text-xs">print</span> Print
                                        </a>
                                        <button @click="kirimStrukWa(row)" class="inline-flex items-center gap-1 rounded-lg bg-green-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-600 transition">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.534 5.855L.054 23.272a.75.75 0 00.917.928l5.528-1.451A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.723 9.723 0 01-5.02-1.394l-.36-.213-3.73.979.997-3.645-.235-.374A9.718 9.718 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg> WA
                                        </button>
                                        <button @click="openEdit(row)" class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-semibold text-white hover:bg-amber-600 transition">
                                            <span class="material-symbols-outlined text-xs">edit</span> Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="angsuran.last_page > 1" class="flex items-center justify-between border-t border-[color:var(--color-outline-variant)] px-4 py-3">
                    <p class="text-sm text-[color:var(--color-secondary)]">{{ angsuran.from }}–{{ angsuran.to }} dari {{ angsuran.total }}</p>
                    <div class="flex gap-1">
                        <Link v-for="link in angsuran.links" :key="link.label" :href="link.url ?? '#'" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                            :class="link.active ? 'bg-[color:var(--color-primary)] text-white' : link.url ? 'hover:bg-[color:var(--color-surface-container)]' : 'cursor-not-allowed text-[color:var(--color-outline-variant)]'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Edit Transaksi Modal -->
    <EditTransaksiModal
        :show="showEdit"
        :transaksi="editData"
        :update-url="editUrl"
        mode="angsuran"
        :csrf-token="csrfToken"
        @close="showEdit = false"
        @saved="handleSaved"
    />
</template>
